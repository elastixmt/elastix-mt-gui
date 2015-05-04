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
  $Id: index.php,v 1.1.1.1 2012/09/07 Rocio Mera rmera@palosanto.com Exp $ */

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
        case "new_conference":
            $content = viewFormConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_edit":
            $content = viewFormConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveNewConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "getConferenceMemb":
            $content = conferenceStatus($smarty,$pDB,$module_name,$arrCredentials);
            break;
        case "inviteCaller":
            $content = inviteCaller($smarty,$pDB,$module_name,$arrCredentials);
            break;
        case "muteCallers":
            $content = muteCallers($smarty,$pDB,$module_name,$arrCredentials);
            break;
        case "kickCallers":
            $content = kickCallers($smarty,$pDB,$module_name,$arrCredentials);
            break;
        case "showCallers":
            $content = showCallers($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "updateShowCallers":
            $content=statusShowCallers($smarty,$pDB,$module_name,$arrCredentials,"updateShowCallers");
            break;
        case "reloadAasterisk":
            $content = reloadAasterisk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default: // report
            $content = reportConference($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function reportConference($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $pORGZ = new paloSantoOrganization($pDB);
    $error = "";
    
    $date=date("Y-m-d H:i");
    $state_conf=getParameter("state_conf");
    $name_conf=getParameter("name_conf");
    $type_conf=getParameter("type_conf");
    
    if(empty($state_conf)){
        $state_conf="all";
    }
        
    if(empty($type_conf)){
        $type_conf="both";
    }
        
    if(is_null($name_conf)){
        $name_conf="";
    }
    
    if($credentials['userlevel']=="superadmin"){
        $domain=getParameter("organization");
        $domain=empty($domain)?'all':$domain;
        
        $arrOrgz=array("all"=>_tr("all"));
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }else{
        $arrOrgz=ARRAY();
        $domain=$credentials['domain'];
    }
    
    $url['menu']=$module_name;
    $url["state_conf"]=$state_conf;
    $url["name_conf"]=$name_conf;
    $url["type_conf"]=$type_conf;
    $url['date']=$date;
    
    $pconference = new paloConference($pDB,$domain);
    $total=$pconference->getTotalConference($domain,$date,$state_conf,$type_conf,$name_conf);
    
	if($total===false){
        $error=$pconference->errMsg;
        $total=0;
    }
    
    $limit=20;
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();

    $end = ($offset+$limit)<=$total ? $offset+$limit : $total;
	
	$oGrid->setTitle(_tr('Conference'));
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    $oGrid->setURL($url);

    //permission
    $delete=in_array("delete_conf",$arrPermission);
    $create=in_array("create_conf",$arrPermission);
    $edit=in_array("edit_conf",$arrPermission);
    $invite_part=in_array("admin_conference_participant",$arrPermission);
    
    if($delete){ 
        $arrColum[]=_tr("");
    }
    $arrColum[]=_tr("Name");
    $arrColum[]=_tr("Room Number");
    $arrColum[]=_tr("Period");
    $arrColum[]=_tr("Participants / MaxUsers");
    $arrColum[]=_tr("Status");
    
    $oGrid->setColumns($arrColum);
           
    $arrData = array();
    $arrconference = array();
    if($total!=0){
        $arrconference=$pconference->getConferesPagging($domain,$date,$limit,$offset,$state_conf,$type_conf,$name_conf);
    }

    $session = getSession();
    if($arrconference===false){
        $error=_tr("Error getting conference data.").$pconference->errMsg;
    }else{
        foreach($arrconference as $conf) {
            $arrTmp=array();
            if($delete){
                $arrTmp[] = "<input type='checkbox' name='confdel[]' value='{$conf['bookid']}'/>"; //delete
            }
            if($edit){ 
                $arrTmp[] = "<a href='?menu=$module_name&action=view&id_conf={$conf['bookid']}&organization={$conf['organization_domain']}'>".htmlentities($conf["name"],ENT_QUOTES,"UTF-8")."</a>"; //name
            }else{
                $arrTmp[]=htmlentities($conf["name"],ENT_QUOTES,"UTF-8");
            }
            
            $arrTmp[]=$conf["ext_conf"]; //roomnumber
            $perid="No Set";
            if(!empty($conf["startTime"]) && $conf["startTime"]!="1900-01-01 12:00:00"){
               $perid=$conf["startTime"]." - ".$conf["endtime"];
            }
            $arrTmp[]=$perid;//period
            
            $max=empty($conf["maxusers"])?"unlimited":$conf["maxusers"];
            $participants="<spam class='conf_memb' id='{$conf['bookid']}'>".$conf["members"]." / $max </spam>";
            $status="<spam class='conf_status'></spam>";
            if($perid!="No Set"){
                $date=time();
                if($date>=strtotime($conf["startTime"]) && $date<=strtotime($conf["endtime"])){
                    if($invite_part){ 
                        $participants="<a href='?menu=$module_name&action=current_conf&id_conf={$conf['bookid']}&organization={$conf['organization_domain']}' class='conf_memb' id='{$conf['bookid']}'>".$conf["members"]." / $max</a>";
                    }
                        $status="<spam class='conf_status' style='color:green'/>"._tr("In Progress")."</spam>";
                }else{
                    if($date<strtotime($conf["startTime"]))
                        $status="<spam class='conf_status'>"._tr("Future")."</spam>";
                    else
                        $status="<spam class='conf_status'>"._tr("Past")."</spam>";
                }
            }else{
                if($invite_part){ 
                    $participants="<a href='?menu=$module_name&action=current_conf&id_conf={$conf['bookid']}&organization={$conf['organization_domain']}' class='conf_memb' id='{$conf['bookid']}'>".$conf["members"]." / $max</a>";
                }
            }
                
            $arrTmp[]=$participants;
            $arrTmp[]=$status;
            $arrData[] = $arrTmp;
            //se usa para comprobar si ha habido cambios en el estado de las conferencias
            $session['conference']["conf_list"][$conf['bookid']]=array($participants,$status);
        }
    }

    //se escribe en session el estado actual de las conferencias
    putSession($session);
    
    //filters
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='report'>");
    if($pORGZ->getNumOrganization(array()) >= 1){
        if($create){
            if($credentials['userlevel']=='superadmin'){
                $oGrid->addComboAction("organization_add",_tr("ADD Conference"), array_slice($arrOrgz,1), $selected=null, "create_conference", $onchange_select=null);
            }else
                $oGrid->addNew("create_conference",_tr("ADD Conference"));
        }
        if($delete){
            $oGrid->deleteList(_tr("Are you sure you wish to delete conference (es)?"),"delete_conference",_tr("Delete"));
        }

        if($credentials['userlevel']=='superadmin'){
            $_POST["organization"]=$domain;
            $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true);
        }
        //arreglo usado para formar los elementos del filtro
        $arrState=array("all"=>_tr("All"),"past"=>_tr("Past Conference"),"current"=>_tr("Current  Conference"),"future"=>_tr("Future Conference"));
        $arrType=array("both"=>_tr("Both"),"yes"=>_tr("Schedule"),"no"=>_tr("No Schedule"));

        $oGrid->addFilterControl(_tr("Filter applied: ")._tr("State")." = ".$arrState[$state_conf], $state_conf, array("state_conf" => "all"),true);
        $oGrid->addFilterControl(_tr("Filter applied: ")._tr("Name")." = ".$name_conf, $name_conf, array("name_conf" => ""));
        $oGrid->addFilterControl(_tr("Filter applied: ")._tr("Type")." = ".$arrType[$type_conf], $type_conf, array("type_conf" => "both"),true);

        $smarty->assign("SHOW", _tr("Show"));
        $arrFormElements = createFieldFilter($arrState,$arrType,$arrOrgz);
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
    $contenidoModulo .="<input type='hidden' name='grid_limit' id='grid_limit' value='$limit'>";
    $contenidoModulo .="<input type='hidden' name='grid_offset' id='grid_offset' value='$offset'>";
    $contenidoModulo .="<input type='hidden' name='conf_action' id='conf_action' value='report'>";
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

function getSession()
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $tmp = $_SESSION;
        session_commit();
    }
    return $tmp;
}

function putSession($data)//data es un arreglo
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $_SESSION = $data;
        session_commit();
    }
}

