<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0                                                  |
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
  $Id: paloSantoEndPoint.class.php,v 1.1 2008/01/15 10:39:57 bmacias Exp $ */

include_once("libs/paloSantoDB.class.php");
if (file_exists("/var/lib/asterisk/agi-bin/phpagi-asmanager.php")) {
require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
}
/* Clase que implementa EndPoint Configuracion */
class paloSantoEndPoint
{
    var $_dsnAsterisk;
    var $_dsnSqlite;
    var $errMsg;
    function paloSantoEndPoint($dsnAsterisk, $dsnSqlite)
    {
        $this->_dsnAsterisk = $dsnAsterisk;
        $this->_dsnSqlite = $dsnSqlite;
    }

    function connectDataBase($engineBase, $nameBase)
    {
        if($engineBase=="mysql")
            $stringConnect = $this->_dsnAsterisk . "/$nameBase";
        else if($engineBase=="sqlite")
            $stringConnect = $this->_dsnSqlite . "/$nameBase.db";
        else{
            $this->errMsg = "Error: String of connection not support.";
            return false;
        }
        $pDB = new paloDB($stringConnect);

        if ($pDB->connStatus) {
            $this->errMsg = $pDB->errMsg;
            // debo llenar alguna variable de error
            return false;
        }
        return $pDB;
    }

    function listEndpointConf() {
        $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
    $sqlPeticion = "select e.id,e.desc_device,e.account,e.mac_adress,e.id_model from endpoint e;";
        $result = $pDB->fetchTable($sqlPeticion,true); //se consulta a la base endpoints
        $pDB->disconnect();
    return $result;
    }

    function listVendor() {
        $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
    $sqlPeticion = "select v.id,v.name vendor ,m.value mac from vendor v inner join mac m on v.id = m.id_vendor;";
        $result = $pDB->fetchTable($sqlPeticion,true); //se consulta a la base endpoints
        $pDB->disconnect(); 
    return $result;
    }

     function deleteEndpointsConf($Mac) {
        $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
        $ok1 = true;
        $ok2 = true;

        //First delete the parameters endpoint
        $sqlPeticion = "delete from parameter where id_endpoint = (select id from endpoint where mac_adress='$Mac');";
        $ok1 = $pDB->genQuery($sqlPeticion); 
        //Second delete the endpoint
        $sqlPeticion = "delete from endpoint  where mac_adress='$Mac';";
        $ok2 = $pDB->genQuery($sqlPeticion);

        $pDB->disconnect(); 
        return ($ok1 && $ok2); //no es tan buena la validacion, ver si se puede mejorar, probabilidad de q ocurra este error es muy baja
    }

    function modelSupportIAX($id_model)
    {
    $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
    $query = "select iax_support from model where id=?";
    $result = $pDB->getFirstRowQuery($query,true,array($id_model));
    if(is_array($result) && count($result)>0){
        if($result['iax_support'] == '1')
        return true;
        else
        return false;
    }
    else
        return null;
    }

    function getEndpointParameters($mac)
    {
    $pDB = $this->connectDataBase("sqlite","endpoint");
    if($pDB==false)
            return false;
    $query = "select * from parameter where id_endpoint like (select id from endpoint where  mac_adress=?)";
    $result = $pDB->fetchTable($query,true,array($mac)); //se consulta a la base endpoints
    return $result;
    }

    function getCountries()
    {
    $pDB = $this->connectDataBase("sqlite","endpoint");
    if($pDB==false)
            return false;
    $query = "select id,country from settings_by_country order by country";
    $result = $pDB->fetchTable($query,true); //se consulta a la base endpoints
    return $result;
    }

    function getToneSet($id)
    {
    $pDB = $this->connectDataBase("sqlite","endpoint");
    if($pDB==false)
            return false;
    $query = "select fxo_fxs_profile,tone_set from settings_by_country where id=?";
    $result = $pDB->getFirstRowQuery($query,true,array($id)); //se consulta a la base endpoints
    return $result;
    }

