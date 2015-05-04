<?php

/**
 * Clase que parsea los archivos 'full' que se encuentran normalmente en 
 * /var/log/asterisk y prepara índices para que se pueda consultar mensajes
 * en una fecha determinada. Esta clase está basada en leer los archivos del
 * disco, debido a que los archivos de log son grandes (varias decenas de Mb)
 * y no se pueden cargar enteros en memoria, porque se corre el riesgo de 
 * exceder el límite de memoria de PHP.
 *
 * Esta clase conoce de la rotación de archivos de log, y considera todos
 * los archivos con el patrón full.N además del archivo full.
 */
class LogParser_Full
{
	private $_infoArchivos;
	private $_infoFechas;
	private $_pos_Fecha;
	private $_pos_OffsetMsg;
	private $_hArchivo;
	private $_sNombreArchivo;

	function LogParser_Full($sRuta = '/var/log/asterisk', $pattern = 'full')
	{
		$this->_pos_Fecha = NULL;
		$this->_pos_OffsetMsg = NULL;
		$this->_infoArchivos = array();
		$this->_infoFechas = array();
		$this->_hArchivo = NULL;
		$this->_sNombreArchivo = NULL;

		/* Se listan los archivos de logs disponibles. El archivo original
			tiene el nombre de 'full'. El resto de archivos rotados tienen
			nombres de 'full.N' donde N es un entero que comienza desde 1.
			Se hace uso del hecho de que para N mayor, el archivo es más 
			antiguo.
		*/
		$listaArchivos = glob($sRuta.'/'.$pattern.'*');
		if (is_array($listaArchivos)) {
			$listaArchivos = array_reverse($listaArchivos);
			foreach ($listaArchivos as $sNombreArchivo) {
				//print "DEBUG: analizando archivo $sNombreArchivo...\n";
				
				// Saltarse archivos vacíos
				$iTamanioArchivo = filesize($sNombreArchivo);
				if ($iTamanioArchivo === 0) continue;

				$hArchivo = fopen($sNombreArchivo, 'rb');
				if ($hArchivo !== FALSE) {
					$infoLog = array(
						'ruta'		=>	$sNombreArchivo,
						'fechas'	=>	array(),
					);

					/*
						Lo que se está haciendo abajo es implementar una búsqueda binaria sobre
						las fechas del archivo. El archivo se divide conceptualmente en porciones,
						y se toma la mitad de cada porción que queda por procesar. Si la fecha
						legible al inicio de cada mitad es idéntica (ignorando la hora), se asume
						que la primera mitad consiste enteramente de mensajes en esa fecha, lo cual
						ahorra leer esa mitad del archivo.
					 */
					$listaPorciones = array();
					$listaPorciones[] = array(0, filesize($sNombreArchivo));
					while (count($listaPorciones) > 0) {
						$porcion = array_pop($listaPorciones);
						$iPuntoMedio = (int)(($porcion[0] + $porcion[1]) / 2);
						$iLuegoPuntoMedio = NULL;

						// Leer la fecha en el punto inicial. Aquí debería siempre haber una 
						// línea completa.
						$sFechaPuntoInicial = NULL;
						fseek($hArchivo, $porcion[0], SEEK_SET);
						while (is_null($sFechaPuntoInicial) && !feof($hArchivo)) {
							$porcion[0] = ftell($hArchivo);
							$sLinea = fgets($hArchivo);
							
                            $sFechaPuntoInicial = $this->_obtenerFechaPrefijoLinea($sLinea);
						}

						// Leer la fecha en el punto medio. Si la línea leída no empieza con
						// una fecha, es porque el fseek() cayó en la mitad de una línea, 
						// en cuyo caso el siguiente fgets() debe dar una línea completa.
						// TODO: esta suposición se rompe si mensaje en múltiples líneas.
						$sFechaPuntoMedio = NULL;
						fseek($hArchivo, $iPuntoMedio, SEEK_SET);
						while (is_null($sFechaPuntoMedio) && !feof($hArchivo)) {
							$iPuntoMedio = ftell($hArchivo);
							$sLinea = fgets($hArchivo);

                            $sFechaPuntoMedio = $this->_obtenerFechaPrefijoLinea($sLinea);
                            if (!is_null($sFechaPuntoMedio))
                                $iLuegoPuntoMedio = ftell($hArchivo);
						}

						// Si las fechas inicial y media son iguales, el punto indicado por $iPuntoMedio
						// cae todavía dentro de la primera mitad
						if (!is_null($sFechaPuntoMedio) && $sFechaPuntoInicial == $sFechaPuntoMedio) {
							$iPuntoMedio = $iLuegoPuntoMedio;
						}

						$mitades = array(
							array($porcion[0], $iPuntoMedio),
							array($iPuntoMedio, $porcion[1]),
						);
						
						// Verificar si se debe procesar la segunda mitad...
						if ($mitades[1][0] < $mitades[1][1]) {
							// La lectura de la segunda mitad no ha excedido el fin de la segunda mitad.
							$listaPorciones[] = $mitades[1];
						} else {
						}
						
						// Verificar si se debe procesar la primera mitad...
						if (is_null($sFechaPuntoMedio) || $sFechaPuntoInicial == $sFechaPuntoMedio) {
							// BINGO!!! toda la primera mitad se puede desechar,
							// luego de anotar los offsets correspondientes
							$sFecha = $sFechaPuntoInicial;
							
							if (!isset($infoLog['fechas'][$sFecha])) {
								$infoLog['fechas'][$sFecha] = array(
									'offset_inicio'	=>	$mitades[0][0],
									'offset_final'  =>  $iPuntoMedio,
								);
							} else {
								$infoLog['fechas'][$sFecha]['offset_final'] = $iPuntoMedio;
							}
						} else {
							// Fecha varía a través de primera mitad, agregar a procesamiento
							if ($mitades[1][0] == $mitades[1][1]) {
								// Este caso significa que la la porción es tan corta que la primera
								// línea completa luego del punto medio está al final de la segunda 
								// mitad. En este lugar, se puede usar escaneo lineal.
								fseek($hArchivo, $mitades[0][0], SEEK_SET);
								while (ftell($hArchivo) < $mitades[0][1]) {
									$iPunto = ftell($hArchivo);
									$sLinea = fgets($hArchivo);

                                    $sFecha = $this->_obtenerFechaPrefijoLinea($sLinea);
                                    if (!is_null($sFecha)) {
                                        if (!isset($infoLog['fechas'][$sFecha])) {
                                            $infoLog['fechas'][$sFecha] = array(
                                                'offset_inicio' =>  $iPunto,
                                                'offset_final'  =>  ftell($hArchivo),
                                            );
                                        } else {
                                            $infoLog['fechas'][$sFecha]['offset_final'] = ftell($hArchivo);
                                        }
                                    }
								}
							} else {
								$listaPorciones[] = $mitades[0];
							}
						}
						
					}
					fclose($hArchivo);
					
					if (count($infoLog['fechas']) > 0) $this->_infoArchivos[] = $infoLog;
				}
			}
			
			//print_r($this->_infoArchivos);
			foreach ($this->_infoArchivos as $infoArchivo) {
				foreach ($infoArchivo['fechas'] as $k => $v) {
					if (trim($k) == '') continue;
					if (!isset($this->_infoFechas[$k])) $this->_infoFechas[$k] = array();
					$v['ruta'] = $infoArchivo['ruta'];
					$this->_infoFechas[$k][] = $v;
				}
			}
			//print_r($this->_infoFechas);
		}
	}

