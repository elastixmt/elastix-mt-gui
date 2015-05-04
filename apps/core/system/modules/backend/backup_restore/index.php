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
  $Id: index.php,v 1.1 2008/01/30 15:55:57 afigueroa Exp $ */

include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoGrid.class.php";

function _moduleContent(&$smarty, $module_name)
{
//include elastix framework
    include_once "libs/paloSantoValidar.class.php";
    include_once "libs/misc.lib.php";
    include_once "libs/paloSantoForm.class.php";
    include_once "modules/$module_name/libs/paloSantoFTPBackup.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    load_language_module($module_name);

    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $dir_backup = $arrConf["dir"];

    $accion = getAction();
    $content = "";
    switch($accion)
    {
        case 'delete_backup': //BOTON DE BORRAR BACKUP "ELIMINAR"
            $content = delete_backup($smarty, $module_name, $local_templates_dir, $dir_backup, $pDB);
            break;
        case 'backup': //BOTON "RESPALDAR"
            $content = backup_form($smarty, $local_templates_dir, $module_name);
            break;
        case 'submit_restore': //BOTON DE RSTAURAR, lleva a la ventana de seleccion para restaurar
            $content = restore_form($smarty, $local_templates_dir, $dir_backup, $module_name);
            break;
        case 'process_backup':
            $content = process_backup($smarty, $local_templates_dir, $module_name);
            break;
        case 'process_restore':
            $content = process_restore($smarty, $local_templates_dir, $dir_backup, $module_name);
            break;
        case 'download_file':
            $content = downloadBackup($smarty, $module_name, $local_templates_dir, $dir_backup);
            break;

/******************************* PARA FTP BACKUP ***************************************/
        case "save_new_FTP":
            $content = saveNewFTPBackup($smarty, $module_name, $local_templates_dir, $pDB);
            break;
        case "view_form_FTP":
            $content = viewFormFTPBackup($smarty, $module_name, $local_templates_dir, $pDB);
            break;
        case 'uploadFTPServer':
            $content = file_upload_FTPServer($module_name, $pDB);
            break;
        case 'downloadFTPServer':
            $content = file_download_FTPServer($module_name, $pDB);
            break;
/***************************************************************************************/
          case "detail":
            $content = viewDetail($smarty, $module_name, $local_templates_dir, $dir_backup);
            break;
/******************************* PARA BACKUP AUTOMATICO ********************************/
        case "automatic":
            $content = automatic_backup($smarty, $module_name, $local_templates_dir, $dir_backup,$pDB);
            break;
/***************************************************************************************/
        default:
            $content = report_backup_restore($smarty, $module_name, $local_templates_dir, $dir_backup, $pDB);
            break;
    }

    return $content;
}

