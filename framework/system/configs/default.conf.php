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
  $Id: default.conf.php,v 1.1.1.1 2007/07/06 21:31:56 gcarrillo Exp $ */

global $arrConf;
$arrConf['dsn_mysql_elastix'] = generarDSNSistema("asteriskuser","elxpbx");
$arrConf['elastix_dbdir'] = '/var/www/db';
$arrConf['elastix_dsn'] = array(
                                "elastix"   =>  $arrConf['dsn_mysql_elastix'],
                                "acl"       =>  $arrConf['dsn_mysql_elastix'], //se lo deja por compatibilidad
                                "samples"   =>  "sqlite3:///$arrConf[elastix_dbdir]/samples.db",
                            );
$arrConf['documentRoot'] = '/var/www/html';
$arrConf['basePath'] = '/var/www/html';
$arrConf['webCommon'] = 'web/_common';
$arrConf['elxPath'] = '/usr/share/elastix';
$arrConf['theme'] = 'default'; //theme personal para los modulos esencialmente

// Verifico si las bases del framework están, debido a la migración de dichas bases como archivos .db a archivos .sql
checkFrameworkDatabases($arrConf['elastix_dbdir']);

$arrConf['elastix_version'] = load_version_elastix($arrConf['basePath']."/"); //la version y le release  del sistema elastix
$arrConf['defaultMenu'] = 'config';
$arrConf['language'] = 'en';
$arrConf['cadena_dsn'] = "mysql://asterisk:asterisk@localhost/call_center";
?>
