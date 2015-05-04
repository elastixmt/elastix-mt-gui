<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.1-4                                                |
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
  $Id: default.conf.php,v 1.1 2008-06-12 09:06:35 afigueroa Exp $ */
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
        
    $dsn_agi_manager=getDNSAGIManager();
    
    $action = getAction();
    $content = "";
    
    switch($action){
        case "add":
            $content = form_Recordings($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrCredentials);
            break;
        case "record":
            $content = record($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $arrCredentials);
            break;
        case "hangup":
            $content = hangup($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $arrCredentials);
            break;
        case "save":
            $content = save_recording($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrCredentials);
            break;
        case "remove":
            $content = remove_recording($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrCredentials);
            break;
        case "check_call_status":
            $content = checkCallStatus("call_status", $smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $arrCredentials);
            break;
        case "checkName":
            $content = check_name($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $arrCredentials);
            break;
        case "download":
            $content = downloadFile($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $arrCredentials);
            break;
        default:
            $content = reportRecording($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrCredentials);
            break;
    }

    return $content;
}

function getDNSAGIManager(){
    $pConfig = new paloConfig("/var/www/elastixdir/asteriskconf", "/elastix_pbx.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);
    
    $dsn_agi_manager['password'] = $arrConfig['MGPASSWORD']['valor'];
    $dsn_agi_manager['user'] = $arrConfig['MGUSER']['valor'];
    $dsn_agi_manager['host']=$arrConfig['DBHOST']['valor'];
    return $dsn_agi_manager;
}

function reportRecording($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    global $arrPermission;
    $error = "";
    $pORGZ = new paloSantoOrganization($pDB);
        
    $name=getParameter("name");
    if($credentials['userlevel']=='superadmin'){
        $domain=getParameter('organization');
        $domain=empty($domain)?'all':$domain;
        
        $arrOrgz=array("all"=>_tr("all"));
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
    }else{
        $arrOrgz=array();
        $domain=$credentials['domain'];
    }
    $url['menu']=$module_name;
    $url['organization']=$domain;
    $url['name']=$name;
            
    $pRecording = new paloSantoRecordings($pDB);
    $total=$pRecording->getNumRecording($domain,$name);
    
    if ($total===false) {
        $error=$pRecording->errMsg;
        $total=0;
    }
    $limit=20;

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;

    //permission
    $create=in_array("create",$arrPermission);
    $edit=in_array("edit",$arrPermission);
    $delete=in_array("delete",$arrPermission);
    
    if($delete)
        $check = "&nbsp;<input type='checkbox' name='checkall' class='checkall' id='checkall' onclick='jqCheckAll(this.id);' />";
    else
        $check = "";
    $oGrid->setTitle(_tr('Recordings List'));
    //$oGrid->setIcon('url de la imagen');
    $oGrid->setWidth("99%");
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    $oGrid->setTotal($total);
    $oGrid->setURL($url);
    
    $arrColumns[]=$check;
    if($credentials['userlevel']=='superadmin')
        $arrColumns[]=_tr("Organization");
    $arrColumns[]=_tr("Name");
    //$arrColumns[]=_tr("Source");
    $arrColumns[]=_tr("");
    $oGrid->setColumns($arrColumns);

    $arrRecordings=array();
    $arrData = array();
    
    if($total!=0){
        $arrRecordings = $pRecording->getRecordings($domain,$name,$limit,$offset);
    }
                   
    if($arrRecordings===false){
            $error=_tr("Error to obtain Recordings").$pRecording->errMsg;
        $arrRecordings=array();
    }
    $i=0;
       
    foreach($arrRecordings as $recording) {
        $arrTmp=array();
        $ext = explode(".",$recording["name"]);
        if($delete){
            $arrTmp[] = "&nbsp;<input type ='checkbox' class='delete' name='record_delete[]' value='".$recording['uniqueid']."' />";
        }
        if($credentials['userlevel']=='superadmin'){
            $arrTmp[] = ($recording["organization_domain"]=='')?'':$arrOrgz[$recording["organization_domain"]];
        }
        //$arrTmp[] = $recording["source"];
        $idfile = $recording['uniqueid'];
        if($ext[1]=="gsm"){
            $div_display = '';
        }else{
            $div_display = "<div class='single' id='$i' style='display:inline;'><span data-src='index.php?menu=$module_name&action=download&id=$idfile&rawmode=yes'><img style='cursor:pointer;' width='13px' src='web/apps/recordings/images/sound.png'/>&nbsp;&nbsp;</span></div>";
        }
        $download = "<a href='index.php?menu=$module_name&action=download&id=$idfile&rawmode=yes'>".$recording['name']."</a>";
        $arrTmp[] = $div_display.$download;
        $arrTmp[] = "<audio></audio>";
        $i++;
        $arrData[] = $arrTmp;
    }
    
    $smarty->assign("USERLEVEL",$credentials['userlevel']);
    $smarty->assign("SEARCH","<input type='submit' class='button' value='"._tr('Search')."' name='report'>");
    if($create){
        $oGrid->addNew("add_recording",_tr("Add Recording"));
    }
    if($delete){
        $oGrid->deleteList(_tr("Are you sure you want to delete?"), "remove", _tr("Delete Selected"),false);
    }

    if($error!=""){
        $smarty->assign("mb_title", _tr("MESSAGE"));
        $smarty->assign("mb_message",$error);
    }

    if($credentials['userlevel']=='superadmin'){
        $_POST["organization"]=$domain;
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$domain], $_POST, array("organization" => "all"),true);
    }
    $_POST["name"]=$name; // name 
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Name")." = ".$name, $_POST, array("name" => "")); 
    $arrFormElements = createFieldFilter($arrOrgz);
    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
    $oGrid->showFilter(trim($htmlFilter));
    
    $contenidoModulo = $oGrid->fetchGrid(array(), $arrData);
    return $contenidoModulo;
}

