<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0-16                                               |
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
*/

/**
 * Función para obtener un detalle de los rpms que se encuentran instalados en el sistema.
 *
 *
 * @return  mixed   NULL si no se reconoce usuario, o el DNS con clave resuelta
 */
function obtenerDetallesRPMS()
{
    $packageClass = array(
        'Kernel'    =>  NULL,
        'Elastix'   =>  array('elastix*'),
        'RoundCubeMail'  =>  array('RoundCubeMail'),
        'Mail'          =>  array('postfix', 'cyrus-imapd'),
        'IM'            =>  array('openfire'),
        'FreePBX'       =>  array('freePBX'),
        'Asterisk'      =>  array('asterisk', 'asterisk-perl', 'asterisk-addons'),
        'FAX'           =>  array('hylafax', 'iaxmodem'),
        'DRIVERS'       =>  array('dahdi', 'rhino', 'wanpipe-util'),
        
    );
    $sCommand = 'rpm -qa  --queryformat "%{name} %{version} %{release}\n"';
    foreach ($packageClass as $packageLists) {
        if (is_array($packageLists)) $sCommand .= ' '.implode(' ', array_map('escapeshellarg', $packageLists));
    }
    $sCommand .= ' | sort';
    $output = $retval = NULL;
    exec($sCommand, $output, $retval);
    $packageVersions = array();
    foreach ($output as $s) {
        $fields = explode(' ', $s);
        $packageVersions[$fields[0]] = $fields;
    }
    
    $result = array();
    foreach ($packageClass as $sTag => $packageLists) {
        if (!isset($result[$sTag])) $result[$sTag] = array();
        if ($sTag == 'Kernel') {
            // Caso especial
            $result[$sTag][] = explode(' ', trim(`uname -s -r -i`));
        } elseif ($sTag == 'Elastix') {
            // El paquete elastix debe ir primero
            if (isset($packageVersions['elastix']))
                $result[$sTag][] = $packageVersions['elastix'];
            foreach ($packageVersions as $packageName => $fields) {
                if (substr($packageName, 0, 8) == 'elastix-')
                    $result[$sTag][] = $fields;
            }
        } else {
            foreach ($packageLists as $packageName)
                $result[$sTag][] = isset($packageVersions[$packageName])
                    ? $packageVersions[$packageName]
                    : array($packageName, '(not installed)', ' ');
        }
    }
    return $result;
}

function setUserPassword()
{
    global $arrConf;
    include_once "libs/paloSantoACL.class.php";
    include_once "libs/paloSantoOrganization.class.php";

    $old_pass   = getParameter("oldPassword");
    $new_pass   = getParameter("newPassword");
    $new_repass = getParameter("newRePassword");
    $arrResult  = array();
    $arrResult['status'] = FALSE;
    if($old_pass == ""){
      $arrResult['msg'] = _tr("Please write your current password.");
      return $arrResult;
    }
    if($new_pass == "" || $new_repass == ""){
      $arrResult['msg'] = _tr("Please write the new password and confirm the new password.");
      return $arrResult;
    }
    if($new_pass != $new_repass){
      $arrResult['msg'] = _tr("The new password doesn't match with retype new password.");
      return $arrResult;
    }
    //verificamos que la nueva contraseña sea fuerte
    if(!isStrongPassword($new_pass)){
        $arrResult['msg'] = _tr("The new password can not be empty. It must have at least 10 characters and contain digits, uppers and little case letters");
        return $arrResult;
    }

    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
    $pACL = new paloACL($pDB);
    $uid = $pACL->getIdUser($user);
    if($uid===FALSE)
        $arrResult['msg'] = _tr("Please your session id does not exist. Refresh the browser and try again.");
    else{
        // verificando la clave vieja
        $val = $pACL->authenticateUser($user, md5($old_pass));
        if($val === TRUE){
            $pORG=new paloSantoOrganization($pDB);
            $status = $pORG->changeUserPassword($user,$new_pass);
            if($status){
                $arrResult['status'] = TRUE;
                $arrResult['msg'] = _tr("Elastix password has been changed.");
                $_SESSION['elastix_pass'] = md5($new_pass);
                $_SESSION['elastix_pass2'] = $new_pass;
            }else{
                $arrResult['msg'] = _tr("Impossible to change your Elastix password.")." ".$pORG->errMsg;
            }
        }else{
            $arrResult['msg'] = _tr("Impossible to change your Elastix password. User does not exist or password is wrong");
        }
    }
    return $arrResult;
}

