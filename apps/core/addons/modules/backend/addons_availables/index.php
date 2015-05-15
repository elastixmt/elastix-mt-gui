<?php
/*
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-15                                             |
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
  $Id: index.php,v 1.1 2007/01/09 23:49:36 alex Exp $
*/

/************************************************************
El rango de porcentajes de progreso definido es el siguiente:

0% - 19%  acción reporefresh
20% - 39% acción depsolving
40% - 89% acción downloading
90% - 100% acción applying 
*************************************************************/
define('BOTTOM_LIMIT_REPOREFRESH',0);
define('BOTTOM_LIMIT_DEPSOLVING',20);
define('BOTTOM_LIMIT_DOWNLOADING',40);
define('BOTTOM_LIMIT_APPLYING',90);

require_once 'libs/JSON.php';

/*
 * Explicación de los estados y transiciones en el módulo Addon Market
 * 
 * Como parte del diseño del Addon Market, he decidido que el módulo tendrá las
 * siguientes propiedades:
 * - Invariabilidad frente a múltiples refrescos: sin importar el estado en que
 *   se encuentre el módulo, o qué operación esté realizando, debe ser posible
 *   cambiar a otro módulo de Elastix, y volver y encontrar al módulo en el mismo
 *   estado en que se dejó, o en un estado resultante de una transición.
 *   TODO: puede ser necesario agregar una manera de autoconfirmar transacción
 *   sin mandar a ejecutar 'confirm' una vez lista la lista de paquetes.
 * - Múltiples vistas coordinadas: varias sesiones administrativas logoneadas
 *   al mismo equipo deben poder ver el mismo estado de operación al momento de
 *   visitar el módulo, idealmente sin que tengan que refrescar manualmente
 *   cuando una cualquiera de ellas inicia una operación.
 *   TODO: puede ser necesario implementar un broadcast o aviso de refresco con
 *   tiempo/ID para que a través del demonio elxupdaterd, una sesión le avise
 *   a las demás de que ha hecho algo, probablemente modificar estado común.
 * - Persistencia frente a logout: incluso si se realiza logout de la sesión,
 *   el actualizador continúa trabajando. Luego de volver a hacer login, se debe
 *   poder ver al módulo en el mismo estado en que se encontraba, o en un estado
 *   resultante de una transición.
 * 
 * Componentes del estado del módulo de Addon Market:
 * - name_rpm: nombre del addon que se está instalando, o NULL. No se permite
 *   instalar más de un addon a la vez. Si está NULL, se asume estado ocioso.
 * - operacion: none, install, update, remove
 * - accion:  none, confirm, reporefresh, depsolving, downloading, applying, cancelling
 *   Las acciones listadas se corresponden con nombres de acciones de status
 *   del demonio elxupdaterd, excepto que no se usa checkinstalled.
 * - package: lista de paquetes que serán afectados por la operación, junto con
 *   operación, longitudes descargada/total, estado
 * 
 * Transiciones soportadas:
 * Todas las transiciones son notificadas por el método do_checkStatus. Las 
 * transiciones soportadas son
 * Para install y update:
 *  none->(reporefresh error)
 *  reporefresh->(depsolving error)
 *  depsolving->(confirm error)
 *  confirm->(downloading)
 *  downloading->(cancelling applying)
 *  applying->(none error)
 *  error->none 
 *  cancelling->none
 * Para remove:
 *  none->(reporefresh error)
 *  reporefresh->(depsolving error)
 *  depsolving->(confirm error)
 *  confirm->(applying)
 *  applying->(none error)
 *  error->none 
 * 
 * Determinación del estado actual:
 * - La interfaz invoca el listado de addons y asume que parte del estado ocioso.
 * - Se invoca do_checkStatus() con el estado almacenado en el navegador.
 * - Se consulta el estado según el demonio de actualización. Este estado lista
 *   el paquete que se está operando, y qué operación se hace. Por ejemplo:
 *   elastix-callcenter install
 * - Si se está haciendo una operación en un paquete, el estatus de operación
 *   indica más detalles. Se debe de recoger la lista de paquetes afectados.
 * - Si el estado del navegador (paquete y porcentaje) es idéntico al calculado,
 *   se espera hasta 2 minutos por algún cambio, verificando periódicamente
 *   el estatus de operación.
 * - Si el estado del navegador se vuelve distinto al calculado, se construye
 *   una respuesta JSON que incluye el nuevo estado para el navegador (nombre
 *   de RPM y porcentaje), y etiquetas de texto a actualizar.
 * - Al recibir el JSON, el navegador realiza las actualizaciones, y vuelve a
 *   lanzar do_checkStatus() para repetir el proceso.
 */

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    global $arrConfModule;

    $arrConf = array_merge($arrConf,$arrConfModule);

    $local_templates_dir = getWebDirModule($module_name);

	// Valores estáticos comunes a todas las operaciones
    $smarty->assign("module_name", $module_name);

    // Elegir la operación deseada
    $sAccion = getParameter('action');
    $listaAcciones = array(
        'listarAddonsHTML', // Página principal 
        'listarAddons',     // AJAX - listado de addons
	'iniciarInstallUpdate', // AJAX - inicio de instalación/actualización de un addon
	'checkStatus', // AJAX - se revisa el estado en la instalación/actualización o eliminación de un addon
	'clearYum', // AJAX - se hace un clear a Yum
	'deleteActionTmp', // AJAX - se elimina las acciones de la tabla action_tmp
	//'invalidarCacheAddons', // AJAX - se elimina la cache que almacena los addons
	'iniciarUninstall', // AJAX - inicio de desinstalación de un addon
	'getServerKey', // AJAX - se obtiene el Server Key
	'checkDependencies', // AJAX - se inicia la verificación de dependencias
	'cancelTransaction', // AJAX - cancela una transacción en progreso
        );
    if (!in_array($sAccion, $listaAcciones))
        $sAccion = $listaAcciones[0];
    $sAccion = 'do_'.$sAccion;
    return $sAccion($smarty, $module_name, $local_templates_dir);
}

