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
  $Id: paloSantoGrid.class.php, bmacias@palosanto.com Exp $ */

global $arrConf;
class paloSantoGrid {
    private $title;
    private $icon;
    private $width;
    private $enableExport;
    private $limit;
    private $total;
    private $offset;
    private $currentPage;
    private $numPage;
    private $start;
    private $end;
    private $tplFile;
    private $pagingShow;
    private $nameFile_Export;
    private $arrHeaders;
    private $arrData;
    private $url;
    private $arrActions;
    private $arrControlFilters;
    private $_basePath;
    private $_webCommon;
    
    public function paloSantoGrid($smarty)
    {
        global $arrConf;
        $this->_basePath=$arrConf['basePath'];
        $this->_webCommon=$arrConf['webCommon'];
        $this->title  = "";
        $this->icon   = $this->_webCommon."/images/list.png";
        $this->width  = "99%";
        $this->smarty = $smarty;
        $this->enableExport = false;
        $this->offset = 0;
        $this->start  = 0;
        $this->end    = 0;
        $this->limit  = 0;
        $this->total  = 0;
        $this->pagingShow = 1;
        $this->tplFile    = "_common/_list.tpl";
        $this->nameFile_Export = "Report-".date("YMd.His");
        $this->arrHeaders = array();
        $this->arrData    = array();
        $this->url        = "";

        $this->arrActions = array();
        $this->arrFiltersControl = array();
    }

    public function addFilterControl($msg, &$arrData, $arrFilter = array(), $always_activated=false)
    {
		if (!empty($msg)) $msg = htmlentities($msg, ENT_COMPAT, 'UTF-8');
		$defaultFiler = "yes";
        if((is_array($arrFilter) && count($arrFilter)>0)){
            $name_delete_filters = getParameter('name_delete_filters');
            $keys = array_keys($arrFilter);
            $first = $keys[0];

            $name_delete_filters = explode(",",$name_delete_filters);
            if(in_array($first, $name_delete_filters)){ //accion eliminar
                foreach($arrFilter as $name => $value){
                    $arrData[$name] = $value;
                }
                if($always_activated){ // a pesar de que fue eliminado el filtro, se desea que el control siga visible.
                    $this->arrFiltersControl[] = array("msg" => $msg, "filters" => implode(",",$keys), "defaultFilter" => "yes");
				}
            }
            else{
                $filter_apply = true;
                foreach($arrFilter as $name => $value){
                    $val = (isset($arrData[$name]) && !$this->isEmpty($arrData[$name]))?$arrData[$name]:null;
                    if($val===null){
                        $filter_apply = false;
                        break;
                    }
					//esto se hace para poder saber si el fitro aplicado corresponde al valor por default del filtro
					if($always_activated){
						if($val!=$arrFilter[$name]){
							$defaultFiler = "no";
						}
					}else
						$defaultFiler = "no";
                }
                if($filter_apply){ //solo si todos estan seteados o tiene un value asociado (!=null)
                    $this->arrFiltersControl[] = array("msg" => $msg, "filters" => implode(",",$keys), "defaultFilter" => $defaultFiler);
				}
            }
        }
        else{
            echo "Invalid format for variable \$arrFilter.";
        }
    }

	public function isEmpty($var){
		if(!is_null($var)){
			if($var===false || $var==="" || (is_array($var) && count($var)==0)){
				return true;
			}
		}else
			return true;
		return false;
	}

    public function addNew($task="add", $alt="New Row", $asLink=false)
    {
        $type = ($asLink)?"link":"submit";
        $this->addAction($task,$alt,$this->_webCommon."/images/plus2.png",$type);
    }

    public function customAction($task="task", $alt="Custom Action", $img="",  $asLink=false)
    {
        $type = ($asLink)?"link":"submit";
        $this->addAction($task,$alt,$img,$type);
    }

    public function deleteList($msg="" , $task="remove", $alt="Delete Selected",  $asLink=false)
    {
        $type    = ($asLink)?"link":"submit";
        $onclick = "return confirmSubmit('"._tr($msg)."')";
        $this->addAction($task,$alt,$this->_webCommon."/images/delete5.png",$type,$onclick);
    }

    public function addLinkAction($href="action=add", $alt="New Row", $icon=null, $onclick=null)
    {
        $this->addAction($href,$alt,$icon,"link",$onclick);
    }

    public function addSubmitAction($task="add", $alt="New Row", $icon=null, $onclick=null)
    {
        $this->addAction($task,$alt,$icon,"submit",$onclick);
    }

    public function addButtonAction($name="add", $alt="New Row", $icon=null, $onclick="javascript:click()")
    {
        $this->addAction($name,$alt,$icon,"button",$onclick);
    }

