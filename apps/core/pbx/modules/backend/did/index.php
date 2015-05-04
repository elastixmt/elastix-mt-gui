<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.2.0-29                                               |
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
  $Id: index.php,v 1.1 2012-02-07 11:02:12 Rocio Mera rmera@palosanto.com Exp $ */
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoJSON.class.php";

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
        case "new_did":
            $content = viewFormDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view":
            $content = viewFormDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "view_edit":
            $content = viewFormDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_new":
            $content = saveNewDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "save_edit":
            $content = saveEditDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "delete":
            $content = deleteDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "get_country_code":
            $content=get_country_code();
            break;
        case "validate_delete":
            $content = validate_delete($pDB,$arrCredentials);
            break;
        default: // report
            $content = reportDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
    }
    return $content;
}
            

function reportDID($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $pDID = new paloDidPBX($pDB);
    $pORGZ = new paloSantoOrganization($pDB);
    $error="";
    
    
    $did_number=getParameter("did_number"); // did number
    $status=getParameter("status"); // asignado o sin asignar
    $country=getParameter("country"); // pais al que pertence el did
    $domain=getParameter("organization"); // did que se encuentran asigandos a dicha organizacion
 
    $url['menu']=$module_name;
    if(isset($did_number) && $did_number!=''){
        $expression=$pDID->getRegexPatternFromAsteriskPattern($did_number);
        if($expression===false)
            $did_number='';
    }
    $status=empty($status)?'all':$status;
    $domain=empty($domain)?'all':$domain;
    
    $url['did_number']=$did_number;
    $url['status']=$status;
    $url['country']=$country;
    $url['organization']=$domain;
    
    $total=0;
    $total=$pDID->getTotalDID($domain,$did_number,$country,$status);
    
    if($total===false){
        $error=$pDID->errMsg;
        $total=0;
    }

    $limit=20;

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $url = "?menu=$module_name";
    $arrGrid = array("title"    => _tr('DID List'),
                "url"      => $url,
                "width"    => "99%",
                "start"    => ($total==0) ? 0 : $offset + 1,
                "end"      => $end,
                "total"    => $total,
                'columns'   =>  array(
                    array("name"      => _tr("DID"),),
                    array("name"      => _tr("Organization Domain"),),
                    array("name"      => _tr("Type"),),
                    array("name"      => _tr("Country"),),
                    array("name"      => _tr("Country Code / Area Code"),)
                    ),
                );
                
    $arrData = array();
    $arrDID = array();
    if($total!=0){
        $arrDID=$pDID->getDIDs($domain,$did_number,$country,$status,$limit,$offset);
    }
    if($arrDID===false){
        $error=_tr("Error to obtain DID").$pDID->errMsg;
        $arrData=array();
    }else{
        //si es un usuario solo se ve su didsion
        //si es un administrador ve todas las didsiones
        foreach($arrDID as $did) {
            $arrTmp[0] = "&nbsp;<a href='?menu=did&action=view&id_did=".$did['id']."'>".$did['did']."</a>";
            $arrTmp[1] = $did["organization_domain"];
            $arrTmp[2] = $did["type"];
            $arrTmp[3] = $did["country"];
            $arrTmp[4] = $did["country_code"]." / ".$did["area_code"];
            $arrData[]=$arrTmp;
        }
    }
    
    if(in_array('create',$arrPermission)){
        $oGrid->addNew("create_did",_tr("New DID"));
    }
    
    $_POST["did_number"]=$did_number;
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("DID Number")." = ".$did_number, $_POST, array("did_number" => ""));
    
    $arrStatus=array("all"=>_tr('All'),"free"=>_tr("No Assigned"),"busy"=>_tr('Assigned'));
    $_POST["status"]=$status;
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Status")." = ".$arrStatus[$status], $_POST, array("status" => _tr("all")),true);
    
    $_POST["country"]=$country;
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Country")." = ".$country, $_POST, array("country" => ""));
    
    $arrOrgz=array("all"=>_tr("all"));
    foreach(($pORGZ->getOrganization(array())) as $value){
        $arrOrgz[$value["domain"]]=$value["name"];
    }
    $_POST["organization"]=$domain;
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true); //organization
    
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='search'>");
    $arrFormElements = createFieldFilter($arrOrgz,$arrStatus);
    $oFilterForm = new paloForm($smarty, $arrFormElements);
    
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
    $oGrid->showFilter(trim($htmlFilter));
    
	if($error!=""){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",$error);
    }

    $content = $oGrid->fetchGrid($arrGrid,$arrData);
    return $content;
}

