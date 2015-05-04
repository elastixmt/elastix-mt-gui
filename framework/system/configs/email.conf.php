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
  $Id: email.conf.php,v 1.1.1.1 2007/07/06 21:31:56 gcarrillo Exp $ */


$configPostfix2 = isPostfixToElastix2();// in misc.lib.php
$clave = obtenerClaveCyrusAdmin();

if(!$configPostfix2){
    define("SASL_DOMAIN","example.com");
}

$GLOBALS['CYRUS'] = array(
              'HOST'    => "localhost",
              'PORT'    => 143,
              'ADMIN'   => 'cyrus',
              'PASS'    => $clave
              );

$script="require [\"fileinto\",\"vacation\"];\n";
$script.="if header :contains \"X-Spam-Status\" \"Yes,\" {\n".
         " fileinto \"spam\";\n".
         "}\n".
         "\r\n";

define("DEFAULT_SCRIPT",$script);
?>
