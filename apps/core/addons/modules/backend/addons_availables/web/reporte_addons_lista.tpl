{foreach from=$arrData key=k item=data}
  <div class="neo-addons-row">
    <input type="hidden" id="url_moreinfo" value="{$data.url_moreinfo}"/>
    <div class="neo-addons-row-title">{$data.name} - {$data.version}-{$data.release}</div>
    <div class="neo-addons-row-author">{$by} {$data.developed_by}</div>
    <div class="neo-addons-row-icon"><img src="{$url_images}/{$data.name_rpm}.jpeg " width="65" height="65" alt="iconaddon" align="absmiddle" /></div>
    <div class="neo-addons-row-desc">{$data.description}</div>
    <div class="neo-addons-row-location"><font style='font-weight:bold;'>{$location}: </font>{$data.location}</div>
    {if $data.note}
    <div class="neo-addons-row-note"><font style='font-weight:bold;'>{$note}: </font>{$data.note}</div>
    {/if}
    <div class="neo-addons-row-button">
        {* El siguiente campo hidden contiene el nombre del RPM a manipular *}
        <input type="hidden" id="name_rpm" value="{$data.name_rpm}"/>
        {if $data.installed_version == ''}
            {if $data.is_commercial and $data.fecha_compra eq 0}
	    <input type="hidden" id="{$data.name_rpm}_link" value="{$data.url_marketplace}{$server_key}&referer="/>
	    {if $data.has_trialversion eq 1}
	    <div class="neo-addons-row-button-trial-left">{$TRIAL}</div><div class="neo-addons-row-button-trial-right"><img width="17" height="17" alt="Install" src="web/apps/{$module_name}/images/addons_icon_install.png"></div>
	    {/if}
            <div class="neo-addons-row-button-buy-left">{$BUY}</div>
            <div class="neo-addons-row-button-buy-right"><img src="web/apps/{$module_name}/images/addons_icon_buy.png" width="19" height="18" alt="Buy" /></div>
            {else}
            <div class="neo-addons-row-button-install-left">{$INSTALL}</div>
            <div class="neo-addons-row-button-install-right"><img src="web/apps/{$module_name}/images/addons_icon_install.png" width="17" height="17" alt="Install" /></div>
            {/if}
        {else}
        {if $data.can_update}
            <div class="neo-addons-row-button-install-left tooltipInfo">{$UPDATE}{if !empty($data.upgrade_info)}<span>{$data.upgrade_info}</span>{/if}</div>
            <div class="neo-addons-row-button-install-right"><img src="web/apps/{$module_name}/images/addons_icon_update.png" width="20" height="17" alt="Update" /></div>
        {/if}
	<input type="hidden" id="{$data.name_rpm}_installed" value="yes"/>
        <div class="neo-addons-row-button-uninstall-left">{$UNINSTALL}</div>
        <div class="neo-addons-row-button-uninstall-right"><img src="web/apps/{$module_name}/images/addons_icon_uninstall.png" width="17" height="17" alt="Uninstall" /></div>
	{if $data.fecha_compra eq 0 and $data.is_commercial}
	    <input type="hidden" id="{$data.name_rpm}_link" value="{$data.url_marketplace}{$server_key}&referer="/>
	    <div class="neo-addons-row-button-buy-left">{$BUY}</div>
	    <div class="neo-addons-row-button-buy-right"><img src="web/apps/{$module_name}/images/addons_icon_buy.png" width="19" height="18" alt="Buy" /></div>
	{/if}
        {/if}
    </div>
    <div class="neo-addons-row-moreinfo">{$more_info}...</div>
  </div>
{/foreach}

<input type="hidden" name="callback" id="callback" value="" />
