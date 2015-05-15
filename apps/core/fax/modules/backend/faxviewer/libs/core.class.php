<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4                                                |
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
  $Id: puntosF_Fax.class.php,v 1.0 2011-03-31 09:10:00 Alberto Santos F.  asantos@palosanto.com Exp $*/

define('SOAP_DATETIME_FORMAT', 'Y-m-d\TH:i:sP');

$elxPath="/usr/share/elastix";
require_once "$elxPath/libs/misc.lib.php";
require_once "$elxPath/configs/default.conf.php";
require_once "$elxPath/libs/paloSantoACL.class.php";
require_once "$elxPath/apps/faxviewer/libs/paloSantoFaxVisor.class.php";


class core_Fax
{
    /**
     * Description error message
     *
     * @var array
     */
    private $errMsg;

    /**
     * Object paloACL
     *
     * @var object
     */
    private $_pACL;

    /**
     * ACL User ID for authenticated user
     *
     * @var integer
     */
    private $_id_user;

    /**
     * Array that contains a paloDB Object, the key is the DSN of a specific database
     *
     * @var array
     */
    private $_dbCache;

    /**
     * Constructor
     *
     */
    public function core_Fax()
    {
        $this->_pACL    = NULL;
        $this->_id_user = NULL;
        $this->_dbCache = array();
        $this->errMsg   = NULL;
    }

    /**
     * Static function that creates an array with all the functional points with the parameters IN and OUT
     *
     * @return  array     Array with the definition of the function points.
     */
    public static function getFP()
    {
        $arrData["listFaxDocs"]["params_IN"] = array(
            "date"       => array("type" => "date",             "required" => false),
            "direction"  => array("type" => "string",           "required" => false),
            "offset"     => array("type" => "positiveInteger",  "required" => false),
            "limit"      => array("type" => "positiveInteger",  "required" => false)
        );

        $arrData["listFaxDocs"]["params_OUT"] = array(
            "totalfaxcount" => array("type" => "positiveInteger",   "required" => true),
            "faxes"         => array("type" => "array",             "required" => true, "minOccurs"=>"0", "maxOccurs"=>"unbounded",
                "params" => array(
                    "id"                  => array("type" => "positiveInteger",    "required" => true),
                    "modemdev"            => array("type" => "string",             "required" => true),
                    "errormsg"            => array("type" => "string",             "required" => false),
                    "company_name"        => array("type" => "string",             "required" => true),
                    "company_fax"         => array("type" => "string",             "required" => true),
                    "date"                => array("type" => "date",               "required" => true),
                    "type"                => array("type" => "string",             "required" => true),
                    "destiny_name"        => array("type" => "string",             "required" => true),
                    "destiny_fax"         => array("type" => "string",             "required" => true)
                        )
                    )
            );

        $arrData["delFaxDoc"]["params_IN"] = array(
            "id"       => array("type" => "positiveInteger",  "required" => true)
        );

        $arrData["delFaxDoc"]["params_OUT"] = array(
            "return"   => array("type" => "boolean",   "required" => true)
        );

        return $arrData;
    }

    /**
     * Function that creates, if do not exist in the attribute _pACL, a new paloACL object
     *
     * @return  object   paloACL object
     */
    private function & _getACL()
    {
        global $arrConf;

        if (is_null($this->_pACL)) {
            $pDB_acl = $this->_getDB($arrConf['elastix_dsn']['acl']);
            $this->_pACL = new paloACL($pDB_acl);
        }
        return $this->_pACL;
    }

    /**
     * Function that creates, if do not exist in the attribute dbCache, a new paloDB object for the given DSN
     *
     * @param   string   $sDSN   DSN of a specific database
     * @return  object   paloDB object for the entered database
     */
    private function & _getDB($sDSN)
    {
        if (!isset($this->_dbCache[$sDSN])) {
            $this->_dbCache[$sDSN] = new paloDB($sDSN);
        }
        return $this->_dbCache[$sDSN];
    }

    /**
     * Function that reads the login user ID, that assumed is on $_SERVER['PHP_AUTH_USER']
     *
     * @return  integer   ACL User ID for authenticated user, or NULL if the user in $_SERVER['PHP_AUTH_USER'] does not exist
     */
    private function _leerIdUser()
    {
        if (!is_null($this->_id_user)) return $this->_id_user;

        $pACL = $this->_getACL();
        $id_user = $pACL->getIdUser($_SERVER['PHP_AUTH_USER']);
        if ($id_user == FALSE) {
            $this->errMsg["fc"] = 'INTERNAL';
            $this->errMsg["fm"] = 'User-ID not found';
            $this->errMsg["fd"] = 'Could not find User-ID in ACL for user '.$_SERVER['PHP_AUTH_USER'];
            $this->errMsg["cn"] = get_class($this);
            return NULL;
        }
        $this->_id_user = $id_user;
        return $id_user;
    }

