<?php
/**
 * Simpleviewer personality
 */
// initialization stuff
if ($_zp_gallery_page=='search.php' || zp_getCookie("noFlash") || $perm = (isset($_GET['noflash']) || !MOD_REWRITE)) {
	if (isset($perm)) {
		zp_setCookie("noFlash", "noFlash");
	}
	require_once(SERVERPATH.'/'.THEMEFOLDER.'/effervescence_plus/image_page/functions.php');
} else {
	$personality = new simpleviewer($zenCSS);
	if (isset($_GET['format']) && $_GET['format'] == 'xml') {
		$personality->XML_part();
		exitZP();
	}
}


class simpleviewer {
		// Change the Simpleviewer configuration here

		var $maxImageWidth="600";
		var $maxImageHeight="600";

		var $preloaderColor="0xFFFFFF";
		var $textColor="0xFFFFFF";
		var $frameColor="0xFFFFFF";

		var $frameWidth="10";
		var $stagePadding="20";

		var $thumbnailColumns="3";
		var $thumbnailRows="5";
		var $navPosition="left";

		var $enableRightClickOpen="true";

		var $backgroundImagePath="";
		var $backgroundColor;
		// End of Simpeviewer config
	function __construct($zenCSS) {
		$this->backgroundColor = $this->parseCSSDef($zenCSS);
	}

	function theme_head($_zp_themeroot) {
		?>
		<script type="text/javascript" src="<?php echo $_zp_themeroot; ?>/simpleviewer/swfobject.js"></script>
		<?php
		return true;
	}

	function theme_bodyopen($_zp_themeroot) {

	}

	function theme_content($map) {
		global $_zp_current_image, $_zp_current_album, $_zp_themeroot, $points;
		if ($imagePage = isImagePage()) {
			?>
			<!-- Simpleviewer section -->
		<div id="content">
			<div id="flash">
				<p align="center">
					<span style=""><?php printf(gettext('For the best viewing experience <a href="http://%s"> Get Adobe Flash.</a>'), 'get.adobe.com/flashplayer'); ?>
					</span>
				</p>
				<p align="center">
				<?php
				if ($map) {
					$points = array();
					while (next_image(true)) {
						$coord = getGeoCoord($_zp_current_image);
						if ($coord) {
							$coord['desc'] = '<p align=center>'.$coord['desc'].'</p>';
							$points[] = $coord;
						}
					}
				}
				if ($imagePage) {
					$url = html_encode(getPageURL(getTotalPages(true)));
				} else {
					$url = html_encode(getPageURL(getCurrentPage()));
				}
				printLinkWithQuery($url, 'noflash', gettext('View Gallery Without Flash'));
				echo "</p>";
				$flash_url = html_encode(rtrim(getAlbumLinkURL(),'/'));
				$flash_url = $flash_url . (MOD_REWRITE ? "?" : "&amp;") . "format=xml";

				?>
				<script type="text/javascript">
					// <!-- <![CDATA[
					var fo = new SWFObject("<?php echo  $_zp_themeroot ?>/simpleviewer/simpleviewer.swf", "viewer", "100%", "100%", "7", "<?php echo $this->backgroundColor; ?>");
					fo.addVariable("preloaderColor", "<?php echo $this->preloaderColor ?>");
					fo.addVariable("xmlDataPath", "<?php echo $flash_url ?>");
					fo.addVariable("width", "100%");
					fo.addVariable("height", "100%");
					fo.addParam("wmode", "opaque");
					fo.write("flash");
					// ]]> -->
				</script>
			<?php
						echo '<div class="clearage"></div>';
				if (!empty($points) && $map) {
					function map_callback($map) {
						global $points;
						foreach ($points as $coord) {
							addGeoCoord($map, $coord);
						}
					}
					?>
					<div id="map_link">
					<?php printGoogleMap(NULL, NULL, NULL, 'album_page', 'map_callback'); ?>
					</div>
					<?php
				}
		?>
			</div><!-- flash -->
			<div id="main">
				<div id="images">
				<?php if (function_exists('printAddToFavorites')) printAddToFavorites($_zp_current_album); ?>
				<?php @call_user_func('printRating'); ?>
				</div>
			</div>
		<div class="clearage"></div>
		</div> <!-- content -->
		<?php
		}
	}

	function XML_part() {
		header ('Content-Type: application/xml');
		$path = '';
		$levels = explode('/', getAlbumLinkURL());
		foreach ($levels as $v) {
			$path = $path . '../';
		}
		$path=substr($path, 0, -1);

		echo '<?xml version="1.0" encoding="UTF-8"?>
			<simpleviewerGallery title=""  maxImageWidth="'.$this->maxImageWidth.'" maxImageHeight="'.$this->maxImageHeight.
				'" textColor="'.$this->textColor.'" frameColor="'.$this->frameColor.'" frameWidth="'.$this->frameWidth.'" stagePadding="'.
				$this->stagePadding.'" thumbnailColumns="'.$this->thumbnailColumns.'" thumbnailRows="'.$this->thumbnailRows.'" navPosition="'.
				$this->navPosition.'" enableRightClickOpen="'.$this->enableRightClickOpen.'" backgroundImagePath="'.$this->backgroundImagePath.
				'" imagePath="'.$path.'" thumbPath="'.$path.'">';

		while (next_image(true)){
			if (isImagePhoto()) {
				// simpleviewer does not do videos
				?>
				<image><filename><?php echo getDefaultSizedImage();?></filename>
					<caption>
					<![CDATA[<a href="<?php echo html_encode(getImageLinkURL());?>" title="<?php echo gettext('Open In New Window'); ?>">
						<font face="Times"><u><b><em><?php echo getImageTitle() ?></font></em></b></u></a></u>
						<br /></font><?php echo getImageDesc(); ?>]]>
				</caption>
				</image>
				<?php
				}
			}
		echo "</simpleviewerGallery>";
	}

	function parseCSSDef($file) {
		$file = str_replace(WEBPATH, '', $file);
		$file = SERVERPATH . internalToFilesystem($file);
		if (is_readable($file) && $fp = @fopen($file, "r")) {
			while($line = fgets($fp)) {
				if (!(false === strpos($line, "#simpleviewer {"))) {
					$line = fgets($fp);
					$line = trim($line);
					preg_match('/background:(.*);/', $line, $matches);
					$rslt = trim($matches[1]);
					return $rslt;
				}
			}
		}
		return "#0b9577"; /* the default value */
	}


}

?>