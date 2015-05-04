<?php

function getPattonConfiguration($arrData,$tone_set)
{
    if($arrData["router_present"] == "yes")
	$pbx_side = strtoupper($arrData["pbx_side"]);
    else
	$pbx_side = "LAN";
    $config = <<<CONF
webserver port 80 language en

CONF;
    if(isset($arrData["sntp_address"]) && $arrData["sntp_address"] != ""){
	$config .= <<<CONF
sntp-client server $arrData[sntp_address]

CONF;
    }
    if($arrData["dns_address"] != ""){
	$config .= <<<CONF
dns-client server $arrData[dns_address]

CONF;
    }
    $config .= <<<CONF

system
ic voice 0
low-bitrate-codec g729
profile ppp default


$tone_set[tone_set]


profile tone-set default
profile voip default
codec 1 g711alaw64k rx-length 20 tx-length 20
codec 2 g711ulaw64k rx-length 20 tx-length 20
fax transmission 1 relay t38-udp
fax transmission 2 bypass g711alaw64k
profile pstn default
profile sip default
profile aaa default
method 1 local
method 2 none

context ip router
interface IF_IP_LAN

CONF;
    if($arrData["lan_type"] == "dhcp"){
	$config .= <<<CONF
ipaddress dhcp

CONF;
    }
    else{
	$config .= <<<CONF
  ipaddress $arrData[lan_ip_address] $arrData[lan_ip_mask]

CONF;
    }
    $config .= <<<CONF
tcp adjust-mss rx mtu
tcp adjust-mss tx mtu

interface IF_IP_WAN

CONF;
    if($arrData["router_present"] == "yes"){
	if($arrData["wan_type"] == "dhcp"){
	    $config .= <<<CONF
  ipaddress dhcp

CONF;
	}
	else{
	    $config .= <<<CONF
  ipaddress $arrData[wan_ip_address] $arrData[wan_ip_mask]

CONF;
	}
    }
    $config .= <<<CONF
tcp adjust-mss rx mtu
tcp adjust-mss tx mtu

context ip router
  route 0.0.0.0 0.0.0.0  $arrData[default_gateway]
context cs switch

CONF;
    if($arrData["timeout"] == ""){
	$config .= <<<CONF
  no digit-collection timeout

CONF;
    }
    else{
	$config .= <<<CONF
  digit-collection timeout $arrData[timeout]

CONF;
    }
    $interface_fxs = "";
    $authentication_extensions = "";
    $location_service = "";
    $port_fxs = "";
    if($arrData["analog_extension_lines"] != 0){
	$config .= <<<CONF

routing-table called-e164 RT_DIGITCOLLECTION
  route .T dest-interface IF_SIP_PBX MT_EXT_TO_NAME

routing-table called-e164 RT_TO_FXS

CONF;
	$mapping_table = <<<CONF

mapping-table calling-e164 to calling-name MT_EXT_TO_NAME

CONF;
	$interface_fxs = <<<CONF


interface sip IF_SIP_PBX
    bind context sip-gateway GW_SIP_ALL_EXTENSIONS
    route call dest-table RT_TO_FXS
	  remote $arrData[pbx_address] $arrData[sip_port]



CONF;
	$authentication_extensions = <<<CONF

authentication-service AS_ALL_EXTENSIONS


CONF;
	$location_service = <<<CONF

location-service LS_ALL_EXTENSIONS
  domain 1 $arrData[pbx_address] $arrData[sip_port]
  identity-group default

CONF;
	if($arrData["callerID_presentation"] == "pre-ring")
	    $presentation = "caller-id-presentation pre-ring";
	elseif($arrData["callerID_presentation"] == "mid-ring")
	    $presentation = "caller-id-presentation mid-ring";
	else
	    $presentation = "";
	for($i = 0;$i < $arrData["analog_extension_lines"];$i++){
	    $extension = $arrData["first_extension"] + $i*$arrData["increment"];
	    $number = $i+1;
	    $config .= <<<CONF
    route $extension dest-interface IF_FXS_$number

CONF;
	    $user_name = $arrData["user_name$i"];
	    $mapping_table .= <<<CONF
    map $extension to "$user_name"

CONF;
	    $interface_fxs .= <<<CONF
interface fxs IF_FXS_$number
    $presentation
	  route call dest-table RT_DIGITCOLLECTION
	  message-waiting-indication stutter-dial-tone
	  message-waiting-indication frequency-shift-keying
	  call-transfer
	  subscriber-number $extension

CONF;
	    $user = $arrData["user$i"];
	    $password = $arrData["authentication_user$i"];
	    $authentication_extensions .= <<<CONF
  username $user password $password

CONF;
	    $location_service .= <<<CONF
  identity $extension
      authentication outbound
	  authenticate 1 authentication-service AS_ALL_EXTENSIONS username $user
      registration outbound
	  registrar $arrData[pbx_address] $arrData[sip_port]
	  lifetime 300
	  register auto
	  retry-timeout on-system-error 10
	  retry-timeout on-client-error 10
	  retry-timeout on-server-error 10

CONF;
	    $port_fxs .= <<<CONF

port fxs 0 $i
    caller-id format $arrData[callerID_format]
      use profile fxs $tone_set[fxo_fxs_profile]
  encapsulation cc-fxs
    bind interface IF_FXS_1 switch 
  no shutdown
CONF;
	}
	$config .= $mapping_table;
    }
    $authentication_trunks = "";
    $context_sip_gateway = "";
    $port_fxo = "";
    if($arrData["analog_trunk_lines"] != 0){
	$interface_fxo = "";
	$authentication_trunks = <<<CONF

authentication-service AS_ALL_LINES



CONF;
	if($arrData["delivery_announcements"] == "yes")
	    $early_connect = "early-connect";
	else
	    $early_connect = "no early-connect";
	if($arrData["wait_callerID"] == "yes")
	    $ring_number = "ring-number on-caller-id";
	else
	    $ring_number = "ring-number 1";
	for($i = 0;$i < $arrData["analog_trunk_lines"];$i++){
	    $number = $i+1;
	    $line = $arrData["line$i"];
	    $config .= <<<CONF

interface sip IF_SIP_$number
    bind context sip-gateway GW_SIP_$number
    $early_connect 
    early-disconnect
    route call dest-interface IF_FXO_$number
    remote $arrData[pbx_address] $arrData[sip_port]
    address-translation outgoing-call request-uri user-part fix $line host-part to-header target-param none
    address-translation incoming-call called-e164 request-uri

CONF;
	    $interface_fxo .= <<<CONF

interface fxo IF_FXO_$number
      route call dest-interface IF_SIP_$number
      loop-break-duration min 200 max 1000
      $ring_number 
      mute-dialing
	disconnect-signal loop-break
      disconnect-signal busy-tone
      dial-after timeout 1
CONF;
	    $id_trunk = $arrData["ID$i"];
	    $password_trunk = $arrData["authentication_ID$i"];
	    $authentication_trunks .= <<<CONF
  username $id_trunk password $password_trunk

CONF;
	    $port = $arrData["lines_sip_port"] + $i*2;
	    $context_sip_gateway .= <<<CONF

context sip-gateway GW_SIP_$number
interface $pbx_side
bind interface IF_IP_$pbx_side context router port $port
context sip-gateway GW_SIP_$number
bind location-service LS_$number
no shutdown
CONF;
	    $port_fxo .= <<<CONF


port fxo 0 $i
  use profile fxo $tone_set[fxo_fxs_profile]
  encapsulation cc-fxo
  bind interface IF_FXO_$number switch
  no shutdown
CONF;
	}
	$config .= $interface_fxo;
    }
    $config .= $interface_fxs;
    $config .= <<<CONF

context cs switch
no shutdown

CONF;
    $config .= $authentication_extensions;
    $config .= $authentication_trunks;
    $config .= $location_service;
    $config .= $context_sip_gateway;
    $config .= $port_fxo;
    $config .= <<<CONF


context sip-gateway GW_SIP_ALL_EXTENSIONS
  interface $pbx_side
    bind interface IF_IP_$pbx_side context router port $arrData[extensions_sip_port]

context sip-gateway GW_SIP_ALL_EXTENSIONS
    bind location-service LS_ALL_EXTENSIONS
    no shutdown

port ethernet 0 0
medium auto
encapsulation ip
bind interface IF_IP_LAN router
no shutdown

CONF;
    if($arrData["router_present"] == "yes"){
	$config .= <<<CONF

port ethernet 0 1
medium auto
encapsulation ip
bind interface IF_IP_WAN router
no shutdown

CONF;
    }
    $config .= $port_fxs;
    return $config;  
}

function getPattonCommands($arrData,$ipAdressServer)
{
    $arrComands = array(
	$arrData["telnet_username"] => "",
	$arrData["telnet_password"] => "",
	"enable"	 	    => "",
	"copy"			    => "tftp://$ipAdressServer/$arrData[mac]_Patton.cfg startup-config",
	"reload"		    => "forced"
    );
    return $arrComands;
}

?>
