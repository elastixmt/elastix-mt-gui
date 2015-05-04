<?php
/*
    Genera la plantilla de configuración del terminal de Yealink
*/
function PrincipalFileAlcatelTemporisIP800($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{
    $configNetwork = "";
    $ByDHCP = existsValue($arrParameters,'By_DHCP',''); 
    $ByDHCP = ($ByDHCP == 1)?0:2; // 0 indica que es por DHCP y 2 por estatico
    if(($ByDHCP==0)||($ByDHCP==2)){
        $configNetwork ="
[ WAN ]
path= /config/Network/Network.cfg
#0=DHCP,1=PPPoE,2=StaticIP
WANType 	  = $ByDHCP
WANStaticIP       =".existsValue($arrParameters,'IP','')."
WANSubnetMask     =".existsValue($arrParameters,'Mask','')."
WANDefaultGateway =".existsValue($arrParameters,'GW','')."

[ DNS ]
path= /config/Network/Network.cfg
PrimaryDNS   = ".existsValue($arrParameters,'DNS1','')."
SecondaryDNS = ".existsValue($arrParameters,'DNS2','');
   }

$timezone = existsValue($arrParameters,'Time_Zone',''); 
$pcportmode= existsValue($arrParameters,'Bridge',1);

# CONTENT
    $content="

$configNetwork

[ PPPoE ]
path= /config/Network/Network.cfg
PPPoEUser = 
PPPoEPWD = 

[ LAN ]
path= /config/Network/Network.cfg
#0=Router, 1=Bridge
LANTYPE = $pcportmode
RouterIP = 10.0.0.1
LANSubnetMask = 255.255.255.0
EnableDHCP = 1
DHCPStartIP = 10.0.0.10
DHCPEndIP = 10.0.0.100
SpanToPCPort = 0

[ telnet ]
path= /config/Network/Network.cfg
telnet_enable = 0

[ Time ]
path = /config/Setting/Setting.cfg
TimeZone = $timezone
TimeServer1 = europe.pool.ntp.org
TimeServer2 = europe.pool.ntp.org
Interval = 1000
SummerTime = 2
DSTTimeType = 0
TimeZoneInstead = 8
StartTime = 1/1/0
EndTime = 12/31/23
TimeFormat = 1
DateFormat = 6
OffSetTime = 60
DHCPTime = 0

[ autop_mode ]
path = /config/Setting/autop.cfg
mode = 1
schedule_min = 
schedule_time = 
schedule_time_end = 
schedule_dayofweek = 

[ PNP ]
path = /config/Setting/autop.cfg
Pnp = 0

[ cutom_option ]
path = /config/Setting/autop.cfg
cutom_option_code0 = 
cutom_option_type0 = 1

[ autoprovision ]
path = /config/Setting/autop.cfg
server_address = tftp://$ipAdressServer
user = 
password = 

[ account ]
path=  /config/voip/sipAccount0.cfg
Enable = 1
Label = $DisplayName
DisplayName = $DisplayName 
UserName = $id_device
AuthName = $id_device
password = $secret
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
ptime = 20
srtp_encryption = 0
srtp_encryption_algorithm = 0
BackupSIPServerHost = 
BackupSIPServerPort = 5060
Enable 100Rel = 0
precondition = 0
SubsribeRegister = 0
EnableSessionTimer = 0
SessionExpires = 1800
SessionRefresher = 0
EnableUserEqualPhone = 0
BLFList_URI = 
BlfListCode = *97
SubsribeMWI = 0
AnonymousCall = 0
RejectAnonymousCall = 0
Transport = 0
ShareLine = 0
dialoginfo_callpickup = 0
AutoAnswer = 0
MissedCallLog = 1
AnonymousCall_OnCode = 
AnonymousCall_OffCode = 
AnonymousReject_OnCode = 
AnonymousReject_OffCode = 
BLANumber = 
SubscribeMWIExpire = 3600
RegisterMAC = 0
RegisterLine = 0
conf-type = 0
conf-uri = 
SubscribeACDExpire = 3600
SubscribeMWIToVM = 0
CallIDNoIPAddr = 0
MWI Subscription Period = 3600
SIP Registration Retry Timer = 30
DisableAlertInfoURL = 0
RegExpiresOverlap = -1
SubExpiresOverlap = -1
EnableRFC4475 = 1
CIDSource = 0
CPSource = 1
SIPTrustCtrl = 0
FullSDPAnswer = 0
bCalleeIDbyINFO = 0
bPCMANEGVAD = 0
SIPServerType = 0
EnableCompactHeader = 0


[ AES_KEY ]
path = /config/Setting/autop.cfg
aes_key_16 = 
aes_key_16_mac = 
 
[ ringtone ]
path = /tmp/download.cfg
server_address = 
 
[ Lang ]
path = /tmp/download.cfg
server_address = 
 
[ ContactList ]
path = /tmp/download.cfg
server_address = 

[ UserName ]
path = /config/Advanced/Advanced.cfg
admin = 
user = 

[ AdminPassword ]
path =  /config/Setting/autop.cfg
password = 

[ UserPassword ]
path =  /config/Setting/autop.cfg
password =

[ firmware ]
path = /tmp/download.cfg
server_type = 
server_ip = 
server_port = 
login_name = 
login_pswd = 
http_url   = 
firmware_name = 

[ DialNow ]
path = /tmp/dialnow.xml 
server_address =

[ ReplaceRule ]
path = /tmp/dialplan.xml
1 = 0,Prefix,Replace
2 = 0,Prefix,Replace
3 = 0,Prefix,Replace
4 = 0,Prefix,Replace
5 = 0,Prefix,Replace
6 = 0,Prefix,Replace
7 = 0,Prefix,Replace
8 = 0,Prefix,Replace
9 = 0,Prefix,Replace
10 = 0,Prefix,Replace

[ AreaCode ]
path = /config/DialRule/areacode.cfg 
code = 
minlen = 
maxlen = 
LineID = 

[ BlockOut ]
path = /config/DialRule/BlockOut.cfg
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

[ BlockOutLineID ]
path = /config/DialRule/BlockOut.cfg
#Set enable account.
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


[ Forbidden ]
path =  /config/Setting/AdvSetting.cfg
DND = 0
FWD = 0

[ Zero ]
path = /factory/config/Advanced/Advanced.cfg
ForbidZero = 1
WaitTime = 5

[ Transfer ] 
path = /config/Setting/AdvSetting.cfg
EnableSemiAttendTran = 1
BlindTranOnHook = 1
TranOthersAfterConf = 0

[ LLDP ]
path = /factory/config/Network/Network.cfg
EnableLLDP = 0
PacketInterval = 120

[ ActionURL ]
path = /config/Features/Phone.cfg
SetupCompleted = 
LogOn = 
LogOff = 
RegisterFailed = 
Offhook = 
Onhook = 
IncomingCall = 
OutgoingCall = 
CallEstablished = 
DNDOn = 
DNDOff = 
AwaysFWDOn = 
AwaysFWDOff = 
BusyFWDOn = 
BusyFWDOff = 
NoAnswerFWDOn = 
NoAnswerFWDOff = 
TransferCall = 
BlindTransferCall = 
AttendedTransferCall = 
Hold = 
Unhold = 
Mute = 
Unmute = 
MissedCall = 
CallTerminated = 

[ WatchDog ]
path = /config/Features/Phone.cfg
IsUse = 1
Time = 10

[ sip ]
path = /config/Setting/AdvSetting.cfg
ReservePound = 1
UseOutBoundInDialog = 1

[ memory11 ]
path = /config/vpPhone/vpPhone.ini
DKtype = 15
Line = 1
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 

[ memory12 ]
path = /config/vpPhone/vpPhone.ini
DKtype = 15
Line = 2
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 

[ memory13 ]
path = /config/vpPhone/vpPhone.ini
DKtype = 15
Line = 3
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 

[ memory14 ]
path = /config/vpPhone/vpPhone.ini
DKtype = 15
Line = 4
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 

[ memory15 ]
path = /config/vpPhone/vpPhone.ini
DKtype = 15
Line = 5
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 

[ memory16 ]
path = /config/vpPhone/vpPhone.ini
DKtype = 15
Line = 6
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 
";
    return $content;
}

function templatesAlcatel($ipAdressServer)
{
    $content= <<<TEMP
[ WAN ]
path= /config/Network/Network.cfg
#0=DHCP,1=PPPoE,2=StaticIP
WANType = 0
WANStaticIP = 
WANSubnetMask = 
WANDefaultGateway = 

[ DNS ]
path= /config/Network/Network.cfg
PrimaryDNS = 192.168.10.100
SecondaryDNS = 

[ PPPoE ]
path= /config/Network/Network.cfg
PPPoEUser = 
PPPoEPWD = 

[ LAN ]
path= /config/Network/Network.cfg
#0=Router, 1=Bridge
LANTYPE = 1
RouterIP = 10.0.0.1
LANSubnetMask = 255.255.255.0
EnableDHCP = 1
DHCPStartIP = 10.0.0.10
DHCPEndIP = 10.0.0.100
SpanToPCPort = 0

[ telnet ]
path= /config/Network/Network.cfg
telnet_enable = 1

[ Time ]
path = /config/Setting/Setting.cfg
TimeZone = -5
TimeZoneName = United States-Eastern Time
TimeServer1 = europe.pool.ntp.org
TimeServer2 = europe.pool.ntp.org
Interval = 1000
SummerTime = 2
DSTTimeType = 0
TimeZoneInstead = 8
StartTime = 1/1/0
EndTime = 12/31/23
TimeFormat = 1
DateFormat = 6
OffSetTime = 60
DHCPTime = 0
[ autop_mode ]
path = /config/Setting/autop.cfg
mode = 
schedule_min = 
schedule_time = 
schedule_time_end = 
schedule_dayofweek = 

[ PNP ]
path = /config/Setting/autop.cfg
Pnp = 0

[ cutom_option ]
path = /config/Setting/autop.cfg
cutom_option_code0 = 
cutom_option_type0 = 1

[ autoprovision ]
path = /config/Setting/autop.cfg
server_address = tftp://$ipAdressServer
user = 
password = 

[ account ]
path=  /config/voip/sipAccount0.cfg
Enable = 1
Label = 
DisplayName =  
UserName = 
AuthName = 
password = 
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
ptime = 20
srtp_encryption = 0
srtp_encryption_algorithm = 0
BackupSIPServerHost = 
BackupSIPServerPort = 5060
Enable 100Rel = 0
precondition = 0
SubsribeRegister = 0
EnableSessionTimer = 0
SessionExpires = 1800
SessionRefresher = 0
EnableUserEqualPhone = 0
BLFList_URI = 
BlfListCode = *97
SubsribeMWI = 0
AnonymousCall = 0
RejectAnonymousCall = 0
Transport = 0
ShareLine = 0
dialoginfo_callpickup = 0
AutoAnswer = 0
MissedCallLog = 1
AnonymousCall_OnCode = 
AnonymousCall_OffCode = 
AnonymousReject_OnCode = 
AnonymousReject_OffCode = 
BLANumber = 
SubscribeMWIExpire = 3600
RegisterMAC = 0
RegisterLine = 0
conf-type = 0
conf-uri = 
SubscribeACDExpire = 3600
SubscribeMWIToVM = 0
CallIDNoIPAddr = 0
MWI Subscription Period = 3600
SIP Registration Retry Timer = 30
DisableAlertInfoURL = 0
RegExpiresOverlap = -1
SubExpiresOverlap = -1
EnableRFC4475 = 1
CIDSource = 0
CPSource = 1
SIPTrustCtrl = 0
FullSDPAnswer = 0
bCalleeIDbyINFO = 0
bPCMANEGVAD = 0
SIPServerType = 0
EnableCompactHeader = 0


[ AES_KEY ]
path = /config/Setting/autop.cfg
aes_key_16 = 
aes_key_16_mac = 
 
[ ringtone ]
path = /tmp/download.cfg
server_address = 
 
[ Lang ]
path = /tmp/download.cfg
server_address = 
 
[ ContactList ]
path = /tmp/download.cfg
server_address = 

[ UserName ]
path = /config/Advanced/Advanced.cfg
admin = 
user = 

[ AdminPassword ]
path =  /config/Setting/autop.cfg
password = 

[ UserPassword ]
path =  /config/Setting/autop.cfg
password =

[ firmware ]
path = /tmp/download.cfg
server_type = 
server_ip = 
server_port = 
login_name = 
login_pswd = 
http_url   = 
firmware_name = 

[ DialNow ]
path = /tmp/dialnow.xml 
server_address =

[ ReplaceRule ]
path = /tmp/dialplan.xml
1 = 0,Prefix,Replace
2 = 0,Prefix,Replace
3 = 0,Prefix,Replace
4 = 0,Prefix,Replace
5 = 0,Prefix,Replace
6 = 0,Prefix,Replace
7 = 0,Prefix,Replace
8 = 0,Prefix,Replace
9 = 0,Prefix,Replace
10 = 0,Prefix,Replace

[ AreaCode ]
path = /config/DialRule/areacode.cfg 
code = 
minlen = 
maxlen = 
LineID = 

[ BlockOut ]
path = /config/DialRule/BlockOut.cfg
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

[ BlockOutLineID ]
path = /config/DialRule/BlockOut.cfg
#Set enable account.
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


[ Forbidden ]
path =  /config/Setting/AdvSetting.cfg
DND = 0
FWD = 0

[ Zero ]
path = /factory/config/Advanced/Advanced.cfg
ForbidZero = 1
WaitTime = 5

[ Transfer ] 
path = /config/Setting/AdvSetting.cfg
EnableSemiAttendTran = 1
BlindTranOnHook = 1
TranOthersAfterConf = 0

[ LLDP ]
path = /factory/config/Network/Network.cfg
EnableLLDP = 0
PacketInterval = 120

[ ActionURL ]
path = /config/Features/Phone.cfg
SetupCompleted = 
LogOn = 
LogOff = 
RegisterFailed = 
Offhook = 
Onhook = 
IncomingCall = 
OutgoingCall = 
CallEstablished = 
DNDOn = 
DNDOff = 
AwaysFWDOn = 
AwaysFWDOff = 
BusyFWDOn = 
BusyFWDOff = 
NoAnswerFWDOn = 
NoAnswerFWDOff = 
TransferCall = 
BlindTransferCall = 
AttendedTransferCall = 
Hold = 
Unhold = 
Mute = 
Unmute = 
MissedCall = 
CallTerminated = 

[ WatchDog ]
path = /config/Features/Phone.cfg
IsUse = 1
Time = 10

[ sip ]
path = /config/Setting/AdvSetting.cfg
ReservePound = 1
UseOutBoundInDialog = 1

[ memory11 ]
path = /factory/config/vpPhone/vpPhone.ini
DKtype = 15
Line = 1
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 

[ memory12 ]
path = /factory/config/vpPhone/vpPhone.ini
DKtype = 15
Line = 2
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 

[ memory13 ]
path = /factory/config/vpPhone/vpPhone.ini
DKtype = 15
Line = 3
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 

[ memory14 ]
path = /factory/config/vpPhone/vpPhone.ini
DKtype = 15
Line = 4
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 

[ memory15 ]
path = /factory/config/vpPhone/vpPhone.ini
DKtype = 15
Line = 5
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 

[ memory16 ]
path = /factory/config/vpPhone/vpPhone.ini
DKtype = 15
Line = 6
type = 
Value = 
KEY_MODE = Asterisk
HotNumber = 
HotLineId = 1
Callpickup = 
IntercomId = -1
IntercomNumber = 
PickupValue = 
Label = 
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

function set_provision_server($ip_endpoint,$user,$passwd,$ip_server,$mac){
    $options = array(CURLOPT_POSTFIELDS => 'PAGEID=16&CONFIG_DATA=þ1þtftp://'.$ip_server.'þþ********þþ********þ1þþ00:00þ00:00þþ********þ1þ1þ1þ3'); 
    if($ch = curl_init("http://$user:$passwd@$ip_endpoint/cgi-bin/ConfigManApp.com")) 
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
