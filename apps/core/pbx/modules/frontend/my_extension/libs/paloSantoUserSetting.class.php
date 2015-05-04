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
  $Id: paloSantoForm.class.php,v 1.4 2007/05/09 01:07:03 gcarrillo Exp $ */
global $arrConf;
 
class paloMyExten{

    public $_DB;
    private $errMsg;
    private $idUser;

    public function paloMyExten(&$pDB,$idUser){
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

        $this->idUser=$idUser;
    }


    function getErrorMsg(){
        return $this->errMsg;
    }

    /**
     * Returns extension user data
     * It returns also voicemail user data If user have an active voicemail
     */
    function getMyExtension(){
        $myexten=array();
        //1 obtener la extension del mismo de la tabla acl_user
        //2 si no tiene extension retornamos false
        //3 leer los datos de la tabla extension 
        //4 revisamos si tiene un mai lactivo
        //5 si tiene un mail activo obtenemos lo datos del mail
        //6 obtenemos datos de la base astDB

        $exten=$this->getExtensionUser();
        if($exten===false){
            return false;
        }

        $orgInfo=$this->userOrganizationInfo();
        if($orgInfo===false){
            return false;
        }
   
        $callConfiguration=$this->getExtensionDB($exten,$orgInfo['domain']);
        if($callConfiguration===false){
            return false;
        }      

        $dataBaseInternal=$this->getDataBaseInternalByExt($exten,$orgInfo['code']);
        if($dataBaseInternal===false){
            return false;
        }

        $myexten['extension']=$exten;
        $myexten['recordIncoming']=$callConfiguration['record_in'];
        $myexten['recordOutgoing']=$callConfiguration['record_out'];
        $myexten['device']=$callConfiguration['device'];

        $clidName=$dataBaseInternal['clid_name']." <".$dataBaseInternal['clid_number'].">";
        $myexten['clid_name']=$clidName;
        $myexten['language_vm']=$dataBaseInternal['language']; //el mismo valor se aplica para la extension
        $myexten['doNotDisturb']=$dataBaseInternal['do_not_disturb'];
        $myexten['callWaiting']=$dataBaseInternal['call_waiting'];
        $myexten['callForwardOpt']=$dataBaseInternal['call_forward'];
        $myexten['callForwardUnavailableOpt']=$dataBaseInternal['call_f_unavailable'];
        $myexten['callForwardBusyOpt']=$dataBaseInternal['call_f_busy'];    
        
        if($myexten['callForwardOpt']=="yes"){$myexten['callForwardInp']=$dataBaseInternal['number_forward'];}
        if($myexten['callForwardUnavailableOpt']=="yes"){$myexten['callForwardUnavailableInp']=$dataBaseInternal['number_unavailable'];}
        if($myexten['callForwardBusyOpt']){$myexten['callForwardBusyInp']=$dataBaseInternal['number_busy'];}
        

        if(empty($callConfiguration['voicemail']) || $callConfiguration['voicemail']=='novm'){
            $myexten['status_vm']='no';
        }else{
            $myexten['status_vm']='yes';

            //obtener los datos del voicemail
            $vmConfiguration=$this->voicemailConfigurationByExt($exten,$orgInfo['domain']);
            if($vmConfiguration===false){
              return false;
            }//email, password, attach, saycid, envelope, deletevoicemail, language
            $myexten['email_vm']=$vmConfiguration['email'];
            $myexten['password_vm']=$vmConfiguration['password'];
            $myexten['emailAttachment_vm']=$vmConfiguration['attach'];
            $myexten['playEnvelope_vm']=$vmConfiguration['envelope'];
            $myexten['deleteVmail']=$vmConfiguration['deletevoicemail'];
            if($vmConfiguration['saycid']=="yes"){
              $myexten['playCid_vm']= "yes"; 
            }else{
              $myexten['playCid_vm']= "no";
            }
                      
        }

        return $myexten;
    }

