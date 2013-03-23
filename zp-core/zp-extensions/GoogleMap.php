<?php
/**
 * Supports Google Maps API version 3
 *
 * Automatically detects if it is on an Image or Album page. Album pages should use the
 * callback function to populate the geo-cordinates to show on the map.
 *
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 5|THEME_PLUGIN;
$plugin_description = gettext('Display Google Maps based on <em>latitude</em> and <em>longitude</em> metadata in the images.');
$plugin_notice = sprintf(gettext('<strong>Note</strong>: Google does place limits on the use of its <a href="%s"><em>Maps API</em></a>. Please review these to be sure your site is in compliance.'),'http://googlegeodevelopers.blogspot.com/2011/10/introduction-of-usage-limits-to-maps.html');
$plugin_author = 'Stephen Billard (sbillard)';


$option_interface = 'googleMap';
if (isset($_zp_gallery_page) && $_zp_gallery_page != 'index.php') {
	if (getOption('gmap_sessions')) {
		zp_session_start();;
	}
	zp_register_filter('theme_head','googleMap::js');
}


/**
 * googleMap
 *
 */
class googleMap {

	function googleMap() {
		/* put any setup code needed here */
		setOptionDefault('gmap_width', 595);
		setOptionDefault('gmap_height', 300);
		setOptionDefault('gmap_map', 1);
		setOptionDefault('gmap_hybrid', 1);
		setOptionDefault('gmap_satellite', 1);
		setOptionDefault('gmap_terrain', 1);
		setOptionDefault('gmap_control_size', 'small');
		setOptionDefault('gmap_control', 'horizontal');
		setOptionDefault('gmap_starting_map', 'hybrid');
		setOptionDefault('gmap_zoom', 16);
		setOptionDefault('gmap_sessions', 1);
		if (OFFSET_PATH==2) {
			setOptionDefault('gmap_display', 'hide');
			purgeOption('gmap_hide');
		}
		setOptionDefault('gmap_display', 'show');
	}

	function getOptionsSupported() {
		$MapTypes =  array(); // order matters here because the first allowed map is selected if the 'gmap_starting_map' is not allowed
		if (getOption('gmap_map')) $MapTypes[gettext('Map')] = 'map';
		if (getOption('gmap_hybrid')) $MapTypes[gettext('Hybrid')] = 'hybrid';
		if (getOption('gmap_satellite')) $MapTypes[gettext('Satellite')] = 'satellite';
		if (getOption('gmap_terrain')) $MapTypes[gettext('Terrain')] = 'terrain';

		$defaultmap = getOption('gmap_starting_map');
		if (array_search($defaultmap, $MapTypes) === false) { // the starting map is not allowed, pick a new one
			$temp = $MapTypes;
			$defaultmap = array_shift($temp);
			setOption('gmap_starting_map', $defaultmap);
		}

		return array(	gettext('Map dimensions—width') => array('key' => 'gmap_width', 'type' => OPTION_TYPE_TEXTBOX,
																	'order'=>6,
																	'desc' => gettext('The default width of the map.')),
									gettext('Map dimensions—height') => array('key' => 'gmap_height', 'type' => OPTION_TYPE_TEXTBOX,
																	'order'=>6.5,
																	'desc' => gettext('The default height of the map.')),
									gettext('Initial Zoom') => array('key' => 'gmap_zoom', 'type' => OPTION_TYPE_TEXTBOX,
																	'order'=>7,
																	'desc' => gettext('The initial zoom of the map.')),
									gettext('Allowed maps') => array('key' => 'gmap_allowed_maps', 'type' => OPTION_TYPE_CHECKBOX_ARRAY,
																	'order'=>1,
																	'checkboxes' => array(gettext('Map') => 'gmap_map', gettext('Satellite') => 'gmap_satellite' ,gettext('Hybrid') => 'gmap_hybrid' ,gettext('Terrain') => 'gmap_terrain'),
																	'desc' => gettext('Select the map types that are allowed.')),
									gettext('Map control size') => array('key' => 'gmap_control_size', 'type' => OPTION_TYPE_RADIO,'buttons' => array(gettext('Small') => 'small',gettext('Large') => 'large'),
																	'order'=>4,
																	'desc' => gettext('Use buttons or list for the map type selector.')),
									gettext('Map controls') => array('key' => 'gmap_control', 'type' => OPTION_TYPE_RADIO,'buttons' => array(gettext('None') => 'none',gettext('Dropdown') => 'dropdown',gettext('Horizontal') => 'horizontal'),
																	'order'=>3,
																	'desc' => gettext('Select the kind of map controls.')),
									gettext('Initial map display selection') => array('key' => 'gmap_starting_map', 'type' => OPTION_TYPE_SELECTOR, 'selections' => $MapTypes,
																	'order'=>2,
																	'desc' => gettext('Select the initial type of map to display.')),
									gettext('Map display') => array('key' => 'gmap_display', 'type' => OPTION_TYPE_SELECTOR,
																	'selections' => array(gettext('show')=>'show', gettext('hide')=>'hide',gettext('colorbox')=>'colorbox'),
																	'order'=>2.5,
																	'desc' => gettext('Select <em>hide</em> to initially hide the map. Select <em>colorbox</em> for the map to display in a colorbox. Select <em>show</em> and the map will display when the page loads.')),
									gettext('Map sessions') => array('key' => 'gmap_sessions', 'type' => OPTION_TYPE_CHECKBOX,
																	'order'=>8,
																	'desc' => gettext('If checked GoogleMaps will use sessions to pass map data for the <em>colorbox</em> display option. We recommend this option be selected. It protects against reference forgery security attacks and mitigates problems with data exceeding the allowed by some browsers.'))
		);
	}

