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
  $Id: new_campaign.php $ */

require_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoACL.class.php";

function _moduleContent(&$smarty, $module_name)
{
    
    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
        
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    // se conecta a la base
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
    $pACL = new paloACL($pDB);
	$user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = $pACL->getIdUser($user);
    
    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        case "save":
            $content = saveThemes($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $uid);
            break;
        default:
            $content = formThemes($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $uid);
            break;
    }
    return $content;

}

function formThemes($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $uid){
   
    global $arrPermission;
    if(!empty($pDB->errMsg)) {
        $smarty->assign("mb_message", _tr("Error when connecting to database")."<br/>".$pDB->errMsg);
    }
    
    // Definición del formulario de nueva campaña
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CHANGE", _tr("Save"));
    $smarty->assign("icon","web/apps/$module_name/images/system_preferences_themes.png");

    $oThemes = new PaloSantoThemes($pDB); 
    $arr_themes = $oThemes->getThemes("/var/www/html/admin/web/themes/");
    $formThemes= createFieldForm($arr_themes);
    $oForm = new paloForm($smarty, $formThemes);
    
    if((in_array('edit',$arrPermission)))
            $smarty->assign('EDIT_THEME',true);
            
    $tema_actual = $oThemes->getThemeActual($uid); 
    $arrTmp['themes'] = $tema_actual;
   
    $contenidoModulo = $oForm->fetchForm("$local_templates_dir/new.tpl", _tr("Change Theme"),$arrTmp);
    return $contenidoModulo;

}


function createFieldForm($arr_themes){
    $formCampos = array(
        'themes'                 => array(
            "LABEL"                  => _tr("Themes"),
            "DESCRIPTION"            => _tr("TH_tem"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $arr_themes,
            "VALIDATION_TYPE"        => "",
            "VALIDATION_EXTRA_PARAM" => "",
        )
    );
   return $formCampos;
}

function saveThemes($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $uid){
           
        
    $oThemes = new PaloSantoThemes($pDB);
    $arr_themes = $oThemes->getThemes("/var/www/html/admin/web/themes/");
    $formThemes= createFieldForm($arr_themes);
    $oForm = new paloForm($smarty, $formThemes);
    
    $exito   = $oThemes->updateTheme($_POST['themes'],$uid);

    if ($exito) {
        if($oThemes->smartyRefresh($_SERVER['DOCUMENT_ROOT'])){
            header("Location: index.php?menu=themes_system");
            die();
        }else{
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("The smarty cache could not be deleted"));
        }
    } else {
        $smarty->assign("mb_title", _tr("Validation Error"));
        $smarty->assign("mb_message", $oThemes->errMsg);
    } 
    return formThemes($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $uid);
}


function getAction()
{
    global $arrPermission;
    if(getParameter("changeTheme")) //Get parameter by POST (submit)
        return (in_array('edit',$arrPermission))?'save':'report';
    else
        return "report";
}

?>