function getConferenceMemb($smarty,&$pDB,$module_name,$credentials){
    global $arrPermission;
    
    $error=$pagging="";
    $jsonObject=new PaloSantoJSON();
    $change=false;
    //parametros necesarios para obtener las conferencias
    $state_conf=getParameter("state_conf");
    $name_conf=getParameter("name_conf");
    $type_conf=getParameter("type_conf");
    $limit=(int)getParameter("limit");
    $offset=(int)getParameter("offset");
    
    if($credentials['userlevel']=='superadmin'){
        $domain=getParameter('organization');
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/",$domain)){
            $domain=null;
        }
    }else{
        $domain=$credentials['domain'];
    }
    
    $date=date("Y-m-d H:i");
    $pConf = new paloConference($pDB,$domain);
    $conf=$pConf->getConferesPagging($domain,$date,$limit,$offset,$state_conf,$type_conf,$name_conf);
    
    $data=array();
    if($conf==false){
        if($conf===false){
            $jsonObject->set_error($pDB->errMsg);
            $change=true;
        }
    }else{
        //permission
        $invite_part=in_array("admin_conference_participant",$arrPermission);
        
        $date=time();
        foreach($conf as $value){
            $max=empty($value["maxusers"])?"unlimited":$value["maxusers"];
            $participants="<spam class='conf_memb' id='{$value['bookid']}'>".$value["members"]." / $max </spam>";
            $status="<spam class='conf_status'></spam>";
            if(!empty($value["startTime"]) && $value["startTime"]!="1900-01-01 12:00:00"){            
                if($date>=strtotime($value["startTime"]) && $date<=strtotime($value["endtime"])){
                    if($invite_part){ 
                        $participants="<a href='?menu=$module_name&action=current_conf&id_conf={$value['bookid']}&organization={$value['organization_domain']}' class='conf_memb' id='{$value['bookid']}'>".$value["members"]." / $max</a>";
                    }
                        $status="<spam class='conf_status' style='color:green'>"._tr("In Progress")."</spam>";
                }else{
                    if($date<strtotime($value["startTime"]))
                        $status="<spam class='conf_status'>"._tr("Future")."</spam>";
                    else
                        $status="<spam class='conf_status'>"._tr("Past")."</spam>";
                }
            }else{
                if($invite_part){ 
                    $participants="<a href='?menu=$module_name&action=current_conf&id_conf={$value['bookid']}&organization={$value['organization_domain']}' class='conf_memb' id='{$value['bookid']}'>".$value["members"]." / $max</a>";
                }
            }
            $data[$value["bookid"]]["count"]=$participants;
            $data[$value["bookid"]]["status"]=$status;
        }
    }
    
    $result=thereChanges($data);
    if(is_array($result) && count($result)>0){
        $jsonObject->set_status("CHANGED");
        $jsonObject->set_message($result);
        $change=true;
    }else{
        $jsonObject->set_status("NO CHANGED");
    }
    
    return array('there_was_change'=>$change,"data"=>$jsonObject->createJSON());
}

