<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        {if $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="save_dhcpclient" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'view'}
        <td align="left">
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'edit'}
        <td align="left">
            <input class="button" type="submit" name="save_edit" value="{$EDIT}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="left"><b>{$iphost.LABEL}: </b></td>
        <td align="left">{$iphost.INPUT}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$date_starts.LABEL}: </b></td>
        <td align="left">{$date_starts.INPUT}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$date_ends.LABEL}: </b></td>
        <td align="left">{$date_ends.INPUT}</td>
    </tr>

    <tr class="letra12">
        <td align="left"><b>{$macaddress.LABEL}: </b></td>
        <td align="left">{$macaddress.INPUT}</td>
    </tr>

</table>

<input class="button" type="hidden" name="id" value="{$ID}" />