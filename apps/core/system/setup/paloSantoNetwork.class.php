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
  $Id: paloSantoNetwork.class.php,v 1.1.1.1 2007/07/06 21:31:55 gcarrillo Exp $ */

class paloNetwork
{
    var $errMsg;

    // Constructor
    function paloNetwork()
    {
        $this->errMsg = "";
    }

    private static function obtener_tipo_interfase($if)
    {
        $filePattern = "/etc/sysconfig/network-scripts/ifcfg-";
        $fileIf      = $filePattern . $if;
        $lineaIfcfg  = "";
        $type        = "static";

        if(file_exists($fileIf))
        {
            if($fh = fopen($fileIf, "r")) {
                while(!feof($fh)) {
                    $lineaIfcfg = fgets($fh, 4048);
                    if(preg_match("/^BOOTPROTO[[:space:]]*=[[:space:]]*\"?dhcp/", $lineaIfcfg)) {
                        $type = "dhcp";
                    }
                }
                fclose($fh);
            } else {
                // error
                $type = "";
            }
        }else $type = ""; //error

        return $type;
    }
 
    /**
     * Procedimiento para obtener información sobre las interfases de red del
     * sistema. Actualmente se listan las interfases de tipo Ethernet y las
     * localhost. Para cada interfaz de red se crea una entrada cuya clave es
     * el nombre de la interfaz, y el valor es un arreglo con los siguientes
     * elementos:
     *  Name        : 'Ethernet' o 'Loopback'. Si se identifica la interfaz 
     *                como un alias de otra interfaz, se la marca como 
     *                Ethernet X Alias Y
     *  Type        : Tipo de configuración de red: static dhcp ...
     *  HW_info     : Información de hardware sobre la interfaz. Sólo para Ethernet.
     *  HWaddr      : MAC de la interfaz Ethernet
     *  Inet Addr   : IPv4 asignada a la interfaz de red
     *  Mask        : Máscara IPv4 asignada a la intefaz de red
     *  Running     : 'Yes' si la interfaz está activa
     *  RX packets  : Número de paquetes recibidos
     *  RX bytes    : Número de bytes recibidos
     *  TX packets  : Número de paquetes enviados
     *  TX bytes    : Número de bytes enviados
     * 
     * @return array    Lista de interfases de red
     */
    static function obtener_interfases_red()
    {
    	$interfases = array();
        
        // Se listan todas las interfases físicas, y se toman loopback y ether
        /*
        [root@elx2 net]# ip link show
        1: lo: <LOOPBACK,UP,LOWER_UP> mtu 16436 qdisc noqueue 
            link/loopback 00:00:00:00:00:00 brd 00:00:00:00:00:00
        2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast qlen 1000
            link/ether 08:00:27:2b:f0:14 brd ff:ff:ff:ff:ff:ff
        3: sit0: <NOARP> mtu 1480 qdisc noop 
            link/sit 0.0.0.0 brd 0.0.0.0
         */
        $output = NULL;
        exec('/sbin/ip link show', $output);
        $if_actual = NULL;  // La interfaz que se examina
        $if_flags = NULL;
        foreach ($output as $s) {
        	$regs = NULL;
            if (preg_match('/^\d+:\s+(\w+):\s*<(.*)>/', $s, $regs)) {
            	$if_actual = $regs[1];
                $if_flags = explode(',', $regs[2]);
            } elseif (preg_match('!\s*link/(loopback|ether) ([[:xdigit:]]{2}(:[[:xdigit:]]{2}){5})!', $s, $regs)) {
            	$interfases[$if_actual] = array(
                    'Name'          =>  (($regs[1] == 'ether') ? 'Ethernet' : 'Loopback'),
                    'Link'          =>  $regs[1],
                    'Type'          =>  NULL,
                    'HW_info'       =>  NULL,
                    'HWaddr'        =>  $regs[2],
                    'Inet Addr'     =>  NULL,
                    'Mask'          =>  NULL,
                    'Running'       =>  in_array('UP', $if_flags) ? 'Yes' : NULL,
                    'RX packets'    =>  0,
                    'RX bytes'      =>  0,
                    'TX packets'    =>  0,
                    'TX bytes'      =>  0,
                );
                if ($regs[1] == 'ether') {
                    if (preg_match('/^eth(\d+)$/', $if_actual, $regs)) {
                    	$interfases[$if_actual]['Name'] = 'Ethernet '.$regs[1];
                    } else {
                    	$interfases[$if_actual]['Name'] .= $if_actual;
                    }
                }
            }
        }
        
        /* Para cada interfaz física, se leen las estadísticas de bytes y 
         * paquetes transmitidos y recibidos, así como el controlador del
         * dispositivo de red. */
        foreach (array_keys($interfases) as $if_actual) {
        	$fuentes = array(
                'RX packets'    =>  "/sys/class/net/$if_actual/statistics/rx_packets",
                'RX bytes'      =>  "/sys/class/net/$if_actual/statistics/rx_bytes",
                'TX packets'    =>  "/sys/class/net/$if_actual/statistics/tx_packets",
                'TX bytes'      =>  "/sys/class/net/$if_actual/statistics/tx_bytes",
            );
            foreach ($fuentes as $k => $p) {
            	if (file_exists($p))
                    $interfases[$if_actual][$k] = trim(file_get_contents($p));
            }
            
            // Nombre del controlador del dispositivo de red
            if (file_exists("/sys/class/net/$if_actual/device/driver")) {
            	$interfases[$if_actual]['HW_info'] = basename(readlink("/sys/class/net/$if_actual/device/driver"));
            }
        }

        /* Para cada interfaz física, se listan sus IPs. La que tiene la 
           interfaz sin adornos es la IP de la interfaz. Otras IPs definen
           alias de la interfaz. */ 
        /*
        [root@elx2 net]# ip addr show dev eth0
        2: eth0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast qlen 1000
            link/ether 08:00:27:2b:f0:14 brd ff:ff:ff:ff:ff:ff
            inet 192.168.5.193/16 brd 192.168.255.255 scope global eth0
            inet 192.168.6.1/24 brd 192.168.6.255 scope global eth0:0
            inet6 fe80::a00:27ff:fe2b:f014/64 scope link 
               valid_lft forever preferred_lft forever
        [root@elx2 net]# ip addr show dev lo
        1: lo: <LOOPBACK,UP,LOWER_UP> mtu 16436 qdisc noqueue 
            link/loopback 00:00:00:00:00:00 brd 00:00:00:00:00:00
            inet 127.0.0.1/8 scope host lo
            inet6 ::1/128 scope host 
               valid_lft forever preferred_lft forever
         */        
        foreach (array_keys($interfases) as $if_actual) {
        	$output = NULL;
            exec('/sbin/ip addr show dev '.$if_actual, $output);
            foreach ($output as $s) {
            	if (preg_match('|\s*inet (\d+\.\d+\.\d+.\d+)/(\d+).+\s((\w+)(:(\d+))?)\s*$|', trim($s), $regs)) {
            		// Calcular IP de máscara a partir de número de bits
                    $iMaskBits = $regs[2];
                    $iMask = (0xFFFFFFFF << (32 - $iMaskBits)) & 0xFFFFFFFF;
                    $sMaskIP = implode('.', array(
                        ($iMask >> 24) & 0xFF,
                        ($iMask >> 16) & 0xFF,
                        ($iMask >>  8) & 0xFF,
                        ($iMask      ) & 0xFF,
                    ));
                    
                    // Verificar si es IP de interfaz o de alias
                    if ($regs[3] == $if_actual) {
                    	$interfases[$if_actual]['Inet Addr'] = $regs[1];
                        $interfases[$if_actual]['Mask'] = $sMaskIP;
                    } else {
                    	$if_alias = $regs[3];
                        $if_orig = $regs[4];
                        $if_aliasnum = $regs[6];
                        $interfases[$if_alias] = array(
                            'Name'          =>  $interfases[$if_orig]['Name'].' Alias '.$if_aliasnum,
                            'Type'          =>  NULL,
                            'HWaddr'        =>  $interfases[$if_orig]['HWaddr'],
                            'Inet Addr'     =>  $regs[1],
                            'Mask'          =>  $sMaskIP,
                            'Running'       =>  $interfases[$if_orig]['Running'],
                        );
                    }
            	}
            }
        }
        
        // Tipo de interfaz de red configurada en /etc/sysconfig/network-scripts/
        foreach (array_keys($interfases) as $if_actual) {
        	$interfases[$if_actual]['Type'] = self::obtener_tipo_interfase($if_actual);
        }

        return $interfases;
    }
    
