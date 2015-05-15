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
  $Id: SOAP_Calendar.class.php,v 1.0 2011-03-31 12:30:00 Alberto Santos F.  asantos@palosanto.com Exp $*/

$root = $_SERVER["DOCUMENT_ROOT"];
require_once("$root/modules/calendar/libs/core.class.php");

class SOAP_Calendar extends core_Calendar
{
    /**
     * SOAP Server Object
     *
     * @var object
     */
    private $objSOAPServer;

    /**
     * Constructor
     *
     * @param  object   $objSOAPServer     SOAP Server Object
     */
    public function SOAP_Calendar($objSOAPServer)
    {
         parent::core_Calendar();
         $this->objSOAPServer = $objSOAPServer;
    }

    /**
     * Static function that calls to the function getFP of its parent
     *
     * @return  array     Array with the definition of the function points.
     */
    public static function getFP()
    {
        return parent::getFP();
    }

    /**
     * Function that implements the SOAP call to see the events on the calendar of the registered user, by date range. If an
     * error exists a SOAP fault is thrown
     * 
     * @param mixed request:
     *                  startdate:  (date)  Starting date of event
     *                  enddate:    (date)  Ending date of event
     * @return  mixed   Array with the information of the calendar events
     */
    public function listCalendarEvents($request)
    {
        $return = parent::listCalendarEvents($request->startdate,$request->enddate);
        if(!$return){
            $eMSG = parent::getError();
            $this->objSOAPServer->fault($eMSG['fc'],$eMSG['fm'],$eMSG['cn'],$eMSG['fd'],'fault');
        }
        return $return;
    }

    /**
     * Function that implements the SOAP call to add a new one-time event in the calendar of the registered user. If an
     * error exists a SOAP fault is thrown
     * 
     * @param mixed request:
     *                  startdate:           (datetime) Starting date and time of event
     *                  enddate:             (datetime) Ending date and time of event
     *                  subject:             (string)   Subject of event
     *                  description:         (string) Long description of event
     *                  asterisk_call:       (bool) TRUE if must be generated reminder call
     *                  recording:           (string,optional) Name of the recording used to call. It is required if asterisk_call
     *                                                         is TRUE The file must exist in the recording directory for the
     *                                                         extension associated with the user.
     *                  call_to:             (string,optional) Extension to which call for Reminder. If omitted, assume the associated 
     *                                                         extension registered user. Not applicable unless asterisk_call is TRUE.
     *                  reminder_timer:      (string,optional) Number of minutes before which will make the call reminder. Applies if 
     *                                                         Asterisk_call is TRUE. By default it is assumed 0. Normal values ​​are 
     *                                                         10/30/60 minutes.
     *                  emails_notification: (array(string)) Zero or more emails will be notified with a message when creating the 
     *                                                       event.
     *                  color:               (string,optional) Color for the event
     * @return mixed    Array with boolean data, true if was successful or false if an error exists
     */
    public function addCalendarEvent($request)
    {
        $return = parent::addCalendarEvent($request->startdate,$request->enddate,$request->subject,$request->description,$request->asterisk_call,$request->recording,$request->call_to,$request->reminder_time,$request->emails_notification,$request->color);
        if(!$return){
            $eMSG = parent::getError();
            $this->objSOAPServer->fault($eMSG['fc'],$eMSG['fm'],$eMSG['cn'],$eMSG['fd'],'fault');
        }
        return array("return" => $return);
    }

    /**
     * Procedure that implements the SOAP call to remove an existing event calendar of the registered user. If an
     * error exists a SOAP fault is thrown
     * 
     * @param   mixed   $request:
     *                      id: ID of the event to remove
     * @return  mixed   Array with boolean data, true if was successful or false if an error exists
     */
    public function delCalendarEvent($request)
    {
        $return = parent::delCalendarEvent($request->id);
        if(!$return){
            $eMSG = parent::getError();
            $this->objSOAPServer->fault($eMSG['fc'],$eMSG['fm'],$eMSG['cn'],$eMSG['fd'],'fault');
        }
        return array("return" => $return);
    }
}
?>