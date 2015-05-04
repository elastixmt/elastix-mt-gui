<form method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
    <td>
            {if $EDIT_THEME}<input class="button" type="submit" name="changeTheme" value="{$CHANGE}" >{/if}
    </td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td width="9%"><b>{$themes.LABEL}:</b></td>
    <td width="35%">{$themes.INPUT}</td>
    </tr>
</table>
</form>