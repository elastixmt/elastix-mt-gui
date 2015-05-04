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
$Id: index.php,v 1.1.1.1 2007/07/06 21:31:56 gcarrillo Exp $ */
include_once "libs/paloSantoJSON.class.php";

function _moduleContent(&$smarty, $module_name){
    include_once("libs/paloSantoGrid.class.php");
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/paloSantoOrganization.class.php";


    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);

    global $arrCredentials;
       
    $action = getAction();
    $content = "";

    switch($action){
        case "new_user":
            $content = viewFormUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "edit":
            $content = viewFormUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveNewUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "getGroups":
            $content = getGroups($pDB,$arrCredentials);
            break;
        case "getImage":
            $content = getImage($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "reloadAasterisk":
            $content = reloadAasterisk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "reconstruct_mailbox":
            $content = reconstruct_mailbox($pDB, $arrConf, $arrCredentials);
            break;
        /*case "changes_email_quota":
            $content = changes_email_quota($smarty, $module_name, $pDB, $arrConf, $arrCredentials);
            break;*/
        default: // report
            $content = reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function reportUser($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    global $arrPermission;
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $idOrgFil=getParameter("idOrganization");
    $username=getParameter("username");
    
    $total=0;
    if($credentials['userlevel']=="superadmin"){
        if(!empty($idOrgFil)){
            $total=$pACL->getNumUsers($idOrgFil,$username);
        }else{
            $idOrgFil=0; //opcion default se muestran todos los usuarios
            $total=$pACL->getNumUsers(null,$username);
        }
    }else{
        $idOrgFil=$credentials['id_organization'];
        $total=$pACL->getNumUsers($idOrgFil,$username);
    }
    
    if($total===false){
        $total=0;
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",_tr("Couldn't be retrieved user data"));
    }

    //url
    $url['menu']=$module_name;
    $url['idOrganization']=$idOrgFil;
    $url['username']=$module_name;
    
    $limit=20;
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $oGrid->setTitle(_tr('User List'));
    $oGrid->setIcon("../web/_common/images/user.png");
    $oGrid->setURL($url);
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    
    $arrColumns=array();
    if($credentials["userlevel"]=="superadmin"){
        $arrColumns[]=_tr("Organization"); //delete
    }
    $arrColumns[]=_tr("Username");
    $arrColumns[]=_tr("Name");
    $arrColumns[]=_tr("Group");
    $arrColumns[]=_tr("Extension")." / "._tr("Fax Extension");
    $arrColumns[]=_tr("Used Space")." / "._tr("Email Quota");
    if(in_array('reconstruct_mailbox',$arrPermission))
        $arrColumns[]=""; //reconstruct mailbox
    $oGrid->setColumns($arrColumns);

    $arrData=array();
    if($credentials['userlevel']=="superadmin"){
        if($idOrgFil!=0)
            $arrUsers = $pACL->getUsersPaging($limit, $offset, $idOrgFil, $username);
        else
            $arrUsers = $pACL->getUsersPaging($limit, $offset, null, $username);
    }else{
        $arrUsers = $pACL->getUsersPaging($limit, $offset, $idOrgFil, $username);
    }

    if($arrUsers===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pACL->errMsg));
    }
    
    //si es un usuario solo se ve a si mismo
    //si es un administrador ve a todo los usuarios de
    foreach($arrUsers as $user) {
        $arrTmp=array();
        if($credentials["userlevel"]=="superadmin"){
            $arrOgz=$pORGZ->getOrganizationById($user[4]);
            $arrTmp[] = htmlentities($arrOgz["name"], ENT_COMPAT, 'UTF-8'); //organization 
        }
        $arrTmp[] = "&nbsp;<a href='?menu=userlist&action=view&id=$user[0]'>".$user[1]."</a>"; //username   
        $arrTmp[] = htmlentities($user[2], ENT_COMPAT, 'UTF-8'); //name
        $gpTmp = $pACL->getGroupNameByid($user[7]);
        $arrTmp[]=$gpTmp==("superadmin")?_tr("NONE"):$gpTmp;
        if(!isset($user[5]) || $user[5]==""){
            $ext=_tr("Not assigned");
        }else{
            $ext=$user[5];
        }
        if(!isset($user[6]) || $user[6]==""){
            $faxExt=_tr("Not assigned");
        }else{
            $faxExt=$user[6];
        }
        $arrTmp[] = $ext." / ".$faxExt;
        if($user[4]!=1){ //user that belong organization 1 do not have email account
            $arrTmp[] = obtener_quota_usuario($user[1],$module_name); //email quota
            if(in_array('reconstruct_mailbox',$arrPermission)){
                $arrTmp[] = "&nbsp;<a href='#' onclick=mailbox_reconstruct('{$user[1]}')>"._tr('Reconstruct Mailbox')."</a>";//reconstruct mailbox
            }
        }else{
            $arrTmp[] = '';
            $arrTmp[] = '';
        }
        $arrData[] = $arrTmp;
        $end++;
    }

    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input name='search_org' type='submit' class='button' value='"._tr('Search')."'>");
    if($pORGZ->getNumOrganization(array()) > 0){
        $arrOrgz=array(0=>_tr("all"));
        if(in_array('create_user',$arrPermission))
                $oGrid->addNew("create_user",_tr("Create New User"));
        if($credentials['userlevel']=="superadmin"){
            foreach(($pORGZ->getOrganization(array())) as $value){
                $arrOrgz[$value["id"]]=$value["name"];
            }
            $_POST["idOrganization"]=$idOrgFil;
            $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$idOrgFil], $_POST, array("idOrganization" => 0),true); //organization
        }
        $arrFormElements = createFieldFilter($arrOrgz);
        $oFilterForm = new paloForm($smarty, $arrFormElements);
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Username")." = ".$username, $_POST, array("username" => "")); //username
        $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
        $oGrid->showFilter(trim($htmlFilter));
    }else{
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("In order to use this module must exist at least 1 organization in the Elastix Server"));
    }

    $contenidoModulo = $oGrid->fetchGrid(array(), $arrData);
    $mensaje=showMessageReload($module_name, $pDB, $credentials);
    $contenidoModulo = $mensaje.$contenidoModulo;
    return $contenidoModulo;
}

