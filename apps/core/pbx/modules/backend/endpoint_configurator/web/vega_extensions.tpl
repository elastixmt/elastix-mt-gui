{literal}
<style type="text/css">
 .tabForm tr:hover {background:#e0e0e0;}
 </style>
{/literal}
<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="moduleTitle">
        <td class="moduleTitle" valign="middle" colspan='2'>&nbsp;&nbsp;<img src="{$IMG}" border="0" width="40px" height="50px" align="absmiddle">&nbsp;&nbsp;{$title}</td>
    </tr>
    <tr class="letra12">
        <td align="left">
            <input class="button" type="submit" name="{$NEXT2}" value="{$NEXT}">&nbsp;&nbsp;
	    <input class="button" type="submit" name="return1_vega" value="{$RETURN}">&nbsp;&nbsp;
            <input class="button" type="submit" name="cancel" value="{$CANCEL}">
        </td>
        <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>

<div class="tabForm" style="font-size: 16px" width="100%">
    <div>	
	<table border="0" width="100%" cellspacing="0" cellpadding="8">
	    {$fields}
	</table>
    </div>
</div>
