<div>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
        {if $mode eq 'input'}
            <input class="button" type="submit" name="save_new" value="{$SAVE}" >
        {elseif $mode eq 'edit'}
            {if $EDIT_MOH}<input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}">{/if}
            {if $DEL_MOH}<input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
        {else}
            {if $EDIT_MOH}<input class="button" type="submit" name="edit" value="{$EDIT}">{/if}
            {if $DEL_MOH}<input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
        {/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        {if $mode ne 'view'}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
        {/if}
     </tr>
   </table>
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
    <tr class="music">
        <td width="20%" nowrap>{$name.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
        {if $mode eq 'edit'}
            <td>{$NAME_MOH}</td>
        {else}
            <td>{$name.INPUT}</td>
        {/if}
    </tr>
    <tr class="music">
        <td nowrap>{$mode_moh.LABEL}: </td>
        {if $mode eq 'edit'}
            <td >{$MODE_MOH} </td>
        {else}
            <td>{$mode_moh.INPUT}</td>
        {/if}
    </tr>
    <tr class="music sort">
        <td width="20%" nowrap>{$sort.LABEL}: </td>
        <td>{$sort.INPUT}</td>
    </tr>
    <tr class="music application">
        <td nowrap>{$application.LABEL}: </td>
        <td>{$application.INPUT}</td>
    </tr>
    <tr class="music application">
        <td nowrap>{$format.LABEL}: </td>
        <td>{$format.INPUT}</td>
    </tr>
    
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm" id="files">
    {if $mode eq 'view'}
        {foreach from=$items key=myId item=i}
            <tr class="music">
                <td width="20%" nowrap> Filename: </td>
                <td > {$i} </td>             
            </tr>
        {/foreach}
    {else}
        <tr id="test" style="display:none;">
            <td width="20%" nowrap> <label for="file__">Filename:</label> </td> 
            <td > <input type="file" class='file_upload' name="file[]" style='width=400px'/> <input type="image" class='delete' src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove' /></td>
        </tr>
        {if count($items) eq 0}
            <tr class="content-files" id="1">
                <td width="20%" nowrap> <label for="file">Filename:</label> </td>
                <td ><input type="file" class='file_upload' name="file[]" id="file1" /> <input type="image" class='delete' src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove' /></td>
            </tr>
        {else}
            {foreach from=$items item=i}
                <input type="hidden" value"{$j++}" />
                <tr class="content-files" id="{$j}">
                    <td width="20%" nowrap> <label for="file{$j}">Filename:</label> </td> 
                    <td> <input type="hidden" name="current_File[]" value="{$i}"> {$i} <input type="image" class='delete'  src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove' /> </td>
                </tr>
            {/foreach}
        {/if}
    {/if}
    {if $mode ne 'view'}
        <tr class="music add_file">
            <td nowrap><input type="button" name="add" value="{$ADD_FILE}" class="button" id="add_file"/>  </td>
        </tr>
    {/if}
</table>

<input type="hidden" name="id_moh" id="id_moh" value="{$id_moh}">
<input type="hidden" name="index"  id="index" value="{$j+1}">
<input type="hidden" name="arrFiles"  id="arrFiles" value="{$arrFiles}">
<input type="hidden" name="mostra_adv" id="mostra_adv" value="{$mostra_adv}">
<input type="hidden" name="mode_input" id="mode_input" value="{$mode}">
<input type="hidden" name="moh_mode" id="moh_mode" value="{$MODE_MOH}">
<input type='hidden' name='max_size' id='max_size' value='{$max_size}' />
<input type='hidden' name='alert_max_size' id='alert_max_size' value='{$alert_max_size}' />

{literal}
<style type="text/css">
.music td {
    padding-left: 12px;
}
.content-files td {
    padding-left: 12px;
}
</style>
{/literal}
