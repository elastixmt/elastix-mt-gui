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
  $Id: index.php,v 1.1.1.1 2012/07/30 rocio mera rmera@palosanto.com Exp $ */
include_once "libs/paloSantoJSON.class.php";
include_once("libs/paloSantoGrid.class.php");
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoOrganization.class.php";
include_once "libs/paloSantoPBX.class.php";

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
        case "new_rg":
            $content = viewFormMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_edit":
            $content = viewFormMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveNewMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default: // report
            $content = reportMoH($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function reportMoH($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error = "";
    $pORGZ = new paloSantoOrganization($pDB);

    $name=getParameter("name");
    
    if($credentials['userlevel']=="superadmin"){
        $domain=getParameter("organization");
        $domain=empty($domain)?'all':$domain;
        
        $arrOrgz=array("all"=>_tr("all"));
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }else{
        $arrOrgz=array();
        $domain=$credentials['domain'];
    }
    
    $url["menu"]=$module_name;
    $url["organization"]=$domain;
    $url["name"]=$name;
    
    $pMoH = new paloSantoMoH($pDB,$domain);
    $total=$pMoH->getNumMoH($domain,$name);
    
    if($total===false){
        $error=$pMoH->errMsg;
        $total=0;
    }

    $limit=20;

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $oGrid->setTitle(_tr('MoH Class List'));
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
    $arrColum[]=_tr("Name");
    $arrColum[]=_tr("Type");
    $arrColum[]=_tr("Sort");
    $arrColum[]=_tr("Directory");
    $arrColum[]=_tr("Aplication");
    $oGrid->setColumns($arrColum);

    $arrMoH=array();
    $arrData = array();
    if($total!=0){
        $arrMoH = $pMoH->getMoHs($domain,$name,$limit,$offset);
    }

    if($arrMoH===false){
        $error=_tr("Error to obtain MoH Class").$pMoH->errMsg;
        $arrMoH=array();
    }

    $arrData=array();
    foreach($arrMoH as $moh) {
        $arrTmp=array();
        if($credentials['userlevel']=="superadmin"){
            if(empty($moh["organization_domain"])){
                $arrTmp[] = "";
                $arrTmp[] = "&nbsp;<a href='?menu=$module_name&action=view&id_moh=".$moh["name"]."'>".$moh["description"]."</a>";
            }else{
                $arrTmp[] = $arrOrgz[$moh["organization_domain"]];
                $arrTmp[] = $moh["description"];
            }
        }else
            $arrTmp[] = "&nbsp;<a href='?menu=$module_name&action=view&id_moh=".$moh["name"]."'>".$moh["description"]."</a>";
        $arrTmp[]=$moh["mode"];
        $arrTmp[]=$moh["sort"];
        $arrTmp[]=$moh["directory"];
        $arrTmp[]=$moh["application"];
        $arrData[] = $arrTmp;
    }
            
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='report'>");
    if(in_array("create",$arrPermission))
        $oGrid->addNew("create_moh",_tr("Create New Class MoH"));
    if($credentials['userlevel']=="superadmin"){
        $_POST["organization"]=$domain;
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true);
    }
    $_POST["name"]=$name; // name 
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Name")." = ".$name, $_POST, array("name" => "")); 
    $arrFormElements = createFieldFilter($arrOrgz);
    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
    $oGrid->showFilter(trim($htmlFilter));

    if($error!=""){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",$error);
    }

    $contenidoModulo = $oGrid->fetchGrid(array(), $arrData);
    return $contenidoModulo;
}

