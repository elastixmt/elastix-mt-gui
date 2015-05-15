<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.2-2                                               |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: ElastixInstallerProcess.class.php,v 1.48 2009/03/26 13:46:58 alex Exp $ */
require_once('AbstractProcess.class.php');
require_once('/var/www/html/libs/paloSantoDB.class.php');

class ElastixInstallerProcess extends AbstractProcess
{
    private $_debug = FALSE;    // Setear a TRUE para mostrar salida de yum

    private $oMainLog;      // Log abierto por framework de demonio
    private $_hEscucha;     // Socket que escucha las conexiones entrantes
    private $_procYum;      // Objeto que administra conexión a YUM
    private $_procPipes;    // Arreglo de tuberías a YUM 0-STDIN 1-STDOUT 2-STDERR
    private $_conexiones;   // Arreglo de conexiones activas del sistema
    
    private $_sContenido;   // Contenido devuelto por yum shell como resultado del último comando
    private $_bCapturarStderr = FALSE;
    private $_stderrBuf = '';    // Salida de stderr para actividad actual
    private $_estadoPaquete = NULL;
    private $_timestampUltimoUso = NULL;    // timestamp de la última vez que se requirió yum shell

    private $_customStatus = '';    // Estado arbitrario para compartir con interfaz web
    private $_numAsignacionesCustom = 0;    // Número de veces que ha cambiado el estado arbitrario
    private $_cachedir = '/var/cache/yum';
    
    /* Lote de comandos a ejecutar para la operación actual. Cada comando se
     * representa como una tupla. El elemento 0 es el comando a ejecutar o 
     * ejecutado. El elemento 1 es la salida del comando, o NULL si el comando 
     * está pendiente de ejecutar. En todo momento, el comando actualmente en
     * ejecución es el primer elemento que tenga la salida puesta a NULL.  */
    private $_loteComandos = array();

    function inicioPostDemonio($infoConfig, &$oMainLog)
    {
        $bContinuar = TRUE;

        // Guardar referencias al log del programa
        $this->oMainLog =& $oMainLog;

        $this->_conexiones = array();

        // El siguiente comando ejecuta python para averiguar los valores de 
        // $basearch y $releasever que se referencian en /etc/yum.conf en Fedora 17
        $basearch = $releasever = NULL;
        $sCmd = "python -c '".
            "import yum; ".
            "yb = yum.YumBase(); ".
            "ba = yb.conf.yumvar[\"basearch\"]; ".
            "rv = yb.conf.yumvar[\"releasever\"]; ".
            "print \"BASEARCH-RELEASEVER:\", ba, rv;'";
        $output = $retval = NULL;
        exec($sCmd, $output, $retval);
        foreach ($output as $s) {
        	$regs = NULL;
            if (preg_match('/^BASEARCH-RELEASEVER:\s+(\S+)\s+(\S+)/', $s, $regs)) {
            	$basearch = $regs[1];
                $releasever = $regs[2];
            }
        }
        if (!is_null($basearch) && file_exists('/etc/yum.conf')) {
        	foreach (file('/etc/yum.conf') as $s) {
        		$regs = NULL;
                if (preg_match('/^cachedir\s*=\s*(.+)/', trim($s), $regs)) {
                	$this->_cachedir = str_replace(
                        array('$basearch', '$releasever'),
                        array($basearch, $releasever),
                        $regs[1]);
                    $this->oMainLog->output("INFO: cachedir es ahora {$this->_cachedir}");
                }
        	}
        }

        // Socket para recibir peticiones entrantes
        if ($bContinuar) {
            $errno = $errstr = NULL;
            $sUrlSocket = $this->_construirUrlSocket();
            $this->_hEscucha = stream_socket_server($sUrlSocket, $errno, $errstr);
            if (!$this->_hEscucha) {
                $this->oMainLog->output("ERR: no se puede iniciar socket de escucha: ($errno) $errstr");
                $bContinuar = FALSE;
            } else {
                // No bloquearse en escucha de conexiones
                stream_set_blocking($this->_hEscucha, 0);
                $this->oMainLog->output("INFO: escuchando peticiones en $sUrlSocket ...");
            }
        }
        $this->_estadoPaquete = array(
            'status'    =>  'idle',
            'action'    =>  'none',
            'testonly'  =>  FALSE,
            'autoconfirm'   =>  FALSE,

            'iniciales' =>  array(),
            'progreso'  =>  array(),
            'instalado' =>  array(),
            'errores'   =>  array(),
            'warning'   =>  array(),
        );

        return $bContinuar;
    }

    private function _asegurarYumShellIniciado()
    {
        $this->_timestampUltimoUso = time();
        if (is_null($this->_procYum))
            return $this->_iniciarYumShell();
        else return TRUE;
    }
    
    private function _iniciarYumShell()
    {
        $bContinuar = TRUE;
        $bFinInicio = FALSE;
        
        // Abrir proceso de yum
        if ($bContinuar) {
            $descriptores = array(
	            0	=>	array('pipe', 'r'),
	            1	=>	array('pipe', 'w'),
	            2	=>	array('pipe', 'w'),
            );
            $this->_procPipes = NULL; $cwd = '/';
            $this->_procYum = proc_open('/usr/sbin/close-on-exec.pl /usr/bin/yum -y shell', $descriptores, $this->_procPipes, $cwd);
            if (!is_resource($this->_procYum)) {
                $this->oMainLog->output("ERR: no se puede iniciar instancia de yum shell");
                $bContinuar = FALSE;
            } else {
                $this->oMainLog->output("INFO: arrancando yum shell ...");
                //stream_set_blocking($this->_procPipes[0], 0);
                stream_set_blocking($this->_procPipes[1], 0);
                stream_set_blocking($this->_procPipes[2], 0);                
            }
        }
        
        /* En Fedora 17+, el yum shell ya no muestra la cadena "Setting up Yum Shell",
         * de forma que para saber que el shell está listo para recibir comandos,
         * se envía un comando y se espera a recibir la respuesta conocida. */
        fwrite($this->_procPipes[0], "help\n");
        
        // Leer los datos de la salida de yum hasta que se obtenga la cadena
        // final que indica que se tiene el shell listo.
        $bFinInicio = FALSE; $sContenido = '';
        while ($bContinuar && !$bFinInicio) {
		    $salidaYum = array($this->_procPipes[1], $this->_procPipes[2]);
		    $entradaYum = NULL;
		    $exceptYum = NULL;
		    $iNumCambio = stream_select($salidaYum, $entradaYum, $exceptYum, 1);
		    if ($iNumCambio === false) {
		        $this->oMainLog->output("ERR: falla al esperar en select()");
		        $bContinuar = FALSE;
    		} elseif ($iNumCambio > 0) {
    		    if (in_array($this->_procPipes[2], $salidaYum)) {
    		        // Mensaje de stderr de yum, mandar a log
    		        $s = stream_get_contents($this->_procPipes[2]);
    		        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
    		        if ($this->_debug) $this->oMainLog->output("yum(stderr): $s");
    		    }
    		    if (in_array($this->_procPipes[1], $salidaYum)) {
    		        // Mensaje de stdout de yum
    		        $s = stream_get_contents($this->_procPipes[1]);
    		        $sContenido .= $s;
    		        if ($s == '') {
        		        $this->oMainLog->output("ERR: fin no esperado de yum!");
    		            $bContinuar = false;
    		            break;
    		        }
    		        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
    		        if ($this->_debug) $this->oMainLog->output("yum(stdout): $s");
                    $t = $this->_recuperarSalidaConHelp($sContenido);
                    if (!is_null($t)) {
        		        $this->oMainLog->output("INFO: yum shell está preparado.");
        		        $bFinInicio = TRUE;
		            }
    		    }
            }
        }
        
        // Abortar procesos si no se puede iniciar yum
        if (!$bContinuar && is_resource($this->_procYum)) {
        	fclose($this->_procPipes[0]);
        	fclose($this->_procPipes[1]);
        	fclose($this->_procPipes[2]);
        	$this->_procPipes = NULL;
        	$ret = proc_close($this->_procYum);
        	$this->oMainLog->output("INFO: yum finaliza con ret=$ret");
        	$this->_procYum = NULL;
        }
        
        return $bContinuar;
    }

