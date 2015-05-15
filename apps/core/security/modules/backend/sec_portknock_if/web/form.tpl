<form  method='POST' style='margin-bottom:0;' action='?menu={$MODULE_NAME}&amp;action=setport'>
<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        <td align="left">
            <input class="button" type="submit" name="save" value="{$SAVE}">&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12" id="name">
        <td align="left" width="15%"><b>{$eth_in.LABEL}: <span  class="required">*</span></b></td>
        <td align="left">{$eth_in.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$port.LABEL}: <span  class="required">*</span></b></td>
        <td align="left">{$port.INPUT}</td>
    </tr>
</table>
</form>