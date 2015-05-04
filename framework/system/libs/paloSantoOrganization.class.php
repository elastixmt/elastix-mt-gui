<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.2.0-29                                             |
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
  $Id: paloSantoOrganization.class.php,v 1.1 2012-02-07 11:02:13 Rocio Mera rmera@palosanto.com Exp $ */

$elxPath="/usr/share/elastix";
include_once "$elxPath/libs/paloSantoEmail.class.php";
include_once "$elxPath/libs/paloSantoACL.class.php";
include_once "$elxPath/libs/paloSantoFax.class.php";
include_once "$elxPath/libs/paloSantoAsteriskConfig.class.php";
include_once "$elxPath/libs/paloSantoPBX.class.php";


class paloSantoOrganization{
    var $_DB;
    var $errMsg;

    function paloSantoOrganization(&$pDB)
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
    
    /**
     * This function return the number the organizations that exist filtering by the given params
     * @param array =>
     * @return mixed => false in case of errors
     *                  integer => number of organizations
     */
    function getNumOrganization($arrProp){
        $arrWhere=array();
        $arrParam=array();
        //la organizacion por default de elastix no se contabiliza
        $query="SELECT count(id) FROM organization WHERE id!=1";
        
        if(!empty($arrProp['state']) && $arrProp['state']!='all'){
            $arrWhere[]=" state=? ";
            $arrParam[]=$arrProp['state'];
        }
        if(!empty($arrProp['name'])){
            $arrWhere[]=" UPPER(name) like ? ";
            $arrParam[]="%".strtoupper($arrProp['name'])."%";
        }
        if(!empty($arrProp['domain'])){
            $arrWhere[]=" domain like ? ";
            $arrParam[]="%{$arrProp['domain']}%";
        }
        if(!empty($arrProp['id'])){
            $arrWhere[]=" id=? ";
            $arrParam[]=$arrProp['id'];
        }
        
        if(count($arrWhere)>0){
            $query .=" AND ".implode(" AND ",$arrWhere);
        }
        
        $result=$this->_DB->getFirstRowQuery($query, false, $arrParam);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result[0];
    }
    
    /**
     * This function return a list of organizations that exist filtering by the given params
     * @param array =>
     * @return mixed => false in case of errors
     *                  array => list of organizations
     */
    function getOrganization($arrProp){
        $arrWhere=array();
        $arrParam=array();
        $query="SELECT * FROM organization WHERE id!=1";
        if(!empty($arrProp['state']) && $arrProp['state']!='all'){
            $arrWhere[]=" state=? ";
            $arrParam[]=$arrProp['state'];
        }
        if(!empty($arrProp['name'])){
            $arrWhere[]=" UPPER(name) like ? ";
            $arrParam[]="%".strtoupper($arrProp['name'])."%";
        }
        if(!empty($arrProp['domain'])){
            $arrWhere[]=" domain like ? ";
            $arrParam[]="%{$arrProp['domain']}%";
        }
        if(!empty($arrProp['id'])){
            $arrWhere[]=" id=? ";
            $arrParam[]=$arrProp['id'];
        }
        
        if(count($arrWhere)>0){
            $query .=" AND ".implode(" AND ",$arrWhere);
        }
        
        if(isset($arrProp['limit']) && isset($arrProp['offset'])){
            $query .=" limit ? offset ?";
            $arrParam[]=$arrProp['limit'];
            $arrParam[]=$arrProp['offset'];
        }
        
        $result=$this->_DB->fetchTable($query, true, $arrParam);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else
            return $result;
    }


