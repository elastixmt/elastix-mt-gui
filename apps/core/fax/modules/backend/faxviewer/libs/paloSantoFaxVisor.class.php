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
  $Id: paloSantoFaxVisor.class.php,v 1.1.1.1 2008/12/09 18:00:00 aflores Exp $ */

/*-
CREATE TABLE fax_docs
(
    id           INTEGER  PRIMARY KEY,
    pdf_file    varchar(255)   NOT NULL DEFAULT '',
    modemdev     varchar(255)   NOT NULL DEFAULT '',
    status       varchar(255)   NOT NULL DEFAULT '',
    commID       varchar(255)   NOT NULL DEFAULT '',
    errormsg     varchar(255)   NOT NULL DEFAULT '',
    company_name varchar(255)   NOT NULL DEFAULT '',
    company_fax  varchar(255)   NOT NULL DEFAULT '',
    id_user      INTEGER NOT NULL DEFAULT 0,
    date     timestamp  NOT NULL ,
    FOREIGN KEY (id_user)   REFERENCES acl_user(id)
);
*/

class paloFaxVisor
{
    private $_db;
    var $errMsg;

    function paloFaxVisor($pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_db =& $pDB;
            $this->errMsg = $this->_db->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_db = new paloDB($dsn);

            if (!$this->_db->connStatus) {
                $this->errMsg = $this->_db->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    function obtener_faxes($idOrg, $company_name, $company_fax, $fecha_fax, $offset, $cantidad, $type)
    {
        $listaWhere = "";
        $paramSQL = array();

        if(!is_null($idOrg)){
            if(!preg_match("/^[[:digit:]]+$/","$idOrg")){
                $this->errMsg = _tr("Organization ID is not valid");
                return false;
            }
        }

        if (empty($company_name)) $company_name = NULL;
        if (empty($company_fax)) $company_fax = NULL;
        if (empty($fecha_fax)) $fecha_fax = NULL;
        if (empty($type)) $type = NULL;
        if (!is_null($fecha_fax) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fax)) {
            $this->errMsg = '(internal) Invalid date for query, expected yyyy-mm-dd';
        	return NULL;
        }
        if (!ctype_digit("$offset") || !ctype_digit("$cantidad")) {
        	$this->errMsg = '(internal) Invalid offset/limit';
            return NULL;
        }
        if (!is_null($type)) {
        	$type = strtolower($type);
            if (!in_array($type, array('in', 'out'))) $type = NULL;
        }
        
        $sPeticionSQL = 
            'SELECT f.id, f.pdf_file, f.modemdev, f.commID, f.status, f.errormsg, '.
                'f.company_name, f.company_fax, f.date, '.
                'f.type, f.faxpath, u.name destiny_name, u.fax_extension destiny_fax '.
            'FROM fax_docs f join acl_user u on f.id_user = u.id WHERE 1=1 ';

		if (!is_null($idOrg)){
            $sPeticionSQL .= " and u.id_group in (SELECT id from acl_group where id_organization=?)";
            $paramSQL[] = $idOrg;
        }
        if (!is_null($company_name)) {
        	$listaWhere .= ' and f.company_name LIKE ?';
            $paramSQL[] = "%$company_name%";
        }
        if (!is_null($company_fax)) {
            $listaWhere .= ' and f.company_fax LIKE ?';
            $paramSQL[] = "%$company_fax%";
        }
        if (!is_null($fecha_fax)) {
            $listaWhere .= ' and f.date BETWEEN ? AND ?';
            $paramSQL[] = "$fecha_fax 00:00:00";
            $paramSQL[] = "$fecha_fax 23:59:59";
        }
        if (!is_null($type)) {
        	$listaWhere .= ' and f.type = ?';
            $paramSQL[] = $type;
        }
        $sPeticionSQL .= $listaWhere.' ORDER BY f.id desc LIMIT ? OFFSET ?';
        $paramSQL[] = $cantidad; $paramSQL[] = $offset;
        
        $arrReturn = $this->_db->fetchTable($sPeticionSQL, TRUE, $paramSQL);
        if ($arrReturn == FALSE) {
            $this->errMsg = $this->_db->errMsg;
            return array();
        }
        return $arrReturn;
    }

    function obtener_cantidad_faxes($idOrg, $company_name, $company_fax, $fecha_fax, $type)
    {
        $listaWhere = "";
        $paramSQL = array();

        if(!is_null($idOrg)){
            if(!preg_match("/^[[:digit:]]+$/","$idOrg")){
                $this->errMsg = _tr("Organization ID is not valid");
                return false;
            }
        }

        if (empty($company_name)) $company_name = NULL;
        if (empty($company_fax)) $company_fax = NULL;
        if (empty($fecha_fax)) $fecha_fax = NULL;
        if (empty($type)) $type = NULL;
        if (!is_null($fecha_fax) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fax)) {
            $this->errMsg = '(internal) Invalid date for query, expected yyyy-mm-dd';
            return false;
        }
        if (!is_null($type)) {
            $type = strtolower($type);
            if (!in_array($type, array('in', 'out'))) $type = NULL;
        }

        $sPeticionSQL = 'SELECT COUNT(*) cantidad FROM fax_docs f join acl_user u on f.id_user = u.id WHERE 1=1';
       
        if (!is_null($idOrg)){
            $sPeticionSQL .= " and u.id_group in (SELECT id from acl_group where id_organization=?)";
            $paramSQL[] = $idOrg;
        }
        if (!is_null($company_name)) {
            $listaWhere .= ' and company_name LIKE ?';
            $paramSQL[] = "%$company_name%";
        }
        if (!is_null($company_fax)) {
            $listaWhere .= ' and company_fax LIKE ?';
            $paramSQL[] = "%$company_fax%";
        }
        if (!is_null($fecha_fax)) {
            $listaWhere .= ' and date BETWEEN ? AND ?';
            $paramSQL[] = "$fecha_fax 00:00:00";
            $paramSQL[] = "$fecha_fax 23:59:59";
        }
        if (!is_null($type)) {
            $listaWhere .= ' and type = ?';
            $paramSQL[] = $type;
        }

		$sPeticionSQL .= $listaWhere;
        
        $arrReturn = $this->_db->getFirstRowQuery($sPeticionSQL, TRUE, $paramSQL);

        if ($arrReturn === FALSE) {
            $this->errMsg = $this->_db->errMsg;
            return false;
        }
        return $arrReturn['cantidad'];
    }

