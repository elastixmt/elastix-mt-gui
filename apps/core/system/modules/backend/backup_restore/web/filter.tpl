<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
<tr>
  <td align="right" width="13%">
    <input class="button" type="submit" name="automatic"  value="{$AUTOMATIC}">
  </td>
  <td align="left" width="87%">
    <select name="time">
        <option value="DISABLED" {$SEL_DISABLED}>{$DISABLED}</option>
        <option value="DAILY" {$SEL_DAILY}>{$DAILY}</option>
        <option value="MONTHLY" {$SEL_MONTHLY}>{$MONTHLY}</option>
        <option value="WEEKLY" {$SEL_WEEKLY}>{$WEEKLY}</option>
    </select>
  </td>
<!--
  <td>
    {$FILE_UPLOAD}: <input type="file" name="file_upload">
    <input class="button" type="submit" name="upload" value="{$UPLOAD}">
  </td>
-->
</tr>
</table>
