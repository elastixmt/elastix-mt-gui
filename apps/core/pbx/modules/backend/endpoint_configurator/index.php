<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0                                                  |
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
  $Id: index.php,v 1.1 2008/01/04 10:39:57 bmacias Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    //include elastix framework
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoValidar.class.php";
    include_once "libs/paloSantoConfig.class.php";
    include_once "libs/paloSantoJSON.class.php";
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/misc.lib.php";
    include_once "libs/paloSantoNetwork.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoEndPoint.class.php";
    include_once "modules/$module_name/libs/paloSantoFileEndPoint.class.php";

    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $pConfig     = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrAMP      = $pConfig->leer_configuracion(false);
    $dsnAsterisk = $arrAMP['AMPDBENGINE']['valor']."://".
                   $arrAMP['AMPDBUSER']['valor']. ":".
                   $arrAMP['AMPDBPASS']['valor']. "@".
                   $arrAMP['AMPDBHOST']['valor'];
    $dsnSqlite   = $arrConfModule['dsn_conn_database_1'];

    $accion = getAction();
    $content = "";

    // Asegurarse de que el arreglo siempre exista, aunque esté vacío
    if (!isset($_SESSION['elastix_endpoints']))
        $_SESSION['elastix_endpoints'] = array();

    switch($accion){
        case "endpoint_scan":
            $content = endpointScan($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
            break;
        case "endpoint_set":
            $content = endpointConfiguratedSet($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
            break;
        case "endpoint_unset":
            $content = endpointConfiguratedUnset($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
            break;
        case "getDevices":
            $content = getDevices($dsnAsterisk,$dsnSqlite);
            break;
        case "patton_data":
            $content = getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
            break;
        case "vega_data":
            $content = getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
            break;
        case "next_1":
            $content = getExtensionsForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, false);
            break;
        case "next_1_vega":
            $content = getExtensionsVegaForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, false);
            break;
        case "next_2":
            $content = getLinesForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, false);
            break;
        case "next_2_vega":
            $content = getLinesVegaForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, false);
            break;
        case "save":
            $content = savePatton($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
            break;
        case "save_vega":
            $content = saveVega($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
            break;
        case "return2":
            $content = getExtensionsForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, true);
            break;
        case "return2_vega":
            $content = getExtensionsVegaForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, true);
            break;
        default: // endpoint_show
            $content = buildReport($_SESSION['elastix_endpoints'], $smarty, $module_name, network());
            break;
    }
    return $content;
}

function endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf)
{
    $arrData = array();
    if(!isset($_SESSION['elastix_endpoints']) || !is_array($_SESSION['elastix_endpoints']) || empty($_SESSION['elastix_endpoints'])){
       
        $paloEndPoint        = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
        $arrEndpointsConf    = $paloEndPoint->listEndpointConf();
        $arrVendor           = $paloEndPoint->listVendor();
        $arrDeviceFreePBX    = $paloEndPoint->getDeviceFreePBX();
        $arrDeviceFreePBXAll = $paloEndPoint->getDeviceFreePBX(true);
        $endpoint_mask       = isset($_POST['endpoint_mask'])?$_POST['endpoint_mask']:network();
        $_SESSION["endpoint_mask"] = $endpoint_mask;
        $paloFileEndPoint = new PaloSantoFileEndPoint($arrConf["tftpboot_path"],$_SESSION["endpoint_mask"]);
        $pValidator          = new PaloValidar();

        if(!$pValidator->validar('endpoint_mask', $endpoint_mask, 'ip/mask')){
            $smarty->assign("mb_title",_tr('ERROR').":");
            $strErrorMsg = "";
            if(is_array($pValidator->arrErrores) && count($pValidator->arrErrores) > 0){
                foreach($pValidator->arrErrores as $k=>$v) {
                    $strErrorMsg .= "$k, ";
                }
            }
            $smarty->assign("mb_message",_tr('Invalid Format in Parameter').": ".$strErrorMsg);
        }else{

            $pattonDevices    = $paloEndPoint->getPattonDevices();
            $arrEndpointsMap  = $paloEndPoint->endpointMap($endpoint_mask,$arrVendor,$arrEndpointsConf,$pattonDevices);

            if($arrEndpointsMap==false){
                $smarty->assign("mb_title",_tr('ERROR').":");
                $smarty->assign("mb_message",$paloEndPoint->errMsg);
            }

            if(is_array($arrEndpointsMap) && count($arrEndpointsMap)>0){
		$cont=0;
                foreach($arrEndpointsMap as $key => $endspoint){
		    $flag=0;
                    $cont++;
                    if(isset($endspoint['model_no']) && $endspoint['model_no'] != ""){
                        if($paloEndPoint->modelSupportIAX($endspoint['model_no']))
                            $comboDevices = combo($arrDeviceFreePBXAll,$endspoint['account']);
                        else
                            $comboDevices = combo($arrDeviceFreePBX,$endspoint['account']);
                    }
                    else
                        $comboDevices = combo(array("Unselected" => _tr("Unselected")),"");
                    if($endspoint['configurated']){
                        $unset  = "<input type='checkbox' name='epmac_{$endspoint['mac_adress']}'  />";
                        $report = $paloEndPoint->compareDevicesAsteriskSqlite($endspoint['account']);
                    }
                    else{
                        $unset  = "";
                    }
                    if($endspoint['desc_vendor'] == "Unknown")
                        $endspoint['desc_vendor'] = $paloEndPoint->getDescription($endspoint['name_vendor']);
                    $macWithout2Points = str_replace(":","",$endspoint['mac_adress']);
                    $currentExtension = $paloEndPoint->getExtension($endspoint['ip_adress']);

                    if($endspoint["name_vendor"] == "Patton"){
                        $arrTmp[0] = "";
                        $arrTmp[1] = "";
                        $arrTmp[5] = $endspoint["model_no"];
                        $arrTmp[6] = "<a href='?menu=$module_name&action=patton_data&mac=$endspoint[mac_adress]'>"._tr("Data Configuration")."</a>";
                        $configured = false;
                        foreach($arrEndpointsConf as $arrConf){
                            if(in_array($endspoint["mac_adress"],$arrConf)){
                                $configured = true;
                                break;
                            }
                        }
                        if($configured)
                            $arrTmp[7] = "<font color = 'green'>"._tr("Configured")."</font>";
                        else
                            $arrTmp[7] = _tr("Not Configured");
                        $_SESSION['endpoint_model'][$endspoint['mac_adress']] = $endspoint["model_no"];
                    }
            elseif($endspoint["name_vendor"] == "Sangoma"){
            
                        $arrTmp[0] = "";
                        $arrTmp[1] = "";
                       // $arrTmp[5] = $endspoint["model_no"];
            //  $arrTmp[5] = "<select name='id_model_device_{$endspoint['mac_adress']}' onchange='getDevices(this,\"$macWithout2Points\");'>".combo($paloEndPoint->getAllModelsVendor($endspoint['name_vendor']),$endspoint['model_no'])."</select>";
            $arrPorts = $paloFileEndPoint->getSangomaPorts($endspoint['ip_adress'],$endspoint["mac_adress"],$dsnAsterisk, $dsnSqlite,2);
                        
                         if($arrPorts==null){ $arrPorts['fxo']="?"; $arrPorts['fxs']="?";}
                         
                        
                        $arrTmp[5] = $paloFileEndPoint->getSangomaModel($endspoint['ip_adress'],$endspoint["mac_adress"],$dsnAsterisk, $dsnSqlite,1)."FXO: ".$arrPorts['fxo']. " FXS: ".$arrPorts['fxs'];
                        $arrTmp[6] = "<a href='?menu=$module_name&action=vega_data&mac=$endspoint[mac_adress]'>"._tr("Data Configuration")."</a>";
                        $configured = false;
                        foreach($arrEndpointsConf as $arrConf){
                            if(in_array($endspoint["mac_adress"],$arrConf)){
                                $configured = true;
                                break;
                            }
                        }
                        if($configured)
                            $arrTmp[7] = "<font color = 'green'>"._tr("Configured")."</font>";
                        else
                            $arrTmp[7] = _tr("Not Configured");
                        $_SESSION['endpoint_model'][$endspoint['mac_adress']] = $endspoint["model_no"];
                    }
                    else{
			if($endspoint["name_vendor"] == "Grandstream"){
			 $arr = $paloFileEndPoint->getModelElastix("admin","admin",$endspoint['ip_adress'],2);
			 if($arr){
			       $flag=1;
			       $endpointElastix = $paloEndPoint->getVendorByName("Elastix");
			       $endspoint['name_vendor'] = $endpointElastix["name"]; 
			       $endspoint['desc_vendor'] = $endpointElastix["description"];
			       $endspoint['id_vendor']	 = $endpointElastix["id"];
			 }
			}
            //Bloque agregado para manejar ciertos telefonos Voptech ya que tienen la misma porcion de MAC de vendor que Fanvil         		
            if($endspoint["name_vendor"] == "Fanvil"){	
				$var = $paloFileEndPoint->isVendorVoptech("admin","admin",$endspoint['ip_adress'],2);
				if($var){
					$endpointVoptech = $paloEndPoint->getVendorByName("Voptech");
					$endspoint['name_vendor'] = $endpointVoptech["name"];
					$endspoint['desc_vendor'] = $endpointVoptech["description"];
					$endspoint['id_vendor']	 = $endpointVoptech["id"];
				}
			}
                        $arrTmp[0] = "<input type='checkbox' name='epmac_{$endspoint['mac_adress']}'  />";
                        $arrTmp[1] = $unset;
                        $arrTmp[5] = "<select name='id_model_device_{$endspoint['mac_adress']}' onchange='getDevices(this,\"$macWithout2Points\");'>".combo($paloEndPoint->getAllModelsVendor($endspoint['name_vendor']),$endspoint['model_no'])."</select>";
                        $arrTmp[6] = "<select name='id_device_{$endspoint['mac_adress']}' id='id_device_$macWithout2Points'   >$comboDevices</select>";
                        if($currentExtension != "Not Registered")
                            $arrTmp[7] = "<font color = 'green'>$currentExtension</font>";
                        else
                            $arrTmp[7] = $currentExtension;
                    }
                   
                    $arrTmp[2] = $endspoint['mac_adress'];
                    //$arrTmp[3] = "<div class='chkbox' id=".$cont." style='width:135px;'><div class='resp_".$cont."'><a href='http://{$endspoint['ip_adress']}/' target='_blank' id='a_".$cont."' style='float:left;'>{$endspoint['ip_adress']}</a><input type='hidden' name='ip_adress_endpoint_{$endspoint['mac_adress']}' id='hid_".$cont."' value='{$endspoint['ip_adress']}' />"."<input type='checkbox' id='chk_".$cont."' name='{$endspoint['mac_adress']}' style='margin-top:1px;' /></div></div>";
                    $arrTmp[3] = "<a href='http://{$endspoint['ip_adress']}/' target='_blank'>{$endspoint['ip_adress']}</a><input type='hidden' name='ip_adress_endpoint_{$endspoint['mac_adress']}' value='{$endspoint['ip_adress']}' />";
                    $arrTmp[4] = $endspoint['name_vendor']." / ".$endspoint['desc_vendor']."&nbsp;<input type='hidden' name='id_vendor_device_{$endspoint['mac_adress']}' value='{$endspoint['id_vendor']}' />&nbsp;<input type='hidden' name='name_vendor_device_{$endspoint['mac_adress']}' value='{$endspoint['name_vendor']}' />";
		
                    $arrData[] = $arrTmp;
                    $_SESSION["endpoint_ip"][$endspoint['mac_adress']] = $endspoint['ip_adress'];
                }
                $_SESSION['elastix_endpoints'] = $arrData;
                $_SESSION['grid'] = $arrData;
                //Lo guardo en la session para hacer mucho mas rapido el proceso
                //de configuracion de los endpoint. Solo la primera vez corre el
                //comado nmap y cuando quiera el usuario correrlo de nuevo lo debe
                //hacer por medio del boton Discover Endpoints in this Network, ahi de nuevo vuelve a
                //construir el arreglo $arrData.
            }
        }
    }
    else{
        $arrData = $_SESSION['elastix_endpoints'];
    }
    if(!isset($endpoint_mask))
        $endpoint_mask = network();
    return buildReport($arrData,$smarty,$module_name, $endpoint_mask);
}

