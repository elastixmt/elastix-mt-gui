<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4                                                |
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
  $Id: puntosF_Calendar.class.php,v 1.0 2011-03-31 10:15:00 Alberto Santos F.  asantos@palosanto.com Exp $*/

define('ROOT', $_SERVER["DOCUMENT_ROOT"]);
define('SOAP_DATE_FORMAT', 'Y-m-dP');
define('SOAP_DATETIME_FORMAT', 'Y-m-d\TH:i:sP');

require_once(ROOT."/libs/misc.lib.php");
require_once(ROOT."/configs/default.conf.php");
require_once(ROOT."/modules/calendar/libs/paloSantoCalendar.class.php");
require_once(ROOT."/modules/calendar/configs/default.conf.php");
require_once(ROOT."/libs/paloSantoACL.class.php");
require_once(ROOT."/libs/paloSantoDB.class.php");
require_once(ROOT."/libs/paloSantoLongPoll.class.php");

$arrConf = array_merge($arrConf,$arrConfModule);

class core_Calendar extends LongPoll
{
    /**
     * Description error message
     *
     * @var array
     */
    private $errMsg;

    /**
     * Array that contains a paloDB Object, the key is the DSN of a specific database
     *
     * @var array
     */
    private $_dbCache;

    /**
     * ACL User ID for authenticated user
     *
     * @var integer
     */
    private $_id_user;

    /**
     * Object paloACL
     *
     * @var object
     */
    private $_pACL;

    /**
     * String with the id of a queue
     *
     * @var string
     */
    private $_ticket;

    /**
     * Constructor
     *
     */
    public function core_Calendar()
    {
        $this->_dbCache = array();
        $this->_id_user = NULL;
	$this->_ticket  = NULL;
        $this->errMsg   = NULL;
        $this->_pACL    = NULL;
	parent::__construct();
    }

    /**
     * Static function that creates an array with all the functional points with the parameters IN and OUT
     *
     * @return  array     Array with the definition of the function points.
     */
    public static function getFP()
    {
        $arrData["listCalendarEvents"]["params_IN"] = array(
            "startdate"       => array("type" => "date",     "required" => true),
            "enddate"         => array("type" => "date",     "required" => true)
        );

        $arrData["listCalendarEvents"]["params_OUT"] = array(
            "events"         => array("type" => "array",   "required" => true, "minOccurs"=>"0", "maxOccurs"=>"unbounded",
                "params" => array(
                    "id"                     => array("type" => "positiveInteger",  "required" => true),
                    "startdate"              => array("type" => "date",             "required" => true),
                    "enddate"                => array("type" => "date",             "required" => true),
                    "starttime"              => array("type" => "dateTime",         "required" => true),
                    "endtime"                => array("type" => "dateTime",         "required" => true),
                    "subject"                => array("type" => "string",           "required" => true),
                    "description"            => array("type" => "string",           "required" => true),
                    "asterisk_call"          => array("type" => "boolean",          "required" => true),
                    "recording"              => array("type" => "string",           "required" => false),
                    "call_to"                => array("type" => "string",           "required" => false),
                    "reminder_timer"         => array("type" => "positiveInteger",  "required" => false),
                    "emails_notification"    => array("type" => "string",           "required" => true, "minOccurs"=>"0", "maxOccurs"=>"unbounded")
                        )
                    )
            );

        $arrData["addCalendarEvent"]["params_IN"] = array(
            "startdate"            => array("type" => "dateTime",         "required" => true),
            "enddate"              => array("type" => "dateTime",         "required" => true),
            "subject"              => array("type" => "string",           "required" => true),
            "description"          => array("type" => "string",           "required" => true),
            "asterisk_call"        => array("type" => "boolean",          "required" => true),
            "recording"            => array("type" => "string",           "required" => false),
            "call_to"              => array("type" => "string",           "required" => false),
            "reminder_timer"       => array("type" => "positiveInteger",  "required" => false),
            "color"                => array("type" => "string",           "required" => false),
            "emails_notification"  => array("type" => "string",           "required" => true, "minOccurs"=>"0", "maxOccurs"=>"unbounded")
        );

        $arrData["addCalendarEvent"]["params_OUT"] = array(
            "return"        => array("type" => "boolean",   "required" => true)
        );

        $arrData["delCalendarEvent"]["params_IN"] = array(
            "id"            => array("type" => "positiveInteger",    "required" => true)
        );

        $arrData["delCalendarEvent"]["params_OUT"] = array(
            "return"        => array("type" => "boolean",   "required" => true)
        );

        return $arrData;
    }

    /**
     * Function that creates, if do not exist in the attribute dbCache, a new paloDB object for the given DSN
     *
     * @param   string   $sDSN   DSN of a specific database
     * @return  object   paloDB object for the entered database
     */
    private function & _getDB($sDSN)
    {
        if (!isset($this->_dbCache[$sDSN])) {
            $this->_dbCache[$sDSN] = new paloDB($sDSN);
        }
        return $this->_dbCache[$sDSN];
    }

    /**
     * Function that creates, if do not exist in the attribute _pACL, a new paloACL object
     *
     * @return  object   paloACL object
     */
    private function & _getACL()
    {
        global $arrConf;

        if (is_null($this->_pACL)) {
            $pDB_acl = $this->_getDB($arrConf['elastix_dsn']['acl']);
            $this->_pACL = new paloACL($pDB_acl);
        }
        return $this->_pACL;
    }

    /**
     * Function that reads the login user ID, that assumed is on $_SERVER['PHP_AUTH_USER']
     *
     * @return  integer   ACL User ID for authenticated user, or NULL if the user in $_SERVER['PHP_AUTH_USER'] does not exist
     */
    private function _leerIdUser()
    {
        if (!is_null($this->_id_user)) return $this->_id_user;

        $pACL = $this->_getACL();        
        $id_user = $pACL->getIdUser($_SERVER['PHP_AUTH_USER']);
        if ($id_user == FALSE) {
            $this->errMsg["fc"] = 'INTERNAL';
            $this->errMsg["fm"] = 'User-ID not found';
            $this->errMsg["fd"] = 'Could not find User-ID in ACL for user '.$_SERVER['PHP_AUTH_USER'];
            $this->errMsg["cn"] = get_class($this);
            return NULL;
        }
        $this->_id_user = $id_user;
        return $id_user;    
    }

