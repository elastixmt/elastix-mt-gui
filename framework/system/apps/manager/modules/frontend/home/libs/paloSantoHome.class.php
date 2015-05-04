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
*/
  
global $arrConf;
require_once("libs/misc.lib.php");
require_once("configs/email.conf.php");

class paloHome
{
    
}
/*
  @author: paloImap,v 1 2013/05/09 01:07:03 Washington Reyes wreyes@palosanto.com Exp $
  @author: paloImap,v 2 2013/11/21 01:07:03 Rocio Mera rmera@palosanto.com Exp $ */
class paloImap {
    private $user;
    private $port;
    private $host;
    private $imap_ref;
    private $mailbox='INBOX';
    private $folders;
    private $default_folders = array('Sent','Drafts','Trash','Spam');
    private $sort_field = 'date';
    private $sort_order = 'DESC';
    private $default_charset = 'ISO-8859-1';
    private $struct_charset = NULL;
    private $offset = 0;
    private $message_by_page = 3;
    public $errMsg = '';
    private $connection; //contiene la la coneccion a un IMAP a un buzon
    
    public function paloImap($mailbox='INBOX',$host='',$port='', $default_folders=''){
        global $CYRUS;
        
        $this->host=empty($host)?$host:$CYRUS['HOST'];
        $this->port=empty($port)?$port:$CYRUS['PORT'];
        $this->mailbox=empty($mailbox)?'INBOX':$mailbox;
        if(is_array($default_folders) && count($default_folders)>0);
            $this->default_folders==$default_folders;
    }
    
    public function setMailbox($mailbox){
        $this->mailbox=(!isset($mailbox) || $mailbox=='' || $mailbox===false)?'INBOX':$mailbox;
    }
    
    public function getMailbox(){
        return $this->mailbox;
    }
    public function setDefaultFolders($arrFolders){
        $this->$default_folders=$arrFolders;
    }
    
    public function getConnection(){
        return $this->connection;
    }
    
    public function getImapref(){
        return $this->imap_ref;
    }
    
    public function getMessageByPage(){
        return $this->message_by_page;
    }
    
    public function getOffset(){
        return $this->offset;
    }
    
    public function setMessageByPage($message_by_page){
        $this->message_by_page=$message_by_page;
    }
    
    public function setOffset($offset){
        $this->offset=$offset;
    }
    
    public function getMsgByPage(){
        return $this->$message_by_page;
    }
    
    public function getErrorMsg(){
        return $this->errMsg;
    }
        
    public function login($user, $pass, $use_ssl=null, $validate_cert=false, $options = 0){
        //validamos la cuenta del usuario
        if(!preg_match("/^[a-z0-9]+([\._\-]?[a-z0-9]+[_\-]?)*@[a-z0-9]+([\._\-]?[a-z0-9]+)*(\.[a-z0-9]{2,4})+$/",$user)){
            $this->errMsg=_tr('Invalid Username');
            return false;
        }
        
        //validamos el password
        if($pass=='' || $pass===false){
            $this->errMsg=_tr('Password can not be empty');
            return false;
        }
        
        //TODO: revisar conecction usando ssl
        
        if($validate_cert){
            $cert_opt="validate-cert";
        }else{
            $cert_opt="novalidate-cert";
        }
        
        //validamos host, si no esta configurado usamos localhost
        $this->host=empty($this->host)?'localhost':$this->host;
        //validamos port, si no esta configurado usamos 143
        $this->port=empty($this->port)?'143':$this->port;
        
        $this->imap_ref = "{".$this->host.":".$this->port."/imap/novalidate-cert}";
        
        //el nombre dle buzon que se vaya a leer debe tener 
        $this->mailbox=(!isset($this->mailbox) || $this->mailbox=='' || $this->mailbox===false)?'INBOX':$this->mailbox;
        
        $str_connection=$this->imap_ref.@imap_utf7_encode($this->mailbox);
        
        $this->connection = @imap_open($str_connection, $user, $pass, $options);
        
        if(!$this->connection){
            $this->errMsg="Imap_open failed ".@imap_last_error();
            return false;
        }else
            return true;
    }

