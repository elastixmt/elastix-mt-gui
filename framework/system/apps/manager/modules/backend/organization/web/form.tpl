<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        {if $mode eq 'input'}
            <td >
                {if $CREATE_ORG}<input class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;{/if}
                <input class="button" type="submit" name="cancel" value="{$CANCEL}">
            </td>
        {elseif $mode eq 'view'}
        <td >
            {if $EDIT_ORG}<input class="button" type="submit" name="edit" value="{$EDIT}">{/if}
            {if $DELETE_ORG}<input class="button" type="submit" name="delete" value="{$DELETE}" onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'edit'}
        <td >
            {if $EDIT_ORG}<input class="button" type="submit" name="save_edit" value="{$APLICAR_CAMBIOS}">&nbsp;&nbsp;{/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
		{if $mode ne 'view'}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
		{/if}
    </tr>
</table>
<table class="tabForm" style="font-size: 14px" width="100%" cellpadding="4" align="center">
    <tr>
        <td width="14%" >{$name.LABEL}: <b>{if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
        <td width="31%" >{$name.INPUT}</td>
        <td width="19%" >{$domain.LABEL}: <b>{if $mode ne 'view'} <span  class="required">*</span>{/if}</b></td>
        {if $edit_entity}
            <td width="31%" >{$domain_name}</td>
        {else}
            <td width="31%" >{$domain.INPUT}</td>
        {/if}
    </tr>
    <tr>
        <td >{$country.LABEL}: <b>{if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
        <td >{$country.INPUT}</td>
        <td >{$city.LABEL}: <b>{if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
        <td >{$city.INPUT}</td>
    </tr>
    <tr>
        <td >{$address.LABEL}: </td>
        <td  colspan="3" width="74%">{$address.INPUT}</td>
    </tr>
    <tr>
        <td >{$country_code.LABEL}: <b>{if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
        <td >{$country_code.INPUT} </td>
        <td >{$area_code.LABEL}: <b>{if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
        <td >{$area_code.INPUT} </td>
    </tr>
    <tr>
        <td >{$email_contact.LABEL}: <b>{if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
        <td >{$email_contact.INPUT} </td>
    </tr>
</table>
<table class="tabForm" style="font-size: 14px" width="100%" cellpadding="4" align="center">
    <th >{$ORG_RESTRINCTION}</th>
    {if $USERLEVEL eq 'superadmin'}
        <tr>
            <td width="20%">{$max_num_user.LABEL}: <b>{if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
            <td>{$max_num_user.INPUT} <input type="checkbox" name="max_num_user_chk" class='org_chk_limits' {$CHECK_U} onclick="org_chk_limit('max_num_user');"> {$UNLIMITED}</td>
        </tr>
        <tr>
            <td >{$max_num_exten.LABEL}: <b>{if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
            <td>{$max_num_exten.INPUT} <input type="checkbox" name="max_num_exten_chk" class='org_chk_limits' {$CHECK_E}  onclick="org_chk_limit('max_num_exten');"> {$UNLIMITED}</td>
        </tr>
        <tr>
            <td >{$max_num_queues.LABEL}:<b>{if $mode ne 'view'}<span  class="required">*</span>{/if}</b> </td>
            <td>{$max_num_queues.INPUT} <input type="checkbox" name="max_num_queues_chk" class='org_chk_limits' {$CHECK_Q} onclick="org_chk_limit('max_num_queues');"> {$UNLIMITED}</td>
        </tr>
    {else}
        <tr>
            <td width="20%">{$max_num_user.LABEL}: </td>
            <td> {$CHECK_U}</td>
        </tr>
        <tr>
            <td >{$max_num_exten.LABEL}: </td>
            <td> {$CHECK_E}</td>
        </tr>
        <tr>
            <td >{$max_num_queues.LABEL}:  </td>
            <td> {$CHECK_Q}</td>
        </tr>
    {/if}
    <tr>
        <td >{$quota.LABEL}: <b>{if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
        <td >{$quota.INPUT} </td>
    </tr>
</table>
<input type="hidden" name="id" value="{$ID}" />
<input type="hidden" name="org_mode" value="{$mode}" />