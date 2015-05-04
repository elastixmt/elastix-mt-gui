<?php 
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0-16                                               |
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
  $Id: index.php,v 1.1 2007/01/09 23:49:36 alex Exp $
*/
require_once "libs/paloSantoJSON.class.php";

function _moduleContent(&$smarty, $module_name)
{
    require_once "apps/$module_name/libs/elastixutils.lib.php";
    $sFuncName = 'handleJSON_'.getParameter('action');
    if (function_exists($sFuncName))
        return $sFuncName($smarty, $module_name);
    
    $jsonObject = new PaloSantoJSON();
    $jsonObject->set_status('false');
    $jsonObject->set_error(_tr('Undefined utility action'));
    return $jsonObject->createJSON();
}

function handleJSON_versionRPM($smarty, $module_name)
{
    $json = new Services_JSON();
    return $json->encode(obtenerDetallesRPMS());
}

function handleJSON_changePasswordElastix($smarty, $module_name)
{
    Header('Content-Type: application/json');
    
    $jsonObject = new PaloSantoJSON();
    $output = setUserPassword();
    $jsonObject->set_status(($output['status'] === TRUE) ? 'true' : 'false');
    if($output['status'])
        $jsonObject->set_message($output['msg']);
    else{
        $jsonObject->set_error($output['msg']);
    }
    return $jsonObject->createJSON();
}

function handleJSON_search_module($smarty, $module_name)
{
    return searchModulesByName();
}

function handleJSON_changeColorMenu($smarty, $module_name)
{
    $jsonObject = new PaloSantoJSON();
    $output = changeMenuColorByUser();
    $jsonObject->set_status(($output['status'] === TRUE) ? 'true' : 'false');
    $jsonObject->set_error($output['msg']);
    return $jsonObject->createJSON();
}

function handleJSON_addBookmark($smarty, $module_name)
{
    $jsonObject = new PaloSantoJSON();
    $id_menu = getParameter("id_menu");
    if (empty($id_menu)) {
        $jsonObject->set_status('false');
        $jsonObject->set_error(_tr('Module not specified'));
    } else {
        $output = putMenuAsBookmark($id_menu);
        if(getParameter('action') == 'deleteBookmark') $output["data"]["menu_url"] = $id_menu;
        $jsonObject->set_status(($output['status'] === TRUE) ? 'true' : 'false');
        $jsonObject->set_error($output['msg']);
        $jsonObject->set_message($output['data']);
    }
    return $jsonObject->createJSON();
}

function handleJSON_deleteBookmark($smarty, $module_name)
{
    // La función subyacente agrega el bookmark si no existe, o lo quita si existe
    return handleJSON_addBookmark($smarty, $module_name);
}

function handleJSON_save_sticky_note($smarty, $module_name)
{
    $jsonObject = new PaloSantoJSON();
    $id_menu = getParameter("id_menu");
    if (empty($id_menu)) {
        $jsonObject->set_status('ERROR');
        $jsonObject->set_error(_tr('Module not specified'));
    } else {
        $description_note = getParameter("description");
        $popup_note = getParameter("popup");    
        $output = saveStickyNote($id_menu, $description_note, $popup_note);
        $jsonObject->set_status(($output['status'] === TRUE) ? 'OK' : 'ERROR');
        $jsonObject->set_error($output['msg']);
    }
    return $jsonObject->createJSON();
}

function handleJSON_get_sticky_note($smarty, $module_name)
{
    $jsonObject = new PaloSantoJSON();
    $id_menu = getParameter("id_menu");
    if (empty($id_menu)) {
        $jsonObject->set_status('ERROR');
        $jsonObject->set_error(_tr('Module not specified'));
    } else {
        global $arrConf;
        
        $pdbACL = new paloDB($arrConf['elastix_dsn']['elastix']);
        $pACL = new paloACL($pdbACL);
        $idUser = $pACL->getIdUser($_SESSION['elastix_user']);

        $output = getStickyNote($pdbACL, $idUser, $id_menu);
        $jsonObject->set_status(($output['status'] === TRUE) ? 'OK' : 'ERROR');
        $jsonObject->set_error($output['msg']);
        $jsonObject->set_message($output['data']);
    }
    return $jsonObject->createJSON();
}

