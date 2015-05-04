<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version {ELASTIX_VERSION}                                               |
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
  $Id: paloSantoAsteriskConfig,v 1.1 05/11/2012 rocio mera rmera@palosanto.com Exp $ */

$elxPath="/usr/share/elastix";
include_once "$elxPath/libs/paloSantoConfig.class.php";
include_once "$elxPath/libs/misc.lib.php";

global $arrConf;

class paloSantoASteriskConfig{
    public $errMsg;
    public $_DB; //conexion a la base elxpbx mysql

    //recibe una conexion a la base de elxpbx de mysql
    function paloSantoASteriskConfig(&$pDB)
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

    function getCodeByDomain($domain){
        $query="SELECT code FROM organization where domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($domain));
        if($result===false)
            $this->errMsg=$this->_DB->errMsg;
        elseif(count($result)==0)
            $this->errMsg=_tr("Organization doesn't exist");
        return $result;
    }
    
    function writeExtesionConfFile(){
        $sComando = '/usr/bin/elastix-helper asteriskconfig writeExtensionConf 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return true;
    }

    //Si se falla la momento de crear los archivos, ahi que deshacer los cambios desde donde se llame a esta funcion
    function createOrganizationAsterisk($domain,$country){
        require_once "apps/features_code/libs/paloSantoFeaturesCode.class.php";
        
        //validamos que la organizacion que intentamos crear realmente exista
        $query="SELECT code FROM organization WHERE domain=?";
        $result=$this->_DB->getFirstRowQuery($query, true, array($domain));
        if($result==false){
            $this->errMsg = ($result===false)?$this->_DB->errMsg:_tr("Organization doesn't exist");
        }else{
            $orgCode=$result["code"];
        }
        
        $pFC=new paloFeatureCodePBX($this->_DB,$domain);

        // 1.-Seateamos las configuracions generales para la organizacion en la base de datos
        //	  (sip_settings,iax_settings,voicemail_settings,globals,features_codes)
        // 2.-Creamos dentro de asterisk directorios que van a ser usados por la organizacion
        // 3.-Inclumos los archivos recien creados en con la sentencias include dentro del archivo
        //    extensions.conf y extensions_globals.conf
        // TODO: No se escriben los archivos de configuracion de la organizacion dentro del plan de marcado
        //       hasta que el superadmin cree al admin de la organizacion recien creada
        if($this->setGeneralSettingFirstTime($domain,$orgCode,$country)){
            if($pFC->insertPaloFeatureDB()){
                if($this->setReloadDialplan($domain)){
                    //realizamos la
                    $sComando = '/usr/bin/elastix-helper asteriskconfig createOrganizationAst '.
                        escapeshellarg($domain).'  2>&1';
                    $output = $ret = NULL;
                    exec($sComando, $output, $ret);
                    if ($ret != 0) {
                        $this->errMsg = implode('', $output);
                        return FALSE;
                    }
                    return true;
                }else{
                    $this->errMsg=_tr("Error trying set organizations properties").$this->errMsg;}
            }else
                $this->errMsg=_tr("Error trying set Features Codes").$pFC->errMsg;
        }else{
            $this->errMsg=_tr("Error trying set general settings asterisk. ").$this->errMsg;}

        return false;
    }


    function deleteOrganizationPBX($domain,$code){
        require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
        // 1. Eliminar las entradas dentro de astDB que correspondan a la organizacion
        // 2. actualizamos los  registros de los did que  hayan pertenecido a una organizacion
        $queryd="UPDATE did set organization_domain=NULL where organization_domain=?";
        if($this->_DB->genQuery($queryd,array($domain))==false){
            $this->errMsg .=$this->_DB->errMsg;
            return false;
        }

        //borramos las entradas de la organizacion dentro de astDB
        $errorMng="";
        $astMang=AsteriskManagerConnect($errorMng);
        if($astMang==false){
            $this->errMsg=$errorMng;
            return false;
        }else{ 
            $result=$astMang->database_delTree("EXTUSER/".$code);
            $result=$astMang->database_delTree("DEVICE/".$code);
            $result=$astMang->database_delTree("DND/".$code);
            $result=$astMang->database_delTree("CALLTRACE/".$code);
            $result=$astMang->database_delTree("CFU/".$code);
            $result=$astMang->database_delTree("CFB/".$code);
            $result=$astMang->database_delTree("CF/".$code);
            $result=$astMang->database_delTree("CW/".$code);
            $result=$astMang->database_delTree("BLACKLIST/".$code);
            $result=$astMang->database_delTree("QPENALTY/".$code);
        }
        $astMang->disconnect();
               
        return true;
    }

