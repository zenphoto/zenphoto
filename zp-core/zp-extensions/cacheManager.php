<?php
/**
 *
 * This plugin is the centralized Cache manager for Zenphoto. It provides:
 * <ul>
 * 		<li>Options to purge the HTML and RSS caches on publish state changes of:
 * 			<ul>
 * 				<li>albums</li>
 * 				<li>images</li>
 * 				<li>news articles</li>
 * 				<li>pages</li>
 * 			</ul>
 * 		</li>
 * 		<li><i>pre-creating</i> the Image cache images</li>
 * 		<li>utilities for purging Image, HTML, and RSS caches</li>
 * </ul>
 *
 * Image cache <i>pre-creating</i> will examine the gallery and make image references to any images which have not
 * already been cached. Your browser will then request these images causing the caching process to be
 * executed.
 *
 * The <i>default</i>, <i>effervescence+</i>,<i>garland</i>, <i>stopdesign</i>, and <i>zenpage</i> themes have created <i>Caching</i> size options
 * for the images sizes they use. The <i>stopdesign</i> theme creates some two sets of thumbnail sizes, one for landscape and one for protrait
 * "35mm slide" thumbnails. You may wish not to apply both (or either) of these sizes if you do not want the excess images. The caching
 * process does not consider the image orientation, it simply creates a cache image at the sizes specified.
 *
 *
 * <b>Notes:</b>
 * <ul>
 * 		<li>
 * 			Setting theme options or installing a new version of Zenphoto will re-create these caching sizes.
 * 			Use a different <i>theme name</i> for custom versions that you create.
 * 		</li>
 *
 * 		<li>
 * 			The <i>pre-creating</i> process will cause your browser to display each and every image that has not
 * 			been previously cached. If your server does not do a good job of thread management this may swamp
 * 			it! You should probably also clear your browser cache before using this utility. Otherwise
 * 			your browser may fetch the images locally rendering the above process useless.
 * 		</li>
 * 		<li>
 * 			You may have to refresh the page multiple times until the report of the number of images cached is zero.
 * 			If some images seem to never be rendered you may be experiencing memory limit or other graphics processor
 * 			errors. You can click on the image that does not render to get the <var>i.php</var> debug screen for the
 * 			image. This may help in figuring out what has gone wrong.
 * 		</li>
 * </ul>
 *
 *
 * @package plugins
 * @subpackage utilities
 * @author Stephen Billard (sbillard)
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Provides cache management utilities for Image, HTML, and RSS caches.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'cacheManager';


zp_register_filter('admin_utilities_buttons', 'cacheManager::overviewbutton');
zp_register_filter('edit_album_utilities', 'cacheManager::albumbutton', -9999);
zp_register_filter('show_change', 'cacheManager::published');

/**
 *
 * Standard options interface
 * @author Stephen
 *
 */
class cacheManager {

	function __construct() {
		self::deleteThemeCacheSizes('admin');
		self::addThemeCacheSize('admin', 40, NULL, NULL, 40, 40, NULL, NULL, -1);
		self::addThemeCacheSize('admin', 80, NULL, NULL, 80, 80, NULL, NULL, -1);
	}

