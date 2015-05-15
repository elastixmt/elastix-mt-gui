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
  $Id: index.php,v 1.1 2009-12-14 02:12:12 Oscar Navarrete J. onavarrete@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoFax.class.php";
require_once 'libs/paloSantoJSON.class.php';

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);

    //user credentials
    global $arrCredentials;
    
    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save_new":
            $content = sendNewSendFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'checkFaxStatus':
            $content = checkFaxStatus();
            break;
        default: // view_form
            $content = viewFormSendFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function viewFormSendFax($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    global $arrCredentials;
    $pSendFax = new paloSantoSendFax($pDB);
    $pFaxList = new paloFax($pDB);

    //obtenemos el fax del usuario
    //para eso necesitamos el numero de extension del usuario
    $faxexten=$pSendFax->getFaxExtensionUser($arrCredentials['idUser']);
    if($faxexten===false || $faxexten==''){
        $smarty->assign("mb_title", _tr('ERROR'));
        $smarty->assign("mb_message","An error has ocurred to retrieved fax info. ".$faxexten->errMsg);
        return '';
    }
    
    $faxes      = $pFaxList->getFaxList(array('organization_domain'=>$arrCredentials['domain'],'exten'=>$faxexten));
    $arrFaxList = array("none"=>'-- '._tr("Select a Fax Device").' --');
    foreach($faxes as $values){
        $smarty->assign("FAX_USER",$values['clid_name']." / ".$values['exten']);
        $smarty->assign("FAX_DEV","ttyIAX".$values['dev_id']);
    }
    
    if($faxes==false){
        $smarty->assign("mb_title", _tr('ERROR'));
        $smarty->assign("mb_message", _tr("An error has ocurred to retrieved fax info"));
        return '';
    }else{
        $arrFormSendFax = createFieldForm();
        $oForm = new paloForm($smarty,$arrFormSendFax);

        //begin, Form data persistence to errors and other events.
        $_DATA  = $_POST;
        $action = getParameter("action");
        $id     = getParameter("id");
        $smarty->assign("ID", $id); //persistence id with input hidden in tpl

        //Lo q hace es ckeckear por default Text Information
        if(isset($_POST['option_fax']) && $_POST['option_fax']=='by_file')
            $smarty->assign("check_file", "checked");
        else
            $smarty->assign("check_text", "checked");

        $smarty->assign("SEND", _tr("Send"));
        $smarty->assign("EDIT", _tr("Edit"));
        $smarty->assign("CANCEL", _tr("Cancel"));
        $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
        $smarty->assign("icon", "web/apps/$module_name/images/fax_virtual_fax_send_fax.png");

        /*if($userLevel1=="other"){
            $smarty->assign("isOther", false);
            $smarty->assign("FAX_USER",array_pop($arrFaxList));
        }else{
            $smarty->assign("isOther", true);
        }*/
        
        //News
        $smarty->assign("file_upload", _tr("File Upload"));
        $smarty->assign("text_area", _tr("Text Information"));
        $smarty->assign("record_Label", _tr("Select the files to FAX"));
        $smarty->assign("type_files", _tr("Types of files"));

        
        $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Send Fax"), $_DATA);
        $content = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

        return $content;
    }
}

