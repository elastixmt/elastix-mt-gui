<?php
/**
* DOMIT! RSS is a DOM based RSS parser
* @package domit-rss
* @subpackage domit-rss-main
* @version 0.4
* @copyright (C) 2004 John Heinstein. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/domitrss/ DOMIT! RSS Home Page
* DOMIT! RSS is Free Software
**/



if (!defined('DOMIT_RSS_INCLUDE_PATH')) {
	define('DOMIT_RSS_INCLUDE_PATH', (dirname(__FILE__) . "/"));
}

/** current version of DOMIT! RSS */
define ('DOMIT_RSS_VERSION', '0.4');
/** language constant */
define('DOMIT_RSS_ELEMENT_LANGUAGE', 'language');
/** copyright constant */
define('DOMIT_RSS_ELEMENT_COPYRIGHT', 'copyright');
/** managing editor constant */
define('DOMIT_RSS_ELEMENT_MANAGINGEDITOR', 'managingeditor');
/** webmaster constant */
define('DOMIT_RSS_ELEMENT_WEBMASTER', 'webmaster');
/** pubdate constant */
define('DOMIT_RSS_ELEMENT_PUBDATE', 'pubdate');
/** last build date constant */
define('DOMIT_RSS_ELEMENT_LASTBUILDDATE', 'lastbuilddate');
/** category constant */
define('DOMIT_RSS_ELEMENT_CATEGORY', 'category');
/** generator constant */
define('DOMIT_RSS_ELEMENT_GENERATOR', 'generator');
/** docs constant */
define('DOMIT_RSS_ELEMENT_DOCS', 'docs');
/** cloud constant */
define('DOMIT_RSS_ELEMENT_CLOUD', 'cloud');
/** ttl constant */
define('DOMIT_RSS_ELEMENT_TTL', 'ttl');
/** image constant */
define('DOMIT_RSS_ELEMENT_IMAGE', 'image');
/** rating constant */
define('DOMIT_RSS_ELEMENT_RATING', 'rating');
/** textinput constant */
define('DOMIT_RSS_ELEMENT_TEXTINPUT', 'textinput');
/** skiphours constant */
define('DOMIT_RSS_ELEMENT_SKIPHOURS', 'skiphours');
/** skipdays constant */
define('DOMIT_RSS_ELEMENT_SKIPDAYS', 'skipdays');
/** url constant */
define('DOMIT_RSS_ELEMENT_URL', 'url');
/** width constant */
define('DOMIT_RSS_ELEMENT_WIDTH', 'width');
/** height constant */
define('DOMIT_RSS_ELEMENT_HEIGHT', 'height');
/** guid constant */
define('DOMIT_RSS_ELEMENT_GUID', 'guid');
/** enclosure constant */
define('DOMIT_RSS_ELEMENT_ENCLOSURE', 'enclosure');
/** comments constant */
define('DOMIT_RSS_ELEMENT_COMMENTS', 'comments');
/** source constant */
define('DOMIT_RSS_ELEMENT_SOURCE', 'source');
/** name constant */
define('DOMIT_RSS_ELEMENT_NAME', 'name');
/** author constant */
define('DOMIT_RSS_ELEMENT_AUTHOR', 'author');

/** domain constant */
define('DOMIT_RSS_ATTR_DOMAIN', 'domain');
/** port constant */
define('DOMIT_RSS_ATTR_PORT', 'port');
/** path constant */
define('DOMIT_RSS_ATTR_PATH', 'path');
/** registerProcedure constant */
define('DOMIT_RSS_ATTR_REGISTERPROCEDURE', 'registerProcedure');
/** protocol constant */
define('DOMIT_RSS_ATTR_PROTOCOL', 'protocol');
/** url constant */
define('DOMIT_RSS_ATTR_URL', 'url');
/** length constant */
define('DOMIT_RSS_ATTR_LENGTH', 'length');
/** type constant */
define('DOMIT_RSS_ATTR_TYPE', 'type');
/** isPermaLink constant */
define('DOMIT_RSS_ATTR_ISPERMALINK', 'isPermaLink');

require_once(DOMIT_RSS_INCLUDE_PATH . 'xml_domit_rss_shared.php');