    /**
     * This function create the default mailbox
     */
    public function create_mailbox($folder) {
        $exist=false;
        //chequeamos que la carpeta no exista
        $list_mailbox=@imap_list($this->connection ,$this->imap_ref, "*");
        if (is_array($list_mailbox)) {
            foreach ($list_mailbox as $mailbox) {
                if(@imap_utf7_decode($mailbox)==$this->imap_ref.$folder){
                    $exist=true;
                }
            }
        } else {
            $this->errMsg="Imap_list failed: " . @imap_last_error();
            return false;
        }
        
        if(!$exist){
            $result=@imap_createmailbox($imap_stream, @imap_utf7_encode($this->imap_ref.$folder));
            if(!$result){
                $this->errMsg="Imap_createmailbox failed: " . @imap_last_error();
            }
            return $result;
        }else{
            $this->errMsg=_tr("Already exist a folder with teh same name");
            return false;
        }
    }
    
    /**
     * This function return the list of mailbox
     * whit extra info has the number of message has been read.
     * Also this function check if exist the default folders. If any of default_folders
     * does not exist this function try to create this one
     */
    public function getMailboxList($searh_pattern=''){
        $mailboxs=array();
        $mailbox_list=@imap_list($this->connection,$this->imap_ref,"*");
        if (is_array($mailbox_list)) {
            //loop through rach array index
            foreach ($mailbox_list as $folder) {
                //remove any slashes
                $folder = trim(stripslashes($folder));
        
                //remove $this->imap_ref from the folderName
                $folderName = str_replace($this->imap_ref, '', $folder);
  
                $mailboxs[]=@imap_utf7_decode($folderName);
                
                //procedemos a subscribir los mailboxs
                @imap_subscribe($this->connection ,$folder);
            }
                        
            //chequemos que existan las carpetas por default
            //en caso de no existir las borramos
            $list_create=array_diff($this->default_folders,$mailboxs);
            foreach($list_create as $folder){
                $result=@imap_createmailbox($this->connection, @imap_utf7_encode($this->imap_ref.$folder));
                if(!$result){
                    $this->errMsg="Imap_createmailbox failed: " . @imap_last_error();
                    return false;
                }else{
                    $mailboxs[]=@imap_utf7_decode($folder);
                }
                
                //procedemos a subscribir los mailboxs
                @imap_subscribe($this->connection ,@imap_utf7_encode($this->imap_ref.$folder));
            }
            
            return $mailboxs;
        } else {
            $this->errMsg="Imap_list failed: " . @imap_last_error();
            return false;
        }
    }
    
    public function close_mail_connection() {
        @imap_close($this->connection);
    }
    
    public function getNumMails($arrFilter,&$listUID){
        $numMessage=0;
        //This function performs a search on the mailbox currently opened in the given IMAP stream.
        //Returns an array of UIDs of mails.
        $param='ALL';
        if(isset($arrFilter['filter_view'])){
            switch($arrFilter['filter_view']){
                case 'seen':
                    $param=strtoupper($arrFilter['filter_view']);
                    break;
                case 'unseen':
                    $param=strtoupper($arrFilter['filter_view']);
                    break;
                case 'flagged':
                    $param=strtoupper($arrFilter['filter_view']);
                    break;
                case 'unflagged':
                    $param=strtoupper($arrFilter['filter_view']);
                    break;
            }
        }
        
        $emailnum = @imap_sort($this->connection,SORTARRIVAL,0,SE_UID,$param);
        if($emailnum!=false){
            $listUID=$emailnum;
            $numMessage=count($listUID);
        }
        return $numMessage; 
    }
    
    public function readMails($listUID){
        $emails=array();
        if(is_array($listUID)){
            $start=(count($listUID)-1)-$this->offset;
            $end=$start-$this->message_by_page;
            if($end<=0){
                $end=-1;
            }
            for($i=$start; $i > $end ; $i--){
                $overview = @imap_fetch_overview($this->connection,$listUID[$i],FT_UID);
                if($overview!==false && count($overview)>0){
                    
                    $emails[]= array("from" => isset($overview[0]->from)?$overview[0]->from:'',
                                    "subject" => isset($overview[0]->subject)?$overview[0]->subject:'',
                                    "date"=> isset($overview[0]->date)?substr($overview[0]->date,0,17):'',
                                    "UID"=>isset($overview[0]->uid)?$overview[0]->uid:0,
                                    "SEEN"=>isset($overview[0]->seen)?$overview[0]->seen:0,
                                    "FLAGGED"=>isset($overview[0]->flagged)?$overview[0]->flagged:0,
                                    "RECENT"=>isset($overview[0]->recent)?$overview[0]->recent:0,
                                    "ANSWERED"=>isset($overview[0]->answered)?$overview[0]->answered:0,
                                    "DELETED"=>isset($overview[0]->deleted)?$overview[0]->deleted:0,
                                    "DRAFT"=>isset($overview[0]->draft)?$overview[0]->draft:0);
                }
            }
        }
        return $emails;
    }
    
