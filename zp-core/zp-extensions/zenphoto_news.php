<?php
/**
 * A backend plugin that displays the lastest news articles  from the RSS news feed from Zenphoto.org on Zenphoto's backend overview page.
 * An adaption of RSS Extractor and Displayer	(c) 2007-2009  Scriptol.com - License Mozilla 1.1. PHP 5 only.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Places the latest 3 news articles from Zenphoto.org on the admin overview page.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$plugin_version = '1.4.1';
$plugin_disable = (version_compare(PHP_VERSION, '5.0.0') != 1 || !class_exists('DOMDocument')) ? gettext('PHP version 5 or greater with the <em>DOM Object Model</em> is required.') : false;
if (OFFSET_PATH && !$plugin_disable) {
	zp_register_filter('admin_overview', 'printNews',0);
}

function printNews($side) {
	$pos = zp_filter_slot('admin_overview', 'comment_form_print10Most') !== false;
	if (($pos && ($side=='left')) || (!$pos && ($side=='right'))) {
		if ($connected = is_connected()) {
			require_once(dirname(__FILE__).'/zenphoto_news/rsslib.php');
		}
		?>
		<div class="box" id="overview-news">
		<h2 class="h2_bordered"><?php echo gettext("News from Zenphoto.org"); ?></h2>
		<?php
		if ($connected) {
			echo RSS_Display("http://www.zenphoto.org/index.php?rss-news&withimages", 5);
		} else {
			?>
			<ul>
				<li><?php echo gettext('A connection to <em>Zenphoto.org</em> could not be established.'); ?>
				</li>
			</ul>
			<?php
		}
		?>
		</div>
		<?php
	}
	return $side;
}
?>