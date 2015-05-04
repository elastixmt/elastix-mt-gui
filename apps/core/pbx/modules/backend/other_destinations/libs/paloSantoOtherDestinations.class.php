<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 3.0.0                                                |
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
  $Id: paloSantoOtherDestinations.class.php,v 1.1 2014-03-12 Bruno Macias bmacias@elastix.org Exp $ */

class paloSantoOtherDestinations extends paloAsteriskDB{
    protected $code;
    protected $domain;

    function paloSantoOtherDestinations(&$pDB,$domain)
    {
       parent::__construct($pDB);
        
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloSantoOtherDestinations").$this->errMsg;
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
                $this->errMsg .=_tr("Can't create a new instace of paloSantoOtherDestinations").$this->errMsg;
            }else{
                $this->code=$result["code"];
            }
        }
    }
    
    function getDomain(){
        return $this->domain;
    }
    
    function validateDomainPBX(){
        if(is_null($this->code) || is_null($this->domain))
            return false;
        return true;
    }
    
    function getNumOtherDestinations($domain=null,$OtherDestinations_name=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(id) from other_destinations";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($OtherDestinations_name) && $OtherDestinations_name!=''){
            $where[]=" UPPER(description) like ?";
            $arrParam[]="%".strtoupper($OtherDestinations_name)."%";
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

    
    function getOtherDestinations($domain=null,$other_destinations_name=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;

        $query="SELECT * from other_destinations";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($other_destinations_name) && $other_destinations_name!=''){
            $where[]=" UPPER(description) like ?";
            $arrParam[]="%".strtoupper($other_destinations_name)."%";
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

    function getOtherDestinationsById($id){
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = _tr("Invalid Other Destination ID");
            return false;
        }

        $query="SELECT * from other_destinations where id=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($id,$this->domain));
        
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }elseif(count($result)>0){
            return $result;
        }else
              return false;
    }
    
    function createNewOtherDestinations($arrProp){
        if(!$this->validateDomainPBX()){
            $this->errMsg=_tr("Invalid Organization");
            return false;
        }
    
        $query="INSERT INTO other_destinations (";
        $arrOpt=array();
        
        $query .="organization_domain,";
        $arrOpt[count($arrOpt)]=$this->domain;

        //debe haberse seteado description
        if(!isset($arrProp["description"]) || $arrProp["description"]==""){
            $this->errMsg=_tr("Field 'Description' can't be empty");
            return false;
        }else{
            $query .="description,";
            $arrOpt[count($arrOpt)]=$arrProp["description"];
        }
        
        if(!isset($arrProp["destdial"]) || $arrProp["destdial"]==""){
            $this->errMsg=_tr("Field 'Dial Destination' can't be empty");
            return false;
        }else{
            $query .="destdial";
            $arrOpt[count($arrOpt)]=$arrProp["destdial"];
        }
       
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

    function updateOtherDestinationsPBX($arrProp){
        $query="UPDATE other_destinations SET ";
        $arrOpt=array();

        $result=$this->getOtherDestinationsById($arrProp["id"]);
        if($result==false){
            $this->errMsg=_tr("Other Destination doesn't exist").$this->errMsg;
            return false;
        }
        $idOtherDestinations=$result["id"];
        
        //debe haberse seteado description
        if(!isset($arrProp["description"]) || $arrProp["description"]==""){
            $this->errMsg=_tr("Field 'Description' can't be empty");
            return false;
        }else{
            $query .="description=?,";
            $arrOpt[count($arrOpt)]=$arrProp["description"];
        }
        
        if(!isset($arrProp["destdial"]) || $arrProp["destdial"]==""){
            $this->errMsg=_tr("Field 'Dial Destination' can't be empty");
            return false;
        }else{
            $query .="destdial=?";
            $arrOpt[count($arrOpt)]=$arrProp["destdial"];
        }
        
        //caller id options                
        $query = $query." WHERE id=?"; 
        $arrOpt[count($arrOpt)]=$idOtherDestinations;
        $result=$this->executeQuery($query,$arrOpt);
        if($result==false)
            $this->errMsg=$this->errMsg;
        return $result; 
         
    }


    function deleteOtherDestinations($id){
        $result=$this->getOtherDestinationsById($id);
        if($result==false){
            $this->errMsg=_tr("Other Destination doesn't exist").$this->errMsg;
            return false;
        }
        
        $query="DELETE from other_destinations where id=?";
        if($this->executeQuery($query,array($id))){
            return true;
        }else{
            $this->errMsg = _tr("Other Destination can't be deleted.").$this->errMsg;
            return false;
        } 
    }
    
    function getAdditionalsDestinations()
    {
        require_once("apps/features_code/libs/paloSantoFeaturesCode.class.php");
        require_once("apps/shortcut_apps/libs/paloSantoShortcutApps.class.php");
                
        $pFC   = new paloFeatureCodePBX($this->_DB,$this->domain);
        $arrFC = $pFC->getAllFeaturesCode($this->domain);
 
        
        $FCs = array();
        $SAs = array();
        foreach($arrFC as $kfc => $fc){
            if($fc['estado']=="enabled"){
                $FCs[] = array(
                    "label" => $fc['description'],
                    "code"  => isset($fc['code'])?$fc['code']:$fc['default_code']
                );
            }
        }
        asort($FCs);
        
        $pSA   = new paloSantoShortcutApps($this->_DB,$this->domain);
        $arrSA = $pSA->getShortcutApps($this->domain);
        
        foreach($arrSA as $ksa => $sa){
            $SAs[] = array(
                    "label" => $sa['description'],
                    "code"  => $sa['exten']
                );
        }
        asort($SAs);
        return array("fc" => $FCs, "sa" => $SAs) ;
    }
    
    function createDialplanOtherDestinations(&$arrFromInt){
        if(is_null($this->code) || is_null($this->domain))
            return false;
            
        $arrOtherDestinations = $this->getOtherDestinations($this->domain);
        if($arrOtherDestinations===false){
            $this->errMsg=_tr("Error creating dialplan for OtherDestinations. ").$this->errMsg; 
            return false;
        }
        else{
            $arrContext = array();
            
            foreach($arrOtherDestinations as $value){
                $arrExt[]=new paloExtensions($value['id'], new ext_noop("Other Destination: {$value['description']}"),"1");
                $arrExt[]=new paloExtensions($value['id'], new extension("Goto({$this->code}-from-internal,{$value['destdial']},1)"),"n");
            }
            
            //creamos context other destinations
            $context = new paloContexto($this->code,"ext-otherdestine");
            if($context === false)
                $context->errMsg = "ext-otherdestine. Error: ".$context->errMsg;
                else{
                    $context->arrExtensions = $arrExt;
                    $arrContext[]           = $context;
                    $arrExt                 = array();
                }
        
            return $arrContext;
        }
    }
}
?>
