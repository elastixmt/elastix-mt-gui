<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    {if $USERLEVEL eq 'superadmin'}
        <tr class="letra12">
            <td  align="right" nowrap>{$organization.LABEL}: </td><td>{$organization.INPUT}</td>
        </tr>
    {/if}
    <tr class="letra12">
        <td width="10%" align="right" nowrap>{$state_conf.LABEL}: </td>
        <td align="left" >{$state_conf.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="right" nowrap>{$type_conf.LABEL}: </td>
        <td align="left" >{$type_conf.INPUT} </td>
    </tr>
    <tr>
        <td align="right">{$name_conf.LABEL}: </td>
        <td align="left" >{$name_conf.INPUT} {$SEARCH}</td>
    </tr>
</table>