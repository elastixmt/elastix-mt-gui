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
$Id: paloSantoEmail.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ */

$elxPath="/usr/share/elastix";
include_once("$elxPath/libs/paloSantoDB.class.php");
include_once("$elxPath/libs/paloSantoConfig.class.php");
include_once("$elxPath/libs/misc.lib.php");
include_once("$elxPath/configs/email.conf.php");
include_once("$elxPath/libs/cyradm.php");


class paloEmail {

    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function paloEmail(&$pDB)
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
    * Procedimiento para obtener el listado de los dominios existentes. 
    *
    * @param int   $idOrganization    Si != NULL, indica el id de la organization del que se quiere obtener el dominio   *
    * @return array    Listado de dominios en el siguiente formato, o FALSE en caso de error:
    *  array(
    *      array(id, domain_name),
    *      ...
    *  )
    */
    function getDomains($idOrganization = NULL)
    {
        $arr_result = FALSE;
        $where="";
        $arrParams = array();
        
        if(!is_null($idOrganization)){
            if(!preg_match('/^[[:digit:]]+$/', $idOrganization)) {
                $this->errMsg = _tr("Organization ID is not valid");
                return false;
            }else{
                $where = "where id=?";
                $arrParams[] = $idOrganization;
            }    
        }
        
        $sPeticionSQL = "SELECT id, domain FROM organization $where ORDER BY domain";
        $arr_result =& $this->_DB->fetchTable($sPeticionSQL,true,$arrParams);
        if (!is_array($arr_result)) {
            $this->errMsg = $this->_DB->errMsg;
        }

        return $arr_result;
    }

    /**
    * Procedimiento saber si un dominio existe 
    *
    * @param string    $domain_name       nombre para el dominio
    * @return bool     VERDADERO si el dominio existe, FALSO caso contrario
    */
    function domainExist($domain)
    {
        $bExito = FALSE;
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $error=_tr("Invalid domain format");
            return false;
        }
        