    private function getExtensionUser(){
        $query="SELECT extension FROM acl_user WHERE id=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($this->idUser));
        if($result===false){
            $this->errMsg=_tr("DATABASE ERROR").' '.$this->_DB->errMsg;
            return false;
        }elseif(count($result)==0){
            $this->errMsg=_tr("User does not exist").' '.$this->_DB->errMsg;
            return false;
        }else{
            if($result['extension']=='' || is_null($result['extension'])){
                $this->errMsg=_tr("User does not have an extension");
                return false;                
            }else{
                return $result['extension'];
            }            
        }    
    }

    //obtenemos el dominio y el codigo de la organizacion a la que pertenece 
    //el usuario
    private function userOrganizationInfo(){
        $query="select org.id, org.domain, org.code from acl_user acu ".
                    "join acl_group acg on acu.id_group = acg.id ".
                    "join organization org on acg.id_organization = org.id ".
                        "where acu.id=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($this->idUser));
        if($result===false){
            $this->errMsg=_tr("DATABASE ERROR").' '.$this->_DB->errMsg;
            return false;
        }elseif(count($result)==0){
            $this->errMsg=_tr("User does not exist").' '.$this->_DB->errMsg;
            return false;
        }else{
            return $result;   
        }
    }

    //obtenemos Call Forward Configuration del usuario segun su extension y organización
    private function getExtensionDB($exten, $domain){
        $query="select device, voicemail, record_in, record_out, tech FROM extension WHERE exten=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($exten, $domain));
        if($result===false){
            $this->errMsg=_tr("DATABASE ERROR").' '.$this->_DB->errMsg;
            return false;
        }elseif(count($result)==0){
            $this->errMsg=_tr("Extension does not exist").' '.$this->_DB->errMsg;
            return false;
        }else{
            return $result;   
        }
    }


    //obtenemos Voicemail Configuration del usuario segun su extensión y organización
    private function voicemailConfigurationByExt($exten, $domain){
        $query="select email, password, attach, saycid, envelope, deletevoicemail FROM voicemail WHERE mailbox=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($exten, $domain));
        if($result===false){
            $this->errMsg=_tr("DATABASE ERROR").' '.$this->_DB->errMsg;
            return false;
        }elseif(count($result)==0){
            $this->errMsg=_tr("Voicemail Configuration does not exist").' '.$this->_DB->errMsg;
            return false;
        }else{
            return $result;   
        }
    }


    //obtenemos la data de la extension de la base interna de asterisk con la extension y el code_domain
    private function getDataBaseInternalByExt($exten, $org_code){
        
        $astMang=AsteriskManagerConnect($errorM);
		  if($astMang==false){
		    $this->errMsg=_tr("DATABASE ERROR");
		  }else{		
            $familia="EXTUSER/".$org_code."/".$exten;

			$arrExtension["clid_name"]=$astMang->database_get($familia, "cidname");
			$arrExtension["clid_number"]=$astMang->database_get($familia, "cidnum");
            $arrExtension["language"]=$astMang->database_get($familia, "language");
	        $arrExtension["do_not_disturb"]=($astMang->database_get("DND/".$org_code, $exten)=="YES")?"yes":"no";
            $arrExtension["call_waiting"]=($astMang->database_get("CW/".$org_code, $exten)=="ENABLED")?"yes":"no";
            $arrExtension["call_forward"]=($astMang->database_get("CF/".$org_code, $exten)!="")?"yes":"no";
            $arrExtension["number_forward"]=$astMang->database_get("CF/".$org_code, $exten);
            $arrExtension["call_f_unavailable"]=($astMang->database_get("CFU/".$org_code, $exten)!="")?"yes":"no";
            $arrExtension["number_unavailable"]=$astMang->database_get("CFU/".$org_code, $exten);
			$arrExtension["call_f_busy"]=($astMang->database_get("CFB/".$org_code, $exten)!="")?"yes":"no";
            $arrExtension["number_busy"]=$astMang->database_get("CFB/".$org_code, $exten);
            
            return $arrExtension;
          }
    }

