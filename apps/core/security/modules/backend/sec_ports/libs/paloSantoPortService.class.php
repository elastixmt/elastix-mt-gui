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
  $Id: paloSantoPuertos.class.php,v 1.1 2010-12-13 03:09:33 Alberto Santos asantos@palosanto.com Exp $ */
class paloSantoPortService {
    var $_DB;       // Reference to the active DB
    var $errMsg;    // Variable where the errors are stored

    /**
     * Constructor of the class, receives as a parameter the database, which is stored in the class variable $_DB
     *  .
     * @param string    $pDB     object of the class paloDB    
     */
    function paloSantoPortService(&$pDB)
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

    /*HERE YOUR FUNCTIONS*/

    /**
     * Function that returns the number of ports (data) in the database
     *  .
     * @param string    $field_type        string that indicates the filter applying (by name or by protocol)
     * @param string    $field_pattern     string that has the pattern of the filter
     *
     * @return integer  0 in case of an error or the number of ports in the database
     */
    function ObtainNumPuertos($field_type, $field_pattern)
    {
        $query  = "SELECT COUNT(*) FROM port ";
        $arrParm = null;
        if( strlen($field_pattern) != 0 ){
            if( $field_type == 'name' ){
                $arrParm = array("%$field_pattern%");
                $query .= "WHERE name LIKE ? ";
            }
            else if( $field_type == 'protocol' ){
                $arrParm = array("%$field_pattern%");
                $query .= "WHERE protocol LIKE ? ";
            }
        }

        $result = $this->_DB->getFirstRowQuery($query,false,$arrParm);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }

