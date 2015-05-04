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
/* A continuacion se ilustra como luce un tipico elemento del arreglo $this->arrFormElements
"subject"  => array(
                "LABEL"                  => $arrLang["Fax Suject"],
                "DESCRIPTION"            => _tr("Subject sent in the mail"),
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "TEXT",
                "INPUT_EXTRA_PARAM"      => array("style" => "width:240px"),
                "VALIDATION_TYPE"        => "text",
                "EDITABLE"               => "yes",
                "VALIDATION_EXTRA_PARAM" => "")

"content" => array(
                "LABEL"                  => $arrLang["Fax Content"],
                "DESCRIPTION"            => _tr("Subject sent in the mail"),
                "REQUIRED"               => "no",
                "INPUT_TYPE"             => "TEXTAREA",
                "INPUT_EXTRA_PARAM"      => "",
                "VALIDATION_TYPE"        => "text",
                "EDITABLE"               => "yes",
                "COLS"                   => "50",
                "ROWS"                   => "4",
                "VALIDATION_EXTRA_PARAM" => "")

"today"  => array(
                "LABEL"                  => "Today",
                "DESCRIPTION"            => "",
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "DATE",
                "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "'%d %b %Y' %H:%M","TIMEFORMAT" => "12", "FIRSTDAY" => 1),
                "VALIDATION_TYPE"        => '',
                "EDITABLE"               => "yes",
                "VALIDATION_EXTRA_PARAM" => '')

'formulario'       => array(
                "LABEL"                  => $arrLang["Form"],
                "DESCRIPTION"            => "",
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "SELECT",
                "INPUT_EXTRA_PARAM"      => $arrSelectForm,
                "VALIDATION_TYPE"        => "text",
                "VALIDATION_EXTRA_PARAM" => "",
                "EDITABLE"               => "yes",
                "MULTIPLE"               => true,
                "SIZE"                   => "5")


"checkbox"  => array(
                "LABEL"                  => "Habiltar",
                "DESCRIPTION"            => "",
                "REQUIRED"               => "no",
                "INPUT_TYPE"             => "CHECKBOX",
                "INPUT_EXTRA_PARAM"      => "",
                "VALIDATION_TYPE"        => "",
                "EDITABLE"               => "yes",
                "VALIDATION_EXTRA_PARAM" => "")
*/
global $arrConf;
require_once("{$arrConf['elxPath']}/libs/misc.lib.php");

class paloForm
{
    var $smarty;
    var $arrFormElements;
    var $arrErroresValidacion;
    var $modo;

    function paloForm(&$smarty, $arrFormElements)
    {
        $this->smarty = &$smarty;
        $this->arrFormElements = $arrFormElements;
        $this->arrErroresValidacion = "";
        $this->modo = 'input'; // Modo puede ser 0 (Modo normal de formulario) o 1 (modo de vista o preview 
                               // de datos donde no se puede modificar.
    }

