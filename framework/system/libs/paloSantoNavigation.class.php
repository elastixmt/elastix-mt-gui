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
  $Id: paloSantoNavigation.class.php,v 1.2 2007/09/07 00:20:03 gcarrillo Exp $ */

define('MENUTAG', 'description');
define('MAX_THEME_LEVEL', 3);

class paloSantoNavigationBase
{
    protected $_menubase;     // Lista de todos los items de primer nivel
    protected $_menunodes;    // Todos los items de menú, indexados por id de menu
    protected $_selection;    // Arreglo de IDs de menú desde primer nivel hasta selección

    /**
     * Constructor de objeto de manejo del menú. El parámetro arrMenu es un 
     * arreglo de tuplas. La clave de cada tupla es el valor del item del menú.
     * Cada tupla contiene los siguientes elementos:
     *  id          Valor de item del menú, debe ser idéntico a la clave de tupla
     *  IdParent    Valor de item del menú que contiene a este menú, o es empty()
     *              si es un item de primer nivel de menú.
     *  Description        Etiqueta a mostrar en el menú para este item
     *  Type        module|framed|empty() 
     *  Link        Si no es empty(), plantilla de enlace para Type=framed
     *  order_no    (no se usa)
     *  HasChild    (se sobreescribe según presencia o ausencia de hijos)
     * 
     * @param   array   $arrMenu    Lista de menús
     * @param   string  $idMenuSelected Si != NULL, elemento inicial a seleccionar
     * @param   object  $smarty     Referencia a objeto Smarty para plantillas
     */
    function __construct($arrMenu, $idMenuSelected = NULL)
    {
        // Construcción del árbol de menú
        $this->_menubase = array();
        $this->_menunodes = array();
        foreach ($arrMenu as $menuitem) {
            if (empty($menuitem['IdParent'])) $menuitem['IdParent'] = NULL;
            $menuitem['children'] = array();
            $menuitem['parent'] = NULL;
            $menuitem['HasChild'] = FALSE;
            $this->_menunodes[$menuitem['id']] = $menuitem;
        }
        foreach (array_keys($this->_menunodes) as $id) {
            $id_parent = $this->_menunodes[$id]['IdParent'];
            if (!is_null($id_parent) && !isset($this->_menunodes[$id_parent]))
                $this->_menunodes[$id]['IdParent'] = $id_parent = NULL;
            if (is_null($id_parent)) {
                $this->_menubase[$id] = &$this->_menunodes[$id];
            } else {
                $this->_menunodes[$id_parent]['HasChild'] = TRUE;
                $this->_menunodes[$id_parent]['children'][$id] = &$this->_menunodes[$id];
                $this->_menunodes[$id]['parent'] = &$this->_menunodes[$id_parent];
            }
        }
        $this->setSelectedModule($idMenuSelected);
    }

    /**
     * Procedimiento para obtener un arreglo que representa la ruta a través del
     * menú desde el primer nivel hasta el módulo seleccionado. Si el item es
     * un item con hijos, se completa con el primer hijo en cada nivel hasta
     * el último nivel. 
     * 
     * @param   $idMenuSelected string  Item de menú que se ha seleccionado.
     * 
     * @return  mixed   NULL si el árbol está vacío, o la ruta como arreglo
     */
    private function _getMenuSelectionPath($idMenuSelected)
    {
        if (empty($idMenuSelected)) $idMenuSelected = NULL;
        if (!is_null($idMenuSelected) && !isset($this->_menunodes[$idMenuSelected]))
            $idMenuSelected = NULL;
        if (is_null($idMenuSelected) && count($this->_menubase) > 0)
            $idMenuSelected = array_shift(array_keys($this->_menubase));
        if (is_null($idMenuSelected)) return NULL;

        /* En este punto se $m es el nodo seleccionado, el cual puede estar en
         * cualquier parte del árbol. Primero se navegará por este nodo y los
         * hijos hasta llegar al último nivel. Luego, del mismo nodo, se 
         * obtendrán los padres hasta llegar al primer nivel */
        $path = array();
        $m = &$this->_menunodes[$idMenuSelected];
        array_push($path, $m['id']);
        while (count($m['children']) > 0) {
            $m = &$m['children'][array_shift(array_keys($m['children']))];
            array_push($path, $m['id']);
        }
        $m = &$this->_menunodes[$idMenuSelected];
        while (!is_null($m['parent'])) {
            $m = &$m['parent'];
            array_unshift($path, $m['id']);
        }
        return $path;
    }

