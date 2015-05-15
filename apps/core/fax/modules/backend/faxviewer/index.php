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
  $Id: index.php,v 1.2 2007/09/07 01:18:43 gcarrillo Exp $ */
function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    require_once 'libs/paloSantoForm.class.php';
    require_once 'libs/paloSantoFaxVisor.class.php';
    require_once 'libs/paloSantoDB.class.php';
    require_once 'libs/paloSantoGrid.class.php';
    
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);

    //user credentials
    global $arrCredentials;
    switch (getAction()) {
        case 'edit':
            return actualizarFax($smarty, $module_name, $local_templates_dir, $pDB, $arrCredentials);
        case 'download_faxFile':
            return download_faxFile($pDB, $arrCredentials);
        case 'delete':
            return delete_faxFile($smarty, $module_name, $local_templates_dir, $pDB, $arrCredentials);
        default:
            return listarFaxes($smarty, $module_name, $local_templates_dir, $pDB, $arrCredentials);
    }
}

function listarFaxes(&$smarty, $module_name, $local_templates_dir, $pDB, $credentials)
{
    global $arrPermission;
    $pORGZ=new paloSantoOrganization($pDB);
    
    $smarty->assign(array(
        'SEARCH'    =>  _tr('Search'),
    ));
    $smarty->assign('USERLEVEL',$credentials['userlevel']);
    $arrOrgz=array(0=>"all");
    $organization=getParameter('organization');
    if($credentials['userlevel']=='superadmin'){
        if(empty($organization))
            $organization=0;
        if($pORGZ->getNumOrganization(array()) > 0){
            foreach(($pORGZ->getOrganization(array())) as $value){
                $arrOrgz[$value["id"]]=$value["name"];
            }
        }
    }else{
        $tmpOrg=$pORGZ->getOrganizationById($credentials['id_organization']);
        $arrOrgz[$tmpOrg["id"]]=$tmpOrg['name'];
        $organization=$credentials['id_organization'];
    }
    
    $oFax = new paloFaxVisor($pDB);
    
    // Generación del filtro
    $oFilterForm = new paloForm($smarty, getFormElements($arrOrgz));
    
    // Parámetros base y validación de parámetros
    $url = array('menu' => $module_name);    
    $paramFiltroBase = $paramFiltro = array(
        'name_company'  =>  '',
        'fax_company'   =>  '',
        'date_fax'      =>  NULL,
        'filter'        =>  'All',
    );
    foreach (array_keys($paramFiltro) as $k) {
        if(!is_null(getParameter($k))){
            $paramFiltro[$k] = getParameter($k); 
        }
    }

    $oGrid  = new paloSantoGrid($smarty);
    $arrType = array("All"=>_tr('All'),"In"=>_tr('in'),"Out"=>_tr('out'));
    if($credentials['userlevel']=='superadmin'){
        $_POST["organization"]=$organization;
        $oGrid->addFilterControl(_tr("Filter applied ")._tr("Organization")." = ".$arrOrgz[$organization], $_POST, array("organization" => 0),true); //organization
    }
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Company Name")." = ".$paramFiltro['name_company'], $paramFiltro, array("name_company" => ""));
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Company Fax")." = ".$paramFiltro['fax_company'], $paramFiltro, array("fax_company" => ""));
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Fax Date")." = ".$paramFiltro['date_fax'], $paramFiltro, array("date_fax" => NULL));
    $oGrid->addFilterControl(_tr("Filter applied ")._tr("Type Fax")." = ".$arrType[$paramFiltro['filter']], $paramFiltro, array("filter" => "All"),true);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $paramFiltro);

    if (!$oFilterForm->validateForm($paramFiltro)) {
        $smarty->assign(array(
            'mb_title'      =>  _tr('Validation Error'),
            'mb_message'    =>  '<b>'._tr('The following fields contain errors').':</b><br/>'.
                                implode(', ', array_keys($oFilterForm->arrErroresValidacion)),
        ));
        $paramFiltro = $paramFiltroBase;
    }

    $url = array_merge($url, $paramFiltro);

    $oGrid->setTitle(_tr("Fax Viewer"));
    $oGrid->setIcon("web/apps/$module_name/images/kfaxview.png");
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->setURL($url);
	
    $arrData = NULL;
    if($organization==0){
        $total = $oFax->obtener_cantidad_faxes(null,$paramFiltro['name_company'],
            $paramFiltro['fax_company'], $paramFiltro['date_fax'],
            $paramFiltro['filter']);
    }else{
        $total = $oFax->obtener_cantidad_faxes($organization,$paramFiltro['name_company'],
            $paramFiltro['fax_company'], $paramFiltro['date_fax'],
            $paramFiltro['filter']);
    }
    if($total===false){
        $total=0;
        $smarty->assign(array(
            'mb_title'      =>  _tr('ERROR'),
            'mb_message'    =>  $oFax->errMsg,
        ));
    }
    
    $delete=in_array('delete_fax',$arrPermission);
    $edit=in_array('edit_fax',$arrPermission);
    
    $limit = 20;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    if($delete)
        $columns[]="<input type='checkbox' class='checkall'/>";
    if($credentials['userlevel']=='superadmin'){
        $columns[]=_tr('Organization');
    }
    $columns[]=_tr('Type');
    $columns[]=_tr('File');
    $columns[]=_tr('Fax Cid Name');
    $columns[]=_tr('Fax Cid Number');
    $columns[]=_tr('Fax Destiny');
    $columns[]=_tr('Fax Date');
    $columns[]=_tr('Status');
    if($edit)
        $columns[]=_tr('Options');
    
    $oGrid->setColumns($columns);
        
    if($total>0){
        if($organization==0){
            $arrResult = $oFax->obtener_faxes(null,$paramFiltro['name_company'],
                $paramFiltro['fax_company'], $paramFiltro['date_fax'], $offset, $limit,
                $paramFiltro['filter']);
        }else{
            $arrResult = $oFax->obtener_faxes($organization,$paramFiltro['name_company'],
                $paramFiltro['fax_company'], $paramFiltro['date_fax'], $offset, $limit,
                $paramFiltro['filter']);
        }
        
        if (!is_array($arrResult)) {
            $smarty->assign(array(
                'mb_title'      =>  _tr('ERROR'),
                'mb_message'    =>  $oFax->errMsg,
            ));
        }else{
            foreach ($arrResult as $fax) {
                foreach (array('pdf_file', 'company_name', 'company_fax', 'destiny_name', 'destiny_fax') as $k)
                    $fax[$k] = htmlentities($fax[$k], ENT_COMPAT, 'UTF-8');
                $doc = explode(".",$fax['pdf_file']);
                $iddoc = $doc[0];
                $arrTmp=array();
                if($delete)
                    $arrTmp[]='<input type="checkbox" name="faxes[]" value="'.$fax['id'].'" />';
                if($credentials['userlevel']=='superadmin')
                    $arrTmp[]='ttt';//$arrOrg[$fax['id_organization']];
                $arrTmp[]=_tr($fax['type']);
                $arrTmp[]=(strtolower($fax['type']) == 'in' || strpos($fax['pdf_file'], '.pdf') !== FALSE) 
                        ? "<a href='?menu=$module_name&action=download&id=".$fax['id']."&rawmode=yes'>".$fax['pdf_file']."</a>" 
                        : $fax['pdf_file'];
                $arrTmp[]=$fax['company_name'];
                $arrTmp[]=$fax['company_fax'];
                $arrTmp[]=$fax['destiny_name']." - ".$fax['destiny_fax'];
                $arrTmp[]=$fax['date'];
                $arrTmp[]=_tr($fax['status']).(empty($fax['errormsg']) ? '' : ': '.$fax['errormsg']);
                if($edit)
                    $arrTmp[]="<a href='?menu=$module_name&action=edit&id=".$fax['id']."'>"._tr('Edit')."</a>";
                $arrData[]=$arrTmp;
            }
        }
    }

    $oGrid->setData($arrData);
    if($delete)
        $oGrid->deleteList(_tr('Are you sure you wish to delete fax (es)?'),"faxes_delete",_tr("Delete"));
    $oGrid->showFilter($htmlFilter);
    return $oGrid->fetchGrid();
}

