<?php

/**
 * Child class of adminGalleryStats handling the search statiscis plugin results
 * 
 * @since 1.6.6
 * 
 * @author Malte Müller (acrylian) adapted from procedural plugin code by Stephen Billard (sbillard)
 * @package admin
 * @package zpcore\plugins\searchstatistics
 */
class adminGalleryStatsSearch extends adminGalleryStats {

	protected $basedata = null;
	protected $ips = null;
	protected $maxiterations = array();
	protected $skipslicing = false;
	protected static $pagepath = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/search_statistics/search_analysis.php?page=searchstatistics&amp;tab=search';

	/**
	 * Gets the base db query for some requests
	 * 
	 * @since 1.6.6
	 * 
	 * @global obj $_zp_db
	 * @return string
	 */
	function getDBQueryBase() {
		global $_zp_db;
		return 'SELECT * FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type`="search_statistics"';
	}

	/**
	 * Gets the db query LIMIt part generated  from the from and to values 
	 * 
	 * @since 1.6.6
	 * 
	 * @return string
	 */
	function getDBQueryLimit() {
		return null;
	}

	/**
	 * Gets an nestsed array of supported items types as key asn and array with the gettext type title and an array of supported sortorders
	 * 
	 * @since 1.6.6
	 * 
	 * @return array
	 */
	static function getSupportedTypes() {
		return array(
				'search' => array(
						'title' => gettext('Search'),
						'sortorders' => array(
								'successfulsearchcriteria',
								'failedsearchcriteria',
								'mostusedterms',
								'mostusedips'
						)
				)
		);
	}

	/**
	 * Gets an array with the all possible sortorders as key and the gettext names as values
	 * 
	 * @since 1.6.6
	 * 
	 * @return array
	 */
	static function getSortorders() {
		return array(
				'successfulsearchcriteria' => gettext('Successful search criteria'),
				'failedsearchcriteria' => gettext('Failed search criteria'),
				'mostusedterms' => gettext('Search terms used'),
				'mostusedips' => gettext('Search IPs'),
		);
	}

	/**
	 * Gets the max value of total items statistics
	 * 
	 * Depends on getItems() already been used!
	 * 
	 * @since 1.6.6
	 *
	 * @return int
	 */
	function getMaxvalue() {
		$itemssorted = $this->getItems();
		if (empty($itemssorted)) {
			return 0;
		} else {
			return $itemssorted[0]['value'];
		}
	}

	/**
	 * Gets the items as requested
	 * 
	 * @since 1.6.6
	 *
	 * @return array 
	 */
	function getItems() {
		if (!is_null($this->items)) {
			return $this->items;
		}
		switch ($this->sortorder) {
			case 'successfulsearchcriteria':
				return $this->items = $this->getSuccessfulSearchCriteria();
			case 'failedsearchcriteria':
				return $this->items = $this->getFailedSearchCriteria();
			case 'mostusedterms':
				return $this->items = $this->getSearchTerms();
			case 'mostusedips':
				return $this->items = $this->getSearchIPs();
		}
	}

	/**
	 * Fixes the from number if exceeding the results count since we don't use a db query here but array_slice
	 * 
	 * @since 1.6.6
	 *
	 * @param array $results Thefinal query result
	 */
	private function setFromNumber($results) {
		$totalcount = count($results);
		if ($this->from_number >= $totalcount) {
			$this->from_number = 0;
		}
	}

	/**
	 * Gets the unprocessed base data and internally cache the resutl
	 * 
	 * @since 1.6.6
	 *
	 * @global obj $_zp_db
	 * @return array
	 */
	private function getBasedata() {
		global $_zp_db;
		if (!is_null($this->basedata)) {
			return $this->basedata;
		}
		return $_zp_db->query($this->getDBQueryBase());
	}

