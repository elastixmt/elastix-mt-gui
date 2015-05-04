<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.3.0-6                                               |
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
  $Id: index.php,v 1.1 2012-05-30 05:05:31 Sergio Broncano sbroncano@palosanto.com Exp $ */
//include elastix framework
 
// ASUME TECH SIP
// SE ASUME QUE LOS TELEFONOS DEL .CSV ESTAN TODOS EN LA MISMA RED

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoValidar.class.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoJSON.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/misc.lib.php";
include_once "libs/paloSantoNetwork.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/endpoint_configurator/libs/paloSantoEndPoint.class.php";
    include_once "modules/endpoint_configurator/libs/paloSantoFileEndPoint.class.php";
    include_once "modules/$module_name/libs/paloSantoEndPointDownload.class.php";

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
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

    //conexion resource
    $pConfig     = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrAMP      = $pConfig->leer_configuracion(false);
    $dsnAsterisk = $arrAMP['AMPDBENGINE']['valor']."://".
                   $arrAMP['AMPDBUSER']['valor']. ":".
                   $arrAMP['AMPDBPASS']['valor']. "@".
                   $arrAMP['AMPDBHOST']['valor'];
    $dsnSqlite   = $arrConf['dsn_conn_database'];

    //Sirve para todos los casos
    $smarty->assign("MODULE_NAME", $module_name);
    $smarty->assign("label_file", _tr("File"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("HeaderFile", _tr("Header File Batch Endpoint"));
    $smarty->assign("DOWNLOAD", _tr("Download Endpoints"));
    $smarty->assign("AboutUpdate", _tr("About Update Batch Endpoint"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("label_endpoint", _tr("Discover Endpoints in this Network"));
    $smarty->assign("title", _tr("Endpoint Batch"));
    $smarty->assign("title_module", _tr("Endpoint Batch"));

    //actions
    $action = getAction();
    $content = "";
    switch($action){
        case "load_endpoint":
            $content = load_endpoint($smarty, $module_name, $local_templates_dir, $arrLang, $arrConf, $base_dir, $dsnAsterisk, $dsnSqlite);
            break;
        case "download_csv":
            download_endpoints($dsnAsterisk, $dsnSqlite);
            break;
        default: // view_form
            $content = viewFormBatchofEndpoint($smarty, $module_name, $local_templates_dir, $arrConf);
            break;
    }
    return $content;
}

function load_endpoint($smarty, $module_name, $local_templates_dir, $arrLang, $arrConf, $base_dir, $dsnAsterisk, $dsnSqlite)
{
    $arrTmp=array();
    $bMostrarError = false;
    
    //valido el tipo de archivo
    if (!preg_match('/.csv$/', $_FILES['file']['name'])) {
        $smarty->assign("mb_title", _tr("Validation Error"));
        $smarty->assign("mb_message", _tr("Invalid file extension.- It must be csv"));
    }else {
        if(is_uploaded_file($_FILES['file']['tmp_name'])) {
            //Funcion para cargar los endpoints
            load_endpoint_from_csv($smarty, $arrLang, $_FILES['file']['tmp_name'], $base_dir, $dsnAsterisk, $dsnSqlite, $module_name, $local_templates_dir, $arrConf);
        }else {
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", _tr("Possible file upload attack. Filename") ." :". $_FILES['file']['name']);
        }
    }
    return viewFormBatchofEndpoint($smarty, $module_name, $local_templates_dir, $arrLang, $arrConf);
}

function load_endpoint_from_csv($smarty, $arrLang, $ruta_archivo_csv, $base_dir, $dsnAsterisk, $dsnSqlite, $module_name, $local_templates_dir, $arrConf)
{
    $paloEndPoint      = new paloSantoEndPoint($dsnAsterisk,$dsnSqlite);
    $arrEndpointsConf  = $paloEndPoint->listEndpointConf();
    $arrVendor         = $paloEndPoint->listVendor();
    $endpoint_mask     = isset($_POST['endpoint_mask'])?$_POST['endpoint_mask']:network();
    $pValidator        = new PaloValidar();
    $arrFindVendor     = array(); //variable de ayuda, para llamar solo una vez la funcion createFilesGlobal de cada vendor
    $arrayColumnas     = array();

    $result = isValidCSV($arrLang, $ruta_archivo_csv, $arrayColumnas);
    if($result != "valided"){
        $smarty->assign("mb_title",_tr('ERROR').":");
        $smarty->assign("mb_message", $result);
        return false;
    }

    if(!$pValidator->validar('endpoint_mask', $endpoint_mask, 'ip/mask')){
        $smarty->assign("mb_title",_tr('ERROR').":");
        $smarty->assign("mb_message",_tr('Invalid Format IP address'));
        return false;
    }

    $pattonDevices = $paloEndPoint->getPattonDevices();
    $arrles  = $paloEndPoint->endpointMap($endpoint_mask,$arrVendor,$arrEndpointsConf,$pattonDevices,true);
    
    if(!(is_array($arrles) && count($arrles) > 0)){
        $smarty->assign("mb_title",_tr('ERROR').":");
        $smarty->assign("mb_message",_tr("There weren't  endpoints in the subnet."));
        return false;
    }

    $hArchivo = fopen($ruta_archivo_csv, 'r+');
    $lineProcessed = 0;
    $line = 0;
    $msg  = "";
    if($hArchivo){
        $paloFileEndPoint = new PaloSantoFileEndPoint($arrConf["tftpboot_path"],$endpoint_mask);

        //Linea 1 header ignorada
        $tupla = fgetcsv($hArchivo, 4096, ",");
        //Desde linea 2 son datos
        while ($tupla = fgetcsv($hArchivo, 4096, ",")) {
            $line++;
            if(is_array($tupla) && count($tupla)>=4){
                $arrEndpoint = csv2Array($tupla, $arrayColumnas);

                if($arrEndpoint['data']==null)
                    $msg .= _tr("Line")." $line: $arrEndpoint[msg] <br />";
                else{
                    $name_model = $arrEndpoint['data']['Model'];
                    $extension  = $arrEndpoint['data']['Ext'];
                    $MAC    = $arrEndpoint['data']['MAC'];
                    $macTMP = strtolower($MAC);
                    $macTMP = str_replace(":","",$macTMP);
                    
                    if(isset($arrles[$macTMP])){ // Si el endpoint fue encontrado en la red.
                        
                        $currentEndpointIP = $arrles[$macTMP]['ip_adress'];
                       
                        $tech = $paloEndPoint->getTech($extension);
                        $freePBXParameters = $paloEndPoint->getDeviceFreePBXParameters($extension,$tech);

                        if(!(is_array($freePBXParameters) && count($freePBXParameters)>0)){
                            $msg .= _tr("Line")." $line: "._tr("Extension")."  $extension (tech:$tech)"._tr("has not been created.")."<br />";
                            continue;
                        }
                        
                        $dataVendor = $paloEndPoint->getVendor(substr($MAC,0,8));
			if($dataVendor["name"]=="Grandstream"){
			   $arr = $paloFileEndPoint->getModelElastix("admin","admin",$currentEndpointIP,2);
			   if($arr){
			      $endpointElastix   = $paloEndPoint->getVendorByName("Elastix");
			      $dataVendor["id"]  = $endpointElastix["id"];
			      $dataVendor["name"]= $endpointElastix["name"];
			   }

			}
            //Bloque agregado para manejar ciertos telefonos Voptech ya que tienen la misma porcion de MAC de vendor que Fanvil         		
            if($dataVendor["name"] == "Fanvil"){	
				$var = $paloFileEndPoint->isVendorVoptech("admin","admin",$currentEndpointIP,2);
				if($var){
					$endpointVoptech = $paloEndPoint->getVendorByName("Voptech");
					$dataVendor["id"]	 = $endpointVoptech["id"];
					$dataVendor["name"] = $endpointVoptech["name"];
				}
			}
                        $dataModel  = $paloEndPoint->getModelByVendor($dataVendor["id"],$name_model);
                        if(!(is_array($dataVendor) && count($dataVendor)>0)){
                            $msg .= _tr("Line")." $line: Vendor $dataVendor[name]"._tr("not supported.")."<br />";
                            continue;
                        }

                        if(!(is_array($dataModel) && count($dataModel)>0)){ //No existe el modelo
                            $msg .= _tr("Line")."$line: Model $name_model of vendor $dataVendor[name]"._tr("not supported.")."<br />";
                            continue;
                        }
                                
                        $tmpEndpoint['id_device']   = $freePBXParameters['id_device'];
                        $tmpEndpoint['desc_device'] = $freePBXParameters['desc_device'];
                        $tmpEndpoint['account']     = $freePBXParameters['account_device'];
                        $tmpEndpoint['secret']      = $freePBXParameters['secret_device'];
                        $tmpEndpoint['id_model']    = $dataModel["id"];
                        $tmpEndpoint['mac_adress']  = $MAC;
                        $tmpEndpoint['id_vendor']   = $dataVendor["id"];
                        $tmpEndpoint['name_vendor'] = $dataVendor["name"];
                        $tmpEndpoint['ip_adress']   = $currentEndpointIP;
                        $tmpEndpoint['comment']     = "Nada";

                        $arrParametersOld = $paloEndPoint->getParameters($MAC);
                        $arrParameters    = $paloFileEndPoint->updateArrParameters($dataVendor["name"], $name_model, $arrParametersOld);
                        $tmpEndpoint['arrParameters']=array_merge($arrParameters,$arrEndpoint['data']);
                        
                        if($paloEndPoint->createEndpointDB($tmpEndpoint)){
                            //verifico si la funcion createFilesGlobal del vendor ya fue ejecutado
                            if(!in_array($dataVendor["name"],$arrFindVendor)){
                                if($paloFileEndPoint->createFilesGlobal($dataVendor["name"]))
                                    $arrFindVendor[] = $dataVendor["name"];
                            }

                            //escribir archivos
                            $ArrayData['vendor'] = $dataVendor["name"];
                            $ArrayData['data'] = array(
                                "filename"     => strtolower(str_replace(":","",$MAC)),
                                "DisplayName"  => $tmpEndpoint['desc_device'],
                                "id_device"    => $tmpEndpoint['id_device'],
                                "secret"       => $tmpEndpoint['secret'],
                                "model"        => $dataModel['name'],
                                "ip_endpoint"  => $tmpEndpoint['ip_adress'],
                                "arrParameters"=> $tmpEndpoint['arrParameters'],
                                "tech"         => $tech
                            );

                            if(!$paloFileEndPoint->createFiles($ArrayData)){
                                    if(isset($paloFileEndPoint->errMsg))
                                        $msg .= _tr("Line")."$line: "._tr("$paloFileEndPoint->errMsg.")."<br />";
                                    else
                                        $msg .= _tr("Line")."$line: "._tr("Error, Technology Device (SIP/IAX) not supported on endpoint.")."<br />";
                            }
                            else
                                $lineProcessed++;
                        }                                   
                    }
                    else{
                        
                        $v = isset($dataVendor['name'])?$dataVendor['name']:$arrEndpoint['data']['Vendor'];
                        $msg .= _tr("Line")."$line: " ._tr("Endpoint wasn't founded in subnet.")."(vendor:$v - model:$name_model - Ext:$extension)<br />";
                    }
                }                     
            }                        
        }
    
        $smarty->assign("mb_title",_tr('Resume').":");
        $msg = _tr("Total endpoint processed").": $lineProcessed <br /> <br />$msg";
        $smarty->assign("mb_message", $msg);
        unlink($ruta_archivo_csv);
    }
    return true;
}

function csv2Array($tupla, $arrayColumnas)
{
    $Vendor    = $tupla[$arrayColumnas[0]];
    $Model     = $tupla[$arrayColumnas[1]];
    $MAC       = $tupla[$arrayColumnas[2]];
    $Ext       = $tupla[$arrayColumnas[3]];
    $IP        = isset($arrayColumnas[4])?$tupla[$arrayColumnas[4]]:"";
    $Mask      = isset($arrayColumnas[5])?$tupla[$arrayColumnas[5]]:"";
    $GW        = isset($arrayColumnas[6])?$tupla[$arrayColumnas[6]]:"";
    $DNS1       = isset($arrayColumnas[7])?$tupla[$arrayColumnas[7]]:"";
    $Bridge    = isset($arrayColumnas[8])?$tupla[$arrayColumnas[8]]:0;
    $Time_Zone = isset($arrayColumnas[9])?$tupla[$arrayColumnas[9]]:"";
    $DNS2       = isset($arrayColumnas[10])?$tupla[$arrayColumnas[10]]:"";

    if(!preg_match("/^[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}$/",strtolower($MAC))){
        $Messages = _tr("Invalid MAC Address") ." $MAC";
        return array("data" => null, "msg" => $Messages);
    }

    if(!empty($Bridge) && !preg_match("/^[01]$/",$Bridge)){
        $Messages = _tr("Invalid Bridge") ." $Bridge";
        return array("data" => null, "msg" => $Messages);
    }
    
    if (!empty($IP)){ // LA IP NO ESTA VACIA
        if(validaIpMask($IP) != "valided"){
            $Messages = _tr("Invalid IP address")." $IP";
            return array("data" => null, "msg" => $Messages);
        }

        if (empty($Mask)){
            $Messages = _tr("Mask is empty");
            return array("data" => null, "msg" => $Messages);
        }

        if (validaIpMask($Mask) != "valided"){
            $Messages = _tr("Invalid Mask address") ." $Mask";
            return array("data" => null, "msg" => $Messages);
        }

        if (!empty($GW) && validaIpMask($GW) != "valided"){
            $Messages = _tr("Invalid GW address") ." $GW";
            return array("data" => null, "msg" => $Messages);
        }

        if (!empty($DNS1) && validaIpMask($DNS1) != "valided"){
            $Messages = _tr("Invalid DNS1 address") ." $DNS1";
            return array("data" => null, "msg" => $Messages);
        }

        if (!empty($DNS2) && validaIpMask($DNS2) != "valided"){
            $Messages = _tr("Invalid DNS2 address") ." $DNS2";
            return array("data" => null, "msg" => $Messages);
        }

        $endpoint = array(   
            "Vendor"    => $Vendor,
            "Model"     => $Model,
            "MAC"       => $MAC,
            "Ext"       => $Ext,
            "IP"        => $IP,
            "Mask"      => $Mask,
            "GW"        => $GW,
            "DNS1"      => $DNS1,
            "Bridge"    => $Bridge,
            "Time_Zone" => $Time_Zone,
            "DNS2"      => $DNS2,
            "By_DHCP"   => 0);
        return array("data" => $endpoint, "msg" => "OK");
    }
    else{
        $endpoint = array(  
            "Vendor"    => $Vendor,
            "Model"     => $Model,
            "MAC"       => $MAC,
            "Ext"       => $Ext,
            "IP"        => $IP,
            "Mask"      => "",
            "GW"        => "",
            "DNS1"      => "",
            "Bridge"    => $Bridge,
            "Time_Zone" => $Time_Zone,
            "DNS2"      => "",
            "By_DHCP"   => 1);
        return array("data" => $endpoint, "msg" => "OK");
    }
}

function viewFormBatchofEndpoint($smarty, $module_name, $local_templates_dir, $arrConf)
{
    $arrFormBatchofEndpoint = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormBatchofEndpoint);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataBatchofEndpoint = $pBatchofEndpoint->getBatchofEndpointById($id);
        if(is_array($dataBatchofEndpoint) & count($dataBatchofEndpoint)>0)
            $_DATA = $dataBatchofEndpoint;
        else{
            $smarty->assign("mb_title", _tr("Error get Data"));
            $smarty->assign("mb_message", $pBatchofEndpoint->errMsg);
        }
    }

    if(empty($_DATA['endpoint_mask']))
        $_DATA['endpoint_mask'] = network();

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "images/list.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Batch of Endpoint"), $_DATA);
    $content = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}


