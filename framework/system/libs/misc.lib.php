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
  $Id: misc.lib.php,v 1.3 2007/08/10 01:32:51 gcarrillo Exp $ */

global $elxPath;
$elxPath="/usr/share/elastix";

function recoger_valor($key, &$get, &$post, $default = NULL) {
    if (isset($post[$key])) return $post[$key];
    elseif (isset($get[$key])) return $get[$key];
    else return $default;
}

function obtener_muestra_actividad_cpu()
{
    if (!function_exists('_info_sistema_linea_cpu')) {
        function _info_sistema_linea_cpu($s) { return (strpos($s, 'cpu ') === 0); }
    }
    $muestra = preg_split('/\s+/', array_shift(array_filter(file('/proc/stat', FILE_IGNORE_NEW_LINES), '_info_sistema_linea_cpu')));
    array_shift($muestra);
    return $muestra;
}

function calcular_carga_cpu_intervalo($m1, $m2)
{
    if (!function_exists('_info_sistema_diff_stat')) {
        function _info_sistema_diff_stat($a, $b)
        {
            $aa = str_split($a);
            $bb = str_split($b);
            while (count($aa) < count($bb)) array_unshift($aa, '0');
            while (count($aa) > count($bb)) array_unshift($bb, '0');
            while (count($aa) > 0 && $aa[0] == $bb[0]) {
                array_shift($aa);
                array_shift($bb);
            }
            if (count($aa) <= 0) return 0;
            $a = implode('', $aa); $b = implode('', $bb);
            return (int)$b - (int)$a;
        }
    }
    $diffmuestra = array_map('_info_sistema_diff_stat', $m1, $m2);
    $cpuActivo = $diffmuestra[0] + $diffmuestra[1] + $diffmuestra[2] + $diffmuestra[4] + $diffmuestra[5] + $diffmuestra[6];
    $cpuTotal = $cpuActivo + $diffmuestra[3];
    return ($cpuTotal > 0) ? $cpuActivo / $cpuTotal : 0;
}

function obtener_info_de_sistema()
{
    $muestracpu = array();
    $muestracpu[0] = obtener_muestra_actividad_cpu();

    $arrInfo=array(
        'MemTotal'      =>  0,
        'MemFree'       =>  0,
        'MemBuffers'    =>  0,
        'SwapTotal'     =>  0,
        'SwapFree'      =>  0,
        'Cached'        =>  0,
        'CpuModel'      =>  '(unknown)',
        'CpuVendor'     =>  '(unknown)',
        'CpuMHz'        =>  0.0,
    );
    $arrExec=array();
    $arrParticiones=array();
    $varExec="";

    if($fh=fopen("/proc/meminfo", "r")) {
        while($linea=fgets($fh, "4048")) {
            // Aqui parseo algunos parametros
            if(preg_match("/^MemTotal:[[:space:]]+([[:digit:]]+) kB/", $linea, $arrReg)) {
                $arrInfo["MemTotal"]=trim($arrReg[1]);
            }
            if(preg_match("/^MemFree:[[:space:]]+([[:digit:]]+) kB/", $linea, $arrReg)) {
                $arrInfo["MemFree"]=trim($arrReg[1]);
            }
            if(preg_match("/^Buffers:[[:space:]]+([[:digit:]]+) kB/", $linea, $arrReg)) {
                $arrInfo["MemBuffers"]=trim($arrReg[1]);
            }
            if(preg_match("/^SwapTotal:[[:space:]]+([[:digit:]]+) kB/", $linea, $arrReg)) {
                $arrInfo["SwapTotal"]=trim($arrReg[1]);
            }
            if(preg_match("/^SwapFree:[[:space:]]+([[:digit:]]+) kB/", $linea, $arrReg)) {
                $arrInfo["SwapFree"]=trim($arrReg[1]);
            }
            if(preg_match("/^Cached:[[:space:]]+([[:digit:]]+) kB/", $linea, $arrReg)) {
                $arrInfo["Cached"]=trim($arrReg[1]);
            }
        }
        fclose($fh);
    }

    if($fh=fopen("/proc/cpuinfo", "r")) {
        while($linea=fgets($fh, "4048")) {
            // Aqui parseo algunos parametros
            if(preg_match("/^model name[[:space:]]+:[[:space:]]+(.*)$/", $linea, $arrReg)) {
                $arrInfo["CpuModel"]=trim($arrReg[1]);
            }
            if (preg_match("/^Processor[[:space:]]+:[[:space:]]+(.*)$/", $linea, $arrReg)) {
                $arrInfo["CpuModel"]=trim($arrReg[1]);
            }
            if(preg_match("/^vendor_id[[:space:]]+:[[:space:]]+(.*)$/", $linea, $arrReg)) {
                $arrInfo["CpuVendor"]=trim($arrReg[1]);
            }
            if(preg_match("/^cpu MHz[[:space:]]+:[[:space:]]+(.*)$/", $linea, $arrReg)) {
                $arrInfo["CpuMHz"]=trim($arrReg[1]);
            }
        }
        fclose($fh);
    }

    exec("/usr/bin/uptime", $arrExec, $varExec);

    if($varExec=="0") {
        if(preg_match("/up[[:space:]]+([[:digit:]]+ days?,)?(([[:space:]]*[[:digit:]]{1,2}:[[:digit:]]{1,2}),?)?([[:space:]]*[[:digit:]]+ min)?/",
                $arrExec[0],$arrReg)) {
            if(!empty($arrReg[3]) and empty($arrReg[4])) {
                list($uptime_horas, $uptime_minutos) = explode(":", $arrReg[3]);
                $arrInfo["SysUptime"]=$arrReg[1] . " $uptime_horas hour(s), $uptime_minutos minute(s)";
            } else if (empty($arrReg[3]) and !empty($arrReg[4])) {
                // Esto lo dejo asi
                $arrInfo["SysUptime"]=$arrReg[1].$arrReg[3].$arrReg[4];
            } else {
                $arrInfo["SysUptime"]=$arrReg[1].$arrReg[3].$arrReg[4];
            }
        }
    }


    // Infomacion de particiones
    //- TODO: Aun no se soportan lineas quebradas como la siguiente:
    //-       /respaldos/INSTALADORES/fedora-1/disco1.iso
    //-                              644864    644864         0 100% /mnt/fc1/disc1

    exec("/bin/df -P /etc/fstab", $arrExec, $varExec);

    if($varExec=="0") {
        foreach($arrExec as $lineaParticion) {
            if(preg_match("/^([\/-_\.[:alnum:]|-]+)[[:space:]]+([[:digit:]]+)[[:space:]]+([[:digit:]]+)[[:space:]]+([[:digit:]]+)" .
                    "[[:space:]]+([[:digit:]]{1,3}%)[[:space:]]+([\/-_\.[:alnum:]]+)$/", $lineaParticion, $arrReg)) {
                $arrTmp="";
                $arrTmp["fichero"]=$arrReg[1];
                $arrTmp["num_bloques_total"]=$arrReg[2];
                $arrTmp["num_bloques_usados"]=$arrReg[3];
                $arrTmp["num_bloques_disponibles"]=$arrReg[4];
                $arrTmp["uso_porcentaje"]=$arrReg[5];
                $arrTmp["punto_montaje"]=$arrReg[6];
                $arrInfo["particiones"][]=$arrTmp;
            }
        }
    }

    usleep(250000);
    $muestracpu[1] = obtener_muestra_actividad_cpu();
    $arrInfo['CpuUsage'] = calcular_carga_cpu_intervalo($muestracpu[0], $muestracpu[1]);

    return $arrInfo;
}

/**
 * Procedimiento para construir una cadena de parámetros GET a partir de un 
 * arreglo asociativo de variables. Opcionalmente se puede indicar un conjunto
 * de variables a excluir de la construcción. Si se ejecuta en contexto web y
 * se dispone del superglobal $_GET, sus variables se agregan también a la 
 * cadena, a menos que el nombre de la variable GET conste también en la lista
 * de variables indicada explícitamente.
 *
 * @param   array   $arrVars    Lista de variables a incluir en cadena URL
 * @param   array   $arrExcluir Lista de variables a excluir de cadena URL
 *
 * @return  string  Cadena URL con signo de interrogación enfrente, si hubo al
 *                  menos una variable a convertir, o cadena vacía si no hay
 *                  variable alguna a convertir
 */
function construirURL($arrVars=array(), $arrExcluir=array())
{
    $listaVars = array();   // Lista de variables inicial

    // Variables GET, si existen
    if (isset($_GET) && is_array($_GET))
        $listaVars = array_merge($listaVars, $_GET);

    // Variables explícitas, si existen
    if (is_array($arrVars))
        $listaVars = array_merge($listaVars, $arrVars);

    // Quitar variables excluídas
    foreach ($arrExcluir as $k) unset($listaVars[$k]);
    if (count($listaVars) <= 0) return '';

    $keyval = array();
    foreach ($listaVars as $k => $v) {
        $keyval[] = urlencode($k).'='.urlencode($v);
    }
    return '?'.implode('&amp;', $keyval);    
}

// Translate a date in format 9 Dec 2006
function translateDate($dateOrig)
{
    if(preg_match("/([[:digit:]]{1,2})[[:space:]]+([[:alnum:]]{3})[[:space:]]+([[:digit:]]{4})/", $dateOrig, $arrReg)) {
        if($arrReg[2]=="Jan")      $numMonth = "01";
        else if($arrReg[2]=="Feb") $numMonth = "02";
        else if($arrReg[2]=="Mar") $numMonth = "03";
        else if($arrReg[2]=="Apr") $numMonth = "04";
        else if($arrReg[2]=="May") $numMonth = "05";
        else if($arrReg[2]=="Jun") $numMonth = "06";
        else if($arrReg[2]=="Jul") $numMonth = "07";
        else if($arrReg[2]=="Aug") $numMonth = "08";
        else if($arrReg[2]=="Sep") $numMonth = "09";
        else if($arrReg[2]=="Oct") $numMonth = "10";
        else if($arrReg[2]=="Nov") $numMonth = "11";
        else if($arrReg[2]=="Dec") $numMonth = "12";
        return $arrReg[3] . "-" . $numMonth . "-" . $arrReg[1]; 
    } else {
        return false;
    }
}

