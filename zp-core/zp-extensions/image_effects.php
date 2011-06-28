<?php
/**
 * An example plugin demonstrating the use of the various "image_html" filters.
 *
 * Each effect is defined by a text file these sections (no individual section is required).
 *
 * <source>...</source>
 *  Documents the source of the effect
 *
 * <head>...</head>:
 * 	each effect will have a head section that contains the HTML to be emitted in the theme head.
 *
 * <class>...</class>:
 * 	elements from this section will be added to the class element of the <img src... /> tag.
 *
 * <extra>..</extra>:
 * 	It is also possible to create a extra section for html that will be inserted into the
 * 	<img src... /> tag just befor the />. This can be used to insert style="..." or other elements.
 *
 * <validate> file path </validate>
 * 	used specially for Effenberger effects, but applicable to similar situations. Causes the
 * 	named file to have its presence tested. If it is not present, the "effect" is not made available.
 * 	Multiple files are separated by semicolons. Each is tested.
 *
 * The following tokens are available to represent paths:
 *	%WEBPATH% to represent the WEB path to the Zenphoto installation.
 *	%SERVERPATH% to represent the server path to the Zenphoto installation.
 *	%PLUGIN_FOLDER% to represent the Zenphoto "extensions" folder.
 *	%USER_PLUGIN_FOLDER% to represent the root "plugin" folder.
 *	%ZENFOLDER% to represent the zp-core folder.
 *
 * Pixastic effects:
 *	Included standard are several effects from http://www.pixastic.com/. Please visit their site for
 *	detailed documentation.
 *
 * Effenberger effects:
 * 	Some effects which can be used are the javascript effectscreated by Christian Effenberger
 * 	which may be downloaded from his site: //www.netzgesta.de/cvi/
 *
 * 	No effenberger effects are distributed with this plugin to conform with CVI licensing constraints.
 * 	To add an effect to the plugin, download the zip file from the site. Extract the effect
 * 	files and place in the image_effects folder (in the global plugins folder.)
 *
 * 	For instance, to add the Reflex effect, download reflex.zip, unzip it, and place reflex.js
 * 	in the folder. Check the image_effects foder in the zenphoto extensions to see if there is already
 * 	header file. If not create one in the plugins/image_effects folder. Use as a model one of the other
 * 	Effenberger effects header files.
 *
 * 	Included some typical Effenberger files as examples. Simply provide the appropriate js script as above
 * 	to activate them.
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

$plugin_description = gettext('Attaches "Image effects" to images and thumbnails.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';

$option_interface = 'image_effects';

zp_register_filter('standard_image_html', 'image_std_images');
zp_register_filter('custom_image_html', 'image_custom_images');
zp_register_filter('standard_album_thumb_html', 'image_std_album_thumbs');
zp_register_filter('standard_image_thumb_html', 'image_std_image_thumbs');
zp_register_filter('custom_album_thumb_html', 'image_custom_album_thumbs');

if (defined('OFFSET_PATH') && OFFSET_PATH == 0) {
	zp_register_filter('theme_head', 'image_effectsJS');
}

function image_effectsJS() {
	global $_image_effects_random;
	$effectlist = array_keys(getPluginFiles('*.txt','image_effects'));
	shuffle($effectlist);
	$common = array();
	do {
		$_image_effects_random = array_shift($effectlist);
		if (getOption('image_effects_random_'.$_image_effects_random)) {
			$effectdata = image_effects_getEffect($_image_effects_random);
			$invalid_effect = $effectdata && array_key_exists('error', $effectdata);
		} else {
			$invalid_effect = true;
		}
	} while ($_image_effects_random && $invalid_effect);

	if (!$_image_effects_random) echo "<br />random effect empty!";

	$selected_effects = array_unique(array(	getOption('image_std_images'), getOption('image_custom_images'),
																					getOption('image_std_album_thumbs'), getOption('image_std_image_thumbs'),
																					getOption('image_custom_album_thumbs'), $_image_effects_random));
	if (false !== $key = array_search('!', $selected_effects)) {
		unset($selected_effects[$key]);
	}
	if (count($selected_effects) > 0) {
		foreach ($selected_effects as $effect) {
			$effectdata = image_effects_getEffect($effect);
			if (array_key_exists('head', $effectdata)) {
				$common_data = trim($effectdata['head']);
				while ($common_data) {
					$i = strcspn ($common_data,'= >');
					if ($i === false) {
						$common_data = '';
					} else {
						$tag = '</'.trim(substr($common_data, 1, $i)).'>';
						$k = strpos($common_data, '>');
						$j = strpos($common_data,$tag, $k+1);
						if ($j === false) {
							$j = strpos($common_data,'/>');
							if ($j === false) {
								$common_data = '';
							} else {
								$j = $j + 2;
							}
						} else {
							$j = $j + strlen($tag);
						}
						if ($j === false) {
							$common_data = '';
						} else {
							$common_element = substr($common_data,0,$j);
							$common_data = trim(substr($common_data, strlen($common_element)));
							$common_element = trim($common_element);
							if (!in_array($common_element, $common)) {
								$common[] = $common_element;
							}
						}
					}
				}
			}
		}
		if (!empty($common)) {
			echo implode("\n",$common);
		}
	}
}

/**
 * The option handler class
 *
 */
