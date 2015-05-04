<?php
function templatesFileEscene($ipAdressServer)
{
    $content= <<<TEMP
<all> 
 <SipDSPs> 
			<dsp EchoSuppression="0" CodecType="0"
CodecType1="1" CodecType2="2" CodecType3="3" CodecType4="4"
CodecType5="5" CodecType6="6" CodecType7="7" CodecType8="8"
HandInputVoice="5" HandOutputVoice="3" EarphoneInputVoice="5"
EarphoneOutputVoice="3" RingTypeVoice="3" DiallingTone="1" RTPsize="1"
HandFreeInputVoice="5" HandFreeOutputVoice="3" VoEnableAEC="1"
VoEnableAGC="1" HighG723="1" VoEnableVAD="0" ringfileName0=""
ringfileName1="" ringfileName2="" ringfileName3="" ringfileName4=""
ringfileName5="" ringfileName6="" ringfileName7="" ringfileName8=""
ringfileName9="" CurrentRingName="Ring1" enableCode0="G722"
enableCode1="G711A" enableCode2="G711U" enableCode3="G729A"
enableCode4="G723" enableCode5="" enableCode6="" enableCode7=""
enableCode8="" disableCode0="" disableCode1="" disableCode2=""
disableCode3="" disableCode4="" disableCode5="" disableCode6=""
disableCode7="" disableCode8="" disableCode9="" />
	 </SipDSPs>
	 
	<pnps> 
			<pnp AutoGainBasicFlag="1" AutoUpVersionFlag="1"
UsePnPFlag="0" ReportLocalIP="*99" mode="0" address="" key=""
autoConfig="0" autoContract="0" autoUpgrade="0" />
	 </pnps>
	 
<Privisions> 
	<privision AutoPrivisionFlag="1" 
DHCPOptionFlag="0" Freqency="168" time="24" protocol="0" 
Firmware="TFTP://$ipAdressServer" DHCPOptionValue="66" username="" 
password="" DownloadFirmwar="0" DownloadKernel="0" DownloadPhonebook="0" 
DownloadPersonPhonebook="0" DownloadBroadsoft="0" DownloadExtension="1" 
Downloadconfig="1" Bootingchecked="1" ExtensionNumber="" 
BootingcheckedMode="1" />
</Privisions>
	 
	<Cwmps> 
			<cwmp CwmpConfigFlag="0" CwmpEveryReboot="0"
SerialNumber="00100400YJ012050000000268b004129"
CwmpHost="http://183.62.12.23:8012/service.tr069" Port="8012" username=""
password="" protocol="1" Periodic="0" freqency="3600" />
	 </Cwmps>
	 
	 
	<Networks> 
			<network DefaultGateways="192.168.10.100"
NetConfigType="2" IPAddress="$ipAdressServer" IPDNS="192.168.0.1"
SecondDNS="0.0.0.0" SubnetMask="255.255.0.0" UsrKey="" UsrName=""
MTU="1500" WebPort="80" AutoGetDNS="0" />
	 
			<newTelnet TelnetPort="23" />
	 </Networks>
	 
	<NATs> 
			<nat PCPortMode="0" PCPortIPAddress=""
PCPortIPMask="" DHCPServerEnable="0" DHCPStartAddress=""
DHCPEndAddress="" />
	 </NATs>
	 
	<Proxys> 
			<proxy ProxyServerEnable="0"
ProxyServerPort="1080" AnonymousLogin="1" ProxyServerDomain=""
Username="" Password="" />
	 </Proxys>
	 
	<sipServers> 
			<sipserver DomainName="" ProxyServerAddress=""
SecondDomainName="" STUNAddress="" STUNEnableFlag="0"
SipRefreshTime="3600" LocalPort="5060" LinkUse="0" BeginPort="10000"
EndPort="10128" Qos="40" SubExpire="3600" AffiliatedPortEnable="1"
P-Asserted-Identity="1" />
	 </sipServers>
	 
	<syss> 
			<sys KeyboardPasswd="" KeyboardEncrypt="0"
FirstDialTime="15" MaxConnectTime="30" NoAnswerTime="70"
NoAnswerTimeEnable="1" UpgradeCheck="1" NetworkPacket="0"
PstnRingType="1" pictureType="0" startPicturePath="" idlePicturePath=""
waitPicturePath1="" waitPicturePath2="" EmbeddedMode="0"
EmbeddedFreqency="300" EmbeddedUrl="" voicemail="0" PoundSendType="0"
voicemailToneEnable="0" />
	 </syss>
	 
	<DTMEFs> 
			<DTMF DTMFSendType="0" DTMFShowSendType="0"
RFC2833CodeId="101" SIPInfoReSend="0" />
	 </DTMEFs>
	 
	<calls> 
			<call PrefixPSTNNum="" PrefixVOIPNum=""
AutoAnswerNum="123" CallLeaveMessageNum="*97" PlaycallwaitingTone="0"
AfterTurnType="0" ShowPhonelist="0" AutoAnswerEnableFlag="0"
AutoAnswerEnableGroupName="" AutoAnswerMode="0" SIPQos="26" RTPQos="46"
CallBusyTone="1" MisscallDisEnable="1" AvoidDisturbEnableFlag="0"
forkEnableFlag="1" forktime="500" Unconditional="0" UnconditionalNum=""
busytransfer="0" busytransferNum="" UnAnswer="0" UnAnswerNum=""
RingFrequency="15" PickupEnable="1" ConfEndMethod="0" />
	 </calls>
	 
	<SysTimes> 
			<systime
SNTPAddress="sparky.services.adelaide.edu.au" DaylightSaveingFlag="0"
SNTPEnableFlag="1" TimeZoneType="20" DaySet="1" HourSet="0" MinuteSet="0"
SecondSet="0" YearSet="2000" MouthSet="1" AutoAddress="0" DSTtype="0"
DSTStartMonth="1" DSTStartDay="1" DSTStartHour="0" DSTEndMonth="12"
DSTEndDay="31" DSTEndHour="23" DSTWeekStartMonth="1" DSTWeekStartWeek="0"
DSTWeekStartInMonth="1" DSTWeekStartHour="0" DSTWeekEndMonth="12"
DSTWeekEndWeek="6" DSTWeekEndInMonth="5" DSTWeekEndHour="23" Offset="60" />
	 </SysTimes>
	 
	<FunCodes> 
			<CallFun CallBackFC="" CallWaitFC=""
TransferCodeEnable="0" TransferCodeNum="" />
	 </FunCodes>
	 
	<RouteGoals> 
			<route DialNumLength="25" DialOutButton="1"
DialOutFlag="0" DialOutMaxTime="5" />
	 </RouteGoals>
	 
	<RouteTabs />
	 
	<BlackTabs />
	 
	<logs> 
			<log SysLogAddres="" LogCallGrade="0" LogSave="0"
LogType="1" />
	 </logs>
	 
	<hotlineBLFs> 
			<hotlineBLF BLF="1" />
	 </hotlineBLFs>
	 
	<lcds> 
			<lcd Password="" AvoidDisturbEnableFlag="0"
Language="1" WebLanguage="1" Light="3" NoSoundEnableFlag="0"
ScreenLightEnableFlag="1" BackLightEnableFlag="0" TimeFormat="0"
TimeListSeparator="0" TouchScreen="0" CloseLightTime="60" ScreenTime="60"
Contrast="3" ScreenSaverEnable="0" lockScreenPassword="1234"
LockScreenEnable="0" LockScreenMode="0" LockScreenTime="60" />
	 </lcds>
	 
	<passwords> 
			<password UsrName="root" Password="root"
User="user" UserPassword="user" type="0" />
	 </passwords>
	 
	<HotLineFuns> 
			<HotLineFun HotLineState="0" HotLineNum="" />
	 </HotLineFuns>
	 
	<LDAPs> 
			<ldap id="0" LDAPName="" LDAPNumber=""
Address="0.0.0.0" Com="389" Base="" UserName="" Password="" MaxHit="50"
Name_Attribites1="" Name_Attribites2="" Name_Attribites3=""
Number_Attribites1="" Number_Attribites2="" Number_Attribites3=""
Protocol="3" Deslay="0" call="1" Results="1" PreDial_Dial="0"
LDAPEnable="0" />
	 
			<ldap id="1" LDAPName="" LDAPNumber=""
Address="0.0.0.0" Com="389" Base="" UserName="" Password="" MaxHit="50"
Name_Attribites1="" Name_Attribites2="" Name_Attribites3=""
Number_Attribites1="" Number_Attribites2="" Number_Attribites3=""
Protocol="3" Deslay="0" call="1" Results="1" PreDial_Dial="0"
LDAPEnable="0" />
	 </LDAPs>
	 
	<sipUsers> 
			<sipUser id="0" Describe=""
DomainName="" Password="" ProxyServerAddress=""
SecondProxyServerAddress="" PollingRegistrationTime="32"
RefreshTime="3600" Subscribe="1800" SecondDomainName="" STUNAddress=""
STUNEnableFlag="0" UserName="" UserNumber="" flag="0" LinkUse="0"
PossessNumber="1" SupportNumber="8" approveName="" RTPBegin="10000"
RTPEND="10128" EnableAccount="1" AccountMode="0" RegisterMethod="0"
BLAEnableFlag="0" BLANum="" AnonymousCall="0" UserSessionTimerEnable="0"
SessionTimer="300" AllowEventsEnable="0" DNSSRVEnable="0"
RegisteredNAT="1" />
	 
			<sipUser id="1" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="2" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="3" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="4" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="5" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="6" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="7" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 </sipUsers>
	 
	<SipEncrys> 
			<SipEncry id="0" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="1" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="2" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="3" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="4" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="5" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="6" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="7" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 </SipEncrys>
	 
	<modes> 
			<mode Headphonesmode="0" RingMode="0" />
	 </modes>
	 
	<VPNs> 
			<VPN VPNUserName="" VPNUserPassword=""
VPNServer="" Enable="0" Type="0" />
	 </VPNs>
	 
	<VLANs> 
			<Vlan EnableVlan="0" LocalEnableVlan="0"
PCEnableVlan="0" LocalVID="0" PCVID="0" LocalPriority="0" PCPriority="0" />
	 </VLANs>
</all>
TEMP;

    return $content;
}
/*
    The function creates content of phone specific settings file for ESCENE ES620. 
*/
function PrincipalFileEscene620($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{

$ByDHCP = existsValue($arrParameters,'By_DHCP',"");

switch ($ByDHCP){
    case '' : $configNetwork= <<<TEMP
<Networks> 
  <network IPDNS="" SecondDNS="" UsrKey="" UsrName="" MTU="1500" 
  WebPort="80" AutoGetDNS="1" /> <newTelnet TelnetPort="23" />
</Networks>
TEMP;
	     break;
    case 0:  $configNetwork ="<Networks> 
  <network DefaultGateways=\"".existsValue($arrParameters,'GW','')."\" NetConfigType=\"0\" IPAddress=\"".existsValue($arrParameters,'IP','')."\" 
  IPDNS=\"".existsValue($arrParameters,'DNS1','')."\" SecondDNS=\"".existsValue($arrParameters,'DNS2','')."\" SubnetMask=\"".existsValue($arrParameters,'Mask','')."\" UsrKey=\"\" UsrName=\"\"MTU=\"1500\" 
  WebPort=\"80\" AutoGetDNS=\"1\" /> <newTelnet TelnetPort=\"23\" />
	</Networks>";
	     break;

    case 1: $configNetwork ="<Networks> 
			<network DefaultGateways=\"\" 
NetConfigType=\"2\" IPAddress=\"\" IPDNS=\"\"
SecondDNS=\"\" SubnetMask=\"\" UsrKey=\"\" UsrName=\"\"
MTU=\"1500\" WebPort=\"80\" AutoGetDNS=\"1\" />
	<newTelnet TelnetPort=\"23\" />
	 </Networks>";
	    break;
    default: 	
	    $configNetwork = "";
	    break;
	    
}


$timezone = "TimeZoneType=\"".existsValue($arrParameters,'Time_Zone',12)."\""; 
$pcportmode= existsValue($arrParameters,'Bridge',1);
if($pcportmode==0)
    $pcportmode= "PCPortMode=\"1\"";
else
    $pcportmode= "PCPortMode=\"0\"";

    $content= <<<TEMP
<all> 
 <SipDSPs> 
			<dsp EchoSuppression="0" CodecType="0"
CodecType1="1" CodecType2="2" CodecType3="3" CodecType4="4"
CodecType5="5" CodecType6="6" CodecType7="7" CodecType8="8"
HandInputVoice="5" HandOutputVoice="3" EarphoneInputVoice="5"
EarphoneOutputVoice="3" RingTypeVoice="3" DiallingTone="1" RTPsize="1"
HandFreeInputVoice="5" HandFreeOutputVoice="3" VoEnableAEC="1"
VoEnableAGC="1" HighG723="1" VoEnableVAD="0" ringfileName0=""
ringfileName1="" ringfileName2="" ringfileName3="" ringfileName4=""
ringfileName5="" ringfileName6="" ringfileName7="" ringfileName8=""
ringfileName9="" CurrentRingName="Ring1" enableCode0="G722"
enableCode1="G711A" enableCode2="G711U" enableCode3="G729A"
enableCode4="G723" enableCode5="" enableCode6="" enableCode7=""
enableCode8="" disableCode0="" disableCode1="" disableCode2=""
disableCode3="" disableCode4="" disableCode5="" disableCode6=""
disableCode7="" disableCode8="" disableCode9="" />
	 </SipDSPs>
	 
	<pnps> 
			<pnp AutoGainBasicFlag="1" AutoUpVersionFlag="1"
UsePnPFlag="0" ReportLocalIP="*99" mode="0" address="" key=""
autoConfig="0" autoContract="0" autoUpgrade="0" />
	 </pnps>
	 
<Privisions> 
	<privision AutoPrivisionFlag="1" 
DHCPOptionFlag="0" Freqency="168" time="24" protocol="0" 
Firmware="TFTP://$ipAdressServer" DHCPOptionValue="66" username="" 
password="" DownloadFirmwar="0" DownloadKernel="0" DownloadPhonebook="0" 
DownloadPersonPhonebook="0" DownloadBroadsoft="0" DownloadExtension="1" 
Downloadconfig="1" Bootingchecked="1" ExtensionNumber="" 
BootingcheckedMode="1" />
</Privisions>
	 
	<Cwmps> 
			<cwmp CwmpConfigFlag="0" CwmpEveryReboot="0"
SerialNumber="00100400YJ012050000000268b004129"
CwmpHost="http://183.62.12.23:8012/service.tr069" Port="8012" username=""
password="" protocol="1" Periodic="0" freqency="3600" />
	 </Cwmps>
	 
	$configNetwork
	 
	<NATs> 
			<nat $pcportmode PCPortIPAddress=""
PCPortIPMask="" DHCPServerEnable="0" DHCPStartAddress=""
DHCPEndAddress="" />
	 </NATs>
	 
	<Proxys> 
			<proxy ProxyServerEnable="0"
ProxyServerPort="1080" AnonymousLogin="1" ProxyServerDomain=""
Username="" Password="" />
	 </Proxys>
	 
	<sipServers> 
			<sipserver DomainName="" ProxyServerAddress=""
SecondDomainName="" STUNAddress="" STUNEnableFlag="0"
SipRefreshTime="3600" LocalPort="5060" LinkUse="0" BeginPort="10000"
EndPort="10128" Qos="40" SubExpire="3600" AffiliatedPortEnable="1"
P-Asserted-Identity="1" />
	 </sipServers>
	 
	<syss> 
			<sys KeyboardPasswd="" KeyboardEncrypt="0"
FirstDialTime="15" MaxConnectTime="30" NoAnswerTime="70"
NoAnswerTimeEnable="1" UpgradeCheck="1" NetworkPacket="0"
PstnRingType="1" pictureType="0" startPicturePath="" idlePicturePath=""
waitPicturePath1="" waitPicturePath2="" EmbeddedMode="0"
EmbeddedFreqency="300" EmbeddedUrl="" voicemail="0" PoundSendType="0"
voicemailToneEnable="0" />
	 </syss>
	 
	<DTMEFs> 
			<DTMF DTMFSendType="0" DTMFShowSendType="0"
RFC2833CodeId="101" SIPInfoReSend="0" />
	 </DTMEFs>
	 
	<calls> 
			<call PrefixPSTNNum="" PrefixVOIPNum=""
AutoAnswerNum="123" CallLeaveMessageNum="*97" PlaycallwaitingTone="0"
AfterTurnType="0" ShowPhonelist="0" AutoAnswerEnableFlag="0"
AutoAnswerEnableGroupName="" AutoAnswerMode="0" SIPQos="26" RTPQos="46"
CallBusyTone="1" MisscallDisEnable="1" AvoidDisturbEnableFlag="0"
forkEnableFlag="1" forktime="500" Unconditional="0" UnconditionalNum=""
busytransfer="0" busytransferNum="" UnAnswer="0" UnAnswerNum=""
RingFrequency="15" PickupEnable="1" ConfEndMethod="0" />
	 </calls>
	 
	<SysTimes> 
			<systime
SNTPAddress="sparky.services.adelaide.edu.au" DaylightSaveingFlag="0"
SNTPEnableFlag="1" $timezone DaySet="1" HourSet="0" MinuteSet="0"
SecondSet="0" YearSet="2000" MouthSet="1" AutoAddress="0" DSTtype="0"
DSTStartMonth="1" DSTStartDay="1" DSTStartHour="0" DSTEndMonth="12"
DSTEndDay="31" DSTEndHour="23" DSTWeekStartMonth="1" DSTWeekStartWeek="0"
DSTWeekStartInMonth="1" DSTWeekStartHour="0" DSTWeekEndMonth="12"
DSTWeekEndWeek="6" DSTWeekEndInMonth="5" DSTWeekEndHour="23" Offset="60" />
	 </SysTimes>
	 
	<FunCodes> 
			<CallFun CallBackFC="" CallWaitFC=""
TransferCodeEnable="0" TransferCodeNum="" />
	 </FunCodes>
	 
	<RouteGoals> 
			<route DialNumLength="25" DialOutButton="1"
DialOutFlag="0" DialOutMaxTime="5" />
	 </RouteGoals>
	 
	<RouteTabs />
	 
	<BlackTabs />
	 
	<logs> 
			<log SysLogAddres="" LogCallGrade="0" LogSave="0"
LogType="1" />
	 </logs>
	 
	<hotlineBLFs> 
			<hotlineBLF BLF="1" />
	 </hotlineBLFs>
	 
	<lcds> 
			<lcd Password="" AvoidDisturbEnableFlag="0"
Language="1" WebLanguage="1" Light="3" NoSoundEnableFlag="0"
ScreenLightEnableFlag="1" BackLightEnableFlag="0" TimeFormat="0"
TimeListSeparator="0" TouchScreen="0" CloseLightTime="60" ScreenTime="60"
Contrast="3" ScreenSaverEnable="0" lockScreenPassword="1234"
LockScreenEnable="0" LockScreenMode="0" LockScreenTime="60" />
	 </lcds>
	 
	<passwords> 
			<password UsrName="root" Password="root"
User="user" UserPassword="user" type="0" />
	 </passwords>
	 
	<HotLineFuns> 
			<HotLineFun HotLineState="0" HotLineNum="" />
	 </HotLineFuns>
	 
	<LDAPs> 
			<ldap id="0" LDAPName="" LDAPNumber=""
Address="0.0.0.0" Com="389" Base="" UserName="" Password="" MaxHit="50"
Name_Attribites1="" Name_Attribites2="" Name_Attribites3=""
Number_Attribites1="" Number_Attribites2="" Number_Attribites3=""
Protocol="3" Deslay="0" call="1" Results="1" PreDial_Dial="0"
LDAPEnable="0" />
	 
			<ldap id="1" LDAPName="" LDAPNumber=""
Address="0.0.0.0" Com="389" Base="" UserName="" Password="" MaxHit="50"
Name_Attribites1="" Name_Attribites2="" Name_Attribites3=""
Number_Attribites1="" Number_Attribites2="" Number_Attribites3=""
Protocol="3" Deslay="0" call="1" Results="1" PreDial_Dial="0"
LDAPEnable="0" />
	 </LDAPs>
	 
	<sipUsers> 
			<sipUser id="0" Describe=""
DomainName="$ipAdressServer" Password="$secret" ProxyServerAddress=""
SecondProxyServerAddress="" PollingRegistrationTime="32"
RefreshTime="3600" Subscribe="1800" SecondDomainName="" STUNAddress=""
STUNEnableFlag="0" UserName="$id_device" UserNumber="$id_device" flag="0" LinkUse="0"
PossessNumber="1" SupportNumber="8" approveName="$id_device" RTPBegin="10000"
RTPEND="10128" EnableAccount="1" AccountMode="0" RegisterMethod="0"
BLAEnableFlag="0" BLANum="" AnonymousCall="0" UserSessionTimerEnable="0"
SessionTimer="300" AllowEventsEnable="0" DNSSRVEnable="0"
RegisteredNAT="1" />
	 
			<sipUser id="1" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="2" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="3" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="4" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="5" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="6" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 
			<sipUser id="7" Describe="" DomainName=""
Password="" ProxyServerAddress="" SecondProxyServerAddress=""
PollingRegistrationTime="32" RefreshTime="3600" Subscribe="1800"
SecondDomainName="" STUNAddress="" STUNEnableFlag="0" UserName=""
UserNumber="" flag="0" LinkUse="0" PossessNumber="2" SupportNumber="8"
approveName="" RTPBegin="10000" RTPEND="10128" EnableAccount="0"
AccountMode="0" RegisterMethod="0" BLAEnableFlag="0" BLANum=""
AnonymousCall="0" UserSessionTimerEnable="0" SessionTimer="300"
AllowEventsEnable="0" DNSSRVEnable="0" RegisteredNAT="1" />
	 </sipUsers>
	 
	<SipEncrys> 
			<SipEncry id="0" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="1" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="2" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="3" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="4" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="5" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="6" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 
			<SipEncry id="7" EncryptionNum=""
EncryptionType="0" FaxEncryption="0" RTPEncryption="0"
SignalingEncryption="0" />
	 </SipEncrys>
	 
	<modes> 
			<mode Headphonesmode="0" RingMode="0" />
	 </modes>
	 
	<VPNs> 
			<VPN VPNUserName="" VPNUserPassword=""
VPNServer="" Enable="0" Type="0" />
	 </VPNs>
	 
	<VLANs> 
			<Vlan EnableVlan="0" LocalEnableVlan="0"
PCEnableVlan="0" LocalVID="0" PCVID="0" LocalPriority="0" PCPriority="0" />
	 </VLANs>
</all>

TEMP;
   

    return $content;
}
function arrEscene($ipAdressServer, $macAdress)
{
    $arrEscene = array(
	"cd /mnt/sip"=>"",
	"tftp -g $ipAdressServer -r $macAdress.xml"  => "",
	"mv $macAdress.xml ESConfig.xml" => "",
	
    );
    return $arrEscene;
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