    public function moveMsgToFolder($current_folder,$new_folder,$listUID){
        if(is_array($listUID) && count($listUID)>0){
            if($new_folder==='' || $new_folder===false || !isset($new_folder)){
                $this->errMsg=_tr("Dest_mail_inv");
                return false;
            }
            
            if($current_folder==$new_folder){
                $this->errMsg=_tr("Dest_mail_inv");
                return false;
            }
            
            //procedemos a mover los mensaje a la nueva carpeta. Usamos la bandera 'CP_UID', porque
            //pasamos como parametros los UIDs de los mensajes en lugar de la secuencia
            $result=@imap_mail_move($this->connection, implode(",",$listUID), @imap_utf7_encode($new_folder) ,CP_UID);
            if($result==false){
                //algo paso devolvemos el error
                $this->errMsg="Imap_move failed: " . @imap_last_error();
                return false;
            }else{
                //despues d eusar las funciones imap_mail_move or imap_mail_copy or imap_delete es necesary usar la 
                //funcion imap_expunge
                @imap_expunge($this->connection); 
                return true;
            }
        }else{
            $this->errMsg=_tr("At_least_one");
            return false;
        }
    }
    
    public function markMsgFolder($tag,$listUID){
        if(is_array($listUID) && count($listUID)>0){
            $valid_tags=array('seen','unseen','flagged','unflagged');
            
            if(!in_array($tag,$valid_tags)){
                $this->errMsg=_tr("Invalid_tag");
                return false;
            }
        
            if($tag=='unseen' || $tag=='unflagged'){
                //en el caso de unseen y unflagged lo que debemos hacer es quitar los tags seen y flagged de los mensajes
                if($tag=='unseen')
                    $tag='seen';
                elseif($tag=='unflagged')
                    $tag='flagged';
                    
                return $this->flagMsg($tag,$listUID,'unset');
            }else{
                return $this->flagMsg($tag,$listUID,'set');
            }
        }else{
            $this->errMsg=_tr("At_least_one");
            return false;
        }
    }
    
    /**
     * This function given a list UID message set or unset a especified flag
     * from this message
     * @param string flag => key flag to set or unset
     * @param array $lisUID => UID message array
     * @param string $action => action to do. This can be set or unset
     * @param bool => true if the action is success else false
     */
    private function flagMsg($flag,$listUID,$action='set'){
        $valid_tags=array('seen','flagged','deleted','answered','draft');
        if(is_array($listUID) && count($listUID)>0){
            
            if(!in_array($flag,$valid_tags)){
                $this->errMsg=_tr("Invalid_tag");
                return false;
            }
            
            //The flags which you can set are \Seen, \Answered, \Flagged, \Deleted, and \Draft as defined by » RFC2060.
            $flag="\\".ucfirst($flag);
            if($action=='set'){
                $result=@imap_setflag_full($this->connection, implode(",",$listUID),$flag,ST_UID);
            }else{
                $result=@imap_clearflag_full($this->connection, implode(",",$listUID),$flag,ST_UID);
            }
            
            if($result==false){
                $this->errMsg="Imap_setflag failed: " . @imap_last_error();
            }
            return $result;
        }else{
            $this->errMsg=_tr("No_messages");
            return false;
        }
    }
    
    function deleteMsgTrash($listUID){
        if(is_array($listUID) && count($listUID)>0){
            //marcamos los mensajes para borrar
            if(!$this->flagMsg('deleted',$listUID)){
                return false;
            }
            
            //eliminamos los mensajes definitivamente del buzon
            @imap_expunge($this->connection); 
            return true;
        }else{
            $this->errMsg=_tr("At_least_one");
            return false;
        }
    }
    
