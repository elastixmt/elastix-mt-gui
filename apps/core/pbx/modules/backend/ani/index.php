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
  $Id: index.php,v 1.1 2014-03-12 Bruno Macias bmacias@elastix.org Exp $ $*/
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
        case "save_edit":
            $content = saveEditANI($pDB, $arrCredentials);
            break;
        default: // report
            $content = reportANI($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;

}

function reportANI($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error = "";
    $smarty->assign("SHOW_DIV_ERROR","0"); //FIXED: can show div error message with javascript
    $smarty->assign("mb_message","hide");  //FIXED: can show div error message with javascript
    
    $pORGZ = new paloSantoOrganization($pDB);
    $domain=getParameter("organization");
    $domain=empty($domain)?'all':$domain;
    if($credentials['userlevel']!="superadmin"){
        $domain=$credentials['domain'];
    }
    $ani_prefix=getParameter("ani_prefix");
    
    $pANI = new paloSantoANI($pDB,$domain);
  
    $url['menu']         = $module_name;
    $url['organization'] = $domain;
    $url['ani_prefix']   = $ani_prefix;
    
    $total=$pANI->getNumANI($domain,$ani_prefix);
    $arrOrgz=array();
    if($credentials['userlevel']=="superadmin"){
        $arrOrgz=array("all"=>_tr("all"));
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }
    
    if($total===false){
        $error = $pANI->errMsg;
        $total = 0;
    }

    $limit=20;

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    
    $oGrid->setTitle(_tr('ANI List'));
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    $oGrid->setURL($url);

    $arrColum=array(); 
    if($credentials['userlevel']=="superadmin"){
        $arrColum[]=_tr("Organization");
    }
    $arrColum[]=_tr("Trunk Name");
    $arrColum[]=_tr("Prefix ANI");
    $oGrid->setColumns($arrColum);

    $arrANI=array();
    $arrData = array();
    if($total!=0){
        $arrANI = $pANI->getANI($domain,$ani_prefix,$limit,$offset);
    }

    if($arrANI===false){
        $error=_tr("Error to obtain ANI").$pANI->errMsg;
        $arrANI = array();
    }
   
    if($error!=""){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",$error);
        $smarty->assign("SHOW_DIV_ERROR","1");
    }

    foreach($arrANI as $row) {
        $arrTmp=array();
        if($credentials['userlevel']=="superadmin"){
            $arrTmp[] = $arrOrgz[$row["organization_domain"]];
        }
        $arrTmp[] = $row["name"];
        $arrTmp[] = "<input type='text' style='width:60px; text-align:center;' id='text#{$row['organization_domain']}#{$row['trunkid']}' value='{$row['ani_prefix']}' />&nbsp;&nbsp;
                     <input type='button' id='button#{$row['organization_domain']}#{$row['trunkid']}' value='"._tr("Save")."' />";
        $arrData[] = $arrTmp;
    }
            
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='report'>");
    if($pORGZ->getNumOrganization(array()) == 0 ){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",_tr("It's necesary you create at least one organization so you can use this module"));
        $smarty->assign("SHOW_DIV_ERROR","1");
    }
    
    if($credentials['userlevel']=='superadmin'){
        $_POST["organization"]=$domain;
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true);
    }
    $_POST["ani_prefix"]=$ani_prefix;
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("ANI Prefix")." = ".$ani_prefix, $_POST, array("ani_prefix" => "")); 
    $arrFormElements = createFieldFilter($arrOrgz);
    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
    $oGrid->showFilter(trim($htmlFilter));
    
    return $oGrid->fetchGrid(array(), $arrData);
}

function saveEditANI($pDB, $credentials)
{
    $jsonObject  = new PaloSantoJSON();
    $ani_domain  = getParameter("ani_domain");
    $ani_trunkid = getParameter("ani_trunkid");
    $ani_prefix  = getParameter("ani_prefix");
    $ani_prefix  = trim($ani_prefix);
    
    if($ani_prefix=="")
        $ani_prefix = NULL;
    
    if($credentials['userlevel']!='superadmin'){
        $ani_domain=$credentials['domain'];
    }
    
    $pANI = new paloSantoANI($pDB,$ani_domain);
    
    $pDB->beginTransaction();
    $success = $pANI->updateANI_Prefix($ani_trunkid,$ani_prefix);

    if($success){
        $pDB->commit();
        $jsonObject->set_message(_tr("Prefix ANI was saved successfully.")." [{$ani_prefix}] -> {$ani_domain}");
    }
    else{
        $pDB->rollBack();
        $error = $pANI->errMsg;
        $jsonObject->set_error($error);
    }
    
    return $jsonObject->createJSON();
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
        "ani_prefix"  => array("LABEL"            => _tr("ANI Prefix"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "TEXT",
                        "INPUT_EXTRA_PARAM"      => "",
                        "VALIDATION_TYPE"        => "text",
                        "VALIDATION_EXTRA_PARAM" => ""),
        );
    return $arrFields;
}

function getAction(){
    global $arrPermission;
    if(getParameter("action")=="save_edit")
        return (in_array('edit',$arrPermission))?'save_edit':'report';    
    else
        return "report"; //cancel
}
?>
