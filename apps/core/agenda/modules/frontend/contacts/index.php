<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
Codificación: UTF-8
+----------------------------------------------------------------------+
| Elastix version 1.4-1                                                |
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
$Id: index.php,v 1.1 20013-08-26 15:24:01 wreyes wreyes@palosanto.com Exp $ */
//include elastix framework

include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoDB.class.php";
include_once "libs/paloSantoJSON.class.php";
include_once "libs/paloSantoGrid.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //global variables
    global $arrConf;

    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);

    //return array("idUser"=>$idUser,"id_organization"=>$idOrganization,"userlevel"=>$userLevel1,"domain"=>$domain);
    global $arrCredentials;
    
    //actions
    $accion = getAction();
    
    switch($accion){
        case 'saveNew':
            $content = saveContact($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "saveEdit":
            $content = editContact($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'uploadImageContact':
            $content = uploadImageContact($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'deleteContacts':
            $content = deleteContacts($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'uploadCSV':
            $content = uploadCSV($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'templateContact':
            $content = templateContact($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "getImageExtContact":
            $content = getImageExtContact($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "getImageTmp":
            $content = getImageTmp($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "call2phone":
            $content = call2phone($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "transfer_call":
            $content = transferCALL($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default:
            $content = reportContact($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function reportContact($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf){
    global $arrCredentials;
    $coreContact=new coreContact($pDB);
    $jsonObject = new PaloSantoJSON();
    
    //obtener los parametros del filtro
    $filters['ftype_contacto']=getParameter('ftype_contacto'); 
    $filters['filter']=getParameter('filter');
    $filters['filter_value']=getParameter('filter_value');
    
    $validatedfilters= $coreContact->validatedFilters($filters);
    $total = $coreContact->getTotalContactsByFilter($validatedfilters);

    if($total===false){
        $total=0;
        $smarty->assign("MSG_ERROR_FIELD",_tr("Error en database"));
        $jsonObject->set_error($coreContact->sqlContact->getErrorMsg());
        return $jsonObject->createJSON();
    }
    
    $limit=7;
    
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    $oGrid->setStart(($total==0) ? 0 : $offset + 1);
    $oGrid->setEnd($end);
    
    $currentPage = $oGrid->calculateCurrentPage();
    $numPage = $oGrid->calculateNumPage();
    $url['menu']=$module_name;
    $url['ftype_contacto']=$filters['ftype_contacto'];
    $url['filter']=$filters['filter'];
    $url['filter_value']=$filters['filter_value'];
    
    $oGrid->setTitle(_tr('Contacts List'));
    $oGrid->setURL($url);
    $oGrid->enableExport();   // enable export.
    $oGrid->setNameFile_Export(_tr("ContactsExport"));
        
    $arrColumn=array();
    if($oGrid->isExportAction()){
        // arreglo de columnas para mostrar en los archivos de exportacion
        $arrColumn[]='Name';//if($validatedfilters['table']=="internal"){
        if($validatedfilters['table']=="internal"){
            $arrColumn[]='Ext';
        }else{
            $arrColumn[]='Phone';
        }    
        $arrColumn[]='Email';
        $arrColumn[]='Type Contact';
    }else{
        //arreglo de columnas para mostrar en la grilla
        $arrColumn[]="<span class='glyphicon glyphicon-check'></span>";
        $arrColumn[]=_tr('Picture');
        $arrColumn[]=_tr('Name');//if($validatedfilters['table']=="internal"){
        $arrColumn[]=_tr('Ext / Phone');
        $arrColumn[]=_tr('Email');
        $arrColumn[]=_tr('Call');
        $arrColumn[]=_tr('Transfer');
        $arrColumn[]=_tr('Type Contact'); 
    }
    
    $oGrid->setColumns($arrColumn);
    
    $validatedfilters= $coreContact->validatedFilters($filters);
    //enviamos como parametros limit, offset y los filtros validados
    
    $contacts= $coreContact->getContacts($limit, $offset, $validatedfilters);

    $arrDatosGrid=array();
    if($contacts === false){
        $smarty->assign("MSG_ERROR_FIELD",$coreContact->getErrorMsg());
        $jsonObject->set_error($coreContact->getErrorMsg());
        return $jsonObject->createJSON();
    }else{
        if($oGrid->isExportAction()){
            // data para exportar en los archivos
            foreach($contacts as $value){
                $tmp=array();
                
                $tmp[]=$value['name'];
                    
                if(empty($value['work_phone'])){
                    $tmp[]="N/A";
                }else{
                    $tmp[]= $value['work_phone'];
                }
                
                if(!empty($value['username'])){
                    $tmp[]= $value['username'];
                }else{
                    $tmp[]="N/A";
                }
                
                if($validatedfilters['table']!="internal"){
                    if($value['status']=="isPrivate"){
                        $tmp[]=_tr('Private');
                    }else{
                        $tmp[]=_tr('Public');
                    }
                }else{
                    $tmp[]=_tr('Public');
                }
                $arrDatosGrid[]=$tmp;    
            }
        }else{
            //data para mostrar en las grillas
            foreach($contacts as $value){
                $tmp=array();
                if($validatedfilters['table']=="internal"){
                    $tmp[]="<input type='checkbox' name='checkContacts' id='{$value['id']}' disabled >";
                }else{
                    if($arrCredentials['idUser']==$value['iduser']){
                        $tmp[]="<input type='checkbox' name='checkContacts' id='{$value['id']}'>";
                    }else{
                        $tmp[]="<input type='checkbox' name='checkContacts' id='{$value['id']}' disabled >";
                    }
                }
                
                if($validatedfilters['table']=="internal"){
                    $tmp[]="<img id='img-users' width='16' height='16' alt='image' src='index.php?menu=_elastixutils&action=getImage&ID={$value['id']}&rawmode=yes'/>";
                }else{
                    $tmp[]="<img id='img-users' width='16' height='16' alt='image' src='index.php?menu=$module_name&action=getImageExtContact&image={$value['picture']}&rawmode=yes'/>";
                }
                
                if($validatedfilters['table']=="internal"){
                    $tmp[]= htmlentities($value['name'],ENT_QUOTES, "UTF-8");
                }else{
                    if($arrCredentials['idUser']==$value['iduser']){
                        $tmp[]="<a href='#' onclick='editContact({$value['id']})'>".htmlentities($value['name'],ENT_QUOTES, "UTF-8")."</a>";
                    }else{
                        $tmp[]= htmlentities($value['name'],ENT_QUOTES, "UTF-8");
                    }
                }
                
                if($validatedfilters['table']=="internal"){
                    $tmp[]= htmlentities($value['extension'],ENT_QUOTES, "UTF-8");  
                }else{
                    if(empty($value['work_phone'])){
                        $tmp[]="N/A";
                    }else{
                        $tmp[]= htmlentities($value['work_phone'],ENT_QUOTES, "UTF-8");
                    }
                }
                
                if(!empty($value['username'])){
                    $tmp[]= htmlentities($value['username'],ENT_QUOTES, "UTF-8");
                }else{
                    $tmp[]="N/A";
                }
                
                //$tmp[]="<span class='glyphicon glyphicon-earphone'></span>";
                $tmp[]="<a href='#' onclick='callContact({$value['id']})'><span class='glyphicon glyphicon-earphone'></span></a>";
                
                if($validatedfilters['table']=="internal"){
                    //$tmp[]=_tr('Transfer');
                    $tmp[]="<a href='#' onclick='transferCall({$value['id']})'>"._tr('Transfer')."</a>";
                }else{
                    $tmp[]="N/A";
                }

                if($validatedfilters['table']!="internal"){
                    if($value['status']=="isPrivate"){
                        $tmp[]=_tr('Private');
                    }else{
                        $tmp[]=_tr('Public');
                    }
                }else{
                    $tmp[]=_tr('Public');
                }
                $arrDatosGrid[]=$tmp;
            }
        }
        
    }
    
    $action=getParameter('action');
    if($action=='search'){
        $arrData['url']=$oGrid->getURL();
        $arrData['url']=str_replace('&amp;','&',$arrData['url']);
        $arrData['numPage']=$numPage;
        $arrData['currentPage']=$currentPage;
        $arrData['content']=$arrDatosGrid;
        $jsonObject->set_message($arrData);
        return $jsonObject->createJSON();
    }
    
    $oGrid->addButtonAction("new_contact","<span class='glyphicon glyphicon-user'></span> New Contact","", "newContact()");
    $oGrid->addButtonAction("remove_contact","<span class='glyphicon glyphicon-remove'></span> Delete Contacts","", "deleteContacts('"._tr("Are you sure you wish to delete the contact.")."')");
    $oGrid->addButtonAction("elx_upload_file","<span class='glyphicon glyphicon-upload'></span> Upload from CSV","", "");
    $oGrid->addButtonAction("elx_export_data","<span class='glyphicon glyphicon-download'></span>","", "");
    $oGrid->addButtonAction("elx_show_filter","<span class='glyphicon glyphicon-filter'></span> Show filter","", "");
    
    $arrayData=array();
    
    $arrFormFilter = createFilterForm();
    $oFilterForm = new paloForm($smarty, $arrFormFilter);
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl",_tr('extension'), $arrayData);
    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo=actionsReport($arrDatosGrid, $oGrid);
    return $contenidoModulo;
}    

function actionsReport($arrDatosGrid, $oGrid){
    $contenidoModulo = $oGrid->fetchGrid(array(),$arrDatosGrid);
    if(getParameter('action')=='cancel'){
        $jsonObject = new PaloSantoJSON();
        $jsonObject->set_message($contenidoModulo);
        return $jsonObject->createJSON();
    }else
        return $contenidoModulo."<script type='text/javascript' src='web/_common/js/jquery.liteuploader.js'></script>";
}

function templateContact($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $jsonObject = new PaloSantoJSON();
    $coreContact=new coreContact($pDB);
    
    $smarty->assign("TOOLTIP_FIRS_NAME",_tr("Invalid first name"));
    $smarty->assign("TOOLTIP_LAST_NAME",_tr("Invalid last name"));
    $smarty->assign("TOOLTIP_POHNE",_tr("Just numeric characters are valid"));
    $smarty->assign("TOOLTIP_EMAIL",_tr("Invalid email"));

    if(getParameter('action')=='newContact'){
        //formulario vario con los valores por default   
        $smarty->assign('ELX_ACTION','new');
        $arrayContact=array();
        $arrayContact['contact_type']='isPrivate';
    }else{
        //consulto la informacion del usuario que me piden
        $idContact=getParameter('idContact');
        
        //consulta que exista en la base y que el usuario tenga acceso la mismo (usuario y dominio)
        $arrayContact = $coreContact->sqlContact->getExternalContactForEdit($idContact);
        
        if($arrayContact===false){
            $smarty->assign("MSG_ERROR_FIELD",$coreContact->sqlContact->getErrorMsg());
            $jsonObject->set_error($coreContact->sqlContact->getErrorMsg());
            return $jsonObject->createJSON();
        }
        
        if($arrayContact==false){
            $smarty->assign("MSG_ERROR_FIELD",_tr('User does not exist'));
            $jsonObject->set_error($coreContact->sqlContact->getErrorMsg());
            return $jsonObject->createJSON();
        }
        
        $_SESSION['idContact']=$idContact;
        
        $arrayContact['contact_type']= htmlentities($arrayContact['status'],ENT_QUOTES, "UTF-8");
        $arrayContact['first_name']= htmlentities($arrayContact['name'],ENT_QUOTES, "UTF-8");
        $arrayContact['last_name']= htmlentities($arrayContact['last_name'],ENT_QUOTES, "UTF-8");
        $arrayContact['work_phone_number']= htmlentities($arrayContact['work_phone'],ENT_QUOTES, "UTF-8");
        $arrayContact['cell_phone_number']= htmlentities($arrayContact['cell_phone'],ENT_QUOTES, "UTF-8");
        $arrayContact['home_phone_number']= htmlentities($arrayContact['home_phone'],ENT_QUOTES, "UTF-8");
        $arrayContact['fax_number_1']= htmlentities($arrayContact['fax1'],ENT_QUOTES, "UTF-8");
        $arrayContact['fax_number_2']= htmlentities($arrayContact['fax2'],ENT_QUOTES, "UTF-8");
        $arrayContact['email']= htmlentities($arrayContact['email'],ENT_QUOTES, "UTF-8");
        $arrayContact['province']= htmlentities($arrayContact['province'],ENT_QUOTES, "UTF-8");
        $arrayContact['city']= htmlentities($arrayContact['city'],ENT_QUOTES, "UTF-8");
        $arrayContact['address']= htmlentities($arrayContact['address'],ENT_QUOTES, "UTF-8");
        $arrayContact['company']= htmlentities($arrayContact['company'],ENT_QUOTES, "UTF-8");
        $arrayContact['contact_person']= htmlentities($arrayContact['company_contact'],ENT_QUOTES, "UTF-8");
        $arrayContact['contact_person_position']= htmlentities($arrayContact['contact_rol'],ENT_QUOTES, "UTF-8");
        $arrayContact['notes']= htmlentities($arrayContact['notes'],ENT_QUOTES, "UTF-8");
        $smarty->assign('ID_PICTURE',$arrayContact['picture']);
        $smarty->assign('ELX_ACTION','edit');
    }

    //contiene los elementos del formulario    
    $arrForm = createForm();
    $oForm = new paloForm($smarty,$arrForm);
    
    $html = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr('extension'),$arrayContact);
    $contenidoModulo = "<div><form enctype='multipart/form-data' method='POST' style='margin-bottom:0;' name='$module_name' id='$module_name' action='?menu=$module_name'>".$html."</form></div>";
    $jsonObject->set_message($contenidoModulo);

    return $jsonObject->createJSON();
}

function saveContact($smarty, $module_name, $local_templates_dir, $pDB, $arrConf){
    $jsonObject = new PaloSantoJSON();
    $coreContact=new coreContact($pDB);
    
    $contact['contact_type']=getParameter('contact_type'); 
    $contact['first_name']=getParameter('first_name');
    $contact['last_name']=getParameter('last_name');
    $contact['work_phone_number']=getParameter('work_phone_number');
    $contact['cell_phone_number']=getParameter('cell_phone_number'); 
    $contact['home_phone_number']=getParameter('home_phone_number'); 
    $contact['fax_number_1']=getParameter('fax_number_1'); 
    $contact['fax_number_2']=getParameter('fax_number_2'); 
    $contact['email']=getParameter('email'); 
    $contact['province']=getParameter('province'); 
    $contact['city']=getParameter('city'); 
    $contact['address']=getParameter('address'); 
    $contact['company']=getParameter('company');
    $contact['contact_person']=getParameter('contact_person');
    $contact['contact_person_position']=getParameter('contact_person_position');
    $contact['notes']=getParameter('notes');
    $contact['picture']=getParameter('picture');
    
    $validateForm = $coreContact->validateForm($contact);
    
    if($validateForm===false){
        $jsonObject->set_error($coreContact->getErrorMsg());
        return $jsonObject->createJSON();
    }

    $sqlContact= $coreContact->getSqlContact();
    $sqlContact->_DB->beginTransaction();
    
    $last_id = $sqlContact->addContact($contact);
    if($last_id === false){
        $sqlContact->_DB->rollBack();
        $jsonObject->set_error($sqlContact->getErrorMsg());
        return $jsonObject->createJSON();
        //debo borrar la imagen en caso de uqe se haya subido
        if(!empty($_SESSION['tmp_contact_img']))
            unlink("{$coreContact->pathImageContact}/{$_SESSION['tmp_contact_img']}");
    }else{
        $sqlContact->_DB->commit();
        $uploadImage = $coreContact->uploadImage($last_id);
        if($uploadImage===false){
            $jsonObject->set_error($coreContact->getErrorMsg());
            return $jsonObject->createJSON();
        }        
        $contenidoModulo =reportContact($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf);
        $jsonObject->set_message($contenidoModulo);
    }
    return $jsonObject->createJSON();
}

function editContact($smarty, $module_name, $local_templates_dir, $pDB, $arrConf){
    $jsonObject = new PaloSantoJSON();
    $coreContact=new coreContact($pDB);

    if(empty($_SESSION['idContact'])){
        $smarty->assign("MSG_ERROR_FIELD",_tr('User does not exist'));
        $jsonObject->set_error(_tr('User does not exist'));
        return $jsonObject->createJSON();
    }
 
    $existContact = $coreContact->sqlContact->existContact($_SESSION['idContact']);
    if($existContact===false){
        $smarty->assign("MSG_ERROR_FIELD",$coreContact->sqlContact->getErrorMsg());
        $jsonObject->set_error($coreContact->sqlContact->getErrorMsg());
        return $jsonObject->createJSON();
    }
   
    if($existContact==false){
        $smarty->assign("MSG_ERROR_FIELD",_tr('User does not exist'));
        $jsonObject->set_error(_tr('User does not exist'));
        return $jsonObject->createJSON();
    }
    
    $contact['id']= $_SESSION['idContact'];
    $contact['contact_type']=getParameter('contact_type'); 
    $contact['first_name']=getParameter('first_name');
    $contact['last_name']=getParameter('last_name');
    $contact['work_phone_number']=getParameter('work_phone_number');
    $contact['cell_phone_number']=getParameter('cell_phone_number'); 
    $contact['home_phone_number']=getParameter('home_phone_number'); 
    $contact['fax_number_1']=getParameter('fax_number_1'); 
    $contact['fax_number_2']=getParameter('fax_number_2'); 
    $contact['email']=getParameter('email'); 
    $contact['province']=getParameter('province'); 
    $contact['city']=getParameter('city'); 
    $contact['address']=getParameter('address'); 
    $contact['company']=getParameter('company');
    $contact['contact_person']=getParameter('contact_person');
    $contact['contact_person_position']=getParameter('contact_person_position');
    $contact['notes']=getParameter('notes');
    $contact['picture']=getParameter('picturidusere');
    
    $validateForm = $coreContact->validateForm($contact);

    if($validateForm===false){
        $jsonObject->set_error($coreContact->getErrorMsg());
        return $jsonObject->createJSON();
    }

    $sqlContact= $coreContact->getSqlContact();
    $sqlContact->_DB->beginTransaction();
    
    $result = $sqlContact->editContact($contact);
    if($result === false){
        $sqlContact->_DB->rollBack();
        $jsonObject->set_error($sqlContact->getErrorMsg());
        return $jsonObject->createJSON();
        //debo borrar la imagen en caso de uqe se haya subido
        if(!empty($_SESSION['tmp_contact_img']))
            unlink("{$coreContact->pathImageContact}/{$_SESSION['tmp_contact_img']}");
    }else{
        $sqlContact->_DB->commit();
        $uploadImage = $coreContact->uploadImage($contact['id']);
        if($uploadImage===false){
            $jsonObject->set_error($coreContact->getErrorMsg());
            return $jsonObject->createJSON();
        }
        $contenidoModulo =reportContact($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf);
        $jsonObject->set_message($contenidoModulo);
    }
    return $jsonObject->createJSON();
    unset($_SESSION['idContact']);   
}


function uploadImageContact($smarty, $module_name, $local_templates_dir, $pDB, $arrConf)
{
    global $arrCredentials;
    
    $jsonObject = new PaloSantoJSON();
    $coreContact=new coreContact($pDB); 

    $domain = $arrCredentials['domain'];

    foreach ($_FILES['picture']['error'] as $key => $error)
    {
        if ($error == UPLOAD_ERR_OK)
        {  
            $pictureUpload = $_FILES['picture']['name'][$key];
            $uploadedUrl = $coreContact->checkRequirementsForUpload($domain, $pictureUpload, $nameTmp);
            
            if($uploadedUrl===false){
                $jsonObject->set_error(_tr("Error uploading your file"));
                return $jsonObject->createJSON();
            }
            
            if(move_uploaded_file( $_FILES['picture']['tmp_name'][$key], $uploadedUrl)===false){
                $jsonObject->set_error(_tr("Failed to move file"));
                return $jsonObject->createJSON();
            }else{
                /*
                $urls[] = $uploadedUrl;
                $jsonObject->set_message($nameTmp);*/
                $src="index.php?menu=$module_name&action=getImageTmp&image=$nameTmp&rawmode=yes";
                $imgData = array();
                $imgData['name']= $nameTmp;
                $imgData['url']= $src;
                $jsonObject->set_message($imgData);
            }
        }else{
            $jsonObject->set_error(_tr("Error uploading your file"));
        }
    }
    return $jsonObject->createJSON();
}


function getImageExtContact($smarty, $module_name, $local_templates_dir, $pDB, $arrConf)
{
    $coreContact=new coreContact($pDB);
    $picture=getParameter('image');
    $coreContact->getImageContactExternal($picture);
    return;
}

function getImageTmp($smarty, $module_name, $local_templates_dir, $pDB, $arrConf)
{
    $coreContact=new coreContact($pDB);
    $picture=getParameter('image');
    $coreContact->getImagePreview($picture);
    return;
}

function deleteContacts($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $jsonObject = new PaloSantoJSON();
    $coreContact=new coreContact($pDB);
    
    $contact['contactChecked']=getParameter('contactChecked'); 
    
    if(empty($contact['contactChecked'])){
        $jsonObject->set_error(_tr("There are no contacts to remove"));
        return $jsonObject->createJSON();
    }
    
    $arrayIdChecked = explode(",", $contact['contactChecked']);
    // elimina los valoress vacios, 0, null, 0.0, fale
    $newArrayIds = (array_filter($arrayIdChecked));
    
    $nameImages = $coreContact->sqlContact->getContactsImages($newArrayIds);

    // luego de borrar las imagenes se procede a borrar los contactos de las tablas
    $result = $coreContact->deleteImages($nameImages, $newArrayIds);
    
    if($result === false){
        $jsonObject->set_error($coreContact->sqlContact->getErrorMsg());
        return $jsonObject->createJSON();
    }else{
       // $coreContact->deleteImages($newArrayIds);
        $jsonObject->set_message(_tr("Deleted users correctly"));
        $contenidoModulo = reportContact($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf);
        $jsonObject->set_message($contenidoModulo);
    }
    
    return $jsonObject->createJSON();

}

function uploadCSV($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $jsonObject = new PaloSantoJSON();
    $coreContact=new coreContact($pDB);
    
    foreach ($_FILES['elx_uploadFile']['error'] as $key => $error)
    {
        if ($error == UPLOAD_ERR_OK)
        {  
            if (!preg_match('/\.csv$/i', $_FILES['elx_uploadFile']['name'][$key])) {
                $smarty->assign("MSG_ERROR_FIELD",_tr("Invalid file extension.- It must be csv"));
                $jsonObject->set_error(_tr('Invalid file extension.- It must be csv'));
                return $jsonObject->createJSON();
            }else {
                if(is_uploaded_file($_FILES['elx_uploadFile']['tmp_name'][$key])) {
                    //Funcion para cargar las extensiones
                    $result = $coreContact->load_contacts_from_csv($_FILES['elx_uploadFile']['tmp_name'][$key]);
                    if($result===false){
                        $jsonObject->set_error($coreContact->getErrorMsg());
                        return $jsonObject->createJSON();
                    }
                    $jsonObject->set_message(_tr("contacts created"));
                }else {
                    $smarty->assign("MSG_ERROR_FIELD",_tr("Possible file upload attack. Filename"));
                    $jsonObject->set_error(_tr('Possible file upload attack. Filename'));
                    return $jsonObject->createJSON();
                }
            }
        }else{
            $jsonObject->set_error('algo paso');
        }
    }
    return $jsonObject->createJSON();

}

function call2phone($smarty, $module_name, $local_templates_dir, $pDB, $arrConf)
{
    global $arrCredentials;
    $jsonObject = new PaloSantoJSON();
    $coreContact = new coreContact($pDB);
    $id_user      = $arrCredentials['idUser'];
    
    $idContact=getParameter('idContact');
    
    //obtener los parametros del filtro
    $filters['ftype_contacto']=getParameter('ftype_contacto'); 
    $validatedfilters= $coreContact->validatedFilters($filters);

    if(!empty($id_user))
    {
        if($validatedfilters['table']=="internal"){
            $extension = $coreContact->sqlContact->getExtension($idContact);
        }else{
            $extension = $coreContact->sqlContact->getPhone($idContact);
        }
            
        if($extension === false)
        {
            $smarty->assign("MSG_ERROR_FIELD",$coreContact->sqlContact->getErrorMsg());
            $jsonObject->set_error($coreContact->sqlContact->getErrorMsg());
            return $jsonObject->createJSON();
        }

        $dataExt = $coreContact->sqlContact->Obtain_Protocol_from_Ext($id_user);
        
        if($dataExt === FALSE)
        {
            $smarty->assign("MSG_ERROR_FIELD",$coreContact->sqlContact->getErrorMsg());
            $jsonObject->set_error($coreContact->getErrorMsg());
            return $jsonObject->createJSON();
        }
        
        $result = $coreContact->Call2Phone($extension, $dataExt['dial'],$dataExt['exten'],$dataExt['context'],$dataExt['clid_name'],$dataExt['code']);
        if(!$result)
        {
            $smarty->assign("MSG_ERROR_FIELD",_tr("The call couldn't be realized"));
            $jsonObject->set_error(_tr("The call couldn't be realized"));
            return $jsonObject->createJSON();
        }
        
    }
    else{
        $smarty->assign("MSG_ERROR_FIELD",$coreContact->getErrorMsg());
        $jsonObject->set_error($coreContact->getErrorMsg());
        return $jsonObject->createJSON();
    }

    return $jsonObject->createJSON();
}


function transferCALL($smarty, $module_name, $local_templates_dir, $pDB, $arrConf)
{
    global $arrCredentials;
    $jsonObject = new PaloSantoJSON();
    $coreContact = new coreContact($pDB);
    $id_user      = $arrCredentials['idUser'];
    
    $idContact=getParameter('idContact');
    
    //obtener los parametros del filtro
    $filters['ftype_contacto']=getParameter('ftype_contacto'); 
    $validatedfilters= $coreContact->validatedFilters($filters);

    if(!empty($id_user))
    {
        if($validatedfilters['table']=="internal"){
            $extension = $coreContact->sqlContact->getExtension($idContact);
        }else{
            $extension = $coreContact->sqlContact->getPhone($idContact);
        }
            
        if($extension === false)
        {
            $smarty->assign("MSG_ERROR_FIELD",$coreContact->sqlContact->getErrorMsg());
            $jsonObject->set_error($coreContact->sqlContact->getErrorMsg());
            return $jsonObject->createJSON();
        }

        $dataExt = $coreContact->sqlContact->Obtain_Protocol_from_Ext($id_user);
        
        if($dataExt === FALSE)
        {
            $smarty->assign("MSG_ERROR_FIELD",$coreContact->sqlContact->getErrorMsg());
            $jsonObject->set_error($coreContact->getErrorMsg());
            return $jsonObject->createJSON();
        }
        
        $result = $coreContact->TranferCall($extension, $dataExt['dial'],$dataExt['context'],$dataExt['code']);
        if(!$result)
        {
            $smarty->assign("MSG_ERROR_FIELD",_tr("The transfer couldn't be realized, maybe you don't have any conversation now."));
            $jsonObject->set_error($coreContact->getErrorMsg());
            return $jsonObject->createJSON();
        }
        
    }
    else{
        $smarty->assign("MSG_ERROR_FIELD",$coreContact->getErrorMsg());
        $jsonObject->set_error($coreContact->getErrorMsg());
        return $jsonObject->createJSON();
    }

    return $jsonObject->createJSON();
}



function createForm(){
    $contactType[]=array("id"=>'radio1',"label"=>'Private',"value"=>"isPrivate");
    $contactType[]=array("id"=>'radio2',"label"=>'Public',"value"=>"isPublic");

    $arrForm = array("contact_type"    => array("LABEL"                  => _tr("Contact Type:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "OPTION",
                                                "INPUT_EXTRA_PARAM"      => $contactType,
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "first_name"  => array("LABEL"               => _tr("First Name: "),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "last_name"  => array("LABEL"               => _tr("Last Name:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "work_phone_number"  => array("LABEL"               => _tr("Work's Phone Number:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                    "cell_phone_number"  => array("LABEL"               => _tr("Cell Phone Number (SMS):"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                    "home_phone_number"  => array("LABEL"               => _tr("Home Phone Number:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "fax_number_1"  => array("LABEL"               => _tr("FAX Number 1:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                        "fax_number_2"  => array("LABEL"               => _tr("FAX Number 2:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                                "email"  => array("LABEL"               => _tr("Email:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "province"  => array("LABEL"               => _tr("Province:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                                "city"  => array("LABEL"               => _tr("City:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "address"  => array("LABEL"               => _tr("Address:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "company"   => array("LABEL"               => _tr("Company:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                "contact_person"   => array( "LABEL"                    => _tr("Contact person in your Company:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
            "contact_person_position"  => array("LABEL"               => _tr("Contact person's current position:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXT",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                                "notes"  => array("LABEL"               => _tr("Notes:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "TEXTAREA",
                                                "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                                "VALIDATION_TYPE"        => "text",
                                                "VALIDATION_EXTRA_PARAM" => ""),
                            "picture"   => array("LABEL"               => _tr("Picture:"),
                                                "REQUIRED"               => "no",
                                                "INPUT_TYPE"             => "FILE",
                                                "INPUT_EXTRA_PARAM"      => array("id" => "picture", "class"=>"fileUpload"),
                                                "VALIDATION_TYPE"        => "",
                                                "VALIDATION_EXTRA_PARAM" => ""),

                        
    );
    return $arrForm;
}

function createFilterForm()
{
    $typeContact=array('internal'=>"Internal",'external'=>"External");
    $filter=array('name'=>"Name",'extension'=>"Extension");
    
    $arrFields = array(
            "ftype_contacto"   => array("LABEL"                      => _tr("Phone Directory"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $typeContact,
                                            "INPUT_EXTRA_PARAM_OPTIONS" => array("class" => "form-control input-sm"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "filter"   => array("LABEL"                              => _tr("Filter"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $filter,
                                            "INPUT_EXTRA_PARAM_OPTIONS"=> array("class" => "form-control input-sm"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
            "filter_value"  => array("LABEL"                         => _tr(""),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("class" => "form-control input-sm", "placeholder" => ""),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "")
                            );
    return $arrFields;
}

function getAction()
{
    if(getParameter('action')=='saveNew'){
        return 'saveNew';
    }elseif(getParameter('action')=='saveEdit'){
        return 'saveEdit';
    }elseif(getParameter('action')=='uploadImageContact'){
        return 'uploadImageContact';
    }elseif(getParameter('action')=='deleteContacts'){
        return 'deleteContacts';
    }elseif(getParameter('action')=='uploadCSV'){
        return 'uploadCSV';
    }elseif(getParameter('action')=='newContact' || getParameter('action')=='editContact'){
        return "templateContact";
    }elseif(getParameter('action')=='getImageTmp'){
        //obtener imagen contactos externos
        return 'getImageTmp';
    }elseif(getParameter('action')=='getImageExtContact'){
        //obtener imagen contactos externos
        return 'getImageExtContact';
    }elseif(getParameter('action')=='call2phone'){
        return 'call2phone';
    }elseif(getParameter('action')=='transfer_call'){
        return 'transfer_call';
    }else{
        return "report";
    }
}


?>
