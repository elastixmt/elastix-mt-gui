<?php
/*
  vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2003 Palosanto Solutions S. A.                    |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  +----------------------------------------------------------------------+
  | Este archivo fuente está sujeto a las políticas de licenciamiento    |
  | de Palosanto Solutions S. A. y no está disponible públicamente.      |
  | El acceso a este documento está restringido según lo estipulado      |
  | en los acuerdos de confidencialidad los cuales son parte de las      |
  | políticas internas de Palosanto Solutions S. A.                      |
  | Si Ud. está viendo este archivo y no tiene autorización explícita    |
  | de hacerlo, comuníquese con nosotros, podría estar infringiendo      |
  | la ley sin saberlo.                                                  |
  +----------------------------------------------------------------------+
  | Autores: Rocio Mera Suarez <rmera@palosanto.com>               |
  +----------------------------------------------------------------------+
  $Id: ContactList.class.php,v 1.1 2012/02/07 23:49:36 Rocio Mera Exp $
*/
$documentRoot = $_SERVER["DOCUMENT_ROOT"];
require_once "$documentRoot/libs/REST_Resource.class.php";
require_once "$documentRoot/libs/paloSantoJSON.class.php";
require_once "$documentRoot/libs/paloSantoOrganization.class.php";
global $arrConf;

 
class organization
{
    private $resourcePath; //arreglo que contiene los parametros enviados en la uri de la peticion
    
    function __construct($resourcePath)
    {
        $this->resourcePath = $resourcePath;
    }

    function URIObject()
    {
        $uriObject = NULL;
        if(count($this->resourcePath)>0){
            $param=array_shift($this->resourcePath);
            if($param=="state"){
                // /state/id1[;id2;id3]
                if(count($this->resourcePath)>0)
                    $uriObject = new orgStatus(explode(";",array_shift($this->resourcePath))); //GET - PUT 
                else
                    $uriObject = new orgStatus(); //GET
            }else
                $uriObject = new orgActions(explode(";",$param)); //GET /id1[;id2;id3]
        }else
            $uriObject = new orgActions(); //POST - GET(todas)
            
        if(count($this->resourcePath) > 0)
            return NULL;
        else
            return $uriObject;
    }
}


class orgREST extends REST_Resource
{
    public $errMsg;
    
    function orgREST(){
        $this->errMsg="";
    }
    
    protected function isSuperAdmin(){
        $user=$_SERVER['PHP_AUTH_USER'];
        if($user!="admin"){
            return false;
        }
        return true;
    }
    
    protected function setSession(){
        session_name("elastixSession");
        session_start();
        $_SESSION['elastix_user']=$_SERVER['PHP_AUTH_USER'];
    }
    
    protected function invalidCredentials(&$json){
        header('HTTP/1.1 403 Forbidden');
        $json->set_status("ERROR");
        $json->set_error("Invalid credentials");
    }
    
    protected function validateIdOrg($idOrg){
        if(!preg_match("/^[0-9]+$/",$idOrg)){
            return false;
        }
        return true;
    }
    
    protected function resourceNotExis(&$json){
        header('HTTP/1.1 404 Not Found');
        $json->set_status("ERROR");
        $json->set_error("Organanization(s) don't exist");
    }
    
    protected function validateContentType(&$json){
        if (!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] != 'application/x-www-form-urlencoded') {
            header('HTTP/1.1 415 Unsupported Media Type');
            $json->set_status("ERROR");
            $json->set_error('Please POST standard URL encoding only');
            return false;
        }
        return true;
    }
    
    protected function badRequest(&$json){
        header("HTTP/1.1 400 Bad Request");
        $json->set_status("ERROR");
        $json->set_error($this->errMsg);
    }
    
    protected function errSever(&$json){
        header("HTTP/1.1 500 Internal Server Error");
        $json->set_status("ERROR");
        $json->set_error($this->errMsg);
    }
    
    protected function methodNoAllowed($listaPermitida){
        header('HTTP/1.1 405 Method Not Allowed');
        header("Allow: ".implode(', ', $listaPermitida));
    }
}

class orgActions extends orgREST{
    protected $arrIdOrgs;
    
    function orgActions($arrIdOrgs=null){
        parent::__construct();
        $this->arrIdOrgs=$arrIdOrgs;
    }
    
