<?php
/*
  vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2003 Palosanto Solutions S. A.                    |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  +----------------------------------------------------------------------+
  | Este archivo fuente está sujeto a las políticas de licenciamiento    |
  | de Palosanto Solutions S. A. y no está disponible públicamente.      |
  | El acceso a este documento está restringido según lo estipulado      |
  | en los acuerdos de confidencialidad los cuales son parte de las      |
  | políticas internas de Palosanto Solutions S. A.                      |
  | Si Ud. está viendo este archivo y no tiene autorización explícita    |
  | de hacerlo, comuníquese con nosotros, podría estar infringiendo      |
  | la ley sin saberlo.                                                  |
  +----------------------------------------------------------------------+
  | Autores:      Gladys Carrillo B.   <gcarrillo@palosanto.com>         |
  | Modificación: Adonis Figueroa A.   <afigueroa@palosanto.com>         |
  +----------------------------------------------------------------------+
  $Id: paloSantoModuloXML.class.php,v 1.1 2007/09/05 00:25:25 gcarrillo Exp $
  $Id: paloSantoModuloXML.class.php,v 1.1 2008/05/29 11:25:25 afigueroa Exp $
  $Id: paloSantoModuloXML.class.php,v 1.1 2011/01/31 10:00:00 ecueva Exp $
  $Id: paloSantoModuloXML.class.php,v 3.1 2013/09/27 10:00:00 rmera@palosato.com Exp $
*/


class ModuloXML
{
    private $arbolResource;// 
    private $xmlFile;
    private $errMsg;
    /**
     * Constructor del objeto ModuloXML
     * 
     * @param string    $sRutaArchivo   Ruta al archivo donde se encuentra el menú XML
     */
    function ModuloXML($xmlFile)
    {
        $this->xmlFile=$xmlFile;
        $this->_privado_construirArbolMenu();
    }

    private function _privado_construirArbolMenu()
    {
        $this->arbolResource = array();

        //comprabamos que realmente sea un archivo
        if (!is_file($this->xmlFile)) 
            return false;
        
        $xmlObj=simplexml_load_file($this->xmlFile);
        if($xmlObj===false){
            return false;
        }
        //atributos del recurso
        //id="email_stats" name="Email stats" idParent="email_admin" type="module" link=""  order_no="5"
        if(!isset($xmlObj->menu)){
            return false;
        }
        
        $resource['id']=(string)$xmlObj->menu['id'];
        $resource['description']=(string)$xmlObj->menu['name'];
        $resource['idParent']=(string)$xmlObj->menu['idParent'];
        $resource['type']=(string)$xmlObj->menu['type'];
        $resource['link']=(string)$xmlObj->menu['link'];
        $resource['order_no']=(string)$xmlObj->menu['order_no'];
        $resource['administrative']=(string)$xmlObj->menu->permissions['administrative'];
        $resource['org_access']=(string)$xmlObj->menu->permissions['organization_access'];
        if(isset($xmlObj->menu->permissions->actions)){
            foreach($xmlObj->menu->permissions->actions->action as $actiontag){
                $tmpAction=explode("|",(string)$actiontag['name']);
                $arrGroup=array();
                if(isset($actiontag->groups)){
                    foreach($actiontag->groups->group as $group){
                        $arrGroup[(string)$group['name']]=(string)$group['desc'];
                    }
                }
                foreach($tmpAction as $action){
                    $resource['actions'][$action]=$arrGroup;
                }
            }
        }
        $this->arbolResource = $resource;
        return true;
    }
    
    function getArbolResource(){
        return $this->arbolResource;
    }
}
?>
