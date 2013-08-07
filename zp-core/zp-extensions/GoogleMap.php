<?php
/**
 * This is plugin for display pictures on a Google Map
 * Uses :
 * 		CodeIgniter Google Maps API V3 Class (hacked for zenphoto needs) (https://github.com/BIOSTALL/CodeIgniter-Google-Maps-V3-API-Library)
 * 		markerClustererPlus library 2.0.15 (http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerclustererplus/)
 * 		overlappingMarkerSpiderfier library 0.3 (https://github.com/jawj/OverlappingMarkerSpiderfier)
 *
 * @author Stephen Billard (sbillard) & Vincent Bourganel (vincent3569)
 * @package plugins
 *
 */
$plugin_is_filter = 5 | THEME_PLUGIN;
$plugin_description = gettext('Display Google Maps based on <em>latitude</em> and <em>longitude</em> metadata in the images.');
$plugin_notice = sprintf(gettext('<strong>Note</strong>: Google does place limits on the use of its <a href="%s"><em>Maps API</em></a>. Please review these to be sure your site is in compliance.'), 'http://googlegeodevelopers.blogspot.com/2011/10/introduction-of-usage-limits-to-maps.html');
$plugin_author = 'Stephen Billard (sbillard) & Vincent Bourganel (vincent3569)';


$option_interface = 'GoogleMap';
if (isset($_zp_gallery_page) && $_zp_gallery_page != 'index.php') {
	if (getOption('gmap_sessions')) {
		zp_session_start();
	}
	zp_register_filter('theme_head', 'GoogleMap::js');
}

/**
 * googleMap
 *
 */
class GoogleMap {

	function GoogleMap() {
		setOptionDefault('gmap_width', 595);
		setOptionDefault('gmap_height', 300);
		setOptionDefault('gmap_map_roadmap', 1);
		setOptionDefault('gmap_map_hybrid', 1);
		setOptionDefault('gmap_map_satellite', 1);
		setOptionDefault('gmap_map_terrain', 1);
		setOptionDefault('gmap_starting_map', 'HYBRID');
		setOptionDefault('gmap_control_type', 'HORIZONTAL_BAR');
		setOptionDefault('gmap_zoom_size', 'LARGE');
		setOptionDefault('gmap_cluster_max_zoom', 13);
		setOptionDefault('gmap_sessions', 1);
		if (OFFSET_PATH == 2) {
			setOptionDefault('gmap_display', 'hide');
			purgeOption('gmap_hide');
		}
		setOptionDefault('gmap_display', 'show');
	}

	function getOptionsSupported() {

		$MapTypes = array(); // order matters here because the first allowed map is selected if the 'gmap_starting_map' is not allowed
		if (getOption('gmap_map_hybrid'))
			$MapTypes[gettext('Hybrid')] = 'HYBRID';
		if (getOption('gmap_map_roadmap'))
			$MapTypes[gettext('Map')] = 'ROADMAP';
		if (getOption('gmap_map_satellite'))
			$MapTypes[gettext('Satellite')] = 'SATELLITE';
		if (getOption('gmap_map_terrain'))
			$MapTypes[gettext('Terrain')] = 'TERRAIN';

		$defaultMap = getOption('gmap_starting_map');
		if (array_search($defaultMap, $MapTypes) === false) { // the starting map is not allowed, pick a new one
			$temp = $MapTypes;
			$defaultMap = array_shift($temp);
			setOption('gmap_starting_map', $defaultMap);
		}

		return array(
						gettext('Allowed maps')									 => array('key'				 => 'gmap_allowed_maps', 'type'			 => OPTION_TYPE_CHECKBOX_ARRAY,
										'order'			 => 1,
										'checkboxes' => array(gettext('Hybrid')		 => 'gmap_map_hybrid',
														gettext('Map')			 => 'gmap_map_roadmap',
														gettext('Satellite') => 'gmap_map_satellite',
														gettext('Terrain')	 => 'gmap_map_terrain'),
										'desc'			 => gettext('Select the map types that are allowed.')),
						gettext('Initial map display selection') => array('key'				 => 'gmap_starting_map', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 2,
										'selections' => $MapTypes,
										'desc'			 => gettext('Select the initial type of map to display.')),
						gettext('Map display')									 => array('key'				 => 'gmap_display', 'type'			 => OPTION_TYPE_SELECTOR,
										'order'			 => 3,
										'selections' => array(gettext('show')			 => 'show',
														gettext('hide')			 => 'hide',
														gettext('colorbox')	 => 'colorbox'),
										'desc'			 => gettext('Select <em>hide</em> to initially hide the map. Select <em>colorbox</em> for the map to display in a colorbox. Select <em>show</em> and the map will display when the page loads.')),
						gettext('Map controls')									 => array('key'			 => 'gmap_control_type', 'type'		 => OPTION_TYPE_RADIO,
										'order'		 => 4,
										'buttons'	 => array(gettext('None')				 => 'none',
														gettext('Default')		 => 'DEFAULT',
														gettext('Dropdown')		 => 'DROPDOWN_MENU',
														gettext('Horizontal')	 => 'HORIZONTAL_BAR'),
										'desc'		 => gettext('Display options for the Map type control.')),
						gettext('Zoom controls')								 => array('key'			 => 'gmap_zoom_size', 'type'		 => OPTION_TYPE_RADIO,
										'order'		 => 5,
										'buttons'	 => array(gettext('Small')	 => 'SMALL',
														gettext('Default') => 'DEFAULT',
														gettext('Large')	 => 'LARGE'),
										'desc'		 => gettext('Display options for the Zoom control.')),
						gettext('Max zoom level')								 => array('key'		 => 'gmap_cluster_max_zoom', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 6,
										'desc'	 => gettext('The max zoom level for clustering pictures on map.')),
						gettext('Map dimensions—width')					 => array('key'		 => 'gmap_width', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 7,
										'desc'	 => gettext('The default width of the map.')),
						gettext('Map dimensions—height')				 => array('key'		 => 'gmap_height', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 8,
										'desc'	 => gettext('The default height of the map.')),
						gettext('Map sessions')									 => array('key'		 => 'gmap_sessions', 'type'	 => OPTION_TYPE_CHECKBOX,
										'order'	 => 9,
										'desc'	 => gettext('If checked GoogleMaps will use sessions to pass map data for the <em>colorbox</em> display option. We recommend this option be selected. It protects against reference forgery security attacks and mitigates problems with data exceeding the allowed by some browsers.'))
		);
	}

