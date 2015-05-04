<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.6-12                                               |
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
  $Id: paloSantoDHCP_Configuration.class.php,v 1.1 2009-11-12 04:11:04 Oscar Navarrete onavarrete.palosanto.com Exp $ */
class paloSantoDHCP_Configuration
{
	private $_DB;
    var $errMsg;

    function paloSantoDHCP_Configuration(&$pDB)
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

    /**
     * Procedimiento para contar el número de IPs fijas que se guardan en la 
     * base de datos.
     * 
     * @param   string  $filter_field   (opcional) Nombre del campo, uno de 
     *                                  hostname, ipaddress, macaddress
     * @param   string  $filter_value   (opcional) Prefijo por el cual filtrar
     * 
     * @return  FALSE en caso de error, o número de IPs que coinciden
     */
    function contarIpFijas($filter_field = '', $filter_value = '')
    {    	
        if (!in_array($filter_field, array('hostname', 'ipaddress', 'macaddress')))
            $filter_field = '';
        $sPeticionSQL = 'SELECT COUNT(*) FROM dhcp_conf';
        $paramSQL = array();
        if ($filter_field != '') {
        	$sPeticionSQL .= " WHERE $filter_field LIKE ?";
            $paramSQL[] = $filter_value.'%';
        }
        $result = $this->_DB->getFirstRowQuery($sPeticionSQL, FALSE, $paramSQL);

        if($result === FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        return $result[0];
    }
    
    /**
     * Procedimiento para leer las IPs fijas que se guardan en la base de datos.
     * 
     * @param   int     $limit          Número máximo de registros a devolver
     * @param   int     $offset         (opcional) Desde qué registro devolver
     * @param   string  $filter_field   (opcional) Nombre del campo, uno de 
     *                                  hostname, ipaddress, macaddress
     * @param   string  $filter_value   (opcional) Prefijo por el cual filtrar
     *
     * @return  mixed   NULL en caso de error, o lista de tupla con los 
     *                  siguientes campos: id hostname ipaddress macaddress
     */
    function leerIPsFijas($limit, $offset = 0, $filter_field = '', $filter_value = '')
    {    	
        if (!in_array($filter_field, array('hostname', 'ipaddress', 'macaddress')))
            $filter_field = '';
        $sPeticionSQL = 'SELECT id, hostname, ipaddress, macaddress FROM dhcp_conf';
        $paramSQL = array();
        if ($filter_field != '') {
            $sPeticionSQL .= " WHERE $filter_field LIKE ?";
            $paramSQL[] = $filter_value.'%';
        }
        $sPeticionSQL .= ' ORDER BY hostname LIMIT ? OFFSET ?';
        $paramSQL[] = (int)$limit; $paramSQL[] = (int)$offset;
        $result = $this->_DB->fetchTable($sPeticionSQL, TRUE, $paramSQL);

        if($result === FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return NULL;
        }
        return $result;
    }

    /**
     * Procedimiento para leer la información de una sola IP fija
     *
     * @param   int     $id         ID en base de datos del registro
     *  
     * @return  mixed   NULL en caso de error, o tupla con los siguientes 
     *                  campos: id hostname ipaddress macaddress
     */
    function leerInfoIPFija($id)
    {
    	$result = $this->_DB->getFirstRowQuery(
            'SELECT id, hostname, ipaddress, macaddress FROM dhcp_conf WHERE id = ?', 
            TRUE, array($id));
        if($result === FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return NULL;
        }
        return $result;
    }
    
    /**
     * Procedimiento para insertar una nueva IP fija en la base de datos
     * 
     * @param   string  $hostname   Nombre de host para el registro
     * @param   string  $ipaddress  Dirección IPv4 a asignar para el registro
     * @param   string  $macaddress Dirección MAC para el registro
     * 
     * @return  bool    VERDADERO en éxito, FALSO en error
     */
    function insertarIpFija($hostname, $ipaddress, $macaddress)
    {
        // Verificar que parámetros son válidos
        if (!preg_match('/^([[:alnum:]-]+)$/', $hostname)) {
            $this->errMsg = _tr('Invalid hostname');
        	return FALSE;
        }
        if (!preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ipaddress)) {
            $this->errMsg = _tr('Invalid IP address');
        	return FALSE;
        }
        if (!preg_match('/^[[:xdigit:]]{2}(:[[:xdigit:]]{2}){5}$/', $macaddress)) {
            $this->errMsg = _tr('Invalid MAC address');
            return FALSE;
        }
        
        // Verificar que no existen duplicado de MAC o IP en base de datos
        $result = $this->_DB->getFirstRowQuery(
            'SELECT COUNT(*) FROM dhcp_conf WHERE ipaddress = ? OR macaddress = ?', 
            FALSE, array($ipaddress, $macaddress));
        if($result === FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        if ($result[0] > 0) {
            $this->errMsg = _tr('Duplicate IP or MAC');
        	return FALSE;
        }
        
        // Insertar y refrescar configuración
        $result = $this->_DB->genQuery(
            'INSERT INTO dhcp_conf (hostname, ipaddress, macaddress) VALUES (?, ?, ?)',
            array($hostname, $ipaddress, $macaddress));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
        	return FALSE;
        }
        return $this->_actualizarConfiguracionDHCP();
    }
    