function conferenceStatus($smarty,&$pDB,$module_name,$credentials){
    $executed_time = 1; //en segundos
    $max_time_wait = 30; //en segundos
    $data          = null;

    $i = 1;
    while(($i*$executed_time) <= $max_time_wait){
        $return = getConferenceMemb($smarty,$pDB,$module_name,$credentials);
        $data   = $return['data'];
        if($return['there_was_change']){
            break;
        }
        $i++;
        sleep($executed_time); //cada $executed_time estoy revisando si hay algo nuevo....
    }
    return $data;
}


function thereChanges($data){
    $session = getSession();
    $arrData = array();
    
    if (isset($session['conference']["conf_list"]) && is_array($session['conference']["conf_list"])){
        $arrData = $session['conference']["conf_list"];
    }
    $arraResult = array();
    foreach($arrData as $bookid => $value){
        $members = $value[0];
        $status = $value[1];
        if(isset($data[$bookid])){
            if((isset($data[$bookid]["count"]) && $data[$bookid]["count"] != $members) || $data[$bookid]["status"] != $status){
                $arraResult[$bookid]["count"] = $data[$bookid]["count"];
                $arraResult[$bookid]["status"] = $data[$bookid]["status"];
                $arrData[$bookid][0] = $data[$bookid]["count"];
                $arrData[$bookid][1] = $data[$bookid]["status"];
            }
        }
    }
    
    $session['conference']["conf_list"] = $arrData;
    putSession($session);
    return $arraResult;
}

