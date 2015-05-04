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
  | Autores: Gladys Carrillo B.   <gcarrillo@palosanto.com>              |
  +----------------------------------------------------------------------+
  $Id: paloSantoInstaller.class.php,v 1.1 2007/09/05 00:25:25 gcarrillo Exp $
*/

$elxPath="/usr/share/elastix";
require_once "$elxPath/libs/paloSantoDB.class.php";
require_once "$elxPath/libs/paloSantoModuloXML.class.php";
require_once "$elxPath/libs/misc.lib.php";

// La presencia de MYSQL_ROOT_PASSWORD es parte del API global.
define('MYSQL_ROOT_PASSWORD', obtenerClaveConocidaMySQL('root'));

class Installer
{

    var $_errMsg;

    function createNewDatabase($path_script_db,$sqlite_db_path,$db_name)
    {
        $comando="cat $path_script_db | sqlite3 $sqlite_db_path/$db_name.db";
        exec($comando,$output,$retval);
        return $retval;
    }

    function createNewDatabaseMySQL($path_script_db, $db_name, $datos_conexion)
    {
        $root_password = MYSQL_ROOT_PASSWORD;

        $db = 'mysql://root:'.$root_password.'@localhost/';
        $pDB = new paloDB ($db);
        $sPeticionSQL = "CREATE DATABASE $db_name";
        $result = $pDB->genExec($sPeticionSQL);
        if($datos_conexion['locate'] == "")
            $datos_conexion['locate'] = "localhost";
        $GrantSQL = "GRANT SELECT, INSERT, UPDATE, DELETE ON $db_name.* TO ";
        $GrantSQL .= $datos_conexion['user']."@".$datos_conexion['locate']." IDENTIFIED BY '".                          $datos_conexion['password']."'";
        $result = $pDB->genExec($GrantSQL);
        $comando="mysql --password=".escapeshellcmd($root_password)." --user=root $db_name < $path_script_db";
        exec($comando,$output,$retval);
        return $retval;
    }

    function refresh($documentRoot='')
    {
        global $arrConf;
        $documentRoot = $arrConf['documentRoot'];

        //STEP 1: Delete tmp templates of smarty.
        exec("rm -rf $documentRoot/tmp/smarty/templates_c/*",$arrConsole,$flagStatus); 

        //STEP 2: Update menus elastix permission.
        if(isset($_SESSION['elastix_user_permission']))
          unset($_SESSION['elastix_user_permission']);

        return $flagStatus;
    }
}
?>
