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
  $Id: paloSantoANI.class.php,v 1.1 2014-03-12 Bruno Macias bmacias@elastix.org Exp $ */

class paloSantoANI extends paloAsteriskDB{
    protected $code;
    protected $domain;

    function paloSantoANI(&$pDB,$domain)
    {
       parent::__construct($pDB);
        
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloSantoANI").$this->errMsg;
            }else{
                $this->code=$result["code"];
            }
        }
    }
    
    function getNumANI($domain=null, $ANI_prefix=null)
    {
        $where=array();
        $arrParam=null;

        $query="SELECT count(*) from trunk_organization";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($ANI_prefix) && $ANI_prefix!=''){
            $where[]=" ani_prefix like ?";
            $arrParam[]="%{$ANI_prefix}%";
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

    
    function getANI($domain=null,$ANI_prefix=null,$limit=null,$offset=null)
    {
        $where=array();
        $arrParam=null;

        $query="SELECT torg.*, t.name from trunk_organization torg inner join trunk t on torg.trunkid=t.trunkid";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($ANI_prefix) && $ANI_prefix!=''){
            $where[]=" ani_prefix like ?";
            $arrParam[]="%{$ANI_prefix}%";
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

    function getANI_ByTrunkId($trunkid)
    {
        if (!preg_match('/^[[:digit:]]+$/', "$trunkid")) {
            $this->errMsg = _tr("Invalid Trunk ID");
            return false;
        }

        $query="SELECT * from trunk_organization where trunkid=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($trunkid,$this->domain));
        
        if($result===false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        elseif(count($result)>0){
            return $result;
        }
        else
            return false;
    }
    
    function updateANI_Prefix($ani_trunkid, $ani_prefix)
    {
        $result = $this->getANI_ByTrunkId($ani_trunkid);
        if($result==false){
            $this->errMsg = _tr("Prefix ANI doesn't exist")." ".$this->errMsg;
            return false;
        }
        
        if (!preg_match('/^[[:digit:]]{0,5}$/', "$ani_prefix")) {
            $this->errMsg = _tr("Prefix ANI Invalid, it must be a number maximum 5 digits");
            return false;
        }
        
        $query = "UPDATE trunk_organization SET ani_prefix=? WHERE trunkid=? and organization_domain=?";
        return $this->executeQuery($query,array($ani_prefix,$ani_trunkid,$this->domain)); 
    }
}
?>