function get_key_settings($pDB,$key)
{
    $r = $pDB->getFirstRowQuery(
        'SELECT value FROM settings WHERE property = ?',
        FALSE, array($key));
    return ($r && count($r) > 0) ? $r[0] : '';
}

function set_key_settings($pDB,$key,$value)
{
    // Verificar si existe el valor de configuración
    $r = $pDB->getFirstRowQuery(
        'SELECT COUNT(*) FROM settings WHERE property = ?',
        FALSE, array($key));
    if (!$r) return FALSE;
    $r = $pDB->genQuery(
        (($r[0] > 0) 
            ? 'UPDATE settings SET value = ? WHERE property = ?' 
            : 'INSERT INTO settings (value, property) VALUES (?, ?)'),
        array($value, $key));
    return $r ? TRUE : FALSE;    
}

function load_version_elastix()
{
    global $elxPath;
    require_once "$elxPath/configs/default.conf.php";
    include_once "$elxPath/libs/paloSantoDB.class.php";
    global $arrConf;
    //conectarse a la base de settings para obtener la version y release del sistema elastix
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
    if(empty($pDB->errMsg)) {
        $theme=get_key_settings($pDB,'elastix_version_release');
    }
//si no se encuentra setear solo ?
    if (empty($theme)){
        set_key_settings($pDB,'elastix_version_release','?');
        return "?";
    }
    else return $theme;
}

function load_theme()
{
    global $elxPath;
    require_once "$elxPath/configs/default.conf.php";
    include_once "$elxPath/libs/paloSantoDB.class.php";
    global $arrConf;
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $theme=null;
    if(empty($pDB->errMsg)) {
        if($user==""){
            $theme=getOrganizationProp(1,'theme',$pDB);
        }else{
            $theme=getUserProp($user,'theme',$pDB);
        }
    }

    if (!preg_match('/^\w+$/', $theme)) $theme = false;
    if ($theme !== false && !is_dir($arrConf['basePath']."/web/themes/$theme")) $theme = false;

    //si no se encuentra setear el tema por default
    if (empty($theme) || $theme==false){
        if($user!=""){
            setUserProp($user,'theme',"tenant","system",$pDB);}
        return "tenant";
    }else{
        return $theme;
    }
}

function load_theme_fui()
{
    return "elastix3";
}

function update_theme()
{
    //actualizo el tema personalizado del usuario
    global $arrConf;
    $arrConf['mainTheme'] = load_theme();
    
    //Update menus elastix permission.
    if(isset($_SESSION['elastix_user_permission']))
        unset($_SESSION['elastix_user_permission']);
}

function getUserProp($username,$key,&$pdB){
    $bQuery = "select value from user_properties where id_user=(Select id from acl_user where username=?) and property=?";
    $bResult=$pdB->getFirstRowQuery($bQuery,false, array($username,$key));
    if($bResult==false){
        return false;
    }else{
        return $bResult[0];
    }
}

function getOrganizationProp($id,$key,&$pDB){
    $bQuery = "select value from organization_properties where id_organization=? and property=?";
    $bResult=$pDB->getFirstRowQuery($bQuery,false, array($id,$key));
    if($bResult==false){
        return false;
    }else{
        return $bResult[0];
    }
}

function setUserProp($username,$key,$value,$category="",&$pDB){
    $query="INSERT INTO user_properties values ((Select id from acl_user where username=?),?,?,?)";
    $arrParams=array($username,$key,$value,$category);
    $result=$pDB->genQuery($query, $arrParams);
    if($result==false){
        return false;
    }else
        return true;
}

function load_language()
{
    global $elxPath;
    $lang = get_language();

    include_once "$elxPath/lang/en.lang";
    $lang_file = "$elxPath/lang/$lang.lang";

    if ($lang != 'en' && file_exists("$lang_file")) {
        $arrLangEN = $arrLang;
        include_once "$lang_file";
        $arrLang = array_merge($arrLangEN, $arrLang);
    }
}

function load_language_module($module_id)
{
    global $elxPath;
    global $arrLangModule;
    $lang = get_language();
    include_once "$elxPath/apps/$module_id/lang/en.lang";
    $lang_file_module = "$elxPath/apps/$module_id/lang/$lang.lang";
    if ($lang != 'en' && file_exists("$lang_file_module")) {
        $arrLangEN = $arrLangModule;
        include_once "$lang_file_module";
        $arrLangModule = array_merge($arrLangEN, $arrLangModule);
    }

    global $arrLang;
    global $arrLangModule;
    $arrLang = array_merge($arrLang,$arrLangModule);
}

function _tr($s)
{
    global $arrLang;
    return isset($arrLang[$s]) ? $arrLang[$s] : $s;
}

function get_language()
{
    global $elxPath;
    require_once "$elxPath/configs/default.conf.php";
    include "$elxPath/configs/languages.conf.php";
    include_once "$elxPath/libs/paloSantoOrganization.class.php";

    global $arrConf;
    $lang="";

	$pdB = new paloDB($arrConf['elastix_dsn']['elastix']);
    $pACL = new paloACL($pdB);
	$pOrgz =new paloSantoOrganization($pdB);
	$user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $uid = $pACL->getIdUser($user);

	if(empty($pDB->errMsg)) {
        if($uid===false){
			$lang=$pOrgz->getOrganizationProp(1,'language');
		}else{
			$lang=$pACL->getUserProp($uid,'language');
		}
    }

    //si no se encuentra tomar del archivo de configuracion
    if (empty($lang) || $lang===false) $lang=isset($arrConf['language'])?$arrConf['language']:"en";

    //verificar que exista en el arreglo de idiomas, sino por defecto en
    if (!array_key_exists($lang,$languages)) $lang="en";
    return $lang;
}


#funciones para menu


/**
* Genera la lista de opciones para el tag SELECT_INPUT
* @generic
*/
function combo($arreglo_valores, $selected) {
    $cadena = '';
    if(!is_array($arreglo_valores) or empty($arreglo_valores)) return '';

    foreach($arreglo_valores as $key => $value) if ($selected == $key)
        $cadena .= "<option value='$key' selected>$value</option>\n"; else $cadena .= "<option value='$key'>$value</option>\n";
    return $cadena;
}

/**
* Funcion que sirve para obtener informacion de un checkbox si esta o no seteado.
* Habia un problema q cunado un checkbox no era seleccionado, este no devolvia nada por POST
* Esta funcion garantiza que siempre q defina un checkbox voy a tener un 'false' si no esta
* seteado y un 'true' si lo esta.
*
* Ejemplo: $html = checkbox("chk_01",'on','off'); //define un checkbox y esta seteado.
           $smarty("eje",$html); //lo paso a las plantilla.
           ......... por POST lo recibo ......
*          $check = $_POST['chk_01'] //recibo 'on' or 'off' segun el caso de q este seteado o  no.
*/
function checkbox($id_name, $checked='off', $disable='off')
{
    $check = $disab = "";
    $id_name_fixed  = str_replace("-","_",$id_name);

    if(!($checked=='off'))
        $check = "checked=\"checked\"";
    if(!($disable=='off'))
        $disab = "disabled=\"disabled\"";

    $checkbox  = "<input type=\"checkbox\" name=\"chkold{$id_name}\" $check $disab onclick=\"javascript:{$id_name_fixed}check();\" /> 
                  <input type=\"hidden\"   name=\"{$id_name}\" id=\"{$id_name}\"   value=\"{$checked}\" />
                  <script type=\"text/javascript\">
                    function {$id_name_fixed}check(){
                        var node = document.getElementById('$id_name');
                        if(node.value == 'on')
                            node.value = 'off';
                        else node.value = 'on';
                    }
                  </script>";
    return $checkbox;
}

/**
* Funcion que sirve para obtener los valores de los parametros de los campos en los
* formularios, Esta funcion verifiva si el parametro viene por POST y si no lo encuentra
* trata de buscar por GET para poder retornar algun valor, si el parametro ha consultar no
* no esta en request retorna null.
*
* Ejemplo: $nombre = getParameter('nombre');
*/
function getParameter($parameter)
{
    $name_delete_filters = null;
    if(isset($_POST['name_delete_filters']) && !empty($_POST['name_delete_filters']))
        $name_delete_filters = $_POST['name_delete_filters'];
    else if(isset($_GET['name_delete_filters']) && !empty($_GET['name_delete_filters']))
        $name_delete_filters = $_GET['name_delete_filters'];

    if($name_delete_filters){
        $arrFilters = explode(",",$name_delete_filters);
        if(in_array($parameter,$arrFilters))
            return null;
    }
    if(isset($_POST[$parameter]))
        return $_POST[$parameter];
    else if(isset($_GET[$parameter]))
        return $_GET[$parameter];
    else
        return null;
}

/**
 * Función para obtener la clave del Cyrus Admin de Elastix.
 * La clave es obtenida de /etc/elastix.conf
 *
 * @param   string  $ruta_base          Ruta base para inclusión de librerías
 *
 * @return  mixed   NULL si no se reconoce usuario, o la clave en plaintext
 */
function obtenerClaveCyrusAdmin()
{
    global $elxPath;
    require_once "$elxPath/libs/paloSantoConfig.class.php";

	$pConfig = new paloConfig("/etc", "elastix.conf", "=", "[[:space:]]*=[[:space:]]*");
	$listaParam = $pConfig->leer_configuracion(FALSE);
	if (isset($listaParam['cyrususerpwd'])) 
		return $listaParam['cyrususerpwd']['valor'];
	else return 'palosanto'; // Compatibility for updates where /etc/elastix.conf is not available
}

/**
 * Función para obtener la clave MySQL de usuarios bien conocidos de Elastix.
 * Los usuarios conocidos hasta ahora son 'root' (sacada de /etc/elastix.conf)
 * y 'asteriskuser' (sacada de /etc/amportal.conf)
 *
 * @param   string  $sNombreUsuario     Nombre de usuario para interrogar
 * @param   string  $ruta_base          Ruta base para inclusión de librerías
 *
 * @return  mixed   NULL si no se reconoce usuario, o la clave en plaintext
 */
