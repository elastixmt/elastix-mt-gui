<div>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
            {if $mode eq 'input'}
                <input class="button" type="submit" name="save_new" value="{$SAVE}" >
            {elseif $mode eq 'edit'}
                {if $EDIT_RG}<input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}">{/if}
                {if $DEL_RG}<input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
            {else}
                {if $EDIT_RG}<input class="button" type="submit" name="edit" value="{$EDIT}">{/if}
                {if $DEL_RG}<input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
            {/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        {if $mode ne 'view'}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
        {/if}
     </tr>
   </table>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
    {if $USERLEVEL eq 'superadmin'}
        <tr class="ringgroup">
            <td>{$ORGANIZATION_LABEL}: </td>
            <td>{$ORGANIZATION}</td>
        </tr>
    {/if}
    <tr class="ringgroup">
        <td width="20%" nowrap>{$rg_number.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
        {if $mode eq 'view' || $mode eq 'edit'}
            <td width="31%">{$RG_NUMBER}</td>
        {else}
            <td width="31%">{$rg_number.INPUT}</td>
        {/if}
        <td width="20%" nowrap>{$rg_name.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
        <td width="30%">{$rg_name.INPUT}</td>
    </tr>
    <tr class="ringgroup">
        <td nowrap>{$rg_strategy.LABEL}: </td>
        <td>{$rg_strategy.INPUT}</td>
        <td nowrap>{$rg_time.LABEL}</td>
        <td>{$rg_time.INPUT}</td> 
    </tr>
    <tr class="ringgroup">
        <td width="20%" nowrap>{$rg_alertinfo.LABEL}:</td>
        <td width="30%">{$rg_alertinfo.INPUT}</td>
        <td width="20%" nowrap>{$rg_cid_prefix.LABEL}: </td>
        <td width="20%">{$rg_cid_prefix.INPUT}</td>
    </tr>
    <tr class="ringgroup">
        <td width="20%" nowrap>{$rg_recording.LABEL}:</td>
        <td width="30%">{$rg_recording.INPUT}</td>
        <td width="20%" nowrap>{$rg_moh.LABEL}: </td>
        <td width="20%">{$rg_moh.INPUT}</td>
    </tr>
    <tr class="ringgroup">
        <td width="20%" nowrap>{$rg_cf_ignore.LABEL}:</td>
        <td width="30%">{$rg_cf_ignore.INPUT}</td>
        <td width="20%" nowrap>{$rg_skipbusy.LABEL}: </td>
        <td width="20%">{$rg_skipbusy.INPUT}</td>
    </tr>
    <tr class="ringgroup">
        <td nowrap>{$rg_pickup.LABEL}:</td>
        <td >{$rg_pickup.INPUT}</td>
    </tr>
    <tr class="ringgroup">
        <td nowrap>{$rg_confirm_call.LABEL}:</td>
        <td >{$rg_confirm_call.INPUT}</td>
    </tr>
    {if $mode ne 'view' || $confirm eq 'yes'}
    <tr class="ringgroup confirm">
        <td nowrap>{$rg_record_remote.LABEL}:</td>
        <td >{$rg_record_remote.INPUT}</td>
        <td nowrap>{$rg_record_toolate.LABEL}: </td>
        <td >{$rg_record_toolate.INPUT}</td>
    </tr>
    {/if}
    <tr><th>{$RING_EXTENSIONS}</th></tr>
    <tr class="ringgroup">
        <td valign="top" nowrap>{$rg_extensions.LABEL}: </td>
        <td align="left">{$rg_extensions.INPUT}</td>
        {if $mode ne 'view'}
        <td valign="top">{$pickup_extensions.INPUT}</td>
        {/if}
    </tr>
    <tr><th>{$SETDESTINATION}</th></tr>       
    <tr class="ringgroup">
        <td>{$goto.LABEL}:</td>
        <td colspan="3">{$goto.INPUT} {if $mode eq 'view'}>>{/if} {$destination.INPUT}</td>
    </tr>
    <tr><td></td></tr>
</table>
<input type="hidden" name="id_rg" id="id_rg" value="{$id_rg}">
<input type="hidden" name="organization" value="{$ORGANIZATION}">

{literal}
<style type="text/css">
.ringgroup td {
    padding-left: 12px;
}
</style>
{/literal}
