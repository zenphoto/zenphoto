<?php
/**
 * This is a shell plugin for legacy external SPAM filters.
 *
 * It assumes that there is only one SPAM filter in the <var>%USER_PLUGIN_FOLDER%/spamfilters</var> folder.
 * It will "load" that SPAM filter for use by the <var>comment_form</var> plugin.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage spam
 */

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext("Plugin to enable using legacy third party SPAM filters.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = (isset($_zp_spamFilter) && !getoption('zp_plugin_legacySpam'))?sprintf(gettext('Only one SPAM handler plugin may be enalbed. <a href="#%1$s"><code>%1$s</code></a> is already enabled.'),$_zp_spamFilter->name):'';
$plugin_notice = gettext('This plugin is to enable using older third party spam filters. Please contact the author of your SPAM filter and request it be updated to work with the <i>plugin</i> model of SPAM filters. This plugin will be removed from future Zenphoto releases.');

if ($plugin_disable) {
	setOption('zp_plugin_legacySpam', 0);
} else {
	$filters = getPluginFiles('*.php','spamfilters');
	if (!empty($filters)) {
		foreach ($filters as $name=>$path) {

			require_once($path);

			class zpLegacySpam extends SpamFilter {

				var $name = 'legacySpam';
				var $actingFor = NULL;

				function __construct($for) {
					$this->actingFor = $for;
					parent::__construct();
				}

				function displayName() {
					return $this->actingFor;
				}

			}

			$_zp_spamFilter = new zpLegacySpam($name);
			break;
		}

	}
}

?>