    function updateInfoFaxFromDB($idFax, $company_name, $company_fax)
    {
        if (!$this->_db->genQuery(
            'UPDATE fax_docs SET company_name = ?, company_fax = ? WHERE id = ?',
            array($company_name, $company_fax, $idFax))) {
            $this->errMsg = $this->_db->errMsg;
            return false;
        }
        return true;
    }

    function obtener_fax($idFax){
        $arrReturn = $this->_db->getFirstRowQuery(
            'SELECT * FROM fax_docs WHERE id = ?',
            TRUE, array($idFax));
        if ($arrReturn == FALSE){
            $this->errMsg = $this->_db->errMsg;
            return false;
        }
        return $arrReturn;
	}

    function deleteInfoFax($idFax,$idOrg=null)
    {
        $this->errMsg = '';
        $bExito = TRUE;


        // Leer la información del fax
        $infoFax = $this->obtener_fax($idFax);
        if ($infoFax == false) return ($this->errMsg == '');

        // Borrar la información y el documento asociado
        $this->_db->conn->beginTransaction();
        if(!is_null($idOrg)){
            $bExito = $this->_db->genQuery(
                'DELETE from fax_docs WHERE id=? and id_user in (SELECT u.id from acl_group g join acl_user u on u.id_group=g.id where id_organization=?)',
                array($infoFax['id'],$idOrg));
        }else{
            $bExito = $this->_db->genQuery(
                'DELETE from fax_docs WHERE id=?;',
                array($infoFax['id'],$idOrg));
        }
        if (!$bExito) $this->errMsg = $this->_db->errMsg;
        if ($bExito) {
            $file = "/var/www/elastixdir/faxdocs/{$infoFax['faxpath']}/fax.pdf";
            $bExito = file_exists($file) ? unlink($file) : TRUE;
        } 
        if ($bExito)
            $this->_db->conn->commit();
        else $this->_db->conn->rollback();
        return $bExito;
    }

	function fax_bellowOrganization($idFax,$idOrg){
		$query='SELECT 1 from fax_docs WHERE id=? and id_user in (SELECT u.id from acl_group g join acl_user u on u.id_group=g.id where id_organization=?)';
		$result = $this->_db->genQuery($query,array($idFax,$idOrg));
		if($result===false){
			$this->errMsg = $this->_db->errMsg;
		}elseif(count($result)==0){
			$this->errMsg = _tr("Fax doesn't exist");
		}else{
			return true;
		}
		return false;
	}
}
?>