<form method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          {if $mode eq 'input'}
          <input class="button" type="submit" name="save_interfase_changes" value="{$APPLY_CHANGES}" 
                 onClick="return confirmSubmit('{$CONFIRM_EDIT}')">
          <input class="button" type="submit" name="cancel_interfase_edit" value="{$CANCEL}  "></td>
          {else}
          <input class="button" type="submit" name="edit" value="{$EDIT_PARAMETERS}"></td>
          {/if}          
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
	<td width="15%">{$type.LABEL}: <span  class="required">*</span></td>
	<td width="35%">{$type.INPUT}</td>
	<td width="20%">&nbsp;</td>
	<td width="30%">&nbsp;</td>
      </tr>
      <tr>
	<td width="15%">{$ip.LABEL}: <span  class="required">*</span></td>
	<td width="35%">{$ip.INPUT}</td>
	<td width="20%">&nbsp;</td>
	<td width="30%">&nbsp;</td>
      </tr>
      <tr>
	<td>{$mask.LABEL}: <span  class="required">*</span></td>
	<td>{$mask.INPUT}</td>
	<td width="20%">&nbsp; </td>
	<td width="30%">&nbsp;</td>
      </tr>
    </table>
  </td>
</tr>
</table>
{$dev_id.INPUT}
</form>
