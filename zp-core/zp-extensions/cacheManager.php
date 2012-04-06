<?php
/**
 *
 *  This plugin is the centralized Cache manager for Zenphoto. It provides options for automatic HTML and RSS cache
 * purging when
 * the publish state objects changes, for <i>pre-creating</i> the image cache images, and the utilities for
 * purging these caches.
 *
 * The image cache <i>pre-creating</i> will examine the gallery and make image references to any images which have not
 * already been cached. Your browser will then request these images causing the caching process to be
 * executed.
 *
 * <b>Note:</b> The Caching process will cause your browser to display each and every image that has not
 * been previously cached. If your server does not do a good job of thread management this may swamp
 * it! You should probably also clear your browser cache before using this utility. Otherwise
 * your browser may fetch the images locally rendering the above process useless.
 *
 * You may have to refresh the page multiple times until the report of the number of images cached is zero.
 * If some images seem to never be rendered you may be experiencing memory limit or other graphics processor
 * errors. You can click on the image that does not render to get the <var>i.php</var> debug screen for the
 * image. This may help in figuring out what has gone wrong.
 *
 * The <i>default</i>, <i>effervescence+</i>,<i>garland</i>, <i>stopdesign</i>, and <i>zenpage</i> themes have created <i>Caching</i> size options
 * for the images sizes they use. The <i>stopdesign</i> theme creates some two sets of thumbnail sizes, one for landscape and one for protrait
 * "35mm slide" thumbnails. You may wish not to apply both (or either) of these sizes if you do not want the excess images. The caching
 * process does not consider the image orientation, it simply creates a cache image at the sizes specified.
 *
 * <b>NOTE:</b> setting theme options or installing a new version of Zenphoto will re-create these caching sizes.
 * Use a different <i>theme name</i> for custom versions that you create.
 *
 * @package plugins
 * @author Stephen Billard (sbillard)
 */
