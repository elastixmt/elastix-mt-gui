<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.4-2                                               |
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
  $Id: index.php,v 1.1 2008-09-11 03:09:47 Alex Villacis Lasso <a_villacis@palosanto.com> Exp $ */

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoDB.class.php";
include_once "libs/paloSantoACL.class.php";


function _moduleContent(&$smarty, $module_name)
{
   

    global $arrConf;

    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    $pDB = new paloDB($arrConf['dsn_conn_database']);

    switch (getParameter('action')) {
    case 'new':
    case 'edit':
        return addRemovePortsUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    default:
        return listPortKnockUsers($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
}

function listPortKnockUsers(&$smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pACL = new paloACL($arrConf['elastix_dsn']['acl']);
    $pk = new paloSantoPortKnockUsers($pDB);

    // Manejar la operación de borrar todas las autorizaciones de un usuario
    if (isset($_POST['delete']) && isset($_POST['id_user'])) {
    	$r = $pk->deleteUserAuthorizations($_POST['id_user']);
        if (!$r) {
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", $pk->errMsg);
        } else {
            
            $pr = new paloSantoRules($pDB);
            $pr->activateRules();

            $smarty->assign("mb_title", _tr("Message"));
            $smarty->assign("mb_message", _tr("Revocation successful"));
        }
    }

    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr('PortKnock Users'));
    $oGrid->setColumns(array('', _tr('User'), _tr('Authorized ports'), _tr('Options')));
    $oGrid->deleteList(
        _tr('Are you sure you wish to revoke the user authorizations?'),
        'delete', _tr('Revoke authorizations'));
    $oGrid->addNew("?menu=$module_name&amp;action=new", _tr('Authorize new user'), true);

    // Construcción de la vista de usuarios autorizados
    $data = array();
    $recordset = $pk->listAuthorizedUsers();
    if (is_array($recordset)) {
    	foreach ($recordset as $id_user => $auths) {
    		$userinfo = $pACL->getUsers($id_user);
            $protocols = array();
            foreach ($auths as $a) { $protocols[] = $a['name']; }
            $data[] = array(
                '<input type="radio" name="id_user" value="'.$id_user.'" />',
                $userinfo[0][1],    // Nombre de login del usuario
                implode(' ', $protocols),
                "<a href=\"?menu=$module_name&amp;action=edit&amp;id_user=$id_user\">["._tr('Add/Remove Ports')."]</a>"
            );
    	}
    }
    
    
    $oGrid->pagingShow(false);
    $url = array(
        "menu"         =>  $module_name,
    );    
    $oGrid->setURL($url);
    $oGrid->setData($data);
    return $oGrid->fetchGrid();
}

function addRemovePortsUser($smarty, $module_name, $local_templates_dir, $pDB, $arrConf)
{
    // Listar los usuarios y preparar el combo de usuarios disponibles
    $pACL = new paloACL($arrConf['elastix_dsn']['acl']);
    $id_user = getParameter('id_user');
    $userlist = $pACL->getUsers();
    $cbo_users = array();
    foreach ($userlist as $userinfo) {
    	$cbo_users[$userinfo[0]] = $userinfo[1].' - '.$userinfo[2];
    }
    
    // Verificar si el usuario existe
    if (!is_null($id_user)) {
    	if (!isset($cbo_users[$id_user])) {
            Header("Location: ?menu=$module_name");
    		return NULL;
    	}
    } else {
    	$id_user = $userlist[0][0];
    }
    
    $ps = new paloSantoPortService($pDB);
    $pk = new paloSantoPortKnockUsers($pDB);

    // Construir lista de puertos autorizados
    $userauth = $pk->listAuthorizationsForUser($id_user);
    $portauths = array();
    if (is_array($userauth)) foreach ($userauth as $auth) {
        $portauths[$auth['id_port']] = $auth['id'];
    }

    $portlist = $ps->ObtainPuertos($ps->ObtainNumPuertos('', ''), 0, '', '');
    $listaIdPuertos = array();
    foreach ($portlist as $portinfo) $listaIdPuertos[] = $portinfo['id'];
    
    if (isset($_POST['apply']) && is_array($_POST['auth_port'])) {
    	// Se requiere aplicar lista de cambios
        $listaNuevosPuertos = array_keys($_POST['auth_port']);
        $bReglasBorradas = FALSE;
        
        // Borrar la autorización de todos los puertos que ya no aparecen
        $bExito = TRUE;
        foreach ($portauths as $id_port => $id_auth) {
        	if (!in_array($id_port, $listaNuevosPuertos)) {
        		if (!$pk->deleteAuthorization($id_auth)) {
                    $smarty->assign("mb_title", _tr("ERROR"));
                    $smarty->assign("mb_message", $pk->errMsg);
        			$bExito = FALSE;
                    break;
        		} else {
        			unset($portauths[$id_port]);
                    $bReglasBorradas = TRUE;
        		}
        	}
        }
        if (!$bExito) break;
        
        // Ingresar la autorización de los puertos nuevos
        foreach ($listaNuevosPuertos as $id_port) {
        	if (in_array($id_port, $listaIdPuertos) && !isset($portauths[$id_port])) {
        		$id_nueva_auth = $pk->insertAuthorization($id_user, $id_port);
                if (is_null($id_nueva_auth)) {
                    $smarty->assign("mb_title", _tr("ERROR"));
                    $smarty->assign("mb_message", $pk->errMsg);
                    $bExito = FALSE;
                    break;
        		} else {
        			$portauths[$id_port] = $id_nueva_auth;
        		}
        	}
        }
        
        if ($bExito) {
            if ($bReglasBorradas) {
                // Ejecutar iptables para revocar las reglas del usuario
                require_once "apps/sec_rules/libs/paloSantoRules.class.php";
                $pr = new paloSantoRules($pDB);
                $pr->activateRules();
            }
        	Header("Location: ?menu=$module_name");
            return NULL;
        }
    }
    
    $data = array();
    if (is_array($portlist)) {        
        foreach($portlist as $portinfo){
            $id_port = $portinfo['id'];
            
            $protocol_details = '';
            switch ($portinfo['protocol']) {
            case 'TCP':
            case 'UDP':
                $protocol_details = ((stripos($portinfo['details'], ':') === false ) 
                    ? _tr('Port') : _tr('Ports')).
                    ' '.$portinfo['details'];
                break;
            case 'ICMP':
                $arr = explode(':', $portinfo['details']);
                if(isset($arr[1]))
                    $protocol_details = _tr('Type').": ".$arr[0]." "._tr('Code').": ".$arr[1];
                break;
            default:
                $protocol_details = _tr('Protocol Number').': '.$portinfo['details'];
                break;
            }
            $data[] = array(
                "<input type=\"checkbox\" name=\"auth_port[$id_port]\" ".
                    (isset($portauths[$id_port]) ? 'checked="checked"' : '').' />',
                htmlentities($portinfo['name'], ENT_COMPAT, 'UTF-8'),
                htmlentities($portinfo['protocol'], ENT_COMPAT, 'UTF-8'),
                $protocol_details,
            );
        }
    }

    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr('Add/remove ports for user'));
    $oGrid->setColumns(array('', _tr('Port'), _tr('Protocol'), _tr('Details')));
    $oGrid->addSubmitAction('apply', _tr('Apply changes'), "apps/web/$module_name/images/Check.png");
    $oGrid->addComboAction('id_user', _tr('User'), $cbo_users, $id_user, 'refresh', 'submit();');
	
    // Construcción de la vista de puertos autorizados
    $oGrid->pagingShow(false);
    $url = array(
        "menu"         =>  $module_name,
    );    
    $oGrid->setURL($url);
    $oGrid->setData($data);
    return $oGrid->fetchGrid();
}
?>
