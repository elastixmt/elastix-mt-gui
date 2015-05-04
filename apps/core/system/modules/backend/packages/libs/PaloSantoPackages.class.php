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
require_once 'libs/paloSantoDB.class.php';

class PaloSantoPackages
{
    var $errMsg = NULL;
    var $bActualizar = FALSE;
    private $_ruta = NULL;
    private $_repodb = array();

    function __construct($ruta) { $this->_ruta = $ruta; }

    function listarPaquetes($tipo, $filtro = NULL)
    {
    	if (!is_null($filtro)) $filtro = trim($filtro);
        if (!is_null($filtro) && !preg_match('/^[\w\.-]+$/', $filtro)) $filtro = NULL;
        if (!in_array($tipo, array('installed', 'all'))) $tipo = 'installed';
        
        $this->_abrirRepoDB();
        switch ($tipo) {
        case 'installed':
            return $this->_listarPaquetesInstalados($filtro);
        case 'all':
            return $this->_listarPaquetesTotales($filtro);
        }
    }

    private function _listarPaquetesInstalados($filtro)
    {
    	$rpmlist = array();
        
        $p = $this->_abrirTuberiaRPM($filtro);
        $sql = 'SELECT version, release FROM packages WHERE name = ? AND arch = ?';
        while (!is_null($r = $this->_leerInstaladoRPM($p))) {
        	foreach ($this->_repodb as $repo => $db) {
                $paramSQL = array($r['name'], $r['arch']);
                $recordset = $db->fetchTable($sql, TRUE, $paramSQL);
                foreach ($recordset as $tuple) {
                	if (is_null($r['latestversion']) || 
                        $this->_compareRpmVersion(
                            array($tuple['version'], $tuple['release']),
                            array($r['latestversion'], $r['latestrelease'])) > 0) {

                		$r['repo'] = $repo;
                        $r['latestversion'] = $tuple['version'];
                        $r['latestrelease'] = $tuple['release'];
                	}
                }
        	}
            
            // Verificar si este paquete es actualizable
            if (!is_null($r['latestversion']) && $this->_compareRpmVersion(
                array($r['version'], $r['release']),
                array($r['latestversion'], $r['latestrelease'])) < 0)
                $r['canupdate'] = TRUE;
            
            $rpmlist[$r['name'].'.'.$r['arch']] = $r;
        }
        pclose($p);
        ksort($rpmlist);
        return $rpmlist;
    }
    
    private function _listarPaquetesTotales($filtro)
    {
        $rpmlist = array();
        
        $p = $this->_abrirTuberiaRPM($filtro);
        
        // Cargar la versión más reciente de cada RPM en los repos
        $sql = 'SELECT name, arch, version AS latestversion, release AS latestrelease, summary FROM packages';
        $paramSQL = NULL;
        if (!is_null($filtro)) {
        	$sql .= ' WHERE name LIKE ?';
            $paramSQL = array('%'.$filtro.'%');
        }
        foreach ($this->_repodb as $repo => $db) {
            $recordset = $db->fetchTable($sql, TRUE, $paramSQL);
            foreach ($recordset as $tuple) {
                $rpmkey = $tuple['name'].'.'.$tuple['arch'];
                if (!isset($rpmlist[$rpmkey])) {
                	$tuple['version'] = NULL;
                    $tuple['release'] = NULL;
                    $tuple['repo'] = $repo;
                    $tuple['canupdate'] = FALSE;
                    $rpmlist[$rpmkey] = $tuple;
                } else {
                	$curtuple =& $rpmlist[$rpmkey];
                    if ($this->_compareRpmVersion(
                        array($curtuple['latestversion'], $curtuple['latestrelease']),
                        array($tuple['latestversion'], $tuple['latestrelease'])) < 0) {
                        foreach (array('latestversion', 'latestrelease', 'summary') as $k)
                            $curtuple[$k] = $tuple[$k];
                        $curtuple['repo'] = $repo;
                    }
                    unset($curtuple);
                }
            }
        }
    	
        // Con los RPMs instalados, verificar si se puede actualizar
        while (!is_null($r = $this->_leerInstaladoRPM($p))) {
            $rpmkey = $r['name'].'.'.$r['arch'];
            if (isset($rpmlist[$rpmkey])) {
            	$curtuple =& $rpmlist[$rpmkey];
                $curtuple['version'] = $r['version'];
                $curtuple['release'] = $r['release'];
                $curtuple['summary'] = $r['summary'];

                if ($this->_compareRpmVersion(
                    array($curtuple['latestversion'], $curtuple['latestrelease']),
                    array($curtuple['version'], $curtuple['release'])) > 0) {
                    $curtuple['canupdate'] = TRUE;
                }
                unset($curtuple);
            } else {
            	$rpmlist[$rpmkey] = $r;
            }
        }
        
        pclose($p);
        ksort($rpmlist);
        return $rpmlist;
    }
    
