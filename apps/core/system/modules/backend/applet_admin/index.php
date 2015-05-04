<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-7                                               |
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
  $Id: index.php,v 1.1 2009-12-28 06:12:49 Bruno bomv.27 Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //user credentials
    $arrCredentiasls=getUserCredentials($_SESSION['elastix_user']);
    
    //user permissions
    global $arrPermission;
    $arrPermission=getResourceActionsByUser($arrCredentiasls['idUser'],$module_name);
    if($arrPermission==false)
       header("Location: index.php");
       
    //actions
    $action = getAction();
    $content = "";
    switch($action){
        case "save_new":
            $content = saveApplets_Admin($module_name);
            break;
        default: // view_form
            $content = showApplets_Admin($module_name);
            break;
    }
    return $content;
}

function showApplets_Admin($module_name)
{
    global $smarty;
    global $arrLang;
    global $arrConf;

    $pAppletAdmin = new paloSantoAppletAdmin();
    $oForm = new paloForm($smarty,array());

    $arrApplets = $pAppletAdmin->getApplets_User($_SESSION["elastix_user"]);

    $smarty->assign("applets",$arrApplets);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("Applet", $arrLang["Applet"]);
    $smarty->assign("Activated", $arrLang["Activated"]);
    $smarty->assign("icon", "web/apps/$module_name/images/system_dashboard_applet_admin.png");
    setActionTPL();
    
    //folder path for custom templates
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);
    $htmlForm = $oForm->fetchForm("$local_templates_dir/applet_admin.tpl",$arrLang["Dashboard Applet Admin"], $_POST);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveApplets_Admin($module_name)
{
    global $smarty;
    global $arrLang;
    $arrIDs_DAU = null;

    if(is_array($_POST) & count($_POST)>0){
        foreach($_POST as $key => $value){
            if(substr($key,0,7) == "chkdau_")
                $arrIDs_DAU[] = substr($key,7);
        }
    }

    $pAppletAdmin = new paloSantoAppletAdmin();
    if(count($arrIDs_DAU)==0){
        $smarty->assign("mb_title", $arrLang["ERROR"]);
        $smarty->assign("mb_message", $arrLang["You must have at least one applet activated"]);
    }
    else{
        $ok = $pAppletAdmin->setApplets_User($arrIDs_DAU, $_SESSION["elastix_user"]);
        if(!$ok){
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $pAppletAdmin->errMsg);
        }
    }
    return showApplets_Admin($module_name);
}

function getAction()
{
    global $arrPermission;
    if(getParameter("save_new")) //Get parameter by POST (submit)
        //preguntar si el usuario puede hacer accion
        return (in_array('edit',$arrPermission))?'save_new':'report';
    else
        return "report"; //cancel
}

function setActionTPL(){
    global $smarty;
    global $arrPermission;
    if(in_array('edit',$arrPermission)){
        $smarty->assign('EDIT_APP',TRUE);
    }
}
?>
