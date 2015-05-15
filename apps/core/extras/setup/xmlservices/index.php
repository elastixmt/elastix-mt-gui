<?php

$Server = $_SERVER["SERVER_ADDR"];
$url="http://".$Server."/xmlservices";

header("Content-Type: text/xml");

$menu = "<CiscoIPPhoneMenu>\n";
$menu .= "<Title>XML Services</Title>\n";
$menu .= "<Prompt>Please select one</Prompt>\n";

$menu .= "<MenuItem>\n";
$menu .= "<Name>View Phone Directory</Name>\n";
$menu .= "<URL>$url/E_book.php?Page=1</URL>\n";
$menu .= "</MenuItem>\n";

$menu .= "<MenuItem>\n";
$menu .= "<Name>Help</Name>\n";
$menu .= "<URL>$url/help.php</URL>\n";
$menu .= "</MenuItem>\n";

$menu .= "<MenuItem>\n";
$menu .= "<Name>RSS Feeds</Name>\n";
$menu .= "<URL>$url/rssfeeds.php</URL>\n";
$menu .= "</MenuItem>\n";

$menu .= "</CiscoIPPhoneMenu>\n";

echo $menu;

?>
