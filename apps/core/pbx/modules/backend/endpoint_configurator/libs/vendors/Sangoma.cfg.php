<?php

function getSangomaConfiguration($arrData, $tone_set)
{

    $date =  date("j/m/Y H:i:s"); 


    if($arrData["router_present"] == "yes")
     	$pbx_side = strtoupper($arrData["pbx_side"]);
    else
    	$pbx_side = "LAN";
    
    $config .= <<<CONF
        ;writing file ...
	;PUT completed
	;
	; Script generated using
	; PUT MEM:0x95bf3878;length=0x00026666,buffer=0x95bf3878
 	;<all>
	; CONFIGVERSION:this_hostname:$date 
	;
        set .tftp.ip=$arrData[pbx_address]
        set .quick.voip.proxy.proxy_addr=$arrData[pbx_address]
        set .quick.voip.proxy.proxy_domain_name=$arrData[pbx_address]
        set .quick.voip.proxy.registrar_addr=$arrData[pbx_address]
        purge .pots.port

CONF;


if($arrData["dns_address"] != ""){
        $config .= <<<CONF
purge .dns
 cp .dns.1
  set .dns.1.ip=$arrData[dns_address]

CONF;
    }
else{
 $config .= <<<CONF
purge .dns
 cp .dns.1
  set .dns.1.ip=0.0.0.0

CONF;
}


if($arrData["lan_type"] == "dhcp"){
        $config .= <<<CONF
        set .lan.bridge_mode="1"
        set .lan.file_transfer_method="TFTP"
        set .lan.lan_profile="1"
        set .lan.name="this_hostname"
        set .lan.gateway.dhcp_if="1"

CONF;
    }
    else{
        $config .= <<<CONF
        set .lan.bridge_mode="1"
        set .lan.file_transfer_method="TFTP"
        set .lan.lan_profile="1"
        set .lan.name="this_hostname"
        set .lan.gateway.dhcp_if="0"
        purge .lan.if
        cp .lan.if.1
        set .lan.if.1.ip=$arrData[lan_ip_address]
        set .lan.if.1.max_tx_rate="0"
        set .lan.if.1.protocol="ip"
        set .lan.if.1.subnet=$arrData[lan_ip_mask]
        set .lan.if.1.use_apipa="1"
        set .lan.if.1.use_dhcp="0"
            set .lan.if.1.dhcp.get_dns="0"
            set .lan.if.1.dhcp.get_gateway="0"
            set .lan.if.1.dhcp.get_ntp="0"
            set .lan.if.1.dhcp.get_tftp="0"
            set .lan.if.1.nat.enable="0"
            set .lan.if.1.nat.private_subnet_auto="1"
            set .lan.if.1.nat.private_subnet_list_index="1"
CONF;
}

 $registration_ID = $arrData["registration"];
 $registration_password = $arrData["registration_password"];
 if (($registration_ID!="")&& ($registration_password=!""))
 {
 $config .= <<<CONF

  set .sip.local_rx_port=$arrData[sip_port]
  set .sip.reg_enable="1"
  purge .sip.profile
  purge .sip.reg.user
  purge .pots.port
  purge .quick.fxo
   purge .sip.auth.user
   cp .sip.auth.user.1
    set .sip.auth.user.1.enable="1"
    set .sip.auth.user.1.password=$registration_password
    set .sip.auth.user.1.resource_priority="routine"
    set .sip.auth.user.1.sip_profile="1"
    set .sip.auth.user.1.subscriber="IF:[^9]..."
    set .sip.auth.user.1.username=$registration_ID
   cp .sip.profile.1
   set .sip.profile.1.alt_domain="alt-reg-domain.com"
   set .sip.profile.1.from_header_host="reg_domain"
   set .sip.profile.1.from_header_userinfo="auth_username"
   set .sip.profile.1.interface=$registration_ID
   set .sip.profile.1.name=$registration_ID
   set .sip.profile.1.redirect_host="reg_domain"
   set .sip.profile.1.reg_domain=$arrData[pbx_address]
   set .sip.profile.1.reg_expiry="600"
   set .sip.profile.1.reg_req_uri_port=$arrData[sip_port]
   set .sip.profile.1.req_uri_port=$arrData[sip_port]
   set .sip.profile.1.sig_transport="udp"
   set .sip.profile.1.to_header_host="reg_domain"
    set .sip.profile.1.proxy.accessibility_check="off"
    set .sip.profile.1.proxy.accessibility_check_transport="udp"
    set .sip.profile.1.proxy.min_valid_response="180"
    set .sip.profile.1.proxy.mode="normal"
    set .sip.profile.1.proxy.retry_delay="0"
    set .sip.profile.1.proxy.timeout_ms="5000"
    purge .sip.profile.1.proxy
    cp .sip.profile.1.proxy.1
     set .sip.profile.1.proxy.1.enable="1"
     set .sip.profile.1.proxy.1.ipname=$arrData[pbx_address]
     set .sip.profile.1.proxy.1.port=$arrData[sip_port]
     set .sip.profile.1.proxy.1.tls_port="5061"
    set .sip.profile.1.registrar.accessibility_check="off"
    set .sip.profile.1.registrar.accessibility_check_transport="udp"
    set .sip.profile.1.registrar.max_registrars="3"
    set .sip.profile.1.registrar.min_valid_response="200"
    set .sip.profile.1.registrar.mode="normal"
    set .sip.profile.1.registrar.retry_delay="0"
    set .sip.profile.1.registrar.timeout_ms="5000"
    purge .sip.profile.1.registrar
    cp .sip.profile.1.registrar.1
     set .sip.profile.1.registrar.1.enable="1"
     set .sip.profile.1.registrar.1.ipname=$arrData[pbx_address]
     set .sip.profile.1.registrar.1.port=$arrData[sip_port]
     set .sip.profile.1.registrar.1.tls_port="5061"
   purge .sip.reg.user
   cp .sip.reg.user.1
    set .sip.reg.user.1.auth_user_index="1"
    set .sip.reg.user.1.dn=$registration_ID
    set .sip.reg.user.1.enable="1"
    set .sip.reg.user.1.sip_profile="1"
    set .sip.reg.user.1.username=$registration_ID



CONF;
}

$number=0;
    for($i = 0;$i < $arrData["analog_extension_lines"];$i++){
            $extension = $arrData["first_extension"] + $i*$arrData["increment"];
            $username = $arrData["authentication_user$i"];
            $dn = $arrData["user$i"]; 
            $interface = $arrData["user_name$i"];
            $number = $i+1;
            $call_conference = $arrData["call_conference$i"]; 
            $call_transfer = $arrData["call_transfer$i"];
            $caller_id = $arrData["caller_id$i"];
            $call_waiting = $arrData["call_waiting$i"];
            $enable = $arrData["enable$i"];
            $call_dnd = $arrData["call_dnd$i"];
            $config .= <<<CONF

	    cp .pots.port.$number
   	     set .pots.port.$number.call_conference=$call_conference
   	     set .pots.port.$number.call_fwd_enable=on
   	     set .pots.port.$number.call_transfer=$call_transfer 
   	     set .pots.port.$number.call_waiting=$call_waiting
   	     set .pots.port.$number.callerid=$caller_id
   	     set .pots.port.$number.dnd_enable=$call_dnd
   	     set .pots.port.$number.dnd_off_hook_deactivate="off"
   	     set .pots.port.$number.dnd_response="instant_reject"
   	     set .pots.port.$number.enable=$enable
   	     set .pots.port.$number.fx_profile="1"
   	     set .pots.port.$number.lyr1="g711Alaw64k"
   	     set .pots.port.$number.tx_gain="0"
    	      purge .pots.port.$number.if
    	       cp .pots.port.1.if.1
     		set .pots.port.$number.if.1.dn=$dn
     		set .pots.port.$number.if.1.interface=$interface
     		set .pots.port.$number.if.1.profile="1"
     		set .pots.port.$number.if.1.ring_index="1"
     		set .pots.port.$number.if.1.username=$username

CONF;
	    $user = $arrData["user$i"];
            $password = $arrData["authentication_user$i"];

	    $config .= <<<CONF

CONF;
}

$number_prof = $number;



for($i=0; $i < $arrData["analog_trunk_lines"];$i++){
            $number = $i+1;
            $number_prof++;
            $line = $arrData["line$i"];
            $dn = $arrData["ID$i"];
            $username = $arrData["authentication_ID$i"];
            $numlist = $arrData["num_list$i"];
            $enable_line = $arrData["enable_line$i"];
            $config .= <<<CONF
            
	    cp .quick.fxo.$number_prof
   		set .quick.fxo.$number_prof.handle_emergency_calls="0"
   		set .quick.fxo.$number_prof.incoming_forward="default"
   		set .quick.fxo.$number_prof.name=$username
   		set .quick.fxo.$number_prof.numlist=$numlist
   		set .quick.fxo.$number_prof.this_tel=$dn
	    cp .pots.port.$number_prof
  		 set .pots.port.$number_prof.call_conference="off"
  		 set .pots.port.$number_prof.call_fwd_enable="on"
 		 set .pots.port.$number_prof.call_transfer="on"
  		 set .pots.port.$number_prof.call_waiting="off"
  		 set .pots.port.$number_prof.callerid="off"
  		 set .pots.port.$number_prof.dnd_enable="on"
  		 set .pots.port.$number_prof.dnd_off_hook_deactivate="off"
  		 set .pots.port.$number_prof.dnd_response="instant_reject"
  		 set .pots.port.$number_prof.enable=$enable_line
   		 set .pots.port.$number_prof.fx_profile="2"
  		 set .pots.port.$number_prof.lyr1="g711Alaw64k"
  		 set .pots.port.$number_prof.tx_gain="0"
  	    purge .pots.port.$number_prof.if
   		 cp .pots.port.$number_prof.if.1
     		 set .pots.port.$number_prof.if.1.dn=$dn
    	         set .pots.port.$number_prof.if.1.interface=$line
    		 set .pots.port.$number_prof.if.1.profile="2"
    		 set .pots.port.$number_prof.if.1.ring_index="1"
    		 set .pots.port.$number_prof.if.1.username= $username

CONF;

 /*
     $id_trunk = $arrData["ID$i"];
            $password_trunk = $arrData["authentication_ID$i"];
            $line = $arrData["line$i"];


    
    $config .= <<<CONF
            cp .sip.auth.user.$number
                set .sip.auth.user.$number.enable="1"
                set .sip.auth.user.$number.password=$password_trunk
                set .sip.auth.user.$number.resource_priority="routine"
                set .sip.auth.user.$number.sip_profile=$number
                set .sip.auth.user.$number.username=$id_trunk

            cp .sip.reg.user.$number
                set .sip.reg.user.$number.auth_user_index=$number
                set .sip.reg.user.$number.dn=$line
                set .sip.reg.user.$number.enable="1"
                set .sip.reg.user.$number.sip_profile=$number
                set .sip.reg.user.$number.username=$id_trunk

CONF;
--------------------


    cp .sip.profile.$number
                set .sip.profile.$number.interface= $line
                set .sip.profile.$number.name= $line
                set .sip.profile.$number.reg_domain=$arrData[pbx_address]

             purge .sip.profile.$number.proxy
                cp .sip.profile.$number.proxy.1
                 set .sip.profile.$number.proxy.1.enable="1"
                 set .sip.profile.$number.proxy.1.ipname=$arrData[pbx_address]
                 set .sip.profile.$number.proxy.1.port="5060"
                 set .sip.profile.$number.proxy.1.tls_port="5061"

             purge .sip.profile.$number.registrar
                cp .sip.profile.$number.registrar.1
                set .sip.profile.$number.registrar.1.enable="1"
                set .sip.profile.$number.registrar.1.ipname=$arrData[pbx_address]
                set .sip.profile.$number.registrar.1.port="5060"
                set .sip.profile.$number.registrar.1.tls_port="5061"
----------------------------------------------------
*/

    }

$config.=<<<CONF

cp .
;
; PUT end
;
CONF;
    return $config;  
}

function getSangomaFile($arrData, $tone_set)
{
  $date =  date("j/m/Y H:i:s");

  $config .= <<<CONF
  ;PUT completed
  ;
  ; Script generated using
  ; PUT MEM:0x95bf3878;length=0x00026666,buffer=0x95bf3878
  ;<all>
  ; CONFIGVERSION:this_hostname:$date
  ;

  [lan] 
  file_transfer_method=tftp 
 
  [ftp] 
  ip = $arrData[pbx_address]

CONF;
 return $config;
}

function getSangomaCommands($arrData,$ipAdressServer)
{
    $arrComands = array(
       $arrData["telnet_username"] => "",
       $arrData["telnet_password"] => "",
       "get tftp:config.txt" => ""
 
    );
    return $arrComands;
}

?>
