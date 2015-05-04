<div>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
            {if $mode eq 'input'}
                <input class="button" type="submit" name="save_new" value="{$SAVE}" >
            {elseif $mode eq 'edit'}
                {if $EDIT_CONF}<input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}">{/if}
            {else}
                {if $EDIT_CONF}<input class="button" type="submit" name="edit" value="{$EDIT}">{/if}
            {/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        {if $mode ne 'view'}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
        {/if}
     </tr>
   </table>
</div>
<table width="99%" cellpadding="4" cellspacing="0" border="0" class="tabForm">
    {if $USERLEVEL eq 'superadmin'}
        <tr class="extension">
            <td><b>{$ORGANIZATION_LABEL}: </b></td>
            <td>{$ORGANIZATION}</td>
        </tr>
    {/if}
    <tr>
        <td align="left" width="20%"><b>{$name.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
        <td class="required" align="left">{$name.INPUT}</td>
        <td align="left"><b>{$confno.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
        {if $mode ne edit}
            <td align="left">{$confno.INPUT}</td>
        {else}
            <td align="left">{$CONFNO}</td>
        {/if}
    </tr>
    <tr>
        <td align="left"><b>{$adminpin.LABEL}: </b></td>
        <td align="left">{$adminpin.INPUT}</td>
        <td align="left"><b>{$pin.LABEL}: </b></td>
        <td align="left">{$pin.INPUT}</td>
    </tr>
    <tr>
        <td align="left"><b>{$maxusers.LABEL}: </b></td>
        <td align="left">{$maxusers.INPUT}</td>
    </tr>
    {if $mode ne 'view'}
        <td colspan="4">{$schedule.INPUT} <b>{$schedule.LABEL}</b></td>
    {/if}
    {if $SCHEDULE eq 'on' || $mode ne 'view'}
        <tr class="schedule">
            <td align="left"><b>{$start_time.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
            <td align="left">{$start_time.INPUT}</td>
            <td align="left"><b>{$duration.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</b></td>
            <td align="left">
                {$duration.INPUT}&nbsp;:
                {$duration_min.INPUT}
            </td>
        </tr>
    {/if}
    <tr>
        <td align="left"><b>{$moh.LABEL}: </b></td>
        <td align="left">{$moh.INPUT}</td>
        <td align="left"><b>{$announce_intro.LABEL}: </b></td>
        <td align="left">{$announce_intro.INPUT}</td>
    </tr>
    <tr>
        <td align="left"><b>{$record_conf.LABEL}: </b></td>
        <td align="left">{$record_conf.INPUT}</td>
    </tr>
    <tr>
        <td align="left"><b>{$moderator_options_1.LABEL}: </b></td>
        <td align="left" colspan="3">
            {$moderator_options_1.INPUT}{$announce}&nbsp;&nbsp;&nbsp;
        </td>
    </tr>
    <tr>
        <td align="left"><b>{$user_options_1.LABEL}: </b></td>
        <td align="left" colspan="3">
            {$user_options_1.INPUT}{$announce}&nbsp;&nbsp;&nbsp;
            {$user_options_2.INPUT}{$listen_only}&nbsp;&nbsp;&nbsp;
            {$user_options_3.INPUT}{$wait_for_leader}
        </td>
    </tr>
</table>
<input type="hidden" name="id_conf" id="id_conf" value="{$id_conf}">
<input type="hidden" name="mode_input" id="mode_input" value="{$mode}">
<input type="hidden" name="organization" id="organization" value="{$ORGANIZATION}">