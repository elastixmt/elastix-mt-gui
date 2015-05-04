<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.2.0-29                                               |
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
  $Id: index.php,v 1.1 2012-02-07 11:02:12 Rocio Mera rmera@palosanto.com Exp $ */
//include elastix framework

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoJSON.class.php";

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    
    include_once "apps/did/libs/paloSantoDID.class.php";
    include_once "libs/paloSantoOrganization.class.php";
    
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
    
    //user credentials
    global $arrCredentials;
    
    $action = getAction();
    $content = "";

    switch($action){
        case "new_organization":
            $content = viewFormOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "edit":
            $content = viewFormOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$arrCredentials);
            break;
        case "save_new":
            $content = saveNewOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "get_country_code":
            $content=get_country_code();
            break;
        case "reportDIDs":
            $content=reportDIDorganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "didAssign":
            $content=didAssign($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "saveDidAssign":
            $content=didAssign($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "changeDIDfilter":
            $content=changeDIDfilter($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "removeDID":
            $content=removeDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "reloadAsterisk":
            $content = reloadAsterisk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "change_state":
            $content = change_state($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete_org_2":
            $content = delete_org_2($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default: // report
            $content = reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
    }
    return $content;
}
            

function reportOrganization($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $pOrganization = new paloSantoOrganization($pDB);
    $pACL = new paloACL($pDB);
    $arrData = array();
    $arrOrgs = false;
    $arrProp["name"]=null;
    $arrProp["domain"]=null;
    $arrProp["state"]='all';

    if($credentials["userlevel"]=="superadmin"){
        $arrProp["name"]=getParameter("fname");
        $arrProp["domain"]=getParameter("fdomain");
        $arrProp["state"]=getParameter("fstate");
        $total=$pOrganization->getNumOrganization($arrProp);
    }else{
        $arrProp["id"]=$credentials["id_organization"];
        $total=$pOrganization->getNumOrganization($arrProp);
    }

    if($total===false){
        $total=0;
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",_tr("Couldn't be retrieved organization data"));
    }
    
    $limit=20;
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $url['menu']=$module_name;
    $url['fname']=$arrProp["name"];
    $url['fstate']=$arrProp["state"];
    $url['fdomain']=$arrProp["domain"];
    
    $oGrid->setTitle(_tr('Organization List'));
    $oGrid->setURL($url);
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    
    $arrColumns=array();
    if($credentials["userlevel"]=="superadmin"){
        $arrColumns[]=""; //delete
    }
    if(in_array('access_DID',$arrPermission)){
        $arrColumns[]=""; //did
    }
    $arrColumns[]=_tr("Domain");
    $arrColumns[]=_tr("Name");
    $arrColumns[]=_tr("State");
    $arrColumns[]=_tr("Number of Users");
    $arrColumns[]=_tr("Country Code")." / "._tr("Area Code");
    $arrColumns[]=_tr("Email Qouta")." (MB)";                
    $oGrid->setColumns($arrColumns);

    
    $arrDatosGrid=array();
    if($total!=0){
        if($credentials["userlevel"]=="superadmin"){
            $arrProp["limit"]=$limit;
            $arrProp["offset"]=$offset;
            $arrOrgs = $pOrganization->getOrganization($arrProp);
        }else
            $arrOrgs = $pOrganization->getOrganization($arrProp);
    }
    
    if($arrOrgs===FALSE)
    {
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",_tr($pOrganization->errMsg));
    }else{
        foreach($arrOrgs as $value)
        {
            $arrTmp = array();
            if($credentials["userlevel"]=="superadmin"){
                $arrTmp[] = "<input type='checkbox' class='chk_id' value='{$value['id']}' />"; //checkbox selet
            }
            if(in_array('access_DID',$arrPermission)){
                $arrTmp[] = "&nbsp;<a href='?menu=$module_name&action=reportDIDs&domain=".$value['domain']."'>"._tr("Assign DIDs")."</a>"; //did
            }
            $arrTmp[] = "&nbsp;<a href='?menu=$module_name&action=view&id=".$value['id']."'>".htmlentities($value['domain'], ENT_COMPAT, 'UTF-8')."</a>";
            $arrTmp[] = htmlentities($value['name'], ENT_COMPAT, 'UTF-8');
            
            if($value['state']=='active'){
                $arrTmp[]="<span class='font-green'>"._tr($value['state'])."</span>";
            }elseif($value['state']=='suspend'){
                $arrTmp[]="<span class='font-orange'>"._tr($value['state'])."</span>";
            }else
                $arrTmp[]="<span class='font-red'>"._tr($value['state'])."</span>";
                
            $arrTmp[] = $pOrganization->getNumUserByOrganization($value['id']);
            
            $cCode=$pOrganization->getOrganizationProp($value['id'],"country_code");
            $aCode=$pOrganization->getOrganizationProp($value['id'],"area_code");
            $eQuota=$pOrganization->getOrganizationProp($value['id'],"email_quota");
            $tmpcode = ($cCode===false)?_tr("NONE"):$cCode;
            $tmpcode .=($aCode===false)?_tr("NONE"):" / ".$aCode;
            $arrTmp[] = $tmpcode;
            $arrTmp[] = ($eQuota===false)?_tr("NONE"):$eQuota;
            $arrDatosGrid[] = $arrTmp;
        }
    }
    
	if($credentials['userlevel']=="superadmin"){
        $oGrid->addNew("new_organization",_tr("Create Organization"));
        $stateButton='<select name="state_orgs" id="state_orgs">';
        $stateButton .='<option label="'._tr("Suspend").'" value="suspend">'._tr("Suspend").'</option>';
        $stateButton .='<option label="'._tr("Unsuspend").'" value="unsuspend">'._tr("Unsuspend").'</option>';
        $stateButton .='<option label="'._tr("Terminate").'" value="terminate">'._tr("Terminate").'</option>';
        $stateButton .="</select>";
        $stateButton .='<input type="button" name="button_state" value="'._tr("Change State").'" onclick="change_state();" class="neo-table-action">';
        $stateButton .='<input type="hidden" name="msg_ch_alert" id="msg_ch_alert" value="'._tr("Are you sure you wish change the states of checked organizations to: ")."STATE_NAME\n"._tr("This process can take several minutes").'">';
        $oGrid->addHTMLAction($stateButton);
        $oGrid->addButtonAction("del_orgs",_tr("Delete"),"{$arrConf['webCommon']}/images/delete5.png", "delete_orgs();");
        
        
        //filter
        $smarty->assign('USERLEVEL',$credentials['userlevel']);
        $smarty->assign('SEARCH',"<input name='search_org' type='submit' class='button' value='"._tr('Search')."'>");
        $arrState=array("all"=>_tr("All"),"active"=>_tr("Active"),"suspend"=>_tr("Suspend"),"terminate"=>_tr("terminate"));
        
        $_POST['fname']=$arrProp['name'];
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("fname")." = {$arrProp['name']}", $_POST, array("fname" =>''));
        
        $_POST['fdomain']=$arrProp['domain'];
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("fdomain")." = {$arrProp['domain']}", $_POST, array("fdomain" =>''));
        
        $_POST['fstate']=(isset($arrState[$arrProp['state']]))?$arrProp['state']:'all';  
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("State")." = ".$arrState[$_POST['fstate']], $_POST, array("fstate" =>'all'),true);
        
        $arrFormFilter = createFilterForm($arrState);
        $oFilterForm = new paloForm($smarty, $arrFormFilter);
        $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
        $oGrid->showFilter(trim($htmlFilter));
    }
        
    $content = $oGrid->fetchGrid(array(),$arrDatosGrid);
    $mensaje=showMessageReload($module_name, $pDB, $credentials);
    $content = $mensaje.$content;
    return $content;
}

