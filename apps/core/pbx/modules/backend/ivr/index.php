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
  $Id: index.php,v 1.1.1.1 2012/09/07 German Macas gmacas@palosanto.com Exp $ */
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
        case "new_ivr":
            $content = viewFormIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_edit":
            $content = viewFormIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveNewIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "reloadAasterisk":
            $content = reloadAasterisk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
                break;
        case "get_destination_category":
            $content = get_destination_category($smarty, $module_name, $pDB, $arrConf, $arrCredentials);
            break;
        default: // report
            $content = reportIVR($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function reportIVR($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error = "";
    $pORGZ = new paloSantoOrganization($pDB);

    $domain=getParameter("organization");
    $name=getParameter("name");
    
    $domain=empty($domain)?'all':$domain;
    if($credentials['userlevel']!='superadmin')
        $domain=$credentials['domain'];
    
    $url['menu']=$module_name;
    $url['organization']=$domain;
    $url['name']=$name;
    
    if($credentials['userlevel']=="superadmin"){
        if(isset($domain) && $domain!="all"){
            $pIVR = new paloIvrPBX($pDB,$domain);
        }else{
            $pIVR = new paloIvrPBX($pDB,"");
        }
        $total=$pIVR->getTotalIvr($domain,$name);
        
        $arrOrgz=array("all"=>_tr("all"));
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }else{
        $arrOrgz=array();
        $pIVR = new paloIvrPBX($pDB,$domain);
        $total=$pIVR->getTotalIvr($domain,$name);
    }
    
    if($total===false){
        $error=$pIVR->errMsg;
        $total=0;
    }

    $limit=20;

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $oGrid->setTitle(_tr('Ivrs List'));
    $oGrid->setUrl($url);
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    if($credentials['userlevel']=="superadmin")
        $arrColumns[]=_tr("Organization");
    $arrColumns[]=_tr("Ivr Name");
    $arrColumns[]=_tr("Timeout");
    $arrColumns[]=_tr("Enable Call Extensions");
    $arrColumns[]=_tr("# Loops");
    $oGrid->setColumns($arrColumns);
    
    $arrData = array();
    $arrIVR = array();
    if($total!=0){
        $arrIVR=$pIVR->getIvrs($domain,$name,$limit,$offset);
    }

    if($arrIVR===false){
        $error=_tr("Error getting ivr data.").$pIVR->errMsg;
    }else{
        foreach($arrIVR as $ivr) {
            $arrTmp=array();
            if($credentials['userlevel']=="superadmin")
                $arrTmp[]=$arrOrgz[$ivr['organization_domain']];
            $arrTmp[] = "&nbsp;<a href='?menu=$module_name&action=view&id_ivr=".$ivr['id']."&organization={$ivr["organization_domain"]}'>".htmlentities($ivr["name"],ENT_QUOTES,"UTF-8")."</a>";
            $arrTmp[]=$ivr["timeout"];
            $arrTmp[]=$ivr["directdial"];
            $arrTmp[]=$ivr["loops"];
            $arrData[] = $arrTmp;
        }
    }

    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='report'>"); 
    if($pORGZ->getNumOrganization(array()) >= 1){
        if(in_array('create',$arrPermission)){
            if($credentials['userlevel']=='superadmin'){
                $oGrid->addComboAction("organization_add",_tr("Create New IVR"), array_slice($arrOrgz,1), $selected=null, "create_ivr", $onchange_select=null);
            }else{
                $oGrid->addNew("create_ivr",_tr("Create New IVR"));
            }   
        }
        if($credentials['userlevel']=='superadmin'){
            $_POST["organization"]=$domain;
            $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => _tr("all")),true);
        }
        $_POST["name"]=$name; //ivr name
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Name")." = ".$name, $_POST, array("name" => "")); 
        
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

function viewFormIVR($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials, $arrDestine=array()){
    global $arrPermission;
    $error = "";

    $arrIvr=array();
    $action = getParameter("action");
    $idIVR=getParameter("id_ivr");

    if($action=="view" || $action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        if(!isset($idIVR)){
            $error=_tr("Invalid IVR");
        }else{
            $domain=getParameter('organization');
            if($credentials['userlevel']!='superadmin'){
                $domain=$credentials['domain'];
            }
            
            $pIVR=new paloIvrPBX($pDB,$domain);
            $arrIVR=$pIVR->getIvrById($idIVR);
            if($arrIVR===false){
                $error=_tr($pIVR->errMsg);
            }else if(count($arrIVR)==0){
                $error=_tr("IVR doesn't exist");
            }else{
                //para que se muestren los destinos
                $smarty->assign('j',0);
                $arrGoTo=$pIVR->getCategoryDefault($domain);
                $smarty->assign('arrGoTo',$arrGoTo);
                
                if($action=="view" || getParameter("edit") ){
                    $arrDestine = $pIVR->getArrDestine($idIVR);
                }
                
                $smarty->assign('items',$arrDestine);
                if(getParameter("save_edit")){
                    $arrIVR=$_POST;
                }
                $arrIVR["mesg_invalid"]=(is_null($arrIVR["mesg_invalid"]))?"none":$arrIVR["mesg_invalid"];
                $arrIVR["mesg_timeout"]=(is_null($arrIVR["mesg_timeout"]))?"none":$arrIVR["mesg_timeout"];
                $arrIVR["announcement"]=(is_null($arrIVR["announcement"]))?"none":$arrIVR["announcement"];
                if(isset($arrIVR["retvm"])){
                    if($arrIVR["retvm"]=="yes"){
                        $smarty->assign("CHECKED","checked");
                    }
                }
                if(getParameter("retvm")){
                    $smarty->assign("CHECKED","checked");
                }
            }
        }
    }else{
        if($credentials['userlevel']=='superadmin'){
            if(getParameter("create_ivr")){
                $domain=getParameter('organization_add'); //este parametro solo es selecionable cuando es el superadmin quien crea la ruta
            }else
                $domain=getParameter('organization');
        }else{
            $domain=$credentials['domain'];
        }
        
        $pIVR=new paloIvrPBX($pDB,$domain);
        //para que se muestren los destinos
        $smarty->assign('j',0);
        $arrGoTo=$pIVR->getCategoryDefault($domain);
        $smarty->assign('arrGoTo',$arrGoTo);
        $smarty->assign('items',$arrDestine);
        if(getParameter("create_ivr")){
            $arrIVR["timeout"]="10";
            $arrIVR["loops"]="2";
            $arrIVR["directdial"]="no";
        }else{
            $arrIVR=$_POST;
        }
    }

    $arrFormOrgz = createFieldForm($pIVR->getRecordingsSystem($domain),$arrGoTo);
    $oForm = new paloForm($smarty,$arrFormOrgz);

    if($action=="view"){
        $oForm->setViewMode();
    }else if($action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
       $oForm->setEditMode();
    }
    
    $smarty->assign("EDIT_IVR",in_array('edit',$arrPermission));
    $smarty->assign("CREATE_IVR",in_array('create',$arrPermission));
    $smarty->assign("DEL_IVR",in_array('delete',$arrPermission));

    $smarty->assign("ERROREXT",_tr($pIVR->errMsg));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("id_ivr", $idIVR);
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("ORGANIZATION_LABEL",_tr("Organization Domain"));
    $smarty->assign("ORGANIZATION",$domain);
    $smarty->assign("RETIVR", _tr("Return to IVR"));
    $smarty->assign("DIGIT", _tr("Exten"));
    $smarty->assign("OPTION", _tr("Option"));
    $smarty->assign("DESTINE", _tr("Destine"));
    $smarty->assign("GENERAL", _tr("General"));

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl","IVR", $arrIVR);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewIVR($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $error = "";
    $continue = true;
    $success = false;

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien crea la ruta
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $pIVR=new paloIvrPBX($pDB,$domain);
    $domain=$pIVR->getDomain();
    if(empty($domain)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Organization"));
        return viewFormIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $arrGoTo=$pIVR->getCategoryDefault($domain);
    $arrFormOrgz = createFieldForm($pIVR->getRecordingsSystem($domain),$arrGoTo);
    $oForm = new paloForm($smarty,$arrFormOrgz);

    //destinos del ivr
    $arrDestine = getParameter("arrDestine");
    $tmpstatus = explode(",",$arrDestine);
    $arrDestine = array_values(array_diff($tmpstatus, array('')));
    $tmp_destine=array();
    foreach($arrDestine as $destine){
        $ivr_ret = getParameter("ivrret".$destine);
        $option = getParameter("option".$destine);
        $goto = getParameter("goto".$destine);
        $destine = getParameter("destine".$destine);
        $val=($ivr_ret=="on")?"yes":"no";
        $tmp_destine[]=array("0",$option,$goto,$destine,$val);
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
        return viewFormIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$credentials,$tmp_destine);
    }else{
        //seteamos un arreglo con los parametros configurados
        $arrProp=array();
        $arrProp["name"]=getParameter("name");
        $arrProp['announcement']=getParameter("announcement");
        $arrProp['retvm'] = (getParameter("retvm")) ? "yes" : "no";
        $arrProp['directdial'] = getParameter("directdial");
        $arrProp['mesg_timeout']=getParameter("mesg_timeout");
        $arrProp['mesg_invalid']=getParameter("mesg_invalid");
        $arrProp['loops']=getParameter("loops");
        $arrProp['timeout']=getParameter("timeout");

        $pDB->beginTransaction();
        $successIVR=$pIVR->insertIVRDB($arrProp,$tmp_destine);
        //$successDest=$pIVR->insertDestineDB($arrDestine,$arrProp["displayname"],$domain);
        if($successIVR)
            $pDB->commit();
        else
            $pDB->rollBack();
        $error .=$pIVR->errMsg;
    }

    if($successIVR){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("IVR has been created successfully."));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials,$tmp_destine);
    }
    return $content;
}

function saveEditIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $continue = true;
    $successIVR = false;
    
    $idIVR=getParameter("id_ivr");
    //obtenemos la informacion del usuario por el id dado, sino existe mostramos un mensaje de error
    if(!isset($idIVR)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid IVR"));
        return reportIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $domain=getParameter('organization');
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }

    $pIVR = new paloIvrPBX($pDB,$domain);
    $arrIVR = $pIVR->getIVRById($idIVR);

    if($arrIVR===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pIVR->errMsg));
        return reportIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else if(count($arrIVR)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("IVR doesn't exist"));
        return reportIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        //seteamos un arreglo con los parametros configurados
        //seteamos un arreglo con los parametros configurados
        $arrProp=array();
        $arrProp["name"]=getParameter("name");
        $arrProp['announcement']=getParameter("announcement");
        $arrProp['retvm'] = (getParameter("retvm")) ? "yes" : "no";
        $arrProp['directdial'] = getParameter("directdial");
        $arrProp['mesg_timeout']=getParameter("mesg_timeout");
        $arrProp['mesg_invalid']=getParameter("mesg_invalid");
        $arrProp['loops']=getParameter("loops");
        $arrProp['timeout']=getParameter("timeout");
        
        //destinos del ivr
        $arrDestine = getParameter("arrDestine");
        $tmpstatus = explode(",",$arrDestine);
        $arrDestine = array_values(array_diff($tmpstatus, array('')));
        $tmp_destine=array();
        foreach($arrDestine as $destine){
            $ivr_ret = getParameter("ivrret".$destine);
            $option = getParameter("option".$destine);
            $goto = getParameter("goto".$destine);
            $destine = getParameter("destine".$destine);
            $val=($ivr_ret=="on")?"yes":"no";
            $tmp_destine[]=array("0",$option,$goto,$destine,$val);
        }
        
        if($arrProp["name"]=="" || !isset($arrProp["name"])){
            $error="Field "._tr('Display Name')." can't be empty";
            $continue=false;
        }
        
        if(!preg_match("/^[0-9]+$/",$arrProp['timeout'])){
            $error=_tr("Invalid field Timeout");
            $continue=false;
        }
        
        if(!preg_match("/^[0-9]+$/",$arrProp['timeout'])){
            $error=_tr("Invalid field Repeat Loops");
            $continue=false;
        }
        
        if($continue){
            $pDB->beginTransaction();
            $successIVR=$pIVR->updateIVRDB($arrProp,$idIVR,$tmp_destine);
            if($successIVR)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pIVR->errMsg;
        }
    }

    $smarty->assign("id_ivr", $idIVR);

    if($successIVR){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("IVR has been edited successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials,$tmp_destine);
    }
    return $content;
}

function deleteIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $continue = true;
    $successIVR = false;
    
    $idIVR=getParameter("id_ivr");
    //obtenemos la informacion del usuario por el id dado, sino existe mostramos un mensaje de error
    if(!isset($idIVR)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid IVR"));
        return reportIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $domain=getParameter('organization');
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }

    $pIVR = new paloIvrPBX($pDB,$domain);
    $arrIVR = $pIVR->getIVRById($idIVR);

    if($arrIVR===false){
        $error=_tr("Error with database connection. ").$pIVR->errMsg;
    }elseif(count($arrIVR)==false){
        $error=_tr("Ivr doesn't exist");
    }else{
        $pDB->beginTransaction();
        $successIVR=$pIVR->deleteIVRDB($idIVR);
        if($successIVR){
            $pDB->commit();
        }else
            $pDB->rollBack();
        $error .=$pIVR->errMsg;
    }

    if($successIVR){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("The IVR was deleted successfully"));
        //mostramos el mensaje para crear los archivos de configuracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($error));
    }
    return reportIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);;
}

function get_destination_category($smarty, $module_name, $pDB, $arrConf, $credentials){
    $jsonObject = new PaloSantoJSON();
    $categoria=getParameter("option");
    $domain=getParameter("organization");
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    $pIVR=new paloIvrPBX($pDB,$domain);
    $arrDestine=$pIVR->getDefaultDestination($domain,$categoria);
    if($arrDestine==FALSE){
        $jsonObject->set_error(_tr($pIVR->errMsg));
    }else{
        $jsonObject->set_message($arrDestine);
    }
    return $jsonObject->createJSON();
}

