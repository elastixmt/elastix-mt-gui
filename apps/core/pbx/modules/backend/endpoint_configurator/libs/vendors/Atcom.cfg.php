<?php
function templatesFileAtcom($ipAdressServer)
{
    $content= <<<TEMP
<<VOIP CONFIG FILE>>Version:2.0002                            

<GLOBAL CONFIG MODULE>
Static IP          :
Static NetMask     :
Static GateWay     :
Default Protocol   :2
Primary DNS        :
Alter DNS          :
DHCP Mode          :0
DHCP Dns           :1
Domain Name        :
Host Name          :VOIP
Pppoe Mode         :0
HTL Start Port     :10000
HTL Port Number    :200
SNTP Server        :$ipAdressServer
Enable SNTP        :1
Time Zone          :12
Enable Daylight    :0
SNTP Time Out      :60
MMI Set            :1

<LAN CONFIG MODULE>
Lan Ip             :192.168.10.1
Lan NetMask        :255.255.255.0
Bridge Mode        :1

<TELE CONFIG MODULE>
Dial End With #    :1
Dial Fixed Length  :0
Fixed Length       :11
Dial With Timeout  :1
Dial Timeout value :5
Poll Sequence      :0
Accept Any Call    :1
Phone Prefix       :
Local Area Code    :
IP call network    :.
--Port Config--    :
P1 No Disturb      :0
P1 No Dial Out     :0
P1 No Empty Calling:0
P1 Enable CallerId :1
P1 Forward Service :0
P1 SIP TransNum    :
P1 SIP TransAddr   :
P1 SIP TransPort   :5060
P1 CallWaiting     :1
P1 CallTransfer    :1
P1 Call3Way        :1
P1 AutoAnswer      :0
P1 No Answer Time  :20
P1 Extention No.   :
P1 Hotline Num     :
P1 Record Server   :
P1 Enable Record   :0
P1 Busy N/A Line   :0

<DSP CONFIG MODULE>
Signal Standard    :3
Handdown Time      :200
G729 Payload Length:1
VAD                :0
Ring Type          :1
Dtmf Payload Type  :101
Disable Handfree   :0
--Port Config--    :
P1 Output Vol      :7
P1 Input Vol       :3
P1 HandFree Vol    :4
P1 RingTone Vol    :5
P1 Codec           :1
P1 Voice Record    :0
P1 Record Playing  :1
P1 UserDef Voice   :0

<SIP CONFIG MODULE>
SIP  Port          :5060
Stun Address       :
Stun Port          :3478
Stun Effect Time   :50
SIP  Differv       :0
DTMF Mode          :1
Extern Address     :
Url Convert        :0
--SIP Line List--  :
SIP1 Phone Number  :
SIP1 Display Name  :
SIP1 Register Addr :$ipAdressServer
SIP1 Register Port :5060
SIP1 Register User :
SIP1 Register Pwd  :
SIP1 Register TTL  :60
SIP1 Enable Reg    :1
SIP1 Proxy Addr    :$ipAdressServer
SIP1 Proxy Port    :5060
SIP1 Proxy User    :
SIP1 Proxy Pwd     :
SIP1 Signal Enc    :0
SIP1 Signal Key    :
SIP1 Media Enc     :0
SIP1 Media Key     :
SIP1 Local Domain  :
SIP1 Fwd Service   :0
SIP1 Fwd Number    :
SIP1 Enable Detect :0
SIP1 Detect TTL    :60
SIP1 Server Type   :0
SIP1 User Agent    :Voip Phone 1.0
SIP1 PRACK         :0
SIP1 KEEP AUTH     :0
SIP1 Session Timer :0
SIP1 DTMF Mode     :1
SIP1 Use Stun      :0
SIP1 Via Port      :1
SIP1 Subscribe     :0
SIP1 Sub Expire    :300
SIP1 Single Codec  :0
SIP1 CLIR          :0
SIP1 RFC Ver       :1
SIP1 Use Mixer     :0
SIP1 Mixer Uri     :
SIP2 Phone Number  :
SIP2 Display Name  :
SIP2 Register Addr :
SIP2 Register Port :5060
SIP2 Register User :
SIP2 Register Pwd  :
SIP2 Register TTL  :60
SIP2 Enable Reg    :0
SIP2 Proxy Addr    :
SIP2 Proxy Port    :5060
SIP2 Proxy User    :
SIP2 Proxy Pwd     :
SIP2 Signal Enc    :0
SIP2 Signal Key    :
SIP2 Media Enc     :0
SIP2 Media Key     :
SIP2 Local Domain  :
SIP2 Fwd Service   :0
SIP2 Fwd Number    :
SIP2 Enable Detect :0
SIP2 Detect TTL    :60
SIP2 Server Type   :0
SIP2 User Agent    :Voip Phone 1.0
SIP2 PRACK         :0
SIP2 KEEP AUTH     :0
SIP2 Session Timer :0
SIP2 DTMF Mode     :1
SIP2 Use Stun      :0
SIP2 Via Port      :1
SIP2 Subscribe     :0
SIP2 Sub Expire    :300
SIP2 Single Codec  :0
SIP2 CLIR          :0
SIP2 RFC Ver       :1
SIP2 Use Mixer     :0
SIP2 Mixer Uri     :

<IAX2 CONFIG MODULE>
Server   Address   :
Server   Port      :4569
User     Name      :
User     Password  :
User     Number    :
Voice    Number    :0
Voice    Text      :mail
EchoTest Number    :1
EchoTest Text      :echo
Local    Port      :4569
Enable   Register  :0
Refresh  Time      :60
Enable   G.729     :0

<PPPoE CONFIG MODULE>
Pppoe User         :user123
Pppoe Password     :password
Pppoe Service      :ANY
Pppoe Ip Address   :

<MMI CONFIG MODULE>
Telnet Port        :23
Web Port           :80
Remote Control     :1
Enable MMI Filter  :0
Telnet Prompt      :
--MMI Account--    :
Account1 Name      :admin
Account1 Pass      :admin
Account1 Level     :10
Account2 Name      :guest
Account2 Pass      :guest
Account2 Level     :5

<QOS CONFIG MODULE>
Enable VLAN        :0
Enable diffServ    :0
DiffServ Value     :184
VLAN ID            :256
802.1P Value       :0
VLAN Recv Check    :1
Data VLAN ID       :254
Data 802.1P Value  :0
Diff Data Voice    :0

<DEBUG CONFIG MODULE>
MGR Trace Level    :0
SIP Trace Level    :0
IAX Trace Level    :0
Trace File Info    :0

<AAA CONFIG MODULE>
Enable Syslog      :0
Syslog address     :0.0.0.0
Syslog port        :514

<ACCESS CONFIG MODULE>
Enable In Access   :0
Enable Out Access  :0

<DHCP CONFIG MODULE>
Enable DHCP Server :1
Enable DNS Relay   :1
DHCP Update Flag   :0
TFTP  Server       :0.0.0.0
--DHCP List--      :
Item1 name         :lan
Item1 Start Ip     :192.168.10.1
Item1 End Ip       :192.168.10.30
Item1 Param        :snmk=255.255.255.0:maxl=1440:rout=192.168.10.1:dnsv=192.168.10.1

<NAT CONFIG MODULE>
Enable Nat         :1
Enable Ftp ALG     :1
Enable H323 ALG    :0
Enable PPTP ALG    :1
Enable IPSec ALG   :1

<PHONE CONFIG MODULE>
Keypad Password    :123
LCD Logo           :VOIP PHONE
Time 12hours       :0
Memory Key 1       :
Memory Key 2       :
Memory Key 3       :
Memory Key 4       :
Memory Key 5       :
Memory Key 6       :
Memory Key 7       :
Memory Key 8       :
Memory Key 9       :
Memory Key 10      :

<AUTOUPDATE CONFIG MODULE>
Download Username  :user
Download password  :pass
Download Server IP :$ipAdressServer
Config File Name   :atc\$macAdress.cfg
Config File Key    :
Download Protocol  :2
Download Mode      :1
Download Interval  :1

<VPN CONFIG MODULE>
VPN mode           :0
L2TP LNS IP        :
L2TP User Name     :
L2TP Password      :
Enable VPN Tunnel  :0
VPN Server IP      :0.0.0.0
VPN Server Port    :80
Server Group ID    :VPN
Server Area Code   :12345
<<END OF FILE>>
TEMP;

    return $content;
}

