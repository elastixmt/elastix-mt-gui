<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.2-3                                               |
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
  $Id: default.conf.php,v 1.1 2008-09-01 10:09:57 jjvega Exp $ */
require_once("libs/jpgraph/jpgraph.php");
require_once("libs/jpgraph/jpgraph_line.php");
require_once("libs/jpgraph/jpgraph_pie.php");
require_once("libs/jpgraph/jpgraph_pie3d.php");
require_once("libs/jpgraph/jpgraph_bar.php");
require_once("libs/jpgraph/jpgraph_canvas.php");
require_once("libs/jpgraph/jpgraph_canvtools.php");
require_once "libs/paloSantoDB.class.php";
require_once "libs/paloSantoSampler.class.php";
require_once "libs/paloSantoTrunk.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include elastix framework
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/paloSantoConfig.class.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoExtention.class.php";
    
    load_language_module($module_name);

    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    //solo para obtener los devices (extensiones) creadas.
    $dsnAsteriskCdr = $arrConfig['AMPDBENGINE']['valor']."://".
                      $arrConfig['AMPDBUSER']['valor']. ":".
                      $arrConfig['AMPDBPASS']['valor']. "@".
                      $arrConfig['AMPDBHOST']['valor']."/asteriskcdrdb";

    $pDB_cdr = new paloDB($dsnAsteriskCdr);//asteriskcdrdb -> CDR

    $dsnAsteriskDev = $arrConfig['AMPDBENGINE']['valor']."://".
                      $arrConfig['AMPDBUSER']['valor']. ":".
                      $arrConfig['AMPDBPASS']['valor']. "@".
                      $arrConfig['AMPDBHOST']['valor']."/asterisk";

    $pDB_ext = new paloDB($dsnAsteriskDev);//asterisk -> devices
    $accion = getAction();

    $content = "";
    switch($accion)
    {
        case "show":
            $_POST['nav'] = null; $_POST['start'] = null;
            $content = report_Extention($smarty, $module_name, $local_templates_dir, $pDB_cdr, $pDB_ext);
            break;
        case 'grafic':
            $du = $_GET['du'];
            $totIn = $_GET['in'];
            $totOut = $_GET['out'];
            $tot = $_GET['tot'];
            $ext = $_GET['ext'];

	    if(preg_match("/^[1-9]{1}[[:digit:]]*$/",$ext))
		grafic($du, $totIn, $totOut, $tot, $ext);
	    else
		$content = report_Extention($smarty, $module_name, $local_templates_dir, $pDB_cdr, $pDB_ext);
            break;
        case 'grafic_queue':
            $queue = "";//isset($_GET['queue'])?$_GET['queue']:"";//queue
            $dti   = isset($_GET['dti'])?$_GET['dti']:"";//fecha inicio
            $dtf   = isset($_GET['dtf'])?$_GET['dtf']:"";//fecha fin

            grafic_queue($pDB_cdr, $pDB_ext, $queue, $dti, $dtf);
            break;
        case 'grafic_trunk':
            $trunk = isset($_GET['trunk'])?$_GET['trunk']:"";
            $dti  = isset($_GET['dti'])?$_GET['dti']:"";
            $dtf  = isset($_GET['dtf'])?$_GET['dtf']:"";

            grafic_trunk($pDB_cdr, $pDB_ext, $module_name, $trunk, $dti, $dtf);
            break;
        case 'grafic_trunk2':
            $trunk = isset($_GET['trunk'])?$_GET['trunk']:"";
            $dti  = isset($_GET['dti'])?$_GET['dti']:"";
            $dtf  = isset($_GET['dtf'])?$_GET['dtf']:"";

            grafic_trunk2($pDB_cdr, $pDB_ext, $module_name, $trunk, $dti, $dtf);
            break;
        default:
            $content = report_Extention($smarty, $module_name, $local_templates_dir, $pDB_cdr, $pDB_ext);
            break;
    }

    return $content;
}

