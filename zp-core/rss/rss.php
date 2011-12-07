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
require_once(ZENFOLDER .  "/lib-MimeTypes.php");
header('Content-Type: application/xml');
$rssmode = getRSSAlbumsmode();
$albumfolder = getRSSAlbumnameAndCollection("albumfolder");
$collection = getRSSAlbumnameAndCollection("collection");
$albumname = getRSSAlbumTitle();
$albumpath = getRSSImageAndAlbumPaths("albumpath");
$imagepath = getRSSImageAndAlbumPaths("imagepath");
$size = getRSSImageSize();
$items = getOption('feed_items'); // # of Items displayed on the feed
$gallery = new Gallery();
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
<channel>
<title><?php echo html_encode($channeltitle.' '.strip_tags($albumname)); ?></title>
<link><?php echo $protocol."://".$host.WEBPATH; ?></link>
<atom:link href="<?php echo $protocol; ?>://<?php echo html_encode($_SERVER["HTTP_HOST"]); ?><?php echo html_encode($_SERVER["REQUEST_URI"]); ?>" rel="self"	type="application/rss+xml" />
<description><?php echo strip_tags(get_language_string($gallery->get('Gallery_description'), $locale)); ?></description>
<language><?php echo $validlocale; ?></language>
<pubDate><?php echo date("r", time()); ?></pubDate>
<lastBuildDate><?php echo date("r", time()); ?></lastBuildDate>
<docs>http://blogs.law.harvard.edu/tech/rss</docs>
<generator>ZenPhoto RSS Generator</generator>
	<?php
	if ($rssmode == "albums") {
		$result = getAlbumStatistic($items,getOption("feed_sortorder_albums"),$albumfolder);
	} else {
		$result = getImageStatistic($items,getOption("feed_sortorder"),$albumfolder,$collection);
	}
	foreach ($result as $item) {
		if($rssmode != "albums") {
			$ext = getSuffix($item->filename);
			$albumobj = $item->getAlbum();
			$itemlink = $host.WEBPATH.$albumpath.pathurlencode($albumobj->name).$imagepath.pathurlencode($item->filename).$modrewritesuffix;
			$fullimagelink = $host.WEBPATH."/albums/".pathurlencode($albumobj->name)."/".$item->filename;
			$imagefile = "albums/".$albumobj->name."/".$item->filename;
			$thumburl = '<img border="0" src="'.$protocol.'://'.$host.$item->getCustomImage($size, NULL, NULL, NULL, NULL, NULL, NULL, TRUE).'" alt="'.get_language_string(get_language_string($item->get("title"),$locale)) .'" /><br />';
			$itemcontent = '<![CDATA[<a title="'.html_encode(get_language_string($item->get("title"),$locale)).' in '.html_encode(get_language_string($albumobj->get("title"),$locale)).'" href="'.$protocol.'://'.$itemlink.'">'.$thumburl.'</a>' . get_language_string(get_language_string($item->get("desc"),$locale)) . ']]>';
			$videocontent = '<![CDATA[<a title="'.html_encode(get_language_string($item->get("title"),$locale)).' in '.html_encode(get_language_string($albumobj->getTitle(),$locale)).'" href="'.$protocol.'://'.$itemlink.'"><img src="'.$protocol.'://'.$host.$item->getThumb().'" alt="'.get_language_string(get_language_string($item->get("title"),$locale)) .'" /></a>' . get_language_string(get_language_string($item->get("desc"),$locale)) . ']]>';
			$datecontent = '<![CDATA[<br />Date: '.zpFormattedDate(DATE_FORMAT,$item->get('mtime')).']]>';
		} else {
			$galleryobj = new Gallery();
			$albumitem = new Album($galleryobj, $item['folder']);
			$totalimages = $albumitem->getNumImages();
			$itemlink = $host.WEBPATH.$albumpath.pathurlencode($albumitem->name);
			$thumb = $albumitem->getAlbumThumbImage();
			$thumburl = '<img border="0" src="'.$thumb->getCustomImage($size, NULL, NULL, NULL, NULL, NULL, NULL, TRUE).'" alt="'.html_encode(get_language_string($albumitem->get("title"),$locale)) .'" />';
			$title =  get_language_string($albumitem->get("title"),$locale);
			if(true || getOption("feed_sortorder_albums") == "latestupdated") {
				$filechangedate = filectime(ALBUM_FOLDER_SERVERPATH.internalToFilesystem($albumitem->name));
				$latestimage = query_single_row("SELECT mtime FROM " . prefix('images'). " WHERE albumid = ".$albumitem->getAlbumID() . " AND `show` = 1 ORDER BY id DESC");
				$count = db_count('images',"WHERE albumid = ".$albumitem->getAlbumID() . " AND mtime = ". $latestimage['mtime']);
				if($count == 1) {
					$imagenumber = sprintf(gettext('%s (1 new image)'),$title);
				} else {
					$imagenumber = sprintf(gettext('%1$s (%2$s new images)'),$title,$count);
				}
				$itemcontent = '<![CDATA[<a title="'.$title.'" href="'.$protocol.'://'.$itemlink.'">'.$thumburl.'</a>'.
						'<p>'.html_encode($imagenumber).'</p>'.html_encode(get_language_string($albumitem->get("desc"),$locale)).']]>';
				$videocontent = '';
				$datecontent = '<![CDATA['.sprintf(gettext("Last update: %s"),zpFormattedDate(DATE_FORMAT,$filechangedate)).']]>';
			} else {
				if($totalimages == 1) {
					$imagenumber = sprintf(gettext('%s (1 image)'),$title);
				} else {
					$imagenumber = sprintf(gettext('%1$s (%2$s images)'),$title,$totalimages);
				}
				$itemcontent = '<![CDATA[<a title="'.html_encode($title).'" href="'.$protocol.'://'.$itemlink.'">'.$thumburl.'</a>'.html_encode(get_language_string($albumitem->get("desc"),$locale)).']]>';
				$datecontent = '<![CDATA['.sprintf(gettext("Date: %s"),zpFormattedDate(DATE_FORMAT,$albumitem->get('mtime'))).']]>';
			}
			$ext = getSuffix($thumb->filename);
		}
		$mimetype = getMimeString($ext);
		?>
<item>
<title><?php
if($rssmode != "albums") {
	html_encode(printf('%1$s (%2$s)', get_language_string($item->get("title"),$locale), get_language_string($albumobj->get("title"),$locale)));
} else {
	echo html_encode($imagenumber);
}
?></title>
<link>
<?php echo '<![CDATA['.$protocol.'://'.$itemlink. ']]>';?>
</link>
<description>
<?php
if ((($ext == "flv") || ($ext == "mp3") || ($ext == "mp4") ||  ($ext == "3gp") ||  ($ext == "mov")) AND $rssmode != "album") {
	echo $videocontent;
} else {
	echo $itemcontent;
} ?>
<?php  echo $datecontent; ?>
</description>
<?php // enables download of embeded content like images or movies in some RSS clients. just for testing, shall become a real option
if(getOption("feed_enclosure") AND $rssmode != "albums") { ?>
<enclosure url="<?php echo $protocol; ?>://<?php echo $fullimagelink; ?>" type="<?php echo $mimetype; ?>" length="<?php echo filesize($imagefile);?>" />
<?php  } ?>
<category>
	<?php
	if($rssmode != "albums") {
		echo html_encode(get_language_string($albumobj->get("title"),$locale));
	} else {
		echo html_encode(get_language_string($albumitem->get("title"),$locale));
	} ?>
</category>
<?php if(getOption("feed_mediarss") AND $rssmode != "albums") { ?>
<media:content url="<?php echo $protocol; ?>://<?php echo $fullimagelink; ?>" type="image/jpeg" />
<media:thumbnail url="<?php echo $protocol; ?>://<?php echo $fullimagelink; ?>" width="<?php echo $size; ?>"	height="<?php echo $size; ?>" />
<?php } ?>
<guid><?php echo '<![CDATA['.$protocol.'://'.$itemlink.']]>';?></guid>
<pubDate>
	<?php
	if($rssmode != "albums") {
		echo date("r",strtotime($item->get('date')));
	} else {
		echo date("r",strtotime($albumitem->get('date')));
	}
	?>
</pubDate>
</item>
<?php } ?>
</channel>
</rss>