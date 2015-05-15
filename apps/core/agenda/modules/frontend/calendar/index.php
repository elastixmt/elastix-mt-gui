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
  $Id: index.php,v 1.1 2010-01-05 11:01:26 Bruno Macias V.  bmacias@elastix.org Exp $
  $Id: index.php,v 1.2 2010-11-10 11:00:00 Eduardo Cueva D. ecueva@palosanto.com Exp $ */

//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoACL.class.php";
include_once "libs/phpmailer/class.phpmailer.php";
include_once "libs/paloSantoJSON.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoCalendar.class.php";
//    include_once "modules/$module_name/libs/JSON.php";

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save_new":
            $content = saveEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "edit":
            $content = viewForm_NewEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "delete":
            $content = deleteEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "save_edit":
            $content = saveEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "get_lang":
            $content = getLanguages($arrLangModule, $arrConf);
            break;
        case "get_data":
            $content = getDataCalendar($arrLang,$pDB,$module_name,$arrConf);
            break;
        case "get_num_ext":
            $content = getNumExtesion($arrConf, $pDB, $arrLang);
            break;
        case "setData":
            $content = setDataCalendar($arrLang,$pDB,$arrConf);
            break;
        case "view_box":
            $content = viewBoxCalendar($arrConf,$arrLang,$pDB,$local_templates_dir,$smarty,$module_name);
            break;
        case "new_box":
            $content = newBoxCalendar($arrConf,$arrLang,$pDB,$local_templates_dir,$smarty,$module_name);
            break;
        case "delete_box":
            $content = deleteBoxCalendar($arrConf,$arrLang,$pDB,$module_name);
            break;
        case "download_icals":
            $content = download_icals($arrLang,$pDB,$module_name, $arrConf);
            break;
        case "get_contacts2":
            $content = getContactEmails2($arrConf);
            break;
        case "getTextToSpeach":
            $content = getTextToSpeach($arrLang,$pDB);
            break;
        case "display":
            $content = viewCalendarById($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
        case "phone_numbers":

            // Include language file for EN, then for local, and merge the two.
            $arrLangModule = NULL;
            include_once("modules/address_book/lang/en.lang");
            $lang_file="modules/address_book/lang/$lang.lang";
            if (file_exists("$base_dir/$lang_file")) {
                $arrLanEN = $arrLangModule;
                include_once($lang_file);
                $arrLangModule = array_merge($arrLanEN, $arrLangModule);
            }
            $arrLang = array_merge($arrLang, $arrLangModule);

                //solo para obtener los devices (extensiones) creadas.
            $dsnAsterisk = generarDSNSistema('asteriskuser', 'asterisk');
            $pDB_addressbook = new paloDB($arrConf['dsn_conn_database3']);
            $pDB_acl = new paloDB($arrConf['dsn_conn_database1']);
            $html = report_adress_book($smarty, $module_name, $local_templates_dir, $pDB_addressbook, $pDB_acl, $arrLang, $dsnAsterisk);
            $smarty->assign("CONTENT", $html);
            $smarty->assign("THEMENAME", $arrConf['mainTheme']);
            $smarty->assign("path", "");
            $content = $smarty->display("$local_templates_dir/address_book_list.tpl");
            break;
        default: // view_form
            $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function getNameDayToday($arrLang)
{
    $arrDay = array(
        1 => $arrLang["Monday"],
        2 => $arrLang["Tuesday"],
        3 => $arrLang["Wednesday"],
        4 => $arrLang["Thursday"],
        5 => $arrLang["Friday"],
        6 => $arrLang["Saturday"],
        7 => $arrLang["Sunday"]
    );
    $today = date("N");
    return $arrDay[$today];
}

function viewCalendar($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pCalendar = new paloSantoCalendar($pDB);

    $arrForm = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrForm);

	$id_event="";
	$visibility_alert="";
	$visibility_emails="";

    $date_ini = getParameter("event_date");
    $date_end = getParameter("to");

    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user,$arrConf);

    $festival = $pCalendar->festivalUp(); // verifica si esta levantado el festival
    if(!$festival){
		$smarty->assign("mb_title", _tr("Message"));
        $smarty->assign("mb_message", $arrLang['Festival is not up']);
    }
                    // yyyy-mm-dd
    if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$date_ini))
        $date_ini = date("M d Y");

                         //D M d Y H:i:s TO (e)
    $dateServer = gmdate("D M d Y H:i:s TO (e)", strtotime($date_ini));//Fri Nov 12 2010 00:00:00 GMT-0500 (ECT)
    $icalFile = $arrLang["Download ical calendar"];

	$smarty->assign("add_phone",$arrLang["Search in Address Book"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("Start_date", $arrLang["Start_date"]);
    $smarty->assign("Notification_Alert", $arrLang["Notification_Alert"]);
    $smarty->assign("End_date", $arrLang["End_date"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("module_name", $module_name);
    $smarty->assign("notification_email", $arrLang["notification_email"]);
    $smarty->assign("id_event",$id_event);
    $smarty->assign("Call_alert",$arrLang["Call_alert"]);
    $smarty->assign("visibility_emails",$visibility_emails);
    $smarty->assign("Export_Calendar",$arrLang["Export_Calendar"]);
    $smarty->assign("ical",$icalFile);
    $smarty->assign("icon", "modules/$module_name/images/agenda_calendar.png");
    $smarty->assign("visibility_alert", $visibility_alert);
    $smarty->assign("LBL_EDIT", $arrLang["Edit Event"]);
    $smarty->assign("LBL_LOADING", $arrLang["Loading"]);
    $smarty->assign("LBL_DELETING", $arrLang["Deleting"]);
    $smarty->assign("LBL_SENDING", $arrLang["Sending Request"]);
    $smarty->assign("START_TYPE", $arrLang["START_TYPE"]);
    $smarty->assign("DATE_SERVER", $dateServer);
    $smarty->assign("Color", $arrLang["Color"]);
    $smarty->assign("CreateEvent", $arrLang["Create New Event"]);
    $smarty->assign("Listen", $arrLang["Listen"]);
    $smarty->assign("Listen_here", _tr("Click here to listen"));

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Calendar"], array());
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name' name='formCalendar' id='formCalendar'>".$htmlForm."</form>";


    return $content;
}

function viewCalendarById($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang)
{
    $pCalendar = new paloSantoCalendar($pDB);

    $arrForm = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrForm);

    //$_DATA['ReminderTime'] = isset($_DATA['ReminderTime'])?$_DATA['ReminderTime']:"10";

    $date_ini = getParameter("event_date");
    $id = getParameter("id");

    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user,$arrConf);

    $festival = $pCalendar->festivalUp(); // verifica si esta levantado el festival
    if(!$festival){
		$smarty->assign("mb_title", _tr("Message"));
        $smarty->assign("mb_message", $arrLang['Festival is not up']);
    }
                    // yyyy-mm-dd
    if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$date_ini))
        $date_ini = date("M d Y");

    if(!preg_match("/^[1-9][0-9]*$/",$id))
        $id = "";

                         //D M d Y H:i:s TO (e)
    $dateServer = gmdate("D M d Y H:i:s TO (e)", strtotime($date_ini));//Fri Nov 12 2010 00:00:00 GMT-0500 (ECT)
    $icalFile = $arrLang["Download ical calendar"];

    $smarty->assign("ID",$id);
    $smarty->assign("module_name", $module_name);
    $smarty->assign("Export_Calendar",$arrLang["Export_Calendar"]);
    $smarty->assign("ical",$icalFile);
    $smarty->assign("LBL_EDIT", $arrLang["Edit Event"]);
    $smarty->assign("LBL_LOADING", $arrLang["Loading"]);
    $smarty->assign("LBL_DELETING", $arrLang["Deleting"]);
    $smarty->assign("LBL_SENDING", $arrLang["Sending Request"]);
    $smarty->assign("START_TYPE", $arrLang["START_TYPE"]);
    $smarty->assign("DATE_SERVER", $dateServer);
    $smarty->assign("CreateEvent", $arrLang["Create New Event"]);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Calendar"], array());
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name' name='formCalendar' id='formCalendar'>".$htmlForm."</form>";

    return $content;
}

