<div>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
            {if $mode eq 'input'}
                <input class="button" type="submit" name="save_new" value="{$SAVE}" >
            {elseif $mode eq 'edit'}
                <input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}" >
            {elseif $mode eq 'view'}
                <input class="button" type="submit" name="edit" value="{$EDIT}">
                <input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">
            {/if}
		  <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
		{if $mode ne 'view'}
		<td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
		{/if}
     </tr>
   </table>
</div>
<p style="margin:center; padding:5px 15px 5px 15px;">{$MESSAGE_DID}</p>
<table width="100%" border="0" cellspacing="0" cellpadding="5" class="tabForm" id="tab_did">
    <tr>
		<td width="15%" nowrap>{$did.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
		{if $mode eq 'edit'}
			<td align="left" width="20%">{$DID}</td>
		{else}
			<td width="20%">{$did.INPUT}</td>
		{/if}
            <td></td>
            <td></td>
    </tr>
    </tr>
        <tr>
        <td nowrap>{$type.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
        {if $mode eq 'edit'}
            <td align="left">{$TYPE}</td>
        {else}
            <td align="left">{$type.INPUT}</td>
        {/if}
    </tr>
    {if $mode eq 'view'}
        <tr style="{$DISPLAY_analog}" class="type_did">
            <td width="15%" nowrap>{$channel.LABEL}: </td>
            <td width="20%"> {$CHANNELS} </td>
            <td ></td>
        </tr>
    {else}
        <tr style="{$DISPLAY_analog}" class="type_did">
            <td width="15%" valign="top" nowrap>{$channel.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
            <td colspam="3" width="10%" valign="top">{$channel.INPUT} &nbsp;&nbsp;&nbsp;
                <input class="button" name="remove" id="remove" value="<<" onclick="javascript:quitar_channel();" type="button">
                <select name="arr_channel" size="4" id="arr_channel" style="width: 120px;">
                </select>
                <input type="hidden" id="select_chans" name="select_chans" value={$CHANNELS}>
            </td>
        </tr>
    {/if}
    <tr><th>{$DID_PARAMETERS}</th></tr>
    <tr>
        <td >{$country.LABEL}:</td>
        <td align="left">{$country.INPUT}</td>
    </tr>
    <tr>
        <td >{$city.LABEL}:</td>
        <td align="left">{$city.INPUT}</td>
    </tr>
    <tr>
        <td >{$country_code.LABEL}:</td>
        <td align="left">{$country_code.INPUT} </td>
    </tr>
    <tr>
        <td >{$area_code.LABEL}:</td>
        <td align="left">{$area_code.INPUT} </td>
</table>
<input type="hidden" name="id_did" value="{$id_did}">
{literal}
<script type="text/javascript">
$(document).ready(function(){
    mostrar_select_chans();
});
</script>
{/literal}