function actualizarFax($smarty, $module_name, $local_templates_dir,  $pDB, $credentials)
{
    $smarty->assign(array(
        'CANCEL'            =>  _tr('Cancel'),
        'APPLY_CHANGES'     =>  _tr('Apply changes'),
        'REQUIRED_FIELD'    =>  _tr('Required field'),
    ));
    $idFax = getParameter('id');
    if (isset($_POST['cancel']) || !ctype_digit("$idFax")) {
        header("Location: ?menu=$module_name");
        return;
    }

    $oFax = new paloFaxVisor($pDB);
    if(empty($idFax)){
        $smarty->assign("mb_title", _tr('ERROR'));
        $smarty->assign("mb_message", _tr('Invalid Fax'));
        return listarFaxes($smarty, $module_name, $local_templates_dir, $pDB, $credentials); 
    }
    
    if($credentials['userlevel']!='superadmin'){
        if(!$oFax->fax_bellowOrganization($idFax,$credentials['id_organization'])){
            $smarty->assign("mb_title", _tr('ERROR').":");
            $smarty->assign("mb_message", _tr("Invalid Fax"));
            return listarFaxes($smarty, $module_name, $local_templates_dir,$pDB, $credentials);
        }
    }
    
    if (isset($_POST['save'])) {
        if (!$oFax->updateInfoFaxFromDB($idFax, $_POST['name_company'], $_POST['fax_company'])) {
            $smarty->assign("mb_title", _tr('ERROR'));
            $smarty->assign("mb_message", _tr($oFax->errMsg));
        }else{
            $smarty->assign("mb_title", _tr('MESSAGE'));
            $smarty->assign("mb_message", _tr("Changes were applied successfully"));
            return listarFaxes($smarty, $module_name, $local_templates_dir,$pDB, $credentials);
        }
    }
    
    $smarty->assign("id_fax", $idFax);
    $arrDataFax = $oFax->obtener_fax($idFax);
    if (is_array($arrDataFax) && count($arrDataFax) > 0) {
        if (!isset($_POST['name_company'])) $_POST['name_company'] = $arrDataFax['company_name'];
        if (!isset($_POST['fax_company'])) $_POST['fax_company'] = $arrDataFax['company_fax'];
    }else{
        $smarty->assign("mb_title", _tr('ERROR'));
        $smarty->assign("mb_message", _tr('Fax does not exist'));
        return listarFaxes($smarty, $module_name, $local_templates_dir,$pDB, $credentials);
    }
    
    $oForm = new paloForm($smarty, getFormElements(array()));
    $htmlForm = $oForm->fetchForm("$local_templates_dir/edit.tpl", _tr('Edit'), $_POST);
    return "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name&action=edit'>".$htmlForm."</form>";
}