function downloadFile($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $credentials)
{     
    $domain=null;
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials["domain"];
    }
    
    $fullPath=NULL;
    $id = getParameter("id");
    $pRecording = new paloSantoRecordings($pDB);
    $record = $pRecording->getRecordingById($id,$domain);
    if ($record) {
        $fullPath = $record['filename'];
        $name = $record['name'];
    }
    
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

        /*$path_parts = pathinfo($fullPath); 
        $ext = strtolower($path_parts["extension"]); 
        
        // Determine Content Type 
        switch ($ext) { 
            case "wav":   $ctype="audio/x-wav"; break;
            case "Wav":   $ctype="audio/x-wav"; break;
            case "WAV":   $ctype="audio/x-wav"; break;
            case "WAV49": $ctype="audio/x-wav"; break;
            case "gsm":   $ctype="audio/x-gsm"; break;
            case "GSM":   $ctype="audio/x-gsm"; break;
            case 'mp3':   $ctype='audio/mpeg'; break;
            default: $ctype="application/force-download"; 
        } */
   
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

function check_name($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $credentials)
{
    $domain=null;
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials["domain"];
    }else{
        $jsonObject->set_status("Error");
        $jsonObject->set_error("Invalid Action");
        return $jsonObject->createJSON();
    }
    
    $name = getParameter("recording_name");
    $pRecording = new paloSantoRecordings($pDB);
    
    if ($name!="") {
        $filename = "/var/lib/asterisk/sounds/".$domain."/system/".$name.".wav";
        $status = $pRecording->checkFilename($filename);
        $recId = $pRecording->getId($name.".wav","system",$domain);
        $id  = $recId["uniqueid"];
    } else
        $status = "empty";

    $jsonObject = new PaloSantoJSON();
    $msgResponse["name"] = $status;
    $msgResponse["id"] = $id;
    $jsonObject->set_status("OK");
    $jsonObject->set_message($msgResponse);

    return $jsonObject->createJSON();
}

function record($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $credentials)
{
    session_commit();
    $status  = TRUE;
    $status = new_recording($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $credentials);
    $jsonObject = new PaloSantoJSON();
    $msgResponse["record"] = $status;
    $jsonObject->set_status("OK");
    $jsonObject->set_message($msgResponse);

    return $jsonObject->createJSON();
}