function obtener_quota_usuario($username,$module_name)
{
    include_once "configs/email.conf.php";
    include_once "libs/cyradm.php";
    global $CYRUS;
    global $arrPermission;
    $cyr_conn = new cyradm;
    $cyr_conn->imap_login();
    $edit_quota = _tr("Edit quota");
    $quota = $cyr_conn->getquota("user/" . $username);
    $tamano_usado=_tr("Could not query used disc space");
    if(is_array($quota) && count($quota)>0){
        if ($quota['used'] != "NOT-SET"){
            $q_used  = $quota['used'];
            $q_total = $quota['qmax'];
            if (! $q_total == 0){
                $q_percent = number_format((100*$q_used/$q_total),2);
                $q_usada=($q_used<1024)?"$q_used KB":($q_used/1024)." MB ";
                $q_total=($q_total<1024)?"$q_total KB":($q_total/1024)." MB ";
                /*if(in_array('edit_user',$arrPermission)){
                    $tamano_usado=" $q_usada / <a href='#' onclick=changes_email_quota('$username') title='$edit_quota'> $q_total </a> ($q_percent%)";
                }else*/
                    $tamano_usado=" $q_usada / $q_total ($q_percent%)";
            }
            else {
                $tamano_usado=_tr("Could not obtain used disc space");
            }
        } else {
            $tamano_usado=_tr("Size is not set");
        }
    }
    return $tamano_usado;
}

function reconstruct_mailbox(&$pDB, $arrConf, $credentials)
{
    require_once("libs/paloSantoEmail.class.php");
 
    $pACL = new paloACL(new paloDB($arrConf['elastix_dsn']['acl']));
    $pEmail = new paloEmail($pDB);
    $jsonObject = new PaloSantoJSON();
    $username=getParameter('username');
    
    if(empty($username)){
        $jsonObject->set_error('Invalid Username');
        return $jsonObject->createJSON();
    }
    
    $user=$pACL->getUserByUsername($username);
    if($user==false){
        $jsonObject->set_error($pACL->errMsg);
        return $jsonObject->createJSON();
    }
    
    if($credentials["userlevel"]=="administrator"){
        $user['id_organization']!=$credentials['id_organization'];
        $jsonObject->set_error(_tr('Invalid User'));
        return $jsonObject->createJSON();
    }
    
    if($pEmail->resconstruirMailBox($username)){
        $jsonObject->set_message(_tr("The MailBox was reconstructed succefully"));
        return $jsonObject->createJSON();
    }else{
        $jsonObject->set_error(_tr("The MailBox couldn't be reconstructed.\n".$pEmail->errMsg));
        return $jsonObject->createJSON();
    }
    
    return $jsonObject->createJSON();
}

