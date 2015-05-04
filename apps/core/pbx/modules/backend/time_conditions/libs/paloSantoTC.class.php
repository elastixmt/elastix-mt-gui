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
  | Some functions within this class or script that implements an	     | 	
  | asterisk dialplan are based in FreePBX code.			             |
  | FreePBX® is a Registered Trademark of Schmooze Com, Inc.   		     |
  | http://www.freepbx.org - http://www.schmoozecom.com 		         |
  +----------------------------------------------------------------------+
  $Id: index.php,v 1.1.1.1 2012/07/30 rocio mera rmera@palosanto.com Exp $ */
    include_once "libs/paloSantoACL.class.php";
    include_once "libs/paloSantoAsteriskConfig.class.php";
    include_once "libs/paloSantoPBX.class.php";
    global $arrConf;
    
class paloSantoTC extends paloAsteriskDB{
    protected $code;
    protected $domain;

    function paloSantoTC(&$pDB,$domain)
    {
       parent::__construct($pDB);
        
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloSantoTC").$this->errMsg;
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
    
    function validateDomainPBX(){
        //validamos que la instancia de paloDevice que se esta usando haya sido creda correctamente
        if(is_null($this->code) || is_null($this->domain))
            return false;
        return true;
    }

    function getNumTC($domain=null,$name=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(id) from time_conditions";
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

    
    function getTCs($domain=null,$name=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;

        $query="SELECT id,name,(SELECT tg.name from time_group tg where tg.id=id_tg) as tg_name,id_tg,destination_m,destination_f,organization_domain from time_conditions";
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

    //debo devolver un arreglo que contengan los parametros del TC
    function getTCById($id){
        global $arrConf;
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = _tr("Invalid Time Conditions");
            return false;
        }

        $query="SELECT * from time_conditions where id=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($id,$this->domain));
        
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }elseif(count($result)>0){
            return $result;
        }else
            return false;
    }
    
    private function existTimeConditions($name){
        $query="SELECT 1 from time_conditions where name=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($name,$this->domain));
        if($result===false || count($result)>0){
            $this->errMsg=$this->_DB->errMsg;
            return true;
        }else
            return false;
    } 
    
    private function existTimeGroup($id){
        $query="SELECT 1 from time_group where id=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($id,$this->domain));
        if(is_array($result) && count($result)>0){
            return true;
        }else{
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }
    } 
    
    function getTimeGroup(){
        $tg=array("0"=>_tr("--Select one--"));
        $query="SELECT name,id from time_group where organization_domain=?";
        $result=$this->_DB->fetchTable($query,true,array($this->domain));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
        }else{
            foreach($result as $value){
                $tg[$value["id"]]=$value["name"];
            }
        }
        return $tg; 
    }
    
    function createNewTC($arrProp){
        if(!$this->validateDomainPBX()){
            $this->errMsg=_tr("Invalid Organization");
            return false;
        }
    
        $query="INSERT into time_conditions (name,id_tg,goto_m,destination_m,goto_f,destination_f,organization_domain) values(?,?,?,?,?,?,?)";
                
        if(empty($arrProp["name"])){
            $this->errMsg = _tr("Field Name can't be empty");
            return false;
        }
        if($this->existTimeConditions($arrProp["name"])==true){
            $this->errMsg = _tr("Already exist a Time Conditions with the same name").$this->errMsg;
            return false;
        }
        
        if($this->existTimeGroup($arrProp["id_tg"])==false){
            $this->errMsg = _tr("Selected Time Group doesn't exist").$this->errMsg;
            return false;
        }
        
        //destination match
        if($this->validateDestine($this->domain,$arrProp["destination_m"])!=false){
            $dest_m=$arrProp["destination_m"];
            $tmp=explode(",",$arrProp["destination_m"]);
            $goto_m=$tmp[0];
        }else{
            $this->errMsg=_tr("Invalid destination if match");
            return false;
        }
        
        //destination fail
        if($this->validateDestine($this->domain,$arrProp["destination_f"])!=false){
            $dest_f=$arrProp["destination_f"];
            $tmp=explode(",",$arrProp["destination_f"]);
            $goto_f=$tmp[0];
        }else{
            $this->errMsg=_tr("Invalid destination if fail");
            return false;
        }
        
        //creamos el time_conditions
        $result=$this->executeQuery($query,array($arrProp["name"],$arrProp["id_tg"],$goto_m,$dest_m,$goto_f,$dest_f,$this->domain));
        if($result==false){
            $this->errMsg=$this->errMsg;
            return false;
        }else
            return true;
    }