function hangup($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $credentials)
{
    $pRecording = new paloSantoRecordings($pDB);
    $jsonObject = new PaloSantoJSON();
    
    $domain=null;
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials["domain"];
    }else{
        $jsonObject->set_status("Error");
        $jsonObject->set_error("Invalid Action");
        return $jsonObject->createJSON();
    }
  
    //obtenemos la extension del usuario que esta haciendo la grabacion
    $extension = $pRecording->Obtain_Extension_Current_User();
    if(!$extension){
        $jsonObject->set_status("Error");
        $jsonObject->set_error("An erro has ocurred to obtain user extension");
        return $jsonObject->createJSON();
    }
    
    $pRecording = new paloSantoRecordings($pDB);
    $result = $pRecording->Obtain_Protocol_Current_User($domain,$extension);
      
    if($result != FALSE)
       $result = $pRecording->hangupPhone($dsn_agi_manager, $result['device'], $result['dial'], $result['exten']);
   
    $msgResponse["record"] = $result;
    
    $jsonObject->set_status("OK");
    $jsonObject->set_message($msgResponse);

    return $jsonObject->createJSON();
}


function remove_recording($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    $error = "";
    $success = false;
         
    $domain=null;
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials["domain"];
    }

    $record=getParameter("record_delete");
    
    if (isset($record)&& count($record)>0) {
        $pRecording = new paloSantoRecordings($pDB);
        
        $success = $pRecording->deleteRecordings($record,$domain);
        $error = $pRecording->errMsg;
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
    return reportRecording($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
}

function save_recording($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    $success= false;
    $error="";
  
    $domain=null;
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials["domain"];
    }else{
        $domain=getParameter("organization");
    }
  
    $bExito = true;
    $pRecording = new paloSantoRecordings($pDB);
    
    if(empty($domain)){
        $destiny_path = "/var/lib/asterisk/sounds/custom";
        $source = "custom";
    }else{
        //validamos el formato
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/",$domain)){
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message",_tr("Invalid organization"));
            return form_Recordings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }else{
            $destiny_path = "/var/lib/asterisk/sounds/$domain/system";
            $source = "system";
        }
    }
    
    if (isset($_FILES)) {   
        if($_FILES['file_record']['name']!="") {
            $smarty->assign("file_record_name", $_FILES['file_record']['name']);
            if(!file_exists($destiny_path))
            {
                $bExito = mkdir($destiny_path, 0755, TRUE);
            }
            if((!preg_match("/^(\w|-|\.|\(|\)|\s)+\.(wav|WAV|Wav|gsm|GSM|Gsm|Wav49|wav49|WAV49|mp3|MP3|Mp3)$/",$_FILES['file_record']['name']))||(preg_match("/(\.php)/",$_FILES['file_record']['name']))){
                $smarty->assign("mb_title", _tr("ERROR"));
                $smarty->assign("mb_message",_tr("Invalid extension file ")." ".$_FILES["file_record"]["name"]);
                $bExito = false;
                return form_Recordings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
            }
            if($bExito){
                $filenameTmp = $_FILES['file_record']['name'];
                $tmp_name = $_FILES['file_record']['tmp_name'];
                $filename = basename("$destiny_path/$filenameTmp");
                $info=pathinfo($filename);
                $file_sin_ext=$info["filename"];
                if (strlen($filenameTmp)>50) {
                    $smarty->assign("mb_title", _tr("ERROR"));
                    $smarty->assign("mb_message",_tr("Filename's length must be max 50 characters").": $filenameTmp");
                    $bExito = false;
                } elseif(($pRecording->checkFilename($destiny_path."/".$filenameTmp)!=true)||($pRecording->checkFilename($destiny_path."/".$file_sin_ext.".wav")!=true)) {
                    //Verificar que no existe otro archivo con el mismo nombre en la misma carpeta
                    $smarty->assign("mb_title", _tr("ERROR"));
                    $smarty->assign("mb_message",_tr("Already exists a file with same filename").": $filenameTmp");
                    $bExito = false;
                    return form_Recordings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
                } else {
                    // $filename = basename("$destiny_path/$filenameTmp");
                    $date=date("YMd_His");
                    $tmpFile=$date."_".$filename;
                    if (move_uploaded_file($tmp_name, "$destiny_path/$tmpFile")) {
                        $info=pathinfo($filename);
                        $file_sin_ext=$info["filename"];
                        $type=$pRecording->getTipeOfFile("$destiny_path/$tmpFile");
                        $continue=true;
                        if($type==false){
                            $error .=$pRecording->errMsg;
                            $continue=false;
                        }
                        if($type=="audio/mpeg; charset=binary") {
                            if($pRecording->convertMP3toWAV($destiny_path,$tmpFile,$file_sin_ext,$date)==false){
                                $error .=$pRecording->errMsg;
                                $continue=false;
                                $bExito = false;
                            }else{
                                $filename=$file_sin_ext.".wav";
                            }
                        }
                        if($continue){
                            if($pRecording->resampleMoHFiles($destiny_path,$tmpFile,$filename)==false){
                                $error .=$pRecording->errMsg;
                                $bExito = false;
                            }
                        }
                    } else {
                        $smarty->assign("mb_title",_tr("ERROR").":");
                        $smarty->assign("mb_message", _tr("Possible file upload attack")." $filename");
                        return form_Recordings($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials);
                    }
                }
            } else {
                $smarty->assign("mb_title", _tr("ERROR").":");
                $smarty->assign("mb_message", _tr("Destiny directory couldn't be created"));
                return form_Recordings($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials);
            }
        }else{
            $smarty->assign("mb_title", _tr("ERROR").":");
            $smarty->assign("mb_message", _tr("Error copying the file"));
            return form_Recordings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
        }
    }else{
        $smarty->assign("mb_title",  _tr("ERROR").":");
        $smarty->assign("mb_message", _tr("Error copying the file"));
        return form_Recordings($smarty, $module_name, $local_templates_dir,$pDB, $arrConf, $credentials);
    }
 
    if($bExito) {
        $name = "$destiny_path/$filename";
        $pDB->beginTransaction();
        $success=$pRecording->createNewRecording($filename,$name,$source,$domain);

        if($success)
            $pDB->commit();
        else
            $pDB->rollBack();
        $error .=$pRecording->errMsg;

        if($success==false){
            $smarty->assign("mb_title", _tr("ERROR").":");
            $smarty->assign("mb_message",  $error);   
        } else {
            $smarty->assign("mb_title", _tr("MESSAGE"));
            $smarty->assign("mb_message",_tr("Record Saved Successfully"));
        }
    } else {
        $smarty->assign("mb_title", _tr("ERROR").":");
        $smarty->assign("mb_message", _tr("ERROR Uploading File.")." ".$error);   
        return form_Recordings($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $credentials);
    }

    return reportRecording($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials);
}