/**
* The base DOMIT! RSS document class
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_document extends xml_domit_rss_base_document {
	/**
	* Constructor
	* @param string Path to the rss file
	* @param string Directory in which cache files are to be stored
	* @param string Expiration time (in seconds) for the cache file
	* @return Object A new instance of xml_domit_rss_document
	*/
	function xml_domit_rss_document($url = '', $cacheDir = './', $cacheTime = '3600') {
	    $this->parser = 'DOMIT_RSS';
		$this->xml_domit_rss_base_document($url, $cacheDir, $cacheTime);
	} //xml_domit_rss_document
	
	/**
	* Performs initialization of the RSS document
	*/
	function _init() {
		$total = $this->node->documentElement->childCount;
		$itemCounter = 0;
		$channelCounter = 0;
		$categoryCounter = 0;
		
		for ($i = 0; $i < $total; $i++) {
			$currNode =& $this->node->documentElement->childNodes[$i];
			$tagName = strtolower($currNode->nodeName);

			switch ($tagName) {
				case DOMIT_RSS_ELEMENT_ITEM:					
					$this->domit_rss_items[$itemCounter] =& new xml_domit_rss_item($currNode);
					$itemCounter++;
					break;
				case DOMIT_RSS_ELEMENT_CHANNEL:
					$this->domit_rss_channels[$channelCounter] =& new xml_domit_rss_channel($currNode);
					$channelCounter++;
					break;
				case DOMIT_RSS_ELEMENT_CATEGORY:
					$this->domit_rss_categories[$categoryCounter] =& new xml_domit_rss_category($currNode);
					$categoryCounter++;
					break;
				case DOMIT_RSS_ELEMENT_IMAGE:
					$this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_image($currNode);
					break;
				case DOMIT_RSS_ELEMENT_CLOUD:
					$this->indexer[$tagName] =& new xml_domit_rss_cloud($currNode);
					break;
				case DOMIT_RSS_ELEMENT_TEXTINPUT:
					$this->indexer[$tagName] =& new xml_domit_rss_textinput($currNode);
					break;
				case DOMIT_RSS_ELEMENT_TITLE:
                case DOMIT_RSS_ELEMENT_LINK:
                case DOMIT_RSS_ELEMENT_DESCRIPTION:
                case DOMIT_RSS_ELEMENT_LANGUAGE:
				case DOMIT_RSS_ELEMENT_COPYRIGHT:
				case DOMIT_RSS_ELEMENT_MANAGINGEDITOR:
				case DOMIT_RSS_ELEMENT_WEBMASTER:
				case DOMIT_RSS_ELEMENT_PUBDATE:
				case DOMIT_RSS_ELEMENT_LASTBUILDDATE:
				case DOMIT_RSS_ELEMENT_GENERATOR:
				case DOMIT_RSS_ELEMENT_DOCS:
				case DOMIT_RSS_ELEMENT_TTL:
				case DOMIT_RSS_ELEMENT_RATING:
				case DOMIT_RSS_ELEMENT_SKIPHOURS:
				case DOMIT_RSS_ELEMENT_SKIPDAYS:
				    $this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_simpleelement($currNode);
				    break;
				default:
				    $this->addIndexedElement($currNode);
					//$this->indexer[$tagName] =& $currNode;
			}
		}
		
		if ($itemCounter != 0) {
			$this->DOMIT_RSS_indexer[DOMIT_RSS_ARRAY_ITEMS] =& $this->domit_rss_items;
		}
		
		if ($channelCounter != 0) {
			$this->DOMIT_RSS_indexer[DOMIT_RSS_ARRAY_CHANNELS] =& $this->domit_rss_channels;
		}
		
		if ($categoryCounter != 0) {
			$this->DOMIT_RSS_indexer[DOMIT_RSS_ARRAY_CATEGORIES] =& $this->domit_rss_categories;
		}
		
		$this->handleChannelElementsEmbedded();
	} //_init	
	
	/**
	* Returns the current version of DOMIT! RSS
	* @return int The current version of DOMIT! RSS
	*/
	function getVersion() {
		return DOMIT_RSS_VERSION;
	} //getVersion
} //xml_domit_rss_document


