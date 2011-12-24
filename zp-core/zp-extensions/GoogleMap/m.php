<?php
/*
 * google_maps -- map server
 *
 * @package plugins
 */

// force UTF-8 Ø

define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))).'/functions.php');
if (getOption('gmap_sessions')) {
	session_start();
}
require_once(dirname(dirname(__FILE__)).'/GoogleMap.php');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s').' GMT');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/2002/REC-xhtml1-20020801/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php googlemap_js(); ?>
<style type="text/css">
.errorbox {
	padding: 5px;
	background-color: #FDD;
	border-top: 1px solid #FAA;
	border-left: 1px solid #FAA;
	border-right: 1px solid #FAA;
	border-bottom: 5px solid #FAA;
	margin-bottom: 10px;
	font-size: 100%;
	color: #DD6666;
	}
	.errorbox h2 {
		color: #DD6666;
		font-size: 100%;
		font-weight: bold;
		margin: 0px;
	}
	.errorlist {
		list-style-type: none;
	}
</style>
</head>
<body>
	<script type="text/javascript">
		// <!-- <![CDATA[
		function image(album,image) {
			window.parent.location = '<?php echo WEBPATH ?>/index.php?album='+album+'&image='+image;
		}
		// ]]> -->
	</script>
	<?php
	if (getOption('gmap_sessions')) {
		$mapdata = $_SESSION['GoogleMapVars'];
	} else {
		$mapdata = base64_decode(str_replace(' ', '+', sanitize($_GET['data'])));
		if ($mapdata) {
			if (function_exists('bzcompress')) {
				$mapdata = bzdecompress($mapdata);
			} else {
				$mapdata = gzuncompress($mapdata);
			}
			$mapdata = unserialize($mapdata);
		}
	}
	if (is_array($mapdata)) {
		$MAP_OBJECT = new GoogleMapAPI(sanitize($_GET['type']));
		$MAP_OBJECT->_minify_js = defined('RELEASE');
		$MAP_OBJECT->setZoomLevel(getOption('gmap_zoom'));
		$MAP_OBJECT->setWidth(getOption('gmap_width'));
		$MAP_OBJECT->setHeight(getOption('gmap_height'));
		$MAP_OBJECT->setMapType(getOption('gmap_starting_map'));
		$mapcontrol = getOption('gmap_control');
		if ($mapcontrol=='none') {
			$MAP_OBJECT->disableTypeControls();
		} else {
			$MAP_OBJECT->enableMapControls();
			$MAP_OBJECT->setTypeControlsStyle($mapcontrol);
			$MAP_OBJECT->setControlSize(getOption('gmap_control_size'));
			$mapsallowed = array();
			if (getOption('gmap_map')) $mapsallowed[] = 'ROADMAP';
			if (getOption('gmap_hybrid')) $mapsallowed[] = 'HYBRID';
			if (getOption('gmap_satellite')) $mapsallowed[] = 'SATELLITE';
			if (getOption('gmap_terrain')) $mapsallowed[] = 'TERRAIN';
			$MAP_OBJECT->setTypeControlTypes($mapsallowed);
		}
		foreach ($mapdata as $key=>$datum) {
			$MAP_OBJECT->$key = $datum;
		}
		echo $MAP_OBJECT->getMapJS();
		echo $MAP_OBJECT->printMap();
		echo $MAP_OBJECT->printOnLoad();
	} else {
		?>
		<div class="errorbox">
			<h2><?php echo gettext('Map display error')?></h2>
			<p>
				<?php echo gettext('The GoogleMap plugin <em>map display</em> script has received a corrupt <em>Map</em> parameter.
				This is most likely caused by URL character limitations from your browser.'); ?>
				<?php echo gettext('For information on URL limits visit <a href="javascript:parent.window.location=\'http://www.boutell.com/newfaq/misc/urllength.html\';">What is the maximum length of a URL</a>.'); ?>
			</p>
			<p>
				<?php echo gettext('You can try a different browser or contact the site manager for assistance.'); ?>
			</p>
			<p>
			<?php echo gettext('If you are the manager of this site you can try the following:'); ?>
				<ul>
					<?php
					if (!getOption('gmap_sessions')) {
						?>
						<li><?php printf(gettext('Enable the <a href="javascript:parent.window.location=%s;">GoogleMap option</a> <em>Map sessions</em>.'),"'".FULLWEBPATH.'/'.ZENFOLDER.'/admin-options.php?tab=plugin&show-GoogleMap'."'"); ?></li>
						<?php
					}
					?>
					<li><?php echo gettext('Reduce the number of <em>points</em> being displayed'); ?></li>
					<li><?php echo gettext('Reduce the text passed as the description of each <em>point</em>'); ?></li>
					<li><?php echo gettext('Truncate the titles of the <em>points</em>'); ?></li>
				</ul>
			</p>
		</div>
		<?php
	}
	?>
</body>
</html>