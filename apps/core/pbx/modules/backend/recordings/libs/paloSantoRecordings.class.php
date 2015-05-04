<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.1-4                                               |
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
  $Id: default.conf.php,v 1.1 2008-06-12 09:06:35 afigueroa Exp $ */

if (file_exists("/var/lib/asterisk/agi-bin/phpagi-asmanager.php")) {
    require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
}

include_once "libs/paloSantoACL.class.php";
include_once "libs/paloSantoAsteriskConfig.class.php";
include_once "libs/paloSantoPBX.class.php";

class paloSantoRecordings extends paloAsteriskDB{

    function paloSantoRecordings(&$pDB)
    {
       parent::__construct($pDB);
    }
    
    function getNumRecording($domain=null,$name=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(uniqueid) from recordings";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($name) && $name!=''){
            $where[]=" UPPER(name) like ?";
            $arrParam[]="%".strtoupper($name)."%";
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
        
        $result=$this->_DB->getFirstRowQuery($query,false,$arrParam);
        if($result==false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result[0];
    }

    function getRecordings($domain=null,$name=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;

        $query="SELECT * from recordings";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($name) && $name!=''){
            $where[]=" UPPER(name) like ?";
            $arrParam[]="%".strtoupper($name)."%";
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
        $query .=" ORDER BY uniqueid DESC ";
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

    function convertMP3toWAV($base,&$tmpFile,$file_sin_ext,$prep){
        $output = $ret = NULL;
        
        $tmp=$tmpFile;
        $tmpFile=$prep."_".$file_sin_ext.".wav";
        //mpg123 -w outputFile inputFile

        exec("mpg123 -w '$base/$tmpFile' '$base/$tmp'", $output, $ret);
        
        unlink("$base/$tmp");
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    function getTipeOfFile($file){
        $mime_type="";
        $finfo = new finfo(FILEINFO_MIME, "/usr/share/misc/magic.mgc");
        if(is_file($file)){
            $mime_type = $finfo->file($file);
        }else{
            $this->errMsg = _tr("File doens't exist ").$file;
            return false;
        }
        return $mime_type;
    }

    function resampleMoHFiles($base,$tmpFile,$filename){
        //  sox inputFile -r 8000 -c 1 outputFile
        $output = $ret = NULL;		
        exec("sox '$base/$tmpFile' -r 8000 -c 1 '$base/$filename'", $output, $ret);
        unlink("$base/$tmpFile");
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    function getRecordingById($id,$domain=null)
    {
        $query = "SELECT filename,name FROM recordings WHERE uniqueid=?";
        $arrParam[]=$id;
        if(isset($domain)){
            $query .=" and organization_domain=?";
            $arrParam[]=$domain;
        }

        $result = $this->_DB->getFirstRowQuery($query, TRUE, $arrParam);
        if($result != FALSE)
            return $result;
        else{
            $this->errMsg = _tr("DATABASE ERROR");
            return FALSE;
        }
    }


    function Obtain_Extension_Current_User()
    {
        $pACL = new paloACL($this->_DB);
        $username = $_SESSION["elastix_user"];
        $extension = $pACL->getUserExtension($username);
        if(is_null($extension))
            return false;
        else 
            return $extension;
    }


    function  Obtain_Protocol_Current_User($domain,$extension){
        $arr_result2=array();
        $query2="SELECT id, exten, organization_domain, tech, dial, voicemail, device, clid_name, clid_number FROM extension where exten=? and  organization_domain=?";
        $arr_result2 = $this->_DB->getFirstRowQuery($query2,true,array($extension,$domain));
        if (!is_array($arr_result2) || count($arr_result2)==0) {
            $this->errMsg = _tr("Can't get extension user").$this->_DB->errMsg;
        }
        return $arr_result2;
    }


    function Call2Phone($data_connection, $origen, $channel, $description, $recording_name, $domain)
    {
        $command_data['origen'] = $origen;
        $command_data['channel'] = $channel;
        $command_data['description'] = $description;
        $command_data["recording_name"]=$recording_name;
        return $this->AsteriskManager_Originate($data_connection['host'], $data_connection['user'], $data_connection['password'], $command_data, $domain);
    }

    function hangupPhone($data_connection, $origen, $channel, $description)
    {
        $command_data['origen'] = $origen;
        $command_data['channel'] = $channel;
        $command_data['description'] = $description;
        return $this->AsteriskManager_Hangup($data_connection['host'], $data_connection['user'], $data_connection['password'], $command_data);
    }

    //Verificamos el estado del channel, para saber si ha colgado o no.
    function callStatus($channelName){
        $status ="hangup";
        $arrChannel = explode("/", $channelName);
        $pattern = "/^".$arrChannel[0]."\/".$arrChannel[1]."/";
        exec("/usr/sbin/asterisk -rx 'core show channels concise'", $output, $retval);
        
        if(count($output)==0){
            $status ="hangup";
        }else{
            foreach($output as $linea) {
                if(preg_match($pattern, $linea, $arrReg)){
                    $status = "recording";
                }
            }
        }
        return $status;
    }

    function AsteriskManager_Originate($host, $user, $password, $command_data,$domain) {
        $astman = new AGI_AsteriskManager();

        if (!$astman->connect("$host", "$user" , "$password")) {
            $this->errMsg = _tr("Error when connecting to Asterisk Manager");
        } else{
            //
            $parameters = $this->Originate($command_data['origen'], $command_data['channel'],$command_data['description'], $command_data["recording_name"],$domain);

            $salida = $astman->send_request('Originate', $parameters);
            $this->errMsg=implode(",",$parameters);
            $astman->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                return explode("\n", $salida["Response"]);
            }else{
                $this->errMsg .=$salida["Response"];
                return false;
            }
        }
        return false;
    }

    function AsteriskManager_Hangup($host, $user, $password, $command_data) {
        $astman = new AGI_AsteriskManager();
        $channel = "";
        if (!$astman->connect("$host", "$user" , "$password")) {
            $this->errMsg = _tr("Error when connecting to Asterisk Manager");
        } else{
            //
            $channelName = $command_data['channel'];
            $arrChannel = explode("/", $channelName);
            $pattern = "/^".$arrChannel[0]."\/".$arrChannel[1]."/";
            exec("/usr/sbin/asterisk -rx 'core show channels concise'", $output, $retval);
            foreach($output as $linea) {
                if(preg_match($pattern, $linea, $arrReg)){
                        $arr = explode("!", $linea);
                        $channel = $arr[0];
                            
                }else{
                    $channel = $channelName;
                }
            }
            $parameters = array('Channel'=>$channel);
        
            $salida = $astman->send_request('Hangup',$parameters);
            $astman->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                return explode("\n", $salida["Response"]);
            }else 
                return false;
        }
        return false;
    }


    function Originate($origen, $channel="", $description="", $recording_name, $domain)
    {
        $parameters = array();
        $parameters['Channel']      = $channel;
        $parameters['CallerID']     = "$description <$origen>";
        $parameters['Application']  = "Record";
        $parameters['Data']         = "/var/lib/asterisk/sounds/$domain/system/$recording_name.wav,,,k";
        return $parameters;
    }


    function createNewRecording($name,$filename,$source,$domain)
    {
        $query="INSERT INTO recordings (name,filename,organization_domain,source) values (?,?,?,?)";
        $result=$this->_DB->genQuery($query,array($name,$filename,$domain,$source));
        if($result==false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return true; 
    }

    function checkFilename($filename)
    {
        $query = "SELECT uniqueid FROM recordings WHERE filename like ?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($filename));
        
        if(count($result)==0)
            return TRUE;
        else
            return FALSE;
    }


    function getId($name,$source,$domain)
    {
        $query = "SELECT uniqueid FROM recordings WHERE name=? and source=? and organization_domain=?";
        $result = $this->_DB->getFirstRowQuery($query, TRUE, array($name,$source,$domain));
        
        if($result != FALSE)
            return $result;
        else{
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
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
            $query="SELECT uniqueid,name,organization_domain FROM recordings WHERE uniqueid in ($q)";
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
            
            $query="DELETE FROM recordings WHERE uniqueid=?";
            foreach($result as $value){
                $this->_DB->beginTransaction();
                if(!$this->_DB->genQuery($query,array($value['uniqueid']))){
                    $error[]=$value['name']." - DATABASE ERROR";
                    $this->_DB->rollBack();
                    continue;
                }
                
                if (basename($value['name']) != $value['name']){
                    $error[]=$value['name']." - possible file attack"; //possible attack
                    $this->_DB->rollBack();
                    continue;
                }
                if($value['organization_domain']!=""){
                    $file="/var/lib/asterisk/sounds/{$value['organization_domain']}/system/{$value['name']}";
                }else{
                    $file="/var/lib/asterisk/sounds/custom/{$value['name']}";
                }
                //procedemos a eliminar los archivos del sistema
                if(file_exists($file)){
                    if(unlink($file)===false){
                        $error[]=$value['name']." - error to delete system";
                        $this->_DB->rollBack();
                    }else{
                        $this->_DB->commit();
                    }
                }else{
                    $this->_DB->commit();
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
