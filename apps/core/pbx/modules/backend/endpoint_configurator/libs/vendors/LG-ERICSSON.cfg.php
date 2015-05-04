<?php

function PrincipalFileLG_IP8802A($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{
    $content = "
[VOIP]
;VoIP configurations
line1_proxy_address $ipAdressServer ;Specifies the IP address or hostname of the Proxy Server for line
line1_proxy_port 5060 ;Specifies the port number of the proxy server for line: 1024 ~ 32000(default:5060)
line1_displayname $DisplayName ;Specifies the display name for line : max. 50 length string
line1_name $id_device ;Specifies the name for line : max. 50 length string
line1_authname $id_device ;Specifies the authentication name for line : max. 50 length string
line1_password $secret ;Specifies the authentication password for line : max. 50 length string
line1_type private ;Specifies the type for line : private/shared/dss/service
line1_extension ;Specifies the extension number of the line for DSS: max. 50 length string
line1_registration enable ;Specifies the registration for line : enable/disable
";
    return $content;
}

function templatesFileLG_Ericsson($ipAdressServer)
{
    $content=<<<TEMP
;=========================================================================
;IP88XX configuration information(v1.0)
;copyright LG-Ericsson since 2010
;=========================================================================
;Description for configuration file
;
;configuration file has following format
;[SECTION-NAME]
;namevalue;comments
;
;SECTION-NAME is one of VOIP, DSP,....
;name is one of dns1_address, domain_name, tftp_server_address ....
;blank is needed between value and ';'(comments)
;comments shall be started ';'
;
;=========================================================================

[VOIP]
;VoIP configurations
line1_proxy_address $ipAdressServer ;Specifies the IP address or hostname of the Proxy Server for line
line1_proxy_port 5060 ;Specifies the port number of the proxy server for line: 1024 ~ 32000(default:5060)
line1_displayname ;Specifies the display name for line : max. 50 length string
line1_name ;Specifies the name for line : max. 50 length string
line1_authname ;Specifies the authentication name for line : max. 50 length string
line1_password ;Specifies the authentication password for line : max. 50 length string
line1_type private ;Specifies the type for line : private/shared/dss/service
line1_extension ;Specifies the extension number of the line for DSS: max. 50 length string
line1_registration enable ;Specifies the registration for line : enable/disable

preferred_codec pcma pcmu g729 ;Specifies the preferred codec : g729/pcmu/pcma
rtp_port   23000 ;Specifies the start rtp port number : 16300 ~ 32700(default:23000)
local_udp_port 5060 ;Specifies the local udp port number : 1024 ~ 32000(default:5060)
timer_register_expires 3600 ;Specifies the registration-expiration timer : 0 ~ 86400 sec(default:3600)
timer_subscribe_expires 3600 ;Specifies the subscription-expiration timer : 0 ~ 86400 sec(default:3600)
timer_t1   250 ;Specifies the T1 timer value : 100 ~ 640 msec(default:500)
timer_t2   2000 ;Specifies the T2 timer value : 641 ~ 6400 msec(default:4000)

centralized_conference disable
supported_itsp IPPBX
ippbx_server_type ASTERISK
options_timer 0
de_register disable

[SYSTEM]
function remote_phonebook enable
function predialing disable
function sharp_convert disable
function conf_all_call_rel enable
function dtmf_info_convert enable
function tcp enable

[DIAL]
end_of_digit none          ;End of Digit : none/*/#
vsc    direct_call_pickup ** 

[CALL]
anonymous_call_blk enable
TEMP;
    return $content;
}

?>