    private function setGeneralSettingFirstTime($domain,$codeOrg,$country)
    {   
        require_once "apps/general_settings/libs/paloSantoGlobalsPBX.class.php";
        include_once "libs/paloSantoPBX.class.php";
        
        global $arrConf;
        $source_file="/var/www/elastixdir/asteriskconf/globals.conf";
        
        $pGlobals=new paloGlobalsPBX($this->_DB,$domain);
        $res=$pGlobals->insertDBGlobals($country);
        if($res==false){
            $this->errMsg = $pGlobals->errMsg;
            return false;
        }
        
        $reslng=$pGlobals->getGlobalVar("LANGUAGE");
        if($reslng!=false){
            $language=$reslng;
        }
        //sip , iax , voicemail 
        //llenado de tablas tech_settings usando como referencia lo configurado en tech_general
        $psip=new paloSip($this->_DB);
        $piax=new paloIax($this->_DB);
        $pvoicemail=new paloVoicemail($this->_DB);
        foreach(array("sip","iax","voicemail") as $tech){
            if($tech=="voicemail"){
                $query="SELECT * from ".$tech."_general";
                $arrConfig=$this->_DB->getFirstRowQuery($query,true);
            }else{
                $query="SELECT property_name, property_val from ".$tech."_general";
                $arrConfig=$this->_DB->fetchTable($query,true);
            }
            
            if($arrConfig===false){
                $this->errMsg=$this->_DB->errMsg;
                return false;
            }elseif(count($arrConfig)==0){
                $this->errMsg=_tr("Don't exist default parameters ").$tech."_general";
                return false;
            }
            
            $arrSettings=array();
            foreach($arrConfig as $key => $value){
                if($tech=="voicemail"){
                    if(isset($value) && $value!="")
                        $arrSettings[$key]=$value;
                }else{
                    if(isset($value["property_val"]) && $value["property_val"]!="")
                        $arrSettings[$value["property_name"]]=$value["property_val"];
                }
            }
            
            $arrSettings["organization_domain"]=$domain;
            $arrSettings["code"]=$codeOrg;
            if(!empty($language)){
                $arrSettings["language"]=$language;
            }
            $result = ${"p".$tech}->insertDefaultSettings($arrSettings);
            if($result==false){
                $this->errMsg=${"p".$tech}->errMsg;
                return false;
            }
        }
                    
        //settings de la organizacion que crearan cierto plan de marcado por default
        
        //una ruta de salida por default
        $query="insert into outbound_route (routename,outcid_mode,mohsilence,seq,organization_domain) VALUES (?,?,?,?,?)";
        if($this->_DB->genQuery($query,array("out_9","off","default","1",$domain))==false){
            $this->errMsg="Error creating outbound_route. ".$this->_DB->errMsg;
            return false;
        }
        //obtenemos el id de la ruta creada
        $result = $this->_DB->getFirstRowQuery("SELECT LAST_INSERT_ID()",false);
        if($result!=false){
            $outboundid=$result[0];
            $query="insert into outbound_route_dialpattern (outbound_route_id,prefix,match_pattern,seq) VALUES (?,?,?,?)";
            if($this->_DB->genQuery($query,array($outboundid,"9",".","1"))==false){
                $this->errMsg="Error creating outbound_route. ".$this->_DB->errMsg;
                return false;
            }
        }
        //TODO:falta asignarle una truncal de salida. Esto no se puede hacer porque aun no se le
        //ha permitido salida por ninguna truncal a la organizacion
       
        return true;
    }

    /**
        funcion que crear un registro en la tabla reloadDialplan
        esta tabla se utiliza para saber si es necesario mostrar un mensaje
        al adminitranor indicando que se debe reescribir el plan de marcado
        de la organizacion para que los cambios efectudos en la pbx tomen
        efecto dentro de asterisk
    */
    function setReloadDialplan($domain,$reload=false){
        $status=($reload==true)?"yes":"no";
        $query="SELECT show_msg from reload_dialplan where organization_domain=?";
        $estado=$this->_DB->getFirstRowQuery($query, false, array($domain));
        if($estado===false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
            if(is_array($estado) && count($estado)>0)
                $query="UPDATE reload_dialplan SET show_msg=? where organization_domain=?";
            else
                $query="Insert into reload_dialplan (show_msg,organization_domain) values(?,?)";
            $res=$this->_DB->genQuery($query,array($status,$domain));
            if($res==false)
                $this->errMsg = $this->_DB->errMsg;
            return $res;
        }
    }


