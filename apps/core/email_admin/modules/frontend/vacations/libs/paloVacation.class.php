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
  $Id: paloSantoForm.class.php,v 1.4 2007/05/09 01:07:03 gcarrillo Exp $ */
global $arrConf;
 
class paloVacation{

    public $_DB;
    public $errMsg;
    
    public function paloVacation(&$pDB){
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


    function getErrorMsg(){
        return $this->errMsg;
    }

    /*********************************************************************************
    /* Funcion que verifica si el sieve esta corriendo.
    /* Parametros de entrada:
    /*
    /* Retorna:
    /*  - $result:       El resultado de la consulta realizada
    /*********************************************************************************/
    function verifySieveStatus()
    {
	    $response = array();

	    exec("sudo /sbin/service generic-cloexec cyrus-imapd status",$arrConsole,$flagStatus);
            if($flagStatus != 0){
	        $response['response'] = false;
	        $response['message'] = "Cyrus Imap is down";
            }else{
	        $response['response'] = true;
	        $response['message'] = "Cyrus Imap is up";
	    }
        return $response;
    }
    
    /*********************************************************************************
    /* Funcion para subir un script de vacaciones dado los siguientes parametros:
    /* - $email:        cuenta de email a la cual se subira el script de vacaciones
    /* - $subject:      titulo del mensaje que se envia como respuesta
    /* - $body:         cuerpo o contenido del mensaje que se enviara
    /* - $objAntispam   objeto Antispam
    /* - $spamCapture   boleano que indica si esta activo el eveto de captura de spam
    /*
    /*********************************************************************************/
    function uploadVacationScript($email, $subject, $body, $objAntispam, $spamCapture){

	$SIEVE  = array();
        $SIEVE['HOST'] = "localhost";
        $SIEVE['PORT'] = 4190;
        $SIEVE['USER'] = "";
        $SIEVE['PASS'] = obtenerClaveCyrusAdmin();
        $SIEVE['AUTHTYPE'] = "PLAIN";
        $SIEVE['AUTHUSER'] = "cyrus";
	    $SIEVE['USER'] = $email;

	$existCron = $this->existCronFile();
	if(!$existCron)
	    $this->createCronFile();

        $contentVacations  = $this->getVacationScript($subject, $body);
	$contentSpamFilter = "";

	// si esta activada la captura de spam entonces se deber reemplazar <require "fileinto";> por require ["fileinto","vacation"];
	if($spamCapture){
	    $contentSpamFilter = $objAntispam->getContentScript();
	    $contentSpamFilter = str_replace("require \"fileinto\";", "require [\"fileinto\",\"vacation\"];", $contentSpamFilter);
	}else{
	    $contentSpamFilter =  "require [\"fileinto\",\"vacation\"];";
	}
	$content = $contentSpamFilter."\n".$contentVacations;
        $fileScript = "/tmp/vacations.sieve";
        $fp = fopen($fileScript,'w');
        fwrite($fp,$content);
        fclose($fp);

	exec("echo ".escapeshellarg($SIEVE['PASS'])." | sieveshell ".escapeshellarg("--username=".$SIEVE['USER']).
        " --authname=".$SIEVE['AUTHUSER']." ".$SIEVE['HOST'].":".$SIEVE['PORT'].
        " -e 'put $fileScript'",$flags, $status);
    if($status!=0){
	    $this->errMsg = _tr("Error: Impossible upload ")."vacations.sieve";
	    return false;
	}else{
	    exec("echo ".escapeshellarg($SIEVE['PASS'])." | sieveshell ".escapeshellarg("--username=".$SIEVE['USER']).
            " --authname=".$SIEVE['AUTHUSER']." ".$SIEVE['HOST'].":".$SIEVE['PORT'].
            " -e 'activate vacations.sieve'",$flags, $status);
	    if($status!=0){
		    $this->errMsg = _tr("Error: Impossible activate ")."vacations.sieve";
		    return false;
	    }
	}

        if(is_file($fileScript))
            unlink($fileScript);
	return true;
    }


    /*********************************************************************************
    /* Funcion que verifica si existe el archivo de cron del script de vacaciones:
    /*
    /* Retorna:
    /* - $result:     Un arreglo con los emails con el script de vacaciones activo
    /*********************************************************************************/
    function existCronFile()
    {
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper vacationconfig exist_cron';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }


    /*********************************************************************************
    /* Funcion para crear el cron de eliminacion de script de vacaciones automatica:
    /*
    /* Retorna:
    /* - $result:     Un arreglo con los emails con el script de vacaciones activo
    /*********************************************************************************/
    function createCronFile()
    {
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper vacationconfig create_cron';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    
    /*********************************************************************************
    /* Funcion retorna la plantilla basica del script de vacaciones:
    /* - $subject:      titulo del mensaje que se envia como respuesta
    /* - $body:         cuerpo o contenido del mensaje que se enviara
    /*
    /*********************************************************************************/
    function getVacationScript($subject, $body){
        $script = <<<SCRIPT

 vacation
        # Reply at most once a day to a same sender
        :days 1

        # Currently, encode subject, so you can't use
        # Non-English characters in subject field.
        # The easiest way is let your webmail do that.
        :subject "$subject"

        # Use 'mime' parameter to compose utf-8 message, you can use
        # Non-English characters in mail body.
        :mime
"MIME-Version: 1.0
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: 8bit
$body
";

SCRIPT;
        return $script;
    }


    /*********************************************************************************
    /* Funcion que devuelve todos los correos electronicos con el script de vacaciones
    /* activado:
    /*
    /* Retorna:
    /* - $result:     Un arreglo con los emails con el script de vacaciones activo
    /*********************************************************************************/
    function getEmailsVacationON()
    {
        $query="select vac.id id, acu.username account, acu.id id_user, vac.init_date ini_date, vac.end_date end_date, ".
                    "vac.email_subject subject, vac.email_body body from acl_user acu ".
                    "join vacations vac on acu.id = vac.id_user ".
                        "where vac.vacation = 'yes'";
	    $result=$this->_DB->fetchTable($query,true);
	    if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	    return _tr($result);
    }


    /*********************************************************************************
    /* Funcion que actualiza un mensaje dado los siguientes parametros:
    /* - $subject:    Titulo del mensaje
    /* - $body:       Cuerpo del mensaje
    /* - $status:     el estado de las vacaciones
    /* - $ini_date:   fecha de inicio de vacaciones
    /* - $ini_date:   fecha fin de vacaciones
    /* - $id_user:    id del usuario 
    /*
    /* Retorna:
    /* - $result:     Un booleano con el resultado si se actualizo el registro
    /*********************************************************************************/
    function updateMessageByUser($id_user, $subject, $body, $ini_date, $end_date, $status=null)
    {
	$data = array($subject, $body, $status, $ini_date, $end_date, $id_user);
	$query = "update vacations set email_subject=?,  email_body=? , vacation=?, init_date=?, end_date=?  where id_user=?";
	$result=$this->_DB->genQuery($query,$data);
	if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
	return true;
    }

    
    /*********************************************************************************
    /* Funcion para eliminar un script de vacaciones dado los siguientes parametros:
    /* - $email:        cuenta de email a la cual se subira el script de vacaciones
    /* - $objAntispam   objeto Antispam
    /* - $spamCapture   boleano que indica si esta activo el eveto de captura de spam
    /*
    /*********************************************************************************/
    function deleteVacationScript($email, $objAntispam, $spamCapture){

        $SIEVE  = array();
        $SIEVE['HOST'] = "localhost";
        $SIEVE['PORT'] = 4190;
        $SIEVE['USER'] = "";
        $SIEVE['PASS'] = obtenerClaveCyrusAdmin();
        $SIEVE['AUTHTYPE'] = "PLAIN";
        $SIEVE['AUTHUSER'] = "cyrus";
	    $SIEVE['USER'] = $email;

	$existCron = $this->existCronFile();
	if(!$existCron)
	    $this->createCronFile();

	exec("echo ".escapeshellarg($SIEVE['PASS'])." | sieveshell ".escapeshellarg("--username=".$SIEVE['USER']).
        " --authname=".$SIEVE['AUTHUSER']." ".$SIEVE['HOST'].":".$SIEVE['PORT'].
        " -e 'delete vacations.sieve'",$flags, $status);

	if($status!=0){
	    $this->errMsg = _tr("Error: Impossible remove ")."vacations.sieve";
	    return false;
	}

	if($spamCapture){
	    $contentSpamFilter = $objAntispam->getContentScript();
	    $fileScript = "/tmp/scriptTest.sieve";
	    $fp = fopen($fileScript,'w');
	    fwrite($fp,$contentSpamFilter);
	    fclose($fp);

	    exec("echo ".escapeshellarg($SIEVE['PASS'])." | sieveshell ".escapeshellarg("--username=".$SIEVE['USER']).
            " --authname=".$SIEVE['AUTHUSER']." ".$SIEVE['HOST'].":".$SIEVE['PORT'].
            " -e 'put $fileScript'",$flags, $status);

	    if($status!=0){
		$this->errMsg = _tr("Error: Impossible upload ")."scriptTest.sieve";
		return false;
	    }else{
		exec("echo ".$SIEVE['PASS']." | sieveshell ".escapeshellarg("--username=".$SIEVE['USER']).
            " --authname=".$SIEVE['AUTHUSER']." ".$SIEVE['HOST'].":".$SIEVE['PORT'].
            " -e 'activate scriptTest.sieve'",$flags, $status);
		if($status!=0){
		    $this->errMsg = _tr("Error: Impossible activate ")."scriptTest.sieve";
		    return false;
		}
	    }
	    if(is_file($fileScript))
		unlink($fileScript);
	}
	return true;
    }
}


class paloMyVacation extends paloVacation{
    private $idUser;

    function paloMyVacation($pDB,$idUser){
        parent::__construct($pDB);
        $this->idUser=$idUser;
    }

    function editVacation($arrProp){

        $errorData=array();
        $errorBoolean= false;

        if($arrProp['init_date']==''){
            $errorData['field'][] = "FROM";
            $errorBoolean= true;
        }

        if($arrProp['end_date']==''){
            $errorData['field'][] = "TO";
            $errorBoolean= true;
        }

        if($arrProp['email_subject']==''){
            $errorData['field'][] = "EMAIL_SUBJECT";
            $errorBoolean= true;
        }

        if($arrProp['email_body']==''){
            $errorData['field'][] = "EMAIL_CONTENT";
            $errorBoolean= true;
        }

        $timeInit = strtotime($arrProp['init_date']);     
        $initDate = date('Y-m-d',$timeInit); 

        $timeEnd = strtotime($arrProp['end_date']);
        $endDate = date('Y-m-d',$timeEnd);

        //una vez terminada las vacaciones, no se permitirá activar las vacaciones con la misma fecha
        //se valida que la fecha final debe ser igual o mayor a la actual
        $timestamp0 = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $timestamp1 = mktime(0,0,0,date("m",strtotime($arrProp['end_date'])),date("d",strtotime($arrProp['end_date'])),date("Y",strtotime($arrProp['end_date'])));    
        $timeSince = $timestamp0 - $timestamp1;

        if($timeSince > 0){
            $errorData['field'][] = "TO";
            $errorBoolean= true;        
        }

        if($errorBoolean){
            $errorData['stringError'] = "Some fields are wrong";
            $this->errMsg = $errorData;
            return false;
        }

        $idVacation=$this->getVacationByUser();

        if($idVacation == "default-vacation"){
            $data = array($this->idUser, $arrProp['email_subject'], $arrProp['email_body'], $initDate, $endDate, $arrProp['vacation']);
	        $query = "insert into vacations (id_user, email_subject, email_body, init_date, end_date, vacation) values (?,?,?,?,?,?)";
	        $result=$this->_DB->genQuery($query,$data);
	        if($result==FALSE){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }                     
        }elseif($idVacation === false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
            $data = array($initDate, $endDate, $arrProp['email_subject'], $arrProp['email_body'], $arrProp['vacation'], $idVacation['id']);
	        $query = "update vacations set init_date=?, end_date=?, email_subject=?, email_body=?, vacation=? where id=?";
	        $result=$this->_DB->genQuery($query,$data);
	        if($result==FALSE){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }
        }
        
	    return true;
    }

    function getVacationByUser(){
        $query="SELECT id, init_date, end_date, email_subject, email_body  FROM vacations WHERE id_user=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($this->idUser));
        if($result===false){
            $this->errMsg=_tr("DATABASE ERROR").' '.$this->_DB->errMsg;
            return false;
        }elseif(count($result)==0){
            $this->errMsg=_tr("User does not exist").' '.$this->_DB->errMsg;
            return "default-vacation";
        }else{
            return $result;
        }            
    }
}
?>
