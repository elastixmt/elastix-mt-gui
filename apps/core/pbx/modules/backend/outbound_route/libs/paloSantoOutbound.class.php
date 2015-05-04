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
  $Id: index.php,v 1.1.1.1 2012/07/30 rocio mera rmera@palosanto.com Exp $ */
    include_once "libs/paloSantoACL.class.php";
    include_once "libs/paloSantoAsteriskConfig.class.php";
    include_once "libs/paloSantoPBX.class.php";
	global $arrConf;
class paloSantoOutbound extends paloAsteriskDB{
    protected $code;
    protected $domain;

    function paloSantoOutbound(&$pDB,$domain)
    {
       parent::__construct($pDB);
        
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

    function getNumOutbound($domain=null,$name=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(id) from outbound_route";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($name) && $name!=''){
            $where[]=" UPPER(routename) like ?";
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

    function getOutbounds($domain=null,$name=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;
        
        $query="SELECT * from outbound_route";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($name) && $name!=''){
            $where[]=" UPPER(routename) like ?";
            $arrParam[]="%".strtoupper($name)."%";
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
        
        $query .=" order by seq ";
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

    function getTrunks(){
        $where="";
        $arrParam=null;

        $query="SELECT tr.trunkid, tr.name, tr.tech  from trunk as tr join trunk_organization as tor on tr.trunkid=tor.trunkid where organization_domain=?";
        
        $arrTrunk=array();
        $result=$this->_DB->fetchTable($query,true,array($this->domain));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else{
            foreach($result as $value){
                $arrTrunk[$value['trunkid']]=$value['name']."/".strtoupper($value['tech']);
            }
            return $arrTrunk;
        }
    }

    function getTrunkById($id){
        global $arrConf;
        $where="";
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = "Trunk ID must be numeric";
            return false;
        }
        
        $query="SELECT t.trunkid, t.name, t.tech from trunk t where trunkid=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($id));

        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else{
            return $result;
        }
    }    

    function getArrDestine($idOutbound){
        $query="SELECT * from outbound_route_dialpattern WHERE outbound_route_id=? order by seq";
        $result=$this->_DB->fetchTable($query,false,array($idOutbound));

        if($result==false)
            $this->errMsg=$this->errMsg;
        return $result; 
    }

    function getArrTrunkPriority($idOutbound){
        $query="SELECT t.trunkid,t.name,t.tech from outbound_route_trunkpriority o, trunk t WHERE t.trunkid=o.trunk_id AND o.outbound_route_id=? order by o.seq";
            $result=$this->_DB->fetchTable($query,false,array($idOutbound));
        $arrTrunk = array();
        if($result==false)
            $this->errMsg=$this->errMsg;

        foreach($result as $value){
            $arrTrunk[$value[0]]=$value[1]."/".strtoupper($value[2]);
        }
        return $arrTrunk; 
    }
    
    /**
     *   funcion que le devuelve la ruta dado su id. 
     *   Esta ruta debe pertenecer al dominio del parametro domain de la clase
     */
    function getOutboundById($id){
        global $arrConf;
        $arrOutbound=array();
        $where=""; 
        
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = _tr("Invalid Outbound Route");
            return false;
        }
        $param[]=$id;
        
        if(empty($this->domain)){
            $this->errMsg = _tr("Invalid Organization");
            return false;
        }

        $where=" and organization_domain=?";
        $param[]=$this->domain;
        
        $query="SELECT routename,outcid,outcid_mode,routepass,mohsilence,time_group_id,organization_domain,seq ";
            $query.="from outbound_route where id=? $where";
        $result=$this->_DB->getFirstRowQuery($query,true,$param);
        
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }elseif(count($result)>0){
            $arrOutbound["routename"]=$result["routename"];
            $arrOutbound["outcid"]=$result["outcid"];
            $arrOutbound["outcid_mode"]=$result["outcid_mode"];
            $arrOutbound["routepass"]=$result["routepass"];
            $arrOutbound["mohsilence"]=$result["mohsilence"];
            $arrOutbound["time_group_id"]=$result["time_group_id"];
            $arrOutbound["seq"]=$result["seq"];
            $arrOutbound["domain"]=$result["organization_domain"];   			
            return $arrOutbound;
        }
    }

