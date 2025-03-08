<?php
/**
 * A Zenphoto plugin that provides scriptless and privacy friendly sharing buttons and profile buttons for various social networks
 * 
 * To have it work correctly you should also enable the html_meta_tags plugin 
 * and check the Open Graph (og:) meta data elements in the plugin's options.
 *
 * The plugin loads default CSS styling using an icon font. If you wish to use theme based custom icons 
 * and CSS to avoid extra loading you can disable it.
 *
 * Icon font created using the icomoon app: http://icomoon.io/#icons-icomoon
 * Fonts used:
 * - fontawesome
 *
 * Usage for sharing links:
 * Place <code><?php ScriptlessSocialSharing::printButtons(); ?></code> on your theme files where you wish the sharing buttons to appear.
 * 
 * Usage for profile links:
 * Place <code><?php ScriptlessSocialSharing::printProfileButtons(); ?></code> on your theme files where you wish the sharing buttons to appear.
 *
 * @author Malte Müller (acrylian)
 * @license GPL v3 or later
 * @package zpcore\plugins\scriptlesssocialsharing
 */
$plugin_is_filter = 9 | THEME_PLUGIN;
$plugin_description = gettext('A Zenphoto plugin that provides scriptless and privacy friendly sharing and social network profile buttons.');
$plugin_author = 'Malte Müller (acrylian)';
$plugin_category = gettext('Misc');
$option_interface = 'scriptlessSocialsharingOptions';
if (getOption('scriptless_socialsharing_iconfont')) {
	zp_register_filter('theme_head', 'scriptlessSocialsharing::CSS');
}

class scriptlessSocialsharingOptions {

	function __construct() {
		setOptionDefault('scriptless_socialsharing_profiles_alignment', 'center');
		setOptionDefault('scriptless_socialsharing_rssurlmode', 'custom'); // plugins may not be active
		purgeOption('scriptless_socialsharing_gplus');
		purgeOption('scriptless_socialsharing_livejournal');
		purgeOption('scriptless_socialsharing_delicious');
		purgeOption('scriptless_socialsharing_stumbleupon');
		renameOption('scriptless_socialsharing_twitter','scriptless_socialsharing_x');
	}

	function getOptionsSupported() {
		$networks_sharing = array_keys(scriptlessSocialsharing::getSupportedNetworks('sharinglinks'));
		$networks_profiles = scriptlessSocialsharing::getSupportedNetworks('profilelinks');
		$sharingoptions = $profileoptions = array();
		foreach ($networks_sharing as $network) {
			$sharingoptions[$network] = 'scriptless_socialsharing_' . $network;
		}
		foreach ($networks_profiles as $network => $data) {
			if ($network == 'rss') {
				$profileoptions[gettext('RSS custom URL')] = array(
						'key' => 'scriptless_socialsharing_profile-' . $network,
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('Enter the custom RSS URL and choose the custom mode below to use it.')
				);
			} else {
				$optionname = sprintf(gettext('%s profile URL'), $data['title']);
				$profileoptions[$optionname] = array(
						'key' => 'scriptless_socialsharing_profile-' . $network,
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => sprintf(gettext("Enter the URL to your profile on %s."), $data['title'])
				);
			}
		}

		$rssmode_options = array(
				gettext('Custom URL (URL entered above)') => 'custom'
		);
		if (extensionEnabled('rss')) {
			$rssmode_options[gettext('Gallery: Latest images')] = 'gallery_latestimages';
			$rssmode_options[gettext('Gallery: Latest albums')] = 'gallery_latestalbums';

			if (extensionEnabled('mergedrss')) {
				$rssmode_options[gettext('Merged RSS')] = 'mergedrss';
			}
			if (extensionEnabled('zenpage')) {
				$rssmode_options[gettext('News: latest articles')] = 'news';
			}
		}
		$options = array(
				gettext('Social networks') => array(
						'key' => 'scriptless_socialsharing_socialnetworks',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => $sharingoptions,
						'desc' => gettext('Select the social networks you wish sharing buttons to appear for.')),
				gettext('Icon font and default CSS') => array(
						'key' => 'scriptless_socialsharing_iconfont',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext("Uncheck to disable loading the included font and CSS and use your own theme based icon font and CSS.")),
				gettext('Icons only') => array(
						'key' => 'scriptless_socialsharing_iconsonly',
						'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext("Check to hide the service name and only show icon buttons.")),
				gettext('Twitter user name') => array(
						'key' => 'scriptless_socialsharing_twittername',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext("Enter your Twitter name without @ here if you like to have it appended to tweets made."))
		);
		$options['Divider'] = array(
				'key' => 'scriptless_socialsharing_profiles_note',
				'type' => OPTION_TYPE_NOTE,
				'desc' => gettext('Profile links')
		);
		
		$options = array_merge($options, $profileoptions);
		
		$options[gettext('RSS URL mode')] = array(
				'key' => 'scriptless_socialsharing_rssurlmode',
				'type' => OPTION_TYPE_RADIO,
				'buttons' => $rssmode_options,
				'desc' => gettext('Select the RSS mode to use. If the RSS and/or mergedRSS plugin are not enabled only the custom mode is available.'));
		
		$options[gettext('Profile links alignment')] = array(
				'key' => 'scriptless_socialsharing_profiles_alignment',
				'type' => OPTION_TYPE_RADIO,
				'buttons' => array(
						gettext('Left') => 'left',
						gettext('Center') => 'center',
						gettext('Right') => 'right'
				),
				'desc' => gettext('Select the alignment for te profile button alignment. The theme used may override this and may require an update to work properly.')
		);
		return $options;
	}
}

/**
 * Static class wrapper
 * 
 * @since 1.5
 */
class scriptlessSocialsharing {