    /* Al ejecutar yum shell via pipe, no existe ningún separador obvio que 
     * permita saber que un comando ya ha sido terminado de procesar por yum.
     * Para lidiar con esto, todos los comandos serán ejecutados con un "help"
     * a continuación. En el momento en que se detecta la salida de "help", se
     * sabe que se ha terminado de ejecutar el comando, y la salida previa a la
     * de "help" es la salida del comando de interés.
     * 
     * Esta función intenta extraer la salida de comando de un comando ejecutado
     * con help a continuación. Si la salida no contiene help, se asume que el 
     * comando no se ha terminado de ejecutar, y se devuelve NULL. De otro modo
     * se devuelve la salida previa al help */
    private function _recuperarSalidaConHelp(&$sContenido)
    {
    	$inicio = stripos($sContenido, '> usage: yum [options] COMMAND');
        if ($inicio !== FALSE &&
            strpos($sContenido, "List of Commands:\n") !== FALSE &&
            strpos($sContenido, "Shell specific arguments:\n") !== FALSE &&
            substr($sContenido, -6) == "\n    \n") {
            return substr($sContenido, 0, $inicio);
        }
        return NULL;
    }

    private function _finalizarYumShell()
    {
        if (is_resource($this->_procYum)) {

            $yumStatus = proc_get_status($this->_procYum);
            if ($yumStatus['running']) {
                $sComando = "quit\n";
                fwrite($this->_procPipes[0], $sComando);

                $bFinInicio = FALSE; $sContenido = '';
                while (!$bFinInicio) {
			$yumStatus = proc_get_status($this->_procYum);
			if (!$yumStatus['running']) {
			    $this->oMainLog->output("INFO: finalizada instancia de yum shell (2)");
			    $bFinInicio = TRUE;
			    break;
			}
			$salidaYum = array($this->_procPipes[1], $this->_procPipes[2]);
			$entradaYum = NULL;
			$exceptYum = NULL;
			$iNumCambio = stream_select($salidaYum, $entradaYum, $exceptYum, 1);
			if ($iNumCambio === false) {
			    $this->oMainLog->output("ERR: falla al esperar en select()");
			    break;
            		} elseif ($iNumCambio > 0) {
            		    if (in_array($this->_procPipes[2], $salidaYum)) {
            		        // Mensaje de stderr de yum, mandar a log
            		        $s = stream_get_contents($this->_procPipes[2]);
            		        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
            		        if ($this->_debug) $this->oMainLog->output("yum(stderr): $s");
            		    }
            		    if (in_array($this->_procPipes[1], $salidaYum)) {
            		        // Mensaje de stdout de yum
            		        $s = stream_get_contents($this->_procPipes[1]);
            		        $sContenido .= $s;
            		        if ($s == '') {
                		        $this->oMainLog->output("INFO: finalizada instancia de yum shell");
            		            $bFinInicio = TRUE;
            		            break;
            		        }
            		        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
            		        if ($this->_debug) $this->oMainLog->output("yum(stdout): $s");
            		    }
                    }
                }
            }
        	fclose($this->_procPipes[0]);
        	fclose($this->_procPipes[1]);
        	fclose($this->_procPipes[2]);
        	$this->_procPipes = NULL;
        	$ret = proc_close($this->_procYum);
        	$this->oMainLog->output("INFO: yum finaliza con ret=$ret");
        	$this->_procYum = NULL;
        }
    }

    /* Asignar una lista de comandos a ejecutar por lote, y empezar por el 
     * primero de ellos. A cada comando se le concatena el comando "help" para
     * poder determinar cuándo cada comando ha sido terminado de ejecutarse */
    private function _iniciarLoteComandos($listaCmd)
    {
    	$this->_sContenido = '';
        $this->_loteComandos = array();
        foreach ($listaCmd as $cmd) {
        	$this->_loteComandos[] = array($cmd, NULL);
        }
        $this->oMainLog->output("INFO: ejecutando comando yum: ".$this->_loteComandos[0][0]);
        fwrite($this->_procPipes[0], $this->_loteComandos[0][0]."\nhelp\n");
    }

    private function _actualizarLoteComandos()
    {
        // Leer la última salida disponible y agregarla para parsear
        $s = stream_get_contents($this->_procPipes[1]);
        $this->_sContenido .= $s;
        if ($s == '') {
            $this->oMainLog->output("ERR: fin no esperado de yum!");
            return FALSE;
        }

        // Para depuración
        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
        if ($this->_debug) $this->oMainLog->output("yum(stdout): $s");
        
    	// Localizar comando actualmente en ejecución
        for ($i = 0; $i < count($this->_loteComandos); $i++) {
        	if (is_null($this->_loteComandos[$i][1])) break;
        }
        if ($i < count($this->_loteComandos)) {
            $salidaComando = $this->_recuperarSalidaConHelp($this->_sContenido);
            if (!is_null($salidaComando)) {
            	// El comando actual se ha terminado de ejecutar
                $this->_loteComandos[$i][1] = $salidaComando;
                $this->_sContenido = '';
                
                // Ejecutar el siguiente comando en la lista, si existe
                $i++;
                if ($i < count($this->_loteComandos)) {
                    $this->oMainLog->output("INFO: ejecutando comando yum: ".$this->_loteComandos[$i][0]);
                	fwrite($this->_procPipes[0], $this->_loteComandos[$i][0]."\nhelp\n");
                }
            }
        } else {
        	$this->oMainLog->output("WARN: ".__FUNCTION__.
                ": no hay comandos pendientes, lote actual es: ".
                print_r($this->_loteComandos, TRUE));
        }
        return TRUE;
    }

    /* Verificar si el comando que inicia con el prefijo indicado ha terminado,
     * y recuperar su salida de haberlo hecho. Si no, devuelve NULL. */
    private function _salidaComandoLote($prefijo)
    {
        for ($i = 0; $i < count($this->_loteComandos); $i++) {
            if (is_null($this->_loteComandos[$i][1])) return NULL;
            if (strpos($this->_loteComandos[$i][0], $prefijo) === 0)
                return $this->_loteComandos[$i][1];
        }
        
        // Esto no debería ocurrir
        $this->oMainLog->output("WARN: ".__FUNCTION__.
            ": no se encuentra comando que inicie con '$prefijo', lote actual es: ".
            print_r($this->_loteComandos, TRUE));
        return NULL;
    }

    private function _concatenarSalidaCompletaLote($bIncompleto = FALSE)
    {
    	$s = '';
        for ($i = 0; $i < count($this->_loteComandos); $i++) {
            if (is_null($this->_loteComandos[$i][1])) break;
            $s .= $this->_loteComandos[$i][1];
        }
        if ($bIncompleto) $s .= $this->_sContenido;
        return $s;
    }

    // Construir el URL que describe el socket para escuchar peticiones
    private function _construirUrlSocket()
    {
        // TODO: hacer configurable
        return 'tcp://127.0.0.1:20004';
    }
    
