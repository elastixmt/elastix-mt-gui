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
  $Id: index.php,v 1.1 2007/01/09 23:49:36 alex Exp $
*/
if (file_exists('/usr/share/php/magpierss/rss_fetch.inc'))
    require_once 'magpierss/rss_fetch.inc';
else require_once "libs/magpierss/rss_fetch.inc";

class Applet_News
{
	function handleJSON_getContent($smarty, $module_name, $appletlist)
    {
        $respuesta = array(
            'status'    =>  'success',
            'message'   =>  '(no message)',
        );
        
        define('MAGPIE_CACHE_DIR', '/tmp/rss-cache');
        define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
        $infoRSS = @fetch_rss('http://elastix.org/index.php?option=com_mediarss&feed_id=1&format=raw');
        $sMensaje = magpie_error();
        if (strpos($sMensaje, 'HTTP Error: connection failed') !== FALSE) {
            $respuesta['status'] = 'error';
            $respuesta['message'] = _tr('Could not get web server information. You may not have internet access or the web server is down');
        } else {
            // Formato de fecha y hora
            for ($i = 0; $i < count($infoRSS->items); $i++) {
            	$infoRSS->items[$i]['date_format'] = date('Y.m.d', $infoRSS->items[$i]['date_timestamp']);
            }
            
            $smarty->assign(array(
                'WEBSITE'   =>  'http://www.elastix.org',
                'NO_NEWS'   =>  _tr('No News to display'),
                'NEWS_LIST' =>  array_slice($infoRSS->items, 0, 7),
            ));
            $local_templates_dir = getWebDirModule($module_name)."/applets/News/tpl";
            $respuesta['html'] = $smarty->fetch("$local_templates_dir/rssfeed.tpl");
        }
    
        $json = new Services_JSON();
        Header('Content-Type: application/json');
        return $json->encode($respuesta);
    }
}