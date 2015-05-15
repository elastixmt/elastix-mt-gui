<html>
<head><title>RSS Test</title></head>
<body>

<?php
require_once('xml_domit_rss_lite.php');

//$url = 'http://www.feedroom.com/rssout/att_rss_1ebaad7be9f5b75e7783f8b495e59bd0f58380b9.xml';
//$url = 'http://www.computerworld.com/news/xml/50/0,5009,,00.xml';
//$url = 'http://cyber.law.harvard.edu/blogs/gems/tech/sampleRss20.xml';
//$url = 'http://mosforge.net/export/rss_sfnews.php';
//$url = 'http://linuxtoday.com/backend/my-netscape.rdf'; //not yet! rdf + 0.9 style
//$url = 'http://headlines.internet.com/internetnews/bus-news/news.rss';
//$url = 'http://headlines.internet.com/internetnews/wd-news/news.rss';
//$url = 'http://www.bbc.co.uk/syndication/feeds/news/ukfs_news/technology/rss091.xml';
//$url = 'http://myrss.com/f/a/m/amazon102Minus3289784Minus3662556N6f8pb3.rss'; //not yet! rdf + 0.9 style
//$url = 'http://www.cancerletter.com/cancerletter.xml';
//$url = 'http://realbeer.com/rdf/realbeernews.rdf';
//$url = 'http://www.wired.com/news/feeds/rss2/0,2610,,00.xml';
//$url = 'http://www.incidents.org/rssfeed.xml';
$url = 'http://www.alternet.org/module/feed/rss/';

//********RSS 0.91***********
//$url = 'http://plebian.com/rss.php';
//$url = 'http://www.comingsoon.net/news/rss-reviews-5.php';
//$url = 'http://www.usrbingeek.com/index91.xml';
//$url = 'http://www.pixelcharmer.com/fieldnotes/index.xml';
//$url = 'http://www.growinglifestyle.com/h75/garden/index.rss';
//$url = 'http://history1900s.about.com/b/index.xml';
//$url = 'http://www.vinayaksworld.com/index.xml';
//$url = 'http://weblogs.asp.net/cfrazier/rss.aspx';
//$url = 'http://www.networkscience.com/blog/index.rdf';
//$url = 'http://www.ladyandtramp.com/mars/rss/mars.rss';
//$url = 'http://www.brewedfreshdaily.com/blogger_rss.xml';

//********RSS 0.91 fn***********
//$url = 'http://www.fool.com/xml/foolnews_rss091fn.xml';

//********RSS 0.92***********
//$url = 'http://www.phireworx.com/content/blog/pwrss.asp';
//$url = 'http://siteframe.org/rss.xml';
//$url = 'http://www.writenews.com/rss.xml';
//$url = 'http://xhp.sourceforge.net/rss.php';
//$url = 'http://drupal.org/node/feed';

//********RSS 1.0***********
//$url = 'http://blog.ctrlbreak.co.uk/index.rdf';
//$url = 'http://rss.topix.net/rss/who/hair-of-the-dog.xml';
//$url = 'http://www.betaland.net/joke/index.rdf';
//$url = 'http://rss.topix.net/rss/music/acoustic.xml';
//$url = 'http://baseballcrank.com/index.rdf';

//********RSS 2.0***********
//$url = 'http://www.solport.com/roundtable/index.xml';
//$url = 'http://sport.scotsman.com/tennis.cfm?format=rss';
//$url = 'http://radio.weblogs.com/0106123/categories/python/rss.xml';
//$url = 'http://www.peer-solutions.com/weblog/SyndicationService.asmx/GetRss?';
//$url = 'http://radio.weblogs.com/0105058/rss.xml';

//$url = 'http://mosforge.net/export/rss_sfnews.php';


//*******************CODE*************************

//instantiate rss document
$cacheDir = './';
$cacheTime = 3600;

$rssdoc =& new xml_domit_rss_document_lite($url, $cacheDir, $cacheTime);

//get total number of channels
$totalChannels = $rssdoc->getChannelCount();

//loop through each channel
for ($i = 0; $i < $totalChannels; $i++) {
	//get reference to current channel
	$currChannel =& $rssdoc->getChannel($i);
	
	//echo channel info
	echo "<h2><a href=\"" . $currChannel->getLink() . "\" target=\"_child\">" . 
						$currChannel->getTitle() . "</a>";
	echo "  " . $currChannel->getDescription() . "</h2>\n\n";
	
	//get total number of items
	$totalItems = $currChannel->getItemCount();
	
	//loop through each item
	for ($j = 0; $j < $totalItems; $j++) {
		//get reference to current item
		$currItem =& $currChannel->getItem($j);
	
		//echo item info
		echo "<p><a href=\"" . $currItem->getLink() . "\" target=\"_child\">" . 
				$currItem->getTitle() . "</a> " . $currItem->getDescription() . "</p>\n\n";
	
	}
}

echo $rssdoc->toNormalizedString(true);

?>

</body>
</html>