function report_backup_restore($smarty, $module_name, $local_templates_dir, $dir_backup, &$pDB)
{
    $total_archivos = array_reverse(array_map('basename', glob("$dir_backup/*.tar")));

    // Paginacion
    $limit = 10;
    $total = count($total_archivos);
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = $oGrid->getEnd();

    $nombre_archivos = array_slice($total_archivos, $offset, $limit);
    //Fin Paginacion
    
    // obtencion de parametros desde la base
    $pFTPBackup = new paloSantoFTPBackup($pDB);
    $_DATA = $pFTPBackup->getStatusAutomaticBackupById(1);
    if(!(is_array($_DATA) & count($_DATA)>0)){
        $_DATA['status'] = "DISABLED";
    }

    $arrData = null;
    if(is_array($nombre_archivos) && $total>0){
        foreach($nombre_archivos as $key => $nombre_archivo){
            $arrTmp[0] = "<input type='checkbox' name='chk[".$nombre_archivo."]' id='chk[".$nombre_archivo."]'/>";
            $arrTmp[1] = "<a href='?menu=$module_name&action=download_file&file_name=$nombre_archivo&rawmode=yes'>$nombre_archivo</a>";
            $fecha="";
            // se parsea el archivo para obtener la fecha
            if(preg_match("/\w*-\d{4}\d{2}\d{2}\d{2}\d{2}\d{2}-\w{2}\.\w*/",$nombre_archivo)){ //elastixbackup-20110720122759-p7.tar
                $arrMatchFile = preg_split("/-/",$nombre_archivo);
                $data  = $arrMatchFile[1];
                $fecha = substr($data,-8,2)."/".substr($data,-10,2)."/".substr($data,0,4)." ".substr($data,-6,2).":".substr($data,-4,2 ).":".substr($data,-2,2);
                $id    = $arrMatchFile[1]."-".$arrMatchFile[2];
            }

            $arrTmp[2] = $fecha;
            $arrTmp[3] = "<input type='submit' name='submit_restore[".$nombre_archivo."]' value='"._tr('Restore')."' class='button' />";
            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title"    => _tr('Backup List'),
                     "url"      => array('menu' => $module_name),
                     "icon"     => "/modules/$module_name/images/system_backup_restore.png",
                     "width"    => "99%",
                     "start"    => ($total==0) ? 0 : $offset + 1,
                     "end"      => $end,
                     "total"    => $total,
                     "columns"  => array(0 => array("name"      => ""),
                                         1 => array("name"      => _tr('Name Backup')),
                                         2 => array("name"      => _tr('Date')),
                                         3 => array("name"      => _tr('Action')),
                                    )
                    );
    $time = $_DATA['status'];

    $smarty->assign("FILE_UPLOAD", _tr('File Upload'));
    $smarty->assign("AUTOMATIC", _tr('AUTOMATIC'));
    $smarty->assign("UPLOAD", _tr('Upload'));
    $smarty->assign("FTP_BACKUP", _tr('FTP Backup'));
    $oGrid->addNew("backup",_tr("Backup"));
    $oGrid->deleteList(_tr("Are you sure you wish to delete backup (s)?"),'delete_backup',_tr("Delete"));
    $oGrid->customAction("view_form_FTP",_tr("FTP Backup"));

    $backupIntervals = array(
        'DISABLED'  =>  _tr('DISABLED'),
        'DAILY'     =>  _tr('DAILY'),
        'MONTHLY'   =>  _tr('MONTHLY'),
        'WEEKLY'    =>  _tr('WEEKLY'),
    );

    $oGrid->addComboAction("time",_tr("AUTOMATIC"),$backupIntervals,$time,'automatic');
    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData);

    return $contenidoModulo;
}

function automatic_backup($smarty, $module_name, $local_templates_dir, $dir_backup, &$pDB)
{
	$time = getParameter("time");

    //if there is data in database
    $pFTPBackup = new paloSantoFTPBackup($pDB);
    $result = $pFTPBackup->getStatusAutomaticBackupById();
    if(isset($result) && $result != "")
        $pFTPBackup->updateStatus($time);
    else
        $pFTPBackup->insertStatus($time);
    $smarty->assign("mb_message", _tr('SUCCESSFUL'));
    $pFTPBackup->createCronFile($time);

    return report_backup_restore($smarty, $module_name, $local_templates_dir, $dir_backup, $pDB);
}

function downloadBackup($smarty, $module_name, $local_templates_dir, $dir_backup, &$pDB)
{
    $bArchivoValido = TRUE;

    $file_name = getParameter("file_name");
    if (basename($file_name) != $file_name) {
        $bArchivoValido = FALSE;
    } elseif (!preg_match('/^elastixbackup-\d{14}-\w{2}\.tar$/', $file_name)) {
        $bArchivoValido = FALSE;
    }

    if ($bArchivoValido) {
        if (file_exists("$dir_backup/$file_name")) {
            header("Cache-Control: private");
            header("Pragma: cache");
            header('Content-Type: application/octet-stream');
            header("Content-Length: ".filesize("$dir_backup/$file_name"));
            header("Content-Disposition: attachment; filename=$file_name");

            readfile("$dir_backup/$file_name");
        } else {
            header("HTTP/1.1 404 Not Found");
            print "File not found";
        }
    } else {
        header("HTTP/1.1 403 Forbidden");
        print "Invalid file";
    }
}

function delete_backup($smarty, $module_name, $local_templates_dir, $dir_backup, &$pDB)
{
    function delete_backup_isInvalidFile($file_name) {
        return !preg_match('/^elastixbackup-\d{14}-\w{2}\.tar$/', $file_name);
    }
    function delete_backup_doDelete($filePath) {
    	return file_exists($filePath) ? !unlink($filePath) : FALSE;
    }

    $archivos_borrar = isset($_POST['chk']) ? array_keys($_POST['chk']) : array();
    if (!is_array($archivos_borrar) || count($archivos_borrar) <= 0) {
    	$smarty->assign('mb_message', _tr('There are not backup file selected'));
    } elseif (count(array_filter(array_map('delete_backup_isInvalidFile', $archivos_borrar))) > 0) {
        $smarty->assign('mb_message', _tr('Invalid files selected to delete'));
    } else {
    	foreach (array_keys($archivos_borrar) as $i)
            $archivos_borrar[$i] = $dir_backup.'/'.$archivos_borrar[$i];
        if (count(array_filter(array_map('delete_backup_doDelete', $archivos_borrar))) > 0) {
            $smarty->assign('mb_message', _tr('Error when deleting backup file'));
        }
    }
    return report_backup_restore($smarty, $module_name, $local_templates_dir, $dir_backup, $pDB);
}