//pendiente
function searchModulesByName()
{
    global $arrConf;
    include_once "libs/paloSantoACL.class.php";
    include_once "libs/JSON.php";
    include_once "apps/group_permission/libs/paloSantoGroupPermission.class.php";
    $json = new Services_JSON();

    $pGroupPermission = new paloSantoGroupPermission();
    $name = getParameter("name_module_search");
    $result = array();
    $arrIdMenues = array();
    $lang=get_language();
    global $arrLang;

    // obteniendo los id de los menus permitidos
    $pACL = new paloACL($arrConf['elastix_dsn']['elastix']);
    $pMenu = new paloMenu($arrConf['elastix_dsn']['elastix']);
    
    //antes de obtener el listado de los modulos debemos determinar
    //si la interfaz desde la cual se esta llamando a los metodos es administrativa o 
    //es de usuario final. 
    $tmpPath=explode("/",$arrConf['basePath']);
    if($tmpPath[count($tmpPath)-1]=='admin')
        $administrative="yes";
    else
        $administrative="no";
    
    $org_access=null;
    if(!$pACL->isUserSuperAdmin($_SESSION['elastix_user'])){
        $org_access='yes';
    }
        
    $arrSessionPermissions = $pMenu->filterAuthorizedMenus($pACL->getIdUser($_SESSION['elastix_user']),$administrative);
    if(!is_array($arrSessionPermissions))
        $arrSessionPermissions = array();
        
    $arrIdMenues = array();
    foreach($arrSessionPermissions as $key => $value){
        $arrIdMenues[] = $value['id']; // id, IdParent, Link,  Type, order_no, HasChild
    }

    $parameter_to_find = array(); // arreglo con los valores del name dada la busqueda
    // el metodo de busqueda de por nombre sera buscando en el arreglo de lenguajes y obteniendo su $key para luego buscarlo en la base de
    // datos menu.db
    if($lang != "en"){ // entonces se adjunta la busqueda con el arreglo de lenguajes en ingles
        foreach($arrLang as $key=>$value){
            $langValue    = strtolower(trim($value));
            $filter_value = strtolower(trim($name));
            if($filter_value!=""){
                if(preg_match("/^[[:alnum:]| ]*$/",$filter_value))
                    if (strpos($langValue, $filter_value) !== FALSE)
                        $parameter_to_find[] = $key;
            }
        }
    }
    $parameter_to_find[] = $name;

    // buscando en la base de datos acl.db tabla acl_resource con el campo description
    if(empty($parameter_to_find))
        $arrResult = $pACL->getListResources(25, 0, $name, $org_access, $administrative);
    else
        $arrResult = $pACL->getListResources(25, 0, $parameter_to_find, $org_access, $administrative);

    foreach($arrResult as $key2 => $value2){
        // leyendo el resultado del query
        if(in_array($value2["id"], $arrIdMenues)){
            $arrMenu['caption'] = _tr($value2["description"]);
            $arrMenu['value']   = $value2["id"];
            $result[] = $arrMenu;
        }
    }

    header('Content-Type: application/json');
    return $json->encode($result);
}

function changeMenuColorByUser()
{
    global $arrConf;
    include_once "libs/paloSantoACL.class.php";

    $color = getParameter("menuColor");
    $arrResult  = array();
    $arrResult['status'] = FALSE;

    if($color == ""){
       $color = "#454545";
    }

    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
    $pdbACL = new paloDB($arrConf['elastix_dsn']['elastix']);
    $pACL = new paloACL($pdbACL);
    $uid = $pACL->getIdUser($user);

    if($uid===FALSE)
        $arrResult['msg'] = _tr("Please your session id does not exist. Refresh the browser and try again.");
    else{
        //si el usuario no tiene un color establecido entonces se crea el nuevo registro caso contrario se lo actualiza
        if(!$pACL->setUserProp($uid,"menuColor",$color,"profile")){
            $arrResult['msg'] = _tr("ERROR DE DB: ").$pACL->errMsg;
        }else{
            $arrResult['status'] = TRUE;
            $arrResult['msg'] = _tr("OK");
        }
    }
    return $arrResult;
}