function viewFormConference($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    global $arrPermission;
    $error = "";
    $action = getParameter("action");

    $id_conf=getParameter("id_conf");
    if($action=="view" || getParameter("edit") || getParameter("save_edit")){
        if(!isset($id_conf)){
            $error=_tr("Invalid conference");
        }else{
            $domain=getParameter('organization');
            if($credentials['userlevel']!='superadmin'){
                $domain=$credentials['domain'];
            }
            
            $pConf = new paloConference($pDB,$domain);
            $Conf = $pConf->getConferenceById($id_conf);
                        
            if($Conf==false){
                $error=$pConf->errMsg;
            }else{
                $Conf["confno"]=$Conf["ext_conf"];
                $smarty->assign("CONFNO",$Conf["ext_conf"]);
                if(!empty($Conf["startTime"]) && $Conf["startTime"]!="1900-01-01 12:00:00"){
                    $smarty->assign("SCHEDULE","on");
                    $Conf["schedule"]="on";
                }
                if(getParameter("save_edit"))
                    $Conf=$_POST;
                else{
                    if(!empty($Conf["startTime"]) && $Conf["startTime"]!="1900-01-01 12:00:00"){
                        $elap=strtotime($Conf["endtime"])-strtotime($Conf["startTime"]);
                        $Conf["start_time"]=substr($Conf["startTime"],0,-3);
                        $Conf["duration"]=floor($elap/3600);
                        $Conf["duration_min"]=floor(fmod($elap,3600)/60);
                    }
                    
                    $Conf["record_conf"]=(empty($Conf["recordingformat"]))?'no':$Conf["recordingformat"];
                    
                    //adminopts
                    preg_match_all("/^aAs(i){0,1}(r){0,1}(M\(([[:alnum:]_]+)\)){0,1}(G\((.*)\)){0,1}$/",$Conf["adminopts"],$match);
                    $Conf["moderator_options_1"]=empty($match[1][0])?"off":"on";
                    $Conf["moderator_options_2"]=empty($match[2][0])?"off":"on";
                    
                    if(empty($match[3][0])){
                        $Conf["moh"]="";
                    }else{
                        $Conf["moh"]=$match[4][0];
                    }
                    if(empty($match[5][0])){
                        $Conf["announce_intro"]="";
                    }else{
                        $Conf["announce_intro"]=$Conf["intro_record"];
                    }
                    
                    //useropts
                    preg_match_all("/^(i){0,1}(m){0,1}(w){0,1}(r){0,1}(M\(([[:alnum:]_]+)\)){0,1}(G\((.*)\)){0,1}$/",$Conf["opts"],$matchu);
                    $Conf["user_options_1"]=empty($matchu[1][0])?"off":"on";
                    $Conf["user_options_2"]=empty($matchu[2][0])?"off":"on";
                    $Conf["user_options_3"]=empty($matchu[3][0])?"off":"on";
                    $Conf["user_options_4"]=empty($matchu[4][0])?"off":"on";
                }
            }  
        }
    }else{
        if($credentials['userlevel']=='superadmin'){
            if(getParameter("create_conference")){
                $domain=getParameter('organization_add'); //este parametro solo es selecionable cuando es el superadmin quien crea la ruta
            }else
                $domain=getParameter('organization');
        }else{
            $domain=$credentials['domain'];
        }
        $pConf = new paloConference($pDB,$domain);
        $domain=$pConf->getDomain();
        if(empty($domain)){
            $error=_tr("Invalid Organization");
        }else{
            $smarty->assign("SCHEDULE","on");
                //para que se muestren los destinos
            if(getParameter("create_conference")){
                $Conf["schedule"]="off";
                $Conf["duration"]="1";
                $Conf["duration_min"]="0";
                $Conf['start_time'] = date("Y-m-d H:i",strtotime(date("Y-m-d H:i")." + 5 minutes"));
            }else{
                $Conf=$_POST;
            }
        }
    }
    
    if($error!=""){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Action"));
        return reportRG($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $arrRecording=$pConf->getRecordingsSystem($domain);
    $arrForm = createFieldForm($arrRecording,$pConf->getMoHClass($domain));
    $oForm = new paloForm($smarty,$arrForm);

    //permission
    $smarty->assign("EDIT_CONF",in_array('edit_conf',$arrPermission));
    
	if($action=="view"){
        $oForm->setViewMode();
    }else if(getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
    }

    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("id_conf", $id_conf);
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("ORGANIZATION_LABEL",_tr("Organization Domain"));
    $smarty->assign("ORGANIZATION",$domain);
    $smarty->assign("announce", _tr("Announce Join/Leave"));
    $smarty->assign("record", _tr("Record"));
    $smarty->assign("listen_only", _tr("Listen Only"));
    $smarty->assign("wait_for_leader", _tr("Wait for Leader"));
    
    
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("Conference"), $Conf);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewConference($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $error = "";
    $success = false;

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien crea la ruta
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }

    $pConf = new paloConference($pDB,$domain);
    $domain=$pConf->getDomain();
    if(empty($domain)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Organization"));
        return reportConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $arrForm = createFieldForm(array(),array());
    $oForm = new paloForm($smarty,$arrForm);

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
        return viewFormConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        //seteamos un arreglo con los parametros configurados
        $arrProp=array();
        $arrProp["name"]=getParameter("name");
        $arrProp['confno']=getParameter("confno");
        $arrProp['adminpin']=getParameter("adminpin");
        $arrProp['pin']=getParameter("pin");
        $arrProp['maxusers']=getParameter("maxusers");
        $arrProp['schedule']=getParameter("schedule");
        $arrProp['start_time']=getParameter("start_time");
        $arrProp['duration']=getParameter("duration");
        $arrProp['duration_min']=getParameter("duration_min");
        $arrProp['announce_intro']=getParameter("announce_intro");
        $arrProp['moh']=getParameter("moh");
        $arrProp['record_conf']=getParameter("record_conf");
        $arrProp['moderator_options_1']=getParameter("moderator_options_1"); //announce join/leave
        $arrProp['moderator_options_2']=getParameter("moderator_options_2"); //record
        $arrProp['user_options_1']=getParameter("user_options_1"); //announce join/leave
        $arrProp['user_options_2']=getParameter("user_options_2"); //mute
        $arrProp['user_options_3']=getParameter("user_options_3"); //waitlider
        $arrProp['user_options_4']=getParameter("user_options_4"); //record
        
        if($arrProp['schedule']=="on"){
            if(!preg_match("/^(([1-2][0,9][0-9][0-9])-((0[1-9])|(1[0-2]))-((0[1-9])|([1-2][0-9])|(3[0-1]))) (([0-1][0-9]|2[0-3]):[0-5][0-9])$/",$arrProp['start_time']))
                $error=_tr("Invalid Format Start Time YYYY-MM-DD HH:MM");
            else{
                if(strtotime($arrProp['start_time']."+ 1 minutes")<time()){
                    $error=_tr("Start Time can't less than current time");
                }
            } 
        }
        
        if($arrProp['user_options_3']=="on" && $arrProp['adminpin']==""){
            $error=_tr("Field 'Moderator PIN' can't be empty if feature 'Wait Lider' is on");
        }
        
        if($error==""){
            $pDB->beginTransaction();
            $success=$pConf->createNewConf($arrProp);
            if($success)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pConf->errMsg;
        }
    }

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("conference has been created successfully."));
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function saveEditConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error="";
    $success=false;

    $id_conf=getParameter("id_conf");

    $domain=getParameter('organization');
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $pConf=new paloConference($pDB,$domain);
    $conference=$pConf->getConferenceById($id_conf);
    if($conference==false){
        $error=$pConf->errMsg;
    }elseif($conference["members"]!="0"){
        $error=_tr("Conference can't be edited because It has already started");
    }else{
        //seteamos un arreglo con los parametros configurados
        $arrProp=array();
        $arrProp["name"]=getParameter("name");
        $arrProp["id_conf"]=$id_conf;
        $arrProp['adminpin']=getParameter("adminpin");
        $arrProp['pin']=getParameter("pin");
        $arrProp['maxusers']=getParameter("maxusers");
        $arrProp['announce_intro']=getParameter("announce_intro");
        $arrProp['moh']=getParameter("moh");
        $arrProp['record_conf']=getParameter("record_conf");
        $arrProp['moderator_options_1']=getParameter("moderator_options_1"); //announce join/leave
        $arrProp['moderator_options_2']=getParameter("moderator_options_2"); //record
        $arrProp['user_options_1']=getParameter("user_options_1"); //announce join/leave
        $arrProp['user_options_2']=getParameter("user_options_2"); //mute
        $arrProp['user_options_3']=getParameter("user_options_3"); //waitlider
        $arrProp['user_options_4']=getParameter("user_options_4"); //record
        $arrProp['schedule']=getParameter("schedule");
        
        if($arrProp['schedule']=="on"){
            $arrProp['start_time']=getParameter("start_time");
            $arrProp['duration']=getParameter("duration");
            $arrProp['duration_min']=getParameter("duration_min");
            if(!preg_match("/^(([1-2][0,9][0-9][0-9])-((0[1-9])|(1[0-2]))-((0[1-9])|([1-2][0-9])|(3[0-1]))) (([0-1][0-9]|2[0-3]):[0-5][0-9])$/",$arrProp['start_time']))
                $error=_tr("Invalid Format Start Time YYYY-MM-DD HH:MM");
            else{
                if(strtotime($arrProp['start_time']."+ 1 minutes")<time()){
                    $error=_tr("Start Time can't less than current time");
                }
            }
        }
        
        if($arrProp['user_options_3']=="on" && $arrProp['adminpin']==""){
            $error=_tr("Field 'Moderator PIN' can't be empty if feature 'Wait Lider' is on");
        }
        
        if($error==""){
            $pDB->beginTransaction();
            $success=$pConf->updateConference($arrProp);
            if($success)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pConf->errMsg;
        }
    }

    $smarty->assign("id_conf", $id_conf);

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("conference has been created successfully."));
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function deleteConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error=$del=$msg="";
    //conexion elastix.db

    $domain=null;
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials["domain"];
    }
    
    $arrConference=getParameter("confdel");
    if(!is_array($arrConference) || count($arrConference)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("You must select at least one conference"));
        return reportConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConference, $credentials);
    }
    
    $pConf=new paloConference($pDB,$domain);
    $succes=$pConf->deleteConference($arrConference);
    if($succes){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message","Conference(s) were deleted successfully");
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Conference(s) could not be deleted.")." ".$pConf->errMsg);
    }
    return reportConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function showCallers($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    global $arrPermission;
    $error="";
    $success=false;

    
    $id_conf=getParameter("id_conf");
    $domain=getParameter('organization');
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $pConf=new paloConference($pDB,$domain);
    $conference=$pConf->getConferenceById($id_conf);
    if($conference==false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$pConf->errMsg);
        return reportConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $date=time();
        if(($date>=strtotime($conference["startTime"]) && $date<=strtotime($conference["endtime"])) 
            || $conference["startTime"]=="1900-01-01 12:00:00"){
            $room=$conference["confno"];
            $total=$conference["members"];
        }else{
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Conference out of Time"));
            return reportConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }
        
    $limit=20;
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end = ($offset+$limit)<=$total ? $offset+$limit : $total;
    
    $oGrid->setTitle(_tr('Conference').": {$conference['name']} ({$conference['ext_conf']})");
    //$oGrid->setIcon('url de la imagen');
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    $oGrid->setURL($url = "?menu=$module_name&action=current_conf&id_conf=$id_conf");

    //permission
    $invite_part=in_array("admin_conference_participant",$arrPermission);
    
    $arrColum=array(); 
    $arrColum[]=_tr("Orden Join");
    $arrColum[]=_tr("CallerId");
    $arrColum[]=_tr("Time in Conference");
    $arrColum[]=_tr("Mode");
    if($invite_part){
        $arrColum[]="<input type='button' name='mute_caller' value="._tr("Mute")." class='button' onclick='javascript:muteCaller()'/>";
        $msgKill=_tr("Are you sure you wish to Kick all caller (s)?");
        $arrColum[]="<input type='button' name='kick_caller' value="._tr("Kick")." class='button' onclick=\"javascript:kickCaller('$msgKill');\"/>";
    }
    $oGrid->setColumns($arrColum);
           
    $arrData = array();
    $arrMemb = array();
    if($total!=0){
        $arrMemb=$pConf->ObtainCallers($room);
    }

    $session = getSession();
    if($arrMemb===false){
        $error=_tr("Error getting conference data.").$pConf->errMsg;
    }else{
        $membPagg=array_slice($arrMemb,$offset,$limit);
        foreach($membPagg as $memb) {
            $arrTmp=array();
            $arrTmp[0] = $memb['userId'];
            $arrTmp[1] = trim($memb['callerId']);
            $arrTmp[2] = $memb['duration'];
            $arrTmp[3] = (empty($memb['mode']))?"user":"admin";
            if($invite_part){
                $status = strstr($memb['status'], "Muted"); //falso si no encuentra la palabra en el arreglo
                $checked = (empty($status))?"":"checked";
                $arrTmp[4] = "<input type='checkbox' name=mute_{$memb['userId']} class='conf_mute' $checked>";
                $arrTmp[5] = "<input type='checkbox' name=kick_{$memb['userId']} class='conf_kick'>";
            }
            $arrData[] = $arrTmp;
            //se usa para comprobar si ha habido cambios en el estado de las conferencias
            $session['conference']["current_conf"][$memb['userId']]=$memb['callerId'];
        }
    }
    
    //se escribe en session el estado actual de las conferencias
    if(!isset($session['conference']["current_conf"]))
        $session['conference']["current_conf"]=array();
    putSession($session);
    
    //filters
    $extens=$pConf->getAllDevice($domain);
    $arrExten=array(""=>"--unselected--");
    if($extens!=false){
        $astMang=AsteriskManagerConnect($errorM);
        $result=$pConf->getCodeByDomain($domain);
        foreach($extens as $value){
            $cidname="";
            if($astMang!=false && $result!=false){
                $cidname=$astMang->database_get("EXTUSER/".$result["code"]."/".$value["exten"], "cidname");
            } 
            $arrExten[$value["dial"]]=isset($cidname)?$cidname." <{$value["exten"]}>":$value["exten"]." ({$value["dial"]})";
        }
    }
    
    if($invite_part){
        $oGrid->addComboAction("invite_caller",_tr("Invite Caller"),$arrExten,"Invite Caller to Conference", "invite_caller", "javascript:inviteCaller()");
        $oGrid->addButtonAction("kick_all", $alt="Kick All Callers", "../web/_common/images/delete5.png", "javascript:kickAll('$msgKill')");
        $oGrid->addButtonAction("mute_all", $alt="Mute All Callers", null, "javascript:muteAll()");
    }
    
    if($error!=""){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",$error);
    }
    
    $contenidoModulo = $oGrid->fetchGrid(array(), $arrData);
    $contenidoModulo .="<input type='hidden' name='id_conf' id='id_conf' value='$id_conf'>";
    $contenidoModulo .="<input type='hidden' name='organization' id='organization' value='$domain'>";
    $contenidoModulo .="<input type='hidden' name='grid_limit' id='grid_limit' value='$limit'>";
    $contenidoModulo .="<input type='hidden' name='grid_offset' id='grid_offset' value='$offset'>";
    $contenidoModulo .="<input type='hidden' name='conf_action' id='conf_action' value='showCallers'>";
    return $contenidoModulo;
}

