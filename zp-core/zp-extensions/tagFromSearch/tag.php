<?php
/**
 * This script is used to create dynamic albums from a search.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package core
 */
// force UTF-8 Ø

define('OFFSET_PATH', 3);

require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');

admin_securityChecks(TAGS_RIGHTS, $return = currentRelativeURL());

$search = new SearchEngine(true);
$fields = $search->fieldList;
$words = $search->codifySearchString();

$images = $search->getImages(0);
$albums = $search->getAlbums(0);
$count = count($images) + count($albums);
if (extensionEnabled('zenpage')) {
	$articles = $search->getArticles();
	$pages = $search->getPages();
	$count = $count + count($articles) + count($pages);
} else {
	$articles = $pages = array();
}


printAdminHeader('tags');
echo "\n</head>";
echo "\n<body>";
printLogoAndLinks();
echo "\n" . '<div id="main">';
printTabs();
echo "\n" . '<div id="content">';
if (isset($_GET['tagitems'])) {
	?>
	<div class="messagebox fade-message">
		<?php printf(ngettext('%d search item taggec', '%d search items tagged', $count), $count); ?>
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
		function addSuggestedTag(tag) {
			var name = 'tags_' + bin2hex(tag);
			if ($('#' + name).length) {
				$('#' + name + '_element').remove();
			}
			html = '<li id="' + name + '_element"><label class="displayinline"><input id="' + name + '" name="' + name +
							'" type="checkbox" checked="checked" value="1" />' + tag + '</label></li>';
			$('#list_tags_').prepend(html);
		}
		$(document).ready(function () {
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
						addSuggestedTag('<?php echo $singlesearchstring; ?>');
				<?php
				break;
		}
	}
	?>
		});

	</script>
	<?php
}
?>
<h1>
	<?php printf(ngettext('Tag %d search item', 'Tag %d search items', $count), $count); ?>
</h1>
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
			<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/arrow_left_blue_round.png" alt="" />
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

	$tags = array();
	foreach ($_POST as $key => $value) {
		if (substr($key, 0, 5) == 'tags_') {
			if ($value) {
				$tags[] = sanitize(postIndexDecode(substr($key, 5)));
			}
		}
	}
	$tags = array_unique($tags);

	foreach (array('newImage' => $images, 'newAlbum' => $albums, 'newArticle' => $articles, 'newPage' => $pages) as $instantiate => $list) {
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
	tagSelector(NULL, 'tags_');
	?>
	<p class="buttons">
		<button type="submit"  title="<?php echo gettext("Tag the items"); ?>">
			<img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" />
			<?php echo gettext("Tag the items"); ?>
		</button>
	</p>
	<p class="buttons">
		<button type="reset">
			<img	src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" alt="" />
			<strong><?php echo gettext("Reset"); ?></strong>
		</button>
	</p>

</table>


</form>

<?php
echo "\n" . '</div>';
echo "\n" . '</div>';

printAdminFooter();

echo "\n</body>";
echo "\n</html>";
?>