function showMessageReload($module_name, &$pDB, $credentials){
    $pAstConf=new paloSantoASteriskConfig($pDB);
    $params=array();
    $msgs="";

    $query = "SELECT domain, id from organization";
    //si es superadmin aparece un link por cada organizacion que necesite reescribir su plan de marcado
    if($credentials["userlevel"]!="superadmin"){
        $query .= " where id=?";
        $params[]=$credentials["id_organization"];
    }

    $mensaje=_tr("Click here to reload dialplan");
    $result=$pDB->fetchTable($query,false,$params);
    if(is_array($result)){
        foreach($result as $value){
            if($value[1]!=1){
                $showmessage=$pAstConf->getReloadDialplan($value[0]);
                if($showmessage=="yes"){
                    $append=($credentials["userlevel"]=="superadmin")?" $value[0]":"";
                    $msgs .= "<div id='msg_status_$value[1]' class='mensajeStatus'><a href='?menu=$module_name&action=reloadAsterisk&organization_id=$value[1]'/><b>".$mensaje.$append."</b></a></div>";
                }
            }
        }
    }
    return $msgs;
}

function viewFormUser($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $arrFill=array();
    $action = getParameter("action");

    $arrOrgz=array(0=>"Select one Organization");
    if($credentials["userlevel"]=="superadmin"){
        $orgTmp=$pORGZ->getOrganization(array());
    }else{
        $orgTmp=$pORGZ->getOrganization(array("id"=>$credentials["id_organization"]));
    }

    if($orgTmp===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pORGZ->errMsg));
        return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }elseif(count($orgTmp)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("You need yo have at least one organization created before you can create a user"));
        return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        if(($action=="new_user" || $action=="save_new")&& count($orgTmp)<=1){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("It's necesary you create a new organization so you can create new user"));
            return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
        foreach($orgTmp as $value){
            $arrOrgz[$value["id"]]=$value["name"];
            $arrDomains[$value["id"]]=$value["domain"];
        }
        $smarty->assign("ORGANIZATION",htmlentities($orgTmp[0]["name"], ENT_COMPAT, 'UTF-8'));
    }


    $idUser=getParameter("id");

    $arrFill=$_POST;

    if($action=="view" || getParameter("edit") || getParameter("save_edit")){
        if(!isset($idUser)){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Invalid User"));
            return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }else{
            if($credentials["userlevel"]=="superadmin"){
                $arrUsers = $pACL->getUsers($idUser);
            }else{
                $arrUsers = $pACL->getUsers($idUser, $credentials["id_organization"], null, null);
            }
        }
        
        if($arrUsers===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr($pACL->errMsg));
            return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }else if(count($arrUsers)==0){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("User doesn't exist"));
            return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }else{
            $picture = $pACL->getUserPicture($idUser);
            if($picture!==false){
                $smarty->assign("ShowImg",1);
            }
            foreach($arrUsers as $value){
                $arrFill["username"]=$value[1];
                $arrFill["name"]=$value[2];
                $arrFill["password1"]="";
                $arrFill["password2"]="";
                $arrFill["organization"]=$value[4];
                $arrFill["group"]=$value[7];
                $extu=isset($value[5])?$value[5]:_tr("Not assigned yet");
                $extf=isset($value[6])?$value[6]:_tr("Not assigned yet");
                $arrFill["extension"]=$extu;
                $arrFill["fax_extension"]=$extf;
            }
            if($arrFill["organization"]!=1)
                $smarty->assign("ORGANIZATION",htmlentities($arrOrgz[$arrFill["organization"]], ENT_COMPAT, 'UTF-8'));
            $smarty->assign("USERNAME",$arrFill["username"]);
            $nGroup=$pACL->getGroupNameByid($arrFill["group"]);
            if($nGroup=="superadmin");
                $nGroup=_tr("NONE");
            $smarty->assign("GROUP",$nGroup);
            $_POST["organization"]=$arrFill["organization"];
            
            //ahora obtenemos las configuraciones de fax dle usuario
            $pFax=new paloFax($pACL->_DB);
            $listFaxs=$pFax->getFaxList(array("exten"=>$extf,"organization_domain"=>$arrDomains[$arrFill["organization"]]));
            if($listFaxs!=false){
                $faxUser=$listFaxs[0];
                $arrFill["country_code"]=$faxUser["country_code"];
                $arrFill["area_code"]=$faxUser["area_code"];
                $arrFill["clid_number"]=$faxUser["clid_number"];
                $arrFill["clid_name"]=$faxUser["clid_name"];
            }
            
            //ahora obtenemos la cuenta del email
            $arrFill["email_quota"]=$pACL->getUserProp($idUser,"email_quota");
            if($idUser=="1")
                $arrFill["email_contact"]=$pACL->getUserProp($idUser,"email_contact");
            $smarty->assign("EMAILQOUTA",$arrFill["email_quota"]);
            $smarty->assign("EXTENSION",$extu);
            $smarty->assign("FAX_EXTENSION",$extf);
            if(getParameter("save_edit")){
                $arrFill=$_POST;
            }
        }
    }

    if($credentials["userlevel"]!="superadmin"){
        $idOrgSel=$credentials["id_organization"];
    }else
        $idOrgSel=getParameter("organization");
    
    if(!isset($idOrgSel)){
        $idOrgSel=0;
    }

    if($idOrgSel==0){
        $arrGrupos=array();
    }else{
        $temp = $pACL->getGroupsPaging(null,null,$idOrgSel);
        if($temp===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr($pACL->errMsg));
            return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
        }
        foreach($temp as $value){
            $arrGrupos[$value[0]]=$value[1];
        }
    }

    if(getParameter("create_user")){
        $arrFill["country_code"]=$pORGZ->getOrganizationProp($idOrgSel,"country_code");
        $arrFill["area_code"]=$pORGZ->getOrganizationProp($idOrgSel,"area_code");
        $arrFill["email_quota"]=$pORGZ->getOrganizationProp($idOrgSel,"email_quota");
    }

    $arrFormOrgz = createFieldForm($arrGrupos,$arrOrgz);
    $oForm = new paloForm($smarty,$arrFormOrgz);

    $smarty->assign("HEIGHT","310px");
    $smarty->assign("MARGIN_PIC",'style="margin-top: 40px;"');
    $smarty->assign("MARGIN_TAB","");

    if($action=="view"){
        $smarty->assign("HEIGHT","220px");
        $smarty->assign("MARGIN_PIC","");
        $smarty->assign("MARGIN_TAB","margin-top: 10px;");
        $oForm->setViewMode();
        $arrFill["password1"]="*****";
        $arrFill["password2"]="*****";
        $smarty->assign("HEIGHT","220px");
    }else if(getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
    }
    
    global $arrPermission;
    if(in_array('create_user',$arrPermission)){
        $smarty->assign("CREATE_USER",true);
    }
    if(in_array('edit_user',$arrPermission)){
        $smarty->assign("EDIT_USER",true);
    }
    if(in_array('delete_user',$arrPermission)){
        $smarty->assign("DEL_USER",true);
    }
    
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("icon","../web/_common/images/user_info.png");
    $smarty->assign("FAX_SETTINGS",_tr("Fax Settings"));
    $smarty->assign("EMAIL_SETTINGS",_tr("Email Settings"));
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("userLevel", $credentials["userlevel"]);
    $smarty->assign("id_user", $idUser);
    if(isset($arrUsers[0][1]))
        $smarty->assign("isSuperAdmin",$pACL->isUserSuperAdmin($arrUsers[0][1]));
    else
        $smarty->assign("isSuperAdmin",FALSE);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("User"), $arrFill);
    $content = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewUser($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrCredentiasls){
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $exito = false;
    $continuar=true;
    $errorImg="";
    $renameFile="";

    if($pORGZ->getNumOrganization(array()) == 0){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("It's necesary you create a new organization so you can create user"));
        return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }

    $arrOrgz=array(0=>"Select one Organization");
    if($arrCredentiasls['userlevel']=="superadmin"){
        $orgTmp=$pORGZ->getOrganization(array());
    }else{
        $orgTmp=$pORGZ->getOrganization(array("id"=>$arrCredentiasls['id_organization']));
    }

    if($orgTmp===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pORGZ->errMsg));
        return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }elseif(count($orgTmp)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("You need yo have at least one organization created before you can create a user"));
        return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }else{
        foreach($orgTmp as $value){
            $arrOrgz[$value["id"]]=$value["name"];
        }
    }

    $arrFormOrgz = createFieldForm(array(),array());
    $oForm = new paloForm($smarty,$arrFormOrgz);

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
        return viewFormUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }else{
        $password1=getParameter("password1");
        $password2=getParameter("password2");
        $organization=getParameter("organization");
        if($password1==""){
            $error=_tr("Password can not be empty");
        }else if($password1!=$password2){
            $error=_tr("Passwords don't match");
        }else{
            if(!isStrongPassword($password1)){
                $error=_tr("Secret can not be empty, must be at least 10 characters, contain digits, uppers and little case letters");
                $continuar=false;
            }
            
            if($arrCredentiasls['userlevel']=="superadmin"){
                if($organization==0 || $organization==1){
                    $error=_tr("You must select a organization");
                    $continuar=false;
                }else
                    $idOrganization=$organization;
            }else
                $idOrganization=$arrCredentiasls['id_organization'];
                
            if($continuar){
                $username=getParameter("username");
                $name=getParameter("name");
                $idGrupo=getParameter("group");
                $extension=getParameter("extension");
                $fax_extension=getParameter("fax_extension");
                $md5password=md5($password1);
                $countryCode=getParameter("country_code");
                $areaCode=getParameter("area_code");
                $clidNumber=getParameter("clid_number");
                $cldiName=getParameter("clid_name");
                $quota=getParameter("quota");
                $exito=$pORGZ->createUserOrganization($idOrganization, $username, $name, $md5password, $password1, $idGrupo, $extension, $fax_extension,$countryCode, $areaCode, $clidNumber, $cldiName, $quota, $lastid);
                $error=$pORGZ->errMsg;
            }
        }
    }

    if($exito){
        //esta seccion es solo si el usuario quiere subir una imagen a su cuenta
        if(isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != ""){
            uploadImage($lastid,$pDB,$errorImg);
        }
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("User has been created successfully")."</br>".$errorImg);
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $orgTmp2 = $pORGZ->getOrganization(array("id" => $idOrganization));
        $pAstConf->setReloadDialplan($orgTmp2[0]["domain"],true);
        $content = reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }
    return $content;
}


function saveEditUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls){
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $exito = false;
    $idUser=getParameter("id");
    $errorImg="";
    $renameFile="";
    $reAsterisk=false;

    //obtenemos la informacion del usuario por el id dado, sino existe el usuario mostramos un mensaje de error
    if(!isset($idUser)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid User"));
        return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }else{
        if($arrCredentiasls['userlevel']=="superadmin"){
            $arrUsers = $pACL->getUsers($idUser);
        }else{
            $arrUsers = $pACL->getUsers($idUser, $arrCredentiasls['id_organization']);
        }
    }

    if($arrUsers===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pACL->errMsg));
        return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }else if(count($arrUsers)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("User doesn't exist"));
        return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }else{
        $idOrgz=$arrUsers[0][4]; //una vez creado un usuario este no se puede cambiar de organizacion
        $arrOrgz=array();
        $temp = $pACL->getGroupsPaging(null,null,$idOrgz);
        if($temp===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr($pACL->errMsg));
            return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
        }
        foreach($temp as $value){
            $arrGrupos[$value[0]]=$value[1];
        }

        $arrFormOrgz = createFieldForm($arrGrupos,$arrOrgz);
        $oForm = new paloForm($smarty,$arrFormOrgz);

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
            return viewFormUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
        }else{
            $password1=getParameter("password1");
            $password2=getParameter("password2");
            $quota=getParameter("email_quota");
            $countryCode=getParameter("country_code");
            $areaCode=getParameter("area_code");
            $idGrupo=getParameter("group");
            $extension=getParameter("extension");
            $fax_extension=getParameter("fax_extension");
            $name=getParameter("name");
            $md5password=md5($password1);
            $clidNumber=getParameter("clid_number");
            $cldiName=getParameter("clid_name");
            
            if($pACL->isUserSuperAdmin($arrUsers[0][1])){
                $idGrupo=$arrUsers[0][7];
                $email_contact=getParameter("email_contact");
                $exito=$pORGZ->updateUserSuperAdmin($idUser, $name, $md5password, $password1, $email_contact, $arrCredentiasls['userlevel']);
                $error=$pORGZ->errMsg;
            }else{
                if($password1!=$password2){
                    $error=_tr("Passwords don't match");
                }elseif($password1!="" && !isStrongPassword($password1)){
                    $error=_tr("Secret can not be empty, must be at least 10 characters, contain digits, uppers and little case letters");
                }elseif(!isset($quota) || $quota==""){
                    $error=_tr("Qouta must not be empty");
                }elseif(!isset($countryCode) || $countryCode==""){
                    $error=_tr("Country Code must not be empty");
                }elseif(!isset($areaCode) || $areaCode==""){
                    $error=_tr("Area Code must not be empty");
                }elseif(!isset($clidNumber) || $clidNumber==""){
                    $error=_tr("C er Id Number must not be empty");
                }elseif(!isset($cldiName) || $cldiName==""){
                    $error=_tr("Caller Id Name must not be empty");
                }else{
                    $exito=$pORGZ->updateUserOrganization($idUser, $name, $md5password, $password1, $extension, $fax_extension,$countryCode, $areaCode, $clidNumber, $cldiName, $idGrupo, $quota, $arrCredentiasls['userlevel'], $reAsterisk);
                    $error=$pORGZ->errMsg;
                }
            }
        }
    }

    if($exito){
        //esta seccion es solo si el usuario quiere subir una imagen a su cuenta
        if(isset($_FILES['picture']['name']) && $_FILES['picture']['name']!=""){
            uploadImage($idUser,$pDB,$errorImg);
        }
        
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("User has been edited successfully")."<br>$errorImg");
        if($reAsterisk){
            //mostramos el mensaje para crear los archivos de ocnfiguracion
            $pAstConf=new paloSantoASteriskConfig($pDB);
            $orgTmp2 = $pORGZ->getOrganization(array("id" => $idOrgz));
            $pAstConf->setReloadDialplan($orgTmp2[0]["domain"],true);
        }
        $content = reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }
    return $content;
}

function deleteUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls){
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $idUser=getParameter("id");
    $exito=false;

    $idOrgReload=$pACL->getIdOrganizationUser($idUser);
    if($idOrgReload==false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pACL->errMsg));
        return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }
    
    if($arrCredentiasls['userlevel']=="superadmin"){
        if($idUser==1){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("The admin user cannot be deleted because is the default Elastix administrator. You can delete any other user."));
            return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
        }else{
            $exito=$pORGZ->deleteUserOrganization($idUser);
        }
    }else{
        if($idOrgReload==$arrCredentiasls['id_organization']){
            $exito=$pORGZ->deleteUserOrganization($idUser);
        }else{
            $pORGZ->errMsg=_tr("Invalid User");
        }
    }

    if($exito){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("The user was deleted successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $orgTmp2 = $pORGZ->getOrganization(array("id" => $idOrgReload));
        $pAstConf->setReloadDialplan($orgTmp2[0]["domain"],true);
        $content = reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pORGZ->errMsg));
        $content = reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }

    return $content;
}

function getGroups(&$pDB,$arrCredentiasls){
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $jsonObject = new PaloSantoJSON();
    $idOrgSel = getParameter("idOrganization");
    
    $arrGrupos = array();
    if($idOrgSel==0){
        $arrGrupos=array();
    }else{
        if($arrCredentiasls['userlevel']!='superadmin'){
            if($idOrgSel!=$arrCredentiasls['id_organization']){
                $jsonObject->set_error("Invalid Action");
                $arrGrupos=array();
            }
        }else{
            $arrGrupos[0]=array("country_code",$pORGZ->getOrganizationProp($idOrgSel,"country_code"));
            $arrGrupos[1]=array("area_code",$pORGZ->getOrganizationProp($idOrgSel,"area_code"));
            $arrGrupos[2]=array("email_quota",$pORGZ->getOrganizationProp($idOrgSel,"email_quota"));
            $temp = $pACL->getGroupsPaging(null,null,$idOrgSel);
            if($temp===false){
                $jsonObject->set_error(_tr($pACL->errMsg));
            }else{
                $i=3;
                foreach($temp as $value){
                    $arrGrupos[$i]=array($value[0],$value[1]);
                    $i++;
                }
            }
        }
    }
    $jsonObject->set_message($arrGrupos);
    return $jsonObject->createJSON();
}

