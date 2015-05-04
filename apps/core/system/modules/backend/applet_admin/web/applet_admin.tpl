<table width="100%" border="0" cellspacing="0" cellpadding="4">
    <tr class="letra12">
        <td align="left">
            {if $EDIT_APP}
                <input class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
            {/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="99%" border="0">
    <tr class="letra12">
        <td align="left" width="20%"><b>{$Applet}</b></td>
        <td align="left"><b>{$Activated}</b></td>
    </tr>
    {foreach from=$applets key=q item=applet name=appletrow}
        <tr class="letra12">
            <td align="left">
                <b>{$applet.name}:</b>
            </td>
            <td align="left">
                <input name="chkdau_{$applet.id}" type="checkbox" {if $applet.activated} checked="checked" {/if}> 
            </td>
        </tr>
    {/foreach}
</table>
