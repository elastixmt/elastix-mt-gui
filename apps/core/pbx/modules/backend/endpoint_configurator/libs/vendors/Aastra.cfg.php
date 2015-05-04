<?php
/*
    PrincipalFileAastra nos retorna el contenido del archivo de configuracion de los EndPoint
    Aastra, para ello es necesario enviarle el DisplayName, id_device, secret, ipAdressServer
*/
function PrincipalFileAastra($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{
    $content="#aastra default config file
time server disabled: 0
time server1: $ipAdressServer

sip proxy ip: $ipAdressServer
sip proxy port: 5060
sip registrar ip: $ipAdressServer
sip registrar port: 5060

sip digit timeout: 6

xml application post list: $ipAdressServer

softkey1 type: speeddial
softkey1 label: \"Voice Mail\"
softkey1 value: *97

softkey2 type: speeddial
softkey2 label: \"DND On\"
softkey2 value: *78

softkey3 type: speeddial
softkey3 label: \"DND Off\"
softkey3 value: *79

sip line1 screen name: $DisplayName
sip line1 screen name 2: $id_device
sip line1 display name: $id_device
sip line1 auth name: $id_device
sip line1 user name: $id_device
sip line1 password: $secret
sip line1 vmail: *97
sip line1 mode: 0 ";

    return $content;
}

function templatesFileAastra($ipAdressServer)
{
    $content= <<<TEMP
#aastra default config file
time server disabled: 0
time server1: $ipAdressServer

sip proxy ip: $ipAdressServer
sip proxy port: 5060
sip registrar ip: $ipAdressServer
sip registrar port: 5060

sip digit timeout: 6

xml application post list: $ipAdressServer

softkey1 type: speeddial
softkey1 label: "Voice Mail"
softkey1 value: *97

softkey2 type: speeddial
softkey2 label: "DND On"
softkey2 value: *78

softkey3 type: speeddial
softkey3 label: "DND Off"
softkey3 value: *79
TEMP;

    return $content;
}
?>