    /**
     * Function that verifies if the parameter can be parsed as a date, and returns the canonic value of the date
     * like yyyy-mm-dd in local time.
     *
     * @param   string   $sDateString   string date to be parsed as a date
     * @return  date     parsed date, or NULL if the $sDateString can not be parsed
     */
    private function _checkDateFormat($sDateString)
    {
        $sTimestamp = strtotime($sDateString);
        if ($sTimestamp === FALSE) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Unrecognized date format, expected yyyy-mm-dd';
            $this->errMsg["cn"] = get_class($this);
            return NULL;
        }
        return date('Y-m-d', $sTimestamp);
    }

    /**
     * Function that verifies if the authenticated user is authorized to the passed module.
     *
     * @param   string   $sModuleName   name of the module to check if the user is authorized
     * @return  boolean    true if the user is authorized, or false if not
     */ 
    private function _checkUserAuthorized($sModuleName)
    {
        $pACL = $this->_getACL();        
        $id_user = $this->_leerIdUser();
        if (!$pACL->isUserAuthorizedById($id_user, "access", $sModuleName)) { 
            $this->errMsg["fc"] = 'UNAUTHORIZED';
            $this->errMsg["fm"] = 'Not authorized for this module: '.$sModuleName;
            $this->errMsg["fd"] = 'Your user login is not authorized for this functionality. Please contact your system administrator.';
            $this->errMsg["cn"] = get_class($this);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Function that verifies if the parameter can be parsed as a date, and returns the canonic value of the date
     * like yyyy-mm-dd hh:mm:ss in local time.
     *
     * @param   string   $sDateString   string date to be parsed as a date time
     * @return  date     parsed date, or NULL if the $sDateString can not be parsed
     */
    private function _checkDateTimeFormat($sDateString)
    {
        $sTimestamp = strtotime($sDateString);
        if ($sTimestamp === FALSE) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Unrecognized date format, expected yyyy-mm-dd hh:mm:ss';
            $this->errMsg["cn"] = get_class($this);
            return NULL;
        }
        return date('Y-m-d H:i:s', $sTimestamp);
    }

    /**
     * Procedure starts sending emails to an event.
     *
     * @param   integer   $idEvento              ID of the Event
     * @param   date      $sFechaInicio          Starting date of event
     * @param   date      $sFechaFinal           Ending date of event
     * @param   string    $subject               Subject of event
     * @param   array     $emails_notification   Array with the direction emails
     * @param   string    $description           Description of event
     * @param   integer   $sTema                 Theme of the event
     */
    private function _enviarCorreosNotificacionEvento($idEvento, $sFechaInicio, $sFechaFinal, $subject, $emails_notification, $description, $sTema)
    {
        // El siguiente archivo define la clase PHPMailer
        include_once('/var/www/html/libs/phpmailer/class.phpmailer.php');

        /* Leer el nombre del usuario. 
         * TODO: si se repite varias veces el acto de averiguar el nombre del 
         * usuario, es mejor si se lo envía a un método. */
        $pACL =& $this->_getACL();
        $tuplaUser = $pACL->getUsers($this->_id_user);
        $sNombreUsuario = $tuplaUser[0][1];
        $sDescUsuario = $tuplaUser[0][2];
        
        $sContenidoCorreo = $this->_generarContenidoCorreoEvento($subject, $description, $sNombreUsuario, $sFechaInicio, $sFechaFinal, $sTema);
        $sContenidoIcal = $this->_generarContenidoIcal($idEvento, $subject, 
            $sFechaInicio, $sFechaFinal);
        $sHostname = `hostname`; // TODO: mejorar petición de nombre de host
        $sRemitente = 'noreply@'.$sHostname;
        
        $oMail = new PHPMailer();
        $oMail->Host = 'localhost';
        $oMail->Body = $sContenidoCorreo;
        $oMail->IsHTML(true); // Correo HTML
        $oMail->WordWrap = 50; 
        $oMail->From = $sRemitente; 
        $oMail->FromName = $sNombreUsuario;
        // Depende de carga de idiomas hecha por _generarContenidoCorreoEvento()
        $oMail->Subject = _tr($sTema).': '.$subject; 
        $oMail->AddStringAttachment($sContenidoIcal, 'icalout.ics', 'base64', 'text/calendar');
        foreach ($emails_notification as $sDireccionEmail) {
            $regs = NULL;
            if (preg_match('/<(\S+)>/', $sDireccionEmail, $regs)) {
                $sEmail = $regs[1];
            } else {
                $sEmail = $sDireccionEmail;
            }
            $oMail->ClearAddresses();
            $oMail->AddAddress($sEmail, $sDireccionEmail);
            $oMail->Send();
        } 
    }

    /**
     * Function that joins the iCalendar to attach to an email
     *
     * @param   integer   $idEvento              ID of the Event
     * @param   date      $sFechaInicio          Starting date of event
     * @param   date      $sFechaFinal           Ending date of event
     * @param   string    $subject               Subject of event
     * @return  string    string with the content of iCalendar
     */
    private function _generarContenidoIcal($idEvento, $subject, $sFechaInicio, $sFechaFinal)
    {
        $sFechaInicio_ical = gmdate('Ymd\THis\Z', strtotime($sFechaInicio));
        $sFechaFinal_ical = gmdate('Ymd\THis\Z', strtotime($sFechaFinal));
        $sContenido = <<<CONTENIDO_ICAL
BEGIN:VCALENDAR
PRODID:-//Elastix Development Department// Elastix 2.0 //EN
VERSION:2.0

BEGIN:VEVENT
DTSTAMP:{$sFechaInicio_ical}
CREATED:{$sFechaInicio_ical}
UID:0-{$idEvento}
SUMMARY:{$subject}
CLASS:PUBLIC
PRIORITY:5
DTSTART:{$sFechaInicio_ical}
DTEND:{$sFechaFinal_ical}
TRANSP:OPAQUE
SEQUENCE=0
END:VEVENT

END:VCALENDAR
CONTENIDO_ICAL;
        return $sContenido;
    }

    /**
     * Function to join the email message sent to an event.
     *
     * @param   string    $subject            Subject of event
     * @param   string    $description        Description of event
     * @param   string    $sNombreUsuario     Name of user
     * @param   date      $sFechaInicio       Starting date of event
     * @param   date      $sFechaFinal        Ending date of event
     * @param   string    $sTema              Theme of the event
     * @return  string    content of email event
     */
    private function _generarContenidoCorreoEvento($subject, $description, $sNombreUsuario, $sFechaInicio, $sFechaFinal, $sTema)
    {
        load_language_module('calendar', ROOT.'/');

        $sTemaMensaje = _tr($sTema); // Uno de New_Event, Delete_Event
        
        $sContenidoCorreo = <<<CONTENIDO_CORREO
<html>
    <head>
        <title>%s</title>
    </head>
    <body>
        <style>
            .title{
                background-color:#D1E6FA;
                color:#000000;
            }
            .tr{
                background-color:#F1F8FF;
            }
            .td1{
                font-weight: bold;
                color:#b9b2b2; 
                font-size: large;
                width:165px;
            }
            .footer{
                background-color:#EBF5FF;
                color:#b9b2b2;
                font-weight:bolder;
                font-size:12px;
            }
        </style>
        <div>
            <table width='600px'>
                <tr class='title'><td colspan='2'><center><h1>%s</h1></center></td></tr>
                <tr class='tr'><td class='td1'>%s: </td><td>%s.</td></tr>
                <tr class='tr'><td class='td1'>%s: </td><td>%s.</td></tr>
                <tr class='tr'><td class='td1'>%s: </td><td>%s.</td></tr>
                <tr class='tr'><td class='td1'>%s:</td><td>%s.</td></tr>
                <tr class='tr'><td class='td1'>%s: </td><td>%s.</td></tr>
                <tr class='tr'><td class='td1'>%s: </td><td><span>%s.</span></td></tr>
                <tr class='footer'><td colspan='2'><center><span>%s</span></center></td></tr>
            </table>
        </div>
    </body>
</html>
CONTENIDO_CORREO;

        // La manipulación de fechas asume yyyy-mm-dd hh:mm:ss
        return sprintf($sContenidoCorreo, 
            htmlentities($sTemaMensaje, ENT_COMPAT, 'UTF-8'),
            htmlentities($sTemaMensaje, ENT_COMPAT, 'UTF-8'),
            htmlentities(_tr('Event'), ENT_COMPAT, 'UTF-8'),
                htmlentities($subject, ENT_COMPAT, 'UTF-8'),
            htmlentities(_tr('Date'), ENT_COMPAT, 'UTF-8'),
                htmlentities(date('d M Y', strtotime($sFechaInicio)), ENT_COMPAT, 'UTF-8'),
            htmlentities(_tr('To'), ENT_COMPAT, 'UTF-8'),
                htmlentities(date('d M Y', strtotime($sFechaFinal)), ENT_COMPAT, 'UTF-8'),
            htmlentities(_tr('time'), ENT_COMPAT, 'UTF-8'),
                htmlentities(substr($sFechaInicio, 11).' - '.substr($sFechaFinal, 11), ENT_COMPAT, 'UTF-8'),
            htmlentities(_tr('Description'), ENT_COMPAT, 'UTF-8'),
                htmlentities($description, ENT_COMPAT, 'UTF-8'),
            htmlentities(_tr('Organizer'), ENT_COMPAT, 'UTF-8'),
                htmlentities($sNombreUsuario, ENT_COMPAT, 'UTF-8'),
            htmlentities(_tr('footer'), ENT_COMPAT, 'UTF-8')
            );
    }

    /**
     * Procedure to create the call file for a new event is being created. It is assumed that the recording file itself referenced     * exists. We assume that establishing a single audio file for one-time event. The file ends in the directory called Asterisk,     * with the date and time of atime and mtime to initiate appropriate the call at the moment required
     *
     * @param   integer    $idEvento           ID of event
     * @param   integer    $sExtUsuario        Extension of user
     * @param   integer    $sNumDestino        Destiny number
     * @param   string     $sRecording         Recording name file
     * @param   integer    $iRetries           Maximum number of retries
     * @param   date       $iCallTimestamp     Call time stamp
     */
    private function _crearArchivoLlamadaAsterisk($idEvento, $sExtUsuario, 
        $sNumDestino, $sRecording, $iRetries, $iCallTimestamp)
    {
        global $arrConf;
        
        $sContenido = <<<CONTENIDO_ARCHIVO_AUDIO
Channel: Local/{$sNumDestino}@from-internal
CallerID: Calendar Event <{$sExtUsuario}>
MaxRetries: $iRetries
RetryTime: 60
WaitTime: 30
Context: calendar-event
Extension: *7899
Priority: 1
Set: FILE_CALL={$sRecording}
Set: ID_EVENT_CALL={$idEvento}
CONTENIDO_ARCHIVO_AUDIO;
        $sNombreArchivo = "event_{$idEvento}_0.call";
        $sRutaArchivo = $arrConf['output_callfile_base']."/$sNombreArchivo";
        $sNombreTemp = tempnam('/tmp', 'callfile_');
        $r = file_put_contents($sNombreTemp, $sContenido);
        if ($r === FALSE) {
            $this->errMsg["fc"] = 'INTERNALERROR';
            $this->errMsg["fm"] = 'Filesystem operation failed';
            $this->errMsg["fd"] = 'Unable to create callfile for event in calendar';
            $this->errMsg["cn"] = get_class($this);
            return NULL;
        }
        touch($sNombreTemp, $iCallTimestamp, $iCallTimestamp);
        
        // La función rename() de PHP no preserva atime o mtime. Grrrr...
        system("mv $sNombreTemp $sRutaArchivo");
    }

    /**
     * Function for listing audio files associated with the extension indicated by the parameter. It returns an associative array     * whose key Is the short name of the audio, and the value is the audio path on / var / lib / asterisk / sounds /
     *
     * @param   integer    $sExt           Extension to search its audio files
     * @return  array      array with the path of the audio for the associated extension
     */
    private function _listarAudiosExtension($sExt)
    {
        global $arrConf;

        $listaAudios = array();

        // Se listan todos los audios compartidos entre todas las extensiones,
        // y luego los audios de la extensión requerida.
        $listaDir = array($arrConf['custom_audiofile_base']);
        if (is_dir($arrConf['custom_audiofile_base']."/$sExt"))
            $listaDir[] = $arrConf['custom_audiofile_base']."/$sExt";
        foreach ($listaDir as $sDir) {
            $sRelPath = substr($sDir, strlen($arrConf['audiofile_base'].'/'));

            if (!is_dir($sDir)) {
                $this->errMsg["fc"] = 'INTERNALERROR';
                $this->errMsg["fm"] = 'Internal error';
                $this->errMsg["fd"] = 'Invalid path '.$sDir;
                $this->errMsg["cn"] = get_class($this);
                return NULL;
            }
            $hDir = opendir($sDir);
            if (!$hDir) {
                $this->errMsg["fc"] = 'INTERNALERROR';
                $this->errMsg["fm"] = 'Internal error';
                $this->errMsg["fd"] = 'Unable to open path '.$sDir;
                $this->errMsg["cn"] = get_class($this);
                return NULL;
            }
            while (false !== ($sNombre = readdir($hDir))) {
                $regs = NULL;
                if (preg_match('/^(.*)\.(gsm|wav)$/', $sNombre, $regs))
                    $listaAudios[$regs[1]] = $sRelPath.'/'.$regs[1];
            }
            closedir($hDir);
        }
        return $listaAudios;
    }

    /**
     * Function that gets the extension of the login user, that assumed is on $_SERVER['PHP_AUTH_USER']
     *
     * @return  string   extension of the login user, or NULL if the user in $_SERVER['PHP_AUTH_USER'] does not have an extension     *                   assigned
     */
    private function _leerExtension()
    {
        // Identificar el usuario para averiguar el número telefónico origen
        $id_user = $this->_leerIdUser();

        $pACL = $this->_getACL();        
        $user = $pACL->getUsers($id_user);
        if ($user == FALSE) {
            $this->errMsg["fc"] = 'ACL';
            $this->errMsg["fm"] = 'ACL lookup failed';
            $this->errMsg["fd"] = 'Unable to read information from ACL - '.$pACL->errMsg;
            $this->errMsg["cn"] = get_class($pACL);
            return NULL;
        }
        
        // Verificar si tiene una extensión
        $extension = $user[0][3];
        if ($extension == "") {
            $this->errMsg["fc"] = 'EXTENSION';
            $this->errMsg["fm"] = 'Extension lookup failed';
            $this->errMsg["fd"] = 'No extension has been set for user '.$_SERVER['PHP_AUTH_USER'];
            $this->errMsg["cn"] = get_class($pACL);
            return NULL;
        }

        return $extension;        
    }

    private function _rechazar_correo_vacio($email)
    {
        if(preg_match("/^<?[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*@[a-z0-9]+([\._\-]?[a-z0-9]+)*(\.[a-z0-9]{2,4})+>?$/",trim($email)))
            return TRUE;
        else
            return FALSE;
    }

/*
    public function getFunction()
    {
        return array(
            "newEvent" => array(
                "params" => array(
                    "user",
                    "name",
                    "description",
                    "date_ini",
                    "date_end",
                    "call_to",
                    "reminder",
                    "reminderTime",
                    "notification",
                    "emails",
                    "color",
                    "recording"
                ),
                "return" => ""),
            "editEvent" => array(
                "params" => array(
                    "id_event",
                    "user",
                    "name",
                    "description",
                    "date_ini",
                    "date_end",
                    "call_to",
                    "reminder",
                    "reminderTime",
                    "notification",
                    "emails",
                    "color",
                    "recording"
                ),
                "return" => ""),
            "showEventById" => array(
                "params" => array(
                    "id_event",
                    "user"
                ),
                "return" => ""),
            "showEventByDate" => array(
                "params" => array(
                    "user",
                    "start_time",
                    "end_time"
                ),
                "return" => ""),
            "removeEvent" => array(
                "params" => array(
                    "id_event",
                    "user"
                ),
                "return" => "")
        );
    }

    public function newEvent($user, $name, $description, $date_ini, $date_end, $call_to, $reminder, $remainerTime, $notification, $emails, $color, $recording)
    {
        return $this->saveEvent("new",null,$user,$name,$description,$date_ini,$date_end,$call_to,$reminder,$remainerTime,$notification,$emails,$color,$recording);
    }

    public function editEvent($id_event, $user, $name, $description, $date_ini, $date_end, $call_to, $reminder, $remainerTime, $notification, $emails, $color, $recording)
    {
        return $this->saveEvent("edit",$id_event,$user,$name,$description,$date_ini,$date_end,$call_to,$reminder,$remainerTime,$notification,$emails,$color,$recording);
    }

    public function showEventById($id_event, $user)
    {
        $pDB       = new paloDB($this->arrConf['dsn_conn_database']);
        $pCalendar = new paloSantoCalendar($pDB);

        $pDBACL    = new paloDB($this->arrConf['dsn_conn_database1']);
        $pACL      = new paloACL($pDBACL);
        $id_user   = $pACL->getIdUser($user);
        if(!$id_user){
            $this->errorMSG = "Error user, no existe";
            return false;
        }

        $arrData   = $pCalendar->getEventIdByUid($id_user, $id);

        if(is_null($arrData)){
            $this->errorMSG = "Error data,".$pCalendar-errMsg;
            return null;
        }
        return $arrData;
    }

    public function showEventByDate($user, $start, $end)
    {
        $pDB       = new paloDB($this->arrConf['dsn_conn_database']);
        $pCalendar = new paloSantoCalendar($pDB);

        $pDBACL    = new paloDB($this->arrConf['dsn_conn_database1']);
        $pACL      = new paloACL($pDBACL);
        $id_user   = $pACL->getIdUser($user);

        $start_time = date('Y-m-d', $start);
        $end_time   = date('Y-m-d', $end);

        if(!$id_user){
            $this->errorMSG = "Error user, no existe";
            return false;
        }

        $year  = date('Y');
        $month = date('m');
        $day   = date('d');

        $arrData = $pCalendar->getEventByDate($start_time, $end_time, $id_user);
        if(is_null($arrData)){
            $this->errorMSG = "Error data,".$pCalendar-errMsg;
            return null;
        }
        return $arrData;
    }

    public function removeEvent($id_event, $user)
    {
        $pDB       = new paloDB($this->arrConf['dsn_conn_database']);
        $pCalendar = new paloSantoCalendar($pDB);

        $pDBACL    = new paloDB($this->arrConf['dsn_conn_database1']);
        $pACL      = new paloACL($pDBACL);

        $id_user   = $pACL->getIdUser($user);
        if(!$id_user){
            $this->errorMSG = "Error user, no existe";
            return false;
        }

        $data = $pCalendar->getEventById($id, $id_user);

        if($data!="" && isset($data)){
            if($pCalendar->deleteEvent($id, $id_user))
                return true;
            else{
                $this->errorMSG = "Error delete,".$pCalendar-errMsg;
                return false;
            }
        }
        else{
            $this->errorMSG = "Error delete, no se puede eliminar el evento";
            return false;
        }
    }

    private function saveEvent($action, $id_event, $user, $name, $description, $date_ini, $date_end, $call_to, $reminder, $remainerTime, $notification, $emails, $color, $recording)
    {
        $pDB       = new paloDB($this->arrConf['dsn_conn_database']);
        $pCalendar = new paloSantoCalendar($pDB);

        $pDBACL    = new paloDB($this->arrConf['dsn_conn_database1']);
        $pACL      = new paloACL($pDBACL);
        $id_user   = $pACL->getIdUser($user);
        if(!$id_user){
            $this->errorMSG = "Error user, no existe";
            return false;
        }

        $ext       = $pACL->getUserExtension($user);

        if(!preg_match("/^#\w{3,6}$/",$color))
            $color = "#3366CC";

        $start_event   = strtotime($date_ini);
        $end_event     = strtotime($date_end);

        if($name == ""){
            $this->errorMSG = "Error campo esta vacio";
            return false;
        }

        if($start_event <= $end_event){
            if($reminder == "on"){ //Configure a phone call reminder
                if($call_to==null || $call_to==""){
                    $link = "<a href='?menu=userlist'>user_list</a>";
                    $this->errorMSG = 'error_ext'.$link;
                    return false;
                }
            }
            else{
                $call_to   = "";
                $recording = "";
            }
        }

        if($notification == "on") // si ingresa emails o contactos
            $emails = htmlspecialchars_decode($emails); // codifica los caracteres especiales 
        else
            $emails = "";


        $hora      = date('H',strtotime($date_ini));
        $minuto    = date('i',strtotime($date_ini));

        $hora2     = date('H',strtotime($date_end));
        $minuto2   = date('i',strtotime($date_end));

        $start = date('Y-m-d',$start_event);
        $end   = date('Y-m-d',$end_event);
        $starttime = date('Y-m-d',$start_event)." ".$hora.":".$minuto;
        $endtime   = date('Y-m-d',$end_event)." ".$hora2.":".$minuto2;

        $event_type  = 1;
        $num_frec    = 0;
        $each_repeat = 1;


        $isok=false;
        if($action=="new"){
            $isok = $pCalendar->insertEvent($id_user,$start,$end,$starttime,$event_type,$name,$description,$reminder,$recording,$call_to,
                    $notification,$emails,$endtime,$each_repeat,"", $remainerTime, $color);
        }
        else if($action=="edit"){
            $dataUp = $pCalendar->getEventById($id_event,$id_user);
            if($dataUp!="" && isset($dataUp)){
                $isok = $pCalendar->updateEvent($id_event,$start,$end,$starttime,$event_type,$name,$description,$reminder,$recording,$call_to,
                    $notification,$emails,$endtime,$each_repeat,"", $remainerTime, $color);
            }
            else{
                $this->errorMSG = "Error edit, no existe el evento asociado a $id_event";
                return false;
            }
        }

        if($isok){
            $id = $pDB->getLastInsertId();
            /*if($emails != ""){
                $data_Send['emails_notification'] = $emails;
                $data_Send['subject']             = $name;
                $data_Send['description']         = $description;
                $data_Send['startdate']           = $start;
                $data_Send['enddate']             = $end;
                $data_Send['starttime']           = $starttime;
                $data_Send['endtime']             = $endtime;
                $data_Send['eventtype']           = $event_type;
                sendMails($data_Send, $arrLang, "NEW", $this->arrConf,$pDB,$module_name, $id);
            }
            TODO: tener en cuenta la eliminación de los archivos en que escenarios debe de ser necesario
            if($reminder == "on"){
                createRepeatAudioFile($each_repeat,$day_repeat,$starttime,$endtime,$num_frec,$asterisk_calls,$ext,$call_to,$pDB,$id,$arrLang,$this->arrConf,$recording,$remainerTime);
            }
            else{ //borra los .call que existan asociados a este evento
                $dir_outgoing = $arrConf['dir_outgoing'];
                system("rm -f $dir_outgoing/event_{$id}_*.call"); // si existen lo archivos los elimina
            }
            *//*
            return true;
        }
        else{
            $this->errorMSG = "Error save,".$pCalendar->errMsg;
            return false;
        }
    }*/

    /**
     * Functional point that returns the calendar events for the authenticated user
     *
     * @param   date    $startdate         Starting date event
     * @param   date    $enddate           Ending date event
     * @return  array   Array of contacts with the following information:
     *                      id (positiveInteger) in database ID of the event
     *                      startdate (date) Event Start Date
     *                      enddate (date) Date of end of event
     *                      starttime (datetime) Start date and time
     *                      endtime (datetime) final time
     *                      subject (string) Subject Event
     *                      description (string) Long Description of event
     *                      asterisk_call (bool) TRUE if must be generated reminder call
     *                      Recording (string, optional) Name of the recording used to call.
     *                      call_to (string, optional) Extent to which call for Reminder
     *                      reminder_timer (string, optional) number of minutes before which will make the call reminder
     *                      emails_notification (array (string)) Zero or more emails will be notified with a message when creating the
     *                                                           event.
     *                   or false if an error exists
     */
    function listCalendarEvents($startdate, $enddate=NULL, $id_event = NULL)
    {
        global $arrConf;

        if (!$this->_checkUserAuthorized('calendar')) return false;

        // Validación de fechas
	if(is_null($id_event)){
	    $sFechaInicio = $this->_checkDateFormat(isset($startdate) ? $startdate : NULL);
	    if (is_null($sFechaInicio)) return false;
	    if(isset($enddate)){
		$sFechaFinal  = $this->_checkDateFormat($enddate);
		if (is_null($sFechaFinal)) return false;
		if ($sFechaFinal < $sFechaInicio) {
		    $t = $sFechaFinal; $sFechaFinal = $sFechaInicio; $sFechaInicio = $t;
		}
	    }
	}

        // Identificar el usuario para averiguar el ID de usuario en calendario
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;
        // Base de datos del calendario
        $pDB_calendar = $this->_getDB($arrConf['dsn_conn_database']);

	if(isset($id_event)){
	    $arrParam = array($id_user, $id_event);
	    $sql = <<<LEER_EVENTOS
SELECT * FROM events
WHERE uid = ? AND id = ?
LEER_EVENTOS;
	}
	elseif(isset($enddate)){
	    $arrParam = array($sFechaFinal, $sFechaInicio, $id_user);
	    $sql = <<<LEER_EVENTOS
SELECT * FROM events
WHERE   ? >= strftime('%Y-%m-%d', startdate)
    AND ? <= strftime('%Y-%m-%d', enddate)
    AND uid = ?
ORDER BY starttime
LEER_EVENTOS;
	}
	else{  // Se devuelven todos los eventos cuyo fecha de inicio es mayor o igual a la fecha inicio pasada como parámetro
	    $arrParam = array($sFechaInicio, $id_user);
	    $sql = <<<LEER_EVENTOS
SELECT * FROM events
WHERE   ? <= strftime('%Y-%m-%d', startdate)
    AND uid = ?
ORDER BY starttime
LEER_EVENTOS;
	}
        
        $recordset = $pDB_calendar->fetchTable($sql, TRUE, $arrParam);
        if (!is_array($recordset)) {
            $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to read data from calendar - '.$pDB_calendar->errMsg;
            $this->errMsg["cn"] = get_class($this);
            return false;
        }

        $events = array();
        foreach ($recordset as $tupla) {
            $events[] = array(
                'id'            =>  (int)$tupla['id'],

                // Las siguientes 3 son fechas
                'startdate'     =>  date(SOAP_DATE_FORMAT, strtotime($tupla['startdate'])),
                'enddate'       =>  date(SOAP_DATE_FORMAT, strtotime($tupla['enddate'])),
                'starttime'     =>  $tupla['starttime'],
                'endtime'       =>  $tupla['endtime'],

                'subject'       =>  $tupla['subject'],
                'description'   =>  $tupla['description'],
                'asterisk_call' =>  ($tupla['asterisk_call'] == 'on'),
                // Los siguientes 2 campos dependen de asterisk_call
                'recording'     =>  ($tupla['asterisk_call'] == 'on') ? $tupla['recording'] : NULL,
                'call_to'       =>  ($tupla['asterisk_call'] == 'on') ? $tupla['call_to'] : NULL,
                'reminder_timer' => ($tupla['asterisk_call'] == 'on') 
                    ? ((!is_null($tupla['reminderTimer']) && $tupla['reminderTimer'] != '') 
                        ? $tupla['reminderTimer'] 
                        : 0)
                    : NULL,
                'emails_notification' => ($tupla['notification'] == 'on') 
                    ? array_filter(explode(',', $tupla['emails_notification']), array($this, '_rechazar_correo_vacio')) 
                    : array(),
            );
        }
        return array('events' => $events);
    }

    /**
     * Functional point that adds a new event in the calendar of the authenticated user
     *
     * @param   date      $startdate                 Starting date and time of event
     * @param   date      $enddate                   Ending date and time of event
     * @param   string    $subject                   Subject of event
     * @param   string    $description               Long description of event
     * @param   boolean   $asterisk_call             TRUE if must be generated reminder call
     * @param   string    $recording                 (Optional)  Name of the recording used to call
     * @param   string    $call_to                   (Optional) Extension to which call for Reminder
     * @param   string    $reminder_timer            (Optional) Number of minutes before which will make the call reminder
     * @param   array     $emails_notification       Zero or more emails will be notified with a message when creating the event.
     * @param   string    $color                     (Optional) Color for the event
     * @return  boolean   True if the event was successfully created, or false if an error exists
     */
    function addCalendarEvent($startdate,$enddate,$subject,$description,$asterisk_call,$recording,$call_to,$reminder_timer,$emails_notification, $color, $getIdInserted=FALSE)
    {
        global $arrConf;

        if (!$this->_checkUserAuthorized('calendar')) return false;

        // Identificar el usuario para averiguar el ID de usuario en calendario
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;

        // Validación de instantes de inicio y final
        $sFechaInicio = $this->_checkDateTimeFormat(isset($startdate) ? $startdate : NULL);
        $sFechaFinal  = $this->_checkDateTimeFormat(isset($enddate) ? $enddate : NULL);
        if (is_null($sFechaInicio) || is_null($sFechaFinal)) return false;
        if ($sFechaFinal < $sFechaInicio) {
            $t = $sFechaFinal; $sFechaFinal = $sFechaInicio; $sFechaInicio = $t;
        }

        // Verificar presencia de asunto y descripción
        if (!isset($subject) || trim($subject) == '') {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid subject';
            $this->errMsg["fd"] = 'Subject must be specified and nonempty';
            $this->errMsg["cn"] = get_class($this);
            return false;
        }
        if (!isset($description) || trim($description) == '') {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid description';
            $this->errMsg["fd"] = 'Description must be specified and nonempty';
            $this->errMsg["cn"] = get_class($this);
            return false;
        }

        // Validaciones dependientes de asterisk_call
        if (!isset($asterisk_call) || 
            ($asterisk_call !== TRUE && $asterisk_call !== FALSE)) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid reminder flag';
            $this->errMsg["fd"] = 'Reminder flag must be specified and be a boolean';
            $this->errMsg["cn"] = get_class($this);
            return false;
        }
        $listaAudios = array();
        $sExtUsuario = $this->_leerExtension();
        if (!$asterisk_call) {
            // Ninguno de estos valores se usa cuando no hay llamada
            $recording = NULL;
            $call_to = NULL;
            $reminder_timer = NULL;
        } else {
            // Si reminder_timer no es entero positivo, se asume 0
            if (!isset($reminder_timer) || !preg_match('/^\d+$/', $reminder_timer))
                $reminder_timer = 0;

            // Número a marcar debería ser cadena numérica
            if (!isset($call_to)) {
                $call_to = $sExtUsuario;
            }
            if (!preg_match('/^\d+$/', $call_to)) {
                $this->errMsg["fc"] = 'PARAMERROR';
                $this->errMsg["fm"] = 'Invalid reminder dialout number';
                $this->errMsg["fd"] = 'Reminder dialout number (call_to) must be specified and be a numeric string';
                $this->errMsg["cn"] = get_class($this);
                return false;
            }

            // Verificar que la grabación se ha especificado y existe
            $listaAudios = $this->_listarAudiosExtension($sExtUsuario);
            if (!is_array($listaAudios)) return NULL;
            if (!isset($listaAudios[$recording])) {
                $this->errMsg["fc"] = 'PARAMERROR';
                $this->errMsg["fm"] = 'Invalid recording';
                $this->errMsg["fd"] = 'Specified recording not found.';
                $this->errMsg["cn"] = get_class($this);
                return false;
            }
        }

        if (!isset($emails_notification))
            $emails_notification = array();
        if (!is_array($emails_notification))
            $emails_notification = array($emails_notification);

        /* Construir cadena de correos en 1era forma ANORMAL. Se requiere la 
         * coma al final porque la interfaz web se come el último elemento al
         * mostrar la lista de correos. Además, los nombres descriptivos deben
         * estar encerrados en comillas dobles, y los correos tienen que estar
         * encerrados entre mayor y menor que. Todo esto se requiere para que
         * la interfaz funcione correctamente al mostrar los correos al editar. 
         */
        if (count($emails_notification) == 0) {
            $sCadenaCorreo = '';
        } else {
            function canonicalizar_correo($x)
            {
                $regs = NULL;
                if (preg_match('/^\s*"?([^"]*)"?\s*<(\S*)>\s*$/', $x, $regs)) {
                    $sNombre = '';
                    if (trim($regs[1]) != '') $sNombre = "\"{$regs[1]}\" ";
                    return "$sNombre<{$regs[2]}>";
                } else {
                    return "<$x>";
                }
            }
            $sCadenaCorreo = implode(', ', array_map('canonicalizar_correo', $emails_notification)).',';
        }

        $color = isset($color)? $color : "#3366CC";
        /* Insertar el registro del nuevo evento. */
	$dbCalendar = $this->_getDB($arrConf['dsn_conn_database']);
        $pCalendar = new paloSantoCalendar($dbCalendar);
        $r = $pCalendar->insertEvent(
            $id_user,
            substr($sFechaInicio, 0, 10),   // asume yyyy-mm-dd al inicio
            substr($sFechaFinal, 0, 10),    // asume yyyy-mm-dd al inicio
            substr($sFechaInicio, 0, 16),   // asume yyyy-mm-dd hh:mm al inicio
            1,                              // 1 es evento de una sola vez
            $subject,
            $description,
            $asterisk_call ? 'on' : 'off',
            $asterisk_call ? $recording : '',
            $asterisk_call ? $call_to : '',
            (count($emails_notification) > 0) ? 'on' : 'off',
            $sCadenaCorreo,   // 1era forma ANORMAL
            substr($sFechaFinal, 0, 16),    // asume yyyy-mm-dd hh:mm al inicio,
            1,                              // 1 es audio de una sola vez
            substr(date("D", strtotime("2010-11-04 12:54:00")), 0, 2).",", // Primeras 2 letras de día semana, con coma
            $asterisk_call ? $reminder_timer : '',
            $color);
        if (!$r) {
            $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to create event in calendar - '.$pCalendar->errMsg;
            $this->errMsg["cn"] = get_class($pCalendar);
            return false;
        }
        $idEvento = $pCalendar->_DB->getLastInsertId();

        // Crear el archivo de llamada, en caso necesario
        if ($asterisk_call) {
            $this->_crearArchivoLlamadaAsterisk($idEvento, $sExtUsuario, 
                $call_to, $listaAudios[$recording], 2, 
                strtotime($sFechaInicio.(($reminder_timer > 0) ? " - {$reminder_timer} second" : '')));
        }

        // Enviar los correos de notificación, en caso necesario
        if (count($emails_notification) > 0) {
            $this->_enviarCorreosNotificacionEvento($idEvento, $sFechaInicio, $sFechaFinal, $subject, $emails_notification, $description, 'New_Event');
        }
        if($getIdInserted)
	    return $dbCalendar->getLastInsertId();
	else
	    return true;
    }

    /**
     * Functional point that deletes an existing event in the calendar of the authenticated user
     *
     * @param   integer      $id        ID of the event to be deleted
     * @return  boolean      True if the event was successfully deleted, or false if an error exists
     */
    function delCalendarEvent($id)
    {
        global $arrConf;

        if (!$this->_checkUserAuthorized('calendar')) return false;

        // Identificar el usuario para averiguar el ID de usuario en calendario
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;

        // Verificar presencia de ID del evento
        if (!isset($id) || !preg_match('/^\d+$/', $id)) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid ID';
            $this->errMsg["fd"] = 'Event ID must be nonnegative integer';
            $this->errMsg["cn"] = get_class($this);
            return false;
        }
        $id = (int)$id;

        // Leer los datos del evento del usuario
        $pCalendar = new paloSantoCalendar($this->_getDB($arrConf['dsn_conn_database']));
        $infoEvento = $pCalendar->getEventById($id, $id_user);
        if (!is_array($infoEvento) && $pCalendar->errMsg != '') {
            $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to read event in calendar - '.$pCalendar->errMsg;
            $this->errMsg["cn"] = get_class($pCalendar);
            return false;
        }
        if (count($infoEvento) <= 0 || $infoEvento['uid'] != $id_user) {
            $this->errMsg["fc"] = 'CALENDAR';
            $this->errMsg["fm"] = 'Event lookup failed';
            $this->errMsg["fd"] = 'No event was found for user '.$_SERVER['PHP_AUTH_USER'];
            $this->errMsg["cn"] = get_class($pCalendar);
            return false;
        }

        // Borrar los archivos de audio para el ID indicado
        array_map(
            'unlink',
            glob($arrConf['output_callfile_base']."/event_{$id}_*.call"));

        // Si había notificación de correo, se envía mensaje a lista de usuarios
        if ($infoEvento['emails_notification'] != '') {
            $r = (object)$infoEvento; // subject description emails_notification
            $r->emails_notification = array_filter(
                preg_split('/[\s,]+/', $infoEvento['emails_notification']), 
                array($this, '_rechazar_correo_vacio')); 
            $this->_enviarCorreosNotificacionEvento($id, 
                $infoEvento['starttime'].':00', $infoEvento['endtime'].':00', 
                $r->subject,$r->emails_notification,$r->description, 'Delete_Event');
        }

        // Borrar el evento
        if (!$pCalendar->deleteEvent($id, $id_user)) {
            $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to delete event in calendar - '.$pCalendar->errMsg;
            $this->errMsg["cn"] = get_class($pCalendar);
            return false;
        }
        return true;
    }

    /**
     * This function creates a queue for the differential sync
     *
     * @param   string   $data      String containing the JSON data to be sync   
     *
     * @return  mixed    returns the ticket of the queue, or false if an error exists
     */
    public function eventDifferentialSync($data)
    {
        global $arrConf;

        if (!$this->_checkUserAuthorized('calendar')) return false;

        $dbCalendar = $this->_getDB($arrConf['dsn_conn_database']);
        $pCalendar = new paloSantoCalendar($dbCalendar);

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;
       
        $result = $pCalendar->addQueue($data,"event",$id_user);
        if (!$result) {
            $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to write data - '.$pCalendar->_DB->errMsg;
            $this->errMsg["cn"] = get_class($pCalendar);
            return false;
        }
	else
	    return $result;
    }

    /**
     * This function gets the status of a queue and returns the data to be sync in the client.
     * Uses the long poll method
     *
     * @param   string   $ticket     Ticket of the queue   
     *
     * @return  mixed    returns an array with the data to be sync, or an array with an informative
     *                   message if the timeout has been reached and the queue is still unsolved,
     *			 or false if an error exists
     */
    public function getStatusQueue($ticket)
    {
        if (!$this->_checkUserAuthorized('calendar')) return false;

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;

	$this->_ticket = $ticket;

	// Se llama al método definido en la clase LongPoll. Para establecer una conexión permanente con el cliente
	$data = $this->run();
	if(is_null($data)){
	    $result["status"] = "The ticket is still in the queue";
	    return $result;
	}
	elseif($data === false)
	    return false;
	else
	    return $data;
    }

    /**
     * This function gets all the events of the authenticated user in the server
     *
     * @return  mixed    returns an array with all the events, or false if an error exists
     */
    public function getFullSync()
    {
	global $arrConf;

        if (!$this->_checkUserAuthorized('calendar')) return false;

        $dbCalendar = $this->_getDB($arrConf['dsn_conn_database']);
        $pCalendar = new paloSantoCalendar($dbCalendar);

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;

	$events = $pCalendar->getUserEvents($id_user);
	if($events === FALSE){
	    $this->errMsg["fc"] = 'DBERROR';
	    $this->errMsg["fm"] = 'Database operation failed';
	    $this->errMsg["fd"] = 'Unable to get data - '.$pCalendar->_DB->errMsg;
	    $this->errMsg["cn"] = get_class($pCalendar);
	    return false;
	}
	else{
	    $result["last_sync"] = time();
	    $result["events"] = $events;
	    return $result;
	}
    }

    /**
     * This function gets the md5 hash for the data verification integrity of all the events
     *
     * @param   string   $fields      String containing the JSON of the fields to be verified    
     *
     * @return  mixed    returns an array with the hash, or false if an error exists
     */
    public function getHash($fields)
    {
	global $arrConf;

        if (!$this->_checkUserAuthorized('calendar')) return false;

        $dbCalendar = $this->_getDB($arrConf['dsn_conn_database']);
        $pCalendar = new paloSantoCalendar($dbCalendar);

        // Obtener el ID del usuario logoneado
        $id_user = $this->_leerIdUser();
        if (is_null($id_user)) return false;

	$json = new Services_JSON();
	$fields = $json->decode($fields);

	if(is_array($fields)){
	    //Se eliminan valores repetidos
	    $fields = array_unique($fields);
	    $key = array_search("id",$fields); // Se elimina el campo id en caso de que lo envie el cliente
	    if($key !== FALSE)
		unset($fields[$key]);
	}

	if(!is_array($fields) || count($fields) == 0){
	    $this->errMsg["fc"] = 'PARAMERROR';
	    $this->errMsg["fm"] = 'Wrong parameter';
	    $this->errMsg["fd"] = "The parameter \"fields\" must be an array json serialized and must contain at least one value different than \"id\".";
	    $this->errMsg["cn"] = get_class($this);
	    return false;
	}

	//TODO: Este arreglo contiene los campos de la tabla "events", quiza se deba buscar una manera más eficiente de protegerse contra inyección de sql
	$arrFields = array("id","uid","startdate","enddate","starttime","eventtype","subject","description","asterisk_call","recording","call_to","notification","emails_notification","endtime","each_repeat","days_repeat","reminderTimer","color","last_update");
	$counter = 1;
	$queryFields = "id,";
	foreach($fields as $value){
	    if(!in_array($value,$arrFields)){
		$result["error"] = "Some field/s do not exist in the server";
		return $result;
	    }
	    if($counter == count($fields))
		$queryFields .= $value;
	    else
		$queryFields .= $value.",";
	    $counter++;
	}
	$result = $pCalendar->getUserEvents($id_user,$queryFields);
	if($result === FALSE){
	    $this->errMsg["fc"] = 'DBERROR';
	    $this->errMsg["fm"] = 'Database operation failed';
	    $this->errMsg["fd"] = 'Unable to get data - '.$pCalendar->_DB->errMsg;
	    $this->errMsg["cn"] = get_class($pCalendar);
	    return false;
	}
	$contacts_json = $json->encode($result);
	$hash = md5($contacts_json);
	$response["hash"] = $hash;
	return $response;
    }

    /**
     * This function query the status of the ticket of a queue, if it is unsolved returns NULL,
     * but if it is solved it will get the data to be sync in the client. 
     *
     * @return  mixed    returns an array with the data to be sync in the client, or NULL it the ticket
     *			 is unsolved or false if an error exists
     */
    protected function getData()
    {
	global $arrConf;

	$dbCalendar = $this->_getDB($arrConf['dsn_conn_database']);
        $pCalendar = new paloSantoCalendar($dbCalendar);

	$data_ticket = $pCalendar->getDataTicket($this->_ticket,$this->_id_user);
	if(is_null($data_ticket)){
	    $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to write data - '.$pCalendar->_DB->errMsg;
            $this->errMsg["cn"] = get_class($pCalendar);
            return false;
	}
	elseif(!$data_ticket){
	    $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Wrong ticket';
            $this->errMsg["fd"] = "The ticket {$this->_ticket} does not exist or does not belong to you";
            $this->errMsg["cn"] = get_class($pCalendar);
            return false;
	}
	else{
	    if($data_ticket["status"] != "OK")
		return null;
	    else{
		$result["status"] = "OK";
		$json = new Services_JSON();
		$data = $json->decode($data_ticket["data"]);
		if(!isset($data->last_sync) || !isset($data->events)){
		    $remove_queue = $pCalendar->removeQueue($this->_ticket);
		    if($remove_queue === false){
			$this->errMsg["fc"] = 'DBERROR';
			$this->errMsg["fm"] = 'Database operation failed';
			$this->errMsg["fd"] = 'Unable to delete data - '.$pCalendar->_DB->errMsg;
			$this->errMsg["cn"] = get_class($pCalendar);
			return false;
		    }
		    $this->errMsg["fc"] = 'PARAMERROR';
		    $this->errMsg["fm"] = 'Wrong data';
		    $this->errMsg["fd"] = "The data of the ticket {$this->_ticket} is wrong or corrupted. This data has to be a JSON string containing the keywords \"last_sync\" and \"contacts\". The ticket will be deleted";
		    $this->errMsg["cn"] = get_class($pCalendar);
		    return false;
		}
		if(!is_array($data->events)){
		    $remove_queue = $pCalendar->removeQueue($this->_ticket);
		    if($remove_queue === false){
			$this->errMsg["fc"] = 'DBERROR';
			$this->errMsg["fm"] = 'Database operation failed';
			$this->errMsg["fd"] = 'Unable to delete data - '.$pCalendar->_DB->errMsg;
			$this->errMsg["cn"] = get_class($pCalendar);
			return false;
		    }
		    $this->errMsg["fc"] = 'PARAMERROR';
		    $this->errMsg["fm"] = 'Wrong data';
		    $this->errMsg["fd"] = "The data of the contacts in ticket {$this->_ticket} is wrong or corrupted. It has to be an array. The ticket will be deleted";
		    $this->errMsg["cn"] = get_class($pCalendar);
		    return false;
		}
		$last_sync = $data->last_sync;
		if(isset($data_ticket["response_data"]) && !empty($data_ticket["response_data"]))
		    $response_data = $json->decode($data_ticket["response_data"]);
		else
		    $response_data = array();
		$events = $pCalendar->getEventsAfterSync($last_sync,$data->events,$this->_id_user,$response_data);
		if($events === false){
		    $this->errMsg["fc"] = 'DBERROR';
		    $this->errMsg["fm"] = 'Database operation failed';
		    $this->errMsg["fd"] = 'Unable to get data - '.$pCalendar->_DB->errMsg;
		    $this->errMsg["cn"] = get_class($pCalendar);
		    return false;
		}
		else{
		    $remove_queue = $pCalendar->removeQueue($this->_ticket);
		    if($remove_queue === false){
			$this->errMsg["fc"] = 'DBERROR';
			$this->errMsg["fm"] = 'Database operation failed';
			$this->errMsg["fd"] = 'Unable to delete data - '.$pCalendar->_DB->errMsg;
			$this->errMsg["cn"] = get_class($pCalendar);
			return false;
		    }
		    else{
			$result["last_sync"] = time();
			$result["events"] = $events;
			return $result;
		    }
		}
	    }
	}
    }

    /**
     * 
     * Function that returns the error message
     *
     * @return  string   Message error if had an error.
     */
    public function getError()
    {
        return $this->errMsg;
    }
}
?>
