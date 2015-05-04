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

/*  @author: paloEmailAddress 2014/01/15 01:07:03 Rocio Mera rmera@palosanto.com Exp $ */
require_once "apps/home/libs/emailaddress.php";
require_once "apps/home/libs/paloSantoHome.class.php";
require_once "libs/phpmailer/class.phpmailer.php";

class paloComposeEmail{
    //email address that send the email
    protected $username;
    //name from the person who send the email
    protected $name='';
    //password of account that send the email
    protected $pass='';
    // intancia de la calse paloImap
    protected $paloImap;
    // instance of class phpMailer usada para enviar los correos
    protected $phpMailer;
    protected $useSMTP = false;
    protected $errMsg = '';
    protected $attachments = '';
    
    
    function __construct($user, $pass, $name, &$pImap){
        //validate user
        if (filter_var($user, FILTER_VALIDATE_EMAIL)){
            //revisamos que el usuario exista en elastix y obtenemos el nombre
            $this->username=$user;
        }else{
            $this->username=null;
        }
        
        $this->pass=$pass;
        
        $this->name=$name;
        
        //TODO: read email configuration user
        // set if user use smt to send email and other security issue
        
        //imap connection
        if($pImap instanceof paloEmailAddress){
            $this->paloImap=$pImap;
        }else{
            $this->paloImap=new paloImap();
        }
    }
        
    function getErrorMsg(){
        return $this->errMsg;
    }
    
    function setAttachments($attachments){
        if(is_array($attachments)){
            $this->attachments=$attachments;
        }
    }
    
    function sendEmail($headers,$subject,$body,$draft=false){
        //check de headers
        //to
        try{
            $this->phpMailer = new PHPMailerMime(true);
            
            //Elastix usa codificacion UTF8
            $this->phpMailer->CharSet = 'UTF-8';
            
            if(!isset($this->username)){
                $this->errMsg=_tr("Invalid Sender");
                return false;
            }
            
            if(!isset($headers['to'])){
                $this->errMsg=_tr("Invalid Field 'TO'");
                return false;
            }
                        
            //TO: creamos una nueva instacia de paloListEmailAddress con los emails
            //que se encuentran en el campo 'TO'
            $to=new paloListEmailAddress(explode(",",$headers['to']));
            //ninguna de las direcciones dadas es válida, no podemos continuar
            if($to->getNumAddress()==0){
                $this->errMsg=_tr("Invalid Field 'TO'");
                return false;
            }
            foreach($to->getListAddress() as $address){
                $this->phpMailer->AddAddress($address->getEmail(), $address->getName());
            }
            
            //CC:
            if(isset($headers['cc'])){
                $cc=new paloListEmailAddress(explode(",",$headers['cc']));
                //ninguna de las direcciones dadas es válida, no podemos continuar
                foreach($cc->getListAddress() as $address){
                    $this->phpMailer->AddCC($address->getEmail(), $address->getName());
                }
            }
            
            //BCC:
            if(isset($headers['bcc'])){
                $bcc=new paloListEmailAddress(explode(",",$headers['bcc']));
                //ninguna de las direcciones dadas es válida, no podemos continuar
                foreach($cc->getListAddress() as $address){
                    $this->phpMailer->AddBCC($address->getEmail(), $address->getName());
                }
            }
            
            //REPLY_TO
            if(isset($headers['reply_to'])){
                $replayTo=new paloListEmailAddress(explode(",",$headers['reply_to']));
                //ninguna de las direcciones dadas es válida, no podemos continuar
                foreach($replayTo->getListAddress() as $address){
                    $this->phpMailer->AddReplyTo($address->getEmail(), $address->getName());
                }
            }
            
            //FROM
            $this->phpMailer->setFrom($this->username,$this->name);
            
            //SUBJECT
            $this->phpMailer->Subject=(is_string($subject))?$subject:'';
        
            //esta funcion ademas de enviar el mensaje como html
            //también setea la priedad altBody que corresponde a la parte de mensaje en texto plano
            $this->phpMailer->paloMsgHTML($body,'/var/www/html',$this->attachments);
            
            $uploadedFiles=array();
            //attachments
            If(is_array($this->attachments)){
                foreach($this->attachments as $key => $attach){
                    if($attach['type']=='file'){
                        if(is_string($attach['filename']) && $attach['filename']!=''){
                            $filename=basename($attach['filename']);
                            $name=$filename;
                            if(isset($attach['name'])){
                                if(is_string($attach['name']) && $attach['name']!=''){
                                    $name=$attach['name'];
                                }
                            }
                            
                            if(is_file(PATH_UPLOAD_ATTACHS."/$filename")){
                                $uploadedFiles[]=PATH_UPLOAD_ATTACHS."/$filename";
                                $this->phpMailer->AddAttachment(PATH_UPLOAD_ATTACHS."/$filename", $name, 'base64', $attach['mime']);
                            }
                        }
                    }
                }
            }
            
            //si draft==true significa que se debe guardar una copia del archivo 
            //en borradores y no debe ser enviado
            if($draft){
                cleanAlertsImap();
                $pImap->close_mail_connection();
                return true;
            }
            
            $this->phpMailer->Send();
            
            //eliminamos los archivos subidos para los attachments
            foreach($this->phpMailer->GetAttachments() as $attachment){
                //if $attachment[5]=false significa que son archivos,
                // tambien ahi otros tipo de attachments que son strings que no se deben elminiar
                if($attachment[5]==false){ 
                    if(in_array($attachment[0],$uploadedFiles)){
                        if(is_file($attachment[0])){
                            unlink($attachment[0]);
                        }
                    }
                }
            }
            
            //guardamos una copia del archivo enviado dentro de la carpeta Sent
            //1.- comprobamos que la carpeta Sent exista y sí no existe la creamos
            //2.- procedemos a crear la coneccion a dicha carpeta
            //3.- procedemo a agregar el archivo a la carpeta
            if($this->paloImap->login($this->username, $this->pass)){;
                $sentFolder='Sent';
                $arrFolders=$this->paloImap->getMailboxList($sentFolder);
                if(isset($arrFolders[$sentFolder])){
                    if(!$this->paloImap->createMailbox($sentFolder)){
                        $this->errMsg=$this->paloImap->getErrorMsg();
                    }
                }
                
                $this->paloImap->setMailbox('Sent');
                if(!$this->paloImap->appendMessage($sentFolder,$this->phpMailer->getMiMeStringToAppendImap(),"\\Seen")){
                    $this->errMsg=$this->paloImap->getErrorMsg();
                }
            }else{
                $this->errMsg=$this->paloImap->getErrorMsg();
            }
            
            cleanAlertsImap();
            $this->paloImap->close_mail_connection();
            return true;
        } catch(phpmailerException $e){
            $this->errMsg=$e->errorMessage();
            return false;
        } catch(Exception $e){
            $this->errMsg=$e->getMessage();
            return false;
        }
        
    }
}