    public function addInputTextAction($name_input="add", $label="New Row", $value_input="", $task="add", $onkeypress_text=null)
    {
        $newAction['type']  = "text";
        $newAction['name']  = $name_input;
        $newAction['alt']   = $label;
        $newAction['value'] = $value_input;
        $newAction['onkeypress'] = empty($onkeypress_text)?null:$onkeypress_text;
        $newAction['task']    = empty($task)?"add":$task;

        $this->arrActions[] = $newAction;
    }

    public function addComboAction($name_select="cmb", $label="New Row", $data=array(), $selected=null, $task="add", $onchange_select=null)
    {
        $newAction['type'] = "combo";
        $newAction['name'] = $name_select;
        $newAction['alt']  = $label;
        $newAction['arrOptions'] = empty($data)?array():$data;
        $newAction['selected']   = empty($selected)?null:$selected;
        $newAction['onchange']   = empty($onchange_select)?null:$onchange_select;
        $newAction['task']    = empty($task)?"add":$task;

        $this->arrActions[] = $newAction;
    }
    
    public function addButtonGroup($name="dropdown", $label="New Row", $data=array())
    {
        $newAction['type'] = "dropdown";
        $newAction['name'] = $name;
        $newAction['label']  = $label;
        $newAction['arrOptions'] = empty($data)?array():$data;
        $this->arrActions[] = $newAction;
    }

    public function addHTMLAction($html)
    {
        $this->addAction($html,null,null,"html",null);
    }

    private function addAction($task, $alt, $icon, $type="submit", $event=null)
    {
        $newAction = array();

        switch($type){
            case 'link':
            case 'button':
            case 'submit':
                $newAction = array(
                    'type' => $type,
                    'task' => $task,
                    'alt'  => $alt,
                    'icon' => $icon,
                    'onclick' => empty($event)?null:$event);
                break;
            case 'html':
                $newAction = array(
                    'type' => $type,
                    'html' => $task);
                break;
            default:
                $newAction = array(
                    'type' => "submit",
                    'task' => $task,
                    'alt'  => $alt,
                    'icon' => $icon);
                break;
        }

        $this->arrActions[] = $newAction;
    }

    function pagingShow($show)
    {
        $this->pagingShow = (int)$show;
    }

    function setTplFile($tplFile)
    {
        $this->tplFile  = $tplFile;
    }

    function getTitle()
    {
        return $this->title;
    }

    function setTitle($title)
    {
        $this->title = $title;
    }

    function getIcon()
    {
        return $this->icon;
    }

    function setIcon($icon)
    {
        $this->icon = $icon;
    }

    function getWidth()
    {
        return $this->width;
    }

    function setWidth($width)
    {
        $this->width = $width;
    }

    function setURL($arrURL)
    {
        if (is_array($arrURL))
            $this->url = construirURL($arrURL, array('nav', 'start', 'logout','name_delete_filters'));
        else
            $this->url = $arrURL;
    }
    
    function getURL()
    {
        return $this->url;
    }

    function getColumns()
    {
        return $this->arrHeaders;
    }

    function setColumns($arrColumns)
    {
        $arrHeaders = array();

        if(is_array($arrColumns) && count($arrColumns)>0){
            foreach($arrColumns as $k => $column){
                $arrHeaders[] = array(
                    "name"      => $column,
                    "property1" => "");
            }
        }
        $this->arrHeaders = $arrHeaders;
    }

    function getData()
    {
        return $this->arrData;
    }

    function setData($arrData)
    {
        if(is_array($arrData) && count($arrData)>0)
            $this->arrData = $arrData;
    }

    function fetchGrid($arrGrid=array(), $arrData=array(), $arrLang=array())
    {
        if(isset($arrGrid["title"]))
            $this->title = $arrGrid["title"];
        if(isset($arrGrid["icon"]))
            $this->icon  = $arrGrid["icon"];
        if(isset($arrGrid["width"]))
            $this->width = $arrGrid["width"];

        if(isset($arrGrid["start"]))
            $this->start = $arrGrid["start"];
        if(isset($arrGrid["end"]))
            $this->end   = $arrGrid["end"];
        if(isset($arrGrid["total"]))
            $this->total = $arrGrid["total"];

        if(isset($arrGrid['url'])) {
            if (is_array($arrGrid['url']))
                $this->url = construirURL($arrGrid['url'], array('nav', 'start', 'logout','name_delete_filters'));
            else
                $this->url = $arrGrid["url"];
        }

        if(isset($arrGrid["columns"]) && count($arrGrid["columns"]) > 0)
            $this->arrHeaders = $arrGrid["columns"];
        if(isset($arrData) && count($arrData) > 0)
            $this->arrData = $arrData;


        $export = $this->exportType();

        switch($export){
            case "csv":
                $content = $this->fetchGridCSV($arrGrid, $arrData);
                break;
            case "pdf":
                $content = $this->fetchGridPDF();
                break;
            case "xls":
                $content = $this->fetchGridXLS();
                break;
            default: //html
                $content = $this->fetchGridHTML();
                break;
        }
        return $content;
    }