function statusShowCallers($smarty,&$pDB,$module_name,$credentials,$function){
    $executed_time = 1; //en segundos
    $max_time_wait = 15; //en segundos
    $data          = null;

    $i = 1;
    while(($i*$executed_time) <= $max_time_wait){
        $return = $function($smarty,$pDB,$module_name,$credentials);
        $data   = $return['data'];
        if($return['there_was_change']){
            break;
        }
        $i++;
        sleep($executed_time); //cada $executed_time estoy revisando si hay algo nuevo....
    }
    return $data;
}

function updateShowCallers($smarty,&$pDB,$module_name,$credentials){
    global $arrPermission;
    
    $id_conf=getParameter("id_conf");
    $offset=getParameter("offset");
    $limit=getParameter("limit");
    $error="";
    $change=false;
    
    $id_conf=getParameter("id_conf");
    $domain=getParameter('organization');
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    $pConf=new paloConference($pDB,$domain);
    $conference=$pConf->getConferenceById($id_conf);
    
    if($conference==false){
        $error=_tr($pConf->errMsg);
    }else{
        //permission
        $invite_part=in_array("admin_conference_participant",$arrPermission);
            
        $room=$conference["confno"];
        $date=time();
        if(($date>=strtotime($conference["startTime"]) && $date<=strtotime($conference["endtime"])) || $conference["startTime"]=="1900-01-01 12:00:00"){
            $total=$conference["members"];    
            $arrData = array();
            $arrMemb = array();
            $data = array();
            if($total!=0){
                $arrMemb=$pConf->ObtainCallers($room);
                if($arrMemb===false){
                    $error=_tr("Error getting conference data.").$pConf->errMsg;
                }else{
                    $membPagg=array_slice($arrMemb,$offset,$limit);
                    foreach($membPagg as $memb) {
                        $arrTmp=array();
                        $arrTmp[0] = $memb['userId'];
                        $arrTmp[1] = trim($memb['callerId']);
                        $arrTmp[2] = $memb['duration'];
                        $arrTmp[3] = (empty($memb['mode']))?"user":"admin";
                        $status = strstr($memb['status'], "Muted"); //falso si no encuentra la palabra en el arreglo
                        $checked = (empty($status))?"":"checked";
                        if($invite_part){
                            $arrTmp[4] = "<input type='checkbox' name=mute_{$memb['userId']} class='conf_mute' $checked>";
                            $arrTmp[5] = "<input type='checkbox' name=kick_{$memb['userId']} class='conf_kick'>";
                        }
                        $arrData[] = $arrTmp;
                        $data[$memb['userId']]=$memb['callerId'];
                    }
                }
            }
        }else{
            $error=_tr("Conference out of Time");
        }
    }
    
    $jsonObject=new PaloSantoJSON();
    
    if($error==""){
        if(thereChangesShowCallers($data)==true){
            $jsonObject->set_status("CHANGED");
            $jsonObject->set_message($arrData);
            $change=true;
        }else{
            $jsonObject->set_status("NO CHANGED");
        }
    }else{
        $jsonObject->set_error($error);
        $change=true;
    }
    
    return array('there_was_change'=>$change,"data"=>$jsonObject->createJSON());
}