	/**
	 *
	 * supported options
	 */
	function getOptionsSupported() {
		global $_zp_zenpage;
		$options = array(gettext('Image caching sizes') => array('key'		 => 'cropImage_list', 'type'	 => OPTION_TYPE_CUSTOM,
										'order'	 => 1,
										'desc'	 => '<p>' .
										gettext('Cropped images will be made in these parameters if the <em>Create image</em> box is checked. Un-check to box to remove the settings.' .
														'You can determine the values for these fields by examining your cached images. The file names will look something like these:') .
										'<ul>' .
										'<li>' . gettext('<code>photo_595.jpg</code>: sized to 595 pixels') . '</li>' .
										'<li>' . gettext('<code>photo_w180_cw180_ch80_thumb.jpg</code>: a size of 180px wide and 80px high and it is a thumbnail (<code>thumb</code>)') . '</li>' .
										'<li>' . gettext('<code>photo_85_cw72_ch72_thumb_copyright_gray.jpg</code>: sized 85px cropped at about 7.6% (one half of 72/85) from the horizontal and vertical sides with a watermark (<code>copyright</code>) and rendered in grayscale (<code>gray</code>).') . '</li>' .
										'<li>' . gettext('<code>photo_w85_h85_cw350_ch350_cx43_cy169_thumb_copyright.jpg</code>: a custom cropped 85px thumbnail with watermark.') . '</li>' .
										'</ul>' .
										'</p>' .
										'<p>' .
										gettext('If a field is not represented in the cached name, leave the field blank. Custom crops (those with cx and cy) really cannot be cached easily since each image has unique values. ' .
														'See the <em>template-functions</em>::<code>getCustomImageURL()</code> comment block for details on these fields.') .
										'</p>' .
										'<p>' .
										gettext('Some themes use <em>MaxSpace</em> image functions. To cache images referenced by these functions set the <em>width</em> and <em>height</em> parameters to the <em>MaxSpace</em> container size and check the <code>MaxSpace</code> checkbox.') .
										'</p>'
						)
		);
		$list = array('<em>' . gettext('Albums') . '</em>' => 'cacheManager_albums', '<em>' . gettext('Images') . '</em>' => 'cacheManager_images');
		if (extensionEnabled('zenpage')) {
			$list['<em>' . gettext('News') . '</em>'] = 'cacheManager_news';
			$list['<em>' . gettext('Pages') . '</em>'] = 'cacheManager_pages';
		} else {
			setOption('cacheManager_news', 0);
			setOption('cacheManager_pages', 0);
		}
		$options[gettext('Purge cache files')] = array('key'				 => 'cacheManager_items', 'type'			 => OPTION_TYPE_CHECKBOX_ARRAY,
						'order'			 => 0,
						'checkboxes' => $list,
						'desc'			 => gettext('If a <em>type</em> is checked, the HTML and RSS caches for the item will be purged when the published state of an item of <em>type</em> changes.') .
						'<div class="notebox">' . gettext('<strong>NOTE:</strong> The entire cache is cleared since there is no way to ascertain if a gallery page contains dependencies on the item.') . '</div>'
		);
		return $options;
	}

