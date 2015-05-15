<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.3                                                |
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
  $Id: index.php,v 1.1 2010-12-18 05:12:06 Bruno Macias bmacias@palosanto.com Exp $ */
    
    //include elastix framework
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{

    //global variables
    global $arrConf;
    
    //folder path for custom templates
    $local_templates_dir=getWebDirModule($module_name);
    
    $accion = getAction();
    $content = "";

    switch($accion)
    {
        default:
            $content = report_AccessAudit($smarty, $module_name, $local_templates_dir);
            break;
    }
    return $content;
}

function report_AccessAudit($smarty, $module_name, $local_templates_dir)
{
    global $arrPermission;
    $pAccessLogs   = new paloSantoAccessaudit();
    $listaFechas     = $pAccessLogs->astLog->listarFechas();
    $arrFormElements = createFieldFilter($listaFechas);
    $field_pattern   = getParameter("filter");
    $busqueda        = getParameter('busqueda');
    $sUltimaBusqueda = getParameter('ultima_busqueda');
    $iUltimoOffset   = getParameter('ultimo_offset');

    if (is_null($busqueda) || trim($busqueda) == '') $busqueda = '';
    if ($busqueda != '') $_POST['busqueda'] = $busqueda;

    /* Última búsqueda, si existe */
    if (is_null($sUltimaBusqueda) || $sUltimaBusqueda == '' ||
        is_null($iUltimoOffset) || !ereg('^[[:digit:]]+$', $iUltimoOffset)) {
        $sUltimaBusqueda = NULL;
        $iUltimoOffset   = NULL;
    }

    if (!ereg($arrFormElements['filter']['VALIDATION_EXTRA_PARAM'], $field_pattern))
        $field_pattern = $listaFechas[count($listaFechas) - 1];

    $_POST['filter']   = $field_pattern;
    $oFilterForm = new paloForm($smarty, $arrFormElements);

    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr("Audit"));
    $oGrid->setIcon("web/apps/$module_name/images/security_audit.png");
    $oGrid->pagingShow(true); // show paging section.
    if(in_array("export",$arrPermission))
        $oGrid->enableExport();   // enable export.
    $oGrid->setNameFile_Export(_tr("Access audit"));
    if(in_array("export",$arrPermission))
        $isExport = $oGrid->isExportAction();
    else
        $isExport = false;

    $total_datos = $pAccessLogs->ObtainNumAccessLogs($field_pattern);
    $totalBytes  = $total_datos[0];
    $iNumLineasPorPagina  = 30;
    $iEstimadoBytesPagina = $iNumLineasPorPagina * 128;

    $iOffsetVerdadero = getParameter('offset');
    if (is_null($iOffsetVerdadero) || !ereg('^[[:digit:]]+$', $iOffsetVerdadero))
        $iOffsetVerdadero = 0;

    if ($iOffsetVerdadero >= $totalBytes) $iOffsetVerdadero = 0;

    if (isset($_GET['filter']) && isset($_POST['filter']) && $_GET['filter'] != $_POST['filter'])
        $iOffsetVerdadero = 0;

    /* Para ubicarse en la página, se obtiene la región 5 páginas estimadas hacia delante y
       5 páginas estimadas hacia atrás desde el offset indicado.
     */
    $inicioRango = $iOffsetVerdadero - 5 * $iEstimadoBytesPagina;
    if ($inicioRango < 0) $inicioRango = 0;
    if($isExport)
        $arrResult =$pAccessLogs->ObtainAccessLogs($totalBytes, 0, $field_pattern, NULL, $isExport);
    else
        $arrResult =$pAccessLogs->ObtainAccessLogs(10 * $iEstimadoBytesPagina, $inicioRango, $field_pattern, NULL, $isExport);

    /* Localizar la línea del offset verdadero, así como los offsets de las páginas previa y siguiente */
    for ($iPos = 0; $iPos < count($arrResult); $iPos++) {
        if ($arrResult[$iPos]['offset'] >= $iOffsetVerdadero) break;
    }
    $iPosPrevio    = $iPos - $iNumLineasPorPagina;
    $iPosSiguiente = $iPos + $iNumLineasPorPagina;

    if ($iPosPrevio < 0) $iPosPrevio = 0;
    if ($iPosSiguiente > count($arrResult) - 1) $iPosSiguiente = count($arrResult) - 1;
    $iOffsetPrevio    = $arrResult[$iPosPrevio]['offset'];
    $iOffsetSiguiente = $arrResult[$iPosSiguiente]['offset'];

    $limit=30;
    $total=(int)($totalBytes / 128);
    $offset = $iOffsetVerdadero;
    $nav = getParameter('nav');
    if ($nav) switch ($nav) {
    case 'start':
        $offset = 0;
        break;
    case 'end':
        /* Caso especial: se debe tomar la última sección del log */
        $inicioRango = $totalBytes - 5 * $iEstimadoBytesPagina;
        if ($inicioRango < 0) $inicioRango = 0;
        if($isExport)
            $arrResult =$pAccessLogs->ObtainAccessLogs($totalBytes, 0, $field_pattern,
            (($busqueda != '') ? $busqueda : NULL), $isExport);
        else
            $arrResult =$pAccessLogs->ObtainAccessLogs(10 * $iEstimadoBytesPagina, $inicioRango, $field_pattern, NULL, $isExport);
        if (count($arrResult) <= $iNumLineasPorPagina)
            $offset = $arrResult[0]['offset'];
        else $offset = $arrResult[count($arrResult) - $iNumLineasPorPagina]['offset'];
        break;
    case 'next':
        $offset = $iOffsetSiguiente;
        break;
    case 'previous':
        $offset = $iOffsetPrevio;
        break;
        case 'bypage':
        $numPage = ($limit==0)?0:ceil($total / $limit);

        $page  = getParameter("page");
        if(preg_match("/[0-9]+/",$page)==0)// no es un número
            $page = 1;

        if( $page > $numPage) // se está solicitando una pagina mayor a las que existen
            $page = $numPage;

        $start = ( ( ($page - 1) * $limit ) + 1 ) - $limit;

        //$accion = "next";
        if($start + $limit <= 1){
            break;
        }

        /*$inicioRango = $page * $iEstimadoBytesPagina;

        $arrResult =$pAccessLogs->ObtainAccessLogs(10 * $iEstimadoBytesPagina, $inicioRango, $field_pattern, NULL, $isExport);
        $offset = $arrResult[0]['offset'];

        $oGrid->setOffsetValue($offset);

        $oGrid->setEnd(((int)($offset / 128) + $iNumLineasPorPagina) <= $oGrid->getTotal() ? (int)($offset / 128) + $iNumLineasPorPagina : $oGrid->getTotal());

        $oGrid->setStart(($oGrid->getTotal()==0) ? 0 : (1 + (int)($offset / 128)));*/
        $inicioBusqueda = ($page * $iEstimadoBytesPagina) - ($iEstimadoBytesPagina);
        $arrResult =$pAccessLogs->ObtainAccessLogs(10 * $iEstimadoBytesPagina, $inicioBusqueda, $field_pattern, NULL, $isExport);
                $offset = $arrResult[0]['offset'];

        $oGrid->setOffsetValue($offset);
        break;
    }

    // Buscar la cadena de texto indicada, y modificar offset si se encuentra
    $smarty->assign("SEARCHNEXT", _tr('Search'));
    if (isset($_POST['searchnext'])  && $busqueda != '') {
        $pAccessLogs->astLog->posicionarMensaje($field_pattern, $offset);
        $posBusqueda = $pAccessLogs->astLog->buscarTextoMensaje($busqueda);
        if (!is_null($posBusqueda)) {
            $offset = $posBusqueda[1];
            $smarty->assign('SEARCHNEXT', _tr('Search next'));
            $_POST['ultima_busqueda'] = $busqueda;
            $_POST['ultimo_offset']   = $offset;

            // Si el offset anterior indicado es idéntico al offset recién encontrado
            // y la cadena de búsqueda es también idéntica, se asume que se ha
            // pedido una búsqueda de la siguiente ocurrencia.
            if (!is_null($sUltimaBusqueda) && !is_null($iUltimoOffset) &&
                $offset == $iUltimoOffset && $sUltimaBusqueda == $busqueda) {
                $pAccessLogs->astLog->posicionarMensaje($field_pattern, $offset);
                $pAccessLogs->astLog->siguienteMensaje(); // Sólo para ignorar primera ocurrencia
                $posBusqueda = $pAccessLogs->astLog->buscarTextoMensaje($busqueda);
                if (!is_null($posBusqueda)) {
                    $offset = $posBusqueda[1];
                    $_POST['ultimo_offset'] = $offset;
                }
            }
        } else {
        }
    }

    $url = array(
        'menu'              =>  $module_name,
        'filter'            =>  $field_pattern,
        'offset'            =>  $offset,
        'busqueda'          =>  $busqueda,
        'ultima_busqueda'   =>  (isset($_POST['ultima_busqueda']) ? $_POST['ultima_busqueda'] : ''),
        'ultimo_offset'     =>  (isset($_POST['ultimo_offset']) ? $_POST['ultimo_offset'] : ''),
    );
    $oGrid->setURL($url);
    //Fin Paginacion

    if($isExport)
        $arrResult =$pAccessLogs->ObtainAccessLogs($totalBytes, 0, $field_pattern,
        (($busqueda != '') ? $busqueda : NULL), $isExport);
    else
        $arrResult =$pAccessLogs->ObtainAccessLogs(10 * $iEstimadoBytesPagina, $offset, $field_pattern,
        (($busqueda != '') ? $busqueda : NULL), $isExport);
    if(!$isExport)
        $arrResult = array_slice($arrResult, 0, $iNumLineasPorPagina);

    $arrData = null;
    if(is_array($arrResult) && $totalBytes>0){
        foreach($arrResult as $key => $value){
            $arrTmp[0] = $value['fecha'];
            $arrTmp[1] = $value['tipo'];
            $arrTmp[2] = $value['origen'];
            $arrTmp[3] = $value['linea'];
            $arrData[] = $arrTmp;
        }
    }

    $arrColumns = array(_tr("Date"),_tr("Type"),_tr("User"),_tr("Message"));
    $oGrid->setColumns($arrColumns);
    $oGrid->setData($arrData);
    $oGrid->setStart(($totalBytes==0) ? 0 : 1 + (int)($offset / 128));

    $t = (int)($totalBytes / 128);
    $e = (int)($offset / 128) + $iNumLineasPorPagina;
    $e = ($t <= $e)?$t:$e;
    $oGrid->setEnd($e+1);
    $oGrid->setTotal($t+1);
    $oGrid->setLimit(30);

    $_POST['offset'] = $offset;

    $smarty->assign("SHOW", _tr("Show"));

    $oGrid->addFilterControl(_tr("Filter applied: ")._tr("Date")." = ".$_POST['filter'], $_POST, array('filter' => $listaFechas[count($listaFechas) - 1]),true);
    $oGrid->addFilterControl(_tr("Filter applied: ")._tr('Search string')." = ".$busqueda, $_POST, array('busqueda' => ""));

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $_POST);
    $oGrid->showFilter(trim($htmlFilter));
    return $oGrid->fetchGrid();
}