    function updateTCPBX($arrProp){
        $TC=$this->getTCById($arrProp["id"]);
        if($TC==false){
            $this->errMsg=_tr("Time Conditions doens't exist. ").$this->errMsg;
            return false;
        }
        
        if(empty($arrProp["name"])){
            $this->errMsg = _tr("Field Name can't be empty");
            return false;
        }
        if($TC["name"]!=$arrProp["name"]){
            if($this->existTimeGroup($arrProp["name"])==true){
                $this->errMsg = _tr("Already exist a Time Conditions with the same name").$this->errMsg;
                return false;
            }
        }
        
        //time_group election
        if($this->existTimeGroup($arrProp["id_tg"])==false){
            $this->errMsg = _tr("Selected Time Group doesn't exist").$this->errMsg;
            return false;
        }
        
        //destination match
        if($this->validateDestine($this->domain,$arrProp["destination_m"])!=false){
            $dest_m=$arrProp["destination_m"];
            $tmp=explode(",",$arrProp["destination_m"]);
            $goto_m=$tmp[0];
        }else{
            $this->errMsg=_tr("Invalid destination if match");
            return false;
        }
        
        //destination fail
        if($this->validateDestine($this->domain,$arrProp["destination_f"])!=false){
            $dest_f=$arrProp["destination_f"];
            $tmp=explode(",",$arrProp["destination_f"]);
            $goto_f=$tmp[0];
        }else{
            $this->errMsg=_tr("Invalid destination if fail");
            return false;
        }
        
        $query="UPDATE time_conditions set name=?,id_tg=?,goto_m=?,destination_m=?,goto_f=?,destination_f=? where id=?";
        if($this->executeQuery($query,array($arrProp["name"],$arrProp["id_tg"],$goto_m,$dest_m,$goto_f,$dest_f,$arrProp["id"]))==false){
            $this->errMsg=_tr("Time conditions can't be updated. ").$this->errMsg;
            return false;
        }else
            return true;
    }


    function deleteTC($id){
        $result=$this->getTCById($id);
        if($result==false){
            $this->errMsg=_tr("Time Conditions doens't exist. ").$this->errMsg;
            return false;
        }
        
        $query="DELETE from time_conditions where id=?";
        if($this->executeQuery($query,array($id))==false){
            $this->errMsg=_tr("Time Conditions can't be deleted. ").$this->errMsg;
            return false;
        }else{
            return true;
        } 
    }
    
    private function getTimeGroupParameters($id_tg){
        $arrTg=array();
        $query="SELECT tg_hour,tg_day_w,tg_day_m,tg_month from tg_parameters join time_group on id=id_tg where id_tg=? and organization_domain=?";
        $result=$this->_DB->fetchTable($query,true,array($id_tg,$this->domain));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
        }else{
            foreach($result as $value){
                $arrTg[]=$value["tg_hour"].",".$value["tg_day_w"].",".$value["tg_day_m"].",".$value["tg_month"];
            }
        }
        return $arrTg;
    }
    
    function createDialplanTC(&$arrFromInt){
        if(is_null($this->code) || is_null($this->domain))
            return false;
            
        $arrExt=array();
        $arrCon=array();
        $arrTC=$this->getTCs($this->domain);
        if($arrTC===false){
            $this->errMsg=_tr("Error creating dialplan for time conditions. ").$this->errMsg; 
            return false;
        }else{
            foreach($arrTC as $value){
                $exten=$value["id"];
                $goto_m=$this->getGotoDestine($this->domain,$value["destination_m"]);
                $goto_f=$this->getGotoDestine($this->domain,$value["destination_f"]);
                $goto_m=($goto_m==false || $goto_m=="return")?"return":$goto_m;
                $goto_f=($goto_f==false || $goto_m=="return")?"return":$goto_f;
                $arrTg=$this->getTimeGroupParameters($value["id_tg"]);
                if(is_array($arrTg)){
                    $i=0;
                    foreach($arrTg AS $tg){
                        $i=($i==0)?"1":"";
                        $arrExt[]=new paloExtensions($exten,new ext_gotoiftime($tg,$goto_m),$i);
                        $i++; 
                    }
                    $arrExt[]=new paloExtensions($exten,new extension("Goto(".$goto_f.")"),$i);
                }
            }
            $arrExt[]=new paloExtensions("_X.",new extension("Return()"),"n","return");
            
            if(count($arrTC)>0){
                $context=new paloContexto($this->code,"timeconditions");
                if($context===false){
                    $context->errMsg="timeconditions Error: ".$contextQ->errMsg;
                }else{
                    $context->arrExtensions=$arrExt;
                }
                $arrCon[]=$context;
            }
            return $arrCon;
        }
    }
}
?>