function new_recording($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $credentials)
{
    $domain=null;
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials["domain"];
    }else{
        //el superadmin no tiene una extesion asociada por lo que no puede realizar esta accion
        $result["msg"]=_tr("Invalid Action") ;
        $result["status"] = "error";
        return $result;
    }
   
    $recording_name = getParameter("recording_name");
    if (basename($recording_name) != $recording_name) $recording_name = '';
    if (strpbrk($recording_name, "\r\n") !== FALSE) $recording_name = '';
  
    if($recording_name != '') {
        $pRecording = new paloSantoRecordings($pDB);
        $filename = "/var/lib/asterisk/sounds/".$domain."/system/".$recording_name.".wav";
        //verifcamos que no haya otra grbacion con el mismo nombre
        //para no crear un nuevo registro en caso de qu eexista
        $checkRecordingName = $pRecording->checkFileName($filename);
        
        //obtenemos la extension del usuario que esta haciendo la grabacion
        $extension = $pRecording->Obtain_Extension_Current_User();
        if(!$extension){
            $result["msg"]=_tr("An error has ocurred to obtain user extension") ;
            $result["status"] = "error";
            return $result;
        }
        $result = $pRecording->Obtain_Protocol_Current_User($domain,$extension);
        if($result != FALSE) {
            $result = $pRecording->Call2Phone($dsn_agi_manager, $result['exten'], $result['dial'], $result['clid_name'],$recording_name,$domain);
            if($result) {
                $result["filename"] = $recording_name;
                $result["msg"] = _tr("Recording...") ;
                $result["status"] = "ok";
                if ($checkRecordingName==TRUE) {
                    $pDB->beginTransaction();
                    $name = $recording_name.".wav";
                    $success=$pRecording->createNewRecording($name,$filename,"system",$domain);
                    if ($success) {
                        $pDB->commit();
                        $name =$recording_name.".wav";
                        $recId = $pRecording->getId($name,"system",$domain);
                        $id=$recId["uniqueid"];
                        $result["id"] = $id;
                    } else {
                        $pDB->rollBack();
                        $error =$pRecording->errMsg;
                        $result["msg"]=_tr("The record couldn't be saved ") ;
                        $result["status"] = "error";
                    }
                }
            } else {
               $result["msg"]=_tr("The record couldn't be realized.")." "._tr($pRecording->errMsg) ;
               $result["status"] = "error";
            }
        } else {
            $result["msg"]=_tr("An error has ocurred to obtain user extension") ;
            $result["status"] = "error";
        }
    } else {
        $result["msg"]=_tr("Insert the Recording Name.") ;
        $result["status"] = "error";
    }

    return $result;
}