/* Método que lista la base HTML del módulo. Actualmente lo único que hace es
 * mostrar la plantilla con la animación de espera. El refresco de HTML real 
 * se realiza en do_listarAddons. */
function do_listarAddonsHTML($smarty, $module_name, $local_templates_dir)
{
	$smarty->assign(array(
        'title'     	   =>  _tr('Addon Market'),
        'icon'      	   =>  'web/apps/'.$module_name.'/images/addons.png',
	'filter_by' 	   =>  _tr('Filter by'),
	'available' 	   =>  _tr('Available'),
	'installed' 	   =>  _tr('Installed'),
	'purchased' 	   =>  _tr('Purchased'),
	'update_available' =>  _tr('Update Available'),
	'name'		   =>  _tr('Name'),
	'showing'	   =>  _tr('Showing'),
	'of'		   =>  _tr('of')
    ));
    return $smarty->fetch($local_templates_dir.'/reporte_addons.tpl');
}

/* Método AJAX que lista los addons. Las tareas que realiza este módulo son:
 * - construir el HTML del listado de addons, incluyendo su versión y su estado
 *   de actualización.
 * - comunicar la información de progreso para el addon que se esté instalando.
 *   El detalle de este progreso se explica al inicio de este módulo.
 */
function do_listarAddons($smarty, $module_name, $local_templates_dir)
{
    global $arrConf;
    
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    $respuesta = array('action' => 'list');
    $filter_type = getParameter('filter_by');
    $filter_nameRpm = getParameter('filter_nameRpm');
    $oAddons = new paloSantoAddons();

    $respuesta['cancel_confirm'] = _tr("Are you sure you want to cancel this transaction?");

    // Consultar el número de addons
    $total = $oAddons->contarAddons($filter_type, $filter_nameRpm);
    if (is_null($total)) {
        $respuesta['action'] = 'error';
        $respuesta['message'] = 
            _tr('The system can not connect to the Web Service resource. Please check your Internet connection.').' '.
            $oAddons->getErrMsg();
        return $json->encode($respuesta);
    }
    $limit = 10;
    $offset = getParameter('offset');
    if (is_null($offset)) $offset = 0;
    if ($offset < 0) $offset = 0;
    if ($offset > $total - 1) $offset = $total - 1;
    $offset = (int)$offset;
    $offset -= ($offset % $limit);
    
    $respuesta['offset'] = $offset;
    $respuesta['limit'] = $limit;
    $respuesta['total'] = $total;
    
    // Preparación de la información de addons
    $listaAddons = $oAddons->listarAddons($filter_type, $limit, $offset, $filter_nameRpm);
    if (is_null($listaAddons)) {
        $respuesta['action'] = 'error';
        $respuesta['message'] = 
            _tr('The system can not connect to the Web Service resource. Please check your Internet connection.').
            $oAddons->errMsg;
        return $json->encode($respuesta);
    }
    if(count($listaAddons) <= 0){
	$respuesta['empty_addons'] = _tr("No addons match your search criteria");
	return $json->encode($respuesta);
    }
    $server_key = $oAddons->getSID();
    if(is_null($server_key))
	$server_key = "";
    else
	$server_key = "&serverkey=$server_key";
    $smarty->assign(array(
        'url_images'    =>  $arrConf['url_images'],
        'arrData'       =>  $listaAddons,
	'server_key'	=>  $server_key,
	'by'		=>  _tr('by'),
	'TRIAL'		=>  _tr('TRIAL'),
	'BUY'		=>  _tr('BUY'),
	'INSTALL'	=>  _tr('INSTALL'),
	'UPDATE'	=>  _tr('UPDATE'),
	'UNINSTALL'	=>  _tr('UNINSTALL'),
	'more_info'	=>  _tr('More info'),
	'location'	=>  _tr('Location'),
	'note'		=>  _tr('Note')
    ));
    $respuesta['addonlist_html'] = $smarty->fetch("$local_templates_dir/reporte_addons_lista.tpl");
    return $json->encode($respuesta);
}