    function editExten($arrProp){
        require_once("libs/paloSantoPBX.class.php");
        $errorData=array();
        $errorBoolean= false;

        $exten=$this->getExtensionUser();
        if($exten===false){
            return false;
        }

        $arrProp["exten"]=$exten;

        $orgInfo=$this->userOrganizationInfo();
        if($orgInfo===false){
            return false;
        }
        $arrProp["domain"]=$orgInfo['domain'];
        $arrProp["code"]=$orgInfo['code'];

        $extenDevice=$this->getExtensionDB($exten,$orgInfo['domain']);
        if($extenDevice===false){
            return false;
        }

        $arrProp["device"]=$extenDevice['device'];

        $extenDB=$this->getExtensionDB($exten,$orgInfo['domain']);
        if($extenDB===false){
            return false;
        }  

        //validamos el lenguage
        if(!preg_match('/^[[:alpha:]]+$/', $arrProp['language'])){
            $errorData['field'][] = "language_vm";
              
            $errorBoolean= true;       
        }
        
        
        if($arrProp['create_vm']=="yes"){
            //validar que ingrese un password numerico
            if($arrProp['vmpassword']!=''){
              if(!preg_match('/^[0-9]+$/', $arrProp['vmpassword'])){
                $errorData['field'][] = "password_vm";
                $errorBoolean= true;        
              } 
            }else{
                $errorData['field'][] = "password_vm";
                $errorBoolean= true;
            } 
                        

            if($arrProp['vmattach']=='yes'){
              if(!filter_var($arrProp['vmemail'], FILTER_VALIDATE_EMAIL)){
                $errorData['field'][] = "email_vm";
                $errorBoolean= true;     
              } 
            }      
        }else{
            $arrProp['create_vm']=="no";
        }

        //callforward
        if($arrProp['callForwardOpt']=="yes"){
        //validamos que haya ingresado un numero a marcar en el campo callForwardUnavailableInp    
              if(!preg_match('/^[0-9]+$/', $arrProp['callForwardInp'])){
                $errorData['field'][] = "callForwardInp";
                $errorBoolean= true;
              }           
        }

        if($arrProp['callForwardUnavailableOpt']=="yes"){
        //validamos que haya ingresado un numero a marcar en el campo callForwardUnavailableInp    
              if(!preg_match('/^[0-9]+$/', $arrProp['callForwardUnavailableInp'])){
                $errorData['field'][] = "callForwardUnavailableInp";
                $errorBoolean= true;      
              }           
        }

        if($arrProp['callForwardBusyOpt']=="yes"){
        //validamos que haya ingresado un numero a marcar en el campo callForwardUnavailableInp    
              if(!preg_match('/^[0-9]+$/', $arrProp['callForwardBusyInp'])){
                $errorData['field'][] = "callForwardBusyInp";
                $errorBoolean= true;     
              }           
        }

        if($errorBoolean){
            $errorData['stringError'] = "Some fields are wrong";
            $this->errMsg = $errorData;
            return false;
        }

        //primero guardamos el voicemail
        if(!$this->saveVoicemail($arrProp)){
            return false;
        }

        //guaradamos los cambios en la tabla extension
        //validamos los recording
		switch(strtolower($arrProp["record_in"])){
			case "always":
				$arrProp["record_in"]="always";
				break;
			case "never":
				$arrProp["record_in"]="never";
				break;
			default:
				$arrProp["record_in"]="on_demand";
				break;
		}

		//validamos los recording
		switch(strtolower($arrProp["record_out"])){
			case "always":
				$arrProp["record_out"]="always";
				break;
			case "never":
				$arrProp["record_out"]="never";
				break;
			default:
				$arrProp["record_out"]="on_demand";
				break;
		}

        if($arrProp['create_vm']=="yes"){
            if($extenDB['voicemail']=='novm'){
                $arrProp['mailbox']=$arrProp["exten"]."@{$orgInfo['code']}-default";
                $arrProp["voicemail_context"]="{$orgInfo['code']}-default";
            }else{
			    $arrProp['mailbox']=$arrProp["exten"]."@".$extenDB['voicemail'];
			    $arrProp["voicemail_context"]=$extenDB['voicemail'];
            }
		}else{
            $arrProp['mailbox']=NULL;
			$arrProp["voicemail_context"]="novm";
        }
        

        //cambiamos en la tabla exten
        //primero guardamos el voicemail
        if(!$this->setExtension($arrProp)){
            return false;
        }        
        
        //guardamos los cambioe en la tabla sip
        if(!$this->setMailbox($arrProp)){
            return false;
        }

        //guardamos los cambios en la tabla astDB
        if(!$this->insertDeviceASTDB($arrProp)){
            return false;
        }       
        
        return true;
    }

