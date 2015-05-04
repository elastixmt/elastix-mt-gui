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
        case "get_default_code": // report
            $content = get_default_code($smarty, $module_name, $pDB, $arrConf, $arrCredentials);
            break;
        default: // view
            $content = viewFeatures($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
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

function viewFeatures($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials, $action=""){
    global $arrPermission;
    $error = "";
    $pORGZ = new paloSantoOrganization($pDB);
    
    if($credentials['userlevel']=='superadmin'){
        $domain=getParameter(_tr('organization'));
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
    
    $arrForm = createFieldForm();
    $oForm = new paloForm($smarty,$arrForm);
    
    $pFC = new paloFeatureCodePBX($pDB,$domain);
    $arrFC = $pFC->getAllFeaturesCode($domain);
    if($arrFC===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pFC->errMsg));
    }else{
        foreach($arrFC as $feature){
            $name=$feature["name"];
            $disabled_sel="disabled";
            if($action=="edit"){
                $data[$name]=$_POST[$name];
                if(isset($_POST[$name."_stat"]))
                    $estado=$_POST[$name."_stat"];
            }else{
                if($feature["estado"]!="enabled")
                    $estado="disabled";
                else{
                    if(!is_null($feature["code"]) && $feature["code"]!=""){
                        $code=$feature["code"];
                        $estado="ena_custom";
                    }else{
                        $code=$feature["default_code"];
                        $estado="ena_default";
                    }
                }
                $data[$feature["name"]]=$code;
            }
            if($name!="pickup" && $name!="blind_transfer" && $name!="attended_transfer" && $name!="one_touch_monitor" 
            && $name!="disconnect_call"){
                if(getParameter("edit") || $action=="edit"){
                    $disabled_sel="";
                }
                $smarty->assign($feature["name"]."_stat",crearSelect($feature["name"],$estado,$disabled_sel));
            }
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
    
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE",_tr("Delete"));
    $smarty->assign("BLACKLIST",_tr("BLACKLIST"));
    $smarty->assign("CALLFORWARD",_tr("CALLFORWARD"));
    $smarty->assign("CALLWAITING",_tr("CALLWAITING"));
    $smarty->assign("CORE",_tr("CORE"));
    $smarty->assign("DICTATION",_tr("DICTATION"));
    $smarty->assign("DND",_tr("DND"));
    $smarty->assign("INFO",_tr("INFO"));
    $smarty->assign("RECORDING",_tr("RECORDING"));
    $smarty->assign("SPEEDDIAL",_tr("SPEEDDIAL"));
    $smarty->assign("VOICEMAIL",_tr("VOICEMAIL"));
    $smarty->assign("FOLLOWME",_tr("FOLLOWME"));
    $smarty->assign("QUEUE",_tr("QUEUE"));
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    if(getParameter("edit") || $action=="edit"){
        $oForm->setEditMode();
    }else{
        $oForm->setViewMode();
    }
    
    //permission
    $smarty->assign("EDIT_FC",in_array("edit",$arrPermission));
    
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("Features Code"), $data);
    $mensaje=showMessageReload($module_name, $pDB, $credentials);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$mensaje.$htmlForm."</form>";
    return $content;
}


function applyChanges($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $action = "";
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
                return viewFeatures($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
            }
        }else{
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("It's necesary you create at least one organization so you can use this module"));
            return viewFeatures($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{
        $domain=$credentials['domain'];
    }
    
    $arrForm = createFieldForm();
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
        $action="edit";
    }else{
        $pFC = new paloFeatureCodePBX($pDB,$domain);
        $arrFC = $pFC->getAllFeaturesCode($domain);
        if($arrFC===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr($pFC->errMsg));
        }else{
            $arrData=array();
            //obtengo las entradas
            foreach($arrFC as $feature){
                $code=null;
                $name=$feature["name"];
                if($name!="pickup" && $name!="blind_transfer" && $name!="attended_transfer" && $name!="one_touch_monitor" 
                && $name!="disconnect_call"){
                    $estado=getParameter($name."_stat"); //si esta o no habilitado el feature
                    if($estado=="ena_custom")
                        $code=getParameter($name); //el code altenativo en caso de que no se quiera usar el de po default
                }else{
                    $estado=$feature["estado"]; //si esta o no habilitado el feature
                }
                $arrData[]=array("name"=>$name,"default_code"=>$feature["default_code"],"code"=>$code,"estado"=>$estado);
            }
            $pDB->beginTransaction();
            $exito=$pFC->editPaloFeatureDB($arrData);
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
                $smarty->assign("mb_message",_tr("Changes couldn't be applied. ").$pFC->errMsg);
                $action="edit";
            }
        }
    }
    return viewFeatures($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials, $action);
}

