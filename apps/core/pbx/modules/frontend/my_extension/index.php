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
    $content = "";
    
    switch($accion){
        case 'save':
            $content = saveExtensionSettings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
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

    $pMyExten=new paloMyExten($pDB,$arrCredentials['idUser']);

    
    if(getParameter('action')=='save'){
        $my_exten=$_POST;
    }else{
        $my_exten=$pMyExten->getMyExtension();
    }

    if($my_exten==false){
        $smarty->assign("MSG_ERROR_FIELD",$pMyExten->getErrorMsg());
    }

    $smarty->assign("DISPLAY_NAME_LABEL",_tr("Display Name CID:"));
    $smarty->assign("clid_name",$my_exten['clid_name']);
    $smarty->assign("DISPLAY_EXT_LABEL",_tr("Extension number:"));
    $smarty->assign("DISPLAY_DEVICE_LABEL",_tr("Device:"));
    $smarty->assign("device",$my_exten['device']);
    $smarty->assign("extension",$my_exten['extension']);
    $smarty->assign("DISPLAY_CFC_LABEL",_tr("Call Forward Configuration"));
    $smarty->assign("DISPLAY_CMS_LABEL",_tr("Call Monitor Settings"));
    $smarty->assign("DISPLAY_VOICEMAIL_LABEL",_tr("Voicemail Configuration"));
   //$smarty->assign("SAVE_CONF_BTN",_tr("Save Configuration"));
   // $smarty->assign("CANCEL_BTN",_tr("Cancel"));

    //contiene los elementos del formulario    
    $arrForm = createForm();
    $oForm = new paloForm($smarty,$arrForm);
    
    $html = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr('extension'),$my_exten);
    $contenidoModulo = "<div><form  method='POST' style='margin-bottom:0;' name='$module_name' id='$module_name' action='?menu=$module_name'>".$html."</form></div>";
   
    return $contenidoModulo;
}

function saveExtensionSettings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf){
    $jsonObject = new PaloSantoJSON();
    
    global $arrCredentials;

    $pMyExten=new paloMyExten($pDB,$arrCredentials['idUser']);
    $myExten['secret']=getParameter('secretExtension'); 
    $myExten['language']=getParameter('language_vm');

    $myExten['doNotDisturb']=getParameter('doNotDisturb');
    $myExten['callwaiting']=getParameter('callWaiting');
    $myExten['callForwardOpt']=getParameter('callForwardOpt'); //n
    $myExten['callForwardUnavailableOpt']=getParameter('callForwardUnavailableOpt'); //n
    $myExten['callForwardBusyOpt']=getParameter('callForwardBusyOpt'); //n
    $myExten['callForwardInp']=getParameter('callForwardInp'); //n
    $myExten['callForwardUnavailableInp']=getParameter('callForwardUnavailableInp'); //n
    $myExten['callForwardBusyInp']=getParameter('callForwardBusyInp'); //n
    $myExten['record_in']=getParameter('recordIncoming'); 
    $myExten['record_out']=getParameter('recordOutgoing'); 
    $myExten['create_vm']=getParameter('status_vm');
    $myExten['vmemail']=getParameter('email_vm');
    $myExten['vmpassword']=getParameter('password_vm');
    $myExten['vmattach']=getParameter('emailAttachment_vm');
    $myExten['vmsaycid']=getParameter('playCid_vm');
    $myExten['vmenvelope']=getParameter('playEnvelope_vm');    
    $myExten['vmdelete']=getParameter('deleteVmail');
    
    $pMyExten=new paloMyExten($pDB,$arrCredentials['idUser']);
    
    $pMyExten->_DB->beginTransaction();
    if(!$pMyExten->editExten($myExten)){
        $pMyExten->_DB->rollBack();
        $jsonObject->set_error($pMyExten->getErrorMsg());
        //$jsonObject->set_error($myExten);
    }else{
        $pMyExten->_DB->commit();
        //$jsonObject->set_message($myExten);
    $jsonObject->set_message("Changes were saved succefully");
    }

    return $jsonObject->createJSON();
}

