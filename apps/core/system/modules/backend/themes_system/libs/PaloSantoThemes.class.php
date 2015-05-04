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
  $Id: new_themes.php $ */

global $arrConf;
require_once "libs/paloSantoInstaller.class.php";

/* Clase que implementa themes */
class PaloSantoThemes
{
    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function PaloSantoThemes(&$pDB)
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
    
    /**
     * Procedimiento para obtener el listado de los temas 
     *
     * @return array    Listado de los temas 
     */
    function getThemes($dir='')
    {
        if($dir == ''){
            global $arrConf;
            $dir = $arrConf['basePath'];;
        }
        $arr_themes  = scandir($dir);
        $arr_respuesta = array();

        if (is_array($arr_themes) && count($arr_themes) > 0) {
            foreach($arr_themes as $key => $theme){ 
                if(is_dir($dir.$theme) && $theme!="." && $theme!="..")
                    $arr_respuesta[$theme] = $theme;
            }
        } 
        else 
            $this->errMsg = _tr("Themes not Found");
        return $arr_respuesta;
    }

    /**
     * Procedimiento para obtener de la base settings el tema actual de elastix
     *
     * @return string    nombre del tema actual si lo encontro, vacio si no 
     */
    function getThemeActual($uid)
    {
		$pACL = new paloACL($this->_DB);
        $theme=$pACL->getUserProp($uid,'theme');
        return $theme;
    }

    /**
     * Procedimiento para actualizar el tema actual de elastix
     *
     * @param   $sTheme        Nombre del tema a cambiar
     * 
     * @return  bool    true or false si actualizo o no
     */
    function updateTheme($sTheme,$uid)
    {
        global $arrConf;
        if (!preg_match('/^\w+$/', $sTheme)) {
            $this->errMsg = _tr('Invalid theme');
            return false;
        }
        if (!is_dir($arrConf['basePath']."/web/themes/$sTheme")) {
            $this->errMsg = _tr('Invalid theme');
            return false;
        }
        
		$pACL = new paloACL($this->_DB);
        if($pACL->setUserProp($uid,'theme',$sTheme,"system"))
            return true;
        else{
            $this->errMsg = _tr('The theme could not be updated');
            return false;
        }
    } 

    /**
     * Procedimiento para borrar los tpl temporales de smarty
     *
     * @param   $documentRoot        ruta del document root de la aplicacion
     * 
     * @return  bool    true or false si refresco o no
     */
    function smartyRefresh($documentRoot='')
    {
        if($documentRoot == ''){
            global $arrConf;
            $documentRoot = $arrConf['basePath'];
        }

        update_theme();
        
        return true;
    } 
}
?>