	function handleOption($option, $currentValue) {
	}

	/**
	 * Output the header JS
	 */
	static function js() {
		require_once(dirname(__FILE__).'/GoogleMap/GoogleMap.php');
		require_once(dirname(__FILE__).'/GoogleMap/JSMin.php');
		global $MAP_OBJECT, $_zp_current_image, $_zp_current_album;
		$MAP_OBJECT = new GoogleMapAPI();
		$MAP_OBJECT->setJSAlert('<b>Javascript must be enabled in order to use Google Maps.</b>');
		$MAP_OBJECT->setBrowserAlert('Sorry, the Google Maps API is not compatible with this browser.');
		$MAP_OBJECT->setLocale(substr(getOption('locale'),0,2));
		$MAP_OBJECT->bounds_fudge = 0.001;
		echo $MAP_OBJECT->getHeaderJS()."\n";
		?>
		<style type="text/css">
			<!--
			.hidden_map {
				position: absolute;
				left: -50000px;
			}
			.map_image {
				margin-left: 30%;
				margin-right: 10%;
				border: 0px;
			}
			-->
		</style>
		<?php
	}
}

/**
 * Returns true if the current image has EXIF location data
 *
 * @return bool
 */
function hasMapData() {
	$exif = getImageMetaData(NULL, false);
	if(!empty($exif['EXIFGPSLatitude']) && !empty($exif['EXIFGPSLongitude'])){
		return true;
	}
	return false;
}

/**
 * $eturns coordiante information for an image
 * @param $obj
 */
function getGeoCoord($obj) {
	$result = false;
	if (is_object($obj) && $obj->table == 'images') {
		$exif = $obj->getMetaData();
		if(!empty($exif['EXIFGPSLatitude']) && !empty($exif['EXIFGPSLongitude'])){
			$lat_c = explode('.',str_replace(',', '.', $exif['EXIFGPSLatitude']));
			$lat_f = round((float) abs($lat_c[0])+$lat_c[1]/pow(10,strlen($lat_c[1])),5);
			if (strtoupper(@$exif['EXIFGPSLatitudeRef']{0}) == 'S') {
				$lat_f = (float) -$lat_f;
			}
			$long_c = explode('.',str_replace(',', '.', $exif['EXIFGPSLongitude']));
			$long_f = round((float) abs($long_c[0])+$long_c[1]/pow(10,strlen($long_c[1])),5);
			if (strtoupper(@$exif['EXIFGPSLongitudeRef']{0}) == 'W') {
				$long_f = (float) -$long_f;
			}
			$result = array('lat'=>$lat_f,'long'=>$long_f, 'title'=>$obj->getTitle(), 'desc'=>$obj->getDesc());
		}
	}
	return $result;
}

