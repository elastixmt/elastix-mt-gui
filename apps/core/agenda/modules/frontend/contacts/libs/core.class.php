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
$Id: paloSantoForm.class.php,v 1.4 2007/05/09 01:07:03 gcarrillo Exp $ */
global $arrConf;


class coreContact{

    public $sqlContact;
    private $errMsg;
    public $pathImageContact;
    
    public function coreContact(&$pDB){ 
        global $arrCredentials;      
        $this->idUser=$arrCredentials['idUser'];
        $this->pathImageContact="/var/www/elastixdir/contacts_images/{$arrCredentials['domain']}/{$arrCredentials['idUser']}";    
        $this->pathImageOrganization="/var/www/elastixdir/contacts_images/{$arrCredentials['domain']}";
        $this->sqlContact = new paloContact($pDB,$this->idUser);
    }

    function getErrorMsg(){
        return $this->errMsg;
    }

    function getSqlContact(){
        return $this->sqlContact;
    }

    function validateForm($arrayData)
    {
        $errorData = array();
        $errorBoolean = false;

        foreach($arrayData as $key => $value){  
            if($key == "contact_type"){  
                if($value==""  || !isset($value)){
                    $errorBoolean = true;
                    $errorData['field'][] = "contact_type";
                }
            }       
            if($key == "first_name"){  
                if($value==""){
                    $errorBoolean = true;
                    $errorData['field'][] = "first_name";
                }
            }
            if($key == "last_name"){  
                if($value== ""){
                    $errorBoolean = true;
                    $errorData['field'][] = "last_name";
                }
            }
            if($key == "email"){  
                if($value!= ""){
                    if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
                        $errorBoolean = true;
                        $errorData['field'][] = "email";
                    }
                }
            }
            if($key == "work_phone_number"){  
                if($value== ""){
                    $errorBoolean = true;
                    $errorData['field'][] = "work_phone_number";
                }elseif(!preg_match('/^[0-9]+$/', $value)){
                    $errorBoolean = true;
                    $errorData['field'][] = "work_phone_number";
                }   
            }
        }

        if($errorBoolean){
            $errorData['stringError'] = _tr("Fields marked with * are required");
            $this->errMsg = $errorData;
            return false;
        }

