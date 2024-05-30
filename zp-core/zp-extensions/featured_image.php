<?php
/**
 * This plugin provides a functionality to assign an image from your albums to an Zenpage news article, category or page as a "featured image".
 * You can use this image for example for headers of your single article/page/category pages or within the news article list as a thumbnail. 
 * The benefit compared to the embedding an image within the text content statically is that you can control the size of it 
 * via your theme's layout dynamically as with gallery items.
 *
 * To use it you need to modify your theme used if it has no built in support already. 
 * 
 * Usage examples:
 * 
 * a) Object model 
 * $featuredimage = getFeaturedImage(<object of the Zenpage item>);
 * if($featuredimage) { // if an feature image exists use the object model
 *  ?>
 *  <img src="<?php echo pathurlencode($featuredimage->getThumb()); ?>" alt="<?php echo html_encode($featuredimage->getTitle()); ?>">
 *  <?php
 * }
 * 
 * b) Theme function for pages.php and news.php for the current article, category or page
 * <?php printSizedFeaturedImage(NULL,'My featured image',500); ?>
 *  
 * Requirement: Zenpage CMS plugin and a theme supporting it
 * 
 * @since 1.6.3 - former "half official" plugin included
 *
 * @author Malte Müller (acrylian) <info@maltem.de>
 * @copyright 2014 Malte Müller
 * @license: GPL v3 or later
 * @package plugins
 * @subpackage misc
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN | THEME_PLUGIN;
$plugin_description = gettext("Attach an image to a Zenpage news article, category or page.");
$plugin_author = "Malte Müller (acrylian)";
$plugin_disable = (!getOption('zp_plugin_zenpage')) ? gettext('The Zenpage CMS plugin is required for this and not enabled!') : false;
if (getOption('zp_plugin_zenpage')) {
	zp_register_filter('publish_article_utilities', 'featuredImage::getFeaturedImageSelector');
	zp_register_filter('publish_page_utilities', 'featuredImage::getFeaturedImageSelector');
	zp_register_filter('publish_category_utilities', 'featuredImage::getFeaturedImageSelector');
	zp_register_filter('remove_object', 'featuredImage::deleteFeaturedImage');
	zp_register_filter('admin_head', 'featuredImage::featuredImageCSS');
}

/* * ***********************
 * Backend functions 
 * ************************ */

class featuredImage {

	static function featuredImageCSS() {
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/'. PLUGIN_FOLDER; ?>/featured_image/style.css" />
		<?php
	}