	/**
	 * Returns the most successful search terms
	 * 
	 * @since 1.6.6
	 *
	 * @global obj $_zp_db
	 * @param bool $skipslicing
	 * @return array
	 */
	private function getSuccessfulSearchCriteria($skipslicing = false) {
		global $_zp_db;
		$data = $this->getBasedata();
		if ($data) {
			$results = array();
			while ($datum = $_zp_db->fetchAssoc($data)) {
				$element = getSerializedArray($datum['data']);
				if (is_array($element)) {
					$this->maxiterations[$element['iteration']] = 1;
					$searchset = $element['data'];
					$success = $element['success'];
					$instance = implode(' ', $searchset);
					if ($success) {
						if (array_key_exists($instance, $results)) {
							$results[$instance]++;
						} else {
							$results[$instance] = 1;
						}
					}
				}
			}
		}
		$_zp_db->freeResult($data);
		if ($skipslicing) {
			return $results;
		}
		return $this->getResultsSlice($results);
	}

	/**
	 * Returns the most used failed searches
	 * 
	 * @since 1.6.6
	 *
	 * @global obj $_zp_db
	 * @param bool $skipslicing
	 * @return array
	 */
	private function getFailedSearchCriteria($skipslicing = false) {
		global $_zp_db;
		$data = $this->getBasedata();
		$successes = $this->getSuccessfulSearchCriteria(true);

		if ($data) {
			$results = array();
			while ($datum = $_zp_db->fetchAssoc($data)) {
				$element = getSerializedArray($datum['data']);
				if (is_array($element)) {
					$this->maxiterations[$element['iteration']] = 1; // I need this as we store multiple entries of the same search…
					$searchset = $element['data'];
					$success = $element['success'];
					$instance = implode(' ', $searchset);
					if (!$success) {
						if (array_key_exists($instance, $results)) {
							$results[$instance]++;
						} else {
							$results[$instance] = 1;
						}
					}
				}
			}
		}
		$_zp_db->freeResult($data);
		foreach ($results as $key => $failed) {
			if (array_key_exists($key, $successes)) { // really a successful search
				unset($results[$key]);
			}
		}
		if ($skipslicing) {
			return $results;
		}
		return $this->getResultsSlice($results);
	}

	/**
	 * Returns the most used search terms
	 * 
	 * @since 1.6.6
	 *
	 * @global obj $_zp_db
	 * @param bool $skipslicing
	 * @return array
	 */
	private function getSearchTerms($skipslicing = false) {
		global $_zp_db;
		$data = $this->getBasedata();
		$opChars = array('(', ')', '&', '|', '!', ',');
		if ($data) {
			$results = array();
			while ($datum = $_zp_db->fetchAssoc($data)) {
				$element = getSerializedArray($datum['data']);
				if (is_array($element)) {
					$searchset = $element['data'];
					foreach ($searchset as $instance) {
						if (!in_array($instance, $opChars)) {
							if (array_key_exists($instance, $results)) {
								$results[$instance]++;
							} else {
								$results[$instance] = 1;
							}
						}
					}
				}
			}
		}
		$_zp_db->freeResult($data);
		if ($skipslicing) {
			return $results;
		}
		return $this->getResultsSlice($results);
	}

	/**
	 * Returns the IP addressses that searched most
	 * 
	 * @since 1.6.6
	 *
	 * @global obj $_zp_db
	 * @param bool $skipslicing
	 * @return array
	 */
	private function getSearchIPs($skipslicing = false) {
		global $_zp_db;
		$data = $this->getBasedata();
		if ($data) {
			$results = array();
			while ($datum = $_zp_db->fetchAssoc($data)) {
				$ip = $datum['aux'];
				if (array_key_exists($ip, $results)) {
					$results[$ip]++;
				} else {
					$results[$ip] = 1;
				}
			}
		}
		$_zp_db->freeResult($data);
		if ($skipslicing) {
			return $results;
		}
		return $this->getResultsSlice($results);
	}

