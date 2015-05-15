#!/usr/bin/php
<?php

$elxPath="/usr/share/elastix";
ini_set('include_path',"$elxPath:".ini_get('include_path'));

// script que se encarga de eliminar los mensajes viaejos de las carpetas de spam
require_once 'libs/misc.lib.php';
include_once "configs/email.conf.php";
include_once "libs/cyradm.php";

$days = trim($_SERVER['argv'][1]);

if(isset($days) & $days!=""){
    
    // Para silenciar avisos de fecha/hora
    if (function_exists('date_default_timezone_get')) {
        load_default_timezone();
    }

    $today     = date("d-M-Y");
    $sinceDate = date("d-M-Y",strtotime($today." -$days day"));
    
    //realizamos la coneccion con cyrus
    global $CYRUS;
    $cyr_conn = new cyradm;
    $error_msg = "";
    $error = $cyr_conn->imap_login();
    if ($error===FALSE){
        wlog("IMAP login error: $error <br>");
        exit(1);
    }
    
    //verificamos si el antispam esta activado
    exec("/etc/init.d/spamassassin status", $flag, $status);
    if($status == 0){
        //obtenemos las listas de correos
        $accounts=getListMailbox();
        if($accounts!=false){
            foreach($accounts as $email){
                deleteSpamMessages(&$cyr_conn, $email, $sinceDate);
            }
        }
    }else{
        wlog("ERROR: ".$flag[0]."\n");
        exit(1);
    }
    
    $cyr_conn->imap_logout();
}

function deleteSpamMessages(&$cyr_conn, $email, $dateSince)
{
    $seperator  = '/';
    $dataEmail = explode("@",$email);
    $bValido=$cyr_conn->command(". select \"user" . $seperator . $dataEmail[0] . $seperator . "Spam@" . $dataEmail[1] ."\"");
    if(!$bValido)
        wlog("Error selected Spam folder:".$cyr_conn->getMessage()."<br>");
    else{
        $bValido=$cyr_conn->command(". SEARCH NOT SINCE $dateSince"); // busca los email que no empiecen desde la fecha dada
        if(!$bValido)
            wlog("error cannot be added flags Deleted to the messages of Spam folder for $email:".$cyr_conn->getMessage()."<br>");
        else{
            $sal  = explode("SEARCH", $bValido[0]);
            $uids = trim($sal[1]); //ids de mensajes
            if($uids != ""){
                //$bValido=$cyr_conn->command(". store 1:* +flags \Deleted");
                $uids = trim($uids);
                $uids = str_replace(" ", ",",$uids);
                if(strlen($uids)>100){
                    $arrID = explode(",","$uids");
                    $size = count($arrID);
                    $limitID = $arrID[0].":".$arrID[$size-1];
                    $bValido=$cyr_conn->command(". store $limitID +flags \Deleted");
                }else
                    $bValido=$cyr_conn->command(". store $uids +flags \Deleted"); // messages $uids = 1 2 4 5 7 8
                if(!$bValido)
                    wlog("error cannot be deleted the messages of Spam folder for $email:".$cyr_conn->getMessage()."<br>");
                else{
                    $bValido=$cyr_conn->command(". expunge");
                    if(!$bValido)
                        wlog("error cannot be deleted the messages of Spam folder for $email:".$cyr_conn->getMessage()."<br>");
                }
            }
        }
    }
}

function wlog($message){
    file_put_contents("/var/log/elastix/delete_spam.log",$message,FILE_APPEND);
}

function getListMailbox(){
    try{
        $userAccounts=array();
        $regexpUsuario =  '/^[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*@[a-z0-9]+([\._\-]?[a-z0-9]+)*(\.[a-z0-9]{2,6})+$/';
        $arrDBConn=parseDSN(generarDSNSistema("asteriskuser","elxpbx"));
        $conn = new PDO($arrDBConn["dsn"],$arrDBConn["user"],$arrDBConn["passwd"]);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sqlEmailInfo = <<<EMAIL_INFO
SELECT username FROM acl_user  
EMAIL_INFO;
        foreach ($conn->query($sqlEmailInfo) as $row) {
            if(preg_match($regexpUsuario,$row['username'])){
                $userAccounts[]=$row['username'];
            }
        }
        return $userAccounts;
    } catch (PDOException $e) {
        wlog("ERR: failed to read account information - ".$e->getMessage()."\n");
        return false;
    }
}

function parseDSN($dsn){
    //$dsn => databasemotor://username:password@hostspec/database
    //mysql => mysql://username:password@hostspec/database
    //squlite => sqlite:///database
    $database=$username=$password=$hostspec=$dbname=false;
    //get the technology
    if(($pos = strpos($dsn, '://')) !== false) {
        $database = substr($dsn, 0, $pos);
        $dsn = substr($dsn, $pos + 3);
    } else {
        return array("dsn"=>$dsn,"user"=>$username,"passwd"=>$password);
    }
     
    //username y password en caso de haberlos
    if (($at = strrpos($dsn,'@')) !== false) {
        $str = substr($dsn, 0, $at);
        $dsn = substr($dsn, $at + 1);
        if (($pos = strpos($str, ':')) !== false) {
            $username = rawurldecode(substr($str, 0, $pos));
            $password = rawurldecode(substr($str, $pos + 1));
        } else {
            $username = rawurldecode($str);
        }
    }
    
    //hostspec 
    if (strpos($dsn, '/') !== false) {
        list($hostspec, $dbname) = explode('/', $dsn, 2);
    }
        
    if($database=="sqlite" || $database=="sqlite3"){
        $dsn="sqlite:$dbname";
    }elseif($database=="mysql"){
        $dsn="$database:dbname=$dbname;host=$hostspec";
    }
    
    return array("dsn"=>$dsn,"user"=>$username,"passwd"=>$password);
}

?>