<?php
/**
 * Supports showing slideshows of images in an album.
 *
 * 	Plugin Option 'slideshow_size' -- Size of the images
 *	Plugin Option 'slideshow_mode' -- The player to be used
 *	Plugin Option 'slideshow_effect' -- The cycle effect
 *	Plugin Option 'slideshow_speed' -- How fast it runs
 *	Plugin Option 'slideshow_timeout' -- Transition time
 *	Plugin Option 'slideshow_showdesc' -- Allows the show to display image descriptions
 *
 * The theme files 'slideshow.php', 'slideshow.css', and 'slideshow-controls.png' must reside in the theme
 * folder. If you are creating a custom theme, copy these files form the "default" theme of the Zenphoto
 * distribution. Note that the Colorbox mode does not require these files as it is called on your theme's image.php and album.php direclty
 * via the slideshow button. The Colorbox plugin must be enabled and setup for these pages.
 *
 * NOTE: The jQuery Cycle and the jQuery Colorbox modes do not support movie and audio files. If you need to show them please use the Flash mode.
 * In Colorbox mode there will be no slideshow button on the image page if that current image is a movie/audio file.
 *
 *
 * @author Malte Müller (acrylian), Stephen Billard (sbillard), Don Peterson (dpeterson)
 * @package plugins
 */

$plugin_description = gettext("Adds a theme function to call a slideshow either based on jQuery (default) or Flash using Flowplayer3 if installed.");
$plugin_author = "Malte Müller (acrylian), Stephen Billard (sbillard), Don Peterson (dpeterson)";
$plugin_version = '1.4.1';
$option_interface = 'slideshowOptions';

/**
 * Plugin option handling class
 *
 */
class slideshowOptions {

	function slideshowOptions() {
		//setOptionDefault('slideshow_size', '595');
		setOptionDefault('slideshow_width', '595');
		setOptionDefault('slideshow_height', '595');
		setOptionDefault('slideshow_watermark', '');
		setOptionDefault('slideshow_mode', 'jQuery');
		setOptionDefault('slideshow_effect', 'fade');
		setOptionDefault('slideshow_speed', '1000');
		setOptionDefault('slideshow_timeout', '3000');
		setOptionDefault('slideshow_showdesc', '');
		setOptionDefault('slideshow_colorbox_transition', 'fade');
		// incase the flowplayer has not been enabled!!!
		setOptionDefault('slideshow_flow_player_width', '640');
		setOptionDefault('slideshow_flow_player_height', '480');
		setOptionDefault('slideshow_colorbox_imagetype', 'sizedimage');
	}