function viewFormMoH($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials,$arrFiles=array()){
    global $arrPermission;
    $error = "";
    $arrMoH=array();
    $action = getParameter("action");
        
    if($credentials['userlevel']=='superadmin'){
        $pMoH = new paloSantoMoH($pDB,"");
    }else{
        $domain=$credentials['domain'];
        $pMoH = new paloSantoMoH($pDB,$domain);
    }

    $idMoH=getParameter("id_moh");
    if($action=="view"  || getParameter("edit") || getParameter("save_edit")){
        if(!isset($idMoH)){
            $error=_tr("Invalid Music on Hold Class");
        }
        
        $arrMoH = $pMoH->getMoHByClass($idMoH);
        $smarty->assign('NAME_MOH',$arrMoH["name"]);
        $smarty->assign('MODE_MOH',$arrMoH["mode_moh"]);
        
        if($error==""){
            if($arrMoH===false){
                $error=_tr($pMoH->errMsg);
            }else if(count($arrMoH)==0){
                $error=_tr("MoH doesn't exist");
            }else{
                $smarty->assign('j',0);
                $smarty->assign('items',$arrMoH["listFiles"]);
                if(getParameter("save_edit"))
                    $arrMoH=$_POST;
            }
        }
        
        if($error!=""){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",$error);
            return reportMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{
        if($credentials['userlevel']=='superadmin'){
            $pMoH = new paloSantoMoH($pDB,"");
        }else{
            $pMoH = new paloSantoMoH($pDB,$domain);
        }
        
        $smarty->assign('j',0);
        $smarty->assign('items',$arrFiles);
        $smarty->assign('arrFiles',"1");
        
        $arrMoH=$_POST; 
    }

    $arrForm = createFieldForm($pMoH->getAudioFormatAsterisk(), ($credentials['userlevel']=='superadmin'));
    $oForm = new paloForm($smarty,$arrForm);

    if($action=="view"){
        $oForm->setViewMode();
    }else if($action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
    }

    //permission
    $smarty->assign("EDIT_MOH",in_array("edit",$arrPermission));
    $smarty->assign("DEL_MOH",in_array("delete",$arrPermission));
    
    //$smarty->assign("ERROREXT",_tr($pTrunk->errMsg));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("OPTIONS", _tr("Options"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("id_moh", $idMoH);
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("ADD_FILE",_tr("Add New file"));
    $max_upload = (int)(ini_get('upload_max_filesize'));
    $max_post = (int)(ini_get('post_max_size'));
    $memory_limit = (int)(ini_get('memory_limit'));
    $upload_mb = min($max_upload, $max_post, $memory_limit)*1048576;
    $smarty->assign("max_size", $upload_mb);
    $smarty->assign("alert_max_size", _tr("File size exceeds the limit. "));
        
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("MoH Route"), $arrMoH);
    $content = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewMoH($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $error = "";
    $continue=true;
    $success=false;

    if($credentials['userlevel']=='superadmin'){
        $pMoH = new paloSantoMoH($pDB,"");
    }else{
        $domain=$credentials['domain'];
        $pMoH = new paloSantoMoH($pDB,$domain);
    }

    $arrFormOrgz = createFieldForm(array(), ($credentials['userlevel']=='superadmin'));
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
        return viewFormMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $name = getParameter("name");
        if($name==""){
            $error=_tr("Field 'Name' can't be empty.");
            $continue=false;
        } elseif (getParameter("mode_moh") == 'custom' && $credentials['userlevel'] != 'superadmin') {
            $error = _tr('Creation of custom MOH restricted to superadmin');
            $continue = FALSE;
        }
        if($continue){
            //seteamos un arreglo con los parametros configurados
            $arrProp=array();
            $arrProp["name"]=getParameter("name");
            $arrProp["mode"]=getParameter("mode_moh");
            $arrProp["application"]=getParameter("application");
            $arrProp["sort"]=getParameter("sort");
            $arrProp["format"]=getParameter("format");
        }

        if($continue){
            $pDB->beginTransaction();
            $success=$pMoH->createNewMoH($arrProp);
            if($success){
                $pDB->commit();
                if($arrProp["mode"]=="files")
                    $pMoH->uploadFile($arrProp["name"]);
            }else{
                $pDB->rollBack();
            }
            $error .=$pMoH->errMsg;
        }
    }

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("New MoH Class has been created successfully")." ".$error);
        $content = reportMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function saveEditMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $success=false;
    $idMoH=getParameter("id_moh");

    if($credentials['userlevel']=='superadmin'){
        $pMoH = new paloSantoMoH($pDB,"");
    }else{
        $domain=$credentials['domain'];
        $pMoH = new paloSantoMoH($pDB,$domain);
    }
    
    //obtenemos la informacion del ring_group por el id dado, sino existe el ring_group mostramos un mensaje de error
    if(!isset($idMoH)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid MoH Class"));
        return reportMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $arrMoH = $pMoH->getMoHByClass($idMoH);
    if($arrMoH===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pMoH->errMsg));
        return reportMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else if(count($arrMoH)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("MoH doesn't exist"));
        return reportMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$credentials);
    } elseif ($arrMoH["mode_moh"] == 'custom' && $credentials['userlevel'] != 'superadmin') {
        $success = FALSE;
        $error = _tr('Update of custom MOH restricted to superadmin.');
    } else {
        $arrProp=array();
        $arrProp["class"]=$arrMoH["class"];
        $arrProp["application"]=getParameter("application");
        $arrProp["sort"]=getParameter("sort");
        $arrProp["format"]=getParameter("format");
        if(!isset($_POST['current_File']))
            $arrProp["remain_files"]=array();
        else
            $arrProp["remain_files"]=$_POST['current_File'];

        //rint_r($arrProp["remain_files"]);
        $pDB->beginTransaction();
        $success=$pMoH->updateMoHPBX($arrProp);
        if($success){
            $pDB->commit();
            if($arrMoH["mode_moh"]=="files")
                $pMoH->uploadFile($arrMoH["name"]);
        }else
            $pDB->rollBack();
        $error .=$pMoH->errMsg;
    }

    $smarty->assign("id_moh", $idMoH);

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("MoH Class has been edited successfully")." ".$error);
        $content = reportMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function deleteMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $continue=true;
    $success=false;
    $idMoH=getParameter("id_moh");

    if($credentials['userlevel']=='superadmin'){
        $pMoH = new paloSantoMoH($pDB,"");
    }else{
        $domain=$credentials['domain'];
        $pMoH = new paloSantoMoH($pDB,$domain);
    }

    if(!isset($idMoH)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid MoH"));
        return reportMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $userLevel1, $userAccount, $org_domain);
    }

    $pDB->beginTransaction();
    $success = $pMoH->deleteMoH($idMoH);
    if($success)
        $pDB->commit();
    else
        $pDB->rollBack();
    $error .=$pMoH->errMsg;

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("MoH class was deleted successfully"));
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($error));
    }

    return reportMoH($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}


