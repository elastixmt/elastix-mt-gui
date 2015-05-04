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

function spl_elastix_class_autoload($sNombreClase)
{
    if (!preg_match('/^\w+$/', $sNombreClase)) return;

    $sNombreBase = $sNombreClase.'.class.php';
    foreach (explode(':', ini_get('include_path')) as $sDirInclude) {
        if (file_exists($sDirInclude.'/'.$sNombreBase)) {
            require_once($sNombreBase);
            return;
        }
    }
}
spl_autoload_register('spl_elastix_class_autoload');

// Agregar directorio libs de script a la lista de rutas a buscar para require()
$elxPath="/usr/share/elastix";
// /usr/share/elastix/ directorio que contiene las librerias del sistema
//
ini_set('include_path',dirname($_SERVER['SCRIPT_FILENAME']).":$elxPath:".ini_get('include_path'));

include_once "libs/misc.lib.php";
include_once "configs/default.conf.php";
include_once "libs/paloSantoDB.class.php";
include_once "libs/paloSantoACL.class.php";
include_once "libs/paloSantoMenu.class.php";
include_once "libs/paloSantoNavigation.class.php";

$arrConf['basePath']=$arrConf['basePath']; //se cambia la ubicacion del modulo

load_default_timezone();

session_name("elastixSession");
session_start();

$arrConf['mainTheme'] = load_theme_fui();

if(isset($_GET['logout']) && $_GET['logout']=='yes') {
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"unknown";
    writeLOG("audit.log", "LOGOUT $user: Web Interface logout successful. Accepted logout for $user from $_SERVER[REMOTE_ADDR].");
    session_destroy();
    session_name("elastixSession");
    session_start();
    header("Location: index.php");
    exit;
}
//cargar el archivo de idioma
load_language();
$lang = get_language();
if(file_exists("langmenus/$lang.lang")){
    include_once "langmenus/$lang.lang";
    global $arrLangMenu;
    global $arrLang;
    $arrLang = array_merge($arrLang,$arrLangMenu);
}

$pdbACL = new paloDB($arrConf['elastix_dsn']['elastix']);
$pACL = new paloACL($pdbACL);
if(!empty($pACL->errMsg)) {
    echo "ERROR DE DB: $pACL->errMsg <br>";
}

// Load smarty
$smarty = getSmarty($arrConf['mainTheme']);

//- 1) SUBMIT. Si se hizo submit en el formulario de ingreso
//-            autentico al usuario y lo ingreso a la sesion
if(isset($_POST['submit_login']) and !empty($_POST['input_user'])) {
    $pass_md5 = md5(trim($_POST['input_pass']));
    
    if($pACL->authenticateUser($_POST['input_user'], $pass_md5)) {
        session_regenerate_id(TRUE);

        $_SESSION['elastix_user'] = trim($_POST['input_user']);
        $_SESSION['elastix_pass'] = $pass_md5;      
        $_SESSION['elastix_pass2'] = $_POST['input_pass'];
        header("Location: index.php");
        writeLOG("audit.log", "LOGIN $_POST[input_user]: Web Interface login successful. Accepted password for $_POST[input_user] from $_SERVER[REMOTE_ADDR].");
        update_theme();
        exit;
    } else {
        $user = urlencode(substr($_POST['input_user'],0,20));
        if(!$pACL->getIdUser($_POST['input_user'])) // not exists user?
            writeLOG("audit.log", "LOGIN $user: Authentication Failure to Web Interface login. Invalid user $user from $_SERVER[REMOTE_ADDR].");
        else
            writeLOG("audit.log", "LOGIN $user: Authentication Failure to Web Interface login. Failed password for $user from $_SERVER[REMOTE_ADDR].");
        // Debo hacer algo aquí?
    }
}