function do_iniciarInstallUpdate($smarty, $module_name, $local_templates_dir)
{
    $name_rpm = getParameter("name_rpm");
    $json = new Services_JSON();
    Header('Content-Type: application/json');

    if (!preg_match("/^[\w-\.]+$/", $name_rpm)) {
        $respuesta["error"] = _tr("Invalid addon name");
        return $json->encode($respuesta);
    }

    $oAddons = new paloSantoAddons();
    $sid = $oAddons->getSID();
    if(is_null($sid)){
	$respuesta["error"] = _tr("Server id is not defined, you need to register your Elastix");
	return $json->encode($respuesta);
    }
    $oAddons->updateStatusCache();
    $status = $oAddons->getStatusCache();
    if($status["status"] == "busy"){
	$respuesta["error"] = _tr("Sorry, another transaction is already in process");
	return $json->encode($respuesta);
    }
    else{
	$respuesta["status"] = $oAddons->installAddon($name_rpm);
	$user = $_SESSION["elastix_user"];
	if(!$oAddons->saveActionTmp($name_rpm,"Installing/Updating", $user)){
	    $respuesta["db_error"] = $oAddons->getErrMsg();
	    return $json->encode($respuesta);
	}
	else{
	    $respuesta["title"] = _tr("Installing/Updating");
	    return $json->encode($respuesta);
	}
    }
}

