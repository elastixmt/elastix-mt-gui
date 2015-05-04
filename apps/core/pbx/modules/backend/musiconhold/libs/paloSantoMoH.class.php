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
    global $arrConf;
    
class paloSantoMoH extends paloAsteriskDB{
    protected $code;
    protected $domain;
    private $_mohdir;

    function paloSantoMoH(&$pDB,$domain)
    {
        parent::__construct($pDB);
        $this->_mohdir = '/var/lib/asterisk/moh/';  // TODO: leer de musiconhold.conf
        
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
           // $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloQueuePBX").$this->errMsg;
            }else{
                $this->code=$result["code"];
            }
        }
    }

    private function _buildMoHDirectory()
    {
    	return $this->_mohdir.(($this->domain == '') ? '' : $this->domain.'/');
    }

    function getNumMoH($domain=null,$name=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(name) from musiconhold";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($name) && $name!=''){
            $where[]=" UPPER(description) like ?";
            $arrParam[]="%".strtoupper($name)."%";
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
        
        $result=$this->_DB->getFirstRowQuery($query,false,$arrParam);
        if($result==false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }else
            return $result[0];
    }

    
    function getMoHs($domain=null,$name=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;

        $query="SELECT name, description, mode,directory, application, sort, organization_domain from musiconhold";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($name) && $name!=''){
            $where[]=" UPPER(description) like ?";
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

    //debo devolver un arreglo que contengan los parametros del MoH
    function getMoHByClass($class){
        $where="";
        $arrParam=array($class);
       
        if (!preg_match('/^([[:alnum:]-_\.])+$/', "$class")) {
            $this->errMsg = _tr("Invalid MoH Class");
            return false;
        }

        $directory = $this->_buildMoHDirectory();
        if ($this->domain != '') {
            $arrParam[] = $this->domain;
            $where = ' and organization_domain = ?';
        }

        $query="SELECT name as class, description as name, mode as mode_moh,directory, application, sort, format from musiconhold where name=? $where";
        $result=$this->_DB->getFirstRowQuery($query,true,$arrParam);
        
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }elseif(count($result)>0){
            $directory .=$result["name"];
            $result["listFiles"]=array();
            if($result["mode_moh"]=="files"){
                if(is_dir($directory)){
                    $arrFiles=scandir($directory);
                    foreach($arrFiles as $file){
                        if($file!="." && $file!=".."){
                            if(is_file($directory."/".$file)){
                                $result["listFiles"][]=$file;
                            }
                        }
                    }
                }
            }
            return $result;
        }else
            return $result;
    }
    
    function existMoH($class){
        $query="SELECT count(name) from musiconhold where name=?";
        $result=$this->_DB->getFirstRowQuery($query,true,$arrParam);
        if($result===false || count($result)>0){
            $this->errMsg=$this->_DB->errMsg;
            return true;
        }else{
            return false;
        }
    }
    
    /**
        funcion que crea un nueva ruta entrante dentro del sistema
    */
    function createNewMoH($arrProp){
        $query="INSERT INTO musiconhold (organization_domain,name,description,mode,directory,application,sort,format) values (?,?,?,?,?,?,?,?)";
        $arrOpt=array();
       
        if($this->domain!=""){
            if(empty($this->code)){
                $this->errMsg=_tr("Invalid Organization");
                return false;
            }
            $class=$this->code."_".$arrProp["name"];
        }else{
            $this->domain="";
            $class=$arrProp["name"];
        }

        //debe haberse seteado un nombre
        if (!preg_match('/^([[:alnum:]-_\.])+$/', "$class")) {
            $this->errMsg = _tr("Invalid MoH Class");
            return false;
        }
        
        if($this->existMoHClass($class)){
            $this->errMsg=_tr("Already exist another MoH Class woth the same name. ").$this->errMsg;
            return false;
        }
        
        if($arrProp["mode"]=="files"){
            $mode="files";
            $application="";
            $format="";
            $sort=$arrProp["sort"];
            $directory = $this->_buildMoHDirectory().$arrProp['name'];
        }else{
            $mode="custom";
            if($arrProp["application"]==""){
                $this->errMsg=_tr("Field 'application' can't be empty").$this->errMsg;
                return false;
            }
            $application=$arrProp["application"];
            $format=$arrProp["format"];
            $directory="";
            $sort="";
        }
        
        
        $result=$this->executeQuery($query,array($this->domain,$class,$arrProp["name"],$mode,$directory,$application,$sort,$format));
                
        if($result==false){
            $this->errMsg=$this->errMsg;
            return false;
        }else{
            if($this->createDirMoH($arrProp["name"])==false)
                return false;
            else
                return true;
        }
    }
    
    private function createDirMoH($name_class){
        $sComando = "/usr/bin/elastix-helper asteriskconfig createMoHDir $name_class ";
        
        if($this->domain!=""){
            $sComando .=$this->domain;
        }
        $sComando .='  2>&1';
        
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }
    
    //$name -> nombre de la clase sin prefijo dela organizacion
    function UploadFile($name){
        $where=$error="";
        $param=array($name,"files");
        
        if (!preg_match('/^([[:alnum:]-_\.])+$/', "$name")) {
            $this->errMsg = _tr("Files can't be uploaded. ")._tr("Invalid MoH Class");
            return false;
        }
        
        $directory = $this->_buildMoHDirectory().$name;
        if ($this->domain != '') {
            $param[] = $this->domain;
            $where = ' and organization_domain = ?';
        }
                
        $query="SELECT directory from musiconhold where description=? and mode=? $where";
        $result=$this->_DB->getFirstRowQuery($query,true,$param);
        if($result===false || count($result)==0){
            $this->errMsg=_tr("Files can't be uploaded. ")._tr("MoH Class doens't exist. ").$this->_DB->errMsg;
            return false;
        }
        
        if(!is_dir($directory)){
            if($this->createDirMoH($name)==false)
                return false;
        }
        
        if (isset($_FILES['file'])) {
            $count=count($_FILES['file']['name']);
            for($i=0;$i<$count;$i++){
                if($_FILES['file']['tmp_name'][$i]!=""){
                    if (preg_match("/^(\w|-|\.|\(|\)|\s)+\.(wav|WAV|Wav|gsm|GSM|Gsm|Wav49|wav49|WAV49|mp3|MP3|Mp3)$/",$_FILES['file']['name'][$i])) {
                        if (!preg_match("/(\.php)/",$_FILES['file']['name'][$i])) {
                            $filenameTmp = $_FILES['file']['name'][$i];
                            $tmp_name = $_FILES['file']['tmp_name'][$i];
                            $filename = basename("$directory/$filenameTmp");
                            $date=date("YMd_His");
                            $tmpFile=$date."_".$filename;
                     
                            if (move_uploaded_file($tmp_name, "$directory/$tmpFile"))
                            {
                                $info=pathinfo($filename);
                                $file_sin_ext=$info["filename"];
                                $type=$this->getTipeOfFile("$directory/$tmpFile");
                                $continue=true;
                                
                                if($type==false){
                                    $error .=$this->errMsg;
                                    $continue=false;
                                }
                                
                                if($type=="audio/mpeg; charset=binary"){
                                    if($this->convertMP3toWAV($directory,$tmpFile,$file_sin_ext,$date)==false){
                                        $error .=$this->errMsg;
                                        $continue=false;
                                    }else{
                                        $filename=$file_sin_ext.".wav";
                                    }
                                }
                                if($continue){
                                    if($this->resampleMoHFiles($directory,$tmpFile,$filename)==false)
                                        $error .=_tr("Music can be resampled: ").$this->errMsg;
                                }
                            }else{
                                $error .=_tr("File could be uploaded: ").$_FILES['file']['name'][$i]." \n";
                            }
                        }else{
                            $error .=_tr("Possible file upload attack: ").$_FILES['file']['name'][$i]." \n";
                        }
                    }else{
                       $error .=_tr("Possible file upload attack: ").$_FILES['file']['name'][$i]." \n";
                    }
                }
            }
        }
        
        if($error!="")
            $this->errMsg=_tr("Some files can't be uploaded. ").$error;
    }
    
    private function getTipeOfFile($file){
        $mime_type="";
        $finfo = new finfo(FILEINFO_MIME, "/usr/share/misc/magic.mgc");
        if(is_file($file)){
            $mime_type = $finfo->file($file);
        }else{
            $this->errMsg = _tr("File doens't exist ").$file;
            return false;
        }
        return $mime_type;
    }
    
    private function convertMP3toWAV($base,&$tmpFile,$file_sin_ext,$prep){
        $output = $ret = NULL;
        
        $tmp=$tmpFile;
        $tmpFile=$prep."_".$file_sin_ext.".wav";
        //mpg123 -w outputFile inputFile
        exec("mpg123 -w '$base/$tmpFile' '$base/$tmp'", $output, $ret);
        if ($ret != 0) {
            unlink("$base/$tmp");
            $this->errMsg = implode('', $output);
            return FALSE;
        }else{
            unlink("$base/$tmp");
            return TRUE;
        }
    }
    
    private function resampleMoHFiles($base,$tmpFile,$filename){
      //  sox inputFile -r 8000 -c 1 outputFile
        $output = $ret = NULL;
        exec("sox '$base/$tmpFile' -r 8000 -c 1 '$base/$filename'", $output, $ret);
        if ($ret != 0) {
            unlink("$base/$tmpFile");
            $this->errMsg = implode('', $output);
            return FALSE;
        }else{
            unlink("$base/$tmpFile");
            return TRUE;
        }
    }
    
    function updateMoHPBX($arrProp){
        $class=$arrProp["class"];
        $param=array();
        $error="";
        $arrMoH=$this->getMoHByClass($class);
        if($arrMoH==false){
            $this->errMsg=_tr("MoH class doesn't exist");
            return false;
        }
        
        $query="Update musiconhold ";
        if($arrMoH["mode_moh"]=="files"){
            $query .="set sort=?";
            $param[]=$arrProp["sort"];
        }else{
            $query .="set application=?,format=?";
            if($arrProp["application"]==""){
                $this->errMsg=_tr("Field 'application' can't be empty");
                return false;
            }
            $param[]=$arrProp["application"];
            $param[]=$arrProp["format"];
        }
        
        $query .="where name=?";
        $param[]=$class;
        
        $result=$this->executeQuery($query,$param);
                
        if($result==false){
            $this->errMsg=$this->errMsg;
            return false;
        }else{
            //revisamos los archivos a ver si ahi alguno que el usuario desse eliminar de la clase
            $directory = $this->_buildMoHDirectory().$arrMoH['name'];
            
            $arrFiles=$arrMoH["listFiles"];
            $act_files=$arrProp["remain_files"];
            if(is_array($act_files)){
                $diffFiles=array_diff($arrFiles,$act_files);
                foreach($diffFiles as $file){
                    if(is_file($directory."/".$file)){
                        if(unlink($directory."/".$file)==false){
                            $error .="File $file couldn't be deleted";
                        }
                    }   
                }
            }
        }
        $this->errMsg = $error;
        return true;
    }

    function deleteMoH($class){
        $arrMoH=$this->getMoHByClass($class);
        if($arrMoH==false){
            $this->errMsg=_tr("MoH class doens't exist. ").$this->errMsg;
            return false;
        }
        
        $query="DELETE from musiconhold where name=?";
        if($this->executeQuery($query,array($class))){
            //eliminamos los archivos de audio y la carpeta correspondientes a la clase
            $directory = $this->_buildMoHDirectory().$arrMoH['name'];
                
            if(is_dir($directory)){
                foreach($arrMoH["listFiles"] as $file){
                    if(is_file($directory."/".$file)){
                        if(unlink($directory."/".$file)==false)
                            break;
                    }
                }
                if(rmdir($directory)==false){
                    $this->errMsg=$directory._tr(" couldn't be deleted from system");
                    return false;
                }
            }
            return true;
        }else{
            $this->errMsg="MoH clase can't be deleted.".$this->errMsg;
            return false;
        } 
    }
   
}
?>