    private function _obtenerFechaPrefijoLinea($sLinea)
    {
        $listaRegexp = array(
            '/^\[(\w{3}\s+\d+\s+\d{2}:\d{2}:\d{2})\]/',         // [Jun  6 04:02:01] VERBOSE[3708] logger.c: Asterisk Event Logger restarted
            '/^\[(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\]/',   // [2014-01-02 14:40:15] VERBOSE[4443] config.c:   == Parsing '/etc/asterisk/asterisk.conf': Found
        );
        foreach ($listaRegexp as $rx) {
            $regs = NULL;
        	if (preg_match($rx, $sLinea, $regs)) {
        		$iTimestamp = strtotime($regs[1]);
                if ($iTimestamp !== FALSE) return date('Y-m-d', $iTimestamp);
        	}
        }
        return NULL;
    }
	
	/**
	 * Procedimiento que lista las fechas para los cuales hay mensajes en 
	 * los archivos de log. Las fechas se devuelven en un arreglo, en 
	 * orden cronológico. Cada fecha se devuelve como una cadena en formato
	 * YYYY-MM-DD
	 * 
	 * @return array	Lista de las fechas disponibles
	 */
	function listarFechas()
	{
		return array_keys($this->_infoFechas);
	}
	
	/**
	 * Procedimiento que cuenta el número de bytes de mensajes que están disponibles
	 * bajo una fecha determinada indicada por el parámetro, en formato YYYY-MM-DD.
	 * Si la fecha es inválida, se devuelve NULL. Si no hay mensajes para la 
	 * fecha indicada, o está fuera de rango para los archivos encontrados, se
	 * devuelve 0
	 *
	 * @param string	$sFecha	Fecha en formato YYYY-MM-DD para reporte
	 *
	 * @return mixed	NULL si fecha inválida, o número de bytes de mensajes
	 */
	function numeroBytesMensajesFecha($sFecha)
	{
		if (!isset($this->_infoFechas[$sFecha])) return NULL;
		$iNumBytes = 0;
		foreach ($this->_infoFechas[$sFecha] as $infoArchivo) {
			$iNumBytes += $infoArchivo['offset_final'] - $infoArchivo['offset_inicio'];
		}
		return $iNumBytes;
	}
	