	/**
	 *
	 * custom option handler
	 * @param string $option
	 * @param mixed $currentValue
	 */
	function handleOption($option, $currentValue) {
		global $_zp_gallery;
		$currenttheme = $_zp_gallery->getCurrentTheme();
		$custom = array();
		$result = query('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager" ORDER BY `aux`');
		$key = 0;
		while ($row = db_fetch_assoc($result)) {
			$theme = $row['aux'];
			$data = unserialize($row['data']);
			$custom[$theme][] = $data;
		}
		ksort($custom, SORT_LOCALE_STRING);
		$custom[''] = array(array());
		$c = 0;
		?>
		<script type="text/javascript">
			//<!-- <![CDATA[
			function showTheme(theme) {
				html = $('#' + theme + '_arrow').html();
				if (html.match(/down/)) {
					html = html.replace(/_down/, '_up');
					html = html.replace(/title="<?php echo gettext('Show'); ?>/, 'title="<?php echo gettext('Hide'); ?>"');
					$('#' + theme + '_list').show();
				} else {
					html = html.replace(/_up/, '_down');
					html = html.replace(/title="<?php echo gettext('Hide'); ?>/, 'title="<?php echo gettext('Show'); ?>"');
					$('#' + theme + '_list').hide();
				}
				$('#' + theme + '_arrow').html(html);
			}
			//]]> -->
		</script>
		<?php
		foreach ($custom as $theme => $themedata) {
			$themedata = sortMultiArray($themedata, array('thumb', 'image_size', 'image_width', 'image_height'));
			?>
			<span class="icons" id="<?php echo $theme; ?>_arrow">
				<?php
				if ($theme) {
					$inputclass = 'hidden';
					echo '<em>' . $theme . '</em> (' . count($themedata), ')';
				} else {
					$inputclass = 'textbox';
					echo '<br />' . gettext('add');
				}
				?>
				<a href="javascript:showTheme('<?php echo $theme; ?>');" title="<?php echo gettext('Show'); ?>">
					<img class="icon-position-top4" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/arrow_down.png'; ?>" alt="" />
				</a>
			</span>
			<br />
			<div id="<?php echo $theme; ?>_list" style="display:none">
				<br />
				<?php
				foreach ($themedata as $cache) {
					$key++;
					if ($c % 2) {
						$class = 'boxb';
					} else {
						$class = 'box';
					}
					?>
					<div>
						<?php
						$c++;
						if (isset($cache['enable']) && $cache['enable']) {
							$allow = ' checked="checked"';
						} else {
							$allow = '';
						}
						?>
						<div class="<?php echo $class; ?>">
							<input type="<?php echo $inputclass; ?>" size="25" name="cacheManager_theme_<?php echo $key; ?>" value="<?php echo $theme; ?>" />
							<?php
							if ($theme) {
								?>
								<span class="displayinlineright"><?php echo gettext('Delete'); ?> <input type="checkbox" name="cacheManager_delete_<?php echo $key; ?>" value="1" /></span>
								<input type="hidden" name="cacheManager_valid_<?php echo $key; ?>" value="1" />
								<?php
							}
							?>
							<br />
							<?php
							foreach (array('image_size'	 => gettext('Size'), 'image_width'	 => gettext('Width'), 'image_height' => gettext('Height'),
							'crop_width'	 => gettext('Crop width'), 'crop_height'	 => gettext('Crop height'), 'crop_x'			 => gettext('Crop X axis'),
							'crop_y'			 => gettext('Crop Y axis')) as $what => $display) {
								if (isset($cache[$what])) {
									$v = $cache[$what];
								} else {
									$v = '';
								}
								?>
								<span class="nowrap"><?php echo $display; ?> <input type="textbox" size="2" name="cacheManager_<?php echo $what; ?>_<?php echo $key; ?>" value="<?php echo $v; ?>" /></span>
								<?php
							}
							if (isset($cache['wmk'])) {
								$wmk = $cache['wmk'];
							} else {
								$wmk = '';
							}
							?>
							<span class="nowrap"><?php echo gettext('Watermark'); ?> <input type="textbox" size="20" name="cacheManager_wmk_<?php echo $key; ?>" value="<?php echo $wmk; ?>" /></span>
							<br />
							<span class="nowrap"><?php echo gettext('MaxSpace'); ?><input type="checkbox"  name="cacheManager_maxspace_<?php echo $key; ?>" value="1"<?php if (isset($cache['maxspace']) && $cache['maxspace']) echo ' checked="checked"'; ?> /></span>
							<span class="nowrap"><?php echo gettext('Thumbnail'); ?><input type="checkbox"  name="cacheManager_thumb_<?php echo $key; ?>" value="1"<?php if (isset($cache['thumb']) && $cache['thumb']) echo ' checked="checked"'; ?> /></span>
							<span class="nowrap"><?php echo gettext('Grayscale'); ?><input type="checkbox"  name="cacheManager_gray_<?php echo $key; ?>" value="gray"<?php if (isset($cache['gray']) && $cache['gray']) echo ' checked="checked"'; ?> /></span>
						</div>
						<br />
					</div>
					<?php
				}
				?>
			</div><!-- <?php echo $theme; ?>_list -->
			<?php
		}
	}

	/**
	 *
	 * process custom option saves
	 * @param string $themename
	 * @param string $themealbum
	 * @return string
	 */
	static function handleOptionSave($themename, $themealbum) {
		$cache = array();
		foreach ($_POST as $key => $value) {
			preg_match('/^cacheManager_(.*)_(.*)/', $key, $matches);
			if ($value && !empty($matches)) {
				$cache[$matches[2]][$matches[1]] = sanitize(trim($value));
			}
		}
		query('DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager"');
		foreach ($cache as $cacheimage) {
			if (!isset($cacheimage['delete']) && count($cacheimage) > 1) {
				$cacheimage['theme'] = preg_replace("/[\s\"\']+/", "-", $cacheimage['theme']);
				$sql = 'INSERT INTO ' . prefix('plugin_storage') . ' (`type`, `aux`,`data`) VALUES ("cacheManager",' . db_quote($cacheimage['theme']) . ',' . db_quote(serialize($cacheimage)) . ')';
				query($sql);
			}
		}
		return false;
	}

	static function addThemeCacheSize($theme, $size, $width, $height, $cw, $ch, $cx, $cy, $thumb, $watermark = NULL, $effects = NULL, $maxspace = NULL) {
		$cacheSize = serialize(array('theme'				 => $theme, 'apply'				 => false, 'image_size'	 => $size, 'image_width'	 => $width, 'image_height' => $height,
						'crop_width'	 => $cw, 'crop_height'	 => $ch, 'crop_x'			 => $cx, 'crop_y'			 => $cy, 'thumb'				 => $thumb, 'wmk'					 => $watermark, 'gray'				 => $effects, 'maxspace'		 => $maxspace, 'valid'				 => 1));
		$sql = 'INSERT INTO ' . prefix('plugin_storage') . ' (`type`, `aux`,`data`) VALUES ("cacheManager",' . db_quote($theme) . ',' . db_quote($cacheSize) . ')';
		query($sql);
	}

	/**
	 *
	 * filter for the setShow() methods
	 * @param object $obj
	 */
	static function published($obj) {
		if (getOption('cacheManager_' . $obj->table)) {
			//	TODO: clear other feed caches?
			if (class_exists('RSS')) {
				$RSS = new RSS();
			} else {
				$RSS = NULL;
			}
			if (class_exists('static_html_cache')) {
				static_html_cache::clearHTMLCache('index');
			}
			switch ($type = $obj->table) {
				case 'pages':
					if (class_exists('static_html_cache')) {
						static_html_cache::clearHTMLCache();
					}
					if ($RSS)
						$RSS->clearCache();
					break;
				case 'news':
					if (class_exists('static_html_cache')) {
						static_html_cache::clearHTMLCache();
					}
					if ($RSS)
						$RSS->clearCache();
					break;
				case 'albums':
					if (class_exists('static_html_cache')) {
						static_html_cache::clearHTMLCache();
					}
					if ($RSS)
						$RSS->clearCache();
					break;
				case 'images':
					if (class_exists('static_html_cache')) {
						static_html_cache::clearHTMLCache();
					}
					if ($RSS)
						$RSS->clearCache();
					break;
			}
		}
		return $obj;
	}

	static function overviewbutton($buttons) {
		if (query_single_row('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager" LIMIT 1')) {
			$enable = true;
			$title = gettext('Finds images that have not been cached and creates the cached versions.');
		} else {
			$enable = false;
			$title = gettext('You must first set the plugin options for cached image parameters.');
		}

		$buttons[] = array(
						'category'		 => gettext('Cache'),
						'enable'			 => $enable,
						'button_text'	 => gettext('Cache images'),
						'formname'		 => 'cacheManager_button',
						'action'			 => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager/cacheImages.php',
						'icon'				 => 'images/cache.png',
						'alt'					 => '',
						'hidden'			 => '',
						'rights'			 => ADMIN_RIGHTS,
						'title'				 => $title
		);
		if (class_exists('RSS')) {
			$buttons[] = array(
							'XSRFTag'			 => 'clear_cache',
							'category'		 => gettext('Cache'),
							'enable'			 => true,
							'button_text'	 => gettext('Purge RSS cache'),
							'formname'		 => 'purge_rss_cache.php',
							'action'			 => WEBPATH . '/' . ZENFOLDER . '/admin.php?action=clear_rss_cache',
							'icon'				 => 'images/edit-delete.png',
							'alt'					 => '',
							'title'				 => gettext('Delete all files from the RSS cache'),
							'hidden'			 => '<input type="hidden" name="action" value="clear_rss_cache" />',
							'rights'			 => ADMIN_RIGHTS
			);
		}
		$buttons[] = array(
						'XSRFTag'			 => 'clear_cache',
						'category'		 => gettext('Cache'),
						'enable'			 => true,
						'button_text'	 => gettext('Purge Image cache'),
						'formname'		 => 'purge_image_cache.php',
						'action'			 => WEBPATH . '/' . ZENFOLDER . '/admin.php?action=action=clear_cache',
						'icon'				 => 'images/edit-delete.png',
						'alt'					 => '',
						'title'				 => gettext('Delete all files from the Image cache'),
						'hidden'			 => '<input type="hidden" name="action" value="clear_cache" />',
						'rights'			 => ADMIN_RIGHTS
		);
		$buttons[] = array(
						'category'		 => gettext('Cache'),
						'enable'			 => true,
						'button_text'	 => gettext('Purge HTML cache'),
						'formname'		 => 'clearcache_button',
						'action'			 => WEBPATH . '/' . ZENFOLDER . '/admin.php?action=clear_html_cache',
						'icon'				 => 'images/edit-delete.png',
						'title'				 => gettext('Clear the static HTML cache. HTML pages will be re-cached as they are viewed.'),
						'alt'					 => '',
						'hidden'			 => '<input type="hidden" name="action" value="clear_html_cache">',
						'rights'			 => ADMIN_RIGHTS,
						'XSRFTag'			 => 'ClearHTMLCache'
		);
		return $buttons;
	}

	static function albumbutton($html, $object, $prefix) {
		$html .= '<hr />';
		if (query_single_row('SELECT * FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager" LIMIT 1')) {
			$disable = '';
			$title = gettext('Finds images that have not been cached and creates the cached versions.');
		} else {
			$disable = ' disabled="disabled"';
			$title = gettext("You must first set the plugin's options for cached image parameters.");
		}
		$html .= '<div class="button buttons tooltip" title="' . $title . '"><a href="' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/cacheManager/cacheImages.php?album=' . html_encode($object->name) . '&amp;XSRFToken=' . getXSRFToken('cacheImages') . '"' . $disable . '><img src="images/cache.png" />' . gettext('Cache album images') . '</a><br class="clearall" /></div>';
		return $html;
	}

	static function deleteThemeCacheSizes($theme) {
		query('DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`="cacheManager" AND `aux`=' . db_quote($theme));
	}

}
?>
