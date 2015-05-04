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
  $Id: paloSantoExtensions.class.php,v 1.1 2012-07-18 11:50:00 Rocio Mera rmera@palosanto.com Exp $ */
    include_once "libs/paloSantoACL.class.php";
	include_once "libs/paloSantoAsteriskConfig.class.php";
	include_once "libs/paloSantoPBX.class.php";
	global $arrConf;
class paloSantoExtensions{
    var $_DB; //conexion base de mysql elxpbx
    var $errMsg;

    function paloSantoExtensions(&$pDB)
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

    function getNumExtensions($domain=null,$extension=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(id) from extension";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($extension) && $extension!=''){
            $pPBX=new paloAsteriskDB($this->_DB);
            $expression=$pPBX->getRegexPatternFromAsteriskPattern($extension);
            if($expression!=false){
                $where[]=" exten REGEXP ? ";
                $arrParam[]="^$expression$";
            }
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
        
        $query .= " order by cast(exten as UNSIGNED) asc ";
        $result=$this->_DB->getFirstRowQuery($query,false,$arrParam);
        if($result==false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result[0];
    }

    function getExtensions($domain=null,$extension=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;

        $query="SELECT * from extension";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($extension) && $extension!=''){
            $pPBX=new paloAsteriskDB($this->_DB);
            $expression=$pPBX->getRegexPatternFromAsteriskPattern($extension);
            if($expression!=false){
                $where[]=" exten REGEXP ? ";
                $arrParam[]="^$expression$";
            }
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
       
        $query .= " order by cast(exten as UNSIGNED) asc "; 
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

    function getExtensionByNum($domain,$exten){
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
            return false;
        }

        $query="SELECT * from extension where organization_domain=? and exten=?";
        $arrParam=array($domain,$exten);
        $result=$this->_DB->fetchTable($query,true,$arrParam);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }

    private function getOrganizationCode($domain){
        $query="SELECT code FROM organization WHERE domain=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($domain));
        if($result==false){
            $this->errMsg=_tr("An error has ocurred to get organization code");
            return false;
        }else{
            return $result[0];
        }
    }
    
    //debo devolver un arreglo que contengan los parametros de la extension, dispositivo y voicemail
    function getExtensionById($id,$domain=null){
        global $arrConf;
        $arrExtension=array();
        $where="";
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = "Extension ID must be numeric";
            return false;
        }

        $param=array($id);
        if(isset($domain)){
            if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
                $this->errMsg="Invalid domain format";
                return false;
            }else{
                $where=" and organization_domain=?";
                $param[]=$domain;
            }
        }

