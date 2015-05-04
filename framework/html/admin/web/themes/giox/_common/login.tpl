<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Elastix - {$PAGE_NAME}</title>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">-->
<link rel="stylesheet" href="{$WEBPATH}/themes/{$THEMENAME}/styles.css">
</head>

<body bgcolor="#ffffff" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
  <table cellspacing=0 cellpadding=0 width="100%" border=0>
    <tr>
      <td>
        <table cellSpacing="0" cellPadding="0" width="100%" border="0">
          <tr>
            <td class="menulogo" width=380><img src="{$WEBPATH}images/logo_elastix.png" width="233" height="75" /></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
<form method="POST">
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="400" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td width="498" class="menudescription">
      <table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
        <tr>
          <td>
              <div align="left"><font color="#ffffff">&nbsp;&raquo;&nbsp;{$WELCOME}</font></div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td width="498" bgcolor="#ffffff">
      <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tabForm">
        <tr>
          <td colspan="2">
            <div align="center">{$ENTER_USER_PASSWORD}<br><br></div>
          </td>
        </tr>
        <tr>
          <td>
              <div align="right">{$USERNAME}:</div>
          </td>
          <td>
            <input type="text" id="input_user" name="input_user" style="color:#000000; FONT-FAMILY: verdana, arial, helvetica, sans-serif; FONT-SIZE: 8pt;
             font-weight: none; text-decoration: none; background: #fbfeff; border: 1 solid #000000;">
          </td>
        </tr>
        <tr>
          <td>
              <div align="right">{$PASSWORD}:</div>
          </td>
          <td>
            <input type="password" name="input_pass" style="color:#000000; FONT-FAMILY: verdana, arial, helvetica, sans-serif; FONT-SIZE: 8pt;
             font-weight: none; text-decoration: none; background: #fbfeff; border: 1 solid #000000;">
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <input type="submit" name="submit_login" value="{$SUBMIT}" class="button">
          </td>
        </tr>
        <tr>
            <td colspan="2"><img src="themes/{$THEMENAME}/images/0.gif" width="1" height="5"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
<br>
 <div align="center" class="copyright"><a href="http://www.elastix.org" target='_blank'>Elastix</a> is licensed under <a href="http://www.opensource.org/licenses/gpl-license.php" target='_blank'>GPL</a> by <a href="http://www.palosanto.com" target='_blank'>PaloSanto Solutions</a>. 2006 - {$currentyear}.</div>
<br>
<script type="text/javascript">
    document.getElementById("input_user").focus();
</script>
</body>
</html>