	function handleOption($option, $currentValue) {

	}

	/**
	 * Add required informations in the header
	 */
	static function js() {

		if (!defined('BASEPATH'))
			define('BASEPATH', true); //	for no access test in GoogleMap.php
		require_once(dirname(__FILE__) . '/GoogleMap/CodeIgniter-Google-Maps-V3-API/Googlemaps.php');
		$loc = getOption('locale');
		if (empty($loc)) {
			$loc = '';
		} else {
			$loc = '&amp;language=' . substr(getOption('locale'), 0, 2);
		}
		?>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false<?php echo $loc; ?>"></script>
		<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/GoogleMap/markerClustererPlus/markerclusterer_packed.js"></script>
		<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/GoogleMap/overlappingMarkerSpiderfier/oms.min.js"></script>
		<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/GoogleMap/googleMap.css" type="text/css" media="screen"/>
		<?php
	}

}

// codeIgniter stuff
error_reporting(E_ALL ^ E_STRICT); //	required for the CodeIgniter-Google-Maps-V3-API code!
require_once(SERVERPATH . '/' . ZENFOLDER . '/' . COMMON_FOLDER . '/jsMin/JSMin.php');

class CI_load {

	function library($library) {
		//	better be jsmin, that's all we do
		assert($library == 'jsmin');
	}

}

class CI_jsmin {

	function min($js) {
		return JSMin::minify($js);
	}

}

class codeIgniter_kludge { //	dummy for all the CI stuff in the CodeIngnter-Google_maps script

	var $load;
	var $jsmin;

	function __construct() {
		$this->load = new CI_load();
		$this->jsmin = new CI_jsmin();
	}

}

function log_message($class, $msg) {
	// do nothing
}

function get_instance() {
	// standin for CI library
	return new codeIgniter_kludge();
}

function omsAdditions() {
	// maybe we can move some of the zenphoto hacks here.
	return '';
}

/**
 * $returns coordinate informations for an image
 * @param $image		image object
 */
