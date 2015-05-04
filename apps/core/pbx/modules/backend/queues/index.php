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
        case "new_queue":
            $content = viewQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_edit":
            $content = viewQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveNewQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
		case "reloadAasterisk":
			$content = reloadAasterisk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "get_destination_category":
            $content = get_destination_category($smarty, $module_name, $pDB, $arrConf, $arrCredentials);
            break;
        default: // report
            $content = reportQueue($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function reportQueue($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error = "";
    $pORGZ = new paloSantoOrganization($pDB);

    $domain=getParameter("organization");
    $domain=empty($domain)?'all':$domain;
    if($credentials['userlevel']!="superadmin"){
        $domain=$credentials['domain'];
    }
    $queue_name=getParameter("queue_name");
    
    $pQueue = new paloQueuePBX($pDB,$domain);
    
    $queue_number=getParameter("queue_number");
    if(isset($queue_number) && $queue_number!=''){
        $expression=$pQueue->getRegexPatternFromAsteriskPattern($queue_number);
        if($expression===false)
            $queue_number='';
    }
    
    $url['menu']=$module_name;
    $url['organization']=$domain;
    $url['queue_number']=$queue_number; //queue_number
    $url['queue_name']=$queue_name; //queue_name

    $total=$pQueue->getTotalQueues($domain,$queue_number,$queue_name);
    $arrOrgz=array();
    if($credentials['userlevel']=="superadmin"){
        $arrOrgz=array("all"=>_tr("all"));
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }
    
    if($total===false){
        $error=$pQueue->errMsg;
        $total=0;
    }

    $limit=20;

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $oGrid->setTitle(_tr('Queues List'));
    //$oGrid->setIcon('url de la imagen');
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    $oGrid->setURL($url);
    
    if($credentials['userlevel']=='superadmin')
        $arrColumns[]=_tr("Organization");
    $arrColumns[]=_tr("Queue Number");
    $arrColumns[]=_tr("Queue Name");
    $arrColumns[]=_tr("Password");
    $arrColumns[]=_tr("Record Call");
    $arrColumns[]=_tr("Strategy");
    $arrColumns[]=_tr("Timeout Queue");
    $arrColumns[]=_tr("Timeout Agent");
    $oGrid->setColumns($arrColumns);

    $arrData = array();
    $arrQueues = array();
    if($total!=0){
        $arrQueues=$pQueue->getQueues($domain,$queue_number,$queue_name,$limit,$offset);
    }

    if($arrQueues===false){
        $error=_tr("Error getting queue data. ").$pQueue->errMsg;
    }else{
        foreach($arrQueues as $queue){
            $arrTmp=array();
            if($credentials['userlevel']=='superadmin')
                $arrTmp[]=$arrOrgz[$queue['organization_domain']];
            
            $queunumber=$queue["queue_number"];
            $arrTmp[] = "&nbsp;<a href='?menu=queues&action=view&qname=".$queue['name']."&organization={$queue['organization_domain']}'>".$queunumber."</a>";
                            
            $arrTmp[]=htmlentities($queue["description"],ENT_QUOTES,"UTF-8");
            $arrTmp[]=$queue["password_detail"];
            $arrTmp[]=isset($queue["monitor_format"])?"yes":"no";
            $arrTmp[]=$queue["strategy"];
            $arrTmp[]=($queue["timeout_detail"]=="0")?"unlimited":$queue["timeout_detail"];
            $arrTmp[]=$queue["timeout"];
            /*$result=getInfoQueue();
            $arrTmp[6]=$result["logged"];
            $arrTmp[6]=$result["free"];*/
            $arrData[]=$arrTmp;
        }
    }

    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='report'>");
    if($pORGZ->getNumOrganization(array()) >= 1){
        if(in_array('create',$arrPermission)){
            if($credentials['userlevel']=='superadmin'){
                $oGrid->addComboAction("organization_add",_tr("Create New Queue"), array_slice($arrOrgz,1), $selected=null, "create_queue", $onchange_select=null);
            }else{
                $oGrid->addNew("create_queue",_tr("Create New Queue"));
            }   
        }
        if($credentials['userlevel']=='superadmin'){
            $_POST["organization"]=$domain;
            $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true);
        }
        $_POST["queue_number"]=$queue_number; // patter to filter estension number
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Queue Number")." = ".$queue_number, $_POST, array("queue_number" => "")); 
        $_POST["queue_name"]=$queue_name; // patter to filter estension number
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Queue Name")." = ".$queue_name, $_POST, array("queue_name" => "")); 
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

function viewQueue($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    global $arrPermission;
    $error="";

    $arrQueue=array();
    $action = getParameter("action");

    $qname=getParameter("qname");
    if($action=="view" || $action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        if(!isset($qname)){
            $error=_tr("Invalid Queue");
        }else{
            $domain=getParameter('organization');
            if($credentials['userlevel']!='superadmin'){
                $domain=$credentials['domain'];
            }
            
            $pQueue=new paloQueuePBX($pDB,$domain);
            $arrTmp=$pQueue->getQueueByName($qname);
                
            if($arrTmp===false){
                $error=_tr("Error with database connection. ").$pQueue->errMsg;
            }elseif(count($arrTmp)==false){
                $error=_tr("Queue doesn't exist");
            }else{
                $smarty->assign("QUEUE", $arrTmp["queue_number"]);
                if(getParameter("save_edit")){
                    $arrQueue=$_POST;
                }else{
                    $arrMember=$pQueue->getQueueMembers($qname);
                    if($arrMember===false){
                        $error=_tr("Problems getting queue members. ").$pQueue->errMsg;
                        $arrMember=array();
                    }
                    $arrQueue=showQueueSetting($arrTmp,$arrMember);
                }
            }
        }
    }else{
        if($credentials['userlevel']=='superadmin'){
            if(getParameter("create_queue")){
                $domain=getParameter('organization_add'); //este parametro solo es selecionable cuando es el superadmin quien crea la ruta
            }else
                $domain=getParameter('organization');
        }else{
            $domain=$credentials['domain'];
        }
    
        $pQueue=new paloQueuePBX($pDB,$domain);
        if(getParameter("create_queue")){
            $arrQueue=$pQueue->defaultOptions();
        }else{
            $arrQueue=$_POST;
        }
    }

    if($error!=""){
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",$error);
        return reportQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $category=$pQueue->getCategoryDefault($domain);
    if($category===false)
        $category=array();
    $res=$pQueue->getDefaultDestination($domain,$arrQueue["category"]);
    $destiny=($res==false)?array():$res;

    $arrForm = createFieldForm($pQueue->getRecordingsSystem($domain),getArrayExtens($pDB,$domain),$category,$destiny,$pQueue->getMoHClass($domain));
    $oForm = new paloForm($smarty,$arrForm);

    if($action=="view"){
        $oForm->setViewMode();
    }else if($action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
    }

    //permission
    $smarty->assign("EDIT_QUEUE",in_array('edit',$arrPermission));
    $smarty->assign("CREATE_QUEUE",in_array('create',$arrPermission));
    $smarty->assign("DEL_QUEUE",in_array('delete',$arrPermission));
    
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to continue?"));
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("qname", $qname);
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("ORGANIZATION_LABEL",_tr("Organization Domain"));
    $smarty->assign("ORGANIZATION",$domain);
    $smarty->assign("GENERAL",_tr("General"));
    $smarty->assign("MEMBERS",_tr("Queue Members"));
    $smarty->assign("ADVANCED",_tr("Advanced Options"));
    $smarty->assign("TIME_OPTIONS",_tr("Timing Options"));
    $smarty->assign("EMPTY_OPTIONS",_tr("Empty Options"));
    $smarty->assign("RECORDING",_tr("Recording Options"));
    $smarty->assign("ANN_OPTIONS",_tr("Announce Options"));
    $smarty->assign("PER_OPTIONS",_tr("Periodic Announce Options"));
    $smarty->assign("DEFAULT_DEST",_tr("Default Destination"));
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("Queues"),$arrQueue);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    return $content;
}

function showQueueSetting($arrProp,$arrMember){
    if(!isset($arrProp["musicclass"])){
        $arrProp["musicclass"]="inherit";
    }
    if(isset($arrProp["ringing_detail"])){
        if($arrProp["ringing_detail"]=="yes"){
            $arrProp["musicclass"]="ring";
        }
    }
    if(!isset($arrProp["monitor_format"])){
        $arrProp["monitor_format"]="no";
    }
    if(isset($arrProp["retry_detail"])){
        if($arrProp["retry_detail"]=="no"){
            $arrProp["retry"]="no_retry";
        }
    }
    if(!isset($arrProp["min_announce_frequency"])){
        $arrProp["min_announce_frequency"]=0;
    }
    if(!isset($arrProp["announce_detail"])){
        $arrProp["announce_detail"]="none";
    }
    if(isset($arrProp["context"])){
        $arrProp["context"]=substr($arrProp["context"],16);
    }
    if(isset($arrProp["cid_prefix_detail"])){
        $arrProp["cid_prefix"]=$arrProp["cid_prefix_detail"];
    }
    if(isset($arrProp["cid_holdtime_detail"])){
        $arrProp["cid_holdtime"]=$arrProp["cid_holdtime_detail"];
    }
    
    $category="none";
    if(isset($arrProp['destination_detail'])){
        $tmp=explode(",",$arrProp['destination_detail']);
        if(count($tmp)==2){
            $category=$tmp[0];
        }
    }
    $arrProp["category"]=$category;
    $arrProp["destination"]=$arrProp['destination_detail'];
    
    $statics=$dynamics="";
    foreach($arrMember["statics"] as $value){
        $statics .=$value["exten"].",".$value["penalty"]."\n";
    }
    $arrProp["static_members"]=$statics;
    
    foreach($arrMember["dynamics"] as $value){
        $dynamics .=$value["exten"].",".$value["penalty"]."\n";
    }
    $arrProp["dynamic_members"]=$dynamics;
    
    //print_r($arrProp);
    
    return $arrProp;
}

function saveNewQueue($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $error="";
    $exito=false;

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $pQueue=new paloQueuePBX($pDB,$domain);
    
    $arrForm = createFieldForm(array(),array(),array(),array(),array());
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
        return viewQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $qname=getParameter("name");
        $password=getParameter("password_detail");
        $arrMembers=array('dynamic_members'=>getParameter("dynamic_members"),'static_members'=>getParameter("static_members"));
        if(!preg_match("/^[0-9]+$/",$qname)){
            $error .= _tr("Invalid queue number.");
        }elseif(isset($password)){
            if(!preg_match("/^[0-9]*$/",$password)){
                $error .= _tr("Password must only contain digits.");
            }
        }
        
        if($error==""){  
            $pDB->beginTransaction();
            $exito=$pQueue->createQueue(queueParams(),$arrMembers);
            if($exito){
                $pDB->commit();
            }else{
                $pDB->rollBack();
            }
            $error .=$pQueue->errMsg;
        }
    }
    
    if($exito){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Queue has been created successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        return reportQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        return viewQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    } 
}

function saveEditQueue($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $error="";
    $exito=false;
    $qname=getParameter("qname");
    
    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    if(!isset($qname)){
        $error=_tr("Queue doesn't exist");
    }else{
        $pQueue=new paloQueuePBX($pDB,$domain);
        $arrTmp=$pQueue->getQueueByName($qname);
        if($arrTmp===false){
            $error=_tr("Error with database connection. ").$pQueue->errMsg;
        }elseif(count($arrTmp)==false){
            $error=_tr("Queue doesn't exist");
        }else{
            $arrForm = createFieldForm(array(),array(),array(),array(),array());
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
                return viewQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
            }else{
                $password=getParameter("password_detail");
                if(isset($password)){
                    if(!preg_match("/^[0-9]*$/",$password)){
                        $smarty->assign("mb_title", _tr("ERROR"));
                        $smarty->assign("mb_message",_tr("Password must only contain digits."));
                        return viewQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
                    }
                }
                $arrMembers=array('dynamic_members'=>getParameter("dynamic_members"),'static_members'=>getParameter("static_members"));  
                $pDB->beginTransaction();
                $arrProp=queueParams();
                $arrProp["name"]=$qname;
                $exito=$pQueue->updateQueue($arrProp,$arrMembers);
                if($exito){
                    $pDB->commit();
                }else
                    $pDB->rollBack();
                $error .=$pQueue->errMsg;
            }
        }
    }
    
    if($exito){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Queue has been edited successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
    } 
    
    return reportQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function deleteQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error="";
    $exito=false;
    $qname=getParameter("qname");
    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien hace la accion
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    if(!isset($qname)){
        $error=_tr("Queue doesn't exist");
    }else{
        $pQueue=new paloQueuePBX($pDB,$domain);
        $arrTmp=$pQueue->getQueueByName($qname);
        if($arrTmp===false){
            $error=_tr("Error with database connection. ").$pQueue->errMsg;
        }elseif(count($arrTmp)==false){
            $error=_tr("Queue doesn't exist");
        }else{
            $pDB->beginTransaction();
            $exito=$pQueue->deleteQueue($qname);
            if($exito){
                $pDB->commit();
            }else
                $pDB->rollBack();
            $error .=$pQueue->errMsg;
        }
    }
    
    if($exito){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Queue has been deleted successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
    } 
    
    return reportQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function getArrayExtens($pDB,$domain){
    $pQueue=new paloQueuePBX($pDB,$domain);
    $arrExten=$pQueue->getAllDevice($domain);
    $extens=array(_tr("none")=>_tr("select one"));
    if($arrExten!=false){
        foreach($arrExten as $value){
            $extens[$value["exten"]]=$value["exten"]."(".$value["dial"].")";
        }
    }
    return $extens;  
}

function queueParams(){
    $arrProp=array();
    $arrProp["name"]=getParameter("name");
    $arrProp["description"]=getParameter("description");
    $arrProp['cid_prefix_detail']=getParameter("cid_prefix");
    $arrProp['cid_holdtime_detail']=getParameter("cid_holdtime");
    $arrProp['alert_info_detail']=getParameter("alert_info");
    $arrProp['musicclass']=getParameter("musicclass");
    $arrProp['announce_caller_detail']=getParameter("announce_caller_detail");
    $arrProp['announce_detail']=getParameter("announce_detail");
    $arrProp['reportholdtime']=getParameter("reportholdtime");
    $arrProp['strategy']=getParameter("strategy");
    $arrProp['maxlen']=getParameter("maxlen");
    $arrProp['monitor_format']=getParameter("monitor_format");
    $arrProp['timeout_detail']=getParameter("timeout_detail");
    $arrProp['timeout']=getParameter("timeout");
    $arrProp['retry']=getParameter("retry");
    $arrProp['timeoutpriority']=getParameter("timeoutpriority");
    $arrProp['joinempty']=getParameter("joinempty");
    $arrProp['leavewhenempty']=getParameter("leavewhenempty");
    $arrProp["skip_busy_detail"]=getParameter("skip_busy_detail");
    $arrProp['password_detail']=getParameter("password_detail");
    $arrProp['servicelevel']=getParameter("servicelevel");
    $arrProp['context']=getParameter("context");
    $arrProp['weight']=getParameter("weight");
    $arrProp['wrapuptime']=getParameter("wrapuptime");
    $arrProp['autofill']=getParameter("autofill");
    $arrProp['autopausedelay']=getParameter("autopausedelay");
    $arrProp['autopause']=getParameter("autopause");
    $arrProp['announce_frequency']=getParameter("announce_frequency");
    $arrProp['min_announce_frequency']=getParameter("min_announce_frequency");
    $arrProp['announce_holdtime']=getParameter("announce_holdtime");
    $arrProp['announce_position']=getParameter("announce_position");
    $arrProp['announce_position_limit']=getParameter("announce_position_limit");
    $arrProp['periodic_announce']=getParameter("periodic_announce");
    $arrProp['periodic_announce_frequency']=getParameter("periodic_announce_frequency");
    $arrProp['restriction_agent']=getParameter("restriction_agent");
    $arrProp['calling_restriction']=getParameter("calling_restriction");
    $arrProp['destination_detail']=getParameter("destination");
    return $arrProp; 
}

function get_destination_category($smarty, $module_name, $pDB, $arrConf, $credentials){
    $jsonObject = new PaloSantoJSON();
    $categoria=getParameter("category");
    $domain=getParameter("organization");
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    $pQueue=new paloQueuePBX($pDB,$domain);
    $arrDestine=$pQueue->getDefaultDestination($domain,$categoria);
    if($arrDestine==FALSE){
        $jsonObject->set_error(_tr($pQueue->errMsg));
    }else{
        $jsonObject->set_message($arrDestine);
    }
    return $jsonObject->createJSON();
}

function createFieldForm($Recordings,$extens,$category,$destiny,$arrMusic)
{   
    $arrRecordings=array("none"=>_tr("None"));
    if(is_array($Recordings)){
        foreach($Recordings as $key => $value){
            $arrRecordings[$key] = $value;
        }
    }
    
    $music=array("ring"=>"ring");
    foreach($arrMusic as $key => $value){
        $music[$key]=$value;
    }
    
    $arrTime=range(0,120);
    $arrYesNo=array("yes"=>_tr("Yes"),"no"=>_tr("No"));
    $arrMonitor=array("no"=>_tr("No"),"gsm"=>"gsm","wav"=>"wav","wav49"=>"wav49");
    $arrLen=array("0"=>_tr("unlimited"),1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60);
	$arrPosition=array(""=>_tr("none"),"1"=>1,"2"=>2,"3"=>3,"4"=>4,"5"=>5,"6"=>6,"7"=>7,"8"=>8,"9"=>9,"10"=>10,"15"=>15,"20"=>20);	$arrTimeF=array("0"=>_tr("desactivate"),1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120);
    $arrBusy=array("No",_tr("Yes"),_tr("Yes + (ringuse=no)"),_tr("Queues Calls only + (ringuse=no)"));    
    $strategy=array('ringall'=>'ringall','leastrecent'=>'leastrecent','fewestcalls'=>'fewestcalls','random'=>'random','rrmemory'=>'rrmemory','rrordered'=>'rrordered','linear'=>'linear','leastrecent'=>'leastrecent');
	$arrMaxTimeOut=array("0"=>_tr("unlimited"),"10"=>"10 seconds","20"=>"20 seconds","30"=>"30 seconds","40"=>"40 seconds","50"=>"50 seconds","60"=>"1 minute","90"=>"1 min 30\"","120"=>"2 mins","150"=>"2 mins 30\"","180"=>"3 mins","210"=>"3 mins 30\"","240"=>"4 mins","270"=>"4 mins 30\"","300"=>"5 mins","600"=>"10 mins","900"=>"15 mins","1200"=>"20 mins","1500"=>"25 mins","1800"=>"30 mins","2100"=>"35 mins","2400"=>"40 mins","2700"=>"45 mins","3000"=>"50 mins","3300"=>"55 mins","3600"=>"1 hour");
    $retry=array("no_retry"=>"no retry",0=>"0 seconds","1"=>"1 seconds","2"=>"2 seconds","3"=>"3 seconds","4"=>"4 seconds","5"=>"5 seconds","6"=>"6 seconds","7"=>"7 seconds","8"=>"8 seconds","9"=>"9 seconds","10"=>"10 seconds","11"=>"11 seconds","12"=>"12 seconds","13"=>"13 seconds","14"=>"14 seconds","15"=>"15 seconds","16"=>"16 seconds","17"=>"17 seconds","18"=>"18 seconds","19"=>"19 seconds","20"=>"20 seconds");



    $arrFormElements = array("description" => array("LABEL"                  => _tr('Description'),
                                                    "DESCRIPTION"            => _tr("QUO_description"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "name" => array("LABEL"                  => _tr('Queue Number'),
                                                    "DESCRIPTION"            => _tr("Queue Number"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "cid_prefix"  => array("LABEL"                  => _tr("Cid Prefix"),
                                                    "DESCRIPTION"            => _tr("QUO_cidprefix"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "cid_holdtime"   => array("LABEL"                  => _tr("Cid Prefix Holdtime"),
                                                    "DESCRIPTION"            => _tr("QUO_cidprefixholdtime"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => _tr("^(yes|no){1}$")),
                             "alert_info"   => array("LABEL"                  => _tr("Alert Info"),
                                                    "DESCRIPTION"            => _tr("QUO_alertinfo"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
							 "musicclass"       => array("LABEL"           => _tr("Music On Hold"),
                                                    "DESCRIPTION"            => _tr("QUO_musiconhold"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $music,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "announce_caller_detail"       => array("LABEL"        => _tr("Announce Caller"),
                                                    "DESCRIPTION"            => _tr("QUO_announcecaller"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrRecordings,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "announce_detail"       => array("LABEL"            => _tr("Announce Agent"),
                                                    "DESCRIPTION"            => _tr("QUO_announceagent"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrRecordings,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "reportholdtime"       => array("LABEL"         => _tr("report agent caller's hold time"),
                                                    "DESCRIPTION"            => _tr("QUO_reportagentcaller"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                             "strategy"       => array("LABEL"              => _tr("Strategy"),
                                                    "DESCRIPTION"            => _tr("QUO_strategy"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $strategy,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "maxlen"   => array("LABEL"                  => _tr("Max Number Caller"),
                                                    "DESCRIPTION"            => _tr("QUO_maxnumbercalls"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrLen,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
							 "monitor_format"   => array("LABEL"               => _tr("Record Call"),
                                                    "DESCRIPTION"            => _tr("QUO_recordcalls"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrMonitor,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(wav|no|gsm|wav49){1}$"),
                             "timeout_detail"   => array("LABEL"               => _tr("Max time caller in queue"),
                                                    "DESCRIPTION"            => _tr("QUO_maxtimecallerinqueue"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrMaxTimeOut,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "timeout"   => array("LABEL"            => _tr("Agent Timeout"),
                                                    "DESCRIPTION"            => _tr("QUO_agenttimeout"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrLen,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "retry"   => array("LABEL"               => _tr("Retry"),
                                                    "DESCRIPTION"            => _tr("QUO_retry"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $retry,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "timeoutpriority" => array("LABEL"             => _tr("Timeout Priority"),
                                                    "DESCRIPTION"            => _tr("QUO_timeoutpriority"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array("app"=>"app","conf"=>"conf"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(app|conf){1}$"),
                             "joinempty"   => array("LABEL"               => _tr("Joinempty"),
                                                    "DESCRIPTION"            => _tr("QUO_joinempty"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array("yes"=>_tr("yes"),"no"=>"no","strict"=>_tr("strict"),"loose"=>_tr("loose")),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|strict|loose){1}$"),
                             "leavewhenempty"   => array("LABEL"              => _tr("Leavewhenempty"),
                                                    "DESCRIPTION"            => _tr("QUO_leavewhenempty"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array("yes"=>"yes","no"=>"no","strict"=>"strict","loose"=>"loose"),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|strict|loose){1}$"),
                             "skip_busy_detail" => array("LABEL"             => _tr("Skip Busy Agent"),
                                                    "DESCRIPTION"            => _tr("QUO_skipbusyagent"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrBusy,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
							 "password_detail"       => array("LABEL"               => _tr("Password"),
                                                    "DESCRIPTION"            => _tr("Password"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "servicelevel"       => array("LABEL"              => _tr("Service Level"),
                                                    "DESCRIPTION"            => _tr("QUO_servicelevel"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrTime,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "context"       => array("LABEL"               => _tr("exit context"),
                                                    "DESCRIPTION"            => _tr("QUO_exitcontext"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
							 "weight"       => array("LABEL"               => _tr("Queue Weight"),
                                                    "DESCRIPTION"            => _tr("QUO_queueweight"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrTime,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "wrapuptime"       => array("LABEL"              => _tr("Wrap-up-Time"),
                                                    "DESCRIPTION"            => _tr("QUO_wrapuptime"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrTime,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "autofill"       => array("LABEL"              => _tr("Autofill"),
                                                    "DESCRIPTION"            => _tr("QUO_autofill"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                             "autopausedelay"       => array("LABEL"         => _tr("autopausedelay"),
                                                    "DESCRIPTION"            => _tr("QUO_autopausedelay"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrTime,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "autopause"       => array("LABEL"              => _tr("Autopause"),
                                                    "DESCRIPTION"            => _tr("QUO_autopause"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array("no"=>"No",_tr("yes")=>"Yes",_tr("all")=>_tr("All")),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|all){1}$"),
                             "announce_frequency" => array("LABEL"            => _tr("Announce Frecuency"),
                                                    "DESCRIPTION"            => _tr("QUO_announcefrecuency"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrTimeF,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "min_announce_frequency" => array("LABEL"            => _tr("Min Announce Frecuency"),
                                                    "DESCRIPTION"            => _tr("QUO_minannouncefrecuency"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrTimeF,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "announce_holdtime"       => array("LABEL"       => _tr("Announce Holdtime"),
                                                    "DESCRIPTION"            => _tr("QUO_announceholdtime"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array(_tr("yes")=>"yes","no"=>"no",_tr("once")=>_tr("once")),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|once){1}$"),
                            "announce_position"       => array("LABEL"       => _tr("Announce Position"),
                                                    "DESCRIPTION"            => _tr("QUO_announceposition"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array("yes"=>_tr("yes"),"no"=>"no","more"=>_tr("more"),"limit"=>_tr("limit")),
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no|more|limit){1}$"),
                            "announce_position_limit" => array("LABEL"            => _tr("Announce Position Limit"),
                                                    "DESCRIPTION"            => _tr("QUO_announcepositionlimit"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrPosition,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "periodic_announce" => array("LABEL"            => _tr("Periodic Announce"),
                                                    "DESCRIPTION"            => _tr("QUO_periodicannounce"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "periodic_announce_frequency" => array("LABEL"   => _tr("Periodic Announce Frecuency"),
                                                    "DESCRIPTION"            => _tr("QUO_periodicannouncefrecuency"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrTime,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "dynamic_members" => array("LABEL"               => _tr("Dynamic Members"),
                                                    "DESCRIPTION"            => _tr("QUO_dynamicmembers"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXTAREA",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:400px;resize:none"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => "",
                                                    "ROWS"                   => "5",
                                                    "COLS"                   => "2"),
                            "static_members" => array("LABEL"               => _tr("Static Members"),
                                                    "DESCRIPTION"            => _tr("QUO_staticmembers"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXTAREA",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:400px;resize:none"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => "",
                                                    "ROWS"                   => "5",
                                                    "COLS"                   => "2"),
                            "pickup_dynamic"   => array("LABEL"                => _tr("Estension List"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $extens,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "pickup_static"   => array("LABEL"                => _tr("Estension List"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $extens,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "restriction_agent"   => array("LABEL"          => _tr("Only Dynamic Agents Listed"),
                                                    "DESCRIPTION"            => _tr("QUO_onlyagentsdynamic"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrYesNo,
                                                    "VALIDATION_TYPE"        => "ereg",
                                                    "VALIDATION_EXTRA_PARAM" => "^(yes|no){1}$"),
                            "calling_restriction" => array("LABEL"          => _tr("Agent Restrinctions"),
                                                    "DESCRIPTION"            => _tr("QUO_agentrestriction"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array(_tr("as called"),_tr("no followme"),_tr("only extension")),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "category"     => array("LABEL"          => _tr("Default Destination"),
                                                    "DESCRIPTION"            => _tr("QUO_defaultdestination"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $category,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "destination" => array("LABEL"                  => _tr(""),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $destiny,
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
        "queue_number"  => array("LABEL"            => _tr("Queue Number"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "TEXT",
                        "INPUT_EXTRA_PARAM"      => "",
                        "VALIDATION_TYPE"        => "text",
                        "VALIDATION_EXTRA_PARAM" => ""),
        "queue_name"  => array("LABEL"            => _tr("Queue Name"),
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

    return reportQueue($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function getAction(){
    global $arrPermission;
    if(getParameter("create_queue"))
        return (in_array('create',$arrPermission))?'new_queue':'report';
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
