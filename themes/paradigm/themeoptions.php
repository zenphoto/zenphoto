<?php

// force UTF-8 Ø

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 *
 */

class ThemeOptions {

	function __construct() {
		$me = basename(dirname(__FILE__));
		setThemeOptionDefault('albums_per_row', 4);
		setThemeOptionDefault('albums_per_page', 12);
		setThemeOptionDefault('images_per_row', 5);
		setThemeOptionDefault('images_per_page', 20);
		setThemeOptionDefault('thumb_size', 200);
		setThemeOptionDefault('thumb_crop', 0);
		setThemeOptionDefault('thumb_crop_width', 200);
		setThemeOptionDefault('thumb_crop_height', 200);
		setThemeOptionDefault('image_size', 650);
		setThemeOptionDefault('image_use_side', 'longest');
		setThemeOptionDefault('custom_index_page', '');
		
		setOptionDefault('gmap_width', '100%');
		setOptionDefault('htmlmeta_name-title', 0);
		setOptionDefault('htmlmeta_name-description', 0);

		setThemeOptionDefault('carousel_number', '5');
		setThemeOptionDefault('homepage_content', 'gallery');
		setThemeOptionDefault('Allow_search', true);
		setThemeOptionDefault('display_archive', false);

		if (class_exists('cacheManager')) {
			$me = basename(dirname(__FILE__));
			cacheManager::deleteThemeCacheSizes($me);
			cacheManager::addThemeCacheSize($me, getThemeOption('image_size'), NULL, NULL, NULL, NULL, NULL, NULL, false, NULL, NULL, NULL);
			cacheManager::addThemeCacheSize($me, getThemeOption('thumb_size'), NULL, NULL, getThemeOption('thumb_crop_width'), getThemeOption('thumb_crop_height'), NULL, NULL, true, NULL, NULL, NULL);
		}		
	}

