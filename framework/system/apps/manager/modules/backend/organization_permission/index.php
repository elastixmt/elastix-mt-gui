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

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);

    //user credentials
    global $arrCredentials;
       
    //solo el susperadmin puede acceder a este modulo
    if($arrCredentials["userlevel"]!="superadmin"){
        header("Location: index.php");
    }
    
    //actions
    $accion = getAction();
    $content = "";

    switch($accion){
        case "apply":
            $content = applyOrgPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "getSelected":
            $content = getSelected($pDB, $arrCredentials["userlevel"]);
            break;
        default:
            $content = reportOrgPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
    }
    return $content;
}

function applyOrgPermission($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf,$arrCredentiasls)
{
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $arrGroups=array();
    $arrOrgz=array();
    $idOrgFil=getParameter("idOrganization");
    $filter_resource = getParameter("resource_apply");
    $error=false;

    $orgTmp=$pORGZ->getOrganizationById($idOrgFil);
    //valido exista una organizacion con dicho id
    if($orgTmp===false){
        $error=true;
        $msg_error=_tr($pORGZ->errMsg);
    }elseif(count($orgTmp)==0){
        $error=true;
        $msg_error=_tr("Organization doesn't exist");
    }

    if($idOrgFil==1){
        $error=true;
        $msg_error=_tr("Invalid Organization");
    }

    
    //obtenemos las traducciones del parametro filtrado
    $filter_resource = htmlentities($filter_resource);
    $lang = get_language(); //lenguage que esta siendo usado
    $parameter_to_find=null;
    if(isset($filter_resource)){
        if(trim($filter_resource)!=""){
            if($lang != "en"){
                global $arrLang;
                $filter_value = strtolower(trim($filter_resource));
                $parameter_to_find[]=$filter_value; //parametro de busqueda sin traduccion
                foreach($arrLang as $key=>$value){
                    $langValue=strtolower(trim($value));
                    if(preg_match("/^[[:alnum:]| ]*$/",$filter_value))
                        if(strpos($langValue, $filter_value) !== FALSE)
                            $parameter_to_find[] = $key;
                }
            }else{
                $parameter_to_find[]=$filter_resource;
            }
        }
    }
    

    if(isset($filter_resource)){
        $parameter_to_find[] = $filter_resource;
    }else{
        $parameter_to_find=null;
    }

    $pACL->_DB->beginTransaction();
    if(!$error){
        $oGrid  = new paloSantoGrid($smarty);
        $total=$pACL->getNumResources($parameter_to_find);
        $limit=25;
        $oGrid->setLimit($limit);
        $oGrid->setTotal($total);
        $offset = $oGrid->calculateOffset();

        $tmpResource=$pACL->getListResources($limit, $offset,$parameter_to_find,'yes');//todos los recursos
        $tmpResourceOrg=$pACL->getResourcesByOrg($idOrgFil,$parameter_to_find);//los recuros a los que tiene permiso actualmente la organizacion

        if($tmpResourceOrg===false || $tmpResource===false){
            $error=true;
            $msg_error=$msg_error.""._tr($pACL->errMsg);
        }else{
            $arrPermissionAct=array();
            //los recursos seleccionados a los que se le va a dar acceso
            $selectedResource = isset($_POST['resource'])?array_keys($_POST['resource']):array();
            //validamos que los recursos seleccionados realmente existan

            foreach($tmpResourceOrg as $value){
                $arrPermissionAct[]=$value["id"];
            }

            $selectedResource[]='usermgr';
            $selectedResource[]='grouplist';
            $selectedResource[]='userlist';
            $selectedResource[]='group_permission';
            $selectedResource[]='organization';

            //hacemos una lista de los permisos que debemos eliminar y de los que debemos añadir
            $saveAcc=array_diff($selectedResource,$arrPermissionAct); //permisos que debemos añadir
            $delAcc=array_diff($arrPermissionAct,$selectedResource); //permisos que debemos eliminar
            $arrSave=array();
            $arrDelete=array();
            $arrSelected=array();
            //nos aseguramos que los recursos existan y cogemos los que se visualizan en el modulo al dar click en save
            foreach($tmpResource as $resource){
                if(in_array($resource["id"],$saveAcc))
                    $arrSave[]=$resource["id"];
                if(in_array($resource["id"],$delAcc))
                    $arrDelete[]=$resource["id"];
                if(in_array($resource["id"],$selectedResource))
                    $arrSelected[]=$resource["id"];
            }

            if(!$pACL->saveOrgPermission($idOrgFil, $arrSave) || !$pACL->deleteOrgPermissions($idOrgFil, $arrDelete)){
                $error=true;
                $msg_error=_tr($pACL->errMsg);
            }
        }
    }
    //verificamos si todo salio bien
    if($error){
        $pACL->_DB->rollBAck();
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Error saving changes.")." ".$msg_error);
    }else{
        $pACL->_DB->commit();
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Changes were applied successfully"));
    }

    unset($_SESSION['elastix_user_permission']);
    return reportOrgPermission($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$arrCredentiasls);
}


