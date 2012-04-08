<?php
/**
 * Provides functionality to list (or get) objects related to the current object based on a search of the tags
 * the assigned to the current object.
 *
 * @package plugins
 */

$plugin_description = gettext('Provides functionality to get the related items to an item based on a tag search.');
$plugin_author = "Malte Müller (acrylian)";

/**
 * Gets the related items based on a tag search sorted by date descending (newest to oldest)
 *
 * @param string $type 'albums', 'images','news','pages'
 * @param string $album If $type = 'albums' or 'images' name of album
 */
function getRelatedItems($type='news',$album=NULL) {
	global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_page, $_zp_current_zenpage_news;
	$tags = getTags();
	if(!empty($tags)) { // if there are tags at all
		$searchstring = '';
		$count = '';
		foreach($tags as $tag) {
			$count++;
			if($count == 1) {
				$bool = '';
			} else {
				$bool = '|'; // connect tags by OR to get a wide range
			}
			$searchstring .= $bool.$tag;
		}
		$paramstr = urlencode('words').'='.$searchstring.'&searchfields=tags';
		if(!is_null($album)) {
			$paramstr = '&albumname='.urlencode($album);
		}
		$search = new SearchEngine();
		$search->setSearchParams($paramstr);
		switch($type) {
			case 'albums':
				$result = $search->getAlbums(0,"date","desc");
				break;
			case 'images':
				$result = $search->getImages(0,0,'date','desc');
				break;
			case 'news':
				$result = $search->getArticles(0,NULL,true,"date","desc");
				break;
			case 'pages':
				$result = $search->getPages();
				break;
		}
		return $result;
	}
	return array();
}


/**
 * Prints the x related articles based on a tag search
 *
 * @param int $number Number of items to get
 * @param string $type 'albums', 'images','news','pages'
 * @param string $specific If $type = 'albums' or 'images' name of album, if $type = 'news' name of category
 */
function printRelatedItems($number=5,$type='news',$specific=NULL) {
	global $_zp_gallery, $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_page, $_zp_current_zenpage_news;
	$label = array('albums'=>gettext('Albums'), 'images'=>gettext('Images'),'news'=>gettext('News'),'pages'=>gettext('Pages'));

$type = 'albums';

	$result = getRelatedItems($type,$specific);
	$count = 0;
	foreach($result as $item) {
		switch($type) {
			case 'albums':
				$obj = new Album($_zp_gallery,$item);
				$current = @$_zp_current_album;
				break;
			case 'images':
				$obj = newImage(NULL,$item);
				$current = @$_zp_current_image;
				break;
			case 'news':
				$obj = new ZenpageNews($item);
				$current = @$_zp_current_zenpage_news;
				break;
			case 'pages':
				$obj = new ZenpagePage($item);
				$current = @$_zp_current_zenpage_page;
				break;
		}
		if(!$current || $current->getID() != $obj->getID()) { // avoid listing the item itself
			if (!$count) {
				?>
				<h3 class="relateditems"><?php printf(gettext('Related %s'),$label[$type]); ?></h3>
				<ul id="relateditems">
				<?php
			}
			$count++;
			?>
			<li>
			<?php
				$category = '';
				switch($type) {
					case 'albums':
						$url = $obj->getAlbumLink();
						break;
					case 'images':
						$url = $obj->getImageLink();
						break;
					case 'news':
						$url = getNewsURL($obj->getTitlelink());
						if($_zp_current_zenpage_news->inNewsCategory('extensions')) {
							$category = '<small> (Extensions)</small>';
						} else if($_zp_current_zenpage_news->inNewsCategory('user-guide')) {
							$category = '<small> (User guide)</small>';
						} else if($_zp_current_zenpage_news->inNewsCategory('news')) {
							$category = '<small> (News)</small>';
						}
						break;
					case 'pages':
						$url = getPageLinkURL($obj->getTitlelink());
						break;
				}
			?>
			<a href="<?php echo html_encode($url); ?>" title="<?php echo html_encode($obj->getTitle()); ?>"><?php echo html_encode($obj->getTitle()).$category; ?></a>
			</h4></li>
			<?php
		}
		if($count == $number) {
			break;
		}
	} // foreach
	if ($count) {
		?>
		</ul>
		<?php
	}
}
?>