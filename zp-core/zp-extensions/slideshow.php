<?php
/**
 * Supports showing slideshows of images in an album.
 * 	<ul>
 * 		<li>Plugin Option 'slideshow_size' -- Size of the images</li>
 * 		<li>Plugin Option 'slideshow_mode' -- The player to be used</li>
 * 		<li>Plugin Option 'slideshow_effect' -- The cycle effect</li>
 * 		<li>Plugin Option 'slideshow_speed' -- How fast it runs</li>
 * 		<li>Plugin Option 'slideshow_timeout' -- Transition time</li>
 * 		<li>Plugin Option 'slideshow_showdesc' -- Allows the show to display image descriptions</li>
 * 	</ul>
 *
 * The theme files <var>slideshow.php</var>, <var>slideshow.css</var>, and <var>slideshow-controls.png</var> must reside in the theme
 * folder. If you are creating a custom theme, copy these files form the "default" theme of the Zenphoto
 * distribution. Note that the Colorbox mode does not require these files as it is called on your theme's image.php and album.php direclty
 * via the slideshow button. The Colorbox plugin must be enabled and setup for these pages.
 *
 * <b>NOTE:</b> The jQuery Cycle and the jQuery Colorbox modes do not support movie and audio files.
 * In Colorbox mode there will be no slideshow button on the image page if that current image is a movie/audio file.
 *
 * Content macro support:
 * Use [SLIDESHOW <albumname> <true/false for control] for showing a slideshow within image/album descriptions or Zenpage article and page contents.
 * The slideshow size options must fit the space
 * Notes:
 * <ul>
 * 	<li>The slideshow scripts must be enabled for the pages you wish to use it on.</li>
 * 	<li>Use only one slideshow per page to avoid CSS conflicts.</li>
 * 	<li>Also your theme might require extra CSS for this usage, especially the controls.</li>
 * 	<li>This only creates a slideshow in jQuery mode, no matter how the mode is set.</li>
 * </ul>
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard), Don Peterson (dpeterson)
 * @package plugins
 * @subpackage media
 */