    function HTTP_GET(){
        global $arrConf;
        $jsonObject = new PaloSantoJSON();
        $where="";
        
        if(!$this->isSuperAdmin()){
            $this->invalidCredentials($jsonObject);
            return $jsonObject->createJSON();
        }
        $this->setSession();
        
        $validOrgs=array();
        if(is_array($this->arrIdOrgs)){
            foreach($this->arrIdOrgs as $idOrg){
                if($this->validateIdOrg($idOrg) && $idOrg!="1"){
                    $validOrgs[]=$idOrg;
                }
            }
        }
        
        if(is_array($this->arrIdOrgs)){
            if(count($validOrgs)==0){ //ningun id pasado en la peticion es valido. Devolvemos 404 not found
                $this->resourceNotExis($jsonObject);
                return $jsonObject->createJSON();
            }else{
                $q=str_repeat("?,",count($validOrgs));
                $where="where id in (".substr($q,0,-1).")";
            }
        }else{
            $where="where id!=1";
        }
        
        $pOrg = new paloSantoOrganization($arrConf['elastix_dsn']["elastix"]);
        $query = "SELECT * FROM organization $where";
        $result=$pOrg->_DB->fetchTable($query, true, $validOrgs);
        if($result===false){
            $this->error=$pOrg->_DB->errMsg;
            $this->errSever($jsonObject);
            return $jsonObject->createJSON();
        }elseif($result==false){
            $this->resourceNotExis($jsonObject);
            return $jsonObject->createJSON();
        }else{
            $arrORGS=array();
            $sBaseUrl = '/rest.php/organization/organization';
            foreach($result as $x => $org){
                $arrORGS["organization"][$x]['name']=$org['name'];
                $arrORGS["organization"][$x]['domain']=$org['domain'];
                $arrORGS["organization"][$x]['state']=$org['state'];
                $arrORGS["organization"][$x]['country_code']=$pOrg->getOrganizationProp($org['id'],"country_code");
                $arrORGS["organization"][$x]['area_code']=$pOrg->getOrganizationProp($org['id'],"area_code");
                $arrORGS["organization"][$x]['email_quota']=$pOrg->getOrganizationProp($org['id'],"email_quota");
                $arrORGS["organization"][$x]['max_num_user']=$pOrg->getOrganizationProp($org['id'],"max_num_user");
                $arrORGS["organization"][$x]['max_num_exten']=$pOrg->getOrganizationProp($org['id'],"max_num_exten");
                $arrORGS["organization"][$x]['max_num_queues']=$pOrg->getOrganizationProp($org['id'],"max_num_queues");
                $arrORGS["organization"][$x]['url']=$sBaseUrl.'/'.$org['id'];
            }
            $json = new Services_JSON();
            return $json->encode($arrORGS);
        }
    }
    
    function HTTP_POST(){
        global $arrConf;
        $jsonObject = new PaloSantoJSON();
        
        //solo usuario superadmin puede crear una organization
        if(!$this->isSuperAdmin()){
            $this->invalidCredentials($jsonObject);
            return $jsonObject->createJSON();
        }
        $this->setSession();
        
        //validamos los parametros pasados en el cuerpo de la peticion
        $arrParam=$this->validateParams();
        if($arrParam==false){
            $this->badRequest($jsonObject);
            return $jsonObject->createJSON();
        }
        
        $pOrg = new paloSantoOrganization($arrConf['elastix_dsn']["elastix"]);
        $idOrg=$pOrg->createOrganization($arrParam["name"],$arrParam["domain"],$arrParam["country"],$arrParam["city"],$arrParam["address"],$arrParam["country_code"],$arrParam["area_code"],$arrParam["quota"],$arrParam["email_contact"],$arrParam["numUser"],$arrParam["numExtensions"],$arrParam["numQueues"],$arrParam["org_user_pswd"]);
        if($idOrg===false){
            header("HTTP/1.1 500 Internal Server Error");
            $jsonObject->set_status("ERROR");
            $jsonObject->set_message(array("organization"=>false,"user"=>false));
            $jsonObject->set_error($pOrg->errMsg);
        }else{
            header('HTTP/1.1 201 Created');
            Header('Location: /rest.php/organization/organization/'.$idOrg);
            $jsonObject->set_status("OK");
            $jsonObject->set_message(array("organization"=>true,"user"=>true));
        }
        return $jsonObject->createJSON();
    }
    
