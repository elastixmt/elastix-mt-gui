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
class paloFaxMaster{
    private $_DB;
    private $errMsg;

    function paloFaxMaster($pDB)
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
    
    function getErrorMsg(){
        return $this->errMsg;
    }
    
    /**
     * Funcion que devuelve la direecion de correo a notificar en caso 
     * que algun evento ocurra con el fax. Este parametro se enceuntra en la tabla 
     * settings. Esta es un tabla clave valor. Las clave es fax_master
     */
    function getFaxMaster(){
        $query="SELECT value FROM settings WHERE property='fax_master'";
        $result=$this->_DB->getFirstRowQuery($query);
        if($result===false){
            $this->errMsg=_tr("DATABASE ERROR");
            return false;
        }elseif(count($result)==0){
            return '';
        }else
            return $result[0];
    }
    
    function setFaxMaster($email_account){
        if(!preg_match("/^[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*@[a-z0-9]+([\._\-]?[a-z0-9]+)*(\.[a-z0-9]{2,6})+$/",$email_account)){
            $this->errMsg=_tr("Invalid Email Address");
            return false;
        }
    
        $this->_DB->beginTransaction();
        $query="DELETE from settings WHERE property='fax_master'";
        if(!$this->_DB->genQuery($query)){
            $this->_DB->rollBack();
            $this->_tr("DATABASE ERROR");
            return false;
        }
        
        $query="INSERT INTO settings(property,value) VALUES(?,?)";
        if(!$this->_DB->genQuery($query,array('fax_master',$email_account))){
            $this->_DB->rollBack();
            $this->_tr("DATABASE ERROR");
            return false;
        }
        
        //realizamos el cambios en los archivos del email
        if(!$this->modificar_archivos_mail($email_account)){
            $this->_DB->rollBack();
            $this->errMsg = 'Error in mail configuration. '.$this->errMsg;
            return false;
        }else{
            $this->_DB->commit();
            return true;
        }
    }
    
    private function modificar_archivos_mail($email)
    {
        $output = $retval = NULL;
        exec('/usr/bin/elastix-helper faxconfig faxmaster '.escapeshellarg($email).' 2>&1', $output, $retval);
        if (is_array($output)) 
            $this->errMsg = implode('<br/>', $output);
        return ($retval == 0);
    }
}
?>