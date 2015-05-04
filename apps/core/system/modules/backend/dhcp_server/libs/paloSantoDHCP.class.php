<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0                                                  |
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
  $Id: paloSantoDHCP.class.php,v 1.1 2008/01/04 10:39:57 bmacias Exp $ */

include_once("libs/paloSantoDB.class.php");

/* Clase que implementa DHCP Server */
class PaloSantoDHCP
{
    var $errMsg;

    function getConfigurationDHCP() 
    {
        global $arrLang;

        // Trato de abrir el archivo de configuracion de dhcp
        $arrConfigurationDHCP = NULL;
        $output = $ret = NULL;
        exec('/usr/bin/elastix-helper dhcpconfig --dumpconfig', $output, $ret);
        if ($ret == 0) {
            foreach ($output as $linea_archivo) {
                // RANGO DE IPS
                $patron = "^[[:space:]]*range dynamic-bootp[[:space:]]+([[:digit:]]{1,3})\.([[:digit:]]{1,3})\." .
                        "([[:digit:]]{1,3})\.([[:digit:]]{1,3})[[:space:]]+([[:digit:]]{1,3})\." .
                        "([[:digit:]]{1,3})\.([[:digit:]]{1,3})\.([[:digit:]]{1,3})[[:space:]]?;";
                if(ereg($patron, $linea_archivo, $arrReg)) {
                    $arrConfigurationDHCP["IPS_RANGE"]["in_ip_ini_1"] = $arrReg[1]; 
                    $arrConfigurationDHCP["IPS_RANGE"]["in_ip_ini_2"] = $arrReg[2];
                    $arrConfigurationDHCP["IPS_RANGE"]["in_ip_ini_3"] = $arrReg[3]; 
                    $arrConfigurationDHCP["IPS_RANGE"]["in_ip_ini_4"] = $arrReg[4];
                    $arrConfigurationDHCP["IPS_RANGE"]["in_ip_fin_1"] = $arrReg[5]; 
                    $arrConfigurationDHCP["IPS_RANGE"]["in_ip_fin_2"] = $arrReg[6];
                    $arrConfigurationDHCP["IPS_RANGE"]["in_ip_fin_3"] = $arrReg[7]; 
                    $arrConfigurationDHCP["IPS_RANGE"]["in_ip_fin_4"] = $arrReg[8];
                } 
    
                // LEASE TIME
                $patron = "^[[:space:]]*default-lease-time[[:space:]]([[:digit:]]{1,8})[[:space:]]?;";
                if(ereg($patron, $linea_archivo, $arrReg)) {
                    $arrConfigurationDHCP["LEASE_TIME"]["in_lease_time"] = $arrReg[1];
                } 
    
                // GATEWAY
                $patron = "^[[:space:]]*option routers[[:space:]]+([[:digit:]]{1,3})\.([[:digit:]]{1,3})\." .
                        "([[:digit:]]{1,3})\.([[:digit:]]{1,3})[[:space:]]?";
                if(ereg($patron, $linea_archivo, $arrReg)) {
                    $arrConfigurationDHCP["GATEWAY"]["in_gw_1"] = $arrReg[1]; 
                    $arrConfigurationDHCP["GATEWAY"]["in_gw_2"] = $arrReg[2];
                    $arrConfigurationDHCP["GATEWAY"]["in_gw_3"] = $arrReg[3]; 
                    $arrConfigurationDHCP["GATEWAY"]["in_gw_4"] = $arrReg[4]; 
                } 
    
                // GATEWAY NETMASK
                $patron = "^[[:space:]]*option subnet-mask[[:space:]]+([[:digit:]]{1,3})\.([[:digit:]]{1,3})\." .
                        "([[:digit:]]{1,3})\.([[:digit:]]{1,3})[[:space:]]?";
                if(ereg($patron, $linea_archivo, $arrReg)) {
                    $arrConfigurationDHCP["GATEWAY_NETMASK"]["in_gwm_1"] = $arrReg[1]; 
                    $arrConfigurationDHCP["GATEWAY_NETMASK"]["in_gwm_2"] = $arrReg[2];
                    $arrConfigurationDHCP["GATEWAY_NETMASK"]["in_gwm_3"] = $arrReg[3]; 
                    $arrConfigurationDHCP["GATEWAY_NETMASK"]["in_gwm_4"] = $arrReg[4]; 
                } 
    
                // WINS
                $patron = "^[[:space:]]*option netbios-name-servers[[:space:]]+([[:digit:]]{1,3})\.([[:digit:]]{1,3})\." .
                        "([[:digit:]]{1,3})\.([[:digit:]]{1,3})[[:space:]]?";
                if(ereg($patron, $linea_archivo, $arrReg)) {
                    $arrConfigurationDHCP["WINS"]["in_wins_1"] = $arrReg[1]; 
                    $arrConfigurationDHCP["WINS"]["in_wins_2"] = $arrReg[2];
                    $arrConfigurationDHCP["WINS"]["in_wins_3"] = $arrReg[3]; 
                    $arrConfigurationDHCP["WINS"]["in_wins_4"] = $arrReg[4]; 
                }
    
                // DNSs
                $patron = '/^\s*option domain-name-servers\s+([\d\.\s,]+)/';
                if (preg_match($patron, $linea_archivo, $arrReg)) {
                	$dnsList = preg_split('/,\s*/', $arrReg[1]);
                    foreach ($dnsList as $dnsString) {
                    	$ip = explode('.', $dnsString);
                        if(!isset($arrConfigurationDHCP["DNS1"])) {
                            $arrConfigurationDHCP["DNS1"]["in_dns1_1"] = $ip[0]; 
                            $arrConfigurationDHCP["DNS1"]["in_dns1_2"] = $ip[1];
                            $arrConfigurationDHCP["DNS1"]["in_dns1_3"] = $ip[2]; 
                            $arrConfigurationDHCP["DNS1"]["in_dns1_4"] = $ip[3]; 
                        } else if (!isset($arrConfigurationDHCP["DNS2"])){
                            $arrConfigurationDHCP["DNS2"]["in_dns2_1"] = $ip[0]; 
                            $arrConfigurationDHCP["DNS2"]["in_dns2_2"] = $ip[1];
                            $arrConfigurationDHCP["DNS2"]["in_dns2_3"] = $ip[2]; 
                            $arrConfigurationDHCP["DNS2"]["in_dns2_4"] = $ip[3];
                        } 
                    }
                }
            } //end while
    
            if(!isset($arrConfigurationDHCP["IPS_RANGE"])){
                // Error, no se encontro el rango de IPs, la directiva mas importante...
                $this->errMsg = $arrLang["Could not find IP range"];
            }

            //Lleno de vacio los q no se encontraron... Para tener q mostrar .. esto solo por presentacion.
            if(!isset($arrConfigurationDHCP["DNS2"])){
                $arrConfigurationDHCP["DNS2"]["in_dns2_1"] = ""; 
                $arrConfigurationDHCP["DNS2"]["in_dns2_2"] = "";
                $arrConfigurationDHCP["DNS2"]["in_dns2_3"] = ""; 
                $arrConfigurationDHCP["DNS2"]["in_dns2_4"] = "";
            }
            if(!isset($arrConfigurationDHCP["WINS"])){
                $arrConfigurationDHCP["WINS"]["in_wins_1"] = ""; 
                $arrConfigurationDHCP["WINS"]["in_wins_2"] = "";
                $arrConfigurationDHCP["WINS"]["in_wins_3"] = ""; 
                $arrConfigurationDHCP["WINS"]["in_wins_4"] = "";
            }
            if(!isset($arrConfigurationDHCP["GATEWAY_NETMASK"])){
                $arrConfigurationDHCP["GATEWAY_NETMASK"]["in_gwm_1"] = ""; 
                $arrConfigurationDHCP["GATEWAY_NETMASK"]["in_gwm_2"] = "";
                $arrConfigurationDHCP["GATEWAY_NETMASK"]["in_gwm_3"] = ""; 
                $arrConfigurationDHCP["GATEWAY_NETMASK"]["in_gwm_4"] = ""; 
            }
            if(!isset($arrConfigurationDHCP["LEASE_TIME"])){
                $arrConfigurationDHCP["LEASE_TIME"]["in_lease_time"] = "7200";
            }
        }
        else{
            // Error al abrir el archivo
            $this->errMsg = $arrLang["DHCP configuration reading error: Verify that the DHCP service is installed and try again."];
        }
        return $arrConfigurationDHCP;
    }

