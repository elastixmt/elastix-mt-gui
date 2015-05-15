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
  $Id: SOAP_Fax.class.php,v 1.0 2011-03-31 13:00:00 Alberto Santos F.  asantos@palosanto.com Exp $*/

$root = $_SERVER["DOCUMENT_ROOT"];
require_once("$root/modules/faxviewer/libs/core.class.php");

class SOAP_Fax extends core_Fax
{
    /**
     * SOAP Server Object
     *
     * @var object
     */
    private $objSOAPServer;

    /**
     * Constructor
     *
     * @param  object   $objSOAPServer     SOAP Server Object
     */
    public function SOAP_Fax($objSOAPServer)
    {
         parent::core_Fax();
         $this->objSOAPServer = $objSOAPServer;
    }

    /**
     * Static function that calls to the function getFP of its parent
     *
     * @return  array     Array with the definition of the function points.
     */
    public static function getFP()
    {
        return parent::getFP();
    }

    /**
     * Function that implements the SOAP call to list the faxes received and sent by the system. If an error exists a SOAP
     * fault is thrown
     * 
     * @param mixed request:
     *                  date:       (date,opcional) date for which is desired the report, or all if omitted
     *                  direction:  (string,opcional) 'in' for faxes received 'out' for sent faxes, or all if omitted
     *                  offset:     (positiveInteger,opcional) start of records or 0 if omitted
     *                  limit:      (positiveInteger, opcional) limit records or all if omitted
     * @return  mixed   Array with the information of the document faxes.
     */
    public function listFaxDocs($request)
    {
        $return = parent::listFaxDocs(
            empty($request->date) ? NULL : $request->date,
            empty($request->direction) ? NULL : $request->direction,
            empty($request->offset) ? NULL : $request->offset,
            empty($request->limit) ? NULL : $request->limit);
        if(!$return){
            $eMSG = parent::getError();
            $this->objSOAPServer->fault($eMSG['fc'],$eMSG['fm'],$eMSG['cn'],$eMSG['fd'],'fault');
        }
        return $return;
    }

    /**
     * Function that implements the SOAP call to delete a fax document from the database and also delete the associated PDF
     * document to fax, if the file exists. If an error exists a SOAP
     * 
     * @param   mixed   $request:
     *                      id: ID of fax to be deleted
     * @return  mixed   Array with boolean data, true if was successful or false if an error exists
     */
    public function delFaxDoc($request)
    {
        $return = parent::delFaxDoc(empty($request->id) ? NULL : $request->id);
        if(!$return){
            $eMSG = parent::getError();
            $this->objSOAPServer->fault($eMSG['fc'],$eMSG['fm'],$eMSG['cn'],$eMSG['fd'],'fault');
        }
        return array("return" => $return);
    }
}
?>