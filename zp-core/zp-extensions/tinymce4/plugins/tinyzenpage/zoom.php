<?php
define('OFFSET_PATH', 3);
require_once("../../../../functions/functions.php");
?>
<!DOCTYPE html>
<html<?php printLangAttribute(); ?>>
<head>
<title>TinyZenpage</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<body>
<div style="text-align: center; width: 450px;">
<?php
if(isset($_GET['image']) && isset($_GET['album'])) {
	$imagename = sanitize($_GET['image']);
	$albumname = sanitize($_GET['album']);
	// getting the webpath manually since the offset does not work here
	$partialpath = strpos(FULLWEBPATH, '/'.ZENFOLDER);
	$webpath = substr(FULLWEBPATH,0,$partialpath);
	$ext = strtolower(strrchr($imagename, "."));
	$albumobj = AlbumBase::newAlbum($albumname);
	$imageobj = Image::newImage($albumobj,$imagename);
	echo $imageobj->getTitle()."<br />";
	if($imageobj->isVideo()) {
		switch($ext) {
			case '.flv':
			case '.mp4':
			case '.m4v':
				echo '<video src="'.pathurlencode($imageobj->getFullImage()).'" id="player"></video>';
				break;
			case '.mp3':
			case '.fla':
			case '.m4a':
				echo '<audio src="'.pathurlencode($imageobj->getFullImage()).'" id="player"></audio>';
				break;
			}
	} else {
		?>
		<img src="<?php echo html_encode(pathurlencode($imageobj->getSizedImage(440))); ?>" />
		<?php
	}
} else {
	echo "<div style='text-align: left; width 450px; font-size:0.8em'>";
	if(isset($_GET['news'])) {
		$item = sanitize($_GET['news']);
		$obj = new ZenpageNews($item);
		$cats = $obj->getCategories();
		$categories = gettext('Categories: ');
		$count = '';
		if($cats) {
			foreach($cats as $cat) {
				$count++;
				$catobj = new ZenpageCategory($cat['titlelink']);
				if($count != 1) {
					$categories .= ', ';
				}
				$categories .= $catobj->getTitle();
			}
		}
	} elseif(isset($_GET['pages'])) {
		$item = sanitize($_GET['pages']);
		$obj = new ZenpagePage($item);
		$categories = '';
	}
	echo '<h3>'.$obj->getTitle().'</h3>';
	echo '<p><small>'.$obj->getDatetime().'</small></p>';
	echo $obj->getContent();
	echo $categories;
	echo '</div>';
}
?>
</div><!-- main div -->
</body>
</html>
