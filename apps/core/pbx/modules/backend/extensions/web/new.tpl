<div align="right" style="padding-right: 4px;">
    {if $mode ne 'view'}
    <span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span>
    {/if}
</div>
<div class="neo-table-header-row">
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-1" name="tab-group-1" onclick="radio('tab-1');" checked>
        <label for="tab-1">{$EXTENSION}</label>
    </div>
    {if $mode ne 'input'}
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-2" name="tab-group-2" onclick="radio('tab-2');">
        <label for="tab-2">{$DEVICE}</label>
    </div>
    {/if}
    {if $DIV_VM ne 'no'}
    <div class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-3" name="tab-group-3" onclick="radio('tab-3');">
        <label for="tab-3">{$VOICEMAIL}</label>
    </div>
    {/if}
    <div class="neo-table-header-row-navigation" align="right" style="display: inline-block;">
        {if $mode eq 'input'}
            <input type="submit" name="save_new" value="{$SAVE}" >
        {elseif $mode eq 'edit'}
            {if $EDIT_EXTEN}<input type="submit" name="save_edit" value="{$APPLY_CHANGES}" >{/if}
            {if $DEL_EXTEN}<input type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
        {else}
            {if $EDIT_EXTEN}<input type="submit" name="edit" value="{$EDIT}">{/if}
            {if $DEL_EXTEN}<input type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
        {/if}
        <input type="submit" name="cancel" value="{$CANCEL}">
    </div>
