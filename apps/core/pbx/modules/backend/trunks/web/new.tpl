<div align="right" style="padding-right: 4px;">
    {if $mode ne 'view'}
        <span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span>
    {/if}
</div>
<div class="neo-table-header-row">
    <div  class="neo-table-header-row-filter tab">
        <input type="radio" id="tab-general" name="tab-group-general" onclick="radio('tab-general');" checked>
        <label for="tab-general">{$GENERAL}</label>
    </div>
    {if $TECH eq 'SIP' or $TECH eq 'IAX2'}
        <div  class="neo-table-header-row-filter tab">
            <input type="radio" id="tab-peer" name="tab-group-peer" onclick="radio('tab-peer');" checked>
            <label for="tab-peer">{$SETTINGS}</label>
        </div>

        <div  class="neo-table-header-row-filter tab">
            <input type="radio" id="tab-user" name="tab-group-user" onclick="radio('tab-user');">
            <label for="tab-user">User Settings</label>
        </div>
        <div  class="neo-table-header-row-filter tab">
            <input type="radio" id="tab-register" name="tab-group-register" onclick="radio('tab-register');">
            <label for="tab-register">Registration</label>
        </div>
    {/if}
    <div class="neo-table-header-row-navigation" align="right" style="display: inline-block;">
        {if $mode eq 'input'}
            <input type="submit" name="save_new" value="{$SAVE}" >
        {elseif $mode eq 'edit'}
            <input type="submit" name="save_edit" value="{$APPLY_CHANGES}">
        {else}
            {if $EDIT}<input type="submit" name="edit" value="{$EDIT}">{/if}
            {if $DELETE}<input type="submit" name="delete" value="{$DELETE}"  onClick="return confirmSubmit('{$CONFIRM_CONTINUE}')">{/if}
        {/if}
        <input type="submit" name="cancel" value="{$CANCEL}">
    </div>
