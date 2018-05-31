<?php
/**
 * A plugin to require that visitors view and acknowledge a site policy page upon the first
 * visit to your site.
 *
 * The plugin requires a <i>zenpage page</i> object or a custom URL which states the site usage policy.
 * This URL will also become the <i>Policy URL</i> used for <i>Policy Submit</i> buttons. But of course that will
 * be moot since the visitor will have had to acknowledge your policy before
 * any content pages are shown.
 *
 * Usage:
 *
 * <ul>
 * 	<li>
 * 		Create a <em>zenpage page</em> or a custom theme script that states your site usage policy. For guidelines visit
 * 		{@link https://www.itgovernance.co.uk/blog/how-to-write-a-gdpr-privacy-notice-with-documentation-template-example/* How to write a GDPR privacy notice}.
 * </li>
 * 	<li>
 * 		Place the following text in one of the <i>codeblocks</i> for the page:
 * 		<br />
 * 		<code><?php GDPR_required::button();?></code>
 * 		<br />
 * 		(This will place the policy button on the page.)
 * 		For most themes, <i>codeblock&nbsp;1</i> will put the button just below your privacy statement.
 * 		But this is theme dependent. Choose the <i>codeblock</i> which best locates the button.
 * 		(For Effervescence and Garland use <i>codeblock&nbsp;2</i>.)
 *
 * 		<strong>Note</strong>: If you are using a custom page you will need to place this function call somewhere appropriate in
 * 		your script.
 * 	</li>
 * 	<li>
 * 		Enable the <i>Usage policy</i> option on the general options page.
 * 	</li>
 *
 * </ul>
 *
 * Now when a visitor visits your site for the first time the site will redirect him to
 * your policy page. When he checks the acknowledgement box a button will appear to
 * direct him to your site index.
 *
 * @author Stephen Billard (sbillard)
 * @Copyright 2016 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 * @package plugins/GDPR_required
 * @pluginCategory theme
 */
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/zenpage.php');

$plugin_is_filter = 980 | FEATURE_PLUGIN;
$plugin_description = gettext('Inject a site policy acknowledgement page.');
$plugin_author = "Stephen Billard (sbillard)";
$option_interface = 'GDPR_required';

class GDPR_required {

	function getOptionsSupported() {
		global $_zp_CMS;
		if (extensionEnabled('zenpage')) {
			if (file_exists(SERVERPATH . '/' . THEMEFOLDER . '/' . internalToFilesystem(getCurrentTheme()) . '/pages.php')) {
				$possibilities = array('*' . gettext('Custom url') . '*' => '');
				foreach ($_zp_CMS->getPages(false) as $page) {
					$possibilities[get_language_string($page['title'])] = $page['titlelink'];
				}
				if (empty($possibilities)) {
					$options = array(
							NULL => array('key' => 'GDPR_page', 'type' => OPTION_TYPE_NOTE,
									'desc' => gettext('There are no pages to select from.'))
					);
				} else {
					$options = array(
							gettext('Policy page') => array('key' => 'GDPR_page', 'type' => OPTION_TYPE_SELECTOR,
									'selections' => $possibilities,
									'order' => 1,
									'desc' => gettext('The <em>zenpage page</em> object to use as the policy page.')),
							gettext('Policy page URL') => array('key' => 'GDPR_URL', 'type' => OPTION_TYPE_CUSTOM,
									'order' => 2,
									'desc' => gettext('The URL to the site policy page. This will be the link to the <em>Policy page</em> object if a <em>zenpage page</em> is selected.'))
					);
				}
			} else {
				$options = array(
						gettext('Policy page') => array('key' => 'GDPR_page', 'type' => OPTION_TYPE_NOTE,
								'desc' => gettext('The active theme has no <em>pages.php</em> script.'))
				);
			}
		} else {
			$options = array(
					NULL => array('key' => 'GDPR_page', 'type' => OPTION_TYPE_NOTE,
							'desc' => gettext('The zenpage plugin is reqired but not enabled.'))
			);
		}
		return $options;
	}

	function handleOption($option, $currentvalue) {
		?>
		<input type="text" name="GDPR_URL" size="35" value="<?php echo getOption('GDPR_URL'); ?>" />
		<?php
	}

	static function handleOptionSave($themename, $themealbum) {
		$page = getOption('GDPR_page');
		if ($page) {
			$pageobj = newPage($page);
			$link = $pageobj->getLink();
		} else {
			$link = sanitize($_POST['GDPR_URL']);
		}
		setOption('GDPR_URL', $link);
		return false;
	}

	/*
	 * Tests if the site policy has been acknowledged and if not, redirects to the
	 * policy page.
	 */

	static function page() {
		global $_zp_current_admin_obj, $_GDPR_acknowledge_loaded;
		if (!($_zp_current_admin_obj && $_zp_current_admin_obj->getPolicyAck()) && zp_getCookie('policyACK') != getOption('GDPR_cookie')) {
			if ($link = getOption('GDPR_URL')) {
				if (getRequestURI() == $link) {
					$_GDPR_acknowledge_loaded = true;
				} else {
					//	redirect to the policy page
					header("HTTP/1.0 307 Found");
					header("Status: 307 Found");
					header('Location: ' . $link);
					exitZP();
				}
			}
		}
	}

	/**
	 * Displays the policySubmitButton on the policy page.
	 *
	 * Note: the button will NOT be present if the visitor has already acknowledged
	 * the policy, e.g. he visited the page from a normal link after his acknowledgement
	 * was recorded.
	 *
	 * @global type $_GDPR_acknowledge_loaded
	 */
	static function button() {
		global $_GDPR_acknowledge_loaded;
		if ($_GDPR_acknowledge_loaded) {
			setOption('GDPR_text', gettext('Check to acknowledge the site usage policy.'), false);
			setOption('GDPR_acknowledge', 1, false);
			$link = getGalleryIndexURL();
			?>
			<form action="<?php echo $link; ?>" method = "post">
				<?php policySubmitButton(gettext('Continue to site')); ?>
			</form>
			<?php
		}
	}

}

zp_register_filter('load_theme_script', 'GDPR_required::page');
