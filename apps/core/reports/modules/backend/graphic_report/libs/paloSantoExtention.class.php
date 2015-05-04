<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.2-3                                               |
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
  $Id: default.conf.php,v 1.1 2008-09-01 10:09:57 jjvega Exp $ */

//include_once "libs/paloSantoQueue.class.php";

class paloSantoExtention {
    var $_DB;
    var $errMsg;

    function paloSantoExtention(&$pDB)
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
     * Procedimiento para contar las llamadas relacionadas a una extensión, 
     * desglosadas por dirección. 
     * 
     * @param   string  $date_ini   Fecha inicial yyyy-mm-dd
     * @param   string  $date_fin   Fecha final yyyy-mm-dd
     * @param   string  $ext        Extensión a consultar
     * 
     * @return  mixed   NULL en caso de error, o tupla (num_incoming_call, num_outgoing_call)
     */
    function countCallsByExtension($date_ini, $date_fin, $ext)
    {
    	if (trim($ext) == '') {
            $this->errMsg = _tr('Invalid extension');
    		return NULL;
    	}
        $paramSQL = array($ext, $ext, $ext, $ext, $date_ini.' 00:00:00', $date_fin.' 23:59:59');
        $sql = <<<COUNT_CALLS_BY_EXTENSION
SELECT
    SUM(IF(dst = ? OR SUBSTRING_INDEX(SUBSTRING_INDEX(dstchannel,'-',1),'/',-1) = ?, 1, 0))
        AS num_incoming_call,
    SUM(IF(src = ? OR SUBSTRING_INDEX(SUBSTRING_INDEX(channel,'-',1),'/',-1) = ?, 1, 0))
        AS num_outgoing_call
FROM cdr
WHERE calldate BETWEEN ? AND ?
COUNT_CALLS_BY_EXTENSION;
        $result = $this->_DB->getFirstRowQuery($sql, TRUE, $paramSQL);

        if (!is_array($result)) {
            $this->errMsg = $this->_DB->errMsg;
            return NULL;
        }
        return $result;
    }

    function loadExtentions()
    {
        $query = "SELECT id, user FROM devices ORDER BY 1 asc";

        $result = $this->_DB->fetchTable($query, true);

        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }

        return $result;
    }

    function countQueue($queue, $date_ini, $date_fin)
    {
        $query = "SELECT count(*) FROM cdr WHERE dst='$queue' ";

        if( strlen($date_ini) >= 5 ){
            if( strlen($date_fin) <= 5 )
                $query .= " and ( TO_DAYS( DATE(calldate) ) > TO_DAYS( '$date_ini') OR TO_DAYS( DATE(calldate) ) = TO_DAYS( '$date_ini') )";
            else{
                $query .= " and ( TO_DAYS( DATE(calldate) ) > TO_DAYS( '$date_ini') OR TO_DAYS( DATE(calldate) ) = TO_DAYS( '$date_ini') )  ";
                $query .= " and ( TO_DAYS( DATE(calldate) ) < TO_DAYS( '$date_fin') OR TO_DAYS( DATE(calldate) ) = TO_DAYS( '$date_fin') ) ";
            }
        }

        $result = $this->_DB->getFirstRowQuery($query);

        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }

        return $result;
    }

    /**
     * Procedimiento para consultar estadísticas sobre los CDRs de Asterisk, 
     * clasificados por troncal.
     *
     * @param   mixed   $trunk  Nombre de la troncal, o arreglo de troncales.
     * @param   string  $sTipoReporte   'min' para total de segundos entrantes y
     *                                  salientes, o 'numcall' para número de 
     *                                  llamadas entrantes y salientes
     * @param   string  $sFechaInicial  Fecha de inicio de rango yyyy-mm-dd
     * @param   string  $sFechaFinal    Fecha de final de rango yyyy-mm-dd
     *
     * @result  mixed   NULL en caso de error, o una tupla con 1 elemento que es
     *                  tupla de 2 valores para (entrante,saliente)
     */
    function loadTrunks($trunk, $sTipoReporte, $sFechaInicial, $sFechaFinal)
    {
        if (!is_array($trunk)) $trunk = array($trunk);
        $sCondicionSQL_channel = implode(' OR ', array_fill(0, count($trunk), 'channel LIKE ?'));
        $sCondicionSQL_dstchannel = implode(' OR ', array_fill(0, count($trunk), 'dstchannel LIKE ?'));

        /* Se asume que la lista de troncales es válida, y que todo canal
           empieza con la troncal correspondiente */
        if (!function_exists('loadTrunks_troncal2like')) {
            // Búsqueda por DAHDI/1 debe ser 'DAHDI/1-%'
            function loadTrunks_troncal2like($s) { return $s.'-%'; }
        }
        $paramTrunk = array_map('loadTrunks_troncal2like', $trunk);
        
        // Construir la sentencia SQL correspondiente
        switch ($sTipoReporte) {
        case 'min':
            $sPeticionSQL = <<<SQL_LOADTRUNKS_MIN
SELECT 
    IFNULL(SUM(IF(($sCondicionSQL_channel), duration, 0)), 0) AS totIn,
    IFNULL(SUM(IF(($sCondicionSQL_dstchannel), duration, 0)), 0) AS totOut
FROM cdr
WHERE calldate >= ? AND calldate <= ?
SQL_LOADTRUNKS_MIN;
            $paramSQL = array_merge($paramTrunk, $paramTrunk, array($sFechaInicial.' 00:00:00', $sFechaFinal.' 23:59:59'));
            break;
        case 'numcall':
            $sPeticionSQL = <<<SQL_LOADTRUNKS_NUMCALL
SELECT 
    IFNULL(SUM(IF(($sCondicionSQL_channel), 1, 0)), 0) AS numIn,
    IFNULL(SUM(IF(($sCondicionSQL_dstchannel), 1, 0)), 0) AS numOut
FROM cdr
WHERE calldate >= ? AND calldate <= ?
SQL_LOADTRUNKS_NUMCALL;
            $paramSQL = array_merge($paramTrunk, $paramTrunk, array($sFechaInicial.' 00:00:00', $sFechaFinal.' 23:59:59'));
            break;
        default:
            $this->errMsg = '(internal) Invalid report type';
            return NULL;
        }
        $result = $this->_DB->fetchTable($sPeticionSQL, FALSE, $paramSQL);
        if (!is_array($result)) {
            $this->errMsg = '(internal) Failed to fetch stats - '.$this->_DB->errMsg;
            return array();
        }
        return $result;
    }
}
?>