function change_state($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $jsonObject = new PaloSantoJSON();
    $idOrgs=getParameter("idOrgs");
    $state=getParameter("state");
    
    if($credentials['userlevel']!="superadmin"){
        $jsonObject->set_error(_tr("You are not authorized to perform this action"));
        return $jsonObject->createJSON();
    }
    
    $arrOrgs=array_diff(explode(",",$idOrgs),array(""));
    
    if(!is_array($arrOrgs) || count($arrOrgs)==0){
        $jsonObject->set_error(_tr("Err: Any valid organization has been selected"));
        return $jsonObject->createJSON();
    }
    
    $pOrg = new paloSantoOrganization($pDB);
    
    if($pOrg->changeStateOrganization($arrOrgs,$state)){
        $jsonObject->set_message(_tr("State of selected organizations have been updated successfully"));
    }else{
        $jsonObject->set_error($pOrg->errMsg);
    }
    
    return $jsonObject->createJSON();
}

function showMessageReload($module_name, &$pDB, $credentials){
    $pDBMySQL=new paloDB(generarDSNSistema("asteriskuser", "elxpbx"));
    $pAstConf=new paloSantoASteriskConfig($pDB);
    $params=array();
    $msgs="";

    $query = "SELECT domain, id from organization";
    //si es superadmin aparece un link por cada organizacion que necesite reescribir su plan de mnarcada
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

function viewFormOrganization($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    $pOrganization = new paloSantoOrganization($pDB);
    $pACL = new paloACL($pDB);
    $dataOrgz = false;
    $arrFill = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");

    $check_e=isset($_POST["max_num_exten_chk"])?"checked":"";
    $check_q=isset($_POST["max_num_queues_chk"])?"checked":"";
    $check_u=isset($_POST["max_num_user_chk"])?"checked":"";

    $smarty->assign("edit_entity",0);
    if($action=="view" || getParameter("edit") || getParameter("save_edit")){ 
        if($id=="1"){//no se puede editar ni observar la organizacion principal
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("Invalid ID Organization"));
            return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
        
        if($credentials['userlevel']!="superadmin" && ($id!=$credentials['id_organization']) ){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("Invalid Organization"));
            return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
        
        $dataOrgz = $pOrganization->getOrganizationById($id);
        if(is_array($dataOrgz) & count($dataOrgz)>0){
            $num_exten = $pOrganization->getOrganizationProp($id ,"max_num_exten");
            $num_queues = $pOrganization->getOrganizationProp($id ,"max_num_queues");
            $num_users = $pOrganization->getOrganizationProp($id ,"max_num_user");
            if($credentials['userlevel']!="superadmin"){
                $check_e=empty($num_exten)?_tr("unlimited"):$num_exten;
                $check_q=empty($num_queues)?_tr("unlimited"):$num_queues;
                $check_u=empty($num_users)?_tr("unlimited"):$num_users;
            }
            if(!getParameter("save_edit")){
                $arrFill['name'] = $dataOrgz['name'];
                $arrFill['country'] = $dataOrgz['country'];
                $arrFill['city'] = $dataOrgz['city'];
                $arrFill['address'] = $dataOrgz['address'];
                $arrFill['email_contact'] = $dataOrgz['email_contact'];
                $arrFill['country_code'] = $pOrganization->getOrganizationProp($id ,"country_code");
                $arrFill['area_code'] = $pOrganization->getOrganizationProp($id ,"area_code");
                $arrFill['quota'] = $pOrganization->getOrganizationProp($id ,"email_quota");
                $arrFill['domain'] = $dataOrgz['domain'];
                if($credentials['userlevel']=="superadmin"){
                    if(empty($num_exten)){
                        $check_e="checked";
                    }else{
                        $check_e="";
                        $arrFill["max_num_exten"]=$num_exten;
                    }
                    if(empty($num_queues)){
                        $check_q="checked";
                    }else{
                        $check_q="";
                        $arrFill["max_num_queues"]=$num_queues;
                    }
                    if(empty($num_users)){
                        $check_u="checked";
                    }else{
                        $check_u="";
                        $arrFill["max_num_user"]=$num_users;
                    }
                }
            }
            $smarty->assign("domain_name", $dataOrgz['domain']);
        }else{
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message",_tr("An error has ocurred to try retrieve organization data"));
            return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{
        //solo el superadmin tiene permitido crear organizaciones
        if($credentials['userlevel']!="superadmin"){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", _tr("You are not authorized to perform this action"));
            return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
        if(getParameter("new_organization")){
            $arrFill['quota'] = 30;
            $check_e="checked";
            $check_u="checked";
            $check_q="checked";
        }
    }
    
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl
    $smarty->assign("ORG_RESTRINCTION", _tr("Organization Limits"));
    $smarty->assign("UNLIMITED", _tr("unlimited"));
    $smarty->assign("CHECK_U", $check_u);
    $smarty->assign("CHECK_E", $check_e);
    $smarty->assign("CHECK_Q", $check_q);
    $smarty->assign("USERLEVEL", $credentials['userlevel']);
    $smarty->assign("APLICAR_CAMBIOS", _tr("Apply Changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
   // $smarty->assign("icon", "web/apps/organizaciones/images/organization.png");

    //variable usadas en el tpl
    //estas acciones solosp pueden ser realizadas por el susperadmin
    global $arrPermission;
    if($credentials['userlevel']=="superadmin"){
        if(in_array('create_org',$arrPermission)){
            $smarty->assign('CREATE_ORG',TRUE);
        }
        if(in_array('delete_org',$arrPermission)){
            $smarty->assign('DELETE_ORG',TRUE);
        }
    }
    if(in_array('edit_org',$arrPermission)){
        $smarty->assign('EDIT_ORG',TRUE);
    }
    
    $arrFormOrgz = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormOrgz);
    if($action=="view"){
        $oForm->setViewMode();
        $smarty->assign("edit_entity",1);
    }else if(getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
        $smarty->assign("edit_entity",1);
    }
    
    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Organization"), $arrFill);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}


function saveNewOrganization($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    $pOrganization = new paloSantoOrganization($pDB);
    $arrFormOrgz = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormOrgz);
    $error="";
    $exito=false;

    if($credentials['userlevel']!="superadmin"){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("You are not authorized to perform this action"));
        return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

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
        return viewFormOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $name = trim(getParameter("name"));
        $domain = trim(getParameter("domain"));
        $country = trim(getParameter("country"));
        $state = trim(getParameter("city"));
        $address = trim(getParameter("address"));
        $country_code = trim(getParameter("country_code"));
        $area_code = trim(getParameter("area_code"));
        $quota = trim(getParameter("quota"));
        $email_contact = trim(getParameter("email_contact"));
        $num_user = isset($_POST["max_num_user_chk"])?"0":getParameter("max_num_user");
        $num_exten = isset($_POST["max_num_exten_chk"])?"0":getParameter("max_num_exten");
        $num_queues = isset($_POST["max_num_queues_chk"])?"0":getParameter("max_num_queues");
        
        if($country=="0" || !isset($country)){
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", _tr("You must select a country"));
            return viewFormOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
        
        if(!isset($_POST["max_num_user_chk"]) && (!ctype_digit($num_user) || ($num_user+0)==0)){
                $error=_tr("Field ")._tr("Max. # of User Accounts")._tr(" must be a integer > 0");
            }else
                $num_user=$num_user+0;
            
        if(!isset($_POST["max_num_exten_chk"]) && (!ctype_digit($num_exten) || ($num_exten+0)==0)){
            $error=_tr("Field '")._tr("Max. # of extensions")._tr(" must be a integer > 0");
        }elseif(($num_exten<$num_user && $num_exten!=0)  || ($num_user==0 && $num_exten!=0)){
            $error=_tr("Field ")._tr("Max. # of extensions")._tr(" must be greater than Field ")._tr("Max. # of User Accounts");
        }else
            $num_exten=$num_exten+0;
        
        if(!isset($_POST["max_num_queues_chk"]) && (!ctype_digit($num_queues) || ($num_queues+0)==0)){
            $error=_tr("Field ")._tr("Max. # of queues")._tr(" must be a integer > 0");
        }else
            $num_queues=$num_queues+0;
        
        if($error!=""){
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", $error);
            return viewFormOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
        
        $admin_password=generatePassword();
        $exito=$pOrganization->createOrganization($name,$domain,$country,$state,$address,$country_code,$area_code,$quota,$email_contact,$num_user,$num_exten,$num_queues,$admin_password);
        if($exito!==false){
            $smarty->assign("mb_title", _tr("Message"));
            $smarty->assign("mb_message", _tr("The organization was created successfully")."<br />"._tr("To admin the new organization login to elastix as admin@").$domain.$pOrganization->errMsg);
            return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }else{
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message",_tr($pOrganization->errMsg));
            return viewFormOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }
}

function generatePassword(){
    //password debe tener minimo 10 caracteres y contener digitos y upper case
    $chars = "abABcdCDefEFghGHijIJkmKMnpNPqrQRstSTuvUVwxWXyzYZ23456789";
    $pass="";
    srand((double)microtime()*1000000);   
    // Genero los caracteres del password
    while (strlen($pass) < 10) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass .= $tmp;
    }
    return $pass;
}

function saveEditOrganization($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    $pOrganization = new paloSantoOrganization($pDB);
    $arrFormOrgz = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormOrgz);
    $error = "";
    $idOrg=getParameter("id");   
    
    if($credentials['userlevel']!="superadmin"){
       //si el usuario es diferente de superadmin el usuario debe pertence a la organizacion que quiere editar
        if($idOrg!=$credentials['id_organization']){
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", _tr("Invalid ID organization"));
            return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }
    
    if(!isset($idOrg) || $idOrg=="1"){
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message", _tr("Invalid ID organization"));
        return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

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
        $content = viewFormOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    else{
        $name = trim(getParameter("name"));
        $country = trim(getParameter("country"));
        $city = trim(getParameter("city"));
        $address = trim(getParameter("address"));
        $country_code = trim(getParameter("country_code"));
        $area_code = trim(getParameter("area_code"));
        $quota = trim(getParameter("quota"));
        $email_contact = trim(getParameter("email_contact"));
        
        if($credentials['userlevel']=="superadmin"){
            $num_user = isset($_POST["max_num_user_chk"])?"0":getParameter("max_num_user");
            $num_exten = isset($_POST["max_num_exten_chk"])?"0":getParameter("max_num_exten");
            $num_queues = isset($_POST["max_num_queues_chk"])?"0":getParameter("max_num_queues");
            
            if(!isset($_POST["max_num_user_chk"]) && (!ctype_digit($num_user) || ($num_user+0)==0)){
                $error=_tr("Field ")._tr("Max. # of User Accounts")._tr(" must be a integer > 0");
            }else
                $num_user=$num_user+0;
            
            if(!isset($_POST["max_num_exten_chk"]) && (!ctype_digit($num_exten) || ($num_exten+0)==0)){
                $error=_tr("Field '")._tr("Max. # of extensions")._tr(" must be a integer > 0");
            }elseif(($num_exten<$num_user && $num_exten!=0)  || ($num_user==0 && $num_exten!=0)){
                $error=_tr("Field ")._tr("Max. # of extensions")._tr(" must be greater than Field ")._tr("Max. # of User Accounts");
            }else
                $num_exten=$num_exten+0;
            
            if(!isset($_POST["max_num_queues_chk"]) && (!ctype_digit($num_queues) || ($num_queues+0)==0)){
                $error=_tr("Field ")._tr("Max. # of queues")._tr(" must be a integer > 0");
            }else
                $num_queues=$num_queues+0;
            
            if($error!=""){
                $smarty->assign("mb_title", _tr("Error"));
                $smarty->assign("mb_message", $error);
                return viewFormOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
            }
        }else{
            $num_user=null;
            $num_exten=null;
            $num_queues=null;
        }
            
        if($country=="0" || !isset($country)){
            $smarty->assign("mb_title", _tr("Error"));
            $smarty->assign("mb_message", _tr("You must select a country"));
            $content = viewFormOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }else{
            $exito=$pOrganization->setOrganization($idOrg,$name,$country,$city,$address,$country_code,$area_code,$quota,$email_contact,$num_user,$num_exten,$num_queues,$credentials['userlevel']);
            if($exito)
            {
                $smarty->assign("mb_title", _tr("Message"));
                $smarty->assign("mb_message", _tr("The organization was edit successfully"));
                $content = reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
            }else{
                $smarty->assign("mb_title", _tr("Error"));
                $smarty->assign("mb_message", _tr($pOrganization->errMsg));
                $content = viewFormOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
            }
        }
    }
    return $content;
}

function deleteOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $pOrganization = new paloSantoOrganization($pDB);
    $id     = getParameter("id");
    $smarty->assign("ID", $id);
    
    //el susperadmin es el unico autorizado a borrar una organizacion
    if($credentials['userlevel']!="superadmin"){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("You are not authorized to perform this action"));
        return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    if($id==1){
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message", _tr("Invalid Organization"));
        return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $exito=$pOrganization->deleteOrganization($id);
    if($exito){
        $smarty->assign("mb_title", _tr("Message"));
        $smarty->assign("mb_message", _tr("The organization was deleted successfully"));
    }else{
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message", _tr($pOrganization->errMsg));
    }
    return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function delete_org_2($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $jsonObject = new PaloSantoJSON();
    $idOrgs=getParameter("idOrgs");
    
    //el susperadmin es el unico autorizado a borrar una organizacion
    if($credentials['userlevel']!="superadmin"){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message", _tr("You are not authorized to perform this action"));
        return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $arrOrgs=array_diff(explode(",",$idOrgs),array(""));
    
    if(!is_array($arrOrgs) || count($arrOrgs)==0){
        $jsonObject->set_error(_tr("Err: Any valid organization has been selected"));
        return $jsonObject->createJSON();
    }
    
    $pOrg = new paloSantoOrganization($pDB);
    
    if($pOrg->deleteOrganization($arrOrgs)){
        $jsonObject->set_message($pOrg->errMsg);
    }else{
        $jsonObject->set_error($pOrg->errMsg);
    }
    
    return $jsonObject->createJSON();
}

function get_country_code(){
    $jsonObject = new PaloSantoJSON();
    $country=getParameter("country");
    $arrSettings=getCountrySettings($country);
    if($arrSettings==false){
        $jsonObject->set_message("");
    }else{
        $jsonObject->set_message($arrSettings["code"]);
    }
    return $jsonObject->createJSON();
}

function createFieldForm()
{
    $arrCountry = array(_tr("Select a country").' --');
    $arrCountry = array_merge($arrCountry,getCountry());

    $arrFields = array(
            "name"   => array(      "LABEL"                  => _tr("Organization"),
                                            "DESCRIPTION"            => _tr("ORG_name"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:297px","maxlength" =>"100"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "domain"   => array(      "LABEL"                  => _tr("Domain Name"),
                                            "DESCRIPTION"            => _tr("ORG_domainname"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:290px","maxlength" =>"50"),
                                            "VALIDATION_TYPE"        => "domain",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "email_contact"   => array( "LABEL"                  => _tr("Email Contact"),
                                            "DESCRIPTION"            => _tr("ORG_emailcontact"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:297px","maxlength" =>"100"),
                                            "VALIDATION_TYPE"        => "email",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "country"   => array(      "LABEL"                  => _tr("Country"),
                                            "DESCRIPTION"            => _tr("ORG_country"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrCountry,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "ONCHANGE"         => "select_country();"
                                            ),
            "city"   => array(      "LABEL"                  => _tr("City"),
                                            "DESCRIPTION"            => _tr("ORG_city"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:290px","maxlength" =>"100"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "address"   => array(      "LABEL"                  => _tr("Address"),
                                            "DESCRIPTION"            => _tr("ORG_address"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("style" => "width:828px","maxlength" =>"1000"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "country_code" => array(     "LABEL"                  => _tr('Country Code'),
                                        "DESCRIPTION"            => _tr("ORG_countrycode"),
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:297px","maxlength" =>"100"),
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""),
            "area_code"   => array(     "LABEL"                  => _tr('Area Code'),
                                        "DESCRIPTION"            => _tr("ORG_areacode"),
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:290px","maxlength" =>"100"),
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""),
            "quota"   => array(     "LABEL"                  => _tr('Email Quota By User(MB)'),
                                        "DESCRIPTION"            => _tr("ORG_emailquota"),
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:100px","maxlength" =>"100"),
                                        "VALIDATION_TYPE"        => "numeric",
                                        "VALIDATION_EXTRA_PARAM" => ""),
            "max_num_user"   => array(     "LABEL"               => _tr('Max. # of User Accounts'),
                                        "DESCRIPTION"            => _tr("ORG_maxnumaccounts"),
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:100px","maxlength" =>"100"),
                                        "VALIDATION_TYPE"        => "numeric",
                                        "VALIDATION_EXTRA_PARAM" => ""),
            "max_num_exten"       => array( "LABEL"              => _tr('Max. # of extensions'),
                                        "DESCRIPTION"            => _tr("ORG_maxnumextensions"),
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:100px","maxlength" =>"100"),
                                        "VALIDATION_TYPE"        => "numeric",
                                        "VALIDATION_EXTRA_PARAM" => ""),
            "max_num_queues"    => array( "LABEL"              => _tr('Max. # of queues'),
                                        "DESCRIPTION"            => _tr("ORG_masxnumqueues"),
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:100px","maxlength" =>"100"),
                                        "VALIDATION_TYPE"        => "numeric",
                                        "VALIDATION_EXTRA_PARAM" => ""),

            );
    return $arrFields;
}

function createFilterForm($arrState)
{
    $arrFields = array(
            "fname"   => array("LABEL"       => _tr("Name"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "fdomain"   => array("LABEL"     => _tr("Domain"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "fstate"   => array( "LABEL"    => _tr("State"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrState,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "")
                            );
    return $arrFields;
}

function reloadAsterisk($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $pACL = new paloACL($pDB);
    $showMsg=false;
    $continue=false;

    if($credentials['userlevel']=="superadmin"){
        $idOrganization = getParameter("organization_id");
    }else{
        $idOrganization = $credentials['id_organization'];
    }

    if($idOrganization==1){
        return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $query="select domain from organization where id=?";
    $result=$pACL->_DB->getFirstRowQuery($query, false, array($idOrganization));
    if($result===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Asterisk can't be reloaded. ")._tr($pACL->_DB->errMsg));
        $showMsg=true;
    }elseif(count($result)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Asterisk can't be reloaded. "));
        $showMsg=true;
    }else{
        $domain=$result[0];
        $continue=true;
    }

    if($continue){
        $pAstConf=new paloSantoASteriskConfig($pACL->_DB);
        if($pAstConf->generateDialplan($domain)===false){
            $pAstConf->setReloadDialplan($domain,true);
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Asterisk can't be reloaded. ").$pAstConf->errMsg);
            $showMsg=true;
        }else{
            $pAstConf->setReloadDialplan($domain);
            $smarty->assign("mb_title", _tr("MESSAGE"));
            $smarty->assign("mb_message",_tr("Asterisk was reloaded correctly. "));
        }
    }

    return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

//que la organizacion puede ver sus DID asigandos asi como el administrador pueda ver los DID de la
//organizacion o asignarle una
function reportDIDorganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $pORGZ = new paloSantoOrganization($pDB);
    $pDID=new paloDidPBX($pDB);
    $domain=getParameter('domain');
    
    if($credentials['userlevel']!="superadmin"){
        $domain=$credentials['domain'];
    }
    
    if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid domain format"));
        return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $total=$pDID->getTotalDID($domain);
    if($total===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("An error has ocurred to retrieve DID data"));
        return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $limit=20;
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $url['menu']=$module_name;
    $url['domain']=$domain;
    
    $oGrid->setTitle(_tr('DID Organization List'));
    $oGrid->setURL($url);
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    
    if($credentials['userlevel']=="superadmin"){
        $arrColumns[]='';
        $arrColumns[]=_tr("Organization Domain");
    }
    $arrColumns[]=_tr("DID");
    $arrColumns[]=_tr("Type");
    $arrColumns[]=_tr("Country");
    $arrColumns[]=_tr("City");
    $arrColumns[]=_tr("Country Code / Area Code");
    $oGrid->setColumns($arrColumns);
    
    $arrData=array();
    $arrDID=$pDID->getDIDs($domain,null,null,null,$limit,$offset);
    
    if($arrDID===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("An error has ocurred to retrieve DID data"));
        return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        //si es un usuario solo se ve su didsion
        //si es un administrador ve todas las didsiones
        foreach($arrDID as $did) {
            $arrTmp=array();
            if($credentials["userlevel"]=="superadmin"){
                $arrTmp[] = "<input type='checkbox' name='dids[]' value='{$did['id']}' />";
                $arrTmp[] = $did["organization_domain"];
            }
            $arrTmp[] = $did['did'];
            $arrTmp[] = $did["type"];
            $arrTmp[] = $did["country"];
            $arrTmp[] = $did["city"];
            $arrTmp[] = $did["country_code"]." / ".$did["area_code"];
            $arrData[]=$arrTmp;
        }
    }
    
    if($credentials['userlevel']=="superadmin"){
        $oGrid->addNew("assignDIDs",_tr("Add DID"));
        $oGrid->deleteList(_tr('Are you sure you wish REMOVE this DID from organization'),'removeDID',"Remove DID");
    }
    
    $content = $oGrid->fetchGrid(array(),$arrData);
    return $content;
}

function didAssign($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $pDID=new paloDidPBX($pDB);
    $domain=getParameter('domain');
    $prop['country']=getParameter('country');
    $prop['city']=getParameter('city');
    
    if($credentials['userlevel']!="superadmin"){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("You are not authorized to perform this action"));
        return reportDIDorganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    //validamos que sea un dominio valido
    if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid domain format"));
        return reportDIDorganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    if(getParameter('save_did')){
        //procedemos a guardar los cambios
        $selectDID=getParameter("listDIDOrg");
        if(!empty($selectDID)){
            $listDIDOrg=explode(",",$selectDID);
            if($pDID->assignDIDs($listDIDOrg,$domain)){
                $smarty->assign("mb_title", _tr("Message"));
                if(writeDHADIDidFile($error)){
                    $smarty->assign("mb_message",_tr("DID was assigned successfully"));
                }else{
                    $smarty->assign("mb_message",_tr("DID was assigned").$error);
                }
            }else{
                $smarty->assign("mb_title", _tr("ERROR"));
                $smarty->assign("mb_message",$pDID->errMsg);
            }
            return reportDIDorganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }else{
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("You must select at least one DID"));
        }
    }else{
        //obtenemos la lista de los DID filtrado por el dominio
        $listDID=$pDID->getDIDFree(array('country'=>null,'city'=>null));
        if($listDID===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("An error has ocurred to retrieve DID data"));
            return reportDIDorganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
        
        $country=array("0"=>_tr("--Select a Country--"));
        $city=array("0"=>_tr("--Select a City--"));
        $arrDID=$listDIDOrg=array();
        foreach($listDID as $value){
            $arrDID[]=array('id'=>$value['id'],'did'=>$value['did'],'country_code'=>$value['country_code'],'area_code'=>$value['area_code']);
            $country[$value['country']]=$value['country'];
            $city[$value['city']]=$value['city'];
        }   
    }
    
    global $arrPermission;
    if(in_array('edit_DID',$arrPermission)){
        $smarty->assign('EDIT_DID',TRUE);
    }
    
    $smarty->assign("SEARCH","<input name='search_did' type='button' class='button' onclick='filer_did()' value='"._tr('Search')."'>");
    $smarty->assign("DID_FREE", $arrDID);
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("DIDLIST_LABEL",_tr('Available DID'));
    $smarty->assign("DIDORG_LABEL",_tr('DID to assign to')." ".$domain);
    $smarty->assign("LEYENDDRAG",_tr("Drag and Drop DIDs into 'DID to assign' Area"));
    $smarty->assign("listDIDOrg",'');
    $smarty->assign("domain",$domain);
    
    $arrForm = createDidForn($country,$city);
    $oForm = new paloForm($smarty,$arrForm);

    $htmlForm = $oForm->fetchForm("$local_templates_dir/organization_did.tpl",_tr("Organization DID"), $prop);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    return $content;
}

function changeDIDfilter($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $jsonObject = new PaloSantoJSON();
    $pDID=new paloDidPBX($pDB);
    $prop["country"]=getParameter("country");
    $prop["city"]=getParameter("city");
    
    $listDID=$pDID->getDIDFree($prop);
    if($listDID===false){
        $jsonObject->set_error(_tr("An error has ocurred to retrieve DID data"));
    }else{
        $arrDID=array();
        if(count($listDID)>0){
            //debemos quitar de la lista de did  aquellos que ya han sido seleccionados
            $selectDID=getParameter("listDIDOrg");
            if(!empty($selectDID)){
                $listDIDOrg=explode(",",$selectDID);
                foreach($listDID as $value){
                    if(!in_array($value['id'],$listDIDOrg))
                        $arrDID[]=array('id'=>$value['id'],'did'=>$value['did'],'country_code'=>$value['country_code'],'area_code'=>$value['area_code']);
                }
            }else{
                foreach($listDID as $value){
                    $arrDID[]=array('id'=>$value['id'],'did'=>$value['did'],'country_code'=>$value['country_code'],'area_code'=>$value['area_code']);
                }
            }
        }
        $jsonObject->set_message($arrDID);
    }
    return $jsonObject->createJSON();
}

function removeDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $pORGZ = new paloSantoOrganization($pDB);
    $pDID=new paloDidPBX($pDB);
    $domain=getParameter('domain');
    
    if($credentials['userlevel']!="superadmin"){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("You are not authorized to perform this action"));
        return reportOrganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $listDID = $_POST["dids"];
    $arrDIDs=array();
    if(is_array($listDID)){
        foreach($listDID as $value){
            if(preg_match('/^[[:digit:]]+$/', $value)){
                $arrDIDs[]=$value;
            }
        }
    }
    
    if(count($arrDIDs)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("You must select at least one item."));
        return reportDIDorganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $error="";
    $pDB->beginTransaction();
    if($pDID->removeAsignation($arrDIDs,$domain)){
        $pDB->commit();
        $smarty->assign("mb_title", _tr("Message"));
        if(writeDHADIDidFile($error)){
            $smarty->assign("mb_message",_tr("DID was removed successfully"));
        }else{
            $smarty->assign("mb_message",_tr("DID was removed").$error);
        }
        return reportDIDorganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $pDB->rollBack();
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Changes couldn't be applied."));
        return reportDIDorganization($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
}

function writeDHADIDidFile(&$error){
    $sComando = '/usr/bin/elastix-helper asteriskconfig createFileDahdiChannelAdd 2>&1';
    $output = $ret = NULL;
    exec($sComando, $output, $ret);
    if ($ret != 0) {
        $error = _tr("Error writing did file").implode('', $output);
        return FALSE;
    }
    
    return true;
}

function createDidForn($country,$city){
    $arrFormElements = array("country"   => array("LABEL"              => _tr("Country"),
                                              "REQUIRED"               => "yes",
                                              "INPUT_TYPE"             => "SELECT",
                                              "INPUT_EXTRA_PARAM"      => $country,
                                              "VALIDATION_TYPE"        => "numeric",
                                              "VALIDATION_EXTRA_PARAM" => ""),
                            "city"   => array("LABEL"                  => _tr("City"),
                                              "REQUIRED"               => "yes",
                                              "INPUT_TYPE"             => "SELECT",
                                              "INPUT_EXTRA_PARAM"      => $city,
                                              "VALIDATION_TYPE"        => "numeric",
                                              "VALIDATION_EXTRA_PARAM" => "")
                            );
    return $arrFormElements;
}

function getAction()
{
    global $arrPermission;
    if(getParameter("new_organization")){
        //preguntar si el usuario puede hacer accion
        return (in_array('create_org',$arrPermission))?'new_organization':'report';
    }else if(getParameter("save_new")){ //Get parameter by POST (submit)
        //preguntar si el usuario puede hacer accion
        return (in_array('create_org',$arrPermission))?'save_new':'report';
    }else if(getParameter("save_edit")){
        //preguntar si el usuario puede hacer accion
        return (in_array('edit_org',$arrPermission))?'save_edit':'report';
    }else if(getParameter("edit")){
        //preguntar si el usuario puede hacer accion
        return (in_array('edit_org',$arrPermission))?'edit':'report';
    }else if(getParameter("delete")){ 
        //preguntar si el usuario puede hacer accion
        return (in_array('delete_org',$arrPermission))?'delete':'report';
    }else if(getParameter("action")=="view"){      //Get parameter by GET (command pattern, links)
        return "view";
    }else if(getParameter("action")=="get_country_code"){
        return "get_country_code";
    }else if(getParameter("assignDIDs")){
        //preguntar si el usuario puede hacer accion
        return (in_array('edit_DID',$arrPermission))?'didAssign':'report';
    }else if(getParameter("removeDID")){
        //preguntar si el usuario puede hacer accion
        return (in_array('edit_DID',$arrPermission))?'didAssign':'report';
    }else if(getParameter("action")=="changeDIDfilter"){
        return "changeDIDfilter";
    }else if(getParameter("action")=="reportDIDs"){
        //preguntar si el usuario puede hacer accion
        return (in_array('access_DID',$arrPermission))?'reportDIDs':'report';
    }else if(getParameter("save_did")){
        //preguntar si el usuario puede hacer accion
        return (in_array('edit_DID',$arrPermission))?'saveDidAssign':'report';
    }else if(getParameter("action")=="reloadAsterisk"){
        return "reloadAsterisk";
    }else if(getParameter("action")=="change_org_state"){
        //preguntar si el usuario puede hacer accion
        return (in_array('delete_org',$arrPermission))?'change_state':'report';
    }else if(getParameter("action")=="delete_org_2"){
        //preguntar si el usuario puede hacer accion
        return (in_array('delete_org',$arrPermission))?'delete_org_2':'report';
    }else
        return "report"; //cancel
}
?>