    // Es decir que no se incluye "lo" ni interfases virtuales
    static function obtener_interfases_red_fisicas()
    {
        $arrInterfasesRedPreliminar=array();
        $arrInterfasesRedPreliminar=self::obtener_interfases_red();
    
        // Selecciono solo las interfases de red fisicas
        $arrInterfasesRed=array();
        foreach($arrInterfasesRedPreliminar as $nombreReal => $arrData) {
            if (isset($arrData['Link']) && $arrData['Link'] == 'ether')
                $arrInterfasesRed[$nombreReal]=$arrData;
        }

        return $arrInterfasesRed;
    }

    /**
     * Procedimiento para interrogar la configuración general de red del 
     * sistema. 
     * 
     * @return array arreglo con los siguientes valores:
     *      dns:        arreglo con 0 o más DNS asignados
     *      host:       nombre de host para el sistema
     *      gateway:    El gateway predeterminado asignado para el sistema
     */
    static function obtener_configuracion_red()
    {
        $archivoResolv = "/etc/resolv.conf";
        $arrResult = array(
            'dns'       =>  array(),
            'host'      =>  NULL,
            'gateway'   =>  NULL,
        );

        //- Obtengo los dnss
        if($fh=fopen($archivoResolv, "r")) {
            while(!feof($fh)) {
                $linea = fgets($fh, 4048); 
                if(preg_match("/^nameserver[[:space:]]+(.*)$/", $linea, $arrReg)) {
                    $arrResult['dns'][] = $arrReg[1];
                }                
            } 

        } else {
            // Error?
        }

        //- Obtengo el hostname
        $arrOutput = NULL;
        exec("/bin/hostname", $arrOutput);
        $arrResult['host'] = $arrOutput[0];

        //- Obtengo el Default Gateway
        exec("/sbin/route -n", $arrOutput);
        if(is_array($arrOutput)) {
            foreach($arrOutput as $linea) {
                if(preg_match("/^0.0.0.0[[:space:]]+(([[:digit:]]{1,3})\.([[:digit:]]{1,3})\.([[:digit:]]{1,3})\.([[:digit:]]{1,3}))/", $linea, $arrReg)) {
                    $arrResult['gateway'] = $arrReg[1];
                }
            }
        }
        return $arrResult;
    }

