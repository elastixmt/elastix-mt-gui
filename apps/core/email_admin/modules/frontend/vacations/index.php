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

include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoDB.class.php";
include_once "libs/paloSantoJSON.class.php";
include_once "apps/antispam/libs/paloSantoAntispam.class.php";
include_once "apps/antispam/libs/sieve-php.lib.php";

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
            $content = saveVacationSettings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default:
            $content = showVacationSettings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function showVacationSettings($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    global $arrCredentials;

    $email=$_SESSION['elastix_user'];

    $pVacations=new paloMyVacation($pDB,$arrCredentials['idUser']);
    $objAntispam = new paloSantoAntispam($arrConf['path_postfix'], $arrConf['path_spamassassin'],$arrConf['file_master_cf'], $arrConf['file_local_cf']);

    $statusSieve = $pVacations->verifySieveStatus();
    
    if(!$statusSieve['response']){
        $smarty->assign("MSG_ERROR_FIELD",$statusSieve['message']);
    }


    if(getParameter('action')=='save'){
        $my_vacation=$_POST;
    }else{
        $my_vacation=$pVacations->getVacationByUser();
    }

    if($my_vacation==false){
        $smarty->assign("MSG_ERROR_FIELD",$pVacations->getErrorMsg());
    }elseif($my_vacation == "default-vacation") {
        $my_vacation=array();
        $my_vacation[_tr('FROM')]= date('Y-m-d');
        $my_vacation[_tr('TO')]= date('Y-m-d');
        $my_vacation['EMAIL_ADDRESS']= $_SESSION['elastix_user'];
        $my_vacation['EMAIL_SUBJECT']= "Auto-Reply: Out of the office";
        $my_vacation['EMAIL_CONTENT']= "I will be out of the office until {END_DATE}. \n\n----\nBest Regards.";
    }else{
        $my_vacation[_tr('FROM')]= $my_vacation['init_date'];
        $my_vacation[_tr('TO')]= $my_vacation['end_date'];
        $my_vacation['EMAIL_ADDRESS']= $_SESSION['elastix_user'];
        $my_vacation['EMAIL_SUBJECT']= htmlentities($my_vacation['email_subject'],ENT_QUOTES, "UTF-8");
        $my_vacation['EMAIL_CONTENT']= htmlentities($my_vacation['email_body'],ENT_QUOTES, "UTF-8");
    }

        
    $smarty->assign("PERIOD_LABEL",_tr("Period:"));
    $smarty->assign("STATUS_LABEL",_tr("Status:"));
    $smarty->assign("FAX_EMAIL_SETTINGS",_tr("Fax email settings"));

    //contiene los elementos del formulario    
    $arrForm = createForm();
    $oForm = new paloForm($smarty,$arrForm);
    
    $html = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr('vacation'),$my_vacation);
    $contenidoModulo = "<div><form  method='POST' style='margin-bottom:0;' name='$module_name' id='$module_name' action='?menu=$module_name'>".$html."</form></div>";
    return $contenidoModulo;
    
    
}

function saveVacationSettings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf){
    global $arrCredentials;

    $objAntispam = new paloSantoAntispam($arrConf['path_postfix'], $arrConf['path_spamassassin'],$arrConf['file_master_cf'], $arrConf['file_local_cf']);

    $pVacations=new paloMyVacation($pDB,$arrCredentials['idUser']);

    $jsonObject = new PaloSantoJSON();

    $myVacation['init_date']=getParameter('intiDate'); 
    $myVacation['end_date']=getParameter('endDate');
    $myVacation['email_subject']=getParameter('emailSubject');
    $myVacation['email_body']=getParameter('emailBody');
    
    $email=$_SESSION['elastix_user'];
    $subject=$myVacation['email_subject'];
    $body=$myVacation['email_body'];
    $ini_date=$myVacation['init_date'];
    $end_date=$myVacation['end_date'];

    $timestamp0 = mktime(0,0,0,date("m"),date("d"),date("Y"));
    $timestamp1 = mktime(0,0,0,date("m",strtotime($ini_date)),date("d",strtotime($ini_date)),date("Y",strtotime($ini_date)));    
    $timeSince = $timestamp0 - $timestamp1;

    if($timeSince >= 0){
        $myVacation['vacation']="yes";
    }
    else{
        $myVacation['vacation']="no";
    }
    $scripts = $objAntispam->existScriptSieve($email, "scriptTest.sieve");
    $spamCapture = false;// si CapturaSpam=OFF y Vacations=OFF
    if($scripts['actived'] != ""){// hay un script activo
        if(preg_match("/scriptTest.sieve/",$scripts['actived'])) // si CapturaSpam=ON y Vacations=OFF
            $spamCapture = true;// si CapturaSpam=ON y Vacations=OFF
    } 

    $pVacations->_DB->beginTransaction();
    if(!$pVacations->editVacation($myVacation)){
        $pVacations->_DB->rollBack();
        $jsonObject->set_error($pVacations->getErrorMsg());
        return $jsonObject->createJSON();
    }else{
        //mandamos a actualizar el script del sieve
        if($timeSince >= 0){
            $body = str_replace("{END_DATE}", $end_date, $body);
            $result = $pVacations->uploadVacationScript($email, $subject, $body, $objAntispam, $spamCapture);
        }else    
            $result = true;
        if($result){
            $pVacations->_DB->commit();
            $jsonObject->set_message("Changes were saved succefully");
        }else{
            $pVacations->_DB->rollBack();
            $jsonObject->set_error($pVacations->getErrorMsg());
        }
        return $jsonObject->createJSON();
    }
}

function createForm(){
    $arrForm = array("CID_NAME"        => array("LABEL"                  => _tr("CID NAME:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => "12345"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                                "FROM"  => array("LABEL"               => _tr("From:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("id"=>"inputFrom", "class" => "form-control input-sm", "placeholder" => "yyyy-mm-dd"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                                    "TO"  => array("LABEL"               => _tr("To:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("id"=>"inputTo", "class" => "form-control input-sm", "placeholder" => "yyyy-mm-dd"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                "EMAIL_ADDRESS"   => array( "LABEL"                    => _tr("Email Address:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control", "placeholder" => "Enter email"),
                                                "VALIDATION_TYPE"        => "email",
                                                "VALIDATION_EXTRA_PARAM" => ""),  
                        "EMAIL_SUBJECT"  => array("LABEL"               => _tr("Email Subject:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => "Email Subject"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "EMAIL_CONTENT"  => array("LABEL"               => _tr("Email content:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXTAREA",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => "Email content"),
                                                "VALIDATION_TYPE"        => "text",
                                                "ROWS"                   => "4",
                                                "VALIDATION_EXTRA_PARAM" => ""),

    );
    return $arrForm;
}
function getAction()
{
    if(getParameter('action')=='editVacation'){
        return 'save';
    }else{
        return "show";
    }
}


?>
