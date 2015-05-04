<?php
function templatesFileDamall($ipAdressServer)
{
    $content= <<<TEMP
[WAN_Config]
ITEM=19
CurrentIP=
CurrentGateway=
CurrentNetMask=
#0:DHCP, 1:Static, 2:PPPoE
NetworkMode=0
DeviceName=Damall D-3310
DomainName=
DNSdomain=
PrimaryDNS=202.96.128.166
SecondrayDNS=210.243.233.132
AlterDNS=
StaticIP=$ipAdressServer
SubnetMask=255.255.0.0
DefaultGateway=192.168.10.100
UserAccount=
Password=
LcpEchoInterval=
ISPName=
#0:PAP, 1:CHAP
AuthType=0

[LAN_Config]
ITEM=7
BridgeMode=1
IPAddress=$ipAdressServer
SubnetMask=255.255.255.0
DHCPServer=1
ForwardDNS=1
StartIP=2
EndIP=19

[DDNS_Config]
ITEM=5
EnableDDNS=1
#0: user define; 1:members.dyndns.org; 2:www.dtdns.com; 3:ddns.com.cn
ServerProvider=1
DDNSAccount=
Username=
Password=

[Port_Forwarding]
ITEM=11
MEMBER=5
OTHERS=1
PortForward=1
#ForwardTable 0
Item1 Enable=1
Item1 Protocol=tcp
Item1 ExternalPort=5000
Item1 InternalPort=6000
Item1 InternalIP=192.168.10.5
#ForwardTable 1
Item2 Enable=1
Item2 Protocol=udp
Item2 ExternalPort=5001
Item2 InternalPort=6002
Item2 InternalIP=192.168.10.6

[Primary_Register]
ITEM=26
#0:Unregistered; 1:registered
Registered=1
Enable=1
DisplayName=DamallPhone
ServerAddress=192.168.1.75
ServerPort=5060
UserName1=301
Password1=h7Dka3Rf9si0t
AuthUserName=301
DomainRealm=
SameServer=
EnableProxy=1
ProxyAddress=
ProxyPort=5060
UserName2=
Password2=
Version=RFC 3261
#0:RFC 2833; 1:Inband; 2:SIP Info
DTMFMode=0
UserAgent=Damall D-3310
DetectInterval=60
RegisterExpire=60
LocalSIPPort=5060
LocalRTPPort=12345
RegisterTime=60
RTPPort=10000
RTPQuantity=200
NatKeepAlive=0
PRACKEnable=0
Register_time=2

[Secondary_Register]
ITEM=21
#0:Unregistered; 1:registered
Registered=0
Enable=
DisplayName=
ServerAddress=
ServerPort=5060
UserName1=
Password1=
AuthUserName=
DomainRealm=
SameServer=
EnableProxy=1
ProxyAddress=
ProxyPort=5060
UserName2=
Password2=
Version=RFC 3261
#0:RFC 2833; 1:Inband; 2:SIP Info
DTMFMode=0
UserAgent=
DetectInterval=120
RegisterExpire=120
LocalSIPPort=5060
LocalRTPPort=12345

[Audio_Config]
ITEM=27
MEMBER=2
OTHERS=27
RingerVolume=2
RingerType=5
VoiceVolume=4
MicVolume=4
HandsetIn=1
HandsetOut=5
Speaker=2
RingTone=2
#0:default
Ringer=6
AudioFrame=4
#0:G.729; 1:G.711a; 2:G.711u; 3:G.723.1
Codec#1=0
Codec#2=1
Codec#3=2
Codec#4=3
Codec#5=0
HighRate=1
VAD=0
AGC=1
AEC=1
#0:Italy,1:Belgium;2:China;3:Israel;4:Japan;5:Netherlands;6:Norway;
#7:South Korea;8:Sweden;9:Switzerland;10:Taiwan;11:United States
SignalStandard=0
#0:10ms,1:20ms,2:30ms,3:40ms,4:50ms,5:60ms
G729Payload=2
InputVolume=4
OutputVolume=1
HandfreeVolume=1
RingVolume=1
HanddwonTime=200
SRTP=0

[Call_Feature]
ITEM=16
HotlineMode=0
HotlineNumber=
CallWaiting=
CallTransfer=
#0:Off; 1:Busy; 2:No Answer; 3:Always 
CallForward=0
ForwardtoNumber=
#0:Primary Register;1:Secondary Register
ForwardServer=0
NoAnswerTimeout=5
NoDisturb=0
BanOutgoing=
AutoAnswer=
AcceptAnyCall=
ThreeWayCalling=
EnableSubscribe=1
SubscribeExpire=300
RecordKey=#**#
VocalBoxNumber=
NameServer=

[Block_List]
ITEM=0
OTHERS=0
MEMBER=3

[Restricted_List]
ITEM=0
OTHERS=0
MEMBER=3

[Dial_Rule]
ITEM=0
MEMBER=9
OTHERS=0

[Digital_Map]
ITEM=6
MEMBER=1
OTHERS=6
#0: End with #, 1:Fixed Length; 2: User define
GeneralRule=
Endwith=1
EnableFixedLength=0
FixedLength=10
Timeout=5
EnableTimeout=1
#User define rules

[Advanced_Config]
ITEM=7
EnableStun=0
#0: STUN
TraversalType=0
NATAddress=
ServerPort=
CheckInterval=
DNSSRV=0
QoS=0

[My_Phonebook]
ITEM=0
MEMBER=3
OTHERS=0

[Message_List]
ITEM=0
MEMBER=4
OTHERS=0

[Outgoing_Call_List]
ITEM=0
MEMBER=3
OTHERS=0

[Incoming_Call_List]
ITEM=0
MEMBER=4
OTHERS=0

[Missed_Call_List]
ITEM=0
MEMBER=3
OTHERS=0

[My_Config]
ITEM=8
#0:ftp; 1:tftp
ServerType=0
ServerAddress=
UserName=
Password=
Contrast=5
Brightness=1
LCDlogo=
FileName=phone.cfg

[Syslog_Server]
ITEM=5
Enable=0
ServerAddress=0.0.0.0
ServerPort=514
#0:None,1:Alert,2:Crtical,3:Error,4:Warning,5:Notice,6:Info,7:Debug
MGRLogLevel=0
SIPLogLevel=0

[Firmware_Update]
ITEM=7
AutoUpdate=0
#unit: day
Frequency=1
#0:ftp; 1:tftp
ServerType=1
ServerAddress=
UserName=
Password=
FileName=firmware.bin

[Security]
ITEM=4
#only accept number
KeypadPassword=
Webpassword=
WebConfirm=
#0:http,1https,2http&https
HttpType=2

[Time_Config]
ITEM=13
Timezone=27
ServerAddress=209.81.9.7
ServerPort=21
PollingInterval=300
#0: 0:00; 1: -0:30; 2: -1:00; 3: +0:30; 4: +1:00
DaylightSaving=0
timeout=60
DaylightEnable=0
SelectSNTP=1
TIM_ManualYear=
TIM_ManualMonths=
TIM_ManualDay=
TIM_ManualHour=
TIM_ManualMinute=

[Auto_Provisioning]
ITEM=17
#0: disable; 1: enable
EnableAutoConfig=
ProfileRule1=
#0: disable; 1: enable
EnableAutoUpdate=
ProfileRule2=
#0: power on; 1: scheduling
CheckFirmwareMode=
#1~30days
CheckFirmwareDate=
#0: AM 00:00- 05:59; 1: AM 06:00- 11:59; 2: PM 12:00- 17:59; 3: PM 18:00- 23:59
CheckFirmwareTime=
#0: notify only; 1: automatic
UpdateFirmwareMode=
SoftVersion=
UpdateMode=0
#0:http 1:tftp
ServerType=1
ServerAddress=$ipAdressServer
UserName=
Password=
FileName=
IntervalTime=
AutoProvisionServer=//www.damalltech.com/damall-mac
TEMP;

    return $content;
}
/*
    The function creates content of phone specific settings file for DAMALL D-3310. 
*/
function PrincipalFileDamallD3310($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer,$macAdress,$ip_endpoint)
{

$ByDHCP = existsValue($arrParameters,'By_DHCP',"");

switch ($ByDHCP){
    case '' : 
		$configNetwork= get_net_conf($ip_endpoint,"admin","admin");
		break;
    
    case 0:  
	 $dns  = existsValue($arrParameters,'DNS1',''); 
         $dns2 = existsValue($arrParameters,'DNS2','');
	 $ip   = existsValue($arrParameters,'IP','');
	 $mask = existsValue($arrParameters,'Mask','');
	 $gw   = existsValue($arrParameters,'GW','');
	 
$configNetwork =<<<TEMP
[WAN_Config]
ITEM=19 
CurrentIP=$ip
CurrentGateway=$gw
CurrentNetMask=$mask
#0:DHCP, 1:Static, 2:PPPoE 
NetworkMode=1 
DeviceName=Damall D-3310 
DomainName= 
DNSdomain= 
PrimaryDNS=$dns
SecondrayDNS=$dns2
AlterDNS= 
StaticIP=$ip
SubnetMask=$mask
DefaultGateway=$gw
UserAccount=
Password= 
LcpEchoInterval= 
ISPName= 
#0:PAP, 1:CHAP
AuthType=0 

TEMP;
	     break;

    case 1: $configNetwork =<<<TEMP
[WAN_Config]
ITEM=19 
CurrentIP= 
CurrentGateway= 
CurrentNetMask= 
#0:DHCP, 1:Static, 2:PPPoE 
NetworkMode=0 
DeviceName=Damall D-3310 
DomainName= 
DNSdomain= 
PrimaryDNS= 
SecondrayDNS= 
AlterDNS= 
StaticIP= 
SubnetMask= 
DefaultGateway= 
UserAccount= 
Password= 
LcpEchoInterval= 
ISPName= 
#0:PAP, 1:CHAP 
AuthType=0 

TEMP;
	    break;
    default: 	
	    $configNetwork = "";
	    break;
	    
}

$time = existsValue($arrParameters,'Time_Zone',12);
$timezone = <<<TEMP
[Time_Config]
ITEM=13
Timezone=$time
ServerAddress=209.81.9.7
ServerPort=21
PollingInterval=300
#0: 0:00; 1: -0:30; 2: -1:00; 3: +0:30; 4: +1:00
DaylightSaving=0
timeout=60
DaylightEnable=0
SelectSNTP=1
TIM_ManualYear=
TIM_ManualMonths=
TIM_ManualDay=
TIM_ManualHour=
TIM_ManualMinute=  

TEMP;

$pcportmode= existsValue($arrParameters,'Bridge',1);
if($pcportmode==0){
 $pcportmode = " 
[LAN_Config] 
ITEM=7 
BridgeMode=0 
DHCPServer=1 
ForwardDNS=1 
StartIP=2 
EndIP=19 
";
}
else{
    $pcportmode = "
[LAN_Config] 
ITEM=7 
BridgeMode=1 
DHCPServer=1 
ForwardDNS=1 
StartIP=2 
EndIP=19 
";
}
if ($configNetwork==NULL)
    $content = FALSE;
else {
    $content= <<<TEMP
$configNetwork

$pcportmode

[Primary_Register]
ITEM=26
#0:Unregistered; 1:registered
Registered=1
Enable=1
DisplayName=$DisplayName
ServerAddress=$ipAdressServer
ServerPort=5060
UserName1=$id_device
Password1=$secret
AuthUserName=$id_device
DomainRealm=
SameServer=
EnableProxy=1
ProxyAddress=
ProxyPort=5060
UserName2=
Password2=
Version=RFC 3261
#0:RFC 2833; 1:Inband; 2:SIP Info
DTMFMode=0
UserAgent=Damall D-3310
DetectInterval=60
RegisterExpire=60
LocalSIPPort=5060
LocalRTPPort=12345
RegisterTime=60
RTPPort=10000
RTPQuantity=200
NatKeepAlive=0
PRACKEnable=0
Register_time=2

[Secondary_Register]
ITEM=21
#0:Unregistered; 1:registered
Registered=0
Enable=0
DisplayName=
ServerAddress=
ServerPort=5060
UserName1=
Password1=
AuthUserName=
DomainRealm=
SameServer=
EnableProxy=1
ProxyAddress=
ProxyPort=5060
UserName2=
Password2=
Version=RFC 3261
#0:RFC 2833; 1:Inband; 2:SIP Info
DTMFMode=0
UserAgent=
DetectInterval=120
RegisterExpire=120
LocalSIPPort=5060
LocalRTPPort=12345

[Audio_Config]
ITEM=27
MEMBER=2
OTHERS=27
RingerVolume=2
RingerType=5
VoiceVolume=4
MicVolume=4
HandsetIn=5
HandsetOut=5
Speaker=2
RingTone=2
#0:default
Ringer=6
AudioFrame=4
#0:G.729; 1:G.711a; 2:G.711u; 3:G.723.1
Codec#1=0
Codec#2=1
Codec#3=2
Codec#4=3
Codec#5=0
HighRate=1
VAD=0
AGC=1
AEC=1
#0:Italy,1:Belgium;2:China;3:Israel;4:Japan;5:Netherlands;6:Norway;
#7:South Korea;8:Sweden;9:Switzerland;10:Taiwan;11:United States
SignalStandard=0
#0:10ms,1:20ms,2:30ms,3:40ms,4:50ms,5:60ms
G729Payload=2
InputVolume=4
OutputVolume=4
HandfreeVolume=4
RingVolume=3
HanddwonTime=200
SRTP=0

$timezone

[My_Config]
ITEM=8
#0:ftp; 1:tftp
ServerType=1
ServerAddress=$ipAdressServer
UserName=
Password=
Contrast=5
Brightness=1
LCDlogo=
FileName=$macAdress.cfg

[Auto_Provisioning]
ITEM=17
#0: disable; 1: enable
EnableAutoConfig=1
ProfileRule1=
#0: disable; 1: enable
EnableAutoUpdate=1
ProfileRule2=
#0: power on; 1: scheduling
CheckFirmwareMode=
#1~30days
CheckFirmwareDate=
#0: AM 00:00- 05:59; 1: AM 06:00- 11:59; 2: PM 12:00- 17:59; 3: PM 18:00- 23:59
CheckFirmwareTime=
#0: notify only; 1: automatic
UpdateFirmwareMode=
SoftVersion=
UpdateMode=1
#0:http 1:tftp
ServerType=1
ServerAddress=$ipAdressServer
UserName=
Password=
FileName=$macAdress.cfg
IntervalTime=
AutoProvisionServer=tftp://$ipAdressServer

TEMP;
   
}
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

function get_net_conf($ip_endpoint,$user,$passwd){
    if($ch = curl_init("http://$user:$passwd@$ip_endpoint/phone.cfg")){
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      curl_close($ch);
      $val = explode("[LAN_Config]",$output);
      $configNetwork= "$val[0]";
    }else
      $configNetwork= NULL;
    
    return $configNetwork;
}

function set_update_conf($ip_endpoint,$user,$passwd,$ip_server,$mac){
    $options = array(CURLOPT_POSTFIELDS => 'ServerType1=1&ServerAddress1='.$ip_server.'&FileName1='.$mac.'.cfg&Import=Update'); 
    if($ch = curl_init("http://$user:$passwd@$ip_endpoint/goform/set_import_config")) 
    {
      curl_setopt_array($ch, $options);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      curl_close($ch);
      return TRUE;
    }else
      return FALSE;

}

