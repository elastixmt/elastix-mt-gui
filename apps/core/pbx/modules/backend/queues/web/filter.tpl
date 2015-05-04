<table width="100%" border="0" cellspacing="0" cellpadding="8" align="center">
    {if $USERLEVEL eq 'superadmin'}
        <tr class="letra12">
            <td width="10%">{$organization.LABEL}: </td><td>{$organization.INPUT}</td>
        </tr>
    {/if}
    <tr class="letra12">
        <td width="10%">{$queue_name.LABEL}: </td><td>{$queue_name.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td width="10%">{$queue_number.LABEL}: </td><td>{$queue_number.INPUT} {$SEARCH}</td>
    </tr>
</table>