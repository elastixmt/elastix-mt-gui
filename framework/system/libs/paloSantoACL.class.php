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
  $Id: paloSantoACL.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ 
  $Id: paloSantoACL.class.php,v 3.0 2012/09/01 21:31:55 Rocio Mera rmera@palosanto.com Exp $ */

$elxPath="/usr/share/elastix";
include_once "$elxPath/libs/paloSantoDB.class.php";

define('PALOACL_MSG_ERROR_1', 'Username or password is empty');
define('PALOACL_MSG_ERROR_2', 'Invalid characters found in username');
define('PALOACL_MSG_ERROR_3', 'Invalid characters found in password hash');

class paloACL {

    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function paloACL(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB = $pDB;
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
    }

   /**
     * Procedimiento para obtener el listado de los usuarios existentes en los ACL. Los usuarios
     * pertenecen a una entidad
     * Recibe como para parametros el id del usuario y el id de la entidad a la que pertenece,
     * si no se especifica id_user y no se especifica entidad se devuelve todos los usuarios, si se
     * espefica entidad y no id_user todos los usuarios de una entidad, si se especifica id usuario se
     * devuelve solo dicho usuario
     *
     * @param int   $id_user    Si != NULL, indica el ID del usuario a recoger
     *
     * @return array    Listado de usuarios en el siguiente formato, o FALSE en caso de error:
     *  array(
     *      array(id, name, description),
     *      ...
     *  )
     */
	function getUsers($id_user = NULL, $id_organization = NULL, $limit = NULL, $offset = NULL)
    {
        $arr_result = FALSE;
        $where = "";
		$paging = "";
        $arrParams = null;
        if (!is_null($id_user) && !preg_match('/^[[:digit:]]+$/', "$id_user")) {
            $this->errMsg = "User ID is not numeric";
        }elseif (!is_null($id_organization) && !preg_match('/^[[:digit:]]+$/', "$id_organization")) {
            $this->errMsg = _tr("Organization ID must be numeric");
        }else {
			if(!is_null($id_user) && is_null($id_organization)){
				$where = "where u.id=?";
				$arrParams = array($id_user);
			}elseif(is_null($id_user) && !is_null($id_organization)){
				$where = "where g.id_organization=?";
				$arrParams = array($id_organization);
			}elseif(!is_null($id_user) && !is_null($id_organization)){
				$where = "where g.id_organization=? and u.id=?";
				$arrParams = array($id_organization,$id_user);
			}

			if(!is_null($limit) && !is_null($offset)){
				$paging = "limit $limit offset $offset";
			}
            $this->errMsg = "";

            $sPeticionSQL = "SELECT u.id, u.username, u.name, u.md5_password, g.id_organization, u.extension, u.fax_extension, u.id_group FROM acl_user as u JOIN  acl_group as g on u.id_group=g.id $where $paging";
            $arr_result = $this->_DB->fetchTable($sPeticionSQL,false,$arrParams);
            if (!is_array($arr_result)) {
                $arr_result = FALSE;
                $this->errMsg = $this->_DB->errMsg;
            }
        }
        return $arr_result;
    }
    
    function getUsers2($id_user = NULL, $id_organization = NULL, $limit = NULL, $offset = NULL)
    {
        $arr_result = FALSE;
        $where = "";
        $paging = "";
        $arrParams = null;
        if (!is_null($id_user) && !preg_match('/^[[:digit:]]+$/', "$id_user")) {
            $this->errMsg = "User ID is not numeric";
        }elseif (!is_null($id_organization) && !preg_match('/^[[:digit:]]+$/', "$id_organization")) {
            $this->errMsg = _tr("Organization ID must be numeric");
        }else {
            if(!is_null($id_user) && is_null($id_organization)){
                $where = "where u.id=?";
                $arrParams = array($id_user);
            }elseif(is_null($id_user) && !is_null($id_organization)){
                $where = "where g.id_organization=?";
                $arrParams = array($id_organization);
            }elseif(!is_null($id_user) && !is_null($id_organization)){
                $where = "where g.id_organization=? and u.id=?";
                $arrParams = array($id_organization,$id_user);
            }

            if(!is_null($limit) && !is_null($offset)){
                $paging = "limit $limit offset $offset";
            }
            $this->errMsg = "";

            $sPeticionSQL = "SELECT u.id, u.username, u.name, u.md5_password, g.id_organization, u.extension, u.fax_extension, u.id_group FROM acl_user as u JOIN  acl_group as g on u.id_group=g.id $where $paging";
            $arr_result = $this->_DB->fetchTable($sPeticionSQL,true,$arrParams);
            if (!is_array($arr_result)) {
                $arr_result = FALSE;
                $this->errMsg = $this->_DB->errMsg;
            }
        }
        return $arr_result;
    }
    
	function getUserPicture($id_user){
		$arr_result = FALSE;
		if (!preg_match('/^[[:digit:]]+$/', "$id_user")) {
            $this->errMsg = _tr("User ID must be numeric");
		}else{
			$query="SELECT picture_type,picture_content from acl_user where id=?";
			$arr_result = $this->_DB->getFirstRowQuery($query,true,array($id_user));
			if ($arr_result===false || count($arr_result)==0) {
				$this->errMsg = $this->_DB->errMsg;
			}
		}
        return $arr_result;
	}

	function setUserPicture($id_user,$picture_type,$picture_content){
		$result = FALSE;
		if (!preg_match('/^[[:digit:]]+$/', "$id_user")) {
            $this->errMsg = _tr("User ID must be numeric");
		}else{
			$query="update acl_user set picture_type=?,picture_content=? where id=?";
			$result = $this->_DB->genQuery($query,array($picture_type,$picture_content,$id_user));
		}
        return $result;
	}

	/**
	 * Procedimiento para obtener los datos de la extension usada por el usuario dentro de
	   asterisk
	 * @param int $idUser Id del usuario del que se quiere obtener los datos de su extension
	 * @return array $ext Devuelte un arreglo donde esta el numero de la extension, la tegnologia usada y el nombre del dispositivo usado
	*/
	function getExtUser($id_user){
		$arr_result2=array();
		$pDB2=new paloDB(generarDSNSistema("asteriskuser", "elxpbx"));
		if (!preg_match('/^[[:digit:]]+$/', "$id_user")) {
            $this->errMsg = _tr("User ID must be numeric");
		}else{
			$query="SELECT a.extension, (Select domain from organization o where o.id=g.id_organization) FROM acl_user as a JOIN  acl_group as g on a.id_group=g.id where a.id=?";
			$arr_result = $this->_DB->getFirstRowQuery($query,false,array($id_user));
			if ($arr_result===false){
				$this->errMsg = _tr("Can't get extension user").$this->_DB->errMsg;
			}elseif(count($arr_result)==0) {
				$this->errMsg = _tr("User doesn't have a associated extension");
			}else{
				$query2="SELECT id, exten, organization_domain, tech, dial, voicemail, device FROM extension where exten=? and  organization_domain=?";
				$arr_result2 = $pDB2->getFirstRowQuery($query2,true,array($arr_result[0],$arr_result[1]));
				if (!is_array($arr_result2) || count($arr_result2)==0) {
					$this->errMsg = _tr("Can't get extension user").$pDB2->errMsg;
				}
			}
		}
		return $arr_result2;
	}

	/**
		funcion para obtener la extension del usuario dado su username
	*/
	function getUserExtension($username)
    {
        $extension = null;
        if (is_null($username)) {
            $this->errMsg = _tr("Username is not valid");
        } else {
            $this->errMsg = "";
            $sPeticionSQL = "SELECT extension FROM acl_user WHERE username = ?";
            $result = $this->_DB->getFirstRowQuery($sPeticionSQL, FALSE, array($username));
            if ($result && is_array($result) && count($result)>0) {
               $extension = $result[0];
            }else $this->errMsg = $this->_DB->errMsg;
        }
        return $extension;
    }

    function getUserByUsername($username){
        $query="SELECT u.id,u.username,u.name,u.extension,u.fax_extension,g.id_organization FROM acl_user u JOIN acl_group g ON u.id_group=g.id WHERE username=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($username));
        if($result==false){
            $this->errMsg=($result===false)?_tr("DATABASE ERROR"):_tr('User does not exist');
            return false;
        }else{
            return $result;
        }
    }

    /**
     * Procedimiento para obtener el listado de los usuarios existentes en los ACL. Se
     * especifica un limite y un offset para obtener la data paginada.
     *
     * @param int   $limit    Si != NULL, indica el número de maximo de registros a devolver por consulta
     * @param int   $offset   Si != NULL, indica el principio o desde donde parte la consulta
     *
     * @return array    Listado de usuarios en el siguiente formato, o FALSE en caso de error:
     *  array(
     *      array(id, name, description),
     *      ...
     *  )
     */
    function getUsersPaging($limit = NULL, $offset = NULL, $id_organization = null,$username = null)
    {
        $arrParams = null;
        $where = array();
        $paging = "";
        $arr_result = FALSE;
        if (!is_null($limit) && !preg_match('/^[[:digit:]]+$/', "$limit")) {
            $this->errMsg = _tr("Limit must be numeric");
            return FALSE;
        }
        if (!is_null($offset) && !preg_match('/^[[:digit:]]+$/', "$offset")) {
            $this->errMsg = _tr("Offset must be numeric");
            return FALSE;
        }
        if(!is_null($limit) && !is_null($offset)){
            $paging = "limit $limit offset $offset";
        }

        if(!empty($id_organization)){
            $where[] = " g.id_organization=?";
            $arrParams[] = $id_organization;
        }
        if(!empty($username)){
            $where[] = " UPPER(a.username) like ?";
            $arrParams[] = "%$username%";
        }

        $this->errMsg = "";
        $sPeticionSQL = "SELECT a.id, a.username, a.name, a.md5_password, g.id_organization, a.extension, a.fax_extension, a.id_group FROM acl_user as a JOIN  acl_group as g on a.id_group=g.id " ;
        if(count($where)>0){
            $sPeticionSQL .=" WHERE ".implode(" AND ",$where);
        }
        $sPeticionSQL .=" $paging";

        $arr_result = $this->_DB->fetchTable($sPeticionSQL,false,$arrParams);
        if (!is_array($arr_result)) {
            $arr_result = FALSE;
            $this->errMsg = $this->_DB->errMsg;
        }
        return $arr_result;
    }