function checkCallStatus($function, $smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $credentials)
{
    $executed_time = 2; //en segundos
    $max_time_wait = 30; //en segundos
    $event_flag    = false;
    $data          = null;

    $i = 1;
    while(($i*$executed_time) <= $max_time_wait){
        $return = $function($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $credentials);
        $data   = $return['data'];
        if($return['there_was_change']){
            $event_flag = true;
            break;
        }
        $i++;
        sleep($executed_time); //cada $executed_time estoy revisando si hay algo nuevo....
    }
    $return = $function($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $credentials);
    $data   = $return['data'];
    return $data;
}

function call_status($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $dsn_agi_manager, $credentials)
{
    session_commit();
    if($credentials['userlevel']!='superadmin'){
        $domain=$credentials["domain"];
    }else{
        $jsonObject->set_status("HANGUP");
        return array("there_was_change" => true,
                 "data" => $jsonObject->createJSON());
    }
   
    $status=true;
    $pRecording = new paloSantoRecordings($pDB,$domain);
    $extension = getParameter("extension");
    $result = $pRecording->Obtain_Protocol_Current_User($domain,$extension);
        
    $state = $pRecording->callStatus($result['dial']);
    $jsonObject = new PaloSantoJSON();
    if($state=="hangup")
        $status = FALSE;
   
    if($status){
        $msgResponse["status"] = $state;
        $jsonObject->set_status("RECORDING");
        $jsonObject->set_message($msgResponse);
        return array("there_was_change" => false,
                 "data" => $jsonObject->createJSON());
    }else{
        $jsonObject->set_status("HANGUP");
        return array("there_was_change" => true,
                 "data" => $jsonObject->createJSON());
    }
   
}