class PHPMailerMime extends PHPMailer{
    public function getMiMeStringToAppendImap(){
        return str_replace("\n","\r\n",$this->MIMEHeader.$this->mailHeader)."\r\n\r\n".str_replace("\n","\r\n",$this->MIMEBody);
    }
    
    /**
     * Create a message from an HTML string.
     * Automatically makes modifications for inline images and backgrounds
     * and creates a plain-text version by converting the HTML.
     * Overwrites any existing values in $this->Body and $this->AltBody
     * @access public
     * @param string $message HTML message string
     * @param string $basedir baseline directory for path
     * @param bool $advanced Whether to use the advanced HTML to text converter
     * @return string $message
     */
    public function paloMsgHTML($message, $basedir = '', $attachments, $advanced=FALSE)
    {
        //path directo solo cuando son imágenes de los emoticones 
        //otras referencias a paths directos deben ser ignorados
        //revisar los urls por si las peticiones corresponden a inline attachments
        //al momento de reenviar un archivo
        
        preg_match_all("/(src|background)=[\"'](.*)[\"']/Ui", $message, $images);
        if (isset($images[2])) {
            foreach ($images[2] as $i => $url) {
                // do not change urls for absolute images (thanks to corvuscorax)
                if (!preg_match('#^[A-z]+://#', $url)) {
                    //inline attachment la hacer forward
                    if(preg_match("/^index.php\\?(.*)/",$url,$parts)){
                        parse_str($parts[1], $query);
                        if(isset($query['amp;cid'])){
                            if(isset($attachments[$query['amp;cid']])){
                                if($attachments[$query['amp;cid']]['type']=='inline'){
                                    $attach=$attachments[$query['amp;cid']];
                                    if(is_string($attach['filename']) && $attach['filename']!=''){
                                        $filename=basename($attach['filename']);
                                        $name=$filename;
                                        if(isset($attach['name'])){
                                            if(is_string($attach['name']) && $attach['name']!=''){
                                                $name=$attach['name'];
                                            }
                                        }
                                        $cid = md5($url) . '@phpmailer.0'; //RFC2392 S 2
                                        if($this->addEmbeddedImage(
                                            PATH_UPLOAD_ATTACHS."/$filename",
                                            $cid,
                                            $filename,
                                            'base64',
                                            $attach['mime'])){
                                            $message = preg_replace(
                                                "/" . $images[1][$i] . "=[\"']" . preg_quote($url, '/') . "[\"']/Ui",
                                                $images[1][$i] . "=\"cid:" . $cid . "\"",
                                                $message
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }else{
                        $filename = basename($url);
                        $directory = dirname($url);
                        //solo permitimos imágenes que corresponde a emoticones
                        //del editor
                        if (strlen($basedir) > 1 && substr($basedir, -1) != '/') {
                            $basedir .= '/';
                        }
                        if (strlen($directory) > 1 && substr($directory, -1) != '/') {
                            $directory .= '/';
                        }
                        if ( $basedir . $directory == '/var/www/html/web/apps/home/tinymce/js/tinymce/plugins/emoticons/img' ){
                            $cid = md5($url) . '@phpmailer.0'; //RFC2392 S 2
                            if ($this->addEmbeddedImage(
                                $basedir . $directory . $filename,
                                $cid,
                                $filename,
                                'base64',
                                self::_mime_types(self::mb_pathinfo($filename, PATHINFO_EXTENSION))
                            )
                            ) {
                                $message = preg_replace(
                                    "/" . $images[1][$i] . "=[\"']" . preg_quote($url, '/') . "[\"']/Ui",
                                    $images[1][$i] . "=\"cid:" . $cid . "\"",
                                    $message
                                );
                            }
                        }else{
                            //se trata de incluir un archivo no válido
                        }
                    }
                }
            }
        }
        $this->isHTML(true);
        if (empty($this->AltBody)) {
            $this->AltBody = 'To view this email message, open it in a program that understands HTML!' . "\n\n";
        }
        //Convert all message body line breaks to CRLF, makes quoted-printable encoding work much better
        $this->Body = $this->normalizeBreaks($message);
        $this->AltBody = $this->normalizeBreaks($this->html2text($message, $advanced));
        return $this->Body;
    }
}
