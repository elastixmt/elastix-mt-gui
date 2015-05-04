<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.6-12                                               |
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
  $Id: index.php,v 1.1 2009-11-12 04:11:04 Oscar Navarrete onavarrete.palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
   
    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);

    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        case "new_dhcpconft":
            $content = viewFormDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $edit="false");
            break;
        case "view_dhcpconf":
            $content = viewFormDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $edit="false");
            break;
        case "edit_dhcpconf":
            $content = viewFormDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $edit="true");
            break;
        case "update_dhacp":
            $content = saveDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, true);
            break;
        case "save_dhcp":
            $content = saveDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "delete_dhcpConf":
            $content = deleteDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default:
            $content = reportDHCP_Configuration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function reportDHCP_Configuration($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pDHCP_Configuration = new paloSantoDHCP_Configuration($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $action = getParameter("nav");
    $start  = getParameter("start");
    
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
        $nameOpt = array(
        "hostname" => _tr('Host Name'),
        "ipaddress" => _tr('IP Address'),
        "macaddress" => _tr('MAC Address'),
                    );

    if(isset($nameOpt[$filter_field])){
        $valorFiltro = $nameOpt[$filter_field];
    }else
        $valorFiltro = "";

    $oGrid->addFilterControl(_tr("Filter applied ")." ".$valorFiltro." = $filter_value", $_POST, array("filter_field" => "hostname","filter_value" => ""));

    $totalDHCP_Configuration = $pDHCP_Configuration->contarIpFijas($filter_field, $filter_value);

    $oGrid->addNew("new_dhcpconft",_tr("Assign IP Address"));
    $oGrid->deleteList("Are you sure you wish to delete the DHCP configuration.","delete_dhcpConf",_tr("Delete"));

    $limit  = 20;
    $total  = $totalDHCP_Configuration;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();

    $arrData = null;
    $arrResult = $pDHCP_Configuration->leerIPsFijas($limit, $offset, $filter_field, $filter_value);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 
        $arrTmp[0]  = "<input type='checkbox' name='DhcpConfID_{$value['id']}' />";
        $arrTmp[1] = "<a href='?menu=$module_name&action=view_dhcpconf&id=".$value['id']."'>".$value['hostname']."</a>";;
        $arrTmp[2] = $value['ipaddress'];
        $arrTmp[3] = $value['macaddress'];
            $arrData[] = $arrTmp;
        }
    }

    $buttonDelete = "";

    $arrGrid = array("title"    => _tr('Assign IP Address to Host'),
                        "icon"     => "web/apps/$module_name/images/system_network_assign_ip_address.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => array('menu' => $module_name, 'filter_field' => $filter_field, 'filter_value' => $filter_value),
                        "columns"  => array(
                0 => array("name"      => $buttonDelete,
                                    "property1" => ""),
                1 => array("name"      => _tr('Host Name'),
                                    "property1" => ""),
                2 => array("name"      => _tr('IP Address'),
                                    "property1" => ""),
                3 => array("name"      => _tr('MAC Address'),
                                    "property1" => ""),
                            )
                    );

    //begin section filter
    $arrFormFilterDHCP_Configuration = createFieldFilter();
    $oFilterForm = new paloForm($smarty, $arrFormFilterDHCP_Configuration);
    $smarty->assign("SHOW", _tr('Show'));

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData);
    //end grid parameters

    return $contenidoModulo;
}


