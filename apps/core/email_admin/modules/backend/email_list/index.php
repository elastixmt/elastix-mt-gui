<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4-28                                               |
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
  $Id: index.php,v 1.1 2011-07-27 05:07:46 Alberto Santos asantos@palosanto.com Exp $ 
  $Id: index.php,v 3.0 2012-08-29 Rocio Mera rmera@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoEmail.class.php";

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
    
    //user credentials
    global $arrCredentials;

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "view_list":
            $content = viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "new_emaillist":
            $content = viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_newList":
            $content = saveNewList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "mailman_settings":
            $content = viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "saveMailmanSettings":
            $content = saveMailmanSettings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "export":
            $content = exportMembers($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_memberlist":
            $content = reportMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "new_memberlist":
            $content = viewFormMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_newMember":
            $content = saveNewMember($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "remove_memberlist":
            $content = removeMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default:
            $content = reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
    }
    return $content;
}

function reportEmailList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf,$credentials)
{
    global $arrPermission;
    $pORGZ = new paloSantoOrganization($pDB);
    $pEmailList = new paloSantoEmailList($pDB);
    $org_domain=getParameter("domain");
    $name_list=getParameter("name_list");
    
    $total=0;
    if($credentials['userlevel']=="superadmin"){
        if(!empty($org_domain)){
            $total=$pEmailList->getNumEmailList($name_list,$org_domain);
        }else{
            $org_domain=0; //opcion default se muestran todas las listas
            $total=$pEmailList->getNumEmailList($name_list);
        }
    }else{
        $org_domain=$credentials['domain'];
        $total=$pEmailList->getNumEmailList($name_list,$org_domain);
    }
    
    if($total===false){
        $total=0;
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",_tr("Couldn't be retrieved Email List data"));
    }

    //url
    $url['menu']=$module_name;
    $url['domain']=$org_domain;
    $url['name_list']=$name_list;
    
    $limit  = 20;
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTitle(_tr("Email List"));
    $oGrid->setIcon("web/apps/$module_name/images/email.png");
    $oGrid->setURL($url);
    $oGrid->setWidth("99%");
    
    $del_permission=in_array('delete_list',$arrPermission);
    $edit_permission=in_array('edit_list',$arrPermission);
    $create_permission=in_array('create_list',$arrPermission);
    
    if($del_permission)
        $arrColumns[]="";//checkbox to delete
    $arrColumns[]=_tr('List Name');
    $arrColumns[]=_tr('Number of Members');
    $arrColumns[]=_tr('Actions');
    $oGrid->setColumns($arrColumns);
    
    $arrData = null;
    if($total>0){
        $arrResult = $pEmailList->getEmailListPagging($name_list,$org_domain,$limit,$offset);
        if($arrResult===false){
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message",_tr("Couldn't be retrieved Email List data"));
        }else{
            foreach($arrResult as $list){
                $arrTmp=array();
                if($del_permission)
                    $arrTmp[] = "<input type='checkbox' name='del_list' id='{$list['id']}'>";
                $arrTmp[] = "<a href='?menu=$module_name&action=view_list&id={$list['id']}'>".htmlentities($list['listname'],ENT_QUOTES, "UTF-8")."@".$list['organization_domain']."</a>";
                $arrTmp[] = $pEmailList->getTotalMembers($list['id']);
                $arrTmp[] = "<a href='?menu=$module_name&action=view_memberlist&id=".$list['id']."'>"._tr("View members")."</a>";
                $arrData[] = $arrTmp;
            }
        }
    }
    
    //Verifico si en el archivo /etc/postfix/main.cf las variables alias_map y virtual_alias_map están apuntando a los archivos correctos, de no ser así se lo corrige
    $checkPostfixFile = $pEmailList->checkPostfixFile();
    if(!$checkPostfixFile){
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",_tr("An error has ocurred to try config postfix file"));
    }
    
    //begin section filter
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input name='search_org' type='submit' class='button' value='"._tr('Search')."'>");
    
    //se comprueba que el mailman haya sido configurado por primera vez
    $MailmanListCreated = $pEmailList->isMailmanListCreated();
    if(is_null($MailmanListCreated)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", $pEmailList->getError());
    }elseif(!$MailmanListCreated){ //sino ha sido configurado se muestra un mensaje
        if($credentials['userlevel']!='superadmin'){ //solo el superadmin puede hacer esta accion
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("A new List can not be added because some configurations are missed. Please contact with Elastix Admisnistrator"));
        }else{
            $smarty->assign("mb_title", _tr("Message"));
            $smarty->assign("mb_message", _tr("In order to use this module configure the Mailman Admin Settings. Click here >> ")."<a href='?menu=$module_name&action=mailman_settings'>"._tr('Mailman Settings')."</a>");
        }
    }else{
        if($pORGZ->getNumOrganization(array()) > 0){
            if($create_permission){
                $oGrid->addNew("new_emaillist",_tr("New Email list"));
            }
            /*if($del_permission)
                $oGrid->deleteList(_tr("Are you sure you wish to delete the Email List(s)."),"delete",_tr("Delete"));*/
            $arrOrgz=array(0=>"all");
            if($credentials['userlevel']=="superadmin"){
                foreach(($pORGZ->getOrganization(array())) as $value){
                    $arrOrgz[$value["domain"]]=$value["domain"];
                }
                $_POST["domain"]=$org_domain;
                $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$org_domain], $_POST, array("domain" => 0),true); //organization
            }
            $oGrid->addFilterControl(_tr("Filter applied ")._tr("Name List")." = ".$name_list, $_POST, array("name_list" => "")); //name_list
            $arrFormElements = createFieldFilter($arrOrgz);
            $oFilterForm = new paloForm($smarty, $arrFormElements);
            $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
            $oGrid->showFilter(trim($htmlFilter));
        }else{
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message",_tr("In order to use this module must exist at least 1 organization in the Elastix Server"));
        }
    }
    
    $content = $oGrid->fetchGrid(array(), $arrData);
    return $content;
}

