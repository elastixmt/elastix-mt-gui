<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
Codificación: UTF-8
+----------------------------------------------------------------------+
| Elastix version 1.4-1                                                |
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
$Id: index.php,v 1.1 20013-08-26 15:24:01 wreyes wreyes@palosanto.com Exp $ */
//include elastix framework
//include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoDB.class.php";
include_once "libs/paloSantoJSON.class.php";
include_once "libs/paloSantoFax.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //global variables
    global $arrConf;

    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);

    //return array("idUser"=>$idUser,"id_organization"=>$idOrganization,"userlevel"=>$userLevel1,"domain"=>$domain);
    global $arrCredentials;
    
    //actions
    $accion = getAction();
    
    switch($accion){
        case 'save':
            $content = saveExtensionSettings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'checkFaxStatus':
            $content = checkFaxStatus('getFaxStatus', $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'showSendFax':
            $content = showSendFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'sendNewFax':
            $content = sendNewFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break; 
        case 'faxAttachmentUpload':
            $content = faxAttachmentUpload($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break; 
        default:
            $content = showExtensionSettings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function showExtensionSettings($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    global $arrCredentials;

    $pMyFax=new paloMyFax($pDB,$arrCredentials['idUser']);

    if(getParameter('action')=='save'){
        $my_fax=$_POST;
    }else{
        $my_fax=$pMyFax->getMyFaxExtension();
    }

    if($my_fax==false){
        $smarty->assign("MSG_ERROR_FIELD",$pMyFax->getErrorMsg());
    }    

    $smarty->assign("EXTENSION_LABEL",_tr("Fax Extension:"));
    $smarty->assign("EXTENSION",$my_fax['FAX_EXTEN']);
    $smarty->assign("DEVICE_LABEL",_tr("Device:"));
    $smarty->assign("DEVICE",$my_fax['DEVICE']);
    $smarty->assign("STATUS_LABEL",_tr("Status:"));
    $smarty->assign("STATUS",$my_fax['STATUS']);
    $smarty->assign("FAX_EMAIL_SETTINGS",_tr("Fax email settings"));

    $my_fax['FAX_SUBJECT']= htmlentities($my_fax['FAX_SUBJECT'],ENT_QUOTES, "UTF-8");
    $my_fax['FAX_CONTENT']= htmlentities($my_fax['FAX_CONTENT'],ENT_QUOTES, "UTF-8");
    $my_fax['CID_NAME']= htmlentities($my_fax['CID_NAME'],ENT_QUOTES, "UTF-8");
    $my_fax['CID_NUMBER']= htmlentities($my_fax['CID_NUMBER'],ENT_QUOTES, "UTF-8");
    $my_fax['COUNTRY_CODE']= htmlentities($my_fax['COUNTRY_CODE'],ENT_QUOTES, "UTF-8");
    $my_fax['AREA_CODE']= htmlentities($my_fax['AREA_CODE'],ENT_QUOTES, "UTF-8");

    $session = getSession();
    $session['faxlistStatus'] = $my_fax['STATUS'];
    putSession($session);

    //contiene los elementos del formulario    
    $arrForm = createForm();
    $oForm = new paloForm($smarty,$arrForm);
    
    $html = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr('extension'),$my_fax);
    $contenidoModulo = "<div><form  method='POST' style='margin-bottom:0;' name='$module_name' id='$module_name' action='?menu=$module_name'>".$html."</form></div>";
    return $contenidoModulo;
}

function saveExtensionSettings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf){
    $jsonObject = new PaloSantoJSON();
    
    global $arrCredentials;

    $pMyFax=new paloMyFax($pDB,$arrCredentials['idUser']);
    $myFax['clid_name']=getParameter('CID_NAME'); 
    $myFax['clid_number']=getParameter('CID_NUMBER');
    $myFax['country_code']=getParameter('COUNTRY_CODE');
    $myFax['area_code']=getParameter('AREA_CODE'); 
    $myFax['fax_subject']=getParameter('FAX_SUBJECT');
    $myFax['fax_content']=getParameter('FAX_CONTENT');
    
    
    $pMyFax=new paloMyFax($pDB,$arrCredentials['idUser']);
    
    $pMyFax->_DB->beginTransaction();
    if(!$pMyFax->editFaxExten($myFax)){
        $pMyFax->_DB->rollBack();
        $jsonObject->set_error($pMyFax->getErrorMsg());
    }else{
        $pMyFax->_DB->commit();
        $jsonObject->set_message(_tr("Changes were saved succefully")." ".$pMyFax->getErrorMsg());
    }
    return $jsonObject->createJSON();
}

function checkFaxStatus($function, $module_name, $local_templates_dir, $pDB, $arrConf){
    $executed_time = 1; //en segundos
    $max_time_wait = 30; //en segundos
    $event_flag    = false;
    $data          = null;

    $i = 1;
    while(($i*$executed_time) <= $max_time_wait){
        $return = $function($module_name, $local_templates_dir, $pDB, $arrConf);
        $data   = $return['data'];
        if($return['there_was_change']){
            $event_flag = true;
            break;
        }
        $i++;
        sleep($executed_time); //cada $executed_time estoy revisando si hay algo nuevo....
    }
return $data;
}

function getFaxStatus($module_name, $local_templates_dir, &$pDB, $arrConf)
{
    global $arrCredentials;

    $pMyFax=new paloMyFax($pDB,$arrCredentials['idUser']);

    $jsonObject = new PaloSantoJSON();    
    $my_fax=$pMyFax->getMyFaxExtension();
    
    if($my_fax==false){   
        $status = FALSE;
    }else{       
        // 1 COMPARA EL VALOR DEVUELTO CON EL VALOR QUE ESTA EN SESION
        //SI HUBO UN CAMBIO
        // si hay cambio status true
        // poner el nuevo valor el seesion
        $session = getSession();        
        if($session['faxlistStatus']!= $my_fax['STATUS'])
        {
            $msgResponse = $my_fax['STATUS'];
            $status = true;
        }else{
            $status = false;
        }

        if($status){ //hubo un cambio
            $jsonObject->set_status("CHANGED");
            $jsonObject->set_message($msgResponse); //el valor del status actual
        }else{
            $jsonObject->set_status("NOCHANGED");
        }
    }
    
    $session['faxlistStatus'] = $my_fax['STATUS'];
    putSession($session);
    
    return array("there_was_change" => $status,
                "data" => $jsonObject->createJSON());
}

function getSession()
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $tmp = $_SESSION;
        session_commit();
    }
    return $tmp;
}