        return true;
    }
        
    function checkRequirementsForUpload($domain, $pictureUpload, &$nameTmp)
    {        
        if(!empty($pictureUpload)){
            $generalPath= "/var/www/elastixdir/contacts_images/";
            $this->pathImageContact = "/var/www/elastixdir/contacts_images/$domain/$this->idUser";

            if (!file_exists($generalPath)) {
                return false;  
            }
            
            //verificamos que existe el directorio
            if (!file_exists($this->pathImageContact)) {
                mkdir($this->pathImageContact, 0755, true);   
            }

            //verificamos la extension del archivo
            if (!preg_match("/^(\w|-|\.|\(|\)|\s)+\.(png|PNG|JPG|jpg|JPEG|jpeg)$/",$pictureUpload)) {
                return false;
            }
            
            if(empty($_SESSION['tmp_contact_img'])){
                $nameFile=date("Ymdhis");
                $ext = pathinfo($pictureUpload, PATHINFO_EXTENSION);
                $nameTmp = "tmp_contact_$nameFile.$ext";
                $_SESSION['tmp_contact_img']=$nameTmp;
            }else{
                if(!empty($_SESSION['tmp_contact_img'])){
                    //si existe el archivo
                    if("{$this->pathImageContact}/{$_SESSION['tmp_contact_img']}")                                        
                        unlink("{$this->pathImageContact}/{$_SESSION['tmp_contact_img']}");
                    $nameFile=date("Ymdhis");
                    $ext = pathinfo($pictureUpload, PATHINFO_EXTENSION);
                    $nameTmp = "tmp_contact_$nameFile.$ext";
                    $_SESSION['tmp_contact_img']=$nameTmp;
                }            
            }
            $uploadedUrl= "{$this->pathImageContact}/$nameTmp";
            return $uploadedUrl;
        }

    }
    
    function fileCSV()
    {        
        $externalContacts = $this->sqlContact->getExternalContactsByUser();
        if($externalContacts === false){
            $this->errMsg = $this->sqlContact->getErrorMsg();
        }
        
    }
    
    function validatedFilters($filters){
        $arrayFilter= array();
        
        if($filters['ftype_contacto']=="internal" or empty($filters['ftype_contacto'])){
            $arrayFilter['table']="internal";
        }else{
            $arrayFilter['table']="external";
        }
        
        if(!empty($filters['filter_value'])){
            if(!empty($filters['filter'])){
                $arrayFilter['filter'] = $filters['filter'];
                $arrayFilter['filter_value'] = $filters['filter_value'];
            }
        }
        
        return $arrayFilter;
    }
    
    function getTotalContactsByFilter($validatedfilters)
    {       
        if($validatedfilters['table']=="internal"){
            $result = $this->sqlContact->getNumberOfContactsInternal($validatedfilters);
        }else{
            $result = $this->sqlContact->getNumberOfContactsExternal($validatedfilters);
        }
        return $result;    
    }
    
    function getContacts($limit, $offset, $validatedfilters)
    {        
        if($validatedfilters['table']=="internal"){
            $result = $this->sqlContact->getIntrnalContacts($limit, $offset, $validatedfilters);
        }else{
            $result = $this->sqlContact->getExternalContactsByUser($limit, $offset, $validatedfilters);
        }
        
        if($result===false){
            $this->errMsg = $this->sqlContact->getErrorMsg();
            return false;
        }
        
        return $result;    
    }
    
    
    function getImageContactExternal($picture){
        $imgPath='';
        
        if(!empty($picture)){
            $picture=basename($picture);
            $idContact= explode(".", $picture);
            
            $parentId = $this->sqlContact->getIdParent($idContact[0]);
            if($parentId===false){
                $this->errMsg = $this->sqlContact->getErrorMsg();
                $imgPath='';
            }else
                $imgPath="{$this->pathImageOrganization}/$parentId/$picture";
        }
        
        $imgDefault = "/var/www/html/web/_common/images/Icon-user.png";
        
        // Creamos la imagen a partir de un fichero existente
        if(file_exists($imgPath) && $imgPath!=''){
            $ext = pathinfo($imgPath, PATHINFO_EXTENSION);
            Header("Content-type: image/$ext");
            $im = file_get_contents($imgPath);
            echo $im;
            //print($imgPath);
        }else{
            Header("Content-type: image/png");
            $im = file_get_contents($imgDefault);
            echo $im;
            //print($imgDefault);
        }
        return;
    }
    
    
    function uploadImage($last_id){
        //debo subir la imagen en caso de que haya alguna en la session
        global $arrCredentials;
        $domain = $arrCredentials['domain'];
        $this->pathImageContact = "/var/www/elastixdir/contacts_images/$domain/$this->idUser";
        if(!empty($_SESSION['tmp_contact_img'])){
            $ext = pathinfo($_SESSION['tmp_contact_img'], PATHINFO_EXTENSION);
            $imgUser = "$last_id.$ext";
            //si existe la imgen del contacto ya guardada, procedmos a borrarla para subir la nueva imagen
            if(file_exists("{$this->pathImageContact}/$imgUser")){
                $removeImgContact = unlink("{$this->pathImageContact}/$imgUser");
                if(!$removeImgContact){
                    $this->errMsg = _tr("Failed to change the image");
                    return false;
                }
            }
            rename("{$this->pathImageContact}/{$_SESSION['tmp_contact_img']}", "{$this->pathImageContact}/$imgUser");
            //update picture sql
            if($this->sqlContact->updatePicture($last_id, $imgUser)===false){
                $this->errMsg = _tr("Error uploading image");
                return false;
            }
            //BORRO LA VARIABLE DE SESSION
            unset($_SESSION['tmp_contact_img']);
        }            
        return true;       
    }
    
    function deleteImages($arrayImages, $newArrayIds){
        foreach($arrayImages as $images){
            if(!empty($images['picture'])){
                if (file_exists("{$this->pathImageContact}/{$images['picture']}")) {
                    $removeImgContact = unlink("{$this->pathImageContact}/{$images['picture']}");
                }
            }
        }
        
        $resultado = $this->sqlContact->deleteContacts($newArrayIds);
        
        return $resultado;
    }
    
    
    function load_contacts_from_csv($ruta_archivo)
    {
        $Messages = "";
        $arrayColumnas = array();
        $id_user      = $this->idUser;

        $result = $this->isValidCSV($ruta_archivo, $arrayColumnas);
        if($result != 'true'){
            $this->errMsg = $result;
            return false;
        }

        $hArchivo = fopen($ruta_archivo, 'rt');
        $cont = 0;
        

        if ($hArchivo) {
            //Linea 1 header ignorada
            $tupla = fgetcsv($hArchivo, 4096, ",");
            //Desde linea 2 son datos
            while ($tupla = fgetcsv($hArchivo, 4096, ","))
            {
                if(is_array($tupla) && count($tupla)>=3)
                {
                    $data = array();

                    $namedb       = $tupla[$arrayColumnas[0]];
                    $last_namedb  = $tupla[$arrayColumnas[1]];
                    $telefonodb   = $tupla[$arrayColumnas[2]];
                    $cellphonedb  = isset($arrayColumnas[3])?$tupla[$arrayColumnas[3]]:"";
                    $homephonedb  = isset($arrayColumnas[4])?$tupla[$arrayColumnas[4]]:"";
                    $fax1db       = isset($arrayColumnas[5])?$tupla[$arrayColumnas[5]]:"";
                    $fax2db       = isset($arrayColumnas[6])?$tupla[$arrayColumnas[6]]:"";
                    $emaildb      = isset($arrayColumnas[7])?$tupla[$arrayColumnas[7]]:"";
                    $provincedb   = isset($arrayColumnas[8])?$tupla[$arrayColumnas[8]]:"";
                    $citydb       = isset($arrayColumnas[9])?$tupla[$arrayColumnas[9]]:"";
                    $addressdb    = isset($arrayColumnas[10])?$tupla[$arrayColumnas[10]]:"";
                    $companydb    = isset($arrayColumnas[11])?$tupla[$arrayColumnas[11]]:"";
                    $company_codb = isset($arrayColumnas[12])?$tupla[$arrayColumnas[12]]:"";
                    $contact_pdb  = isset($arrayColumnas[13])?$tupla[$arrayColumnas[13]]:"";
                    $notesdb      = isset($arrayColumnas[14])?$tupla[$arrayColumnas[14]]:"";
                    $iduserdb     = $id_user;

                    $data = array($namedb, $last_namedb, $telefonodb, $cellphonedb, $homephonedb, $fax1db, $fax2db, $emaildb,
                    $provincedb, $citydb, $iduserdb, $addressdb, $companydb, $company_codb, $contact_pdb, $notesdb);
                    //Paso 1: verificar que no exista un usuario con los mismos datos
                    $result = $this->sqlContact->existContacts($namedb, $last_namedb, $telefonodb);
                    if($result===false){
                        $Messages .= _tr('ERROR')." :". $this->sqlContact->errMsg . "  <br />";
                    }else if($result['total']>0){
                        $Messages .= _tr('ERROR')." :". _tr("Contact Data already exists")." :"." {$data[0]} {$data[1]} [{$data[2]}]<br />";
                    }else{
                        //Paso 2: creando en la contact data
                        $msg= $this->sqlContact->addContactCsv($data);
                        if($msg===false)
                            $Messages .= _tr("ERROR") . $pDB->errMsg . "<br />";

                        $cont++;
                    }
                }
            }

            $Messages .= _tr("Total contacts created").": $cont<br />";
            $this->errMsg = $Messages;
            //$smarty->assign("mb_message", $Messages);
        }

        unlink($ruta_archivo);
    }
    
    function isValidCSV($sFilePath, &$arrayColumnas){
        $hArchivo = fopen($sFilePath, 'rt');
        $cont = 0;
        $ColName = -1;

        //Paso 1: Obtener Cabeceras (Minimas las cabeceras: Display Name, User Extension, Secret)
        if ($hArchivo) {
            $tupla = fgetcsv($hArchivo, 4096, ",");
            if(count($tupla)>=3)
            {
                for($i=0; $i<count($tupla); $i++)
                {
                    if($tupla[$i] == 'Name')
                        $arrayColumnas[0] = $i;
                    else if($tupla[$i] == 'Last Name')
                        $arrayColumnas[1] = $i;
                    else if($tupla[$i] == 'Phone Number' || $tupla[$i] == "Work's Phone Number")
                        $arrayColumnas[2] = $i;
                    else if($tupla[$i] == 'Cell Phone Number (SMS)')
                        $arrayColumnas[3] = $i;
                    else if($tupla[$i] == 'Home Phone Number')
                        $arrayColumnas[4] = $i;
                    else if($tupla[$i] == 'FAX Number 1')
                        $arrayColumnas[5] = $i;
                    else if($tupla[$i] == 'FAX Number 2')
                        $arrayColumnas[6] = $i;
                    else if($tupla[$i] == 'Email')
                        $arrayColumnas[7] = $i;
                    else if($tupla[$i] == 'Province')
                        $arrayColumnas[8] = $i;
                    else if($tupla[$i] == 'City')
                        $arrayColumnas[9] = $i;
                    else if($tupla[$i] == 'Address')
                        $arrayColumnas[10] = $i;
                    else if($tupla[$i] == 'Company')
                        $arrayColumnas[11] = $i;
                    else if($tupla[$i] == 'Contact person in your Company')
                        $arrayColumnas[12] = $i;
                    else if($tupla[$i] == "Contact person's current position")
                        $arrayColumnas[13] = $i;
                    else if($tupla[$i] == 'Notes')
                        $arrayColumnas[14] = $i;
                }
                if(isset($arrayColumnas[0]) && isset($arrayColumnas[1]) && isset($arrayColumnas[2]))
                {
                    //Paso 2: Obtener Datos (Validacion que esten llenos los mismos de las cabeceras)
                    $count = 2;
                    while ($tupla = fgetcsv($hArchivo, 4096,","))
                    {
                        if(is_array($tupla) && count($tupla)>=3)
                        {
                                $Name           = $tupla[$arrayColumnas[0]];
                                if($Name == '')
                                    return _tr("Can't exist a Name empty. Line").": $count. - ". _tr("Please read the lines in the footer");

                                $LastName       = $tupla[$arrayColumnas[1]];
                                if($LastName == '')
                                    return _tr("Can't exist a Last Name empty. Line").": $count. - ". _tr("Please read the lines in the footer");

                                $PhoneNumber    = $tupla[$arrayColumnas[2]];
                                if($PhoneNumber == '')
                                    return _tr("Can't exist a Phone Number/Work's Phone Number empty. Line").": $count. - ". _tr("Please read the lines in the footer");
                                if (!preg_match('/^[\*|#]*[[:digit:]]*$/', $PhoneNumber)) {
                                    return _tr("Invalid phone number/Work's phone number . Line").": $count. - ". _tr("Please read the lines in the footer");
                                }
                        }
                        $count++;
                    }
                    return true;
                }else return _tr("Verify the header") ." - ". _tr("At minimum there must be the columns").": \"Name\", \"Last Name\", \"Work's Phone Number\"";
            }else return _tr("Verify the header") ." - ". _tr("Incomplete Columns");
        }else return _tr("The file is incorrect or empty") .": $sFilePath";
    }
    
    
    /* obtenemos la imagen guardada con el nombre temporal, para mostrarla en el preview
    */
    function getImagePreview($picture){
        $imgPath='';

        if(!empty($picture)){
            $picture=basename($picture);
            $imgPath="{$this->pathImageContact}/$picture";
        }
        
        $imgDefault = "/var/www/html/web/_common/images/Icon-user.png";
        
        // Creamos la imagen a partir de un fichero existente
        if(file_exists($imgPath) && $imgPath!=''){
            $ext = pathinfo($imgPath, PATHINFO_EXTENSION);
            Header("Content-type: image/$ext");
            $im = file_get_contents($imgPath);
            echo $im;
            //print($imgPath);
        }else{
            Header("Content-type: image/png");
            $im = file_get_contents($imgDefault);
            echo $im;
            //print($imgDefault);
        }
        return;
    }
    
    