function putMenuAsBookmark($menu)
{
    global $arrConf;
    include_once "libs/paloSantoACL.class.php";
    $arrResult['status'] = FALSE;
    $arrResult['data'] = array("action" => "none", "menu" => "$menu");
    $arrResult['msg'] = _tr("Please your session id does not exist. Refresh the browser and try again.");
    if($menu != ""){
        $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
        $pdbACL = new paloDB($arrConf['elastix_dsn']['elastix']);
        $pACL = new paloACL($pdbACL);
        $uid = $pACL->getIdUser($user);
        if($uid!==FALSE){
            //antes de obtener el listado de los modulos debemos determinar
            //si la interfaz desde la cual se esta llamando a los metodos es administrativa o 
            //es de usuario final. 
            $tmpPath=explode("/",$arrConf['basePath']);
            if($tmpPath[count($tmpPath)-1]=='admin')
                $administrative="yes";
            else
                $administrative="no";
        
            //si el que realiza la accion no es el superadmin incluir en la busqueda la restriccion
            //de que el modulo puede ser accedido por la organizacion
            $org_access=(!$pACL->isUserSuperAdmin($_SESSION['elastix_user']))?'yes':NULL;
            
            //OBTENEMOS EL RECURSO
            $resource = $pACL->getResources($menu,$org_access,$administrative);
            
            $exist = false;
            $bookmarks = "SELECT aus.id AS id, ar.id AS id_menu,  ar.description AS description FROM user_shortcut aus, acl_resource ar WHERE id_user = ? AND aus.type = 'bookmark' AND ar.id = aus.id_resource ORDER BY aus.id DESC";
            $arr_result1 = $pdbACL->fetchTable($bookmarks, TRUE, array($uid));
            if($arr_result1 !== FALSE){
                $i = 0;
                $arrIDS = array();
                foreach($arr_result1 as $key => $value){
                    if($value['id_menu'] == $menu)
                        $exist = true;
                }
                //existia anteriormente se procede a eliminarlo del bookmark
                if($exist){
                    $pdbACL->beginTransaction();
                    $query = "DELETE FROM user_shortcut WHERE id_user = ? AND id_resource = ? AND type = ?";
                    $r = $pdbACL->genQuery($query, array($uid, $menu, "bookmark"));
                    if(!$r){
                        $pdbACL->rollBack();
                        $arrResult['status'] = FALSE;
                        $arrResult['data'] = array("action" => "delete", "menu" => _tr($resource[0][1]), "idmenu" => $menu, "menu_session" => $menu);
                        $arrResult['msg'] = _tr("Bookmark cannot be removed. Please try again or contact with your elastix administrator and notify the next error: ").$pdbACL->errMsg;
                        return $arrResult;
                    }else{
                        $pdbACL->commit();
                        $arrResult['status'] = TRUE;
                        $arrResult['data'] = array("action" => "delete", "menu" => _tr($resource[0][1]), "idmenu" => $menu,  "menu_session" => $menu);
                        $arrResult['msg'] = _tr("Bookmark has been removed.");
                        return $arrResult;
                    }
                }

                //no existia anteriormente se lo agrega
                if(count($arr_result1) > 4){
                    $arrResult['msg'] = _tr("The bookmark maximum is 5. Please uncheck one in order to add this bookmark");
                }else{
                    $pdbACL->beginTransaction();
                    $query = "INSERT INTO user_shortcut(id_user, id_resource, type) VALUES(?, ?, ?)";
                    $r = $pdbACL->genQuery($query, array($uid, $menu, "bookmark"));
                    if(!$r){
                        $pdbACL->rollBack();
                        $arrResult['status'] = FALSE;
                        $arrResult['data'] = array("action" => "add", "menu" => _tr($resource[0][1]), "idmenu" => $menu,  "menu_session" => $menu );
                        $arrResult['msg'] = _tr("Bookmark cannot be added. Please try again or contact with your elastix administrator and notify the next error: ").$pdbACL->errMsg;
                    }else{
                        $pdbACL->commit();
                        $arrResult['status'] = TRUE;
                        $arrResult['data'] = array("action" => "add", "menu" => _tr($resource[0][1]), "idmenu" => $menu,  "menu_session" => $menu );
                        $arrResult['msg'] = _tr("Bookmark has been added.");
                        return $arrResult;
                    }
                }
            }
        }
    }
    return $arrResult;
}

