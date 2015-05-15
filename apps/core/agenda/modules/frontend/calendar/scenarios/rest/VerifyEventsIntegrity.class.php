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
  $Id: VerifyEventsIntegrity.class.php,v 1.1 2012/05/30 23:49:36 Alberto Santos Exp $
*/

$documentRoot = $_SERVER["DOCUMENT_ROOT"];
require_once "$documentRoot/libs/REST_Resource.class.php";
require_once "$documentRoot/libs/paloSantoJSON.class.php";
require_once "$documentRoot/modules/calendar/libs/core.class.php";

/*
 * Para esta implementación de REST, se tienen los siguientes URI
 * 
 *  /VerifyEventsIntegrity            application/json
 *      GET     devuelve un hash md5 de la serialización en json del arreglo de eventos en el servidor
 *              pero sólo con los campos especificados por el cliente por GET
 *
 */

class VerifyEventsIntegrity
{
    private $resourcePath;
    function __construct($resourcePath)
    {
	$this->resourcePath = $resourcePath;
    }

    function URIObject()
    {
	$uriObject = new VerifyEventsIntegrityBase();
	if(count($this->resourcePath) > 0)
	    return NULL;
	else
	    return $uriObject;
    }
}

class VerifyEventsIntegrityBase extends REST_Resource
{
    function HTTP_GET()
    {
    	$json = new paloSantoJSON();
	$pCore_calendar = new core_Calendar();
	$fields = isset($_GET["fields"]) ? $_GET["fields"] : NULL;
	if(is_null($fields)){
	    header("HTTP/1.1 400 Bad Request");
	    $error = "You need to specify by GET the parameter \"fields\"";
            $json->set_status("ERROR");
            $json->set_error($error);
            return $json->createJSON();
	}
	$result = $pCore_calendar->getHash($fields);
	if($result === FALSE){
	    $error = $pCore_calendar->getError();
            if ($error["fc"] == "DBERROR")
                header("HTTP/1.1 500 Internal Server Error");
            else
                header("HTTP/1.1 400 Bad Request");
            $json->set_status("ERROR");
            $json->set_error($error);
            return $json->createJSON();
	}
	else{
	    $json = new Services_JSON();
	    return $json->encode($result);
	}
    }
}

?>