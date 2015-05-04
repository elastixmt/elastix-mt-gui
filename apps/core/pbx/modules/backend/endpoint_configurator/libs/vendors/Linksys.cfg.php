<?php
/*
    PrincipalFileLinksys nos retorna el contenido del archivo de configuracion de los EndPoint
    Linksys, para ello es necesario enviarle el UserID, Password.
*/
function PrincipalFileLinksys($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{
    $content="
<flat-profile>
    <Resync_Periodic ua=\"na\">86400</Resync_Periodic>
    <Proxy_1_ ua=\"na\">$ipAdressServer</Proxy_1_>
    <Outbound_Proxy_1_ ua=\"na\">$ipAdressServer</Outbound_Proxy_1_>
    <Primary_NTP_Server ua=\"na\">$ipAdressServer</Primary_NTP_Server>
    <Profile_Rule ua=\"na\">tftp://$ipAdressServer/spa\$MA.cfg</Profile_Rule>
 <!-- Subscriber Information -->
    <Text_Logo group=\"Phone/General\">$DisplayName</Text_Logo>
    <Station_Name group=\"Phone/General\">$DisplayName</Station_Name>
    <Voice_Mail_Number group=\"Phone/General\"></Voice_Mail_Number>
    <Display_Name_1_ ua=\"na\">$DisplayName</Display_Name_1_>
    <Short_Name_1_ ua=\"na\">$id_device</Short_Name_1_> 
    <User_ID_1_ ua=\"na\">$id_device</User_ID_1_>
    <Password_1_ ua=\"na\">$secret</Password_1_>
 <!-- Speed Dial -->
    <Speed_Dial_2 ua=\"rw\"/>
    <Speed_Dial_3 ua=\"rw\"/>
    <Speed_Dial_4 ua=\"rw\"/>
    <Speed_Dial_5 ua=\"rw\"/>
    <Speed_Dial_6 ua=\"rw\"/>
    <Speed_Dial_7 ua=\"rw\"/>
    <Speed_Dial_8 ua=\"rw\"/>
    <Speed_Dial_9 ua=\"rw\"/>

 <!-- Additional -->
 <!-- <Time_Zone  ua=\"na\">GMT-06:00</Time_Zone> -->
   <Voice_Mail_Number  ua=\"na\">*97</Voice_Mail_Number>
   <Paging_Code ua=\"na\">*80</Paging_Code>
   <Select_Logo ua=\"ua\">BMP Picture</Select_Logo>
   <Text_Logo ua=\"na\">Linksys</Text_Logo>
   <Select_Background_Picture ua=\"ua\">BMP Picture</Select_Background_Picture>
 <!-- <BMP_Picture_Download_URL ua=\"ua\">tftp://$ipAdressServer/Linksys.bmp</BMP_Picture_Download_URL> -->
</flat-profile>";

    return $content;
}

function templatesFileLinksys($ipAdressServer)
{
    $content= <<<TEMP
<flat-profile>
  <Resync_Periodic ua="na">2</Resync_Periodic>
  <Profile_Rule ua="na">tftp://$ipAdressServer/spa\$MA.cfg</Profile_Rule>
 <!-- Proxy and Registration -->
  <Proxy_1_ ua="na">$ipAdressServer</Proxy_1_>
  <Primary_NTP_Server ua="na">$ipAdressServer</Primary_NTP_Server>
  <Voice_Mail_Number  ua="na">*97</Voice_Mail_Number>
  <Display_Name_1_ ua="na">\$USER</Display_Name_1_>
  <Dial_Plan_1_ ua="na">(**xxx|**xxxx|*xx|xxx*|xxx**|xxxx*|xxxx**[3469]11|0|00|[2-9]xxxxxx|1xxx[2-9]xxxxxxS0|xxxxxxxxxxxx.)</Dial_Plan_1_>
  <Time_Zone  ua="na">GMT-08:00</Time_Zone>
  <Text_Logo group="Phone/General">Elastix</Text_Logo> 
  <BMP_Picture_Download_URL group="Phone/General" /> 
  <Select_Logo group="Phone/General">Text Logo</Select_Logo> 
  <Select_Background_Picture group="Phone/General">Text Logo</Select_Background_Picture> 
  <!-- options: GMT-12:00/GMT-11:00/GMT-10:00/GMT-09:00/GMT-07:00/GMT-07:00/GMT-06:00/GMT-05:00/GMT-04:00/GMT-03:30/GMT-03:00/GMT-02:00/GMT-01:00/GMT/GMT+01:00/GMT+02:00/GMT+03:00/GMT+03:30/GMT+04:00/GMT+05:00/GMT+05:30/GMT+05:45/GMT+06:00/GMT+06:30/GMT+07:00/GMT+07:00/GMT+09:00/GMT+09:30/GMT+10:00/GMT+11:00/GMT+12:00/GMT+13:00 -->
  <Daylight_Saving_Time_Rule ua="na">start=3/8/7/02:0:0;end=11/1/7/02:0:0;save=1</Daylight_Saving_Time_Rule>
  <Call_Return_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Blind_Transfer_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Call_Back_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Call_Back_Deact_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Cfwd_All_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Cfwd_All_Deact_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Cfwd_Busy_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Cfwd_Busy_Deact_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Cfwd_No_Ans_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Cfwd_No_Ans_Deact_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <CW_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <CW_Deact_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <CW_Per_Call_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <CW_Per_Call_Deact_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Block_CID_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Block_CID_Deact_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Block_CID_Per_Call_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Block_CID_Per_Call_Deact_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Block_ANC_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Block_ANC_Deact_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <DND_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <DND_Deact_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Secure_All_Call_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Secure_No_Call_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Secure_One_Call_Act_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Secure_One_Call_Deact_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Paging_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Call_Park_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Call_Pickup_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Call_UnPark_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Group_Call_Pickup_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Media_Loopback_Code group="Regional/Vertical_Service_Activation_Codes" /> 
  <Referral_Services_Codes group="Regional/Vertical_Service_Activation_Codes" />
</flat-profile>
TEMP;

    return $content;
}

function conexionHTTP($ip_endpoint, $ipAdressServer, $mac_address){
    $url_resync = "http://$ip_endpoint/admin/resync?$ipAdressServer/spa{$mac_address}.cfg";
    $fp = @fopen($url_resync, 'r');
    if ($fp == false)
        return false;
    @fclose($fp);
        return true;
}
?>