    function readEmailMsg($uid){
        $message=array();
        
        //obtenemos el message number dado su id
        $msg_num=@imap_msgno($this->connection,$uid);
        //$msg_num no puede ser igual a 0
        if(empty($msg_num)){
            $this->errMsg=_tr('Messages does not exist');
            return false;
        }
        
        //leemos la cabecera del mensaje
        try{
            $header=@imap_headerinfo($this->connection,$msg_num);
        }catch(Exception $e){
             //no exist un mensaje con dicho uid
            $this->errMsg="imap_headerinfo failed: " . @imap_last_error();
            return false;
        }
        
        //TODO: parsear el arreglo de las etiquetas to, from, cc , bcc y replay_to 
        // y reemplazar con ese valor las etiquetas toaddress, fromaddress, ccaddress, bccaddress, reply_toaddress
        if(!empty($header)){
            //$message['header']['to']=$header->to;
            $message['header']['to']['tag']=_tr('To');
            $message['header']['to']['content']=trim(htmlentities($header->toaddress,ENT_COMPAT,'UTF-8'));
            //$message['header']['from']=$header->from;
            $message['header']['from']['tag']=_tr('From');
            $message['header']['from']['content']=trim(htmlentities($header->fromaddress,ENT_COMPAT,'UTF-8'));
            
            $message['header']['subject']=isset($header->subject)?trim(htmlentities($header->subject,ENT_COMPAT,'UTF-8')):_tr('No Subject');
            if($message['header']['subject']===''){
                $message['header']['subject']=_tr('No Subject');
            }
            
            if(isset($header->date)){
                $message['header']['date']['tag']=_tr('Date');
                $message['header']['date']['content']=$header->date;
            }
            
            if(isset($header->cc)){
                //$message['header']['cc']=$header->cc;
                $message['header']['cc']['tag']=_tr('CC');
                $message['header']['cc']['content']=trim(htmlentities($header->ccaddress,ENT_COMPAT,'UTF-8'));
            }
            
            if(isset($header->bcc)){
                //$message['header']['bcc']=$header->bcc;
                $message['header']['bcc']['tag']=_tr('BCC');
                $message['header']['bcc']['content']=trim(htmlentities($header->bccaddress,ENT_COMPAT,'UTF-8'));
            }
            
            if(isset($header->reply_to)){
                $message['header']['reply_to']=$header->reply_toaddress;
            }
        }else{
            //no exist un mensaje con dicho uid
            $this->errMsg=_tr('Messages does not exist');
            return false;
        }
        
        //leemos el mensaje en si
        $pMessage=new paloImapMessage($this->connection,$uid,$this->mailbox);
        $pMessage->imapReadMesg();
        
        $message['attachment']=$pMessage->getAttachments();
        $message['body']=$pMessage->getBody();
        
        return $message;
    }
    
    public function createMailbox($mailbox){
        $mailbox=trim($mailbox);
        if(!preg_match("/^[[:alnum:]-_[:blank:]]+$/",$mailbox)){
            $this->errMsg=_tr("Folder Name is Invalid");
            return false;
        }
        $result=@imap_createmailbox($this->connection, @imap_utf7_encode($this->imap_ref.$mailbox));
        if(!$result){
            $this->errMsg="Imap_createmailbox failed: " . @imap_last_error();
            return false;
        }
        
        //procedemos a subscribir los mailboxs
        @imap_subscribe($this->connection , @imap_utf7_encode($this->imap_ref.$mailbox));
        return true;
    }
    
    public function appendMessage($mailbox,$string_msg,$options=''){
        if($mailbox!='' && is_string($mailbox)){
            if($string_msg!=''){
                if(!@imap_append($this->connection, $this->imap_ref.$mailbox, $string_msg,$options)){
                    $this->errMsg="Imap_append failed: " . @imap_last_error();
                }else{
                    return true;
                }
            }else{
                $this->errMsg=_tr("Message Content can not be empty");
            }
        }else{
            $this->errMsg=_tr("Invalid Folder");
        }
        return false;
    }
    
    public function getRecentMessage(){
        $recent=@imap_num_recent($this->connection);
        if($recent===false){
            $this->errMsg="Imap_num_recent failed: " . @imap_last_error();
        }
        return $recent;
    }
    
    function getListInternalContacts() {
        global $arrCredentials,$arrConf;
        $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);

        $data = array($arrCredentials['id_organization']);
        $query="select acu.name, acu.username from acl_user acu ".
                    "join acl_group acg on acu.id_group = acg.id ".
                    "join organization org on acg.id_organization = org.id ".
                        "where org.id=? ORDER BY acu.name ASC";