/**
* Represents an RSS channel
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_channel extends xml_domit_rss_elementindexer {
	/** @var array A list of references to channel items */
	var $domit_rss_items = array();
	/** @var array A list of references to category items */
	var $domit_rss_categories = array();
	
	/**
	* Constructor
	* @param Object A DOM node containing channel data
	* @param boolean True if channel elements are siblings of the channel rather than children
	*/
	function xml_domit_rss_channel(&$channel, $externalElements = false) {
		$this->node =& $channel;
		$this->rssDefinedElements = array('title','link','description','language','copyright',
											'managingEditor','webmaster','pubDate','lastBuildDate',
											'generator','docs','cloud','ttl','image', 'rating',
											'textInput','skipHours','skipDays','domit_rss_channels',
											'domit_rss_items','domit_rss_categories');
		$this->_init();
	} //xml_domit_rss_channel
	
	/**
	* Performs initialization of the RSS channel element
	*/
	function _init() {
		$total = $this->node->childCount;
		$itemCounter = 0;
		$categoryCounter = 0;
		
		for ($i = 0; $i < $total; $i++) {
			$currNode =& $this->node->childNodes[$i];
			$tagName = strtolower($currNode->nodeName);
			
			switch($tagName) {
				case DOMIT_RSS_ELEMENT_ITEM:
					$this->domit_rss_items[$itemCounter] =& new xml_domit_rss_item($currNode);
					$itemCounter++;
					break;
				case DOMIT_RSS_ELEMENT_CATEGORY:
					$this->domit_rss_categories[$categoryCounter] =& new xml_domit_rss_category($currNode);
					$categoryCounter++;
					break;
				case DOMIT_RSS_ELEMENT_IMAGE:
					$this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_image($currNode);
					break;
				case DOMIT_RSS_ELEMENT_CLOUD:
					$this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_cloud($currNode);
					break;
				case DOMIT_RSS_ELEMENT_TEXTINPUT:
					$this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_textinput($currNode);
					break;
                case DOMIT_RSS_ELEMENT_TITLE:
                case DOMIT_RSS_ELEMENT_LINK:
                case DOMIT_RSS_ELEMENT_DESCRIPTION:
                case DOMIT_RSS_ELEMENT_LANGUAGE:
				case DOMIT_RSS_ELEMENT_COPYRIGHT:
				case DOMIT_RSS_ELEMENT_MANAGINGEDITOR:
				case DOMIT_RSS_ELEMENT_WEBMASTER:
				case DOMIT_RSS_ELEMENT_PUBDATE:
				case DOMIT_RSS_ELEMENT_LASTBUILDDATE:
				case DOMIT_RSS_ELEMENT_GENERATOR:
				case DOMIT_RSS_ELEMENT_DOCS:
				case DOMIT_RSS_ELEMENT_TTL:
				case DOMIT_RSS_ELEMENT_RATING:
				case DOMIT_RSS_ELEMENT_SKIPHOURS:
				case DOMIT_RSS_ELEMENT_SKIPDAYS:
				    $this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_simpleelement($currNode);
				    break;
				default:
				    $this->addIndexedElement($currNode);
					//$this->DOMIT_RSS_indexer[$tagName] =& $currNode;
			}
		}
		
		if ($itemCounter != 0) {
			$this->DOMIT_RSS_indexer[DOMIT_RSS_ARRAY_ITEMS] =& $this->domit_rss_items;
		}
		
		if ($categoryCounter != 0) {
			$this->DOMIT_RSS_indexer[DOMIT_RSS_ARRAY_CATEGORIES] =& $this->domit_rss_categories;
		}
	} //_init		
	
	/**
	* Returns the title of the channel
	* @return string The title of the channel, or an empty string
	*/
	function getTitle() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_TITLE);
	} //getTitle	
	
	/**
	* Returns the url of the channel
	* @return string The url of the channel, or an empty string
	*/
	function getLink() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_LINK);
	} //getLink
	
	/**
	* Returns a description of the channel
	* @return string A description of the channel, or an empty string
	*/
	function getDescription() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_DESCRIPTION);
	} //getDescription
	
	/**
	* Returns the number of items in the channel
	* @return int The number of items in the channel
	*/
	function getItemCount() {
		return count($this->domit_rss_items);
	} //getItemCount
	
	/**
	* Returns a reference to the item at the specified index
	* @param int The index of the requested item
	* @return Object A reference to the item at the specified index
	*/
	function &getItem($index) {
		return $this->domit_rss_items[$index];
	} //getItem
	
	/**
	* Returns the number of categories in the channel
	* @return int The number of categories in the channel
	*/
	function getCategoryCount() {
		return count($this->domit_rss_categories);
	} //getCategoryCount

	/**
	* Returns a reference to the category at the specified index
	* @param int The index of the requested category
	* @return Object A reference to the category at the specified index
	*/
	function &getCategory($index) {
		return $this->domit_rss_categories[$index];
	} //getCategory
} //xml_domit_rss_channel