function createFieldFilter(){
    $arrFilter = array(
        "hostname" => _tr('Host Name'),
        "ipaddress" => _tr('IP Address'),
        "macaddress" => _tr('MAC Address'),
                    );

    $arrFormElements = array(
            "filter_field" => array("LABEL"                  => _tr('Search'),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => $arrFilter,
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
            "filter_value" => array("LABEL"                  => "",
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => array("id" => "filter_value"),
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
                    );
    return $arrFormElements;
}


function viewFormDHCP_Configuration($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $edit="true")
{
    $pDHCP_Configuration = new paloSantoDHCP_Configuration($pDB);

    $arrFormDHCP_Configuration = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormDHCP_Configuration);
    
    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl
    
    if($action=="view_dhcpconf"){
        $oForm->setViewMode();
    }else if($edit=="true"){
        $oForm->setEditMode();
    }

    //end, Form data persistence to errors and other events.
    if($action=="view_dhcpconf" || $edit=="true"){ // the action is to view or view_edit.
        $dataDhcpConfig = $pDHCP_Configuration->leerInfoIPFija($id);
        if(is_array($dataDhcpConfig) & count($dataDhcpConfig)>0)
            $_DATA = $dataDhcpConfig;
        else{
            $smarty->assign("mb_title", _tr('Error get Data'));
            $smarty->assign("mb_message", $pDHCP_Configuration->errMsg);
        }
    }

    $smarty->assign("SAVE", _tr('Save'));
    $smarty->assign("EDIT", _tr('Edit'));
    $smarty->assign("CANCEL", _tr('Cancel'));
    $smarty->assign("REQUIRED_FIELD", _tr('Required field'));
    $smarty->assign("icon", "web/apps/$module_name/images/system_network_assign_ip_address.png");
    $smarty->assign("HOST_NAME", _tr('ex_hostname'));
    $smarty->assign("IP_ADDRESS", _tr('ex_ipaddress'));
    $smarty->assign("MAC_ADDRESS", _tr('ex_mac_address'));

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr('Assign IP Address to Host'), $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}


function saveDHCP_Configuration($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $update=FALSE){
    $pDHCP_Configuration = new paloSantoDHCP_Configuration($pDB);
    $arrFormDHCP_Configuration = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormDHCP_Configuration);
    if ($update) $oForm->setEditMode();   

    $smarty->assign("REQUIRED_FIELD", _tr('Required field'));
    $smarty->assign("SAVE", _tr('Save'));
    $smarty->assign("EDIT", _tr('Edit'));
    $smarty->assign("CANCEL", _tr('Cancel'));
    $smarty->assign("icon", "web/apps/$module_name/images/system_network_assign_ip_address.png");
    $smarty->assign("ID", getParameter('id'));
    
    if(!$oForm->validateForm($_POST)) {
        // Falla la validación básica del formulario
        $smarty->assign("mb_title", _tr('Validation Error'));
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr('The following fields contain errors').":</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }
        $smarty->assign("mb_message", $strErrorMsg);

        $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", _tr('Assign IP Address to Host'), $_POST);
        $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
        return $contenidoModulo;
    }else {
        $arrDhcpPost = array();
        $hostname = getParameter("hostname");
        if(preg_match("/^([a-zA-Z]+)[[:space:]]+([a-zA-Z]+)$/", $hostname, $arrReg))
            $arrDhcpPost['hostname'] = $arrReg[1]."_".$arrReg[2];
        else $arrDhcpPost['hostname'] = getParameter("hostname");

        $arrDhcpPost['ipaddress'] = getParameter("ipaddress");
        $arrDhcpPost['macaddress'] = getParameter("macaddress");
        
        if($update){
            $id = getParameter("id");
            $r = $pDHCP_Configuration->actualizarIpFija($id, $arrDhcpPost['hostname'], $arrDhcpPost['ipaddress'], $arrDhcpPost['macaddress']);
        }else{
            $r = $pDHCP_Configuration->insertarIpFija($arrDhcpPost['hostname'], $arrDhcpPost['ipaddress'], $arrDhcpPost['macaddress']);
        }
        if (!$r) {
            $smarty->assign("mb_message", $pDHCP_Configuration->errMsg);
    
            $smarty->assign("REQUIRED_FIELD", _tr('Required field'));
            $smarty->assign("SAVE", _tr('Save'));
            $smarty->assign("CANCEL", _tr('Cancel'));
            $smarty->assign("icon", "web/apps/$module_name/images/system_network_assign_ip_address.png");
    
            $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", _tr('Assign IP Address to Host'), $_POST);
            $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
            return $contenidoModulo;
        } else {
            header("Location: ?menu=$module_name&action=show");
        }
    }
}

function deleteDHCP_Configuration($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf){
    $pDHCP_Configuration = new paloSantoDHCP_Configuration($pDB);
    foreach($_POST as $key => $values){
        if(substr($key,0,11) == "DhcpConfID_")
        {
            $dhcpConfId = substr($key, 11);
            $pDHCP_Configuration->borrarIpFija($dhcpConfId);
        }
    }

    header("Location: ?menu=$module_name&action=show");
}

function createFieldForm()
{
    $arrFields = array(
            "hostname"   => array(      "LABEL"                  => _tr('Host Name'),
                                            "DESCRIPTION"            => _tr("DCHBYMAC_hostname"),  
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "ipaddress"   => array(      "LABEL"                  => _tr('IP Address'),
                                            "DESCRIPTION"            => _tr("DCHBYMAC_ipaddress"),  
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "ip",
                                            //"VALIDATION_EXTRA_PARAM" => "([0-9]){1,3}.([0-9]+){1,3}.([0-9]+){1,3}.([0-9]+){1,3}$"
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "macaddress"   => array(      "LABEL"                  => _tr('MAC Address'),
                                            "DESCRIPTION"            => _tr("DCHBYMAC_macaddress"),  
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "ereg",//AGREGAR MAC VALIDACION AL FRAMEWORD PALOSANTOVALIDAR
                                            "VALIDATION_EXTRA_PARAM" => "([a-fA-F0-9]{2}):([a-fA-F0-9]{2}):([a-fA-F0-9]{2}):([a-fA-F0-9]{2}):([a-fA-F0-9]{2}):([a-fA-F0-9]{2})$"
                                            ),
            );
    return $arrFields;
}

function getAction()
{
    if(getParameter("show")) //Get parameter by POST (submit)
        return "show";
    else if(getParameter("new_dhcpconft"))
        return "new_dhcpconft";
    else if(getParameter("edit_dhcpconf"))
        return "edit_dhcpconf";
    else if(getParameter("delete_dhcpConf"))
        return "delete_dhcpConf";
    else if(getParameter("action")=="view_dhcpconf")
        return "view_dhcpconf";
    else if(getParameter("update_dhacp"))
        return "update_dhacp";
    else if(getParameter("save_dhcp"))
        return "save_dhcp";
    else if(getParameter("action")=="show") //Get parameter by GET (command pattern, links)
        return "show";
    else
        return "report";
}

?>
