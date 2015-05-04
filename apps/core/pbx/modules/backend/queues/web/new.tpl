<div align="right" style="padding-right: 4px;">
    {if $mode ne 'view'}
    <span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span>
    {/if}
</div>
<div class="neo-table-header-row">
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-1" name="tab-group-1" onclick="radio('tab-1');" checked>
        <label for="tab-1">{$GENERAL}</label>
    </div>
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-2" name="tab-group-2" onclick="radio('tab-2');">
        <label for="tab-2">{$MEMBERS}</label>
    </div>
    <div class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-3" name="tab-group-3" onclick="radio('tab-3');">
        <label for="tab-3">{$ADVANCED}</label>
    </div>
    <div class="neo-table-header-row-navigation" align="right" style="display: inline-block;">
        {if $mode eq 'input'}
            <input type="submit" name="save_new" value="{$SAVE}" >
        {elseif $mode eq 'edit'}
            {if $EDIT_QUEUE}<input type="submit" name="save_edit" value="{$APPLY_CHANGES}" >{/if}
            {if $DEL_QUEUE}<input type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
        {else}
            {if $EDIT_QUEUE}<input type="submit" name="edit" value="{$EDIT}">{/if}
            {if $DEL_QUEUE}<input type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
        {/if}
        <input type="submit" name="cancel" value="{$CANCEL}">
    </div>
</div>
<div class="tabs">
    <div class="tab">
       <div class="content" id="content_tab-1" style="padding-left: 8px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm extension">
                {if $USERLEVEL eq 'superadmin'}
                    <tr class="queue">
                        <td>{$ORGANIZATION_LABEL}: </td>
                        <td>{$ORGANIZATION}</td>
                    </tr>
                {/if}
                <tr class="queue">
                    <td width="15%" nowrap>{$name.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    {if $mode eq 'view' || $mode eq 'edit'}
                        <td width="31%">{$QUEUE}</td>
                    {else}
                        <td width="31%">{$name.INPUT}</td>
                    {/if}
                    <td width="21%" nowrap>{$description.LABEL}: {if $mode ne 'view'}<span class="required">*</span>{/if}</td>
                    <td>{$description.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$cid_prefix.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$cid_prefix.INPUT}</td>
                    <td width="15%" nowrap>{$cid_holdtime.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$cid_holdtime.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$alert_info.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$alert_info.INPUT}</td>
                    <td width="15%" nowrap>{$musicclass.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$musicclass.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$announce_caller_detail.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$announce_caller_detail.INPUT}</td>
                    <td width="15%" nowrap>{$announce_detail.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$announce_detail.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$reportholdtime.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$reportholdtime.INPUT}</td>
                    <td width="15%" nowrap>{$strategy.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$strategy.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$weight.LABEL}: </td>
                    <td width="31%">{$weight.INPUT}</td>
                    <td width="15%" nowrap>{$maxlen.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$maxlen.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$TIME_OPTIONS}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$timeout_detail.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$timeout_detail.INPUT}</td>
                    <td width="15%" nowrap>{$timeout.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$timeout.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$retry.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$retry.INPUT}</td>
                    <td width="15%" nowrap>{$timeoutpriority.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$timeoutpriority.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$EMPTY_OPTIONS}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$joinempty.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$joinempty.INPUT}</td>
                    <td width="15%" nowrap>{$leavewhenempty.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$leavewhenempty.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$skip_busy_detail.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$skip_busy_detail.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$RECORDING}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$monitor_format.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="31%">{$monitor_format.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$DEFAULT_DEST}</td>
                </tr>
                <tr class="queue">
                    <td nowrap>{$category.LABEL}:</td>
                    <td colspan=3>{$category.INPUT} {if $mode eq 'view'}>>{/if} {$destination.INPUT}</td>
                </tr>
            </table>
        </div>       
    </div>
    <div class="tab">
        <div class="content" id="content_tab-2" style="padding-left: 8px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
                <tr class="queue">
                    <td width="15%" nowrap>{$password_detail.LABEL}: </td>
                    <td width="31%">{$password_detail.INPUT}</td>
                    <td width="20%"></td>
                    <td width="30%"></td>
                </tr>
                <tr class="queue">
                    <td valign="top" nowrap>{$static_members.LABEL}: </td>
                    <td>{$static_members.INPUT}</td>
                    <td valign="top">{$pickup_static.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td valign="top" nowrap>{$dynamic_members.LABEL}: </td>
                    <td>{$dynamic_members.INPUT}</td>
                    <td valign="top">{$pickup_dynamic.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td nowrap>{$restriction_agent.LABEL}: </td>
                    <td>{$restriction_agent.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td nowrap>{$calling_restriction.LABEL}: </td>
                    <td>{$calling_restriction.INPUT}</td>
                </tr>
            </table>
        </div>
    </div>  
    <div class="tab">
        <div class="content" id="content_tab-3">
            <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
                <tr class="queue">
                    <td width="15%" nowrap>{$servicelevel.LABEL}: </td>
                    <td width="31%">{$servicelevel.INPUT}</td>
                    <td width="15%" nowrap>{$context.LABEL}: </td>
                    <td width="31%">{$context.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$wrapuptime.LABEL}: </td>
                    <td width="31%">{$wrapuptime.INPUT}</td>
                    <td width="15%" nowrap>{$autofill.LABEL}: </td>
                    <td width="31%">{$autofill.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$autopause.LABEL}: </td>
                    <td width="31%">{$autopause.INPUT}</td>
                    <td width="15%" nowrap>{$autopausedelay.LABEL}: </td>
                    <td width="31%">{$autopausedelay.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$ANN_OPTIONS}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$announce_frequency.LABEL}: </td>
                    <td width="31%">{$announce_frequency.INPUT}</td>
                    <td width="15%" nowrap>{$min_announce_frequency.LABEL}: </td>
                    <td width="31%">{$min_announce_frequency.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$announce_holdtime.LABEL}: </td>
                    <td width="31%">{$announce_holdtime.INPUT}</td>
                    <td width="15%" nowrap>{$announce_position.LABEL}: </td>
                    <td width="31%">{$announce_position.INPUT}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$announce_position_limit.LABEL}: </td>
                    <td width="31%">{$announce_position_limit.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$PER_OPTIONS}</td>
                </tr>
                <tr class="queue">
                    <td width="15%" nowrap>{$periodic_announce.LABEL}: </td>
                    <td width="31%">{$periodic_announce.INPUT}</td>
                    <td width="15%" nowrap>{$periodic_announce_frequency.LABEL}: </td>
                    <td width="31%">{$periodic_announce_frequency.INPUT}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
<input type="hidden" name="mode_input" value="{$mode}">
<input type="hidden" name="qname" value="{$qname}">
<input type="hidden" name="organization" value="{$ORGANIZATION}">


{literal}
<script type="text/javascript">
$(document).ready(function(){
    radio("tab-1");
});
</script>
<style type="text/css">
 .queue td{
	padding-left: 12px;
}
</style>
{/literal}