        $result=$pDB->fetchTable($query,true,$data);
        if($result===FALSE){
            $this->errMsg = $pDB->errMsg;
            return false;
        }else{
            return $result;
        }
    }
    
    function getListExternalContacts() {
        global $arrCredentials,$arrConf;
        $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
        
        $data = array($arrCredentials['idUser'], $arrCredentials['id_organization'], $arrCredentials['idUser']);

        $query="(select concat (name,' ',last_name) as name, ".
                    "email as username from contacts where iduser=? AND email!='' AND email IS NOT NULL) ".
                "UNION (select concat (c.name,' ',c.last_name) as name, ".
                    "c.email as username from contacts c where c.iduser IN ".
                        "(select acu.id from acl_user acu ".
                            "join acl_group acg on acu.id_group = acg.id ".
                                "WHERE acg.id_organization = ? AND acu.id!=?) ".
                        " AND c.email!='' AND c.email IS NOT NULL AND c.status='isPublic') ORDER BY name ASC";

        $result=$pDB->fetchTable($query,true,$data);
        if($result===FALSE){
            $this->errMsg = $pDB->errMsg;
            return false;
        }else{
            return $result;
        }
    }
}

/**
    stdClass Object
    (
    [type] => 1
    [encoding] => 0
    [ifsubtype] => 1
    [subtype] => MIXED
    [ifdescription] => 0
    [ifid] => 0
    [ifdisposition] => 0
    [ifdparameters] => 0
    [ifparameters] => 1
    [parameters] => Array
        (
        [0] => stdClass Object
            (
                [attribute] => BOUNDARY
                [value] => bcaec54b516462cef304c7e9d5c3
            )
        )
    [parts] => Array
        (
        [0] => stdClass Object
            (
            [type] => 1
            [encoding] => 0
            [ifsubtype] => 1
            [subtype] => ALTERNATIVE
            [ifdescription] => 0
            [ifid] => 0
            [ifdisposition] => 0
            [ifdparameters] => 0
            [ifparameters] => 1
            [parameters] => Array
                (
                [0] => stdClass Object
                    (
                    [attribute] => BOUNDARY
                    [value] => bcaec54b516462ceeb04c7e9d5c1
                    )
                )
            [parts] => Array
                (
                [0] => stdClass Object
                    (
                    [type] => 0
                    [encoding] => 0
                    [ifsubtype] => 1
                    [subtype] => PLAIN
                    [ifdescription] => 0
                    [ifid] => 0
                    [lines] => 1
                    [bytes] => 2
                    [ifdisposition] => 0
                    [ifdparameters] => 0
                    [ifparameters] => 1
                    [parameters] => Array
                        (
                        [0] => stdClass Object
                            (
                            [attribute] => CHARSET
                            [value] => ISO-8859-1
                            )
                        )
                    )
                [1] => stdClass Object
                    (
                    [type] => 0
                    [encoding] => 0
                    [ifsubtype] => 1
                    [subtype] => HTML
                    [ifdescription] => 0
                    [ifid] => 0
                    [lines] => 1
                    [bytes] => 6
                    [ifdisposition] => 0
                    [ifdparameters] => 0
                    [ifparameters] => 1
                    [parameters] => Array
                        (
                        [0] => stdClass Object
                            (
                            [attribute] => CHARSET
                            [value] => ISO-8859-1
                            )
                        )
                    )
                )
            )
        [1] => stdClass Object
            (
            [type] => 3
            [encoding] => 3
            [ifsubtype] => 1
            [subtype] => ZIP
            [ifdescription] => 0
            [ifid] => 0
            [bytes] => 115464
            [ifdisposition] => 1
            [disposition] => ATTACHMENT
            [ifdparameters] => 1
            [dparameters] => Array
                (
                [0] => stdClass Object
                    (
                    [attribute] => FILENAME
                    [value] => weekly-reports.zip
                    )
                )
            [ifparameters] => 1
            [parameters] => Array
                (
                [0] => stdClass Object
                    (
                    [attribute] => NAME
                    [value] => weekly-reports.zip
                    )
                )
            )
        )
    )
*/
class paloImapMessage{
    private $app;
    private $paloImap; // connection open by imap_open
    private $uid = null;
    private $mailbox = null;
    //private $headers;
    private $structure;
    private $attachments = array();
    private $inline_parts = array();
    private $inline_attachs = array();
    private $mime_parts = array();
    private $body = '';
    
    /*
    private $opt = array();
    private $inline_parts = array();
    private $parse_alternative = false;
  
    public $parts = array();
    public $mime_parts = array();
    public $is_safe = false;*/
    