if (!defined('OFFSET_PATH')) {
	define('OFFSET_PATH', 3);
	require_once(dirname(dirname(__FILE__)).'/admin-globals.php');
}
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Provides cache management utilities for Image, HTML, and RSS caches.");
$plugin_notice = gettext('<strong>NOTE</strong>: The image caching process requires that your WEB browser <em>fetch</em> each image size. For a full gallery cache this may excede the capacity of your server and not complete.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'cacheManager';


zp_register_filter('admin_utilities_buttons', 'cacheManager::overviewbutton');
zp_register_filter('edit_album_utilities', 'cacheManager::albumbutton',9999);
zp_register_filter('custom_option_save','cacheManager::handleOptionSave');

zp_register_filter('show_change', 'cacheManager::published');

/**
 *
 * Standard options interface
 * @author Stephen
 *
 */
class cacheManager {

	function __construct() {
	}

	/**
	 *
	 * supported options
	 */
	function getOptionsSupported() {
		global $_zp_zenpage;
		if (getOption('zp_plugin_cacheManager')) {
			$options = array(gettext('Image caching sizes')=>array('key' => 'cropImage_list', 'type' => OPTION_TYPE_CUSTOM,
																														'order' => 1,
																														'desc' => gettext('Cropped images will be made in these parameters if the <em>Create image</em> box is checked. Un-check to box to remove the settings.'.
																																			'You can determine the values for these fields by examining your cached images. The file names will look something like these:').
																																			'<ul>'.
																																			'<li>'.gettext('<code>photo_595.jpg</code>: sized to 595 pixels').'</li>'.
																																			'<li>'.gettext('<code>photo_w180_cw180_ch80_thumb.jpg</code>: a size of 180px wide and 80px high and it is a thumbnail (<code>thumb</code>)').'</li>'.
																																			'<li>'.gettext('<code>photo_85_cw72_ch72_thumb_copyright_gray.jpg</code>: sized 85px cropped at about 7.6% (one half of 72/85) from the horizontal and vertical sides with a watermark (<code>copyright</code>) and rendered in grayscale (<code>gray</code>).').'</li>'.
																																			'<li>'.gettext('<code>photo_w85_h85_cw350_ch350_cx43_cy169_thumb_copyright.jpg</code>: a custom cropped 85px thumbnail with watermark.').'</li>'.
																																			'</ul>'.
																																			gettext('If a field is not represented in the cached name, leave the field blank. Custom crops (those with cx and cy) really cannot be cached easily since each image has unique values. '.
																																			'See the <em>template-functions</em>::<code>getCustomImageURL()</code> comment block for details on these fields.')
			));
			$list = array('<em>'.gettext('Albums').'</em>'=>'cacheManager_albums', '<em>'.gettext('Images').'</em>'=>'cacheManager_images');
			if (getOption('zp_plugin_zenpage')) {
				$list['<em>'.gettext('News').'</em>'] = 'cacheManager_news';
				$list['<em>'.gettext('Pages').'</em>'] = 'cacheManager_pages';
			} else {
				setOption('cacheManager_news', 0);
				setOption('cacheManager_pages', 0);
			}
			$options[gettext('Purge cache files')] = array('key'=>'cacheManager_items', 'type'=>OPTION_TYPE_CHECKBOX_ARRAY,
																										'order'=> 0,
																										'checkboxes' => $list,
																										'desc'=>gettext('If a <em>type</em> is checked, the HTML and RSS caches for the item  will be purged when an the published state of an item of <em>type</em> changes.').
																										'<div class="notebox">'.gettext('<strong>NOTE:</strong> The entire cache is cleared since there is no way to ascertain if pages contain dependencies on the item.').'</div>');
			return $options;
		} else {
			return array(''=>array('key'=>'cropImage_note', 'type'=>OPTION_TYPE_NOTE, 'desc'=>'<span class="notebox">'.gettext('This plugin must be enabled to process options.').'</span>'));
		}
	}

	/**
	 *
	 * place holder
	 * @param string $option
	 * @param mixed $currentValue
	 */
	function handleOption($option, $currentValue) {
		global $_zp_gallery;
		$currenttheme = $_zp_gallery->getCurrentTheme();
		$custom = array();
		$result = query('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="cacheManager" ORDER BY `aux`');
		while ($row = db_fetch_assoc($result)) {
			$custom[] = unserialize($row['data']);
		}
		$custom = sortMultiArray($custom, array('theme','thumb','size'));
		$custom[] = array();
		$c = 0;
		foreach($custom as $key=>$cache) {
			if ($c % 2) {
				$class = 'boxb';
			} else {
				$class = 'box';
			}
			?>
			<div>
			<?php
			$c++;
			$theme = $allow = '';
			if (isset($cache['enable']) && $cache['enable']) {
				$allow = ' checked="checked"';
			}
			$theme = @$cache['theme'];
			?>
			<input type="textbox" size="25" name="cacheManager_theme_<?php echo $key; ?>" value="<?php echo $theme; ?>" />
			<span class="nowrap"><?php echo gettext('Delete'); ?> <input type="checkbox" name="cacheManager_delete_<?php echo $key; ?>" value="1" /></span>
			<div class="<?php echo $class; ?>">
			<?php
			foreach (array('image_size'=>gettext('Size'),'image_width'=>gettext('Width'),'image_height'=>gettext('Height'),
											'crop_width'=>gettext('Crop width'),'crop_height'=>gettext('Crop height'),'crop_x'=>gettext('Crop X axis'),
											'crop_y'=>gettext('Crop Y axis')) as $what=>$display) {
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
			<span class="nowrap"><?php echo gettext('Thumbnail'); ?><input type="checkbox"  name="cacheManager_thumb_<?php echo $key; ?>" value="1"<?php if (isset($cache['thumb'])&&$cache['thumb']) echo ' checked="checked"'; ?> /></span>
			<span class="nowrap"><?php echo gettext('Grayscale'); ?><input type="checkbox"  name="cacheManager_gray_<?php echo $key; ?>" value="gray"<?php if (isset($cache['gray'])&&$cache['gray']) echo ' checked="checked"'; ?> /></span>
			</div>
			</div><br />
			<?php
		}
	}


	/**
	 *
	 * filter for the setShow() methods
	 * @param object $obj
	 */
	static function published($obj) {
		if (getOption('cacheManager_'.$obj->table)) {
			require_once (SERVERPATH.'/'.ZENFOLDER.'/class-RSS.php');
			//TODO: there should be a finer purging for RSS
			if (class_exists('static_html_cache')) {
				static_html_cache::clearHTMLCache('index');
			}
			switch ($type = $obj->table) {
				case 'pages':
					if (class_exists('static_html_cache')) {
						static_html_cache::clearHTMLCache();
					}
					RSS::clearRSSCache();
					break;
				case 'news':
					if (class_exists('static_html_cache')) {
						static_html_cache::clearHTMLCache();
					}
					RSS::clearRSSCache();
					break;
				case 'albums':
					if (class_exists('static_html_cache')) {
						static_html_cache::clearHTMLCache();
					}
					RSS::clearRSSCache();
					break;
				case 'images':
					if (class_exists('static_html_cache')) {
						static_html_cache::clearHTMLCache();
					}
					RSS::clearRSSCache();
					break;
			}
		}
		return $obj;
	}

	static function overviewbutton($buttons) {
		if (query_single_row('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="cacheManager" LIMIT 1')) {
			$enable = true;
			$title = gettext('Finds images that have not been cached and creates the cached versions.');
		} else {
			$enable = false;
			$title = gettext('You must first set the plugin options for cached image parameters.');
		}

		$buttons[] = array(
									'category'=>gettext('cache'),
									'enable'=>$enable,
									'button_text'=>gettext('Cache images'),
									'formname'=>'cacheManager_button',
									'action'=>WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cacheManager/cacheImages.php',
									'icon'=>'images/cache.png',
									'alt'=>'',
									'hidden'=>'',
									'rights'=>ADMIN_RIGHTS,
									'XSRFTag'=>'cacheImages',
									'title'=>$title
										);
		$buttons[] = array(
									'XSRFTag'=>'clear_cache',
									'category'=>gettext('cache'),
									'enable'=>true,
									'button_text'=>gettext('Purge RSS cache'),
									'formname'=>'purge_rss_cache.php',
									'action'=>WEBPATH.'/'.ZENFOLDER.'/admin.php?action=clear_rss_cache',
									'icon'=>'images/edit-delete.png',
									'alt'=>'',
									'title'=>gettext('Delete all files from the RSS cache'),
									'hidden'=>'<input type="hidden" name="action" value="clear_rss_cache" />',
									'rights'=> ADMIN_RIGHTS
									);
		$buttons[] = array(
									'XSRFTag'=>'clear_cache',
									'category'=>gettext('cache'),
									'enable'=>true,
									'button_text'=>gettext('Purge Image cache'),
									'formname'=>'purge_image_cache.php',
									'action'=>WEBPATH.'/'.ZENFOLDER.'/admin.php?action=action=clear_cache',
									'icon'=>'images/edit-delete.png',
									'alt'=>'',
									'title'=>gettext('Delete all files from the Image cache'),
									'hidden'=>'<input type="hidden" name="action" value="clear_cache" />',
									'rights'=> ADMIN_RIGHTS
									);
		$buttons[] = array(
									'category'=>gettext('cache'),
									'enable'=>true,
									'button_text'=>gettext('Purge HTML cache'),
									'formname'=>'clearcache_button',
									'action'=>PLUGIN_FOLDER.'/cacheManager.php?action=clear_html_cache',
									'icon'=>'images/edit-delete.png',
									'title'=>gettext('Clear the static HTML cache. HTML pages will be re-cached as they are viewed.'),
									'alt'=>'',
									'hidden'=> '<input type="hidden" name="action" value="clear_html_cache">',
									'rights'=> ADMIN_RIGHTS,
									'XSRFTag'=>'ClearHTMLCache'
									);
		return $buttons;
	}

	static function albumbutton($html, $object, $prefix) {
		$html .= '<hr />';
		if (query_single_row('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="cacheManager" LIMIT 1')) {
			$disable = '';
			$title = gettext('Finds images that have not been cached and creates the cached versions.');
		} else {
			$disable = ' disabled="disabled"';
			$title = gettext("You must first set the plugin's options for cached image parameters.");
		}
		$html .= '<div class="button buttons tooltip" title="'.$title.'"><a href="'.WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cacheManager/cacheImages.php?album='.html_encode($object->name).'&amp;XSRFToken='.getXSRFToken('cacheImages').'"'.$disable.'><img src="images/cache.png" />'.gettext('Cache album images').'</a><br clear="all" /></div>';
		return $html;
	}

	static function handleOptionSave($notify,$themename,$themealbum) {
		$cache = array();
		foreach ($_POST as $key=>$value) {
			preg_match('/^cacheManager_(.*)_(.*)/', $key, $matches);
			if ($value && !empty($matches)) {
				$cache[$matches[2]][$matches[1]] = sanitize(trim($value));
			}
		}
		query('DELETE FROM '.prefix('plugin_storage').' WHERE `type`="cacheManager"');
		foreach($cache as $cacheimage) {
			if (!isset($cacheimage['delete']) && count($cacheimage)>1) {
				$sql = 'INSERT INTO '.prefix('plugin_storage').' (`type`, `aux`,`data`) VALUES ("cacheManager",'.db_quote($cacheimage['theme']).','.db_quote(serialize($cacheimage)).')';
				query($sql);
			}
		}
		return $notify;
	}

	static function addThemeCacheSize($theme, $size, $width, $height, $cw, $ch, $cx, $cy, $thumb, $watermark, $effects) {
		$cacheSize = serialize( array('theme'=>$theme,'apply'=>false,'image_size'=>$size, 'image_width'=>$width, 'image_height'=>$height,
										'crop_width'=>$cw, 'crop_height'=>$ch, 'crop_x'=>$cx, 'crop_y'=>$cy, 'thumb'=>$thumb, 'wmk'=>$watermark, 'gray'=>$effects));
		$sql = 'INSERT INTO '.prefix('plugin_storage').' (`type`, `aux`,`data`) VALUES ("cacheManager",'.db_quote($theme).','.db_quote($cacheSize).')';
		query($sql);
	}

	static function deleteThemeCacheSizes($theme) {
		query('DELETE FROM '.prefix('plugin_storage').' WHERE `type`="cacheManager" AND `aux`='.db_quote($theme));
	}

}

if (isset($_GET['action']) && $_GET['action']=='clear_html_cache' && zp_loggedin(ADMIN_RIGHTS)) {
	XSRFdefender('ClearHTMLCache');
	require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/static_html_cache.php');
	static_html_cache::clearHTMLCache();
	header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?action=external&msg='.gettext('HTML cache cleared.'));
	exitZP();
}
?>