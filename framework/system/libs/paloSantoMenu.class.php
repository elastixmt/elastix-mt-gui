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
  $Id: paloSantoMenu.class.php,v 1.2 2007/09/05 00:25:25 gcarrillo Exp $ */

$elxPath="/usr/share/elastix";
include_once("$elxPath/libs/paloSantoDB.class.php");

class paloMenu {

    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function paloMenu(&$pDB)
    {
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
    }

    function cargar_menu()
    {
       //leer el contenido de la tabla menu y devolver un arreglo con la estructura
        $menu = array ();
        $query="Select m1.id, m1.IdParent, m1.Link, m1.description, m1.Type, m1.order_no,".
               "(Select count(*) from acl_resource m2 where m2.IdParent=m1.id) as HasChild from acl_resource m1 order by order_no asc;";
        $oRecordset = $this->_DB->fetchTable($query, true);
        if ($oRecordset){
            foreach($oRecordset as $key => $value)
            {
                if($value['HasChild']>0)
                    $value['HasChild'] = true;
                else $value['HasChild'] = false;
                $menu[$value['id']]= $value;
            }
        }
        return $menu;
    }

    function filterAuthorizedMenus($idUser,$administrative='no')
    {
        global $arrConf;
        require_once("libs/paloSantoACL.class.php");
        $pACL = new paloACL($this->_DB);
        $org_access='';
        $uelastix = FALSE;
        if (isset($_SESSION)) {
            $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
            if (empty($pDB->errMsg)) {
                $uelastix = get_key_settings($pDB, 'uelastix');
                $uelastix = ((int)$uelastix != 0);
            }
            unset($pDB);
        }
        
        if ($uelastix && isset($_SESSION['elastix_user_permission']))
            return $_SESSION['elastix_user_permission'];

        
        $superAdmin=$pACL->isUserSuperAdmin($_SESSION['elastix_user']);
        //el usuario superadmin solo tiene acceso a los modulos administrativos
        if($superAdmin && $administrative=='no'){
            return NULL;
        }
        
        if(!$superAdmin){
            //comprobamos que el modulo puede ser accesado por la organizacion
            $org_access="AND ar.organization_access='yes'"; 
        }
        
        //obtenemos el id del grupo al que pertecene el usuario
        $idGroup=$pACL->getUserGroup($idUser);
        if($idGroup==false)
            return NULL;
            
        //seleccionamos los recuersos a los cuales la organizacion a la que pertenece el usuario tiene acceso
        //y de eso hacemos uns interseccion con la 
        //union de las acciones permitidas por el grupo al que pertenece el usuario
        //y las acciones permitidas a el usuario
        $query="SELECT ar.id, ar.IdParent, ar.Link, ar.description, ar.Type, ar.order_no 
                    FROM acl_resource ar JOIN organization_resource ore ON ar.id=ore.id_resource 
                        JOIN acl_group g ON g.id_organization=ore.id_organization  
                        WHERE g.id=? AND ar.administrative=? $org_access AND ar.id IN 
                            (SELECT ract.id_resource FROM resource_action ract 
                                JOIN group_resource_action as gr ON ract.id=gr.id_resource_action 
                                WHERE gr.id_group=? AND ract.action='access'  
                            UNION  
                            SELECT ract.id_resource FROM user_resource_action as ur  
                                    JOIN resource_action ract ON ract.id=ur.id_resource_action  
                                    WHERE ur.id_user=? AND ract.action='access') ORDER BY ar.order_no";
        $arrModulesFiltered = array();
        
        $r = $this->_DB->fetchTable($query, TRUE, array($idGroup,$administrative,$idGroup,$idUser));
        if (!is_array($r)) {
            $this->errMsg = $this->_DB->errMsg;
        	return NULL;
        }

        foreach ($r as $tupla) {
        	$tupla['HasChild'] = FALSE;
            $arrModulesFiltered[$tupla['id']] = $tupla;
        }

        //Leer el nombre de todos los menus dentro de acl_resource 
        $r = $this->_DB->fetchTable(
            'SELECT ar.id, ar.IdParent, ar.Link, ar.description, ar.Type, ar.order_no, 1 AS HasChild '.
            "FROM acl_resource ar WHERE ar.administrative=? $org_access ORDER BY ar.order_no", TRUE, array($administrative));
        if (!is_array($r)) {
            $this->errMsg = $this->_DB->errMsg;
            return NULL;
        }
        
        $allMenus = array();
        foreach ($r as $tupla) {
            $tupla['HasChild'] = FALSE;
            $allMenus[$tupla['id']] = $tupla;
        }
                
        //resolveoms referencia a los niveles superiores
        $menuMenus = array();
        foreach (array_keys($arrModulesFiltered) as $k) {
            if($arrModulesFiltered[$k]['Type']=='module'){
                $menuMenus[$k]=$k;
            }
            $kp = $arrModulesFiltered[$k]['IdParent'];
            if (isset($allMenus[$kp])) { //menu de segundo o tercer nivel
                $menuMenus[$kp] = $kp;
                //se hace esta verificacion para que loe menus de primer nivel sean incluidos
                if(isset($allMenus[$kp]['IdParent'])){
                    $menuMenus[$allMenus[$kp]['IdParent']] = $allMenus[$kp]['IdParent'];
                }
            } 
        }
        

        // Copiar al arreglo filtrado los menús de primer nivel y segundo nivel EN EL ORDEN LEÍDO
        $arrMenuFiltered = array_intersect_key($allMenus, $menuMenus);
        
        if ($uelastix) $_SESSION['elastix_user_permission'] = $arrMenuFiltered;
        return $arrMenuFiltered;
    }

    /**
     * Procedimiento para obtener el listado de los menus
     *
     * @return array    Listado de menus
     */
    function getRootMenus()
    {
        $this->errMsg = "";
        $listaMenus = array();
        $sQuery = "SELECT id, description FROM acl_resource WHERE IdParent=''";
        $arrMenus = $this->_DB->fetchTable($sQuery);
        if (is_array($arrMenus)) {
        foreach ($arrMenus as $menu)
            {
                $listaMenus[$menu[0]]=$menu[1];
            }
        }else
        {
            $this->errMsg = $this->_DB->errMsg;
        }
        return $listaMenus;

    }

    
    /**
     * This function is for obtaining all the submenu from menu 
     *
     * @param string    $menu_name   The name of the main menu or menu father       
     *
     * @return array    $result      An array of children or submenu where the father or main menu is $menu_name
     */
    function getChilds($menu_name){
            $query   = "SELECT id, IdParent, Link, description, Type, order_no FROM acl_resource where IdParent=?";
            $result=$this->_DB->fetchTable($query, true, array($menu_name));
            if($result==FALSE){
                $this->errMsg = $this->_DB->errMsg;
                return 0;
            }
            return $result;
    }
}
?>