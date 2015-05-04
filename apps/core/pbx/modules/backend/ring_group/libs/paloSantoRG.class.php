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

class paloSantoRG extends paloAsteriskDB{
    protected $code;
    protected $domain;

    function paloSantoRG(&$pDB,$domain)
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
    
    function validateDomainPBX(){
        //validamos que la instancia de paloDevice que se esta usando haya sido creda correctamente
        if(is_null($this->code) || is_null($this->domain))
            return false;
        return true;
    }
    
    function getNumRG($domain=null,$rg_number=null,$rg_name=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(id) from ring_group";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($rg_number) && $rg_number!=''){
            $expression=$this->getRegexPatternFromAsteriskPattern($rg_number);
            if($expression!=false){
                $where[]=" rg_number REGEXP ? ";
                $arrParam[]="^$expression$";
            }
        }
        if(isset($rg_name) && $rg_name!=''){
            $where[]=" UPPER(rg_name) like ?";
            $arrParam[]="%".strtoupper($rg_name)."%";
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

    
    function getRGs($domain=null,$rg_number=null,$rg_name=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;

        $query="SELECT * from ring_group";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($rg_number) && $rg_number!=''){
            $expression=$this->getRegexPatternFromAsteriskPattern($rg_number);
            if($expression!=false){
                $where[]=" rg_number REGEXP ? ";
                $arrParam[]="^$expression$";
            }
        }
        if(isset($rg_name) && $rg_name!=''){
            $where[]=" UPPER(rg_name) like ?";
            $arrParam[]="%".strtoupper($rg_name)."%";
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

    //debo devolver un arreglo que contengan los parametros del RG
    function getRGById($id){
        global $arrConf;
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = _tr("Invalid Ring Group");
            return false;
        }

        $query="SELECT * from ring_group where id=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($id,$this->domain));
        
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }elseif(count($result)>0){
            return $result;
        }else
              return false;
    }
    
    /**
        funcion que crea un nueva ruta entrante dentro del sistema
    */
    function createNewRG($arrProp){
        if(!$this->validateDomainPBX()){
            $this->errMsg=_tr("Invalid Organization");
            return false;
        }
    
        $query="INSERT INTO ring_group (";
        $arrOpt=array();
        
        $query .="organization_domain,";
        $arrOpt[count($arrOpt)]=$this->domain;

        //debe haberse seteado un nombre
        if(!isset($arrProp["rg_name"]) || $arrProp["rg_name"]==""){
            $this->errMsg=_tr("Field 'Name' can't be empty");
            return false;
        }else{
            $query .="rg_name,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_name"];
        }
        
        //el numero del ring_group no debe estar siendo usado como patron de marcado
        if(!preg_match("/^[0-9]*$/",$arrProp["rg_number"])){
            $this->errMsg=_tr("Invalid Ring Group Number");
            return false;
        }
        
