<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.4-2                                               |
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
  $Id: index.php,v 1.1 2008-09-11 03:09:47 Alex Villacis Lasso <a_villacis@palosanto.com> Exp $ */

class paloSantoPortKnockInterfaces
{
    private $_DB;       // Reference to the active DB
    var $errMsg;    // Variable where the errors are stored

    function paloSantoPortKnockInterfaces(&$pDB)
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
    
    function listProtectedInterfaces()
    {
    	$result = $this->_DB->fetchTable(
            'SELECT DISTINCT portknock_eth.eth_in, portknock_eth.udp_port, '.
                'portknock_user_current_rule.ip_source '.
            'FROM portknock_eth '.
            'LEFT JOIN portknock_user_current_rule '.
                'ON portknock_eth.eth_in = portknock_user_current_rule.eth_in',
            TRUE);
        if (!is_array($result)) {
            $this->errMsg = $this->_DB->errMsg;
            return NULL;
        }
        $interfaces = array();
        foreach ($result as $row) {
        	if (!isset($interfaces[$row['eth_in']])) {
        		$interfaces[$row['eth_in']] = array(
                    'eth_in'    => $row['eth_in'],
                    'udp_port'  => $row['udp_port'],
                    'num_auth'  => is_null($row['ip_source']) ? 0 : 1,
                );
        	} else {
        		$interfaces[$row['eth_in']]['num_auth']++;
        	}
        }
        return $interfaces;
    }
    
    function removeProtectedInterface($eth_in)
    {
    	$sqls = array(
            'DELETE FROM portknock_user_current_rule WHERE eth_in = ?',
            'DELETE FROM portknock_eth WHERE eth_in = ?',
        );
        foreach ($sqls as $sql) {
        	$r = $this->_DB->genQuery($sql, array($eth_in));
            if (!$r) {
                $this->errMsg = $this->_DB->errMsg;
            	return FALSE;
            }
        }
        
        // Reiniciar servicio de portknock para actualizar escuchas
        $output = $retval = NULL;
        exec('sudo /sbin/service generic-cloexec elastix-portknock restart', $output, $retval);
        if ($retval != 0) {
            $this->errMsg = _tr('Failed to restart portknock service');
        	return FALSE;
        }

        // Ejecutar iptables para revocar las reglas del usuario
        require_once "modules/sec_rules/libs/paloSantoRules.class.php";
        $pr = new paloSantoRules($this->_DB);
        if (!$pr->activateRules()) {
        	$this->errMsg = $pr->errMsg;
            return FALSE;
        }

        return TRUE;
    }
    
    function setProtectedInterfacePort($eth_in, $port)
    {
        $row = $this->_DB->getFirstRowQuery(
            'SELECT COUNT(*) FROM portknock_eth WHERE eth_in = ?',
            FALSE, array($eth_in));
        if (!is_array($row)) {
            $this->errMsg = $this->_DB->errMsg;
        	return FALSE;
        }
        $bNuevaInterfaz = ($row[0] <= 0); 
        $r = $this->_DB->genQuery(
            ($bNuevaInterfaz
                ? 'INSERT INTO portknock_eth (udp_port, eth_in) VALUES (?, ?)' 
                : 'UPDATE portknock_eth SET udp_port = ? WHERE eth_in = ?'),
            array($port, $eth_in));
        if (!$r) {
            $this->errMsg = $this->_DB->errMsg;
        	return FALSE;
        }
    	
        // Reiniciar servicio de portknock para actualizar escuchas
        $output = $retval = NULL;
        exec('sudo /sbin/service generic-cloexec elastix-portknock restart', $output, $retval);
        if ($retval != 0) {
            $this->errMsg = _tr('Failed to restart portknock service');
            return FALSE;
        }

        // Ejecutar iptables para iniciar el bloqueo sobre la interfaz nueva
        if ($bNuevaInterfaz) {
            require_once "modules/sec_rules/libs/paloSantoRules.class.php";
            $pr = new paloSantoRules($this->_DB);
            if (!$pr->activateRules()) {
                $this->errMsg = $pr->errMsg;
                return FALSE;
            }
        }

        return TRUE;
    }

    function listAuthorizationsInterface($eth_in)
    {
    	$sql =
            'SELECT portknock_user_auth.id_user, portknock_user_current_rule.id, '.
                'portknock_user_current_rule.ip_source, portknock_user_current_rule.rule_start, '.
                'port.name '.
            'FROM portknock_user_current_rule, portknock_user_auth, port '.
            'WHERE portknock_user_current_rule.id_portknock_auth = portknock_user_auth.id '.
                'AND portknock_user_auth.id_port = port.id '.
                'AND portknock_user_current_rule.eth_in = ?';
        $recordset = $this->_DB->fetchTable($sql, TRUE, array($eth_in));
        if (!is_array($recordset)) {
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        
        $auths = array();
        foreach ($recordset as $row) {
        	if (!isset($auths[$row['id_user']]))
                $auths[$row['id_user']] = array();
            if (!isset($auths[$row['id_user']][$row['ip_source']]))
                $auths[$row['id_user']][$row['ip_source']] = array();
            $auths[$row['id_user']][$row['ip_source']][$row['id']] = 
                array('name' => $row['name'], 'rule_start' => $row['rule_start']);
        }
        return $auths;
    }
    
    function removeAuthorizationsUserInterface($id_user, $ip_source)
    {
    	$r = $this->_DB->genQuery(
            'DELETE FROM portknock_user_current_rule '.
            'WHERE ip_source = ? AND id_portknock_auth IN ('.
                'SELECT id FROM portknock_user_auth WHERE id_user = ?)',
            array($ip_source, $id_user));
        if (!$r) {
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        
        // Ejecutar iptables para revocar las reglas del usuario
        require_once "modules/sec_rules/libs/paloSantoRules.class.php";
        $pr = new paloSantoRules($this->_DB);
        if (!$pr->activateRules()) {
            $this->errMsg = $pr->errMsg;
            return FALSE;
        }

        return TRUE;
    }
}
?>