function thereChangesShowCallers($data){
    $session = getSession();
    $arrData = array();
    $flag=false;
    
    if (isset($session['conference']["current_conf"]) && is_array($session['conference']["current_conf"])){
        $arrData = $session['conference']["current_conf"];
    }
    $arraResult = array();
    if(count($data)!=count($arrData))
        $flag=true;
    else{
        //data[userid]=callerid
        foreach($data as $userid => $value){
            if(isset($arrData[$userid])){
                if($arrData[$userid]!=$value){
                    $flag=true;
                    break;
                }
            }else{
                $flag=true;
                break;
            }
        }
    }
    
    $session['conference']["current_conf"] = $data;
    putSession($session);
    return $flag;
}

function inviteCaller($smarty,&$pDB,$module_name,$credentials){
    global $arrPermission;
    $exten=getParameter("exten");
    $id_conf=getParameter("id_conf");
    $domain=getParameter('organization');
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $jsonObject=new PaloSantoJSON();
    
    //permission
    $invite_part=in_array("admin_conference_participant",$arrPermission);
        
    if(is_null($exten) || $exten==""){
        $jsonObject->set_error(_tr("Invalid Exten"));
    }elseif(!$invite_part){
        $jsonObject->set_error(_tr("You are not authorized to perform this action"));
    }else{
        $pConf=new paloConference($pDB,$domain);
        $conference=$pConf->getConferenceById($id_conf);
        if($conference==false){
            $jsonObject->set_error(_tr($pConf->errMsg));
        }else{
            $room=$conference["confno"];
            $room_exten=$conference["ext_conf"];
            $callerId = _tr('Conference'). "<{$conference["ext_conf"]}>";
            $date=time();
            if(($date>=strtotime($conference["startTime"]) && $date<=strtotime($conference["endtime"])) || $conference["startTime"]=="1900-01-01 12:00:00"){
                $result=$pConf->InviteCaller($room, $room_exten, $exten, $callerId);
                if($result==false)
                    $jsonObject->set_error(_tr("Exten couldn't be added to the conference"));
                else
                    $jsonObject->set_message(_tr("Exten $exten has been invited"));
            }else{
                $jsonObject->set_error(_tr("Conference out of Time"));
            }
        }
    }
    return $jsonObject->createJSON();
}