function getGeoCoord($image) {
	global $_zp_current_image;
	$result = false;
	if ((is_object($image)) && ($image->table == 'images')) {
		$_zp_current_image = $image;
		$exif = $_zp_current_image->getMetaData();
		if ((!empty($exif['EXIFGPSLatitude'])) && (!empty($exif['EXIFGPSLongitude']))) {
			$lat_c = explode('.', str_replace(',', '.', $exif['EXIFGPSLatitude']) . '.0');
			$lat_f = round((float) abs($lat_c[0]) + ($lat_c[1] / pow(10, strlen($lat_c[1]))), 5);
			if (strtoupper(@$exif['EXIFGPSLatitudeRef']{0}) == 'S') {
				$lat_f = -$lat_f;
			}
			$lat_s = str_replace(',', '.', (string) $lat_f);

			$long_c = explode('.', str_replace(',', '.', $exif['EXIFGPSLongitude']) . '.0');
			$long_f = round((float) abs($long_c[0]) + ($long_c[1] / pow(10, strlen($long_c[1]))), 5);
			if (strtoupper(@$exif['EXIFGPSLongitudeRef']{0}) == 'W') {
				$long_f = -$long_f;
			}
			$long_s = str_replace(',', '.', (string) $long_f);

			$thumb = '<a href="javascript:image(\'' . $_zp_current_image->albumname . '\',\'' . $_zp_current_image->filename . '\');"><img src="' . getCustomImageURL(150) . '" /></a>';

			$result = array('lat'		 => $lat_s, 'long'	 => $long_s, 'title'	 => $_zp_current_image->getTitle(), 'desc'	 => $_zp_current_image->getDesc(), 'thumb'	 => $thumb);
		}
	}
	return $result;
}

/**
 * Add a point to a map object
 * @param $map			google map object
 * @param $coord		coordinates array
 */
function addGeoCoord($map, $coord) {
	if ($coord) {
		$marker = array();

		$title = str_replace('/', '\/', str_replace('"', '\"', str_replace(array("\n", "\r"), "", html_encodeTagged($coord['title']))));
		$desc = str_replace('/', '\/', str_replace('"', '\"', str_replace(array("\n", "\r"), "", html_encodeTagged($coord['desc']))));
		$thumb = str_replace('/', '\/', str_replace('"', '\"', str_replace(array("\n", "\r"), "", html_encodeTagged($coord['thumb']))));
		if ($title <> '') {
			$title = '<h3 class="map_title">' . $title . '</h3>';
		}
		if ($desc <> '') {
			$desc = '<div class="map_desc">' . $desc . '</div>';
		}
		if ($coord['thumb'] <> '') {
			$thumb = '<p class="map_img">' . $coord['thumb'] . '</p>';
		}

		$marker['position'] = $coord['lat'] . ", " . $coord['long'];
		$marker['title'] = $coord['title'];
		$marker['infowindow_content'] = $title . $thumb . $desc;
		$map->add_marker($marker);
	}
}

/**
 * Gathers the data for an image
 * @param $image		image object
 * @param $map			google map object
 */
function getImageGeodata($image, $map) {
	$coord = getGeoCoord($image);
	if ($coord) {
		addGeoCoord($map, $coord);
	}
	return $coord;
}

/**
 * Gathers the map data for an album
 * @param $album		album object
 * @param $map			google map object
 */
function getAlbumGeodata($album, $map) {
	$result = false;
	$images = $album->getImages(0, 0, null, null, false);
	foreach ($images as $an_image) {
		$image = newImage($album, $an_image);
		$coord = getGeoCoord($image);
		if ($coord) {
			$result = true; // at least one image has geodata
			addGeoCoord($map, $coord);
		}
	}
	return $result;
}

/**
 * Output the google map
 *
 * @param string $text text for the "toggle" link that shows/hides the map. Set empty to omit (then Map is always displayed)
 * @param string $id used to set the IDs for the toggle href element ($id_toggle) and the map element ($id_data)
 * @param string $hide initial map state: "hide", "show", or "colorbox"
 * @param object $obj optional image/album object. Pass string for generic map and use callback to set points
 * @param function $callback optional callback function to set map options.
 */
