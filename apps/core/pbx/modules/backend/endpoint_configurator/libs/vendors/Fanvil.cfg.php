<?php 
function templatesFileFanvil($ipAdressServer)
{
    $content= <<<TEMP
<<VOIP CONFIG FILE>>Version:2.0002                            

<DSP CONFIG MODULE>
Signal Standard    :11
Onhook Time        :200
G729 Payload Len   :1
G723 Bit Rate      :1
G722 Timestamps    :0
VAD                :0
Ring Type          :1
Dtmf Payload Type  :101
RTP Probe          :0
--Port Config--    :
P1 General Spk Vol :5
P1 General Mic Vol :3
P1 Headset Vol     :5
P1 Ring in Headset :0
P1 HandFree Vol    :5
P1 RingTone Vol    :5
P1 Voice Codec1    :0
P1 Voice Codec2    :1
P1 Voice Codec3    :15
P1 Voice Codec4    :9
P1 Voice Codec5    :23
P1 Voice Codec6    :17

<SIP CONFIG MODULE>
SIP  Port          :5060
STUN Server        :
STUN Port          :3478
STUN Refresh Time  :50
SIP Wait Stun Time :800
Extern NAT Addrs   :
Reg Fail Interval  :32
Strict BranchPrefix:0
Video Mute Attr    :0
Enable Group Backup:0
--SIP Line List--  :
SIP1 Phone Number  :
SIP1 Display Name  :
SIP1 Sip Name      :
SIP1 Register Addr :
SIP1 Register Port :5060
SIP1 Register User :
SIP1 Register Pswd :
SIP1 Register TTL  :3600
SIP1 Enable Reg    :0
SIP1 Proxy Addr    :$ipAdressServer
SIP1 Proxy Port    :5060
SIP1 BakProxy Addr :
SIP1 BakProxy Port :5060
SIP1 Signal Crypto :0
SIP1 SigCrypto Key :
SIP1 Media Crypto  :0
SIP1 MedCrypto Key :
SIP1 SRTP Auth-Tag :0
SIP1 Local Domain  :
SIP1 FWD Type      :0
SIP1 FWD Number    :
SIP1 FWD Timer     :60
SIP1 Ring Type     :0
SIP1 Hotline Num   :
SIP1 Enable Hotline:0
SIP1 WarmLine Time :0
SIP1 NAT UDPUpdate :1
SIP1 UDPUpdate TTL :60
SIP1 Server Type   :0
SIP1 User Agent    :
SIP1 PRACK         :0
SIP1 Keep AUTH     :0
SIP1 Session Timer :0
SIP1 S.Timer Expire:0
SIP1 Enable GRUU   :0
SIP1 DTMF Mode     :1
SIP1 DTMF Info Mode:0
SIP1 NAT Type      :0
SIP1 Enable Rport  :0
SIP1 Subscribe     :0
SIP1 Sub Expire    :3600
SIP1 Single Codec  :0
SIP1 CLIR          :0
SIP1 Strict Proxy  :0
SIP1 Direct Contact:0
SIP1 History Info  :0
SIP1 DNS SRV       :0
SIP1 XFER Expire   :0
SIP1 Ban Anonymous :0
SIP1 Dial Off Line :0
SIP1 Quota Name    :0
SIP1 Presence Mode :0
SIP1 RFC Ver       :1
SIP1 Signal Port   :0
SIP1 Transport     :0
SIP1 Use SRV Mixer :0
SIP1 SRV Mixer Uri :
SIP1 Long Contact  :0
SIP1 Auto TCP      :0
SIP1 Uri Escaped   :1
SIP1 Click to Talk :0
SIP1 MWI Num       :*97
SIP1 CallPark Num  :
SIP1 MSRPHelp Num  :
SIP1 User Is Phone :1
SIP1 Auto Answer   :0
SIP1 NoAnswerTime  :60
SIP1 MissedCallLog :1
SIP1 SvcCode Mode  :0
SIP1 DNDOn SvcCode :
SIP1 DNDOff SvcCode:
SIP1 CFUOn SvcCode :
SIP1 CFUOff SvcCode:
SIP1 CFBOn SvcCode :
SIP1 CFBOff SvcCode:
SIP1 CFNOn SvcCode :
SIP1 CFNOff SvcCode:
SIP1 ANCOn SvcCode :
SIP1 ANCOff SvcCode:
SIP1 VoiceCodecMap :G711A,G711U,G722,G723,G726-32,G729
SIP1 BLFList Uri   :
SIP1 Enable BLFList:0
SIP1 Caller Id Type:1


<IAX2 CONFIG MODULE>
Server Address     :$ipAdressServer
Server Port        :4569
User Name          :
User Password      :
User Number        :
Voice Number       :*97
Voice Text         :*97
EchoTest Number    :1
EchoTest Text      :echo
Local Port         :4569
Enable Register    :0
Refresh Time       :60
Enable G.729       :0
<DEBUG CONFIG MODULE>
MGR Trace Level    :0
SIP Trace Level    :0
IAX Trace Level    :0
Trace File Info    :0


<AUTOUPDATE CONFIG MODULE>
Download Username  :user
Download Password  :pass
Download Server IP :$ipAdressServer
Config File Name   :mac.cfg
Config File Key    :
Common Cfg File Key:
Download Protocol  :2
Download Mode      :1
Download Interval  :1
DHCP Option        :66
PNP Enable         :0
<<END OF FILE>>
TEMP;

    return $content;
}