function saveEvent($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang){

    $pCalendar = new paloSantoCalendar($pDB);
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user,$arrConf);
    $pDB3 = new paloDB($arrConf['dsn_conn_database1']);
    $ext = $pCalendar->obtainExtension($pDB3,$uid);
    $_DATA              = $_POST;

    $action             = getParameter("action");
    $id                 = getParameter("id_event");
    $event              = getParameter("event");
    $description        = getParameter("description");
    $date_ini           = getParameter("date");
    $date_end           = getParameter("to");
    $color              = getParameter("colorHex");
    // options call reminder
    $reminder           = getParameter("reminder"); // puede ser on o off
    $call_to            = getParameter("call_to"); // elemento Call to
    $remainerTime       = getParameter("ReminderTime"); // tiempo de recordatorio 10, 20, 30 minutos antes
    $recording          = getParameter("tts");
    // options email notification
    $notification       = getParameter("notification");      // puede ser on o off
    $notification_email = getParameter("notification_email"); // si es notification==off => no se toma en cuenta esta variable
    $list               = getParameter("emails");

    $hora               = date('H',strtotime($date_ini));
    $minuto             = date('i',strtotime($date_ini));
    $hora2              = date('H',strtotime($date_end));
    $minuto2            = date('i',strtotime($date_end));
    $asterisk_calls     = "";
    $each_repeat        = 1;
    $repeat             = "none";
    $event_type = 0;

    if (!ctype_digit($id)) $id = NULL;

    if(!preg_match("/^#\w{3,6}$/",$color))
        $color = "#3366CC";

    $_GET['event_date'] = date("Y-m-d", strtotime($date_ini));

    $start_event   = strtotime($date_ini);
    $end_event     = strtotime($date_end);
    $end_event2    = $end_event;
    //validar si la primera fecha es menor que la segunda
    if($event != ""){
        if($start_event <= $end_event){
            if($reminder == "on"){ //Configure a phone call reminder
                $asterisk_calls = $reminder;
                if($asterisk_calls == "on"){ // si es on entonces el campo call_to es vacio
                    if($call_to==null || $call_to==""){
                        $link = "<a href='?menu=userlist'>".$arrLang['user_list']."</a>";
                        $smarty->assign("mb_message", $arrLang['error_ext'].$link);
                        $content = viewForm_NewEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                        return $content;
                    }
                }else{// se asigna una extension cualquiera
                    if($call_to==""){
                        $smarty->assign("mb_message", $arrLang['error_call_to']);
                        $content = viewForm_NewEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                        return $content;
                    }
                }

                // Número a llamar sólo puede ser numérico
                if (!preg_match('/^\d+$/', $call_to)) {
                    $smarty->assign("mb_message", _tr('Invalid extension to call for reminder'));
                    $content = viewForm_NewEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                    return $content;
                }
                
                // Texto a generar no debe contener saltos de línea
                if (count(preg_split("/[\r\n]+/", $recording)) > 1) {
                    $smarty->assign("mb_message", _tr('Reminder text may not have newlines'));
                    $content = viewForm_NewEvent($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                    return $content;
                }                
            }else{
                $call_to = "";
                $asterisk_calls = "off";
                $recording = "";
				$remainerTime = "";
            }

            if($notification == "on"){ // si ingresa emails o contactos
                $list = htmlspecialchars_decode($list); // codifica los caracteres especiales
                $notification_email = $list;
            }else{
                $notification_email = "";
                $notification = "off";
            }

            $start = date('Y-m-d',$start_event);
            $end   = date('Y-m-d',$end_event);

            $checkbox_days = getConvertDay($start_event);

            $starttime = date('Y-m-d',$start_event)." ".$hora.":".$minuto;
            $endtime = date('Y-m-d',$end_event2)." ".$hora2.":".$minuto2;
            $day_repeat  = explode(',',$checkbox_days);

            $event_type = 1;
            $num_frec   = 0;

            if($repeat == "none"){ //solo un dia
                $event_type = 1;
                $num_frec   = 0;
            }
            if($repeat == "each_day"){ //dias que se repiten durante un numero de semanas
                $event_type = 5;
                $num_frec   = 7;
            }
            if($repeat == "each_month"){ //dias que se repiten durante un numero de meses
                $event_type = 6;
                $num_frec   = 30;
            }
            // dataToSendEmail
            $data_Send['emails_notification'] = $notification_email;
            $data_Send['startdate']           = $start;
            $data_Send['enddate']             = $end;
            $data_Send['starttime']           = $starttime;
            $data_Send['eventtype']           = $event_type;
            $data_Send['subject']             = $event;
            $data_Send['description']         = $description;
            $data_Send['endtime']             = $endtime;
            if(getParameter("save_edit")){ // si se va modificar un evento existente
                $dataUp = $pCalendar->getEventById($id, $uid);
                if($dataUp!="" && isset($dataUp))
                    $val = $pCalendar->updateEvent($id,$start,$end,$starttime,$event_type,$event,$description,$asterisk_calls,$recording,$call_to,$notification,$notification_email,$endtime,$each_repeat,$checkbox_days, $remainerTime, $color);
                else    $val = false;
                if($val == true){
                    if($notification_email != "")
                        sendMails($data_Send, $arrLang, "UPDATE",$arrConf,$pDB,$module_name, $id);
                    if($reminder == "on")
                        createRepeatAudioFile($each_repeat,$day_repeat,$starttime,$endtime,$num_frec,$asterisk_calls,$ext,$call_to,$pDB,$id,$arrLang,$arrConf,$recording,$remainerTime);
                    else{ //borra los .call que existan asociados a este evento
                        $dir_outgoing = $arrConf['dir_outgoing'];
                        system("rm -f $dir_outgoing/event_{$id}_*.call"); // si existen lo archivos los elimina
                    }
                    $smarty->assign("mb_message", $arrLang['update_successful']);
                    $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                    return $content;
                }
                else{
                    $smarty->assign("mb_message", $arrLang['error_update']);
                    $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                    return $content;
                }
            }
            else{
                if(getParameter("save_new")){ // si se va a ingresar un nuevo evento
                    $val = $pCalendar->insertEvent($uid,$start,$end,$starttime,$event_type,$event,$description,$asterisk_calls,$recording,$call_to,$notification,$notification_email,$endtime,$each_repeat,$checkbox_days, $remainerTime, $color);
                    $id = $pDB->getLastInsertId();
                    if($val == true){
                        if($notification_email != "")
                            sendMails($data_Send, $arrLang, "NEW", $arrConf,$pDB,$module_name, $id);
                        if($reminder == "on")
                            createRepeatAudioFile($each_repeat,$day_repeat,$starttime,$endtime,$num_frec,$asterisk_calls,$ext,$call_to,$pDB,$id,$arrLang,$arrConf,$recording,$remainerTime);
                        $smarty->assign("mb_message", $arrLang['insert_successful']);
                        $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                        return $content;
                    }
                    else{
                        $smarty->assign("mb_message", $arrLang['error_insert']);
                        $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                        return $content;
                    }
                }else{
                    $smarty->assign("mb_message", $arrLang['error_insert']);
                    $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
                    return $content;
                }
            }
        }else{
            $smarty->assign("mb_message", $arrLang['error_date']);
            $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
            return $content;
        }
    }else{
        $smarty->assign("mb_message", $arrLang['error_eventName']);
        $content = viewCalendar($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLang);
        return $content;
    }
}

function deleteEvent($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLang){
    $pCalendar = new paloSantoCalendar($pDB);
    $pDBACL    = new paloDB($arrConf['dsn_conn_database1']);
    $pACL      = new paloACL($pDBACL);
    $id_user   = $pACL->getIdUser($_SESSION["elastix_user"]);
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id_event");
    $data = $pCalendar->getEventById($id, $id_user);
    $val = false;
    if($data!="" && isset($data)){
        $val = $pCalendar->deleteEvent($id, $id_user);
        if($data['emails_notification'] != "" && $val)
            sendMails($data, $arrLang,"DELETE",$arrConf,$pDB,$module_name, $id);
        return $val;
    }
    return $val;
}