function printGoogleMap($text = NULL, $id = NULL, $hide = NULL, $obj = NULL, $callback = NULL) {
	global $_zp_current_album, $_zp_current_image;

	/* controls of parameters */
	if (is_null($obj)) {
		if (is_null($_zp_current_image)) {
			$obj = $_zp_current_album;
		} else {
			$obj = $_zp_current_image;
		}
	}
	if (is_null($obj)) {
		return false;
	}
	if (is_object($obj)) {
		$type = $obj->table;
		$typeid = $obj->getID();
	} else {
		$type = $obj;
		$typeid = '';
	}
	if (is_null($text)) {
		$text = gettext('Google Map');
	}
	if (empty($text)) {
		$hide = 'show';
	}
	if (is_null($hide)) {
		$hide = getOption('gmap_display');
	}
	if (!is_string($hide)) {
		if ($hide) {
			$hide = 'hide';
		} else {
			$hide = 'show';
		}
	}

	/* map configuration */
	$mapControls = getOption('gmap_control_type');
	if ($mapControls == 'none') {
		$mapTypeControl = false;
	} else {
		$mapTypeControl = true;
		$map_control_type = $mapControls;

		$allowedMapTypes = array();
		if (getOption('gmap_map_hybrid'))
			$allowedMapTypes[] = 'HYBRID';
		if (getOption('gmap_map_roadmap'))
			$allowedMapTypes[] = 'ROADMAP';
		if (getOption('gmap_map_satellite'))
			$allowedMapTypes[] = 'SATELLITE';
		if (getOption('gmap_map_terrain'))
			$allowedMapTypes[] = 'TERRAIN';
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
	$config['map_width'] = getOption('gmap_width') . "px";
	$config['map_height'] = getOption('gmap_height') . "px";
	$config['clusterMaxZoom'] = getOption('gmap_cluster_max_zoom');
	$config['clusterAverageCenter'] = true;
	$config['onclick'] = "iw.close();";
	$config['minifyJS'] = !TEST_RELEASE;

	$map = new Googlemaps($config);

	/* add markers from geocoded pictures */
	switch ($type) {
		case 'images':
			if (getImageGeodata($obj, $map)) {
				break;
			} else {
				$map = NULL;
				return false;
			}
		case 'albums':
			if (getAlbumGeodata($obj, $map)) {
				break;
			} else {
				$map = NULL;
				return false;
			}
		default:
			break;
	}

	if (!is_null($callback)) {
		call_user_func($callback, $map);
	}

	/* map display */
	if (is_null($id)) {
		$id = $type . $typeid . '_googlemap';
	}
	$id_toggle = $id . '_toggle';
	$id_data = $id . '_data';

	switch ($hide) {
		case 'show':
			$map->create_map();
			?>
			<script type="text/javascript">
			//<![CDATA[
			<?php
			echo $map->output_js_contents;
			echo omsAdditions();
			?>

				function image(album, image) {
					window.location = '<?php echo WEBPATH ?>/index.php?album=' + album + '&image=' + image;
				}
			//]]>
			</script>
			<div id="<?php echo $id_data; ?>">
				<?php echo $map->output_html; ?>
			</div>
			<?php
			break;
		case 'hide':
			$map->create_map();
			?>
			<script type="text/javascript">
			//<![CDATA[
			<?php
			echo $map->output_js_contents;
			echo omsAdditions();
			?>

				function image(album, image) {
					window.location = '<?php echo WEBPATH ?>/index.php?album=' + album + '&image=' + image;
				}

				function toggle_<?php echo $id_data; ?>() {
					if ($('#<?php echo $id_data; ?>').hasClass('hidden_map')) {
						$('#<?php echo $id_data; ?>').removeClass('hidden_map');
					} else {
						$('#<?php echo $id_data; ?>').addClass('hidden_map');
					}
				}
			//]]>
			</script>
			<a id="<?php echo $id_toggle; ?>" href="javascript:toggle_<?php echo $id_data; ?>();" title="<?php echo gettext('Display or hide the Google Map.'); ?>">
				<?php echo $text; ?>
			</a>
			<div id="<?php echo $id_data; ?>" class="hidden_map">
				<?php echo $map->output_html; ?>
			</div>
			<?php
			break;
		case 'colorbox':
			if (zp_has_filter('theme_head', 'colorbox::css')) {
				$map->create_map();
				$map_data["output_js_contents"] = $map->output_js_contents;
				$map_data["output_html"] = $map->output_html;

				if (getOption('gmap_sessions')) {
					$param = '';
					$_SESSION['GoogleMapVars'] = $map_data;
				} else {
					$serializedData = serialize($map_data);
					if (function_exists('bzcompress')) {
						$data = bzcompress($serializedData);
					} else {
						$data = gzcompress($serializedData);
					}
					$param = '?map_data=' . base64_encode($data);
				}
				?>
				<a href="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/GoogleMap/Map.php' . $param ?>" title="<?php echo $text; ?>" class="google_map">
					<?php echo $text; ?>
				</a>
				<script type="text/javascript">
				//<![CDATA[
					$(document).ready(function() {
						$(".google_map").colorbox({
							iframe: true,
							innerWidth: '<?php echo (int) (getOption('gmap_width') + 20) ?>px',
							innerHeight: '<?php echo (int) ($cbox_h = getOption('gmap_height') + 20) ?>px',
							close: '<?php echo gettext("close"); ?>'
						});
					});
				//]]>
				</script>
				<?php
			}
			break;
	}
}
?>