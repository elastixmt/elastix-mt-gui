<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.2                                               |
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
  $Id: paloSantoPortKnockUsers.class.php,v 1.1 2010-12-13 03:09:33  Exp $ */

class paloSantoPortKnockUsers
{
    private $_DB;       // Reference to the active DB
    var $errMsg;    // Variable where the errors are stored

    function paloSantoPortKnockUsers(&$pDB)
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
    
    function countAuthorizedUsers()
    {
    	$row = $this->_DB->getFirstRowQuery(
            'SELECT COUNT(*) FROM (SELECT DISTINCT id_user FROM portknock_user_auth)');
        return is_array($row) ? $row[0] : NULL;
    }
    
    function listAuthorizedUsers()
    {
        $result = $this->_DB->fetchTable(
            'SELECT portknock_user_auth.id, portknock_user_auth.id_user, '.
                'portknock_user_auth.id_port, port.name, port.protocol, '.
                'port.details '.
            'FROM portknock_user_auth, port '.
            'WHERE portknock_user_auth.id_port = port.id',
            TRUE);
        if (!is_array($result)) {
            $this->errMsg = $this->_DB->errMsg;
            return NULL;
        }

        $userAuth = array();
        foreach ($result as $row) {
        	$userAuth[$row['id_user']][] = array(
                'id'        =>  $row['id'],
                'id_port'   =>  $row['id_port'],
                'name'      =>  $row['name'],
                'protocol'  =>  $row['protocol'],
                'details'   =>  $row['details'],
            );
        }
        return $userAuth;
    }
    
    function listAuthorizationsForUser($id_user)
    {
    	$result = $this->_DB->fetchTable(
            'SELECT portknock_user_auth.id, portknock_user_auth.id_user, '.
                'portknock_user_auth.id_port, port.name, port.protocol, '.
                'port.details '.
            'FROM portknock_user_auth, port '.
            'WHERE portknock_user_auth.id_port = port.id '.
                'AND portknock_user_auth.id_user = ?',
            TRUE, array($id_user));
        if (!is_array($result)) {
            $this->errMsg = $this->_DB->errMsg;
            return NULL;
        }
        return $result;
    }
    
    function insertAuthorization($id_user, $id_port)
    {
    	$r = $this->_DB->genQuery(
            'INSERT INTO portknock_user_auth (id_user, id_port) VALUES (?, ?)',
            array($id_user, $id_port));
        if (!$r) {
        	$this->errMsg = $this->_DB->errMsg;
            return NULL;
        }
        return $this->_DB->getLastInsertId();
    }
    
    function deleteAuthorization($id_auth)
    {
        $r = $this->_DB->genQuery(
            'DELETE FROM portknock_user_current_rule WHERE id_portknock_auth = ?',
            array($id_auth));
        if (!$r) {
            $this->errMsg = $this->_DB->errMsg;
            return $r;
        }
    	$r = $this->_DB->genQuery(
            'DELETE FROM portknock_user_auth WHERE id = ?', 
            array($id_auth));
        if (!$r) $this->errMsg = $this->_DB->errMsg;
        return $r;
    }

    function deleteUserAuthorizations($id_user)
    {
        $r = $this->_DB->genQuery(
            'DELETE FROM portknock_user_current_rule '.
            'WHERE id_portknock_auth IN '.
                '(SELECT id FROM portknock_user_auth WHERE id_user = ?)', 
            array($id_user));
        if (!$r) {
            $this->errMsg = $this->_DB->errMsg;
            return $r;
        }
        
        $r = $this->_DB->genQuery(
            'DELETE FROM portknock_user_auth WHERE id_user = ?', 
            array($id_user));
        if (!$r) $this->errMsg = $this->_DB->errMsg;
        return $r;
    }
}
?>