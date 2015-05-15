<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        <td align="left">
            <input class="button" type="submit" name="{$MEMBER}" value="{$SAVE}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td align="left" width="12%"><b>{$emailmembers.LABEL}: <span  class="required">*</span></b></td>
        <td align="left">{$emailmembers.INPUT}</td>
	<td align="left" width="55%"><i>{$INFO}</i></td>
    </tr>
</table>

<input class="button" type="hidden" name="id_emaillist" value="{$IDEMAILLIST}" />
<input class="button" type="hidden" name="action" value="{$ACTION}" />
<input class="button" type="hidden" name="id" value="{$IDEMAILLIST}" />