	static function CSS() {
		?>
			<link rel="stylesheet" href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/scriptless-socialsharing/style.css" type="text/css">
		<?php
	}
	
	/**
	 * Returns an array for the icon and title names of a social network
	 * 
	 * @since 1.6.6
	 * 
	 * @param string $name Name of the social network
	 * @param string 'icon' or 'title', If null returns an array with both
	 * @return array
	 */
	static function getNetworks() {
		return array(
				'facebook' => array(
						'icon' => 'sharingicon-facebook-f',
						'title' => 'Facebook',
						'has_sharinglink' => true,
						'has_profilelink' => true
				),
				'x' => array(
						'icon' => 'sharingicon-x-twitter',
						'title' => 'X (Twitter)',
						'has_sharinglink' => true,
						'has_profilelink' => true
				),
				'instagram' => array(
						'icon' => 'sharingicon-instagram',
						'title' => 'Instragram',
						'has_sharinglink' => false,
						'has_profilelink' => true
				),
				'linkedin' => array(
						'icon' => 'sharingicon-linkedin-in',
						'title' => 'linkedin',
						'has_sharinglink' => true,
						'has_profilelink' => true
				),
				'youtube' => array(
						'icon' => 'sharingicon-youtube',
						'title' => 'Youtube',
						'has_sharinglink' => false,
						'has_profilelink' => true
				),
				'threads' => array(
						'icon' => 'sharingicon-threads',
						'title' => 'Threads',
						'has_sharinglink' => true,
						'has_profilelink' => true
				),
				'bluesky' => array(
						'icon' => 'sharingicon-bluesky',
						'title' => 'Bluesky',
						'has_sharinglink' => true,
						'has_profilelink' => true
				),
				'mastodon' => array(
						'icon' => 'sharingicon-mastodon',
						'title' => 'Mastodon',
						'has_sharinglink' => true,
						'has_profilelink' => true
				),
				'xing' => array(
						'icon' => 'sharingicon-xing',
						'title' => 'Xing',
						'has_sharinglink' => true,
						'has_profilelink' => true
				),
				
				'tiktok' => array(
						'icon' => 'sharingicon-tiktok',
						'title' => 'TikTok',
						'has_sharinglink' => false,
						'has_profilelink' => true
				),
				'pinterest' => array(
						'icon' => 'sharingicon-pinterest-p',
						'title' => 'Pinterest',
						'has_sharinglink' => true,
						'has_profilelink' => true
				),
				'flickr' => array(
						'icon' => 'sharingicon-flickr',
						'title' => 'Flickr',
						'has_sharinglink' => false,
						'has_profilelink' => true
				),
				'github' => array(
						'icon' => 'sharingicon-github',
						'title' => 'GitHub',
						'has_sharinglink' => false,
						'has_profilelink' => true
				),
				'buffer' => array(
						'icon' => 'sharingicon-buffer:before',
						'title' => 'Buffer',
						'has_sharinglink' => true,
						'has_profilelink' => false
				),
				'digg' => array(
						'icon' => 'sharingicon-digg',
						'title' => 'Digg',
						'has_sharinglink' => true,
						'has_profilelink' => false
				),
				'evernote' => array(
						'icon' => 'sharingicon-evernote',
						'title' => 'Evernote',
						'has_sharinglink' => true,
						'has_profilelink' => false
				),
				'pocket' => array(
						'icon' => 'sharingicon-get-pocket',
						'title' => 'Pocket',
						'has_sharinglink' => true,
						'has_profilelink' => false
				),
				'patreon' => array(
						'icon' => 'sharingicon-patreon',
						'title' => 'patreon',
						'has_sharinglink' => false,
						'has_profilelink' => true
				),
				'reddit' => array(
						'icon' => 'sharingicon-reddit',
						'title' => 'Reddit',
						'has_sharinglink' => true,
						'has_profilelink' => false
				),
				'soundcloud' => array(
						'icon' => 'sharingicon-soundcloud',
						'title' => 'soundcloud',
						'has_sharinglink' => false,
						'has_profilelink' => true
				),
				'tumblr' => array(
						'icon' => 'sharingicon-tumblr',
						'title' => 'Tumblr',
						'has_sharinglink' => true,
						'has_profilelink' => true
				),
				'vimeo' => array(
						'icon' => 'sharingicon-vimeo-v',
						'title' => 'Vimeo',
						'has_sharinglink' => false,
						'has_profilelink' => true
				),
				'whatsapp' => array(
						'icon' => 'sharingicon-whatsapp',
						'title' => 'Whatsapp',
						'has_sharinglink' => true,
						'has_profilelink' => false
				),
				'wordpress' => array(
						'icon' => 'sharingicon-wordpress',
						'title' => 'WordPress',
						'has_sharinglink' => true,
						'has_profilelink' => true
				),
				'email' => array(
						'icon' => 'sharingicon-envelope',
						'title' => gettext('E-mail'),
						'has_sharinglink' => true,
						'has_profilelink' => false
				),
				'rss' => array(
						'icon' => 'sharingicon-rss',
						'title' => 'RSS',
						'has_sharinglink' => false,
						'has_profilelink' => true
				),
		);
		
	}
	
