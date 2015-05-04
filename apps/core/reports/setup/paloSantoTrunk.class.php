<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
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
  $Id: paloSantoTrunk.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ */

if (isset($arrConf['basePath'])) {
    include_once($arrConf['basePath'] . "/libs/paloSantoDB.class.php");
} else {
    include_once("libs/paloSantoDB.class.php");
}

class paloTrunk {

    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function paloTrunk(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }


    /**
     * Procedimiento para guardar un arreglo de trunks para billing
     *
     * @param array    $listaTrunks       lista trunks para billing
     *
     * @return bool     VERDADERO si se guardaron correctamente, FALSO en error
     */
    function saveTrunksBill($listaTrunks)
    {
        $bExito = FALSE;
        if (!is_array($listaTrunks)) {
            $this->errMsg = "Values for trunks are invalid";
        } else {
            foreach ($listaTrunks as $trunk){
                $sPeticionSQL = paloDB::construirInsert(
                    "trunk_bill",
                    array(
                        "trunk"       =>  paloDB::DBCAMPO($trunk),
                    )
                );
                if ($this->_DB->genQuery($sPeticionSQL)) {
                    $bExito = TRUE;
                } else {
                    $this->errMsg = $this->_DB->errMsg;
                }
             }
         }

        return $bExito;
    }

    /**
     * Procedimiento para borrar una lista de trunks para billing
     *
     * @param array   $listaTrunks 
     *
     * @return bool VERDADERO si se pudieron borrar correctamente
     */
    function deleteTrunksBill($listaTrunks)
    {
        $bExito = FALSE;
        if (!is_array($listaTrunks)) {
            $this->errMsg = "Values for trunks are invalid";
        } 
        else {
            $this->errMsg = "";
            foreach ($listaTrunks as $trunk){
                $sPeticionSQL = 
                    "DELETE FROM trunk_bill WHERE trunk = ".paloDB::DBCAMPO($trunk);
                $bExito = TRUE;
                $bExito = $this->_DB->genQuery($sPeticionSQL);
                if (!$bExito) {print $sPeticionSQL;
                    $this->errMsg = $this->_DB->errMsg;
                    break;
                }
            }

        }
        return $bExito;
    }

    function getTrunksBill()
    {
        $trunks_bill = array();

        $this->errMsg = "";
        $sPeticionSQL = 
            "SELECT * FROM trunk_bill ";

        $arr_result =& $this->_DB->fetchTable($sPeticionSQL);
        if (!is_array($arr_result)) {
            $arr_result = FALSE;
            $this->errMsg = $this->_DB->errMsg;
        }else
        {
            foreach ($arr_result as $trunk)
                $trunks_bill[]=$trunk[0];
        }

        return $trunks_bill;
    }

    /**
     * Method to parse chan_dahdi file and resolve his group to trunks
     *
     * @param    string $chan_dahdi_file     chan_dahdi configuration file
     * @callback array  $grupos          group list
     *
     * @return   array  $troncales       array with resolved group
     */

    function getExtendedTrunksBill(&$grupos, $chan_dahdi_file='/etc/asterisk/chan_dahdi.conf')
    {
       $troncales=NULL;
       //leer el archivo /etc/chan_dahdi.conf para poder reemplazar para DAHDI g#  con los respectivos canales
       $ultGrupo="";

       if (file_exists($chan_dahdi_file)){
           $contenido_archivo=file($chan_dahdi_file);
           foreach ($contenido_archivo as $linea){
               if (preg_match("/^(group|channel[[:space:]]*)=([[:space:]]*.*)/",$linea,$regs)){
                   $regs_key=trim($regs[1]);
                   $regs_value=trim($regs[2]);
                   if ($regs_key=="group") $ultGrupo=$regs_value;
                   if ($regs_key=="channel"){
                       if (isset($ultGrupo)&&$ultGrupo!=""){
                           $channel=explode(',',$regs_value);
                           foreach ($channel as $item){
                              if ($item!=""){
                                   $item   = trim(preg_replace("%>| %","",$item));
                                   $range  = explode('-',$item);
                                   for ($i = min($range);$i<=max($range);$i++) {
                                        $canales[$ultGrupo][]=$i;
                                        $grupos[$i]=$ultGrupo;
                                   }
                              }
                           }
                       }
                   }
               }
           }
       }

       //reemplazo el id del grupo por el valor
       foreach ($this->getTrunksBill() as $trunkBill)
       {
           // Sólo los grupos de puertos DAHDI pueden tener un precio
           //if (substr($tupla[1], 0, 5) == 'DAHDI' && $tupla[1]{4} != 'g') continue;  

           if (preg_match("/^DAHDI\/g([[:digit:]]+)/",$trunkBill,$regs2))
           {
               $id_group=$regs2[1];
               if (isset($canales[$id_group])){
                  foreach($canales[$id_group] as $canal)
                   $troncales[]="DAHDI/$canal";
               }
           }else
               $troncales[]=$trunkBill;
       }
        return $troncales;
    }

}


