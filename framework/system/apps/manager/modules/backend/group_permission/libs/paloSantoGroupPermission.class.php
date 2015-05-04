<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.5.2                                                |
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
  $Id: paloSantoGroupPermission.class.php,v 1.1 2009-05-06 04:05:41 Jonathan Vega jvega112@gmail.com Exp $ */

class paloSantoGroupPermission {
    var $pACl;

    function paloSantoGroupPermission()
    {
        global $pACL; //this variable is defined in index.php of frameWork, it's reused here
        $this->pACL = $pACL;
    }

    function ObtainNumResouces($filter_resource)
    {
        //carga en numero de recursos existentes en la bas
        return $this->pACL->getNumResources($filter_resource);
    }

    function ObtainResources($limit, $offset, $filter_resource)
    {
        //retorna los recursos existentes en la base
        return $this->pACL->getListResources($limit, $offset, $filter_resource);
    }

    function getResourcesACL()
    {
        return $this->pACL->getResources();
    }

    function getGroupsACL()
    {
        return $this->pACL->getGroups();
    }

    function getGroupPermissionsACL($id_group)
    {
        return $this->pACL->getGroupPermissions($id_group);
    }

    function loadResourceGroupPermissions($action, $id_group)
    {
        return $this->pACL->loadResourceGroupPermissions($action, $id_group);
    }

    function loadGroupPermissionsACL($id_group)
    {
        return $this->pACL->loadGroupPermissions($id_group);
    }

    function deleteGroupPermissions($action, $idGroup, $resources)
    {
        return $this->pACL->deleteGroupPermissions($action, $idGroup, $resources);
    }

    function saveGroupPermissions($action, $idGroup, $resources)
    {
        return $this->pACL->saveGroupPermissions($action, $idGroup, $resources);
    }
}
?>
