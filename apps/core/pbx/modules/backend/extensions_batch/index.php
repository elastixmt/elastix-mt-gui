<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0                                                  |
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
  $Id: index.php,v 1.1 2008/01/30 15:55:57 a_villacis Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    
    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    switch (getParameter('action')) {
    case 'csvdownload':
        return download_csv($smarty);
    default:
        return display_form($smarty, $module_name, $local_templates_dir);
    }
}

function display_form($smarty, $module_name, $local_templates_dir)
{
    require_once "libs/paloSantoForm.class.php";

	if (getParameter('csvupload') != '') {
		upload_csv($smarty, $module_name);
	}
    if (getParameter('delete_all') != '') {
    	delete_extensions($smarty, $module_name);
    }
    
    $smarty->assign(array(
        'MODULE_NAME'       =>  $module_name,
        'LABEL_FILE'        =>  _tr("File"),
        'LABEL_UPLOAD'      =>  _tr('Save'),
        'LABEL_DOWNLOAD'    =>  _tr("Download Extensions"),
        'LABEL_DELETE'      =>  _tr('Delete All Extensions'),
        'CONFIRM_DELETE'    =>  _tr("Are you really sure you want to delete all the extensions in this server?"),
        'HeaderFile'        =>  _tr("Header File Extensions Batch"),
        'AboutUpdate'       =>  _tr("About Update Extensions Batch"),
    ));
    
    $oForm = new paloForm($smarty, array());
    return $oForm->fetchForm("$local_templates_dir/extension.tpl", _tr('Extensions Batch'), $_POST);
}

function download_csv($smarty)
{
    header("Cache-Control: private");
    header("Pragma: cache");
    header('Content-Type: text/csv; charset=iso-8859-1; header=present');
    header("Content-disposition: attachment; filename=extensions.csv");

    $pLoadExtension = build_extensionsBatch($smarty);
    $r = $pLoadExtension->queryExtensions();
    
    if (!is_array($r)) {
        print $pLoadExtension->errMsg;
        return;
    }
    
    $keyOrder = array(
        'name'                  =>  'Display Name',
        'extension'             =>  'User Extension',
        'directdid'             =>  'Direct DID',
        'outboundcid'           =>  'Outbound CID',
        'callwaiting'           =>  'Call Waiting',
        'secret'                =>  'Secret',
        'voicemail'             =>  'Voicemail Status',
        'vm_secret'             =>  'Voicemail Password',
        'email_address'         =>  'VM Email Address',
        'pager_email_address'   =>  'VM Pager Email Address',
        'vm_options'            =>  'VM Options',
        'email_attachment'      =>  'VM Email Attachment',
        'play_cid'              =>  'VM Play CID',
        'play_envelope'         =>  'VM Play Envelope',
        'delete_vmail'          =>  'VM Delete Vmail',
        'context'               =>  'Context',
        'tech'                  =>  'Tech',
        'callgroup'             =>  'Callgroup',
        'pickupgroup'           =>  'Pickupgroup',
        'disallow'              =>  'Disallow',
        'allow'                 =>  'Allow',
        'deny'                  =>  'Deny',
        'permit'                =>  'Permit',
        'record_in'             =>  'Record Incoming',
        'record_out'            =>  'Record Outgoing',
        );
    print '"'.implode('","', $keyOrder)."\"\n";
    
    
    foreach ($r as $tupla) {
    
        $t = array();
        foreach (array_keys($keyOrder) as $k) switch ($k) {
        
            case 'name':                    $t[] = $tupla['name']; break;
            case 'extension':               $t[] = $tupla['extension']; break;
            case 'directdid':               $t[] = $tupla['directdid']; break;
            case 'outboundcid':             $t[] = $tupla['outboundcid']; break;
            case 'callwaiting':             $t[] = $tupla['callwaiting']; break;
            case 'voicemail':               $t[] = $tupla['voicemail']; break;
            case 'vm_secret':               $t[] = $tupla['vm_secret']; break;
            case 'email_address':           $t[] = $tupla['email_address']; break;
            case 'pager_email_address':     $t[] = $tupla['pager_email_address']; break;
            case 'vm_options':              $t[] = $tupla['vm_options']; break;
            case 'email_attachment':        $t[] = $tupla['email_attachment']; break;
            case 'play_cid':                $t[] = $tupla['play_cid']; break;
            case 'play_envelope':           $t[] = $tupla['play_envelope']; break;
            case 'delete_vmail':            $t[] = $tupla['delete_vmail']; break;
            case 'tech':                    $t[] = $tupla['tech']; break;
            
            default:
            if (isset($tupla['parameters'][$k])){                             
                if ($tupla['parameters'][$k] == "Adhoc"){
                    $tupla['parameters'][$k] = "On Demand";
                    $t[] = $tupla['parameters'][$k];
                }
                else
                    $t[] = $tupla['parameters'][$k];
            }else
                $t[] = '';
            
        }
        
        print '"'.implode('","', $t)."\"\n";
    }
}

function delete_extensions($smarty, $module_name)
{
    $pLoadExtension = build_extensionsBatch($smarty);
    $r = $pLoadExtension->deleteExtensions();
    if ($r) {
        $smarty->assign("mb_title", _tr('Message'));
        $smarty->assign("mb_message", _tr('All extensions deletes'));
    } else {
        $smarty->assign("mb_title", _tr('Error'));
        $smarty->assign("mb_message", _tr('Could not delete the database').': '.$pLoadExtension->errMsg);
    }
}

function upload_csv($smarty, $module_name)
{
    if (!preg_match('/.csv$/', $_FILES['csvfile']['name'])) {
        $smarty->assign("mb_title", _tr('Validation Error'));
        $smarty->assign("mb_message", _tr('Invalid file extension.- It must be csv'));
        return;
    }
    if (!is_uploaded_file($_FILES['csvfile']['tmp_name'])) {
        $smarty->assign("mb_title", _tr('Error'));
        $smarty->assign("mb_message", _tr('Possible file upload attack. Filename') ." :". $_FILES['csvfile']['name']);
        return;
    }

    $pLoadExtension = build_extensionsBatch($smarty);
    if (!$pLoadExtension->loadExtensionsCSV($_FILES['csvfile']['tmp_name'])) {
        $smarty->assign("mb_title", _tr('Error'));
        $smarty->assign("mb_message", $pLoadExtension->errMsg);
        return;
    }
    if (!$pLoadExtension->applyExtensions()) {
        $smarty->assign("mb_title", _tr('Error'));
        $smarty->assign("mb_message", $pLoadExtension->errMsg);
        return;
    }
    $smarty->assign('mb_message', _tr('Total extension updated').": ".$pLoadExtension->getNumBatch()."<br />");
}

function build_extensionsBatch($smarty)
{
    require_once "libs/paloSantoConfig.class.php";

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrAMP  = $pConfig->leer_configuracion(false);

    $dsnAsterisk = $arrAMP['AMPDBENGINE']['valor']."://".
                   $arrAMP['AMPDBUSER']['valor']. ":".
                   $arrAMP['AMPDBPASS']['valor']. "@".
                   $arrAMP['AMPDBHOST']['valor']. "/asterisk";

    $pDB = new paloDB($dsnAsterisk);
    if(!empty($pDB->errMsg)) {
        $smarty->assign("mb_message", _tr('Error when connecting to database')."<br/>".$pDB->errMsg);
        return NULL;
    }

    $pConfig = new paloConfig($arrAMP['ASTETCDIR']['valor'], "asterisk.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrAST  = $pConfig->leer_configuracion(false);

    return new paloSantoExtensionsBatch($pDB, $arrAST, $arrAMP);
}
?>
