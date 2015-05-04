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
  $Id: index.php,v 1.1 2014-03-12 Bruno Macias bmacias@elastix.org Exp $ */
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
        case "new_announcement":
            $content = viewFormAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_edit":
            $content = viewFormAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveNewAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "reloadAasterisk":
            $content = reloadAasterisk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default: // report
            $content = reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function reportAnnouncement($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error = "";
    $pORGZ = new paloSantoOrganization($pDB);

    $domain=getParameter("organization");
    $domain=empty($domain)?'all':$domain;
    if($credentials['userlevel']!="superadmin"){
        $domain=$credentials['domain'];
    }
    $announcement_name=getParameter("announcement_name");
    
    $pAnnouncement = new paloSantoAnnouncement($pDB,$domain);
  
    $url['menu']             = $module_name;
    $url['organization']     = $domain;
    $url['announcement_name']= $announcement_name;
    
    $total=$pAnnouncement->getNumAnnouncement($domain,$announcement_name);
    $arrOrgz=array();
    if($credentials['userlevel']=="superadmin"){
        $arrOrgz=array("all"=>_tr("all"));
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }
    
    if($total===false){
        $error=$pAnnouncement->errMsg;
        $total=0;
    }

    $limit=20;

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();

    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    
    $oGrid->setTitle(_tr('Announcement List'));
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
    $arrColum[]=_tr("Announcement Name");
    $arrColum[]=_tr("Recording");
    $arrColum[]=_tr("Repeat");
    $arrColum[]=_tr("Allow Skip");
    $arrColum[]=_tr("Return to IVR");
    $arrColum[]=_tr("Don't answer channel");
    $arrColum[]=_tr("Destination after playback");
    $oGrid->setColumns($arrColum);

    $arrAnnouncement=array();
    $arrData = array();
    if($total!=0){
        $arrAnnouncement = $pAnnouncement->getAnnouncement($domain,$announcement_name,$limit,$offset);
    }

    if($arrAnnouncement===false){
        $error=_tr("Error to obtain Announcement").$pAnnouncement->errMsg;
        $arrAnnouncement=array();
    }

    foreach($arrAnnouncement as $ann) {
        $arrTmp=array();
        if($credentials['userlevel']=="superadmin"){
            $arrTmp[] = $arrOrgz[$ann["organization_domain"]];
        }
        $arrTmp[] = "&nbsp;<a href='?menu=$module_name&action=view&id_ann=".$ann['id']."&organization={$ann['organization_domain']}'>".htmlentities($ann["description"],ENT_QUOTES,"UTF-8")."</a>";
        $arrTmp[]=$ann["recording_name"];
        $arrTmp[]=$ann["repeat_msg"];
        $arrTmp[]=$ann["allow_skip"];
        $arrTmp[]=$ann["return_ivr"];
        $arrTmp[]=$ann["noanswer"];
        $arrTmp[]=$ann["destination"];
        $arrData[] = $arrTmp;
    }
            
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='report'>");
    if($pORGZ->getNumOrganization(array()) >= 1){
        if(in_array('create',$arrPermission)){
            if($credentials['userlevel']=='superadmin'){
                $oGrid->addComboAction("organization_add",_tr("ADD Announcement"), array_slice($arrOrgz,1), $selected=null, "create_announcement", $onchange_select=null);
            }else{
                $oGrid->addNew("create_announcement",_tr("ADD Announcement"));
            }   
        }
        if($credentials['userlevel']=='superadmin'){
            $_POST["organization"]=$domain;
            $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true);
        }
        $_POST["announcement_name"]=$announcement_name; // name
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Announcement Name")." = ".$announcement_name, $_POST, array("announcement_name" => "")); 
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

function viewFormAnnouncement($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    global $arrPermission;
    $error = "";
    
    $arrAnnouncement = array();
    $action          = getParameter("action");    
    $idAnnouncement  = getParameter("id_ann");
    
    if($action=="view" || $action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        if(!isset($idAnnouncement)){
            $error=_tr("Invalid Announcement ID");
        }else{
            $domain=getParameter('organization');
            if($credentials['userlevel']!='superadmin'){
                $domain=$credentials['domain'];
            }
            $pAnnouncement = new paloSantoAnnouncement($pDB,$domain);
            $arrAnnouncement = $pAnnouncement->getAnnouncementById($idAnnouncement);
            if($arrAnnouncement===false){
                $error=_tr($pAnnouncement->errMsg);
            }else if(count($arrAnnouncement)==0){
                $error=_tr("Announcement doesn't exist");
            }else{
                if(getParameter("save_edit"))
                    $arrAnnouncement=$_POST;           
            }
        }
        
        if($error!=""){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",$error);
            return reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{ // new, create
        if($credentials['userlevel']=='superadmin'){
            if(getParameter("create_announcement")){
                $domain=getParameter('organization_add'); //este parametro solo es selecionable cuando es el superadmin quien crea la ruta
            }else
                $domain=getParameter('organization');
        }else{
            $domain=$credentials['domain'];
        }
    
        $pAnnouncement = new paloSantoAnnouncement($pDB,$domain);
        if(getParameter("create_announcement")){
            $arrAnnouncement["description"]="";
            $arrAnnouncement["recording_id"]="none";
            $arrAnnouncement["allow_skip"]="no";
            $arrAnnouncement["return_ivr"]="no";
            $arrAnnouncement["noanswer"]="no";
            $arrAnnouncement["repeat_msg"]="disable";
            $arrAnnouncement["destination"]="";
            $arrAnnouncement["goto"]="";
        }else
            $arrAnnouncement=$_POST; 
    }
    
    $goto=$pAnnouncement->getCategoryDefault($domain);
    if($goto===false)
        $goto=array();
    $res=$pAnnouncement->getDefaultDestination($domain,$arrAnnouncement["goto"]);
    $destiny=($res==false)?array():$res;
    
    $arrFormOrgz = createFieldForm($goto,$destiny,$pDB,$domain);
    $oForm = new paloForm($smarty,$arrFormOrgz);

    if($action=="view"){
        $oForm->setViewMode();
    }else if($action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
    }
    
    //permission
    $smarty->assign("EDIT_ANN",in_array('edit',$arrPermission));
    $smarty->assign("CREATE_ANN",in_array('create',$arrPermission));
    $smarty->assign("DEL_ANN",in_array('delete',$arrPermission));
    
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("OPTIONS", _tr("Options"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("id_ann", $idAnnouncement);
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("ORGANIZATION_LABEL",_tr("Organization Domain"));
    $smarty->assign("ORGANIZATION",$domain);
            
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("Announcement"), $arrAnnouncement);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
} 

function saveNewAnnouncement($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $error = "";
    $continue=true;
    $success=false;

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    $pAnnouncement = new paloSantoAnnouncement($pDB,$domain);
    
    $arrFormOrgz = createFieldForm(array(),array(),$pDB,$domain);
    $oForm = new paloForm($smarty,$arrFormOrgz);

    $description = trim(getParameter('description'));
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
        return viewFormAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    } elseif (count(explode("\n", $description)) > 1) {
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid description text"));
        return viewFormAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    } else{
        if($pAnnouncement->validateDestine($domain,getParameter("destination"))==false){
            $error=_tr("You must select a default destination.");
            $continue=false;
        }
            
        if($continue){
            //seteamos un arreglo con los parametros configurados
            $arrProp=array();
            $arrProp["description"]  = $description;
            $arrProp["recording_id"] = getParameter("recording_id");
            $arrProp["allow_skip"]   = getParameter("allow_skip");
            $arrProp["return_ivr"]   = getParameter("return_ivr");
            $arrProp["noanswer"]     = getParameter("noanswer");
            $arrProp["repeat_msg"]   = getParameter("repeat_msg");
            $arrProp["goto"]         = getParameter("goto");
            $arrProp['destination']  = getParameter("destination");
        }

        if($continue){
            $pDB->beginTransaction();
            $success=$pAnnouncement->createNewAnnouncement($arrProp);
            if($success)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pAnnouncement->errMsg;
        }
    }

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Announcement has been created successfully"));
         //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function saveEditAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $continue=true;
    $success=false;
    $idAnnouncement=getParameter("id_ann");
 
    //obtenemos la informacion del ring_group por el id dado, sino existe el ring_group mostramos un mensaje de error
    if(!isset($idAnnouncement)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Announcement ID"));
        return reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $description = trim(getParameter('description'));
    
    $pAnnouncement = new paloSantoAnnouncement($pDB,$domain);
    $arrAnnouncement = $pAnnouncement->getAnnouncementById($idAnnouncement);
    if($arrAnnouncement===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pAnnouncement->errMsg));
        return reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }elseif(count($arrAnnouncement)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Announcement doesn't exist"));
        return reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    } elseif (count(explode("\n", $description)) > 1) {
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid description text"));
        return viewFormAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        if($pAnnouncement->validateDestine($domain,getParameter("destination"))==false){
            $error=_tr("You must select a default destination.");
            $continue=false;
        }
        
        if($continue){
            //seteamos un arreglo con los parametros configurados
            $arrProp=array();
            $arrProp["id"]           = $idAnnouncement;
            $arrProp["description"]  = $description;
            $arrProp["recording_id"] = getParameter("recording_id");
            $arrProp["allow_skip"]   = getParameter("allow_skip");
            $arrProp["return_ivr"]   = getParameter("return_ivr");
            $arrProp["noanswer"]     = getParameter("noanswer");
            $arrProp["repeat_msg"]   = getParameter("repeat_msg");
            $arrProp["goto"]         = getParameter("goto");
            $arrProp['destination']  = getParameter("destination");
        }

        if($continue){
            $pDB->beginTransaction();
            $success=$pAnnouncement->updateAnnouncementPBX($arrProp);
            
            if($success)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pAnnouncement->errMsg;
        }
    }

    $smarty->assign("id_ann", $idAnnouncement);

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Announcement has been edited successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function deleteAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    
    $error = "";
    $continue=true;
    $success=false;
    $idAnnouncement=getParameter("id_ann");

    //obtenemos la informacion del ring_group por el id dado, sino existe el ring_group mostramos un mensaje de error
    if(!isset($idAnnouncement)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Announcement ID"));
        return reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $pAnnouncement = new paloSantoAnnouncement($pDB,$domain);
    $arrAnnouncement = $pAnnouncement->getAnnouncementById($idAnnouncement);
    if($arrAnnouncement===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pAnnouncement->errMsg));
        return reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else if(count($arrAnnouncement)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Announcement doesn't exist"));
        return reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    

    $pDB->beginTransaction();
    $success = $pAnnouncement->deleteAnnouncement($idAnnouncement);
    if($success)
        $pDB->commit();
    else
        $pDB->rollBack();
    $error .=$pAnnouncement->errMsg;

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Announcement was deleted successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($error));
    }

    return reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function createFieldForm($goto,$destination,$pDB,$domain)
{
    $pAnnouncement = new paloSantoAnnouncement($pDB,$domain);
    $arrYesNo      = array("yes" => _tr("Yes"), "no" => _tr("No"));
    $arrRecording  = $pAnnouncement->getRecordingsSystem($domain);
    
    $recording = array("none" => _tr("None"));
    if(is_array($arrRecording)){
        foreach($arrRecording as $key => $value){
            $recording[$key] = $value;
        }
    }
    
    $arrRepeat["no"] = _tr("Disable");
    for($i=0;$i<=9;$i++)
        $arrRepeat[$i] = $i;
    $arrRepeat["*"] = "*";
    $arrRepeat["#"] = "#";
    
    
    $arrFormElements = array("description"   => array("LABEL"                => _tr('Name'),
                                                    "DESCRIPTION"            => _tr("Name"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "recording_id"  => array("LABEL"                => _tr("Recording"),
                                                    "DESCRIPTION"            => _tr("Announcement recording"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $recording,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "repeat_msg"    => array("LABEL"                => _tr("Repeat"),
                                                    "DESCRIPTION"            => _tr("Repeat Message"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrRepeat,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),         
                            "allow_skip"     => array("LABEL"                => _tr("Allow Skip"),
                                                    "DESCRIPTION"            => _tr("Allow Skip"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "return_ivr"       => array("LABEL"             => _tr("Return to IVR"),
                                                    "DESCRIPTION"            => _tr("Return to IVR"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "noanswer"         => array("LABEL"             => _tr("Don't answer channel"),
                                                    "DESCRIPTION"            => _tr("Don't answer channel"),
                                                    "REQUIRED"               => "no",
                                                     "INPUT_TYPE"            => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "goto"              => array("LABEL"             => _tr("Destination"),
                                                    "DESCRIPTION"            => _tr("Destination after playback"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $goto,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""), 
                            "destination"   => array("LABEL"             => _tr(""),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $destination,
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
        "announcement_name"  => array("LABEL"            => _tr("Announcement Name"),
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
        return reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
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

    return reportAnnouncement($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function getAction(){
    global $arrPermission;
    if(getParameter("create_announcement"))
        return (in_array('create',$arrPermission))?'new_announcement':'report';
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