function do_iniciarUninstall($smarty, $module_name, $local_templates_dir)
{
    $name_rpm = getParameter("name_rpm");
    $json = new Services_JSON();
    Header('Content-Type: application/json');

    if (!preg_match("/^[\w-\.]+$/", $name_rpm)) {
        $respuesta["error"] = _tr("Invalid addon name");
        return $json->encode($respuesta);
    }

    //Se busca si existe el archivo que indica las dependencias a desinstalar también
    if(file_exists("/usr/bin/$name_rpm-dependencies")){
	exec("/usr/bin/$name_rpm-dependencies",$output,$retval);
	if($retval == 0){
	    if(is_array($output) && count($output)>0){
		foreach($output as $dependency)
		    $name_rpm .= " $dependency";
	    }
	}
    }

    $oAddons = new paloSantoAddons();
    $oAddons->updateStatusCache();
    $status = $oAddons->getStatusCache();
    if($status["status"] == "busy"){
	$respuesta["error"] = _tr("Sorry, another transaction is already in process");
	return $json->encode($respuesta);
    }
    else{
	$respuesta["status"] = $oAddons->uninstallAddon($name_rpm);
	$user = $_SESSION["elastix_user"];
	if(!$oAddons->saveActionTmp($name_rpm,"Uninstalling", $user)){
	    $respuesta["db_error"] = $oAddons->getErrMsg();
	    return $json->encode($respuesta);
	}
	else{
	    $respuesta["title"] = _tr("Uninstalling");
	    return $json->encode($respuesta);
	}
    }
}

function do_checkDependencies($smarty, $module_name, $local_templates_dir)
{
    $name_rpm = getParameter("name_rpm");
    $json = new Services_JSON();
    Header('Content-Type: application/json');

    if (!preg_match("/^[\w-\.]+$/", $name_rpm)) {
        $respuesta["error"] = _tr("Invalid addon name");
        return $json->encode($respuesta);
    }

    $oAddons = new paloSantoAddons();
    $sid = $oAddons->getSID();
    if(is_null($sid)){
	$respuesta["error"] = _tr("Server id is not defined, you need to register your Elastix");
	return $json->encode($respuesta);
    }
    $oAddons->updateStatusCache();
    $status = $oAddons->getStatusCache();
    if($status["status"] == "busy"){
	$respuesta["error"] = _tr("Sorry, another transaction is already in process");
	return $json->encode($respuesta);
    }
    else{
	$respuesta["status"] = $oAddons->checkDependencies($name_rpm);
	$user = $_SESSION["elastix_user"];
	if(!$oAddons->saveActionTmp($name_rpm,"Checking Dependencies", $user)){
	    $respuesta["db_error"] = $oAddons->getErrMsg();
	    return $json->encode($respuesta);
	}
	else{
	    $respuesta["title"] = _tr("Checking Dependencies");
	    return $json->encode($respuesta);
	}
    }
}

