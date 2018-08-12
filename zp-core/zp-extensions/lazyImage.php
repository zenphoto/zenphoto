<?php
/*
 * Applies lazy loading to image content.
 * Uses {@link https://github.com/ressio/lazy-load-xt#usage Lazy load XT}
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins/lazyImage
 * @pluginCategory media
 *
 * @Copyright 2017 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20 and derivatives}
 */

$plugin_is_filter = 9 | THEME_PLUGIN;
$plugin_description = gettext('A plugin to turn <em>img src</em> links into lazy loading images.');

$option_interface = 'lazyImage';

zp_register_filter('theme_head', 'lazyImage::head');
// Note: these are not exact. If some other plugin decides to insert before or after, it's output
// will not get processed.
zp_register_filter('theme_body_open', 'lazyImage::start', 99999);
zp_register_filter('theme_body_close', 'lazyImage::end', -99999);

class lazyImage {

	function getOptionsSupported() {
		return array(
				gettext('Bootstrap support') => array('key' => 'lazyImage_Bootstrap', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Load support for the Bootstrap\'s Carousel.')),
				gettext('jqueryMobile support') => array('key' => 'lazyImage_jqMobile', 'type' => OPTION_TYPE_CHECKBOX,
						'desc' => gettext('Load support for the jQueryMobile\'s Panel.'))
		);
	}

	static function start() {
		ob_start();
	}

	static function end() {
		$data = ob_get_contents();
		ob_end_clean();
		preg_match_all('~<img\s+[^>]*src="([^"]*)"[^>]*>~is', $data, $matches);
		foreach ($matches[0] as $imgtag) {
			$data = str_replace($imgtag, str_replace('src=', 'class="lazy" data-src=', $imgtag) . '<noscript>' . $imgtag . '</noscript>', $data);
		}
		if (class_exists('Video')) {
			preg_match_all('~<video.*</video>~is', $data, $matches);
			foreach ($matches[0] as $imgtag) {
				$newtag = str_replace('<video', '<video class="lazy"', $imgtag);
				$newtag = str_replace('src=', 'data-src=', $newtag);
				$data = str_replace($imgtag, $newtag . '<noscript>' . $imgtag . '</noscript>', $data);
			}
		}

		echo $data;
	}

	static function head() {

		if (class_exists('Video')) {
			?>
			<script src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/lazyImage/jquery.lazyloadxt.extra.min.js" ></script>
			<?php
		} else {
			?>
			<script src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/lazyImage/jquery.lazyloadxt.min.js"></script>
			<?php
		}
		?>
		<style>
			img.lazy {
				display: none;
			}
		</style>
		<?php
		if (getOption('lazyImage_jqBootstrap')) {
			?>
			<script src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/lazyImage/jquery.lazyloadxt.jquerymobile.min.js"></script>
			<?php
		}
		if (getOption('lazyImage_jqMobile')) {
			?>
			<script src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/lazyImage/jquery.lazyloadxt.bootstrap.min.js" ></script>
			<?php
		}
	}

}