    function countryExists($id)
    {
    $pDB = $this->connectDataBase("sqlite","endpoint");
    if($pDB==false)
            return false;
    $query = "select count(*) from settings_by_country where id=?";
    $result = $pDB->getFirstRowQuery($query,false,array($id));
    if($result===false){
            $this->errMsg = $pDB->errMsg;
            return false;
        }
    if($result[0] > 0)
        return true;
    else
        return false;
    }

    function getPattonDevices()
    {
    $sComando = '/usr/bin/elastix-helper patton_query';
    $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return array();
        }
    $pattonDevices = array();
    $i=0;
    if(is_array($output) && count($output) > 0){
        foreach($output as $value){
        if(preg_match("/^\[(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\]$/",$value,$matches)){
            $i++;
            $pattonDevices[$i]['ip_adress'] = $matches[1];
            $pattonDevices[$i]['desc_vendor'] = "Patton Electronics Co.";
            $pattonDevices[$i]['name_vendor'] = "Patton";
            $pattonDevices[$i]['id_vendor'] = "";
            $pattonDevices[$i]['id'] = "";
            $pattonDevices[$i]['desc_device'] = "";
            $pattonDevices[$i]['account'] = "";
            $pattonDevices[$i]['configurated'] = "";
        }
        elseif(preg_match("/^type=(.+)$/",$value,$matches))
            $pattonDevices[$i]['model_no'] = $matches[1];
        }
        return $pattonDevices;
    }
    else
        return array();
    }
   
    function saveVegaData($arrData)
    {
        $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
        $query = "select count(*) from endpoint where mac_adress=?";
        $result = $pDB->getFirstRowQuery($query,false,array($arrData["mac"])); //se consulta a la base endpoints
        if($result === false){
            $this->errMsg = $pDB->errMsg;
            return false;
        }
        if($result[0] == 0){
            $query = "insert into endpoint (id_model,mac_adress,id_vendor,edit_date) values ((select id from model where name='Vega'),?,(select id from vendor where name='Sangoma'),datetime('now','localtime'))";
            $result = $pDB->genQuery($query,array($arrData["mac"]));
            if($result == false){
                $this->errMsg = $pDB->errMsg;
                return false;
            }
        }
        foreach($arrData as $name => $value){
            if($name != "mac" && $name != "ip_address"){
                $query = "select count(*) from parameter where name=? and id_endpoint like (select id from endpoint where mac_adress=?)";
                $result = $pDB->getFirstRowQuery($query,false,array($name,$arrData["mac"])); //se consulta a la base endpoints
                if($result === false){
                    $this->errMsg = $pDB->errMsg;
                    return false;
                }
                if($result[0] > 0){
                    $query = "update parameter set value=? where name=? and id_endpoint like (select id from endpoint where mac_adress=?)";
                    $arrParameter = array($value,$name,$arrData["mac"]);
                }
                else{
                    $query = "insert into parameter (id_endpoint,name,value) values ((select id from endpoint where mac_adress=?),?,?)";
                    $arrParameter = array($arrData["mac"],$name,$value);
                }
                $result = $pDB->genQuery($query,$arrParameter);
                if($result == false){
                    $this->errMsg = $pDB->errMsg;
                    return false;
                }
            }
        }
        $extension = "";
        $trunk = "";
        $extensionsData = array("user_name","user","authentication_user");
        $trunksData = array("line","ID","authentication_ID");
        $number = 0;
        foreach($extensionsData as $extData){
            if($number == 0)
                $extension .= " (name like '$extData%'";
            else
                $extension .= " or (name like '$extData%'";
            $number++;
            if($extData == "user")
                $extension .= " and name not like 'user_name%'";
            for($i=0;$i<$arrData["analog_extension_lines"];$i++){
                if($i==0)
                    $extension .= " and name not in ('{$extData}{$i}'";
                else
                    $extension .= ",'{$extData}{$i}'";
                if($i==($arrData["analog_extension_lines"] - 1))
                    $extension .= ")";
            }
            $extension .= ")";
        }
        $number = 0;
        foreach($trunksData as $trunData){
            if($number == 0)
                $trunk .= " (name like '$trunData%'";
            else
                $trunk .= " or (name like '$trunData%'";
            $number++;
            if($trunData == "line")
                $trunk .= " and name<>'lines_sip_port'";
            for($i=0;$i<$arrData["analog_trunk_lines"];$i++){
                if($i==0)
                    $trunk .= " and name not in ('{$trunData}{$i}'";
                else
                    $trunk .= ",'{$trunData}{$i}'";
                if($i==($arrData["analog_trunk_lines"] - 1))
                    $trunk .= ")";
            }
            $trunk .= ")";
        }
        $query = "delete from parameter where id_endpoint like (select id from endpoint where mac_adress=?) and ($extension or $trunk)";
        $result = $pDB->genQuery($query,array($arrData["mac"]));
        if($result == false){
            $this->errMsg = $pDB->errMsg;
            return false;
        }
        return true;
    }


    function savePattonData($arrData)
    {
    $pDB = $this->connectDataBase("sqlite","endpoint");
    if($pDB==false)
            return false;
    $query = "select count(*) from endpoint where mac_adress=?";
    $result = $pDB->getFirstRowQuery($query,false,array($arrData["mac"])); //se consulta a la base endpoints
    if($result === false){
        $this->errMsg = $pDB->errMsg;
        return false;
    }
    if($result[0] == 0){
        $query = "insert into endpoint (id_model,mac_adress,id_vendor,edit_date) values ((select id from model where name='Patton'),?,(select id from vendor where name='Patton'),datetime('now','localtime'))";
        $result = $pDB->genQuery($query,array($arrData["mac"]));
        if($result == false){
        $this->errMsg = $pDB->errMsg;
        return false;
        }
    }
    foreach($arrData as $name => $value){
        if($name != "mac" && $name != "ip_address"){
        $query = "select count(*) from parameter where name=? and id_endpoint like (select id from endpoint where mac_adress=?)";
        $result = $pDB->getFirstRowQuery($query,false,array($name,$arrData["mac"])); //se consulta a la base endpoints
        if($result === false){
            $this->errMsg = $pDB->errMsg;
            return false;
        }
        if($result[0] > 0){
            $query = "update parameter set value=? where name=? and id_endpoint like (select id from endpoint where mac_adress=?)";
            $arrParameter = array($value,$name,$arrData["mac"]);
        }
        else{
            $query = "insert into parameter (id_endpoint,name,value) values ((select id from endpoint where mac_adress=?),?,?)";
            $arrParameter = array($arrData["mac"],$name,$value);
        }
        $result = $pDB->genQuery($query,$arrParameter);
        if($result == false){
            $this->errMsg = $pDB->errMsg;
            return false;
        }
        }
    }
    $extension = "";
    $trunk = "";
    $extensionsData = array("user_name","user","authentication_user");
    $trunksData = array("line","ID","authentication_ID");
    $number = 0;
    foreach($extensionsData as $extData){
        if($number == 0)
        $extension .= " (name like '$extData%'";
        else
        $extension .= " or (name like '$extData%'";
        $number++;
        if($extData == "user")
        $extension .= " and name not like 'user_name%'";
        for($i=0;$i<$arrData["analog_extension_lines"];$i++){
        if($i==0)
            $extension .= " and name not in ('{$extData}{$i}'";
        else
            $extension .= ",'{$extData}{$i}'";
        if($i==($arrData["analog_extension_lines"] - 1))
            $extension .= ")";
        }
        $extension .= ")";
    }
    $number = 0;
    foreach($trunksData as $trunData){
        if($number == 0)
        $trunk .= " (name like '$trunData%'";
        else
        $trunk .= " or (name like '$trunData%'";
        $number++;
        if($trunData == "line")
        $trunk .= " and name<>'lines_sip_port'";
        for($i=0;$i<$arrData["analog_trunk_lines"];$i++){
        if($i==0)
            $trunk .= " and name not in ('{$trunData}{$i}'";
        else
            $trunk .= ",'{$trunData}{$i}'";
        if($i==($arrData["analog_trunk_lines"] - 1))
            $trunk .= ")";
        }
        $trunk .= ")";
    }
    $query = "delete from parameter where id_endpoint like (select id from endpoint where mac_adress=?) and ($extension or $trunk)";
    $result = $pDB->genQuery($query,array($arrData["mac"]));
    if($result == false){
        $this->errMsg = $pDB->errMsg;
        return false;
    }
    return true;
    }

    /**
     * Procedimiento que realiza mapeo de las IPs y MACs de los teléfonos en la
     * red indicada, para preparar una lista con vendedores conocidos.
     * 
     * @param   string  $sNetMask       Especificación de red y máscara: a.b.c.d/ee
     * @param   array   $vendors        Lista de vendedores por prefijo MAC
     * @param   array   $prevEndpoints  Lista previa de endpoints
     * @param   array   $pattonDevices  Lista de dispositivos Patton detectados
     * @param   array   $KeyAsMAC       Indice del arreglo de salida como MAC 
     * 
     * @return  mixed   NULL en error, o lista de extensiones configurables 
     */
    function endpointMap($sNetMask, $vendors, $prevEndpoints, $pattonDevices, $KeyAsMAC=false)
    {
        // Validación de parámetros
        if (!preg_match('|^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/\d{1,2}$|', $sNetMask)) {
            $this->errMsg = '(internal) invalid netmask';
            return NULL;
        }
        if (!is_array($vendors) || count($vendors) < 0) {
        	$this->errMsg = '(internal) invalid vendor list';
            return NULL;
        }
        if (!is_array($prevEndpoints)) {
            $this->errMsg = '(internal) invalid previous list';
            return NULL;
        }
        
        // Organizar vendedores por su MAC
        $vendorByMac = array();
        foreach ($vendors as $vendor) $vendorByMac[$vendor['mac']] = $vendor;
        
        // Organizar endpoints previos por su MAC
        $prevEndpointByMac = array();
        // mac_adress con una sola d
        foreach ($prevEndpoints as $endpoint) $prevEndpointByMac[$endpoint['mac_adress']] = $endpoint;
        
        // Organizar dispositvos Patton por su IP
        $pattonByIp = array();
        // ip_adress con una sola d
        foreach ($pattonDevices as $patton) $pattonByIp[$patton['ip_adress']] = $patton;
        
        // Ejecutar listado de MACs e IPs
        $output = $retval = NULL;
        exec('/usr/bin/elastix-helper listmacip '.$sNetMask, $output, $retval);
        if ($retval != 0) {
            $this->errMsg = 'Failed to map IPs';
            return NULL;
        }
        $listaEndpoints = array();
        foreach ($output as $linea) {
            list($sEndpointMac, $sEndpointIP, $sDescVendor) = explode(' ', $linea, 3);
            $sMacVendor = substr($sEndpointMac, 0, 8);
            
            if (isset($pattonByIp[$sEndpointIP])) {
                // El endpoint listado es un Patton
                $pattonByIp[$sEndpointIP]['mac_adress'] = $sEndpointMac;
                $listaEndpoints[] = $pattonByIp[$sEndpointIP];
            } elseif (isset($vendorByMac[$sMacVendor])) {
                $endpoint = array(
                    'ip_adress'     =>  $sEndpointIP,
                    'mac_adress'    =>  $sEndpointMac,
                    'desc_vendor'   =>  $sDescVendor,
                    'mac_vendor'    =>  $sMacVendor,
                    'name_vendor'   =>  $vendorByMac[$sMacVendor]['vendor'],
                    'id_vendor'     =>  $vendorByMac[$sMacVendor]['id'],
                    'id'            =>  '',
                    'desc_device'   =>  '',
                    'account'       =>  '',
                    'model_no'      =>  '',
                    'configurated'  =>  FALSE,
                );
                if (isset($prevEndpointByMac[$sEndpointMac])) {
                    $endpoint['configurated'] = TRUE;
                    $endpoint['model_no'] = $prevEndpointByMac[$sEndpointMac]['id_model'];
                    $endpoint['desc_device'] = $prevEndpointByMac[$sEndpointMac]['desc_device'];
                    $endpoint['account'] = $prevEndpointByMac[$sEndpointMac]['account'];
                    $endpoint['id'] = $prevEndpointByMac[$sEndpointMac]['id'];
                }
                if($KeyAsMAC==true){
                    $macTMP = strtolower($sEndpointMac);
                    $macTMP = str_replace(":","",$macTMP);
                    $listaEndpoints[$macTMP] = $endpoint;
                }
                else
                    $listaEndpoints[] = $endpoint;
            }
        }

        if (!function_exists('_paloSantoEndPoint_cmp_endpoints')) {
            function _paloSantoEndPoint_cmp_endpoints(&$a, &$b)
            {
                $ip_a = explode('.', $a['ip_adress']);
                $ip_b = explode('.', $b['ip_adress']);
                if ($ip_a > $ip_b) return 1;
                if ($ip_a < $ip_b) return -1;
                return 0;
            }
        }
        uasort($listaEndpoints, '_paloSantoEndPoint_cmp_endpoints');
        
        return $listaEndpoints;
    }

    function getDeviceFreePBX($all=false)
    {
        global $arrLang;

        $pDB = $this->connectDataBase("mysql","asterisk");
        if($pDB==false)
            return false;
    if($all)
        $iax = "OR tech = 'iax2'";
    else
        $iax = "";
        $sqlPeticion = "select id, concat(description,' <',user,'>') label, tech FROM devices WHERE tech = 'sip' $iax ORDER BY id ASC;";
        $result = $pDB->fetchTable($sqlPeticion,true); //se consulta a la base asterisk
        $pDB->disconnect(); 
        $arrDevices = array();
        if(is_array($result) && count($result)>0){
                $arrDevices['unselected'] = "-- {$arrLang['Unselected']} --";
            foreach($result as $key => $device){
                $arrDevices[$device['id']] = strtoupper($device['tech']).": ".$device['label'];
            }
        }
        else{
            $arrDevices['no_device'] = "-- {$arrLang['No Extensions']} --";
        }
    return $arrDevices;
    }

    function getTech($extension)
    {
    $pDB = $this->connectDataBase("mysql","asterisk");
        if($pDB==false)
            return false;
    $query  = "select tech from devices where id=?";
    $result = $pDB->getFirstRowQuery($query,true,array($extension));
    if(isset($result['tech']))
        return $result['tech'];
    else
        return null;
    }

    function getVendor($mac)
    {
    $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
    $query = "select v.id, v.name from vendor v, mac m where m.value=? and v.id=m.id_vendor";
    $result = $pDB->getFirstRowQuery($query,true,array($mac));
    if(is_array($result))
        return $result;
    else
        return array();
    }

    function getVendorByName($vendor)
    {
    $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
    $query = "select * from vendor where name=?";
    $result = $pDB->getFirstRowQuery($query,true,array($vendor));
    if(is_array($result))
        return $result;
    else
        return array();
    }

    function getAllModelsVendor($nameVendor)
    {
        global $arrLang;

        $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
        $sqlPeticion = "select m.id,m.name from vendor v inner join model m on v.id=m.id_vendor where v.name ='$nameVendor' order by m.name;";
        $result = $pDB->fetchTable($sqlPeticion,true); //se consulta a la base endpoints
        $arrModels = array();
        if(is_array($result) && count($result)>0){
            $arrModels['unselected'] = "-- "._tr("Select a model")." --";
            foreach($result as $key => $model)
                $arrModels[$model['id']] = $model['name'];
        }
        else{
            $arrModels['no_model'] = "-- {$arrLang["No Models"]} --";
        }
        $pDB->disconnect(); 
        return $arrModels;
    }
   // Obtener el user y password, dada la mac
    function getPassword($mac){
       global $arrLang;
       $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
        $query  = "select name, value from parameter where id_endpoint=(select id from endpoint where mac_adress='$mac') and (name='telnet_username' OR name='telnet_password')";
        $result = $pDB->fetchTable($query,true); //se consulta a la base endpoints
        if(is_array($result) && count($result)>0){
             $credential['user'] = $result[0]["value"];
             $credential['password'] = $result[1]["value"];
        }

        else{
            $credential['user']="admin";
            $credential['password']="admin";
        }
        return $credential;

    }

    function getModelByVendor($id_vendor, $name_model)
    {
        $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;

        $sqlPeticion = "select m.* from vendor v inner join model m on v.id=m.id_vendor where v.id =? and m.name=?;";
        $result = $pDB->getFirstRowQuery($sqlPeticion,true,array($id_vendor,$name_model)); //se consulta a la base endpoints

        if(is_array($result) && count($result)>0)
            return $result;
        else return false;
    }

    function getModelById($id_model)
    {
        global $arrLang;

        $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
        $sqlPeticion = "select m.name from model m where m.id ='$id_model';";
        $result = $pDB->getFirstRowQuery($sqlPeticion,true); //se consulta a la base endpoints

        if(is_array($result) && count($result)>0)
            return $result['name'];
        else return false;
        $pDB->disconnect();
    }

    function getDeviceFreePBXParameters($id_device, $tech) {
        $pDB = $this->connectDataBase("mysql","asterisk");
        $parameters = array();

        if($pDB==false)
            return false;
    if($tech=='iax2')
        $tech = "iax";
        $sqlPeticion = "select 
                            d.id, 
                            d.description,
                            t.data 
                        from 
                            devices d 
                                inner 
                            join $tech t on d.id = t.id 
                        where 
                            t.keyword = 'secret' and 
                            d.id = '$id_device';";
        $result = $pDB->getFirstRowQuery($sqlPeticion,true); //se consulta a la base endpoints

    if(is_array($result) && count($result)>0){
            $parameters['id_device']     = $result['id'];
            $parameters['desc_device']   = $result['description'];
            $parameters['account_device']   = $result['id'];//aparentemente siempre son iguales
            $parameters['secret_device'] = $result['data'];
    }
        $pDB->disconnect(); 
    return $parameters;
    }

    function createEndpointDB($endpointVars)
    {
        $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
        $sqlPeticion = "select count(*) existe from endpoint where mac_adress ='{$endpointVars['mac_adress']}';";
        $result = $pDB->getFirstRowQuery($sqlPeticion,true); //se consulta a la base endpoints

        if(is_array($result) && count($result)>0 && $result['existe']==1){//Si existe entonces actualizo
           $sqlPeticion = "update endpoint set 
                            id_device   = '{$endpointVars['id_device']}',
                            desc_device = '{$endpointVars['desc_device']}',
                            account     = '{$endpointVars['account']}',
                            secret      = '{$endpointVars['secret']}',
                            id_model    = {$endpointVars['id_model']},
                            mac_adress  = '{$endpointVars['mac_adress']}',
                            id_vendor   = {$endpointVars['id_vendor']},
                            edit_date   = datetime('now','localtime'),
                            comment     = '{$endpointVars['comment']}'
                          where mac_adress = '{$endpointVars['mac_adress']}';";
        }
        else{ // Si no existe entonces lo inserto
            $sqlPeticion = "insert into endpoint(id_device,desc_device,account,secret,id_model,
                            mac_adress,id_vendor,edit_date,comment)
                            values ('{$endpointVars['id_device']}',
                                    '{$endpointVars['desc_device']}',
                                    '{$endpointVars['account']}',
                                    '{$endpointVars['secret']}',
                                    {$endpointVars['id_model']},
                                    '{$endpointVars['mac_adress']}',
                                    {$endpointVars['id_vendor']},
                                    datetime('now','localtime'),
                                    '{$endpointVars['comment']}');";
        }

        //Realizo el query.
        if(!$pDB->genQuery($sqlPeticion)){
            $this->errMsg = $pDB->errMsg;
            $pDB->disconnect();
            return false;
        }else{
            if(isset($endpointVars['arrParameters']) && is_array($endpointVars['arrParameters']) && count($endpointVars['arrParameters'])>0)
                $result = $this->setParameters($endpointVars['arrParameters'], $endpointVars['mac_adress'], $pDB);
        }
        $pDB->disconnect();
        if(isset($result) && !$result) return false;
        return true;
    }

    function setParameters($arrParameters, $mac_adress, $pDB)
    {
        foreach($arrParameters as $key => $value){
            $sqlPeticion = " select count(*) as exist from parameter where name='$key' and id_endpoint = (select id from endpoint where mac_adress = '$mac_adress');";
            $result = $pDB->getFirstRowQuery($sqlPeticion,true); //se consulta a la base

            if(is_array($result) && count($result)>0 && $result['exist']==1){
                $sqlPeticion = "update parameter set 
                                  value   = '$value'
                                  where name='$key' and id_endpoint = (select id from endpoint where mac_adress = '$mac_adress');";
            }else{
                $sqlPeticion = "insert into parameter (name, value, id_endpoint) values 
                                  ('$key', '$value', (select id from endpoint where mac_adress = '$mac_adress'));";
            }

            //Realizo el query.
            if(!$pDB->genQuery($sqlPeticion)){
                $this->errMsg = $pDB->errMsg;
                $pDB->disconnect();
                return false;
            }
        }
        return true;
    }

    /** Funcion que compara si hay incongruencia de informacion entre las bases asterisk(mysql) y endpoint(sqlite)
        Diferencia entre la descripcion y secret de los devices y tambien si en alguna base no existe el device.
    **/
    function compareDevicesAsteriskSqlite($device)
    {
        global $arrLang;
        $report = "";

        //comprobar si existe en base asterisk
    $tech = $this->getTech($device);
        $deviceParametersFreePBX = $this->getDeviceFreePBXParameters($device,$tech);
        if($deviceParametersFreePBX===false)
            return false;
        else if(is_array($deviceParametersFreePBX) && empty($deviceParametersFreePBX))
            return $arrLang["Don't exist in FreePBX extension"]." $device";
        else if(is_array($deviceParametersFreePBX) && count($deviceParametersFreePBX) == 4){
            //ok tengo datos del freePBX acerca del device.entonces continuo ahora con endpoint
            $pDB_sqlite = $this->connectDataBase("sqlite","endpoint");
            if($pDB_sqlite==false)
                    return false;

            $sqlPeticion = "select desc_device,secret from endpoint where id_device ='$device';";
            $result = $pDB_sqlite->getFirstRowQuery($sqlPeticion,true);//se consulta a la base asterisk

            if(is_array($result) && count($result) == 0){//no existe en sqlite
                $pDB_sqlite->disconnect();
                return $arrLang["Don't exist in Endpoint extension"]." $device";
            }
            else if(is_array($result) && count($result) == 2){//si existe en sqlite
                $desc_asterisk   = $deviceParametersFreePBX['desc_device'];
                $secret_asterisk = $deviceParametersFreePBX['secret_device'];
                $desc_endpoint   = $result['desc_device'];
                $secret_endpoint = $result['secret'];

                if($desc_asterisk!=$desc_endpoint)
                    $report .= $arrLang["User Name in Endpoint is"]." $desc_endpoint.";
                if($secret_asterisk!=$secret_endpoint)
                    $report .= "<br />".$arrLang['And secrets no equals in FreePBX and Endpoint'].".";
                $pDB_sqlite->disconnect();
            }
        }
        if($report=="") return false;
        else return $report;
    }

    function getParameters($mac_adress)
    {
        $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;

        $arrParameters = array();
        $sqlPeticion = " select name, value from parameter where id_endpoint = (select id from endpoint where mac_adress = '$mac_adress');";
        $result = $pDB->fetchTable($sqlPeticion,true); //se consulta a la base
        if(is_array($result) && count($result)>0)
        {
            foreach($result as $key => $value)
            {
                $arrParameters["{$value['name']}"] = $value['value'];
            }
        }

        $pDB->disconnect();
        return $arrParameters;
    }

    function getExtension($ip)
    {
    unset($_SESSION['endpoint_configurator']['extensions_registered'][$ip]);
    //Search in sip extensions
        $parameters = array('Command'=>"sip show peers");
        $result = $this->AsteriskManagerAPI("Command",$parameters,true); 
        $data = explode("\n",$result['data']);
        $extension = "";
        foreach($data as $key => $line){
            if(preg_match("/(\d+\/\d+)[[:space:]]*($ip)[[:space:]]+[[:alpha:]]*[[:space:]]*[[:alpha:]]*[[:space:]]*[[:alpha:]]{0,1}[[:space:]]*[[:digit:]]*[[:space:]]*([[:alpha:]]*)/",$line,$match)){
                if($match[3] == "OK"){
                    $tmp = explode("/",$match[1]);
            $_SESSION['endpoint_configurator']['extensions_registered'][$ip][] = "SIP:$tmp[0]";
                    if($extension == "")
                        $extension = $tmp[0];
                    else
                        $extension = "$extension, $tmp[0]";
                }else
                    return $match[3];
            }
        }

    //Search in iax2 extensions
    $parameters = array('Command'=>"iax2 show peers");
        $result = $this->AsteriskManagerAPI("Command",$parameters,true); 
        $data = explode("\n",$result['data']);
        foreach($data as $key => $line){
            if(preg_match("/(\d+)[[:space:]]*($ip)[[:space:]]*\([[:alpha:]]{1}\)[[:space:]]*[[:digit:]]+\.[[:digit:]]+\.[[:digit:]]+\.[[:digit:]]+[[:space:]]*[[:digit:]]+[[:space:]]*([[:alpha:]]*)/",$line,$match)){
                if($match[3] == "OK"){
            $_SESSION['endpoint_configurator']['extensions_registered'][$ip][] = "IAX2:$match[1]";
                    if($extension == "")
                        $extension = $match[1];
                    else
                        $extension = "$extension, $match[1]";
                }else
                    return $match[3];
            }
        }

        if ($extension == "")
            $extension = _tr("Not Registered");
        return $extension;
    }

    function getDescription($vendor)
    {
        $query = "Select description from vendor where name=?";
        $arrParameters = array($vendor);
        $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
        $result = $pDB->getFirstRowQuery($query,true,$arrParameters);
        if(!$result)
            return "";
        return $result['description'];
    }

    function AsteriskManagerAPI($action, $parameters, $return_data=false) 
    {
        global $arrLang;
        $astman_host = "127.0.0.1";
        $astman_user = 'admin';
        $astman_pwrd = obtenerClaveAMIAdmin();

        $astman = new AGI_AsteriskManager();

        if (!$astman->connect("$astman_host", "$astman_user" , "$astman_pwrd")) {
            $this->errMsg = _tr("Error when connecting to Asterisk Manager");
        } else{
            $salida = $astman->send_request($action, $parameters);
            $astman->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                if($return_data) return $salida;
                else return explode("\n", $salida["Response"]);
            }else return false;
        }
        return false;
    }

    function getMac($vendor)
    {
        $query = "Select value from mac where id_vendor=?";
        $arrParameters = array($vendor);
        $pDB = $this->connectDataBase("sqlite","endpoint");
        if($pDB==false)
            return false;
        $result = $pDB->getFirstRowQuery($query,true,$arrParameters);
        if(!$result)
            return "";
        return $result['value'];
    }
}

