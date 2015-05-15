<?php
/*
  vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2003 Palosanto Solutions S. A.                    |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  +----------------------------------------------------------------------+
  | Este archivo fuente está sujeto a las políticas de licenciamiento    |
  | de Palosanto Solutions S. A. y no está disponible públicamente.      |
  | El acceso a este documento está restringido según lo estipulado      |
  | en los acuerdos de confidencialidad los cuales son parte de las      |
  | políticas internas de Palosanto Solutions S. A.                      |
  | Si Ud. está viendo este archivo y no tiene autorización explícita    |
  | de hacerlo, comuníquese con nosotros, podría estar infringiendo      |
  | la ley sin saberlo.                                                  |
  +----------------------------------------------------------------------+
  | Autores: Alberto Santos Flores <asantos@palosanto.com>               |
  +----------------------------------------------------------------------+
  $Id: CalendarEvent.class.php,v 1.1 2012/02/07 23:49:36 Alberto Santos Exp $
*/

$documentRoot = $_SERVER["DOCUMENT_ROOT"];
require_once "$documentRoot/libs/REST_Resource.class.php";
require_once "$documentRoot/libs/paloSantoJSON.class.php";
require_once "$documentRoot/modules/calendar/libs/core.class.php";


class CalendarEvent
{
    private $resourcePath;
    function __construct($resourcePath)
    {
	$this->resourcePath = $resourcePath;
    }

    function URIObject()
    {
	$uriObject = NULL;
	if (count($this->resourcePath) <= 0)
	    $uriObject = new CalendarEventBase();
	else
	    $uriObject = new CalendarEventById(array_shift($this->resourcePath));
	if(count($this->resourcePath) > 0)
	    return NULL;
	else
	    return $uriObject;
    }
}

class CalendarEventBase extends REST_Resource
{
    function HTTP_GET()
    {
	$pCore_Calendar = new core_Calendar();
        $json = new paloSantoJSON();

        $startdate = isset($_GET["startdate"]) ? $_GET["startdate"] : NULL;
        $enddate = isset($_GET["enddate"]) ? $_GET["enddate"] : NULL;
        $result = $pCore_Calendar->listCalendarEvents($startdate,$enddate);
        if (!is_array($result)) {
            $error = $pCore_Calendar->getError();
            if ($error["fc"] == "DBERROR")
                header("HTTP/1.1 500 Internal Server Error");
            else
                header("HTTP/1.1 400 Bad Request");
            $json->set_status("ERROR");
            $json->set_error($error);
            return $json->createJSON();
        }
        
        $sBaseUrl = '/rest.php/calendar/CalendarEvent';
        foreach (array_keys($result['events']) as $k)
            $result['events'][$k]['url'] = $sBaseUrl.'/'.$result['events'][$k]['id'];
        $json = new Services_JSON();
        return $json->encode($result);
    }

    function HTTP_POST()
    {
	$json = new paloSantoJSON();
    	if (!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] != 'application/x-www-form-urlencoded') {
            header('HTTP/1.1 415 Unsupported Media Type');
            $json->set_status("ERROR");
            $json->set_error('Please POST standard URL encoding only');
            return $json->createJSON();
    	}

        $pCore_Calendar      = new core_Calendar();
        $startdate 	     = (isset($_POST["startdate"])) 	      ? $_POST["startdate"] 	      : NULL;
        $enddate 	     = (isset($_POST["enddate"])) 	      ? $_POST["enddate"] 	      : NULL;
        $subject 	     = (isset($_POST["subject"])) 	      ? $_POST["subject"] 	      : NULL;
        $description 	     = (isset($_POST["description"])) 	      ? $_POST["description"] 	      : NULL;
	$asterisk_call 	     = (isset($_POST["asterisk_call"]))       ? $_POST["asterisk_call"]       : NULL;
	$recording 	     = (isset($_POST["recording"])) 	      ? $_POST["description"] 	      : NULL;
	$call_to 	     = (isset($_POST["call_to"])) 	      ? $_POST["call_to"] 	      : NULL;
	$reminder_timer      = (isset($_POST["reminder_timer"]))      ? $_POST["reminder_timer"]      : NULL;
	$emails_notification = (isset($_POST["emails_notification"])) ? $_POST["emails_notification"] : NULL;
	$color		     = (isset($_POST["color"]))		      ? $_POST["color"]		      : NULL;
        
        $result = $pCore_Calendar->addCalendarEvent($startdate,$enddate,$subject,$description,$asterisk_call,$recording,$call_to,$reminder_timer,$emails_notification, $color, TRUE);
        if ($result !== FALSE) {
            Header('HTTP/1.1 201 Created');
            Header('Location: /rest.php/calendar/CalendarEvent/'.$result);
        } else {
            $error = $pCore_Calendar->getError();
            if ($error["fc"] == "DBERROR")
                header("HTTP/1.1 500 Internal Server Error");
            else
                header("HTTP/1.1 400 Bad Request");
            $json->set_status("ERROR");
            $json->set_error($error);
            return $json->createJSON();
        }
    }
}

class CalendarEventById extends REST_Resource
{
    protected $_idNumero;
    
    function __construct($sIdNumero)
    {
	$this->_idNumero = $sIdNumero;
    }

    function HTTP_GET()
    {
	$pCore_Calendar = new core_Calendar();
	$json = new paloSantoJSON();
	
	$result = $pCore_Calendar->listCalendarEvents(NULL,NULL, $this->_idNumero);
	if (!is_array($result)) {
            $error = $pCore_Calendar->getError();
            if ($error["fc"] == "DBERROR")
                header("HTTP/1.1 500 Internal Server Error");
            else
                header("HTTP/1.1 400 Bad Request");
            $json->set_status("ERROR");
            $json->set_error($error);
            return $json->createJSON();
        }
        if (count($result['events']) <= 0) {
        	header("HTTP/1.1 404 Not Found");
            $json->set_status("ERROR");
            $json->set_error('No event was found');
            return $json->createJSON();
        }
        
        $tupla = $result['events'][0];
        $tupla['url'] = '/rest.php/calendar/CalendarEvent/'.$this->_idNumero;
        $json = new Services_JSON();
        return $json->encode($tupla);
    }

/* TODO: Implementar en core.class.php para poder modificar un evento
    function HTTP_PUT()
    {
	$json = new paloSantoJSON();
        if (!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] != 'application/x-www-form-urlencoded') {
            header('HTTP/1.1 415 Unsupported Media Type');
            $json->set_status("ERROR");
            $json->set_error('Please POST standard URL encoding only');
            return $json->createJSON();
        }

	$pCore_Calendar = new core_Calendar();
	$putvars = NULL;
        parse_str(file_get_contents('php://input'), $putvars);
    }
*/

    function HTTP_DELETE()
    {
	$pCore_Calendar = new core_Calendar();
	$json = new paloSantoJSON();
	$result = $pCore_Calendar->delCalendarEvent($this->_idNumero);
	if ($result === FALSE) {
            $error = $pCore_Calendar->getError();
            if($error["fc"] == "DBERROR")
                header("HTTP/1.1 500 Internal Server Error");
            elseif ($error['fc'] == 'ADDRESSBOOK')
                header("HTTP/1.1 404 Not Found");
            else
                header("HTTP/1.1 400 Bad Request");
            $json->set_status("ERROR");
            $json->set_error($error);
            return $json->createJSON();
        }
	else{
	    $json = new Services_JSON();
	    $response["message"] = "The event was successfully deleted";
	    return $json->encode($response);
	}
    }
}