	function getOptionsSupported() {

		global $_zp_gallery;
		$albumlist = array();
		$albumlist['Entire Gallery'] = '';
		$albums = getNestedAlbumList(null, 9999999, false);
		foreach($albums as $album) {
			$albumobj = newAlbum($album['name'], true);
			$albumlist[$album['name']] = $album['name'];
		}

		return array(
			gettext('Fluid')	=> array('key' => 'full_width', 'type' => OPTION_TYPE_CHECKBOX, 'order' => 0, 'desc' => gettext('Check to enable fluid full width layout.')),
			gettext('Homepage slideshow')	=> array('key' => 'homepage_slideshow', 'type' => OPTION_TYPE_CHECKBOX, 'order' => 1, 'desc' => gettext('Check to include a slideshow on the hompepage.')),
			gettext('Slideshow Type') => array('key' => 'carousel_type', 'type' => OPTION_TYPE_SELECTOR,
				'order' => 2, 
				'selections' => array(
					gettext('Random') => 'random', 
					gettext('Popular') => 'popular', 
					gettext('Latest by ID') => 'latestbyid', 
					gettext('Latest by Date') => 'latestbydate', 
					gettext('Latest by mtime') => 'latestbymtime', 
					gettext('Latest by Publish Date') => 'latestbypdate', 
					gettext('Most Rated') => 'mostrated', 
					gettext('Top Rated') => 'toprated'), 
				'desc' => gettext('Select how the pictures will be chosen for the homepage slideshow.')),
			gettext('Album to choose from') => array('key' => 'carousel_album', 'type' => OPTION_TYPE_SELECTOR,
				'order' => 3, 
				'selections' => $albumlist, 
				'desc' => gettext('Choose a specific album to display its pictures. Album needs to be published. Images are preferrably in panoramic format: 1920px wide')),
			gettext('Number of slides') => array('key' => 'carousel_number', 'type' => OPTION_TYPE_SELECTOR,
				'order' => 4, 
				'selections' => array(
					gettext('3') => '3', 
					gettext('4') => '4', 
					gettext('5') => '5', 
					gettext('6') => '6', 
					gettext('7') => '7', 
					gettext('8') => '8', 
					gettext('9') => '9', 
					gettext('10') => '10'), 
				'desc' => gettext('Select how the pictures will be chosen for the homepage slideshow. Default is 5')),

			gettext('Homepage blog') => array('key' => 'homepage_blog', 'type' => OPTION_TYPE_CHECKBOX, 'order' => 5, 'desc' => gettext('Check to enable blog posts as main content of the homepage.')),
			gettext('Homepage content') => array('key' => 'homepage_content', 'type' => OPTION_TYPE_RADIO, 'order' => 6,
				'buttons' => array(
					gettext('Albums') => 'albums',
					gettext('Latest pictures') => 'latest',
					gettext('Random pictures') => 'random'), 
				'desc' => gettext('Chose what to display on the homepage.')),

			gettext('Allow search')				=> array('key' => 'Allow_search', 'type' => OPTION_TYPE_CHECKBOX, 'order' => 7, 'desc' => gettext('Check to enable search form.')),
			gettext('Archive')				=> array('key' => 'display_archive', 'type' => OPTION_TYPE_CHECKBOX, 'order' => 8, 'desc' => gettext('Display archive link in footer.')),
			gettext('Google Analytics id')		=> array('key' => 'analytics_code', 'type' => OPTION_TYPE_CLEARTEXT, 'order' => 9, 'desc' => gettext('If you use <a href="http://www.google.com/analytics">Google Analytics</a>, paste your ID here')),
			gettext('ShareThis id')		=> array('key' => 'sharethis_id', 'type' => OPTION_TYPE_TEXTBOX, 'order' => 10, 'desc' => gettext('Provide your <a href="http://www.sharethis.com">ShareThis</a> ID')),
			gettext('AddThis code')		=> array('key' => 'addthis_code', 'type' => OPTION_TYPE_TEXTAREA, 'order' => 11, 'desc' => gettext('Write your <a href="http://www.addthis.com">Addthis</a> Code (the one under "Go to www.addthis.com/dashboard to customize your tools"). Use small buttons for AddThis. Do not add both Addthis and Sharethis!')),															
			gettext('URL to Facebook')		=> array('key' => 'facebook_url', 'type' => OPTION_TYPE_TEXTBOX, 'desc' => gettext('Provide your <a href="http://www.facebook.com">Facebook</a> page or profile URL')),
			gettext('Twitter profile name')		=> array('key' => 'twitter_profile', 'type' => OPTION_TYPE_TEXTBOX, 'desc' => gettext('Provide your <a href="http://www.twitter.com">Twitter</a> profile name (without the @)')),
			gettext('URL to Google Plus Page')		=> array('key' => 'googleplus_page_url', 'type' => OPTION_TYPE_TEXTBOX, 'desc' => gettext('Provide your <a href="http://plus.google.com">Google Plus</a> <em>page</em> URL')),						
			gettext('URL to FlickR')		=> array('key' => 'flickr_url', 'type' => OPTION_TYPE_TEXTBOX, 'desc' => gettext('Provide your <a href="http://www.flickr.com">FlickR</a> gallery URL')),
			gettext('URL to 500px')		=> array('key' => '500px_url', 'type' => OPTION_TYPE_TEXTBOX, 'desc' => gettext('Provide your <a href="http://500px.com">500px</a> gallery URL')),	
			gettext('URL to Instagram')		=> array('key' => 'instagram_url', 'type' => OPTION_TYPE_TEXTBOX, 'desc' => gettext('Provide your <a href="http://instagram.com/">Instagram</a>')),											
			gettext('URL to Pinterest')		=> array('key' => 'pinterest_url', 'type' => OPTION_TYPE_TEXTBOX, 'desc' => gettext('Provide your <a href="http://pinterest.com/">Pinterest</a> board or page')),											
			gettext('URL to Deviantart')		=> array('key' => 'deviantart_url', 'type' => OPTION_TYPE_TEXTBOX, 'desc' => gettext('Provide your <a href="http://deviantart.com/">Deviantart</a> page')),
			gettext('URL to Tumblr')		=> array('key' => 'tumblr_url', 'type' => OPTION_TYPE_TEXTBOX, 'desc' => gettext('Provide your <a href="http://tumblr.com/">Tumblr</a> page URL'))
		);			

	}
	function getOptionsDisabled() {
		return array('custom_index_page', 'paradigm_zp_index_news', 'paradigm_homepage');
	}
	function handleOption($option, $currentValue) {
	}
}
?>