<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-31                                               |
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
  $Id: paloSantoRegistration.class.php,v 1.1 2011-02-25 10:08:51 Eduardo Cueva ecueva@palosanto.com Exp $ */

class paloSantoRegistration {
    var $_DB;
    var $errMsg;

    function paloSantoRegistration(&$pDB)
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

    function getDataRegister()
    {
        $query = "SELECT 
                        id                   AS id,
                        contact_name         AS contactNameReg,
                        email                AS emailReg,
                        phone                AS phoneReg,
                        company              AS companyReg,
                        address              AS addressReg,
                        city                 AS cityReg,
                        country              AS countryReg,
                        idPartner            AS idPartnerReg
                    FROM 
                        register";
        $result=$this->_DB->getFirstRowQuery($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    private function _getSOAP()
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        $url_webservices = "https://webservice.elastix.org/modules/installations/webservice/registerWSDL.wsdl";
    	
        /* La presencia de xdebug activo interfiere con las excepciones de
         * SOAP arrojadas por SoapClient, convirtiéndolas en errores 
         * fatales. Por lo tanto se desactiva la extensión. */
        if (function_exists("xdebug_disable")) xdebug_disable(); 
        
        return @new SoapClient($url_webservices);
    }

    function getDataServerRegistration()
    {
		if(is_file("/etc/elastix.key"))
	    	$serverKey = file_get_contents("/etc/elastix.key");
		else
	    	$serverKey = "";
	    	
	    try {
            $client = $this->_getSOAP();
        	$content = $client->getDataServerRegistration($serverKey);
        	return $content;
	    } catch(SoapFault $e) {
        	return null;
        }
    }

    function insertDataRegister($data)
    {
        $query = "INSERT INTO register(contact_name, email, phone, company, address, city, country, idPartner) VALUES(?,?,?,?,?,?,?,?)";
        $result = $this->_DB->genQuery($query, $data);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        return TRUE;
    }


    function updateDataRegister($data)
    {
        $query = "UPDATE register SET contact_name=?, email=?, phone=?, company=?, address=?, city=?, country=?, idPartner=? WHERE id=?";
        $result = $this->_DB->genQuery($query, $data);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        return TRUE;
    }

    function createTableRegister()
    {
	$query = "CREATE TABLE register(
	id 		integer 	primary key,
	contact_name 	varchar(25),
	email 		varchar(25),
	phone 		varchar(20),
	company 	varchar(25),
	address 	varchar(100),
	city 		varchar(25),
	country 	varchar(25),
	idPartner	varchar(25)
)";
	return $this->_DB->genExec($query);
    }

    function tableRegisterExists()
    {  
	$query = "SELECT * FROM register";
	$result = $this->_DB->genQuery($query);
	if($result === false){
	    if(preg_match("/No such table/i",$this->_DB->errMsg))
		return false;
	    else
		return true;
	}
	else
	  return true;
    }

    function sendDataWebService($data)
    {
        try {
            $client = $this->_getSOAP();
           	$content = $client->saveInstallation($data);
        	return $content;
        } catch(SoapFault $e) {
        	return null;
        }
    }
}
?>
