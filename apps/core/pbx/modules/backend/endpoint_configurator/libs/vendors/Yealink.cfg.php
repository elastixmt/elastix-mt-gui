<?php
/*
    Genera la plantilla de configuración del terminal de Yealink
*/
function PrincipalFileYealink($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{
    $configNetwork = "";
    $ByDHCP = existsValue($arrParameters,'By_DHCP',1); 
    $ByDHCP = ($ByDHCP == 1)?0:2; // 0 indica que es por DHCP y 2 por estatico
    if($ByDHCP==2){
        $configNetwork ="
WANStaticIP       =".existsValue($arrParameters,'IP','')."
WANSubnetMask     =".existsValue($arrParameters,'Mask','')."
WANDefaultGateway =".existsValue($arrParameters,'GW','')."

[ DNS ]
path=/yealink/config/Network/Network.cfg
PrimaryDNS   = ".existsValue($arrParameters,'DNS1','')."
SecondaryDNS = ".existsValue($arrParameters,'DNS2','');
   }

# CONTENT
    $content="
[ autop_mode ]
path = /yealink/config/Setting/autop.cfg
mode = 1

[ WAN ]
path=/yealink/config/Network/Network.cfg
#WANType:0:DHCP,1:PPPoE,2:StaticIP
WANType = $ByDHCP
$configNetwork

[ LAN ]
path=/yealink/config/Network/Network.cfg
#LANTYPE:0:Router, 1:Bridge
LANTYPE = ".existsValue($arrParameters,'Bridge','1')."

[ account ]
path=/yealink/config/voip/sipAccount0.cfg
Enable = 1
Label = $DisplayName
DisplayName = $DisplayName
UserName = $id_device
AuthName = $id_device
password = $secret
SIPServerHost = $ipAdressServer
Expire = 60

[ autoprovision ]
path = /yealink/config/Setting/autop.cfg
server_type = tftp
server_address = $ipAdressServer
user =
password =

[ Time ] 
path = /yealink/config/Setting/Setting.cfg 
TimeZone = ".existsValue($arrParameters,'Time_Zone','-5')." 
TimeServer1 = $ipAdressServer
TimeServer2 = 0.pool.ntp.org 
Interval = 300 
SummerTime = 0

[ PhoneSetting ]
path = /yealink/config/Setting/Setting.cfg
Manual_Time = 0


[ Lang ]
path = /yealink/config/Setting/Setting.cfg
ActiveWebLanguage = Spanish


[ ContactList ]
path = /tmp/download.cfg
server_address = $ipAdressServer/contactData1.xml

[ RemotePhoneBook0 ]
path = /yealink/config/Setting/Setting.cfg
URL = tftp://$ipAdressServer/phonebook.xml
Name = Directory

[ memory11 ]
path = /yealink/config/vpPhone/vpPhone.ini
DKtype = 15
Line = 1
Value =  
type = 

[ memory12 ]
path = /yealink/config/vpPhone/vpPhone.ini
DKtype = 15 
Line = 1
Value =
type = 

[ memory13 ]
path = /yealink/config/vpPhone/vpPhone.ini
DKtype = 15
Line = 1
Value = 
type = 

[ memory14 ]
path = /yealink/config/vpPhone/vpPhone.ini
DKtype = 15
Line = 1
Value = 
type = 

[ memory15 ]
path = /yealink/config/vpPhone/vpPhone.ini
DKtype = 15
Line =
Value = 
type = 

[ memory16 ]
path = /yealink/config/vpPhone/vpPhone.ini
DKtype = 15
Line = 1
Value =
type =
";
    return $content;
}

function templatesFileYealink($ipAdressServer)
{
    $content= <<<TEMP
[ autop_mode ]
path = /yealink/config/Setting/autop.cfg
#disable:0; power on:1; repeatly:4; weekly:5
#schedule_min is the interval of time to update, the minimum value is 1
#schedule_time and schedule_time_end are the time for weekly update
#schedule_dayofweek is the setting for weekly choosen, Sunday:0; Monday:1;  Tuesday:2;...if you want to update every sunday and Saturday, you could set it to 06
mode = 1
schedule_min = 
schedule_time = 
schedule_time_end = 
schedule_dayofweek = 


[ autoprovision ]
path = /yealink/config/Setting/autop.cfg
server_type = tftp
server_address = $ipAdressServer
user = 
password = 


[ AES_KEY ]
path = /yealink/config/Setting/autop.cfg
aes_key_16 = 


[ ringtone ]
path = /tmp/download.cfg
#to specify a ringtone for update
#the format of the ringtone must be WAV
#an example for a right server_address:ftp://wxf:123456@192.168.0.132:21/Ring20.wav
server_address = 
 
[ Lang ]
path = /tmp/download.cfg
#to specify a language for update
#an example for a right  server_address:http://192.168.0.132:9/file_provision/lang+English.txt
server_address = 

[ ContactList ]
path = /tmp/download.cfg
#to specify a XML phonebook for update
#an example for a right  server_address:http://192.168.0.132:9/file_provision/contactData1.xml
#server_address = http://192.168.26.1/phonebook/xml/snom/phonebook.xml 


[ firmware ]
path = /tmp/download.cfg
#to specify a firmware for update
#server_type can be tftp,ftp or http
#if the server requires authentication,the login_name and login_pswd will be used.
server_type = tftp
server_ip = $ipAdressServer
server_port = 69
login_name = 
login_pswd = 
http_url   = 
firmware_name = 2.41.0.28-T28.rom

# Added by CTM

[ Time ]
path = /yealink/config/Setting/Setting.cfg   
TimeZone = 0
TimeServer1= $ipAdressServer
TimeServer2 = hora.roa.es
Interval = 1000
SummerTime = 1
StartTime = 10/4/2
EndTime = 4/4/3


[ account ]
path=/yealink/config/voip/sipAccount0.cfg
SIPServerHost = $ipAdressServer
SIPServerPort = 5060
SIPListenRandom = 0
SIPListenPort = 5060
Expire = 3600   
UseOutboundProxy = 0
OutboundHost = 
OutboundPort = 5060
EnableEncrypt = 0
EncryptKey = 29749
EncryptVersion = 1
BakOutboundHost = 
BakOutboundPort = 5060
EnableSTUN = 0
proxy-require =
ptime = 0
srtp_encryption = 0
srtp_encryption_algorithm = 0
BackupSIPServerHost = 
BackupSIPServerPort = 
Enable 100Rel = 0
precondition = 0
SubsribeRegister = 0
CIDSource = 0
EnableSessionTimer = 0
SessionExpires = 
SessionRefresher = 0
EnableUserEqualPhone = 0
BLFList_URI = 
SubsribeMWI = 0
AnonymousCall = 0
RejectAnonymousCall = 0

[ audio0 ]
path=/yealink/config/voip/sipAccount0.cfg
enable = 1
PayloadType = PCMU
priority = 1
rtpmap = 0

[ audio1 ]
path=/yealink/config/voip/sipAccount0.cfg
enable = 1
PayloadType = PCMA
priority = 2
rtpmap = 8

[ audio2 ]
path=/yealink/config/voip/sipAccount0.cfg
enable = 0
PayloadType = G723_53
priority = 0
rtpmap = 4

[ audio3 ]
path=/yealink/config/voip/sipAccount0.cfg
enable = 0
PayloadType = G723_63
priority = 0
rtpmap = 4

[ audio4 ]
path=/yealink/config/voip/sipAccount0.cfg
enable = 1
PayloadType = G729
priority = 3
rtpmap = 18

[ audio5 ]
path=/yealink/config/voip/sipAccount0.cfg
enable = 0
PayloadType = G722
priority = 0
rtpmap = 9

[ audio6 ]
path=/yealink/config/voip/sipAccount0.cfg
enable = 0
PayloadType = iLBC
priority = 3
rtpmap = 97

[ audio7 ]
path=/yealink/config/voip/sipAccount0.cfg
enable = 0
PayloadType = G726-16
priority = 0
rtpmap = 112

[ audio8 ]
path=/yealink/config/voip/sipAccount0.cfg
enable = 0
PayloadType = G726-24
priority = 0
rtpmap = 102

[ audio9 ]
path=/yealink/config/voip/sipAccount0.cfg
enable = 0
PayloadType = G726-32
priority = 0
rtpmap = 2

[ audio10 ]
path=/yealink/config/voip/sipAccount0.cfg
enable = 0
PayloadType = G726-40
priority = 0
rtpmap = 104

[ DTMF ]
path=/yealink/config/voip/sipAccount0.cfg
DTMFInbandTransfer = 1
DTMFPayload = 101
DTMFToneLen = 300
InbandDtmfVolume = 0
RxLatency = 20
CPTToneCountry = 12
G726CodeWord = 1
InfoType = 0

[ NAT ]
path=/yealink/config/voip/sipAccount0.cfg
MaxRTPPort = 11800
MinRTPPort = 11780
NATTraversal = 0
STUNServer = 217.10.79.21
STUNPort = 10000
EnableUDPUpdate = 1
UDPUpdateTime = 30
rport = 0

[ ADVANCED ]
path=/yealink/config/voip/sipAccount0.cfg
default_t1 = 0.5
default_t2 = 4
default_t4 = 5

[blf]
path=/yealink/config/voip/sipAccount0.cfg
SubscribePeriod = 3600

[ Forward ]
path=/yealink/config/Features/Forward.cfg
Type = 0
AlwaysForward = 
BusyForward = 
NoAnswerForward = 
AfterRingTimes = 10
Active = 0
BusyNoAnswerForward = 
BusyNoAfterRingTimes = 10

[ Message ]
path=/yealink/config/Features/Message.cfg
#Set voicemail number for each account
VoiceNumber0 = *97
VoiceNumber1 = 
VoiceNumber2 = 
VoiceNumber3 = 
VoiceNumber4 = 
VoiceNumber5 = 

[ Features ]
path=/yealink/config/Features/Phone.cfg
DND = 0
Call_Waiting = 1
EnableHotline = 0
Callpickup = 
Hotlinenumber = 

[ AutoAnswer ]
path=/yealink/config/Features/Phone.cfg
Enable = 0

[ PoundSend ]
path=/yealink/config/Features/Phone.cfg
#Set # key or * key as send. #:1 and *:2
Enable = 1

[ WAN ]
path=/yealink/config/Network/Network.cfg
#WANType:0:DHCP,1:PPPoE,2:StaticIP
WANType = 0
WANStaticIP = 
WANSubnetMask = 
WANDefaultGateway = 

[ DNS ]
path=/yealink/config/Network/Network.cfg
PrimaryDNS = 
SecondaryDNS = 

[ PPPoE ]
path=/yealink/config/Network/Network.cfg
PPPoEUser = 
PPPoEPWD = 

[ LAN ]
path=/yealink/config/Network/Network.cfg
#LANTYPE:0:Router, 1:Bridge
LANTYPE = 1
RouterIP = 10.0.0.1
LANSubnetMask = 255.255.255.0
EnableDHCP = 1
DHCPStartIP = 10.0.0.10
DHCPEndIP = 10.0.0.100

[ SYSLOG ]
path=/yealink/config/Network/Network.cfg
#specify the server for syslog storage
SyslogdIP = 

[ RTPPORT ]
path=/yealink/config/Network/Network.cfg
MaxRTPPort = 11800
MinRTPPort = 11780

[ QOS ]
path=/yealink/config/Network/Network.cfg
SIGNALTOS = 40
RTPTOS = 40

[ VLAN ]
path=/yealink/config/Network/Network.cfg
#ISVLAN,VID and USRPRIORITY are used for VLAN on LAN port
#PC_PORT_VLAN_ENABLE,PC_PORT_VID and PC_PORT_PRIORITY are used for PC port
ISVLAN = 0
VID = 
USRPRIORITY = 
PC_PORT_VLAN_ENABLE = 
PC_PORT_VID = 
PC_PORT_PRIORITY = 

[ snmp ]
path=/yealink/config/Network/Network.cfg
port = 0
[ Profile ]
path=/yealink/config/vpm.cfg
VAD = 0
CNG = 1
GPHS = 12
ECHO = 1

[ Jitter ]
path=/yealink/config/vpm.cfg
Adaptive = 1
Min = 0
Max = 300
Nominal = 120
trusted_address = 

[ SecurityRTP ]
path=/yealink/config/vpm.cfg
Enable = 0

[ Country ]
path=/yealink/config/voip/tone.ini
#The tones are defined by countries.If Country = Custom,the customized values wi
ll be used.
Country = Australia

[ Tone Param ]
path=/yealink/config/voip/tone.ini
dial = 
ring = 
busy = 
congestion = 
callwaiting = 
dialrecall = 
record = 
info = 
stutter = 
message = 
autoanswer = 

[ Default ]
path=/yealink/config/voip/tone.ini
dial = 1
ring = 1
busy = 1
congestion = 1
callwaiting = 1
dialrecall = 1
record = 1
info = 1
stutter = 1
message = 1
autoanswer = 1


[ PhoneSetting ]
path = /yealink/config/Setting/Setting.cfg
BacklightTime = 120
Manual_Time = 0
BackLight = 2
Contrast = 2
InterDigitTime = 4
FlashHookTimer = 300
Lock = 0
#the range of the volume is 1~15
Voicevolume = 4
Ringtype = Ring1.wav
HandFreeSpkVol = 8
HandFreeMicVol = 8
HandSetSpkVol = 8
HandSetMicVol = 8
HeadSetSpkVol = 8
HeadSetMicVol = 8 

[ Lang ]
path = /yealink/config/Setting/Setting.cfg
#ActiveWebLanguage is the setting of language on LCD.
#WebLanguage is the setting of language on web management
ActiveWebLanguage = 
WebLanguage = 

[ AlertInfo0 ]
path = /yealink/config/Setting/Setting.cfg
Text = 
Ringer = 

[ AlertInfo1 ]
path = /yealink/config/Setting/Setting.cfg
Text = 
Ringer = 

[ AlertInfo2 ]
path = /yealink/config/Setting/Setting.cfg
Text = 
Ringer = 

[ AlertInfo3 ]
path = /yealink/config/Setting/Setting.cfg
Text = 
Ringer = 

[ AlertInfo4 ]
path = /yealink/config/Setting/Setting.cfg
Text =
Ringer = 

[ AlertInfo5 ]
path = /yealink/config/Setting/Setting.cfg
Text = 
Ringer = 

[ AlertInfo6 ]
path = /yealink/config/Setting/Setting.cfg
Text = 
Ringer = 

[ AlertInfo7 ]
path = /yealink/config/Setting/Setting.cfg
Text = 
Ringer = 

[ AlertInfo8 ]
path = /yealink/config/Setting/Setting.cfg
Text = 
Ringer = 

[ AlertInfo9 ]
path = /yealink/config/Setting/Setting.cfg
Text = 
Ringer = 

[ BlockOut ]
path = /yealink/config/DialRule/BlockOut.cfg
#Set Block Out number.
1 = 
2 =
3 = 
4 = 
5 = 
6 = 
7 = 
8 = 
9 = 
10 = 

[ AreaCode ]
path = /yealink/config/DialRule/areacode.cfg
code = 
minlen = 
maxlen = 

[ memory1 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Line means the line taken in use.0 stands for auto,1 stands for line1...except 
for one condition when type is blf(DKtype:16),0 stands for line1,1 stands for li
ne2...
#DKtype defines the type of the key,ranging from 0 to 17
#DKtype 0:N/A           1:Conference    2:Forward       3:Transfer
#DKtype 4:Hold          5:DND           6:Redial        7:Call Return
#DKtype 8:SMS           9:Call Pickup   10:Call Park    11:Custom
#DKtype 12:Voicemail    13:SpeedDial    14:Intercom     15:Line(for line key onl
y)
#DKtype 16:BLF          17:URL
#Set Memory key1
Line = 0
type = blf
Value = 180
Callpickup = 
DKtype = 16
PickupValue = *8


[ memory2 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set Memory key2
Line = 0
type = blf
Value = 181
Callpickup = 
DKtype = 16 
PickupValue = *8

[ memory3 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set Memory key3
Line = 0
type = blf
Value = 182
Callpickup = 
DKtype = 16 
PickupValue = *8

[ memory4 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set Memory key4
Line = 0
type = blf
Value = 183
Callpickup = 
DKtype = 16 
PickupValue = *8

[ memory5 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set Memory key5
Line = 0
type = blf
Value = 184
Callpickup = 
DKtype = 16 
PickupValue = *8

[ memory6 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set Memory key6
Line = 0
type = blf
Value = 186
Callpickup = 
DKtype = 16 
PickupValue = *8

[ memory7 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set Memory key7
Line = 0
type = blf
Value = 187
Callpickup = 
DKtype = 16 
PickupValue = *8

[ memory8 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set Memory key8
Line = 0
type = blf
Value = 188
Callpickup = 
DKtype = 16
PickupValue = *8

[ memory9 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set Memory key9
Line = 0
type = blf
Value = 189
Callpickup = 
DKtype = 16 
PickupValue = *8

[ memory10 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set Memory key10
Line = 0
type = blf
Value = 190
Callpickup = 
DKtype = 16 
PickupValue = *8

[ memory11 ]
path = /yealink/config/vpPhone/vpPhone.ini
#from memory11 to memory 16 are settings for 6 line keys of T28
#DKtype value is the same as it is defined for memory keys.Except for one that t
he line keys cannot be set to blf(DKtype:16).
#Set line key1
DKtype = 
Line = 
Value = 

[ memory12 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set line key2
DKtype =  
Line = 
Value = 
type =

[ memory13 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set line key2
DKtype = 
Line = 
Value = 

[ memory14 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set line key2
DKtype = 
Line = 
Value = 

[ memory15 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set line key2
DKtype = 
Line = 
Value = 

[ memory16 ]
path = /yealink/config/vpPhone/vpPhone.ini
#Set line key2
DKtype =    
Line = 
Value = 
type =
TEMP;

    return $content;
}
function PrincipalFileYealinkVP530($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{

$ByDHCP = existsValue($arrParameters,'By_DHCP',""); 
$time = existsValue($arrParameters,'Time_Zone',12);
$pcportmode= existsValue($arrParameters,'Bridge',1);


$timezone = <<<TEMP
#######################################################################################
##         	                   Time Settings                                         ##
#######################################################################################
#Configure the time zone and time zone name. The time zone ranges from -11 to +12, the default value is +8. 
local_time.time_zone = $time
TEMP;


$pcport= <<<TEMP
#Configure the PC port type; 0-Router, 1-Bridge (default);
#Require reboot;
network.bridge_mode = $pcportmode
TEMP;



switch ($ByDHCP){
    case '' : 
		$configNetwork= "";
		break;
  
    case 0  :
		$dns  = existsValue($arrParameters,'DNS1',''); 
		$dns2 = existsValue($arrParameters,'DNS2','');
		$ip   = existsValue($arrParameters,'IP','');
		$mask = existsValue($arrParameters,'Mask','');
		$gw   = existsValue($arrParameters,'GW','');
		$configNetwork= <<<TEMP
#######################################################################################
##                          Network                                                  ## 
#######################################################################################

#Configure the WAN port type; 0-DHCP (default), 1-PPPoE, 2-Static IP Address;
#Require reboot;
network.internet_port.type =  2
#Configure the static IP address, subnet mask, gateway and DNS server;
#Require Reboot;
network.internet_port.ip = $ip
network.internet_port.mask = $mask
network.internet_port.gateway = $gw
network.primary_dns= $dns
network.secondary_dns = $dns2
TEMP;
		break;
    case 1: 
		$configNetwork= <<<TEMP
#######################################################################################
##                          Network                                                  ## 
#######################################################################################

#Configure the WAN port type; 0-DHCP (default), 1-PPPoE, 2-Static IP Address;
#Require reboot;
network.internet_port.type =  0
TEMP;
		break;

   

}

    $content= <<<TEMP
﻿#!version:1.0.0.1

##File header "#!version:1.0.0.1" can not be edited or deleted.##

$configNetwork

#######################################################################################
##                           Account1 Settings                                       ##                                                                          
#######################################################################################

#Enable or disable the account1, 0-Disabled (default), 1-Enabled;
account.1.enable = 1

#Configure the label displayed on the LCD screen for account1.
account.1.label = $DisplayName

#Configure the display name of account1.
account.1.display_name = $DisplayName

#Configure the username and password for register authentication.
account.1.auth_name = $id_device
account.1.password = $secret

#Configure the register user name.
account.1.user_name = $id_device

#Configure the SIP server address.
account.1.sip_server_host = $ipAdressServer 
#Specify the port for the SIP server. The default value is 5060.
account.1.sip_server_port = 5060
auto_provision.server.url = tftp://$ipAdressServer:69

$pcport

$timezone

TEMP;

    return $content;
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

function  setServerURL($ip_endpoint,$user,$passwd,$ip_server,$model){
    if($model=="VP530")
       $options = array(CURLOPT_POSTFIELDS => 'command=regSetString("/config/system/system.ini","AutoProvision","strServerURL","tftp://'.$ip_server.'")'); 
    elseif($model=="SIP-T38G")
       $options = array(CURLOPT_POSTFIELDS => 'command=regSetString("/phone/config/system.ini","AutoProvision","strServerURL","tftp://'.$ip_server.'")'); 
    else
       return FALSE;

    if($ch = curl_init("http://$user:$passwd@$ip_endpoint/cgi-bin/cgiServer.exx")) 
    {
      curl_setopt_array($ch, $options);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      curl_close($ch);
      return TRUE;
    }else
      return FALSE;

}
?>
