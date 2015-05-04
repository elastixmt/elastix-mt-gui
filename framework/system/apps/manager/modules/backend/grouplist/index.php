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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:56 afigueroa Exp $ */

    include_once("libs/paloSantoDB.class.php");
    include_once("libs/paloSantoGrid.class.php");
    include_once("libs/paloSantoACL.class.php");
	include_once "libs/paloSantoOrganization.class.php";
    include_once("libs/paloSantoConfig.class.php");
	include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);


    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);

     //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //user credentials
    global $arrCredentials;
    $userLevel1=$arrCredentials['userlevel'];
    $userAccount=$_SESSION['elastix_user'];
    $idOrganization=$arrCredentials['id_organization'];

    $action = getAction();
    $content = "";

    switch($action){
        case "new_group":
            $content = viewFormGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
            break;
        case "view":
            $content = viewFormGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
            break;
        case "view_edit":
            $content = viewFormGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
            break;
        case "save_new":
            $content = saveNewGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
            break;
        case "save_edit":
            $content = saveEditGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
            break;
        case "delete":
            $content = deleteGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
            break;
        default: // report
            $content = reportGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
            break;
    }
    return $content;
}

function reportGroup($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $userLevel1, $userAccount, $idOrganization)
{
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $idOrgFil=getParameter("idOrganization");

    if($userLevel1=="superadmin"){
        if(!empty($idOrgFil)){
            $cntGroups=$pACL->getNumGroups($idOrgFil);
        }else{
            $idOrgFil=0; //opcion default se muestran todos los grupos
            $cntGroupsMO=$pACL->getNumGroups(1);//obtenemos en numero de grupos que pertenecen a
                                                //la organizacion 1 y lo restamos del total de grupos
            $cntGroups=$pACL->getNumGroups()-$cntGroupsMO;
        }
    }else{
        $idOrgFil=$idOrganization;
        $cntGroups= $pACL->getNumGroups($idOrganization);
    }

    if($cntGroups===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pACL->errMsg));
        $total = 0;
    }else{
        $total = $cntGroups;
    }
    
    //url
    $url['menu']=$module_name;
    $url['idOrganization']=$idOrgFil;
    
    $total = ($total == NULL)?0:$total;
    $limit  = 20;
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end = $oGrid->getEnd();
    
    $oGrid->setURL($url);
    $oGrid->setTitle(_tr('Group List'));
    $oGrid->setIcon("web/apps/$module_name/images/system_groups.png");
    $oGrid->setURL($url);
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);

    if($userLevel1=="superadmin"){
        $arrColumns[]=_tr("Organization");
    }
    $arrColumns[]=_tr("Group Name");
    $arrColumns[]=_tr("Group Description");
    $oGrid->setColumns($arrColumns);
    
    if($idOrgFil!=0)
        $Groups = $pACL->getGroupsPaging($limit, $offset,$idOrgFil);
    else
        $Groups = $pACL->getGroupsPaging($limit, $offset);

    $arrData = array();
    foreach($Groups as $group) {
        if($group[3]!=1){
            $arrTmp    = array();
            if($userLevel1=="superadmin"){
                $orgz=$pORGZ->getOrganizationById($group[3]);
                $arrTmp[] = htmlentities($orgz["name"], ENT_COMPAT, 'UTF-8'); //organization 
            }
            $arrTmp[] = "&nbsp;<a href='?menu=grouplist&action=view&id=" . $group[0] . "'>" . $group[1] . "</a>";//id,group name
            $arrTmp[] = _tr($group[2]);//description
            $arrData[] = $arrTmp;
        }
    }

     
    if($pORGZ->getNumOrganization(array()) > 0){
        global $arrPermission;
        if(in_array('create_group',$arrPermission))
            $oGrid->addNew("create_group",_tr("Create New Group"));
        
        if($userLevel1 == "superadmin"){
            $arrOrgz=array(0=>_tr("all"));
            foreach(($pORGZ->getOrganization(array())) as $value){
                if($value["id"]!=1)
                    $arrOrgz[$value["id"]]=$value["name"];
            }
            $arrFormElements = createFieldFilter($arrOrgz);
            $oFilterForm = new paloForm($smarty, $arrFormElements);
            $_POST["idOrganization"]=$idOrgFil;
            $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$idOrgFil], $_POST, array("idOrganization" => 0),true);
            $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
            $oGrid->showFilter(trim($htmlFilter));
        }
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("You need have created at least one organization before you can create a new group"));
    }

    $contenidoModulo = $oGrid->fetchGrid(array(), $arrData);
    return $contenidoModulo;
}

