<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-18                                               |
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
  $Id: index.php,v 1.3 2007/09/05 00:26:21 gcarrillo Exp $
  $Id: index.php,v 1.3 2008/04/14 09:22:21 afigueroa Exp $
  $Id: index.php,v 2.0 2010/02/03 09:00:00 onavarre Exp $
  $Id: index.php,v 2.1 2010-03-22 05:03:48 Eduardo Cueva ecueva@palosanto.com Exp $ 
  $Id: index.php,v 3.1 2013-09-13 05:03:48 Rocio Mera rmera@palosanto.com Exp $ */

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
    $dsn = generarDSNSistema('asteriskuser', 'asteriskcdrdb');
    $pDB = new paloDB($dsn);

    //user credentials
    global $arrCredentials;
            
    $action = getAction();
    $content = "";

    switch($action){
        case 'delete':
            $content = deleteRecord($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case 'download':
            $content = downloadFile($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        case "display_record":
            $content = display_record($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default:
            $content = reportMonitoring($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
    }
    return $content;
}

function reportMonitoring($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error='';
    $pMonitoring = new paloSantoMonitoring($pDB);
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
    
    $arrType=array(""=>"","conference"=>_tr("Conference"),"group"=>_tr("Group"),"queue"=>_tr("Queue"),'incoming'=>_tr('Incoming'),'outgoing'=>_tr("Outgoing"));
    $type=getParameter("type");
    $type=(array_key_exists($type,$arrType))?$type:"";
    
    $source=getParameter("source");
    if(isset($source) && $source!=''){
        $expression=$pPBX->getRegexPatternFromAsteriskPattern($source);
        if($expression===false)
            $source='';
    }
    
    $destination=getParameter("destination");
    if(isset($destination) && $destination!=''){
        $expression=$pPBX->getRegexPatternFromAsteriskPattern($destination);
        if($expression===false)
            $destination='';
    }
    
    $url['menu']=$module_name;
    $url['organization']=$arrProp['organization']=$domain;
    $url['date_start']=$arrProp['date_start']=$date_start;
    $url['date_end']=$arrProp['date_end']=$date_end;
    $url['source']=$arrProp['source']=$source;
    $url['destination']=$arrProp['destination']=$destination;
    $url['type']=$arrProp['type']=$type;
    
    //permission
    $delete=in_array("delete",$arrPermission);
    $export=in_array("export",$arrPermission);
    
    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr("Monitoring"));
    $oGrid->setIcon("web/apps/$module_name/images/pbx_monitoring.png");
    $oGrid->pagingShow(true); // show paging section.
    if($export)
        $oGrid->enableExport();   // enable export.
    $oGrid->setNameFile_Export(_tr("Monitoring"));
    $oGrid->setURL($url);
    
    if($delete && !$oGrid->isExportAction()){
        $arrColumns[]="";
    }
    if($credentials['userlevel']=='superadmin'){
        $arrColumns[]=_tr('organization');
    }
    $arrColumns[]=_tr("Date");
    $arrColumns[]=_tr("Time");
    $arrColumns[]=_tr("Source");
    $arrColumns[]=_tr("Destination");
    $arrColumns[]=_tr("Duration");
    $arrColumns[]=_tr("Type");
    $arrColumns[]=_tr("File");
    if(!$oGrid->isExportAction()){
        $arrColumns[]=""; //to display audio
    }
    $oGrid->setColumns($arrColumns);
    
    $totalMonitoring = $pMonitoring->getNumMonitoring($arrProp);
    if($totalMonitoring===false){
        $error=_tr('Recordings could not be retrieved.')." "."DATABASE ERROR";
        $totalMonitoring=0;
    }
    
    $arrData = array();
    $arrResult = array();
    
    if($totalMonitoring!=0){
        if($oGrid->isExportAction()){
            $arrResult =$pMonitoring->getMonitoring($arrProp);
        }else{
            $limit  = 20;
            $total  = $totalMonitoring;
            $oGrid->setLimit($limit);
            $oGrid->setTotal($total);
            $offset = $oGrid->calculateOffset();
            $arrProp['limit']=$limit;
            $arrProp['offset']=$offset;
            $arrResult =$pMonitoring->getMonitoring($arrProp);
        }
    }
    if($arrResult===false){
        $error=_tr('Recordings could not be retrieved.')." "."DATABASE ERROR";
    }else{
        if($oGrid->isExportAction()){
            if(!$export){
                $arrData=_tr('INVALID ACTION');
            }
            foreach($arrResult as $monitoring){
                $arrTmp=array();
                if($credentials['userlevel']=='superadmin'){
                    $arrTmp[] = (isset($arrOrgz[$monitoring['organization_domain']]))?$arrOrgz[$monitoring['organization_domain']]:'';
                }
                $arrTmp[] = date('d M Y',strtotime($monitoring['calldate'])); //date
                $arrTmp[] = date('H:i:s',strtotime($monitoring['calldate'])); //time
                $arrTmp[] = $monitoring['src']; //source
                $arrTmp[] = $monitoring['dst']; //destination
                $arrTmp[] = SecToHHMMSS($monitoring['duration']); //duration
                $namefile = basename($monitoring['userfield']);
                $namefile = str_replace("audio:","",$namefile);
                if($monitoring['toout']=='1'){
                    $arrTmp[]=$arrType['outgoing'];
                }elseif($monitoring['fromout']=='1'){
                    $arrTmp[]=$arrType['incoming'];
                }else{
                    if($namefile[0]=='g'){
                        $arrTmp[]=$arrType['group'];
                    }elseif($namefile[0]=='q'){
                        $arrTmp[]=$arrType['queue'];
                    }elseif(strpos($namefile,"meetme-conf")!==false){
                        $arrTmp[]=$arrType['conference'];
                    }else{
                        $arrTmp[] = "";
                    }
                }
                $arrTmp[]=$namefile;
                $arrData[]=$arrTmp;
            }
        }else{
            $i=0;
            foreach($arrResult as $monitoring){
                $arrTmp=array();
                if($delete){
                    $arrTmp[] = "<input type='checkbox' name='recordDel[]' value='{$monitoring['uniqueid']}' />";
                }
                if($credentials['userlevel']=='superadmin'){
                    $arrTmp[] = (isset($arrOrgz[$monitoring['organization_domain']]))?$arrOrgz[$monitoring['organization_domain']]:'';
                }
                $arrTmp[] = date('d M Y',strtotime($monitoring['calldate'])); //date
                $arrTmp[] = date('H:i:s',strtotime($monitoring['calldate'])); //time
                $arrTmp[] = $monitoring['src']; //source
                $arrTmp[] = $monitoring['dst']; //destination
                $arrTmp[] = SecToHHMMSS($monitoring['duration']); //duration
                $namefile = basename($monitoring['userfield']);
                $namefile = str_replace("audio:","",$namefile);
                if($monitoring['toout']=='1'){
                    $arrTmp[]=$arrType['outgoing'];
                }elseif($monitoring['fromout']=='1'){
                    $arrTmp[]=$arrType['incoming'];
                }else{
                    if($namefile[0]=='g'){
                        $arrTmp[]=$arrType['group'];
                    }elseif($namefile[0]=='q'){
                        $arrTmp[]=$arrType['queue'];
                    }elseif(strpos($namefile,"meetme-conf")!==false){
                        $arrTmp[]=$arrType['conference'];
                    }else{
                        $arrTmp[] = "";
                    }
                }
                if($namefile=='deleted'){
                    $arrTmp[] = $namefile;
                    $arrTmp[]= "";
                }else{
                    $explod_name=explode(".",$namefile);
                    $ext=array_pop($explod_name);
                    if($ext=='gsm' || $ext=='WAV'){
                        $div_display = "<a href=\"javascript:popUp('index.php?menu=$module_name&action=display_record&id={$monitoring['uniqueid']}&rawmode=yes',350,100)\"><img style='cursor:pointer;' width='13px' src='web/apps/recordings/images/sound.png'/></a>  ";
                    }else{
                        $div_display = "<div class='single' id='$i' style='display:inline;'><span data-src='index.php?menu=$module_name&action=download&id={$monitoring['uniqueid']}&rawmode=yes'><img style='cursor:pointer;' width='13px' src='web/apps/recordings/images/sound.png'/>&nbsp;&nbsp;</span></div>";
                    }
                    $download = "<a href='index.php?menu=$module_name&action=download&id={$monitoring['uniqueid']}&rawmode=yes'>".$namefile."</a>";
                    $arrTmp[] = $div_display.$download;
                    $arrTmp[]="<audio></audio>";
                }
                $i++;
                $arrData[]=$arrTmp;
            }
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
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => _tr("all")),true);
    }
    
    $_POST['date_start']=$arrProp['date_start'];
    $_POST['date_end']=$arrProp['date_end'];
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Start Date")." = ".$arrProp['date_start'].", "._tr("End Date")." = ".$arrProp['date_end'], $arrProp,  array('date_start' => date("d M Y"),'date_end' => date("d M Y")),true);//DATE START - DATE END
    $_POST["type"]=$type; // type 
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Type")." = ".$arrType[$type], $_POST, array("type" => ""));
    $_POST["source"]=$source; // source 
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Source")." = ".$source, $_POST, array("source" => ""));
    $_POST["destination"]=$destination; // destination 
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Destination")." = ".$source, $_POST, array("destination" => "")); 
    
    $arrForm = createFieldFilter($arrOrgz,$arrType);
    $oFilterForm = new paloForm($smarty, $arrForm);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);    
    $oGrid->showFilter(trim($htmlFilter));
    //end section filter
    
    $content = $oGrid->fetchGrid();
    return $content;
}

