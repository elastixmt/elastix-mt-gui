<form method="POST" action="?menu={$module_name}">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          <input class="button" type="submit" name="submit_save_rate" value="{$SAVE}" >
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td> 
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
			<td width="15%">{$Prefix.LABEL}: <span  class="required">*</span></td>
			<td width="25%">{$Prefix.INPUT}</td>
			<td>{$Rate.LABEL} {$by_min}:<span  class="required">*</span></td>
			<td><b><a href="index.php?menu=currency" style="text-decoration:none;">{$currency}</a>&nbsp;</b>{$Rate.INPUT}</td>
			<td>{$Hidden_Digits.LABEL}: <span  class="required">*</span></td>
			<td>{$Hidden_Digits.INPUT}</td>
      </tr>
      <tr>
			<td>{$Name.LABEL}: <span  class="required">*</span></td>
			<td>{$Name.INPUT}</td>
			<td>{$Rate_offset.LABEL}: <span  class="required">*</span></td>
			<td><b><a href="index.php?menu=currency" style="text-decoration:none;">{$currency}</a>&nbsp;</b>{$Rate_offset.INPUT}</td>
			<td>{$Trunk.LABEL}: <span  class="required">*</span></td>
			<td>{$Trunk.INPUT}</td>
      </tr>
    </table>
  </td>
</tr>
</table>
<input type="hidden" name="id_rate" value="{$id_rate}">
</form>
