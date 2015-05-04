<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.8                                                  |
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
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoValidar.class.php";
    include_once "libs/paloSantoNetwork.class.php";


function _moduleContent(&$smarty, $module_name)
{
    //global variables
    global $arrConf;
    global $arrConfModule;
   
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    $arrFormDHCP = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormDHCP);

    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CONFIGURATION_UPDATE", _tr("Update"));
    $smarty->assign("SERVICE_START", _tr("Service Start"));
    $smarty->assign("SERVICE_STOP", _tr("Service Stop"));
    $smarty->assign("STATUS", _tr("Status"));
    $smarty->assign("START_RANGE_OF_IPS", _tr("Start range of IPs"));
    $smarty->assign("END_RANGE_OF_IPS", _tr("End range of IPs"));
    $smarty->assign("DNS_1", _tr("DNS 1"));
    $smarty->assign("DNS_2", _tr("DNS 2"));
    $smarty->assign("WINS", _tr("WINS"));
    $smarty->assign("GATEWAY", _tr("Gateway"));
    $smarty->assign("OPTIONAL", _tr("Optional"));
    $smarty->assign("icon", "web/apps/$module_name/images/system_network_dhcp_server.png");
    $smarty->assign("OF_1_TO_50000_SECONDS", _tr("Of 1 to 50000 Seconds"));

    if(isset($_POST["in_iniciar"])) $accion ="service_start";
    else if(isset($_POST["in_finalizar"])) $accion ="service_stop";
    else if(isset($_POST["in_actualizar_conf_red"])) $accion ="service_update";
    else $accion ="service_show";
    $content = "";
    
    switch($accion){
        case "service_start":
            $content = serviceStartDHCP($smarty, $module_name, $local_templates_dir, $oForm);
            break;
        case "service_stop":
            $content = serviceStopDHCP($smarty, $module_name, $local_templates_dir, $oForm);
            break;
        case "service_update":
            $content = serviceUpdateDHCP($smarty, $module_name, $local_templates_dir, $oForm);
            break;
        default: //service_show
            $content = serviceShowDHCP($smarty, $module_name, $local_templates_dir, $oForm);
            break;
    }
    return $content;
}