/**
* Procedimiento para obtener el listado de los trunks existentes. 
*
* @return array    Listado de trunks en el siguiente formato, o FALSE en caso de error:
*  array(
*      array(variable, valor),
*      ...
*  )
*/

function getTrunks($oDB)
{
    $arrResult = false;
    $arrTrunk = array();

	 $arr_result =& $oDB->fetchTable("SHOW TABLES LIKE 'trunks'");
	 if (!is_array($arr_result)) {
            //$this->errMsg = $this->_DB->errMsg;
            $arrResult = FALSE;
	 }
	 else{
			 if (count($arr_result) > 0) { // si se usa freepbx 2.6
					$sPeticionSQL = "select trunkid, concat(if(tech='iax','IAX2',upper(tech)),'/',channelid) as value from trunks";
					$arrResult =& $oDB->fetchTable($sPeticionSQL);
					if (is_array($arrResult) && count($arrResult)>0) {
						foreach($arrResult as $key => $trunk){
									$tmpTrunk = str_replace("ZAP","DAHDI",$trunk); //para soportar dahdi, freepbx aun conserva el formato ZAP y esto es para entender q se usa dahdi
									//$tmpTrunk = str_replace("IAX","IAX2",$trunk); //para iax a iax2
									$tmpTrunk = str_replace("AMP:","",$tmpTrunk);
                                    $tmpTrunk = str_replace('CUSTOM/',"",$tmpTrunk);
                                    $arrTrunk[$key] = str_replace('$OUTNUM$@',"",$tmpTrunk);
						}
						return $arrTrunk;
					}
			 }
			 else{// si se usa freepbx 2.5
					$sPeticionSQL =
								"SELECT * FROM globals ".
								"WHERE variable LIKE 'OUT\\\_%' ".
								"ORDER BY RIGHT( variable, LENGTH( variable ) - 4 )+0";
					$arrResult =& $oDB->fetchTable($sPeticionSQL);
					// si se esta usando freepbx 2.5 o menor a 2.5
					if (is_array($arrResult) && count($arrResult)>0) {
						foreach($arrResult as $key => $trunk){
									$tmpTrunk = str_replace("ZAP","DAHDI",$trunk); //para soportar dahdi, freepbx aun conserva el formato ZAP y esto es para entender q se usa dahdi
									$tmpTrunk = str_replace("AMP:","",$tmpTrunk);
                                    $tmpTrunk = str_replace('CUSTOM/',"",$tmpTrunk);
                                    $arrTrunk[$key] = str_replace('$OUTNUM$@',"",$tmpTrunk);
						}
						return $arrTrunk;
    				}
			 }
	 }
    return false;
}

/**
 * Procedimiento para listar todos los grupos de troncales DAHDI que han sido
 * definidos. Este procedimiento requiere que Asterisk esté en ejecución en el
 * sistema y que soporte el comando "dahdi show channels group N".
 *
 * @return  mixed   Arreglo de la siguiente forma:
 *  array(0 => array("DAHDI/1", "DAHDI/2", "DAHDI/3"), 1 => array("DAHDI/4", "DAHDI/5", "DAHDI/6"))
 */