</div>
<div class="tabs">
    <div class="tab">
       <div class="content" id="content_tab-1" style="padding-left: 8px;">
          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm extension">
            {if $USERLEVEL eq 'superadmin'}
                <tr class="extension">
                    <td>{$ORGANIZATION_LABEL}: </td>
                    <td>{$ORGANIZATION}</td>
                </tr>
            {/if}
            <tr class="extension">
                <td width="15%" nowrap>{$exten.LABEL}: {if $mode eq 'input'}<span  class="required">*</span>{/if}</td>
                {if $mode eq 'edit'}
                    <td width="31%">{$EXTEN}</td>
                {else}
                    <td width="31%">{$exten.INPUT}</td>
                {/if}
                {if $USER_EXTEN} 
                    <td width="21%" nowrap>{$secret.LABEL}: {if $mode eq 'input'}<span class="required">*</span>{/if}</td>
                    <td>{$secret.INPUT}</td>
                {/if}
            </tr>
            <tr class="extension">
                <td width="15%" nowrap>{$technology.LABEL}: {if $mode eq 'input'}<span  class="required">*</span>{/if}</td>
                {if $mode eq 'edit'}
                    <td width="31%">{$TECHNOLOGY}</td>
                {else}
                    <td width="31%">{$technology.INPUT}</td>
                {/if}
            </tr>
            <tr>
                <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4>{$EXT_OPTIONS}</td>
            </tr>
            <tr class="extension">
                <td nowrap>{$clid_name.LABEL}:</td>
                <td>{$clid_name.INPUT}</td>
                <td nowrap>{$clid_number.LABEL}:</td>
                <td>{$clid_number.INPUT}</td>
            </tr>
            <tr class="extension">
                <td nowrap>{$out_clid.LABEL}: </td>
                <td>{$out_clid.INPUT}</td>
                <td nowrap>{$language.LABEL}: </td>
                <td>{$language.INPUT}</td>
            </tr>
            <tr class="extension">
                <td nowrap>{$ring_timer.LABEL}: </td>
                <td>{$ring_timer.INPUT}</td>
                <td nowrap>{$call_waiting.LABEL}: </td>
                <td>{$call_waiting.INPUT}</td>
            </tr>
                <tr class="extension">
                <td nowrap>{$screen.LABEL}: </td>
                <td>{$screen.INPUT}</td>
            </tr>
            <tr>
                <td class="extension" style="font-weight: bold;" colspan=4>{$REC_OPTIONS}</td>
            </tr>
            <tr class="extension">
                <td nowrap>{$record_in.LABEL}: </td>
                <td>{$record_in.INPUT}</td>
                <td nowrap>{$record_out.LABEL}: </td>
                <td>{$record_out.INPUT}</td>
            </tr>
            <tr>
                <td class="extension" style="font-weight: bold;" colspan=4>{$DICT_OPTIONS}</td>
            </tr>
            <tr class="extension">
                <td nowrap>{$dictate.LABEL}: </td>
                <td>{$dictate.INPUT}</td>
                <td nowrap>{$dictformat.LABEL}: </td>
                <td>{$dictformat.INPUT}</td>
            </tr>
            <tr class="extension">
                <td nowrap>{$dictemail.LABEL}: </td>
                <td>{$dictemail.INPUT}</td>
            </tr>
         </table>
        </div>       
   </div>
   {if $mode ne 'input'}
   <div class="tab">
       {if $isIax}
        <div class="content" id="content_tab-2" style="padding-left: 8px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
                <tr class="iax_settings">
                    <td width="15%" nowrap>{$context.LABEL}:</td>
                    <td width="31%">{$context.INPUT}</td>
                    <td width="21%" nowrap>{$transfer.LABEL}:</td>
                    <td>{$transfer.INPUT}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$host.LABEL}:</td>
                    <td>{$host.INPUT}</td>
                    <td nowrap>{$type.LABEL}:</td>
                    <td>{$type.INPUT}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$qualify.LABEL}:</td>
                    <td>{$qualify.INPUT}</td>
                    <td nowrap>{$port.LABEL}:</td>
                    <td>{$port.INPUT}</td>
                </tr>
                <tr class="iax_settings">
                    <td width="15%" nowrap>{$disallow.LABEL}:</td>
                    <td width="31%">{$disallow.INPUT}</td>
                    <td width="21%" nowrap>{$allow.LABEL}:</td>
                    <td>{$allow.INPUT}</td>
                </tr>
                <tr class="iax_settings">
                    <td nowrap>{$accountcode.LABEL}:</td>
                    <td>{$accountcode.INPUT}</td>
                    <td nowrap>{$requirecalltoken.LABEL}:</td>
                    <td>{$requirecalltoken.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; font-size: 13px" colspan=4><a href="javascript:void(0);" class="adv_opt"><b>{$ADV_OPTIONS}</b></a></td>
                </tr>
                <tr class="iax_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$username.LABEL}:</td>
                    <td>{$username.INPUT}</td>
                    <td nowrap>{$amaflags.LABEL}:</td>
                    <td>{$amaflags.INPUT}</td>
                </tr>
                <tr class="iax_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$defaultip.LABEL}:</td>
                    <td>{$defaultip.INPUT}</td>
                    <td nowrap>{$mask.LABEL}:</td>
                    <td>{$mask.INPUT}</td>
                </tr>
                <tr class="iax_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$mohinterpret.LABEL}:</td>
                    <td>{$mohinterpret.INPUT}</td>
                    <td nowrap>{$mohsuggest.LABEL}:</td>
                    <td>{$mohsuggest.INPUT}</td>
                </tr>
                <tr class="iax_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$jitterbuffer.LABEL}:</td>
                    <td>{$jitterbuffer.INPUT}</td>
                    <td nowrap>{$forcejitterbuffer.LABEL}:</td>
                    <td>{$forcejitterbuffer.INPUT}</td>
                </tr>
                <tr class="iax_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$codecpriority.LABEL}:</td>
                    <td>{$codecpriority.INPUT}</td>
                    <td nowrap>{$qualifysmoothing.LABEL}:</td>
                    <td>{$qualifysmoothing.INPUT}</td>
                </tr>
                <tr class="iax_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$qualifyfreqok.LABEL}:</td>
                    <td>{$qualifyfreqok.INPUT}</td>
                    <td nowrap>{$qualifyfreqnotok.LABEL}:</td>
                    <td>{$qualifyfreqnotok.INPUT}</td>
                </tr>
                <tr class="iax_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$encryption.LABEL}:</td>
                    <td>{$encryption.INPUT}</td>
                    <td nowrap>{$timezone.LABEL}:</td>
                    <td>{$timezone.INPUT}</td>
                </tr>
                <tr class="iax_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$sendani.LABEL}:</td>
                    <td>{$sendani.INPUT}</td>
                    <td nowrap>{$adsi.LABEL}:</td>
                    <td>{$adsi.INPUT}</td>
                </tr>
            </table>
        </div>
        {else}
        <div id="content_tab-2" class="content" style="padding-left: 8px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
                <tr class="sip_settings">
                    <td width="15%" nowrap>{$context.LABEL}: </td>
                    <td width="31%">{$context.INPUT}</td>
                    <td width="21%" nowrap>{$dtmfmode.LABEL}: </td>
                    <td >{$dtmfmode.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$host.LABEL}: </td>
                    <td>{$host.INPUT}</td>
                    <td nowrap>{$type.LABEL}: </td>
                    <td>{$type.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$port.LABEL}: </td>
                    <td>{$port.INPUT}</td>
                    <td nowrap>{$qualify.LABEL}: </td>
                    <td>{$qualify.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$nat.LABEL}: </td>
                    <td>{$nat.INPUT}</td>
                    <td nowrap>{$accountcode.LABEL}: </td>
                    <td>{$accountcode.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$disallow.LABEL}: </td>
                    <td>{$disallow.INPUT}</td>
                    <td nowrap>{$allow.LABEL}: </td>
                    <td>{$allow.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$mailbox.LABEL}: </td>
                    <td>{$mailbox.INPUT}</td>
                    <td nowrap>{$vmexten.LABEL}: </td>
                    <td>{$vmexten.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$namedcallgroup.LABEL}: </td>
                    <td>{$namedcallgroup.INPUT}</td>
                    <td nowrap>{$namedpickupgroup.LABEL}: </td>
                    <td>{$namedpickupgroup.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$allowtransfer.LABEL}: </td>
                    <td>{$allowtransfer.INPUT}</td>
                    <td nowrap>{$directmedia.LABEL}: </td>
                    <td>{$directmedia.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$trustrpid.LABEL}: </td>
                    <td>{$trustrpid.INPUT}</td>
                    <td nowrap>{$sendrpid.LABEL}: </td>
                    <td>{$sendrpid.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$transport.LABEL}: </td>
                    <td>{$transport.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$callcounter.LABEL}: </td>
                    <td>{$callcounter.INPUT}</td>
                    <td nowrap>{$busylevel.LABEL}: </td>
                    <td>{$busylevel.INPUT}</td>
                </tr>
                <tr class="sip_settings">
                    <td nowrap>{$subscribecontext.LABEL}: </td>
                    <td>{$subscribecontext.INPUT}</td>
                </tr>
                <tr>
                    <td style="padding-left: 2px; font-size: 13px" colspan=4><a href="javascript:void(0);" class="adv_opt"><b>{$ADV_OPTIONS}</b></a></td>
                </tr>
                <tr class="sip_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$username.LABEL}: </td>
                    <td>{$username.INPUT}</td>
                    <td nowrap>{$amaflags.LABEL}: </td>
                    <td>{$amaflags.INPUT}</td>
                </tr>
                <tr class="sip_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$defaultuser.LABEL}: </td>
                    <td>{$defaultuser.INPUT}</td>
                    <td nowrap>{$defaultip.LABEL}: </td>
                    <td>{$defaultip.INPUT}</td>
                </tr>
                <tr class="sip_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$mohinterpret.LABEL}:</td>
                    <td>{$mohinterpret.INPUT}</td>
                    <td nowrap>{$mohsuggest.LABEL}:</td>
                    <td>{$mohsuggest.INPUT}</td>
                </tr>
                <tr class="sip_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$g726nonstandard.LABEL}:</td>
                    <td>{$g726nonstandard.INPUT}</td>
                </tr>
                <tr class="sip_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$videosupport.LABEL}:</td>
                    <td>{$videosupport.INPUT}</td>
                    <td nowrap>{$maxcallbitrate.LABEL}:</td>
                    <td>{$maxcallbitrate.INPUT}</td>
                </tr>
                <tr class="sip_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$qualifyfreq.LABEL}:</td>
                    <td>{$qualifyfreq.INPUT}</td>
                    <td nowrap>{$rtptimeout.LABEL}:</td>
                    <td>{$rtptimeout.INPUT}</td>
                </tr>
                <tr class="sip_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$rtpholdtimeout.LABEL}:</td>
                    <td>{$rtpholdtimeout.INPUT}</td>
                    <td nowrap>{$rtpkeepalive.LABEL}:</td>
                    <td>{$rtpkeepalive.INPUT}</td>
                </tr>
                <tr class="sip_settings show_more" {$SHOW_MORE}>
                    <td nowrap>{$progressinband.LABEL}:</td>
                    <td>{$progressinband.INPUT}</td>
                </tr>
            </table>
        </div>
        {/if}
      </div>  
    {/if}
    {if $DIV_VM ne 'no'}
    <div class="tab">
       <div class="content" id="content_tab-3">
        <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
        <tr>
            <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4><b>{$VM_OPTIONS}</b></td>
        </tr>
        <tr>
            <td style="padding-left: 12px;" colspan=4><input id="create_vm" type="checkbox" class="create_vm" name="create_vm" {$CHECKED} {$VALVM} {if $mode eq 'view'}disabled{/if}/>{$CREATE_VM}</td>
        </tr>
        <tr class="voicemail">
            <td width="15%" nowrap>{$vmpassword.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
            <td width="31%">{$vmpassword.INPUT}</td>
            <td width="15%" nowrap>{$vmemail.LABEL}:</td>
            <td>{$vmemail.INPUT}</td>
        </tr>
        <tr class="voicemail">
            <td nowrap>{$vmattach.LABEL}:</td>
            <td>{$vmattach.INPUT}</td>
            <td nowrap>{$vmsaycid.LABEL}:</td>
            <td>{$vmsaycid.INPUT}</td>
        </tr>
        <tr class="voicemail">
            <td nowrap>{$vmenvelope.LABEL}:</td>
            <td>{$vmenvelope.INPUT}</td>
            <td nowrap>{$vmdelete.LABEL}:</td>
            <td>{$vmdelete.INPUT}</td>
        </tr>
        <tr class="voicemail">
            <td nowrap>{$vmcontext.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
            <td>{$vmcontext.INPUT}</td>
        </tr>
        <tr class="voicemail">
            <td>{$vmemailsubject.LABEL}:</td>
            <td colspan=3>{$vmemailsubject.INPUT}</td>
        </tr>
        <tr class="voicemail">
            <td>{$vmemailbody.LABEL}:</td>
            <td colspan=3>{$vmemailbody.INPUT}</td>
        </tr>
        <tr class="voicemail">
            <td width="15%" nowrap>{$vmoptions.LABEL}: </td> <td colspan=3>{$vmoptions.INPUT}</td>
        </tr>
        <tr>
            <td style="padding-left: 2px; font-size: 13px; color: #E35332; font-weight: bold;" colspan=4><b>{$LOCATOR}</b></td>
        </tr>
        <tr class="voicemail">
            <td nowrap>{$vmx_locator.LABEL}:</td>
            <td>{$vmx_locator.INPUT}</td>
        </tr>
        <tr class="voicemail vm_locator">
            <td nowrap>{$vmx_use.LABEL}:</td>
            <td>{$vmx_use.INPUT}</td>
        </tr>
        <tr class="voicemail">
            <td nowrap>{$vmx_extension_0.LABEL}:</td>
            <td colspan=3>{$vmx_extension_0.INPUT}  {$vmx_operator.INPUT} {$vmx_operator.LABEL}</td>
        </tr>
        <tr class="voicemail vm_locator">
            <td nowrap>{$vmx_extension_1.LABEL}:</td>
            <td>{$vmx_extension_1.INPUT}</td>
        </tr>
        <tr class="voicemail vm_locator">
            <td nowrap>{$vmx_extension_2.LABEL}:</td>
            <td>{$vmx_extension_2.INPUT}</td>
        </tr>
        </table>
     </div>
    </div>
   {/if}
</div>
<input type="hidden" name="mode_input" value="{$mode}">
<input type="hidden" name="id_exten" value="{$id_exten}">
<input type="hidden" name="mostra_adv" id="mostra_adv" value="{$mostra_adv}">
<input type="hidden" name="organization" value="{$ORGANIZATION}">

{literal}
<script type="text/javascript">
$(document).ready(function(){
    radio("tab-1");
});
</script>
<style type="text/css">
.extension td, .voicemail td, .sip_settings td, .iax_settings td{
	padding-left: 12px;
}
</style>
{/literal}
