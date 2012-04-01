<?php
/**
 *
 * This plugin will examine the gallery and make image references to any images which have not
 * already been cached. Your browser will then request these images causing the caching process to be
 * executed.
 *
 * <b>Note:</b> The Caching process will cause your browser to display each and every image that has not
 * been previously cached. If your server does not do a good job of thread management this may swamp
 * it!
 *
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext("Caches newly uploaded images.");
$plugin_notice = gettext('<strong>NOTE</strong>: The default caching is based on the gallery\'s default theme <em>thumbnail</em> and <em>image</em> options. Should your theme use custom images or thumbs you should change the plugin options accordingly. The caching process requires that your WEB browser <em>fetch</em> each image size. For a full gallery cache this may excede the capacity of your server and not complete. You may have to refresh the page multiple times until the report of the number of images cached is zero.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.3';
$option_interface = 'cache_images';

zp_register_filter('admin_utilities_buttons', 'cache_images::overviewbutton');
zp_register_filter('edit_album_utilities', 'cache_images::albumbutton',9999);
zp_register_filter('custom_option_save','cache_images::handleOptionSave');

class cache_images {
	static function overviewbutton($buttons) {
		if (query_single_row('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="cacheImages" LIMIT 1')) {
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
									'formname'=>'cache_images_button',
									'action'=>WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cacheImages/cacheImages.php',
									'icon'=>'images/cache.png',
									'alt'=>'',
									'hidden'=>'',
									'rights'=>ADMIN_RIGHTS,
									'XSRFTag'=>'cache_images',
									'title'=>$title
		);
		return $buttons;
	}
	static function albumbutton($html, $object, $prefix) {
		$html .= '<hr />';
		if (query_single_row('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="cacheImages" LIMIT 1')) {
			$disable = '';
			$title = gettext('Finds images that have not been cached and creates the cached versions.');
		} else {
			$disable = ' disabled="disabled"';
			$title = gettext("You must first set the plugin's options for cached image parameters.");
		}
		$html .= '<div class="button buttons tooltip" title="'.$title.'"><a href="'.WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/cacheImages/cacheImages.php?album='.html_encode($object->name).'&amp;XSRFToken='.getXSRFToken('cache_images').'"'.$disable.'><img src="images/cache.png" />'.gettext('Cache album images').'</a><br clear="all" /></div>';
		return $html;

	}

	function getOptionsSupported() {
		if (getOption('zp_plugin_cacheImages')) {
			return array(''=>array('key' => 'cropImage_list', 'type' => OPTION_TYPE_CUSTOM,
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
		} else {
			return array(''=>array('key'=>'cropImage_note', 'type'=>OPTION_TYPE_NOTE, 'desc'=>'<span class="notebox">'.gettext('This plugin must be enabled to process options.').'</span>'));
		}
	}
	function handleOption($option, $currentValue) {
		$options = $custom = array();
		$result = query('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="cacheImages"');
		while ($row = db_fetch_assoc($result)) {
			$custom[] = unserialize($row['data']);
		}
		if (empty($custom)) {
			$custom[] = array('image_size'=>getOption('image_size'),'image_use_side'=>getOption('image_use_side'));
			$custom[] = array('image_size'=>getOption('thumb_size'),'thumb'=>1);
		}
		$custom[] = array();
		$c = 0;
		foreach($custom as $key=>$cache) {
			if ($c % 2) {
				?>
				<div style="background-color:LightGray">
				<?php
			} else {
				?>
				<div>
				<?php
			}
			$c++;
			?>
			<input type="checkbox" name="cacheImages_enable_<?php echo $key; ?>" value="1" checked="checked" /><?php echo gettext('Apply:'); ?><br />
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
				<span class="nowrap"><?php echo $display; ?> <input type="textbox" size="2" name="cacheImages_<?php echo $what; ?>_<?php echo $key; ?>" value="<?php echo $v; ?>" /></span>
				<?php
			}
			if (isset($cache['wmk'])) {
				$wmk = $cache['wmk'];
			} else {
				$wmk = '';
			}
			?>
			<span class="nowrap"><?php echo gettext('Watermark'); ?> <input type="textbox" size="20" name="cacheImages_wmk_<?php echo $key; ?>" value="<?php echo $wmk; ?>" /></span>
			<span class="nowrap"><?php echo gettext('Thumbnail'); ?><input type="checkbox"  name="cacheImages_thumb_<?php echo $key; ?>" value="1"<?php if (isset($cache['thumb'])) echo ' checked="checked"'; ?> /></span>
			<span class="nowrap"><?php echo gettext('Grayscale'); ?><input type="checkbox"  name="cacheImages_gray_<?php echo $key; ?>" value="gray"<?php if (isset($cache['gray'])) echo ' checked="checked"'; ?> /></span>
			</div><br />
			<?php
		}
	}

	static function handleOptionSave($notify,$themename,$themealbum) {
		$cache = array();
		foreach ($_POST as $key=>$value) {
			preg_match('/^cacheImages_(.*)_(.*)/', $key, $matches);
			if ($value && !empty($matches)) {
				$cache[$matches[2]][$matches[1]] = sanitize($value);
			}
		}
		query('DELETE FROM '.prefix('plugin_storage').' WHERE `type`="cacheImages"');
		foreach($cache as $crop) {
			if (isset($crop['enable']) && count($crop)>1) {
				$sql = 'INSERT INTO '.prefix('plugin_storage').' (`type`, `aux`,`data`) VALUES ("cacheImages", "",'.db_quote(serialize($crop)).')';
				query($sql);
			}
		}
		return $notify;
	}

}
?>