/**
 * Add a point to a map object
 * @param $MAP_OBJECT
 * @param $coord	coordinates array
 */
function addGeoCoord($MAP_OBJECT, $coord) {
	if ($coord) {
		$MAP_OBJECT->addMarkerByCoords($coord['long'], $coord['lat'], $coord['title'], $coord['desc']);
	}
}

/**
 * Adds a geoPoint after first insuring it uses periods for the decimal separator
 *
 * @deprecated
 *
 * @param object $MAP_OBJECT
 * @param string $lat Latitude of the point
 * @param string $long Longitude of the point
 * @param string $title point title
 * @param string $desc point description
 */
function addPoint($MAP_OBJECT, $lat, $long, $title, $desc) {
	//convert to float cononically
	$lat_c = explode('.',str_replace(',', '.', $lat));
	$lat_f = round((float) abs($lat_c[0])+$lat_c[1]/pow(10,strlen($lat_c[1])),5);
	if ($lat_c<0) {
		$lat_f = (float) -1*$lat_f;
	}
	$long_c = explode('.',str_replace(',', '.', $long));
	$long_f = round((float) abs($long_c[0])+$long_c[1]/pow(10,strlen($long_c[1])),5);
	if ($long<0) {
		$long_f = (float) -1*$long_f;
	}
	$MAP_OBJECT->addMarkerByCoords($long_f, $lat_f, $title, $desc);
}

/**
 * Gathers the data for an image
 * @param image $obj
 * @param object $MAP_OBJECT
 */
function getImageGeodata($obj,$MAP_OBJECT) {
	$coord = getGeoCoord($obj);
	if ($coord) {
		addGeoCoord($MAP_OBJECT, $coord);
	}
	return $coord;
}

/**
 * Gathers the data for an album
 * @param album $obj
 * @param object $MAP_OBJECT
 */
function getAlbumGeodata($obj,$MAP_OBJECT){
	$result = false;
	$images = $obj->getImages(0, 0, null, null, false);
	foreach ($images as $an_image) {
		$image = newImage($obj, $an_image);
		$coord = getGeoCoord($image);
		if ($coord) {
			$result = true;	//	at least one image has geodata
			$coord['desc'] = '<a href="javascript:image(\''.$obj->name.'\',\''.$image->filename.'\');"><img src="'.pathurlencode($image->getThumb()).'" alt="'.$image->getDesc().'" '.'class="map_image" /></a>';
			if ($image->getDesc()) $coord['desc'] .= '<p align=center>' . $image->getDesc()."</p>";
			addGeoCoord($MAP_OBJECT, $coord);
		}
	}
	return $result;
}

/**
 * Output the google map
 *
 * @param string $text text for the "toggle" link that shows/hides the map. Set empty to omit
 * @param string $id used to set the IDs for the toggle href element ($id_toggle) and the map element ($id_data)
 * @param string $hide initial map state: "hide", "show", or "colorbox"
 * @param object $obj optional image/album object. Pass string for generic map and use callback to set points
 * @param function $callback optional callback function to set map options.
 */
