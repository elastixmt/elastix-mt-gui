<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 3.0.0                                                |
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
  | Some functions within this class or script that implements an	     | 	
  | asterisk dialplan are based in FreePBX code.			             |
  | FreePBX® is a Registered Trademark of Schmooze Com, Inc.   		     |
  | http://www.freepbx.org - http://www.schmoozecom.com 		         |
  +----------------------------------------------------------------------+
  $Id: paloSantoAnnouncement.class.php,v 1.1 2014-03-12 Bruno Macias bmacias@elastix.org Exp $ */

class paloSantoAnnouncement extends paloAsteriskDB{
    protected $code;
    protected $domain;

    function paloSantoAnnouncement(&$pDB,$domain)
    {
       parent::__construct($pDB);
        
        if(!preg_match("/^(([[:alnum:]-]+)\.)+([[:alnum:]])+$/", $domain)){
            $this->errMsg="Invalid domain format";
        }else{
            $this->domain=$domain;

            $result=$this->getCodeByDomain($domain);
            if($result==false){
                $this->errMsg .=_tr("Can't create a new instace of paloSantoAnnouncement").$this->errMsg;
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
                $this->errMsg .=_tr("Can't create a new instace of paloSantoAnnouncement").$this->errMsg;
            }else{
                $this->code=$result["code"];
            }
        }
    }
    
    function getDomain(){
        return $this->domain;
    }
    
    function validateDomainPBX(){
        if(is_null($this->code) || is_null($this->domain))
            return false;
        return true;
    }
    
