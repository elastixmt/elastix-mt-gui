#!/usr/bin/php
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
  $Id: disable_vacations.php,v 1.1 2011-05-01 05:09:57 Eduardo Cueva <ecueva@palosanto.com> Exp $ */

  // script para eliminar el script de vacaciones si ya se ha pasado el periodo de vacaciones
    $module_name = "vacations";
    $module_name2  = "antispam";

    $elxPath="/usr/share/elastix";
    ini_set('include_path',"$elxPath:".ini_get('include_path'));

    include_once "libs/misc.lib.php";
    include_once "configs/default.conf.php";
    include_once "libs/paloSantoDB.class.php";
    include_once "configs/email.conf.php";
    include_once "apps/vacations/libs/paloVacation.class.php";
    include_once "apps/antispam/libs/paloSantoAntispam.class.php";

    load_default_timezone();

    $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
    $pVacations  = new paloVacation($pDB);
    $objAntispam = new paloSantoAntispam("", "", "", "");
    // obteniendo todas las cuentas de correos con el script de vacaciones activado.
    $emails = $pVacations->getEmailsVacationON();
    $timestamp1 = mktime(0,0,0,date("m"),date("d"),date("Y"));

    if(count($emails)>0 && is_array($emails)>0){
	foreach($emails as $key => $value){
	    $id       = $value['id'];
        $id_user = $value['id_user'];
	    $email    = $value['account'];
	    $subject  = $value['subject'];
	    $body     = $value['body'];
	    $ini_date = $value['ini_date'];
	    $end_date = $value['end_date'];
        
	    $day_ini   = date("d",strtotime($ini_date));
	    $month_ini = date("m",strtotime($ini_date));
	    $year_ini  = date("Y",strtotime($ini_date));
	    $day_end   = date("d",strtotime($end_date));
	    $month_end = date("m",strtotime($end_date));
	    $year_end  = date("Y",strtotime($end_date));
	    $timestamp0 = mktime(0,0,0,$month_ini,$day_ini,$year_ini);
	    $timestamp2 = mktime(0,0,0,$month_end,$day_end,$year_end);
	    $spamCapture = false;
	    $seconds0 = $timestamp1 - $timestamp0;

	    //resto a una fecha la otra
	    $seconds = $timestamp1 - $timestamp2;
	    $dias = $seconds / (60 * 60 * 24);
	    $dias = abs($dias);
	    $dias = floor($dias);

	    $scripts = $objAntispam->existScriptSieve($email, "scriptTest.sieve");

	    // verifica que usuarios no tienen activado el script de vacaciones
	    if($seconds0 >= 0 && $seconds <= 0){// si la fecha inicial >= fecha actual entonces se debe subir el script
		    $spamCapture0 = false;
		    if(preg_match("/scriptTest.sieve/",$scripts['actived']) && $scripts['status']) // si CapturaSpam=? y Vacations=ON
		        $spamCapture0 = true;
		    $body = str_replace("{END_DATE}", $end_date, $body);
		    $status = $pVacations->uploadVacationScript($email, $subject, $body, $objAntispam, $spamCapture0);
	    }

	    // elimina el script de vacaciones si el tiempo de sus vacaciones ya expiro
	    if($scripts['actived'] != ""){
		    if(preg_match("/vacations.sieve/",$scripts['actived']) && $scripts['status']) // si CapturaSpam=? y Vacations=ON
		        $spamCapture = true;
		        if($seconds > 0){// si es positivo entonces la fecha actual es mayor que la fecha final del script
		            $res = $pVacations->updateMessageByUser($id_user, $subject, $body, $ini_date, $end_date, "no");
		            if($res){
			            $status = $pVacations->deleteVacationScript($email, $objAntispam, $spamCapture);
                    }
		            if(!$status)
			            echo $pVacations->errMsg;
		        }
	        }
	    }
    }
    

?>