function sendMails($data, $arrLang, $type, $arrConf,$pDB, $module_name, $idEvent){

    $emails      = $data['emails_notification'];
    $start       = $data['startdate'];
    $end         = $data['enddate'];
    $starttime   = $data['starttime'];
    $event_type  = $data['eventtype'];
    $event       = $data['subject'];
    $description = $data['description'];
    $endtime     = $data['endtime'];
    $IcalTmp     = "";

    $pCalendar = new paloSantoCalendar($pDB);
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid  = Obtain_UID_From_User($user,$arrConf);
    $pDB3 = new paloDB($arrConf['dsn_conn_database1']);
    $user_name = $pCalendar->getNameUsers($uid,$pDB3);
    $user_name1 = $pCalendar->getDescUsers($uid,$pDB3);

    if(!isset($user_name) || $user_name=="")
        $user_name = $user;

    if($uid == "" || !isset($uid)) return; // validar el error de envio de email
    //obtain email FROM....
    $From = 'events@elastixserver.com';
    $subject = $arrLang['New_Event'].": ".$event;
    if($type == "NEW")
        $subject = $arrLang['New_Event'].": ".$event;
    if($type == "UPDATE")
        $subject = $arrLang['Change_Event'].": ".$event;
    if($type == "DELETE")
        $subject = $arrLang['Delete_Event'].": ".$event;

    $event_type = returnTypeEvent($event_type, $arrLang);
    $val = false;
    if($type != "DELETE"){
        //$dirIcalTmp = "/tmp/icalout.ics";
        $IcalTmp = createTmpIcal($arrLang,$pDB,$module_name, $arrConf, $idEvent); // write the file /tmp/icalout.ics
        /*$file = fopen($dirIcalTmp, "r");
        $encoded_attach = "";
        if($file){
            $contenido = fread($file, filesize($dirIcalTmp));
            $encoded_attach = chunk_split(base64_encode($contenido));
            $val = true;
        }*/
    }

    $startarray = explode(" ",$starttime);
    $endarray   = explode(" ",$endtime);
    $emails     = str_replace('"',"",$emails);
    $arrEmails  = explode(",",$emails);

$msg = "
<html>
     <head>
       <title>$subject</title>
     </head>
     <body>
         <h1 style='background-color:#A9A9A9; border-bottom:solid 1px #3b6d92; padding:10px 40px; font-size:28px; color:#fcfdff;'> {$arrLang['notification_event']}</h1>
         <div style='margin:0px 40px;'>
             <div style='color:#000; font-size:26px; padding:15px 0px; margin-bottom:20px;'>
                 $subject
             </div>
             <div style='margin-top:20px;'>
                 <span style='font-style:italic; font-weight:bolder; font-size: 16px;'>{$arrLang['Dear User']}: </span>
             </div>
             <div style='margin-top:20px; margin-bottom:30px;'>
                 {$arrLang['invitation_event']}:
             </div>
             <div style='margin-top:10px; margin-left:40px;'>
                 <div style='margin-top:10px; font-style:italic; font-weight:bolder;'>{$arrLang['Event']}: </div>
                 <div style='margin:0px 0px 0px 60px'>$event.</div>
             </div>
             <div style='margin-top:10px; margin-left:40px;'>
                 <div style='margin-top:10px; font-style:italic; font-weight:bolder;'>{$arrLang['Date']}: </div>
                 <div style='margin:0px 0px 0px 60px'>".date("d M Y",strtotime($start))." - ".date("d M Y",strtotime($end)).".</div>
             </div>
             <div style='margin-top:10px; margin-left:40px;'>
                 <div style='margin-top:10px; font-style:italic; font-weight:bolder;'>{$arrLang['time']}: </div>
                 <div style='margin:0px 0px 0px 60px'>".$startarray[1]." - ".$endarray[1].".</div>
             </div>";

    if($description != "")
             $msg .= "<div style='margin-top:10px; margin-left:40px;'>
                 <div style='margin-top:10px; font-style:italic; font-weight:bolder;'>{$arrLang['Description']}: </div>
                 <div style='margin:0px 0px 0px 60px'>$description.</div>
             </div>";

             $msg .= "<div style='margin-top:10px; margin-left:40px;'>
                 <div style='margin-top:10px; font-style:italic; font-weight:bolder;'>{$arrLang['Organizer']}: </div>
                 <div style='margin:0px 0px 0px 60px'>".$user_name1." - ".$user_name.".</div>
             </div>
             <div style='margin-top:20px; text-align: center; color: #BEBEBE; font-size: 12px;'>
                 <b>{$arrLang['noResponseNotification']}.</b><br />
                 <b>{$arrLang['copyrightNotification']}. 2006 - ".date("Y")."</b><br />
             </div>
         </div>
     </body>
 </html>";




//Agregamos al cuerpo del mensaje la iCal
// $msg .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST\n';
// $msg .= "Content-Transfer-Encoding: 8bit\n\n";
// $msg .= file_get_contents($dirIcalTmp,true);


    $msg = utf8_decode($msg);
    $mail = new PHPMailer();
    $mail->Host = "localhost";
    $mail->Body = $msg;
    $mail->IsHTML(true); // El correo se envía como HTML
    $mail->WordWrap = 50;
    $mail->From = $From;
    $mail->FromName = $user_name1." (".$user_name.")";
    /*if($val){
        $mail->AddAttachment($dirIcalTmp, "icalout.ics");
    }*/
    if($IcalTmp != "")
        $mail->AddStringAttachment($IcalTmp, "icalout.ics", "7bit", "text/calendar; charset=utf-8; method=REQUEST");
    for($i=0; $i<count($arrEmails)-1; $i++){
        $To = $arrEmails[$i];
        $cabecerasIcal = "";
        $cuerpo = "";
        $posini = strpos($To,"<");
        $posend = strpos($To,">");

        if($posini || $posend)
            $ToSend = substr($To,$posini+1,-1);
        else
            $ToSend = $To;

        $mail->ClearAddresses();
        $mail->Subject = utf8_decode($subject);
        $mail->AddAddress($ToSend, $To);

        $mail->Send();
    }
    if($val){
        fclose($file);
        unlink($dirIcalTmp);
    }
}

function getEmails($emails){
    //"eduardo cueva" <ecueva@palosanto.com>, <edu19432@hotmail.com>,
    $emails = htmlspecialchars_decode($emails);
    $emails = str_replace('"',"",$emails);
    $arrEmails = explode(",",$emails);

    $cad_emails = "";
    for($i=0; $i<count($arrEmails)-1; $i++)
        $cad_emails .= "<option value='registed' class='selected'>".htmlspecialchars($arrEmails[$i])."</option>";
    return $cad_emails;
}

function getEmailToTables($emails){
    //"eduardo cueva" <ecueva@palosanto.com>, <edu19432@hotmail.com>,
    $emails = htmlspecialchars_decode($emails);
    //$emails = str_replace('"',"",$emails);
    $arrEmails = explode(",",$emails);
    $i = 0;
    $cad_emails = array();
    for($i=0; $i<count($arrEmails)-1; $i++){
        //"eduardo cueva" <ecueva@palosanto.com>
        $arr_tmp = explode("\"",$arrEmails[$i]);
        $num_email  = "num_email".$i;
        $cont_email = "cont_email".$i;
        $name_email = "name_email".$i;

        $cad_emails[$num_email]  = $i+1;

        if(count($arr_tmp) > 1)
            $cad_emails[$cont_email] = $arr_tmp[1];
        else
            $cad_emails[$cont_email] = "-";

        $pos1 = stripos($arrEmails[$i],"<");
        $pos2 = stripos($arrEmails[$i],">");
        if($pos1 || $pos2)
            $cad_emails[$name_email] = substr($arrEmails[$i],($pos1)+1,($pos2-strlen($arrEmails[$i])));
        else
            $cad_emails[$name_email] = "-";
    }
    $cad_emails['size_emails'] = $i;
    return $cad_emails;
}

function returnTypeEvent($dig, $arrLang){
    $type = "";
    switch($dig){
        case "1":
            $type = $arrLang["No_Repeat"];
            break;
        case "5":
            $type = $arrLang["Each_Week"];
            break;
        case "6":
            $type = $arrLang["Each_Month"];
            break;
        default:
            $type = $arrLang["No_Repeat"];
            break;
    }
    return $type;
}

function returnEventToType($dig, $arrLang){
    $type = "";
    switch($dig){
        case "1":
            $type = "none";
            break;
        case "5":
            $type = "each_day";
            break;
        case "6":
            $type = "each_month";
            break;
        default:
            $type = "none";
            break;
    }
    return $type;
}

function Obtain_UID_From_User($user,$arrConf)
{
    global $arrConf;
    $pdbACL = new paloDB($arrConf['dsn_conn_database1']);
    $pACL = new paloACL($pdbACL);
    $uid = $pACL->getIdUser($user);
    if($uid!=FALSE)
        return $uid;
    else return -1;
}

function getCheckDays($sunday,$monday,$tuesday,$wednesday,$thursday,$friday,$saturday)
{
    $out = "";
    if($sunday    == "on")   $out .= "Su,";
    if($monday    == "on")   $out .= "Mo,";
    if($tuesday   == "on")   $out .= "Tu,";
    if($wednesday == "on")   $out .= "We,";
    if($thursday  == "on")   $out .= "Th,";
    if($friday    == "on")   $out .= "Fr,";
    if($saturday  == "on")   $out .= "Sa,";
    return $out;
}

function getConvertDay($start){
    $ini = date('D', $start);
    $out = "";
    switch($ini){
        case "Sun":
            $out .= "Su,";
            break;
        case "Mon":
            $out .= "Mo,";
            break;
        case "Tue":
            $out .= "Tu,";
            break;
        case "Wed":
            $out .= "We,";
            break;
        case "Thu":
            $out .= "Th,";
            break;
        case "Fri":
            $out .= "Fr,";
            break;
        case "Sat":
            $out .= "Sa,";
            break;
    }
    return $out;
}

function getDaysByCheck($days,$type=0){
    $arrDays = explode(',',$days);
    $arrOut  = "";
    for($i=0; $i<(count($arrDays)-1); $i++){
        if($type==0){
            switch($arrDays[$i]){
                case "Su":
                    $arrOut['Sunday']    = "on";
                    break;
                case "Mo":
                    $arrOut['Monday']    = "on";
                    break;
                case "Tu":
                    $arrOut['Tuesday']   = "on";
                    break;
                case "We":
                    $arrOut['Wednesday'] = "on";
                    break;
                case "Th":
                    $arrOut['Thursday']  = "on";
                    break;
                case "Fr":
                    $arrOut['Friday']    = "on";
                    break;
                case "Sa":
                    $arrOut['Saturday']  = "on";
                    break;
            }
        }else{
            switch($arrDays[$i]){
                case "Su":
                    $arrOut['Sunday_check']    = "on";
                    break;
                case "Mo":
                    $arrOut['Monday_check']    = "on";
                    break;
                case "Tu":
                    $arrOut['Tuesday_check']   = "on";
                    break;
                case "We":
                    $arrOut['Wednesday_check'] = "on";
                    break;
                case "Th":
                    $arrOut['Thursday_check']  = "on";
                    break;
                case "Fr":
                    $arrOut['Friday_check']    = "on";
                    break;
                case "Sa":
                    $arrOut['Saturday_check']  = "on";
                    break;
            }
        }
    }
    return $arrOut;
}

function getLanguages($arrLang, $arrConf)
{
    $pDBACL    = new paloDB($arrConf['dsn_conn_database1']);
    $pACL      = new paloACL($pDBACL);
	$mensajeError = getParameter("mensajeError");
    $id_user   = $pACL->getIdUser($_SESSION["elastix_user"]);
    $jsonObject = new PaloSantoJSON();
    if($id_user)
        $jsonObject->set_message(_tr($mensajeError));
    else
		$jsonObject->set_message("");
    return $jsonObject->createJSON();
}