    function getStatusServiceDHCP()
    {
        $output = $ret = NULL;
        exec('/sbin/service dhcpd status > /dev/null 2>&1', $output, $ret);
        return ($ret == 0) ? 'active' : 'desactive';
    }

    function startServiceDHCP()
    {
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper dhcpconfig --start 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    function stopServiceDHCP()
    {
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper dhcpconfig --stop 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    function calcularIpSubred($ipCualquiera, $mascaraRed)
    {
        if(empty($ipCualquiera) or empty($mascaraRed)) {
            return false;
        }

        $arrLanIp   = explode(".", $ipCualquiera);
        $arrLanMask = explode(".", $mascaraRed);

        $IPSubnetOct1 = (int)$arrLanIp[0]&(int)$arrLanMask[0];
        $IPSubnetOct2 = (int)$arrLanIp[1]&(int)$arrLanMask[1];
        $IPSubnetOct3 = (int)$arrLanIp[2]&(int)$arrLanMask[2];
        $IPSubnetOct4 = (int)$arrLanIp[3]&(int)$arrLanMask[3];
        $strResultado = $IPSubnetOct1 . "." . $IPSubnetOct2 . "." . $IPSubnetOct3 . "." . $IPSubnetOct4;
        return $strResultado;
    }

    function updateFileConfDHCP(
                $ip_gw, 
                $ip_gw_nm, 
                $ip_wins, 
                $ip_dns1, 
                $ip_dns2, 
                $IPSubnet, 
                $conf_red_actual,
                $ip_ini,
                $ip_fin,
                $in_lease_time)
    {
        // $ip_gw_nm $IPSubnet $conf_red_actual no se usan
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper dhcpconfig --config'.
            ' --ip-start '.escapeshellcmd($ip_ini).
            ' --ip-end '.escapeshellcmd($ip_fin).
            ' --lease-time '.escapeshellcmd($in_lease_time).
            (($ip_gw != '...') ? ' --gateway '.escapeshellcmd($ip_gw) : '').
            (($ip_wins != '...') ? ' --wins '.escapeshellcmd($ip_wins) : '').
            (($ip_dns1 != '...') ? ' --dns1 '.escapeshellcmd($ip_dns1) : '').
            (($ip_dns2 != '...') ? ' --dns2 '.escapeshellcmd($ip_dns2) : '').
            ' 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }
}
?>