function viewFormGroup($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $userLevel1, $userAccount, $idOrganization)
{
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $arrFill = array();
    $action = getParameter("action");

    $arrOrgz=array(0=>_tr("Select one Organization"));
    if($userLevel1=="superadmin")
        $orgTmp=$pORGZ->getOrganization(array());
    else
        $orgTmp=$pORGZ->getOrganization(array("id"=>$idOrganization));

    if($orgTmp===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pORGZ->errMsg));
        return reportGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
    }else{
        if(count($orgTmp)==0){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("You haven't created any organization"));
            return reportGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
        }
        foreach($orgTmp as $value){
            $arrOrgz[$value["id"]]=$value["name"];
        }
    }


    $idGroup=getParameter("id");
    $arrFill=$_POST;
    if($action=="view" || getParameter("edit") || getParameter("save_edit")){
        if(!isset($idGroup)){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Invalid Group"));
            return reportGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
        }else{
            if($userLevel1=="superadmin"){
                $arrGroup = $pACL->getGroups($idGroup);
            }else {
                $arrGroup = $pACL->getGroups($idGroup,$idOrganization);
            }
        }
        if($arrGroup===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr($pACL->errMsg));
            return reportGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
        }elseif(count($arrGroup)==0){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Group doesn't exist"));
            return reportGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
        }else{
            if($arrGroup[0][3]==1){
                $smarty->assign("mb_title", _tr("ERROR"));
                $smarty->assign("mb_message",_tr("Group doesn't exist"));
                return reportGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
            }
            $arrFill["group"]=_tr($arrGroup[0][1]);
            $arrFill["description"]=_tr($arrGroup[0][2]);
            $arrFill["organization"]=$arrGroup[0][3];
            $smarty->assign("GROUP",$arrFill["group"]);
            $smarty->assign("ORGANIZATION",$arrOrgz[$arrFill["organization"]]);
        }if(getParameter("save_edit")){
            $arrFill["description"]=$_POST["description"];
        }
    }

    $arrFormGroup = createFieldForm($arrOrgz);
    $oForm = new paloForm($smarty,$arrFormGroup);

    if($action=="view"){
        $oForm->setViewMode();
    }else if($action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
    }

    global $arrPermission;
    if(in_array('create_group',$arrPermission)){
        $smarty->assign("CREATE_GROUP",true);
    }
    if(in_array('edit_group',$arrPermission)){
        $smarty->assign("EDIT_GROUP",true);
    }
    if(in_array('delete_group',$arrPermission)){
        $smarty->assign("DELETE_GROUP",true);
    }
    
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("icon","web/apps/$module_name/images/system_groups.png");
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("USERLEVEL", $userLevel1);
    $smarty->assign("id_group", $idGroup);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/grouplist.tpl",_tr("Group"), $arrFill);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    return $content;
}

function saveNewGroup($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $userLevel1, $userAccount, $idOrganization)
{

    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);

    $group=getParameter("group");
    $description=getParameter("description");
    $idOrgzSel=getParameter("organization");

    if($userLevel1!="superadmin"){
        $idOrgzSel=$idOrganization;
    }

    $arrFormGroup = createFieldForm(array());
    $oForm = new paloForm($smarty,$arrFormGroup);
    if(isset($idOrgzSel)){
        if(!$oForm->validateForm($_POST)){
            // Validation basic, not empty and VALIDATION_TYPE
            $smarty->assign("mb_title", _tr("Validation Error"));
            $arrErrores = $oForm->arrErroresValidacion;
            $strErrorMsg = "<b>"._tr("The following fields contain errors").":</b><br/>";
            if(is_array($arrErrores) && count($arrErrores) > 0){
                foreach($arrErrores as $k=>$v)
                    $strErrorMsg .= "$k, ";
            }
            $smarty->assign("mb_message", $strErrorMsg);
            return viewFormGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
        }else{
            if($idOrgzSel==0 || $idOrgzSel==1){
                $smarty->assign("mb_title", _tr("Validation Error"));
                $smarty->assign("mb_message", _tr("You must select a organization"));
                return viewFormGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
            }else{
                if($pACL->createGroup($group, $description, $idOrgzSel)){
                    $smarty->assign("mb_title", _tr("MESSSAGE"));
                    $smarty->assign("mb_message", _tr("Group was created sucessfully"));
                    return reportGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
                }else{
                    $smarty->assign("mb_title", _tr("ERROR"));
                    $smarty->assign("mb_message", _tr($pACL->errMsg));
                    return viewFormGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
                }
            }
        }
    }else{
        $smarty->assign("mb_title", _tr("Validation Error"));
        $smarty->assign("mb_message", _tr("You must select a organization"));
        return viewFormGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
    }
}