/**
 * Funcion que se encarga de guardar o editar una nota de tipo sticky note.
 *
 * @return array con la informacion como mensaje y estado de resultado
 * @param string $menu nombre del menu al cual se le va a agregar la nota
 * @param string $description contenido de la nota que se desea agregar o editar
 *
 * @author Eduardo Cueva
 * @author ecueva@palosanto.com
 */
function saveStickyNote($menu, $description, $popup)
{
    global $arrConf;
    include_once "libs/paloSantoACL.class.php";
    $arrResult['status'] = FALSE;
    $arrResult['msg'] = _tr("Please your session id does not exist. Refresh the browser and try again.");
    if($menu != ""){
        $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
        $pdbACL = new paloDB($arrConf['elastix_dsn']['elastix']);
        $pACL = new paloACL($pdbACL);
        //$id_resource = $pACL->getIdResource($menu);
        $uid = $pACL->getIdUser($user);
        $date_edit = date("Y-m-d h:i:s");
        if($uid!==FALSE){
            $exist = false;
            $query = "SELECT * FROM sticky_note WHERE id_user = ? AND id_resource = ?";
            $arr_result1 = $pdbACL->getFirstRowQuery($query, TRUE, array($uid, $menu));
            if($arr_result1 !== FALSE && count($arr_result1) > 0)
                $exist = true;

            if($exist){
                $pdbACL->beginTransaction();
                $query = "UPDATE sticky_note SET description = ?, date_edit = ?, auto_popup = ? WHERE id_user = ? AND id_resource = ?";
                $r = $pdbACL->genQuery($query, array($description, $date_edit, $popup, $uid, $menu));
                if(!$r){
                    $pdbACL->rollBack();
                    $arrResult['status'] = FALSE;
                    $arrResult['msg'] = _tr("Request cannot be completed. Please try again or contact with your elastix administrator and notify the next error: ").$pdbACL->errMsg;
                    return $arrResult;
                }else{
                    $pdbACL->commit();
                    $arrResult['status'] = TRUE;
                    $arrResult['msg'] = "";
                    return $arrResult;
                }
            }else{
                $pdbACL->beginTransaction();
                $query = "INSERT INTO sticky_note(id_user, id_resource, date_edit, description, auto_popup) VALUES(?, ?, ?, ?, ?)";
                $r = $pdbACL->genQuery($query, array($uid, $menu, $date_edit, $description, $popup));
                if(!$r){
                    $pdbACL->rollBack();
                    $arrResult['status'] = FALSE;
                    $arrResult['msg'] = _tr("Request cannot be completed. Please try again or contact with your elastix administrator and notify the next error: ").$pdbACL->errMsg;
                    return $arrResult;
                }else{
                    $pdbACL->commit();
                    $arrResult['status'] = TRUE;
                    $arrResult['msg'] = "";
                    return $arrResult;
                }
            }
        }
    }
    return $arrResult;
}

