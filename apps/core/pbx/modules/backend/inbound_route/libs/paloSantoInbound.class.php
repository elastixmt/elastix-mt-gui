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
	
class paloSantoInbound extends paloAsteriskDB{
    protected $code;
    protected $domain;

    function paloSantoInbound(&$pDB,$domain)
    {
       parent::__construct($pDB);
        
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloQueuePBX").$this->errMsg;
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

    function getNumInbound($domain=null,$name=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(id) from inbound_route";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($name) && $name!=''){
            $where[]=" UPPER(description) like ?";
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


    function getInbounds($domain=null,$name=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;
        
        $query="SELECT * from inbound_route";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($name) && $name!=''){
            $where[]=" description like ?";
            $arrParam[]="%$name%";
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

    //debo devolver un arreglo que contengan los parametros del Inbound
    function getInboundById($id){
        global $arrConf;
        $arrInbound=array();
        $where="";
        
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = "Invalid Inbound ID";
            return false;
        }
        $param=array($id);
        
        if(empty($this->domain)){
            $this->errMsg = _tr("Invalid Organization");
            return false;
        }
        $where=" and organization_domain=?";
        $param[]=$this->domain;

        $query="SELECT * from inbound_route where id=? $where";
        $result=$this->_DB->getFirstRowQuery($query,true,$param);
        
        if($result===false){
            $this->errMsg=_tr("DATABASE ERROR");
            return false;
        }else{
            return $result;
        }
    }

    private function isRepeatDidCid($did,$cid){
        if(isset($cid)){
            if(!preg_match("/^[0-9_XZN\-\[\]\.#]*$/",$cid)){
                $this->errMsg=_tr("Invalid Caller ID number");
                return true;
            }
        }else
            $cid="";
        
        if(!isset($did))
            $did="";
        
        $param[]=$cid;
        $param[]=$did;
        $where=" and organization_domain=?";
        $param[]=$this->domain;
        
        $query="SELECT description from inbound_route where cid_number=? and did_number=? $where";
        $result=$this->_DB->getFirstRowQuery($query,true,$param);
        if($result===false || count($result)!=0){
            $this->errMsg=$this->_DB->errMsg;
            return true;
        }else
            return false;
    }

    /**
        funcion que crea un nueva ruta entrante dentro del sistema
    */
    function createNewInbound($arrProp){
        $query="INSERT INTO inbound_route (";
        $arrOpt=array();
        
        if(empty($this->domain)){
            $this->errMsg = _tr("Invalid Organization");
            return false;
        }
        $query .="organization_domain,";
        $arrOpt[0]=$this->domain;

        //debe haberse seteado un nombre
        if(!isset($arrProp["description"]) || $arrProp["description"]==""){
            $this->errMsg="Description of inbound can't be empty";
            return false;
        }else{
            $query .="description,";
            $arrOpt[count($arrOpt)]=$arrProp["description"];
        }
        
        //la comdinacion de DID y CID debe se unica
        if(isset($arrProp["did_number"])){
            $query .="did_number,";
            $arrOpt[count($arrOpt)]=$arrProp["did_number"];
        }

        if(isset($arrProp["cid_number"])){
            if(!preg_match("/^[0-9_XZN\-\[\]\.#]*$/",$arrProp["cid_number"])){
                $this->errMsg=_tr("Invalid Caller ID number");
                return false;
            }
            $query .="cid_number,";
            $arrOpt[count($arrOpt)]=$arrProp["cid_number"];
        }
        
        if($this->isRepeatDidCid($arrProp["did_number"],$arrProp["cid_number"])==true){
            $this->errMsg=_tr("Already exist other inbound route with the same DID number and Caller ID number").$this->errMsg;
            return false;
        }
        
        if(isset($arrProp["fax_detect"])){
            $query .="fax_detect,";
            $arrOpt[count($arrOpt)]=$arrProp["fax_detect"];
            if($arrProp["fax_detect"]=="yes"){
                if(isset($arrProp["fax_destiny"]) && $arrProp["fax_destiny"]!=""){
                    $query .="fax_destiny,";
                    $arrOpt[count($arrOpt)]=$arrProp["fax_destiny"];
                }else{
                    $this->errMsg=_tr("You must select a fax extension");
                    return false;
                }
                
                if(isset($arrProp["fax_type"])){
                    $query .="fax_type,";
                    $arrOpt[count($arrOpt)]=$arrProp["fax_type"];
                }
                if(isset($arrProp["fax_time"])){
                    $query .="fax_time,";
                    $arrOpt[count($arrOpt)]=$arrProp["fax_time"];
                }
            }
        }

        if(isset($arrProp["alertinfo"])){
            $query .="alertinfo,";
            $arrOpt[count($arrOpt)]=$arrProp["alertinfo"];
        }

        if(isset($arrProp["cid_prefix"])){
            $query .="cid_prefix,";
            $arrOpt[count($arrOpt)]=$arrProp["cid_prefix"];
        }
        
        if(isset($arrProp["moh"])){
            $query .="moh,";
            $arrOpt[count($arrOpt)]=$arrProp["moh"];
        }

        if(isset($arrProp["ringing"])){
            $query .="ringing,";
            $arrOpt[count($arrOpt)]=$arrProp["ringing"];
        }

        if(isset($arrProp["delay_answer"])){
            $query .="delay_answer,";
            $arrOpt[count($arrOpt)]=$arrProp["delay_answer"];
        }

        if(isset($arrProp["primanager"])){
            $query .="primanager,";
            $arrOpt[count($arrOpt)]=$arrProp["primanager"];
            if($arrProp["primanager"]=="yes"){
                if(isset($arrProp["max_attempt"])){
                    $query .="max_attempt,";
                    $arrOpt[count($arrOpt)]=$arrProp["max_attempt"];
                }

                if(isset($arrProp["min_length"])){
                    $query .="min_length,";
                    $arrOpt[count($arrOpt)]=$arrProp["min_length"];
                }
            }
        }

        if(isset($arrProp["language"])){
            $query .="language,";
            $arrOpt[count($arrOpt)]=$arrProp["language"];
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

    function updateInboundPBX($arrProp){
        $query="UPDATE inbound_route SET ";
        $arrOpt=array();
        
        //verificamos que la ruta exista
        $idInbound=$arrProp["id_inbound"];
        $arrIn=$this->getInboundById($idInbound);
        if($arrIn==false){
            $this->errMsg .=_tr("Inbound Route doesn't exist");
            return false;
        }
        
        //que los nuevos did y cid que se quieren usar no esten ya siendo usado
        if(isset($arrProp["did_number"])){
            $query .="did_number=?,";
            $arrOpt[0]=$arrProp["did_number"];
        }else
            $arrProp["did_number"]="";
            
        if(isset($arrProp["cid_number"])){
            if(!preg_match("/^[0-9_XZN\-\[\]\.#]*$/",$arrProp["cid_number"])){
                $this->errMsg=_tr("Invalid Caller ID number");
                return false;
            }
            $query .="cid_number=?,";
            $arrOpt[count($arrOpt)]=$arrProp["cid_number"];
        }else
            $arrProp["cid_number"]="";
        
        $q="Select id from inbound_route where id!=? and cid_number=? and did_number=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($q,true,array($idInbound,$arrProp["cid_number"],$arrProp["did_number"],$this->domain));
        if($result===false || count($result)!=0){
            $this->errMsg=_("Already exist other inbound route with the same DID number and Caller ID number").$this->_DB->errMsg;
            return false;
        }

        if(!isset($arrProp["description"]) || $arrProp["description"]==""){
            $this->errMsg="Name of inbound can't be empty";
            return false;
        }else{
            $query .="description=?,";
            $arrOpt[count($arrOpt)]=$arrProp["description"];
        }
        
        if(isset($arrProp["fax_detect"])){
            $query .="fax_detect=?,";
            $arrOpt[count($arrOpt)]=$arrProp["fax_detect"];
            if($arrProp["fax_detect"]=="yes"){
                if(isset($arrProp["fax_destiny"]) && $arrProp["fax_destiny"]!=""){
                    $query .="fax_destiny=?,";
                    $arrOpt[count($arrOpt)]=$arrProp["fax_destiny"];
                }else{
                    $this->errMsg=_tr("You must select a fax extension");
                    return false;
                }
                
                if(isset($arrProp["fax_type"])){
                    $query .="fax_type=?,";
                    $arrOpt[count($arrOpt)]=$arrProp["fax_type"];
                }
                if(isset($arrProp["fax_time"])){
                    $query .="fax_time=?,";
                    $arrOpt[count($arrOpt)]=$arrProp["fax_time"];
                }
            }
        }
        
        //si se define un password
        if(isset($arrProp["alertinfo"])){
            $query .="alertinfo=?,";
            $arrOpt[count($arrOpt)]=$arrProp["alertinfo"];
        }

        if(isset($arrProp["cid_prefix"])){
            $query .="cid_prefix=?,";
            $arrOpt[count($arrOpt)]=$arrProp["cid_prefix"];
        }

        if(isset($arrProp["moh"])){
            $query .="moh=?,";
            $arrOpt[count($arrOpt)]=$arrProp["moh"];
        }

        if(isset($arrProp["delay_answer"])){
            $query .="delay_answer=?,";
            $arrOpt[count($arrOpt)]=$arrProp["delay_answer"];
        }

        if(isset($arrProp["primanager"])){
            $query .="primanager=?,";
            $arrOpt[count($arrOpt)]=$arrProp["primanager"];
            if($arrProp["primanager"]=="yes"){
                if(isset($arrProp["max_attempt"])){
                    $query .="max_attempt=?,";
                    $arrOpt[count($arrOpt)]=$arrProp["max_attempt"];
                }

                if(isset($arrProp["min_length"])){
                    $query .="min_length=?,";
                    $arrOpt[count($arrOpt)]=$arrProp["min_length"];
                }
            }
        }
        
        if(isset($arrProp["language"])){
            $query .="language=?,";
            $arrOpt[count($arrOpt)]=$arrProp["language"];
        }

        if(isset($arrProp["destination"])){
            if($this->validateDestine($this->domain,$arrProp["destination"])!=false){
                $query .="destination=?,goto=?,";
                $arrOpt[count($arrOpt)]=$arrProp["destination"];
                $tmp=explode(",",$arrProp["destination"]);
                $arrOpt[count($arrOpt)]=$tmp[0];
            }else{
                $this->errMsg="Invalid destination";
                return false;
            }
        }
        
        if(isset($arrProp["ringing"])){
            $query .="ringing=?";
            $arrOpt[count($arrOpt)]=$arrProp["ringing"];
        }

        //caller id options                
        $query = $query." WHERE id=?";
        $arrOpt[count($arrOpt)]=$idInbound;
        
        $result=$this->executeQuery($query,$arrOpt);
        if($result==false)
            $this->errMsg=$this->errMsg;
        return $result; 
            
    }


    function deleteInbound($inboundId){
        $arrIn=$this->getInboundById($inboundId);
        if($arrIn==false){
            $this->errMsg .=_tr("Inbound Route doesn't exist");
            return false;
        }
        
        $query="DELETE from inbound_route where id=?";
        if($this->executeQuery($query,array($inboundId))){
            return true;
        }else{
            $this->errMsg="Inbound can't be deleted.".$this->errMsg;
            return false;
        } 
    }

    function createDialplanIndbound(&$arrFromInt){
        if(is_null($this->code) || is_null($this->domain))
            return false;
            
        $arrExt=array();
        $arrIn=$this->getInbounds($this->domain);
        if($arrIn===false){
            $this->errMsg=_tr("Error creating dialplan for inbound routes. ").$this->_DB->errMsg; 
            return false;
        }else{
            foreach($arrIn as $value){
                $exten=$value["did_number"];
                $cid=$value["cid_number"];
                
                //debe tener un destino final la ruta y este debe ser valido
                if(isset($value["destination"])){
                    $goto=$this->getGotoDestine($this->domain,$value["destination"]);
                    if($goto==false)
                        continue;
                }else
                    continue;
                
                $cidroute = false;
                if($cid!="" &&  $exten==""){
                    $exten="_.";
                    $context="1";
                    $cidroute = true;
                }elseif(($cid != '' && $exten != '') || ($cid == '' && $exten == '')){
                    $context="1";
                }else{
                    $context="2";
                }
                                
                $exten = (($exten == "")?"s":$exten);
                $exten=($cid=="")?$exten:$exten."/".$cid;
                
                if ($cidroute) {
                        $arrExt[$context][]=new paloExtensions($exten, new ext_setvar('__FROM_DID','${EXTEN}'),"1");
                        $arrExt[$context][]=new paloExtensions($exten, new ext_goto('1','s'));
                        $exten = "s/$cid";
                        $arrExt[$context][]=new paloExtensions($exten, new ext_execif('$["${FROM_DID}" = ""]','Set','__FROM_DID=${EXTEN}'));
                } else {
                    $arrExt[$context][]=new paloExtensions($exten, new ext_setvar('__FROM_DID','${EXTEN}'),"1");
                }
                
                // always set callerID name
                $arrExt[$context][]=new paloExtensions($exten, new ext_execif('$[ "${CALLERID(name)}" = "" ] ','Set','CALLERID(name)=${CALLERID(num)}'));

                if (!empty($value['moh']) && trim($value['moh']) != 'default') {
                    $arrExt[$context][]=new paloExtensions($exten, new ext_setmusiconhold($value['moh']));
                    $arrExt[$context][]=new paloExtensions($exten, new ext_setvar('__MOHCLASS',$value['moh']));
                }
                
                // If we require RINGING, signal it as soon as we enter.
                if ($value['ringing'] === "on") {
                    $arrExt[$context][]=new paloExtensions($exten, new ext_ringing(''));
                }
                if ($value['delay_answer']) {
                    $arrExt[$context][]=new paloExtensions($exten, new ext_wait($value['delay_answer']));
                }
                
                if ($value['primanager'] == "1") {
                    $arrExt[$context][]=new paloExtensions($exten, new ext_macro($this->code.'-privacy-mgr',$value['max_attempt'].','.$value['min_length']));
                } else {
                    // if privacymanager is used, this is not necessary as it will not let blocked/anonymous calls through
                    // otherwise, we need to save the caller presence to set it properly if we forward the call back out the pbx
                    // note - the indirect table could go away as of 1.4.20 where it is fixed so that SetCallerPres can take
                    // the raw format.
                    //
                    $arrExt[$context][]=new paloExtensions($exten, new ext_setvar('__CALLINGPRES_SV','${CALLERPRES()}'));
                    $arrExt[$context][]=new paloExtensions($exten, new ext_setcallerpres('allowed_not_screened'));
                }
                
                if (!empty($value['alertinfo'])) {
                    $arrExt[$context][]=new paloExtensions($exten, new ext_setvar("__ALERT_INFO", str_replace(';', '\;', $value['alertinfo'])));
                }
                
                if (!empty($value['cid_prefix'])) {
                    $arrExt[$context][]=new paloExtensions($exten, new ext_setvar('_RGPREFIX', $value['cid_prefix']));
                    $arrExt[$context][]=new paloExtensions($exten, new ext_setvar('CALLERID(name)','${RGPREFIX}${CALLERID(name)}'));
                }
                
                //en caso de que se haya activado la funcion de detectar fax en la ruta escribimos el plan de marcado correspondiente
                if($value['fax_detect']=="yes"){
                    if(isset($value['fax_destiny'])){
                        if($value['fax_destiny']!=""){
                            if($value['fax_destiny']=="any")
                                $value['fax_destiny']="fax";
                            $arrExt[$context][]=new paloExtensions($exten, new ext_setvar('FAX_EXTEN',$value['fax_destiny']));
                            $arrExt[$context][]=new paloExtensions($exten, new ext_answer());
                            $faxDetect=new ext_wait($value['fax_time']);
                            if($value['fax_type']=='nvfax'){
                                //comprobamos que este instalado el modulo de deteccion de faxs
                                if(array_key_exists('nvfax',$this->getDetectFax()))
                                    $faxDetect=new extension("NVFaxDetect(".$value['fax_time'].")");
                            }
                            $arrExt[$context][]=new paloExtensions($exten, $faxDetect);
                        }
                    }
                }
                
                $arrExt[$context][]=new paloExtensions($exten, new extension("Goto(".$goto.")"));
            }
            
            $arrContext=array();
            //creamos el contexto "ext-did-0001" y "ext-did-0002"
            foreach($arrExt as $key => $value){
                $context=new paloContexto($this->code,"ext-did-000".$key);
                if($context===false){
                    $context->errMsg="ext-did-000".$key." Error: ".$context->errMsg;
                }else{
                    $value[]=new paloExtensions("fax", new ext_goto(1,'${FAX_EXTEN}',$this->code."-ext-fax"),"1");
                    $context->arrExtensions=$value;
                    $arrContext[]=$context;
                }
            }
            
            //creamos el context ext-did
            $context=new paloContexto($this->code,"ext-did");
            if($context===false){
                $context->errMsg="ext-did. Error: ".$context->errMsg;
            }else{
                $context->arrExtensions=array(new paloExtensions('foo',new ext_noop('bar'),"1"));
                $arrInclude[]=array("name"=>"ext-did-0001");
                $arrInclude[]=array("name"=>"ext-did-0002");
                $context->arrInclude=$arrInclude;
                $arrContext[]=$context;
            }
            return $arrContext;
        }
    }
    
    function getFaxExtesion(){
        $fax=array();
        $query="SELECT exten,clid_name from fax where organization_domain=?";
        $result=$this->_DB->fetchTable($query,true,array($this->domain));
        if($result!=false){
            $fax["any"]="any fax exten";
            foreach($result as $value){
                $fax[$value["exten"]]=$value["exten"]." (".$value["clid_name"].")";
            }
        }
        return $fax;
    }
    
    function getDetectFax(){
        $arrDetect=array("fax"=>"use extension 'fax'");
        $loaded=$this->isAsteriskModInstaled('app_nv_faxdetect');
        if($loaded==true)
            $arrDetect["nvfax"]="NVFaxDetect";
        return $arrDetect;
    }
}
?>