function form_general($smarty, $local_templates_dir, $arrBackupOptions, $module_name)
{
    $smarty->assign("PROCESS",_tr('Process'));
    $smarty->assign("LBL_TODOS", _tr('Select All options'));
    $smarty->assign("TODO_FAX", _tr('Select all in this section'));
    $smarty->assign("TODO_EMAIL", _tr('Select all in this section'));
    $smarty->assign("TODO_ENDPOINT", _tr('Select all in this section'));
    $smarty->assign("TODO_ASTERISK", _tr('Select all in this section'));
    $smarty->assign("TODO_OTROS", _tr('Select all in this section'));
    $smarty->assign("TODO_OTROS_NEW", _tr('Select all in this section'));
    $smarty->assign("BACK", _tr('Cancel'));
    $smarty->assign("WARNING", _tr('This process could take several minutes'));

    /*****************/
    $smarty->assign("FAX", _tr('Fax'));
    $smarty->assign("EMAIL", _tr('Email'));
    $smarty->assign("ENDPOINT", _tr('Endpoint'));
    $smarty->assign("ASTERISK", _tr('Asterisk'));
    $smarty->assign("OTROS", _tr('Others'));
    $smarty->assign("OTROS_NEW", _tr('Others'));
    /*****************/

    $smarty->assign("backup_fax", $arrBackupOptions['fax']);
    $smarty->assign("backup_email", $arrBackupOptions['email']);
    $smarty->assign("backup_endpoint", $arrBackupOptions['endpoint']);
    $smarty->assign("backup_asterisk", $arrBackupOptions['asterisk']);
    $smarty->assign("backup_otros", $arrBackupOptions['otros']);
    $smarty->assign("backup_otros_new", $arrBackupOptions['otros_new']);

    $smarty->assign("module", $module_name);
    return $smarty->fetch("$local_templates_dir/backup.tpl");
}

function backup_form($smarty, $local_templates_dir, $module_name)
{
    $arrBackupOptions = Array_Options();

    $smarty->assign("title", _tr('Backup'));
    $smarty->assign("OPTION_URL", "backup");

    return form_general($smarty, $local_templates_dir, $arrBackupOptions, $module_name);
}

function restore_form($smarty, $local_templates_dir, $path_backup, $module_name)
{
    $arrBackupOptions = Array_Options("disabled='disabled'");
    if(isset($_POST["submit_restore"]))
    {
        $arr = array_keys($_POST["submit_restore"]);
        $archivo_post = $arr[0];
    }else $archivo_post = isset($_POST["backup_file"])?$_POST["backup_file"]:"";
    $archivo_post = basename($archivo_post);

    $output = $retval = NULL;
    exec('tar -xOf '.escapeshellarg("$path_backup/$archivo_post").' backup/a_options.xml',
        $output, $retval);
    if ($retval == 0)
    {
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML(implode('', $output));        

        //copio el archivo en memoria
        $root = $xmlDoc->documentElement;//apunto a el tag raiz

        $optionsList = $root->getElementsByTagName("options");
        foreach($optionsList as $optionGeneral) {
            $attributeID = $optionGeneral->getAttribute("id");
            $option = $optionGeneral->getElementsByTagName("option");
            foreach($option as $value) {
                $arrBackupOptions[$attributeID][$value->nodeValue]["disable"] = "";
            }
        }
    }

    $smarty->assign("BACKUP_FILE", $archivo_post);
    $smarty->assign("title", _tr("Restore"). ": $archivo_post");
    $smarty->assign("OPTION_URL", "restore");
    list($versionList_current, $versionList_torestore, $compare) = 
        runPackageVersionCompare($path_backup, $smarty, $archivo_post);

    if (!is_null($compare) && count($compare) > 0) {
        $pag = '"?menu='.$module_name.'&action=detail&rawmode=yes&file_name='.$archivo_post.'"';
        $outMessage = _tr('Warning')." <a href='javascript:popup_dif($pag);'>"._tr('details').'</a>';
        $smarty->assign('mb_message', $outMessage);
    }

    return form_general($smarty, $local_templates_dir, $arrBackupOptions, $module_name);
}

