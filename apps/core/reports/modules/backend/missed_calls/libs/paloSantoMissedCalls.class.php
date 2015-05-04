<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4-18                                               |
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
  $Id: paloSantoMissedCalls.class.php,v 1.1 2011-04-25 09:04:41 Eduardo Cueva ecueva@palosanto.com Exp $ */
class paloSantoMissedCalls{
    var $_DB;
    var $errMsg;

    function paloSantoMissedCalls(&$pDB)
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

    /*HERE YOUR FUNCTIONS*/

    function getNumCallingReport($date_start, $date_end, $filter_field, $filter_value, $sExtension)
    {
	$where = "";
	$arrParam = array();
        if(isset($filter_field) & $filter_field !=""){
            $where = " AND $filter_field like ? ";
	    $arrParam = array("$filter_value%");
	}
	$dates = array($date_start, $date_end);
	$arrParam = array_merge($dates,$arrParam);

	$query   = "select COUNT(*) from cdr where (lastapp = 'Dial' OR lastapp = 'Hangup' OR lastapp = 'Voicemail') AND calldate >= ? AND calldate <= ? $where order by calldate desc;";
        $result=$this->_DB->getFirstRowQuery($query, false, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getCallingReport($date_start, $date_end, $filter_field, $filter_value, $sExtension)
    {
        $paramSQL = array($date_start, $date_end);
        $filtercond = '1';
        if (empty($filter_value) || !in_array($filter_field, array('src', 'dst')))
            $filter_field = NULL;
        if (!is_null($filter_field)) {
        	$filtercond = "cdr.$filter_field LIKE ?";
            $paramSQL[] = "$filter_value%";
        }

        $paramSQL = array_merge($paramSQL, array($date_start, $date_end, $date_start, $date_end));

        // Se require UNION debido a la ausencia de FULL OUTER JOIN
    	$sql = <<<SQL_MISSING_CALLS
(SELECT
    cdr.calldate, 
    IF(TRIM(cdr.src) = '', 'UNKNOWN', TRIM(cdr.src)) AS src, 
    IF(TRIM(cdr.dst) = '', 'UNKNOWN', TRIM(cdr.dst)) AS dst, 
    UCASE(TRIM(cdr.lastapp)) AS lastapp,
    UCASE(TRIM(cdr.lastdata)) AS lastdata,
    cdr.billsec,
    UCASE(TRIM(cdr.disposition)) AS disposition,
    MAX(succ_cdr.maxcalldate) AS maxcalldate
FROM cdr
LEFT JOIN (
    SELECT src, dst, MAX(calldate) AS maxcalldate
    FROM cdr
    WHERE lastapp = 'Dial' AND disposition = 'ANSWERED' AND billsec > 0 AND calldate BETWEEN ? AND ?
    GROUP BY src, dst
) succ_cdr ON ((succ_cdr.src = cdr.src AND succ_cdr.dst = cdr.dst) OR (succ_cdr.src = cdr.dst AND succ_cdr.dst = cdr.src))
WHERE $filtercond
    AND cdr.calldate BETWEEN ? AND ?
    AND (cdr.lastapp = 'Dial' OR cdr.lastapp = 'Hangup' OR cdr.lastapp = 'Voicemail')
    AND (NOT (cdr.lastapp = 'Dial' AND cdr.disposition = 'ANSWERED' AND cdr.billsec > 0))
    AND (succ_cdr.maxcalldate IS NULL OR cdr.calldate > succ_cdr.maxcalldate)
GROUP BY cdr.calldate, cdr.src, cdr.dst, cdr.lastapp, cdr.lastdata, cdr.billsec, cdr.disposition
)
UNION
(
SELECT
    NULL AS calldate,
    IF(TRIM(cdr.src) = '', 'UNKNOWN', TRIM(cdr.src)) AS src, 
    IF(TRIM(cdr.dst) = '', 'UNKNOWN', TRIM(cdr.dst)) AS dst,
    NULL,
    NULL,
    NULL,
    NULL,
    MAX(calldate) AS maxcalldate
FROM cdr
WHERE lastapp = 'Dial' AND disposition = 'ANSWERED' AND billsec > 0 AND calldate BETWEEN ? AND ?
GROUP BY src, dst 
)
ORDER BY calldate DESC
SQL_MISSING_CALLS;
        $result = $this->_DB->fetchTable($sql, true, $paramSQL);

        if ($result == FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }

        return $result;
    }
    
    /**********************************************************************************/
    /*    Manejo de estados:
	  1)    ext1 => call => ext2   
			* lastapp = Dial
			* billsec != 0
			* disposition = ANSWERED
			* status = CONTESTADA
	  2)	ext1 => call => ext2
			* lastapp = Dial
			* billsec = 0
			* disposition = NO ANSWER
			* status = NO CONTESTADA SIN DEJAR VOICEMAIL
	  3)	ext1 => call => ext2
			* lastapp = Hangup
			* billsec <> 0
			* disposition = ANSWERED
			* status = NO CONTESTADA Y DEJANDO VOICEMAIL
	  4)	ext1 => call => ext2
			* lastapp = VoiceMail
			* billsec <> 0
			* disposition = ANSWERED
			* status = NO CONTESTADA Y COLGANDO CUANDO ENTRA EN EL VOICEMAIL
	  5)    ext1 => call => ext2
			* lastapp = Dial
			* billsec = 0
			* disposition = ANSWERED
			* status = SE CONTESTA UNAS MILESIMAS DE SEGUNDOS ANTES DE CERRAR
	  6)	ext1 => call => ext2
			* lastapp = Hangup
			* billsec = 0
			* disposition = BUSY
			* status = SE CONTESTA UNAS MILESIMAS DE SEGUNDOS ANTES DE CERRAR
    /*
    /* funcion que recibe el arreglo de datos de llamadas y retorna el arreglo con los
    /* datos que seran mostrados en el reporte
    /**********************************************************************************/

    function showDataReport(&$arrData)
    {
     	/* El recordset en $arrData contiene los CDRs de interés, en orden de
         * fecha de CDR descendente. El reporte a producir debe de tener una
         * fila por cada combinación de número fuente y número destino para el
         * cual, posterior a la última llamada exitosa entre los dos números
         * (en cualquier sentido), la fuente haya intentado llamar al destino
         * y fallado. Se lleva la cuenta del número de intentos, así como la
         * fecha y razón de fallo del último intento. */
        
        /* PASO 1: se recolecta la fecha de la llamada exitosa más reciente 
         * entre una fuente y un destino. Para este paso A-->B y B-->A son 
         * igualmente válidos. Se asume que calldate está en formato 
         * yyyy-mm-dd hh:mm:ss y que por lo tanto la comparación de cadena es
         * idéntica a la comparación cronológica. */
        $llamadasExitosas = array();
        foreach ($arrData as $tupla) {
            
        	// Esto asume ordenamiento descendiente por calldate
            if ($tupla['src'] < $tupla['dst']) {
            	$k1 = $tupla['src'];
                $k2 = $tupla['dst'];
            } else {
                $k1 = $tupla['dst'];
                $k2 = $tupla['src'];
            }
            if (!is_null($tupla['maxcalldate'])) {
                if (!(isset($llamadasExitosas[$k1]) && isset($llamadasExitosas[$k1][$k2]) && $tupla['maxcalldate'] < $llamadasExitosas[$k1][$k2])) {
                    $llamadasExitosas[$k1][$k2] = $tupla['maxcalldate'];
                }
            }
        }
        
        /* PASO 2: se buscan todas las llamadas fallidas posteriores a la última
         * llamada exitosa anotada, o que no hayan tenido llamada exitosa en el
         * intervalo. */
        $reportPos = array();
        $report = array();
        foreach ($arrData as $tupla) if (!is_null($tupla['calldate'])) {
        	if (!($tupla['lastapp'] == 'DIAL' && $tupla['disposition'] == 'ANSWERED' && $tupla['billsec'] > 0)) {
        		// Buscar fecha de la última llamada exitosa, si existe
                $src = $tupla['src'];
                $dst = $tupla['dst'];
                if ($src < $dst) {
                    $k1 = $src;
                    $k2 = $dst;
                } else {
                    $k1 = $dst;
                    $k2 = $src;
                }
                $llamada_exitosa = NULL;
                if (isset($llamadasExitosas[$k1]) && isset($llamadasExitosas[$k1][$k2]))
                    $llamada_exitosa = $llamadasExitosas[$k1][$k2];
                
                if (is_null($llamada_exitosa) || $llamada_exitosa < $tupla['calldate']) {
                	// Este CDR debe contribuir al reporte de llamadas fallidas
                    if (!isset($reportPos[$src]) || !isset($reportPos[$src][$dst])) {
                        if (($tupla['lastapp'] == 'DIAL' && $tupla['billsec'] == 0 && $tupla['disposition'] == 'NO ANSWER') ||
                            ($tupla['lastapp'] == 'DIAL' && $tupla['billsec'] == 0 && $tupla['disposition'] == 'ANSWERED') ||
                            ($tupla['disposition'] == 'BUSY')) {
                            $failcause = _tr('NO ANSWER');
                        } elseif (($tupla['lastapp'] == 'HANGUP' || $tupla['lastapp'] == 'VOICEMAIL') && $tupla['billsec'] > 0 && $tupla['disposition'] == 'ANSWERED') {
                            $failcause = _tr('NO ANSWER - VOICEMAIL');
                        } else {
                        	$failcause = _tr($tupla['disposition']);
                        }
                    	$reportPos[$src][$dst] = count($report);
                        $report[] = array(
                            date('d-M-Y H:i:s', strtotime($tupla['calldate'])),
                            (($src == 'UNKNOWN') ? _tr('UNKNOWN') : $src),
                            (($dst == 'UNKNOWN') ? _tr('UNKNOWN') : $dst),
                            $this->getTimeToLastCall($tupla['calldate']),   // tiempo desde fallo más reciente hasta ahora
                            0,      // Número de fallas
                            $failcause,
                        );
                    }
                    $report[$reportPos[$src][$dst]][4]++;   // Contador de fallas
                }
        	}
        }
        return $report;
    }

    /**********************************************************************************/
    /* Escala de tiempos en seguntos:
    /*		1 minuto => 60 segundos
    /*		1 hora   => 3600 segundos
    /*		1 dia    => 86400 segundos
    /*		1 mes	 => 2592000 segundos
    /*		1 año	 => 31104000 segundos
    /**********************************************************************************/
    private function getTimeToLastCall($time)
    {
	$anios    = "";
	$meses    = "";
	$dias     = "";
	$horas    = "";
	$minutos  = "";
	$segundos = "";
	$result   = "";
	$now = strtotime(date('Y-m-d H:i:s'));
	$time = $now - strtotime($time);
	if($time >= 31104000){//esta en años
	    //convirtiendo segundos en años
	    $anios    = ($time/31104000);
	    //convirtiendo años decimales a meses
	    $meses    = ($anios - floor($anios)) * 12;
	    //convirtiendo meses decimales a dias
	    $dias     = ($meses - floor($meses)) * 30;
	    //convirtiendo dias decimales a horas
	    $horas    = ($dias - floor($dias)) * 24;
	    //convirtiendo horas decimales a minutos
	    $minutos  = ($horas - floor($horas)) * 60;
	    //convirtiendo minutos decimales a segundos
	    $segundos = ($minutos - floor($minutos)) * 60;
	    $result   = floor($anios)." "._tr("year(s)")." ".floor($meses)." "._tr("month(s)")." ".floor($dias)." "._tr("day(s)")." ".floor($horas)." "._tr("hour(s)")." ".floor($minutos)." "._tr("minute(s)")." ".floor($segundos)." "._tr("second(s)");
	}elseif($time < 31104000 && $time >= 2592000){//esta en meses
	    //convirtiendo segundos a meses
	    $meses    = ($time/2592000);
	    //convirtiendo meses decimales a dias
	    $dias     = ($meses - floor($meses)) * 30;
	    //convirtiendo dias decimales a horas
	    $horas    = ($dias - floor($dias)) * 24;
	    //convirtiendo horas decimales a minutos
	    $minutos  = ($horas - floor($horas)) * 60;
	    //convirtiendo minutos decimales a segundos
	    $segundos = ($minutos - floor($minutos)) * 60;
	    $result   = floor($meses)." "._tr("month(s)")." ".floor($dias)." "._tr("day(s)")." ".floor($horas)." "._tr("hour(s)")." ".floor($minutos)." "._tr("minute(s)")." ".floor($segundos)." "._tr("second(s)");
	}elseif($time < 2592000 && $time >= 86400){//esta en dias
	    //convirtiendo segundos a dias
	    $dias     = ($time/86400);
	    //convirtiendo dias decimales a horas
	    $horas    = ($dias - floor($dias)) * 24;
	    //convirtiendo horas decimales a minutos
	    $minutos  = ($horas - floor($horas)) * 60;
	    //convirtiendo minutos decimales a segundos
	    $segundos = ($minutos - floor($minutos)) * 60;
	    $result   = floor($dias)." "._tr("day(s)")." ".floor($horas)." "._tr("hour(s)")." ".floor($minutos)." "._tr("minute(s)")." ".floor($segundos)." "._tr("second(s)");
	}elseif($time < 86400 && $time >= 3600){//esta en horas
	    //convirtiendo segundos a horas
	    $horas    = ($time/3600);
	    //convirtiendo horas decimales a minutos
	    $minutos  = ($horas - floor($horas)) * 60;
	    //convirtiendo minutos decimales a segundos
	    $segundos = ($minutos - floor($minutos)) * 60;
	    $result   = floor($horas)." "._tr("hour(s)")." ".floor($minutos)." "._tr("minute(s)")." ".floor($segundos)." "._tr("second(s)");
	}elseif($time < 3600 && $time >= 60){//esta en minutos
	    //convirtiendo segundos a minutos
	    $minutos  = ($time/60);
	    //convirtiendo minutos decimales a segundos
	    $segundos = ($minutos - floor($minutos)) * 60;
	    $result   = floor($minutos)." "._tr("minute(s)")." ".floor($segundos)." "._tr("second(s)");
	}else{//esta en segundo
	    $result   = floor($time)." "._tr("second(s)");
	}
	return $result;
    }

    function getDataByPagination($arrData, $limit, $offset)
    {
	$arrResult = array();
	$limitInferior = "";
	$limitSuperior = "";
	if($offset == 0){
	    $limitInferior = $offset;
	    $limitSuperior = $offset + $limit -1;
	}else{
	    $limitInferior = $offset + 1;
	    $limitSuperior = $offset + $limit + 1;
	}
	$cont = 0;
	foreach($arrData as $key => $value){
	    if($key > $limitSuperior){
		$cont = 0;
		break;
	    }
	    if($key >= $limitInferior & $key <= $limitSuperior){
		$arrResult[]=$arrData[$key]; //echo $key."<br />";
	    }

	}
	//echo "limit: $limit , offset $offset , $limitInferior-$limitSuperior   ";
	//echo count($arrResult);
	return $arrResult;
    }
}
?>
