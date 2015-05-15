<?php

##########################################

$Server = $_SERVER["SERVER_ADDR"];
$url="http://".$Server."/xmlservices";

##########################################

header("Content-Type: text/xml");

$menu = "<CiscoIPPhoneText>\n";
$menu .= "<Title>Help</Title>\n";
$menu .= "<Prompt>RSS Feed</Prompt>\n";
$menu .= "<Text>\n";
$menu .= "Feature Codes - List\n";
$menu .= "\n";
$menu .= "*411 Directory\n";
$menu .= "*43 Echo Test\n";
$menu .= "*60 Time\n";
$menu .= "*61 Weather\n";
$menu .= "*62 Schedule wakeup call\n";
$menu .= "*65 festival test (your extension is XXX)\n";
$menu .= "*70 Activate Call Waiting (deactivated by default)\n";
$menu .= "*71 Deactivate Call Waiting\n";
$menu .= "*72 Call Forwarding System\n";
$menu .= "*73 Disable Call Forwarding\n";
$menu .= "*77 IVR Recording\n";
$menu .= "*78 Enable Do-Not-Disturb\n";
$menu .= "*79 Disable Do-Not-Disturb\n";
$menu .= "*90 Call Forward on Busy\n";
$menu .= "*91 Disable Call Forward on Busy\n";
$menu .= "*97 Message Center (does no ask for extension)\n";
$menu .= "*98 Enter Message Center\n";
$menu .= "*99 Playback IVR Recording\n";
$menu .= "666 Test Fax\n";
$menu .= "7777 Simulate incoming call\n";
$menu .= "</Text>\n";
$menu .= "</CiscoIPPhoneText>\n";
echo $menu;
?>

