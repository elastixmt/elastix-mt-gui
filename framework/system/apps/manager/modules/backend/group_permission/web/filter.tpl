<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr class="letra12">
        <td width="5%" align="right">{$filter_group.LABEL}:&nbsp;&nbsp;</td>
        <td width="10%" align="left">{$filter_group.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td width="5%" align="right">{$filter_resource.LABEL}:&nbsp;&nbsp;</td>
        <td width="10%" align="left">{$filter_resource.INPUT}</td>
        {if $userLevel ne 'superadmin'}
            <td align="left"><input class="button" type="submit" name="show" value="{$SHOW}" /><td>
        {/if}
    </tr>
    {if $userLevel eq 'superadmin'}
    <tr class="letra12">
        <td width="5%" align="right">{$idOrganization.LABEL}:&nbsp;&nbsp;</td>
        <td width="10%" align="left">{$idOrganization.INPUT}</td>
        <td align="left"><input class="button" type="submit" name="show" value="{$SHOW}" /><td>
    </tr>
    {/if}
</table>

<input type="hidden" name="resource_apply" value="{$resource_apply}">
<input type="hidden" name="limit_apply" value="{$limit_apply}">
<input type="hidden" name="offset_apply" value="{$offset_apply}">