function handleJSON_saveNeoToggleTab($smarty, $module_name)
{
    $jsonObject = new PaloSantoJSON();
    $id_menu = getParameter("id_menu");
    if (empty($id_menu)) {
        $jsonObject->set_status('false');
        $jsonObject->set_error(_tr('Module not specified'));
    } else {
        $statusTab  = getParameter("statusTab");
        $output = saveNeoToggleTabByUser($id_menu, $statusTab);
        $jsonObject->set_status(($output['status'] === TRUE) ? 'true' : 'false');
        $jsonObject->set_error($output['msg']);
    }
    return $jsonObject->createJSON();
}

function handleJSON_showAboutAs($smarty, $module_name)
{
    global $arrConf;
    $jsonObject   = new PaloSantoJSON();
    $about_us_content=_tr('About Elastix Content');
    $html="<table border='0' cellspacing='0' cellpadding='2' width='100%'>".
            "<tr class='tabForm' >".
                "<td class='tabForm' align='center'>".
                    "$about_us_content<br />".
                    "<a href='http://www.elastix.org' target='_blank'>www.elastix.org</a>".
                "</td>".
            "</tr>".
          "</table>";


    $response['html']  = $html;
    $response['title'] = _tr('About Elastix')." ".$arrConf['elastix_version'];

    if($arrConf['mainTheme']=="elastixwave" || $arrConf['mainTheme']=="elastixneo")
        $response['title'] = _tr('About Elastix2');

    $jsonObject->set_message($response);
    return $jsonObject->createJSON();
}

function handleJSON_getImage($smarty, $module_name){
    global $arrCredentials;    
    global $arrConf;
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
    $pACL       = new paloACL($pDB);
    $imgDefault = "/var/www/html/web/_common/images/Icon-user.png";
    $id_user=getParameter("ID");
    $picture=false;
   
    $picture = $pACL->getUserPicture($id_user);
    
    // Creamos la imagen a partir de un fichero existente
    if($picture!=false && !empty($picture["picture_type"])){
        Header("Content-type: {$picture["picture_type"]}");
        print $picture["picture_content"];
    }else{
        Header("Content-type: image/png");
        $im = file_get_contents($imgDefault);
        echo $im;
    }
    return;
}

function handleJSON_getElastixAccounts($smarty, $module_name)
{
    Header('Content-Type: application/json');
    $jsonObject = new PaloSantoJSON();
    $searchFilter = getParameter('searchFilter');

    $errmsg = NULL;
    $arrContacts = getNewListElastixAccounts($searchFilter, &$errmsg);
    if ($arrContacts === FALSE) {
    	$jsonObject->set_error($errmsg);
    } else {
    	$jsonObject->set_message($arrContacts);
    }
    return $jsonObject->createJSON();
}

function handleJSON_getSIPParameters($smarty, $module_name)
{
    global $arrConf;

    Header('Content-Type: application/json');
    $jsonObject = new PaloSantoJSON();

    $error = '';
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
    $paramSIP = getChatClientConfig($pDB, $error);
    if (!is_array($paramSIP)) {
        $jsonObject->set_error(_tr("An error has ocurred to retrieved server configuration params:  ").': '.$error);
        return $jsonObject->createJSON();
    }
    
    $pACL = new paloACL($pDB);
    $arrCredentials = getUserCredentials($_SESSION['elastix_user']);
    $accountInfo = $pACL->getUserAccountInfo($arrCredentials['idUser'], $arrCredentials['id_organization']);

    /* Agregar los siguientes parámetros requeridos: 
     * elxuser_username display_name password  */
    $paramSIP += array(
        'elxuser_username'  =>  str_replace('IM_', 'IM@', $accountInfo['elxweb_device']),
        'display_name'      =>  $accountInfo['name'],
        'password'          =>  $_SESSION['elastix_pass2']
    );
    $jsonObject->set_message($paramSIP);

    return $jsonObject->createJSON();
}

