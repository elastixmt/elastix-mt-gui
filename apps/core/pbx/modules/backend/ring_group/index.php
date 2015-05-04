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
        case "new_rg":
            $content = viewFormRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_edit":
            $content = viewFormRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveNewRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "reloadAasterisk":
            $content = reloadAasterisk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default: // report
            $content = reportRG($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function reportRG($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error = "";
    $pORGZ = new paloSantoOrganization($pDB);

    $domain=getParameter("organization");
    $domain=empty($domain)?'all':$domain;
    if($credentials['userlevel']!="superadmin"){
        $domain=$credentials['domain'];
    }
    $rg_name=getParameter("rg_name");
    
    $pRG = new paloSantoRG($pDB,$domain);
    
    $rg_number=getParameter("rg_number");
    if(isset($rg_number) && $rg_number!=''){
        $expression=$pRG->getRegexPatternFromAsteriskPattern($rg_number);
        if($expression===false)
            $rg_number='';
    }
    
    $url['menu']=$module_name;
    $url['organization']=$domain;
    $url['rg_number']=$rg_number; //ring group number
    $url['rg_name']=$rg_name; //ring group number
    
    $total=$pRG->getNumRG($domain,$rg_number,$rg_name);
    $arrOrgz=array();
    if($credentials['userlevel']=="superadmin"){
        $arrOrgz=array("all"=>_tr("all"));
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }
    
    if($total===false){
        $error=$pRG->errMsg;
        $total=0;
    }

    $limit=20;

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();

    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    
    $oGrid->setTitle(_tr('RG List'));
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
    $arrColum[]=_tr("Number");
    $arrColum[]=_tr("Name");
    $arrColum[]=_tr("Strategy");
    $arrColum[]=_tr("Ring Time");
    $arrColum[]=_tr("Ignore CF");
    $arrColum[]=_tr("Skip Busy Extensions");
    $arrColum[]=_tr("Default Destination");
    $oGrid->setColumns($arrColum);

    $arrRG=array();
    $arrData = array();
    if($total!=0){
        $arrRG = $pRG->getRGs($domain,$rg_number,$rg_name,$limit,$offset);
    }

    if($arrRG===false){
        $error=_tr("Error to obtain Ring Groups").$pRG->errMsg;
        $arrRG=array();
    }

    foreach($arrRG as $rg) {
        $arrTmp=array();
        if($credentials['userlevel']=="superadmin"){
            $arrTmp[] = $arrOrgz[$rg["organization_domain"]];
        }
        $arrTmp[] = "&nbsp;<a href='?menu=ring_group&action=view&id_rg=".$rg['id']."&organization={$rg['organization_domain']}'>".$rg['rg_number']."</a>";
        $arrTmp[]=htmlentities($rg["rg_name"],ENT_QUOTES,"UTF-8");
        $arrTmp[]=$rg["rg_strategy"];
        $arrTmp[]=$rg["rg_time"];
        $arrTmp[]=$rg["rg_cf_ignore"];
        $arrTmp[]=$rg["rg_skipbusy"];
        $arrTmp[]=$rg["destination"];
        $arrData[] = $arrTmp;
    }
            
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='report'>");
    if($pORGZ->getNumOrganization(array()) >= 1){
        if(in_array('create',$arrPermission)){
            if($credentials['userlevel']=='superadmin'){
                $oGrid->addComboAction("organization_add",_tr("ADD Ring Group"), array_slice($arrOrgz,1), $selected=null, "create_rg", $onchange_select=null);
            }else{
                $oGrid->addNew("create_rg",_tr("ADD Ring Group"));
            }   
        }
        if($credentials['userlevel']=='superadmin'){
            $_POST["organization"]=$domain;
            $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true);
        }
        $_POST["rg_number"]=$rg_number; // patter to filter estension number
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Ring Group Number")." = ".$rg_number, $_POST, array("rg_number" => "")); 
        $_POST["rg_name"]=$rg_name; // name
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Ring Group Name")." = ".$rg_name, $_POST, array("rg_name" => "")); 
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

function viewFormRG($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    global $arrPermission;
    $error = "";
    
    $arrRG=array();
    $action = getParameter("action");
    
    $idRG=getParameter("id_rg");
    if($action=="view" || $action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        if(!isset($idRG)){
            $error=_tr("Invalid Ring Group");
        }else{
            $domain=getParameter('organization');
            if($credentials['userlevel']!='superadmin'){
                $domain=$credentials['domain'];
            }
            $pRG = new paloSantoRG($pDB,$domain);
            $arrRG = $pRG->getRGById($idRG);
            if($arrRG===false){
                $error=_tr($pRG->errMsg);
            }else if(count($arrRG)==0){
                $error=_tr("RG doesn't exist");
            }else{
                if(getParameter("save_edit"))
                    $arrRG=$_POST;
                else{
                    if($action!="view"){
                        $tmpExt=explode("-",$arrRG["rg_extensions"]);
                        $arrRG["rg_extensions"]="";
                        foreach($tmpExt as $value){
                            $arrRG["rg_extensions"] .=$value."\n";
                        }
                    }
                    if($arrRG["rg_play_moh"]!="yes"){
                        $arrRG["rg_moh"]=$arrRG["rg_play_moh"];
                    }
                }
                $smarty->assign("confirm",$arrRG["rg_confirm_call"]);
                $smarty->assign("RG_NUMBER",$arrRG["rg_number"]);
            }
        }
        
        if($error!=""){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",$error);
            return reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{
        if($credentials['userlevel']=='superadmin'){
            if(getParameter("create_rg")){
                $domain=getParameter('organization_add'); //este parametro solo es selecionable cuando es el superadmin quien crea la ruta
            }else
                $domain=getParameter('organization');
        }else{
            $domain=$credentials['domain'];
        }
    
        $pRG = new paloSantoRG($pDB,$domain);
        if(getParameter("create_rg")){
            $arrRG["rg_strategy"]="ringall";
            $arrRG["rg_moh"]="ring";
            $arrRG["rg_recording"]="none";
            $arrRG["rg_cf_ignore"]="no";
            $arrRG["rg_skipbusy"]="no";
            $arrRG["rg_confirm_call"]="no";
            $arrRG["rg_time"]="20";
            $arrRG["goto"]="";
        }else
            $arrRG=$_POST; 
    }
    
    $goto=$pRG->getCategoryDefault($domain);
    if($goto===false)
        $goto=array();
    $res=$pRG->getDefaultDestination($domain,$arrRG["goto"]);
    $destiny=($res==false)?array():$res;
    
    $arrFormOrgz = createFieldForm($goto,$destiny,$pDB,$domain);
    $oForm = new paloForm($smarty,$arrFormOrgz);

    if($action=="view"){
        $oForm->setViewMode();
    }else if($action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
    }
    
    //permission
    $smarty->assign("EDIT_RG",in_array('edit',$arrPermission));
    $smarty->assign("CREATE_RG",in_array('create',$arrPermission));
    $smarty->assign("DEL_RG",in_array('delete',$arrPermission));
    
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
    $smarty->assign("id_rg", $idRG);
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("ORGANIZATION_LABEL",_tr("Organization Domain"));
    $smarty->assign("ORGANIZATION",$domain);
    $smarty->assign("SETDESTINATION", _tr("Final Destination"));
        
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("RG Route"), $arrRG);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewRG($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $error = "";
    $continue=true;
    $success=false;

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    $pRG = new paloSantoRG($pDB,$domain);
    
    $arrFormOrgz = createFieldForm(array(),array(),$pDB,$domain);
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
        return viewFormRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $name = getParameter("rg_name");
        if($name==""){
            $error=_tr("Field 'Name' can't be empty.");
            $continue=false;
        }
        
        if($pRG->validateDestine($domain,getParameter("destination"))==false){
            $error=_tr("You must select a default destination.");
            $continue=false;
        }
            
        if($continue){
            //seteamos un arreglo con los parametros configurados
            $arrProp=array();
            $arrProp["rg_name"]=getParameter("rg_name");
            $arrProp["rg_number"]=getParameter("rg_number");
            $arrProp['rg_strategy']=getParameter("rg_strategy");
            $arrProp['rg_time']=getParameter("rg_time");
            $arrProp['rg_alertinfo']=getParameter("rg_alertinfo");
            $arrProp['rg_cid_prefix']=getParameter("rg_cid_prefix");
            $arrProp['rg_recording'] = getParameter("rg_recording");
            $arrProp['rg_moh']=getParameter("rg_moh");
            $arrProp['rg_cf_ignore'] = getParameter("rg_cf_ignore");
            $arrProp['rg_skipbusy'] = getParameter("rg_skipbusy");
            $arrProp['rg_confirm_call'] = getParameter("rg_confirm_call");
            $arrProp['rg_extensions'] = getParameter("rg_extensions");
            $arrProp['rg_pickup'] = getParameter("rg_pickup");
            if($arrProp['rg_confirm_call']=="yes"){
                $arrProp['rg_record_remote']=getParameter("rg_record_remote");
                $arrProp['rg_record_toolate']=getParameter("rg_record_toolate");
            }
            $arrProp['goto']=getParameter("goto");
            $arrProp['destination']=getParameter("destination");
        }

        if($continue){
            $pDB->beginTransaction();
            $success=$pRG->createNewRG($arrProp);
            if($success)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pRG->errMsg;
        }
    }

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Ring Group has been created successfully"));
         //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function saveEditRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $continue=true;
    $success=false;
    $idRG=getParameter("id_rg");
 
    //obtenemos la informacion del ring_group por el id dado, sino existe el ring_group mostramos un mensaje de error
    if(!isset($idRG)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Ring Group"));
        return reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $pRG = new paloSantoRG($pDB,$domain);
    $arrRG = $pRG->getRGById($idRG);
    if($arrRG===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pRG->errMsg));
        return reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else if(count($arrRG)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("RG doesn't exist"));
        return reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        if($pRG->validateDestine($domain,getParameter("destination"))==false){
            $error=_tr("You must select a destination for this ring_group.");
            $continue=false;
        }
        
        if($continue){
            //seteamos un arreglo con los parametros configurados
            $arrProp=array();
            $arrProp["id_rg"]=$idRG;
            $arrProp["rg_name"]=getParameter("rg_name");
            $arrProp["rg_number"]=getParameter("rg_number");
            $arrProp['rg_strategy']=getParameter("rg_strategy");
            $arrProp['rg_time']=getParameter("rg_time");
            $arrProp['rg_alertinfo']=getParameter("rg_alertinfo");
            $arrProp['rg_cid_prefix']=getParameter("rg_cid_prefix");
            $arrProp['rg_recording'] = getParameter("rg_recording");
            $arrProp['rg_moh']=getParameter("rg_moh");
            $arrProp['rg_cf_ignore'] = getParameter("rg_cf_ignore");
            $arrProp['rg_skipbusy'] = getParameter("rg_skipbusy");
            $arrProp['rg_pickup'] = getParameter("rg_pickup");
            $arrProp['rg_confirm_call'] = getParameter("rg_confirm_call");
            $arrProp['rg_extensions'] = getParameter("rg_extensions");
            if($arrProp['rg_confirm_call']=="yes"){
                $arrProp['rg_record_remote']=getParameter("rg_record_remote");
                $arrProp['rg_record_toolate']=getParameter("rg_record_toolate");
            }
            $arrProp['goto']=getParameter("goto");
            $arrProp['destination']=getParameter("destination");
        }

        if($continue){
            $pDB->beginTransaction();
            $success=$pRG->updateRGPBX($arrProp);
            
            if($success)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pRG->errMsg;
        }
    }

    $smarty->assign("id_inbound", $idRG);

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Ring Group has been edited successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function deleteRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    
    $error = "";
    $continue=true;
    $success=false;
    $idRG=getParameter("id_rg");

    //obtenemos la informacion del ring_group por el id dado, sino existe el ring_group mostramos un mensaje de error
    if(!isset($idRG)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Ring Group"));
        return reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $pRG = new paloSantoRG($pDB,$domain);
    $arrRG = $pRG->getRGById($idRG);
    if($arrRG===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pRG->errMsg));
        return reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else if(count($arrRG)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("RG doesn't exist"));
        return reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    

    $pDB->beginTransaction();
    $success = $pRG->deleteRG($idRG);
    if($success)
        $pDB->commit();
    else
        $pDB->rollBack();
    $error .=$pRG->errMsg;

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("The Ring Group was deleted successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($error));
    }

    return reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function generateOptionNum($start, $end){
    $arr = array();
    for($i=$start;$i<=$end;$i++){
        $arr[$i]=$i;
    }
    return $arr;
}

function createFieldForm($goto,$destination,$pDB,$domain)
{
    $pRG=new paloSantoRG($pDB,$domain);
    $strategy = array('ringall'=>'ringall','ringall-prim'=>'ringall-prim','hunt'=>'hunt','hunt-prim'=>'hunt-prim','memoryhunt'=>'memoryhunt','memoryhunt-prim'=>'memoryhunt-prim', 'firstavailable'=>'firstavailable', 'firstnotonphone'=>'firstnotonphone');
    $time = generateOptionNum(1, 60);
    $arrYesNo = array(_tr("yes") => _tr("Yes"), "no" => "No");
    
    $arrRecording=$pRG->getRecordingsSystem($domain);
    $arrMoH=$pRG->getMoHClass($domain);
    
    $recording = array(_tr("none")=>_tr("None"));
    $recording2 = array("default"=>"Default");
    if(is_array($arrRecording)){
        foreach($arrRecording as $key => $value){
            $recording[$key] = $value;
            $recording2[$key] = $value;
        }
    }
    
    $arrMusic=array("ring"=>_tr("Only Ring"));
    if(is_array($arrMoH)){
        foreach($arrMoH as $key => $value){
            $arrMusic[$key] = $value;
        }
    }
    
    $extens=$pRG->getAllDevice($domain);
    $arrExten=array(""=>_tr("--unselected--"));
    if($extens!=false){
        $astMang=AsteriskManagerConnect($errorM);
        $result=$pRG->getCodeByDomain($domain);
        foreach($extens as $value){
            $cidname="";
            if($astMang!=false && $result!=false){
                $cidname=$astMang->database_get("EXTUSER/".$result["code"]."/".$value["exten"], "cidname");
            } 
            $arrExten[$value["exten"]]=isset($cidname)?$cidname." <{$value["exten"]}>":$value["exten"]." ({$value["dial"]})";
        }
    }
    
    $arrFormElements = array("rg_name"  => array("LABEL"             => _tr('Name'),
                                                    "DESCRIPTION"            => _tr("Name"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "rg_number"    => array("LABEL"             => _tr("Number"),
                                                    "DESCRIPTION"            => _tr("Number"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "rg_strategy"  => array("LABEL"             => _tr("Strategy"),
                                                    "DESCRIPTION"            => _tr("Strategy"),
                                                    "REQUIRED"              => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $strategy,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "rg_alertinfo"     => array("LABEL"             => _tr("Alert Info"),
                                                    "DESCRIPTION"            => _tr("RG_alertinfo"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:100px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "rg_cid_prefix"    => array("LABEL"             => _tr("CID Name Prefix"),
                                                    "DESCRIPTION"            => _tr("RG_cidnameprefix"),
                                                    "REQUIRED"               => "no",
                                                     "INPUT_TYPE"            => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:100px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "rg_moh"           => array("LABEL"             => _tr("Music On Hold"),
                                                    "DESCRIPTION"            => _tr("RG_musiconhold"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrMusic,
                                                    "VALIDATION_TYPE"        => "",
                                                    "VALIDATION_EXTRA_PARAM" => ""),  
                            "rg_time"   => array("LABEL"             => _tr("Ring Time"),
                                                    "DESCRIPTION"            => _tr("Ring Time"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"            => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $time,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "goto"      => array("LABEL"             => _tr("Destine"),
                                                    "DESCRIPTION"            => _tr("RG_destine"),
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
                            "rg_cf_ignore"     => array("LABEL"             => _tr("Ignore CF"),
                                                    "DESCRIPTION"            => _tr("RG_ignorecallforward"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "rg_skipbusy"     => array("LABEL"             => _tr("Skip Busy Extensions"),
                                                    "DESCRIPTION"            => _tr("RG_skipbusyextension"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "rg_pickup"     => array("LABEL"             => _tr("Enable Call Pickup"),
                                                    "DESCRIPTION"            => _tr("RG_enablecallpickup"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "rg_confirm_call"     => array("LABEL"           => _tr("Confirm Call"),
                                                    "DESCRIPTION"            => _tr("RG_confirmcall"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""), 
                            "rg_recording"     => array("LABEL"           => _tr("Recording"),
                                                    "DESCRIPTION"            => _tr("RG_recording"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $recording,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""), 
                            "rg_record_remote"     => array("LABEL"           => _tr("Recording Remote"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $recording2,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""), 
                            "rg_record_toolate"     => array("LABEL"           => _tr("Recording Too Late"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $recording2,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""), 
                            "rg_extensions" => array("LABEL"               => _tr("Extensions List"),
                                                    "DESCRIPTION"            => _tr("RG List"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXTAREA",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px;resize:none"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => "",
                                                    "ROWS"                   => "5",
                                                    "COLS"                   => "2"),
                            "pickup_extensions"   => array("LABEL"                => _tr(""),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrExten,
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
        "rg_number"  => array("LABEL"            => _tr("Ring Group Number"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "TEXT",
                        "INPUT_EXTRA_PARAM"      => "",
                        "VALIDATION_TYPE"        => "text",
                        "VALIDATION_EXTRA_PARAM" => ""),
        "rg_name"  => array("LABEL"            => _tr("Ring Group Name"),
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
        return reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
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

    return reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function getAction(){
    global $arrPermission;
    if(getParameter("create_rg"))
        return (in_array('create',$arrPermission))?'new_rg':'report';
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