function viewBoxCalendar($arrConf,$arrLang,$pDB,$local_templates_dir,$smarty,$module_name){
    $pCalendar = new paloSantoCalendar($pDB);
    $id        = getParameter('id_event');
    $action    = getParameter('action');
    $pDBACL    = new paloDB($arrConf['dsn_conn_database1']);
    $pACL      = new paloACL($pDBACL);
    $id_user   = $pACL->getIdUser($_SESSION["elastix_user"]);
    $jsonObject = new PaloSantoJSON();

	$data = $pCalendar->getEventById($id, $id_user);
    $val  = false;
    if($data=="" && !isset($data)){
		$jsonObject->set_error(_tr("Don't exist the event"));
		return $jsonObject->createJSON();
	}

    $data = $pCalendar->get_event_by_id($id);
    $type_event = $data['it_repeat'];
    $days_repeat = $data['days_repeat'];
    $data['it_repeat'] = returnEventToType($type_event, $arrLang);
    $data['visibility'] = "visibility: hidden;";
    $data['visibility_repeat'] = "visibility: hidden;";
    $data['notification_status'] = $data['notification'];
    $data['title'] = _tr("View Event");
    $new_date_ini = $data['starttime'];
    $new_date_end = $data['endtime'];
    $data['date'] = date("d M Y H:i",strtotime($new_date_ini));
    $data['to'] = date("d M Y H:i",strtotime($new_date_end));
	$data['Contact'] = $arrLang['Contact'];
	$data['Email'] = $arrLang['Email'];

    if($data['notification']=="on"){
        $arrContacts = getEmailToTables($data['emails_notification']);
        $data['emails_notification'] = getEmails($data['emails_notification']);
        $data['visibility'] = "visibility: visible;";
        $data = array_merge($data,$arrContacts);
    }else
        $data['size_emails'] = 0;

    if($type_event==5){
        $data['visibility_repeat'] = "visibility: visible;";
    }

    if($type_event==6){
        $visibility_repeat = "visibility: visible;";
    }

    if($days_repeat != ""){
        $arr = getDaysByCheck($days_repeat,2);
        $data = array_merge($data,$arr);
    }

	if($data['call_to']!=""){
		$visibility_alert = "";
	}else
		$visibility_alert = "display:none";

	$date_ini = getParameter("event_date");
	if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$date_ini))
		$date_ini = date("M d Y");

	$dateServer = gmdate("D M d Y H:i:s TO (e)", strtotime($date_ini));//Fri Nov 12 2010 00:00:00 GMT-0500 (ECT)

	$arrForm = createFieldForm($arrLang);
	$oForm = new paloForm($smarty,$arrForm);

	$smarty->assign("add_phone",$arrLang["Search in Address Book"]);
	$smarty->assign("SAVE", $arrLang["Save"]);
	$smarty->assign("EDIT", $arrLang["Edit"]);
	$smarty->assign("DELETE", $arrLang["Delete"]);
	$smarty->assign("CANCEL", $arrLang["Cancel"]);
	$smarty->assign("Start_date", $arrLang["Start_date"]);
	$smarty->assign("Notification_Alert", $arrLang["Notification_Alert"]);
	$smarty->assign("End_date", $arrLang["End_date"]);
	$smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
	$smarty->assign("module_name", $module_name);
	$smarty->assign("notification_email", $arrLang["notification_email"]);
	$smarty->assign("id_event",$id);
	$smarty->assign("Call_alert",$arrLang["Call_alert"]);
//  $smarty->assign("visibility_emails",$visibility_emails);
	$smarty->assign("icon", "modules/$module_name/images/agenda_calendar.png");
	$smarty->assign("visibility_alert", $visibility_alert);
	$smarty->assign("LBL_EDIT", $arrLang["Edit Event"]);
	$smarty->assign("LBL_LOADING", $arrLang["Loading"]);
	$smarty->assign("LBL_DELETING", $arrLang["Deleting"]);
	$smarty->assign("LBL_SENDING", $arrLang["Sending Request"]);
	$smarty->assign("START_TYPE", $arrLang["START_TYPE"]);
	$smarty->assign("DATE_SERVER", $dateServer);
	$smarty->assign("Color", $arrLang["Color"]);
	$smarty->assign("Listen", $arrLang["Listen"]);
	$smarty->assign("Listen_here", _tr("Click here to listen"));

	$htmlForm = $oForm->fetchForm("$local_templates_dir/evento.tpl",$arrLang["Calendar"], array());
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name' name='formNewEvent' id='formNewEvent' onsubmit='return sendNewEvent();'>".$htmlForm."</form>";
	$data["html"]=$content;

    $jsonObject->set_message($data);
	return $jsonObject->createJSON();
}

function download_icals($arrLang,&$pDB,$module_name, $arrConf){

    $pDBACL    = new paloDB($arrConf['dsn_conn_database1']);
    $pACL      = new paloACL($pDBACL);
    $id_user   = $pACL->getIdUser($_SESSION["elastix_user"]);
    $arr_out = getAllDataCalendar($arrLang,$pDB,$module_name, $arrConf);

    header("Cache-Control: private");
    header("Pragma: cache");
    header('Content-Type: application/octec-stream');
    header('Content-disposition: inline; filename="icalout.ics"');
    header('Content-Type: application/force-download');

    $document_output = "BEGIN:VCALENDAR\nPRODID:-//Elastix Development Department// Elastix 2.0 //EN\nVERSION:2.0\n\n";
    //$document_output .= "BEGIN:VTIMEZONE\nTZID:America/Guayaquil\nX-LIC-LOCATION:America/Guayaquil\nBEGIN:STANDARD\nTZOFFSETFROM:-0500\nTZOFFSETTO:-0500\nTZNAME:ECT\nDTSTART:19700101T000000\nEND:STANDARD\nEND:VTIMEZONE\n\n";
    for($i=0; $i<count($arr_out); $i++){
        $start_time = gmdate("Ymd",strtotime($arr_out[$i]['start']))."T".gmdate("Hi",strtotime($arr_out[$i]['start']))."00Z";
        $end_time   = gmdate("Ymd",strtotime($arr_out[$i]['end']))."T".gmdate("Hi",strtotime($arr_out[$i]['end']))."00Z";
        $tmStamp    = gmdate("Ymd",strtotime($arr_out[$i]['start']))."T".gmdate("His",strtotime($arr_out[$i]['start']))."Z";

        $document_output.= "BEGIN:VEVENT\n";
        $document_output.= "DTSTAMP:$tmStamp\n";
        $document_output.= "CREATED:$start_time\n";
        $document_output.= "UID:$i-".$arr_out[$i]['id']."\n";
        $document_output.= "SUMMARY:".$arr_out[$i]['title']."\n";
        $document_output.= "CLASS:PUBLIC\n";
        $document_output.= "PRIORITY:5\n";
        $document_output.= "DTSTART:$start_time\n";
        $document_output.= "DTEND:$end_time\n";
        $document_output.= "TRANSP:OPAQUE\n";
        $document_output.= "SEQUENCE=0\n";
        $document_output.= "END:VEVENT\n\n";
    }
    $document_output .= "END:VCALENDAR";
    return $document_output;
}

function createTmpIcal($arrLang,&$pDB,$module_name, $arrConf, $idEvent){
    $pDBACL    = new paloDB($arrConf['dsn_conn_database1']);
    $pACL      = new paloACL($pDBACL);
    $id_user   = $pACL->getIdUser($_SESSION["elastix_user"]);
    $arr_out   = getDataCalendarByEventId($arrLang,$pDB,$module_name, $arrConf, $idEvent);
    $document_output = "BEGIN:VCALENDAR\nPRODID:-//Elastix Development Department// Elastix 2.0 //EN\nVERSION:2.0\n\n";
    for($i=0; $i<count($arr_out); $i++){
        $start_time = gmdate("Ymd",strtotime($arr_out[$i]['start']))."T".gmdate("Hi",strtotime($arr_out[$i]['start']))."00Z";
        $end_time   = gmdate("Ymd",strtotime($arr_out[$i]['end']))."T".gmdate("Hi",strtotime($arr_out[$i]['end']))."00Z";
        $tmStamp    = gmdate("Ymd",strtotime($arr_out[$i]['start']))."T".gmdate("His",strtotime($arr_out[$i]['start']))."Z";

        $document_output.= "BEGIN:VEVENT\n";
        $document_output.= "DTSTAMP:$tmStamp\n";//"DTSTAMP:$start_time\n";
        $document_output.= "CREATED:$start_time\n";
        $document_output.= "UID:$i-".$arr_out[$i]['id']."\n";
        $document_output.= "SUMMARY:".$arr_out[$i]['title']."\n";
        $document_output.= "CLASS:PUBLIC\n";
        $document_output.= "PRIORITY:5\n";
        $document_output.= "DTSTART:$start_time\n";
        $document_output.= "DTEND:$end_time\n";
        $document_output.= "TRANSP:OPAQUE\n";
        $document_output.= "SEQUENCE=0\n";
        $document_output.= "END:VEVENT\n\n";
    }
    $document_output .= "END:VCALENDAR";
    return $document_output;
}