    function getNumUserByOrganization($id)
    {
        $query="SELECT COUNT(u.id) FROM acl_user u inner join acl_group g on u.id_group = g.id where g.id_organization=?;";
        $result=$this->_DB->getFirstRowQuery($query, false, array($id));

        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
            return $result[0];
        }
    }

    function getUsersByOrganization($id)
    {
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = "Organization ID is not numeric";
            return false;
        }

        $query = "SELECT u.id, u.username, u.name, u.md5_password, u.id_group, u.extension, u.fax_extension, u.picture FROM acl_user u inner join acl_group g on u.id_group = g.id where g.id_organization=?";
        $result=$this->_DB->fetchTable($query, true, array($id));

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result;
    }

    function getOrganizationById($id)
    {
		if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = "Organization ID is not numeric";
			return false;
        }

        $query = "SELECT * FROM organization WHERE id=?;";

        $result=$this->_DB->getFirstRowQuery($query, true, array($id));

        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result;
    }
    
    function getOrganizationByName($name)
    {
        $query = "SELECT * FROM organization WHERE name=?;";
        $result=$this->_DB->getFirstRowQuery($query, true, array($name));
        //triple igual problema de conneccion o de sintaxis, falso booleano
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result;
    }

    function getOrganizationByDomain_Name($domain_name)
    {
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain_name)){
            $this->errMsg = _tr("Invalid domain format");
            return false;
        }
        
        $query = "SELECT * FROM organization WHERE domain=?;";
        $result=$this->_DB->getFirstRowQuery($query, true, array($domain_name));
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result;
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
        }
        return $result;
    }

    //recibe como parametros el id de la organizacion y el nombre de la propiedad que se desea obtener
    function getOrganizationProp($id,$key)
    {
        $query = "SELECT value FROM organization_properties WHERE id_organization=? and property=?";
        $result=$this->_DB->getFirstRowQuery($query, false, array($id,$key));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result[0];
    }

	function getOrganizationPropByCategory($id,$category)
    {
        $query = "SELECT property,value FROM organization_properties WHERE id_organization=? and category=?";
        $result=$this->_DB->fetchTable($query, true, array($id,$category));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result;
    }

    /**
      *  Procedimiento para setear una propiedad de una organizacion, dado el id de la organizacion,
      *  el nombre de la propiedad y el valor de la propiedad
      *  Si la propiedad ya existe actualiza el valor, caso contrario crea el nuevo registro
      *  @param integer $id de la organizacion a la que se le quiere setear la propiedad
      *  @param string $key nombre de la propiedad
      *  @param string $value valor que tomarà la propiedad
      *  @return boolean verdadera si se ejecuta con existo la accion, falso caso contrario
    */
    function setOrganizationProp($id,$key,$value,$category=""){
        $bQuery = "select 1 FROM organization_properties WHERE id_organization=? AND property=?";
        $bResult=$this->_DB->getFirstRowQuery($bQuery,false,array($id,$key));
        if($bResult===false){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }else{
            if(count($bResult)==0){
                $query="INSERT INTO organization_properties VALUES(?,?,?,?)";
                $arrParams=array($id,$key,$value,$category);
            }else{
                if($bResult[0]=="1"){
                    $query="UPDATE organization_properties SET value=? where id_organization=? and property=?";
                    $arrParams=array($value,$id,$key);
                }
            }
            $result=$this->_DB->genQuery($query, $arrParams);
            if($result==false){
                $this->errMsg = $this->_DB->errMsg;
                return false;
            }else
                return true;
        }
    }

    //esta funcion se usa para setear las propiedades de una organizacion por default que pertenecen a la categoria
    //system y fax. Los valores por default son tomados de los valores configurados en la organizacion principal
    //principal
    private function setDefaultOrganizationProp($idOrganization){
        $Exito=false;
        if (is_null($idOrganization) || !preg_match('/^[[:digit:]]+$/', "$idOrganization")) {
            $this->errMsg = "Invalid ID Organization";
        }
        $query="INSERT INTO organization_properties (id_organization,property,value,category) 
                    SELECT ?,property,value,category FROM organization_properties 
                        WHERE id_organization=? and category IN ('system','fax')";
        $result=$this->_DB->genQuery($query,array($idOrganization,'1'));
        if($result==false){
            return false;
        }
        return true;
    }

    private function getNewPBXCode($domain)
    {
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg = _tr("Invalid domain format");
            return false;
        }
        
        $code=str_replace(array("-","."),"",$domain);
        
        $existCode = $this->existPBXCode($code);
        
        if($existCode){
            $this->errMsg = _tr("New domain ($domain) is similar a $existCode, please use another domain name.");
            return false;
        }
        
        return $code;
    }
    
    private function existPBXCode($org_code){
        $query="select domain from organizacion where code=?";
        $result=$this->_DB->getFirstRowQuery($query, false, array($org_code));
        if($result==false){
            return false;
        }else{
            return $result[0];
        }
    }
    
    
    private function getNewIDCode()
    {
        $chars = "abcdefghijkmnpqrstuvwxyz23456789";
        $existCode=false;
        do{
            srand((double)microtime()*1000000);
            $code="";
            // Genero la clave
            while (strlen($code) < 20) {
                    $num = rand() % 33;
                    $tmp = substr($chars, $num, 1);
                    $code .= $tmp;
            }
            $existCode = $this->existIDCode($code);
        }while ($existCode);
        return $code;
    }
    
    private function existIDCode($idcode){
        $query="select 1 from org_hystory_register where org_idcode=?";
        $result=$this->_DB->getFirstRowQuery($query, false, array($idcode));
        if($result==false){
            return false;
        }else{
            return true;
        }
    }


    function getOrganizationCode($domain)
    {
        $query="select code from organization where domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($domain));
        if($result==FALSE)
            $this->errMsg = $this->_DB->errMsg;
        return $result;
    }
    
    
    function getIdOrgByDomain($domain){
        $query="SELECT id from organization where domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($domain));
        if($result===false)
            $this->errMsg=$this->_DB->errMsg;
        elseif(count($result)==0 || empty($result["code"]))
            $this->errMsg=_tr("Organization doesn't exist");
        return $result;
    }
    
    //funcion que crea una entrada en la tabla org_hystory_register haciendo constancia
    //de la creacion o eliminacion de una organizacion dentro del sistema
    //esta tabla solo es escrita dos veces
    //  - al momento de creacion de la organizacion
    //  - al momento que la organizacion es borrada del sistema
    //action string ( create , delete)
    private function orgHistoryRegister($action, $idcode){
        if(empty($idcode)){
            $this->errMsg=_tr("Invalid idcode");
            return false;
        }
            
        //compatible con DATETIME MySQL format
        $date=date("Y-m-d H:i:s");
        
        if($action=="create"){
            $selq="SELECT code,domain from organization where idcode=?";
            $res=$this->_DB->getFirstRowQuery($selq,true,array($idcode));
            if($res==false){
                $this->errMsg=("Invalid idcode at moment to register Organizaion in the system");
                return false;
            }
            $query="INSERT INTO org_history_register (org_domain,org_code,org_idcode,create_date) values(?,?,?,?)";
            $param=array($res["domain"],$res["code"],$idcode,$date);
        }elseif($action=="delete"){
            $query="UPDATE org_history_register SET delete_date=? where org_idcode=?";
            $param=array($date,$idcode);
        }else{
            $this->errMsg=_tr("Invalid action at moment to register Organizaion in the system");
            return false;
        }
        
        $result=$this->_DB->genQuery($query,$param);
        if($result==false){
            $this->errMsg=_tr("Problem had happened to try register the Organization. ").$this->_DB->errMsg;
            return false;
        }else
            return true;
    }
    
    //registra los eventos dentro la organizacion relacionado con la creacion, suspencion del servicio
    //reactivacion del servicio y eliminacion de la organizacion
    function registerEvent($event,$idcode){
        //por ahora los eventos soportados son create,suspend,unsuspend,delete
        if(!($event=="create" || $event=="suspend" || $event=="unsuspend" || $event=="terminate" || $event=="delete")){
            $this->errMsg=_tr("Invalid event");
            return false;
        }
        $date=date("Y-m-d H:i:s");
        $query="INSERT INTO org_history_events (event,org_idcode,event_date) values(?,?,?)";
        $param=array($event,$idcode,$date);
        $result=$this->_DB->genQuery($query,$param);
        if($result==false){
            $this->errMsg=_tr("Problem had happened to try register event in Organization. ").$this->_DB->errMsg;
            return false;
        }else
            return true;
    }
    
    /**
    * Funcion que retorna el estado de una organizacion dado sus id
    * @param $idorg => idOrg
    * @return $orgState => (id => id, state => state , since => since)
    */
    function getOrganizationState($idorg){
        $query="SELECT idcode,state from organization where id=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($idorg));
        if($result==false){
            $this->errMsg=($result===false)?$this->_DB-errMsg:_tr("Organization doesn't exist");
            return $result;
        }
    
        $query="SELECT max(event_date) from org_history_events where org_idcode=?";
        $event=$this->_DB->getFirstRowQuery($query,false,array($result["idcode"]));
        if($event==false){
            $this->errMsg=($event===false)?$this->_DB-errMsg:_tr("Organization doesn't exist");
            return $event;
        }
        
        $orgState=array("id"=>$idorg,"state"=>$result["state"],"since"=>$event[0]);
        return $orgState; 
    }
    
    /**
    * Funcion que retorna el estado de todas las organizaciones
    * @return $orgState => (id => id, state => state , since => since)
    */
    function getbunchOrganizationState($arrIds=null){
        $where="";
        if(is_array($arrIds)){
            $q=substr(str_repeat("?,",count($arrIds)),0,-1);
            $where="where id in ($q)";
        }
    
        $query="SELECT id,idcode,state from organization $where";
        $result=$this->_DB->fetchTable($query,true,$arrIds);
        if($result==false){
            $this->errMsg=($result===false)?$this->_DB-errMsg:_tr("Organizations don't exist");
            return $result;
        }
    
        $orgState=array();
        foreach($result as $x => $value){
            $query="SELECT max(event_date) from org_history_events where org_idcode=?";
            $event=$this->_DB->getFirstRowQuery($query,false,array($value["idcode"]));
            if($event===false){
                $this->errMsg=$this->_DB->errMsg;
                return false;
            }elseif(!empty($event[0]))
                $orgState[$x]=array("id"=>$value["id"],"state"=>$value["state"],"since"=>$event[0]);
        }
        
        return $orgState; 
    }
    
    
    /**
    * Funcion que cambia el estado de un (unas) organizacion dado
    * su id(s) dentro del servidor
    * en el estado suspendido los miembros de la organizacion no son capaces
    * de loguearse dentro del servidor elastix, ademas de esto no son
    * capaces de recibir ni realizar llamadas
    * @param $org => array(idOrg1,idOrg) -> id de las organizaciones cuyo estado sera cambiado
    * @param $state => srting -> estado que tomara la organizacion (suspend,unsuspend,terminate) 
    */
    function changeStateOrganization($arrOrg,$state){
        if(!is_array($arrOrg) || count($arrOrg)==0){
            $this->errMsg=_tr("Invalid Organization(s)");
            return false;
        }
        
        if(!($state=="suspend" || $state=="unsuspend" || $state=="terminate")){
            $this->errMsg=_tr("Invalid Organization State");
            return false;
        }
        
        $file=tempnam("/tmp","orgToChange");
        
        //escribimos un archivo que en contiene el id de las organizaciones que deseamos 
        //cambiar de estado, un id por linea
        $validOrg=array();
        foreach($arrOrg as $ids){
            if(preg_match("/^[0-9]+$/",$ids) && $ids!="1"){
                $validOrg[]=$ids."\n";
            }
        }
        
        if(count($validOrg)==0){
            $this->errMsg=_tr("Invalid Organization(s)");
            return false;
        }
        
        if(file_put_contents("$file",$validOrg)===false){
            $this->errMsg=_tr("Couldn't be written file $file");
            return false;
        }
        
        $sComando = '/usr/bin/elastix-helper asteriskconfig changeOrgsState '.
            escapeshellarg($file).' '.escapeshellarg($state).' 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0){
            $this->errMsg .=implode('',$output);
            return false;
        }else
            return true;
    }


    private function assignResource($idOrganization){
        $rInsert=true;
        $recurso=array();
        $query="INSERT INTO organization_resource (id_organization, id_resource) ".
                    "SELECT ?,re.id FROM acl_resource re WHERE re.organization_access='yes'";
        $result=$this->_DB->genQuery($query,array($idOrganization));
        if($result==false){
            $this->errMsg="An error has occurred trying to assign resources to the organization. ".$this->_DB->errMsg;
            return false;
        }else{
            //creamos los grupos de la organizacion y asignamos los permisos por default de estos grupos
            if($this->createAllGroupOrganization($idOrganization)){
                return true;
            }else
                return false;
        }
    }

    private function createAllGroupOrganization($idOrganization){
        $gExito = false;
        $pACL = new paloACL($this->_DB);

        //creamos los grupos 
        $query="INSERT INTO acl_group (description,name,id_organization) ".
                "SELECT description,name,? FROM acl_group WHERE id_organization=1 AND name IN ('administrator', 'supervisor', 'end_user')";
        $exito=$this->_DB->genQuery($query,array($idOrganization));
        if($exito==false){
            $this->errMsg=_tr("An error has ocurred trying to create organizaion's group");
            return false;
        }
        
        //obtenemos los grupos recien insertados a la organizacion
        $grpOrga=$pACL->getGroups(null,$idOrganization);
        if($grpOrga==false){
            $this->errMsg=_tr("An error has ocurred trying to create organizaion's group");
            return false;
        }
        
        //asignamos los recursos a los grupos recien creados
        //la asignacion de recursos se obtiene de la asignacion que existe a los grupos 
        // 'administrator', 'supervisor', 'end_user' de la organizacion por default
        // que tiene id 1. 
        //Los grupos antes mencionados no deberian ser borrados del sistema
        $query="INSERT INTO group_resource_action (id_group,id_resource_action) " .
                    "SELECT ?,gract.id_resource_action FROM ".
                        "(SELECT or1.id_resource FROM organization_resource or1 
                            WHERE or1.id_organization=?) as or_re ".
                    "JOIN ".
                        "(SELECT gr.id_resource_action,ract.id_resource FROM resource_action ract 
                            JOIN group_resource_action gr ON ract.id=gr.id_resource_action 
                            JOIN acl_group g ON g.id=gr.id_group 
                                WHERE g.name=? AND g.id_organization=1) as gract ".
                    "ON or_re.id_resource=gract.id_resource";
        foreach($grpOrga as $value){
            //$value[0]=id
            //$value[1]=name
            $result=$this->_DB->genQuery($query,array($value[0],$idOrganization,$value[1]));
            if($result==false){
                $this->errMsg = _tr("An error has ocurred trying to assign group resources");
                return false;
            }
        }
        return true;
    }
    

    function createOrganization($name,$domain,$country,$city,$address,$country_code,$area_code, $quota, $email_contact,$max_num_user,$max_num_exten,$max_num_queues,$admin_password)
    {
        global $arrConf;
        $pEmail=new paloEmail($this->_DB);
        $flag=false;
        $error_domain=$error="";
        $address=isset($address)? $address : "";
        //contrumios la nueva entidad
        //antes que todo debemos validar que no exista el dominio que queremos crear en el sistema
        $resOrgz=$this->getOrganizationByDomain_Name($domain);
        if(array($resOrgz) && count($resOrgz)==0){
            $this->_DB->beginTransaction();
            //obtenemos el pbxcode de la organizacion que sera usado como unico identificador dentro de asterisk
            //se valida que el dominio de la organizacion tenga un formato valido dentro de la funcion getNewPBXCode
            $pbxcode = $domain;
            /*$pbxcode=$this->getNewPBXCode($domain);
            if(!$pbxcode){
                // El error fue escrito dentro de la función getNewPBXCode
                return false;
            }*/
            
            //obtenemos el idcode de la organizacion. Este es unico en el sistema y no puede existir o haber 
            //existido otra organizacion dentro del sistema con el mismo codgo
            $idcode=$this->getNewIDCode();

            //creamos la organizacion dentro del sistema
            if (!$this->_DB->genQuery(
                'INSERT INTO organization (name,domain,code,idcode,country,city,address,email_contact,state) '.
                'VALUES (?,?,?,?,?,?,?,?,?)',
                array($name, $domain, $pbxcode, $idcode, $country, $city, $address, $email_contact, 'active'))) {
                $this->_DB->rollBack();
                $this->errMsg = $this->_DB->errMsg;
            } elseif (!$this->_DB->genQuery(
                'REPLACE INTO kamailio.domain (domain, last_modified) VALUES (?, NOW())',
                array($domain))) {
                $this->_DB->rollBack();
                $this->errMsg = $this->_DB->errMsg;
            }else{
                if(!$this->orgHistoryRegister("create",$idcode))
                    return false;
                if(!$this->registerEvent("create",$idcode))
                    return false;
                //obtenemos la organizacion recien creada
                $resultOrgz=$this->getOrganizationByDomain_Name($domain);
                //seteamos los valores de organization_properties por default tomados de la organizacion 1
                $proExito=$this->setDefaultOrganizationProp($resultOrgz['id']);
                //seteamos las demas propiedades de la organization
                $cExito=$this->setOrganizationProp($resultOrgz['id'],"country_code",$country_code,"fax");
                $aExito=$this->setOrganizationProp($resultOrgz['id'],"area_code",$area_code,"fax");
                $eExito=$this->setOrganizationProp($resultOrgz['id'],"email_quota",$quota,"email");
                $cExito=$this->setOrganizationProp($resultOrgz['id'],"max_num_user",$max_num_user,"limit");
                $aExito=$this->setOrganizationProp($resultOrgz['id'],"max_num_exten",$max_num_exten,"limit");
                $eExito=$this->setOrganizationProp($resultOrgz['id'],"max_num_queues",$max_num_queues,"limit");
                
                if($proExito && $cExito && $aExito && $eExito){
                    //se asignan los recursos a la organizacion
                    //se crean los grupos
                    //se asignan los recursos a los grupos
                    $gExito=$this->assignResource($resultOrgz['id']);
                    if($gExito==false){
                        $error=$this->errMsg;
                        $this->_DB->rollBack();
                    }else{
                        //procedo a crear el plan de marcado para la organizacion
                        $pAstConf=new paloSantoASteriskConfig($this->_DB);
                        //procedo a setear las configuaraciones generales del plan de marcado por cada organizacion
                        if($pAstConf->createOrganizationAsterisk($domain,$country)){
                            //procedo a crear el nuevo dominio
                            if(!($this->createDomain($domain))){
                                $error=$this->errMsg;
                                //no se puede crear el dominio
                                $this->_DB->rollBack(); //desasemos los cambios en la base
                                $this->cleanFailCreation($resultOrgz["domain"]); //eliminamos cualquier rastro de la organizacion
                            }else{
                                //creamos al usuario administrador de las organizacion
                                $user=$this->createAdminUserOrg($resultOrgz['id'],$domain,$name,$email_contact,$admin_password,$country_code,$area_code,$quota,true);
                                if($user){
                                    $this->_DB->commit();
                                    return true;
                                }else{
                                    $error=$this->errMsg;
                                    //revertimos los cambios realizados
                                    $this->_DB->rollBack(); //desasemos los cambios en la base
                                    //eliminamos cualquier rastro de la oprganizacion de asterisk
                                    $pAstConf->writeExtesionConfFile();
                                    $this->cleanFailCreation($resultOrgz["domain"]);
                                    //eliminamos el dominio de la organizacion
                                    $pEmail->writePostfixMain();
                                    $pEmail->reloadPostfix();
                                }
                            }
                        }else{
                            $error=_tr("An Error has ocurred to create dialplan for new organization. ").$pAstConf->errMsg;
                            $this->_DB->rollBack();
                            $this->cleanFailCreation($resultOrgz["domain"]); //eliminamos cualquier rastro de la organizacion
                        }
                    }
                }else{
                    $error=_tr("An Error has ocurred to set organization properties").$this->errMsg;
                    $this->_DB->rollBack();
                }
            }
        }else{
            $error=_tr("Already exist other organization with the same domain");
        }

        $this->errMsg=$error;
        return $flag;
    }
    
    //esta funcion es usada para crear al usuario administrado de la organizacion 
    //una vez que la organizacion ha sido creada
    private function createAdminUserOrg($idOrg,$domain,$CompanyName,$email_contact,$password,$country_code,$area_code,$quota,$sendEmail=false){
        $md5password=md5($password);
        $pACL=new paloACL($this->_DB);
        $idGrupo=$pACL->getIdGroup("administrator",$idOrg);
        $exito=$this->createUserOrganization($idOrg,"admin", "Administrator", $md5password, $password, $idGrupo, "100", "200",$country_code, $area_code, "200", "admin", $quota, $lastid,false);
        if($exito){
            //mostramos el mensaje para crear los archivos de configuracion dentro de asterisk
            $pAstConf=new paloSantoASteriskConfig($this->_DB);
            $pAstConf->setReloadDialplan($domain,true);
            //enviamos un email a la nueva organizacion creada
            if($sendEmail==true){
                if(!$this->sendEmail($password,$CompanyName,$domain,$email_contact,"create",$error)){
                    $this->errMsg="<br />"._tr("Mail to new admin user couldn't be sent. ").$error;
                }else
                    $this->errMsg="<br />"._tr("A email with the password for admin@$domain user has been sent to ").$email_contact;
            }
            return true;
        }else{
            //mensaje en caso de que no se pueda crear el usuario administrador de la organizaion
            $this->errMsg="<br />Error: ".$this->errMsg;
        }
        return false;
    }

    //a una entidad no se le puede editar el dominio
    function setOrganization($id,$name,$country,$city,$address,$country_code,$area_code,$quota,$email_contact,$max_num_user,$max_num_exten,$max_num_queues,$userLevel1)
    {
        if (!preg_match('/^[[:digit:]]+$/', "$id") || $id=="1") {
            $this->errMsg = "Invalid ID Organizaion";
            return false;
        }

        $query="SELECT domain from organization where id=?";
        $res=$this->_DB->getFirstRowQuery($query,true,array($id));
        if($res==false){
            $this->errMsg=_tr("Organization doesn't exist. ").$this->_DB->errMsg;
            return false;
        }
        $domain=$res["domain"];

        if($userLevel1=="superadmin"){
            $numUser=$this->getNumUserByOrganization($id);
            if($max_num_user!=0){
                if($max_num_user<$numUser){
                    $this->errMsg=_tr("Max. # of User Accounts")._tr(" must be greater than current numbers of users "). "($numUser)";
                    return false;
                }
            }
            //obtenemos el total de extensiones y colas creadas
            if($max_num_exten!=0){
                $query="SELECT count(id) from extension where organization_domain=?";
                $res=$this->_DB->getFirstRowQuery($query,false,array($domain));
                if($max_num_exten<$res[0]){
                    $this->errMsg=_tr("Max. # of exten")._tr(" must be greater than current numbers of exten ")."($res[0])";
                    return false;
                }
            }
            if($max_num_queues!=0){
                $query="SELECT count(name) from extension where organization_domain=?";
                $res=$this->_DB->getFirstRowQuery($query,false,array($domain));
                if($res!==false){
                    if($max_num_queues<$res[0]){
                        $this->errMsg=_tr("Max. # of queues")._tr(" must be greater than current numbers of queues "). "($res[0])";
                        return false;
                    }    
                }
            }
        }

        $flag=false;$cExito=false;$aExito=false;$qExito=false;
        $address=isset($address)? $address : "";
        $query="UPDATE organization set name=?,country=?,city=?,address=?,email_contact=? where id=?;";
        $arr_params=array($name,$country,$city,$address,$email_contact,$id);
		$this->_DB->beginTransaction();
        $result=$this->_DB->genQuery($query,$arr_params);
        if($result==FALSE){
            $this->errMsg=$this->_DB->errMsg;
            $this->_DB->rollBack();
        }else{
            $cExito=$this->setOrganizationProp($id,"country_code",$country_code,"fax");
            $aExito=$this->setOrganizationProp($id,"area_code",$area_code,"fax");
            $qExito=$this->setOrganizationProp($id,"email_quota",$quota,"email");
            
            if($userLevel1=="superadmin"){
                $muExito=$this->setOrganizationProp($id,"max_num_user",$max_num_user,"limit");
                $meExito=$this->setOrganizationProp($id,"max_num_exten",$max_num_exten,"limit");
                $mqExito=$this->setOrganizationProp($id,"max_num_queues",$max_num_queues,"limit");
            }else{
                $muExito=$meExito=$mqExito=true;
            }
            
            if($cExito!=false && $aExito!=false && $qExito!=false && $muExito!=false && $meExito!=false && $mqExito!=false){
                $flag=true;
                $this->_DB->commit();
            }else{
                $this->_DB->rollBack();
            }
        }
        return $flag;
    }
    
    /**
        funcion que elimina de elastix un conjunto de organizacion
        @param $arrOrg array arreglo unidimensional que contiene el id de
                             las organizaciones que se van a eliminar
    */
    function deleteOrganization($arrOrg){
        if(!is_array($arrOrg)){
            $arrOrg=array($arrOrg);
        }
        
        $pFax=new paloFax($this->_DB);
        $pEmail=new paloEmail($this->_DB);
        $flag=true;
        $arrDelOrg=$arrIdCode=array();
        $exito=$error="";
        
        foreach($arrOrg as $idOrg){
            if(preg_match("/^[0-9]+$/",$idOrg) && $idOrg!=1){
                //se borra tosod los registros de la organizacion de la base de datos
                //se elimina los correos y los faxes de los usuarios
                if($this->deleteOrganizationDB($idOrg,$idcode)){
                    $arrIdCode[]=$idcode;
                    $arrDelOrg[]=$this->errMsg;
                }else{
                    $error .=$this->errMsg."<br />";
                    $flag=false;
                }
            }
        }
        
        if(count($arrDelOrg)!=0){
            $exito=_tr("The organizations with the followings domains where deleted from database: ").implode(",",$arrDelOrg)."<br /><br />";
        }else{
            //ninguna de las organizaciones dada pudo ser elminada
            //regresamos y mostramos los errores
            $this->errMsg=$error;
            return false;
        }
        
        //***************************************************
        //reescribimos los archivos extensions.conf, extensions_globals.conf chan_dahdi_additional.conf con las configuraciones correctas
        $astError="";
        $pAstConf=new paloSantoASteriskConfig($this->_DB);// extensions.conf, extensions_globals.conf
        if(!$pAstConf->writeExtesionConfFile()){
            $astError=_tr("Error has ocurred to try rewriting asterisk configs file. ").$pAstConf->errMsg."<br />";
            $flag=false;
        }
        
        $sComando = "/usr/bin/elastix-helper asteriskconfig createFileDahdiChannelAdd 2>&1";
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0){
            $astError .=_tr("Error has ocurred to try rewriting asterisk config file extensions_additional_dahdi.conf").implode('', $output)."<br />";
            $flag=false;
        }
        if($astError!="")
            $astError ="<br />".$astError;
            
        //***************************************************
        
        //***************************************************
        //reescribimos archivos /var/spool/hylafax/faxDispatch y /etc/init/elastix_fax
        //estos manejan los envios de los faxes al mail y la creacion de la lineas tty para los modems
        //***************************************************
        $fError="";
        if(!$pFax->writeFilesFax()){
            $fError=_tr("Error has ocurred to try rewriting fax config file. ").$pFax->errMsg."<br />";
            $flag=false;
        }
        //***************************************************
        
        //***************************************************
        //reescribimos el archivo /etc/postfix/main.cf que contiene los dominios creados en el sistema
        if(!$pEmail->writePostfixMain()){
            $fError=_tr("Error has ocurred to try rewriting email config file. ").$pEmail->errMsg."<br />";
            $flag=false;
        }
        //***************************************************
        
        //********************************************************************
        //elminamos los archivos de audio,grabaciones,faxes, etc relacionados con la organizacion
        $dError="";
        foreach($arrIdCode as $idcode){
            if(!$this->deleteFilesOrganization($idcode)){
                $dError .= $this->errMsg;
                $flag=false;
            }
        }
        if($dError!="")
            $dError = "<br />"._tr("Error has ocurred to try delete organizations data:")."<br />".$dError;
        //***************************************************
        
        //***************************************************
        //recargamos lo servicios de fax,email y asterisk para que los cambios hechos en los archivos de 
        //configuracion tomen efecto
        $reError="";
        if(!$this->reloadServices()){
            $reError .= "<br />"._tr("Error has ocurred to try reloading Elastix services:")."<br />";
            $reError .=$this->errMsg;
            $flag=false;
        }
        //***************************************************
        $this->errMsg=$exito.$error.$astError.$fError.$dError.$reError;
        return $flag;
    }
    
    private function deleteFilesOrganization($idcode){
        $sComando = "/usr/bin/elastix-helper asteriskconfig deleteFolderOrganization ".
            escapeshellarg($idcode)." 2>&1";
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0){
            $this->errMsg = implode('', $output);
            return false;
        }else
            return true;
    }
    
    private function cleanFailCreation($domain){
        $sComando = "/usr/bin/elastix-helper asteriskconfig cleanFailCreation ".
            escapeshellarg($domain)." 2>&1";
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0){
            $this->errMsg = implode('', $output);
            return false;
        }else
            return true;
    }

    private function deleteOrganizationDB($id,&$idcode){
		$dGroup=true;
		$pACL=new paloACL($this->_DB);
		$error="";
        
        if(!preg_match("/^[0-9]+$/",$id)){
            $this->errMsg=_tr("Inavlid Organization");
            return false;
        }elseif($id==1){
            //la organization con id 1 corresponde a la organizacion que viene por default en asterisk
            //esta no puede ser borrada
            $this->errMsg=_tr("Inavlid Organization");
            return false;
        }
        
        $numUsers=$this->getNumUserByOrganization($id);
        $arrOrgz=$this->getOrganizationById($id);

        if(is_array($arrOrgz) && count($arrOrgz)>0){
            $name=$arrOrgz['name'];
            $domain=$arrOrgz['domain'];
			$code=$arrOrgz['code'];
			$idcode=$arrOrgz['idcode'];
			$error=_tr("Organization domain: ")."$domain Err:";
            if($numUsers===false){ //ahi un error en la conexion
                $this->errMsg = $error."ct".$this->_DB->errMsg;
				return false;
            }else{
                if($arrOrgz['state']!="terminate"){
                    $this->errMsg =$error._tr("Organization state != 'Terminate'");
                    return false;
                }
                
                $this->_DB->beginTransaction();
                
                //registramos en el servidor que la organizacion ha sido borrada
                if(!$this->orgHistoryRegister("delete", $arrOrgz['idcode'])){
                    $this->errMsg =$error.$this->errMsg;
                    $this->_DB->rollBack();
                    return false;
                }
                
                //registramos en el servidor que la organizacion ha sido borrada
                if(!$this->registerEvent("delete", $arrOrgz['idcode'])){
                    $this->errMsg =$error.$this->errMsg;
                    $this->_DB->rollBack();
                    return false;
                }
                
                //borramos los archivos del configuracion de faxes de los usuarios pertenecientes a la organizacion
                $bExito =$this->deleteFaxsByOrg($id);
                if (!$bExito){
                    $this->errMsg=$error._tr("Faxes couldn't be deleted.")." ".$this->errMsg;
                    $this->_DB->rollBack();
                    return false;
                }
                
                //borramos la organizacion de asterisk
                $pAstConf=new paloSantoASteriskConfig($this->_DB);
                //TODO: setear backup de astDB de la organizaiona ntes de proseguir para
                //poder restaurar estos valores en caso de que algo salga mal
                if(!$pAstConf->deleteOrganizationPBX($domain,$code)){
                    $this->errMsg .=$error.$pAstConf->errMsg." ".$this->errMsg;
                    $this->_DB->rollBack();
                    return false;
                }
                
                // se borra la organización en kamailio
                if (!$this->_DB->genQuery(
                    'DELETE FROM kamailio.domain WHERE domain = ?',
                    array($domain))) {
                	
                    $this->errMsg =$error.$this->_DB->errMsg;
                    $this->_DB->rollBack();
                    return false;
                }
                
                //borramos la organization
                //la base esta en mysql y todas las tablas relacionadas a la organizacion
                //tiene referencia a la tabla organization y tienen un constraint delete cascade
                $query="DELETE FROM organization WHERE id = ?";
                $result=$this->_DB->genQuery($query,array($id));
                if($result==FALSE){ //no se puede eliminar la organizacion
                    $this->errMsg =$error.$this->_DB->errMsg;
                    $this->_DB->rollBack();
                    return false;
                }
                
                //borramos los buzones de correo de los usuarios pertencientes a la organizacion
                //esto se hace al ultimo porque en caso de que algo salga mal no tener que restaurar lso correos
                $bExito = $this->deleteAccountByDomain($domain);
                if (!$bExito){
                    $this->errMsg=_tr("Mailbox couldn't be deleted.")." ".$this->errMsg;
                    $this->_DB->rollBack();
                    return false;
                }else{
                    $this->_DB->commit();
                    $this->errMsg .=$domain; //regresa el dominio de la organizacion que se elimino
                    return true;
                }
            }
		}else{
			$this->errMsg=_tr("Organization doesn't exist. Id: ").$id;
			return false;
		}
    }
    
    /**
    * Procedimiento que elimina todos los faxes asociados con una organizacion
    * recibe como parametros el id de la organizacion
    *
    * @return bool VERDADERO en caso de éxito, FALSO en error
    */
    private function deleteFaxsByOrg($idOrg)
    {
        if(!preg_match("/^[0-9]+$/",$idOrg)){
            $this->errMsg=_tr("Invalid Organization");
            return false;
        }
        
        $sComando = '/usr/bin/elastix-helper faxconfig deleteFaxsByOrg '.escapeshellarg($idOrg).'  2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }
    
    //*****Email section - Esatas funciones son usadas dentro de esta libreria********
    //para crear o eliminar los dominios al momento de crear o elimanr una organizacion
    //respectivamente
    /**
    * Procedimiento que crea un dominio dentro del sistema
    * esta funcion solo debe ser llamada al momento de crear una organizacion
    *
    * @param string    $domain_name       nombre para el dominio
    * @return bool     VERDADERO si el dominio se crea correctamente, FALSO en error
    */
    private function createDomain($domain){
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $error=_tr("Invalid domain format");
            return false;
        }
        
        $sComando = '/usr/bin/elastix-helper email_account --createdomain '.
            escapeshellarg($domain).' 2>&1';
        exec($sComando, $output, $retval);
        if ($retval != 0) {
            foreach ($output as $s) {
                $regs = NULL;
                if (preg_match('/^ERR: (.+)$/', trim($s), $regs)) {
                    $this->errMsg = $regs[1];
                }
            }
            if ($this->errMsg == '')
                $this->errMsg = implode('<br/>', $output);
            return FALSE;
        }
        return TRUE;
    }
    
    /**
    * Procedimiento para borrar del sistema el dominio asociado a una 
    * organizacion. Se borran tambien todas las lista de mail y mailboxs
    * asociados a la organizacion
    *
    * @param string    $domain_name       nombre para el dominio
    *
    * @return bool     VERDADERO si el dominio se borra correctamente, FALSO en error
    */
    private function deleteAccountByDomain($domain_name)
    {
        $this->errMsg = '';
        $output = $retval = NULL;
        $sComando = '/usr/bin/elastix-helper email_account --deleteAccountByDomain '.
            escapeshellarg($domain_name).' 2>&1';
        
        exec($sComando, $output, $retval);
        if ($retval != 0) {
            foreach ($output as $s) {
                $regs = NULL;
                if (preg_match('/^ERR: (.+)$/', trim($s), $regs)) {
                    $this->errMsg = $regs[1];
                }
            }
            if ($this->errMsg == '')
                $this->errMsg = implode('<br/>', $output);
            return FALSE;
        }
        return TRUE;
    }
    //*****End Email section ********
    
    private function reloadServices(){
        $pFax = new paloFax($this->_DB);
        $pEmail = new paloEmail($this->_DB);
        $flag=true;
        
        if(!$pFax->restartService()){
            $this->errMsg .= $pFax->errMsg;
            $flag=false;
        }
        
        if(!$pEmail->reloadPostfix()){
            $this->errMsg .= $pEmail->errMsg;
            $flag=false;
        }
        
        $sComando = '/usr/bin/elastix-helper asteriskconfig reload 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0){
            $this->errMsg = implode('', $output);
            $flag=false;
        }
        return $flag;
    }

    
    //funcion usada para enviar un email de respuesta desde el servidor elastix 
    //al email_contact de una organizacion, al momento de que la organizacion es creada, 
    //suspendida o terminada
    private function sendEmail($password ,$org_name, $org_domain, $email_contact, $category, &$error)
    {
        global $arrConf;
        require_once("{$arrConf['elxPath']}/libs/phpmailer/class.phpmailer.php");

        if (!preg_match(
            '/^[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*@[a-z0-9]+([\._\-]?[a-z0-9]+)*(\.[a-z0-9]{2,4})+$/',
            $email_contact)) {
            $error = 'Email address for notification is invalid or not set';
            return false;
        }
        if ($category == 'create' && empty($password)){
            $error = _tr("User Password can't be empty");
            return false;
        }
        
        // Configuración por omisión de parámetros del envío de email
        $default_content = array(
            'create'    =>  "Your entity {COMPANY_NAME}, associated with the domain {DOMAIN} has been created.\n".
                            "To configure you Elastix server, please go to https://{HOST_IP} and login into Elastix with the following credentials:\n".
                            "Username: admin@{DOMAIN}\n".
                            "Password: {USER_PASSWORD}",
            'suspend'   =>  "Your entity {COMPANY_NAME}, associated with the domain {DOMAIN} has been suspended.\n",
            'delete'    =>  "Your entity {COMPANY_NAME}, associated with the domain {DOMAIN} has been deleted.\n",
        );
        if (!isset($default_content[$category])) {
            $error = _tr("Invalid category");
            return false;
        }
        $default_conf_email = array(
            'subject'       =>  'Elastix Notification',
            'from_email'    =>  'elastix@example.com',  //quien envia el email
            'from_name'     =>  'Elastix Admin',        //nombre de quien envia el email
            'content'       =>  $default_content[$category],
            'host_ip'       =>  '',
            'host_domain'   =>  '', // no se usa ahora
            'host_name'     =>  '', // no se usa ahora
        );

        // obtenemos los parametros de configuracion para mandar mail de acuredo a la categoria
        $conf_email = $this->_DB->getFirstRowQuery(
            'SELECT * FROM org_email_template where category = ?',
            true, array($category));
        foreach (array_keys($default_conf_email) as $k) {
        	if (empty($conf_email[$k])) $conf_email[$k] = $default_conf_email[$k];
        }

        // El siguiente código obtiene la IP pública para este servidor
        if (empty($conf_email['host_ip'])){
            $output = NULL;
            exec("curl ifconfig.me", $output);
            if (isset($output[0])) $conf_email['host_ip'] = $output[0];
        }
        
        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->From = $conf_email['from_email'];
        $mail->FromName = $conf_email['from_name'];
        $mail->AddAddress($email_contact);
        $mail->WordWrap = 70;                                 // set word wrap to 70 characters
        $mail->IsHTML(false);                                  // set email format to TEXT
                
        $mail->Subject = $conf_email['subject'];
        $mail->Body    = str_replace(
            array('{COMPANY_NAME}', '{DOMAIN}', '{USER_PASSWORD}', '{HOST_IP}'),
            array($org_name, $org_domain, $password, $conf_email['host_ip']),
            $conf_email['content']);
                
        // envio del mensaje
        if ($mail->Send()){
            $error = "Se envio correctamenete el mail";
            return true;
        }else{ 
            $error = "Error al enviar el mail".$mail->ErrorInfo;
            return false;
        }
    }

    function setParameterUserExtension($domain,$type,$exten,$secret,$fullname,$email)
    {
        $pDevice=new paloDevice($domain,$type,$this->_DB);
        if($pDevice->errMsg!=""){
            $this->errMsg=_tr("Error getting settings from extension user").$pDevice->errMsg;
            return false;
        }
        $pGPBX = new paloGlobalsPBX($this->_DB,$domain);
        
        $arrProp["elastix_user"]=strstr($email, '@', true);
        
        $arrProp=array();
        $arrProp["fullname"]=$fullname;
        //$arrProp["elastix_user"]=strstr($email, '@', true);
        //en un futuro se tiene pensado usar como name para el dispositivo de usario su username
        //$arrProp["name"]=$exten;
        $arrProp["name"]=strstr($email, '@', true);
        $arrProp["exten"]=$exten;
        $arrProp['secret']= $secret;
        $arrProp["vmpassword"]= $exten;
        $arrProp["vmemail"]=$email;
        $arrProp["record_in"]="on_demand";
        $arrProp["record_out"]="on_demand";
        $arrProp["callwaiting"]="no";
        $arrProp["rt"]=$pGPBX->getGlobalVar("RINGTIMER");
        $arrProp["create_vm"]=$pGPBX->getGlobalVar("CREATE_VM");
        $result=$pDevice->tecnologia->getDefaultSettings($domain);
        $arrOpt=array_merge($result,$arrProp);
        if(empty($arrOpt["context"]))
            $arrOpt["context"]="from-internal";
        if(empty($arrOpt["host"]))
            $arrOpt["host"]="dynamic";
            
        $arrOpt["create_elxweb_device"]="yes"; //a esto se le agrega el codigo de la organizacion
        $arrOpt["alias"]=strstr($email, '@', true);
        //$arrOpt["alias"]=$exten;
        return $arrOpt;
    }

    /**
        Este procedimiento se encarga de crear un usuario que pertenece a una organizacion,
        al usuario se le crea una cuenta de correo dentro de la organizacion
        una extension telefonica dentro de asterisk
        un fax con hylafax y la extension para el fax dentro de asterisk
    */
    function createUserOrganization($idOrganization, $username, $name, $md5password, $password, $idGroup, $extension, $fax_extension,$countryCode, $areaCode, $clidNumber, $cldiName, $quota, &$lastId,$transaction=true)
    {
        require_once "apps/general_settings/libs/paloSantoGlobalsPBX.class.php";
        
        $pACL=new paloACL($this->_DB);
        $pEmail = new paloEmail($this->_DB);
        $pFax = new paloFax($this->_DB);
        $continuar=true;
        $Exito = false;
        $error="";

        // 1) valido que la organizacion exista
        // 2) trato de crea el usuario en la base -- aqui se hacen validaciones con respecto al usuario
        //		--Se valida que no exista otro usuario con el mismo username
        //		--Se valida que no exista otro usuario dentro de la misma organizacion con la misma sip_extension
        //		--Se valida que no exista otro usuario dentro de la misma organizacion con la misma fax_extension
        //      --Que no se supere el maximo numeros de usuarios por organizacion de existir esa propiedad
        // 3) creo la cuenta de fax
        // 4) creo la cuenta de mail
        // 5) se crea la extension dentro del plan de marcado para el usuario

        if($name=="")
            $name=$username;

        $arrOrgz=$this->getOrganizationById($idOrganization);
        if(is_array($arrOrgz) && count($arrOrgz)>0){ // 1)
            $emailUser = $username;
            $username  = $emailUser."@".$arrOrgz["domain"];
            $peer_name = $emailUser."_".$arrOrgz["code"];
            $peer_fax  = $fax_extension."_".$arrOrgz["code"];
            
            //validamos que no exista otro usuario con la misma sip_extension
            //validamos que no exista otro usuario con la misma fax_extension
            //TODO: en un futuro las extensiones podran ser sip o iax, eso lo define el administrador entre las
            //opciones generales y habra que preguntar que tipo de extension se va a crear
            if($fax_extension==$extension){
                $this->errMsg=_tr("Extension number and Fax number can not be equal");
                return false;
            }

            $pDevice=new paloDevice($arrOrgz["domain"],"sip",$this->_DB);
            if($pDevice->existDevice($extension,$peer_name,"sip")==true){
                $this->errMsg="Error Extension Number. ".$pDevice->errMsg;
                return false;
            }

            //las extensiones usadas para el fax siempre son de tipo iax
            if($pDevice->existDevice($fax_extension,$peer_fax,"iax2")==true){
                $this->errMsg="Error Extension Number. ".$pDevice->errMsg;
                return false;
            }
            
            $max_num_user=$this->getOrganizationProp($idOrganization,"max_num_user");
            if(ctype_digit($max_num_user)){
                if($max_num_user!=0){
                    $numUser=$this->getNumUserByOrganization($idOrganization);
                    if($numUser>=$max_num_user){
                        $this->errMsg=_tr("Err: You can't create new users because you have reached the max numbers of users permitted")." ($max_num_user). "._tr("Contact with the server's admin");
                        return false;
                    }
                }
            }
            
            if($transaction) $this->_DB->beginTransaction();
            if(($pACL->createUser($username, $name, $md5password, $idGroup,$extension,$fax_extension, $idOrganization))){//creamos usuario
                //seteamos los registros en la tabla user_properties
                if($countryCode=="" || $countryCode==null) $countryCode= $this->getOrganizationProp($idOrganization,"country_code");
                if($areaCode=="" || $areaCode==null) $areaCode= $this->getOrganizationProp($idOrganization,"area_code");
                if($clidNumber=="" || $clidNumber==null) $clidNumber = $fax_extension;
                if($cldiName=="" || $cldiName==null) $cldiName = $name;
                $fax_subject=$this->getOrganizationProp($idOrganization,"fax_subject");
                $fax_content=$this->getOrganizationProp($idOrganization,"fax_content");
                $fax_subject = (empty($fax_subject))?"Fax attached (ID: {NAME_PDF})":$fax_subject;
                $fax_content = (empty($fax_content))?"Fax sent from '{COMPANY_NAME_FROM}'. The phone number is {COMPANY_NUMBER_FROM}. \n This email has a fax attached with ID {NAME_PDF}.":$fax_content;
                
                //obtenemos el id del usuario que acabmos de crear
                $idUser = $pACL->getIdUser($username);
                $lastId=$idUser;

                if($quota=="" || $quota==null) $quota = $this->getOrganizationProp($idOrganization,"email_quota");
                //seteamos la quota
                if($quota!==false && $continuar){
                    if(!$pACL->setUserProp($idUser,"email_quota",$quota,"email")){
                        $error= _tr("Error setting quota").$pACL->errMsg;
                        if($transaction) $this->_DB->rollBack();
                        $continuar=false;
                    }
                }else{
                    $error= _tr("Property quota is not set").$this->errMsg;
                    $continuar=false;
                }

                $arrSysProp = $this->getOrganizationPropByCategory($idOrganization,"system");
                if(is_array($arrSysProp) && $continuar){
                    foreach($arrSysProp as $tmp){
                        if(!$pACL->setUserProp($idUser,$tmp["property"],$tmp["value"],"system")){
                            $error= _tr("Error setting user properties").$pACL->errMsg;
                            if($transaction) $this->_DB->rollBack();
                            $continuar=false;
                            break;
                        }
                    }
                }

                if($continuar){
                    //creamos la extension del usuario
                    $arrProp=$this->setParameterUserExtension($arrOrgz["domain"],"sip",$extension,$password,$name,$username,$this->_DB);
                    if($arrProp==false){
                        $error=$this->errMsg;
                        if($transaction) $this->_DB->rollBack();
                        $continuar=false;
                    }else{
                        if($pDevice->createNewDevice($arrProp,"sip")==false){
                            $error=$pDevice->errMsg;
                            if($transaction) $this->_DB->rollBack();
                            $pDevice->deleteAstDBExt($extension,"sip");
                            $continuar=false;
                        }
                    }
                }

                //creamos fax y el email del usuario
                if($continuar){
                    //$idUser,$countryCode,$areaCode,$cldiName,$clidNumber
                    if($pFax->createFaxToUser(array("idUser"=>$idUser, "country_code"=>$countryCode, "area_code"=>$areaCode,"clid_name"=>$cldiName, "clid_number"=>$clidNumber, "fax_content"=>$fax_content,"fax_subject"=>$fax_subject))){//si se crea exitosamente el fax creamos el email
                        if($pEmail->createAccount($arrOrgz["domain"],$emailUser,$password,$quota*1024)){
                            $Exito=true;
                            if($transaction) $this->_DB->commit();
                            $pFax->restartService();
                        }else{
                            $error=_tr("Error trying create email_account").$pEmail->errMsg;
                            $devId=$pACL->getUserProp($idUser,"dev_id");
                            if($transaction) $this->_DB->rollBack();
                            $pDevice->deleteAstDBExt($extension,"sip");
                            $pFax->deleteFaxConfiguration($devId);
                        }
                    }else{
                        $error=_tr("Error trying create new fax").$pFax->errMsg;
                        $pDevice->deleteAstDBExt($extension,"sip");
                        if($transaction) $this->_DB->rollBack();
                    }
                }
            }else{
                $error=_tr("User couldn't be created").". ".$pACL->errMsg;
                if($transaction) $this->_DB->rollBack();
            }
        }else{
            $error=_tr("Invalid Organization").$this->errMsg;
        }
        $this->errMsg=$error;
        return $Exito;
    }

    function updateUserSuperAdmin($idUser, $name, $md5password, $password1, $email_contact, $userLevel1){
        $pACL=new paloACL($this->_DB);
        $arrUser=$pACL->getUsers($idUser);
        if($arrUser===false || count($arrUser)==0 || !isset($idUser)){
            $this->errMsg=_tr("User dosen't exist");
            return false;
        }

        if($userLevel1!="superadmin"){
            $this->errMsg=_tr("You aren't authorized to perform this action");
            return false;
        }

        $this->_DB->beginTransaction();
        //actualizamos la informacion de usuario que esta en la tabla acl_user
        if($pACL->updateUserName($idUser, $name)){
            if($pACL->setUserProp($idUser,"email_contact",$email_contact,"email")){
                //actualizamos el password del usuario
                if($password1!==""){
                    if($pACL->changePassword($idUser,$md5password)){
                        $this->_DB->commit();
                        return true;
                    }else{
                        $error=_tr("Password couldn't be updated")." ".$pACL->errMsg;
                        $this->_DB->rollBack();
                        return false;
                    }
                }else{
                    $this->_DB->commit();
                    return true;
                }
            }else{
                $error=_tr("Can't set email contact.")." ".$pACL->errMsg;
                $this->_DB->rollBack();
                return false;
            }
        }else{
            $error=_tr("User couldn't be update.")." ".$pACL->errMsg;
            $this->_DB->rollBack();
            return false;
        }
    }

    function updateUserOrganization($idUser, $name, $md5password, $password1, $extension, $fax_extension,$countryCode, $areaCode, $clidNumber, $cldiName, $idGrupo, $quota, $userLevel1,&$reAsterisk){
        require_once "apps/general_settings/libs/paloSantoGlobalsPBX.class.php";
        $pACL=new paloACL($this->_DB);
        $pEmail = new paloEmail($this->_DB);
        $pFax = new paloFax($this->_DB);
        $continuar=true;
        $Exito = false;
        $error="";
        $cExten=false;
        $cFExten=false;
        $arrBackup=array();
        $editFax=false;
        $faxProperties=array();
        
        $arrUser=$pACL->getUsers2($idUser);
        if($arrUser===false || count($arrUser)==0 || !isset($idUser)){
            $this->errMsg=_tr("User dosen't exist");
            return false;
        }

        if($pACL->isUserSuperAdmin($arrUser[0]['username'])){
            $this->errMsg=_tr("Invalid Action");
            return false;
        }

        $arrOrgz=$this->getOrganizationById($arrUser[0]['id_organization']);

        $username=$arrUser[0]['username'];
        $oldExten=$arrUser[0]['extension'];
        $oldFaxExten=$arrUser[0]['fax_extension'];
        
        $pDevice=new paloDevice($arrOrgz["domain"],"sip",$this->_DB);
        $arrExtUser=$pDevice->getExtension($oldExten);
        $listFaxs=$pFax->getFaxList(array("exten"=>$oldFaxExten,"organization_domain"=>$arrOrgz['domain']));
        $faxUser=$listFaxs[0];
        
        if($name=="")
            $name=$username;

        if($userLevel1=="other"){
            $extension=$arrUser[0]['extension'];
            $fax_extension=$arrUser[0]['fax_extension'];
            $quota=$pACL->getUserProp($idUser,"email_quota");
            $idGrupo=$arrUser[0]['id_group'];
            $modificarExts=false;
        }else{
            //verificar si el usuario cambio de extension y si es asi que no este siendo usado por otro usuario
            if($extension!=$oldExten){
                if($pDevice->existDevice($extension,"{$arrOrgz["code"]}_{$extension}",$arrExtUser["tech"])==true){
                    $this->errMsg=$pDevice->errMsg;
                    return false;
                }else
                    $cExten=true;
            }

            if($fax_extension!=$oldFaxExten){
                //si el usairo quiere cambiar el patron de marcado asociado al fax verificar que el nuevo 
                //patron de marcado no este siendo usado dentro de la organizacion
                if($pDevice->tecnologia->existExtension($fax_extension,$pDevice->getDomain())){
                    $this->errMsg=$pDevice->errMsg;
                    return false;
                }else
                    $cFExten=true;
            }

            //para cambiar al usuario de extension o faxextension es necesario que se haya llenado el campo password para
            //poder crear las extensiones con la clave correcta
            if($cExten || $cFExten){
                if(is_null($md5password) || $md5password=="" || is_null($password1) || $password1==""){
                    $this->errMsg=_tr("Please set a password");
                    return false;
                }
            }
        }
        
        if(empty($clidNumber) && $clidNumber!=0){
            $clidNumber = $faxUser['clid_number'];
        }
        if(empty($cldiName) && $cldiName!=0){
            $cldiName = $faxUser['clid_name'];
        }
        if(empty($country_code)){
            $country_code = $faxUser['country_code'];
        }
        if(empty($area_code)){
            $area_code = $faxUser['area_code'];
        }
                
        $this->_DB->beginTransaction();
        //actualizamos la informacion de usuario que esta en la tabla acl_user
        if($pACL->updateUser($idUser, $name, $extension, $fax_extension)){
            //actualizamos el grupo al que pertennece el usuario
            if($pACL->addToGroup($idUser, $idGrupo)){

                $old_quota=$pACL->getUserProp($idUser,"email_quota");
                if($old_quota===false){
                    $old_quota=1;
                }
                //actualizamos la quota de correo
                if(isset($quota) && $quota!="" && $continuar){
                    if($pEmail->updateQuota($old_quota*1024,$quota*1024,$username)){
                        if(!$pACL->setUserProp($idUser,"email_quota",$quota,"email")){
                            $error= _tr("Error setting email quota").$pACL->errMsg;
                            $pEmail->updateQuota($quota,$old_quota);
                            $this->_DB->rollBack();
                            $continuar=false;
                        }
                    }else{
                        $error= _tr("Error setting email quota").$pEmail->errMsg;
                        $continuar=false;
                    }
                }

                if($continuar){
                    if($cExten && $userLevel1!="other"){
                        if(!$this->modificarExtensionUsuario($arrOrgz["domain"],$oldExten,$extension,$password1,$name,$username,$arrBackup)){
                            $error="Couldn't updated user extension. ".$this->errMsg;
                            $continuar=false;
                        }
                    }
                }
                
                //actualizamos el password del usuario
                if($password1!=="" && $continuar){
                    if($pACL->changePassword($idUser,$md5password)){
                        //en caso que no se hayan modificado la extensiones del usuario 
                        //entonces es necesario actualizar el passoword para la extension y el fax
                        if(!$cExten){
                            if(!$pDevice->changePasswordExtension($password1,$extension)){
                                $this->errMsg=_tr("Extension password couldn't be updated").$pDevice->errMsg;
                                $continuar=false;
                            }
                        }
                        
                        //editamos la configuracion del fax
                        if($continuar){
                            if($cFExten  && $userLevel1!="other"){
                                //cuando se cambia el patron de marcado asociado al fax del usuario 
                                //es necesario incluir el parametro oldFaxExten entre los parametros para
                                //la actualizacion correcta de los datos
                                if(!$pFax->editFaxToUser(array("idUser"=>$idUser,"oldFaxExten"=>$oldFaxExten, "country_code"=>$countryCode, "area_code"=>$areaCode,"clid_name"=>$cldiName, "clid_number"=>$clidNumber))){
                                    $error="Couldn't updated user fax. ".$pFax->errMsg;
                                    $continuar=false;
                                }
                            }else{
                                if(!$pFax->editFaxToUser(array("idUser"=>$idUser, "country_code"=>$countryCode, "area_code"=>$areaCode,"clid_name"=>$cldiName, "clid_number"=>$clidNumber))){
                                    $error="Couldn't updated user fax. ".$pFax->errMsg;
                                    $continuar=false;
                                }
                            }
                        }

                        if($continuar){
                            if(!$pEmail->setAccountPassword($username,$password1)){
                                $continuar=false;
                                $error=_tr("Password couldn't be updated")." ".$pEmail->errMsg;
                                $editFax=true;
                            }
                        }
                        //debemos actualizar el password en las variable de session
                        if($continuar && $_SESSION['elastix_user'] == $username){
                            $_SESSION['elastix_pass'] = $md5password;
                            $_SESSION['elastix_pass2'] = $password1;
                        }
                    }else{
                        $error=_tr("Password couldn't be updated")." ".$pACL->errMsg;
                        $continuar=false;
                    }
                }else{
                    //editamos la configuracion del fax
                    if($continuar){
                        if($cFExten  && $userLevel1!="other"){
                            //cuando se cambia el patron de marcado asociado al fax del usuario 
                            //es necesario incluir el parametro oldFaxExten entre los parametros para
                            //la actualizacion correcta de los datos
                            if(!$pFax->editFaxToUser(array("idUser"=>$idUser,"oldFaxExten"=>$oldFaxExten, "country_code"=>$countryCode, "area_code"=>$areaCode,"clid_name"=>$cldiName, "clid_number"=>$clidNumber))){
                                    $error="Couldn't updated user fax. ".$pFax->errMsg;
                                    $continuar=false;
                                }
                        }else{
                            if(!$pFax->editFaxToUser(array("idUser"=>$idUser, "country_code"=>$countryCode, "area_code"=>$areaCode,"clid_name"=>$cldiName, "clid_number"=>$clidNumber))){
                                $error="Couldn't updated user fax. ".$pFax->errMsg;
                                $continuar=false;
                            }
                        }
                    }
                }

                if($continuar){
                    $Exito=true;
                    $this->_DB->commit();
                    //recargamos la configuracion en realtime de los dispositivos para que tomen efectos los cambios
                    if($cExten){
                        //se cambio la extension del usuario hay que eliminar de cache la anterior
                        $pDevice->tecnologia->prunePeer($arrExtUser["device"],$arrExtUser["tech"]);
                    }else{
                        $pDevice->tecnologia->prunePeer($arrExtUser["device"],$arrExtUser["tech"]);
                        $pDevice->tecnologia->loadPeer($arrExtUser["device"],$arrExtUser["tech"]);
                    }
                    
                    if($cFExten){
                        //se cambio la faxextension del usuario hay que eliminar de cache la anterior
                        $pDevice->tecnologia->prunePeer($faxUser["device"],$faxUser["tech"]);
                    }else{
                        //se recarga la faxextension del usuario por los cambios que pudo haber
                        $pDevice->tecnologia->prunePeer($faxUser["device"],$faxUser["tech"]);
                        $pDevice->tecnologia->loadPeer($faxUser["device"],$faxUser["tech"]);
                    }
                    
                    $pFax->restartService();
                }else{
                    $this->_DB->rollBack();
                    if($editFax==true){
                        $pFax->editFaxFileConfig($faxUser['dev_id'],$faxUser['country_code'],$faxUser['area_code'],$faxUser['clid_name'],$faxUser['clid_number'],$arrUser[0]['md5_password'],0,$arrOrgz['domain']);
                    }
                    if($cExten==true){
                        $pDevice->deleteAstDBExt($extension,"sip");
                        $pDevice->restoreBackupAstDBEXT($arrBackup);
                    }
                }
            }else{
                $error=_tr("Failed Updated Group")." ".$pACL->errMsg;
                $this->_DB->rollBack();
            }
        }else{
            $error=_tr("User couldn't be update")." ".$pACL->errMsg;
            $this->_DB->rollBack();
        }

        if($cExten || $cFExten)
            $reAsterisk=true;

        $this->errMsg=$error." ".$this->errMsg;
        return $Exito;
    }

    /**
     * Procedimiento que actualiza los passwords de un usuario dentro de elastix
     * La calve ingresada sera configurada para la cuenta de interfaz web, para su cuenta
     * de email, su secret en el caso de las extensiones sip e iax
     */
    function changeUserPassword($username,$password){
        $pEmail = new paloEmail($this->_DB);
        $pFax = new paloFax($this->_DB);
        $pACL=new paloACL($this->_DB);
        
        //comprobamos que la calve este seteada y sea una clave fuerte
        //verificamos que la nueva contraseña sea fuerte
        if(!isStrongPassword($password)){
            $this->errMsg = _tr("The new password can not be empty. It must have at least 10 characters and contain digits, uppers and little case letters");
            return false;
        }
        //obtenemos la conversion md5 de la clave
        $md5_password=md5($password);
        
        //verficamos que el usuario exista
        $idUser = $pACL->getIdUser($username);
        if($idUser==false){
            $this->errMsg=($pACL->errMsg=='')?_tr("User does not exist"):_tr("DATABASE ERROR");
            return false;
        }
        
        //obtenemos los datos del usuario
        //extension de fax y de telefonia
        $arrUser=$pACL->getUsers($idUser);
        if($arrUser==false){
            $this->errMsg=($arrUser===false)?_tr("DATABASE ERROR"):_tr("User dosen't exist");
            return false;
        }
        
        $this->_DB->beginTransaction();
        if($pACL->isUserSuperAdmin($username)){
            //si es superadmin solo se cambia la clave de interfaz administrativa
            //cambiamos la clave en la insterfax administrativa
            if(!$pACL->changePassword($idUser,$md5_password)){
                $this->_DB->rollBack();
                $this->errMsg=$pACL->errMsg;
                return false;
            }else{
                $this->_DB->commit();
                return true;
            }
        }else{
            //obtenemos el dominio al cual pertenece el usuario
            $arrOrgz=$this->getOrganizationById($arrUser[0][4]);
            if($arrOrgz==false){
                $this->errMsg=_tr("An error has ocurred to retrieve organization data");
                return false;
            }
            
            $domain=$arrOrgz['domain'];
            $extension=$arrUser[0][5];
            $fax_extension=$arrUser[0][6];
            
            $pDevice=new paloDevice($domain,"sip",$this->_DB);
            $arrExtUser=$pDevice->getExtension($extension);
            $listFaxs=$pFax->getFaxList(array("exten"=>$fax_extension,"organization_domain"=>$domain));
            $faxUser=$listFaxs[0];
            
            //cambiamos la clave en la insterfax administrativa
            if(!$pACL->changePassword($idUser,$md5_password)){
                $this->_DB->rollBack();
                $this->errMsg=$pACL->errMsg;
                return false;
            }
            //cambiamos la clave en la extension telefonica
            if(!$pDevice->changePasswordExtension($password,$extension)){
                $this->_DB->rollBack();
                $this->errMsg=_tr("Extension password couldn't be updated").$pDevice->errMsg;
                return false;
            }
            
            //cambiamos la clave para el fax (peer, archivos de configuracion)
            if(!$pFax->editFaxToUser(array("idUser"=>$idUser, "country_code"=>$faxUser['country_code'], "area_code"=>$faxUser['area_code'],"clid_name"=>$faxUser['clid_name'], "clid_number"=>$faxUser['clid_number']))){
                $this->_DB->rollBack();
                $this->errMsg=_tr("Fax Extension password couldn't be updated").$pFax->errMsg;
                return false;
            }
            
            //cambiamos la clave en el correo
            if(!$pEmail->setAccountPassword($username,$password)){
                $this->_DB->rollBack();
                $this->errMsg=_tr("Error to update email account password");
                //reestauramos la configuracion anterior en los archivos de fax
                $pFax->editFaxFileConfig($faxUser['dev_id'],$faxUser['country_code'],$faxUser['area_code'],$faxUser['clid_name'],$faxUser['clid_number'],$arrUser[0][3],0,$arrOrgz['domain']);
                return false;
            }else{
                $this->_DB->commit();
                //recargamos la configuracion en realtime de los dispositivos para que tomen efectos los cambios
                $pDevice->tecnologia->prunePeer($arrExtUser["device"],$arrExtUser["tech"]);
                $pDevice->tecnologia->loadPeer($arrExtUser["device"],$arrExtUser["tech"]);
                if(!empty($arrExtUser["elxweb_device"])){
                    $pDevice->tecnologia->prunePeer($arrExtUser["elxweb_device"],$arrExtUser["tech"]);
                    $pDevice->tecnologia->loadPeer($arrExtUser["elxweb_device"],$arrExtUser["tech"]);
                }
                
                //se recarga la faxextension del usuario por los cambios que pudo haber
                $pDevice->tecnologia->prunePeer($faxUser["device"],$faxUser["tech"]);
                $pDevice->tecnologia->loadPeer($faxUser["device"],$faxUser["tech"]);  
                $pFax->restartService();
                return true;
            } 
        }
    }
    
    private function modificarExtensionUsuario($domain,$oldExten,$extension,$password,$name,$username,&$arrBackup){
        $continuar=true;
        $pDevice=new paloDevice($domain,"sip",$this->_DB);
        $error="";

        //1.- Tomar un backup de las entradas en la base astDB para dicha extension
        //2.- Eliminar la extension anterior
        //3.- Crear la nueva extension

        //borramos la extension anterior 
        $arrBackup=$pDevice->backupAstDBEXT($oldExten);
        //borramos la extension anterior
        if(!$pDevice->deleteExtension($oldExten)){
            $this->errMsg=_tr("Old extension can't be deleted").$pDevice->errMsg;
            return false;
        }

        //creamos una extension nueva
        $arrProp=$this->setParameterUserExtension($domain,"sip",$extension,$password,$name,$username);
        if($arrProp==false){
            $error=$this->errMsg;
            $continuar=false;
        }else{
            if($pDevice->createNewDevice($arrProp,"sip")==false){
                //si no se pude crear la extension anterior se restaura los valores anteriores en la base astDB
                $pDevice->restoreBackupAstDBEXT($arrBackup);
                $error=$pDevice->errMsg;
                $continuar=false;
            }
        }

        $this->errMsg=$error;
        return $continuar;
    }


    function deleteUserOrganization($idUser){
        $pACL=new paloACL($this->_DB);
        $pEmail = new paloEmail($this->_DB);
        $pFax = new paloFax($this->_DB);
        $Exito=false;

        //1)se comprueba de que el ID de USUARIO se un numero
        //2)se verifica que exista dicho usuario
        //3)se recompila los datos del usuario de las tablas acl_user y user_properties
        //4)se elimina al usuario de la base
        //5)se elimina la extension de uso del usuario y la extension de fax
        //6)se trata de eliminar la cuenta de fax
        //7)se elimina el buzon de correo
        if (!preg_match('/^[[:digit:]]+$/', "$idUser")) {
            $this->errMsg = _tr("User ID is not numeric");
            return false;
        }else{
            $arrUser=$pACL->getUsers($idUser);
            if($arrUser===false || count($arrUser)==0){
                $this->errMsg=_tr("User dosen't exist");
                return false;
            }
        }

        $idDomain=$arrUser[0][4];
        $query="Select domain from organization where id=?";
        $getDomain=$this->_DB->getFirstRowQuery($query, false, array($idDomain));
        if($getDomain==false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }
        
        $pDevice=new paloDevice($getDomain[0],"sip",$this->_DB);
        $arrExten=$pDevice->getExtension($arrUser[0][5]);
        $faxList=$pFax->getFaxList($arrUser[0][6],$getDomain[0]);
        $arrFaxExten=$faxList[0];
        
        $this->_DB->beginTransaction();
        //tomamos un backup de las extensiones que se van a eliminar de la base astDB por si algo sale mal
        //y ahi que restaurar la extension
        $arrExt=$pDevice->backupAstDBEXT($arrUser[0][5]);
        if($pDevice->deleteExtension($arrUser[0][5])){
            if($pFax->deleteFaxByUser($idUser)){
                if($pACL->deleteUser($idUser)){
                    if($pEmail->deleteAccount($arrUser[0][1])){
                        $Exito=true;
                        $this->_DB->commit();
                        $pDevice->tecnologia->prunePeer($arrExten["device"],$arrExten["tech"]);
                        $pDevice->tecnologia->prunePeer($arrFaxExten["device"],$arrFaxExten["tech"]);
                        $pFax->restartService();
                    }else{
                        $this->errMsg=_tr("Email Account cannot be deleted").$pEmail->errMsg;
                        $this->_DB->rollBack();
                        $pDevice->restoreBackupAstDBEXT($arrExt);
                        $pFax->createFaxFileConfig($arrFaxExten['dev_id'],$getDomain[0]);
                    }
                }else{
                    $this->errMsg=$pACL->errMsg;
                    $this->_DB->rollBack();
                    $pDevice->restoreBackupAstDBEXT($arrExt);
                    $pFax->createFaxFileConfig($arrFaxExten['dev_id'],$getDomain[0]);
                }
            }else{
                $this->errMsg=_tr("Fax cannot be deleted").$pFax->errMsg;
                $this->_DB->rollBack();
                $pDevice->restoreBackupAstDBEXT($arrExt);
            }
        }else{
            $this->errMsg=_tr("User Extension can't be deleted").$pDevice->errMsg;
            $this->_DB->rollBack();
            $pDevice->restoreBackupAstDBEXT($arrExt);
        }
        return $Exito;
    }
}
?>