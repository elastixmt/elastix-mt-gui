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

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    include_once "libs/paloSantoForm.class.php";
    
     //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);

    //user credentials
    global $arrCredentials;

    switch (getAction()) {
        case 'save':
            return saveConfigs($smarty, $module_name, $local_templates_dir, $pDB, $arrCredentials);
        default:
            return showConfigs($smarty, $module_name, $local_templates_dir, $pDB, $arrCredentials);
    }
}
 
function showConfigs($smarty, $module_name, $local_templates_dir, $pDB, $arrCredentials){
    global $arrPermission;
    $fMaster=new paloFaxMaster($pDB);
    $arrForm = fieldFrorm();
    
    $oForm = new paloForm($smarty, $arrForm);
    $oForm->setEditMode();
    
    $smarty->assign("EDIT",in_array('edit',$arrPermission));
    
    if(getParameter("save_default"))
        $arrDefault['fax_master']=$_POST['fax_master'];
    else{
        //obtener el valor de la tarifa por defecto
        $arrDefault['fax_master']=$fMaster->getFaxMaster();
        if($arrDefault['fax_master']===false){
            $smarty->assign("mb_title", "ERROR");
            $smarty->assign("mb_message", _tr("An error has ocurred to retrieved configuration.")." ".$fMaster->getErrorMsg());
            $arrDefault['fax_master']='';
        }
    }
    
    $smarty->assign("FAXMASTER_MSG", _tr("Write the email address which will receive the notifications of received messages, errors and activity summary of the Fax Server"));

    $smarty->assign("icon", "web/apps/$module_name/images/fax_fax_master.png"); 
    $smarty->assign("APPLY_CHANGES", _tr("Save"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $strReturn = $oForm->fetchForm("$local_templates_dir/fax_master.tpl", _tr("Fax Master Configuration"), $arrDefault);
    return $strReturn;
}

function saveConfigs($smarty, $module_name, $local_templates_dir, $pDB, $arrCredentials){
    $fMaster=new paloFaxMaster($pDB);
    $email_account=getParameter('fax_master');
    $email_account=trim($email_account);
    
    if(!preg_match("/^[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*@[a-z0-9]+([\._\-]?[a-z0-9]+)*(\.[a-z0-9]{2,6})+$/",$email_account)){
        $smarty->assign("mb_title", "ERROR");
        $smarty->assign("mb_message", _tr('Invalid Email Address'));
        return showConfigs($smarty, $module_name, $local_templates_dir, $pDB, $arrCredentials);
    }
    
    if($fMaster->setFaxMaster($email_account)){
        $smarty->assign("mb_title", "Message");
        $smarty->assign("mb_message", _tr('Changes were applied successfully')); 
    }else{
        $smarty->assign("mb_title", "ERROR");
        $smarty->assign("mb_message", _tr('Changes could not be applied.')." ".$fMaster->getErrorMsg());
    }
    return showConfigs($smarty, $module_name, $local_templates_dir, $pDB, $arrCredentials);
}

function fieldFrorm(){
        $arrForm  = array("fax_master" => array("LABEL"                   => _tr("Fax Master Email"),
                                                "REQUIRED"               => "yes",
                                                "EDITABLE"               => "yes",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => "",
                                                "VALIDATION_TYPE"        => "ereg",
                                                "VALIDATION_EXTRA_PARAM" => "^[a-zA-Z0-9_.\-]+@[a-zA-Z0-9_.\-]+\.[a-zA-Z0-9_.\-]+$"),
                    );
        return $arrForm;
}
function getAction()
{
    global $arrPermission;
    if(getParameter("save_default")) //Get parameter by POST (submit)
        return (in_array('edit',$arrPermission))?'save':'report';
    else
        return "show"; //cancel
}


?>
