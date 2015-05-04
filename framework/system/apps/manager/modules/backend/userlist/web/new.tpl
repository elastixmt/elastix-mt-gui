<div>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          {if $mode eq 'input'}
            {if $CREATE_USER}<input class="button" type="submit" name="save_new" value="{$SAVE}" >{/if}
          {elseif $mode eq 'edit'}
            {if $EDIT_USER}<input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}" >{/if}
          {elseif $mode eq 'view'}
            {if $EDIT_USER}<input class="button" type="submit" name="edit" value="{$EDIT}">{/if}
            {if $DEL_USER}<input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
          {/if}
		  <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
		{if $mode ne 'view'}
		<td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
		{/if}
     </tr>
   </table>
</div>
<div style="display: inline-block; width: 270px; height: 270px; position: absolute;">
    {if $ShowImg}
        <div class="div-image" {$MARGIN_PIC}>
            <img class="picture" alt="image" src="index.php?menu={$MODULE_NAME}&action=getImage&ID={$id_user}&rawmode=yes"/>
        </div>
    {else}
        <img alt="image" style="margin-top: 20px; height: 256px; width: 256px;" src="web/apps/{$MODULE_NAME}/images/Icon-user.png"/>
    {/if}
</div>
<div style="display: inline-block; position: relative; left: 280px; height: {$HEIGHT}; {$MARGIN_TAB}">
<div id="formulario">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
		<td width="19%" nowrap>{$username.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
		{if $mode eq 'edit'}
			<td width="31%">{$USERNAME}</td>
		{else}
			<td width="31%">{$username.INPUT}</td>
		{/if}
		<td width="21%" nowrap>{$name.LABEL}: </td>
		<td >{$name.INPUT}</td>
      </tr>
	  <tr>
		<td nowrap>{$password1.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
		<td>{$password1.INPUT}</td>
		<td nowrap>{$password2.LABEL}: {if $mode ne 'view'}<span class="required">*</span>{/if}</td>
		<td>{$password2.INPUT}</td>
      </tr>
	{if !$isSuperAdmin}
	  <tr>
		<td nowrap>{$organization.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
		{if $mode ne 'edit' && $userLevel eq 'superadmin'}
			<td>{$organization.INPUT}</td>
		{else}
			<td>{$ORGANIZATION}</td>
		{/if}
		<td nowrap>{$group.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
		{if $userLevel eq 'other' || $isSuperAdmin}
			<td>{$GROUP}</td>
		{else}
			<td>{$group.INPUT}</td>
		{/if}
      </tr>
	  <tr>
		<td nowrap>{$extension.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
		{if $userLevel eq 'superadmin' || $userLevel eq 'admin'}
			<td>{$extension.INPUT}</td>
		{else}
			<td>{$EXTENSION}</td>
		{/if}
		<td nowrap>{$fax_extension.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
		{if $userLevel eq 'superadmin' || $userLevel eq 'admin'}
			<td>{$fax_extension.INPUT}</td>
		{else}
			<td>{$FAX_EXTENSION}</td>
		{/if}
      </tr>
	  <tr>
		<td style="padding-left: 5px;" colspan="4"><b>{$FAX_SETTINGS}</b></td>
	  </tr>
	  <tr>
		<td nowrap>{$clid_name.LABEL}:</td>
		<td>{$clid_name.INPUT}</td>
		<td nowrap>{$clid_number.LABEL}:</td>
		<td>{$clid_number.INPUT}</td>
      </tr>
      <tr>
		<td nowrap>{$country_code.LABEL}: </td>
		<td >{$country_code.INPUT}</td>
		<td nowrap>{$area_code.LABEL}: </td>
		<td>{$area_code.INPUT}</td>
	  </tr>
	  <tr>
		<td style="padding-left: 5px;" colspan="4"><b>{$EMAIL_SETTINGS}</b></td>
	  </tr>
	  <tr>
		<td nowrap>{$email_quota.LABEL}: </td>
		{if $userLevel eq 'superadmin' || $userLevel eq 'admin'}
			<td>{$email_quota.INPUT}</td>
		{else}
			<td>{$EMAILQOUTA}</td>
		{/if}
	  </tr>
	{/if}
	{if $isSuperAdmin}
		<td nowrap>{$email_contact.LABEL}: {if $mode ne 'view'}<span class="required">*</span>{/if}</td>
		<td colspan="3">{$email_contact.INPUT}</td>
	{/if}
	{if $mode ne 'view'}
	  <tr>
		<td nowrap><b>{$picture.LABEL}:</b></td>
		<td colspan="3">{$picture.INPUT}</td>
	  </tr>
	{/if}
	</table>
</div>
</div>
<input type="hidden" name="id" value="{$id_user}">
<input type="hidden" name="idOrgz" value="{$idOrgz}">