function form_Recordings($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $credentials)
{
    $pRecording = new paloSantoRecordings($pDB);
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $domain='';
    
    if($credentials['userlevel']!='superadmin'){
        $arrOrgz=array();
        $domain=$credentials["domain"];
        $extension = $pRecording->Obtain_Extension_Current_User();
    }else{
        $domain=getParameter('organization');
        $arrOrgz=array(""=>_tr("-- Any (To the system) --"));
        $pORGZ=new paloSantoOrganization($pDB);
        foreach(($pORGZ->getOrganization(array())) as $value){
            $arrOrgz[$value["domain"]]=$value["name"];
        }
        $extension = '';
    }
       
    if(isset($_POST['option_record']) && $_POST['option_record']=='by_file')
        $smarty->assign("check_file", "checked");
    else
        $smarty->assign("check_record", "checked");
    
    $arrForm=createFieldForm($arrOrgz);
    $oForm = new paloForm($smarty,$arrForm);

    $smarty->assign("recording_name_Label", _tr("Record Name"));
    $smarty->assign("overwrite_record", _tr("This record Name already exists. Do you want to Overwrite it?"));
    $smarty->assign("record_Label",_tr("File Upload"));
    $smarty->assign("record_on_extension", _tr("Record On Extension"));
    $smarty->assign("Record", _tr("Record"));
    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("INFO", _tr("Press REC and start your recording. Once you have finished recording you must press ·STOP or hangup the phone").".");
    $smarty->assign("NAME", _tr("You do not need to add an extension to the record name").".");
    $smarty->assign("icon", "web/apps/$module_name/images/recording.png");
    $smarty->assign("module_name", $module_name);
    $smarty->assign("file_upload", _tr("File Upload"));
    $smarty->assign("record", _tr("Record"));
    $smarty->assign("ext", _tr("Extension"));
    $smarty->assign("system", _tr("System"));
    $smarty->assign("exten", _tr("Extension"));
    $smarty->assign("EXTENSION",$extension);
    $smarty->assign("checking", _tr("Checking Name..."));
    $smarty->assign("dialing", _tr("Dialing..."));
    $smarty->assign("domain", $domain);
    $smarty->assign("confirm_dialog", _tr("This Record Name already exists."));
    $smarty->assign("success_record", _tr("Record was saved succesfully."));
    $smarty->assign("cancel_record", _tr("Record was canceled."));
    $smarty->assign("hangup", _tr("Hang up."));
    $max_upload = (int)(ini_get('upload_max_filesize'));
    $max_post = (int)(ini_get('post_max_size'));
    $memory_limit = (int)(ini_get('memory_limit'));
    $upload_mb = min($max_upload, $max_post, $memory_limit)*1048576;
    $smarty->assign("max_size", $upload_mb);
    $smarty->assign("alert_max_size", _tr("File size exceeds the limit. "));
    $smarty->assign("USERLEVEL", $credentials['userlevel']);
    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl", _tr("Recordings"), $_POST);

    $contenidoModulo = "<form enctype='multipart/form-data' method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function createFieldFilter($arrOrgz)
{
    $arrFields = array(
        "organization"  => array("LABEL"         => _tr("Organization"),
                        "DESCRIPTION"            => _tr("RE_organization"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "SELECT",
                        "INPUT_EXTRA_PARAM"      => $arrOrgz,
                        "VALIDATION_TYPE"        => "domain",
                        "VALIDATION_EXTRA_PARAM" => ""),
        "name"  => array("LABEL"                 => _tr("Name"),
                        "DESCRIPTION"            => _tr("RE_attachfile"),
                        "REQUIRED"               => "no",
                        "INPUT_TYPE"             => "TEXT",
                        "INPUT_EXTRA_PARAM"      => '',
                        "VALIDATION_TYPE"        => "text",
                        "VALIDATION_EXTRA_PARAM" => ""),
        );
    return $arrFields;
}

function createFieldForm($arrOrgz)
{
    $arrFields = array(
        "organization"  => array("LABEL"         => _tr("Organization"),
                        "DESCRIPTION"            => _tr("RE_organization"),
                        "REQUIRED"               => "yes",
                        "INPUT_TYPE"             => "SELECT",
                        "INPUT_EXTRA_PARAM"      => $arrOrgz,
                        "VALIDATION_TYPE"        => "domain",
                        "VALIDATION_EXTRA_PARAM" => ""),
        );
    return $arrFields;
}


function getAction()
{
    global $arrPermission;
    if(getParameter("record"))
        return (in_array("create",$arrPermission))?"record":"report";
    else if(getParameter("save"))
        return (in_array("create",$arrPermission))?"save":"report";
    else if(getParameter("remove"))
        return (in_array("delete",$arrPermission))?"remove":"report";
    else if(getParameter("add_recording"))
        return (in_array("create",$arrPermission))?"add":"report";
    elseif(getParameter("action")=="record")
        return (in_array("create",$arrPermission))?"record":"report";
    elseif(getParameter("action")=="hangup")
        return "hangup";
    elseif(getParameter("action")=="check_call_status")
        return "check_call_status";
    elseif(getParameter("action")=="checkName")
        return "checkName";
    elseif(getParameter("action")=="download")
        return "download";
    elseif(getParameter("action")=="remove")
        return (in_array("delete",$arrPermission))?"remove":"report";
    else
        return "report";
}
?>
