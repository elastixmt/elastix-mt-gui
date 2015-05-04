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

include_once "libs/paloSantoJSON.class.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoOrganization.class.php";
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
        case "reloadAasterisk":
            $content = reloadAasterisk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "apply":
            $content = applyChanges($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default: // view
            $content = viewGeneralSetting($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function showMessageReload($module_name, &$pDB, $credentials){
    $pAstConf=new paloSantoASteriskConfig($pDB);
    $params=array();
    $msgs="";

    $query = "SELECT domain, id from organization";
    //si es superadmin aparece un link por cada organizacion que necesite reescribir su plan de marcado
    if($credentials["userlevel"]!="superadmin"){
        $query .= " where id=?";
        $params[]=$credentials["id_organization"];
    }

    $mensaje=_tr("Click here to reload dialplan");
    $result=$pDB->fetchTable($query,false,$params);
    if(is_array($result)){
        foreach($result as $value){
            if($value[1]!=1){
                $showmessage=$pAstConf->getReloadDialplan($value[0]);
                if($showmessage=="yes"){
                    $append=($credentials["userlevel"]=="superadmin")?" $value[0]":"";
                    $msgs .= "<div id='msg_status_$value[1]' class='mensajeStatus'><a href='?menu=$module_name&action=reloadAsterisk&organization_id=$value[1]'/><b>".$mensaje.$append."</b></a></div>";
                }
            }
        }
    }
    return $msgs;
}

function viewGeneralSetting($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    global $arrPermission;
    $error = "";
    $pORGZ = new paloSantoOrganization($pDB);

    if($credentials['userlevel']=='superadmin'){
        $domain=getParameter('organization');
        $tmpORG=$pORGZ->getOrganization(array());
        $arrOrgz=array();
        foreach($tmpORG as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
        if(count($arrOrgz)>0){
            if(!isset($arrOrgz[$domain])){
                $domain=$tmpORG[0]["domain"];
            }
        }else{
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("It's necesary you create at least one organization so you can use this module"));
            return '';
        }
    }else{
        $domain=$credentials['domain'];
    }
    
    $pGPBX = new paloGlobalsPBX($pDB,$domain);
    $arrTone = $pGPBX->getToneZonePBX();
    $arrMOH = $pGPBX->getMoHClass($domain);
    $arrForm = createFieldForm($arrTone,$arrMOH,$pGPBX->getVoicemailTZ());
    $oForm = new paloForm($smarty,$arrForm);
    $arrSettings = $pGPBX->getGeneralSettings();
    if($arrSettings==false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Error getting default settings. ")._tr($pGPBX->errMsg));
    }else{
        if(getParameter("save_edit")){
            $arrSettings=$_POST;
        }
    }
    
    if($credentials['userlevel']=='superadmin'){
        $HTML='<select name="organization" id="organization" onchange="javascript:submit();">';
        foreach($arrOrgz as $key => $value){
            $seleted='';
            if($key==$domain)
                $seleted='selected="selected"';
            $value=htmlentities($value,ENT_QUOTES,"UTF-8");
            $key=htmlentities($key,ENT_QUOTES,"UTF-8");
            $HTML .='<option label="'.$value.'" value="'.$key.'" '.$seleted.'>'.$value.'</option>';
        }
        $HTML .='</select>';
        $HTML .='<input type="button" name="select_org" value="Organization" class="neo-table-action">';
        $smarty->assign("SELECT_ORG",$HTML);
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
    $smarty->assign("SIP_GENERAL",_tr("Sip Settings"));
    $smarty->assign("IAX_GENERAL",_tr("Iax Settings"));
    $smarty->assign("VM_GENERAL",_tr("Voicemail Settings"));
    $smarty->assign("DIAL_OPTS",_tr("Dial Options"));
    $smarty->assign("CALL_RECORDING",_tr("Call Recording"));
    $smarty->assign("LOCATIONS",_tr("Locations"));
    $smarty->assign("DIRECTORY_OPTS",_tr("Directory Options"));
    $smarty->assign("EXT_OPTS",_tr("Create User Options"));
    $smarty->assign("QUALIFY",_tr("Qualify Seetings"));
    $smarty->assign("CODEC",_tr("Codec Selections"));
    $smarty->assign("RTP_TIMERS",_tr("RTP Timers"));
    $smarty->assign("VIDEO_OPTS",_tr("Video Support"));
    $smarty->assign("MOH",_tr("Music on Hold"));
    $smarty->assign("JITTER",_tr("Jitter Buffer Settings"));
    $smarty->assign("GENERAL_VM",_tr("Voicemail Gneral Settings"));
    $smarty->assign("VMX_OPTS",_tr("Voicemail VMX Locator"));
    $smarty->assign("OTHER",_tr("Advande Settings"));
    $smarty->assign("CONTEXT",_tr("context"));
    $smarty->assign("USERLEVEL",$credentials['userlevel']);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("General Settings"), $arrSettings);
    $mensaje=showMessageReload($module_name, $pDB, $credentials);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$mensaje.$htmlForm."</form>";
    return $content;
}


function applyChanges($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $action = "";
    //conexion elastix.db
    $pORGZ = new paloSantoOrganization($pDB);
    
    if($credentials['userlevel']=='superadmin'){
        $domain=getParameter('organization');
        $tmpORG=$pORGZ->getOrganization(array());
        $arrOrgz=array();
        foreach($tmpORG as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
        if(count($arrOrgz)>0){
            if(!isset($arrOrgz[$domain])){
                $smarty->assign("mb_title", _tr("ERROR"));
                $smarty->assign("mb_message",_tr("Organization doesn't exist"));
                return viewGeneralSetting($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
            }
        }else{
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("It's necesary you create at least one organization so you can use this module"));
            return viewGeneralSetting($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{
        $domain=$credentials['domain'];
    }
    
    $pGPBX = new paloGlobalsPBX($pDB,$domain);
    $arrTone = $pGPBX->getToneZonePBX();
    $arrMOH = $pGPBX->getMoHClass($domain);
    $arrForm = createFieldForm($arrTone,$arrMOH,$pGPBX->getVoicemailTZ());
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
        $exito=$pGPBX->setGeneralSettings($arrProp);
        if($exito===true){
            $pDB->commit();
            $smarty->assign("mb_title", _tr("MESSAGE"));
            $smarty->assign("mb_message",_tr("Changes applied successfully. "));
            //mostramos el mensaje para crear los archivos de ocnfiguracion
            $pAstConf=new paloSantoASteriskConfig($pDB);
            $pAstConf->setReloadDialplan($domain,true);
        }else{
            $pDB->rollBack();
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Changes couldn't be applied. ").$pGPBX->errMsg);
        }
    }
        
    return viewGeneralSetting($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function getParameterGeneralSettings(){
    //general settings
        $arrPropGen["DIAL_OPTIONS"]=getParameter("DIAL_OPTIONS");
        $arrPropGen["TRUNK_OPTIONS"]=getParameter("TRUNK_OPTIONS");
        $arrPropGen["RECORDING_STATE"]=getParameter("RECORDING_STATE");
        $arrPropGen["MIXMON_FORMAT"]=getParameter("MIXMON_FORMAT");
        $arrPropGen["RINGTIMER"]=getParameter("RINGTIMER");
        if(isset($arrPropGen["RINGTIMER"])){
            if($arrPropGen["RINGTIMER"]==0)
                $arrPropGen["RINGTIMER"]=="";
        }
        $arrPropGen["TONEZONE"]=getParameter("TONEZONE");
        $arrPropGen["LANGUAGE"]=getParameter("LANGUAGE");
        $arrPropGen["DIRECTORY"]=getParameter("DIRECTORY");
        $arrPropGen["DIRECTORY_OPT_EXT"]=getParameter("DIRECTORY_OPT_EXT");
        $arrPropGen["CREATE_VM"]=getParameter("CREATE_VM");
        $arrPropGen["VM_PREFIX"]=getParameter("VM_PREFIX");
        $arrPropGen["VM_DDTYPE"]=getParameter("VM_DDTYPE");
        $arrPropGen["VM_GAIN"]=getParameter("VM_GAIN");
        $arrPropGen["VM_OPTS"]=getParameter("VM_OPTS");
        $arrPropGen["OPERATOR_XTN"]=getParameter("OPERATOR_XTN");
        $arrPropGen["VMX_CONTEXT"]=getParameter("VMX_CONTEXT");
        $arrPropGen["VMX_PRI"]=getParameter("VMX_PRI");
        $arrPropGen["VMX_TIMEDEST_CONTEXT"]=getParameter("VMX_TIMEDEST_CONTEXT");
        $arrPropGen["VMX_TIMEDEST_EXT"]=getParameter("VMX_TIMEDEST_EXT");
        $arrPropGen["VMX_TIMEDEST_PRI"]=getParameter("VMX_TIMEDEST_PRI");
        $arrPropGen["VMX_LOOPDEST_CONTEXT"]=getParameter("VMX_LOOPDEST_CONTEXT");
        $arrPropGen["VMX_LOOPDEST_EXT"]=getParameter("VMX_LOOPDEST_EXT");
        $arrPropGen["VMX_LOOPDEST_PRI"]=getParameter("VMX_LOOPDEST_PRI");
        $arrPropGen["VMX_OPTS_TIMEOUT"]=getParameter("VMX_OPTS_TIMEOUT");
        $arrPropGen["VMX_OPTS_LOOP"]=getParameter("VMX_OPTS_LOOP");
        $arrPropGen["VMX_OPTS_DOVM"]=getParameter("VMX_OPTS_DOVM");
        $arrPropGen["VMX_TIMEOUT"]=getParameter("VMX_TIMEOUT");
        $arrPropGen["VMX_REPEAT"]=getParameter("VMX_REPEAT");
        $arrPropGen["VMX_LOOPS"]=getParameter("VMX_LOOPS");
    //sip settings
        $arrPropSip["context"]=getParameter("sip_context");
        $arrPropSip['dtmfmode']=getParameter("sip_dtmfmode");
        $arrPropSip['host']=getParameter("sip_host");
        $arrPropSip['type']=getParameter("sip_type");
        $arrPropSip['port']=getParameter("sip_port");
        $arrPropSip['qualify']=getParameter("sip_qualify");
        $arrPropSip['nat']=getParameter("sip_nat");
        $arrPropSip['disallow']=getParameter("sip_disallow");
        $arrPropSip['allow']=getParameter("sip_allow");
        $arrPropSip['allowtransfer']=getParameter("sip_allowtransfer");
        $arrPropSip["vmexten"]=getParameter("sip_vmexten");
        $arrPropSip['mohinterpret']=getParameter("sip_mohinterpret");
        $arrPropSip['mohsuggest']=getParameter("sip_mohsuggest");
        $arrPropSip['directmedia']=getParameter("sip_directmedia");
        $arrPropSip['callcounter']=getParameter("sip_callcounter");
        $arrPropSip['busylevel']=getParameter("sip_busylevel");
        $arrPropSip['trustrpid']=getParameter("sip_trustrpid");
        $arrPropSip['sendrpid']=getParameter("sip_sendrpid");
        $arrPropSip['transport']=getParameter("sip_transport");
        $arrPropSip['videosupport']=getParameter("sip_videosupport");
        $arrPropSip['qualifyfreq']=getParameter("sip_qualifyfreq");
        $arrPropSip['rtptimeout']=getParameter("sip_rtptimeout");
        $arrPropSip['rtpholdtimeout']=getParameter("sip_rtpholdtimeout");
        $arrPropSip['rtpkeepalive']=getParameter("sip_rtpkeepalive");
        $arrPropSip['progressinband']=getParameter("sip_progressinband");
        $arrPropSip['g726nonstandard']=getParameter("sip_g726nonstandard");
        $arrPropSip['callingpres']=getParameter("sip_callingpres");
        $arrPropSip['language']=getParameter("LANGUAGE");
    //iax settings
        $arrPropIax["context"]=getParameter("iax_context");
        $arrPropIax['host']=getParameter("iax_host");
        $arrPropIax['type']=getParameter("iax_type");
        $arrPropIax['port']=getParameter("iax_port");
        $arrPropIax['qualify']=getParameter("iax_qualify");
        $arrPropIax['disallow']=getParameter("iax_disallow");
        $arrPropIax['allow']=getParameter("iax_allow");
        $arrPropIax['transfer']=getParameter("iax_transfer");
        $arrPropIax['requirecalltoken']=getParameter("iax_requirecalltoken");
        $arrPropIax['defaultip']=getParameter("iax_defaultip");
        $arrPropIax['mask']=getParameter("iax_mask");
        $arrPropIax['mohinterpret']=getParameter("iax_mohinterpret");
        $arrPropIax['mohsuggest']=getParameter("iax_mohsuggest");
        $arrPropIax['jitterbuffer']=getParameter("iax_jitterbuffer");
        $arrPropIax['forcejitterbuffer']=getParameter("iax_forcejitterbuffer");
        $arrPropIax['codecpriority']=getParameter("iax_codecpriority");
        $arrPropIax['qualifysmoothing']=getParameter("iax_qualifysmoothing");
        $arrPropIax['qualifyfreqok']=getParameter("iax_qualifyfreqok");
        $arrPropIax['qualifyfreqnotok']=getParameter("iax_qualifyfreqnotok");
        $arrPropIax['encryption']=getParameter("iax_encryption");
        $arrPropIax['sendani']=getParameter("iax_sendani");
        $arrPropIax['adsi']=getParameter("iax_adsi");
        $arrPropIax['language']=getParameter("LANGUAGE");
    //voicemail settings
        $arrPropVM["attach"]=getParameter("vm_attach");
        $arrPropVM["maxmsg"]=getParameter("vm_maxmsg");
        $arrPropVM["saycid"]=getParameter("vm_saycid");
        $arrPropVM["sayduration"]=getParameter("vm_sayduration");
        $arrPropVM["envelope"]=getParameter("vm_envelope");
        $arrPropVM["context"]=getParameter("vm_context");
        $arrPropVM["tz"]=getParameter("vm_tz");
        $arrPropVM["emailsubject"]=getParameter("vm_emailsubject");
        $arrPropVM["emailbody"]=getParameter("vm_emailbody");
        $arrPropVM["review"]=getParameter("vm_review");
        $arrPropVM["operator"]=getParameter("vm_operator");
        $arrPropVM["forcename"]=getParameter("vm_forcename");
        $arrPropVM["forcegreetings"]=getParameter("vm_forcegreetings");
        $arrPropVM['language']=getParameter("LANGUAGE");
        $arrPropVM['volgain']=getParameter("VM_GAIN");
    return array("gen"=>$arrPropGen,"sip"=>$arrPropSip,"iax"=>$arrPropIax,"vm"=>$arrPropVM);
}

function createFieldForm($arrTone,$arrMOH,$arrZoneMessage)
{
    $arrRCstat=array("ENABLED"=>_tr("Enabled"),"DISABLED"=>_tr("Disabled"));
    $arrRings=array(""=>_tr("Default")) + range(1,120);
    
    //TODO: obtener la lista de codecs de audio soportados por el servidor
    //se los puede hacer con el comando en consola de asterisk "module show like format" or "core show codecs audio"
    //por ahora se pone los que vienes con la instalacion de asterisk
    $arrRCFormat=array("WAV"=>"WAV","wav"=>"wav","ulaw"=>"ulaw","alaw"=>"alaw","sln"=>"sln","gsm"=>"gsm","g729"=>"g729");
    $arrYesNO=array(_tr("yes")=>_tr("YES"),"no"=>"NO");
    $arrLng=getLanguagePBX();
    
    $arrFormElements = array("DIAL_OPTIONS" => array("LABEL"                  => _tr('Asterisk Dial Options'),
                        						    "DESCRIPTION"            => _tr("GS_asteriskdialoptions"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:80px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "TRUNK_OPTIONS" => array("LABEL"                  => _tr('Asterisk Dial Options in Trunk'),
                        						    "DESCRIPTION"            => _tr("GS_asteriskdialoptionsintrunk"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:80px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "RECORDING_STATE" => array("LABEL"                  => _tr('Enabled/Disabled Call Recording'),
                        						    "DESCRIPTION"            => _tr("GS_enable/disablecall"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrRCstat,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "MIXMON_FORMAT" => array("LABEL"                  => _tr('Call Recording Format'),
                        						    "DESCRIPTION"            => _tr("GS_callrecordingformat"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrRCFormat,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "RINGTIMER" => array("LABEL"                  => _tr('Ringtime before Voicemail'),
                        						    "DESCRIPTION"            => _tr("GS_ringtimebeforevoicemail"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrRings,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "TONEZONE" => array("LABEL"        => _tr('Country Tonezone'),
                        						    "DESCRIPTION"            => _tr("GS_countrytonezone"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrTone,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "LANGUAGE" => array("LABEL"            => _tr('Language'),
                        						    "DESCRIPTION"            => _tr("GS_language"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrLng,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "DIRECTORY" => array("LABEL"        => _tr('Search in Directory by'),
                        						    "DESCRIPTION"            => _tr("GS_searchdirectoryby"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array(_tr("first")=>_tr("surname"),_tr("last")=>_tr("first name"),_tr("both")=>_tr("both")),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "DIRECTORY_OPT_EXT" => array("LABEL"            => _tr('Say Extension with name'),
                        						    "DESCRIPTION"            => _tr("GS_sayextensionwithname"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array("e" => _tr("Yes"), "" => "No"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "CREATE_VM" => array("LABEL"            => _tr('Create Voicemail with extension'),
                        						    "DESCRIPTION"            => _tr("GS_createvoicemailwithextension"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNO,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                        );
    $arrFormElements = array_merge(createSipForm($arrMOH),$arrFormElements);
    $arrFormElements = array_merge(createIaxForm(),$arrFormElements);
    $arrFormElements = array_merge(createVMForm($arrZoneMessage),$arrFormElements);
    return $arrFormElements;
}

function createSipForm($arrMOH){
    $arrCallingpres=array(""=>"",'allowed_not_screened'=>'allowed_not_screened','allowed_passed_screen'=>'allowed_passed_screen','allowed_failed_screen'=>'allowed_failed_screen','allowed'=>'allowed','prohib_not_screened'=>'prohib_not_screened','prohib_passed_screen'=>'prohib_passed_screen','prohib_failed_screen'=>'prohib_failed_screen','prohib'=>'prohib');
    $arrYesNo=array("yes"=>_tr("yes"),"no"=>"no");
    $arrYesNod=array("noset"=>"",_tr("yes")=>_tr("Yes"),"no"=>_tr("No"));
    $arrType=array(_tr("friend")=>_tr("friend"),_tr("user")=>_tr("user"),"peer"=>"peer");
    $arrDtmf=array('rfc2833'=>'rfc2833','info'=>"info",'shortinfo'=>'shortinfo','inband'=>'inband','auto'=>'auto');
    $arrMedia=array("noset"=>"",_tr('yes')=>_tr('yes'),'no'=>'no','nonat'=>'nonat','update'=>'update',"update,nonat"=>"update,nonat","outgoing"=>"outgoing");
    
    $arrMusic=array(""=>"");
    foreach($arrMOH as $key => $value){
        $arrMusic[$key]=$value;
    }
    
    $arrFormElements = array("sip_type"  => array("LABEL"                  => _tr("type"),
                        						"DESCRIPTION"            => _tr("GS_type"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrType,
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_context"  => array("LABEL"                  => _tr("context"),
                        						"DESCRIPTION"            => _tr("GS_context"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_host"   => array("LABEL"                  => _tr("host"),
                        						"DESCRIPTION"            => _tr("GS_host"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_port"   => array("LABEL"                  => _tr("port"),
                        						"DESCRIPTION"            => _tr("GS_port"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_qualify"       => array("LABEL"           => _tr("qualify"),
                        						"DESCRIPTION"            => _tr("GS_qualify"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_allow"   => array("LABEL"                  => _tr("allow"),
                        						"DESCRIPTION"            => _tr("GS_allow"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_disallow"   => array("LABEL"                  => _tr("disallow"),
                        						"DESCRIPTION"            => _tr("GS_disallow"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_nat"  => array("LABEL"                  => _tr("nat"),
                                                "DESCRIPTION"            => _tr("Address NAT-related issues in incoming SIP or media sessions.\nnat = no; Use rport if the remote side says to use it.\nnat = force_rport ; Pretend there was an rport parameter even if there wasn't.\nnat = comedia; Use rport if the remote side says to use it and perform comedia RTP handling.\nnat = auto_force_rport  ; Set the force_rport option if Asterisk detects NAT (default)\nnat = auto_comedia      ; Set the comedia option if Asterisk detects NAT\nNAT settings are a combinable list of options.\n The equivalent of the deprecated nat=yes is nat=force_rport,comedia."),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => "",
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_dtmfmode"   => array( "LABEL"                  => _tr("dtmfmode"),
                        					    	"DESCRIPTION"            => _tr("GS_dtmfmode"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrDtmf,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_vmexten" => array("LABEL"             => _tr("vmexten"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_allowtransfer"   => array( "LABEL"              => _tr("allowtransfer"),
                            						"DESCRIPTION"            => _tr("GS_allowtransfer"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                            "sip_directmedia"   => array( "LABEL"              => _tr("directmedia"),
                            						"DESCRIPTION"            => _tr("GS_directmedia"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrMedia,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_busylevel" => array("LABEL"             => _tr("busylevel"),
                            						"DESCRIPTION"            => _tr("GS_busylevel"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_callcounter"   => array( "LABEL"              => _tr("callcounter"),
                            						"DESCRIPTION"            => _tr("GS_callcounter"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNod,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "sip_callingpres"   => array( "LABEL"              => _tr("callingpres"),
                            						"DESCRIPTION"            => _tr("GS_callingpres"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrCallingpres,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_videosupport"   => array( "LABEL"              => _tr("videosupport"),
                            						"DESCRIPTION"            => _tr("GS_videosupport"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNod,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "sip_maxcallbitrate" => array("LABEL"             => _tr("maxcallbitrate"),
                            						"DESCRIPTION"            => _tr("GS_maxcallbitrate"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_qualifyfreq" => array("LABEL"             => _tr("qualifyfreq"),
                            						"DESCRIPTION"            => _tr("GS_qualifyfreq"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_rtptimeout" => array("LABEL"             => _tr("rtptimeout"),
                            						"DESCRIPTION"            => _tr("GS_rtptimeout"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_rtpholdtimeout" => array("LABEL"             => _tr("rtpholdtimeout"),
                            						"DESCRIPTION"            => _tr("GS_rtpholdtimeout"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_rtpkeepalive" => array("LABEL"             => _tr("rtpkeepalive"),
                            						"DESCRIPTION"            => _tr("GS_rtpkeepalive"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_progressinband" => array("LABEL"             => _tr("progressinband"),
                            						"DESCRIPTION"            => _tr("GS_progressinband"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_g726nonstandard" => array("LABEL"             => _tr("g726nonstandard"),
                            						"DESCRIPTION"            => _tr("GS_g726nonstandard"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_mohinterpret"   => array( "LABEL"              => _tr("mohinterpret"),
                            						"DESCRIPTION"            => _tr("GS_mohinterpret"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrMusic,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_mohsuggest"   => array( "LABEL"              => _tr("mohsuggest"),
                            						"DESCRIPTION"            => _tr("GS_mohsuggest"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrMusic,
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
                                                "INPUT_EXTRA_PARAM"      => array("no"=>"no","yes"=>_tr("yes"), "pai"=>"pai","yes,pai"=>"yes,pai"),
                                                "VALIDATION_TYPE"        => "text", //no
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "sip_transport"    =>  array("LABEL"        => _tr("transport"),
                                                "DESCRIPTION"            => _tr("This sets the default transport type for outgoing.\nIt's also possible to list several supported transport types for the peer by separating them with\ncommas.\nThe order determines the primary default transport.\nThe default transport type is only used for\noutbound messages until a Registration takes place.  During the\npeer Registration the transport type may change to another supported\ntype if the peer requests so\n.The 'transport' part defaults to 'udp' but may also be 'tcp', 'tls', 'ws', or 'wss'\n"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => "",
                                                "VALIDATION_TYPE"        => "text", //no
                                                "VALIDATION_EXTRA_PARAM" => ""),
    );
    return $arrFormElements;
}

function createIaxForm(){
    $arrTrans=array(_tr("yes")=>_tr("yes"),"no"=>"no","mediaonly"=>"mediaonly");
    $arrYesNo=array(_tr("yes")=>_tr("Yes"),"no"=>_tr("No"));
    $arrYesNod=array("noset"=>"","yes"=>_tr("Yes"),"no"=>_tr("No"));
    $arrType=array("friend"=>"friend","user"=>"user","peer"=>"peer");
    $arrCallTok=array(_tr("yes")=>_tr("yes"),"no"=>"no","auto"=>"auto");
    $arrCodecPrio=array("noset"=>"","host"=>"host","caller"=>"caller",_tr("disabled")=>_tr("disabled"),"reqonly"=>"reqonly");
    $encryption=array("noset"=>"","aes128"=>"aes128",_tr("yes")=>_tr("yes"),"no"=>"no");
    $arrFormElements = array("iax_transfer"  => array("LABEL"                  => _tr("transfer"),
                            				    "DESCRIPTION"            => _tr("GS_transferIax"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrTrans,
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_type"  => array("LABEL"                  => _tr("type"),
                            			        "DESCRIPTION"            => _tr("GS_typeIax"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrType,
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_context"  => array("LABEL"                  => _tr("context"),
                            					"DESCRIPTION"            => _tr("GS_contextIax"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_host"   => array("LABEL"                  => _tr("host"),
                            					"DESCRIPTION"            => _tr("GS_hostIax"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_port"   => array("LABEL"                  => _tr("port"),
                            				    "DESCRIPTION"            => _tr("GS_portIax"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_qualify"=> array("LABEL"           => _tr("qualify"),
                            				    "DESCRIPTION"            => _tr("GS_qualifyIax"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_allow"   => array("LABEL"                  => _tr("allow"),
                            				    "DESCRIPTION"            => _tr("GS_allowIax"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_disallow"   => array("LABEL"                  => _tr("disallow"),
                            				    "DESCRIPTION"            => _tr("GS_disallowIax"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_requierecalltoken" => array("LABEL"             => _tr("requierecalltoken"),
                            				    "DESCRIPTION"            => _tr("GS_requierecalltokenIax"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrCallTok,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_mask"     => array("LABEL"                   => _tr("mask"),
                            				    "DESCRIPTION"            => _tr("GS_maskIax"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_mohinterpret"   => array( "LABEL"                  => _tr("mohinterpret"),
                                				    "DESCRIPTION"            => _tr("GS_mohinterpretIax"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_mohsuggest" => array("LABEL"             => _tr("mohsuggest"),
                            				        "DESCRIPTION"            => _tr("GS_mohsuggestIax"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_timezone"   => array( "LABEL"                  => _tr("timezone"),
                                				    "DESCRIPTION"            => _tr("GS_timezone"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_sendani" => array("LABEL"             => _tr("sendani"),
                                				    "DESCRIPTION"            => _tr("GS_sendaniIax"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNod,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "iax_adsi" => array("LABEL"             => _tr("adsi"),
                                				    "DESCRIPTION"            => _tr("GS_adsiIax"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNod,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "iax_encryption" => array("LABEL"             => _tr("encryption"),
                                				    "DESCRIPTION"            => _tr("GS_encrytionIax"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $encryption,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_jitterbuffer" => array("LABEL"             => _tr("jitterbuffer"),
                                				    "DESCRIPTION"            => _tr("GS_jitterbufferIax"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNod,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "iax_forcejitterbuffer" => array("LABEL"             => _tr("forcejitterbuffer"),
                                				    "DESCRIPTION"            => _tr("GS_forcejitterbufferIax"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNod,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "iax_codecpriority" => array("LABEL"             => _tr("codecpriority"),
                                				    "DESCRIPTION"            => _tr("GS_codecpriority"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrCodecPrio,
                                                    "VALIDATION_TYPE"        => "",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_qualifysmoothing" => array("LABEL"             => _tr("qualifysmoothing"),
                                				    "DESCRIPTION"            => _tr("GS_qualifysmoothingIax"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNod,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|noset){1}$"),
                            "iax_qualifyfreqok" => array("LABEL"             => _tr("qualifyfreqokIax"),
                                				    "DESCRIPTION"            => _tr("GS_qualifyfreqokIax"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "iax_qualifyfreqnotok" => array("LABEL"             => _tr("qualifyfreqnotok"),
                                				    "DESCRIPTION"            => _tr("GS_qualifyfreqnotokIax"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => "")
    );
    return $arrFormElements;
}

function createVMForm($arrZoneMessage)
{
    $arrVMesg=array(""=>_tr("Default"),"u"=>_tr("Unavailable"),"b"=>_tr("Busy"),"s"=>_tr("No Message"));
    $arrYesNo=array(_tr("yes")=>_tr("Yes"),"no"=>"No");
    $arrOptions=array(""=>_tr("Standard Message"),"s"=>_tr("Beep only"));
    $arrTries=array("1","2","3","4");
    $arrTime=array("1","2","3","4","5","6","7","8","9","10");
    $arrZoneMessage = ($arrZoneMessage===false)?array():$arrZoneMessage;
    
    $arrFormElements = array("VM_PREFIX" => array("LABEL"                  => _tr('Voicemail Prefix'),
                                				    "DESCRIPTION"            => _tr("GS_voicemailprefix"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:80px"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "VM_DDTYPE" => array("LABEL"                  => _tr('Voicemail Message type'),
                                				    "DESCRIPTION"            => _tr("GS_voicemailmessagetype"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrVMesg,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VM_GAIN" => array("LABEL"                  => _tr('Voicemail Gain'),
                                				    "DESCRIPTION"            => _tr("GS_voicemailgain"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:80px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VM_OPTS" => array("LABEL"                  => _tr('Play "please leave message after tone" to caller'),
                                				    "DESCRIPTION"            => _tr("GS_playpleaseleave"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array("s"=>_tr("Yes"),""=>"No"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "OPERATOR_XTN" => array("LABEL"                  => _tr('Operator Extension'),
                                				    "DESCRIPTION"            => _tr("GS_operatorextension"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:80px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_CONTEXT" => array("LABEL"                  => _tr('Default Context & Pri'),
                                				    "DESCRIPTION"            => _tr("GS_defaultcontext_prim"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_PRI" => array("LABEL"                  => _tr('pri'),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:80px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_TIMEDEST_CONTEXT" => array("LABEL"        => _tr('Timeout / #press'),
                                				    "DESCRIPTION"            => _tr("GS_timeout_press"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_TIMEDEST_EXT" => array("LABEL"            => _tr("exten"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:80px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_TIMEDEST_PRI" => array("LABEL"            => _tr('pri'),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:80px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_LOOPDEST_CONTEXT" => array("LABEL"        => _tr('Loop exceed Default'),
                                				    "DESCRIPTION"            => _tr("GS_loopexceed"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_LOOPDEST_EXT" => array("LABEL"            => _tr("exten"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:80px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_LOOPDEST_PRI" => array("LABEL"            => _tr('pri'),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:80px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_OPTS_TIMEOUT" => array("LABEL"        => _tr('Timeout VM Msg'),
                                				    "DESCRIPTION"            => _tr("GS_timeoutVM"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrOptions,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_OPTS_LOOP" => array("LABEL"            => _tr("Max Loop VM msg"),
                                				    "DESCRIPTION"            => _tr("GS_maxloopVM"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrOptions,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_OPTS_DOVM" => array("LABEL"            => _tr('Direct VM Option'),
                                				    "DESCRIPTION"            => _tr("GS_directVMoption"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrOptions,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_TIMEOUT" => array("LABEL"        => _tr('Msg Timeout'),
                                				    "DESCRIPTION"            => _tr("GS_msgtimeout"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrTime,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_REPEAT" => array("LABEL"            => _tr("Msg Play"),
                                				    "DESCRIPTION"            => _tr("GS_msgplay"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrTries,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                              "VMX_LOOPS" => array("LABEL"            => _tr('Error Re-tries'),
                                				    "DESCRIPTION"            => _tr("GS_error_retries"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrTries,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_attach"   => array("LABEL"               => _tr("Email Attachment"),
                                				    "DESCRIPTION"            => _tr("GS_emailattachment"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                            "vm_maxmsg"   => array("LABEL"               => _tr("Maximum # of message per Folder"),
                                				    "DESCRIPTION"            => _tr("GS_maxnummessageperfolder"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_emailsubject"   => array("LABEL"               => _tr("Email Subject"),
                                                    "DESCRIPTION"            => _tr("Email subject used at moment to send the email."),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:500px"),
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
                            "vm_saycid"   => array("LABEL"               => _tr("Play CID"),
                                				    "DESCRIPTION"            => _tr("GS_playCID"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                            "vm_sayduration"   => array("LABEL"               => _tr("Say Duration"),
                                				    "DESCRIPTION"            => _tr("GS_sayduration"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                            "vm_envelope"   => array("LABEL"            => _tr("Play Envelope"),
                                				    "DESCRIPTION"            => _tr("GS_playenvelope"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                            "vm_delete"   => array("LABEL"               => _tr("Delete Voicemail"),
                                				    "DESCRIPTION"            => _tr("GS_deletevoicemail"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                            "vm_context"   => array("LABEL"               => _tr("Voicemail Context"),
                                				    "DESCRIPTION"            => _tr("GS_vociemailcontext"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_tz"   => array("LABEL"               => _tr("Time Zone"),
                                				    "DESCRIPTION"            => _tr("GS_timezone"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrZoneMessage,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "vm_review"   => array("LABEL"               => _tr("Review Message"),
                                				    "DESCRIPTION"            => _tr("GS_reviewmessage"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                            "vm_operator"   => array("LABEL"               => _tr("Operator"),
                                				    "DESCRIPTION"            => _tr("GS_operator"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                            "vm_forcename"   => array("LABEL"               => _tr("Force to record name"),
                                				    "DESCRIPTION"            => _tr("GS_forcetorecordname"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                            "vm_forcegreetings" => array("LABEL"            => _tr("Force to record greetings"),
                                				    "DESCRIPTION"            => _tr("GS_forcetorecordgreetings"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                        );
    return $arrFormElements;
}

function reloadAasterisk($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $showMsg=false;
    $continue=false;

    /*if($arrCredentiasls['userlevel']=="other"){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("You are not authorized to perform this action"));
    }*/

    $idOrganization=$credentials['id_organization'];
    if($credentials['userlevel']=="superadmin"){
        $idOrganization = getParameter("organization_id");
    }

    if($idOrganization==1){
        return viewGeneralSetting($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $query="select domain from organization where id=?";
    $result=$pDB->getFirstRowQuery($query, false, array($idOrganization));
    if($result===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Asterisk can't be reloaded. ")._tr($pDB->errMsg));
        $showMsg=true;
    }elseif(count($result)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Asterisk can't be reloaded. ")._tr("Invalid Organization. "));
        $showMsg=true;
    }else{
        $domain=$result[0];
        $continue=true;
    }

    if($continue){
        $pAstConf=new paloSantoASteriskConfig($pDB);
        if($pAstConf->generateDialplan($domain)===false){
            $pAstConf->setReloadDialplan($domain,true);
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Asterisk can't be reloaded. ").$pAstConf->errMsg);
            $showMsg=true;
        }else{
            $pAstConf->setReloadDialplan($domain);
            $smarty->assign("mb_title", _tr("MESSAGE"));
            $smarty->assign("mb_message",_tr("Asterisk was reloaded correctly. "));
        }
    }

    return viewGeneralSetting($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function getAction(){
    global $arrPermission;
    if(getParameter("save_edit"))
        return (in_array("edit",$arrPermission))?"apply":"view";
    elseif(getParameter("action")=="reloadAsterisk")
        return "reloadAasterisk";
    else
        return "view"; //cancel
}
?>
