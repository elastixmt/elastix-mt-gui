#!/usr/bin/php
<?php
$cmdLine = "";

for($i=1; $i<$_SERVER['argc']; $i++)
	$cmdLine .= "\"{$_SERVER['argv'][$i]}\" ";

if(file_exists("bin/notify-avantfax.php"))
	echo `bin/notify-avantfax.php $cmdLine`;

if(file_exists("bin/notify-elastix.php"))
	echo `bin/notify-elastix.php  $cmdLine`;
?>