function newBoxCalendar($arrConf,$arrLang,$pDB,$local_templates_dir,$smarty,$module_name){
    $pCalendar = new paloSantoCalendar($pDB);
	$jsonObject = new PaloSantoJSON();
    $pDBACL  = new paloDB($arrConf['dsn_conn_database1']);
    $pACL    = new paloACL($pDBACL);
    $id_user = $pACL->getIdUser($_SESSION["elastix_user"]);
    $ext = "";
	$id_event = "";

	$_DATA['ReminderTime'] = isset($_DATA['ReminderTime'])?$_DATA['ReminderTime']:"10";

    if($id_user){
        $pDB3 = new paloDB($arrConf['dsn_conn_database1']);
        $ext = $pCalendar->obtainExtension($pDB3,$id_user);
        if(empty($ext)) $ext = "empty";
    }else{
        $ext = "empty";
    }
    $data['now']       = date("d M Y H:i");// convert times to (d M Y) like (02 Feb 2010)
    $data['after']     = date("d M Y H:i",strtotime($data['now']." + 5 minutes"));
    $data['New_Event'] = $arrLang["New_Event"];
    $data['ext']       = $ext;

	$arrForm = createFieldForm($arrLang);
    $oForm = new paloForm($smarty,$arrForm);

	$date_ini = getParameter("event_date");
	if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$date_ini))
        $date_ini = date("M d Y");

	$dateServer = gmdate("D M d Y H:i:s TO (e)", strtotime($date_ini));//Fri Nov 12 2010 00:00:00 GMT-0500 (ECT)
	$visibility_emails = "visibility: visible;";
    $visibility_alert  = "display: none;";

	$smarty->assign("add_phone",$arrLang["Search in Address Book"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("Start_date", $arrLang["Start_date"]);
    $smarty->assign("Notification_Alert", $arrLang["Notification_Alert"]);
    $smarty->assign("End_date", $arrLang["End_date"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("module_name", $module_name);
    $smarty->assign("notification_email", $arrLang["notification_email"]);
    $smarty->assign("id_event",$id_event);
    $smarty->assign("Call_alert",$arrLang["Call_alert"]);
    $smarty->assign("visibility_emails",$visibility_emails);
    $smarty->assign("icon", "modules/$module_name/images/agenda_calendar.png");
    $smarty->assign("visibility_alert", $visibility_alert);
    $smarty->assign("LBL_EDIT", $arrLang["Edit Event"]);
    $smarty->assign("LBL_LOADING", $arrLang["Loading"]);
    $smarty->assign("LBL_DELETING", $arrLang["Deleting"]);
    $smarty->assign("LBL_SENDING", $arrLang["Sending Request"]);
    $smarty->assign("START_TYPE", $arrLang["START_TYPE"]);
    $smarty->assign("DATE_SERVER", $dateServer);
    $smarty->assign("Color", $arrLang["Color"]);
    $smarty->assign("Listen", $arrLang["Listen"]);
    $smarty->assign("Listen_here", _tr("Click here to listen"));

	$htmlForm = $oForm->fetchForm("$local_templates_dir/evento.tpl",$arrLang["Calendar"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name' name='formNewEvent' id='formNewEvent' onsubmit='return sendNewEvent();'>".$htmlForm."</form>";
	$data["html"]=$content;
	$jsonObject->set_message($data);
    return $jsonObject->createJSON(); 
}

function deleteBoxCalendar($arrConf,$arrLang,$pDB,$module_name){
    $pCalendar = new paloSantoCalendar($pDB);
    $pDBACL    = new paloDB($arrConf['dsn_conn_database1']);
    $pACL      = new paloACL($pDBACL);
	$jsonObject = new PaloSantoJSON();
    $id_user   = $pACL->getIdUser($_SESSION["elastix_user"]);
    $id   = getParameter('id_event');
    $data = $pCalendar->getEventById($id, $id_user);
    $dir_outgoing = $arrConf['dir_outgoing'];
    $val = false;
    if($data !="" && isset($data)){ // si el evento le pertenece al usuario
        // Enviar el correo de notificación ANTES de borrar el evento, porque
        // la generación del iCal requiere que el evento todavía exista.
        if($data['emails_notification'] != "")
            sendMails($data, $arrLang,"DELETE",$arrConf,$pDB,$module_name, $id);
        $val = $pCalendar->deleteEvent($id, $id_user);
    }
    if($val == true){
        $data["error_delete_JSON"] = $arrLang['delete_successful'];
        $data["error_delete_status"] = "on";
        // eliminacion de archivos .call
        // para este caso el nombre del archivo a eliminar tendra un formato:
        // event_id_*.call dado que solo se estan registrando eventos diarios.
        array_map('unlink', glob("$dir_outgoing/event_{$id}_*.call"));
    }
    else{
        $data["error_delete_JSON"] = $arrLang['error_delete'];
        $data["error_delete_status"] = "off";
    }

	$jsonObject->set_message($data);
    return $jsonObject->createJSON();
}

function getNumExtesion($arrConf,&$pDB,$arrLang){
    $pDBACL  = new paloDB($arrConf['dsn_conn_database1']);
    $pACL    = new paloACL($pDBACL);
    $id_user = $pACL->getIdUser($_SESSION["elastix_user"]);
    $pCalendar = new paloSantoCalendar($pDB);
	$jsonObject = new PaloSantoJSON();
    if($id_user){
        $pDB3 = new paloDB($arrConf['dsn_conn_database1']);
        $ext = $pCalendar->obtainExtension($pDB3,$id_user);
        if(empty($ext)) $ext = "empty";
        $arr = array("ext" => $ext);
    }else{
        $arr = array();
    }
	$jsonObject->set_message($arr);
    return $jsonObject->createJSON();
}

function getTextToSpeach($arrLang,&$pDB)
{
    $data = array();
    $jsonObject = new PaloSantoJSON();
    $pCalendar = new paloSantoCalendar($pDB);
    $number    = getParameter('call_to');
    $text      = getParameter('tts');
    
    // Número a llamar sólo puede ser numérico
    if (!preg_match('/^\d+$/', $number)) return $jsonObject->createJSON();
    
    // Texto a generar no debe contener saltos de línea
    if (count(preg_split("/[\r\n]+/", $text)) > 1) return $jsonObject->createJSON();

    $pCalendar->makeCalled($number, $number, $text);
	$jsonObject->set_message($data);
    return $jsonObject->createJSON();
}

function getAllDataCalendar($arrLang,&$pDB,$module_name, $arrConf){
    $pCalendar = new paloSantoCalendar($pDB);
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user,$arrConf);
    $arrDates = $pCalendar->getAllEventsByUid($uid);
    $j=0;
    $k=0;
    $arr = "";
    while($j < count($arrDates)){
        $event_type = $arrDates[$j]['eventtype'];
        $arr1 = "";
        // evento diario
        if($event_type == 1){
            $arr1 = array(
                        'id'    => $arrDates[$j]['id'],
                        'title' => $arrDates[$j]['subject'],
                        'start' => $arrDates[$j]['starttime'],
                        'end'   => $arrDates[$j]['endtime'],
                        'allDay'=> false,
                        'color' => $arrDates[$j]['color'],
                        'url'   => "getDataAjaxForm('menu=".$module_name."&action=view_box&rawmode=yes&id_event=".$arrDates[$j]['id']."', event);"
                        );
            $arr[$k] = $arr1;
            $k += 1;
        }
        // evento semanal
        if($event_type == 5){
            $each_repeat = $arrDates[$j]['each_repeat'];
            $day_repeat  = explode(',',$arrDates[$j]['days_repeat']);
            $starttime   = $arrDates[$j]['starttime'];
            $endtime     = $arrDates[$j]['endtime'];
            $type = 7;
            getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,$k,$arr,$arrDates,$type,$module_name);
        }
        // evento mensual
        if($event_type == 6){
            $each_repeat = $arrDates[$j]['each_repeat'];
            $day_repeat  = explode(',',$arrDates[$j]['days_repeat']);
            $starttime   = $arrDates[$j]['starttime'];
            $endtime     = $arrDates[$j]['endtime'];
            $type = 30;
            getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,$k,$arr,$arrDates,$type,$module_name);
        }
        $j++;
    }
    return $arr;
}

function getDataCalendarByEventId($arrLang,&$pDB,$module_name, $arrConf, $idEvent){
    $pCalendar = new paloSantoCalendar($pDB);
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = Obtain_UID_From_User($user,$arrConf);
    $arrDates = $pCalendar->getEventIdByUid($uid, $idEvent);
    $j=0;
    $k=0;
    $arr = "";
    while($j < count($arrDates)){
        $event_type = $arrDates[$j]['eventtype'];
        $arr1 = "";
        // evento diario
        if($event_type == 1){
            $arr1 = array(
                        'id'    => $arrDates[$j]['id'],
                        'title' => $arrDates[$j]['subject'],
                        'start' => $arrDates[$j]['starttime'],
                        'end'   => $arrDates[$j]['endtime'],
                        'allDay'=> false,
                        'color' => $arrDates[$j]['color'],
                        'url'   => "getDataAjaxForm('menu=".$module_name."&action=view_box&rawmode=yes&id_event=".$arrDates[$j]['id']."', event);"
                        );
            $arr[$k] = $arr1;
            $k += 1;
        }
        // evento semanal
        if($event_type == 5){
            $each_repeat = $arrDates[$j]['each_repeat'];
            $day_repeat  = explode(',',$arrDates[$j]['days_repeat']);
            $starttime   = $arrDates[$j]['starttime'];
            $endtime     = $arrDates[$j]['endtime'];
            $type = 7;
            getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,$k,$arr,$arrDates,$type,$module_name);
        }
        // evento mensual
        if($event_type == 6){
            $each_repeat = $arrDates[$j]['each_repeat'];
            $day_repeat  = explode(',',$arrDates[$j]['days_repeat']);
            $starttime   = $arrDates[$j]['starttime'];
            $endtime     = $arrDates[$j]['endtime'];
            $type = 30;
            getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,$k,$arr,$arrDates,$type,$module_name);
        }
        $j++;
    }
    return $arr;
}