function handleJSON_getSIPRoster($smarty, $module_name)
{
    global $arrConf;
    
    Header('Content-Type: application/json');
    $jsonObject = new PaloSantoJSON();
    
    $error = '';
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
    $pACL = new paloACL($pDB);
    $arrCredentials = getUserCredentials($_SESSION['elastix_user']);
    $accountList = $pACL->getUsersAccountsInfoByDomain($arrCredentials["id_organization"]);
    if (!is_array($accountList)) {
        $jsonObject->set_error(_tr("An error has ocurred to retrieved Contacts Info").': '.$pACL->errMsg);
        return $jsonObject->createJSON();
    }
    $paramSIP = getChatClientConfig($pDB, $error);
    if (!is_array($paramSIP)) {
        $jsonObject->set_error(_tr("An error has ocurred to retrieved server configuration params:  ").': '.$error);
        return $jsonObject->createJSON();
    }
    
    $result = array();
    foreach ($accountList as $tupla) if ($tupla['id'] != $arrCredentials['idUser']) {
        $result[] = array(
            'idUser'        =>  $tupla['id'],
            'display_name'  =>  $tupla['name'],
            'uri'           =>  $tupla['elxweb_device'].'@'.$paramSIP['elastix_chat_server'],
            'username'      =>  $tupla['username'],
            'extension'     =>  $tupla['extension'],
        );
    }
    $jsonObject->set_message($result);
    
    return $jsonObject->createJSON();
}

//action = getUserProfile
function handleJSON_getUserProfile($smarty, $module_name){
    include_once "libs/paloSantoForm.class.php";
    include "configs/languages.conf.php"; //este archivo crea el arreglo language que contine los idiomas soportados
                                          //por elastix
                                     
    Header('Content-Type: application/json');
    $arrCredentials=getUserCredentials($_SESSION['elastix_user']);
   
    $lang=get_language();
    $error_msg='';
    $archivos=array();
    $langElastix=array();
    
    global $arrConf;
    $ERROR='';
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
    $pACL = new paloACL($pDB);
    
    $jsonObject = new PaloSantoJSON();
    
    $dataProfile=getDataProfile($pDB, $ERROR);
    if($dataProfile === FALSE)
    {
        $smarty->assign("MSG_ERROR_FIELD",getErrorMsg());
        $jsonObject->set_error(getErrorMsg());
        return $jsonObject->createJSON();
    }
    $extension="{$dataProfile['exten']}/{$dataProfile['device']}";

    
    leer_directorio("/usr/share/elastix/lang",$error_msg,$archivos);
    if (count($archivos)>0){
        foreach ($languages as $lang=>$lang_name){
            if (in_array("$lang.lang",$archivos))
               $langElastix[$lang]=$lang_name;
        }
    }
    
    $selectedLanguage = $pACL->getUserProp($arrCredentials['idUser'],"language");
    
    if($selectedLanguage === FALSE)
    {
        $jsonObject->set_error(_tr("Invalid Language"));
        return $jsonObject->createJSON();
    }
    
    $smarty->assign("TITLE_POPUP",_tr("My Profile "));
    $smarty->assign("SAVE_POPUP",_tr("Save changes"));
    $smarty->assign("CHANGE_PASSWD_POPUP",_tr("Change Password"));
    $smarty->assign("userProfile_label",_tr("User"));
    $smarty->assign("userProfile",$dataProfile['username']);
    $smarty->assign("extenProfile_label",_tr("Extension"));
    $smarty->assign("extenProfile",$extension);
    $smarty->assign("faxProfile_label",_tr("Fax"));
    $smarty->assign("faxProfile",$dataProfile['fax_extension']);
    $smarty->assign("nameProfile",$dataProfile['name']);
    $smarty->assign('ID_PICTURE',$arrCredentials['idUser']);
    $smarty->assign('DeleteImage', _tr('Delete Image'));
    
    $dataProfile['languageProfile']=$selectedLanguage;
    
    $arrFormFilter = createProfileForm($langElastix);
    $oFilterForm = new paloForm($smarty, $arrFormFilter);
    $htmlFilter = $oFilterForm->fetchForm("/var/www/html/web/themes/elastix3/_common/profile_uf.tpl",_tr('My Profile'), $dataProfile);
    $jsonObject = new PaloSantoJSON();
    $jsonObject->set_message($htmlFilter);
    return $jsonObject->createJSON();
    
}

