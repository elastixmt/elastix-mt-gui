<form method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        {if $EDIT}<td align="left"><input class='button' type='submit' name='save_default' value='{$APPLY_CHANGES}'></td>{/if}
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
	<td width="55%"><i>{$FAXMASTER_MSG}</i></td>
	<td width="35%">{$fax_master.INPUT}</td>
      </tr>
    </table>
  </td>
</tr>
</table>
</form>
