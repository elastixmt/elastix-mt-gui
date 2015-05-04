<table width="99%" cellpadding="4" cellspacing="0" border="0">
    <tr class="letra12">
        <td width="10%" nowrap>{$date_start.LABEL}:</td>
        <td >{$date_start.INPUT}</td>
        <td width="10%" nowrap>{$src.LABEL}:</td>
        <td >{$src.INPUT}</td>
        <td width="10%" nowrap>{$src_channel.LABEL}:</td>
        <td >{$src_channel.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td >{$date_end.LABEL}:</td>
        <td >{$date_end.INPUT}</td>
        <td >{$dst.LABEL}:</td>
        <td >{$dst.INPUT}</td>
        <td >{$dst_channel.LABEL}:</td>
        <td >{$dst_channel.INPUT}</td>
    </tr>
    <tr class="letra12">
        {if $USERLEVEL eq 'superadmin'}
            <td >{$organization.LABEL}: </td>
            <td >{$organization.INPUT} {$SEARCH}</td>
        {else}
            <td >{$status.LABEL}: </td>
            <td >{$status.INPUT} {$SEARCH}</td>
        {/if}
        <td >{$calltype.LABEL}: </td>
        <td >{$calltype.INPUT} {if $USERLEVEL ne 'superadmin'}{$SEARCH}{/if}</td>
        <td >{$accountcode.LABEL}: </td>
        <td >{$accountcode.INPUT} {$SEARCH}</td>
    </tr>
    {if $USERLEVEL eq 'superadmin'}
        <tr class="letra12">
            <td > </td>
            <td ></td>
            <td >{$status.LABEL}: </td>
            <td >{$status.INPUT} {$SEARCH}</td>
            <td > </td>
            <td ></td>
        </tr>
    {/if}
</table>


