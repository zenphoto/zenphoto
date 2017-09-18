<?php
/**
 * ZenPhoto20 object paster for tinyMCE
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 */
// force UTF-8 Ø
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/admin-globals.php");
admin_securityChecks(ALBUM_RIGHTS | ZENPAGE_PAGES_RIGHTS | ZENPAGE_NEWS_RIGHTS, NULL);

header('Last-Modified: ' . ZP_LAST_MODIFIED);
header('Content-Type: text/html; charset=' . LOCAL_CHARSET);

function getIPSizedImage($size, $image) {
	$wmt = getWatermarkParam($image, WATERMARK_IMAGE);
	$args = getImageParameters(array($size, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $wmt), $image->album->name);
	return getImageProcessorURI($args, $image->album->name, $image->filename);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php printStandardMeta(); ?>
		<title>tinyMCE:obj</title>
		<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.js"></script>
		<script type="text/javascript" src="pasteobj_popup.js"></script>

	</head>

	<body>
		<h2><?php echo gettext('ZenPhoto20 object insertion'); ?></h2>
		<?php
		if (isset($_SESSION['pick'])) {
			$args = $_SESSION['pick'];
			$picture = isset($args['picture']);
			if (!$size = getOption('pasteImageSize')) {
				$size = getOption('image_size');
			}
			if (isset($args['album'])) {
				if (isset($args['image'])) {
					$obj = newImage(array('folder' => $args['album'], 'filename' => $args['image']));
					$imagef = $obj->getFullImageURL(FULLWEBPATH);
					$title = gettext('<em>image</em>: %s');
					$token = gettext('%s with link to image');
					if ($picture) {
						$imageb = $image = $args['picture'];
					} else {
						$image = $obj->getThumb();
						$imageb = preg_replace('~&check=(.*)~', '', getIPSizedImage($size, $obj));
					}
				} else {
					$args['image'] = $imagef = NULL;
					$obj = newAlbum($args['album']);
					$title = gettext('<em>album</em>: %s');
					$token = gettext('%s with link to album');
					$image = $obj->getThumb();
					$thumbobj = $obj->getAlbumThumbImage();
					$args['image'] = $thumbobj->getFilename();
					$args['album'] = $thumbobj->album->getFilename();
					$imageb = preg_replace('~&check=(.*)~', '', getIPSizedImage($size, $thumbobj));
				}
				$alt1 = $obj->getFileName();

				// an image type object
			} else {
				// a simple link
				$args['album'] = $args['image'] = $imagef = $imageb = $image = $alt1 = $title1 = NULL;
				if (isset($args['news'])) {
					$obj = newArticle($args['news']);
					$title = gettext('<em>news article</em>: %s');
					$token = gettext('title with link to news article');
				}
				if (isset($args['pages'])) {
					$obj = newPage($args['pages']);
					$title = gettext('<em>page</em>: %s');
					$token = gettext('title with link to page');
				}
				if (isset($args['news_categories'])) {
					$obj = newCategory($args['news_categories']);
					$title = gettext('<em>category</em>: %s');
					$token = gettext('title with link to category');
				}
			}
			$link = $obj->getLink();
			$title1 = getBare($obj->getTitle());

			if ($image && $obj->table == 'images') {
				$link2 = $obj->album->getLink();
			} else {
				$link2 = $alt2 = $title2 = false;
			}
			?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				var link = '<?php echo $link; ?>';
				var link2 = '<?php echo $link2; ?>';
				var alt1 = '<?php echo addslashes($alt1); ?>'.replace(/"/g, '\\"');
				var title1 = '<?php echo addslashes($title1); ?>'.replace(/"/g, '\\"');
				var title = '<?php echo sprintf($title, addslashes($title1)); ?>'.replace(/"/g, '\\"');
				var image = '<img src="<?php echo pathurlencode($image); ?>" alt="' + alt1 + '" title="' + title1 + '" />';
				var imagec = '<img src="<?php echo pathurlencode($imageb); ?>" alt="' + alt1 + '" title="' + title1 + '" />';
				var imagef = '<?php echo pathurlencode($imagef); ?>';
				var picture = <?php echo (int) $picture; ?>;

				function zenchange() {
					var selectedlink = $('input:radio[name=link]:checked').val();
					if (picture) {
						imageb = imagec;
					} else {
						imageb = imagec.replace('s=<?php echo $size; ?>', 's=' + $('#imagesize').val());
					}

					switch (selectedlink) {
						case 'thumb':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure class="thumbFigure">' + image + '<figcaption>' + title + '</figcaption></figure>');
							} else {
								$('#content').html(image);
							}
							break;
						default:
						case 'image':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure class="imageFigure">' + imageb + '<figcaption>' + title + '</figcaption></figure>');
							} else {
								$('#content').html(imageb);
							}
							break;
						case'player':
							$('#content').html('[MEDIAPLAYER \'<?php echo html_encode($args['album']) . "\' \'" . html_encode($args['image']) . "\' " . $obj->getID(); ?>]');
							break;
						case'show':
							$('#content').html('[SLIDESHOW \'<?php echo html_encode($args['album']) . "\'"; ?>]');
							break;
						case 'title':
							if (image) {
								$('#content').html('<a href="" class="tinyMCE_OBJ">' + title + '</a>');
							} else {
								$('#content').html(title);
							}
							break;
						case 'thumblink':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure class="thumbFigure"><a href="' + link + '" title="' + title + '" class="tinyMCE_OBJ_thumb">' + image + '</a><figcaption><a href="' + link + '" title="' + title + '" class="tinyMCE_OBJ_thumb">' + title + '</a></figcaption></figure>');
							} else {
								$('#content').html('<a href="' + link + '" title="' + title + '" class="tinyMCE_OBJ_thumb">' + image + '</a>');
							}
							break;
						case 'imagelink':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure class="imageFigure"><a href="' + link + '" title="' + title + '" class="tinyMCE_OBJ_image">' + imageb + '</a><figcaption><a href="' + link + '" title="' + title + '" class="tinyMCE_OBJ_image">' + title + '</a></figcaption></figure>');
							} else {
								$('#content').html('<a href="' + link + '" title="' + title + '" class="tinyMCE_OBJ_image">' + imageb + '</a>');
							}
							break;
						case 'imagelinkfull':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure class="imageFigure"><a href="' + imagef + '" title="' + title + '" class="tinyMCE_OBJ_full">' + imageb + '</a><figcaption><a href="' + imagef + '" title="' + title + '" class="tinyMCE_OBJ_full">' + title + '</a></figcaption></figure>');
							} else {
								$('#content').html('<a href="' + imagef + '" title="' + title + '" class="tinyMCE_OBJ_full">' + imageb + '</a>');
							}
							break;
						case 'link':
							$('#content').html('<a href="' + link + '" title="' + title + '" class="tinyMCE_OBJ">' + title + ' </a>');
							break;
						case 'thumblink2':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure class="thumbFigure"><a href="' + link2 + '" title="' + title + '" class="tinyMCE_OBJ_thumb">' + image + '</a>' + '<figcaption><a href="' + link2 + '" title="' + title + '" class="tinyMCE_OBJ_thumb">' + title + '</a></figcaption>' + '</figure>');
							} else {
								$('#content').html('<a href="' + link2 + '" title="' + title + '" class="tinyMCE_OBJ_thumb">' + image + '</a>');
							}
							break;
						case 'thumblinkfull':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure class="thumbFigure"><a href="' + imagef + '" title="' + title + '" class="tinyMCE_OBJ_full">' + image + '</a>' + '<figcaption><a href="' + imagef + '" title="' + title + '" class="tinyMCE_OBJ_full">' + title + '</a></figcaption>' + '</figure>');
							} else {
								$('#content').html('<a href="' + imagef + '" title="' + title + '" class="tinyMCE_OBJ_full">' + image + '</a>');
							}
							break;
						case 'link2':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure class="imageFigure"><a href="' + link2 + '" title="' + title + '" class="tinyMCE_OBJ_image">' + imageb + '</a>' + '<figcaption><a href="' + link2 + '" title="' + title + '" class="tinyMCE_OBJ">' + title + '</a></figcaption>' + '</figure>');
							} else {
								$('#content').html('<a href="' + link2 + '" title="' + title + '" class="tinyMCE_OBJ_image">' + imageb + '</a>');
							}
							break;
					}
				}

				function paste() {
					if ($('#imagesize').val() != <?php echo $size; ?>) {
						$.ajax({
							type: 'POST',
							cache: false,
							data: 'pasteImageSize=' + $('#imagesize').val(),
							url: '<?php echo WEBPATH . '/' . ZENFOLDER; ?>/pickSource.php'
						});
					}
					pasteObjPopup.execCommand('mceInsertContent', false, $('#content').html());
					pasteObjPopup.close();
				}

				window.addEventListener('load', zenchange, false);

				// ]]> -->
			</script>
			<h3>
				<span class="buttons">
					<button type="button" title="<?php echo gettext('paste'); ?>" onclick="paste();">
						<?php echo CHECKMARK_GREEN; ?>
						<?php echo gettext('paste'); ?>
					</button>
				</span>
				<?php printf($title, html_encodeTagged($obj->getTitle())); ?>

			</h3>
			<p>
				<?php
				if ($image) {
					if (!$picture) {
						?>
						<label class="nowrap"><input type="radio" name="link" value="thumb" id="link_thumb_none" onchange="zenchange();" /><?php echo gettext('thumb only'); ?></label>
						<label class="nowrap"><input type="radio" name="link" value="thumblink" id="link_thumb_image" checked="checked" onchange="zenchange();" /><?php printf($token, 'thumb'); ?>
						</label>
						<?php
						if ($imagef) {
							if (isImagePhoto($obj)) {
								?>
								<label class="nowrap"><input type="radio" name="link" value="thumblinkfull" id="link_thumb_full" onchange="zenchange();" /><?php echo gettext('thumb with link to full-sized image'); ?></label>
								<?php
							}
						}
						if ($link2) {
							?>
							<label class="nowrap">
								<input type="radio" name="link" value="thumblink2" id="link_thumb_album" onchange="zenchange();" />
								<?php echo gettext('thumb with link to album'); ?>
							</label>
							<?php
						}
						?>
						<br />
						<?php
					}
					if (isImagePhoto($obj)) {
						?>
						<label class="nowrap"><input type="radio" name="link" value="image" id="link_image_none" onchange="zenchange();" /><?php echo gettext('image only'); ?></label>
						<label class="nowrap"><input type="radio" name="link" value="imagelink" id="link_image_image"<?php if ($picture) echo 'checked="checked"'; ?> onchange="zenchange();" /><?php printf($token, 'image'); ?>
						</label>
						<?php
						if ($imagef) {
							?>
							<label class="nowrap"><input type="radio" name="link" value="imagelinkfull" id="link_image_full" onchange="zenchange();" /><?php echo gettext('image with link to full-sized image'); ?></label>
							<?php
						}
						?>
						<?php
						if ($link2) {
							?>
							<label class="nowrap">
								<input type="radio" name="link" value="link2" id="link_image_album" onchange="zenchange();" />
								<?php echo gettext('image with link to album'); ?>
							</label>
							<?php
						}
					} elseif (isImageVideo($obj)) {
						$content_macros = getMacros();
						if (array_key_exists('MEDIAPLAYER', $content_macros)) {
							?>
							<label class="nowrap"><input type="radio" name="link" value="player" id="link_image_none" onchange="zenchange();" /><?php echo gettext('Mediaplayer macro'); ?></label>
							<?php
						}
					} else if (!$imagef) {
						$content_macros = getMacros();
						if (array_key_exists('SLIDESHOW', $content_macros)) {
							?>
							<label class="nowrap"><input type="radio" name="link" value="show" id="link_image_none" onchange="zenchange();" /><?php echo gettext('Slideshow macro'); ?></label>
							<?php
						}
					}
					?>

					<br />
					<?php
					if ($picture) {
						?>
						<input type="hidden" size="4" name="image_size" id="imagesize" value="<?php echo $size; ?>" />
						<?php
					} else {
						?>
						<input type="text" size="4" name="image_size" id="imagesize" value="<?php echo $size; ?>" onchange="zenchange();" />px
						<?php
					}
					?>
					<label><input type="checkbox" name="addcaption" id="addcaption" onchange="zenchange()"	/><?php echo gettext('Include caption'); ?></label>
					<?php
				} else {
					?>
					<label class="nowrap"><input type="radio" name="link" value="title" id="link_title" onchange="zenchange();" /><?php echo gettext('title only'); ?></label>
					<label class="nowrap"><input type="radio" name="link" value="link" id="link_on" checked="checked" onchange="zenchange();" /><?php echo $token; ?>
					</label>
					<?php
				}
				?>
			</p>

			<div id="content"></div>
			<?php
			if ($image && !$picture && isImagePhoto($obj)) {
				?>
				<a href="javascript:launchScript('<?php echo WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/crop_image.php',['a=<?php echo str_replace('%27', "\'", pathurlencode($args['album'])); ?>','i=<?php echo str_replace('%27', "\'", urlencode($args['image'])); ?>','performcrop=pasteobj','size='+$('#imagesize').val()]);" title="<?php echo gettext('Click to bring up the custom cropping page.'); ?>">
					<img src="<?php echo WEBPATH . "/" . ZENFOLDER . '/'; ?>images/shape_handles.png" alt="" /><?php echo gettext("Custom crop"); ?></a>
				<?php
			}
		} else {
			?>
			<p>
				<?php echo gettext('No object source has been chosen.'); ?>
			</p>
			<p>
				<?php printf(gettext('You can pick a ZenPhoto20 object for insertion by browsing to the object and clicking on the %s icon. This icon will be just below the <em>Title</em> of the object. You can quickly select objects from admin pages that list objects (e.g. the <em>albums</em>, <em>news</em>, <em>categories</em>, or <em>pages</em> tabs or the <em>image order</em> album subtab.) A <em>pick</em> icon is provided for each item in the list.'), PLUS_ICON); ?>
			</p>
			<?php
		}
		?>
	</body>
</html>