function obtenerClaveConocidaMySQL($sNombreUsuario)
{
    global $elxPath;
    require_once "$elxPath/libs/paloSantoConfig.class.php";
    switch ($sNombreUsuario) {
    case 'root':
        $pConfig = new paloConfig("/etc", "elastix.conf", "=", "[[:space:]]*=[[:space:]]*");
        $listaParam = $pConfig->leer_configuracion(FALSE);
        if (isset($listaParam['mysqlrootpwd'])) 
            return $listaParam['mysqlrootpwd']['valor'];
        else return 'eLaStIx.2oo7'; // Compatibility for updates where /etc/elastix.conf is not available
        break;
    case 'asteriskuser':
        $pConfig = new paloConfig("/var/www/elastixdir/asteriskconf", "elastix_pbx.conf", "=", "[[:space:]]*=[[:space:]]*");
        $listaParam = $pConfig->leer_configuracion(FALSE);
        if (isset($listaParam['DBPASSWORD']))
            return $listaParam['DBPASSWORD']['valor'];
        break;
    }
    return NULL;
};

/**
 * Función para obtener la clave AMI del usuario admin, obtenida del archivo /etc/elastix.conf
 *
 * @param   string  $ruta_base          Ruta base para inclusión de librerías
 *
 * @return  string   clave en plaintext de AMI del usuario admin
 */

function obtenerClaveAMIAdmin()
{
    global $elxPath;
    require_once "$elxPath/libs/paloSantoConfig.class.php";
    $pConfig = new paloConfig("/etc", "elastix.conf", "=", "[[:space:]]*=[[:space:]]*");
    $listaParam = $pConfig->leer_configuracion(FALSE);
    if(isset($listaParam["amiadminpwd"]))
        return $listaParam["amiadminpwd"]['valor'];
    else
        return "elastix456";
}

/**
 * Función para construir un DSN para conectarse a varias bases de datos 
 * frecuentemente utilizadas en Elastix. Para cada base de datos reconocida, se
 * busca la clave en /etc/elastix.conf o en /etc/amportal.conf según corresponda.
 *
 * @param   string  $sNombreUsuario     Nombre de usuario para interrogar
 * @param   string  $sNombreDB          Nombre de base de datos para DNS
 *
 * @return  mixed   NULL si no se reconoce usuario, o el DNS con clave resuelta
 */
function generarDSNSistema($sNombreUsuario, $sNombreDB)
{
    global $elxPath;
    require_once "$elxPath/libs/paloSantoConfig.class.php";
    switch ($sNombreUsuario) {
    case 'root':
        $sClave = obtenerClaveConocidaMySQL($sNombreUsuario);
        if (is_null($sClave)) return NULL;
        return 'mysql://root:'.$sClave.'@localhost/'.$sNombreDB;
    case 'asteriskuser':
        $sClave = obtenerClaveConocidaMySQL($sNombreUsuario);
        if (is_null($sClave)) 
            return NULL;
        else{
            return "mysql://asteriskuser:".$sClave.'@localhost/'.$sNombreDB;
        }
    }
    return NULL;
}

function isPostfixToElastix2(){
    $pathImap    = "/etc/imapd.conf";
    $vitualDomain = "virtdomains: yes";
    $band = TRUE;
    $handle = fopen($pathImap, "r");
    $contents = fread($handle, filesize($pathImap));
    fclose($handle);
    if(strstr($contents,$vitualDomain)){
        $band = TRUE; // if the conf postfix is for Elastix 2.0
    }
    else{
        $band = FALSE;// if the conf postfix is for Elastix 1.6
    } 
    return $band;
}

// Esta función revisa las bases de datos del framework (elastix.db, register.db, samples.db) en caso de que no existan y se encuentre su equivalente pero con extensión .rpmsave entonces se las renombra.
// Esto se lo hace exclusivamente debido a la migración de las bases de datos .db del framework a archivos .sql ya que el último rpm generado que contenía las bases como .db las renombra a .rpmsave
function checkFrameworkDatabases($dbdir)
{
    $arrFrameWorkDatabases = array("register.db","samples.db");
    foreach($arrFrameWorkDatabases as $database){
        if(!file_exists("$dbdir/$database") || filesize("$dbdir/$database")==0){
            if(file_exists("$dbdir/$database.rpmsave"))
                 rename("$dbdir/$database.rpmsave","$dbdir/$database");
        }
    }
}

function writeLOG($logFILE, $log)
{
    $logPATH = "/var/log/elastix"; 
    $path_of_file = "$logPATH/".$logFILE;

    $fp = fopen($path_of_file, 'a+');
    if ($fp) {
        fwrite($fp,date("[M d H:i:s]")." $log\n");
        fclose($fp);
    }
    else
        echo "The file $logFILE couldn't be opened";
}

function getMenuColorByMenu($pdbACL, $uid)
{
    $sql = <<<SQL_PROFILE_MENUCOLOR
SELECT value FROM user_properties WHERE id_user = ? AND property = ?
SQL_PROFILE_MENUCOLOR;
    $tupla = $pdbACL->getFirstRowQuery($sql, FALSE, array($uid, 'menuColor'));
    return (is_array($tupla) && count($tupla) > 0) ? $tupla[0] : '#454545';
}

/**
 * Procedimiento que almacena el item de menú como parte del historial de 
 * navegación del usuario indicado por $uid. El historial del usuario debe 
 * cumplir las siguientes propiedades:
 * - El historial es una lista con un máximo número de items (5), parecido, pero
 *   no idéntico, a una cola FIFO.
 * - Los items están ordenados por su ID de inserción. El item más reciente es
 *   el item de mayor número de inserción.
 * - Repetidas llamadas sucesivas a esta función con el mismo valor de $uid y 
 *   $menu deben dejar la lista inalterada, asumiendo que no hayan otras 
 *   ventanas de navegación abierta.
 * - Si la lista tiene su número máximo de items y se agrega un nuevo item que
 *   no estaba previamente presente en la lista, el item más antiguo se olvida.
 * - Si el item resulta idéntico en menú a uno que ya existe, debe de quitarse
 *   de su posición actual y colocarse en la parte superior de la lista. El 
 *   número de items debe quedar inalterado.
 * 
 * @param   object  $pdbACL     Objeto paloDB que contiene la coneccion a la base elxpbx.
 * @param   integer $uid        ID de usuario para el historial
 * @param   string  $id_resource Item de menú a insertar en el historial
 * 
 * @return  bool    VERDADERO si se inserta el item, FALSO en error.  
 */
function putMenuAsHistory($pdbACL, $uid, $id_resource)
{
    global $arrConf;
    
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
    if (empty($pDB->errMsg)) {
        $uelastix = get_key_settings($pDB, 'uelastix');
        if ((int)$uelastix != 0) return TRUE;
    }

    // Leer historial actual. El item 0 es el más reciente
    $sqlselect = <<<SQL_LEER_HISTORIAL
SELECT aus.id AS id, ar.id AS id_menu FROM user_shortcut aus, acl_resource ar
WHERE id_user = ? AND aus.type = 'history' AND ar.id = aus.id_resource
ORDER BY aus.id DESC
SQL_LEER_HISTORIAL;
    $historial = $pdbACL->fetchTable($sqlselect, TRUE, array($uid));
    if (!is_array($historial)) return FALSE;
    if (count($historial) > 0 && $historial[0]['id_menu'] == $id_resource)
        return TRUE;    // Idempotencia
    for ($i = 0; $i < count($historial); $i++) $historial[$i]['modified'] = FALSE;
        
    // Procesar la lista según las reglas requeridas
    $shiftindex = NULL;
    for ($i = 0; $i < count($historial); $i++) {
        if ($historial[$i]['id_menu'] == $id_resource) {
            $shiftindex = $i;
            break;
        }
    }
    if (is_null($shiftindex) && count($historial) >= 5)
        $shiftindex = count($historial);
    
    // Insertar nuevo item al inicio, corriendo los items si es necesario
    if (!is_null($shiftindex)) {
        for ($i = $shiftindex; $i > 0; $i--) if ($i < count($historial)) {
            $historial[$i]['id_menu'] = $historial[$i - 1]['id_menu'];
            $historial[$i]['modified'] = TRUE;
        }
        $historial[0]['id_menu'] = $id_resource;
        $historial[0]['modified'] = TRUE;
    } else array_unshift($historial, array('id' => NULL, 'id_menu' => $id_resource, 'modified' => TRUE));
    
    // Guardar en la DB todas las modificaciones
    $pdbACL->beginTransaction();
    foreach ($historial as $item) if ($item['modified']) {
        if (is_null($item['id'])) {
            $sqlupdate = 'INSERT INTO user_shortcut(id_resource, id_user, type) VALUES(?, ?, ?)';
            $paramsql = array($item['id_menu'], $uid, 'history');
        } else {
            $sqlupdate = 'UPDATE user_shortcut SET id_resource = ? WHERE id_user = ? AND type = ? AND id = ?';
            $paramsql = array($item['id_menu'], $uid, 'history', $item['id']);
        }
        if (!$pdbACL->genQuery($sqlupdate, $paramsql)) {
            $pdbACL->rollBack();
            return FALSE;
        }
    }
    $pdbACL->commit();
    return TRUE;
}

function menuIsBookmark($pdbACL, $uid, $menu)
{
    $tupla = $pdbACL->getFirstRowQuery(
        'SELECT COUNT(id) FROM user_shortcut WHERE id_user = ? AND id_resource = ? AND type = ?',
        FALSE, array($uid, $menu, 'bookmark'));
    return (is_array($tupla) && ($tupla[0] > 0));
}

function getStatusNeoTabToggle($pdbACL, $uid)
{
    $tupla = $pdbACL->getFirstRowQuery(
        "SELECT description FROM user_shortcut WHERE id_user = ? AND type = 'NeoToggleTab'",
        TRUE, array($uid));
    return (is_array($tupla) && count($tupla) > 0) ? $tupla['description'] : 'none';
}

/**
 * Funcion que se encarga obtener un sticky note.
 *
 * @return array con la informacion como mensaje y estado de resultado
 * @param string $menu nombre del menu al cual se le va a agregar la nota
 *
 * @author Eduardo Cueva
 * @author ecueva@palosanto.com
 */
function getStickyNote($pdbACL, $uid, $menu)
{
    $arrResult = array(
        'status'    =>  FALSE,
        'msg'       =>  'no_data',
        'data'      =>  _tr("Click here to leave a note."),
    );
    $tupla = $pdbACL->getFirstRowQuery(
        'SELECT * FROM sticky_note WHERE id_user = ? AND id_resource = ?',
        TRUE, array($uid, $menu));
    if (is_array($tupla) && count($tupla) > 0) {
        $arrResult = array(
            'status'    =>  TRUE,
            'msg'       =>  '',
            'data'      =>  $tupla['description'],
            'popup'     =>  $tupla['auto_popup'],
        );
    }

    return $arrResult;
}