    /**
     * Procedimiento para obtener el listado de los grupos existentes en los ACL. Se
     * especifica un limite y un offset para obtener la data paginada.
     *
     * @param int   $limit    Si != NULL, indica el número de maximo de registros a devolver por consulta
     * @param int   $offset   Si != NULL, indica el principio o desde donde parte la consulta
     *
     * @return array    Listado de usuarios en el siguiente formato, o FALSE en caso de error:
     *  array(
     *      array(id, name, description),
     *      ...
     *  )
     */
    function getGroupsPaging($limit = NULL, $offset = NULL, $id_organization = NULL)
    {
		$arrParams = array();
		$where = "";
		$paging = "";
		$arr_result = FALSE;
        if (!is_null($limit) && !preg_match('/^[[:digit:]]+$/', "$limit")) {
            $this->errMsg = _tr("Limit must be numeric");
            return FALSE;
        }
        if (!is_null($offset) && !preg_match('/^[[:digit:]]+$/', "$offset")) {
            $this->errMsg = _tr("Offset must be numeric");
            return FALSE;
        }
		if(!is_null($limit) && !is_null($offset)){
			$paging = "limit $limit offset $offset";
		}


		if(!is_null($id_organization) && !preg_match('/^[[:digit:]]+$/', "$id_organization")){
            $this->errMsg = _tr("Organization ID must be numeric");
            return FALSE;
		}elseif(!is_null($id_organization)){
			$where = "where id_organization=?";
			$arrParams = array($id_organization);
		}
		
        $this->errMsg = "";
        $sPeticionSQL = "SELECT id, name, description, id_organization FROM acl_group $where ORDER BY id_organization $paging";

        $arr_result = $this->_DB->fetchTable($sPeticionSQL,false,$arrParams);
        if (!is_array($arr_result)) {
            $arr_result = FALSE;
            $this->errMsg = $this->_DB->errMsg;
        }
        return $arr_result;
    }

    /**
     * Procedimiento para obtener la cantidad de usuarios existentes en los ACL.
     *
     * @return int    Cantidad de usuarios existentes, o NULL en caso de error:
     */
    function getNumUsers($id_organization = NULL,$username = null)
    {
        $this->errMsg = "";
        $arrParams = null;
        $where = array();

        if(!empty($id_organization)){
            $where[] = " g.id_organization=?";
            $arrParams[] = $id_organization;
        }
        if(!empty($username)){
            $where[] = " UPPER(a.username) like ?";
            $arrParams[] = "%$username%";
        }

        $sPeticionSQL = "SELECT count(*) FROM acl_user as a JOIN  acl_group as g on a.id_group=g.id";
        if(count($where)>0){
            $sPeticionSQL .=" WHERE ".implode(" AND ",$where);
        }
        $data = $this->_DB->getFirstRowQuery($sPeticionSQL,false,$arrParams);
        if (!is_array($data) || count($data) <= 0) {
            $this->errMsg = $this->_DB->errMsg;
            return FALSE;
        }
        return $data[0];
    }

    /**
     * Procedimiento para obtener la cantidad de grupos existentes en los ACL.
     *
     * @return int    Cantidad de usuarios existentes, o NULL en caso de error:
     */
    function getNumGroups($id_organization = NULL)
    {
        $this->errMsg = "";
		$arrParams = null;
		$where = "";

		if(!is_null($id_organization) && !preg_match('/^[[:digit:]]+$/', "$id_organization")){
            $this->errMsg = _tr("Organization ID must be numeric");
            return FALSE;
		}elseif(!is_null($id_organization)){
			$where = "where id_organization=?";
			$arrParams = array($id_organization);
		}

        $sPeticionSQL = "SELECT count(*) cnt FROM acl_group $where";

        $data = $this->_DB->getFirstRowQuery($sPeticionSQL,true,$arrParams);
        if (!is_array($data) || count($data) <= 0) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $data['cnt'];
    }

    /**
     * Procedimiento para crear un nuevo usuario con hash MD5 de la clave ya proporcionada.
     *
     * @param string    $username       Login del usuario a crear
     * @param string    $name    Descripción del usuario a crear
     * @param string    $md5_password   Hash MD5 de la clave a asignar (32 dígitos y letras min a-f)
     *
     * @return bool     VERDADERO si el usuario se crea correctamente, FALSO en error
     */
	// 1) debo validar que el grupo exista y que dicho grupo pertenezca a la organizacion
	// 2) no puede ser un grupo que pertenezca a la organization con id 1, ya que esta es solo una organizacion
	//    de administracion y su unico usuario es el superadmin
    function createUser($username, $name, $md5_password, $id_group, $extension,  $fax_extension, $idOrganization)
    {
        $bExito = FALSE;
        if ($username == "") {
            $this->errMsg = _tr("Username can't be empty");
        } elseif(!preg_match("/^[[:alnum:]]+([_]?[[:alnum:]]+[_]?)*@[[:alnum:]]+([\._\-]?[[:alnum:]]+)*(\.[[:alnum:]]{2,4})+$/", $username)){
            $this->errMsg = _tr("Username is not valid")._tr("Permited characters are: letters a-z, numbers (0-9) and underscore");
        }else{
            if ( !$name ) $name = $username;
            // Verificar que el nombre de usuario no existe previamente
            $id_user = $this->getIdUser($username);
            if ($id_user !== FALSE) {
                $this->errMsg = _tr("Username already exists");
            } elseif ($this->errMsg == "") {
            //El id_group no puede ser el grupo del superadmin, superadmin_group=1
                if(!preg_match("/^[[:digit:]]+$/","$id_group") || $id_group=="1"){ // 1)
                    $this->errMsg = _tr("Grout ID is not valid");
                    return false;
                }

            //El id_organization no puede ser 1
                if(!preg_match("/^[[:digit:]]+$/","$idOrganization") || $idOrganization=="1"){ // 2)
                    $this->errMsg = _tr("Organization ID is not valid");
                    return false;
                }

            //validar que el grupo exista y que pertenezca a la misma organization que el usuario
                $arrGroup=$this->getGroups($id_group, $idOrganization);
                if($arrGroup==false){ // 2)
                    $this->errMsg = _tr("Group dosen't exist");
                    return false;
                }

                $sPeticionSQL = "INSERT into acl_user (username,name,md5_password,id_group,extension,fax_extension) VALUES (?,?,?,?,?,?)";
                $arrParam = array($username,$name,$md5_password,$id_group,$extension, $fax_extension);
                if ($this->_DB->genQuery($sPeticionSQL,$arrParam)) {
                    $bExito = TRUE;
                } else {
                    $this->errMsg = $this->_DB->errMsg;
                }
            }
        }
        return $bExito;
    }

    /**
     * Procedimiento para modificar al usuario con el ID de usuario especificado, para darle una nueva extension, fax extension y description
     *
     * @param int       $id_user        Indica el ID del usuario a modificar
     * @param string    $name           nombre descriptivo del usuario
     * @param string    $extension      extension telefonica del usuario
     * @param string    $fax_extension  extensión de fax del usuario
     *
     * @return bool VERDADERO si se modifico correctamente el usuario, FALSO si ocurre un error.
     */
    function updateUser($id_user, $name, $extension, $fax_extension)
    {
        $bExito = FALSE;

		if (!preg_match("/^[[:digit:]]+$/", "$id_user")) {
            $this->errMsg = _tr("User ID must be numeric");
        } else {

			// Verificar que el usuario indicado existe
			$tuplaUser = $this->getUsers($id_user);
			if (!is_array($tuplaUser)) {
				$this->errMsg =_tr("On having checked user's existence - ").$this->errMsg;
			} else if (count($tuplaUser) == 0) {
				$this->errMsg = _tr("User doesn't exist");
			} else {
				$bContinuar = TRUE;
			}

			if ( !$name ) $name = $tuplaUser[0][1];

			if ($bContinuar) {
				// Proseguir con la modificación del usuario
				$sPeticionSQL = "UPDATE acl_user SET name = ?, extension  = ?, fax_extension  = ? WHERE id = ?";
				$arrParam = array($name,$extension,$fax_extension,$id_user);
				if ($this->_DB->genQuery($sPeticionSQL,$arrParam)) {
					$bExito = TRUE;
				} else {
					$this->errMsg = $this->_DB->errMsg;
				}
			}
		}
        return $bExito;
    }

    /**
     * Procedimiento para cambiar la clave de un usuario, dado su ID de usuario.
     *
     * @param int       $id_user        ID del usuario para el que se cambia la clave
     * @param string    $md5_password   Nuevo hash MD5 a asignar al usuario
     *
     * @return bool VERDADERO si se modifica correctamente el usuario, FALSO si ocurre un error.
     */
    function changePassword($id_user, $md5_password)
    {
        $bExito = FALSE;
        if (!preg_match("/^[[:digit:]]+$/", "$id_user")) {
            $this->errMsg = _tr("User ID must be numeric");
        } else if (!preg_match("/^[[:digit:]a-f]{32}$/", $md5_password)) {
            $this->errMsg = _tr("Password is not a valid MD5 hash");
        } else {
             if ($this->errMsg == "") {
				$sPeticionSQL = "UPDATE acl_user SET md5_password = ? WHERE id = ?";
				if ($this->_DB->genQuery($sPeticionSQL,array($md5_password,$id_user))) {
					$bExito = TRUE;
				} else {
					$this->errMsg = $this->_DB->errMsg;
				}
			}
        }

        return $bExito;
    }
    
    /**
     * Procedimiento para borrar un usuario ACL, dado su ID numérico de usuario
     *
     * @param int   $id_user    ID del usuario que debe eliminarse
     *
     * @return bool VERDADERO si el usuario puede borrarse correctamente
     */
    function deleteUser($id_user)
    {
        $bExito = FALSE;
        if (!preg_match('/^[[:digit:]]+$/', "$id_user") || $id_user=="1") {
            $this->errMsg = _tr("User ID is not valid");
        } else {
            $this->errMsg = "";
            $query = "DELETE FROM acl_user WHERE id=?";
            $bExito = $this->_DB->genQuery($query,array($id_user));
            if (!$bExito) {
                $this->errMsg = $this->_DB->errMsg;
            }
		}
        return $bExito;
    }