// 2) Autentico usuario
if (isset($_SESSION['elastix_user']) && 
    isset($_SESSION['elastix_pass']) && 
    $pACL->authenticateUser($_SESSION['elastix_user'], $_SESSION['elastix_pass'])) {

    if($pACL->isUserSuperAdmin($_SESSION['elastix_user'])){
        header("Location: admin/index.php");
    }


    $idUser = $pACL->getIdUser($_SESSION['elastix_user']);
    $pMenu = new paloMenu($arrConf['elastix_dsn']['elastix']);
    
    $arrUser = $pACL->getUsers($idUser);
    foreach($arrUser as $value){
        $arrFill["username"]=$value[1];
        $arrFill["name"]=$value[2];
	$arrFill["extension"]=$value[5];
    }
    $smarty->assign("ID_ELX_USER",$idUser);
    $smarty->assign("USER_NAME", $arrFill["name"]);
    $smarty->assign("USER_ESTENSION", $arrFill["extension"]);
    
    //obtenemos los menu a los que el usuario tiene acceso
    $arrMenuFiltered = $pMenu->filterAuthorizedMenus($idUser);
    
    $id_organization = $pACL->getIdOrganizationUser($idUser);
    $_SESSION['elastix_organization'] = $id_organization;

    if(!is_array($arrMenuFiltered))
        $arrMenuFiltered=array();
    
    //traducir el menu al idioma correspondiente
    foreach($arrMenuFiltered as $idMenu=>$arrMenuItem) {
        $arrMenuFiltered[$idMenu]['description'] = _tr($arrMenuItem['description']);
    }
    
    //variables de smarty usadas en los templates
    $smarty->assign("THEMENAME", $arrConf['mainTheme']);
    $smarty->assign("WEBPATH", "web/");
    $smarty->assign("WEBCOMMON", $arrConf['webCommon']."/");
    
    
    $smarty->assign("md_message_title", _tr('md_message_title'));
    $sCurYear = date('Y');
    if ($sCurYear < '2013') $sCurYear = '2013';
    $smarty->assign("currentyear", $sCurYear);
    $smarty->assign("ABOUT_ELASTIX_CONTENT", _tr('About Elastix Content'));
    $smarty->assign("ABOUT_CLOSED", _tr('About Elastix Closed'));
    $smarty->assign("Profile_l", _tr('Profile'));
    $smarty->assign("LOGOUT", _tr('Logout'));
    $smarty->assign("textMode", _tr('textMode'));
    $smarty->assign("htmlMode", _tr('htmlMode'));
    $smarty->assign("INT_SESSION", _tr('Starting Session'));
    $smarty->assign("ABOUT_ELASTIX", _tr('About Elastix')." ".$arrConf['elastix_version']);
    $selectedMenu = getParameter('menu');
    /* El módulo _elastixutils sirve para contener las utilidades json que
     * atienden requerimientos de varios widgets de la interfaz Elastix. Todo
     * requerimiento nuevo que no sea un módulo debe de agregarse aquí */
    // TODO: agregar manera de rutear _elastixutils a través de paloSantoNavigation
    if (!is_null($selectedMenu) && $selectedMenu == '_elastixutils' && 
        file_exists("$elxPath/apps/_elastixutils/index.php")) {
        
        // Cargar las configuraciones para el módulo elegido
        if (file_exists("$elxPath/apps/_elastixutils/configs/default.conf.php")) {
            require_once "apps/_elastixutils/configs/default.conf.php";

            global $arrConf;
            global $arrConfModule;
            if(is_array($arrConfModule))
                $arrConf = array_merge($arrConf, $arrConfModule);
        }
        
        // Cargar las traducciones para el módulo elegido
        load_language_module($selectedMenu);
        
        require_once "apps/_elastixutils/index.php";
        echo _moduleContent($smarty, $selectedMenu);
        return;
    }

    // Inicializa el objeto palosanto navigation
    $oPn = new paloSantoNavigation($arrMenuFiltered, $smarty, $selectedMenu);
    $selectedMenu = $oPn->getSelectedModule();
    // Obtener contenido del módulo, si usuario está autorizado a él
    $bModuleAuthorized = $pACL->isUserAuthorizedById($idUser, $selectedMenu);
    $sModuleContent = ($bModuleAuthorized) ? $oPn->showContent() : array('data'=>'');    
    // rawmode es un modo de operacion que pasa directamente a la pantalla la salida
    // del modulo. Esto es util en ciertos casos.
    $rawmode = getParameter("rawmode");
    if(isset($rawmode) && $rawmode=='yes') {
        $changeModule = getParameter("changeModule");
        if(isset($changeModule) && $changeModule=='yes'){
            require_once "libs/paloSantoJSON.class.php";
            $jsonObject = new PaloSantoJSON();
            if ($bModuleAuthorized) {
                $jsonObject->set_message($sModuleContent);
            }else{
                $jsonObject->set_error("Module is invalid");
            }
            echo $jsonObject->createJSON();
            return;
        }else{
            echo $sModuleContent['data'];
            return;
        }
    } else {
        $oPn->renderMenuTemplates();

        if (file_exists($arrConf['basePath'].'/web/themes/'.$arrConf['mainTheme'].'/themesetup.php')) {
        	require_once($arrConf['basePath'].'/web/themes/'.$arrConf['mainTheme'].'/themesetup.php');
            themeSetup($smarty, $selectedMenu, $pdbACL, $pACL, $idUser);
        }

        // Autorizacion
        if ($bModuleAuthorized) {
            if(isset($sModuleContent['JS_CSS_HEAD'])){
                //es necesario cargar los css y js que el modulo pone
                //$smarty->assign("HEADER_MODULES",$sModuleContent['JS_CSS_HEAD']);
                $smarty->assign("CONTENT", $sModuleContent['JS_CSS_HEAD']."<div id='module_content_framework_data'>".$sModuleContent['data']."</div>");
            }else{
                $smarty->assign("CONTENT", "<div id='module_content_framework_data'>".$sModuleContent['data']."<div>");
            }
            
            $smarty->assign('MENU', (count($arrMenuFiltered) > 0) 
                ? $smarty->fetch("_common/_menu_uf.tpl") 
                : _tr('No modules'));
        }
        $smarty->display("_common/index_uf.tpl");
    }
} else {
    $rawmode = getParameter("rawmode");
    if(isset($rawmode) && $rawmode=='yes'){
        include_once "libs/paloSantoJSON.class.php";
        $jsonObject = new PaloSantoJSON();
        $jsonObject->set_status("ERROR_SESSION");
        $jsonObject->set_error(_tr("Your session has expired. If you want to do a login please press the button 'Accept'."));
        $jsonObject->set_message(null);
        Header('Content-Type: application/json');
        echo $jsonObject->createJSON();
    }else{
        $oPn = new paloSantoNavigation(array(), $smarty);
        $oPn->putHEAD_JQUERY_HTML();
        $smarty->assign("THEMENAME", $arrConf['mainTheme']);
        $smarty->assign("WEBPATH", "web/");
        $smarty->assign("WEBCOMMON", $arrConf['webCommon']."/");      
        $smarty->assign("currentyear",date("Y"));
        $smarty->assign("PAGE_NAME", _tr('Login page'));
        $smarty->assign("WELCOME", _tr('Welcome to Elastix'));
        $smarty->assign("ENTER_USER_PASSWORD", _tr('Please enter your username and password'));
        $smarty->assign("USERNAME", _tr('Usernam'));
        $smarty->assign("PASSWORD", _tr('Password'));
        $smarty->assign("SUBMIT", _tr('Submit'));
        
        $smarty->display("_common/login_uf.tpl");

    }
}
?>