function getImage($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrCredentiasls){
    $pACL       = new paloACL($pDB);
    $imgDefault = $_SERVER['DOCUMENT_ROOT']."/admin/web/apps/$module_name/images/Icon-user.png";
    $id_user=getParameter("ID");
    $picture=false;

    if($arrCredentiasls["userlevel"]=="superadmin"){
        $picture = $pACL->getUserPicture($id_user);
    }else{
        //verificamos que el usario pertenezca a la organizacion
        if($pACL->userBellowOrganization($id_user,$arrCredentiasls["id_organization"]))
            $picture = $pACL->getUserPicture($id_user);
    } 
    
    // Creamos la imagen a partir de un fichero existente
    if($picture!=false && !empty($picture["picture_type"])){
        Header("Content-type: {$picture["picture_type"]}");
        print $picture["picture_content"];
    }else{
        Header("Content-type: image/png");
        $im = file_get_contents($imgDefault);
        echo $im;
    }
    return;
}


function uploadImage($idUser,$pDB,&$error){
    $pACL = new paloACL($pDB);
    $pictureUpload = $_FILES['picture']['name'];
    $Exito=false;

    //valido el tipo de archivo
    // \w cualquier caracter, letra o guion bajo
    // \s cualquier espacio en blanco
    if (!preg_match("/^(\w|-|\.|\(|\)|\s)+\.(png|PNG|JPG|jpg|JPEG|jpeg)$/",$pictureUpload)) {
        $error=_tr("Invalid file extension.- It must be png or jpg or jpeg");
    }elseif(preg_match("/(\.php)/",$pictureUpload)){
        $error=_tr("Possible file upload attack.");
    }else{
        if(is_uploaded_file($_FILES['picture']['tmp_name'])){
            $ancho = 240;
            $alto = 200;
            redimensionarImagen($_FILES['picture']['tmp_name'],$_FILES['picture']['tmp_name'],$ancho,$alto);
            $picture_type=$_FILES['picture']['type'];
            $picture_content=file_get_contents($_FILES['picture']['tmp_name']);
            $Exito=$pACL->setUserPicture($idUser,$picture_type,$picture_content);
            if($Exito===false){
                $error="Image couldn't be upload";
            }
        }else {
            $error=_tr("Possible file upload attack. Filename")." : ". $pictureUpload;
        }
    }
    return $Exito;
}

