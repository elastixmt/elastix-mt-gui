<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-15                                               |
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
*/

define ('ELASTIX_WEBSERVICE_API_VERSION', '2.0.4');

class paloSantoAddons
{
    private $_db = NULL;
    private $_soap = NULL;
    private $_socket = NULL;
    
    private $_statusCache = NULL;
    private $_installerStatus = array(
        'name_rpm'  =>  NULL,
        'action'    =>  NULL,
    );
    private $_errMsg = "";
    
    private function _getDB()
    {
    	global $arrConf;
        
        if (is_null($this->_db)) {
            $this->_db = new paloDB($arrConf['dsn_conn_database']);
            if ($this->_db->errMsg != '') {
                $this->_errMsg = $this->_db->errMsg;
                $this->_db = NULL;            
            }
        }
        return $this->_db;
    }
    
    private function _getSOAP()
    {
        global $arrConf;
        
    	if (is_null($this->_soap)) {
            try {
                /* La presencia de xdebug activo interfiere con las excepciones de
                 * SOAP arrojadas por SoapClient, convirtiéndolas en errores 
                 * fatales. Por lo tanto se desactiva la extensión. */
                if (function_exists("xdebug_disable")) xdebug_disable(); 
            
                $this->_soap = @new SoapClient(
                    $arrConf['url_webservice'],
                    array('compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP));
            } catch (SoapFault $e) {
                $this->_errMsg = $e->getMessage();
            	$this->_soap = NULL;
            }
    	}
        return $this->_soap;
    }

    public function getSID()
    {
        if(file_exists("/etc/elastix.key")){
            $key = file_get_contents("/etc/elastix.key");
            $key = trim($key);
            return empty($key)?null:$key;
        }
        return null;
    }

    private function _getAddons($sNombre)
    {
        $this->_errMsg = '';
    	if (is_null($client = $this->_getSOAP())) return NULL;
	try {
	    if(is_null($sNombre))
		$sNombre = '';
	    $iNumAddons = $client->getNumAddonsAvailables(
		ELASTIX_WEBSERVICE_API_VERSION, 'name', $sNombre, 'all');
	    $recordset = $client->getAddonsAvailables(
		ELASTIX_WEBSERVICE_API_VERSION, $iNumAddons, 0, 'name', $sNombre, 
		$this->getSID(), 'all');

	    // Listar los RPMS instalados en el sistema
	    $listaRPMS = array();
	    foreach ($recordset as $tupla) {
		$listaRPMS[] = escapeshellarg($tupla['name_rpm']);
	    }
	    if (count($listaRPMS) > 0) {
		$output = $retval = NULL;
		exec('rpm -q --qf '.escapeshellarg(
		    'FOUND %{NAME} %{ARCH} %{EPOCH} %{VERSION} %{RELEASE}\n').
		    ' '.implode(' ', $listaRPMS), $output, $retval);
		$listaRPMS = array();
		foreach ($output as $linea) {
		    $campos = explode(' ', $linea);
		    if ($campos[0] == 'FOUND') {
			// TODO: ¿Qué ocurre si un paquete se instala en múltiples arch?
			// TODO: Webservice no devuelve información de epoch
			$listaRPMS[$campos[1]] = array(
			    'name'      =>  $campos[1],
			    'arch'      =>  $campos[2],
			    'epoch'     =>  $campos[3],
			    'version'   =>  $campos[4],
			    'release'   =>  $campos[5],
			);
		    }
		}
	    }
	    
	    // Agregar la información de instalación local a la lista de addons
	    for ($i = 0; $i < count($recordset); $i++) {
		$sPkgName = $recordset[$i]['name_rpm'];
		$recordset[$i]['can_update'] = FALSE;
		if (isset($listaRPMS[$sPkgName])) {
		    $recordset[$i]['installed_version'] = $listaRPMS[$sPkgName]['version'];
		    $recordset[$i]['installed_release'] = $listaRPMS[$sPkgName]['release'];
		    $recordset[$i]['can_update'] = ($this->_compareRpmVersion(
			array($recordset[$i]['version'], $recordset[$i]['release']), 
			array($recordset[$i]['installed_version'], $recordset[$i]['installed_release'])) > 0);
		} else {
		    $recordset[$i]['installed_version'] = NULL;
		    $recordset[$i]['installed_release'] = NULL;
		}
	    }
	    
	    return $recordset;
	} catch (SoapFault $e) {
	    $this->_errMsg = $e->getMessage();
	    return NULL;
	}
    }

    private function _filtrarAddons($filter_type, $sNombre = NULL, $sCategoria = 'all')
    {
        if (!is_null($sNombre) && trim($sNombre) == '') $sNombre = NULL;
        if (!is_null($sCategoria) && 
            !in_array($sCategoria, array('all', 'commercial', 'noncommercial')))
            $sCategoria = 'all';
	if (!in_array($filter_type, array('available','installed','purchased','update_available')))
	    $filter_type = 'available';
    	$listAddons = $this->_getAddons($sNombre);
        
        if (is_null($listAddons)) return NULL;

        $t = array();
        foreach ($listAddons as $tupla) {
            if (($sCategoria == 'commercial' && !$tupla['is_commercial']) ||
                ($sCategoria == 'noncommercial' && $tupla['is_commercial']))
                continue;
	    if ($filter_type == 'installed' && $tupla['installed_version'] == '')
		continue;
	    if ($filter_type == 'purchased' && $tupla['fecha_compra'] == 0)
		continue;
	    if ($filter_type == 'update_available' && !$tupla['can_update'])
		continue;
            $t[] = $tupla;
        }
        return $t;
    }
    
    /**
     * Método para contar los addons disponibles bajo los parámetros de filtrado
     * indicados.
     * 
     * @param   string  $sNombre    Nombre de addon a buscar, o NULL para todos
     * @param   string  $sCategoria all, commercial, noncommercial
     * 
     * @return mixed NULL en caso de error, o número de addons que cumplen filtro
     */
    function contarAddons($filter_type, $sNombre = NULL, $sCategoria = 'all')
    {
        $recordset = $this->_filtrarAddons($filter_type, $sNombre, $sCategoria);
        return is_array($recordset) ? count($recordset) : NULL;
    }

    /**
     * Método para listar los addons disponibles bajo los parámetros de filtrado
     * indicados. Con el SID local, también se lista si el addon ha sido 
     * comprado.
     *
     * @param   int     $limit        Límite máximo de registros a reportar
     * @param   int     $offset       Offset desde el cual reportar registros
     * @param   string  $filter_type  Tipo de filtro a aplicar, comprado, instalado, actualización disponible o disponibles
     * @param   string  $sNombre      Nombre de addon a buscar, o NULL para todos
     * @param   string  $sCategoria   all, commercial, noncommercial
     * @param   string  $sid          Clave de registro de /etc/elastix.key
     * 
     * @return mixed    NULL en caso de error, o lista de tuplas con el siguiente
     *                  esquema: 
     *  id                  ID numérico del addon
     *  name_rpm            Nombre del RPM que instala el addon
     *  name                Nombre corto del RPM
     *  version             Versión disponible del addon
     *  release             Release disponible del addon
     *  developed_by        Autor del addon
     *  description         Descripción larga del addon
     *  is_commercial       TRUE si es un addon licenciado y de paga
     *  url_marketplace     URL en la tienda Palosanto para comprar licencia, o NULL
     *  msg_activation      Mensaje descriptivo de la activación
     *  activated           (uso interno del webservice)
     *  location            Texto que explica dónde ubicar el addon instalado
     *  fecha_compra        09 Dec 2011 05:17:53, o 0 si no se ha comprado
     *  can_update          (local) TRUE si está instalada una versión vieja
     *  installed_version   (local) Versión instalada del addon, o NULL
     *  installed_release   (local) Release instalado del addon, o NULL
     */
    function listarAddons($filter_type, $limit, $offset = 0, $sNombre = NULL, $sCategoria = 'all')
    {
        $recordset = $this->_filtrarAddons($filter_type, $sNombre, $sCategoria);
        if (!is_array($recordset)) return NULL;
        return array_slice($recordset, $offset, $limit);
    }

    private function _compareRpmVersion($a, $b)
    {
    	if (!function_exists('_compareRpmVersion_string')) {
            function _compareRpmVersion_string($v1, $v2)
            {
            	$v1 = preg_split("/[^a-zA-Z0-9]+/", $v1);
                $v2 = preg_split("/[^a-zA-Z0-9]+/", $v2);
                while (count($v1) > 0 && count($v2) > 0) {
                	$a = array_shift($v1); $b = array_shift($v2);
                    $bADigit = ctype_digit($a); $bBDigit = ctype_digit($b);  
                    if ($bADigit && $bBDigit) {
                    	$a = (int)$a; $b = (int)$b;
                        if ($a > $b) return 1;
                        if ($a < $b) return -1;
                    } elseif ($bADigit != $bBDigit) {
                    	if ($bADigit) return 1;
                        if ($bBDigit) return -1;
                    } else {
                    	$rr = strcmp($a, $b);
                        if ($rr != 0) return $rr;
                    }
                }
                if (count($v1) > 0) return 1;
                if (count($v2) > 0) return -1;
                return 0;
            }
        }
        
        $r = _compareRpmVersion_string($a[0], $b[0]);
        if ($r != 0) return $r;
        return _compareRpmVersion_string($a[1], $b[1]);
    }
    
    private function _getUpdater()
    {
    	if (is_null($this->_socket)) {
            // Iniciar la conexión
            $errno = $errstr = NULL;
    		 
            $sUrlConexion = "tcp://localhost:20004";
            $this->_socket = @stream_socket_client($sUrlConexion, $errno, $errstr);
            if (!$this->_socket) {
            	$this->_socket = NULL;
                $this->_errMsg = "(internal) Cannot connect to updater daemon - ($errno) $errstr";
            } else {
            	// Leer el reporte de estado de operación
                $this->_statusCache = $this->_recogerStatus();
                $this->_updateInstallerStatus();
            }
    	}
        return $this->_socket;
    }

    // Función que parsea el reporte de estatus
    private function _recogerStatus()
    {
    	$estado = array(
            'status'    =>  NULL,
            'action'    =>  NULL,
            'custom'    =>  NULL,
            'package'   =>  array(),
            'installed' =>  array(),
            'errmsg'    =>  array(),
            'warnmsg'   =>  array(),
        );
        do {
    		$s = fgets($this->_socket);
            list($sKeyword, $sResto) = explode(' ', rtrim($s), 2);
            switch ($sKeyword) {
            case 'status':
            case 'action':
            case 'custom': 
                $estado[$sKeyword] = $sResto;
                break;
            case 'errmsg':
            case 'warnmsg':
                $estado[$sKeyword][] = $sResto;
                break;
            case 'package':
                $l = explode(' ', $sResto);
                $estado[$sKeyword][] = array(
                    'pkgaction'     =>  $l[0],
                    'nombre'        =>  $l[1],
                    'longitud'      =>  ctype_digit($l[2]) ? (int)$l[2] : NULL,
                    'descargado'    =>  ctype_digit($l[3]) ? (int)$l[3] : NULL,
                    'currstatus'    =>  $l[4],
                );
                break;
            case 'installed':
                $l = explode(' ', $sResto);
                $estado[$sKeyword][] = array(
                    'nombre'    =>  $l[0],
                    'arch'      =>  $l[1],
                    'epoch'     =>  $l[2],
                    'version'   =>  $l[3],
                    'release'   =>  $l[4],
                );
                break;
            }
    	} while ($s != "end status\n");
        return $estado;
    }
    
    /**
     * Procedimiento que obtiene el caché del estado de actualización
     * 
     * @return  mixed   NULL si error de conexión, o contenido del caché
     */
    function getStatusCache()
    {
    	return $this->_statusCache;
    }

    
    function getErrMsg()
    {
	return $this->_errMsg;
    }
    
    /**
     * Procedimiento que actualiza el caché del estado de actualización
     * 
     * @return  bool    VERDADERO en caso de éxito, FALSE en error
     */
    function updateStatusCache()
    {
    	$socket = $this->_getUpdater();
        if (is_null($socket)) return FALSE;
        fwrite($socket, "status\n");
        $this->_statusCache = $this->_recogerStatus();
        return TRUE;
    }
    
    private function _updateInstallerStatus()
    {
    	$socket = $this->_getUpdater();
        fwrite($socket, "getcustom\n");
        $s = fgets($socket);
        $s = substr($s, 0, strlen($s) - 1); // quitar \n
        if ($s == '') {
        	$this->_installerStatus = array(
                'name_rpm'  =>  NULL,
                'action'    =>  NULL,
            );
        } else {
        	$this->_installerStatus = unserialize(urldecode($s));
        }
    }
    
    /**
     * Procedimiento que obtiene el valor actual cacheado del estado de 
     * instalación.
     * 
     * @return  mixed   arreglo que describe el estado de la instalación
     */
    function getInstallerStatus()
    {
    	if (is_null($this->_installerStatus))
            $this->_updateInstallerStatus();
        return $this->_installerStatus;
    }
    
    /**
     * Procedimiento que actualiza el estado de la instalación en el demonio
     * 
     * @return void
     */
    function saveInstallerStatus()
    {
    	$status = $this->_installerStatus;
        $socket = $this->_getUpdater();
        $this->_installerStatus = $status;
        $s = urlencode(serialize($status));
        fwrite($socket, "setcustom $s\n");
        $r = fgets($socket);
    }

    /**
     * Procedimiento que le indica al demonio que debe iniciar una instalación o actualización
     * 
     * @param  string     $name_rpm        Nombre del rpm a instalar
     *
     * @return string  Respuesta del demonio
     */
    function installAddon($name_rpm)
    {
	$socket = $this->_getUpdater();
	fwrite($socket, "addconfirm $name_rpm\n");
	return fgets($socket);
    }

    function uninstallAddon($name_rpm)
    {
	$socket = $this->_getUpdater();
	fwrite($socket, "removeconfirm $name_rpm\n");
	return fgets($socket);
    }

    function checkDependencies($name_rpm)
    {
	$socket = $this->_getUpdater();
	fwrite($socket, "testadd $name_rpm\n");
	return fgets($socket);
    }
    
    function cancelTransaction()
    {
	$socket = $this->_getUpdater();
	fwrite($socket, "cancel\n");
	return fgets($socket);
    }

    function clearYum()
    {
	$socket = $this->_getUpdater();
	fwrite($socket, "clear\n");
	return fgets($socket);
    }

    function deleteActionTmp()
    {
	$pDB = $this->_getDB();
	$query = "DELETE FROM action_tmp";
	$result = $pDB->genQuery($query);
	if($result == FALSE){
	     $this->_errMsg = $pDB->errMsg;
	      return FALSE;
	}
	return TRUE;
    }

    /**
     * Procedimiento que almacena en la base local addons.db en la tabla action_tmp, la transacción que está en progreso
     * 
     * @param  string     $name_rpm        Nombre del rpm
     * @param  string     $action          Acción a realizar (instalación/actualización, desinstalación, chequear dependencias, 	*				      cancelar transacción) 
     * @param  string     $user        	   Usuario que realiza la transacción
     * @param  string     $status      	   Estado de la transacción (reporefresh, depsolving, downloading, applying)
     * @param  integer    $percentage      Porcentaje de progreso de la transacción
     *
     * @return bool     TRUE en caso de éxito, FALSE caso contrario
     */
    function saveActionTmp($name_rpm, $action, $user, $status="reporefresh", $percentage=0)
    {
	$pDB = $this->_getDB();
	$query = "INSERT INTO action_tmp (name_rpm,action_rpm,data_exp,user,init_time) VALUES (?,?,?,?,?)";
	$result = $pDB->genQuery($query,array($name_rpm,$action,$status,$user,$percentage));
	if($result == FALSE){
	     $this->_errMsg = $pDB->errMsg;
	      return FALSE;
	}
	return TRUE;
    }

    /**
     * Procedimiento que obtiene la transacción en progreso en caso de haberla
     * 
     * @return mixed  NULL en caso de error, caso contrario un arreglo con los datos de la tabla action_tmp
     */
    function getActionTmp()
    {
	$pDB = $this->_getDB();
	$query = "SELECT * FROM action_tmp";
	$result = $pDB->getFirstRowQuery($query,TRUE);
	if($result === FALSE){
	     $this->_errMsg = $pDB->errMsg;
	     return NULL;
	}
	return $result;
    }

    /**
     * Procedimiento que actualiza en la base local addons.db en la tabla action_tmp, la transacción que está en progreso
     * 
     * @param  string     $status      	   Estado de la transacción (reporefresh, depsolving, downloading, applying)
     * @param  integer    $percentage      Porcentaje de progreso de la transacción
     * @param  string     $action_rpm      Acción a realizar (instalación/actualización, desinstalación, chequear dependencias, 	*				      cancelar transacción)
     *
     * @return  bool     TRUE en caso de éxito, FALSE caso contrario
     */
    function updateActionTmp($status,$percentage=NULL,$action_rpm=NULL)
    {
	$pDB = $this->_getDB();
	$query = "UPDATE action_tmp SET data_exp=?";
	$arrParam = array($status);
	if(!is_null($percentage)){
	    $query .= ", init_time=?";
	    $arrParam[] = $percentage;
	}
	if(!is_null($action_rpm)){
	    $query .= ", action_rpm=?";
	    $arrParam[] = $action_rpm;
	}
	$result = $pDB->genQuery($query,$arrParam);
	if($result == FALSE){
	     $this->_errMsg = $pDB->errMsg;
	      return FALSE;
	}
	return TRUE;
    }
}
?>