function handleJSON_changeLanguageProfile($smarty, $module_name){
    global $arrConf;
    
    Header('Content-Type: application/json');
    $arrCredentials=getUserCredentials($_SESSION['elastix_user']);
    
    $ERROR='';
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
    $pACL = new paloACL($pDB);
    
    $jsonObject = new PaloSantoJSON();
    
    $newLanguage = getParameter('newLanguage'); 
    
    $selectedLanguage=$pACL->setUserProp($arrCredentials['idUser'],"language",$newLanguage);
    //verificar que la respuesta no sea false
    if($selectedLanguage === FALSE)
    {
        $jsonObject->set_error(_tr("Invalid Language"));
        return $jsonObject->createJSON();
    }
    $jsonObject->set_message(_tr("Changes were saved succefully"));
    return $jsonObject->createJSON();
}

function handleJSON_deleteImageProfile($smarty, $module_name){
    global $arrConf;
    
    Header('Content-Type: application/json');
	
    $arrCredentials=getUserCredentials($_SESSION['elastix_user']);
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
    $pACL = new paloACL($pDB);
    $jsonObject = new PaloSantoJSON();
    $ERROR='';
    $idUser = $arrCredentials['idUser'];
    $result = deleteImgProfile($pDB, $ERROR);
    if($result === FALSE)
    {
        $jsonObject->set_error($ERROR);
        return $jsonObject->createJSON();
    }
    $url="index.php?menu=_elastixutils&action=getImage&ID=$idUser&rawmode=yes";
    $jsonObject->set_message($url);
    return $jsonObject->createJSON();
}

function handleJSON_changeImageProfile($smarty, $module_name){
    global $arrConf;
	
    Header('Content-Type: application/json');
	
    $arrCredentials=getUserCredentials($_SESSION['elastix_user']);
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
    $pACL = new paloACL($pDB);
    $jsonObject = new PaloSantoJSON();
    
    $idUser = $arrCredentials['idUser'];
    
    foreach ($_FILES['picture']['error'] as $key => $error)
    {
        if ($error == UPLOAD_ERR_OK)
        { 
            $pictureUpload = $_FILES['picture']['name'][$key];
            if (!preg_match("/^(\w|-|\.|\(|\)|\s)+\.(png|PNG|JPG|jpg|JPEG|jpeg)$/",$pictureUpload)) {
                $jsonObject->set_error(_tr("Invalid file extension.- It must be png or jpg or jpeg"));
                return $jsonObject->createJSON();
            }elseif(preg_match("/(\.php)/",$pictureUpload)){
                $jsonObject->set_error(_tr("Possible file upload attack."));
                return $jsonObject->createJSON();
            }else{
                
                if(is_uploaded_file($_FILES['picture']['tmp_name'][$key])){
                    $ancho = 159;
                    $alto = 159;
                    redimensionarImagen($_FILES['picture']['tmp_name'][$key],$_FILES['picture']['tmp_name'][$key],$ancho,$alto);
                    
                    $picture_type=$_FILES['picture']['type'][$key];
                    
                    $picture_content=file_get_contents($_FILES['picture']['tmp_name'][$key]);
                    
                    $Exito=$pACL->setUserPicture($idUser,$picture_type,$picture_content);
                    
                    if($Exito===false){
                       $jsonObject->set_error(_tr("Image couldn't be upload."));
                       return $jsonObject->createJSON();
                    }
                }else {
                    $jsonObject->set_error(_tr("Possible file upload attack. Filename")." : ". $pictureUpload);
                    return $jsonObject->createJSON();
                }
            }
            $url="index.php?menu=_elastixutils&action=getImage&ID=$idUser&rawmode=yes";
            $jsonObject->set_message($url);
            return $jsonObject->createJSON();
        }
    }
    return $jsonObject->createJSON();
    
}

// Manejo del guardado del ETag de la petición PUBLISH de presencia del usuario
function handleJSON_setPublishState($smarty, $module_name)
{
	Header('Content-Type: application/json');
	
    $publishState = array(
        'ETag'          => NULL,
        'note'          => 'Online',    // TODO: i18n, pero investigar interacción con Jitsi
        'activities'    =>  array(),
    );
    if (isset($_POST['ETag'])) {
        $publishState['ETag'] = $_POST['ETag'];
        if (empty($publishState['ETag'])) $publishState['ETag'] = NULL;
    }
    if (isset($_POST['note']) && is_string($_POST['note']))
        $publishState['note'] = $_POST['note'];
    if (isset($_POST['activities']) && is_array($_POST['activities'])) {
        $publishState['activities'] = array_map(function($x) { return "$x"; }, $_POST['activities']);
    }
    $_SESSION['publish_state'] = $publishState;
    
	$jsonObject = new PaloSantoJSON();
	$jsonObject->set_message('OK');
	return $jsonObject->createJSON();
}

