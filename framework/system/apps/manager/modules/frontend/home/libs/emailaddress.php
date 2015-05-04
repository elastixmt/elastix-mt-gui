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
class paloEmailAddress {
    // the domain portion
    protected $domain;
    // the user portion
    protected $user;
    // the NAME portion in the email with format NAME <user@domain>
    protected $name = '';
    // the complete email address user@domain 
    protected $email = '';
    
    //TODO: IMPLEMENTS EXCEPTIONS
    //If the parameters are not a valid email address, the param email if set = ''
    public function __construct($email){
        $this->email = '';
        if(is_string($email) && trim($email)!=''){
            if($this->parseEmail($email)){
                $this->email = $this->user.'@'.$this->domain;
            }
        }
    }
    
    /*//TODO: IMPLEMENTS EXCEPTIONS
    //If the parameters are not a valid email address, the param email if set = ''
    public function __construct($domain,$user,$name=''){
        $domain=trim($domain);
        $user=trim($user);
        if(validateEmailAddress($user.'@'.$domain)){
            $this->domain=$domain;
            $this->user=$user;
            $this->name=trim($name, '" \'');
            $this->email=$this->user.'@'.$this->domain;
        }else{
            $this->email='';
        }
    }*/
    
    private function parseEmail($email){
        $email = trim($email);
        if (preg_match('/^([^>]+)<([^>]+)>$/i', $email, $parts)) {
            $this->name = trim($parts[1], '" \'');
            if ($this->validateEmailAddress($parts[2])) {
                $this->user = strtok($parts[2], '@');
                $this->domain = strtok('@');
                return true;
            }
        } else {
            $this->name = '';
            if ($this->validateEmailAddress($email)) {
                //obtenemos las partes
                $this->user = strtok($email, '@');
                $this->domain = strtok('@');
                return true;
            }
        }
        return false;
    }
    
    public function validateEmailAddress($email){
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }else{
            return false;
        }
    }
    
    public function getDomain(){
        return $this->domain;
    }
    
    public function getUser(){
        return $this->user;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function getEmail(){
        return $this->email;
    }
    
    public function toString(){
        $email = $this->getEmail();
        if($this->getName!=''){
            $email = $this->getName." <$email>";
        }
        return htmlentities($email, ENT_QUOTES, 'UTF-8');
    }
}

/*  @author: paloListEmailAddress 2014/01/15 01:07:03 Rocio Mera rmera@palosanto.com Exp $ */
class paloListEmailAddress {
    //array of paloEmailAddress objects
    protected $listAddress = array();
    
    /**
     * received as parameter a array of email address
     * It can be an array of paloEmailAddress
     * or a array of strings
     */
    public function __construct($arrEmails){
        if(is_array($arrEmails)){
            foreach($arrEmails as $email) {
                $this->addAddress($email);
            }
        }
    }
    
    public function addAddress($email){
        if(is_string($email)){
            $address=new paloEmailAddress($email);
            if($address->getEmail()!=''){
                $this->add($address);
            }
        }elseif($email instanceof paloEmailAddress){
            if($email->getEmail()!=''){
                $this->add($email);
            }
        }
    }
    
    //recibe una instacia de paloEmailAddress
    private function add($address){
        //antes de agregar la direccion debemos comprobar que esta no existe en el arreglo
        if(!$this->exist($address->getEmail())){
            $this->listAddress[strtolower($address->getEmail())]=$address;
        }
    }
    
    public function exist($email){
        return isset($this->listAddress[strtolower($email)]);
    }
    
    public function deleteAddress($email){
        unset($this->listAddress[strtolower($email)]);
    }
    
    public function getAddressbyEmail($email){
        $email=strtolower($email);
        if(isset($this->listAddress[$email])){
            return $this->listAddress[$email];
        }else{
            return false;
        }
    }
    
    public function getNumAddress(){
        return count($this->listAddress);
    }
    
    public function getListAddress(){
        return $this->listAddress;
    }
    
    public function toString(){
        $str[]=array();
        foreach($this->listAddress as $address){
            $str[]= $address->toString();
        }
        return implode(",",$str);
    }
}
?>