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
  $Id: packages.php $ */

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoJSON.class.php";

function _moduleContent(&$smarty, $module_name)
{ 
    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    $action = getParameter('action');
    switch ($action) {
    case 'updateRepositories':
        return actualizarRepositorios($arrConf);
    case 'install':
        return installPaquete($arrConf);
    case 'uninstall':
        return uninstallPaquete($arrConf);
    default:
        return listPackages($smarty, $module_name, $local_templates_dir, $arrConf);
    }
}

function listPackages($smarty, $module_name, $local_templates_dir, $arrConf)
{
    $oPackages = new PaloSantoPackages($arrConf['ruta_yum']);
    
    $submitInstalado = getParameter('submitInstalado');
    $nombre_paquete = getParameter('nombre_paquete');

    $smarty->assign(array(
        'module_name'           =>  $module_name,
        'RepositoriesUpdate'    =>  _tr('Repositories Update'),
        'Search'                =>  _tr('Search'),
        'UpdatingRepositories'  =>  _tr('Updating Repositories'),
        'InstallPackage'        =>  _tr('Installing Package'),
        'UpdatePackage'         =>  _tr('Updating Package'),
        'accionEnProceso'       =>  _tr('There is an action in process'),
        'msgConfirmDelete'      =>  _tr('You will uninstall package along with everything what it depends on it. System can lose important functionalities or become unstable! Are you sure want to Uninstall?'),
        'msgConfirmInstall'     =>  _tr('Are you sure want to Install this package?'),
        'UninstallPackage'      =>  _tr('Uninstalling Package'),
        'msgConfirmUpdate'      =>  _tr('Are you sure want to Update this package?'),
    ));
    
    $arrPaquetes = $oPackages->listarPaquetes(
        ($submitInstalado == 'all') ? 'all' : 'installed',
        $nombre_paquete);        
    
    if ($oPackages->bActualizar) {
        $smarty->assign("mb_title",_tr("Message"));
        $smarty->assign("mb_message",_tr("The repositories are not up to date. Click on the")." <b>\""._tr('Repositories Update')."\"</b> "._tr("button to list all available packages."));
    }

    // Pagination
    $limit = 20;
    $total = count($arrPaquetes);
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = $oGrid->getEnd();

    $arrPaquetes = array_slice($arrPaquetes, $offset, $limit);

    $arrData = array();
    foreach ($arrPaquetes as $paquete) {
        $packageActions = array();
        $tmpPaquete = $paquete['name'].'.'.$paquete['arch'];
        if ($paquete['canupdate']) {
        	$packageActions[] = "<a href='#'  onclick="."confirmUpdate('$tmpPaquete')".">["._tr('Update')."]</a>";
        }
        if (is_null($paquete['version'])) {
        	$packageActions[] = "<a href='#'  onclick="."installaPackage('$tmpPaquete',0)".">["._tr('Install')."]</a>";
        } else {
        	$packageActions[] = "<a href='#'  onclick="."confirmDelete('$tmpPaquete')".">["._tr('Uninstall')."]</a>";
        }        
        $rowData = array(
            $paquete['name'],
            $paquete['arch'],
            $paquete['summary'],
            is_null($paquete['version']) ? _tr('(not installed)') : $paquete['version'].'-'.$paquete['release'],
            is_null($paquete['latestversion']) ? _tr('(not available)') : $paquete['latestversion'].'-'.$paquete['latestrelease'],
            $paquete['repo'],
            implode('&nbsp;', $packageActions),
        );
        if ($paquete['canupdate']) {
        	$rowData[0] = '<b>'.$rowData[0].'</b>';
            $rowData[4] = '<b>'.$rowData[4].'</b>';
        }
        $arrData[] = $rowData;
    }

    $url = array(
        'menu'              =>  $module_name,
        'submitInstalado'   =>  $submitInstalado,
        'nombre_paquete'    =>  $nombre_paquete,
    );
    $arrGrid = array(
        "title"    => _tr('Packages'),
        "icon"     => "web/apps/$module_name/images/system_updates_packages.png",
        "width"    => "99%",
        "start"    => ($total==0) ? 0 : $offset + 1,
        "end"      => $end,
        "total"    => $total,
        "url"      => $url,
        "columns"  => array(
            array("name"    => _tr("Package Name")),
            array("name"    => _tr("Architecture")),
            array("name"    => _tr("Package Info")),
            //array("name"    => _tr("Package Version")." / "._tr("Package Release")),
            array('name'    =>  _tr('Current Version')),
            array('name'    =>  _tr('Available Version')),
            array("name"    => _tr("Repositor Place")),
            array("name"    => _tr("Status")),
        )
    );

    /*Inicio Parte del Filtro*/
    $arrFilter = filterField();
    $oFilterForm = new paloForm($smarty, $arrFilter);

    if (getParameter('submitInstalado')=='all') {
        $arrFilter["submitInstalado"] = 'all';
        $tipoPaquete = _tr('All Package');
    } else {
        $arrFilter["submitInstalado"] = 'installed';
        $tipoPaquete = _tr('Package Installed');
    }
    $arrFilter["nombre_paquete"] = $nombre_paquete;

    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Status")." =  $tipoPaquete",
        $arrFilter, array("submitInstalado" => "installed"),true);
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Name")." = $nombre_paquete",
        $arrFilter, array("nombre_paquete" => ""));
    $oGrid->addButtonAction('update_repositorios', _tr('Repositories Update'),
        null, 'mostrarReloj()');
    $oGrid->showFilter($oFilterForm->fetchForm("$local_templates_dir/new.tpl",
        '', $arrFilter));
    return $oGrid->fetchGrid($arrGrid, $arrData);
}

function filterField()
{
    $arrPackages = array(
        "all"       =>  _tr('All Package'),
        "installed" =>  _tr('Package Installed')
    );

    $arrFilter = array(
        "nombre_paquete" => array(
            "LABEL"                  => _tr("Name"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
        "submitInstalado"   => array(
            "LABEL"                  => _tr("Status"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $arrPackages,
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => "",
            "ONCHANGE"               => "javascript:submit()"),
    );
    return $arrFilter;
}

function actualizarRepositorios($arrConf)
{
    $oPackages = new PaloSantoPackages($arrConf['ruta_yum']);
    $resultado = $oPackages->checkUpdate();
    
    $jsonObject = new PaloSantoJSON();
    $jsonObject->set_status($resultado);
    return $jsonObject->createJSON();
}

function installPaquete($arrConf)
{
    $oPackages = new PaloSantoPackages($arrConf['ruta_yum']);
    $paquete = getParameter("paquete");
    $val  = getParameter("val");
    $resultado = $oPackages->installPackage($paquete,$val);

    $jsonObject = new PaloSantoJSON();
    $jsonObject->set_status($resultado);
    return $jsonObject->createJSON();
}

function uninstallPaquete($arrConf)
{
    $oPackages = new PaloSantoPackages($arrConf['ruta_yum']);
    $paquete = getParameter("paquete");
    $resultado = $oPackages->uninstallPackage($paquete);

    $jsonObject = new PaloSantoJSON();
    $jsonObject->set_status($resultado);
    return $jsonObject->createJSON();
}

?>
