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

class paloContact{

    public $_DB;
    private $errMsg;
    private $idUser;

    public function paloContact(&$pDB,$idUser){
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }

        $this->idUser=$idUser;
    }


    function getErrorMsg(){
        return $this->errMsg;
    }

    /* funcion que agrega un nuevo contacto a la tabla ("acl_user")
    */
    
    function addContact($arrayData){

        $data = array($this->idUser, $arrayData['contact_type'], $arrayData['first_name'], $arrayData['last_name'], $arrayData['work_phone_number'], $arrayData['cell_phone_number'],$arrayData['home_phone_number'], $arrayData['fax_number_1'], $arrayData['fax_number_2'], $arrayData['email'], $arrayData['province'], $arrayData['city'], $arrayData['address'], $arrayData['company'], $arrayData['contact_person'], $arrayData['contact_person_position'], $arrayData['notes']);

        $query = "insert into contacts (iduser, status, name, last_name, work_phone, cell_phone, home_phone, fax1, fax2, email, province, city, address, company, company_contact, contact_rol, notes) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $result=$this->_DB->genQuery($query,$data);
        $last_id = $this->_DB->getLastInsertId();

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return $last_id;
            }
        
    }

    /* funcion que realiza un update del contacto, para subir la foto del usuario,
    siempre y cuando haya sido creado.
    se realiza un update porque necesito obtener el id del ultimo usuario creado para 
    poder asignarle un nombre a la imagen que haya sido subida.
    */
    
    function updatePicture($idNewContact, $imgUser){
        $data = array($imgUser, $idNewContact);
        $query = "update contacts set picture=? where id=?";
        $result=$this->_DB->genQuery($query,$data);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return true;
            }
    }
    
    /* funcion que obtiene el total de los contactos internos pertenicentes
       a mi organizacion de la tabla ("acl_user")
    */

    function getNumberOfContactsInternal($validatedfilters){
    // cuenta los usuarios de la tabla acl_user segun su filtros 
        global $arrCredentials;
        
        $data = array($arrCredentials['id_organization']);
        $query="select count(acu.id) from acl_user acu ".
                    "join acl_group acg on acu.id_group = acg.id ".
                    "join organization org on acg.id_organization = org.id ".
                        "where org.id=? ";
        
        if(isset($validatedfilters['filter_value'])){
            if($validatedfilters['filter']=="name"){
                $field = "name";
            }elseif($validatedfilters['filter']=="extension"){
                $field = "extension";
            }else{
                return false;
            }
            
            $valueField = $validatedfilters['filter_value'];
            $data[]="$valueField%";
            $query .="AND acu.$field LIKE ?";
        }
        
        $result=$this->_DB->getFirstRowQuery($query,false,$data);

        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return $result[0];
            }
    }
    
    /* funcion que obtiene los contactos internos pertenicentes a mi organizacion
       tabla ("acl_user")
    */
    
    function getIntrnalContacts($limit, $offset, $validatedfilters){
        global $arrCredentials;
        $data = array($arrCredentials['id_organization']);
        $query="select acu.id, acu.name, acu.extension, acu.username from acl_user acu ".
                    "join acl_group acg on acu.id_group = acg.id ".
                    "join organization org on acg.id_organization = org.id ".
                        "where org.id=? ";
                        
        if(isset($validatedfilters['filter_value'])){
            if($validatedfilters['filter']=="name"){
                $field = "name";
            }elseif($validatedfilters['filter']=="extension"){
                $field = "extension";
            }else{
                return false;
            }
            
            $valueField = $validatedfilters['filter_value'];
            $data[]="$valueField%";
            $query .="AND acu.$field LIKE ?";
        }
        
        
        $data[]=$limit;
        $data[]=$offset;
        $query .="ORDER BY acu.name ASC limit ? offset ?";
        
        
        $result=$this->_DB->fetchTable($query,true,$data);
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
            return $result;
        }
    }
    
    /* funcion que obtiene el total de contactos que hayan sido creados
    por el usuario logueado + los usuarios publicos creados por otros usuarios
    de mi organizacion estos contactos son los externos (tabla "contacs")
    */
    
    function getNumberOfContactsExternal($validatedfilters){
    // cuenta los usuarios de la tabla contacts 
        global $arrCredentials;
        $flag= false;
        $data1[] = $this->idUser;
        $data2[] =$arrCredentials['id_organization'];
        $data2[] =$this->idUser;
        
        $query0="Select q1.id + q2.id FROM ";
        
        $query1="(select count(id) as id from contacts where iduser=? ";
                    
        $query2=" (select count(c.id) as id from contacts c where c.iduser IN ".
                        "(select acu.id from acl_user acu ".
                            "join acl_group acg on acu.id_group = acg.id ".
                                "WHERE acg.id_organization = ? AND acu.id!=?) ".
                        "AND c.status='isPublic' ";
    
        if(isset($validatedfilters['filter_value'])){
            if($validatedfilters['filter']=="name"){
                $field = "name";
            }elseif($validatedfilters['filter']=="extension"){
                $field = "extension";
            }else{
                return false;
            }
            
            $valueField = $validatedfilters['filter_value'];
            $data1[]="$valueField%";
            $data2[]="$valueField%";
            $query1 .="AND $field LIKE ?) as q1, ";
            $query2 .="AND $field LIKE ?) as q2";
            $flag= true;
        }else{
            $query1 .=") as q1, ";
            $query2 .=") as q2";
        }      

        $query=$query0.$query1.$query2;
        $data=$data1;
        foreach($data2 as $value){
            $data[]=$value;
        }
        
        $result=$this->_DB->getFirstRowQuery($query,false,$data);

        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return $result[0];
            }
    }
    
    /* funcion que obtiene los contactos del usuario logueado + los
    usuarios publicos creados por otros usuarios de mi organizacion
    estos contactos son los externos (tabla "contacs")
    */
    
    function getExternalContactsByUser($limit, $offset, $validatedfilters){
        global $arrCredentials;
        $flag= false;
        $data1[] = $this->idUser;
        $data2[] =$arrCredentials['id_organization'];
        $data2[] =$this->idUser;
        
        $query1="(select id, iduser, concat (name,' ',last_name) as name, work_phone, ".
                    "email as username, picture, status from contacts where iduser=? ";
                    
        $query2=" UNION (select c.id, c.iduser, concat(c.name,' ',c.last_name) as name, c.work_phone, ".
                    "c.email as username, c.picture, c.status from contacts c where c.iduser IN ".
                        "(select acu.id from acl_user acu ".
                            "join acl_group acg on acu.id_group = acg.id ".
                                "WHERE acg.id_organization = ? AND acu.id!=?) ".
                        "AND c.status='isPublic' ";
    
        if(isset($validatedfilters['filter_value'])){
            if($validatedfilters['filter']=="name"){
                $field = "name";
            }elseif($validatedfilters['filter']=="extension"){
                $field = "extension";
            }else{
                return false;
            }
            
            $valueField = $validatedfilters['filter_value'];
            $data1[]="$valueField%";
            $data2[]="$valueField%";
            $query1 .="AND $field LIKE ?) ";
            $query2 .="AND $field LIKE ?) ";
            $flag= true;
        }else{
            $query1 .=")";
            $query2 .=")";
        }      

        $query=$query1.$query2;
        $data=$data1;
        foreach($data2 as $value){
            $data[]=$value;
        }
        
        $data[]=$limit;
        $data[]=$offset;
        $query .="ORDER BY name ASC limit ? offset ?";
        
        
        $result=$this->_DB->fetchTable($query,true,$data);
        
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return $result;
            }
    }
    
    /* funcion para obtener los datos que seran mostrados en el formulario
    el cual procederán a ser editados
    */
    
    function getExternalContactForEdit($idContact){
        $data = array($idContact, $this->idUser);
        $query="select name, last_name, work_phone, cell_phone, home_phone, ".
                    "fax1, fax2, email, province, city, address, company, ".
                        "company_contact, contact_rol, notes, picture, status ".
                            "from contacts where id=? and iduser=?";
                            
        $result=$this->_DB->getFirstRowQuery($query,true,$data);

        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return $result;
            }
    }
    
    /*verificamos si el contacto existe y es perteneciente al usuario logueado
    para proceder a editarlo o eliminarlo*/

    function existContact($idContact){
        $data = array($idContact, $this->idUser);
        $query="select count(id) from contacts where id=? and iduser=?";
        
        $result=$this->_DB->getFirstRowQuery($query,false,$data);

        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return $result[0];
            }
    }
    
    /* funcion para realizar la edicion del contacto externo*/
    
    function editContact($arrayData){

        $data = array($arrayData['contact_type'], $arrayData['first_name'], $arrayData['last_name'], $arrayData['work_phone_number'], $arrayData['cell_phone_number'],$arrayData['home_phone_number'], $arrayData['fax_number_1'], $arrayData['fax_number_2'], $arrayData['email'], $arrayData['province'], $arrayData['city'], $arrayData['address'], $arrayData['company'], $arrayData['contact_person'], $arrayData['contact_person_position'], $arrayData['notes'], $arrayData['id']);

        $query = "update contacts set status=?, name=?, last_name=?, work_phone=?, ".
                    "cell_phone=?, home_phone=?, fax1=?, fax2=?, email=?, province=?, ".
                    "city=?, address=?, company=?, company_contact=?, contact_rol=?, notes=? ".
                    "where id=?";

        $result=$this->_DB->genQuery($query,$data);
        
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return true;
            }
        
    }
    
    /* funcion para eliminar los contactos externos seleccionados del contacto externo
    se borraran solo contactos creados por el usuario logueado
    */
    
    function deleteContacts($arrayData){
    
        if(!is_array($arrayData)){
            $this->errMsg = _tr("Contact does not exist");
            return false;
        }
        
        if(count($arrayData)<=0){
            $this->errMsg = _tr("Contact does not exist");
            return false;
        }
    
        $q=implode(",",array_fill(0,count($arrayData),"?"));
        $arrayData[]=$this->idUser;
        
        //$strIds = implode(",", $arrayData);
        $query = "delete from contacts where id IN ($q) AND iduser=?";

        $result=$this->_DB->genQuery($query,$arrayData);
        
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return true;
            }
        
    }

    
    /* para borrar un contacto, primero deberemos borrar la imagen,
    para esto primero necesitamos devolver los nombres de las imagenes que estan
    en la tabla "contacts".
    
    funcion que devuelve el nombre de las imagenes de todos los contactos
    que fueron seleccionados para ser eliminados.
    */
    
    function getContactsImages($arrayData){
    
        if(!is_array($arrayData)){
            $this->errMsg = _tr("Contact does not exist");
            return false;
        }
        
        if(count($arrayData)<=0){
            $this->errMsg = _tr("Contact does not exist");
            return false;
        }
    
        $q=implode(",",array_fill(0,count($arrayData),"?"));
        $arrayData[]=$this->idUser;
        
        //$strIds = implode(",", $arrayData);
        $query = "select picture from contacts where id IN ($q) AND iduser=?";

        $result=$this->_DB->fetchTable($query,true,$arrayData);
        
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return $result;
            }
        
    }
    
    /*funcion para verificar si existe un contacto con los mismos datos que se van a subir en el 
    archivo csv. siempre verificando que pertenezcan al usuario logueado
    */
    function existContacts($name, $last_name, $telefono)
    {
        $query =     " SELECT count(id) as total FROM contacts "
                    ." WHERE name=? and last_name=?"
                    ." and work_phone=? AND iduser=?";
        $arrParam = array($name,$last_name,$telefono,$this->idUser);
        
        $result=$this->_DB->getFirstRowQuery($query,false,$arrParam);

        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return $result[0];
            }
    }
     
    /* funcion par insertar los contactos desde un file csv
    */
    function addContactCsv($data)
    {
        $queryInsert = "insert into contacts(name,last_name,work_phone,cell_phone,home_phone,fax1,fax2,email,province,city,iduser,address,company,company_contact,contact_rol,notes) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $result = $this->_DB->genQuery($queryInsert, $data);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
            return true;
            }
    }
    
    
    /* funcion para obtener el parent id del contacto seleccionado
    */
    
    function getIdParent($idContact){
        $data = array($idContact);
        
        $query = "select iduser from contacts where id=?";

        $result=$this->_DB->getFirstRowQuery($query,false,$data);
        
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return $result[0];
            }
        
    }
    
