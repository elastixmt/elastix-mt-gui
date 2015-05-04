<div align="right" style="padding-right: 4px;">
    {if $mode ne 'view'}
    <span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span>
    {/if}
</div>
<div class="neo-table-header-row">
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-1" name="tab-group-1" onclick="radio('tab-1');">
        <label for="tab-1">{$GENERAL}
    </div>
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-2" name="tab-group-2" onclick="radio('tab-2');">
        <label for="tab-2">{$SIP_GENERAL}
    </div>
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-3" name="tab-group-3" onclick="radio('tab-3');">
        <label for="tab-3">{$IAX_GENERAL}
    </div>
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-4" name="tab-group-4" onclick="radio('tab-4');">
        <label for="tab-4">{$VM_GENERAL}
    </div>
    <div class="neo-table-header-row-navigation" align="right" style="display: inline-block;">
        {if $EDIT_GS}<input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}" >{/if}
        {if $EDIT_GS}<input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>{/if}
    </div>
</div>
<div class="tabs">
  <div class="tab" >
    <div class="content" id="content_tab-1" style="padding-left: 8px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
            <tr class="sip_settings">
                <td >{$gen_LANGUAGE.LABEL}: </td>
                <td>{$gen_LANGUAGE.INPUT}</td>
            </tr>
            <th class="sip_settings">{$CODEC}</th>
            <tr class="sip_settings">
                <td width="15%">{$gen_audio_codec.LABEL}:
                <td width="30%">
                    <ul id="audio_codec">
                        {foreach from=$audioCodec item=value}
                            <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><input type="checkbox" name="audioCodec[]" id="{$value.name}" value="{$value.name}" {$value.check}/>{$value.name}</li>
                        {/foreach}
                    </ul>
                </td>
                <td width="15%">{$gen_video_codec.LABEL}:
                <td class="sip_settings">
                    <ul id="video_codec">
                        {foreach from=$videoCodec item=value}
                            <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><input type="checkbox" name="videoCodec[]" id="{$value.name}" value="{$value.name}" {$value.check}/>{$value.name}</li>
                        {/foreach}
                    </ul>
                </td>
            </tr>
        </table>
    </div>
    <div class="content" id="content_tab-2" style="padding-left: 8px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
            <tr class="sip_settings">
                <td width="15%">{$sip_default_context.LABEL}: </td>
                <td width="30%">{$sip_default_context.INPUT}</td>
                <td width="15%"> </td>
                <td ></td>
            </tr>
            <tr class="sip_settings">
                <td >{$sip_allowguest.LABEL}: </td>
                <td >{$sip_allowguest.INPUT}</td>
            </tr>
            <tr class="sip_settings">
                <td >{$sip_allowoverlap.LABEL}: </td>
                <td>{$sip_allowoverlap.INPUT}</td>
            </tr>
            <tr class="sip_settings">
                <td >{$sip_allowtransfer.LABEL}: </td>
                <td>{$sip_allowtransfer.INPUT}</td>
            </tr>
            <tr class="sip_settings">
                <td >{$sip_transport.LABEL}: </td>
                <td>{$sip_transport.INPUT}</td>
            </tr>
            <tr class="sip_settings">
                <td >{$sip_srvlookup.LABEL}: </td>
                <td>{$sip_srvlookup.INPUT}</td>
            </tr>
            <tr class="sip_settings">
                <td >{$sip_vmexten.LABEL}: </td>
                <td>{$sip_vmexten.INPUT}</td>
            </tr>
            <th class="sip_settings">{$REGIS_TIMERS}</th>
            <tr class="sip_settings">
                <td >{$sip_maxexpiry.LABEL}: </td>
                <td>{$sip_maxexpiry.INPUT}</td>
                <td >{$sip_minexpiry.LABEL}:</td>
                <td>{$sip_minexpiry.INPUT}</td>
            </tr>
            <tr class="sip_settings">
                <td >{$sip_defaultexpiry.LABEL}: </td>
                <td>{$sip_defaultexpiry.INPUT}</td>
            </tr>
            <tr class="sip_settings">
                <td >{$sip_qualifyfreq.LABEL}: </td>
                <td>{$sip_qualifyfreq.INPUT}</td>
                <td >{$sip_qualifygap.LABEL}:</td>
                <td>{$sip_qualifygap.INPUT}</td>
            </tr>
            <th class="sip_settings" colspan="4">{$OUT_REGIS_TIMERS}</th>
            <tr class="sip_settings">
                <td >{$sip_registertimeout.LABEL}: </td>
                <td>{$sip_registertimeout.INPUT}</td>
                <td >{$sip_registerattempts.LABEL}:</td>
                <td>{$sip_registerattempts.INPUT}</td>
            </tr>
            <th class="sip_settings">{$RTP_TIMERS}</th>
            <tr class="sip_settings" >
                <td >{$sip_rtptimeout.LABEL}:</td>
                <td>{$sip_rtptimeout.INPUT}</td>
                <td >{$sip_rtpholdtimeout.LABEL}:</td>
                <td>{$sip_rtpholdtimeout.INPUT}</td>
            </tr>
            <tr class="sip_settings">
                <td >{$sip_rtpkeepalive.LABEL}:</td>
                <td>{$sip_rtpkeepalive.INPUT}</td>
            </tr>
            <th class="sip_settings">{$VIDEO_OPTS}</th>
            <tr class="sip_settings">
                <td >{$sip_videosupport.LABEL}:</td>
                <td>{$sip_videosupport.INPUT}</td>
                <td >{$sip_maxcallbitrate.LABEL}:</td>
                <td>{$sip_maxcallbitrate.INPUT}</td>
            </tr>
            <th class="sip_settings">{$FAX}</th>
            <tr class="sip_settings" >
                <td >{$sip_faxdetect.LABEL}:</td>
                <td>{$sip_faxdetect.INPUT}</td>
                <td >{$sip_t38pt_udptl.LABEL}:</td>
                <td>{$sip_t38pt_udptl.INPUT}</td>
            </tr>
            <th class="sip_settings">{$NAT}</th>
            <tr class="sip_settings" >
                <td >{$sip_nat.LABEL}:</td>
                <td>{$sip_nat.INPUT}</td>
            </tr>
            <tr class="sip_settings" >
                <td >{$sip_nat_type.LABEL}:</td>
                <td>{$sip_nat_type.INPUT}</td>
            </tr>
            {foreach from=$localnetIP key=i item=prop}
                <tr class="sip_settings nat_param">
                    <td >{$sip_localnetip.LABEL}:</td>
                    <td colspan=3><input type="text" name="localnetip[]" value="{$prop}"></input> / <input type="text" name="localnetmask[]" value="{$localnetMASK.$i}"></input>
                    {if $i eq 0}
                        <img src='web/apps/{$MODULE_NAME}/images/add1.png' title='Add' class="add_local" id="custom_sip"/>
                    {else}
                        <img src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove' class="remove_local"/> 
                    {/if}
                    </td>
                </tr>
            {/foreach}
            <tr class="sip_settings nat_param static_conf">
                <td >{$sip_externaddr.LABEL}:</td>
                <td>{$sip_externaddr.INPUT} </td>
            </tr>
            <tr class="sip_settings nat_param dynamic_conf">
                <td >{$sip_externhost.LABEL}:</td>
                <td colspan=3>{$sip_externhost.INPUT} {$sip_externrefresh.INPUT} {$sip_externrefresh.LABEL}</td>
            </tr>
            <th class="sip_settings">{$MEDIA_HANDLING}</th>
            <tr class="sip_settings">
                <td >{$sip_directmedia.LABEL}:</td>
                <td>{$sip_directmedia.INPUT}</td>
            </tr>
            <th class="sip_settings">{$STATUS_NOTIFICATIONS}</th>
            <tr class="sip_settings" >
                <td >{$sip_notifyringing.LABEL}:</td>
                <td>{$sip_notifyringing.INPUT}</td>
                <td >{$sip_notifyhold.LABEL}:</td>
                <td>{$sip_notifyhold.INPUT}</td>
            </tr>
            <th class="sip_settings">{$ADVANCED}</th>
            <tr class="sip_settings" >
                <td >{$sip_dtmfmode.LABEL}:</td>
                <td>{$sip_dtmfmode.INPUT}</td>
                <td >{$sip_relaxdtmf.LABEL}:</td>
                <td>{$sip_relaxdtmf.INPUT}</td>
            </tr>
            <tr class="sip_settings" >
                <td >{$sip_trustrpid.LABEL}:</td>
                <td>{$sip_trustrpid.INPUT}</td>
                <td >{$sip_sendrpid.LABEL}:</td>
                <td>{$sip_sendrpid.INPUT}</td>
            </tr> 
            <tr class="sip_settings">
                <td >{$sip_useragent.LABEL}:</td>
                <td>{$sip_useragent.INPUT}</td>
            </tr>
            <tr class="sip_settings" >
                <td >{$sip_contactdeny.LABEL}:</td>
                <td>{$sip_contactdeny.INPUT}</td>
                <td >{$sip_contactpermit.LABEL}:</td>
                <td>{$sip_contactpermit.INPUT}</td>
            </tr>
            <th class="sip_settings">{$CUSTOM_SET}</th>
            {foreach from=$sipCustom key=i item=prop}
                <tr class="sip_settings" >
                    <td colspan=3><input type="text" name="sip_custom_name[]" value="{$prop.name}"></input> = <input type="text" name="sip_custom_val[]" value="{$prop.value}"></input>
                    {if $i eq 0}
                        <img src='web/apps/{$MODULE_NAME}/images/add1.png' title='Add' class="add_prop" id="custom_sip"/>
                    {else}
                        <img src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove' class="remove_prop_sip"/> 
                    {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
    <div class="content" id="content_tab-3" style="padding-left: 8px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
            <tr class="iax_settings">
                <td width="20%" >{$iax_delayreject.LABEL}:</td>
                <td width="31%">{$iax_delayreject.INPUT}</td>
                <td colspan=2 width="31%"></td>
            </tr>
            <tr class="iax_settings">
                <td>{$iax_bindport.LABEL}:</td>
                <td>{$iax_bindport.INPUT}</td>
            </tr>
            <tr class="iax_settings">
                <td width="20%" >{$iax_bindaddr.LABEL}:</td>
                <td width="31%">{$iax_bindaddr.INPUT}</td>
            </tr>
            <th class="iax_settings">{$CODEC}</th>
            <tr class="iax_settings">
                <td >{$iax_codecpriority.LABEL}:</td>
                <td>{$iax_codecpriority.INPUT}</td>
            </tr>
            <tr class="iax_settings">
                <td >{$iax_bandwidth.LABEL}:</td>
                <td>{$iax_bandwidth.INPUT}</td>
            </tr>
            <th class="iax_settings">{$JITTER}</th>
            <tr class="iax_settings">
                <td >{$iax_jitterbuffer.LABEL}:</td>
                <td>{$iax_jitterbuffer.INPUT}</td>
            </tr>
            <tr class="iax_settings iax_jitter">
                <td >{$iax_forcejitterbuffer.LABEL}:</td>
                <td>{$iax_forcejitterbuffer.INPUT}</td>
            </tr>
            <tr class="iax_settings iax_jitter">
                <td >{$iax_maxjitterbuffer.LABEL}:</td>
                <td>{$iax_maxjitterbuffer.INPUT}</td>
                <td >{$iax_resyncthreshold.LABEL}:</td>
                <td>{$iax_resyncthreshold.INPUT}</td>
            </tr>
            <tr class="iax_settings iax_jitter">
                <td >{$iax_maxjitterinterps.LABEL}:</td>
                <td>{$iax_maxjitterinterps.INPUT}</td>
            </tr>
            <th class="iax_settings">{$CUSTOM_SET}</th>
            {foreach from=$iaxCustom key=i item=prop}
                <tr class="iax_settings" >
                    <td colspan="4"><input type="text" name="iax_custom_name[]" value="{$prop.name}"></input> = <input type="text" name="iax_custom_val[]" value="{$prop.value}"></input>
                    {if $i eq 0}
                        <img src='web/apps/{$MODULE_NAME}/images/add1.png' title='Add' class="add_prop" id="custom_iax"/>
                    {else}
                        <img class="remove_prop_iax" src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove'/> 
                    {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
    <div class="content" id="content_tab-4" style="padding-left: 8px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
            <tr class="vm_settings">
                <td width="20%">{$vm_attach.LABEL}:</td>
                <td>{$vm_attach.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_forcename.LABEL}:</td>
                <td>{$vm_forcename.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_envelope.LABEL}:</td>
                <td>{$vm_envelope.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_maxmsg.LABEL}:</td>
                <td>{$vm_maxmsg.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_maxlogins.LABEL}:</td>
                <td>{$vm_maxlogins.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_moveheard.LABEL}:</td>
                <td>{$vm_moveheard.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_nextaftercmd.LABEL}:</td>
                <td>{$vm_nextaftercmd.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_operator.LABEL}:</td>
                <td>{$vm_operator.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_review.LABEL}:</td>
                <td>{$vm_review.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_saycid.LABEL}:</td>
                <td>{$vm_saycid.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_sayduration.LABEL}:</td>
                <td>{$vm_sayduration.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_tempgreetwarn.LABEL}:</td>
                <td>{$vm_tempgreetwarn.INPUT}</td>
            </tr>
            <tr><th>{$EMAIL_VM}</th></tr>
            <tr class="vm_settings">
                <td>{$vm_serveremail.LABEL}:</td>
                <td>{$vm_serveremail.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_fromstring.LABEL}:</td>
                <td>{$vm_fromstring.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_emailsubject.LABEL}:</td>
                <td>{$vm_emailsubject.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_emailbody.LABEL}:</td>
                <td>{$vm_emailbody.INPUT}</td>
            </tr>
            <tr><th>{$LOCATION_VM}</th></tr>
            <tr class="vm_settings">
                <td>{$vm_tz.LABEL}:</td>
                <td>{$vm_tz.INPUT}</td>
            </tr>
            <tr><th>{$ADVANCED}</th></tr>
            <tr class="vm_settings">
                <td>{$vm_maxsecs.LABEL}:</td>
                <td>{$vm_maxsecs.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_minsecs.LABEL}:</td>
                <td>{$vm_minsecs.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_maxgreet.LABEL}:</td>
                <td>{$vm_maxgreet.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_skipms.LABEL}:</td>
                <td>{$vm_skipms.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_maxsilence.LABEL}:</td>
                <td>{$vm_maxsilence.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_silencethreshold.LABEL}:</td>
                <td>{$vm_silencethreshold.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_volgain.LABEL}:</td>
                <td>{$vm_volgain.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_forward_urgent_auto.LABEL}:</td>
                <td>{$vm_forward_urgent_auto.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_externpasscheck.LABEL}:</td>
                <td>{$vm_externpasscheck.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_minpassword.LABEL}:</td>
                <td>{$vm_minpassword.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_odbcstorage.LABEL}:</td>
                <td>{$vm_odbcstorage.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_odbctable.LABEL}:</td>
                <td>{$vm_odbctable.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_mailcmd.LABEL}:</td>
                <td>{$vm_mailcmd.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_pollmailboxes.LABEL}:</td>
                <td>{$vm_pollmailboxes.INPUT}</td>
            </tr>
            <tr class="vm_settings">
                <td>{$vm_pollfreq.LABEL}:</td>
                <td>{$vm_pollfreq.INPUT}</td>
            </tr>
        </table>
    </div>
  </div>
</div>
<input type="hidden" name="mod_name" value="{$MODULE_NAME}">
{literal}
<script type="text/javascript">
$(document).ready(function(){
    $("div.neo-module-content").attr("style","");
    radio("tab-1");
});
</script>
<style type="text/css">
.general td, .sip_settings td, .iax_settings td, .vm_settings td{
    padding-left: 12px;
}
</style>
{/literal}