// Set default timezone from /etc/sysconfig/clock for PHP 5.3+ compatibility
function load_default_timezone()
{
    $sDefaultTimezone = @date_default_timezone_get();
    if ($sDefaultTimezone == 'UTC') {
        $sDefaultTimezone = 'America/New_York';
        if (file_exists('/etc/sysconfig/clock')) {
            foreach (file('/etc/sysconfig/clock') as $s) {
                $regs = NULL;
                if (preg_match('/^ZONE\s*=\s*"(.+)"/', $s, $regs)) {
                    $sDefaultTimezone = $regs[1];
                }
            }
        }
    }
    date_default_timezone_set($sDefaultTimezone);
}

//funcion que crea una conexion a asterisk manager
function AsteriskManagerConnect(&$error) {
    global $elxPath;
	require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
	require_once "$elxPath/libs/paloSantoConfig.class.php";

	$pConfig = new paloConfig("/var/www/elastixdir/asteriskconf", "/elastix_pbx.conf", "=", "[[:space:]]*=[[:space:]]*");
	$arrConfig = $pConfig->leer_configuracion(false);

	$password = $arrConfig['MGPASSWORD']['valor'];
	$host = $arrConfig['DBHOST']['valor'];
	$user = $arrConfig['MGUSER']['valor'];
	$astman = new AGI_AsteriskManager();

	if (!$astman->connect("$host", "$user" , "$password")) {
		$error = _tr("Error when connecting to Asterisk Manager");
	} else{
		return $astman;
	}
	return false;
}


/**
    funcion que sirve para obtener las credenciales de un usuario
    @return
    Array => ( idUser => (idUser or ""),
               id_organization => (ID_ORG or false),
               userlevel => (superadmin,organization),
               domain => (dominio de la ORG or false)
             )
*/
function getUserCredentials($username){
    global $arrConf,$elxPath;
    require_once("$elxPath/libs/paloSantoACL.class.php");
    $pdbACL = new paloDB($arrConf['elastix_dsn']['elastix']);
    $pACL = new paloACL($pdbACL);

    $userLevel1 = "other";
    $idOrganization = $domain = false;
    $idUser = $pACL->getIdUser($username);
    if($idUser!=false){
        $idOrganization = $pACL->getIdOrganizationUser($idUser);
        if($idOrganization!=false){
            if($pACL->isUserSuperAdmin($username)){
                $userLevel1 = "superadmin";
            }elseif($pACL->isUserAdministratorGroup($username)){
                $userLevel1 = "admin";
            }
        }
    }
    
    if($idOrganization!=false){
        //obtenemos el dominio de las organizacion
        $query="SELECT domain from organization where id=?";
        $result=$pdbACL->getFirstRowQuery($query,false,array($idOrganization));
        if($result==false){
            $domain=false;
        }else{
            if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $result[0]))
                $domain=false;
            else
                $domain=$result[0];
        }
    }
    return array("idUser"=>$idUser,"id_organization"=>$idOrganization,"userlevel"=>$userLevel1,"domain"=>$domain);
}

function getResourceActionsByUser($idUser,$moduleId){
    global $arrConf,$elxPath;
    require_once("$elxPath/libs/paloSantoACL.class.php");
    $pdbACL = new paloDB($arrConf['elastix_dsn']['elastix']);
    $pACL = new paloACL($pdbACL);
    return $pACL->getResourceActionsByUser($idUser,$moduleId);
}

function isStrongPassword($password){
    if(strlen($password)>=10){
        if(preg_match("/[a-z]+/",$password)){
            if(preg_match("/[A-Z]+/",$password)){
                if(preg_match("/[0-9]+/",$password)){
                    return true;
                }
            }
        }
    }
    return false;
}

