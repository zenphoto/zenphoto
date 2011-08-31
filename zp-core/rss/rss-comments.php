<?php
$host = getRSSHost();
$protocol = SERVER_PROTOCOL;
if ($protocol == 'https_admin') {
	$protocol = 'http';
}
$locale = getRSSLocale();
$validlocale = getRSSLocaleXML();
$modrewritesuffix = getRSSImageAndAlbumPaths("modrewritesuffix");
if(getOption('zp_plugin_zenpage')) {
	require_once(ZENFOLDER . '/'.PLUGIN_FOLDER. "/zenpage/zenpage-template-functions.php");
}
header('Content-Type: application/xml');
$id = getRSSID() ;
$title = getRSSTitle();
if(!empty($title)) {
	$title = " - ".html_encode($title);
}
$type = getRSSType();
$albumpath = getRSSImageAndAlbumPaths("albumpath");
$imagepath = getRSSImageAndAlbumPaths("imagepath");
$items = getOption('feed_items'); // # of Items displayed on the feed
$gallery = new Gallery();
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title><?php echo strip_tags(get_language_string($gallery->get('gallery_title'), $locale)).$title.gettext(' (latest comments)'); ?></title>
<link><?php echo $protocol."://".$host.WEBPATH; ?></link>
<atom:link href="<?php echo $protocol; ?>://<?php echo html_encode($_SERVER["HTTP_HOST"]); ?><?php echo html_encode($_SERVER["REQUEST_URI"]); ?>" rel="self"	type="application/rss+xml" />
<description><?php echo strip_tags(get_language_string($gallery->get('Gallery_description'), $locale)); ?></description>
<language><?php echo $validlocale; ?></language>
<pubDate><?php echo date("r", time()); ?></pubDate>
<lastBuildDate><?php echo date("r", time()); ?></lastBuildDate>
<docs>http://blogs.law.harvard.edu/tech/rss</docs>
<generator>Zenphoto Comment RSS Generator</generator>
<?php
$comments = array();
switch($type) {
	case 'gallery':
	case 'album':
	case 'image':
		if($type == 'gallery') {
			$type = 'all';
		}
		$comments = getLatestComments($items,$type,$id);
		break;
	case 'zenpage':
	case 'news':
	case 'page':
		if($type == 'zenpage') {
			$type = 'all';
		}
		if(function_exists('getLatestZenpageComments')) {
			$comments = getLatestZenpageComments($items,$type,$id);
		}
		break;
	case 'allcomments':
		$type = 'all';
		$comments= getLatestComments($items,$type,$id);
		$comments_zenpage = array();
		if(function_exists('getLatestZenpageComments')) {
			$comments_zenpage = getLatestZenpageComments($items,$type,$id);
			$comments = array_merge($comments,$comments_zenpage);
			$comments = sortMultiArray($comments,'id',true);
			$comments = array_slice($comments,0,$items);
		}
		break;
}
foreach ($comments as $comment) {
	if($comment['anon']) {
		$author = "";
	} else {
		$author = " ".gettext("by")." ".$comment['name'];
	}
	$imagetag = "";
	$title = '';
	switch($comment['type']) {
		case 'images':
			$title = get_language_string($comment['title']);
			$imagetag = $imagepath.$comment['filename'].$modrewritesuffix;
		case 'albums':
			$album = pathurlencode($comment['folder']);
			$date = $comment['date'];
			$category = $comment['albumtitle'];
			$website = $comment['website'];
			if($comment['type'] == 'albums') {
				$title = $category;
			} else {
				$title = $category.": ".$title;
			}
			$commentpath = $protocol.'://'.$host.WEBPATH.$albumpath.$album.$imagetag."#".$comment['id'];
			break;
		case 'news':
		case 'pages':
			$album = '';
			$date = $comment['date'];
			$category = '';
			$title = get_language_string($comment['title']);
			$titlelink = $comment['titlelink'];
			$website = $comment['website'];
			if(function_exists('getNewsURL')) {
				if ($comment['type']=='news') {
					$commentpath = $protocol.'://'.$host.getNewsURL($titlelink)."#".$comment['id'];
				} else {
					$commentpath = $protocol.'://'.$host.getPageLinkURL($titlelink)."#".$comment['id'];
				}
			} else {
				$commentpath = '';
			}
			break;
	}
?>
<item>
<title><?php echo html_encode(strip_tags($title.$author)); ?></title>
<link><?php echo '<![CDATA['.html_encode($commentpath).']]>';?></link>
<description><?php echo html_encode($comment['comment']); ?></description>
<guid><?php echo '<![CDATA['.html_encode($commentpath).']]>';?></guid>
<pubDate><?php echo date("r",strtotime($date)); ?></pubDate>
</item>
<?php
}
?>
</channel>
</rss>