    function procedimientoDemonio()
    {
        $listoLeer = array();
        $listoEscribir = array();
        $listoErr = NULL;

        // Recolectar todos los descriptores que se monitorean
        $listoLeer[] = $this->_hEscucha;        // Escucha de nuevas conexiones
        if (is_resource($this->_procYum)) {
            $listoLeer[] = $this->_procPipes[1];    // yum salida estándar
            $listoLeer[] = $this->_procPipes[2];    // yum error estándar
        }
        foreach ($this->_conexiones as &$conexion) {
            if (!$conexion['exit_request']) $listoLeer[] = $conexion['socket'];
            if (strlen($conexion['pendiente_escribir']) > 0) {
                $listoEscribir[] = $conexion['socket'];                
            }
        }
        $iNumCambio = stream_select($listoLeer, $listoEscribir, $listoErr, 1);
        if ($iNumCambio === false) {
            // Interrupción, tal vez una señal
            $this->oMainLog->output("INFO: select() finaliza con fallo - señal pendiente?");
            return FALSE;
        } elseif ($iNumCambio > 0 || count($listoLeer) > 0 || count($listoEscribir) > 0) {
            if (in_array($this->_hEscucha, $listoLeer)) {
                // Entra una conexión nueva
                $this->_procesarConexionNueva();
            }
            if (is_resource($this->_procYum) && in_array($this->_procPipes[1], $listoLeer)) {
                // Se tiene nueva información del yum shell
                //$bActivo = $this->_actualizarEstadoYumShell();
                $bActivo = $this->_actualizarLoteComandos();
                if (!$bActivo) {
                    $this->_finalizarYumShell();
                    if ($this->_estadoPaquete['status'] != 'error') {
                        $this->_estadoPaquete = array(
                            'status'    =>  'idle',
                            'action'    =>  'none',
                            'testonly'  =>  FALSE,
    
                            'progreso'  =>  array(),
                            'instalado' =>  array(),
                            'errores'   =>  array(),
                            'warning'   =>  array(),
                        );
                    }
                } else {
                	$this->_actualizarEstadoYumShell();
                }
            }
            if (is_resource($this->_procYum) && in_array($this->_procPipes[2], $listoLeer)) {
		        // Mensaje de stderr de yum, mandar a log
		        $this->_actualizarStderrYumShell();
            }
            foreach ($this->_conexiones as $iPos => &$conexion) {
                if (in_array($conexion['socket'], $listoEscribir)) {
                    // Escribir lo más que se puede de los datos pendientes por mostrar
                    $iBytesEscritos = fwrite($conexion['socket'], $conexion['pendiente_escribir']);
                    if ($iBytesEscritos === FALSE) {
                        $this->oMainLog->output("ERR: error al escribir datos a ".$conexion['socket']);
                        $this->_cerrarConexion($iPos);
                    } else {
                        $conexion['pendiente_escribir'] = substr($conexion['pendiente_escribir'], $iBytesEscritos);
                    }
                }
                if (in_array($conexion['socket'], $listoLeer)) {
                    $this->_procesarEntradaConexion($iPos);
                }
            }

            // Cerrar todas las conexiones que no tienen más datos que mostrar
            // y que han marcado que deben terminarse
            foreach ($this->_conexiones as $iPos => &$conexion) {
                if (is_array($conexion) && $conexion['exit_request'] && strlen($conexion['pendiente_escribir']) <= 0) {
                    $this->_cerrarConexion($iPos);
                }
            }

            // Remover todos los elementos seteados a FALSE
            $this->_conexiones = array_filter($this->_conexiones);
            
            // Revisar regularmente la descarga de los paquetes
            if ($this->_estadoPaquete['action'] == 'downloading')
                $this->_revisarProgresoPaquetes();
                
        }        

        // Si el yum shell ha estado inactivo por más de 1 minuto se apaga
        if (is_resource($this->_procYum) && 
            time() - $this->_timestampUltimoUso > 60 &&
            ($this->_estadoPaquete['status'] == 'idle' || $this->_estadoPaquete['status'] == 'error') &&
            $this->_estadoPaquete['action'] == 'none')
            $this->_finalizarYumShell();

        return TRUE;
    }
    
    private function _procesarConexionNueva()
    {
        $nuevaConn = array(
            'socket'                =>  stream_socket_accept($this->_hEscucha),
            'pendiente_leer'        =>  '',
            'pendiente_escribir'    =>  '',
            'exit_request'          =>  FALSE,
        );
        stream_set_blocking($nuevaConn['socket'], 0);                

        // TODO: enviar status de yum shell al socket antes de aceptar comandos
        $dummy = array();
        $nuevaConn['pendiente_escribir'] = $this->_procesarStatus($dummy);
        $this->_conexiones[] =& $nuevaConn; 
    }
    
    private function _procesarEntradaConexion($iPos)
    {
        $sNuevaEntrada = fread($this->_conexiones[$iPos]['socket'], 8192);
        if ($sNuevaEntrada == '') {
            // Lectura de cadena vacía indica que se ha cerrado la conexión remotamente
	        $this->_cerrarConexion($iPos);
	        return ;
        }

        // pendiente_leer puede tener un contenido previo que no es una línea completa
        $this->_conexiones[$iPos]['pendiente_leer'] .= $sNuevaEntrada;
        $listaComandos = explode("\n", $this->_conexiones[$iPos]['pendiente_leer']);
        while (count($listaComandos) > 1) {
            $sComando = array_shift($listaComandos);
            if (trim($sComando) != '') $this->_procesarComando($iPos, trim($sComando));
        }

        // Esto asigna, o la cadena vacía, o el pedazo de comando que se ha leído
        $this->_conexiones[$iPos]['pendiente_leer'] = $listaComandos[0];
    }

    private function _cerrarConexion($iPos)
    {
        fclose($this->_conexiones[$iPos]['socket']);
        $this->_conexiones[$iPos] = FALSE;  // Será removido por array_map()
    }

    function limpiezaDemonio()
    {
        // TODO: limpiar las conexiones activas
        foreach ($this->_conexiones as &$conexion) {
            fclose($conexion['socket']);
        }
    
        // TODO: cancelar la operación yum activa
        
        // Cerrar las conexiones al yum shell
        if (is_resource($this->_procYum)) $this->_finalizarYumShell();
        
        // Cerrar el socket de escucha de eventos
        fclose($this->_hEscucha);
        $this->_hEscucha = NULL;
    }

    /**************************************************************************/

/*
Programa en PHP que ejecute a su vez "yum shell" como root.
Se debe exponer un socket para control desde página Web.
Tareas deben de poderse realizar incluso entre desconexiones de socket.
Interfaz simple de comandos vía socket:

* mostrar estado
* agregar paquete a transacción
* limpiar transacción
* remover paquete instalado como parte de transacción
* verificar actualización de paquete y agregarlo a transacción
* iniciar transacción
* cancelar transacción (mandar SIGINT a yum, posiblemente dos veces con demora)


*/

    /* La interfaz de comando que se presenta consiste en un protocolo texto.
       El comando a ingresar es de la forma: COMANDO [ARG1] [ARG2] ...
       seguido de un salto de línea que manda a procesar el comando. Los 
       comandos reconocidos son:
       status 
       add nombredepaquete( nombrepaquete2 ...)
       remove nombredepaquete( nombredepaquete2 ...)
       clear
       confirm
       update nombredepaquete( nombredepaquete2)
       cancel
       quit
       exit     
     */
    private function _procesarComando($iPos, $sComando)
    {
        $sTextoSalida = '';
        $listaComando = preg_split('/\s+/', $sComando);
        if (count($listaComando) <= 0) return;

        $sVerbo = array_shift($listaComando);
        
        switch ($sVerbo) {
        case 'status':
            $sTextoSalida = $this->_procesarStatus($listaComando);
            break;
        case 'add':
            $sTextoSalida = $this->_procesarAdd($listaComando);
            break;
        case 'addconfirm':
            $sTextoSalida = $this->_procesarAddConfirm($listaComando);
            break;
        case 'testadd':
            $sTextoSalida = $this->_procesarTestAdd($listaComando);
            break;
        case 'remove':
            $sTextoSalida = $this->_procesarRemove($listaComando);
            break;
        case 'removeconfirm':
            $sTextoSalida = $this->_procesarRemoveConfirm($listaComando);
            break;
        case 'clear':
            $sTextoSalida = $this->_procesarClear($listaComando);
            break;
        case 'confirm':
            $sTextoSalida = $this->_procesarConfirm($listaComando);
            break;
        case 'update':
            $sTextoSalida = $this->_procesarUpdate($listaComando);
            break;
        case 'updateconfirm':
            $sTextoSalida = $this->_procesarUpdateConfirm($listaComando);
            break;
        case 'testupdate':
            $sTextoSalida = $this->_procesarTestUpdate($listaComando);
            break;
        case 'cancel':
            $sTextoSalida = $this->_procesarCancel($listaComando);
            break;
        case 'check':
            $sTextoSalida = $this->_procesarCheck($listaComando);
            break;
        case 'yumoutput':
            $sTextoSalida = $this->_sContenido;
            break;
        case 'yumerror':
            $sTextoSalida = $this->_stderrBuf;
            break;
        case 'exit':
        case 'quit':
            $this->_conexiones[$iPos]['exit_request'] = TRUE;
            break;
        case 'setcustom':
            $sTextoSalida = $this->_procesarSetCustom($listaComando);
            break;
        case 'getcustom':
            $sTextoSalida = $this->_customStatus."\n";
            break;
        default:
            $sTextoSalida = "ERR Unrecognized\n";
            break;
        }
        $this->_conexiones[$iPos]['pendiente_escribir'] .= $sTextoSalida;
    }

    private function _procesarStatus(&$listaArgs)
    {
        $sReporte = '';

        $sReporte .= "status ".$this->_estadoPaquete['status']."\n";
        $sReporte .= "action ".$this->_estadoPaquete['action']."\n"; // none confirm reporefresh depsolving downloading applying
        $sReporte .= "custom ".$this->_numAsignacionesCustom."\n";
        foreach ($this->_estadoPaquete['progreso'] as $infoProgreso) {
            $sReporte .= 'package'.
                ' '.$infoProgreso['pkgaction']. // pkgaction puede ser: install update remove
                ' '.$infoProgreso['nombre'].    // nombre del paquete
                ' '.$infoProgreso['longitud'].' '.$infoProgreso['descargado'].  // total y descarga
                ' '.$infoProgreso['currstatus']."\n"; // currstatus puede ser: waiting downloading downloaded installing installed removing removed
        }
        foreach ($this->_estadoPaquete['instalado'] as $infoInstalado) {
            $sReporte .= 'installed'.
                ' '.$infoInstalado['nombre'].
                ' '.$infoInstalado['arch'].
                ' '.$infoInstalado['epoch'].
                ' '.$infoInstalado['version'].
                ' '.$infoInstalado['release']."\n";
        }
        
        foreach ($this->_estadoPaquete['errores'] as $sMsg) {
            $sReporte .= 'errmsg '.$sMsg."\n";
        }
        foreach ($this->_estadoPaquete['warning'] as $sMsg) {
            $sReporte .= 'warnmsg '.$sMsg."\n";
        }
        $sReporte .= "end status\n";
        return $sReporte;
    }
    