    /**
     * Asignar como item actual el módulo seleccionado según el item indicado
     * 
     * @param   $idMenuSelected string  Item de menú que se ha seleccionado.
     * 
     * @return  void
     */
    function setSelectedModule($idMenuSelected)
    {
        $this->_selection = $this->_getMenuSelectionPath($idMenuSelected);
    }

    /**
     * Obtener el item actualmente seleccionado
     * 
     * @return  mixed   NULL si el elemento es inválido, o el módulo seleccionado
     */
    function getSelectedModule()
    {
        return is_null($this->_selection) ? NULL : $this->_selection[count($this->_selection) - 1];
    }
    
    /**
     * Obtener la ruta a través del menú hasta el item de módulo seleccionado
     * 
     * @return  mixed   NULL si el elemento es inválido, o el módulo seleccionado
     */
    function getSelectedModulePath()
    {
    	return $this->_selection;
    }
}

class paloSantoNavigation extends paloSantoNavigationBase
{
    private $_smarty;       // Objeto Smarty para las plantillas

    function __construct($arrMenu, &$smarty, $idMenuSelected = NULL)
    {
        parent::__construct($arrMenu, $idMenuSelected);
        $this->_smarty = &$smarty;
    }

    // TODO: esta función es usada por extras/developer así que por ahora es pública
    function getArrSubMenu($idParent)
    {
        if (!empty($idParent)) {
            if (!isset($this->_menunodes[$idParent])) return FALSE;
            $children = &$this->_menunodes[$idParent]['children'];
        } else {
            $children = &$this->_menubase;
        }
        $arrSubMenu = array();
        foreach ($children as $element) {
            unset($element['parent']);
            unset($element['children']);
            $arrSubMenu[$element['id']] = $element;
        }
        if (count($arrSubMenu) <= 0) return FALSE;
        return $arrSubMenu;
    }

    function renderMenuTemplates()
    {
    	if (is_null($this->_selection)) die('FATAL: Unable to render with empty menu!');
        
        // Generar las listas de items de menú en formato compatible con temas
        $menuItemsForThemes = array();
        $nodeListRef = &$this->_menubase;
        $i = 0;
        foreach ($this->_selection as $menuItem) {
        	if ($i >= MAX_THEME_LEVEL) break;
            
            $menuItemsForThemes[$i] = &$nodeListRef;
            $nodeListRef = &$this->_menunodes[$menuItem]['children'];
            $i++;
        }

        // Asignar las listas genéricas
        $smartyVars = array(
            array('arrMainMenu', 'idMainMenuSelected', 'nameMainMenuSelected'),
            array('arrSubMenu',  'idSubMenuSelected',  'nameSubMenuSelected'),
            array('arrSubMenu2', 'idSubMenu2Selected', 'nameSubMenu2Selected'),
        );
        for ($i = 0; $i < count($menuItemsForThemes); $i++) {
        	$this->_smarty->assign($smartyVars[$i][0], $menuItemsForThemes[$i]);
            $this->_smarty->assign($smartyVars[$i][1], $this->_selection[$i]);
            $this->_smarty->assign($smartyVars[$i][2], $this->_menunodes[$this->_selection[$i]][MENUTAG]);
        }
        $this->_smarty->assign('isThirdLevel', ((count($this->_selection) > 2) ? 'on' : 'off'));

        // Escribir el log de navegación para cada página visitada sin acción alguna
        if (isset($_GET) && count($_GET) == 1 && isset($_GET['menu'])) {
            $tagstack = array();
            foreach ($this->_selection as $key) $tagstack[] = $this->_menunodes[$key][MENUTAG];
            $user = isset($_SESSION['elastix_user']) ? $_SESSION['elastix_user'] : 'unknown';
            writeLOG('audit.log', sprintf('NAVIGATION %s: User %s visited "%s" from %s.',
                $user, $user, implode(' >> ', $tagstack), $_SERVER['REMOTE_ADDR']));
        }
    }