function putSession($data)//data es un arreglo
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $_SESSION = $data;
        session_commit();
    }
}

/*funcion que mouestra el popup de para enviar fax (send fax)*/
function showSendFax($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf){
    global $arrCredentials;
    $jsonObject = new PaloSantoJSON();
    $dataFax=array();
    $pMyFax=new paloMyFax($pDB,$arrCredentials['idUser']);
    
    // si la variable tiene $alias tiene un valor diferente de null, es 
    // porque se abrio el fax desde la ventana del chat, del cual se  obtendrá el número de fax del destinatario
    $alias=getParameter('alias');
    if(!empty($alias)){
        $username = explode("@", $alias);
        $mail=$username[0]."@".$arrCredentials['domain'];
        $destinationFax = $pMyFax->getFaxExtensionUserByUsername($mail);
        
        if($destinationFax==false){
            $jsonObject->set_error($pMyFax->getErrorMsg());
            return $jsonObject->createJSON();
        }
        $dataFax['destinationFaxNumber']= htmlentities($destinationFax,ENT_QUOTES, "UTF-8");
    }
    
    $my_fax=$pMyFax->getMyFaxExtension();
     
    if($my_fax==false){
        $smarty->assign("MSG_ERROR_FIELD",$pMyFax->getErrorMsg());
    } 
    //$my_fax['MODEM']
    $device=$my_fax['CID_NAME']." / ".$my_fax['CID_NUMBER'];
    
    $smarty->assign("FAX_DEV",$my_fax['MODEM']);
    $smarty->assign("TITLE_POPUP",_tr("Send Fax"));
    $smarty->assign("file_upload",_tr("File Upload"));
    $smarty->assign("note",_tr("Note: Types of files supported: pdf, tiff, txt"));
    $smarty->assign("SEND_FAX",_tr("Send"));
    $smarty->assign("CANCEL",_tr("Cancel"));
    $smarty->assign("faxDeviceLabel",_tr("Fax Device to use:"));
    $smarty->assign("faxDevice",$device);//CID_NAME / CID_NUMBER
    $smarty->assign("FAX_DEV",$my_fax['MODEM']);//ttyIAX
    
    $arrFormFilter = createSendFaxForm();
    $oFilterForm = new paloForm($smarty, $arrFormFilter);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/sendfax.tpl",_tr('Send Fax'), $dataFax);
    $jsonObject = new PaloSantoJSON();
    $jsonObject->set_message($htmlFilter);
    return $jsonObject->createJSON();
}

function sendNewFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf)
{
    global $arrCredentials;
    $jsonObject = new PaloSantoJSON();
    $pSendFax = new paloMyFax($pDB,$arrCredentials['idUser']);
    $my_fax=$pSendFax->getMyFaxExtension();
    
    if($my_fax==false){
        $smarty->assign("MSG_ERROR_FIELD",$pSendFax->getErrorMsg());
    } 
    
    $faxDev = $my_fax['MODEM'];
    $destine = getParameter('to');
    $data_content = getParameter('body');
    $checked = getParameter('checked');
    $ruta_archivo = "";
    
    if(empty($destine)){
        $jsonObject->set_error(_tr('Destination fax numbers is empty'));
        return $jsonObject->createJSON();
    }
    
    if($checked=="true"){
        if(!empty($_SESSION['faxFileAattached'])){
        $ruta_archivo = $_SESSION['faxFileAattached'];
        }
    }else{
        if (!empty($data_content)) {
            /* Las siguientes operaciones son necesarias para lidiar con el
                * bug Elastix #446. El programa sendfax es incapaz de detectar
                * un archivo como un archivo de texto si contiene caracteres 
                * fuera del rango ASCII.*/ 
            $ruta_archivo = $pSendFax->generarArchivoTextoPS($data_content);
            if (is_null($ruta_archivo)) {
                $jsonObject->set_error(_tr('Failed to convert text'));
                return $jsonObject->createJSON();
            }
        } else {
            $jsonObject->set_error(_tr('Text to send is empty'));
            return $jsonObject->createJSON();
        } 
    }

    $jobid = $pSendFax->sendFax($faxDev, $destine, $ruta_archivo);
    if (is_null($jobid)) {
        $jsonObject->set_error(_tr('Failed to submit job'));
        return $jsonObject->createJSON();
    } else {
        $jsonObject->set_message(_tr('Fax has been sent correctly'));
    }
    
    if (file_exists($ruta_archivo)) unlink($ruta_archivo);
    if(!empty($_SESSION['faxFileAattached'])){
        unset($_SESSION['faxFileAattached']);
    }
    
    return $jsonObject->createJSON();

}

function faxAttachmentUpload($smarty, $module_name, $local_templates_dir, $pDB, $arrConf)
{ 
    $jsonObject = new PaloSantoJSON();
    $tmpfaxdocs= "/var/www/elastixdir/tmpfaxdocs/";
    
    if (!file_exists($tmpfaxdocs)) {
        $jsonObject->set_error(_tr("Error uploading your file"));
        return $jsonObject->createJSON();
    }
    
    foreach ($_FILES['faxFile']['error'] as $key => $error)
    {
        if ($error == UPLOAD_ERR_OK)
        {   
            $tmpFileName = tempnam($tmpfaxdocs,"");
            if ($tmpFileName==false){
                $jsonObject->set_error(_tr("Error uploading your file"));
                return $jsonObject->createJSON();
            }
            
            if(move_uploaded_file( $_FILES['faxFile']['tmp_name'][$key], $tmpFileName)===false){
                $jsonObject->set_error(_tr("Failed to move file"));
                return $jsonObject->createJSON();
            }
            
            if(empty($_SESSION['faxFileAattached'])){
                $_SESSION['faxFileAattached'] = $tmpFileName;
            }else{
                if(!empty($_SESSION['faxFileAattached'])){
                    unlink($_SESSION['faxFileAattached']);
                    unset($_SESSION['faxFileAattached']);
                    $_SESSION['faxFileAattached'] = $tmpFileName;
                }
            }  
            $jsonObject->set_message($_SESSION['faxFileAattached']);
        }else{
            $jsonObject->set_error(_tr("Error uploading your file"));
        }
    }
    return $jsonObject->createJSON();
}


function createForm(){
    $arrForm = array("CID_NAME"        => array("LABEL"                  => _tr("CID NAME:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => "CID NAME"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "CID_NUMBER"  => array("LABEL"               => _tr("CID Number:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => "12345"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "COUNTRY_CODE"  => array("LABEL"               => _tr("Country Code:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => "593"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "AREA_CODE"  => array("LABEL"               => _tr("Area Code:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => "04"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "FAX_SUBJECT"  => array("LABEL"               => _tr("Fax Subject:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => "Fax Subject"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "FAX_CONTENT"  => array("LABEL"               => _tr("Fax content:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXTAREA",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => "Fax content"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),

    );
    return $arrForm;
}

function createSendFaxForm()
{   
    $arrFields = array(
                                                  
            "destinationFaxNumber"   => array("LABEL"                  => _tr("Destination fax numbers:"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "id" => "newPasswordProfile"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "faxContent"              => array("LABEL"               => _tr("Email content:"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => "Fax content"),
                                            "VALIDATION_TYPE"        => "text",
                                            "ROWS"                   => "8",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "faxFile"                 =>   array("LABEL"             => _tr("Upload File:"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "FILE",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "faxFile", "class"=>"faxFileUpload"),
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                                                
                            );
    return $arrFields;
}

function getAction()
{
    if(getParameter('action')=='editFaxExten'){
        return 'save';
    }elseif (getParameter('action')=='checkFaxStatus'){
        return 'checkFaxStatus';
    }elseif (getParameter('action')=='showSendFax'){
        return 'showSendFax';
    }elseif (getParameter('action')=='sendNewFax'){
        return 'sendNewFax';
    }elseif (getParameter('action')=='faxAttachmentUpload'){
        return 'faxAttachmentUpload';
    }else{
        return "show";
    }
}


?>
