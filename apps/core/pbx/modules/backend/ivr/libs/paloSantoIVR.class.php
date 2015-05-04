<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.2.0-29                                             |
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
  $Id: paloSantoIVR.class.php,v 1.1 2012-09-07 11:50:00 Germán Macas gmacas@palosanto.com Exp $ */
    include_once "libs/paloSantoACL.class.php";
    include_once "libs/paloSantoAsteriskConfig.class.php";
    include_once "libs/paloSantoPBX.class.php";
	global $arrConf;

class paloIvrPBX extends paloAsteriskDB{
    protected $code;
    protected $domain;
        
    function paloIvrPBX(&$pDB,$domain){
        parent::__construct($pDB);
        
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloIvrPBX").$this->errMsg;
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
    
    function validateIvrPBX(){
        //validamos que la instancia de paloDevice que se esta usando haya sido creda correctamente
        if(is_null($this->code) || is_null($this->domain))
            return false;
        return true;
    }
    
    function getTotalIvr($domain=null,$name=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(id) from ivr";
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
    
    function getIvrs($domain=null,$name=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;

        $query="SELECT * from ivr";
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
        if($result==false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }
    
    //debo devolver un arreglo que contengan los parametros del IVR
    function getIvrById($id){
        if($this->validateIvrPBX()==false){
            return false;
        }
        
        $arrIVR=array();
        $where="";
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = "IVR ID must be numeric";
            return false;
        }

        $query="SELECT * from ivr where id=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($id,$this->domain));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else{
            return $result;
        }
    }
    
    function getArrDestine($idIVR){
        $query="SELECT * from ivr_destination WHERE ivr_id=? order by key_option";
        $result=$this->_DB->fetchTable($query,false,array($idIVR));
    
        if($result==false)
            $this->errMsg=$this->errMsg;
        return $result; 
    }
    
    function existIvrByName($name){
        if($this->validateIvrPBX()==false){
            return false;
        }
        
        $query="SELECT name from ivr where name=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($name,$this->domain));
        if($result===false || count($result)=="1"){
            return true;
        }
        return false;
    }
    
    function insertIVRDB($arrProp,$arrDestine)
    {
        if($this->validateIvrPBX()==false){
            return false;
        }
        
        //validamos que no exista otro ivr con el mismo nombre
        if($this->existIvrByName($arrProp["name"])==true){
            $this->errMsg=_tr("Already exist other IVR with the same name");
            return false;
        }
        
        $arrProp["mesg_invalid"]=($arrProp["mesg_invalid"]=="none")?null:$arrProp["mesg_invalid"];
        $arrProp["mesg_timeout"]=($arrProp["mesg_timeout"]=="none")?null:$arrProp["mesg_timeout"];
        $arrProp["announcement"]=($arrProp["announcement"]=="none")?null:$arrProp["announcement"];
        //falta validar que el id de las grabaciones mandadas realmente exista y que esta pertenezca al mismo dominio
        $query="INSERT INTO ivr (name,announcement,timeout,retvm,directdial,mesg_timeout,loops,mesg_invalid,organization_domain) values (?,?,?,?,?,?,?,?,?)";
        $result=$this->_DB->genQuery($query,array($arrProp["name"],$arrProp['announcement'],$arrProp['timeout'],$arrProp['retvm'],$arrProp['directdial'],$arrProp['mesg_timeout'],$arrProp['loops'],$arrProp['mesg_invalid'],$this->domain));
        if($result==false){
            $this->errMsg=_tr("Error trying created ivr. ").$this->_BD->errMsg;
            return false;
        }
        
        //guardamos las opciones de ivr creado
        //para eso primero obtenemos el id del ivr creado dado el nombre, el cual debe ser unico
        $queryIVR="SELECT id from ivr where name=? and organization_domain=?";
        $resultIVR=$this->_DB->getFirstRowQuery($queryIVR,true,array($arrProp["name"],$this->domain));
        if($resultIVR==false){
            $this->errMsg=_tr("Error trying created ivr. ").$this->_BD->errMsg;
            return false;
        }
        
        $idIvr = $resultIVR["id"];
        //creamos los destinos
        if($this->createDestineIVR($idIvr,$arrDestine)==false){
            $this->errMsg=_tr("Error trying created ivr destinies. ").$this->errMsg;
            return false;
        }
        
        return true; 
    }
    
    function updateIVRDB($arrProp,$idIvr,$arrDestine)
    {
        if($this->validateIvrPBX()==false){
            return false;
        }
        
        //validamos que exista el ivr y obtenemos su nombre actual
        $exist=$this->getIvrById($idIvr);
        if($exist==false){
            $this->errMsg=_tr("IVR doesn't exist. ").$this->errMsg;
            return false;
        }
        
        //si se cambia de nombre al ivr se verifica que este no se este usando
        if($exist["name"]!=$arrProp["name"]){
            if($this->existIvrByName($arrProp["name"])==true){
                $this->errMsg=_tr("Already exist other IVR with the same name");
                return false;
            }
        }
        
        $arrProp["mesg_invalid"]=($arrProp["mesg_invalid"]=="none")?null:$arrProp["mesg_invalid"];
        $arrProp["mesg_timeout"]=($arrProp["mesg_timeout"]=="none")?null:$arrProp["mesg_timeout"];
        $arrProp["announcement"]=($arrProp["announcement"]=="none")?null:$arrProp["announcement"];
        //falta validar que el id de las grabaciones mandadas realmente exista y que esta pertenezca al mismo dominio
        $query="UPDATE ivr set name=?,announcement=?,timeout=?,retvm=?,directdial=?,mesg_timeout=?,loops=?,mesg_invalid=? where id=? and organization_domain=?";
        $result=$this->_DB->genQuery($query,array($arrProp["name"],$arrProp['announcement'],$arrProp['timeout'],$arrProp['retvm'],$arrProp['directdial'],$arrProp['mesg_timeout'],$arrProp['loops'],$arrProp['mesg_invalid'],$idIvr,$this->domain));
        if($result==false){
            $this->errMsg=_tr("Error trying updated ivr. ").$this->_BD->errMsg;
            return false;
        }
        
        //borramos los destinos anteriores para luego crear los nuevos
        $queryD="DELETE from ivr_destination where ivr_id=?";
        $result=$this->_DB->genQuery($queryD,array($idIvr));
        if($result==false){
            $this->errMsg=_tr("Error trying created ivr destinies. ").$this->_DB->errMsg;
            return false;
        }
        
        //creamos nuevamente los destinos
        if($this->createDestineIVR($idIvr,$arrDestine)==false){
            $this->errMsg=_tr("Error trying created ivr destinies. ").$this->errMsg;
            return false;
        }
        return true;
    }
    
    private function createDestineIVR($idIvr,$arrDestine){
        $result=true;
        setType($idIvr,"integer");
        //tenemos la lista de destinos
        //validamos que sean desinos validos dentro de la organzacion, en caso de no serlos se descartan
        //validamos que la extension a marcar no sea vacio
        foreach($arrDestine as $destine){
            $ivr_ret = $destine["4"];
            $option = $destine["1"];
            $goto = $destine["2"];
            $destine = $destine["3"];
            
            if(preg_match("/^(([0-9\#\*]+)|(i|t){1})$/",$option)){
                if(isset($destine)){
                    if($this->validateDestine($this->domain,$destine)!=false){
                        $tmp=explode(",",$destine);
                        $query="INSERT INTO ivr_destination (key_option,type,destine,ivr_return,ivr_id) values (?,?,?,?,?)";
                        $result=$this->_DB->genQuery($query,array($option,$tmp[0],$destine,$ivr_ret,$idIvr));
                        if($result==false){
                            $this->errMsg=$this->_DB->errMsg;
                            break;
                        }
                    }
                }
            }else{
                $result=false;
                $this->errMsg =_tr("Invalid exten")." '$option' "._tr("in destination")." $goto ";
                break;
            }
        }
        return $result; 
    }
    
    function deleteIVRDB($idIVR)
    {
        if($this->validateIvrPBX()==false){
            return false;
        }
        setType($idIVR,"integer");
    
        $queryDeleteIVRDestine="delete from ivr_destination where ivr_id=?";
        $result=$this->_DB->genQuery($queryDeleteIVRDestine,array($idIVR));
        if($result==false){
            $this->errMsg=_tr("Error deleting IVR destinies. ").$this->_DB->errMsg;
            return false;
        }

        $queryDeleteIVR="delete from ivr where id=? and organization_domain=?";
        $result=$this->_DB->genQuery($queryDeleteIVR,array($idIVR,$this->domain));
        if($result==false){
            $this->errMsg=_tr("Error deleting IVR. ").$this->_DB->errMsg;
        }

        return $result; 
    }
    
    function createDialPlanIvr(&$arrFromInt){
        if($this->validateIvrPBX()==false){
            return false;
        }
        
        $arrConIvr=array();
        
        //obtenemos los ivrs creados en el sistema
        $ivrs=$this->getIvrs($this->domain);
        if($ivrs===false){
            $this->errMsg=_tr("Error creating dialplan for ivr. ").$this->errMsg; 
            return false;
        }else{
            foreach($ivrs as $ivr){
                $arrIvr=array();
                $announcement="";
                if(isset($ivr["announcement"])){
                    $file=$this->getFileRecordings($this->domain,$ivr["announcement"]);
                    if(isset($file))
                        $announcement=$file;
                }
                
                $arrIvr[]=new paloExtensions("s",new ext_setvar('COUNT',"0"),"1");
                $arrIvr[]=new paloExtensions("s",new ext_setvar('MSG', "$announcement"));
                $arrIvr[]=new paloExtensions("s",new ext_setvar('_IVR_CONTEXT_${CONTEXT}', '${IVR_CONTEXT}'));
                $arrIvr[]=new paloExtensions("s",new ext_setvar('_IVR_CONTEXT', '${CONTEXT}'));
                $arrIvr[]=new paloExtensions("s",new ext_gotoif('$["${CDR(disposition)}" = "ANSWERED"]','start'));
                $arrIvr[]=new paloExtensions("s",new ext_wait(1));
                $arrIvr[]=new paloExtensions("s",new ext_answer());
                $timeout=10;
                if(preg_match("/^[0-9]+$/",$ivr["timeout"])){
                    if($ivr["timeout"]!=0){
                        $timeout=$ivr["timeout"]+0;
                    }
                }
                $arrIvr[]=new paloExtensions("s",new ext_setvar("TIMEOUT(digit)","3"),"n","start");
                $arrIvr[]=new paloExtensions("s",new ext_setvar("TIMEOUT(response)",$timeout));
                $return="";
                if(isset($ivr["retvm"])){
                    if($ivr["retvm"]=="yes")
                        $return="RETURN";
                }
                $arrIvr[]=new paloExtensions("s",new ext_setvar('__IVR_RETVM', "$return"));
                $arrIvr[]=new paloExtensions("s",new ext_execif('$["${MSG}"!=""]','Background','${MSG}'));
                $arrIvr[]=new paloExtensions("s",new ext_waitexten());
                
                $i_mesg=$t_mesg=false;
                if(isset($ivr["mesg_invalid"])){
                    $file=$this->getFileRecordings($this->domain,$ivr["mesg_invalid"]);
                    if(isset($file))
                        $i_mesg=$file;
                }    
                if(isset($ivr["mesg_timeout"])){
                    $file=$this->getFileRecordings($this->domain,$ivr["mesg_timeout"]);
                    if(isset($file))
                        $t_mesg=$file;
                } 
                
                $arrDest=array();
                $query="SELECT key_option,destine,ivr_return FROM ivr_destination where ivr_id=? order by key_option";
                $destinations=$this->_DB->fetchTable($query,true,array($ivr["id"]));
                if($destinations!=false){
                    foreach($destinations as $value){
                        $goto=$key=false;
                        if(isset($value["destine"]))
                            $goto=$this->getGotoDestine($this->domain,$value["destine"]);
                            
                        if(preg_match("/^(([0-9\#\*]+)|(i|t){1})$/",$value["key_option"]))
                            $key=$value["key_option"];
                            
                        if($goto!=false && $key!==false && $key!==""){
                            $arrIvr[]=new paloExtensions($key, new ext_dbdel('${BLKVM_OVERRIDE}'),"1");
                            $arrIvr[]=new paloExtensions($key, new extension("Goto(".$goto.")"));
                            $arrIvr[]=new paloExtensions($key, new ext_setvar('__NODEST', ''));
                            $arrDest[]=$key;
                        }
                    }
                }   
                
                //si no se ha defino destinos para la extensiones 'i' y 't' las creamos
                if(!in_array("i",$arrDest)){
                    $arrIvr[]=new paloExtensions("i",new ext_playback("invalid"),"1");
                    if($i_mesg!=false)
                        $arrIvr[]=new paloExtensions("i",new ext_setvar('MSG', "$i_mesg"));
                    $arrIvr[]=new paloExtensions("i",new ext_goto("1","repeat"));
                }
                
                if(!in_array("t",$arrDest)){
                    if($t_mesg!=false){
                        $arrIvr[]=new paloExtensions("t",new ext_setvar('MSG', "$t_mesg"),"1");
                        $arrIvr[]=new paloExtensions("t",new ext_goto("1","repeat"));
                    }else
                        $arrIvr[]=new paloExtensions("t",new ext_goto("1","repeat"),"1");
                }
                
                //aqui se cuelgan las llamadas que no son contestadas
                $arrIvr[]=new paloExtensions("hang",new ext_playback("vm-goodbye"),"1");
                $arrIvr[]=new paloExtensions("hang",new ext_hangup());
                
                $repeat=2;
                if(preg_match("/^[0-9]+$/",$ivr["loops"]))
                    $repeat=$ivr["loops"]+0;
                if($repeat!="0"){
                    $arrIvr[]=new paloExtensions("repeat",new ext_setvar('COUNT','$[${COUNT} + 1]'),"1");
                    $arrIvr[]=new paloExtensions("repeat",new ext_gotoif('$[${COUNT} > '.$repeat.']',"hang,1","s,start"));
                }else{
                    $arrIvr[]=new paloExtensions("repeat",new ext_goto("1","hang"),"1");
                }
                
                $arrIvr[]=new paloExtensions("return",new ext_setvar('MSG', "$announcement"),"1");
                $arrIvr[]=new paloExtensions("return",new ext_setvar('_IVR_CONTEXT','${CONTEXT}'));
                $arrIvr[]=new paloExtensions("return",new ext_setvar('_IVR_CONTEXT_${CONTEXT}','${IVR_CONTEXT_${CONTEXT}}'));
                $arrIvr[]=new paloExtensions("return",new ext_goto("start",'s'));
                $arrIvr[]=new paloExtensions("h",new ext_hangup(),"1");
                
                $context=new paloContexto($this->code,"ivr-".$ivr["id"]);
                if($context===false){
                    $context->errMsg="ivr-".$ivr["id"]." Error: ".$contextQ->errMsg;
                }else{
                    $context->arrExtensions=$arrIvr;
                    if(isset($ivr["directdial"])){
                        if($ivr["directdial"]=="yes")
                            $context->arrInclude=array(array("name"=>"from-did-direct-ivr"));
                    }
                }
                print_r($context->errMsg);
                $arrConIvr[]=$context;
            }
            return $arrConIvr;
        }
    }
}
?>
