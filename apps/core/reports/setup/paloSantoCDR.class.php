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
  $Id: paloSantoCDR.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ */

include_once "libs/paloSantoPBX.class.php";
class paloSantoCDR
{
    private $_DB;
    public $errMsg;
    
    function paloSantoCDR(&$pDB)
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
    
    private function _construirWhereCDR($param)
    {
        global $arrConf;
        $pPBX= new paloAsteriskDB($arrConf['elastix_dsn']["elastix"]);
        $condSQL = array();
        $paramSQL = array();

        if (!is_array($param)) {
            $this->errMsg = '(internal) invalid parameter array';
            return NULL;
        }
        
        if (!function_exists('_not_empty_param')) {
            function _not_empty_param($x) { 
                if(is_null($x) || $x=='') return false;
                else Return true;
            }
        }
        
        $param = array_filter($param, '_not_empty_param');

        if(isset($param['organization'])){
            if($param['organization']!='all'){
                $condSQL[] = 'cdr.organization_domain = ?';
                $paramSQL[] = $param['organization'];
            }
        }
        // Fecha y hora de inicio y final del rango
        if (isset($param['date_start'])) {
            $start=date('Y-m-d',strtotime($param['date_start']))." 00:00:00";
            $condSQL[] = 'calldate >= ?';
            $paramSQL[] = $start;
        }
        
        if (isset($param['date_end'])) {
            $end=date('Y-m-d',strtotime($param['date_end']))." 23:59:59";
            $condSQL[] = 'calldate <= ?';
            $paramSQL[] = $end;
        }
        
        // Estado de la llamada
        if (isset($param['status'])){
            if($param['status'] != 'all') {
                $condSQL[] = 'disposition = ?';
                $paramSQL[] = $param['status'];
            }
        }
        
        if (isset($param['calltype'])){
            if(in_array($param['calltype'], array('incoming', 'outgoing'))) {
                $sCampo = ($param['calltype'] == 'incoming') ? 'fromout' : 'toout';
                $condSQL[] = "$sCampo = 1";
            }
        }
        
        //permite busque por patron de marcado por lo que ahi que traducir 
        //estos patrones al formato de busqueda aceptado por mysql
        //en el caso de src a dst tambien se hace la busqueda en channel y dstchannel respectivament
        if(isset($param['src'])){ 
            $expression=$pPBX->getRegexPatternFromAsteriskPattern($param['src']);
            if($expression!=false){
                $condSQL[]="( src REGEXP ? OR SUBSTRING_INDEX(SUBSTRING_INDEX(channel,'-',1),'_',-1) REGEXP ?)";
                $paramSQL[]="^$expression$";
                $paramSQL[]="^$expression$";
            }
        }
        if(isset($param['dst'])){
            $expression=$pPBX->getRegexPatternFromAsteriskPattern($param['dst']);
            if($expression!=false){
                $condSQL[]="( dst REGEXP ? OR SUBSTRING_INDEX(SUBSTRING_INDEX(dstchannel,'-',1),'_',-1) REGEXP ?)";
                $paramSQL[]="^$expression$";
                $paramSQL[]="^$expression$";
            }
        }
        if(isset($param['src_channel'])){
            $condSQL[] = 'channel like ?';
            $paramSQL[] = "%".$param['src_channel']."%";
        }
        if(isset($param['dst_channel'])){
            $condSQL[] = 'dstchannel like ?';
            $paramSQL[] = "%".$param['dst_channel']."%";
        }
        if(isset($param['accountcode'])){
            $condSQL[] = 'accountcode LIKE ?';
            $paramSQL[] = $param['accountcode'];
        }
        
        // Extensión de fuente o destino
        // este parametro se usa para consultar regitro de un usuario 
        // que solo puede ver sus registros
        if (isset($param['extension'])) {
            $condSQL[] = <<<SQL_COND_EXTENSION
(
       src = ?
    OR dst = ?
    OR SUBSTRING_INDEX(SUBSTRING_INDEX(channel,'-',1),'_',-1) = ?
    OR SUBSTRING_INDEX(SUBSTRING_INDEX(dstchannel,'-',1),'_',-1) = ?
)
SQL_COND_EXTENSION;
            array_push($paramSQL, $param['extension'], $param['extension'],
                $param['extension'], $param['extension']);
        }

        // Construir fragmento completo de sentencia SQL
        $where = array(implode(' AND ', $condSQL), $paramSQL);
        if ($where[0] != '') $where[0] = 'WHERE '.$where[0];
        return $where;
    }