	/**
	 * Gets a selector with all toplevel albums whose images you can assign as featured image for the dialogue window
	 *
	 * @param string $html
	 * @param object $obj Zenpage article, category or page object to assign the thumb
	 * @param string $prefix (not used)
	 * @param string $allowedfiles	"all" (default) for all registered "image" files,
	  *															"images" for only standard "image" files (jpg, jpeg, png, gif),
	  *															"<suffix>"  (without trailing dot, e.g. "jpg") for only "image" files with that suffix
	 */
	static function getFeaturedImageSelector($html, $obj, $prefix = '' , $allowedfiles = 'images') {
		global $_zp_gallery;
		$selection = featuredImage::getFeaturedImageSelection($obj);
		if ($selection) {
			$buttontext = gettext('Change');
		} else {
			$buttontext = gettext('Set');
		}
		$itemtype = featuredImage::getFeaturedImageType($obj);
		switch ($itemtype) {
			case 'featuredimage_article':
				$type = 'news'; // this is needed for the getItemByID() function using in Ajax calls
				$itemid = $obj->getID();
				break;
			case 'featuredimage_category':
				$type = 'news_categories';
				$itemid = $obj->getID();
				break;
			case 'featuredimage_page':
				$type = 'pages';
				$itemid = $obj->getID();
				break;
		}
		// admin utility box button to call the dialog
		$html .= '<hr /><h3>Feature image</h3>';
		$fimage = featuredImage::getFeaturedImage($obj);
		$imghtml = '';
		if ($fimage) {
			$imghtml = '<img src="' . html_encode($fimage->getThumb()) . '" alt="" loading="lazy" decoding="async">';
		}
		$html .= '<div id="fi_adminthumb"><a href="#" class="fi_opener">' . $imghtml . '</a></div>';
		$html .= '<p class="buttons fi_adminbuttons"><button class="fi_opener fi_opener_admin">' . $buttontext . '</button>';
		$html .= '</p>';
		$html .= '<hr>';
		// this is part of the dialog window
		$albumshtml = '<ul>';
		$albumshtml .= '<li><a href="#" class="active fi_image">' . gettext('Current featured image') . '</a></li>';
		$albums = $_zp_gallery->getAlbums();
		foreach ($albums as $album) {
			$albobj = AlbumBase::newAlbum($album);
			if ($albobj->getNumImages() == 0) {
				$albumshtml .= '<li>' . $albobj->getTitle() . ' <small>(' . $albobj->getNumImages() . ')</small>';
			} else {
				$albumshtml .= '<li><a href="#" title="' . $albobj->getID() . '" class="fi_album">' . $albobj->getTitle() . '</a> <small>(' . $albobj->getNumImages() . ')</small>';
			}
			$albumshtml .= featuredImage::getFeaturedImageSubalbums($albobj);
			$albumshtml .= '</li>';
		}
		$albumshtml .= '</ul>';
		$html .= '
			<div id="featuredimageselector">
				<div id="fi_albumlist">' . $albumshtml . '</div>
				<div id="fi_content"><h4>' . gettext('Current featured image') . '</h4></div>
			</div>';
		$html .= '<script>
		$(document).ready(function(){
			var winwidth = $(window).width();
			var winheight = $(window).height();
			if(winwidth-150 != 0) {
				winwidth = winwidth-150;
			}
			if(winheight-150 != 0) {
				winheight = winheight-150;
			}
			$( "#featuredimageselector" ).dialog({
				autoOpen: false,
				modal: true,
				resizable: true,
				title: "Select a featured image",
				closeOnEscape: true,
				width: winwidth,
				height: winheight
			});
 
			$( ".fi_opener" ).click(function() {
				$( "#featuredimageselector" ).dialog( "open" );
				$( "#featuredimageselector #fi_content" ).html("");
				$( "#featuredimageselector #fi_content" ).load( "' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/featured_image/process_selection.php?fi_currentimg=' . $itemid . '&fi_type=' . $type . '&XSRFToken=' . getXSRFToken('fi_currentimg') . '");
			
				//current featured image
				$( "#featuredimageselector #fi_albumlist li a.fi_image" ).click(function() {
					var linktitle = $(this).attr( "title" );
					$("#fi_albumlist li a").removeClass( "active" );
					$(this).addClass( "active" );
					$( "#featuredimageselector #fi_content" ).load( "' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/featured_image/process_selection.php?fi_currentimg=' . $itemid . '&fi_type=' . $type . '&XSRFToken=' . getXSRFToken('fi_currentimg') . '");
				});
			
				// thumbs of an album
				$( "#featuredimageselector #fi_albumlist li a.fi_album" ).click(function() {
					var id = $(this).attr( "title" );
					$("#fi_albumlist li a").removeClass( "active" );
					$(this).addClass( "active" );
					$( "#featuredimageselector #fi_content" ).load( "' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/featured_image/process_selection.php?fi_getalb="+id+"&fi_itemid=' . $itemid . '&fi_type=' . $type . '&fi_imgpage=1&XSRFToken=' . getXSRFToken('fi_getalb') . '&fi_allowedfiles=' . $allowedfiles . '");
				});       
				return false;
			});
     
		});
		</script>';
		return $html;
	}

	/**
	 * Gets the subalbums of the album object $albobj recursively
	 * @param object $albobj album object
	 */
	private static function getFeaturedImageSubalbums(&$albobj) {
		global $_zp_gallery;
		if ($albobj->getNumAlbums() != 0) {
			$html = '<ul>';
			$albums = $_zp_gallery->getAllAlbumsFromDB(true, $albobj, UPLOAD_RIGHTS, false);
			//$albums = $albobj->getAlbums();
			foreach ($albums as $album) {
				$obj = AlbumBase::newAlbum($album);
				if ($obj->getNumImages() == 0) {
					$html .= '<li>' . $obj->getTitle() . ' <small>(' . $obj->getNumImages() . ')</small>';
				} else {
					$html .= '<li><a href="#" title="' . $obj->getID() . '" class="fi_album">' . $obj->getTitle() . '</a> <small>(' . $obj->getNumImages() . ')</small>';
				}
				$html .= featuredImage::getFeaturedImageSubalbums($obj);
				$html .= '</li>';
			}
			$html .= '</ul>';
			return $html;
		} 
	}

