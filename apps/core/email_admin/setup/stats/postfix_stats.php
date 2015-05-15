#!/usr/bin/php
<?php

$elxPath="/usr/share/elastix";
ini_set('include_path',"$elxPath:".ini_get('include_path'));
require_once "libs/paloSantoDB.class.php";
require_once('libs/misc.lib.php');


$arrDBConn=generarDSNSistema("asteriskuser","elxpbx");
global $pDB;
$pDB = new paloDB($arrDBConn); //coneccion a la base usada en el sistema
if($pDB===false){
    wlog(' DATABASE ERROR CONNECTION '.$pDB->errMsg);
}

// Para silenciar avisos de fecha/hora
if (function_exists('date_default_timezone_get')) {
    load_default_timezone();
}

$arrLogs = array(0=> "/var/log/maillog",
                 1=> "/var/log/maillog.1",
                 2=> "/var/log/maillog.2",
                 3=> "/var/log/maillog.3");
$debug=true;
$argError=false;

$date = date("Y/m/d/H");
$date = explode("/",$date);

$argYear=$date[0];
$argMonth=$date[1];
$argDay=$date[2];
$argHour=$date[3];
wlog("The datetime is $argYear/$argMonth/$argDay $argHour" . ":00.\n");

// TODO: A quick check to si if the date is older than the log files. It could save time searching the logs contents one by one.
// TODO: Actually the datetime passed as argument is also being returned in the output. But its value is not accurate. I need to remove this key.

/* ====== MAIN PROGRAM ====== */

$arrStats=array();
$oFile = new SearchLog;
$oFile->setDebug($debug);

// The last stored timestamp.
// I am supposing that the samples will be stored in a database. So this is the last stored timestamp in the DB.
$lastStoredTS = $oFile->convert2ts($argYear, $argMonth, $argDay, $argHour);
$lastStoredTS = $lastStoredTS - 2*60*60; //Se le resta 1 hora para que muestre desde la hora que ingrese

$storeDay = false;
$storeMonth = false;

if(($argDay=="01" || $argDay=="16") && $argHour=="00")
    $storeMonth = true;

if($argHour%6==0)
    $storeDay = true;

// The foreach funcion always starts in the right order? I need it to iterate from index 0 to index n+1
// Otherwise the log files will be analized in the wrong order
foreach($arrLogs as $idFile=>$logFilename) {
    wlog("====================================\nSearching in file " . $logFilename . "\n====================================\n");
    if(file_exists($logFilename)) {
        $arrStats[$idFile] = $oFile->searchFile($logFilename, $lastStoredTS);
    } else {
        wlog("The file " . $arrLogs[$idFile] . " does not exist. Exiting.\n");
    }

    // The searcFile method fills the dateFound and wantedDateStatus variables. If the date is not found in the file
    // it can be a older or a newer date than the dates stored in the file. If the date is older i do nothing to let the
    // foreach to go to the previous maillog file. If the date is older i break because i don't need to analyze more files.
    if(!$oFile->dateFound and $oFile->wantedDateStatus=="older") {
        wlog("The wanted date was not found on this file. It seems to be an older date. Looking in the previous log file.\n");
        // I do nothing 
    } else {
        break;
    }
}

// Up to here i have an array  
wlog("====================================\nPreparing the output\n====================================\n");

$arrFinal = array(); $date="";
foreach($arrStats as $arrFile) {
    if(is_array($arrFile)) {
        foreach($arrFile as $date=>$arrNumConn) {
            if(!isset($arrFinal[$date])) $arrFinal[$date] = $arrNumConn['NumConnections'];
            else $arrFinal[$date] = $arrFinal[$date] + $arrNumConn['NumConnections'];
        }
    }
}

ksort($arrFinal);
$i=0;
foreach($arrFinal as $date=>$numConn) {
    if(preg_match("/^([[:alpha:]]{3})([[:digit:]]{1,2})([[:digit:]]{2})([[:digit:]]{4})$/",$date,$matches) && $i==0){
	$month = $oFile->month2digits($matches[1]);
	$day   = $matches[2];
	$time  = $matches[3].":00:00";
	$year  = $matches[4];
	$unix_time = $oFile->convert2ts($year,$month,$day,$matches[3]);
	if(preg_match("/^[[:digit:]]{1}$/",$month))
	    $month = "0".$month;
	if(preg_match("/^[[:digit:]]{1}$/",$day))
	    $day = "0".$day;
	$date_mail  = $year."-".$month."-".$day." ".$time;
	storeInDatabase($date_mail,$unix_time,$numConn,0);
	$i++;
    }
    wlog("$date  =  $numConn\n");
}

