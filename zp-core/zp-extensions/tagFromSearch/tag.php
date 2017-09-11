<?php
/**
 * This script is used tag items from a search.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package core
 *
 * Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 *
 */
// force UTF-8 Ø

define('OFFSET_PATH', 3);

require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');

admin_securityChecks(TAGS_RIGHTS, $return = currentRelativeURL());

$search = new SearchEngine(true);
$fields = $search->fieldList;
$words = $search->codifySearchString();

$imagechecked = !isset($_POST['XSRFToken']) || @$_POST['image_tag'];
$albumchecked = !isset($_POST['XSRFToken']) || @$_POST['album_tag'];
$articlechecked = !isset($_POST['XSRFToken']) || @$_POST['article_tag'];
$pagechecked = !isset($_POST['XSRFToken']) || @$_POST['page_tag'];

$images = $search->getImages(0);
$albums = $search->getAlbums(0);

$count = 0;
if ($imagechecked) {
	$count = $count + count($images);
}

if ($albumchecked) {
	$count = $count + count($albums);
}

if (extensionEnabled('zenpage')) {
	$articles = $search->getArticles();
	$pages = $search->getPages();

	if ($articlechecked) {
		$count = $count + count($articles);
	}

	if ($pagechecked) {
		$count = $count + count($pages);
	}
} else {
	$articles = $pages = array();
}

printAdminHeader('tags');
echo "\n</head>";
?>

<body>
	<?php
	printLogoAndLinks();
	?>

	<div id="main">
		<?php
		printTabs();
		?>
		<div id="content">
			<?php
			if (isset($_GET['tagitems'])) {
				?>
				<div class="messagebox fade-message">
					<?php printf(ngettext('%d search item tagged', '%d search items tagged', $count), $count); ?>
				</div>
				<?php
			}
			if (MOD_REWRITE) {
				$searchurl = SEO_WEBPATH . '/' . _SEARCH_ . '/';
			} else {
				$searchurl = WEBPATH . "/index.php?p=search";
			}

			$searchstring = $search->getSearchString();
			if (is_array($searchstring)) {
				?>
				<script type="text/javascript">
					function addSearchTag(tag) {
						if (tag) {
							var name = 'tags_' + bin2hex(tag);
							if ($('#' + name).length) {
								$('#' + name + '_element').remove();
							}
							html = '<li id="' + name + '_element"><label class="displayinline"><input id="' + name + '" name="tag_list_tags_[]" type="checkbox" checked="checked" value="' + tag + '" />' + tag + '</label></li>';
							$('#list_tags_').prepend(html);
						}
					}

					window.addEventListener('load', function () {
	<?php
	foreach ($searchstring as $key => $singlesearchstring) {
		switch ($singlesearchstring) {
			case '&':
			case '|':
			case '!':
			case '(':
			case ')':
				break;
			default:
				?>
									addSearchTag('<?php echo $singlesearchstring; ?>');
				<?php
				break;
		}
	}
	?>
					}, false);

				</script>
				<?php
			}
			?>
			<h1>
				<?php echo ngettext('Tag search item', 'Tag search items', $count); ?>
			</h1>
			<div class="tabbox">
				<form method="post" action="<?php echo $searchurl; ?>" id="search_form">
					<input type="hidden" name="words" value="<?php echo html_encode($words); ?>" />
					<?php
					foreach ($fields as $display => $key) {
						?>
						<input type="hidden" name="SEARCH_<?php echo $key; ?>" value="<?php echo $key; ?>"  />
						<?php
					}
					?>
					<p class = "buttons">
						<button type="submit" title="<?php echo gettext("Return to search"); ?>" >
							<?php echo BACK_ARROW_BLUE; ?>
							<?php echo gettext("Back");
							?>
						</button>
					</p>
				</form>
				<br clear="all" />
				<br clear="all" />
				<?php
				if (isset($_GET['tagitems'])) {
					XSRFdefender('tagitems');
					if (isset($_POST['tag_list_tags_'])) {
						$tags = sanitize($_POST['tag_list_tags_']);
					} else {
						$tags = array();
					}
					$tags = array_unique($tags);
					$totag = array();
					if ($imagechecked)
						$totag['newImage'] = $images;
					if ($albumchecked)
						$totag['newAlbum'] = array_merge($albums);
					if ($articlechecked)
						$totag['newArticle'] = $articles;
					if ($pagechecked)
						$totag['newPage'] = $pages;

					foreach ($totag as $instantiate => $list) {
						foreach ($list as $item) {
							$obj = $instantiate($item);
							addTags($tags, $obj);
							$obj->save();
						}
					}
				}
				?>
				<form class="dirtylistening" onReset="setClean('tagitems_form');" id="tagitems_form" action="?tagitems" method="post" >
					<?php XSRFToken('tagitems'); ?>
					<input type="hidden" name="words" value="<?php echo html_encode($words); ?>" />
					<?php
					foreach ($fields as $display => $key) {
						?>
						<input type="hidden" name="SEARCH_<?php echo $key; ?>" value="<?php echo $key; ?>"  />
						<?php
					}
					?>
					<div class="floatleft" style="width:25em;">
						<ul class="no_bullets">
							<?php
							if (count($images) > 0) {
								?>
								<li>
									<input name="image_tag" type="checkbox" value="1" <?php if ($imagechecked) echo ' checked="checked"'; ?> /><?php printf(ngettext('Tag %d image', 'Tag %d images', $c = count($images)), $c); ?>
								</li>
								<?php
							}
							if (count($albums) > 0) {
								?>
								<li>
									<input name="album_tag" type="checkbox" value="1" <?php if ($albumchecked) echo ' checked="checked"'; ?> /><?php printf(ngettext('Tag %d album', 'Tag %d albums', $c = count($albums)), $c); ?>
								</li>
								<?php
							}
							if (count($articles) > 0) {
								?>
								<li>
									<input name="article_tag" type="checkbox" value="1" <?php if ($articlechecked) echo ' checked="checked"'; ?> /><?php printf(ngettext('Tag %d article', 'Tag %d articles', $c = count($articles)), $c); ?>
								</li>
								<?php
							}
							if (count($pages) > 0) {
								?>
								<li>
									<input name="page_tag" type="checkbox" value="1" <?php if ($pagechecked) echo ' checked="checked"'; ?> /><?php printf(ngettext('Tag %d page', 'Tag %d pages', $c = count($pages)), $c); ?>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
					<div >
						<?php tagSelector(NULL, 'tags_'); ?>
					</div>
					<br clear="all">
					<p class="buttons">
						<button type="submit"  title="<?php echo gettext("Tag the items"); ?>">
							<?php echo CHECKMARK_GREEN; ?>
							<?php echo gettext("Tag the items"); ?>
						</button>
					</p>
					<p class="buttons">
						<button type="reset">
							<?php echo CROSS_MARK_RED; ?>
							<strong><?php echo gettext("Reset"); ?></strong>
						</button>
					</p>
					<br class="clearall">
					</table>


				</form>
			</div>
		</div>
	</div>
	<?php
	printAdminFooter();
	?>
</body>
<?php echo "\n</html>"; ?>