function PrincipalFileFanvilC62SIP($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer, $macAdress, $versionCfg)
{
 $versionCfg = isset($versionCfg)?$versionCfg:'2.0002';
 $configNetwork ="";
 $ByDHCP = existsValue($arrParameters,'By_DHCP',"");
   // 1 indica que es por DHCP y 0 por estatico

if($ByDHCP==="0"){ // 0 es IP Estatica
$configNetwork ="
WAN Mode:	   :STATIC
WAN IP             :".existsValue($arrParameters,'IP','')."
WAN Subnet Mask    :".existsValue($arrParameters,'Mask','')."
WAN Gateway        :".existsValue($arrParameters,'GW','')."
Primary DNS        :".existsValue($arrParameters,'DNS1','')."
Secondary DNS      :".existsValue($arrParameters,'DNS2','')."
Enable DHCP        :$ByDHCP
";
}
elseif($ByDHCP==="1"){ // 0 es IP Estatica
$configNetwork ="
WAN Mode:	   :DHCP
Enable DHCP        :$ByDHCP
";
}
else{ 
$configNetwork = "";
}

# CONTENT
   $content= "<<VOIP CONFIG FILE>>Version:$versionCfg                           

<GLOBAL CONFIG MODULE>
$configNetwork

Default Protocol   :2

Time Zone          :".existsValue($arrParameters,'Time_Zone',12)."

<LAN CONFIG MODULE>
Enable Bridge Mode :".existsValue($arrParameters,'Bridge',1)."

<DSP CONFIG MODULE>
Signal Standard    :11
Onhook Time        :200
G729 Payload Len   :1
G723 Bit Rate      :1
G722 Timestamps    :0
VAD                :0
Ring Type          :1
Dtmf Payload Type  :101
RTP Probe          :0
--Port Config--    :
P1 General Spk Vol :5
P1 General Mic Vol :3
P1 Headset Vol     :5
P1 Ring in Headset :0
P1 HandFree Vol    :5
P1 RingTone Vol    :5
P1 Voice Codec1    :0
P1 Voice Codec2    :1
P1 Voice Codec3    :15
P1 Voice Codec4    :9
P1 Voice Codec5    :23
P1 Voice Codec6    :17

<SIP CONFIG MODULE>
SIP  Port          :5060
STUN Server        :
STUN Port          :3478
STUN Refresh Time  :50
SIP Wait Stun Time :800
Extern NAT Addrs   :
Reg Fail Interval  :32
Strict BranchPrefix:0
Video Mute Attr    :0
Enable Group Backup:0
--SIP Line List--  :
SIP1 Phone Number  :$id_device
SIP1 Display Name  :$DisplayName
SIP1 Sip Name      :$id_device
SIP1 Register Addr :$ipAdressServer
SIP1 Register Port :5060
SIP1 Register User :$id_device
SIP1 Register Pswd :$secret
SIP1 Register TTL  :3600
SIP1 Enable Reg    :1
SIP1 Proxy Addr    :$ipAdressServer
SIP1 Proxy Port    :5060
SIP1 BakProxy Addr :
SIP1 BakProxy Port :5060
SIP1 Signal Crypto :0
SIP1 SigCrypto Key :
SIP1 Media Crypto  :0
SIP1 MedCrypto Key :
SIP1 SRTP Auth-Tag :0
SIP1 Local Domain  :
SIP1 FWD Type      :0
SIP1 FWD Number    :
SIP1 FWD Timer     :60
SIP1 Ring Type     :0
SIP1 Hotline Num   :
SIP1 Enable Hotline:0
SIP1 WarmLine Time :0
SIP1 NAT UDPUpdate :1
SIP1 UDPUpdate TTL :60
SIP1 Server Type   :0
SIP1 User Agent    :
SIP1 PRACK         :0
SIP1 Keep AUTH     :0
SIP1 Session Timer :0
SIP1 S.Timer Expire:0
SIP1 Enable GRUU   :0
SIP1 DTMF Mode     :1
SIP1 DTMF Info Mode:0
SIP1 NAT Type      :0
SIP1 Enable Rport  :0
SIP1 Subscribe     :0
SIP1 Sub Expire    :3600
SIP1 Single Codec  :0
SIP1 CLIR          :0
SIP1 Strict Proxy  :0
SIP1 Direct Contact:0
SIP1 History Info  :0
SIP1 DNS SRV       :0
SIP1 XFER Expire   :0
SIP1 Ban Anonymous :0
SIP1 Dial Off Line :0
SIP1 Quota Name    :0
SIP1 Presence Mode :0
SIP1 RFC Ver       :1
SIP1 Signal Port   :0
SIP1 Transport     :0
SIP1 Use SRV Mixer :0
SIP1 SRV Mixer Uri :
SIP1 Long Contact  :0
SIP1 Auto TCP      :0
SIP1 Uri Escaped   :1
SIP1 Click to Talk :0
SIP1 MWI Num       :*97
SIP1 CallPark Num  :
SIP1 MSRPHelp Num  :
SIP1 User Is Phone :1
SIP1 Auto Answer   :0
SIP1 NoAnswerTime  :60
SIP1 MissedCallLog :1
SIP1 SvcCode Mode  :0
SIP1 DNDOn SvcCode :
SIP1 DNDOff SvcCode:
SIP1 CFUOn SvcCode :
SIP1 CFUOff SvcCode:
SIP1 CFBOn SvcCode :
SIP1 CFBOff SvcCode:
SIP1 CFNOn SvcCode :
SIP1 CFNOff SvcCode:
SIP1 ANCOn SvcCode :
SIP1 ANCOff SvcCode:
SIP1 VoiceCodecMap :G711A,G711U,G722,G723,G726-32,G729
SIP1 BLFList Uri   :
SIP1 Enable BLFList:0
SIP1 Caller Id Type:1

SIP2 Enable Reg    :0
SIP3 Enable Reg    :0
SIP4 Enable Reg    :0

<IAX2 CONFIG MODULE>
Server Address     :
Server Port        :4569
User Name          :
User Password      :
User Number        :
Voice Number       :*97
Voice Text         :*97
EchoTest Number    :1
EchoTest Text      :echo
Local Port         :4569
Enable Register    :0
Refresh Time       :60
Enable G.729       :0

<DHCP CONFIG MODULE>
DHCP Server Type   :0

<AUTOUPDATE CONFIG MODULE>
Download Server IP :$ipAdressServer
Config File Name   :$macAdress.cfg
Config File Key    :
Common Cfg File Key:
Download Protocol  :2
Download Mode      :1
Download Interval  :1
PNP Enable         :0

<<END OF FILE>>";

    return $content;
}
function PrincipalFileFanvilC62IAX($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer, $macAdress, $versionCfg)
{
 $versionCfg = isset($versionCfg)?$versionCfg:'2.0002';

 $configNetwork ="";
 $ByDHCP = existsValue($arrParameters,'By_DHCP',"");
   // 1 indica que es por DHCP y 0 por estatico

if($ByDHCP==="0"){ // 0 es IP Estatica
$configNetwork ="
WAN Mode:	   :STATIC
WAN IP             :".existsValue($arrParameters,'IP','')."
WAN Subnet Mask    :".existsValue($arrParameters,'Mask','')."
WAN Gateway        :".existsValue($arrParameters,'GW','')."
Primary DNS        :".existsValue($arrParameters,'DNS1','')."
Secondary DNS      :".existsValue($arrParameters,'DNS2','')."
Enable DHCP        :$ByDHCP
";
}
elseif($ByDHCP==="1"){ // 0 es IP Estatica
$configNetwork ="
WAN Mode:	   :DHCP
Enable DHCP        :$ByDHCP
";
}
else{ 
$configNetwork = "";
}
# CONTENT
   $content= "<<VOIP CONFIG FILE>>Version:$versionCfg                            

<PHONE CONFIG MODULE>
<GLOBAL CONFIG MODULE>
$configNetwork

Default Protocol   :2

Time Zone          :".existsValue($arrParameters,'Time_Zone',12)."
--Function Key--   :
Fkey1 Type         :2
Fkey1 Value        :IAX2

<LAN CONFIG MODULE>
Bridge Mode        :".existsValue($arrParameters,'Bridge',1)."

<DSP CONFIG MODULE>
Signal Standard    :11
Onhook Time        :200
G729 Payload Len   :1
G723 Bit Rate      :1
G722 Timestamps    :0
VAD                :0
Ring Type          :1
Dtmf Payload Type  :101
RTP Probe          :0
--Port Config--    :
P1 General Spk Vol :5
P1 General Mic Vol :3
P1 Headset Vol     :5
P1 Ring in Headset :0
P1 HandFree Vol    :5
P1 RingTone Vol    :5
P1 Voice Codec1    :0
P1 Voice Codec2    :1
P1 Voice Codec3    :15
P1 Voice Codec4    :9
P1 Voice Codec5    :23
P1 Voice Codec6    :17

<SIP CONFIG MODULE>
--SIP Line List--  :
SIP1 Phone Number  :
SIP1 Display Name  :
SIP1 Sip Name      :
SIP1 Register Addr :
SIP1 Register Port :5060
SIP1 Register User :
SIP1 Register Pswd :
SIP1 Register TTL  :3600
SIP1 Proxy Addr    :
SIP1 Proxy Port    :5060
SIP1 BakProxy Addr :
SIP1 BakProxy Port :5060
SIP1 Signal Crypto :0
SIP1 Enable Reg    :0
SIP2 Enable Reg    :0
SIP3 Enable Reg    :0
SIP4 Enable Reg    :0

<IAX2 CONFIG MODULE>
Server Address     :$ipAdressServer
Server Port        :4569
User Name          :$id_device
User Password      :$secret
User Number        :$id_device
Voice Number       :*97
Voice Text         :*97
EchoTest Number    :1
EchoTest Text      :echo
Local Port         :4569
Enable Register    :1
Refresh Time       :60
Enable G.729       :0

<DHCP CONFIG MODULE>
DHCP Server Type   :0

<AUTOUPDATE CONFIG MODULE>
Download Server IP :$ipAdressServer
Config File Name   :$macAdress.cfg
Config File Key    :
Common Cfg File Key:
Download Protocol  :2
Download Mode      :1
Download Interval  :1
PNP Enable         :0

<<END OF FILE>>";

    return $content;
}