    /**
     * Esta función genera una cadena que contiene un formulario HTML. Para hacer
     * esto, toma una plantilla de formulario e inserta en ella los elementos de
     * formulario.
     *
     * @param   string  $templateName   Ruta al archivo de plantilla Smarty a usar.
     * @param   string  $title          Texto a usar como título del formulario
     * @param   array   $arrPreFilledValues Arreglo para asignar variables a mostrar
     *                                  en el formulario. Este arreglo es idéntico
     *                                  en formato al arreglo $_POST que se genera
     *                                  al enviar el formulario lleno, de forma que
     *                                  se puede usar $_POST directamente para 
     *                                  llenar con valores en caso de que la 
     *                                  validación falle.
     *
     * @return  string  Texto HTML del formulario con valores asignados
     */
    function fetchForm($templateName, $title, $arrPreFilledValues = array())
    {
        global $arrConf;
        /* Función interna para convertir un arreglo especificado en 
           INPUT_EXTRA_PARAM en una cadena de atributos clave=valor adecuada 
           para incluir al final de un widget HTML. Si no existe 
           INPUT_EXTRA_PARAM, o no es un arreglo, se devuelve una cadena vacía
         */
        if (!function_exists('_inputExtraParam_a_atributos')) {
            function _inputExtraParam_a_atributos(&$arrVars)
            {
                if (!isset($arrVars['INPUT_EXTRA_PARAM']) || 
                    !is_array($arrVars['INPUT_EXTRA_PARAM']) || 
                    count($arrVars['INPUT_EXTRA_PARAM']) <= 0)
                    return '';
                $listaAttr = array();
                foreach($arrVars['INPUT_EXTRA_PARAM'] as $key => $value) {
                    $listaAttr[] = sprintf(
                        '%s="%s"', 
                        htmlentities($key, ENT_COMPAT, 'UTF-8'),
                        htmlentities($value, ENT_COMPAT, 'UTF-8'));
                }
                
                return implode(' ', $listaAttr);
            }
        }
        
        if (!function_exists('_inputExtraParam_options')) {
            function _inputExtraParam_options(&$arrVars)
            {
                if (!isset($arrVars['INPUT_EXTRA_PARAM_OPTIONS']) || 
                    !is_array($arrVars['INPUT_EXTRA_PARAM_OPTIONS']) || 
                    count($arrVars['INPUT_EXTRA_PARAM_OPTIONS']) <= 0)
                    return '';
                $listaAttr = array();
                foreach($arrVars['INPUT_EXTRA_PARAM_OPTIONS'] as $key => $value) {
                    $listaAttr[] = sprintf(
                        '%s="%s"', 
                        htmlentities($key, ENT_COMPAT, 'UTF-8'),
                        htmlentities($value, ENT_COMPAT, 'UTF-8'));
                }
                
                return implode(' ', $listaAttr);
            }
        }

        // Función para usar con array_map
        if (!function_exists('_map_htmlentities')) {
            function _map_htmlentities($s)
            {
                return htmlentities($s, ENT_COMPAT, 'UTF-8');
            }
        }
        
        if (!function_exists('_labelName')) {
            function _labelName($varName,&$arrVars)
            {
                $tooltip="";
                if(isset($arrVars['DESCRIPTION'])){
                    if($arrVars['DESCRIPTION']!=false && $arrVars['DESCRIPTION']!=""){
                        $tooltip='data-tooltip="'.htmlentities($arrVars['DESCRIPTION'], ENT_COMPAT, 'UTF-8').'"';
                    }
                }
                if($tooltip!=""){
                    return sprintf('<label for="%s" %s>%s</label>',
                            htmlentities($varName, ENT_COMPAT, 'UTF-8'),
                            $tooltip,
                            htmlentities($arrVars['LABEL'], ENT_COMPAT, 'UTF-8')
                        );
                }else{
                    return htmlentities($arrVars['LABEL'], ENT_COMPAT, 'UTF-8');
                }
            }
        }
        
        foreach($this->arrFormElements as $varName=>$arrVars) {
            if(!isset($arrPreFilledValues[$varName]))
                $arrPreFilledValues[$varName] = "";
            $arrMacro = array();
            $strInput = "";
            $arrVars['EDITABLE'] = isset($arrVars['EDITABLE'])?$arrVars['EDITABLE']:'';

            // Verificar si se debe mostrar un widget activo para ingreso de valor
            $bIngresoActivo = ($this->modo == 'input' || ($this->modo == 'edit' && $arrVars['EDITABLE']!='no'));

            /* El indicar ENT_COMPAT escapa las comillas dobles y deja intactas
               las comillas simples. Por lo tanto, se asume que todos los usos
               de $varXXX_escaped serán dentro de comillas dobles, o en texto 
               libre. */
            $varName_escaped = htmlentities($varName, ENT_COMPAT, 'UTF-8');
            $varValue_escaped = is_array($arrPreFilledValues[$varName]) 
                ? NULL : htmlentities($arrPreFilledValues[$varName], ENT_COMPAT, 'UTF-8');

            switch($arrVars['INPUT_TYPE']) {
                case "TEXTAREA":
                    $strInput = $bIngresoActivo 
                        ? sprintf(
                            '<textarea name="%s" rows="%s" cols="%s" %s>%s</textarea>',
                            $varName_escaped,
                            isset($arrVars['ROWS']) ? (int)$arrVars['ROWS'] : 3,
                            isset($arrVars['COLS']) ? (int)$arrVars['COLS'] : 20,
                            _inputExtraParam_a_atributos($arrVars),
                            $varValue_escaped)
                        : $varValue_escaped;
                    break;
                case "TEXT":
                    $strInput = $bIngresoActivo
                        ? sprintf(
                            '<input type="text" name="%s" value="%s" %s />', 
                            $varName_escaped,
                            $varValue_escaped,
                            _inputExtraParam_a_atributos($arrVars))
                        : $varValue_escaped;                    
                    break;
                case "CHECKBOX":
                    $checked = 'off';
                    $disable = 'on';
                    if($arrPreFilledValues[$varName]=='on')
                        $checked = 'on';
                    if($bIngresoActivo)
                        $disable = 'off';

                    //Funcion definida en misc.lib.php
                    $strInput = checkbox($varName,$checked, $disable);
                    break;
                case "PASSWORD":
                    $strInput = $bIngresoActivo
                        ? sprintf(
                            '<input type="password" name="%s" value="%s" %s />', 
                            $varName_escaped,
                            $varValue_escaped,
                            _inputExtraParam_a_atributos($arrVars))
                        : $varValue_escaped;                    
                    break;
                case "HIDDEN":
                    $strInput = sprintf(
                        '<input type="hidden" name="%s" value="%s" %s />', 
                        $varName_escaped,
                        $varValue_escaped,
                        _inputExtraParam_a_atributos($arrVars));                    
                    break;
                case "FILE":
                    $strInput = $bIngresoActivo
                        ? sprintf(
                            '<input type="file" name="%s" %s />', 
                            $varName_escaped,
                            _inputExtraParam_a_atributos($arrVars))
                        : $varValue_escaped;                    
                    break;
                case "RADIO":
                    if($bIngresoActivo) {
                        $strInput = "";
                        if(is_array($arrVars['INPUT_EXTRA_PARAM'])) {
                            $listaRadio = array();
                            foreach($arrVars['INPUT_EXTRA_PARAM'] as $radioValue => $radioLabel) {
                                $listaRadio[] = sprintf(
                                    '<input type="radio" name="%s" value="%s" %s %s/>&nbsp;%s&nbsp;',
                                    $varName_escaped,
                                    htmlentities($radioValue, ENT_COMPAT, 'UTF-8'),
                                    ($radioValue == $arrPreFilledValues[$varName]) ? 'checked="checked"' : '',
                                    _inputExtraParam_options($arrVars),
                                    htmlentities($radioLabel, ENT_COMPAT, 'UTF-8'));
                            }
                            $strInput = implode("\n", $listaRadio);                            
                        }
                    } else {
                        $strInput = $varValue_escaped;
                    }
                    break;
                case "OPTION":
                    if($bIngresoActivo) {
                        $strInput = "";
                        if(is_array($arrVars['INPUT_EXTRA_PARAM'])) {
                            $listaRadio = array();
                            foreach($arrVars['INPUT_EXTRA_PARAM'] as $radioItem) {
                                $listaRadio[] = sprintf(
                                    '<input type="radio" id="%s" name="%s" value="%s" %s %s/><label for="%s">%s</label>',
                                    htmlentities($radioItem['id'], ENT_COMPAT, 'UTF-8'),
                                    $varName_escaped,
                                    htmlentities($radioItem['value'], ENT_COMPAT, 'UTF-8'),
                                    ($radioItem['value'] == $arrPreFilledValues[$varName]) ? 'checked="checked"' : '',
                                    _inputExtraParam_options($arrVars),
                                    htmlentities($radioItem['id'], ENT_COMPAT, 'UTF-8'),
                                    htmlentities($radioItem['label'], ENT_COMPAT, 'UTF-8'));
                            }
                            $strInput = implode("\n", $listaRadio);                            
                        }
                    } else {
                        $strInput = $varValue_escaped;
                    }
                    break;
                case "SELECT":
                    if ($bIngresoActivo) {
                        $listaOpts = array();
                        $keyVals = is_array($arrPreFilledValues[$varName])
                            ? $arrPreFilledValues[$varName] 
                            : array($arrPreFilledValues[$varName]);
                        if (is_array($arrVars['INPUT_EXTRA_PARAM'])) {
                            foreach($arrVars['INPUT_EXTRA_PARAM'] as $idSeleccion => $nombreSeleccion) {
                                $listaOpts[] = sprintf(
                                    '<option value="%s" %s>%s</option>',
                                    htmlentities($idSeleccion, ENT_COMPAT, 'UTF-8'),
                                    in_array((string)$idSeleccion, $keyVals) ? 'selected="selected"' : '',
                                    htmlentities($nombreSeleccion, ENT_COMPAT, 'UTF-8'));
                            }
                        }
                        $sNombreSelect = $varName_escaped;
                        $sAttrMultiple = '';
                        if (isset($arrVars['MULTIPLE']) && $arrVars['MULTIPLE'] != '' && $arrVars['MULTIPLE']) {
                            $sAttrMultiple = 'multiple="multiple"';
                            $sNombreSelect .= '[]';
                        }
                        $strInput = sprintf(
                            '<select name="%s" id="%s" %s %s %s %s>%s</select>',
                            $sNombreSelect,
                            $sNombreSelect,
                            $sAttrMultiple,
                            (isset($arrVars['SIZE']) && $arrVars['SIZE'] != '') 
                                ? sprintf('size="%s"', htmlentities($arrVars['SIZE'], ENT_COMPAT, 'UTF-8')) 
                                : '',
                            (isset($arrVars['ONCHANGE']) && $arrVars['ONCHANGE'] != '') 
                                ? "onchange='{$arrVars['ONCHANGE']}'" 
                                : '',
                            _inputExtraParam_options($arrVars),
                            implode("\n", $listaOpts));
                    } else {
                        $strInput = is_array($arrPreFilledValues[$varName])
                            ? '| '.implode(' | ', 
                                array_map('_map_htmlentities', 
                                    array_intersect_key(
                                        $arrVars['INPUT_EXTRA_PARAM'],
                                        array_flip($arrPreFilledValues[$varName])
                                    )))
                            : (isset($arrVars['INPUT_EXTRA_PARAM'][$arrPreFilledValues[$varName]])
                                ? htmlentities($arrVars['INPUT_EXTRA_PARAM'][$arrPreFilledValues[$varName]], ENT_COMPAT, 'UTF-8')
                                : '');
                    }
                    break;
                case "DATE":
                    if($bIngresoActivo) {
                        require_once("{$arrConf['webCommon']}/js/jscalendar/calendar.php");    
                        $time = false;
                        $format = '%d %b %Y';
                        $timeformat = '12';
                        $firstDay = 1;
                        if(is_array($arrVars['INPUT_EXTRA_PARAM']) && count($arrVars['INPUT_EXTRA_PARAM'])>0) {
                            foreach($arrVars['INPUT_EXTRA_PARAM'] as $key => $value){
                                if($key=='TIME')
                                    $time=$value;
                                if($key=='FORMAT')
                                    $format = $value;
                                if($key=='TIMEFORMAT')
                                    $timeformat = $value;
                                if($key=='FIRSTDAY')
                                    $firstDay = $value;
                            }
                        }
                        $oCal = new DHTML_Calendar("{$arrConf['webCommon']}/js/jscalendar/", "en", "calendar-win2k-2", $time);
                        $this->smarty->assign("HEADER", $oCal->load_files());

                        $strInput .= $oCal->make_input_field(
                                        array('firstDay'       => $firstDay, // show Monday=1 first by default or Sunday=7
                                              'showsTime'      => true,
                                              'showOthers'     => true,
                                              'ifFormat'       => $format,
                                              'timeFormat'     => $timeformat),
                                        // field attributes go here
                                        array('style'          => 'width: 10em; color: #840; background-color: #fafafa; ' .
                                                                   'border: 1px solid #999999; text-align: center',
                                              'name'        => $varName,
                                              //'value'       => strftime('%d %b %Y', strtotime('now'))));
                                              'value'       => $arrPreFilledValues[$varName]));

                    } else {
                        $strInput = $varValue_escaped;
                    }
                    break;
                default:
                    $strInput = "";
            }
            $arrMacro['LABEL'] = _labelName($varName,&$arrVars);
            $arrMacro['INPUT'] = $strInput;
            $this->smarty->assign($varName, $arrMacro);
        }
        $this->smarty->assign("title", htmlentities($title, ENT_COMPAT, 'UTF-8'));
        $this->smarty->assign("mode", $this->modo);
        return $this->smarty->fetch("file:$templateName");
    }
    
