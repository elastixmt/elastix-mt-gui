<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4-22                                               |
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
  $Id: paloSantoPostfixStats.class.php,v 1.1 2011-06-01 10:06:04 Alberto Santos asantos@palosanto.com Exp $ */

global $arrConf;
require_once "libs/misc.lib.php";
require_once "libs/paloSantoDB.class.php";

class paloSantoPostfixStats{
    var $_DB;
    var $errMsg;

    function paloSantoPostfixStats(&$pDB)
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

    /*HERE YOUR FUNCTIONS*/

    function postfixStats($id)
    {
        $arrayResult = array();
        $arrStyle = array();
        $endtime = time();
        switch($id){
            case 0:	
                $starttime = $endtime - 28*60*60;
                $data = $this->getStats($starttime,0);
                $title = _tr("Incoming Email by Hour");
                break;
            case 1:
                $starttime = $endtime - 7*24*60*60;
                $data = $this->getStats($starttime,1);
                $title = _tr("Incoming Email by Day");
                break;
            case 2:
                $starttime = $endtime - 366*24*60*60;
                $data = $this->getStats($starttime,2);
                $title = _tr("Incoming Email by Month");
                break;
	}
        $arrayResult['ATTRIBUTES'] = array('TITLE' => $title,'TYPE'=>'lineplot',
                'LABEL_X'=>"",'LABEL_Y'=>'','SHADOW'=>false,'SIZE'=>"580,210",'MARGIN'=>"50,160,30,70",
                'COLOR' => "#fafafa",'POS_LEYEND'=> "0.02,0.5");

        $arrayResult['MESSAGES'] = array('ERROR' => 'Error', 'NOTHING_SHOW' => _tr('Nothing to show yet'));

        $arrData = array();
        $arrDat_N = array();

        $arrValues = array();
        foreach($data as $key => $value)
            $arrValues[$value["unix_time"]] = (int)$value['total'];

        $arrStyle['COLOR'] = "#0000cc";
        $arrStyle['STYLE_STEP'] = true;
        $arrStyle['FILL_COLOR'] = false;
        $arrStyle['LEYEND'] = _tr("Number of Emails");
        $arrDat_N["VALUES"] = $arrValues;
        $arrDat_N["STYLE"] = $arrStyle;

        $arrData["DAT_1"] = $arrDat_N;

        if(count($arrValues)>1)
            $arrayResult['DATA'] = $arrData;
        else
            $arrayResult['DATA'] = array();
        return $arrayResult;
    }

    function getStats($timestamp,$type)
    {
        $query = "select * from email_statistics where unix_time>=? and type=? order by unix_time";
        $result = $this->_DB->fetchTable($query,true,array($timestamp,$type));
        if($result === FALSE)
            {
                $this->errMsg = $this->_DB->errMsg;
                return array();
            }
        return $result;
    }

    function functionCallback($value)
    {
        $date = date('d/H',$value);
        $date = explode("/",$date);
        if(($date[0] == "01" || $date[0] == "15") && $date[1] == "00")
            return date('M d', $value);
        if($date[1] == "00" || $date[1] == "06" || $date[1] == "12" || $date[1] == "18")
            return date('D H:i',$value);
        return date('H:i',$value);
    }
}
?>