	/**
	 * Procedimiento que mueve un cursor interno (usado por siguienteMensaje()) a
	 * la posición indicada por el número de mensaje indicado, para la fecha 
	 * indicada. Luego de esta operación, la siguiente llamada a siguienteMensaje()
	 * devolverá la línea en la posición de $iNumMensaje (basada en 0).
	 * La posición es indefinida (NULL) hasta la primera llamada a posicionarMensaje()
	 *
	 * @param string	$sFecha		Fecha en formato YYYY-MM-DD para posición
	 * @param int		$iNumMensaje	Número de mensaje a recuperar
	 * 
	 * @return bool	VERDADERO si se puede posicionar el mensaje, FALSO en error
	 */
	function posicionarMensaje($sFecha, $iOffsetMensaje)
	{
		$iOffsetMaximo = $this->numeroBytesMensajesFecha($sFecha);
		if (is_null($iOffsetMaximo)) return FALSE;
		if ($iOffsetMensaje < 0 || $iOffsetMensaje > $iOffsetMaximo) return FALSE;
		
		// Determinar en qué archivo y offset está la posición a leer
		$infoFecha = $this->_infoFechas[$sFecha];
		$iPosArchivo = $iOffsetMensaje;
		$archivoFecha = NULL;
		foreach ($infoFecha as $posibleArchivoFecha) {
			if ($iPosArchivo >= $posibleArchivoFecha['offset_final'] - $posibleArchivoFecha['offset_inicio']) {
				$iPosArchivo -= $posibleArchivoFecha['offset_final'] - $posibleArchivoFecha['offset_inicio'];
			} else {
				$archivoFecha = $posibleArchivoFecha;
				break;
			}
		}
		if (is_null($archivoFecha)) return FALSE; // Más allá del final de los datos

		// Cerrar archivo anterior si debe leerse uno nuevo
		if (!is_null($this->_sNombreArchivo) && $archivoFecha['ruta'] != $this->_sNombreArchivo) {
			if (!is_null($this->_hArchivo)) fclose($this->_hArchivo);
			$this->_hArchivo = NULL;
			$this->_sNombreArchivo = NULL;
		}
		
		// Abrir el archivo si es necesario
		if (is_null($this->_hArchivo)) {
			$this->_sNombreArchivo = $archivoFecha['ruta'];
			$this->_hArchivo = fopen($this->_sNombreArchivo, 'rb');
		}
		fseek($this->_hArchivo, $archivoFecha['offset_inicio'] + $iPosArchivo, SEEK_SET);
		$this->_pos_Fecha = $sFecha;
		$this->_pos_OffsetMsg = $iOffsetMensaje;
		return TRUE;		
	}
	
	/**
	 * Procedimiento que obtiene la posición actual de la siguiente línea a leer, como
	 * una tupla cuyo primer elemento es la cadena de fecha, y el segundo elemento es
	 * la línea que sería leída a continuación.
	 *
	 * @return mixed	NULL si no está definida la posición, o la tupla.
	 */
	function obtenerPosicionMensaje()
	{
		if (is_null($this->_pos_Fecha) || is_null($this->_pos_OffsetMsg))
			return NULL;
		else return array($this->_pos_Fecha, $this->_pos_OffsetMsg);
	}
	