class image_effects {

	var $effects = NULL;

	/**
	 * Class instantiation function
	 *
	 * @return effenberger
	 */
	function image_effects() {
		$effect = getPluginFiles('*.txt','image_effects');
		foreach ($this->effects = array_keys($effect) as $suffix) {
			setOptionDefault('image_effects_random_'.$suffix, 1);
		}
	}

		/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$list = array('random'=>'!');
		$docs = array();
		$effenberger = array('bevel','corner','crippleedge','curl','filmed','glossy','instant','reflex','slided','sphere');
		foreach($this->effects as $effect) {
			$rand[$effect] = 'image_effects_random_'.$effect;
			$effectdata = image_effects_getEffect($effect);
			$list[$effect] = $effect;
			$docs[chr(0).$effect] = array('key' => 'image_effect_'.$effect, 'order' => $effect, 'type' => OPTION_TYPE_CUSTOM);
			if (array_key_exists('source', $effectdata)) {
				$docs[chr(0).$effect]['desc'] = $effectdata['source'];
			} else {
				$docs[chr(0).$effect]['desc'] = gettext('No acknowledgement');
			}
			if (array_key_exists('error', $effectdata)) {
				$docs[chr(0).$effect]['desc'] .= '<p class="notebox">'.$effectdata['error'].'</p>';
				query('DELETE FROM '.prefix('options').' WHERE `name`="image_custom_random_'.$effect.'"');
				if (in_array($effect, $effenberger)) {
					$docs[chr(0).$effect]['desc'] .= '<p class="notebox">'.gettext('<strong>Note:</strong> Although this plugin supports <em>Effenberger effects</em>, due to licensing considerations no <em>Effenberger effects</em> scripts are included. See <a href="http://www.netzgesta.de/cvi/">CVI Astonishing Image Effects</a> by Christian Effenberger to select and download effects.').'</p>';
				}
			}
		}
		if (count($list) == 0) {
			return array(gettext('No effects') => array('key' => 'image_effect_none', 'type' => OPTION_TYPE_CUSTOM,
										'desc' => ''));
		}
		foreach (array('image_std_images','image_custom_images','image_std_image_thumbs','image_std_album_thumbs','image_custom_image_thumbs','image_custom_album_thumbs') as $option) {
			$effect = getOption($option);
			if ($effect && $effect!='!' && !array_key_exists($effect,$list)) {
				$error[$effect] = '<p class="errorbox">'.sprintf(gettext('<strong>Error:</strong> <em>%s</em> effect no longer exists.'),$effect).'</p>';
			}
		}
		$std = array(	gettext('Images (standard)') => array('key' => 'image_std_images', 'type' => OPTION_TYPE_SELECTOR,
										'order' => 0,
										'selections' => $list, 'null_selection' => gettext('none'),
										'desc' => gettext('Apply <em>effect</em> to images shown via the <code>printDefaultSizedImage()</code> function.')),
									gettext('Images (custom)') =>array('key' => 'image_custom_images', 'type' => OPTION_TYPE_SELECTOR,
										'order' => 0,
										'selections' => $list, 'null_selection' => gettext('none'),
										'desc' => gettext('Apply <em>effect</em> to images shown via the custom image functions.')),
									gettext('Image thumbnails (standard)') =>array('key' => 'image_std_image_thumbs', 'type' => OPTION_TYPE_SELECTOR,
										'order' => 0,
										'selections' => $list, 'null_selection' => gettext('none'),
										'desc' => gettext('Apply <em>effect</em> to images shown via the <code>printImageThumb()</code> function.')),
									gettext('Album thumbnails (standard)') =>array('key' => 'image_std_album_thumbs', 'type' => OPTION_TYPE_SELECTOR,
										'order' => 0,
										'selections' => $list, 'null_selection' => gettext('none'),
										'desc' => gettext('Apply <em>effect</em> to images shown via the <code>printAlbumThumbImage()</code> function.')),
									gettext('Image thumbnails (custom)') =>array('key' => 'image_custom_image_thumbs', 'type' => OPTION_TYPE_SELECTOR,
										'order' => 0,
										'selections' => $list, 'null_selection' => gettext('none'),
										'desc' => gettext('Apply <em>effect</em> to images shown via the custom image functions when <em>thumbstandin</em> is set.')),
									gettext('Album thumbnails  (custom)') =>array('key' => 'image_custom_album_thumbs', 'type' => OPTION_TYPE_SELECTOR,
										'order' => 0,
										'selections' => $list, 'null_selection' => gettext('none'),
										'desc' => gettext('Apply <em>effect</em> to images shown via the <code>printCustomAlbumThumbImage()</code> function.')),
									gettext('Random pool') => array('key' => 'image_effects_random_', 'type' => OPTION_TYPE_CHECKBOX_UL,
										'order' => 1,
										'checkboxes' => $rand,
										'desc' => gettext('Pool of effects for the <em>random</em> effect selection.')),
									chr(0) => array('key' => 'image_effects_docs', 'type' => OPTION_TYPE_CUSTOM,
										'order' => 9,
										'desc' => '<em>'.gettext('Acknowledgments').'</em>')
									);

