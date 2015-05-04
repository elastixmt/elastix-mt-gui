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
  $Id: paloSantoShortcutApps.class.php,v 1.1 2014-03-12 Bruno Macias bmacias@elastix.org Exp $ */

class paloSantoShortcutApps extends paloAsteriskDB{
    protected $code;
    protected $domain;

    function paloSantoShortcutApps(&$pDB,$domain)
    {
       parent::__construct($pDB);
        
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloSantoShortcutApps").$this->errMsg;
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
                $this->errMsg .=_tr("Can't create a new instace of paloSantoShortcutApps").$this->errMsg;
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
    
    function getNumShortcutApps($domain=null,$ShortcutApps_name=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(id) from shortcut_apps";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($ShortcutApps_name) && $ShortcutApps_name!=''){
            $where[]=" UPPER(description) like ?";
            $arrParam[]="%".strtoupper($ShortcutApps_name)."%";
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

    
    function getShortcutApps($domain=null,$shortcut_apps_name=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;

        $query="SELECT * from shortcut_apps";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($shortcut_apps_name) && $shortcut_apps_name!=''){
            $where[]=" UPPER(description) like ?";
            $arrParam[]="%".strtoupper($shortcut_apps_name)."%";
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

    function getShortcutAppsById($id){
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = _tr("Invalid ShortcutApps ID");
            return false;
        }

        $query="SELECT * from shortcut_apps where id=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($id,$this->domain));
        
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }elseif(count($result)>0){
            return $result;
        }else
              return false;
    }
    
    function createNewShortcutApps($arrProp){
        if(!$this->validateDomainPBX()){
            $this->errMsg=_tr("Invalid Organization");
            return false;
        }
    
        $query="INSERT INTO shortcut_apps (";
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
        
        if(isset($arrProp["exten"])){
            $query .="exten,";
            $arrOpt[count($arrOpt)]=$arrProp["exten"];
        }

        if(isset($arrProp["destination"])){
            if($this->validateDestine($this->domain,$arrProp["destination"])!=false){
                $query .="destination,goto";
                $arrOpt[count($arrOpt)]=$arrProp["destination"];
                $tmp=explode(",",$arrProp["destination"]);
                $arrOpt[count($arrOpt)]=$tmp[0];
            }else{
                $this->errMsg="Invalid destination";
                return false;
            }
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

    function updateShortcutAppsPBX($arrProp){
        $query="UPDATE shortcut_apps SET ";
        $arrOpt=array();

        $result=$this->getShortcutAppsById($arrProp["id"]);
        if($result==false){
            $this->errMsg=_tr("ShortcutApps doesn't exist").$this->errMsg;
            return false;
        }
        $idShortcutApps=$result["id"];
        
        //debe haberse seteado description
        if(!isset($arrProp["description"]) || $arrProp["description"]==""){
            $this->errMsg=_tr("Field 'Description' can't be empty");
            return false;
        }else{
            $query .="description=?,";
            $arrOpt[count($arrOpt)]=$arrProp["description"];
        }
        
        if(isset($arrProp["exten"])){
            $query .="exten=?,";
            $arrOpt[count($arrOpt)]=$arrProp["exten"];
        }
               
        if(isset($arrProp["destination"])){
            if($this->validateDestine($this->domain,$arrProp["destination"])!=false){
                $query .="destination=?,goto=?";
                $arrOpt[count($arrOpt)]=$arrProp["destination"];
                $tmp=explode(",",$arrProp["destination"]);
                $arrOpt[count($arrOpt)]=$tmp[0];
            }else{
                $this->errMsg="Invalid destination";
                return false;
            }
        }       
        
        //caller id options                
        $query = $query." WHERE id=?"; 
        $arrOpt[count($arrOpt)]=$idShortcutApps;
        $result=$this->executeQuery($query,$arrOpt);
        if($result==false)
            $this->errMsg=$this->errMsg;
        return $result; 
         
    }


    function deleteShortcutApps($id){
        $result=$this->getShortcutAppsById($id);
        if($result==false){
            $this->errMsg=_tr("ShortcutApps doesn't exist").$this->errMsg;
            return false;
        }
        
        $query="DELETE from shortcut_apps where id=?";
        if($this->executeQuery($query,array($id))){
            return true;
        }else{
            $this->errMsg = _tr("ShortcutApps can't be deleted.").$this->errMsg;
            return false;
        } 
    }
    
    function createDialplanShortcutApps(&$arrFromInt){
        if(is_null($this->code) || is_null($this->domain))
            return false;
            
        $arrShortcutApps = $this->getShortcutApps($this->domain);
        if($arrShortcutApps===false){
            $this->errMsg=_tr("Error creating dialplan for ShortcutApps. ").$this->errMsg; 
            return false;
        }
        else{
            $arrContext = array();
            
            foreach($arrShortcutApps as $value){
                $exten = $value['exten'];
                $id    = $value['id'];
                
                if(isset($value["destination"])){
                    $goto = $this->getGotoDestine($this->domain,$value["destination"]);
                    if($goto==false)
                        $goto = "h,1";
                }
                    
                $arrExt[]=new paloExtensions($exten, new ext_noop("Running shortcut apps {$id}: {$value['description']}"),"1");
                $arrExt[]=new paloExtensions($exten, new ext_macro($this->code."-user-callerid"),"n");
                $arrExt[]=new paloExtensions($exten, new extension("Goto(".$goto.")"),"n");
                  
            
                //creamos context app-shortcut_apps
                $context = new paloContexto($this->code,"app-shortcut-{$value['id']}");
                if($context === false)
                    $context->errMsg = "app-shortcut. Error: ".$context->errMsg;
                else{
                    $context->arrExtensions = $arrExt;
                    $arrFromInt[]["name"]   = "app-shortcut-{$value['id']}";
                    $arrContext[]           = $context;
                    $arrExt                 = array();
                }
            }
        
            return $arrContext;
        }
    }
}
?>