function crearSelect($name,$option,$disabled){
    $opt1 = $opt2 = $opt3 = "";
    switch($option){
        case "ena_default":
            $opt1="selected";
            break;
        case "ena_custom":
            $opt2="selected";
            break;
        default:
            $opt3="selected";
            break;
    }
    $select="<select $disabled name='".$name."_stat' class='select'>";
    $select .="<option $opt1 value='ena_default'>Enabled Default</option>";
    $select .="<option $opt2 value='ena_custom'>Enabled Custom</option>";
    $select .="<option $opt3 value='disabled'>Disabled</option>";
    $select .="</select>";
    
    return $select; 
}

function get_default_code($smarty, $module_name, &$pDB, $arrConf, $credentials){
    $jsonObject = new PaloSantoJSON();
    $feature=getParameter("fc_name");
    if($credentials['userlevel']=='superadmin'){
        $domain=getParameter('organization');
    }else
        $domain=$credentials['domain'];
    
    $pFC = new paloFeatureCodePBX($pDB,$domain);
    if(!$pFC->validateFeatureCodePBX()){
            $jsonObject->set_error(_tr("Invalid Organization"));
    }else{
        $arrFC = $pFC->getFeaturesCode($domain,$feature);
        if($arrFC==FALSE){
            $jsonObject->set_error(_tr($pFC->errMsg));
        }else{
            $jsonObject->set_message($arrFC);
        }
    }
    return $jsonObject->createJSON();
}