    function showContent()
    {
        $selectedModule = $this->getSelectedModule();
        $this->putHEAD_JQUERY_HTML();
        
        // Módulo seleccionado es un verdadero módulo con código
        if ($this->_menunodes[$selectedModule]['Type'] == 'module')
            return $this->includeModule($selectedModule);

        // TODO: mover iframe a plantilla
        // Módulo seleccionado es un iframe con un enlace dentro
        $this->_smarty->assign('title', $this->_menunodes[$selectedModule][MENUTAG]);
        $link = $this->_menunodes[$selectedModule]['Link'];
        $link = str_replace('{NAME_SERVER}', $_SERVER['SERVER_NAME'], $link);
        $link = str_replace('{IP_SERVER}', $_SERVER['SERVER_ADDR'], $link);
        return  "<iframe marginwidth=\"0\" marginheight=\"0\" class=\"frameModule\"".
                "\" src=\"$link\" name=\"myframe\" id=\"myframe\" frameborder=\"0\"".
                " width=\"100%\" onLoad=\"calcHeight();\"></iframe>";
    }

    private function includeModule($module)
    {
        global $arrConf;
        //comprobamos que exista el index del modulo
        if (!file_exists("{$arrConf['elxPath']}/apps/$module/index.php"))
            return array('data'=>"Error: The module <b>{$arrConf['elxPath']}/apps/$module/index.php</b> could not be found!<br/>");
        
        require_once "apps/$module/index.php";
        
        //si existe el archivo de configuracion del modulo se los incluye y se cargan las configuraciones
        //especificas del modulo elegido
        if (file_exists("{$arrConf['elxPath']}/apps/$module/configs/default.conf.php")) {
            include_once "apps/$module/configs/default.conf.php";
            global $arrConf;
            global $arrConfModule;
            if(is_array($arrConfModule))
                $arrConf = array_merge($arrConf, $arrConfModule);
        }
        
        
        //se incluyen las librerias que esten dentro de apps/$module/libs
        $dirLibs="{$arrConf['elxPath']}/apps/$module/libs";
        if(is_dir($dirLibs)){
            $arr_libs = $this->obtainFiles($dirLibs,"class.php");
            if($arr_libs!=false && count($arr_libs)>0){
                for($i=0; $i<count($arr_libs); $i++){
                    include_once "apps/$module/libs/".$arr_libs[$i];
                }
            }
        }
        
        // Cargar las traducciones para el módulo elegido
        load_language_module($module);
        
        // Cargar las creadenciales del usuario
        global $arrCredentials;
        $arrCredentials=getUserCredentials($_SESSION['elastix_user']);
        if($arrCredentials==false)
            return array('data'=>"Error to load User Credentials: {$_SESSION['elastix_user']}");
        
        //cargar los permisos del modulo
        global $arrPermission;
        $arrPermission=getResourceActionsByUser($arrCredentials['idUser'],$module);
        if($arrPermission==false)
            return array('data'=>"Error to load Module Permissions: $module");
        
        if (!function_exists("_moduleContent"))
            return array('data'=>"Wrong module: apps/$module/index.php");
            
        $CssJsModule=$this->putHEAD_MODULE_HTML($module);
        $moduleContent=_moduleContent($this->_smarty, $module);
        return array("data"=>$moduleContent,"JS_CSS_HEAD"=>$CssJsModule); 
    }