    function createNewOutbound($arrProp,$arrDialPattern,$arrTrunkPriority){
        $query="INSERT INTO outbound_route (";
        $arrOpt=array();

        if(empty($this->domain)){
            $this->errMsg = _tr("Invalid Organization");
            return false;
        }
        
        $query .="organization_domain,";
        $arrOpt[]=$this->domain;
        
        //debe haberse seteado un nombre
        if(!isset($arrProp["routename"]) || $arrProp["routename"]==""){
            $this->errMsg="Name of outbound can't be empty";
        }else{
            $val = $this->checkName($this->domain,$arrProp['routename']);
            if($val==1)
               $this->errMsg="Route Name is already used by another Outbound Route"; 
            else{
               $query .="routename,";
               $arrOpt[]=$arrProp["routename"];
            }
        }

        //si se define un callerid 
        if(isset($arrProp["outcid"])){
            $query .="outcid,";
            $arrOpt[]=$arrProp["outcid"];
        }

        if(isset($arrProp["outcid_mode"])){
            $query .="outcid_mode,";
            $arrOpt[]=$arrProp["outcid_mode"];
        }
      
        //si se define un password
        if(isset($arrProp["routepass"])){
            $query .="routepass,";
            $arrOpt[]=$arrProp["routepass"];
        }
        
        if(!empty($arrProp["time_group_id"])){
            $result=$this->_DB->fetchTable("SELECT 1 from time_group where organization_domain=? and id=?",true,array($this->domain,$arrProp["time_group_id"]));
            if($result!=false){
                $query .="time_group_id,";
                $arrOpt[]=$arrProp["time_group_id"];
            }
        }
        
        if(isset($arrProp["mohsilence"])){
            $query .="mohsilence,";
            $arrOpt[]=$arrProp["mohsilence"];
        }
        
        $query .="seq";
        $arrOpt[]=$this->gatMaxSeq($this->domain)+1;
     
        $query .=")";
        $qmarks = "(";
        for($i=0;$i<count($arrOpt);$i++){
            $qmarks .="?,"; 
        }
        $qmarks=substr($qmarks,0,-1).")"; 
        $query = $query." values".$qmarks;
        if($this->errMsg==""){
            $exito=$this->createOutbound($query,$arrOpt,$arrProp);
        }else{
            return false;
        }

        if($exito==true){
            //si ahi dialpatterns se los procesa
            $result = $this->getFirstResultQuery("SELECT LAST_INSERT_ID()",NULL);
            $outboundid=$result[0];
            if($this->createDialPattern($arrDialPattern,$outboundid)==false){
                $this->errMsg="Outbound can't be created .".$this->errMsg;
                return false;
            }elseif($this->createTrunkPriority($arrTrunkPriority,$outboundid,$this->domain)==false){
                $this->errMsg="Outbound can't be created .".$this->errMsg;
                return false;
            }else
                return true;
        }else
            return false;

    }
    
    //numero de sequencia cambia cuando se crea o se barra una ruta
    //cuando se crea -- buscar el numero de seq  mas alto que tenga
    private function gatMaxSeq($domain){
        $query="Select max(seq) from outbound_route";
        if(preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $query .=" where organization_domain=?";
            $param[]=($domain);
        }
        $result = $this->getFirstResultQuery($query,$param,false);
        if($result==false)
            return 0;
        else
            return $result[0]; 
    }

    private function createOutbound($query,$arrOpt,$arrProp){
        if(!isset($arrProp["routename"]) || $arrProp["routename"]==""){
            $this->errMsg="Outbound can't be created. Route Name can't be empty";
            return false;
        }
        $result=$this->executeQuery($query,$arrOpt);
        
        if($result==false)
            $this->errMsg=$this->errMsg;
        return $result; 
    }

