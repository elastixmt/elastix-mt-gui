<div align="right" style="padding-right: 4px;">
    {if $mode ne 'view'}
    <span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span>
    {/if}
</div>
<div class="neo-table-header-row">
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-1" name="tab-group-1" onclick="radio('tab-1');" checked>
        <label for="tab-1">{$GENERAL}</label>
    </div>
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-2" name="tab-group-2" onclick="radio('tab-2');">
        <label for="tab-2">{$DESTINE}</label>
    </div>
    <div class="neo-table-header-row-navigation" align="right" style="display: inline-block;">
        {if $mode eq 'input'}
            <input type="submit" name="save_new" value="{$SAVE}" >
        {elseif $mode eq 'edit'}
            {if $EDIT_IVR}<input type="submit" name="save_edit" value="{$APPLY_CHANGES}" >{/if}
            {if $DEL_IVR}<input type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
        {else}
            {if $EDIT_IVR}<input type="submit" name="edit" value="{$EDIT}">{/if}
            {if $DEL_IVR}<input type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
        {/if}
        <input type="submit" name="cancel" value="{$CANCEL}">
    </div>
</div>
<div class="tabs">
    <div class="tab" style="width:100%">
        <div class="content" id="content_tab-1">
            <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
                {if $USERLEVEL eq 'superadmin'}
                    <tr class="extension">
                        <td>{$ORGANIZATION_LABEL}: </td>
                        <td>{$ORGANIZATION}</td>
                    </tr>
                {/if}
                <tr class="extension">
                    <td width="20%" nowrap>{$name.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                    <td width="30%">{$name.INPUT}</td>
                    <td width="20%" nowrap>{$announcement.LABEL}: {if $mode ne 'view'}<span class="required">*</span>{/if}</td>
                    <td width="30%">{$announcement.INPUT}</td>
                </tr>
                <tr class="extension">
                    <td nowrap>{$retvm.LABEL}</td>
                    <td ><input type="checkbox" {if $mode eq 'view'} disabled="disabled"{/if} name="retvm" {$CHECKED} ></td>
                    <td>{$directdial.LABEL}</td>
                    <td>{$directdial.INPUT}</td>
                </tr>
                <tr class="extension"> 
                    <td nowrap>{$timeout.LABEL}</td>
                    <td>{$timeout.INPUT}</td>
                    <td nowrap>{$loops.LABEL}</td>
                    <td>{$loops.INPUT}</td>
                <tr>
                <tr class="extension">
                    <td nowrap>{$mesg_timeout.LABEL}</td>
                    <td>{$mesg_timeout.INPUT}</td>
                    <td nowrap>{$mesg_invalid.LABEL}</td>
                    <td>{$mesg_invalid.INPUT}</td>
                </tr>
            </table>
        </div>       
   </div>
     <div class="tab" style="width:100%">
       <div class="content" id="content_tab-2">
        <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm" id="destine" >
            <thead>
                 <tr>
                    <th>{$DIGIT}</th>
                    <th>{$DESTINE}</th>
                    <th>{$OPTION}</th>
                    <th>{$RETIVR}</th>
                    <th>{if $mode ne 'view'}<div class="add" style="cursor:pointer; float: left"><img src='web/apps/ivr/images/add1.png' title='Add'/></div>{/if}</th>
                 </tr>
        </thead>
            {if $mode eq 'view'}
            {foreach from=$items key=myId item=i}
                <tr><td align="center">{$i.1}</td>
                <td align="center" width="40%">{$i.2}</td>
                <td align="center">{assign var=someVar value=","|explode:$i.3}{$someVar.1}</td>
                <td align="center"><input type="CHECKBOX" {if $i.4 eq 'yes' }checked{/if} disabled="disabled"/></td>
                </tr>
            {/foreach}
        {else}
            <tr id="test" style="display:none;">
            <td align="center">{$option__.INPUT} </td>
            <td align="center">{$goto__.INPUT} </td>
            <td align="center">{$destine__.INPUT}</td>
            <td align="center">{$ivrret__.INPUT}</td>
            <td width="50px"><div class='delete' style='float:left; cursor:pointer;'><img src='web/apps/ivr/images/remove1.png' title='Remove'/></div> </td>
            </tr>
            {foreach from=$items key=myId item=i}
            <input type="hidden" value"{$j++}" />
            <tr class="content-destine" id="{$j}"><td align="center" ><input type="text" name="option{$j}" value="{$i.1}" style="width:50px;text-align:center;"></td>
                <td align="center" width="40%">
                    <select name="goto{$j}" id="goto{$j}" class="goto">
                    {foreach from=$arrGoTo key=k item=v}
                        <option value="{$k}" {if $k eq $i.2} selected {/if}>{$v}</option>
                    {/foreach}
                    </select>
                </td>
                <td align="center">
                <input type="hidden" name="optionDestine{$j}" value="{$i.3}" id="optionDestine{$j}" />
                <select name="destine{$j}" id="destine{$j}"></select>
                </td>
                <td align="center" ><input type="CHECKBOX" name="ivrret{$j}" {if $i.4 eq 'yes' }checked{/if} </td>
                <td width="50px">
                    <div class='delete' style='float:left; cursor:pointer;'><img src='web/apps/ivr/images/remove1.png' title='Remove'/></div>     
                </td>
            </tr>
            {/foreach}
        {/if}
       </table>
     </div>       
   </div>
</div>
<div style="display:none" id="terminate">
{foreach from=$arrTerminate key=k item=v}
<option value="{$k}">{$v}</option>
{/foreach}
</div>
<input type="hidden" name="mode_input" id="mode_input" value="{$mode}">
<input type="hidden" name="id_ivr" id="id_ivr" value="{$id_ivr}">
<input type="hidden" name="mostra_adv" id="mostra_adv" value="{$mostra_adv}">
<input type="hidden" name="arrDestine"  id="arrDestine" value="">
<input type="hidden" name="index"  id="index" value="{$j+1}">
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