	/**
	 * Returns an array of social media networks supported. Order roughly be importance (as of date of making)
	 * 
	 * @since 1.6.6
	 * 
	 * @param string $type 'sharinglinks' or "profilelinks'
	 * @return array
	 */
	static function getSupportedNetworks($type = 'sharinglinks') {
		$networks = self::getNetworks();
		$check = '';
		switch ($type) {
			case 'sharinglinks':
				$check = 'has_sharinglink';
				break;
			case 'profilelinks':
				$check = 'has_profilelink';
				break;
		}
		$supported = array();
		if ($check) {
			foreach ($networks as $network => $data) {
				if ($data[$check]) {
					$supported[$network] = $data;
				}
			}
		}
		return $supported;
	}
	
	/**
	 * Gets the scriptless sharing URL to a social network
	 * 
	 * @since 1.6.6
	 * 
	 * @param string $network Network to get the sharing URL for if available
	 * @param string $url URL for sharing
	 * @param string $title Title for sharing
	 * @return string
	 */
	static function getSharingURL($network = '', $url = '', $title = '') {
		switch ($network) {
			case 'facebook':
				return 'https://www.facebook.com/sharer/sharer.php?u=' . $url . '&amp;quote=' . $title;
			case 'x':
				$via = '';
				if (getOption('scriptless_socialsharing_twittername')) {
					$via = '&amp;via=' . html_encode(getOption('scriptless_socialsharing_twittername'));
				}
				return 'https://x.com/intent/tweet?text=' . $title . $via . '&amp;url=' . $url;
			case 'bluesky':
				return 'https://bsky.app/intent/compose?text=' . $title . ' ' . $url;
			case 'threads':
				return 'https://threads.net/intent/post?text=' . $title . ' ' . $url;
			case 'mastodon':
				return 'https://mastodon.social/share?text=' . $title . ' ' . $url; // note needs processing because of no fixed instance!
			case 'pinterest':
				return 'https://pinterest.com/pin/create/button/?url=' . $url . '&amp;description=' . $title . '&amp;media=' . $url;
			case 'linkedin':
				return 'https://www.linkedin.com/shareArticle?mini=true&amp;url=' . $url . '>&amp;title=' . $title . '&amp;source=' . $url;
			case 'xing':
				return 'https://www.xing-share.com/app/user?op=share;sc_p=xing-share;url=' . $url;
			case 'reddit':
				return 'https://reddit.com/submit?url=' . $url . '/?socialshare&amp;title=' . $title;
			case 'tumblr':
				return 'https://www.tumblr.com/share/link?url=' . $url . '&amp;name=' . $title;
			case 'whatsapp':
				return 'https://wa.me/?text=' . $url;
			case 'digg':
				return 'http://digg.com/submit?url=' . $url . '&amp;title=' . $title;
			case 'buffer':
				return 'https://buffer.com/add?text=' . $url . '&amp;url=' . $url;
			case 'evernote':
				return 'https://www.evernote.com/clip.action?url=' . $url . '&amp;title=' . $title;
			case 'wordpress':
				return 'https://wordpress.com/press-this.php?u=' . $url . '&amp;t=' . $title;
			case 'pocket':
				return 'https://getpocket.com/save?url=' . $url . '&amp;title=' . $title;
			case 'email':
				return 'mailto:?subject=' . $title . '&amp;body=' . $url;
		}
	}