    /*conjunto de paramatros que pueden venir dentro del cuarpo de la peticion
    * required
    *   domain string  
    *   email_contact string
    *   org_user_pswd string
    * no required
    *   name string
    *   country string
    *   city string
    *   address string
    *   country_code string
    *   area_code digit
    *   quota digit default 30
    *   numUser digit default 0 
    *   numExtensions digit default 0
    *   numQueues digit default 0
    *   send_email digit default 0
    */
    function validateParams(){
        $error=array();
        $req="Required Field: ";
        $bf="Bad Format: ";
        
        //parametros requeridos
        if(!isset($_POST["domain"])){
            $error[]="domain";
        }
        if(!isset($_POST["email_contact"])){
            $error[]="email_contact";
        }
        if(!isset($_POST["org_user_pswd"])){
            $error[]="org_user_pswd";
        }
        if(count($error)!=0){
            $this->errMsg=$req.implode(",",$error);
            return false;
        }
        
        $arrParam=array();
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/",$_POST["domain"])){
            $error[]="domain";
        }else
            $arrParam["domain"]=$_POST["domain"];
            
        if(!preg_match("/^[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*@[a-z0-9]+([\._\-]?[a-z0-9]+)*(\.[a-z0-9]{2,4})+$/",$_POST["email_contact"])){
            $error[]="email_contact";
        }else
            $arrParam["email_contact"]=$_POST["email_contact"];
            
        if(!isStrongPassword($_POST["org_user_pswd"])){
            $error[]="org_user_pswd";
        }else
            $arrParam["org_user_pswd"]=$_POST["org_user_pswd"];
            
        $arrParam["name"]=empty($_POST["name"])?$arrParam["domain"]:$_POST["name"];
        $arrParam["country"]=isset($_POST["country"])?$_POST["country"]:"";
        $arrParam["city"]=isset($_POST["city"])?$_POST["city"]:"";
        $arrParam["address"]=isset($_POST["address"])?$_POST["address"]:"";
        $arrParam["country_code"]=empty($_POST["country_code"])?"1":$_POST["country_code"];
        $arrParam["area_code"]=empty($_POST["area_code"])?"0":$_POST["area_code"];
        
        if(isset($_POST["quota"])){
            if(!ctype_digit($_POST["quota"]) || ($_POST["quota"]+0)==0){
                $error[]="quota (digit > 0)";
            }else
                $arrParam["quota"]=$_POST["quota"]; 
        }else
            $arrParam["quota"]="30";
            
        if(isset($_POST["numUser"])){
            if(!ctype_digit($_POST["numUser"])){
                $error[]="numUser (digit)";
            }else
                $arrParam["numUser"]=$_POST["numUser"];
        }else
            $arrParam["numUser"]="0";
            
        if(isset($_POST["numExtensions"])){
            if(!ctype_digit($_POST["numExtensions"])){
                $error[]="numExtensions (digit)";
            }elseif(($_POST["numExtensions"]<$arrParam["numUser"] && $arrParam["numUser"]!=0 && $_POST["numExtensions"]!=0)  || ($arrParam["numUser"]==0 && $_POST["numExtensions"]!=0))
                $error[]="numExtensions (numExtensions>=numUser)";
            else
                $arrParam["numExtensions"]=$_POST["numExtensions"];
        }else
            $arrParam["numExtensions"]="0";
            
        if(isset($_POST["numQueues"])){
            if(!ctype_digit($_POST["numQueues"])){
                $error[]="numQueues (digit)";
            }else
                $arrParam["numQueues"]=$_POST["numQueues"];
        }else
            $arrParam["numQueues"]="0";
            
        $arrParam["send_email"]=empty($_POST["send_email"])?false:true;
            
        if(count($error)>0){
            $this->errMsg=$bf.implode(",",$error);
            return false;
        }
        return $arrParam;
    }
}

class orgStatus extends orgREST
{
    protected $arrIdOrgs;
    
    function orgStatus($arrIdOrgs=null){
        parent::__construct();
        $this->arrIdOrgs=$arrIdOrgs;
    } 
    