    function saveVoicemail($arrProp){
        $exitoVM=true;
		$pVM=new paloVoicemail($this->_DB);
		$existVM=$pVM->existVoicemail($arrProp['exten'],$arrProp['domain']);
		if($arrProp['create_vm']=="yes"){
            if($existVM==false)
                $arrVoicemail["context"]="default";

			$arrVoicemail['organization_domain']=$arrProp['domain'];
            $arrVoicemail['mailbox']=$arrProp['exten'];
			$arrVoicemail["password"] = isset($arrProp["vmpassword"])?$arrProp["vmpassword"]:null;
			$arrVoicemail["email"] = isset($arrProp["vmemail"])?$arrProp["vmemail"]:null;
			$arrVoicemail["attach"] = isset($arrProp["vmattach"])?$arrProp["vmattach"]:null;
			$arrVoicemail["saycid"] = isset($arrProp["vmsaycid"])?$arrProp["vmsaycid"]:null;
			$arrVoicemail["envelope"] = isset($arrProp["vmenvelope"])?$arrProp["vmenvelope"]:null;
			$arrVoicemail["deletevoicemail"] = isset($arrProp["vmdelete"])?$arrProp["vmdelete"]:null;
			$arrVoicemail["language"] = isset($arrProp["language"])?$arrProp["language"]:null;

			if($existVM){
				$exitoVM=$pVM->updateParameters($arrVoicemail);
			}else{
				$pVM->setVoicemailProp($arrVoicemail,$arrProp['domain']);
				$exitoVM=$pVM->createVoicemail();
			}
		}else{
			if($existVM){
				$exitoVM=$pVM->deletefromDB($arrProp['exten'],$arrProp['domain']);
			}
		}

		if(!$exitoVM){
			$this->errMsg=_tr("Error setting voicemail parameters").$pVM->errMsg;
			return false;
		}
        return true;
    }

    private function setExtension($arrProp){
        
        $query = "UPDATE extension set record_in=?, record_out=?, voicemail=? where exten=? and organization_domain=?";
        $arrParam = array($arrProp['record_in'], $arrProp['record_out'], $arrProp['voicemail_context'], $arrProp['exten'], $arrProp['domain']);
        $result = $this->_DB->genQuery($query,$arrParam);
        
        if($result==false){
            $this->errMsg=_tr("DATABASE ERROR SAVE EXTENSION").' '.$this->_DB->errMsg;
            return false;
        }else{
            return $result;   
        }
    }


    private function setMailbox($arrProp){
        
        $query = "UPDATE sip set mailbox=? where name=?";
        $arrParam = array($arrProp['mailbox'], $arrProp['device']);
        $result = $this->_DB->genQuery($query,$arrParam);
        
        if($result==false){
            $this->errMsg=_tr("DATABASE ERROR SIP MAILBOX").' '.$this->_DB->errMsg;
            return false;
        }else{
            return $result;   
        }
    }


