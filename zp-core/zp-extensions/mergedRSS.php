<?php

/**
 * Merges several RSS feeds into one stream.
 *
 * Based on David Stinemetze's {@link http://www.widgetsandburritos.com/technical/programming/merge-rss-feeds-php-cache/ MergedRSS Class}
 *
 * If you ever wanted to have a Zenphoto RSS that for example returns latest images for <i>album x</i>
 * but latest albums for <i>album y</i> this is the tool to use.
 * Just enter the urls of the seperate feeds and it will return the combined feed.
 *
 * While this plugin is mean for Zenphoto's RSS, you could also use even external RSS feeds.
 * But be aware that hijacking content may be a vialation of applicable laws!
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 */
$plugin_description = gettext("Merges several RSS feeds into one.");
$plugin_author = "Malte Müller (acrylian)";
$plugin_disable = (class_exists('SimpleXMLElement')) ? false : gettext('PHP <em>SimpleXML</em> is required.');
$option_interface = 'MergedRSSOptions';

// Create the merged rss feed
if (isset($_GET['mergedrss'])) {
	// place our feeds in an array
	$feeds = getOption('mergedrss_feeds');
	$feeds = explode(';', $feeds);
	if (count($feeds) < 0) {
		exitZP();
	}
	// set the header type
	header("Content-type: text/xml");

	// set an arbitrary feed date
	$RSS_date = date("r", mktime(10, 0, 0, 9, 8, 2010));
	if (isset($_GET['lang'])) {
		$locale = sanitize($_GET['lang']);
	} else {
		$locale = getOption('locale');
	}
	$gallery = new Gallery();
	// Create new MergedRSS object with desired parameters
	$MergedRSS = new MergedRSS($feeds, strip_tags(get_language_string($gallery->getTitle(), $locale)), FULLWEBPATH, strip_tags(get_language_string($gallery->getDesc(), $locale)), $RSS_date);

	//Export the first 10 items to screen
	$MergedRSS->export(false, true, 20); //getOption('RSS_items')
	// Retrieve the first 5 items as xml code
	//$xml = $MergedRSS->export(true, false, 5);
	exitZP();
}

class MergedRSSOptions {

	function getOptionsSupported() {
		return array(
						gettext('RSS feeds to merge') => array('key'					 => 'mergedrss_feeds', 'type'				 => OPTION_TYPE_TEXTAREA,
										'order'				 => 11,
										'multilingual' => false,
										'desc'				 => gettext('Enter the full urls of the feeds to merge separated by semicolons(e.g. "http://www.domain1.com/rss; http://www.domain2.com/rss")'))
		);
	}

	function handleOption($option, $currentValue) {

	}

}

class MergedRSS {

	private $myFeeds = null;
	private $myTitle = null;
	private $myLink = null;
	private $myDescription = null;
	private $myPubDate = null;
	private $myCacheTime = null;

	// create our Merged RSS Feed
	public function __construct($feeds, $channel_title = null, $channel_link = null, $channel_description = null, $channel_pubdate = null, $cache_time_in_seconds = 86400) {
		// set variables
		$this->myTitle = $channel_title;
		$this->myLink = $channel_link;
		$this->myDescription = $channel_description;
		$this->myPubDate = $channel_pubdate;
		$this->myCacheTime = $cache_time_in_seconds;

		// initialize feed variable
		$this->myFeeds = array();

		// check if it's an array.  if so, merge it into our existing array.  if it's a single feed, just push it into the array
		if (!is_array($feeds)) {
			$feeds = array($feeds);
		}
		foreach ($feeds as $feed) {
			$this->myFeeds[] = trim($feed);
		}
	}

	// exports the data as a returned value and/or outputted to the screen
	public function export($return_as_string = true, $output = false, $limit = null) {
		// initialize a combined item array for later
		$items = array();
		// loop through each feed
		foreach ($this->myFeeds as $RSS_url) {
			// determine my cache file name.  for now i assume they're all kept in a file called "cache"
			$cache_file = SERVERPATH . '/' . STATIC_CACHE_FOLDER . '/rss/' . self::create_RSS_key($RSS_url);
			// determine whether or not I should use the cached version of the xml
			$use_cache = file_exists($cache_file) && time() - filemtime($cache_file) < $this->myCacheTime;
			if ($use_cache) {
				// retrieve cached version
				$sxe = self::fetch_from_cache($cache_file);
				$results = $sxe->channel->item;
			} else {
				// retrieve updated rss feed
				$sxe = self::fetch_from_url($RSS_url);
				$results = $sxe->channel->item;

				if (!isset($results)) {
					// couldn't fetch from the url. grab a cached version if we can
					if (file_exists($cache_file)) {
						$sxe = self::fetch_from_cache($cache_file);
						$results = $sxe->channel->item;
					}
				} else {
					// we need to update the cache file
					$sxe->asXML($cache_file);
				}
			}

			if (isset($results)) {
				// add each item to the master item list
				foreach ($results as $item) {
					$items[] = $item;
				}
			}
		}
		// set all the initial, necessary xml data
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml .= "<rss version=\"2.0\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" xmlns:wfw=\"http://wellformedweb.org/CommentAPI/\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\" xmlns:slash=\"http://purl.org/rss/1.0/modules/slash/\">\n";
		$xml .= "<channel>\n";
		if (isset($this->myTitle)) {
			$xml .= "\t<title>" . $this->myTitle . "</title>\n";
		}
		$xml .= "\t<atom:link href=\"http://" . WEBPATH . '/' . str_replace(SERVERPATH, '', __FILE__) . "\" rel=\"self\" type=\"application/rss+xml\" />\n";
		if (isset($this->myLink)) {
			$xml .= "\t<link>" . $this->myLink . "</link>\n";
		}
		if (isset($this->myDescription)) {
			$xml .= "\t<description>" . $this->myDescription . "</description>\n";
		}
		if (isset($this->myPubDate)) {
			$xml .= "\t<pubDate>" . $this->myPubDate . "</pubDate>\n";
		}


		// if there are any items to add to the feed, let's do it
		if (sizeof($items) > 0) {

			// sort items
			usort($items, array($this, "self::compare_items"));

			// if desired, splice items into an array of the specified size
			if (isset($limit)) {
				array_splice($items, intval($limit));
			}

			// now let's convert all of our items to XML
			for ($i = 0; $i < sizeof($items); $i++) {
				$xml .= $items[$i]->asXML() . "\n";
			}
		}
		$xml .= "</channel>\n</rss>";

		// if output is desired print to screen
		if ($output) {
			echo $xml;
		}

		// if user wants results returned as a string, do so
		if ($return_as_string) {
			return $xml;
		}
	}

	// compares two items based on "pubDate"
	private static function compare_items($a, $b) {
		return strtotime($b->pubDate) - strtotime($a->pubDate);
	}

	// retrieves contents from a cache file ; returns null on error
	private static function fetch_from_cache($cache_file) {
		if (file_exists($cache_file)) {
			return simplexml_load_file($cache_file);
		}
		return null;
	}

	// retrieves contents of an external RSS feed ; implicitly returns null on error
	private static function fetch_from_url($url) {
		// Create new SimpleXMLElement instance
		$sxe = new SimpleXMLElement($url, null, true);
		return $sxe;
	}

	// creates a key for a specific feed url (used for creating friendly file names)
	private static function create_RSS_key($url) {
		return preg_replace('/[^a-zA-Z0-9\.]/', '_', $url) . 'cache';
	}

}

?>