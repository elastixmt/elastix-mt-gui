<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.2.0-29                                               |
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
  $Id: index.php,v 1.1.1.1 2012/07/30 rocio mera rmera@palosanto.com Exp $ */
    include_once "libs/paloSantoACL.class.php";
    include_once "libs/paloSantoAsteriskConfig.class.php";
    include_once "libs/paloSantoPBX.class.php";
    
class paloSantoTG extends paloAsteriskDB{
    protected $code;
    protected $domain;

    function paloSantoTG(&$pDB,$domain)
    {
       parent::__construct($pDB);
        
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloSantoTG").$this->errMsg;
            }else{
                $this->code=$result["code"];
            }
        }
    }
    
    function setDomain($domain){
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;
            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloSantoOutboundPBX").$this->errMsg;
            }else{
                $this->code=$result["code"];
            }
        }
    }
    
    function getDomain(){
        return $this->domain;
    }
    
    function validateDomainPBX(){
        //validamos que la instancia de paloDevice que se esta usando haya sido creda correctamente
        if(is_null($this->code) || is_null($this->domain))
            return false;
        return true;
    }

    function getNumTG($domain=null,$name=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(id) from time_group";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($name) && $name!=''){
            $where[]=" UPPER(name) like ?";
            $arrParam[]="%".strtoupper($name)."%";
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
    
        $query="SELECT count(id) from time_group $where";
        $result=$this->_DB->getFirstRowQuery($query,false,$arrParam);
        if($result==false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result[0];
    }

    
    function getTGs($domain=null,$name=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;

        $query="SELECT * from time_group";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($name) && $name!=''){
            $where[]=" UPPER(name) like ?";
            $arrParam[]="%".strtoupper($name)."%";
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
        if(isset($limit) && isset($offset)){
            $query .=" limit ? offset ?";
            $arrParam[]=$limit;
            $arrParam[]=$offset;
        }
                
        $result=$this->_DB->fetchTable($query,true,$arrParam);
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result;
    }

    //debo devolver un arreglo que contengan los parametros del TG
    function getTGById($id){
        global $arrConf;
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = _tr("Invalid Time Group");
            return false;
        }

        $query="SELECT * from time_group where id=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($id,$this->domain));
        
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }elseif(count($result)>0){
            return $result;
        }else
            return false;
    }
    
    private function existTimeGroup($name){
        $query="SELECT 1 from time_group where name=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($name,$this->domain));
        if($result===false || count($result)>0){
            $this->errMsg=$this->_DB->errMsg;
            return true;
        }else
            return false;
    } 
    
    
    function createNewTG($arrProp,$smarty){
        if(!$this->validateDomainPBX()){
            $this->errMsg=_tr("Invalid Organization");
            return false;
        }
        
        //se realiza esta accion primero para poner tener un registro de los datos ingresados por el usuario
        //cosa que estos sean preservados asi falle alguna accion posterior
        $this->tg_parameters($smarty,$arrTG);
    
        $query="INSERT into time_group (name, organization_domain) values(?,?)";
                
        if(empty($arrProp["name"])){
            $this->errMsg = _tr("Field Name can't be empty");
            return false;
        }
        
        if($this->existTimeGroup($arrProp["name"])==true){
            $this->errMsg = _tr("Already exist a Time Group with the same name").$this->errMsg;
            return false;
        }
        
        //creamos el time_group
        $result=$this->executeQuery($query,array($arrProp["name"],$this->domain));
        if($result==false){
            $this->errMsg=$this->errMsg;
            return false;
        }
        
        //creamos las condiciones del mismo
        //obtenemos el id del time_group recien creado
        $query="SELECT id from time_group where name=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($arrProp["name"],$this->domain));
        if($result===false || count($result)==0){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }
        
        if($this->savetgPrameters($result[0],$arrTG)==false){
            $this->errMsg=_tr("Errors had happened to try save group time set")." ".$this->errMsg;
            return false;
        }else
            return true;
    }
        
    private function tg_parameters($smarty,&$arrProp){
        $arrkeys=array_keys($_POST['Sday_week']);
        $arrDayWeek=array();
        $arrDayMonth=array();
        $arrMonth=array();
        $arrTime=array();
        
        $this->getArrayParameters($_POST['Sday_week'],$_POST['Fday_week'],"validateDayWeek",$arrDayWeek,$Sday_w,$Fday_w);
        $this->getArrayParameters($_POST['Sday_month'],$_POST['Fday_month'],"validateDay",$arrDayMonth,$Sday_m,$Fday_m);
        $this->getArrayParameters($_POST['Smonth'],$_POST['Fmonth'],"validateMonth",$arrMonth,$Smonth,$Fmonth);
        
        foreach($_POST['Shour'] as $key => $value){
            $arrTime[$key]="*";
            $shour=$fhour=$cond="";
            if(is_array($value)){
                foreach($value as $skey => $hour){
                    $shour=$this->validateHour($hour);
                    $fhour=$this->validateHour($_POST['Fhour'][$key][$skey]);
                    if($shour!="*" || $fhour!="*"){
                        $smin=$this->validateMinute($_POST['Smin'][$key][$skey]);
                        $fmin=$this->validateMinute($_POST['Fmin'][$key][$skey]);
                        if($shour=="*")
                           $fhour=$shour;
                        if($shour=="*")
                           $shour=$fhour;
                        $Shour[$key][]=$shour;
                        $Fhour[$key][]=$fhour;
                        $Smin[$key][]=$smin;
                        $Fmin[$key][]=$fmin;
                        
                        $smin=($smin=="0")?"00":$smin;
                        $fmin=($fmin=="0")?"00":$fmin;
                        if($fhour!=$shour)
                            $cond .=$shour.":".$smin."-".$fhour.":".$fmin;
                        else
                            $cond .=$shour.":".$smin;
                        $cond .="&";
                    }
                }
                
                if(!isset($Shour[$key])){
                    $Shour[$key][]="*";
                    $Fhour[$key][]="*";
                    $Smin[$key][]="*";
                    $Fmin[$key][]="*";
                }
                
                if($cond!=""){
                   $arrTime[$key]= trim($cond,"&");
                }
            }
        }
        
        $smarty->assign("arrItems",$arrkeys);
        $smarty->assign("SHOUR",$Shour);
        $smarty->assign("FHOUR",$Fhour);
        $smarty->assign("SMIN",$Smin);
        $smarty->assign("FMIN",$Fmin);
        $smarty->assign("SDAY_W",$Sday_w);
        $smarty->assign("FDAY_W",$Fday_w);
        $smarty->assign("SDAY_M",$Sday_m);
        $smarty->assign("FDAY_M",$Fday_m);
        $smarty->assign("SMONTH",$Smonth);
        $smarty->assign("FMONTH",$Fmonth);
        
        $arrProp["day_w"]=$arrDayWeek;
        $arrProp["day_m"]=$arrDayMonth;
        $arrProp["month"]=$arrMonth;
        $arrProp["time"]=$arrTime;
        $arrProp["arrkeys"]=$arrkeys;
    }
    
    private function getArrayParameters($sTime,$fTime,$method,&$arrTime,&$start,&$end){
        if(isset($sTime) && is_array($sTime)){
            foreach($sTime as $key => $value){
                $arrTime[$key]="*";
                $stime=$ftime=$cond="";
                if(is_array($value)){
                    foreach($value as $skey => $time){
                        $stime=$this->$method($time);
                        $ftime=$this->$method($fTime[$key][$skey]);
                        if($stime!="*" || $ftime!="*"){
                            if($ftime=="*")
                                $ftime=$stime;
                            if($stime=="*")
                                $stime=$ftime;
                            $start[$key][]=$stime;
                            $end[$key][]=$ftime;
                            if($ftime!=$stime)
                                $cond .=$stime."-".$ftime;
                            else
                                $cond .=$stime;
                            $cond .="&";
                        }
                    }
                    
                    if(!isset($start[$key])){
                        $start[$key][]="*";
                        $end[$key][]="*";
                    }
                    
                    if($cond!=""){
                        $arrTime[$key]= trim($cond,"&");
                    }
                }
            }
        }
    }
    
    private function savetgPrameters($id_tg,$arrProp){
        $result=false;
        foreach($arrProp["arrkeys"] as $value){
            $query="INSERT into tg_parameters (id_tg,tg_hour,tg_day_w,tg_day_m,tg_month) values(?,?,?,?,?)";
            
            if($arrProp["time"][$value]=="*" && $arrProp["day_w"][$value] =="*" && $arrProp["day_m"][$value]=="*"  && $arrProp["month"][$value]=="*")
                continue;
            
            if($this->duplicateTime(array($id_tg,$arrProp["time"][$value],$arrProp["day_w"][$value],$arrProp["day_m"][$value],$arrProp["month"][$value]))==false){
                $result=$this->executeQuery($query,array($id_tg,$arrProp["time"][$value],$arrProp["day_w"][$value],$arrProp["day_m"][$value],$arrProp["month"][$value]));
                if($result==false)
                    break;
            }
        }
        return $result;
    }
    
    private function duplicateTime($arr){
        $query="SELECT 1 from tg_parameters where id_tg=? and tg_hour=? and tg_day_w=? and tg_day_m=? and tg_month=?";
        $result=$this->_DB->fetchTable($query,true,$arr);
        if($result===false || count($result)>0)  
            return true;
        else
            return false;
    }
    
    private function validateDayWeek($value){
        switch($value){
            case "mon":
                $day="mon";
                break;
            case "tue":
                $day="tue";
                break;
            case "wed":
                $day="wed";
                break;
            case "thu":
                $day="thu";
                break;
            case "fri":
                $day="fri";
                break;
            case "sat":
                $day="sat";
                break;
            case "sun":
                $day="sun";
                break;
            default:
                $day="*";
                break;
        }
        return $day;
    }
    
    private function validateDay($value){
        if(ctype_digit($value) && ($value>=1 && $value<=31)){
            return $value;
        }else
            return "*";
    }
    
    private function validateHour($value){
        if(ctype_digit($value) && ($value>=0 && $value<=24)){
            if($value<10)
                $value="0"+$value;
            return $value;
        }else{
            return "*";
        }
    }
    
    private function validateMinute($value){
        if(ctype_digit($value) && ($value>=0 && $value<=60)){
            if($value<10)
                $value="0"+$value;
            return $value;
        }else{
            return "00";
        }
    }
    
    private function validateMonth($value){
        switch($value){
            case "jan":
                $mon="jan";
                break;
            case "feb":
                $mon="feb";
                break;
            case "mar":
                $mon="mar";
                break;
            case "apr":
                $mon="apr";
                break;
            case "may":
                $mon="may";
                break;
            case "jun":
                $mon="jun";
                break;
            case "jul":
                $mon="jul";
                break;
            case "aug":
                $mon="aug";
                break;
            case "sep":
                $mon="sep";
                break;
            case "oct":
                $mon="oct";
                break;
            case "nov":
                $mon="nov";
                break;
            case "dec":
                $mon="dec";
                break;
            default:
                $mon="*";
                break;
        }
        return $mon;
    }

    function updateTGPBX($arrProp,$smarty){
        //se realiza esta accion primero para poner tener un registro de los datos ingresados por el usuario
        //cosa que estos sean preservados asi falle alguna accion posterior
        $this->tg_parameters($smarty,$arrTG);
        
        $TG=$this->getTGById($arrProp["id"]);
        if($TG==false){
            $this->errMsg=_tr("Time Group doens't exist. ").$this->errMsg;
            return false;
        }
        
        if(empty($arrProp["name"])){
            $this->errMsg = _tr("Field Name can't be empty");
            return false;
        }
       
        if($TG["name"]!=$arrProp["name"]){
            if($this->existTimeGroup($arrProp["name"])==true){
                $this->errMsg = _tr("Already exist a Time Group with the same name").$this->errMsg;
                return false;
            }
        }
        
        $query="update time_group set name=? where id=?";
        if($this->executeQuery($query,array($arrProp["name"],$arrProp["id"]))==false){
            $this->errMsg=_tr("Time group can't be updated. ").$this->errMsg;
            return false;
        }
        
        //borramos las condiciones de tiempo ya existente para de ahi proceder a crear 
        //las nuevas con los datos recien ingresados
        $query="DELETE from tg_parameters where id_tg=?";
        if($this->executeQuery($query,array($arrProp["id"]))==false){
            $this->errMsg=_tr("Timg Group can't be updated. ").$this->errMsg;
            return false;
        }
        
        if($this->savetgPrameters($arrProp["id"],$arrTG)==false){
            $this->errMsg=_tr("Errors had happened to try save group time set")." ".$this->errMsg;
            return false;
        }else
            return true;
    }


    function deleteTG($tg_id){
        $result=$this->getTGById($tg_id);
        if($result==false){
            $this->errMsg=_tr("Time Group doens't exist. ").$this->errMsg;
            return false;
        }
        
        $query="DELETE from tg_parameters where id_tg=?";
        if($this->executeQuery($query,array($tg_id))==false){
            $this->errMsg=_tr("Timg Group can't be deleted. ").$this->errMsg;
            return false;
        }
        
        $query="DELETE from time_group where id=?";
        if($this->executeQuery($query,array($tg_id))){
            return true;
        }else{
            $this->errMsg=_tr("Time Group can't be deleted. ").$this->errMsg;
            return false;
        } 
    }
    
    function getParametersTG($id_tg,$smarty){
        $query="SELECT * from tg_parameters where id_tg=?";
        $result=$this->_DB->fetchTable($query,true,array($id_tg));
        if($result==false){
            $smarty->assign("arrItems",array());
            return;
        }
            
        $arrkeys=array();
        foreach($result as $key => $value){
            $arrkeys[]=$key;
            //hour:min
            if($value["tg_hour"]!="*" && $value["tg_hour"]!=""){
                $tmph=explode("&",$value["tg_hour"]);
                foreach($tmph as $val){
                    $range=explode("-",$val);
                    $hm=explode(":",$range[0]);
                    $Shour[$key][]=$hm[0];
                    $Smin[$key][]=$hm[1];
                    if(count($range)>1){
                        $hm=explode(":",$range[1]);
                        $Fhour[$key][]=$hm[0];
                        $Fmin[$key][]=$hm[1];
                    }else{
                        $Fhour[$key][]=$hm[0];
                        $Fmin[$key][]=$hm[1];
                    }
                }
            }else{
                $Fhour[$key][]=$Shour[$key][]=$Fmin[$key][]=$Smin[$key][]="*";
            }
            //day_of_week    
            if($value["tg_day_w"]!="*" && $value["tg_day_w"]!=""){
                foreach(explode("&",$value["tg_day_w"]) as $val){
                    $range=explode("-",$val);
                    $Sday_w[$key][]=$range[0];
                    if(count($range)>1){
                        $Fday_w[$key][]=$range[1];
                    }else
                        $Fday_w[$key][]=$range[0];
                }
            }else{
                $Fday_w[$key][]=$Sday_w[$key][]="*";
            }
            //day_of_month
            if($value["tg_day_m"]!="*" && $value["tg_day_m"]!=""){
                foreach(explode("&",$value["tg_day_m"]) as $val){
                    $range=explode("-",$val);
                    $Sday_m[$key][]=$range[0];
                    if(count($range)>1){
                        $Fday_m[$key][]=$range[1];
                    }else
                        $Fday_m[$key][]=$range[0];
                }
            }else{
                $Fday_m[$key][]=$Sday_m[$key][]="*";
            }
            //month
            if($value["tg_month"]!="*" && $value["tg_month"]!=""){
                foreach(explode("&",$value["tg_month"]) as $val){
                    $range=explode("-",$val);
                    $Smonth[$key][]=$range[0];
                    if(count($range)>1){
                        $Fmonth[$key][]=$range[1];
                    }else
                        $Fmonth[$key][]=$range[0];
                }
            }else{
                $Fmonth[$key][]=$Smonth[$key][]="*";
            }
        }
        
        $smarty->assign("arrItems",$arrkeys);
        $smarty->assign("SHOUR",$Shour);
        $smarty->assign("FHOUR",$Fhour);
        $smarty->assign("SMIN",$Smin);
        $smarty->assign("FMIN",$Fmin);
        $smarty->assign("SDAY_W",$Sday_w);
        $smarty->assign("FDAY_W",$Fday_w);
        $smarty->assign("SDAY_M",$Sday_m);
        $smarty->assign("FDAY_M",$Fday_m);
        $smarty->assign("SMONTH",$Smonth);
        $smarty->assign("FMONTH",$Fmonth);
    }
}
?>