function getFormElements($arrOrg)
{
    return array(
        "organization"=> array(
            "LABEL"                  => _tr('Organization'),
             "REQUIRED"               => "NO",
             "INPUT_TYPE"             => "SELECT",
             "INPUT_EXTRA_PARAM"      => "",
             "VALIDATION_TYPE"        => "numeric",
             "VALIDATION_EXTRA_PARAM" => $arrOrg),
        "name_company"=> array(
            "LABEL"                  => _tr('Fax Cid Name'),
             "REQUIRED"               => "no",
             "INPUT_TYPE"             => "TEXT",
             "INPUT_EXTRA_PARAM"      => "",
             "VALIDATION_TYPE"        => "text",
             "VALIDATION_EXTRA_PARAM" => ""),
        "fax_company" => array(
            "LABEL"                  => _tr('Fax Cid Number'),
             "REQUIRED"               => "no",
             "INPUT_TYPE"             => "TEXT",
             "INPUT_EXTRA_PARAM"      => "",
             "VALIDATION_TYPE"        => "text",
             "VALIDATION_EXTRA_PARAM" => ""),
        "date_fax"    => array(
            "LABEL"                  => _tr('Fax Date'),
             "REQUIRED"               => "no",
             "INPUT_TYPE"             => "DATE",
             "INPUT_EXTRA_PARAM"      => array("TIME" => false, "FORMAT" => "%Y-%m-%d","TIMEFORMAT" => "12"),
             "VALIDATION_TYPE"        => "text",
             "VALIDATION_EXTRA_PARAM" => ""),
        "filter"      => array(
            "LABEL"                  => _tr('Type Fax'),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => array("All"=>_tr('All'),"In"=>_tr('in'),"Out"=>_tr('out')),
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
    );
}

function download_faxFile($pDB, $credentials)
{
    $pACL = new paloACL($pDB);
    $oFax       = new paloFaxVisor($pDB); 
    $idFax      = getParameter("id");
    $arrFax     = $oFax->obtener_fax($idFax);
    if($arrFax==false){
        header('HTTP/1.1 404 Not Found');
        return "File $file_path not found!";
    }
    
    $dir_backup = "/var/www/elastixdir/faxdocs";
    $file_path  = $arrFax['faxpath']."/fax.pdf";
    $file_name  = $arrFax['pdf_file'];

    if($credentials['userlevel']!='superadmin'){
        if(!$pACL->userBellowOrganization($arrFax["id_user"],$credentials['id_organization'])){
            header('HTTP/1.1 404 Not Found');
            return "File $file_path not found!";
        }
    }
    
    if (!file_exists("$dir_backup/$file_path")) {
    	header('HTTP/1.1 404 Not Found');
        return "File $file_path not found!";
    } else {
        header("Cache-Control: private");
        header("Pragma: cache");
        header('Content-Type: application/pdf');
        header("Content-Length: ".filesize("$dir_backup/$file_path"));  
        header("Content-disposition: attachment; filename=$file_name");
        readfile("$dir_backup/$file_path");
    }
}

function delete_faxFile($smarty, $module_name, $local_templates_dir, $pDB, $credentials){
    $oFax = new paloFaxVisor($pDB);
    $id_organization=null;
    if($credentials['userlevel']!='superadmin')
        $id_organization=$credentials['id_organization'];
        
    // Ejecutar el borrado, si se ha validado.
    if ( is_array($_POST['faxes']) && count($_POST['faxes']) > 0) {
        $msgError = NULL;
        foreach ($_POST['faxes'] as $idFax) {
            if (!$oFax->deleteInfoFax($idFax,$id_organization)) {
                if ($oFax->errMsg = '')
                    $msgError = _tr('Unable to eliminate pdf file from the path.');
                else 
                    $msgError = _tr('Unable to eliminate pdf file from the database.').' - '.$oFax->errMsg;
            }
        }
        if (!is_null($msgError)) {
            $smarty->assign(array(
                'mb_title'      =>  _tr('ERROR'),
                'mb_message'    =>  $oFax->errMsg,
            ));
        }
    }else{
        $smarty->assign("mb_title", _tr('ERROR'));
        $smarty->assign("mb_message", _tr('You must select at least one fax'));
    }
    return listarFaxes($smarty, $module_name, $local_templates_dir,$pDB, $credentials);
}

function getAction()
{
    global $arrPermission;
    if(getParameter("action") == "edit")
        return (in_array('edit_fax',$arrPermission))?'edit':'report';
    else if(getParameter('faxes_delete'))
        return (in_array('delete_fax',$arrPermission))?'delete':'report';
    else if(getParameter("action")=="download")
        return 'download_faxFile';
    else
        return "report";
}

?>