function arrFanvil($ipAdressServer, $macAdress)
{
    $arrFanvil = array(
        "download"  => "tftp -ip $ipAdressServer -file $macAdress.cfg",
        "save"      => "",
	"reload"    => "",
    );

    return $arrFanvil;
}


function getVersionConfigFileFANVIL($ip, $user, $passwd)
{
    $nonce = getNonceFANVIL($ip);
    usleep(500000);
    $fileC = getConfigFileFANVIL($ip,$nonce,$user,$passwd,true);
    usleep(500000);
    $logou = logoutFANVIL($ip,$nonce);
    return $fileC;
}

function getVersionConfigFileFANVILC56($ip, $user, $passwd)
{
    $nonce = getNonceFANVILC56($ip);
    usleep(500000);
    $fileC = getConfigFileFANVIL($ip,$nonce,$user,$passwd,true);
    usleep(500000);
    $logou = logoutFANVIL($ip,$nonce);
    return $fileC;
}


function getNonceFANVILC56($ip)
{
    $headers = array();
    $url = "http://$ip";
    $headers = (get_headers($url, 1));
    $nonce = $headers["Set-Cookie"];
    $nonce = explode("auth=",$nonce);
    $nonce = explode(";",$nonce[1]);
    return $nonce[0];
}

function getNonceFANVIL($ip)
{
   
   $token = null;
    while(true){
        $home_html = file_get_contents("http://$ip");
	if($home_html === false) break;

        if(preg_match("/<input type=\"hidden\" name=\"nonce\" value=\"([0-9a-zA-Z]+)\">/",$home_html,$arrTokens)){
            if(isset($arrTokens[1])){
                $token = $arrTokens[1];
                break;
            }
        }
    }
    return $token;
}

