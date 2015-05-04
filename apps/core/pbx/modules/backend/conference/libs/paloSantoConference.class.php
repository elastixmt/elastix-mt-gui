<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.2.0-29                                               |
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
  $Id: paloSantoIVR.class.php,v 1.1 2012-09-07 11:50:00 Rocio Mera rmera@palosanto.com Exp $ */
  
include_once "libs/paloSantoAsteriskConfig.class.php";
include_once "libs/paloSantoPBX.class.php";

class paloConference extends paloAsteriskDB{
    protected $code;
    protected $domain;
        
    function paloConference(&$pDB,$domain){
        parent::__construct($pDB);
        
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloConference").$this->errMsg;
            }else{
                $this->code=$result["code"];
            }
        }
    }
    
    function setDomain($domain){
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;
            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloSantoOutboundPBX").$this->errMsg;
            }else{
                $this->code=$result["code"];
            }
        }
    }
    
    function getDomain(){
        return $this->domain;
    }
    
    function getTotalConference($domain=NULL,$date,$state_conf="",$type_conf="",$name_conf=""){
        $arrParam=null;

        $query="select count(bookid) from meetme";
        $cond=$this->createQueryCondition($domain,$date,$state_conf,$type_conf,$name_conf,$arrParam);
        $query .=" $cond";
        
        $result=$this->_DB->getFirstRowQuery($query,false,$arrParam);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result[0];
    }
    
    private function createQueryCondition($domain,$date,$state_conf,$type_conf,$name_conf,&$arrParam){
        $where=array();
        $arrParam=null;
        
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        
        switch($state_conf){
            case "past":
                $where[]=" endtime<?";
                $arrParam[]=$date;
                break;
            case "future":
                $where[]=" startTime>?";
                $arrParam[]=$date;
                break;
            case "current":
                $where[]=" startTime<=? AND endtime>=?";
                $arrParam[]=$date;
                $arrParam[]=$date;
                break;
            default: 
                break;
        }
        
        switch($type_conf){
            case "yes":
                $where[]=" and startTime is not NULL and endtime is not NULL";
                break;
            case "no":
                $where[]=" and startTime is NULL and endtime is NULL";
                break;
            default: 
                break;
        }
        
        if(isset($name_conf) && $name_conf!=''){
            $where[]=" UPPER(name) LIKE ?";
            $arrParam[]="%$name_conf%";
        }
        
        if(count($where)>0){
            return " WHERE ".implode(" AND ",$where);
        }else
            return '';
    }
    
    function getConferesPagging($domain=null,$date,$limit,$offset,$state_conf="",$type_conf="",$name_conf=""){
        $arrParam=null;
        
        //evaluamos los parametros de busqueda
        $cond=$this->createQueryCondition($domain,$date,$state_conf,$type_conf,$name_conf,$arrParam);
        
        $query="SELECT * from meetme $cond order by endtime desc ";
        if(isset($limit) && isset($offset)){
            $query .=" limit ? offset ?";
            $arrParam[]=$limit;
            $arrParam[]=$offset;
        }
        
        $result=$this->_DB->fetchTable($query,true,$arrParam);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }
    
    private function existConfByName($name){
        if(empty($name)){
            $this->errMsg=_tr("Invalid field 'Conference Name'");
            return true;
        }
        
        $query="SELECT bookid from meetme where organization_domain=? and name=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($this->domain,$name));
        if($result===false || count($result)!=0){
            $this->errMsg=(count($result)!=0)?_tr("Already exist a conference with the same name"):$this->_DB->errMsg;
            return true;
        }else{
            return false;
        }
    }
    
    function getConferenceById($id){
        global $arrConf;
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = _tr("Invalid Conference");
            return false;
        }
        
        $query="SELECT * from meetme where organization_domain=? and bookid=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($this->domain,$id));
        if($result===false || count($result)==0){
            $this->errMsg=(count($result)==0)?_tr("Conference does not exist"):_tr("DATABASE ERROR");
            return false;
        }else{
            return $result;
        }
    }
    
    function createNewConf($arrProp){
        $query="INSERT INTO meetme (";
        $arrOpt=array();
    
        $query .="organization_domain,";
        $arrOpt[]=$this->domain;
                
        if($this->existConfByName($arrProp["name"])==true){
            return false;
        }else{
            $query .="name,";
            $arrOpt[]=$arrProp["name"];
        }
        
        //el numero de la conferencia no debe estar siendo usado como patron de marcado
        if(!preg_match("/^[0-9]*$/",$arrProp["confno"])){
            $this->errMsg=_tr("Invalid Conference Number");
            return false;
        }
        if($this->existExtension($arrProp["confno"],$this->domain)==true){
            return false;
        }else{
            $query .="confno,";
            $arrOpt[]=$this->code."_".$arrProp["confno"];
            $query .="ext_conf,";
            $arrOpt[]=$arrProp["confno"];
        }
        
        if($arrProp['adminpin']!=""){
            if(!preg_match("/^[0-9]*$/",$arrProp['adminpin'])){
                $this->errMsg=_tr("Invalid Field 'Admin PIN'")._tr("Must contain only Digits");
                return false;
            }else{
                $query .="adminpin,";
                $arrOpt[]=$arrProp["adminpin"];
            }
        }
        
        if($arrProp['pin']!=""){
            if(!preg_match("/^[0-9]*$/",$arrProp['pin'])){
                $this->errMsg=_tr("Invalid Field 'User PIN'")._tr("Must contain only Digits");
                return false;
            }else{
                $query .="pin,";
                $arrOpt[]=$arrProp["pin"];
            }
        }
        
        if($arrProp['maxusers']!=""){
            if(!preg_match("/^[0-9]*$/",$arrProp['maxusers'])){
                $this->errMsg=_tr("Invalid Field 'maxusers'")._tr("Must contain only Digits");
                return false;
            }else{
                $query .="maxusers,";
                $arrOpt[]=$arrProp['maxusers'];
            }
        }
        
        if($arrProp['schedule']=="on"){
            if(!preg_match("/^(([1-2][0,9][0-9][0-9])-((0[1-9])|(1[0-2]))-((0[1-9])|([1-2][0-9])|(3[0-1]))) (([0-1][0-9]|2[0-3]):[0-5][0-9])$/",$arrProp['start_time'])){
                $this->errMsg=_tr("Invalid Format Start Time YYYY-MM-DD HH:MM");
                return false;
            }else{
                if(strtotime($arrProp['start_time']."+ 1 minutes")<time()){
                    $this->errMsg=_tr("Start Time can't less current time");
                    return false;
                }
                if(!preg_match("/^[0-9]{1,2}$/",$arrProp['duration']) || !preg_match("/^([0-5][0-9]|[0-9])$/",$arrProp['duration_min'])){
                    $this->errMsg=_tr("Invalid field 'duration'");
                    return false;
                }
                //obtenemos el endtime
                $endtime=strtotime($arrProp["start_time"])+((int)$arrProp['duration']*3600)+(int)$arrProp['duration_min']*60;
                $query .="startTime,endtime,";
                $arrOpt[]=$arrProp['start_time'];
                $arrOpt[]=strftime("%F %R",$endtime);   
            } 
        }
        
        //check for recording
        if($arrProp['record_conf']=='no'){ //disable recording
            $arrProp['moderator_options_2']="off";
            $arrProp['user_options_4']="off";
        }else{
            if($arrProp['record_conf']!='wav' && $arrProp['record_conf']!='wav49' && $arrProp['record_conf']!='gsm'){
                $arrProp['record_conf']='wav';
            }
            $arrProp['moderator_options_2']="on"; 
            $arrProp['user_options_4']="on"; 
            $query .="recordingformat,";
            $arrOpt[]=$arrProp['record_conf'];
        }
        
        $optAd="aAs";
        $optUser="";
        
        $optAd .=($arrProp['moderator_options_1']=="on")?"i":"";
        $optAd .=($arrProp['moderator_options_2']=="on")?"r":"";
        $optUser .=($arrProp['user_options_1']=="on")?"i":"";
        $optUser .=($arrProp['user_options_2']=="on")?"m":"";
        $optUser .=($arrProp['user_options_3']=="on")?"w":"";
        $optUser .=($arrProp['user_options_4']=="on")?"r":"";
        
        if($arrProp['moh']!=""){
            if($this->existMoHClass($arrProp['moh'],$this->domain)){
                $optAd .="M({$arrProp['moh']})";
                $optUser .="M({$arrProp['moh']})";
            }
        }
        
        if($arrProp['announce_intro']!=""){
            $announ=$this->getFileRecordings($this->domain,$arrProp['announce_intro']);
            if($announ!=false){
                $query .="intro_record,";
                $arrOpt[]=$arrProp['announce_intro'];
                $optAd .="G($announ)";
                $optUser .="G($announ)";
            }
        }
        
        $query .="opts,adminopts";
        $arrOpt[]=$optUser;
        $arrOpt[]=$optAd;
        
        $query .=")";
        $qmarks = "(";
        for($i=0;$i<count($arrOpt);$i++){
            $qmarks .="?,"; 
        }
        $qmarks=substr($qmarks,0,-1).")"; 
        $query = $query." values".$qmarks;
        
        $result=$this->executeQuery($query,$arrOpt);
                
        if($result==false)
            $this->errMsg=$this->errMsg;
        return $result; 
    }
    
    function updateConference($arrProp){
        $arrConf=$this->getConferenceById($arrProp["id_conf"]);
        if($arrConf==false){
            return false;
        }
        
        $this->domain=$arrConf["organization_domain"];
        $query="Update meetme set ";
        
        if($arrProp["name"]!=$arrConf["name"]){
            if($this->existConfByName($arrProp["name"])==true){
                return false;
            }else{
                $query .="name=?,";
                $arrOpt[]=$arrProp["name"];
            }
        }
        
        if($arrProp['adminpin']!=""){
            if(!preg_match("/^[0-9]*$/",$arrProp['adminpin'])){
                $this->errMsg=_tr("Invalid Field 'Admin PIN'")._tr("Must contain only Digits");
                return false;
            }else{
                $query .="adminpin=?,";
                $arrOpt[]=$arrProp["adminpin"];
            }
        }else{
            $query .="adminpin=?,";
            $arrOpt[]=NULL;
        }
        
        if($arrProp['pin']!=""){
            if(!preg_match("/^[0-9]*$/",$arrProp['pin'])){
                $this->errMsg=_tr("Invalid Field 'User PIN'")._tr("Must contain only Digits");
                return false;
            }else{
                $query .="pin=?,";
                $arrOpt[]=$arrProp["pin"];
            }
        }else{
            $query .="pin=?,";
            $arrOpt[]=NULL;
        }
        
        if($arrProp['maxusers']!=""){
            if(!preg_match("/^[0-9]*$/",$arrProp['maxusers'])){
                $this->errMsg=_tr("Invalid Field 'maxusers'")._tr("Must contain only Digits");
                return false;
            }else{
                $query .="maxusers=?,";
                $arrOpt[]=$arrProp['maxusers'];
            }
        }else{
            $query .="maxusers=?,";
            $arrOpt[]=NULL;
        }
        
        if($arrProp['schedule']=="on"){
            if(!preg_match("/^(([1-2][0,9][0-9][0-9])-((0[1-9])|(1[0-2]))-((0[1-9])|([1-2][0-9])|(3[0-1]))) (([0-1][0-9]|2[0-3]):[0-5][0-9])$/",$arrProp['start_time'])){
                $this->errMsg=_tr("Invalid Format Start Time YYYY-MM-DD HH:MM");
                return false;
            }else{
                if(!preg_match("/^[0-9]{1,2}$/",$arrProp['duration']) || !preg_match("/^([0-5][0-9]|[0-9])$/",$arrProp['duration_min'])){
                    $this->errMsg=_tr("Invalid field 'duration'");
                    return false;
                }
                //obtenemos el endtime
                $endtime=strtotime($arrProp["start_time"])+((int)$arrProp['duration']*3600)+(int)$arrProp['duration_min']*60;
                $query .="startTime=?,endtime=?,";
                $arrOpt[]=$arrProp['start_time'];
                $arrOpt[]=strftime("%F %R",$endtime);
            } 
        }else{
            $query .="startTime=?,endtime=?,";
            $arrOpt[]='1900-01-01 12:00:00';
            $arrOpt[]='2999-01-01 12:00:00';
        }
        
         //check for recording
        if($arrProp['record_conf']=='no'){ //disable recording
            $arrProp['moderator_options_2']="off";
            $arrProp['user_options_4']="off";
            $query .="recordingformat=?,";
            $arrOpt[]='';
        }else{
            if($arrProp['record_conf']!='wav' && $arrProp['record_conf']!='wav49' && $arrProp['record_conf']!='gsm'){
                $arrProp['record_conf']='wav';
            }
            $arrProp['moderator_options_2']="on"; 
            $arrProp['user_options_4']="on"; 
            $query .="recordingformat=?,";
            $arrOpt[]=$arrProp['record_conf'];
        }
        
        $optAd="aAs";
        $optUser="";
        
        $optAd .=($arrProp['moderator_options_1']=="on")?"i":"";
        $optAd .=($arrProp['moderator_options_2']=="on")?"r":"";
        $optUser .=($arrProp['user_options_1']=="on")?"i":"";
        $optUser .=($arrProp['user_options_2']=="on")?"m":"";
        $optUser .=($arrProp['user_options_3']=="on")?"w":"";
        $optUser .=($arrProp['user_options_4']=="on")?"r":"";
        
        if($arrProp['moh']!=""){
            if($this->existMoHClass($arrProp['moh'],$this->domain)){
                $optAd .="M({$arrProp['moh']})";
                $optUser .="M({$arrProp['moh']})";
            }
        }
        
        $query .="intro_record=?,";
        $arrOpt[]=NULL;
        if($arrProp['announce_intro']!=""){
            $announ=$this->getFileRecordings($this->domain,$arrProp['announce_intro']);
            if($announ!=false){
                $query .="intro_record=?,";
                $arrOpt[]=$arrProp['announce_intro'];
                $optAd .="G($announ)";
                $optUser .="G($announ)";
            }
        }
        
        $query .="opts=?,adminopts=? where bookid=? and organization_domain=?";
        $arrOpt[]=$optUser;
        $arrOpt[]=$optAd;
        $arrOpt[]=$arrConf["bookid"];
        $arrOpt[]=$this->domain;
        
        $result=$this->executeQuery($query,$arrOpt);
        return $result; 
    }
    
    function deleteConference($arrConf){
        //if $this->domain paramater in not set or if = ''
        //we assume that there is not any restriction at the moment to delete
        //action must be being executed by superadmin
        
        if(is_array($arrConf) && count($arrConf)>0){
            $q=implode(",",array_fill(0,count($arrConf),'?'));
            $query="DELETE FROM meetme WHERE bookid IN ($q)";
            if(!empty($this->domain)){
                $query .=" AND organization_domain=?";
                $arrConf[]=$this->domain;
            }
        }else{
            $this->errMsg=_tr("Invalid Conference(s)");
            return false;
        }
    
        if($this->executeQuery($query,$arrConf)){
            return true;
        }else{
            $this->errMsg=_tr("DATABASE ERROR");
            return false;
        }
    }
    
    private function AsteriskManager_Command($command) {
        $astMang=AsteriskManagerConnect($errorM);
        if($astMang==false){
            $this->errMsg=$errorM;
            return false;
        }else{
            $salida = $astMang->Command("$command");;
            $astMang->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                return explode("\n", $salida["data"]);
            }
        }
        return false;
    }
    
    function ObtainCallers($room){
        $arrCallers=array();
        if(empty($this->domain) || empty($this->code)){
            return false;
        }
        
        if(!preg_match("/^".$this->code."_[0-9]+$/",$room)){
            $this->errMsg=_tr("Invalid Room");
            return false;
        }
        
        // 2!408!User Name!SIP/user_domain-00000028!!!yyyyy!!xxxxx!00:01:34
        // 10 records
        // if xxxxx is -1 then (unmonitored)
        // if yyyyy is  1 then (Admin Muted) 
        $command = "meetme list $room concise";
        $arrResult = $this->AsteriskManager_Command($command);
        
        if(is_array($arrResult) && count($arrResult)>0) {
            foreach($arrResult as $Key => $linea) {
                if(preg_match("/^[0-9]+![0-9]+!/", $linea)){
                    $arrReg = explode("!",$linea);               
                    $arrCallers[] = array(
                        'userId'    => $arrReg[0],
                        'callerId'  => "$arrReg[2] <{$arrReg[1]}>",
                        'mode'      => $arrReg[4], //se setea en caso de que el usuario sea de tipo admin (admin/user)
                        'status'    => ($arrReg[6]==1)?"Admin Muted":"", //muted|no muted
                        'duration'  => $arrReg[9]
                    );
                }
            }
        }
        return $arrCallers;
    }

    function MuteCaller($room, $userId, $mute)
    {
        //el $room no es numerico
        if (count(preg_split("/[\r\n]+/",$room)) > 1){
            $this->errMsg=_tr("Invalid Conference");
            return FALSE;
        } 
        if (count(preg_split("/[\r\n]+/", $userId)) > 1){
            $this->errMsg=_tr("Invalid User");
            return FALSE;
        } 

        if($mute=='on')
            $action = 'mute';
        else
            $action = 'unmute';
        $command = "meetme $action $room $userId";
        $arrResult = $this->AsteriskManager_Command($command);
    }

    function KickCaller($room, $userId)
    {
        //el $room no es numerico
        if (count(preg_split("/[\r\n]+/",$room)) > 1){
            $this->errMsg=_tr("Invalid Conference");
            return FALSE;
        } 
        if (count(preg_split("/[\r\n]+/", $userId)) > 1){
            $this->errMsg=_tr("Invalid User");
            return FALSE;
        } 

        $action = 'kick';
        $command = "meetme $action $room $userId";
        $arrResult = $this->AsteriskManager_Command($command);
    }

    function InviteCaller($room, $ext_room, $channel, $callerId){
        $arrCallers=array();
        if(empty($this->domain) || empty($this->code)){
            return false;
        }
        
        if(!preg_match("/^".$this->code."_[0-9]+$/",$room)){
            $this->errMsg=_tr("Invalid Room");
            return false;
        }
        if (count(preg_split("/[\r\n]+/", $ext_room)) > 1) return FALSE;
        
        $query="Select exten from extension where dial=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($channel,$this->domain));
        if($result==false){
            $this->errMsg=_tr("Invalid Exten");
            return false;
        }
        
        $astMang=AsteriskManagerConnect($errorM);
        if($astMang==false){
            $this->errMsg=$errorM;
            return false;
        } else{ 
            $parameters['Channel'] = $channel;
            $parameters['Context'] = $this->code."-ext-meetme";
            $parameters['Exten'] = $ext_room;
            $parameters['Priority']=1;
            $parameters['CallerID'] = $callerId;
            $parameters['Variable'] = "REALCALLERIDNUM=".$result[0];
            $salida = $astMang->send_request('Originate', $parameters);
            $astMang->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                return true;
            }else
                return false;
        }
        return false;
    }
    
    function createDialplanConf(&$arrFromInt){
        if(is_null($this->code) || is_null($this->domain))
            return false;
    
        $arrExt=array();
        $query="SELECT ext_conf,confno,recordingformat from meetme where organization_domain=?";
        $result=$this->_DB->fetchTable($query,true,array($this->domain));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else{
            foreach($result as $value){
                if(isset($value["ext_conf"]) && $value["ext_conf"]!=""){
                    $exten=$value["ext_conf"];
                    $arrExt[]=new paloExtensions($exten,new ext_setvar('MEETME_RECORDINGFILE', '/var/spool/asterisk/monitor/'.$this->domain.'/meetme-conf-rec-'.$value["ext_conf"].'-${UNIQUEID}'),1);
                    //los archivos con extension wav49 se guardan dentro de asterisk com WAV
                    $arrExt[]=new paloExtensions($exten,new ext_setvar('MEETME_RECORDINGFORMAT',$value["recordingformat"]));
                    $arrExt[]=new paloExtensions($exten,new ext_execif('$["${MEETME_RECORDINGFORMAT}"="wav49"]','Set','MEETME_RECORDINGFORMAT=WAV'));
                    $arrExt[]=new paloExtensions($exten,new ext_macro($this->code.'-user-callerid',"SKIPTTL"));
                    $arrExt[]=new paloExtensions($exten,new ext_meetme($value["confno"]));
                    $arrExt[]=new paloExtensions($exten,new ext_hangup());
                }
            }
            $arrExt[]=new paloExtensions("h",new ext_macro($this->code."-hangupcall"),1);
        }
        
        $arrContext=array();
        //creamos el context ext-meetme
        $context=new paloContexto($this->code,"ext-meetme");
        if($context===false){
            $context->errMsg="ext-meetme. Error: ".$context->errMsg;
        }else{
            $context->arrExtensions=$arrExt;
            $arrFromInt[]["name"]="ext-meetme";
            $arrContext[]=$context;
        }
        return $arrContext; 
    }
}
?>