    function updateOutboundPBX($arrProp,$arrDialPattern,$idOutbound,$arrTrunkPriority){
        $query="UPDATE outbound_route SET ";
        $arrOpt=array();

        //verificamos que exista la ruta
        $arrOut=$this->getOutboundById($idOutbound);
        if($arrOut==false){
            $this->errMsg .=_tr("Outbound Route doesn't exist");
            return false;
        }
        
        //debe haberse seteado un nombre
        if(!isset($arrProp["routename"]) || $arrProp["routename"]==""){
            $this->errMsg="Name of outbound can't be empty";
        }else{
            $val = $this->checkName($arrOut['domain'],$arrProp['routename'],$idOutbound);
            if($val==1)
               $this->errMsg="Route Name is already used"; 
            else{
                $query .="routename=?,";
                $arrOpt[0]=$arrProp["routename"];
            }
        }

        //si se define un callerid 
        if(isset($arrProp["outcid"])){
            $query .="outcid=?,";
            $arrOpt[]=$arrProp["outcid"];
        }
      
        if(isset($arrProp["outcid_mode"])){
            $query .="outcid_mode=?,";
            $arrOpt[]=$arrProp["outcid_mode"];
        }
      
        //si se define un password
        if(isset($arrProp["routepass"])){
            $query .="routepass=?,";
            $arrOpt[]=$arrProp["routepass"];
        }

        $query .="time_group_id=?,";
        if(!empty($arrProp["time_group_id"])){
            $result=$this->_DB->fetchTable("SELECT 1 from time_group where organization_domain=? and id=?",true,array($this->domain,$arrProp["time_group_id"]));
            if($result==false){
                $arrOpt[]=NULL;
            }else
                $arrOpt[]=$arrProp["time_group_id"];
        }else
            $arrOpt[]=NULL;
        
        if(isset($arrProp["mohsilence"])){
            $query .="mohsilence=?";
            $arrOpt[]=$arrProp["mohsilence"];
        }
                
        $query = $query." WHERE id=?";
            $arrOpt[]=$idOutbound;
        if($this->errMsg==""){
            $exito=$this->updateOutbound($query,$arrOpt,$arrProp);
        }else{
            return false;
        }

        if($exito==true){
            $resultDelete = $this->deleteDialPatterns($idOutbound);
            $resultDeleteTrunks = $this->deleteTrunks($idOutbound);
            if(($resultDelete==false)||($this->createDialPattern($arrDialPattern,$idOutbound)==false)||($resultDeleteTrunks==false)){
                $this->errMsg="Outbound can't be updated.".$this->errMsg;
                return false;
            }elseif($this->createTrunkPriority($arrTrunkPriority,$idOutbound,$arrOut["domain"])==false){
                $this->errMsg="Outbound can't be updated .".$this->errMsg;
                return false;
            }else
                return true;
        }else
            return false;

    }

    private function updateOutbound($query,$arrOpt,$arrProp){
        if(!isset($arrProp["routename"]) || $arrProp["routename"]==""){
            $this->errMsg="Outbound can't be created. Outbound Name can't be empty";
            return false;
        }
        $result=$this->executeQuery($query,$arrOpt);
        
        if($result==false)
            $this->errMsg=$this->errMsg;
        return $result; 
    }

