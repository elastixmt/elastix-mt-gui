<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.5.2                                                |
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
  $Id: index.php,v 1.1 2009-05-06 04:05:41 Jonathan Vega jvega112@gmail.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoJSON.class.php";

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    //include module files
    include_once "libs/paloSantoOrganization.class.php";   
    
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);
    
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
    
    //user credentials
    global $arrCredentials;

    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        case "apply":
            $content = applyGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default:
            $content = reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$arrCredentials);
            break;
    }
    return $content;
}

function applyGroupPermission($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrLang;

    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $filter_resource = getParameter("resource_apply");
    $limit = getParameter("limit_apply");
    $offset = getParameter("offset_apply");
    $idGroup = getParameter("filter_group");

    if($credentials['userlevel']=="superadmin"){
        $idOrgFil=getParameter("idOrganization");
        if(empty($idOrgFil)){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Invalid Organization"));
            return reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{
        $idOrgFil=$credentials['id_organization'];
    }

    if(empty($idGroup)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Group"));
        return reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    //valido exista una organizacion con dicho id y que no sea la organizacion 1
    $orgTmp=$pORGZ->getOrganizationById($idOrgFil);
    if($orgTmp===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pORGZ->errMsg));
        return reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }elseif(count($orgTmp)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Organization doesn't exist"));
        return reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    if($idOrgFil==1){
        $error=true;
        $msg_error=_tr("Invalid Organization");
    }

    //valido que el grupo pertenezca a la organizacion
    if($pACL->getGroups($idGroup,$idOrgFil)==false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Group"));
        return reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $lang = get_language();
    if($lang != "en"){
        if(isset($filter_resource)){
            if(trim($filter_resource)!=""){
                global $arrLang;
                $filter_value = strtolower(trim($filter_resource));
                $parameter_to_find[]=$filter_value; //parametro de busqueda sin traduccion
                foreach($arrLang as $key=>$value){
                    $langValue=strtolower(trim($value));
                    if(preg_match("/^[[:alnum:]| ]*$/",$filter_value))
                        if(strpos($langValue, $filter_value) !== FALSE)
                            $parameter_to_find[] = $key;
                }
            }
        }
    }

    if(isset($filter_resource)){
        $parameter_to_find[] = $filter_resource;
    }else{
        $parameter_to_find=null;
    }

    //obtenemos los recursos a los que la organizacion tiene acceso
    $arrResourcesOrg = $pACL->getResourcesByOrg($idOrgFil,  $parameter_to_find);
    if($arrResourcesOrg===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pACL->errMsg));
        return reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $arrResources=array_slice($arrResourcesOrg,$offset,$limit);
    foreach($arrResources as $resource){
        $listResource[]=$resource['id']; //lista de id de los recursos que queremos consultar
    }
    
    //el grupo administrator de cada organizacion tiene ciertos recursos siempre activos
    $isAdministrator = ($pACL->getGroupNameByid($idGroup) == _tr("administrator")) ? true :false;
    if( $isAdministrator ){
        $listResource[] = "grouplist";
        $listResource[] = "userlist";
        $listResource[] = "group_permission";
    }
    
    //las acciones que tiene cada drecurso
    $arrResourceActions=$pACL->getResourcesActions($listResource);
    if($arrResourceActions===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("An error has ocurred to retrieved Resources Actions"));
        return reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    //para el casos de los recursos organization, dashboard, cdrreport ahi acciones que no se les puede otorgar a los usuarios
    if(isset($arrResourceActions['organization'])){
        $arrResourceActions['organization']=array_diff($arrResourceActions['organization'],array('change_org_status','create_org','delete_org','edit_DID'));
    }
    if(isset($arrResourceActions['dashboard'])){
        $arrResourceActions['dashboard']=array('access');
    }
    if(isset($arrResourceActions['cdrreport'])){
        $arrResourceActions['cdrreport']=array('access',_tr('export'));
    }
    
    //los premisos que tiene el grupo
    $arrPermisos = $pACL->loadGroupPermissions($idGroup,$listResource);
    if($arrPermisos===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("An error has ocurred to retrieved Group Permissions"));
        return reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $arrNewPermissions=array();
    $arrDelPermissions=array();
    $arrSelectdPermissions=array();
    if(isset($_POST['groupPermission'])){
        foreach($_POST['groupPermission'] as $resource => $actions){
            if(isset($arrResourceActions[$resource])){
                $res_actions=array_intersect(array_keys($actions),$arrResourceActions[$resource]);
                if(in_array('access',$res_actions)){
                    $arrSelectdPermissions[$resource]=$res_actions;
                }
            }
        }
    }
    
     if( $isAdministrator ){
        if(isset($arrResourceActions['grouplist']))
            $arrSelectdPermissions["grouplist"]=$arrResourceActions['grouplist'];
        if(isset($arrResourceActions['userlist']))
            $arrSelectdPermissions["userlist"]=$arrResourceActions['userlist'];
        if(isset($arrResourceActions['group_permission']))
            $arrSelectdPermissions["group_permission"]=$arrResourceActions['group_permission'];
    }
    
    //sacamos la lista de los permisos nuevos
    foreach($arrSelectdPermissions as $resource => $actions){
        if(isset($arrPermisos[$resource])){
            $new_actions=array_diff($actions,$arrPermisos[$resource]);
            if(count($new_actions)>0){
                $arrNewPermissions[$resource]=$new_actions;
            }
        }else{
            //no se hallaba antes lo agregamos a la lista de recursos nuevos
            $arrNewPermissions[$resource]=$actions;
        }
    }
    
    //sacamos la lista de los recursos ausentes
    foreach($arrPermisos as $resource => $actions){
        if(isset($arrSelectdPermissions[$resource])){
            $del_actions=array_diff($actions,$arrSelectdPermissions[$resource]);
            if(count($del_actions)>0){
                $arrDelPermissions[$resource]=$del_actions;
            }
        }else{
            //no se halla entre los recursos seleccionados lo agregamos a la lista de recursos ausentes
            $arrDelPermissions[$resource]=$actions;
        }
    }

    $pACL->_DB->beginTransaction();
    if( count($arrDelPermissions) > 0 ){
        if(!$pACL->deleteGroupPermission($idGroup, $arrDelPermissions)){
            $smarty->assign("mb_title", "ERROR");
            $smarty->assign("mb_message",_tr("A error has been ocurred. ").$pACL->errMsg);
            return reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }

    if( count($arrNewPermissions) > 0 ){
        if(!$pACL->saveGroupPermission($idGroup, $arrNewPermissions)){
            $smarty->assign("mb_title", "ERROR");
            $smarty->assign("mb_message",_tr("A error has been ocurred. ").$pACL->errMsg);
            return reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }

    $smarty->assign("mb_title", _tr("MESSAGE"));
    $smarty->assign("mb_message",_tr("Changes was applied successfully"));
    $pACL->_DB->commit();

    //borra los menus q tiene de permisos que estan guardados en la session, el index.php principal (html) volvera a generar esta arreglo de permisos.
    unset($_SESSION['elastix_user_permission']);
    return reportGroupPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function reportGroupPermission($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $arrGroups=array();
    $arrOrgz=array();
    $idOrgFil=getParameter("idOrganization");
    
    if($credentials['userlevel']=="superadmin"){
        $orgTmp=$pORGZ->getOrganization(array());
        if($orgTmp===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr($pORGZ->errMsg));
        }elseif(count($orgTmp)==0){
            $smarty->assign("mb_title", _tr("MESSAGE"));
            $msg=_tr("You haven't created any organization");
            $smarty->assign("mb_message",$msg);
        }else{
            //si el usuario a selecionado una organizacion comprobamos que esta exista
            //caso contrario procedemos a sellecionar la primera disponible
            $flag=false;
            foreach($orgTmp as $value){
                $arrOrgz[$value["id"]]=$value["name"];
                if($value["id"]==$idOrgFil)
                    $flag=true;
            }
            if(!$flag)
                $idOrgFil=$orgTmp[0]['id'];
        }
    }else{
        $idOrgFil=$credentials['id_organization'];
        $orgTmp=$pORGZ->getOrganizationById($idOrgFil);
        if($orgTmp==false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("An error has ocurred to retrieved organization data"));
        }else{
            $arrOrgz=$orgTmp;
        }
    }
    
    
    if(count($arrOrgz)>0){ //que se un arreglo y que tenga al menos una organizacion
        $groupTmp = $pACL->getGroupsPaging(null,null,$idOrgFil);
        if($groupTmp===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr($pACL->errMsg));
        }else{
            foreach($groupTmp as $value){
                $arrGroups[$value[0]]=$value[1];
            }
        }
    }

    $filter_group = getParameter("filter_group");
    if(count($arrGroups)>0){
        if(empty($filter_group)){
            //seleccionamos el primer grupo de la lista de grupos
            $filter_group=$groupTmp[0][0];
        }
    
        //valido que el grupo pertenzca a la organizacion
        if($pACL->getGroups($filter_group,$idOrgFil)==false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Invalid Group"));
            $filter_group=$groupTmp[0][0];
        }
    }
    
    $filter_resource = getParameter("filter_resource");
    $lang = get_language();
    if($lang != "en"){
        if(isset($filter_resource)){
            if(trim($filter_resource)!=""){
                global $arrLang;
                $filter_value = strtolower(trim($filter_resource));
                $parameter_to_find[]=$filter_value; //parametro de busqueda sin traduccion
                foreach($arrLang as $key=>$value){
                    $langValue=strtolower(trim($value));
                    if(preg_match("/^[[:alnum:]| ]*$/",$filter_value))
                        if(strpos($langValue, $filter_value) !== FALSE)
                            $parameter_to_find[] = $key;
                }
            }
        }
    }

    if(isset($filter_resource)){
        $parameter_to_find[] = $filter_resource;
    }else{
        $parameter_to_find=null;
    }
    
    $totalGroupPermission=0;
    if(count($arrGroups)>0){
        $arrResourceOrg=$pACL->getResourcesByOrg($idOrgFil, $parameter_to_find);
        if($arrResourceOrg===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("An error has ocurred to retrieved Resources"));
        }else
            $totalGroupPermission = count($arrResourceOrg);
    }

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);

    $limit  = 25;
    $total  = $totalGroupPermission;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $url['menu']=$module_name;
    $url['idOrganization']=$idOrgFil;
    $url['filter_group']=$filter_group;
    $url['filter_resource']=$filter_resource;
    
    $arrData = $arrResourceActions = $arrPermisos = array();
    $error=false;
    if(count($arrGroups)>0 && $totalGroupPermission>0){
        $arrResource = array_slice($arrResourceOrg,$offset,$limit);
        $idGroup = $filter_group;
        
        foreach($arrResource as $resource){
            $listResource[]=$resource['id']; //lista de id de los recursos que queremos consulta
            $listResDes[$resource['id']]=$resource['description'];
        }
        
        //las acciones que tiene cada drecurso
        $arrResourceActions=$pACL->getResourcesActions($listResource);
        if($arrResourceActions===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("An error has ocurred to retrieved Resources Actions"));
            $error=true;
        }
        
        //los premisos que tiene el grupo
        $arrPermisos = $pACL->loadGroupPermissions($idGroup,$listResource);
        if($arrPermisos===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("An error has ocurred to retrieved Group Permissions"));
            $error=true;
        }
    }

    $max_actions = 0;
    $isAdministrator = ($pACL->getGroupNameByid($idGroup) == _tr("administrator")) ? true :false;
    if($totalGroupPermission>0 && !$error){
        foreach($arrResourceActions as $resource => $actions){
            $arrTmp=array();
            $arrTmp[] = _tr($listResDes[$resource]);
            $disabled = "";
            if( $isAdministrator && ( $resource == 'grouplist' || $resource == 'userlist'  || $resource == 'group_permission')){
                $disabled = "disabled='disabled'";
            }
            
            //dentro del modulo organizacion ahi acciones que unicamente las puede realizar el superadmin
            //por lo tando no deben aparecer listadas
            if($resource=="organization"){
                $actions=array_diff($actions,array('change_org_status','create_org','delete_org','edit_DID'));
            }elseif($resource=="dashboard"){
                $actions=array('access');
            }elseif($resource=='cdrreport'){
                $actions=array('access','export');
            }
            
            if(count($actions)>$max_actions){
                $max_actions=count($actions);
            }
            
            $desactivar=false;
            if(isset($arrPermisos[$resource])){ //grupo no tiene nigun permiso
                if(!in_array('access',$arrPermisos[$resource])){
                    $desactivar=true;
                }
            }else{
                $desactivar=true;
                $arrPermisos[$resource]=array();
            }
                
            foreach($actions as $action){
                $class='other_act';
                if($action=='access')
                    $class='access_act';
                elseif($desactivar){
                    $disabled = "disabled='disabled'";
                }
                
                $checked0 = '';
                //chequeamos si la accion se encuentra en la lista de acciones permitidas en el recurso
                if(in_array($action,$arrPermisos[$resource])){ 
                    $checked0 = "checked";
                }
                $arrTmp[] = "<input type='checkbox' class='$class' $disabled name='groupPermission[".$resource."][$action]' $checked0> $action";
            }
            $arrData[] = $arrTmp;
        }
    }
    
     
    $oGrid->setTitle(_tr("Group Permission"));
    $oGrid->setURL($url);
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    $arrColumn[]=_tr("Resource");
    for($i=1;$i<=$max_actions;$i++){
        $act= _tr("Action");
        $arrColumn[]="$act"." $i";
    }
    $oGrid->setColumns($arrColumn);
    
    //begin section filter
    $arrFormFilter = createFieldFilter($arrGroups);
    $oFilterForm = new paloForm($smarty, $arrFormFilter);

    $smarty->assign("SHOW", _tr("Show"));
    $smarty->assign("limit_apply", htmlspecialchars($limit, ENT_COMPAT, 'UTF-8'));
    $smarty->assign("offset_apply", htmlspecialchars($offset, ENT_COMPAT, 'UTF-8'));
    $smarty->assign("resource_apply", htmlentities($filter_resource));

    $_POST["filter_group"] = htmlspecialchars($filter_group, ENT_COMPAT, 'UTF-8');
    $_POST["filter_resource"] = htmlspecialchars($filter_resource, ENT_COMPAT, 'UTF-8');
    $_POST["idOrganization"] = $idOrgFil;

    if(count($arrOrgz)>0){
        global $arrPermission;
        if(in_array('edit_permission',$arrPermission)){
            $oGrid->addSubmitAction("apply",_tr("Save"));
        }
        if($credentials['userlevel']=="superadmin"){
            $oGrid->addComboAction("idOrganization",_tr("Organization"),$arrOrgz,$idOrgFil,"report");
        }
        $nameGroup=isset($arrGroups[$filter_group])?$arrGroups[$filter_group]:"";
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Group")." = $nameGroup", $_POST, array("filter_group" => $groupTmp[0][0]),true);
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Resource")." = $filter_resource", $_POST, array("filter_resource" =>""));
        $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
        $oGrid->showFilter(trim($htmlFilter));
    }else{
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("You haven't created any organization"));
    }

    $contenidoModulo = $oGrid->fetchGrid(array(), $arrData);
    //end grid parameters

    return $contenidoModulo;
}


function createFieldFilter($arrGrupos)
{
    $arrFormElements = array(
            "filter_group" => array(    "LABEL"                  => _tr("Group"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $arrGrupos,
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""),
            "filter_resource" => array( "LABEL"                  => _tr("Resource"),
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
    if(getParameter("apply")) //Get parameter by POST (submit)
        return (in_array('edit_permission',$arrPermission))?'apply':'report';
    else
        return "report";
}
?>
