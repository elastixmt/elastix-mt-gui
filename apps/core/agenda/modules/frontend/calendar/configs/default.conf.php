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
  $Id: default.conf.php,v 1.1 2010-01-05 11:01:26 Bruno Macias V. bmacias@elastix.org Exp $ */
    global $arrConf;
    global $arrConfModule;

    $arrConfModule['module_name']        = 'calendar';
    $arrConfModule['templates_dir']      = 'themes';
    $arrConfModule['dsn_conn_database']  = "sqlite3:///$arrConf[elastix_dbdir]/elastix.db";
    $arrConfModule['dsn_conn_database1'] = "sqlite3:///$arrConf[elastix_dbdir]/acl.db";
    $arrConfModule['dsn_conn_database3'] = "sqlite3:///$arrConf[elastix_dbdir]/elastix.db";
    //ex2: $arrConfModule['dsn_conn_database'] = "mysql://user:password@ip_host_sever_mysql/base_name";
    $arrConfModule['start_year'] = '2008';
    $arrConfModule['end_year']   = '2060';
    $arrConfModule['sDirectorioBase'] = '/tmp';
    $arrConfModule['dir_outgoing'] = "/var/spool/asterisk/outgoing";
?>