    function fetchGridCSV($arrGrid=array(), $arrData=array())
    {
        if(isset($arrGrid["columns"]) && count($arrGrid["columns"]) > 0)
            $this->arrHeaders = $arrGrid["columns"];
        if(isset($arrData) && count($arrData) > 0)
            $this->arrData = $arrData;

        header("Cache-Control: private");
        header("Pragma: cache");    // Se requiere para HTTPS bajo IE6
        header('Content-Disposition: attachment; filename="'."{$this->nameFile_Export}.csv".'"');
        header("Content-Type: text/csv; charset=UTF-8");

        $numColumns = count($this->getColumns());
        $this->smarty->assign("numColumns", $numColumns);
        $this->smarty->assign("header",     $this->getColumns());
        $this->smarty->assign("arrData",    $this->getData());

        return $this->smarty->fetch("_common/listcsv.tpl");
    }

    function fetchGridPDF()
    {
        global $arrConf;
        require_once "{$arrConf['elxPath']}/libs/paloSantoPDF.class.php";

        $pdf= new paloPDF();
        $pdf->setOrientation("L");
        $pdf->setFormat("A3");
        $pdf->setColorHeader(array(5,68,132));
        $pdf->setColorHeaderTable(array(227,83,50));
        $pdf->setFont("Verdana");
        $pdf->printTable("{$this->nameFile_Export}.pdf", $this->getTitle(), $this->getColumns(), $this->getData());
        return "";
    }

    function fetchGridXLS()
    {
        header ("Cache-Control: private");
        header ("Pragma: cache");    // Se requiere para HTTPS bajo IE6
        header ('Content-Disposition: attachment; filename="'."{$this->nameFile_Export}.xls".'"');
        header ("Content-Type: application/vnd.ms-excel; charset=UTF-8");

        $tmp = $this->xlsBOF();
        # header
        $headers = $this->getColumns();
        foreach($headers as $i => $header)
            $tmp .= $this->xlsWriteCell(0,$i,$header["name"]);

        #data
        $data = $this->getData();
        foreach($data as $k => $row) {
            foreach($row as $i => $cell){
                $tmp .= $this->xlsWriteCell($k+1,$i,$cell);
            }
        }
        $tmp .= $this->xlsEOF();
        echo $tmp;
    }

    function fetchGridHTML($arrLang=array())
    {
        $this->smarty->assign("pagingShow",$this->pagingShow);

        $this->smarty->assign("arrActions",$this->arrActions);
        $this->smarty->assign("arrFiltersControl",$this->arrFiltersControl);

        $this->smarty->assign("title", $this->getTitle());
        $this->smarty->assign("icon",  $this->getIcon());
        $this->smarty->assign("width", $this->getWidth());

        $this->smarty->assign("start", $this->start);
        $this->smarty->assign("end",   $this->end);
        $this->smarty->assign("total", $this->total);

        $numPage = ($this->limit==0)?0:ceil($this->total / $this->limit);
        $this->smarty->assign("numPage",$numPage);

        $currentPage = ($this->limit==0 || $this->start==0)?0:(floor($this->start / $this->limit) + 1);
        $this->smarty->assign("currentPage",$currentPage);

        if(!empty($this->url))
            $this->smarty->assign("url",   $this->url);

        $numColumns = count($this->getColumns());
        $numData    = count($this->getData());
        $this->smarty->assign("numColumns", $numColumns);
        $this->smarty->assign("header",     $this->getColumns());
        $this->smarty->assign("arrData",    $this->getData());
        $this->smarty->assign("numData",    $numData);

        $this->smarty->assign("enableExport", $this->enableExport);

        //dar el valor a las etiquetas segun el idioma
        $etiquetas = array('Export','Start','Previous','Next','End','Page','of','records');
        foreach ($etiquetas as $etiqueta)
            $this->smarty->assign("lbl$etiqueta", _tr($etiqueta));

        $this->smarty->assign("NO_DATA_FOUND"     , _tr("No records match the filter criteria"));
        $this->smarty->assign("FILTER_GRID_SHOW"  , _tr("Show Filter"));
        $this->smarty->assign("FILTER_GRID_HIDE"  , _tr("Hide Filter"));
        $this->smarty->assign("MORE_OPTIONS"      , _tr("More Options"));
        $this->smarty->assign("DOWNLOAD_GRID"     , _tr("Download"));
        
        return $this->smarty->fetch($this->tplFile);
    }