        //el campo ya viene validado del formulario
        //verificar que no exista ya un dominio con ese nombre en la base
        $sPeticionSQL = "SELECT 1 FROM organization WHERE domain = ?";
        $arr_result =$this->_DB->fetchTable($sPeticionSQL,false,array($domain));
        if (is_array($arr_result) && count($arr_result)>0) {
            $bExito = true;
            $this->errMsg = _tr("Domain name already exists");
        }
        return $bExito;
    }


    function accountExists($account)
    {
        $query = "SELECT 1 FROM acl_user WHERE username=?";
        $result = $this->_DB->getFirstRowQuery($query,false,array($account));
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return true;
        }
        if($result[0] > 0)
            return true;
        else
            return false;
    }
    
    /**
     * Procedimiento para crear una nueva cuenta en la base de datos y en el 
     * sistema.
     * 
     * @param   string  $domain     Dominio donde crear la cuenta
     * @param   string  $username   Usuario SIN DOMINIO
     * @param   string  $password   Password inicial para la cuenta de correo
     * @param   int     $quota      Cuota inicial de la cuenta de correo
     * 
     * @return  bool    VERDADERO en Ã©xito, FALSO en error
     */
    function createAccount($domain, $username, $password, $quota)
    {
        $this->errMsg = '';
        $output = $retval = NULL;
        $sComando = '/usr/bin/elastix-helper email_account --createaccount'.
            ' --domain '.escapeshellarg($domain).
            ' --username '.escapeshellarg($username).
            ' --password '.escapeshellarg($password).
            ' --quota '.escapeshellarg($quota).
            ' 2>&1';
        exec($sComando, $output, $retval);
        if ($retval != 0) {
            foreach ($output as $s) {
                $regs = NULL;
                if (preg_match('/^ERR: (.+)$/', trim($s), $regs)) {
                    $this->errMsg = $regs[1];
                }
            }
            if ($this->errMsg == '')
                $this->errMsg = implode('<br/>', $output);
            return FALSE;
        }
        return TRUE;
    }
    
     /**
     * Procedimiento para borrar completamente una cuenta de la base de datos y
     * del sistema.
     * 
     * @param   string  $username   Usuario completo usuario@dominio.com
     * 
     * @return  bool    VERDADERO en Ã©xito, FALSO en error
     */
    function deleteAccount($username)
    {
        $this->errMsg = '';
        $output = $retval = NULL;
        $sComando = '/usr/bin/elastix-helper email_account --deleteaccount --username '.
            escapeshellarg($username).' 2>&1';
        exec($sComando, $output, $retval);
        if ($retval != 0) {
            foreach ($output as $s) {
                $regs = NULL;
                if (preg_match('/^ERR: (.+)$/', trim($s), $regs)) {
                    $this->errMsg = $regs[1];
                }
            }
            if ($this->errMsg == '')
                $this->errMsg = implode('<br/>', $output);
            return FALSE;
        }
        return TRUE;
    }


    function edit_email_account($username,$password,$quota)
    {
        global $CYRUS;
        global $arrLang;
        $bExito=TRUE;
        
        $virtual = FALSE;
        if(!$this->updateQuota($old_quota,$quota)){
            $bExito=false;
        }
        
        if(!empty($password)){
            if(!$this->setAccountPassword($username, $password))
                $bExito=false;
        }
        return $bExito;
    }
    
    /**
     * Obtener la cuota del correo del usuario indicado.
     * 
     * @param string    $username   Correo completo usuario@dominio.com
     * 
     * @return mixed    Arreglo (used,qmax) o NULL en caso de error
     */
    function getAccountQuota($username)
    {
        $this->errMsg = '';
        $regexp = '/^[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*@[a-z0-9]+([\._\-]?[a-z0-9]+)*(\.[a-z0-9]{2,6})+$/';
        if (!preg_match($regexp, $username)) {
            $this->errMsg = _tr('Username is not valid');
            return NULL;
        }

        $cyr_conn = new cyradm;
        if (!$cyr_conn->imap_login()) {
            $this->errMsg = _tr('Failed to login to IMAP');
            return NULL;
        }
        $quota = $cyr_conn->getquota('user/'.$username);
        $cyr_conn->imap_logout();
        return $quota;
    }


    //esta funcion actualiza la quota en el sistema
    function updateQuota($old_quota,$quota,$username)
    {
        $bExito=true;
        if(!preg_match('/^[[:digit:]]+$/', "$old_quota")) {
            $this->errMsg=_tr("Quota must be numeric");
            $bExito=false;
        }elseif(!preg_match('/^[[:digit:]]+$/', "$quota")){
            $this->errMsg=_tr("Quota must be numeric");
            $bExito=false;
        }

        if($old_quota!=$quota){
            $cyr_conn = new cyradm;
            $cyr_conn->imap_login();
            $bContinuar=$cyr_conn->setmbquota("user" . "/".$username, $quota);
            if (!$bContinuar){
                $this->errMsg=_tr("Quota could not be changed.")." ".$cyr_conn->getMessage();
                $bExito=FALSE;
            }
        }
        return $bExito;
    }

    /**
     * Procedimiento para actualizar la contraseÃ±a de una cuenta de correo en
     * el sistema y en la base de datos.
     * 
     * @param   string  $username   Usuario completo usuario@dominio.com
     * @param   string  $password   Password nuevo para la cuenta de correo
     * 
     * @return  bool    VERDADERO en Ã©xito, FALSO en error
     */
    function setAccountPassword($username, $password)
    {
        $this->errMsg = '';
        $output = $retval = NULL;
        $sComando = '/usr/bin/elastix-helper email_account --setaccountpassword'.
            ' --username '.escapeshellarg($username).
            ' --password '.escapeshellarg($password).
            ' 2>&1';
        exec($sComando, $output, $retval);
        if ($retval != 0) {
            foreach ($output as $s) {
                $regs = NULL;
                if (preg_match('/^ERR: (.+)$/', trim($s), $regs)) {
                    $this->errMsg = $regs[1];
                }
            }
            if ($this->errMsg == '')
                $this->errMsg = implode('<br/>', $output);
            return FALSE;
        }
        return TRUE;
    }

    function resconstruirMailBox($username)
    {
        $output = $retval = NULL;
        $configPostfix2 = isPostfixToElastix2();// in misc.lib.php
        $regularExpresion = "";
        if($configPostfix2)
            $regularExpresion = '/^[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*@[a-z0-9]+([\._\-]?[a-z0-9]+)*(\.[a-z0-9]{2,6})+$/';
        else
            $regularExpresion = '/^([a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*)$/';

        if(!is_null($username)){
            if(!preg_match($regularExpresion,$username)){
                    $this->errMsg = _tr("Username is not valid");
            }else{
                    exec('/usr/bin/elastix-helper email_account --reconstruct_mailbox  --mailbox '.escapeshellarg($username).' 2>&1', $output, $retval);
            }
        }else{
            $this->errMsg = _tr("Username can't be empty");
        }

        if ($retval != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }

        return TRUE;
    }
    
    function reloadPostfix(){
        $sComando = '/usr/bin/elastix-helper email_account --reloadPostfix 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0){
            $this->errMsg = implode('', $output);
            return false;
        }
        return true;
    }
    
    function writePostfixMain(){
        $sComando = '/usr/bin/elastix-helper email_account --writePostfixMain 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0){
            $this->errMsg = implode('', $output);
            return false;
        }
        return true;
    }
}
?>