function report_Extention($smarty, $module_name, $local_templates_dir, $pDB_cdr, $pDB_ext)
{
    $arrFormElements = array(
        "date_from"         => array(
            "LABEL"                  => _tr("Start date"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "DATE",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
        "date_to"           => array(
            "LABEL"                  => _tr("End date"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "DATE",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
        "extensions"        => array(
            "LABEL"                  => _tr("Number"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => loadExtentions($pDB_ext),
            "VALIDATION_TYPE"        => "text",
            "EDITABLE"               => "yes",
            "VALIDATION_EXTRA_PARAM" => ""),
        "classify_by"       => array(
            "LABEL"                  => "",
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => array("Number"=>_tr('Extension (Number)'),"Queue"=>_tr('Queue'),"Trunk"=>_tr('Trunk')),
            "VALIDATION_TYPE"        => "text",
            "EDITABLE"               => "yes",
            "VALIDATION_EXTRA_PARAM" => "",
            'ONCHANGE'               => 'show_elements();'),
        "call_to"           => array(
            "LABEL"                  => _tr("Number"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => array("id" => 'call_to'),
            "VALIDATION_TYPE"        => "text",
            "EDITABLE"               => "yes",
            "VALIDATION_EXTRA_PARAM" => ""),
        "trunks"            => array(
            "LABEL"                  => "Trunk",
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => loadTrunks($pDB_ext),
            "VALIDATION_TYPE"        => "text",
            "EDITABLE"               => "yes",
            "VALIDATION_EXTRA_PARAM" => ""),
    );

    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $smarty->assign("SHOW", _tr("Show"));
    $smarty->assign("HERE", _tr("Here"));

    $date_ini = getParameter("date_from");
    $date_fin = getParameter("date_to");
    $ext = getParameter("call_to");

    $date_ini2 = translateDate($date_ini);
    $date_fin2 = translateDate($date_fin);
    $ext2 = $ext;

    $option = "";
    if( isset($_POST["classify_by"]) ){
        $option = $_POST["classify_by"];
        $smarty->assign("classify_by",$option);
    }

    if( getAction() == "show" ){
        $smarty->assign("date_from", $date_ini);
        $smarty->assign("date_1", $date_ini);

        $smarty->assign("date_to", $date_fin);
        $smarty->assign("date_2", $date_fin);

        $date_ini2 = translateDate($date_ini);
        $date_fin2 = translateDate($date_fin);
    }
    else{
        $_POST["date_from"] = date("d M Y");
        $_POST["date_to"] = date("d M Y");
        $date_ini = date("d M Y");
        $date_fin = date("d M Y");
        $date_ini2 = translateDate($date_ini);
        $date_fin2 = translateDate($date_fin);
    }

    $_POST["extensions"] = $ext;

    $smarty->assign("value_2", $date_ini);
    $smarty->assign("module_name", $module_name);

    $pExtention = new paloSantoExtention($pDB_cdr);

    $ruta_img = array();
    $error = false;
    if ($option == "Number") {
    	if (!preg_match("/^[1-9]{1}[[:digit:]]*$/",$ext) && isset($ext)) {
    	    $error = true;
    	    $smarty->assign("mb_title",_tr("Validation Error"));
    	    $smarty->assign("mb_message",_tr("The extension must be numeric and can not start with zero"));
    	}
        $smarty->assign("SELECTED_1","selected");
        $smarty->assign("SELECTED_2","");
        $smarty->assign("SELECTED_3","");

        $numIn = 0; $numOut = 0; $numTot = 0;
        $result = $pExtention->countCallsByExtension($date_ini2, $date_fin2, $ext);
        if (is_array($result)) {
        	$numIn = $result['num_incoming_call'];
            $numOut = $result['num_outgoing_call'];
            $numTot = $numIn + $numOut;
        }

        if($numIn != 0) $VALUE = (int)( 100*( $numIn/$numTot ) );
        else $VALUE = 0;

        $ruta_img = array("?menu={$module_name}&amp;action=grafic&amp;du={$VALUE}%&amp;in={$numIn}&amp;out={$numOut}&amp;ext={$ext2}&amp;tot={$numTot}&amp;rawmode=yes");
    } else if($option == "Queue") {
        $smarty->assign("SELECTED_1","");
        $smarty->assign("SELECTED_2","selected");
        $smarty->assign("SELECTED_3","");

        $ruta_img = array("?menu={$module_name}&amp;action=grafic_queue&amp;queue={$ext2}&amp;dti={$date_ini2}&amp;dtf={$date_fin2}&amp;rawmode=yes");
    } else if($option == "Trunk") {
        $smarty->assign("SELECTED_1","");
        $smarty->assign("SELECTED_2","");
        $smarty->assign("SELECTED_3","selected");

        $trunkT = getParameter("trunks");
        $smarty->assign("trunks", $trunkT);
        $ruta_img  = array(
            "?menu={$module_name}&amp;action=grafic_trunk&amp;trunk={$trunkT}&amp;dti={$date_ini2}&amp;dtf={$date_fin2}&amp;rawmode=yes",
            "?menu={$module_name}&amp;action=grafic_trunk2&amp;trunk={$trunkT}&amp;dti={$date_ini2}&amp;dtf={$date_fin2}&amp;rawmode=yes");

    }
    for ($i = 0; $i < count($ruta_img); $i++) 
	   $ruta_img[$i] = "<img src='".$ruta_img[$i]."' border='0'>";
    if(count($ruta_img)>0 && !$error)
	   $smarty->assign("ruta_img",  "<tr class='letra12'><td align='center'>".implode('&nbsp;&nbsp;', $ruta_img).'<td></tr>');
    else
	   $smarty->assign("ruta_img",  "<tr class='letra12'><td align='center'><td></tr>");

    $smarty->assign("icon","modules/$module_name/images/reports_graphic_reports.png");
    $htmlForm = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", _tr("Graphic Report"), $_POST);

    $contenidoModulo = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function loadExtentions($pDB_ext)
{
    $pExtention = new paloSantoExtention($pDB_ext);
    $arrayExt = $pExtention->loadExtentions();

    $arrayR = array();
    foreach($arrayExt as $key => $value){
        $arrayR[$value['id']] = $value['id'];
    }

    return $arrayR; 
}

function loadTrunks($pDB_ext)
{
    include_once "libs/paloSantoTrunk.class.php";

    $arrTrunksTemp = getTrunks($pDB_ext);
    $arrTrunk = array();
    foreach($arrTrunksTemp as $key => $arr){
        $arrTrunk[ $arr[1] ] = $arr[1]; 
    }

    return $arrTrunk;
}

function getAction()
{
    foreach (array('show', 'new') as $key)
        if (getParameter($key)) return $key;
    $action = getParameter('action');
    if (in_array($action, array('show', 'grafic', 'grafic_queue', 'grafic_trunk', 'grafic_trunk2')))
        return $action;
    return 'report';
}

// Generación de todos los gráficos a partir de este punto
function grafic($du, $totIn, $totOut, $tot, $ext)
{
	if(ereg("^([[:digit:]]{1,3})%", trim($du), $arrReg)) {
		$usoDisco = $arrReg[1];
	} else {
		$usoDisco = $du;
	}

	if( $tot != 0 )
	{
		$freeDisco = 100 - $usoDisco;
		
		// Some data
		$data = array($usoDisco, $freeDisco);
		
		// Create the Pie Graph.
		$graph = new PieGraph(630, 170,"auto");
		//$graph->SetShadow();
		$graph->SetMarginColor('#fafafa');
		$graph->SetFrame(true,'#999999');
		
		$graph->legend->SetFillColor("#fafafa");
		//$graph->legend->Pos(0.012, 0.5, "right","center");
		$graph->legend->SetColor("#444444", "#999999");
		$graph->legend->SetShadow('gray@0.6',4);
		
		//$graph->title->SetColor("#444444");
		
		// Set A title for the plot
		$graph->title->Set(utf8_decode(_tr("Number of calls extension")." $ext"));
		//$graph->title->SetFont(FF_VERDANA,FS_BOLD,18);
		$graph->title->SetColor("#444444");
		$graph->legend->Pos(0.1,0.2);
		
		// Create 3D pie plot
		$p1 = new PiePlot3d($data);
		//$p1->SetTheme("water");
		$p1->SetSliceColors( array("#3333cc", "#9999cc", "#CC3333", "#72394a", "#aa3424") ); 
		$p1->SetCenter(0.4);
		$p1->SetSize(80);
		
		// Adjust projection angle
		$p1->SetAngle(45);
		
		// Adjsut angle for first slice
		$p1->SetStartAngle(45);
		
		// Display the slice values
		//$p1->value->SetFont(FF_ARIAL,FS_BOLD,11);
		//$p1->value->SetColor("navy");
		$p1->value->SetColor("black");
		
		// Add colored edges to the 3D pies
		// NOTE: You can't have exploded slices with edges!
		$p1->SetEdge("black");
		
		$p1->SetLegends(array(utf8_decode(_tr("Incoming Calls")." ").$totIn,
		                      utf8_decode(_tr("Outcoming Calls")." ").$totOut ));
		
		$graph->Add($p1);
		$graph->Stroke();
	}
	else
	{
		$graph = new CanvasGraph(500,140,"auto");
		$title = new Text(utf8_decode(_tr("The extension")." $ext "._tr("does not have calls yet")));
		$title->ParagraphAlign('center');
		$title->SetFont(FF_FONT2,FS_BOLD);
		$title->SetMargin(3);
		$title->SetAlign('center');
		$title->Center(0,500,70);
		$graph->AddText($title);

		$t1 = new Text(utf8_decode(_tr("No exist calls for this number")));
		$t1->SetBox("white","black",true);
		$t1->ParagraphAlign("center");
		$t1->SetColor("black");

		$graph->AddText($t1);
		$graph->img->SetColor('navy');
		$graph->img->SetTextAlign('center','bottom');
		$graph->img->Rectangle(0,0,499,139);
		$graph->Stroke();
	}
}

function grafic_queue(&$pDB_ast_cdr, &$pDB_ast, $queue, $dti, $dtf)
{
    global $arrConf;

    $ancho = "700";
    $margenDerecho = "100";

    //============================================================================

    $objPalo_AST_CDR = new paloSantoExtention($pDB_ast_cdr);

    /*
    *   VALORES POR GET
    */

    $arrData=array();
    $numResults=0;
    $arrValue=array();
    $arrTimestamp=array();

    //============================================================================

    include_once "libs/paloSantoQueue.class.php";
    $paloQueue = new paloQueue($pDB_ast);
    $arrResult = ( strlen($queue) != 0 )?$paloQueue->getQueue($queue):$paloQueue->getQueue();

    //$arrResult
    //Array ( [0] => Array ( [0] => 2000 [1] => 2000 Recepcion )
    //        [1] => Array ( [0] => 5000 [1] => 5000 Soporte )
    //        [2] => Array ( [0] => 7000 [1] => 7000 Ventas )  )

    /*
    *   SE CREA UN 2 ARREGLOS DE TAMAÑO 3*X+1
    *   $arrData PARA LOS DATOS DEL EJE Y
    *   $arrayX PARA EL ARREGLO DE DATOS PARA EL EJE X
    */

    $arrayX = array();
    $num = sizeof($arrResult) ;
    $i = 0;
    for($i = 1; $i <= $num ; $i++){

        $s = $arrResult[$i-1];
        $s_0 = array( 0 => "", 1 => "" );

        if( $i == 1 ){ $arrData[0] = $s_0; $arrayX[0] = ""; }

        $arrData[3*($i-1)+1] = $s;    $arrayX[3*($i-1)+1] = "";
        $arrData[3*($i-1)+2] = $s;    $arrayX[3*($i-1)+2] = $s[0];
        $arrData[3*($i-1)+3] = $s_0;  $arrayX[3*($i-1)+3] = "";

        if($i == $num){ $arrData[3*($i-1)+4] = $s_0; $arrayX[3*($i-1)+4] = ""; }
    }

    //======================================================

    $graph = new Graph($ancho,250);
    $graph->SetMargin(50,$margenDerecho,30,40);
    $graph->SetMarginColor('#fafafa');
    $graph->SetFrame(true,'#999999');

    $graph->legend->SetFillColor("#fafafa");
    $graph->legend->Pos(0.012, 0.5, "right","center");
    $graph->legend->SetColor("#444444", "#999999");
    $graph->legend->SetShadow('gray@0.6',4);
    $graph->title->SetColor("#444444");

    // Especifico la escala
    $graph->SetScale("intlin");
    $graph->title->Set(utf8_decode(_tr("Number Calls vs Queues")));
    $graph->xaxis->SetLabelFormatCallback('NameQueue');
    $graph->xaxis->SetLabelAngle(90);
    $graph->xaxis->SetColor("#666666","#444444");

    if(is_array($arrData) && count($arrData) > 0 ){
        foreach($arrData as $k => $arrMuestra){
            $arrTimestamp[$k] = $k; /* X */
            
            //$arr = $objPalo_AST_CDR->countQueue( $arrMuestra['id'], $dti, $dtf);
            $arr = $objPalo_AST_CDR->countQueue( $arrMuestra[0], $dti, $dtf);
            $arrValue[$k] = $arr[0]; /* Y */
        }
        
        if( count($arrTimestamp) > 0 ){
            $numResults++;
            $line = new LinePlot($arrValue, $arrTimestamp);
            $line->SetStepStyle();
            $line->SetColor("#00cc00");
            $line->setFillColor("#00cc00");
            $line->SetLegend("# "._tr("Calls"));
            $graph->Add($line);
            $graph->yaxis->SetColor("#00cc00");
        }
    }
    //======================================================================================

    if ($numResults>0)
        $graph->Stroke();
    else{
        $graph = new CanvasGraph(500,140,"auto");
	$title = new Text(utf8_decode(_tr("No records found")));
	$title->ParagraphAlign('center');
	$title->SetFont(FF_FONT2,FS_BOLD);
	$title->SetMargin(3);
	$title->SetAlign('center');
	$title->Center(0,500,70);
	$graph->AddText($title);

	$t1 = new Text(utf8_decode(_tr("There are no data to present")));
	$t1->SetBox("white","black",true);
	$t1->ParagraphAlign("center");
	$t1->SetColor("black");

	$graph->AddText($t1);
	$graph->img->SetColor('navy');
	$graph->img->SetTextAlign('center','bottom');
	$graph->img->Rectangle(0,0,499,139);
	$graph->Stroke();

    }

}

function NameQueue($aVal){
    global $arrayX;
    return $arrayX[$aVal];
}

function grafic_trunk(&$pDB_ast_cdr, &$pDB_ast, $module_name, $trunk, $dti, $dtf)
{
	//*******************
	require_once "modules/$module_name/libs/paloSantoExtention.class.php";
	$objPalo_AST_CDR = new paloSantoExtention($pDB_ast_cdr);

    /* Si la troncal pedida es un grupo, se expande el grupo para averiguar las
       troncales individuales. */
    $regs = NULL;
    if (preg_match('!^DAHDI/(g|r)(\d+)$!i', $trunk, $regs)) {
        $iGrupoTrunk = (int)$regs[2];
        $gruposTrunk = getTrunkGroupsDAHDI();
        if (is_array($gruposTrunk) && isset($gruposTrunk[$iGrupoTrunk])) {
            $trunk = $gruposTrunk[$iGrupoTrunk];
        }
    }

	//total minutos de llamadas in y out
	$arrayTemp = $objPalo_AST_CDR->loadTrunks($trunk, "min", $dti, $dtf);
	$arrResult = $arrayTemp[0];

	//$arrResult[0] => "IN"
	//$arrResult[1] => "OUT"
	$tot = $arrResult[0] + $arrResult[1];
	$usoDisco = ($tot!=0)?100*( $arrResult[0] / $tot ):0;

	if( $tot != 0 )
	{
		$freeDisco = 100 - $usoDisco;
		
		// Some data
		$data = array($usoDisco, $freeDisco);
		
		// Create the Pie Graph.
		$graph = new PieGraph(400, 170,"auto");
		//$graph->SetShadow();
		$graph->SetMarginColor('#fafafa');
		$graph->SetFrame(true,'#999999');
		
		$graph->legend->SetFillColor("#fafafa");
		//$graph->legend->Pos(0.012, 0.5, "right","center");
		$graph->legend->SetColor("#444444", "#999999");
		$graph->legend->SetShadow('gray@0.6',4);
		
		//$graph->title->SetColor("#444444");
		
		// Set A title for the plot
		$graph->title->Set(utf8_decode(_tr("Total Time")));
		//$graph->title->SetFont(FF_VERDANA,FS_BOLD,18);
		$graph->title->SetColor("#444444");
		$graph->legend->Pos(0.05,0.2);
		
		// Create 3D pie plot
		$p1 = new PiePlot3d($data);
		//$p1->SetTheme("water");
		$p1->SetSliceColors( array("#3333cc", "#9999cc", "#CC3333", "#72394a", "#aa3424") ); 
		$p1->SetCenter(0.3);
		$p1->SetSize(80);
		
		// Adjust projection angle
		$p1->SetAngle(45);
		
		// Adjsut angle for first slice
		$p1->SetStartAngle(45);
		
		// Display the slice values
		//$p1->value->SetFont(FF_ARIAL,FS_BOLD,11);
		//$p1->value->SetColor("navy");
		$p1->value->SetColor("black");
		
		// Add colored edges to the 3D pies
		// NOTE: You can't have exploded slices with edges!
		$p1->SetEdge("black");
		
		$p1->SetLegends(array( utf8_decode(_tr("Incoming Calls").":\n").SecToHHMMSS($arrResult[0]),
		                       utf8_decode(_tr("Outcoming Calls").":\n").SecToHHMMSS($arrResult[1])));
		
		$graph->Add($p1);
		$graph->Stroke();
	}
	else
	{
		$graph = new CanvasGraph(400,140,"auto");
		$title = new Text(utf8_decode(_tr("Total Time")));
		$title->ParagraphAlign('center');
		$title->SetFont(FF_FONT2,FS_BOLD);
		$title->SetMargin(3);
		$title->SetAlign('center');
		$title->Center(0,400,70);
		$graph->AddText($title);

		$t1 = new Text(utf8_decode(_tr("There are no data to present")));
		$t1->SetBox("white","black",true);
		$t1->ParagraphAlign("center");
		$t1->SetColor("black");

		$graph->AddText($t1);
		$graph->img->SetColor('navy');
		$graph->img->SetTextAlign('center','bottom');
		$graph->img->Rectangle(0,0,399,139);
		$graph->Stroke();
	}
}

function SecToHHMMSS($sec)
{
    $HH = 0;$MM = 0;$SS = 0;
    $segundos = $sec;

    if( $segundos/3600 >= 1 ){ $HH = (int)($segundos/3600);$segundos = $segundos%3600;} if($HH < 10) $HH = "0$HH";
    if(  $segundos/60 >= 1  ){ $MM = (int)($segundos/60);  $segundos = $segundos%60;  } if($MM < 10) $MM = "0$MM";
    $SS = $segundos; if($SS < 10) $SS = "0$SS";

    return "$HH:$MM:$SS";
}

function grafic_trunk2(&$pDB_ast_cdr, &$pDB_ast, $module_name, $trunk, $dti, $dtf)
{
	//
	require_once "modules/$module_name/libs/paloSantoExtention.class.php";
	$objPalo_AST_CDR = new paloSantoExtention($pDB_ast_cdr);

    /* Si la troncal pedida es un grupo, se expande el grupo para averiguar las
       troncales individuales. */
    $regs = NULL;
    if (preg_match('!^DAHDI/(g|r)(\d+)$!i', $trunk, $regs)) {
        $iGrupoTrunk = (int)$regs[2];
        $gruposTrunk = getTrunkGroupsDAHDI();
        if (is_array($gruposTrunk) && isset($gruposTrunk[$iGrupoTrunk])) {
            $trunk = $gruposTrunk[$iGrupoTrunk];
        }
    }

	//total minutos de llamadas in y out
	$arrayTemp = $objPalo_AST_CDR->loadTrunks($trunk, "numcall", $dti, $dtf);
	$arrResult = $arrayTemp[0];

	//$arrResult[0] => "IN"
	//$arrResult[1] => "OUT"
	$tot = $arrResult[0] + $arrResult[1];
	$usoDisco = ($tot!=0)?100*( $arrResult[0] / $tot ):0;

	if( $tot != 0 )
	{
		$freeDisco = 100 - $usoDisco;
		
		// Some data
		$data = array($usoDisco, $freeDisco);
		
		// Create the Pie Graph.
		$graph = new PieGraph(400, 170,"auto");
		//$graph->SetShadow();
		$graph->SetMarginColor('#fafafa');
		$graph->SetFrame(true,'#999999');
		
		$graph->legend->SetFillColor("#fafafa");
		//$graph->legend->Pos(0.012, 0.5, "right","center");
		$graph->legend->SetColor("#444444", "#999999");
		$graph->legend->SetShadow('gray@0.6',4);
		
		//$graph->title->SetColor("#444444");
		
		// Set A title for the plot
		$graph->title->Set(utf8_decode(_tr("Number of Calls")));
		//$graph->title->SetFont(FF_VERDANA,FS_BOLD,18);
		$graph->title->SetColor("#444444");
		$graph->legend->Pos(0.04,0.2);
		
		// Create 3D pie plot
		$p1 = new PiePlot3d($data);
		//$p1->SetTheme("water");
		$p1->SetSliceColors( array("#3333cc", "#9999cc", "#CC3333", "#72394a", "#aa3424") ); 
		$p1->SetCenter(0.3);
		$p1->SetSize(80);
		
		// Adjust projection angle
		$p1->SetAngle(45);
		
		// Adjsut angle for first slice
		$p1->SetStartAngle(45);
		
		// Display the slice values
		//$p1->value->SetFont(FF_ARIAL,FS_BOLD,11);
		//$p1->value->SetColor("navy");
		$p1->value->SetColor("black");
		
		// Add colored edges to the 3D pies
		// NOTE: You can't have exploded slices with edges!
		$p1->SetEdge("black");
		
		$p1->SetLegends(array( utf8_decode(_tr("Incoming Calls").":  ").$arrResult[0],
		                       utf8_decode(_tr("Outcoming Calls").": ").$arrResult[1] ));
		
		$graph->Add($p1);
		$graph->Stroke();
	}
	else
	{

		$graph = new CanvasGraph(400,140,"auto");
		$title = new Text(utf8_decode(_tr("Number of Calls")));
		$title->ParagraphAlign('center');
		$title->SetFont(FF_FONT2,FS_BOLD);
		$title->SetMargin(3);
		$title->SetAlign('center');
		$title->Center(0,400,70);
		$graph->AddText($title);

		$t1 = new Text(utf8_decode(_tr("There are no data to present")));
		$t1->SetBox("white","black",true);
		$t1->ParagraphAlign("center");
		$t1->SetColor("black");

		$graph->AddText($t1);
		$graph->img->SetColor('navy');
		$graph->img->SetTextAlign('center','bottom');
		$graph->img->Rectangle(0,0,399,139);
		$graph->Stroke();

	}
}

?>
