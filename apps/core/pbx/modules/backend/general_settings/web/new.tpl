<div align="right" style="padding-right: 4px;">
    {if $mode ne 'view'}
    <span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span>
    {/if}
</div>
<div class="neo-table-header-row">
    {if $USERLEVEL eq 'superadmin'}
        <div  class="neo-table-header-row-filter tab">
            {$SELECT_ORG}
        </div>
    {/if}
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-1" name="tab-group-1" onclick="radio('tab-1');" checked>
        <label for="tab-1">{$GENERAL}</label>
    </div>
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-2" name="tab-group-2" onclick="radio('tab-2');" checked>
        <label for="tab-2">{$SIP_GENERAL}</label>
    </div>
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-3" name="tab-group-3" onclick="radio('tab-3');">
        <label for="tab-3">{$IAX_GENERAL}</label>
    </div>
    <div class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-4" name="tab-group-4" onclick="radio('tab-4');">
        <label for="tab-4">{$VM_GENERAL}</label>
    </div>
    <div class="neo-table-header-row-navigation" align="right" style="display: inline-block;">
        {if $EDIT_GS}<input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}" >{/if}
        {if $EDIT_GS}<input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>{/if}
    </div>
</div>
<div class="tabs">
    <div class="tab">
       <div class="content" id="content_tab-1" style="padding-left: 8px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-left: 8px;" class="tabForm">
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$DIAL_OPTS}</td>
                </tr>
                <tr class="general">
                    <td nowrap>{$DIAL_OPTIONS.LABEL}:</td>
                    <td>{$DIAL_OPTIONS.INPUT}</td>
                    <td nowrap>{$TRUNK_OPTIONS.LABEL}:</td>
                    <td>{$TRUNK_OPTIONS.INPUT}</td>
                </tr>
                <tr class="general">
                    <td nowrap>{$RINGTIMER.LABEL}:</td>
                    <td>{$RINGTIMER.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$CALL_RECORDING}</td>
                </tr>
                <tr class="general">
                    <td nowrap>{$RECORDING_STATE.LABEL}:</td>
                    <td>{$RECORDING_STATE.INPUT}</td>
                    <td nowrap>{$MIXMON_FORMAT.LABEL}:</td>
                    <td>{$MIXMON_FORMAT.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$LOCATIONS}</td>
                </tr>
                <tr class="general">
                    <td nowrap>{$TONEZONE.LABEL}:</td>
                    <td>{$TONEZONE.INPUT}</td>
                    <td nowrap>{$LANGUAGE.LABEL}:</td>
                    <td>{$LANGUAGE.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$DIRECTORY_OPTS}</td>
                </tr>
                <tr class="general">
                    <td nowrap>{$DIRECTORY.LABEL}:</td>
                    <td>{$DIRECTORY.INPUT}</td>
                    <td nowrap>{$DIRECTORY_OPT_EXT.LABEL}:</td>
                    <td>{$DIRECTORY_OPT_EXT.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$EXT_OPTS}</td>
                </tr>
                <tr class="general">
                    <td nowrap>{$CREATE_VM.LABEL}:</td>
                    <td>{$CREATE_VM.INPUT}</td>
                </tr>
            </table>
       </div>
       <div class="content" id="content_tab-2" style="padding-left: 8px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
                <tr class="sip_settings">
                    <td width="15%" nowrap>{$sip_context.LABEL}: </td>
                    <td width="31%">{$sip_context.INPUT}</td>
                    <td width="21%" nowrap>{$sip_dtmfmode.LABEL}: </td>
                    <td >{$sip_dtmfmode.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_host.LABEL}: </td>
                    <td>{$sip_host.INPUT}</td>
                    <td nowrap>{$sip_type.LABEL}: </td>
                    <td>{$sip_type.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_nat.LABEL}: </td>
                    <td>{$sip_nat.INPUT}</td>
                    <td nowrap>{$sip_allowtransfer.LABEL}: </td>
                    <td>{$sip_allowtransfer.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_port.LABEL}: </td>
                    <td>{$sip_port.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$QUALIFY}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_qualify.LABEL}: </td>
                    <td>{$sip_qualify.INPUT}</td>
                    <td nowrap>{$sip_qualifyfreq.LABEL}:</td>
                    <td>{$sip_qualifyfreq.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$CODEC}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_disallow.LABEL}: </td>
                    <td>{$sip_disallow.INPUT}</td>
                    <td nowrap>{$sip_allow.LABEL}: </td>
                    <td>{$sip_allow.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$RTP_TIMERS}</td>
                </tr>
                <tr class="sip_settings" >
                    <td nowrap>{$sip_rtptimeout.LABEL}:</td>
                    <td>{$sip_rtptimeout.INPUT}</td>
                    <td nowrap>{$sip_rtpholdtimeout.LABEL}:</td>
                    <td>{$sip_rtpholdtimeout.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_rtpkeepalive.LABEL}:</td>
                    <td>{$sip_rtpkeepalive.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$VIDEO_OPTS}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_videosupport.LABEL}:</td>
                    <td>{$sip_videosupport.INPUT}</td>
                    <td nowrap>{$sip_maxcallbitrate.LABEL}:</td>
                    <td>{$sip_maxcallbitrate.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$MOH}</td>
                </tr>
                <tr class="sip_settings" >
                    <td nowrap>{$sip_mohinterpret.LABEL}:</td>
                    <td>{$sip_mohinterpret.INPUT}</td>
                    <td nowrap>{$sip_mohsuggest.LABEL}:</td>
                    <td>{$sip_mohsuggest.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$OTHER}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_directmedia.LABEL}: </td>
                    <td>{$sip_directmedia.INPUT}</td>
                    <td nowrap>{$sip_transport.LABEL}: </td>
                    <td>{$sip_transport.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_trustrpid.LABEL}: </td>
                    <td>{$sip_trustrpid.INPUT}</td>
                    <td nowrap>{$sip_sendrpid.LABEL}: </td>
                    <td>{$sip_sendrpid.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_callingpres.LABEL}: </td>
                    <td>{$sip_callingpres.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_callcounter.LABEL}: </td>
                    <td>{$sip_callcounter.INPUT}</td>
                    <td nowrap>{$sip_busylevel.LABEL}: </td>
                    <td>{$sip_busylevel.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$sip_progressinband.LABEL}:</td>
                    <td>{$sip_progressinband.INPUT}</td>
                    <td nowrap>{$sip_g726nonstandard.LABEL}:</td>
                    <td>{$sip_g726nonstandard.INPUT}</td>
                </tr>
            </table>
       </div>
       <div class="content" id="content_tab-3" style="padding-left: 8px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
                <tr class="iax_settings">
                    <td width="15%" nowrap>{$iax_context.LABEL}:</td>
                    <td width="31%">{$iax_context.INPUT}</td>
                    <td nowrap>{$iax_port.LABEL}:</td>
                    <td>{$iax_port.INPUT}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$iax_host.LABEL}:</td>
                    <td>{$iax_host.INPUT}</td>
                    <td nowrap>{$iax_type.LABEL}:</td>
                    <td>{$iax_type.INPUT}</td>
                </tr>
                <tr class="iax_settings">
                    <td width="21%" nowrap>{$iax_transfer.LABEL}:</td>
                    <td>{$iax_transfer.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$CODEC}</td>
                </tr>
                <tr class="iax_settings">
                    <td width="15%" nowrap>{$iax_disallow.LABEL}:</td>
                    <td width="31%">{$iax_disallow.INPUT}</td>
                    <td width="21%" nowrap>{$iax_allow.LABEL}:</td>
                    <td>{$iax_allow.INPUT}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$iax_codecpriority.LABEL}:</td>
                    <td>{$iax_codecpriority.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$MOH}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$iax_mohinterpret.LABEL}:</td>
                    <td>{$iax_mohinterpret.INPUT}</td>
                    <td nowrap>{$iax_mohsuggest.LABEL}:</td>
                    <td>{$iax_mohsuggest.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$QUALIFY}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$iax_qualify.LABEL}:</td>
                    <td>{$iax_qualify.INPUT}</td>
                    <td nowrap>{$iax_qualifysmoothing.LABEL}:</td>
                    <td>{$iax_qualifysmoothing.INPUT}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$iax_qualifyfreqok.LABEL}:</td>
                    <td>{$iax_qualifyfreqok.INPUT}</td>
                    <td nowrap>{$iax_qualifyfreqnotok.LABEL}:</td>
                    <td>{$iax_qualifyfreqnotok.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$JITTER}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$iax_jitterbuffer.LABEL}:</td>
                    <td>{$iax_jitterbuffer.INPUT}</td>
                    <td nowrap>{$iax_forcejitterbuffer.LABEL}:</td>
                    <td>{$iax_forcejitterbuffer.INPUT}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$iax_sendani.LABEL}:</td>
                    <td>{$iax_sendani.INPUT}</td>
                    <td nowrap>{$iax_adsi.LABEL}:</td>
                    <td>{$iax_adsi.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$OTHER}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$iax_requierecalltoken.LABEL}:</td>
                    <td>{$iax_requierecalltoken.INPUT}</td>
                    <td nowrap>{$iax_encryption.LABEL}:</td>
                    <td>{$iax_encryption.INPUT}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$iax_mask.LABEL}:</td>
                    <td>{$iax_mask.INPUT}</td>
                </tr>
            </table>
       </div>
       <div class="content" id="content_tab-4" style="padding-left: 8px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-left: 8px;" class="tabForm">
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$GENERAL_VM}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$VM_PREFIX.LABEL}:</td>
                    <td>{$VM_PREFIX.INPUT}</td>
                    <td nowrap>{$VM_DDTYPE.LABEL}:</td>
                    <td>{$VM_DDTYPE.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$VM_GAIN.LABEL}:</td>
                    <td>{$VM_GAIN.INPUT}</td>
                    <td nowrap>{$VM_OPTS.LABEL}:</td>
                    <td>{$VM_OPTS.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$OPERATOR_XTN.LABEL}:</td>
                    <td>{$OPERATOR_XTN.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td>{$vm_emailsubject.LABEL}:</td>
                    <td colspan="3">{$vm_emailsubject.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td>{$vm_emailbody.LABEL}:</td>
                    <td colspan="3">{$vm_emailbody.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$vm_attach.LABEL}:</td>
                    <td>{$vm_attach.INPUT}</td>
                    <td nowrap>{$vm_maxmsg.LABEL}:</td>
                    <td>{$vm_maxmsg.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$vm_saycid.LABEL}:</td>
                    <td>{$vm_saycid.INPUT}</td>
                    <td nowrap>{$vm_sayduration.LABEL}:</td>
                    <td>{$vm_sayduration.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$vm_envelope.LABEL}:</td>
                    <td>{$vm_envelope.INPUT}</td>
                    <td nowrap>{$vm_delete.LABEL}:</td>
                    <td>{$vm_delete.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$vm_context.LABEL}:</td>
                    <td>{$vm_context.INPUT}</td>
                    <td nowrap>{$vm_tz.LABEL}:</td>
                    <td>{$vm_tz.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$vm_review.LABEL}:</td>
                    <td>{$vm_review.INPUT}</td>
                    <td nowrap>{$vm_operator.LABEL}:</td>
                    <td>{$vm_operator.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$vm_forcename.LABEL}:</td>
                    <td>{$vm_forcename.INPUT}</td>
                    <td nowrap>{$vm_forcegreetings.LABEL}:</td>
                    <td>{$vm_forcegreetings.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; color: #E35332; font-weight: bold;" colspan=4>{$VMX_OPTS}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$VMX_CONTEXT.LABEL}: </td>
                    <td colspan=4>{$VMX_CONTEXT.INPUT} {$CONTEXT} {$VMX_PRI.INPUT}{$VMX_PRI.LABEL}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$VMX_TIMEDEST_CONTEXT.LABEL}: </td>
                    <td colspan=4>{$VMX_TIMEDEST_CONTEXT.INPUT} {$CONTEXT} {$VMX_TIMEDEST_EXT.INPUT}{$VMX_TIMEDEST_EXT.LABEL} {$VMX_PRI.INPUT}{$VMX_PRI.LABEL}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$VMX_LOOPDEST_CONTEXT.LABEL}: </td>
                    <td colspan=4>{$VMX_LOOPDEST_CONTEXT.INPUT} {$CONTEXT} {$VMX_LOOPDEST_EXT.INPUT}{$VMX_LOOPDEST_EXT.LABEL} {$VMX_LOOPDEST_PRI.INPUT}{$VMX_LOOPDEST_PRI.LABEL}</td>  
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$VMX_OPTS_TIMEOUT.LABEL}:</td>
                    <td>{$VMX_OPTS_TIMEOUT.INPUT}</td>
                    <td nowrap>{$VMX_OPTS_LOOP.LABEL}:</td>
                    <td>{$VMX_OPTS_LOOP.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$VMX_OPTS_DOVM.LABEL}:</td>
                    <td>{$VMX_OPTS_DOVM.INPUT}</td>
                    <td nowrap>{$VMX_TIMEOUT.LABEL}:</td>
                    <td>{$VMX_TIMEOUT.INPUT}</td>
                </tr>
                <tr class="voicemail">
                    <td nowrap>{$VMX_REPEAT.LABEL}:</td>
                    <td>{$VMX_REPEAT.INPUT}</td>
                    <td nowrap>{$VMX_LOOPS.LABEL}:</td>
                    <td>{$VMX_LOOPS.INPUT}</td>
                </tr>
            </table>
       </div>
    </div>
</div>

{literal}
<script type="text/javascript">
$(document).ready(function(){
    radio("tab-1");
});
</script>
<style type="text/css">
.genaral td, .voicemail td, .sip_settings td, .iax_settings td{
    padding-left: 12px;
}
</style>
{/literal}