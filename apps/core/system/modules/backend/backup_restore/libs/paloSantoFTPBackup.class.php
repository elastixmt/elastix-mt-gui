<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-1                                               |
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
  $Id: paloSantoFTPBackup.class.php,v 1.1 2009-09-07 10:09:02 Eduardo Cueva ecueva@palosanto.com Exp $ */

define ('ELASTIX_BACKUP_DIR', '/var/www/backup');

class paloSantoFTPBackup {
    private $_DB;
    var $errMsg;

    function paloSantoFTPBackup(&$pDB)
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
     * Procedimiento para cargar las credenciales del servidor FTP de la base de
     * datos.
     * 
     * @return  mixed   NULL en error, o tupla de credenciales.
     */
    function obtenerCredencialesFTP()
    {
        $idServidorFTP = 1;
        $paramSQL = array($idServidorFTP);
        $tupla = $this->_DB->getFirstRowQuery(
            'SELECT server, port, user, password, pathServer FROM serverFTP WHERE id = ?',
            TRUE, $paramSQL);
        if (!is_array($tupla)) {
            $tupla = NULL;
            $this->errMsg = _tr('Failed to load FTP server credentials').' - '.$this->_DB->errMsg;
        }
        
        return $tupla;
    }

    /**
     * Procedimiento para guardar un nuevo conjunto de credenciales FTP a la 
     * base de datos.
     * 
     * @param   string  $server     Servidor FTP a contactar para las operaciones
     * @param   string  $port       Puerto FTP para contactar a servidor
     * @param   string  $user       Usuario remoto FTP
     * @param   string  $password   Contraseña del usuario remoto
     * @param   string  $path       Ruta en el servidor FTP para respaldos
     * 
     * @return  bool    VERDADERO en éxito, FALSO en error
     */
    function asignarCredencialesFTP($server, $port, $user, $password, $path)
    {
        $idServidorFTP = 1;
        $paramSQL = array($idServidorFTP);
        $tupla = $this->_DB->getFirstRowQuery(
            'SELECT COUNT(*) AS N FROM serverFTP WHERE id = ?',
            TRUE, $paramSQL);
        if (!is_array($tupla)) {
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        $sql = ($tupla['N'] > 0)
            ? 'UPDATE serverFTP SET server = ?, port = ?, user = ?, '.
                'password = ?, pathServer = ? WHERE id = ?'
            : 'INSERT INTO serverFTP (server, port, user, password, '.
                'pathServer, id) VALUES (?, ?, ?, ?, ?, ?)';
        $paramSQL = array($server, $port, $user, $password, $path, $idServidorFTP);
        $r = $this->_DB->genQuery($sql, $paramSQL);
        if (!$r) $this->errMsg = $this->_DB->errMsg;
        return $r;
    }

    // Abrir la conexión FTP con login y modo pasivo y cambio de directorio
    private function _abrirConexionFTP()
    {
    	$ftpcred = $this->obtenerCredencialesFTP();
        if (!is_array($ftpcred)) return NULL;
        if (count($ftpcred) <= 0) {
            $this->errMsg = _tr('FTP server credentials not set');
            return NULL;
        }
        
        $ftpconn = @ftp_connect($ftpcred['server'], $ftpcred['port']);
        if (!$ftpconn) {
            $this->errMsg = _tr('Error Connection');
        	return NULL;
        }
        if (!ftp_login($ftpconn, $ftpcred['user'], $ftpcred['password'])) {
            $this->errMsg = _tr('Error user_password');
            ftp_close($ftpconn);
        	return NULL;
        }
        if (!ftp_pasv($ftpconn, TRUE)) {
            $this->errMsg = _tr('Failed to set passive mode on FTP');
            ftp_close($ftpconn);
        	return NULL;
        }
        if (!ftp_chdir($ftpconn, $ftpcred['pathServer'])) {
            $this->errMsg = _tr('Failed to change remote FTP directory');
            ftp_close($ftpconn);
            return NULL;
        }
        return $ftpconn;
    }

    /**
     * Procedimiento para enviar un archivo de respaldo al servidor FTP 
     * configurado.
     * 
     * @param   string  $sNombreArchivo Nombre del archivo de respaldo
     * 
     * @return  bool    VERDADERO en éxito, FALSO en error
     */
    function enviarArchivoFTP($sNombreArchivo)
    {
    	$ftpconn = $this->_abrirConexionFTP();
        if (is_null($ftpconn)) return FALSE;
        
        $sRutaLocal = ELASTIX_BACKUP_DIR.'/'.$sNombreArchivo;
        $bExito = ftp_put($ftpconn, $sNombreArchivo, $sRutaLocal, FTP_BINARY);
        ftp_close($ftpconn);
        if (!$bExito) $this->errMsg = _tr('Problem uploading').' '.$sNombreArchivo;
        return $bExito;
    }
    
    /**
     * Procedimiento para recibir un archivo de respaldo del servidor FTP 
     * configurado.
     * 
     * @param   string  $sNombreArchivo Nombre del archivo de respaldo
     * 
     * @return  bool    VERDADERO en éxito, FALSO en error
     */
    function recibirArchivoFTP($sNombreArchivo)
    {
        $ftpconn = $this->_abrirConexionFTP();
        if (is_null($ftpconn)) return FALSE;
        
        $sRutaLocal = ELASTIX_BACKUP_DIR.'/'.$sNombreArchivo;
        $bExito = ftp_get($ftpconn, $sRutaLocal, $sNombreArchivo, FTP_BINARY);
        ftp_close($ftpconn);
        if (!$bExito) $this->errMsg = _tr('Problem downloading').' '.$sNombreArchivo;
        return $bExito;
    }

    /**
     * Procedimiento para listar los archivos de respaldo (todos los .tar) del
     * servidor FTP configurado.
     * 
     * @return  mixed   NULL en caso de error, o lista (posiblemente vacía)
     */
    function listarArchivosTarFTP()
    {
        $ftpconn = $this->_abrirConexionFTP();
        if (is_null($ftpconn)) return NULL;
        
        $contents = ftp_nlist($ftpconn, '-la .');
        ftp_close($ftpconn);
        if ($contents === FALSE) {
            $this->errMsg = _tr('Failed to list contents');
        	return NULL;
        }

        $new_list = array();
        foreach ($contents as $fileline) {
            $regs = NULL;
            /* Recolectar nombres de archivo sin espacios que contengan .tar .
             * Aparentemente esto no es necesario ya que $fileline debería tener
             * la totalidad del nombre de archivo, pero da la impresión de que
             * el código anterior se encontró con un caso en que la entrada 
             * tenía información que no era parte del nombre de archivo. */
            if (preg_match('/(\S*\.tar\S*)/', $fileline, $regs)) {
                $new_list[] = $regs[1];
            }
        }
        return $new_list;
    }

    /*HERE YOUR FUNCTIONS*/

    //obtiene lo asrchivos locales
    function obtainFiles($dir){
        $files =  glob($dir."/{*.tar}",GLOB_BRACE);
        $array = array();
        $names ="";
        foreach ($files as $ima)
            $names[]=array_pop(explode("/",$ima));
        if(!$names) return $array;
        return $names;
    }

    ///////////////////////////////

    function getStatusAutomaticBackupById()
    {
        $query = "SELECT status FROM automatic_backup WHERE id=1";

        $result=$this->_DB->getFirstRowQuery($query,true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function updateStatus($status)
    {
        $query = 'UPDATE automatic_backup SET status = ? WHERE id = 1';
        $result = $this->_DB->genQuery($query, array($status));

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function insertStatus($status)
    {
        $query = "INSERT INTO automatic_backup(status) VALUES(?);";
        $result=$this->_DB->genQuery($query, array($status));

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }
    
    function createCronFile($time)
    {
        $time = strtolower($time);
        if (!in_array($time, array('daily', 'monthly', 'weekly'))) $time = 'off';
        $sComando = '/usr/bin/elastix-helper backupengine --autobackup '.$time;
        $output = $retval = NULL;
        exec($sComando, $output, $retval);
        if ($retval != 0) {
        	$this->errMsg = _tr('Unabled write file').' - '.implode("\n", $output);
            return FALSE;
        }
        return TRUE;
    }
}
?>