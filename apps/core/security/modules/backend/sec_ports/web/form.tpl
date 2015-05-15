<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
        <td align="left">
        {if $MODE eq "new"}
            {if $NEW_PORT}<input class="button" type="submit" name="save" value="{$SAVE}">&nbsp;{/if}
        {/if}
        {if $MODE eq "edit"}
            {if $EDIT_PORT}<input class="button" type="submit" name="save" value="{$SAVE}">&nbsp;{/if}
        {/if}
        {if $MODE eq "view"}
            {if $EDIT_PORT}<input class="button" type="submit" name="edit" value="{$EDIT}">&nbsp;{/if}
        {/if}
            <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
	{if $MODE ne "view"}
	    <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
	{/if}
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12" id="name">
        <td align="left" width="8%"><b>{$name.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$name.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$protocol.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$protocol.INPUT}</td>
    </tr>
    <tr {$port_style} class="letra12" id="port">
        <td align="left"><b>{$port.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        {if $MODE eq "new"}
        <td align="left">{$port.INPUT}&nbsp;:&nbsp;{$port2.INPUT}</td>
        {/if}
        {if $MODE eq "edit"}
        <td align="left">{$port.INPUT}&nbsp;:&nbsp;{$port2.INPUT}</td>
        {/if}
        {if $MODE eq "view"}
        <td align="left">{$port.INPUT}&nbsp; {if $HAS eq "yes"}:{/if} &nbsp;{$port2.INPUT}</td>
        {/if}
    </tr>
    <tr {$type_style} class="letra12" id="type">
        <td align="left"><b>{$type.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$type.INPUT}</td>
    </tr>
    <tr {$code_style} class="letra12" id="code">
        <td align="left"><b>{$code.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$code.INPUT}</td>
    </tr>
    <tr {$protocol_style} class="letra12" id="protocol_number">
        <td align="left"><b>{$protocol_number.LABEL}: {if $MODE ne "view"}<span  class="required">*</span>{/if}</b></td>
        <td align="left">{$protocol_number.INPUT}</td>
    </tr>
    <tr class="letra12">
        <td align="left"><b>{$comment.LABEL}:</b></td>
        <td align="left">{$comment.INPUT}</td>
    </tr>
</table>
<input type="hidden" name="mode" value="{$MODE}">
<input type="hidden" name="idtemp" value="{$IDTEMP}">