    function showFilter($htmlFilter,$as_options=false)
    {
        if($as_options)
            $this->smarty->assign("AS_OPTION", 1);
        else
            $this->smarty->assign("AS_OPTION", 0);

        $this->smarty->assign("contentFilter", $htmlFilter);
    }

    function calculatePagination()
    {
        $accion = getParameter("nav");

        if($accion == "bypage"){
            $numPage = ($this->getLimit()==0)?0:ceil($this->getTotal() / $this->getLimit());

            $page  = getParameter("page");
            if(preg_match("/[0-9]+/",$page)==0)// no es un número
                $page = 1;

            if( $page > $numPage) // se está solicitando una pagina mayor a las que existen
                $page = $numPage;

            $start = ( ( ($page - 1) * $this->getLimit() ) + 1 ) - $this->getLimit();

            $accion = "next";
            if($start + $this->getLimit() <= 1){
                $accion = null;
                $start = null;
            }
        }
        else
            $start  = getParameter("start");

        $this->setOffsetValue($this->getOffSet($this->getLimit(),$this->getTotal(),$accion,$start));
        $this->setEnd(($this->getOffsetValue() + $this->getLimit()) <= $this->getTotal() ? $this->getOffsetValue() + $this->getLimit() : $this->getTotal());
        $this->setStart(($this->getTotal()==0) ? 0 : $this->getOffsetValue() + 1);
    }

    function calculateOffset()
    {
        $this->calculatePagination();
        return $this->getOffsetValue();
    }
    
    function calculateCurrentPage()
    {
        $this->currentPage = ($this->limit==0 || $this->start==0)?0:(floor($this->start / $this->limit) + 1);
        return $this->currentPage;
    }

    function calculateNumPage()
    {
        $this->numPage = ($this->limit==0)?0:ceil($this->total / $this->limit);
        return $this->numPage;
    }
    
    function getOffSet($limit,$total,$accion,$start)
    {
        // Si se quiere avanzar a la sgte. pagina
        if(isset($accion) && $accion=="next") {
            $offset = $start + $limit - 1;
        }
        // Si se quiere retroceder
        else if(isset($accion) && $accion=="previous") {
            $offset = $start - $limit - 1;
        }
        else if(isset($accion) && $accion=="end") {
            if(($total%$limit)==0)
                $offset = $total - $limit;
            else
                $offset = $total - $total%$limit;
        }
        else if(isset($accion) && $accion=="start") {
            $offset = 0;
        }
        else $offset = 0;
        return $offset;
    }

    function enableExport()
    {
        $this->enableExport = true;
    }

    function setLimit($limit)
    {
        $this->limit = $limit;
    }

    function setTotal($total)
    {
        $this->total = $total;
    }

    function setOffsetValue($offset)
    {
        $this->offset = $offset;
    }

    function setStart($start)
    {
        $this->start = $start;
    }

    function setEnd($end)
    {
        $this->end = $end;
    }

    function getLimit()
    {
        return $this->limit;
    }

    function getTotal()
    {
        return $this->total;
    }

    function getOffsetValue()
    {
        return $this->offset;
    }

    function getEnd()
    {
        return $this->end;
    }

    function exportType()
    {
        if(getParameter("exportcsv") == "yes")
            return "csv";
        else if(getParameter("exportpdf") == "yes")
            return "pdf";
        else if(getParameter("exportspreadsheet") == "yes")
            return "xls";
        else
            return "html";
    }

    function isExportAction()
    {
        if(getParameter("exportcsv") == "yes")
            return true;
        else if(getParameter("exportpdf") == "yes")
            return true;
        else if(getParameter("exportspreadsheet") == "yes")
            return true;
        else
            return false;
    }

    function setNameFile_Export($nameFile)
    {
        $this->nameFile_Export = "$nameFile-".date("YMd.His");
    }

    function xlsBOF()
    {
        $data = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
        return $data;
    }

    function xlsEOF()
    {
        $data = pack("ss", 0x0A, 0x00);
        return $data;
    }

    function xlsWriteNumber($Row, $Col, $Value)
    {
        $data  = pack("sssss", 0x203, 14, $Row, $Col, 0x0);
        $data .= pack("d", $Value);
        return $data;
    }

    function xlsWriteLabel($Row, $Col, $Value )
    {
        $Value2UTF8=utf8_decode($Value);
        $L = strlen($Value2UTF8);
        $data  = pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
        $data .= $Value2UTF8;
        return $data;
    }

    function xlsWriteCell($Row, $Col, $Value )
    {
        if(is_numeric($Value))
            return $this->xlsWriteNumber($Row, $Col, $Value);
        else
            return $this->xlsWriteLabel($Row, $Col, $Value);
    }
}
?>