/**
create table vendor(
    id          integer         primary key,
    name        varchar(255)    not null default '',
    description varchar(255)    not null default '',
    script      text
);

create table model(
    id          integer         primary key,
    name        varchar(255)    not null default '',
    description varchar(255)    not null default '',
    id_vendor   integer         not null,
    foreign key (id_vendor)     references vendor(id)
);

create table mac(
    id          integer         primary key,
    id_vendor   integer         not null,
    value       varchar(8)      not null default '--:--:--',
    description varchar(255)    not null default '',
    foreign key (id_vendor)     references vendor(id)
);

create table endpoint(
    id          integer         primary key,
    id_device   varchar(255)    not null default '',
    desc_device varchar(255)    not null default '',
    account     varchar(255)    not null default '',
    secret      varchar(255)    not null default '',
    id_model    integer         not null,
    mac_adress  varchar(17)     not null default '--:--:--:--:--:--',
    id_vendor   integer         not null,
    edit_date   timestamp       not null,
    comment     varchar(255),
    foreign key (id_model)      references model(id), 
    foreign key (id_vendor)     references vendor(id) 
);

create table parameter(
    id          integer         primary key,
    id_endpoint integer         not null,
    name        varchar(255)    not null default '',
    value       varchar(255)    not null default '',
    foreign key (id_endpoint)   references endpoint(id)
);
**/
?>
