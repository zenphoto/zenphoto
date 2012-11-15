<?php
/**
 *
 * This plugin gathers data about searches that users make on your site.
 *
 * Notes on the analysis of the data:
 *
 *  	Searches the result of Dynamic album processing are ignored
 *
 *		Analysis presumes that the Theme does a "uniform" set of object retrievals. That
 *		is the <var>search.php</var> script will always request albums, images, pages, and/or news
 *		consistently. Data collection happens for each of these objects so to "normalize"
 *		the data the analysis will divide the data by the number of objects searched.
 *
 *   So, if for instance, you sometimes enable Zenpage results, sometimes there will be results
 *   for images, albums, pages, and news; and other times there will just be results for
 *   images and albums. In this case the reports will under value the searches done when
 *   Zenpage results were not enabled.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage admin
 */
$plugin_is_filter = 2|CLASS_PLUGIN;
$plugin_description = gettext("Collects and displays search criteria.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'search_statistics';

zp_register_filter('search_statistics','search_statistics::handler');
zp_register_filter('admin_utilities_buttons', 'search_statistics::button');

/**
 * Option handler class
 *
 */
class search_statistics {
	var $ratingstate;
	/**
	 * class instantiation function
	 *
	 * @return jquery_rating
	 */
	function search_statistics() {
		setOptionDefault('search_statistics_threshold', 25);
		setOptionDefault('search_statistics_terms_threshold', 25);
		setOptionDefault('search_statistics_failed_threshold', 10);
		setOptionDefault('search_statistics_ip_threshold', 10);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(	gettext('Threshold (success)') => array('key' => 'search_statistics_threshold', 'type' => OPTION_TYPE_TEXTBOX,
										'order' =>1,
										'desc' => gettext('Show the top <em>Threshold</em> criteria of successful searches. (Searches which returned at least one result.)')),
									gettext('Threshold (failed)') => array('key' => 'search_statistics_failed_threshold', 'type' => OPTION_TYPE_TEXTBOX,
										'order' =>2,
										'desc' => gettext('Show the top <em>Threshold</em> criteria of searches that failed.')),
									gettext('Threshold (terms)') => array('key' => 'search_statistics_terms_threshold', 'type' => OPTION_TYPE_TEXTBOX,
										'order' =>3,
										'desc' => gettext('Show the top <em>Threshold</em> terms used in searches.')),
									gettext('Threshold (IP)') => array('key' => 'search_statistics_ip_threshold', 'type' => OPTION_TYPE_TEXTBOX,
										'order' =>4,
										'desc' => gettext('Show the top <em>Threshold</em> IPs that have performed searches.'))
									);
	}

	function handleOption($option, $currentValue) {
	}


	static function button($buttons) {
		$buttons[] = array(
									'category'=>gettext('Info'),
									'enable'=>true,
									'button_text'=>gettext('Search statistics'),
									'formname'=>'search_statistics_button',
									'action'=>PLUGIN_FOLDER.'/search_statistics/search_analysis.php',
									'icon'=>'images/bar_graph.png',
									'title'=>gettext('Analyze searches'),
									'alt'=>'',
									'hidden'=> '',
									'rights'=> OVERVIEW_RIGHTS,
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
		if (!$dynamic) {	// log unique user searches
			$store = array('type'=>$type, 'success'=>$success, 'iteration'=>$iteration, 'data'=>$search_statistics);
			$sql = 'INSERT INTO '.prefix('plugin_storage').' (`type`, `aux`,`data`) VALUES ("search_statistics", '.db_quote(getUserIP()).','.db_quote(serialize($store)).')';
			query($sql);
		}
		return $search_statistics;
	}

}
?>