function logoutFANVIL($ip, $nonce)
{
    $respuesta = null;
    $conexion  = @fsockopen($ip,80);

    if ($conexion) {
       $dataSend = "DefaultLogout=Logout";
       $dataLenght = strlen($dataSend);
       $headerRequest = createHeaderHttp($ip,"/LogOut.htm",$dataLenght,$nonce);
       fputs($conexion,$headerRequest.$dataSend);

       while(($r = fread($conexion,2048)) != "")
     	  $respuesta .= $r;
     
      fclose($conexion);
    }
    else
       echo "Error Conección $ip\n";

    return $respuesta;
}

function getConfigFileFANVIL($ip, $nonce, $user, $passwd, $getOnlyVersion = false)
{
    $respuesta = null;
    $encoded   = "$user:".md5("$user:$passwd:$nonce");
    $conexion  = @fsockopen($ip,80);

    if ($conexion) {
       $dataSend = "encoded=$encoded&nonce=$nonce&goto=Logon&URL=/";
       $dataLenght = strlen($dataSend);
       $headerRequest = createHeaderHttp($ip,"/config.txt",$dataLenght,$nonce);
       fputs($conexion,$headerRequest.$dataSend);
    
       while(($r = fread($conexion,2048)) != ""){
            if($getOnlyVersion){
                if(preg_match("/<<VOIP CONFIG FILE>>Version:([2-9]{1}\.[0-9]{4})/",$r,$arrTokens)){
                    if(isset($arrTokens[1])){
                        $respuesta = $arrTokens[1];
                        break;
                    }
                }
            }
            else
                $respuesta .= $r;
       }
       fclose($conexion);
    }
    else
       echo "Error Conección $ip\n";

    return $respuesta;
}

function createHeaderHttp($ip_remote, $file_remote, $dataLenght, $nonce)
{
    $headerRequest  = "POST $file_remote HTTP/1.0\r\n";
    $headerRequest .= "Host: $ip_remote\r\n";
    $headerRequest .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $headerRequest .= "Cookie: auth=$nonce\r\n";
    $headerRequest .= "Connection:keep-alive\r\n";
    $headerRequest .= "Content-Length: $dataLenght\r\n\r\n";
    return $headerRequest;
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