    /**
     * Procedimiento para averiguar el ID de un usuario, dado su login (nombre@dominio).
     *
     * @param string    $login    Login del usuario para buscar ID
     *
     * @return  mixed   Valor entero del ID de usuario, o FALSE en caso de error o si el usuario no existe
     */
    function getIdUser($username)
    {
        $idUser = FALSE;
        $this->errMsg = '';
        $sPeticionSQL = "SELECT id FROM acl_user WHERE username = ?";
		
        $result = $this->_DB->getFirstRowQuery($sPeticionSQL,false,array($username));
		if (is_array($result) && count($result)>0) {
            $idUser = $result[0];
        }else
			$this->errMsg = $this->_DB->errMsg;
        return $idUser;
    }

    /**
     * Procedimiento para obtener el listado de los grupos existentes en los ACL.
     * cada organizacion tiene sus propios grupos.
     * Se recibe como parametros el id del grupo y el id de la organizacion a la que pertenece el grupo
     *
     * @param int   $id_group    Si != NULL, indica el ID del grupos a recoger
     * @param int   $id_organization   Si != NULL, indica el ID de la organization a la que pertenece el grupo
     *
     * @return array    Listado de grupos en el siguiente formato, o FALSE en caso de error:
     *  array(
     *      array(id, name, description),
     *      ...
     *  )
     */
    function getGroups($id_group = NULL, $id_organization = NULL)
    {
        $arr_result = FALSE;
        $where = "";
        $arrParams = null;
        if (!is_null($id_group) && !preg_match('/^[[:digit:]]+$/', "$id_group")) {
            $this->errMsg = _tr("Group ID must be numeric");
        }else if(!is_null($id_organization) && !preg_match('/^[[:digit:]]+$/', "$id_organization")) {
            $this->errMsg = _tr("Organization ID must be numeric");
        }else {
            if(!is_null($id_group) || !is_null($id_organization)){
                $where = "where ";
                $arrParams = array();
                if(!is_null($id_group)){
                    $where .= "id=?";
                    $arrParams[] = $id_group;
                }
                if(!is_null($id_group) && !is_null($id_organization))
                    $where .= " and ";
                if(!is_null($id_organization)){
                    $where .= "id_organization=?";
                    $arrParams[] = $id_organization;
                }
            }
            $this->errMsg = "";
            $sPeticionSQL = "SELECT id, name, description, id_organization FROM acl_group $where;";
            $arr_result = $this->_DB->fetchTable($sPeticionSQL,false,$arrParams);
            if (!is_array($arr_result)) {
                $arr_result = FALSE;
                $this->errMsg = $this->_DB->errMsg;
            }
        }
        return $arr_result;
    }

    /**
     * Procedimiento para construir un arreglo que describe el grupo al cual
     * pertenece un usuario identificado por un ID. El arreglo devuelto tiene el siguiente
     * formato:
     *  array(
     *      nombre_grupo_1  =>  id_grupo_1,
     *  )
     *
     * @param int   $id_user    ID del usuario para el cual se pide la pertenencia
     *
     * @return mixed    Arreglo que describe la pertenencia, o NULL en caso de error.
     */
    function getMembership($id_user)
    {
        $arr_resultado = NULL;
        if (!is_null($id_user) && !preg_match('/^[[:digit:]]+$/', "$id_user")) {
            $this->errMsg = _tr("User ID must be numeric");
        } else {
            $this->errMsg = "";
            $sPeticionSQL =
                "SELECT g.id, g.name ".
                "FROM acl_group as g, acl_user as u ".
                "WHERE u.id_group = g.id AND u.id = ?";
            $result = $this->_DB->getFirstRowQuery($sPeticionSQL, FALSE, array($id_user));
            if($result==false){
                $this->errMsg = ($result===false)?$this->_DB->errMsg:"User doen't belong to any group";
            }else{
                $arr_resultado[$result[1]] = $result[0];
            }
        }
        return $arr_resultado;
    }


    /**
     * Procedimiento para averiguar el ID de un grupo, dado su nombre y la entidad del grupo.
     *
     * @param string    $sNombreUser    Login del usuario para buscar ID
     *
     * @return  mixed   Valor entero del ID de usuario, o FALSE en caso de error o si el usuario no existe
     */
    function getIdGroup($sNombreGroup,$id_organization)
    {
        $idGroup = FALSE;

        if(!preg_match('/^[[:digit:]]+$/', "$id_organization")) {
            $this->errMsg = _tr("Organization ID must be numeric");
            return false;
        }

        $arrParams = array($sNombreGroup, $id_organization);

        $this->errMsg = '';
        $sPeticionSQL = "SELECT id FROM acl_group WHERE name = ? and id_organization = ?";
        $result = $this->_DB->getFirstRowQuery($sPeticionSQL, FALSE, $arrParams);
        if (is_array($result) && count($result)>0) {
            $idGroup = $result[0];
        }else $this->errMsg = $this->_DB->errMsg;
        return $idGroup;
    }
    
    /**
     * Procedimiento para asegurar que un usuario identificado por su ID pertenezca al grupo
     * identificado también por su ID. Se verifica primero que tanto el usuario como el grupo
     * existen en las tablas ACL.
     *
     * @param int   $id_user    ID del usuario que se desea agregar al grupo
     * @param int   $id_group   ID del grupo al cual se desea agregar al usuario
     *
     * @return bool VERDADERO si se puede agregar el usuario al grupo, o si ya pertenecía al grupo
     */
    function addToGroup($id_user, $id_group)
    {
        $bExito = FALSE;
        if (is_null($id_user) || is_null($id_group)) {
            $this->errMsg = _tr("User ID and Group ID can't be empty");
        }elseif(!preg_match('/^[[:digit:]]+$/', "$id_user")) {
            $this->errMsg = _tr("User ID must be numeric");
        }elseif( !preg_match('/^[[:digit:]]+$/', "$id_group") || $id_group=="1" ) {
            $this->errMsg = _tr("Group ID is not valid");
		}elseif (is_array($listaUser = $this->getUsers($id_user)) &&
            is_array($listaGrupo = $this->getGroups($id_group))) {

            if (count($listaUser) == 0) {
                $this->errMsg = _tr("User doesn't exist");
            } else if (count($listaGrupo) == 0) {
                $this->errMsg = _tr("Group doesn't exist");
            } elseif($listaGrupo[0][3]=="1") {//valido que el grupo no pertenezca a la organizacion 1
				$this->errMsg = _tr("Group ID is not valid");
			} else{
                // Verificar existencia de la combinación usuario-grupo
                $sPeticionSQL = "SELECT id FROM acl_user WHERE id = ? AND id_group = ?";
                $arrusuario = $this->_DB->fetchTable($sPeticionSQL,false,array($id_user, $id_group));
                if (!is_array($arrusuario)) {
                    // Ocurre un error de base de datos
                    $this->errMsg = $this->_DB->errMsg;
                } else if (is_array($arrusuario) && count($arrusuario) > 0) {
                    // El usuario ya pertecene al grupo el grupo - no se hace nada
                    $bExito = TRUE;
                } else {
                    // El usuario no pertenece al grupo - se debe de agregar
					// antes de agregarlo se debe verificar que el grupo al que se
					// lo quiere agregar al usuario pertenezca a la misma organizacion
					// a la que ya pertence el usuario
					$query="select count(u.id) from acl_user as u join acl_group as g on g.id=u.id_group and u.id=? and g.id_organization=?";
					$bellow=$this->_DB->getFirstRowQuery($query,false,array($id_user,$listaGrupo[0][3]));
					if($bellow[0]==1){
						$sPeticionSQL = "Update acl_user set id_group=? where id=?";
						$bExito = $this->_DB->genQuery($sPeticionSQL,array($id_group,$id_user));
						if (!$bExito) {
							// Ocurre un error de base de datos
							$this->errMsg = $this->_DB->errMsg;
						}
					}else{
						$this->errMsg = _tr("Invalid new Group");
					}
                }
            }
        }
        return $bExito;
    }

    /**
      *  Procedimiento para setear una propiedad de un usuario, dado el id del usuario,
      *  el nombre de la propiedad y el valor de la propiedad
      *  Si la propiedad ya existe actualiza el valor, caso contrario crea el nuevo registro
      *  @param integer $id del usuario al que se le quiere setear la propiedad
      *  @param string $key nombre de la propiedad
      *  @param string $value valor que tomarà la propiedad
      *  @return boolean verdadera si se ejecuta con existo la accion, falso caso contrario
    */
    function setUserProp($id,$key,$value,$category=""){
        $bQuery = "select 1 from user_properties where id_user=? and property=?";
        $bResult=$this->_DB->getFirstRowQuery($bQuery,false, array($id,$key));
        if($bResult===false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
            if(count($bResult)==0){
                $query="INSERT INTO user_properties values (?,?,?,?)";
                $arrParams=array($id,$key,$value,$category);
            }else{
                if($bResult[0]=="1"){
                $query="UPDATE user_properties SET value=? where id_user=? and property=?";
                $arrParams=array($value,$id,$key);}
            }
            $result=$this->_DB->genQuery($query, $arrParams);
            if($result==false){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }else
                return true;
        }
    }

	function getUserProp($id,$key){
        $bQuery = "select value from user_properties where id_user=? and property=?";
        $bResult=$this->_DB->getFirstRowQuery($bQuery,false, array($id,$key));
        if($bResult==false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
			return $bResult[0];
        }
    }

	//funcion usada para obtener parametros del usuario como username, fax_extesion, extension, name
	//recibe como parametros el id del usuario y el nombre del parametro que desea consultar
	function getUserParameter($id_user,$key){
		$bQuery = "select id, $key from acl_user where id_user=?";
        $bResult=$this->_DB->getFirstRowQuery($bQuery,true, array($id));
        if($bResult==false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
			return $bResult;
        }
	}
	
	/**
	 * Funcion que devuelve el id del grupo al que pertenece un usuario.
	 * Devuelve falso en caso de error
	 * @param integer $idUser
	 * @return mixed  integer -> id del grupo al que pertence el usuario 
	 *                false -> en caso de eero
	 */
	function getUserGroup($idUser){
        $query="SELECT id_group FROM acl_user WHERE id=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($idUser));
        if($result==false){
            $this->errMsg=($result===false)?_tr("DATABASE ERROR"):_tr("User doesn't exist");
            return false;
        }else{
            return $result[0];
        }
	}