    function paloImapMessage($imap,$uid,$mailbox){
        $this->paloImap=$imap;
        $this->uid=$uid;
        $this->mailbox=$mailbox;
    }
    
    function getAttachments(){
        return $this->attachments;
    }
    
    function getInline_Parts(){
        return $this->inline_parts;
    }
    
    function getBody(){
        return $this->body;
    }
    
    function imapReadMesg(){
        //get msg structure
        $this->structure = @imap_fetchstructure($this->paloImap, $this->uid, FT_UID);
        
        if (!isset($this->structure->parts))  // simple
            $this->getpart($this->structure,0);  // pass 0 as part-number
        else {  // multipart: cycle through each part
            foreach ($this->structure->parts as $partno0 => $p)
                $this->getpart($p,$partno0+1);
        }
        
        //una vez leidas todas la partes del mensaje es necesario procesar el body del texto
        //si el contenido es html, es necesario parsear el texto en busca de imagenes embebidas para
        //hacer el cambio de url correspondiente
        if(isset($this->inline_parts['html'])){
            if(count($this->inline_parts['html'])>0){
                foreach($this->inline_parts['html'] as $data){
                    $result=preg_match_all("/src=[\"|']cid:(.*)[\"|']/Uims", $data, $matches);
                    /*print_r($matches);
                    print_r($this->inline_attachs);*/
                    if(count($matches)){
                        foreach($matches[1] as $key => $match) {
                            $search=$matches[0][$key];
                            if(isset($this->inline_attachs[$match])){
                                //sólo se soportan inline_attachs tipo imágenes
                                //se prodece a obtener el attachment y con este
                                $replace = "src=https://{$_SERVER['HTTP_HOST']}/index.php?menu=home&action=get_inline_attach&rawmode=yes&uid=".$this->uid."&enc=".$this->inline_attachs[$match]['enc']."&partnum=".$this->inline_attachs[$match]['partNum']."&current_folder=".htmlspecialchars(urlencode($this->mailbox))."&cid=".md5($match);
                                $data = str_replace($search, $replace, $data);
                            }
                        }
                    }
                    $this->body .=$data;
                }
            }
            $this->body=$this->parseHTMLdocument($this->body);
        }elseif(isset($this->inline_parts['plaintext'])){
            //TODO:si el contenido es texto plano, entonces debemos hacer la conversion de cierto caracteres como
            //salto de linea, regreso de carro entre otros
            foreach($this->inline_parts['plaintext'] as $data){
                $this->body .=$data;
            }
            $this->body=nl2br(strip_tags($this->body));
        }
    }
    
    private function parseHTMLdocument($html){
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();

        // load the HTML string we want to strip
        $doc->loadHTML($html);

        // get all the script tags
        $script_tags = $doc->getElementsByTagName('script');

        $length = $script_tags->length;

        // for each tag, remove it from the DOM
        for ($i = 0; $i < $length; $i++) {
            $script_tags->item($i)->parentNode->removeChild($script_tags->item($i));
        }

        // get the HTML string back
        return $doc->saveHTML();
    }
    
