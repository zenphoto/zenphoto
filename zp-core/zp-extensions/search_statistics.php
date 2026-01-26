<?php
/**
 *
 * This plugin gathers data about searches that users make on your site.
 *
 * Notes on the analysis of the data:
 *
 *  	The search results used for Dynamic album processing are ignored.
 *
 *		Analysis presumes that the Theme does a "uniform" set of object retrievals. That
 *		is, the <var>search.php</var> script will always request albums, images, pages, and/or news
 *		consistently. Data collection happens for each of these objects so to "normalize"
 *		the data the analysis will divide the data by the number of objects searched.
 *
 *   So, if for instance, you sometimes enable Zenpage results, sometimes there will be results
 *   for images, albums, pages, and news; and other times there will just be results for
 *   images and albums. In this case the reports will under value the searches done when
 *   Zenpage results were not enabled.
 *
 * @author Stephen Billard (sbillard)
 * @package zpcore\plugins\searchstatistics
 */
$plugin_is_filter = 2|CLASS_PLUGIN;
$plugin_description = gettext("Collects and displays search criteria.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_category = gettext('Statistics');

filter::registerFilter('search_statistics','search_statistics::handler');
filter::registerFilter('admin_utilities_buttons', 'search_statistics::button');

/**
 * Option handler class
 *
 */
class search_statistics {
	public $ratingstate;
	/**
	 * class instantiation function
	 *
	 * @return jquery_rating
	 */
	function __construct() {
		purgeOption('search_statistics_threshold');
		purgeOption('search_statistics_terms_threshold');
		purgeOption('search_statistics_failed_threshold');
		purgeOption('search_statistics_ip_threshold');
	}

	static function button($buttons) {
		$buttons[] = array(
				'category' => gettext('Info'),
				'enable' => true,
				'button_text' => gettext('Search statistics'),
				'formname' => 'search_statistics_button',
				'action' => FULLWEBPATH . '/'. ZENFOLDER . '/' . PLUGIN_FOLDER . '/search_statistics/search_analysis.php',
				'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/bar_graph.png',
				'title' => gettext('Analyze searches'),
				'alt' => '',
				'hidden' => '',
				'rights' => OVERVIEW_RIGHTS,
		);
		return $buttons;
	}

	/**
	 *
	 * Logs User searches
	 * @param array $search_statistics the search criteria
	 * @param string $type 'album', 'image', etc.
	 * @param bool $success	did the search return a result
	 * @param bool $dynamic was it from a dynamic album
	 * @param int $iteration count of the filters since the search engine instantiation
	 */
	static function handler($search_statistics, $type, $success, $dynamic, $iteration) {
		global $_zp_db;
		if (!$dynamic) {	// log unique user searches
			$store = array('type'=>$type, 'success'=>$success, 'iteration'=>$iteration, 'data'=>$search_statistics);
			$sql = 'INSERT INTO '.$_zp_db->prefix('plugin_storage').' (`type`, `aux`,`data`) VALUES ("search_statistics", '.$_zp_db->quote(getUserIP()).','.$_zp_db->quote(serialize($store)).')';
			$_zp_db->query($sql);
		}
		return $search_statistics;
	}

}