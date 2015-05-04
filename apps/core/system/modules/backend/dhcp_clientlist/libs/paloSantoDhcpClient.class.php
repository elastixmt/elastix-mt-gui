<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.5-9                                               |
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
  $Id: paloSantoDhcpClienList.class.php,v 1.1 2009-05-13 10:05:04 Oscar Navarrete onavarrete@palosanto.com Exp $ */
class paloSantoDhcpClienList {
    var $_DB;
    var $errMsg;

    function paloSantoDhcpClienList(&$pDB)
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


    /////////NEW FUNCTIONS FOR MODULE DHCP_CLIENT
    
    function readFileDhcpClient()
    {
        $myFile='/var/lib/dhcpd/dhcpd.leases';
        $fh = fopen($myFile, 'r');
    
        return $fh;
    }

    function saveNewFileConf($reemplazar){
        $fp = fopen('/var/lib/dhcpd/dhcpd.leases', 'w');
        
        fwrite($fp, $reemplazar);

        fclose($fp);
        return $reemplazar;
    }

    function addSantoDhcpClienList($data)
    {
        $queryInsert = $this->_DB->construirInsert('dhcp_info', $data);

        $result = $this->_DB->genQuery($queryInsert);

        return $result;
    }



//     function getDhcpClientByAll(){
//         $query   = "SELECT iphost, date_start, macaddress FROM dhcp_info ";
//         
//         $result=$this->_DB->fetchTable($query, true);
// 
//         if($result==FALSE){
//             $this->errMsg = $this->_DB->errMsg;
//             return array();
//         }
//         return $result;
//     }


    //function saveFileDhcpClientList($pDB){
    function getDhcpClientList(){
	    $FILE='/var/lib/dhcpd/dhcpd.leases';
        //$query = "DELETE FROM dhcp_info";
        //$result = $this->_DB->genQuery($query);
	    $count = 1;
	    $data = array();
	    $fp = fopen($FILE,'r');
        
        while($line = fgets($fp))
        {
            // Saltarse los comentarios
            if (preg_match('/^\s*#/', $line)) continue;

	        if(eregi("lease", $line)) {
		        if(ereg("([0-9.]+)", $line, $arrReg)){
		            //$data[$count]['iphost'] = $pDB->DBCAMPO($arrReg[1]);
		            $data[$count]['iphost'] = $arrReg[1];
		        }
	        }elseif(eregi("starts", $line)) {
		        if(ereg("^[[:space:]][[:space:]]([[:alnum:]]+)[[:space:]]([[:digit:]]+)[[:space:]]([0-9/]+)[[:space:]]([0-9:]+)", $line, $arrReg)){
		            //$data[$count]['date_starts'] = $pDB->DBCAMPO($arrReg[3]." ".$arrReg[4]);
		            $data[$count]['date_starts'] = $arrReg[3]." ".$arrReg[4];
		        }
	        }elseif(eregi("ends", $line)) {
		        if(ereg("^[[:space:]][[:space:]]([[:alnum:]]+)[[:space:]]([[:digit:]]+)[[:space:]]([0-9/]+)[[:space:]]([0-9:]+)", $line, $arrReg)){
		            //$data[$count]['date_ends'] = $pDB->DBCAMPO($arrReg[3]." ".$arrReg[4]);
		            $data[$count]['date_ends'] = $arrReg[3]." ".$arrReg[4];
		        }else $data[$count]['date_ends'] = "";
	        }elseif(eregi("hardware", $line)) {
		        if(ereg("^[[:space:]][[:space:]]([[:alnum:]]+)[[:space:]]([[:alnum:]]+)[[:space:]]([a-z0-9:]+)", $line, $arrReg)){
		            //$data[$count]['macaddress'] = $pDB->DBCAMPO($arrReg[3]);
		            $data[$count]['macaddress'] = $arrReg[3];
		        }
		        $count++;
	        }
        }
// 	$result = $this->addSantoDhcpClienList($data);
// 
//         if($result == false){
//             $this->errMsg = $this->_DB->errMsg;
//             return false;
//         }
        fclose($fp);
        return $data;
    }
    
    function getDhcpClientListById($id)
    {
//         $query   = "SELECT * FROM dhcp_info ";
//         $strWhere = "id=$id";
// 
//         // Clausula WHERE aqui
//         if(!empty($strWhere)) $query .= "WHERE $strWhere ";
// 
//         $result=$this->_DB->getFirstRowQuery($query, true);
        $result = array();
        $arrResult = $this->getDhcpClientList();

        if(is_array($arrResult) && count($arrResult)>0){
            for($i=1 ; $i<=count($arrResult); $i++){
                if($id==$i){
                    $result['iphost'] = $arrResult[$i]['iphost'];
                    $result['date_starts'] = $arrResult[$i]['date_starts'];
                    if($arrResult[$i]['date_ends']!="") $result['date_ends'] = $arrResult[$i]['date_ends'];
                    else $result['date_ends'] = "never";
                    $result['macaddress'] = $arrResult[$i]['macaddress'];
                }
            }
        }

        return $result;
    }


//     function updateDhcpClientList($data, $where)
//     {
//         $queryUpdate = $this->_DB->construirUpdate('dhcp_info', $data,$where);
//         $result = $this->_DB->genQuery($queryUpdate);
// 
//         return $result;
//     }
}
?>