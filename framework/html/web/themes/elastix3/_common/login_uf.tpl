<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
	<title>Elastix - {$PAGE_NAME}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">-->
	<link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/login_styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, target-densitydpi=device-dpi"/>
    {$HEADER_LIBS_JQUERY}
  </head>
  <body>
	<div class="wrapper">
		<form id="form" method="post">
			<img id="logo" src="{$WEBPATH}themes/{$THEMENAME}/images/icon2.png" alt="photo profile">
			<div id="dname">
					<input id="name" name="input_user" placeholder="{$USERNAME}:" type="text" tabindex="1" required autofocus>
			</div>
			<div id="dpass">
					<input id="pass" name="input_pass" placeholder="{$PASSWORD}:" type="password" tabindex="2" required>
			</div>
			<div id="dbutt">
                    <button  name="submit_login" type="submit" id="submit">{$SUBMIT}</button>
            </div>
		</form>
		 
		 <footer>
				<a target="_blank" style="text-decoration: none;" href="http://www.elastix.org">Elastix</a>
				is licensed under
				<a target="_blank" style="text-decoration: none;" href="http://www.opensource.org/licenses/gpl-license.php">GPL</a>
				by
				<a target="_blank" style="text-decoration: none;" href="http://www.palosanto.com">PaloSanto Solutions</a>
				. 2006 - {$currentyear}.
		  </footer>
	</div>
	
  </body>
</html>