function downloadFile($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{     
    $domain=null;
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials["domain"];
    }
    
    $fullPath=NULL;
    $uniqueid = getParameter("id");
    $pMonitoring = new paloSantoMonitoring($pDB);
    $record = $pMonitoring->getMonitoringById($uniqueid,$domain);
    if ($record) {
        $fullPath = $record['userfield'];
    }
    
    //replace audio:
    $fullPath = str_replace("audio:","",$fullPath);
    
    // Must be fresh start 
    if(headers_sent()) 
        die('Headers Sent'); 

    // File Exists? 
    if(file_exists($fullPath)){ 
            
        //get mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $ctype = finfo_file($finfo, $fullPath);
        finfo_close($finfo);
        
        if($ctype==false){
            $ctype="application/force-download";
        }
        
        // Parse Info / Get Extension 
        $fsize = filesize($fullPath); 
   
        header("Pragma: public"); // required 
        header("Expires: 0"); 
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
        header("Cache-Control: private",false); //required for certain browsers 
        header("Content-Type: $ctype"); 
       
        header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" ); 
        header("Content-Transfer-Encoding: binary"); 
        header("Content-Length: ".$fsize); 
            
        if ($fileh = fopen($fullPath, 'rb')) {
            while(!feof($fileh) and (connection_status()==0)) {
                print(fread($fileh, 1024*12));//10kb de buffer stream
                flush();
            }
            
            fclose($fileh);
        }else die('File Not Found'); 
            
        return((connection_status()==0) and !connection_aborted());

    } else die('File Not Found'); 
          
} 

