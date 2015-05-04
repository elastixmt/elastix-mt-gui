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
  $Id: index.php,v 1.3 2007/07/17 00:03:42 gcarrillo Exp $ */
$module = "registration";
include_once("libs/misc.lib.php");
include_once "configs/default.conf.php";
include_once "libs/paloSantoNavigation.class.php"; 
include_once "libs/paloSantoDB.class.php";
include_once("libs/paloSantoACL.class.php");// Don activate unless you know what you are doing. Too risky!

load_default_timezone();

require_once("modules/$module/index.php");
$developerMode=false;

session_name("elastixSession");
session_start();

load_language();
$pDB = new paloDB($arrConf['elastix_dsn']['acl']);

if(!empty($pDB->errMsg)) {
    echo "ERROR DE DB: $pDB->errMsg <br>";
}

$pACL = new paloACL($pDB);

if(!empty($pACL->errMsg)) {
    echo "ERROR DE DB: $pACL->errMsg <br>";
}

// Load smarty
$arrConf['mainTheme'] = load_theme($arrConf['basePath']."/");
$smarty = getSmarty($arrConf['mainTheme']);

$pDBMenu = new paloDB($arrConf['elastix_dsn']['elastix']);

// 2) Autentico usuario
if(isset($_SESSION['elastix_user']) && isset($_SESSION['elastix_pass']) && $pACL->authenticateUser($_SESSION['elastix_user'], $_SESSION['elastix_pass']) or $developerMode==true) {
    $idUser = $pACL->getIdUser($_SESSION['elastix_user']);

    // rawmode es un modo de operacion que pasa directamente a la pantalla la salida
    // del modulo. Esto es util en ciertos casos.
    $rawmode = getParameter("rawmode");
    if(isset($rawmode) && $rawmode=='yes') {
         // Autorizacion si es usuario admin
            echo _moduleContent($smarty,$module);
    } 
} else {
    $smarty->assign("THEMENAME", $arrConf['mainTheme']);
    $smarty->assign("currentyear",date("Y"));
    $smarty->assign("PAGE_NAME", _tr('Login page'));
    $smarty->assign("WELCOME", _tr('Welcome to Elastix'));
    $smarty->assign("ENTER_USER_PASSWORD", _tr('Please enter your username and password'));
    $smarty->assign("USERNAME", _tr('Username'));
    $smarty->assign("PASSWORD", _tr('Password'));
    $smarty->assign("SUBMIT", _tr('Submit'));
    $smarty->display("_common/login.tpl");
}
?>