function saveNeoToggleTabByUser($menu, $action_status)
{
    global $arrConf;
    include_once "libs/paloSantoACL.class.php";
    $arrResult['status'] = FALSE;
    $arrResult['msg'] = _tr("Please your session id does not exist. Refresh the browser and try again.");
    if($menu != ""){
        $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";
        $pdbACL = new paloDB($arrConf['elastix_dsn']['elastix']);
        $pACL = new paloACL($pdbACL);
        $uid = $pACL->getIdUser($user);
        if($uid!==FALSE){
            $exist = false;
            $togglesTabs = "SELECT * FROM user_shortcut WHERE id_user = ? AND type = 'NeoToggleTab'";
            $arr_result1 = $pdbACL->getFirstRowQuery($togglesTabs, TRUE, array($uid));
            if($arr_result1 !== FALSE && count($arr_result1) > 0)
                $exist = true;

            if($exist){
                $pdbACL->beginTransaction();
                $query = "UPDATE user_shortcut SET description = ? WHERE id_user = ? AND type = ?";
                $r = $pdbACL->genQuery($query, array($action_status, $uid, "NeoToggleTab"));
                if(!$r){
                    $pdbACL->rollBack();
                    $arrResult['status'] = FALSE;
                    $arrResult['msg'] = _tr("Request cannot be completed. Please try again or contact with your elastix administrator and notify the next error: ").$pdbACL->errMsg;
                    return $arrResult;
                }else{
                    $pdbACL->commit();
                    $arrResult['status'] = TRUE;
                    $arrResult['msg'] = _tr("Request has been sent.");
                    return $arrResult;
                }
            }else{
                $pdbACL->beginTransaction();
                $query = "INSERT INTO user_shortcut(id_user, id_resource, type, description) VALUES(?, ?, ?, ?)";
                $r = $pdbACL->genQuery($query, array($uid, $menu, "NeoToggleTab", $action_status));
                if(!$r){
                    $pdbACL->rollBack();
                    $arrResult['status'] = FALSE;
                    $arrResult['msg'] = _tr("Request cannot be completed. Please try again or contact with your elastix administrator and notify the next error: ").$pdbACL->errMsg;
                    return $arrResult;
                }else{
                    $pdbACL->commit();
                    $arrResult['status'] = TRUE;
                    $arrResult['msg'] = _tr("Request has been sent.");
                    return $arrResult;
                }
            }
        }
    }
    return $arrResult;
}

function getChatClientConfig($pDB,&$error)
{
    $query = 'SELECT property_name,property_val from elx_chat_config';
    $result = $pDB->fetchTable($query,true);
    if ($result===false) {
        //error de conexion a la base no podemos determinar los parametros de configuracion del chat
        //mostramos un error
        $error = 'Error to obtain elastix chat configurations';
        return false;
    }
    
    $chat_conf = array();
    $type_connection = 'ws';
    foreach($result as $value) switch($value['property_name']) {
    // Caso especial: wss se usa para construir URLs más abajo
    case 'type_connection':
        if (in_array($value['property_val'], array('ws', 'wss')))
            $type_connection = $value['property_val'];
        break;

    // Enteros

    // Registration expiry time (in seconds) (Integer). Default value is 600.
    case 'register_expires':
    /* Time (in seconds) (Integer) after which an incoming call is rejected if 
     * not answered. Default value is 60. */
    case 'no_answer_timeout':
    /* Minimum interval (Number) in seconds between WebSocket reconnection 
     * attempts. Default value is 2 */
    case 'connection_recovery_min_interval':
    /* Minimum interval (Number) in seconds between WebSocket reconnection 
     * attempts. Default value is 2 */
    case 'connection_recovery_max_interval':
        if ($value['property_val'] != '' && ctype_digit($value['property_val']))
            $chat_conf[$value['property_name']] = (int)$value['property_val'];
        break;

    // Booleanos            
             
    /* Indicate if JsSIP User Agent should register automatically when starting.
     * Valid values are true and false (Boolean). Default value is true. */
    case 'register':
    /* Indicate whether incoming and outgoing SIP request/responses must be 
     * logged in the browser console (Boolean). Default value is false */
    case 'trace_sip':
    /* If set to true every SIP initial request sent by JsSIP includes a Route 
     * header with the SIP URI associated to the WebSocket server as value. Some
     * SIP Outbound Proxies require such a header. Valid values are true and 
     * false (Boolean). Default value is false. */
    case 'use_preloaded_route':
    /* Set Via transport parameter in outgoing SIP requests to “TCP”, Valid 
     * values are true and false (Boolean). Default value is false. */
    case 'hack_via_tcp':
    /* Set a random IP address as the host value in the Contact header field and
     * Via sent-by parameter. Valid values are true and false (Boolean). Default
     * value is a false. */
    case 'hack_ip_in_contact':
        $chat_conf[$value['property_name']] = ($value['property_val'] == 'yes');
        break;

    // Cadenas de texto
    default:
        $chat_conf[$value['property_name']] = $value['property_val'];
        break;
    }
    
    //obtenemos las configuraciones de asterisk del module http para web_socket support
    $http = getAsteriskHttpModuleConfig($pDB,$error);
    if($http === false) return false;
    
    //se quiere usar wss debe estar habilitado soporte tls
    //si no está habilitado se debe usar ws en su lugar
    if (!isset($http['tlsenable']) || $http['tlsenable']=='no') {
        $type_connection = 'ws';
    }

    // Llenar valores por omisión si no están presentes, y elegir en base a wss
    if (empty($http['bindport'])) $http['bindport'] = 8088;
    if (empty($http['tlsbindport'])) $http['tlsbindport'] = 8089;
    $puerto = $http[($type_connection == 'wss') ? 'tlsbindport' : 'bindport'];
    
    //"ws://192.168.5.110:8088/asterisk/ws"
    //ws -> transport, puede ser ws o wss
    //192.168.5.110 -> server
    //8088 -> puerto usado para la comunicacion definido en /etc/asterisk/http.conf 
    //asterisk -> prefix usado para la coneccion definido en http.conf
    //el ultimo ws simpre va, esto es una especificacion de asterisk
    $http['prefix'] = (isset($http['prefix']) && !empty($http['prefix']))
        ? '/'.$http['prefix'] : '';
    $chat_conf['elastix_chat_server'] = $_SERVER['SERVER_NAME'];
    $chat_conf['ws_servers'] = "{$type_connection}://{$chat_conf['elastix_chat_server']}:{$puerto}{$http['prefix']}/ws";
    return $chat_conf;
}
function getAsteriskHttpModuleConfig($pDB,&$error){
    $query="SELECT property_name,property_val FROM http_ast";
    $result=$pDB->fetchTable($query,true);
    if($result===false){
        //problemas con la coneccion no podemos determinar los 
        //valores usados para configurar web_socket con asterisk
        $error='Error to obtain asterisk web socket configurations';
        return false;
    }
    $http_conf=array();
    foreach($result as $value){
        $http_conf[$value['property_name']]=$value['property_val'];
    }
    return $http_conf;
}
function getStatusContactFromCode($code){
    /*
        -1 = Extension not found
        0 = Idle
        1 = In Use
        2 = Busy
        4 = Unavailable
        8 = Ringing
        16 = On Hold
    */
    switch($code){
        case "0":
            $status=_tr('Idle');
            break;
        case "1":
            $status=_tr('In Use');
            break;
        case "2":
            $status=_tr('Busy');
            break;
        case "4":
            $status=_tr('Unavailable');
            break;
        case "8":
            $status=_tr('Ringing');
            break;
        case "16":
            $status=_tr('On Hold');
            break;
        default:
            $status=_tr('Extension not found');
            break;
    }
    return $status;
}


