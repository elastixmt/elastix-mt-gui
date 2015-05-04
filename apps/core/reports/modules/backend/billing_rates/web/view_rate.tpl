<form method="POST" action="?menu={$module_name}">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          <input class="button" type="submit" name="edit" value="{$EDIT}">
			 {if $name neq 'Default'}
          <input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">
			 {/if}
			 <input class="button" type="submit" name="cancel" value="{$CANCEL}">
		  </td>        
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
			<td width="15%"><b>{$Prefix.LABEL}: </b></td>
			<td width="25%">{$prefix}</td>
			<td><b>{$Rate.LABEL} {$by_min}:</b></td>
			<td><b><a href="index.php?menu=currency" style="text-decoration:none;">{$currency}</a>&nbsp;</b>{$rate}</td>
			<td><b>{$Creation_Date.LABEL}: </b></td>
			<td>{$creation_date}</td>
      </tr>
      <tr>
			<td><b>{$Name.LABEL}: </b></td>
			<td>{$name}</td>
			<td><b>{$Rate_offset.LABEL}: </b></td>
			<td><b><a href="index.php?menu=currency" style="text-decoration:none;">{$currency}</a>&nbsp;</b>{$rate_offset}</td>
			<td><b>{$Trunk.LABEL}: </b></td>
			<td>{$trunk}</td>
			<td><b>{$Hidden_Digits.LABEL}: </b></td>
			<td>{$hidden_digits}</td>
      </tr>
    </table>
  </td>
</tr>
</table>

<input type="hidden" name="id_rate" value="{$id_rate}">
</form>
