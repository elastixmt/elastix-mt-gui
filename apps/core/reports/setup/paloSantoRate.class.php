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
  $Id: paloSantoRate.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ */

if (isset($arrConf['basePath'])) {
    include_once($arrConf['basePath'] . "/libs/paloSantoDB.class.php");
} else {
    include_once("libs/paloSantoDB.class.php");
}

class paloRate {

    var $_DB; // instancia de la clase paloDB
    var $errMsg;

    function paloRate(&$pDB)
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

    /**
     * Procedimiento para obtener el listado de los rates existentes. Si
     * se especifica prefix y num_digits, el listado contendrá únicamente el rate
     * indicado por los valores datos. De otro modo, se listarán todos los rates.
     *
     * @param varchar   $prefix    Si != NULL, indica el prefix del rate a recoger
     *
     * @return array    Listado de rates en el siguiente formato, o FALSE en caso de error:
     *  array(
     *      array(id, prefix, name, rate, rate_offset, trunk),
     *      ...
     *  )
     */
    function getRates($id_rate = NULL)
    {
        $arr_result = FALSE;
        if (!is_null($id_rate) && !preg_match('/^[[:digit:]]+$/', "$id_rate")) {
            $this->errMsg = "Rate ID is not valid";
        } 
        else {
            $this->errMsg = "";
              $sPeticionSQL = "SELECT id, prefix, name, rate, rate_offset, trunk FROM rate".
                (is_null($id_rate) ? '' : " WHERE id = $id_rate");
            $sPeticionSQL .=" ORDER BY name";
            $arr_result =& $this->_DB->fetchTable($sPeticionSQL);
            if (!is_array($arr_result)) {
                $arr_result = FALSE;
                $this->errMsg = $this->_DB->errMsg;
            }
        }
        return $arr_result;
    }

    /**
     * Procedimiento para crear un nuevo rate 
     *
     * @param string    $prefix       prefix para el rate
     * @param string    $name         nombre para el rate
     * @param string    $rate         rate
     * @param string    $rate_offset  rate_offset
     * @param string    $trunk        trunk for this rate
     *
     * @return bool     VERDADERO si el rate se crea correctamente, FALSO en error
     */
    function createRate($prefix, $name, $rate, $rate_offset, $trunk)
    {
        $bExito = FALSE;
        if ($prefix == "" && $num_digits == "") {
            $this->errMsg = "Prefix can't be empty";
        } else {
            //verificar que no exista la misma combinacion de prefijo
            $sPeticionSQL = "SELECT id FROM rate ".
                " WHERE prefix = '$prefix' AND trunk = '$trunk'";
            $arr_result =& $this->_DB->fetchTable($sPeticionSQL);
            if (is_array($arr_result) && count($arr_result)>0) {
                $bExito = FALSE;
                $this->errMsg = "Rate for prefix already exists";
            }else{
                $sPeticionSQL = paloDB::construirInsert(
                    "rate",
                    array(
                        "prefix"       =>  paloDB::DBCAMPO($prefix),
                        "name"         =>  paloDB::DBCAMPO($name),
                        "rate"         =>  paloDB::DBCAMPO($rate),
                        "rate_offset"  =>  paloDB::DBCAMPO($rate_offset),
                        "trunk"        =>  paloDB::DBCAMPO($trunk)
                    )
                );
                if ($this->_DB->genQuery($sPeticionSQL)) {
                    $bExito = TRUE;
                } else {
                    $this->errMsg = $this->_DB->errMsg;
                }
            }
        }

        return $bExito;
    }

    /**
     * Procedimiento para modificar el rate con el prefix 
     *
     * @param string    $prefix       prefix para el rate
     * @param string    $name         nombre para el rate
     * @param string    $rate         rate
     * @param string    $rate_offset  rate_offset
     * @param string    $trunk        trunk
     *
     * @return bool VERDADERO si se ha modificar correctamente el usuario, FALSO si ocurre un error.
     */
    function updateRate($id_rate, $prefix, $name, $rate, $rate_offset ,$trunk)
    {
        $bExito = FALSE;
        if (!preg_match("/^[[:digit:]]+$/", "$id_rate")) {
            $this->errMsg = "Rate ID is not valid";
        } else {
            //modificar rate
                    $sPeticionSQL = paloDB::construirUpdate(
                        "rate",
                        array(
                            "name"          =>  paloDB::DBCAMPO($name),
                            "rate"          =>  paloDB::DBCAMPO($rate),
                            "rate_offset"   =>  paloDB::DBCAMPO($rate_offset),
                            "trunk"         =>  paloDB::DBCAMPO($trunk)
                         ),
                        array(
                            "id"  => $id_rate)
                        );
                    if ($this->_DB->genQuery($sPeticionSQL)) {
                        $bExito = TRUE;
                    } else {
                        $this->errMsg = $this->_DB->errMsg;
                    }
        }
        return $bExito;
    }


