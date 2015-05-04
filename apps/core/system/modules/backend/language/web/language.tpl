<form method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
	<td>
        {if $conectiondb}
            {if $EDIT_LANG}<input class="button" type="submit" name="save_language" value="{$CAMBIAR}">{/if}
        {else}
            {$MSG_ERROR}
        {/if}
	</td>
    </tr>
</table>
<table class="tabForm" style="font-size: 16px;" width="100%" >
    <tr class="letra12">
        <td width="9%"><b>{$language.LABEL}:</b></td>
	<td width="35%">{$language.INPUT}</td>
    </tr>
</table>
</form>