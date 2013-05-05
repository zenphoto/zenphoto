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
	zp_session_start();
}
require_once(dirname(dirname(__FILE__)).'/GoogleMap.php');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s').' GMT');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/2002/REC-xhtml1-20020801/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo getOption('charset'); ?>" />
	<?php GoogleMap::js(); ?>
</head>
<body>
	<?php if (getOption('gmap_sessions')) {
		$map_data = $_SESSION['GoogleMapVars'];
	} else {
		$param = base64_decode(str_replace(' ', '+', sanitize($_GET['map_data'])));
		if ($param) {
			if (function_exists('bzcompress')) {
				$data = bzdecompress($param);
			} else {
				$data = gzuncompress($param);
			}
			$map_data = sanitize(unserialize($data), 4);
		}
	}

	if (is_array($map_data)) {

		/* map configuration */
		$mapControls = getOption('gmap_control_type');
		if ($mapControls == 'none') {
			$mapTypeControl = false;
		} else {
			$mapTypeControl = true;
			$map_control_type = $mapControls;

			$allowedMapTypes = array();
			if (getOption('gmap_map_hybrid')) $allowedMapTypes[] = 'HYBRID';
			if (getOption('gmap_map_roadmap')) $allowedMapTypes[] = 'ROADMAP';
			if (getOption('gmap_map_satellite')) $allowedMapTypes[] = 'SATELLITE';
			if (getOption('gmap_map_terrain')) $allowedMapTypes[] = 'TERRAIN';
		}

		$config['center'] = '0, 0';
		$config['zoom'] = 'auto';
		$config['cluster'] = true;
		$config['zoomControlStyle'] = getOption('gmap_zoom_size');
		if ($mapTypeControl) {
			$config['map_type'] = getOption('gmap_starting_map');
			$config['map_types_available'] = $allowedMapTypes;
		} else {
			$config['disableMapTypeControl'] = true;
		}
		$config['map_width'] = getOption('gmap_width')."px";
		$config['map_height'] = getOption('gmap_height')."px";
		$config['clusterMaxZoom'] = getOption('gmap_cluster_max_zoom');
		$config['clusterAverageCenter'] = true;
		$config['onclick'] = "iw.close();";
		$config['minifyJS'] = true;

		$map = new Googlemaps($config);

		$map->output_js_contents = $map_data["output_js_contents"];
		$map->output_html = $map_data["output_html"];
		?>
		<div class="googlemap">
			<script type="text/javascript">
			//<![CDATA[
				<?php
				echo $map->output_js_contents;
				echo omsAdditions();
				?>

				function image(album, image) {
					window.parent.location = '<?php echo WEBPATH; ?>/index.php?album=' + album + '&image=' + image;
				}
			//]]>
			</script>
			<div id=googlemap_data">
				<?php echo $map->output_html; ?>
			</div>
		</div>
	<?php } else { ?>
		<div class="errorbox">
			<h2><?php echo gettext('Map display error')?></h2>
			<p>
				<?php echo gettext('The GoogleMap plugin <em>map display</em> script has received a corrupt <em>Map</em> parameter.	This is most likely caused by URL character limitations from your browser.'); ?>
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
	<?php } ?>
</body>
</html>