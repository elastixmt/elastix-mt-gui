<table width="100%" cellpadding="4" cellspacing="0" border="0">
    <tr>
        <td align="left">
            <input class="button" type="submit" name="save_did" onclick="save_user_access()"value="{$SAVE}" >
            <input class="button" type="submit" name="cancel_did" value="{$CANCEL}">
        </td>
    </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
    <tr>
        <td width="5%"><b>{$country.LABEL}:</b></td>
        <td >{$country.INPUT}</td>
    </tr>
    <tr>
        <td width="5%"><b>{$city.LABEL}:</b></td>
        <td >{$city.INPUT} {$SEARCH}</td>
    </tr>
</table>
<div id="dragzone">
    <div id="didlist">
        <fieldset>
            <legend>{$DIDLIST_LABEL}</legend>
            <ul id="sortable1" class="connectedSortable">
                {foreach from=$DID_FREE item=did}
                    <li class="ui-state-default" id="{$did.id}">({$did.country_code}) {$did.area_code}-{$did.did}</li>
                {/foreach}
            </ul>
        </fieldset>
    </div>
    <div id="didorg">
        <fieldset>
            <legend>{$DIDORG_LABEL}</legend>
            <ul id="sortable2" class="connectedSortable">
            {foreach from=$DID_ORG item=did}
                <li class="ui-state-default" id="{$did.id}">({$did.country_code}) {$did.area_code}-{$did.did}</li>
            {/foreach}
            </ul>
        </fieldset>
    </div>
</div>
<div style="padding: 10px 12px 0px 12px;"> * {$LEYENDDRAG}</div>
<input type="hidden" name="domain" value="{$domain}">
<input type="hidden" name="listDIDOrg" value="{$listDIDOrg}">