$plugin_is_filter = 9 | THEME_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext("Adds a theme function to call a slideshow either based on jQuery (default) or Colorbox.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard), Don Peterson (dpeterson)";

$option_interface = 'slideshow';

global $_zp_gallery, $_zp_gallery_page;
if (getOption('slideshow_' . $_zp_gallery->getCurrentTheme() . '_' . stripSuffix($_zp_gallery_page))) {
	zp_register_filter('theme_head', 'slideshow::header_js');
}
zp_register_filter('content_macro', 'slideshow::macro');


//if (!OFFSET_PATH) {
//}

/**
 * slideshow
 *
 */
class slideshow {

	function slideshow() {
		global $_zp_gallery;
		//setOptionDefault('slideshow_size', '595');
		setOptionDefault('slideshow_width', '595');
		setOptionDefault('slideshow_height', '595');
		setOptionDefault('slideshow_mode', 'jQuery');
		setOptionDefault('slideshow_effect', 'fade');
		setOptionDefault('slideshow_speed', '1000');
		setOptionDefault('slideshow_timeout', '3000');
		setOptionDefault('slideshow_showdesc', '');
		setOptionDefault('slideshow_colorbox_transition', 'fade');
		// incase the flowplayer has not been enabled!!!
		setOptionDefault('slideshow_colorbox_imagetype', 'sizedimage');
		setOptionDefault('slideshow_colorbox_imagetitle', 1);
		if (class_exists('cacheManager')) {
			cacheManager::deleteThemeCacheSizes('slideshow');
			cacheManager::addThemeCacheSize('slideshow', NULL, getOption('slideshow_width'), getOption('slideshow_height'), NULL, NULL, NULL, NULL, NULL, NULL, NULL, true);
			cacheManager::addThemeCacheSize('slideshow', NULL, getOption('slideshow_width'), getOption('slideshow_height'), getOption('slideshow_width'), getOption('slideshow_height'), NULL, NULL, NULL, NULL, NULL, false);
		}
		//	we will presume that themes slideshow script wants to use the slideshow
		foreach (array_keys($_zp_gallery->getThemes()) as $theme) {
			setOptionDefault('slideshow_' . $theme . '_slideshow', 1);
		}
	}

	function getOptionsSupported() {
		$options = array(gettext('Mode')	 => array('key'				 => 'slideshow_mode', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 0,
										'selections' => array(gettext("jQuery Cycle")		 => "jQuery", gettext("jQuery Colorbox") => "colorbox"),
										'desc'			 => gettext('<em>jQuery Cycle</em> for slideshow using the jQuery Cycle plugin<br /><em>jQuery Colorbox</em> for slideshow using Colorbox (Colorbox plugin required).<br />NOTE: The jQuery Colorbox mode is attached to the link the printSlideShowLink() function prints and can neither be called directly nor used on the slideshow.php theme page.')),
						gettext('Speed') => array('key'		 => 'slideshow_speed', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext("Speed of the transition in milliseconds."))
		);
		foreach (getThemeFiles(array('404.php', 'themeoptions.php', 'theme_description.php')) as $theme => $scripts) {
			$list = array();
			foreach ($scripts as $script) {
				$list[$script] = 'slideshow_' . $theme . '_' . stripSuffix($script);
			}
			$opts[$theme] = array('key'				 => 'slideshow_' . $theme . '_scripts', 'type'			 => OPTION_TYPE_CHECKBOX_ARRAY,
							'checkboxes' => $list,
							'desc'			 => gettext('The scripts for which the slideshow is enabled. {Should have been set by the themes!}')
			);
		}
		$options = array_merge($options, $opts);

		switch (getOption('slideshow_mode')) {
			case 'jQuery':
				$options = array_merge($options, array(gettext('Slide width')	 => array('key'		 => 'slideshow_width', 'type'	 => OPTION_TYPE_TEXTBOX,
												'order'	 => 5,
												'desc'	 => gettext("Width of the images in the slideshow.")),
								gettext('Slide height')	 => array('key'		 => 'slideshow_height', 'type'	 => OPTION_TYPE_TEXTBOX,
												'order'	 => 6,
												'desc'	 => gettext("Height of the images in the slideshow.")),
								gettext('Cycle Effect')	 => array('key'				 => 'slideshow_effect', 'type'			 => OPTION_TYPE_SELECTOR,
												'order'			 => 2,
												'selections' => array(gettext('fade')					 => "fade", gettext('shuffle')			 => "shuffle", gettext('zoom')					 => "zoom", gettext('slide X')			 => "slideX", gettext('slide Y')			 => "slideY", gettext('scroll up')		 => "scrollUp", gettext('scroll down')	 => "scrollDown", gettext('scroll left')	 => "scrollLeft", gettext('scroll right')	 => "scrollRight"),
												'desc'			 => gettext("The cycle slide effect to be used.")),
								gettext('Timeout')			 => array('key'		 => 'slideshow_timeout', 'type'	 => OPTION_TYPE_TEXTBOX,
												'order'	 => 3,
												'desc'	 => gettext("Milliseconds between slide transitions (0 to disable auto advance.)")),
								gettext('Description')	 => array('key'		 => 'slideshow_showdesc', 'type'	 => OPTION_TYPE_CHECKBOX,
												'order'	 => 4,
												'desc'	 => gettext("Check if you want to show the image's description below the slideshow."))
				));
				break;
			case 'colorbox':
				$options = array_merge($options, array(gettext('Colorbox transition')	 => array('key'				 => 'slideshow_colorbox_transition', 'type'			 => OPTION_TYPE_SELECTOR,
												'order'			 => 2,
												'selections' => array(gettext('elastic') => "elastic", gettext('fade')		 => "fade", gettext('none')		 => "none"),
												'desc'			 => gettext("The Colorbox transition slide effect to be used.")),
								gettext('Colorbox image type')	 => array('key'				 => 'slideshow_colorbox_imagetype', 'type'			 => OPTION_TYPE_SELECTOR,
												'order'			 => 3,
												'selections' => array(gettext('full image')	 => "fullimage", gettext('sized image') => "sizedimage"),
												'desc'			 => gettext("The image type you wish to use for the Colorbox. If you choose 'sized image' the slideshow width value will be used for the longest side of the image.")),
								gettext('Colorbox image title')	 => array('key'		 => 'slideshow_colorbox_imagetitle', 'type'	 => OPTION_TYPE_CHECKBOX,
												'order'	 => 4,
												'desc'	 => gettext("If the image title should be shown at the bottom of the Colorbox."))
				));
				if (getOption('slideshow_colorbox_imagetype') == 'sizedimage') {
					$options = array_merge($options, array(gettext('Slide width') => array('key'		 => 'slideshow_width', 'type'	 => OPTION_TYPE_TEXTBOX,
													'order'	 => 3.5,
													'desc'	 => gettext("Width of the images in the slideshow."))
					));
				}
				break;
		}
		return $options;
	}

	function handleOption($option, $currentValue) {

	}

	static function getPlayer($album, $controls = false) {
		$albumobj = NULL;
		if (!empty($album)) {
			$albumobj = newAlbum($album, NULL, true);
		}
		if (is_object($albumobj) && $albumobj->loaded) {
			$returnpath = rewrite_path('/' . pathurlencode($albumobj->name) . '/', '/index.php?album=' . urlencode($albumobj->name));
			return slideshow::getShow(false, false, $albumobj, NULL, NULL, NULL, false, false, false, $controls, $returnpath, 0);
		} else {
			return '<div class="errorbox" id="message"><h2>' . gettext('Invalid slideshow album name!') . '</h2></div>';
		}
	}

	static function getShow($heading, $speedctl, $albumobj, $imageobj, $width, $height, $crop, $shuffle, $linkslides, $controls, $returnpath, $imagenumber) {
		global $_zp_gallery, $_zp_gallery_page;
		setOption('slideshow_' . $_zp_gallery->getCurrentTheme() . '_' . stripSuffix($_zp_gallery_page), 1);
		if (!$albumobj->isMyItem(LIST_RIGHTS) && !checkAlbumPassword($albumobj)) {
			return '<div class="errorbox" id="message"><h2>' . gettext('This album is password protected!') . '</h2></div>';
		}
		$slideshow = '';

		$numberofimages = $albumobj->getNumImages();

		// setting the image size
		if (!empty($width) AND !empty($height)) {
			$width = $wrapperwidth = sanitize_numeric($width);
			$height = $wrapperheight = sanitize_numeric($height);
		} else {
			$width = $wrapperwidth = getOption("slideshow_width");
			$height = $wrapperheight = getOption("slideshow_height");
		}
		if ($numberofimages == 0) {
			return '<div class="errorbox" id="message"><h2>' . gettext('No images for the slideshow!') . '</h2></div>';
		}
		$option = getOption("slideshow_mode");
		// jQuery Cycle slideshow config
		// get slideshow data

		$showdesc = getOption("slideshow_showdesc");
		// slideshow display section
		$validtypes = array('jpg', 'jpeg', 'gif', 'png', 'mov', '3gp');
		$slideshow .= '
				<script type="text/javascript">
				// <!-- <![CDATA[
				$(document).ready(function(){
				$(function() {
				var ThisGallery = "' . html_encode($albumobj->getTitle()) . '";
				var ImageList = new Array();
				var TitleList = new Array();
				var DescList = new Array();
				var ImageNameList = new Array();
				var DynTime=(' . (int) getOption("slideshow_timeout") . ');
				';
		$images = $albumobj->getImages(0);
		if ($shuffle) {
			shuffle($images);
		}
		for ($imgnr = 0, $cntr = 0, $idx = $imagenumber; $imgnr < $numberofimages; $imgnr++, $idx++) {
			if (is_array($images[$idx])) {
				$filename = $images[$idx]['filename'];
				$album = newAlbum($images[$idx]['folder']);
				$image = newImage($album, $filename);
			} else {
				$filename = $images[$idx];
				$image = newImage($albumobj, $filename);
			}
			$ext = slideshow::is_valid($filename, $validtypes);
			if ($ext) {
				if ($crop) {
					$img = $image->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, NULL, NULL);
				} else {
					$maxwidth = $width;
					$maxheight = $height;
					getMaxSpaceContainer($maxwidth, $maxheight, $image);
					$img = $image->getCustomImage(NULL, $maxwidth, $maxheight, NULL, NULL, NULL, NULL, NULL, NULL);
				}
				$slideshow .= 'ImageList[' . $cntr . '] = "' . $img . '";' . "\n";
				$slideshow .= 'TitleList[' . $cntr . '] = "' . js_encode($image->getTitle()) . '";' . "\n";
				if ($showdesc) {
					$desc = $image->getDesc();
					$desc = str_replace("\r\n", '<br />', $desc);
					$desc = str_replace("\r", '<br />', $desc);
					$slideshow .= 'DescList[' . $cntr . '] = "' . js_encode($desc) . '";' . "\n";
				} else {
					$slideshow .= 'DescList[' . $cntr . '] = "";' . "\n";
				}
				if ($idx == $numberofimages - 1) {
					$idx = -1;
				}
				$slideshow .= 'ImageNameList[' . $cntr . '] = "' . urlencode($filename) . '";' . "\n";
				$cntr++;
			}
		}

		$slideshow .= "\n";
		$numberofimages = $cntr;
		$slideshow .= '
				var countOffset = ' . $imagenumber . ';
				var totalSlideCount = ' . $numberofimages . ';
				var currentslide = 2;
				function onBefore(curr, next, opts) {
				if (opts.timeout != DynTime) {
				opts.timeout = DynTime;
		}
		if (!opts.addSlide)
		return;
		var currentImageNum = currentslide;
		currentslide++;
		if (currentImageNum == totalSlideCount) {
		opts.addSlide = null;
		return;
		}
		var relativeSlot = (currentslide + countOffset) % totalSlideCount;
		if (relativeSlot == 0) {relativeSlot = totalSlideCount;}
		var htmlblock = "<span class=\"slideimage\"><h4><strong>" + ThisGallery + ":</strong> ";
		htmlblock += TitleList[currentImageNum]  + " (" + relativeSlot + "/" + totalSlideCount + ")</h4>";
		';
		if ($linkslides) {
			if (MOD_REWRITE) {
				$slideshow .= 'htmlblock += "<a href=\"' . pathurlencode($albumobj->name) . '/"+ImageNameList[currentImageNum]+"' . getOption('mod_rewrite_image_suffix') . '\">";';
			} else {
				$slideshow .= 'htmlblock += "<a href=\"index.php?album=' . pathurlencode($albumobj->name) . '&image="+ImageNameList[currentImageNum]+"\">";';
			}
		}
		$slideshow .= ' htmlblock += "<img src=\"" + ImageList[currentImageNum] + "\"/>";';
		if ($linkslides) {
			$slideshow .= ' htmlblock += "</a>";';
		}

		$slideshow .= 'htmlblock += "<p class=\"imgdesc\">" + DescList[currentImageNum] + "</p></span>";';
		$slideshow .= 'opts.addSlide(htmlblock);';
		$slideshow .= '}';

		$slideshow .= '
				function onAfter(curr, next, opts){
				';
		if (!$albumobj->isMyItem(LIST_RIGHTS)) {
			$slideshow .= '
					//Only register at hit count the first time the image is viewed.
					if ($(next).attr("viewed") != 1) {
					$.get("' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/slideshow/slideshow-counter.php?album=' . pathurlencode($albumobj->name) . '&img="+ImageNameList[opts.currSlide]);
					$(next).attr("viewed", 1 );
				}
				';
		}
		$slideshow .='}';
		$slideshow .='
				$("#slides").cycle({
				fx:     "' . getOption("slideshow_effect") . '",
				speed:   "' . getOption("slideshow_speed") . '",
				timeout: DynTime,
				next:   "#next",
				prev:   "#prev",
				cleartype: 1,
				before: onBefore,
				after: onAfter
		});

		$("#speed").change(function () {
		DynTime = this.value;
		return false;
		});

		$("#pause").click(function() { $("#slides").cycle("pause"); return false; });
		$("#play").click(function() { $("#slides").cycle("resume"); return false; });
		});

		});	// Documentready()
		// ]]> -->
		</script>
		<div id="slideshow" style="height:' . ($wrapperheight + 40) . 'px; width:' . $wrapperwidth . 'px;">
		';
		// 7/21/08dp
		if ($speedctl) {
			$slideshow .= '<div id="speedcontrol">'; // just to keep it away from controls for sake of this demo
			$minto = getOption("slideshow_speed");
			while ($minto % 500 != 0) {
				$minto += 100;
				if ($minto > 10000) {
					break;
				} // emergency bailout!
			}
			$dflttimeout = (int) getOption("slideshow_timeout");
			/* don't let min timeout = speed */
			$thistimeout = ($minto == getOption("slideshow_speed") ? $minto + 250 : $minto);
			$slideshow .= 'Select Speed: <select id="speed" name="speed">';
			while ($thistimeout <= 60000) { // "around" 1 minute :)
				$slideshow .= "<option value=$thistimeout " . ($thistimeout == $dflttimeout ? " selected='selected'>" : ">") . round($thistimeout / 1000, 1) . " sec</option>";
				/* put back timeout to even increments of .5 */
				if ($thistimeout % 500 != 0) {
					$thistimeout -= 250;
				}
				$thistimeout += ($thistimeout < 1000 ? 500 : ($thistimeout < 10000 ? 1000 : 5000));
			}
			$slideshow .= '</select> </div>';
		}
		if ($controls) {
			$slideshow .= '
					<div id="controls">
					<div>
					<a href="#" id="prev" title="' . gettext("Previous") . '"></a>
					<a href="' . html_encode($returnpath) . '" id="stop" title="' . gettext("Stop and return to album or image page") . '"></a>
					<a href="#" id="pause" title="' . gettext("Pause (to stop the slideshow without returning)") . '"></a>
					<a href="#" id="play" title="' . gettext("Play") . '"></a>
					<a href="#" id="next" title="' . gettext("Next") . '"></a>
					</div>
					</div>
					';
		}
		$slideshow .= '
				<div id="slides" class="pics">
				';

		if ($cntr > 1)
			$cntr = 1;
		for ($imgnr = 0, $idx = $imagenumber; $imgnr <= $cntr; $idx++) {
			if ($idx >= $numberofimages) {
				$idx = 0;
			}
			if (is_array($images[$idx])) {
				$folder = $images[$idx]['folder'];
				$dalbum = newAlbum($folder);
				$filename = $images[$idx]['filename'];
				$image = newImage($dalbum, $filename);
				$imagepath = FULLWEBPATH . ALBUM_FOLDER_EMPTY . $folder . "/" . $filename;
			} else {
				$folder = $albumobj->name;
				$filename = $images[$idx];
				//$filename = $animage;
				$image = newImage($albumobj, $filename);
				$imagepath = FULLWEBPATH . ALBUM_FOLDER_EMPTY . $folder . "/" . $filename;
			}
			$ext = slideshow::is_valid($filename, $validtypes);
			if ($ext) {
				$imgnr++;
				$slideshow .= '<span class="slideimage"><h4><strong>' . $albumobj->getTitle() . gettext(":") . '</strong> ' . $image->getTitle() . ' (' . ($idx + 1) . '/' . $numberofimages . ')</h4>';

				if ($ext == "3gp") {
					$slideshow .= '</a>
							<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="352" height="304" codebase="http://www.apple.com/qtactivex/qtplugin.cab">
							<param name="src" value="' . pathurlencode(internalToFilesystem($imagepath)) . '"/>
							<param name="autoplay" value="false" />
							<param name="type" value="video/quicktime" />
							<param name="controller" value="true" />
							<embed src="' . pathurlencode(internalToFilesystem($imagepath)) . '" width="352" height="304" autoplay="false" controller"true" type="video/quicktime"
							pluginspage="http://www.apple.com/quicktime/download/" cache="true"></embed>
							</object>
							<a>';
				} elseif ($ext == "mov") {
					$slideshow .= '</a>
							<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="640" height="496" codebase="http://www.apple.com/qtactivex/qtplugin.cab">
							<param name="src" value="' . pathurlencode(internalToFilesystem($imagepath)) . '"/>
							<param name="autoplay" value="false" />
							<param name="type" value="video/quicktime" />
							<param name="controller" value="true" />
							<embed src="' . pathurlencode(internalToFilesystem($imagepath)) . '" width="640" height="496" autoplay="false" controller"true" type="video/quicktime"
							pluginspage="http://www.apple.com/quicktime/download/" cache="true"></embed>
							</object>
							<a>';
				} else {
					if ($linkslides)
						$slideshow .= '<a href="' . html_encode($image->getImageLink()) . '">';
					if ($crop) {
						$img = $image->getCustomImage(NULL, $width, $height, $width, $height, NULL, NULL, NULL, NULL);
					} else {
						$maxwidth = $width;
						$maxheight = $height;
						getMaxSpaceContainer($maxwidth, $maxheight, $image);
						$img = $image->getCustomImage(NULL, $maxwidth, $maxheight, NULL, NULL, NULL, NULL, NULL, NULL);
					}
					$slideshow .= '<img src="' . html_encode(pathurlencode($img)) . '" alt="" />';
					if ($linkslides)
						$slideshow .= '</a>';
				}
				if ($showdesc) {
					$desc = $image->getDesc();
					$desc = str_replace("\r\n", '<br />', $desc);
					$desc = str_replace("\r", '<br />', $desc);
					$slideshow .= '<p class="imgdesc">' . $desc . '</p>';
				}
				$slideshow .= '</span>';
			}
		}
		$slideshow .= '
		</div>
		</div>
		';
		return $slideshow;
	}

	static function macro($macros) {
		$macros['SLIDESHOW'] = array(
						'class'	 => 'function',
						'params' => array('string', 'bool*'),
						'value'	 => 'slideshow::getPlayer',
						'owner'	 => 'slideshow',
						'desc'	 => gettext('provide the album name as %1 and (optionally) <code>true</code> (or <code>false</code>) as %2 to show (hide) controls. Hiding the controls is the default.')
		);
		return $macros;
	}

	static function header_js() {
		$theme = getCurrentTheme();
		$css = SERVERPATH . '/' . THEMEFOLDER . '/' . internalToFilesystem($theme) . '/slideshow.css';
		if (file_exists($css)) {
			$css = WEBPATH . '/' . THEMEFOLDER . '/' . $theme . '/slideshow.css';
		} else {
			$css = WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/slideshow/slideshow.css';
		}
		?>
		<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/slideshow/jquery.cycle.all.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $css; ?>" />
		<?php
	}

	/**
	 * Returns the file extension if the item passed is displayable by the player
	 *
	 * @param mixed $image either an image object or the filename of an image.
	 * @param array $valid_types list of the types we will accept
	 * @return string;
	 */
	static function is_valid($image, $valid_types) {
		if (is_object($image))
			$image = $image->filename;
		$ext = getSuffix($image);
		if (in_array($ext, $valid_types)) {
			return $ext;
		}
		return false;
	}

}

$slideshow_instance = 0;

/**
 * Prints a link to call the slideshow (not shown if there are no images in the album)
 * To be used on album.php and image.php
 * A CSS id names 'slideshowlink' is attached to the link so it can be directly styled.
 *
 * If the mode is set to "jQuery Colorbox" and the Colorbox plugin is enabled this link starts a Colorbox slideshow
 * from a hidden HTML list of all images in the album. On album.php it starts with the first always, on image.php with the current image.
 *
 * @param string $linktext Text for the link
 * @param string $linkstyle Style of Text for the link
 */
function printSlideShowLink($linktext = NULL, $linkstyle = Null) {
	global $_zp_gallery, $_zp_current_image, $_zp_current_album, $_zp_current_search, $slideshow_instance, $_zp_gallery_page;
	if (is_null($linktext)) {
		$linktext = gettext('View Slideshow');
	}
	if (empty($_GET['page'])) {
		$pagenr = 1;
	} else {
		$pagenr = sanitize_numeric($_GET['page']);
	}
	$slideshowhidden = '';
	$numberofimages = 0;
	if (in_context(ZP_SEARCH)) {
		$imagenumber = '';
		$imagefile = '';
		$albumnr = 0;
		$slideshowlink = rewrite_path('/' . _PAGE_ . '/slideshow', "index.php?p=slideshow");
		$slideshowhidden = '<input type="hidden" name="preserve_search_params" value="' . html_encode($_zp_current_search->getSearchParams()) . '" />';
	} else {
		if (in_context(ZP_IMAGE)) {
			$imagenumber = imageNumber();
			$imagefile = $_zp_current_image->filename;
		} else {
			$imagenumber = '';
			$imagefile = '';
		}
		if (in_context(ZP_SEARCH_LINKED)) {
			$albumnr = -$_zp_current_album->getID();
			$slideshowhidden = '<input type="hidden" name="preserve_search_params" value="' . html_encode($_zp_current_search->getSearchParams()) . '" />';
		} else {
			$albumnr = $_zp_current_album->getID();
		}
		if ($albumnr) {
			$slideshowlink = rewrite_path(pathurlencode($_zp_current_album->getFolder()) . '/' . _PAGE_ . '/slideshow', "index.php?p=slideshow&amp;album=" . urlencode($_zp_current_album->getFolder()));
		} else {
			$slideshowlink = rewrite_path('/' . _PAGE_ . '/slideshow', "index.php?p=slideshow");
			$slideshowhidden = '<input type="hidden" name="favorites_page" value="1" />';
		}
	}
	$numberofimages = getNumImages();
	$option = getOption('slideshow_mode');
	switch ($option) {
		case 'jQuery':
			if ($numberofimages > 1) {
				?>
				<form name="slideshow_<?php echo $slideshow_instance; ?>" method="post"	action="<?php echo $slideshowlink; ?>">
					<?php echo $slideshowhidden; ?>
					<input type="hidden" name="pagenr" value="<?php echo html_encode($pagenr); ?>" />
					<input type="hidden" name="albumid" value="<?php echo $albumnr; ?>" />
					<input type="hidden" name="numberofimages" value="<?php echo $numberofimages; ?>" />
					<input type="hidden" name="imagenumber" value="<?php echo $imagenumber; ?>" />
					<input type="hidden" name="imagefile" value="<?php echo html_encode($imagefile); ?>" />
					<?php if (!empty($linkstyle)) echo '<p style="' . $linkstyle . '">'; ?>
					<a class="slideshowlink" id="slideshowlink_<?php echo $slideshow_instance; ?>" 	href="javascript:document.slideshow_<?php echo $slideshow_instance; ?>.submit()"><?php echo $linktext; ?></a>
					<?php if (!empty($linkstyle)) echo '</p>'; ?>
				</form>
				<?php
			}
			$slideshow_instance++;
			break;
		case 'colorbox':
			$theme = $_zp_gallery->getCurrentTheme();
			$script = stripSuffix($_zp_gallery_page);
			if (!getOption('colorbox_' . $theme . '_' . $script)) {
				setOptionDefault('colorbox_' . $theme . '_' . $script, 1);
				$themes = $_zp_gallery->getThemes();
				?>
				<div class="errorbox"><?php printf(gettext('Slideshow not available because colorbox is not enabled on %1$s <em>%2$s</em> pages.'), $themes[$theme]['name'], $script); ?></div>
				<?php
				break;
			}
			if ($numberofimages > 1) {
				if ((in_context(ZP_SEARCH_LINKED) && !in_context(ZP_ALBUM_LINKED)) || in_context(ZP_SEARCH) && is_null($_zp_current_album)) {
					$images = $_zp_current_search->getImages(0);
				} else {
					$images = $_zp_current_album->getImages(0);
				}
				$count = '';
				?>
				<script type="text/javascript">
					$(document).ready(function() {
						$("a[rel='slideshow']").colorbox({
							slideshow: true,
							loop: true,
							transition: '<?php echo getOption('slideshow_colorbox_transition'); ?>',
							slideshowSpeed: <?php echo getOption('slideshow_speed'); ?>,
							slideshowStart: '<?php echo gettext("start slideshow"); ?>',
							slideshowStop: '<?php echo gettext("stop slideshow"); ?>',
							previous: '<?php echo gettext("prev"); ?>',
							next: '<?php echo gettext("next"); ?>',
							close: '<?php echo gettext("close"); ?>',
							current: '<?php printf(gettext('image %1$s of %2$s'), '{current}', '{total}'); ?>',
							maxWidth: '98%',
							maxHeight: '98%',
							photo: true
						});
					});
				</script>
				<?php
				foreach ($images as $image) {
					if (is_array($image)) {
						$suffix = getSuffix($image['filename']);
					} else {
						$suffix = getSuffix($image);
					}
					$suffixes = array('jpg', 'jpeg', 'gif', 'png');
					if (in_array($suffix, $suffixes)) {
						$count++;
						if (is_array($image)) {
							$albobj = newAlbum($image['folder']);
							$imgobj = newImage($albobj, $image['filename']);
						} else {
							$imgobj = newImage($_zp_current_album, $image);
						}
						if (in_context(ZP_SEARCH_LINKED) || $_zp_gallery_page != 'image.php') {
							if ($count == 1) {
								$style = '';
							} else {
								$style = ' style="display:none"';
							}
						} else {
							if ($_zp_current_image->filename == $image) {
								$style = '';
							} else {
								$style = ' style="display:none"';
							}
						}
						switch (getOption('slideshow_colorbox_imagetype')) {
							case 'fullimage':
								$imagelink = getFullImageURL($imgobj);
								break;
							case 'sizedimage':
								$imagelink = $imgobj->getCustomImage(getOption("slideshow_width"), NULL, NULL, NULL, NULL, NULL, NULL, false, NULL);
								break;
						}
						$imagetitle = '';
						if (getOption('slideshow_colorbox_imagetitle')) {
							$imagetitle = html_encode(strip_tags($imgobj->getTitle()));
						}
						?>
						<a href="<?php echo html_encode(pathurlencode($imagelink)); ?>" rel="slideshow"<?php echo $style; ?> title="<?php echo $imagetitle; ?>"><?php echo $linktext; ?></a>
						<?php
					}
				}
			}
			break;
	}
}

/**
 * Gets the slideshow using the {@link http://http://www.malsup.com/jquery/cycle/  jQuery plugin Cycle}
 *
 * NOTE: The jQuery mode does not support movie and audio files anymore. If you need to show them please use the Flash mode.
 * Also note that this function is not used for the Colorbox mode!
 *
 * @param bool $heading set to true (default) to emit the slideshow breadcrumbs in flash mode
 * @param bool $speedctl controls whether an option box for controlling transition speed is displayed
 * @param obj $albumobj The object of the album to show the slideshow of. If set this overrides the POST data of the printSlideShowLink()
 * @param obj $imageobj The object of the image to start the slideshow with. If set this overrides the POST data of the printSlideShowLink(). If not set the slideshow starts with the first image of the album.
 * @param int $width The width of the images (jQuery mode). If set this overrides the size the slideshow_width plugin option that otherwise is used.
 * @param int $height The heigth of the images (jQuery mode). If set this overrides the size the slideshow_height plugin option that otherwise is used.
 * @param bool $crop Set to true if you want images cropped width x height (jQuery mode only)
 * @param bool $shuffle Set to true if you want random (shuffled) order
 * @param bool $linkslides Set to true if you want the slides to be linked to their image pages (jQuery mode only)
 * @param bool $controls Set to true (default) if you want the slideshow controls to be shown (might require theme CSS changes if calling outside the slideshow.php page) (jQuery mode only)
 *
 */

/**
 * Prints the slideshow using the {@link http://http://www.malsup.com/jquery/cycle/  jQuery plugin Cycle}
 *
 * Two ways to use:
 * a) Use on your theme's slideshow.php page and called via printSlideShowLink():
 * If called from image.php it starts with that image, called from album.php it starts with the first image (jQuery only)
 * To be used on slideshow.php only and called from album.php or image.php.
 *
 * b) Calling directly via printSlideShow() function (jQuery mode)
 * Place the printSlideShow() function where you want the slideshow to appear and set create an album object for $albumobj and if needed an image object for $imageobj.
 * The controls are disabled automatically.
 *
 * NOTE: The jQuery mode does not support movie and audio files anymore. If you need to show them please use the Flash mode.
 * Also note that this function is not used for the Colorbox mode!
 *
 * @param bool $heading set to true (default) to emit the slideshow breadcrumbs in flash mode
 * @param bool $speedctl controls whether an option box for controlling transition speed is displayed
 * @param obj $albumobj The object of the album to show the slideshow of. If set this overrides the POST data of the printSlideShowLink()
 * @param obj $imageobj The object of the image to start the slideshow with. If set this overrides the POST data of the printSlideShowLink(). If not set the slideshow starts with the first image of the album.
 * @param int $width The width of the images (jQuery mode). If set this overrides the size the slideshow_width plugin option that otherwise is used.
 * @param int $height The heigth of the images (jQuery mode). If set this overrides the size the slideshow_height plugin option that otherwise is used.
 * @param bool $crop Set to true if you want images cropped width x height (jQuery mode only)
 * @param bool $shuffle Set to true if you want random (shuffled) order
 * @param bool $linkslides Set to true if you want the slides to be linked to their image pages (jQuery mode only)
 * @param bool $controls Set to true (default) if you want the slideshow controls to be shown (might require theme CSS changes if calling outside the slideshow.php page) (jQuery mode only)
 *
 */
function printSlideShow($heading = true, $speedctl = false, $albumobj = NULL, $imageobj = NULL, $width = NULL, $height = NULL, $crop = false, $shuffle = false, $linkslides = false, $controls = true) {
	global $_myFavorites, $_zp_conf_vars;
	if (!isset($_POST['albumid']) AND !is_object($albumobj)) {
		return '<div class="errorbox" id="message"><h2>' . gettext('Invalid linking to the slideshow page.') . '</h2></div>';
	}
	//getting the image to start with
	if (!empty($_POST['imagenumber']) AND !is_object($imageobj)) {
		$imagenumber = sanitize_numeric($_POST['imagenumber']) - 1; // slideshows starts with 0, but zp with 1.
	} elseif (is_object($imageobj)) {
		$imagenumber = $imageobj->getIndex();
	} else {
		$imagenumber = 0;
	}
	// set pagenumber to 0 if not called via POST link
	if (isset($_POST['pagenr'])) {
		$pagenumber = sanitize_numeric($_POST['pagenr']);
	} else {
		$pagenumber = 1;
	}
	// getting the number of images
	if (!empty($_POST['numberofimages'])) {
		$numberofimages = sanitize_numeric($_POST['numberofimages']);
	} elseif (is_object($albumobj)) {
		$numberofimages = $albumobj->getNumImages();
	} else {
		$numberofimages = 0;
	}
	if ($imagenumber < 2 || $imagenumber > $numberofimages) {
		$imagenumber = 0;
	}
	//getting the album to show
	if (!empty($_POST['albumid']) && !is_object($albumobj)) {
		$albumid = sanitize_numeric($_POST['albumid']);
	} elseif (is_object($albumobj)) {
		$albumid = $albumobj->getID();
	} else {
		$albumid = 0;
	}

	if (isset($_POST['preserve_search_params'])) { // search page
		$search = new SearchEngine();
		$params = sanitize($_POST['preserve_search_params']);
		$search->setSearchParams($params);
		$searchwords = $search->getSearchWords();
		$searchdate = $search->getSearchDate();
		$searchfields = $search->getSearchFields(true);
		$page = $search->page;
		$returnpath = getSearchURL($searchwords, $searchdate, $searchfields, $page);
		$albumobj = new AlbumBase(NULL, false);
		$albumobj->setTitle(gettext('Search'));
		$albumobj->images = $search->getImages(0);
	} else {
		if (isset($_POST['favorites_page'])) {
			$albumobj = $_myFavorites;
			$returnpath = rewrite_path(favorites::getFavorites_link() . '/' . $pagenumber, FULLWEBPATH . '/index.php?p=favorites' . '&page=' . $pagenumber);
		} else {
			$albumq = query_single_row("SELECT title, folder FROM " . prefix('albums') . " WHERE id = " . $albumid);
			$albumobj = newAlbum($albumq['folder']);
			if (empty($_POST['imagenumber'])) {
				$returnpath = rewrite_path('/' . pathurlencode($albumobj->name) . '/' . _PAGE_ . '/' . $pagenumber, '/index.php?album=' . urlencode($albumobj->name) . '&page=' . $pagenumber);
			} else {
				$returnpath = rewrite_path('/' . pathurlencode($albumobj->name) . '/' . rawurlencode(sanitize($_POST['imagefile'])) . getOption('mod_rewrite_image_suffix'), '/index.php?album=' . urlencode($albumobj->name) . '&image=' . urlencode($_POST['imagefile']));
			}
		}
	}


	echo slideshow::getShow($heading, $speedctl, $albumobj, $imageobj, $width, $height, $crop, $shuffle, $linkslides, $controls, $returnpath, $imagenumber);
}
?>
