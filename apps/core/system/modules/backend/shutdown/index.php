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

/**
 * resource actions => access
 *                     shutdown
 */
function _moduleContent(&$smarty, $module_name) {
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);
    
    //user credentials
    global $arrCredentials;
    
    global $arrPermission;
    
    $action=getAction();
    $content = "";

    switch($action){
        case "shutdown":
            $content = shutdown($smarty,$module_name,$local_templates_dir,$arrPermission,$arrCredentials);
        default:
            $content = showModule($smarty,$module_name,$local_templates_dir,$arrPermission,$arrCredentials);
    }
    return $content;
}

function showModule($smarty,$module_name,$local_templates_dir,$arrPermission,$arrCredentiasls){
    $smarty->assign("icon","web/apps/$module_name/images/system_shutdown.png");
    $smarty->assign("title",_tr("Shutdown"));
    $smarty->assign("ACCEPT", _tr("Accept"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("HALT", _tr("Halt"));
    $smarty->assign("REBOOT", _tr("Reboot"));
    $smarty->assign("module_name",$module_name);
    setActionTPL($smarty,$arrPermission);
    $salida = $smarty->fetch("$local_templates_dir/shutdown.tpl");
    return $salida;
}

function shutdown($smarty,$module_name,$local_templates_dir,$arrPermission,$arrCredentiasls){
    $smarty->assign("SHUTDOWN_PROGRESS", _tr("Shutdown in progress"));
    $smarty->assign("MSG_LINK", _tr("Continue"));
    if($_POST['shutdown_mode']=='1') {
        $smarty->assign("SHUTDOWN_MSG", _tr("Your system in shutting down now. Please, try again later."));
        exec("sudo -u root /sbin/shutdown -h now", $salida, $retorno);
        $salida = $smarty->fetch("file:$local_templates_dir/shutdown_in_progress.tpl");
    } else if ($_POST['shutdown_mode']=='2') {
        $smarty->assign("SHUTDOWN_MSG", _tr("The reboot signal has been sent correctly."));
        exec("sudo -u root /sbin/shutdown -r now", $salida, $retorno);
        $salida = $smarty->fetch("file:$local_templates_dir/shutdown_in_progress.tpl");
    } else {
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",_tr("Invalid Mode"));
        return showModule($smarty,$module_name,$local_templates_dir,$arrPermission,$arrCredentiasls);
    }
}

function getAction()
{
    global $arrPermission;
    if(getParameter('shutdown')){
        //preguntar si el usuario puede hacer accion
        return (in_array('shutdown',$arrPermission))?'shutdown':'show';
    }else{
        return 'show';
    }
}

function setActionTPL($smarty,$arrPermission){
    if(in_array('shutdown',$arrPermission)){
        $smarty->assign('SHUTDOWN',TRUE);
    }
}
?>