function createFieldForm()
{
    $arrFormElements = array("blacklist_num" => array("LABEL"                  => _tr('Blacklist a number'),
                                                    "DESCRIPTION"            => _tr("FC_blacklistnumber"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "blacklist_lcall" => array("LABEL"                  => _tr('Blacklist the last caller'),
                                                    "DESCRIPTION"            => _tr("FC_blacklistlastcaller"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "blacklist_rm" => array("LABEL"                  => _tr('Remove a number from the blacklist'),
                                                    "DESCRIPTION"            => _tr("FC_removenumberfromblacklist"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "cf_all_act" => array("LABEL"                  => _tr('Call Forward All Activate'),
                                                    "DESCRIPTION"            => _tr("FC_callforwardallactivate"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "cf_all_desact" => array("LABEL"                  => _tr('Call Forward All Deactivate'),
                                                    "DESCRIPTION"            => _tr("FC_callforwardalldesactivate"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "cf_all_promp" => array("LABEL"                  => _tr('Call Forward All Prompting Deactivate'),
                                                    "DESCRIPTION"            => _tr("FC_callforwardallprompting"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "cf_busy_act" => array("LABEL"                  => _tr('Call Forward Busy Activate'),
                                                    "DESCRIPTION"            => _tr("FC_callforwardbusydesactivate"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "cf_busy_desact" => array("LABEL"                  => _tr('Call Forward Busy Deactivate'),
                                                    "DESCRIPTION"            => _tr("FC_callforwardbusydesactivate"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "cf_busy_promp" => array("LABEL"                  => _tr('Call Forward Busy Prompting Deactivate'),
                                                    "DESCRIPTION"            => _tr("FC_callforwardbusypromptingdesactivate"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),     
                              "cf_nu_act" => array("LABEL"                  => _tr('Call Forward No Answer/Unavailable Activate'),
                                                    "DESCRIPTION"            => _tr("FC_callforwardansweractivate"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "cf_nu_desact" => array("LABEL"                  => _tr('Call Forward No Answer/Unavailable Deactivate'),
                                                    "DESCRIPTION"            => _tr("FC_callforwardanswernoactivate"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "cf_toggle" => array("LABEL"                  => _tr('Call Forward Toggle'),
                                                    "DESCRIPTION"            => _tr("FC_callforwardtoggle"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"), 
                              "cw_act" => array("LABEL"                  => _tr('Call Waiting Activate'),
                                                    "DESCRIPTION"            => _tr("FC_callwaitingactivate"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),     
                              "cw_desact" => array("LABEL"                  => _tr('Call Waiting Deactivate'),
                                                    "DESCRIPTION"            => _tr("FC_callwaitingdesactivate"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "dictation_email" => array("LABEL"                  => _tr('Email completed dictation'),
                                                    "DESCRIPTION"            => _tr("FC_emailcompleteddictation"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "dictation_perform" => array("LABEL"                  => _tr('Perform dictation'),
                                                    "DESCRIPTION"            => _tr("FC_performdictation"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"), 
                              "dnd_act" => array("LABEL"                  => _tr('DND Activate'),
                                                    "DESCRIPTION"            => _tr("FC_dndactivate"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),     
                              "dnd_desact" => array("LABEL"                  => _tr('DND Desactivate'),
                                                    "DESCRIPTION"            => _tr("FC_dnddesactivate"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "dnd_toggle" => array("LABEL"                  => _tr('DND Toggle'),
                                                    "DESCRIPTION"            => _tr("FC_dndtoggle"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "fm_toggle" => array("LABEL"                  => _tr('Findme Follow Toggle'),
                                                    "DESCRIPTION"            => _tr("FC_findmefollowtoggle"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"), 
                              "call_trace" => array("LABEL"                  => _tr('Call Trace'),
                                                    "DESCRIPTION"            => _tr("FC_calltrace"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),     
                              "directory" => array("LABEL"                  => _tr('Directory'),
                                                    "DESCRIPTION"            => _tr("FC_directory"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "echo_test" => array("LABEL"                  => _tr('Echo Test'),
                                                    "DESCRIPTION"            => _tr("FC_echotest"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "speak_u_exten" => array("LABEL"                  => _tr('Speak Your Exten Number'),
                                                    "DESCRIPTION"            => _tr("FC_speakyourextennumber"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),   
                              "speak_clock" => array("LABEL"                  => _tr('Speaking Clock'),
                                                    "DESCRIPTION"            => _tr("FC_speakingclock"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "pbdirectory" => array("LABEL"                  => _tr('Phonebook dial-by-name directory'),
                                                    "DESCRIPTION"            => _tr("FC_phonebookdialbynamedirectory"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "queue_toggle" => array("LABEL"                  => _tr('Queue Toggle'),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),   
                              "speeddial_set" => array("LABEL"                  => _tr('Set user speed dial'),
                                                    "DESCRIPTION"            => _tr("FC_setuserspeeddial"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),        
                              "speeddial_prefix" => array("LABEL"                  => _tr('Speeddial prefix'),
                                                    "DESCRIPTION"            => _tr("FC_speeddialprefix"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "voicemail_dial" => array("LABEL"                  => _tr('Dial Voicemail'),
                                                    "DESCRIPTION"            => _tr("FC_dialvoicemail"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "voicemail_mine" => array("LABEL"                  => _tr('My Voicemail'),
                                                    "DESCRIPTION"            => _tr("FC_myvoicemail"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),     
                              "sim_in_call" => array("LABEL"                  => _tr('Simulate Incoming Call'),
                                                    "DESCRIPTION"            => _tr("FC_simulateincomingcall"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "direct_call_pickup" => array("LABEL"                  => _tr('Directed Call Pickup'),
                                                    "DESCRIPTION"            => _tr("FC_directcallpickup"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "pickup" => array("LABEL"                  => _tr('Asterisk General Call Pickup'),
                                                    "DESCRIPTION"            => _tr("FC_asteriskgeneralcallpickup"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "blind_transfer" => array("LABEL"              => _tr('In-Call Asterisk Blind Transfer'),
                                                    "DESCRIPTION"            => _tr("FC_incallasteriskblindtransfer"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),        
                              "attended_transfer" => array("LABEL"           => _tr('In-Call Asterisk Attended Transfer'),
                                                    "DESCRIPTION"            => _tr("FC_incallasteriskattendtransfer"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "one_touch_monitor" => array("LABEL"           => _tr('In-Call Asterisk Toggle Call Recording'),
                                                    "DESCRIPTION"            => _tr("FC_incallasterisktoggle"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"),
                              "disconnect_call" => array("LABEL"             => _tr('In-Call Asterisk Disconnect Code'),
                                                    "DESCRIPTION"            => _tr("FC_incallasteriskdisconnectcode"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px","class"=>"feature_val"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^[0-9\#\*]+$"), 
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
        return viewFeatures($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
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

    return viewFeatures($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function getAction(){
    global $arrPermission;
    if(getParameter("save_edit"))
        return (in_array("edit",$arrPermission))?"apply":"view";
    else if(getParameter("edit"))
        return (in_array("edit",$arrPermission))?"view_edit":"view";
    elseif(getParameter("action")=="reloadAsterisk")
        return "reloadAasterisk";
    elseif(getParameter("action")=="fc_get_default_code")
        return "get_default_code";
    else
        return "view"; //cancel
}
?>
