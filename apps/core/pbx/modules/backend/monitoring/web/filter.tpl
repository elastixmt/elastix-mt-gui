<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
    {if $USERLEVEL eq 'superadmin'}
        <tr >
            <td width="10%" nowrap>{$organization.LABEL}: </td><td>{$organization.INPUT}</td>
        </tr>
    {/if}
    <tr >
        <td width="10%" nowrap>{$date_start.LABEL}:</td>
        <td >{$date_start.INPUT}</td>
        <td width="10%" nowrap>{$source.LABEL}:</td>
        <td >{$source.INPUT}</td>
    </tr>
    <tr>
        <td width="10%" nowrap>{$date_end.LABEL}:</td>
        <td >{$date_end.INPUT}:</td>
        <td width="10%" nowrap>{$destination.LABEL}:</td>
        <td >{$destination.INPUT}</td>
    </tr>
    <tr >
        <td width="10%" nowrap></td>
        <td ></td>
        <td width="10%" nowrap>{$type.LABEL}:</td>
        <td >{$type.INPUT} {$SEARCH}</td>
    </tr>
</table>