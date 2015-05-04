<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-7                                               |
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
  $Id: paloSantoprueba_applets.class.php,v 1.1 2009-12-28 06:12:49 Bruno bomv.27 Exp $ */

class paloSantoAppletAdmin {
    var $errMsg;

    function paloSantoAppletAdmin()
    {
    }

    function getApplets_User($user)
    {
        global $arrConf;
        $dsn = "sqlite3:///$arrConf[elastix_dbdir]/dashboard.db";
        $pDB  = new paloDB($dsn);
        $pDB2 = new paloDB($arrConf['elastix_dsn']['elastix']);
        $pACL = new paloACL($pDB2);

        if($pACL->isUserSuperAdmin($user))
            $typeUser = "admin";
        else
            $typeUser = "no_admin";

        $query = "
                select
                    t1.id id,
                    t1.name name,
                    ifnull(t2.activated,0) activated,
                    ifnull(t2.order_no,0) order_no
                from
                    (select
                        dau.id id,
                        a.name name
                     from
                        applet a
                            inner join
                        default_applet_by_user dau on a.id=dau.id_applet
                    where
                        dau.username=?) t1
                left join
                    (select
                        aau.id_dabu id_dabu,
                        aau.id activated,
                        aau.order_no order_no
                     from
                        activated_applet_by_user aau
                     where
                        aau.username=?) t2
                on
                    t1.id=t2.id_dabu
                order by
                    t1.id asc;";

        $result=$pDB->fetchTable($query, true,array($typeUser,$user));

        if($result==FALSE){
            $this->errMsg = $pDB->errMsg;
            return array();
        }else{
            $applets=array();
            foreach($result as $value){
                $value['name']=_tr($value['name']);
                $applets[]=$value;
            }
            return $applets;
        } 
    }

    function setApplets_User($arrIDs_DAU, $user)
    {
        global $arrConf;
        $dsn = "sqlite3:///$arrConf[elastix_dbdir]/dashboard.db";
        $pDB  = new paloDB($dsn);

        if(is_array($arrIDs_DAU) & count($arrIDs_DAU)>0){
            $pDB2 = new paloDB($arrConf['elastix_dsn']['elastix']);
            $pACL = new paloACL($pDB2);

            if($pACL->isUserSuperAdmin($user))
                $typeUser = "admin";
            else
                $typeUser = "no_admin";

            $pDB->beginTransaction();
            // Parte 1: Elimino todas las actuales
            $query1 = " delete from activated_applet_by_user 
                        where username=? and id_dabu in (select id from default_applet_by_user where username=?)";
            $result1=$pDB->genQuery($query1,array($user,$typeUser));

            if($result1==FALSE){
                $this->errMsg = $pDB->errMsg;
                $pDB->rollBack();
                return false;
            }

            // Parte 2: Inserto todas las checked
            foreach($arrIDs_DAU as $key => $value){
                $query2 = "insert into activated_applet_by_user (id_dabu, order_no, username) values (?,?,?)";
                $result2=$pDB->genQuery($query2,array($value,$key+1,$user));

                    if($result2==FALSE){
                        $this->errMsg = $pDB->errMsg;
                        $pDB->rollBack();
                        return false;
                    }
            }
            $pDB->commit();
        }
        return true;
    }
}
?>
