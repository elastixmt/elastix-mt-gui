<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version {ELASTIX_VERSION}                                    |
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
  $Id: paloSantoPBX.class.php,v 1.1 2012/07/30 rocio mera rmera@palosanto.com Exp $ */

global $arrConf;
include_once "{$arrConf['elxPath']}/libs/paloSantoPBX.class.php";

if (file_exists("/var/lib/asterisk/agi-bin/phpagi-asmanager.php")) {
    require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
}

class paloIM extends paloAsteriskDB{
    private $domain;
    private $code;
    
    function paloIM(&$pDB,$domain)
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
    
    //soporte para cuentas que no sean solamente numero 
    //las cuentas de chat simepre son de tipo sip
    function createIMAccount($arrProp){
        if(!isset($arrProp["id_exten"])){
            $arrProp["id_exten"]=NULL;
        }
        if(empty($arrProp["alias"])){
            $arrProp["alias"]='';
        }
        if(empty($arrProp["display_name"])){
            $arrProp["display_name"]=$arrProp['name'];
        }
        
         if($arrProp['create_device']){
            if(!$this->createSipAccount($arrProp)){
                return false;
            }
        }else{
            //en caso de que se indique que no es necesario crear el dispositivo verificamos que el mismo exista
            $pSip=new paloSip($this->_DB);
            if(!$pSip->existPeer($arrProp['device'])){
                $this->errMsg="Peer: {$arrProp['device']} does not exist";
                return false;
            }
        }
        
        $query="INSERT INTO im (device,organization_domain,display_name,alias,id_exten) VALUES (?,?,?,?,?)";
        $result=$this->_DB->genQuery($query,array($arrProp['device'],$this->domain,$arrProp['display_name'],$arrProp['alias'],$arrProp['id_exten']));
        
        if($result==false){
            $this->errMsg=_tr("DATABASE ERROR").": ".$this->_DB->errMsg;
            return false;
        }
        return true;
    }
    
    /**
     * Las cuentas de chat son de tipo sip
     * Son manejadas por el plan de marcado usando la funcion SEND_MESSAGE
     * Es necesario crear un dispisitvo sip para la cuenta devido a que asterisk no
     * soporta multipresencia con sip
     * Supuestamente esto sera resuelto en asterisk 12
     */
    private function createSipAccount($arrProp){
        $device=$arrProp['name']."_".$this->code;
        
        $pSip=new paloSip($this->_DB);
        
        //validamos que no exista un dispositvo con ese nombre
        if($pSip->existPeer($device)){
            $this->errMsg=$pSip->errMsg;
            return false;
        }
        
        $arrProp["organization_domain"]=$this->domain;
        $arrProp['callerid']="device <".$arrProp['name'].">";
        $arrProp["outofcall_message_context"] = empty($arrProp["outofcall_message_context"])?'im-sip':$arrProp["outofcall_message_context"];
        $arrProp["context"] = empty($arrProp["context"])?'default':$arrProp["context"]; 
        $arrProp["transport"] = "ws,wss,udp";
        $arrProp["dial"] = "SIP/$device";
        //para poder monitorear la presencia del dispositivo
        $arrProp["subscribecontext"] = empty($arrProp["subscribecontext"])?'im-sip':$arrProp["subscribecontext"];
        
        //creamos el dispositivo
        $pSip->setGroupProp($arrProp,$this->domain);
        if($pSip->insertDB()==false){
            $this->errMsg="Error setting parameter to peer $device. ".$pSip->errMsg;
            return false;
        }
        return true;
    }
    
    function updateIMAccount($arrProp){
        $device=$arrProp['name'];
        $arrProp['display_name']=empty($arrProp['display_name'])?$device:$arrProp['display_name'];
        $arrProp['alias']=empty($arrProp['alias'])?NULL:$arrProp['alias'];
        $query="UPDATE im SET display_name=?,alias=? WHERE organization_domain=? and device=?";
        $result=$this->_DB->genQuery($query,array($arrProp['display_name'],$arrProp['alias'],$this->domain,$device));
        
        if($result==false){
            $this->errMsg=_tr("DATABASE ERROR");
            return false;
        }
        
        if($arrProp['update_device']){
            if(!$this->updateSipAccount($arrProp)){
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }
    }
    
    private function updateSipAccount($arrProp){
        $pSip=new paloSip($this->_DB);
        
        //validamos que exista un dispositvo con ese nombre
        if(!$pSip->existPeer($arrProp['name'])){
            $this->errMsg="Peer: {$arrProp['name']}.".$this->errMsg;
            return false;
        }
        
        $arrProp["organization_domain"] = $this->domain;
        $arrProp["outofcall_message_context"] = empty($arrProp["outofcall_message_context"])?'im-sip':$arrProp["outofcall_message_context"];
        $arrProp["context"] = empty($arrProp["context"])?'default':$arrProp["context"]; 
        $arrProp["transport"] = "ws,wss,udp";
        //para poder monitorear la presencia del dispositivo
        $arrProp["subscribecontext"] = empty($arrProp["subscribecontext"])?'im-sip':$arrProp["subscribecontext"];
        
        
        //actualizamos el dispositvo
        if($pSip->updateParameters($arrProp)==false){
            $this->errMsg="Error setting parameter to peer {$arrProp['name']}. ".$pSip->errMsg;
            return false;
        }
        return true;
    }
        
    function createDialPlanIM(){
        $query="SELECT elxweb_device,device,alias FROM extension WHERE organization_domain=? and enable_chat='yes' and tech='sip'";
        $result=$this->_DB->fetchTable($query,true,array($this->domain));
        if($result==false){
            $this->errMsg=_tr("IM Database Error");
            return false;
        }
        
        //coje todos aquellos mensajes para los que no existe una entrada
        $arrExtenIM[]=new paloExtensions("_[1-9a-zA-Z].",new ext_hangup(),1);
        foreach($result as $value){
            $devices=array();
            if(!empty($value['elxweb_device']))
                $devices[]=$value['elxweb_device'];
            if(!empty($value['device']))
                $devices[]=$value['device'];
            $strdev=implode('&',$devices);
            if(!empty($value['elxweb_device'])){
                $arrExtenIM[] = new paloExtensions($value['elxweb_device'],new ext_gosub(1,'im','im-sip',$this->code.",$strdev"),1);
                $arrExtenIM[] = new paloExtensions($value['elxweb_device'],new ext_hangup());
                if(!empty($value['alias'])){
                    $arrExtenIM[] = new paloExtensions($value['alias'],new ext_goto(1,$value['elxweb_device']),1);
                }
            }
        }
        
        $contextoIM=new paloContexto($this->code,"im-sip");
        if($contextoIM===false){
            $contextoIM->errMsg="im-sip. Error: ".$contextoIM->errMsg;
        }else
            $contextoIM->arrExtensions=$arrExtenIM;
            
        $arrContext=array($contextoIM);
        return $arrContext; 
    }
}