function createFieldFilter($listaFechas)
{
    $comboFechas = array();
    foreach ($listaFechas as $f) $comboFechas[$f] = $f;

    $arrFormElements = array(
            "filter"            => array(   "LABEL"                  => _tr("Date"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $comboFechas,
                                            "VALIDATION_TYPE"        => "ereg",
                                            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]{4}-[[:digit:]]{2}-[[:digit:]]{2}$'),

            "busqueda"          => array(
                                            "LABEL"                  => _tr('Search string'),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            'VALIDATION_TYPE'           =>  'text',
                                            'INPUT_EXTRA_PARAM'         =>  '',
                                            'VALIDATION_EXTRA_PARAM'    =>  '',
            ),
            "offset"            => array(   "LABEL"                  => _tr("offset"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "HIDDEN",
                                            "INPUT_EXTRA_PARAM"      => NULL,
                                            "VALIDATION_TYPE"        => "ereg",
                                            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]+$'),

            /* Variables requeridas para seguir la pista de la última búsqueda */
            "ultima_busqueda"          => array(
                                            "LABEL"                  => _tr('Search string'),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "HIDDEN",
                                            'VALIDATION_TYPE'           =>  'text',
                                            'INPUT_EXTRA_PARAM'         =>  '',
                                            'VALIDATION_EXTRA_PARAM'    =>  '',),
            "ultimo_offset"            => array(   "LABEL"                  => _tr("offset"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "HIDDEN",
                                            "INPUT_EXTRA_PARAM"      => NULL,
                                            "VALIDATION_TYPE"        => "ereg",
                                            "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]+$'),
                                );
    return $arrFormElements;
}

function getAction()
{
    return "report";
}
?>