function getDataProfile($pDB, &$ERROR){
    $arrCredentials=getUserCredentials($_SESSION['elastix_user']);
   
    $data = array($arrCredentials['idUser'],$arrCredentials['domain']);
    $query="select u.username, u.name, e.exten, u.fax_extension, e.device ".
                    "FROM extension e JOIN acl_user u ON e.exten=u.extension ".
                       "WHERE u.id=? AND e.organization_domain=?";
    
    $dataProfile=$pDB->getFirstRowQuery($query,true,$data);
   
    if($dataProfile===FALSE){
        $ERROR = $pDB->errMsg;
        return false;
    }else{
        return $dataProfile;
    }
    
}

function leer_directorio($directorio,$error_msg,&$archivos){
    $bExito=FALSE;
    $archivos=array();
    if (file_exists($directorio)) {
        if ($handle = opendir($directorio)) {
            $bExito=true;
            while (false !== ($file = readdir($handle))) {
               //no tomar en cuenta . y ..
                if ($file!="." && $file!=".." )
                    $archivos[]=$file;
            }
            closedir($handle);
        }

     }else
        $error_msg ="No existe directorio";

     return $bExito;
}

function deleteImgProfile($pDB, &$ERROR){
    $arrCredentials=getUserCredentials($_SESSION['elastix_user']);
   
    $data = array($arrCredentials['idUser']);
    $query="update acl_user set picture_type=NULL, picture_content=NULL where id=?";
    
    $result=$pDB->genQuery($query,$data);
        
    if($result==FALSE){
        $ERROR = $pDB->errMsg;
        return false;
    }else{
        return true;
        }
    
}

