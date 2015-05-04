<form method="POST" action="?menu={$module_name}" enctype="multipart/form-data">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          <input class="button" type="submit" name="submit_import_changes" value="{$SAVE}" >
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
	<td width="10%">{$importcsv.LABEL}: <span  class="required">*</span></td>
	<td width="90%">{$importcsv.INPUT}&nbsp;&nbsp;<a class='tooltip' title='{$alert_import}'><img src='modules/{$module_name}/images/img_info.png' width='15' height='15' alt='info' longdesc='Descripcion de Nombre' /></a></td>
      </tr>
    </table>
  </td>
</tr>
</table>
<input type="hidden" name="id_rate" value="{$id_rate}">
</form>