function getTrunkGroupsDAHDI()
{
    require_once '/var/lib/asterisk/agi-bin/phpagi-asmanager.php';
    require_once 'libs/paloSantoConfig.class.php';
    
    // Obtener las credenciales y abrir la conexión Asterisk
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);
    $astman = new AGI_AsteriskManager();
    if (!$astman->connect('localhost', $arrConfig['AMPMGRUSER']['valor'], $arrConfig['AMPMGRPASS']['valor'])) {
        // No se puede conectar a AMI, se intenta parsear configuración
        return $grupos = getTrunkGroupsDAHDI_config();
    } else {
        /*
           Chan Extension  Context         Language   MOH Interpret        Blocked    State     
              1            from-pstn                  default                         In Service
              2            from-pstn                  default                         In Service
              3            from-pstn                  default                         In Service
              4            from-pstn                  default                         In Service
        */
        // Se conoce que los números de grupo van de 0 a 63
        $grupos = array();
        $bSoportado = TRUE; // Se asume que el comando soporta listar por grupos
        for ($iGrupo = 0; $iGrupo < 64 && $bSoportado; $iGrupo++) {
            $r = $astman->Command("dahdi show channels group $iGrupo");
            if (isset($r['data'])) {
                $lineas = explode("\n", $r['data']);
                foreach ($lineas as $sLinea) {
                    /* Si una línea empieza con Usage, entonces la versión de 
                       Asterisk no soporta la extensión "group N" del comando
                       "dahdi show channels" */
                    if (strpos($sLinea, 'Usage') === 0) {
                        $bSoportado = FALSE;
                        break;
                    }                    
                    if (preg_match('/^\s+(\d+)/', $sLinea, $regs))
                        $grupos[$iGrupo][] = 'DAHDI/'.$regs[1];
                }
            }
        }
        $astman->disconnect();        

        if (!$bSoportado) {
            // Comando AMI no soportado, se intenta parsear configuración
            $grupos = getTrunkGroupsDAHDI_config();
        }

        return $grupos;
    }
}

/**
 * Procedimiento para listar todos los grupos de troncales DAHDI que han sido
 * definidos. Este procedimiento extrae la información requerida de los archivos
 * de configuración de Asterisk.
 *
 * @return  mixed   Arreglo de la siguiente forma:
 *  array(0 => array("DAHDI/1", "DAHDI/2", "DAHDI/3"), 1 => array("DAHDI/4", "DAHDI/5", "DAHDI/6"))
 */
function getTrunkGroupsDAHDI_config()
{
    $listaArchivos = array(
        'chan_dahdi.conf',
    );
    $listaVisitada = array();
    $sDirConfigAsterisk = '/etc/asterisk/';

    $grupos = array();
    $iGrupo = array();  // Un canal puede pertenecer a múltiples grupos
    while (count($listaArchivos) > 0) {
        $sArchivo = array_shift($listaArchivos);
        array_push($listaVisitada, $sArchivo);
        $sRuta = $sDirConfigAsterisk.$sArchivo;
        if (file_exists($sRuta)) {
            foreach (file($sRuta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $sLinea) {
                $regs = NULL;
                if (preg_match('/^#include\s+(.+\.conf)/', $sLinea, $regs)) {
                    /* Se ha encontrado un #include. Si no ha sido visto antes,
                       se agrega al final de la lista de archivos a procesar */
                    if (!in_array($regs[1], $listaVisitada))
                        array_push($listaArchivos, $regs[1]);
                } elseif (preg_match('/^\s*group\s*=\s*(.*)/', $sLinea, $regs)) {
                    /* Se ha encontrado inicio de grupo: group = a,b,c */
                    $listaGrupos = preg_split('/[\s,]+/', $regs[1]);
                    $iGrupo = array();
                    foreach ($listaGrupos as $i) {
                        if (preg_match('/^\d+$/', $i)) $iGrupo[] = (int)$i;
                    }
                } elseif (preg_match('/^\s*channel\s*=>\s*(\d+)(\-(\d+))?/', $sLinea, $regs)) {
                    /* Se ha encontrado channel => a[-b]. Se deben colocar los
                       números de troncal en todos los grupos indicados por 
                       el arreglo $iGrupo */
                    $iCanalInicio = $iCanalFinal = (int)$regs[1];
                    if (isset($regs[3])) $iCanalFinal = (int)$regs[3];
                    $canales = ($iCanalInicio <= $iCanalFinal) 
                        ? range((int)$iCanalInicio, (int)$iCanalFinal) 
                        : array();
                    
                    /* Si hay canales, se combinan dentro de todos los grupos */
                    if (count($canales) > 0) foreach ($iGrupo as $i) {
                        if (!isset($grupos[$i])) $grupos[$i] = array();
                        $grupos[$i] = array_merge($grupos[$i], $canales);
                    }
                }
            }
        }
    }

    if (!function_exists('getTrunkGroupsDAHDI_config_dahdiformat')) {
        function getTrunkGroupsDAHDI_config_dahdiformat($i) { return "DAHDI/$i"; }
        function getTrunkGroupsDAHDI_config_mapdahdiformat($a) {
            return array_values(array_map('getTrunkGroupsDAHDI_config_dahdiformat', array_unique($a)));
        }
    }
    return array_map('getTrunkGroupsDAHDI_config_mapdahdiformat', $grupos);
}
?>
