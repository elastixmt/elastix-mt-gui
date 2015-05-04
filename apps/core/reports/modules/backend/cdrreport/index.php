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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:21 gcarrillo Exp $ 
  $Id: index.php,v 2.0.0.0 2012/12/26 21:31:21 rmera Exp $ */


include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoDB.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoCDR.class.php";
require_once "libs/misc.lib.php";

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    
     //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $dsn = generarDSNSistema('asteriskuser', 'asteriskcdrdb');
    $pDB = new paloDB($dsn);

    //user credentials
    global $arrCredentials;
        
    $action = getAction();
    $content = "";
       
    switch($action){
        case "delete":
            $content = deleteCDR($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
        default: // report
            $content = reportCDR($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $arrCredentials);
            break;
    }
    return $content;
}

function reportCDR($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $credentials){
    global $arrPermission;
    $error='';
    $pCDR    = new paloSantoCDR($pDB);
    $pORGZ = new paloSantoOrganization($arrConf['elastix_dsn']["elastix"]);
    $pPBX= new paloAsteriskDB($arrConf['elastix_dsn']["elastix"]);
    
    if($credentials['userlevel']=='superadmin'){
        $domain=getParameter('organization');
        $domain=(empty($domain))?'all':$domain;
        
        $arrOrgz=array("all"=>_tr("all"));
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }else{
        $arrOrgz=array();
        $domain=$credentials['domain'];
    }
    
    $date_start=getParameter('date_start');
    if(!preg_match("/^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$/",$date_start)){
        $date_start=date("d M Y");
    }
    
    $date_end=getParameter('date_end');
    if(!preg_match("/^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$/",$date_end)){
        $date_end=date("d M Y");
    }
    
    $src=getParameter("src");
    if(isset($src) && $src!=''){
        $expression=$pPBX->getRegexPatternFromAsteriskPattern($src);
        if($expression===false)
            $src='';
    }
    
    $dst=getParameter("dst");
    if(isset($dst) && $dst!=''){
        $expression=$pPBX->getRegexPatternFromAsteriskPattern($dst);
        if($expression===false)
            $dst='';
    }
    
    $src_channel=getParameter("src_channel");
    $dst_channel=getParameter("dst_channel");
    
    $calltype=getParameter("calltype");
    $arrCallType=array("all"=>_tr("ALL"),'incoming'=>_tr('Incoming'),'outgoing'=>_tr("Outgoing"));
    $calltype=(array_key_exists($calltype,$arrCallType))?$calltype:"all";
    
    $status=getParameter("status");
    $arrStatus=array("all"=>_tr("ALL"),'ANSWERED'=>_tr('ANSWERED'),'BUSY'=>_tr("BUSY"),'FAILED'=>_tr("FAILED"),"NO ANSWER "  => _tr("NO ANSWER"));
    $status=(array_key_exists($status,$arrStatus))?$status:"all";
    
    $accountcode=getParameter('accountcode');
    
    $url['menu'] = $module_name;
    $url['organization'] = $paramFiltro['organization'] = $domain;
    $url['date_start'] = $paramFiltro['date_start'] =$date_start;
    $url['date_end'] = $paramFiltro['date_end'] =$date_end;
    $url['src'] = $paramFiltro['src'] =$src;
    $url['dst'] = $paramFiltro['dst'] =$dst;
    $url['src_channel'] = $paramFiltro['src_channel'] =$src_channel;
    $url['dst_channel'] = $paramFiltro['dst_channel'] =$dst_channel;
    $url['calltype'] = $paramFiltro['calltype'] =$calltype;
    $url['status'] = $paramFiltro['status'] =$status;
    $url['accountcode'] = $paramFiltro['accountcode'] =$accountcode;
    
    //permission
    $delete=in_array("delete",$arrPermission);
    $export=in_array("export",$arrPermission);
    
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr("CDR Report"));
    $oGrid->pagingShow(true);
    if($export){
        $oGrid->enableExport();   // enable export.
        $oGrid->setNameFile_Export(_tr("CDRReport"));
    }
    $oGrid->setURL($url);
    if($delete && !$oGrid->isExportAction()){
        $arrColumns[]="<input type='checkbox' name='cdrcheckall' class='cdrcheckall' id='cdrcheckall' onclick='jqCheckAll(this.id)';>";
    }
    if($credentials['userlevel']=='superadmin'){
        $arrColumns[]=_tr("Organization");
    }
    $arrColumns[]=_tr("Date");
    $arrColumns[]=_tr("Source");
    if($credentials['userlevel']!='superadmin')
        $arrColumns[]=_tr("Ring Group");
    $arrColumns[]=_tr("Destination");
    $arrColumns[]=_tr("Src. Channel");
    $arrColumns[]=_tr("Account Code");
    $arrColumns[]=_tr("Dst. Channel");
    $arrColumns[]=_tr("Call Direction");
    $arrColumns[]=_tr("Status");
    $arrColumns[]=_tr("Duration");
    $oGrid->setColumns($arrColumns);
    
    //get NumCDR
    $total=$pCDR->getNumCDR($paramFiltro);
    if($total===false){
        $total=0;
        $error=_tr("An error has ocurred to retrieve CDR data")." "."DATABASE ERROR";
    }
    
    $arrData=array();
    $arrResult = array();
    
    if($total!=0){
        if($oGrid->isExportAction()){
            if(!$export){
                $arrData=_tr('INVALID ACTION');
            }else
                $arrResult = $pCDR->listarCDRs($paramFiltro);
        }else{
            $limit  = 20;
            $oGrid->setLimit($limit);
            $oGrid->setTotal($total);
            $offset = $oGrid->calculateOffset();
            $arrResult = $pCDR->listarCDRs($paramFiltro,$limit,$offset);
        }
    }
    
    if($arrResult===false){
        $error=_tr('CDR data could not be retrieved.')." "."DATABASE ERROR";
    }else{
        foreach($arrResult as  $value){
            $arrTmp=array();
            if($delete && !$oGrid->isExportAction()){
                $arrTmp[] = "<input type='checkbox' name='crdDel[]' class='cdrdelete' value='$value[6]' />";
            }
            if($credentials['userlevel']=="superadmin")
                $arrTmp[] = (isset($arrOrgz[$value[11]]))?$arrOrgz[$value[11]]:'';
            $arrTmp[] = $value[0]; //calldate
            $arrTmp[] = $value[1]; //src
            if($credentials['userlevel']!="superadmin")
                $arrTmp[] = $value[10]; //rg_name
            $arrTmp[] = $value[2]; //dst
            $arrTmp[] = $value[3]; //channel
            $arrTmp[] = $value[9]; //accountcode
            $arrTmp[] = $value[4]; //dst_channel
            if($value[12]=="1" || $value[13]=="1"){ //call_type
                $arrTmp[] = ($value[12]=="1")?"outgoing":"incoming";
            }else
                $arrTmp[] = "";
            $arrTmp[] = $value[5]; //disposition
            $iDuracion = $value[8]; //billsec
            $iSec = $iDuracion % 60; $iDuracion = (int)(($iDuracion - $iSec) / 60);
            $iMin = $iDuracion % 60; $iDuracion = (int)(($iDuracion - $iMin) / 60);
            $sTiempo = "{$value[8]}s";
            if ($value[8] >= 60) {
                if ($iDuracion > 0) $sTiempo .= " ({$iDuracion}h {$iMin}m {$iSec}s)";
                elseif ($iMin > 0)  $sTiempo .= " ({$iMin}m {$iSec}s)";
            }
            $arrTmp[]=$sTiempo;
            $arrData[] = $arrTmp;
        }
    }
    
    $oGrid->setData($arrData);

    if($error!=""){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",$error);
    }
    
    //begin section filter
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='report'>");
    if($delete){
        $oGrid->deleteList(_tr("Are you sure you want to delete?"), "delete", _tr("Delete Selected"),false);
    }
    
    if($credentials['userlevel']=='superadmin'){
        $_POST["organization"]=$domain;
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true);
    }
    
    $_POST['date_start']=$paramFiltro['date_start'];
    $_POST['date_end']=$paramFiltro['date_end'];
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Start Date")." = ".$paramFiltro['date_start'].", "._tr("End Date")." = ".$paramFiltro['date_end'], $paramFiltro,  array('date_start' => date("d M Y"),'date_end' => date("d M Y")),true);//DATE START - DATE END
    
    $_POST["src"]=$src; // source 
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Source")." = ".$src, $_POST, array("src" => ""));
    
    $_POST["dst"]=$dst; // destination 
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Destination")." = ".$dst, $_POST, array("dst" => ""));
    
    $_POST["src_channel"]=$src_channel; // source channel
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Source Channel")." = ".$src_channel, $_POST, array("src_channel" => ""));
    
    $_POST["dst_channel"]=$dst_channel; // destination channel
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Destination Channel")." = ".$dst_channel, $_POST, array("dst_channel" => ""));
    
    $_POST["calltype"]=$calltype; // call type 
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Call Destination")." = ".$arrCallType[$calltype], $_POST, array("calltype" => "all"),true);
    
    $_POST["status"]=$status; // call status
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Call Status")." = ".$arrStatus[$status], $_POST, array("status" => "all"),true);
     
    $_POST["accountcode"]=$accountcode; // destination channel
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Account Code")." = ".$dst, $_POST, array("accountcode" => ""));
    
    $arrForm = createFieldFilter($arrOrgz,$arrCallType,$arrStatus);
    $oFilterForm = new paloForm($smarty, $arrForm);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);    
    $oGrid->showFilter(trim($htmlFilter));
    
    $content = $oGrid->fetchGrid();
    return $content;
}

