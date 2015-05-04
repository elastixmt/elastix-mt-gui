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
  $Id: sampler.php,v 1.2 2007/07/07 22:50:40 admin Exp $ */

$elxPath="/usr/share/elastix";
// /usr/share/elastix/ directorio que contiene las librerias del sistema
//
ini_set('include_path',dirname($_SERVER['SCRIPT_FILENAME']).":$elxPath:".ini_get('include_path'));

require_once("libs/misc.lib.php");
require_once("configs/default.conf.php");
require_once("libs/paloSantoSampler.class.php");
require_once("libs/paloSantoDB.class.php");

$oSampler = new paloSampler();

// NUMERO DE LLAMADAS SIMULTANEAS
$simCalls = 0;
$comando = "/usr/sbin/asterisk -r -x \"core show channels\"";
exec($comando, $arrSalida, $varSalida);

$counter_channels_dahdi = 0;
$counter_channels_sip = 0;
$counter_channels_iax = 0;
$counter_channels_h323 = 0;
$counter_channels_local = 0;

foreach($arrSalida as $linea) {
    if(eregi("^DAHDI/", $linea)) {
        $counter_channels_dahdi++;
    } else if(eregi("SIP", $linea)) {
        $counter_channels_sip++;
    } else if(eregi("IAX2", $linea)) {
        $counter_channels_iax++;
    } else if(eregi("h323", $linea)) {
        $counter_channels_h323++;
    } else if(eregi("Local", $linea)) {
        $counter_channels_local++;
    } else if(preg_match("/^([[:digit:]]+)[[:space:]]+active calls?/", $linea, $arrReg)) {
        $simCalls = $arrReg[1];
    }
}

$counter_channels_total = $counter_channels_dahdi + $counter_channels_sip + $counter_channels_iax + $counter_channels_h323 + $counter_channels_local;

$timestamp = time();
$oSampler->insertSample(1, $timestamp, $simCalls);

$arrSysInfo = obtener_info_de_sistema();

// CPU Usage
$cpuUsage = number_format($arrSysInfo['CpuUsage'] * 100, 2);
$timestamp = time();
$oSampler->insertSample(2, $timestamp, $cpuUsage);

// Memory Usage
$memUsage = number_format(($arrSysInfo['MemTotal'] - $arrSysInfo['MemFree'] - $arrSysInfo['Cached'] - $arrSysInfo['MemBuffers'])/1024, 2);
$timestamp = time();
$oSampler->insertSample(3, $timestamp, $memUsage);

// Total Channels Usage
$timestamp = time();
$oSampler->insertSample(4, $timestamp, $counter_channels_total);

// DAHDI Channels Usage
$timestamp = time();
$oSampler->insertSample(5, $timestamp, $counter_channels_dahdi);

// SIP Channels Usage
$timestamp = time();
$oSampler->insertSample(6, $timestamp, $counter_channels_sip);

// IAX Channels Usage
$timestamp = time();
$oSampler->insertSample(7, $timestamp, $counter_channels_iax);

// H323 Channels Usage
$timestamp = time();
$oSampler->insertSample(8, $timestamp, $counter_channels_h323);

// Local Channels Usage
$timestamp = time();
$oSampler->insertSample(9, $timestamp, $counter_channels_local);

// Delete old data
$timestampLimiteBorrarData = $timestamp - 26 * (60 * 60);
$oSampler->deleteDataBeforeThisTimestamp($timestampLimiteBorrarData);
?>