    /**
     * Procedimiento para actualizar una IP fija existente en la base de datos
     *
     * @param   int     $id         ID en base de datos del registro 
     * @param   string  $hostname   Nombre de host para el registro
     * @param   string  $ipaddress  Dirección IPv4 a asignar para el registro
     * @param   string  $macaddress Dirección MAC para el registro
     * 
     * @return  bool    VERDADERO en éxito, FALSO en error
     */
    function actualizarIpFija($id, $hostname, $ipaddress, $macaddress)
    {    	
        // Verificar que parámetros son válidos
        if (!preg_match('/^([[:alnum:]-]+)$/', $hostname)) {
            $this->errMsg = _tr('Invalid hostname');
            return FALSE;
        }
        if (!preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ipaddress)) {
            $this->errMsg = _tr('Invalid IP address');
            return FALSE;
        }
        if (!preg_match('/^[[:xdigit:]]{2}(:[[:xdigit:]]{2}){5}$/', $macaddress)) {
            $this->errMsg = _tr('Invalid MAC address');
            return FALSE;
        }
        
        // Verificar que no existen duplicado de MAC o IP en base de datos
        $result = $this->_DB->getFirstRowQuery(
            'SELECT COUNT(*) FROM dhcp_conf WHERE id <> ? AND (ipaddress = ? OR macaddress = ?)', 
            FALSE, array($id, $ipaddress, $macaddress));
        if($result === FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        if ($result[0] > 0) {
            $this->errMsg = _tr('Duplicate IP or MAC');
            return FALSE;
        }
        
        // Modificar y refrescar configuración
        $result = $this->_DB->genQuery(
            'UPDATE dhcp_conf SET hostname = ?, ipaddress = ?, macaddress = ? WHERE id = ?',
            array($hostname, $ipaddress, $macaddress, $id));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        return $this->_actualizarConfiguracionDHCP();
    }
    
    /**
     * Procedimiento para eliminar una IP fija existente de la base de datos
     *
     * @param   int     $id         ID en base de datos del registro 
     * 
     * @return  bool    VERDADERO en éxito, FALSO en error
     */
    function borrarIpFija($id)
    {
        // Borrar y refrescar configuración
        $result = $this->_DB->genQuery(
            'DELETE FROM dhcp_conf WHERE id = ?',
            array($id));
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        return $this->_actualizarConfiguracionDHCP();
    }
    
    // Llamar al programa privilegiado
    private function _actualizarConfiguracionDHCP()
    {
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper dhcpconfig --refresh 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }
}
?>