function do_checkStatus($smarty, $module_name, $local_templates_dir)
{
    $json = new Services_JSON();
    Header('Content-Type: application/json');

    $oAddons = new paloSantoAddons();
    $oAddons->updateStatusCache();
    $status = $oAddons->getStatusCache();
    $respuesta["status"] = $status["status"];
    $action_tmp = $oAddons->getActionTmp();
    if(is_null($action_tmp)){
	$respuesta["db_error"] = $oAddons->getErrMsg();
	return $json->encode($respuesta);
    }
    if($status["status"] == "busy"){
	if(is_array($action_tmp) && count($action_tmp) > 0){
	    $respuesta["title"] = $action_tmp["action_rpm"]." ".$action_tmp["name_rpm"];
	    $respuesta["info"] = getInfoStatus($status["action"],$status["package"]);
	    $respuesta["action"] = $action_tmp["action_rpm"];
	    if($status["action"] == "cancelling")
		$respuesta["percentage"] = $action_tmp["init_time"]; // Se inicia el proceso de cancelación y el porcentaje queda estático
	    elseif($status["action"] != $action_tmp["data_exp"])
		$respuesta["percentage"] = getLimitPercentage($status["action"]); // Quiere decir que hubo un cambio de "step" o "paso" (de una acción a otra acción)
	    elseif($status["action"] == "downloading")
		$respuesta["percentage"] = getDownloadingPercentage($status["package"], $action_tmp["init_time"], $status["action"]); // Se obtiene el porcentaje de descarga, este es el único valor que se lo puede calcular con mayor precisión
	    else
		$respuesta["percentage"] = addPercentage($action_tmp["init_time"], $status["action"]); // Quiere decir que se encuentra refrescando repos, o resolviendo dependencias o instalando paquetes, para estos casos no se puede calcular un porcentaje por lo que se le va sumando de 1 en 1 hasta llegar al límite
	    if(!$oAddons->updateActionTmp($status["action"],$respuesta["percentage"])){
		$respuesta["db_error"] = $oAddons->getErrMsg();
		return $json->encode($respuesta);
	    }
	}
	// caso extraño en el que el demonio se encuentra en estado busy pero no se ha almacenado ninguna acción en la tabla action_tmp
	else{
	    $respuesta["percentage"] = getLimitPercentage($status["action"]);
	    $respuesta["title"] = _tr("Transaction");
	    $respuesta["info"] = getInfoStatus($status["action"],$status["package"]);
	    if($status["action"] == "downloading")
		$percentage = getDownloadingPercentage($status["package"], $respuesta["percentage"], $status["action"]);
	    else
		$percentage = $respuesta["percentage"] + 1;
	    if(!$oAddons->saveActionTmp("","Transaction","",$status["action"],$percentage)){
		$respuesta["db_error"] = $oAddons->getErrMsg();
		return $json->encode($respuesta);
	    }
	}
    }
    elseif($status["status"] == "error")
	$respuesta["error_description"] = $status["errmsg"];
    elseif($status["status"] == "idle" && is_array($action_tmp) && count($action_tmp) > 0){
        if(isset($status["warnmsg"][0]))
            $respuesta["warnmsg"] = _tr("Warning").": ".$status["warnmsg"][0];
        elseif($action_tmp["action_rpm"] == "Checking Dependencies")
            $respuesta["transaction_status"] = _tr("Addon")." $action_tmp[name_rpm] "._tr("has no problem with dependencies");
        else {
            $respuesta["transaction_status"] = _tr("Addon")." $action_tmp[name_rpm] "._tr("was successfully")." "._tr(getWordInPast($action_tmp["action_rpm"]));
            
            // Para addon instalado, se debe borrar la cache de permisos de usuario
            if (isset($_SESSION['elastix_user_permission']))
                unset($_SESSION['elastix_user_permission']);
        }
    }
    return $json->encode($respuesta);
}

function getWordInPast($word)
{
    $word = explode("/",$word);
    $past = array();
    foreach($word as $value)
	$past[] = preg_replace("/ing$/i","ed",$value);
    return implode("/",$past);
}

function getLimitPercentage($action)
{
    switch($action){
	case "depsolving":
	    $percentage = BOTTOM_LIMIT_DEPSOLVING;
	    break;
	case "downloading":
	    $percentage = BOTTOM_LIMIT_DOWNLOADING;
	    break;
	case "applying":
	    $percentage = BOTTOM_LIMIT_APPLYING;
	    break;
	default:
	    $percentage = BOTTOM_LIMIT_REPOREFRESH;
	    break;
    }
    return $percentage;
}

function getDownloadingPercentage($packages, $percentage, $action)
{
    $size = 0;
    $downloaded = 0;
    foreach($packages as $package){
	if(!is_null($package["longitud"]))
	    $size += $package["longitud"];
	if(!is_null($package["descargado"]))
	    $downloaded += $package["descargado"];
    }
    if($size == 0)
	return addPercentage($percentage, $action); // Se desconoce del tamaño de los paquetes, se procede a sumar de 1 en 1
    else
	return floor(BOTTOM_LIMIT_DOWNLOADING + ((BOTTOM_LIMIT_APPLYING - BOTTOM_LIMIT_DOWNLOADING)*$downloaded/$size));
}

