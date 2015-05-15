<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr class="letra12">
	<td width="10%" align="right">{$Phone_Directory}:</td>
	<td width="7%" align="left">&nbsp;
	    <select name="select_directory_type" onchange='report_by_directory_type()'>
		<option value="Internal" {$internal_sel}>{$Internal}</option>
		<option value="External" {$external_sel}>{$External}</option>
	    </select>
	</td>
	<td align="left" nowrap> &nbsp;
	    {$field.LABEL}: {$field.INPUT} &nbsp;{$pattern.INPUT}&nbsp;&nbsp;
	    <input class="button" type="submit" name="report" value="{$SHOW}">
	</td>
    </tr>
</table>

{literal}
    <script type="text/javascript">
        function return_phone_number(number, type, id)
        {
            window.opener.document.getElementById("call_to").value = number;
            window.opener.document.getElementById("phone_type").value = type;
            window.opener.document.getElementById("phone_id").value = id;
            window.close();
        }

        function report_by_directory_type()
        {
            var forms = document.getElementsByTagName('form');
            forms[0].submit();
        }
    </script>
{/literal}