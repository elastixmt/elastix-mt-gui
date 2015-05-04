<?php
require_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    global $arrConf;
    
     //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);

    $txtCommand = isset($_POST['txtCommand'])? trim($_POST['txtCommand']) : '';
    $oForm = new paloForm($smarty, array());
    $smarty->assign(array(
        'asterisk'  =>  _tr('Asterisk CLI'),
        'command'   =>  _tr('Command'),
        'txtCommand'=>  htmlspecialchars($txtCommand),
        'execute'   =>  _tr('Execute'),
        'icon'      =>  "web/apps/$module_name/images/pbx_tools_asterisk_cli.png",
    ));

    $result = "";
    if (!empty($txtCommand)) {
    	$output = $retval = NULL;
        exec("/usr/sbin/asterisk -rnx ".escapeshellarg($txtCommand), $output, $retval);
        $result = '<pre>'.implode("\n", array_map('htmlspecialchars', $output)).'</pre>';
    }
    if ($result == "") $result = "&nbsp;";
    $smarty->assign("RESPUESTA_SHELL", $result);

    return $oForm->fetchForm("$local_templates_dir/new.tpl", _tr('Asterisk-Cli'), $_POST);
}
?>