function muteCallers($smarty,&$pDB,$module_name,$credentials){
    global $arrPermission;
    $id_conf=getParameter("id_conf");
    $domain=getParameter('organization');
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    $type_mute=getParameter("type");
    $jsonObject=new PaloSantoJSON();
    
    //permission
    $invite_part=in_array("admin_conference_participant",$arrPermission);
        
    if(!$invite_part){
        $jsonObject->set_error(_tr("You are not authorized to perform this action"));
    }else{
        $pConf=new paloConference($pDB,$domain);
        $conference=$pConf->getConferenceById($id_conf);
        if($conference==false){
            $jsonObject->set_error(_tr($pConf->errMsg));
        }else{
            $room=$conference["confno"];
            $date=time();
            if(($date>=strtotime($conference["startTime"]) && $date<=strtotime($conference["endtime"])) || $conference["startTime"]=="1900-01-01 12:00:00"){
                if($type_mute=="all")
                    $result=$pConf->MuteCaller($room, "all", "on");
                else{
                    $keys=array_keys($_POST);
                    foreach($keys as $value){
                        if(preg_match("/^mute_[0-9]+$/",$value)){
                            $userid=substr($value,5);
                            $result=$pConf->MuteCaller($room, $userid,$_POST[$value]);
                        }
                    }
                }
                $jsonObject->set_message(_tr("Changes has been applied"));
            }else{
                $jsonObject->set_error(_tr("Conference out of Time"));
            }
        }
    }
    return $jsonObject->createJSON();
}

