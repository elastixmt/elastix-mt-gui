<?php  
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version {ELASTIX_VERSION}                                    |
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
*/

class paloSIPAccount
{
    // Todos los campos disponibles
    protected static $allfields = array('id','name','context','callingpres',
        'deny','permit','acl','secret','md5secret','remotesecret','transport',
        'host','nat','type','accountcode','amaflags','callgroup','pickupgroup',
        'namedcallgroup','namedpickupgroup','callerid','directmedia',
        'directmediapermit','directmediaacl','description','defaultip',
        'dtmfmode','fromuser','fromdomain','insecure','language','tonezone',
        'mailbox','qualify','regexten','rtptimeout','rtpholdtimeout','setvar',
        'disallow','allow','fullcontact','ipaddr','port','username','defaultuser',
        'dial','trustrpid','sendrpid','progressinband','promiscredir',
        'useclientcode','callcounter','busylevel','allowoverlap','allowsubscribe',
        'allowtransfer','lastms','useragent','regseconds','regserver','videosupport',
        'maxcallbitrate','rfc2833compensate','session-timers','session-expires',
        'session-minse','session-refresher','outboundproxy','callbackextension',
        'timert1','timerb','qualifyfreq','constantssrc','contactpermit',
        'contactdeny','contactacl','usereqphone','textsupport','faxdetect',
        'buggymwi','auth','fullname','trunkname','cid_number','mohinterpret',
        'mohsuggest','parkinglot','hasvoicemail','subscribemwi','vmexten',
        'rtpkeepalive','g726nonstandard','ignoresdpversion','subscribecontext',
        'template','keepalive','t38pt_usertpsource','organization_domain',
        'outofcall_message_context');

    // Los siguientes campos deben ser distintos de NULL
    protected static $requiredfields = array('id','name','username','lastms',
        'regseconds','regserver','organization_domain','kamailioname');

    // Los siguientes campos, si se asignan, deben ser enteros
    protected static $intfields = array('id','rtptimeout','rtpholdtimeout','port',
        'busylevel','lastms','regseconds','maxcallbitrate','session-expires',
        'session-minse','timert1','timerb','qualifyfreq','rtpkeepalive');

    // Los siguientes campos, si se asignan, deben ser booleanos (yes/no)
    protected static $boolfields = array('trustrpid','promiscredir','useclientcode',
        'callcounter','allowoverlap','allowsubscribe','allowtransfer','videosupport',
        'rfc2833compensate','constantssrc','usereqphone','textsupport','faxdetect',
        'buggymwi','hasvoicemail','subscribemwi','g726nonstandard','ignoresdpversion',
        't38pt_usertpsource');

    // Los siguientes campos, si se asignan, sólo pueden tomar los siguientes valores:
    protected static $enumfields = array(
        'callingpres' => array('allowed_not_screened','allowed_passed_screen',
            'allowed_failed_screen','allowed','prohib_not_screened',
            'prohib_passed_screen','prohib_failed_screen','prohib'),
        'type' => array('friend','user','peer'),
        'directmedia' => array('yes','no','nonat','update','outgoing','update,nonat'),
        'dtmfmode' => array('rfc2833','info','shortinfo','inband','auto'),
        'sendrpid' => array('yes','no','pai','yes,pai'),
        'progressinband' => array('yes','no','never'),
        'session-timers' => array('accept','refuse','originate'),
        'session-refresher' => array('uac','uas'),
    );
	
    // Los siguientes campos se mapean y se deja el campo original como NULL
    protected static $mapsql = array(
        'secret'    =>  'sippasswd',
    );
    
    // Los siguientes campos se mapean como ORGDOMAIN-valor en la base de datos
    protected static $contextval = array('context', 'subscribecontext', 'outofcall_message_context');
    
    // Los siguientes campos se mapean como ORGDOMAIN_valor en la base de datos
    protected static $groupval = array('namedpickupgroup', 'namedcallgroup');
    
    
    private $_DB;
    public $errMsg;

    function __construct($pDB)
    {
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
}
?>