	/**
	 * Gets the featured image of this article from the database
	 * @param object $obj Zenpage article, category or page object to assign the thumb
	 */
	static function getFeaturedImageSelection($obj) {
		global $_zp_db;
		$type = featuredImage::getFeaturedImageType($obj);
		if ($type) {
			$itemid = $obj->getID();
			$query = $_zp_db->querySingleRow("SELECT `data` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE `type` = " . $_zp_db->quote($type) . " AND `aux` = " . $_zp_db->quote($itemid));
			if ($query) {
				return $query['data'];
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	 * Saves the featured image of this item via JS get request
	 */
	static function saveFeaturedImageSelection() {
		global $_zp_db;
		if (isset($_GET['fi_save'])) {
			XSRFdefender('fi_save');
			$itemtype = sanitize($_GET['fi_type']);
			$itemid = sanitize_numeric($_GET['fi_save']);
			$obj = getItemByID($itemtype, $itemid);
			if (is_object($obj)) {
				$type = featuredImage::getFeaturedImageType($obj);
				$message = '';
				if (isset($_GET['fi_imgid']) && $type) {
					$data = sanitize_numeric($_GET['fi_imgid']);
					$itemid = $obj->getID();
					$query = $_zp_db->querySingleRow("SELECT `data` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE `type` = " . $_zp_db->quote($type) . " AND `aux` = " . $_zp_db->quote($itemid));
					if ($query) {
						$query = $_zp_db->query("UPDATE " . $_zp_db->prefix('plugin_storage') . " SET `data` = " . $_zp_db->quote($data) . " WHERE `type` = " . $_zp_db->quote($type) . " AND `aux` = " . $_zp_db->quote($itemid));
					} else {
						$query = $_zp_db->query("INSERT INTO " . $_zp_db->prefix('plugin_storage') . " (`type`,`data`,`aux`) VALUES (" . $_zp_db->quote($type) . "," . $_zp_db->quote($data) . "," . $_zp_db->quote($itemid) . ")");
					}
					if (!$query) {
						$message .= '<p class="errorbox">' . sprintf(gettext('Query failure: %s'), $_zp_db->getError()) . '</p>';
					}
				}
				return $message;
			}
			exitZP();
		}
	}

	/**
	 * Removes the featured image via JS get request from plugin_storage table if their object is removed.
	 *
	 * @return bool
	 */
	static function deleteFeaturedImage() {
		global $_zp_db;
		if (isset($_GET['fi_delete'])) {
			XSRFdefender('fi_delete');
			$itemtype = sanitize($_GET['fi_type']);
			$itemid = sanitize_numeric($_GET['fi_delete']);
			$obj = getItemByID($itemtype, $itemid);
			if (is_object($obj)) {
				$type = featuredImage::getFeaturedImageType($obj);
				if ($type) {
					$itemid = $obj->getID();
					$check = $_zp_db->querySingleRow("SELECT `data` FROM " . $_zp_db->prefix('plugin_storage') . " WHERE `type` = " . $_zp_db->quote($type) . " AND `aux` = " . $_zp_db->quote($itemid));
					if ($check) {
						$query = $_zp_db->query('DELETE FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `aux` = ' . $_zp_db->quote($itemid) . ' AND `type` = ' . $_zp_db->quote($type) . '', false);
					}
				}
			}
			exitZP();
		}
	}

	/**
	 * Checks the class of $obj and returns the Zenpage item type "featuredimage_article/category/page"
	 * Returns false if no Zenpage object is passed
	 *
	 * @param object $obj Zenpage article, category or page object
	 * @return mixed
	 */
	static function getFeaturedImageType($obj) {
		if (is_object($obj)) {
			switch (get_class($obj)) {
				case 'ZenpageNews':
					return 'featuredimage_article';
				case 'ZenpageCategory':
					return 'featuredimage_category';
				case 'ZenpagePage':
					return 'featuredimage_page';
				default:
					return false;
			}
		}
	}
	
	/**
	 * Fetch single image via JS get request for previewing and selecting an image for the dialogue window
	 * @since 1.6.3
	 */
	static function getFeaturedImagePreview() {
		if (isset($_GET['fi_getimg'])) {
			XSRFdefender('fi_getimg');
			$type = sanitize($_GET['fi_type']);
			$id = sanitize_numeric($_GET['fi_getimg']);
			$itemid = sanitize_numeric($_GET['fi_itemid']);
			if (!empty($id) && !empty($itemid)) {
				$image = getItemByID('images', $id);
				if (is_object($image)) {
					featuredImage::printFeaturedImageDialog($image, 'save');
					?>
						<script>
							$(document).ready(function () {
								$("#fi_setimage").click(function () {
									$.ajax({
										type: "GET",
										url: "<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/featured_image/process_selection.php",
										data: {
											fi_save: "<?php echo $itemid; ?>",
											fi_imgid: "<?php echo $id; ?>",
											fi_type: "<?php echo $type; ?>",
											XSRFToken: "<?php echo getXSRFToken('fi_save') ?>"
										}
									});
									$("#fi_adminthumb a").html('<img src="<?php echo pathurlencode($image->getThumb()); ?>" alt="" loading="lazy" decoding="async">');
									$(".fi_opener_admin").html("<?php echo gettext('Change'); ?>");
									$("#fi_singleimage").dialog("close");
								});
							});
						</script>
					<?php
				}
			} 
			exitZP();
		}
	}

	/**
	 * Function to be used in the dialog box for displaying the single image 
	 * for setting the featured image and the current featured image 
	 *
	 * @param object $imgobj the object of the image
	 * @param string $buttontype "save" for save button and "delete" for delete button
	 */
	static function printFeaturedImageDialog($imgobj, $buttontype) {
		$album = $imgobj->getAlbum();
		if ($imgobj->isPhoto()) {
			$url = pathurlencode($imgobj->getSizedImage(400));
		} else {
			$url = pathurlencode($imgobj->getThumb());
		}
		?>
		<div id="fi_imagepreview">
			<img src="<?php echo $url; ?>" alt="<?php echo html_encode($imgobj->getTitle()); ?>" class="fi_thumb" loading="lazy" decoding="async">
		</div>
		<div id="fi_imageinfo">
			<h5><?php echo html_encode($imgobj->getTitle() . ' (' . $imgobj->filename . ')'); ?></h5>
			<p><?php echo gettext('Album: '); ?><?php echo html_encode($album->getTitle() . ' (' . $album->name . ')'); ?></p>
			<?php
			switch ($buttontype) {
				case 'save':
					$buttontext = gettext('Set feature image');
					$buttonid = 'fi_setimage';
					break;
				case 'delete':
					$buttontext = gettext('Remove feature image');
					$buttonid = 'fi_deleteimage';
					break;
			}
			?>
			<p class="buttons"><button id="<?php echo $buttonid; ?>"><?php echo $buttontext; ?></button></p>
			<br class="clearfix">
		</div>
		<?php
	}
	
	/**
	 * Gets image thumbs of an album via JS get request for the dialogue window
	 * 
	 * @since 1.6.3
	 */
	static function getThumbs() {
		global $_zp_supported_images;
		if (isset($_GET['fi_getalb']) && isset($_GET['fi_imgpage'])) {
			XSRFdefender('fi_getalb');
			$allowedfiles = sanitize($_GET['fi_allowedfiles']);
			$type = sanitize($_GET['fi_type']);
			$itemid = sanitize_numeric($_GET['fi_itemid']);
			$id = sanitize_numeric($_GET['fi_getalb']);
			$imgpage = sanitize_numeric($_GET['fi_imgpage']);
			if (!empty($id) && !empty($imgpage) && !empty($itemid)) {
				$obj = getItemByID('albums', $id);
				if (is_object($obj)) {
					//setOption('images_per_page', 45, false);
					$images = $obj->getImages($imgpage);
					$numimages = $obj->getNumImages();
					$img_per_page = getOption('images_per_page');
					$totalpages = ceil($numimages / $img_per_page);
					?>
					<script>
						$(document).ready(function () {
							$(".fi_pagenav").html(''); //clear the html so the nav is always fresh!
							var total = <?php echo $totalpages; ?>;
							var current = <?php echo $imgpage; ?>;
							var activeclass = '';
							if (total > 1) {
								if (current !== 1) {
									$(".fi_pagenav").append('<li><a href="#" title="' + (current - 1) + '">prev</a></li>');
								}
								for (i = 1; i <= total; i++) {
									if (current === i) {
										activeclass = ' class = "active"';
									} else {
										activeclass = '';
									}
									$(".fi_pagenav").append('<li><a href="#" title="' + i + '"' + activeclass + '>' + i + '</a></li>');
								}
								if (current < total) {
									$(".fi_pagenav").append('<li><a href="#" title="' + (current + 1) + '">next</a></li>');
								}
							}
							$(".fi_pagenav li a").click(function () {
								var imgpage = $(this).attr("title");
								//alert("click");
								$("#featuredimageselector #fi_content").load("<?php echo WEBPATH . '/' . ZENFOLDER . '/'. PLUGIN_FOLDER; ?>/featured_image/process_selection.php?fi_getalb=<?php echo $id; ?>&fi_itemid=<?php echo $itemid; ?>&fi_type=<?php echo $type; ?>&fi_imgpage=" + imgpage + "&XSRFToken=<?php echo getXSRFToken('fi_getalb') ?>&fi_allowedfiles=<?php echo $allowedfiles; ?>");
							});

							//single image dialog to preview uncropped and set feature image
							$("#fi_singleimage").dialog({
								autoOpen: false,
								modal: true,
								resizable: true,
								closeOnEscape: true,
								width: 640,
								height: 480
							});

							//dialog to preview and set an image
							$("#featuredimageselector #fi_content a.fi_thumb").click(function () {
								$("#fi_singleimage").html("");
								$("#fi_singleimage").dialog("open");
								var id = $('img', this).attr("title");
								$("#fi_singleimage").load("<?php echo WEBPATH . '/' . ZENFOLDER . '/'. PLUGIN_FOLDER; ?>/featured_image/process_selection.php?fi_getimg=" + id + "&fi_itemid=<?php echo $itemid; ?>&fi_type=<?php echo $type; ?>&XSRFToken=<?php echo getXSRFToken('fi_getimg') ?>");
							});
						});
					</script>
					<h4><?php echo $obj->getTitle(); ?> (<?php echo $obj->name; ?>) – <?php echo $obj->getNumImages(); ?></h4>
					<ul class="fi_pagenav"></ul><!-- filled with the nav -->
					<?php
					if ($numimages == 0) {
						?>
						<p class="notebox"><?php echo gettext('This album does not contain any images.'); ?></p>
						<?php
					} else {
						$count = '';
						foreach ($images as $image) {
							if($obj->isDynamic()) {
								$filename = $image['filename'];
							} else {
								$filename = $image;
							}
							$suffix = getSuffix($filename);
							$image_allowed = false;
							if ($allowedfiles == 'all') {
								$image_allowed = true;
							} else if ($allowedfiles == 'images') {
								if (in_array($suffix, $_zp_supported_images)) {
									$image_allowed = true;
								}
							} else if ($allowedfiles == $suffix) {
								$image_allowed = true;
							}
							if ($image_allowed) {
								$count++;
								$imgobj = Image::newImage($obj, $image);
								?>
								<a href="#" title="<?php echo html_encode($imgobj->getTitle() . ' (' . $imgobj->filename . ')'); ?>" class="fi_thumb">
									<img src="<?php echo pathurlencode($imgobj->getThumb()); ?>" alt="<?php echo html_encode($imgobj->getTitle() . ' (' . $imgobj->filename . ')'); ?>" title="<?php echo $imgobj->getID(); ?>" loading="lazy" decoding="async">
								</a>
								<?php
							}
					
						}
						if (empty($count)) {
							echo '<p class="notebox">' .gettext('No supported image files available.') . '</p>';
						}
						?>
						<ul class="fi_pagenav"></ul><!-- filled with the nav -->
						<div id="fi_singleimage"></div>
						<?php
					}
				} // if variables not empty
			}
			exitZP();
		}
	}
	
	/**
	 * Gets the current featured images for the dialogue window
	 * @since 1.6.3
	 */
	static function getCurrentFeatureImage() {
		if (isset($_GET['fi_currentimg'])) {
			XSRFdefender('fi_currentimg');
			$type = sanitize($_GET['fi_type']);
			$id = sanitize_numeric($_GET['fi_currentimg']);
			$obj = getItemByID($type, $id);
			if (is_object($obj)) {
				$title = $obj->getTitle();
				$image = featuredImage::getFeaturedImage($obj);
				?>
				<h4><?php echo gettext('Current featured image for ');
				echo '<em>' . html_encode($title) . ' (' . $type . ')</em>';
				?></h4>
					<p class="notebox"><?php echo gettext('<strong>Note:</strong> Your theme might need changes to support this feature.'); ?></p>
					<div id="fi_singleimage">
				<?php
				if ($image) {
					featuredImage::printFeaturedImageDialog($image, 'delete');
					?>
					<script>
						$(document).ready(function () {
							$("#fi_deleteimage").click(function () {
								$.ajax({
									type: "GET",
									url: "<?php echo WEBPATH . '/' . ZENFOLDER . '/'. PLUGIN_FOLDER; ?>/featured_image/process_selection.php",
									data: {
										fi_delete: "<?php echo $id; ?>",
										fi_type: "<?php echo $type; ?>",
										XSRFToken: "<?php echo getXSRFToken('fi_delete') ?>"
									}
								});
								$("#fi_adminthumb a").html('');
								$(".fi_opener_admin").html("<?php echo gettext('Set'); ?>");
								$("#featuredimageselector").dialog("close");
							});
						});
					</script>
					<?php
				} else {
					?>
					<p><?php echo gettext('No featured image selected.'); ?></p>
					<?php
				}
				?>
				</div>
				<?php
			}
			exitZP();
		}
	}
	
	/**
	 * Processes JS GET requests and may return/display HTML for the dialogue window
	 * @since 1.6.3
	 */
	static function processSelection() {
		featuredImage::getCurrentFeatureImage();
		featuredImage::saveFeaturedImageSelection();
		featuredImage::deleteFeaturedImage();
		featuredImage::getFeaturedImagePreview();
		featuredImage::getThumbs();
	}

	/**
	 * Returns the image object of the featured iamge if set
	 *
	 * @param obj/strong $obj Zenpage article, category or page object you want the featured image of
	 */
	static function getFeaturedImage($obj) {
		$imgobj = '';
		$imagedata = featuredImage::getFeaturedImageSelection($obj);
		if ($imagedata) {
			if (is_numeric($imagedata)) {
				$imgobj = getItemByID('images', $imagedata);
			} else {
				//fallback for existing entries using the old albumthumb only way 
				//where the album name was stored
				$album = AlbumBase::newAlbum($imagedata);
				$imgobj = $album->getAlbumThumbImage();
			}
			if (is_object($imgobj)) {
				return $imgobj;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

}

/* * **********************
 * Frontend theme function
 * ********************** */

/**
 * Returns the image object if an featured image is assigned, otherwise false
 *
 * @param obj/string $obj Object of the Zenpage article, category you want the featured image to get
 * return mixed
 */
function getFeaturedImage($obj = NULL) {
	if (is_null($obj)) {
		$obj = getContextObject();
	}
	return featuredImage::getFeaturedImage($obj);
}

/**
 * Gets a custom sized version of this image based on the parameters. Multimedia items don't use these.
 * 
 * @since 1.6.3
 *
 * @param obj/string $obj Object of the Zenpage page, article or category you want the featured image to get. If you set it to NULL on news.php or pages.php it will try to get the image for the current page, news article or category
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cropw crop width
 * @param int $croph crop height
 * @param int $cropx crop x axis
 * @param int $cropy crop y axis
 * @param bool $thumb set to true to treat as thumbnail. Multimedia items are not used then
 * @param bool $effects set to desired image effect (e.g. 'gray' to force gray scale)
 * @param bool $maxspace Set $width and height and this to true (default false) to get a maxspace image. Other size paramaters will be ignored
 * @return string
 */
function getSizedFeaturedImage($obj = NULL, $size = NULL, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $cropx = NULL, $cropy = NULL, $thumb = false, $effects = NULL, $maxspace = false) {
	if (is_null($obj)) {
		$obj = getContextObject();
	}
	$imageobj = getFeaturedImage($obj);
	if ($imageobj) {
		if ($maxspace && !is_null($width) && !is_null($height)) {
			getMaxSpaceContainer($width, $height, $imageobj, $thumb);
			return $imageobj->getCustomImage(null, $width, $height, null, null, null, null, $thumb, $effects);
		} else {
			return $imageobj->getCustomImage($size, $width, $height, $cropw, $croph, $cropx, $cropy, $thumb, $effects);
		}
	}
}

/**
 *  Print a custom sized version of this image based on the parameters. Multimedia items don't use these.
 *
 * @param obj/string $obj Object of the Zenpage page, article or category you want the featured image to get. If you set it to NULL on news.php or pages.php it will try to get the image for the current page, news article or category
 * @param string $alt Alt text of the img element
 * @param int $size size
 * @param int $width width
 * @param int $height height
 * @param int $cropw crop width
 * @param int $croph crop height
 * @param int $cropx crop x axis
 * @param int $cropy crop y axis
 * @param string $class Class of the img element
 * @param string $id Id of the img element
 * @param bool $thumb set to true to treat as thumbnail. Multimedia items are not used then
 * @param bool $effects set to desired image effect (e.g. 'gray' to force gray scale)
 * @param bool $maxspace Set $width and height and this to true (default false) to get a maxspace image. Other size paramaters will be ignored
 * @return string
 */
function printSizedFeaturedImage($obj = NULL, $alt = '', $size = NULL, $width = NULL, $height = NULL, $cropw = NULL, $croph = NULL, $cropx = NULL, $cropy = NULL, $class = NULL, $id = NULL, $thumb = false, $effects = NULL, $maxspace = false) {
	if (is_null($obj)) {
		$obj = getContextObject();
	}
	$imageobj = getFeaturedImage($obj);
	if ($imageobj) {
		if (empty($alt)) {
			$alt = $imageobj->getTitle();
		}
		if ($maxspace && !is_null($width) && !is_null($height)) {
			printCustomSizedImageMaxSpace($alt, $width, $height, $class, $id, $thumb, $alt, $imageobj);
		} else {
			printCustomSizedImage($alt, $size, $width, $height, $cropw, $croph, $cropx, $cropy, $class, $id, $thumb, $effects, $alt, 'image', $imageobj);
		}
	}
}

/**
 * Gets the default thumb size of a featured image
 *
 * @since 1.6.3
 *
* @param obj/string $obj Object of the Zenpage page, article or category you want the featured image to get. If you set it to NULL on news.php or pages.php it will try to get the image for the current page, news article or category
 * @return string
 */
function getFeaturedImageThumb($obj = null) {
	if (is_null($obj)) {
		$obj = getContextObject();
	}
	$imageobj = getFeaturedImage($obj);
	if ($imageobj) {
		return getImageThumb($imageobj);
	}
}

/**
 * Prints the default thumb size of a featured image
 *
 * @since 1.6.3
 *
 * @param string $alt Alt text
 * @param string $class optional class attribute
 * @param string $id optional id attribute
 * @param string $title optional title attribute
 * @param obj/string $obj Object of the Zenpage page, article or category you want the featured image to get. If you set it to NULL on news.php or pages.php it will try to get the image for the current page, news article or category
 *
 */
function printFeaturedImageThumb($alt = null, $class = null, $id = NULL, $title = null, $obj = null) {
	if (is_null($obj)) {
		$obj = getContextObject();
	}
	$imageobj = getFeaturedImage($obj);
	if ($imageobj) {
		if ($obj->checkAccess()) {
			if (empty($alt)) {
				$alt = $imageobj->getTitle();
			}
				printImageThumb($alt, $class, $id, $title, $imageobj);
		} else {
			$sizes = getSizeDefaultThumb($imageobj);
			$size = ' width="' . $sizes[0] . '"';
			printPasswordProtectedImage($size);
		}
	}
}