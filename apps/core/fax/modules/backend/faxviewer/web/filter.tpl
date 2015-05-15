<table width="100%" border="0" cellspacing="0" cellpadding="4">
    {if $USERLEVEL eq superadmin}
        <tr>
            <td width="12%" align="right">{$organization.LABEL}:</td>
            <td width="10%"align="left">{$organization.INPUT}</td>
        </tr>
    {/if}
    <tr>
        <td width="12%" align="right">{$name_company.LABEL}:</td>
        <td width="10%"align="left">{$name_company.INPUT}</td>
        <td width="12%" align="right">{$date_fax.LABEL}:</td>
        <td width="13%" align="left">{$date_fax.INPUT}</td>
    </tr>
    <tr>
        <td align="right">{$fax_company.LABEL}:</td>
        <td align="left">{$fax_company.INPUT}</td>
        <td align="right">{$filter.LABEL}</td> 
        <td align="left">{$filter.INPUT}</td>
        <td align="left">
            <input class="button" type="submit" name="buscar" value="{$SEARCH}" />
        </td>
    </tr>
</table>
