<?php
/*
	xmlrssparse.php - Read feed, output to template
	xmlrssparse
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
	www.csma.biz
*/
require_once "lib/xtpl.php";

$xtpl=new XTemplate ("xmlrssparse.html");

if (isset($_GET['feed'])) 
{
	//
	//        Read Feed
	//
	
	$myUrl = $_GET['feed'];
	$rss = file_get_contents($myUrl);
	
	//create new DOM Document
	require_once("DOMIT/domit_rss/xml_domit_parser.php");
	$rssDoc = new DOMIT_Document();
	
	//parse RSS XML
	$rssDoc->parseXML($rss, true);
	
	$numChannels = count($rssDoc->documentElement->childNodes);

	for ($i = 0; $i < $numChannels; $i++) {
		$currentChannel =& $rssDoc->documentElement->childNodes[$i];
		$channelTitle = $currentChannel->childNodes[0]->firstChild->nodeValue;
		$channelDesc = $currentChannel->childNodes[3]->firstChild->nodeValue;
		$channelPubDate = $currentChannel->childNodes[4]->firstChild->nodeValue;		
		$numChannelNodes = count($currentChannel->childNodes);
	
		$xtpl->assign("main_title",$channelTitle);	
		
		//parse out items data
		for ($j = 5; $j < $numChannelNodes; $j++) {
			$currentItem = $currentChannel->childNodes[$j];
			$itemTitle = $currentItem->childNodes[0]->firstChild->nodeValue;
			$itemDesc = $currentItem->childNodes[2]->firstChild->nodeValue;
			$itemLink = $currentItem->childNodes[1]->firstChild->nodeValue;
			$itemDate = $currentItem->childNodes[5]->firstChild->nodeValue;
			
			$xtpl->assign("description",remove_illegal_char ($itemDesc));
			$xtpl->assign("title",remove_illegal_char ($itemTitle));
			$xtpl->assign("link", remove_illegal_char ($itemLink));
			$xtpl->assign("newline","\n \n");//add spacing
			if ($itemDate == "")
			{
				//date does not exist, replace with dashes
				$xtpl->assign("date","\n-----------------------------");
			} else {
				//date exists, display date
				$xtpl->assign("date","\n".$itemDate);
			}
			if ($itemTitle != '')
			{
				//item is not blank, display it
				$xtpl->parse('main.feed');
			}
		}
	}
} else {
	//URL to feed has not been located
	$xtpl->assign("main_title","Feed not specified");
}
// Output
$xtpl->parse("main");
$xtpl->out("main");

//
//
// Functions
//

function remove_illegal_char ($input)
{
	// Remove illegal characters that would cause xml errors
	
	$output = trim(eregi_replace("&", "", $input));
	$output = trim(eregi_replace("<", "", $output));
	//$output = trim(eregi_replace(">", "", $output));
	//$output = trim(eregi_replace("\'", "", $output));
	//$output = trim(eregi_replace("\"", "", $output));
	/*
		Out of XML's 5 predefined entity references, only the 
		characters "<" and "&" are strictly illegal.
		
		We can still get away with using Apostrophes, Quotation Marks
		and Greater Than signs.
	*/
	return $output;
}
?>