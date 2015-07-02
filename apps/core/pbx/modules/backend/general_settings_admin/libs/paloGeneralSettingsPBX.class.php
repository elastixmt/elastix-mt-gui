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
  $Id: paloSantoFeatuteCode.class.php,v 1.1 2012/07/30 rocio mera rmera@palosanto.com Exp $ */

/*
* la tabla globals_settings contiene los valores por default de las globales
  que seran usadas para crear las variables globales de
  de cada organizacion

* la tabla globals contiene los valores de la variables globales
  usadas dentro de cada organizacion
*/
global $arrConf;

include_once "libs/paloSantoACL.class.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoAsteriskConfig.class.php";
include_once "libs/extensions.class.php";
include_once "libs/misc.lib.php";

class paloGeneralPBX extends paloAsteriskDB{

    function paloGeneralPBX(&$pDB){
        parent::__construct($pDB);
    }

    function getGeneralSettings(){
        $arrProp=array();
        foreach(array("sip","iax") as $tech){
            $query="SELECT property_name,property_val,cathegory from ".$tech."_general";
            $arrRes=$this->_DB->fetchTable($query,true);
            if($arrRes===false){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }else{
                foreach($arrRes as $value){
                    if(isset($value["property_val"])){
                        $arrProp[$tech][$value["property_name"]]["value"]=$value["property_val"];
                        $arrProp[$tech][$value["property_name"]]["type"]=$value["cathegory"];
                    }
                }
            }
        }

        $query="SELECT * from voicemail_general";
        $arrRes=$this->_DB->getFirstRowQuery($query,true);
        if($arrRes===false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
            foreach($arrRes as $key => $value){
                if(isset($value)){
                    $arrProp["vm"][$key]["value"]=$value;
                    $arrProp["vm"][$key]["type"]="general";
                }
            }
        }

        $query="SELECT variable,value from globals_settings where variable=? or variable=?";
        $arrRes=$this->_DB->fetchTable($query,true,array("LANGUAGE","ALLOW_CODEC"));
        if($arrRes===false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
            foreach($arrRes as $value){
                if(isset($value)){
                    $arrProp["gen"][$value["variable"]]["value"]=$value["value"];
                    $arrProp["gen"][$value["variable"]]["type"]="general";
                }
            }
        }
        return $arrProp;
    }

    function getNatLocalConfing(){
        $localNet=array("ip"=>"","mask"=>"");
        $query="SELECT property_name,property_val,cathegory from sip_general where cathegory=?";
        $arrRes=$this->_DB->fetchTable($query,true,array("nat"));
        if($arrRes===false){
            $this->errMsg = $this->_DB->errMsg;
        }else{
            foreach($arrRes as $value){
                if(isset($value["property_val"])){
                    if(preg_match("/^localnetip_[0-9]+$/",$value["property_name"])){
                        $localNet["ip"][]=$value["property_val"];
                    }
                    if(preg_match("/^localnetmask_[0-9]+$/",$value["property_name"])){
                        $localNet["mask"][]=$value["property_val"];
                    }
                }
            }
        }
        return $localNet;
    }

