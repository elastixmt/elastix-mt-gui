<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
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
  $Id: paloSantoSampler.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ */

class paloSampler {

    private $rutaDB;
    private $errMsg;
    private $_db;

    function paloSampler()
    {
        global $arrConf;
        $this->rutaDB = $arrConf['elastix_dsn']['samples'];
        //instanciar clase paloDB
        $pDB = new paloDB($this->rutaDB);
	    if(!empty($pDB->errMsg)) {
        	echo "$pDB->errMsg <br>";
		}else{
			$this->_db = $pDB;
		}
    }

    function insertSample($idLine, $timestamp, $value)
    {
        $this->errMsg='';
        $sqliteError = '';
        $query = "INSERT INTO samples (id_line, timestamp, value) values ($idLine, '$timestamp', '$value')";
        $bExito = $this->_db->genQuery($query);
        if (!$bExito) {
            $this->errMsg = $this->_db->errMsg;
        }
    }

    function getSamplesByLineId($idLine) 
    {
        $this->errMsg='';
        $query = "SELECT timestamp, value FROM samples WHERE id_line='$idLine'";
        $arrayResult = $this->_db->fetchTable($query, TRUE);
        if (!$arrayResult){
            $this->errMsg = $this->_db->errMsg;
            return array();
        }
        return $arrayResult;
    }

    function getGraphLinesById($idGraph)
    {
        $this->errMsg='';
        $arrReturn=array();
        $sqliteError='';
        $query  = "SELECT l.id as id, l.name as name, l.color as color, l.line_type as line_type ";
        $query .= " FROM graph_vs_line as gl, line as l WHERE gl.id_line=l.id AND gl.id_graph='$idGraph'";

        $arrayResult = $this->_db->fetchTable($query, TRUE);
        if (!$arrayResult){
            $this->errMsg = "It was not possible to obtain information about the graph - ".$this->_db->errMsg;
            return array();
        }
        return $arrayResult;
    }

    function getGraphById($idGraph)
    {
        $this->errMsg='';
        $sqliteError='';
        $query  = "SELECT name FROM graph WHERE id='$idGraph'";

        $arrayResult = $this->_db->getFirstRowQuery($query, TRUE);
        if (!$arrayResult){
            $this->errMsg = "It was not possible to obtain information about the graph - ".$this->_db->errMsg;
            return array();
        }
        return $arrayResult;
    }

    function deleteDataBeforeThisTimestamp($timestamp)
    {
        $this->errMsg='';
        $sqliteError='';
        if(empty($timestamp)) return false;
        $query = "DELETE FROM samples WHERE timestamp<=$timestamp";
        $bExito = $this->_db->genQuery($query);
        if (!$bExito) {
        	$this->errMsg = $this->_db->errMsg;
        	return false;
        }
		return true;
    }
}
?>
