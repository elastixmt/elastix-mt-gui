<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
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
  $Id: index.php,v 1.1.1.1 2012/07/30 rocio mera rmera@palosanto.com Exp $ */

include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    
     //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);

    //user credentials
    global $arrCredentials;
        
    $action = getAction();
    $content = "";

    switch($action){
        case "apply":
            $content = applyChanges($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default: // view
            $content = viewGeneralSetting($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}


function viewGeneralSetting($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf){
    global $arrPermission;
    $error = "";
    
    //obtenemos los datos guardados
    $pGP= new paloGeneralPBX($pDB);
    $arrForm = createFieldForm($pGP->getVoicemailTZ());
    $oForm = new paloForm($smarty,$arrForm);
    $arrSettings = $pGP->getGeneralSettings();
    if($arrSettings==false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Error getting default settings. ")._tr($pGP->errMsg));
    }else{
        $arrCustom=array();
        $genSettings=array();
        if(getParameter("save_edit")){
            $genSettings=$_POST;
            foreach(array("sip","iax") as $tech){
                if(is_array($_POST[$tech."_custom_name"])){
                    foreach(array_keys($_POST[$tech."_custom_name"]) as $index){
                        if(!empty($_POST[$tech."_custom_name"][$index]) && isset($_POST[$tech."_custom_val"][$index])){
                            if($_POST[$tech."_custom_val"][$index]!=""){
                                $name=strtolower($_POST[$tech."_custom_name"][$index]);
                                $arrCustom[$tech][]=array("name"=>$name,"value"=>$_POST[$tech."_custom_val"][$index]);
                            }
                        }
                    }
                }
            }
            //nap settings
            if(is_array($_POST['localnetip'])){
                $arrLocalNetIP=$_POST['localnetip'];
                $arrLocalNetMASK=$_POST['localnetmask'];
            }
            
            //codec settings
            $codecsAlllow=array();
            if(is_array($_POST['audioCodec'])){
                foreach($_POST['audioCodec'] as $codec){
                    $codecsAlllow[]=$codec;
                }
            }
            if(is_array($_POST['videoCodec'])){
                foreach($_POST['videoCodec'] as $codec){
                    $codecsAlllow[]=$codec;
                }
            }
            $listCodecs=getListCodecs($pGP,implode(",",$codecsAlllow));
        }else{
            foreach($arrSettings as $tech => $prop){
                foreach($prop as $key => $value){
                    if($value["type"]=="custom")
                        $arrCustom[$tech][]=array("name"=>$key,"value"=>$value["value"]);
                    else
                        $genSettings[$tech."_".$key]=$value["value"];
                }
            }
            
            $listCodecs=getListCodecs($pGP,$genSettings["gen_ALLOW_CODEC"]);
            
            if(isset($genSettings["sip_nat_type"])){
                if($genSettings["sip_nat_type"]!="public"){
                    $res=$pGP->getNatLocalConfing();
                    $arrLocalNetIP=$res["ip"];
                    $arrLocalNetMASK=$res["mask"];
                }
            }
        }
    }
    
    if(!isset($arrCustom["sip"])){
        $arrCustom["sip"][]=array("name"=>"","value"=>"");
    }
    if(!isset($arrCustom["iax"])){
        $arrCustom["iax"][]=array("name"=>"","value"=>"");
    }
    if(!isset($arrLocalNetIP)){
        $arrLocalNetIP=array("");
        $arrLocalNetMASK=array("");
    }
    
    $oForm->setEditMode();
    
    //permission
    $smarty->assign("EDIT_GS",in_array('edit',$arrPermission));

    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE",_tr("Delete"));
    $smarty->assign("GENERAL",_tr("General Settings"));
    $smarty->assign("SIP_GENERAL",_tr("SIP Settings"));
    $smarty->assign("IAX_GENERAL",_tr("IAX Settings"));
    $smarty->assign("VM_GENERAL",_tr("Voicemail Settings"));
    $smarty->assign("QUALIFY",_tr("Qualify Seetings"));
    $smarty->assign("CODEC",_tr("Codec Selections"));
    $smarty->assign("REGIS_TIMERS",_tr("Register Timers"));
    $smarty->assign("OUT_REGIS_TIMERS",_tr("Outbound SIP Registrations"));
    $smarty->assign("RTP_TIMERS",_tr("RTP Timers"));
    $smarty->assign("CODEC",_tr("Codecs Negociation"));
    $smarty->assign("FAX",_tr("T.38 FAX Support"));
    $smarty->assign("NAT",_tr("NAT Support"));
    $smarty->assign("localnetIP",$arrLocalNetIP);
    $smarty->assign("localnetMASK",$arrLocalNetMASK);
    $smarty->assign("MEDIA_HANDLING",_tr("MEDIA HANDLING"));
    $smarty->assign("STATUS_NOTIFICATIONS",_tr("Status Notifications"));
    $smarty->assign("CUSTOM_SET",_tr("Custom Settings"));
    $smarty->assign("VIDEO_OPTS",_tr("Video Support"));
    $smarty->assign("JITTER",_tr("Jitter Buffer Settings"));
    $smarty->assign("EMAIL_VM",_tr("Send Email Settings"));
    $smarty->assign("ADVANCED",_tr("Advance Settings"));
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("sipCustom",$arrCustom["sip"]);
    $smarty->assign("iaxCustom",$arrCustom["iax"]);
    $smarty->assign("LOCATION_VM","Timezones Settings");
    $smarty->assign("audioCodec",$listCodecs["audio"]);
    $smarty->assign("videoCodec",$listCodecs["video"]);
    
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("General Settings"), $genSettings);
//    $mensaje=showMessageReload($module_name, $arrConf, $pDB, $userLevel1, $userAccount);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    return $content;
}

function getListCodecs(&$pGP,$allowCodec){
    //arreglo que contine una lista de los codecs que existen dentro de asterisk
    //esto es solo informativo
    $arrCodecs=$pGP->getCodecsAsterisk();
    //lista de codecs actual permitidos para una tecnolgia separados por ',' en orden de preferencia
    $arrSelected=explode(",",$allowCodec);
    
    $listCodecs=array("audio"=>array(),"video"=>array());
    $listNoSelect=array("audio"=>array(),"video"=>array());
    
    foreach($arrSelected as $codec){
        if(in_array($codec,$arrCodecs["audio"])){
            $listCodecs["audio"][]=array("name"=>$codec,"check"=>"checked");
        }elseif(in_array($codec,$arrCodecs["video"])){
            $listCodecs["video"][]=array("name"=>$codec,"check"=>"checked");
        }
    }
    
    
    //hacer algo pra evitar que se desordene la lista de codigos
    foreach($arrCodecs as $key => $value){
        foreach($value as $codec){
            if(!in_array($codec,$arrSelected)){
                $listCodecs[$key][]=array("name"=>$codec,"check"=>"");
            }
        }
    }
    
    return $listCodecs;
}

function applyChanges($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf){
    $action = "";
    $pORGZ = new paloSantoOrganization($pDB);
    
    $pGP= new paloGeneralPBX($pDB);
    $arrForm = createFieldForm($pGP->getVoicemailTZ());
    $oForm = new paloForm($smarty,$arrForm);
    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr("The following fields contain errors").":</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "{$k} [{$v['mensaje']}], ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
    }else{
        $arrProp=getParameterGeneralSettings();
        $pDB->beginTransaction();
        $exito=$pGP->setGeneralSettings($arrProp);
        if($exito===true){
            $pDB->commit();
            unset($_POST["save_edit"]);
            if(reloadFiles()){    
                $smarty->assign("mb_title", _tr("MESSAGE"));
                $smarty->assign("mb_message",_tr("Changes have been applied successfully."));
            }else{
                $smarty->assign("mb_title", _tr("ERROR"));
                $msg=_tr("Changes couldn't be applied successfully. ");
                $msg .=$pGP->errMsg;
                $smarty->assign("mb_message",$msg);
            }
        }else{
            $pDB->rollBack();
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Changes couldn't be applied. ").$pGP->errMsg);
        }
    }
    
    return viewGeneralSetting($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
}

function reloadFiles(){
    //reescribo el archivo de configuracion correspondiente al modulo
    //verificamos que los modules chan_sip.so,chan_iax2.so,app_voicemail.so esten cargados
    //si estan cargados se realiza un reload del modulo, caso contrario se debe realizar un load del modulo
    $flag=false;
    $actions=array("SIP"=>"chan_sip.so","IAX"=>"chan_iax2.so","VM"=>"app_voicemail.so");
    foreach($actions as $key => $value){
        $sComando = "/usr/bin/elastix-helper asteriskconfig write".$key."GeneralFile 2>&1";
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = "<br/>File ".strtolower($key).".conf couldn't be writen. $key changes won't can take effect in server. ".implode('', $output);
            $flag=true;
            break;
        }   
    }
    if($flag){
        return false;
    }else
        return true;
    
}

