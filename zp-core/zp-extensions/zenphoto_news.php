<?php
/**
 * A backend plugin that displays the lastest news articles  from the RSS news feed from Zenphoto.org on Zenphoto's backend overview page.
 * An adaption of RSS Extractor and Displayer	(c) 2007-2009  Scriptol.com - License Mozilla 1.1.
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenphotonews
 */
$plugin_is_filter = 7 | ADMIN_PLUGIN;
$plugin_description = gettext("Places the latest 5 news articles from Zenphoto.org on the admin overview page.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard)";
$plugin_disable = (!class_exists('DOMDocument')) ? gettext('PHP <em>DOM Object Model</em> is required.') : false;
$plugin_notice = gettext('Privacy note: This plugin performs external checks for new releases using the RSS feed from zenphoto.org. No data from your install is submitted or collected.');
$plugin_category = gettext('Admin');
$option_interface = 'zenphoto_org_news';

zp_register_filter('admin_overview', 'printNews');

class zenphoto_org_news {

	function __construct() {
		setOptionDefault('zenphoto_news_length', 0);
	}

	function getOptionsSupported() {
		return array(
				gettext('Truncation') => array(
						'key' => 'zenphoto_news_length',
						'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('The length of the article to display.'))
		);
	}
}

function printNews() {
	?>
	<div class="box overview-utility">
		<h2 class="h2_bordered"><?php echo gettext("News from Zenphoto.org"); ?></h2>
		<?php
		if (is_connected()) {
			require_once(SERVERPATH . '/' . ZENFOLDER . '/libs/class-rsslib.php');
			require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');
			$recents = rsslib::retrieve("https://www.zenphoto.org/index.php?rss=news&withimages");
			if ($recents) {
				$opened = false;
				$recents = array_slice($recents, 1, 5);
				$shorten = getOption('zenphoto_news_length');
				foreach ($recents as $article) {
					$type = $article["type"];
					if ($type == 0) {
						if ($opened) {
							?>
						</ul>
						<?php
						$opened = false;
					}
					?>
					<b />
					<?php
				} else {
					if (!$opened) {
						?>
						<ul>
							<?php
							$opened = true;
						}
					}
					$title = $article["title"];
					$date = zpFormattedDate(DATETIME_DISPLAYFORMAT, strtotime($article["pubDate"]));
					$link = $article["link"];
					if ($shorten) {
						$description = shortenContent($article["description"], $shorten, '...');
					} else {
						$description = false;
					}
					?>
					<li><a href="<?php echo $link; ?>" target="_blank" rel="noopener noreferrer"><strong><?php echo $title; ?></strong> (<?php echo $date; ?>)</a>
						<?php
						if ($description != false) {
							?>
							<br />
							<?php
							echo $description;
						}
						?>
					</li>
					<?php
					if ($type == 0) {
						?>
						<br />
						<?php
					}
				}
				if ($opened) {
					?>
				</ul>
				<?php
			}
		} else {
			?>
			<ul>
				<li><?php printf(gettext('Failed to retrieve link <em>%s</em>'), 'https://www.zenphoto.org/index.php?rss=news&withimages'); ?></li>
			</ul>
			<?php
		}
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
?>