	/**
	 * Procedimiento que lee el siguiente mensaje en la posición actual, y actualiza la posición
	 * virtual dentro de la fecha actual.
	 *
	 * @return mixed	NULL si la posición no está definida, o la cadena en la posición
	 */
	function siguienteMensaje()
	{
		if (!is_resource($this->_hArchivo)) return NULL;
		$sLinea = fgets($this->_hArchivo);
		$iPosArchivo = ftell($this->_hArchivo);

        // Leer tentativamente las siguientes líneas. Si las líneas no empiezan con corchete,
        // se asume que son continuación del mensaje anterior y se concatenan.
        while (($sContinuacion = fgets($this->_hArchivo)) !== FALSE) {
            if ($sContinuacion{0} == '[') {
                // Siguiente línea es nuevo mensaje
                fseek($this->_hArchivo, $iPosArchivo, SEEK_SET);
                break;
            } else {
                // Siguiente línea es continuación de mensaje anterior
                $sLinea .= $sContinuacion;
                $iPosArchivo = ftell($this->_hArchivo);
            }
        }
		
		// Construir offset virtual a partir de archivo actual y posición		
		$infoFecha = $this->_infoFechas[$this->_pos_Fecha];
		$iOffsetVirtual = 0;
		$bOverflow = FALSE;
		$sigArchivo = NULL;
		$iOffsetNuevo = NULL;
		foreach ($infoFecha as $posibleArchivoFecha) {
			if ($posibleArchivoFecha['ruta'] != $this->_sNombreArchivo) {
				if (!$bOverflow) {
					$iOffsetVirtual += $posibleArchivoFecha['offset_final'] - $posibleArchivoFecha['offset_inicio'];
				} else {
					$sigArchivo = $posibleArchivoFecha;
					$iOffsetNuevo += $posibleArchivoFecha['offset_inicio'];
					break;
				}
			} else {
				$iOffsetVirtual += $iPosArchivo - $posibleArchivoFecha['offset_inicio'];
				if ($iPosArchivo >= $posibleArchivoFecha['offset_final']) {
					$bOverflow = TRUE;
					$iOffsetNuevo = $iPosArchivo - $posibleArchivoFecha['offset_final'];
				} else {
					break;
				}
			}
		}
		
		if (!is_null($sigArchivo)) {
			if (!is_null($this->_hArchivo)) fclose($this->_hArchivo);
			$this->_hArchivo = NULL;
			$this->_sNombreArchivo = $sigArchivo['ruta'];
			$this->_hArchivo = fopen($this->_sNombreArchivo, 'rb');
			fseek($this->_hArchivo, $iOffsetNuevo, SEEK_SET);
		} elseif ($bOverflow) {
			// Se ha alcanzado el final del archivo
			if (!is_null($this->_hArchivo)) fclose($this->_hArchivo);
			$this->_hArchivo = NULL;
			$this->_sNombreArchivo = NULL;
		}
		$this->_pos_OffsetMsg = $iOffsetVirtual;

		return $sLinea;
	}
	
	/**
	 * Procedimiento que busca la primera ocurrencia de la cadena indicada a 
	 * partir de la posición actual, hasta el límite de la fecha indicada.
	 * Actualmente sólo busca hacia delante.
	 *
	 * @param   string  $text   Cadena que se busca
	 *
	 * @return  mixed   NULL si no se encuentra la cadena, o la posición de
	 * la primera línea donde se encontró la cadena.
	 */
	function buscarTextoMensaje($text)
	{
	    // Guardar posición vieja
	    $tuplaPosVieja = $this->obtenerPosicionMensaje();
	    $tuplaPosTexto = NULL;
	    
        do {
            $tuplaPosTexto = $this->obtenerPosicionMensaje();
            $sLinea = $this->siguienteMensaje();
            if (is_null($sLinea)) {
                $tuplaPosTexto = NULL;
                break;
            }
            if (strpos($sLinea, $text) !== FALSE) break;
        } while(1);

        // Restaurar posición anterior
        $this->posicionarMensaje($tuplaPosVieja[0], $tuplaPosVieja[1]);
	    return $tuplaPosTexto;
	}
}

?>