function process_backup($smarty, $local_templates_dir, $module_name)
{
	// Recolectar las claves conocidas seleccionadas
    $opcionesBackup = Array_Options();
    $clavesBackup = array();
    foreach ($opcionesBackup as $opcionBackup) {
    	$clavesBackup = array_merge($clavesBackup, array_keys($opcionBackup));
    }
    $clavesSeleccion = array_intersect($clavesBackup, array_keys($_POST));

    // Ejecución del comando en sí
    $sArchivoBackup = 'elastixbackup-'.date('YmdHis').'-'.substr(session_id(), 0, 1).substr(session_id(), -1, 1).'.tar';
    $sDirBackup = '/var/www/backup';
    $sOpcionesBackup = implode(',', $clavesSeleccion);
    $output = $retval = NULL;
    $sComando = '/usr/bin/elastix-helper backupengine --backup'.
        " --backupfile $sArchivoBackup".
        " --tmpdir $sDirBackup".
        " --components $sOpcionesBackup".
        ' 2>&1';
    exec($sComando, $output, $retval);
    if ($retval == 0) {
    	$smarty->assign('ERROR_MSG', _tr('Backup Complete!').': '.$sArchivoBackup);
    } else {
    	$sMensaje = _tr('Could not generate backup file').': '.$sArchivoBackup.'<br/>'.
            _tr('Output follows: ').'<br/><br/>'.
            implode("<br/>\n", $output);
        $smarty->assign('ERROR_MSG', $sMensaje);
    }

    return backup_form($smarty, $local_templates_dir, $module_name);
}

function process_restore($smarty, $local_templates_dir, $path_backup, $module_name)
{
    $smarty->assign("module", $module_name);

    // Recolectar las claves conocidas seleccionadas
    $opcionesBackup = Array_Options();
    $clavesBackup = array();
    foreach ($opcionesBackup as $opcionBackup) {
        $clavesBackup = array_merge($clavesBackup, array_keys($opcionBackup));
    }
    $clavesSeleccion = array_intersect($clavesBackup, array_keys($_POST));

    if (count($clavesSeleccion) <= 0) {
    	$smarty->assign('ERROR_MSG', _tr('Choose an option to restore'));
    } elseif (!isset($_POST['backup_file']) || trim($_POST['backup_file']) == '') {
        $smarty->assign('ERROR_MSG', _tr("Backup file path can't be empty"));
    } elseif (!file_exists($path_backup.'/'.$_POST['backup_file'])) {
        $smarty->assign('ERROR_MSG', _tr("File doesn't exist"));
    } else {
        // Ejecución del comando en sí
        $sOpcionesBackup = implode(',', $clavesSeleccion);
        $output = $retval = NULL;
        $sComando = '/usr/bin/elastix-helper backupengine --restore'.
            ' --backupfile '.escapeshellarg($_POST['backup_file']).
            ' --tmpdir '.escapeshellarg($path_backup).
            " --components $sOpcionesBackup".
            ' 2>&1';
        exec($sComando, $output, $retval);
        if ($retval == 0) {
        	$smarty->assign('ERROR_MSG', _tr('Restore Complete!'));
        } else {
            $sMensaje = _tr('Could not restore from backup file').': '.$_POST['backup_file'].'<br/>'.
                _tr('Output follows: ').'<br/><br/>'.
                implode("<br/>\n", $output);
            $smarty->assign('ERROR_MSG', $sMensaje);
        }
    }
    return restore_form($smarty, $local_templates_dir, $path_backup, $module_name);
}

