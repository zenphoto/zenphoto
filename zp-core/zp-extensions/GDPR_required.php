<?php
/**
 * A plugin to require that visitors view and acknowledge a site policy page upon the first
 * visit to your site.
 *
 * The plugin requires that zenpage be implemented for the theme and that you have created a <i>zenpage page</i>
 * object which states the site usage policy.
 *
 * Usage:
 *
 * <ul>
 * 	<li>
 * 		Create a <em>zenpage page</em> that states your site usage policy. For guidelines visit
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
				$possibilities = array();
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
									'desc' => gettext('The zenpage plugin is reqired but not enabled.'))
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

	static function page() {
		global $_zp_current_admin_obj, $_GDPR_acknowledge_loaded;
		if (getOption('GDPR_acknowledge') && !($_zp_current_admin_obj && $_zp_current_admin_obj->getPolicyAck()) && zp_getCookie('policyACK') != getOption('GDPR_cookie')) {
			$page = newPage(getOption('GDPR_page'));
			$link = $page->getLink();
			$me = getRequestURI();
			if ($me == $link) {
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

	static function button() {
		global $_GDPR_acknowledge_loaded;
		if (true || $_GDPR_acknowledge_loaded) {
			$link = getGalleryIndexURL();
			?>
			<form action="<?php echo $link; ?>" method = "post">
				<?php policySubmitButton(gettext('Continue to site')); ?>
			</form>
			<?php
		}
	}

}

if (extensionEnabled('zenpage')) {
	zp_register_filter('theme_head', 'GDPR_required::page');
}
?>