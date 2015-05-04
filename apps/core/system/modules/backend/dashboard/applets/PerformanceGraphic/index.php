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
  $Id: index.php,v 1.1 2007/01/09 23:49:36 alex Exp $
*/
global $arrConf;
require_once "libs/paloSantoSampler.class.php";
require_once "libs/paloSantoGraphImage.lib.php";

class Applet_PerformanceGraphic
{
    function handleJSON_getContent($smarty, $module_name, $appletlist)
    {
        $respuesta = array(
            'status'    =>  'success',
            'message'   =>  '(no message)',
        );
        //CallsMemoryCPU
        $respuesta['html'] = "<div class='tabFormTable' style='text-align:center;'><img alt=\"CallsMemoryCPU\" src=\"?menu=$module_name&amp;rawmode=yes&amp;applet=PerformanceGraphic&amp;action=graphic\"/></div>";
    
        $json = new Services_JSON();
        Header('Content-Type: application/json');
        return $json->encode($respuesta);
    }
    
    function handleJSON_graphic($smarty, $module_name, $appletlist)
    {
        $result = $this->_sampler_CallsMemoryCPU();
        displayGraphResult($result);
    }

    private function _sampler_CallsMemoryCPU()
    {
        $arrayResult = array();

        $oSampler = new paloSampler();

        //retorna
        //Array ( [0] => Array ( [id] => 1 [name] => Sim. calls [color] => #00cc00 [line_type] => 1 )
        $arrLines = $oSampler->getGraphLinesById(1);

        //retorna
        //Array ( [name] => Simultaneous calls, memory and CPU )
        $arrGraph = $oSampler->getGraphById(1);

        $endtime = time();
        $starttime = $endtime - 26*60*60;
        $oSampler->deleteDataBeforeThisTimestamp($starttime);

        $arrayResult['ATTRIBUTES'] = array(
            'TITLE'     =>  utf8_decode(_tr($arrGraph['name'])),
            'TYPE'      =>  'lineplot_multiaxis',
            'LABEL_X'   =>  'Etiqueta X',
            'LABEL_Y'   =>  'Etiqueta Y',
            'SHADOW'    =>  false,
            'SIZE'      =>  "450,260",
            'MARGIN'    =>  "50,110,30,120",
            'COLOR'     => "#fafafa",
            'POS_LEYEND'=> "0.35,0.85",
        );

        $arrayResult['MESSAGES'] = array(
            'ERROR' => 'Error',
            'NOTHING_SHOW' => _tr('Nothing to show yet')
        );

        //$oSampler->getSamplesByLineId(1)
        //retorna
        //Array ( [0] => Array ( [timestamp] => 1230562202 [value] => 2 ), ....... 

        $i = 1;
        $arrData = array();
        foreach($arrLines as $num => $line)
        {
            $arraySample = $oSampler->getSamplesByLineId($line['id']);

            $arrDat_N = array();

            $arrValues = array();
            foreach( $arraySample as $num => $time_value )
                $arrValues[ $time_value['timestamp'] ] = (int)$time_value['value'];

            $arrStyle = array();
            $arrStyle['COLOR'] = $line['color'];
            $arrStyle['LEYEND'] = utf8_decode(_tr($line['name']));
            $arrStyle['STYLE_STEP'] = true;
            $arrStyle['FILL_COLOR'] = ($i==1)?true:false;

            $arrDat_N["VALUES"] = $arrValues;
            $arrDat_N["STYLE"] = $arrStyle;

            if(count($arrValues)>1)
                $arrData["DAT_$i"] = $arrDat_N;
            else
                $arrData["DAT_$i"] = array();

            $i++;
        }
        $arrayResult['DATA'] = $arrData;
        $arrayResult['FORMAT_CALLBACK'] = array($this, 'functionCallback');

        return $arrayResult;
    }

    function functionCallback($value)
    {
        return Date('H:i', $value);
    }

}

?>