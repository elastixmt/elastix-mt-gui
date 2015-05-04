<table width="100%" cellpadding="4" cellspacing="0" border="0">
    <tr>
        <td align="left">
          {if $mode eq 'input'}
            {if $CREATE_GROUP}<input class="button" type="submit" name="save_group" value="{$SAVE}" >{/if}
          {elseif $mode eq 'edit'}
            {if $EDIT_GROUP}<input class="button" type="submit" name="apply_changes" value="{$APPLY_CHANGES}" >{/if}
          {else}
            {if $EDIT_GROUP}<input class="button" type="submit" name="edit" value="{$EDIT}">{/if}
            {if $DELETE_GROUP}<input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
          {/if}
        <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
        {if $mode ne 'view'}<td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>{/if}
    </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
    {if $USERLEVEL eq 'superadmin'}
        <td>{$organization.LABEL}:{if $mode ne 'view'} <span  class="required">*</span>{/if}</td>
        <td>{if $mode eq 'input'}{$organization.INPUT}{else}{$ORGANIZATION}{/if}</td>
        <td width="50%"></td>
    {/if}
    <tr>
        <td>{$group.LABEL}:{if $mode ne 'view'} <span  class="required">*</span>{/if}</td>
        <td>{if $mode ne 'edit'}{$group.INPUT}{else}{$GROUP}{/if}</td>
        <td width="50%"></td>
    </tr>
    <tr>
        <td>{$description.LABEL}:{if $mode ne 'view'} <span  class="required">*</span>{/if}</td>
        <td>{$description.INPUT}</td>
        <td width="50%"></td>
    </tr>
</table>
<input type="hidden" name="id" value="{$id_group}">