    /**
     * Procedimiento para escribir la configuracin de red del sistema en los 
     * archivos de configuración, a partir del arreglo indicado en el parámetro.
     * El arreglo indicado en el parámetro debe de tener los siguientes
     * elementos:
     *      $arreglo["host"]        Nombre simbolico del sistema
     *      $arreglo["dns_ip_1"]    DNS primario de la maquina
     *      $arreglo["dns_ip_2"]    DNS secundario de la maquina
     *      $arreglo["gateway_ip"]  IP del gateway asociado a la interfaz externa
     *  La función devuelve VERDADERO en caso de éxito, FALSO en caso de error.
     * 
     * @param   mixed   $config_red Nueva configuración deseada de la red
     * 
     * @return  bool    VERDADERO en éxito, FALSO en error
     */
    function escribir_configuracion_red_sistema($config_red)
    {
        $this->errMsg = '';
    	$sComando = '/usr/bin/elastix-helper netconfig --genconf'.
            ' --host '.escapeshellarg($config_red['host']).
            ' --gateway '.escapeshellarg($config_red['gateway_ip']).
            ' --dns1 '.escapeshellarg($config_red['dns_ip_1']).
            ($config_red['dns_ip_2'] == '' ? '' : ' --dns2 '.escapeshellarg($config_red['dns_ip_2'])).
            ' 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
        	return FALSE;
        }
        return TRUE;
    }

    /**
     * Procedimiento para escribir la configuración de red de una interfaz
     * Ethernet específica.
     * 
     * @param   string  $dev    Dispositivo de red a modificar: eth0
     * @param   string  $tipo   Una de las cadenas: static dhcp
     * @param   string  $ip     (opcional)  IP a asignar en caso static
     * @param   string  $mask   (opcional)  Máscara a asignar en caso static
     * 
     * @return  bool    VERDADERO en éxito, FALSO en error
     */
    function escribirConfiguracionInterfaseRed($dev, $tipo, $ip="", $mask="")
    {
        $this->errMsg = '';
        $sComando = '/usr/bin/elastix-helper netconfig --ifconf'.
            ' --device '.escapeshellarg($dev).
            ' --bootproto '.escapeshellarg($tipo).
            (($ip == '') ? '' : ' --ipaddr '.escapeshellarg($ip)).
            (($mask == '') ? '' : ' --netmask '.escapeshellarg($mask)).
            ' 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Compute IPv4 network address given IPv4 host address and bits in netmask. 
     * Function that returns the network address of the given ip for the given mask 
     *
     * @param string     $ip         IPv4 host address in dotted-quad format
     * @param string     $mask       Number of bits in network mask    
     *
     * @return string    Computed IPv4 network address
     */ 
    static function getNetAdress($ip, $mask)
    {
        $octetos_ip = explode('.', $ip);
        $octetos_net = array(0, 0, 0, 0);
        if ($mask <= 0 || $mask > 32) return NULL;
        for ($k = 0; $k < 4 && $mask; $k++) {
        	$octetmask = ($mask >= 8) ? 8 : $mask;
            $mask -= $octetmask;
            $octetos_net[$k] = (int)$octetos_ip[$k] & ((0xFF << (8 - $octetmask)) & 0xFF);
        }        
        return implode('.', $octetos_net);
    }

    /**
     * Count the number of bits set in a network mask and returns the count:
     * 255.255.128.0 => 17 
     * This assumes the network mask is well formed.
     * 
     * @param string    $mask   IP mask in dotted-quad format
     * 
     * @return int      Number of bits set in the mask
     */
    static function maskToDecimalFormat($mask)
    {
        $mask = explode(".", $mask);
        $decimal = 0;
        foreach($mask as $octeto) {
            $octeto = (int)$octeto & 0xFF;
            while (($octeto & 0x80) != 0) {
                $octeto = ($octeto << 1) & 0xFF;
            	$decimal++;
            }            
        }
        return $decimal;
    }
}
?>
