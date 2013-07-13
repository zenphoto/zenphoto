<?php
/**
 * This is a shell plugin for legacy external SPAM filters.
 *
 * The plugin will load the spam filter defined by the <i>spam_filter</i> option if it is found in the
 * <var>%USER_PLUGIN_FOLDER%/spamfilter</var> folder. This allows older third-party spam filters to continue to
 * be used.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage spam
 */

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext("Use this plugin is to enable using older third party spam filters.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (isset($_zp_spamFilter) && !extensionEnabled('legacySpam'))?sprintf(gettext('Only one SPAM handler plugin may be enabled. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'),$_zp_spamFilter->name):'';
$plugin_notice = gettext('Please contact the author of your SPAM filter and request it be updated to work with the <i>plugin</i> model of SPAM filters. This plugin will be removed from future Zenphoto releases.');

$option_interface = 'zpLegacySpam';

$filters = getPluginFiles('*.php','spamfilters');
$actingfor = getOption('spam_filter');
foreach ($filters as $_legacyFilter=>$path) {
	if ($actingfor == $_legacyFilter) {
		require_once($path);
		break;
	}
}

if (class_exists('SpamFilter')) {

	class zpLegacySpam extends SpamFilter {

		var $name = 'legacySpam';
		var $actingFor = NULL;

		function __construct() {
			global $_legacyFilter;
			$this->actingFor = $_legacyFilter;
			parent::SpamFilter();
		}

		function displayName() {
			return 'zpLegacySpam::'.$this->actingFor;
		}

		function getOptionsSupported() {
			$list = array();
			foreach (getPluginFiles('*.php','spamfilters') as $filter=>$path) {
				$list[$filter] = $filter;
			}
			$options[gettext('Associated Spam Filter')] = array('key' => 'spam_filter', 'type' => OPTION_TYPE_SELECTOR,
					'selections' => $list,
					'order' => 0,
					'desc' => gettext('Select the legacy spam filter to be used.'));
			return array_merge($options,parent::getOptionsSupported());
		}

	}

} else {

	class zpLegacySpam {

		var $name = 'legacySpam';
		var $actingFor = NULL;

		function getOptionsSupported() {
			$list = array();
			foreach (getPluginFiles('*.php','spamfilters') as $filter=>$path) {
				$list[$filter] = $filter;
			}
			$options[gettext('Associated Spam Filter')] = array('key' => 'spam_filter', 'type' => OPTION_TYPE_SELECTOR,
					'selections' => $list,
					'order' => 0,
					'desc' => gettext('Select the legacy spam filter to be used.'));
			return $options;
		}

		function displayName() {
			return 'zpLegacySpam::<em>not found</em>';
		}

	}

}

if (isset($_legacyFilter) && !$plugin_disable) {
	$_zp_spamFilter = new zpLegacySpam();
}

?>
