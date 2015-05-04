<?php
/*
    The function creates content of phone specific settings file for SNOM 3XX. 
*/
function PrincipalFileSnom($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{
    $content="
            <html>
                <pre>
                    user_realname1: $DisplayName
                    user_name1: $id_device
                    user_pass1: $secret
                    user_pname1: $id_device
                    user_mailbox1: $id_device
                </pre>
            </html>";

    return $content;
}

/*
    The function creates content of phone specific settings file for SNOM 3XX. 
*/
function PrincipalFileSnom821($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{
$ByDHCP = existsValue($arrParameters,'By_DHCP',""); 
$time = existsValue($arrParameters,'Time_Zone',"USA-5");
$pcportmode= existsValue($arrParameters,'Bridge',1);

if($pcportmode===1)
   $pcportmode = "on";
else
   $pcportmode = "off"; 

$timezone = <<<TEMP
<timezone perm="">$time</timezone>
TEMP;


$pcport= <<<TEMP
<wifi_ether_bridge perm="">$pcportmode</wifi_ether_bridge>
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
<ip_adr perm="RW">$ip</ip_adr>
<netmask perm="RW">$mask</netmask>
<dns_server1 perm="RW">$dns</dns_server1>
<dns_server2 perm="">$dns2</dns_server2>
<dhcp perm="">off</dhcp>
<gateway perm="RW">$gw</gateway>
TEMP;
		break;
    case 1: 
		$configNetwork= <<<TEMP
<dhcp perm="">on</dhcp>
TEMP;
		break;
}

    $content= <<<TEMP
<?xml version="1.0" encoding="utf-8"?>
<settings>
<phone-settings e="2">
<language perm="">English</language>
<phone_type perm="">snom821</phone_type>
<codec_tos perm="">160</codec_tos>
<setting_server perm="RW">tftp://$ipAdressServer</setting_server>
<subscribe_config perm="">off</subscribe_config>
<pnp_config perm="">on</pnp_config>
$configNetwork
<phone_name perm=""></phone_name>
<utc_offset perm="">-18000</utc_offset>
<ntp_server perm="RW"></ntp_server>
<lcserver1 perm=""></lcserver1>
<http_proxy perm=""></http_proxy>
<http_port perm="">80</http_port>
<http_user perm=""></http_user>
<http_pass perm=""></http_pass>
<http_scheme perm="">off</http_scheme>
<https_port perm="">443</https_port>
<webserver_type perm="">http_https</webserver_type>
<webserver_cert perm=""></webserver_cert>
<dst perm="">3600 01.04 12:00:00 10.05.07 01:00:00</dst>
$timezone
<backlight perm="">15</backlight>
<contrast perm="">12</contrast>
<sip_retry_t1 perm="">500</sip_retry_t1>
<timer_support perm="">on</timer_support>
<session_timer perm="">3600</session_timer>
<network_id_port perm=""></network_id_port>
<max_forwards perm="">70</max_forwards>
<user_phone perm="">on</user_phone>
<active_line perm="">3</active_line>
<outgoing_identity perm="">3</outgoing_identity>
<challenge_response perm="">on</challenge_response>
<tone_scheme perm="">USA</tone_scheme>
<refer_brackets perm="">off</refer_brackets>
<sip_proxy perm=""></sip_proxy>
$pcport
<auto_reboot_on_setting_change perm="">on</auto_reboot_on_setting_change>
<register_http_contact perm="">off</register_http_contact>
<cmc_feature perm="">on</cmc_feature>
<filter_registrar perm="">on</filter_registrar>
<xml_notify perm="">on</xml_notify>
<challenge_reboot perm="">off</challenge_reboot>
<challenge_checksync perm="">off</challenge_checksync>
<user_active idx="1" perm="">on</user_active>
<user_realname idx="1" perm="">$DisplayName</user_realname>
<user_name idx="1" perm="">$id_device</user_name>
<user_host idx="1" perm="">$ipAdressServer</user_host>
<user_pname idx="1" perm=""></user_pname>
<user_pass idx="1" perm="">$secret</user_pass>
<user_hash idx="1" perm=""></user_hash>
<user_q idx="1" perm="">1.0</user_q>
<user_expiry idx="1" perm="">3600</user_expiry>
<user_mailbox idx="1" perm=""></user_mailbox>
<user_srtp idx="1" perm="">off</user_srtp>
<user_symmetrical_rtp idx="1" perm="">off</user_symmetrical_rtp>
<user_ice idx="1" perm="">off</user_ice>
<user_moh idx="1" perm=""></user_moh>
<user_stream idx="1" perm=""></user_stream>
<user_idle_text idx="1" perm=""></user_idle_text>
<user_alert_info idx="1" perm=""></user_alert_info>
<user_pic idx="1" perm=""></user_pic>
<user_auto_connect idx="1" perm="">off</user_auto_connect>
<user_xml_screen_url idx="1" perm=""></user_xml_screen_url>
<user_descr_contact idx="1" perm="">on</user_descr_contact>
<cc_token idx="1" perm=""></cc_token>
<user_uid idx="1" perm="">e3a17513-4679-4121-8a55-000413457401</user_uid>
<user_sipusername_as_line idx="1" perm="">off</user_sipusername_as_line>
<user_proxy_require idx="1" perm=""></user_proxy_require>
<user_shared_line idx="1" perm="">off</user_shared_line>
<user_send_local_name idx="1" perm="">off</user_send_local_name>
<user_dp_str idx="1" perm=""></user_dp_str>
<user_dp_exp idx="1" perm=""></user_dp_exp>
<user_ringer idx="1" perm="">Ringer1</user_ringer>
<user_custom idx="1" perm=""></user_custom>
<user_outbound idx="1" perm=""></user_outbound>
<codec1_name idx="1" perm="">0</codec1_name>
<codec2_name idx="1" perm="">8</codec2_name>
<codec3_name idx="1" perm="">9</codec3_name>
<codec4_name idx="1" perm="">99</codec4_name>
<codec5_name idx="1" perm="">3</codec5_name>
<codec6_name idx="1" perm="">18</codec6_name>
<codec7_name idx="1" perm="">4</codec7_name>
<codec_size idx="1" perm="">20</codec_size>
<stun_server idx="1" perm=""></stun_server>
<stun_binding_interval idx="1" perm=""></stun_binding_interval>
<keepalive_interval idx="1" perm=""></keepalive_interval>
<record_missed_calls idx="1" perm="">on</record_missed_calls>
<ring_after_delay idx="1" perm=""></ring_after_delay>
<user_dtmf_info idx="1" perm="">off</user_dtmf_info>
<user_event_list_subscription idx="1" perm="">off</user_event_list_subscription>
<user_event_list_uri idx="1" perm=""></user_event_list_uri>
<user_presence_subscription idx="1" perm="">off</user_presence_subscription>
<user_presence_buddy_list_uri idx="1" perm=""></user_presence_buddy_list_uri>
<user_full_sdp_answer idx="1" perm="">on</user_full_sdp_answer>
<user_server_type idx="1" perm="">default</user_server_type>
<presence_state idx="1" perm="">Available</presence_state>
<user_remove_all_bindings idx="1" perm="">off</user_remove_all_bindings>
<user_failover_identity idx="1" perm="">1</user_failover_identity>
<record_dialed_calls idx="1" perm="">on</record_dialed_calls>
<record_received_calls idx="1" perm="">on</record_received_calls>
<user_presence_host idx="1" perm=""></user_presence_host>
<user_subscription_expiry idx="1" perm="">3600</user_subscription_expiry>
<user_presence_uri idx="1" perm=""></user_presence_uri>
<user_enable_hookflash idx="1" perm="">off</user_enable_hookflash>
<user_savp idx="1" perm="">off</user_savp>
<user_auth_tag idx="1" perm="">on</user_auth_tag>
<user_report_machine_state idx="1" perm="">on</user_report_machine_state>
<user_report_phone_state idx="1" perm="">on</user_report_phone_state>
<user_dynamic_payload idx="1" perm="">on</user_dynamic_payload>
<user_publish_presence_bootup idx="1" perm="">on</user_publish_presence_bootup>
<tcp_keepidle idx="1" perm="">5</tcp_keepidle>
<tcp_keepcnt idx="1" perm="">5</tcp_keepcnt>
<tcp_keepintvl idx="1" perm="">5</tcp_keepintvl>
<tcp_failover idx="1" perm="">off</tcp_failover>
<user_g726_packing_order idx="1" perm="">on</user_g726_packing_order>
<user_pic_tie_to_tbook idx="1" perm="">off</user_pic_tie_to_tbook>
<user_media_setup_offer idx="1" perm="">active</user_media_setup_offer>
<user_media_transport_offer idx="1" perm="">udp</user_media_transport_offer>
<user_presence_only idx="1" perm="">off</user_presence_only>
<user_presence_identity idx="1" perm="">none</user_presence_identity>
<user_default_contact_uri idx="1" perm="">none</user_default_contact_uri>
<using_server_managed_dnd idx="1" perm="">off</using_server_managed_dnd>
<using_server_managed_fwd_all idx="1" perm="">off</using_server_managed_fwd_all>
<using_server_managed_fwd_busy idx="1" perm="">off</using_server_managed_fwd_busy>
<using_server_managed_fwd_time idx="1" perm="">off</using_server_managed_fwd_time>
<server_managed_dnd_state idx="1" perm="">off</server_managed_dnd_state>
<server_managed_fwd_all_state idx="1" perm="">off</server_managed_fwd_all_state>
<server_managed_fwd_all_nr idx="1" perm=""></server_managed_fwd_all_nr>
<server_managed_fwd_busy_state idx="1" perm="">off</server_managed_fwd_busy_state>
<server_managed_fwd_busy_nr idx="1" perm=""></server_managed_fwd_busy_nr>
<server_managed_fwd_time_state idx="1" perm="">off</server_managed_fwd_time_state>
<server_managed_fwd_time_nr idx="1" perm=""></server_managed_fwd_time_nr>
<server_managed_fwd_time_secs idx="1" perm="">off</server_managed_fwd_time_secs>
<record_missed_calls_cwi_off idx="1" perm="">on</record_missed_calls_cwi_off>
<redirect_identity_allways idx="1" perm=""></redirect_identity_allways>
<user_hold_inactive idx="1" perm="">off</user_hold_inactive>
<retry_after_failed_subscribe idx="1" perm="">600</retry_after_failed_subscribe>
<user_wait_for_ntp_before_register idx="1" perm="">off</user_wait_for_ntp_before_register>
</phone-settings>
</settings>            

TEMP;
    return $content;
}

function PrincipalFileSnom_m9($DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer)
{
$ByDHCP = existsValue($arrParameters,'By_DHCP',""); 

$time = existsValue($arrParameters,'Time_Zone',"301");

  $timezone = <<<TEMP
<zone_desc perm="RW">GMT $time</zone_desc>
TEMP;
$refresh = "<settings_refresh_timer perm= \"RW\">0</settings_refresh_timer>";

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
<dhcp perm="RW">false</dhcp>
<ip_adr perm="RW">$ip</ip_adr>
<netmask perm="RW">$mask</netmask>
<dns_server1 perm="RW">$dns</dns_server1>
<dns_server2 perm="">$dns2</dns_server2>
<gateway perm="RW">$gw</gateway>
TEMP;
		break;
    case 1: 
		
		   $configNetwork= <<<TEMP
<dhcp perm="RW" />
<ip_adr perm="RW"></ip_adr>
<netmask perm="RW"></netmask>
<dns_server1 perm="RW"></dns_server1>
<dns_server2 perm=""></dns_server2>
<gateway perm="RW"></gateway>

TEMP;
break;
}

 $content= <<<TEMP
<?xml version="1.0" encoding="utf-8"?>
<settings>
 <phone-settings>
  <action_setup_url perm="RW" />
  <allow_check_sync perm="RW">false</allow_check_sync>
  <asset_id perm="RW" />
  <base_name perm="RW">snom-m9</base_name>
  <setting_server perm="RW">tftp://$ipAdressServer</setting_server>
  $configNetwork
  <dst_offset perm="RW">0</dst_offset>
  <dst_start_day perm="RW">0</dst_start_day>
  <dst_start_day_of_week perm="RW">0</dst_start_day_of_week>
  <dst_start_month perm="RW">0</dst_start_month>
  <dst_start_time perm="RW">0</dst_start_time>
  <dst_start_week_of_month perm="RW">0</dst_start_week_of_month>
  <dst_stop_day perm="RW">0</dst_stop_day>
  <dst_stop_day_of_week perm="RW">0</dst_stop_day_of_week>
  <dst_stop_month perm="RW">0</dst_stop_month>
  <dst_stop_time perm="RW">0</dst_stop_time>
  <dst_stop_week_of_month perm="RW">0</dst_stop_week_of_month>
  <emergency_numbers perm="RW" />
  <emergency_proxy perm="RW" />
  <ethernet_replug perm="RW">reregister</ethernet_replug>
  <gmt_offset perm="RW">0</gmt_offset>
   $refresh
  <language perm="RW" />
  <log perm="RW" idx="1">5</log>
  <ntp_server perm="RW">ntp.snom.com</ntp_server>
  <outbound_method perm="RW" />
  <outbound_tcp perm="RW">100</outbound_tcp>
  <outbound_udp perm="RW">20</outbound_udp>
  <packet_length perm="RW" idx="1">20</packet_length>
  <pcap_on_bootup perm="RW">false</pcap_on_bootup>
  <pin_change_prompt perm="RW">true</pin_change_prompt>
  <propose_length perm="RW" idx="1">false</propose_length>
  <qos_publish_uri perm="RW" />
  <read_status perm="RW">false</read_status>
  <reject_msg perm="RW">486 Busy Here</reject_msg>
  <repeater perm="RW">false</repeater>
  <retry_t1 perm="RW">500</retry_t1>
  <ring_duration perm="RW">60</ring_duration>
  <rtcp_dup_rle perm="RW">true</rtcp_dup_rle>
  <rtcp_loss_rle perm="RW">true</rtcp_loss_rle>
  <rtcp_rcpt_times perm="RW">true</rtcp_rcpt_times>
  <rtcp_rcvr_rtt perm="RW">true</rtcp_rcvr_rtt>
  <rtcp_stat_summary perm="RW">true</rtcp_stat_summary>
  <rtcp_voip_metrics perm="RW">true</rtcp_voip_metrics>
  <rtp_port_end perm="RW">65534</rtp_port_end>
  <rtp_port_start perm="RW">49152</rtp_port_start>
  <session_timeout perm="RW">360</session_timeout>
  <setting_server perm="RW" />
  <settings_refresh_timer perm="RW">86400</settings_refresh_timer>
  <short_form perm="RW">false</short_form>
  <sip_port perm="RW">0</sip_port>
  <stun_interval perm="RW">5</stun_interval>
  <stun_server perm="RW" />
  <telnet_enabled perm="RW">false</telnet_enabled>
  <tones perm="RW">1</tones>
  <tos_rtp perm="RW">160</tos_rtp>
  <tos_sip perm="RW">160</tos_sip>
  <user_active idx="1" perm="">on</user_active>
  <user_realname idx="1" perm="">$DisplayName</user_realname>
  <user_name idx="1" perm="">$id_device</user_name>
  <user_host idx="1" perm="">$ipAdressServer</user_host>
  <user_pname idx="1" perm=""></user_pname>
  <user_pass idx="1" perm="">$secret</user_pass>
  <user_allow_call_waiting perm="RW" idx="1">true</user_allow_call_waiting>
  <user_allow_line_switching perm="RW" idx="1">false</user_allow_line_switching>
  <user_ear_protection perm="RW" idx="1">false</user_ear_protection>
  <user_enable_e164_substitution perm="RW" idx="1">false</user_enable_e164_substitution>
  <user_expiry perm="RW" idx="1">3600</user_expiry>
  <user_forward_mode perm="RW" idx="1">0</user_forward_mode>
  <user_forward_number perm="RW" idx="1" />
  <user_forward_timeout perm="RW" idx="1">10</user_forward_timeout>
  <vlan_id perm="RW">0</vlan_id>
  <vlan_prio perm="RW">0</vlan_prio>
  $timezone
  </phone-settings>
</settings>          

TEMP;

return $content;
}