    function getNumAnnouncement($domain=null,$announcement_name=null){
        $where=array();
        $arrParam=null;

        $query="SELECT count(id) from announcement";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($announcement_name) && $announcement_name!=''){
            $where[]=" UPPER(description) like ?";
            $arrParam[]="%".strtoupper($announcement_name)."%";
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

    
    function getAnnouncement($domain=null,$announcement_name=null,$limit=null,$offset=null){
        $where=array();
        $arrParam=null;

        $query="SELECT *, (SELECT name from recordings where uniqueid=recording_id) recording_name from announcement";
        if(isset($domain) && $domain!='all'){
            $where[]=" organization_domain=?";
            $arrParam[]=$domain;
        }
        if(isset($announcement_name) && $announcement_name!=''){
            $where[]=" UPPER(description) like ?";
            $arrParam[]="%".strtoupper($announcement_name)."%";
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

    function getAnnouncementById($id){
        if (!preg_match('/^[[:digit:]]+$/', "$id")) {
            $this->errMsg = _tr("Invalid Announcement ID");
            return false;
        }

        $query="SELECT * from announcement where id=? and organization_domain=?";
        $result=$this->_DB->getFirstRowQuery($query,true,array($id,$this->domain));
        
        if($result===false){
            $this->errMsg=$this->_DB->errMsg;
            return false;
        }elseif(count($result)>0){
            return $result;
        }else
              return false;
    }
    
    function createNewAnnouncement($arrProp){
        if(!$this->validateDomainPBX()){
            $this->errMsg=_tr("Invalid Organization");
            return false;
        }
    
        $query="INSERT INTO announcement (";
        $arrOpt=array();
        
        $query .="organization_domain,";
        $arrOpt[count($arrOpt)]=$this->domain;

        //debe haberse seteado description
        if(!isset($arrProp["description"]) || $arrProp["description"]==""){
            $this->errMsg=_tr("Field 'Description' can't be empty");
            return false;
        }else{
            $query .="description,";
            $arrOpt[count($arrOpt)]=$arrProp["description"];
        }
        
        if(isset($arrProp["allow_skip"])){
            $query .="allow_skip,";
            $arrOpt[count($arrOpt)]=$arrProp["allow_skip"];
        }

        if(isset($arrProp["return_ivr"])){
            $query .="return_ivr,";
            $arrOpt[count($arrOpt)]=$arrProp["return_ivr"];
        }
        
        if(isset($arrProp["noanswer"])){
            $query .="noanswer,";
            $arrOpt[count($arrOpt)]=$arrProp["noanswer"];
        }
        
        if(isset($arrProp["repeat_msg"])){
            $query .="repeat_msg,";
            $arrOpt[count($arrOpt)]=$arrProp["repeat_msg"];
        }

        if(isset($arrProp["recording_id"])){
            if($arrProp["recording_id"]!="none"){
                if($this->getFileRecordings($this->domain,$arrProp["recording_id"])==false){
                    $arrProp["recording_id"]="none";
                }
            }
            $query .="recording_id,";
            $arrOpt[count($arrOpt)]=$arrProp["recording_id"];
        }
        
       if(isset($arrProp["destination"])){
            if($this->validateDestine($this->domain,$arrProp["destination"])!=false){
                $query .="destination,goto";
                $arrOpt[count($arrOpt)]=$arrProp["destination"];
                $tmp=explode(",",$arrProp["destination"]);
                $arrOpt[count($arrOpt)]=$tmp[0];
            }else{
                $this->errMsg="Invalid destination";
                return false;
            }
        }

        $query .=")";
        $qmarks = "(";
        for($i=0;$i<count($arrOpt);$i++){
            $qmarks .="?,"; 
        }
        $qmarks=substr($qmarks,0,-1).")"; 
        $query = $query." values".$qmarks;
        $result=$this->executeQuery($query,$arrOpt);
                
        if($result==false)
            $this->errMsg=$this->errMsg;
        return $result; 
    }

    function updateAnnouncementPBX($arrProp){
        $query="UPDATE announcement SET ";
        $arrOpt=array();

        $result=$this->getAnnouncementById($arrProp["id"]);
        if($result==false){
            $this->errMsg=_tr("Announcement doesn't exist").$this->errMsg;
            return false;
        }
        $idAnnouncement=$result["id"];
        
        //debe haberse seteado description
        if(!isset($arrProp["description"]) || $arrProp["description"]==""){
            $this->errMsg=_tr("Field 'Description' can't be empty");
            return false;
        }else{
            $query .="description=?,";
            $arrOpt[count($arrOpt)]=$arrProp["description"];
        }
        
        if(isset($arrProp["allow_skip"])){
            $query .="allow_skip=?,";
            $arrOpt[count($arrOpt)]=$arrProp["allow_skip"];
        }

        if(isset($arrProp["return_ivr"])){
            $query .="return_ivr=?,";
            $arrOpt[count($arrOpt)]=$arrProp["return_ivr"];
        }
        
        if(isset($arrProp["noanswer"])){
            $query .="noanswer=?,";
            $arrOpt[count($arrOpt)]=$arrProp["noanswer"];
        }
        
        if(isset($arrProp["repeat_msg"])){
            $query .="repeat_msg=?,";
            $arrOpt[count($arrOpt)]=$arrProp["repeat_msg"];
        }

        if(isset($arrProp["recording_id"])){
            if($arrProp["recording_id"]!="none"){
                if($this->getFileRecordings($this->domain,$arrProp["recording_id"])==false){
                    $arrProp["recording_id"]="none";
                }
            }
            $query .="recording_id=?,";
            $arrOpt[count($arrOpt)]=$arrProp["recording_id"];
        }
        
       if(isset($arrProp["destination"])){
            if($this->validateDestine($this->domain,$arrProp["destination"])!=false){
                $query .="destination=?,goto=?";
                $arrOpt[count($arrOpt)]=$arrProp["destination"];
                $tmp=explode(",",$arrProp["destination"]);
                $arrOpt[count($arrOpt)]=$tmp[0];
            }else{
                $this->errMsg="Invalid destination";
                return false;
            }
        }       
        
        //caller id options                
        $query = $query." WHERE id=?"; 
        $arrOpt[count($arrOpt)]=$idAnnouncement;
        $result=$this->executeQuery($query,$arrOpt);
        if($result==false)
            $this->errMsg=$this->errMsg;
        return $result; 
         
    }


    function deleteAnnouncement($id){
        $result=$this->getAnnouncementById($id);
        if($result==false){
            $this->errMsg=_tr("Announcement doesn't exist").$this->errMsg;
            return false;
        }
        
        $query="DELETE from announcement where id=?";
        if($this->executeQuery($query,array($id))){
            return true;
        }else{
            $this->errMsg = _tr("Announcement can't be deleted.").$this->errMsg;
            return false;
        } 
    }
    
    function createDialplanAnnouncement(&$arrFromInt)
    {
        if (is_null($this->code) || is_null($this->domain)) return false;
            
        $arrAnnouncement = $this->getAnnouncement($this->domain);
        if ($arrAnnouncement === false) {
            $this->errMsg = _tr("Error creating dialplan for Announcement. ").$this->errMsg; 
            return false;
        }

        $arrContext = array();
        
        foreach ($arrAnnouncement as $value) {
            $arrExt = array();
            
            /* Archivo para reproducir como Playback sin interrupción, o como 
             * Background mientras se espera una tecla */
            $recording_file = $this->getFileRecordings($this->domain, $value["recording_id"]);
            
            /* Tecla de teclado teléfono a presionar para repetir mensaje, o 
             * FALSE si la característica está desactivada. */
            $keypress_for_repeat = ($value['repeat_msg'] == 'no') ? FALSE : $value['repeat_msg'];
            
            /* Bandera para permitir que presionar una tecla corte la 
             * reproducción del audio indicado. */
            $allow_skip = ($value['allow_skip'] != 'no');
            
            /* Bandera para regresar al IVR que envió llamada a este anuncio.
             * Esto requiere que el IVR setee la variable de contexto 
             * IVR_CONTEXT. Si ningún contexto anterior seteó esta bandera, se
             * salta al destino seleccionado en lugar de a un IVR de vuelta. */
            $return_ivr = ($value['return_ivr'] != 'no');
            
            /* Bandera para dejar sin contestar la llamada que pasa por el anuncio */
            $noanswer       = ($value['noanswer'] != 'no');
            
            /* Cargar [[contexto,]extensión,]prioridad a saltar luego de 
             * terminar de reproducir el anuncio */
            if (isset($value["destination"])) {
                $goto = $this->getGotoDestine($this->domain, $value["destination"]);
                
                // Si el tipo de destino es desconocido, se cuelga la llamada
                if (!$goto) $goto = 'h,1';
            }
            
            /* Tipo de salto a hacer según si regresar o no a IVR. Según la 
             * bandera, se instancia un ext_gotoif o un extension. */
            $rfl = new ReflectionClass($return_ivr ? 'ext_gotoif' : 'extension');
            $rflargs = $return_ivr
                ? array('$["x${IVR_CONTEXT}" = "x"]', $goto.':${IVR_CONTEXT},return,1') 
                : array("Goto(".$goto.")");
            
            if (!$noanswer) {
                // Contestar la llamada si no ha sido contestada previamente
                $arrExt[] = new paloExtensions('s', new ext_gotoif('$["${CDR(disposition)}" = "ANSWERED"]','begin'),1);
                $arrExt[] = new paloExtensions('s', new ext_answer(''));
                $arrExt[] = new paloExtensions('s', new ext_wait('1'));
            }
            $arrExt[] = new paloExtensions('s', new ext_noop('Playing announcement '.$value['description']),($noanswer)?"1":"n","begin");
        
            if ($allow_skip || $keypress_for_repeat) {
                /* Se permite interacción de teclado. Si la interacción es para 
                 * repetir mensaje, se espera 1 segundo para introducir 
                 * extensión de repetir luego de reproducir audio de anuncio.
                 * La reproducción de audio explícitamente no contesta la 
                 * llamada, y puede ser interrumpido por un dígito. */
                if ($keypress_for_repeat)
                    $arrExt[] = new paloExtensions('s', new ext_responsetimeout(1));
                $arrExt[] = new paloExtensions('s', new ext_background($recording_file.',nm'),"n","play");
                if ($keypress_for_repeat)
                    $arrExt[]=new paloExtensions('s', new ext_waitexten(''));
            } else {
                // No se permite interacción de teclado
                $arrExt[] = new paloExtensions('s', new ext_playback($recording_file.',noanswer'));
            }
            if (!$keypress_for_repeat) {
                // Si no hay tecla para repetir anuncio, se sale de contexto ahora
                $arrExt[] = new paloExtensions('s', $rfl->newInstanceArgs($rflargs));
            }
            if ($allow_skip) {
                /* Si usuario presiona tecla para saltarse anuncio, se sale de 
                 * contexto ahora. Pero si se presionó la tecla asignada para
                 * repetir, esta tecla tomará prioridad (véase abajo) */
                $arrExt[] = new paloExtensions('_X', new ext_noop('Announcement skipped'),"1");
                $arrExt[] = new paloExtensions('_X', $rfl->newInstanceArgs($rflargs));
            }
            
            // Se salta a etiqueta de reproducción en caso de tecla para repetir
            if ($keypress_for_repeat)
                $arrExt[] = new paloExtensions($keypress_for_repeat, new ext_goto('s,play'),"1");
            
            /* Si se permite interacción, se sale del contexto en caso de tecla 
             * inválida. En caso de repetición activada, además se sale de 
             * contexto en caso de timeout. */
            if ($keypress_for_repeat)
                $arrExt[] = new paloExtensions("t", $rfl->newInstanceArgs($rflargs), "1");
            if ($allow_skip || $keypress_for_repeat)
                $arrExt[] = new paloExtensions('i', $rfl->newInstanceArgs($rflargs), "1");
            
            //creamos context app-announcement
            $context = new paloContexto($this->code,"app-announcement-{$value['id']}");
            // TODO: esta condición no es posible porque el objeto no es el error, new no devuelve bool
            if ($context === false)
                $context->errMsg = "ext-announcement. Error: ".$context->errMsg;
            else{
                $context->arrExtensions = $arrExt;
                $arrContext[]           = $context;
            }
        }
    
        return $arrContext;
    }
}
?>