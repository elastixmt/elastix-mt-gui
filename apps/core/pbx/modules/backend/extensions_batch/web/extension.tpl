<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu={$MODULE_NAME}'>
<table width="99%" border="0">
<tbody>
<tr>
    <td align="right">{$LABEL_FILE}:</td>
    <td><input type='file' id='csvfile' name='csvfile' /></td>
    <td><input class="button" type="submit" name="csvupload" value="{$LABEL_UPLOAD}" /></td>
</tr>
<tr>
    <td colspan="2"><a class="link1" href="?menu={$MODULE_NAME}&amp;action=csvdownload&amp;rawmode=yes">{$LABEL_DOWNLOAD}</a></td>
    <td><input class='button' type='submit' name='delete_all' value='{$LABEL_DELETE}' onClick="return confirmSubmit('{$CONFIRM_DELETE}');" /></td>
</tr>
<tr><td colspan="3">{$HeaderFile}</td></tr>
<tr><td colspan="3">{$AboutUpdate}</td></tr>
</tbody>
</table>
</form>