    /*
================================================================================
 Package         Arch         Version            Repository                Size
================================================================================
Installing:
 pidgin          i386         2.6.6-1.el5        updates                  1.5 M
 pidgin          x86_64       2.6.6-1.el5        updates                  1.5 M
Installing for dependencies:
 gtkspell        i386         2.0.11-2.1         base                      30 k
 libpurple       i386         2.6.6-1.el5        updates                  8.3 M
 libpurple       x86_64       2.6.6-1.el5        virthost64-updates       8.4 M
 libsilc         i386         1.0.2-2.fc6        base                     412 k
 meanwhile       i386         1.0.2-5.el5        base                     108 k

    */
    private function _recogerPaquetesTransaccion($sContenido)
    {
        $lineas = explode("\n", $sContenido);
        $this->_estadoPaquete['progreso'] = array();
        $bReporte = FALSE;
        $sOperacion = NULL;
        $sLineaPrevia = '';
        foreach ($lineas as $sLinea) {
            $regs = NULL;
            if (!$bReporte && preg_match('/^\s+Package\s+Arch\s+Version\s+Repository\s+Size/', $sLinea)) {
                $bReporte = TRUE;
            } elseif (strpos($sLinea, "Transaction Summary") !== FALSE) {
                $bReporte = FALSE;
            } elseif ($bReporte) {
                /* Si el nombre de paquete es muy largo, puede que el resto de la 
                   información haya sido desplazada a la línea siguiente. Sin
                   embargo, no se espera que hayan más de dos líneas. */
                $regs = NULL;
                $sLineaCompleta = ' '.$sLineaPrevia.$sLinea;
                if (preg_match('/^\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+([0-9\.\,]+)\s+[kM]?/', $sLineaCompleta, $regs)) {
                    $this->_estadoPaquete['progreso'][] = array(
                        'pkgaction' =>  $sOperacion,
                        'nombre'    =>  $regs[1],
                        'arch'      =>  $regs[2],
                        'version'   =>  $regs[3],
                        'repo'      =>  $regs[4],
                        'longitud'  =>  $regs[5],
                        'rpmfile'   =>  NULL,
                        'descargado'=>  '-',
                        'currstatus'=>  ($sOperacion == 'remove') ? 'installed' : 'waiting',
                        'provides'  =>  NULL,
                        'requires'  =>  NULL,
                    );
                    $sLineaPrevia = '';
                } elseif (strpos($sLinea, 'Installing') === 0) {
                    $sOperacion = 'install';
                    $sLineaPrevia = '';
                } elseif (strpos($sLinea, 'Updating') === 0) {
                    $sOperacion = 'update';
                    $sLineaPrevia = '';
                } elseif (strpos($sLinea, 'Removing') === 0) {
                    $sOperacion = 'remove';
                    $sLineaPrevia = '';
                } else {
                    if (preg_match('/^\s+(\S+)\s*$/', $sLinea))
                        $sLineaPrevia = $sLinea;
                    else $sLineaPrevia = '';
                }
            } 
            if (preg_match('/No package (\S+) available/', $sLinea, $regs)) {
                $this->_estadoPaquete['status'] = 'error';
                $this->_estadoPaquete['errores'][] = "The following package is not available: ".$regs[1];
            }
        }
        
        if ($this->_estadoPaquete['status'] != 'error' && count($this->_estadoPaquete['progreso']) <= 0) {
            $this->_estadoPaquete['action'] = 'none';
            $this->_estadoPaquete['warning'][] = 'No packages to install or update';
        }
        
        /* La información de tamaño que proporciona yum es demasiado poco detallada
           para poder seguir la pista de la descarga con precisión de bytes. Por lo
           tanto, hay que abrir las bases SQLITE3 de yum y leer los datos de allí.
         */

        // Validar las rutas base de los repos
        $infoRepo = array();
        if ($this->_estadoPaquete['status'] != 'error') {
            $sRutaCache = $this->_cachedir;
            foreach ($this->_estadoPaquete['progreso'] as $paquete) {
                if (!isset($infoRepo[$paquete['repo']])) {

                    $sNombreRepo = $paquete['repo'];
                    if ($sNombreRepo == 'installed') continue;
                    if ($sNombreRepo[0] == '@') continue;
                    $sRutaRepo = $sRutaCache.'/'.$paquete['repo'].'/';
                    $infoRepo[$sNombreRepo] = array(
                        'ruta'  =>  $sRutaRepo,                        
                    );

                    if (!is_dir($sRutaRepo)) {
                        $this->_estadoPaquete['status'] = 'error';
                        $this->_estadoPaquete['errores'][] = "Unable to figure out cache directory for repo: $sNombreRepo";
                    } elseif (!is_readable($sRutaRepo.'repomd.xml')) {
                        $this->_estadoPaquete['status'] = 'error';
                        $this->_estadoPaquete['errores'][] = "Unable to read file repomd.xml from repo: $sNombreRepo";                        
                    } else {
                        // El siguiente código require el módulo php-xml
                        $repomd = new SimpleXMLElement(file_get_contents($sRutaRepo.'repomd.xml'));
                        foreach ($repomd->data as $dataObj) {
                            if ($dataObj['type'] == 'primary_db') {
                                $sRutaPrimary = $dataObj->location['href'];
                                $regs = NULL;
                                if (preg_match('|^(.*)/(\S+)(\.bz2)|', $sRutaPrimary, $regs)) {
                                    $sRutaPrimary = $regs[2];
                                }
                                $infoRepo[$sNombreRepo]['primary_db'] = $sRutaPrimary;
                            } elseif (!isset($infoRepo[$sNombreRepo]['primary_db']) && $dataObj['type'] == 'primary') {
                                $sRutaPrimary = $dataObj->location['href'];
                                $regs = NULL;
                                if (preg_match('|^(.*)/(\S+)|', $sRutaPrimary, $regs)) {
                                    $sRutaPrimary = $regs[2];
                                }
                                
                                // CentOS 5 usa $sRutaRepo/primary.xml.gz.sqlite
                                // Fedora 17 usa $sRutaRepo/gen/primary.xml.sqlite
                                if (file_exists($sRutaRepo.'gen/'.basename($sRutaPrimary, '.gz').'.sqlite')) {
                                    $infoRepo[$sNombreRepo]['primary_db'] = 'gen/'.basename($sRutaPrimary, '.gz').'.sqlite';
                                } else {
                                    $infoRepo[$sNombreRepo]['primary_db'] = $sRutaPrimary.'.sqlite';
                                }
                            }
                        }
                        if (!isset($infoRepo[$sNombreRepo]['primary_db'])) {
                            $this->_estadoPaquete['status'] = 'error';
                            $this->_estadoPaquete['errores'][] = "Unable to locate primary_db from repo: $sNombreRepo";
                        } elseif (!is_readable($sRutaRepo.$infoRepo[$sNombreRepo]['primary_db'])) {
                            $this->_estadoPaquete['status'] = 'error';
                            $this->_estadoPaquete['errores'][] = "Unable to read primary_db from repo: $sNombreRepo";
                            unset($infoRepo[$sNombreRepo]['primary_db']);
                        }
                    }
                }
            }
        }
        
        // Para cada paquete, se abre el archivo primary_db de su correspondiente
        // repo y se consulta vía SQL el tamaño del paquete.
        if ($this->_estadoPaquete['status'] != 'error') {
            foreach ($this->_estadoPaquete['progreso'] as &$infoPaquete) {
                if ($infoPaquete['repo'] == 'installed') continue;
                if ($infoPaquete['repo'][0] == '@') continue;
                $repo =& $infoRepo[$infoPaquete['repo']];
                $regs = NULL;
                if (!preg_match('/^((\S+):)?(\S+)-(\S+)$/', $infoPaquete['version'], $regs)) {
                    $this->_estadoPaquete['status'] = 'error';
                    $this->_estadoPaquete['errores'][] = "Unable to parse version string for package: ".$infoPaquete['nombre'];
                } else {
                    $sEpoch = ($regs[2] == "") ? 0 : $regs[2];
                    $sVersion = $regs[3];
                    $sRelease = $regs[4];
                    
                    // Abrir la conexión a la base de datos
                    $dsn = "sqlite3:///".$repo['ruta'].$repo['primary_db'];
                    $oDB = new paloDB($dsn);
                    if ($oDB->connStatus) {
                        $this->_estadoPaquete['status'] = 'error';
                        $this->_estadoPaquete['errores'][] = "Unable to open primary_db for package: ".$infoPaquete['nombre'];
                    } else {
                        $pkgKey = NULL;
                        
                        // select size_package from packages where name = "pidgin" and arch = "x86_64" and epoch = "0" and version = "2.6.6" and release = "1.el5"
                        $sql =
                            'SELECT size_package, location_href, pkgKey FROM packages '.
                            'WHERE name = ? AND arch = ? AND epoch = ? AND version = ? AND release = ?';
                        $recordset = $oDB->fetchTable($sql, FALSE, array(
                            $infoPaquete['nombre'],
                            $infoPaquete['arch'],
                            $sEpoch,
                            $sVersion,
                            $sRelease,
                        ));
                        if (!is_array($recordset)) {
                            $this->_estadoPaquete['status'] = 'error';
                            $this->_estadoPaquete['errores'][] = "Unable to query primary_db for package: ".$infoPaquete['nombre'];
                        } elseif (count($recordset) <= 0) {
                            $this->_estadoPaquete['status'] = 'error';
                            $this->_estadoPaquete['errores'][] = "Unable to locate package in primary_db for package: ".$infoPaquete['nombre'].
                                  " $infoPaquete[arch] $sEpoch $sVersion $sRelease";
                        } elseif (count($recordset) > 1) {
                            $this->_estadoPaquete['status'] = 'error';
                            $this->_estadoPaquete['errores'][] = "Duplicate package information in primary_db for package: ".$infoPaquete['nombre'];
                        } else {
                            $pkgKey = $recordset[0][2];
                            $infoPaquete['longitud'] = $recordset[0][0];
                            if ($infoPaquete['pkgaction'] != 'remove') 
                                $infoPaquete['descargado'] = 0;
                            $regs = NULL;
                            if (preg_match('|^((.*)/)?(\S+\.rpm)$|', $recordset[0][1], $regs)) {
                                $infoPaquete['rpmfile'] = $repo['ruta'].'packages/'.$regs[3];
                            } else {
                                $this->_estadoPaquete['status'] = 'error';
                                $this->_estadoPaquete['errores'][] = "Unable to discover RPM filename for package: ".$infoPaquete['nombre'];
                            }                            
                        }

                        // Leer los datos de lo que provee y lo que requiere
                        if (!is_null($pkgKey)) {
                            $infoPaquete['provides'] = $oDB->fetchTable('SELECT * FROM provides WHERE pkgKey = ?', TRUE, array($pkgKey));
                            $infoPaquete['requires'] = $oDB->fetchTable('SELECT * FROM requires WHERE pkgKey = ?', TRUE, array($pkgKey));
                        }

                        $oDB->disconnect();
                    }
                }
            }
        }
    }

