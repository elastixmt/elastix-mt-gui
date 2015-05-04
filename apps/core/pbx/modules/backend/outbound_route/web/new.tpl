<div align="right" style="padding-right: 4px;">
    {if $mode ne 'view'}
    <span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span>
    {/if}
</div>
<div class="neo-table-header-row">
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-1" name="tab-group-1" onclick="radio('tab-1');" checked>
        <label for="tab-1">{$SETTINGS}</label>
    </div>
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-2" name="tab-group-2" onclick="radio('tab-2');">
        <label for="tab-2">{$RULES}</label>
    </div>
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-3" name="tab-group-2" onclick="radio('tab-3');">
        <label for="tab-3">{$TRUNK_SEQUENCE}</label>
    </div>
    <div class="neo-table-header-row-navigation" align="right" style="display: inline-block;">
        {if $mode eq 'input'}
            <input type="submit" name="save_new" value="{$SAVE}" >
        {elseif $mode eq 'edit'}
            {if $EDIT_ROUTE}<input type="submit" name="save_edit" value="{$APPLY_CHANGES}">{/if}
            {if $DELETE_ROUTE}<input type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
        {else}
            {if $EDIT_ROUTE}<input type="submit" name="edit" value="{$EDIT}">{/if}
            {if $DELETE_ROUTE}<input type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
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
                <td width="20%" nowrap>{$routename.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                <td width="30%">{$routename.INPUT}</td>
                <td width="20%" nowrap>{$outcid.LABEL}: </td>
                <td width="20%">{$outcid.INPUT}</td>
                <td align ="right" width="3%"><input type="checkbox" name="over_exten" {$CHECKED_MODE}   {if $mode eq 'view'}DISABLED{/if}/></td>
                <td width="20%">{$OVEREXTEN}</td>
            </tr>
            <tr class="extension">
                <td>{$routepass.LABEL}</td>
                <td>{$routepass.INPUT}</td>
                <td>{$mohsilence.LABEL}</td>
                <td>{$mohsilence.INPUT}</td>  
            </tr>
            <tr class="extension">
                <td>{$time_group_id.LABEL}</td>
                <td>{$time_group_id.INPUT}</td>
            </tr>
         </table>
       </div>       
   </div>
    <div class="tab" style="width:100%">
      <div class="content" id="content_tab-2">
        <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm" id="destine" >
            <thead>
                <tr><td align="center">{$dialoutprefix.LABEL}</td><td>{if $mode ne 'view'}{$dialoutprefix.INPUT}{else}{$DIALOUTPREFIX}{/if}</td></tr>
                <tr>
                    <th>{$PREPEND}</th>
                    <th></th>  
                    <th>{$PREFIX}</th>
                    <th></th>
                    <th>{$MATCH_PATTERN}</th>
                    <th></th>
                    <th>{$CALLERID}</th>
                    <th>{if $mode ne 'view'}<div class="add" style="cursor:pointer; float: left"><img src='web/apps/ivr/images/add1.png' title='Add'/></div>{/if}</th>
                 </tr>
            </thead>
            {if $mode eq 'view'}
                {foreach from=$items key=myId item=i}
                  <tr>
                    <td align="center">( {$i.1} )</td>
                    <td align="center">+</td>
                    <td align="center">{$i.2}</td>
                    <td align="center">|</td>
                    <td align="center">[ {$i.3}</td>
                    <td align="center">/</td>
                    <td align="center">{$i.4} ]</td>			 
		         </tr>
		        {/foreach}
            {else}
                <tr id="test" style="display:none;">
                   <td align="center">( {$prepend_digit__.INPUT} )</td>
                    <td align="center">+</td>
                    <td align="center">{$pattern_prefix__.INPUT}</td>
                    <td align="center">|</td>
                    <td align="center">[ {$pattern_pass__.INPUT}</td>
                    <td align="center">/</td>
                    <td align="center">{$match_cid__.INPUT} ]</td>
                    <td width="50px"><div class='delete' style='float:left; cursor:pointer;'><img src='web/apps/ivr/images/remove1.png' title='Remove'/></div></td>
                </tr>
                {foreach from=$items key=myId item=i}
                <input type="hidden" value"{$j++}" />
                <tr class="content-destine" id="{$j}">
                    <td align="center" >( <input type="text" name="prepend_digit{$j}" value="{$i.1}" style="width:60px;text-align:center;"> )</td>
                    <td align="center">+</td>
                    <td align="center" ><input type="text" name="pattern_prefix{$j}" value="{$i.2}" style="width:30px;text-align:center;"></td>
                    <td align="center">|</td>
                    <td align="center" >[ <input type="text" name="pattern_pass{$j}" value="{$i.3}" style="width:150px;text-align:center;"></td>
                    <td align="center">/</td>
                    <td align="center" ><input type="text" name="match_cid{$j}" value="{$i.4}" style="width:150px;text-align:center;"> ]</td>
                    <td width="50px"><div class='delete' style='float:left; cursor:pointer;'><img src='web/apps/ivr/images/remove1.png' title='Remove'/></div></td>
                </tr>
		       {/foreach}
            {/if}
        </table>
      </div>       
   </div>
   <div class="tab" style="width:100%">
      <div class="content" id="content_tab-3">
        <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
            {if $mode eq 'view'}
                {foreach from=$trunks key=myId item=i}
                    <input type="hidden" value"{$k++}" />
                    <tr><td align="center">{$k}. {$i} </td>
                    </tr>
                {/foreach}  
            {else}
                <tr class="extension"><th>{$TRUNKS}</th><th>{$SEQUENCE}</th></tr>
                    <tr class="extension"> 
                        <td width="50%">
                            <ul id="sortable1" class="connectedSortable">
                                <li style="visibility:hidden; padding:0px;"></li>
                                    {foreach from=$arrDif key=id item=m}
                                        <li class="ui-state-default" id="{$id}">{$m}<input type="hidden" name="trunk{$id}" id="{$id}" value="{$id}"></li>
                                    {/foreach}  
                            </ul>
                        </td>
                        <td width="50%">
                            <ul id="sortable2" class="connectedSortable">
                                <li style="visibility:hidden; padding:0px;"></li>
                                    {foreach from=$trunks key=id item=m}
                                        <li class="ui-state-default" id="{$id}">{$m}<input type="hidden" name="trunk{$id}" id="{$id}" value="{$id}"></li>
                                    {/foreach}  
                            </ul>
                        </td> 
                </tr>
                <tr><td><div><span  class="required">*</span>{$DRAGANDDROP}</div> </td></tr>
            {/if}
            <tr>
                <td></td>
            </tr>
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
<input type="hidden" name="arrTrunks" id="arrTrunks" value="{$arrT}">
<input type="hidden" name="id_outbound" id="id_outbound" value="{$id_outbound}">
<input type="hidden" name="mostra_adv" id="mostra_adv" value="{$mostra_adv}">
<input type="hidden" name="arrDestine"  id="arrDestine" value="{$arrDestine}">
<input type="hidden" name="index"  id="index" value="{$j+1}">
<input type="hidden" name="organization" value="{$ORGANIZATION}">
<input type="hidden" name="organization_add" value="{$ORGANIZATION}">

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
#sortable1, #sortable2 { list-style-type: none; margin: 0; padding: 0 0 2.5em; float: left; margin-right: 10px; cursor:move; width:100%; border:1px dotted #C9C9C9;}
#sortable1 li, #sortable2 li { margin: 0 5px 5px 5px; padding: 5px; font-size: 1.2em; width: 120px; border:0px; color:#000}
	
</style>
{/literal}
