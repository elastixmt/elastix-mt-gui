<?php

require_once "libs/paloSantoSampler.class.php";

class paloSantoChannelUsage
{
    function paloSantoChannelUsage()
    {
    }

    function channelsUsage($id)
    {
        $arrayResult = array();

        $oSampler = new paloSampler();

        //retorna
        //Array ( [0] => Array ( [id] => 1 [name] => Sim. calls [color] => #00cc00 [line_type] => 1 )
        $arrLines = $oSampler->getGraphLinesById($id);

        //retorna
        //Array ( [name] => Simultaneous calls, memory and CPU )
        $arrGraph = $oSampler->getGraphById($id);

        $endtime = time();
        $starttime = $endtime - 26*60*60;
        $oSampler->deleteDataBeforeThisTimestamp($starttime);

        $arrayResult['ATTRIBUTES'] = array('TITLE' => str_ireplace('zap', 'DAHDI', $arrGraph['name']),'TYPE'=>'lineplot',
            'LABEL_X'=>"",'LABEL_Y'=>'','SHADOW'=>false,'SIZE'=>"570,170",'MARGIN'=>"50,140,30,50",
            'COLOR' => "#fafafa",'POS_LEYEND'=> "0.02,0.5");

        $arrayResult['MESSAGES'] = array('ERROR' => 'Error', 'NOTHING_SHOW' => _tr('Nothing to show yet'));

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
            $arrStyle['LEYEND'] = str_ireplace('zap', 'DAHDI', $line['name']);
            $arrStyle['STYLE_STEP'] = true;
            $arrStyle['FILL_COLOR'] = false;

            $arrDat_N["VALUES"] = $arrValues;
            $arrDat_N["STYLE"] = $arrStyle;

            if(count($arrValues)>1)
		$arrData["DAT_$i"] = $arrDat_N;
	    else
		$arrData["DAT_$i"] = array();

            $i++;
        }
        $arrayResult['DATA'] = $arrData;

        return $arrayResult;
    }

    function functionCallback($value)
    {
        return Date('H:i', $value);
    }
}

?>