	/**
	 * Gets an array with the buttons information
	 *  
	 * @param string $beforetext Text to be displayed before the sharing list. HTML code allowed. Default empty
	 * @param string $customtext Custom text to share to override the internalt share text generation via current page
	 * @return array
	 */
	static function getButtons($beforetext = '', $customtext = null) {
		global $_zp_gallery_page, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news, $_zp_current_zenpage_page, $_zp_current_category;
		$title = '';
		$url = '';
		$buttons = array();
		switch ($_zp_gallery_page) {
			case 'index.php':
			case 'gallery.php':
				$url = getGalleryIndexURL();
				$title = (empty($customtext)) ? getBareGalleryTitle() : $customtext;
				break;
			case 'album.php':
				$url = $_zp_current_album->getLink();
				$title = (empty($customtext)) ? $_zp_current_album->getTitle() : $customtext;
				break;
			case 'image.php':
				$url = $_zp_current_image->getLink();
				$title = (empty($customtext)) ? $_zp_current_image->getTitle() : $customtext;
				break;
			case 'news.php':
				if (function_exists("is_NewsArticle")) {
					if (is_NewsArticle()) {
						$url = $_zp_current_zenpage_news->getLink();
						$title = (empty($customtext)) ? $_zp_current_zenpage_news->getTitle() : $customtext;
					} else if (is_NewsCategory()) {
						$url = $_zp_current_category->getLink();
						$title = (empty($customtext)) ? $_zp_current_category->getTitle() : $customtext;
					} else {
						$url = getNewsIndexURL();
						$title = (empty($customtext)) ? getBareGalleryTitle() . ' - ' . gettext('News') : $customtext;
					}
				}
				break;
			case 'pages.php':
				if (function_exists("is_Pages")) {
					$url = $_zp_current_zenpage_page->getLink();
					$title = (empty($customtext)) ? $_zp_current_zenpage_page->getTitle() : $customtext;
				}
				break;
			default: //static custom pages
				$url = getCustomPageURL(stripSuffix($_zp_gallery_page));
				if (empty($customtext)) {
					// Handle some static custom pages we often have
					switch ($_zp_gallery_page) {
						case 'contact.php':
							$title = gettext('Contact');
							break;
						case 'archive.php':
							$title = gettext('Archive');
							break;
						case 'register.php':
							$title = gettext('Register');
							break;
						case 'search.php':
							$title = gettext('Search');
							break;
						default:
							$title = strtoupper(stripSuffix($_zp_gallery_page));
							break;
					} 
				} else {
					$title = $customtext;
				}
				break;
		}
		//override pagetitle with custom text
		if (empty($customtext)) {
			$title .= ' - ' . getBareGalleryTitle();
		}
		//$text = getContentShorten($title, 100, ' (…)', false);
		$title = urlencode($title);
		$url = urlencode(SERVER_HTTP_HOST . html_encode($url));
		if ($beforetext) {
			echo $beforetext;
		}
		$supportednetworks = self::getSupportedNetworks('sharinglinks');
		foreach($supportednetworks as $network => $data) {
			if (getOption('scriptless_socialsharing_' . $network)) {
				$buttons[$network] = array(
					'class' => $data['icon'],
					'title' => $data['title'],
					'url' => self::getSharingURL($network, $url, $title)
			);
			}
		}
		return $buttons;
	}
	
	