function viewFormDID($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $pDID=new paloDidPBX($pDB);
    $error = "";
    //conexion elastix.db
    $pDB2 = new paloDB($arrConf['elastix_dsn']['elastix']);
    $pACL = new paloACL($pDB2);
    $pORGZ = new paloSantoOrganization($pDB2);
    
    $arrDID=array();
    $ex_chans=array();
    $action = getParameter("action");
    
    $idDID=getParameter("id_did");
    
    $smarty->assign("DISPLAY_analog","display:none");
    
    if($action=="view" || $action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        if(!isset($idDID)){
            $error=_tr("Invalid DID");
        }else{
            $arrDID=$pDID->getDID_id($idDID);
            if($arrDID===false){
                $error=_tr($pDID->errMsg);
            }else if(count($arrDID)==0){
                $error=_tr("DID doesn't exist");
            }else{
                $type=$arrDID["type"];
                $smarty->assign("DID",$arrDID["did"]);
                $smarty->assign("TYPE",$type);
                
                $smarty->assign("DISPLAY_".$type,"display:block");
                
                if(getParameter("save_edit")){
                    if($type=="analog"){
                        if(isset($_POST["select_chans"]))
                            $smarty->assign("CHANNELS",$_POST["select_chans"]);
                        if($_POST["select_chans"]==""){
                            foreach($arrDID["select_chans"] as $value){
                                $ex_chans[$value]=$value;
                            }
                        }
                    }
                    $arrDID=$_POST;
                }else{
                    //en caso de ser analogica el conjunto de canales seleccionados
                    if($type=="analog"){
                        $select_chans=implode(",",$arrDID["select_chans"]);
                        if(isset($arrDID["select_chans"]))
                            $smarty->assign("CHANNELS",$select_chans.",");
                    }
                }   
                
                if($action=="view"){
                    if($type=="analog"){
                        $smarty->assign("CHANNELS",$select_chans);
                    }
                }
            }
        }
    }else{
        if(getParameter("create_did"))
            $smarty->assign("DISPLAY_analog","display:table-row");
        else{
            if($arrDID["type"]=="analog"){
                if(isset($_POST["select_chans"]))
                    $smarty->assign("CHANNELS",$_POST["select_chans"]);
            }
            $smarty->assign("DISPLAY_".$arrDID["type"],"display:table-row");
        }
        $arrDID=$_POST;
    }
    
    if($error!=""){
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message",$error);
        return reportDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    $arrChannel=array("none"=>_tr("--Select one--"));
    $tmpChannel=$pDID->getAnalogChannelsFree();
    if(is_array($ex_chans))
        $arrChannel=array_merge($arrChannel,$ex_chans);
    if($tmpChannel!=false){
        $arrChannel=array_merge($arrChannel,$tmpChannel);
    }
    $arrForm = createFieldForm($arrChannel);
    $oForm = new paloForm($smarty,$arrForm);
    
    if($action=="view"){
        $oForm->setViewMode();
    }else if($action=="view_edit" || getParameter("edit") || getParameter("save_edit")){
        $oForm->setEditMode();
    }

    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("APPLY_CHANGES", _tr("Apply changes"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("CONFIRM_CONTINUE", _tr("Are you sure you wish to delete this DID?"));
    $smarty->assign("MODULE_NAME",$module_name);
    $smarty->assign("VOIP","Channel");
    $smarty->assign("id_did", $idDID);
    $smarty->assign("MESSAGE_DID",_tr("message did"));
    $smarty->assign("DID_PARAMETERS",_tr("DID Parameters"));

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl","DID", $arrDID);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewDID($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $pDID=new paloDidPBX($pDB);
    $error = "";
    
    $arrChannel=array("none"=>_tr("--Select one--"));
    $tmpChannel=$pDID->getAnalogChannelsFree();
    if($tmpChannel!=false){
        $arrChannel=array_merge($arrChannel,$tmpChannel);
    }
    $arrForm = createFieldForm($arrChannel);
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
        return viewFormDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $arrProp=array();
        $arrProp["country"]=getParameter("country");
        if($arrProp["country"]=="0" || !isset($arrProp["country"])){
                $error=_tr("You must select a country");
        }else{
            $arrProp["did"]=getParameter("did");
            $arrProp["type"]=getParameter("type");
            $arrProp["city"]=getParameter("city");
            $arrProp["country_code"]=getParameter("country_code");
            $arrProp["area_code"]=getParameter("area_code");
            $arrProp["id_channel"]=getParameter("id_channel_".$arrProp["type"]);
            
            $arrProp["select_chans"]=getParameter("select_chans");
            $pDB->beginTransaction();
            $exito=$pDID->saveNewDID($arrProp);
            if($exito)
                $pDB->commit();
            else
                $pDB->rollBack();
            $error .=$pDID->errMsg;
        }
    }
    
    if($exito){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("DID was created successfully"));
        $content = reportDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function saveEditDID($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $pDID=new paloDidPBX($pDB);
    $error = "";
    $exito=false;
    
    $idDID=getParameter("id_did");
    if(!preg_match("/^[0-9]+$/", $idDID)){
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("Invalid DID"));
        return reportDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    
    $arrChannel=array("none"=>_tr("--Select one--"));
    $tmpChannel=$pDID->getAnalogChannelsFree();
    if($tmpChannel!=false){
        $arrChannel=array_merge($arrChannel,$tmpChannel);
    }
    $arrForm = createFieldForm($arrChannel);
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
        return viewFormDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $arrProp=array();
        $did=$pDID->getDID_id($idDID);
        if($did==false){
            $error .=$pDID->errMsg;
        }else{
            $arrProp["country"]=getParameter("country");
            if($arrProp["country"]=="0" || !isset($arrProp["country"])){
                $error=_tr("You must select a country");
            }else{
                $arrProp["id_did"]=$idDID;
                $arrProp["city"]=getParameter("city");
                $arrProp["country_code"]=getParameter("country_code");
                $arrProp["area_code"]=getParameter("area_code");
                $arrProp["select_chans"]=getParameter("select_chans");
                
                $pDB->beginTransaction();
                $exito=$pDID->saveEditDID($arrProp);
                if($exito)
                    $pDB->commit();
                else
                    $pDB->rollBack();
                $error .=$pDID->errMsg;
            }
        }
    }
    
    if($exito){
         //procedemos a reescribir los archivos extensions_did.conf chan_dhadi_additonals.conf
        $smarty->assign("mb_title", _tr("MESSAGE"));
        if(writeDidFile($error,$did["type"])==true)
            $smarty->assign("mb_message",_tr("DID was updated successfully"));
        else
            $smarty->assign("mb_message",_tr("DID couldn't be updated. ").$error);
        $content = reportDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
        $content = viewFormDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }
    return $content;
}

function deleteDID($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){
    $pDID=new paloDidPBX($pDB);
    $error=$type="";
    $exito=false;

    $idDID=getParameter("id_did");
    if(!preg_match("/^[0-9]+$/", $idDID)){
        $error=_tr("Invalid DID");
    }else{
        $pDB->beginTransaction();
        $exito=$pDID->deleteDID($idDID,$type);
        if($exito)
            $pDB->commit();
        else
            $pDB->rollBack();
        $error .=$pDID->errMsg;
    }
    
    if($exito){
        //procedemos a reescribir el archivo chan_dhadi_additonals.conf
        $smarty->assign("mb_title", _tr("MESSAGE"));
        if(writeDidFile($error,$type)==true)
            $smarty->assign("mb_message",_tr("DID was deleted successfully"));
        else
            $smarty->assign("mb_message",_tr("DID was deleted. ").$error);
    }else{
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",$error);
    }
    
    return reportDID($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);  
}

function writeDidFile(&$error,$type){
    if($type=="analog"){
        $sComando = '/usr/bin/elastix-helper asteriskconfig createFileDahdiChannelAdd 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $error = _tr("Error writing did file").implode('', $output);
            return FALSE;
        }
    }
    return true;
}

function get_country_code(){
    $jsonObject = new PaloSantoJSON();
    $country=getParameter("country");
    $arrSettings=getCountrySettings($country);
    if($arrSettings==false){
        $jsonObject->set_message("");
    }else{
        $jsonObject->set_message($arrSettings["code"]);
    }
    return $jsonObject->createJSON();
}

function validate_delete($pDB,$credentials){
    $jsonObject = new PaloSantoJSON();
    $id_did=getParameter("id_did");
    $pDB="Select did, organization_domain from did where id_did=?";
    $result=$pDB->getFirstRowQuery($query,true,array($id_did));
    if($arrSettings==false){
        $jsonObject->error(_tr("Did doesn't exist"));
    }else{
        $message="";
        if(!is_null($result["organization_domain"])){
            $message="DID ".$result["did"]._tr("<b>is assigned</b> to the organzanization with domain <b>")._tr($result["organization_domain"])."</b>";
        }
        $jsonObject->set_message($message._tr("Are you sure you wish to continue?"));
    }
    return $jsonObject->createJSON();
}

function createFieldForm($arrChannel){
    $arrCountry = array(_tr("Select a country").' --');
    $arrCountry = array_merge($arrCountry,getCountry());
    $arrType=array(_tr("analog")=>_tr("analog"),"digital"=>"digital","voip"=>"VoIP");
    $arrFormElements = array("did" => array("LABEL"                  => _tr('DID Number'),
                                                    "DESCRIPTION"            => _tr("DID_number"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px"),
                                                    "VALIDATION_TYPE"        => "numeric",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "type"   => array("LABEL"                => _tr("Type"),
                                                    "DESCRIPTION"            => _tr("DID_type"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrType,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "channel"   => array("LABEL"                => _tr("Channels"),
                                                    "DESCRIPTION"            => _tr("DID_channels"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrChannel,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "country"   => array("LABEL"                  => _tr("Country"),
                                                    "DESCRIPTION"            => _tr("DID_country"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrCountry,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => "",
                                                    "ONCHANGE"         => "select_country();"),
                             "city"   =>   array("LABEL"                  => _tr("City"),
                                                    "DESCRIPTION"            => _tr("DID_city"),
                                                    "REQUIRED"               => "yes",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"100"),
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                             "country_code" => array("LABEL"                  => _tr('Country Code'),
                                                    "DESCRIPTION"            => _tr("DID_countrycode"),
                                                        "REQUIRED"               => "yes",
                                                        "INPUT_TYPE"             => "TEXT",
                                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"50"),
                                                        "VALIDATION_TYPE"        => "numeric",
                                                        "VALIDATION_EXTRA_PARAM" => ""),
                             "area_code"   => array("LABEL"                  => _tr('Area Code'),
                                                    "DESCRIPTION"            => _tr("DID_areacode"),
                                                        "REQUIRED"               => "yes",
                                                        "INPUT_TYPE"             => "TEXT",
                                                        "INPUT_EXTRA_PARAM"      => array("style" => "width:200px","maxlength" =>"50"),
                                                        "VALIDATION_TYPE"        => "numeric",
                                                        "VALIDATION_EXTRA_PARAM" => ""),
                                        );
    return $arrFormElements;
}

function createFieldFilter($arrOrgz,$status)
{
    $arrFields = array(
        "organization"  => array("LABEL"       => _tr("Organization"),
                      "DESCRIPTION"            => _tr("DID_org"),
                      "REQUIRED"               => "no",
                      "INPUT_TYPE"             => "SELECT",
                      "INPUT_EXTRA_PARAM"      => $arrOrgz,
                      "VALIDATION_TYPE"        => "domain",
                      "VALIDATION_EXTRA_PARAM" => ""),
        "status"  => array("LABEL"       => _tr("Status"),
                      "DESCRIPTION"            => _tr("DID_status"),
                      "REQUIRED"               => "no",
                      "INPUT_TYPE"             => "SELECT",
                      "INPUT_EXTRA_PARAM"      => $status,
                      "VALIDATION_TYPE"        => "text",
                      "VALIDATION_EXTRA_PARAM" => ""),
        "country"  => array("LABEL"            => _tr("Country"),
                      "DESCRIPTION"            => _tr("DID_country"),
                      "REQUIRED"               => "no",
                      "INPUT_TYPE"             => "TEXT",
                      "INPUT_EXTRA_PARAM"      => "",
                      "VALIDATION_TYPE"        => "text",
                      "VALIDATION_EXTRA_PARAM" => ""),
        "did_number"  => array("LABEL"         => _tr("Did Number"),
                      "DESCRIPTION"            => _tr("DID_number"),
                      "REQUIRED"               => "no",
                      "INPUT_TYPE"             => "TEXT",
                      "INPUT_EXTRA_PARAM"      => "",
                      "VALIDATION_TYPE"        => "text",
                      "VALIDATION_EXTRA_PARAM" => ""),
                      
        );
    return $arrFields;
}

function getAction()
{
    global $arrPermission;
    if(getParameter("create_did"))
        return (in_array('create',$arrPermission))?'new_did':'report';
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
    else if(getParameter("action")=="get_country_code")
        return "get_country_code";
    else if(getParameter("action")=="validate_delete")
        return (in_array('delete',$arrPermission))?'validate_delete':'report';
    else
        return "report"; //cancel
}
?>