if($storeDay && isset($unix_time)){
    $result = getData6HoursBefore($unix_time);
    $totalDay = 0;
    $i=0;
    $time="";
    $timestamp="";
    foreach($result as $value){
	$totalDay = $totalDay + $value['total'];
	if($i==0){
	    $time = $value['date'];
	    $timestamp = $value['unix_time'];
	}
	$i++;
    }
    storeInDatabase($time,$timestamp,$totalDay,1);
    $timestamp = $timestamp - 10*24*60*60;
    deleteOldData($timestamp,1);
}

if($storeMonth && isset($unix_time)){
    $result = getDataDaysBefore($unix_time);
    $totalMonth = 0;
    $i=0;
    $time="";
    $tiemstamp="";
    foreach($result as $value){
	$totalMonth = $totalMonth + $value['total'];
	if($i==0){
	    $time = $value['date'];
	    $timestamp = $value['unix_time'];
	}
	$i++;
    }
    storeInDatabase($time,$timestamp,$totalMonth,2);
    deleteOldData($timestamp,0);
    $timestamp = $timestamp - 380*24*60*60;
    deleteOldData($timestamp,2);
}

function deleteOldData($unix_time,$type)
{
    global $pDB;
    $query = "delete from email_statistics where unix_time < ? and type=?";
    $result = $pDB->genQuery($query,array($unix_time,$type));
    if($result==FALSE)
        wlog($pDB->errMsg."\n");
}

function getDataDaysBefore($unix_time)
{
    global $pDB;
    $day = date('d',$unix_time);
    if($day=="28")
	$time = $unix_time - 12*24*60*60;
    if($day=="29")
	$time = $unix_time - 13*24*60*60;
    if($day=="30" || $day=="15")
	$time = $unix_time - 14*24*60*60;
    if($day=="31")
	$time = $unix_time - 15*24*60*60;
    $query = "select * from email_statistics where unix_time>=? and type=0 order by unix_time";
    $result = $pDB->fetchTable($query,true,array($time));
    if($result===FALSE){
	wlog($pDB->errMsg."\n");
	return array();
    }
    return $result;
}

function getData6HoursBefore($unix_time)
{
    global $pDB;
    $time = $unix_time - 5*60*60; //A la hora actual se le resta 5 horas para obtener los otros 5 datos
    $query = "select * from email_statistics where unix_time>=? and type=0 order by unix_time";
    $result = $pDB->fetchTable($query,true,array($time));
    if($result===FALSE){
	wlog($pDB->errMsg."\n");
	return array();
    }
    return $result;
}

function storeInDatabase($date,$unix_time,$total,$type)
{
    global $pDB;
    $arrParam = array($date,$unix_time,$total,$type);
    $query = "insert into email_statistics (date,unix_time,total,type) values (?,?,?,?)";
    $result = $pDB->genQuery($query,$arrParam);
    if($result==FALSE)
	wlog($pDB->errMsg."\n");
}

/* ====== THE SEARCHLOG CLASS ====== */

class searchLog {

    var $debug=false;
    var $f;
    var $cursor;
    var $salto;
    var $dateFound=false;
    var $wantedDateStatus="unknown"; 
    var $filesize;
    var $fileMDateYear;
    var $fileMDateMonth;
    var $methodConverges=false;

    function setDebug($debug)
    {
        $this->debug = $debug;
    }

