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
        case "new_tc":
            $content = viewFormTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_edit":
            $content = viewFormTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveNewTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "reloadAasterisk":
            $content = reloadAasterisk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
                break;
        default: // report
            $content = reportTC($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function reportTC($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error = "";
    $pORGZ = new paloSantoOrganization($pDB);

    $domain=getParameter("organization");
    $domain=empty($domain)?'all':$domain;
    if($credentials['userlevel']!="superadmin"){
        $domain=$credentials['domain'];
    }
    $name=getParameter('name');
    
    $url['menu']=$module_name;
    $url['organization']=$domain;
    $url['name']=$name; //name
    
    $pTC = new paloSantoTC($pDB,$domain);
    $total=$pTC->getNumTC($domain,$name);
    $arrOrgz=array();
    if($credentials['userlevel']=="superadmin"){
        $arrOrgz=array("all"=>"all");
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }
    
    if($total===false){
        $error=$pTC->errMsg;
        $total=0;
    }

    $limit=20;

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $oGrid->setTitle(_tr('Time Conditions List'));
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
    $arrColum[]=_tr("Time Group");
    $arrColum[]=_tr("Destination Match");
    $arrColum[]=_tr("Destination Fail");
    $oGrid->setColumns($arrColum);
    
    $arrTC=array();
    $arrData = array();
    if($total!=0){
        $arrTC = $pTC->getTCs($domain,$name,$limit,$offset);
    }

    if($arrTC===false){
        $error=_tr("Error to obtain Time Conditions").$pTC->errMsg;
        $arrTC=array();
    }

    foreach($arrTC as $tc) {
        $arrTmp=array();
        if($credentials['userlevel']=="superadmin"){
            $arrTmp[] = $arrOrgz[$tc["organization_domain"]];
        }
        $arrTmp[] = "&nbsp;<a href='?menu=$module_name&action=view&id_tc=".$tc['id']."&organization={$tc["organization_domain"]}'>".htmlentities($tc['name'],ENT_QUOTES,"UTF-8")."</a>";
        $arrTmp[] = htmlentities($tc["tg_name"],ENT_QUOTES,"UTF-8"); 
        $arrTmp[] = $tc["destination_m"];
        $arrTmp[] = $tc["destination_f"];
        $arrData[] = $arrTmp;
    }
            
    if($pORGZ->getNumOrganization(array()) >= 1){
        if(in_array('create',$arrPermission)){
            if($credentials['userlevel']=='superadmin'){
                $oGrid->addComboAction("organization_add",_tr("ADD Time Conditions"), array_slice($arrOrgz,1), $selected=null, "create_tc", $onchange_select=null);
            }else{
                $oGrid->addNew("create_tc",_tr("ADD Time Conditions"));
            }   
        }
        if($credentials['userlevel']=='superadmin'){
            $_POST["organization"]=$domain;
            $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true);
        }
        $_POST["name"]=$name; // name
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Time Condition Name")." = ".$name, $_POST, array("name" => "")); 
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

function viewFormTC($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    global $arrPermission;
    $error = "";
    
    $arrRG=array();
    $action = getParameter("action");

    $idTC=getParameter("id_tc");
    if($action=="view" || getParameter("edit") || getParameter("save_edit")){
        if(!isset($idTC)){
            $error=_tr("Invalid Time Conditions");
        }else{
            $domain=getParameter('organization');
            if($credentials['userlevel']!='superadmin'){
                $domain=$credentials['domain'];
            }
            $pTC = new paloSantoTC($pDB,$domain);
            $arrTC = $pTC->getTCById($idTC);
            if($arrTC===false){
                $error=_tr($pTC->errMsg);
            }else if(count($arrTC)==0){
                $error=_tr("TC doesn't exist");
            }else{
                if(getParameter("save_edit"))
                    $arrTC=$_POST;
            }
        }
        
        if($error!=""){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",$error);
            return reportTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{
        if($credentials['userlevel']=='superadmin'){
            if(getParameter("create_tc")){
                $domain=getParameter('organization_add'); //este parametro solo es selecionable cuando es el superadmin quien crea la ruta
            }else
                $domain=getParameter('organization');
        }else{
            $domain=$credentials['domain'];
        }
        
        $pTC = new paloSantoTC($pDB,$domain);
        if(getParameter("create_tc")){
            $arrTC["goto_m"]="";
            $arrTC["goto_f"]="";
        }else
            $arrTC=$_POST; 
    }
    
    $goto=$pTC->getCategoryDefault($domain);
    if($goto===false)
        $goto=array();
    $res1=$pTC->getDefaultDestination($domain,$arrTC["goto_m"]);
    $destiny1=($res1==false)?array():$res1;
    $res2=$pTC->getDefaultDestination($domain,$arrTC["goto_f"]);
    $destiny2=($res2==false)?array():$res2;
    $arrForm = createFieldForm($goto,$destiny1,$destiny2,$pTC->getTimeGroup());
    $oForm = new paloForm($smarty,$arrForm);

    if($action=="view"){
        $oForm->setViewMode();
    }else if(getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
    }
    
    //permission
    $smarty->assign("EDIT_TC",in_array('edit',$arrPermission));
    $smarty->assign("CREATE_TC",in_array('create',$arrPermission));
    $smarty->assign("DEL_TC",in_array('delete',$arrPermission));
    
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("OPTIONS", _tr("Options"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("id_tc", $idTC);
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("ORGANIZATION_LABEL",_tr("Organization Domain"));
    $smarty->assign("ORGANIZATION",$domain);
    $smarty->assign("SETDESTINATION_M", _tr("Destination If Match"));
    $smarty->assign("SETDESTINATION_F", _tr("Destination If Fail"));
    
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("TC Route"), $arrTC);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewTC($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $error = "";
    $continue=true;
    $success=false;

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    $pTC = new paloSantoTC($pDB,$domain);
    
    $goto=$pTC->getCategoryDefault($domain);

    //validations parameters
    $name = getParameter("name");
    if($name==""){
        $error=_tr("Field Name can not be empty.");
        $continue=false;
    }
    
    if($pTC->validateDestine($domain,getParameter("destination_m"))==false){
        $error=_tr("You must select a destination if match");
        $continue=false;
    }
    
    if($pTC->validateDestine($domain,getParameter("destination_f"))==false){
        $error=_tr("You must select a destination if fail");
        $continue=false;
    }
            
    if($continue){
        //seteamos un arreglo con los parametros configurados
        $arrProp=array();
        $arrProp["name"]=getParameter("name");
        $arrProp['id_tg']=getParameter("id_tg");
        $arrProp['goto_m']=getParameter("goto_m");
        $arrProp['destination_m']=getParameter("destination_m");
        $arrProp['goto_f']=getParameter("goto_f");
        $arrProp['destination_f']=getParameter("destination_f");
    }

    if($continue){
        $pDB->beginTransaction();
        $success=$pTC->createNewTC($arrProp);
        if($success)
            $pDB->commit();
        else
            $pDB->rollBack();
        $error .=$pTC->errMsg;
    }

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("TC has been created successfully"));
         //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function saveEditTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $continue=true;
    $success=false;
    $idTC=getParameter("id_tc");

    if(!isset($idTC)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid TC"));
        return reportTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }

    $pTC = new paloSantoTC($pDB,$domain);
    $arrTC = $pTC->getTCById($idTC);
    if($arrTC===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pTC->errMsg));
        return reportTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentialsn);
    }else if(count($arrTC)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("TC doesn't exist"));
        return reportTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        //validations parameters
        $name = getParameter("name");
        if($name==""){
            $error=_tr("Field Name can not be empty.");
            $continue=false;
        }
        
        if($pTC->validateDestine($domain,getParameter("destination_m"))==false){
            $error=_tr("You must select a destination if match");
            $continue=false;
        }
        
        if($pTC->validateDestine($domain,getParameter("destination_f"))==false){
            $error=_tr("You must select a destination if fail");
            $continue=false;
        }
        
        if($continue){
            //seteamos un arreglo con los parametros configurados
            $arrProp=array();
            $arrProp['id']=$idTC;
            $arrProp["name"]=getParameter("name");
            $arrProp['id_tg']=getParameter("id_tg");
            $arrProp['goto_m']=getParameter("goto_m");
            $arrProp['destination_m']=getParameter("destination_m");
            $arrProp['goto_f']=getParameter("goto_f");
            $arrProp['destination_f']=getParameter("destination_f");
        }

        if($continue){
            $pDB->beginTransaction();
            $success=$pTC->updateTCPBX($arrProp);
            if($success)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pTC->errMsg;
        }
    }

    $smarty->assign("id_tc", $idTC);

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("TC has been edited successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB,$pDB2);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function deleteTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $continue=true;
    $success=false;
    $idTC=getParameter("id_tc");

    if(!isset($idTC)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid TC"));
        return reportTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $pTC=new paloSantoTC($pDB,$domain);
    $pDB->beginTransaction();
    $success = $pTC->deleteTC($idTC);
    if($success)
        $pDB->commit();
    else
        $pDB->rollBack();
    $error .=$pTC->errMsg;

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("The TC was deleted successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($error));
    }

    return reportTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}


function createFieldForm($goto,$destination1,$destination2,$time_group)
{
    
    $arrFormElements = array("name"	=> array("LABEL"             => _tr('Name'),
                                                    "DESCRIPTION"            => _tr("Name"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "id_tg" => array("LABEL"               => _tr("Time Group"),
                                                    "DESCRIPTION"            => _tr("TC_timegroup"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $time_group,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "goto_m"   => array("LABEL"             => _tr("Destine"),
                                                    "DESCRIPTION"            => _tr("TC_destinem"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $goto,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""), 
                             "destination_m"   => array("LABEL"             => _tr(""),
                                                    "DESCRIPTION"            => _tr("TC_destinem"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $destination1,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""), 
                             "goto_f"   => array("LABEL"             => _tr("Destine"),
                                                    "DESCRIPTION"            => _tr("TC_destinef"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $goto,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""), 
                             "destination_f"   => array("LABEL"             => _tr(""),
                                                    "DESCRIPTION"            => _tr("TC_destinef"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $destination2,
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
        "name"  => array("LABEL"            => _tr("Time Condition Name"),
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
        return reportTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
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

    return reportTC($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function getAction(){
    global $arrPermission;
    if(getParameter("create_tc"))
        return (in_array('create',$arrPermission))?'new_tc':'report';
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
