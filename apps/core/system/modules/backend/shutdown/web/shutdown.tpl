<form method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          {if $SHUTDOWN}
            <input class="button" type="submit" name="shutdown" value="{$ACCEPT}" onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">
          {/if}
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
	<td width="15%"><input type="radio" name="shutdown_mode" value="1">&nbsp;{$HALT} </td>
	<td width="35%">&nbsp;</td>
	<td width="20%">&nbsp;</td>
	<td width="30%">&nbsp;</td>
      </tr>
      <tr>
	<td width="15%"><input type="radio" name="shutdown_mode" value="2" checked>&nbsp;{$REBOOT}</td>
	<td width="35%">&nbsp;</td>
	<td width="20%">&nbsp;</td>
	<td width="30%">&nbsp;</td>
      </tr>
    </table>
  </td>
</tr>
</table>
</form>