	/**
	 * Returns a from/to number slice of the results and processes the results array for output
	 * 
	 * @since 1.6.6
	 *
	 * @param array $results 
	 * @return array
	 */
	private function getResultsSlice($results) {
		if ($results) {
			asort($results);
			arsort($results);
			$this->setFromNumber($results);
			$results_final = array();
			$slice = array_slice($results, $this->from_number, $this->to_number, true);
			foreach ($slice as $key => $value) {
				$results_final[] = array(
						'title' => $key,
						'value' => $value
				);
			}
			unset($results);
			return $results_final;
		}
		return array();
	}

	/**
	 * Gets the message if there are no statistics available
	 * 
	 * @since 1.6.6
	 *
	 * @return string
	 */
	function getNoStatisticsMessage() {
		$no_statistic_message = '';
		if ($this->getMaxvalue() == 0 || empty($this->getItems())) {
			$no_hitcount_enabled_msg = '';
			if (!extensionEnabled('search_statistics')) {
				$no_hitcount_enabled_msg = gettext("(The search_statistics plugin is not enabled.)");
			}
			$no_statistic_message = '<tr><td colspan="5"><em>' . gettext("No statistic available.") . $no_hitcount_enabled_msg . '</em></td></tr>';
		} else {
			$no_statistic_message = "";
			if (!extensionEnabled('search_statistics')) {
				$no_statistic_message = "<tr><td colspan='5'><em>" . gettext("Note: The search_statistics plugin is not enabled, therefore any existing values will not get updated.") . "</em></td></tr>";
			}
		}
		return $no_statistic_message;
	}

	/**
	 *  Gets the bar size for an item value
	 * 
	 * @since 1.6.6
	 *
	 * @param array $item
	 * @return int
	 */
	function getItemBarSize($item) {
		if ($this->getMaxvalue() == 0) {
			return 0;
		}
		//$count = round($count / $this->maxiterations);
		return round($item['value'] / $this->getMaxvalue() * $this->bargraphmaxsize);
	}

	/**
	 * Gets the value of an item
	 * 
	 * @since 1.6.6
	 * 
	 * @param array $item
	 * @return string|int
	 */
	function getItemValue($item) {
		return $item['value'];
	}

	/**
	 * Gets the item name. 
	 * 
	 * @since 1.6.6
	 *
	 * @param array $item Array of the item
	 * @return string
	 */
	function getItemName($item) {
		return $item['title']; 
	}

	/**
	 * Gets an array with the thumb, editurl, viewurl and title as available
	 * 
	 * @since 1.6.6
	 * 
	 * @global obj $_zp_db
	 * @param array $item Item array
	 * @return array 
	 */
	function getEntryData($item) {
		$name = $this->getItemName($item);
		$data = array(
				'thumb' => '',
				'editurl' => '',
				'viewurl' => '',
				'title' => $item['title'],
				'name' => ''
		);
		if (empty($name) || $name == $data['title']) {
			$data['name'] = "";
		} else {
			$data['name'] = "(" . $name . ")";
		}
		return $data;
	}

	/**
	 * Returns an array with the view more url and title if applicable
	 * 
	 * @since 1.6.6
	 * 
	 * @return array
	 */
	function getViewMoreData() {
		$data = array(
				'viewmoreurl' => '',
				'viewmoreurl_title' => ''
		);
		if (isset($_GET['sortorder'])) {
			$data['viewmoreurl'] = static::$pagepath;
			$data['viewmoreurl_title'] = gettext("Back to the top 10 lists") . ' &rarr;';
		} else {
			$data['viewmoreurl_title'] = gettext("View more") . ' &rarr;';
			if (!$this->getNoStatisticsMessage()) {
				$data['viewmoreurl'] =static::$pagepath . '&amp;sortorder=' . $this->sortorder;
			}
		}
		return $data;
	}
	
	/**
	 * Gets the action URL for from/to single stats form
	 * 
	 * @since 1.6.6.
	 * 
	 * @param string $stats The sortorder
	 * @param string $type The item type 
	 * @return string
	 */
	static function getSingleStatSelectionFormActionURL($stats = '', $type = '') {
		return static::$pagepath;
	}
	
}
