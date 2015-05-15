<?php

$root = $_SERVER["DOCUMENT_ROOT"];
require_once("$root/libs/SOAPhandler.class.php");
require_once("$root/modules/calendar/scenarios/SOAP_Calendar.class.php");

$SOAPhandler = new SOAPhandler("SOAP_Calendar");

if($SOAPhandler->exportWSDL()){
    if($SOAPhandler->authentication())
        $SOAPhandler->execute();
}

$error = $SOAPhandler->getError();
if($error) echo $error;
?>