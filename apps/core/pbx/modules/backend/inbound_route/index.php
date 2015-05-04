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
        case "new_inbound":
            $content = viewFormInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_edit":
            $content = viewFormInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveNewInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "reloadAasterisk":
            $content = reloadAasterisk($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default: // report
            $content = reportInbound($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function reportInbound($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error = "";
    $pORGZ = new paloSantoOrganization($pDB);

    $domain=getParameter("organization");
    $name=getParameter("name"); // 
    
    $domain=empty($domain)?'all':$domain;
    if($credentials['userlevel']!="superadmin"){
        $domain=$credentials['domain'];
    }
    
    $url['menu']=$module_name;
    $url['organization']=$domain;
    $url['name']=$name;

    if($credentials['userlevel']=="superadmin"){
        if(isset($domain) && $domain!="all"){
            $pInbound = new paloSantoInbound($pDB,$domain);
        }else{
            $pInbound = new paloSantoInbound($pDB,"");
        }
        $total=$pInbound->getNumInbound($domain,$name);
        
        $arrOrgz=array("all"=>_tr("all"));
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }else{
        $arrOrgz=array();
        $pInbound = new paloSantoInbound($pDB,$domain);
        $total=$pInbound->getNumInbound($domain,$name);
    }

    if($total===false){
        $error=$pInbound->errMsg;
        $total=0;
    }

    $limit=20;
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $oGrid->setTitle(_tr('Inbound Routes List'));
    $oGrid->setUrl($url);
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    
    if($credentials['userlevel']=="superadmin"){
        $arrColumns[]=_tr("Organization");
    }
    $arrColumns[]=_tr("Description");
    $arrColumns[]=_tr("DID Number")."/"._tr("CID Number");
    $arrColumns[]=_tr("CID Prefix");
    $arrColumns[]=_tr("Language");
    $arrColumns[]=_tr("Destination");
    $oGrid->setColumns($arrColumns);
    

    $arrInbound=array();
    $arrData = array();
    
    if($total!=0)
        $arrInbound = $pInbound->getInbounds($domain,$name,$limit,$offset);

    if($arrInbound===false){
        $error=_tr("Error to obtain Inbounds").$pInbound->errMsg;
        $arrInbound=array();
    }

    foreach($arrInbound as $inbound) {
        $arrTmp=array();
        if($credentials['userlevel']=='superadmin')
            $arrTmp[]=$arrOrgz[$inbound['organization_domain']]; //organization
        $arrTmp[] = "&nbsp;<a href='?menu=inbound_route&action=view&id_inbound=".$inbound['id']."&organization={$inbound['organization_domain']}'>".htmlentities($inbound['description'],ENT_QUOTES,"UTF-8")."</a>";
        $did=$cid="";
        if($inbound["did_number"]!=""){
            $did=$inbound["did_number"];
        }
        if($inbound["cid_number"]!=""){
            $cid=$inbound["cid_number"];
        }
        $arrTmp[] = $did." / ".$cid;
        $arrTmp[] = $inbound["cid_prefix"];
        $arrTmp[] = $inbound["language"];
        $arrTmp[] = $inbound["destination"];
        $arrData[] = $arrTmp;
    }
            
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='report'>");
    if($pORGZ->getNumOrganization(array()) >= 1){
        if(in_array('create',$arrPermission)){
            if($credentials['userlevel']=='superadmin'){
                $oGrid->addComboAction("organization_add",_tr("ADD Incoming Route"), array_slice($arrOrgz,1), $selected=null, "create_inbound", $onchange_select=null);
            }else{
                $oGrid->addNew("create_inbound",_tr("ADD Incoming Route"));
            }   
        }
        if($credentials['userlevel']=='superadmin'){
            $_POST["organization"]=$domain;
            $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true);
        }
        $_POST["name"]=$name; // name inboundrout
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

function viewFormInbound($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    global $arrPermission;
    $error = "";

    $arrInbound=array();
    $action = getParameter("action");
    
    $idInbound=getParameter("id_inbound");
    if($action=="view" ||  getParameter("edit") || getParameter("save_edit")){
        if(!isset($idInbound)){
            $error=_tr("Invalid Inbound Route");
        }
        $domain=getParameter('organization');
        if($credentials['userlevel']!='superadmin'){
            $domain=$credentials['domain'];
        }
        $pInbound = new paloSantoInbound($pDB,$domain);
        $arrInbound = $pInbound->getInboundById($idInbound);
        if($error==""){
            if($arrInbound===false){
                $error=$pInbound->errMsg;
            }else if(count($arrInbound)==0){
                $error=_tr("Inbound doesn't exist");
            }else{
                if(getParameter("save_edit"))
                    $arrInbound=$_POST;
                $smarty->assign("fax_detect_act",$arrInbound["fax_detect"]);
                $smarty->assign("privacy_act",$arrInbound["primanager"]);
            }
        }
        
        if($error!=""){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",$error);
            return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{
        if($credentials['userlevel']=='superadmin'){
            if(getParameter("create_inbound")){
                $domain=getParameter('organization_add'); //este parametro solo es selecionable cuando es el superadmin quien crea la ruta
            }else
                $domain=getParameter('organization');
        }else{
            $domain=$credentials['domain'];
        }
        $pInbound = new paloSantoInbound($pDB,$domain);
        $domain=$pInbound->getDomain();
        if(empty($domain)){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Invalid Organization"));
            return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
        
        if(getParameter("create_inbound")){
            $arrInbound["primanager"]="no";
            $arrInbound["fax_detect"]="no";
            $arrInbound["fax_time"]="4";
            $arrInbound["fax_type"]="fax";
            $arrInbound["min_length"]="3";
            $arrInbound["max_attempt"]="5";
            $arrInbound["goto"]="";
        }else
            $arrInbound=$_POST; 
    }

    $goto=$pInbound->getCategoryDefault($domain);
    if($goto===false)
        $goto=array();
    $res=$pInbound->getDefaultDestination($domain,$arrInbound["goto"]);
    $destiny=($res==false)?array():$res;
    $arrForm = createFieldForm($goto,$destiny,$pInbound->getFaxExtesion(),$pInbound->getDetectFax(),$pInbound->getMoHClass($domain));
    $oForm = new paloForm($smarty,$arrForm);

    if($action=="view"){
        $oForm->setViewMode();
    }else if($action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
    }
    
    $smarty->assign("EDIT_ROUTE",in_array('edit',$arrPermission));
    $smarty->assign("CREATE_ROUTE",in_array('create',$arrPermission));
    $smarty->assign("DELETE_ROUTE",in_array('delete',$arrPermission));
    
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
    $smarty->assign("id_inbound", $idInbound);
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("ORGANIZATION_LABEL",_tr("Organization Domain"));
    $smarty->assign("ORGANIZATION",$domain);
    $smarty->assign("CALLERID", _tr("Caller Id"));
    $smarty->assign("SEQUENCE", _tr("TRUNKS SEQUENCE"));
    $smarty->assign("SETTINGS", _tr("Settings"));
    $smarty->assign("PEERDETAIL", _tr("PEER Details"));
    $smarty->assign("USERDETAIL", _tr("USER Details"));
    $smarty->assign("OVEREXTEN", _tr("Override Extension"));
    $smarty->assign("CIDPRIORITY", _tr("Cid Priority Route"));
    $smarty->assign("PRIVACY", _tr("Privacy"));
    $smarty->assign("PRIVACYMANAGER", _tr("Privacy Manager"));
    $smarty->assign("SIGNALRING", _tr("Signal Ringing"));
    $smarty->assign("CIDSOURCE", _tr("CID Lookup Source"));
    $smarty->assign("FAXDETECT", _tr("Fax Detect"));
    $smarty->assign("DETECTFAX", _tr("Detect Fax "));
    $smarty->assign("LANGUAGE", _tr("Language"));
    $smarty->assign("SETDESTINATION", _tr("Set Destination"));
        
    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",_tr("Inbound Route"), $arrInbound);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    return $content;
}

function saveNewInbound($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $error = "";
    $continue = true;
    $success = false;

    $domain=getParameter('organization'); //este parametro solo es selecionable cuando es el superadmin quien crea la ruta
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }

    $pInbound=new paloSantoInbound($pDB,$domain);
    $domain=$pInbound->getDomain();
    if(empty($domain)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Organization"));
        return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $goto=$pInbound->getCategoryDefault($domain);
    if($goto===false)
        $goto=array();
    $res=$pInbound->getDefaultDestination($domain,getParameter("goto"));
    $destiny=($res==false)?array():$res;

    $arrFormOrgz = createFieldForm($goto,$destiny,$pInbound->getFaxExtesion(),$pInbound->getDetectFax(),$pInbound->getMoHClass($domain));
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
        return viewFormInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $description = getParameter("description");
        if($description==""){
            $error=_tr("Description can not be empty.");
            $continue=false;
        }
        
        if($pInbound->validateDestine($domain,getParameter("destination"))==false){
            $error=_tr("You must select a destination for this inbound.");
            $continue=false;
        }
            
        if($continue){
            //seteamos un arreglo con los parametros configurados
            $arrProp=array();
            $arrProp["description"]=getParameter("description");
            $arrProp['did_number']=getParameter("did_number");
            $arrProp['cid_number']=getParameter("cid_number");
            //$arrProp['cid_priority'] = (getParameter("cid_priority")) ? "on" : "off";
            $arrProp['alertinfo']=getParameter("alertinfo");
            $arrProp['cid_prefix']=getParameter("cid_prefix");
            $arrProp['moh']=getParameter("moh");
            $arrProp['ringing'] = getParameter("ringing");
            $arrProp['delay_answer']=getParameter("delay_answer");
            $arrProp['primanager'] = getParameter("primanager");
            if($arrProp['primanager']=="yes"){
                $arrProp['max_attempt']=getParameter("max_attempt");
                $arrProp['min_length']=getParameter("min_length");
            }
            $arrProp['fax_detect']=getParameter('fax_detect');
            if($arrProp['fax_detect']=="yes"){
                $arrProp['fax_type']=getParameter('fax_type');
                $arrProp['fax_time']=getParameter('fax_time');
                $arrProp['fax_destiny']=getParameter('fax_destiny');
            }
            $arrProp['language']=getParameter("language");
            $arrProp['goto']=getParameter("goto");
            $arrProp['destination']=getParameter("destination");
            $arrProp['domain']=$domain;
        }

        if($continue){
            $pDB->beginTransaction();
            $success=$pInbound->createNewInbound($arrProp);
            if($success)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pInbound->errMsg;
        }
    }

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Inbound has been created successfully"));
            //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function saveEditInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $continue = true;
    $success = false;
    
    $idInbound=getParameter("id_inbound");
    //obtenemos la informacion del usuario por el id dado, sino existe el inbound mostramos un mensaje de error
    if(!isset($idInbound)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Inbound"));
        return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $domain=getParameter('organization');
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    $pInbound = new paloSantoInbound($pDB,$domain);
    $arrInbound = $pInbound->getInboundById($idInbound);
    if($arrInbound===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pInbound->errMsg));
        return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else if(count($arrInbound)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Inbound doesn't exist"));
        return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        if($pInbound->validateDestine($domain,getParameter("destination"))==false){
            $error=_tr("You must select a destination for this inbound.");
            $continue=false;
        }
        
        if($continue){
            //seteamos un arreglo con los parametros configurados
            $arrProp=array();
            $arrProp["id_inbound"]=$idInbound;
            $arrProp["description"]=getParameter("description");
            $arrProp['did_number']=getParameter("did_number");
            $arrProp['cid_number']=getParameter("cid_number");
            $arrProp['alertinfo']=getParameter("alertinfo");
            $arrProp['cid_prefix']=getParameter("cid_prefix");
            $arrProp['moh']=getParameter("moh");
            $arrProp['ringing'] = getParameter("ringing");
            $arrProp['delay_answer']=getParameter("delay_answer");
            $arrProp['primanager'] = getParameter("primanager");
            if($arrProp['primanager']=="yes"){
                $arrProp['max_attempt']=getParameter("max_attempt");
                $arrProp['min_length']=getParameter("min_length");
            }
            $arrProp['fax_detect']=getParameter('fax_detect');
            if($arrProp['fax_detect']=="yes"){
                $arrProp['fax_type']=getParameter('fax_type');
                $arrProp['fax_time']=getParameter('fax_time');
                $arrProp['fax_destiny']=getParameter('fax_destiny');
            }
            
            $arrProp['language']=getParameter("language");
            $arrProp['goto']=getParameter("goto");
            $arrProp['destination']=getParameter("destination");
            $arrProp['domain']=$domain;
        }

        if($continue){
            $pDB->beginTransaction();
            $success=$pInbound->updateInboundPBX($arrProp);
            
            if($success)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pInbound->errMsg;
        }
    }

    $smarty->assign("id_inbound", $idInbound);

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("Inbound has been edited successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
        $content = reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function deleteInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $continue=true;
    $success=false;
    
    $idInbound=getParameter("id_inbound");
    $domain=getParameter('organization');
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials['domain'];
    }
    
    //obtenemos la informacion del inbound por el id dado, 
    if(!isset($idInbound)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid Inbound"));
        return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $pInbound=new paloSantoInbound($pDB,$domain);
    $arrInbound = $pInbound->getInboundById($idInbound, $domain);
    if($arrInbound===false){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($pInbound->errMsg));
        return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else if(count($arrInbound)==0){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Inbound doesn't exist"));
        return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $pDB->beginTransaction();
        $success = $pInbound->deleteInbound($idInbound);
        if($success)
            $pDB->commit();
        else
            $pDB->rollBack();
        $error .=$pInbound->errMsg;
    }

    if($success){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("The Inbound Route was deleted successfully"));
        //mostramos el mensaje para crear los archivos de ocnfiguracion
        $pAstConf=new paloSantoASteriskConfig($pDB);
        $pAstConf->setReloadDialplan($domain,true);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr($error));
    }

    return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function generateOptionNum($start, $end){
    $arr = array();
    for($i=$start;$i<=$end;$i++){
        $arr[$i]=$i;
    }
    return $arr;
}

function createFieldForm($goto,$destination,$faxes,$arrDetect,$music)
{
    $pDB=new paloDB(generarDSNSistema("asteriskuser", "elxpbx"));
    $oneToTen = generateOptionNum(1, 10);
    $oneToFifteen = generateOptionNum(1, 15);
    $twoToTen = generateOptionNum(2, 10);
    $arrLng=getLanguagePBX();
    $arrMusic=array(""=>_tr("-don't music-"));
    foreach($music as $key => $value){
        $arrMusic[$key] = $value;
    }
    $arrFormElements = array("description"	=> array("LABEL"             => _tr('Description'),
                                                    "DESCRIPTION"            => _tr("IN_description"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "did_number"   	=> array("LABEL"             => _tr("DID Number"),
                                                    "DESCRIPTION"            => _tr("IN_didnumber"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "cid_number" 	=> array("LABEL"             => _tr("Caller ID Number"),
                                                    "DESCRIPTION"            => _tr("IN_calleridnumber"),
                                                    "REQUIRED"              => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "alertinfo"    	=> array("LABEL"             => _tr("Alert Info"),
                                                    "DESCRIPTION"            => _tr("IN_alertinfo"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:100px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "cid_prefix" 	=> array("LABEL"             => _tr("CID Name Prefix"),
                                                    "DESCRIPTION"            => _tr("IN_cidnameprefix"),
                                                    "REQUIRED"               => "no",
                                                     "INPUT_TYPE"            => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:100px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "moh"	    	=> array("LABEL"             => _tr("Music On Hold"),
                                                    "DESCRIPTION"            => _tr("IN_moh"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrMusic,
                                                    "VALIDATION_TYPE"        => "",
                                                    "VALIDATION_EXTRA_PARAM" => ""),  
                            "delay_answer" 	=> array("LABEL"             => _tr("Pause Before Answer"),
                                                    "DESCRIPTION"            => _tr("IN_pausebeforeanswer"),
                                                    "REQUIRED"               => "no",
                                                     "INPUT_TYPE"            => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:100px"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "max_attempt" 	=> array("LABEL"             => _tr("Max Attempts"),
                                                     "REQUIRED"              => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $oneToTen,
                                                    "VALIDATION_TYPE"        => "",
                                                    "VALIDATION_EXTRA_PARAM" => ""),  
                            "min_length" 	=> array("LABEL"             => _tr("Min Length"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $oneToFifteen,
                                                    "VALIDATION_TYPE"        => "",
                                                    "VALIDATION_EXTRA_PARAM" => ""),  
                            "language" 	=> array("LABEL"             => _tr("Language"),
                                                    "DESCRIPTION"            => _tr("IN_language"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrLng,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "goto"  	=> array("LABEL"             => _tr("Destine"),
                                                    "DESCRIPTION"            => _tr("IN_destiny"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $goto,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""), 
                            "destination"  	=> array("LABEL"             => _tr(""),
                                                    "DESCRIPTION"            => _tr("IN_destiny"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $destination,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""), 
                            "primanager"   => array("LABEL"             => _tr("Privacy Manager"),
                                                    "DESCRIPTION"            => _tr("IN_privacymanager"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array("yes"=>_tr("Yes"),"no"=>"No"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "ringing"  => array("LABEL"             => _tr("Signal RINGING"),
                                                    "DESCRIPTION"            => _tr("IN_signalring"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "CHECKBOX",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "fax_time"   => array("LABEL"             => _tr("Fax Detection Time"),
                                                     "REQUIRED"              => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $oneToTen,
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),  
                            "fax_type"    => array("LABEL"             => _tr("Fax Detection Type"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrDetect,
                                                    "VALIDATION_TYPE"        => "",
                                                    "VALIDATION_EXTRA_PARAM" => ""), 
                            "fax_detect"  => array("LABEL"             => _tr("Activate Fax Detection"),
                                                    "DESCRIPTION"            => _tr("IN_activatefaxdetection"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => array("yes"=>_tr("Yes"),"no"=>"No"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                            "fax_destiny" => array("LABEL"             => _tr("Fax Extension"),
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $faxes,
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
        return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
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

    return reportInbound($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function getAction(){
    global $arrPermission;
    if(getParameter("create_inbound"))
        return (in_array('create',$arrPermission))?'new_inbound':'report';
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
    else if(getParameter("action")=="view_edit")
        return "view_edit";
	else if(getParameter("action")=="reloadAsterisk")
		return "reloadAasterisk";
    else
        return "report"; //cancel
}
?>