    private function _abrirRepoDB()
    {
    	if (count($this->_repodb) > 0) return;
        
        $repos = glob($this->_ruta."/*");
        if (count($repos) <= 0) {
            //print "Faltan repos<br/>\n";
        	$this->bActualizar = TRUE;
        }
        
        // Filtrar los repos activos según /etc/yum.repos.d/*.repo
        $reposValidos = array();
        $reposInactivos = array();
        foreach (glob('/etc/yum.repos.d/*.repo') as $repospec) {
            $cur_repo = NULL;
            foreach (file($repospec) as $linea) {
                $regs = NULL;
                if (preg_match('/^\[(\S+)\]/', $linea, $regs)) {
                    $cur_repo = $regs[1];
                    if (!in_array($cur_repo, $reposValidos))
                        $reposValidos[] = $cur_repo;
                }
                if (preg_match('/\s*enabled\s*=\s*0/', $linea, $regs)) {
                        $reposInactivos[] = $cur_repo;
                }
            }
        }

        foreach ($repos as $rutarepo) if (is_dir($rutarepo)) {
            $repo = basename($rutarepo);
            if (!in_array($repo, $reposValidos)) continue;
            if (in_array($repo, $reposInactivos)) continue;
            $rutas = glob("$rutarepo/*primary*sqlite");
            if (count($rutas) > 0) {
                // Pedir actualización si los repos tienen más de 1 semana
                $st = stat($rutas[0]);
                if (time() - $st['mtime'] > 3600 * 24 * 7) {
                    //print "Repo $repo es viejo<br/>\n";
                    $this->bActualizar = TRUE;
                }

                $dsn = $cadena_dsn = "sqlite3:///".$rutas[0];
                $dbconn = new paloDB($dsn);
                if (empty($dbconn->errMsg)) $this->_repodb[$repo] = $dbconn;
            } else {
                // Alguien hizo yum clean all
                //print "No hay sqlite para repo $repo<br/>\n";
                $this->bActualizar = TRUE;
            }
        }
    }
    
    private function _abrirTuberiaRPM($filtro)
    {
    	/* Algunos paquetes tienen un resumen que contiene saltos de línea. Para
         * poder obtener el resumen completo sin truncamientos, se inserta un
         * caracter tubería deliberado al final del formato. Toda línea que NO
         * termine en un caracter tubería está truncada y debe de leerse la 
         * siguiente línea para el resto. */
        $rpmcmd = "rpm -qa --queryformat '%{NAME}|%{ARCH}|%{VERSION}|%{RELEASE}|%{SUMMARY}|\n' ";
        if (!is_null($filtro)) $rpmcmd .= escapeshellarg("*$filtro*");
        return popen($rpmcmd, 'r');
    }
    