function createFieldForm($recordings,$arrGoTo)
{
    $arrRecordings=array("none"=>_tr("None"));
    if(is_array($recordings)){
        foreach($recordings as $key => $value){
            $arrRecordings[$key] = $value;
        }
    }

    $arrYesNo=array("yes"=>_tr("Yes"),"no"=>_tr("No"));
    $loops=array(0,1,2,3,4,5,6,7,8,9);
    $arrFormElements = array("name" => array("LABEL"                  => _tr('Display Name'),
                                                    "DESCRIPTION"            => _tr("Ivr Name"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                "announcement"  => array("LABEL"                => _tr("Announcement"),
                                                    "DESCRIPTION"            => _tr("IVR_announcement"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrRecordings,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),//accion en javascript
                                "timeout"   => array("LABEL"                  => _tr("Timeout"),
                                                    "DESCRIPTION"            => _tr("Timeout"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                "retvm"   => array("LABEL"                  => _tr("VM Return to IVR"),
                                                    "DESCRIPTION"            => _tr("IVR_returnVM"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "CHECKBOX",
                                                    "INPUT_EXTRA_PARAM"      => array(),
                                                    "VALIDATION_TYPE"        => "",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                "directdial"       => array("LABEL"             => _tr("Enable Direct Dial"),
                                                    "DESCRIPTION"            => _tr("IVR_enabledirectdial"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                            "mesg_timeout"       => array("LABEL"             => _tr("Timeout Message"),
                                                    "DESCRIPTION"            => _tr("IVR_timeoutmessage"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrRecordings,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "mesg_invalid"  => array("LABEL"                  => _tr("Invalid Message"),
                                                    "DESCRIPTION"            => _tr("IVR_invalidmessage"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrRecordings,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "loops"       => array("LABEL"                  => _tr("Repeat Loops"),
                                                    "DESCRIPTION"            => _tr("# Loops"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $loops,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                "goto__"      => array("LABEL"             => _tr("goto"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrGoTo,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                "option__"    => array("LABEL"                  => _tr("option"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:50px;text-align:center;"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                "destine__"    => array("LABEL"       => _tr(""),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "ivrret__"   => array("LABEL"        => _tr("Return to IVR"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "CHECKBOX",
                                                    "INPUT_EXTRA_PARAM"      => array(),
                                                    "VALIDATION_TYPE"        => "",
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
        "name"  => array("LABEL"                 => _tr("Name"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "TEXT",
                        "INPUT_EXTRA_PARAM"      => '',
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
        return reportOutbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
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

    return reportIVR($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function getAction(){
    global $arrPermission;
    if(getParameter("create_ivr"))
        return (in_array('create',$arrPermission))?'new_ivr':'report';
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
    else if(getParameter("action")=="get_destination_category")
        return "get_destination_category";
    else
        return "report"; //cancel
}
?>