    private function insertDeviceASTDB($arrProp)
    {       
        $arrSetting=array();
        $arrSetting["language"]=isset($arrProp["language"])?$arrProp["language"]:"\"\"";
        $arrSetting["voicemail"]=$arrProp["voicemail_context"];

        //validamos los recording
        switch(strtolower($arrProp["record_out"])){
            case "always":
                $stRecord="out=always";
                break;
            case "never":
                $stRecord="out=never";
                break;
            default:
                $stRecord="out=on_demand";
                break;
        }
        $stRecord .="|";
        switch(strtolower($arrProp["record_in"])){
            case "always":
                $stRecord .="in=always";
                break;
            case "never":
                $stRecord .="in=never";
                break;
            default:
                $stRecord .="in=on_demand";
                break;
        }
        $arrSetting["recording"]=$stRecord;

        $error=false;
        $familia="EXTUSER/{$arrProp['code']}/".$arrProp['exten'];
        $arrInsert=array();

        $errorM="";
        $astMang=AsteriskManagerConnect($errorM);
        if($astMang==false){
            $this->errMsg=$errorM;
            return false;
        }else{ //seteo las propiedades en la base ASTDB de asterisk
            foreach($arrSetting as $key => $value){         
                $result=$astMang->database_put($familia,$key,$value);
                if(strtoupper($result["Response"]) == "ERROR"){  
                    $error=true;
                    break;
                }
            }
        }

        //si se habilito el donotdisturb ingresa ese dato a la base ASTDB
        if(isset($arrProp['doNotDisturb'])){
            if($arrProp['doNotDisturb']=="yes")
                $result=$astMang->database_put("DND/".$arrProp['code'],$arrProp['exten'],"YES");
            else
                $result=$astMang->database_del("DND/".$arrProp['code'],$arrProp['exten']);
        }else
            $result=$astMang->database_del("DND/".$arrProp['code'],$arrProp['exten']);

        if(strtoupper($result["Response"]) == "ERROR"){
            $error=true;
        }

        
        //si se habilito el callwaiting ingresa ese dato a la base ASTDB
        if(isset($arrProp['callwaiting'])){
            if($arrProp['callwaiting']=="yes")
                $result=$astMang->database_put("CW/".$arrProp['code'],$arrProp['exten'],"ENABLED");
            else
                $result=$astMang->database_del("CW/".$arrProp['code'],$arrProp['exten']);
        }else
            $result=$astMang->database_del("CW/".$arrProp['code'],$arrProp['exten']);

        if(strtoupper($result["Response"]) == "ERROR"){
            $error=true;
        }


        //si se habilito el callforward ingresa ese dato a la base ASTDB
        if(isset($arrProp['callForwardOpt'])){
            if($arrProp['callForwardOpt']=="yes")
                $result=$astMang->database_put("CF/".$arrProp['code'],$arrProp['exten'],$arrProp['callForwardInp']);
            else
                $result=$astMang->database_del("CF/".$arrProp['code'],$arrProp['exten']);
        }else
            $result=$astMang->database_del("CF/".$arrProp['code'],$arrProp['exten']);

        if(strtoupper($result["Response"]) == "ERROR"){
            $error=true;
        }


        //si se habilito el callforward Unavailable ingresa ese dato a la base ASTDB
        if(isset($arrProp['callForwardUnavailableOpt'])){
            if($arrProp['callForwardUnavailableOpt']=="yes")
                $result=$astMang->database_put("CFU/".$arrProp['code'],$arrProp['exten'],$arrProp['callForwardUnavailableInp']);
            else
                $result=$astMang->database_del("CFU/".$arrProp['code'],$arrProp['exten']);
        }else
            $result=$astMang->database_del("CFU/".$arrProp['code'],$arrProp['exten']);

        if(strtoupper($result["Response"]) == "ERROR"){
            $error=true;
        }


        //si se habilito el callforward Busy ingresa ese dato a la base ASTDB
        if(isset($arrProp['callForwardBusyOpt'])){
            if($arrProp['callForwardBusyOpt']=="yes")
                $result=$astMang->database_put("CFB/".$arrProp['code'],$arrProp['exten'],$arrProp['callForwardBusyInp']);
            else
                $result=$astMang->database_del("CFB/".$arrProp['code'],$arrProp['exten']);
        }else
            $result=$astMang->database_del("CFB/".$arrProp['code'],$arrProp['exten']);


        if(strtoupper($result["Response"]) == "ERROR"){
            $error=true;
        }

        
        $astMang->disconnect();
        return true;
    }

}
?>