// Petición del ETag previamente guardado para el PUBLISH de presencia del usuario
function handleJSON_getPublishState($smarty, $module_name)
{
	Header('Content-Type: application/json');
    
    $publishState = array(
        'ETag'          => NULL,
        'note'          => 'Online',    // TODO: i18n, pero investigar interacción con Jitsi
        'activities'    =>  array(),
    );
    if (isset($_SESSION['publish_state'])) $publishState = $_SESSION['publish_state'];
	
	$jsonObject = new PaloSantoJSON();
	$jsonObject->set_message($publishState);
	return $jsonObject->createJSON();
}

function handleJSON_getVideoPoster($smarty, $module_name)
{
    global $arrConf;
    
    $dialstring = isset($_GET['dialstring']) ? trim($_GET['dialstring']) : '';
    
    //$w = 150; $h = 120;
    $w = 200; $h = 150;
    
    Header('Content-Type: image/png');
    $im = imagecreatetruecolor($w, $h) or die('Failed to create image');
    $textcolor = imagecolorallocate($im, 192, 0, 0);
    $bgcolor = imagecolorallocate($im, 0, 0, 0);
    
    // Fondo negro
    imagefilledrectangle($im, 0, 0, $w, $h, $bgcolor);
    
    // Mostrar texto
    $domainfont = 2;
    $dialstringfont = 5;
    $otherfont = 4;
    
    $domain = '';
    if (strpos($dialstring, '@') !== FALSE) {
        $t = explode('@', $dialstring);
        $dialstring = $t[0];
        $domain = $t[1];
    }
    if ($domain == '') {
        $error = NULL;
        $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
        $paramSIP = getChatClientConfig($pDB, $error);
        $domain = $paramSIP['elastix_chat_server'];
    }
    
    $textlayout = array(
        array($domainfont, NULL, NULL, $domain),
        array($domainfont, NULL, NULL, ''),
        array($dialstringfont, NULL, NULL, $dialstring),
        array($domainfont, NULL, NULL, ''),
        array($otherfont, NULL, NULL, 'SOUND'),
        array($otherfont, NULL, NULL, 'ONLY'),
    );
    
    $totalheight = 0;
    for ($i = 0; $i < count($textlayout); $i++) {
        $textlayout[$i][2] = imagefontheight($textlayout[$i][0]);
        $textlayout[$i][1] = imagefontwidth($textlayout[$i][0]) * strlen($textlayout[$i][3]);
        
        $totalheight += $textlayout[$i][2];
    }
    
    // Línea central, inicio
    $centery = ($h - $totalheight) / 2;
    for ($i = 0; $i < count($textlayout); $i++) {
        $leftx = ($w - $textlayout[$i][1]) / 2;
        imagestring($im, $textlayout[$i][0], $leftx, $centery, $textlayout[$i][3], $textcolor);
        $centery += $textlayout[$i][2];
    }
    
    // Mostrar imagen;
    imagepng($im);
    imagedestroy($im);
}

function createProfileForm($langElastix)
{   
    $arrFields = array(
            "languageProfile"  => array("LABEL"                      => _tr("Language"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $langElastix,
                                            "INPUT_EXTRA_PARAM_OPTIONS" => array("class" => "form-control input-sm"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "currentPasswordProfile"   => array("LABEL"              => _tr("Current Password"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "PASSWORD",
                                            "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "id" => "currentPasswordProfile"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "newPasswordProfile"   => array("LABEL"                  => _tr("Password"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "PASSWORD",
                                            "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "id" => "newPasswordProfile", "disabled" => "disabled"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "repeatPasswordProfile"   => array("LABEL"               => _tr("Repeat Password"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "PASSWORD",
                                            "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "id" => "repeatPasswordProfile", "disabled" => "disabled"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "deleteImageProfile"   => array("LABEL"                  => _tr("Delete image"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "picture"                 => array("LABEL"               => _tr("Picture:"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "FILE",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "picture", "class"=>"picturePopupProfile"),
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                                                
                            );
    return $arrFields;
}

?>