    function isUserAuthorizedById($id_user, $resource_name)
    {
    //obtenemos el id del grupo al que pertecene el usuario
    $idGroup=$this->getUserGroup($id_user);
    if($idGroup==false)
        return false;
        
    //seleccionamos los recuersos a los cuales la organizacion a la que pertenece el usuario tiene acceso
    //y de eso hacemos uns interseccion con la 
    //union de las acciones permitidas por el grupo al que pertenece el usuario
    //y las acciones permitidas a el usuario
$sPeticionSQL = <<<INFO_AUTH_MODULO
    SELECT ore.id_resource FROM organization_resource ore 
            JOIN acl_group g ON g.id_organization=ore.id_organization 
            WHERE g.id=? AND ore.id_resource=? AND ore.id_resource IN 
                (SELECT ract.id_resource FROM resource_action ract 
                    JOIN group_resource_action as gr ON ract.id=gr.id_resource_action 
                    WHERE gr.id_group=? AND ract.id_resource=? AND ract.action='access'  
                UNION  
                SELECT ract.id_resource FROM resource_action ract 
                        JOIN user_resource_action as ur ON ract.id=ur.id_resource_action  
                        WHERE ur.id_user=? AND ract.id_resource=? AND ract.action='access')
INFO_AUTH_MODULO;
        $result=$this->_DB->fetchTable($sPeticionSQL,false,array($idGroup,$resource_name,$idGroup, $resource_name,$id_user, $resource_name));
        
        //comprobamos que los recursos obtenidos se encuentre tambien en la tabla organization_resource
        if(is_array($result) && count($result)>0){
            return true;
        }else
            return false;
    }

    function isUserAuthorized($username, $resource_name)
    {    
        if($id_user = $this->getIdUser($username)) {
            $resultado = $this->isUserAuthorizedById($id_user, $action_name, $resource_name);
        } else {
            $resultado = false;
        }
        return $resultado;
    }

    // Procedimiento para buscar la autenticación de un usuario en la tabla de ACLs.
    // Devuelve VERDADERO si el usuario existe y tiene el password MD5 indicado,
    // FALSE si no lo tiene, o en caso de error
    function authenticateUser($user, $pass)
    {
        $user = trim($user);
        $pass = trim($pass);
        //$pass = md5($pass);
        if ($this->_DB->connStatus) {
            return FALSE;
        } else {
           $this->errMsg = "";
            if($user == "" or $pass == "") {
                $this->errMsg = PALOACL_MSG_ERROR_1;
                return FALSE;
            }else{
				$idUser =$this->getIdUser($user);
				if($idUser===false){
					$this->errMsg = _tr("User doesn't exist");
					return FALSE;
				}

				if (!preg_match("/^[[:alnum:]]{32}$/", $pass)) {
					$this->errMsg = PALOACL_MSG_ERROR_3;
					return FALSE;
				//validamos el usuario
				} else if($this->userBellowMainOrganization($idUser)){
					if (!preg_match("/^[[:alnum:]\.\\-_]+$/", $user)) {
						$this->errMsg = PALOACL_MSG_ERROR_2;
						return FALSE;
					}
				}else {
					if(!preg_match("/^[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*@[a-z0-9]+([\._\-]?[a-z0-9]+)*(\.[a-z0-9]{2,4})+$/", $user)) {
						$this->errMsg = PALOACL_MSG_ERROR_2;
						return FALSE;
					}
				}
			}

			//comprobamos que el usuario exista, que la clave de login del usuario sea la correcta y que se encuentre relacionado
			//con una organizacion y que esta organizacion este activa en el sistema
            $sql = "SELECT id FROM acl_user WHERE username = ? AND md5_password = ?";
            $arr = $this->_DB->getFirstRowQuery($sql,false,array($user,$pass));
            if (is_array($arr)) {
                if(count($arr) > 0){
					$idOrganization = $this->getIdOrganizationUser($arr[0]);
					if($idOrganization==false)
						return false;
					else{
                        $query="Select 1 from organization where id=? and state=?";
                        $res = $this->_DB->getFirstRowQuery($query,false,array($idOrganization,"active"));
                        if($res==false){
                            $this->errMsg=_tr("User is part a no-active Organization in the System");
                            return FALSE;
                        }else
                            return true;
                    }
				 }else{
					return FALSE;
				 }
            } else {
                $this->errMsg = $this->_DB->errMsg;
                return FALSE;
            }
        }
    }

	//procedimiento para saber si el usuario pertenece al superentidad
	//esa es la entidad principal que es dueña del servidor y a la que pertence superadmin
	//se identifica porque el id de la entidad es 1
	function userBellowMainOrganization($idUser)
	{
		//avereriguamos a que grupo pertenece el usuario
		$id_Organization=$this->getIdOrganizationUser($idUser);
		//error
		if($id_Organization!==false){
			if($id_Organization == "1")
				return true;
		}
		return false;
	}

	function userBellowOrganization($idUser,$idOrganization)
	{
		//avereriguamos a que grupo pertenece el usuario
		$id_Organization=$this->getIdOrganizationUser($idUser);
		//error
		if($id_Organization!==false){
			if($id_Organization == $idOrganization)
				return true;
		}
		return false;
	}

	//funcion que devuelve el id de la organizacion a la que pertenece un usuario dado el id del usuario
	function getIdOrganizationUser($idUser)
	{
        $id_Organization = false;
        if (!preg_match('/^[[:digit:]]+$/', "$idUser")) {
            $this->errMsg = _tr("User ID is not valid");
            return false;
        }
        $sql="Select g.id_organization from acl_group as g join acl_user as u on u.id_group=g.id where u.id=?";
        $result = $this->_DB->getFirstRowQuery($sql,true,array($idUser));
        if (is_array($result)) {
            if(count($result)>0)
                $id_Organization = $result["id_organization"];
            else
                $this->errMsg = _tr("User doesn't exist");
        }else 
            $this->errMsg = _tr('DATABASE ERROR');
		return $id_Organization;
    }

    //funcion que devuelve el id de la organizacion a la que pertenece un usuario dado su username
    function getIdOrganizationUserByName($username)
    {
        $idUser=$this->getIdUser($username);
        $id_Organization=$this->getIdOrganizationUser($idUser);
        return $id_Organization;
    }

    /**
     * Procedimiento para saber si un usuario (login) pertenece al grupo administrador
     *
     * @param string   $username  Username del usuario
     *
     * @return boolean true or false
     */
    function isUserAdministratorGroup($username)
    {
        $is=false;
        $idUser = $this->getIdUser($username);
        if($idUser){
            $arrGroup = $this->getMembership($idUser);
            $is = array_key_exists('administrator',$arrGroup);
        }
        return $is;
    }

     /**
     * Procedimiento para saber si un usuario (login) es super administrador
     *
     * @param string   $username  Username del usuario
     *
     * @return boolean true or false
     */
    function isUserSuperAdmin($username)
    {
        $is=false;
        $idUser = $this->getIdUser($username);
        if($idUser){
            $arrGroup = $this->getMembership($idUser);
            $is = array_search('1', $arrGroup);
            if($username=="admin" && $is!==false){
                return true;
			}
        }
        return false;
    }


    /**
     * Procedimiento para crear un nuevo grupo
     *
     * @param string    $group       nombre del grupo a crear
     * @param string    $description    Descripción del grupo a crear
        * @param string    $id_organization    id de la organization a la que pertenece el grupo a crear
        *
        * @return bool     VERDADERO si el grupo se crea correctamente, FALSO en error
        */
    function createGroup($group, $description, $id_organization)
    {
        $bExito = FALSE;
        //validamos que el id de la organizacion sea numerico
        //no se le pueden crear nuevos grupos a la organizacion 1, ya que esta es solo de administracion
        if (!preg_match("/^[[:digit:]]+$/", "$id_organization") || $id_organization==1){
            $this->errMsg = _tr("Organization ID is not valid");
        }else if ($group == "") {
            $this->errMsg = _tr("Group can't be empty");
        } else {
            if ( !$description ) $description = $group;
            // Verificar que exista la organizacion
            $query="select id from organization where id=?";
            $result=$this->_DB->getFirstRowQuery($query,false,array($id_organization));
            if($result===false){
                $this->errMsg = $this->_DB->errMsg;
            }elseif(count($result)==0){
                $this->errMsg = _tr("Organization doesn't exist");
            }else{
                // Verificar que el nombre de Grupo no existe previamente
                $id_group = $this->getIdGroup($group, $id_organization);
                if ($id_group !== FALSE) {
                    $this->errMsg = _tr("Group already exists");
                } elseif ($this->errMsg == "") {
                    $sPeticionSQL = "INSERT INTO acl_group (description,name,id_organization) values(?,?,?);";
                    if ($this->_DB->genQuery($sPeticionSQL,array($description,$group, $id_organization))) {
                        $bExito = TRUE;
                    } else {
                        $this->errMsg = $this->_DB->errMsg;
                    }
                }
            }
        }

        return $bExito;
    }

    /**
     * Procedimiento para modificar al grupo con el ID de grupo especificado, para
     * darle un nuevo nombre y descripción.
     *
     * @param int       $id_group        Indica el ID del grupo a modificar
     * @param string    $group           Grupo a modificar
     * @param string    $description     Descripción del grupo a modificar
     *
     * @return bool VERDADERO si se ha modificado correctamente el grupo, FALSO si ocurre un error.
     */
    function updateGroup($id_group, $group, $description)
    {
        $bExito = FALSE;
        if ($group == "") {
            $this->errMsg = _tr("Group can't be empty");
        } else if (!preg_match("/^[[:digit:]]+$/", "$id_group")) {
            $this->errMsg = _tr("Group ID must be numeric");
        } else {
            if ( !$description ) $description = $group;

            // Verificar que el grupo indicado existe
            $tuplaGroup = $this->getGroups($id_group);
            if (!is_array($tuplaGroup)) {
                $this->errMsg = _tr("On having checked group's existence - ").$this->errMsg;
            } else if (count($tuplaGroup) == 0) {
                $this->errMsg = _tr("Group doesn't exist");
            } else {
                $bContinuar = TRUE;

                // Si el nuevo group es distinto al anterior, se verifica si el nuevo
                // group colisiona con uno ya existente
                if ($tuplaGroup[0][1] != $group) {
                    $id_group_conflicto = $this->getIdGroup($group);
                    if ($id_group_conflicto !== FALSE) {
                        $this->errMsg = _tr("Group already exists");
                        $bContinuar = FALSE;
                    } elseif ($this->errMsg != "") {
                        $bContinuar = FALSE;
                    }
                }

                if ($bContinuar) {
                    // Proseguir con la modificación del grupo
					// Proseguir con la modificación del grupo
                    $sPeticionSQL = "UPDATE acl_group set description=? where id=?";
                    if ($this->_DB->genQuery($sPeticionSQL,array($description,$id_group))) {
                        $bExito = TRUE;
                    } else {
                        $this->errMsg = $this->_DB->errMsg;
                    }
                }
            }
        }
        return $bExito;
    }