function createFieldForm()
{
    $arrFields =       array("in_ip_ini_1"  => array("LABEL"                  => "",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_ip_ini_2"  => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_ip_ini_3"  => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_ip_ini_4"  => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_ip_fin_1"  => array("LABEL"                  => "",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_ip_fin_2"  => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_ip_fin_3"  => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_ip_fin_4"  => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_lease_time"=> array("LABEL"                  => _tr('Time of client refreshment').":",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:70px;text-align:right","maxlength" =>"5"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_dns1_1"    => array("LABEL"                  => "",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_dns1_2"    => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_dns1_3"    => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_dns1_4"    => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_dns2_1"    => array("LABEL"                  => "",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_dns2_2"    => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_dns2_3"    => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_dns2_4"    => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_wins_1"    => array("LABEL"                  => "",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_wins_2"    => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_wins_3"    => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_wins_4"    => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_gw_1"      => array("LABEL"                  => "",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_gw_2"      => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_gw_3"      => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_gw_4"      => array("LABEL"                  => ". ",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => "")/*,
                             "in_gwm_1"     => array("LABEL"                  => "",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_gwm_2"     => array("LABEL"                  => "<b>. </b>",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_gwm_3"     => array("LABEL"                  => "<b>. </b>",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "in_gwm_4"     => array("LABEL"                  => "<b>. </b>",
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"3"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => "")*/);
    return $arrFields;
}

function serviceStartDHCP($smarty, $module_name, $local_templates_dir, &$oForm)
{
    $paloDHCP = new PaloSantoDHCP();
    if(!$paloDHCP->startServiceDHCP()){
        $smarty->assign("mb_title",_tr('Information'));
        $smarty->assign("mb_message",_tr("The Service dhcpd can't start"));
        return $oForm->fetchForm("$local_templates_dir/dhcp.tpl", _tr("DHCP Configuration"), $_POST);
    }

    $statusDHCP = $paloDHCP->getStatusServiceDHCP();
    if($statusDHCP=="active"){
        $smarty->assign("DHCP_STATUS","<font color='#00AA00'>". _tr('Active')."</font>");
        $smarty->assign("SERVICE_STARING",true);
    }
    else if($statusDHCP=="desactive"){
        $smarty->assign("DHCP_STATUS","<font color='#FF0000'>"._tr('Inactive'). "</font>");
        $smarty->assign("SERVICE_STARING",false);
    }
    header("Location: ?menu=$module_name");
}

function serviceStopDHCP($smarty, $module_name, $local_templates_dir, &$oForm)
{
    // Intentar terminar el servicio
    $paloDHCP = new PaloSantoDHCP();
    if(!$paloDHCP->stopServiceDHCP()){
        $smarty->assign("mb_title",_tr('Information'));
        $smarty->assign("mb_message",_tr("The Service dhcpd can't stop"));
        return $oForm->fetchForm("$local_templates_dir/dhcp.tpl", _tr("DHCP Configuration"), $_POST);
    }

    $statusDHCP = $paloDHCP->getStatusServiceDHCP();
    if($statusDHCP=="active"){
        $smarty->assign("DHCP_STATUS","<font color='#00AA00'>"._tr('Active')."</font>");
        $smarty->assign("SERVICE_STARING",true);
    }
    else if($statusDHCP=="desactive"){
        $smarty->assign("DHCP_STATUS","<font color='#FF0000'>"._tr('Inactive')."</font>");
        $smarty->assign("SERVICE_STARING",false);
    }
    header("Location: ?menu=$module_name");
}

function serviceShowDHCP($smarty, $module_name, $local_templates_dir, &$oForm)
{
    $paloDHCP = new PaloSantoDHCP();
    $arrConfiguration = $paloDHCP->getConfigurationDHCP();
    $statusDHCP = $paloDHCP->getStatusServiceDHCP();

    if($statusDHCP=="active"){
        $smarty->assign("DHCP_STATUS","<font color='#00AA00'>"._tr('Active')."</font>");
        $smarty->assign("SERVICE_STARING",true);
    }
    else if($statusDHCP=="desactive"){
        $smarty->assign("DHCP_STATUS","<font color='#FF0000'>"._tr('Inactive')."</font>");
        $smarty->assign("SERVICE_STARING",false);
    }

    $arrTmp = array();
    if (is_array($arrConfiguration)) {
        foreach($arrConfiguration as $key => $arrValues){
            $arrTmp = array_merge($arrTmp,$arrValues);
        }
    }
    return $oForm->fetchForm("$local_templates_dir/dhcp.tpl", _tr("DHCP Configuration"), $arrTmp);
}

function serviceUpdateDHCP($smarty, $module_name, $local_templates_dir, &$oForm)
{
    $in_ip_ini_1 = trim($_POST['in_ip_ini_1']); $in_ip_ini_2 = trim($_POST['in_ip_ini_2']); 
    $in_ip_ini_3 = trim($_POST['in_ip_ini_3']); $in_ip_ini_4 = trim($_POST['in_ip_ini_4']); 
    $ip_ini      = "$in_ip_ini_1.$in_ip_ini_2.$in_ip_ini_3.$in_ip_ini_4";
    
    $in_ip_fin_1 = trim($_POST['in_ip_fin_1']); $in_ip_fin_2 = trim($_POST['in_ip_fin_2']);
    $in_ip_fin_3 = trim($_POST['in_ip_fin_3']); $in_ip_fin_4 = trim($_POST['in_ip_fin_4']); 
    $ip_fin   = "$in_ip_fin_1.$in_ip_fin_2.$in_ip_fin_3.$in_ip_fin_4";

    $in_dns1_1 = trim($_POST['in_dns1_1']); $in_dns1_2 = trim($_POST['in_dns1_2']);
    $in_dns1_3 = trim($_POST['in_dns1_3']); $in_dns1_4 = trim($_POST['in_dns1_4']);
    $ip_dns1   = "$in_dns1_1.$in_dns1_2.$in_dns1_3.$in_dns1_4";

    $in_dns2_1 = trim($_POST['in_dns2_1']); $in_dns2_2 = trim($_POST['in_dns2_2']);
    $in_dns2_3 = trim($_POST['in_dns2_3']); $in_dns2_4 = trim($_POST['in_dns2_4']);
    $ip_dns2   = "$in_dns2_1.$in_dns2_2.$in_dns2_3.$in_dns2_4";

    $in_wins_1 = trim($_POST['in_wins_1']); $in_wins_2 = trim($_POST['in_wins_2']);
    $in_wins_3 = trim($_POST['in_wins_3']); $in_wins_4 = trim($_POST['in_wins_4']);
    $ip_wins   = "$in_wins_1.$in_wins_2.$in_wins_3.$in_wins_4";

    $in_gw_1 = trim($_POST['in_gw_1']); $in_gw_2 = trim($_POST['in_gw_2']);
    $in_gw_3 = trim($_POST['in_gw_3']); $in_gw_4 = trim($_POST['in_gw_4']);
    $ip_gw   = "$in_gw_1.$in_gw_2.$in_gw_3.$in_gw_4";

    /*$in_gw_nm_1 = trim($_POST['in_gwm_1']); $in_gw_nm_2 = trim($_POST['in_gwm_2']);
    $in_gw_nm_3 = trim($_POST['in_gwm_3']); $in_gw_nm_4 = trim($_POST['in_gwm_4']);
    $ip_gw_nm   = "$in_gw_nm_1.$in_gw_nm_2.$in_gw_nm_3.$in_gw_nm_4";*/
    $ip_gw_nm = "";

    $in_lease_time = trim($_POST['in_lease_time']);

    // Rango de IPs
    $val = new PaloValidar();
    $val->clear();
    $continuar=$val->validar(_tr("Start range of IPs"),$ip_ini,"ip") && $val->validar(_tr("End range of IPs"),$ip_fin,"ip");

    //si las IPs son válidas continuo validando si concuerdan con alguna interfaz
    //obtener las interfaces del sistema y verificar que el rango de ips pertenecen a alguna de las interfases
 
    $paloNet = new paloNetwork();
    $paloDHCP = new PaloSantoDHCP();
    $interfazEncontrada=FALSE;
    $configuracion_de_red_actual = array();

    if($continuar){
        $interfases=$paloNet->obtener_interfases_red();
        $valorIP="";
        $valorMask="";
        $IpSubred="";

        foreach($interfases as $dev=>$datos){
            if (($dev != "lo") && !$interfazEncontrada){
                $valorIP=$datos["Inet Addr"];
                $valorMask=$datos["Mask"];
                if(isset($val->arrErrores[_tr("Start range of IPs")]['mensaje']))
                    unset($val->arrErrores[_tr("Start range of IPs")]);
                if(isset($val->arrErrores[_tr("End range of IPs")]['mensaje']))
                    unset($val->arrErrores[_tr("End range of IPs")]);
    
                $IPSubnet = $paloDHCP->calcularIpSubred($valorIP,$valorMask);
                //verificar si la IP inicial pertenece a esa red
                //si no pertenece se guarda mensaje de error, sino se verifica la Ip final
            
                $IpSubredIni = $paloDHCP->calcularIpSubred($ip_ini, $valorMask);
                if($IpSubredIni!=$IPSubnet)
                    $val->arrErrores[_tr("Start range of IPs")]['mensaje'] = _tr("The start IP is outside the LAN network");
                else{
                    $IpSubredFin = $paloDHCP->calcularIpSubred($ip_fin, $valorMask);
                    if($IpSubredFin!=$IPSubnet)
                        $val->arrErrores[_tr("End range of IPs")]['mensaje'] = _tr("The final IP is outside the LAN network");
                    else{
                        //encontro una interfaz, esta se adopta
                        $configuracion_de_red_actual["lan_mask"]=$valorMask;
                        $configuracion_de_red_actual["lan_ip"]=$valorIP;
                        $interfazEncontrada=TRUE;
                        break;
                    }
                }
            }
        }
    }
    if (!$interfazEncontrada){
        //el rango de Ips proporcionado no pertenece a ninguna de las interfases
        $val->arrErrores[_tr("DHCP Server")]['mensaje'] = _tr("IP Range is invalid for available devices");
    }

    if($ip_dns1!="...")$val->validar("DNS 1",$ip_dns1,"ip");
    if($ip_dns2!="...") $val->validar("DNS 2",  $ip_dns2,"ip");
    if($ip_wins!="...") $val->validar("WINS",  $ip_wins,"ip");
    if($ip_gw !="..."){
        if ($val->validar("Gateway",$ip_gw,"ip")){
            if ($interfazEncontrada) { 
                $IPSubnet   = $paloDHCP->calcularIpSubred($configuracion_de_red_actual["lan_ip"],$configuracion_de_red_actual["lan_mask"]);
                $IpSubredGw = $paloDHCP->calcularIpSubred($ip_gw, $configuracion_de_red_actual["lan_mask"]);
                if($IpSubredGw!=$IPSubnet)
                    $val->arrErrores['Gateway']['mensaje'] = _tr("Gateway is outside the LAN network");
            }
        }
    }
    $val->validar(_tr('Time of client refreshment'), $in_lease_time, "numeric");
    if($in_lease_time>50000 or $in_lease_time<1)
        $val->arrErrores[_tr('Time of client refreshment')]['mensaje'] = _tr("Value outside the range (1-50000)");

    //Veo si hubieron errores
    $msgErrorVal = "";
    $huboError = false;
    if($val->existenErroresPrevios() || !($interfazEncontrada)) {
        foreach($val->arrErrores as $nombreVar => $arrVar) {
            $msgErrorVal .= "<b>" . $nombreVar . "</b>: " . $arrVar['mensaje'] . "<br>";
        }
        $smarty->assign("mb_title",_tr("The following fields contain errors"));
        $smarty->assign("mb_message",$msgErrorVal);
        $huboError=true;
    }
    else {
        // Pase la validacion, empiezo a generar la data que constituira el archivo de configuracion nuevo
        if(!$paloDHCP->updateFileConfDHCP($ip_gw,$ip_gw_nm,$ip_wins,$ip_dns1,$ip_dns2,
                                          $IPSubnet,$configuracion_de_red_actual,
                                          $ip_ini,$ip_fin,$in_lease_time)){
            $smarty->assign("mb_title",_tr("Update Error").":");
            $smarty->assign("mb_message",$paloDHCP->errMsg); 
            $huboError=true;
        }
    }

    if($huboError){
        $statusDHCP = $paloDHCP->getStatusServiceDHCP();
        if($statusDHCP=="active"){
            $smarty->assign("DHCP_STATUS","<font color='#00AA00'>"._tr('Active')."</font>");
            $smarty->assign("SERVICE_STARING",true);
        }
        else if($statusDHCP=="desactive"){
            $smarty->assign("DHCP_STATUS","<font color='#FF0000'>"._tr('Inactive')."</font>");
            $smarty->assign("SERVICE_STARING",false);
        }
        return $oForm->fetchForm("$local_templates_dir/dhcp.tpl",_tr("DHCP Configuration"), $_POST);
    }
    header("Location: ?menu=$module_name");
}
?>