    function setViewMode()
    {
        $this->modo = 'view';
    }

    function setEditMode()
    {
        $this->modo = 'edit';
    }

    // TODO: No se que hacer en caso de que el $arrCollectedVars sea un arreglo vacio
    //       puesto que en ese caso la funcion devolvera true. Es ese el comportamiento esperado?
    function validateForm($arrCollectedVars)
    {
        global $arrConf;
        $arrCollectedVars = array_merge($arrCollectedVars,$_FILES);
        include_once("{$arrConf['elxPath']}/libs/paloSantoValidar.class.php");
        $oVal = new PaloValidar();
        foreach($arrCollectedVars as $varName=>$varValue) {
            // Valido si la variable colectada esta en $this->arrFormElements
            if(@array_key_exists($varName, $this->arrFormElements)) {
                if($this->arrFormElements[$varName]['INPUT_TYPE']=='FILE')
                    $varValue = $_FILES[$varName]['name'];

                if($this->arrFormElements[$varName]['REQUIRED']=='yes' or ($this->arrFormElements[$varName]['REQUIRED']!='yes' AND !empty($varValue))) {
                    $editable = isset($this->arrFormElements[$varName]['EDITABLE'])?$this->arrFormElements[$varName]['EDITABLE']:"yes";
                    if($this->modo=='input' || ($this->modo=='edit' AND $editable != 'no')) {
                        $oVal->validar($this->arrFormElements[$varName]['LABEL'], $varValue, $this->arrFormElements[$varName]['VALIDATION_TYPE'], 
                                       $this->arrFormElements[$varName]['VALIDATION_EXTRA_PARAM']);
                    }
                }
            }
        }
        if($oVal->existenErroresPrevios()) {
            $this->arrErroresValidacion = $oVal->obtenerArregloErrores();
            return false;
        } else {
            return true;
        }
    }
}
?>