function saveEditGroup($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $userLevel1, $userAccount, $idOrganization)
{
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $arrFill = array();

    $idGroup=getParameter("id");
    $description=getParameter("description");

    if(isset($idGroup)){
        if($userLevel1=="superadmin"){
            $arrGroup = $pACL->getGroups($idGroup);
        }else{
            $arrGroup = $pACL->getGroups($idGroup,$idOrganization);
        }
        if($arrGroup===false){
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", _tr($pACL->errMsg));
        }elseif(count($arrGroup)==0){
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", _tr("Group doesn't exist"));
        }else{
            if($arrGroup[0][3]=="1"){ //no se pueden editar los grupos que pertenecen a la organizacion 1
                $smarty->assign("mb_title", _tr("Error"));
                $smarty->assign("mb_message", _tr("Group doesn't exist"));
            }else{
                if($pACL->updateGroup($idGroup, $arrGroup[0][1], $description)){
                    $smarty->assign("mb_title", _tr("MESSAGE"));
                    $smarty->assign("mb_message", _tr("Group was updated successfully"));
                }else{
                    $smarty->assign("mb_title", _tr("Error"));
                    $smarty->assign("mb_message", _tr($pACL->errMsg));
                }
            }
        }
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("Invalid Group"));
    }
    return reportGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
}

function deleteGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization)
{
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $error="";

    $idGroup=getParameter("id");
    if(isset($idGroup))
    {
        // No se puede eliminar al grupo superadmin
        if($idGroup==0)
            $error=_tr("This group  can't be deleted because is used to admin elastix.");
        elseif($pACL->getGroupNameByid($idGroup) == "administrator" ){
            $error=_tr("The administrator group cannot be deleted because is the default Elastix Group. You can delete any other group.");
        }else{
            if($userLevel1=="superadmin"){
                $arrGroup = $pACL->getGroups($idGroup);
            }else{
                $arrGroup = $pACL->getGroups($idGroup,$idOrganization);
            }
            if($arrGroup==false){
                $error=_tr("Group doesn't exist").$pACL->errMsg;
            }
        }
        
        if($error==""){
            if($pACL->deleteGroup($idGroup)){
                $smarty->assign("mb_title", _tr("MESSAGE"));
                $error=_tr("Group was deleted successfully");
            }else{
                $smarty->assign("mb_title", _tr("ERROR"));
                $error=_tr($pACL->errMsg);
            }
        }else{
            $smarty->assign("mb_title", _tr("ERROR"));
        }
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $error=_tr("Invalid Group");
    }
    $smarty->assign("mb_message", $error);
    return reportGroup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $idOrganization);
}

function createFieldForm($arrOrgz)
{
	$arrFormElements = array("description" => array("LABEL"                  => _tr("Description"),
                                                    "DESCRIPTION"            => _tr("Gp_description"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "group"       => array("LABEL"                  => _tr("Group"),
                                                    "DESCRIPTION"            => _tr("Gp_group"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "organization" => array("LABEL"                  => _tr("Organization"),
                                                    "DESCRIPTION"            => _tr("Gp_organization"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrOrgz,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
    );
	return $arrFormElements;
}

function createFieldFilter($arrOrgz)
{
    $arrFields = array(
        "idOrganization"  => array("LABEL"                  => _tr("Organization"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "SELECT",
                        "INPUT_EXTRA_PARAM"      => $arrOrgz,
                        "VALIDATION_TYPE"        => "integer",
                        "VALIDATION_EXTRA_PARAM" => "",
                        "ONCHANGE"	       => "javascript:submit();"),
        );
    return $arrFields;
}

function getAction()
{
    global $arrPermission;
    if(getParameter("create_group")){
        return (in_array('create_group',$arrPermission))?'new_group':'report';
        //return "new_group";
    }else if(getParameter("save_group")){ //Get parameter by POST (submit)
        return (in_array('create_group',$arrPermission))?'save_new':'report';
        //return "save_new";
    }else if(getParameter("apply_changes")){
        return (in_array('edit_group',$arrPermission))?'save_edit':'report';
        //return "save_edit";
    }else if(getParameter("edit")){
        return (in_array('edit_group',$arrPermission))?'view_edit':'report';
        //return "view_edit";
    }else if(getParameter("delete")){
        return (in_array('delete_group',$arrPermission))?'delete':'report';
        //return "delete";
    }else if(getParameter("action")=="view"){      //Get parameter by GET (command pattern, links)
        return "view";
    }else
        return "report"; //cancel
}

?>
