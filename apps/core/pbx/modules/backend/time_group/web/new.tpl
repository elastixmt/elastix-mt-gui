<div>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
        {if $mode eq 'input'}
            <input class="button" type="submit" name="save_new" value="{$SAVE}" >
        {elseif $mode eq 'edit'}
            {if $EDIT_TG}<input class="button" type="submit" name="save_edit" value="{$APPLY_CHANGES}">{/if}
            {if $DEL_TG}<input class="button" type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
        {/if}
        <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        {if $mode ne 'view'}
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
        {/if}
     </tr>
   </table>
</div>
{if $USERLEVEL eq 'superadmin'}
    <p >
        <span class="name" >{$ORGANIZATION_LABEL}: </span> {$ORGANIZATION}
    </p>
{/if}
<p >
<span class="name" >{$name.LABEL}: </span> {if $mode ne 'view'}<span  class="required">*</span>{/if} {$name.INPUT}  {if $mode ne 'view'} <input type="button" name="add_group" value="{$ADD_GROUP}" class="button" id="add_group"/> {/if} 
</p>
<div id="test" style="display:none;">
    {if $mode ne 'view'}
    <div class="div_delete">
        <input type="button" name="delete_group" value="{$DELETE_GROUP}" class="button" id="delete_group"/>
    </div>
    {/if}
    <div class="time">
        <ul class="ul_time">
            <li class="li_time"> <span class="name">{$Stime__.LABEL}: </span> <div style="display:inline; position:relative" class="sTime" id="Stime__"></div> - <div style="display:inline; position:relative" class="fTime" id="Ftime__"></div> <img id='remove_time' class="remove_tg" src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove' /> </li>
        </ul>
    </div>
    <div class="day_w">
        <ul class="ul_day_w">
            <li class="li_day_w"> <span class="name">{$Sday_week__.LABEL}: </span>{$Sday_week__.INPUT} - {$Fday_week__.INPUT} <img id='remove_day_w' class="remove_tg" src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove'/> </li>
        </ul>
    </div>
    <div class="day_m">
        <ul class="ul_day_m">
            <li class="li_day_m"> <span class="name">{$Sday_month__.LABEL}: </span>{$Sday_month__.INPUT} - {$Fday_month__.INPUT} <img id='remove_day_m' class="remove_tg"  src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove'/> </li>
        </ul>
    </div>
    <div class="month">
        <ul class="ul_month">
            <li class="li_month"> <span class="name">{$Smonth__.LABEL}: </span>{$Smonth__.INPUT} - {$Fmonth__.INPUT} <img id='remove_month' class="remove_tg"  src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove'/> </li>
        </ul>
    </div>
