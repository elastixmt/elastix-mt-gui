<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.4-1                                               |
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
  $Id: index.php,v 1.1 2008-08-25 05:08:01 jvega jvega@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoDB.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //global variables
    global $arrConf;

    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    //conexion resource
    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);

    //actions
    $accion = getAction();
    //$content = "";

    switch($accion){
        case "save":
            $content = saveCurrency($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default:
            $content = formCurrency($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function formCurrency($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    global $arrPermission;
    $arrFormCurrency = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormCurrency);

    //CARGAR CURRENCY GUARDADO
    $curr = loadCurrentCurrency($pDB);

    if( $curr == false ) $curr = "$";
    $smarty->assign("SAVE", _tr("Save"));
   
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "web/apps/$module_name/images/system_preferences_currency.png");
    $_POST['currency'] = $curr;

    if((in_array('edit',$arrPermission)))
        $smarty->assign('EDIT_CURR',true);
    
    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Currency"), $_POST);
    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function saveCurrency($smarty, $module_name, $local_templates_dir, $pDB, $arrConf)
{   
    global $arrCredentials;
    $curr = getParameter("currency");
    $oPalo = new paloSantoCurrency($pDB);
    //print_r($curr);
    $bandera = $oPalo->SaveOrUpdateCurrency($arrCredentials['id_organization'],$curr);

    if($bandera == true ){
        $smarty->assign("mb_title", _tr("Message"));
        $smarty->assign("mb_message", _tr("Successfully saved"));
    }
    else{
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message", $oPalo->errMsg);
    }

    return formCurrency($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
}

function createFieldForm()
{
    $arrOptions = array('val1' => 'Value 1', 'val2' => 'Value 2', 'val3' => 'Value 3');

    $arrFields = array(
            "currency"   => array(  "LABEL"                  => _tr("Currency"),
                                    "DESCRIPTION"            => _tr("CU_currency"),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => getCurrencys(),
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => "",
                                    "EDITABLE"               => "si",
                                            ),
            );
    return $arrFields;
}

function getAction()
{
    global $arrPermission;
    if(getParameter("save"))
        return (in_array('edit',$arrPermission))?'save':'report';
    else
        return "report";
}

function loadCurrentCurrency($pDB)
{
    global $arrCredentials;
    $oPalo = new paloSantoCurrency($pDB);
    return $oPalo->loadCurrency($arrCredentials['id_organization']);
}

function getCurrencys()
{
    return array(
            "AR$"   => "AR$ - "._tr("Argentinian peso"),
            "฿"     => "฿ - "._tr("Baht tailandés / balboa panameño"),
            "Bs"    => "Bs - "._tr("Bolívar venezolano"),
            "Bs.F." => "Bs.F. - "._tr("Bolívar fuerte venezolana"),
            "¢"     => "¢ - "._tr("Colón costarricense"),
            "C$"    => "C$ - "._tr("Córdoba nicaragüense/dólar canadiense"),
            "₫"     => "₫ - "._tr("Dong vietnamita"),
            "EC$"   => "EC$ - "._tr("Dólar del Caribe Oriental"),
            "Kr"    => "Kr - "._tr("Corona danesa, corona sueca"),
            "£"     => "£ - "._tr("Lira"),
            "L$"    => "L$ - "._tr("Lempira hondureño"),
            "Q"     => "Q - "._tr("Quetzal guatemalteco"),
            "€"     => "€ - "._tr("Euro"),
            "£GBP"  => "£GBP - "._tr("GB Sterling"),
            "R"     => "R - "._tr("Rand sudafricano"),
            "Rp"    => "Rp - "._tr("Rupia indonesia"),
            "Rs"    => "Rs - "._tr("Rupia"),
            "R$"    => "R$ - "._tr("Real brasileño"),
            "руб"   => "руб - "._tr("Rublo ruso"),
            "A$"    => "A$ - "._tr("Dólar australiano"),
            "$"     => "$ - "._tr("Dólar/Peso"),
            "¥"     => "¥ - "._tr("Yen"),
            "₪"     => "₪ - "._tr("Sheqel israelí"),
            "¢"     => "¢ - "._tr("Colón salvadoreño"),
            "元"    => "元 - "._tr("Yuan chino"),
            "৳"     => "৳ - "._tr("Rupia bengalí"),
            "S$"    => "S$ - "._tr("Dólar de Singapur"),
	    "CHF"   => "CHF - "._tr("Swiss Franc"),
    );
}
?>
