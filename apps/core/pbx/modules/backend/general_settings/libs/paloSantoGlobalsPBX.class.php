<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version {ELASTIX_VERSION}                                    |
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
  $Id: paloSantoFeatuteCode.class.php,v 1.1 2012/07/30 rocio mera rmera@palosanto.com Exp $ */

/*
* la tabla globals_settings contiene los valores por default de las globales
  que seran usadas para crear las variables globales de
  de cada organizacion

* la tabla globals contiene los valores de la variables globales
  usadas dentro de cada organizacion
*/

include_once "libs/paloSantoACL.class.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoAsteriskConfig.class.php";
include_once "libs/extensions.class.php";
include_once "libs/misc.lib.php";


class paloGlobalsPBX extends paloAsteriskDB{
    protected $code;
    protected $domain;

    function paloGlobalsPBX(&$pDB,$domain){
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            parent::__construct($pDB);
            
            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloGlobalsPBX").$this->errMsg;
            }else{
                $this->code=$result["code"];
            }
        }
    }
	
	function validateGlobalsPBX(){
        //validamos que la instancia de paloDevice que se esta usando haya sido creda correctamente
        if(is_null($this->code) || is_null($this->domain))
            return false;
        return true;
    }
	
	/**
        obtine una lista de los tonos de marcado por paises
        que estan registrados en el archivo indications.conf
        En el archivo indications.conf se define el modo de marcado 
        de cada pais
	*/
    function getToneZonePBX(){
        $arrTZ=array();
        
        $astIndications = "/etc/asterisk/indications.conf";
        $content=file($astIndications);
        if($content===false){
            return false;
        }else{
            foreach($content as $value){
                if(preg_match("/^\[[a-z]{2}\]$/",$value)){
                    $str=str_replace(array("[","]"),"",$value);
                    $arrTz[trim($str)]=$str;
                }
            }
        }
        return $arrTz;
    }
    
    /**
        esta funcion solo es llamada al momento de crear una nueva organizacion dentro del sisitema
    */
    function insertDBGlobals($country){
        if($this->validateGlobalsPBX()==false)
            return false;
                
        $query="INSERT INTO globals values (?,?,?)";

        $arrLngPBX=getLanguagePBX();
        $arrTZPBX=$this->getToneZonePBX();
        //de acuerdo al pais al que pertenece la organizacion se seleccion el
        //pais y el TONEZONE del mismo, en caso de no existir entre los que se
        //encuantrarn configurados en el servidor asterisk, se escogen los valoras por
        //default
        $language=$tonezone="";
        $arrSettings=getCountrySettings($country);
        if($arrSettings!=false){
            if($arrSettings["language"]!=""){
                if(array_key_exists($arrSettings["language"],$arrLngPBX))
                    $language=$arrSettings["language"];
            }
            if($arrSettings["tonezone"]!=""){
                 if(array_key_exists($arrSettings["tonezone"],$arrTZPBX))
                    $tonezone=$arrSettings["tonezone"];
            }
        }
        
        //acabamos de crear la organizacion y llenamos con los valores
        //default de las globales
        $arrProp=$this->getAllGlobalSettings();
        if($arrProp===false){
            return false;
        }else{
            foreach($arrProp as $property){
                switch($property["variable"]){
                    case "LANGUAGE":
                        $value=(empty($language))?$property["value"]:$language;
                        break;
                    case "TONEZONE":
                        $value=(empty($tonezone))?$property["value"]:$tonezone;
                        break;
                    case "MIXMON_DIR":
                        $value=(empty($property["value"]))?"":$property["value"].$this->domain."/";
                        break;
                    case "VMX_CONTEXT":
                        $value=(empty($property["value"]))?"":$this->code."-".$property["value"];
                        break;
                    case "VMX_TIMEDEST_CONTEXT":
                        $value=(empty($property["value"]))?"":$this->code."-".$property["value"];
                        break;
                    case "VMX_LOOPDEST_CONTEXT":
                        $value=(empty($property["value"]))?"":$this->code."-".$property["value"];
                        break;
                    case "TRANSFER_CONTEXT":
                        $value=(empty($property["value"]))?"":$this->code."-".$property["value"];
                        break;
                    default:
                        $value=isset($property["value"])?$property["value"]:"";
                        break;
                }
                $insert=$this->_DB->genQuery($query,array($this->domain,$property["variable"],$value));
                if($insert==false){
                    $this->errMsg=_tr("Problem setting globals variables").$this->_DB->errMsg;
                    break;
                }
            }
            return $insert;
        }
    }
    
    function updateGlobalsDB($arrProp){
        if($this->validateGlobalsPBX()==false)
            return false;
            
        $query="UPDATE globals SET value=? WHERE variable=? and organization_domain=?";
        if($arrProp===false){
            $this->errMsg=_tr("Invalid general properties. ");
            return false;
        }else{
            foreach($arrProp as $name => $property){
                switch($name){
                    case "MIXMON_DIR":
                        $value=(empty($property))?"":$property.$this->domain."/";
                        break;
                    case "VMX_CONTEXT":
                        $value=(empty($property))?"":$this->code."-".$property;
                        break;
                    case "VMX_TIMEDEST_CONTEXT":
                        $value=(empty($property))?"":$this->code."-".$property;
                        break;
                    case "VMX_LOOPDEST_CONTEXT":
                        $value=(empty($property))?"":$this->code."-".$property;
                        break;
                    case "TRANSFER_CONTEXT":
                        $value=(empty($property))?"":$this->code."-".$property;
                        break;
                    default:
                        $value=isset($property)?$property:"";
                        break;
                }
                $update=$this->_DB->genQuery($query,array($value,$name,$this->domain));
                if($update==false){
                    $this->errMsg=_tr("Problem setting globals variables").$this->_DB->errMsg;
                    break;
                }
            }
            return $update;
        }
    }
    
    function getGlobalVar($variable){
        $query="SELECT value FROM globals where organization_domain=? and variable=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($this->domain,$variable));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result[0];
    }
    
    function getGlobalVarSettings($variable){
        $query="SELECT value FROM globals_settings where variable=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($variable));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }
    
    /**
        la tabla globals contiene los valores de la variables globales
        usadas dentro de la organizacion
    */
    function getAllGlobals(){
        $query="SELECT variable,value FROM globals where organization_domain=?";
        
        $result=$this->_DB->fetchTable($query,true,array($this->domain));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }
    
    /**
        la tabla globals_settings contiene los valores por default de las globales
        que seran usadas para crear las variables globales de
        de cada organizacion
    */
    function getAllGlobalSettings(){
        $query="SELECT variable,value FROM globals_settings";
        $result=$this->_DB->fetchTable($query,true);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }
    
    function getGeneralSettings(){
        if($this->validateGlobalsPBX()==false)
            return false;
            
        $arrSettings=array();
        $psip=new paloSip($this->_DB);
        $piax=new paloIax($this->_DB);
        $pvm=new paloVoicemail($this->_DB);
        $arrGlobals=$this->getAllGlobals();
        
        if(is_array($arrGlobals)){
            foreach($arrGlobals as $global){
                if(isset($global["value"])){
                    if($global["variable"]=="VMX_CONTEXT" || $global["variable"]=="VMX_TIMEDEST_CONTEXT" || $global["variable"]=="VMX_LOOPDEST_CONTEXT")
                        $arrSettings[$global["variable"]]=substr($global["value"],16);
                    else
                        $arrSettings[$global["variable"]]=$global["value"];
                }
            }
        }else{
            $this->errMsg=_tr("Error getting globals variables. ").$this->errMsg;
            return false;
        }
        
        foreach(array("sip","iax","vm") as $tech){
            $arrValue = ${"p".$tech}->getDefaultSettings($this->domain);
            if(is_array($arrValue)){
                foreach($arrValue as $key => $value){
                    if(isset($value))
                        $arrSettings[$tech."_".$key]=$value;
                }
            }else{
                $this->errMsg=_tr("Error getting ").$value._tr(" settings ").${"p".$tech}->errMsg;
                return false;
            }
        }
        return $arrSettings;
    }
    
    function setGeneralSettings($arrProp){
        if($this->validateGlobalsPBX()==false)
            return false;
            
        $psip=new paloSip($this->_DB);
        $piax=new paloIax($this->_DB);
        $pvm=new paloVoicemail($this->_DB);
        
        $result=$this->updateGlobalsDB($arrProp["gen"]);
        if($result==false){
            return false;
        }else{
            foreach(array("sip","iax","vm") as $tech){
                if(is_array($arrProp[$tech])){
                    $arrProp[$tech]["organization_domain"]=$this->domain;
                    $result = ${"p".$tech}->updateDefaultSettings($arrProp[$tech]);
                    if($result==false){
                        $this->errMsg=${"p".$tech}->errMsg;
                        return false;
                    }
                }else{
                    $this->errMsg=_tr("Error getting ").$value._tr(" settings ").${"p".$tech}->errMsg;
                    return false;
                }
            }
        }
        return true;
    }
}
?>