function createFieldForm($arrTZ){
    $arrLang=getLanguagePBX();
    $arrFormElements = array("gen_audio_codec"  => array("LABEL"                => _tr("Allowed Audio Codecs"),
                                                "DESCRIPTION"            => _tr("Allow codecs in order of preference"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => "",
                                                "VALIDATION_TYPE"        => "text", //no
                                                "VALIDATION_EXTRA_PARAM" => ""),
                             "gen_video_codec"  => array("LABEL"                => _tr("Allowed Video Codecs"),
                                                "DESCRIPTION"            => _tr("Allow codecs in order of preference"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => "",
                                                "VALIDATION_TYPE"        => "text", //no
                                                "VALIDATION_EXTRA_PARAM" => ""),
                             "gen_LANGUAGE"  => array("LABEL"        => _tr("language"),
                                                "DESCRIPTION"            => _tr("Default language setting for all users/peers"),
                                                "REQUIRED"               => "yes",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrLang,
                                                "VALIDATION_TYPE"        => "text", //all o un conjunto de codecs
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            );
    return $arrFormElements + createSipForm($arrLang) + createIaxForm($arrLang) + createVMForm($arrLang,$arrTZ);
}

function createSipForm($arrLang){
    $arrYesNo=array(_tr("yes")=>_tr("yes"),"no"=>"no");
    $arrDtmf=array('rfc2833'=>'rfc2833','info'=>"info",'shortinfo'=>'shortinfo','inband'=>'inband','auto'=>'auto');
    $arrMedia=array(_tr('yes')=>_tr('yes'),'no'=>'no','nonat'=>'nonat','update'=>'update',"update,nonat"=>"update,nonat","outgoing"=>"outgoing");
    
    $arrFormElements = array("sip_default_context"  => array("LABEL"                  => _tr("Default Context"),
                                                "DESCRIPTION"            => _tr("Default context for incoming calls. We  recomend don't edit this at least you know what you are doing"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text", //default
                                                "VALIDATION_EXTRA_PARAM" => ""),
                             "sip_allowguest"  => array("LABEL"                  => _tr("allowguest"),
                                                "DESCRIPTION"            => _tr(" Allow or reject guest calls (default is yes).\n If your Asterisk is connected to the Internet and you have allowguest=yes you want to check\n which services you offer everyone out there, by enabling them in the default context.\nWe strong recomend you let it as 'no'"),
                                                "REQUIRED"               => "yes",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrYesNo, //no
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                             "sip_allowoverlap"  => array("LABEL"        => _tr("allowoverlap"),
                                                "DESCRIPTION"            => _tr("Disable overlap dialing support. (Default is yes)"),
                                                "REQUIRED"               => "yes",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => array(_tr("yes")=>_tr("yes"),"no"=>"no", "dtmf"=>"dtmf","yes,dtmf"=>"yes,dtmf"), //no
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_allowtransfer" => array("LABEL"         => _tr("allowtransfer"),
                                                "DESCRIPTION"            => _tr("Disable all transfers (unless enabled in peers or users)"),
                                                "REQUIRED"               => "yes",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrYesNo, //yes
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_realm"            => array("LABEL"         => _tr("realm"),
                                                "DESCRIPTION"            => _tr("Realm for digest authenticationRealm for digest authentication defaults to 'asterisk'\n. Realms MUST be globally unique according to RFC 3261;\n Set this to your host name or domain name\n. If you do not how to configure it, set this field with  its default value 'asterisk'"),
                                                "REQUIRED"               => "yes",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => '', //default asterisk
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_transport"    =>  array("LABEL"        => _tr("transport"),
                                                "DESCRIPTION"            => _tr("This sets the default transport type for outgoing.\nIt's also possible to list several supported transport types for the peer by separating them with commas.\nThe order determines the primary default transport.\nThe default transport type is only used for\noutbound messages until a Registration takes place.  During the\npeer Registration the transport type may change to another supported\ntype if the peer requests so.\nThe 'transport' part defaults to 'udp' but may also be 'tcp', 'tls', 'ws', or 'wss'\nIt also can be configured by peer"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => "",
                                                "VALIDATION_TYPE"        => "text", //udp,ws,wss
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_srvlookup" => array("LABEL"         => _tr("srvlookup"),
                                                "DESCRIPTION"            => _tr("Enable DNS SRV lookups on outbound calls Note: Asterisk only uses the first host in SRV records"),
                                                "REQUIRED"               => "yes",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrYesNo, //yes
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_vmexten" => array("LABEL"         => _tr("Exten To Dial VM"),
                                                "DESCRIPTION"            => _tr("dialplan extension to reach mailbox"),
                                                "REQUIRED"               => "",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => "", //*97
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            /*"sip_language" =>  array("LABEL"        => _tr("language"),
                                                "DESCRIPTION"            => _tr("Default language setting for all users/peers"),
                                                "REQUIRED"               => "yes",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrLang,
                                                "VALIDATION_TYPE"        => "text", //all o un conjunto de codecs
                                                "VALIDATION_EXTRA_PARAM" => ""),*/
                            //registration
                            "sip_maxexpiry"  => array("LABEL"         => _tr("maxexpiry"),
                                                "DESCRIPTION"            => _tr("Maximum allowed time of incoming registrations and subscriptions (seconds)"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //3600
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_minexpiry"  => array("LABEL"         => _tr("minexpiry"),
                                                "DESCRIPTION"            => _tr("Minimum length of registrations/subscriptions (default 60)"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //60
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_defaultexpiry" => array("LABEL"         => _tr("defaultexpiry"),
                                                "DESCRIPTION"            => _tr("Default length of incoming/outgoing registration"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //120
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_qualifyfreq"   => array("LABEL"         => _tr("qualifyfreq"),
                                                "DESCRIPTION"            => _tr("Qualification: How often to check for the host to be up in seconds  and reported in milliseconds with sip show settings."),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //60
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_qualifygap"   => array("LABEL"         => _tr("qualifygap"),
                                                "DESCRIPTION"            => _tr("Number of milliseconds between each group of peers being qualified."),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //100
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            //OUTBOUND SIP REGISTRATIONS
                            "sip_registertimeout"    =>  array("LABEL"        => _tr("registertimeout"),
                                                "DESCRIPTION"            => _tr("retry registration calls every # seconds (default 20)"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //20
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                            "sip_registerattempts"    =>  array("LABEL"        => _tr("registerattempts"),
                                                "DESCRIPTION"            => _tr("Number of registration attempts before we give up 0 = continue forever."),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //0
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            //videosupport
                            "sip_videosupport" => array("LABEL"        => _tr("videosupport"),
                                                "DESCRIPTION"            => _tr("Turn on support for SIP video. You need to turn this on in this section to get any video support at all."),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array(_tr("yes")=>_tr("yes"),"no"=>"no", "always"=>"always"),
                                                "VALIDATION_TYPE"        => "text", //no
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_maxcallbitrate"    =>  array("LABEL"        => _tr("maxcallbitrate"),
                                                "DESCRIPTION"            => _tr("Maximum bitrate for video calls (default 384 kb/s)"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //384
                                                "VALIDATION_EXTRA_PARAM" => ""),   
                            //RTP timers
                            "sip_rtptimeout"    =>  array("LABEL"        => _tr("rtptimeout"),
                                                "DESCRIPTION"            => _tr("Terminate call if set # seconds of no RTP or RTCP activity on the audio channel when we're not on hold"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //60
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                            "sip_rtpholdtimeout"    =>  array("LABEL"        => _tr("rtpholdtimeout"),
                                                "DESCRIPTION"            => _tr("Terminate call if set # seconds of no RTP or RTCP activity on the audio channel when we're on hold"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //300
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                            "sip_rtpkeepalive"    =>  array("LABEL"        => _tr("rtpkeepalive"),
                                                "DESCRIPTION"            => _tr(" Send keepalives in the RTP stream to keep NAT open (default is off - zero)"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //0
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                             //T.38 FAX SUPPORT
                            "sip_faxdetect"    =>  array("LABEL"        => _tr("faxdetect"),
                                                "DESCRIPTION"            => _tr("FAX detection will cause the SIP channel to jump to the 'fax' extension (if it exists) based one or more events being detected"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => array(_tr("yes")=>_tr("yes"),"no"=>"no", "cng"=>"cng","t38"=>"t38"),
                                                "VALIDATION_TYPE"        => "text", //yes
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                            "sip_t38pt_udptl"    =>  array("LABEL"        => _tr("t38pt_udptl"),
                                                "DESCRIPTION"            => _tr("Setting this to yes enables T.38 FAX (UDPTL) on SIP calls; it defaults to off"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => array(_tr("yes")=>_tr("yes"), "yes,redundancy"=>_tr("yes,redundancy"), "yes,none"=>_tr("yes,none")),
                                                "VALIDATION_TYPE"        => "text", //yes
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                             //NAT SUPPORT
                             "sip_nat"     =>   array("LABEL"        => _tr("nat"),
                                                "DESCRIPTION"            => _tr("Address NAT-related issues in incoming SIP or media sessions.\nnat = no; Use rport if the remote side says to use it.\nnat = force_rport ; Pretend there was an rport parameter even if there wasn't.\nnat = comedia; Use rport if the remote side says to use it and perform comedia RTP handling.\nnat = auto_force_rport  ; Set the force_rport option if Asterisk detects NAT (default)\nnat = auto_comedia      ; Set the comedia option if Asterisk detects NAT\nNAT settings are a combinable list of options.\n The equivalent of the deprecated nat=yes is nat=force_rport,comedia."),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => "",
                                                "VALIDATION_TYPE"        => "text", //yes
                                                "VALIDATION_EXTRA_PARAM" => ""),
                             "sip_nat_type"     =>   array("LABEL"       => _tr("Type Of Nat"),
                                                "DESCRIPTION"            => _tr("Indicate the type of Configuration if you are using NAT."),
                                                "REQUIRED"               => "yes",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => array(_tr("public")=>_tr("public"),_tr("static")=>_tr("static"),_tr("dynamic")=>_tr("dynamic")),
                                                "VALIDATION_TYPE"        => "text", //yes
                                                "VALIDATION_EXTRA_PARAM" => ""),
                             "sip_localnetip"  =>   array("LABEL"        => _tr("Local Network"),
                                                "DESCRIPTION"            => _tr("List of network addresses that are considered 'inside' of the NATted network.\nIF LOCALNET IS NOT SET, THE EXTERNAL ADDRESS WILL NOT BE SET CORRECTLY.\nMultiple entries are allowed. e.g. a reasonable set is the following:\nlocalnet=192.168.0.0/255.255.0.0 addresses\nlocalnet=10.0.0.0/255.0.0.0"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_localnetmask" =>   array("LABEL"        => _tr(""),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                             "sip_externaddr"    =>   array("LABEL"        => _tr("Extern Addres"),
                                                "DESCRIPTION"            => _tr(" 'externaddr = hostname[:port]' specifies a static address[:port] to be used in SIP and SDP messages.\nThe hostname is looked up only once, when [re]loading sip.conf.Examples:\n externaddr = 12.34.56.78; use this address.\n externaddr = 12.34.56.78:9900; use this address and port.\n externaddr = mynat.my.org:12600   ; Public address of my nat box. "),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text", //address[:port]
                                                "VALIDATION_EXTRA_PARAM" => ""),
                             "sip_externhost"    =>   array("LABEL"        => _tr("Extern Host"),
                                                "DESCRIPTION"            => _tr("'externhost = hostname[:port]' is similar to 'externaddr' except that the hostname is looked up every 'externrefresh' seconds"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text", //hostname[:port]
                                                "VALIDATION_EXTRA_PARAM" => ""),
                             "sip_externrefresh"    =>   array("LABEL"        => _tr("Refresh time"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:50px"),
                                                "VALIDATION_TYPE"        => "text", //120
                                                "VALIDATION_EXTRA_PARAM" => ""),
                             // MEDIA HANDLING
                             "sip_directmedia" => array("LABEL"        => _tr("directmedia"),
                                                "DESCRIPTION"            => _tr("Asterisk by default tries to redirect the RTP media stream to go directly from the caller to the callee.  Some devices do not  support this (especially if one of them is behind a NAT)"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrMedia,
                                                "VALIDATION_TYPE"        => "text", //no
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            //varios
                            "sip_relaxdtmf"    =>  array("LABEL"        => _tr("relaxdtmf"),
                                                "DESCRIPTION"            => _tr("Relax dtmf handling"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text", 
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_trustrpid"    =>  array("LABEL"        => _tr("trustrpid"),
                                                "DESCRIPTION"            => _tr("If Remote-Party-ID should be trusted"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                "VALIDATION_TYPE"        => "text", //yes
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_sendrpid"    =>  array("LABEL"        => _tr("sendrpid"),
                                                "DESCRIPTION"            => _tr("If Remote-Party-ID should be sent"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => array("no"=>"no",_tr("yes")=>_tr("yes"), "pai"=>"pai",_tr("yes,pai")=>_tr("yes,pai")),
                                                "VALIDATION_TYPE"        => "text", //no
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_useragent"    =>  array("LABEL"        => _tr("useragent"),
                                                "DESCRIPTION"            => _tr("If Remote-Party-ID should be sent"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text", //Elastix 3.0
                                                "VALIDATION_EXTRA_PARAM" => ""),    
                            "sip_dtmfmode"    =>  array("LABEL"        => _tr("dtmfmode"),
                                                "DESCRIPTION"            => _tr("Set default dtmfmode for sending DTMF. Default: rfc2833\ninfo : SIP INFO messages (application/dtmf-relay)\nshortinfo : SIP INFO messages (application/dtmf)\ninband : Inband audio (requires 64 kbit codec -alaw, ulaw)\nauto : Use rfc2833 if offered, inband otherwise"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrDtmf,
                                                "VALIDATION_TYPE"        => "text", //Elastix 3.0
                                                "VALIDATION_EXTRA_PARAM" => ""),  
                            //security
                            "sip_contactdeny"   =>  array("LABEL"        => _tr("contactdeny"),
                                                "DESCRIPTION"            => _tr("Use contactpermit and contactdeny to restrict at what IPs your users may register their phones."),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text", //ipv4 ipv6
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_contactpermit" =>  array("LABEL"        => _tr("contactpermit"),
                                                "DESCRIPTION"            => _tr("Use contactpermit and contactdeny to restrict at what IPs your users may register their phones."),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text", //ipv4 ipv6
                                                "VALIDATION_EXTRA_PARAM" => ""),   
                            //STATUS NOTIFICATIONS (SUBSCRIPTIONS)
                            "sip_notifyringing"    =>  array("LABEL"        => _tr("notifyringing"),
                                                "DESCRIPTION"            => _tr("Control whether subscriptions already INUSE get sent RINGING when another call is sent"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                "VALIDATION_TYPE"        => "text", //yes
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                            "sip_notifyhold"    =>  array("LABEL"        => _tr("notifyhold"),
                                                "DESCRIPTION"            => _tr("Notify subscriptions on HOLD state (default: no)"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                "VALIDATION_TYPE"        => "text", //yes
                                                "VALIDATION_EXTRA_PARAM" => ""), 
    );
    return $arrFormElements;
}

function createIaxForm($arrLang){
    $arrTrans=array(_tr("yes")=>_tr("yes"),"no"=>"no","mediaonly"=>"mediaonly");
    $arrYesNo=array(_tr("yes")=>_tr("yes"),"no"=>"no");
    $arrYesNod=array("noset"=>"",_tr("yes")=>_tr("Yes"),"no"=>_tr("No"));
    $arrType=array("friend"=>"friend","user"=>"user","peer"=>"peer");
    $arrCallTok=array(_tr("yes")=>_tr("yes"),"no"=>"no","auto"=>"auto");
    $arrCodecPrio=array("noset"=>"","host"=>"host","caller"=>"caller",_tr("disabled")=>_tr("disabled"),"reqonly"=>"reqonly");
    $encryption=array("noset"=>"","aes128"=>"aes128",_tr("yes")=>_tr("yes"),"no"=>"no");
    $arrFormElements = array("iax_delayreject"     => array("LABEL"                  => _tr("delayreject"),
                                                "DESCRIPTION"            => _tr("For increased security against brute force password attacks enable (delayreject) which will delay the sending of authentication reject for REGREQ or AUTHREP if there is a password"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                "VALIDATION_TYPE"        => "text", //yes
                                                "VALIDATION_EXTRA_PARAM" => ""),
                             "iax_bindport"  => array("LABEL"                  => _tr("Bind Port"),
                                                "DESCRIPTION"            => _tr("Port number to bind and listen for IAX message . Default is 4569. It is recommended to leave this blank"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => "",
                                                "VALIDATION_TYPE"        => "numeric", 
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                             "iax_bindaddr"  => array("LABEL"                  => _tr("Bind Address"),
                                                "DESCRIPTION"            => _tr("IP addres to bind and listen for IAX calls on the BIND Port. The default is to bind to all local addresses.\nMay be specified a specific port: bindaddr=192.168.0.1:4569\nUse to custom settings to specific multiple multiple addresses to bind to,\nbut the first will be the default.\nIt is recommended to leave this blank"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => "",
                                                "VALIDATION_TYPE"        => "ip", 
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                            /*"iax_language" =>  array("LABEL"        => _tr("language"),
                                                "DESCRIPTION"            => _tr("Default language setting for all users/peers"),
                                                "REQUIRED"               => "yes",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrLang,
                                                "VALIDATION_TYPE"        => "text", 
                                                "VALIDATION_EXTRA_PARAM" => ""),*/
                            //codecs
                            "iax_codec"  => array("LABEL"                => _tr("Allow Codecs"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => "",
                                                "VALIDATION_TYPE"        => "text", //no
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                            "iax_bandwidth"  => array("LABEL"            => _tr("Bandwidth"),
                                                "DESCRIPTION"            => _tr("Specify bandwidth of low, medium, or high to control which codecs are used in general"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => array(""=>"","low"=>"low","medium"=>"medium", "high"=>"high"),
                                                "VALIDATION_TYPE"        => "text", //''
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                            "iax_codecpriority"  => array("LABEL"        => _tr("Codec Priority"),
                                                "DESCRIPTION"            => _tr("codecpriority controls the codec negotiation of an inbound IAX call.\n This option is inherited to all user entities.  It can also be defined\n in each user entity separately which will override the setting in general.\n\nThe valid values are:\n caller   - Consider the callers preferred order ahead of the host's.\n host     - Consider the host's preferred order ahead of the caller's.\n disabled - Disable the consideration of codec preference altogether.\n(this is the original behaviour before preferences were added)\n reqonly  - Same as disabled, only do not consider capabilities if\n           the requested format is not available the call will only be accepted if the requested format is available."),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrCodecPrio,
                                                "VALIDATION_TYPE"        => "text", //host
                                                "VALIDATION_EXTRA_PARAM" => ""), 
                            //jitter
                            "iax_jitterbuffer" => array("LABEL"                  => _tr("Jitter Buffer"),
                                                    "DESCRIPTION"            => _tr("The jitter buffer's function is to compensate for varying network delay.\nAll the jitter buffer settings are in milliseconds.\nThe jitter buffer works for INCOMING audio - the outbound audio will be dejittered by the jitter buffer at the other end\nThis enabled or disables jitter buffer at all"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "text", //no
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_forcejitterbuffer"  => array( "LABEL"           => _tr("Force Jitter Buffer"),
                                                    "DESCRIPTION"            => "in the ideal world, when we bridge VoIP channels\nwe don't want to do jitterbuffering on the switch, since the endpoints\ncan each handle this.  However, some endpoints may have poor jitterbuffers\nthemselves, so this option will force * to always jitterbuffer, even in this\ncase",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo, //no
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_maxjitterbuffer"    => array( "LABEL"       => _tr("maxjitterbuffer"),
                                                    "DESCRIPTION"            => "a maximum size for the jitter buffer.\nSetting a reasonable maximum here will prevent the call delay\nfrom rising to silly values in extreme situations; you'll hear\nSOMETHING, even though it will be jittery",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric", //200
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_resyncthreshold"    => array( "LABEL"           => _tr("resyncthreshold"),
                                                    "DESCRIPTION"            => "when the jitterbuffer notices a significant change in delay\nthat continues over a few frames, it will resync, assuming that the change in\ndelay was caused by a timestamping mix-up. The threshold for noticing a\nchange in delay is measured as twice the measured jitter plus this resync\nthreshold.\nResyncing can be disabled by setting this parameter to -1",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric", //1000
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_maxjitterinterps"    => array( "LABEL"          => _tr("maxjitterinterps"),
                                                    "DESCRIPTION"            => _tr("the maximum number of interpolation frames the jitterbuffer\nshould return in a row. Since some clients do not send CNG/DTX frames to\nindicate silence, the jitterbuffer will assume silence has begun after\nreturning this many interpolations. This prevents interpolating throughout a long silence"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric", //10
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            //registration
                            "iax_maxexpiry"  => array("LABEL"         => _tr("maxexpiry"),
                                                "DESCRIPTION"            => _tr("Maximum amounts of time that IAX peers can request as\n a registration expiration interval (in seconds)"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //1300
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_minexpiry"  => array("LABEL"         => _tr("minexpiry"),
                                                "DESCRIPTION"            => _tr("Minimum amounts of time that IAX peers can request as\n a registration expiration interval (in seconds)"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "numeric", //60
                                                "VALIDATION_EXTRA_PARAM" => ""),
    );
    return $arrFormElements;
}

function createVMForm($arrLang,$arrTz)
{
    $arrYesNoU=array("noset"=>"",_tr("yes")=>_tr("YES"),"no"=>"NO");
    if($arrTz===false)
        $arrZoneMessage=array();
    else
        $arrZoneMessage=$arrTz;
        
    $arrFormElements = array(
                             "vm_attach"   => array("LABEL"               => _tr("Email Attachment"),
                                                    "DESCRIPTION"            => _tr("Attach Voicemail's sound file to email."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNoU,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                             "vm_forcename"   => array("LABEL"               => _tr("Force to record name"),
                                                    "DESCRIPTION"            => _tr("Forces a new user to record their name."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNoU,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "vm_maxmsg"   => array("LABEL"               => _tr("Max # of message per Folder"),
                                                    "DESCRIPTION"            => _tr("Maximum messages in a folder (100 if not specified).\nMaximum value for this option is 9999.  If set to 0, a  mailbox will be greetings-only."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_maxlogins"   => array("LABEL"               => _tr("Max # of Login Attempts"),
                                                    "DESCRIPTION"            => _tr("Max number of failed login attempts"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_moveheard"   => array("LABEL"               => _tr("Move Messages to Old"),
                                                    "DESCRIPTION"            => _tr("Move heard messages to the 'Old' folder automatically."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNoU,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "vm_nextaftercmd"   => array("LABEL"               => _tr("nextaftercmd"),
                                                    "DESCRIPTION"            => _tr("Skips to the next message after hitting 7 or 9 to delete/save current message."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNoU,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "vm_saycid"   => array("LABEL"               => _tr("Play CID"),
                                                    "DESCRIPTION"            => _tr("Say the caller id information before the message."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNoU,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "vm_sayduration"   => array("LABEL"               => _tr("Say Duration"),
                                                    "DESCRIPTION"            => _tr("Turn on/off the duration information before the message."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNoU,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "vm_envelope"   => array("LABEL"            => _tr("Play Envelope"),
                                                    "DESCRIPTION"            => _tr("Turn on/off envelope playback before message playback."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNoU,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "vm_tz"   => array("LABEL"               => _tr("Time Zone"),
                                                    "DESCRIPTION"            => _tr("Timezone from zonemessages below. Irrelevant if envelope=no."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrZoneMessage,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_review"   => array("LABEL"               => _tr("Review Message"),
                                                    "DESCRIPTION"            => _tr("Allow sender to review/rerecord their message before saving it [OFF by default]"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNoU,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "vm_operator"   => array("LABEL"               => _tr("Operator"),
                                                    "DESCRIPTION"            => _tr("Allow sender to hit 0 before/after/during leaving a voicemail to; reach an operator"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNoU,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "vm_tempgreetwarn"   => array("LABEL"               => _tr("tempgreetwarn"),
                                                    "DESCRIPTION"            => _tr("Remind the user that their temporary greeting is set"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNoU,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "vm_serveremail"   => array("LABEL"               => _tr("serveremail"),
                                                    "DESCRIPTION"            => _tr("Who the e-mail notification should appear to come from."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_fromstring"   => array("LABEL"               => _tr("From"),
                                                    "DESCRIPTION"            => _tr("Change the From: string"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_emailsubject"   => array("LABEL"               => _tr("Email Subject"),
                                                    "DESCRIPTION"            => _tr("Email subject used at moment to send the email."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:300px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_emailbody"   => array("LABEL"               => _tr("Email Body"),
                                                    "DESCRIPTION"            => _tr("Email Body. Until 512 characters"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXTAREA",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:500px;resize:none"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => "",
                                                    "ROWS"                   => "4",
                                                    "COLS"                   => "1"),
                            "vm_maxsecs"   => array("LABEL"               => _tr("Max length VM in sec"),
                                                    "DESCRIPTION"            => _tr("Maximum length of a voicemail message in seconds."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_minsecs"   => array("LABEL"               => _tr("Min length VM in sec"),
                                                    "DESCRIPTION"            => _tr("Minimum length of a voicemail message in seconds for the message to be kept.\nThe default is no minimum"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_maxgreet"   => array("LABEL"               => _tr("Max length greetings in sec"),
                                                    "DESCRIPTION"            => _tr("Maximum length of greetings in seconds."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_skipms"   => array("LABEL"               => _tr("Skip Message ms"),
                                                    "DESCRIPTION"            => _tr("How many milliseconds to skip forward/back when rew/ff in message playback."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_maxsilence"   => array("LABEL"               => _tr("Max length silence in sec"),
                                                    "DESCRIPTION"            => _tr("How many seconds of silence before we end the recording."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_silencethreshold"   => array("LABEL"         => _tr("Silence threshold"),
                                                    "DESCRIPTION"            => _tr("Silence threshold (what we consider silence: the lower, the more sensitive)."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_volgain"   => array("LABEL"               => _tr("Volume Gain"),
                                                    "DESCRIPTION"            => _tr("Increase DB gain on recorded message by this amount (0.0 means none)."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_forward_urgent_auto"   => array("LABEL"      => _tr("forward_urgent_auto"),
                                                    "DESCRIPTION"            => _tr("Forward an urgent message as an urgent message.  Defaults to no so sender can set the urgency on the envelope of the forwarded message."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNoU,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_externpasscheck"   => array("LABEL"               => _tr("Extern Pass check"),
                                                    "DESCRIPTION"            => _tr("If you would like to have an external program called when a user changes the voicemail password for the purpose of doing validation on the new password, then use this option."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_minpassword"   => array("LABEL"         => _tr("Min Lenght Password"),
                                                    "DESCRIPTION"            => _tr("Enforce minimum password length."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_odbcstorage"   => array("LABEL"               => _tr("odbcstorage"),
                                                    "DESCRIPTION"            => _tr("Voicemail can be stored in a database using the ODBC driver. The value of odbcstorage is the database connection configured in res_odbc.conf."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_odbctable"   => array("LABEL"         => _tr("odbctable"),
                                                    "DESCRIPTION"            => _tr("The default table for ODBC voicemail storage is voicemessages."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_pollmailboxes"   => array("LABEL"               => _tr("pollmailboxes"),
                                                    "DESCRIPTION"            => _tr("If mailboxes are changed anywhere outside of app_voicemail, then this option must be enabled for MWI to work."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_pollfreq"   => array("LABEL"               => _tr("pollfreq"),
                                                    "DESCRIPTION"            => _tr("If the 'pollmailboxes' option is enabled, this option  sets the polling frequency.  The default is once every 30 seconds."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_mailcmd"   => array("LABEL"         => _tr("mailcmd"),
                                                    "DESCRIPTION"            => _tr("You can override the default program to send e-mail if you wish, too."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                        );
    return $arrFormElements;
}

function getParameterGeneralSettings(){
    $arrPropGen["LANGUAGE"]=getParameter("gen_LANGUAGE");
    //codecnegociation
    if(isset($_POST["audioCodec"])){
        if(is_array($_POST["audioCodec"])){
            foreach($_POST["audioCodec"] as $codec){
                $arrPropGen["audioCodec"][]=$codec;
            }
        }
    }
    if(isset($_POST["videoCodec"])){
        if(is_array($_POST["videoCodec"])){
            foreach($_POST["videoCodec"] as $codec){
                $arrPropGen["videoCodec"][]=$codec;
            }
        }
    }
    
    //sip settings
        $arrPropSip["default_context"]=getParameter("sip_default_context");
        $arrPropSip["allowguest"]=getParameter("sip_allowguest");
        $arrPropSip['allowoverlap']=getParameter("sip_allowoverlap");
        $arrPropSip['allowtransfer']=getParameter("sip_allowtransfer");
        //$arrPropSip['realm']=getParameter("sip_realm");
        //no se lo setea porque despues de esto se habaria que generar nuevamente todos
        //las calves de los archivos sip
        $arrPropSip['transport']=getParameter("sip_transport");
        $arrPropSip['srvlookup']=getParameter("sip_srvlookup");
        $arrPropSip['vmexten']=getParameter("sip_vmexten");        
        $arrPropSip["maxexpiry"]=getParameter("sip_maxexpiry");
        $arrPropSip['minexpiry']=getParameter("sip_minexpiry");
        $arrPropSip['defaultexpiry']=getParameter("sip_defaultexpiry");
        $arrPropSip['qualifyfreq']=getParameter("sip_qualifyfreq");
        $arrPropSip['qualifygap']=getParameter("sip_qualifygap");
        $arrPropSip['registertimeout']=getParameter("sip_registertimeout");
        $arrPropSip['registerattempts']=getParameter("sip_registerattempts");
        $arrPropSip['videosupport']=getParameter("sip_videosupport");
        $arrPropSip["maxcallbitrate"]=getParameter("sip_maxcallbitrate");
        $arrPropSip['rtptimeout']=getParameter("sip_rtptimeout");
        $arrPropSip['rtpholdtimeout']=getParameter("sip_rtpholdtimeout");
        $arrPropSip['rtpkeepalive']=getParameter("sip_rtpkeepalive");
        $arrPropSip['faxdetect']=getParameter("sip_faxdetect");
        $arrPropSip['t38pt_udptl']=getParameter("sip_t38pt_udptl");
        $arrPropSip['nat']=getParameter("sip_nat");
        $arrPropSip['directmedia']=getParameter("sip_directmedia");
        $arrPropSip['relaxdtmf']=getParameter("sip_relaxdtmf");
        $arrPropSip['trustrpid']=getParameter("sip_trustrpid");
        $arrPropSip['sendrpid']=getParameter("sip_sendrpid");
        $arrPropSip['useragent']=getParameter("sip_useragent");
        $arrPropSip['dtmfmode']=getParameter("sip_dtmfmode");
        $arrPropSip['contactdeny']=getParameter("sip_contactdeny");
        $arrPropSip['contactpermit']=getParameter("sip_contactpermit");
        $arrPropSip['notifyringing']=getParameter("sip_notifyringing");
        $arrPropSip['notifyhold']=getParameter("sip_notifyhold");
        $arrPropSip['language']=getParameter("gen_LANGUAGE");
        //nat Settings
        $arrPropSip["nat"]=getParameter("sip_nat");
        $arrPropSip["nat_type"]=getParameter("sip_nat_type");
        $arrPropSip["externaddr"]=getParameter("sip_externaddr");
        $arrPropSip["externhost"]=getParameter("sip_externhost");
        $arrPropSip["externrefresh"]=getParameter("sip_externrefresh");
        if(is_array($_POST["localnetip"])){
            $arrPropSip["localnetip"]=$_POST["localnetip"]; //arreglo que contiene los configuraciones de la red local
            $arrPropSip["localnetmask"]=$_POST["localnetmask"];
        }
        if(is_array($_POST["sip_custom_name"]) && is_array($_POST["sip_custom_val"])){
            $arrPropSip["custom_name"]=$_POST["sip_custom_name"];
            $arrPropSip["custom_val"]=$_POST["sip_custom_val"];
        }
    //iax settings
        $arrPropIax['delayreject']=getParameter("iax_delayreject");
        $arrPropIax['bindport']=getParameter("iax_bindport");
        $arrPropIax['bindaddr']=getParameter("iax_bindaddr");
        $arrPropIax['language']=getParameter("gen_LANGUAGE");
        $arrPropIax['codecpriority']=getParameter("iax_codecpriority");
        $arrPropIax['bandwidth']=getParameter("iax_bandwidth");
        $arrPropIax['jitterbuffer']=getParameter("iax_jitterbuffer");
        $arrPropIax['forcejitterbuffer']=getParameter("iax_forcejitterbuffer");
        $arrPropIax['maxjitterbuffer']=getParameter("iax_maxjitterbuffer");
        $arrPropIax['resyncthreshold']=getParameter("iax_resyncthreshold");
        $arrPropIax['maxjitterinterps']=getParameter("iax_maxjitterinterps");
        //custom Parameters
        if(is_array($_POST["iax_custom_name"]) && is_array($_POST["iax_custom_val"])){
            $arrPropIax["custom_name"]=$_POST["iax_custom_name"];
            $arrPropIax["custom_val"]=$_POST["iax_custom_val"];
        }
    //voicemail settings
        $arrPropVM["attach"]=getParameter("vm_attach");
        $arrPropVM["forcename"]=getParameter("vm_forcename");
        $arrPropVM["envelope"]=getParameter("vm_envelope");
        $arrPropVM["maxmsg"]=getParameter("vm_maxmsg");
        $arrPropVM["maxlogins"]=getParameter("vm_maxlogins");
        $arrPropVM["moveheard"]=getParameter("vm_moveheard");
        $arrPropVM["operator"]=getParameter("vm_operator");
        $arrPropVM["review"]=getParameter("vm_review");
        $arrPropVM["saycid"]=getParameter("vm_saycid");
        $arrPropVM["sayduration"]=getParameter("vm_sayduration");
        $arrPropVM["saydurationm"]=getParameter("vm_saydurationm");
        $arrPropVM["tempgreetwarn"]=getParameter("vm_tempgreetwarn");
        $arrPropVM["serveremail"]=getParameter("vm_serveremail");
        $arrPropVM["fromstring"]=getParameter("vm_fromstring");
        $arrPropVM["emailsubject"]=getParameter("vm_emailsubject");
        $arrPropVM["emailbody"]=getParameter("vm_emailbody");
        $arrPropVM["tz"]=getParameter("vm_tz");
        $arrPropVM['language']=getParameter('gen_LANGUAGE');
        $arrPropVM['volgain']=getParameter("vm_volgain");
        $arrPropVM["maxsecs"]=getParameter("vm_maxsecs");
        $arrPropVM["minsecs"]=getParameter("vm_minsecs");
        $arrPropVM["maxgreet"]=getParameter("vm_maxgreet");
        $arrPropVM["skipms"]=getParameter("vm_skipms");
        $arrPropVM["maxsilence"]=getParameter("vm_maxsilence");
        $arrPropVM["silencethreshold"]=getParameter("vm_silencethreshold");
        $arrPropVM["forward_urgent_auto"]=getParameter("vm_forward_urgent_auto");
        $arrPropVM["externpasscheck"]=getParameter("vm_externpasscheck");
        $arrPropVM["minpassword"]=getParameter("vm_minpassword");
        $arrPropVM["odbcstorage"]=getParameter("vm_odbcstorage");
        $arrPropVM["odbctable"]=getParameter("vm_odbctable");
        $arrPropVM["mailcmd"]=getParameter("vm_mailcmd");
        $arrPropVM["pollmailboxes"]=getParameter("vm_pollmailboxes");
        $arrPropVM["pollfreq"]=getParameter("vm_pollfreq");
    return array("gen"=>$arrPropGen,"sip"=>$arrPropSip,"iax"=>$arrPropIax,"vm"=>$arrPropVM);
}

function getAction(){
    global $arrPermission;
    if(getParameter("save_edit"))
        return (in_array('edit',$arrPermission))?"apply":'view';
    else
        return "view"; //cancel
}
?>
