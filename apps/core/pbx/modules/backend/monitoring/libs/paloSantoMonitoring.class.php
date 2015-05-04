<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-18                                               |
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
  $Id: paloSantoMonitoring.class.php,v 1.1 2010-03-22 05:03:48 Eduardo Cueva ecueva@palosanto.com Exp $ 
  $Id: index.php,v 3.1 2013-09-13 05:03:48 Rocio Mera rmera@palosanto.com Exp $*/

include_once "libs/paloSantoPBX.class.php";
class paloSantoMonitoring {
    private $_DB;
    public $errMsg;

    function paloSantoMonitoring(&$pDB)
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
    
    function getErrMsg(){
        return $this->errMsg;
    }

    /**
     * Functions that returns the number of recordings to show
     * The search is filter by parameter in $arrProp array
     * organization => organization to who belong the recordings
     * date_start => start date 
     * date_end => start date 
     * type => can be queue,group,ringgroup,incoming,outgoing,interna
     * source => 
     * destination =>
     */
    function getNumMonitoring($arrProp){
        global $arrConf;
        $pPBX= new paloAsteriskDB($arrConf['elastix_dsn']["elastix"]);
        
        $where=array();
        $arrParam=null;
        $query="SELECT COUNT(calldate) FROM cdr WHERE userfield REGEXP '^audio:.+$' ";
        //organization
        if(isset($arrProp['domain'])){
            if($arrProp['domain']!='all' && $arrProp['domain']!=''){
                $where[]=" organization_domain=? ";
                $arrParam[]=$arrProp['domain'];
            }
        }
        //date        
        if(empty($arrProp['date_start'])){
            $start = date('Y-m-d')." 00:00:00";
        }else{
            $start=date('Y-m-d',strtotime($arrProp["date_start"]))." 00:00:00";
        }
        if(empty($arrProp['date_end'])){
            $end   = date('Y-m-d')." 23:59:59";
        }else{
            $end=date('Y-m-d',strtotime($arrProp["date_end"]))." 23:59:59";
        }
        $where[]=" (calldate >= ? AND calldate <= ?)";
        $arrParam[]=$start;
        $arrParam[]=$end;
        
        //type
        if(!empty($arrProp['type'])){
            if($arrProp['type']=='group'){
                //nombre de la grabacion
                $where[]=" userfield REGEXP '^audio:/var/spool/asterisk/monitor/.+/g.+$' ";
            }elseif($arrProp['type']=='queue'){
                //nombre de la grabacion
                $where[]=" userfield REGEXP '^audio:/var/spool/asterisk/monitor/.+/q.+$' ";
            }elseif($arrProp['type']=='conference'){
                //nombre de la grabacion
                $where[]=" userfield REGEXP '^audio:/var/spool/asterisk/monitor/.+/meetme-conf.+$' ";
            }elseif($arrProp['type']=='incoming'){
                $where[]=" toout=1 ";
            }elseif($arrProp['type']=='outgoing'){
                $where[]=" fromout=1 ";
            }
        }
        
        if(isset($arrProp['source'])){
            if($arrProp['source']!=""){
                $expression=$pPBX->getRegexPatternFromAsteriskPattern($arrProp['source']);
                if($expression!=false){
                    $where[]=" src REGEXP ? ";
                    $arrParam[]="^$expression$";
                }
            }
        }
        
        if(isset($arrProp['destination'])){
            if($arrProp['destination']!=""){
                $expression=$pPBX->getRegexPatternFromAsteriskPattern($arrProp['destination']);
                if($expression!=false){
                    $where[]=" dst REGEXP ? ";
                    $arrParam[]="^$expression$";
                }
            }
        }
        
        if(count($where)>0){
            $query .=" AND ".implode(" AND ",$where);
        }
        
        $result=$this->_DB->getFirstRowQuery($query,false,$arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result[0];
    }

    function getMonitoring($arrProp)
    {
        global $arrConf;
        $pPBX= new paloAsteriskDB($arrConf['elastix_dsn']["elastix"]);
        
        $where=array();
        $arrParam=null;
        $query="SELECT calldate,src,dst,userfield,duration,organization_domain,fromout,toout,uniqueid FROM cdr WHERE userfield REGEXP '^audio:.+$' ";
        //organization
        if(isset($arrProp['domain'])){
            if($arrProp['domain']!='all' && $arrProp['domain']!=''){
                $where[]=" organization_domain=? ";
                $arrParam[]=$arrProp['domain'];
            }
        }
        //date        
        if(empty($arrProp['date_start'])){
            $start = date('Y-m-d')." 00:00:00";
        }else{
            $start=date('Y-m-d',strtotime($arrProp["date_start"]))." 00:00:00";
        }
        if(empty($arrProp['date_end'])){
            $end   = date('Y-m-d')." 23:59:59";
        }else{
            $end=date('Y-m-d',strtotime($arrProp["date_end"]))." 23:59:59";
        }
        $where[]=" (calldate >= ? AND calldate <= ?)";
        $arrParam[]=$start;
        $arrParam[]=$end;
        
        //type
        if(!empty($arrProp['type'])){
            if($arrProp['type']=='group'){
                //nombre de la grabacion
                $where[]=" userfield REGEXP '^audio:/var/spool/asterisk/monitor/.+/g.+$' ";
            }elseif($arrProp['type']=='queue'){
                //nombre de la grabacion
                $where[]=" userfield REGEXP '^audio:/var/spool/asterisk/monitor/.+/q.+$' ";
            }elseif($arrProp['type']=='conference'){
                //nombre de la grabacion
                $where[]=" userfield REGEXP '^audio:/var/spool/asterisk/monitor/.+/meetme-conf.+$' ";
            }elseif($arrProp['type']=='incoming'){
                $where[]=" toout=1 ";
            }elseif($arrProp['type']=='outgoing'){
                $where[]=" fromout=1 ";
            }
        }
        
        if(isset($arrProp['source'])){
            if($arrProp['source']!=""){
                $expression=$pPBX->getRegexPatternFromAsteriskPattern($arrProp['source']);
                if($expression!=false){
                    $where[]=" src REGEXP ? ";
                    $arrParam[]="^$expression$";
                }
            }
        }
        
        if(isset($arrProp['destination'])){
            if($arrProp['destination']!=""){
                $expression=$pPBX->getRegexPatternFromAsteriskPattern($arrProp['destination']);
                if($expression!=false){
                    $where[]=" dst REGEXP ? ";
                    $arrParam[]="^$expression$";
                }
            }
        }
        
        if(count($where)>0){
            $query .=" AND ".implode(" AND ",$where);
        }
        
        if(isset($arrProp['limit']) && isset($arrProp['offset'])){
            $query .=" limit ? offset ?";
            $arrParam[]=$arrProp['limit'];
            $arrParam[]=$arrProp['offset'];
        }
        $result=$this->_DB->fetchTable($query,TRUE,$arrParam);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }

    function getMonitoringById($id,$domain=null)
    {
        $query = "SELECT userfield FROM cdr WHERE uniqueid=?";
        $arrParam[]=$id;
        if(isset($domain)){
            $query .= " AND organization_domain=?";
            $arrParam[]=$domain;
        }
        $result=$this->_DB->getFirstRowQuery($query,true,$arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function deleteRecordings($records,$domain=null)
    {   
        $error=array();
        $success=array();
        
        if(!is_array($records)){
            $this->errMsg=_tr("Invalid Recording(s)");
            return false;
        }else{
            if(count($records)==0)
                return true;
                
            //obtenemos los archivos que van a ser eliminados
            $q=implode(",",array_fill(0,count($records),"?"));
            $query="SELECT uniqueid,userfield,organization_domain FROM cdr WHERE uniqueid in ($q)";
            if(isset($domain)){
                $query .=" AND organization_domain=?";
                $records[]=$domain;
            }
            $result=$this->_DB->fetchTable($query,true,$records);
            if($result===false){
                $this->errMsg=_tr("An error has ocurred to obtain selectd recordings.")." "._tr("DATABASE ERROR");
                return false;
            }
            
            if(count($result)==0){
                $this->errMsg=_tr("Invalid Recording(s)");
                return false;
            }
            
            $query='UPDATE cdr SET userfield = "audio:deleted" WHERE uniqueid = ?';
            foreach($result as $value){
                $this->_DB->beginTransaction();
                if(!$this->_DB->genQuery($query,array($value['uniqueid']))){
                    $error[]=basename($value['userfield'])." - DATABASE ERROR";
                    $this->_DB->rollBack();
                    continue;
                }
                
                
                $fullPath = str_replace("audio:","",$value['userfield']);
                $nameFile=basename($fullPath);
                
                if($value['organization_domain']!=""){
                    $file="/var/spool/asterisk/monitor/{$value['organization_domain']}/$nameFile";
                }else{
                    $file="/var/spool/asterisk/monitor/$nameFile";
                }
                
                //procedemos a eliminar los archivos del sistema
                if(file_exists($file)){
                    if(unlink($file)===false){
                        $error[]=$nameFile." - error to delete system";
                        $this->_DB->rollBack();
                    }else{
                        $this->_DB->commit();
                    }
                }else{
                    $this->_DB->commit(); //cerramos la transaccion
                }
            }
            if(count($error)>0){
                $this->errMsg=implode("<br>",$error);
                return false;
            }
        }
        return true;
    }
}
?>