/************************ funcion para la llamar al contacto **************************************************************************************************************/

    function Call2Phone($extension_to_call,$channel, $exten, $context, $callerid, $code)
    {
        //validamos $extension_to_call, $context, $callerid
        
        if (count(preg_split("/[\r\n]+/", $extension_to_call)) > 1){
            $this->errMsg=_tr("Invalid parameter");
            return false;
        }
        
        if (count(preg_split("/[\r\n]+/", $context)) > 1){
            $this->errMsg=_tr("Invalid parameter");
            return false;
        }
        
        if (count(preg_split("/[\r\n]+/", $callerid)) > 1){
            $this->errMsg=_tr("Invalid parameter");
            return false;
        }
        
        $context="$code-$context";
        $callerid="$callerid <$exten>";
        
        $astMang=AsteriskManagerConnect($errorM);
        if($astMang==false){
            $this->errMsg=$errorM;
            return false;
        } else{
            $salida = $astMang->Originate($channel,
                       $extension_to_call, $context, $priority=1,
                       $application=NULL, $data=NULL,
                       $timeout=NULL, $callerid);
            $astMang->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                return true;
            }else
                return false;
        }
        return false;
    }  
    
/******************* funcion para transferir la llamada *******************************************************************/
    function TranferCall($extension_to_transfer,$dial, $context, $code)
    {
        //validamos $extension_to_transfer, $context, $callerid
        
        if (count(preg_split("/[\r\n]+/", $extension_to_transfer)) > 1){
            $this->errMsg=_tr("Invalid parameter");
            return false;
        }
        
        if (count(preg_split("/[\r\n]+/", $context)) > 1){
            $this->errMsg=_tr("Invalid parameter");
            return false;
        }
        
        if (count(preg_split("/[\r\n]+/", $dial)) > 1){
            $this->errMsg=_tr("Invalid parameter");
            return false;
        }
        
        $context="$code-$context";
        exec("/usr/sbin/asterisk -rx 'core show channels concise' | grep ^".escapeshellarg($dial),$arrConsole,$flagStatus);
        if($flagStatus == 0){
            foreach($arrConsole as $data){
                $arrData = explode("!",$data);
                $channel= explode("-",$arrData[0]);
                
                if($channel[0]==$dial){
                    $channel_to_transfer=$arrData[12];
                    $astMang=AsteriskManagerConnect($errorM);
                    if($astMang==false){
                        $this->errMsg=$errorM;
                        return false;
                    }else{
                        $salida = $astMang->Redirect($channel_to_transfer, "", $extension_to_transfer, $context, $priority=1);
                        $astMang->disconnect();
                        if (strtoupper($salida["Response"]) != "ERROR") {
                            return true;
                        }else
                            return false;
                    }    
                }
            }
            $this->errMsg="Can't find the channel";
            return false;
        }
        $this->errMsg="Can't find the channel";
        return false;
    }  
    
}
?>
