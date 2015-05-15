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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:56 gcarrillo Exp $ */

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    
    include_once "libs/paloSantoValidar.class.php";

    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
    
    //user credentials
    global $arrCredentials;
    
    $action = getAction();
    $content = "";
    switch($action){
        case "save":
            $content = editEmailRelay($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
        default:
            $content = reportEmailRelay($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
            break;
    }
    return $content;
}

function reportEmailRelay($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials){
    $contenido='';
    $conf_relay = "/etc/postfix/network_table";
    if(file_exists($conf_relay)) {
        if($fh = @fopen($conf_relay, "r")) {
            while($linea = fgets($fh, 1024)) {
                $contenido .= $linea;
            }
            fclose($fh);
        } else {
            // Si no se puede abrir el archivo se debe mostrar mensaje de error
            $smarty->assign("mb_title",_tr("Error"));
            $smarty->assign("mb_message", _tr("Could not read the relay configuration."));
        }
    } else {
        // Si el archivo no existe algo anda mal.
        $smarty->assign("mb_title",_tr("Error"));
        $smarty->assign("mb_message", _tr("Could not read the relay configuration."));
    } 
    
    global $arrPermission;
    if(in_array('edit',$arrPermission))
        $smarty->assign("EDIT",true);
        
    $relay_msg=_tr("message about email relay");
    $smarty->assign("APPLY_CHANGES",_tr("Apply changes"));
    $smarty->assign("EMAIL_RELAY_MSG",$relay_msg);
    $smarty->assign("RELAY_CONTENT", $contenido);
    $smarty->assign("title",_tr("Networks which can RELAY"));
    $contenidoModulo=$smarty->fetch("file:$local_templates_dir/form_relay.tpl");
    return $contenidoModulo;
}

function editEmailRelay($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials){
    $conf_relay = "/etc/postfix/network_table";
    $in_redes_relay = trim($_POST['redes_relay']);
    $val = new PaloValidar();
    $bGuardar=true;
    
    if(!empty($in_redes_relay)) {
        $arrRedesRelay = array_map('trim', explode("\n", $in_redes_relay));
        // Ahora valido que las redes estén en formato correcto
        if(is_array($arrRedesRelay) and count($arrRedesRelay)>0) {
            foreach ($arrRedesRelay as $redRelay) {
                //validar
                $redRelay = trim($redRelay);
                $val->validar(_tr("Network")." $redRelay", $redRelay, "ip/mask");
            }
        } else {
            $smarty->assign("mb_title",_tr("Error"));
            $smarty->assign("mb_message",_tr("No network entered, you must keep at least the net 127.0.0.1/32"));
            $bGuardar=FALSE;
        }
    } else {
        // El textarea esta vacia
        $bGuardar=FALSE;
        $smarty->assign("mb_title",_tr("Error"));
        $smarty->assign("mb_message",_tr("No network entered, you must keep at least the net 127.0.0.1/32"));
    }

    if($val->existenErroresPrevios()) {
        foreach($val->arrErrores as $nombreVar => $arrVar) {
            $msgErrorVal .= "<b>" . $nombreVar . "</b>: " . $arrVar['mensaje'] . "<br>";

        }
        $smarty->assign("mb_title",_tr("Message"));
        $smarty->assign("mb_message", _tr("Validation Error")."<br><br>$msgErrorVal");
        $bGuardar=FALSE;
    } 
    
    if($bGuardar) {
        // Si no hay errores de validacion entonces ingreso las redes al archivo de relay /etc/postfix/network_table
        $output = $retval = NULL;
        exec('/usr/bin/elastix-helper relayconfig '.            
            implode(' ', array_map('escapeshellarg', $arrRedesRelay)).' 2>&1',
            $output, $retval);
        if ($retval != 0) {
            $smarty->assign(array(
                'mb_title'      =>  _tr('Error'),
                'mb_message'    =>  _tr('Write error when writing the new configuration.').
                    ': '.implode('<br/>', $output),
            ));
        } else {
            $smarty->assign(array(
                'mb_title'      =>  _tr('Message'),
                'mb_message'    =>  _tr('Configuration updated successfully'),
            ));
        }
    }
    return reportEmailRelay($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrCredentials);
}

function getAction()
{
    global $arrPermission;
    if(getParameter("update_relay")){
        return (in_array('edit',$arrPermission))?'save':'view';
    }else{
        return 'view';
    }
}
?>