function createRepeatAudioFile($each_repeat,$day_repeat,$starttime,$endtime,$type,$asterisk_call_me,$ext,$call_to,$pDB,$id_event,$arrLang,$arrConf,$recording, $remainerTime){
    $day_start       = date("D",strtotime("$starttime"));
    $day_end         = date("D",strtotime("$endtime"));
    $hour_start      = date("H:i",strtotime("$starttime"));
    $hour_end        = date("H:i",strtotime("$endtime"));
    $day_start_dig   = convertDayToInt($day_start);
    $day_end_dig     = convertDayToInt($day_end);
    $FechaInicio     = "";
    $dir_outgoing    = $arrConf['dir_outgoing'];
    $sDirectorioBase = $arrConf['sDirectorioBase'];
    $last_day_tmp    = $starttime;
    $m = 0;
    $cont = 0;

    for($i=0; $i<$each_repeat; $i++){// vamos a escribir el numero de eventos que se repiten
        $l = 0;
        while($l < count($day_repeat)-1){// recorremos el arreglo de dias a repetir(Mo,Tu,Fr)
            $day_dig = convertDayToInt($day_repeat[$l]);
            if($i == 0){// si es la primera semana que se va a repetir debemos tomar en cuenta q dia se va a colocar primero deacuerdo a su prioridad

                if($day_start_dig <= $day_dig){// fecha inicial <= dia inicial (Su, Mo,..)
                    $rest = $day_dig - $day_start_dig;
                    $sum_days = $rest;
                    $start = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_start;
                    $end   = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_end;

                    $last_day_tmp = $start;
                    $FechaInicio = $start.":00";
                    // crea el archivo de audio
                    createAudioFiles($asterisk_call_me,$ext,$call_to,$pDB,$id_event,$arrLang,$dir_outgoing,$sDirectorioBase,$cont,$FechaInicio,$recording, $remainerTime);
                }
                else{// ESPECIFICAR SI SOLO HAY UN DIA
                    $m=1;
                }
            }else{
                $last_day = date("D",strtotime("$last_day_tmp"));
                $last_day = convertDayToInt($last_day);
                $sum = $day_dig - $last_day;
                if($i > 1 && $m == 1){
                    $m = 0;
                    $i--;
                }
                if((count($day_repeat)-1) == 1){
                     $start = date("Y-m-d",strtotime("$last_day_tmp + $type days"))." ".$hour_start;
                }
                else{
                    if($sum >= 0){
                        $start = date("Y-m-d",strtotime("$last_day_tmp + $sum days"))." ".$hour_start;
                    }else{
                        if($type == 30){
                            $sum += $type;
                            $start_tmp = date("D",strtotime("$last_day_tmp + $sum days"));
                            $new_day_tmp = convertDayToInt($start_tmp);// se vuelve a convertir en dias para verificar si el dia que cae en el mes es correcto ya que si no lo es entonces son meses con menos de 30 dias
                            $dayToSum = $new_day_tmp - $day_dig;
                            if($dayToSum >= 0){
                                $sum -= $dayToSum;
                            }else{
                                $sum = $dayToSum * (-1);
                            }
                        }
                        else{
                            $sum += $type;
                        }
                        $start = date("Y-m-d",strtotime("$last_day_tmp + $sum days"))." ".$hour_start;
                    }
                }
                $end = date("Y-m-d",strtotime("$start"))." ".$hour_end;
                if($end <= $endtime){
                    $FechaInicio = $start.":00";
                    // crea el archivo de audio
                    createAudioFiles($asterisk_call_me,$ext,$call_to,$pDB,$id_event,$arrLang,$dir_outgoing,$sDirectorioBase,$cont,$FechaInicio,$recording, $remainerTime);
                }
                $last_day_tmp = $start;
            }
            $l++;
            $cont++;
        }
    }
}

function createAudioFiles($asterisk_call,$ext,$call_to,$pDB,$id_event,$arrLang,$dir_outgoing,$sDirectorioBase,$i,$FechaInicio,$recording,$remainerTime){
    $pCalendar = new paloSantoCalendar($pDB);

    $result = "";
    $iRetries = 2;
    if($remainerTime=="10"){
        $FechaInicio = date("Y-m-d H:i",strtotime("$FechaInicio - 600 second"));
    }elseif($remainerTime=="30"){
        $FechaInicio = date("Y-m-d H:i",strtotime("$FechaInicio - 1800 second"));
    }else{
        $FechaInicio = date("Y-m-d H:i",strtotime("$FechaInicio - 3600 second"));
    }

    if($asterisk_call=="on"){
        //Obtener datos sobre quien esta usando el sistema
        //Channel, description, extension
        $result = $pCalendar->Obtain_Protocol($ext);
        if($call_to!="")
            $result['number'] = $call_to;
        else
            $result['number'] = $result['id'];
    }
    /*if($asterisk_call=="on"){
        //Obtener datos sobre quien esta usando el sistema
        //Channel, description, extension
        $result = $pCalendar->Obtain_Protocol($ext);
        $result['number'] = $result['id'];
    }
    else{
        if($call_to!=""){
            $result = $pCalendar->Obtain_Protocol($ext);
            $result['number'] = $call_to;
        }else
            return;
    }*/

    if($result!=FALSE){
       /*$sContenido =   //"Channel: $sTrunk/$tuplaTelf[phone]\n".
                        //"Channel: {$result['dial']}\n".
                        "Channel: Local/{$result['number']}@from-internal\n".
                        "CallerID: Calendar Event <{$result['number']}>\n".
                        "MaxRetries: $iRetries\n".
                        "RetryTime: 60\n".
                        "WaitTime: 30\n".
			//"Application: Festival\n".
                        "Context: calendar-event\n".
                        "Extension: {$result['number']}\n\n".
                        "Priority: 1\n".
                        "Set: FILE_CALL=$sDirectorioBase/test\n".
                        "Set: ID_EVENT_CALL=$id_event\n";*/


        
         $sContenido =   //"Channel: $sTrunk/$tuplaTelf[phone]\n".
                        //"Channel: {$result['dial']}\n".
                        "Channel: Local/{$result['number']}@from-internal\n".
                        "CallerID: Calendar Event <{$result['number']}>\n".
                        "MaxRetries: $iRetries\n".
                        "RetryTime: 60\n".
                        "WaitTime: 30\n".
                        //"Context: festival-event\n".
                        "Application: Festival\n".
                        "Extension: {$result['number']}\n".
                        "Priority: 1\n".
                        "Data: $recording\n".
                        "Set: TTS=$recording\n";
		
    }

    if($sContenido!=""){
        $filename = "event_{$id_event}_{$i}.call";
        $filename_create = $dir_outgoing."/event_{$id_event}_{$i}.call";

        if(file_exists($filename_create)) //si existe se elimina el archivo
            unlink($filename_create);

        $hArchivo = fopen("$sDirectorioBase/$filename", 'w');
        if (!$hArchivo) {
            $bExito = FALSE;
            //$pDB->errMsg = $arrLang["Can not create called file"]." $filename";
            break;
        }
        else {
            fwrite($hArchivo, $sContenido);
            fclose($hArchivo);
            system("touch -d '$FechaInicio' $sDirectorioBase/$filename");
            system("mv $sDirectorioBase/$filename $dir_outgoing/");
        }
    }

}

function getDataCalendar($arrLang,&$pDB,$module_name,$arrConf){
    $pDBACL  = new paloDB($arrConf['dsn_conn_database1']);
    $pACL    = new paloACL($pDBACL);
    $id_user = $pACL->getIdUser($_SESSION["elastix_user"]);
	$jsonObject = new PaloSantoJSON();

    $pCalendar = new paloSantoCalendar($pDB);
    $start = getParameter('start');
    $end = getParameter('end');

    $start_time = date('Y-m-d', $start);
    $end_time = date('Y-m-d', $end);
    if(!$id_user){
        $json = new Services_JSON();
		$arrLanJSON = $json->encode($arr);
    }

    $year  = date('Y');
    $month = date('m');
    $day   = date('d');

    $arrDates = $pCalendar->getEventByDate($start_time, $end_time, $id_user);

    $j=0;
    $k=0;
    $arr = "";
    while($j < count($arrDates)){
        $event_type = $arrDates[$j]['eventtype'];
        $arr1 = "";
        // evento diario
        if($event_type == 1){
            $arr1 = array(
                        'id'    => $arrDates[$j]['id'],
                        'title' => $arrDates[$j]['subject'],
                        'start' => $arrDates[$j]['starttime'],
                        'end'   => $arrDates[$j]['endtime'],
                        'allDay'=> false,
                        'color' => $arrDates[$j]['color'],
                        'url'   => "getDataAjaxForm('menu=".$module_name."&action=view_box&rawmode=yes&id_event=".$arrDates[$j]['id']."', event);"
                        );
            $arr[$k] = $arr1;
            $k += 1;
        }
        // evento semanal
        if($event_type == 5){
            $each_repeat    = $arrDates[$j]['each_repeat'];
            $day_repeat     = explode(',',$arrDates[$j]['days_repeat']);
            $starttime      = $arrDates[$j]['starttime'];
            $endtime        = $arrDates[$j]['endtime'];
            $type = 7;
            getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,$k,$arr,$arrDates,$type,$module_name);
        }
        // evento mensual
        if($event_type == 6){
            $each_repeat    = $arrDates[$j]['each_repeat'];
            $day_repeat     = explode(',',$arrDates[$j]['days_repeat']);
            $starttime      = $arrDates[$j]['starttime'];
            $endtime        = $arrDates[$j]['endtime'];
            $type = 30;
            getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,$k,$arr,$arrDates,$type,$module_name);
        }
        $j++;
    }
	/*$jsonObject->set_message($arr);
	return $jsonObject->createJSON();*/
	$json = new Services_JSON();
    $arrLanJSON = $json->encode($arr);
    return $arrLanJSON;
}