        $query="SELECT tech, exten, outboundcid, rt, record_in, record_out, organization_domain, voicemail, device, clid_name, clid_number,alias,elxweb_device,enable_chat from extension where id=? $where";
        $result=$this->_DB->getFirstRowQuery($query,true,$param);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }elseif(count($result)>0){
            //get organization_code
            $org_code=$this->getOrganizationCode($result["organization_domain"]);
            if($org_code===false){
                return false;
            }
            
            $arrExtension["technology"]=$result["tech"];
            $arrExtension["exten"]=$result["exten"];
            $arrExtension["clid_name"]=$result["clid_name"];
            $arrExtension["clid_number"]=$result["clid_number"];
            $arrExtension["ring_timer"]=$result["rt"];
            $arrExtension["record_in"]=$result["record_in"];
            $arrExtension["record_out"]=$result["record_out"];
            $arrExtension["out_clid"]=$result["outboundcid"];
            $arrExtension["alias"]=$result["alias"];
            $arrExtension["elxweb_device"]=$result["elxweb_device"];
            $arrExtension["enable_chat"]=$result["enable_chat"];
            
            //obtenemos las caracteristicas de voicemail de la extension en caso de que este tenga creada uno
            if(isset($result["voicemail"]) && $result["voicemail"]!="novm"){
                $query="SELECT * from voicemail where mailbox=? and organization_domain=?";
                $voicemail=$this->_DB->getFirstRowQuery($query,true,array($result["exten"],$result["organization_domain"]));
                if($voicemail==false){
                    $arrExtension["create_vm"]="no";
                    if($voicemail===false)
                        $this->errMsg .=_tr("Error getting voicemail").$this->_DB->errMsg;
                }else{
                    $arrExtension["create_vm"]="yes";
                    $option="";
                    foreach($voicemail as $key => $value){
                        switch($key){
                            case "password":
                                $arrExtension["vmpassword"]=$value;
                                break;
                            case "email":
                                $arrExtension["vmemail"]=$value;
                                break;
                            case "attach":
                                $arrExtension["vmattach"]=$value;
                                break;
                            case "saycid":
                                $arrExtension["vmsaycid"]=$value;
                                break;
                            case "deletevoicemail":
                                $arrExtension["vmdelete"]=$value;
                                break;
                            case "envelope":
                                $arrExtension["vmenvelope"]=$value;
                                break;
                            case "context":
                                $arrExtension["vmcontext"]=substr_replace($value,'',0,strlen($org_code)+1); //eliminamos la parte del string que contiene el codigo de la organization
                                break;
                            case "emailsubject":
                                $arrExtension["vmemailsubject"]=$value;
                                break;
                            case "emailbody":
                                $arrExtension["vmemailbody"]=$value;
                                break;
                            case "mailbox":
                                break;
                            case "fullname":
                                break;
                            default:
                                if(isset($value)){
                                    if($key!="uniqueid" && $key!="organization_domain" && $key!="stamp" && $key!="dialout" && $key!="callback"){
                                        $option .="$key=$value|";
                                    }
                                    if($key=="dialout" || $key=="callback" || $key=="exitcontext"){
                                        $option .="$key=".substr($arrExten["vmcontext"],16)."|";
                                    }
                                }
                        }
                        $arrExtension["vmoptions"] = empty($option)?"":substr($option,0,-1);
                    }
                }
            }
            //obtenemos las otras caracticas de la configuracion del dispositivo
            if($result["tech"]=="iax2"){
                $queryDev="SELECT context,dial,host,type,allow,disallow,port,qualify,accountcode,deny,permit,language,amaflags,";
                $queryDev .="defaultip,username,mohinterpret,mohsuggest,transfer,requirecalltoken,mask,jitterbuffer,forcejitterbuffer,";
                $queryDev .="codecpriority,qualifysmoothing,qualifyfreqok,qualifyfreqnotok,encryption,timezone,sendani,adsi from iax where name=? and organization_domain=?";
            }elseif($result["tech"]=="sip"){
                $queryDev="SELECT context,dial,host,type,allow,disallow,port,qualify,accountcode,deny,permit,language,amaflags,";
                $queryDev .="defaultip,username,mohinterpret,mohsuggest,dtmfmode,nat,allowtransfer,namedcallgroup,namedpickupgroup,";
                $queryDev .="mailbox,vmexten,defaultuser,useragent,directmedia,sendrpid,trustrpid,transport,callcounter,busylevel,subscribecontext,videosupport,maxcallbitrate,";
                $queryDev .="qualifyfreq,rtptimeout,rtpholdtimeout,rtpkeepalive,progressinband,g726nonstandard,vmexten from sip where name=?   and organization_domain=?";
            }else{
                $this->errMsg .=_tr("Invalid Technology");
                return false;
            }
            if(isset($queryDev)){
                $device=$this->_DB->getFirstRowQuery($queryDev,true,array($result["device"],trim($result["organization_domain"])));
                if($device==false){
                    $this->errMsg .=_tr("Error getting device settings").$this->_DB->errMsg;
                    return false;
                }else{
                    foreach($device as $key => $value){
                        if(isset($value)){
                            if($key=="namedcallgroup" || $key=="namedpickupgroup" || $key=="context" || $key=='subscribecontext'){
                                $arrExtension[$key]=substr_replace($value,'',0,strlen($org_code)+1);
                            }else{
                                $arrExtension[$key]=$value;
                            }
                        }
                    }
                }
            }

            $arrExtension["domain"]=$result["organization_domain"];
            $arrExtension["device"]=$result["device"];
            
            $pORGZ = new paloSantoOrganization($arrConf['elastix_dsn']['elastix']);
            $orgTmp=$pORGZ->getOrganizationByDomain_Name($result["organization_domain"]);
            if($orgTmp!=false){
                $astMang=AsteriskManagerConnect($errorM);
                if($astMang==false){
                    $this->errMsg .=$errorM;
                    return false;
                }else{
                    $familia="EXTUSER/".$orgTmp["code"]."/".$result["exten"];
                    $arrExtension["call_waiting"]=($astMang->database_get("CW/".$orgTmp["code"], $result["exten"])=="ENABLED")?"yes":"no";
                    $arrExtension["screen"]=$astMang->database_get($familia,"screen");
                    $enDictate=$astMang->database_get($familia."/dictate", "enabled");
                    $arrExtension["dictate"]=($enDictate=="enabled")?"yes":"no";
                    $arrExtension["dictformat"]=$astMang->database_get($familia."/dictate", "format");
                    $arrExtension["dictemail"]=$astMang->database_get($familia."/dictate", "email");
                    
                    //vmx_locator options
                    $vmx_unavail=$astMang->database_get("$familia/vmx/unavail", "state");
                    $vmx_busy=$astMang->database_get("$familia/vmx/busy", "state");
                    if($vmx_unavail=="enabled" || $vmx_busy=="enabled"){
                        $arrExtension["vmx_locator"]="enabled";
                    }else{
                        $arrExtension["vmx_locator"]="disabled";
                    }
                    
                    if($vmx_unavail=="enabled" && $vmx_busy=="enabled")
                        $arrExtension["vmx_use"]="both";
                    else{
                        if($vmx_unavail=="enabled")
                            $arrExtension["vmx_use"]="unavailable";
                        else
                            $arrExtension["vmx_use"]="busy";
                    }
                    
                    $arrExtension["vmx_extension_0"]=$astMang->database_get($familia."/vmx/0", "ext");
                    if(isset($arrExtension["vmx_extension_0"]) && $arrExtension["vmx_extension_0"]!=""){
                        $arrExtension["vmx_operator"]="off";
                    }else
                        $arrExtension["vmx_operator"]="on";
                        
                    $arrExtension["vmx_extension_1"]=$astMang->database_get($familia."/vmx/1", "ext");
                    $arrExtension["vmx_extension_2"]=$astMang->database_get($familia."/vmx/2", "ext");
                }
            }
        }
        return $arrExtension;
    }

    function getDefaultSettings($domain,$tech){
        require_once "apps/general_settings/libs/paloSantoGlobalsPBX.class.php";
        
        $arrExtension=array();
        $queryV="SELECT attach,context,deletevoicemail,saycid,envelope,emailsubject,emailbody from voicemail_settings where organization_domain=?";
        $resultV=$this->_DB->getFirstRowQuery($queryV,true,array($domain));
        if($resultV==false){
            $this->errMsg .=_tr("Error getting voicemail default settings").$this->_DB->errMsg;
        }else{
            $arrExtension["vmcontext"]=isset($resultV["context"])?$resultV["context"]:null;
            $arrExtension["vmattach"]=isset($resultV["attach"])?$resultV["attach"]:null;
            $arrExtension["vmdelete"]=isset($resultV["deletevoicemail"])?$resultV["deletevoicemail"]:null;
            $arrExtension["vmsaycid"]=isset($resultV["saycid"])?$resultV["saycid"]:null;
            $arrExtension["vmenvelope"]=isset($resultV["envelope"])?$resultV["envelope"]:null;
            $arrExtension["vmemailsubject"]=isset($resultV["emailsubject"])?$resultV["emailsubject"]:null;
            $arrExtension["vmemailbody"]=isset($resultV["emailbody"])?$resultV["emailbody"]:null;
        }
        $arrExtension["vmx_locator"]="disabled";
        $arrExtension["vmx_use"]="both";
        $arrExtension["vmx_operator"]="on";
        
        $pGPBX = new paloGlobalsPBX($this->_DB,$domain);
        $arrExtension["ring_timer"]=$pGPBX->getGlobalVar("RINGTIMER");
        $arrExtension["language"]=$pGPBX->getGlobalVar("LANGUAGE");
        
        return $arrExtension;
    }

    function getVMdefault($domain){
        $arrVM=array();
        $queryV="SELECT attach,context,deletevoicemail,saycid,envelope,emailsubject,emailbody from voicemail_settings where organization_domain=?";
        $resultV=$this->_DB->getFirstRowQuery($queryV,true,array($domain));
        if($resultV==false){
            $this->errMsg .=_tr("Error getting voicemail default settings").$this->_DB->errMsg;
        }else{
            $arrVM["vmcontext"]=isset($resultV["context"])?$resultV["context"]:null;
            $arrVM["vmattach"]=isset($resultV["attach"])?$resultV["attach"]:null;
            $arrVM["vmdelete"]=isset($resultV["deletevoicemail"])?$resultV["deletevoicemail"]:null;
            $arrVM["vmsaycid"]=isset($resultV["saycid"])?$resultV["saycid"]:null;
            $arrVM["vmenvelope"]=isset($resultV["envelope"])?$resultV["envelope"]:null;
            $arrVM["vmemailsubject"]=isset($resultV["emailsubject"])?$resultV["emailsubject"]:null;
            $arrVM["vmemailbody"]=isset($resultV["emailbody"])?$resultV["emailbody"]:null;
        }
        return $arrVM;
    }
}
?>
