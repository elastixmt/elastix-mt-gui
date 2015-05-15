<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0                                               |
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
  $Id: paloSantoRules.class.php,v 1.2 2010-12-20 03:09:47 Alberto Santos asantos@palosanto.com Exp $ */

require_once "libs/paloSantoNetwork.class.php";

class paloSantoRules {
    var $_DB;       // Reference to the active DB
    var $errMsg;    // Variable where the errors are stored

     /**
     * Constructor of the class, receives as a parameter the database, which is stored in the class variable $_DB
     *  .
     * @param string    $pDB     object of the class paloDB    
     */
    function paloSantoRules(&$pDB)
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
     * Function that returns the number of rules (data) in the database
     *  .
     * @return integer  0 in case of an error or the number of rules in the database
     */
    function ObtainNumRules()
    {
        $query = "SELECT COUNT(*) FROM Filter ";
        
        $result = $this->_DB->getFirstRowQuery($query);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

     /**
     * Function that returns all the rules in the database that are set as activated (1) order by the field rule_order
     *
     * @return array  empty if an error occurs or the data with the rules
     */
    function getActivatedRules()
    {
        $query   = "SELECT * FROM  filter WHERE activated = 1 ORDER BY rule_order";
        $result = $this->_DB->fetchTable($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    /**
     * Function that returns an especific rule
     *
     * @param string     $id          id of the port to be searched
     *
     * @return array     empty if an error occurs or the data of the especific rule
     */
    function getRule($id)
    {
        $arrParam = array($id);
        $query = "SELECT * FROM filter where id=?";
        $result = $this->_DB->fetchTable($query, true, $arrParam);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result[0];
    }

    /**
     * Function that returns all the rules in the database order by the field rule_order
     *
     * @param integer    $limit         Value to limit the result of the query
     * @param integer    $offset        Value for the offset of the query
     *
     * @return array     empty if an error occurs or an array with all the rules
     */
    function ObtainRules($limit,$offset)
    {
        $query   = "SELECT * FROM  filter ORDER BY rule_order LIMIT ? OFFSET ?";
        $arrParam = array($limit,$offset);
        $result = $this->_DB->fetchTable($query, true, $arrParam);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    /**
     * Function that saves a new rule into the database
     *
     * @param array     $arrValues        Array with all the data of the rule to be saved
     *
     * @return bool     false if an error occurs or true if the port is correctly saved
     */
    function saveRule( $arrValues )
    {
        $traffic   = ($arrValues['traffic'] == null)       ? "" : $arrValues['traffic'];
        $eth_in    = ($arrValues['interface_in'] == null)  ? "" : $arrValues['interface_in'];
        $eth_out   = ($arrValues['interface_out'] == null) ? "" : $arrValues['interface_out'];

        $ip_s      = ($arrValues['ip_source'] == null)     ? "" : $arrValues['ip_source'];
        $ip_mask_s = ($arrValues['mask_source'] == null)   ? "" : $arrValues['mask_source'];
        if($ip_s != "")
            if($ip_mask_s != "")
                $source = $ip_s."/".$ip_mask_s;
            else
                $source = $ip_s;
        else
            $source = "";
        $ip_d      = ($arrValues['ip_destin'] == null)     ? "" : $arrValues['ip_destin'];
        $ip_mask_d = ($arrValues['mask_destin'] == null)   ? "" : $arrValues['mask_destin'];
        if($ip_d != "")
            if($ip_mask_d != "")
                $destino = $ip_d."/".$ip_mask_d;
            else
                $destino = $ip_d;
        else
            $destino = "";
        $protocol  = ($arrValues['protocol'] == null)      ? "" : $arrValues['protocol'];
        $port_in   = ($arrValues['port_in'] == null)       ? "" : $arrValues['port_in'];
        $port_out  = ($arrValues['port_out'] == null)      ? "" : $arrValues['port_out'];
        $type_icmp = ($arrValues['type_icmp'] == null)     ? "" : $arrValues['type_icmp'];
        $id_ip     = ($arrValues['id_ip'] == null)         ? "" : $arrValues['id_ip'];
        $state     =  $arrValues['state'];
        $target    = ($arrValues['target'] == null)        ? "" : $arrValues['target'];
        $Max = $this->getMaxOrder();
        $order = 1 + $Max['lastRule'];
        $query = "INSERT INTO filter(traffic, eth_in, eth_out, ip_source, ip_destiny, protocol, ".
                                    "sport, dport, icmp_type, number_ip, target, rule_order, activated, state) ".
                 "VALUES(?,?,?,?,?,?,?,?,?,?,?,?,1,?)";
        $arrParam = array($traffic,$eth_in,$eth_out,$source,$destino,$protocol,$port_in,$port_out,$type_icmp,$id_ip,$target,$order,$state);
        $result = $this->_DB->genQuery($query,$arrParam);

        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    /**
     * Function that updates the data of an existing port
     *
     * @param array      $arrValues         Array with all the new data of the rule
     * @param string     $id                id of the rule to be updated
     *
     * @return bool      false if an error occurs or true if the rule is correctly updated
     */
    function updateRule($arrValues,$id)
    {
        $traffic   = ($arrValues['traffic'] == null)       ? "" : $arrValues['traffic'];
        $eth_in    = ($arrValues['interface_in'] == null)  ? "" : $arrValues['interface_in'];
        $eth_out   = ($arrValues['interface_out'] == null) ? "" : $arrValues['interface_out'];

        $ip_s      = ($arrValues['ip_source'] == null)     ? "" : $arrValues['ip_source'];
        $ip_mask_s = ($arrValues['mask_source'] == null)   ? "" : $arrValues['mask_source'];
        if($ip_s != "")
            if($ip_mask_s != "")
                $source = $ip_s."/".$ip_mask_s;
            else
                $source = $ip_s;
        else
            $source = "";
        $ip_d      = ($arrValues['ip_destin'] == null)     ? "" : $arrValues['ip_destin'];
        $ip_mask_d = ($arrValues['mask_destin'] == null)   ? "" : $arrValues['mask_destin'];
        if($ip_d != "")
            if($ip_mask_d != "")
                $destino = $ip_d."/".$ip_mask_d;
            else
                $destino = $ip_d;
        else
            $destino = "";
        $protocol  = ($arrValues['protocol'] == null)      ? "" : $arrValues['protocol'];
        $port_in   = ($arrValues['port_in'] == null)       ? "" : $arrValues['port_in'];
        $port_out  = ($arrValues['port_out'] == null)      ? "" : $arrValues['port_out'];
        $type_icmp = ($arrValues['type_icmp'] == null)     ? "" : $arrValues['type_icmp'];
        $id_ip     = ($arrValues['id_ip'] == null)         ? "" : $arrValues['id_ip'];
        $state     =  $arrValues['state'];
        $target    = ($arrValues['target'] == null)        ? "" : $arrValues['target'];
        $orden     = ($arrValues['orden'] == null)         ?  0 : $arrValues['orden'];
        $query = "UPDATE filter SET traffic = ?, eth_in = ?, eth_out = ?, ip_source = ?, ip_destiny = ?, protocol = ?, sport = ?, dport = ?, icmp_type = ?, number_ip = ?, target = ?, rule_order = ?, state = ? WHERE id = ?";
        $arrParam = array($traffic,$eth_in,$eth_out,$source,$destino,$protocol,$port_in,$port_out,$type_icmp,$id_ip,$target,$orden,$state,$id);
        $result = $this->_DB->genQuery($query,$arrParam);

        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    /**
     * Function that returns the maximum number of order of all the rules in the database
     *  .
     * @return array     empty in case of an error or an array that contains the maximum order of all rules
     */
    private function getMaxOrder()
    {
        $query = "SELECT MAX(rule_order) AS lastRule FROM filter";    
        $result = $this->_DB->fetchTable($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result[0];
    }

    /**
     * Function that deletes a rule of the database
     *
     * @param string     $id         id of the rule to be deleted
     *
     * @return bool      false if an error occurs or true if the rule is correctly deleted
     */ 
    function deleteRule($id)
    {
        $arrParam = array($id);
        $query = "DELETE FROM filter WHERE id=?";
        $result = $this->_DB->genQuery($query,$arrParam);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $this->reorder();
    }

    /**
     * Function that reorder all the rules, if there is a jump between the order of one rule to the next one it eliminates that jump setting the * correct order 
     *
     * @return bool      false if an error occurs or true if the rules have been correctly reordered
     */
    private function reorder()
    {
        $total = $this->ObtainNumRules();
        $result = $this->ObtainRules($total,0);
        foreach($result as $key => $value){
            if($value['rule_order'] != $key + 1)
                if(!$this->updateOrder($value['id'],$key+1))
                    return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    /**
     * Function that returns the name of all the network interfaces available in the system
     *
     * @return array      Array with the name of the interfaces
     */
    function obtener_nombres_interfases_red() 
    {
        $pNet = new paloNetwork();

        //Se buscan las descripciones en la base de datos
        $arr_datos=array();
        $arr_descrip=array();
        
        $sQuery="SELECT * FROM interfase";
        $result=$this->_DB->fetchTable($sQuery,true);
        if(is_array($result) && count($result)>0){
            foreach($result as $fila)
                $arr_descrip[$fila['dev']]=array("nombre"=>$fila['nombre'],"descripcion"=>$fila['descripcion']);
        }
        
        $arr_interfases=$pNet->obtener_interfases_red();    
        foreach($arr_interfases as $dev=>$datos){
            if(array_key_exists($dev,$arr_descrip))
                //$arr_datos[$dev]=$arr_descrip[$dev]['nombre']." - ".$datos['Name'];
                $arr_datos[$dev]=$arr_descrip[$dev]['nombre'];
            else
                $arr_datos[$dev]=$datos['Name'];
        }
                
        return $arr_datos;                                 
    }

    /**
     * Function that sets an especific rule as activated (1) 
     *
     * @param string     $id         id of the rule to be activated
     *
     * @return bool      false if an error occurs or true if the rule is correctly activated
     */ 
    function setActivated($id)
    {
        $arrParam = array($id);
        $query = "UPDATE filter SET activated = 1 WHERE id = ?";
               
        $result = $this->_DB->genQuery($query,$arrParam);

        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    /**
     * Function that sets an especific rule as desactivated (0) 
     *
     * @param string     $id         id of the rule to be desactivated
     *
     * @return bool      false if an error occurs or true if the rule is correctly desactivated
     */ 
    function setDesactivated($id)
    {
        $arrParam = array($id);
        $query = "UPDATE filter SET activated = 0 WHERE id = ?";
               
        $result = $this->_DB->genQuery($query,$arrParam);

        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }
/*
    function desactivateAll()
    {
        $query = "UPDATE filter SET activated = 0";
               
        $result = $this->_DB->genQuery($query);

        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }
*/

    /**
     * Function that sets a new order for an especific rule 
     *
     * @param string     $id         id of the rule
     * @param string     $order      New order to be set    
     *
     * @return bool      false if an error occurs or true if new order is set
     */ 
    function updateOrder($id,$order)
    {
        $arrParam = array($order,$id);
        $query = "UPDATE filter SET rule_order = ? WHERE id = ?";
        $result = $this->_DB->genQuery($query,$arrParam);
        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    /**
     * Function that deletes all the rules of the system
     *
     * @return bool    false if an error occurs or true if the rules are deleted of the system
     */ 
    function flushRules()
    {
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper fwconfig --flush 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Function that activates the rules in the system 
     *
     * @return bool      false if an error occurs or true if the rules are correctly activated in the system
     */
    function activateRules()
    {
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper fwconfig --load 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Function that updates the database to indicate that something has not been executed on systen 
     *
     * @return bool      false if an error occurs or true if the update is successful
     */ 
    private function updateNotExecutedInSystem()
    {
        $query = "UPDATE tmp_execute SET exec_in_sys = 0";
        $result = $this->_DB->genQuery($query);
        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    /**
     * Function that updates the database to indicate that all has been executed on systen 
     *
     * @return bool      false if an error occurs or true if the update is successful
     */ 
    function updateExecutedInSystem()
    {
        $query = "UPDATE tmp_execute SET exec_in_sys = 1";
        $result = $this->_DB->genQuery($query);
        if( $result == FALSE )
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    /**
     * Function that indicates if everything has been executed on system or not 
     *
     * @return bool      false if something has not been executed on system or true if everything has
     */ 
    function isExecutedInSystem()
    {
        $query = "SELECT exec_in_sys from tmp_execute";
        $result = $this->_DB->fetchTable($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return;
        }
        $data = $result[0];
        if($data['exec_in_sys'] == 0)
            return false;
        return true;
    }

    function isFirstTime()
    {
        $query = "SELECT first_time from tmp_execute";
        $result = $this->_DB->fetchTable($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return;
        }
        $data = $result[0];
        if($data['first_time'] == 0)
            return false;
        return true;
    }

    function setFirstTime()
    {
        $query = "update tmp_execute set first_time = 1";
        $result = $this->_DB->genQuery($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $this->updateNotExecutedInSystem();
    }

    function noMoreFirstTime()
    {
        $query = "update tmp_execute set first_time = 0";
        $result = $this->_DB->genQuery($query, true);
        if($result == FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function getPreviousRule($actual_order)
    {
	$previous_order = $actual_order - 1;
	$query = "select * from filter where rule_order=?";
	$arrParam = array($previous_order);
	$result = $this->_DB->fetchTable($query, true, $arrParam);
	if($result == FALSE){
	    $this->errMsg = $this->_DB->errMsg;
            return null;
	}
	return $result[0];
    }

    function getNextRule($actual_order)
    {
	$next_order = $actual_order + 1;
	$query = "select * from filter where rule_order=?";
	$arrParam = array($next_order);
	$result = $this->_DB->fetchTable($query, true, $arrParam);
	if($result == FALSE){
	    $this->errMsg = $this->_DB->errMsg;
            return null;
	}
	return (isset($result[0]))?$result[0]:array();
    }

    function getProtocolName($idProtocol)
    {
	$query = "select name from port where id=?";
	$result=$this->_DB->getFirstRowQuery($query,true,array($idProtocol));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return "--";
        }
	return $result["name"];
    }
}
?>