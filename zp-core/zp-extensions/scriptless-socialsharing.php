<?php
/**
 * A Zenphoto plugin that provides scriptless and privacy friendly sharing buttons for:
 * 
 * - Facebook
 * - X (formerly Twitter)
 * - Pinterest 
 * - Linkedin
 * - Xing
 * - Reddit
 * - Stumbleupon
 * - Tumblr
 * - WhatsApp (iOS only)
 * - Digg
 * - Livejournal 
 * - Buffer
 * - Delicious
 * - Evernote
 * - WordPress(.com)
 * - Pocket
 * - e-mail (static link to open the visitor's mail client)
 * 
 * Note: Since no scripts are involved no share counts!
 * 
 * To have it work correctly you should also enable the html_meta_tags plugin 
 * and check the Open Graph (og:) meta data elements in the plugin's options.
 *
 * The plugin loads default CSS styling using an icon font. If you wish to use theme based custom icons 
 * and CSS to avoid extra loading you can disable it.
 *
 * Icon font created using the icomoon app: http://icomoon.io/#icons-icomoon
 * Fonts used:
 * - Brankic 1979 (buffer/stack icon) http://brankic1979.com/icons/ (free for personal and commercial use)
 * - Entypo+ (evernote icon) http://www.entypo.com – CC BY-SA 4.0
 * - fontawesome (all other icons) http://fontawesome.io – SIL OFL 1.1 
 *
 * Usage:
 * Place <code><?php ScriptlessSocialSharing::printButtons(); ?></code> on your theme files where you wish the buttons to appear.
 *
 * @author Malte Müller (acrylian)
 * @license GPL v3 or later
 * @package zpcore\plugins\scriptlesssocialsharing
 */
$plugin_is_filter = 9 | THEME_PLUGIN;
$plugin_description = gettext('A Zenphoto plugin that provides scriptless and privacy friendly sharing buttons for Facebook, Twitter, Google+, Pinterest, Linkedin, Xing, Reddit, Stumbleupon, Tumblr, WhatsApp (iOS only) and e-mail. (Note: No share counts because of that!).');
$plugin_author = 'Malte Müller (acrylian)';
$plugin_category = gettext('Misc');
$option_interface = 'scriptlessSocialsharingOptions';
if (getOption('scriptless_socialsharing_iconfont')) {
	zp_register_filter('theme_head', 'scriptlessSocialsharing::CSS');
}

class scriptlessSocialsharingOptions {

	function __construct() {
		purgeOption('scriptless_socialsharing_gplus');
	}