    private function _procesarAdd(&$listaArgs)
    {
        if (!is_array($listaArgs) || count($listaArgs) <= 0)
            return "ERR No packages\n";
        if ($this->_estadoPaquete['status'] != 'idle')
            return "ERR Invalid status\n";
        
        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'reporefresh';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $this->_estadoPaquete['iniciales'] = $listaArgs;
        $this->_estadoPaquete['testonly'] = FALSE;

        if (!$this->_asegurarYumShellIniciado())
            return "ERR Unable to start Yum Shell\n";
        $this->_activarCapturaStderr();
        $this->_iniciarLoteComandos(array(
            'ts list repoload',
            'install '.implode(' ', $listaArgs), 
            'ts solve',
            'ts list final'));
        return "OK Processing\n";
    }
    
    private function _procesarAddConfirm(&$listaArgs)
    {
    	$r = $this->_procesarAdd($listaArgs);
        if (substr($r, 0, 2) == 'OK')
            $this->_estadoPaquete['autoconfirm'] = TRUE;
        return $r;
    }

    private function _procesarTestAdd(&$listaArgs)
    {
        if (!is_array($listaArgs) || count($listaArgs) <= 0)
            return "ERR No packages\n";
        if ($this->_estadoPaquete['status'] != 'idle')
            return "ERR Invalid status\n";
        
        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'reporefresh';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $this->_estadoPaquete['iniciales'] = $listaArgs;
        $this->_estadoPaquete['testonly'] = TRUE;

        if (!$this->_asegurarYumShellIniciado())
            return "ERR Unable to start Yum Shell\n";
        $this->_activarCapturaStderr();
        $this->_iniciarLoteComandos(array(
            'ts list repoload',
            'install '.implode(' ', $listaArgs), 
            'ts solve',
            'ts list final'));
        return "OK Processing\n";
    }

    private function _procesarUpdate(&$listaArgs)
    {
        if (!is_array($listaArgs) || count($listaArgs) <= 0)
            return "ERR No packages\n";
        if ($this->_estadoPaquete['status'] != 'idle')
            return "ERR Invalid status\n";
        
        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'reporefresh';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $this->_estadoPaquete['iniciales'] = $listaArgs;
        $this->_estadoPaquete['testonly'] = FALSE;
        
        if (!$this->_asegurarYumShellIniciado())
            return "ERR Unable to start Yum Shell\n";
        $this->_iniciarLoteComandos(array(
            'ts list repoload',
            'update '.implode(' ', $listaArgs), 
            'ts solve',
            'ts list final'));
        return "OK Processing\n";
    }

    private function _procesarUpdateConfirm(&$listaArgs)
    {
        $r = $this->_procesarUpdate($listaArgs);
        if (substr($r, 0, 2) == 'OK')
            $this->_estadoPaquete['autoconfirm'] = TRUE;
        return $r;
    }

    private function _procesarTestUpdate(&$listaArgs)
    {
        if (!is_array($listaArgs) || count($listaArgs) <= 0)
            return "ERR No packages\n";
        if ($this->_estadoPaquete['status'] != 'idle')
            return "ERR Invalid status\n";
        
        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'reporefresh';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $this->_estadoPaquete['iniciales'] = $listaArgs;
        $this->_estadoPaquete['testonly'] = TRUE;
        
        if (!$this->_asegurarYumShellIniciado())
            return "ERR Unable to start Yum Shell\n";
        $this->_iniciarLoteComandos(array(
            'ts list repoload',
            'update '.implode(' ', $listaArgs), 
            'ts solve',
            'ts list final'));
        return "OK Processing\n";
    }

    private function _procesarCheck(&$listaArgs)
    {
        if (!is_array($listaArgs) || count($listaArgs) <= 0)
            return "ERR No packages\n";
        if ($this->_estadoPaquete['status'] != 'idle')
            return "ERR Invalid status\n";

        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'checkinstalled';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $this->_estadoPaquete['testonly'] = FALSE;
        
        if (!$this->_asegurarYumShellIniciado())
            return "ERR Unable to start Yum Shell\n";
        $this->_iniciarLoteComandos(array(
            'list '.implode(' ', $listaArgs), 
            //'ts list'
            ));
        return "OK Processing\n";
    }

