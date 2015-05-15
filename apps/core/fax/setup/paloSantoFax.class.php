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
  $Id: paloSantoFax.class.php,v 1.1.1.1 2007/03/23 00:13:58 elandivar Exp $ */

$elxPath="/usr/share/elastix";
include_once "$elxPath/libs/paloSantoACL.class.php";

class paloFax {

    public $dirIaxmodemConf;
    public $dirHylafaxConf;
    public $rutaDB;
    public $firstPort;
    public $_DB;
    public $errMsg;

    function paloFax(&$pDB)
    {
        global $arrConf;
        
        $this->dirIaxmodemConf = "/etc/iaxmodem";
        $this->dirHylafaxConf  = "/var/spool/hylafax/etc";
        $this->rutaDB = $arrConf['elastix_dsn']['elastix'];
        $this->firstPort=40000;
        
        if (is_object($pDB)) {
            $this->_DB=& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB= new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }
    
    //esta funcion se utiliza para obetener todos los faxes configurados en el sistema
    function getFaxList($arrProp, $offset=null, $limit=null)
    {
        $OFFSET = $LIMIT = "";
        if($offset!=null) $OFFSET = "OFFSET $offset";
        if($limit!=null) $LIMIT = "LIMIT $limit";
        $param=array(); 

        $query = "SELECT * from fax WHERE 1=1 ";
        if(isset($arrProp['id'])){
            $query .=' AND id=?';
            $param[]=$arrProp['id'];
        }
        if(isset($arrProp['organization_domain'])){
            $query .=" AND organization_domain=?";
            $param[]=$arrProp['organization_domain'];
        }
        if(isset($arrProp['dev_id'])){
            $query .=" AND dev_id=?";
            $param[]=$arrProp['dev_id'];
        }
        if(isset($arrProp['exten'])){
            $query .=" AND exten=?";
            $param[]=$arrProp['exten'];
        }
        $query .=" $LIMIT $OFFSET";
        
        $arrReturn = $this->_DB->fetchTable($query, true, $param);
        $arrtmp=$arrReturn;
        if($arrReturn === FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
            return $arrReturn;
        }
    }
    
    function getTotalFax($arrProp){
        $param=array();

        $query = "SELECT count(id) from fax WHERE 1=1 ";
        if(isset($arrProp['id'])){
            $query .=' AND id=?';
            $param[]=$arrProp['id'];
        }
        if(isset($arrProp['organization_domain'])){
            $query .=" AND organization_domain=?";
            $param[]=$arrProp['organization_domain'];
        }
        if(isset($arrProp['dev_id'])){
            $query .=" AND dev_id=?";
            $param[]=$arrProp['dev_id'];
        }
        if(isset($arrProp['exten'])){
            $query .=" AND exten=?";
            $param[]=$arrProp['exten'];
        }
        
        $arrReturn = $this->_DB->fetchTable($query, false, $param);
        $arrtmp=$arrReturn;
        if($arrReturn === FALSE) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else {
            return $arrReturn[0];
        }
    }
    
    function getDomainOrganization($id)
    {
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = "Organization ID is not numeric";
            return false;
        }

        $query = "SELECT domain FROM organization WHERE id=?;";

        $result=$this->_DB->getFirstRowQuery($query, true, array($id));

        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }elseif(count($result)==0){
            $this->errMsg = "Organization does not exist";
            return false;
        }else{
            return $result['domain'];
        }
    }
      
    function createFaxToUser($arrProp)
    {
        require_once 'libs/paloSantoPBX.class.php';
        $tech='iax2';
        $pACL=new paloACL($this->_DB);
        // 1) Averiguar el numero de dispositivo que se puede usar
        if(!isset($arrProp['devId'])){
            $devId = $this->getNewDevID();
            if($devId==false){
                $this->errMsg=_tr("Error to get Fax Device Identifier");
                return false;
            }
        }else
            $devId = $arrProp['devId'];
            
        if(!isset($arrProp['port'])){
            $port = $this->getNextAvailablePort();
            if($port==false){
                $this->errMsg=_tr("Error to get Port for Fax");
                return false;
            }
        }else
            $port = $arrProp['port'];
        
        // 2) obtenemos los datos dle usuario para el cual se esta creando el fax
        //    comprobando de que este realmente exista
        if(isset($arrProp['idUser'])){
            if(empty($arrProp['idUser'])){
                $this->errMsg=_tr("Invalid User");
                return false;
            }
        }else{
            $this->errMsg=_tr("Invalid User");
            return false;
        }
        
        $arrUser=$pACL->getUsers2($arrProp['idUser']);
        if($arrUser===false){
            $this->errMsg=_tr("An error has occured when retrieved user data.");
            return false;
        }elseif(count($arrUser)==0){
            $this->errMsg=_tr("User does not exist.");
            return false;
        }
        $user=$arrUser[0]; 
        //cuando de crea un fax para un usario el nombre del peer usado para el fax 
        //es igual code_username. Donde username es el useraname sin la parte del @dominio
        $username=strstr($user['username'], '@', true);
        
        $domain=$this->getDomainOrganization($user["id_organization"]);
        
        // 3) debemos crear el peer para el fax y ademas comprabar que la extension pasada como parametrono se este usando
        //    para los faxes siempre se usa tecnologia iax
        $pDevice=new paloDevice($domain,$tech,$this->_DB);
        if(!$pDevice->validatePaloDevice()){
            $this->errMsg=$pDevice->errMsg;
            return false;
        }
        
        $orgCode=$pDevice->getCode();
        $extension=$user["fax_extension"];
        $device="{$username}FX_{$orgCode}";
        
        if($pDevice->existDevice($extension,$device,$tech)==true){
            $this->errMsg="Error Fax Number. ".$pDevice->errMsg;
            return false;
        }
        
        $clid_name=isset($arrProp['clid_name'])?$arrProp['clid_name']:$extension;
        if(!preg_match("/^[[:alnum:]_[:space:]-]+$/",$clid_name)){
            $clid_name=$extension;
        }
        $clid_number=isset($arrProp['clid_number'])?$arrProp['clid_number']:$extension;
        if(!preg_match("/^[[:alnum:]_[:space:]-]+$/",$clid_number)){
            $clid_number=$extension;
        }
        
        //creamos el peer
        $arrPeer=$arrProp;
        $arrPeer["name"]="{$username}FX";
        $arrPeer["defaultip"]="127.0.0.1";
        $arrPeer['secret']= $user["md5_password"];
        $arrPeer["fullname"]=$clid_name;
        $arrPeer["cid_number"]=$clid_number;
        $arrPeer["port"]=$port;
        $arrPeer['dial'] = strtoupper($tech)."/".$device;
        $arrPeer['organization_domain']=$domain;
        $pDevice->tecnologia->setGroupProp($arrPeer,$domain);
        if(empty($pDevice->tecnologia->context)){
            $pDevice->tecnologia->context='from-internal';
        }
        $arrPeer['context']=$pDevice->tecnologia->context;
        if($pDevice->tecnologia->insertDB()==false){
            $this->errMsg="Error setting parameter $type device ".$pDevice->tecnologia->errMsg;
            return false;
        }
        
        $RT=15;
        if(isset($ringTime)){
            if(preg_match("/^[[:digit:]]+$/",$arrProp['ringTime']) && ($arrProp['ringTime']>0 && $arrProp['ringTime']<60))
                $RT=$arrProp['ringTime'];
        }
            
        // 3) creamos el registro en la tabla fax
        if(!$this->insertFaxDB($domain,$arrPeer['context'],$extension,$tech,$arrPeer['dial'],$device,$RT,$clid_name,$clid_number,$arrProp['area_code'],$arrProp['country_code'],$port,$devId,$arrProp['fax_content'],$arrProp['fax_subject'],$user['username'])){
            return false;
        }

        // 4) Añadir el fax a los archivos de configuración
        if($this->addFaxConfiguration($port,$devId,$arrProp['country_code'],$arrProp['area_code'],$clid_name,$clid_number,$device,$user['md5_password'],$user['username']))
            return true;
        else{
            $this->errMsg=_tr("Error to create fax")." ".$this->errMsg;
            return false;
        }
    }
        
    private function insertFaxDB($organization_domain,$context,$exten,$tech,$dial,$device,$rt,$clid_name,$clid_number,$area_code,$country_code,$port,$dev_id,$fax_content,$fax_subject,$notify_email){
        $query="INSERT INTO fax (organization_domain, context, exten, tech, dial, device, rt, clid_name, clid_number, area_code, country_code, port, dev_id, fax_content, fax_subject, notify_email) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        if(!$this->_DB->genQuery($query,array($organization_domain,$context,$exten,$tech,$dial,$device,$rt,$clid_name,$clid_number,$area_code,$country_code,$port,$dev_id,$fax_content,$fax_subject,$notify_email))){
            $this->errMsg=_tr("DATABASE ERROR");
            return false;
        }
        return true;
    }
    
    /**
     * Esta opcion sirve para mandar a escribir los archvos de configuracion de un fax ya existente
     */
    function createFaxFileConfig($devId,$domain=null){
        //obtenemos los datos del fax desde la base
        if(empty($devId)){
            $this->errMsg=_tr("Invalid Fax Modem");
            return false;
        }
        
        $param['dev_id']=$devId;
        if(isset($domain)){
            $param['organization_domain']=$domain;
        }
        $arrFax=$this->getFaxList($param);
        if($arrFax==false){
            $this->errMsg=_tr("Fax Modem does not exist or DATABASE ERROR");
            return false;
        }
        
        //obtenemos la clave del dispositivo
        $device=$this->_DB->getFirstRowQuery("SELECT secret FROM iax WHERE name=?",true,array($arrFax['device']));
        if($device==false){
            $this->errMsg=_tr("IAX Peer for FAX does not exist or DATABASE ERROR");
            return false;
        }
        
        if($this->addFaxConfiguration($arrFax['port'],$arrFax['dev_id'],$arrFax['country_code'],$arrFax['area_code'],$arrFax['clid_name'],$arrFax['clid_number'],$arrFax['device'],$device['secret'],$arrFax['notify_email']))
            return true;
        else{
            $this->errMsg="Error to write config files for Fax modem $devId. ".$this->errMsg;
            return false;
        }
    }
   
    function editFaxToUser($arrProp){
        require_once 'libs/paloSantoPBX.class.php';
        $tech='iax2';
        $pACL=new paloACL($this->_DB);
        
        // 1) obtenemos los datos dle usuario para el cual se esta editando el fax
        //    comprobando de que este realmente exista
        if(isset($arrProp['idUser'])){
            if(empty($arrProp['idUser'])){
                $this->errMsg=_tr("Invalid User");
                return false;
            }
        }else{
            $this->errMsg=_tr("Invalid User");
            return false;
        }
        $arrUser=$pACL->getUsers2($arrProp['idUser']);
        if($arrUser===false){
            $this->errMsg=_tr("An error has occured when retrieved user data.");
            return false;
        }elseif(count($arrUser)==0){
            $this->errMsg=_tr("User does not exist.");
            return false;
        }
        $user=$arrUser[0]; 
        
        $domain=$this->getDomainOrganization($user["id_organization"]);
        
        //obtenemos el fax del usuario
        //si esta seteado el parametro oldFaxExten entonces significa que le usuario quiere cambiar de 
        //el patron de marcado del fax asociado a el
        if(isset($arrProp['oldFaxExten'])){
            $param['exten']=$arrProp['oldFaxExten'];
        }else{
            $param['exten']=$user['fax_extension'];
        }
        $param['organization_domain']=$domain;
        
        $arrFax=$this->getFaxList($param);
        if($arrFax==false){
            $this->errMsg=($arrFax===false)?'Error to retrieved fax from given user':'User does not have a fax';
            return false;
        }
        $fax=$arrFax[0];
        $devId=$fax['dev_id'];
        $port=$fax['port'];
        
        $clid_name=isset($arrProp['clid_name'])?$arrProp['clid_name']:$fax['clid_name'];
        if(!preg_match("/^[[:alnum:]_[:space:]-]+$/",$clid_name)){
            $clid_name=$extension;
        }
        $clid_number=isset($arrProp['clid_number'])?$arrProp['clid_number']:$fax['clid_number'];
        if(!preg_match("/^[[:alnum:]_[:space:]-]+$/",$clid_number)){
            $clid_number=$extension;
        }
        
        $pIax=new paloIax($this->_DB);
        $arrPeer=$arrProp;
        $arrPeer['fullname']=$clid_name;
        $arrPeer['cid_number']=$clid_number;
        $arrPeer['secret']= $user["md5_password"];
        $arrPeer['name']=$fax['device'];
        $arrPeer['organization_domain']=$fax['organization_domain'];
        if($pIax->updateParameters($arrPeer)==false){
            $this->errMsg="Error setting parameter ".$result["tech"]." device ".$pIax->errMsg;
            return false;
        }  
        
        $fax_content=(isset($arrProp['fax_content']))?$arrProp['fax_content']:$fax['fax_content'];
        $fax_subject=(isset($arrProp['fax_subject']))?$arrProp['fax_subject']:$fax['fax_subject'];
        
        //actualizamos los datos en la tabla fax
        if(!$this->updateFaxDB($user['fax_extension'],$clid_name,$clid_number,$arrProp['area_code'],$arrProp['country_code'],$fax_content,$fax_subject,$fax['id'],$fax['organization_domain'])){
            $this->errMsg=_tr("Error to save fax in database");
            return false;
        }
        
        return $this->editFaxConfiguration($port,$devId,$arrProp['country_code'],$arrProp['area_code'],$clid_name,$clid_number,$fax['device'],$user['md5_password'],0);
    }
    
    private function updateFaxDB($exten,$clid_name,$clid_number,$area_code,$country_code,$fax_content,$fax_subject,$id,$organization_domain){
        $query="UPDATE fax SET exten=?, clid_name=?, clid_number=?, area_code=?, country_code=?, fax_content=?, fax_subject=? where id=? and organization_domain=?";
        $result=$this->_DB->genQuery($query,array($exten,$clid_name,$clid_number,$area_code,$country_code,$fax_content,$fax_subject,$id,$organization_domain));
        if($result==false){
            $this->errMsg=$this->_DB->errMsg;
            print_r($this->_DB->errMsg);
        }
        return $result;
    }
    
    /**
     * Esta opcion sirve para mandar a editar los archivos de configuracion de un fax existente
     */
    function editFaxFileConfig($devId,$country_code,$area_code,$clid_name,$clid_number,$secret,$email,$domain=null){
        //obtenemos los datos del fax desde la base
        if(empty($devId)){
            $this->errMsg=_tr("Invalid Fax Modem");
            return false;
        }
        
        $param['dev_id']=$devId;
        if(isset($domain)){
            $param['organization_domain']=$domain;
        }
        $arrFax=$this->getFaxList($param);
        if($arrFax==false){
            $this->errMsg=_tr("Fax Modem does not exist or DATABASE ERROR");
            return false;
        }
                
        if($this->editFaxConfiguration($arrFax['port'],$devId,$country_code,$area_code,
        $clid_name,$clid_number,$arrFax['device'],$secret,$email)){
            return true;
        }else{
            $this->errMsg="Error to write config files for Fax modem $devId. ".$this->errMsg;
            return false;
        }
    }
    
    function deleteFaxByUser($idUser)
    {
        $pACL=new paloACL($this->_DB);
        // 1) obtenemos los datos dle usuario para el cual se esta creando el fax
        //    comprobando de que este realmente exista
        if(empty($idUser)){
            $this->errMsg=_tr("Invalid User");
            return false;
        }
        $arrUser=$pACL->getUsers2($idUser);
        if($arrUser===false){
            $this->errMsg=_tr("An error has occured when retrieved user data.");
            return false;
        }elseif(count($arrUser)==0){
            $this->errMsg=_tr("User does not exist.");
            return false;
        }
        $user=$arrUser[0]; 
        
        $domain=$this->getDomainOrganization($user["id_organization"]);
        //obtenemos el fax del usuario
        $arrFax=$this->getFaxList(array('exten'=>$user['fax_extension'],'organization_domain'=>$domain));
        if($arrFax==false){
            $this->errMsg=($arrFax===false)?'Error to retrieved fax from given user':'User does not have a fax';
            return false;
        }
        
        $fax=$arrFax[0];
        $devId=$fax['dev_id'];
        $device=$fax['device'];
        
        //borramos el fax de la tabla fax
        $query="DELETE FROM fax WHERE id=?";
        if(!$this->_DB->genQuery($query,array($fax['id']))){
            $this->errMsg=_tr("Error to delete fax from database");
            return false;
        }
        
        //borramos el peer
        $query="DELETE FROM iax WHERE name=? and organization_domain=?";
        if(!$this->_DB->genQuery($query,array($device,$domain))){
            $this->errMsg=_tr("Error to delete fax peer");
            return false;
        }
        
        return $this->deleteFaxConfiguration($devId);
    }
       
    private function getNewDevID()
    {
        $chars = "abcdefghijkmnpqrstuvwxyz23456789";
        $existDevId=false;
        do{
            srand((double)microtime()*1000000);
            $pass="";
            // Genero los 10 caracteres mas
            while (strlen($pass) < 3) {
                    $num = rand() % 33;
                    $tmp = substr($chars, $num, 1);
                    $pass .= $tmp;
            }
        $existDevId = false; //$this->existsDevId($pass);
        }while ($existDevId);

        return $pass;
    }

    function existsDevId($devId){
        $query="Select dev_id from fax";
        $result=$this->_DB->getFirstRowQuery($query,false,array($devId));
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return true;
        }if($result[0]==1){
            return true;
        }else{
            //comprobamos que no exista en el archivo /etc/init/elastix_fax.conf
            foreach (file('/etc/init/elastix_fax.conf') as $sLinea) {
                //env ACTIVE_IAXFAX="ttyIAXkca ttyIAXxxw ttyIAXk8v"
                $cadena='/^env ACTIVE_IAXFAX=".'.$devId.'."$';
                if((preg_match("$cadena", $sLinea))){
                    return true;
                }
            }
        }
        return false;
    }


    private function _getConfigFiles($folder, $filePrefix)
    {
        $arrReg    = array();
        $arrSalida = array();
        $pattern   = "^" . str_replace(".", "\.", $filePrefix) . "([[:alnum:]]+)";
    
        // TODO: Falta revisar si tengo permisos para revisar este directorio
    
        if($handle = opendir($folder)) {
            while (false !== ($file = readdir($handle))) {
                if(preg_match("/^(iaxmodem-cfg\.ttyIAX([[:alnum:]]+))/", $file, $arrReg)) {
                    $arrSalida[$arrReg[1]] = $arrReg[2];
                }
            }
            return $arrSalida;
        } else {
            return false;
        }
    }

    // TODO: Por ahora busco siempre el puerto mayor pero tambien tengo que
    //       buscar si existen huecos.
    function getNextAvailablePort()
    {
        $arrPorts=array();

        // Tengo que abrir todos los archivos de configuracion de iaxmodem y
        // hacer una lista de todos los puertos asignados.

        if($handle = opendir($this->dirIaxmodemConf)) {
            while (false !== ($file = readdir($handle))) {
                if(preg_match("/^iaxmodem-cfg\.ttyIAX([[:alnum:]]+)/", $file)) {
                    // Abro el archivo $file
                    if($fh=@fopen("$this->dirIaxmodemConf/$file", "r")) {
                        while($linea=fgets($fh, 10240)) {
                            if(preg_match("/^port[[:space:]]+([[:digit:]]+)/", $linea, $arrReg)) {
                                $arrPorts[] = $arrReg[1];
                            }
                        }
                        fclose($fh);
                    }
                }
            }

            //- Hasta este punto ya he obtenido una lista de puertos usados
            //- y se encuentran almacenados en el arreglo $arrPorts

            if(is_array($arrPorts) and count($arrPorts)>0) {
                // Encuentro el puerto mayor            
                sort($arrPorts);
                $maxPuerto=array_pop($arrPorts);
                if($maxPuerto>=$this->firstPort) {
                    $puertoDisponible=$maxPuerto+1;
                } else {
                    $puertoDisponible=$this->firstPort;
                }
            } else {
                $puertoDisponible=$this->firstPort;
            }

            return $puertoDisponible;
        } else {
            $this->errMsg = _tr("Don't exist directory iaxmodem");
            return false;
        }
    }

    /*
    function getFaxStatus()
    {
        $arrStatus = array();
        exec("/usr/bin/faxstat", $arrOutCmd);

        foreach($arrOutCmd as $linea) {
            if(preg_match("/^Modem (ttyIAX[[:alnum:]]{1,3})/", $linea, $arrReg)) {
                list($modem, $status) = explode(":", $linea);
                $arrStatus[$arrReg[1]] = $status; 
            }
        }

        return $arrStatus;
    }*/
    
    /**
     * Procedimiento para reportar el estado completo de la cola de faxes.
     * 
     * @return  mixed   Arreglo con el siguiente formato:
     *  array(
     *      'modems' => array(
     *          'ttyIAX1' => 'Running and idle',
     *          'ttyIAX2' => 'Running and idle',
     *      ),
     *      'jobs'  =>  array(
     *          ...
     *      ),     
     * 
     *  )
     */
    function getFaxStatus()
    {
        /*
        [root@elx2 ~]# faxstat -s -d
        HylaFAX scheduler on localhost: Running
        Modem ttyIAX1 (): Running and idle
        Modem ttyIAX2 (): Running and idle
        
        JID  Pri S  Owner Number       Pages Dials     TTS Status
        28   125 S asteri 1099          0:1   2:12   17:27 Busy signal detected
         */
        $status = array('modems' => array(), 'jobs' => array());
        $regexpModem = '/^Modem (ttyIAX[[:alnum:]]+).*?:\s*(.*)/';
        $regexpJob = '/^(\d+)\s+(\d+)\s+(\w+)\s+(\S+)\s+(\S+)\s+(\d+):(\d+)\s+(\d+):(\d+)\s*(\d+:\d+)?\s*(.*)/';    
        $output = $retval = NULL;
        exec('/usr/bin/faxstat -sdl', $output, $retval);
        foreach ($output as $s) {
            $regs = NULL;
            if (preg_match($regexpModem, $s, $regs)) {
                $status['modems'][$regs[1]] = $regs[2];
            } elseif (preg_match($regexpJob, $s, $regs)) {
                $status['jobs'][(int)$regs[1]] = array(
                    'jobid'         =>  $regs[1],
                    'priority'      =>  $regs[2],
                    'state'         =>  $regs[3],
                    'owner'         =>  $regs[4],
                    'outnum'        =>  $regs[5],
                    'sentpages'     =>  $regs[6],
                    'totalpages'    =>  $regs[7],
                    'retries'       =>  $regs[8],
                    'totalretries'  =>  $regs[9],
                    'timetosend'    =>  $regs[10],
                    'status'        =>  $regs[11],
                );
            }
        }
        ksort($status['jobs']);
        return $status;
    }

    function getFaxStatusByModem($devID)
    {
        /*
        [root@elx2 ~]# faxstat -s -d
        HylaFAX scheduler on localhost: Running
        Modem ttyIAX1 (): Running and idle
        Modem ttyIAX2 (): Running and idle
        
        JID  Pri S  Owner Number       Pages Dials     TTS Status
        28   125 S asteri 1099          0:1   2:12   17:27 Busy signal detected
         */
        $status = array('modems' => array(), 'jobs' => array());
        $regexpModem = '/^Modem (ttyIAX'.$devID.').*?:\s*(.*)/';
        $regexpJob = '/^(\d+)\s+(\d+)\s+(\w+)\s+(\S+)\s+(\S+)\s+(\d+):(\d+)\s+(\d+):(\d+)\s*(\d+:\d+)?\s*(.*)/';    
        $output = $retval = NULL;
        exec('/usr/bin/faxstat -sdl', $output, $retval);
        foreach ($output as $s) {
            $regs = NULL;
            if (preg_match($regexpModem, $s, $regs)) {
                $status['modems'][$regs[1]] = $regs[2];
            }
        }
        return $status;
    }

    //Obtener estado en el momento de enviar Fax
    function getSendStatus($destine)
    {
        $arrStatus = array();
        $status = array();
        $cont = 0;
        
        exec("/usr/bin/faxstat -s", $arrOutCmd);

        foreach($arrOutCmd as $linea) {
                if($linea==""||(preg_match("/^Modem/", $linea, $arrReg))||(preg_match("/^HylaFAX/", $linea, $arrReg))||(preg_match("/^JID/", $linea, $arrReg))) {
                }else{
                        $tmpstatus = explode(" ",$linea);
                        $arrDestine = array_values(array_diff($tmpstatus, array('')));
                        if($arrDestine[4]==$destine){
                        $status["dial"][] = $arrDestine[6];
                        $status["jid"][] = $arrDestine[0];
                        $status["status"][]=$arrDestine[8]." ".$arrDestine[9]." ".$arrDestine[10];
            }

            }
            }

        return $status;
    }
    
    //Obtener El estado de un fax dado el jid
    function getStateFax($jid)
    {
        $arrStatus = array();
        $status = array();
        $cont = 0;

        exec("/usr/bin/faxstat -sdl output", $arrOutCmd);

        foreach($arrOutCmd as $linea) {
                if($linea==""||(preg_match("/^Modem/", $linea, $arrReg))||(preg_match("/^HylaFAX/", $linea, $arrReg))||(preg_match("/^JID/", $linea, $arrReg))) {
                }else{
                        $tmpstatus = explode(" ",$linea);
                        $arrDestine = array_values(array_diff($tmpstatus, array('')));
                        if($arrDestine[0]==$jid){
                        $status["state"][]=$arrDestine;
                        }

            }
            }

        return $status;
    }
    //Obtener el estado de todos los faxes enviados
    function setFaxMsg()
    {
        $arrStatus = array();
        $status = array();
        $cont = 0;

        exec("/usr/bin/faxstat -d", $arrOutCmd);

        foreach($arrOutCmd as $linea) {
                if($linea==""||(preg_match("/^Modem/", $linea, $arrReg))||(preg_match("/^HylaFAX/", $linea, $arrReg))||(preg_match("/^JID/", $linea, $arrReg))) {
                }else{
                        $tmpstatus = explode(" ",$linea);
                        $arrDestine = array_values(array_diff($tmpstatus, array('')));
                        $id = $arrDestine[0];
                        // if($arrDestine[0]==$jid){
                        $status["state"][$id]=$arrDestine;
                    // }

            }
            }
        //$status["jid"][]=$jid;
        return $status;
    }

    function getConfigurationSendingFaxMail($id_user)
    {
        $arrReturn["fax_subject"]='';
        $arrReturn["fax_content"]='';
        $arrayProp = array("fax_subject","fax_content");
        foreach($arrayProp as $key){
            $valor=$pACL->getUserProp($id_user,$key);
            if($valor!==false){
                $arrReturn[$key]=$valor;
            }
        }
        return $arrReturn;
    }
    
    function getConfigurationSendingFaxMailOrg($idOrg){
        $query="SELECT property,value FROM organization_properties 
                    WHERE category='fax' AND id_organization=? AND property IN 
                     ('fax_content','fax_subject','fax_remitente','fax_remite')";
        $arrReturn = $this->_DB->fetchTable($query,true,array($idOrg));
        if($arrReturn===false){
            $this->errMsg=_tr("DATABASE ERROR");
            return false;
        }else{
            $configs['fax_content']='';
            $configs['fax_subject']='';
            $configs['fax_remitente']='';
            $configs['fax_remite']='';
            foreach($arrReturn as $value){
                $configs[$value['property']]=$value['value'];
            }
            return $configs;
        }
    }

    //se actualiza los campos en user_properties con key fax_subject y fax_content pertenecientes
    //a la categoria fax_content
    function setConfigurationSendingFaxMail($id_user, $subject, $content)
    {
        $bExito = false;
        $pACL = new paloACL($this->_DB);
        $arrayProp = array("fax_subject"=>$subject,"fax_content"=>$content);
        $arrProp=array_diff($arrayProp,array(''));
        foreach($arrayProp as $key => $value){
            $bExito = $pACL->setUserProp($id,$key,$value,"fax");
            if($bExito===false)
            {
                break;
            }
        }
        return $bExito; 
    }
    
    function setConfigurationSendingFaxMailOrg($idOrg, $remite, $remitente, $subject, $content)
    {
        require_once "/usr/share/elastix/libs/paloSantoOrganization.class.php";
        $bExito = false;
        $pORG = new paloSantoOrganization($this->_DB);
        $arrayProp = array("fax_remite"=>$remite, "fax_remitente"=>$remitente,   
                            "fax_subject"=>$subject,"fax_content"=>$content);
        $arrProp=array_diff($arrayProp,array(''));
        foreach($arrayProp as $key => $value){
            $bExito = $pORG->setOrganizationProp($idOrg,$key,$value,"fax");
            if($bExito===false)
            {
                break;
            }
        }
        return $bExito; 
    }

    /**
    * Procedimiento que llama al ayudante faxconfig para que modifique la
    * información de faxes virtuales creando uno nuevo con los datos dados
    * 
    * @return bool VERDADERO en caso de éxito, FALSO en error
    */
    private function addFaxConfiguration($nextPort,$devId,$country_code,$area_code,
        $clid_name,$clid_number,$peername,$secret,$email)
    {
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper faxconfig add'.
            ' '.escapeshellarg($devId).
            ' '.escapeshellarg($nextPort).
            ' '.escapeshellarg($country_code).
            ' '.escapeshellarg($area_code).
            ' '.escapeshellarg($clid_number).
            ' '.escapeshellarg($peername).
            ' '.escapeshellarg($secret).
            ' '.escapeshellarg($email).
            ' '.escapeshellarg($clid_name).
            ' 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    /**
    * Procedimiento que llama al ayudante faxconfig para que modifique la
    * información de faxes virtuales creando uno nuevo con los datos dados
    *
    * @return bool VERDADERO en caso de éxito, FALSO en error
    */
    private function editFaxConfiguration($nextPort,$devId,$country_code,$area_code,
        $clid_name,$clid_number,$peername,$secret,$email)
    {
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper faxconfig edit'.
            ' '.escapeshellarg($devId).
            ' '.escapeshellarg($nextPort).
            ' '.escapeshellarg($country_code).
            ' '.escapeshellarg($area_code).
            ' '.escapeshellarg($clid_number).
            ' '.escapeshellarg($peername).
            ' '.escapeshellarg($secret).
            ' '.escapeshellarg($email).
            ' '.escapeshellarg($clid_name).
            ' 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    /**
    * Procedimiento que llama al ayudante faxconfig para que modifique la
    * información de faxes virtuales para borrar un fax dado su dev_id
    *
    * @return bool VERDADERO en caso de éxito, FALSO en error
    */
    function deleteFaxConfiguration($dev_id)
    {
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper faxconfig delete '.escapeshellarg($dev_id).'  2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    function restartService(){
        $sComando ='/usr/bin/elastix-helper faxconfig restartService  2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    // esta funcion es utilizada para escribir los archivos
    // /etc/init/elastix_fax.config y /var/spool/hylafax/etc/FaxDispatch
    function writeFilesFax(){
        $sComando ='/usr/bin/elastix-helper faxconfig rewriteFileFax 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }
}
?>
