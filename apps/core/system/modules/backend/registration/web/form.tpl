<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        {if $mode eq 'input'}
        <td align="left">
            <input class="button" type="submit" name="save_new" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'view'}
        <td align="left">
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {elseif $mode eq 'edit'}
        <td align="left">
            <input class="button" type="submit" name="save_edit" value="{$EDIT}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        {/if}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td  align="left" colspan=2;><br /><b style ="color:#E35332; font-weigth:bold;font-size:15px;">{$EXTENSION}</b><br /><br /></td>
    </tr>
    <tr class="letra12">
        <td align="left" width="200px"><b>{$do_not_disturb.LABEL}:</b></td>
        <td align="left">{$do_not_disturb.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$call_waiting.LABEL}:</b></td>
        <td align="left">{$call_waiting.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b style ="color:#E35332; font-weigth:bold;font-size:12px;font-family:'Lucida Console';">Call Forward Configuration</b></td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$call_forward.LABEL}:</b></td>
        <td align="left">{$call_forward.INPUT} {$phone_number_CF.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$call_forward_U.LABEL}:</b></td>
        <td align="left">{$call_forward_U.INPUT} {$phone_number_CFU.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$call_forward_B.LABEL}:</b></td>
        <td align="left">{$call_forward_B.INPUT} {$phone_number_CFB.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b style ="color:#E35332; font-weigth:bold;font-size:12px;font-family:'Lucida Console';">Call Monitor Settings</b></td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$record_incoming.LABEL}:</b></td>
        <td align="left">{$record_incoming.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$record_outgoing.LABEL}:</b></td>
        <td align="left">{$record_outgoing.INPUT}</td>
    </tr>
</table>
<input class="button" type="hidden" name="id" value="{$ID}" />