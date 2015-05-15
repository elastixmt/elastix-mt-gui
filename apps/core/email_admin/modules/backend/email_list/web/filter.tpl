<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    {if $USERLEVEL eq 'superadmin'}
        <tr class="letra12">
            <td width="5%">{$domain.LABEL}: </td>
            <td >{$domain.INPUT}</td>
        </tr>
    {/if}
    <tr class="letra12">
        <td width="5%">{$name_list.LABEL}: </td>
        <td >{$name_list.INPUT} {$SEARCH}</td>
    </tr>
</table>