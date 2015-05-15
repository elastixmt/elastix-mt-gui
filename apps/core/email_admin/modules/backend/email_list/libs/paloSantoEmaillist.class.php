<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4-28                                               |
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
  $Id: paloSantoEmailList2.class.php,v 1.1 2011-07-27 05:07:46 Alberto Santos asantos@palosanto.com Exp $ */
  
  /**
  * IMPORTANTE:
  * MAILMAN no soporta dos lista con nombres iguales a pesar de que estos se encuentren en dominios
  * virtuales diferentes. 
  * REVISAR Y CORREGIR ESO PARA QUE NO HAYA CONFLICTO ENTRE LAS LISTA CREADAS POR DIFERENTES 
  * ORGANIZACIONES
  **/
class paloSantoEmailList {
    private $_DB;
    private $errMsg;

    function paloSantoEmailList(&$pDB)
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

    function getError()
    {
        return $this->errMsg;
    }

    /*HERE YOUR FUNCTIONS*/

    function getNumEmailList($nameList=null,$domain=null)
    {
        $where = array();
        $arrParam=null;
        $query = "SELECT COUNT(*) FROM email_list";
        
        if(!empty($nameList)){
            $where[]=' UPPER(listname) like ?';
            $arrParam[]="%".strtoupper($nameList)."%";
        }
        if(!empty($domain)){
            $where[]=' organization_domain=? ';
            $arrParam[]=$domain;
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
        $result=$this->_DB->getFirstRowQuery($query,false,$arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result[0];
    }

    function getEmailListPagging($nameList=null,$domain=null, $limit, $offset)
    {
        $where = array();
        $arrParam=null;
        $query = "SELECT * FROM email_list";
        
        if(!empty($nameList)){
            $where[]=' UPPER(listname) like ?';
            $arrParam[]="%".strtoupper($nameList)."%";
        }
        if(!empty($domain)){
            $where[]=' organization_domain=? ';
            $arrParam[]=$domain;
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
        $query .="  LIMIT ? OFFSET ?";
        $arrParam[] = $limit;
        $arrParam[] = $offset;
        
        $result=$this->_DB->fetchTable($query, true, $arrParam);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result;
    }
    
    function getEmailList($idList,$domain=null){
        $where = array();
        $arrParam=null;
        $query = "SELECT * FROM email_list";
        
        if(!empty($idList)){
            $where[]=' id=?';
            $arrParam[]=$idList;
        }
        if(!empty($domain)){
            $where[]=' organization_domain=? ';
            $arrParam[]=$domain;
        }
        if(count($where)>0){
            $query .=" WHERE ".implode(" AND ",$where);
        }
        
        $result=$this->_DB->getFirstRowQuery($query,true,$arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result;
    }

    function getTotalMembers($idList)
    {
        $query = "SELECT COUNT(*) FROM member_list WHERE id_emaillist=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($idList));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result[0];
    }

    function isMailmanListCreated()
    {
        $sComando = '/usr/bin/elastix-helper mailman_config list_lists 2>&1';
        $output = $ret = NULL;
            exec($sComando, $output, $ret);
        if($ret != 0){
            $this->errMsg = _tr("Could not execute command list_lists");
            return null;
        }
        else{
            foreach($output as $list){
                if(preg_match("/^mailman[[:space:]]+\-[[:space:]]+.+$/",trim(strtolower($list))))
                    return true;
            }
            return false;
        }
    }

    function checkPostfixFile()
    {
        $output = $ret = NULL;
        exec('/usr/bin/elastix-helper mailman_config check_postfix_file 2>&1', $output, $ret);
        if($ret == 0)
            return true;
        else{
            $this->errMsg=implode('', $output);
            return false;
        }
    }

    function createListMailman($emailAdmin,$password){
        return $this->mailmanCreateList('mailman',$emailAdmin,$password);
    }
    
    private function mailmanCreateList($listName,$emailAdmin,$password,$domain="")
    {
        $sComando = "/usr/bin/elastix-helper mailman_config newlist ".escapeshellarg($listName)." ".escapeshellarg($emailAdmin)." ".escapeshellarg($password)." ".escapeshellarg($domain)." 2>&1";
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if($ret == 0)
            return true;
        else{
            $this->errMsg=implode('', $output);
            return false;
        }
    }
    
    function domainExists($domain)
    {
        $query = "SELECT COUNT(*) FROM organization WHERE domain=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($domain));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        if($result[0] > 0)
            return true;
        else
            return false;
    }

    function createEmailList($domain,$namelist,$password,$emailadmin)
    {
        //vefircamos que el dominio exista
        if(!$this->domainExists($domain)){
            $this->errMsg=_tr("Domain does not exist or DATABASE ERROR");
            return false;
        }
        
        //verificamos que le nombre de la lista sea valido
        if(!preg_match("/^[[:alpha:]]+([\-_\.]?[[:alnum:]]+)*$/",$namelist)){
            $this->errMsg=_tr("Invalid List Name");
            return false;
        }
        
        $namelist=strtolower($namelist);
        //verificamos que no exista otra lista con el mismo nombre
        if($this->listExistsbyName($namelist)){
            $this->errMsg=_tr("Already exist other List with the same name");
            return false;
        }
        
        //procedemos a guardar la lista en la base
        $query = "INSERT INTO email_list (organization_domain,listname,password,mailadmin) VALUES (?,?,?,?)";
        $result = $this->_DB->genQuery($query,array($domain,$namelist,$password,$emailadmin));
        if( $result == false ){
            $this->errMsg = _tr("DATABASE ERROR");
            return false;
        }
        
        //creamos la lista en el programa mailman
        if(!$this->mailmanCreateList($namelist,$emailadmin,$password,$domain)){
            return false;
        }
        
        return true;
    }

    function listExistsbyName($listName)
    {
        $query = "SELECT COUNT(*) FROM email_list WHERE listname=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($listName));
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        if($result[0] > 0)
            return true;
        else
            return false;
    }
    
    //function that removes a emailist identified by his id
    function deleteEmailList($idList)
    {
        //comprobamos que la lista exista
        $query="SELECT listname,organization_domain FROM email_list WHERE id=?";
        $emailList=$this->_DB->getFirstRowQuery($query,true,array($idList));
        if($emailList==FALSE){
            $this->errMsg = ($emailList===FALSE)?_tr("DATABASE ERROR"):_tr('The email list does not exist');
            return false;
        }
        
        //eliminamos la lista de la base
        $query = "DELETE FROM email_list WHERE id=?";
        $result = $this->_DB->genQuery($query,array($idList));
        if( $result == false ){
            $this->errMsg = _tr("DATABASE ERROR");
            return false;
        }
        
        //eliminamos la lista dle mailman
        if(!$this->mailmanRemoveList($emailList['listname'],$emailList['organization_domain'])){
            return false;
        }
        
        return true;
    }

    private function mailmanRemoveList($listName,$domainName)
    {
        $sComando = "/usr/bin/elastix-helper mailman_config remove_list ".escapeshellarg($listName)." ".escapeshellarg($domainName)." 2>&1";
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if($ret == 0)
            return true;
        else{
            $this->errMsg=implode('', $output);
            return false;
        }
    }

    function getMembers($limit,$offset,$id_list,$field_type,$field_pattern)
    {
        $query = "SELECT * FROM member_list WHERE id_emaillist=? ";
        $arrParam = array($id_list);
        if(strlen($field_pattern)!=0){
            if($field_type == "name")
                $query .= "AND namemember like ? ";
            else
                $query .= "AND mailmember like ? ";
                $arrParam[] = "%$field_pattern%";
        }
        $query .= "LIMIT ? OFFSET ?";
        $arrParam[] = $limit;
        $arrParam[] = $offset;
        $result=$this->_DB->fetchTable($query, true, $arrParam);
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }
    
    function getListName($idList)
    {
        $query  = "SELECT * FROM email_list WHERE id=?";
        $result = $this->_DB->getFirstRowQuery($query,true,array($idList));
        if($result===FALSE){
            $this->errMsg = _tr("DATABASE ERROR");
            return false;
        }
        if(is_array($result) && isset($result['listname']))
            return $result['listname'];
        else{
            $this->errMsg = _tr("Email List does not exist");
            return false;
        }
    }
    
    /**
     * Function that add members to a list odentified by its id
     * The members are passed in the param $arrMembers 
     * @param array $arrMembers array(array(email_member=>email@domain, namemember=>name1),
                                      array(email_member=>email2@domain2, namemember=>name2)...)
     */
    function saveMembersList($idEmailList,$arrMembers)
    {
        //comprobamos que la lista exista
        $listName=$this->getListName($idEmailList);
        if($listName==false){
            return false;
        }
        
        if(is_array($arrMembers)){
            if(count($arrMembers)>0){
                //recorremos el arreglo con los miembros y los insertamos en la base
                $query = "INSERT INTO member_list (mailmember,id_emaillist,namemember) VALUES (?,?,?)";
                foreach($arrMembers as $member){
                    $namemember=empty($member['namemember'])?'':$member['namemember'];
                    $result = $this->_DB->genQuery($query,array($member['email_member'],$idEmailList,$namemember));
                    if( $result == false ){
                        $this->errMsg = _tr('DATABASE ERROR');
                        return false;
                    }
                }
                //guardamos los miembros de la lista en mailman
                if(!$this->mailmanAddMembers($arrMembers,$listName)){
                    return false;
                }
            }
        }else{
            $this->errMsg=_tr("Invalid Members(s)");
            return false;
        }
        
        return true;
    }

    private function mailmanAddMembers($arrMembers,$listName)
    {
        $listOfMembers = "";
        foreach($arrMembers as $member)
            $listOfMembers .= $member["member"]."\n";

        $sComando = "/usr/bin/elastix-helper mailman_config add_members ".escapeshellarg($listOfMembers)." ".escapeshellarg($listName)." 2>&1";
        $output = $ret = NULL;
            exec($sComando, $output, $ret);
        if($ret == 0)
            return true;
        else{
            $this->errMsg=implode('',$output);
            return false;
        }
    }

    function isAMemberOfList($emailMember,$id_list)
    {
        $query = "SELECT COUNT(*) FROM member_list WHERE mailmember=? AND id_emaillist=?";
        $result=$this->_DB->getFirstRowQuery($query,false,array($emailMember,$id_list));
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        if($result[0] > 0)
            return true;
        else
            return false;
    }

    function removeMembersList($idEmailList,$arrMembers)
    {
        //comprobamos que la lista exista
        $listName=$this->getListName($idEmailList);
        if($listName==false){
            return false;
        }
        
        if(is_array($arrMembers)){
            if(count($arrMembers)>0){
                $param[]=$idEmailList;
                $q="";
                foreach($arrMembers as $member){
                    $q .="?,";
                    $param[]=$member['member'];
                }
                $q=substr($q,0,-1);
                //eliminamos los miembreos de la lista de la base
                $query = "DELETE FROM member_list WHERE id_emaillist=? AND mailmember IN ($q)";
                $result = $this->_DB->genQuery($query,$param);
                if( $result == false ){
                    $this->errMsg = _tr('DATABASE ERROR');
                    return false;
                }
                
                //eliminamos los miembros de la lista demailman
                if(!$this->mailmanRemoveMembers($arrMembers,$listName)){
                    return false;
                }
            }
        }else{
            $this->errMsg=_tr("Invalid Members(s)");
            return false;
        }
        return true;
    }

    private function mailmanRemoveMembers($arrMembers,$listName)
    {
        $listOfMembers = "";
        foreach($arrMembers as $member)
            $listOfMembers .= $member["member"]."\n";

        $sComando = "/usr/bin/elastix-helper mailman_config remove_members ".escapeshellarg($listOfMembers)." ".escapeshellarg($listName)." 2>&1";
        $output = $ret = NULL;
            exec($sComando, $output, $ret);
        if($ret == 0)
            return true;
        else{
            $this->errMsg=implode('',$output);
            return false;
        }
    }

    
}
?>