        return $result[0];
    }

     /**
     * Function that returns an array with all the ports available in the database
     *
     * @param integer    $limit             Value to limit the result of the query
     * @param integer    $offset            Value for the offset of the query
     * @param string     $field_type        string that indicates the filter applying (by name or by protocol)
     * @param string     $field_pattern     string that has the pattern of the filter
     *
     * @return array   empty if an error occurs or the data with the ports
     */
    function ObtainPuertos($limit, $offset, $field_type, $field_pattern)
    {
        $query   = "SELECT * FROM port ";
        if( strlen($field_pattern) != 0 ){
            if( $field_type == 'name' ){
                $arrParm = array("%$field_pattern%");
                $query .= "WHERE name LIKE ? ";
            }
            else if( $field_type == 'protocol' ){
                $arrParm = array("%$field_pattern%");
                $query .= "WHERE protocol LIKE ? ";
            }
        }
        $arrParm[] = $limit;
        $arrParm[] = $offset;
        $query .= "LIMIT ? OFFSET ? ";

        $result = $this->_DB->fetchTable($query, true, $arrParm);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    /**
     * Function that saves a new port into the database
     *
     * @param string     $name              Name of the protocol to be saved
     * @param string     $protocol          Type of protocol to be saved (TCP, UDP, ICMP, IP)
     * @param string     $port              Number of the port (only for TCP or UDP)
     * @param string     $type              Type of protocol ICMP (only for ICMP)
     * @param string     $code              Code of protocol ICMP (only for ICMP)
     * @param string     $protocol_number   Number of the protocol IP (only for IP)
     * @param string     $comment           A comment about the port to be saved
     *
     * @return bool      false if an error occurs or true if the port is correctly saved
     */
    function savePuertos($name, $protocol, $port, $type, $code, $protocol_number, $comment)
    {
        if($protocol == "TCP" || $protocol == "UDP"){
            $arrParm = array($name,$protocol,$port,$comment);
        }
        elseif($protocol == "ICMP"){
            $value = $type.":".$code;
            $arrParm = array($name,$protocol,$value,$comment);
        }
        else
            $arrParm = array($name,$protocol,$protocol_number,$comment);
        $query = "INSERT INTO port(name,protocol,details,comment) ".
                 "VALUES(?,?,?,?)";
        $result = $this->_DB->genQuery($query,$arrParm);

        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }

        return true;
    }

    /**
     * Function that updates the data of an existing port
     *
     * @param string     $id                id of the port to be updated
     * @param string     $name              New name to be set in the port
     * @param string     $protocol          New protocol to be set in the port
     * @param string     $port              New number of port to be set in the port (only for TCP or UDP)
     * @param string     $type              New type of port to be set (only for ICMP)
     * @param string     $code              New code of port to be set (only for ICMP)
     * @param string     $protocol_number   New ip protocol number to be set (only for IP)
     * @param string     $comment           New comment to be set in the port
     *
     * @return bool      false if an error occurs or true if the port is correctly updated
     */
    function updatePuertos($id, $name, $protocol, $port, $type, $code, $protocol_number, $comment, &$desactivated)
    {
        $query = "UPDATE port SET name=?, protocol=?, details=?, comment=? ".
                 "WHERE id = ?";

        if($protocol == "TCP" || $protocol == "UDP"){
            $arrParm = array($name,$protocol,$port,$comment,$id);
        }
        elseif($protocol == "ICMP"){
            $value = $type.":".$code;
            $arrParm = array($name,$protocol,$value,$comment,$id);
        }
        else
            $arrParm = array($name,$protocol,$protocol_number,$comment,$id);
        

        $result = $this->_DB->genQuery($query,$arrParm);

        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	$query = "SELECT * FROM filter WHERE sport=? OR dport=? OR icmp_type=? OR number_ip=?";
	$result = $this->_DB->fetchTable($query, true, array($id,$id,$id,$id));
	if( $result === false ){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	if(is_array($result) && count($result)>0){
	    $query = "UPDATE tmp_execute SET exec_in_sys = 0";
	    $genQuery = $this->_DB->genQuery($query);
	    if( $genQuery == false ){
		$this->errMsg = $this->_DB->errMsg;
		return false;
	    }
	    foreach($result as $key => $rule){
		$arrParam = array();
		$arrParam[] = $protocol;
		$query = "UPDATE filter SET protocol=?";
		if(($protocol == "TCP" || $protocol == "UDP") && ($rule["protocol"] == "ICMP" || $rule["protocol"] == "IP")){
		    $query .= ", activated=0";
		    $desactivated = true;
		}

		if($protocol == "TCP" || $protocol == "UDP"){
		    if($rule["protocol"] == "TCP" || $rule["protocol"] == "UDP")
			$query .= ", icmp_type='', number_ip=''";
		    else{
			$query .= ", sport=?, dport=?, icmp_type='', number_ip=''";
			$arrParam[] = $id;
			$arrParam[] = $id;
		    }
		}
		elseif($protocol == "ICMP"){
		    $query .= ", sport='', dport='', icmp_type=?, number_ip=''";
		    $arrParam[] = $id;
		}
		else{
		    $query .= ", sport='', dport='', icmp_type='', number_ip=?";
		    $arrParam[] = $id;
		}
		$query .= " WHERE id=?";
		$arrParam[] = $rule["id"];
		$result = $this->_DB->genQuery($query,$arrParam);
		if( $result == false ){
		    $this->errMsg = $this->_DB->errMsg;
		    return false;
		}
	    }
	}
        return true;
    }

    /**
     * Function that indicates if a port is already saved in the database
     *
     * @param string     $protocol          Protocol to be compared
     * @param string     $port              Port to be compared (only for TCP or UDP)
     * @param string     $type              Type of port to be compared (only for ICMP)
     * @param string     $code              Code of port to be compared (only for ICMP)
     * @param string     $protocol_number   ip protocol number to be compared (only for IP)
     * @param string     $id_except         id to be compared
     * 
     * @return bool      false the port does not exist or true if the port is already in the database
     */
    function hasPuerto($protocol, $port, $type, $code, $protocol_number, &$name, $id_except = 0)
    {
        if($protocol == "TCP" || $protocol == "UDP")
            $value = $port;
        elseif($protocol == "ICMP")
            $value = $type.":".$code;
        else
            $value = $protocol_number;
        $arrParm = array($protocol,$value);
        $query = "SELECT * ".
                 "FROM port ".
                 "WHERE protocol=? AND details=? ";

        if( $id_except != 0 ){
            $arrParm[] = $id_except;
            $query .= " AND id <> ? ";
        }
        $result = $this->_DB->getFirstRowQuery($query, true, $arrParm);

        if( $result == false )
            return false;
	$name = $result["name"];
        return true;
    }

    /**
     * Function that searches in the database an existing port
     *
     * @param string     $id                id of the port to be searched
     *
     * @return mixed     false if an error occurs or an array with all the data of the port
     */
    function loadPuerto($id)
    {
        $query = "SELECT * FROM port WHERE id = ?";
        $arrParm = array($id);
        $result = $this->_DB->fetchTable($query, true, $arrParm);

        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }

        return $result[0];
    }

    /**
     * Function that deletes a port of the database
     *
     * @param string     $id                id of the port to be deleted
     *
     * @return bool      false if an error occurs or true if the port is correctly deleted
     */
    function deletePuerto($id)
    {
        $query = "DELETE FROM port WHERE id = ?";
        $arrParm = array($id);
        $result = $this->_DB->genQuery($query,$arrParm);

        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }

        return true;
    }

    /**
     * Function that searches all the number of ports TCP or UDP in the database
     *
     * @return array      an empty array if an error occurs or an array with the data of the number of ports TCP or UDP if everything is fine
     */
    function getTCPortNumbers()
    {
        $query = "SELECT id,name FROM port WHERE protocol = 'TCP'";
        $result = $this->_DB->fetchTable($query, true);
        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getUDPortNumbers()
    {
        $query = "SELECT id,name FROM port WHERE protocol = 'UDP'";
        $result = $this->_DB->fetchTable($query, true);
        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    /**
     * Function that searches all the types for the protocol ICMP available in the database
     *
     * @return array      an empty array if an error occurs or an array with the data of the type of the protocol ICMP if everything is fine
     */
    function getICMPType()
    {
        $query = "SELECT id,name FROM port WHERE protocol = 'ICMP'";
        $result = $this->_DB->fetchTable($query, true);
        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    /**
     * Function that searches all the IP protocol_numbers available in the database
     *
     * @return array      an empty array if an error occurs or an array with the data of the IP protocol_number if everything is fine
     */
    function getIPProtNumber()
    {
        $query = "SELECT id,name FROM port WHERE protocol = 'IP'";
        $result = $this->_DB->fetchTable($query, true);
        if( $result == false ){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        } 
        return $result;
    }

    function isPortInService($id, &$port)
    {
        $query = "SELECT COUNT(*) FROM filter WHERE sport=? OR dport=? OR icmp_type=? OR number_ip=?";
        $result = $this->_DB->getFirstRowQuery($query,false,array($id,$id,$id,$id));

        if ($result===FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        if ($result[0] > 0) {
            // Verificar si el puerto se usa en regla de portknocking
            $result = $this->_DB->getFirstRowQuery(
                'SELECT COUNT(*) FROM portknock_user_auth WHERE id_port = ?', 
                FALSE, array($id));
            if ($result===FALSE) {
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }
            return ($result[0] > 0);
        } else
            return false;
    }
}
?>