    /**
    *
    * Description:
    *   This function put the tags css and js per each module and the libs of the framework
    *
    * Example:
    *   $array = putHEAD_MODULE_HTML('calendar');
    *
    * Developer:
    *   Eduardo Cueva
    *
    * e-mail:
    *   ecueva@palosanto.com
    */
    private function putHEAD_MODULE_HTML($module)  // add by eduardo
    {
        global $arrConf;
        $HEADER_MODULES = array();
        // FIXED: The theme default shouldn't be static.
        $directoryScrips = "{$arrConf['basePath']}/web/apps/$module/js/";
        $directoryCss = "{$arrConf['basePath']}/web/apps/$module/css/";
        if(is_dir($directoryScrips)){
            $arr_js = $this->obtainFiles($directoryScrips,"js");
            if($arr_js!=false && count($arr_js)>0){
                for($i=0; $i<count($arr_js); $i++){
                    $dir_script = "web/apps/$module/js/".$arr_js[$i];
                    $HEADER_MODULES[] = "<script type='text/javascript' class='header-module-elastix' src='$dir_script'></script>";
                }
            }
        }
        if(is_dir($directoryCss)){
            $arr_css = $this->obtainFiles($directoryCss,"css");
            if($arr_css!=false && count($arr_css)>0){
                for($i=0; $i<count($arr_css); $i++){
                    $dir_css = "web/apps/$module/css/".$arr_css[$i];
                    $HEADER_MODULES[] = "<link rel='stylesheet' class='header-module-elastix' href='$dir_css' />";
                }
            }
        }
        return implode("\n", $HEADER_MODULES);
    }

    function putHEAD_JQUERY_HTML()
    {
        global $arrConf;
        $documentRoot = $arrConf['documentRoot'];
        // include file of framework
        $HEADER_LIBS_JQUERY = array();
        $JQqueryDirectory = "$documentRoot/web/_common/js/jquery";
        // it to load libs JQuery
        if(is_dir($JQqueryDirectory)){
            $directoryScrips = "$documentRoot/web/_common/js/jquery/";
            if(is_dir($directoryScrips)){
                $arr_js = $this->obtainFiles($directoryScrips,"js");
                if($arr_js!=false && count($arr_js)>0){
                    for($i=0; $i<count($arr_js); $i++){
                        $dir_script = "{$arrConf['webCommon']}/js/jquery/".$arr_js[$i];
                        $HEADER_LIBS_JQUERY[] = "<script type='text/javascript' src='$dir_script'></script>";
                    }
                }
            }

            // FIXED: The css ui-lightness shouldn't be static.
            $directoryCss = "$documentRoot/web/_common/js/jquery/css/ui-lightness/";
            if(is_dir($directoryCss)){
                $arr_css = $this->obtainFiles($directoryCss,"css");
                if($arr_css!=false && count($arr_css)>0){
                    for($i=0; $i<count($arr_css); $i++){
                        $dir_css = "{$arrConf['webCommon']}/js/jquery/css/ui-lightness/".$arr_css[$i];
                        $HEADER_LIBS_JQUERY[] = "<link rel='stylesheet' href='$dir_css' />";
                    }
                }
            }
            
            $jqueryUICss = "$documentRoot/web/_common/js/jquery/widgetcss/";
            if(is_dir($jqueryUICss)){
                $arr_css = $this->obtainFiles($jqueryUICss,"css");
                if($arr_css!=false && count($arr_css)>0){
                    for($i=0; $i<count($arr_css); $i++){
                        $dir_css = "{$arrConf['webCommon']}/js/jquery/widgetcss/".$arr_css[$i];
                        $HEADER_LIBS_JQUERY[] = "<link rel='stylesheet' href='$dir_css' />";
                    }
                }
            }
            //$HEADER_LIBS_JQUERY
        }
        $this->_smarty->assign("HEADER_LIBS_JQUERY", implode("\n", $HEADER_LIBS_JQUERY));
    }

    /**
    *
    * Description:
    *   This function Obtain all name files into of a directory where $type is the extension of the file
    *
    * Example:
    *   $array = obtainFiles('/var/www/html/web/apps/calendar/js/','js');
    *
    * Developer:
    *   Eduardo Cueva
    *
    * e-mail:
    *   ecueva@palosanto.com
    */
    private function obtainFiles($dir,$type){
        $files =  glob($dir."/{*.$type}",GLOB_BRACE);
        $names ="";
        foreach ($files as $ima)
            $names[]=array_pop(explode("/",$ima));
        if(!$names) return false;
        return $names;
    }
}
?>
