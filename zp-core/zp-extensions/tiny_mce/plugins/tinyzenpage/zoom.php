<?php
define('OFFSET_PATH', 5);
$const_webpath = dirname(dirname(dirname(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))))));
require_once("../../../../functions.php");
$host = "http://".html_encode($_SERVER["HTTP_HOST"]);

$curdir = getcwd();
chdir(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/flowplayer3');
$filelist = safe_glob('flowplayer-*.min.js');
$player = array_shift($filelist);
$filelist = safe_glob('flowplayer.playlist-*.min.js');
$playlist = array_shift($filelist);
$filelist = safe_glob('flowplayer-*.swf');
$swf = array_shift($filelist);
chdir($curdir);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>TinyZenpage</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="../../../../js/jquery.js"></script>
<script type="text/javascript" src="../../../flowplayer3/<?php echo $player; ?>"></script>
<script type="text/javascript" src="../../../flowplayer3/<?php echo $playlist; ?>"></script>
</head>
<body>
<div style="text-align: center; width 450px;">
<?php
$imagename = sanitize($_GET['image']);
$albumname = sanitize($_GET['album']);
// getting the webpath manually since the offset does not work here
$partialpath = strpos(FULLWEBPATH, '/'.ZENFOLDER);
$webpath = substr(FULLWEBPATH,0,$partialpath);
$ext = strtolower(strrchr($imagename, "."));
$galleryobj = new Gallery();
$albumobj = new Album($galleryobj,$albumname);
$imageobj = newImage($albumobj,$imagename);
echo $imageobj->getTitle()."<br />";
if(isImageVideo($imageobj)) {
	if(($ext == ".flv") || ($ext == ".mp3") || ($ext == ".mp4")) {
	echo '
			<a href="'.pathurlencode($imageobj->getFullImage()).'" id="player" style="display:block; width: 420px; height: 400px;"></a>
			<script type="text/javascript">
			flowplayer("player","../../../flowplayer3/'.$swf.'", {
				clip: {
					autoPlay: false,
					autoBuffering: false,
					scaling: "orig"
				},
			});
			</script>';
	} else if (($ext == ".3gp") ||  ($ext == ".mov")) {
		echo '</a>
			 		<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="400" height="400" codebase="http://www.apple.com/qtactivex/qtplugin.cab">
				 	<param name="src" value="' . pathurlencode($imageobj->getFullImage()) . '"/>
				 	<param name="autoplay" value="false" />
				 	<param name="type" value="video/quicktime" />
				 	<param name="controller" value="true" />
				 	<embed src="' . pathurlencode($imageobj->getFullImage()) . '" width="400" height="400" scale="aspect" autoplay="false" controller"true" type="video/quicktime"
				 		pluginspage="http://www.apple.com/quicktime/download/" cache="true"></embed>
					</object><a>';
	}
} else {
?>
<img src="<?php echo pathurlencode($imageobj->getSizedImage(440)); ?>" />
<?php } ?>
</div><!-- main div -->
</body>
</html>