    private function createDialPattern($arrDialPattern,$outboundid)
    {
        $result=true;
        $seq = 0;
        if(is_array($arrDialPattern) && count($arrDialPattern)!=0){
            $temp=$arrDialPattern;
            $arrPattern= array();
            $query="INSERT INTO outbound_route_dialpattern (outbound_route_id,prepend,prefix,match_pattern,match_cid,seq) values (?,?,?,?,?,?)";
            foreach($arrDialPattern as $pattern){ 
                  $prepend = $pattern[1];
                  $prefix = $pattern[2];
                  $cid = $pattern[4];
                  $pattern = $pattern[3];
                  $seq++;
                
                  $arrPattern=array($prepend,$prefix,$pattern,$cid,$outboundid);

                  //Verificamos que no se haya guardado un dial pattern igual
                  if($this->checkDuplicateDialPattern($arrPattern)===true){
                        if(isset($prepend)){
                            //validamos los campos
                            if(!preg_match("/^[[:digit:]]*$/",$prepend)){
                                $this->errMsg .=_tr("Invalid dial pattern").". Prepend '$prepend'";
                                $result=false;
                                break;
                            }
                        }else
                            $prepend="";
                        
                        if(isset($prefix)){
                            if(!preg_match("/^([XxZzNn[:digit:]]*(\[[0-9]+\-{1}[0-9]+\])*(\[[0-9]+\])*)+$/",$prefix)){
                                $this->errMsg .=_tr("Invalid dial pattern").". Prefix '$prefix'";
                                $result=false;
                                break;
                            }
                        }else
                            $prefix="";

                        if(isset($pattern)){
                            if(!preg_match("/^([XxZzNn[:digit:]]*(\[[0-9]+\-{1}[0-9]+\])*(\[[0-9]+\])*\.{0,1})+$/",$pattern)){
                                $this->errMsg .=_tr("Invalid dial pattern").". Match Pattern '$pattern'";
                                $result=false;
                                break;
                            }
                        }else
                            $pattern="";
                  
                        if($prefix!="" || $pattern!="")
                            $result=$this->executeQuery($query,array($outboundid,$prepend,$prefix,$pattern,$cid,$seq));
                        
                        if($result==false)
                            break;
                }
            }
        }
        return $result;
    }

    private function createTrunkPriority($arrTrunkPriority,$outboundid,$domain)
    {
        $result=true;
        $seq = 0;
        
        $arrTrunk=array();
        //obtengo todas la truncales asignadas a la organizacion
        $query="SELECT tr.trunkid from trunk as tr join trunk_organization as tor on tr.trunkid=tor.trunkid where organization_domain=?";
        $arrTrunk=array();
        $result=$this->_DB->fetchTable($query,false,array($domain));
        if($result===false){
            $this->errMsg .=$this->_DB->errMsg;
            return false;
        }else{
            foreach($result as $value){
                $arrTrunk[]=$value[0];
            }
        }
        
        if(is_array($arrTrunkPriority) && count($arrTrunkPriority)!=0){
            $arrTrunks=array_intersect($arrTrunkPriority,$arrTrunk);
            if(!is_array($arrTrunks) || count($arrTrunks)==0){
                $this->errMsg .=_tr("At least one trunk must be selected");
                return false;
            }
            $query="INSERT INTO outbound_route_trunkpriority (outbound_route_id,trunk_id,seq) values (?,?,?)";
            foreach($arrTrunks as $trunk){ 
                $seq++;
                $result=$this->executeQuery($query, array($outboundid,$trunk,$seq));
                if($result==false){
                    $this->errMsg .="Error setting trunk sequence";
                    return false;
                }
            }
        }else{
            $this->errMsg .=_tr("At least one trunk must be selected");
            $result=false;
        }
        return $result;
    }

    private function checkDuplicateDialPattern($arr){
        $query="SELECT * from outbound_route_dialpattern WHERE prepend=? AND prefix=? AND match_pattern=? AND match_cid=? AND outbound_route_id=?";
        $result=$this->_DB->fetchTable($query,true,$arr);
        if(sizeof($result)==0)  
            return true;
        else
            return false;
    }

    function checkName($domain,$routename,$id_outbound=null){
          $where="";
          if(!isset($id_outbound))
              $id_outbound = "";
          
          $arrParam=null;
          if(isset($domain)){
              if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
                  $this->errMsg="Invalid domain format";
                  return false;
              }else{
                  $where="where organization_domain=? AND id<>? AND routename=? ";
                  $arrParam=array($domain,$id_outbound,$routename);
              }
          }
          
          $query="SELECT routename from outbound_route $where";
          