/**
* Represents an RSS item
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_item extends xml_domit_rss_elementindexer {
	/** @var array A list of references to category items */
	var $domit_rss_categories = array();
	
	/**
	* Constructor
	* @param Object A DOM node containing item data
	*/
	function xml_domit_rss_item(&$item) {
		$this->node =& $item;
		$this->rssDefinedElements = array('title','link','description','author','comments',
											'enclosure','guid','pubDate','source','domit_rss_categories');
		$this->_init();
	} //xml_domit_rss_item
	
	/**
	* Performs initialization of the item element
	*/
	function _init(){
		$total = $this->node->childCount;
		$categoryCounter = 0;
		
		for($i = 0; $i < $total; $i++) {
			$currNode =& $this->node->childNodes[$i];
			$tagName = strtolower($currNode->nodeName);
		
		    switch ($tagName) {
		        case DOMIT_RSS_ELEMENT_CATEGORY:
		            $this->categories[$categoryCounter] =& new xml_domit_rss_category($currNode);
					$categoryCounter++;
		            break;
                case DOMIT_RSS_ELEMENT_ENCLOSURE:
                    $this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_enclosure($currNode);
		            break;
                case DOMIT_RSS_ELEMENT_SOURCE:
                    $this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_source($currNode);
		            break;
                case DOMIT_RSS_ELEMENT_GUID:
                    $this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_guid($currNode);
		            break;
                case DOMIT_RSS_ELEMENT_TITLE:
                case DOMIT_RSS_ELEMENT_LINK:
                case DOMIT_RSS_ELEMENT_DESCRIPTION:
                case DOMIT_RSS_ELEMENT_AUTHOR:
				case DOMIT_RSS_ELEMENT_COMMENTS:
				case DOMIT_RSS_ELEMENT_PUBDATE:
				    $this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_simpleelement($currNode);
				    break;
				default:
				    $this->addIndexedElement($currNode);
				    //$this->DOMIT_RSS_indexer[$tagName] =& $currNode;
		    }
		}
		
		if ($categoryCounter != 0) {
			$this->DOMIT_RSS_indexer[DOMIT_RSS_ARRAY_CATEGORIES] =& $this->domit_rss_categories;
		}
	} //init
	
	/**
	* Returns the title of the item
	* @return string The title of the item, or an empty string
	*/
	function getTitle() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_TITLE);
	} //getTitle	
	
	/**
	* Returns the url of the item
	* @return string The url of the item, or an empty string
	*/
	function getLink() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_LINK);
	} //getLink
	
	/**
	* Returns a description of the item
	* @return string A description of the item, or an empty string
	*/
	function getDescription() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_DESCRIPTION);
	} //getDescription
	
	/**
	* Returns the number of categories in the item
	* @return int The number of categories in the item
	*/
	function getCategoryCount() {
		return count($this->domit_rss_categories);
	} //getCategoryCount

	/**
	* Returns a reference to the category at the specified index
	* @param int The index of the requested category
	* @return Object A reference to the category at the specified index
	*/
	function &getCategory($index) {
		return $this->domit_rss_categories[$index];
	} //getCategory
} //xml_domit_rss_item


/**
* Represents an RSS category
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_category extends xml_domit_rss_elementindexer {
	/**
	* Constructor
	* @param Object A DOM node containing category data
	*/
	function xml_domit_rss_category(&$category) {
		$this->node =& $category;
		$this->_init();
	} //xml_domit_rss_category
	
	/**
	* Returns the category
	* @return string The category text
	*/
	function getCategory() {
		return $this->node->firstChild->toString();
	} //getCategory	
	
	/**
	* Returns the category domain
	* @return string The category domain
	*/
	function getDomain() {
		return $this->getAttribute(DOMIT_RSS_ATTR_DOMAIN);
	} //getDomain
} //xml_domit_rss_category