    private function _procesarClear(&$listaArgs)
    {
        if ($this->_estadoPaquete['status'] == 'busy')
            return "ERR Invalid status\n";
        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'idle';
        $this->_estadoPaquete['action'] = 'none';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['progreso'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $this->_estadoPaquete['testonly'] = FALSE;
        
        if (!$this->_asegurarYumShellIniciado())
            return "ERR Unable to start Yum Shell\n";
        $this->_iniciarLoteComandos(array(
            'ts reset', 
            //'ts list'
            ));
        return "OK\n";
    }
    
    private function _procesarCancel(&$listaArgs)
    {
        if ($this->_estadoPaquete['status'] != 'busy')
            return "ERR Nothing to cancel\n";
        if (!($this->_estadoPaquete['action'] == 'downloading' || 
            $this->_estadoPaquete['action'] == 'reporefresh' ||
            $this->_estadoPaquete['action'] == 'depsolving'))
            return "ERR Cannot cancel\n";

        // YUM requiere dos SIGINT para cancelar una descarga. El primero se 
        // envía aquí. El segundo se envía en _actualizarEstadoYumShell() al 
        // detectar la cadena de aviso de ctrl-c.
        if (!$this->_asegurarYumShellIniciado())
            return "ERR Unable to start Yum Shell\n";
        $infoYum = proc_get_status($this->_procYum);
        posix_kill($infoYum['pid'], SIGINT);
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'cancelling';

        return  "OK Cancelled\n";
    }
    
    private function _procesarConfirm(&$listaArgs)
    {
        if ($this->_estadoPaquete['status'] != 'idle' || $this->_estadoPaquete['action'] != 'confirm')
            return "ERR Invalid status\n";

        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'downloading';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $this->_estadoPaquete['testonly'] = FALSE;
        $this->_estadoPaquete['autoconfirm'] = FALSE;

        if (!$this->_asegurarYumShellIniciado())
            return "ERR Unable to start Yum Shell\n";
        $this->_iniciarLoteComandos(array(
            'run'));
        $this->_activarCapturaStderr();
        return "OK Starting transaction...\n";
    }

    private function _procesarSetCustom(&$listaArgs)
    {
        $sNuevoStatus = implode(' ', $listaArgs);
        if ($this->_customStatus != $sNuevoStatus) {
            $this->_customStatus = $sNuevoStatus;
            $this->_numAsignacionesCustom++;
        }
        return "OK Stored\n";
    }

    private function _procesarRemove(&$listaArgs)
    {
        if (!is_array($listaArgs) || count($listaArgs) <= 0)
            return "ERR No packages\n";
        if ($this->_estadoPaquete['status'] != 'idle')
            return "ERR Invalid status\n";
        
        $this->_sContenido = '';    // Anular la salida de yum que se haya leído
        $this->_estadoPaquete['status'] = 'busy';
        $this->_estadoPaquete['action'] = 'reporefresh';
        $this->_estadoPaquete['errores'] = array();
        $this->_estadoPaquete['warning'] = array();
        $this->_estadoPaquete['instalado'] = array();
        $this->_estadoPaquete['testonly'] = FALSE;
        
        if (!$this->_asegurarYumShellIniciado())
            return "ERR Unable to start Yum Shell\n";
        $this->_iniciarLoteComandos(array(
            'ts list repoload',
            'erase '.implode(' ', $listaArgs),
            'ts solve', 
            'ts list final'));
        return "OK Processing\n";
    }

    private function _procesarRemoveConfirm(&$listaArgs)
    {
        $r = $this->_procesarRemove($listaArgs);
        if (substr($r, 0, 2) == 'OK')
            $this->_estadoPaquete['autoconfirm'] = TRUE;
        return $r;
    }

    private function _actualizarEstadoYumShell()
    {
        $this->_timestampUltimoUso = time();

        if ($this->_estadoPaquete['status'] == 'busy') {
            switch ($this->_estadoPaquete['action']) {
            case 'cancelling':
                // Segundo SIGINT (véase _procesarCancel() para explicación)
                $iCancelPos = strpos(
                    $this->_concatenarSalidaCompletaLote(TRUE),
                    'Current download cancelled, interrupt (ctrl-c) again within two seconds to exit');
                if (FALSE !== $iCancelPos) {
                    $infoYum = proc_get_status($this->_procYum);
                    posix_kill($infoYum['pid'], SIGINT);
                    $this->_estadoPaquete = array(
                        'status'    =>  'idle',
                        'action'    =>  'none',

                        'progreso'  =>  array(),
                        'instalado' =>  array(),
                        'errores'   =>  array(),
                        'warning'   =>  array(),
                    );
                }
                $this->_inactivarCapturaStderr();
                break;
            case 'checkinstalled':
                // Se revisa si un paquete en particular está instalado
                $salidaCmd = $this->_salidaComandoLote('list');
                if (!is_null($salidaCmd)) {
                    $this->_estadoPaquete['status'] = 'idle';
                    $this->_estadoPaquete['action'] = 'none';
                    $lineas = explode("\n", $salidaCmd);
                    $bReporteInstalado = FALSE;
                    foreach ($lineas as $sLinea) {
                        if (strpos($sLinea, 'Installed Packages') !== FALSE) {
                            $bReporteInstalado = TRUE;
                        } elseif (strpos($sLinea, 'Available Packages') === 0 || strpos($sLinea, 'Transaction Summary') === 0) {
                            $bReporteInstalado = FALSE;
                        } elseif ($bReporteInstalado) {
                            $regs = NULL;
                            if (preg_match('/^(\S+)\.(\S+)\s+((\S+):)?(\S+)-(\S+)\s+installed/', $sLinea, $regs)) {
                                $this->_estadoPaquete['instalado'][] = array(
                                    'nombre'    =>  $regs[1],
                                    'arch'      =>  $regs[2],
                                    'epoch'     =>  ($regs[4] == '') ? 0 : $regs[4],
                                    'version'   =>  $regs[5],
                                    'release'   =>  $regs[6],
                                );
                            }
                        }
                    }                    
                }
                break;
            case 'reporefresh':
                // Se inicia refresco de repos para poder realizar resolución de dependencias...
                $salidaCmd = $this->_salidaComandoLote('ts list repoload');
                if (!is_null($salidaCmd)) {
                    $this->_estadoPaquete['action'] = 'depsolving';
                }
                break;
            case 'depsolving':
                // Realizando resolución de dependencias
                $salidaCmdSolve = $this->_salidaComandoLote('ts solve');
                $salidaCmdTs = $this->_salidaComandoLote('ts list final');
                if (!is_null($salidaCmdSolve) && strpos($salidaCmdSolve, "Success resolving dependencies") !== FALSE) {
                    if (!is_null($salidaCmdTs)) {
                        // Ya es seguro recolectar los paquetes que conforman la transacción
                        $this->_estadoPaquete['status'] = 'idle';
                        if ($this->_estadoPaquete['testonly']) {
                            $this->_estadoPaquete['action'] = 'none';
                            $this->_estadoPaquete['testonly'] = FALSE;
                            $this->_recogerPaquetesTransaccion($salidaCmdTs);
                            $this->_iniciarLoteComandos(array('ts reset'));
                        } else {
                            $this->_estadoPaquete['action'] = 'confirm';
                            $this->_estadoPaquete['testonly'] = FALSE;
                            $this->_recogerPaquetesTransaccion($salidaCmdTs);
                            
                            // Proceder directamente a operación en caso de autoconfirm
                            if ($this->_estadoPaquete['autoconfirm'] &&
                                $this->_estadoPaquete['status'] == 'idle' &&
                                $this->_estadoPaquete['action'] == 'confirm') {
                            	$dummy = NULL;
                                $this->_procesarConfirm($dummy);
                            }
                        }
                        
                    }
                    $this->_inactivarCapturaStderr();
                } else {
                    // Ocurren problemas de resolución de dependencias
                    if (!is_null($salidaCmdSolve) && !is_null($salidaCmdTs)) {
                        // Recoger los errores de dependencias que han ocurrido
                        $this->_estadoPaquete['status'] = 'idle';
                        $this->_estadoPaquete['action'] = 'none';
                        $this->_estadoPaquete['testonly'] = FALSE;
                        $this->_estadoPaquete['warning'] = array();
                        $this->_estadoPaquete['errores'] = array();

                        $this->_recogerPaquetesTransaccion($salidaCmdTs);
                        $this->_estadoPaquete['status'] = 'error';
                        $listaPaquetes = $this->_estadoPaquete['progreso'];
                        $this->_estadoPaquete['progreso'] = array();

                        // El siguiente método funciona en CentOS 5
                        $lineas = explode("\n", $salidaCmdSolve);
                        $listaDepFaltantes = array();
                        foreach ($lineas as $sLinea) {
                            $regs = NULL;
                            
                            $depsrc = NULL; $depmsg = NULL;
                            if (preg_match('/Missing Dependency: (.+) is needed by package (\S+) \(\S+\)/', $sLinea, $regs)) {
                                $depmsg = $regs[0];
                                $depsrc = $regs[1];
                            }
                            
                            if (!is_null($depsrc)) {
                                $listaDepFaltantes[] = $this->_acumularMensajeDependenciaFallida($depmsg, $depsrc);
                            }
                        }

                        // El siguiente método es necesario en Fedora 17
                        $lineas = explode("\n", $this->_stderrBuf);
                        foreach ($lineas as $i => $sLinea) {
                            $regs = NULL;
                            
                            $depsrc = NULL; $depmsg = NULL;
                            if ($i > 0) {
                                $testmsg = $lineas[$i - 1]." ".$sLinea;
                                if (preg_match('/Error: Package: \S+ \(\S+\)\s+Requires: (.+)/', $testmsg, $regs)) {
                                    $depmsg = $regs[0];
                                    $depsrc = $regs[1];
                                }
                            }
                            if (!is_null($depsrc)) {
                                $listaDepFaltantes[] = $this->_acumularMensajeDependenciaFallida($depmsg, $depsrc);
                            }
                        }

                        /* Marcar cada paquete como inicial si el paquete fue pedido como parte del comando add o update */
                        for ($i = 0; $i < count($listaPaquetes); $i++) {
                            $listaPaquetes[$i]['inicial'] = in_array($listaPaquetes[$i]['nombre'], $this->_estadoPaquete['iniciales']);
                            $listaPaquetes[$i]['faltadep'] = array();
                            $listaPaquetes[$i]['requerido'] = array();

                            for ($j = 0; $j < count($listaPaquetes); $j++) {
                                if ($i == $j) continue;
                                
                                /* Verificar si el paquete i-ésimo es dependencia del paquete j-ésimo */
                                $bEsDependencia = FALSE;
                                for ($k = 0; !$bEsDependencia && $k < count($listaPaquetes[$i]['provides']); $k++) {
                                    for ($n = 0; !$bEsDependencia && $n < count($listaPaquetes[$j]['requires']); $n++) {
                                        $prov =& $listaPaquetes[$i]['provides'][$k];
                                        $req =& $listaPaquetes[$j]['requires'][$n];
                                        
                                        /* $req puede tener flags como un comparador, o vacío. Si es vacío, se busca
                                           el valor exacto en $prov, sin bandera. Si tiene bandera, se busca un $prov
                                           que satisfaga el comparador 
                                         */
                                        if ($req['name'] == $prov['name']) {
                                            if ($req['flags'] == '') {
                                                $bEsDependencia = TRUE;
                                            } elseif ($prov['version'] != '' && $req['version'] != '') {
                                                $reqversion = array(
                                                    'epoch' => ($req['epoch'] != '') ? $req['epoch'] : 0, 
                                                    'version' => ($req['version'] != '') ? explode('.', $req['version']) : array(), 
                                                    'release' => ($req['release'] != '') ? explode('.', $req['release']) : array());
                                                $provversion = array(
                                                    'epoch' => ($prov['epoch'] != '') ? $prov['epoch'] : 0, 
                                                    'version' => ($prov['version'] != '') ? explode('.', $prov['version']) : array(), 
                                                    'release' => ($prov['release'] != '') ? explode('.', $prov['release']) : array());
                                                $sComp = 'EQ'; // Se asume al inicio que son iguales
                                                
                                                // Generar comparador que describe $prov COMP $req
                                                if ($provversion['epoch'] < $reqversion['epoch']) $sComp = 'LT';
                                                if ($provversion['epoch'] > $reqversion['epoch']) $sComp = 'GT';
                                                if ($sComp == 'EQ') {
                                                    while (count($reqversion['version']) && count($provversion['version'])) {
                                                        $r = array_shift($reqversion['version']);
                                                        $p = array_shift($provversion['version']);
                                                        if ($p < $r) $sComp = 'LT';
                                                        if ($p > $r) $sComp = 'GT';
                                                    }
                                                    if ($sComp == 'EQ' && count($reqversion['version'])) $sComp = 'LT';
                                                    if ($sComp == 'EQ' && count($provversion['version'])) $sComp = 'GT';
                                                }
                                                if ($sComp == 'EQ') {
                                                    while (count($reqversion['release']) && count($provversion['release'])) {
                                                        $r = array_shift($reqversion['release']);
                                                        $p = array_shift($provversion['release']);
                                                        if ($p < $r) $sComp = 'LT';
                                                        if ($p > $r) $sComp = 'GT';
                                                    }
                                                    if ($sComp == 'EQ' && count($reqversion['release'])) $sComp = 'LT';
                                                    if ($sComp == 'EQ' && count($provversion['release'])) $sComp = 'GT';
                                                }
                                                
                                                // Verificar comparador de $req
                                                switch ($req['flags']) {
                                                case 'GT':  $bEsDependencia = ($sComp == 'GT'); break;
                                                case 'GE':  $bEsDependencia = ($sComp == 'GT' || $sComp == 'EQ'); break;
                                                case 'EQ':  $bEsDependencia = ($sComp == 'EQ'); break;
                                                case 'LE':  $bEsDependencia = ($sComp == 'EQ' || $sComp == 'LT'); break;
                                                case 'LT':  $bEsDependencia = ($sComp == 'LQ'); break;
                                                }
                                            }
                                        }
                                        
                                        if ($bEsDependencia) {
                                            // Marcar que el paquete i-ésimo es requerido por el j-ésimo
                                            $listaPaquetes[$i]['requerido'][] = $j;
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Localizar todos los paquetes que dependen directamente de una dependencia
                        // faltante indicada en $listaDepFaltantes
                        for ($i = 0; $i < count($listaPaquetes); $i++) {
                            for ($j = 0; $j < count($listaPaquetes[$i]['requires']); $j++) {
                                $req =& $listaPaquetes[$i]['requires'][$j];
                                for ($k = 0; $k < count($listaDepFaltantes); $k++) {
                                    if ($req['name'] == $listaDepFaltantes[$k]['name'] && 
                                        $req['flags'] == $listaDepFaltantes[$k]['flags'] &&
                                        $req['epoch'] == $listaDepFaltantes[$k]['epoch'] &&
                                        $req['version'] == $listaDepFaltantes[$k]['version'] &&
                                        $req['release'] == $listaDepFaltantes[$k]['release']) {
                                        
                                        $listaPaquetes[$i]['faltadep'][] = $listaDepFaltantes[$k];
                                    }
                                }
                            }
                        }

                        /* Revisar las dependencias faltantes. Si se encuentra un paquete
                           con dependencias faltantes, se propagan estas dependencias faltantes
                           a todos los paquetes que se listan como que dependen del paquete 
                           examinado. Se termina cuando en una pasada no hay más propagaciones. */
                        $bNuevaDep = TRUE;
                        while ($bNuevaDep) {
                            $bNuevaDep = FALSE;
                            for ($i = 0; $i < count($listaPaquetes); $i++) {
                                if (count($listaPaquetes[$i]['faltadep']) > 0 && count($listaPaquetes[$i]['requerido']) > 0) {
                                    for ($j = 0; $j < count($listaPaquetes[$i]['requerido']); $j++) {                                        
                                        $dep =& $listaPaquetes[$listaPaquetes[$i]['requerido'][$j]];
                                        for ($k = 0; $k < count($listaPaquetes[$i]['faltadep']); $k++) {
                                            if (!in_array($listaPaquetes[$i]['faltadep'][$k], $dep['faltadep'])) {
                                                $bNuevaDep = TRUE;
                                                $dep['faltadep'][] = $listaPaquetes[$i]['faltadep'][$k];
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // Reportar las dependencias que impiden instalación de paquetes objetivo
                        foreach ($listaPaquetes as $infoPaquete) {
                            if ($infoPaquete['inicial'] && count($infoPaquete['faltadep']) > 0) {
                                foreach ($infoPaquete['faltadep'] as $req) {
                                    $sDescDependencia = $req['name'];
                                    $mapaComp = array(
                                        'GT' => '>',
                                        'GE' => '>=',
                                        'EQ' => '=',
                                        'LE' => '<=',
                                        'LT' => '<',
                                    );
                                    if ($req['flags'] != '') {
                                        $sDescDependencia .= ' '.$mapaComp[$req['flags']].' ';
                                        if ($req['epoch'] != '') $sDescDependencia .= $req['epoch'].':';
                                        $sDescDependencia .= $req['version'];
                                        if ($req['release'] != '') $sDescDependencia .= '-'.$req['release'];
                                    }
                                    $this->_estadoPaquete['errores'][] = "TARGET ".$infoPaquete['nombre'].' REQUIRES '.$sDescDependencia;
                                }
                            }
                        }
                        $this->_inactivarCapturaStderr();
                    }
                }
                break;
            case 'downloading':
                // Descargando paquetes. Se monitorea el tamaño del archivo RPM descargado
                $this->_revisarProgresoPaquetes();
                if (strpos(/*$this->_sContenido*/$this->_concatenarSalidaCompletaLote(TRUE), 'Running Transaction Test') !== FALSE) {
                    $this->_estadoPaquete['action'] = 'applying';
                }
                break;
            case 'applying':
                // Aplicando la transacción
                $lineas = explode("\n", $this->_concatenarSalidaCompletaLote(TRUE));
                $iPosPaquete = NULL;
                
                // Resetear el estado de todos los paquetes
                foreach ($this->_estadoPaquete['progreso'] as &$infoPaquete) {
                    if ($infoPaquete['pkgaction'] != 'remove') $infoPaquete['currstatus'] = 'downloaded';
                }
                
                // Verificar cada una de las líneas de instalación
                foreach ($lineas as $sLinea) {
                    $regs = NULL;
                    if (preg_match('/^\s+Installing\s+:\s+(\S+)/', $sLinea, $regs)) {
                        // Instalando un paquete
                        foreach ($this->_estadoPaquete['progreso'] as $iPos => &$infoPaquete) {
                            if ($infoPaquete['nombre'] == $regs[1] && 
                                $infoPaquete['pkgaction'] == 'install' && 
                                $infoPaquete['currstatus'] == 'downloaded') {
                                if (!is_null($iPosPaquete)) {
                                    if ($this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] == 'installing')
                                        $this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] = 'installed';
                                    if ($this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] == 'removing')
                                        $this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] = 'removed';
                                }
                                $iPosPaquete = $iPos;
                                $infoPaquete['currstatus'] = 'installing';
                                break;
                            }
                        }
                    } elseif (preg_match('/^\s+Updating\s+:\s+(\S+)/', $sLinea, $regs)) {
                        // Actualizando un paquete
                        foreach ($this->_estadoPaquete['progreso'] as $iPos => &$infoPaquete) {
                            if ($infoPaquete['nombre'] == $regs[1] && 
                                $infoPaquete['pkgaction'] == 'update' && 
                                $infoPaquete['currstatus'] == 'downloaded') {
                                if (!is_null($iPosPaquete)) {
                                    if ($this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] == 'installing')
                                        $this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] = 'installed';
                                    if ($this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] == 'removing')
                                        $this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] = 'removed';
                                }
                                $iPosPaquete = $iPos;
                                $infoPaquete['currstatus'] = 'installing';
                                break;
                            }
                        }
                    } elseif (preg_match('/^\s+Erasing\s+:\s+(\S+)/', $sLinea, $regs)) {
                        // Removiendo un paquete
                        foreach ($this->_estadoPaquete['progreso'] as $iPos => &$infoPaquete) {
                            if ($infoPaquete['nombre'] == $regs[1] && 
                                $infoPaquete['pkgaction'] == 'remove' && 
                                $infoPaquete['currstatus'] == 'installed') {
                                if (!is_null($iPosPaquete)) {
                                    if ($this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] == 'removing')
                                        $this->_estadoPaquete['progreso'][$iPosPaquete]['currstatus'] = 'removed';
                                }
                                $iPosPaquete = $iPos;
                                $infoPaquete['currstatus'] = 'removing';
                                break;
                            }
                        }
                    } elseif (strpos($sLinea, 'Finished Transaction') === 0 && strpos($sLinea, 'Finished Transaction Test') === FALSE) {
                        $this->_estadoPaquete['status'] = 'idle';
                        $this->_estadoPaquete['action'] = 'none';
                        $this->_estadoPaquete['progreso'] = array();
                        $this->_estadoPaquete['errores'] = array();
                        $this->_estadoPaquete['warning'] = array();
                        $this->_inactivarCapturaStderr();
                    }
                }                
                break;
            }
        }

        return TRUE;
    }

    private function _actualizarStderrYumShell()
    {
        $this->_timestampUltimoUso = time();

        $s = stream_get_contents($this->_procPipes[2]);
        if ($this->_bCapturarStderr) {
            $this->_stderrBuf .= $s;
        }
        while (substr($s, -1) == "\n") $s = substr($s, 0, strlen($s) - 1);
        if ($this->_debug) $this->oMainLog->output("yum(stderr): $s");
        
        if ($this->_bCapturarStderr) switch ($this->_estadoPaquete['action']) {
        case 'reporefresh':
            // Buscar si yum ha terminado de resolver dependencias por errores
            $lineas = explode("\n", $this->_stderrBuf);
            $bDownloadError = FALSE;
            foreach ($lineas as $sLinea) {
                if (0 === strpos($sLinea, 'Error: ')) {
                    $this->oMainLog->output('DEBUG: reporefresh con error');
                    $bDownloadError = TRUE;
                    $this->_estadoPaquete['status'] = 'error';
                    $this->_estadoPaquete['action'] = 'none';
                    $this->_estadoPaquete['progreso'] = array();
                    $this->_estadoPaquete['errores'] = array();
                    $this->_estadoPaquete['warning'] = array();

                    // Esto asume que el contenido luego del mensaje no está fragmentado
                    $this->_inactivarCapturaStderr();
                    $this->_estadoPaquete['errores'][] = $sLinea;
                } elseif ($bDownloadError) {
                    if (trim($sLinea) != '') $this->_estadoPaquete['errores'][] = $sLinea;
                }
            }
            break;
        case 'downloading':
            // Buscar si yum ha terminado de descargar por errores
            $lineas = explode("\n", $this->_stderrBuf);
            $bDownloadError = FALSE;
            foreach ($lineas as $sLinea) {
                if (0 === strpos($sLinea, 'Error: Error Downloading Packages:')) {
                    $bDownloadError = TRUE;
                    $this->_estadoPaquete['status'] = 'error';
                    $this->_estadoPaquete['action'] = 'none';
                    $this->_estadoPaquete['progreso'] = array();
                    $this->_estadoPaquete['errores'] = array();
                    $this->_estadoPaquete['warning'] = array();

                    // Esto asume que el contenido luego del mensaje no está fragmentado
                    $this->_inactivarCapturaStderr();
                } elseif ($bDownloadError) {
                    if (trim($sLinea) != '') $this->_estadoPaquete['errores'][] = $sLinea;
                }
            }
            break;
        }
    }

    private function _acumularMensajeDependenciaFallida($depmsg, $depsrc)
    {
        // TODO: parsear estado de árbol para trazar árbol de dependencias
        $this->_estadoPaquete['errores'][] = $depmsg;
        $sDependencia = $depsrc;
        
        // Se verifica si la dependencia es por una versión de RPM,
        // o por una versión específica. Se usa a propósito el formato
        // de la petición de requires.
        $regs = NULL;
        if (preg_match('/^(\S+)\s+(\S+)\s+(\S+)$/', $sDependencia, $regs)) {
            $sNombreBase = $regs[1];
            $sSimboloComparador = $regs[2];
            $sVersion = $regs[3];
            
            // Elegir comparador adecuado
            $mapaComp = array(
                '>' =>  'GT',
                '>=' => 'GE',
                '=' =>  'EQ',
                '<=' => 'LE',
                '<' =>  'LT',
            );
            $sComparador = isset($mapaComp[$sSimboloComparador]) ? $mapaComp[$sSimboloComparador] : 'EQ';
            $reqDesc = array(
                'name'  =>  $sNombreBase,
                'flags' =>  $sComparador,
                'epoch' =>  NULL,
                'version' =>  NULL,
                'release' =>  NULL,
            );
            
            // Parseo de la cadena de versión
            if (preg_match('/^((\S+):)?(\S+)-(\S+)$/', $sVersion, $regs)) {
                $reqDesc['epoch'] = $regs[2];
                $reqDesc['version'] = $regs[3];
                $reqDesc['release'] = $regs[4];
            } else {
                $reqDesc['version'] = $sVersion;
            }
            
            return $reqDesc;
        } else {
            return array(
                'name'  =>  $sDependencia,
                'flags' =>  NULL,
                'epoch' =>  NULL,
                'version' =>  NULL,
                'release' =>  NULL,
            );
        }
    }

    private function _revisarProgresoPaquetes()
    {
        clearstatcache();
        foreach ($this->_estadoPaquete['progreso'] as &$infoPaquete) {
            if ($infoPaquete['pkgaction'] != 'remove') {
                if (file_exists($infoPaquete['rpmfile'])) {
                    $infoPaquete['descargado'] = filesize($infoPaquete['rpmfile']);
                    $infoPaquete['currstatus'] = ($infoPaquete['descargado'] < $infoPaquete['longitud']) ? 'downloading' : 'downloaded';
                } else {
                    $infoPaquete['descargado'] = 0;
                    $infoPaquete['currstatus'] = 'waiting';
                }
            }
        }
    }

    private function _activarCapturaStderr()
    {
        $this->_bCapturarStderr = TRUE;
        $this->_stderrBuf = '';
    }
    
    private function _inactivarCapturaStderr()
    {
        $this->_bCapturarStderr = FALSE;
        $this->_stderrBuf = '';
    }
    
}
?>
