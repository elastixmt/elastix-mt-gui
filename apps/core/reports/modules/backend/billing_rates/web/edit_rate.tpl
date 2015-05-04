<form method="POST" action="?menu={$module_name}">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          <input class="button" type="submit" name="submit_apply_changes" value="{$APPLY_CHANGES}" >
          <input class="button" type="submit" name="cancel" value="{$CANCEL}" ></td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
       <tr>
            <td align="right" colspan="6"><span style="font-weight:bold; font-style: italic; color:#E35332; font-size: 14px;">&nbsp;{$checkUpdate.LABEL}</span>{$checkUpdate.INPUT}&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-style: italic; font-size: 12px;">{$text_info}</span></td>
      </tr>
		{if $name eq 'Default'}
      <tr>
			<td width="15%"><b>{$Prefix.LABEL}: <span  class="required">*</span></b></td>
			<td width="25%">*</td>
			<td><b>{$Rate.LABEL} {$by_min}:<span  class="required">*</span></b></td>
			<td><b><a href="index.php?menu=currency" style="text-decoration:none;">{$currency}</a>&nbsp;</b>{$Rate.INPUT}</td>
			<td><b>{$Hidden_Digits.LABEL}: <span  class="required">*</span></b></td>
			<td>{$Hidden_Digits.INPUT}</td>
      </tr>
      <tr>
			<td><b>{$Name.LABEL}: <span  class="required">*</span></b></td>
			<td>{$name}</td>
			<td><b>{$Rate_offset.LABEL}: <span  class="required">*</span></b></td>
			<td><b><a href="index.php?menu=currency" style="text-decoration:none;">{$currency}</a>&nbsp;</b>{$Rate_offset.INPUT}</td>
			<td><b>{$Trunk.LABEL}: <span  class="required">*</span></b></td>
			<td>*</td>
      </tr>
		{else}
	  <tr>
			<td width="15%"><b>{$Prefix.LABEL}: <span  class="required">*</span></b></td>
			<td width="25%">{$prefix}</td>
            <td><b>{$Rate.LABEL} {$by_min}: <span  class="required">*</span></b></td>
            <td><b><a style="text-decoration:none;" href="index.php?menu=currency">{$currency}</a>&nbsp;</b>{$Rate.INPUT}</td>
            <td><b>{$Hidden_Digits.LABEL}: <span  class="required">*</span></b></td>
            <td>{$Hidden_Digits.INPUT}</td>
      </tr>
      <tr>
            <td><b>{$Name.LABEL}: <span  class="required">*</span></b></td>
            <td>{$Name.INPUT}</td>
			<td><b>{$Rate_offset.LABEL}: <span  class="required">*</span></b></td>
			<td><b><a href="index.php?menu=currency" style="text-decoration:none;">{$currency}</a>&nbsp;</b>{$Rate_offset.INPUT}</td>
            <td><b>{$Trunk.LABEL}: <span  class="required">*</span></b></td>
            <td>{$Trunk.INPUT}</td>
      </tr>
		{/if}
    </table>
  </td>
</tr>
</table>


<br />
<table width="99%" align="center" border="0" cellspacing="0" cellpadding="0" class="table_data">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan="8">&nbsp;&nbsp;<img src="images/1x1.gif" border="0" align="absmiddle">&nbsp;&nbsp;{$History}</td>
    </tr>
</table>
<table width="99%" align="center" border="0" cellspacing="0" cellpadding="0" class="table_data">
    <tr class="table_title_row">
        <td class="table_title_row" align="center">{$Name.LABEL}</td>
        <td class="table_title_row" align="center">{$Prefix.LABEL}</td>
        <td class="table_title_row" align="center">{$Rate.LABEL} {$by_min}</td>
        <td class="table_title_row" align="center">{$Rate_offset.LABEL}</td>
        <td class="table_title_row" align="center">{$Creation_Date}</td>
        <td class="table_title_row" align="center">{$Date_close}</td>
        <td class="table_title_row" align="center">{$Trunk.LABEL}</td>
        <td class="table_title_row" align="center">{$Hidden_Digits.LABEL}</td>
        <td class="table_title_row" align="center">{$Status}</td>
    </tr>
    {foreach from=$arrRates key=id item=rate name=rates}
         <tr onmouseout="this.style.backgroundColor='#ffffff';" onmouseover="this.style.backgroundColor='#f2f2f2';" style="background-color: #FFFFFF;">
        {if $smarty.foreach.rates.first}
            {foreach from=$rate key=id2 item=rate2 name=rates2}
            {if $id2 neq 'id'}
                {if $id2 eq 'estado'}
                    {if $rate2 eq 'activo'}
                    <td class="table_data" align="center" style="color: green;">{$Current}</td>
                    {else}
                    <td class="table_data" align="center" style="color: green;">{$Obsolete}</td>
                    {/if}
                {else}
                    <td class="table_data" align="center" style="color: green;">{$rate2}</td>
                {/if}
            {/if}
            {/foreach}
        </tr>
        {else}
            {foreach from=$rate key=id2 item=rate2 name=rates2}
            {if $id2 neq 'id'}
                {if $id2 eq 'estado'}
                    {if $rate2 eq 'activo'}
                    <td align="center" class="table_data">{$Current}</td>
                    {else}
                    <td align="center" class="table_data">{$Obsolete}</td>
                    {/if}
                {else}
                    <td align="center" class="table_data">{$rate2}</td>
                {/if}
            {/if}
            {/foreach}
        </tr>
        {/if}
    {/foreach}
</table>

<input type="hidden" name="id_rate" value="{$id_rate}">
</form>