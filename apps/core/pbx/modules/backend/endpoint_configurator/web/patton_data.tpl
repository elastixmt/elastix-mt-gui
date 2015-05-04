<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        <td align="left">
            <input class="button" type="submit" name="next_1" value="{$NEXT}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>

<div class="tabForm" style="font-size: 16px" width="100%">
    <div id="telnet_data">	
	<table border="0" width="100%" cellspacing="0" cellpadding="8" >
	    <tr class="letra12">
		<td align="left" width="30%"><b style="color: rgb(227, 83, 50); font-size: 16px; font-family: 'Lucida Console';">{$telnet_data}</b></td>
	    </tr>
	    <tr class="letra12">
		<td align="left"><b>{$telnet_username.LABEL}:</b></td>
		<td align="left">{$INFO25}&nbsp;&nbsp;&nbsp;&nbsp;{$telnet_username.INPUT}</td>
	    </tr>
	    <tr class="letra12">
		<td align="left"><b>{$telnet_password.LABEL}:</b></td>
		<td align="left">{$INFO26}&nbsp;&nbsp;&nbsp;&nbsp;{$telnet_password.INPUT}</td>
	    </tr>
	</table>
    </div>
    <br />
    <div id="general_data">	
	<table border="0" width="100%" cellspacing="0" cellpadding="8" >
	    <tr class="letra12">
		<td align="left" width="30%"><b style="color: rgb(227, 83, 50); font-size: 16px; font-family: 'Lucida Console';">{$general_data}</b></td>
	    </tr>
	    <tr class="letra12">
		<td align="left"><b>{$analog_trunk_lines.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO1}&nbsp;&nbsp;&nbsp;&nbsp;{$analog_trunk_lines.INPUT}</td>
	    </tr>
	    <tr class="letra12">
		<td align="left"><b>{$analog_extension_lines.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO2}&nbsp;&nbsp;&nbsp;&nbsp;{$analog_extension_lines.INPUT}</td>
	    </tr>
	    <tr class="letra12">
		<td align="left"><b>{$router_present.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO3}&nbsp;&nbsp;&nbsp;&nbsp;{$router_present.INPUT}</td>
	    </tr>
	    <tr class="letra12" id="side" {$DISPLAY_PBX_SIDE}>
		<td align="left"><b>{$pbx_side.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO4}&nbsp;&nbsp;&nbsp;&nbsp;{$pbx_side.INPUT}</td>
	    </tr>
	    <tr class="letra12">
		<td align="left"><b>{$sntp_address.LABEL}:</b></td>
		<td align="left">{$INFO5}&nbsp;&nbsp;&nbsp;&nbsp;{$sntp_address.INPUT}</td>
	    </tr>
	    <tr class="letra12">
		<td align="left"><b>{$dns_address.LABEL}:</b></td>
		<td align="left">{$INFO6}&nbsp;&nbsp;&nbsp;&nbsp;{$dns_address.INPUT}</td>
	    </tr>
	</table>
    </div>
    <br />
    <div id="network_data">
	<table border="0" width="100%" cellspacing="0" cellpadding="8" >
	    <tr class="letra12">
		<td align="left" width="30%"><b style="color: rgb(227, 83, 50); font-size: 16px; font-family: 'Lucida Console';">{$network_data}</b></td>
	    </tr>
	    <tr class="letra12">
		<td align="left"><b style="color: rgb(227, 83, 50); font-size: 12px; font-family: 'Lucida Console';">LAN</b></td>
	    </tr>
	    <tr class="letra12">
		<td colspan='2'>
		    <input type="radio" name="option_network_lan" id="lan_static" value="lan_static" {$lan_check_static} onclick="activate_option_lan()" />
		    {$lan_static} &nbsp;&nbsp;&nbsp;
		    <input type="radio" name="option_network_lan" id="lan_dhcp" value="lan_dhcp" {$lan_check_dhcp} onclick="activate_option_lan()" />
		    {$lan_dhcp}
		</td>
	    </tr>
	    <tr class="letra12" id="lan_ip" {$DISPLAY_LAN}>
		<td align="left"><b>{$lan_ip_address.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO21}&nbsp;&nbsp;&nbsp;&nbsp;{$lan_ip_address.INPUT}</td>
	    </tr>

	    <tr class="letra12" id="lan_mask" {$DISPLAY_LAN}>
		<td align="left"><b>{$lan_ip_mask.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO22}&nbsp;&nbsp;&nbsp;&nbsp;{$lan_ip_mask.INPUT}</td>
	    </tr>

	    <tr class="letra12" id="wan" {$DISPLAY_LABEL_WAN}>
		<td align="left"><b style="color: rgb(227, 83, 50); font-size: 12px; font-family: 'Lucida Console';">WAN</b></td>
	    </tr>
	    <tr class="letra12" id="check_wan" {$DISPLAY_CHECK_WAN}>
		<td colspan='2'>
		    <input type="radio" name="option_network_wan" id="wan_static" value="wan_static" {$wan_check_static} onclick="activate_option_wan()" />
		    {$wan_static} &nbsp;&nbsp;&nbsp;
		    <input type="radio" name="option_network_wan" id="wan_dhcp" value="wan_dhcp" {$wan_check_dhcp} onclick="activate_option_wan()" />
		    {$wan_dhcp}
		</td>
	    </tr>
	    <tr class="letra12" id="wan_ip" {$DISPLAY_WAN}>
		<td align="left"><b>{$wan_ip_address.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO23}&nbsp;&nbsp;&nbsp;&nbsp;{$wan_ip_address.INPUT}</td>
	    </tr>

	    <tr class="letra12" id="wan_mask" {$DISPLAY_WAN}>
		<td align="left"><b>{$wan_ip_mask.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO24}&nbsp;&nbsp;&nbsp;&nbsp;{$wan_ip_mask.INPUT}</td>
	    </tr>

	    <tr class="letra12">
		<td align="left"><b>{$default_gateway.LABEL}:</b></td>
		<td align="left">{$INFO7}&nbsp;&nbsp;&nbsp;&nbsp;{$default_gateway.INPUT}</td>
	    </tr> 
	</table>
    </div>
    <br />
    <div id="ip_pbx">
	<table border="0" width="100%" cellspacing="0" cellpadding="8" >
	    <tr class="letra12">
		<td align="left"><b style="color: rgb(227, 83, 50); font-size: 16px; font-family: 'Lucida Console';">{$ip_pbx}</b></td>
	    </tr>
	    <tr class="letra12">
		<td align="left" width="30%"><b>{$pbx_address.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO8}&nbsp;&nbsp;&nbsp;&nbsp;{$pbx_address.INPUT}</td>
	    </tr>

	    <tr class="letra12">
		<td align="left"><b>{$sip_port.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO9}&nbsp;&nbsp;&nbsp;&nbsp;{$sip_port.INPUT}</td>
	    </tr>
	</table>
    </div>
    <br />
    <div id="localization_data">
	<table border="0" width="100%" cellspacing="0" cellpadding="8" >
	    <tr class="letra12">
		<td align="left"><b style="color: rgb(227, 83, 50); font-size: 16px; font-family: 'Lucida Console';">{$localization_data}</b></td>
	    </tr>
	    <tr class="letra12">
		<td align="left" width="30%"><b>{$country.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO11}&nbsp;&nbsp;&nbsp;&nbsp;{$country.INPUT}</td>
	    </tr>
	</table>
    </div>
    <br />
    <div id="general_extensions_data">
	<table border="0" width="100%" cellspacing="0" cellpadding="8" >
	    <tr class="letra12">
		<td align="left"><b style="color: rgb(227, 83, 50); font-size: 16px; font-family: 'Lucida Console';">{$general_extensions_data}</b></td>
	    </tr>
	    <tr class="letra12">
		<td align="left" width="30%"><b>{$first_extension.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO12}&nbsp;&nbsp;&nbsp;&nbsp;{$first_extension.INPUT}</td>
	    </tr>

	    <tr class="letra12">
		<td align="left"><b>{$increment.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO13}&nbsp;&nbsp;&nbsp;&nbsp;{$increment.INPUT}</td>
	    </tr>

	    <tr class="letra12">
		<td align="left"><b>{$extensions_sip_port.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO14}&nbsp;&nbsp;&nbsp;&nbsp;{$extensions_sip_port.INPUT}</td>
	    </tr>

	    <tr class="letra12">
		<td align="left"><b>{$lines_sip_port.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO15}&nbsp;&nbsp;&nbsp;&nbsp;{$lines_sip_port.INPUT}</td>
	    </tr>
  
	    <tr class="letra12">
		<td align="left"><b>{$timeout.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO16}&nbsp;&nbsp;&nbsp;&nbsp;{$timeout.INPUT}</td>
	    </tr>

	    <tr class="letra12">
		<td align="left"><b>{$delivery_announcements.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO17}&nbsp;&nbsp;&nbsp;&nbsp;{$delivery_announcements.INPUT}</td>
	    </tr>

	    <tr class="letra12">
		<td align="left"><b>{$wait_callerID.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO18}&nbsp;&nbsp;&nbsp;&nbsp;{$wait_callerID.INPUT}</td>
	    </tr>

	    <tr class="letra12">
		<td align="left"><b>{$callerID_format.LABEL}: <span  class="required">*</span></b></td>
		<td align="left">{$INFO19}&nbsp;&nbsp;&nbsp;&nbsp;{$callerID_format.INPUT}</td>
	    </tr>

	    <tr class="letra12">
		<td align="left"><b>{$callerID_presentation.LABEL}: <span  class="required">*</span></td>
		<td align="left">{$INFO20}&nbsp;&nbsp;&nbsp;&nbsp;</b>{$callerID_presentation.INPUT}</td>
	    </tr>
	</table>
    </div>
</div>