function createFieldForm($arrFormats, $bEnableCustom)
{
    if(!is_array($arrFormats)){
        $arrFormats=array("WAV"=>"WAV","wav"=>"wav","ulaw"=>"ulaw","alaw"=>"alaw","sln"=>"sln","gsm"=>"gsm","g729"=>"g729");
    }
    $mohmodes = array("files"=>_tr("files"), "custom"=>_tr("custom"));
    if (!$bEnableCustom) unset($mohmodes['custom']);
    $arrFormElements = array("name"     => array("LABEL"             => _tr('Class Name'),
                                                    "DESCRIPTION"            => _tr("MOH_classname"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:300px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "mode_moh"     => array("LABEL"             => _tr("Type"),
                                                    "DESCRIPTION"            => _tr("MOH_type"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $mohmodes,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "application" 	=> array("LABEL"             => _tr("Application"),
                                                    "REQUIRED"              => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:300px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "sort"     => array("LABEL"             => _tr("Sort Music"),
                                                    "DESCRIPTION"            => _tr("MOH_sortmusic"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array("alpha"=>_tr("alpha"), "random"=>_tr("random")),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "format"     => array("LABEL"             => _tr("Format"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrFormats,
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
        "name"  => array("LABEL"                 => _tr("Name"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "TEXT",
                        "INPUT_EXTRA_PARAM"      => '',
                        "VALIDATION_TYPE"        => "text",
                        "VALIDATION_EXTRA_PARAM" => ""),
        );
    return $arrFields;
}


function getAction(){
    global $arrPermission;
    if(getParameter("create_moh"))
        return (in_array("create",$arrPermission))?"new_rg":"report";
    else if(getParameter("save_new")) //Get parameter by POST (submit)
        return (in_array("create",$arrPermission))?"save_new":"report";
    else if(getParameter("save_edit"))
        return (in_array("edit",$arrPermission))?"save_edit":"report";
    else if(getParameter("edit"))
        return (in_array("edit",$arrPermission))?"view_edit":"report";
    else if(getParameter("delete"))
        return (in_array("delete",$arrPermission))?"delete":"report";
    else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view";
    else
        return "report"; //cancel
}
?>
