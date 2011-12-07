<?php
$host = getRSSHost();
$channeltitle = getRSSChanneltitle();
$protocol = SERVER_PROTOCOL;
if ($protocol == 'https_admin') {
	$protocol = 'http';
}
$locale = getRSSLocale();
$validlocale = getRSSLocaleXML();
$modrewritesuffix = getRSSImageAndAlbumPaths("modrewritesuffix");
require_once(ZENFOLDER . '/'.PLUGIN_FOLDER . "/image_album_statistics.php");
require_once(ZENFOLDER . '/'.PLUGIN_FOLDER . "/zenpage/zenpage-template-functions.php");
require_once(ZENFOLDER . '/'.PLUGIN_FOLDER . "/image_album_statistics.php");
require_once(ZENFOLDER .  "/lib-MimeTypes.php");
header('Content-Type: application/xml');
$themepath = THEMEFOLDER;
$catlink = getRSSNewsCatOptions("catlink");
$cattitle = getRSSNewsCatOptions("cattitle");
if(!empty($cattitle)) { 
	$cattitle = ' - '.html_encode($cattitle) ; 
}
$option = getRSSNewsCatOptions("option");
$titleappendix = gettext(' (Latest news)');
if (isset($_GET['withimages'])) {
	$option = "withimages";
	$titleappendix = gettext(' (Latest news and images)');
}
$s = getOption('feed_imagesize'); // un-cropped image size
$items = getOption("zenpage_rss_items"); // # of Items displayed on the feed
$gallery = new Gallery();
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title><?php echo html_encode($channeltitle.$cattitle.$titleappendix); ?></title>
<link><?php echo $protocol."://".$host.WEBPATH; ?></link>
<atom:link href="<?php echo $protocol; ?>://<?php echo html_encode($_SERVER["HTTP_HOST"]); ?><?php echo html_encode($_SERVER["REQUEST_URI"]); ?>" rel="self" type="application/rss+xml" />
<description><?php echo html_encode(strip_tags(get_language_string($gallery->get('Gallery_description'), $locale))); ?></description>
<language><?php echo $validlocale; ?></language>
<pubDate><?php echo date("r", time()); ?></pubDate>
<lastBuildDate><?php echo date("r", time()); ?></lastBuildDate>
<docs>http://blogs.law.harvard.edu/tech/rss</docs>
<generator>Zenpage - A CMS plugin for ZenPhoto</generator>
<?php
switch ($option) {
	case "category":
		$latest = getLatestNews($items,"none",$catlink);
		break;
	case "news":
		$latest = getLatestNews($items,"none");
		break;
	case "withimages":
		$latest = getLatestNews($items,"with_latest_images_date");
		break;
}
$count = "";
foreach($latest as $item) {
	$count++;
	$category = "";
	$categories = "";
	//get the type of the news item
	switch($item['type']) {
		case 'news':
			$obj = new ZenpageNews($item['titlelink']);
			$title = get_language_string($obj->get('title'),$locale);
			$link = getNewsURL($obj->getTitlelink());
			$count2 = 0;
			$category = $obj->getCategories();
			foreach($category as $cat){
				$count2++;
				if($count2 != 1) {
					$categories = $categories.", ";
				}
				$categories = $categories.get_language_string($cat['titlelink'], $locale);
			}
			$thumb = "";
			$filename = "";
			if(getOption('zenpage_rss_length') == "") { // empty value means full content!
				$content = get_language_string($obj->get('content'),$locale);
			} else {
				$content = shortenContent(get_language_string($obj->get('content'),$locale),getOption('zenpage_rss_length'), $elipsis='...');
			}
			$content = '<![CDATA['.$content.']]>';
			$type = "news";
			$ext = "";
			$album = "";
			break;
		case 'images':
			$albumobj = new Album($_zp_gallery,$item['albumname']);
			$obj = newImage($albumobj,$item['titlelink']);
			$categories = get_language_string($albumobj->get('title'),$locale);
			$title = strip_tags(get_language_string($obj->get('title'),$locale));
			$link = $obj->getImageLink();
			$type = "image";
			$filename = $obj->getFilename();
			$ext = getSuffix($filename);
			$album = $albumobj->getFolder();
			$fullimagelink = $host.WEBPATH."/albums/".$album."/".$filename;
			$imagefile = "albums/".$album."/".$filename;
			$mimetype = getMimeString($ext);
			if(getOption('zenpage_rss_length') == "") { // empty value means full content!
				$content = get_language_string($obj->get('desc'),$locale);
			} else {
				$content = shortenContent(get_language_string($obj->get('desc'),$locale),getOption('zenpage_rss_length'), '...');
			}
			if(isImagePhoto($obj)) {
				$content = '<![CDATA[<a title="'.html_encode($title).' in '.html_encode($categories).'" href="'.$protocol.'://'.$host.$link.'"><img border="0" src="'.$protocol.'://'.$host.WEBPATH.'/'.ZENFOLDER.'/i.php?a='.$album.'&i='.$filename.'&s='.$s.'" alt="'. html_encode($title) .'"></a>' . $content . ']]>';
			} else {
				$content = '<![CDATA[<a title="'.html_encode($title).' in '.html_encode($categories).'" href="'.$protocol.'://'.$host.$link.'"><img src="'.$obj->getThumb().'" alt="'.html_encode($title).'" /></a>'.$content.']]>';
			}
			break;
		case 'albums':
			break;
	}
	if(empty($categories)) {
		$cat_title = '';
	} else {
		$categories = html_encode($categories);
		$cat_title = ' ('.html_encode($categories).')';
	}
?>
<item>
	<title><?php echo html_encode($title).$cat_title; ?></title>
	<link><?php echo '<![CDATA['.$protocol.'://'.$host.$link.']]>';?></link>
	<description>
	<?php echo $content;	?>
</description>
<?php if(getOption("feed_enclosure") AND !empty($item['thumb'])) { ?>
	<enclosure url="<?php echo $protocol; ?>://<?php echo $fullimagelink; ?>" type="<?php echo $mimetype; ?>" length="<?php echo filesize($imagefile); ?>" />
<?php } ?>
    <category><?php echo $categories; ?>
    </category>
	<guid><?php echo '<![CDATA['.$protocol.'://'.$host.$link.']]>';?></guid>
	<pubDate><?php echo date("r",strtotime($item['date'])); ?></pubDate>
</item>
<?php
if($count === $items) {
	break;
}
} ?>
</channel>
</rss>