	/**
	 * Place this where you wish the buttons to appear. The plugin includes also jQUery calls to set the buttons up to allow multiple button sets per page.
	 *  
	 * @param string $text Text to be displayed before the sharing list. HTML code allowed. Default empty
	 * @param string $customtext Custom text to share to override the internalt share text generation via current page
*/
	static function printButtons($text = '', $customtext = null, $iconsonly = null) {
		$buttons = self::getButtons($text, '', $customtext);
		if (is_null($iconsonly)) {
			$iconsonly = getOption('scriptless_socialsharing_iconsonly');
		}
		?>
			<ul class="scriptless_socialsharing">
				<?php
				foreach ($buttons as $network => $button) {
					$li_class = '';
					?>
					<li<?php echo $li_class; ?>>
						<a class="<?php echo $button['class']; ?>" href="<?php echo $button['url']; ?>" title="<?php echo $button['title']; ?>" target="_blank" rel="noopener noreferrer">
							<?php
							if (!$iconsonly) {
								echo $button['title'];
							}
							if ($network == 'mastodon') {
								?>
								<script>
									// Source: https://christianheilmann.com/2023/08/18/adding-a-share-to-mastodon-link-to-any-web-site-and-here/
									// Grab link from the DOM
									const button = document.querySelector('.<?php echo $button['class']; ?>');
									let key = 'mastodon-instance';
									let prompt = '<?php echo gettext('Please enter your Mastodon instance first, e.g mastodon.social.'); ?>';

									button.addEventListener('click', (e) => {
										if(localStorage.getItem(key)) {
											button.href = button.href.replace(
													"mastodon.social", 
													localStorage.getItem(key)
											);
										} else {
											e.preventDefault();
											let instance = window.prompt(prompt);
											localStorage.setItem(key, instance);
											button.href = button.href.replace(
													"mastodon.social", 
													localStorage.getItem(key)
											);
											window.location.href = button.href;
										}
									});
								</script>
								<?php
							}
							?>
						</a>
					</li>
					<?php  
				} ?>
			</ul>
		<?php
	}

	/**
	 * Gets the profile buttons data
	 * 
	 * @since 1.6.6
	 * 
	 * @return array
	 */
	static function getProfileButtons() {
		$supportedprofiles = self::getSupportedNetworks('profilelinks');
		$buttons = array();
		foreach ($supportedprofiles as $network => $data) {
			if ($network == 'rss') {
				$url = self::getRSSURL();
				if ($url) {
					$buttons[] = array(
							'class' => $data['icon'],
							'title' => gettext('Subscribe to the RSS feed'),
							'url' => $url
					);
				}
			} else {
				if (getOption('scriptless_socialsharing_profile-' . $network)) {
					$buttons[] = array(
							'class' => $data['icon'],
							'title' => sprintf(gettext('Follow on %s'), $data['title']),
							'url' => getOption('scriptless_socialsharing_profile-' . $network)
					);
				}
			}
		}
		return $buttons;
	}

	/**
	 * Prints the profile buttons
	 * 
	 * @param string $before Enter text to print before the buttons like "Follow us", HTML allowed
	 * 
	 * @since 1.6.6
	 */
	static function printProfileButtons($before = '') {
		$buttons = self::getProfileButtons();
		if ($buttons) {
			$alignment = getOption('scriptless_socialsharing_profiles_alignment');
			$aligmentclass = '';
			switch ($alignment) {
				case 'left':
					$aligmentclass = ' scriptless_socialsharing-profiles-alignleft';
					break;
				case 'right':
					$aligmentclass = ' scriptless_socialsharing-profiles-alignright';
					break;
			}
			?>
			<div class="scriptless_socialsharing-profiles<?php echo $aligmentclass; ?>">
				<div class="scriptless_socialsharing-profiles-before"><?php echo $before; ?></div>
				<ul class="scriptless_socialsharing-profileslist">
				<?php
				foreach ($buttons as $network => $button) {
					?>
					<li class="<?php echo $network; ?>">
						<a class="<?php echo $button['class']; ?>" href="<?php echo html_encode($button['url']); ?>" title="<?php echo $button['title']; ?>" target="_blank" rel="noopener noreferrer"></a>
					</li>
					<?php
				}
				?>
				</ul>
			</div>
			<?php
		}
	}
	
	/**
	 * Gets the RSS URL
	 * 
	 * @since 1.6.6
	 * 
	 * @return string
	 */
	static function getRSSURL() {
		$rssmode = getOption('scriptless_socialsharing_rssurlmode');
		$url = '';
		switch ($rssmode) {
			case 'news':
				if (extensionEnabled('zenpage')) {
					$url = getRSSLink('news', null, null);
				}
				break;
			case 'gallery_latestimages':
			case 'gallery_latestalbums':
				if (extensionEnabled('rss')) {
					switch ($rssmode) {
						case 'gallery_latestimages':
							$url = getRSSLink('gallery', null, null);
							break;
						case 'gallery_latestalbums':
							$url = getRSSLink('albumsrss', null, null);
							break;
					}
				}
				break;
			case 'mergedrss':
				if (extensionEnabled('mergedrss')) {
					$url = FULLWEBPATH . '/index.php?mergedrss';
				}
				break;
			default:
			case 'custom':
				$url = getOption('scriptless_socialsharing_profile-rss');
				break;
		}
		return $url;
	}
}