/**
    Funcion que devuelve un arreglo que contiene una lista de paises
    @return array(country_name=>country_name,country_name=>country_name,...)
*/
function getCountry(){
    $arrCountry = array();
    $arrCountry["Afghanistan"] = "Afghanistan";
    $arrCountry["Akrotiri"] = "Akrotiri";
    $arrCountry["Albania"] = "Albania";
    $arrCountry["Algeria"] = "Algeria";
    $arrCountry["American Samoa"] = "American Samoa";
    $arrCountry["Andorra"] = "Andorra";
    $arrCountry["Angola"] = "Angola";
    $arrCountry["Anguilla"] = "Anguilla";
    $arrCountry["Antarctica"] = "Antarctica";
    $arrCountry["Antigua and Barbuda"] = "Antigua and Barbuda";
    $arrCountry["Arctic Ocean"] = "Arctic Ocean";
    $arrCountry["Argentina"] = "Argentina";
    $arrCountry["Armenia"] = "Armenia";
    $arrCountry["Aruba"] = "Aruba";
    $arrCountry["Ashmore and Cartier Islands"] = "Ashmore and Cartier Islands";
    $arrCountry["Atlantic Ocean"] = "Atlantic Ocean";
    $arrCountry["Australia"] = "Australia";
    $arrCountry["Austria"] = "Austria";
    $arrCountry["Azerbaijan"] = "Azerbaijan";
    $arrCountry["Bahamas"] = "Bahamas";
    $arrCountry["Bahrain"] = "Bahrain";
    $arrCountry["Baker Island"] = "Baker Island";
    $arrCountry["Bangladesh"] = "Bangladesh";
    $arrCountry["Barbados"] = "Barbados";
    $arrCountry["Bassas da India"] = "Bassas da India";
    $arrCountry["Belarus"] = "Belarus";
    $arrCountry["Belgium"] = "Belgium";
    $arrCountry["Belize"] = "Belize";
    $arrCountry["Benin"] = "Benin";
    $arrCountry["Bermuda"] = "Bermuda";
    $arrCountry["Bhutan"] = "Bhutan";
    $arrCountry["Bolivia"] = "Bolivia";
    $arrCountry["Bosnia and Herzegovina"] = "Bosnia and Herzegovina";
    $arrCountry["Botswana"] = "Botswana";
    $arrCountry["Bouvet Island"] = "Bouvet Island";
    $arrCountry["Brazil"] = "Brazil";
    $arrCountry["British Indian Ocean Territory"] = "British Indian Ocean Territory";
    $arrCountry["British Virgin Islands"] = "British Virgin Islands";
    $arrCountry["Brunei"] = "Brunei";
    $arrCountry["Bulgaria"] = "Bulgaria";
    $arrCountry["Burkina Faso"] = "Burkina Faso";
    $arrCountry["Burma"] = "Burma";
    $arrCountry["Burundi"] = "Burundi";
    $arrCountry["Cambodia"] = "Cambodia";
    $arrCountry["Cameroon"] = "Cameroon";
    $arrCountry["Canada"] = "Canada";
    $arrCountry["Cape Verde"] = "Cape Verde";
    $arrCountry["Cayman Islands"] = "Cayman Islands";
    $arrCountry["Central African Republic"] = "Central African Republic";
    $arrCountry["Chad"] = "Chad";
    $arrCountry["Chile"] = "Chile";
    $arrCountry["China"] = "China";
    $arrCountry["Christmas Island"] = "Christmas Island";
    $arrCountry["Clipperton Island"] = "Clipperton Island";
    $arrCountry["Cocos (Keeling) Islands"] = "Cocos (Keeling) Islands";
    $arrCountry["Colombia"] = "Colombia";
    $arrCountry["Comoros"] = "Comoros";
    $arrCountry["Democratic Republic of the Congo"] = "Democratic Republic of the Congo";
    $arrCountry["Cook Islands"] = "Cook Islands";
    $arrCountry["Coral Sea Islands"] = "Coral Sea Islands";
    $arrCountry["Costa Rica"] = "Costa Rica";
    $arrCountry["Cote d'Ivoire"] = "Cote d'Ivoire";
    $arrCountry["Croatia"] = "Croatia";
    $arrCountry["Cuba"] = "Cuba";
    $arrCountry["Cyprus"] = "Cyprus";
    $arrCountry["Czech Republic"] = "Czech Republic";
    $arrCountry["Denmark"] = "Denmark";
    $arrCountry["Dhekelia"] = "Dhekelia";
    $arrCountry["Djibouti"] = "Djibouti";
    $arrCountry["Dominica"] = "Dominica";
    $arrCountry["Dominican Republic"] = "Dominican Republic";
    $arrCountry["East Timor"] = "East Timor";
    $arrCountry["Ecuador"] = "Ecuador";
    $arrCountry["Egypt"] = "Egypt";
    $arrCountry["El Salvador"] = "El Salvador";
    $arrCountry["Equatorial Guinea"] = "Equatorial Guinea";
    $arrCountry["Eritrea"] = "Eritrea";
    $arrCountry["Estonia"] = "Estonia";
    $arrCountry["Ethiopia"] = "Ethiopia";
    $arrCountry["Europa Island"] = "Europa Island";
    $arrCountry["Falkland Islands (Islas Malvinas)"] = "Falkland Islands (Islas Malvinas)";
    $arrCountry["Faroe Islands"] = "Faroe Islands";
    $arrCountry["Fiji"] = "Fiji";
    $arrCountry["Finland"] = "Finland";
    $arrCountry["France"] = "France";
    $arrCountry["French Guiana"] = "French Guiana";
    $arrCountry["French Polynesia"] = "French Polynesia";
    $arrCountry["French Southern and Antarctic Lands"] = "French Southern and Antarctic Lands";
    $arrCountry["Gabon"] = "Gabon";
    $arrCountry["Gambia, The"] = "Gambia, The";
    $arrCountry["Gaza Strip"] = "Gaza Strip";
    $arrCountry["Georgia"] = "Georgia";
    $arrCountry["Germany"] = "Germany";
    $arrCountry["Ghana"] = "Ghana";
    $arrCountry["Gibraltar"] = "Gibraltar";
    $arrCountry["Glorioso Islands"] = "Glorioso Islands";
    $arrCountry["Greece"] = "Greece";
    $arrCountry["Greenland"] = "Greenland";
    $arrCountry["Grenada"] = "Grenada";
    $arrCountry["Guadeloupe"] = "Guadeloupe";
    $arrCountry["Guam"] = "Guam";
    $arrCountry["Guatemala"] = "Guatemala";
    $arrCountry["Guernsey"] = "Guernsey";
    $arrCountry["Guinea"] = "Guinea";
    $arrCountry["Guinea-Bissau"] = "Guinea-Bissau";
    $arrCountry["Guyana"] = "Guyana";
    $arrCountry["Haiti"] = "Haiti";
    $arrCountry["Heard Island and McDonald Islands"] = "Heard Island and McDonald Islands";
    $arrCountry["Holy See (Vatican City)"] = "Holy See (Vatican City)";
    $arrCountry["Honduras"] = "Honduras";
    $arrCountry["Hong Kong"] = "Hong Kong";
    $arrCountry["Howland Island"] = "Howland Island";
    $arrCountry["Hungary"] = "Hungary";
    $arrCountry["Iceland"] = "Iceland";
    $arrCountry["India"] = "India";
    $arrCountry["Indian Ocean"] = "Indian Ocean";
    $arrCountry["Indonesia"] = "Indonesia";
    $arrCountry["Iran"] = "Iran";
    $arrCountry["Iraq"] = "Iraq";
    $arrCountry["Ireland"] = "Ireland";
    $arrCountry["Isle of Man"] = "Isle of Man";
    $arrCountry["Israel"] = "Israel";
    $arrCountry["Italy"] = "Italy";
    $arrCountry["Jamaica"] = "Jamaica";
    $arrCountry["Jan Mayen"] = "Jan Mayen";
    $arrCountry["Japan"] = "Japan";
    $arrCountry["Jarvis Island"] = "Jarvis Island";
    $arrCountry["Jersey"] = "Jersey";
    $arrCountry["Johnston Atoll"] = "Johnston Atoll";
    $arrCountry["Jordan"] = "Jordan";
    $arrCountry["Juan de Nova Island"] = "Juan de Nova Island";
    $arrCountry["Kazakhstan"] = "Kazakhstan";
    $arrCountry["Kenya"] = "Kenya";
    $arrCountry["Kingman Reef"] = "Kingman Reef";
    $arrCountry["Kiribati"] = "Kiribati";
    $arrCountry["Korea, North"] = "Korea, North";
    $arrCountry["Korea, South"] = "Korea, South";
    $arrCountry["Kuwait"] = "Kuwait";
    $arrCountry["Kyrgyzstan"] = "Kyrgyzstan";
    $arrCountry["Laos"] = "Laos";
    $arrCountry["Latvia"] = "Latvia";
    $arrCountry["Lebanon"] = "Lebanon";
    $arrCountry["Lesotho"] = "Lesotho";
    $arrCountry["Liberia"] = "Liberia";
    $arrCountry["Libya"] = "Libya";
    $arrCountry["Liechtenstein"] = "Liechtenstein";
    $arrCountry["Lithuania"] = "Lithuania";
    $arrCountry["Luxembourg"] = "Luxembourg";
    $arrCountry["Macau"] = "Macau";
    $arrCountry["Macedonia"] = "Macedonia";
    $arrCountry["Madagascar"] = "Madagascar";
    $arrCountry["Malawi"] = "Malawi";
    $arrCountry["Malaysia"] = "Malaysia";
    $arrCountry["Maldives"] = "Maldives";
    $arrCountry["Mali"] = "Mali";
    $arrCountry["Malta"] = "Malta";
    $arrCountry["Marshall Islands"] = "Marshall Islands";
    $arrCountry["Martinique"] = "Martinique";
    $arrCountry["Mauritania"] = "Mauritania";
    $arrCountry["Mauritius"] = "Mauritius";
    $arrCountry["Mayotte"] = "Mayotte";
    $arrCountry["Mexico"] = "Mexico";
    $arrCountry["Micronesia, Federated States of"] = "Micronesia, Federated States of";
    $arrCountry["Midway Islands"] = "Midway Islands";
    $arrCountry["Moldova"] = "Moldova";
    $arrCountry["Monaco"] = "Monaco";
    $arrCountry["Mongolia"] = "Mongolia";
    $arrCountry["Montserrat"] = "Montserrat";
    $arrCountry["Morocco"] = "Morocco";
    $arrCountry["Mozambique"] = "Mozambique";
    $arrCountry["Namibia"] = "Namibia";
    $arrCountry["Nauru"] = "Nauru";
    $arrCountry["Navassa Island"] = "Navassa Island";
    $arrCountry["Nepal"] = "Nepal";
    $arrCountry["Netherlands"] = "Netherlands";
    $arrCountry["Netherlands Antilles"] = "Netherlands Antilles";
    $arrCountry["New Caledonia"] = "New Caledonia";
    $arrCountry["New Zealand"] = "New Zealand";
    $arrCountry["Nicaragua"] = "Nicaragua";
    $arrCountry["Niger"] = "Niger";
    $arrCountry["Nigeria"] = "Nigeria";
    $arrCountry["Niue"] = "Niue";
    $arrCountry["Norfolk Island"] = "Norfolk Island";
    $arrCountry["Northern Mariana Islands"] = "Northern Mariana Islands";
    $arrCountry["Norway"] = "Norway";
    $arrCountry["Oman"] = "Oman";
    $arrCountry["Pacific Ocean"] = "Pacific Ocean";
    $arrCountry["Pakistan"] = "Pakistan";
    $arrCountry["Palau"] = "Palau";
    $arrCountry["Palmyra Atoll"] = "Palmyra Atoll";
    $arrCountry["Panama"] = "Panama";
    $arrCountry["Papua New Guinea"] = "Papua New Guinea";
    $arrCountry["Paracel Islands"] = "Paracel Islands";
    $arrCountry["Paraguay"] = "Paraguay";
    $arrCountry["Peru"] = "Peru";
    $arrCountry["Philippines"] = "Philippines";
    $arrCountry["Pitcairn Islands"] = "Pitcairn Islands";
    $arrCountry["Poland"] = "Poland";
    $arrCountry["Portugal"] = "Portugal";
    $arrCountry["Puerto Rico"] = "Puerto Rico";
    $arrCountry["Qatar"] = "Qatar";
    $arrCountry["Reunion"] = "Reunion";
    $arrCountry["Romania"] = "Romania";
    $arrCountry["Russia"] = "Russia";
    $arrCountry["Rwanda"] = "Rwanda";
    $arrCountry["Saint Helena"] = "Saint Helena";
    $arrCountry["Saint Kitts and Nevis"] = "Saint Kitts and Nevis";
    $arrCountry["Saint Lucia"] = "Saint Lucia";
    $arrCountry["Saint Pierre and Miquelon"] = "Saint Pierre and Miquelon";
    $arrCountry["Saint Vincent and the Grenadines"] = "Saint Vincent and the Grenadines";
    $arrCountry["Samoa"] = "Samoa";
    $arrCountry["San Marino"] = "San Marino";
    $arrCountry["Sao Tome and Principe"] = "Sao Tome and Principe";
    $arrCountry["Saudi Arabia"] = "Saudi Arabia";
    $arrCountry["Senegal"] = "Senegal";
    $arrCountry["Serbia and Montenegro"] = "Serbia and Montenegro";
    $arrCountry["Seychelles"] = "Seychelles";
    $arrCountry["Sierra Leone"] = "Sierra Leone";
    $arrCountry["Singapore"] = "Singapore";
    $arrCountry["Slovakia"] = "Slovakia";
    $arrCountry["Slovenia"] = "Slovenia";
    $arrCountry["Solomon Islands"] = "Solomon Islands";
    $arrCountry["Somalia"] = "Somalia";
    $arrCountry["South Africa"] = "South Africa";
    $arrCountry["South Georgia and the South Sandwich Islands"] = "South Georgia and the South Sandwich Islands";
    $arrCountry["Southern Ocean"] = "Southern Ocean";
    $arrCountry["Spain"] = "Spain";
    $arrCountry["Spratly Islands"] = "Spratly Islands";
    $arrCountry["Sri Lanka"] = "Sri Lanka";
    $arrCountry["Sudan"] = "Sudan";
    $arrCountry["Suriname"] = "Suriname";
    $arrCountry["Svalbard"] = "Svalbard";
    $arrCountry["Swaziland"] = "Swaziland";
    $arrCountry["Sweden"] = "Sweden";
    $arrCountry["Switzerland"] = "Switzerland";
    $arrCountry["Syria"] = "Syria";
    $arrCountry["Taiwan"] = "Taiwan";
    $arrCountry["Tajikistan"] = "Tajikistan";
    $arrCountry["Tanzania"] = "Tanzania";
    $arrCountry["Thailand"] = "Thailand";
    $arrCountry["Togo"] = "Togo";
    $arrCountry["Tokelau"] = "Tokelau";
    $arrCountry["Tonga"] = "Tonga";
    $arrCountry["Trinidad and Tobago"] = "Trinidad and Tobago";
    $arrCountry["Tromelin Island"] = "Tromelin Island";
    $arrCountry["Tunisia"] = "Tunisia";
    $arrCountry["Turkey"] = "Turkey";
    $arrCountry["Turkmenistan"] = "Turkmenistan";
    $arrCountry["Turks and Caicos Islands"] = "Turks and Caicos Islands";
    $arrCountry["Tuvalu"] = "Tuvalu";
    $arrCountry["Uganda"] = "Uganda";
    $arrCountry["Ukraine"] = "Ukraine";
    $arrCountry["United Arab Emirates"] = "United Arab Emirates";
    $arrCountry["United Kingdom"] = "United Kingdom";
    $arrCountry["United States"] = "United States";
    $arrCountry["United States Pacific Island Wildlife Refuges"] = "United States Pacific Island Wildlife Refuges";
    $arrCountry["Uruguay"] = "Uruguay";
    $arrCountry["Uzbekistan"] = "Uzbekistan";
    $arrCountry["Vanuatu"] = "Vanuatu";
    $arrCountry["Venezuela"] = "Venezuela";
    $arrCountry["Vietnam"] = "Vietnam";
    $arrCountry["Virgin Islands, BRITISH"] = "Virgin Islands, BRITISH";
    $arrCountry["Virgin Islands, U.S"] = "Virgin Islands, U.S";
    $arrCountry["Wake Island"] = "Wake Island";
    $arrCountry["Wallis and Futuna"] = "Wallis and Futuna";
    $arrCountry["West Bank"] = "West Bank";
    $arrCountry["Western Sahara"] = "Western Sahara";
    $arrCountry["Yemen"] = "Yemen";
    $arrCountry["Zambia"] = "Zambia";
    $arrCountry["Zimbabwe"] = "Zimbabwe";
    return $arrCountry;
}

