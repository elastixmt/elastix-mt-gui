{if $USERLEVEL eq 'superadmin'}
    <div class="neo-table-header-row">
        <div  class="neo-table-header-row-filter tab">
            {$SELECT_ORG}
        </div>
    </div>
{/if}
<table width="100%" cellpadding="4" cellspacing="0" border="0">
    <tr>
    <td align="left">
        {if $mode eq 'edit'}
            {if $EDIT_FC}<input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}" >{/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
        {else}
            {if $EDIT_FC}<input class="button" type="submit" name="edit" value="{$EDIT}"></td>{/if}
        {/if}
    {if $mode ne 'view'}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    {/if}
    </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-left: 8px;" class="tabForm">
    <tr>
        <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$BLACKLIST}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$blacklist_num.LABEL}:</td>
        <td>{$blacklist_num.INPUT}</td>
        <td>{$blacklist_num_stat}</td>
        <td nowrap>{$blacklist_lcall.LABEL}:</td>
        <td>{$blacklist_lcall.INPUT}</td>
        <td>{$blacklist_lcall_stat}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$blacklist_rm.LABEL}: </td>
        <td>{$blacklist_rm.INPUT}</td>
        <td>{$blacklist_rm_stat}</td>
    </tr>
    <tr>
        <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$CALLFORWARD}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$cf_all_act.LABEL}: </td>
        <td>{$cf_all_act.INPUT}</td>
        <td>{$cf_all_act_stat}</td>
        <td nowrap>{$cf_all_desact.LABEL}: </td>
        <td>{$cf_all_desact.INPUT}</td>
        <td>{$cf_all_desact_stat}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$cf_all_promp.LABEL}: </td>
        <td>{$cf_all_promp.INPUT}</td>
        <td>{$cf_all_promp_stat}</td>
        <td nowrap>{$cf_busy_act.LABEL}: </td>
        <td>{$cf_busy_act.INPUT}</td>
        <td>{$cf_busy_act_stat}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$cf_busy_desact.LABEL}: </td>
        <td>{$cf_busy_desact.INPUT}</td>
        <td>{$cf_busy_desact_stat}</td>
        <td nowrap>{$cf_busy_promp.LABEL}: </td>
        <td>{$cf_busy_promp.INPUT}</td>
        <td>{$cf_busy_promp_stat}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$cf_nu_act.LABEL}: </td>
        <td>{$cf_nu_act.INPUT}</td>
        <td>{$cf_nu_act_stat}</td>
        <td nowrap>{$cf_nu_desact.LABEL}: </td>
        <td>{$cf_nu_desact.INPUT}</td>
        <td>{$cf_nu_desact_stat}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$cf_toggle.LABEL}: </td>
        <td>{$cf_toggle.INPUT}</td>
        <td>{$cf_toggle_stat}</td>
    </tr>
    <tr>
        <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$CALLWAITING}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$cw_act.LABEL}: </td>
        <td>{$cw_act.INPUT}</td>
        <td>{$cw_act_stat}</td>
        <td nowrap>{$cw_desact.LABEL}: </td>
        <td>{$cw_desact.INPUT}</td>
        <td>{$cw_desact_stat}</td>
    </tr>
    <tr>
        <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$CORE}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$direct_call_pickup.LABEL}: </td>
        <td>{$direct_call_pickup.INPUT}</td>
        <td>{$direct_call_pickup_stat}</td>
        <td nowrap>{$pickup.LABEL}: </td>
        <td>{$pickup.INPUT}</td>
        <td>{$pickup_stat}</td>
    </tr>
    <tr class="feature" style="height:32px">
        <td nowrap>{$blind_transfer.LABEL}: </td>
        <td>{$blind_transfer.INPUT}</td>
        <td>{$blind_transfer_stat}</td>
        <td nowrap>{$attended_transfer.LABEL}: </td>
        <td>{$attended_transfer.INPUT}</td>
        <td>{$attended_transfer_stat}</td>
    </tr>
    <tr class="feature" style="height:32px">
        <td nowrap>{$one_touch_monitor.LABEL}: </td>
        <td>{$one_touch_monitor.INPUT}</td>
        <td>{$one_touch_monitor_stat}</td>
        <td nowrap>{$disconnect_call.LABEL}: </td>
        <td>{$disconnect_call.INPUT}</td>
        <td>{$disconnect_call_stat}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$sim_in_call.LABEL}: </td>
        <td>{$sim_in_call.INPUT}</td>
        <td>{$sim_in_call_stat}</td>
    </tr>
    <tr>
        <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$DICTATION}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$dictation_email.LABEL}: </td>
        <td>{$dictation_email.INPUT}</td>
        <td>{$dictation_email_stat}</td>
        <td nowrap>{$dictation_perform.LABEL}: </td>
        <td>{$dictation_perform.INPUT}</td>
        <td>{$dictation_perform_stat}</td>
    </tr>
    <tr>
        <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$DND}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$dnd_act.LABEL}: </td>
        <td>{$dnd_act.INPUT}</td>
        <td>{$dnd_act_stat}</td>
        <td nowrap>{$dnd_desact.LABEL}: </td>
        <td>{$dnd_desact.INPUT}</td>
        <td>{$dnd_desact_stat}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$dnd_toggle.LABEL}: </td>
        <td>{$dnd_toggle.INPUT}</td>
        <td>{$dnd_toggle_stat}</td>
    </tr>
    <tr>
        <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$INFO}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$call_trace.LABEL}: </td>
        <td>{$call_trace.INPUT}</td>
        <td>{$call_trace_stat}</td>
        <td nowrap>{$echo_test.LABEL}: </td>
        <td>{$echo_test.INPUT}</td>
        <td>{$echo_test_stat}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$speak_u_exten.LABEL}: </td>
        <td>{$speak_u_exten.INPUT}</td>
        <td>{$speak_u_exten_stat}</td>
        <td nowrap>{$speak_clock.LABEL}: </td>
        <td>{$speak_clock.INPUT}</td>
        <td>{$speak_clock_stat}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$directory.LABEL}: </td>
        <td>{$directory.INPUT}</td>
        <td>{$directory_stat}</td>
        <td nowrap>{$pbdirectory.LABEL}: </td>
        <td>{$pbdirectory.INPUT}</td>
        <td>{$pbdirectory_stat}</td>
    </tr>
    <tr>
        <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$SPEEDDIAL}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$speeddial_set.LABEL}: </td>
        <td>{$speeddial_set.INPUT}</td>
        <td>{$speeddial_set_stat}</td>
        <td nowrap>{$speeddial_prefix.LABEL}: </td>
        <td>{$speeddial_prefix.INPUT}</td>
        <td>{$speeddial_prefix_stat}</td>
    </tr>
    <tr>
        <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$VOICEMAIL}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$voicemail_dial.LABEL}: </td>
        <td>{$voicemail_dial.INPUT}</td>
        <td>{$voicemail_dial_stat}</td>
        <td nowrap>{$voicemail_mine.LABEL}: </td>
        <td>{$voicemail_mine.INPUT}</td>
        <td>{$voicemail_mine_stat}</td>
    </tr>
    <tr>
        <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$FOLLOWME}</td>
    </tr>
    <tr class="feature">
        <td nowrap>{$fm_toggle.LABEL}: </td>
        <td>{$fm_toggle.INPUT}</td>
        <td>{$fm_toggle_stat}</td>
    </tr>
</table>

<input type="hidden" name="mostra_adv" id="mostra_adv" value="{$mostra_adv}">

{literal}
<style type="text/css">
.feature td {
	padding-left: 12px;
}
input[type="text"]:readonly
{
background:#dddddd;
}
</style>
{/literal}
