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
  | Some functions within this class or script that implements an	     | 	
  | asterisk dialplan are based in FreePBX code.			             |
  | FreePBX® is a Registered Trademark of Schmooze Com, Inc.   		     |
  | http://www.freepbx.org - http://www.schmoozecom.com 		         |
  +----------------------------------------------------------------------+
  $Id: paloSantoFeatuteCode.class.php,v 1.1 2012/07/30 rocio mera rmera@palosanto.com Exp $ */


include_once "libs/misc.lib.php";
include_once "libs/paloSantoACL.class.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoAsteriskConfig.class.php";
include_once "libs/extensions.class.php";


class paloFeatureCode {
    public $code;
    public $default_code;
    public $name;
    public $description;
    public $estado;
    public $errMsg;

    function paloFeatureCode($name, $default_code, $description="",$estado="enabled",$code=null){
        if(!isset($name) || $name==""){
            $this->errMsg=_tr("Invalid name of feature Code");
            return false;
        }else
            $this->name=$name;

        $this->setDefaultCode($default_code);
        if($this->default_code===false){
            $this->errMsg=_tr("Invalid default code");
            return false;
        }

        $this->setDescription($description);
        $this->setCode($code);
        $this->setEstado($estado);
    }

    function setDefaultCode($default_code){
        if(!isset($default_code) || $default_code==""){
            $this->default_code=false;
        }else{
            if($this->validateCode($default_code))
                $this->default_code=$default_code;
            else
                $this->default_code=false;
        }
    }

    function setCode($code){
        if(isset($code)){
            if($this->validateCode($code))
                $this->code=$code;
            else
                $this->code=null;
        }else{
            $this->code=null;
        }
    }

    function setEstado($estado){
        if($estado=="disabled"){
            $this->estado="disabled";
        }else
            $this->estado="enabled";
    }

    function setDescription($description){
        if(isset($description)){
            if($description=="")
                $this->description=$this->name;
            else
                $this->description=$description;
        }
    }

    function validateCode($code){
        if(!preg_match("/^[\d\#\*]+$/",$code))
            return false;
        else
            return true;
    }

    function getCurrentCode(){
        if(isset($this->code))
            return $this->code;
        else
            return $this->default_code;
    }
}

class paloFeatureCodePBX extends paloAsteriskDB{
    public $arrFeatureCode;
    protected $code;
    protected $domain;
    public $errMsg;