function redimensionarImagen($ruta1,$ruta2,$ancho,$alto){

    # se obtene la dimension y tipo de imagen
    $datos=getimagesize($ruta1);

    if(!$datos)
        return false;

    $ancho_orig = $datos[0]; # Anchura de la imagen original
    $alto_orig = $datos[1];    # Altura de la imagen original
    $tipo = $datos[2];
    $img = "";
    if ($tipo==1){ # GIF
        if (function_exists("imagecreatefromgif"))
            $img = imagecreatefromgif($ruta1);
        else
            return false;
    }
    else if ($tipo==2){ # JPG
        if (function_exists("imagecreatefromjpeg"))
            $img = imagecreatefromjpeg($ruta1);
        else
            return false;
    }
    else if ($tipo==3){ # PNG
        if (function_exists("imagecreatefrompng"))
            $img = imagecreatefrompng($ruta1);
        else
            return false;
    }

    $anchoTmp = imagesx($img);
    $altoTmp = imagesy($img);
    if(($ancho > $anchoTmp || $alto > $altoTmp)){
        ImageDestroy($img);
        return true;
    }

    # Se calculan las nuevas dimensiones de la imagen
    if ($ancho_orig>$alto_orig){
        $ancho_dest=$ancho;
        $alto_dest=($ancho_dest/$ancho_orig)*$alto_orig;
    }else{
        $alto_dest=$alto;
        $ancho_dest=($alto_dest/$alto_orig)*$ancho_orig;
    }

    // imagecreatetruecolor, solo estan en G.D. 2.0.1 con PHP 4.0.6+
    $img2=@imagecreatetruecolor($ancho_dest,$alto_dest) or $img2=imagecreate($ancho_dest,$alto_dest);

    // Redimensionar
    // imagecopyresampled, solo estan en G.D. 2.0.1 con PHP 4.0.6+
    @imagecopyresampled($img2,$img,0,0,0,0,$ancho_dest,$alto_dest,$ancho_orig,$alto_orig) or imagecopyresized($img2,$img,0,0,0,0,$ancho_dest,$alto_dest,$ancho_orig,$alto_orig);

    // Crear fichero nuevo, segÃºn extensiÃ³n.
    if ($tipo==1) // GIF
    if (function_exists("imagegif"))
        imagegif($img2, $ruta2);
    else
        return false;

    if ($tipo==2) // JPG
    if (function_exists("imagejpeg"))
        imagejpeg($img2, $ruta2);
    else
        return false;

    if ($tipo==3)  // PNG
    if (function_exists("imagepng"))
        imagepng($img2, $ruta2);
    else
        return false;

    return true;
}

