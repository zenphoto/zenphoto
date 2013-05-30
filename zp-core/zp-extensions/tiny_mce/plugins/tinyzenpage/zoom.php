<?php
define('OFFSET_PATH', 3);
require_once("../../../../functions.php");
$host = "http://".html_encode($_SERVER["HTTP_HOST"]);
?>
<!DOCTYPE html>
<html>
<head>
<title>TinyZenpage</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<body>
<div style="text-align: center; width 450px;">
<?php
if(isset($_GET['image']) && isset($_GET['album'])) {
	$imagename = sanitize($_GET['image']);
	$albumname = sanitize($_GET['album']);
	// getting the webpath manually since the offset does not work here
	$partialpath = strpos(FULLWEBPATH, '/'.ZENFOLDER);
	$webpath = substr(FULLWEBPATH,0,$partialpath);
	$ext = strtolower(strrchr($imagename, "."));
	$albumobj = newAlbum($albumname);
	$imageobj = newImage($albumobj,$imagename);
	echo $imageobj->getTitle()."<br />";
	if(isImageVideo($imageobj)) {
		switch($ext) {
			case '.flv':
			case '.mp4':
			case '.m4v':
				echo '<video src="http://localhost/'.pathurlencode($imageobj->getFullImage()).'" id="player"></video>';
				break;
			case '.mp3':
			case '.fla':
			case '.m4a':
				echo '<audio src="'.pathurlencode($imageobj->getFullImage()).'" id="player"></audio>';
				break;
			case '.3gp':
			case '.mov':
				echo '</a>
				<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="400" height="400" codebase="http://www.apple.com/qtactivex/qtplugin.cab">
				<param name="src" value="' . pathurlencode($imageobj->getFullImage()) . '"/>
				<param name="autoplay" value="false" />
				<param name="type" value="video/quicktime" />
				<param name="controller" value="true" />
				<embed src="' . pathurlencode($imageobj->getFullImage()) . '" width="400" height="400" scale="aspect" autoplay="false" controller"true" type="video/quicktime"
					pluginspage="http://www.apple.com/quicktime/download/" cache="true"></embed>
				</object><a>';
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