function redimensionarImagen($ruta1,$ruta2,$ancho,$alto){

    # se obtene la dimension y tipo de imagen
    $datos=getimagesize($ruta1);

    if(!$datos)
        return false;

    $ancho_orig = $datos[0]; # Anchura de la imagen original
    $alto_orig = $datos[1];    # Altura de la imagen original
    $tipo = $datos[2];
    $img = "";
    if ($tipo==1){ # GIF
        if (function_exists("imagecreatefromgif"))
            $img = imagecreatefromgif($ruta1);
        else
            return false;
    }
    else if ($tipo==2){ # JPG
        if (function_exists("imagecreatefromjpeg"))
            $img = imagecreatefromjpeg($ruta1);
        else
            return false;
    }
    else if ($tipo==3){ # PNG
        if (function_exists("imagecreatefrompng"))
            $img = imagecreatefrompng($ruta1);
        else
            return false;
    }

    $anchoTmp = imagesx($img);
    $altoTmp = imagesy($img);
    if(($ancho > $anchoTmp || $alto > $altoTmp)){
        ImageDestroy($img);
        return true;
    }

    # Se calculan las nuevas dimensiones de la imagen
    if ($ancho_orig>$alto_orig){
        $ancho_dest=$ancho;
        $alto_dest=($ancho_dest/$ancho_orig)*$alto_orig;
    }else{
        $alto_dest=$alto;
        $ancho_dest=($alto_dest/$alto_orig)*$ancho_orig;
    }

    // imagecreatetruecolor, solo estan en G.D. 2.0.1 con PHP 4.0.6+
    $img2=@imagecreatetruecolor($ancho_dest,$alto_dest) or $img2=imagecreate($ancho_dest,$alto_dest);

    // Redimensionar
    // imagecopyresampled, solo estan en G.D. 2.0.1 con PHP 4.0.6+
    @imagecopyresampled($img2,$img,0,0,0,0,$ancho_dest,$alto_dest,$ancho_orig,$alto_orig) or imagecopyresized($img2,$img,0,0,0,0,$ancho_dest,$alto_dest,$ancho_orig,$alto_orig);

    // Crear fichero nuevo, segÃºn extensiÃ³n.
    if ($tipo==1) // GIF
    if (function_exists("imagegif"))
        imagegif($img2, $ruta2);
    else
        return false;

    if ($tipo==2) // JPG
    if (function_exists("imagejpeg"))
        imagejpeg($img2, $ruta2);
    else
        return false;

    if ($tipo==3)  // PNG
    if (function_exists("imagepng"))
        imagepng($img2, $ruta2);
    else
        return false;

    return true;
}


