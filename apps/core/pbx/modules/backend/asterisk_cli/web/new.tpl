<!--<form method="POST" enctype="multipart/form-data">

Comentario:  He agregado variables para que se muestre la misma vista de la 160

-->

<form method="POST" enctype="multipart/form-data">

<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">

<table>
		<tr>
			<td class="label" align="right">{$command}</td>
			<td class="type"><input name="txtCommand" type="text" size="70" value="{$txtCommand}"></td>
		</tr>
		
		<tr>
			<td valign="top">   </td>
			<td valign="top" class="label">
				<input type="submit" class="button" value="{$execute}">
			</td>
		</tr>
		
		<tr>
			<td height="8"></td>
			<td><hr>{$RESPUESTA_SHELL}</td>
		</tr>
</table>
</table>
</form>