function reportOrgPermission($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf,$arrCredentiasls)
{
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $arrGroups=array();
    $arrOrgz=array();
    $filter_resource=getParameter("filter_resource");
    $idOrgFil=getParameter("idOrganization");

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
    
    $filter_resource = htmlentities($filter_resource);
    
    //buscamos en el arreglo del lenguaje la traduccion del recurso en caso de que exista
    $lang = get_language(); //lenguage que esta siendo usado
    $parameter_to_find=null;
    if(isset($filter_resource)){
        if(trim($filter_resource)!=""){
            if($lang != "en"){
                global $arrLang;
                $filter_value = strtolower(trim($filter_resource));
                $parameter_to_find[]=$filter_value; //parametro de busqueda sin traduccion
                foreach($arrLang as $key=>$value){
                    $langValue=strtolower(trim($value));
                    if(preg_match("/^[[:alnum:]| ]*$/",$filter_value))
                        if(strpos($langValue, $filter_value) !== FALSE)
                            $parameter_to_find[] = $key;
                }
            }else{
                $parameter_to_find[]=$filter_resource;
            }
        }
    }
   
    //obtenemos el numero de recursos disponibles del sistema
    $total=0;
    if(count($arrOrgz)>0){
        $total=$pACL->getNumResources($parameter_to_find,'yes');
    }
    
    if($total==false && $pACL->errMsg!=""){
        $total=0;
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("An error has ocurred to retrieved resources data"));
    }

    $limit=25;
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;    
    $url["menu"]=$module_name;
    $url["filter_resource"]=$filter_resource;
    $url["idOrganization"]=$idOrgFil;
    
    $oGrid->setTitle(_tr("Organization Permission"));
    $oGrid->setURL($url);
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    $arrColumn=array(_tr("Resource"),"<input type='checkbox' name='selectAll' id='selectAll' />"._tr('Permit Access'));
    
    $oGrid->setColumns($arrColumn);

    $arrData=array();
    if(count($arrOrgz)>0 && $total>0){
        //obtengo una lista con todos los recursos a los que una organizacion puede tener acceso
        $arrResource=$pACL->getListResources($limit,$offset,$parameter_to_find,'yes');
        
        //lista de los recursos permitidos a la organizacion seleccionada organizacion
        $arrResourceOrg=$pACL->getResourcesByOrg($idOrgFil,$parameter_to_find);
        if($arrResourceOrg===false || $arrResource===false){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("An error has ocurred to retrieve resource list.")." "._tr($pACL->errMsg));
        }else{
            $temp=array();
            foreach($arrResourceOrg as $value){
                $temp[]=$value["id"];
            }
            if(is_array($arrResource) && count($arrResource) > 0){
                foreach( $arrResource as $resource ){
                    $disabled = "";
                    if( ( $resource["id"] == 'usermgr'   || $resource["id"] == 'grouplist' || $resource["id"] == 'userlist'  ||
                        $resource["id"] == 'group_permission' || $resource["id"] == 'organization')){
                        $disabled = "disabled='disabled'";
                    }

                    $checked0 = "";
                    if(in_array($resource["id"],$temp)){
                        $checked0 = "checked";
                    }

                    $arrTmp[0] = _tr($resource["description"]);
                    $arrTmp[1] = "<input type='checkbox' $disabled name='resource[".$resource["id"]."]' id='".$resource["id"]."' class='resource' $checked0>"." "._tr("Permit");
                    $arrData[] = $arrTmp;
                }
            }
        }
    }


    $smarty->assign("SHOW", _tr("Show"));
    $smarty->assign("resource_apply", $filter_resource);

    if(count($arrOrgz)>0){
        $oGrid->addSubmitAction("apply",_tr("Save"));
        $oGrid->addComboAction("idOrganization",_tr("Organization"),$arrOrgz,$idOrgFil,"report");
        $arrFormFilter = createFieldFilter();
        $oFilterForm = new paloForm($smarty, $arrFormFilter);
        $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Resource")." = $filter_resource", $_POST, array("filter_resource" =>""));
        $oGrid->showFilter(trim($htmlFilter));
    }

    $contenidoModulo = $oGrid->fetchGrid(array(), $arrData);
    //end grid parameters
    return $contenidoModulo;
}


function getSelected(&$pDB, $userLevel1){
    $jsonObject = new PaloSantoJSON();
    $pACL = new paloACL($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $arrData = array();

    if($userLevel1!="superadmin"){
        $jsonObject->set_error("You are not authorized to perform this action. ");
    }else{
        $idOrg=getParameter("idOrg");
        //validamos que la organization exista
        $orgTmp=$pORGZ->getOrganization(array("id"=>$idOrg));

        //valido que al menos exista una organizacion creada
        if($orgTmp===false){
            $jsonObject->set_error(_tr($pORGZ->errMsg));
        }elseif(count($orgTmp)<=0){
            $jsonObject->set_error(_tr("Organization doesn't exist"));
        }else{
            //obtengo los recursos asignados a la organizacion
            $arrResourceOrg=$pACL->getResourcesByOrg($idOrg);
            if($arrResourceOrg===false){
                $jsonObject->set_error(_tr($pACL->errMsg));
            }else{
                foreach($arrResourceOrg as $resource){
                    $arrData[]=$resource["id"];
                }
                $jsonObject->set_message($arrData);
            }
        }
    }
    return $jsonObject->createJSON();
}

function getAction()
{
    global $arrPermission;
    if(getParameter("apply")) //Get parameter by POST (submit)
        //preguntar si el usuario puede hacer accion
        return (in_array('edit',$arrPermission))?'apply':'report';
    if(getParameter("action") == "getSelected")
        return "getSelected";
    else
        return "report";
}

function createFieldFilter()
{
    $arrFormElements = array(
            "filter_resource" => array( "LABEL"                  => _tr("Resource"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""),
                    );
    return $arrFormElements;
}
?>