/**
* Represents an RSS image
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_image extends xml_domit_rss_elementindexer {
	/**
	* Constructor
	* @param Object A DOM node containing image data
	*/
	function xml_domit_rss_image(&$image) {
		$this->node =& $image;
		$this->rssDefinedElements = array('title','link','description','url',
											'width', 'height');
		$this->_init();
	} //xml_domit_rss_image
	
	/**
	* Performs initialization of image element
	*/
	function _init(){
		$total = $this->node->childCount;

		for($i = 0; $i < $total; $i++) {
			$currNode =& $this->node->childNodes[$i];
			$tagName = strtolower($currNode->nodeName);

		    switch ($tagName) {
                case DOMIT_RSS_ELEMENT_TITLE:
                case DOMIT_RSS_ELEMENT_LINK:
                case DOMIT_RSS_ELEMENT_DESCRIPTION:
                case DOMIT_RSS_ELEMENT_URL:
				case DOMIT_RSS_ELEMENT_WIDTH:
				case DOMIT_RSS_ELEMENT_HEIGHT:
				    $this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_simpleelement($currNode);
				    break;
				default:
				    $this->addIndexedElement($currNode);
				    //$this->DOMIT_RSS_indexer[$tagName] =& $currNode;
		    }
		}
	} //_init
	
	/**
	* Returns the image title
	* @return string The image title
	*/
	function getTitle() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_TITLE);
	} //getTitle	
	
	/**
	* Returns the image link
	* @return string The image link
	*/
	function getLink() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_LINK);
	} //getLink
	
	/**
	* Returns the image url
	* @return string The image url
	*/
	function getUrl() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_URL);
	} //getUrl
	
	/**
	* Returns the image width in pixels
	* @return string The image width (maximum 144, default 88)
	*/
	function getWidth() {
		$myWidth = $this->getElementText(DOMIT_RSS_ELEMENT_WIDTH);
		
		if ($myWidth == '') {
			$myWidth = '88';
		}
		else if (intval($myWidth) > 144) {
	        $myWidth = '144';
		}
		
		return $myWidth;
	} //getWidth
	
	/**
	* Returns the image height in pixels
	* @return string The image height (maximum 400, default 31)
	*/
	function getHeight() {
		$myHeight = $this->getElementText(DOMIT_RSS_ELEMENT_HEIGHT);

		if ($myHeight == '') {
			$myHeight = '31';
		}
		else if (intval($myHeight) > 400) {
	        $myHeight = '400';
		}

		return $myHeight;
	} //getHeight
	
	/**
	* Returns the image description
	* @return string The image description
	*/
	function getDescription() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_DESCRIPTION);
	} //getDescription
} //xml_domit_rss_image

/**
* Represents an RSS text input form
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_textinput extends xml_domit_rss_elementindexer {
	/**
	* Constructor
	* @param Object A DOM node containing text input data
	*/
	function xml_domit_rss_textinput(&$textinput) {
		$this->node =& $textinput;
		$this->rssDefinedElements = array('title','link','description','name');
		$this->_init();
	} //xml_domit_rss_textinput
	
	/**
	* Performs initialization of textInput element
	*/
	function _init(){
		$total = $this->node->childCount;

		for($i = 0; $i < $total; $i++) {
			$currNode =& $this->node->childNodes[$i];
			$tagName = strtolower($currNode->nodeName);

		    switch ($tagName) {
                case DOMIT_RSS_ELEMENT_TITLE:
                case DOMIT_RSS_ELEMENT_LINK:
                case DOMIT_RSS_ELEMENT_DESCRIPTION:
                case DOMIT_RSS_ELEMENT_NAME:
				    $this->DOMIT_RSS_indexer[$tagName] =& new xml_domit_rss_simpleelement($currNode);
				    break;
				default:
				    $this->addIndexedElement($currNode);
				    //$this->DOMIT_RSS_indexer[$tagName] =& $currNode;
		    }
		}
	} //_init
	
	/**
	* Returns the title of the text input
	* @return string The title of the text input, or an empty string
	*/
	function getTitle() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_TITLE);
	} //getTitle	
	
	/**
	* Returns a description of the text input
	* @return string A description of the text input, or an empty string
	*/
	function getDescription() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_DESCRIPTION);
	} //getDescription
	
	/**
	* Returns the name of the text input
	* @return string The name of the text input, or an empty string
	*/
	function getName() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_NAME);
	} //getName
	
	/**
	* Returns the url of the text input
	* @return string The url of the text input, or an empty string
	*/
	function getLink() {
		return $this->getElementText(DOMIT_RSS_ELEMENT_LINK);
	} //getLink
} //xml_domit_rss_textinput