		ksort($docs);
		return array_merge($std,$docs);
	}

	/**
	 * handles any custom options
	 *
	 * @param string $option
	 * @param mixed $currentValue
	 */
	function handleOption($option, $currentValue) {
		switch ($option) {
			case 'image_effect_none':
				gettext('No image effects found.');
				break;
			case 'image_effects_docs':
				echo '<em>'.gettext('Effect').'</em>';
				break;
			default:
				echo str_replace('image_effect_','',$option);
				break;
		}
	}

}

$image_effects = array();

function image_effects_getEffect($effect) {
	global $image_effects;
	$effectdata = array();
	$file = getPlugin('image_effects/'.internalToFilesystem($effect).'.txt');
	if (file_exists($file)) {
		$text = file_get_contents($file);
		foreach (array('head','class','extra','validate','common', 'source') as $tag) {
			$i = strpos($text, '<'.$tag.'>');
			if ($i !== false) {
				$j = strpos($text, '</'.$tag.'>');
				if ($j !== false) {
					$effectdata[$tag] = str_replace('%ZENFOLDER%',ZENFOLDER,
															str_replace('%SERVERPATH%',SERVERPATH,
															str_replace('%USER_PLUGIN_FOLDER%',USER_PLUGIN_FOLDER,
															str_replace('%PLUGIN_FOLDER%',PLUGIN_FOLDER,
															str_replace('%WEBPATH%',WEBPATH,
															substr($text,$s=$i+strlen($tag)+2,$j-$s))))));
				}
			}
		}
		if (empty($effectdata)) {
			return array('error'=>sprintf(gettext('<strong>Warning:</strong> <em>%1$s</em> invalid effect definition file'),$effect));
		}
		if (array_key_exists('validate', $effectdata)) {
			$effectdata['error'] = array();
			foreach (explode(';',$effectdata['validate']) as $file) {
				$file = trim($file);
				if ($file && !file_exists($file)) {
					$effectdata['error'][] = basename($file);
				}
			}
			if (count($effectdata['error']) > 0) {
				$effectdata['error'] = sprintf(ngettext('<strong>Warning:</strong> <em>%1$s</em> missing effect component: %2$s','<strong>Warning:</strong><em>%1$s</em> missing effect components: %2$s',count($effectdata['error'])),$effect,implode(', ',$effectdata['error']));
			} else {
				unset($effectdata['error']);
			}
			unset($effectdata['validate']);
		}
		$image_effects[$effect] = $effectdata;
		return $effectdata;
	} else {
			return array('error'=>sprintf(gettext('<strong>Warning:</strong> <em>%1$s</em> missing effect definition file'),$effect));
	}
}

function image_effects_insertClass($html, $effect) {
	global $_image_effects_random;
	if ($effect=='!') {
		$effect = $_image_effects_random;
	}
	$effectData = image_effects_getEffect($effect);
	if (array_key_exists('error', $effectData)) {
		$html .= '<span class="errorbox">'.$effectData['error']."</span>\n";
	} else {
		if (array_key_exists('class', $effectData)) {
			$i = strpos($html,'<img');
			if ($i !== false) {
				$i = strpos($html, 'class=', $i);
				if ($i === false) {
					$i = strpos($html, '/>');
					$html = substr($html, 0, $i).'class="'.$effectData['class'].'" '.substr($html,$i);
				} else {
					$quote = substr($html, $i+6,1);
					$i = strpos($html, $quote, $i+7);
					$html = substr($html, 0, $i).' '.$effectData['class'].substr($html,$i);
				}
			}
		}
		if (array_key_exists('extra', $effectData)) {
			$i = strpos($html, '/>');
			$html = substr($html, 0, $i).' '.$effectData['extra'].' '.substr($html, $i);
		}
	}
	return $html;
}

function image_std_images($html) {
	if ($effect = getOption('image_std_images')) {
		$html = image_effects_insertClass($html,	$effect);
	}
	return $html;
}

function image_custom_images($html, $thumbstandin) {
	if ($thumbstandin) {
		if ($effect = getOption('image_custom_image_thumbs')) {
			$html = image_effects_insertClass($html,	$effect);
		}
	} else {
		if ($effect = getOption('image_custom_images')) {
			$html = image_effects_insertClass($html,	$effect);
		}
	}
	return $html;
}

function image_std_album_thumbs($html) {
	if ($effect = getOption('image_std_album_thumbs')) {
		$html = image_effects_insertClass($html,	$effect);
	}
	return $html;
}

function image_std_image_thumbs($html) {
	if ($effect = getOption('image_std_image_thumbs')) {
		$html = image_effects_insertClass($html,	$effect);
	}
	return $html;
}

function image_custom_album_thumbs($html) {
	if ($effect = getOption('image_custom_album_thumbs')) {
		$html = image_effects_insertClass($html,	$effect);
	}
	return $html;
}

?>