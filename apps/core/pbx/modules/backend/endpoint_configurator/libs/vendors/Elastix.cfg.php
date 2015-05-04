<?php

function templatesFileElastix($ipAdressServer)
{
    $content= <<<TEMP
# SIP Server
P47 = $ipAdressServer

# Outbound Proxy
P48 = $ipAdressServer

# SIP User ID
P35 = 8000

# Authenticate ID
P36 = 8000

# Authenticate password
P34 = 0000

# Display Name (John Doe)
P3 = 

# DHCP support. 0 - yes, 1 - no
P8 = 1
TEMP;
    return $content;
}

function PrincipalFileElastixLXP200($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer, $model){

    $configNetwork = "";
    $ipTftpServer = explode(".", $ipAdressServer);   
    $ByDHCP = existsValue($arrParameters,'By_DHCP',1);
    $ByDHCP = ($ByDHCP == 1)?0:1; // 0 indica que es por DHCP y 1 por estatico
    if($ByDHCP==1){
        
        
        $IP = existsValue($arrParameters,'IP',"0.0.0.0");
        $IP = explode(".", $IP);
        $Mask = existsValue($arrParameters,'Mask',"0.0.0.0");
        $Mask = explode(".", $Mask);
        $GW = existsValue($arrParameters,'GW',"0.0.0.0");
        $GW = explode(".", $GW);
        $DNS1 = existsValue($arrParameters,'DNS1',"0.0.0.0");
        $DNS1 = explode(".", $DNS1);
        $DNS2 = existsValue($arrParameters,'DNS2',"0.0.0.0");
        $DNS2 = explode(".", $DNS2);
    
        $configNetwork ="
        
        # IP Address
        P9=$IP[0]
        P10=$IP[1]
        P11=$IP[2]
        P12=$IP[3]
        
        # Subnet Mask
        P13=$Mask[0]
        P14=$Mask[1]
        P15=$Mask[2]
        P16=$Mask[3]
        
        # Gateway
        P17=$GW[0]
        P18=$GW[1]
        P19=$GW[2]
        P20=$GW[3]
        
        # DNS Server 1
        P21=$DNS1[0]
        P22=$DNS1[1]
        P23=$DNS1[2]
        P24=$DNS1[3]
        
        # DNS Server 2
        P25=$DNS2[0]
        P26=$DNS2[1]
        P27=$DNS2[2]
        P28=$DNS2[3]";
   }


    $content="
    
    # Firmware Server Path
    P192 = $ipAdressServer
    
    # Config Server Path
    P237 = $ipAdressServer
    
    # Firmware Upgrade. 0 - TFTP Upgrade,  1 - HTTP Upgrade.
    P212 = 0
    
    # Account Name
    P270 = $DisplayName
    
    # SIP Server
    P47 = $ipAdressServer
    
    # Outbound Proxy
    P48 = $ipAdressServer
    
    # SIP User ID
    P35 = $id_device
    
    # Authenticate ID
    P36 = $id_device
    
    # Authenticate password
    P34 = $secret
    
    # Display Name (John Doe)
    P3 = $DisplayName
    
    # DHCP=0 o static=1
    P8=$ByDHCP
    
    # SIP Registration ( 0 = do not register, 1 = register )
    P31=1
    
    #Time Zone
    P64=".existsValue($arrParameters,'Time_Zone','auto')."
    
    # TFT Server
    P41=$ipTftpServer[0]
    P42=$ipTftpServer[1]
    P43=$ipTftpServer[2]
    P44=$ipTftpServer[3]
    
    $configNetwork";    
    
    return $content;
}



/**
 * Procedimiento para codificar la configuración en formato INI en el formato
 * binario que espera el teléfono Elastix. Este procedimiento reemplaza a
 * la llamada al programa externo GS_CFG_GEN/bin/encode.sh.
 * 
 * @param   string  $sMac MAC del teléfono Grandstream en formato aabbccddeeff
 * @param   string  $sTxtConfig Bloque de configuración en formato INI
 * 
 * @return  string  Bloque binario codificado listo para escribir al archivo
 */
function elastix_encode_config($sMAC, $sTxtConfig)
{
    $sBloqueConfig = '';

    // Validar y codificar la MAC del teléfono
    if (!preg_match('/^[[:xdigit:]]{12}$/', $sMAC)) return FALSE;

    // Parsear y codificar las variables de configuración
    $params = array();
    foreach (preg_split("/(\x0d|\x0a)+/", $sTxtConfig) as $s) {
        $s = trim($s);
        if (strpos($s, '#') === 0) continue;
        $regs = NULL;
        if (preg_match('/^(\w+)\s*=\s*(.*)$/', $s, $regs))
            $params[] = $regs[1].'='.rawurlencode($regs[2]);
    }
    $params[] = 'gnkey=0b82';
    $sPayload = implode('&', $params);
    if (strlen($sPayload) & 1) $sPayload .= "\x00";
    //if (strlen($sPayload) & 3) $sPayload .= "\x00\x00";
    
    // Calcular longitud del bloque en words, más el checksum
    $iLongitud = 8 + strlen($sPayload) / 2;
    $sPayload = pack('NxxH*', $iLongitud, $sMAC)."\x0d\x0a\x0d\x0a".$sPayload;
    $iChecksum = 0x10000 - (array_sum(unpack('n*', $sPayload)) & 0xFFFF);

    $sPayload[4] = chr(($iChecksum >> 8) & 0xFF);
    $sPayload[5] = chr(($iChecksum     ) & 0xFF);

    if ((array_sum(unpack("n*", $sPayload)) & 0xFFFF) != 0) 
        die('Invalid Checksum');
    return $sPayload;
}

function existsValue($arr, $key, $default)
{
    if(isset($arr[$key])){
        $value = trim($arr[$key]);
        if($value != "") return $value;
        else return $default;
    }
    else return $default;
}

?>