    private function getpart($p,$partno) {
        //print_r($p);
        // $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
        
        // PARAMETERS
        // get all parameters, like charset, filenames of attachments, etc.
        $params = array();
        if ($p->ifparameters)
            foreach ($p->parameters as $x)
                $params[strtolower($x->attribute)] = $x->value;
        if ($p->ifdparameters)
            foreach ($p->dparameters as $x)
                $params[strtolower($x->attribute)] = $x->value;
                
        // ATTACHMENT
        if ($p->ifdisposition){
            if ($p->disposition == "ATTACHMENT") {
                $attachmentDetails = array(
                    "name"    => ($params['filename'])? htmlspecialchars(urlencode($params['filename'])) : htmlspecialchars(urlencode($params['name'])),
                    "partNum" => htmlspecialchars(urlencode($partno)),
                    "enc"     => htmlspecialchars(urlencode($p->encoding))
                );
                array_push($this->attachments,$attachmentDetails);
            }elseif ($p->disposition == "INLINE") {
                //TODO:imagenes embebidas u otra clase de contenido embebido
                //no se como interpretar esto. 
                //hacer funciones que llamen a este contenido
                $inline_attachs = array(
                    "name"    => ($params['filename'])? htmlspecialchars(urlencode($params['filename'])) : htmlspecialchars(urlencode($params['name'])),
                    "partNum" => htmlspecialchars(urlencode($partno)),
                    "enc"     => htmlspecialchars(urlencode($p->encoding))
                );
                //TODO:note que a veces el id del elemento esta entr <>, lo cual hace que este no 
                //coincida con el identificador de la imagen, para reparar esto debemos eliminar esos simbolos
                //del nombre. Esto esta en prueba, ahi que probar con distintos servidores 
                if(preg_match("/^<(.*)>$/",$p->id,$match)){
                    $p->id=$match[1];
                }
                $this->inline_attachs[$p->id]=$inline_attachs;
            }
        }else{
            // TEXT
            if ($p->type==0 && !$p->ifdisposition) {
                $data = $this->decodeData($p,$partno);
                if($data){
                    if(isset($params['charset']))
                        $data=$this->changeCharset($params['charset'],$data);
                    // Messages may be split in different parts because of inline attachments,
                    // so append parts together with blank row.
                    if (strtolower($p->subtype)=='plain')
                        $this->inline_parts['plaintext'][]= trim($data)."\n\n";
                    else
                        $this->inline_parts['html'][]= trim($data)."<br><br>";
                }
            }

            // EMBEDDED MESSAGE
            // Many bounce notifications embed the original message as type 2,
            // but AOL uses type 1 (multipart), which is not handled here.
            // There are no PHP functions to parse embedded messages,
            // so this just appends the raw source to the main message.
            elseif ($p->type==2) {
                $data = $this->decodeData($p,$partno);
                if($data){
                    $this->inline_parts['plaintext'][]= trim($data)."\n\n";
                    $this->inline_parts['html'][]= trim($data)."\n\n";
                }
            }
        }

        // SUBPART RECURSION
        if (isset($p->parts)) {
            foreach ($p->parts as $partno0=>$p2)
                $this->getpart($p2,$partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
        }
    }
    
    private function decodeData($p,$partno){
        // DECODE DATA
        $data = ($partno)?
            @imap_fetchbody($this->paloImap, $this->uid,$partno,FT_UID):  // multipart
            @imap_body($this->paloImap, $this->uid,FT_UID);  // simple
        
        // Any part may be encoded, even plain text messages, so check everything.
        if ($p->encoding==4)
            $data = quoted_printable_decode($data);
        elseif ($p->encoding==3)
            $data = base64_decode($data);
            
        return $data;
    }
    
    private function changeCharset($charset,$text){
        $outputCharset = 'UTF-8';
        if (!empty($charset)) {
            if ($outputCharset != $charset) {
                if ($utf8Text = iconv($charset, $outputCharset, $text)) {
                    $text = $utf8Text;
                }
            }
        }
        return $text;
    }
    
    /**
     * Esta funcion es invocada cuando se quiere reenviar un mensaje
     * para agregar al nuevo mensaje los adjuntos que tenia el mensaje 
     * anterior
     * Lo que hace es dado el id del mensaje y el mailbox leer la estructura
     * del mensaje. Si el archivo tiene adjuntos procede a obtener estos
     * adjunto y crear archivos que despues seran adjuntos al nuevo mensaje
     * @return array => regresa un arreglo con la informacion de los archivos 
     *                  adjuntos que fueron creados dentro del servidor
     */
    function createAttachmentsToForward(){
        //1 leemos la estructura del mensaje que estamos reenviando dado su id
        //2 comprobamos si el archivo tiene datos adjuntos
        //3 procedemos a crear un nuevo archivo dentro del directorio donde 
        //  se almacenan los archivos que son subidos como adjuntos
        //4 agregamos a la variable de session[elastix_emailAttachs] la informacion
        //  del archivo adjunto que acabamos de crear
        
        $arrAttachments=array();
        try {
            if(!defined("PATH_UPLOAD_ATTACHS")){
                throw new RuntimeException(_tr("Error uploading your file.")._tr("Temporary directory does not exist"));
            }
            
            if (!is_dir(PATH_UPLOAD_ATTACHS)) {
                throw new RuntimeException(_tr("Error uploading your file.")._tr("Temporary directory does not exist"));
            }
            
            $finfo = new finfo(FILEINFO_MIME_TYPE);
        
            //get msg structure
            $this->structure = @imap_fetchstructure($this->paloImap, $this->uid, FT_UID);
            //print_r($this->structure);
            if(isset($this->structure->parts)){ // multipart: cycle through each part
                foreach ($this->structure->parts as $partno0 => $p)
                    $this->getpartAttachment($p,$partno0+1,$arrAttachments,$finfo);
            }
            
            return $arrAttachments;
        }catch (RuntimeException $e){
            $this->errMsg=$e->getMessage();
            return false;
        }
    }
    
    private function getpartAttachment($p,$partno,&$arrAttachments,$finfo){
        // ATTACHMENT
        if ($p->ifdisposition){
            if ($p->disposition == "ATTACHMENT" || $p->disposition == "INLINE") {
                if ($p->ifparameters)
                    foreach ($p->parameters as $x)
                        $params[strtolower($x->attribute)] = $x->value;
                if ($p->ifdparameters)
                    foreach ($p->dparameters as $x)
                        $params[strtolower($x->attribute)] = $x->value;
                
                $filename = $params['name']?$params['name']:$params['filename'];
                //obtenemos la información del adjunto y procedemos a crear el archivo 
            
                $filename = $params['name']?$params['name']:$params['filename'];
                //obtenemos la información del adjunto y procedemos a crear el archivo 
                
                $tmpFileName = tempnam(PATH_UPLOAD_ATTACHS,"");
                if ($tmpFileName==false){
                    throw new RuntimeException('Failed to create temporary file file.');
                }
                
                $message = @imap_fetchbody($this->paloImap, $this->uid, $partno, FT_UID);
                $message = $this->decodeAttachData($p->encoding,$message);
                                            
                if(file_put_contents($tmpFileName,$message)===false){
                    throw new RuntimeException('Failed to load attachs file.');
                }
                
                $mimetype = $finfo->file($tmpFileName);
                
                if ($p->disposition == "ATTACHMENT") {
                    $idAttach=md5($tmpFileName);
                    $type='file';
                    $arrAttachments[]=array('idAttach'=>$idAttach,'name'=>htmlentities($filename,ENT_COMPAT,'UTF-8'));
                }elseif($p->disposition == "INLINE"){
                    //TODO:note que a veces el id del elemento esta entr <>, lo cual hace que este no 
                    //coincida con el identificador de la imagen, para reparar esto debemos eliminar esos simbolos
                    //del nombre. Esto esta en prueba, ahi que probar con distintos servidores 
                    if(preg_match("/^<(.*)>$/",$p->id,$match)){
                        $p->id=$match[1];
                    }
                    $idAttach=md5($p->id);
                    $type='inline';
                }   
                $_SESSION["elastix_emailAttachs"][$idAttach]['filename']=$tmpFileName;
                $_SESSION["elastix_emailAttachs"][$idAttach]['name']=$filename;
                $_SESSION["elastix_emailAttachs"][$idAttach]['mime']=$mimetype;
                $_SESSION["elastix_emailAttachs"][$idAttach]['type']=$type;
            }
        }
        if (isset($p->parts)) {
            foreach ($p->parts as $partno0=>$p2)
                $this->getpartAttachment($p2,$partno.'.'.($partno0+1),$arrAttachments,$finfo);  // 1.2, 1.2.1, etc.
        }
    }
    
    function downloadAttachment($partNum, $encoding) {
        $this->structure = @imap_bodystruct($this->paloImap, @imap_msgno($this->paloImap, $this->uid), $partNum);
    
        $filename = $this->structure->dparameters[0]->value;
        $message = @imap_fetchbody($this->paloImap, $this->uid, $partNum, FT_UID);
    
        $message=$this->decodeAttachData($encoding,$message);
        
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        echo $message;
    }
    
    function getInlineAttach($partNum, $encoding) {
        $this->structure = @imap_bodystruct($this->paloImap, @imap_msgno($this->paloImap, $this->uid), $partNum);
        
        $filename = $this->structure->dparameters[0]->value;
        $message = @imap_fetchbody($this->paloImap, $this->uid, $partNum, FT_UID);
                   
        $message=$this->decodeAttachData($encoding,$message);
        
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: inline; filename=" . $filename);
        echo $message;
    }
    
    private function decodeAttachData($encoding,$message){
        switch ($encoding) {
            case 0:
            case 1:
                $message = @imap_8bit($message);
                break;
            case 2:
                $message = @imap_binary($message);
                break;
            case 3:
                $message = @imap_base64($message);
                break;
            case 4:
                $message = quoted_printable_decode($message);
                break;
        }
        return $message;
    }
}



?>
