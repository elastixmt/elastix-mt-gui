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

//TODO: la seccion en la que se asignan las organizaciones que tienen permitida salir por la troncal
// debe ser impletada en un funcion aparte yno dentro de edit trunk
// el proposito de esto es evitar hacer un dialplan reload innecesario
// el cambiar las organizaciones permitidas no necesita madar a recargar el plan de marcado

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
        case "new_trunk":
            $content = viewFormTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_edit":
            $content = viewFormTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "get_num_calls":
            $content = get_num_calls($smarty,$pDB,$arrCredentials);
            break;
        case "actDesactTrunk":
            $content = actDesactTrunk($smarty,$pDB);
            break;
        default: // report
            $content = reportTrunks($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;
}

function reportTrunks($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $pTrunk = new paloSantoTrunk($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $error = "";
    
    $domain=getParameter("organization");
    $technology=getParameter("technology");
    $status=getParameter("status");
    
    $url['menu']=$module_name;
    $url['organization']=$domain;
    $url['technology']=$technology;
    $url['status']=$status;
    
    $total=$pTrunk->getNumTrunks($domain,$technology,$status);

    if($total===false){
        $error=$pTrunk->errMsg;
        $total=0;
    }

    $limit=20;

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $oGrid->setTitle(_tr('Trunks List'));
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    $oGrid->setURL($url);

    $arrColum[]=_tr("Name");
    $arrColum[]=_tr("Technology");
    $arrColum[]=_tr("Channel / Peer|[User]");
    $arrColum[]=_tr("Max. Channels");
    $arrColum[]=_tr("Current # of calls by period");
    $arrColum[]=_tr("Status");
    $oGrid->setColumns($arrColum);

    $edit=in_array('edit',$arrPermission);
    $arrData = array();    
    if($total!=0){
        $arrTrunks=$pTrunk->getTrunks($domain,$technology,$status,$limit,$offset);
        if($arrTrunks===false){
            $error=_tr("Error to obtain trunks").$pTrunk->errMsg;
            $arrTrunks=array();
        }
        foreach($arrTrunks as $trunk){
            $arrTmp[0] = "&nbsp;<a href='?menu=trunks&action=view&id_trunk=".$trunk['trunkid']."&tech_trunk=".$trunk["tech"]."'>".$trunk['name']."</a>";
            $arrTmp[1] = strtoupper($trunk['tech']);
            $arrTmp[2] = $trunk['channelid'];
            $arrTmp[3] = $trunk['maxchans'];
            $state="";
            if($trunk['sec_call_time']=="yes"){
                $arrTmp[4] = createDivToolTip($trunk['trunkid'],$pTrunk,$state);
            }else
                $arrTmp[4] = _tr("Feature don't Set");     
                
            if($trunk['disabled']=="on" || $state=="YES")
                $disabled = "on";
            else
                $disabled = "off";
            if($edit)
                $arrTmp[5]=createSelect($trunk['trunkid'],$disabled);
            else{
                $arrTmp[5]=($disabled=='on')?_tr('Disabled'):_tr('Enabled');
            }
            $arrData[] = $arrTmp;
        }
    }

    if(in_array('create',$arrPermission)){
        $arrTech = array("sip"=>_tr("SIP"),"dahdi"=>_tr("DAHDI"), "iax2"=>_tr("IAX2"), "custom"=>_tr("CUSTOM"));
        $oGrid->addComboAction($name_select="tech_trunk",_tr("Create New Trunk"), $arrTech, $selected=null, $task="create_trunk", $onchange_select=null);
    }
    $arrOrgz=array(""=>_tr("all"));
    foreach(($pORGZ->getOrganization(array())) as $value){
        $arrOrgz[$value["domain"]]=$value["name"];
    }
    $_POST["organization"]=$domain;
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization Allow")." = ".$arrOrgz[$domain], $_POST, array("organization" => ""),true); //organization allow
    
    $techFilter=array(''=>_tr('All'),"sip"=>_tr("SIP"),"dahdi"=>_tr("DAHDI"), "iax2"=>_tr("IAX2"), "custom"=>_tr("CUSTOM"));
    $_POST["technology"]=$technology;
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Type")." = ".$techFilter[$technology], $_POST, array("technology" => ""),true); //technology
    
    $arrStatus=array(''=>'','off'=>_tr('Enabled'),'on'=>_tr('Disabled'));
    $_POST["status"]=$status;
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Status")." = ".$arrStatus[$status], $_POST, array("status" => ""),true); //status
    
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='search'>");
    
    $arrFormElements = createFieldFilter($arrOrgz,$techFilter,$arrStatus);
    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
    $oGrid->showFilter(trim($htmlFilter));

    if($error!=""){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",$error);
    }

    $contenidoModulo = $oGrid->fetchGrid(array(), $arrData);
    $contenidoModulo .="<input type='hidden' name='grid_limit' id='grid_limit' value='$limit'>";
    $contenidoModulo .="<input type='hidden' name='grid_offset' id='grid_offset' value='$offset'>";
    return $contenidoModulo;
}

function createDivToolTip($trunkid,$pTrunk,&$block){
    $arrSec=array();
    $res=$pTrunk->getSecTimeASTDB($trunkid);
    
    $block="NO";
    $fail=0;
    $style=$tmp_block="";
    if($res['BLOCK']=="true"){
        $block="YES";
        $tmp_block=" / BLOCKED";
        $fail=(int)$res['NUM_FAIL'];
        $style = "style='color: red; font-weight:bold'";
    }
    $count=$res["COUNT"];
    
    $start="--";
    if(isset($res["START_TIME"])){
        $start=strftime("%D - %T",(int)$res["START_TIME"]);
    }
    
    //recibimos el periodo en minutos y lo llevamos asegundos
    $perid_sec=(int)$res["period_time"]*60;  
    if((int)$res["period_time"]<59){
        $period=$res["period_time"]." min.";
    }else{
        $period=($res["period_time"] / 60)." h.";
    }
    
    $elapsed_sec=fmod((time()-(int)$res["START_TIME"]),$perid_sec);
    $elap_h=floor($elapsed_sec / 3600);
    $elap_m=floor(fmod($elapsed_sec,3600) / 60);
    $elap=$elap_h.":".$elap_m;
    
    $max=$res["maxcalls_time"];
    $count=$res["COUNT"];
    
    $div ="<div class='trunk_tooltip'>
        <p class='start_point'><label>"._tr("Applied Since").": </label><span>$start</span></p>
        <p class='time_period'><label>"._tr("Period Duration").": </label><span>$period</span></p>
        <p class='elapsed_time'><label>"._tr("Elapsed Time Since Last Period").": </label><span>$elap</span></p>
        <p class='max_calls'><label>"._tr("Max Number of Calls").": </label><span>$max</span></p>
        <p class='count_calls'><label>"._tr("Current Number of Calls").": </label><span>$count</span></p>
        <p class='state'><label>"._tr("Blocked").": </label><span>$block</span></p>
        <p class='fail_calls'><label>"._tr("Number of Fail Calls").": </label><span>$fail</span></p>
     </div>";
     
     return "<div class='sec_trunk'><p class='num_calls' id='".$trunkid."' $style>".$count.$tmp_block."</p>".$div."</div>";
}

function createSelect($id,$disabled){
    $arr=array("on"=>_tr('Disabled'),"off"=>_tr('Enabled')); //la logica es invertida
    $field="<select id='sel_$id' name='state_trunk' class='state_trunk' >";
    foreach($arr as $key => $value){
        $select="";
        if($disabled==$key)
            $select="selected";
        $field .="<option value='$key' $select>$value</option>";
    }
    $field .="</select>";
    return $field;
}

function get_num_calls($smarty,&$pDB,$credentials){
    $pTrunk = new paloSantoTrunk($pDB);
    $error=$pagging="";
    $arrParam=$arrTrunk=array();
    $jsonObject=new PaloSantoJSON();
    $limit=getParameter("limit");
    $offset=getParameter("offset");
    
    if(preg_match("/^[0-9]+$/",$limit) && preg_match("/^[0-9]+$/",$offset)){
        $pagging=" limit ? offset ?";
        $arrParam[]=(int)$limit;
        $arrParam[]=(int)$offset;
    }
    
    $query="SELECT trunkid,sec_call_time from trunk $pagging";
    $result=$pDB->fetchTable($query,true,$arrParam);
    if($result==false){
        if($result===false)
            $jsonObject->set_error($pDB->errMsg);
        else
            $jsonObject->set_error("There aren't trunks");
    }else{
        foreach($result as $value){
            if($value["sec_call_time"]=="yes"){
                $block=$style="";
                $res=$pTrunk->getSecTimeASTDB($value["trunkid"]);
                $fail=0;
                if($res['BLOCK']=="true"){
                    $block = " / BLOCKED";
                    $style = "style='color: red; font-weight:bold'";
                    $fail=(int)$res['NUM_FAIL'];
                }
                $arrTrunk[$value["trunkid"]]["p"]="<p class='num_calls' id='".$value['trunkid']."' $style>".$res['COUNT'].$block."</p>";
                
                //tiempo transcurrido desde el ultimo periodo
                $elapsed_sec=fmod((time()-(int)$res["START_TIME"]),(int)$res["period_time"]*60);
                $elap_h=floor($elapsed_sec / 3600);
                $elap_m=floor(fmod($elapsed_sec,3600) / 60);
                $elap=$elap_h.":".$elap_m;
                
                $arrTrunk[$value["trunkid"]]["elapsed_time"]=$elap;
                $arrTrunk[$value["trunkid"]]["count_calls"]=$res['COUNT'];
                $arrTrunk[$value["trunkid"]]["state"]=($block=="")?"NO":"YES";
                $arrTrunk[$value["trunkid"]]["fail_calls"]=$fail;
            }else
                $arrTrunk[$value["trunkid"]]["p"]="";
        }
        $jsonObject->set_message($arrTrunk);
    }
    
    sleep(2);
    return $jsonObject->createJSON();
}

function actDesactTrunk($smarty,&$pDB){
    $pTrunk=new paloSantoTrunk($pDB);
    $error="";
    $idTrunk=getParameter("id_trunk");
    $action=getParameter("trunk_action");
    $jsonObject=new PaloSantoJSON();
    
    if(!preg_match('/^[[:digit:]]+$/', "$idTrunk")){
        $error=_tr("Invalid Trunk Id");
    }else{
        $pDB->beginTransaction();
        $result=$pTrunk->actDesacTrunk($idTrunk,$action);
        if($result==true){
            $pDB->commit();
        }else{
            $error=$pTrunk->errMsg();
            $pDB->rollBack();
        }
    }
    
    if($error!=""){
        $jsonObject->set_error($error);
    }else{
        $state=($action=="on")?"desactivated":"activated";    
        $jsonObject->set_message(_tr("Trunk have been $state successfully"));
    }
    return $jsonObject->createJSON();
}

function viewFormTrunk($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error  = "";
    $pTrunk = new paloSantoTrunk($pDB);
    $pORGZ  = new paloSantoOrganization($pDB);

    $arrTrunks=array();
    $action = getParameter("action");    
    $idTrunk=getParameter("id_trunk");
    
    if($action=="view" || $action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        if(!isset($idTrunk)){
            $error=_tr("Invalid Trunk");
        }
        else{
            $arrTrunks = $pTrunk->getTrunkById($idTrunk);
            if($arrTrunks===false)
                $error=_tr($pTrunk->errMsg);
            else if(count($arrTrunks)==0)
                $error=_tr("Trunk doesn't exist");
            else{
                if($error!=""){
                    $smarty->assign("mb_title", _tr("ERROR"));
                    $smarty->assign("mb_message",$error);
                    return reportTrunks($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$credentials);
                }
                $tech=$arrTrunks["general_tech"];
                                
                $smarty->assign('j',0);
                if($action=="view"|| getParameter("edit") ){
                    $arrDialPattern = $pTrunk->getArrDestine($idTrunk);
                    $smarty->assign('items',$arrDialPattern);
                }
                //$smarty->assign('items',$arrDialPattern);
                
                if(getParameter("save_edit")){
                    if(isset($_POST["general_select_orgs"]))
                        $smarty->assign("ORGS",$_POST["general_select_orgs"]);
                    $arrTrunks=$_POST;
                }else{
                    $select_orgs=implode(",",$arrTrunks["general_select_orgs"]);
                    if(isset($arrTrunks["general_select_orgs"]))
                        $smarty->assign("ORGS",$select_orgs.",");
                }
                
                if($arrTrunks["general_sec_call_time"]=="yes")
                    $smarty->assign("SEC_TIME","yes");
                    
                if($action=="view"){
                    $smarty->assign("ORGS",$select_orgs);
                }
            }
	}
    }
    else{//save_new
        $tech = getParameter("tech_trunk");
        
        $arrDialPattern = getParameter("arrDestine");
        $tmpstatus      = explode(",",$arrDialPattern);
        $arrDialPattern = array_values(array_diff($tmpstatus, array('')));
        $tmp_dial       = array();
        foreach($arrDialPattern as $pattern){
            $prepend = getParameter("general_prepend_digit".$pattern);
            $prefix  = getParameter("general_pattern_prefix".$pattern);
            $pattern = getParameter("general_pattern_pass".$pattern);
            $tmp_dial[] = array(0,$prefix,$pattern,$prepend);
        }
    
        $smarty->assign('j',0);
        $smarty->assign('items',$tmp_dial);
        
        if(getParameter("create_trunk")){
            $arrTrunks1  = $pTrunk->getDefaultConfig($tech,"peer");
            $arrTrunks2 = $pTrunk->getDefaultConfig($tech,"user");
            $arrTrunks  = array_merge($arrTrunks1,$arrTrunks2);
        }else{
            if(isset($_POST["general_select_orgs"]))
                $smarty->assign("ORGS",$_POST["general_select_orgs"]);
            $arrTrunks=$_POST;
        }
    }
    
    $smarty->assign("EDIT",in_array('edit',$arrPermission));
    $smarty->assign("DELETE",in_array('delete',$arrPermission));

    if($error!=""){
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",$error." ".$pTrunk->errMsg);
        return reportTrunks($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $arrTmp=$pORGZ->getOrganization(array());
    $arrOrgz=array(0=>_tr("--pickup organizations--"));
    foreach($arrTmp as $value){
        $arrOrgz[$value["domain"]]=$value["domain"];
    }
    $arrForm = createFieldForm($tech,$arrOrgz);
    $oForm = new paloForm($smarty,$arrForm);
    $smarty->assign("arrAttributes",$arrForm);

    if($action=="view"){
        $oForm->setViewMode();
    }else if($action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
        $mostra_adv["peer"] = getParameter("mostra_adv_peer");
        $mostra_adv["user"] = getParameter("mostra_adv_peer");
        
        foreach($mostra_adv as $km => $vm){
            if(isset($vm)){
                if($vm=="yes"){
                    $smarty->assign("SHOW_MORE_".strtoupper($km),"style='visibility: visible;'");
                    $smarty->assign("mostra_adv_{$km}","yes");
                }else{
                    $smarty->assign("SHOW_MORE_".strtoupper($km),"style='display: none;'");
                    $smarty->assign("mostra_adv_{$km}","no");
                }
            }else{
                $smarty->assign("SHOW_MORE_".strtoupper($km),"style='display: none;'");
                $smarty->assign("mostra_adv_{$km}","yes");
            }
        }
    }
    
    if($tech=="dahdi")
        $smarty->assign("NAME_CHANNEL",_tr("DAHDI Channel"));
    else
        $smarty->assign("NAME_CHANNEL",_tr("CUSTOM Channel"));
        
    $smarty->assign("TECH",strtoupper($tech));
    $smarty->assign("PEER_Details",_tr("Peer Details"));
    $smarty->assign("ADV_OPTIONS",_tr("Advanced Settings"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("id_trunk", $idTrunk);
    $smarty->assign("tech_trunk", $tech);
    $smarty->assign("PREPEND", _tr("Prepend"));
    $smarty->assign("PREFIX", _tr("Prefix"));
    $smarty->assign("MATCH_PATTERN", _tr("Match Pattern"));
    $smarty->assign("RULES", _tr("Dialed Number Manipulation Rules"));
    $smarty->assign("GENERAL", _tr("General"));
    $smarty->assign("SETTINGS", _tr("Peer Settings"));
    $smarty->assign("REGISTRATION", _tr("Registration"));
    $smarty->assign("SEC_SETTINGS", _tr("Security Settings"));
    $smarty->assign("ORGANIZATION_PERM",_tr("Organizations Allowed"));
    
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("Trunk")." ".strtoupper($tech), $arrTrunks);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveTrunk($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    $pTrunk = new paloSantoTrunk($pDB);
    $error  = "";
    $continue = true;
    $successTrunk = false;
    
    $tech = getParameter("tech_trunk");
    if(!preg_match("/^(sip|iax2|dahdi|custom){1}$/",$tech)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Trunk Technology"));
        return viewFormTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $arrForm = createFieldForm($tech,array());
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
        return viewFormTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $arrProp = getAttibutesValue($arrForm);
    $arrProp["general"]["tech"] = $tech;
    
    if(getParameter("save_edit")){   
        $idTrunk = getParameter("id_trunk");
        //obtenemos la informacion del usuario por el id dado, sino existe el trunk mostramos un mensaje de error
        if(!isset($idTrunk)){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Invalid Trunk"));
            return viewFormTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
        else{
            $arrProp["general"]["id_trunk"] = $idTrunk;
            $smarty->assign("id_trunk", $idTrunk);
        }
    }
    
    if($arrProp["general"]["sec_call_time"]=="yes"){
        if(!preg_match("/^[0-9]+$/",$arrProp["general"]["maxcalls_time"])){
            $error=_tr("Field 'Max Num Calls' can't be empty");
            $continue=false;
        }
        if(!preg_match("/^[0-9]+$/",$arrProp["general"]["period_time"])){
            $error=_tr("Field 'Period of Time' can't be empty");
            $continue=false;
        }
    }

    if($tech=="dahdi" || $tech=="custom"){
        $tlabel=($tech=="dahdi")?_tr("DAHDI Identifier"):_tr("Dial String");
        if(empty($arrProp["general"]["channelid"])){
            $error=_tr("Field $tlabel can't be empty");
            $continue=false;
        }
        if($tech=="dahdi"){
            if(!preg_match("/^(g|r){0,1}[0-9]+$/",$arrProp["general"]["channelid"])){
                $error=_tr("Field DAHDI Identifier can't be empty and must be a dahdi number or channel number")._tr(" Ex: g0");
                $continue=false;
            }
        }
    }
    
    if($continue){
        $pDB->beginTransaction();
        
        if(getParameter("save_edit"))
            $successTrunk = $pTrunk->updateTrunkPBX($arrProp);
        else // save_new 
            $successTrunk = $pTrunk->createNewTrunk($arrProp);

        if($successTrunk)
            $pDB->commit();
        else{
            $pDB->rollBack();
            $error .= $pTrunk->errMsg;
        }
    }

    if($successTrunk){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        if(writeAsteriskFile($error,$tech)==true)
            $smarty->assign("mb_message",_tr("Trunk has been created successfully"));
        else
            $smarty->assign("mb_message",_tr("Error: Trunk has been created. ").$error);
        
        $content = reportTrunks($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function getAttibutesValue($arrAttibutes)
{
    $arrExclude = array(
        "general_prepend_digit__",
        "general_pattern_prefix__",
        "general_pattern_pass__",
        "general_org");
    
    $arrProp = array();
    foreach($arrAttibutes as $idattr => $attr){
        if(!in_array($idattr,$arrExclude)){
            list($prefix,$param) = explode("_",$idattr,2);
            $arrProp[$prefix][$param] = getParameter($idattr);
        }
    }
    
    $arrDialPattern = getParameter("arrDestine");
    $tmpstatus      = explode(",",$arrDialPattern);
    $arrDialPattern = array_values(array_diff($tmpstatus, array('')));
    $tmp_dial       = array();
    foreach($arrDialPattern as $pattern){
        $prepend = getParameter("general_prepend_digit".$pattern);
        $prefix  = getParameter("general_pattern_prefix".$pattern);
        $pattern = getParameter("general_pattern_pass".$pattern);
        $tmp_dial[] = array(0,$prefix,$pattern,$prepend);
    }
    $arrProp["general"]["dial_rules"]  = $tmp_dial;
    $arrProp["general"]["select_orgs"] = getParameter("select_orgs");
    
    return $arrProp;
}

function getSIP_IAX_Attributes($tech, $prefix_attribute)
{
    $arrAttributes = array();
    $arrTypes      = array("friend"=>_tr("friend"), "peer"=>_tr("peer"), "user"=>_tr("user"));
    $arrYesNo1     = array("noset"=>"", "yes"=>_tr("Yes"), "no"=>_tr("No"));
    $arrYesNo2     = array("never"=>_tr("Never"), "yes"=>_tr("Yes"), "no"=>_tr("No"));
    $arrYesNo3     = array("yes"=>_tr("Yes"), "no"=>_tr("No"));
    $arrYesNo4     = array("yes"=>_tr("Yes"), "no"=>_tr("No"), "auto"=>_tr("auto"));
    $arrDtmfs      = array('rfc2833'=>'rfc2833', 'info'=>"info", 'shortinfo'=>'shortinfo', 'inband'=>'inband', 'auto'=>'auto');
    $arrMedia      = array(""=>"", 'yes'=>'yes', 'no'=>'no', 'nonat'=>'nonat', 'update'=>'update', "update,nonat"=>"update,nonat", "outgoing"=>"outgoing");
    $arrAmaflag    = array("noset"=>"", "default"=>"default", "omit"=>"omit", "billing"=>"billing", "documentation"=>"documentation");
    $arrAuth       = array("md5"=>"md5", "plaintext"=>"plaintext", "rsa"=>"rsa");
    $arrCodecPrio  = array("noset"=>"", "host"=>"host", "caller"=>"caller", "disabled"=>"disabled", "reqonly"=>"reqonly");
    $arrEncryption = array("noset"=>"", "aes128"=>"aes128", "yes"=>"yes", "no"=>"no");
    $arrTransfer   = array("yes"=>"Yes", "no"=>"No", "mediaonly"=>"mediaonly");
    
    // Commun attributes between sip and iax2 tech.
    $required_field = (substr($prefix_attribute,0,4)=="peer")?"yes":"no";
    addArray_Attribute($arrAttributes,$prefix_attribute,"name",$required_field,null,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"type",$required_field,$arrTypes,"ereg","^(friend|peer|user){1}$","no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"secret","no",null,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"username",$required_field,null,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"host","no",null,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"context",$required_field,null,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"disallow","no",null,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"allow","no",null,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"deny","no",null,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"permit","no",null,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"qualify","no",null,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"acl","no",null,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"amaflags","no",$arrAmaflag,"text",null,"no");
    addArray_Attribute($arrAttributes,$prefix_attribute,"defaultip","no",null,"text",null,"yes");

    addArray_Attribute($arrAttributes,"registration","register","no",null,"text",null,"no");
    $arrAttributes["registration_register"]['INPUT_EXTRA_PARAM'] = array("style" => "width:600px");
    
    if($tech == "sip"){
        addArray_Attribute($arrAttributes,$prefix_attribute,"transport","no",null,"text",null,"no");    
        addArray_Attribute($arrAttributes,$prefix_attribute,"dtmfmode",$required_field,$arrDtmfs,"text",null,"no");
        addArray_Attribute($arrAttributes,$prefix_attribute,"directmedia","no",$arrMedia,"text",null,"no");   
        addArray_Attribute($arrAttributes,$prefix_attribute,"nat","no",null,"text",null,"no");
        addArray_Attribute($arrAttributes,$prefix_attribute,"insecure",$required_field,null,"text",null,"no");
        addArray_Attribute($arrAttributes,$prefix_attribute,"qualifyfreq","no",null,"numeric",null,"no");
        addArray_Attribute($arrAttributes,$prefix_attribute,"callcounter","no",$arrYesNo1,"ereg","^(yes|no|noset){1}$","no");   
        addArray_Attribute($arrAttributes,$prefix_attribute,"busylevel","no",null,"text",null,"no");           
        addArray_Attribute($arrAttributes,$prefix_attribute,"allowoverlap","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"allowsubscribe","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"allowtransfer","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"fromuser","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"fromdomain","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"defaultuser","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"keepalive","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"rtptimeout","no",null,"numeric",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"rtpholdtimeout","no",null,"numeric",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"sendrpid","no",$arrYesNo1,"ereg","^(yes|no|noset){1}$","yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"trustrpid","no",$arrYesNo1,"ereg","^(yes|no|noset){1}$","yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"outboundproxy","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"contactdeny","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"contactpermit","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"contactacl","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"videosupport","no",$arrYesNo1,"ereg","^(yes|no|noset){1}$","yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"maxcallbitrate","no",null,"numeric",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"progressinband","no",$arrYesNo2,"ereg","^(yes|no|never){1}$","yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"g726nonstandard","no",$arrYesNo1,"ereg","^(yes|no|noset){1}$","yes");

    }
    else if($tech == "iax2"){
        addArray_Attribute($arrAttributes,$prefix_attribute,"trunk",$required_field,$arrYesNo3,"ereg","^(yes|no){1}$","no");
        addArray_Attribute($arrAttributes,$prefix_attribute,"auth",$required_field,$arrAuth,"ereg","^(md5|plaintext|rsa){1}$","no");
        addArray_Attribute($arrAttributes,$prefix_attribute,"inkeys","no",null,"text",null,"no");
        addArray_Attribute($arrAttributes,$prefix_attribute,"transfer","no",$arrTransfer,"text",null,"no");
        addArray_Attribute($arrAttributes,$prefix_attribute,"trunkfreq","no",null,"numeric",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"trunktimestamps","no",$arrYesNo3,"ereg","^(yes|no){1}$","yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"sendani","no",$arrYesNo3,"ereg","^(yes|no){1}$","yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"adsi","no",$arrYesNo3,"ereg","^(yes|no){1}$","yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"requirecalltoken","no",$arrYesNo4,"ereg","^(yes|no|auto){1}$","yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"maxcallnumbers","no",null,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"jitterbuffer","no",$arrYesNo1,"ereg","^(yes|no|noset){1}$","yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"forcejitterbuffer","no",$arrYesNo1,"ereg","^(yes|no|noset){1}$","yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"codecpriority","no",$arrCodecPrio,"text",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"qualifysmoothing","no",$arrYesNo1,"ereg","^(yes|no|noset){1}$","yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"qualifyfreqok","no",null,"numeric",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"qualifyfreqnotok","no",null,"numeric",null,"yes");
        addArray_Attribute($arrAttributes,$prefix_attribute,"encryption","no",$arrEncryption,"text",null,"yes");
    }
    
    return $arrAttributes;
}

function addArray_Attribute(&$arrAttributes, $prefix_attribute, $name_attribute, $required,
        $arrParams, $validation_type, $validation_extra=null, $is_advanced_settings=null)
{
    // SHOW_IN_TECH and IS_ADVANCED_SETTING are only use in tpl
    // by smarty foreach. Framework -> paloSantoForm don't known
    // about key or parameter.
    
    $key_array = isset($prefix_attribute)?"{$prefix_attribute}_{$name_attribute}":$name_attribute;
    
    $arrAttributes[$key_array] = array(
        "LABEL"                  => _tr($name_attribute),
        "DESCRIPTION"            => _tr("PBXT_{$name_attribute}"),  
        "REQUIRED"               => $required,
        "INPUT_TYPE"             => isset($arrParams)?"SELECT":"TEXT",
        "INPUT_EXTRA_PARAM"      => isset($arrParams)?$arrParams:array("style" => "width:200px"),
        "VALIDATION_TYPE"        => $validation_type,
        "VALIDATION_EXTRA_PARAM" => $validation_extra,
        "IS_ADVANCED_SETTING"    => $is_advanced_settings);
        
   
}

function deleteTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){

    $pTrunk = new paloSantoTrunk($pDB);
    $error = "";
    //conexion elastix.db
    $continue=true;
    $successTrunk=false;
    
    $idTrunk=getParameter("id_trunk");

	if(!isset($idTrunk)){
        $error=_tr("Invalid Trunk");
    }else{
        $arrTrunks = $pTrunk->getTrunkById($idTrunk);
        if($arrTrunks===false){
            $error=_tr($pTrunk->errMsg);
        }else if(count($arrTrunks)==0){
            $error=_tr("Trunk doesn't exist");
        }else{
            if($error!=""){
                $smarty->assign("mb_title", _tr("ERROR"));
                $smarty->assign("mb_message",$error);
                return reportTrunks($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
            }
            $pDB->beginTransaction();
            $successTrunk = $pTrunk->deleteTrunk($idTrunk);
            if($successTrunk)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pTrunk->errMsg;
        }
    }

    if($successTrunk){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        //quitamos al peer de cache en caso que la truncal haya sido iax o sip
        if($arrTrunks["general_tech"]=="sip" || $arrTrunks["general_tech"]=="iax2"){
            $pTrunk->prunePeer($arrTrunks["peer_name"],$arrTrunks["general_tech"]);
            
            if(!empty($arrTrunks["user_name"]))
                $pTrunk->prunePeer($arrTrunks["user_name"],$arrTrunks["general_tech"]);
        }
        if(writeAsteriskFile($error,$arrTrunks["general_tech"])==true)
            $smarty->assign("mb_message",_tr("Trunk was deleted successfully"));
        else
            $smarty->assign("mb_message",_tr("Error: Trunk was deleted. ").$error);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($error));
    }

    return reportTrunks($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);;
}

function writeAsteriskFile(&$error,$tech){
    if($tech=="sip" || $tech=="iax2"){
        $sComando = "/usr/bin/elastix-helper asteriskconfig writeTechRegister $tech 2>&1";
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $error = _tr("Error writing $tech.conf file").implode('', $output);
            return FALSE;
        }
    }
    
    $sComando = '/usr/bin/elastix-helper asteriskconfig createExtGeneral 2>&1';
    $output = $ret = NULL;
    exec($sComando, $output, $ret);
    if ($ret != 0) {
        $error = _tr("Error writing extensions_additionals file").implode('', $output);
        return FALSE;
    }
    
    $sComando = '/usr/bin/elastix-helper asteriskconfig dialplan-reload 2>&1';
    $output = $ret = NULL;
    exec($sComando, $output, $ret);
    if ($ret != 0){
        $error = implode('', $output);
        return FALSE;
    }
    
    return true;
}

function createFieldForm($tech,$arrOrgz)
{
    $arrFields = array();
    $arrCid    = array("off"=>_tr("Allow Any CID"), "on"=>_tr("Block Foreign CIDs"), "cnum"=>_tr("Remove CNAM"), "all"=>_tr("Force Trunk CID"));
    $arrYesNo  = array("yes"=>_tr("Yes"),"no"=>_tr("No"));
    $arrPeriod = array(5=>"5 min",10=>"10 min",15=>"15 min",30=>"30 min",45=>"45",60=>"1 hora",120=>"2 horas",180=>"3 horas",240=>"4 horas",300=>"5 horas",360=>"6 horas",600=>"10 horas",720=>"12 horas",900=>"15 horas",1200=>"20 horas",1440=>"1 dia");
    $arrStatus = array('off'=>_tr('Enabled'),'on'=>_tr('Disabled'));
    
    $prefix_attribute = "general";
    addArray_Attribute($arrFields,$prefix_attribute,"trunk_name","yes",null,"text");
    addArray_Attribute($arrFields,$prefix_attribute,"keepcid","no",$arrCid,"text"); //accion en javascript
    addArray_Attribute($arrFields,$prefix_attribute,"outcid","no",null,"text");
    addArray_Attribute($arrFields,$prefix_attribute,"max_chans","no",null,"numeric");
    addArray_Attribute($arrFields,$prefix_attribute,"dialout_prefix","no",null,"text");
    addArray_Attribute($arrFields,$prefix_attribute,"disabled","yes",$arrStatus,"text");
    addArray_Attribute($arrFields,$prefix_attribute,"prepend_digit__","no",null,"text");    
    addArray_Attribute($arrFields,$prefix_attribute,"pattern_prefix__","no",null,"text");    
    addArray_Attribute($arrFields,$prefix_attribute,"pattern_pass__","no",null,"text");
    addArray_Attribute($arrFields,$prefix_attribute,"org","yes",$arrOrgz,"text");
    addArray_Attribute($arrFields,$prefix_attribute,"period_time","no",$arrPeriod,"text");
    addArray_Attribute($arrFields,$prefix_attribute,"maxcalls_time","no",null,"text");
    addArray_Attribute($arrFields,$prefix_attribute,"sec_call_time","yes",$arrYesNo,"text");
    
    $arrFields["{$prefix_attribute}_prepend_digit__"]['INPUT_EXTRA_PARAM']  = array("style" => "width:60px;text-align:center;");
    $arrFields["{$prefix_attribute}_pattern_prefix__"]['INPUT_EXTRA_PARAM'] = array("style" => "width:40px;text-align:center;");
    $arrFields["{$prefix_attribute}_pattern_pass__"]['INPUT_EXTRA_PARAM']   = array("style" => "width:150px;text-align:center;");
    
    if($tech=="dahdi" || $tech=="custom"){
        addArray_Attribute($arrFields,$prefix_attribute,"channelid","yes",null,"text");
        $arrFields["{$prefix_attribute}_channelid"]['LABEL'] = ($tech=="dahdi")?_tr("DAHDI Identifier"):_tr("Dial String");
    }
    else{
        $arrFields = array_merge($arrFields,getSIP_IAX_Attributes($tech,"peer"));
        $arrFields = array_merge($arrFields,getSIP_IAX_Attributes($tech,"user"));
    }
    
    return $arrFields;
}

function createFieldFilter($arrOrgz,$arrTech,$arrStatus)
{
    $arrFields = array(
        "organization"  => array("LABEL"         => _tr("Organization Allow"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "SELECT",
                        "INPUT_EXTRA_PARAM"      => $arrOrgz,
                        "VALIDATION_TYPE"        => "domain",
                        "VALIDATION_EXTRA_PARAM" => ""),
        "technology"  => array("LABEL"         => _tr("Type"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "SELECT",
                        "INPUT_EXTRA_PARAM"      => $arrTech,
                        "VALIDATION_TYPE"        => "text",
                        "VALIDATION_EXTRA_PARAM" => ""),
        "status"  => array("LABEL"         => _tr("Status"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "SELECT",
                        "INPUT_EXTRA_PARAM"      => $arrStatus,
                        "VALIDATION_TYPE"        => "text",
                        "VALIDATION_EXTRA_PARAM" => ""),
        );
    return $arrFields;
}

function getAction(){
    global $arrPermission;
    if(getParameter("create_trunk"))
        return (in_array('create',$arrPermission))?'new_trunk':'report';
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
    else if(getParameter("action")=="get_num_calls")
        return "get_num_calls";
    else if(getParameter("action")=="actDesactTrunk")
        return (in_array('edit',$arrPermission))?'actDesactTrunk':'report';
    else
        return "report"; //cancel
}
?>
