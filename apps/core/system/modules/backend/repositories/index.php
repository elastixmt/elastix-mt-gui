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
  $Id: repositories.php $ */

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);
    
    $contenidoModulo = listRepositories($smarty, $module_name, $local_templates_dir,$arrConf);

    return $contenidoModulo;
}

function listRepositories($smarty, $module_name, $local_templates_dir,$arrConf) {

    $oRepositories = new PaloSantoRepositories();
    $arrReposActivos=array();
    $typeRepository = getParameter("typeRepository");
    if(isset($_POST['submit_aceptar'])){
        foreach($_POST as $key => $value){
            if(substr($key,0,5)=='repo-')
                $arrReposActivos[]=substr($key,5);
        }
        $oRepositories->setRepositorios($arrConf['ruta_repos'],$arrReposActivos,$typeRepository,$arrConf["main_repos"]);
    }

    $option["main"]   = "";
    $option["others"] = "";
    $option["all"]    = "";

    $arrRepositorios = $oRepositories->getRepositorios($arrConf['ruta_repos'],$typeRepository,$arrConf["main_repos"]);
    $limit  = 40;
    $total  = count($arrRepositorios);
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = $oGrid->getEnd();
    $arrData = array();
    $version = $oRepositories->obtenerVersionDistro();
    $arch = $oRepositories->obtenerArquitectura();
 
    if (is_array($arrRepositorios)) {
        for($i=$offset;$i<$end;$i++){
            $activo = "";
            if($arrRepositorios[$i]['activo'])
                $activo="checked='checked'";
             $arrData[] = array(
                            "<input $activo name='repo-".$arrRepositorios[$i]['id']."' type='checkbox' id='repo-$i' />",$valor = str_replace(array("\$releasever","\$basearch"),array($version,$arch),$arrRepositorios[$i]['name']),);
        }
    }

    if(isset($typeRepository)){
        $oGrid->setURL("?menu=$module_name&typeRepository=$typeRepository");
        $_POST["typeRepository"]=$typeRepository;
    }else{
        $oGrid->setURL("?menu=$module_name");
        $_POST["typeRepository"]="main";
    }

    $arrGrid = array("title"    => _tr("Repositories"),
        "icon"     => "web/apps/$module_name/images/system_updates_repositories.png",
        "width"    => "99%",
        "start"    => ($total==0) ? 0 : $offset + 1,
        "end"      => $end,
        "total"    => $total,
        "columns"  => array(0 => array("name"      => _tr("Active"),
                                       "property1" => ""),
                            1 => array("name"      => _tr("Name"),
                                       "property1" => "")));

    $oGrid->customAction('submit_aceptar',_tr('Save/Update'));
    $oGrid->addButtonAction("default",_tr('Default'),null,"defaultValues($total,'$version','$arch')");
    $FilterForm = new paloForm($smarty,createFilter());

    $arrOpt = array("main"=>_tr('Main'),"others"=>_tr('Others'),"all"=>_tr('All'));
    if(isset($arrOpt[$typeRepository])){
        $valorfiltro = $arrOpt[$typeRepository];
    }else
        $valorfiltro = _tr('Main');

    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Repo")." = ".$valorfiltro, $_POST, array("typeRepository" => "main"),true);

    $htmlFilter = $FilterForm->fetchForm("$local_templates_dir/new.tpl","",$_POST);
    $oGrid->showFilter($htmlFilter);

    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData);
    return $contenidoModulo;
}

function createFilter(){
    $arrOpt = array("main"=>_tr('Main'),"others"=>_tr('Others'),"all"=>_tr('All'));
        $arrFields = array(
            "typeRepository"   => array(      "LABEL"                  => _tr("Repo"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOpt,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "ONCHANGE"               => "javascript:submit()"),
            );
        return $arrFields;
}
?>