function setDataCalendar($arrLang,$pDB,$arrConf){
    $pDBACL        = new paloDB($arrConf['dsn_conn_database1']);
    $pACL          = new paloACL($pDBACL);
    $id_user       = $pACL->getIdUser($_SESSION["elastix_user"]);
    $action        = getParameter('action');
    $days          = getParameter('days');
    $minutes       = getParameter('minutes');
    $dateIni       = getParameter('dateIni');
    $dateEnd       = getParameter('dateEnd');
    $dateIni       = str_replace("|mas|","+",$dateIni);
    $dateIni       = str_replace("|menos|","-",$dateIni);
    $dateEnd       = str_replace("|mas|","+",$dateEnd);
    $dateEnd       = str_replace("|menos|","-",$dateEnd);
    $id            = getParameter('id');// id_event
    $pCalendar     = new paloSantoCalendar($pDB);
    $Initial       = explode(" ",$dateIni);
    $Finally       = explode(" ",$dateEnd);
    $hour_ini      = date("H:i",strtotime($Initial[4]));
    $hour_end      = date("H:i",strtotime($Finally[4]));
    $event         = $pCalendar->getEventById($id, $id_user);
    $start         = $event['startdate'];
    $end           = $event['enddate'];
    $checkbox_days = "";
    $startdate     = date("Y-m-d",strtotime("$dateIni"));
    $enddate       = date("Y-m-d",strtotime("$dateEnd"));
    $starttime     = $startdate." ".$hour_ini;
    $endtime       = $enddate." ".$hour_end;

    if (!ctype_digit($id)) $id = NULL;

    // obtain data to create audio files
    $arrResult = $pCalendar->getEventById($id, $id_user);

    if(!isset($arrResult) || $arrResult=="")
        return $arrLang['error_Noevent'];

    $uid = $arrResult['uid'];
    $pDB3 = new paloDB($arrConf['dsn_conn_database1']);
    $ext = $pCalendar->obtainExtension($pDB3,$uid);

    $each_repeat = $arrResult['each_repeat'];
    $day_repeat = explode(',',$arrResult['days_repeat']);

    if($arrResult['eventtype'] == 1)
        $num_frec = 0;
    else{ if($arrResult['eventtype'] == 5){
            $num_frec = 7;
          }else{
            $num_frec = 30;
          }
    }

    if($arrResult['eventtype'] == 1){
        $startdateTime = strtotime($startdate);
        $checkbox_days = getConvertDay($startdateTime);
        $day_repeat  = explode(',',$checkbox_days);
    }

    $asterisk_calls = $arrResult['asterisk_call'];
    $call_to        = $arrResult['call_to'];
    $recording      = $arrResult['recording'];
    $remainerTime   = $arrResult['reminderTimer'];

    $val = $pCalendar->updateDateEvent($id,$startdate,$enddate,$starttime,$endtime, $checkbox_days);

    if($val){
        if(isset($arrResult['call_to']) && $arrResult['call_to'] != "")
            createRepeatAudioFile($each_repeat,$day_repeat,$starttime,$endtime,$num_frec,$asterisk_calls,$ext,$call_to,$pDB,$id,$arrLang,$arrConf,$recording,$remainerTime);
        return $arrLang['update_successful'];
    }else
        return $arrLang['error_update'];
}

function getContactEmails($arrConf)
{
    $pDBACL  = new paloDB($arrConf['dsn_conn_database1']);
    $pACL    = new paloACL($pDBACL);
    $id_user = $pACL->getIdUser($_SESSION["elastix_user"]);
    $tag = getParameter('tag');
    if(isset($id_user) && $id_user!=""){
        $pDB  = new paloDB($arrConf['dsn_conn_database']);
        $pDBAddress = new paloDB($arrConf['dsn_conn_database3']);
        $pCalendar = new paloSantoCalendar($pDB);
        $salida = $pCalendar->getContactByTag($pDBAddress, $tag, $id_user);
    }else{
        $salida = array();
    }

    // se instancia a JSON
    $jsonObject = new PaloSantoJSON();
	$jsonObject->set_message($salida);
	return $jsonObject->createJSON();
}

function getRepeatDate($each_repeat,$day_repeat,$starttime,$endtime,$j,&$k,&$arr,$arrDates,$type,$module_name){
    $day_start      = date("D",strtotime("$starttime"));
    $day_end        = date("D",strtotime("$endtime"));
    $hour_start     = date("H:i",strtotime("$starttime"));
    $hour_end       = date("H:i",strtotime("$endtime"));
    $day_start_dig  = convertDayToInt($day_start);
    $day_end_dig    = convertDayToInt($day_end);
    $last_day_tmp   = $starttime;
    $m = 0;
    for($i=0; $i<$each_repeat; $i++){// vamos a escribir el numero de eventos que se repiten
        $l = 0;
        while($l < count($day_repeat)-1){// recorremos el arreglo de dias a repetir(Mo,Tu,Fr)
            $day_dig = convertDayToInt($day_repeat[$l]);
            if($i == 0){// si es la primera semana que se va a repetir debemos tomar en cuenta q dia se va a colocar primero deacuerdo a su prioridad
                if($day_start_dig <= $day_dig){// fecha inicial <= dia inicial (Su, Mo,..)
                    $rest         = $day_dig - $day_start_dig;
                    $sum_days     = $rest;
                    $start        = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_start;
                    $end          = date("Y-m-d",strtotime("$starttime + $sum_days days"))." ".$hour_end;
                    $last_day_tmp = $start;
                    $arr1 = array(
                        'id'    => $arrDates[$j]['id'],
                        'title' => $arrDates[$j]['subject'],
                        'start' => $start,
                        'end'   => $end,
                        'allDay'=> false,
                        'color' => $arrDates[$j]['color'],
                        'url'   => "getDataAjaxForm('menu=".$module_name."&action=view_box&rawmode=yes&id_event=".$arrDates[$j]['id']."', event);"
                        );
                    $last_day_tmp = $start;
                    $arr[$k] = $arr1;
                    $k += 1;
                }
                else{// ESPECIFICAR SI SOLO HAY UN DIA
                    $m=1;
                }
            }else{
                $last_day = date("D",strtotime("$last_day_tmp"));
                $last_day = convertDayToInt($last_day);
                $sum = $day_dig - $last_day;
                if($i > 1 && $m == 1){
                    $m = 0;
                    $i--;
                }
                if((count($day_repeat)-1) == 1){
                     $start = date("Y-m-d",strtotime("$last_day_tmp + $type days"))." ".$hour_start;
                }
                else{
                    if($sum >= 0){
                        $start = date("Y-m-d",strtotime("$last_day_tmp + $sum days"))." ".$hour_start;
                    }else{
                        if($type == 30){
                            $sum += $type;
                            $start_tmp = date("D",strtotime("$last_day_tmp + $sum days"));
                            $new_day_tmp = convertDayToInt($start_tmp);// se vuelve a convertir en dias para verificar si el dia que cae en el mes es correcto ya que si no lo es entonces son meses con menos de 30 dias
                            $dayToSum = $new_day_tmp - $day_dig;
                            if($dayToSum >= 0){
                                $sum -= $dayToSum;
                            }else{
                                $sum = $dayToSum * (-1);
                            }
                        }
                        else{
                            $sum += $type;
                        }
                        $start = date("Y-m-d",strtotime("$last_day_tmp + $sum days"))." ".$hour_start;
                    }
                }
                $end = date("Y-m-d",strtotime("$start"))." ".$hour_end;
                if($end <= $endtime){
                    $arr1 = array(
                        'id'    => $arrDates[$j]['id'],
                        'title' => $arrDates[$j]['subject'],
                        'start' => $start,
                        'end'   => $end,
                        'allDay'=> false,
                        'color' => $arrDates[$j]['color'],
                        'url'   => "getDataAjaxForm('menu=".$module_name."&action=view_box&rawmode=yes&id_event=".$arrDates[$j]['id']."', event);"
                        );
                    $arr[$k] = $arr1;
                    $k += 1;
                }
                $last_day_tmp = $start;
            }
            $l++;
        }
    }
}

function convertDayToInt($day)
{
    switch($day){
        case "Sun":
            return 0;
            break;
        case "Mon":
            return 1;
            break;
        case "Tue":
            return 2;
            break;
        case "Wed":
            return 3;
            break;
        case "Thu":
            return 4;
            break;
        case "Fri":
            return 5;
            break;
        case "Sat":
            return 6;
            break;
        case "Su":
            return 0;
            break;
        case "Mo":
            return 1;
            break;
        case "Tu":
            return 2;
            break;
        case "We":
            return 3;
            break;
        case "Th":
            return 4;
            break;
        case "Fr":
            return 5;
            break;
        case "Sa":
            return 6;
            break;
    }
}

