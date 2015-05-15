<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.3                                                |
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
  $Id: paloSantoAccessaudit.class.php,v 1.1 2010-12-18 03:12:50 Bruno Macias bmacias@palosanto.com Exp $ */

include_once "apps/asterisk_log/libs/LogParser_Full.class.php";

class paloSantoAccessaudit{
    var $_DB;
    var $errMsg;
    var $astLog;

    function paloSantoAccessaudit()
    {
        $this->astLog = new LogParser_Full("/var/log/elastix","audit");
    }

    function ObtainNumAccessLogs($sFecha)
    {
        $total = $this->astLog->numeroBytesMensajesFecha($sFecha);
        return array($total);
    }

    function ObtainAccessLogs($limit, $offset, $sFecha, $sCadenaHighlight = NULL, $isExport=false)
    {
        $iBytesLeidos = 0;
        $lineas = array();
        $this->astLog->posicionarMensaje($sFecha, $offset);
        $bContinuar = TRUE;
        while ($bContinuar) {
            $pos = $this->astLog->obtenerPosicionMensaje();
            $s = $this->astLog->siguienteMensaje();            
            // Se desactiva la condición porque ya no todas las líneas empiezan con corchete
            if (!(count($lineas) == 0 && !is_null($s) && $s{0} != '[')) {
                $regs = NULL;
                if (ereg('^\[([[:alnum:][:space:]\:]+)\][[:space:]]+([[:alpha:]]+)[[:space:]]+([^[:space:]]+):[[:space:]]+(.*)$', $s, $regs)) {
                    $l = array(
                        'offset'=> $pos[1],
                        'fecha' => $regs[1],
                        'tipo' => $regs[2],
                        'origen' => $regs[3],
                        'linea' => $regs[4],
                    );
                } else {
                    $l = array(
                        'offset'=> $pos[1],
                        'fecha' =>  '',
                        'tipo'  =>  '',
                        'origen'=> '',
                        'linea' =>  $s,
                    );
                }
                if($isExport){
                    $lineas[] = $l;
                }
                else{
                    $l['linea'] = htmlentities($l['linea']);

                    if (!is_null($sCadenaHighlight) && trim($sCadenaHighlight) != '') {
                        $l['linea'] = str_replace($sCadenaHighlight, "<span style=\"background:#ffff00;\">$sCadenaHighlight</span>", $l['linea']);
                    }
                    $l['linea'] = '<pre>'.$l['linea'].'</pre>';
                    $lineas[] = $l;
                }
            }
            $pos = $this->astLog->obtenerPosicionMensaje();
            $iBytesLeidos = $pos[1] - $offset;
            $bContinuar = (!is_null($s) && $iBytesLeidos < $limit);
        }
        return $lineas;
    }
}
?>
