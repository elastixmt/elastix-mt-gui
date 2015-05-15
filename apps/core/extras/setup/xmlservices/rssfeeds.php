<?php

##########################################

$Server = $_SERVER["SERVER_ADDR"];
$url="http://".$Server."/xmlservices";

##########################################

header("Content-Type: text/xml");

$menu = "<CiscoIPPhoneMenu>\n";
$menu .= "<Title>RSS Feeds</Title>\n";
$menu .= "<Prompt>Please select an RSS Feed</Prompt>\n";

$menu .= "<MenuItem>\n";
$menu .= "<Name>What's News - US</Name>\n";
$menu .= "<URL>$url/rss/xmlrssparse.php?feed=http://online.wsj.com/xml/rss/0,,3_7011,00.xml</URL>\n";
$menu .= "</MenuItem>\n";

$menu .= "</CiscoIPPhoneMenu>\n";

echo $menu;

?>
