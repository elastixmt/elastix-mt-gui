<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        <td align="left">
        {if $mode ne 'view'}
            <input class="button" type="submit" name={if $StatusNew}"save_mailmail_admin"{else}"save_newList"{/if} value="{$SAVE}">&nbsp;&nbsp;
        {else}
            {if $DELETE_LIST}<input class="button" type="submit" name="delete" value="{$DELETE}">&nbsp;&nbsp;{/if}
        {/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>

<div class="tabForm" style="font-size: 16px" width="100%">
    {if $StatusNew}
        <div id="mailman_detail">	
            <table border="0" width="100%" cellspacing="0" cellpadding="8" >
                <tr class="letra12">
                    <td align="left" width="23%"><b style="color: rgb(227, 83, 50); font-size: 12px; font-family: 'Lucida Console';">{$Mailman_Setting}</b></td>
                </tr>
                <tr class="letra12">
                    <td align="left"><b>{$emailmailman.LABEL}: <span  class="required">*</span></b></td>
                    <td align="left">{$emailmailman.INPUT}</td>
                </tr>
                <tr class="letra12">
                    <td align="left"><b>{$passwdmailman.LABEL}: <span  class="required">*</span></b></td>
                    <td align="left">{$passwdmailman.INPUT}</td>
                </tr>
                <tr class="letra12">
                    <td align="left"><b>{$repasswdmailman.LABEL}: <span  class="required">*</span></b></td>
                    <td align="left">{$repasswdmailman.INPUT}</td>
                </tr>
            </table>
        </div>
    {else}
        <div id="list_detail">
            <table border="0" width="100%" cellspacing="0" cellpadding="8" >
                <tr class="letra12">
                    <td align="left"><b style="color: rgb(227, 83, 50); font-size: 12px; font-family: 'Lucida Console';">{$List_Setting}</b></td>
                </tr>
                {if $USERLEVEL eq 'superadmin'}
                    <tr class="letra12">
                        <td align="left" width="23%"><b>{$domain.LABEL}: <span  class="required">*</span></b></td>
                        <td align="left">{if $mode eq 'view'}{$DOMAIN}{else}{$domain.INPUT}{/if}</td>
                    </tr>
                {/if}
                <tr class="letra12">
                    <td align="left"><b>{$namelist.LABEL}: <span  class="required">*</span></b></td>
                    <td align="left">{if $mode eq 'view'}{$LIST_NAME}{else}{$namelist.INPUT}{/if}</td>
                </tr>
                <tr class="letra12">
                    <td align="left"><b>{$emailadmin.LABEL}: <span  class="required">*</span></b></td>
                    <td align="left">{if $mode eq 'view'}{$LIST_ADMIN_USER}{else}{$emailadmin.INPUT}{/if}</td>
                </tr>
                {if $mode ne 'view'}
                    <tr class="letra12">
                        <td align="left"><b>{$password.LABEL}: <span  class="required">*</span></b></td>
                        <td align="left">{$password.INPUT}</td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$passwordconfirm.LABEL}: <span  class="required">*</span></b></td>
                        <td align="left">{$passwordconfirm.INPUT}</td>
                    </tr> 
                {/if}
            </table>
        </div>
    {/if}
</div>
<input type='hidden' name='id' value={$idList}>