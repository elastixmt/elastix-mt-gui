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
  $Id: paloSantoLongPoll.class.php,v 1.0 2012-05-24 11:30:00 Alberto Santos F.  asantos@palosanto.com Exp $*/

abstract class LongPoll
{
    /**
     * Time in seconds that the script will sleep
     *
     * @var integer
     */
    protected $sleepTime;

    /**
     * Time in seconds the script will wait until finish the poll
     *
     * @var integer
     */
    protected $timeout;

    /**
     * Constructor
     *
     */
    function __construct($sleepTime=5, $timeout=30)
    {
	$this->sleepTime = $sleepTime;
	$this->timeout = $timeout;
    }

    /**
     * This function will wait until there is some data in the server or the timeout has been reached
     *
     * @return  mixed   return the data of the server in case there is some or NULL in case the timeout
     *			was reached
     */
    public function run()
    {
	$data = NULL;
	$timeout = 0;

	set_time_limit($this->timeout + $this->sleepTime + 15);

	while(is_null($data) && $timeout < $this->timeout){
	    $data = $this->getData();
	    if(is_null($data)){
		//Wait for response
		sleep($this->sleepTime);
		$timeout += $this->sleepTime;
	    }
	}

	return $data;
    }

    /**
     * This function must be defined in the class that extends of this one. This function must return
     * the data requested or NULL in case there is no data
     *
     * @return  mixed   return the data of the server in case there is some or NULL in case there is
     *			data
     */
    protected abstract function getData();
}

?>