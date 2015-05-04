<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 3.0.0                                                |
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
  +----------------------------------------------------------------------+*/
//include elastix framework

include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoJSON.class.php";
include_once "libs/paloSantoGrid.class.php";
require_once "libs/paloComposeEmail.php";

define('PATH_UPLOAD_ATTACHS','/var/www/elastixdir/uploadAttachs');
/*
  @author: index.php,v 1 2013/05/09 01:07:03 Washington Reyes wreyes@palosanto.com Exp $
  @author: index.php,v 2 2013/09/10 01:07:03 Rocio Mera rmera@palosanto.com Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    //global variables
    global $arrConf;
    global $arrCredentials;
   // global $arrConfModule;
    //$arrConf = array_merge($arrConf,$arrConfModule);
  
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    $pImap = new paloImap();
    
    //actions
    $accion = getAction();
    
    switch($accion){
        case "view_bodymail":
            $content = viewMail($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "download_attach":
            $content = download_attach($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "get_inline_attach":
            $content = inline_attach($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "mv_msg_to_folder":
            $content = moveMsgsToFolder($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "mark_msg_as":
            $content = markMsgAs($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "delete_msg_trash":
            $content = deleteMsgTrash($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "toggle_important":
            $content = toogle_important_msg($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "create_mailbox":
            $content = create_mailbox($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "get_templateEmail":
            $content = get_templateEmail($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "compose_email":
            $content = compose_email($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "attach_file":
            $content = attachFile($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "deattach_file":
            $content = dettachFile($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "forwardGetAttachs":
            $content = forwardGetAttachs($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        case "refreshMail":
            $content = refreshMail('checkmail',$smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
        default:
            $content = reportMail($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
            break;
    }
    return $content;
}

function reportMail($smarty, $module_name, $local_templates_dir, & $arrConf, &$pImap)
{
    $jsonObject = new PaloSantoJSON();
    $arrFilter=array();
    
    //obtenemos el mailbox que deseamos leer
    $mailbox=getParameter('folder');
    $action=getParameter('action');
    
    //creamos la connección al mailbox
    $pImap->setMailbox($mailbox);
    $smarty->assign("CURRENT_MAILBOX",$pImap->getMailbox());
    
    $result=$pImap->login($_SESSION['elastix_user'], $_SESSION['elastix_pass2']);
    if($result===false){
        if($action=='show_messages_folder'){
            $jsonObject->set_error($pImap->errMsg);
            return $jsonObject->createJSON();
        }else{
            $smarty->assign("ERROR_FIELD",$pImap->errMsg);
            return '';
        }
    }
    
    $listMailbox=$pImap->getMailboxList();
    if($result===false){
        $jsonObject->set_error($pImap->errMsg);
        $smarty->assign("ERROR_FIELD",$pImap->errMsg);
        return '';
    }else{
        $smarty->assign('MAILBOX_FOLDER_LIST',$listMailbox);
        $smarty->assign('NEW_FOLDER',_tr('New Folder'));
    }
    
    
    $view_filter_opt['all']=_tr("All");
    $view_filter_opt['seen']=_tr("Seen");
    $view_filter_opt['unseen']=_tr("Unseen");
    $view_filter_opt['flagged']=_tr("Important");
    $view_filter_opt['unflagged']=_tr("No Important");
    $smarty->assign("ELX_MAIL_FILTER_OPT",$view_filter_opt);
    
    $filter_view='all';
    $tmp_filter_view=getParameter('email_filter1');
    if(array_key_exists($tmp_filter_view,$view_filter_opt)){
        $filter_view=$tmp_filter_view;
    }
    $arrFilter=array("filter_view"=>$filter_view);
    
    //obtenemos el numero de correos que ahi en el buzon
    //filtrando por los parámetros dados
    $listUID=array();
    $total = $pImap->getNumMails($arrFilter,$listUID);
    if($total===false){
        $total=0;
        $jsonObject->set_error($pImap->errMsg);
        $smarty->assign("ERROR_FIELD",$pImap->errMsg);
    }
    
    $limit=50;
    //sacamos calculamos el offset
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $currentPage = $oGrid->calculateCurrentPage();
    $numPage = $oGrid->calculateNumPage();
    $url['menu']=$module_name;
    $url['email_filter1']=$filter_view;
    
    $oGrid->setTitle(_tr('Contacts List'));
    $oGrid->setURL($url);
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    
    $smarty->assign("TOTAL_MAILS",$total);
    $smarty->assign("CURRENT_PAGMAIL",$currentPage);
    $smarty->assign("NUM_PAGMAIL",$numPage);
    $smarty->assign("PAGINA",_tr("Page"));
    $smarty->assign("MESSAGES_LABEL",_tr("Messages"));
    
    $arrData=array();
    if($total!=0){
        $pImap->setMessageByPage($limit);
        $pImap->setOffset($offset);
        $emails = $pImap->readMails($listUID);
        if($emails!==false){
            foreach($emails as $email){
                $tmp=array();
                $class='elx_unseen_email';
                if($email['SEEN']==1){
                    $class='elx_seen_email';
                }
                $tmp[]="<div class='elx_row $class' id={$email['UID']}>";
                $tmp[]="<div class='ic'>";
                $tmp[]="<div class='sel'><input type='checkbox' value='{$email['UID']}' class='inp1 checkmail'/></div>";
                //$tmp[]="<div class='icon'><img border='0' src='web/apps/home/images/mail2.png' class='icn_buz'></div>";
                $class='elx_unflagged_email';
                if($email['FLAGGED']==1){
                    $class='elx_flagged_email';
                }
                $tmp[]="<div class='star'><span class='$class glyphicon glyphicon-star'></span></div>";
                $tmp[]="</div>";
                //$tmp[]="<div class='email_msg_attr'>";
                $tmp[]="<div class='from  elx_row_email_msg'> <span>".htmlentities($email['from'],ENT_COMPAT,'UTF-8')."</span></div>";
                $tmp[]="<div class='subject elx_row_email_msg'> <span>".htmlentities($email['subject'],ENT_COMPAT,'UTF-8')."</span></div>";
                $tmp[]="<div class='date elx_row_email_msg'><span>".$email['date']."</span></div>";
                //$tmp[]="</div>";
                $tmp[]="</div>";
                $arrData[]=$tmp;
            }
            $smarty->assign("MAILS",$arrData);
        }else{
            $jsonObject->set_error($pImap->errMsg);
            $smarty->assign("ERROR_FIELD",$pImap->errMsg);
        }
    }

    $pImap->close_mail_connection();
    $imapAlertErros=cleanAlertsImap();
    
    $listMailbox=array_diff($listMailbox,array($pImap->getMailbox()));
    $move_folder=array();
    foreach($listMailbox as $value){
        $move_folder[$value]=$value;
    }
    $smarty->assign("MOVE_FOLDERS",$move_folder);
    
    if($action=='show_messages_folder'){
        $message['email_content']=$arrData;
        $message['email_filter1']=$filter_view;
        $message['move_folders']=$move_folder;
        $message['imap_alerts']=$imapAlertErros;
        $message['paging']['total']=$total;
        $message['paging']['currentPage']=$currentPage;
        $message['paging']['numPages']=$numPage;
        $jsonObject->set_message($message);
        return $jsonObject->createJSON();
    }
    
    $smarty->assign("IMAP_ALERTS",$imapAlertErros);
    $smarty->assign("ICON_TYPE","web/apps/$module_name/images/mail2.png");
    $smarty->assign("FOLDER_LIST_TITLE",_tr("Folders"));
    
    $mark_opt['seen']=_tr("Seen");
    $mark_opt['unseen']=_tr("Unseen");
    $mark_opt['flagged']=_tr("Important");
    $mark_opt['unflagged']=_tr("No Important");
    $smarty->assign("ELX_MAIL_MARK_OPT",$mark_opt);
    $smarty->assign("MOVE_TO",_tr("Move to"));
    $smarty->assign("MARK_AS",_tr("Mark message as"));
    
    $smarty->assign("NO_EMAIL_MSG",_tr("Not messages"));
    $smarty->assign("VIEW",_tr("View"));
    $smarty->assign("SELECTED_VIEW_FILTER",$filter_view);
    $smarty->assign("SEND_MAIL_LABEL",_tr("Send"));
    $smarty->assign("ATTACH_LABEL",_tr("Attach"));
    
    $smarty->assign("ACTION_MSG", _tr('Actions'));
    $arrActionsMsg['reply']=_tr('Reply');
    $arrActionsMsg['reply_all']=_tr('Reply All');
    $arrActionsMsg['forward']=_tr('Forward');
    $arrActionsMsg['delete']=_tr('Delete');
    $arrActionsMsg['flag_important']=_tr('Flag as Important');
    $arrActionsMsg['flag_unimportant']=_tr('Flag as Unimportant');
    $smarty->assign("ELX_EMAIL_MSG_ACT", $arrActionsMsg);
    
    $html = $smarty->fetch("file:$local_templates_dir/form.tpl");
    $contenidoModulo = "<div>".$html."</div>";
    return $contenidoModulo;
}
//si ocurre algun error o alguna excepcion al invocar una funcion de la
//libreria imap de php esta se agrega al arreglo imap_errors e imap_alerts 
//Si no se invoca a las funciones imap_alerts y imap_errors php muestra estos
//como e_notice lo que daña el ajax
function cleanAlertsImap(){
    $alerts=$errors='';
    
    $arrAlerts=imap_alerts();
    if(is_array($arrAlerts))
        $alerts=implode("<br>",$arrAlerts);
        
    $arrErrors=imap_errors();
    if(is_array($arrErrors))
        $errors=implode("<br>",$arrErrors);
        
    if($alerts!='' && $errors!=''){
        $text=$alerts."<br>".$errors;
    }else{
        $text=$alerts.$errors;
    }
    return $text;
}
function moveMsgsToFolder($smarty, $module_name, $local_templates_dir, & $arrConf, &$pImap){
    $jsonObject = new PaloSantoJSON();
   
    //lista de UIDs de mensajes a mover
    $lisUIDs=getParameter('UIDs');
    if(empty($lisUIDs)){
        $jsonObject->set_error(_tr("At_least_one"));
        return $jsonObject->createJSON();
    }
    
    $arrUID=array_diff(explode(",",$lisUIDs),array(''));
    if(!is_array($arrUID) || count($arrUID)==0){
        $jsonObject->set_error(_tr("At_least_one"));
        return $jsonObject->createJSON();
    }
    
    //carpetas a la que queremos mover los mensajes seleccionados
    $new_folder=getParameter('new_folder');
    
    //current mailbox
    $mailbox=getParameter('current_folder');
    
    //creamos la connección al mailbox
    $pImap->setMailbox($mailbox);

    $result=$pImap->login($_SESSION['elastix_user'], $_SESSION['elastix_pass2']);
    if($result===false){
        $jsonObject->set_error($pImap->errMsg);
    }else{
        if(!$pImap->moveMsgToFolder($mailbox,$new_folder,$arrUID)){
            $jsonObject->set_error($pImap->errMsg);
        }else{
            $jsonObject->set_message(_tr("Success_mv"));
        }
    }
    
    cleanAlertsImap();
    $pImap->close_mail_connection();
    return $jsonObject->createJSON();
}
function markMsgAs($smarty, $module_name, $local_templates_dir, & $arrConf, &$pImap){
    $jsonObject = new PaloSantoJSON();
   
    //current mailbox
    $mailbox=getParameter('current_folder');
    
    //creamos la connección al mailbox
    $pImap->setMailbox($mailbox);

    //lista de UIDs de mensajes a mover
    $lisUIDs=getParameter('UIDs');
    if(empty($lisUIDs)){
        $jsonObject->set_error(_tr("At_least_one"));
        return $jsonObject->createJSON();
    }
    
    $arrUID=array_diff(explode(",",$lisUIDs),array(''));
    if(!is_array($arrUID) || count($arrUID)==0){
        $jsonObject->set_error(_tr("At_least_one"));
        return $jsonObject->createJSON();
    }
    
    //carpetas a la que queremos mover los mensajes seleccionados
    $tag=getParameter('tag');
    
    $result=$pImap->login($_SESSION['elastix_user'], $_SESSION['elastix_pass2']);
    if($result===false){
        $jsonObject->set_error($pImap->errMsg);
    }else{
        if(!$pImap->markMsgFolder($tag,$arrUID)){
            $jsonObject->set_error($pImap->errMsg);
        }else{
            $jsonObject->set_message(_tr("Success_tag"));
        }
    }
    
    cleanAlertsImap();
    $pImap->close_mail_connection();
    return $jsonObject->createJSON();
}
function toogle_important_msg($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap){
    $jsonObject = new PaloSantoJSON();
   
    //current mailbox
    $mailbox=getParameter('current_folder');
    
    //creamos la connección al mailbox
    $pImap->setMailbox($mailbox);

    //uid del mensaje que vamos a marcar
    $uid=getParameter("uid");
    if(is_null($uid) || $uid=='' || $uid===false){
        $jsonObject->set_error('Invalid Email Message');
        return $jsonObject->createJSON();
    }
        
    //como vamos a marcar el mensaje
    $tag=getParameter('tag');
    if($tag!='flagged' && $tag!='unflagged'){
        $jsonObject->set_error('Invalid Action');
        return $jsonObject->createJSON();
    }
    
    $arrUID[]=$uid;
    
    $result=$pImap->login($_SESSION['elastix_user'], $_SESSION['elastix_pass2']);
    if($result===false){
        $jsonObject->set_error($pImap->errMsg);
    }else{
        if(!$pImap->markMsgFolder($tag,$arrUID)){
            $jsonObject->set_error($pImap->errMsg);
        }else{
            $jsonObject->set_message(_tr("Success_tag"));
        }
    }
    
    cleanAlertsImap();
    $pImap->close_mail_connection();
    return $jsonObject->createJSON();
}

function deleteMsgTrash($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap){
    $jsonObject = new PaloSantoJSON();
       
    //creamos la connección al mailbox
    $pImap->setMailbox("Trash");

    //lista de UIDs de mensajes a mover
    $lisUIDs=getParameter('UIDs');
    if(empty($lisUIDs)){
        $jsonObject->set_error(_tr("At_least_one"));
        return $jsonObject->createJSON();
    }
    
    $arrUID=array_diff(explode(",",$lisUIDs),array(''));
    if(!is_array($arrUID) || count($arrUID)==0){
        $jsonObject->set_error(_tr("At_least_one"));
        return $jsonObject->createJSON();
    }
    
    $result=$pImap->login($_SESSION['elastix_user'], $_SESSION['elastix_pass2']);
    if($result===false){
        $jsonObject->set_error($pImap->errMsg);
    }else{
        if(!$pImap->deleteMsgTrash($arrUID)){
            $jsonObject->set_error($pImap->errMsg);
        }else{
            $jsonObject->set_message(_tr("Success_del"));
        }
    }
    
    cleanAlertsImap();
    $pImap->close_mail_connection();
    return $jsonObject->createJSON();
}
function viewMail($smarty, $module_name, $local_templates_dir, & $arrConf, &$pImap)
{
    $jsonObject = new PaloSantoJSON();

    $mailbox=getParameter('current_folder');
    $pImap->setMailbox($mailbox);
    
    $uid=getParameter("uid");
    if(is_null($uid) || $uid=='' || $uid===false || $uid=='undefined'){
        $jsonObject->set_error('Invalid Email Message');
        return $jsonObject->createJSON();
    }
    
    $result=$pImap->login($_SESSION['elastix_user'], $_SESSION['elastix_pass2']);
    if($result===false){
        $jsonObject->set_error($pImap->errMsg);
    }else{
        $result=$pImap->readEmailMsg($uid);
        if($result===false){
            $jsonObject->set_error($pImap->errMsg);
        }else{
            $jsonObject->set_message($result);
        }
    }
    
    cleanAlertsImap();
    $pImap->close_mail_connection();
    return $jsonObject->createJSON();
}
function create_mailbox($smarty, $module_name, $local_templates_dir,  $arrConf, &$pImap){
    $jsonObject = new PaloSantoJSON();

    $new_mailbox=getParameter("new_folder"); 
    if(is_null($new_mailbox) || $new_mailbox=='' || $new_mailbox===false){
        $jsonObject->set_error('Invalid Mailbox');
        return $jsonObject->createJSON();
    }
    
    $result=$pImap->login($_SESSION['elastix_user'], $_SESSION['elastix_pass2']);
    if($result===false){
        $jsonObject->set_error($pImap->errMsg);
    }else{
        $result=$pImap->createMailbox($new_mailbox);
        if($result===false){
            $jsonObject->set_error($pImap->errMsg);
        }
    }
    
    cleanAlertsImap();
    $pImap->close_mail_connection();
    return $jsonObject->createJSON();
}
function download_attach($smarty, $module_name, $local_templates_dir,  $arrConf, &$pImap){
    $jsonObject = new PaloSantoJSON();

    $mailbox=getParameter('current_folder');
    $pImap->setMailbox($mailbox);
    
    $uid=getParameter("uid");
    if(is_null($uid) || $uid=='' || $uid===false){
        $jsonObject->set_error('Invalid Email Message');
        return $jsonObject->createJSON();
    }
    
    $result=$pImap->login($_SESSION['elastix_user'], $_SESSION['elastix_pass2']);
    if($result===false){
        return '';
    }else{
        $partNum=getParameter('partnum');
        $encoding=getParameter('enc');
        
        $pMessage=new paloImapMessage($pImap->getConnection(),$uid,$mailbox);
        $result=$pMessage->downloadAttachment($partNum, $encoding);
    }
    cleanAlertsImap();
    $pImap->close_mail_connection();
    return;
}
function inline_attach($smarty, $module_name, $local_templates_dir,  $arrConf, &$pImap){
    //$jsonObject = new PaloSantoJSON();

    $mailbox=getParameter('current_folder');
    $pImap->setMailbox($mailbox);
    
    $result=$pImap->login($_SESSION['elastix_user'], $_SESSION['elastix_pass2']);
    if($result===false){
        return '';
    }
    
    $uid=getParameter("uid");
    if(is_null($uid) || $uid=='' || $uid===false){
        return '';
    }
    
    $partNum=getParameter('partnum');
    $encoding=getParameter('enc');
    
    $pMessage=new paloImapMessage($pImap->getConnection(),$uid,$pImap->getMailbox());
    $pMessage->getInlineAttach($partNum, $encoding);
    
    cleanAlertsImap();
    $pImap->close_mail_connection();
    return;
}
function get_templateEmail($smarty, $module_name, $local_templates_dir,  $arrConf, &$pImap){
    $jsonObject = new PaloSantoJSON();
    global $arrCredentials;
    
    $alias=getParameter('destination');
    if(!empty($alias)){
        $username = explode("@", $alias);
        $destination=$username[0]."@".$arrCredentials['domain'];
        $smarty->assign("USERNAME",$destination);
    }
    
    $smarty->assign("TO", _tr('To'));
    $smarty->assign("CC", _tr('CC'));
    $smarty->assign("BCC", _tr('BCC'));
    $smarty->assign("REPLYTO", _tr('Reply to'));
    $smarty->assign("SUBJECT", _tr('Subject'));
    
    //eliminamos el contenido de la variable de session elastix_emailAttachs
    //esta variable contiene el nombre de los archivos atachados
    unset($_SESSION['elastix_emailAttachs']);
    
    //es necesario obtener el lenguage del usuario para la traduccion del lenguage
    //para el complemento que se usa para componer los mensajes
    global $arrCredentials;
    $idUser = $arrCredentials['idUser'];
    $lang = 'en'; //default language
    
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
    $pACL = new paloACL($pDB);
    $defLang=$pACL->getUserProp($idUser,'language');
    if($defLang==false)
        $lang=$defLang;
    unset($_SESSION['elastix_emailAttachs']);
    //unset($_POST['?menu=home&action=attach_file&rawmode=yes']);
    //unset($_FILES['attachFileButton']);
    
    $smarty->assign("USER_LANG",$lang);
    $smarty->assign("MSG_EMPTYTO",_tr("Field 'To' can not be empty"));
    $smarty->assign("MSG_SUBJECT",_tr("Do you wish send this message with empty subject"));
    $smarty->assign("MSG_CONTENT",_tr("Do you wish send this message with empty body"));
    $smarty->assign("TEXT_UPLOADING", _tr("Uploading.."));
    
    
    $internalContacts = $pImap->getListInternalContacts();
    $externalContacts = $pImap->getListExternalContacts();
    
    $arrContacts=array();
    
    if (is_array($internalContacts)){
        foreach($internalContacts as $contact){
            $arrContacts[]=htmlentities($contact['name'],ENT_COMPAT,'UTF-8')." <".htmlentities($contact['username'],ENT_COMPAT,'UTF-8').">";
        }
    }
    
    if (is_array($externalContacts)){
        foreach($externalContacts as $contact){
            $arrContacts[]=htmlentities($contact['name'],ENT_COMPAT,'UTF-8')." <".htmlentities($contact['username'],ENT_COMPAT,'UTF-8').">";
        }
    }
    
    $contenidoModulo = $smarty->fetch("file:$local_templates_dir/compose.tpl");
    $arrData=array();
    $arrData['modulo']=$contenidoModulo;
    $arrData['contacts']=$arrContacts;

    $jsonObject->set_message($arrData);
    return $jsonObject->createJSON();
}
function compose_email($smarty, $module_name, $local_templates_dir,  $arrConf, &$pImap){
    $jsonObject = new PaloSantoJSON();
    global $arrCredentials;
    $idUser = $arrCredentials['idUser'];
    
    //obtenemos el name del usuario
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
    $pACL = new paloACL($pDB);
    $result=$pACL->getUsers2($idUser);
    if($result==false){
        $jsonObject->set_error("Error to get user info");
        return $jsonObject->createJSON();
    }else{
        $name=$result[0]['name'];
    }
    
    $pCompose = new paloComposeEmail($_SESSION['elastix_user'], $_SESSION['elastix_pass2'], $name, $pImap);
    
    $headers['to']=getParameter("to");
    $headers['cc']=getParameter("cc");
    $headers['bcc']=getParameter("bcc");
    $headers['reply_to']=getParameter("reply_to");
    $subject=getParameter("subject");
    $content=getParameter("bodyMsg");
    $attachments=null;
    if(isset($_SESSION['elastix_emailAttachs'])){
        $pCompose->setAttachments($_SESSION['elastix_emailAttachs']);
    }
    
    if($pCompose->sendEmail($headers,$subject,$content)){
        $strError=$pCompose->getErrorMsg();
        $jsonObject->set_message(_tr("Message was sent successfully.")." $strError");
        unset($_SESSION['elastix_emailAttachs']);
    }else{
        $jsonObject->set_error($pCompose->getErrorMsg());
    }
    
    return $jsonObject->createJSON();
}
function forwardGetAttachs($smarty, $module_name, $local_templates_dir,  $arrConf, &$pImap){
    $jsonObject = new PaloSantoJSON();

    $mailbox=getParameter('current_folder');
    $pImap->setMailbox($mailbox);
    
    $uid=getParameter("uid");
    if(is_null($uid) || $uid=='' || $uid===false || $uid=='undefined'){
        $jsonObject->set_error('Invalid Email Message');
        return $jsonObject->createJSON();
    }
    
    $result=$pImap->login($_SESSION['elastix_user'], $_SESSION['elastix_pass2']);
    if($result===false){
        $jsonObject->set_error($pImap->errMsg);
    }else{
        $pMessage=new paloImapMessage($pImap->getConnection(),$uid,$pImap->getMailbox());
        $attachments=$pMessage->createAttachmentsToForward();
        if($attachments===false){
            $jsonObject->set_error($pMessage->errMsg);
        }else{
            $jsonObject->set_message($attachments);
        }
    }
    
    cleanAlertsImap();
    $pImap->close_mail_connection();
    return $jsonObject->createJSON();
}
function attachFile($smarty, $module_name, $local_templates_dir,  $arrConf, &$pImap){
    $jsonObject = new PaloSantoJSON();
    
    try {
        if(!defined("PATH_UPLOAD_ATTACHS")){
            throw new RuntimeException(_tr("Error uploading your file.")._tr("Temporary directory does not exist"));
        }
        
        if (!is_dir(PATH_UPLOAD_ATTACHS)) {
            throw new RuntimeException(_tr("Error uploading your file.")._tr("Temporary directory does not exist"));
        }
        
        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if ( !isset($_FILES['attachFileButton']['error'][0])) {
            throw new RuntimeException('Invalid parameters.');
        }
        
        // Check $_FILES['upfile']['error'] value.
        switch ($_FILES['attachFileButton']['error'][0]) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Exceeded filesize limit.'); //chequea el limite seteado en php.ini
            default:
                throw new RuntimeException('Unknown errors.');
        }

        // You should also check filesize here. 
        // TODO:definir cual debe ser el máximo tamaño de los archivos adjuntos 
        // o sacarlo de lagun archivo de ocnfiguracion
        $uploadSize='10000000'; //10Mega Bytes
        if ($_FILES['attachFileButton']['size'][0] > $uploadSize) {
            throw new RuntimeException('Exceeded filesize limit.');
        }

        if(preg_match("/(\.php)/",$_FILES['attachFileButton']['name'][0])) {
            throw new RuntimeException('Possible file upload attack.');
        }
        
        //En los archivos adjuntos todos los tipo están permitidos
        //TODO: Definir cual es la lista de archivos adjunto que están permitidos
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimetype = $finfo->file($_FILES["attachFileButton"]["tmp_name"][0]);
        
        //Movemos el archivo subido a la ruta definida en PATH_UPLOAD_ATTACHS
        //aqui se almacenan hasta ser enviados
        //Para asegurarnos de que el archvo subido tenga un nombre único
        //usamos la funcion tempnam para crear un archivo temporal con nombre único
        //y después usamos ese nombre para el archivo que subido
        $tmpFileName = tempnam(PATH_UPLOAD_ATTACHS,"");
        if ($tmpFileName==false){
            throw new RuntimeException('Failed to create temporary file file.');
        }
        
        if(!move_uploaded_file( $_FILES['attachFileButton']['tmp_name'][0], $tmpFileName)){
            throw new RuntimeException('Failed to move uploaded file.');
        }
        
        //una vez que el archivo a sido subido procedemos a guardar
        //la información de este en sesión para luego ser adjunto al momento de enviar el correo
        $idAttach=md5($tmpFileName);
        $_SESSION["elastix_emailAttachs"][$idAttach]['filename']=$tmpFileName;
        $_SESSION["elastix_emailAttachs"][$idAttach]['name']=$_FILES['attachFileButton']['name'][0];
        $_SESSION["elastix_emailAttachs"][$idAttach]['mime']=$mimetype;
        $_SESSION["elastix_emailAttachs"][$idAttach]['type']="file";
        
        $jsonObject->set_message(array('idAttach'=>$idAttach,'name'=>htmlentities($_FILES['attachFileButton']['name'][0],ENT_COMPAT,'UTF-8')));
        
        unset($_POST);
        unset($_FILES);
    } catch (RuntimeException $e) {
        $jsonObject->set_error($e->getMessage());
    }
    return $jsonObject->createJSON();
}
/*
function addInlineImage(){
    try {
        if(!defined("PATH_UPLOAD_ATTACHS")){
            throw new RuntimeException(_tr("Error uploading your file.")._tr("Temporary directory does not exist"));
        }
        
        if (!file_exists(PATH_UPLOAD_ATTACHS)) {
            $jsonObject->set_error(_tr("Error uploading your file")._tr("Temporary directory does not exist"));
            return $jsonObject->createJSON();
        }
        
        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if ( !isset($_FILES['attachFileButton']['error']) ||
            is_array($_FILES['attachFileButton']['error']) ) {
            throw new RuntimeException('Invalid parameters.');
        }

        // Check $_FILES['upfile']['error'] value.
        switch ($_FILES['attachFileButton']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Exceeded filesize limit.');
            default:
                throw new RuntimeException('Unknown errors.');
        }

        // You should also check filesize here. 
        if ($_FILES['upfile']['size'] > 1000000) {
            throw new RuntimeException('Exceeded filesize limit.');
        }

        // DO NOT TRUST $_FILES['upfile']['type'] VALUE !!
        // Check MIME Type by yourself.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($_FILES['upfile']['tmp_name']),
            array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ),
            true
        )) {
            throw new RuntimeException('Invalid file format.');
        }

        // You should name it uniquely.
        // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
        // On this example, obtain safe unique name from its binary data.
        if (!move_uploaded_file(
            $_FILES['upfile']['tmp_name'],
            sprintf('./uploads/%s.%s',
                sha1_file($_FILES['upfile']['tmp_name']),
                $ext
            )
        )) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        echo 'File is uploaded successfully.';

    } catch (RuntimeException $e) {

        echo $e->getMessage();

    }
}*/
function dettachFile($smarty, $module_name, $local_templates_dir,  $arrConf, &$pImap){
    $jsonObject = new PaloSantoJSON();
    $idAttach=getParameter('idAttach');
    try {
        if(is_string($idAttach) && $idAttach!=''){
            
            if(!defined("PATH_UPLOAD_ATTACHS")){
                throw new RuntimeException(_tr("Error uploading your file.")._tr("Temporary directory does not exist"));
            }
            
            if (!is_dir(PATH_UPLOAD_ATTACHS)) {
                throw new RuntimeException(_tr("Error uploading your file.")._tr("Temporary directory does not exist"));
            }
            
            $filename=$_SESSION['elastix_emailAttachs'][$idAttach]['filename'];
            $name=basename($filename);
            if(is_file(PATH_UPLOAD_ATTACHS."/$name")){
                unlink(PATH_UPLOAD_ATTACHS."/$name");
            }
        }
    }catch (RuntimeException $e){
        $jsonObject->set_error($e->getMessage());
    }
    return $jsonObject->createJSON();
}
function refreshMail($function,$smarty, $module_name, $local_templates_dir,  $arrConf, $pImap){
    $executed_time = 30; //en segundos
    $max_time_wait = 120; //en segundos
    $event_flag    = false;
    $data          = null;

    $i = 1;
    while(($i*$executed_time) <= $max_time_wait){
        $return = $function($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap);
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
function checkmail($smarty, $module_name, $local_templates_dir,  $arrConf, $pImap){
    $jsonObject = new PaloSantoJSON();
    $flag=false;
    
    //obtenemos la lista de mails y vemos si ahi algun cambio
    //obtenemos el mailbox que deseamos leer
    $mailbox=getParameter('folder');
    $action=getParameter('action');
    
    session_commit();
    ini_set("session.use_cookies","0");
    
    //creamos la connección al mailbox
    $pImap->setMailbox($mailbox);
    
    $result=$pImap->login($_SESSION['elastix_user'], $_SESSION['elastix_pass2']);
    if($result==false){
        $jsonObject->set_status("ERROR");
        $jsonObject->set_error($pImap->errMsg);
        $flag=true;
    }else{
        $result=$pImap->getRecentMessage();
        if($result===false){
            $jsonObject->set_status("ERROR");
            $jsonObject->set_error($pImap->errMsg);
            $flag=true;
        }else{
            if($result==0){
                $jsonObject->set_status("NOCHANGED");
            }else{
                $jsonObject->set_status("CHANGED");
                $flag=true;
            }
        }
    }
    
    cleanAlertsImap();
    $pImap->close_mail_connection();
    return array('there_was_change'=>$flag,'data'=>$jsonObject->createJSON());
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
function getAction()
{
    if(getParameter("action")=="view_bodymail"){
      return "view_bodymail";  
    }elseif(getParameter("action")=="mv_msg_to_folder"){
      return "mv_msg_to_folder";  
    }elseif(getParameter("action")=="mark_msg_as"){
      return "mark_msg_as";  
    }elseif(getParameter("action")=="delete_msg_trash"){
      return "delete_msg_trash";  
    }elseif(getParameter("action")=="toggle_important"){
      return "toggle_important";  
    }elseif(getParameter("action")=="create_mailbox"){
      return "create_mailbox";
    }elseif(getParameter("action")=="download_attach"){
      return "download_attach";  
    }elseif(getParameter("action")=="get_inline_attach"){
      return "get_inline_attach";
    }elseif(getParameter("action")=="get_templateEmail"){
      return "get_templateEmail";
    }elseif(getParameter("action")=="compose_email"){
      return "compose_email";
    }elseif(getParameter("action")=="attach_file"){
      return "attach_file";
    }elseif(getParameter("action")=="deattach_file"){
      return "deattach_file";
    }elseif(getParameter("action")=="forwardGetAttachs"){
      return "forwardGetAttachs";
    }elseif(getParameter("action")=="refreshMail"){
      return "refreshMail";
    }else
      return "report";
}
?>
