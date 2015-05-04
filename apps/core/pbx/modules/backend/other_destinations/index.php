<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 3.0.0                                                |
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
  $Id: index.php,v 1.1 2014-03-12 Bruno Macias bmacias@elastix.org Exp $ 
  $Id: index.php,v 1.1 2014-04-07 Armando Chuto achuto@elastix.org Exp $*/
include_once "libs/paloSantoJSON.class.php";
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoOrganization.class.php";


   
function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    
     //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);

    //user credentials
    global $arrCredentials;
        
    $action = getAction();
    $content = "";
    
    switch($action){
        case "new":
            $content = viewFormOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_edit":
            $content = viewFormOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveNewOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "reloadAasterisk":
            $content = reloadAasterisk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default: // report
            $content = reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function reportOtherDestinations($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error = "";
    $pORGZ = new paloSantoOrganization($pDB);

    $domain=getParameter("organization");
    $domain=empty($domain)?'all':$domain;
    if($credentials['userlevel']!="superadmin"){
        $domain=$credentials['domain'];
    }
    $other_destination_name=getParameter("other_destination_name");
    
    $pOtherDestinations = new paloSantoOtherDestinations($pDB,$domain);
  
    $url['menu']                  = $module_name;
    $url['organization']          = $domain;
    $url['other_destination_name']= $other_destination_name;
    
    $total=$pOtherDestinations->getNumOtherDestinations($domain,$other_destination_name);
    $arrOrgz=array();
    if($credentials['userlevel']=="superadmin"){
        $arrOrgz=array("all"=>_tr("all"));
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }
    
    if($total===false){
        $error=$pOtherDestinations->errMsg;
        $total=0;
    }

    $limit=20;

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();

    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    
    $oGrid->setTitle(_tr('Other Destinations List'));
    //$oGrid->setIcon('url de la imagen');
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    $oGrid->setURL($url);

    $arrColum=array(); 
    if($credentials['userlevel']=="superadmin"){
        $arrColum[]=_tr("Organization");
    }
    $arrColum[]=_tr("Other Destination Name");
    $arrColum[]=_tr("Dial Destination");
    $oGrid->setColumns($arrColum);

    $arrOtherDestinations=array();
    $arrData = array();
    if($total!=0){
        $arrOtherDestinations = $pOtherDestinations->getOtherDestinations($domain,$other_destination_name,$limit,$offset);
    }

    if($arrOtherDestinations===false){
        $error=_tr("Error to obtain Other Destinations").$pOtherDestinations->errMsg;
        $arrOtherDestinations=array();
    }

    foreach($arrOtherDestinations as $row) {
        $arrTmp=array();
        if($credentials['userlevel']=="superadmin"){
            $arrTmp[] = $arrOrgz[$row["organization_domain"]];
        }
        $arrTmp[] = "&nbsp;<a href='?menu=$module_name&action=view&id=".$row['id']."&organization={$row['organization_domain']}'>".htmlentities($row["description"],ENT_QUOTES,"UTF-8")."</a>";
        $arrTmp[] = $row["destdial"];
        $arrData[] = $arrTmp;
    }
            
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='report'>");
    if($pORGZ->getNumOrganization(array()) >= 1){
        if(in_array('create',$arrPermission)){
            if($credentials['userlevel']=='superadmin'){
                $oGrid->addComboAction("organization_add",_tr("ADD Other Destination"), array_slice($arrOrgz,1), $selected=null, "create_other_destination", $onchange_select=null);
            }else{
                $oGrid->addNew("create_other_destination",_tr("ADD Other Destination"));
            }   
        }
        if($credentials['userlevel']=='superadmin'){
            $_POST["organization"]=$domain;
            $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true);
        }
        $_POST["other_destination_name"]=$other_destination_name; // name
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Other Destination Name")." = ".$other_destination_name, $_POST, array("other_destination_name" => "")); 
        $arrFormElements = createFieldFilter($arrOrgz);
        $oFilterForm = new paloForm($smarty, $arrFormElements);
        $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
        $oGrid->showFilter(trim($htmlFilter));
    }else{
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("It's necesary you create at least one organization so you can use this module"));
    }

    if($error!=""){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",$error);
    }
    $contenidoModulo = $oGrid->fetchGrid(array(), $arrData);
    $mensaje=showMessageReload($module_name, $pDB, $credentials);
    $contenidoModulo = $mensaje.$contenidoModulo;
    return $contenidoModulo;
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

