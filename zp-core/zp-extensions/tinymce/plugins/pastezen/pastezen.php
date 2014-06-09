<?php
/**
 * ZenPhoto20 object paster for tinyMCE
 *
 * @author Stephen Billard (sbillard)
 *
 */
// force UTF-8 Ø
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/admin-globals.php");
admin_securityChecks(ALBUM_RIGHTS | ZENPAGE_PAGES_RIGHTS | ZENPAGE_NEWS_RIGHTS, NULL);

header('Last-Modified: ' . ZP_LAST_MODIFIED);
header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
?>
<!DOCTYPE html>
<html>
	<head>
		<?php printStandardMeta(); ?>
		<title>tinyMCE:zen</title>
		<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/jquery.js"></script>
		<script type="text/javascript" src="pastezen_popup.js"></script>

	</head>

	<body>
		<h2><?php echo gettext('ZenPhoto20 object insertion'); ?></h2>
		<?php
		if (isset($_SESSION['pick'])) {
			$args = $_SESSION['pick'];
			if (isset($args['album'])) {
				if (isset($args['image'])) {
					$obj = newImage(NULL, array('folder' => $args['album'], 'filename' => $args['image']));
					$title = gettext('<em>image</em>: %s');
					$token = gettext('image with link to image');
					if (isset($args['picture'])) {
						$image = $args['picture'];
					} else {
						$image = $obj->getThumb();
					}
				} else {
					$obj = newAlbum($args['album']);
					$title = gettext('<em>album</em>: %s');
					$token = gettext('image with link to album');
					$image = $obj->getThumb();
				}
				// an image type object
			} else {
				// a simple link
				$image = false;
				if (isset($args['news'])) {
					$obj = new ZenpageNews($args['news']);
					$title = gettext('<em>news article</em>: %s');
					$token = gettext('title with link to news article');
				}
				if (isset($args['pages'])) {
					$obj = new ZenpagePage($args['pages']);
					$title = gettext('<em>page</em>: %s');
					$token = gettext('title with link to page');
				}
				if (isset($args['news_categories'])) {
					$obj = new ZenpageCategory($args['news_categories']);
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

				function zenchange() {
					var selectedlink = $('input:radio[name=link]:checked').val();
					switch (selectedlink) {
						case 'none':
							$('#content').html('<img src="' + image + '" />');
							break;
						case 'title':
							if (image) {
								$('#content').html('<a href="">' + title + '</a>');
							} else {
								$('#content').html(title);
							}
							break;
						case 'link':
							if (image) {
								$('#content').html('<a href="' + link + '" title="' + title + '"><img src="' + image + '" /></a>');
							} else {
								$('#content').html('<a href="' + link + '" title="' + title + '">' + title + ' </a>');
							}
							break;
						case 'link2':
							$('#content').html('<a href="' + link2 + '" title="' + title + '"><img src="' + image + '" /></a>');
							break;
					}
				}

				function paste() {
					pasteZenPopup.execCommand('mceInsertContent', false, $('#content').html());
					pasteZenPopup.close();
				}

				window.onload = function() {
					zenchange();
				};
				// ]]> -->
			</script>
			<h3>
				<span class="buttons">
					<button type="button" title="<?php echo gettext('paste'); ?>" onclick="javascript:paste();">
						<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" onclick="paste();"  />
						<?php echo gettext('paste'); ?>
					</button>
				</span>
				<?php printf($title, html_encodeTagged($obj->getTitle())); ?>

			</h3>
			<p>
				<?php
				if ($image) {
					?>
					<label class="nowrap"><input type="radio" name="link" value="none" id="link_none" onchange="zenchange();" /><?php echo gettext('image only'); ?></label>
					<?php
				} else {
					?>
					<label class="nowrap"><input type="radio" name="link" value="title" id="link_title" onchange="zenchange();" /><?php echo gettext('title only'); ?></label>
					<?php
				}
				?>
				<label class="nowrap"><input type="radio" name="link" value="link" id="link_on" checked="checked" onchange="zenchange();" /><?php echo $token; ?>
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
			</p>


			<div id="content"></div>
			<?php
		} else {
			?>
			<p>
				<?php printf(gettext('No source has been picked. You can pick a ZenPhoto20 object for insertion by browsing to the object and clicking on the %s icon.'), '<img src="' . WEBPATH . '/' . ZENFOLDER . '/images/add.png" />'); ?>
			</p>
			<?php
		}
		?>
	</body>
</html>