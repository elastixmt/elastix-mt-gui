<div>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
            {if $mode eq 'input'}
                <input class="button" type="submit" name="save_new" value="{$SAVE}" >
            {elseif $mode eq 'edit'}
                {if $EDIT_ANN}<input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}">{/if}
                {if $DEL_ANN} <input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
            {else}
                {if $EDIT_ANN}<input class="button" type="submit" name="edit" value="{$EDIT}">{/if}
                {if $DEL_ANN} <input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
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
        <tr class="newtpltable">
            <td>{$ORGANIZATION_LABEL}: </td>
            <td>{$ORGANIZATION}</td>
        </tr>
    {/if}
    <tr class="newtpltable">
        <td nowrap>{$description.LABEL}: <span  class="required">*</span></td>
        <td>{$description.INPUT}</td>
    </tr>
    <tr class="newtpltable">
        <td nowrap>{$recording_id.LABEL}: <span  class="required">*</span></td>
        <td>{$recording_id.INPUT}</td>
    </tr> 
    <tr class="newtpltable">
        <td nowrap>{$repeat_msg.LABEL}: </td>
        <td>{$repeat_msg.INPUT}</td>
    </tr> 
    <tr class="newtpltable">
        <td nowrap>{$allow_skip.LABEL}: </td>
        <td>{$allow_skip.INPUT}</td>
    </tr> 
    <tr class="newtpltable">
        <td nowrap>{$return_ivr.LABEL}: </td>
        <td>{$return_ivr.INPUT}</td>
    </tr>
    <tr class="newtpltable">
        <td nowrap>{$noanswer.LABEL}: </td>
        <td>{$noanswer.INPUT}</td>
    </tr>
    <tr class="newtpltable">
        <td nowrap>{$goto.LABEL}: <span  class="required">*</span></td>
        <td>{$goto.INPUT} {$destination.INPUT}</td>
    </tr>
</table>
<input type="hidden" name="id_ann" id="id_ann" value="{$id_ann}">
<input type="hidden" name="organization" value="{$ORGANIZATION}">

{literal}
<style type="text/css">
.newtpltable td {
    padding-left: 12px;
}
</style>
{/literal}