function viewFormOtherDestinations($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    global $arrPermission;
    $error = "";
    
    $arrOtherDestinations = array();
    $action          = getParameter("action");    
    $idOtherDestinations  = getParameter("id");
    
    if($action=="view" || $action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        if(!isset($idOtherDestinations)){
            $error=_tr("Invalid Other Destination ID");
        }else{
            $domain=getParameter('organization');
            if($credentials['userlevel']!='superadmin'){
                $domain=$credentials['domain'];
            }
            $pOtherDestinations = new paloSantoOtherDestinations($pDB,$domain);
            $arrOtherDestinations = $pOtherDestinations->getOtherDestinationsById($idOtherDestinations);
            if($arrOtherDestinations===false){
                $error=_tr($pOtherDestinations->errMsg);
            }else if(count($arrOtherDestinations)==0){
                $error=_tr("Other Destination doesn't exist");
            }else{
                if(getParameter("save_edit"))
                    $arrOtherDestinations=$_POST;           
            }
        }
        
        if($error!=""){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",$error);
            return reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{ // new, create
        if($credentials['userlevel']=='superadmin'){
            if(getParameter("create_other_destination")){
                $domain=getParameter('organization_add'); //este parametro solo es selecionable cuando es el superadmin quien crea la ruta
            }else
                $domain=getParameter('organization');
        }else{
            $domain=$credentials['domain'];
        }
    
        $pOtherDestinations = new paloSantoOtherDestinations($pDB,$domain);
        if(getParameter("create_other_destination")){
            $arrOtherDestinations["destdial"]="";
            $arrOtherDestinations["description"]="";
        }else
            $arrOtherDestinations=$_POST; 
    }
            
    $arrFormOrgz = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormOrgz);

    if($action=="view"){
        $oForm->setViewMode();
    }else if($action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
    }
    
    //permission
    $arrAD = $pOtherDestinations->getAdditionalsDestinations();
    $smarty->assign("arrAditionalsDestinations",$arrAD);
    $smarty->assign("EDIT_OD",in_array('edit',$arrPermission));
    $smarty->assign("CREATE_OD",in_array('create',$arrPermission));
    $smarty->assign("DEL_OD",in_array('delete',$arrPermission));
    $smarty->assign("FeatureCodes",_tr("Feature Codes"));
    $smarty->assign("ShortcutApps",_tr("Shortcut Apps"));
            
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("OPTIONS", _tr("Options"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("id", $idOtherDestinations);
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("ORGANIZATION_LABEL",_tr("Organization Domain"));
    $smarty->assign("ORGANIZATION",$domain);
            
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("Other Destinations"), $arrOtherDestinations);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
} 

function saveNewOtherDestinations($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $error = "";
    $success=false;

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    $pOtherDestinations = new paloSantoOtherDestinations($pDB,$domain);
    
    $arrFormOrgz = createFieldForm();
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
        return viewFormOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    else{
        $description = trim(getParameter("description"));
        if (count(explode("\n", $description)) > 1) {
            $error = _tr("Invalid description");
            $continue = false;
        } elseif (!preg_match('/^[[:digit:]\*#]+$/', getParameter("destdial"))) {
            $error = _tr("Invalid dial destination");
            $continue = false;
        } else {
            //seteamos un arreglo con los parametros configurados
            $arrProp=array();
            $arrProp["description"]  = $description;
            $arrProp["destdial"]     = getParameter("destdial");
    
            $pDB->beginTransaction();
            $success=$pOtherDestinations->createNewOtherDestinations($arrProp);
            if($success)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pOtherDestinations->errMsg;
        }
    }

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Other Destination has been created successfully"));
         //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function saveEditOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $success=false;
    $idOtherDestinations=getParameter("id");
 
    //obtenemos la informacion del ring_group por el id dado, sino existe el ring_group mostramos un mensaje de error
    if(!isset($idOtherDestinations)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Other Destination ID"));
        return reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $pOtherDestinations = new paloSantoOtherDestinations($pDB,$domain);
    $arrOtherDestinations = $pOtherDestinations->getOtherDestinationsById($idOtherDestinations);
    if($arrOtherDestinations===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pOtherDestinations->errMsg));
        return reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else if(count($arrOtherDestinations)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Other Destination doesn't exist"));
        return reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $description = trim(getParameter("description"));
        if (count(explode("\n", $description)) > 1) {
            $error = _tr("Invalid description");
            $continue = false;
        } elseif (!preg_match('/^[[:digit:]\*#]+$/', getParameter("destdial"))) {
            $error = _tr("Invalid dial destination");
            $continue = false;
        } else {
            //seteamos un arreglo con los parametros configurados
            $arrProp=array();
            $arrProp["id"]           = $idOtherDestinations;
            $arrProp["description"]  = $description;
            $arrProp["destdial"]     = getParameter("destdial");                        
    
            $pDB->beginTransaction();
            $success=$pOtherDestinations->updateOtherDestinationsPBX($arrProp);
    
            if($success)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pOtherDestinations->errMsg;
        }
    }

    $smarty->assign("id", $idOtherDestinations);

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Other Destination has been edited successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function deleteOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    
    $error = "";
    $success=false;
    $idOtherDestinations=getParameter("id");

    if(!isset($idOtherDestinations)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Other Destination ID"));
        return reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $pOtherDestinations = new paloSantoOtherDestinations($pDB,$domain);
    $arrOtherDestinations = $pOtherDestinations->getOtherDestinationsById($idOtherDestinations);
    if($arrOtherDestinations===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pOtherDestinations->errMsg));
        return reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else if(count($arrOtherDestinations)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("OtherDestinations doesn't exist"));
        return reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    

    $pDB->beginTransaction();
    $success = $pOtherDestinations->deleteOtherDestinations($idOtherDestinations);
    if($success)
        $pDB->commit();
    else
        $pDB->rollBack();
    $error .=$pOtherDestinations->errMsg;

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Other Destination was deleted successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($error));
    }

    return reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function createFieldForm()
//function createFieldForm($destination,$pDB,$domain)
{
    $arrFormElements = array("description"   => array("LABEL"                => _tr('Description'), //field1
                                                    "DESCRIPTION"            => _tr("Name"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "destdial"    => array("LABEL"                  => _tr("Dial Destination"), //field2
                                                    "DESCRIPTION"            => _tr("Dial Destination"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
    );
    
    return $arrFormElements;
}



function createFieldFilter($arrOrgz)
{
    $arrFields = array(
        "organization"  => array("LABEL"         => _tr("Organization"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "SELECT",
                        "INPUT_EXTRA_PARAM"      => $arrOrgz,
                        "VALIDATION_TYPE"        => "domain",
                        "VALIDATION_EXTRA_PARAM" => ""),        
        "other_destination_name"  => array("LABEL"            => _tr("Other Destination Name"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "TEXT",
                        "INPUT_EXTRA_PARAM"      => "",
                        "VALIDATION_TYPE"        => "text",
                        "VALIDATION_EXTRA_PARAM" => ""),
        );
    return $arrFields;
}


function reloadAasterisk($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $showMsg=false;
    $continue=false;

    /*if($arrCredentiasls['userlevel']=="other"){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("You are not authorized to perform this action"));
    }*/

    $idOrganization=$credentials['id_organization'];
    if($credentials['userlevel']=="superadmin"){
        $idOrganization = getParameter("organization_id");
    }

    if($idOrganization==1){
        return reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
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
            $smarty->assign("mb_message",_tr("Asterisk can't be reloaded. ").$pAstConf->errMsg);
            $showMsg=true;
        }else{
            $pAstConf->setReloadDialplan($domain);
            $smarty->assign("mb_title", _tr("MESSAGE"));
            $smarty->assign("mb_message",_tr("Asterisk was reloaded correctly. "));
        }
    }

    return reportOtherDestinations($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function getAction(){
    global $arrPermission;
    if(getParameter("create_other_destination"))
        return (in_array('create',$arrPermission))?'new':'report';
    else if(getParameter("save_new")) //Get parameter by POST (submit)
        return (in_array('create',$arrPermission))?'save_new':'report';
    else if(getParameter("save_edit"))
        return (in_array('edit',$arrPermission))?'save_edit':'report';
    else if(getParameter("edit"))
        return (in_array('edit',$arrPermission))?'view_edit':'report';
    else if(getParameter("delete"))
        return (in_array('delete',$arrPermission))?'delete':'report';
    else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view";
    else if(getParameter("action")=="reloadAsterisk")
        return "reloadAasterisk";
    else
        return "report"; //cancel
}
?>
