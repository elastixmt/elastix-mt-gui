<div>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
            {if $mode eq 'input'}
                <input class="button" type="submit" name="save_new" value="{$SAVE}" >
            {elseif $mode eq 'edit'}
                {if $EDIT_ROUTE}<input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}">{/if}
            {else}
                {if $EDIT_ROUTE}<input class="button" type="submit" name="edit" value="{$EDIT}">{/if}
                {if $DELETE_ROUTE}<input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
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
        <tr class="inbound">
            <td>{$ORGANIZATION_LABEL}: </td>
            <td>{$ORGANIZATION}</td>
        </tr>
    {/if}
    <tr class="inbound">
        <td width="20%" nowrap>{$description.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
        <td width="30%">{$description.INPUT}</td>
    </tr>
    <tr class="inbound">
        <td width="20%" nowrap>{$did_number.LABEL}: </td>
        <td width="20%">{$did_number.INPUT}</td>
        <td>{$cid_number.LABEL}</td>
        <td>{$cid_number.INPUT}</td> 
    </tr>
    <tr><th>{$OPTIONS}</th></tr>
        <tr class="inbound">
        <td width="20%" nowrap>{$alertinfo.LABEL}:</td>
        <td width="30%">{$alertinfo.INPUT}</td>
        <td width="20%" nowrap>{$cid_prefix.LABEL}: </td>
        <td width="20%">{$cid_prefix.INPUT}</td>
    </tr>
    <tr class="inbound">
        <td width="20%" nowrap>{$moh.LABEL}:</td>
        <td width="30%">{$moh.INPUT}</td>
        <td width="20%" nowrap>{$ringing.LABEL}: </td>
        <td width="20%">{$ringing.INPUT}</td>
    </tr>
    <tr class="inbound">
        <td width="20%" nowrap>{$delay_answer.LABEL}:</td>
        <td width="30%">{$delay_answer.INPUT}</td>
    </tr>
    <tr><th>{$PRIVACY}</th></tr>
    <tr class="inbound">
        <td nowrap>{$primanager.LABEL}:</td>
        <td >{$primanager.INPUT}</td>
    </tr>
    {if $mode ne 'view' || $privacy_act eq 'yes'}
    <tr class="inbound privacy">
        <td nowrap>{$max_attempt.LABEL}:</td>
        <td >{$max_attempt.INPUT}</td>
        <td nowrap>{$min_length.LABEL}: </td>
        <td >{$min_length.INPUT}</td>
    </tr>
    {/if}
    <tr><th>{$FAXDETECT}</th></tr>
    <tr class="inbound">
        <td nowrap>{$fax_detect.LABEL}:</td>
        <td >{$fax_detect.INPUT}</td>
    </tr>
    {if $mode ne 'view' || $fax_detect_act eq 'yes'}
    <tr class="inbound fax_detect">
        <td nowrap>{$fax_type.LABEL}: </td>
        <td >{$fax_type.INPUT}</td>
        <td nowrap>{$fax_time.LABEL}:</td>
        <td >{$fax_time.INPUT}</td>
    </tr>
    <tr class="inbound fax_detect">
        <td nowrap>{$fax_destiny.LABEL}: </td>
        <td >{$fax_destiny.INPUT}</td>
    </tr>
    {/if}
    <tr><th>{$LANGUAGE}</th></tr>       
    <tr class="inbound">
        <td>{$language.LABEL}:</td>
        <td>{$language.INPUT}</td>
    </tr>
    <tr><th>{$SETDESTINATION}</th></tr>       
    <tr class="inbound">
        <td>{$goto.LABEL}:</td>
        <td colspan="3">{$goto.INPUT} {if $mode eq 'view'}>>{/if} {$destination.INPUT}</td>
    </tr>
    <tr><td></td></tr>
</table>
<input type="hidden" name="id_inbound" id="id_inbound" value="{$id_inbound}">
<input type="hidden" name="organization" value="{$ORGANIZATION}">

{literal}
<style type="text/css">
.inbound td {
    padding-left: 12px;
}
</style>
{/literal}