    function searchFile($archivoLog, $lastStoredTS)
    {
        // Reset the dateFound and wantedDateStatus variables
        $this->dateFound=false;
        $this->wantedDateStatus = "unknown"; 

        $arrSalida=array();
        $this->filesize = filesize($archivoLog);
        $this->fileMDateYear  = date('Y', filemtime($archivoLog));
        $this->fileMDateMonth = date('m', filemtime($archivoLog));
        if($this->debug) {
            wlog("The size of the file " . $archivoLog . " is: " . $this->filesize . "\n");
            wlog("The last modification date of the file is " . date('F d Y H:i:s.', filemtime($archivoLog)) . "\n");
        }
        $this->f = fopen($archivoLog, "r");

        // This functions search for the wanted date using a mathematical convergence method instead of parsing the file line by line
        $this->convergeToPosition($lastStoredTS);

        if($this->dateFound==false and $this->cursor>0 and $this->methodConverges==false) {
            $this->wantedDateStatus="newer";
            if($this->debug) wlog("Date not found. The wanted date is NEWER than the ones included in the file $archivoLog\n");
        } else {

            if($this->dateFound==false and $this->cursor>0 and $this->methodConverges==true) {
                wlog("It seems that the date you are looking for is missing in the file. We will start the analysis from " .
                     "the nearest date possible\n");
            }

            if ($this->dateFound==false and $this->cursor<=0) {    
                $this->wantedDateStatus="older";
                if($this->debug) wlog("Date not found. The wanted date is OLDER than the ones included in the file $archivoLog\n");
                if($this->debug) wlog("Anyway, we will return the array with the dates found on this file.\n");
                // Nota, en este caso la primera fecha se debe sumar con la ultima (son la misma) del archivo anterior
            }
            wlog("Analyzing the file...\n");
            $linea = ""; $lastDateTXT=""; $contCon=0; $year="";
            $lastMonth=""; $lastDay=""; $lastHour=""; $currentRecordTS=0; $lastRecordTS=0;
            $arrSalida = array();
            while(!feof($this->f)) {
                $linea=fgets($this->f);;
                if(preg_match("/^([[:alpha:]]{3})[[:space:]]+([[:digit:]]{1,2}) ([[:digit:]]{2})/", $linea, $arrMatches)) {
                    // Si el dia y hora son diferentes al anterior entonces rese
                    if(empty($lastDateTXT)) {
                        $lastDateTXT = $arrMatches[1].$arrMatches[2].$arrMatches[3];
                        $lastMonth = $arrMatches[1]; $lastDay = $arrMatches[2]; $lastHour = $arrMatches[3];
                        $contCon=0;
			$year = $this->calculateYear($this->month2digits($arrMatches[1]),$this->fileMDateYear, $this->fileMDateMonth);
                    } elseif($arrMatches[1].$arrMatches[2].$arrMatches[3]!=$lastDateTXT and !empty($lastDateTXT)) {
                        $this->printProgress();
                        // If the next datetime in the log is newer than the last one, then it is ok
                        $currentRecordTS = $this->convert2ts($this->calculateYear($this->month2digits($arrMatches[1]), 
                                                             $this->fileMDateYear, $this->fileMDateMonth), 
                                                             $arrMatches[1], $arrMatches[2], $arrMatches[3], 0);
                        $lastRecordTS    = $this->convert2ts($this->calculateYear($this->month2digits($lastMonth), 
                                                             $this->fileMDateYear, $this->fileMDateMonth), 
                                                             $lastMonth, $lastDay, $lastHour, 0);
                        if($currentRecordTS>$lastRecordTS) {
                            $arrSalida[$lastDateTXT.$year]["NumConnections"] = $contCon;
                            $arrSalida[$lastDateTXT.$year]["Status"] = "Completed";
                            // We store the date of the current line as the 'last' one in order to compare with 
                            // the next line's date in the next iteraction
                            $lastDateTXT = $arrMatches[1].$arrMatches[2].$arrMatches[3];
                            $lastMonth = $arrMatches[1]; $lastDay = $arrMatches[2]; $lastHour = $arrMatches[3];
                            $contCon=0;
			    $year = $this->calculateYear($this->month2digits($arrMatches[1]),$this->fileMDateYear, $this->fileMDateMonth);
                        } else {
                            wlog("Error parsing the log: A line has a date not newer than the last one. Weird!\n");
                        }
                    } else {
                        if(preg_match("/]: connect from /", $linea)) {
                            $contCon++;
                        }
                    }
                } 
            }
            if(!@is_array($arrSalida[$lastDateTXT.$year])) {
                $arrSalida[$lastDateTXT.$year]["NumConnections"]=$contCon;
                $arrSalida[$lastDateTXT.$year]["Status"]="Last Date in Log File";
            }
        }
        fclose($this->f);
        wlog("\n");
        return $arrSalida;
    }

