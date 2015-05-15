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
require_once "libs/paloSantoNetwork.class.php";

function _moduleContent(&$smarty, $module_name)
{
    
    global $arrConf;

    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    $pDB = new paloDB($arrConf['dsn_conn_database']);

    switch (getParameter('action')) {
    case 'setport':
        return setPortKnockPort($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    case 'viewauths':
        return listPortKnockCurrentAuths($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    default:
        return listPortKnockInterfaces($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
}

function listPortKnockInterfaces(&$smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pk = new paloSantoPortKnockInterfaces($pDB);
    
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr('PortKnock Protected Interfaces'));
    $oGrid->setColumns(array('', _tr('Interface'), _tr('Protection'), _tr('Port to Knock'), _tr('Authorizations'), _tr('Options')));
    $oGrid->deleteList(
        _tr('Are you sure you wish to unprotect the interface?'),
        'unprotect', _tr('Unprotect interface'));

    $listaIf = PaloNetwork::obtener_interfases_red_fisicas();
    $recordset = $pk->listProtectedInterfaces();

    // Eliminar la protección de una interfaz específica
    if (isset($_POST['unprotect']) && isset($_POST['eth_in']) && 
        isset($recordset[$_POST['eth_in']])) {
    	$bExito = $pk->removeProtectedInterface($_POST['eth_in']);
        if (!$bExito) {
            $smarty->assign("mb_title", _tr("ERROR"));
            $smarty->assign("mb_message", $pk->errMsg);
        }
        $recordset = $pk->listProtectedInterfaces();
    }

    $data = array();
    if (is_array($recordset)) {
        foreach (array_keys($listaIf) as $eth_in) {
        	if (!isset($recordset[$eth_in])) {
        		$recordset[$eth_in] = array(
                    'eth_in'    => $eth_in,
                    'udp_port'  => NULL,
                    'num_auth'  => 0,
                );
        	}
        }
        ksort($recordset);
    	foreach ($recordset as $eth_in => $eth_info) {
    		$data[] = array(
                is_null($eth_info['udp_port']) ? '' : '<input type="radio" name="eth_in" value="'.$eth_in.'" />',
                $eth_in,
                is_null($eth_info['udp_port']) ? _tr('Inactive') : _tr('Active'),
                "<a href=\"?menu=$module_name&amp;action=setport&amp;eth_in=$eth_in\">".(is_null($eth_info['udp_port']) ? '['._tr('Enable Protection').']' : $eth_info['udp_port']).'</a>',
                ($eth_info['num_auth'] > 0) ? $eth_info['num_auth'] : '-',
                ($eth_info['num_auth'] > 0) ? "<a href=\"?menu=$module_name&amp;action=viewauths&amp;eth_in=$eth_in\">["._tr('View authorizations').']</a>': '-',
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

function setPortKnockPort(&$smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    if (isset($_POST['cancel'])) {
        Header("Location: ?menu=$module_name");
    	return NULL;
    }

    $listaIf = PaloNetwork::obtener_interfases_red_fisicas();
    $cbo_eth = array();
    foreach ($listaIf as $eth_in => $eth_info) {
    	$cbo_eth[$eth_in] = $eth_info['Name'].' - '.$eth_info['Inet Addr'];
    }

    $requested_eth_in = getParameter('eth_in');
    if (!is_null($requested_eth_in) && !isset($cbo_eth[$requested_eth_in]))
        $requested_eth_in = NULL;
    $_POST['eth_in'] = $requested_eth_in;

    $pk = new paloSantoPortKnockInterfaces($pDB);
    $recordset = $pk->listProtectedInterfaces();
    if (!isset($_POST['port'])) {
    	if (!is_null($requested_eth_in) && isset($recordset[$requested_eth_in])) {
    		$_POST['port'] = $recordset[$requested_eth_in]['udp_port'];
    	} else {
    		// Generar un número aleatorio entre 32768 y 65535
            $bColision = FALSE;
            $_POST['port'] = rand(32768, 65535);
    	}
    }
    
	$camposFormulario = array(
        "eth_in"    =>  array(
            "LABEL"                 => _tr("Interface"),
            "REQUIRED"              => "yes",
            "INPUT_TYPE"            => "SELECT",
            "INPUT_EXTRA_PARAM"     => $cbo_eth,
            "VALIDATION_TYPE"       => "text",
            "VALIDATION_EXTRA_PARAM"=> ""
        ),
        "port"      => array(
            "LABEL"                  => _tr("Port"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => array("style" => "width:76px"),
            "VALIDATION_TYPE"        => "numeric",
            "VALIDATION_EXTRA_PARAM" => ""
        ),
    );
    $oForm = new paloForm($smarty, $camposFormulario);
    $smarty->assign(array(
        'MODULE_NAME'   =>  $module_name,
        'CANCEL'        =>  _tr('Cancel'),
        'SAVE'          =>  _tr('Save'),
        'REQUIRED_FIELD'=>  _tr('Required field'),
        'icon'          =>  '../web/_common/images/list.png',
    ));

    // Guardar los cambios
    if (isset($_POST['save'])) {
        if (!$oForm->validateForm($_POST)) {
            // Falla la validación básica del formulario
            $strErrorMsg = "<b>"._tr('The following fields contain errors').":</b><br/>";
            $arrErrores = $oForm->arrErroresValidacion;
            if (is_array($arrErrores) && count($arrErrores) > 0) {
                foreach($arrErrores as $k=>$v) {
                    $strErrorMsg .= "$k: [$v[mensaje]] <br /> ";
                }
            }
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", $strErrorMsg);
        } elseif ($_POST['port'] > 65535) {
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr('Port must be in range 0-65535'));
        } elseif (!in_array($_POST['eth_in'], array_keys($listaIf))) {
            $smarty->assign("mb_title", _tr("Validation Error"));
            $smarty->assign("mb_message", _tr('Invalid interface'));
        } else {
        	$bExito = $pk->setProtectedInterfacePort($_POST['eth_in'], $_POST['port']);
            if (!$bExito) {
                $smarty->assign("mb_title", _tr("Error"));
                $smarty->assign("mb_message", $pk->errMsg);
            } else {
            	Header("Location: ?menu=$module_name");
                return NULL;
            }
        }
    }

    return $oForm->fetchForm("$local_templates_dir/form.tpl", _tr('Assign Knocking Port'), $_POST);
}

function listPortKnockCurrentAuths(&$smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $eth_in = getParameter('eth_in');
    if (is_null($eth_in)) {
        Header("Location: ?menu=$module_name");
        return NULL;
    }

    include_once "libs/paloSantoACL.class.php";

    $pACL = new paloACL($arrConf['elastix_dsn']['acl']);
    $pk = new paloSantoPortKnockInterfaces($pDB);
    
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr('PortKnock Interface Authorizations'));
    $oGrid->setColumns(array('', _tr('IP'), _tr('User'), _tr('Ports'), _tr('Since')));
    $oGrid->deleteList(
        _tr('Are you sure you wish to revoke this authorization?'),
        'delete', _tr('Revoke authorization'));
    
    if (isset($_POST['delete']) && isset($_POST['id_user_ip'])) {
    	$l = explode('-', $_POST['id_user_ip']);
        if (count($l) >= 2) {
        	$bExito = $pk->removeAuthorizationsUserInterface($l[0], $l[1]);
            if (!$bExito) {
                $smarty->assign("mb_title", _tr("Error"));
                $smarty->assign("mb_message", $pk->errMsg);
            } else {
                Header("Location: ?menu=$module_name");
                return NULL;
            }
        }
    }

    $recordset = $pk->listAuthorizationsInterface($eth_in);

    $data = array();
    if (is_array($recordset)) {
    	foreach ($recordset as $id_user => $auth_user) {
            $userinfo = $pACL->getUsers($id_user);
    		foreach ($auth_user as $ip_source => $auth_ips) {
    			$listaProto = array();
                $ruleStart = NULL;
                foreach ($auth_ips as $id_auth => $info_auth) {
    				$listaProto[] = $info_auth['name'];
                    $ruleStart = $info_auth['rule_start'];
    			}
                $data[] = array(
                    '<input type="radio" name="id_user_ip" value="'.$id_user.'-'.$ip_source.'" />',
                    $ip_source,
                    $userinfo[0][1],
                    implode(' ', $listaProto),
                    $ruleStart,
                );
    		}
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
?>
