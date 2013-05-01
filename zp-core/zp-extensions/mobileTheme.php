<?php
/**
 *
 * Mobile devices are detected with
 * {@link http://code.google.com/p/php-mobile-detect/ php-mobile-detect}
 * A particular theme may be designated for <i>phones</i> and for <i>tablets</i>. If the connecting
 * device is one of those, the theme will automatically switch to the designated mobile theme.
 *
 * Test mode allows you to run your standard desktop client but simulate being either a <i>phone</i> or
 * a <i>tablet</i>.
 *
 * You may place a call on <var>mobileTheme::controlLink();</var> in your theme(s) to allow the client viewer
 * to override the switch and view your standard gallery theme. If the same call is placed in your gallery
 * theme he will be able to switch back as well. <b>NOTE:</b> This link is present only when the browsing client
 * is a mobile device!
 *
 * Class <var>mobile</var> methods you can use in your theme:
 * <ul>
 * 	<li>phone is connected.
 * 	<ul>
 * 		<li>isiPhone()</li>
 * 		<li>isBlackBerry()</li>
 * 		<li>isHTC()</li>
 * 		<li>isNexus()</li>
 * 		<li>isDellStreak()</li>
 * 		<li>isMotorola()</li>
 * 		<li>isSamsung()</li>
 * 		<li>isSony()</li>
 * 		<li>isAsus()</li>
 * 		<li>isPalm()</li>
 * 	</ul>
 * </li>
 *
 * 	<li>tablet is connected
 * 	<ul>
 * 		<li>isBlackBerryTablet()</li>
 * 		<li>isiPad()</li>
 * 		<li>isKindle()</li>
 * 		<li>isSamsungTablet()</li>
 * 		<li>isMotorolaTablet()</li>
 * 		<li>isAsusTablet()</li>
 * 	</ul>
 * </li>
 *
 * 	<li>OS on the device
 * 	<ul>
 * 		<li>isAndroidOS()</li>
 * 		<li>isBlackBerryOS()</li>
 * 		<li>isPalmOS()</li>
 * 		<li>isSymbianOS()</li>
 * 		<li>isWindowsMobileOS()</li>
 * 		<li>isiOS()</li>
 * 	</ul>
 * </li>
 *
 * 	<li> user agent (browser) on the device
 * 	<ul>
 * 		<li>isChrome()</li>
 * 		<li>isDolfin()</li>
 * 		<li>isOpera()</li>
 * 		<li>isSkyfire()</li>
 * 		<li>isIE()</li>
 * 		<li>isFirefox()</li>
 * 		<li>isBolt()</li>
 * 		<li>isTeaShark()</li>
 * 		<li>isBlazer()</li>
 * 		<li>isSafari()
 * 		<li>isMidori()</li>
 * 	</ul>
 * </li>
 * </ul>
 *
 * @package plugins
 */

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext('Select your theme based on the device connecting to your site');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'mobileTheme';

class mobileTheme {

	function __construct() {
	}

	function getOptionsSupported() {
		global $_zp_gallery;
		$themes = array();
		foreach ($_zp_gallery->getThemes() as $theme=>$details) {
			$themes[$details['name']] = $theme;
		}
		$options = array(gettext('Phone theme') => array('key' => 'mobileTheme_phone', 'type' => OPTION_TYPE_SELECTOR,
																				'selections'=>$themes,
																				'null_selection' => gettext('gallery theme'),
																				'desc' => gettext('Select the theme to be used when a phone device connects.')),
															gettext('Tablet theme') => array('key' => 'mobileTheme_tablet', 'type' => OPTION_TYPE_SELECTOR,
																				'selections'=>$themes,
																				'null_selection' => gettext('gallery theme'),
																				'desc' => gettext('Select the theme to be used when a tablet device connects.')),
															gettext('Test mode') => array('key' => 'mobileTheme_test', 'type' => OPTION_TYPE_SELECTOR,
																				'selections'=>array(gettext('Phone')=>'phone',gettext('Tablet')=>'tablet'),
																				'null_selection' => gettext('live'),
																				'desc' => gettext('Put the plugin in <em>test mode</em> and it will simulate the selected device. If <em>live</em> is selected operations are normal.'))
		);
		return $options;
	}

	function handleOption($option, $currentValue) {
	}

	/**
	 *
	 * Filter to "setupTheme" that will override the gallery theme with the appropriate mobile theme
	 * @param string $theme
	 */
	static function theme($theme) {
		global $_zp_gallery;
		$detect = new mobile();
		if ($detect->isMobile()) {
			if ($detect->isTablet()) {
				$new = getOption('mobileTheme_tablet');
			} else {
				$new = getOption('mobileTheme_phone');
			}
		} else {
			$new = false;
		}
		if ($new) {
			if (array_key_exists($new, $_zp_gallery->getThemes())) {
				$theme = $new;
			}
		}
		return $theme;
	}

	/**
	 *
	 * places a link on the theme page to switch to or from the mobile theme
	 * @param string $text link text
	 */
	static function controlLink($text=NULL, $before=NULL, $after=Null) {
		$detect = new mobile();
		if ($detect->isMobile()) {
			if (zp_getCookie('mobileTheme_disable')) {
				if (is_null($text)) {
					$text = gettext('View the mobile gallery');
				}
				$enable = 'on';
			} else {
				if (is_null($text)) {
					$text = gettext('View the normal gallery');
				}
				$enable = 'off';
			}
			if ($before) {
				echo '<span class="beforetext">'.html_encode($before).'</span>';
			}
			?>
			<span class="mobileThemeControlLink">
				<a href="?mobileTheme=<?php echo $enable; ?>" rel="external">
					<?php echo html_encode($text); ?>
				</a>
			</span>
			<?php
			if ($after) {
				echo '<span class="aftertext">'.html_encode($after).'</span>';
			}
		}
	}

}

require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mobileTheme/Mobile_Detect.php');

class mobile extends Mobile_Detect {

	function __construct() {
		parent::__construct();
	}

	/**
	 * (non-PHPdoc)
	 * @see Mobile_Detect::isMobile()
	 */
	function isMobile($userAgent = NULL, $httpHeaders = NULL) {
		if (getOption('mobileTheme_test')) {
			return true;
		}
		return parent::isMobile();
	}

	/**
	 * (non-PHPdoc)
	 * @see Mobile_Detect::isTablet()
	 */
	function isTablet($userAgent = NULL, $httpHeaders = NULL) {
		if (getOption('mobileTheme_test')=='tablet') {
			return true;
		}
		return parent::isTablet();
	}

}

if (isset($_GET['mobileTheme'])) {
	switch ($_GET['mobileTheme']) {
		case 'on':
			zp_setCookie('mobileTheme_disable', 0);
			break;
		case 'off':
			zp_setCookie('mobileTheme_disable', 1);
			break;
	}
}

if (!zp_getCookie('mobileTheme_disable')) {
	zp_register_filter('setupTheme', 'mobileTheme::theme');
}

?>