<?php
/**
 * ZenPhoto20 object paster for tinyMCE
 *
 * @author Stephen Billard (sbillard)
 *
 * copyright © 2014 Stephen L Billard
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
		<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/zenphoto.js"></script>
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
					$obj = newImage(NULL, array('folder' => $args['album'], 'filename' => $args['image']));
					$title = gettext('<em>image</em>: %s');
					$token = gettext('%s with link to image');
					if ($picture) {
						$imageb = $image = $args['picture'];
					} else {
						$image = $obj->getThumb();
						$imageb = preg_replace('~&check=(.*)~', '', getIPSizedImage($size, $obj));
					}
				} else {
					$obj = newAlbum($args['album']);
					$title = gettext('<em>album</em>: %s');
					$token = gettext('%s with link to album');
					$image = $obj->getThumb();
					$thumbobj = $obj->getAlbumThumbImage();
					$args['image'] = $thumbobj->getFilename();
					$imageb = preg_replace('~check=(.*)~', '', getIPSizedImage($size, $thumbobj));
				}
				// an image type object
			} else {
				// a simple link
				$imageb = $image = NULL;
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
			if ($image && $obj->table == 'images') {
				$link2 = $obj->album->getLink();
			} else {
				$link2 = false;
			}
			?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				var link = '<?php echo $link; ?>';
				var link2 = '<?php echo $link2; ?>';
				var title = '<?php echo html_encodeTagged($obj->getTitle()); ?>';
				var image = '<?php echo $image; ?>';
				var imagec = '<?php echo $imageb; ?>';
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
								$('#content').html('<figure><img src="' + image + '" /><figcaption>' + title + '</figcaption></figure>');
							} else {
								$('#content').html('<img src="' + image + '" />');
							}
							break;
						default:
						case 'image':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure><img src="' + imageb + '" /><figcaption>' + title + '</figcaption></figure>');
							} else {
								$('#content').html('<img src="' + imageb + '" />');
							}
							break;
						case 'title':
							if (image) {
								$('#content').html('<a href="">' + title + '</a>');
							} else {
								$('#content').html(title);
							}
							break;
						case 'thumblink':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure><a href="' + link + '" title="' + title + '"><img src="' + image + '" /></a><figcaption><a href="' + link + '" title="' + title + '">' + title + '</a></figcaption></figure>');
							} else {
								$('#content').html('<a href="' + link + '" title="' + title + '"><img src="' + image + '" /></a>');
							}
							break;
						case 'imagelink':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure><a href="' + link + '" title="' + title + '"><img src="' + imageb + '" /></a><figcaption><a href="' + link + '" title="' + title + '">' + title + '</a></figcaption></figure>');
							} else {
								$('#content').html('<a href="' + link + '" title="' + title + '"><img src="' + imageb + '" /></a>');
							}
							break;
						case 'link':
							$('#content').html('<a href="' + link + '" title="' + title + '">' + title + ' </a>');
							break;
						case 'thumblink2':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure><a href="' + link2 + '" title="' + title + '"><img src="' + image + '" /></a>' + '<figcaption><a href="' + link2 + '" title="' + title + '">' + title + '</a></figcaption>' + '</figure>');
							} else {
								$('#content').html('<a href="' + link2 + '" title="' + title + '"><img src="' + image + '" /></a>');
							}
							break;
						case 'link2':
							if ($('#addcaption').prop('checked')) {
								$('#content').html('<figure><a href="' + link2 + '" title="' + title + '"><img src="' + imageb + '" /></a>' + '<figcaption><a href="' + link2 + '" title="' + title + '">' + title + '</a></figcaption>' + '</figure>');
							} else {
								$('#content').html('<a href="' + link2 + '" title="' + title + '"><img src="' + imageb + '" /></a>');
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

				window.onload = function() {
					zenchange();
				};

				// ]]> -->
			</script>
			<h3>
				<span class="buttons">
					<button type="button" title="<?php echo gettext('paste'); ?>" onclick="paste();">
						<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" onclick="paste();"  />
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
						<label class="nowrap"><input type="radio" name="link" value="thumb" id="link_none" onchange="zenchange();" /><?php echo gettext('thumb only'); ?></label>
						<label class="nowrap"><input type="radio" name="link" value="thumblink" id="link_on" onchange="zenchange();" /><?php printf($token, 'thumb'); ?>
						</label>
						<?php
						if ($link2) {
							?>
							<label class="nowrap">
								<input type="radio" name="link" value="thumblink2" id="link_album" onchange="zenchange();" />
								<?php echo gettext('thumb with link to album'); ?>
							</label>
							<?php
						}
						?>
						<br />
						<?php
					}
					?>
					<label class="nowrap"><input type="radio" name="link" value="image" id="link_none" checked="checked" onchange="zenchange();" /><?php echo gettext('image only'); ?></label>
					<label class="nowrap"><input type="radio" name="link" value="imagelink" id="link_on" onchange="zenchange();" /><?php printf($token, 'image'); ?>
					</label>
					<?php
					if ($link2) {
						?>
						<label class="nowrap">
							<input type="radio" name="link" value="link2" id="link_album" onchange="zenchange();" />
							<?php echo gettext('image with link to album'); ?>
						</label>
						<?php
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
			if ($image && !$picture) {
				?>
				<a href="javascript:launchScript('<?php echo WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/crop_image.php',['a=<?php echo pathurlencode($args['album']); ?>','i=<?php echo urlencode($args['image']); ?>','performcrop=pasteobj','size='+$('#imagesize').val()]);" title="<?php echo gettext('Click to bring up the custom cropping page.'); ?>">
					<img src="<?php echo WEBPATH . "/" . ZENFOLDER . '/'; ?>images/shape_handles.png" alt="" /><?php echo gettext("Custom crop"); ?></a>
				<?php
			}
		} else {
			?>
			<p>
				<?php printf(gettext('No source has been picked. You can pick a ZenPhoto20 object for insertion by browsing to the object and clicking on the %s icon. Custom sized and cropped images may be picked from the <em>crop image</em> page if the <code>crop_image</code> plugin is enabled.'), '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/add.png" />'); ?>
			</p>
			<?php
		}
		?>
	</body>
</html>