    private function validateIP($ip)
    {
        $regs = NULL;
        if (!preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip, $regs))
            return false;
        for ($i = 1; $i <= 4; $i++) if ($regs[$i] > 255) return false;
        return ($regs[1] > 0);
    }

    private function validateNetwork($ip, $mask)
    {
        $regs = NULL;
        if (!$this->validateIP($ip)) return false;
        if (!$this->validateIP($mask)) return false;
        preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $mask, $regs);
        $b = FALSE;
        for ($i = 1; $i <= 4; $i++) {
            if ($regs[$i] == 255 && !$b) continue;
            if ($regs[$i] != 0 && $b) return false;
            if ($regs[$i] == 0 && $b) continue;
            $b = TRUE;
            if (!in_array($regs[$i], array(255, 254, 252, 248, 240, 224, 192, 128, 0)))
                return false;
        }
        return true;
    }

    private function validateHost($value){
        $pieces = explode(".",$value);
        foreach($pieces as $piece)
        {
            if (!preg_match('/^[a-z\d][a-z\d-]{0,62}$/i', $piece)
                || preg_match('/-$/', $piece) )
            {
                return false;
            }
        }
        return true;
    }

    function setGeneralSettings($arrProp){
        if($this->setGlobalsGeneralSettings($arrProp["gen"])){
            //los codec permitidos para estas tecnologias
            $arrProp["sip"]["allow"]=$arrProp["gen"]["ALLOW_CODEC"];
            $arrProp["iax"]["allow"]=$arrProp["gen"]["ALLOW_CODEC"];
        }else{
            return false;
        }

        if(!$this->setSipGeneralSettings($arrProp["sip"]))
            return false;
        elseif(!$this->setIaxGeneralSettings($arrProp["iax"]))
            return false;
        else
            return $this->setVMGeneralSettings($arrProp["vm"]);
    }

    private function setGlobalsGeneralSettings(&$arrProp){
        $gerror="<br/>Error: "._tr("General Settings").". ";
        //codec negotiation
        $listCodec=array();
        $validCodecs=$this->getCodecsAsterisk();
        if(isset($arrProp["audioCodec"])){
            if(is_array($arrProp["audioCodec"])){
                foreach($arrProp["audioCodec"] as $codec){
                    if(in_array($codec,$validCodecs["audio"])){
                        $listCodec[]=$codec;
                    }
                }
            }
        }
        if($listCodec==false){
            $this->errMsg=$gerror."You must select at least a valid audio Codec";
            return false;
        }
        if(isset($arrProp["videoCodec"])){
            if(is_array($arrProp["videoCodec"])){
                foreach($arrProp["videoCodec"] as $codec){
                    if(in_array($codec,$validCodecs["video"])){
                        $listCodec[]=$codec;
                    }
                }
            }
        }
        $arrProp["ALLOW_CODEC"]=implode(",",$listCodec);

        //settings that are common for sip, iax and voicemail technologies
        $query="UPDATE globals_settings set value=? where variable=?";
        foreach($arrProp as $key => $value){
            if($key!="audioCodec" && $key!="videoCodec"){
                if($this->_DB->genQuery($query,array($value,$key))==false){
                    $this->errMsg=$gerror.$this->_DB->errMsg;
                    return false;
                }
            }
        }
        return true;
    }

    private function setSipGeneralSettings($arrProp)
    {
        $etech="<br/>Error: "._tr("SIP Settings").". ";
        //actualizamos los parametros en la base
        $query="UPDATE sip_general set property_val=? where property_name=? and cathegory=?";
        foreach($arrProp as $key => $value){
            if (in_array($key, array('custom_name', 'custom_val', 'localnetip', 'localnetmask')))
                continue;
            if ($value == "" || $value == "noset"){
                $value = NULL;
            }
            if (!$this->_DB->genQuery($query,array($value,$key,"general"))){
                $this->errMsg=$etech.$this->_DB->errMsg;
                return false;
            }
        }

        //nat parameters
        if (empty($arrProp["nat_type"])) $arrProp["nat_type"] = 'noset';
        if (!in_array($arrProp["nat_type"], array('static', 'dynamic', 'public'))) {
            $this->errMsg=$etech."Invalid Field "._tr("Type Of Nat");
            return false;
        }

        //borramos los parametros de configuracion anterior de nat
        $query="DELETE from sip_general where cathegory=?";
        if (!$this->_DB->genQuery($query,array("nat"))) {
            $this->errMsg=$etech.$this->_DB->errMsg;
            return false;
        }

        $qINSERT="INSERT INTO sip_general (property_name,property_val,cathegory) values(?,?,?)";
        if ($arrProp["nat_type"]!="public") {
            /* Validar contenidos de localnetip y localnetmask. Para nat_type distinto
             * de "public", ambos elementos deben existir, ser arreglos, deben
             * ser arreglos numéricos, no asociativos, deben tener ambos el mismo
             * número de elementos, todos los elementos de localnetip deben ser
             * IPs válidas de definición de red, y el elemento localnetmask
             * correspondiente debe ser una máscara válida. */
            if (!(isset($arrProp["localnetip"]) && isset($arrProp["localnetmask"]) &&
                is_array($arrProp["localnetip"]) && is_array($arrProp["localnetmask"]) &&
                count($arrProp["localnetip"]) > 0 && count($arrProp["localnetmask"]) > 0)) {
                $this->errMsg = $etech."You must set a valid Local Network [ip/mask]";
                return false;
            } elseif (count($arrProp["localnetip"]) != count($arrProp["localnetmask"])) {
                $this->errMsg = $etech."(internal) localnetip/localnetmask count mismatch";
                return false;
            }
            for ($i = 0; $i < count($arrProp["localnetip"]); $i++) {
                if (!(isset($arrProp["localnetip"][$i]) && isset($arrProp["localnetmask"][$i]))) {
                    $this->errMsg = $etech."(internal) localnetip/localnetmask not numeric array";
                    return false;
                }
                if (!$this->validateNetwork($arrProp["localnetip"][$i], $arrProp["localnetmask"][$i])) {
                    $this->errMsg = $etech."You must set a valid Local Network [ip/mask] (".
                        $arrProp["localnetip"][$i]."/".$arrProp["localnetmask"][$i].")";
                    return false;
                }
                // FIXME: localnetip y localnetmask deben insertarse de forma contigua
                if (!$this->_DB->genQuery($qINSERT,array("localnetip_$i", $arrProp["localnetip"][$i], "nat")) ||
                    !$this->_DB->genQuery($qINSERT,array("localnetmask_$i", $arrProp["localnetmask"][$i], "nat"))){
                    $this->errMsg = $this->_DB->errMsg;
                    return false;
                }
            }

            if($arrProp["nat_type"]=="static"){
                $error=$etech."You must set a valid "._tr("Extern Addres");
                if(empty($arrProp["externaddr"])){
                    $this->errMsg=$error;
                    return false;
                }else{
                    $addr=explode(":",$arrProp["externaddr"]);
                    if(count($addr)>1){
                        if(!ctype_digit($addr[1])){
                            $this->errMsg=$error;
                            return false;
                        }
                    }
                    if(!$this->validateHost($addr[0]) && !$this->validateIP($addr[0])){
                        $this->errMsg=$error;
                        return false;
                    }else{
                        if(!$this->_DB->genQuery($qINSERT,array("externaddr",$arrProp["externaddr"],"nat"))){
                            $this->errMsg=$etech.$this->_DB->errMsg;
                            return false;
                        }
                    }
                }
            }elseif($arrProp["nat_type"]=="dynamic"){
                $error=$etech."You must set a valid "._tr("Extern Host");
                if(empty($arrProp["externhost"])){
                    $this->errMsg=$error;
                    return false;
                }elseif(!$this->validateHost($arrProp["externhost"])){
                    $this->errMsg=$error;
                    return false;
                }else{
                    if($arrProp["externrefresh"]=="" || !ctype_digit($arrProp["externrefresh"])){
                        $arrProp["externrefresh"]="120";
                    }
                    if (!$this->_DB->genQuery($qINSERT,array("externhost",$arrProp["externhost"],"nat")) ||
                        !$this->_DB->genQuery($qINSERT,array("externrefresh",$arrProp["externrefresh"],"nat"))){
                        $this->errMsg=$etech.$this->_DB->errMsg;
                        return false;
                    }
                }
            }
        }

        //custom Parameters
        return $this->setCustomValues("sip",$arrProp);
    }


    private function setIaxGeneralSettings($arrProp){
        //actualizamos los parametros en la base
        $query="UPDATE iax_general set property_val=? where property_name=? and cathegory=?";
        foreach($arrProp as $key => $value){
            if($key!="custom_name" && $key!="custom_val"){
                if($value=="" || $value=="noset"){
                    $value=NULL;
                }
                if($this->_DB->genQuery($query,array($value,$key,"general"))==false){
                    $this->errMsg="<br/>Error: "._tr("IAX Settings").". ".$this->_DB->errMsg;
                    return false;
                }
            }
        }

        //custom Parameters
        return $this->setCustomValues("iax",$arrProp);
    }

    private function setCustomValues($tech,$arrProp){
        $qINSERT="INSERT INTO $tech"."_general (property_name,property_val,cathegory) values(?,?,?)";
        if(is_array($arrProp["custom_name"])){
            $error="<br/>Error: "._tr(strtoupper($tech)." Settings").". ";
            //borramos las configuraciones personalizadas anteriores
            $query="DELETE from $tech"."_general where cathegory=?";
            if($this->_DB->genQuery($query,array("custom"))==false){
                $this->errMsg=$error.$this->_DB->errMsg;
                return false;
            }

            //obtenemos los keys almacenados, los custom parameters no pueden ser igual a los que estan almacenados
            $query="SELECT property_name FROM $tech"."_general";
            $nameProp=$this->_DB->fetchTable($query);
            if($nameProp===false){
                $this->errMsg=$error.$this->_DB->errMsg;
                return false;
            }else{
                foreach($nameProp as $item){
                    $arrKey[]=$item[0];
                }
            }

            foreach(array_keys($arrProp["custom_name"]) as $index){
                if(!empty($arrProp["custom_name"][$index]) && isset($arrProp["custom_val"][$index])){
                    if($arrProp["custom_val"][$index]!=""){
                        $name=strtolower($arrProp["custom_name"][$index]);
                        if(!in_array($name,$arrKey)){
                            if($this->_DB->genQuery($qINSERT,array($name,$arrProp["custom_val"][$index],"custom"))==false){
                                $this->errMsg=$error.$this->_DB->errMsg;
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    private function setVMGeneralSettings($arrProp){
        $arrQuery=array();
        $arrParam=array();

        foreach($arrProp as $name => $value){
            if(!empty($name)){
                if(isset($value)){
                    if($value=="" || $value=="noset"){
                        $value=NULL;
                    }
                    $arrQuery[]="$name=?";
                    $arrParam[]=$value;
                }
            }
        }
        if(count($arrQuery)>0){
            $query ="Update voicemail_general set ".implode(",",$arrQuery);
            if(!$this->_DB->genQuery($query,$arrParam)){
                $this->errMsg="<br/>Error: "._tr("Voicemail Settings").". ".$this->_DB->errMsg;
                return false;
            }
        }
        return true;
    }
}
?>