    function getReloadDialplan($domain){
        $query="SELECT show_msg FROM reload_dialplan WHERE organization_domain=?";
        $estado=$this->_DB->getFirstRowQuery($query, false, array($domain));
        if($estado==false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else
            return $estado[0];
    }

    function generateDialplan($domain,$reload=false){
        //valido que exista el dominio
        //obtenemos el codigo de la organizacion
        $queryCode="SELECT code FROM organization WHERE domain=?";
        $code=$this->_DB->getFirstRowQuery($queryCode, false, array($domain));
        if($code===false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }elseif(count($code)==0){
            $this->errMsg = _tr("Organization dosen't exist");
            return false;
        }

        $sComando = "/usr/bin/elastix-helper asteriskconfig generateDialPlan ".
            escapeshellarg($domain)." ".escapeshellarg($reload)."  2>&1";
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }
}

class paloContexto{
    public $name; //nombre de contexto sin el code de la organizacion a la que pertences
    public $arrExtensions; //arreglo de extensiones que pertenecen al contexto
    public $arrInclude; //include tipo de extension especial, arreglo que ocntige extensiones de este tipo
    public $switch; //swtich tipo de extension especial, arreglo que ocntige extensiones de este tipo
    public $code; //code de la organizacion a la que pertence el contexto
    public $errMsg;

    function paloContexto($code,$name){
        global $arrConf;
        //valido que el codigo exista
        $pDB=new paloDB($arrConf['elastix_dsn']['elastix']);
        $queryCode="SELECT count(code) from organization where code=?";
        $recode=$pDB->getFirstRowQuery($queryCode, false, array($code));
        if($recode===false){
            $this->errMsg = $pDB->errMsg;
            return false;
        }elseif(count($recode)==0){
            $this->errMsg = _tr("Organization doesn't exist");
            return false;
        }

        $this->code=$code;

        if(preg_match("/^[A-Za-z0-9\-_]+$/",$name) || strlen($name)>62){
            if(substr($name,0,6)=="macro-")
                $this->name="[macro-".$this->code."-".substr($name,6)."]";
            else
                $this->name="[".$this->code."-".$name."]";
        }else{
            $this->errMsg=_tr("Context names cannot contain special characters and have a maximum length of 62 characters");
            return false;
        }
    }


    //retorna el contexto como un string para se añadido
    //al plan de marcado, esto es de una contexto especifico
    function stringContexto($arrInclude,$arrExtensions){
        $contexto="\n".$this->name."\n";
        //incluimos los contextos personalizados , TODO: falta preguntar si se los quiere o no incluir
        $contexto .="include =>".substr($this->name,1,-1)."-custom\n";
        if(isset($arrInclude)){
            foreach($arrInclude as $value){
                if(preg_match("/^[A-Za-z0-9\-_]+$/",$value["name"]) || strlen($value["name"])>62){
                    if(substr($this->name,0,6)=="macro-")
                        $contexto .="include =>macro-".$this->code."-".substr($value["name"],6);
                    else
                        $contexto .="include =>".$this->code."-".$value["name"];
                    
                    if(isset($value["extra"])){
                        $contexto .=$value["extra"];
                    }
                    $contexto .="\n";
                }else{
                    $this->errMsg=_tr("Context names cannot contain special characters and have a maximum length of 62 characters");
                    return "";
                }
            }
        }

        if(is_array($arrExtensions)){
            foreach($arrExtensions as $extension){
                if(!is_null($extension) && is_object($extension))
                    $contexto .=$extension->data."\n";
            }
        }
        return $contexto;
    }
}

class paloExtensions{
	public $extension;
	public $priority;
	public $label;
	public $application;
	public $data;

	function paloExtensions($extension,$application,$priority="",$label=""){
		$this->extension=$this->validateExtension($extension);
		$this->priority=$this->validatePriority($priority);
		$this->label=$this->validateLabel($label);
		$this->application=$this->validateApplication($application);
		if($this->extension===false || $this->priority===false || $this->label===false || $this->application===false)
			return false;
		else{
			$this->data="exten => ".$this->extension.",".$this->priority.$this->label.",".$this->application;
			return true;
		}
	}

	function validateExtension($extension){
		if(!isset($extension) || $extension=="")
			return false;
		//if(preg_match("/^[A-Za-z0-9#\*]+$/",$extension) || preg_match("/^_[A-Za-z0-9#\*\.\[\]]+$/",$extension))
			return $extension;
		/*else
			return false;*/
	}
	
	function validatePriority($prioridad){
		if(!isset($prioridad) || $prioridad=="" ||$prioridad=="n")
			return "n";
		elseif(strtolower($prioridad)==("hint"))
			return strtolower($prioridad);
		elseif(preg_match("/[[:digit:]]+/",$prioridad))
			return $prioridad;
		else
			return false;
	}

	function validateLabel($label){
		if(is_null($label) || $label=="")
			return "";
		elseif(preg_match("/^\+[[:digit:]]+$/",$label))
			return $label;
		else
			return '('.$label.')';
	}

	//recibe un objeto de tipo extension
	function validateApplication($application){
		if(!is_object($application))
			return false;
		else{
			if($application->output()=="")
				return false;
			else
				return $application->output();
		}
	}
}
?>