function printGoogleMap($text=NULL, $id=NULL, $hide=NULL, $obj=NULL, $callback=NULL) {
	global $_zp_current_album,$_zp_current_image;
	if (is_null($obj)) {
		if (is_null($_zp_current_image)) {
			$obj = $_zp_current_album;
		} else {
			$obj = $_zp_current_image;
		}
	}
	if (is_null($obj)) {
		$MAP_OBJECT = NULL;
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

	$MAP_OBJECT = new GoogleMapAPI($maptype = $type.$typeid);
	$MAP_OBJECT->_minify_js = !TEST_RELEASE;
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
	$empty = get_object_vars($MAP_OBJECT);
	switch ($type) {
		case 'images':
			if (getImageGeodata($obj,$MAP_OBJECT)) {
				break;
			}
			$MAP_OBJECT = NULL;
			return false;
		case 'albums':
			if (getAlbumGeodata($obj,$MAP_OBJECT)) {
				break;
			}
			$MAP_OBJECT = NULL;
			return false;
		default:
			break;
	}
	$type = $type.$typeid.'_';
	if (is_null($id)) {
		$id = $type.'googlemap';
	}
	$id_toggle = $id.'_toggle';
	$id_data = $id.'_data';

	if (!is_null($callback)) {
		call_user_func($callback,$MAP_OBJECT);
	}


	switch ($hide) {
		case 'colorbox':
			if (zp_has_filter('theme_head','colorbox::css')) {
				$w = str_replace('px','',$MAP_OBJECT->width)+20;
				$h = str_replace('px','',$MAP_OBJECT->height)+20;

				$mapvars = get_object_vars($MAP_OBJECT);
				foreach ($mapvars as $key=>$value) {
					if ($empty[$key] == $mapvars[$key]) {
						unset ($mapvars[$key]);
					}
				}
				if (getOption('gmap_sessions')) {
					$param = '';
					$_SESSION['GoogleMapVars'] = $mapvars;
				} else {
					if (isset($mapvars['_markers'])) {
						//	force rounding of lat/lon for shorter string
						foreach ($mapvars['_markers'] as $key=>$marker) {
							$mapvars['_markers'][$key]['lat'] = (string) $marker['lat'];
							$mapvars['_markers'][$key]['lon'] = (string) $marker['lon'];
						}
					}
					$serializedData = serialize($mapvars);
					if (function_exists('bzcompress')) {
						$data = bzcompress($serializedData);
					} else {
						$data = gzcompress($serializedData);
					}
					$param = '&amp;data='.base64_encode($data);
				}
				?>

				<a href="<?php echo WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/GoogleMap/m.php?type='.$maptype.$param ?>" title="<?php echo $text; ?>" class="google_map">
					<?php echo $text; ?>
				</a>
				<script type="text/javascript">
					// <!-- <![CDATA[
					$(document).ready(function(){
						$(".google_map").colorbox({
																	iframe:true,
																	innerWidth:'<?php echo $w; ?>px',
																	innerHeight:'<?php echo $h; ?>px',
																	close: '<?php echo gettext("close"); ?>'
																	});
					});
					// ]]> -->
				</script>
				<?php
				break;
			}
		case 'hide':
			?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				function image(album,image) {
					window.location = '<?php echo WEBPATH ?>/index.php?album='+album+'&image='+image;
				}
				function toggle_<?php echo $id_data; ?>() {
					if ($('#<?php echo $id_data; ?>').hasClass('hidden_map')) {
						$('#<?php echo $id_data; ?>').removeClass('hidden_map');
					} else {
						$('#<?php echo $id_data; ?>').addClass('hidden_map');
					}
				}
				// ]]> -->
			</script>
			<a id="<?php echo $id_toggle; ?>" href="javascript:toggle_<?php echo $id_data; ?>();" title="<?php  echo gettext('Display or hide the Google Map.'); ?>">
				<?php echo $text; ?>
			</a>
			<div id="<?php echo $id_data; ?>" class="hidden_map">
				<?php
				echo $MAP_OBJECT->getMapJS();
				echo $MAP_OBJECT->printMap();
				echo $MAP_OBJECT->printOnLoad();
				?>
			</div>
			<?php
			break;
		case 'show':
			?>
			<script type="text/javascript">
				// <!-- <![CDATA[
				function image(album,image) {
					window.location = '<?php echo WEBPATH ?>/index.php?album='+album+'&image='+image;
				}
				// ]]> -->
			</script>
			<a id="<?php echo $id_toggle; ?>" href="javascript:toggleMap('<?php echo $id_data; ?>');" title="<?php  echo gettext('Display or hide the Google Map.'); ?>">
				<?php echo $text; ?>
			</a>
			<div id="<?php echo $id_data; ?>">
				<?php
				echo $MAP_OBJECT->getMapJS();
				echo $MAP_OBJECT->printMap();
				echo $MAP_OBJECT->printOnLoad();
				?>
			</div>
			<?php
			break;
	}
}

?>