</div>
<div class="tabs">
    <div class="tab" >
       <div class="content" id="content_tab-general">
          <div id="div_body_tab">
            <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">
                <tr class="tech">
                    <td width="20%" nowrap>{$general_trunk_name.LABEL}: {if $mode ne 'view'}<span class="required">*</span>{/if}</td>
                    <td width="30%">{$general_trunk_name.INPUT}</td>
                </tr>
                <tr class="tech">
                    <td width="20%" nowrap>{$general_outcid.LABEL}: {if $mode ne 'view'}<span class="required">*</span>{/if}</td>
                    <td width="30%">{$general_outcid.INPUT}</td>
                    <td width="20%" nowrap>{$general_keepcid.LABEL}</td>
                    <td width="30%">{$general_keepcid.INPUT}</td>
                </tr>
                <tr class="tech">
                    <td nowrap>{$general_disabled.LABEL}</td>
                    <td>{$general_disabled.INPUT}</td>
                </tr>
                <tr><th>{$SEC_SETTINGS}</th></tr>
                <tr class="tech">
                    <td nowrap>{$general_maxchans.LABEL}</td>
                    <td>{$general_maxchans.INPUT}</td>
                </tr>
                <tr class="tech">
                    <td nowrap>{$general_sec_call_time.LABEL}</td>
                    <td>{$general_sec_call_time.INPUT}</td>
                </tr>
                {if $mode ne 'view' || $SEC_TIME eq 'yes' }
                <tr class="tech general_sec_call_time">
                    <td nowrap>{$general_maxcalls_time.LABEL}</td>
                    <td>{$general_maxcalls_time.INPUT}</td>
                    <td nowrap>{$general_period_time.LABEL}</td>
                    <td>{$general_period_time.INPUT}</td>
                </tr>
                {/if}
                {if $TECH eq 'DAHDI' | $TECH eq 'CUSTOM'}
                    <tr><th>{$NAME_CHANNEL}</th></tr>
                    <tr class="tech">
                        <td nowrap>{$general_channelid.LABEL}:</td>
                        <td >{$general_channelid.INPUT}</td>
                    </tr>
                {/if}
                <tr><th>{$ORGANIZATION_PERM}</th></tr>
                {if $mode eq 'view'}
                    <tr class="tech">
                        <td width="15%" nowrap>{$general_org.LABEL}: </td>
                        <td width="20%"> {$ORGS} </td>
                        <td ></td>
                    </tr>
                {else}
                    <tr class="tech">
                        <td width="15%" valign="top" nowrap>{$general_org.LABEL}: {if $mode ne 'view'}<span  class="required">*</span>{/if}</td>
                        <td width="10%" valign="top">{$general_org.INPUT}</td>
                        <td rowspan="2">
                            <input class="button" name="remove" id="remove" value="<<" onclick="javascript:quitar_org();" type="button">
                            <select name="arr_org" size="4" id="arr_org" style="width: 120px;">
                            </select>
                            <input type="hidden" id="select_orgs" name="select_orgs" value={$ORGS}>
                        </td>
                    </tr>
                {/if}
            </table>
            <p style="margin-top: 0px; padding-left: 4px; color: #E35332; font-weight: bold;" colspan=4>{$RULES}</td>
            <p style="margin-top: 0px; padding-left: 12px;">{$general_dialout_prefix.LABEL}: {$general_dialout_prefix.INPUT} </p>
            <table width="80%" border="0" cellspacing="0" cellpadding="5px" class="tabForm" id="destine">
            <thead>
                <tr>
                    <th >{$PREPEND}</th>
                    <th ></th>  
                    <th >{$PREFIX}</th>
                    <th ></th>
                    <th >{$MATCH_PATTERN}</th>
                    <th >{if $mode ne 'view'}<div class="add" style="cursor:pointer; float: left"><img src='web/apps/ivr/images/add1.png' title='Add'/></div>{/if}</th>
                </tr>
            </thead>
            {if $mode eq 'view'}
            {foreach from=$items key=myId item=i}
                <tr><td align="center">{if $i.3 eq ''}(  ){else}({$i.3}){/if}</td>
                <td align="center">+</td>
                <td align="center">{$i.1}</td>
                <td align="center">|</td>
                <td align="center">{$i.2}</td>
                </tr>
                {/foreach}
            {else}
            <tr id="test" style="display:none;">
                <td align="center">({$general_prepend_digit__.INPUT})</td>
                <td align="center">+</td>
                <td align="center">{$general_pattern_prefix__.INPUT}</td>
                <td align="center">|</td>
                <td align="center">{$general_pattern_pass__.INPUT}</td>
                <td width="50px">
                    <div class='delete' style='float:left; cursor:pointer;'><img src='web/apps/ivr/images/remove1.png' title='Remove'/></div>     
                </td>
            </tr>
            {foreach from=$items key=myId item=i}
                <input type="hidden" value"{$j++}" />
                <tr class="content-destine" id="{$j}">
                    <td align="center" >(<input type="text" name="general_prepend_digit{$j}" value="{$i.3}" style="width:60px;text-align:center;">)</td>
                    <td align="center">+</td>
                    <td align="center" ><input type="text" name="general_pattern_prefix{$j}" value="{$i.1}" style="width:40px;text-align:center;"></td>
                    <td align="center">|</td>
                    <td align="center" ><input type="text" name="general_pattern_pass{$j}" value="{$i.2}" style="width:150px;text-align:center;"></td>
                    <td width="50px"><div class='delete' style='float:left; cursor:pointer;'><img src='web/apps/ivr/images/remove1.png' title='Remove'/></div></td>
                </tr>
            {/foreach}
            {/if}
            </table>
        </div>
       </div>       
   </div>
   
    {php}
        function genHTML(&$cnt, $idattr, $attr, $arrTPL_VARs)
        {
            $required = "";
            $adsettin = "";
            $class    = "";
            $html     = "";
            $prefix   = substr($idattr,0,4);

            if($attr['REQUIRED']=="yes") $required = ($arrTPL_VARs['mode'] == 'input' || $arrTPL_VARs['mode'] == 'edit')?"<span  class='required'>*</span>":"";
            if($attr['IS_ADVANCED_SETTING']=="yes"){ $adsettin = $arrTPL_VARs["SHOW_MORE_".strtoupper($prefix)]; $class = "show_more_{$prefix}"; };

            if($cnt%2 == 0) $html .= "<tr class='tech $class' {$adsettin}>";                

            $html .= "<td width='15%' nowrap> {$arrTPL_VARs[$idattr]['LABEL']}: {$required}</td>";
            $html .= "<td> {$arrTPL_VARs[$idattr]['INPUT']} </td>";

            if($cnt%2 == 1) $html .= "</tr>";
            $cnt++;

            return $html;
        }

        $arrCNT      = array(0,0,0,0,0,0);
        $arrHTML     = array("","","","","","");
        $arrTPL_VARs = $this->get_template_vars();
        foreach($arrTPL_VARs['arrAttributes'] as $idattr => $attr){
            $prefix = substr($idattr,0,4);
            switch($prefix){
                case "peer":
                    if($attr['IS_ADVANCED_SETTING']=="yes")
                        $arrHTML[0] .= genHTML($arrCNT[0],$idattr,$attr,$arrTPL_VARs);
                    else
                        $arrHTML[1] .= genHTML($arrCNT[1],$idattr,$attr,$arrTPL_VARs);
                    break;
                case "user":
                    if($attr['IS_ADVANCED_SETTING']=="yes")
                        $arrHTML[2] .= genHTML($arrCNT[2],$idattr,$attr,$arrTPL_VARs);
                    else
                        $arrHTML[3] .= genHTML($arrCNT[3],$idattr,$attr,$arrTPL_VARs);
                    break;
                case "gene":
                    $arrHTML[4] .= genHTML($arrCNT[4],$idattr,$attr,$arrTPL_VARs);
                    break;
                case "regi":
                    $arrHTML[5] .= genHTML($arrCNT[5],$idattr,$attr,$arrTPL_VARs);
                    break;
                default: //case register                    
                    break;
            } 
        }             
    {/php}
            
    {if $TECH eq 'SIP' | $TECH eq 'IAX2'}    
    <div class="tab">
      <div class="content" id="content_tab-peer">
        <div id="div_body_tab">
          <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">   
            {php} echo $arrHTML[1]; {/php}  
            <tr>
                <td style="padding-left: 2px; font-size: 13px" colspan=4><a href="javascript:void(0);" class="adv_opt_peer"><b>{$ADV_OPTIONS}</b></a></td>
            </tr>
            {php} echo $arrHTML[0]; {/php}  
          </table>
        </div>
      </div>       
    </div>
          
    <div class="tab">
      <div class="content" id="content_tab-user">
        <div id="div_body_tab">
          <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">            
            {php} echo $arrHTML[3]; {/php}
            <tr>
                <td style="padding-left: 2px; font-size: 13px" colspan=4><a href="javascript:void(0);" class="adv_opt_user"><b>{$ADV_OPTIONS}</b></a></td>
            </tr>
            {php} echo $arrHTML[2]; {/php}
          </table>
        </div>
      </div>       
    </div>
     
    <div class="tab">
      <div class="content" id="content_tab-register">
        <div id="div_body_tab">
          <table width="100%" border="0" cellspacing="0" cellpadding="5px" class="tabForm">            
            {php} echo $arrHTML[5]; {/php}
          </table>
        </div>
      </div>       
    </div>       
   {/if}
</div>
<div style="display:none" id="terminate">
{foreach from=$arrTerminate key=k item=v}
    <option value="{$k}">{$v}</option>
{/foreach}
</div>
<input type="hidden" name="mode_input" id="mode_input" value="{$mode}">
<input type="hidden" name="id_trunk" id="id_trunk" value="{$id_trunk}">
<input type="hidden" name="tech_trunk" id="tech_trunk" value="{$tech_trunk}">
<input type="hidden" name="mostra_adv_peer" id="mostra_adv_peer" value="{$mostra_adv_peer}">
<input type="hidden" name="mostra_adv_user" id="mostra_adv_user" value="{$mostra_adv_user}">
<input type="hidden" name="arrDestine"  id="arrDestine" value="{$arrDestine}">
<input type="hidden" name="index"  id="index" value="{$j+1}">
{literal}
<script type="text/javascript">
$(document).ready(function(){
    radio("tab-general");
});
</script>
<style type="text/css">
.tech td{
	padding-left: 12px;
}
</style>
{/literal}