function viewFormEmaillist($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    
    $pORGZ = new paloSantoOrganization($pDB);
    $pEmailList = new paloSantoEmailList($pDB);
    
    //no se puede editar una lista una vez que ha sido creada
    //la unica accion que existe es observar la configuracion de la lista
    $idList=getParameter("id");
    $action = getParameter("action");
    $arrDominios=array();
    if($action=='view_list'){
        //comprabamos que la lista exista
        if(empty($idList)){
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message",_tr("Invalid Email List"));
            return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$credentials);
        }else{
            if($credentials['userlevel']=='superadmin')
                $emailList=$pEmailList->getEmailList($idList);
            else{
                $emailList=$pEmailList->getEmailList($idList,$credentials['domain']);
            }
            if($emailList==false){
                $smarty->assign("mb_title", _tr("Error"));
                $error=($emailList===false)?_tr("Couldn't be retrieved Email List data"):_tr("Email List does not exist");
                $smarty->assign("mb_message",$error);
                return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$credentials);
            }else{
                $smarty->assign("DOMAIN",$emailList['organization_domain']);
                $smarty->assign("LIST_NAME",htmlentities($emailList['listname'],ENT_QUOTES, "UTF-8"));
                $smarty->assign("LIST_ADMIN_USER",$emailList['mailadmin']);
            }
        }
    }else{
        //queremos crear una nueva lista
        if($credentials['userlevel']=='superadmin'){
            foreach(($pORGZ->getOrganization(array())) as $value){
                $arrDominios[$value["domain"]]=$value["domain"];
            }
        }
        
        //se comprueba que el mailman haya sido configurado por primera vez
        $MailmanListCreated = $pEmailList->isMailmanListCreated();
        if(is_null($MailmanListCreated)){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", $pEmailList->getError());
            return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$credentials);
        }elseif(!$MailmanListCreated){
            $smarty->assign("StatusNew", 1);
            $smarty->assign("Mailman_Setting", _tr("Mailman Admin Settings"));
            if($credentials['userlevel']!='superadmin'){ //solo el superadmin puede hacer esta accion
                $smarty->assign("mb_title", _tr("ERROR"));
                $smarty->assign("mb_message", _tr("A new List can be added because some configurations are missed. Please contact with Elastix Admisnistrator"));
                return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$credentials);
            }
        }
    }
    
    $arrFormEmaillist = createFieldForm($arrDominios);
    $oForm = new paloForm($smarty,$arrFormEmaillist);
    
    if($action=='view_list'){
        $oForm->setViewMode();
    }
    
    if(in_array('delete_list',$arrPermission))
        $smarty->assign("DELETE_LIST",true);
        
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("List_Setting", _tr("New List Settings"));
    $smarty->assign("icon", "web/apps/$module_name/images/email.png");
    $smarty->assign("idList",$idList);
    
    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", _tr("New Email List"), $_POST);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    return $content;
}

