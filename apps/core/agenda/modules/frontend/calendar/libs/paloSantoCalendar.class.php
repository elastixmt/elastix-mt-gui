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
  $Id: paloSantoCalendar.class.php,v 1.1 2010-01-05 11:01:26 Bruno Macias V. bmacias@elastix.org Exp $ */
if (file_exists("/var/lib/asterisk/agi-bin/phpagi-asmanager.php")) {
require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
}
class paloSantoCalendar {
    var $_DB;
    var $errMsg;

    function paloSantoCalendar(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    /*HERE YOUR FUNCTIONS*/

    function getNumCalendar($filter_field, $filter_value)
    {
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT COUNT(*) FROM table $where";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getCalendar($limit, $offset, $filter_field, $filter_value)
    {
        $where = "";
        if(isset($filter_field) & $filter_field !="")
            $where = "where $filter_field like '$filter_value%'";

        $query   = "SELECT * FROM table $where LIMIT $limit OFFSET $offset";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getEventById($id, $id_user)
    {
        $query = "SELECT * FROM events WHERE id=? and uid=?";

        $result=$this->_DB->getFirstRowQuery($query,true,array($id,$id_user));

        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        return $result;
    }

    function get_events_by_date($day, $month, $year)
    {
        /* event types:
        1 - Normal event
        2 - full day event
        3 - unknown time event
        4 - reserved
        5 - weekly event
        6 - monthly event
        */

        $startdate = "strftime('%Y-%m-%d', startdate)";
        $enddate = "strftime('%Y-%m-%d', enddate)";
        $date = "'" . date('Y-m-d', mktime(0, 0, 0, $month, $day, $year))
                . "'";

        // day of week
        $dow_startdate = "strftime('%w', startdate)";
        $dow_date = "strftime('%w', $date)";

        // day of month
        $dom_startdate = "strftime('%d', startdate)";
        $dom_date = "strftime('%d', $date)";

        $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
        $uid  = $this->Obtain_UID_From_User($user);

        $query = "SELECT * FROM events\n"
            ."WHERE $date >= $startdate AND $date <= $enddate\n"
                    // find normal events
                    ."AND (eventtype = 1 OR eventtype = 2 OR eventtype = 3 "
                    ."OR eventtype = 4\n"
                    // find weekly events
            ."OR (eventtype = 5 AND $dow_startdate = $dow_date)\n"
                    // find monthly events
            ."OR (eventtype = 6 AND $dom_startdate = $dom_date)\n"
                    .")\n"
            ."AND uid = $uid "
            ."ORDER BY starttime";

        $result = $this->_DB->fetchTable($query, true);
        if($result == FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    // returns the event that corresponds to $id
    function get_event_by_id($id)
    {
        $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
        $uid  = $this->Obtain_UID_From_User($user); 

        $query = "SELECT 
                    id,
                    uid,
                    subject event, 
                    startdate date, 
                    enddate `to`, 
                    description, 
                    asterisk_call asterisk_call_me, 
                    recording, 
                    starttime,
                    endtime,
                    call_to, 
                    notification, 
                    emails_notification, 
                    each_repeat as repeat,
                    days_repeat,
                    color,
                    eventtype as it_repeat,
					reminderTimer as reminderTimer,
                    strftime('%H', starttime) hora1, 
                    strftime('%M', starttime) minuto1,
                    strftime('%H', endtime) hora2, 
                    strftime('%M', endtime) minuto2,
                    strftime('%Y', startdate) AS year,\n"
                    ."strftime('%m', startdate) AS month,\n"
                    ."strftime('%d', startdate) AS day,\n"
                    ."strftime('%Y', enddate) AS end_year,\n"
                    ."strftime('%m', enddate) AS end_month,\n"
                    ."strftime('%d', enddate) AS end_day\n"
            ."FROM 
                events\n"
            ."WHERE 
                id = ?\n"
                ."AND uid=?";
        $result = $this->_DB->getFirstRowQuery($query, true, array($id,$uid));
        if($result == FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }

        if(!is_array($result) || count($result) == 0) {
            $this->errMsg = "item doesn't exist!";
            return array();
        }
        return $result;
    }

    function Obtain_UID_From_User($user)
    {
        global $pACL;
        $uid = $pACL->getIdUser($user);
        if($uid!=FALSE)
            return $uid;
        else return -1;
    }

    function obtainExtension($db,$id){
        $query = "SELECT extension FROM acl_user WHERE id=?";

        $result = $db->getFirstRowQuery($query,true,array($id));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result['extension'];
    }

    function Obtain_Recordings_Current_User()
    {
        global $pACL;
        global $arrConf;
        $archivos = array();

        $username = $_SESSION["elastix_user"];
        $ext = $pACL->getUserExtension($username);
        if($ext){
            $folder_path = "/var/lib/asterisk/sounds/custom";
            $path = "$folder_path/$ext";

            $retval = 0;
            if(!file_exists($path)){
                $comando = "mkdir -p $path";
                exec($comando, $output, $retval);
                if ($retval==0){
                    $comando = "ln -s $folder_path/calendarEvent.gsm $path/calendarEvent.gsm";
                    exec($comando, $output, $retval);
                }
            }

            if(!$retval){
                if ($handle = opendir($path)) {
                    while (false !== ($dir = readdir($handle))) {
                        if (ereg("(.*)\.[gsm$|wav$]", $dir, $regs)) {
                            $archivos[$regs[1]] = $regs[1];
                        }
                    }
                }
            }
        }
        return $archivos;
    }

    function Obtain_Protocol($extension)
    {
        if($extension)
        {
            $dsnAsterisk = generarDSNSistema('asteriskuser', 'asterisk');                            
    
            $pDB = new paloDB($dsnAsterisk);
    
            $query = "SELECT dial, description, id FROM devices WHERE id=?";
            $result = $pDB->getFirstRowQuery($query, TRUE, array($extension));
            if($result != FALSE)
                return $result;
            else return FALSE;
        }else return FALSE;
    }

    function insertEvent($uid,$startdate,$enddate,$starttime,$eventtype,$subject,$description,$asterisk_call,$recording,$call_to,$notification,$email_notification, $endtime, $each_repeat,  $checkbox_days, $reminderTimer, $color, $returnId=false){
	$time = time();
        $data = array($uid,$startdate,$enddate,$starttime,$eventtype,$subject,$description,$asterisk_call,$recording,$call_to,$notification,$email_notification,$endtime,$each_repeat,$checkbox_days,$reminderTimer,$color,$time);
        $query = "INSERT INTO events(uid,startdate,enddate,starttime,eventtype,subject,description,asterisk_call,recording,call_to,notification,emails_notification,endtime,each_repeat,days_repeat,reminderTimer,color,last_update) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $result = $this->_DB->genQuery($query, $data);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	$id = $this->getLastInsertIdEvent();
	$result = $this->addHistory($id,$uid,"","event",$time,"create","An event was created: id=$id, uid=$uid, startdate=$startdate, enddate=$enddate, starttime=$starttime, eventtype=$eventtype, subject=$subject, description=$description, asterisk_call=$asterisk_call, recording=$recording, call_to=$call_to, notification=$notification, emails_notification=$email_notification, endtime=$endtime, each_repeat=$each_repeat, days_repeat=$checkbox_days, reminderTimer=$reminderTimer, color=$color");
	if($result==FALSE){
	    $this->errMsg = $this->_DB->errMsg;
            return false;
	}
	if($returnId)
	    return $id;
	else
	    return true; 
    }

    function updateEvent($id_event,$startdate,$enddate,$starttime,$eventtype,$subject,$description,$asterisk_call,$recording,$call_to,$notification,$email_notification, $endtime ,$each_repeat,$checkbox_days,$reminderTimer, $color, $uid){
	$time = time();
        $data = array($startdate,$enddate,$starttime,$eventtype,$subject,$description,$asterisk_call,$recording,$call_to,$notification,$email_notification,$endtime,$each_repeat,$checkbox_days,$reminderTimer,$color,$time,$id_event);
        $query = "UPDATE events SET  startdate=?,enddate=?,starttime=?,eventtype=?,subject=?,description=?,asterisk_call=?,recording=?,call_to=?,notification=?,emails_notification=?,endtime=?,each_repeat=?,days_repeat=?,reminderTimer=?,color=?,last_update=? WHERE id=?";
        
        $result = $this->_DB->genQuery($query, $data);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }

	$result = $this->addHistory($id_event,$uid,"","event",$time,"modify","An event was modified: id=$id_event, startdate=$startdate, enddate=$enddate, starttime=$starttime, eventtype=$eventtype, subject=$subject, description=$description, asterisk_call=$asterisk_call, recording=$recording, call_to=$call_to, notification=$notification, emails_notification=$email_notification, endtime=$endtime, each_repeat=$each_repeat, days_repeat=$checkbox_days, reminderTimer=$reminderTimer, color=$color");
	if($result==FALSE){
	    $this->errMsg = $this->_DB->errMsg;
            return false;
	}
        return true; 
    }

    function updateDateEvent($id_event,$startdate,$enddate,$starttime,$endtime,$day_repeat){
        $data = array($startdate, $enddate, $starttime, $endtime, $day_repeat, time(), $id_event);
        $query = "UPDATE events SET  startdate=?,enddate=?,starttime=?,endtime=?,days_repeat=?,last_update=? WHERE id=?";

        $result = $this->_DB->genQuery($query, $data);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }

	$id_user = $this->getUserOfEvent($id_event);

	$result = $this->addHistory($id_event,$id_user,"","event",$time,"modify","An event was modified: id=$id_event, startdate=$startdate, enddate=$enddate, starttime=$starttime, endtime=$endtime, days_repeat=$day_repeat");
	if($result==FALSE){
	    $this->errMsg = $this->_DB->errMsg;
            return false;
	}
        return true; 
    }

    function deleteEvent($id_event, $id_user){
        $query = "DELETE FROM events WHERE id=? and uid=?";
        $data = array($id_event,$id_user);
        $result = $this->_DB->genQuery($query, $data);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	$time = time();
	$result = $this->addHistory($id_event,$id_user,"","event",$time,"delete","An event was deleted");
	if($result==FALSE){
	    $this->errMsg = $this->_DB->errMsg;
            return false;
	}
        return true; 
    }

    function getContactByTag($db, $tag, $userid)
    {
        $query = "SELECT  (lower(name)||' '||lower(last_name)||' '||'&lt;'||email||'&gt;') AS caption,id AS value FROM contact WHERE (iduser = ? or status='isPublic') and (name like ? or last_name like ? or email like ?) and email <> ''";
        $data = array($userid, "%$tag%", "%$tag%", "%$tag%");
        $result = $db->fetchTable($query,true, $data);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getContactByEmail($db, $tag, $userid)
    {
        $query = "SELECT  email AS caption,id AS value FROM contact WHERE (iduser = ? or status='isPublic')";
        $data = array($userid);
        $result = $db->fetchTable($query,true,$data);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getEventByDate($startdate, $enddate, $uid){
        $query = "SELECT * FROM events WHERE uid = ? AND ((startdate <= ? AND enddate >= ?) OR (startdate >= ? AND enddate <= ?) OR (startdate <= ? AND enddate >= ?) OR (startdate >= ? AND enddate >= ?))";

        $result = $this->_DB->fetchTable($query,true,array($uid,$startdate,$enddate,$startdate,$enddate,$startdate,$startdate,$startdate,$enddate));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getLastInsertIdEvent(){
        $query = "SELECT id FROM events order by id desc";
        $result = $this->_DB->getFirstRowQuery($query, TRUE);
        if($result != FALSE || $result != "")
            return $result['id'];
        else
            return false;
    }

    function getAllEvents(){
        $query = "SELECT * FROM events";
        $result = $this->_DB->fetchTable($query,true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getAllEventsByUid($uid){
        $query = "SELECT * FROM events WHERE uid = ?";
        $result = $this->_DB->fetchTable($query,true,array($uid));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getEventIdByUid($uid, $idEvent){
        $query = "SELECT * FROM events WHERE uid = ? and id=?";
        $result = $this->_DB->fetchTable($query,true,array($uid,$idEvent));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getNameUsers($id_user,$db)
    {
        $query = "SELECT name FROM acl_user WHERE id=?";
    	$username = $_SESSION["elastix_user"];
        $result = $db->getFirstRowQuery($query,true,array($id_user));
        if($result != FALSE || $result != "")
            return $result['name'];
        else
            return $username;
    }

    function getDescUsers($id_user,$db)
    {
        $query = "SELECT description FROM acl_user WHERE id=?";
    	$username1 = $_SESSION["elastix_user"];
        $result = $db->getFirstRowQuery($query,true,array($id_user));
        if($result != FALSE || $result != "")
            return $result['description'];
        else
            return $username1;
    }

    function existPassword($pass){
        $query = "SELECT password FROM share_calendar WHERE password = ?";
        $result = $this->_DB->getFirstRowQuery($query,true,array($pass));
        if($result==FALSE || $result==null || $result==""){
            $this->errMsg = $this->_DB->errMsg;
            return false; //no existe
        }
        return true; // existe
    }

    function createShareCalendar($uid_from, $user, $password){
        $query = "INSERT INTO share_calendar(uid_from,uid_to,user,password,confirm) VALUES(?,'',?,?,'FALSE')";

        $result = $this->_DB->genQuery($query,array($uid_from,$user,$password));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true; 
    }

    function getUidFrom($userEXT,$passEXT){
        $query = "SELECT uid_from FROM share_calendar WHERE user=? and password=?";
        $result = $this->_DB->getFirstRowQuery($query,true,array($userEXT,$passEXT));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result['uid_from'];
    }

    function getUserNameFromById()
    {
        global $pACL;
        global $arrConf;
        $idUser = $_SESSION["elastix_userFromUid"];
        $name = $pACL->getNameFromIdUser($idUser);
        return $name;
    }

    /*************************************************  new  ***************************************************************/

    function AsteriskManagerAPI($action, $parameters, $return_data=false) 
    {
        global $arrLang;
        $astman_host = "127.0.0.1";
        $astman_user = 'admin';
        $astman_pwrd = obtenerClaveAMIAdmin();

        $astman = new AGI_AsteriskManager();

        if (!$astman->connect("$astman_host", "$astman_user" , "$astman_pwrd")) {
            $this->errMsg = _tr("Error when connecting to Asterisk Manager");
        } else{
            $salida = $astman->send_request($action, $parameters);
            $astman->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                if($return_data) return $salida;
                else return explode("\n", $salida["Response"]);
            }else return false;
        }
        return false;
    }

    function makeCalled($number_org, $number_dst, $textToSpeach)
    {
        $dataExt    = $this->getDataExt($number_org);
        $variables  = "TTS=\"$textToSpeach\"";
        //$parameters = $this->Originate($number_org, $number_dst, $dataExt['dial'], $dataExt['cidname'], "festival-event", $variables, "Festival", "hello");
        $parameters = $this->Originate($number_org, $number_dst, $dataExt['dial'], $dataExt['cidname'], "from-internal", $variables, "Festival", $textToSpeach);

        return $this->AsteriskManagerAPI("Originate",$parameters);
    }

    function Originate($origen, $destino, $channel="", $description="", $context="", $variables="", $aplication="", $data="")
    {
        $parameters = array();
        $parameters['Channel']      = $channel;
        $parameters['CallerID']     = "$description <$origen>";
        $parameters['Exten']        = $destino;
        $parameters['Context']      = $context;
        $parameters['Priority']     = 1;
        $parameters['Application']  = $aplication;
        $parameters['Data']         = $data;
        $parameters['Variable']     = $variables;

        return $parameters;
    }

    function getDataExt($ext)
    {
        $parameters = array('Command'=>"database show AMPUSER $ext/cidname");
        $data = $this->AsteriskManagerAPI("Command",$parameters,true);
        $arrData = explode("\n",$data['data']);
        $arrData = isset($arrData[1])?$arrData[1]:"";
        $arrData = explode(":",$arrData);
        $salida['cidname'] = isset($arrData[1])?trim($arrData[1]):"";

        $parameters = array('Command'=>"database show DEVICE  $ext/dial");
        $data = $this->AsteriskManagerAPI("Command",$parameters,true);
        $arrData = explode("\n",$data['data']);
        $arrData = isset($arrData[1])?$arrData[1]:"";
        $arrData = explode(":",$arrData);
        $salida['dial'] = isset($arrData[1])?trim($arrData[1]):"";

        return $salida;
    }

    function festivalUp()
    {
        exec("service festival status", $flag, $status);
        if($status == 0){
            return true;
        }
        return false;
    }

    function addHistory($id_register,$id_user,$status,$type,$timestamp,$action,$description)
    {
	$query = "INSERT INTO history (id_register,id_user,status,type,timestamp,action,description) VALUES(?,?,?,?,?,?,?)";
	$result = $this->_DB->genQuery($query,array($id_register,$id_user,$status,$type,$timestamp,$action,$description));
	if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true; 
    }

    function getLastQueueid()
    {
	$query = "SELECT id FROM queues order by id desc";
        $result = $this->_DB->fetchTable($query, TRUE);
        if($result === FALSE || count($result) == 0)
            return 0;
        else{
	    $idNumber = 0;
	    foreach($result as $value){
		$number = explode("-",$value["id"]);
		if($number[2] > $idNumber)
		    $idNumber = $number[2];
	    }
            return $idNumber;
	}
    }

    function addQueue($data,$type,$user)
    {
	$id = $this->getLastQueueid();
	$next = $id + 1;
	$id = "queue-event-$next";
	$query = "INSERT INTO queues (id,type,user,data,status) VALUES (?,?,?,?,'NEW')";
	$result = $this->_DB->genQuery($query, array($id,$type,$user,$data));
	if($result == FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return FALSE;
	}
	else
	    return $id;
    }

    function getDataTicket($ticket,$id_user)
    {
	$query = "SELECT * FROM queues WHERE id=? AND user=? AND type='event'";
	$result = $this->_DB->getFirstRowQuery($query, TRUE, array($ticket,$id_user));
	if($result === FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return NULL;
	}
	elseif(count($result) == 0)
	    return FALSE;
	else
	    return $result;
    }

    function removeQueue($ticket)
    {
	$query = "DELETE FROM queues WHERE id=?";
	$result = $this->_DB->genQuery($query, array($ticket));
	if($result == FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return FALSE;
	}
	else
	    return TRUE;
    }

    function getUserEvents($id_user,$fields=NULL)
    {
	if(is_null($fields))
	    $fields = "*";
	$query = "SELECT $fields FROM events WHERE uid=?";
	$result = $this->_DB->fetchTable($query, true, array($id_user));
	if($result === FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return FALSE;
	}
	else
	    return $result;
    }

    function getEventsAfterSync($last_sync,$events,$id_user,$dataResponse)
    {
	$query = "SELECT * FROM events WHERE last_update > ? AND uid=?";
	$result = $this->_DB->fetchTable($query, true, array($last_sync,$id_user));
	if($result === FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return FALSE;
	}
	else{
	    $query = "SELECT * FROM history WHERE timestamp > ? AND action='delete' AND id_user=? AND type='event'";
	    $deleted_events = $this->_DB->fetchTable($query, true, array($last_sync,$id_user));
	    if($deleted_events === FALSE){
		$this->errMsg = $this->_DB->errMsg;
		return FALSE;
	    }
	    foreach($deleted_events as $key => $deleted){
		$remove = FALSE;
		foreach($events as $event){
		    if($deleted["id_register"] == $event->id && $deleted["timestamp"] < $event->last_update){
			$remove = TRUE;
			break;
		    }
		}
		if(!$remove){
		  $next = count($result);
		  $result[$next]["id"] = $deleted["id_register"];
		  $result[$next]["delete"] = "yes";  
		}
	    }
	    $arrEvents = array();
	    foreach($result as $key => $value){
		$remove = FALSE;
		$isFromClient = FALSE;
		if(is_array($dataResponse) && count($dataResponse) > 0){
		    foreach($dataResponse as $data){
			if($value["id"] == $data->id){
			    $value["id_client"] = $data->id_client;
			    $isFromClient = TRUE;
			    break;
			}   
		    }
		}
		if(!$isFromClient){
		    foreach($events as $event){
			if(isset($event->id)){
			    if($event->id == $value["id"]){
				if(isset($event->id_client))
				    $value["id_client"] = $event->id_client;
				if(isset($event->uid))
				    if($event->uid != $value["uid"])
					break;
				if(isset($event->startdate))
				    if($event->startdate != $value["startdate"])
					break;
				if(isset($event->enddate))
				    if($event->enddate != $value["enddate"])
					break;
				if(isset($event->starttime))
				    if($event->starttime != $value["starttime"])
					break;
				if(isset($event->eventtype))
				    if($event->eventtype != $value["eventtype"])
					break;
				if(isset($event->subject))
				    if($event->subject != $value["subject"])
					break;
				if(isset($event->description))
				    if($event->description != $value["description"])
					break;
				if(isset($event->asterisk_call))
				    if($event->asterisk_call != $value["asterisk_call"])
					break;
				if(isset($event->recording))
				    if($event->recording != $value["recording"])
					break;
				if(isset($event->call_to))
				    if($event->call_to != $value["call_to"])
					break;
				if(isset($event->notification))
				    if($event->notification != $value["notification"])
					break;
				if(isset($event->emails_notification))
				    if($event->emails_notification != $value["emails_notification"])
					break;
				if(isset($event->endtime))
				    if($event->endtime != $value["endtime"])
					break;
				if(isset($event->each_repeat))
				    if($event->each_repeat != $value["each_repeat"])
					break;
				if(isset($event->days_repeat))
				    if($event->days_repeat != $value["days_repeat"])
					break;
				if(isset($event->reminderTimer))
				    if($event->reminderTimer != $value["reminderTimer"])
					break;
				if(isset($event->color))
				    if($event->color != $value["color"])
					break;
				$remove = TRUE;
			    }
			}
		    }
		}
		if(!$remove){
		    if($isFromClient)
                        $value["new"] = "no";
                    else{ // Se verifica si es un registro nuevo para el cliente
                        $query = "SELECT COUNT(*) FROM history WHERE type='event' AND action='create' AND id_register=? AND id_user=? AND timestamp > ?";
                        $created = $this->_DB->getFirstRowQuery($query,FALSE,array($value["id"],$id_user,$last_sync));
                        if($created === FALSE){
                            $this->errMsg = $this->_DB->errMsg;
                            return FALSE;
                        }
                        if($created[0] == 0)
                            $value["new"] = "no";
                        else
                            $value["new"] = "yes";
                    }

		    $next = count($arrEvents);
		    $arrEvents[$next] = $value;
		    if(!isset($arrEvents[$next]["delete"]))
			$arrEvents[$next]["delete"] = "no";
		}
	    }
	    return $arrEvents;
	}
    }

    function getUserOfEvent($id_event)
    {
	$query = "SELECT uid FROM events WHERE id=?";
	$result = $this->_DB->getFirstRowQuery($query, TRUE, array($id_event));
	if($result == FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return "";
	}
	else
	    return $result["uid"];
    }

    function getUnsolvedQueues()
    {
	$query = "SELECT * FROM queues WHERE type='event' AND status='NEW'";
	$result = $this->_DB->fetchTable($query, true);
	if($result === FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return FALSE;
	}
	else
	    return $result;
    }

    function changeStatusQueue($id,$status)
    {
	$query = "UPDATE queues SET status=? WHERE id=?";
	$result = $this->_DB->genQuery($query,array($status,$id));
	if($result === FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return FALSE;
	}
	else
	    return TRUE;
    }

    function eventDeleted($id,$id_user)
    {
	$query = "SELECT * FROM history WHERE id_register=? AND type='event' AND action='delete' AND id_user=?";
	$result = $this->_DB->getFirstRowQuery($query,TRUE,array($id,$id_user));
	if($result === FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return FALSE;
	}
	else
	    return $result;
    }

    function eventExists($id)
    {
	$query = "SELECT COUNT(*) FROM events WHERE id=?";
	$result = $this->_DB->getFirstRowQuery($query,FALSE,array($id));
	if($result === FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return NULL;
	}
	elseif($result[0] == 0)
	    return FALSE;
	else
	    return TRUE;
    }

    function addEventWithId($id,$uid,$startdate,$enddate,$starttime,$eventtype,$subject,$description,$asterisk_call,$recording,$call_to,$notification,$email_notification, $endtime, $each_repeat,  $checkbox_days, $reminderTimer, $color)
    {
	$time = time();
        $data = array($id,$uid,$startdate,$enddate,$starttime,$eventtype,$subject,$description,$asterisk_call,$recording,$call_to,$notification,$email_notification,$endtime,$each_repeat,$checkbox_days,$reminderTimer,$color,$time);
	$query = "INSERT INTO events(id,uid,startdate,enddate,starttime,eventtype,subject,description,asterisk_call,recording,call_to,notification,emails_notification,endtime,each_repeat,days_repeat,reminderTimer,color,last_update) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$result = $this->_DB->genQuery($query,$data);
	if($result === FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return FALSE;
	}
	else
	    return TRUE;
    }

    function setQueueDataResponse($id,$dataResponse)
    {
	$query = "UPDATE queues SET response_data=? WHERE id=?";
	$result = $this->_DB->genQuery($query,array($dataResponse,$id));
	if($result === FALSE){
	    $this->errMsg = $this->_DB->errMsg;
	    return FALSE;
	}
	else
	    return TRUE;
    }
}
?>