function getDevices($dsnAsterisk,$dsnSqlite)
{
    $jsonObject    = new PaloSantoJSON();
    $paloEndPoint  = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $idModel       = getParameter("id_model");
    if($idModel == "unselected")
        $jsonObject->set_message(array("Unselected" => _tr("Unselected")));
    else{
        $iaxSupport        = $paloEndPoint->modelSupportIAX($idModel);
        if($iaxSupport === null)
            $jsonObject->set_error("yes");
        else{
            if($iaxSupport)
                $jsonObject->set_message($paloEndPoint->getDeviceFreePBX(true));
            else
                $jsonObject->set_message($paloEndPoint->getDeviceFreePBX());
        }
    }
    return $jsonObject->createJSON();
}

function buildReport($arrData, $smarty, $module_name, $endpoint_mask)
{
    if(getParameter("cancel"))
        unset($_SESSION["endpoint_configurator"]);
    $nav = (isset($_GET['nav']) && $_GET['nav'] != '')
        ? $_GET['nav']
        : ((isset($_GET['navpost']) && $_GET['navpost'] != '')
            ? $_GET['navpost'] : NULL);
    $start = (isset($_GET['start']) && $_GET['start'] != '')
        ? $_GET['start']
        : ((isset($_GET['startpost']) && $_GET['startpost'] != '')
            ?$_GET['startpost'] : NULL);

    $ip = $_SERVER['SERVER_ADDR'];
    $devices = subMask($ip);
    $limit  = 20;
    $total  = count($arrData);
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    //$offset = $oGrid->getOffSet($limit,$total,$nav,$start);
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    if($devices<=20){
       $devices = pow(2,(32-$devices));
       $devices = $devices - 2;
       $smarty->assign("mb_title",_tr('WARNING').":");
       $smarty->assign("mb_message",_tr("It can take several minutes, because your ip address has some devices, ").$devices._tr("hosts"));
    }

    if ($total <= $limit)
        $arrDataPorcion = $arrData;
    else $arrDataPorcion = array_slice($arrData, $offset, $limit);

    $arrGrid = array("title"    => _tr("Endpoint Configurator"),
        "url"      => array(
            'menu' => $module_name,
            'navpost' => $nav,
            'startpost' => $start,
            ),
        "icon"     => "/modules/$module_name/images/pbx_endpoint_configurator.png",
        "width"    => "99%",
        "start"    => ($total==0) ? 0 : $offset + 1,
        "end"      => $end,
        "total"    => $total,
        "columns"  => array(0 => array("name"      => "<input type='submit' name='endpoint_set' value='"._tr('Set')."' class='button' onclick=\" return confirmSubmit('"._tr("Are you sure you wish to set endpoint(s)?")."');\" />",
                                       "property1" => ""),
                            1 => array("name"      => "<input type='submit' name='endpoint_unset' value='"._tr('Unset')."' class='button' onclick=\" return confirmSubmit('"._tr("Are you sure you wish to unset endpoint(s)?")."');\" />",
                                       "property1" => ""),
                            2 => array("name"      => _tr("MAC Adress"),
                                       "property1" => ""),
                            3 => array("name"      => _tr("IP Adress"),
                                       "property1" => ""),
                            4 => array("name"      => _tr("Vendor"),
                                       "property1" => ""),
                            5 => array("name"      => _tr("Phone Type"),
                                       "property1" => ""),
                            6 => array("name"      => _tr("User Extension"),
                                       "property1" => ""),
                            7 => array("name"      => _tr("Current Extension"),
                                       "property1" => "")));
   /* $html_filter = "<input type='submit' name='endpoint_scan' value='"._tr('Discover Endpoints in this Network')."' class='button' />";
    $html_filter.= "&nbsp;&nbsp;<input type='text' name='endpoint_mask' value='$endpoint_mask' style='width:130px;' />";*/
    $oGrid->addInputTextAction("endpoint_mask",_tr('Discover Endpoints in this Network'),$endpoint_mask,'endpoint_scan');
    //$oGrid->showFilter($html_filter,true);
    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrDataPorcion);
    return $contenidoModulo;
}

function endpointScan($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf)
{
    unset($_SESSION['elastix_endpoints']);
    return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
}

function endpointConfiguratedSet($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf)
{
    $paloEndPoint     = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $paloFileEndPoint = new PaloSantoFileEndPoint($arrConf["tftpboot_path"],$_SESSION["endpoint_mask"]);
    $arrFindVendor    = array(); //variable de ayuda, para llamar solo una vez la funcion createFilesGlobal de cada vendor
    $valid = validateParameterEndpoint($_POST, $module_name,$dsnAsterisk,$dsnSqlite);
    $count = 0;
    $error = "";
    if($valid!=false){
        $smarty->assign("mb_title",_tr('ERROR').":");
        $smarty->assign("mb_message",$valid);
        $endpoint_mask = isset($_POST['endpoint_mask'])?$_POST['endpoint_mask']:network();

        return buildReport($_SESSION['elastix_endpoints'],$smarty,$module_name, $endpoint_mask);
    }
   
    foreach($_POST as $key => $values){
        if(substr($key,0,6) == "epmac_"){ //encontre una mac seleccionada entoces por forma empirica con ayuda del mac_adress obtego los parametros q se relacionan con esa mac.
            $tmpMac = substr($key,6);

            $count++;
            $tech   = $paloEndPoint->getTech($_POST["id_device_$tmpMac"]);
            $freePBXParameters = $paloEndPoint->getDeviceFreePBXParameters($_POST["id_device_$tmpMac"],$tech);

            $tmpEndpoint['id_device']   = $freePBXParameters['id_device'];
            $tmpEndpoint['desc_device'] = $freePBXParameters['desc_device'];
            $tmpEndpoint['account']     = $freePBXParameters['account_device'];
            $tmpEndpoint['secret']      = $freePBXParameters['secret_device'];
            $tmpEndpoint['id_model']    = $_POST["id_model_device_$tmpMac"];
            $tmpEndpoint['mac_adress']  = $tmpMac;
            $tmpEndpoint['id_vendor']   = $_POST["id_vendor_device_$tmpMac"];
            $tmpEndpoint['name_vendor'] = $_POST["name_vendor_device_$tmpMac"];
            $tmpEndpoint['ip_adress']   = $_POST["ip_adress_endpoint_$tmpMac"];
            $tmpEndpoint['comment']     = "Nada";

            //Variables usadas para parametros extras
            $name_model = $paloEndPoint->getModelById($tmpEndpoint['id_model']);
            $arrParametersOld = $paloEndPoint->getParameters($tmpEndpoint['mac_adress']);
            $arrParameters = $paloFileEndPoint->updateArrParameters($tmpEndpoint['name_vendor'], $name_model, $arrParametersOld);
            $tmpEndpoint['arrParameters']=$arrParameters;

            if($paloEndPoint->createEndpointDB($tmpEndpoint)){
                //verifico si la funcion createFilesGlobal del vendor ya fue ejecutado
                if(!in_array($tmpEndpoint['name_vendor'],$arrFindVendor)){
                    if($paloFileEndPoint->createFilesGlobal($tmpEndpoint['name_vendor']))
                        $arrFindVendor[] = $tmpEndpoint['name_vendor'];
                }
                //escribir archivos
                $ArrayData['vendor'] = $tmpEndpoint['name_vendor'];
                $ArrayData['data'] = array(
                        "filename"     => strtolower(str_replace(":","",$tmpMac)),
                        "DisplayName"  => $tmpEndpoint['desc_device'],
                        "id_device"    => $tmpEndpoint['id_device'],
                        "secret"       => $tmpEndpoint['secret'],
                        "model"        => $name_model,
                        "ip_endpoint"  => $tmpEndpoint['ip_adress'],
                        "arrParameters"=> $tmpEndpoint['arrParameters'],
                        "tech"         => $tech
                        );
		
                //Falta si hay error en la creacion de un archivo, ya esta para saber q error es, el problema es como manejar un error o los errores dentro del este lazo (foreach).
                //ejemplo: if($paloFile->createFiles($ArrayData)==false){ $paloFile->errMsg  (mostrar error con smarty)}
		
                if(!$paloFileEndPoint->createFiles($ArrayData)){
                    if(isset($paloFileEndPoint->errMsg))
                        $error .= $paloFileEndPoint->errMsg."<br />";
                }
            }
        }
    }
    if($count > 0 && $error == ""){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message", _tr("The Extension(s) parameters have been saved. Each checked phone will be configured with the new parameters once it has finished rebooting"));
    }
    elseif(isset($error) && $error != ""){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $message = _tr("The following errors ocurred").":<br />".$error;
        $smarty->assign("mb_message",$message);
    }
    unset($_SESSION['elastix_endpoints']);
    return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
}