    /**
     * Procedimiento para listar los CDRs desde la tabla asterisk.cdr con varios
     * filtrados aplicados.
     *
     * @param   mixed   $param  Lista de parámetros de filtrado:
     *  organization    Dominio de la  organizacion de la que se quieren 
     *                  obtener el registro de llamadas. Es NULL en caso
     *                  de que el superadmin es el que esta revisando los registro
     *  date_start      Fecha y hora minima de la llamada, en formato 
     *                  yyyy-mm-dd hh:mm:ss. Si se omite, se lista desde la 
     *                  primera llamada.
     *  date_end        Fecha y hora máxima de la llamada, en formato 
     *                  yyyy-mm-dd hh:mm:ss. Si se omite, se lista hasta la 
     *                  última llamada.
     *  status          Estado de la llamada, guardado en el campo 'disposition'.
     *                  Si se especifica, puede ser uno de los valores siguientes:
     *                  ANSWERED, NO ANSWER, BUSY, FAILED
     *  calltype        Tipo de llamada. Se puede indicar "incoming" o "outgoing".
     *                  toout = 1 => "outgoing"
     *                  fromout = 1 => "incoming"
     *  extension       Número de extensión para el cual filtrar los números. 
     *                  Este valor filtra por los campos 'src' y 'dst' 'src_channel' y 'dst_channel'.
     * @param   mixed   $limit  Máximo número de CDRs a leer, o NULL para todos
     * @param   mixed   $offset Inicio de lista de CDRs, si se especifica $limit
     *
     * @return  mixed   Lista de los cdrs. Se devuelven los siguientes campos
     *                      en el orden en que se listan a continuación:
     *                      calldate, src, dst, channel, dstchannel, disposition, 
     *                      uniqueid, duration, billsec, accountcode
     */
    function listarCDRs($param,$limit = NULL, $offset = 0)
    {
        list($sWhere, $paramSQL) = $this->_construirWhereCDR($param);
        if (is_null($sWhere)) return FALSE;
        
        // Los datos de los registros, respetando limit y offset
        $sPeticionSQL = 
            'SELECT calldate, src, dst, channel, dstchannel, disposition, '.
               "uniqueid, duration, billsec, accountcode, rg.rg_name, cdr.organization_domain, toout, fromout FROM cdr ".
            'LEFT JOIN elxpbx.ring_group rg '.
               'ON asteriskcdrdb.cdr.dst =  elxpbx.rg.rg_number '.
            $sWhere.
            ' ORDER BY calldate DESC';
        if (!empty($limit)) {
            $sPeticionSQL .= " LIMIT ? OFFSET ?";
            array_push($paramSQL, $limit, $offset);
        }
                
        $resultado = $this->_DB->fetchTable($sPeticionSQL, FALSE, $paramSQL);
        if (!is_array($resultado)) {
            $this->errMsg = '(internal) Failed to fetch CDRs - '.$this->_DB->errMsg;
            return false;
        }
        return $resultado;
    }

    /**
     * Procedimiento para contar los CDRs desde la tabla asteriskcdrdb.cdr con varios
     * filtrados aplicados. Véase listarCDRs para los parámetros conocidos.
     *
     * @param   mixed   $param  Lista de parámetros de filtrado.
     * 
     * @return  mixed   NULL en caso de error, o número de CDRs del filtrado
     */
    function getNumCDR($param)
    {
        list($sWhere, $paramSQL) = $this->_construirWhereCDR($param);
        if (is_null($sWhere)) return FALSE;

        // Cuenta del total de registros recuperados
        $sPeticionSQL = 
            'SELECT COUNT(*) FROM cdr LEFT JOIN elxpbx.ring_group rg '.
                'ON asteriskcdrdb.cdr.dst = elxpbx.rg.rg_number '.
            $sWhere;
	$r = $this->_DB->getFirstRowQuery($sPeticionSQL, FALSE, $paramSQL);
        if (!is_array($r)) {
            $this->errMsg = '(internal) Failed to count CDRs - '.$this->_DB->errMsg;
            return false;
        }
        return $r[0];
    }
    
    /**
     * Procedimiento para borrar los CDRs en la tabla asteriskcdrdb.cdr
     * recibe como parametrso el uniqueid de los registros que se desean eliminar
     * @param array array con los uniqueid de los elementos a eliminar
     * @retun bool  true en caso de exito, falso caso contrario
     */
    function borrarCDRs($param)
    {
        if(!array($param)){
            $this->errMsg=_tr("Invalid CDRs");
            return false;
        }
        
        if(count($param)==0){
            return true;
        }else{
            $q=implode(",",array_fill(0,count($param),'?'));
            $query="DELETE FROM cdr WHERE uniqueid IN ($q)";
            $r = $this->_DB->genQuery($query, $param);
            if (!$r) {
                $this->errMsg = '(internal) Failed to delete CDRs - '.$this->_DB->errMsg;
            }
            return $r;
        }
    }
}
?>