function getNewListElastixAccounts($searchFilter, &$errmsg)
{
    global $arrConf;

    $error = '';
    $pDB = new paloDB($arrConf['elastix_dsn']["elastix"]);
    $pACL = new paloACL($pDB);
    
    $astMang=AsteriskManagerConnect($error);
    if($astMang==false){
        $this->errMsg = $error;
        return false;
    }
    
    $arrCredentials=getUserCredentials($_SESSION['elastix_user']);
    
    //obtenemos el codigo pbx de la organizacion
    $query="SELECT code from organization where id=?";
    $result=$pDB->getFirstRowQuery($query,false,array($arrCredentials["id_organization"]));
    if ($result==false) {
        $errmsg = "An error has ocurred to retrieved organization data. ";
        return false;
    } else
        $pbxCode=$result[0];
    
    //1) obtenemos los parametros generales de configuracion para asterisk websocket y el cliente de chat de elastix
    $chatConfig=getChatClientConfig($pDB,$error);
    if ($chatConfig==false) {
        $errmsg = "An error has ocurred to retrieved server configuration params. ".$error;
        return false;
    }
    
    //2) TODO:obtener el dominio sip de la organizacion si no se encuentra configurado utilizar
    //   el ws_server
    $dominio = $chatConfig['elastix_chat_server'];
    
    //3) obtenemos la informacion de las cuentas de los usuarios
    $name= null;
    if(!empty($searchFilter))
        $name= $searchFilter;
    
    $result=$pACL->getUsersAccountsInfoByDomain($arrCredentials["id_organization"], $name);
    if($result===false){
        //hubo un error de la base de datos ahi que desactivar la columna lateral
        $errmsg = "An error has ocurred to retrieved Contacts Info. ".$pACL->errMsg;
        return false;
    }else{
        $arrContacts=array();
        foreach($result as $key => $value){
            //TODO: por el momento se obtine la presencia del usuario al
            // travès de AMI con la función que extension_state
            // en el futuro esto debe ser manejado con la libreria jssip
            // actualmente este libreria no tiene esa funcion implementada
            /*
            -1 = Extension not found
            0 = Idle
            1 = In Use
            2 = Busy
            4 = Unavailable
            8 = Ringing
            16 = On Hold
            */
            if ($value['extension'] != '' && isset($value['extension'])) {
                $result = $astMang->send_request('ExtensionState',array(
                    'Exten'=>"{$value['extension']}",
                    'Context'=>"$pbxCode-ext-local"
                ));
                if($result['Response']=='Success'){
                    $status=getStatusContactFromCode($result['Status']);
                    $st_code=$result['Status'];
                    if($result['Status']=='-1'){
                        $index_st='not_found';
                    }elseif($result['Status']=='4'){
                        $index_st='unava';
                    }else{
                        $index_st='ava';
                    }
                }else{
                    //TODO:ahi un error con el manager y nopuede determinar le estado de los
                    //contactos por lo tanto dejo a todas como disponibles
                    $index_st='ava';
                    $st_code=0;
                    $status=_tr('Idle');
                }
                if ($value['id'] != $arrCredentials['idUser']) {   
                    $arrContacts[$index_st][$key]['idUser']=$value['id'];
                    $arrContacts[$index_st][$key]['display_name']=$value['name'];
                    $arrContacts[$index_st][$key]['username']=$value['username'];
                    $arrContacts[$index_st][$key]['presence']=$status;
                    $arrContacts[$index_st][$key]['st_code']=$st_code;
                    $arrContacts[$index_st][$key]['uri']="{$value['elxweb_device']}@$dominio";
                    $arrContacts[$index_st][$key]['alias']="{$value['alias']}@$dominio";
                }else{
                    $arrContacts['my_info']['uri']="{$value['elxweb_device']}@$dominio";
                    $arrContacts['my_info']['ws_servers']=$chatConfig['ws_servers'];
                    $arrContacts['my_info']['password']=$_SESSION['elastix_pass2'];
                    $arrContacts['my_info']['display_name']=$value['name'];
                    $arrContacts['my_info']['elxuser_username']=$value['username'];
                    $arrContacts['my_info']['elxuser_exten']=$value['extension'];
                    $arrContacts['my_info']['elxuser_faxexten']=$value['fax_extension'];
                    $arrContacts['my_info']['st_code']=$st_code;
                    foreach($chatConfig as $key => $value){
                        $arrContacts['my_info'][$key] = $value;
                    }
                }
            }
        }
        $resultado = $arrContacts;
    }
    $astMang->disconnect();
    return $resultado;
    
}


function getChatContactsStatus($searchFilter)
{
    $jsonObject = new PaloSantoJSON();
    $dummy = NULL;    
    $newListContacts=getNewListElastixAccounts($searchFilter, $dummy);
    
    if($newListContacts===false){   
        $status = FALSE;
    }else{       
        // 1 COMPARA EL VALOR DEVUELTO CON EL VALOR QUE ESTA EN SESION
        //SI HUBO UN CAMBIO
        // si hay cambio status true
        // poner el nuevo valor el seesion
        $session = getSession();        
        //var_dump($session['chatlistStatus']);
        //print_r("---------------------------------------------------------------------------------------");
        //var_dump($newListContacts);
        //file_put_contents("/tmp/testchat",);
        if($session['chatlistStatus']!= $newListContacts)
        {
            $msgResponse = $newListContacts;
            $status = true;
        }else{
            $status = false;
        }

        if($status){ //hubo un cambio
            $jsonObject->set_status("CHANGED");
            $jsonObject->set_message($msgResponse); //el valor del status actual
        }else{
            $jsonObject->set_status("NOCHANGED");
        }
    }
    
    $session['chatlistStatus'] = $newListContacts;
    putSession($session);
    
    return array("there_was_change" => $status,
                "data" => $jsonObject->createJSON());
}

function getSession()
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $tmp = $_SESSION;
        session_commit();
    }
    return $tmp;
}

function putSession($data)//data es un arreglo
{
    session_commit();
    ini_set("session.use_cookies","0");
    if(session_start()){
        $_SESSION = $data;
        session_commit();
    }
}


?>