          $result=$this->_DB->fetchTable($query,true,$arrParam);
          if($result===false){
              $this->errMsg=$this->_DB->errMsg;
              return false;
          }else{
             if ($result==null)
                 return 0;
             else
                 return 1;
            }
    }

    private function deleteDialPatterns($outboundId){
        $queryD="DELETE from outbound_route_dialpattern where outbound_route_id=?";
        $result=$this->_DB->genQuery($queryD,array($outboundId));
        if($result==false){
            $this->errMsg=_tr("Error Deleting Outbound dialpatterns.").$this->_DB->errMsg;
            return false;
        }else
            return true;
              
    }
    
    private function deleteTrunks($outboundId){
        $queryD="DELETE from outbound_route_trunkpriority where outbound_route_id=?";
        $result=$this->_DB->genQuery($queryD,array($outboundId));
        if($result==false){
            $this->errMsg=_tr("Error Deleting Outbound trunkspriority.").$this->_DB->errMsg;
            return false;
        }else
            return true;
    }

    function deleteOutbound($outboundId){
        //verificamos que exista la ruta
        $arrOut=$this->getOutboundById($outboundId);
        if($arrOut==false){
            $this->errMsg .=_tr("Outbound Route doesn't exist");
            return false;
        }
        
        $resultDeleteTrunks = $this->deleteTrunks($outboundId);
        $resultDelete = $this->deleteDialPatterns($outboundId);
        if(($resultDelete==true)&&($resultDeleteTrunks==true)){
            $query="DELETE from outbound_route where id=?";
            if($this->executeQuery($query,array($outboundId))){
                if($this->recalculateSeq($arrOut["domain"]))
                    return true;
                else{
                    $this->errMsg=_tr("Outbound can't be deleted. ").$this->errMsg;
                    return false;
                }
            }else{
                $this->errMsg=_tr("Outbound can't be deleted. ").$this->errMsg;
                return false;
            }
        }else{
            $this->errMsg=_tr("Outbound can't be deleted. ").$this->errMsg;
            return false;
        }     
    }
    
    private function recalculateSeq($domain){
        $query="Select id,seq from outbound_route";
        $param=array();
        if(preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $query .=" where organization_domain=?";
            $param[]=($domain);
        }
        $query .=" order by seq";
        $result = $this->_DB->fetchTable($query,true,$param);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }
        
        $i=1;
        $arrIndex=array();
        foreach($result as $route){
            if($i!=$route["seq"]){
                for($j=$i-1;$j<count($result);$j++){
                    $arrIndex[]=$result[$j]["id"];
                }
                break;
            }
            $i++;
        }
        
        if(count($arrIndex)>0){
            $query="Update outbound_route set seq=seq-1 where id in (".implode(",",$arrIndex).")";
            $result=$this->executeQuery($query,null);
            if($result==false){
                $this->errMsg =_tr("Outbound Route order couldn't be updated. ").$this->errMsg;
                return false;
            }
        }
        return true;
    }
    
    function reorderRoute($routeId,$new_seq){
        //vefrificamos que la tuta exista
        $arrOut=$this->getOutboundById($routeId);
        
        if (!preg_match('/^[[:digit:]]+$/', "$new_seq")) {
            $this->errMsg = _tr("Invalid new Order");
            return false;
        }
        
        if($arrOut==false){
            $this->errMsg = _tr("Route doesn't exist");
            return false;
        }
        
        //la nueva posicion no debe ser menor que 1 ni mayor que el maximo numero de rutas
        $max=$this->gatMaxSeq($arrOut["domain"]);
        if($new_seq<"1"||$new_seq>$max){
            $this->errMsg = _tr("Invalid new Order");
            return false;
        } 
    
        //dos movimietos
        //nueva posicion de la ruta es mayor que la anterior
        if($new_seq>$arrOut["seq"]){
            $condition="seq-1";
            $query="Select id from outbound_route where seq>? and seq<? and organization_domain=?";
            $param=array($arrOut["seq"],$new_seq+1,$arrOut["domain"]);
        }else{
            //nueva posicion de la ruta es menor que la anterior
            $condition="seq+1";
            $query="Select id from outbound_route where seq<? and seq>? and organization_domain=?";
            $param=array($arrOut["seq"],$new_seq-1,$arrOut["domain"]);
        }
        
        $result = $this->_DB->fetchTable($query,true,$param);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }
        
        $arrIndex=array();
        for($j=0;$j<count($result);$j++){
            $arrIndex[]=$result[$j]["id"];
        }
        
        //actualizamos el campo seq de las otras rutas
        if(count($arrIndex)!=0){
            $query="Update outbound_route set seq=$condition where id in (".implode(",",$arrIndex).")";
            $result=$this->executeQuery($query,null);
            if($result==false){
                $this->errMsg =_tr("Outbound Route order couldn't be updated. ").$this->errMsg;
                return false;
            }
        }
        //actualizamos la ruta seleccionada con el nuevo valor de seq
        $query="Update outbound_route set seq=? where id=?";
        $result=$this->executeQuery($query,array($new_seq,$routeId));
        if($result==false){
            $this->errMsg =_tr("Outbound Route order couldn't be updated. ").$this->errMsg;
            return false;
        }
        return true;
    }
    
    function createDialPlanOutbound(&$arrFromInt){
        if(is_null($this->code) || is_null($this->domain))
            return false;
            
        $arrExt=array();
        $arrInclude=array();
        $arrOut=$this->getOutbounds($this->domain);
        if($arrOut===false){
            $this->errMsg=_tr("Error creating dialplan for Outbound Routes. ").$this->_DB->errMsg; 
            return false;
        }else{
            foreach($arrOut as $route){
                $out_id=$route["id"];
                $context="outrt-$out_id";
                //en caso de que se haya seleccionado un time_group para la ruta
                //ahi que incluir tanta veces como dondiciones de tiempo tenga el time:group asignado
                $arrtg=$this->getTimeConditions($route["time_group_id"]);
                if($arrtg!=false){
                    foreach($arrtg as $key => $value){
                        $arrInclude[$key]["name"]=$context;
                        $arrInclude[$key]["extra"]=",$value";
                    }
                }else{
                    $arrInclude[]["name"]=$context;
                } 
                $arrPattern=$this->getPattern($out_id);
                $arrTrunk=$this->getArrTrunkPriority($out_id);
                if($arrTrunk!=false){
                    foreach($arrPattern as $value){
                        $exten=$value["exten"];
                        $arrExt[$context][]=new paloExtensions($exten,new ext_macro($this->code.'-user-callerid','SKIPTTL'),1);
                        $arrExt[$context][]=new paloExtensions($exten,new ext_noop('Calling Out Route: '.$route["routename"]));
                        if(isset($route["mohsilence"]) && $route["mohsilence"]!=""){
                            $arrExt[$context][]=new paloExtensions($exten,new ext_set("MOHCLASS", '${IF($["${MOHCLASS}"=""]?'.$route['mohsilence'].':${MOHCLASS})}'));
                        }
                        if (isset($route['outcid']) && $route['outcid']!= '') {
                            if ($route['outcid_mode'] == "on") {
                                $arrExt[$context][]=new paloExtensions($exten,new ext_execif('$["${KEEPCID}"!="TRUE" & ${LEN(${TRUNKCIDOVERRIDE})}=0]','Set','TRUNKCIDOVERRIDE='.$route['outcid']));
                            } else {
                                $arrExt[$context][]=new paloExtensions($exten,new ext_execif('$["${KEEPCID}"!="TRUE" & ${LEN(${DB(EXTUSER/'.$this->code.'/${EXTUSER}/outboundcid)})}=0 & ${LEN(${TRUNKCIDOVERRIDE})}=0]','Set','TRUNKCIDOVERRIDE='.$route['outcid']));
                            }
                        }
                        $arrExt[$context][]=new paloExtensions($exten,new ext_set("_NODEST",""));
                        $arrExt[$context][]=new paloExtensions($exten,new ext_macro($this->code.'-record-enable','${EXTUSER},OUT'));
                        $numTrunk=count($arrTrunk);
                        $i=0;
                        foreach($arrTrunk as $key => $trunk){
                            $i++;
                            $len="";
                            if($value["prefix"]!="")
                                $len=":".strlen($value["prefix"]);
                            //ARG1 TRUNK ID
                            //ARG2 DIALNUMBER
                            //ARG3 ROUTE_PASS
                            //ARG4 ON or OFF -> check for other trunk or stop
                            $continue=($i==$numTrunk)?'off':'on';
                            $arrExt[$context][]=new paloExtensions($exten,new ext_macro($this->code."-dialout-trunk","$key,".$value["prepend"].'${EXTEN'.$len.'},'.$route["routepass"].",$continue"));
                        }
                        $arrExt[$context][]=new paloExtensions($exten,new ext_macro($this->code.'-outisbusy'));
                    }
                }
            }
            
            $arrContext=array();
            //creamos los contextos de las rutas creadas
            foreach($arrExt as $key => $value){
                $context=new paloContexto($this->code,$key);
                if($context===false){
                    $context->errMsg=$key." Error: ".$context->errMsg;
                }else{
                    $context->arrExtensions=$value;
                    $arrContext[]=$context;
                }
            }
            
            //creamos el contexto que contiene todas la rutas de salida
            $context=new paloContexto($this->code,"outbound-allroutes");
            if($context===false){
                $context->errMsg=$key." Error: ".$context->errMsg;
            }else{
                $context->arrInclude=$arrInclude;
                $context->arrExtensions=array(new paloExtensions('foo',new ext_noop('bar'),"1"));
                $arrFromInt[]["name"]="outbound-allroutes";
                $arrContext[]=$context;
            }
            
            return $arrContext;
        }
    }
    
    private function getTimeConditions($tg_id){
        $arrTg=false;
        if(!preg_match("/^[0-9]$/",$tg_id))
            return false;
        $query="SELECT * from tg_parameters join time_group on id=id_tg where id_tg=? and organization_domain=?";
        $result=$this->_DB->fetchTable($query,true,array($tg_id,$this->domain));
        if($result!=false){
            foreach($result as $value){
                $arrTg[]=$value["tg_hour"].",".$value["tg_day_w"].",".$value["tg_day_m"].",".$value["tg_month"];
            }
        }
        return $arrTg;
    }
    
    private function getPattern($out_id){
        $pattern=array();
        $add=$prefix=$match_pattern="";$i=0;
        $query="SELECT * from outbound_route_dialpattern WHERE outbound_route_id=? order by seq";
        $arrPattern=$this->_DB->fetchTable($query,true,array($out_id));
        if($arrPattern!=false){
            foreach($arrPattern as $value){
                
                if((isset($value["match_pattern"]) && $value["match_pattern"]!="") || (isset($value["prefix"]) && $value["prefix"]!="")){
                    //prefix
                    if(isset($value["prefix"]) && $value["prefix"]!=""){
                        if(!preg_match("/^[0-9]+$/",$value["prefix"])){
                            $add="_";
                        }
                        $prefix=$value["prefix"];
                    }
                    //match_pattern
                    if(isset($value["match_pattern"]) && $value["match_pattern"]!=""){
                        if(!preg_match("/^[0-9]+$/",$value["match_pattern"])){
                            $add="_";
                        }
                        $match_pattern=$value["match_pattern"];
                    }else{
                        $add="_";
                        $match_pattern=".";
                    }
                    //match_cid
                    $pattern[$i]["exten"]=$add.$prefix.$match_pattern;
                    if($match_pattern!="" && $match_pattern!="."){
                        if(preg_match("/[0-9]+/",$value["match_cid"]))
                            $pattern[$i]["exten"]="/".$value["match_cid"];
                    }
                    $pattern[$i]["prefix"]=$prefix;
                    $pattern[$i]["prepend"]=isset($value["prepend"])?$value["prepend"]:"";
                    $i++;
                }
            }
        }
        
        if(count($pattern)==0){
            $pattern[0]["exten"]="_X.";
            $pattern[0]["prefix"]="";
            $pattern[0]["prepend"]="";
        }
        
        return $pattern;
    }
}
?>