	function getOptionsSupported() {
		return array(	gettext('Slide width') => array('key' => 'slideshow_width', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Width of the images in the slideshow. <em>[jQuery Cycle mode option]</em><br />If empty the theme options <em>image size</em> is used.")),
									gettext('Slide height') => array('key' => 'slideshow_height', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Height of the images in the slideshow. <em>[jQuery Cycle mode option]</em><br />If empty the theme options <em>image size</em> is used.")),
									gettext('Watermark') => array('key' => 'slideshow_watermark', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("Check if you want to use your watermark on the images <em>[jQuery Cycle mode option]</em>.")),
									gettext('Mode') => array('key' => 'slideshow_mode', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext("jQuery Cycle")=>"jQuery", gettext("jQuery Colorbox")=>"colorbox", gettext("Flowplayer3 (flash)")=>"flash"),
										'desc' => gettext("<ol><li><em>jQuery Cycle</em> for slideshow using the jQuery Cycle plugin <em></li><li>jQuery Colorbox</em> for slideshow using Colorbox (Colorbox plugin required).</li><li><em>flash</em> for flash based slideshow using Flowplayer3.</li></ol> NOTE: The jQuery Colorbox mode is attached to the link the printSlideShowLink() function prints and can neither be called directly nor used on the slideshow.php theme page.")),
									gettext('Cycle Effect') => array('key' => 'slideshow_effect', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('fade')=>"fade", gettext('shuffle')=>"shuffle", gettext('zoom')=>"zoom", gettext('slide X')=>"slideX", gettext('slide Y')=>"slideY", gettext('scroll up')=>"scrollUp", gettext('scroll down')=>"scrollDown", gettext('scroll left')=>"scrollLeft", gettext('scroll right')=>"scrollRight"),
										'desc' => gettext("The cycle slide effect to be used. <em>[jQuery Cycle mode option]</em>")),
									gettext('Colorbox transition') => array('key' => 'slideshow_colorbox_transition', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('elastic')=>"elastic", gettext('fade')=>"fade", gettext('none')=>"none"),
										'desc' => gettext("The Colorbox transition slide effect to be used. <em>[jQuery Colorbox mode option]</em>")),
									gettext('Colorbox image type') => array('key' => 'slideshow_colorbox_imagetype', 'type' => OPTION_TYPE_SELECTOR,
										'selections' => array(gettext('full image')=>"fullimage", gettext('sized image')=>"sizedimage"),
										'desc' => gettext("The image type you wish to use for the Colorbox. If you choose 'sized image' the slideshow width value will be used for the longest side of the image <em>[jQuery Colorbox mode option]</em>")),
									gettext('Speed') => array('key' => 'slideshow_speed', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Speed of the transition in milliseconds.")),
									gettext('Timeout') => array('key' => 'slideshow_timeout', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Milliseconds between slide transitions (0 to disable auto advance.) <em>[jQuery Cycle mode option]</em>")),
									gettext('Description') => array('key' => 'slideshow_showdesc', 'type' => OPTION_TYPE_CHECKBOX,
										'desc' => gettext("Check if you want to show the image's description below the slideshow <em>[jQuery Cycle mode option]</em>.")),
									gettext('flow player width') => array('key' => 'slideshow_flow_player_width', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Width of the Flowplayer display for the slideshow <em>(Flash mode)</em>.")),
									gettext('flow player height') => array('key' => 'slideshow_flow_player_height', 'type' => OPTION_TYPE_TEXTBOX,
										'desc' => gettext("Height of the Flowplayer display for the slideshow <em>(Flash mode)</em>."))
		);
	}

	function handleOption($option, $currentValue) {
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
function printSlideShowLink($linktext='', $linkstyle='') {
	global $_zp_gallery, $_zp_current_image, $_zp_current_album, $_zp_current_search, $slideshow_instance, $_zp_gallery_page;
	if(empty($_GET['page'])) {
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
		$slideshowlink = rewrite_path("/page/slideshow","index.php?p=slideshow");
		$slideshowhidden = '<input type="hidden" name="preserve_search_params" value="'.html_encode($_zp_current_search->getSearchParams()).'" />';
	} else {
		if(in_context(ZP_IMAGE)) {
			$imagenumber = imageNumber();
			$imagefile = $_zp_current_image->filename;
		} else {
			$imagenumber = '';
			$imagefile = '';
		}
		if (in_context(ZP_SEARCH_LINKED)) {
			$albumnr = -getAlbumID();
			$slideshowhidden = '<input type="hidden" name="preserve_search_params" value="'.html_encode($_zp_current_search->getSearchParams()).'" />';
		} else {
			$albumnr = getAlbumID();
		}
		$slideshowlink = rewrite_path(pathurlencode($_zp_current_album->getFolder())."/page/slideshow","index.php?p=slideshow&amp;album=".urlencode($_zp_current_album->getFolder()));
	}
	$numberofimages = getNumImages();
	$option = getOption('slideshow_mode');
	switch($option) {
		case 'jQuery':
	 	case 'flash':
			if($numberofimages > 1) {
				?>
				<form name="slideshow_<?php echo $slideshow_instance; ?>" method="post"	action="<?php echo $slideshowlink; ?>">
					<?php echo $slideshowhidden; ?>
					<input type="hidden" name="pagenr" value="<?php echo html_encode($pagenr);?>" />
					<input type="hidden" name="albumid" value="<?php echo $albumnr;?>" />
					<input type="hidden" name="numberofimages" value="<?php echo $numberofimages;?>" />
					<input type="hidden" name="imagenumber" value="<?php echo $imagenumber;?>" />
					<input type="hidden" name="imagefile" value="<?php echo html_encode($imagefile);?>" />
					<?php if (!empty($linkstyle)) echo '<p style="'.$linkstyle.'">';?>
					<a id="slideshowlink_<?php echo $slideshow_instance; ?>" 	href="javascript:document.slideshow_<?php echo $slideshow_instance; ?>.submit()"><?php echo $linktext; ?></a>
					<?php if (!empty($linkstyle)) echo '</p>';?>
				</form>
				<?php
			}
			$slideshow_instance++;
			break;
		case 'colorbox':
			$theme = $_zp_gallery->getCurrentTheme();
			$script = stripSuffix($_zp_gallery_page);
			if (!getOption('colorbox_'.$theme.'_'.$script)) {
				$themes =$_zp_gallery->getThemes();
				?>
				<p class="errorbox"><?php printf(gettext('Slideshow not available because colorbox is not enabled on %1$s <em>%2$s</em> pages.'),$themes[$theme]['name'],$script); ?></p>
				<?php
				break;
			}
			if($numberofimages > 1) {
				if(in_context(ZP_SEARCH) || in_context(ZP_SEARCH_LINKED)) {
					$images = $_zp_current_search->getImages(0);
				} else {
					$images = $_zp_current_album->getImages(0);
				}
				$count = '';
				?>
				<script type="text/javascript">
					$(document).ready(function(){
						$("a[rel='slideshow']").colorbox({
							slideshow: true,
							loop: true,
							transition: '<?php echo getOption('slideshow_colorbox_transition'); ?>',
							slideshowSpeed: <?php echo getOption('slideshow_speed'); ?>,
							slideshowStart: '<?php echo gettext("start slideshow"); ?>',
							slideshowStop: '<?php echo gettext("stop slideshow"); ?>',
							previous: '<?php echo gettext("previous"); ?>',
							next: '<?php echo gettext("next"); ?>',
							close: '<?php echo gettext("close"); ?>',
							maxWidth:'98%',
							maxHeight:'98%',
							photo: true
						});
					});
				</script>
				<?php
				foreach($images as $image) {
					if(is_array($image)) {
						$suffix = getSuffix($image['filename']);
					} else {
						$suffix = getSuffix($image);
					}
					$suffixes = array('jpg','jpeg','gif','png');
					if(in_array($suffix,$suffixes)) {
						$count++;
						if(is_array($image)) {
							$albobj = new Album($_zp_gallery,$image['folder']);
							$imgobj = newImage($albobj,$image['filename']);
						} else {
							$imgobj = newImage($_zp_current_album,$image);
						}
						if($_zp_gallery_page == 'image.php' || in_context(ZP_SEARCH_LINKED)) {
							if(in_context(ZP_SEARCH_LINKED)) {
								if($count == 1) {
									$style = '';
								} else {
									$style = ' style="display:none"';
								}
							} else {
								if($_zp_current_image->filename == $image) {
									$style = '';
								} else {
									$style = ' style="display:none"';
								}
							}
						} elseif ($_zp_gallery_page == 'album.php' || $_zp_gallery_page == 'search.php') {
							if($count == 1) {
								$style = '';
							} else {
								$style = ' style="display:none"';
							}
						}
						switch(getOption('slideshow_colorbox_imagetype')) {
							case 'fullimage':
								$imagelink = $imgobj->getFullImage();
								break;
							case 'sizedimage':
								$imagelink = $imgobj->getSizedImage(getOption("slideshow_width"));
								break;
						}
						?>
						<p>
						<a href="<?php echo html_encode($imagelink); ?>" rel="slideshow"<?php echo $style; ?> title="<?php echo html_encode(strip_tags($imgobj->getTitle())); ?>"><?php echo $linktext; ?></a>
						</p>
						<?php
					}
				}
			}
			break;
	}
}

/**
 * Returns the file extension if the item passed is displayable by the player
 *
 * @param mixed $image either an image object or the filename of an image.
 * @param array $valid_types list of the types we will accept
 * @return string;
 */
function is_valid($image, $valid_types) {
	if (is_object($image)) $image = $image->filename;
	$ext = getSuffix($image);
	if (in_array($ext, $valid_types)) {
		return $ext;
	}
	return false;
}

/**
 * Prints the slideshow using the jQuery plugin Cycle (http://http://www.malsup.com/jquery/cycle/)
 * or Flash based using Flowplayer3 http://flowplayer.org if installed
 *
 * Two ways to use:
 * a) Use on your theme's slideshow.php page and called via printSlideShowLink():
 * If called from image.php it starts with that image, called from album.php it starts with the first image (jQuery only)
 * To be used on slideshow.php only and called from album.php or image.php.
 *
 * b) Calling directly via printSlideShow() function (jQuery mode recommended)
 * Call printSlideShowJS() function in the head section of the theme page you want to use the slideshow on.
 * Them place the printSlideShow() function where you want the slideshow to appear and set $albumobj and if needed $imageobj.
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
 * */
function printSlideShow($heading = true, $speedctl = false, $albumobj = "", $imageobj = "", $width = "", $height = "",$crop=false,$shuffle=false) {
	if (!isset($_POST['albumid']) AND !is_object($albumobj)) {
		echo "<div class=\"errorbox\" id=\"message\"><h2>".gettext("Invalid linking to the slideshow page.")."</h2></div>";
		echo "</div></body></html>";
		exit();
	}
	global $_zp_flash_player, $_zp_current_image, $_zp_current_album, $_zp_gallery;
  $imagenumber = 0;
	//getting the image to start with
	if(!empty($_POST['imagenumber']) AND !is_object($imageobj)) {
		$imagenumber = sanitize_numeric($_POST['imagenumber'])-1; // slideshows starts with 0, but zp with 1.
	} elseif (is_object($imageobj)) {
		makeImageCurrent($imageobj);
		$imagenumber = (imageNumber()-1);
	}
  // set pagenumber to 0 if not called via POST link
	if(isset($_POST['pagenr'])) {
		$pagenumber = sanitize_numeric($_POST['pagenr']);
	} else {
		$pagenumber = 0;
	}
	// getting the number of images
	if(!empty($_POST['numberofimages'])) {
		$numberofimages = sanitize_numeric($_POST['numberofimages']);
	} elseif (is_object($albumobj)) {
		$numberofimages = $albumobj->getNumImages();
	} else {
		$numberofimages = 0;
	}
	if($imagenumber < 2 || $imagenumber > $numberofimages) {
		$imagenumber = 0;
	}
	//getting the album to show
	if(!empty($_POST['albumid']) AND !is_object($albumobj)) {
		$albumid = sanitize_numeric($_POST['albumid']);
	} elseif(is_object($albumobj)) {
		$albumid = $albumobj->id;
	} else {
		$albumid = -1;
	}

	// setting the image size
	if (!empty($width) AND !empty($height)) {
		$width = sanitize_numeric($width);
		$height = sanitize_numeric($height);
	} else {
		$width = getOption("slideshow_width");
		$height = getOption("slideshow_height");
	}
	if($numberofimages == 0) {
		return NULL;
	}
	$option = getOption("slideshow_mode");
	// jQuery Cycle slideshow config
	// get slideshow data
	$gallery = new Gallery();
	if ($albumid <= 0) { // search page
		$dynamic = 2;
		$search = new SearchEngine();
		$params = sanitize($_POST['preserve_search_params']);
		$search->setSearchParams($params);
		$images = $search->getImages(0);
		$searchwords = $search->words;
		$searchdate = $search->dates;
		$searchfields = $search->getSearchFields(true);
		$page = $search->page;
		if (empty($_POST['imagenumber'])) {
			$albumq = query_single_row("SELECT title, folder FROM ". prefix('albums') ." WHERE id = ".abs($albumid));
			$album = new Album($gallery, $albumq['folder']);
			$returnpath = getSearchURL($searchwords, $searchdate, $searchfields, $page);
			//$returnpath = rewrite_path('/'.pathurlencode($album->name).'/page/'.$pagenumber,'/index.php?album='.urlencode($album->name).'&page='.$pagenumber);
		} else {
			$returnpath = getSearchURL($searchwords, $searchdate, $searchfields, $page);
		}
		$albumtitle = gettext('Search');
	} else {
		$albumq = query_single_row("SELECT title, folder FROM ". prefix('albums') ." WHERE id = ".$albumid);
		$album = new Album($gallery, $albumq['folder']);
		$albumtitle = $album->getTitle();
		if(!$album->isMyItem(LIST_RIGHTS) && !checkAlbumPassword($albumq['folder'])) {
			echo gettext("This album is password protected!");
			exit();
		}
		$dynamic = $album->isDynamic();
		$images = $album->getImages(0);
		// return path to get back to the page we called the slideshow from
		if (empty($_POST['imagenumber'])) {
			$returnpath = rewrite_path('/'.pathurlencode($album->name).'/page/'.$pagenumber,'/index.php?album='.urlencode($album->name).'&page='.$pagenumber);
		} else {
			$returnpath = rewrite_path('/'.pathurlencode($album->name).'/'.rawurlencode(sanitize($_POST['imagefile'])).getOption('mod_rewrite_image_suffix'),'/index.php?album='.urlencode($album->name).'&image='.urlencode($_POST['imagefile']));
		}
	}
	if($shuffle) shuffle($images);
	$showdesc = getOption("slideshow_showdesc");
	// slideshow display section
	switch($option) {
		case "jQuery":
			$validtypes = array('jpg','jpeg','gif','png','mov','3gp');
			?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				$(document).ready(function(){
					$(function() {
						var ThisGallery = '<?php echo html_encode($albumtitle); ?>';
						var ImageList = new Array();
						var TitleList = new Array();
						var DescList = new Array();
						var ImageNameList = new Array();
						var DynTime=(<?php echo getOption("slideshow_timeout"); ?>) * 1.0;	// force numeric
						<?php
						for ($imgnr = 0, $cntr = 0, $idx = $imagenumber; $imgnr < $numberofimages; $imgnr++, $idx++) {
							if ($dynamic) {
								$filename = $images[$idx]['filename'];
								$album = new Album($gallery, $images[$idx]['folder']);
								$image = newImage($album, $filename);
							} else {
								$filename = $images[$idx];
								$image = newImage($album, $filename);
							}
							$ext = is_valid($filename, $validtypes);
							if ($ext) {
								makeImageCurrent($image);
								if($crop) {
									$img = getCustomImageURL(NULL, $width, $height,$width, $height);
								} else {
									$img = getCustomSizedImageMaxSpace($width,$height);
								}
								//$img = WEBPATH . '/' . ZENFOLDER . '/i.php?a=' . pathurlencode($image->album->name) . '&i=' . pathurlencode($filename) . '&s=' . $imagesize;
								echo 'ImageList[' . $cntr . '] = "' . $img . '";'. "\n";
								echo 'TitleList[' . $cntr . '] = "' . js_encode($image->getTitle()) . '";'. "\n";
								if($showdesc) {
									$desc = $image->getDesc();
									$desc = str_replace("\r\n", '<br />', $desc);
									$desc = str_replace("\r", '<br />', $desc);
									echo 'DescList[' . $cntr . '] = "' . js_encode($desc) . '";'. "\n";
								} else {
									echo 'DescList[' . $cntr . '] = "";'. "\n";
								}
								if ($idx == $numberofimages - 1) { $idx = -1; }
								echo 'ImageNameList[' . $cntr . '] = "'.urlencode($filename).'";'. "\n";
								$cntr++;
							}
						}
						echo "\n";
						$numberofimages = $cntr;
						?>
						var countOffset = <?php echo $imagenumber; ?>;
						var totalSlideCount = <?php echo $numberofimages; ?>;
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
							var htmlblock = "<span class='slideimage'><h4><strong>" + ThisGallery + ":</strong> ";
							htmlblock += TitleList[currentImageNum]  + " (" + relativeSlot + "/" + totalSlideCount + ")</h4>";
							htmlblock += "<img src='" + ImageList[currentImageNum] + "'/>";
							htmlblock += "<p class='imgdesc'>" + DescList[currentImageNum] + "</p></span>";
							opts.addSlide(htmlblock);
						}

						function onAfter(curr, next, opts){
							<?php if (!$album->isMyItem(LIST_RIGHTS)) { ?>
							//Only register at hit count the first time the image is viewed.
							if ($(next).attr( 'viewed') != 1) {
								$.get("<?php echo FULLWEBPATH .'/'. ZENFOLDER . '/'.PLUGIN_FOLDER; ?>/slideshow/slideshow-counter.php?album=<?php echo pathurlencode($album->name); ?>&img="+ImageNameList[opts.currSlide]);
								$(next).attr( 'viewed', 1 );
							}
							<?php } ?>
						}

						$('#slides').cycle({
								fx:     '<?php echo getOption("slideshow_effect"); ?>',
								speed:   <?php echo getOption("slideshow_speed"); ?>,
								timeout: DynTime,
								next:   '#next',
								prev:   '#prev',
								cleartype: 1,
								before: onBefore,
								after: onAfter
						});

						$('#speed').change(function () {
							DynTime = this.value;
							return false;
						});

						$('#pause').click(function() { $('#slides').cycle('pause'); return false; });
						$('#play').click(function() { $('#slides').cycle('resume'); return false; });
					});

				});	// Documentready()
				// ]]> -->
			</script>
				<div id="slideshow" align="center">
				<?php
				// 7/21/08dp
				if ($speedctl) {
					echo '<div id="speedcontrol">'; // just to keep it away from controls for sake of this demo
					$minto = getOption("slideshow_speed");
					while ($minto % 500 != 0) {
						$minto += 100;
						if ($minto > 10000) { break; }  // emergency bailout!
					}
					$dflttimeout = getOption("slideshow_timeout");
					/* don't let min timeout = speed */
					$thistimeout = ($minto == getOption("slideshow_speed")? $minto + 250 : $minto);
					echo 'Select Speed: <select id="speed" name="speed">';
					while ( $thistimeout <= 60000) {  // "around" 1 minute :)
						echo "<option value=$thistimeout " . ($thistimeout == $dflttimeout?" selected='selected'>" :">") . round($thistimeout/1000,1) . " sec</option>";
						/* put back timeout to even increments of .5 */
						if ($thistimeout % 500 != 0) { $thistimeout -= 250; }
						$thistimeout += ($thistimeout < 1000? 500:($thistimeout < 10000? 1000:5000));
					}
					echo "</select> </div>";
				}
				if(!is_object($albumobj)) { // disable controls if calling the slideshow directly on homepage for example
				?>
				<div id="controls">
				<div><span><a href="#" id="prev"
					title="<?php echo gettext("Previous"); ?>"></a></span> <a
					href="<?php echo html_encode($returnpath); ?>" id="stop"
					title="<?php echo gettext("Stop and return to album or image page"); ?>"></a>
				<a href="#" id="pause"
					title="<?php echo gettext("Pause (to stop the slideshow without returning)"); ?>"></a>
				<a href="#" id="play" title="<?php echo gettext("Play"); ?>"></a> <a
					href="#" id="next" title="<?php echo gettext("Next"); ?>"></a>
				</div>
				</div>
				<?php } ?>
				<div id="slides" class="pics">
				<?php
				if ($cntr > 1) $cntr = 1;
				for ($imgnr = 0, $idx = $imagenumber; $imgnr <= $cntr; $idx++) {
					if ($idx >= $numberofimages) { $idx = 0; }
					if ($dynamic) {
						$folder = $images[$idx]['folder'];
						$dalbum = new Album($gallery, $folder);
						$filename = $images[$idx]['filename'];
						$image = newImage($dalbum, $filename);
						$imagepath = FULLWEBPATH.ALBUM_FOLDER_EMPTY.pathurlencode($folder)."/".urlencode($filename);
					} else {
						$folder = $album->name;
						$filename = $images[$idx];
						//$filename = $animage;
						$image = newImage($album, $filename);
						$imagepath = FULLWEBPATH.ALBUM_FOLDER_EMPTY.pathurlencode($folder)."/".urlencode($filename);

					}
					$ext = is_valid($filename, $validtypes);
					if ($ext) {
						$imgnr++;
						echo "<span class='slideimage'><h4><strong>".$albumtitle.gettext(":")."</strong> ".$image->getTitle()." (". ($idx + 1) ."/".$numberofimages.")</h4>";

						if ($ext == "3gp") {
							echo '</a>
										<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="352" height="304" codebase="http://www.apple.com/qtactivex/qtplugin.cab">
										<param name="src" value="' . $imagepath. '"/>
										<param name="autoplay" value="false" />
										<param name="type" value="video/quicktime" />
										<param name="controller" value="true" />
										<embed src="' . $imagepath. '" width="352" height="304" autoplay="false" controller"true" type="video/quicktime"
										pluginspage="http://www.apple.com/quicktime/download/" cache="true"></embed>
										</object>
										<a>';
						}	elseif ($ext == "mov") {
							echo '</a>
										<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="640" height="496" codebase="http://www.apple.com/qtactivex/qtplugin.cab">
										<param name="src" value="' . $imagepath. '"/>
										<param name="autoplay" value="false" />
										<param name="type" value="video/quicktime" />
										<param name="controller" value="true" />
										<embed src="'  . $imagepath. '" width="640" height="496" autoplay="false" controller"true" type="video/quicktime"
										pluginspage="http://www.apple.com/quicktime/download/" cache="true"></embed>
										</object>
										<a>';
						} else {
							makeImageCurrent($image);
							if($crop) {
								printCustomSizedImage('', NULL, $width, $height, $width, $height, NULL, NULL, NULL, NULL, false);
							} else {
								printCustomSizedImageMaxSpace($alt='',$width,$height,NULL,NULL,false);
							}
							//echo "<img src='".WEBPATH."/".ZENFOLDER."/i.php?a=".pathurlencode($folder)."&i=".urlencode($filename)."&s=".$imagesize."' alt='".html_encode($image->getTitle())."' title='".html_encode($image->getTitle())."' />\n";
						}
						if($showdesc) {
							$desc = $image->getDesc();
							$desc = str_replace("\r\n", '<br />', $desc);
							$desc = str_replace("\r", '<br />', $desc);
							echo "<p class='imgdesc'>".$desc."</p>";
						}
						echo "</span>";
					}
				}

				break;

		case "flash":
			if ($heading) {
				echo "<span class='slideimage'><h4><strong>".$albumtitle."</strong> (".$numberofimages." images) | <a style='color: white' href='".html_encode($returnpath)."' title='".gettext("back")."'>".gettext("back")."</a></h4>";
			}
			echo "<span id='slideshow' style='display: block; margin: 0 auto; width:".getOption('slideshow_flow_player_width')."px; height: ".getOption('slideshow_flow_player_height')."px'></span>";
			$curdir = getcwd();
			chdir(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/flowplayer3');
			$filelist = safe_glob('flowplayer-*.swf');
			$swf = array_shift($filelist);
			$filelist = safe_glob('flowplayer.controls-*.swf');
			$controls = array_shift($filelist);
			chdir($curdir);
			?>
			<script type="text/javascript">
			// <!-- <![CDATA[
			flowplayer('slideshow','<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER;?>/flowplayer3/<?php echo $swf; ?>', {

			clip: {
					onLastSecond: function() {
					this.getScreen().animate({opacity: 0}, <?php echo getOption('slideshow_speed')/2; ?>);
					},
					onFinish: function(){
					this.getScreen().animate({opacity: 1}, 1000);
					},
					onStart: function() {
					this.getScreen().animate({opacity: 1}, <?php echo getOption('slideshow_speed')/2; ?>);
					}
						},

			playlist: [
			<?php
			echo "\n";
			$count = 0;
			foreach($images as $animage) {
					if ($dynamic) {
						$folder = $animage['folder'];
						$filename = $animage['filename'];
						$salbum = new Album($_zp_gallery, $folder);
						$image = newImage($salbum, $filename);
						$imagepath = FULLWEBPATH.ALBUM_FOLDER_EMPTY.pathurlencode($salbum->name)."/".urlencode($filename);
					} else {
						$folder = $album->name;
						$filename = $animage;
						$image = newImage($album, $filename);
						$imagepath = FULLWEBPATH.ALBUM_FOLDER_EMPTY.pathurlencode($folder)."/".pathurlencode($filename);
					}
				$ext = is_valid($filename, array('jpg','jpeg','gif','png','flv','mp3','mp4'));
				if ($ext) {
					if (($ext == "flv") || ($ext == "mp3") || ($ext == "mp4")) {
						$duration = "";
					} else {
						$duration = ", duration: ".getOption("slideshow_timeout")/1000;
					}
					if($count > 0) { echo ",\n"; }
					echo "{ url: '".FULLWEBPATH.ALBUM_FOLDER_EMPTY.pathurlencode($folder)."/".urlencode($filename)."'".$duration.", scaling: 'fit', autoBuffering: true }";
					$count++;
				}
			}
			echo "\n";
			?>
		],
		plugins:  {
				controls: {
					url: '<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER; ?>/flowplayer3/<?php echo $controls; ?>',
						playlist: true,
						autoHide: 'always'
				}
		}
										});
			// ]]> -->
		</script>
			<?php
			echo "</span>";
			echo "<p>";
			echo gettext("Click on the right in the player control bar to view full size.");
			echo "</p>";
			break;
	}
	?>
	</div>
</div>
	<?php
	restore_context(); // needed if the slideshow is for example called directly via album object before the next_album loop on index.php
}


/**
 * Prints the path to the slideshow JS and CSS (printed because some values need to be changed dynamically).
 * CSS can be adjusted
 * To be used on slideshow.php
 *
 */
function printSlideShowJS() {
	$curdir = getcwd();
	chdir(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/flowplayer3');
	$filelist = safe_glob('flowplayer-*.min.js');
	$player = array_shift($filelist);
	$filelist = safe_glob('flowplayer.playlist-*.min.js');
	$playlist = array_shift($filelist);
	chdir($curdir);
	?>
	<script	src="<?php echo FULLWEBPATH . '/' . ZENFOLDER.'/'.PLUGIN_FOLDER ?>/slideshow/jquery.cycle.all.js" type="text/javascript"></script>
	<?php
	if(!getOption('zp_plugin_flowplayer3')) {
		?>
		<script type="text/javascript" src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER; ?>/flowplayer3/<?php echo $player; ?>"></script>
		<?php
	}
	?>
	<script type="text/javascript" src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/'.PLUGIN_FOLDER;?>/flowplayer3/<?php echo $playlist; ?>"></script>
	<?php
}

?>
