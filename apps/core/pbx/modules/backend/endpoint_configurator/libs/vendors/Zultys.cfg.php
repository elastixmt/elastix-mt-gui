<?php
/*
    CommonFileZultys nos retorna el archivo de comun para la configuracion de los
    EndPoint Zultys
*/
function CommonFileZultys($model, $ipAdressServer)
{
    $content="[NET_CONFIG]
use_dhcp=yes
ntp_server_addr=$ipAdressServer
;sntp_server_addr=$ipAdressServer
tftp_server_addr=$ipAdressServer
tftp_cfg_dir=./$model

[SIP_CONFIG]
phone_sip_port=5060
rtp_start_port=33000
register_w_proxy=yes
proxy_addr=$ipAdressServer
proxy_port=5060
voice_mail_uri=*98
registration_expires=3600
session_expires=3600
[GENERAL_INFO]
language=es_ES.iso88591

[VLAN_CONFIG]
mode=0
;vlan_id_a=10
;circuits_a=UET
;vlan_id_b=30
;circuits_b=EUT
;cos_setting=5";

    return $content;
}

/*
    PrincipalFileZultys nos retorna la configuración específica para un teléfono ZIP2xX
    EndPoint Zultys
*/
function PrincipalFileZultys($DisplayName, $id_device, $secret, $arrParameters)
{
     $content="[SIP_CONFIG]
device_id=$id_device
display_name=$DisplayName
auth_password=$secret

[GENERAL_INFO]
greeting_message=$DisplayName";

    return $content;
}

/*
    templatesFileZultys es un archivo ejemplo con todos los parametros
    que se puedan utilizar para configuración
*/
function templatesFileZultys($model, $ipAdressServer)
{
    $content="[HW_CONFIG]
lcd_contrast=8
ring_volume=5
speaker_volume=5
handset_volume=5

[NET_CONFIG]
use_dhcp=yes
;ip_addr=
;subnet_mask=
;default_gateway=
;primary_dns=
;secondary_dns=
domain=zultys.com
sntp_server_addr=$ipAdressServer
tftp_server_addr=$ipAdressServer
tftp_cfg_dir=./$model
;dscp_setting=0

[SIP_CONFIG]
phone_sip_port=5060
rtp_start_port=33000
register_w_proxy=yes
proxy_addr=$ipAdressServer
proxy_port=5060
voice_mail_uri=*98
registration_expires=3600
session_expires=3600

[AUDIO_INFO]
ext_ring_tone=0
ext_cust_ring=
int_ring_tone=0
int_cust_ring=
ring_tone2=0
cust_ring2=
key_click=0
codec=0
distinctive_ring=yes
sound_url=
ext_call_answer=0

[GENERAL_INFO]
software_version=1.0.0
password=123456
time_fmt=%H:%M
date_fmt=%m/%-d/%Y
date_time_order=0
timezone=-420
country=USA
language=es_ES.iso88591
clear_settings=2

[VLAN_CONFIG]
mode=0
vlan_id_a=10
circuits_a=UET
vlan_id_b=30
circuits_b=EUT
cos_setting=5";

    return $content;
}
?>