function sendNewSendFax($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    global $arrCredentials;
    $pSendFax = new paloSantoSendFax($pDB);
    $pFaxList = new paloFax($pDB);
    $ruta_archivo = "";
    
    //obtenemos el fax del usuario
    //para eso necesitamos el numero de extension del usuario
    $faxexten=$pSendFax->getFaxExtensionUser($arrCredentials['idUser']);
    if($faxexten===false || $faxexten==''){
        $smarty->assign("mb_title", _tr('ERROR'));
        $smarty->assign("mb_message","An error has ocurred to retrieved fax info. ".$faxexten->errMsg);
        return viewFormSendFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    
    $faxes      = $pFaxList->getFaxList(array('organization_domain'=>$arrCredentials['domain'],'exten'=>$faxexten));
    if($faxes==false){
        $smarty->assign("mb_title", _tr('ERROR'));
        $smarty->assign("mb_message", _tr("An error has ocurred to retrieved fax info"));
        return viewFormSendFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    $faxDev="ttyIAX".$faxes[0]['dev_id'];
    $smarty->assign("FAX_DEV",$faxDev);
    
    $arrFormSendFax = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormSendFax);

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
        return viewFormSendFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    else{
        $destine      = getParameter("to");
        //$faxDev     = getParameter("from");
        $data_content = getParameter("body");

        /*if($faxDev == "none"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Please, select a valid Fax Device"));
            return viewFormSendFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
        }*/

        if(getParameter("option_fax")=="by_file"){
            if(is_uploaded_file($_FILES['file_record']['tmp_name'])) {
                $ruta_archivo = $_FILES['file_record']['tmp_name'];
            } else {
                $smarty->assign("mb_title", _tr("Validation Error"));
                $smarty->assign("mb_message", _tr("File to upload is empty"));
                return viewFormSendFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            }
        }else{
            if (!empty($data_content)) {
                /* Las siguientes operaciones son necesarias para lidiar con el
                 * bug Elastix #446. El programa sendfax es incapaz de detectar
                 * un archivo como un archivo de texto si contiene caracteres 
                 * fuera del rango ASCII. */
                $ruta_archivo = $pSendFax->generarArchivoTextoPS($data_content);
                if (is_null($ruta_archivo)) {
                    $smarty->assign(array(
                        'mb_title'      =>  _tr('Validation Error'),
                        'mb_message'    =>  _tr('Failed to convert text'),
                    ));
                    return viewFormSendFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
                }
            } else {
                $smarty->assign("mb_title", _tr("Validation Error"));
                $smarty->assign("mb_message", _tr("Text to send is empty"));
                return viewFormSendFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            }
        }
    
        $jobid = $pSendFax->sendFax($faxDev, $destine, $ruta_archivo);
        if (is_null($jobid)) {
            $smarty->assign("mb_title", _tr('Validation Error'));
            $smarty->assign("mb_message", _tr('Failed to submit job').': '.$pSendFax->errMsg);
        } else {
            $smarty->assign("SEND_FAX_SUCCESS",_tr('Fax has been sent correctly'));
            $smarty->assign("SENDING_FAX",_tr('Sending Fax...'));
            $smarty->assign('JOBID', $jobid);
        }
        if (file_exists($ruta_archivo)) unlink($ruta_archivo);
        return  viewFormSendFax($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
}

function checkFaxStatus()
{
    session_commit();
    $jobid = getParameter('jobid');
    $modem = getParameter('modem');
    $oldhash = getParameter('outputhash');

    $startTime = time();
    do {
        $faxinfo = paloFax::getFaxStatus();
        $faxstatus = array(
            'state' => 'F',
            'modemstatus' => '(invalid modem)',
            'status' => '(invalid jobid)'
        );
        if (isset($faxinfo['modems'][$modem]))
            $faxstatus['modemstatus'] = $modem.': '.$faxinfo['modems'][$modem];
        if (isset($faxinfo['jobs'][$jobid]))
            $faxstatus = array_merge($faxstatus, $faxinfo['jobs'][$jobid]);

        $newhash = md5(serialize($faxstatus));
        if ($oldhash == $newhash) usleep(2 * 1000000);
    } while($oldhash == $newhash && time() - $startTime < 30);

    $jsonObject = new PalosantoJSON();
    $jsonObject->set_status(($oldhash != $newhash) ? 'CHANGED' : 'NOCHANGED');
    $jsonObject->set_message(array('faxstatus' => $faxstatus, 'outputhash' => $newhash));
    Header('Content-Type: application/json');
    return $jsonObject->createJSON();
}

function createFieldForm()
{
    $arrFaxList=array();
    $arrFields = array(
            "to"   => array(      "LABEL"                  => _tr("Destination fax numbers"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:300px","maxlength" =>"300"),
                                            "VALIDATION_TYPE"        => "numeric",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "from"   => array(      "LABEL"                  => _tr("Fax Device to use"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrFaxList,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "body"   => array(      "LABEL"                  => _tr("Text to FAX"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"      => array("cols" => "80", "rows" => "12"),
                                            "VALIDATION_TYPE"        => "text",
                                            "EDITABLE"               => "si",
                                            "COLS"                   => "50",
                                            "ROWS"                   => "4",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            );
    return $arrFields;
}

function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
    elseif (getParameter('action') == 'checkFaxStatus')
        return 'checkFaxStatus';
    else
        return "report"; //cancel
}
?>