function deleteCDR($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $credentials){
    //solo el superadmin tiene permito borrar resgistros del CDR
    if($credentials['userlevel']!='superadmin'){
        $smarty->assign("mb_title",_tr('Error'));
        $smarty->assign("mb_message",_tr("You are not authorized to perform this action"));
        return reportCDR($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $credentials);
    }
    
    $arrCDR=getParameter("crdDel");
    
    if(!is_array($arrCDR) || count($arrCDR)==0){
        $smarty->assign("mb_title",_tr('Error'));
        $smarty->assign("mb_message",_tr("You must select at least one record"));
        return reportCDR($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $credentials);
    }
    
    $pCDR = new paloSantoCDR($pDB);
    $pDB->beginTransaction();
    if($pCDR->borrarCDRs($arrCDR)){
        $pDB->commit();
        $smarty->assign("mb_title",_tr('Message'));
        $smarty->assign("mb_message",_tr("CDR record(s) were deleted successfully"));
    }else{
        $pDB->rollBack();
        $smarty->assign("mb_title",_tr('Error'));
        $smarty->assign("mb_message",$pCDR->errMsg);
    }
    return reportCDR($smarty, $module_name, $local_templates_dir, $pDB,$arrConf, $credentials);
}


function createFieldFilter($arrOrgz,$arrCallType,$arrStatus){

    $arrFormElements = array(
        "organization"  => array("LABEL"         => _tr("Organization"),
                            "DESCRIPTION"            => _tr("CDR_organization"),  
                                "REQUIRED"               => "no",
                                "INPUT_TYPE"             => "SELECT",
                                "INPUT_EXTRA_PARAM"      => $arrOrgz,
                                "VALIDATION_TYPE"        => "domain",
                                "VALIDATION_EXTRA_PARAM" => ""),
        "date_start"  => array("LABEL"                  => _tr("Start Date"),
                            "DESCRIPTION"            => _tr("CDR_datestart"),       
                            "REQUIRED"               => "yes",
                            "INPUT_TYPE"             => "DATE",
                            "INPUT_EXTRA_PARAM"      => "",
                            "VALIDATION_TYPE"        => "ereg",
                            "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
        "date_end"    => array("LABEL"                  => _tr("End Date"),
                            "DESCRIPTION"            => _tr("CDR_dateend"),       
                            "REQUIRED"               => "yes",
                            "INPUT_TYPE"             => "DATE",
                            "INPUT_EXTRA_PARAM"      => "",
                            "VALIDATION_TYPE"        => "ereg",
                            "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
        "status"  => array("LABEL"                  => _tr("Status"),
                            "DESCRIPTION"            => _tr("CDR_status"),
                            "REQUIRED"               => "no",
                            "INPUT_TYPE"             => "SELECT",
                            "INPUT_EXTRA_PARAM"      => $arrStatus,
                            "VALIDATION_TYPE"        => "text",
                            "VALIDATION_EXTRA_PARAM" => ""),
        "calltype"  => array("LABEL"                  => _tr("Call Type"),
                            "DESCRIPTION"            => _tr("CDR_calltype"),
                            "REQUIRED"               => "no",
                            "INPUT_TYPE"             => "SELECT",
                            "INPUT_EXTRA_PARAM"      => $arrCallType ,
                            "VALIDATION_TYPE"        => "text",
                            "VALIDATION_EXTRA_PARAM" => ""),
        "src"  => array("LABEL"                  => _tr("Source"),
                            "DESCRIPTION"            => _tr("CDR_origin"),
                            "REQUIRED"               => "no",
                            "INPUT_TYPE"             => "TEXT",
                            "INPUT_EXTRA_PARAM"      => '' ,
                            "VALIDATION_TYPE"        => "text",
                            "VALIDATION_EXTRA_PARAM" => ""),
        "src_channel"  => array("LABEL"                  => _tr("Source Channel"),
                            "DESCRIPTION"            => _tr("CDR_sourcechannel"),
                            "REQUIRED"               => "no",
                            "INPUT_TYPE"             => "TEXT",
                            "INPUT_EXTRA_PARAM"      => '' ,
                            "VALIDATION_TYPE"        => "text",
                            "VALIDATION_EXTRA_PARAM" => ""),
        "dst"  => array("LABEL"                  => _tr("Destination"),
                            "DESCRIPTION"            => _tr("CDR_destine"),
                            "REQUIRED"               => "no",
                            "INPUT_TYPE"             => "TEXT",
                            "INPUT_EXTRA_PARAM"      => '' ,
                            "VALIDATION_TYPE"        => "text",
                            "VALIDATION_EXTRA_PARAM" => ""),
        "dst_channel"  => array("LABEL"              => _tr("Destination Channel"),
                            "DESCRIPTION"            => _tr("CDR_destinationchannel"),
                            "REQUIRED"               => "no",
                            "INPUT_TYPE"             => "TEXT",
                            "INPUT_EXTRA_PARAM"      => '' ,
                            "VALIDATION_TYPE"        => "text",
                            "VALIDATION_EXTRA_PARAM" => ""),
        "accountcode"  => array("LABEL"                  => _tr("Account Code"),
                            "DESCRIPTION"            => _tr("CDR_accountcode"),
                            "REQUIRED"               => "no",
                            "INPUT_TYPE"             => "TEXT",
                            "INPUT_EXTRA_PARAM"      => '' ,
                            "VALIDATION_TYPE"        => "text",
                            "VALIDATION_EXTRA_PARAM" => ""),
        
        );
        
    return $arrFormElements;
}

function getAction(){
    global $arrPermission;
    if(getParameter('delete')){
        return (in_array('delete',$arrPermission))?'delete':'report';
    }else
        return "report"; //cancel
}
?>