        if($this->existExtension($arrProp["rg_number"],$this->domain)==true){
            return false;
        }else{
            $query .="rg_number,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_number"];
        }
        
        $extensions = $this->_createExtensionHyphenList($arrProp["rg_extensions"]);
        
        if($extensions==""){
            $this->errMsg=_tr("Field 'Extensions List' can't be empty");
            return false;
        }else{
            $query .="rg_extensions,";
            $arrOpt[] = $extensions;
        }
        
        if(isset($arrProp["rg_strategy"])){
            $query .="rg_strategy,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_strategy"];
        }

        if(isset($arrProp["rg_time"])){
            $query .="rg_time,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_time"];
        }
        
        if(isset($arrProp["rg_alertinfo"])){
            $query .="rg_alertinfo,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_alertinfo"];
        }
        
        if(isset($arrProp["rg_cid_prefix"])){
            $query .="rg_cid_prefix,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_cid_prefix"];
        }

        if(isset($arrProp["rg_moh"])){
            if($arrProp["rg_moh"]!="ring"){
                if($this->existMoHClass($arrProp["rg_moh"],$this->domain)){
                    $query .="rg_moh,";
                    $arrOpt[count($arrOpt)]=$arrProp["rg_moh"];
                    $arrProp["rg_moh"]="yes";
                }else{
                    $arrProp["rg_moh"]="ring";
                }  
            }
            $query .="rg_play_moh,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_moh"];
        }
        
        if(isset($arrProp["rg_recording"])){
            if($arrProp["rg_recording"]!="none"){
                if($this->getFileRecordings($this->domain,$arrProp["rg_recording"])==false){
                    $arrProp["rg_recording"]="none";
                }
            }
            $query .="rg_recording,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_recording"];
        }
        
        if(isset($arrProp["rg_cf_ignore"])){
            $query .="rg_cf_ignore,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_cf_ignore"];
        }
        
        if(isset($arrProp["rg_skipbusy"])){
            $query .="rg_skipbusy,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_skipbusy"];
        }
        
        if(isset($arrProp["rg_pickup"])){
            $query .="rg_pickup,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_pickup"];
        }
        
        if(isset($arrProp["rg_confirm_call"])){
            $query .="rg_confirm_call,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_confirm_call"];          
            if($arrProp["rg_confirm_call"]=="yes"){
                if($this->getFileRecordings($this->domain,$arrProp["rg_record_remote"])==false){
                    $arrProp["rg_record_remote"]="default";
                }
            
                if($this->getFileRecordings($this->domain,$arrProp["rg_record_toolate"])==false){
                    $arrProp["rg_record_toolate"]="default";
                }
                
                $query .="rg_record_remote,rg_record_toolate,";
                $arrOpt[count($arrOpt)]=$arrProp["rg_record_remote"]; 
                $arrOpt[count($arrOpt)]=$arrProp["rg_record_toolate"]; 
            }
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

    function updateRGPBX($arrProp){
        $query="UPDATE ring_group SET ";
        $arrOpt=array();

        $result=$this->getRGById($arrProp["id_rg"]);
        if($result==false){
            $this->errMsg=_tr("Ring Group doens't exist. ").$this->errMsg;
            return false;
        }
        $idRG=$result["id"];
        
        //debe haberse seteado un nombre
        if(!isset($arrProp["rg_name"]) || $arrProp["rg_name"]==""){
            $this->errMsg=_tr("Field 'Name' can't be empty");
            return false;
        }else{
            $query .="rg_name=?,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_name"];
        }
        
        //no se puede actualizar el numero del ringroup
        
        $extensions = $this->_createExtensionHyphenList($arrProp["rg_extensions"]);
        
        if($extensions==""){
            $this->errMsg=_tr("Field 'Extensions List' can't be empty");
            return false;
        }else{
            $query .="rg_extensions=?,";
            $arrOpt[] = $extensions;
        }
        
        if(isset($arrProp["rg_strategy"])){
            $query .="rg_strategy=?,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_strategy"];
        }

        if(isset($arrProp["rg_time"])){
            $query .="rg_time=?,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_time"];
        }
        
        if(isset($arrProp["rg_alertinfo"])){
            $query .="rg_alertinfo=?,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_alertinfo"];
        }
        
        if(isset($arrProp["rg_cid_prefix"])){
            $query .="rg_cid_prefix=?,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_cid_prefix"];
        }

        if(isset($arrProp["rg_moh"])){
            if($arrProp["rg_moh"]!="ring"){
                if($this->existMoHClass($arrProp["rg_moh"],$this->domain)){
                    $query .="rg_moh=?,";
                    $arrOpt[count($arrOpt)]=$arrProp["rg_moh"];
                    $arrProp["rg_moh"]="yes";
                }else{
                    $arrProp["rg_moh"]="ring";
                }  
            }
            $query .="rg_play_moh=?,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_moh"];
        }
        
        if(isset($arrProp["rg_recording"])){
            if($arrProp["rg_recording"]!="none"){
                if($this->getFileRecordings($this->domain,$arrProp["rg_recording"])==false){
                    $arrProp["rg_recording"]="none";
                }
            }
            $query .="rg_recording=?,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_recording"];
        }
        
        if(isset($arrProp["rg_cf_ignore"])){
            $query .="rg_cf_ignore=?,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_cf_ignore"];
        }
        
        if(isset($arrProp["rg_skipbusy"])){
            $query .="rg_skipbusy=?,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_skipbusy"];
        }
        
        if(isset($arrProp["rg_pickup"])){
            $query .="rg_pickup=?,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_pickup"];
        }
        
        if(isset($arrProp["rg_confirm_call"])){
            $query .="rg_confirm_call=?,";
            $arrOpt[count($arrOpt)]=$arrProp["rg_confirm_call"];          
            if($arrProp["rg_confirm_call"]=="yes"){
                if($this->getFileRecordings($this->domain,$arrProp["rg_record_remote"])==false){
                    $arrProp["rg_record_remote"]="default";
                }
            
                if($this->getFileRecordings($this->domain,$arrProp["rg_record_toolate"])==false){
                    $arrProp["rg_record_toolate"]="default";
                }
                
                $query .="rg_record_remote=?,rg_record_toolate=?,";
                $arrOpt[count($arrOpt)]=$arrProp["rg_record_remote"]; 
                $arrOpt[count($arrOpt)]=$arrProp["rg_record_toolate"]; 
            }
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
        $arrOpt[count($arrOpt)]=$idRG;
        $result=$this->executeQuery($query,$arrOpt);
        if($result==false)
            $this->errMsg=$this->errMsg;
        return $result; 
         
    }

    private function _createExtensionHyphenList($ext_text)
    {
        /* El textarea alimenta líneas con \r\n o \n. Arreglo para Elastix bug #1875. */
        $extlist = array_map('trim', explode("\n", $ext_text));
        $arr_ext = array();
        foreach ($extlist as $ext) {
            if (preg_match('/^([0-9]+)(#){0,1}$/', $ext)) $arr_ext[] = $ext;
        }
        sort($arr_ext);
        return implode('-', $arr_ext);
    }

    function deleteRG($rg_id){
        $result=$this->getRGById($rg_id);
        if($result==false){
            $this->errMsg=_tr("Ring Group doens't exist. ").$this->errMsg;
            return false;
        }
        
        $query="DELETE from ring_group where id=?";
        if($this->executeQuery($query,array($rg_id))){
            return true;
        }else{
            $this->errMsg="RG can't be deleted.".$this->errMsg;
            return false;
        } 
    }
    
    function createDialplanRG(&$arrFromInt){
        if(is_null($this->code) || is_null($this->domain))
            return false;
            
        $arrExt=array();
        $arrExt2=array();
        $arrGR=$this->getRGs($this->domain);
        if($arrGR===false){
            $this->errMsg=_tr("Error creating dialplan for ring_group. ").$this->errMsg; 
            return false;
        }else{
            foreach($arrGR as $value){
                $exten=$value["rg_number"];
                if($value["rg_extensions"]!="" && isset($value["rg_extensions"])){
                    $arrExt[]=new paloExtensions($exten, new ext_macro($this->code.'-user-callerid'),1);
                    $arrExt[]=new paloExtensions($exten, new ext_gotoif('$["foo${BLKVM_OVERRIDE}" = "foo"]', 'skipdb'));
                    $arrExt[]=new paloExtensions($exten, new ext_gotoif('$["${DB(${BLKVM_OVERRIDE})}" = "TRUE"]', 'skipov'));
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('__NODEST', ''),"n",'skipdb');
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('__BLKVM_OVERRIDE', 'BLKVM/'.$this->code.'/${EXTEN}/${CHANNEL}'));
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('__BLKVM_BASE', '${EXTEN}'));
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('DB(${BLKVM_OVERRIDE})', 'TRUE'));
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('RRNODEST', '${NODEST}'),"n",'skipov');
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('__NODEST', '${EXTEN}'),"n",'skipvmblk');
                    //ring_group cid prefix
                    $arrExt[]=new paloExtensions($exten, new ext_gotoif('$["foo${RGPREFIX}" = "foo"]', 'rgprefix'));
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('CURRENT_PREFIX', '${CALLERID(name):0:${LEN(${RGPREFIX})}'));
                    $arrExt[]=new paloExtensions($exten, new ext_gotoif('$["${RGPREFIX}" = "${CURRENT_PREFIX}"]', 'continue'));
                    $arrExt[]=new paloExtensions($exten, new ext_noop('CALLERID(name) is ${CALLERID(name)}'),"n",'rgprefix');
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('_RGPREFIX', $value["rg_cid_prefix"]));
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('CALLERID(name)','${RGPREFIX} ${CALLERID(name)}'));
                    //seteamos los parametros para la grabacion
                    $arrExt[]=new paloExtensions($exten, new ext_macro($this->code.'-record-enable',$value["rg_extensions"].',Group'),'n','continue');
                    
                    if($value["rg_alertinfo"]!=""){
                        $arrExt[]=new paloExtensions($exten, new ext_setvar('__ALERT_INFO', str_replace(';', '\;', $value["rg_alertinfo"])));
                    }
                    
                    if($value["rg_cf_ignore"]=="yes"){
                        $arrExt[]=new paloExtensions($exten, new ext_setvar('__CWIGNORE', 'TRUE'));
                    }
                    
                    if($value["rg_skipbusy"]=="yes"){
                        $arrExt[]=new paloExtensions($exten, new ext_setvar('_CFIGNORE', 'TRUE'));
                        $arrExt[]=new paloExtensions($exten, new ext_setvar('_FORWARD_CONTEXT', 'block-cf'));
                    }
                    
                    if($value["rg_pickup"]=="yes"){
                        $arrExt[]=new paloExtensions($exten, new ext_setvar('__PICKUPMARK', $this->code.'_${EXTEN}'));
                    }
                    
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('RingGroupMethod', $value["rg_strategy"]));
                    
                    if(isset($value["rg_recording"])){
                        if($value["rg_recording"]!="" && $value["rg_recording"]!="none"){
                            $file=$this->getFileRecordings($this->domain,$arrProp["rg_recording"]);
                            if($file!=false){
                                $arrExt[]=new paloExtensions($exten, new ext_gotoif('$["foo${RRNODEST}" != "foo"]','DIALGRP'));
                                $arrExt[]=new paloExtensions($exten, new ext_answer(''));
                                $arrExt[]=new paloExtensions($exten, new ext_wait(1));
                                $arrExt[]=new paloExtensions($exten, new ext_playback($file));
                            }
                        }
                    }
                    
                    $dialopts='${DIAL_OPTIONS}';
                    if($value["rg_play_moh"]=="yes"){
                        if($value["rg_moh"]!=""){
                            if($this->existMoHClass($value["rg_moh"],$this->domain)==true)
                                $dialopts="m(".$value["rg_moh"].')${DIAL_OPTIONS}';
                        }
                    }
                    
                    if($value["rg_confirm_call"]=="yes"){
                        $remote=$this->getFileRecordings($this->domain,$value["rg_record_remote"]);
                        $toolate=$this->getFileRecordings($this->domain,$value["rg_record_toolate"]);
                        $remote=($remote==false)?"":$remote;
                        $toolate=($toolate==false)?"":$toolate;
                        $len=strlen($exten)+4;
                        
                        $arrExt2[]=new paloExtensions("_RG-$exten-.", new ext_nocdr(''), 1);
                        $arrExt2[]=new paloExtensions("_RG-$exten-.", new ext_macro($this->code.'-dial',$value["rg_time"].",M(".$this->code."-confirm^$remote^$toolate^$exten)$dialopts".',${EXTEN:'.$len.'}'));
                        
                        $arrExt[]=new paloExtensions($exten, new ext_macro($this->code.'-dial-confirm',$value["rg_time"].",".$dialopts.",".$value["rg_extensions"].",".$exten),"n",'DIALGRP');
                    }else{
                        $arrExt[]=new paloExtensions($exten, new ext_macro($this->code.'-dial',$value["rg_time"].",".$dialopts.",".$value["rg_extensions"],"n",'DIALGRP'));
                    }
                    
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('RingGroupMethod', ""));
                    if($value["rg_cf_ignore"]=="yes"){
                        $arrExt[]=new paloExtensions($exten, new ext_setvar('__CWIGNORE', ''));
                    }
                    
                    if($value["rg_skipbusy"]=="yes"){
                        $arrExt[]=new paloExtensions($exten, new ext_setvar('_CFIGNORE', ''));
                        $arrExt[]=new paloExtensions($exten, new ext_setvar('_FORWARD_CONTEXT', $this->code.'-from-internal'));
                    }
                    
                    if($value["rg_pickup"]=="yes"){
                        $arrExt[]=new paloExtensions($exten, new ext_setvar('__PICKUPMARK', ''));
                    }
                    
                    $arrExt[]=new paloExtensions($exten, new ext_setvar('__NODEST', ''));
                    $arrExt[]=new paloExtensions($exten, new ext_dbdel('${BLKVM_OVERRIDE}'));
                    
                    if(isset($value["destination"])){
                        $goto=$this->getGotoDestine($this->domain,$value["destination"]);
                        if($goto==false)
                            $goto="h,1";
                    }
                    
                    $arrExt[]=new paloExtensions($exten, new extension("Goto(".$goto.")"));
                    $arrExt[]=new paloExtensions($exten, new ext_noop('SKIPPING DEST, CALL CAME FROM Q/RG: ${RRNODEST}'),"n","nodest");
                }
            }
            $arrExt[]=new paloExtensions("h", new ext_macro($this->code.'-hangupcall'),"1");
            
            $arrContext=array();
            //creamos el context ext-group
            $context=new paloContexto($this->code,"ext-group");
            if($context===false){
                $context->errMsg="ext-group. Error: ".$context->errMsg;
            }else{
                $context->arrExtensions=$arrExt;
                $arrFromInt[]["name"]="ext-group";
                $arrContext[]=$context;
            }
            
            if(count($arrExt2)>0){
                //creamos el context grps
                $context=new paloContexto($this->code,"grps");
                if($context===false){
                    $context->errMsg="grps. Error: ".$context->errMsg;
                }else{
                    $context->arrExtensions=$arrExt2;
                    $arrFromInt[]["name"]="grps";
                    $arrContext[]=$context;
                }
            }
            
            return $arrContext;
        }
    }
}
?>