/**
    Funcion que devuelve un arreglo que contiene los lenguages soportados en el servidor por asterisk
    @return array
*/
function getLanguagePBX(){
    $arrLang=array();
    $pConfig = new paloConfig("/var/www/elastixdir/asteriskconf", "elastix_pbx.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);
    
    $astlibsound = $arrConfig['ASTVARLIBDIR']['valor']."/sounds";
    $listDir=scandir($astlibsound);
    if($listDir!==false){
        foreach($listDir as $value){
            if ($value != "." && $value != "..") {
                if(is_dir($astlibsound."/".$value)){
                    if(preg_match("/^[a-z]{2}(-[A-Z]{2})*$/",$value)==true){
                        $list=scandir($astlibsound."/".$value);
                        if($list!==false && count($list)>2)
                            $arrLang[$value]=$value;
                    }
                }
            }
        }
    }else{
        return false;
    }
    return $arrLang;
}

//esta function devuelve el country code, lenguage
//de una pais dado el nombre del pais
function getCountrySettings($country){
    $arrCountry=array();
    $arrCountry["Afghanistan"] = array("code"=>"93","language"=>"ps","tonezone"=>"af");
    $arrCountry["Akrotiri"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Albania"] = array("code"=>"355","language"=>"sq","tonezone"=>"al");
    $arrCountry["Algeria"] = array("code"=>"213","language"=>"tzm","tonezone"=>"dz");
    $arrCountry["American Samoa"] = array("code"=>"1 684","language"=>"en","tonezone"=>"as");
    $arrCountry["Andorra"] = array("code"=>"376","language"=>"","tonezone"=>"ad");
    $arrCountry["Angola"] = array("code"=>"244","language"=>"kg","tonezone"=>"ao");
    $arrCountry["Anguilla"] = array("code"=>"1 264","language"=>"","tonezone"=>"ai");
    $arrCountry["Antarctica"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Antigua and Barbuda"] = array("code"=>"1 268","language"=>"","tonezone"=>"ag");
    $arrCountry["Arctic Ocean"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Argentina"] = array("code"=>"54","language"=>"es","tonezone"=>"ar");
    $arrCountry["Armenia"] = array("code"=>"7","language"=>"hy","tonezone"=>"am");
    $arrCountry["Aruba"] = array("code"=>"297","language"=>"nl","tonezone"=>"aw");
    $arrCountry["Ashmore and Cartier Islands"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Atlantic Ocean"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Australia"] = array("code"=>"61","language"=>"en","tonezone"=>"au");
    $arrCountry["Austria"] = array("code"=>"43","language"=>"hu","tonezone"=>"at");
    $arrCountry["Azerbaijan"] = array("code"=>"994","language"=>"az","tonezone"=>"az");
    $arrCountry["Bahamas"] = array("code"=>"1 242","language"=>"en","tonezone"=>"bs");
    $arrCountry["Bahrain"] = array("code"=>"973","language"=>"ar","tonezone"=>"bh");
    $arrCountry["Baker Island"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Bangladesh"] = array("code"=>"880","language"=>"bn","tonezone"=>"bd");
    $arrCountry["Barbados"] = array("code"=>"1 246","language"=>"en","tonezone"=>"bb");
    $arrCountry["Bassas da India"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Belarus"] = array("code"=>"375","language"=>"be","tonezone"=>"by");
    $arrCountry["Belgium"] = array("code"=>"32","language"=>"en","tonezone"=>"be");
    $arrCountry["Belize"] = array("code"=>"501","language"=>"en","tonezone"=>"bz");
    $arrCountry["Benin"] = array("code"=>"229","language"=>"fr","tonezone"=>"bj");
    $arrCountry["Bermuda"] = array("code"=>"1 441","language"=>"en","tonezone"=>"bm");
    $arrCountry["Bhutan"] = array("code"=>"975","language"=>"dz","tonezone"=>"bt");
    $arrCountry["Bolivia"] = array("code"=>"591","language"=>"es","tonezone"=>"bo");
    $arrCountry["Bosnia and Herzegovina"] = array("code"=>"387","language"=>"bs","tonezone"=>"ba");
    $arrCountry["Botswana"] = array("code"=>"267","language"=>"en","tonezone"=>"bw");
    $arrCountry["Bouvet Island"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Brazil"] = array("code"=>"55","language"=>"pt","tonezone"=>"br");
    $arrCountry["British Indian Ocean Territory"] = array("code"=>"246","language"=>"en","tonezone"=>"io");
    $arrCountry["British Virgin Islands"] = array("code"=>"","language"=>"en","tonezone"=>"");
    $arrCountry["Brunei"] = array("code"=>"673","language"=>"en","tonezone"=>"bn");
    $arrCountry["Bulgaria"] = array("code"=>"359","language"=>"bg","tonezone"=>"bg");
    $arrCountry["Burkina Faso"] = array("code"=>"226","language"=>"bm","tonezone"=>"bf");
    $arrCountry["Burma"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Burundi"] = array("code"=>"257","language"=>"","tonezone"=>"bi");
    $arrCountry["Cambodia"] = array("code"=>"855","language"=>"","tonezone"=>"kh");
    $arrCountry["Cameroon"] = array("code"=>"237","language"=>"","tonezone"=>"cm");
    $arrCountry["Canada"] = array("code"=>"1","language"=>"en","tonezone"=>"ca");
    $arrCountry["Cape Verde"] = array("code"=>"238","language"=>"pt","tonezone"=>"");
    $arrCountry["Cayman Islands"] = array("code"=>"1 345","language"=>"en","tonezone"=>"ky");
    $arrCountry["Central African Republic"] = array("code"=>"236","language"=>"fr","tonezone"=>"cf");
    $arrCountry["Chad"] = array("code"=>"235","language"=>"ar","tonezone"=>"td");
    $arrCountry["Chile"] = array("code"=>"56","language"=>"es","tonezone"=>"cl");
    $arrCountry["China"] = array("code"=>"86","language"=>"zh","tonezone"=>"");
    $arrCountry["Christmas Island"] = array("code"=>"61","language"=>"ms","tonezone"=>"cn");
    $arrCountry["Clipperton Island"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Cocos (Keeling) Islands"] = array("code"=>"61","language"=>"ms","tonezone"=>"cc");
    $arrCountry["Colombia"] = array("code"=>"57","language"=>"es","tonezone"=>"co");
    $arrCountry["Comoros"] = array("code"=>"269","language"=>"fr","tonezone"=>"km");
    $arrCountry["Democratic Republic of the Congo"] = array("code"=>"243","language"=>"fr","tonezone"=>"cd");
    $arrCountry["Cook Islands"] = array("code"=>"682","language"=>"en","tonezone"=>"ck");
    $arrCountry["Coral Sea Islands"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Costa Rica"] = array("code"=>"506","language"=>"es","tonezone"=>"cr");
    $arrCountry["Cote d'Ivoire"] = array("code"=>"225","language"=>"fr","tonezone"=>"ci");
    $arrCountry["Croatia"] = array("code"=>"385","language"=>"hr","tonezone"=>"hr");
    $arrCountry["Cuba"] = array("code"=>"53","language"=>"es","tonezone"=>"cu");
    $arrCountry["Cyprus"] = array("code"=>"357","language"=>"el","tonezone"=>"cy");
    $arrCountry["Czech Republic"] = array("code"=>"420","language"=>"cs","tonezone"=>"cz");
    $arrCountry["Denmark"] = array("code"=>"45","language"=>"da","tonezone"=>"dk");
    $arrCountry["Dhekelia"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Djibouti"] = array("code"=>"253","language"=>"fr","tonezone"=>"dj");
    $arrCountry["Dominica"] = array("code"=>"1 767","language"=>"en","tonezone"=>"dm");
    $arrCountry["Dominican Republic"] = array("code"=>"1 809","language"=>"es","tonezone"=>"do");
    $arrCountry["East Timor"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Ecuador"] = array("code"=>"593","language"=>"es","tonezone"=>"ec");
    $arrCountry["Egypt"] = array("code"=>"20","language"=>"ar","tonezone"=>"eg");
    $arrCountry["El Salvador"] = array("code"=>"503","language"=>"es","tonezone"=>"sv");
    $arrCountry["Equatorial Guinea"] = array("code"=>"240","language"=>"es","tonezone"=>"gq");
    $arrCountry["Eritrea"] = array("code"=>"291","language"=>"en","tonezone"=>"er");
    $arrCountry["Estonia"] = array("code"=>"372","language"=>"et","tonezone"=>"ee");
    $arrCountry["Ethiopia"] = array("code"=>"251","language"=>"en","tonezone"=>"et");
    $arrCountry["Europa Island"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Falkland Islands (Islas Malvinas)"] = array("code"=>"500","language"=>"en","tonezone"=>"fk");
    $arrCountry["Faroe Islands"] = array("code"=>"298","language"=>"da","tonezone"=>"fo");
    $arrCountry["Fiji"] = array("code"=>"679","language"=>"en","tonezone"=>"fj");
    $arrCountry["Finland"] = array("code"=>"358","language"=>"fi","tonezone"=>"fi");
    $arrCountry["France"] = array("code"=>"33","language"=>"fr","tonezone"=>"fr");
    $arrCountry["French Guiana"] = array("code"=>"594","language"=>"fr","tonezone"=>"gf");
    $arrCountry["French Polynesia"] = array("code"=>"689","language"=>"fr","tonezone"=>"pf");
    $arrCountry["French Southern and Antarctic Lands"] = array("code"=>"","language"=>"fr","tonezone"=>"");
    $arrCountry["Gabon"] = array("code"=>"241","language"=>"fr","tonezone"=>"ga");
    $arrCountry["Gambia, The"] = array("code"=>"220","language"=>"en","tonezone"=>"gm");
    $arrCountry["Gaza Strip"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Georgia"] = array("code"=>"995","language"=>"ka","tonezone"=>"ge");
    $arrCountry["Germany"] = array("code"=>"49","language"=>"de","tonezone"=>"de");
    $arrCountry["Ghana"] = array("code"=>"233","language"=>"en","tonezone"=>"gh");
    $arrCountry["Gibraltar"] = array("code"=>"350","language"=>"en","tonezone"=>"gi");
    $arrCountry["Glorioso Islands"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Greece"] = array("code"=>"30","language"=>"el","tonezone"=>"gr");
    $arrCountry["Greenland"] = array("code"=>"299","language"=>"da","tonezone"=>"gl");
    $arrCountry["Grenada"] = array("code"=>"1 473","language"=>"en","tonezone"=>"gd");
    $arrCountry["Guadeloupe"] = array("code"=>"590","language"=>"fr","tonezone"=>"gp");
    $arrCountry["Guam"] = array("code"=>"1 671","language"=>"en","tonezone"=>"gu");
    $arrCountry["Guatemala"] = array("code"=>"502","language"=>"es","tonezone"=>"gt");
    $arrCountry["Guernsey"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Guinea"] = array("code"=>"224","language"=>"fr","tonezone"=>"gn");
    $arrCountry["Guinea-Bissau"] = array("code"=>"245","language"=>"pt","tonezone"=>"gw");
    $arrCountry["Guyana"] = array("code"=>"592","language"=>"en","tonezone"=>"gy");
    $arrCountry["Haiti"] = array("code"=>"509","language"=>"fr","tonezone"=>"ht");
    $arrCountry["Heard Island and McDonald Islands"] = array("code"=>"672","language"=>"","tonezone"=>"");
    $arrCountry["Holy See (Vatican City)"] = array("code"=>"","language"=>"it","tonezone"=>"va");
    $arrCountry["Honduras"] = array("code"=>"504","language"=>"es","tonezone"=>"hn");
    $arrCountry["Hong Kong"] = array("code"=>"852","language"=>"zh","tonezone"=>"hk");
    $arrCountry["Howland Island"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Hungary"] = array("code"=>"36","language"=>"de","tonezone"=>"hu");
    $arrCountry["Iceland"] = array("code"=>"354","language"=>"is","tonezone"=>"is");
    $arrCountry["India"] = array("code"=>"91","language"=>"hi","tonezone"=>"in");
    $arrCountry["Indian Ocean"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Indonesia"] = array("code"=>"92","language"=>"id","tonezone"=>"id");
    $arrCountry["Iran"] = array("code"=>"98","language"=>"ku","tonezone"=>"ir");
    $arrCountry["Iraq"] = array("code"=>"964","language"=>"ar","tonezone"=>"iq");
    $arrCountry["Ireland"] = array("code"=>"353","language"=>"en","tonezone"=>"ie");
    $arrCountry["Isle of Man"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Israel"] = array("code"=>"972","language"=>"he","tonezone"=>"il");
    $arrCountry["Italy"] = array("code"=>"39","language"=>"it","tonezone"=>"it");
    $arrCountry["Jamaica"] = array("code"=>"1 876","language"=>"en","tonezone"=>"jm");
    $arrCountry["Jan Mayen"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Japan"] = array("code"=>"81","language"=>"ja","tonezone"=>"jp");
    $arrCountry["Jarvis Island"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Jersey"] = array("code"=>"44","language"=>"","tonezone"=>"");
    $arrCountry["Johnston Atoll"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Jordan"] = array("code"=>"962","language"=>"ar","tonezone"=>"jo");
    $arrCountry["Juan de Nova Island"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Kazakhstan"] = array("code"=>"7","language"=>"av","tonezone"=>"kz");
    $arrCountry["Kenya"] = array("code"=>"245","language"=>"so","tonezone"=>"ke");
    $arrCountry["Kingman Reef"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Kiribati"] = array("code"=>"686","language"=>"en","tonezone"=>"ki");
    $arrCountry["Korea, North"] = array("code"=>"850","language"=>"ko","tonezone"=>"kp");
    $arrCountry["Korea, South"] = array("code"=>"82","language"=>"ko","tonezone"=>"kr");
    $arrCountry["Kuwait"] = array("code"=>"965","language"=>"ar","tonezone"=>"kw");
    $arrCountry["Kyrgyzstan"] = array("code"=>"996","language"=>"ky","tonezone"=>"kg");
    $arrCountry["Laos"] = array("code"=>"856","language"=>"","tonezone"=>"");
    $arrCountry["Latvia"] = array("code"=>"371","language"=>"lv","tonezone"=>"lv");
    $arrCountry["Lebanon"] = array("code"=>"961","language"=>"ar","tonezone"=>"lb");
    $arrCountry["Lesotho"] = array("code"=>"266","language"=>"st","tonezone"=>"ls");
    $arrCountry["Liberia"] = array("code"=>"231","language"=>"en","tonezone"=>"lr");
    $arrCountry["Libya"] = array("code"=>"218","language"=>"ar","tonezone"=>"ly");
    $arrCountry["Liechtenstein"] = array("code"=>"423","language"=>"de","tonezone"=>"li");
    $arrCountry["Lithuania"] = array("code"=>"370","language"=>"lt","tonezone"=>"lt");
    $arrCountry["Luxembourg"] = array("code"=>"352","language"=>"lb","tonezone"=>"lu");
    $arrCountry["Macau"] = array("code"=>"853","language"=>"","tonezone"=>"");
    $arrCountry["Macedonia"] = array("code"=>"389","language"=>"mk","tonezone"=>"mk");
    $arrCountry["Madagascar"] = array("code"=>"261","language"=>"fr","tonezone"=>"mg");
    $arrCountry["Malawi"] = array("code"=>"265","language"=>"en","tonezone"=>"mw");
    $arrCountry["Malaysia"] = array("code"=>"60","language"=>"jv","tonezone"=>"my");
    $arrCountry["Maldives"] = array("code"=>"960","language"=>"dv","tonezone"=>"mv");
    $arrCountry["Mali"] = array("code"=>"223","language"=>"fr","tonezone"=>"ml");
    $arrCountry["Malta"] = array("code"=>"356","language"=>"en","tonezone"=>"mt");
    $arrCountry["Marshall Islands"] = array("code"=>"692","language"=>"en","tonezone"=>"mh");
    $arrCountry["Martinique"] = array("code"=>"596","language"=>"fr","tonezone"=>"mq");
    $arrCountry["Mauritania"] = array("code"=>"222","language"=>"","tonezone"=>"mr");
    $arrCountry["Mauritius"] = array("code"=>"230","language"=>"en","tonezone"=>"mu");
    $arrCountry["Mayotte"] = array("code"=>"269","language"=>"fr","tonezone"=>"yt");
    $arrCountry["Mexico"] = array("code"=>"52","language"=>"es","tonezone"=>"mx");
    $arrCountry["Micronesia, Federated States of"] = array("code"=>"691","language"=>"en","tonezone"=>"fm");
    $arrCountry["Midway Islands"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Moldova"] = array("code"=>"373","language"=>"tr","tonezone"=>"md");
    $arrCountry["Monaco"] = array("code"=>"377","language"=>"fr","tonezone"=>"mc");
    $arrCountry["Mongolia"] = array("code"=>"976","language"=>"mn","tonezone"=>"mn");
    $arrCountry["Montserrat"] = array("code"=>"1 664","language"=>"en","tonezone"=>"ms");
    $arrCountry["Morocco"] = array("code"=>"212","language"=>"ar","tonezone"=>"ma");
    $arrCountry["Mozambique"] = array("code"=>"258","language"=>"pt","tonezone"=>"mz");
    $arrCountry["Namibia"] = array("code"=>"264","language"=>"en","tonezone"=>"na");
    $arrCountry["Nauru"] = array("code"=>"674","language"=>"en","tonezone"=>"nr");
    $arrCountry["Navassa Island"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Nepal"] = array("code"=>"977","language"=>"ne","tonezone"=>"mp");
    $arrCountry["Netherlands"] = array("code"=>"31","language"=>"nl","tonezone"=>"an");
    $arrCountry["Netherlands Antilles"] = array("code"=>"599","language"=>"nl","tonezone"=>"an");
    $arrCountry["New Caledonia"] = array("code"=>"687","language"=>"fr","tonezone"=>"nc");
    $arrCountry["New Zealand"] = array("code"=>"64","language"=>"en","tonezone"=>"nz");
    $arrCountry["Nicaragua"] = array("code"=>"505","language"=>"es","tonezone"=>"ni");
    $arrCountry["Niger"] = array("code"=>"227","language"=>"fr","tonezone"=>"ne");
    $arrCountry["Nigeria"] = array("code"=>"234","language"=>"en","tonezone"=>"ng");
    $arrCountry["Niue"] = array("code"=>"683","language"=>"en","tonezone"=>"nu");
    $arrCountry["Norfolk Island"] = array("code"=>"","language"=>"en","tonezone"=>"nf");
    $arrCountry["Northern Mariana Islands"] = array("code"=>"1 670","language"=>"en","tonezone"=>"mp");
    $arrCountry["Norway"] = array("code"=>"47","language"=>"no","tonezone"=>"no");
    $arrCountry["Oman"] = array("code"=>"968","language"=>"ar","tonezone"=>"om");
    $arrCountry["Pacific Ocean"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Pakistan"] = array("code"=>"92","language"=>"ur","tonezone"=>"pk");
    $arrCountry["Palau"] = array("code"=>"680","language"=>"en","tonezone"=>"pw");
    $arrCountry["Palmyra Atoll"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Panama"] = array("code"=>"507","language"=>"es","tonezone"=>"pa");
    $arrCountry["Papua New Guinea"] = array("code"=>"675","language"=>"en","tonezone"=>"pg");
    $arrCountry["Paracel Islands"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Paraguay"] = array("code"=>"595","language"=>"es","tonezone"=>"py");
    $arrCountry["Peru"] = array("code"=>"51","language"=>"es","tonezone"=>"pe");
    $arrCountry["Philippines"] = array("code"=>"63","language"=>"en","tonezone"=>"ph");
    $arrCountry["Pitcairn Islands"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Poland"] = array("code"=>"48","language"=>"pl","tonezone"=>"pl");
    $arrCountry["Portugal"] = array("code"=>"351","language"=>"pt","tonezone"=>"pt");
    $arrCountry["Puerto Rico"] = array("code"=>"1","language"=>"es","tonezone"=>"pr");
    $arrCountry["Qatar"] = array("code"=>"974","language"=>"ar","tonezone"=>"qa");
    $arrCountry["Reunion"] = array("code"=>"262","language"=>"fr","tonezone"=>"re");
    $arrCountry["Romania"] = array("code"=>"40","language"=>"hu","tonezone"=>"ro");
    $arrCountry["Russia"] = array("code"=>"7","language"=>"ru","tonezone"=>"ru");
    $arrCountry["Rwanda"] = array("code"=>"250","language"=>"fr","tonezone"=>"rw");
    $arrCountry["Saint Helena"] = array("code"=>"290","language"=>"en","tonezone"=>"sh");
    $arrCountry["Saint Kitts and Nevis"] = array("code"=>"1 869","language"=>"en","tonezone"=>"kn");
    $arrCountry["Saint Lucia"] = array("code"=>"1 758","language"=>"en","tonezone"=>"lc");
    $arrCountry["Saint Pierre and Miquelon"] = array("code"=>"508","language"=>"fr","tonezone"=>"pm");
    $arrCountry["Saint Vincent and the Grenadines"] = array("code"=>"1 784","language"=>"en","tonezone"=>"vc");
    $arrCountry["Samoa"] = array("code"=>"685","language"=>"en","tonezone"=>"ws");
    $arrCountry["San Marino"] = array("code"=>"378","language"=>"it","tonezone"=>"sm");
    $arrCountry["Sao Tome and Principe"] = array("code"=>"239","language"=>"pt","tonezone"=>"st");
    $arrCountry["Saudi Arabia"] = array("code"=>"966","language"=>"ar","tonezone"=>"sa");
    $arrCountry["Senegal"] = array("code"=>"221","language"=>"wo","tonezone"=>"sn");
    $arrCountry["Serbia and Montenegro"] = array("code"=>"381","language"=>"sr","tonezone"=>"cs");
    $arrCountry["Seychelles"] = array("code"=>"248","language"=>"en","tonezone"=>"sc");
    $arrCountry["Sierra Leone"] = array("code"=>"232","language"=>"en","tonezone"=>"sl");
    $arrCountry["Singapore"] = array("code"=>"65","language"=>"zh","tonezone"=>"sg");
    $arrCountry["Slovakia"] = array("code"=>"421","language"=>"hu","tonezone"=>"sk");
    $arrCountry["Slovenia"] = array("code"=>"386","language"=>"hu","tonezone"=>"si");
    $arrCountry["Solomon Islands"] = array("code"=>"677","language"=>"en","tonezone"=>"sb");
    $arrCountry["Somalia"] = array("code"=>"252","language"=>"ar","tonezone"=>"so");
    $arrCountry["South Africa"] = array("code"=>"27","language"=>"en","tonezone"=>"za");
    $arrCountry["South Georgia and the South Sandwich Islands"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Southern Ocean"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Spain"] = array("code"=>"34","language"=>"es","tonezone"=>"es");
    $arrCountry["Spratly Islands"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Sri Lanka"] = array("code"=>"94","language"=>"si","tonezone"=>"lk");
    $arrCountry["Sudan"] = array("code"=>"249","language"=>"ar","tonezone"=>"sd");
    $arrCountry["Suriname"] = array("code"=>"597","language"=>"jv","tonezone"=>"sr");
    $arrCountry["Svalbard"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Swaziland"] = array("code"=>"268","language"=>"en","tonezone"=>"sz");
    $arrCountry["Sweden"] = array("code"=>"46","language"=>"sv","tonezone"=>"se");
    $arrCountry["Switzerland"] = array("code"=>"41","language"=>"de","tonezone"=>"ch");
    $arrCountry["Syria"] = array("code"=>"963","language"=>"ar","tonezone"=>"sy");
    $arrCountry["Taiwan"] = array("code"=>"886","language"=>"zh","tonezone"=>"tw");
    $arrCountry["Tajikistan"] = array("code"=>"992","language"=>"os","tonezone"=>"tj");
    $arrCountry["Tanzania"] = array("code"=>"255","language"=>"sw","tonezone"=>"tz");
    $arrCountry["Thailand"] = array("code"=>"66","language"=>"th","tonezone"=>"th");
    $arrCountry["Togo"] = array("code"=>"228","language"=>"fr","tonezone"=>"tg");
    $arrCountry["Tokelau"] = array("code"=>"690","language"=>"en","tonezone"=>"tk");
    $arrCountry["Tonga"] = array("code"=>"676","language"=>"en","tonezone"=>"to");
    $arrCountry["Trinidad and Tobago"] = array("code"=>"1 868","language"=>"en","tonezone"=>"tt");
    $arrCountry["Tromelin Island"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Tunisia"] = array("code"=>"216","language"=>"ar","tonezone"=>"tn");
    $arrCountry["Turkey"] = array("code"=>"90","language"=>"tr","tonezone"=>"tr");
    $arrCountry["Turkmenistan"] = array("code"=>"993","language"=>"os","tonezone"=>"tm");
    $arrCountry["Turks and Caicos Islands"] = array("code"=>"1 649","language"=>"en","tonezone"=>"tc");
    $arrCountry["Tuvalu"] = array("code"=>"688","language"=>"gil","tonezone"=>"tv");
    $arrCountry["Uganda"] = array("code"=>"256","language"=>"en","tonezone"=>"ug");
    $arrCountry["Ukraine"] = array("code"=>"380","language"=>"ru","tonezone"=>"ua");
    $arrCountry["United Arab Emirates"] = array("code"=>"971","language"=>"ar","tonezone"=>"ae");
    $arrCountry["United Kingdom"] = array("code"=>"44","language"=>"en","tonezone"=>"gb");
    $arrCountry["United States"] = array("code"=>"1","language"=>"en","tonezone"=>"us");
    $arrCountry["United States Pacific Island Wildlife Refuges"] = array("code"=>"","language"=>"en","tonezone"=>"um");
    $arrCountry["Uruguay"] = array("code"=>"598","language"=>"es","tonezone"=>"uy");
    $arrCountry["Uzbekistan"] = array("code"=>"998","language"=>"uz","tonezone"=>"uz");
    $arrCountry["Vanuatu"] = array("code"=>"678","language"=>"en","tonezone"=>"vu");
    $arrCountry["Venezuela"] = array("code"=>"58","language"=>"es","tonezone"=>"ve");
    $arrCountry["Vietnam"] = array("code"=>"84","language"=>"vi","tonezone"=>"vn");
    $arrCountry["Virgin Islands, BRITISH"] = array("code"=>"1 284","language"=>"en","tonezone"=>"vg");
    $arrCountry["Virgin Islands, U.S."] = array("code"=>"1 340","language"=>"en","tonezone"=>"vi");
    $arrCountry["Wake Island"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Wallis and Futuna"] = array("code"=>"681","language"=>"fr","tonezone"=>"wf");
    $arrCountry["West Bank"] = array("code"=>"","language"=>"","tonezone"=>"");
    $arrCountry["Western Sahara"] = array("code"=>"212","language"=>"","tonezone"=>"");
    $arrCountry["Yemen"] = array("code"=>"967","language"=>"ar","tonezone"=>"ye");
    $arrCountry["Zambia"] = array("code"=>"260","language"=>"en","tonezone"=>"zm");
    $arrCountry["Zimbabwe"] = array("code"=>"263","language"=>"en","tonezone"=>"zw");
    if(isset($arrCountry[$country])){
        return $arrCountry[$country];
    }else
        return false;
}

// Create a new Smarty object and initialize template directories   
function getSmarty($mainTheme)  
{   
    global $elxPath;
    global $arrConf;
    if (file_exists('/usr/share/php/Smarty/Smarty.class.php'))
        require_once('Smarty/Smarty.class.php');
    else require_once("$elxPath/libs/smarty/libs/Smarty.class.php");     
    $smarty = new Smarty();     
    
    $mainTheme=basename($mainTheme);
    
    $smarty->template_dir = "{$arrConf['basePath']}/web/themes/$mainTheme";   
    $smarty->config_dir =   "$elxPath/configs/";  
    
    if(!is_dir("{$arrConf['documentRoot']}/tmp/smarty/templates_c/$mainTheme")){
        mkdir("{$arrConf['documentRoot']}/tmp/smarty/templates_c/$mainTheme");
    }
    if(!is_dir("{$arrConf['documentRoot']}/tmp/smarty/cache/$mainTheme")){
        mkdir("{$arrConf['documentRoot']}/tmp/smarty/cache/$mainTheme");
    }
    $smarty->compile_dir =  "{$arrConf['documentRoot']}/tmp/smarty/templates_c/$mainTheme";    
    $smarty->cache_dir =    "{$arrConf['documentRoot']}/tmp/smarty/cache/$mainTheme";  
    
    return $smarty;     
}

function loadShortcut($pdbACL, $uid, &$smarty)
{
    global $arrConf;
    
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
    if (empty($pDB->errMsg)) {
        $uelastix = get_key_settings($pDB, 'uelastix');
        if ((int)$uelastix != 0) return '';
    }

    if($uid === FALSE) return '';
    $sql = <<<SQL_BOOKMARKS_HISTORY
SELECT us.id AS id, ar.description AS name, ar.id AS id_menu
FROM user_shortcut us, acl_resource ar
WHERE id_user = ? AND us.type = ? AND ar.id = us.id_resource
ORDER BY us.id DESC
SQL_BOOKMARKS_HISTORY;

    $bookmarks = $pdbACL->fetchTable($sql, TRUE, array($uid, 'bookmark'));
    if (is_array($bookmarks) && count($bookmarks) >= 0)
    foreach (array_keys($bookmarks) as $i) {
        $bookmarks[$i]['name'] = _tr($bookmarks[$i]['name']); 
    } else $bookmarks = NULL;
    $smarty->assign(array(
        'SHORTCUT_BOOKMARKS' => $bookmarks,
        'SHORTCUT_BOOKMARKS_LABEL' => _tr('Bookmarks'),
    ));

    $history = $pdbACL->fetchTable($sql, TRUE, array($uid, 'history'));
    if (is_array($history) && count($history) >= 0)
    foreach (array_keys($history) as $i) {
        $history[$i]['name'] = _tr($history[$i]['name']); 
    } else $history = NULL;
    $smarty->assign(array(
        'SHORTCUT_HISTORY' => $history,
        'SHORTCUT_HISTORY_LABEL' => _tr('History'),
    ));
    
    return $smarty->fetch('_common/_shortcut.tpl');
}


function getWebDirModule($module_name)
{
    global $arrConf;

    //folder path for custom templates
    $base_dir = $arrConf['basePath'];;
    return "$base_dir/web/apps/$module_name";
}
?>
