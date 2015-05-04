<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.6-6                                               |
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
  $Id: index.php,v 1.1 2009-08-26 09:08:29 Oscar Navarrete onavarrete@palosanto.com Exp $ */
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
    $action = getAction();
    $content = "";

    switch($action){
//         case "save_dhcpclient":
//             $content = saveNewDhcpClient($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
//             break;
        case "new_dhcpclient":
            $content = viewFormDhcpClientlist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "see_dhcpclient":
            $content = viewFormDhcpClientlist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
//         case "delete_list":
//             $content = delete_emailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
//             break;
        default: 
            $content = reportDhcpClientlist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function reportDhcpClientlist($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pDhcpClientlist = new paloSantoDhcpClienList($pDB);
    $filter_field = "";
    $filter_value = "";
    $action = getParameter("nav");
    $start  = getParameter("start");
    
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $limit  = 20;
    
    $arrResult = $pDhcpClientlist->getDhcpClientList();
    $total  = count($arrResult);

    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);

    $oGrid->calculatePagination($action, $start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name";
    $arrData = null;

    if(is_array($arrResult) && count($arrResult)>0){
        for($i=1 ; $i<=$total; $i++){
            //$arrTmp[0]  = "<input type='checkbox' name='DhcpListID_$i'  />";
//          $arrTmp[1] = "<a href='?menu=$module_name&action=show&id=".$id."'>".$arrResult[$id]['iphost']."</a>";
            $arrTmp[0] = "".$arrResult[$i]['iphost'];
            $arrTmp[1] = "".$arrResult[$i]['macaddress'];
            $arrTmp[2] = "<a href='?menu=$module_name&action=see_dhcpclient&id=".$i."'>"._tr("View Details")."</a>";
    
            $arrData[] = $arrTmp;
        }
    }

    $buttonDelete = "<input type='submit' name='delete_dhcpclient' value='"._tr("Delete")."' class='button' onclick=\" return confirmSubmit('"._tr("Are you sure you wish to delete the Ip.")."');\" />";

    $arrGrid = array("title"    => _tr("DHCP Client List"),
                        "icon"     => "web/apps/$module_name/images/system_network_dhcp_client_list.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
//             0 => array("name"      => $buttonDelete,
//                                    "property1" => ""),
            0 => array("name"      => _tr("IP Address"),
                                   "property1" => ""),
            1 => array("name"      => _tr("MAC Address"),
                                   "property1" => ""),
            2 => array("name"      => _tr("Action"),
                                   "property1" => ""),
                                        )
                    );
    //begin section filter

   // $arrFormFilterDhcplist = createFieldFilter();
   // $oFilterForm = new paloForm($smarty, $arrFormFilterDhcplist);
//     $smarty->assign("SHOW", _tr("Show"));
    $smarty->assign("NEW_DHCPCLIENT", _tr("New Dhcp client"));

  //  $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

  //  $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData);
    if (strpos($contenidoModulo, '<form') === FALSE)
        $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action=$url>$contenidoModulo</form>";
    //end grid parameters

    return $contenidoModulo;
}

function createFieldFilter(){

    $arrFormElements = array(
//             "domain"   => array("LABEL"          => _tr("Domain"),
//                                     "REQUIRED"               => "yes",
//                                     "INPUT_TYPE"             => "SELECT",
//                                     "INPUT_EXTRA_PARAM"      => $arrDominios,
//                                     "VALIDATION_TYPE"        => "text",
//                                     "VALIDATION_EXTRA_PARAM" => "",
//                                     "EDITABLE"               => "si", ),
                );
    return $arrFormElements;
}


function viewFormDhcpClientlist($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pDhcpClientlist = new paloSantoDhcpClienList($pDB);

    $arrFormDhcplist = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormDhcplist);
    
    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl

    if($action=="see_dhcpclient")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="see_dhcpclient" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataDhcplist = $pDhcpClientlist->getDhcpClientListById($id);
        if(is_array($dataDhcplist) & count($dataDhcplist)>0)
            $_DATA = $dataDhcplist;
        else{
            $smarty->assign("mb_title", _tr("Error get Data"));
            $smarty->assign("mb_message", $pDhcpClientlist->errMsg);
        }
    }

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "web/apps/$module_name/images/system_network_dhcp_client_list.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("View Details"), $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewDhcpClient($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pDhcpClientlist = new paloSantoDhcpClienList($pDB);
    
    $arrFormDhcplist = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormDhcplist);

    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE 
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr('The following fields contain errors').":</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
        //return $content = viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }else{
        $dataDhcpClient = array();
        $data = array();

//         $dataDhcpClient['iphost'] = $pDB->DBCAMPO($_POST['iphost']);
//         $dataDhcpClient['date_starts'] = $pDB->DBCAMPO($_POST['date_starts']);
//         $dataDhcpClient['date_ends'] = $pDB->DBCAMPO($_POST['date_ends']);
//         
//         $dataDhcpClient['macaddress'] = $pDB->DBCAMPO($_POST['macaddress']);
// 
//         $pDhcpClientlist->addNewMailList($_POST['emailadmin'], $_POST['password'], $_POST['namelist']);
        //$result = $pEmaillist->addEmailListDB($dataEmailList);
        
        header("Location: ?menu=$module_name&action=");
    }
}

function createFieldForm()
{
    $arrFields = array(
            "iphost"   => array(      "LABEL"                  => _tr("IP Address"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "date_starts"   => array(      "LABEL"                  => _tr("Start Date"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "date_ends"   => array(      "LABEL"                  => _tr("End Date"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "macaddress"   => array(      "LABEL"                  => _tr("MAC Address"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            );
    return $arrFields;
}

function getAction()
{
    if(getParameter("save_dhcpclient")) //Get parameter by POST (submit)
        return "save_dhcpclient";
    else if(getParameter("new_dhcpclient"))
        return "new_dhcpclient";
    else if(getParameter("delete_dhcpclient")) 
        return "delete_dhcpclient";
    else if(getParameter("action")=="see_dhcpclient")
        return "see_dhcpclient";
    else
        return "report"; //cancel
}
?>
