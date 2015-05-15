<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.2-2                                               |
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
  $Id: default.conf.php,v 1.1 2008-09-01 05:09:57 Bruno Macias <bmacias@palosanto.com> Exp $ */

include_once "libs/cyradm.php";
include_once "apps/antispam/libs/sieve-php.lib.php";

class paloSantoAntispam {
    public $fileMaster;
    public $fileLocal;
    public $folderPostfix;
    public $folderSpamassassin;
    public $errMsg;

    function paloSantoAntispam($pathPostfix,$pathSpamassassin,$fileMaster,$fileLocal)
    {
        $this->fileLocal     = $fileLocal;
        $this->fileMaster    = $fileMaster;
        $this->folderPostfix = $pathPostfix;
        $this->folderSpamassassin = $pathSpamassassin;
    }

    /*HERE YOUR FUNCTIONS*/

    function isActiveSpamFilter()
    {
        $step_three_config = false;
        exec("sudo /sbin/service generic-cloexec spamassassin status",$arrConsole,$flagStatus);
        if($flagStatus == 0){
            if(preg_match("/pid/",$arrConsole[0]))
                $step_three_config = true;
        }
        return $step_three_config;
    }

    function getValueRequiredHits()
    {
        // Trato de abrir el archivo de configuracion
        $data = array();
        if($fh = @fopen($this->fileLocal, "r")) {
            while($line_file = fgets($fh, 4096)) {
                //line to valid:required_hits 5
                if(preg_match("/[[:space:]]*required_hits[[:space:]]+([[:digit:]]{0,2})/",$line_file,$arrReg)){
                        $data['level'] = $arrReg[1];
                }
                if(preg_match("/[[:space:]]*rewrite_header[[:space:]]*Subject[[:space:]]+(.*)/",$line_file,$arrReg2)){
                        $data['header'] = $arrReg2[1];
                }
            }
        }
        return $data;
    }

    function activateSpamFilter($time_spam = NULL)
    {
    	$this->errMsg = '';
        $output = $retval = NULL;
        if (!is_null($time_spam)) switch ($time_spam) {
        case 'one_week': $time_spam = 7; break;
        case 'two_week': $time_spam = 14; break;
        case 'one_month':
        default:         $time_spam = 30; break;
        }
        exec('/usr/bin/elastix-helper spamconfig --enablespamfilter'.
            (is_null($time_spam) ? '' : ' --deleteperiod '.escapeshellarg($time_spam)),
            $output, $retval);
        if ($retval != 0) {
            foreach ($output as $s) {
                $regs = NULL;
                if (preg_match('/^ERR: (.+)$/', trim($s), $regs)) {
                    $this->errMsg = $regs[1];
                }
            }
        	return FALSE;
        }
        return TRUE;
    }
    
    function disactivateSpamFilter()
    {
        $this->errMsg = '';
        $output = $retval = NULL;
        exec('/usr/bin/elastix-helper spamconfig --disablespamfilter',
            $output, $retval);
        if ($retval != 0) {
            foreach ($output as $s) {
                $regs = NULL;
                if (preg_match('/^ERR: (.+)$/', trim($s), $regs)) {
                    $this->errMsg = $regs[1];
                }
            }
            return FALSE;
        }
        return TRUE;
    }
    
    function changeFileLocal($level, $header)
    {
    	$this->errMsg = '';
        $output = $retval = NULL;
        exec('/usr/bin/elastix-helper spamconfig --setlevelheader'.
            ' --requiredhits '.escapeshellarg($level).
            ' --headersubject '.escapeshellarg($header),
            $output, $retval);
        if ($retval != 0) {
            foreach ($output as $s) {
                $regs = NULL;
                if (preg_match('/^ERR: (.+)$/', trim($s), $regs)) {
                    $this->errMsg = $regs[1];
                }
            }
        	$this->errMsg .= ' '._tr('The command failed when attempting to change the header');
            return FALSE;
        }
        return TRUE;
    }
    
    function getTimeDeleteSpam()
    {
        $output = $retval = NULL;
        exec('/usr/bin/elastix-helper spamconfig --getdeleteperiod 2>&1', $output, $retval);
        if ($retval != 0 || count($output) < 1) return '';
        switch (trim($output[0])) {
            case '7':   return 'one_week';
            case '14':  return 'two_week';
            case '30':  return 'one_month';
            default:    return '';
        }
    }

    function existScriptSieve($email, $search)
    {
        $SIEVE  = array();
        $SIEVE['HOST'] = "localhost";
        $SIEVE['PORT'] = 4190;
        $SIEVE['USER'] = "";
        $SIEVE['PASS'] = obtenerClaveCyrusAdmin();
        $SIEVE['AUTHTYPE'] = "PLAIN";
        $SIEVE['AUTHUSER'] = "cyrus";
        $SIEVE['USER'] = $email;
        $result['status']  = false;
        $result['actived'] = "";

        $flag = $status = null;
        exec("echo ".$SIEVE['PASS']." | sieveshell --username=".$SIEVE['USER']." --authname=".$SIEVE['AUTHUSER']." ".$SIEVE['HOST'].":".$SIEVE['PORT']." -e 'list'",$flags, $status);

        if($status != 0){
            return null;
        }else{
            for($i=0; $i<count($flags); $i++){
                $value = trim($flags[$i]);
                if(preg_match("/$search/", $value)){
                    $result['status'] = true;
                }
                if(preg_match("/active script/", $value)){
                    $result['actived'] = $value;
                }
            }
        }
        return $result;
    }
    
    function getContentScript(){
        $script = <<<SCRIPT
require "fileinto";
if exists "X-Spam-Flag" {
    if header :is "X-Spam-Flag" "YES" {
        fileinto "Spam";
        stop;
    }
}
if exists "X-Spam-Status" {
    if header :contains "X-Spam-Status" "Yes," {
        fileinto "Spam";
        stop;
    }
}
SCRIPT;
        return $script;
    }
    
}
?>
