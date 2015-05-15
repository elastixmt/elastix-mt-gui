<form method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        {if $mode eq 'edit'}
            <td align="left">
                <input class="button" type="submit" name="submit_apply_change" value="{$SAVE}" >
                <input class="button" type="submit" name="cancel" value="{$CANCEL}">
            </td>
            <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
        {else}
            {if $EDIT}<td align="left"><input class="button" type="submit" name="submit_edit" value="{$EDIT_PARAMETERS}"></td>{/if}
        {/if}
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
        <td width="15%">{$fax_remite.LABEL}: {if $mode eq 'edit'}<span  class="required">*</span>{/if}</td>
        <td width="30%">{$fax_remite.INPUT}</td>
        <td width="10%" rowspan='3'>{$fax_content.LABEL}: </td>
        <td width="30%" rowspan='3'>{$fax_content.INPUT}</td>	
      </tr>
      <tr>
        <td width="15%">{$fax_remitente.LABEL}: {if $mode eq 'edit'}<span  class="required">*</span>{/if}</td>
        <td width="30%">{$fax_remitente.INPUT}</td>
     </tr>
      <tr>
        <td width="15%">{$fax_subject.LABEL}: {if $mode eq 'edit'}<span  class="required">*</span>{/if}</td>
        <td width="30%">{$fax_subject.INPUT}</td>
      </tr>
    </table>
  </td>
</tr>
</table>
</form>