function display_record($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials){

    $domain=null;
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials["domain"];
    }
    
    $fullPath=NULL;
    $uniqueid = getParameter("id");
    $pMonitoring = new paloSantoMonitoring($pDB);
    $record = $pMonitoring->getMonitoringById($uniqueid,$domain);
    if ($record) {
        $fullPath = $record['userfield'];
    }
    
    //replace audio:
    $fullPath = str_replace("audio:","",$fullPath);
    
    if(file_exists($fullPath)){ 
            
        //get mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $ctype = finfo_file($finfo, $fullPath);
        finfo_close($finfo);
        
        if($ctype==false){
            $ctype="application/force-download";
        }
        
        $session_id = session_id(); //si no se seta este parametro no se reproduce la grabacion
        $sContenido=<<<contenido
<html>
<head><title>Elastix</title></head>
<body>
    <embed src='index.php?menu=$module_name&action=download&id={$uniqueid}&rawmode=yes&elastixSession=$session_id' width='300', height='20' autoplay='true' loop='false' type="$ctype"></embed><br>
</body>
</html>
contenido;
        echo $sContenido;
    }else{
        die('File Not Found'); 
    }
}

function deleteRecord($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials){
    $error = "";
    $success = false;
         
    $domain=null;
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials["domain"];
    }

    $record=getParameter("recordDel");
    
    if (isset($record)&& count($record)>0) {
        $pMonitoring = new paloSantoMonitoring($pDB);
        
        $success = $pMonitoring->deleteRecordings($record,$domain);
        $error = $pMonitoring->errMsg;
        if($success){
            $smarty->assign("mb_title", _tr("MESSAGE"));
            $smarty->assign("mb_message",_tr("The Recordings were deleted successfully"));
        } else {
            $smarty->assign("mb_title", _tr("MESSAGE"));
            $smarty->assign("mb_message","Some file could not be deleted.<br>".$error);
        }
    } else {
        $smarty->assign("mb_title", _tr("ERROR"));
        $smarty->assign("mb_message",_tr("You must select at least one record"));
    }
    return reportMonitoring($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function SecToHHMMSS($sec)
{
    $HH = 0;$MM = 0;$SS = 0;
    $segundos = $sec;

    if( $segundos/3600 >= 1 ){ $HH = (int)($segundos/3600);$segundos = $segundos%3600;} if($HH < 10) $HH = "0$HH";
    if(  $segundos/60 >= 1  ){ $MM = (int)($segundos/60);  $segundos = $segundos%60;  } if($MM < 10) $MM = "0$MM";
    $SS = $segundos; if($SS < 10) $SS = "0$SS";

    return "$HH:$MM:$SS";
}

function createFieldFilter($arrOrgz,$arrType){
    $arrFormElements = array(
            "date_start"  => array(           "LABEL"                  => _tr("Start_Date"),
                                              "REQUIRED"               => "yes",
                                              "INPUT_TYPE"             => "DATE",
                                              "INPUT_EXTRA_PARAM"      => "",
                                              "VALIDATION_TYPE"        => "ereg",
                                              "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
            "date_end"    => array(           "LABEL"                  => _tr("End_Date"),
                                              "REQUIRED"               => "yes",
                                              "INPUT_TYPE"             => "DATE",
                                              "INPUT_EXTRA_PARAM"      => "",
                                              "VALIDATION_TYPE"        => "ereg",
                                              "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
            "organization"  => array("LABEL"         => _tr("Organization"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOrgz,
                                            "VALIDATION_TYPE"        => "domain",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "source"  => array("LABEL"           => _tr("Source"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "destination" => array("LABEL"           => _tr("Destination"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "type"  => array("LABEL"         => _tr("Type"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrType,
                                            "VALIDATION_TYPE"        => "domain",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                    );
    return $arrFormElements;
}


function getAction()
{
    global $arrPermission;
    if(getParameter("action")=="display_record")
        return "display_record";
    else if(getParameter("delete"))
        return (in_array("delete",$arrPermission))?"delete":'report';
    else if(getParameter("action")=="download")
        return "download";
    else
        return "report"; //cancel
}
?>
