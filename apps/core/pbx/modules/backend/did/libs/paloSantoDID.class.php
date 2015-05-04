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
  $Id: paloSantoIVR.class.php,v 1.1 2012-09-07 11:50:00 Germán Macas gmacas@palosanto.com Exp $ */

global $arrConf;
  
include_once "libs/paloSantoACL.class.php";
include_once "libs/paloSantoAsteriskConfig.class.php";
include_once "libs/paloSantoPBX.class.php";
    

class paloDidPBX extends paloAsteriskDB{
    
    function paloDidPBX(&$pDB){
        // Se recibe como parámetro una referencia a una conexión paloDB
        parent::__construct($pDB);
    }
    
    /**
     * Obetiene el numero de DID creados dentro del sistema filtrando por los parametros dado
     * $domain -> did que estan asigandos a dicha organizacion
     * $did_number -> expresion que machea con el numero del did
     *                se aceptan las expresiones usadas dentro del plan de marcado en asterisk
     * $country -> pais al que pertence el DID
     * $status -> si el DID se encuentra o no asignado a una organizacion
     */
    function getTotalDID($domain=null,$did_number=null,$country=null,$status=null){
        $where=array();
        $arrParam=array();
        
        $query="SELECT count(did) from did";
        if(!empty($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(!is_null($did_number) && $did_number!=''){
            $expression=$this->getRegexPatternFromAsteriskPattern($did_number);
            if($expression!=false){
                $where[]=" did REGEXP ? ";
                $arrParam[]="^$expression$";
            }
        }
        if(!empty($country)){
            $where[]=" country like ? ";
            $arrParam[]="%$country%";
        }
        if(!empty($status) && $status!='all'){
            if($status=='free'){
                $where[]=" organization_domain IS NULL ";
            }else{
                $where[]=" organization_domain IS NOT NULL ";
            }
        }
        
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
        
        $result=$this->_DB->getFirstRowQuery($query, false, $arrParam);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result[0];
    }
    
    /**
     * Obetiene los DID creados dentro del sistema filtrando por los parametros dado
     * $domain -> did que estan asigandos a dicha organizacion
     * $did_number -> expresion que machea con el numero del did
     *                se aceptan las expresiones usadas dentro del plan de marcado en asterisk
     * $country -> pais al que pertence el DID
     * $status -> si el DID se encuentra o no asignado a una organizacion
     */
    function getDIDs($domain=null,$did_number=null,$country=null,$status=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=array();
        
        $query="SELECT * from did";
        if(!empty($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(!is_null($did_number) && $did_number!=''){
            $expression=$this->getRegexPatternFromAsteriskPattern($did_number);
            if($expression!=false){
                $where[]=" did REGEXP ? ";
                $arrParam[]="^$expression$";
            }
        }
        if(!empty($country)){
            $where[]=" country like ? ";
            $arrParam[]="%$country%";
        }
        if(!empty($status) && $status!='all'){
            if($status=='free'){
                $where[]=" organization_domain IS NULL ";
            }else{
                $where[]=" organization_domain IS NOT NULL ";
            }
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
    
    
    function getDID_id($idDID){
        if(!preg_match("/^[0-9]+$/", $idDID)){
            $this->errMsg="Invalid DID";
            return FALSE;
        }
        
        $query="select * from did where id=?";
        $result=$this->_DB->getFirstRowQuery($query, true, array($idDID));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }
            
        if($result!=false){
            $did_det=$this->getDID_details($result["did"]);
            if($did_det===false){
                return false;
            }else{
                foreach($did_det as $value){
                    if($value["keyword"]=="port"){
                        $result["select_chans"][]=$value["data"];
                    }
                }
            }
        }
        return $result;
    }
    
    function getChannels($type){
        $param=array();
        $where="";
        global $arrConf;
        $pDB = new paloDB("sqlite3:///$arrConf[elastix_dbdir]/hardware_detector.db");
        //los canales disponibles se los obtine de la tabla echo_canceller que pertenece a la base hardware_detector
        if(!is_null($type)){
            if(preg_match("/^(analog|digital)$/",$type)){
                if($type=="analog"){
                    $where=" where name_port=? or name_port=?";
                }else{
                    $where=" where name_port!=? && name_port!=?";
                }
                $param[]="FXO";
                $param[]="FXS";
            }
        }
        
        $query="SELECT num_port,name_port,id_card FROM echo_canceller $where";
        $result=$pDB->fetchTable($query,true,$param);
        if($result===false){
            $this->errMsg=$pDB->errMsg;
            return false;
        }else
            return $result;
    }
    
    function getDID_details($did){
        $query="SELECT * from did_details where did=?";
        $result=$this->_DB->fetchTable($query,true,array($did));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }
    
    function getAnalogChannelsFree(){
        $arrChan=array();
        $temp=array();
        $tmpChann=$this->getChannels("analog");
        if($tmpChann==false){
            return false;
        }
        
        if($tmpChann!=false){
            $query="SELECT did,data from did_details where keyword=?";
            $result=$this->_DB->fetchTable($query,true,array("port"));
            if($result===false){
                $this->errMsg=$this->_DB->errMsg;
                return false;
            }else{
                foreach($result as $value){
                    $num_port=explode("/",$value["data"]);
                    $temp[]=$num_port["1"];
                }
                
                foreach($tmpChann as $value){
                    if(!in_array($value["num_port"],$temp))
                        $arrChan["DAHDI/".$value["num_port"]]="DAHDI/".$value["num_port"];
                }
            }
        }
        return $arrChan; 
    }
    
    function exitsDid($did){
        //verificamos que solo contenga numeros
        $query="SELECT count(did) from did where did=?";
        $result=$this->_DB->getFirstRowQuery($query, false, array($did));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return true;
        }elseif($result[0]!="0")
            return true;
        return false;
    }
    
    function saveNewDID($arrProp){
        //verificamos que solo contenga numeros
        if($this->exitsDid($arrProp["did"])==true){
            $this->errMsg .=_tr("Already exist this DID");
            return false;
        }
        
        if(!preg_match("/^(analog|digital|voip)$/",$arrProp["type"])){
            $this->errMsg .=_tr("Invalid type");
            return false;
        }
        
        $query="INSERT into did (did,type,country,city,country_code,area_code) values(?,?,?,?,?,?)";
        if($this->_DB->genQuery($query,array($arrProp["did"],$arrProp["type"],$arrProp["country"],$arrProp["city"],$arrProp["country_code"],$arrProp["area_code"]))==false){
            $this->errMsg =_tr("DID couldn't be created. ").$this->_DB->errMsg;
            return false;
        }
                
        //en caso de ser analogica se guarda el numero de puerto
        //se comprueba que dicho numero exista, corresponda a una tarjeta analogica
        //y no este asiganado a otro did
        $query="INSERT into did_details values (?,?,?)";
        if($arrProp["type"]=="analog"){
            if(empty($arrProp["select_chans"])){
                $this->errMsg .=_tr("Channels can't be empty. ");
                return false;
            }
            //obtenemos los canales seleccionados
            $arrChann=explode(",",$arrProp["select_chans"]);
            $freeChann=$this->getAnalogChannelsFree();
            foreach($arrChann as $value){
                if($value!=""){
                    if(in_array($value,$freeChann)){
                        if($this->_DB->genQuery($query,array($arrProp["did"],"port",$value))==false){
                            $this->errMsg .=_tr("DID couldn't be created");
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
    
    function saveEditDID($arrProp){
        $idDID=$arrProp["id_did"];
        $did=$this->getDID_id($idDID);
        if($did==false){
            $this->errMsg .=_tr("DID doesn't exist");
            return false;
        }
        $type=$did["type"];
        
        $query="UPDATE did set country=?,city=?,country_code=?,area_code=? where did=?";
        if($this->_DB->genQuery($query,array($arrProp["country"],$arrProp["city"],$arrProp["country_code"],$arrProp["area_code"],$did["did"]))==false){
            $this->errMsg =_tr("DID couldn't be created. ").$this->_DB->errMsg;
            return false;
        }
        
        //en caso de ser analogica se guarda el numero de puerto
        //se comprueba que dicho numero exista, corresponda a una tarjeta analogica
        //y no este asiganado a otro did
        $query="DELETE from did_details where did=? and keyword=?";
        if($this->_DB->genQuery($query,array($did["did"],"port"))==false){
            $this->errMsg .=_tr("DID couldn't be updated. ").$this->_DB->errMsg;
            return false;
        }
        
        $query="INSERT into did_details values (?,?,?)";
        if($type=="analog"){
            if(empty($arrProp["select_chans"])){
                $this->errMsg .=_tr("Channels can't be empty. ");
                return false;
            }
            //obtenemos los canales seleccionados
            $arrChann=explode(",",$arrProp["select_chans"]);
            $freeChann=$this->getAnalogChannelsFree();
            foreach($arrChann as $value){
                if($value!=""){
                    if(in_array($value,$freeChann)){
                        if($this->_DB->genQuery($query,array($did["did"],"port",$value))==false){
                            $this->errMsg .=_tr("DID couldn't be updated. ").$this->_DB->errMsg;
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
    
    //no se debe eliminar un did que ha sido asignado a una organizacion
    function deleteDID($idDID,&$type){
        $did=$this->getDID_id($idDID);
        if($did==false){
            $this->errMsg .=_tr("DID doesn't exist");
            return false;
        }
        
        if(!empty($did["organization_domain"])){
            $this->errMsg .=_tr("DID couldn't be deleted")." "._tr("This DID have been assigned to a organization");
            return false;
        }
        
        $type=$did["type"];
        $query="DELETE from did_details where did=?";
        if($this->_DB->genQuery($query,array($did["did"]))==false){
            $this->errMsg .=_tr("DID couldn't be deleted").$this->_DB->errMsg;
            return false;
        }
        
        $query="DELETE from did where did=?";
        if($this->_DB->genQuery($query,array($did["did"]))==false){
            $this->errMsg .=_tr("DID couldn't be deleted").$this->_DB->errMsg;
            return false;
        }
        return true;
    }
    
    function getDIDFree($arrProp){
        $arrParam=array();
        $query="SELECT id,did,country,city,country_code,area_code FROM did WHERE organization_domain IS NULL";
        if(!empty($arrProp['country'])){
            $query .=" AND country=? ";
            $arrParam[]=$arrProp['country'];
        }
        if(!empty($arrProp['city'])){
            $query .=" AND city=? ";
            $arrParam[]=$arrProp['city'];
        }
        $result=$this->_DB->fetchTable($query,true,$arrParam);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else{
            return $result;
        }
    }
    
    /**
     * Funcion que quita la asignacion de un conjunto de did identificado
     * por sus id de una organizacion identificada por su dominio
     */
    function removeAsignation($select_dids,$domain){
        //comprobamos que la organizacion exista
        $query="SELECT domain,code from organization where domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($domain));
        if($result==false){
            $this->errMsg=($result===false)?$this->_DB->errMsg:_tr("Organization doesn't exist.");
            return false;
        }
        
        if(!is_array($select_dids) || count($select_dids)==0)
            return true;
            
        foreach($select_dids as $value)
            $q="?,";
        $q=substr($q,0,-1);
        
        $select_dids[]=$domain;
        $queryd="UPDATE did SET organization_domain=NULL, organization_code=NULL WHERE id IN ($q) AND organization_domain=?";
        if($this->_DB->genQuery($queryd,$select_dids)==false){
            $this->errMsg .=_tr("DID couldn't be updated. ").$this->_DB->errMsg;
            return false;
        }else
            return true;
    }
    
    /**
     * Funcion que asigna un conjunto de dids identificado
     * por sus id de una organizacion identificada por su dominio
     */
    function assignDIDs($select_dids,$domain){
        $query="SELECT domain,code from organization where domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($domain));
        if($result==false){
            $this->errMsg=($result===false)?$this->_DB->errMsg:_tr("Organization doesn't exist.");
            return false;
        }
        
        if(!is_array($select_dids) || count($select_dids)==0)
            return true;
            
        foreach($select_dids as $value)
            $q="?,";
        $q=substr($q,0,-1);
        
        $query="UPDATE did SET organization_domain=?, organization_code=? WHERE id IN ($q) AND organization_domain IS NULL";
        if($this->_DB->genQuery($query,array($result["domain"],$result["code"],$value))==false){
            $this->errMsg .=_tr("DID couldn't be updated. ").$this->_DB->errMsg;
            return false;
        }else
            return true;
    }
}
?>