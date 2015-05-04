<?php 
 /*
  plantilla de configuración del terminal de Xorcom
 */

function PrincipalFileXorcom($DisplayName, $id_device, $secret, $ipAdressServer)
{

    $content="
[ cfg:/phone/config/voip/sipAccount0.cfg,account=0;reboot=1 ]
account.Enable = 1
account.Label = $DisplayName
account.DisplayName = $DisplayName
account.UserName = $id_device
account.AuthName = $id_device
account.password = $secret
account.SIPServerHost = $ipAdressServer
";
    return $content;
}

function templatesFileXorcom($ipAdressServer)

{

    $content= <<<TEMP

;;[ bin:/phone/config/ContactData.xml]
;;url = http://10.2.9.31:9/auto/ContactData.xml
;;[ bin:/phone/config/DialNow.xml]
;;url = http://10.2.9.31:9/auto/DialNow.xml
;;[ bin:/phone/config/DialPlan.xml]
;;url = http://10.2.9.31:9/auto/DialPlan.xml
;;[ bin:/phone/config/ringtone/tianliang.wav ]
;;url=http://10.2.9.31:9/auto/tianliang.wav
;;[ rom:Firmware]
;;url = tftp://10.2.9.5/update/65.0.0.208.rom
 

[ cfg:/phone/config/voip/sipAccount0.cfg,account=0;reboot=1 ]

audio0.enable = 1
audio0.priority = 1
audio0.PayloadType = PCMU
audio0.rtpmap = 0

audio1.enable = 1
audio1.priority = 2
audio1.PayloadType = PCMA
audio1.rtpmap = 8

audio2.enable = 0
audio2.priority = 4
audio2.PayloadType = G723_53
audio2.rtpmap = 4

audio3.enable = 0
audio3.priority = 0
audio3.PayloadType = G723_63
audio3.rtpmap = 4

audio4.enable = 1
audio4.priority = 5
audio4.PayloadType = G729
audio4.rtpmap = 18

audio5.enable = 1
audio5.priority = 4
audio5.PayloadType = G722
audio5.rtpmap = 9


audio6.enable = 0
audio6.priority = 0
audio6.PayloadType = iLBC
audio6.rtpmap = 102

audio7.enable = 0
audio7.priority = 0
audio7.PayloadType = G726-16
audio7.rtpmap = 112

audio8.enable = 0
audio8.priority = 0
audio8.PayloadType = G726-24
audio8.rtpmap = 102

audio9.enable = 0
audio9.priority = 0
audio9.PayloadType = G726-32
audio9.rtpmap = 2

audio10.enable = 0
audio10.priority = 11
audio10.PayloadType = G726-40
audio10.rtpmap = 104

account.SIPServerPort = 5060
account.SIPListenRandom = 0
account.SIPListenPort = 5060
account.Expire = 3600
account.UseOutboundProxy = 0
account.OutboundHost = 
account.OutboundPort = 5060
account.BakOutboundHost = 

account.BakOutboundPort = 5060
account.proxy-require = 
account.ptime = 20
account.srtp_encryption = 0
account.Enable 100Rel = 0
account.precondition = 0
account.SubsribeRegister = 0
account.CIDSource = 0
account.EnableSessionTimer = 0
account.SessionExpires = 
account.SessionRefresher = 0
account.EnableUserEqualPhone = 0
account.SubsribeMWI = 0
account.AnonymousCall = 0
account.RejectAnonymousCall = 0
account.Transport = 0
account.ShareLine = 0
account.dialoginfo_callpickup = 0
account.AutoAnswer = 0
account.MissedCallLog = 1
account.AnonymousCall_OnCode = 
account.AnonymousCall_OffCode = 
account.AnonymousReject_OnCode = 
account.AnonymousReject_OffCode = 
account.BLANumber = 
account.SubscribeMWIExpire = 3600
account.RegisterMAC = 
account.RegisterLine = 



RingTone.RingType = commom
DTMF.DTMFInbandTransfer = 1
DTMF.DTMFPayload = 101
DTMF.InfoType = 0


NAT.NATTraversal = 0
NAT.STUNServer = 0
NAT.STUNPort = 3478
NAT.EnableUDPUpdate = 1
NAT.UDPUpdateTime = 30
NAT.rport = 0


ADVANCED.default_t1 = 0.5
ADVANCED.default_t2 = 4
ADVANCED.default_t4 = 5


blf.SubscribePeriod = 1800
blf.BLFList_URI = 


[ cfg:/phone/config/voip/sipAccount1.cfg,account=1;reboot=1 ]
account.Enable = 0
[ cfg:/phone/config/voip/sipAccount2.cfg,account=2;reboot=1 ]
account.Enable = 0

[ cfg:/phone/config/system.ini,reboot=1 ]
LocalTime.TimeServer1 = $ipAdressServer
LocalTime.TimeServer2 = 0.centos.pool.ntp.org
LocalTime.TimeZone = 0
LocalTime.DHCPTime = 1
LocalTime.bDSTEnable = 1
LocalTime.iDSTType = 2
LocalTime.TimeFormat = 1
LocalTime.DateFormat = 0
Network.eWANType = 0

Network.strWANIP = 
Network.strWANMask = 
Network.strWanGateway =
Network.strWanPrimaryDNS =202.101.103.55
Network.strWanSecondaryDNS =202.101.103.54
Network.strPPPoEUser = 
Network.strPPPoEPin = 
Network.bBridgeMode = 1
Network.strLanIP = 10.0.0.1
Network.strLanMask = 255.255.255.0
Network.strDHCPClientBegin = 10.0.0.10
Network.strDHCPClientEnd = 10.0.0.100

AutoProvision.bEnablePowerOn = 1
AutoProvision.strServerURL = $ipAdressServer
AutoProvision.strKeyAES16 = 
AutoProvision.strKeyAES16MAC = 
AutoProvision.strUser = 
AutoProvision.strPassword = 

QoS.SIGNALTOS = 10
QoS.RTPTOS = 10


VPN.EnableVPN = 1
RTPPORT.MaxRTPPort = 11800
RTPPORT.MinRTPPort = 11780

VLAN.ISVLAN = 0
VLAN.VID = 0
VLAN.USRPRIORITY = 0
VLAN.PC_PORT_VLAN_ENABLE = 0

VLAN.PC_PORT_VID = 0
VLAN.PC_PORT_PRIORITY = 0

telnet.telnet_enable = 0

[ cfg:/phone/config/user.ini,reboot=1 ]
PoundSend.Enable = 1
Webserver Type.WebType = 1
AreaCode.code = 
AreaCode.minlen = 1
AreaCode.maxlen = 15


BlockOut.1 = 
BlockOut.2 = 
BlockOut.3 = 
BlockOut.4 = 
BlockOut.5 = 
BlockOut.6 = 
BlockOut.7 = 
BlockOut.8 = 
BlockOut.9 = 
BlockOut.10 = 


AlwaysFWD.Enable = 0
AlwaysFWD.Target = 
AlwaysFWD.On_Code = 
AlwaysFWD.Off_Code = 


BusyFWD.Enable = 0
BusyFWD.Target = 
BusyFWD.On_Code = 
BusyFWD.Off_Code = 

TimeoutFWD.Enable = 0
TimeoutFWD.Target = 
TimeoutFWD.Timeout = 10
TimeoutFWD.On_Code = 
TimeoutFWD.Off_Code = 

Message.VoiceNumber0 = 
Message.VoiceNumber1 = 
Message.VoiceNumber2 = 

Features.Call_Waiting = 1
Features.Hotlinenumber = 
Features.Hotlinedelay = 4
Features.BusyToneDelay = 
Features.LCD_Logo = 0
Features.DND_Code = 480
Features.Refuse_Code = 486
Features.DND_On_Code = 
Features.DND_Off_Code = 
Features.CallCompletion = 0
Features.AllowIntercom  = 1
Features.IntercomMute  = 0
Features.IntercomTone  = 1
Features.IntercomBarge  = 1
Features.Call_WaitingTone = 1
Features.ButtonSoundOn = 1

Emergency.Num = 0

RingerDevice.IsUseHeadset = 0

AutoRedial.EnableRedial = 0
AutoRedial.RedialInterval = 10
AutoRedial.RedialTimes = 10

Transfer.BlindTranOnHook = 1 
Transfer.EnableSemiAttendTran = 1
Transfer.TranOthersAfterConf = 0  

ReplaceRule.ReplaceAll = 0

Forbidden.DND = 0
Forbidden.FWD = 0

sip.ReservePound = 1
sip.RFC2543Hold = 0
sip.UseOutBoundInDialog = 1

FactoryConfig.CustomEnabled = 0

PhoneSetting.BacklightTime = 30
PhoneSetting.Ringtype = Ring1.wav
PhoneSetting.InterDigitTime = 4
PhoneSetting.FlashHookTimer = 1
PhoneSetting.Lock = 0
PhoneSetting.ReDialTone = 0
PhoneSetting.LogonWizard = 0
PhoneSetting.IsDeal180 = 1
PhoneSetting.ContrastEXP1 = 6 
PhoneSetting.ContrastEXP2 = 6 
PhoneSetting.ContrastEXP3 = 6 
PhoneSetting.ContrastEXP4 = 6 
PhoneSetting.ContrastEXP5 = 6 
PhoneSetting.ContrastEXP6 = 6

Lang.ActiveWebLanguage = English
Lang.WEBLanguage = English

RemotePhoneBook0.URL = 
RemotePhoneBook0.Name = 
RemotePhoneBook1.URL = 
RemotePhoneBook1.Name = 
RemotePhoneBook2.URL = 
RemotePhoneBook2.Name = 
RemotePhoneBook3.URL = 
RemotePhoneBook3.Name = 
RemotePhoneBook4.URL = 
RemotePhoneBook4.Name = 


AlertInfo0.Text = 
AlertInfo0.Ringer = 1

AlertInfo1.Text = 
AlertInfo1.Ringer = 1

AlertInfo2.Text = 
AlertInfo2.Ringer = 1

AlertInfo3.Text = 
AlertInfo3.Ringer = 1

AlertInfo4.Text = 
AlertInfo4.Ringer = 1

AlertInfo5.Text = 1
AlertInfo5.Ringer = 1

AlertInfo6.Text = 
AlertInfo6.Ringer = 1

AlertInfo7.Text = 
AlertInfo7.Ringer = 1

AlertInfo8.Text = 
AlertInfo8.Ringer = 1

AlertInfo9.Text = 
AlertInfo9.Ringer = 1
                    
vpm_tone_Country.Country = Custom   

Tone Param.dial = 
Tone Param.ring = 
Tone Param.busy =  
Tone Param.congestion = 
Tone Param.callwaiting = 
Tone Param.dialrecall = 
Tone Param.record = 
Tone Param.info = 
Tone Param.stutter = 
Tone Param.message = 
Tone Param.autoanswer = 
Tone Param.callwaiting2 = 
Tone Param.callwaiting3 = 
Tone Param.callwaiting4 = 

vpm_default.dial = 1
vpm_default.ring = 1
vpm_default.busy = 1
vpm_default.congestion = 1
vpm_default.callwaiting = 1
vpm_default.dialrecall = 1
vpm_default.record = 1
vpm_default.info = 1
vpm_default.stutter = 1
vpm_default.message = 1
vpm_default.autoanswer = 1


[ cfg:/phone/config/vpPhone/vpPhone.ini,reboot=0 ]

programablekey1.DKtype = 30
programablekey1.Line = 0
programablekey1.Value = 
programablekey1.XMLPhoneBook = 

memory1.Line = 0
memory1.type = num
memory1.Value = 
memory1.KEY_MODE = Asterisk
memory1.HotLineId = 1
memory1.Callpickup = 
memory1.IntercomId = -1
memory1.IntercomNumber = 
memory1.DKtype = 
memory1.PickupValue = 


[ cfg:phone/config/vpPhone/Ext38_00000000000001.cfg ]
Key0.DKtype = 0
Key0.Line = 0
Key0.Value = 
Key0.type = 
Key0.PickupValue = 
Key0.Label = 
;;Ext38_00000000000001.cfg~Ext38_00000000000006.cfg stands for six expansion modules
;;Key0~Key39 stands for 40 DSSKey on the expansion  modules
TEMP;
    return $content;
}

?>