    function convergeToPosition($lastStoredTS)
    {
        $this->methodConverges = false;
        $nuevoCursor = floor($this->filesize/2);
        $this->cursor = $nuevoCursor; // Esto esta bien?
        $this->salto = $nuevoCursor;
	$currentTS = null;
        wlog("Searching the date position in the file to start analyzing...\n");
        // TODO: Prevent this loop to iteract infinitely. Maybe adding the condition to iteract only the number of bytes of the file
        do {
            // Given the current cursor, the next function seeks the start of the line and place the cursor there
            // This is because the calculated cursor not necessarily is a start of line
            $viejoCursor=$this->cursor;
            $this->cursor=$nuevoCursor;
            $this->seekStartOfLine();
            if($this->debug) wlog("Cursor position=" . $this->cursor . ". Step=" . $this->salto. "\n");
            // The next line checks if the method converge
            if($viejoCursor==$this->cursor) { 
                if($this->debug) wlog("The method converges\n");
                $this->methodConverges=true;
                break;
            }
            $line=fgets($this->f);
            // To reset the possition of the cursor as fgets modify the current possition
            fseek($this->f,$this->cursor);
            if(preg_match("/^([[:alpha:]]{3})[[:space:]]+([[:digit:]]{1,2}) ([[:digit:]]{2}):([[:digit:]]{2})/", $line, $arrMatches)) {
                $currentYear = $this->calculateYear($this->month2digits($arrMatches[1]), $this->fileMDateYear, $this->fileMDateMonth);
                $currentTS=$this->convert2ts($currentYear,$arrMatches[1], $arrMatches[2], $arrMatches[3]);

                if($currentTS>$lastStoredTS) { 
                    $nuevoCursor=$this->retrocederMarca($this->cursor);
                } else if($currentTS<$lastStoredTS) { 
                    $nuevoCursor=$this->adelantarMarca($this->cursor);
                // I found the last stored date. So i will break here. 
                // It does not matter if the cursor is not on the right date. 
                } else if(($currentTS==$lastStoredTS)) { 
                    $this->dateFound = true; 

                    break; 
                }
            } else {
                // A non well formated line... skiping?
            }
        } while($this->cursor>0 and !feof($this->f));

        if($this->cursor>0) {
            // Up to here I am in an older date. Now I will forward the cursor until the right date
            $lastStoredTS=$currentTS;
            while(!feof($this->f)) {
                $line = fgets($this->f);
                if(preg_match("/^([[:alpha:]]{3})[[:space:]]+([[:digit:]]{1,2}) ([[:digit:]]{2}):([[:digit:]]{2})/", $line, $arrMatches)) {
                    $currentYear = $this->calculateYear($this->month2digits($arrMatches[1]), $this->fileMDateYear, $this->fileMDateMonth);
                    $currentTS=$this->convert2ts($currentYear,$arrMatches[1], $arrMatches[2], $arrMatches[3]);
                    if($currentTS!=$lastStoredTS) break;
                }
            }    
            // Rewind the cursor to the last line
            $this->cursor = ftell($this->f);
            $this->cursor = $this->cursor - 2;
            $this->seekStartOfLine();
        }

        wlog("Position set at byte $this->cursor\n");
    }

    function retrocederMarca($cursor) 
    {
        $this->salto = $this->salto/2;
        if($this->salto<=1) $this->salto=1;

        $resultado = $cursor - floor($this->salto);
        if($resultado<=0) return 0;
        else return $resultado;
    }

    function adelantarMarca($cursor) 
    {
        $this->salto = $this->salto/2;
        if($this->salto<=1) $this->salto=1;

        $resultado = $cursor + floor($this->salto);
        if($resultado >= $this->filesize) return $this->filesize;
        else return $resultado;
    }

    function seekStartOfLine()
    {
        fseek($this->f, $this->cursor);
        $currentChar=fgetc($this->f);
        while($this->cursor!=0 and $currentChar!=chr(10)) {
            $this->cursor--;
            fseek($this->f, $this->cursor);
            $currentChar=fgetc($this->f);
        }
        // The next line advance the cursor one possition because the EOL char is in fact in the line before
        if($this->cursor!=0) { $this->cursor++; fseek($this->f, $this->cursor); }
    }

    function convert2ts($year, $month3Chars, $day, $hour, $minute=0)
    {
        if(empty($month3Chars) and empty($day) and empty($hour) and empty($minute)) return 0;

        if($month3Chars>=1 and $month3Chars<=12) {
            $month=$month3Chars;
        } else {
            $month=$this->month2digits($month3Chars);
        }
        return mktime($hour,$minute,0,$month,$day,$year); // Assuming minute 30 or whatever
    }

    function month2digits($month3Chars) 
    { 
        $arrMonth = array("Jan"=>1,"Feb"=>2,"Mar"=>3,"Apr"=>4,"May"=>5,"Jun"=>6,"Jul"=>7,"Aug"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dec"=>12);
        return $arrMonth[$month3Chars]; 
    }

    // The $month and $lastRecord_month arguments must be digits
    // This function assumes a maximum difference of one year. But the log could contain more than
    // one year of records in the same file. This script does not handle this case.
    function calculateYear($month, $lastRecord_year, $lastRecord_month)
    {
        if($month>$lastRecord_month) {
            return $lastRecord_year-1;
        } else {
            return $lastRecord_year;
        } 
    }

    function printProgress()
    {
        wlog("*");
    }
}

function wlog($message){
    $file = fopen("/var/log/elastix/postfix_stats.log","a");
    if($file){
        fwrite($file,$message);
        fclose($file);
    }
}
?>