	function getOptionsSupported() {
		$options = array(
				gettext('Social networks') => array(
						'key' => 'scriptless_socialsharing_socialnetworks',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'order' => 0,
						'checkboxes' => array(
								'Facebook' => 'scriptless_socialsharing_facebook',
								'X (Twitter)' => 'scriptless_socialsharing_twitter',
								'Pinterest' => 'scriptless_socialsharing_pinterest',
								'Linkedin' => 'scriptless_socialsharing_linkedin',
								'Xing' => 'scriptless_socialsharing_xing',
								'Reddit' => 'scriptless_socialsharing_reddit',
								'StumbleUpon' => 'scriptless_socialsharing_stumbleupon',
								'Tumblr' => 'scriptless_socialsharing_tumblr',
								'Whatsapp' => 'scriptless_socialsharing_whatsapp',
								'Digg' => 'scriptless_socialsharing_digg',
								'Livejournal' => 'scriptless_socialsharing_livejournal',
								'Buffer' => 'scriptless_socialsharing_buffer',
								'Delicious' => 'scriptless_socialsharing_delicious',
								'Evernote' => 'scriptless_socialsharing_evernote',
								'WordPress' => 'scriptless_socialsharing_wordpress',
								'Pocket' => 'scriptless_socialsharing_pocket',
								gettext('E-mail') => 'scriptless_socialsharing_email',
						),
						'desc' => gettext('Select the social networks you wish buttons to appear for. Note: WhatsApp iOS only.')),
				gettext('Icon font and default CSS') => array(
						'key' => 'scriptless_socialsharing_iconfont',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 1,
						'desc' => gettext("Uncheck to disable loading the included font and CSS and use your own theme based icon font and CSS.")),
				gettext('Icons only') => array(
						'key' => 'scriptless_socialsharing_iconsonly',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 1,
						'desc' => gettext("Check to hide the service name and only show icon buttons.")),
				gettext('Twitter user name') => array(
						'key' => 'scriptless_socialsharing_twittername',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext("Enter your Twitter name without @ here if you like to have it appended to tweets made."))
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
			<link rel="stylesheet" href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/scriptless-socialsharing/style.min.css" type="text/css">
		<?php
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
		$desc = '';
		$url = '';
		$buttons = array();
		$gallerytitle = html_encode(getBareGallerytitle());
		$imgsource = '';
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
		if (getOption('scriptless_socialsharing_facebook')) {
			$buttons[] = array(
					'class' => 'sharingicon-facebook-f',
					'title' => 'facebook',
					'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . $url . '&amp;quote=' . $title
			);
		}
		if (getOption('scriptless_socialsharing_twitter')) {
			$via = '';
			if (getOption('scriptless_socialsharing_twittername')) {
				$via = '&amp;via=' . html_encode(getOption('scriptless_socialsharing_twittername'));
			}
			$buttons[] = array(
					'class' => 'sharingicon-x',
					'title' => 'X (Twitter)',
					'url' => 'https://x.com/intent/tweet?text=' . $title . $via . '&amp;url=' . $url
			);
		}
		if (getOption('scriptless_socialsharing_pinterest')) {
			$buttons[] = array(
					'class' => 'sharingicon-pinterest-p',
					'title' => 'Pinterest',
					'url' => 'https://pinterest.com/pin/create/button/?url=' . $url . '&amp;description=' . $title . '&amp;media=' . $url
			);
		}
		if (getOption('scriptless_socialsharing_linkedin')) {
			$buttons[] = array(
					'class' => 'sharingicon-linkedin',
					'title' => 'Linkedin',
					'url' => 'https://www.linkedin.com/shareArticle?mini=true&amp;url=' . $url . '>&amp;title=' . $title . '&amp;source=' . $url
			);
		}
		if (getOption('scriptless_socialsharing_xing')) {
			$buttons[] = array(
					'class' => 'sharingicon-xing',
					'title' => 'Xing',
					'url' => 'https://www.xing-share.com/app/user?op=share;sc_p=xing-share;url=' . $url
			);
		}
		if (getOption('scriptless_socialsharing_reddit')) {
			$buttons[] = array(
					'class' => 'sharingicon-reddit-alien',
					'title' => 'Reddit',
					'url' => 'https://reddit.com/submit?url=' . $url . '/?socialshare&amp;title=' . $title
			);
		}

		if (getOption('scriptless_socialsharing_stumbleupon')) {
			$buttons[] = array(
					'class' => 'sharingicon-stumbleupon',
					'title' => 'StumbleUpon',
					'url' => 'https://www.stumbleupon.com/submit?url=' . $url . '&amp;title=' . $title
			);
		}
		if (getOption('scriptless_socialsharing_tumblr')) {
			$buttons[] = array(
					'class' => 'sharingicon-tumblr',
					'title' => 'Tumblr',
					'url' => 'https://www.tumblr.com/share/link?url=' . $url . '&amp;name=' . $title
			);
		}
		if (getOption('scriptless_socialsharing_whatsapp')) { // must be hidden initially!
			$buttons[] = array(
					'class' => 'sharingicon-whatsapp',
					'title' => 'Whatsapp',
					'url' => 'https://wa.me/?text=' . $url
			);
		}
		if (getOption('scriptless_socialsharing_digg')) {
			$buttons[] = array(
					'class' => 'sharingicon-digg',
					'title' => 'Digg',
					'url' => 'http://digg.com/submit?url=' . $url . '&amp;title=' . $title
			);
		}
		if (getOption('scriptless_socialsharing_livejournal')) {
			$buttons[] = array(
					'class' => 'sharingicon-pencil',
					'title' => 'Livejournal',
					'url' => 'https://www.livejournal.com/update.bml?url=' . $url . '&amp;subject=' . $title
			);
		}
		if (getOption('scriptless_socialsharing_buffer')) {
			$buttons[] = array(
					'class' => 'sharingicon-stack',
					'title' => 'Buffer',
					'url' => 'https://buffer.com/add?text=' . $url . '&amp;url=' . $url
			);
		}
		if (getOption('scriptless_socialsharing_delicious')) {
			$buttons[] = array(
					'class' => 'sharingicon-delicious',
					'title' => 'Delicious',
					'url' => 'https://delicious.com/save?v=5&amp;provider=' . $gallerytitle . '&amp;noui&amp;jump=close&amp;url=' . $url . '&amp;title=' . $title
			);
		}
		if (getOption('scriptless_socialsharing_evernote')) {
			$buttons[] = array(
					'class' => 'sharingicon-evernote',
					'title' => 'Evernote',
					'url' => 'https://www.evernote.com/clip.action?url=' . $url . '&amp;title=' . $title
			);
		}
		if (getOption('scriptless_socialsharing_wordpress')) {
			$buttons[] = array(
					'class' => 'sharingicon-wordpress',
					'title' => 'WordPress',
					'url' => 'https://wordpress.com/press-this.php?u=' . $url . '&amp;t=' . $title
			);
		}
		if (getOption('scriptless_socialsharing_pocket')) {
			$buttons[] = array(
					'class' => 'sharingicon-get-pocket',
					'title' => 'Pocket',
					'url' => 'https://getpocket.com/save?url=' . $url . '&amp;title=' . $title
			);
		}
		if (getOption('scriptless_socialsharing_email')) {
			$buttons[] = array(
					'class' => 'sharingicon-envelope-o',
					'title' => gettext('e-mail'),
					'url' => 'mailto:?subject=' . $title . '&amp;body=' . $url
			);
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
				foreach ($buttons as $button) {
					$li_class = '';
					if ($button['class'] == 'sharingicon-whatsapp') {
						$li_class = ' class="whatsappLink hidden"';
					}
					?>
					<li<?php echo $li_class; ?>>
						<a class="<?php echo $button['class']; ?>" href="<?php echo $button['url']; ?>" title="<?php echo $button['title']; ?>" target="_blank">
							<?php
							if (!$iconsonly) {
								echo $button['title'];
							}
							?>
						</a>
					</li>
					<?php  
				} ?>
			</ul>
		<?php
	}
}