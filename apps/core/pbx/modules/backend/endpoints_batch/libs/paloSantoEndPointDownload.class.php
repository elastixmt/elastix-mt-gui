<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0                                                  |
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
  $Id: paloSantoEndPoint.class.php,v 1.1 2008/01/15 10:39:57 bmacias Exp $ */

require_once("libs/paloSantoDB.class.php");
class paloSantoEndPointDownload
{
    private $_DB;
    var $errMsg;

    function paloSantoEndPointDownload(&$pDB)
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
    
    function reportEndpointParameters()
    {
        // Datos principales del endpoint
    	$sqlEndpoints = <<<SQL_ENDPOINT_MAIN
SELECT endpoint.id, endpoint.id_device, endpoint.desc_device, endpoint.account,
    endpoint.secret, vendor.name AS vendor_name, model.name AS model_name,
    endpoint.mac_adress, endpoint.comment
FROM endpoint, vendor, model
WHERE endpoint.id_vendor = vendor.id AND endpoint.id_model = model.id
SQL_ENDPOINT_MAIN;
        $r = $this->_DB->fetchTable($sqlEndpoints, TRUE);
        if (!is_array($r)) {
            $this->errMsg = $this->_DB->errMsg;
            return NULL;
        }
        $endpointList = array();
        foreach ($r as $tupla) {
        	$endpointList[$tupla['id']] = array(
                'id_device'     =>  $tupla['id_device'],
                'desc_device'   =>  $tupla['desc_device'],
                'account'       =>  $tupla['account'],
                'secret'        =>  $tupla['secret'],
                'vendor_name'   =>  $tupla['vendor_name'],
                'model_name'    =>  $tupla['model_name'],
                'mac_adress'    =>  $tupla['mac_adress'],
                'comment'       =>  $tupla['comment'],
                'parameters'    =>  array(),
            );            
        }
        
        // Datos accesorios del endpoint
        $sqlParameters = 'SELECT id_endpoint, name, value FROM parameter';
        $r = $this->_DB->fetchTable($sqlParameters, TRUE);
        if (!is_array($r)) {
            $this->errMsg = $this->_DB->errMsg;
            return NULL;
        }
        foreach ($r as $tupla) {
        	if (isset($endpointList[$tupla['id_endpoint']]))
                $endpointList[$tupla['id_endpoint']]['parameters'][$tupla['name']] = $tupla['value'];
        }
        
        return $endpointList;
    }
}
?>