function createFieldForm()
{
    $arrFields = array(
            "file"              => array(   "LABEL"                  => _tr("file"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "FILE",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                         ),
            "endpoint_mask"     => array(   "LABEL"                  => _tr("Discover Endpoints in this Network"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                        ),

            );
    return $arrFields;
}

function isValidCSV($arrLang, $sFilePath, &$arrayColumnas)
{
    $hArchivo = fopen($sFilePath, 'r+');
    $cont = 0;
    $ColName = -1;

    //Paso 1: Obtener Cabeceras (Minimas las cabeceras: Vendor, Model, Mac, Ext)
    if ($hArchivo) {
        $tupla = fgetcsv($hArchivo, 4096, ",");
        if(count($tupla)>=4)
        {
            for($i=0; $i<count($tupla); $i++)
            {
                if($tupla[$i] == 'Vendor')              $arrayColumnas[0] = $i;
                else if($tupla[$i] == 'Model')          $arrayColumnas[1] = $i;
                else if($tupla[$i] == 'MAC')            $arrayColumnas[2] = $i;
                else if($tupla[$i] == 'Ext')            $arrayColumnas[3] = $i;
                else if($tupla[$i] == 'IP')             $arrayColumnas[4] = $i;
                else if($tupla[$i] == 'Mask')           $arrayColumnas[5] = $i;
                else if($tupla[$i] == 'GW')             $arrayColumnas[6] = $i;
                else if($tupla[$i] == 'DNS1')           $arrayColumnas[7] = $i;
                else if($tupla[$i] == 'Bridge')         $arrayColumnas[8] = $i;
                else if($tupla[$i] == 'Time Zone')      $arrayColumnas[9] = $i;
                else if($tupla[$i] == 'DNS2')           $arrayColumnas[10] = $i;
            }  
            if(isset($arrayColumnas[0]) && isset($arrayColumnas[1]) && isset($arrayColumnas[2]) && isset($arrayColumnas[3]))
            {
                //Paso 2: Obtener Datos (Validacion que esten llenos los mismos de las cabeceras)
                $count = 2;
                $tupla = fgetcsv($hArchivo, 4096,",");
                while ($tupla = fgetcsv($hArchivo, 4096,",")) {
                    if(is_array($tupla) && count($tupla)>=3)
                    {
                        $Vendor = $tupla[$arrayColumnas[0]];                        
                        if($Vendor == '')
                            return _tr("Can't exist a vendor empty. Line").": $count. - ". _tr("Please read the lines in the footer");

                        $Model = $tupla[$arrayColumnas[1]];                      
                        if($Model == '')
                            return _tr("Can't exist a model empty. Line").": $count. - ". _tr("Please read the lines in the footer");

                        $Mac = $tupla[$arrayColumnas[2]];
                        if($Mac == '')
                            return _tr("Can't exist a mac name empty. Line").": $count. - ". _tr("Please read the lines in the footer");

                        $Ext = $tupla[$arrayColumnas[3]];
                        if($Ext == '')
                            return _tr("Can't exist a ext empty. Line").": $count. - ". _tr("Please read the lines in the footer");
                        
                    }
                    $count++;
                }return "valided";
            }else return _tr("Verify the header") ." - ". _tr("At minimum there must be the columns").": \"Vendor\", \"Model\", \"MAC\", \"Ext\"";
        }
        else return _tr("Verify the header") ." - ". _tr("Incomplete Columns");
    }else return _tr("The file is incorrect or empty") .": $sFilePath";
}

function getAction()
{
    if(getParameter("save")) //Get parameter by POST (submit)
        return "load_endpoint";
    elseif(getParameter("accion"))
        return "download_csv";
}

function validaIpMask ($IpMask)
{
    $pattern = "/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/";
        if ($IpMask == "0.0.0.0"){
            return "invalided";
        }
        elseif(!preg_match($pattern,$IpMask)){
            return "invalided";
        }
        else{
            return "valided";
        }
}

function network()
{
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

function download_endpoints($dsnAsterisk, $dsnSqlite)
{
    header("Cache-Control: private");
    header("Pragma: cache");

	$sDSN = $dsnSqlite.'/endpoint.db';
    $pEndpointDownload = new paloSantoEndPointDownload($sDSN);
    $r = $pEndpointDownload->reportEndpointParameters();
    if (!is_array($r)) {
    	print $pEndpointDownload->errMsg;
        return;
    }
    
    /* El siguiente código depende de forma fundamental del hecho de que PHP
     * preserve el orden de las claves y valores en las funciones implode()
     * y array_keys(). */
    header('Content-Type: text/csv; charset=iso-8859-1; header=present');
    header('Content-disposition: attachment; filename=endpoints.csv');
    $keyOrder = array(
        'Vendor'    => 'Vendor',
        'Model'     => 'Model',
        'MAC'       => 'MAC',
        'Ext'       =>  'Ext',
        'IP'        =>  'IP',
        'Mask'      =>  'Mask',
        'GW'        =>  'GW',
        'DNS1'      =>  'DNS1',
        'Bridge'    =>  'Bridge',
        'Time_Zone' =>  'Time Zone',
        'DNS2'      =>  'DNS2',
    );
    print '"'.implode('","', $keyOrder)."\"\n";
    
    foreach ($r as $tupla) {
    	$t = array();
        foreach (array_keys($keyOrder) as $k) switch ($k) {
        case 'Vendor':  $t[] = $tupla['vendor_name']; break;
        case 'Model':   $t[] = $tupla['model_name']; break;
        case 'MAC':     $t[] = $tupla['mac_adress']; break;
        case 'Ext':     $t[] = $tupla['account']; break;
        default:
            $t[] = (isset($tupla['parameters'][$k])) ? $tupla['parameters'][$k] : '';
        }
        
        print '"'.implode('","', $t)."\"\n";
    }
}
?>
