<form method='POST' action="?menu=faxclients">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
        <tr>
            <td>
            {if $EDIT}<input class='button' type='submit' name='update_hosts' value='{$APPLY_CHANGES}'>{/if}
            </td>
        </tr>
     </table>
  </td>
</tr>
<tr>
  <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
        <tr>
            <td><i>{$EMAIL_RELAY_MSG}</i></td>
            <td>
            <textarea name='lista_hosts' cols='40' rows='8'>{$RELAY_CONTENT}</textarea>
            </td>
        </tr>
      </table>
  </td>
</tr>
</table>
</form>