function Array_Options($disabled="")
{
    $arrBackupOptions = array(
        "asterisk"      =>  array(
            "as_db"             =>  array("desc"=>_tr('Database')),
            "as_config_files"   =>  array("desc"=>_tr('Configuration Files')),
            "as_monitor"        =>  array("desc"=>_tr('Monitors')."  "._tr('(Heavy Content)')),
            "as_voicemail"      =>  array("desc"=>_tr('Voicemails')."  "._tr('(Heavy Content)')),
            "as_sounds"         =>  array("desc"=>_tr('Sounds')),
            "as_moh"            =>  array("desc"=>_tr('MOH')),
            "as_dahdi"          =>  array("desc"=>_tr('DAHDI Configuration')),
        ),
        "fax"           =>  array(
            "fx_db"             =>  array("desc"=>_tr('Database')),
            "fx_pdf"            =>  array("desc"=>_tr('PDF')),
        ),
        "email"         =>  array(
            "em_db"             =>  array("desc"=>_tr('Database')),
            "em_mailbox"        =>  array("desc"=>_tr('Mailbox')),
        ),
        "endpoint"      =>  array(
            "ep_db"             =>  array("desc"=>_tr('Database')),
            "ep_config_files"   =>  array("desc"=>_tr('Configuration Files')),
        ),
        "otros"         =>  array(
            "sugar_db"          =>  array("desc"=>_tr('SugarCRM Database')),
            "vtiger_db"         =>  array("desc"=>_tr('VtigerCRM Database')),
            "a2billing_db"      =>  array("desc"=>_tr('A2billing Database')),
            "mysql_db"          =>  array("desc"=>_tr('Mysql Database')),
            "menus_permissions" =>  array("desc"=>_tr('Menus and Permissions')),
            "fop_config"        =>  array("desc"=>_tr('Flash Operator Panel Config Files')),
        ),
       "otros_new"      =>  array(
            "calendar_db"       =>  array("desc"=>_tr('Calendar  Database')),
            "address_db"        =>  array("desc"=>_tr('Address Book Database')),
            "conference_db"     =>  array("desc"=>_tr('Conference  Database')),
            "eop_db"            =>  array("desc"=>_tr('EOP')),
        ),
    );
    foreach (array_keys($arrBackupOptions) as $k1)
    foreach (array_keys($arrBackupOptions[$k1]) as $k2) {
        $arrBackupOptions[$k1][$k2]['check'] = '';
    	$arrBackupOptions[$k1][$k2]['msg'] = '';
        $arrBackupOptions[$k1][$k2]['disable'] = "$disabled";
    }
    
    return $arrBackupOptions;
}

/* ------------------------------------------------------------------------------- */
/* FUNCIONS PARA EL BACKUP*/
/* ------------------------------------------------------------------------------- */


/* ------------------------------------------------------------------------------- */
/* FUNCIONS PARA EL RESTORE*/
/* ------------------------------------------------------------------------------- */

function runPackageVersionCompare($path_backup, $smarty, $backup_file)
{
    //verificar que existe el archivo de respaldo
    $path_file_backup = "$path_backup/$backup_file";
    if (empty($backup_file)) {
        $smarty->assign('ERROR_MSG', _tr("Backup file path can't be empty"));
        return NULL;
    }
    if (!preg_match('/^elastixbackup-\d{14}-\w{2}\.tar$/', $backup_file)) {
        $smarty->assign('ERROR_MSG', _tr('Invalid backup filename'));
        return NULL;
    }
    if (!file_exists($path_file_backup)) {
        $smarty->assign('ERROR_MSG', _tr("File doesn't exist"));
        return NULL;
    }

    $versionList_current = getVersionPrograms_SYSTEM();
    $versionList_torestore = getVersionPrograms_XML($path_file_backup);
    if (is_null($versionList_torestore)) {
        $smarty->assign("mb_message", _tr('no_file_xml'));
        return NULL;
    }
    $compare = comparePackageVersions($versionList_current, $versionList_torestore);
    return array($versionList_current, $versionList_torestore, $compare);
}