    /**
     * Function that verifies if the parameter can be parsed as a date, and returns the canonic value of the date
     * like yyyy-mm-dd in local time.
     *
     * @param   string   $sDateString   string date to be parsed as a date
     * @return  date     parsed date, or NULL if the $sDateString can not be parsed
     */
    private function _checkDateFormat($sDateString)
    {
        $sTimestamp = strtotime($sDateString);
        if ($sTimestamp === FALSE) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Unrecognized date format, expected yyyy-mm-dd';
            $this->errMsg["cn"] = get_class($this);
            return NULL;
        }
        return date('Y-m-d', $sTimestamp);
    }

    /**
     * Function that verifies the parameters offset and limit, if offset is not set it will be set to 0
     *
     * @param   integer   $offset   value of offset passed by reference
     * @param   integer   $limit    value of limit passed by reference
     * @return  mixed    true if both parameters are correct, or NULL if an error exists
     */
    private function _checkOffsetLimit(&$offset, &$limit)
    {
        // Validar los parámetros de offset y limit
        if (!isset($offset)) $offset = 0;
        if (!preg_match('/\d+/', $offset)) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Invalid offset, must be numeric and positive';
            $this->errMsg["cn"] = get_class($this);
            return NULL;
        }
        if (isset($limit) && !preg_match('/\d+/', $limit)) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid format';
            $this->errMsg["fd"] = 'Invalid limit, must be numeric and positive';
            $this->errMsg["cn"] = get_class($this);
            return NULL;
        }
        return TRUE;
    }

    /**
     * Function that verifies if the authenticated user is authorized to the passed module.
     *
     * @param   string   $sModuleName   name of the module to check if the user is authorized
     * @return  boolean    true if the user is authorized, or false if not
     */
    private function _checkUserAuthorized($sModuleName)
    {
        $pACL = $this->_getACL();        
        $id_user = $this->_leerIdUser();
        if (!$pACL->isUserAuthorizedById($id_user, $sModuleName)) {
            $this->errMsg["fc"] = 'UNAUTHORIZED';
            $this->errMsg["fm"] = 'Not authorized for this module: '.$sModuleName;
            $this->errMsg["fd"] = 'Your user login is not authorized for this functionality. Please contact your system administrator.';
            $this->errMsg["cn"] = get_class($this); 
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Functional point that list the sent and received faxes by the system
     *
     * @param   date      $date        (optional) date for which is desired the report, or all if omitted
     * @param   string    $direction   (optional) 'in' for faxes received 'out' for sent faxes, or all if omitted
     * @param   integer   $offset      (optional) start of records or 0 if omitted
     * @param   integer   $limit       (optional) limit records or all if omitted
     * @return  array     Array of records with the following information
     *                          totalfaxcount (positiveInteger) Total number of records
     *                          Faxes: (array) Array of records fax with the following info:
     *                              id (positiveInteger) fax registration ID
     *                              modemdev (string) TTY device on the ingress / egress fax
     *                              errormsg (string, optional) Error message (if any) for failed fax
     *                              company_name (string) Label descriptive fax company
     *                              company_fax (string) descriptive label company number
     *                              date (datetime) date and time sent / received fax
     *                              type (string) 'in ' Fax received 'out' for fax sent
     *                              destiny_name (string) Name of device used fax
     *                              destiny_fax (string) fax telephone number used
     *                   or false if an error exists
     */
    function listFaxDocs($date, $direction, $offset, $limit)
    {
        if (!$this->_checkUserAuthorized('faxviewer')) return false;

        if (!$this->_checkOffsetLimit($offset,$limit)) return false;

        // Validación de la fecha opcional del reporte
        $sFecha = isset($date) ? $this->_checkDateFormat($date) : NULL;

        // Validación de dirección para faxes
        if (isset($direction) && !in_array($direction, array('in','out'))) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid direction';
            $this->errMsg["fd"] = 'Fax direction must be one of: in out';
            $this->errMsg["cn"] = get_class($this);
            return false;
        }

		//obtenemos las credenciales del usuario
		$arrCredentials=getUserCredentials();

        // Cuenta de registros que cumplen con las condiciones
        $oFax = new paloFaxVisor();
        $iNumFaxes = $oFax->obtener_cantidad_faxes($arrCredentials["id_organization"],
            '', // company_name
            '', // company_fax
            (is_null($sFecha) ? '' : $sFecha), 
            (isset($direction) ? $direction : ''));
        // El método obtener_cantidad_faxes devuelve un arreglo vacío en caso de error
        if (is_array($iNumFaxes)) {
            $this->errMsg["fc"] = 'DBERROR';
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["fd"] = 'Unable to count faxes - '.$oFax->errMsg;
            $this->errMsg["cn"] = get_class($oFax);
            return false;
        }
        $infoFax = array(
            'totalfaxcount' =>  $iNumFaxes,
            'faxes'         =>  array(),
        );

        if ($infoFax['totalfaxcount'] > 0) {
            if (isset($offset) && !isset($limit)) {
                $limit = $infoFax['totalfaxcount']; 
            }
            $listaTmpFax = $oFax->obtener_faxes($arrCredentials["id_organization"],
                '', // company_name
                '', // company_fax
                (is_null($sFecha) ? '' : $sFecha),
                $offset,
                $limit,
                (isset($direction) ? $direction : ''));
            /* obtener_faxes devuelve arreglo vacío en caso de error, lo que es
             * difícil de distinguir de una petición exitosa fuera de rango. */
            if (count($listaTmpFax) == 0 && $oFax->errMsg != '') {
                $this->errMsg["fc"] = 'DBERROR';
                $this->errMsg["fm"] = 'Database operation failed';
                $this->errMsg["fd"] = 'Unable to read faxes - '.$oFax->errMsg;
                $this->errMsg["cn"] = get_class($oFax);
                return false;
            }
            function _transformarFaxDoc($tupla)
            {
                return array(
                    'id'            =>  $tupla['id'],
                    'modemdev'      =>  $tupla['modemdev'],
                    'errormsg'      =>  ((trim($tupla['errormsg']) == '') ? NULL : trim($tupla['errormsg'])),
                    'company_name'  =>  $tupla['company_name'],
                    'company_fax'   =>  $tupla['company_fax'],
                    'date'          =>  date(SOAP_DATETIME_FORMAT, strtotime($tupla['date'])),
                    'type'          =>  $tupla['type'],
                    'destiny_name'  =>  $tupla['destiny_name'],
                    'destiny_fax'   =>  $tupla['destiny_fax'],
                );
            }
            $infoFax['faxes'] = array_map('_transformarFaxDoc', $listaTmpFax);
        }

        return $infoFax;
    }

    /**
     * Functional point that deletes a document fax of the database, and deletes also the PDF document associated to the fax if exist
     *
     * @param   integer      $id        ID of the fax to be deleted
     * @return  boolean      true if the document fax was deleted, false if an error exists
     */
    function delFaxDoc($id)
    {

        if (!$this->_checkUserAuthorized('faxviewer')) return false;

        // Verificar presencia de ID del fax
        if (!isset($id) || !preg_match('/^\d+$/', $id)) {
            $this->errMsg["fc"] = 'PARAMERROR';
            $this->errMsg["fm"] = 'Invalid ID';
            $this->errMsg["fd"] = 'Fax ID must be nonnegative integer';
            $this->errMsg["cn"] = get_class($this);
            return false;
        }
        $id = (int)$id;

		//obtenemos las credenciales del usuario
		$arrCredentials=getUserCredentials();

        // Borrar el registro y el documento de fax, dado su ID
        $oFax = new paloFaxVisor();
        $bExito = $oFax->deleteInfoFax($id,$arrCredentials["id_organization"]);
        if (!$bExito) {
            $this->errMsg["fm"] = 'Database operation failed';
            $this->errMsg["cn"] = get_class($oFax);
            if ($oFax->errMsg != '') {
                $this->errMsg["fc"] = 'DBERROR';
                $this->errMsg["fd"] = 'Unable to delete fax information - '.$oFax->errMsg;
            } else {
                $this->errMsg["fc"] = 'INTERNALERROR';
                $this->errMsg["fd"] = 'Unable to delete fax document';
            }
        }
        return $bExito;
    }

    /**
     * 
     * Function that returns the error message
     *
     * @return  string   Message error if had an error.
     */
    public function getError()
    {
        return $this->errMsg;
    }
}
?>