    function HTTP_GET(){
        global $arrConf;
        $jsonObject = new PaloSantoJSON();
        
        if(!$this->isSuperAdmin()){
            $this->invalidCredentials($jsonObject);
            return $jsonObject->createJSON();
        }
        $this->setSession();
        
        $validOrgs=array();
        if(is_array($this->arrIdOrgs)){
            foreach($this->arrIdOrgs as $idOrg){
                if($this->validateIdOrg($idOrg) && $idOrg!="1"){
                    $validOrgs[]=$idOrg;
                }
            }
        }
        
        if(is_array($this->arrIdOrgs)){
            if(count($validOrgs)==0){ //ningun id pasado en la peticion es valido. Devolvemos 404 not found
                $this->resourceNotExis($jsonObject);
                return $jsonObject->createJSON();
            }
        }else
            $validOrgs=null;
        
        $arrOrg=array();
        $pOrg = new paloSantoOrganization($arrConf['elastix_dsn']["elastix"]);
        $sBaseUrl = '/rest.php/organization/organization/state';
        $result=$pOrg->getbunchOrganizationState($validOrgs);
        if($result===false){
            $this->error=$pOrg->errMsg;
            $this->errSever($jsonObject);
            return $jsonObject->createJSON();
        }else{       
            foreach($result as $x => $res){
                $arrOrg["organization"][$x]["id"]=$res["id"];
                $arrOrg["organization"][$x]["state"]=$res["state"];
                $arrOrg["organization"][$x]["since"]=$res["since"];
                $arrOrg["organization"][$x]["url"]=$sBaseUrl."/".$res["id"];
            }
        }
        
        if(count($arrOrg)==0){
            $this->resourceNotExis($jsonObject);
            return $jsonObject->createJSON();
        }else{
            $json = new Services_JSON();
            return $json->encode($arrOrg);
        }
    }
    
    //este metodo cambia el estado de un conjunto de organizaciones dado su id o dominio
    //id y dominios separados por ';'
    //curl -X PUT -k -d state=suspend -u user:password https://host/rest.php/organization/organization/state/id1[;id2;dominio2;domino3;id3]
    function HTTP_PUT(){
        global $arrConf;
        $jsonObject = new PaloSantoJSON();
        
        if(!$this->isSuperAdmin()){
            $this->invalidCredentials($jsonObject);
            return $jsonObject->createJSON();
        }
        $this->setSession();
        
        if(!$this->validateContentType($jsonObject)){
            return $jsonObject->createJSON();
        }
        
        $validOrgs=array();
        $domainOrgs=array();
        if(is_array($this->arrIdOrgs)){
            foreach($this->arrIdOrgs as $idOrg){
                if($this->validateIdOrg($idOrg) && $idOrg!="1"){
                    $validOrgs[]=$idOrg;
                }elseif(preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $idOrg)){
                    $domainOrgs[]=$idOrg;
                }
            }
        }else{
            $this->methodNoAllowed(array("GET"));
            exit;
        }
        
        $putvars=$state=null;
        parse_str(file_get_contents('php://input'), $putvars);
        if(isset($putvars["state"])){
            if($putvars["state"]=="suspend" || $putvars["state"]=="unsuspend" || $putvars["state"]=="terminate"){
                $state=$putvars["state"];
            }
        }
        
        if(is_null($state)){
            $this->errMsg="Field state bad value. Supported values are (suspend|unsuspend|terminate)";
            $this->badRequest($jsonObject);
            return $jsonObject->createJSON();
        }
        
        $pOrg=new paloSantoOrganization($arrConf['elastix_dsn']["elastix"]);
        if(count($domainOrgs)>0){
            foreach($domainOrgs as $domain){
                $result=$pOrg->getIdOrgByDomain($domain);
                if($result!=false){
                    $validOrgs=$result["id"];
                }
            }
        }
        
        if(count($validOrgs)==0){ //ningun id pasado en la peticion es valido. Devolvemos 404 not found
            $this->resourceNotExis($jsonObject);
            return $jsonObject->createJSON();
        }
        
        if($pOrg->getbunchOrganizationState($validOrgs)==false){
            $this->resourceNotExis($jsonObject);
            return $jsonObject->createJSON();
        }
        
        $result=$pOrg->changeStateOrganization($validOrgs,$state);
        if($result==false){
            $this->error=$pOrg->errMsg;
            $this->errSever($jsonObject);
            return $jsonObject->createJSON();
        }else{
            header('HTTP/1.1 200 Ok');
            $jsonObject->set_status("OK");
            $jsonObject->set_message("State of request organizations have been updated successfully");
            return $jsonObject->createJSON();
        }
    }
}
?>