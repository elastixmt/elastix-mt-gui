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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:56 gcarrillo Exp $ */

function _moduleContent($smarty, $module_name)
{
    global $arrConf;
    include_once "libs/paloSantoForm.class.php";
    
     //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);

    //user credentials
    global $arrCredentials;
    
    $action=getAction();
    switch($action){
        case 'save': 
            $contenidoModulo = applyChnageParameterFaxMail($smarty, $module_name, $local_templates_dir, $pDB, $arrCredentials);
            break;
        default:
            $contenidoModulo = listParameterFaxMail($smarty, $module_name, $local_templates_dir, $pDB, $arrCredentials);
            break;
    }
    return $contenidoModulo;
}

function applyChnageParameterFaxMail($smarty, $module_name, $local_templates_dir, $pDB, $credentials)
{
    $arrFaxConfig=createForm();
    $oForm = new paloForm($smarty, $arrFaxConfig);
    if(!$oForm->validateForm($_POST)) {
        // Validation basic, not empty and VALIDATION_TYPE
        $smarty->assign("mb_title", _tr("ERROR"));
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr("The following fields contain errors").":</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "{$k} [{$v['mensaje']}], ";
        }
        $smarty->assign("mb_message", $strErrorMsg);   
    }else {
        $oFax    = new paloFax($pDB);
        if($oFax->setConfigurationSendingFaxMailOrg($credentials['id_organization'], $_POST['fax_remite'],$_POST['fax_remitente'],$_POST['fax_subject'],$_POST['fax_content'])){
            $smarty->assign("mb_title", _tr("Message"));
            $smarty->assign("mb_message", _tr("Changes were applied successfully."));
        }else{
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("Changes could not be applied.")." ".$oFax->errMsg);
        }
    }
    return listParameterFaxMail($smarty, $module_name, $local_templates_dir, $pDB, $credentials);
}

function listParameterFaxMail($smarty, $module_name, $local_templates_dir, $pDB, $credentials)
{
    $arrData = array();
    $oFax    = new paloFax($pDB);
 
    global $arrPermission;
    $smarty->assign("EDIT",in_array('edit',$arrPermission));
    
    if(getParameter("submit_apply_change")){
        $arrParameterFaxMail=$_POST;
    }else{
        $arrParameterFaxMail = $oFax->getConfigurationSendingFaxMailOrg($credentials['id_organization']); 
        if($arrParameterFaxMail===false){
            $smarty->assign("mb_title","ERROR");
            $smarty->assign("mb_message","An error has ocurred to retrieved email fax configutaion");
        }
    }
    
    // Definición del formulario
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("EDIT_PARAMETERS", _tr("Edit Parameters"));
    $smarty->assign("icon","web/apps/$module_name/images/fax_email_template.png");

    
    $arrFaxConfig=createForm();
    $oForm = new paloForm($smarty, $arrFaxConfig);
    
    if(getParameter("submit_apply_change") || getParameter("submit_edit")){
        $oForm->setEditMode();
    }else{
        $oForm->setViewMode();
    }
    
    return $oForm->fetchForm("$local_templates_dir/parameterFaxMail.tpl", _tr("Configuration Sending Fax Mail"), $arrParameterFaxMail);
}

function createForm(){
    $arrFaxConfig    = array("fax_remite"        => array("LABEL"                 => _tr('Fax From'),
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:240px"),
                                                     "VALIDATION_TYPE"        => "email",
                                                     "EDITABLE"               => "si",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "fax_remitente"      => array("LABEL"                => _tr("Fax From Name"),
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:240px"),
                                                     "VALIDATION_TYPE"        => "name",
                                                     "EDITABLE"               => "si",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "fax_subject"        => array("LABEL"                => _tr("Fax Suject"),
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:240px"),
                                                     "VALIDATION_TYPE"        => "text",
                                                     "EDITABLE"               => "si",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "fax_content"       => array("LABEL"                 => _tr("Fax Content"),
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXTAREA",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "text",
                                                     "EDITABLE"               => "si",
                                                     "COLS"                   => "50",
                                                     "ROWS"                   => "4",
                                                     "VALIDATION_EXTRA_PARAM" => ""));
    return $arrFaxConfig;
}


function getAction(){
    global $arrPermission;
    if(getParameter("submit_apply_change")){
        return (in_array('edit',$arrPermission))?'save':'show';
    }else{
        return 'show';
    }
}
?>
