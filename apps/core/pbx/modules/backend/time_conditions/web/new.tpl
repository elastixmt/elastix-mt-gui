<div>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
            {if $mode eq 'input'}
                <input class="button" type="submit" name="save_new" value="{$SAVE}" >
            {elseif $mode eq 'edit'}
                {if $EDIT_TC}<input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}">{/if}
                {if $DEL_TC}<input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
            {else}
                {if $EDIT_TC}<input class="button" type="submit" name="edit" value="{$EDIT}">{/if}
                {if $DEL_TC}<input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
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
        <tr class="time_c">
            <td>{$ORGANIZATION_LABEL}: </td>
            <td>{$ORGANIZATION}</td>
        </tr>
    {/if}
    <tr class="time_c">
        <td width="15%" nowrap>{$name.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
        <td>{$name.INPUT}</td>
    </tr>
    <tr class="time_c">
        <td width="15%" nowrap>{$id_tg.LABEL}: </td>
        <td >{$id_tg.INPUT}</td> 
    </tr>
    <tr><th>{$SETDESTINATION_M}</th></tr>       
    <tr class="time_c">
        <td nowrap>{$goto_m.LABEL}:</td>
        <td colspan="3" class="match">{$goto_m.INPUT} {if $mode eq 'view'}>>{/if} {$destination_m.INPUT}</td>
    </tr>
    <tr><th>{$SETDESTINATION_F}</th></tr>       
    <tr class="time_c">
        <td nowrap>{$goto_f.LABEL}:</td>
        <td colspan="3" class="fail">{$goto_f.INPUT} {if $mode eq 'view'}>>{/if} {$destination_f.INPUT}</td>
    </tr>
</table>
<input type="hidden" name="id_tc" id="id_tc" value="{$id_tc}">
<input type="hidden" name="organization" id="id_tc" value="{$ORGANIZATION}">

{literal}
<style type="text/css">
.time_c td {
    padding-left: 12px;
}
</style>
{/literal}