function createForm(){
    $DND[]=array("id"=>'radio1',"label"=>_tr('Enable'),"value"=>"yes");
    $DND[]=array("id"=>'radio2',"label"=>_tr('Disable'),"value"=>"no");

    $CW[]=array("id"=>'radio3',"label"=>_tr('Enable'),"value"=>"yes");
    $CW[]=array("id"=>'radio4',"label"=>_tr('Disable'),"value"=>"no");

    $CF[]=array("id"=>'radio5',"label"=>_tr('Enable'),"value"=>"yes");
    $CF[]=array("id"=>'radio6',"label"=>_tr('Disable'),"value"=>"no");

    $CFU[]=array("id"=>'radio7',"label"=>_tr('Enable'),"value"=>"yes");
    $CFU[]=array("id"=>'radio8',"label"=>_tr('Disable'),"value"=>"no");

    $CFB[]=array("id"=>'radio9',"label"=>_tr('Enable'),"value"=>"yes");
    $CFB[]=array("id"=>'radio10',"label"=>_tr('Disable'),"value"=>"no");

    $record_incoming[]=array("id"=>'radio11',"label"=>_tr('Always'),"value"=>"always");
    $record_incoming[]=array("id"=>'radio12',"label"=>_tr('Never'),"value"=>"never");
    $record_incoming[]=array("id"=>'radio13',"label"=>_tr('On-Demand'),"value"=>"on_demand");

    $record_outgoing[]=array("id"=>'radio14',"label"=>_tr('Always'),"value"=>"always");
    $record_outgoing[]=array("id"=>'radio15',"label"=>_tr('Never'),"value"=>"never");
    $record_outgoing[]=array("id"=>'radio16',"label"=>_tr('On-Demand'),"value"=>"on_demand");

    $status[]=array("id"=>'radio17',"label"=>_tr('Enable'),"value"=>"yes");
    $status[]=array("id"=>'radio18',"label"=>_tr('Disable'),"value"=>"no");
    

    $email_attachment[]=array("id"=>'radio19',"label"=>_tr('Yes'),"value"=>"yes");
    $email_attachment[]=array("id"=>'radio20',"label"=>'NO',"value"=>"no");

    $PCID[]=array("id"=>'radio21',"label"=>_tr('Yes'),"value"=>"yes");
    $PCID[]=array("id"=>'radio22',"label"=>'NO',"value"=>"no");

    $play_envelope[]=array("id"=>'radio23',"label"=>_tr('Yes'),"value"=>"yes");
    $play_envelope[]=array("id"=>'radio24',"label"=>'NO',"value"=>"no");

    $delete_vmail[]=array("id"=>'radio25',"label"=>_tr('Yes'),"value"=>"yes");
    $delete_vmail[]=array("id"=>'radio26',"label"=>'NO',"value"=>"no");

    $arrLang=getLanguagePBX();



    $arrForm = array("secretExtension"   => array("LABEL"                  => _tr("Secret extension:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "mail"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "doNotDisturb"  => array("LABEL"               => _tr("Do Not Disturb:"),
                                                "DESCRIPTION"            => _tr("Enable/Disable the Don't Disturb"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $DND,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "callWaiting"  => array("LABEL"               => _tr("Call Waiting :"),
                                                "DESCRIPTION"            => _tr("Enable/Disable the call waiting"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $CW,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "callForwardOpt"  => array("LABEL"               => _tr("Call Forward:"),
                                                "DESCRIRPTION"           => _tr("Enable/Disable the call waiting"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $CF,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
            "callForwardUnavailableOpt"  => array("LABEL"               => _tr("Call Forward on Unavailable:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $CFU,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                    "callForwardBusyOpt"  => array("LABEL"               => _tr("Call Forward on Busy:"),
                                                "DESCRIPTION"            => _tr("Enable/Disable the call fordward on busy"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $CFB,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "callForwardInp"  => array("LABEL"               => _tr(""),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control", "placeholder" => "12345"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
            "callForwardUnavailableInp"  => array("LABEL"               => _tr(""),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control", "placeholder" => "12345"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                "callForwardBusyInp"  => array("LABEL"               => _tr(""),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control", "placeholder" => "12345"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "recordIncoming"  => array("LABEL"               => _tr("Record Incoming:"),
                                                "DESCRIRPTION"           => _tr("Selects the frequency with that uses this option"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $record_incoming,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),

                        "recordOutgoing"  => array("LABEL"               => _tr("Record Outgoing:"),
                                                "DESCRIRPTION"           => _tr("Selects the frequency with that uses this option"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $record_outgoing,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "status_vm"   => array("LABEL"               => _tr("Status:"),
                                                "DESCRIRPTION"           => _tr("Enable/Disable the Voicemail Configuration"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $status,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "email_vm"   => array( "LABEL"                    => _tr("Email:"),
                                                "DESCRIRPTION"           => _tr("Defines the email"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control", "placeholder" => "Enter email"),
                                                "VALIDATION_TYPE"        => "email",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "password_vm"  => array("LABEL"               => _tr("Password:"),
                                                "DESCRIPTION"            => _tr("Defines your password"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control", "placeholder" => "Password"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "language_vm"  => array("LABEL"               => _tr("Language:"),
                                                "DESCRIPTION"            => _tr("Select the language for voice recording"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "SELECT",
                                                "INPUT_EXTRA_PARAM"      => $arrLang,
                                                "INPUT_EXTRA_PARAM_OPTIONS" => array("class" => "form-control input-sm"),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "emailAttachment_vm"   => array("LABEL"               => _tr("Email Attachment:"),
                                                "DESCRIPTION"            => _tr("Allow attachment files to mail"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $email_attachment,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "playCid_vm"   => array("LABEL"               => _tr("Play CID:"),
                                                "DESCRIPTION"            => _tr("Enable/Disable the play CID Option"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $PCID,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "playEnvelope_vm"   => array("LABEL"               => _tr("Play Envelope:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $play_envelope,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "deleteVmail"   => array("LABEL"               => _tr("Delete Vmail:"),
                                                "DESCRIPTION"            => _tr("Enable/Disable the delete Vmail"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $delete_vmail,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                     
    );
     
    return $arrForm;
}
function getAction()
{
    if(getParameter('action')=='editExten'){
        return 'save';
    }else
        return "show";
}

?>
