<link href="modules/{$module_name}/themes/default/css/styles.css" rel="stylesheet" />
<div id="moduleContainer">
    <div id="moduleTitle" valign="middle" align="left"><span style="font-size: 15px; color: #666; font-weight: bold;">&nbsp;&nbsp;&nbsp;{$registration}</span></div>

    <div id="formContainer" style="border: 1px solid #666; padding: 5px; margin: 10px 10px 0px; font-size: 12px; font-family:
Verdana,Arial,Helvetica,sans-serif;">
        <div align="center">{$alert_message}</div>
    </div>
    <div style="border: 1px solid #666; padding: 5px; margin: 10px 10px 0px; font-size: 12px; font-family: Verdana,Arial,Helvetica,sans-serif;">
	<div id="msnTextErr" align="center" style="{$displayError}" >{$errorMsg}</div>
	<table style="text-indent: 5px; padding: 3px; margin: 10px 10px 2px 10px;" height="215" width="98%" align="center" border="0" cellpadding="0" cellspacing="0">
	    <tbody>
		{if $registered eq "registered"}
		<tr bordercolor="#FFFFFF" valign="middle">
		    <td height="0" width="132" colspan="2" id="getinfo"><div align="center">{$getinfo}</div><div align="center" style="padding-bottom: 5px;"><img src="modules/{$module_name}/images/loading.gif" alt="loading"/></div></td>
		</tr>
		{/if}
		{if $registered eq "registered"}
		<tr bordercolor="#FFFFFF" valign="middle">
		    <td height="0" class="tdIdServer" width="132"><div align="left" style="padding-bottom: 15px;"><font face="Verdana,Arial, Helvetica, sans-serif" size="2"><b>{$identitykeylbl}</b></font></div></td>
		    <td height="0" class="tdIdServer" width="370"><div align="left" style="padding-bottom: 15px;"><font face="Verdana, Arial, Helvetica,sans-serif" size="2">
			<b id="identitykey">{$identitykey}</b></font></div>
		    </td>
		</tr>
		{/if}
		<tr bordercolor="#FFFFFF" valign="middle">
		    <td height="0" width="132" bgcolor="#f0f0f0">
			<div align="left"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">{$contactNameReg.LABEL}</font></div>
		    </td>
		    <td height="0" width="370" bgcolor="#f0f0f0"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">
			{$contactNameReg.INPUT}
			    <font color="#ff6600"><b>*</b></font></font>
		    </td>
		</tr>
		<tr bordercolor="#FFFFFF" valign="middle">
		    <td height="0" width="132"><div align="left"><font face="Verdana,Arial, Helvetica, sans-serif" size="1">{$emailReg.LABEL}</font></div></td>
		    <td height="0" width="370"><font face="Verdana, Arial, Helvetica,sans-serif" size="1">
			{$emailReg.INPUT}<font color="#ff6600"><b> *</b></font></font>
		    </td>
		</tr>
		<tr bordercolor="#FFFFFF" valign="middle">
		    <td height="0" width="132" bgcolor="#f0f0f0"><div align="left"><font face="Verdana,Arial, Helvetica, sans-serif" size="1">{$phoneReg.LABEL}</font></div></td>
		    <td height="0" width="370" bgcolor="#f0f0f0"><font face="Verdana, Arial, Helvetica,sans-serif" size="1">
			{$phoneReg.INPUT}
			    <font color="#ff6600"><b>*</b></font></font>
		    </td>
		</tr>
		<tr valign="middle">
		    <td bordercolor="#FFFFFF" width="132"><div align="left">
			<font face="Verdana, Arial, Helvetica, sans-serif" size="1">{$companyReg.LABEL}</font></div>
		    </td>
		    <td bordercolor="#FFFFFF" width="370">
			<font face="Verdana, Arial,Helvetica, sans-serif" size="1">
			    {$companyReg.INPUT}
			    <font color="#ff6600"><b>*</b></font>
			</font>
		    </td>
		</tr>
		<tr bordercolor="#FFFFFF" valign="middle">
		    <td width="132" bgcolor="#f0f0f0">
			<div align="left"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">{$addressReg.LABEL}</font></div>
		    </td>
		    <td width="370" bgcolor="#f0f0f0">
			<font face="Verdana, Arial,Helvetica, sans-serif" size="1">
			    {$addressReg.INPUT}
			</font>
		    </td>
		</tr>
		<tr bordercolor="#FFFFFF" valign="middle">
		    <td width="132">
			<div align="left">
			    <font face="Verdana, Arial, Helvetica, sans-serif" size="1">{$cityReg.LABEL}</font>
			</div>
		    </td>
		    <td width="370">
			<font face="Verdana, Arial,Helvetica, sans-serif" size="1">
			    {$cityReg.INPUT}
			    <font color="#ff6600"><b>*</b></font>
			</font>
		    </td>
		</tr>
		<tr bordercolor="#FFFFFF" valign="middle">
		    <td width="132" bgcolor="#f0f0f0">
			<div align="left"><font face="Verdana, Arial, Helvetica, sans-serif" size="1">{$countryReg.LABEL}</font></div>
		    </td>
		    <td nowrap="nowrap" width="370" bgcolor="#f0f0f0">
			<font face="Verdana, Arial, Helvetica, sans-serif" size="1">
			    {$countryReg.INPUT}
			    <font color="#ff6600"><b>*</b></font>
			</font>
		    </td>
		</tr>
		<!--
		<tr bordercolor="#FFFFFF" valign="middle">
		    <td width="132">
			<div align="left">
			    <font face="Verdana, Arial, Helvetica, sans-serif" size="1">{$idPartnerReg.LABEL}</font>
			</div>
		    </td>
		    <td width="370">
			<font face="Verdana, Arial,Helvetica, sans-serif" size="1">
			    {$idPartnerReg.INPUT}
			    <a id="getDataPartner" style="cursor: pointer; text-decoration: underline;">Get Info Partner</a>
			</font>
		    </td>
		</tr>
		-->
		<tr valign="middle">
		    <td colspan="2" style="padding-left: 5px;" height="43" align="right">
			<div id="tdButtons">
			    <table>
				<tbody>
				    <tr>
					{if $showActivate neq 'disactivate'}
					<td>
					    <div id="activateRegister" style="cursor: pointer; font-size: 10px; margin: 0px; padding: 3px 0px 3px 0px; text-align: center;">
						<input type="button" value="{$Activate_registration}" name="btnAct" id="btnAct" onclick="registration();" />
					    </div>
					</td>
					{/if}				
				    </tr>
				</tbody>
			    </table>
			</div>
			<div id="tdloaWeb" style="padding-left: 5px; display: none;" align="center">
			    <div id="imgSending"><img src="modules/{$module_name}/images/loading.gif" alt="loading"/></div>
			    <div id="msnTextReg">{$sending}</div>
			</div>
		    </td>
		</tr>
	    </tbody>
	</table>
    </div>
</div>