/*
    The function creates content of general settings file for SNOM 3XX. 
 */
function generalSettingsFileSnom($ipAdressServer)
{
    $content="
            <html>
                <pre>
                    challenge_response!: off
                    user_phone!: false
                    filter_registrar!: off
                    user_srtp1!: off
                    user_host1: $ipAdressServer
                    timezone!: GBR-0
                    tone_scheme!: USA		
                    ignore_security_warning!: on
                </pre>
            </html>";

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

function reboot($ip){
    $options = array(CURLOPT_POSTFIELDS =>"reboot=Reboot"); 
    if($ch = curl_init("http://$ip/advanced_update.htm")) 
    {
      curl_setopt_array($ch, $options);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      curl_close($ch);
      return TRUE;
    }else
      return FALSE;
}

function set_provision_server($ip_endpoint,$ip_server){
    $options = array(CURLOPT_POSTFIELDS =>"setting_server=tftp://$ip_server&Settings=Save"); 
    if($ch = curl_init("http://$ip_endpoint/advanced_update.htm")) 
    {
      curl_setopt_array($ch, $options);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      curl_close($ch);
      return reboot($ip_endpoint);
    }else
      return FALSE;
}

function set_by_curl($url,$parameters){
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/cookieSnom_m9");
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
$buf2 = curl_exec ($ch);
curl_close ($ch);
unset($ch);
}

function set_provision_server_m9($ip_endpoint,$ip_server){
 
$ch = curl_init();
curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/cookieSnom_m9");
curl_setopt($ch, CURLOPT_URL,"http://$ip_endpoint/index.htm");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "username=admin&password=password&link=index.htm&submit=Login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
ob_start();      
$buf2 = curl_exec ($ch); 
ob_end_clean();  


if(preg_match("/<input type=\"radio\" name=\"eula\" value=\"([0-9]+)\">/",$buf2,$arrTokens)){
            if(isset($arrTokens[1])){
                $eula = $arrTokens[1];
            }
        }
curl_close ($ch);
unset($ch);
if(isset($eula))
  set_by_curl("http://$ip_endpoint/index.htm","eula=$eula&save=Submit");
  set_by_curl("http://$ip_endpoint/network.htm","setting_server=tftp://$ip_server&save=Save");
  set_by_curl("http://$ip_endpoint/update.htm","reboot=Reboot");

}


?>