    private function _leerInstaladoRPM($p)
    {
        $b = '';
        do {
        	// Deben haber 6 campos incluyendo el vacío al final
            $s = fgets($p); if (!$s) return NULL;
            $b .= $s;
            $campos = explode('|', $b);
        } while (count($campos) < 6);

        return array(
            'name'          =>  $campos[0],
            'arch'          =>  $campos[1],
            'version'       =>  $campos[2],
            'release'       =>  $campos[3],
            'summary'       =>  $campos[4],
            'latestversion' =>  NULL,
            'latestrelease' =>  NULL,
            'repo'          =>  '(unknown)',
            'canupdate'     =>  FALSE,
        );
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
    
    /**************************************************************************/

    function checkUpdate()
    { 
        $respuesta = $retorno = NULL;
        exec('/usr/bin/elastix-helper ryum check-update ', $respuesta, $retorno);
        $tmp = array();
        if (is_array($respuesta)) {
            foreach($respuesta as $key => $linea){
                /* Es algo no muy concreto si hay alguna manera de saber las 
                 * posibles salidas hay que cambiar esta condicion para buscar 
                 * el error */
                if (preg_match("/(\[Errno [[:digit:]]{1,}\])/",$linea,$reg))
                    return implode('', $respuesta);
                if ((!preg_match("/^Excluding/",$linea,$reg)) && 
                    (!preg_match("/^Finished/",$linea,$reg)) && 
                    (!preg_match("/^Loaded/",$linea,$reg)) &&
                    (!preg_match("/^\ /",$linea,$reg)) &&
                    (!preg_match("/^Loading/",$linea,$reg)) &&
                    ($linea!="")) {     
                    $var = explode(".",$linea);
                    $tmp[] = $var[0];
                }
            }
            if ($retorno == 1) //Error debido a los repositorios de elastix
                return _tr('ERROR').": url don't open.";
            if ($retorno == 100 || $retorno == 0) { //codigo 100 de q hay paquetes para actualizar y 0 que no hay. (ver man yum )
                return _tr('Satisfactory Update');
            } else //por si acaso se presenta algo desconocido
                return "";
        }
    }

    function installPackage($package,$val)
    {
        $respuesta = $retorno = NULL;
        if ($val == 0)
            exec('/usr/bin/elastix-helper ryum install '.escapeshellarg($package), $respuesta, $retorno);
        else
            exec('/usr/bin/elastix-helper ryum update '.escapeshellarg($package), $respuesta, $retorno);
    
        $indiceInicial = $indiceFinal = 0;
        $terminado = array();
        $paquetesIntall = false;
        $paquetesIntallDependen = false;
        $paquetesUpdateDependen = false;
        if (is_array($respuesta)) {
            foreach ($respuesta as $key => $linea) {
                if (!preg_match("/[[:space:]]{1,}/",$linea)) {
                    $paquetesIntall = false;
                    $paquetesIntallDependen = false;
                    $paquetesUpdateDependen = false;
                }
                // 1 paquetes a instalar
                if ((preg_match("/^Installing:/",$linea))||(preg_match("/^Updating:/",$linea))) {
                    $paquetesIntall = true;
                }
                //2 paquetes a instalar por dependencias
                else if(preg_match("/^Installing for dependencies:/",$linea)){
                    $paquetesIntallDependen = true;
                    $paquetesIntall = false;
                }
                //3 paquetes a actualizar por dependencias
                else if(preg_match("/^Updating for dependencies:/",$linea)){
                    $paquetesUpdateDependen = true;
                    $paquetesIntallDependen = false;
                }
                //Llenado de datos
                else if($paquetesIntall){
                    $terminado['Installing'][] = $linea;
                }
                else if($paquetesIntallDependen){
                    $terminado['Installing for dependencies'][] = $linea;
                }
                else if($paquetesUpdateDependen){
                    $terminado['Updating for dependencies'][] = $linea;
                }
                //4 fin
                else if(preg_match("/^Transaction Summary/",$linea)){
                    // Procesamiento de los datos recolectados
                    return $this->procesarDatos($terminado,$val);
                }
            }
            return _tr('ERROR'); //error
        }
    }

    private function procesarDatos($datos,$val)
    {
        $respuesta = "";
        $total = 0;
        if (isset($datos['Installing'])) {
            $total = $total + count($datos['Installing']);
            if ($val==0)  
                $respuesta .= _tr('Installing')."\n";
            else
                $respuesta .= _tr("Updating")."\n";
            for ($i=0; $i<count($datos['Installing']); $i++) {
                $linea = trim($datos['Installing'][$i]);
                if(preg_match("/^([-\+\.\:[:alnum:]]+)[[:space:]]+([-\+\.\:[:alnum:]]+)[[:space:]]+([-\+\.\:[:alnum:]]+)[[:space:]]+([-\+\.\:[:alnum:]]+)[[:space:]]+([\.[:digit:]]+[[:space:]]+[[:alpha:]]{1})/", $linea, $arrReg)) {
                    $respuesta .= ($i+1)." .- ".trim($arrReg[1])." -- ".trim($arrReg[3])."\n";
                }
            }
        }

        $respuesta .= "\n";
        if (isset($datos['Installing for dependencies'])) {
            $total = $total + count($datos['Installing for dependencies']);
            $respuesta .= _tr('Installing for dependencies')."\n";
            for ($i=0; $i<count($datos['Installing for dependencies']); $i++) {
                $linea = trim($datos['Installing for dependencies'][$i]);
                if(preg_match("/^([-\+\.\:[:alnum:]]+)[[:space:]]+([-\+\.\:[:alnum:]]+)[[:space:]]+([-\+\.\:[:alnum:]]+)[[:space:]]+([-\+\.\:[:alnum:]]+)[[:space:]]+([\.[:digit:]]+[[:space:]]+[[:alpha:]]{1})/", $linea, $arrReg)) {
                    $respuesta .= ($i+1)." .- ".trim($arrReg[1])." -- ".trim($arrReg[3])."\n";
                }
            }
        }
        $respuesta .= "\n";
        if (isset($datos['Updating for dependencies'])) {
            $total = $total + count($datos['Updating for dependencies']);
            $respuesta .= _tr('Updating for dependencies')."\n";
            for ($i=0; $i<count($datos['Updating for dependencies']); $i++) {
                $linea = trim($datos['Updating for dependencies'][$i]);
                if (preg_match("/^([-\+\.\:[:alnum:]]+)[[:space:]]+([-\+\.\:[:alnum:]]+)[[:space:]]+([-\+\.\:[:alnum:]]+)[[:space:]]+([-\+\.\:[:alnum:]]+)[[:space:]]+([\.[:digit:]]+[[:space:]]+[[:alpha:]]{1})/", $linea, $arrReg)) {
                    $respuesta .= ($i+1)." .- ".trim($arrReg[1])." -- ".trim($arrReg[3])."\n";
                }
            }
        }
        $respuesta .= _tr('Total Packages')." = $total";
        if($val==1) 
            $this->checkUpdate();

        return $respuesta;
    }

    function uninstallPackage($package)
    {
        $respuesta = $retorno = NULL;
        exec('/usr/bin/elastix-helper ryum remove '.escapeshellarg($package), $respuesta, $retorno);
        $indiceInicial = $indiceFinal = 0;
        $terminado = array();
        $paquetesUnintall = false;
        $paquetesIntallDependen = false;
        $paquetesUpdateDependen = false;
        $valor ="";
        $total=0;
        if(is_array($respuesta)) {
            $valor .= _tr("Package(s) Uninstalled").":\n\n"; 
            foreach ($respuesta as $key => $linea) {
                if (!preg_match("/[[:space:]]{1,}/",$linea)) {
                    $paquetesIntall = false;
                    $paquetesIntallDependen = false;
                    $paquetesUpdateDependen = false;
                }
                // 1 paquetes a instalar
                if (preg_match("/^Complete!/",$linea)) {
                    $paquetesUnintall = true;
                    $valor .= "\nTotal: ".$total." "._tr("Packages uninstalled");
                    $valor .= "\n\n". _tr("Completed!");
                    return $valor;
                }
                if (preg_match("/Erasing/",$linea)) {
                    $paquetesUnintall = true;
                    $rep =  preg_split("/[\s]*[ ][\s]*/", $linea);                    
                    $valor .= $rep[4]." ".$rep[3]."\n";
                    $total++;
                }
                //2 paquetes a instalar por dependencias
            }
            $valor = _tr("Error");
            return $valor;
        }
    }
}
?>