/****************************** funcion para llamar y transferencia*************************************************************************************************************/    
    
    /* funcion que obtiene le extension del contacto interno
       tabla ("acl_user")
    */
    
    function getExtension($idContact){
        global $arrCredentials;
        
        $data = array($arrCredentials['id_organization'], $idContact);
        $query="select acu.extension from acl_user acu ".
                    "join acl_group acg on acu.id_group = acg.id ".
                    "join organization org on acg.id_organization = org.id ".
                        "where org.id=? AND acu.id=?";

        $result=$this->_DB->getFirstRowQuery($query,false,$data);
       
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return $result[0];
            }
    }
    
    /* funcion que obtiene lel numero de telefono a cual se va a realizar la 
    llamada
    */
    
    function getPhone($idContact){
        global $arrCredentials;
        
        $data = array($idContact);

        $query="select work_phone from contacts where id=?";
       
        $result=$this->_DB->getFirstRowQuery($query,false,$data);
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                return $result[0];
            }
    }        

    function Obtain_Protocol_from_Ext($id_user)
    {
        global $arrCredentials;

        $data = array($id_user,$arrCredentials['domain']);
        $query="select e.context, e.exten, e.dial, e.clid_name ".
                    "FROM extension e JOIN acl_user u ON e.exten=u.extension ".
                       "WHERE u.id=? AND e.organization_domain=?";

        $dataExt=$this->_DB->getFirstRowQuery($query,true,$data);
       
        if($dataExt===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
                $data = array($arrCredentials['id_organization']);
                $query="select code from organization where id=?";
                $code=$this->_DB->getFirstRowQuery($query,true,$data);
                if($code===FALSE){
                    $this->errMsg = $this->_DB->errMsg;
                    return false;
                }else{
                    $dataExt['code']=$code["code"];
                    return $dataExt;
                }
            }
    }
    

   
}
?>
