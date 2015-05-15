<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.6-6                                               |
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
  $Id: paloSantoEmailRelay.class.php,v 1.1 2010-07-21 01:08:56 Bruno Macias bmacias@palosanto.com Exp $ */
class paloSantoEmailRelay {
    var $_DB;
    var $errMsg;

    function paloSantoEmailRelay(&$pDB)
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

    function getMainConfigByAll()
    {
        $query  = "SELECT name, value FROM email_relay ";
        $result = $this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }

        $arrData = null;
        if(is_array($result) && count($result)>0){
            foreach($result as $k => $data)
                $arrData[$data['name']] = $data['value'];
        }
        return $arrData;
    }

    /**
     * Método para actualizar la configuración de SMTP remoto.
     * 
     * @param   array   $arrData    Arreglo con los parámetros de configuración:
     *  status          'on' para activar SMTP remoto, 'off' para desactivar
     *  relayhost       nombre de host del SMTP remoto
     *  port            puerto TCP a contactar en SMTP remoto
     *  user            nombre de usuario para autenticación
     *  password        contraseña para autenticación
     *  autentification 'on' para activar TLS, 'off' para desactivar
     * 
     * @return  bool    VERDADERO en éxito, FALSO en error
     */
    function processUpdateConfiguration($arrData)
    {
    	$this->errMsg = '';
        $output = $retval = NULL;
        $sComando = '/usr/bin/elastix-helper remotesmtp'.
            ' --relay '.escapeshellarg($arrData['relayhost']).
            ' --port '.escapeshellarg($arrData['port']).
            ' --user '.(empty($arrData['user']) ? "''" : escapeshellarg($arrData['user'])).
            ' --pass '.(empty($arrData['password']) ? "''" : escapeshellarg($arrData['password']));
        if ($arrData['status'] == 'on') $sComando .= ' --enableremote';
        if ($arrData['autentification'] == 'on') $sComando .= ' --tls';
        $sComando .= ' 2>&1';
        exec($sComando, $output, $retval);
        if ($retval != 0) {
            foreach ($output as $s) {
                $regs = NULL;
                if (preg_match('/^ERR: (.+)$/', trim($s), $regs)) {
                    $this->errMsg = $regs[1];
                }
            }
        	return FALSE;
        }
        return TRUE;
    }

    function setStatus($status)
    {
        // Existe name status
        $query  = "select count(*) existe from email_relay where name='status';";
        $result = $this->_DB->getFirstRowQuery($query,true);

        if(is_array($result) && count($result) >0){
            $query = ($result['existe'] >= 1)
                ? "update email_relay set value=? where name='status'"
                : "insert into email_relay(name,value) values('status', ?)";
            $ok = $this->_DB->genQuery($query, array($status));

            if(!$ok){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }
            return true;
        }
        else{
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
    }

    function getStatus()
    {
        // Existe name status
        $query  = "select value from email_relay where name='status';";
        $result = $this->_DB->getFirstRowQuery($query,true);

        if(is_array($result) && count($result) >0)
            return $result['value'];
        else return 0;
    }

    function checkSMTP($smtp_server, $smtp_port=25, $username, $password, $auth_enabled=false, $tls_enabled=true)
    {
        require_once("libs/phpmailer/class.smtp.php");

        $smtp = new SMTP();
        $smtp->Connect($smtp_server,$smtp_port);

        if(!$smtp->Connected()){
            return array("ERROR" => "Failed to connect to server", "SMTP_ERROR" => $smtp->getError());
        }

        if(!$smtp->Hello()){
            return array("ERROR" => "Failed to send hello command", "SMTP_ERROR" => $smtp->getError());
        }

        if($tls_enabled){
            if(!$smtp->StartTLS())
                return array("ERROR" => "Failed to start TLS", "SMTP_ERROR" => $smtp->getError());
        }

        if($auth_enabled){
            if(!$smtp->Authenticate($username,$password)){
                $error = $smtp->getError();
                if(preg_match("/STARTTLS/",$error['smtp_msg']))
                    return array("ERROR" => "Authenticate Error, TLS must be activated", "SMTP_ERROR" => $smtp->getError());
                else
                    return array("ERROR" => "Authenticate not accepted from server", "SMTP_ERROR" => $smtp->getError());
            }
        }

        return true;
    }
}
?>