</div>
{if count($arrItems) eq 0}
    <div id="1" class="div_tg">
        <div class="time">
            <ul class="ul_time">
                <li class="li_time"> <span class="name">{$Stime__.LABEL}: </span> 
                <div style="display:inline; position:relative" class="sTime" id="Stime[1][]"></div> - <div style="display:inline; position:relative" class="fTime" id="Ftime[1][]"></div>
                <img src='web/apps/{$MODULE_NAME}/images/add1.png' title='Add' class="add_tg" id="add_time" /></li>
            </ul>
        </div>
        <div class="day_w">
            <ul class="ul_day_w">
                <li class="li_day_w"> <span class="name">{$Sday_week__.LABEL}: </span>
                    <select name="Sday_week[1][]" id="Sday_week[1][]">
                    {foreach from=$dayWeek key=k item=v}
                        <option value="{$k}" >{$v}</option>
                    {/foreach}
                    </select> - <select name="Fday_week[1][]" id="Fday_week[1][]">
                    {foreach from=$dayWeek key=k item=v}
                        <option value="{$k}">{$v}</option>
                    {/foreach}
                    </select> <img src='web/apps/{$MODULE_NAME}/images/add1.png' title='Add' class="add_tg" id="add_day_w" />
                </li>
            </ul>
        </div>
        <div class="day_m">
            <ul class="ul_day_m">
                <li class="li_day_m"> <span class="name">{$Sday_month__.LABEL}: </span>
                    <select name="Sday_month[1][]" id="Sday_month[1][]">
                    {foreach from=$dayMonth key=k item=v}
                        <option value="{$k}" >{$v}</option>
                    {/foreach}
                    </select> - <select name="Fday_month[1][]" id="Fday_month[1][]">
                    {foreach from=$dayMonth key=k item=v}
                        <option value="{$k}">{$v}</option>
                    {/foreach}
                    </select> </select> <img src='web/apps/{$MODULE_NAME}/images/add1.png' title='Add' class="add_tg" id="add_day_m" /> 
                </li>
            </ul>
        </div>
        <div class="month">
            <ul class="ul_month">
                <li class="li_month"> <span class="name">{$Smonth__.LABEL}: </span>
                    <select name="Smonth[1][]" id="Smonth[1][]">
                    {foreach from=$MONTH key=k item=v}
                        <option value="{$k}" >{$v}</option>
                    {/foreach}
                    </select> - <select name="Fmonth[1][]" id="Fmonth[1][]">
                    {foreach from=$MONTH key=k item=v}
                        <option value="{$k}">{$v}</option>
                    {/foreach}
                    </select> </select> <img src='web/apps/{$MODULE_NAME}/images/add1.png' title='Add' class="add_tg" id="add_month" />
                </li>
            </ul>
        </div>
    </div>
{else}
    {foreach from=$arrItems item=i}
        <input type="hidden" value"{$j++}" />
        <div id="{$j}" class="div_tg">
            {if $j ne 1}
            <div class="div_delete">
                <input type="button" name="delete_group" value="{$DELETE_GROUP}" class="button" id="delete_group"/>
            </div>
            {/if}
            <div class="time">
                <ul class="ul_time">
                {foreach from=$SHOUR.$i key=t item=time}
                    <li class="li_time"> <span class="name">{$Stime__.LABEL}: </span>
                        <div style="display:inline; position:relative" class="sTime" id="Stime[{$j}][]"><input type="hidden" name="shour" value="{$SHOUR.$i.$t}"><input type="hidden" name="smin" value="{$SMIN.$i.$t}"></div> - <div style="display:inline; position:relative" class="fTime" id="Ftime[{$j}][]"><input type="hidden" name="fhour" value="{$FHOUR.$i.$t}"><input type="hidden" name="fmin" value="{$FMIN.$i.$t}"></div>
                        {if $t eq 0}
                        <img src='web/apps/{$MODULE_NAME}/images/add1.png' title='Add' class="add_tg" id="add_time" />
                        {else}
                        <img id='remove_time' class="remove_tg" src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove'/>
                        {/if}
                    </li>
                {/foreach}
                </ul>
            </div>
            <div class="day_w">
                <ul class="ul_day_w">
                    {foreach from=$SDAY_W.$i key=d item=day}
                        <li class="li_day_w"> <span class="name">{$Sday_week__.LABEL}: </span>
                            <select name="Sday_week[{$j}][]" id="Sday_week[{$j}][]">
                            {foreach from=$dayWeek key=k item=v}
                                <option value="{$k}" {if $k eq $day} selected {/if}>{$v}</option>
                            {/foreach}
                            </select> - <select name="Fday_week[{$j}][]" id="Fday_week[{$j}][]">
                            {foreach from=$dayWeek key=k item=v}
                                <option value="{$k}" {if $k eq $FDAY_W.$i.$d} selected {/if}>{$v}</option>
                            {/foreach}
                            </select> 
                            {if $d eq 0}
                            <img src='web/apps/{$MODULE_NAME}/images/add1.png' title='Add' class="add_tg" id="add_day_w" />
                            {else}
                            <img id='remove_day_w' class="remove_tg" src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove'/> 
                            {/if}
                        </li>
                    {/foreach}
                </ul>
            </div>
            <div class="day_m">
                <ul class="ul_day_m">
                    {foreach from=$SDAY_M.$i key=f item=date}
                        <li class="li_day_m"> <span class="name">{$Sday_month__.LABEL}: </span>
                            <select name="Sday_month[{$j}][]" id="Sday_month[{$j}][]">
                            {foreach from=$dayMonth key=k item=v}
                                <option value="{$k}" {if $k eq $date} selected {/if}>{$v}</option>
                            {/foreach}
                            </select> - <select name="Fday_month[{$j}][]" id="Fday_month[{$j}][]">
                            {foreach from=$dayMonth key=k item=v}
                                <option value="{$k}" {if $k eq $FDAY_M.$i.$f} selected {/if}>{$v}</option>
                            {/foreach}
                            </select> 
                            {if $f eq 0}
                            <img src='web/apps/{$MODULE_NAME}/images/add1.png' title='Add' class="add_tg" id="add_day_m" />
                            {else}
                            <img id='remove_day_m' class="remove_tg" src='web/apps/{$MODULE_NAME}/images/remove1.png' 
                            title='Remove'/>
                            {/if}
                        </li>
                    {/foreach}
                </ul>
            </div>
            <div class="month">
                <ul class="ul_month">
                    {foreach from=$SMONTH.$i key=m item=mon}
                        <li class="li_month"> <span class="name">{$Smonth__.LABEL}: </span>
                            <select name="Smonth[{$j}][]" id="Smonth[{$j}][]">
                            {foreach from=$MONTH key=k item=v}
                                <option value="{$k}" {if $k eq $mon} selected {/if}>{$v}</option>
                            {/foreach}
                            </select> - <select name="Fmonth[{$j}][]" id="Fmonth[{$j}][]">
                            {foreach from=$MONTH key=k item=v}
                                <option value="{$k}" {if $k eq $FMONTH.$i.$m} selected {/if}>{$v}</option>
                            {/foreach}
                            </select> 
                            {if $m eq 0}
                            <img src='web/apps/{$MODULE_NAME}/images/add1.png' title='Add' class="add_tg" id="add_month" />
                            {else}
                            <img id='remove_month' class="remove_tg" src='web/apps/{$MODULE_NAME}/images/remove1.png' title='Remove'/> 
                            {/if}
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
    {/foreach}
{/if}
<input type="hidden" name="id_tg" id="id_tg" value="{$id_tg}">
<input type="hidden" name="mode_input" id="mode_input" value="{$mode}">
<input type="hidden" name="mostra_adv" id="mostra_adv" value="{$mostra_adv}">
<input type="hidden" name="index"  id="index" value="{$j+1}">
<input type="hidden" name="organization" id="id_tc" value="{$ORGANIZATION}">

{literal}
<script type="text/javascript">
$("div.div_tg div.time ul li div.sTime").each(function(){
    var idDiv=$(this).attr("id").substring(5);
    var hour=$(this).children('input[name="shour"]').val();
    var min=$(this).children('input[name="smin"]').val();
    $(this).jtimepicker({
        minCombo: "Smin"+idDiv,
        hourCombo: "Shour"+idDiv,
        minClass: "smin",
        hourClass: "shour",
        minDefaultValue: evaluate(min),
        hourDefaultValue: evaluate(hour),
    });
});
$("div.div_tg div.time ul li div.fTime").each(function(){
    var idDiv=$(this).attr("id").substring(5);
    var hour=$(this).children('input[name="fhour"]').val();
    var min=$(this).children('input[name="fmin"]').val();
    $(this).jtimepicker({
        minCombo: "Fmin"+idDiv,
        hourCombo: "Fhour"+idDiv,
        minClass: "fmin",
        hourClass: "fhour",
        minDefaultValue: evaluate(min),
        hourDefaultValue: evaluate(hour),
    });
});
function evaluate(tmp){
    if(typeof  tmp=="undefined" || tmp=="")
        return "*";
    return tmp
}
</script>
{/literal}