function boxAlert($smarty, $local_templates_dir, $versionList_current, $versionList_torestore, $compare)
{
    $packagereport = array();
    foreach($compare as $key => $value) {
        $tupla = array(
            'desc'              =>  _tr($key),
            'name'              =>  $key,
            'version_current'   =>  '',
            'version_backup'    =>  '',
        );

        if (!isset($versionList_torestore[$key]['version']) || !isset($versionList_torestore[$key]['release']) ||
            $versionList_torestore[$key]['version'] == $versionList_torestore[$key]['release']){
            $tupla['version_backup'] = "<span style='font-style: italic; color: red;'>"._tr("Package not installed")."</span>";
        } else {
        	$tupla['version_backup'] = $versionList_torestore[$key]['version']."-".$versionList_torestore[$key]['release'];
        }

        $tupla['version_current'] = $versionList_current[$key]['version']."-".$versionList_current[$key]['release'];
        if ($versionList_current[$key]['version'] == $versionList_current[$key]['release']){
            $tupla['version_current'] = "<span style='font-style: italic; color: red;'>"._tr("Package not installed")."</span>";
        }

        $packagereport[] = array_merge($tupla, getValueofBackupOption($key));
    }
	$smarty->assign(array(
        'warning_details'   =>  _tr('warning_details'),
        'programs'          =>  _tr('programs'),
        'Package'           =>  _tr('Package'),
        'Version'           =>  _tr('Version'),
        'local_version'     =>  _tr('local_version'),
        'external_version'  =>  _tr('external_version'),
        'Options_Backup'    =>  _tr('Options Backup'),
        'Endpoint'          =>  _tr('Endpoint'),
        'Fax'               =>  _tr('Fax'),
        'Email'             =>  _tr('Email'),
        'Asterisk'          =>  _tr('Asterisk'),
        'Others'            =>  _tr('Others'),
        'Others_new'        =>  _tr('Others new'),
        'packagereport'     =>  $packagereport,
    ));
    return $smarty->fetch("$local_templates_dir/versionCompareDetail.tpl");
}

function getValueofBackupOption($valueOp)
{
    $arrayOptions = array(
        "endpoint" => array("elastix-pbx"),
        "fax" => array("elastix-fax"),
        "email" => array("elastix","elastix-email_admin"),
        "asterisk" => array("asterisk","dahdi","wanpipe-util","freepbx","elastix"),
        "otros" => array("elastix-vtigercrm","elastix-a2billing","elastix","elastix-pbx","elastix-sugarcrm-addon"),
        "otros_new" => array("elastix-pbx","elastix-agenda")
    );
    $arrayResult = array();
    foreach($arrayOptions as $key => $value) {
        $arrayResult[$key] = in_array($valueOp, $value) ? 'x' : '';
    }
    return $arrayResult;
}

function showMessageAlert($arr){
    $version = "";
    foreach($arr as $key => $value){
        $version .= "$key => version ".$arr[$key]['version']." release ".$arr[$key]['release']."\n";
    }
    return $version;
}

function viewDetail($smarty, $module_name, $local_templates_dir, $path_backup)
{
    $htmlForm = '';
    $backup_file = getParameter("file_name");
    list($versionList_current, $versionList_torestore, $compare) =
        runPackageVersionCompare($path_backup, $smarty, $backup_file);
    if (!is_null($compare)) {
        $htmlForm = boxAlert($smarty, $local_templates_dir, $versionList_current, $versionList_torestore, $compare);
    }
    return $htmlForm;
}

function getVersionPrograms_SYSTEM()
{
    $packageList = array('asterisk', 'dahdi', 'wanpipe-util', 'freePBX',
        'elastix', 'elastix-pbx', 'elastix-email_admin', 'elastix-agenda',
        'elastix-fax', 'elastix-vtigercrm', 'elastix-a2billing',
        'elastix-sugarcrm-addon');
    $output = $retval = NULL;
    exec("rpm -q --queryformat '%{name} %{version} %{release}\\n' ".implode(' ', $packageList),
        $output, $retval);

    // Add all existing packages to report
    $arrPro = array();
    foreach ($output as $s) {
        $fields = explode(' ', trim($s));
        if (count($fields) == 3 && in_array($fields[0], $packageList)) {
        
            // This is needed for compatibility with previous backup implementation 
            $sPackageName = $fields[0];
            if ($sPackageName == 'freePBX') $sPackageName = 'freepbx';

            $arrPro[$sPackageName] = array(
                'version'   =>  $fields[1],
                'release'   =>  $fields[2],
            );
            $k = array_search($fields[0], $packageList);
            unset($packageList[$k]);
        }
    }
    
    /* Any remaining values in $packageList are missing packages. The missing
     * package is marked with 'Package not installed' as attribute value for
     * compatibility with the previous backup implementation. */
    foreach ($packageList as $sPackage) {
        // The string is deliberately not translated
        $arrPro[$sPackage] = array(
            'version'   =>  'Package not installed',
            'release'   =>  'Package not installed',
        );
    }

    return $arrPro;
}

