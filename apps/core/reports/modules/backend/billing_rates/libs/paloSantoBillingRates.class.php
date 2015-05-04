<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-12                                               |
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
  $Id: paloSantoBillingRates.class.php,v 1.1 2010-01-27 02:01:42 Eduardo Cueva ecueva@palosanto.com Exp $ */
class paloSantoBillingRates {
    var $_DB;
    var $errMsg;

    function paloSantoBillingRates(&$pDB)
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

    function getNumBillingRates()
    {
        $query   = "SELECT COUNT(*) FROM rate WHERE estado = 'activo'";

        $result=$this->_DB->getFirstRowQuery($query);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function getBillingRates($limit, $offset)
    {
        $query   = "SELECT * FROM rate WHERE estado='activo' limit $limit OFFSET $offset";

        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result; 
    }

    function getBillingRatesById($id)
    {
        $query = "SELECT * FROM rate WHERE id=$id";

        $result=$this->_DB->getFirstRowQuery($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

	 function getBillingALLRates()
    {
        $query = "SELECT * FROM rate WHERE estado='activo'";

        $result=$this->_DB->fetchTable($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function getRatesPast($id)
    {
        $data = array($id, $id);
        $query = "SELECT name, prefix, rate, rate_offset, fecha_creacion, fecha_cierre, trunk, hided_digits, estado FROM rate where idParent=? or id = ? order by id desc";

        $result=$this->_DB->fetchTable($query,true,$data);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function updateIdParent($id_old, $idParent){
        $data = array($idParent, $id_old, $id_old);
        $query = "UPDATE rate SET idParent=?, estado='desactivo' WHERE idParent=? or id=?";
        $result = $this->_DB->genQuery($query, $data);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
     }

	 
	 function getTrunks($db){

		$query = "SELECT trunk FROM trunk_bill";
		$result2 = "";
        $result=$db->fetchTable($query, true);
        if($result==FALSE){
            $this->errMsg = $db->errMsg;
            return null;
        }else{
				for($i=0; $i<count($result); $i++){
					$id = $result[$i]['trunk'];
					$result2[$id] = $result[$i]['trunk'];
				}
				return $result2;
		  }
	 }

	 function getCurrency($db){

		  $query = "SELECT value FROM settings WHERE key = 'currency'";
        $result=$db->getFirstRowQuery($query, true);
        if($result==FALSE){
            $this->errMsg = $db->errMsg;
            return null;
        }
		  return $result['value'];
	 }

    function createRate($prefix_new,$name_new,$rate_new,$rate_offset_new,$trunk_new,$date_ini,$hidden_digits){
          $data = array($name_new, $prefix_new, $rate_new, $rate_offset_new, $trunk_new, $date_ini, $hidden_digits);
		  $query = "INSERT INTO rate(name,prefix,rate,rate_offset,trunk,estado,fecha_creacion,hided_digits) VALUES(?,?,?,?,?,'activo',?,?)";
		  $result = $this->_DB->genQuery($query, $data);

	     if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true; 
	 }

	 function editRate($id,$name_new,$rate_new,$rate_offset_new,$trunk_new,$hidden_digits){
        $data = array($name_new,$rate_new,$rate_offset_new,$trunk_new,$hidden_digits,$id);
		$query = "UPDATE rate SET name=?, rate=?, rate_offset=?, trunk=?, hided_digits=? WHERE id=?";
		$result = $this->_DB->genQuery($query, $data);

	    if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
	 }

	 function deleteRate($id){
		  $date_close = date("Y-m-d H:i:s");
          $data = array($date_close,$id);
		  $query = "UPDATE rate SET estado='desactivo', fecha_cierre=? where id = ?";
		  $result = $this->_DB->genQuery($query, $data);

	     if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
	 }

	 function existsDefaultRate($db){
		$query = "SELECT name FROM rate WHERE name = 'Default'";
        $result = $this->_DB->getFirstRowQuery($query, true);
        $default_rate = $this->getDefaultRates($db);
        $rate_new = 0;
        $rate_offset_new = 0;
        $date_ini = date("Y-m-d H:i:s");
        if($default_rate != null){
            $rate_new = $default_rate['rate'];
            $rate_offset_new = $default_rate['rate_offset'];
        }
        if($result==FALSE || $result['name']==""){
	        //create default rate
            $this->createRate("",'Default',$rate_new,$rate_offset_new,"",$date_ini,0);
        }else{
            //update default rate
            $this->updateDefaultRate($rate_new,$rate_offset_new,"");
        }
	 }

     function updateDefaultRate($rate_new,$rate_offset_new,$hidden_digits){
        $set ="";
        if($hidden_digits!=""){
            $set = ", hided_digits=$hidden_digits ";
        }
        $data = array($rate_new, $rate_offset_new);
        $query = "UPDATE rate SET rate=?, rate_offset=? $set WHERE name='Default'";

        $result = $this->_DB->genQuery($query,$data);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
     }

     function updateSettingRate($rate_new,$rate_offset_new, $db){
        $data1 = array($rate_new);
        $data2 = array($rate_offset_new);
        $query  = "UPDATE settings SET value=? WHERE key='default_rate'";
        $query2 = "UPDATE settings SET value=? WHERE key='default_rate_offset'";
        $result = $db->genQuery($query, $data1);
        $result2 = $db->genQuery($query2, $data2);
        if($result==FALSE && $result2==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
     }

	 function getDefaultRates($db){
		$query = "SELECT * FROM settings";
		$sal = "";
        $result=$db->fetchTable($query, true);
        if($result==FALSE){
            $this->errMsg = $db->errMsg;
            return null;
        }else{
			for($i=0; $i<count($result); $i++){
				$key   = $result[$i]['key'];
				$value = $result[$i]['value'];
				if($key == 'default_rate'){
					$sal['rate'] = $value;
				}
				if($key == 'default_rate_offset'){
					$sal['rate_offset'] = $value;
				}
			}
            return $sal;
		}
		
	 }
	 
	 function contRates(){
        $query = "select count(*) as cant from rate";
        $result=$this->_DB->getFirstRowQuery($query,true);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }
}
?>