/**
* Represents an RSS cloud
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_cloud extends xml_domit_rss_elementindexer {
	/**
	* Constructor
	* @param Object A DOM node containing cloud data
	*/
	function xml_domit_rss_cloud(&$cloud) {
		$this->node =& $cloud;
		$this->_init();
	} //xml_domit_rss_cloud
	
	/**
	* Returns the domain of the cloud
	* @return string The domain of the cloud
	*/
	function getDomain() {
		return $this->getAttribute(DOMIT_RSS_ATTR_DOMAIN);
	} //getDomain	
	
	/**
	* Returns the port of the cloud
	* @return string The port of the cloud
	*/
	function getPort() {
		return $this->getAttribute(DOMIT_RSS_ATTR_PORT);
	} //getPort
	
	/**
	* Returns the path of the cloud
	* @return string The path of the cloud
	*/
	function getPath() {
		return $this->getAttribute(DOMIT_RSS_ATTR_PATH);
	} //getPath
	
	/**
	* Returns the register procedure value of the cloud
	* @return string The register procedure value of the cloud
	*/
	function getRegisterProcedure() {
		return $this->getAttribute(DOMIT_RSS_ATTR_REGISTERPROCEDURE);
	} //getRegisterProcedure
	
	/**
	* Returns the protocol used by the cloud
	* @return string The protocol used by the cloud
	*/
	function getProtocol() {
		return $this->getAttribute(DOMIT_RSS_ATTR_PROTOCOL);
	} //getProtocol
} //xml_domit_rss_cloud

/**
* Represents an RSS enclosure
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_enclosure extends xml_domit_rss_elementindexer {
	/**
	* Constructor
	* @param Object A DOM node containing enclosure data
	*/
	function xml_domit_rss_enclosure(&$enclosure) {
		$this->node =& $enclosure;
		$this->_init();
	} //xml_domit_rss_enclosure

	/**
	* Returns the url of the enclosure
	* @return string The url of the enclosure
	*/
	function getUrl() {
		return $this->getAttribute(DOMIT_RSS_ATTR_URL);
	} //getUrl	
	
	/**
	* Returns the length of the enclosure
	* @return string The length of the enclosure
	*/
	function getLength() {
		return $this->getAttribute(DOMIT_RSS_ATTR_LENGTH);
	} //getLength
	
	/**
	* Returns the type of the enclosure
	* @return string The type of the enclosure
	*/
	function getType() {
		return $this->getAttribute(DOMIT_RSS_ATTR_TYPE);
	} //getType
} //xml_domit_rss_enclosure

/**
* Represents an RSS guid
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_guid extends xml_domit_rss_elementindexer {
	/**
	* Constructor
	* @param Object A DOM node containing guid data
	*/
	function xml_domit_rss_guid(&$guid) {
		$this->node =& $guid;
		$this->_init();
	} //xml_domit_rss_guid
	
	/**
	* Returns the guid text
	* @return string The guid text
	*/
	function getGuid() {
	    return $this->node->getText();
	} //getGuid
	
	/**
	* Determines whether the guid is a permalink
	* @return boolean True if the guid is a permalink (default true)
	*/
	function isPermaLink() {
	    if (!$this->node->hasAttribute(DOMIT_RSS_ATTR_ISPERMALINK)) {
	        return true;
	    }
	    else {
			return (strtolower($this->node->getAttribute(DOMIT_RSS_ATTR_ISPERMALINK)) == "true");
	    }
	} //isPermaLink
} //xml_domit_rss_guid

/**
* Represents an RSS source
*
* @package domit-rss
* @subpackage domit-rss-main
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class xml_domit_rss_source extends xml_domit_rss_elementindexer {
	/**
	* Constructor
	* @param Object A DOM node containing source data
	*/
	function xml_domit_rss_source(&$source) {
		$this->node =& $source;
		$this->_init();
	} //xml_domit_rss_source

	/**
	* Returns the source text
	* @return string The source text
	*/
	function getSource() {
	    return $this->node->getText();
	} //getSource
	
	/**
	* Returns the url of the source
	* @return string The url of the source
	*/
	function getUrl() {
		return $this->getAttribute(DOMIT_RSS_ATTR_URL);
	} //getUrl
} //xml_domit_rss_source

?>