function validateParameterEndpoint($arrParameters, $module_name, $dsnAsterisk, $dsnSqlite)
{
    // Listar todos los proveedores disponibles
    $base_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $sVendorCfgDir = "$base_dir/modules/$module_name/libs/vendors";
    if (!is_dir($sVendorCfgDir)) {
        return _tr('Vendor configuration directory not found!');
    }
    $h = opendir($sVendorCfgDir);
    $vendorList = array();
    while (($s = readdir($h)) !== false) {
        $regs = NULL;
        if (preg_match('/^(.+)\.cfg\.php/', $s, $regs)) $vendorList[] = $regs[1];
    }
    closedir($h);

    $paloEndPoint = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $arrDeviceFreePBX    = $paloEndPoint->getDeviceFreePBX();
    $arrDeviceFreePBXAll = $paloEndPoint->getDeviceFreePBX(true);
    $error = false;
    foreach($arrParameters as $key => $values){
        if(substr($key,0,6) == "epmac_"){ //encontre una mac seleccionada entoces por forma empirica con ayuda del mac_adress obtego los parametros q se relacionan con esa mac.
            $tmpMac    = substr($key,6);
            $macExists = false;
            foreach($_SESSION["elastix_endpoints"] as $endpoint){
                if($endpoint[2] == $tmpMac){
                    $macExists = true;
                    break;
                }
            }
            if(!$macExists)
                $error .= _tr("The mac was not found").": $tmpMac<br />";
            else{
                // Revisar que la subcadena sea realmente una dirección MAC
                if (!preg_match('/^((([[:xdigit:]]){2}:){5}([[:xdigit:]]){2})$/i', $tmpMac))
                    $error .= "Invalid MAC address for endpoint<br />";

                $tmpDevice       = $arrParameters["id_device_$tmpMac"];
                $tmpModel        = $arrParameters["id_model_device_$tmpMac"];
                $tmpVendor       = $arrParameters["name_vendor_device_$tmpMac"];
                $tmpidVendor     = $arrParameters["id_vendor_device_$tmpMac"];
                $tmpIpAddress    = $arrParameters["ip_adress_endpoint_$tmpMac"];
                $tmpModelsVendor = $paloEndPoint->getAllModelsVendor($tmpVendor);
                if(!array_key_exists($tmpModel,$tmpModelsVendor))
                    $error .= "The model entered does not exist or does not belong to this vendor. <br />";
                
                if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $tmpIpAddress)) {
                    $error .= "Invalid IP address. <br />";
                }
                
                if ($tmpVendor == "Elastix") 
		        {
		            $endpointElastix = $paloEndPoint->getVendorByName("Grandstream");
		            $tmpVendor	     = $endpointElastix["name"];
		            $tmpidVendor     = $endpointElastix["id"];
		        }
                if ($tmpVendor == "Voptech") 
		        {   
                    $endpointElastix = $paloEndPoint->getVendorByName("Fanvil");
                    if(substr($tmpMac,0,8) == $paloEndPoint->getMac($endpointElastix["id"])){		                
		                $tmpVendor	     = $endpointElastix["name"];
		                $tmpidVendor     = $endpointElastix["id"];
                    }
                }
		$dataVendor = $paloEndPoint->getVendor(substr($tmpMac,0,8));
		
                if(!isset($dataVendor["name"]) || $dataVendor["name"] != $tmpVendor || !isset($dataVendor["id"]) || $dataVendor["id"] != $tmpidVendor)
                    $error .= "The id or/and name of vendor do not match with the mac address. <br />";
                if(isset($tmpModel) && $tmpModel != ""){
                    if($paloEndPoint->modelSupportIAX($tmpModel)){
                        $comboDevices = combo($arrDeviceFreePBXAll,$tmpDevice);
                        if(!array_key_exists($tmpDevice,$arrDeviceFreePBXAll))
                            $error .= "The assigned User Extension does not exist or is not allowed. <br />";
                    }
                    else{
                        $comboDevices = combo($arrDeviceFreePBX,$tmpDevice);
                        if(!array_key_exists($tmpDevice,$arrDeviceFreePBX))
                            $error .= "The assigned User Extension does not exist or is not allowed. <br />";
                    }
                }
                else
                    $comboDevices = combo(array("Select a model" => _tr("Select a model")),"");

                if($tmpDevice == "unselected" || $tmpDevice == "no_device" || $tmpModel == "unselected" || $tmpDevice == "Select a model") //el primero que encuentre sin seleccionar mantiene el error
                    $error .= "The mac adress $tmpMac unselected Phone Type or User Extension. <br />";

                // Revisar que el vendedor es uno de los vendedores conocidos
                if (!in_array($tmpVendor, $vendorList))
                    $error .= "Invalid or unsupported vendor<br />";

                $macWithout2Points = str_replace(":","",$tmpMac);
                //PASO 2: Recorro el arreglo de la sesion para modificar y mantener los valores q el usuario ha decidido elegir asi cuando halla un error los datos persisten.
                if(isset($_SESSION['elastix_endpoints'])){
                    foreach($_SESSION['elastix_endpoints'] as &$data){//tomo la referencia del elemento para poder modificar su contenido por referencia.
                        if($data[2]==$tmpMac){
                            $data[0] = "<input type='checkbox' name='epmac_$tmpMac' checked='checked' />";
                            $data[5] = "<select name='id_model_device_$tmpMac' onchange='getDevices(this,\"$macWithout2Points\");'>".combo($tmpModelsVendor,$tmpModel)."</select>";
                            $data[6] = "<select name='id_device_$tmpMac' id='id_device_$macWithout2Points'>".$comboDevices."</select>";
                        }
                    }
                }
            }
        }
    }
    return $error;
}

function endpointConfiguratedUnset($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf)
{
    $paloEndPoint = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $arrEndpoint = array();
    $strError = "";
    if(is_array($_POST) && count($_POST)>0){
        foreach($_POST as $key => $value){
            if(substr($key,0,6)=="epmac_"){
                $tmpMac = substr($key,6);
                $macExists = false;
                foreach($_SESSION["elastix_endpoints"] as $endpoint){
                    if($endpoint[2] == $tmpMac){
                        $macExists = true;
                        break;
                    }
                }
                if(!$macExists)
                    $strError .= _tr("The mac was not found").": $tmpMac<br />";
                else{
                    $tmpEndpoint['id_model']    = $_POST["id_model_device_$tmpMac"];
                    if($paloEndPoint->deleteEndpointsConf($tmpMac)){
                        $paloFile = new paloSantoFileEndPoint($arrConf["tftpboot_path"]);
                        $name_model = $paloEndPoint->getModelById($tmpEndpoint['id_model']);

                        $ArrayData['vendor'] = $_POST["name_vendor_device_$tmpMac"];
                        $ArrayData['data'] = array(
                                    "filename"     => strtolower(str_replace(":","",$tmpMac)),
                                    "model"        => $name_model);

                        //Falta si hay error en la eliminacion de un archivo, ya esta para saber q error es, el problema es como manejar un error o los errores dentro del este lazo (foreach).
                        //ejemplo: if($paloFile->deleteFiles($ArrayData)==false){ $paloFile->errMsg  (mostrar error con smarty)}
                        $paloFile->deleteFiles($ArrayData);
                    }
                }
            }
        }
    }
    if($strError != ""){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", $strError);
    }
    unset($_SESSION['elastix_endpoints']);
    return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    //header("Location: /?menu=$module_name");
}

function createStatus($type,$text)
{
    if($type==1)//Configurado sin novedad.
        return "<label style='color:green' >$text</label>";
    else if($type==2)//No configurado aun
        return "<label style='color:orange'>$text</label>";
    else if($type==3)//Configurado pero hay cambios, en el freepbx cambio y en el endpoint aun no.
        return "<label style='color:red'  >$text</label>";
}

function network()
{
    /* OJO: paloNetwork::getNetAdress() ha sido reescrito y es ahora una función
     * estática. Si PHP se queja de que la función no puede llamarse en contexto
     * estático, NO PARCHE AQUí. En su lugar, actualice a 
     * elastix-system-2.3.0-10 o superior. El spec de elastix-pbx ya tiene este
     * requerimiento mínimo. */
    $ip = $_SERVER['SERVER_ADDR'];
    $total = subMask($ip);
    return paloNetwork::getNetAdress($ip, $total)."/".$total;    
}

function subMask($ip)
{
    $output = NULL;
    exec('/sbin/ip addr', $output);
    /*
    [root@picosam ~]# ip addr show
    1: lo: <LOOPBACK,UP,LOWER_UP> mtu 16436 qdisc noqueue state UNKNOWN 
        link/loopback 00:00:00:00:00:00 brd 00:00:00:00:00:00
        inet 127.0.0.1/8 scope host lo
        inet6 ::1/128 scope host 
           valid_lft forever preferred_lft forever
    2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UNKNOWN qlen 1000
        link/ether 7a:35:22:cd:57:98 brd ff:ff:ff:ff:ff:ff
        inet 192.168.5.130/16 brd 192.168.255.255 scope global eth0
        inet6 fe80::7835:22ff:fecd:5798/64 scope link 
           valid_lft forever preferred_lft forever
     */
    foreach ($output as $s) {
        $regs = NULL;
        if (preg_match('|inet (\d+.\d+.\d+.\d+)/(\d+)|', $s, $regs)) {
            if ($regs[1] == $ip) return (int)$regs[2];
        }
    }
    return 32;  // No se pudo encontrar máscara de la red
}

function getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf)
{
    $paloEndPoint = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $arrSession = getSession();
    if(getParameter("mac"))
        $mac = getParameter("mac");
    else
        $mac = (isset($arrSession["endpoint_configurator"]["mac"]))?$arrSession["endpoint_configurator"]["mac"]:"No mac";
    $macExists = false;
    $isPatton = false;
    foreach($_SESSION["elastix_endpoints"] as $endpoint){
        if(trim($endpoint[2]) == $mac){
            $macExists = true;
            $vendor = explode("&",$endpoint[4]);
            if(trim($vendor[0]) === "Patton / Patton Electronics Co.")
                $isPatton = true;
            break;
        }
    }
    if(!$macExists || !$isPatton){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("The mac was not found").": $mac "._tr("or the endpoint is not a Patton"));
        return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }
    $ip_address = $arrSession["endpoint_ip"][$mac];
    $model = $arrSession["endpoint_model"][$mac];
    $arrParameters = $paloEndPoint->getEndpointParameters($mac);
    if($arrParameters === false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
        return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }
    $_DATA = array();
    foreach($arrParameters as $key => $parameter){
        $_DATA[$parameter["name"]] = $parameter["value"];
    }
    $_DATA = array_merge($_DATA,$_POST);
    if(isset($arrSession["endpoint_configurator"]) && is_array($arrSession["endpoint_configurator"]))
        $_DATA = array_merge($_DATA,$arrSession["endpoint_configurator"]);
    $arrCountry = $paloEndPoint->getCountries();
    if($arrCountry === false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
        return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }
    foreach($arrCountry as $country)
        $arrComboCountry[$country["id"]] = _tr($country["country"]);
    asort($arrComboCountry);
    $arrFormData = createFieldFormData($arrComboCountry);
    $oForm = new paloForm($smarty,$arrFormData);
    $title = _tr("Data Configuration for Patton")." $model";
    if(getParameter("option_network_lan") && getParameter("option_network_lan") == "lan_dhcp"){
        $smarty->assign("lan_check_dhcp", "checked");
        $smarty->assign("DISPLAY_LAN", "style=display:none;");
    }
    elseif(getParameter("option_network_lan") && getParameter("option_network_lan") == "lan_static")
        $smarty->assign("lan_check_static", "checked");
    elseif(isset($_DATA["lan_type"]) && $_DATA["lan_type"]=="dhcp"){
        $smarty->assign("lan_check_dhcp", "checked");
        $smarty->assign("DISPLAY_LAN", "style=display:none;");
    }
    else
        $smarty->assign("lan_check_static", "checked");

    if(getParameter("option_network_wan") && getParameter("option_network_wan") == "wan_dhcp"){
        $smarty->assign("wan_check_dhcp", "checked");
        $smarty->assign("DISPLAY_WAN", "style=display:none;");
    }
    elseif(getParameter("option_network_wan") && getParameter("option_network_wan") == "wan_static")
        $smarty->assign("wan_check_static", "checked");
    elseif(isset($_DATA["wan_type"]) && $_DATA["wan_type"]=="dhcp"){
        $smarty->assign("wan_check_dhcp", "checked");
        $smarty->assign("DISPLAY_WAN", "style=display:none;");
    }
    else
        $smarty->assign("wan_check_static", "checked");

    if((getParameter("router_present") && getParameter("router_present") == "no") || (isset($_DATA["router_present"]) && $_DATA["router_present"] == "no")){
        $smarty->assign("DISPLAY_LABEL_WAN", "style=display:none;");
        $smarty->assign("DISPLAY_CHECK_WAN", "style=display:none;");
        $smarty->assign("DISPLAY_WAN", "style=display:none;");
        $smarty->assign("DISPLAY_PBX_SIDE", "style=display:none;");
    }

    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("NEXT", _tr("Next"));
    $smarty->assign("general_data", _tr("General Data"));
    $smarty->assign("network_data", _tr("Network Data"));
    $smarty->assign("ip_pbx", _tr("IP-PBX/SIP Proxy Data"));
    $smarty->assign("localization_data", _tr("Localization Data"));
    $smarty->assign("lan_static", _tr("Static"));
    $smarty->assign("lan_dhcp", "DHCP");
    $smarty->assign("wan_static", _tr("Static"));
    $smarty->assign("wan_dhcp", "DHCP");
    $smarty->assign("general_extensions_data", _tr("General Extensions Data"));
    $smarty->assign("telnet_data", _tr("Telnet Data"));
    $smarty->assign("IMG", "/modules/$module_name/images/patton.png");
    $smarty->assign("INFO1", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Number of FXO ports to be configured. This number must not be greater than the physical FXO available ports")."'/></a>");
    $smarty->assign("INFO2", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Number of FXS ports to be configured. This number must not be greater than the physical FXS available ports")."'/></a>");
    $smarty->assign("INFO3", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Use \"Yes\" for all Smartnodes with 2 Ethernet ports")."'/></a>");
    $smarty->assign("INFO4", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("LAN side is always Ethernet 0 0 / WAN side is always Ethernet 0 1")."'/></a>");
    $smarty->assign("INFO5", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter IP address or name for the SNTP server")."'/></a>");
    $smarty->assign("INFO6", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter IP address or name for the DNS server. Leave it empty if DNS will be provided by DHCP")."'/></a>");
    $smarty->assign("INFO7", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Make sure the IP address belongs to any of the known networks in LAN or WAN interfaces")."'/></a>");
    $smarty->assign("INFO8", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("This is the IP PBX or register server address where extensions and trunks will be authenticated")."'/></a>");
    $smarty->assign("INFO9", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("This is the SIP port used to communicate to the IPPBX")."'/></a>");
    $smarty->assign("INFO11", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Select a country from pulldown")."'/></a>");
    $smarty->assign("INFO12", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the first extension number to use")."'/></a>");
    $smarty->assign("INFO13", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter incremental factor to calculate the rest of extension number - Valid Numbers from 1 to 10")."'/></a>");
    $smarty->assign("INFO14", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the registration port to be used for the extensions (range from 5060 to 16999)")."'/></a>");
    $smarty->assign("INFO15", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the registration port to be used for the first line. All the remaning ports will be calculated at incrementals of 2 (range from 5060 to 16999)")."'/></a>");
    $smarty->assign("INFO16", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter blank, 2, 5 or 7 seconds (if blank, no digit collection timeout is used)")."'/></a>");
    $smarty->assign("INFO17", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Announcements by the telephone company, such as \"The mobile you are calling is not available\", can be removed from calls by this device. Please note that if you choose the \"Deliver Announcements\" setting, some functions (such as forwarding to an outside number from a Ring Group or Queues) may fail or perform unpredictably")."'/></a>");
    $smarty->assign("INFO18", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Incoming calls may require up to 2 ring sequences to deliver the Caller ID. Select wheter you want to collect Caller ID information at the cost of delaying the first ring on incoming calls")."'/></a>");
    $smarty->assign("INFO19", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Select Caller ID format for FXS ports")."'/></a>");
    $smarty->assign("INFO20", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Select how do you want to present caller ID (mid-ring, pre-ring or none)")."'/></a>");
    $smarty->assign("INFO21", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the static IP for LAN interface")."'/></a>");
    $smarty->assign("INFO22", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the mask for static IP for LAN interface")."'/></a>");
    $smarty->assign("INFO23", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the static IP for WAN interface")."'/></a>");
    $smarty->assign("INFO24", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the mask for static IP for WAN interface")."'/></a>");
    $smarty->assign("INFO25", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the username for telnet authentication. The default value is administrator")."'/></a>");
    $smarty->assign("INFO26", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the password for telnet authentication. The default value is empty")."'/></a>");

    $arrSession["endpoint_configurator"]["mac"] = $mac;
    $arrSession["endpoint_configurator"]["ip_address"] = $ip_address;
    putSession($arrSession);
    if(!isset($_DATA["telnet_username"]))
        $_DATA["telnet_username"] = "administrator";
    if(!isset($_DATA["pbx_address"]))
        $_DATA["pbx_address"] = $_SERVER["SERVER_ADDR"];
    if(!isset($_DATA["sip_port"]))
        $_DATA["sip_port"] = 5060;
    if(!isset($_DATA["increment"]))
        $_DATA["increment"] = 1;
    if(!isset($_DATA["extensions_sip_port"]))
        $_DATA["extensions_sip_port"] = 5060;
    if(!isset($_DATA["lines_sip_port"]))
        $_DATA["lines_sip_port"] = 5060;
    if(!isset($_DATA["wan_ip_address"]))
        $_DATA["wan_ip_address"] = $ip_address;
    if(!isset($_DATA["wan_ip_mask"]))
        $_DATA["wan_ip_mask"] = "255.255.255.0";
    $htmlForm = $oForm->fetchForm("$local_templates_dir/patton_data.tpl", $title, $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    return $content;
}

function getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf)
{
    $paloEndPoint = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $paloFile = new paloSantoFileEndPoint($arrConf["tftpboot_path"]);
    $arrSession = getSession();
    if(getParameter("mac"))
        $mac = getParameter("mac");
    else
        $mac = (isset($arrSession["endpoint_configurator"]["mac"]))?$arrSession["endpoint_configurator"]["mac"]:"No mac";
    $macExists = false;
    $isVega = false;
    foreach($_SESSION["elastix_endpoints"] as $endpoint){
        if(trim($endpoint[2]) == $mac){
            $macExists = true;
            $vendor = explode("&",$endpoint[4]);
            if(trim($vendor[0]) === "Sangoma / VegaStream Limted")
                $isVega = true;
            break;
        }
    }
    if(!$macExists || !$isVega){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("The mac was not found").": $mac "._tr("or the endpoint is not a Vega"));
        return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }
    $ip_address = $arrSession["endpoint_ip"][$mac];
    $model = $arrSession["endpoint_model"][$mac];
    $arrParameters = $paloEndPoint->getEndpointParameters($mac);
    if($arrParameters === false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
        return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }
    $_DATA = array();
    foreach($arrParameters as $key => $parameter){
        $_DATA[$parameter["name"]] = $parameter["value"];
    }
    $_DATA = array_merge($_DATA,$_POST);
    if(isset($arrSession["endpoint_configurator"]) && is_array($arrSession["endpoint_configurator"]))
        $_DATA = array_merge($_DATA,$arrSession["endpoint_configurator"]);
    $arrCountry = $paloEndPoint->getCountries();
    if($arrCountry === false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
        return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }
   foreach($arrCountry as $country)
        $arrComboCountry[$country["id"]] = _tr($country["country"]);
    asort($arrComboCountry);
    $arrFormData = createFieldFormData($arrComboCountry);
    $oForm = new paloForm($smarty,$arrFormData);
    $title = _tr("Data Configuration for Sangoma Vega");
    if(getParameter("option_network_lan") && getParameter("option_network_lan") == "lan_dhcp"){
        $smarty->assign("lan_check_dhcp", "checked");
        $smarty->assign("DISPLAY_LAN", "style=display:none;");
    }
    elseif(getParameter("option_network_lan") && getParameter("option_network_lan") == "lan_static")
        $smarty->assign("lan_check_static", "checked");
    elseif(isset($_DATA["lan_type"]) && $_DATA["lan_type"]=="dhcp"){
        $smarty->assign("lan_check_dhcp", "checked");
        $smarty->assign("DISPLAY_LAN", "style=display:none;");
    }
    else
        $smarty->assign("lan_check_dhcp", "checked");

    if(getParameter("option_network_wan") && getParameter("option_network_wan") == "wan_dhcp"){
        $smarty->assign("wan_check_dhcp", "checked");
        $smarty->assign("DISPLAY_WAN", "style=display:none;");
    }
    elseif(getParameter("option_network_wan") && getParameter("option_network_wan") == "wan_static")
        $smarty->assign("wan_check_static", "checked");
    elseif(isset($_DATA["wan_type"]) && $_DATA["wan_type"]=="dhcp"){
        $smarty->assign("wan_check_dhcp", "checked");
        $smarty->assign("DISPLAY_WAN", "style=display:none;");
    }
    else
        $smarty->assign("wan_check_dhcp", "checked");
    
   /* if((getParameter("router_present") && getParameter("router_present") == "no") || (isset($_DATA["router_present"]) && $_DATA["router_present"] == "no")){
        $smarty->assign("DISPLAY_LABEL_WAN", "style=display:none;");
        $smarty->assign("DISPLAY_CHECK_WAN", "style=display:none;");
        $smarty->assign("DISPLAY_WAN", "style=display:none;");
        $smarty->assign("DISPLAY_PBX_SIDE", "style=display:none;");
    }*/
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("NEXT", _tr("Next"));
    $smarty->assign("general_data", _tr("General Data"));
    $smarty->assign("network_data", _tr("Network Data"));
    $smarty->assign("ip_pbx", _tr("IP-PBX/SIP Proxy Data"));
    $smarty->assign("localization_data", _tr("Localization Data"));
    $smarty->assign("voip_device_configuration", _tr("VoIP Device Configuration"));
    $smarty->assign("lan_static", _tr("Static"));
    $smarty->assign("lan_dhcp", "DHCP");
    $smarty->assign("wan_static", _tr("Static"));
   $smarty->assign("wan_dhcp", "DHCP");
    $smarty->assign("general_extensions_data", _tr("General Extensions Data"));
    $smarty->assign("telnet_data", _tr("Telnet Data"));
    $smarty->assign("IMG", "/modules/$module_name/images/sangoma.gif");
     $smarty->assign("INFO1", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Number of FXO ports to be configured. This number must not be greater than the physical FXO available ports")."'/></a>");
    $smarty->assign("INFO2", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Number of FXS ports to be configured. This number must not be greater than the physical FXS available ports")."'/></a>");
    $smarty->assign("INFO3", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Use \"Yes\" for all Smartnodes with 2 Ethernet ports")."'/></a>");
    $smarty->assign("INFO4", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("LAN side is always Ethernet 0 0 / WAN side is always Ethernet 0 1")."'/></a>");
    $smarty->assign("INFO5", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter IP address or name for the SNTP server")."'/></a>");
    $smarty->assign("INFO6", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter IP address or name for the DNS server. Leave it empty if DNS will be provided by DHCP")."'/></a>");
    $smarty->assign("INFO7", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Make sure the IP address belongs to any of the known networks in LAN or WAN interfaces")."'/></a>");
    $smarty->assign("INFO8", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("This is the IP PBX or register server address where extensions and trunks will be authenticated")."'/></a>");
    $smarty->assign("INFO9", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("This is the SIP port used to communicate to the IPPBX")."'/></a>");
    $smarty->assign("INFO11", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Select a country from pulldown")."'/></a>");
    $smarty->assign("INFO12", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the first extension number to use")."'/></a>");
    $smarty->assign("INFO13", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter incremental factor to calculate the rest of extension number - Valid Numbers from 1 to 10")."'/></a>");
    $smarty->assign("INFO14", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the registration port to be used for the extensions (range from 5060 to 16999)")."'/></a>");
    $smarty->assign("INFO15", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the registration port to be used for the first line. All the remaning ports will be calculated at incrementals of 2 (range from 5060 to 16999)")."'/></a>");
    $smarty->assign("INFO16", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter blank, 2, 5 or 7 seconds (if blank, no digit collection timeout is used)")."'/></a>");
    $smarty->assign("INFO17", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Announcements by the telephone company, such as \"The mobile you are calling is not available\", can be removed from calls by this device. Please note that if you choose the \"Deliver Announcements\" setting, some functions (such as forwarding to an outside number from a Ring Group or Queues) may fail or perform unpredictably")."'/></a>");
    $smarty->assign("INFO18", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Incoming calls may require up to 2 ring sequences to deliver the Caller ID. Select wheter you want to collect Caller ID information at the cost of delaying the first ring on incoming calls")."'/></a>");
    $smarty->assign("INFO19", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Select Caller ID format for FXS ports")."'/></a>");
    $smarty->assign("INFO20", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Select how do you want to present caller ID (mid-ring, pre-ring or none)")."'/></a>");
    $smarty->assign("INFO21", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the static IP for LAN interface")."'/></a>");
    $smarty->assign("INFO22", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the mask for static IP for LAN interface")."'/></a>");
    $smarty->assign("INFO23", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the static IP for WAN interface")."'/></a>");
    $smarty->assign("INFO24", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the mask for static IP for WAN interface")."'/></a>");
    $smarty->assign("INFO25", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the username for telnet authentication. The default value is admin")."'/></a>");
    $smarty->assign("INFO26", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the password for telnet authentication. The default value is admin")."'/></a>");
    $smarty->assign("INFO27", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the username for SIP Registration")."'/></a>");
    $smarty->assign("INFO28", "<a><img src='/modules/$module_name/images/question.png' border=0 title='"._tr("Enter the password for SIP Registration.")."'/></a>");
    $arrPorts = $paloFile->getSangomaPorts($ip_address,$mac,$dsnAsterisk,$dsnSqlite,2);
    if($arrPorts==null){
        $arrPorts['fxo']="";
        $arrPorts['fxs']="";
    }
    $arrSession["endpoint_configurator"]["mac"] = $mac;
    $arrSession["endpoint_configurator"]["ip_address"] = $ip_address;
    putSession($arrSession);
    if(!isset($_DATA["telnet_username"]))
        $_DATA["telnet_username"] = "admin";
    /*if(!isset($_DATA["telnet_password"]))*/
        $_DATA["telnet_password"] = "";
    if(!isset($_DATA["pbx_address"]))
        $_DATA["pbx_address"] = $_SERVER["SERVER_ADDR"];
    if(!isset($_DATA["sip_port"]))
        $_DATA["sip_port"] = 5060;
    if(!isset($_DATA["increment"]))
        $_DATA["increment"] = 1;
    /*if(!isset($_DATA["extensions_sip_port"]))
        $_DATA["extensions_sip_port"] = 5060;
    if(!isset($_DATA["lines_sip_port"]))
        $_DATA["lines_sip_port"] = 5060;*/
    if(!isset($_DATA["analog_extension_lines"]))
        $_DATA["analog_extension_lines"] = $arrPorts['fxs'];
    if(!isset($_DATA["analog_trunk_lines"]))
        $_DATA["analog_trunk_lines"] = $arrPorts['fxo'];
    //if(!isset($_DATA["wan_ip_address"]))
      //  $_DATA["wan_ip_address"] = $ip_address;
    //if(!isset($_DATA["wan_ip_mask"]))
      //  $_DATA["wan_ip_mask"] = "255.255.255.0";
    $htmlForm = $oForm->fetchForm("$local_templates_dir/vega_data.tpl", $title, $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    return $content;
}




function getExtensionsForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, $validationError)
{
    $paloEndPoint = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $arrSession = getSession();
    $mac = $arrSession["endpoint_configurator"]["mac"];
    if(!$validationError){
        $arrParameters = $paloEndPoint->getEndpointParameters($mac);
        if($arrParameters === false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
            return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        $_DATA = array();
        foreach($arrParameters as $key => $parameter){
            $_DATA[$parameter["name"]] = $parameter["value"];
        }
        $_DATA = array_merge($_DATA,$_POST);
        $arrCountry = $paloEndPoint->getCountries();
        if($arrCountry === false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
            return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        foreach($arrCountry as $country)
            $arrComboCountry[$country["id"]] = _tr($country["country"]);
        asort($arrComboCountry);
        $arrFormData = createFieldFormData($arrComboCountry);
        $oForm = new paloForm($smarty,$arrFormData);

        //************TODO: FALTAN MAS VALIDACIONES***************************
        if(!$oForm->validateForm($_POST)) {
            // Falla la validación básica del formulario
            $strErrorMsg = "<b>"._tr('The following fields contain errors').":</b><br/>";
            $arrErrores = $oForm->arrErroresValidacion;
            if(is_array($arrErrores) && count($arrErrores) > 0){
                foreach($arrErrores as $k=>$v) {
                    $strErrorMsg .= "$k: [$v[mensaje]] <br /> ";
                }
            }
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", $strErrorMsg);
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(!$paloEndPoint->countryExists(getParameter("country"))){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("The selected country is not in the list"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif((int)getParameter("analog_trunk_lines") < 0 || (int)getParameter("analog_trunk_lines") > 32 || (int)getParameter("analog_extension_lines") < 0 || (int)getParameter("analog_extension_lines") > 32){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("The number of analog trunk lines and the number of analog extension lines must be greater than 0 but less than 32"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("router_present") != "no" && getParameter("router_present") != "yes"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field router present (2 Ethernet)"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("pbx_side") != "lan" && getParameter("pbx_side") != "wan"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field in which side is the IP PBX"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("delivery_announcements") != "no" && getParameter("delivery_announcements") != "yes"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field delivery announcements"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("wait_callerID") != "no" && getParameter("wait_callerID") != "yes"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field wait for caller ID on incoming"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("callerID_format") != "etsi" && getParameter("callerID_format") != "bell"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field caller ID format"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("callerID_presentation") != "pre-ring" && getParameter("callerID_presentation") != "mid-ring" && getParameter("callerID_presentation") != "none"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field caller ID presentation"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("option_network_lan") == "lan_static" && (getParameter("lan_ip_address") == "" || getParameter("lan_ip_mask") == "")){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", "LAN: "._tr("For static ip configuration you have to enter an ip address and a mask"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("router_present") == "yes" && getParameter("option_network_wan") == "wan_static" && (getParameter("wan_ip_address") == "" || getParameter("wan_ip_mask") == "")){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", "WAN: "._tr("For static ip configuration you have to enter an ip address and a mask"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("timeout") != "" && getParameter("timeout") != 2 && getParameter("timeout") != 5 && getParameter("timeout") != 7){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field digit collection timeout"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif((int)getParameter("increment") < 1 || (int)getParameter("increment") > 10){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("The increment must be a number from 1 to 10"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif((int)getParameter("extensions_sip_port") < 5060 || (int)getParameter("extensions_sip_port") > 16999 || (int)getParameter("lines_sip_port") < 5060 || (int)getParameter("lines_sip_port") > 16999){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("The fields SIP port for extensions and First SIP port for lines must be numbers between 5060 and 16999"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif((getParameter("option_network_lan") != "lan_static" && getParameter("option_network_lan") != "lan_dhcp") || (getParameter("option_network_wan") != "wan_static" && getParameter("option_network_wan") != "wan_dhcp")){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("The options for networks LAN and WAN can only be static or DHCP"));
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        //**************************FIN DE VALIDACIONES*****************************

        $arrSession["endpoint_configurator"]["telnet_username"] = getParameter("telnet_username");
        $arrSession["endpoint_configurator"]["telnet_password"] = getParameter("telnet_password");
        $arrSession["endpoint_configurator"]["analog_extension_lines"] = getParameter("analog_extension_lines");
        $arrSession["endpoint_configurator"]["analog_trunk_lines"] = getParameter("analog_trunk_lines");
        $arrSession["endpoint_configurator"]["router_present"] = getParameter("router_present");
        $arrSession["endpoint_configurator"]["pbx_side"] = getParameter("pbx_side");
        $arrSession["endpoint_configurator"]["sntp_address"] = getParameter("sntp_address");
        $arrSession["endpoint_configurator"]["dns_address"] = getParameter("dns_address");
        if(getParameter("option_network_lan") == "lan_static"){
            $arrSession["endpoint_configurator"]["lan_type"] = "static";
            $arrSession["endpoint_configurator"]["lan_ip_address"] = getParameter("lan_ip_address");
            $arrSession["endpoint_configurator"]["lan_ip_mask"] = getParameter("lan_ip_mask");
        }
        else
            $arrSession["endpoint_configurator"]["lan_type"] = "dhcp";
        if(getParameter("option_network_wan") == "wan_static"){
            $arrSession["endpoint_configurator"]["wan_type"] = "static";
            $arrSession["endpoint_configurator"]["wan_ip_address"] = getParameter("wan_ip_address");
            $arrSession["endpoint_configurator"]["wan_ip_mask"] = getParameter("wan_ip_mask");
        }
        else
            $arrSession["endpoint_configurator"]["wan_type"] = "dhcp";
        $arrSession["endpoint_configurator"]["default_gateway"] = getParameter("default_gateway");
        $arrSession["endpoint_configurator"]["pbx_address"] = getParameter("pbx_address");
        $arrSession["endpoint_configurator"]["sip_port"] = getParameter("sip_port");
        $arrSession["endpoint_configurator"]["country"] = getParameter("country");
        $arrSession["endpoint_configurator"]["first_extension"] = getParameter("first_extension");
        $arrSession["endpoint_configurator"]["increment"] = getParameter("increment");
        $arrSession["endpoint_configurator"]["extensions_sip_port"] = getParameter("extensions_sip_port");
        $arrSession["endpoint_configurator"]["lines_sip_port"] = getParameter("lines_sip_port");
        $arrSession["endpoint_configurator"]["timeout"] = getParameter("timeout");
        $arrSession["endpoint_configurator"]["delivery_announcements"] = getParameter("delivery_announcements");
        $arrSession["endpoint_configurator"]["wait_callerID"] = getParameter("wait_callerID");
        $arrSession["endpoint_configurator"]["callerID_format"] = getParameter("callerID_format");
        $arrSession["endpoint_configurator"]["callerID_presentation"] = getParameter("callerID_presentation");
        putSession($arrSession);
    }else{
        $_DATA = $_POST;
        if(isset($arrSession["endpoint_configurator"]) && is_array($arrSession["endpoint_configurator"]))
            $_DATA = array_merge($_DATA,$arrSession["endpoint_configurator"]);
    }
    if($arrSession["endpoint_configurator"]["analog_extension_lines"] > 0){
        $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
        $smarty->assign("CANCEL", _tr("Cancel"));
        $smarty->assign("RETURN", _tr("Return"));
        $smarty->assign("IMG", "/modules/$module_name/images/patton.png");
        if($arrSession["endpoint_configurator"]["analog_trunk_lines"] == 0){
            $smarty->assign("NEXT", _tr("Save"));
            $smarty->assign("NEXT2", "save");
        }
        else{
            $smarty->assign("NEXT", _tr("Next"));
            $smarty->assign("NEXT2", "next_2");
        }
        $fields = "";
        $model = $arrSession["endpoint_model"][$arrSession["endpoint_configurator"]["mac"]];
        $title = _tr("Extensions Configuration for Patton")." $model";
        for($i = 0;$i < $arrSession["endpoint_configurator"]["analog_extension_lines"];$i++){
            $extension = $arrSession["endpoint_configurator"]["first_extension"] + $i*$arrSession["endpoint_configurator"]["increment"];
            if(!isset($_DATA["user$i"]))
                $_DATA["user$i"] = $extension;
            if(!isset($_DATA["authentication_user$i"]))
                $_DATA["authentication_user$i"] = $extension;
            if(!isset($_DATA["user_name$i"]))
                $_DATA["user_name$i"] = "";
            $number = $i+1;
            $fields .= "<tr class='letra12'>
                          <td><b>"._tr("Extension")." $number:</b></td>
                          <td width='10%'>$extension</td>
                          <td><b>"._tr("User Name")." $number:</b></td>
                          <td><input type='text' maxlength='100' style='width: 200px;' value='".$_DATA["user_name$i"]."' name='user_name$i'></td>
                          <td><b>"._tr("User")." $number:</b> <span  class='required'>*</span></td>
                          <td><input type='text' maxlength='100' style='width: 200px;' value='".$_DATA["user$i"]."' name='user$i'></td>
                          <td><b>"._tr("Authentication User")." $number:</b> <span  class='required'>*</span></td>
                          <td><input type='text' maxlength='100' style='width: 200px;' value='".$_DATA["authentication_user$i"]."' name='authentication_user$i'></td>
                      </tr>";
        }
        $smarty->assign("fields", $fields);
        $oForm = new paloForm($smarty,array());
        $htmlForm = $oForm->fetchForm("$local_templates_dir/patton_extensions.tpl", $title, $_DATA);
        $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    }
    else
        $content = getLinesForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, false);
    return $content;
}

function getExtensionsVegaForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, $validationError)
{
    $paloEndPoint = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $arrSession = getSession();
    $mac = $arrSession["endpoint_configurator"]["mac"];
    if(!$validationError){
        $arrParameters = $paloEndPoint->getEndpointParameters($mac);
        if($arrParameters === false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
            return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        $_DATA = array();
        foreach($arrParameters as $key => $parameter){
            $_DATA[$parameter["name"]] = $parameter["value"];
        }
        $_DATA = array_merge($_DATA,$_POST);
        $arrCountry = $paloEndPoint->getCountries();
        if($arrCountry === false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
            return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        foreach($arrCountry as $country)
            $arrComboCountry[$country["id"]] = _tr($country["country"]);
        asort($arrComboCountry);
        $arrFormData = createFieldFormData($arrComboCountry);
        $oForm = new paloForm($smarty,$arrFormData);
	    //************TODO: FALTAN MAS VALIDACIONES***************************
        if(!$oForm->validateForm($_POST)) {
            // Falla la validación básica del formulario
            $strErrorMsg = "<b>"._tr('The following fields contain errors').":</b><br/>";
            $arrErrores = $oForm->arrErroresValidacion;
            if(is_array($arrErrores) && count($arrErrores) > 0){
                foreach($arrErrores as $k=>$v) {
                    $strErrorMsg .= "$k: [$v[mensaje]] <br /> ";
                }
            }
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", $strErrorMsg);
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(!$paloEndPoint->countryExists(getParameter("country"))){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("The selected country is not in the list"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif((int)getParameter("analog_trunk_lines") < 0 || (int)getParameter("analog_trunk_lines") > 32 || (int)getParameter("analog_extension_lines") < 0 || (int)getParameter("analog_extension_lines") > 32){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("The number of analog trunk lines and the number of analog extension lines must be greater than 0 but less than 32"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("router_present") != "no" && getParameter("router_present") != "yes"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field router present (2 Ethernet)"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
       elseif(getParameter("pbx_side") != "lan" && getParameter("pbx_side") != "wan"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field in which side is the IP PBX"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("telnet_username")== getParameter("telnet_password")){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Password NOT allowed to be the same as the username"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
       /* elseif(getParameter("wait_callerID") != "no" && getParameter("wait_callerID") != "yes"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field wait for caller ID on incoming"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("callerID_format") != "etsi" && getParameter("callerID_format") != "bell"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field caller ID format"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("callerID_presentation") != "pre-ring" && getParameter("callerID_presentation") != "mid-ring" && getParameter("callerID_presentation") != "none"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field caller ID presentation"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }*/
        elseif(getParameter("option_network_lan") == "lan_static" && (getParameter("lan_ip_address") == "" || getParameter("lan_ip_mask") == "")){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", "LAN: "._tr("For static ip configuration you have to enter an ip address and a mask"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("router_present") == "yes" && getParameter("option_network_wan") == "wan_static" && (getParameter("wan_ip_address") == "" || getParameter("wan_ip_mask") == "")){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", "WAN: "._tr("For static ip configuration you have to enter an ip address and a mask"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
       /* elseif(getParameter("timeout") != "" && getParameter("timeout") != 2 && getParameter("timeout") != 5 && getParameter("timeout") != 7){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field digit collection timeout"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }*/
        elseif((int)getParameter("increment") < 1 || (int)getParameter("increment") > 10){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("The increment must be a number from 1 to 10"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
       /* elseif((int)getParameter("extensions_sip_port") < 5060 || (int)getParameter("extensions_sip_port") > 16999 || (int)getParameter("lines_sip_port") < 5060 || (int)getParameter("lines_sip_port") > 16999){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("The fields SIP port for extensions and First SIP port for lines must be numbers between 5060 and 16999"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }*/
        elseif((getParameter("option_network_lan") != "lan_static" && getParameter("option_network_lan") != "lan_dhcp") || (getParameter("option_network_wan") != "wan_static" && getParameter("option_network_wan") != "wan_dhcp")){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("The options for networks LAN and WAN can only be static or DHCP"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
          /*elseif(getParameter("callerID_format") != "etsi" && getParameter("callerID_format") != "bell"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field caller ID format"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        elseif(getParameter("callerID_presentation") != "pre-ring" && getParameter("callerID_presentation") != "mid-ring" && getParameter("callerID_presentation") != "none"){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Invalid option for field caller ID presentation"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }*/
        elseif(getParameter("option_network_lan") == "lan_static" && (getParameter("lan_ip_address") == "" || getParameter("lan_ip_mask") == "")){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", "LAN: "._tr("For static ip configuration you have to enter an ip address and a mask"));
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        //**************************FIN DE VALIDACIONES*****************************
        
        $arrSession["endpoint_configurator"]["telnet_username"] = getParameter("telnet_username");
        $arrSession["endpoint_configurator"]["telnet_password"] = getParameter("telnet_password");
        $arrSession["endpoint_configurator"]["analog_extension_lines"] = getParameter("analog_extension_lines");
        $arrSession["endpoint_configurator"]["analog_trunk_lines"] = getParameter("analog_trunk_lines");
        $arrSession["endpoint_configurator"]["router_present"] = getParameter("router_present");
        $arrSession["endpoint_configurator"]["pbx_side"] = getParameter("pbx_side");
       // $arrSession["endpoint_configurator"]["sntp_address"] = getParameter("sntp_address");
        $arrSession["endpoint_configurator"]["dns_address"] = getParameter("dns_address");
        if(getParameter("option_network_lan") == "lan_static"){
            $arrSession["endpoint_configurator"]["lan_type"] = "static";
            $arrSession["endpoint_configurator"]["lan_ip_address"] = getParameter("lan_ip_address");
            $arrSession["endpoint_configurator"]["lan_ip_mask"] = getParameter("lan_ip_mask");
        }
        else
            $arrSession["endpoint_configurator"]["lan_type"] = "dhcp";
        if(getParameter("option_network_wan") == "wan_static"){
            $arrSession["endpoint_configurator"]["wan_type"] = "static";
            $arrSession["endpoint_configurator"]["wan_ip_address"] = getParameter("wan_ip_address");
            $arrSession["endpoint_configurator"]["wan_ip_mask"] = getParameter("wan_ip_mask");
        }
        else
            $arrSession["endpoint_configurator"]["wan_type"] = "dhcp";
        $arrSession["endpoint_configurator"]["default_gateway"] = getParameter("default_gateway");
        $arrSession["endpoint_configurator"]["pbx_address"] = getParameter("pbx_address");
        $arrSession["endpoint_configurator"]["sip_port"] = getParameter("sip_port");
        $arrSession["endpoint_configurator"]["country"] = getParameter("country");
        $arrSession["endpoint_configurator"]["first_extension_vega"] = getParameter("first_extension_vega");
        $arrSession["endpoint_configurator"]["increment"] = getParameter("increment");
        $arrSession["endpoint_configurator"]["registration"] = getParameter("registration");
        $arrSession["endpoint_configurator"]["registration_password"] = getParameter("registration_password");
      //  $arrSession["endpoint_configurator"]["extensions_sip_port"] = getParameter("extensions_sip_port");
      //  $arrSession["endpoint_configurator"]["lines_sip_port"] = getParameter("lines_sip_port");
      /*  $arrSession["endpoint_configurator"]["timeout"] = getParameter("timeout");
        $arrSession["endpoint_configurator"]["delivery_announcements"] = getParameter("delivery_announcements");
        $arrSession["endpoint_configurator"]["wait_callerID"] = getParameter("wait_callerID");
        $arrSession["endpoint_configurator"]["callerID_format"] = getParameter("callerID_format");
        $arrSession["endpoint_configurator"]["callerID_presentation"] = getParameter("callerID_presentation");*/
        putSession($arrSession);
    }else{
    $_DATA = $_POST;
        if(isset($arrSession["endpoint_configurator"]) && is_array($arrSession["endpoint_configurator"]))
            $_DATA = array_merge($_DATA,$arrSession["endpoint_configurator"]);
    }
      if($arrSession["endpoint_configurator"]["analog_extension_lines"] > 0){
        $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
        $smarty->assign("CANCEL", _tr("Cancel"));
        $smarty->assign("RETURN", _tr("Return"));
        $smarty->assign("IMG", "/modules/$module_name/images/sangoma.gif");
        if($arrSession["endpoint_configurator"]["analog_trunk_lines"] == 0){
            $smarty->assign("NEXT", _tr("Save"));
            $smarty->assign("NEXT2", "save_vega");
        }
        else{
            $smarty->assign("NEXT", _tr("Next"));
            $smarty->assign("NEXT2", "next_2_vega");
        }
        $fields = "";
        $model = $arrSession["endpoint_model"][$arrSession["endpoint_configurator"]["mac"]];
        $title = _tr("Extensions Configuration for Vega (FXS)");
        $fields .= "<tr align='center' style='font-size:14px; font-weight:bold; '><td>"._tr("Port ID")."</td><td>"._tr("Enabled")."<br><input type='checkbox' name='checkall' class='checkall'/> </td><td>"._tr("Caller ID")."</td><td>"._tr("Call Conference")."</td><td>"._tr("Call Transfer")."</td><td>"._tr("Call Waiting")."</td><td>"._tr("Do Not Disturbe Enable")."</td><td>"._tr("Interface")."</td><td>"._tr("DN")."</td><td>"._tr("Username")."</td> </tr>";
        $comboOnOff = array("on" => _tr("On"),"off"=>_tr("Off"));
        $comboCallerId= array("on" => _tr("On"),"off"=>_tr("Off"),"cidcw"=>_tr("cidcw"));
        $check="";
        for($i = 0;$i < $arrSession["endpoint_configurator"]["analog_extension_lines"];$i++){
            $extension = $arrSession["endpoint_configurator"]["first_extension_vega"] + $i*$arrSession["endpoint_configurator"]["increment"];
            if(!isset($_DATA["user$i"]))
                $_DATA["user$i"] = $extension;
            if(!isset($_DATA["authentication_user$i"]))
                $_DATA["authentication_user$i"] = $extension;
            if(!isset($_DATA["user_name$i"]))
                $_DATA["user_name$i"] = $extension;
            $number = $i+1;
            if((isset($_DATA["enable$i"]))&&($_DATA["enable$i"])==1)
            $check="checked=checked";
            else
        $check="";	

        $fields .= "<tr class='letra12' align='center'>
                          <td width='5%' align='center'><b>$number</b></td>
                <td class='enable'><input type='checkbox' name='enable$i' $check/></td>
                          <td><select name='caller_id$i'>". combo($comboCallerId,$_DATA["caller_id$i"]) ."</select></td>
            <td><select name='call_conference$i'>". combo($comboOnOff, $_DATA["call_conference$i"]) ."</select></td>
                          <td><select name='call_transfer$i'>". combo($comboOnOff, $_DATA["call_transfer$i"]) ."</select></td>
                          <td><select name='call_waiting$i'>". combo($comboOnOff, $_DATA["call_waiting$i"]) ."</select></td>
                          <td width='5%'><select name='call_dnd$i'>". combo($comboOnOff, $_DATA["call_dnd$i"]) ."</select></td>
                          <td><input type='text' maxlength='40' style='width: 100px;' value='".$_DATA["user_name$i"]."' name='user_name$i'></td>
                          <td><input type='text' maxlength='40' style='width: 100px;' value='".$_DATA["user$i"]."' name='user$i'></td>
                          <td><input type='text' maxlength='40' style='width: 100px;' value='FXS$number' name='authentication_user$i'></td>
                      </tr>";
         /*  $fields .= "<tr class='letra12'>
                          <td width='5%'><b>"._tr("Extension")." $number:</b></td>
                          <td width='5%'>$extension</td>
                          <td><input type='checkbox'name='chkext$i' /></td>
                          <td><b>"._tr("User Name")." $number:</b></td>
                          <td><input type='text' maxlength='100' style='width: 200px;' value='".$_DATA["user_name$i"]."' name='user_name$i'></td>
                          <td><b>"._tr("User")." $number:</b> <span  class='required'>*</span></td>
                          <td><input type='text' maxlength='100' style='width: 200px;' value='".$_DATA["user$i"]."' name='user$i'></td>
                          <td><b>"._tr("Authentication User")." $number:</b> <span  class='required'>*</span></td>
                          <td><input type='text' maxlength='100' style='width: 200px;' value='".$_DATA["authentication_user$i"]."' name='authentication_user$i'></td>
                      </tr>";*/

        }
        $smarty->assign("fields", $fields);
        $oForm = new paloForm($smarty,array());
        $htmlForm = $oForm->fetchForm("$local_templates_dir/vega_extensions.tpl", $title, $_DATA);
        $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    }
    else
        $content = getLinesVegaForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, false);
    return $content;
  }

function getLinesForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, $validationError)
{
    $paloEndPoint = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $arrSession = getSession();
    $mac = $arrSession["endpoint_configurator"]["mac"];
    if(!$validationError){
        $arrParameters = $paloEndPoint->getEndpointParameters($mac);
        if($arrParameters === false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
            return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        $_DATA = array();
        foreach($arrParameters as $key => $parameter){
            $_DATA[$parameter["name"]] = $parameter["value"];
        }
        $_DATA = array_merge($_DATA,$_POST);
        for($i = 0;$i < $arrSession["endpoint_configurator"]["analog_extension_lines"];$i++){
            if(getParameter("user$i")=="" || getParameter("authentication_user$i")==""){
                $smarty->assign("mb_title", _tr("Validation Error"));
                $smarty->assign("mb_message", _tr("Fields User and Authentication User can not be empty"));
                return getExtensionsForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, true);
            }
            else{
                $arrSession["endpoint_configurator"]["user_name$i"] = getParameter("user_name$i");
                $arrSession["endpoint_configurator"]["user$i"] = getParameter("user$i");
                $arrSession["endpoint_configurator"]["authentication_user$i"] = getParameter("authentication_user$i");
            }
        }
        putSession($arrSession);
    }else
        $_DATA = $_POST;
    if($arrSession["endpoint_configurator"]["analog_trunk_lines"] > 0){
        $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
        $smarty->assign("CANCEL", _tr("Cancel"));
        $smarty->assign("SAVE", _tr("Save"));
        $smarty->assign("RETURN",_tr("Return"));
        $smarty->assign("IMG", "/modules/$module_name/images/patton.png");
        if($arrSession["endpoint_configurator"]["analog_extension_lines"] > 0)
            $smarty->assign("RETURN2","return2");
        else
            $smarty->assign("RETURN2","return1");
        $fields = "";
        $model = $arrSession["endpoint_model"][$arrSession["endpoint_configurator"]["mac"]];
        $title = _tr("Lines Configuration for Patton")." $model";
        for($i = 0;$i < $arrSession["endpoint_configurator"]["analog_trunk_lines"];$i++){
            if(!isset($_DATA["line$i"]))
                $_DATA["line$i"] = 10015 + $i;
            if(!isset($_DATA["ID$i"]))
                $_DATA["ID$i"] = 10015 + $i;
            if(!isset($_DATA["authentication_ID$i"]))
                $_DATA["authentication_ID$i"] = 10015 + $i;
            $number = $i+1;
            $fields .= "<tr class='letra12'>
                          <td><b>"._tr("Line")." $number:</b> <span  class='required'>*</span></td>
                          <td width='25%'><input type='text' maxlength='100' style='width: 200px;' value='".$_DATA["line$i"]."' name='line$i'></td>
                          <td><b>ID $number: <span  class='required'>*</span></b></td>
                          <td><input type='text' maxlength='100' style='width: 200px;' value='".$_DATA["ID$i"]."' name='ID$i'></td>
                          <td><b>"._tr("Authentication ID")." $number:</b> <span  class='required'>*</span></td>
                          <td><input type='text' maxlength='100' style='width: 200px;' value='".$_DATA["authentication_ID$i"]."' name='authentication_ID$i'></td>
                      </tr>";
        }
        $smarty->assign("fields", $fields);
        $oForm = new paloForm($smarty,array());
        $htmlForm = $oForm->fetchForm("$local_templates_dir/patton_lines.tpl", $title, $_DATA);
        $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    }
    else
        $content = savePatton($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    return $content;
}

function getLinesVegaForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, $validationError)
{
    $paloEndPoint = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $arrSession = getSession();
    $mac = $arrSession["endpoint_configurator"]["mac"];
    if(!$validationError){
        $arrParameters = $paloEndPoint->getEndpointParameters($mac);
        if($arrParameters === false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
            return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        }
        $_DATA = array();
        foreach($arrParameters as $key => $parameter){
            $_DATA[$parameter["name"]] = $parameter["value"];
        }
        $_DATA = array_merge($_DATA,$_POST);
        for($i = 0;$i < $arrSession["endpoint_configurator"]["analog_extension_lines"];$i++){
            if(getParameter("user$i")=="" || getParameter("authentication_user$i")==""){
                $smarty->assign("mb_title", _tr("Validation Error"));
                $smarty->assign("mb_message", _tr("Fields User and Authentication User can not be empty"));
                return getExtensionsVegaForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, true);
            }
            else{
                $arrSession["endpoint_configurator"]["user_name$i"] = getParameter("user_name$i");
                $arrSession["endpoint_configurator"]["user$i"] = getParameter("user$i");
                $arrSession["endpoint_configurator"]["authentication_user$i"] = getParameter("authentication_user$i");
                $arrSession["endpoint_configurator"]["call_conference$i"] = getParameter("call_conference$i"); 
                $arrSession["endpoint_configurator"]["call_transfer$i"] = getParameter("call_transfer$i"); 
                $arrSession["endpoint_configurator"]["call_waiting$i"] = getParameter("call_waiting$i");
                $arrSession["endpoint_configurator"]["caller_id$i"] = getParameter("caller_id$i");
                $arrSession["endpoint_configurator"]["call_dnd$i"] = getParameter("call_dnd$i"); 
                if((getParameter("enable$i")))
                    $arrSession["endpoint_configurator"]["enable$i"]=1;
                else
                    $arrSession["endpoint_configurator"]["enable$i"]=0;               
            }
        }
        putSession($arrSession);
    }else
        $_DATA = $_POST;
        if($arrSession["endpoint_configurator"]["analog_trunk_lines"] > 0){
            $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
            $smarty->assign("CANCEL", _tr("Cancel"));
            $smarty->assign("SAVE", _tr("Save"));
            $smarty->assign("RETURN",_tr("Return"));
            $smarty->assign("IMG", "/modules/$module_name/images/sangoma.gif");
            if($arrSession["endpoint_configurator"]["analog_extension_lines"] > 0)
                $smarty->assign("RETURN2_VEGA","return2_vega");
            else
                $smarty->assign("RETURN2_VEGA","return1_vega");
            $fields ="";
            $fields .= "<tr align='center' style='font-size:13px; font-weight:bold;'><td>"._tr("Port ID")."</td><td>"._tr("Enable")."<br><input type='checkbox' name='checkall' class='checkall'/>  </td><td>"._tr("Interface")."</td><td>"._tr("DN")."</td><td>"._tr("Username")."</td><td>"._tr("Telephone number(s) to route to the FXO interface")."</td></tr>";
            $model = $arrSession["endpoint_model"][$arrSession["endpoint_configurator"]["mac"]];
            $title = _tr("Lines Configuration for Vega (FXO)");
            for($i = 0;$i < $arrSession["endpoint_configurator"]["analog_trunk_lines"];$i++){
                $number = $i+1;
                $nport=$arrSession["endpoint_configurator"]["analog_extension_lines"]+$number;
            if(!isset($_DATA["line$i"]))
                    $_DATA["line$i"] = 1000 + $i;
                if(!isset($_DATA["ID$i"]))
                    $_DATA["ID$i"] = 1000 + $i;
                if(!isset($_DATA["authentication_ID$i"]))
                    $_DATA["authentication_ID$i"] = "FXO$nport";

            if((isset($_DATA["enable_line$i"]))&&($_DATA["enable_line$i"])==1)
                    $check="checked=checked";
                else
                    $check="";

                $fields .= "<tr class='letra12' align='center'>
                            <td><b>$nport:</b></td>
                <td class='enable'><input type='checkbox'name='enable_line$i' $check/></td>
                            <td width='25%'><input type='text' maxlength='100' style='width: 100px;' value='".$_DATA["line$i"]."' name='line$i'></td>
                            <td><input type='text' maxlength='100' style='width: 100px;' value='". $_DATA["ID$i"] ."' name='ID$i'></td>
                            <td><input type='text' maxlength='100' style='width: 100px;' value='".$_DATA["authentication_ID$i"]."'  name='authentication_ID$i'></td>
                            <td><input type='text' maxlength='200' style='width: 200px;' value='".$_DATA["num_list$i"]."'  name='num_list$i'></td>
                        </tr>";
                /* Telephone number(s) to route to the FXO interface $fields .= "<tr class='letra12'>
                            <td><b>$nport:</b> <span  class='required'>*</span></td>
                            <td><input type='checkbox'name='enable_line$i'/></td>
                            <td width='25%'><input type='text' maxlength='100' style='width: 200px;' value='' name='line$i'></td>
                            <td><b>ID $number: <span  class='required'>*</span></b></td>
                            <td><input type='text' maxlength='100' style='width: 200px;' value='' name='ID$i'></td>
                            <td><b>"._tr("Authentication ID")."$number:</b> <span  class='required'>*</span></td>
                            <td><input type='text' maxlength='100' style='width: 200px;' value='' name='authentication_ID$i'></td>
                        </tr>";*/

            }
            $smarty->assign("fields", $fields);
            $oForm = new paloForm($smarty,array());
            $htmlForm = $oForm->fetchForm("$local_templates_dir/vega_lines.tpl", $title, $_DATA);
            $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
        }
        else
            $content = savePatton($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    return $content;
}


function savePatton($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf)
{
    $paloEndPoint = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $paloFileEndPoint = new PaloSantoFileEndPoint($arrConf["tftpboot_path"],$_SESSION["endpoint_mask"]);
    $arrSession = getSession();
    for($i = 0;$i < $arrSession["endpoint_configurator"]["analog_trunk_lines"];$i++){
        if(getParameter("line$i") == "" || getParameter("ID$i") == "" || getParameter("authentication_ID$i") == ""){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Fields Line, ID and authentication ID can not be empty"));
            return getLinesForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, true);
        }
        else{
            $arrSession["endpoint_configurator"]["line$i"] = getParameter("line$i");
            $arrSession["endpoint_configurator"]["ID$i"] = getParameter("ID$i");
            $arrSession["endpoint_configurator"]["authentication_ID$i"] = getParameter("authentication_ID$i");
        }
    }
    if(!$paloEndPoint->savePattonData($arrSession["endpoint_configurator"])){
        $smarty->assign("mb_title", _tr("ERROR"));
        if($paloEndPoint->errMsg != "" && isset($paloEndPoint->errMsg))
            $smarty->assign("mb_message", $paloEndPoint->errMsg);
        else
            $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
        return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }

    $tone_set = $paloEndPoint->getToneSet($arrSession["endpoint_configurator"]["country"]);
    if($tone_set == false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("Could not get the tone set by country"));
        return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }
    $result = $paloFileEndPoint->buildPattonConfFile($arrSession["endpoint_configurator"],$tone_set);
    if($result === false || is_null($result)){
        $smarty->assign("mb_title", _tr("ERROR"));
        if($paloFileEndPoint->errMsg != "" && isset($paloFileEndPoint->errMsg))
            $smarty->assign("mb_message", $paloFileEndPoint->errMsg);
        else
            $smarty->assign("mb_message", _tr("Could not create the patton configuration file"));
        if(is_null($result))
            return getPattonData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        else
            return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }
    $smarty->assign("mb_title", _tr("MESSAGE"));
    $smarty->assign("mb_message", _tr("The Patton was successfully configurated. The changes will apply after the Patton is finished rebooting"));
    unset($arrSession["endpoint_configurator"]);
    unset($arrSession["elastix_endpoints"]);
    putSession($arrSession);
    return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
}

function saveVega($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf)
{
    $paloEndPoint = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $paloFileEndPoint = new PaloSantoFileEndPoint($arrConf["tftpboot_path"],$_SESSION["endpoint_mask"]);
    $arrSession = getSession();
    for($i = 0;$i < $arrSession["endpoint_configurator"]["analog_trunk_lines"];$i++){
        if(getParameter("line$i") == "" || getParameter("ID$i") == "" || getParameter("authentication_ID$i") == ""){
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr("Fields Line, ID and authentication ID can not be empty"));
            return getLinesVegaForm($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf, true);
        }
        else{
            $arrSession["endpoint_configurator"]["line$i"] = getParameter("line$i");
            $arrSession["endpoint_configurator"]["ID$i"] = getParameter("ID$i");
            $arrSession["endpoint_configurator"]["authentication_ID$i"] = getParameter("authentication_ID$i");
            $arrSession["endpoint_configurator"]["num_list$i"] = getParameter("num_list$i");
            if(getParameter("enable_line$i"))
                $arrSession["endpoint_configurator"]["enable_line$i"] = 1;
            else
                $arrSession["endpoint_configurator"]["enable_line$i"] = 0;
        }
    }
    $mac="";
    if(getParameter("mac"))
        $mac = getParameter("mac");

    $credential=$paloEndPoint->getPassword($mac);
   
    if(!$paloEndPoint->saveVegaData($arrSession["endpoint_configurator"])){
        $smarty->assign("mb_title", _tr("ERROR"));
        if($paloEndPoint->errMsg != "" && isset($paloEndPoint->errMsg))
            $smarty->assign("mb_message", $paloEndPoint->errMsg);
        else
            $smarty->assign("mb_message", _tr("In the query to database endpoint.db"));
        return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }

    $tone_set = $paloEndPoint->getToneSet($arrSession["endpoint_configurator"]["country"]);
    if($tone_set == false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("Could not get the tone set by country"));
        return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }
    //$last_password=$credential["password"];
    $result = $paloFileEndPoint->buildSangomaConfFile($arrSession["endpoint_configurator"],$tone_set,$dsnAsterisk, $dsnSqlite);
    if($result === false || is_null($result)){
        $smarty->assign("mb_title", _tr("ERROR"));
        if($paloFileEndPoint->errMsg != "" && isset($paloFileEndPoint->errMsg))
            $smarty->assign("mb_message", $paloFileEndPoint->errMsg);
        else
            $smarty->assign("mb_message", _tr("Could not create the Vega configuration file"));
        if(is_null($result))
            return getVegaData($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
        else
            return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
    }
    $smarty->assign("mb_title", _tr("MESSAGE"));
    $smarty->assign("mb_message", _tr("The Sangoma Vega was successfully configurated. The changes will apply after the Vega is finished rebooting"));
    unset($arrSession["endpoint_configurator"]);
    unset($arrSession["elastix_endpoints"]);
    putSession($arrSession);
    return endpointConfiguratedShow($smarty, $module_name, $local_templates_dir, $dsnAsterisk, $dsnSqlite, $arrConf);
}


function createFieldFormData($arrCountry)
{
    $arrYN = array("yes" => _tr("YES"), "no" => _tr("NO"));
    $arrSide = array("lan" => "LAN", "wan" => "WAN");
    $arrformat = array("etsi" => "etsi", "bell" => "bell");
    $arrpresentation = array("mid-ring" => "mid-ring", "pre-ring" => "pre-ring", "none" => "("._tr("none").")");
    $arrTimeout = array("" => "", 2 => 2, 5 => 5, 7 => 7);
    $arrFields = array(
            "telnet_username"            => array(      "LABEL"      => _tr("Telnet Username"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"100"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "telnet_password"            => array(      "LABEL"      => _tr("Telnet Password"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"100"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "analog_trunk_lines"         => array(      "LABEL"      => _tr("Number of analog trunk lines (FXO)"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"100"),
                                            "VALIDATION_TYPE"        => "numeric",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "analog_extension_lines"     => array(     "LABEL"       => _tr("Number of analog extension lines (FXS)"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "numeric",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),

            "router_present"             => array(    "LABEL"        => _tr("Router present (2 Ethernet)?"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrYN,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "ONCHANGE"               => "javascript:changeFields(this);"
                                            ),

            "pbx_side"                   => array(    "LABEL"        => _tr("In which side is the IP PBX?"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrSide,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            
            "sntp_address"               => array(    "LABEL"        => _tr("SNTP Server address"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "dns_address"                => array(   "LABEL"         => _tr("DNS Server address"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "lan_ip_address"             => array(   "LABEL"         => _tr("LAN IP address"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "ip",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "lan_ip_mask"                => array(   "LABEL"         => _tr("LAN IP mask"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "mask",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "wan_ip_address"             => array(   "LABEL"         => _tr("WAN IP address"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "ip",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "wan_ip_mask"                => array(   "LABEL"         => _tr("WAN IP mask"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "mask",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "default_gateway"            => array(   "LABEL"         => _tr("Default Gateway"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "ip",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "pbx_address"                => array(   "LABEL"         => _tr("IP-PBX IP address / Name / Domain"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "ip",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "sip_port"                   => array(   "LABEL"         => _tr("IP-PBX SIP port"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "numeric",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "country"                    => array(   "LABEL"         => _tr("Country"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrCountry,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "first_extension"            => array(   "LABEL"         => _tr("First extension"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "numeric",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "first_extension_vega"       => array(   "LABEL"         => _tr("First extension"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "numeric",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "increment"                  => array(   "LABEL"         => _tr("Extension increment"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "numeric",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "registration"               => array(   "LABEL"         => _tr("Registration and Authentication ID"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "registration_password"      => array(   "LABEL"         => _tr("Authentication Password"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "extensions_sip_port"        => array(   "LABEL"         => _tr("SIP port for extensions"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "numeric",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "lines_sip_port"             => array(   "LABEL"         => _tr("First SIP port for lines"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "numeric",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "timeout"                    => array(   "LABEL"         => _tr("Digit collection timeout"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrTimeout,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "delivery_announcements"     => array(   "LABEL"         => _tr("Delivery Announcements?"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrYN,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "wait_callerID"              => array(   "LABEL"         => _tr("Wait for caller ID on incoming"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrYN,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "callerID_format"            => array(   "LABEL"         => _tr("Caller ID format (FXS)"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrformat,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "callerID_presentation"      => array(   "LABEL"         => _tr("Caller ID presentation (FXS)"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrpresentation,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),

            );
    return $arrFields;
}

function getSession()
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $tmp = $_SESSION;
        session_commit();
    }
    return $tmp;
}

function putSession($data)//data es un arreglo
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $_SESSION = $data;
        session_commit();
    }
}

function getAction()
{
    if(getParameter("endpoint_scan"))
        return "endpoint_scan";
    elseif(getParameter("endpoint_set"))
        return "endpoint_set";
    elseif(getParameter("endpoint_unset"))
        return "endpoint_unset";
    elseif(getParameter("action"))
        return getParameter("action");
    elseif(getParameter("next_1"))
        return "next_1";
    elseif(getParameter("next_1_vega"))
        return "next_1_vega";
    elseif(getParameter("next_2"))
        return "next_2";
    elseif(getParameter("next_2_vega"))
        return "next_2_vega";
    elseif(getParameter("save"))
        return "save";
    elseif(getParameter("save_vega"))
        return "save_vega";
    elseif(getParameter("return1"))
        return "patton_data";
    elseif(getParameter("return1_vega"))
        return "vega_data";
    elseif(getParameter("return2"))
        return "return2";
    elseif(getParameter("return2_vega"))
        return "return2_vega";
  
    else    return "endpoint_show";
}
?>