function saveMailmanSettings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $pEmailList = new paloSantoEmailList($pDB);
    if($credentials['userlevel']!='superadmin'){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("You are not authorized to perform this action"));
        return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$credentials);
    }
    
    $emailmailman=getParameter("emailmailman");
    $passwdmailman=getParameter("passwdmailman");
    $repasswdmailman=getParameter("repasswdmailman");
    
    $arrForm = createFieldForm(array());
    $oForm = new paloForm($smarty,$arrForm);
    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr("The following fields contain errors").":</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "{$k} [{$v['mensaje']}], ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
        return viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        if($passwdmailman!=$repasswdmailman){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("Field Password and Retype Password do not match"));
            return viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
        
        if(!$pEmailList->createListMailman($emailmailman,$passwdmailman)){
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", _tr("Could not be configured Mailman Admin Settings.")." ".$pEmailList->getError());
            return viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }else{
            $smarty->assign("mb_title", _tr("Message"));
            $smarty->assign("mb_message", _tr("Settings were save successfully"));
            return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$credentials);
        }
    }
}

function saveNewList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf,$credentials)
{
    $pEmailList = new paloSantoEmailList($pDB);
    
    if($credentials['userlevel']=='superadmin'){
        $domain=getParameter('domain');
    }else{
        $domain=$credentials['domain'];
    }
    
    $namelist = getParameter("namelist");
    $namelist = strtolower($namelist);
    $password = getParameter("password");
    $passwordconfirm = getParameter("passwordconfirm");
    $emailadmin = getParameter("emailadmin");
    
    $arrForm = createFieldForm(array());
    $oForm = new paloForm($smarty,$arrForm);
    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr("The following fields contain errors").":</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "{$k} [{$v['mensaje']}], ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
        return viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    //validaciones
    if($password != $passwordconfirm){
        $smarty->assign("mb_title", _tr("Validation Error"));
        $smarty->assign("mb_message", _tr("The Password List and Confirm Password List do not match"));
        return viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $pDB->beginTransaction();
    if(!$pEmailList->createEmailList($domain,$namelist,$password,$emailadmin)){
        $pDB->rollBack();
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message", _tr("List could not be created.")." ".$pEmailList->getError());
        return viewFormEmaillist($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $pDB->commit();
        $smarty->assign("mb_title", _tr("Message"));
        $smarty->assign("mb_message", _tr("The List was created successfully "));
        return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
}

function deleteEmailList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    $pEmailList = new paloSantoEmaillist($pDB);
    $idList=getParameter('id');
    if(empty($idList)){
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",_tr("Invalid Email List"));
        return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$credentials);
    }
    if($credentials['userlevel']=='superadmin')
        $emailList=$pEmailList->getEmailList($idList);
    else{
        $emailList=$pEmailList->getEmailList($idList,$credentials['domain']);
    }
    
    if($emailList==false){
        $smarty->assign("mb_title", _tr("Error"));
        $error=($emailList===false)?_tr("Couldn't be retrieved Email List data"):_tr("Email List does not exist");
        $smarty->assign("mb_message",$error);
        return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $pDB->beginTransaction();
    
    if(!$pEmailList->deleteEmailList($idList)){
        $pDB->rollBack();
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message", $pEmailList->getError());
    }else{
        $pDB->commit();
        $smarty->assign("mb_title", _tr("Message"));
        $smarty->assign("mb_message", _tr("The email list(s) were successfully deleted"));
    }
    return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function reportMemberList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $pEmailList = new paloSantoEmailList($pDB);
    $id_list = getParameter("id");

    if($credentials['userlevel']=='superadmin')
        $emailList=$pEmailList->getEmailList($id_list);
    else{
        $emailList=$pEmailList->getEmailList($id_list,$credentials['domain']);
    }
    
    if($emailList==false){
        $smarty->assign("mb_title", _tr("Error"));
        $error=($emailList===false)?_tr("Couldn't be retrieved Email List data"):_tr("Email List does not exist");
        $smarty->assign("mb_message",$error);
        return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $field_type = getParameter("filter_type");
    $field_pattern = getParameter("filter_txt");

    $smarty->assign("IDEMAILLIST",$id_list);
    $smarty->assign("ACTION",'view_memberlist');
    $smarty->assign("SHOW",_tr("Show"));
    $smarty->assign("RETURN",_tr("Return"));
    $smarty->assign("LINK","?menu=$module_name&action=export&id=$id_list&rawmode=yes");
    $smarty->assign("EXPORT",_tr("Export Members"));

    $edit_permission=in_array('edit_list',$arrPermission);
    
    $totalMembers = $pEmailList->getTotalMembers($id_list);

    $oGrid  = new paloSantoGrid($smarty);
    $limit  = 20;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($totalMembers);
    $oGrid->setTitle(_tr("List Members of")." ".$pEmailList->getListName($id_list));
    $oGrid->setIcon("web/apps/$module_name/images/email.png");
    $oGrid->pagingShow(true);
    $offset = $oGrid->calculateOffset();
    $url['menu']=$module_name;
    $url['action']='view_memberlist';
    $url['id']=$id_list;
    $url['filter_type']=$field_type;
    $url['filter_txt']=$field_pattern;
    $oGrid->setURL($url);
    if($edit_permission)
        $arrColumns[] = '';
    $arrColumns[] = _tr("Member name");
    $arrColumns[] = _tr("Member email");
    $oGrid->setColumns($arrColumns);

    $arrResult = $pEmailList->getMembers($limit,$offset,$id_list,$field_type,$field_pattern);
    $arrData = null;

    //print_r($arrResult);
    if(is_array($arrResult) && $totalMembers>0){
        foreach($arrResult as $list){
            $arrTmp=array();
            if($edit_permission)
                $arrTmp[] = "<input type='checkbox' name='del_emailmembers[{$list["mailmember"]}]'>";
            $arrTmp[] = $list["namemember"];
            $arrTmp[] = $list["mailmember"];
            $arrData[] = $arrTmp;
        }
    }

    $arrFormFilterMembers = createFieldFilterViewMembers();
    $oFilterForm = new paloForm($smarty, $arrFormFilterMembers);

    $arrType = array("name" => _tr("Name"), "email" => _tr("Email"));

    if(!is_null($field_type)){
        $nameField = $arrType[$field_type];
    }else{
        $nameField = "";
    }

    $oGrid->customAction("return", _tr("Return"));
    if($edit_permission){
        $oGrid->addNew("new_memberlist",_tr("Add Member(s) to List"));
        $oGrid->deleteList(_tr("Are you sure you wish to delete the Email List(s)."),"remove_memberlist",_tr("Delete"));
    }
    
    $oGrid->customAction("?menu=$module_name&action=export&id=$id_list&rawmode=yes",_tr("Export Members"),null,true);
    $oGrid->addFilterControl(_tr("Filter applied: ").$nameField." = ".$field_pattern, $_POST, array("filter_type" => "name","filter_txt" => ""));
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/view_members.tpl","",$_POST);
    $oGrid->showFilter(trim($htmlFilter));
    $content = $oGrid->fetchGrid(array(),$arrData);
    return $content;
}


function viewFormMemberList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    $pEmailList = new paloSantoEmailList($pDB);
    $id_emailList = getParameter("id");
    if($credentials['userlevel']=='superadmin')
        $emailList=$pEmailList->getEmailList($id_emailList);
    else{
        $emailList=$pEmailList->getEmailList($id_emailList,$credentials['domain']);
    }
    
    if($emailList==false){
        $smarty->assign("mb_title", _tr("Error"));
        $error=($emailList===false)?_tr("Couldn't be retrieved Email List data"):_tr("Email List does not exist");
        $smarty->assign("mb_message",$error);
        return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $title = _tr("New List Member");
    $smarty->assign("MEMBER","save_newMember");
    $info = _tr("You must enter each email line by line, like the following").":<br /><br /><b>"._tr("userEmail1@domain1.com")."<br />"._tr("userEmail2@domain2.com")."<br />"._tr("userEmail3@domain3.com")."</b><br /><br />"._tr("You can also enter a name for each email, like the following").":<br /><br /><b>"._tr("Name1 Lastname1 <userEmail1@domain1.com>")."<br />"._tr("Name2 Lastname2 <userEmail2@domain2.com>")."<br />"._tr("Name3 Lastname3 <userEmail3@domain3.com>")."</b>";
    
    $smarty->assign("SAVE", _tr("Add"));
    $smarty->assign("IDEMAILLIST",$id_emailList);
    $smarty->assign("ACTION",'view_memberlist');
    
    $arrFormMemberlist = createFieldFormMember();
    $oForm = new paloForm($smarty,$arrFormMemberlist);

    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("INFO", $info);
    $smarty->assign("icon", "web/apps/$module_name/images/email.png");
    $htmlForm = $oForm->fetchForm("$local_templates_dir/form_member.tpl", $title, $_POST);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    return $content;
}

function saveNewMember($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    $pEmailList = new paloSantoEmaillist($pDB);
    $emailMembers = getParameter("emailmembers");
    $id_list	  = getParameter("id_emaillist");
    if($credentials['userlevel']=='superadmin')
        $emailList=$pEmailList->getEmailList($id_list);
    else{
        $emailList=$pEmailList->getEmailList($id_list,$credentials['domain']);
    }
    
    if($emailList==false){
        $smarty->assign("mb_title", _tr("Error"));
        $error=($emailList===false)?_tr("Couldn't be retrieved Email List data"):_tr("Email List does not exist");
        $smarty->assign("mb_message",$error);
        return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $emailMembers = explode("\n",$emailMembers);
    $arrMembers = array();
    $arrErrorMembers = array();
    $i = 0;
    foreach($emailMembers as $key => $value){
        $member = trim($value);
        if(preg_match("/^[[:alnum:]]+([\._\-]?[[:alnum:]]+)*@[[:alnum:]]+([\._\-]?[[:alnum:]]+)*(\.[[:alnum:]]{2,4})+$/",$member)){
            if(!$pEmailList->isAMemberOfList($member,$id_list)){
                $arrMembers[$i]["member"] = $member;
                $arrMembers[$i]["email_member"] = $member;
                $i++;
            }else
                $arrErrorMembers[] = _tr("Already a member").": ".htmlentities($member);
        }elseif(preg_match("/^([[:alnum:]]+([[:space:]]*[[:alnum:]]+){0,3})[[:space:]]*\<([[:alnum:]]+([\._\-]?[[:alnum:]]+)*@[[:alnum:]]+([\._\-]?[[:alnum:]]+)*(\.[[:alnum:]]{2,4})+)\>$/",$member,$matches)){
            if(!$pEmailList->isAMemberOfList($matches[3],$id_list)){
                $arrMembers[$i]["member"] = preg_replace("/[[:space:]]+/"," ",$member);
                $arrMembers[$i]["namemember"] = $matches[1];
                $arrMembers[$i]["email_member"] = $matches[3];
                $i++;
            }else
                $arrErrorMembers[] = _tr("Already a member").": ".htmlentities($member);
        }elseif($member!="")
            $arrErrorMembers[] = _tr("Invalid member").": ".htmlentities($member);
    }
    
    
    if(count($arrMembers) > 0){
        $pDB->beginTransaction();
        if($pEmailList->saveMembersList($id_list,$arrMembers)){
            $pDB->commit();
            $message = "<b>"._tr("The following members were added to the list").":</b><br />";
            foreach($arrMembers as $member)
                $message .= htmlentities($member["member"])."<br />";
            if(count($arrErrorMembers)>0){
                $message .= "<b>"._tr("The following members could not be added to the list").":</b><br />";
                foreach($arrErrorMembers as $noMember)
                    $message .= $noMember."<br />";
            }
            $smarty->assign("mb_title", _tr("Message"));
            $smarty->assign("mb_message", $message);
            return reportMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }else{
            $pDB->rollBack();
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", _tr("Members could not be added to the list").". ".$pEmailList->getError());
            if(count($arrErrorMembers)>0){
                $message = "<b>"._tr("The following members contains errors").":</b><br />";
                foreach($arrErrorMembers as $noMember)
                    $message .= $noMember."<br />";
            }
            return viewFormMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{
        if(count($arrErrorMembers)>0){
            $smarty->assign("mb_title", _tr("Error"));
            $message=_tr('Members could not be added to the list. The following members contains errors').":</b><br />";
            foreach($arrErrorMembers as $noMember)
                $message .= $noMember."<br />";
            $smarty->assign("mb_message", $message);
            return viewFormMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }
    return reportMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function removeMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $pEmailList = new paloSantoEmaillist($pDB);
    
    $id_list = getParameter("id");
    if($credentials['userlevel']=='superadmin')
        $emailList=$pEmailList->getEmailList($id_list);
    else{
        $emailList=$pEmailList->getEmailList($id_list,$credentials['domain']);
    }
    
    if($emailList==false){
        $smarty->assign("mb_title", _tr("Error"));
        $error=($emailList===false)?_tr("Couldn't be retrieved Email List data"):_tr("Email List does not exist");
        $smarty->assign("mb_message",$error);
        return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $tempMembers = getParameter("del_emailmembers");
    if(!is_array($tempMembers)){
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",_tr("You must select at least one member"));
        return reportMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $emailMembers=array_keys($tempMembers);
    if(count($emailMembers)==0){
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",_tr("You must select at least one member"));
        return reportMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $arrMembers = array();
    $arrErrorMembers = array();
    foreach($emailMembers as $value){
        $member = trim($value);
        if(preg_match("/^[[:alnum:]]+([\._\-]?[[:alnum:]]+)*@[[:alnum:]]+([\._\-]?[[:alnum:]]+)*(\.[[:alnum:]]{2,4})+$/",$member)){
            if($pEmailList->isAMemberOfList($member,$id_list)){
                $arrMembers[]["member"] = $member;
            }else
                $arrErrorMembers[] = _tr("It is not a member").": ".htmlentities($member);
        }elseif($member!="")
            $arrErrorMembers[] = _tr("Invalid member").": ".htmlentities($member);
    }
    
    if(count($arrMembers) > 0){
        $pDB->beginTransaction();
        if($pEmailList->removeMembersList($id_list,$arrMembers)){
            $pDB->commit();
            $message = "<b>"._tr("The following members were deleted of the list").":</b><br />";
            foreach($arrMembers as $member)
                $message .= htmlentities($member["member"])."<br />";
            $smarty->assign("mb_title", _tr("Message"));
            $smarty->assign("mb_message", $message);
        }else{
            $pDB->rollBack();
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", _tr("Members could not be deleted to the list").". ".$pEmailList->getError());
        }
    }else{
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_title", _tr("Select at least one valid member"));
    }
    return reportMemberList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function exportMembers($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials)
{
    $pEmailList = new paloSantoEmailList($pDB);
    $id_list = getParameter("id");
    if($credentials['userlevel']=='superadmin')
        $emailList=$pEmailList->getEmailList($id_list);
    else{
        $emailList=$pEmailList->getEmailList($id_list,$credentials['domain']);
    }
    
    if($emailList==false){
        $smarty->assign("mb_title", _tr("Error"));
        $error=($emailList===false)?_tr("Couldn't be retrieved Email List data"):_tr("Email List does not exist");
        $smarty->assign("mb_message",$error);
        return reportEmailList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $listName = $emailList['listname'];
    $text = "";
    if(!is_null($listName)){
        $totalMembers = $pEmailList->getTotalMembers($id_list);
        $members      = $pEmailList->getMembers($totalMembers,0,$id_list,null,"");
        foreach($members as $key => $value){
            if($text != "")
                $text .= "\n";
            if(isset($value["namemember"]) && $value["namemember"] != "")
                $text .= $value["namemember"]." <$value[mailmember]>";
            else
                $text .= $value["mailmember"];
        }
    }
    else
        $listName = "";

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: txt file");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment; filename=$listName"."_members.txt");
    header("Content-Transfer-Encoding: binary");
    header("Content-length: ".strlen($text));
    echo $text;
}

function createFieldFilter($arrDominios){
    $arrFormElements = array(
            "domain"   => array(    "LABEL"          	     => _tr("Domain"),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => $arrDominios,
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
            "name_list"  => array(  "LABEL"       => _tr("List Name"),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
            );
    return $arrFormElements;
}

function createFieldForm($arrDominios)
{
    $arrFields = array(
            "emailmailman"   => array(      "LABEL"                  => _tr("Email mailmam admin"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"100"),
                                            "VALIDATION_TYPE"        => "email",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "passwdmailman"   => array(     "LABEL"                  => _tr("Password"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "PASSWORD",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "repasswdmailman"   => array(     "LABEL"                  => _tr("Retype Password"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "PASSWORD",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "domain"  	       => array(    "LABEL"                  => _tr("Domain name"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrDominios,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),

            "namelist" 	       => array(    "LABEL"                  => _tr("List name"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "password"         => array(    "LABEL"                  => _tr("Password"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "PASSWORD",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "passwordconfirm"   => array(   "LABEL"                  => _tr("Confirm password list"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "PASSWORD",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "emailadmin"   	=> array(   "LABEL"                  => _tr("Email admin list"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"200"),
                                            "VALIDATION_TYPE"        => "email",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            );
    return $arrFields;
}

function createFieldFormMember()
{
    $arrFields = array(
        "emailmembers"   => array(       "LABEL"                  => _tr("Members emails"),
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXTAREA",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:400px"),
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => "",
					"ROWS"			 => "9"
                                        ),
        );
    return $arrFields;
}

function createFieldFilterViewMembers()
{
    $arrType = array("name" => _tr("Name"), "email" => _tr("Email"));

    $arrFormElements = array(
            "filter_type"  => array(   "LABEL"                  => _tr("Search"),
                                       "REQUIRED"               => "no",
                                       "INPUT_TYPE"             => "SELECT",
                                       "INPUT_EXTRA_PARAM"      => $arrType,
                                       "VALIDATION_TYPE"        => "text",
                                       "VALIDATION_EXTRA_PARAM" => ""),
            "filter_txt"   => array(   "LABEL"                  => "",
                                       "REQUIRED"               => "no",
                                       "INPUT_TYPE"             => "TEXT",
                                       "INPUT_EXTRA_PARAM"      => "",
                                       "VALIDATION_TYPE"        => "text",
                                       "VALIDATION_EXTRA_PARAM" => ""),
                    );
    return $arrFormElements;
}

function getAction()
{
    global $arrPermission;
    if(getParameter("new_emaillist")) //Get parameter by POST (submit)
        return (in_array('create_list',$arrPermission))?'new_emaillist':'report';
    elseif(getParameter("save_newList"))
        return (in_array('create_list',$arrPermission))?'save_newList':'report';
    elseif(getParameter("delete"))
        return (in_array('delete_list',$arrPermission))?'delete':'report';
    elseif(getParameter("action") == "view_list")
        return "view_list";
    elseif(getParameter("save_mailmail_admin"))
        return (in_array('edit_list',$arrPermission))?'saveMailmanSettings':'report';
    elseif(getParameter("action") == "mailman_settings")
        return (in_array('edit_list',$arrPermission))?'mailman_settings':'report';
    elseif(getParameter("action") == "export")
        return "export";
    elseif(getParameter("return"))
        return "report";
    elseif(getParameter("new_memberlist"))
        return (in_array('edit_list',$arrPermission))?'new_memberlist':'report';
    elseif(getParameter("save_newMember"))
        return (in_array('edit_list',$arrPermission))?'save_newMember':'report';
    elseif(getParameter("remove_memberlist"))
        return (in_array('edit_list',$arrPermission))?'remove_memberlist':'report';
    elseif(getParameter("action") == "view_memberlist" || getParameter("show"))
        return "view_memberlist";
    else
        return "report"; //cancel
}
?>
