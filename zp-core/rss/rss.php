<?php
$host = getRSSHost();
$serverprotocol = getOption("server_protocol");
$locale = getRSSLocale();
$validlocale = getRSSLocaleXML();
$modrewritesuffix = getRSSImageAndAlbumPaths("modrewritesuffix");
header('Content-Type: application/xml');
$rssmode = getRSSAlbumsmode();
$albumfolder = getRSSAlbumnameAndCollection("albumfolder");
$collection = getRSSAlbumnameAndCollection("collection");
$albumname = getRSSAlbumTitle();
$albumpath = getRSSImageAndAlbumPaths("albumpath");
$imagepath = getRSSImageAndAlbumPaths("imagepath");
$size = getRSSImageSize();
$items = getOption('feed_items'); // # of Items displayed on the feed
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
<channel>
<title><?php echo strip_tags(get_language_string(getOption('gallery_title'), $locale)).' '.strip_tags($albumname); ?></title>
<link><?php echo $serverprotocol."://".$host.WEBPATH; ?></link>
<atom:link href="<?php echo $serverprotocol; ?>://<?php echo html_encode($_SERVER["HTTP_HOST"]); ?><?php echo html_encode($_SERVER["REQUEST_URI"]); ?>" rel="self"	type="application/rss+xml" />
<description><?php echo strip_tags(get_language_string(getOption('Gallery_description'), $locale)); ?></description>
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
			$ext = strtolower(strrchr($item->filename, "."));
			$albumobj = $item->getAlbum();
			$itemlink = $host.WEBPATH.$albumpath.pathurlencode($albumobj->name).$imagepath.pathurlencode($item->filename).$modrewritesuffix;
			$fullimagelink = $host.WEBPATH."/albums/".$albumobj->name."/".$item->filename;
			$imagefile = "albums/".$albumobj->name."/".$item->filename;
			$thumburl = '<img border="0" src="'.$serverprotocol.'://'.$host.$item->getCustomImage($size, NULL, NULL, NULL, NULL, NULL, NULL, TRUE).'" alt="'.get_language_string($item->get("title"),$locale) .'" />';
			$itemcontent = '<![CDATA[<a title="'.get_language_string($item->get("title"),$locale).' in '.get_language_string($albumobj->get("title"),$locale).'" href="'.$serverprotocol.'://'.$itemlink.'">'.$thumburl.'</a>' . get_language_string($item->get("desc"),$locale) . ']]>';
			$videocontent = '<![CDATA[<a title="'.get_language_string($item->get("title"),$locale).' in '.$albumobj->getTitle().'" href="'.$serverprotocol.'://'.$itemlink.'"><img src="'.$serverprotocol.'://'.$host.$item->getThumb().'" alt="'.get_language_string($item->get("title"),$locale) .'" /></a>' . get_language_string($item->get("desc"),$locale) . ']]>';
			$datecontent = '<![CDATA[Date: '.zpFormattedDate(getOption('date_format'),$item->get('mtime')).']]>';
		} else {
			$galleryobj = new Gallery();
			$albumitem = new Album($galleryobj, $item['folder']);
			$totalimages = $albumitem->getNumImages();
			$itemlink = $host.WEBPATH.$albumpath.pathurlencode($albumitem->name);
			$albumthumb = $albumitem->getAlbumThumbImage();
			$thumb = newImage($albumitem, $albumthumb->filename);
			$thumburl = '<img border="0" src="'.$thumb->getCustomImage($size, NULL, NULL, NULL, NULL, NULL, NULL, TRUE).'" alt="'.get_language_string($albumitem->get("title"),$locale) .'" />';
			$title =  get_language_string($albumitem->get("title"),$locale);
			if(true || getOption("feed_sortorder_albums") === "latestupdated") {
				$filechangedate = filectime(getAlbumFolder().internalToFilesystem($albumitem->name));
				$latestimage = query_single_row("SELECT mtime FROM " . prefix('images'). " WHERE albumid = ".$albumitem->getAlbumID() . " AND `show` = 1 ORDER BY id DESC");
				$lastuploaded = query("SELECT COUNT(*) FROM ".prefix('images')." WHERE albumid = ".$albumitem->getAlbumID() . " AND mtime = ". $latestimage['mtime']);
				$row = db_fetch_row($lastuploaded);
				$count = $row[0];
				if($count == 1) {
					$imagenumber = sprintf(gettext('(%s: 1 new image)'),$title);
				} else {
					$imagenumber = sprintf(gettext('(%1$s: %2$s new images)'),$title,$count);
				}
				$itemcontent = '<![CDATA[<a title="'.$title.'" href="'.$serverprotocol.'://'.$itemlink.'">'.$thumburl.'</a>'.
						'<p>'.$imagenumber.'</p>'.get_language_string($albumitem->get("desc"),$locale).']]>';
				$videocontent = '';
				$datecontent = '<![CDATA['.sprintf(gettext("Last update: %s"),zpFormattedDate(getOption('date_format'),$filechangedate)).']]>';
			} else {
				if($totalimages == 1) {
					$imagenumber = sprintf(gettext('(%s: 1 image)'),$title);
				} else {
					$imagenumber = sprintf(gettext('(%1$s: %2$s images)'),$title,$totalimages);
				}
				$itemcontent = '<![CDATA[<a title="'.$title.'" href="'.$serverprotocol.'://'.$itemlink.'">'.$thumburl.'</a>'.get_language_string($albumitem->get("desc"),$locale).']]>';
				$datecontent = '<![CDATA['.sprintf(gettext("Date: %s"),zpFormattedDate(getOption('date_format'),$albumitem->get('mtime'))).']]>';
			}
			$ext = strtolower(strrchr($thumb->filename, "."));
		}
		$mimetype = getMimeType($ext);
		?>
<item>
<title><?php
if($rssmode != "albums") {
	printf('%1$s (%2$s)', get_language_string($item->get("title"),$locale), get_language_string($albumobj->get("title"),$locale));
} else {
	echo $imagenumber;
}
?></title>
<link>
<?php echo '<![CDATA['.$serverprotocol.'://'.$itemlink. ']]>';?>
</link>
<description>
<?php
if ((($ext == ".flv") || ($ext == ".mp3") || ($ext == ".mp4") ||  ($ext == ".3gp") ||  ($ext == ".mov")) AND $rssmode != "album") {
	echo $videocontent;
} else {
	echo $itemcontent;
} ?>
<?php  echo $datecontent; ?>
</description>
<?php // enables download of embeded content like images or movies in some RSS clients. just for testing, shall become a real option
if(getOption("feed_enclosure") AND $rssmode != "albums") { ?>
<enclosure url="<?php echo $serverprotocol; ?>://<?php echo $fullimagelink; ?>" type="<?php echo $mimetype; ?>" length="<?php echo filesize($imagefile);?>" />
<?php  } ?>
<category>
	<?php
	if($rssmode != "albums") {
		echo get_language_string($albumobj->get("title"),$locale);
	} else {
		echo get_language_string($albumitem->get("title"),$locale);
	} ?>
</category>
<?php if(getOption("feed_mediarss") AND $rssmode != "albums") { ?>
<media:content url="<?php echo $serverprotocol; ?>://<?php echo $fullimagelink; ?>" type="image/jpeg" />
<media:thumbnail url="<?php echo $serverprotocol; ?>://<?php echo $fullimagelink; ?>" width="<?php echo $size; ?>"	height="<?php echo $size; ?>" />
<?php } ?>
<guid><?php echo '<![CDATA['.$serverprotocol.'://'.$itemlink.']]>';?></guid>
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