    function paloFeatureCodePBX(&$pDB,$domain){
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            parent::__construct($pDB);

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloFeatureCodePBX").$this->errMsg;
            }else{
                $this->code=$result["code"];
            }
        }
    }

    function validateFeatureCodePBX(){
        //validamos que la instancia de paloDevice que se esta usando haya sido creda correctamente
        if(is_null($this->code) || is_null($this->domain))
            return false;
        return true;
    }

    //esto solo se  hace al momento de creacion de una nueva organizacion dentro de elastix
    //funcion solo deberia ser llamada por el usuario superadmin que es el unico capaz de realizar
    //estas acciones
    function insertPaloFeatureDB(){
        $arrFeatures=$this->getAllFeaturesCodeSettings();
        foreach($arrFeatures as $value){
            $this->arrFeatureCode[] = new paloFeatureCode($value["name"], $value["default_code"], null, $value["estado"]);
        }
        $query="INSERT into features_code (name,code,estado,organization_domain) values(?,?,?,?)";
        foreach($this->arrFeatureCode as $feature){
            if($feature!=false){
                $result=$this->executeQuery($query,array($feature->name,$feature->code,$feature->estado,$this->domain));
                if($result==false)
                    return false;
            }
        }
        return true;
    }

    //
    function editPaloFeatureDB($arrFeatures){
        $existe=false;
        if(!$this->validateFeatureCodePBX())
            return false;

        foreach($arrFeatures as $value){
            $this->arrFeatureCode[] = new paloFeatureCode($value["name"], $value["default_code"], null, $value["estado"], $value["code"]);
        }

        $query="UPDATE features_code SET estado=?, code=? WHERE name=? and organization_domain=?";
        foreach($this->arrFeatureCode as $feature){
            if($feature!=false){
                //validamos si se seteo un nuevo codigo para el feature, este no este siendo usado en
                //el sistema
                if(isset($feature->code)){
                    $arrFC=$this->getFeaturesCode($this->domain,$feature->name);
                    if($arrFC["code"]!=$feature->code)
                        $existe=$this->existExtension($feature->code,$this->domain);
                }
                if($existe==false){
                    $result=$this->executeQuery($query,array($feature->estado,$feature->code,$feature->name,$this->domain));
                    if($result==false)
                        return false;
                }else{
                    $this->errMsg=_tr("Error setting feature ").$feature->name.". ".$this->errMsg;
                    return false;
                }
            }else{
                $this->errMsg=_tr("Error setting feature ").$feature->name.". ".$feature->errMsg;
                return false;
            }
                
        }
        return true;
    }

    /**
        setea la propiedad arrFeatureCode
        esta propiedad es un arreglo de featureCodes que contiene el conjunto de features que estan habilitados
        @param $arrFeature array() , contiene el nombre de los fueatures que queremos obtener
    */
    function setArrFCbyCategory($arrFeature){
        foreach($arrFeature as $value){
            $query="SELECT f.code,fg.default_code from features_code f join features_code_settings fg on f.name=fg.name
            where f.name=? and f.estado=? and f.organization_domain=?";
            $result=$this->_DB->getFirstRowQuery($query,true,array($value,"enabled",$this->domain));
            if($result==false){
                $this->errMsg .=$this->_DB->errMsg;
            }else{
                $this->arrFeatureCode[] = new paloFeatureCode($value, $result["default_code"], null, "", $result["code"]);
            }
        }
    }

    function createDialPlanFeaturesCode(&$arrFromInt){
        if(!$this->validateFeatureCodePBX())
            return false;
        $pConfig = new paloConfig("/var/www/elastixdir/asteriskconf", "elastix_pbx.conf", "=", "[[:space:]]*=[[:space:]]*");
        $confAsterisk = $pConfig->leer_configuracion(false);

        $arrContexts=array();
        $arrContexts=array_merge($arrContexts,$this->createDialPlanFuntionBlacklist($arrFromInt));
        $arrContexts=array_merge($arrContexts,$this->createDialPlanFuntionCF($arrFromInt));
        $arrContexts=array_merge($arrContexts,$this->createDialPlanFuntionCW($arrFromInt));
        $arrContexts=array_merge($arrContexts,$this->createDialPlanFuntionDICT($arrFromInt));
        $arrContexts=array_merge($arrContexts,$this->createDialPlanFuntionDND($arrFromInt));
        $arrContexts=array_merge($arrContexts,$this->createDialPlanFuntionInfo($arrFromInt));
        $arrContexts=array_merge($arrContexts,$this->createDialPlanFuntionSpeedDial($arrFromInt));
        $arrContexts=array_merge($arrContexts,$this->createDialPlanFuntionVM($arrFromInt));
        $arrContexts=array_merge($arrContexts,$this->createDialPlanFuntionCore($arrFromInt));
        return $arrContexts;
    }

    private function createDialPlanFuntionBlacklist(&$arrFromInt){
        $arrContext=array();
        //se comprueba si se desean bloquear los numeros con callerid como unnowkn o unvailable
        $astMang=AsteriskManagerConnect($errorMng);
        if($astMang==false){
            $this->errMsg=$errorMng;
            $blck=="0";
        }else{
            $blck=$astMang->database_get("blacklist","blocked");
            $astMang->disconnect();
        }
            
        //se crea el contexto app-black-list-check
        //en este contexto se verifica si un numero se encuentra dentro de la lista de blacklist
        if($blck=="1"){
            $arrBLCheck[] = new paloExtensions("s",new ext_gotoIf('$["${CALLERID(number)}" = "Unknown"]',"check-blocked"),"1");
            $arrBLCheck[] = new paloExtensions("s",new ext_gotoIf('$["${CALLERID(number)}" = "Unavailable"]',"check-blocked"));
            $arrBLCheck[] = new paloExtensions("s",new ext_gotoIf('$["foo${CALLERID(number)}" = "foo"',"check-blocked","check"));
            $arrBLCheck[] = new paloExtensions("s",new ext_gotoIf('$["${DB(blacklist/'.$this->code.'/blocked)}" = "1"]',"blacklisted"),"n","check-blocked");
            $arrBLCheck[] = new paloExtensions("s",new ext_gotoIf('$["${BLACKLIST()}"="1"]',"blacklisted"),"n","check");
        }else
            $arrBLCheck[] = new paloExtensions("s",new ext_gotoIf('$["${BLACKLIST()}"="1"]',"blacklisted"),1,"check");
        $arrBLCheck[] = new paloExtensions("s",new ext_setvar("CALLED_BLACKLIST","1"));
        $arrBLCheck[] = new paloExtensions("s",new ext_return());
        $arrBLCheck[] = new paloExtensions("s",new ext_answer(),"n",'blacklisted');
        $arrBLCheck[] = new paloExtensions("s",new ext_wait("1"));
        $arrBLCheck[] = new paloExtensions("s",new ext_zapateller());
        $arrBLCheck[] = new paloExtensions("s",new ext_playback("ss-noservice"));
        $arrBLCheck[] = new paloExtensions("s",new ext_hangup());
        
        $contextoBLCheck=new paloContexto($this->code,"app-blacklist-check");
        if($contextoBLCheck===false){
            $contextoBLCheck->errMsg="app-blacklist-check. Error: ".$contextoBLCheck->errMsg;
        }else{
            $contextoBLCheck->arrExtensions=$arrBLCheck;
            $arrFromInt[]["name"]="app-blacklist-check";
        }

        $arrContext[]=$contextoBLCheck;

        $arrFeature=array("blacklist_num","blacklist_lcall","blacklist_rm");
        unset($this->arrFeatureCode);
        $this->setArrFCbyCategory($arrFeature);

        $contextoBLapp=new paloContexto($this->code,"app-blacklist");
        $arrFromInt[]["name"]="app-blacklist";

        if(is_array($this->arrFeatureCode)){
            foreach($this->arrFeatureCode as $value){
                if($value!=false){
                    //funcion que me devuelve un arreglo con los contexto creados
                    $fname="dialPlanBlacklist_".$value->name;
                    $contexts=$this->$fname($contextoBLapp->arrExtensions,$value->getCurrentCode());
                    $arrContext=array_merge($arrContext,$contexts);
                }
            }
        }
        $arrContext[]=$contextoBLapp;
        return $arrContext;
    }

    private function dialPlanBlacklist_blacklist_num(&$arrExt,$code_feature){
        $arrBLadd=array();
        $arrBLaddIn=array();
        $arrBLadd[]=new paloExtensions("s",new ext_answer(),"1");
        $arrBLadd[]=new paloExtensions("s",new ext_wait("1"));
        $arrBLadd[]=new paloExtensions("s",new ext_set("NumLoops","0"));
        $arrBLadd[]=new paloExtensions("s",new ext_playback("enter-num-blacklist"),"n","start");
        $arrBLadd[]=new paloExtensions("s",new ext_set("TIMEOUT(digit)","5"));
        $arrBLadd[]=new paloExtensions("s",new ext_set("TIMEOUT(response)","60"));
        $arrBLadd[]=new paloExtensions("s",new ext_read("blacknr","then-press-pound"));
        $arrBLadd[]=new paloExtensions("s",new ext_saydigits('${blacknr}'));
        $arrBLadd[]=new paloExtensions("s",new ext_playback("if-correct-press&digits/1"));
        $arrBLadd[]=new paloExtensions("s",new ext_noop("Waiting for input"));
        $arrBLadd[]=new paloExtensions("s",new ext_waitexten("60"),"n","end");
        $arrBLadd[]=new paloExtensions("s",new ext_playback("sorry-youre-having-problems&goodbye"));
        $arrBLadd[]=new paloExtensions("1",new ext_gotoIf('($[ "${blacknr}" != ""]',$this->code."-app-blacklist-add-invalid,s,1"),"1");
        $arrBLadd[]=new paloExtensions("1",new ext_set('DB(blacklist/'.$this->code.'/${blacknr})',"1"));
        $arrBLadd[]=new paloExtensions("1",new ext_playback("num-was-successfully&added"));
        $arrBLadd[]=new paloExtensions("1",new ext_wait("1"));
        $arrBLadd[]=new paloExtensions("1",new ext_hangup());

        $contextoBLAdd=new paloContexto($this->code,"app-blacklist-add");
        if($contextoBLAdd===false){
            $contextoBLAdd->errMsg="app-blacklist-add. Error: ".$contextoBLAdd;
        }else{
            $arrExt[]=new paloExtensions($code_feature,new ext_goto("1","s",$this->code.'-app-blacklist-add'),1);
            $contextoBLAdd->arrExtensions=$arrBLadd;
        }

        $arrBLaddIn[]=new paloExtensions("s",new ext_set("NumLoops",'$[${NumLoops} + 1]'),1);
        $arrBLaddIn[]=new paloExtensions("s",new ext_playback("pm-invalid-option"));
        $arrBLaddIn[]=new paloExtensions("s",new ext_gotoIf('$[${NumLoops} < 3]',$this->code.'-app-blacklist-add,s,start'));
        $arrBLaddIn[]=new paloExtensions("s",new ext_playback("goodbye"));
        $arrBLaddIn[]=new paloExtensions("s",new ext_hangup());

        $contextoBLAddIn=new paloContexto($this->code,"app-blacklist-add-invalid");
        if($contextoBLAddIn===false){
            $contextoBLAddIn->errMsg="app-blacklist-add-invalid. Error: ".$contextoBLAddIn->errMsg;
        }else
            $contextoBLAddIn->arrExtensions=$arrBLaddIn;

        return array($contextoBLAdd,$contextoBLAddIn);
    }

    private function dialPlanBlacklist_blacklist_lcall(&$arrExt,$code_feature){
        $arrBLlt=array();
        $arrBLlt[]=new paloExtensions("s",new ext_answer(),"1");
        $arrBLlt[]=new paloExtensions("s",new ext_wait("1"));
        $arrBLlt[]=new paloExtensions("s",new ext_set('lastcaller','${DB(CALLTRACE/'.$this->code.'/${CALLERID(number)})}'));
        $arrBLlt[]=new paloExtensions("s",new ext_gotoIf('$[ $[ "${lastcaller}" = "" ] | $[ "${lastcaller}" = "unknown" ] ]',"noinfo"));
        $arrBLlt[]=new paloExtensions("s",new ext_playback('privacy-to-blacklist-last-caller&telephone-number'));
        $arrBLlt[]=new paloExtensions("s",new ext_saydigits('${lastcaller}'));
        $arrBLlt[]=new paloExtensions("s",new ext_set('TIMEOUT(digit)',"3"));
        $arrBLlt[]=new paloExtensions("s",new ext_set('TIMEOUT(response)',"7"));
        $arrBLlt[]=new paloExtensions("s",new ext_playback('if-correct-press&digits/1'));
        $arrBLlt[]=new paloExtensions("s",new ext_goto('end'));
        $arrBLlt[]=new paloExtensions("s",new ext_playback('unidentified-no-callback'),"n","noinfo");
        $arrBLlt[]=new paloExtensions("s",new ext_hangup());
        $arrBLlt[]=new paloExtensions("s",new ext_noop("Waiting for input"));
        $arrBLlt[]=new paloExtensions("s",new ext_waitexten("60"),"n","end");
        $arrBLlt[]=new paloExtensions("s",new ext_playback('sorry-youre-having-problems&goodbye'));
        $arrBLlt[]=new paloExtensions("1",new ext_set('DB(blacklist/{CODE}/${lastcaller}',"1"),"1");
        $arrBLlt[]=new paloExtensions("1",new ext_playback('num-was-successfully'));
        $arrBLlt[]=new paloExtensions("1",new ext_playback('added'));

        $contextoBLlt=new paloContexto($this->code,"app-blacklist-last");
        if($contextoBLlt===false){
            $contextoBLlt->errMsg="app-blacklist-last. Error: ".$contextoBLlt->errMsg;
        }else{
            $arrExt[]=new paloExtensions($code_feature,new ext_goto("1","s",$this->code.'-app-blacklist-last'),1);
            $contextoBLlt->arrExtensions=$arrBLlt;
        }

        return array($contextoBLlt);
    }

    private function dialPlanBlacklist_blacklist_rm(&$arrExt,$code_feature){
        $arrBLrm=array();
        $arrBLrm[]=new paloExtensions("s",new ext_answer(),"1");
        $arrBLrm[]=new paloExtensions("s",new ext_wait("1"));
        $arrBLrm[]=new paloExtensions("s",new ext_playback("entr-num-rmv-blklist"));
        $arrBLrm[]=new paloExtensions("s",new ext_set("TIMEOUT(digit)","5"));
        $arrBLrm[]=new paloExtensions("s",new ext_set("TIMEOUT(digit)","60"));
        $arrBLrm[]=new paloExtensions("s",new ext_read("blacknr","then-press-pound"));
        $arrBLrm[]=new paloExtensions("s",new ext_saydigits('${blacknr}'));
        $arrBLrm[]=new paloExtensions("s",new ext_playback("if-correct-press&digits/1"));
        $arrBLrm[]=new paloExtensions("s",new ext_noop("Waiting for input"));
        $arrBLrm[]=new paloExtensions("s",new ext_waitexten("60"),"n","end");
        $arrBLrm[]=new paloExtensions("s",new ext_playback("sorry-youre-having-problems&goodbye"));
        $arrBLrm[]=new paloExtensions("1",new ext_noop('Deleting: blacklist/{CODE}/${blacknr} ${DB_DELETE(blacklist/{CODE}/${blacknr})}'));
        $arrBLrm[]=new paloExtensions("1",new ext_playback("num-was-successfully&removed"));
        $arrBLrm[]=new paloExtensions("1",new ext_wait("1"));
        $arrBLrm[]=new paloExtensions("1",new ext_hangup());

        $contextoBLrm=new paloContexto($this->code,"app-blacklist-remove");
        if($contextoBLrm===false){
            $contextoBLrm->errMsg="app-blacklist-remove. Error: ".$contextoBLrm->errMsg;
        }else{
            $contextoBLrm->arrExtensions=$arrBLrm;
            $arrExt[]=new paloExtensions($code_feature,new ext_goto("1","s",$this->code.'-app-blacklist-remove'),1);
        }

        return array($contextoBLrm);
    }

    private function createDialPlanFuntionCF(&$arrFromInt){
        $arrContext=array();
        $arrFeature=array("cf_all_act","cf_all_desact","cf_all_promp","cf_busy_act","cf_busy_desact","cf_busy_promp","cf_nu_act","cf_nu_desact","cf_toggle");
        unset($this->arrFeatureCode);
        $this->setArrFCbyCategory($arrFeature);
        if(is_array($this->arrFeatureCode)){
            foreach($this->arrFeatureCode as $value){
                if($value!=false){
                    //funcion que me devuelve un arreglo con los contexto creados
                    $fname="dialPlanCF_".$value->name;
                    $contexts=$this->$fname($value->getCurrentCode(),$arrFromInt);
                    $arrContext=array_merge($arrContext,$contexts);
                }
            }
        }

        return $arrContext;
    }

    private function dialPlanCF_cf_all_act($fcode,&$arrFromInt){
        $arrCF=array();
        $length=strlen($fcode);
        $arrCF[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrCF[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions($fcode,new ext_read('fromext', 'call-fwd-unconditional&please-enter-your&extension&then-press-pound'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('fromext', '${IF($["foo${fromext}"="foo"]?${EXTUSER}:${fromext})}'));
        $arrCF[]=new paloExtensions($fcode,new ext_wait('1'));
        $arrCF[]=new paloExtensions($fcode,new ext_read('toext', 'ent-target-attendant&then-press-pound'),"n",'startread');
        $arrCF[]=new paloExtensions($fcode,new ext_gotoif('$["foo${toext}"="foo"]', 'startread'));
        $arrCF[]=new paloExtensions($fcode,new ext_wait('1'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('DB(CF/'.$this->code.'/${fromext})', '${toext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('STATE', 'BUSY')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions($fcode,new ext_gosub('1', 'state', $this->code.'-app-cf-on')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions($fcode,new ext_playback('call-fwd-unconditional&for&extension'),"n","hook_1");
        $arrCF[]=new paloExtensions($fcode,new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_playback('is-set-to'));
        $arrCF[]=new paloExtensions($fcode,new ext_saydigits('${toext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_answer(),"1");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_wait("1"));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('fromext','${EXTUSER}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('toext','${EXTEN:'.$length.'}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('DB(CF/'.$this->code.'/${fromext})', '${toext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('STATE', 'BUSY')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_gosub('1', 'state', $this->code.'-app-cf-on')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('call-fwd-unconditional&for&extension'),"n","hook_2");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('is-set-to'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_saydigits('${toext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_macro($this->code.'-hangupcall'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:CF'.$this->code.'_${fromext})', '${STATE}'),"1");
        $arrCF[]=new paloExtensions("state",new ext_dbget('DEVICES','EXTUSER/'.$this->code.'/${fromext}/device'));
        $arrCF[]=new paloExtensions("state",new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('ITER', '1'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:DEVCF${CUT(DEVICES,&,${ITER})})','${STATE}'),"n","begin");
        $arrCF[]=new paloExtensions("state",new ext_setvar('ITER', '$[${ITER} + 1]'));
        $arrCF[]=new paloExtensions("state",new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
        $arrCF[]=new paloExtensions("state",new ext_return(),"n","return");

        $contextoCF=new paloContexto($this->code,"app-cf-on");
        if($contextoCF===false){
            $contextoCF->errMsg="app-cf-on. Error: ".$contextoCF->errMsg;
        }else{
            $contextoCF->arrExtensions=$arrCF;
            $arrFromInt[]["name"]="app-cf-on";
        }

        return array($contextoCF);
    }

    private function dialPlanCF_cf_all_desact($fcode,&$arrFromInt){
        $arrCF=array();
        $length=strlen($fcode);
        $arrCF[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrCF[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('fromext', '${EXTUSER}'));
        $arrCF[]=new paloExtensions($fcode,new ext_dbdel('CF/'.$this->code.'/${fromext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('STATE', 'NOT_INUSE')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions($fcode,new ext_gosub('1', 'state', $this->code.'-app-cf-off')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions($fcode,new ext_playback('call-fwd-unconditional&de-activated'),"n","hook_1");
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_answer(),"1");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_wait("1"));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('fromext', '${EXTEN:'.$length.'}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_dbdel('CF/'.$this->code.'/${fromext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('STATE', 'NOT_INUSE')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_gosub('1', 'state', $this->code.'-app-cf-off')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('call-fwd-unconditional&for&extension'),"n","hook_2");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('cancelled'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_macro($this->code.'-hangupcall'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:CF'.$this->code.'_${fromext})', '${STATE}'),"1");
        $arrCF[]=new paloExtensions("state",new ext_dbget('DEVICES','EXTUSER/'.$this->code.'/${fromext}/device'));
        $arrCF[]=new paloExtensions("state",new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('ITER', '1'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:DEVCF${CUT(DEVICES,&,${ITER})})','${STATE}'),"n","begin");
        $arrCF[]=new paloExtensions("state",new ext_setvar('ITER', '$[${ITER} + 1]'));
        $arrCF[]=new paloExtensions("state",new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
        $arrCF[]=new paloExtensions("state",new ext_return(),"n","return");

        $contextoCF=new paloContexto($this->code,"app-cf-off");
        if($contextoCF===false){
            $contextoCF->errMsg="app-cf-off. Error: ".$contextoCF->errMsg;
        }else{
            $contextoCF->arrExtensions=$arrCF;
            $arrFromInt[]["name"]="app-cf-off";
        }

        return array($contextoCF);
    }

    private function dialPlanCF_cf_all_promp($fcode,&$arrFromInt){
        $arrCF=array();
        $length=strlen($fcode);
        $arrCF[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrCF[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions($fcode,new ext_read('fromext', 'please-enter-your&extension&then-press-pound'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('fromext', '${IF($["foo${fromext}"="foo"]?${EXTUSER}:${fromext})}'));
        $arrCF[]=new paloExtensions($fcode,new ext_wait('1'));
        $arrCF[]=new paloExtensions($fcode,new ext_dbdel('CF/'.$this->code.'/${fromext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('STATE', 'NOT_INUSE')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions($fcode,new ext_gosub('1', 'state', $this->code.'-app-cf-off')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions($fcode,new ext_playback('call-fwd-unconditional&for&extension'),"n","hook_1");
        $arrCF[]=new paloExtensions($fcode,new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_playback('cancelled'));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:CF'.$this->code.'_${fromext})', '${STATE}'),"1");
        $arrCF[]=new paloExtensions("state",new ext_dbget('DEVICES','EXTUSER/'.$this->code.'/${fromext}/device'));
        $arrCF[]=new paloExtensions("state",new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('ITER', '1'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:DEVCF${CUT(DEVICES,&,${ITER})})','${STATE}'),"n","begin");
        $arrCF[]=new paloExtensions("state",new ext_setvar('ITER', '$[${ITER} + 1]'));
        $arrCF[]=new paloExtensions("state",new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
        $arrCF[]=new paloExtensions("state",new ext_return(),"n","return");

        $contextoCF=new paloContexto($this->code,"app-cf-off-any");
        if($contextoCF===false){
            $contextoCF->errMsg="app-cf-off-any. Error: ".$contextoCF->errMsg;
        }else{
            $contextoCF->arrExtensions=$arrCF;
            $arrFromInt[]["name"]="app-cf-off-any";
        }

        return array($contextoCF);
    }

    private function dialPlanCF_cf_busy_act($fcode,&$arrFromInt){
        $arrCF=array();
        $length=strlen($fcode);
        $arrCF[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrCF[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions($fcode,new ext_read('fromext', 'call-fwd-on-busy&please-enter-your&extension&then-press-pound'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('fromext', '${IF($["foo${fromext}"="foo"]?${EXTUSER}:${fromext})}'));
        $arrCF[]=new paloExtensions($fcode,new ext_wait('1'));
        $arrCF[]=new paloExtensions($fcode,new ext_read('toext', 'ent-target-attendant&then-press-pound'),"n",'startread');
        $arrCF[]=new paloExtensions($fcode,new ext_gotoif('$["foo${toext}"="foo"]', 'startread'));
        $arrCF[]=new paloExtensions($fcode,new ext_wait('1'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('DB(CFB/'.$this->code.'/${fromext})', '${toext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_playback('call-fwd-on-busy&for&extension'),"n","hook_1");
        $arrCF[]=new paloExtensions($fcode,new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_playback('is-set-to'));
        $arrCF[]=new paloExtensions($fcode,new ext_saydigits('${toext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_answer(),"1");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_wait("1"));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('fromext','${EXTUSER}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('toext','${EXTEN:'.$length.'}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('DB(CFB/'.$this->code.'/${fromext})', '${toext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('call-fwd-on-busy&for&extension'),"n","hook_2");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('is-set-to'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_saydigits('${toext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_macro($this->code.'-hangupcall'));

        $contextoCF=new paloContexto($this->code,"app-cf-busy-on");
        if($contextoCF===false){
            $contextoCF->errMsg="app-cf-busy-on. Error: ".$contextoCF->errMsg;
        }else{
            $contextoCF->arrExtensions=$arrCF;
            $arrFromInt[]["name"]="app-cf-busy-on";
        }

        return array($contextoCF);
    }

    private function dialPlanCF_cf_busy_desact($fcode,&$arrFromInt){
        $arrCF=array();
        $length=strlen($fcode);
        $arrCF[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrCF[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('fromext', '${EXTUSER}'));
        $arrCF[]=new paloExtensions($fcode,new ext_dbdel('CFB/'.$this->code.'/${fromext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_playback('call-fwd-on-busy&de-activated'),"n","hook_1");
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_answer(),"1");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_wait("1"));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('fromext', '${EXTEN:'.$length.'}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_dbdel('CFB/'.$this->code.'/${fromext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('call-fwd-on-busy&de-activated'),"n","hook_2");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('cancelled'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_macro($this->code.'-hangupcall'));

        $contextoCF=new paloContexto($this->code,"app-cf-busy-off");
        if($contextoCF===false){
            $contextoCF->errMsg="app-cf-busy-off. Error: ".$contextoCF->errMsg;
        }else{
            $contextoCF->arrExtensions=$arrCF;
            $arrFromInt[]["name"]="app-cf-busy-off";
        }

        return array($contextoCF);
    }

    private function dialPlanCF_cf_busy_promp($fcode,&$arrFromInt){
        $arrCF=array();
        $length=strlen($fcode);
        $arrCF[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrCF[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions($fcode,new ext_read('fromext', 'please-enter-your&extension&then-press-pound'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('fromext', '${IF($["foo${fromext}"="foo"]?${EXTUSER}:${fromext})}'));
        $arrCF[]=new paloExtensions($fcode,new ext_wait('1'));
        $arrCF[]=new paloExtensions($fcode,new ext_dbdel('CFB/'.$this->code.'/${fromext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_playback('call-fwd-on-busy&for&extension'),"n","hook_1");
        $arrCF[]=new paloExtensions($fcode,new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_playback('cancelled'));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $contextoCF=new paloContexto($this->code,"app-cf-busy-off-any");
        if($contextoCF===false){
            $contextoCF->errMsg="app-cf-busy-off-any. Error: ".$contextoCF->errMsg;
        }else{
            $contextoCF->arrExtensions=$arrCF;
            $arrFromInt[]["name"]="app-cf-busy-off-any";
        }

        return array($contextoCF);
    }

    private function dialPlanCF_cf_nu_act($fcode,&$arrFromInt){
        $arrCF=array();
        $length=strlen($fcode);
        $arrCF[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrCF[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions($fcode,new ext_read('fromext', 'call-fwd-no-ans&please-enter-your&extension&then-press-pound'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('fromext', '${IF($["foo${fromext}"="foo"]?${EXTUSER}:${fromext})}'));
        $arrCF[]=new paloExtensions($fcode,new ext_wait('1'));
        $arrCF[]=new paloExtensions($fcode,new ext_read('toext', 'ent-target-attendant&then-press-pound'),"n",'startread');
        $arrCF[]=new paloExtensions($fcode,new ext_gotoif('$["foo${toext}"="foo"]', 'startread'));
        $arrCF[]=new paloExtensions($fcode,new ext_wait('1'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('DB(CFU/'.$this->code.'/${fromext})', '${toext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_playback('call-fwd-no-ans&for&extension'),"n","hook_1");
        $arrCF[]=new paloExtensions($fcode,new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_playback('is-set-to'));
        $arrCF[]=new paloExtensions($fcode,new ext_saydigits('${toext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_answer(),"1");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_wait("1"));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('fromext','${EXTUSER}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('toext','${EXTEN:'.$length.'}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('DB(CFU/'.$this->code.'/${fromext})', '${toext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('call-fwd-no-ans&for&extension'),"n","hook_2");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('is-set-to'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_saydigits('${toext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_macro($this->code.'-hangupcall'));

        $contextoCF=new paloContexto($this->code,"app-cf-unavailable-on");
        if($contextoCF===false){
            $contextoCF->errMsg="app-cf-unavailable-on. Error: ".$contextoCF->errMsg;
        }else{
            $contextoCF->arrExtensions=$arrCF;
            $arrFromInt[]["name"]="app-cf-unavailable-on";
        }

        return array($contextoCF);
    }

    private function dialPlanCF_cf_nu_desact($fcode,&$arrFromInt){
        $arrCF=array();
        $length=strlen($fcode);
        $arrCF[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrCF[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('fromext', '${EXTUSER}'));
        $arrCF[]=new paloExtensions($fcode,new ext_dbdel('CFU/'.$this->code.'/${fromext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_playback('call-fwd-no-ans&de-activated'),"n","hook_1");
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_answer(),"1");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_wait("1"));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_setvar('fromext', '${EXTEN:'.$length.'}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_dbdel('CFU/'.$this->code.'/${fromext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('call-fwd-no-ans&for&extension'),"n","hook_2");
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_playback('cancelled'));
        $arrCF[]=new paloExtensions("_".$fcode.".",new ext_macro($this->code.'-hangupcall'));

        $contextoCF=new paloContexto($this->code,"app-cf-unavailable-off");
        if($contextoCF===false){
            $contextoCF->errMsg="app-cf-unavailable-off. Error: ".$contextoCF->errMsg;
        }else{
            $contextoCF->arrExtensions=$arrCF;
            $arrFromInt[]["name"]="app-cf-unavailable-off";
        }

        return array($contextoCF);
    }

    private function dialPlanCF_cf_toggle($fcode,&$arrFromInt){
        $arrCF=array();
        $length=strlen($fcode);
        $arrCF[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrCF[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('fromext', '${EXTUSER}'));
        $arrCF[]=new paloExtensions($fcode,new ext_gotoif('$["${DB(CF/'.$this->code.'/${fromext})}" = ""]', 'activate','deactivate'));
        $arrCF[]=new paloExtensions($fcode,new ext_read('toext', 'ent-target-attendant&then-press-pound'),"n",'activate');
        $arrCF[]=new paloExtensions($fcode,new ext_gotoif('$["foo${toext}"="foo"]', 'activate'));
        $arrCF[]=new paloExtensions($fcode,new ext_wait('1'));
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('DB(CF/'.$this->code.'/${fromext})', '${toext}'),"n","toext");
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('STATE', 'BUSY')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions($fcode,new ext_gosub('1', 'state', $this->code.'-app-cf-toggle')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions($fcode,new ext_playback('call-fwd-unconditional&for&extension'),"n","hook_on");
        $arrCF[]=new paloExtensions($fcode,new ext_saydigits('${fromext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_playback('is-set-to'));
        $arrCF[]=new paloExtensions($fcode,new ext_saydigits('${toext}'));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrCF[]=new paloExtensions($fcode,new ext_answer(),"n","setdirect");
        $arrCF[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCF[]=new paloExtensions($fcode,new ext_goto('toext'));
        $arrCF[]=new paloExtensions($fcode,new ext_dbdel('CF/'.$this->code.'/${fromext}'),"n",'deactivate');
        $arrCF[]=new paloExtensions($fcode,new ext_setvar('STATE', 'NOT_INUSE')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions($fcode,new ext_gosub('1', 'state', $this->code.'-app-cf-toggle')); //debe ser astrisk mayor a 1.4
        $arrCF[]=new paloExtensions($fcode,new ext_playback('call-fwd-unconditional&de-activated'),"n","hook_off");
        $arrCF[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:CF'.$this->code.'_${fromext})', '${STATE}'),"1");
        $arrCF[]=new paloExtensions("state",new ext_dbget('DEVICES','EXTUSER/'.$this->code.'/${fromext}/device'));
        $arrCF[]=new paloExtensions("state",new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('ITER', '1'));
        $arrCF[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:DEVCF${CUT(DEVICES,&,${ITER})})','${STATE}'),"n","begin");
        $arrCF[]=new paloExtensions("state",new ext_setvar('ITER', '$[${ITER} + 1]'));
        $arrCF[]=new paloExtensions("state",new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
        $arrCF[]=new paloExtensions("state",new ext_return(),"n","return");

        $contextoCF=new paloContexto($this->code,"app-cf-toggle");
        if($contextoCF===false){
            $contextoCF->errMsg="app-cf-toggle. Error: ".$contextoCF->errMsg;
        }else{
            $contextoCF->arrExtensions=$arrCF;
            $arrFromInt[]["name"]="app-cf-toggle";
        }

        $arrContexts=array($contextoCF);
        
        $arrCFhint=array();
        $result=$this->getAllDevice($this->domain);
        if(is_array($result)){
            foreach($result as $value){
                $offset=$length+strlen($value["exten"]);
                $arrCFhint[]=new paloExtensions($fcode.$value["exten"], new ext_goto("1",$fcode,$this->code."-app-cf-toggle"),"1");
                $arrCFhint[]=new paloExtensions($fcode.$value["exten"], new extension("Custom:DEVCF".$value['device']),"hint");
                $arrCFhint[]=new paloExtensions('_'.$fcode.$value["exten"].".", new ext_set("toext",'${EXTEN:'.$offset.'}'),"1");
                $arrCFhint[]=new paloExtensions('_'.$fcode.$value["exten"].".", new ext_goto("setdirect",$fcode,$this->code."-app-cf-toggle"));
            }
            $contextoCFhint=new paloContexto($this->code,"ext-cf-hints");
            if($contextoCFhint===false){
                $contextoCFhint->errMsg="app-cf-toggle. Error: ".$contextoCFhint->errMsg;
            }else{
                $contextoCFhint->arrExtensions=$arrCFhint;
                $arrFromInt[]["name"]="ext-cf-hints";
            }
            $arrContexts[]=$contextoCFhint;
        }

        return $arrContexts;
    }

    private function createDialPlanFuntionCW(&$arrFromInt){
        $arrContext=array();
        $arrFeature=array("cw_act","cw_desact");
        unset($this->arrFeatureCode);
        $this->setArrFCbyCategory($arrFeature);
        if(is_array($this->arrFeatureCode)){
            foreach($this->arrFeatureCode as $value){
                if($value!=false){
                    //funcion que me devuelve un arreglo con los contexto creados
                    $fname="dialPlanCW_".$value->name;
                    $contexts=$this->$fname($value->getCurrentCode(),$arrFromInt);
                    $arrContext=array_merge($arrContext,$contexts);
                }
            }
        }

        return $arrContext;
    }

    private function dialPlanCW_cw_act($fcode,&$arrFromInt){
        $arrCW=array();
        $arrCW[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrCW[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCW[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCW[]=new paloExtensions($fcode,new ext_setvar('CW/'.$this->code.'/${EXTUSER}', 'ENABLED'));
        $arrCW[]=new paloExtensions($fcode,new ext_playback('call-waiting&activated'),"n","hook_1");
        $arrCW[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));

        $contextoCW=new paloContexto($this->code,"app-callwaiting-cwon");
        if($contextoCW===false){
            $contextoCW->errMsg="app-callwaiting-cwon. Error: ".$contextoCW->errMsg;
        }else{
            $contextoCW->arrExtensions=$arrCW;
            $arrFromInt[]["name"]="app-callwaiting-cwon";
        }
        return array($contextoCW);
    }

    private function dialPlanCW_cw_desact($fcode,&$arrFromInt){
        $arrCW=array();
        $arrCW[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrCW[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrCW[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrCW[]=new paloExtensions($fcode,new ext_dbdel('CW/'.$this->code.'/${EXTUSER}'));
        $arrCW[]=new paloExtensions($fcode,new ext_playback('call-waiting&de-activated'));
        $arrCW[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));

        $contextoCW=new paloContexto($this->code,"app-callwaiting-cwoff");
        if($contextoCW===false){
            $contextoCW->errMsg="app-callwaiting-cwon. Error: ".$contextoCW->errMsg;
        }else{
            $contextoCW->arrExtensions=$arrCW;
            $arrFromInt[]["name"]="app-callwaiting-cwoff";
        }
        return array($contextoCW);
    }

    private function createDialPlanFuntionDICT(&$arrFromInt){
        $arrContext=array();
        $arrFeature=array("dictation_email","dictation_perform");
        unset($this->arrFeatureCode);
        $this->setArrFCbyCategory($arrFeature);
        if(is_array($this->arrFeatureCode)){
            foreach($this->arrFeatureCode as $value){
                if($value!=false){
                    //funcion que me devuelve un arreglo con los contexto creados
                    $fname="dialPlanDICT_".$value->name;
                    $contexts=$this->$fname($value->getCurrentCode(),$arrFromInt);
                    $arrContext=array_merge($arrContext,$contexts);
                }
            }
        }
        return $arrContext;
    }

    private function dialPlanDICT_dictation_perform($fcode,&$arrFromInt){
        global $confAsterisk;
        $arrDT=array();
        $arrDT[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrDT[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrDT[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrDT[]=new paloExtensions($fcode,new ext_NoOp('CallerID is ${EXTUSER}'));
        $arrDT[]=new paloExtensions($fcode,new ext_setvar('DICTENABLED','${DB(EXTUSER/'.$this->code.'/${EXTUSER}/dictate/enabled)}'));
        $arrDT[]=new paloExtensions($fcode,new ext_gotoif('$[$["x${DICTENABLED}"="x"]|$["x${DICTENABLED}"="xdisabled"]]','nodict', 'dictok'));
        $arrDT[]=new paloExtensions($fcode,new ext_playback('feature-not-avail-line'),"n","nodict");
        $arrDT[]=new paloExtensions($fcode,new ext_hangup());
        $arrDT[]=new paloExtensions($fcode,new ext_dictate($confAsterisk['ASTVARLIBDIR']['valor'].'/sounds/'.$this->domain.'/dictate/${EXTUSER}'),"n","dictok");
        $arrDT[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));

        $contextoDT=new paloContexto($this->code,"app-dictate-record");
        if($contextoDT===false){
            $contextoDT->errMsg="app-dictate-record. Error: ".$contextoDT->errMsg;
        }else{
            $contextoDT->arrExtensions=$arrDT;
            $arrFromInt[]["name"]="app-dictate-record";
        }
        return array($contextoDT);
    }

    private function dialPlanDICT_dictation_email($fcode,&$arrFromInt){
        global $confAsterisk;
        $arrDT=array();
        $arrDT[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrDT[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrDT[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrDT[]=new paloExtensions($fcode,new ext_NoOp('CallerID is ${EXTUSER}'));
        $arrDT[]=new paloExtensions($fcode,new ext_setvar('DICTENABLED','${DB(EXTUSER/'.$this->code.'/${EXTUSER}/dictate/enabled)}'));
        $arrDT[]=new paloExtensions($fcode,new ext_gotoif('$[$["x${DICTENABLED}"="x"]|$["x${DICTENABLED}"="xdisabled"]]','nodict', 'dictok'));
        $arrDT[]=new paloExtensions($fcode,new ext_playback('feature-not-avail-line'),"n","nodict");
        $arrDT[]=new paloExtensions($fcode,new ext_hangup());
        $arrDT[]=new paloExtensions($fcode,new ext_read('DICTFILE','enter-filename-short'),"n","dictok");
        $arrDT[]=new paloExtensions($fcode,new ext_setvar('DICTEMAIL','${DB(EXTUSER/'.$this->code.'/${EXTUSER}/dictate/email)}'));
        $arrDT[]=new paloExtensions($fcode,new ext_setvar('DICTFMT','${DB(EXTUSER/'.$this->code.'/${EXTUSER}/dictate/format)}'));
        $arrDT[]=new paloExtensions($fcode,new ext_setvar('NAME','${DB(EXTUSER/'.$this->code.'/${EXTUSER}/cidname)}'));
        $arrDT[]=new paloExtensions($fcode,new ext_playback('dictation-being-processed'));
        $arrDT[]=new paloExtensions($fcode,new ext_system($confAsterisk['ASTVARLIBDIR']['valor'].'/bin/audio-email.pl --file '.$confAsterisk['ASTVARLIBDIR']['valor'].'/sounds/'.$this->domain.'/dictate/${EXTUSER}/${DICTFILE}.raw --attachment dict-${DICTFILE} --format ${DICTFMT} --to ${DICTEMAIL} --subject "Dictation from ${NAME} Attached"'));
        $arrDT[]=new paloExtensions($fcode,new ext_playback('dictation-sent'));
        $arrDT[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));

        $contextoDT=new paloContexto($this->code,"app-dictate-send");
        if($contextoDT===false){
            $contextoDT->errMsg="app-dictate-send. Error: ".$contextoDT->errMsg;
        }else{
            $contextoDT->arrExtensions=$arrDT;
            $arrFromInt[]["name"]="app-dictate-send";
        }
        return array($contextoDT);
    }

    private function createDialPlanFuntionDND(&$arrFromInt){
        $arrContext=array();
        $arrFeature=array("dnd_act","dnd_desact","dnd_toggle");
        unset($this->arrFeatureCode);
        $this->setArrFCbyCategory($arrFeature);
        if(is_array($this->arrFeatureCode)){
            foreach($this->arrFeatureCode as $value){
                if($value!=false){
                    //funcion que me devuelve un arreglo con los contexto creados
                    $fname="dialPlanDND_".$value->name;
                    $contexts=$this->$fname($value->getCurrentCode(),$arrFromInt);
                    $arrContext=array_merge($arrContext,$contexts);
                }
            }
        }
        return $arrContext;
    }

    private function dialPlanDND_dnd_act($fcode,&$arrFromInt){
        $arrDND=array();
        $arrDND[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrDND[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrDND[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrDND[]=new paloExtensions($fcode,new ext_setvar('DND/'.$this->code.'/${EXTUSER}', 'YES'));
        $arrDND[]=new paloExtensions($fcode,new ext_setvar('STATE', 'BUSY'));
        $arrDND[]=new paloExtensions($fcode,new ext_gosub('1', 'state', $this->code."-app-dnd-on"));
        $arrDND[]=new paloExtensions($fcode,new ext_playback('do-not-disturb&activated'),"n","hook_1");
        $arrDND[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:DND'.$this->code.'_${EXTUSER})', '${STATE}'),"1");
        $arrDND[]=new paloExtensions("state",new ext_dbget('DEVICES','EXTUSER/'.$this->code.'/${fromext}/device'));
        $arrDND[]=new paloExtensions("state",new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('ITER', '1'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:DEVDND${CUT(DEVICES,&,${ITER})})','${STATE}'),"n","begin");
        $arrDND[]=new paloExtensions("state",new ext_setvar('ITER', '$[${ITER} + 1]'));
        $arrDND[]=new paloExtensions("state",new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
        $arrDND[]=new paloExtensions("state",new ext_return(),"n","return");

        $contextoDND=new paloContexto($this->code,"app-dnd-on");
        if($contextoDND===false){
            $contextoDND->errMsg="app-dnd-on. Error: ".$contextoDND->errMsg;
        }else{
            $contextoDND->arrExtensions=$arrDND;
            $arrFromInt[]["name"]="app-dnd-on";
        }
        return array($contextoDND);
    }

    private function dialPlanDND_dnd_desact($fcode,&$arrFromInt){
        $arrDND=array();
        $arrDND[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrDND[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrDND[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrDND[]=new paloExtensions($fcode,new ext_dbdel('DND/'.$this->code.'/${EXTUSER}'));
        $arrDND[]=new paloExtensions($fcode,new ext_setvar('STATE', 'NOT_INUSE'));
        $arrDND[]=new paloExtensions($fcode,new ext_gosub('1', 'state', $this->code."-app-dnd-off"));
        $arrDND[]=new paloExtensions($fcode,new ext_playback('do-not-disturb&de-activated'),"n","hook_1");
        $arrDND[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:DND'.$this->code.'_${EXTUSER})', '${STATE}'),"1");
        $arrDND[]=new paloExtensions("state",new ext_dbget('DEVICES','EXTUSER/'.$this->code.'/${fromext}/device'));
        $arrDND[]=new paloExtensions("state",new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('ITER', '1'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:DEVDND${CUT(DEVICES,&,${ITER})})','${STATE}'),"n","begin");
        $arrDND[]=new paloExtensions("state",new ext_setvar('ITER', '$[${ITER} + 1]'));
        $arrDND[]=new paloExtensions("state",new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
        $arrDND[]=new paloExtensions("state",new ext_return(),"n","return");

        $contextoDND=new paloContexto($this->code,"app-dnd-off");
        if($contextoDND===false){
            $contextoDND->errMsg="app-dnd-off. Error: ".$contextoDND->errMsg;
        }else{
            $contextoDND->arrExtensions=$arrDND;
            $arrFromInt[]["name"]="app-dnd-off";
        }
        return array($contextoDND);
    }

    private function dialPlanDND_dnd_toggle($fcode,&$arrFromInt){
        $arrDND=array();
        $arrDND[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrDND[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrDND[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrDND[]=new paloExtensions($fcode, new ext_gotoif('$["${DB(DND/'.$this->code.'/${EXTUSER})}" = ""]', 'activate', 'deactivate'));
        $arrDND[]=new paloExtensions($fcode,new ext_setvar('DND/'.$this->code.'/${EXTUSER}', 'YES'),"n","activate");
        $arrDND[]=new paloExtensions($fcode,new ext_setvar('STATE', 'BUSY'));
        $arrDND[]=new paloExtensions($fcode,new ext_gosub('1', 'state', $this->code."-app-dnd-toggle"));
        $arrDND[]=new paloExtensions($fcode,new ext_playback('do-not-disturb&activated'),"n","hook_1");
        $arrDND[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrDND[]=new paloExtensions($fcode,new ext_dbdel('DND/'.$this->code.'/${EXTUSER}'),"n","deactivate");
        $arrDND[]=new paloExtensions($fcode,new ext_setvar('STATE', 'NOT_INUSE'));
        $arrDND[]=new paloExtensions($fcode,new ext_gosub('1', 'state', $this->code."-app-dnd-off"));
        $arrDND[]=new paloExtensions($fcode,new ext_playback('do-not-disturb&de-activated'),"n","hook_2");
        $arrDND[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:DND'.$this->code.'_${EXTUSER})', '${STATE}'),"1");
        $arrDND[]=new paloExtensions("state",new ext_dbget('DEVICES','EXTUSER/'.$this->code.'/${fromext}/device'));
        $arrDND[]=new paloExtensions("state",new ext_gotoif('$["${DEVICES}" = "" ]', 'return'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('LOOPCNT', '${FIELDQTY(DEVICES,&)}'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('ITER', '1'));
        $arrDND[]=new paloExtensions("state",new ext_setvar('DEVICE_STATE(Custom:DEVDND${CUT(DEVICES,&,${ITER})})','${STATE}'),"n","begin");
        $arrDND[]=new paloExtensions("state",new ext_setvar('ITER', '$[${ITER} + 1]'));
        $arrDND[]=new paloExtensions("state",new ext_gotoif('$[${ITER} <= ${LOOPCNT}]', 'begin'));
        $arrDND[]=new paloExtensions("state",new ext_return(),"n","return");

        $contextoDND=new paloContexto($this->code,"app-dnd-toggle");
        if($contextoDND===false){
            $contextoDND->errMsg="app-dnd-toggle. Error: ".$contextoDND->errMsg;
        }else{
            $contextoDND->arrExtensions=$arrDND;
            $arrFromInt[]["name"]="app-dnd-toggle";
        }
        $arrContexts=array($contextoDND);

        $result=$this->getAllDevice($this->domain);
        $arrhint=array();
        if(is_array($result)){
            foreach($result as $value){
                $arrhint[]=new paloExtensions($fcode.$value["exten"], new ext_goto("1",$fcode,$this->code."-app-dnd-toggle"),'1');
                $arrhint[]=new paloExtensions($fcode.$value["exten"], new extension("Custom:DEVDND".$value['device']),"hint");
            }
            $contextohint=new paloContexto($this->code,"ext-dnd-hints");
            if($contextohint===false){
                $contextohint->errMsg="app-dnd-hints. Error: ".$contextohint->errMsg;
            }else{
                $contextohint->arrExtensions=$arrhint;
                $arrFromInt[]["name"]="ext-dnd-hints";
            }
            $arrContexts[]=$contextohint;
        }
        return $arrContexts;
    }

    private function createDialPlanFuntionInfo(&$arrFromInt){
        $arrContext=array();
        $arrEXT=array();
        $arrFeature=array("call_trace","directory","echo_test","speak_u_exten","speak_clock","pbdirectory");
        unset($this->arrFeatureCode);
        $this->setArrFCbyCategory($arrFeature);
        if(is_array($this->arrFeatureCode)){
            foreach($this->arrFeatureCode as $value){
                if($value!=false){
                    //funcion que me devuelve un arreglo con los contexto creados
                    if($value->name=="pbdirectory"){
                        $arrEXT[]=new paloExtensions($value->getCurrentCode(),new ext_goto(1,'pbdirectory'),"1");
                    }else{
                        $fname="dialPlanInfo_".$value->name;
                        $contexts=$this->$fname($value->getCurrentCode(),$arrFromInt);
                        $arrContext=array_merge($arrContext,$contexts);
                    }
                }
            }
        }

        $arrContext=array_merge($arrContext,$this->dialPlanInfo_pbdirectory($arrEXT,$arrFromInt));
        return $arrContext;
    }

    private function dialPlanInfo_call_trace($fcode,&$arrFromInt){
        $arrEXT=array();

        $arrEXT[]=new paloExtensions($fcode,new ext_goto('1', 's', $this->code.'-app-calltrace-perform'),"1");
        $contextoCT=new paloContexto($this->code,"app-calltrace");
        if($contextoCT===false){
            $contextoCT->errMsg="app-calltrace. Error: ".$contextoCT->errMsg;
        }else{
            $contextoCT->arrExtensions=$arrEXT;
            $arrFromInt[]["name"]="app-calltrace";
        }
        $arrContext=array($contextoCT);

        unset($arrEXT);
        $fcode="s";
        $arrEXT[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrEXT[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('info-about-last-call&telephone-number'));
        $arrEXT[]=new paloExtensions($fcode,new ext_setvar('lastcaller','${DB(CALLTRACE/'.$this->code.'/${EXTUSER})}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_gotoif('$[ $[ "${lastcaller}" = "" ] | $[ "${lastcaller}" = "unknown" ] ]', 'noinfo'));
        $arrEXT[]=new paloExtensions($fcode,new ext_saydigits('${lastcaller}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_setvar('TIMEOUT(digit)', '3'));
        $arrEXT[]=new paloExtensions($fcode,new ext_setvar('TIMEOUT(response)', '7'));
        $arrEXT[]=new paloExtensions($fcode,new ext_background('to-call-this-number&press-1'));
        $arrEXT[]=new paloExtensions($fcode,new ext_goto('fin'));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('from-unknown-caller'),"n",'noinfo');
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrEXT[]=new paloExtensions($fcode,new ext_noop('Waiting for input'),"n","fin");
        $arrEXT[]=new paloExtensions($fcode,new ext_waitexten(60));
        $arrEXT[]=new paloExtensions($fcode,new ext_Playback('sorry-youre-having-problems&goodbye'));
        $arrEXT[]=new paloExtensions("1",new ext_goto('1', '${lastcaller}', $this->code.'-from-internal'),"1");
        $arrEXT[]=new paloExtensions("i",new ext_playback('vm-goodbye'));
        $arrEXT[]=new paloExtensions("i",new ext_macro($this->code.'-hangupcall'));
        $arrEXT[]=new paloExtensions("t",new ext_playback('vm-goodbye'));
        $arrEXT[]=new paloExtensions("t",new ext_macro($this->code.'-hangupcall'));

        $contexto=new paloContexto($this->code,"app-calltrace-perform");
        if($contexto===false){
            $contexto->errMsg="app-calltrace-perform. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
        }
        $arrContext[]=$contexto;

        return $arrContext;
    }

    private function dialPlanInfo_directory($fcode,&$arrFromInt){
        //obtenemos las caracteristicas del directorio
        $vm_context=$this->code."-default";
        $dial_context=$this->code."-from-did-direct";
        $operator_ext="";
        //debido a como estan organizados los nombre dentro del voicemail
        //el campo fullname es (apellido nombre) por ello
        //DIRECTORY=last -> cuando se busca por nombre
        //DIRECTORY=first -> cuando se busca por apellido
        $query="select value from globals where organization_domain=? and variable=?";
        $result=$this->getFirstResultQuery($query,array($this->domain,"OPERATOR_XTN"));
        if($result!=false)
            $operator_ext=$result[0];

        $arrEXT=array();
        $arrEXT[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrEXT[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrEXT[]=new paloExtensions($fcode,new ext_setvar("DIRECTORY_OPT_LENGTH",'${IF($["${'.$this->code.'_DIRECTORY_OPT_LENGTH}"!="" ]?${'.$this->code.'_DIRECTORY_OPT_LENGTH}:3)}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_directory($vm_context,$dial_context,'${'.$this->code.'_DIRECTORY_OPT_EXT}${'.$this->code.'_DIRECTORY:0:1}(${DIRECTORY_OPT_LENGTH})p(1)'));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('vm-goodbye'));
        $arrEXT[]=new paloExtensions($fcode,new ext_hangup());
        if ($operator_ext != '') {
            $arrEXT[]=new paloExtensions('o',new ext_goto($this->code.'-from-internal,${'.$this->code.'_OPERATOR_XTN},1'));
        } else {
            $arrEXT[]=new paloExtensions('o',new ext_playback('privacy-incorrect'));
        }

        $contexto=new paloContexto($this->code,"app-directory");
        if($contexto===false){
            $contexto->errMsg="app-directory. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
            $arrFromInt[]["name"]="app-directory";
        }
        return array($contexto);
    }

    private function dialPlanInfo_echo_test($fcode,&$arrFromInt){
        $arrEXT=array();
        $arrEXT[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrEXT[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('demo-echotest'));
        $arrEXT[]=new paloExtensions($fcode, new ext_echo(''));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('demo-echodone'));
        $arrEXT[]=new paloExtensions($fcode, new ext_hangup());

        $contexto=new paloContexto($this->code,"app-echo-test");
        if($contexto===false){
            $contexto->errMsg="app-echo-test. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
            $arrFromInt[]["name"]="app-echo-test";
        }
        return array($contexto);
    }

    private function dialPlanInfo_speak_u_exten($fcode,&$arrFromInt){
        $arrEXT=array();
        $arrEXT[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrEXT[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code."-user-callerid"));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('your'));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('extension'));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('number'));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('is'));
        $arrEXT[]=new paloExtensions($fcode,new ext_saydigits('${EXTUSER}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_wait("2"));
        $arrEXT[]=new paloExtensions($fcode,new ext_hangup());

        $contexto=new paloContexto($this->code,"app-speakextennum");
        if($contexto===false){
            $contexto->errMsg="app-speakextennum. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
            $arrFromInt[]["name"]="app-speakextennum";
        }
        return array($contexto);
    }

    private function dialPlanInfo_speak_clock($fcode,&$arrFromInt){
        $arrEXT=array();
        $arrEXT[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrEXT[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrEXT[]=new paloExtensions($fcode,new ext_setvar('NumLoops','0'));
        $arrEXT[]=new paloExtensions($fcode,new ext_setvar('FutureTime','$[${EPOCH} + 11]'),"n","start");
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('at-tone-time-exactly'));
        $arrEXT[]=new paloExtensions($fcode,new ext_gotoif('$["${'.$this->code.'_TIMEFORMAT}" = "kM"]','hr24format'));
        $arrEXT[]=new paloExtensions($fcode,new ext_goto('waitloop'));
        $arrEXT[]=new paloExtensions($fcode,new ext_sayunixtime('${FutureTime},,kM and S seconds'),"n","hr24format");
        $arrEXT[]=new paloExtensions($fcode,new ext_set('TimeLeft', '$[${FutureTime} - ${EPOCH}]'),"n","waitloop");
        $arrEXT[]=new paloExtensions($fcode,new ext_gotoif('$[${TimeLeft} < 1]','playbeep'));
        $arrEXT[]=new paloExtensions($fcode,new ext_wait(1));
        $arrEXT[]=new paloExtensions($fcode,new ext_goto('waitloop'));
        $arrEXT[]=new paloExtensions($fcode,new ext_wait("5"));
        $arrEXT[]=new paloExtensions($fcode,new ext_setvar('NumLoops','$[${NumLoops} + 1]'));
        $arrEXT[]=new paloExtensions($fcode,new ext_gotoif('$[${NumLoops} < 5]','start'));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('goodbye'));
        $arrEXT[]=new paloExtensions($fcode,new ext_hangup());

        $contexto=new paloContexto($this->code,"app-speakingclock");
        if($contexto===false){
            $contexto->errMsg="app-speakingclock. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
            $arrFromInt[]["name"]="app-speakingclock";
        }
        return array($contexto);
    }

    private function dialPlanInfo_pbdirectory(&$arrEXT,&$arrFromInt){
        $opts="f";
        //DIRECTORY=last -> cuando se busca por nombre
        //DIRECTORY=first -> cuando se busca por apellido
        $query="select value from globals where organization_domain=? and variable=?";
        $result=$this->getFirstResultQuery($query,array($this->domain,"DIRECTORY"));
        if($result!=false){
            if($result[0]=="first" || $result[0]=="both")
                $opts="a";
        }

        $fcode="pbdirectory";
        $arrEXT[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrEXT[]=new paloExtensions($fcode,new ext_wait("1"));
        $arrEXT[]=new paloExtensions($fcode,new ext_macro('user-callerid'));
        $arrEXT[]=new paloExtensions($fcode,new ext_agi('pbdirectory,'.$this->code.",$opts"));
        $arrEXT[]=new paloExtensions($fcode,new ext_gotoif('$["${dialnumber}"=""]','hangup,1'));
        $arrEXT[]=new paloExtensions($fcode,new ext_noop('Got number to dial: ${dialnumber}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_dial('Local/${dialnumber}@'.$this->code.'-from-internal/n','',''));
        $arrEXT[]=new paloExtensions("hangup",new ext_hangup(),"1");

        $contexto=new paloContexto($this->code,"app-pbdirectory");
        if($contexto===false){
            $contexto->errMsg="app-pbdirectory. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
            $arrFromInt[]["name"]="app-pbdirectory";
        }
        return array($contexto);
    }

    private function createDialPlanFuntionSpeedDial(&$arrFromInt){
        $arrContext=array();
        $arrFeature=array("speeddial_set","speeddial_prefix");
        unset($this->arrFeatureCode);
        $this->setArrFCbyCategory($arrFeature);
        if(is_array($this->arrFeatureCode) && count($this->arrFeatureCode)>0){
            $arrContext[]=$this->dialplan_app_speeddial($arrFromInt);
            $arrContext[]=$this->dialplan_speeddialset();
        }
        return $arrContext;
    }

    private function dialplan_app_speeddial(&$arrFromInt){
        $arrExt=array();
        foreach($this->arrFeatureCode as $value){
            if($value!=false){
                $fcode=$value->getCurrentCode();
                switch ($value->name){
                    case "speeddial_set":
                        $arrEXT[]=new paloExtensions($fcode, new ext_goto(1, "s",$this->code.'-app-speeddial-set'),"1");
                        break;
                    case "speeddial_prefix":
                        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_macro('user-callerid',''),"1");
                        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_set('SPEEDDIALLOCATION','${EXTEN:'.(strlen($fcode)).'}'));
                        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_macro('speeddial-lookup','${SPEEDDIALLOCATION},${EXTUSER}'),"n","lookup");
                        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_gotoif('$["${SPEEDDIALNUMBER}"=""]','failed'));
                        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_goto('1','${SPEEDDIALNUMBER}',$this->code.'-from-internal'));
                        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_playback('speed-dial-empty'),"lookup+101");
                        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_congestion('')); 
                        break;
                }
            }
        }

        $contexto=new paloContexto($this->code,"app-speeddial");
        if($contexto===false){
            $contexto->errMsg="app-speeddial. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
            $arrFromInt[]["name"]="app-speeddial";
        }
        return $contexto;
    }

    private function dialplan_speeddialset(){
        $arrExt=array();
        $fcode="s";
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code.'-user-callerid',''),"1");
        $arrEXT[]=new paloExtensions($fcode,new ext_read('newlocation','speed-enterlocation'),"n","setloc");
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code.'-speeddial-lookup','${newlocation},${EXTUSER}'),"n","lookup");
        $arrEXT[]=new paloExtensions($fcode,new ext_gotoif('$["${SPEEDDIALNUMBER}"!=""]', 'conflicts'));
        $arrEXT[]=new paloExtensions($fcode,new ext_read('newnum','speed-enternumber'),"n","setnum");
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('speed-dial'));
        $arrEXT[]=new paloExtensions($fcode,new ext_saydigits('${newlocation}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('is-set-to'));
        $arrEXT[]=new paloExtensions($fcode,new ext_saydigits('${newnum}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('is-set-to'));
        $arrEXT[]=new paloExtensions($fcode,new ext_hangup(''));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('speed-dial'),"n","conflicts");
        $arrEXT[]=new paloExtensions($fcode,new ext_saydigits('${newlocation}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('is-in-use'));
        $arrEXT[]=new paloExtensions($fcode,new ext_background('press-1&to-listen-to-it&press-2&to-enter-a-diff&location&press-3&to-change&telephone-number'));
        $arrEXT[]=new paloExtensions($fcode,new ext_waitexten('60'));
        $arrEXT[]=new paloExtensions("1",new ext_playback('speed-dial'),"1");
        $arrEXT[]=new paloExtensions("1",new ext_saydigits('${newlocation}'));
        $arrEXT[]=new paloExtensions("1",new ext_saydigits('${SPEEDDIALNUMBER}'));
        $arrEXT[]=new paloExtensions("1",new ext_goto('conflicts','s'));
        $arrEXT[]=new paloExtensions("2",new ext_goto('setloc','s'));
        $arrEXT[]=new paloExtensions("3",new ext_goto('setnum','s'));
        $arrEXT[]=new paloExtensions("t",new ext_congestion(''));

        $contexto=new paloContexto($this->code,"app-speeddial-set");
        if($contexto===false){
            $contexto->errMsg="app-speeddial-set. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
        }
        return $contexto;
    }

    private function createDialPlanFuntionVM(&$arrFromInt){
        $arrContext=array();
        $arrFeature=array("voicemail_dial","voicemail_mine");
        unset($this->arrFeatureCode);
        $this->setArrFCbyCategory($arrFeature);
        if(is_array($this->arrFeatureCode)){
            foreach($this->arrFeatureCode as $value){
                if($value!=false){
                    //funcion que me devuelve un arreglo con los contexto creados
                    $fname="dialPlanVM_".$value->name;
                    $contexts=$this->$fname($value->getCurrentCode(),$arrFromInt);
                    $arrContext=array_merge($arrContext,$contexts);
                }
            }
        }
        return $arrContext;
    }

    private function dialPlanVM_voicemail_dial($fcode,&$arrFromInt){
        $arrExt=array();
        $arrEXT[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrEXT[]=new paloExtensions($fcode,new ext_wait('1'),"n","start");
        $arrEXT[]=new paloExtensions($fcode,new ext_noop('app-dialvm: Asking for mailbox'));
        $arrEXT[]=new paloExtensions($fcode,new ext_read('MAILBOX', 'vm-login', '', '', 3, 2));
        $arrEXT[]=new paloExtensions($fcode,new ext_noop('app-dialvm: Got Mailbox ${MAILBOX}'),"n","check");
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code.'-get-vmcontext','${MAILBOX}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_vmexists('${MAILBOX}@${VMCONTEXT}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_gotoif('$["${VMBOXEXISTSSTATUS}" = "SUCCESS"]', 'good', 'bad'));
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrEXT[]=new paloExtensions($fcode,new ext_noop('app-dialvm: Good mailbox ${MAILBOX}@${VMCONTEXT}'),"n","good");
        $arrEXT[]=new paloExtensions($fcode,new ext_vmmain('${MAILBOX}@${VMCONTEXT}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_gotoif('$["${IVR_RETVM}" = "RETURN" & "${IVR_CONTEXT}" != ""]','playret'));
        $arrEXT[]=new paloExtensions($fcode,new ext_noop('app-dialvm: BAD mailbox ${MAILBOX}@${VMCONTEXT}'),"n","bad");
        $arrEXT[]=new paloExtensions($fcode,new ext_wait('1'));
        $arrEXT[]=new paloExtensions($fcode,new ext_noop('app-dialvm: Asking for password so people can\'t probe for existence of a mailbox'));
        $arrEXT[]=new paloExtensions($fcode,new ext_read('FAKEPW', 'vm-password', '', '', 3, 2));
        $arrEXT[]=new paloExtensions($fcode,new ext_noop('app-dialvm: Asking for mailbox again'));
        $arrEXT[]=new paloExtensions($fcode,new ext_read('MAILBOX', 'vm-incorrect-mailbox', '', '', 3, 2));
        $arrEXT[]=new paloExtensions($fcode,new ext_goto('check'));
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('beep&you-will-be-transfered-menu&silence/1'),"n",'playret');
        $arrEXT[]=new paloExtensions($fcode,new ext_goto('1','return','${IVR_CONTEXT}'));
        //acces you own voicemail
        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_answer(),"1");
        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_wait('1'),"n","start");
        $length=strlen($fcode);
        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_macro($this->code.'-get-vmcontext','${EXTEN:'.$length.'}'));
        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_vmmain('${EXTEN:'.$length.'}@${VMCONTEXT}'));
        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_gotoif('$["${IVR_RETVM}" = "RETURN" & "${IVR_CONTEXT}" != ""]','${IVR_CONTEXT},return,1'));
        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_macro($this->code.'-hangupcall'));

        $contexto=new paloContexto($this->code,"app-dialvm");
        if($contexto===false){
            $contexto->errMsg="app-dialvm. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
            $arrFromInt[]["name"]="app-dialvm";
        }
        return array($contexto);
    }

    private function dialPlanVM_voicemail_mine($fcode,&$arrFromInt){
        $arrExt=array();
        $arrEXT[]=new paloExtensions($fcode,new ext_answer(),"1");
        $arrEXT[]=new paloExtensions($fcode,new ext_wait('1'),"n","start");
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code.'-user-callerid',''));
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code.'-get-vmcontext','${EXTUSER}'));
        $arrEXT[]=new paloExtensions($fcode,new ext_vmexists('${EXTUSER}@${VMCONTEXT}'),"n","check");
        $arrEXT[]=new paloExtensions($fcode,new ext_gotoif('$["${VMBOXEXISTSSTATUS}" = "SUCCESS"]', 'mbexist'));
        $arrEXT[]=new paloExtensions($fcode,new ext_vmmain(''));
        $arrEXT[]=new paloExtensions($fcode,new ext_gotoif('$["${IVR_RETVM}" = "RETURN" & "${IVR_CONTEXT}" != ""]','playret'));
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrEXT[]=new paloExtensions($fcode,new ext_vmmain('${EXTUSER}@${VMCONTEXT}'),"check+101","mbexist");
        $arrEXT[]=new paloExtensions($fcode,new ext_gotoif('$["${IVR_RETVM}" = "RETURN" & "${IVR_CONTEXT}" != ""]','playret'));
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));
        $arrEXT[]=new paloExtensions($fcode,new ext_playback('beep&you-will-be-transfered-menu&silence/1'),"n","playret");
        $arrEXT[]=new paloExtensions($fcode,new ext_macro($this->code.'-hangupcall'));

        $contexto=new paloContexto($this->code,"app-vmmain");
        if($contexto===false){
            $contexto->errMsg="app-vmmain. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
            $arrFromInt[]["name"]="app-vmmain";
        }
        return array($contexto);
    }

    private function createDialPlanFuntionCore(&$arrFromInt){
        $arrContext=array();
        $arrFeature=array("sim_in_call","direct_call_pickup");
        unset($this->arrFeatureCode);
        $this->setArrFCbyCategory($arrFeature);
        if(is_array($this->arrFeatureCode)){
            foreach($this->arrFeatureCode as $value){
                if($value!=false){
                    //funcion que me devuelve un arreglo con los contexto creados
                    $fname="dialPlanCore_".$value->name;
                    $contexts=$this->$fname($value->getCurrentCode(),$arrFromInt);
                    $arrContext=array_merge($arrContext,$contexts);
                }
            }
        }
        return $arrContext;
    }

    private function dialPlanCore_sim_in_call($fcode,&$arrFromInt){
        $arrExt=array();
        if (ctype_digit($fcode))
            $arrEXT[]=new paloExtensions($fcode,new ext_goto('1', '${EXTEN}', $this->code.'-from-pstn'),"1");
        else
            $arrEXT[]=new paloExtensions($fcode,new ext_goto('1', 's', $this->code.'-from-pstn'),"1");
        $arrEXT[]=new paloExtensions("h",new ext_macro($this->code.'-hangupcall'),"1");

        $contexto=new paloContexto($this->code,"ext-test");
        if($contexto===false){
            $contexto->errMsg="ext-test. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
            $arrFromInt[]["name"]="ext-test";
        }
        return array($contexto);
    }

    private function dialPlanCore_direct_call_pickup($fcode,&$arrFromInt){
        /**
         * This application use asterisk dialplan application Pickup()
         * Permit pickup other extension especified inside the application
         * This application can pickup a specified ringing channel. The channel to pickup can be specified in the following ways.
         * 1) If no extension targets are specified, the application will pickup a channel matching the pickup group of the requesting channel.
         * 2) If the extension is specified with a context of the special string PICKUPMARK (for example 10@PICKUPMARK), the application will pickup a channel which has defined the channel variable PICKUPMARK with the same value as extension (in this example, 10).
         * 3) If the extension is specified with or without a context, the channel with a matching extension and context will be picked up. If no context is specified, the current context will be used.
         */
        
        $arrExt=array();
        $fclen=strlen($fcode);
        $picklist = '${EXTEN:'.$fclen.'}';
        $picklist .= '&'.$this->code.'_${EXTEN:'.$fclen.'}@PICKUPMARK';
        
        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_pickup($picklist),"1");
        $arrEXT[]=new paloExtensions("_".$fcode.".",new ext_hangup(''));
        
        //de aqui se debe obtener una lista de todas las extensions que pertenece a algun grupo de marcado
        //esto se hace para poder contestar las llamadas del grupo de timbrado de dicha extensions
        $rg_members=$this->getAllRingGroups(); //falta crear la funcion que me devuelva este arreglo
        foreach ($rg_members as $exten => $grps) {
            $picklist  = $exten;
            $picklist .= '&'.$this->code.'_'.$exten.'@PICKUPMARK'; 

            foreach ($grps as $grp) {
                $picklist .= '&'.$grp.'@'.$this->code.'from-internal'; 
                $picklist .= '&'.$grp.'@'.$this->code.'from-internal-xfer'; 
                $picklist .= '&'.$grp.'@'.$this->code.'ext-group'; 
            }
            $arrEXT[]=new paloExtensions("_".$fcode.$exten,new ext_pickup($picklist),"1");
            $arrEXT[]=new paloExtensions("_".$fcode.$exten,new ext_hangup(''));
        }

        $contexto=new paloContexto($this->code,"app-pickup");
        if($contexto===false){
            $contexto->errMsg="app-pickup. Error: ".$contexto->errMsg;
        }else{
            $contexto->arrExtensions=$arrEXT;
            $arrFromInt[]["name"]="app-pickup";
        }
        return array($contexto);
    }
    
    private function getAllRingGroups(){
        $rgMembers=array();
        $query="SELECT rg_number,rg_extensions FROM ring_group WHERE organization_domain=?";
        $result=$this->_DB->fetchTable($query,true,array($this->domain));
        if($result===false){
            $this->errMsg=_tr("Error to create app-pickup for ring_groups");
        }else{
            foreach($result as $ring_group){
                $arrExtens=explode("-",$ring_group["rg_extensions"]);
                foreach($arrExtens as $value){
                    //se debria además comprobar que las extensiones existieran?
                    if(preg_match("/^([0-9]+)$/",trim($value))){
                        $rgMembers[$value][]=$ring_group['rg_number'];
                    }
                }
            }
        }
        return $rgMembers;
    }

    function getAllFeaturesCode($domain){
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
            return false;
        }
        
        $query="SELECT f.name, fg.description, fg.default_code,f.code,f.estado from features_code f join features_code_settings fg
        on f.name=fg.name where organization_domain=?";
        $result=$this->_DB->fetchTable($query,true,array($domain));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }

    private function getAllFeaturesCodeSettings(){
        $query="SELECT name,default_code,estado from features_code_settings";
        $result=$this->_DB->fetchTable($query,true);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }

    function getFeaturesCode($domain,$feature){
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
            return false;
        }
        
        $query="SELECT f.name,fg.default_code,f.code,f.estado from features_code f join features_code_settings fg
        on f.name=fg.name where f.organization_domain=? and f.name=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($domain,$feature));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }
}
?>
