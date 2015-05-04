<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.4-1                                                |
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
  $Id: paloSantoReportCall.class.php,v 1.1 2009-01-06 09:01:38 jvega jvega@palosanto.com Exp $ */

class paloSantoReportCall {
    var $_DB_cdr;
    var $errMsg;

    function paloSantoReportCall(&$pDB_cdr, &$pDB_billing=null)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB_cdr)) {
            $this->_DB_cdr =& $pDB_cdr;
            $this->errMsg = $this->_DB_cdr->errMsg;
        } else {
            if ($pDB_cdr == '') {
                $pDB_cdr = generarDSNSistema('asteriskuser', 'asteriskcdrdb');
            }

            $dsn = (string)$pDB_cdr;
            $this->_DB_cdr = new paloDB($dsn);

            if (!$this->_DB_cdr->connStatus) {
                $this->errMsg = $this->_DB_cdr->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    function ObtainNumberDevices($type, $value)
    {
        $paramSQL = array();
        $condWhere = '';
        if ($type == 'Ext' && !empty($value)) {
        	$paramSQL[] = $value;
            $condWhere = 'WHERE id = ?';
        }
        if ($type == 'User' && !empty($value)) {
        	$paramSQL[] = $value.'%';
            $condWhere = 'WHERE description LIKE ?';
        }
        $result = $this->_DB_cdr->getFirstRowQuery("SELECT COUNT(*) AS N FROM asterisk.devices $condWhere", FALSE, $paramSQL);

        if ($result == FALSE) {
            $this->errMsg = $this->_DB_cdr->errMsg;
            return 0;
        }
        return $result[0];
    }

    /**
     * Procedimiento para obtener la lista de resumen de actividad de las 
     * extensiones presentes en el sistema.
     * 
     * @param   int     $limit      Número máximo de registros a devolver
     * @param   int     $offset     Registro desde el cual empezar a reportar
     * @param   string  $date_ini   Fecha de inicio del rango a reportar (yyyy-mm-dd hh:mm:ss)
     * @param   string  $date_end   Fecha de final del rango a reportar (yyyy-mm-dd hh:mm:ss)
     * @param   string  $type       Si !empty, tipo de filtro a aplicar sobre valores (Ext, User)
     * @param   string  $value      $i !empty($type), valor del filtro a aplicar
     * @param   string  $order_by   Columna (1..6) por la cual se debe de ordenar
     * @param   string  $order_type 'asc' o 'desc'
     * 
     * @return  mixed   NULL en error, o recordset en éxito
     */
    function ObtainReportCall($limit, $offset, $date_ini, $date_end, $type, $value, $order_by, $order_type="desc")
    {
        // Validación de parámetros
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $regexp_date = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
        if (!preg_match($regexp_date, $date_ini)) $date_ini = date('Y-m-d H:i:s');
        if (!preg_match($regexp_date, $date_end)) $date_end = date('Y-m-d H:i:s');
        if ($date_ini > $date_end) { $t = $date_ini; $date_ini = $date_end; $date_end = $t; }
        
        if (empty($value)) $type = NULL;
        if (!in_array($type, array('Ext', 'User'))) $type = NULL;
        
        $order_by = (int)$order_by;
        if (!($order_by >= 1 && $order_by <= 6)) $order_by = 1;
        
        if (!in_array($order_type, array('asc', 'desc'))) $order_type = 'desc';

        $paramSQL = array($date_ini, $date_end);
        $filter = '1';
        if ($type == 'Ext') {
        	$filter = 'd.id LIKE ?';
            $paramSQL[] = "$value%";
        }
        if ($type == 'User') {
            $filter = 'd.description LIKE ?';
            $paramSQL[] = "$value%";
        }

        /* La expresión anidada SUBSTRING_INDEX captura sobre un canal en formato
         * SIP/1064-xxxxxxx la parte de la extensión (1064) para ser incluida en
         * la comparación. Los IF en las columnas se requieren porque la condición
         * del LEFT JOIN encuentra la extensión en src o dst pero la suma es 
         * condicional a cuál de los dos lados está.
         * 
         * Se excluyen explícitamente los canales Local/1064@from-internal-xxxxxxx
         */
        $sql = <<<SQL_DEVICES
SELECT d.id AS extension, d.description AS user_name,
    SUM(IF(calls.dst = d.id OR SUBSTRING_INDEX(SUBSTRING_INDEX(dstchannel,'-',1),'/',-1) = d.id, 1, 0))
        AS num_incoming_call,
    SUM(IF(calls.src = d.id OR SUBSTRING_INDEX(SUBSTRING_INDEX(channel,'-',1),'/',-1) = d.id, 1, 0))
        AS num_outgoing_call,
    SUM(IF(calls.dst = d.id OR SUBSTRING_INDEX(SUBSTRING_INDEX(dstchannel,'-',1),'/',-1) = d.id, calls.billsec, 0))
        AS duration_incoming_call,
    SUM(IF(calls.src = d.id OR SUBSTRING_INDEX(SUBSTRING_INDEX(channel,'-',1),'/',-1) = d.id, calls.billsec, 0))
        AS duration_outgoing_call
FROM asterisk.devices d
LEFT JOIN (SELECT * FROM asteriskcdrdb.cdr WHERE calldate BETWEEN ? AND ?) calls
    ON (
        calls.src = d.id OR SUBSTRING_INDEX(SUBSTRING_INDEX(channel,'-',1),'/',-1) = d.id 
        OR
        calls.dst = d.id OR SUBSTRING_INDEX(SUBSTRING_INDEX(dstchannel,'-',1),'/',-1) = d.id
    )
WHERE $filter
GROUP BY d.id
ORDER BY $order_by $order_type
LIMIT ? OFFSET ?
SQL_DEVICES;
        $paramSQL[] = $limit;
        $paramSQL[] = $offset;

        $result = $this->_DB_cdr->fetchTable($sql, true, $paramSQL);

        $this->errMsg = '';
        if($result == FALSE){
            $this->errMsg = $this->_DB_cdr->errMsg;
            return array();
        }
        return $result;
    }

    //PARA PLOT3D
    function callbackTop10Salientes($date_ini, $date_end, $ext)
    {
        $data = $this->_listarTopN($date_ini, $date_end, $ext, 10, 'src');
        return $this->_formatPlot3d(_tr('Top 10 (Outgoing) ext')." ".$ext, $ext, $data['data'], $data['all'], $data['total']);
    }

////////
    function callbackTop10Entrantes($date_ini, $date_end, $ext)
    {
        $data = $this->_listarTopN($date_ini, $date_end, $ext, 10, 'dst');
        foreach (array_keys($data['data']) as $i) {
            if ($data['data'][$i][1] == '') $data['data'][$i][1] = _tr('External #');
        }
        return $this->_formatPlot3d(_tr('Top 10 (Incoming) ext')." ".$ext, $ext, $data['data'], $data['all'], $data['total']);
    }

    private function _listarTopN($date_ini, $date_end, $ext, $n, $sel)
    {
        if (!in_array($sel, array('src', 'dst'))) $sel = 'src';
        if ($sel == 'src') {
        	$target = 'dst';
            $selchannel = 'channel';
        } else {
            $target = 'src';
            $selchannel = 'dstchannel';
        }
        
    	$sql = <<<TOP_N_LLAMADAS
SELECT COUNT(*) AS num, $target FROM cdr
WHERE calldate BETWEEN ? AND ?
    AND ($sel = ? OR SUBSTRING_INDEX(SUBSTRING_INDEX($selchannel,'-',1),'/',-1) = ?)
GROUP BY $target
ORDER BY num DESC
TOP_N_LLAMADAS;
        $paramSQL = array($date_ini, $date_end, $ext, $ext);
        $result = $this->_DB_cdr->fetchTable($sql, false, $paramSQL);
        if (!is_array($result)){
            $this->errMsg = $this->_DB_cdr->errMsg;
            print "Errmsg: ".$this->errMsg;
            return array();
        }
        
        $data = array(
            'data'  =>  array(),
            'total' =>  0,
            'all'   =>  0,
        );
        for ($i = 0; $i < count($result); $i++) {
        	$data['all'] += $result[$i][0];
            if ($i < $n) {
            	$data['total'] += $result[$i][0];
                $data['data'][] = $result[$i];
            }
        }
        return $data;
    }


    private function _formatPlot3d($title, $ext, &$result, $num_total, $numTopCalls)
    {
        if($num_total > 0)
            $numCallNoTop = $num_total - $numTopCalls;
        $arrColor = array('blue','red','yellow','brown','green','orange','pink','purple','gray','white','violet');

        $arrT = array();
        $i = 0;
        foreach( $result as $num => $arrR ){
            if($num_total <= 0){
                $arrT["DAT_$i"] = array('VALUES' => array('VALUE'=>0),
                                        'STYLE'  => array('COLOR'=>$arrColor[$i], 'LEYEND'=>" (0 "._tr('calls').")"));
                break;
            }else{
                if($arrR[0]==1){
                    $arrT["DAT_$i"] = array('VALUES' => array('VALUE'=>$arrR[0]),
                                            'STYLE'  => array('COLOR'=>$arrColor[$i], 'LEYEND'=>"$arrR[1] ($arrR[0] "._tr('call').")"));
                }else{
                    $arrT["DAT_$i"] = array('VALUES' => array('VALUE'=>$arrR[0]),
                                            'STYLE'  => array('COLOR'=>$arrColor[$i], 'LEYEND'=>"$arrR[1] ($arrR[0] "._tr('calls').")"));
                }
            }
            $i++;
        }
        
        if($num_total > 0){
            if($numCallNoTop == 1){
                $arrT["DAT_$i"] = array('VALUES' => array('VALUE'=>$numCallNoTop),
                                        'STYLE'  => array('COLOR'=>$arrColor[10], 'LEYEND'=>_tr('Other calls')." (".$numCallNoTop." "._tr('call').")"));
            }else{
                $arrT["DAT_$i"] = array('VALUES' => array('VALUE'=>$numCallNoTop),
                                        'STYLE'  => array('COLOR'=>$arrColor[10], 'LEYEND'=>_tr('Other calls')." (".$numCallNoTop." "._tr('calls').")"));
            }
        }
        

        return array( 
            'ATTRIBUTES' => array(
                //NECESARIOS
                'TITLE'   => $title,
                'TYPE'    => 'plot3d',
                'SIZE'    => "700,250", 
                'MARGIN'  => "5,70,15,20",
            ),

            'MESSAGES'  => array(
                'ERROR' => 'Error', 
                'NOTHING_SHOW' => _tr('No data to display')
            ),
            //DATOS A DIBUJAR
            'DATA' => $arrT );
    }

    function Sec2HHMMSS($sec)
    {
        $HH = '00'; $MM = '00'; $SS = '00';

        if($sec >= 3600){ 
            $HH = (int)($sec/3600);
            $sec = $sec%3600; 
            if( $HH < 10 ) $HH = "0$HH";
        }

        if( $sec >= 60 ){ 
            $MM = (int)($sec/60);
            $sec = $sec%60;
            if( $MM < 10 ) $MM = "0$MM";
        }

        $SS = $sec;
        if( $SS < 10 ) $SS = "0$SS";

        return "{$HH}h. {$MM}m. {$SS}s";
    }
}
?>