function getVersionPrograms_XML($path_file_backup)
{
    // Output program versions to stdout
    $output = $retval = NULL;
    exec('tar -xOf '.escapeshellarg($path_file_backup).' backup/versions.xml',
        $output, $retval);
    if ($retval != 0) return NULL;
    $xmlDoc = new DOMDocument();
    if (!$xmlDoc->loadXML(implode('', $output))) return NULL;
    
    $arrPrograms = null;

    //copio el archivo en memoria
    $root = $xmlDoc->documentElement;//apunto a el tag versions
    $optionsList = $root->getElementsByTagName("program");

    foreach($optionsList as $optionGeneral) {
        $arrPrograms[$optionGeneral->getAttribute("id")] = array(
            "version" => $optionGeneral->getAttribute("ver"),
            "release" => $optionGeneral->getAttribute("rel"));
    }
    return $arrPrograms;
}

/**
 * Procedimiento para comparar las listas de versiones entre lo instalado 
 * actualmente y lo que se va a restarar, para avisar de posibles 
 * inconsistencias.
 * 
 * @param   array   $versionList_current    Lista de paquetes instalados 
 *                                          actualmente.
 * @param   array   $versionList_torestore  Lista de paquetes que estaban 
 *                                          instalados cuando se realizó el
 *                                          backup.
 * 
 * @return  array   Lista (posiblemente vacía) de diferencias de versiones
 */
function comparePackageVersions($versionList_current, $versionList_torestore)
{
    $errors = array();
    foreach ($versionList_current as $key => $value) {
        if (!isset($versionList_torestore[$key]) || !isset($versionList_torestore[$key]['version']) ||
            !isset($versionList_torestore[$key]['release']) ||
            $versionList_torestore[$key]['version'] != $versionList_current[$key]['version'] ||
            $versionList_torestore[$key]['release'] != $versionList_current[$key]['release']) {

            $errors[$key] = $versionList_current[$key]['version']. "-" .$versionList_current[$key]['release'];
        }
    }
    return $errors;
}

/************************  FUNCIONES PARA FTP BACKUP ***********************************/
function viewFormFTPBackup($smarty, $module_name, $local_templates_dir, &$pDB)
{
    global $arrConf;
    
    // Variables estáticas
    $smarty->assign(array(
        'SAVE'              =>  _tr('Save'),
        'EDIT'              =>  _tr('Edit'),
        'CANCEL'            =>  _tr('Cancel'),
        'UPLOAD'            =>  _tr('Upload'),
        'DOWNLOAD'          =>  _tr('Download'),
        'TITLE'             =>  _tr('TITLE'),
        'REQUIRED_FIELD'    =>  _tr('Required field'),
        'icon'              =>  "modules/$module_name/images/system_backup_restore.png",
        'module_name'       =>  $module_name,
    ));
    
    $pFTPBackup = new paloSantoFTPBackup($pDB);
    
    // Datos a mostrar en el formulario de credenciales del servidor
    $ftpcred = array(
        'server'        =>  '',
        'port'          =>  21,
        'user'          =>  '',
        'password'      =>  '',
        'pathServer'    =>  '/',
    );
    $dbcred = $pFTPBackup->obtenerCredencialesFTP();
    if (is_array($dbcred)) foreach (array_keys($ftpcred) as $k) {
    	if (isset($dbcred[$k])) $ftpcred[$k] = $dbcred[$k];
    }
    foreach (array_keys($ftpcred) as $k)
        if (isset($_POST[$k])) $ftpcred[$k] = $_POST[$k];

    // Listado de archivos local y remoto
    $smarty->assign('local_files', $pFTPBackup->obtainFiles($arrConf['dir']));
    $smarty->assign('remote_files', $pFTPBackup->listarArchivosTarFTP());
    if ($pFTPBackup->errMsg != '') $smarty->assign('mb_message', $pFTPBackup->errMsg);

    $oForm = new paloForm($smarty, createFieldForm());
    $htmlForm = $oForm->fetchForm("$local_templates_dir/formFTP.tpl", _tr('FTP Backup'), $ftpcred);
    return "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
}

function saveNewFTPBackup($smarty, $module_name, $local_templates_dir, &$pDB)
{
    $pFTPBackup = new paloSantoFTPBackup($pDB);

    $oForm = new paloForm($smarty, createFieldForm());
    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE
        $smarty->assign("mb_title", _tr('Validation Error'));
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr('The following fields contain errors').":</b><br/>";
        if (is_array($arrErrores) && count($arrErrores) > 0) {
            $strErrorMsg .= implode(', ', array_keys($arrErrores));
        }
        $smarty->assign("mb_message", $strErrorMsg);
    } else {
        $server = getParameter("server");
        $port = getParameter("port");
        $user = getParameter("user");
        $password = getParameter("password");
        $path = getParameter("pathServer");

        //deben estar llenos todos los campos
        if ($server &&  $port &&  $user  &&  $password &&  $path) {
            $r = $pFTPBackup->asignarCredencialesFTP($server, $port, $user, $password, $path);
            if (!$r) $smarty->assign("mb_message", $pFTPBackup->errMsg);
        } else
            $smarty->assign("mb_message", _tr('Error to save'));
    }
    return viewFormFTPBackup($smarty, $module_name, $local_templates_dir, $pDB);
}
/*****************************************************************************************/
/*************** FUNCIONES PARA HACER UN BACKUP/RESTORE A UN SERVIDOR FTP ****************/