function addPercentage($percentage, $action)
{
    switch($action){
	case "depsolving":
	    if($percentage < BOTTOM_LIMIT_DOWNLOADING - 1)
		$percentage++;
	    break;
	case "downloading":
	    if($percentage < BOTTOM_LIMIT_APPLYING - 1)
		$percentage++;
	    break;
	case "applying":
	    if($percentage < 99)
		$percentage++;
	    break;
	default:
	    if($percentage < BOTTOM_LIMIT_DEPSOLVING - 1)
		$percentage++;
	    break;
    }
    return $percentage;
}

function getInfoStatus($action, $packages)
{
    $info = "";
    switch($action){
	case "depsolving":
	    $info = _tr("Resolving dependencies");
	    break;
	case "downloading":
	    foreach($packages as $package){
		if($package["currstatus"] == "downloading"){
		    $info = _tr("Downloading package")." ".$package["nombre"];
		    if(!is_null($package["longitud"]) && !is_null($package["descargado"])){
			if(strlen(round($package["descargado"]/1024)) < 4)
			    $info .= " ".round($package["descargado"]/1024,2)."KB/";
			else
			    $info .= " ".round($package["descargado"]/1024)."KB/";
			if(strlen(round($package["longitud"]/1024)) < 4)
			    $info .= round($package["longitud"]/1024,2)."KB";
			else
			    $info .= round($package["longitud"]/1024)."KB";
		    }
		    break;
		}
	    }
	    break;
	case "applying":
	    foreach($packages as $package){
		if($package["currstatus"] == "installing"){
		    $info = _tr("Installing package")." ".$package["nombre"];
		    break;
		}
		elseif($package["pkgaction"] == "remove"){
		    $info = _tr("Removing package")." ".$package["nombre"];
		}
	    }
	    break;
	case "cancelling":
	    $info = _tr("Cancelling. This process could take several minutes, please wait");
	    break;
	default:
	    $info = _tr("Refreshing repos");
	    break;
    }
    if($info == "")
	$info = _tr("Please wait...");
    return $info;
}

function do_clearYum($smarty, $module_name, $local_templates_dir)
{
    $json = new Services_JSON();
    Header('Content-Type: application/json');

    $oAddons = new paloSantoAddons();
    $oAddons->clearYum();
    return $json->encode(NULL);
}

function do_deleteActionTmp($smarty, $module_name, $local_templates_dir)
{
    $json = new Services_JSON();
    Header('Content-Type: application/json');

    $oAddons = new paloSantoAddons();
    $oAddons->deleteActionTmp();
    return $json->encode(NULL);
}

function do_getServerKey($smarty, $module_name, $local_templates_dir)
{
    $json = new Services_JSON();
    Header('Content-Type: application/json');

    $oAddons = new paloSantoAddons();
    $respuesta["server_key"] = $oAddons->getSID();
    return $json->encode($respuesta);
}

function do_cancelTransaction($smarty, $module_name, $local_templates_dir)
{
    $json = new Services_JSON();
    Header('Content-Type: application/json');

    $oAddons = new paloSantoAddons();
    $oAddons->updateStatusCache();
    $status = $oAddons->getStatusCache();
    if($status["action"] == "applying"){
	$respuesta["error"] = _tr("Sorry, you can not cancel the transaction on this stage");
	return $json->encode($respuesta);
    }
    elseif($status["action"] == "cancelling"){
	$respuesta["error"] = _tr("The cancel signal was already sent, this could take several minutes, please wait");
	return $json->encode($respuesta);
    }
    else{
	$respuesta = $oAddons->cancelTransaction();
	if(!$oAddons->updateActionTmp("Cancelling",NULL,"Cancelling")){
	    $respuesta["db_error"] = $oAddons->getErrMsg();
	    return $json->encode($respuesta);
	}
	else
	    return $json->encode($respuesta);
    }
}
?>
