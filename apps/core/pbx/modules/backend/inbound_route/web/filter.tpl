<table width="100%" border="0" cellspacing="0" cellpadding="8" align="center">
    {if $USERLEVEL eq 'superadmin'}
        <tr class="letra12">
            <td width="10%">{$organization.LABEL}: </td><td>{$organization.INPUT}</td>
        </tr>
    {/if}
    <tr class="letra12">
        <td width="10%">{$name.LABEL}: </td><td>{$name.INPUT} {$SEARCH}</td>
    </tr>
</table>