function KickCallers($smarty,&$pDB,$module_name,$credentials){
    global $arrPermission;
    $id_conf=getParameter("id_conf");
    $domain=getParameter('organization');
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    $type_kick=getParameter("type");
    $jsonObject=new PaloSantoJSON();
    
    //permission
    $invite_part=in_array("admin_conference_participant",$arrPermission);
        
    if(!$invite_part){
        $jsonObject->set_error(_tr("You are not authorized to perform this action"));
    }else{
        $pConf=new paloConference($pDB,$domain);
        $conference=$pConf->getConferenceById($id_conf);
        if($conference==false){
            $jsonObject->set_error(_tr($pConf->errMsg));
        }else{
            $room=$conference["confno"];
            $date=time();
            if(($date>=strtotime($conference["startTime"]) && $date<=strtotime($conference["endtime"])) || $conference["startTime"]=="1900-01-01 12:00:00"){
                if($type_kick=="all")
                    $result=$pConf->KickCaller($room, "all");
                else{
                    $keys=array_keys($_POST);
                    foreach($keys as $value){
                        if(preg_match("/^kick_[0-9]+$/",$value)){
                            $result=$pConf->KickCaller($room, $_POST[$value]);
                        }
                    }
                }
                $jsonObject->set_message(_tr("Changes has been applied").print_r($result));
            }else{
                $jsonObject->set_error(_tr("Conference out of Time"));
            }
        }
    }
    return $jsonObject->createJSON();
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
        return reportConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
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

    return reportConference($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function createFieldForm($recordings,$music){
    $arrMusic=array(""=>_tr("--no music--"));
    if(is_array($music)){
        foreach($music as $key => $value){
            $arrMusic[$key]=$value;
        }
    }
    $arrRecording=array(""=>_tr("--no announcement--"));
    if(is_array($recordings)){
        foreach($recordings as $key => $value){
            $arrRecording[$key]=$value;
        }
    }
    
    $arrMonitor=array("no"=>_tr("No"),"gsm"=>"gsm","wav"=>"wav","wav49"=>"wav49");
    
    $arrFields =       array("name"  => array("LABEL"              => _tr('Conference Name'),
                                                     "DESCRIPTION"            => _tr("CONF_name"),
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "text",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                             "confno" => array("LABEL"              => _tr('Conference Number'),
                                                     "DESCRIPTION"            => _tr("CONF_numconference"),
                                                     "REQUIRED"               => "yes",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "adminpin"     => array("LABEL"              => _tr('Moderator PIN'),
                                                     "DESCRIPTION"            => _tr("CONF_pinmoderator"),
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "moderator_options_1" => array("LABEL"            => _tr('Moderator Options'),
                                                     "DESCRIPTION"            => _tr("CONF_moderatoroptions"),
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "CHECKBOX",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "moderator_options_2" => array("LABEL"            => "",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "CHECKBOX",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "pin"             => array("LABEL"                => _tr('User PIN'),
                                                     "DESCRIPTION"            => _tr("CONF_pinuser"),
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "user_options_1"    => array("LABEL"              => _tr('User Options'),
                                                     "DESCRIPTION"            => _tr("CONF_useroptions"),
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "CHECKBOX",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "user_options_2"    => array("LABEL"              => "",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "CHECKBOX",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "user_options_3"    => array("LABEL"              => "",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "CHECKBOX",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "user_options_4"    => array("LABEL"              => "",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "CHECKBOX",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "start_time"        => array("LABEL"              => _tr('Start Time'),
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "DATE",
                                                     "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%Y-%m-%d %H:%M","TIMEFORMAT" => "24"),
                                                     "VALIDATION_TYPE"        => "",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "duration"          => array("LABEL"              => _tr('Duration (HH:MM)'),
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"2"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "duration_min"      => array("LABEL"              => "",
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:30px;text-align:center","maxlength" =>"2"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "maxusers"  => array("LABEL"              => _tr('Max Participants'),
                                                     "DESCRIPTION"            => _tr("CONF_maxparticipates"),
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "TEXT",
                                                     "INPUT_EXTRA_PARAM"      => array("style" => "width:50px;"),
                                                     "VALIDATION_TYPE"        => "numeric",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "announce_intro"    => array("LABEL"              => _tr('Join Conference Message'),
                                                     "DESCRIPTION"            => _tr("CONF_joinconferencemessage"),
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "SELECT",
                                                     "INPUT_EXTRA_PARAM"      => $arrRecording,
                                                     "VALIDATION_TYPE"        => "text",
                                                     "VALIDATION_EXTRA_PARAM" => ""), 
                            "moh"              => array("LABEL"              => _tr('Music on Hold'),
                                                     "DESCRIPTION"            => _tr("CONF_musiconhold"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrMusic,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "schedule"        => array("LABEL"              => "Schedule Conference",
                                                     "DESCRIPTION"            => _tr("CONF_scheduleconference"),
                                                     "REQUIRED"               => "no",
                                                     "INPUT_TYPE"             => "CHECKBOX",
                                                     "INPUT_EXTRA_PARAM"      => "",
                                                     "VALIDATION_TYPE"        => "",
                                                     "VALIDATION_EXTRA_PARAM" => ""),
                            "record_conf"              => array("LABEL"      => _tr('Record Call'),
                                                     "DESCRIPTION"            => _tr("CONF_recordcall"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrMonitor,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(wav|no|gsm|wav49){1}$"),
                            );
	return $arrFields;
}



function createFieldFilter($arrState,$arrType,$arrOrgz){
    $arrFormElements = array("state_conf" => array("LABEL"                => _tr("State"),
                                                   "REQUIRED"               => "yes",
                                                   "INPUT_TYPE"             => "SELECT",
                                                   "INPUT_EXTRA_PARAM"      => $arrState,
                                                   "VALIDATION_TYPE"        => "text",
                                                   "VALIDATION_EXTRA_PARAM" => ""),
                            "name_conf"   => array("LABEL"                 => _tr("Name"),
                                                   "REQUIRED"               => "no",
                                                   "INPUT_TYPE"             => "TEXT",
                                                   "INPUT_EXTRA_PARAM"      => array("id" => "name_conf"),
                                                   "VALIDATION_TYPE"        => "text",
                                                   "VALIDATION_EXTRA_PARAM" => ""),
                            "type_conf" => array("LABEL"                => _tr("Type"),
                                                  "REQUIRED"               => "yes",
                                                  "INPUT_TYPE"             => "SELECT",
                                                  "INPUT_EXTRA_PARAM"      => $arrType,
                                                  "VALIDATION_TYPE"        => "text",
                                                  "VALIDATION_EXTRA_PARAM" => ""),
                            "organization"  => array("LABEL"         => _tr("Organization"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrOrgz,
                                                    "VALIDATION_TYPE"        => "domain",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            );
    return $arrFormElements;
}

function getAction(){
    global $arrPermission;
    if(getParameter("create_conference"))
        return (in_array('create_conf',$arrPermission))?'new_conference':'report';
    else if(getParameter("save_new")) //Get parameter by POST (submit)
        return (in_array('create_conf',$arrPermission))?'save_new':'report';
    else if(getParameter("save_edit"))
        return (in_array('edit_conf',$arrPermission))?'save_edit':'report';
    else if(getParameter("edit"))
        return (in_array('edit_conf',$arrPermission))?'view_edit':'report';
    else if(getParameter("delete_conference"))
        return (in_array('delete_conf',$arrPermission))?'delete':'report';
    else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view";
    else if(getParameter("action")=="getConferenceMemb")
        return "getConferenceMemb";
    else if(getParameter("action")=="current_conf")
        return "showCallers";
    else if(getParameter("action")=="inviteCaller")
        return "inviteCaller";
    else if(getParameter("action")=="muteCallers")
        return "muteCallers";
    else if(getParameter("action")=="kickCallers")
        return "kickCallers";
    else if(getParameter("action")=="updateShowCallers")
        return "updateShowCallers";
    else if(getParameter("action")=="reloadAsterisk")
        return "reloadAasterisk";
    else
        return "report"; //cancel
}
?>