function PrincipalFileAtcom320IAX($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{
$arrAtcom320 = array(
//***************Network Settings***************
//"set iptype"    => "1", //dhcp
// "set vlan"      => "0", //disable

//***************Audio Settings***************
"set codec1"    => "2", //g711u
// "set codec2"    => "6", //null
// "set codec3"    => "6", //null
// "set codec4"    => "6", //null
// "set codec5"    => "6", //null
// "set codec6"    => "6", //null
// "set vad"           => "0", //disable
// "set agc"           => "0", //disable
// "set aec"           => "1", //enable
// "set audioframes"   => "2",
// "set 6.3k"          => "1", //enable
// "set ilbcpayload"   => "97",
// "set jittersize"    => "0",
// "set handsetin"     => "7",
// "set handsetout"    => "20",
"set ringtype"      => "2", //user define
// "set speakerout"    => "31",
// "set speakerin"     => "15",

//***************Dial Plan Settings***************
// "set dialplan"  => "0", //disable
// "set innerline"     => "0", //disable
// "set callwaiting"   => "0", //disable
// "set fwdpoweroff"   => "0", //disable
// "set fwdalways"     => "0", //disable
// "set fwdbusy"   => "0", //disable
// "set fwdnoanswer"   => "0", //disable
// "set digitmap"  => "0", //disable

//***************Protocol Settings***************
"set service"       => "1", //enable
// "set registerttl"   => "60",
"set serviceaddr"   => $ipAdressServer,
"set phonenumber"   => $id_device,
"set account"       => $id_device,
"set pin"           => $secret,
"set localport"     => "4569",
// "set tos"           => "0",

//***************Other Settings***************
// "set superpassword" => "12345678",
// "set debug"         => "0", //disable
// "set password"      => "1234",
// "set upgradetype"   => "0", //disable
// "set upgradeaddr"   => "empty",
"set sntpip"        => $ipAdressServer,
// "set daylight"      => "0", //disable
// "set timezone"      => "55", //(GMT+07:00)Bangkok,Jakarta,Hanoi
//***************Save Settings***************
"write"         => "",
);

    return $arrAtcom320;
}

function PrincipalFileAtcom320SIP($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{
$arrAtcom320 = array(
//***************Network Settings***************
//"set iptype"    => "1", //dhcp
// "set vlan"      => "0", //disable

//***************Audio Settings***************
"set codec1"    => "2", //g711u
// "set codec2"    => "6", //null
// "set codec3"    => "6", //null
// "set codec4"    => "6", //null
// "set codec5"    => "6", //null
// "set codec6"    => "6", //null
// "set vad"           => "0", //disable
// "set agc"           => "0", //disable
// "set aec"           => "1", //enable
// "set audioframes"   => "2",
// "set 6.3k"          => "1", //enable
// "set ilbcpayload"   => "97",
// "set jittersize"    => "0",
// "set handsetin"     => "7",
// "set handsetout"    => "20",
"set ringtype"      => "2", //user define
// "set speakerout"    => "31",
// "set speakerin"     => "15",

//***************Dial Plan Settings***************
// "set dialplan"  => "0", //disable
// "set innerline"     => "0", //disable
// "set callwaiting"   => "0", //disable
// "set fwdpoweroff"   => "0", //disable
// "set fwdalways"     => "0", //disable
// "set fwdbusy"   => "0", //disable
// "set fwdnoanswer"   => "0", //disable
// "set digitmap"  => "0", //disable

//***************Protocol Settings***************
"set service"       => "1", //enable
// "set registerttl"   => "60",
"set servicetype"   => "13", //sipphone
"set sipproxy"      => $ipAdressServer,
"set domain"        => $ipAdressServer,
// "set nattraversal"  => "0", //disable
// "set nataddr"       => "empty",
// "set natttl"        => "30",
"set phonenumber"   => $id_device,
"set account"       => $id_device,
"set pin"           => $secret,
// "set registerport"  => "1720",
// "set rtpport"       => "1722",
// "set tos"           => "0",
// "set dtmfpayload"   => "101",
"set dtmf"          => "1", //rfc2833
// "set prack"         => "0", //disable
"set outboundproxy" => "1", //enable

//***************Other Settings***************
// "set superpassword" => "12345678",
// "set debug"         => "0", //disable
// "set password"      => "1234",
// "set upgradetype"   => "0", //disable
// "set upgradeaddr"   => "empty",
"set sntpip"        => $ipAdressServer,
// "set daylight"      => "0", //disable
// "set timezone"      => "55", //(GMT+07:00)Bangkok,Jakarta,Hanoi
//***************Save Settings***************
"write"         => "",
);

    return $arrAtcom320;
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

function PrincipalFileAtcom530SIP($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer, $macAdress, $versionCfg)
{
   $versionCfg = isset($versionCfg)?$versionCfg:'2.0002';
   $configNetwork ="";
   $ByDHCP = existsValue($arrParameters,'By_DHCP',1);
   // 1 indica que es por DHCP y 0 por estatico

   if($ByDHCP==0){ // 0 es IP Estatica
        $configNetwork ="
Static IP          :".existsValue($arrParameters,'IP','')."
Static NetMask     :".existsValue($arrParameters,'Mask','')."
Static GateWay     :".existsValue($arrParameters,'GW','')."
Primary DNS        :".existsValue($arrParameters,'DNS1','')."
Secundary DNS      :".existsValue($arrParameters,'DNS2','')."
";
   }

# CONTENT
   $content=
"<<VOIP CONFIG FILE>>Version:$versionCfg                         

<GLOBAL CONFIG MODULE>
SNTP Server        :$ipAdressServer
Enable SNTP        :1
$configNetwork
DHCP Mode          :$ByDHCP
Time Zone          :".existsValue($arrParameters,'Time_Zone',12)."

<LAN CONFIG MODULE>
Bridge Mode        :".existsValue($arrParameters,'Bridge',1)."


<TELE CONFIG MODULE>
Dial End With #    :1
Dial Fixed Length  :0
Fixed Length       :11
Dial With Timeout  :1
Dial Timeout value :5

<DSP CONFIG MODULE>
VAD                :0
Ring Type          :1
--Port Config--    :
P1 Codec           :1

<SIP CONFIG MODULE>
SIP  Port          :5060
Stun Address       :
Stun Port          :3478
Stun Effect Time   :50
SIP  Differv       :0
DTMF Mode          :1
Extern Address     :
Url Convert        :0
--SIP Line List--  :
SIP1 Phone Number  :$id_device
SIP1 Display Name  :$DisplayName
SIP1 Register Addr :$ipAdressServer
SIP1 Register Port :5060
SIP1 Register User :$id_device
SIP1 Register Pwd  :$secret
SIP1 Register TTL  :60
SIP1 Enable Reg    :1
SIP1 Proxy Addr    :$ipAdressServer
SIP1 Proxy Port    :5060
SIP1 Proxy User    :$id_device
SIP1 Proxy Pwd     :$secret
SIP1 Signal Enc    :0
SIP1 Signal Key    :
SIP1 Media Enc     :0
SIP1 Media Key     :
SIP1 Local Domain  :
SIP1 Fwd Service   :0
SIP1 Fwd Number    :
SIP1 Enable Detect :0
SIP1 Detect TTL    :60
SIP1 Server Type   :0
SIP1 User Agent    :Voip Phone 1.0
SIP1 PRACK         :0
SIP1 KEEP AUTH     :0
SIP1 Session Timer :0
SIP1 DTMF Mode     :1
SIP1 Use Stun      :0
SIP1 Via Port      :1
SIP1 Subscribe     :0
SIP1 Sub Expire    :300
SIP1 Single Codec  :0
SIP1 CLIR          :0
SIP1 RFC Ver       :1
SIP1 Use Mixer     :0
SIP1 Mixer Uri     :

<AUTOUPDATE CONFIG MODULE>
Download Username  :user
Download password  :pass
Download Server IP :$ipAdressServer
Config File Name   :atc$macAdress.cfg
Config File Key    :
Download Protocol  :2
Download Mode      :1
Download Interval  :1
<<END OF FILE>>";

    return $content;
}

function PrincipalFileAtcom530IAX($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer, $macAdress, $versionCfg)
{
   $versionCfg = isset($versionCfg)?$versionCfg:'2.0002';
   $configNetwork ="";
   $ByDHCP = existsValue($arrParameters,'By_DHCP',1);
   // 1 indica que es por DHCP y 0 por estatico

   if($ByDHCP==0){ // 0 es IP Estatica
        $configNetwork ="
	Static IP          :".existsValue($arrParameters,'IP','')."
	Static NetMask     :".existsValue($arrParameters,'Mask','')."
	Static GateWay     :".existsValue($arrParameters,'GW','')."
	Primary DNS        :".existsValue($arrParameters,'DNS1','')."
	Secundary DNS      :".existsValue($arrParameters,'DNS2','')."
	";
   }
  
   $content=
"<<VOIP CONFIG FILE>>Version:$versionCfg                         

<GLOBAL CONFIG MODULE>
SNTP Server        :$ipAdressServer
Enable SNTP        :1
$configNetwork 
DHCP Mode          :$ByDHCP
Time Zone          :".existsValue($arrParameters,'Time_Zone',12)."

<LAN CONFIG MODULE>
Bridge Mode        :".existsValue($arrParameters,'Bridge',0)."

<TELE CONFIG MODULE>
Dial End With #    :1
Dial Fixed Length  :0
Fixed Length       :11
Dial With Timeout  :1
Dial Timeout value :5

<DSP CONFIG MODULE>
VAD                :0
Ring Type          :1
--Port Config--    :
P1 Codec           :1

<IAX2 CONFIG MODULE>
Server   Address   :$ipAdressServer
Server   Port      :4569
User     Name      :$id_device
User     Password  :$secret
User     Number    :$id_device
Voice    Number    :0
Voice    Text      :mail
EchoTest Number    :1
EchoTest Text      :echo
Local    Port      :4569
Enable   Register  :1
Refresh  Time      :60
Enable   G.729     :0

<AUTOUPDATE CONFIG MODULE>
Download Username  :user
Download password  :pass
Download Server IP :$ipAdressServer
Config File Name   :atc$macAdress.cfg
Config File Key    :
Download Protocol  :2
Download Mode      :1
Download Interval  :1
<<END OF FILE>>";

    return $content;
}

function arrAtcom530($ipAdressServer, $macAdress)
{
    $arrAtcom530 = array(
        "download"  => "tftp -ip $ipAdressServer -file atc$macAdress.cfg",
        "save"      => "",
        "reload"    => "",
   );

    return $arrAtcom530;
}

function getVersionConfigFileATCOM($ip, $user, $passwd)
{
    $nonce = getNonceATCOM($ip);

    usleep(500000);
    $fileC = getConfigFileATCOM($ip,$nonce,$user,$passwd,true);

    usleep(500000);
    $logou = logoutATCOM($ip,$nonce);

    return $fileC;
}

function getNonceATCOM($ip)
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

function logoutATCOM($ip, $nonce)
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

function getConfigFileATCOM($ip, $nonce, $user, $passwd, $getOnlyVersion = false)
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
?>