    /**
     * Procedimiento para borrar un grupo ACL, dado su ID numérico de grupo
     *
     * @param int   $id_group    ID del grupo que debe eliminarse
     *
     * @return bool VERDADERO si el grupo puede borrarse correctamente
     */
    function deleteGroup($id_group)
    {
        if (!preg_match('/^[[:digit:]]+$/', "$id_group") ) {
            $this->errMsg = _tr("Group ID must be numeric");
            return false;
        } else {
            //no se pueden borrar los grupos por default de elasstix
            $arrGroup=$this->getGroups($id_group);
            if(is_array($arrGroup) && count($arrGroup)>0){
                if($arrGroup[0][3]=="1"){
                    $this->errMsg = _tr("Invalid Group");
                    return FALSE;
                }
            }else{
                $this->errMsg = _tr("Group doesn't exist").$this->errMsg;
                return FALSE;
            }

            $this->errMsg = "";
            $query = "DELETE FROM acl_group WHERE id = ?";
            //no deben haber usuarios ertenecientes al grupo para que este puede ser borrado
            if(!($this->HaveUsersTheGroup($id_group))){
                $bExito = $this->_DB->genQuery($query, array($id_group));
                if (!$bExito) {
                    $this->errMsg = $this->_DB->errMsg;
                }
            }else{
                $this->errMsg = _tr("You can not delete this group. You must delete all users belong this group before to delete the group");
                return FALSE;
            }
        }
        return $bExito;
    }

    function HaveUsersTheGroup($id_group)
    {
        $Haveusers = TRUE;
        $sPeticionSQL = "SELECT count(id) FROM acl_user WHERE id_group = ?";
        $result = $this->_DB->getFirstRowQuery($sPeticionSQL, FALSE,array($id_group));
        if(is_array($result)) {
            $numUsers = $result[0];
            if($numUsers==0)
                $Haveusers = FALSE;
        }else{
            $this->errMsg = $this->_DB->errMsg;
        }
        return $Haveusers;
    }
    
    /**
     * Procedimiento para obtener el nombre del grupo dado un id. 
     *
     * @param integer   $idGroup  id del grupo
     *
     * @return string    nombre del grupo 
     */
    function getGroupNameByid($idGroup)
    {
        $groupName = null;
        $this->errMsg = "";
        $data = array($idGroup);
        $sPeticionSQL = "SELECT name FROM acl_group WHERE id = ?";
        $result = $this->_DB->getFirstRowQuery($sPeticionSQL, FALSE, $data);
        if ($result && is_array($result) && count($result)>0) {
            $groupName = $result[0];
        }else $this->errMsg = $this->_DB->errMsg;
        return $groupName;
    }