function createFieldForm($arrGrupos,$arrOrgz){
    $arrFormElements = array("name" => array("LABEL"                  => _tr('Name').'(Ex. John Doe)',
                                                    "DESCRIPTION"           => _tr("Us_name"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "username"       => array("LABEL"                => _tr("Username"),
                                                    "DESCRIPTION"            => _tr("Us_username"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*",
                                                    "EDITABLE"               => "no"),
                                "password1"   => array("LABEL"                  => _tr("Password"),
                                                    "DESCRIPTION"            => _tr("Us_pass"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "PASSWORD",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                "password2"   => array("LABEL"                  => _tr("Retype password"),
                                                    "DESCRIPTION"            => _tr("Us_confpass"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "PASSWORD",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                "organization"       => array("LABEL"           => _tr("Organization"),
                                                    "DESCRIPTION"            => _tr("Us_organization"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrOrgz,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => "",
                                                    "ONCHANGE"	       => "select_organization();"),
                                "group"       => array("LABEL"                  => _tr("Group"),
                                                    "DESCRIPTION"            => _tr("Us_group"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrGrupos,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "extension"   => array("LABEL"                   => _tr("Extension"),
                                                    "DESCRIPTION"            => _tr("Us_extension"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "fax_extension"   => array("LABEL"               => _tr("Fax Extension"),
                                                    "DESCRIPTION"            => _tr("Us_faxext"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "country_code"   => array("LABEL"               => _tr("Country Code"),
                                                    "DESCRIPTION"            => _tr("Us_countrycode"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "area_code"   => array("LABEL"               => _tr("Area Code"),
                                                    "DESCRIPTION"            => _tr("Us_areacode"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "clid_name"   => array("LABEL"               => _tr("Fax Cid Name"),
                                                    "DESCRIPTION"            => _tr("Us_faxcidname"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "clid_number" => array("LABEL"               => _tr("Fax Cid Number"),
                                                    "DESCRIPTION"            => _tr("Us_faxcidnumber"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "email_quota" => array("LABEL"               => _tr("Email Quota")." (MB)",
                                                    "DESCRIPTION"            => _tr("Us_emailquota"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "email_contact"   => array( "LABEL"                  => _tr("Email Contact"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "email",
                                                    "VALIDATION_EXTRA_PARAM" => ""
                                                    ),
                            "picture"  	 => array("LABEL"                  => _tr("Picture"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "FILE",
                                                    "INPUT_EXTRA_PARAM"      => array("id" => "picture"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
    );
    return $arrFormElements;
}

function createFieldFilter($arrOrgz){
    $arrFields = array(
        "idOrganization"  => array("LABEL"       => _tr("Organization"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "SELECT",
                        "INPUT_EXTRA_PARAM"      => $arrOrgz,
                        "VALIDATION_TYPE"        => "numeric",
                        "VALIDATION_EXTRA_PARAM" => ""),
         "username"       => array("LABEL"       => _tr("Username"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "TEXT",
                        "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                        "VALIDATION_TYPE"        => "text",
                        "VALIDATION_EXTRA_PARAM" => ""),
        );
    return $arrFields;
}


function reloadAasterisk($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrCredentiasls){
    $showMsg=false;
    $continue=false;

    /*if($arrCredentiasls['userlevel']=="other"){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("You are not authorized to perform this action"));
    }*/

    $idOrganization=$arrCredentiasls['id_organization'];
    if($arrCredentiasls['userlevel']=="superadmin"){
        $idOrganization = getParameter("organization_id");
    }

    if($idOrganization==1){
        return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
    }

    $query="select domain from organization where id=?";
    $result=$pDB->getFirstRowQuery($query, false, array($idOrganization));
    if($result===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Asterisk can't be reloaded. ")._tr($pDB->errMsg));
        $showMsg=true;
    }elseif(count($result)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Asterisk can't be reloaded. ")._tr("Invalid Organization. "));
        $showMsg=true;
    }else{
        $domain=$result[0];
        $continue=true;
    }

    if($continue){
        $pAstConf=new paloSantoASteriskConfig($pDB);
        if($pAstConf->generateDialplan($domain)===false){
            $pAstConf->setReloadDialplan($domain,true);
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Asterisk can't be reloaded.").$pAstConf->errMsg);
            $showMsg=true;
        }else{
            $pAstConf->setReloadDialplan($domain);
            $smarty->assign("mb_title", _tr("MESSAGE"));
            $smarty->assign("mb_message",_tr("Asterisk was reloaded correctly."));
        }
    }

    return reportUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentiasls);
}

function getAction(){
    global $arrPermission;
    if(getParameter("create_user")){
        return (in_array('create_user',$arrPermission))?'new_user':'report';
    }else if(getParameter("save_new")){ //Get parameter by POST (submit)
        return (in_array('create_user',$arrPermission))?'save_new':'report';
    }else if(getParameter("save_edit")){
        return (in_array('edit_user',$arrPermission))?'save_edit':'report';
    }else if(getParameter("edit")){
        return (in_array('edit_user',$arrPermission))?'edit':'report';
    }else if(getParameter("delete")){
        return (in_array('delete_user',$arrPermission))?'delete':'report';
    }else if(getParameter("action")=="view"){      //Get parameter by GET (command pattern, links)
        return 'view'; 
    }else if(getParameter("action")=="reconstruct_mailbox"){
        return (in_array('reconstruct_mailbox',$arrPermission))?'reconstruct_mailbox':'report';
    }else if(getParameter("action")=="get_groups"){
        return "getGroups";
    }else if(getParameter("action")=="getImage"){
        return "getImage";
    }else if(getParameter("action")=="reloadAsterisk"){
        return "reloadAasterisk";
    }else
        return "report"; //cancel
}
?>