function createFieldForm($arrLang)
{
    for($i=0; $i<60; $i++){
        if($i < 10) $arrMin["0$i"] = "0$i";
        else $arrMin[$i] = $i;
    }

    for($i=0; $i<24; $i++){
        if($i < 10) $arrHou["0$i"] = "0$i";
        else $arrHou[$i] = $i;
    }

    $arrRepeat= array(
        "none"      => $arrLang["No_Repeat"],
        "each_day"  => $arrLang["Each_Week"],
        "each_month"=> $arrLang["Each_Month"],
    );

    $repeat = "";
    for($i=1; $i<=30; $i++)
        $repeat[$i] = $i;

    $pCalendar = new paloSantoCalendar($pDB);
    //$arrRecording = $pCalendar->Obtain_Recordings_Current_User();
    $lblTime = $arrLang["lblbefore"];
    $arrRadio = array('10' => '10'.$lblTime, '30' => '30'.$lblTime, '60' => '60'.$lblTime);

    $arrFields = array(
            "event"   => array(      "LABEL"                  => $arrLang["Name"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:274px", "id" => "event"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "date"   => array(      "LABEL"                  => $arrLang["Date"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%d %b %Y %H:%M", "style" => "width:80px"),
                                            "VALIDATION_TYPE"        => "",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "to"   => array(      "LABEL"                  => $arrLang["To"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%d %b %Y %H:%M"),
                                            "VALIDATION_TYPE"        => "",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "description"   => array(      "LABEL"                  => $arrLang["Description"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"      => array("style"=>"width: 271px; height: 36px;"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "COLS"                   => "36px",
                                            "ROWS"                   => "2",
                                            "EDITABLE"               => "si",
                                            ),
            "call_to"   => array(      "LABEL"                  => $arrLang["Call to"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:70px","id"=>"call_to"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "tts"   => array(         "LABEL"                  => $arrLang["Text to Speech"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"      => array("style"=>"width: 365px; height: 36px;","maxlength"=>"140", "onchange" => "changeTextAreaTTs();", "onkeyup"=>"KeyUpTextAreaTTs();"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "COLS"                   => "48px",
                                            "ROWS"                   => "2",
                                            "EDITABLE"               => "si",
                                            ),
            "notification"   => array(      "LABEL"                  => $arrLang["notification"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "reminder"   => array(      "LABEL"                  => $arrLang["active_foneCall"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "ReminderTime" => array(    "LABEL"                  => $arrLang["ReminderTime"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrRadio,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),

            );
    return $arrFields;
}

function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
    else if(getParameter("action")=="save_edit")
        return "save_edit";
    else if(getParameter("action")=="set_data")
        return "setData";
    else if(getParameter("save_edit"))
        return "save_edit";
    else if(getParameter("delete"))
        return "delete";
    else if(getParameter("edit"))
        return "edit";
    else if(getParameter("action")=="edit")
        return "edit";
    else if(getParameter("action")=="get_lang")
        return "get_lang";
    else if(getParameter("action")=="get_data")
        return "get_data";
    else if(getParameter("action")=="get_contacts")
        return "get_contacts";
    else if(getParameter("action")=="get_num_ext")
        return "get_num_ext";
    else if(getParameter("action")=="view_box")
        return "view_box";
    else if(getParameter("action")=="new_box")
        return "new_box";
    else if(getParameter("action")=="delete_box")
        return "delete_box";
    else if(getParameter("action")=="phone_numbers")
        return "phone_numbers";
    else if(getParameter("action")=="download_icals")
        return "download_icals";
    else if(getParameter("action")=="get_contacts2")
        return "get_contacts2";
    else if(getParameter("action")=="getTextToSpeach")
        return "getTextToSpeach";
    else if(getParameter("action")=="display")
        return "display";
    else
        return "report"; //cancel
}

function report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $dsnAsterisk)
{
    include_once "modules/address_book/libs/paloSantoAdressBook.class.php";

    $padress_book = new paloAdressBook($pDB);
    $pACL    = new paloACL($pDB_2);
    $id_user = $pACL->getIdUser($_SESSION["elastix_user"]);
    if(isset($_POST['select_directory_type']) && $_POST['select_directory_type']=='External')
    {
        $smarty->assign("external_sel",'selected=selected');
        $directory_type = 'external';
    }
    else{
        $smarty->assign("internal_sel",'selected=selected');
        $directory_type = 'internal';
    }
    $_POST['select_directory_type'] = $directory_type;

    $arrComboElements = array(  "name"        =>$arrLang["Name"],
                                "telefono"    =>$arrLang["Phone Number"]);

    if($directory_type=='external')
        $arrComboElements["last_name"] = $arrLang["Last Name"];

    $arrFormElements = array(   "field" => array(   "LABEL"                  => $arrLang["Filter"],
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrComboElements,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),

                                "pattern" => array( "LABEL"          => "",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => "",
                                                    "INPUT_EXTRA_PARAM"      => ""),
                                );

    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $smarty->assign("SHOW", $arrLang["Show"]);
    $smarty->assign("CSV", $arrLang["CSV"]);
    $smarty->assign("module_name", $module_name);

    $smarty->assign("Phone_Directory",$arrLang["Phone Directory"]);
    $smarty->assign("Internal",$arrLang["Internal"]);
    $smarty->assign("External",$arrLang["External"]);

    $field   = NULL;
    $pattern = NULL;
    $namePattern = NULL;

    $allowSelection = array("name", "telefono", "last_name");
    if(isset($_POST['field']) and isset($_POST['pattern']) and ($_POST['pattern']!="")){
        $field      = $_POST['field'];
        if (!in_array($field, $allowSelection))
            $field = "name";
        $pattern = '%'.$_POST['pattern'].'%';
        $namePattern = $_POST['pattern'];
        $nameField=$arrComboElements[$field];
    }

    $startDate = $endDate = date("Y-m-d H:i:s");

    $arrFilter = array("select_directory_type"=>$directory_type,"field"=>$field,"pattern" =>$namePattern);

    $oGrid  = new paloSantoGrid($smarty);

    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Phone Directory")." =  $directory_type ", $arrFilter, array("select_directory_type" => "internal"),true);
    $oGrid->addFilterControl(_tr("Filter applied ").$field." = $namePattern", $arrFilter, array("field" => "name","pattern" => ""));
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter_adress_book.tpl", "", $arrFilter);

    if($directory_type=='external')
        $total = $padress_book->getAddressBook(NULL,NULL,$field,$pattern,TRUE,$id_user);
    else
        $total = $padress_book->getDeviceFreePBX($dsnAsterisk, NULL,NULL,$field,$pattern,TRUE);

    $total_datos = $total[0]["total"];
    //Paginacion
    $limit  = 20;
    $total  = $total_datos;

    $oGrid->setLimit($limit);
    $offset = $oGrid->getOffSet($limit,$total,(isset($_GET['nav']))?$_GET['nav']:NULL,(isset($_GET['start']))?$_GET['start']:NULL);

    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;

    //Fin Paginacion

    if($directory_type=='external')
        $arrResult =$padress_book->getAddressBook($limit, $offset, $field, $pattern, FALSE, $id_user);
    else
        $arrResult =$padress_book->getDeviceFreePBX($dsnAsterisk, $limit,$offset,$field,$pattern);

    $arrData = null;
    if(is_array($arrResult) && $total>0){
        $arrMails = array();

        if($directory_type=='internal')
            $arrMails = $padress_book->getMailsFromVoicemail();

        foreach($arrResult as $key => $adress_book){
            if($directory_type=='external')
                $email = $adress_book['email'];
            else if(isset($arrMails[$adress_book['id']]))
                $email = $arrMails[$adress_book['id']];
            else $email = '';

            $arrTmp[0]  = ($directory_type=='external')?htmlspecialchars($adress_book['last_name'], ENT_QUOTES, "UTF-8")." ".htmlspecialchars($adress_book['name'], ENT_QUOTES, "UTF-8"):$adress_book['description'];
            $number = ($directory_type=='external')?htmlspecialchars($adress_book['telefono'], ENT_QUOTES, "UTF-8"):$adress_book['id'];
            $arrTmp[1]  = "<a href='javascript:return_phone_number(\"$number\", \"$directory_type\", \"{$adress_book['id']}\")'>$number</a>";
            $arrTmp[2]  = htmlspecialchars($email, ENT_QUOTES, "UTF-8");
            $arrData[]  = $arrTmp;
        }
    }
    if($directory_type=='external')
        $name = "<input type='submit' name='delete' value='{$arrLang["Delete"]}' class='button' onclick=\" return confirmSubmit('{$arrLang["Are you sure you wish to delete the contact."]}');\" />";
    else $name = "";

    $arrGrid = array(   "title"    => $arrLang["Address Book"],
                        "url"      => array('menu' => $module_name, 'action' => 'phone_numbers', 'rawmode' => 'yes', 'filter' => $pattern),
                        "icon"     => "images/list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "columns"  => array(0 => array("name"      => $arrLang["Name"],
                                                    "property1" => ""),
                                            1 => array("name"      => $arrLang["Phone Number"],
                                                    "property1" => ""),
                                            2=> array("name"      => $arrLang["Email"],
                                                    "property1" => ""),
                                        )
                    );

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}

function getContactEmails2($arrConf)
{
    $pDBACL  = new paloDB($arrConf['dsn_conn_database1']);
    $pACL    = new paloACL($pDBACL);
    $id_user = $pACL->getIdUser($_SESSION["elastix_user"]);
    $tag = getParameter('name_startsWith');
    $salida = array();
    if(isset($id_user) && $id_user!=""){
        $pDB  = new paloDB($arrConf['dsn_conn_database']);
        $pDBAddress = new paloDB($arrConf['dsn_conn_database3']);
        $pCalendar = new paloSantoCalendar($pDB);
        $salida = $pCalendar->getContactByTag($pDBAddress, $tag, $id_user);
        if(!$salida)
            $salida = array();
    }

header('Content-Type: application/json');
    // se instancia a JSON
    $json = new Services_JSON();
    return $json->encode($salida);
}
?>