    function updateUserName($idUser, $name){
        if(!preg_match("/[[:digit:]]+/",$idUser)){
            $this->errMsg=_tr("User ID is not valid");
            return false;
        }
        $query="Update acl_user set name=? where id=?";
        $result = $this->_DB->genQuery($query,array($name,$idUser));
        if($result==false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else
            return true;
    }
    
    /**
     * Funcion que devuelve una lista 
     */
    function getUsersAccountsInfoByDomain($idOrganization, $name=null)
    {
        $param = array($idOrganization);
        $search_condition = '';
        if (!empty($name)) {
        	$search_condition = ' AND u.name LIKE ?';
            $param[] = "%$name%";
        }
        $query = <<<SQL_ACCOUNTS_INFO
SELECT u.id, u.name, u.username, u.extension, u.fax_extension, e.elxweb_device,
    e.alias, e.organization_domain
FROM acl_user u, acl_group g, extension e, organization
WHERE u.id_group = g.id
    AND u.extension = e.exten
    AND e.organization_domain = organization.domain
    AND g.id_organization = organization.id
    AND g.id_organization = ? $search_condition
ORDER BY name ASC;
SQL_ACCOUNTS_INFO;
        $result = $this->_DB->fetchTable($query,true,$param);
        if($result === false){
            $this->errMsg = $this->_DB->errMsg;
        }
        
        /* El siguiente código es un arreglo temporal hasta corregir el lugar
         * donde se escribe la columna extension.elxweb_device. Esta columna
         * debe contener el valor sin la cadena adjuntada '_dominio.com' para
         * poder funcionar con Kamailio. */
        for ($i = 0; $i < count($result); $i++) {
        	if (substr($result[$i]['elxweb_device'], -1 * (strlen($result[$i]['organization_domain']) + 1)) 
                == '_'.$result[$i]['organization_domain']) {
                $result[$i]['elxweb_device'] = substr($result[$i]['elxweb_device'], 0,
                    strlen($result[$i]['elxweb_device']) - strlen($result[$i]['organization_domain']) - 1);
            }
        }
        
        return $result;
    }
    
    function getUserAccountInfo($idUser,$idOrganization){
       $query="SELECT u.id, u.name, u.username, u.extension, u.fax_extension, e.elxweb_device, e.alias
                FROM acl_user u JOIN acl_group g ON u.id_group=g.id JOIN extension e ON u.extension=e.exten WHERE u.id=? and g.id_organization=? and e.organization_domain=(SELECT domain from organization where id=?)";
        $result=$this->_DB->getFirstRowQuery($query,true,array($idUser,$idOrganization,$idOrganization));
        if($result===false){
            $this->errMsg=_tr("DATABASE ERROR");
        }
        return $result;
    }
    
    /**
     * Esta funcion devuelve un arreglo que contine las acciones que un usuario puede realizar
     * dentro de un modulo dado su id
     * @param string $moduleId -> id del modulo
     * @param integer $idUser -> id del usuario que realiza las acciones
     * @return array array(action1,
     *                     action2,
     *                     action3)
     */
    function getResourceActionsByUser($idUser,$moduleId){
        $sPeticionSQL = <<<INFO_AUTH_MODULO
                SELECT ract.action FROM resource_action ract 
                    JOIN group_resource_action as gr ON ract.id=gr.id_resource_action 
                    JOIN acl_user u ON u.id_group=gr.id_group
                    WHERE u.id=? AND ract.id_resource=?  
                UNION  
                SELECT ract.action FROM resource_action ract 
                        JOIN user_resource_action as ur ON ract.id=ur.id_resource_action  
                        WHERE ur.id_user=? AND ract.id_resource=?
INFO_AUTH_MODULO;
        $result=$this->_DB->fetchTable($sPeticionSQL,true,array($idUser,$moduleId,$idUser,$moduleId));
        if(is_array($result)){
            $resourcePermission=array();
            foreach($result as $value){
                $resourcePermission[]=$value['action'];
            }
            return $resourcePermission;
        }else{
            $this->errMsg=_tr("DATABASE ERROR");
            return false;
        }
    }
    
    /**
     * Esta funcion retorna verdadero en caso de que un usuario identificado por su id 
     * pueda realizar una determinada accion dentro de un modulo
     * @param string $moduleId -> id del modulo
     * @param integer $idUser -> id del usuario que realiza las acciones
     * @param string $action -> accion que se queire consultar
     */
     function userCanPerformAction($idUser,$moduleId,$action){
        $sPeticionSQL = <<<INFO_AUTH_MODULO
                SELECT ract.id_resource FROM resource_action ract 
                    JOIN group_resource_action as gr ON ract.id=gr.id_resource_action 
                    JOIN acl_user u ON u.id_group=gr.id_group
                    WHERE u.id=? AND ract.id_resource=? AND ract.action=?
                UNION  
                SELECT ract.id_resource FROM resource_action ract 
                        JOIN user_resource_action as ur ON ract.id=ur.id_resource_action  
                        WHERE ur.id_user=? AND ract.id_resource=? AND ract.action=?
INFO_AUTH_MODULO;
        $result=$this->_DB->fetchTable($sPeticionSQL,false,array($idUser,$moduleId,$action,$idUser,$moduleId,$action));
        if(is_array($result) && count($result)>0){
            return true;
        }else{
            $this->errMsg=($result===FALSE)?_tr("DATABASE ERROR"):_tr("You are not authorized to Perform this action");
            return false;
        }
    }

     /**
     * Procedimiento para obtener el listado de los recursos existentes en los ACL. Si
     * se especifica un ID del recurso, el listado contendrá únicamente al recurso
     * indicado. De otro modo, se listarán todos los recursos.
     *
     * @param int   $id_rsrc    Si != NULL, indica el ID del recurso a recoger
     *
     * @return array    Listado de recursos en el siguiente formato, o FALSE en caso de error:
     *  array(
     *      array(id, name, description),
     *      ...
     *  )
     */
    function getResources($id_rsrc = NULL,$orgAccess=null,$administrative=null)
    {
        $arr_result = FALSE;
        $arrParams = null;
        $this->errMsg = "";
        
        $sPeticionSQL = "SELECT id, description FROM acl_resource WHERE Type!=''";
        if(!is_null($id_rsrc)){
            $sPeticionSQL .= " and id = ?";
            $arrParams[] = $id_rsrc;
        }
        if(!is_null($orgAccess)){
            $sPeticionSQL .= " and organization_access = ?";
            $arrParams[] = $orgAccess;
        }
        if(!is_null($administrative)){
            $sPeticionSQL .= " and administrative = ?";
            $arrParams[] = $administrative;
        }
        $arr_result = $this->_DB->fetchTable($sPeticionSQL, false,$arrParams);
        if (!is_array($arr_result)) {
            $arr_result = FALSE;
            $this->errMsg = $this->_DB->errMsg;
        }
        return $arr_result;
    }

	/**
     * Procedimiento para obtener el listado de los recursos existentes en la tabla acl_resource
     * a la que tiene acceso la organizacion. Si se especifica un el nombre del recurso, el listado contendrá 
     * únicamente al recurso indicado. De otro modo, se listarán todos los recursos a los que tenga acceso dicha organizacion.
     * @param array $filter_resource => arreglo que contine los nombres de los recuros
                                        estos nombre son comparados con el campo description
     * @param string $administrative => este campo sirve para distinguir entre modulos 
                                        de tipo administrativo y modulos de tipo usuario final
     */
	function getResourcesByOrg($id_Organization, $filter_resource = NULL,$administrative=null)
    {
        $arr_result = FALSE;
        $where = "";
        if (!preg_match('/^[[:digit:]]+$/', "$id_Organization")) {
            $this->errMsg = _tr("Organization ID must be numeric");
        } else {
            $arrParam = array($id_Organization);
            if(isset($administrative)){
                $where .=" AND administrative=? ";
                $arrParam[] = $administrative;
            }    
            if(isset($filter_resource)){
                if(is_array($filter_resource) && count($filter_resource)>0){
                    $where .=" AND (";
                    for($i=0;$i<count($filter_resource)-1;$i++){
                        $where .=" LOWER(description) LIKE ? OR ";
                        $arrParam[] = "%".strtoupper($filter_resource[$i])."%";
                    }
                    $where .=" LOWER(description) LIKE ? )";
                    $arrParam[] = "%".strtoupper($filter_resource[count($filter_resource)-1])."%";
                    
                }else{
                    $where .=" AND LOWER(description) LIKE ? ";
                    $arrParam = "%".strtoupper($filter_resource)."%";
                }
            }
            $this->errMsg = "";
            $sPeticionSQL = "SELECT ar.id, ar.description FROM acl_resource ar JOIN organization_resource ogr on ar.id=ogr.id_resource WHERE Type!='' and id_organization=? AND organization_access='yes' $where order by ar.id Asc";
            $arr_result = $this->_DB->fetchTable($sPeticionSQL, true,$arrParam);
            if (!is_array($arr_result)) {
                $arr_result = FALSE;
                $this->errMsg = $this->_DB->errMsg;
            }
        }
        return $arr_result;
    }
    
    /**
     * Esta funcion retirna el numero de resursos disponibles en el sistema
     * @param array $filter_resource => arreglo que contine los nombres de los recuros
                                        estos nombre son comparados con el campo description
     * @param strting $orgAccess => este parametro filtra por el campo 'organization_access'
                                    de la tabla acl_resource e indica si el recurso es cuestion deberia
                                    ser poder accedido por una organizacion
     * @param string $administrative => este campo sirve para distinguir entre modulos 
                                        de tipo administrativo y modulos de tipo usuario final
     */
    function getNumResources($filter_resource = NULL,$orgAccess=null,$administrative=null)
    {
        $where = "";
        $arrParam=array();
        if(isset($orgAccess)){
            $where .=" AND organization_access=? ";
            $arrParam[] = $orgAccess;
        }
        if(isset($administrative)){
            $where .=" AND administrative=? ";
            $arrParam[] = $administrative;
        }    
                
        if(isset($filter_resource)){
            if(is_array($filter_resource) && count($filter_resource)>0){
                $where .=" AND (";
                for($i=0;$i<count($filter_resource)-1;$i++){
                    $where .=" LOWER(description) LIKE ? OR ";
                    $arrParam[] = "%".strtoupper($filter_resource[$i])."%";
                }
                $where .=" LOWER(description) LIKE ? )";
                $arrParam[] = "%".strtoupper($filter_resource[count($filter_resource)-1])."%";
                
            }else{
                $where .=" AND LOWER(description) LIKE ? ";
                $arrParam = "%".strtoupper($filter_resource)."%";
            }
        }
        $query = "SELECT count(id) FROM acl_resource WHERE Type!='' $where";
        $result = $this->_DB->getFirstRowQuery($query, FALSE, $arrParam);

        if( $result == false )
        {
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    /**
     * Esta funcion function la lista de resursos disponibles en el sistema
     * Estos recuroso se pueden filtrar por tres parametros
     * @param array $filter_resource => arreglo que contine los nombres de los recuros
                                        estos nombre son comparados con el campo description
     * @param strting $orgAccess => este parametro filtra por el campo 'organization_access'
                                    de la tabla acl_resource e indica si el recurso es cuestion deberia
                                    ser poder accedido por una organizacion
     * @param string $administrative => este campo sirve para distinguir entre modulos 
                                        de tipo administrativo y modulos de tipo usuario final
     */
    function getListResources($limit,$offset,$filter_resource=null,$orgAccess=null,$administrative=null)
    {
        $where = "";
        $arrParam=array();
        if(isset($orgAccess)){
            $where .=" AND organization_access=? ";
            $arrParam[] = $orgAccess;
        }
        if(isset($administrative)){
            $where .=" AND administrative=? ";
            $arrParam[] = $administrative;
        }  
        if(isset($filter_resource)){
            if(is_array($filter_resource) && count($filter_resource)>0){
                $where .=" AND (";
                for($i=0;$i<count($filter_resource)-1;$i++){
                    $where .=" LOWER(description) LIKE ? OR ";
                    $arrParam[] = "%".strtoupper($filter_resource[$i])."%";
                }
                $where .=" LOWER(description) LIKE ? )";
                $arrParam[] = "%".strtoupper($filter_resource[count($filter_resource)-1])."%";
                
            }else{
                $where = " AND LOWER(description) LIKE ? ";
                $arrParam = "%".strtoupper($filter_resource)."%";
            }
        }

        $query = "SELECT id, description FROM acl_resource WHERE Type!='' $where ";
        $query .= "order by id Asc LIMIT ? OFFSET ?";
        $arrParam[] = $limit;
        $arrParam[] = $offset;
        $result = $this->_DB->fetchTable($query, true, $arrParam);

        if( $result == false )
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }
    
    /**
     * Procedimiento para eliminar el recurso dado su id. 
     * Se elimina el recurso de la tabla acl_resource asi como cualquier otra
     * referencia al mismo en la tablas de permisos hacia este recurso
     * Como la tablas estan indexadas y con constraint al elminar el recurso de acl_resource
     * se hace tambien estas acciones
     * @param integer   $idresource
     *
     * @return bool     si es verdadero entonces se elimino bien
     ******************************************************************/
    function deleteResource($idresource)
    {
        //validamos el id del recurso
        if(!preg_match("/^[[:word:]]+$/",$idresource)){
            $this->errMsg=_tr("Invalid Resource");
            return false;
        }
        
        $this->errMsg = "";
        $query = "DELETE FROM acl_resource WHERE id = ?";
        $result = $this->_DB->genQuery($query,array($idresource));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else
            return true;
    }

    /**
     * Procedimiento que devuelve un arreglo de dos dimensiones con las accciones por recurso.
     * Se realiza un filtrado en la busqueda por los nombres de los recursos pasados en el parametro
     * $listResource
     * @param array $listResource => array(resource1,resource2,resource3)
     * @param string $organization_access => ('yes','no')
     * @return mixed false en caso de error
                     array = ( id_resource1 => array(action1,action2,..),
                               id_resource2 => array(action1,action2,..),
                               id_resource3 => array(action1,action2,..),
                         )
     */
    function getResourcesActions($listResource=null,$organization_access=null){
        $where=array();
        $arrParam=null;
        $query="SELECT ra.id_resource,ra.action FROM resource_action ra";
        if(is_array($listResource) && count($listResource)>0){
            $q="";
            foreach($listResource as $resource){
                $arrParam[]=$resource;
                $q .="?,"; 
            }
            $q=substr($q,0,-1);
            $where[]= " ra.id_resource IN ($q) ";
        }
        if(isset($organization_access)){
            $query .=" JOIN acl_resource r ON r.id=ra.id_resource ";
            $where[]=" r.organization_access=? ";
            $arrParam[]=$organization_access;
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
        
        $result = $this->_DB->fetchTable($query,true,$arrParam);
        if( $result === false ) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
            $resourceActions=array();
            foreach($result as $value){
                $resourceActions[$value['id_resource']][]=$value['action'];
            }
            return $resourceActions;
        }
    }
    
    /**
    * Procedimiento que devuelve un arreglo de dos dimensiones con las accciones por recursos
    * que un grupo, identificado por du id, puede realizar
    * Se realiza un filtrado en la busqueda por los nombres de los recursos pasados en el parametro
    * $listResource
    * @param int   $id_group    ID del grupo del que se desea saber sus permisos
    * @param array $listResource => array(resource1,resource2,resource3)
    * @return array Un arreglo con todos los recursos a los que los los miembros del grupo dado tienen
                    acceso
            false en caso de error
            array = ( id_resource1 => array(action1,action2,..),
                      id_resource2 => array(action1,action2,..),
                      id_resource3 => array(action1,action2,..),
                )
    */
    function loadGroupPermissions($id_group,$listResource=null)
    {
        if (!preg_match('/^[[:digit:]]+$/', "$id_group")) {
            $this->errMsg = _tr("Group ID must be numeric");
            return false;
        }else{
            $arrParam=array($id_group);
            $query = "SELECT ract.id_resource,ract.action FROM resource_action ract 
                        JOIN group_resource_action gract ON ract.id=gract.id_resource_action
                        WHERE gract.id_group=?";        
            if(is_array($listResource) && count($listResource)>0){
                $q="";
                foreach($listResource as $resource){
                    $arrParam[]=$resource;
                    $q .="?,"; 
                }
                $q=substr($q,0,-1);
                $query .=" AND ract.id_resource IN ($q) ";
            }
            $result = $this->_DB->fetchTable($query,true,$arrParam);
            if( $result === false ) {
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }else{
                $arrResourcePermission=array();
                foreach($result as $value){
                    $arrResourcePermission[$value['id_resource']][]=$value['action'];
                }
                return $arrResourcePermission;
            }
        }
    }

    /**
     * Funcion que almacena en la tabla organization_resource los modulos 
     * o recursos a  los que una organizacion tiene accesso
     */
    function saveOrgPermission($idOrganization, $resources){
        if (!preg_match('/^[[:digit:]]+$/', "$idOrganization")){
            $this->errMsg = _tr("Organization ID is not valid");
            return false;
        }else{
            //validamos que exista la organizacion
            $query="SELECT 1 from organization where id=?";
            $result=$this->_DB->getFirstRowQuery($query,false, array($idOrganization));
            if($result==false){
                $this->errMsg = _tr("Doesn't exist organization with id=").$idOrganization." ".$this->_DB->errMsg;
                return false;
            }
            
            if(is_array($resources) && count($resources)>0){
                $arrParam[]=$idOrganization;
                $q='';
                foreach ($resources as $resource){
                    $arrParam[]=$resource;
                    $q .="?,";
                }
                $q=substr($q,0,-1);
                $sPeticionSQL = "INSERT INTO organization_resource (id_organization, id_resource) ".
                                " SELECT ?,ar.id FROM acl_resource ar WHERE ar.organization_access='yes'
                                    AND ar.id IN ($q)";
                if (!$this->_DB->genQuery($sPeticionSQL, $arrParam)){
                    $this->errMsg = _tr("DATABASE ERROR").$this->_DB->errMsg;
                    return false;
                }
                
                //una vez el recurso a sido asignado a la organizacion asignar los permisos por default
                //para ese recursos a los grupos a los que se asigno el recurso
                //los grupos a los que se le asignara seran los que tengan de nobre administrator,supervisor 
                //y end_user
                
                //obtenemos los grupos recien insertados a la organizacion
                $grpOrga=$this->getGroups(null,$idOrganization);
                if($grpOrga==false){
                    $this->errMsg=_tr("An error has ocurred trying to set organizaion's group permissions.");
                    return false;
                }
                $query="INSERT INTO group_resource_action (id_group,id_resource_action) ".
                                "SELECT ?,gract.id_resource_action FROM ".
                                "(SELECT or1.id_resource FROM organization_resource or1 
                                    WHERE or1.id_organization=? AND or1.id_resource IN ($q)) as or_re ".
                            "JOIN ".
                                "(SELECT gr.id_resource_action,ract.id_resource FROM resource_action ract 
                                    JOIN group_resource_action gr ON ract.id=gr.id_resource_action 
                                    JOIN acl_group g ON g.id=gr.id_group 
                                        WHERE g.name=? AND g.id_organization=1) as gract ".
                            "ON or_re.id_resource=gract.id_resource";
                
                foreach($grpOrga as $value){
                    $param=array();
                    if($value[1]=='administrator' || $value[1]=='supervisor' || $value[1]=='end_user'){
                        $param[]=$value[0];
                        $param[]=$idOrganization;
                        for($i=0;$i<count($resources);$i++){
                            $param[]=$resources[$i];
                        }
                        $param[]=$value[1];
                        $result=$this->_DB->genQuery($query,$param);
                        if($result==false){
                            $this->errMsg = _tr("An error has ocurred trying to set organizaion's group permissions.");
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
    
    function deleteOrgPermissions($idOrganization, $resources)
    {
        if (!preg_match('/^[[:digit:]]+$/', "$idOrganization")){
            $this->errMsg = _tr("Organization ID is not valid");
            return false;
        }else {
            //debemos borrar los permisos de la tabla group_resource_action, user_resource_action y organization_resource
            if(is_array($resources) && count($resources)>0){
                $q=implode(',',array_fill(0,count($resources),'?'));
                $query1="DELETE gr FROM group_resource_action gr JOIN resource_action ra 
                            ON gr.id_resource_action=ra.id WHERE ra.id_resource IN ($q) AND gr.id_group IN (
                                SELECT g.id FROM acl_group g WHERE g.id_organization=?)";
                $query2="DELETE ur FROM user_resource_action ur JOIN resource_action ra 
                            ON ur.id_resource_action=ra.id WHERE ra.id_resource IN ($q) AND ur.id_user IN (
                                SELECT u.id FROM acl_group g JOIN acl_user u ON u.id_group=g.id 
                                    WHERE g.id_organization=?)";
                $query3="DELETE FROM organization_resource WHERE id_resource IN ($q) AND id_organization = ?";
            
                $resources[]=$idOrganization;
                if (!$this->_DB->genQuery($query1,$resources)){
                    $this->errMsg = _tr("DATABASE ERROR");
                    return false;
                }
                if (!$this->_DB->genQuery($query2,$resources)){
                    $this->errMsg = _tr("DATABASE ERROR");
                    return false;
                }
                if (!$this->_DB->genQuery($query3,$resources)){
                    $this->errMsg = _tr("DATABASE ERROR");
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Procedimiento que almacena acciones que un grupo puede realizar dentro de un recurso
     * @param integer $idGroup -> Id del grupo al que se le van asignar las acciones
     * @param array array(resource1=>array(action1,action2,...,actionN),
                          resource2=>array(action1,action2,...,actionN))
     * @return boolean true en caso de existo
     *                 false en caso de error 
     */
    function saveGroupPermission($idGroup,$arrResourceAction){
        //verificamos que le recurso exista, que este pueda ser accedido por una organizacion
        //y que se encuentre en la tabla organization_resource de la organizacion a la que pertenece el grupo
        if(is_array($arrResourceAction) && count($arrResourceAction)>0){
            $arrParam[]=$idGroup;
            $q='';
            foreach($arrResourceAction as $resource => $value){
                $arrParam[]=$resource;
                $q .='?,';
            }
            $q=substr($q,0,-1);
            $query="SELECT r.id FROM acl_resource r JOIN organization_resource org ON r.id=org.id_resource
                    JOIN acl_group g ON g.id_organization=org.id_organization 
                        WHERE g.id=? AND r.organization_access='yes' AND r.id IN ($q)";
            $resources=$this->_DB->fetchTable($query,true,$arrParam);
            if($resources===false){
                $this->errMsg=_tr('An error has ocurred to retrieve Resources data');
                return false;
            }else{
                foreach($resources as $value){
                    $arrParam=array();
                    if(is_array($arrResourceAction[$value['id']]) && count($arrResourceAction[$value['id']])>0){
                        $arrParam[]=$idGroup;
                        $arrParam[]=$value['id'];
                        $q='';
                        foreach($arrResourceAction[$value['id']] as $action){
                            $arrParam[]=$action;
                            $q .='?,';
                        }
                        $q=substr($q,0,-1);
                        $arrParam[]=$value['id'];
                        $arrParam[]=$idGroup;
                        $query="INSERT INTO group_resource_action (id_group,id_resource_action) 
                                    SELECT ?,ra.id FROM resource_action ra 
                                        WHERE ra.id_resource=? AND ra.action IN ($q) AND ra.id NOT IN 
                                            (SELECT gra.id_resource_action FROM group_resource_action gra JOIN resource_action ra ON gra.id_resource_action=ra.id WHERE ra.id_resource=? AND gra.id_group=?)";
                        $result=$this->_DB->genQuery($query,$arrParam);
                        if($result==false){
                            $this->errMsg=("DATABASE ERROR");
                            return false;
                        }
                    }
                }
                return true;
            }
        }else{
            $this->errMsg=_tr('Invalid Resources');
            return false;
        }
    }
    
    /**
     * Procedimiento que elimina acciones que un grupo puede realizar dentro de un recurso
     * @param integer $idGroup -> Id del grupo al que se le van a eliminar las acciones
     * @param array array(resource1=>array(action1,action2,...,actionN),
                          resource2=>array(action1,action2,...,actionN))
     * @return boolean true en caso de existo
     *                 false en caso de error 
     */
    function deleteGroupPermission($idGroup,$arrResourceAction){
        if(is_array($arrResourceAction) && count($arrResourceAction)>0){
            foreach($arrResourceAction as $resource => $actions){
                $arrParam=array();
                if(is_array($actions) && count($actions)>0){
                    $arrParam[]=$idGroup;
                    $arrParam[]=$resource;
                    $q='';
                    foreach($actions as $action){
                        $arrParam[]=$action;
                        $q .='?,';
                    }
                    $q=substr($q,0,-1);
                    $query="DELETE gra FROM group_resource_action gra 
                            WHERE gra.id_group=? AND gra.id_resource_action IN 
                                (SELECT ra.id FROM resource_action ra 
                                    WHERE ra.id_resource=? AND ra.action IN ($q))";
                    $result=$this->_DB->genQuery($query,$arrParam);
                    if($result==false){
                        $this->errMsg=_tr("DATABASE ERROR");
                        return false;
                    }
                }
            }
            return true;
        }else{
            $this->errMsg=_tr('Invalid Resources');
            return false;
        }
    }
    
    //para la creacion de recursos al momento de instalar o actualizar un paquete
    /**
     * Procedimiento que crea un nuevo recurso o actualiza uno existen dentro del Sistema
     * El recurso en creado o actualizado dentro de la tabla acl_resource
     * A continuación revisa el conjunto de acciones que el recurso posee
     * En caso de un recuros nuevo estas acciones son añadidas a la tabla resource_action
     * Si el recurso existiece se revisan el conjunto de acciones. Si existe alguna accion nueva
     * esta es agregada, Si una accion actual no existe entra la pasadas como parametro esta 
     * es eliminada
     * En caso de agragar nuevas acciones se le da los permisos por default indicados en el parametro
     * estos permisos se setean para las grupos que pertenecen a la organizacion 1  para nadie mas
     *
     * @param array $resource arreglo multidimension que tiene la siguiente estructura
     *              $resurce[id] -> id del recurso es unico en el sistema
     *              $resurce[description] -> nombre del recurso que aparece en los menus
     *              $resurce[idParent] -> nombre del modulo padre
     *              $resurce[link] -> este capo indica si el modulo se trata de un recurso externo
     *              $resurce[type] -> 'module', 'link', ''
     *              $resurce[org_access] -> 'yes' or 'no' 
     *              $resurce[administrative] -> 'yes' or 'no' 
     *              $resurce[actions] => array(action1 =>  array(group1=>description, group2=>description,                  group3=>description),
                                               action2 => array(group1=>description,group2=>description)),
     */
    function createResource($resource)
    {
        //validamos el id del recurso
        if(!preg_match("/^[[:word:]]+$/",$resource['id'])){
            $this->errMsg=_tr("Invalid Resource");
            return false;
        }
        
        //comprobamos si el recurso dado existe
        $db_resource=$this->getResourceById($resource['id']);
        if($db_resource===false){
            //problemas con la base de datos no podemos continuar
            return false;
        }
        
        $this->_DB->beginTransaction();
        if(count($db_resource)>0){//recurso existe
            //actualizamos el recurso existen en la tabla acl_resource
            if(empty($resource['description']))
                $resource['description']=$db_resource['description'];
            $resource['org_access']=(isset($resource['org_access']))?$resource['org_access']:$db_resource['org_access'];
            $resource['administrative']=(isset($resource['administrative']))?$resource['administrative']:$db_resource['administrative'];
            
            if(!$this->updateResource($resource)){
                $this->errMsg=_tr("Resource could not be updated in table acl_resource")." ".$this->errMsg;
                $this->_DB->rollBack();
                return false;
            }
            
            //si el recurso no llegase a exixtir en la tabla organization_resource resource se ingresa
            //este se hace solo para la organizacion 1
            if(!$this->saveOrgAccessDefault($resource)){
                $this->errMsg=_tr("Resource could not be updated in table organization_resource")." ".$this->errMsg;
                $this->_DB->rollBack();
                return false;
            }
            
            //comparamos las acciones existen con las anteriores y sacamos una lista de las acciones nuevas
            //y las acciones que ya no existen
            if(!isset($resource['actions']))
                $resource['actions']=array();
                          
            $actions=array_keys($resource['actions']);
            
            $cu_actions=$this->getResourcesActions(array($resource['id']));
            if($cu_actions===false){
                $this->errMsg=_tr("An error has ocurred to retrieved current resource actions");
                return false;
            }if(count($cu_actions)==0){
                $cuActions=array();
            }else{
                $cuActions=$cu_actions[$resource['id']];
            }
                        
            $new_actions=array_diff($actions,$cuActions);
            $del_actions=array_diff($cuActions,$actions);
            if(!$this->delActions($resource['id'],$del_actions)){
                $this->errMsg=_tr("Actions could not be deleted")." ".$this->errMsg;
                $this->_DB->rollBack();
                return false;
            }
            
            //nuevas acciones
            //creamos las acciones
            if(!$this->insertActions($resource['id'],$new_actions)){
                $this->errMsg=_tr("Actions could not be created")." ".$this->errMsg;
                $this->_DB->rollBack();
                return false;
            }
            
            //ordenamos los permisos por default del recuros en funcion de los grupos
            //en esta funcion se comprueba si los grupos que intervienen en los permisos
            //para las acciones existan y se obtiene el id de los mismos
            //si un grupo listado no existe entonces se lo intenta crear
            $groupActions=$this->orderActionsByGroup($resource['actions']);
            if($groupActions===false){
                return false;
            }
                        
            //primero procedemos a eliminar los permisos actuales de la table group_resource_action 
            //insertar los permisos usando el esquema dado en los xmls
            if(!$this->deleteGroupPermissionDefault($resource['id'],$actions)){
                $this->errMsg=_tr("Default permissions could not be created");
                $this->_DB->rollBack();
                return false;
            }
            
            //insertamos los permisos por default de las nuevas acciones
            if(!$this->insertGroupPermissionDefault($resource['id'],$groupActions)){
                $this->errMsg=_tr("Default permissions could not be created");
                $this->_DB->rollBack();
                return false;
            }
            $this->_DB->commit();
            return true;
        }else{//recurso no existe
            if(empty($resource['description']))
                $resource['description']=$resource['id'];
                
            $resource['org_access']=(isset($resource['org_access']))?$resource['org_access']:'yes';
            $resource['administrative']=(isset($resource['administrative']))?$resource['administrative']:'yes';
            
            //insertamos el recurso en la base
            if(!$this->insertResource($resource)){
                $this->errMsg=_tr("Resource could not be inserted in database acl_resource")." ".$this->errMsg;
                $this->_DB->rollBack();
                return false;
            }
            
            //si el recurso no llegase a exixtir en la tabla organization_resource resource se ingresa
            //este se hace solo para la organizacion 1
            if(!$this->saveOrgAccessDefault($resource,true)){
                $this->errMsg=_tr("Resource could not be updated in table organization_resource")." ".$this->errMsg;
                $this->_DB->rollBack();
                return false;
            }
            
            if(isset($resource['actions']) && is_array($resource['actions'])){
                $actions=array_keys($resource['actions']);
                
                //creamos las acciones
                if(!$this->insertActions($resource['id'],$actions)){
                    $this->errMsg=_tr("Actions could not be created");
                    $this->_DB->rollBack();
                    return false;
                }
                
                //ordenamos los permisos por default del recuros en funcion de los grupos
                //en esta funcion se comprueba si los grupos que intervienen en los permisos
                //para las acciones existan y se obtiene el id de los mismos
                //si un grupo listado no existe entonces se lo intenta crear
                $groupActions=$this->orderActionsByGroup($resource['actions']);
                if($groupActions===false){
                    return false;
                }
                
                //insertamos los permisos por default
                if(!$this->insertGroupPermissionDefault($resource['id'],$groupActions)){
                    $this->errMsg=_tr("Default permissions could not be created");
                    $this->_DB->rollBack();
                    return false;
                }
            }
            $this->_DB->commit();
            return true;
        }
    }
    
    /**
     * Procedimiento que retorna un recurso dado su id
     */
    function getResourceById($resource){
        $query="SELECT * FROM acl_resource WHERE id=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($resource));
        if($result===false){
            $this->errMsg=_tr("DATABASE ERROR");
        }
        return $result;
    }
    
    private function insertResource($resource){
        $query="INSERT INTO acl_resource (id, description, IdParent, Link, Type, order_no, organization_access, administrative) VALUES (?,?,?,?,?,?,?,?)";
        if($this->_DB->genQuery($query,array($resource['id'], $resource['description'], $resource['idParent'], $resource['link'], $resource['type'], $resource['order_no'], $resource['org_access'], $resource['administrative']))){
            return true;
        }else{
            $this->errMsg=("DATABASE ERROR");
            return false;
        }
    }
    
    private function updateResource($resource){
        $query="UPDATE acl_resource SET description=?, IdParent=?, Link=?, Type=?, order_no=?, organization_access=?, administrative=? WHERE id=?";
        if($this->_DB->genQuery($query,array($resource['description'], $resource['idParent'], $resource['link'], $resource['type'], $resource['order_no'], $resource['org_access'], $resource['administrative'],$resource['id']))){
            return true;
        }else{
            $this->errMsg=("DATABASE ERROR");
            return false;
        }
    }
    
    private function saveOrgAccessDefault($resource,$insert=false){
        //solo se ingresa en caso que el recurso sea de tipo module
        if($resource['type']!="module")
            return true;
            
        if(!$insert){
            //comprobamos que el recurso no exista actualemente
            $query="SELECT 1 from organization_resource WHERE id_organization=1 and id_resource=?";
            $result=$this->_DB->getFirstRowQuery($query,false,array($resource['id']));
            if(is_array($result) && count($result)==1){
                //recurso ya existe, regresamos a la funcion principal
                return true;
            }
        }
        //insertamos el recurso en la table organization_resource
        $sPeticionSQL = "INSERT INTO organization_resource (id_organization, id_resource) VALUES (?,?)";
        if (!$this->_DB->genQuery($sPeticionSQL, array(1,$resource['id']))){
            $this->errMsg = _tr("DATABASE ERROR").". ".$this->_DB->errMsg;
            return false;
        }
        return true;
    }
    
    private function insertActions($id_resource,$actions){
        if(!is_array($actions)){
            $this->errMsg=_tr("Invalid parameter actions");
            return false;
        }
        if(count($actions)==0)
            return true; //no ahi nada que hacer
            
        $query="INSERT INTO resource_action (id_resource,action) VALUES (?,?)";
        foreach($actions as $action){
            if(!$this->_DB->genQuery($query,array($id_resource,$action))){
                return false;
            }
        }
        return true;
    }
    
    private function delActions($id_resource,$actions){
        if(!is_array($actions)){
            $this->errMsg=_tr("Invalid parameter actions");
            return false;
        }
        if(count($actions)==0)
            return true; //no ahi nada que hacer
        
        $param[]=$id_resource;
        $q='';
        foreach($actions as $action){
            $param[]=$action;
            $q .="?,";
        }
        $q=substr($q,0,-1);
        $query="DELETE FROM resource_action WHERE id_resource=? and action IN ($q)";
        if(!$this->_DB->genQuery($query,$param)){
            $this->errMsg=_tr("DATABASE ERROR");
            return false;
        }
        return true;
    }
    
    private function orderActionsByGroup($actionGroups){
        $groupActions=$arrGroups=array();
        
        foreach($actionGroups as $action => $groups){
            foreach($groups as $group => $description){
                $groupActions[$group]['actions'][]=$action;
                $arrGroups[$group]=$description;
            }
        }
                
        //obtenemos el id de los grupos a los que ahi que insertarle permisos
        //estos son los grupos que pertenecen a la organizacion 1 
        //si el grupo no existe se lo crea
        foreach($arrGroups as $group => $description){
            $query="SELECT id FROM acl_group WHERE name=? and id_organization=1";
            $result=$this->_DB->getFirstRowQuery($query,true,array($group));
            if($result===false){
                //database error
                $this->errMsg=_tr("An error has ocurred to retrieved group info");
                return false;
            }elseif(count($result)==0){
                //no existe el grupo - lo creamos
                $query="INSERT INTO acl_group (name,description,id_organization) VALUES (?,?,1)";
                if(!$this->_DB->genQuery($query,array($group,$description))){
                    $this->errMsg=_tr("An error has ocurred to create group");
                    return false;
                }
                $groupActions[$group]['idGroup']=$this->_DB->getLastInsertId();
            }else
                $groupActions[$group]['idGroup']=$result['id'];
        }
        return $groupActions;
    }
    
    private function insertGroupPermissionDefault($id_resource,$groupActions){
        if(count($groupActions)==0) //no han sido definido acciones para el grupo
            return true;
            
        foreach($groupActions as $value){
            $param=array();
            $param[]=$value['idGroup'];
            $param[]=$id_resource;
            $q='';
            foreach($value['actions'] as $action){
                $param[]=$action;
                $q .="?,";
            }
            $q=substr($q,0,-1);
            $query="INSERT INTO group_resource_action (id_group,id_resource_action) SELECT ?,ra.id FROM resource_action ra WHERE id_resource=? and action IN ($q)";
            if(!$this->_DB->genQuery($query,$param)){
                return false;
            }
        }
        return true;
    }
    
    private function deleteGroupPermissionDefault($id_resource,$actions){
        if(!is_array($actions)){
            $this->errMsg=_tr("Invalid parameter actions");
            return false;
        }
        if(count($actions)==0)
            return true; //no ahi nada que hacer
        
        $q='';
        $param[]=$id_resource;
        foreach($actions as $action){
            $param[]=$action;
            $q .="?,";
        }
        $q=substr($q,0,-1);
        $param[]=1; //id_organization
        //borramos los permisos actuales de estas acciones
        $query="DELETE gra FROM group_resource_action gra JOIN resource_action ra ON gra.id_resource_action=ra.id JOIN acl_group g ON gra.id_group=g.id WHERE ra.id_resource=? and ra.action IN ($q) and g.id_organization=?";
        if(!$this->_DB->genQuery($query,$param)){
            $this->errMsg=_tr("DATABASE ERROR");
            return false;
        }
        return true;
    }
}
?>