function file_upload_FTPServer($module_name, &$pDB)
{
    $file    = getParameter('file');
    $lista   = getParameter('lista'); //identifica en que lista se hace el drop

    $array = obtainList($file);
    if($lista == 'droptrue2' && $array[0] == 'out')
        return _tr('Error Drag Drop');
    if(!$array[1])
        return _tr('Error Drag Drop');
    $pFTPBackup = new paloSantoFTPBackup($pDB);
    $r = $pFTPBackup->enviarArchivoFTP($array[1]);
    if (!$r)
        return $pFTPBackup->errMsg;
    return _tr('Successfully uploaded').' '.$array[1];
}

function file_download_FTPServer($module_name, &$pDB)
{
    $file    = getParameter('file');
    $lista   = getParameter('lista'); //identifica en que lista se hace el drop

    $array = obtainList($file);
    if($lista == 'droptrue' && $array[0] == 'inn')
        return _tr('Error Drag Drop');
    if(!$array[1])
        return _tr('Error Drag Drop');
    $pFTPBackup = new paloSantoFTPBackup($pDB);
    $r = $pFTPBackup->recibirArchivoFTP($array[1]);
    if (!$r)
        return $pFTPBackup->errMsg;
    return _tr('Successfully written').' '.$array[1];
}

function obtainList($fileString)
{
    $token = strtok($fileString, "_");
    $out = "";
    $i = 0;
    while ($token != false)
    {
        $out[$i] = $token;
        $token = strtok(";");
        $i++;
    }
    return $out;
}
/******************************************************************************************/

function createFieldForm()
{
    $arrFields = array(
        "server"   => array(      
            "LABEL"                  => _tr("Server FTP"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
            ),
        "port"   => array(
            "LABEL"                  => _tr("Port"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
            ),
        "user"   => array(
            "LABEL"                  => _tr("User"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
            ),
        "password"   => array(
            "LABEL"                  => _tr("Password"),
            "REQUIRED"               => "si",
            "INPUT_TYPE"             => "PASSWORD",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
            ),
        "local"   => array(
            "LABEL"                  => _tr("Local"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
            ),
        "server_ftp"   => array(
            "LABEL"                  => _tr("Server FTP"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
            ),
        "pathServer"   => array(
            "LABEL"                  => _tr("Path Server FTP"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""
            ),
        );
    return $arrFields;
}

function getAction()
{
    if      (isset($_POST["delete_backup"])) return "delete_backup";
    else if (isset($_POST["backup"])) return "backup";
    else if (isset($_POST["submit_restore"])) return "submit_restore";
    else if (isset($_POST["process"]) && $_POST["option_url"]=="backup")  return  "process_backup";
    else if (isset($_POST["process"]) && $_POST["option_url"]=="restore") return  "process_restore";

/******************************* PARA FTP BACKUP *************************************/
    else if (isset($_POST["upload"])) return "upload";
    else if (getParameter("action")=="download_file") return "download_file";
    else if (isset($_POST["ftp_backup"])) return "ftp_backup";
    else if (isset($_POST["save_new_FTP"])) return "save_new_FTP";
    else if (isset($_POST["view_form_FTP"])) return "view_form_FTP";
/************************* POPUP DE DETALES DE FTP_BACKUP ****************************/
    else if (getParameter("action")=="detail") return "detail";
/****************************** PARA BACKUP AUTOMATICO ********************************/
    else if (isset($_POST["automatic"])) return "automatic";
/**************************************************************************************/
/****************************** PARA EL CONTROL AJAX **********************************/
    else if (getParameter("action") == "uploadFTPServer") return "uploadFTPServer";
    else if (getParameter("action") == "downloadFTPServer") return "downloadFTPServer";
/**************************************************************************************/
    else return "report_backup_restore";

}
?>