    function deleteRate($id_rate)
    {
        $bExito = FALSE;
        if (!preg_match('/^[[:digit:]]+$/', "$id_rate")) {
            $this->errMsg = "Rate ID is not valid";
        } 
        else {
            $this->errMsg = "";
            $sPeticionSQL = 
                "DELETE FROM rate WHERE id = '$id_rate'";
            $bExito = TRUE;
            $bExito = $this->_DB->genQuery($sPeticionSQL);
            if (!$bExito) {
                $this->errMsg = $this->_DB->errMsg;
                break;
            }

        }
        return $bExito;
    }



  function buscarTarifa($sNumeroMarcado,&$tarifa,$trunk)
  {
    $bExito=TRUE;

    if ($sNumeroMarcado != "" ) {

        # Construir un límite inferior para la búsqueda del número. Esto
        # permite el uso del índice por prefijo telefónico. Para un número como
        # 229031064, el límite inferior es 1999999999999999
        $sLimiteInferior = (chr(ord(substr($sNumeroMarcado, 0, 1)) - 1)).str_repeat('9',15) ;


        $listaLimites = $this->_privado_construirListaLimitesPrefijo($sNumeroMarcado);
        if (is_null($listaLimites)) {
           $sPeticionSQL="SELECT id, rate, rate_offset, name FROM rate ".
                     "WHERE prefix > '$sLimiteInferior' ".
                     "AND prefix <= '$sNumeroMarcado' ".
                     "AND SUBSTR('$sNumeroMarcado',1,length(prefix)) = prefix ".
                     "AND trunk = '$trunk' ".
                     "ORDER BY prefix DESC LIMIT 0,1 ";
         }else{
           $sPeticionSQL="SELECT id, rate, rate_offset, name FROM rate ".
                     "WHERE (
                          prefix = '{$listaLimites[0]}' OR
                          prefix = '{$listaLimites[1]}' OR
                          prefix = '{$listaLimites[2]}' OR
                          prefix = '{$listaLimites[3]}' OR
                          prefix >= '{$listaLimites[4]}'
                      ) ".
                     "AND prefix <= '$sNumeroMarcado' ".
                     "AND SUBSTR('$sNumeroMarcado',1,length(prefix)) = prefix ".
                     "AND trunk = '$trunk' ".
                     "ORDER BY prefix DESC LIMIT 0,1 ";
         }
        # Ejecutar la sentencia y verificar si se obtiene una tarifa
            $result = $this->_DB->fetchTable($sPeticionSQL, false);
            if(!is_array($result))
            {
                $bExito=FALSE;
                $this->errMsg = $this->_DB->errMsg;
            } else {
                $tarifa = array();
                foreach($result as $key => $value)
                {
                    $id  = $value[0];
                    $tarifa[$id]['rate'] = $value[1];
                    $tarifa[$id]['offset'] = $value[2];
                    $tarifa[$id]['name'] = utf8_decode($value[3]);
                    $tarifa[$id]['id'] = $id;
                }
            }
    }
    return $bExito;
  }

# Procedimiento que construye una lista de límites semicerrados para la búsqueda
# de prefijos. Para un prefijo como 13232633128, se construye
# la siguiente lista:
#   1
#   13??
#   132
#   1323
#   13232
# Si el número tiene menos de 5 dígitos, se devuelve null
    function _privado_construirListaLimitesPrefijo($sNumero)
    {
        $lista = array();
    
        if (strlen($sNumero) >= 5){
            for ($i = 1; $i <= 5; $i++) { $lista[$i - 1] = substr($sNumero, 0, $i); }
        }else
            return NULL;

       return $lista;
    }
}
?>