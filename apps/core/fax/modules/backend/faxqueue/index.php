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
  $Id: index.php,v 1.1 2007/01/09 23:49:36 alex Exp $
*/
//require_once 'libs/paloSantoFax.class.php';
function _moduleContent($smarty, $module_name)
{
    global $arrConf;
    require_once 'libs/paloSantoGrid.class.php';
    require_once 'libs/paloSantoJSON.class.php';
    require_once 'libs/paloSantoFax.class.php';
    
     //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);

    //user credentials
    global $arrCredentials;
    
    //actions
    switch (getParameter('action')) {
        case 'checkqueue':
            return listarColaFax_json($smarty, $module_name, $local_templates_dir);
        case 'list':
        default:
            return listarColaFax_html($smarty, $module_name, $local_templates_dir);
    }
}

function listarColaFax_html($smarty, $module_name, $local_templates_dir)
{
    if (isset($_POST['remove']) && isset($_POST['jobid'])) {
    	$output = $retval = NULL;
        exec('/usr/bin/faxrm '.escapeshellarg($_POST['jobid']).' 2>&1', $output, $retval);
        if ($retval != 0) {
            $smarty->assign(array(
                'mb_title'      =>  _tr('ERROR'),
                'mb_message'    =>  _tr('Failed to remove job').': '.implode('<br\>', $output),
            ));
        }
    }
    
    $listaColaFax = enumerarFaxesPendientes();
    $hash = md5(serialize($listaColaFax));
    $html = listarColaFax_raw($smarty, $module_name, $local_templates_dir, $listaColaFax);
    return '<div id="faxqueuelist">'.$html.'</div>'.
        "<input name=\"outputhash\" id=\"outputhash\" type=\"hidden\" value=\"$hash\" />";
}

function listarColaFax_json($smarty, $module_name, $local_templates_dir)
{
    //TODO: falta ahcer un filtrado de los trabajos pendientes por organizaionc
    //      esto se puede hacer si se identifica a que modem pertence cada trajado
    //      en la cola. Cada trabajo listado tiene un correspondiente archivo
    //      en donde se describe a que modem pertenece. Seria de ller ese archivo
    //      si el trbajo es de envio se encuentra en /var/spool/hylafax/sendq/
    //      si el trabjo es de envio se encuentra en /var/spool/hylafax/recvq/
    //      el nombre del archivo seria qJID donde JID ES DE ID DEL JOB 
    
    session_commit();
    $oldhash = getParameter('outputhash');
    $html = NULL;
    $startTime = time();
    do {
        $listaColaFax = enumerarFaxesPendientes();
        $newhash = md5(serialize($listaColaFax));
        if ($oldhash == $newhash) {
        	usleep(2 * 1000000);
        } else {
            $html = listarColaFax_raw($smarty, $module_name, $local_templates_dir, $listaColaFax);
        }
    } while($oldhash == $newhash && time() - $startTime < 30);

    $jsonObject = new PalosantoJSON();
    $jsonObject->set_status(($oldhash != $newhash) ? 'CHANGED' : 'NOCHANGED');
    $jsonObject->set_message(array('html' => $html, 'outputhash' => $newhash));
    Header('Content-Type: application/json');
    return $jsonObject->createJSON();
}

function listarColaFax_raw($smarty, $module_name, $local_templates_dir, $listaColaFax)
{
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->pagingShow(FALSE);
    $oGrid->setURL('?menu=faxqueue');
    $oGrid->setTitle(_tr('Fax Queue'));
    $oGrid->deleteList('Are you sure to cancel selected jobs?', 'remove', _tr('Cancel job'));
    
    $arrColumns = array(
        '',
        _tr('Job ID'),
        _tr('Priority'),
        _tr('Destination'),
        _tr('Pages'),
        _tr('Retries'),
        _tr('Status'));
    $oGrid->setColumns($arrColumns);
    
    function listarColaFax_toHTML($t)
    {
    	return array(
            '<input type="radio" name="jobid" value="'.$t['jobid'].'"/>',
            $t['jobid'],
            $t['priority'],
            $t['outnum'],
            sprintf(_tr('Sent %d pages of %d'), $t['sentpages'], $t['totalpages']),
            sprintf(_tr('Try %d of %d'), $t['retries'], $t['totalretries']),
            '['.$t['state'].'] '._tr($t['status']),
        );
    }    
    $oGrid->setData(array_map('listarColaFax_toHTML', $listaColaFax));
    return $oGrid->fetchGrid();
}

/* Enumerar los faxes pendientes de enviar como una estructura
[root@elx2 ~]# faxstat -s -d
HylaFAX scheduler on localhost: Running
Modem ttyIAX1 (): Running and idle
Modem ttyIAX2 (): Running and idle

JID  Pri S  Owner Number       Pages Dials     TTS Status
28   125 S asteri 1099          0:1   2:12   17:27 Busy signal detected
 */
function enumerarFaxesPendientes()
{
    $faxstatus = paloFax::getFaxStatus();
    $jobs = array();
    foreach ($faxstatus['jobs'] as $k => $t) 
        if (!in_array($t['state'], array('F', 'D'))) $jobs[$k] = $t;
    return $jobs;
}

?>