<form method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          {if $mode eq 'input'}
          <input class="button" type="submit" name="save_network_changes" value="{$SAVE}" >
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
          {else}
          <input class="button" type="submit" name="edit" value="{$EDIT_PARAMETERS}"></td>
          {/if}          
        <td align="right" nowrap> {if $mode eq 'input'} <span class="letra12"> <span  class="required">*</span> {$REQUIRED_FIELD}</span> {/if}</td>
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
	<td width="15%">{$host.LABEL}: {if $mode eq 'input'} <span  class="required">*</span> {/if}</td>
	<td width="35%">{$host.INPUT}</td>
	<td width="20%">{$dns1.LABEL}: {if $mode eq 'input'} <span  class="required">*</span> {/if}</td>
	<td width="30%">{$dns1.INPUT}</td>
      </tr>
      <tr>
	<td>{$gateway.LABEL}: {if $mode eq 'input'} <span  class="required">*</span>{/if}</td>
	<td>{$gateway.INPUT}</td>
	<td width="20%">{$dns2.LABEL}: </td>
	<td width="30%">{$dns2.INPUT}</td>
      </tr>
    </table>
  </td>
</tr>
</table>
</form>
{$ETHERNET_INTERFASES_LIST}
