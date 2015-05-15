<br />
<table class="tabForm" style="font-size: 16px;" width="100%">
    <tr>
        <td width="10%" align="left" valign="top" style="font-size:64%;">
            <div style="margin: 0px 10px 6px 10px;" valign="middle">
                <div class='fc-button-add ui-state-default  ui-corner-left ui-corner-right' id="btnNewEvent" style="height: 25px;" align="center">
                    <a id='add_news' onclick='displayNewEvent(event);'>
                        {$CreateEvent}
                    </a>
                </div>
            </div>
            <div id="datepicker"></div>
            <div id="icals" class="ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
                <div class="ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all title_size">{$Export_Calendar}</div>
                <div class="content_ical">
                    <a href="index.php?menu={$module_name}&action=download_icals&rawmode=yes">
                            <span>{$ical}</span>
                    </a>
                </div>
            </div>
        </td>
        <td align="right" width="90%" >
            <div id='calendar'></div>
        </td>
    </tr>
</table>
<div id="facebox_form">
</div>
<input class="button" type="hidden" name="id" value="{$ID}" id="id" />
<input class="button" type="hidden" name="lblEdit" value="{$LBL_EDIT}" id="lblEdit" />
<input class="button" type="hidden" name="lblLoading" value="{$LBL_LOADING}" id="lblLoading">
<input class="button" type="hidden" name="lblDeleting" value="{$LBL_DELETING}" id="lblDeleting">
<input class="button" type="hidden" name="lblSending" value="{$LBL_SENDING}" id="lblSending">
<input class="button" type="hidden" name="typeen" value="{$START_TYPE}...." id="typeen" />
<input class="button" type="hidden" name="dateServer" value="{$DATE_SERVER}" id="dateServer" />

