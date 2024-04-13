<?php
/**
 * A Zenphoto plugin for showing OpenStreetMap maps using LeafletJS (http://leafletjs.com) for images or images from * 
 * albums with embeded geodata or maps with custom geodata.
 * 
 * Also includes 
 * 
 * - marker cluster plugin https://github.com/Leaflet/Leaflet.markercluster by Dave Leaver
 * - MousePosition plugin https://github.com/ardhi/Leaflet.MousePosition by Ardhi Lukianto
 * - Leaflet-MiniMap plugin: https://github.com/Norkart/Leaflet-MiniMap
 * - leaflet-providers plugin: https://github.com/leaflet-extras/leaflet-providers
 * 
 * @author Malte Müller (acrylian), Fred Sondaar (fretzl), gjr, Vincent Bourganel (vincent3569)
 * @licence GPL v3 or later
 * @package zpcore\plugins\openstreetmap
 */
$plugin_is_filter = 5 | THEME_PLUGIN;
$plugin_description = gettext("A Zenphoto plugin for displaying OpenStreetMap based maps using LeafletJS for images or images from albums with embeded geodata.");
$plugin_author = "Malte Müller (acrylian), Fred Sondaar (fretzl), gjr, Vincent Bourganel (vincent3569)";
$plugin_notice = gettext('Privacy note: This plugin uses external third party sources');
$option_interface = 'openStreetMapOptions';
$plugin_category = gettext('Misc');

zp_register_filter('theme_head', 'openStreetMap::scripts');

class openStreetMapOptions {

	function __construct() {
		renameOption('osmap_controlpos', 'osmap_zoomcontrolpos');
		renameOption('osmap_maptiles', 'osmap_defaultlayer');

		setOptionDefault('osmap_width', '100%'); //responsive by default!
		setOptionDefault('osmap_height', '300px');
		setOptionDefault('osmap_zoom', 13);
		setOptionDefault('osmap_minzoom', 2);
		setOptionDefault('osmap_maxzoom', 18);

		setOptionDefault('osmap_zoomcontrolpos', 'topleft');
		setOptionDefault('osmap_defaultlayer', 'OpenStreetMap.Mapnik');

		setOptionDefault('osmap_clusterradius', 40);
		setOptionDefault('osmap_markerpopup', 1);
		setOptionDefault('osmap_markerpopup_title', 1);
		setOptionDefault('osmap_markerpopup_desc', 1);
		setOptionDefault('osmap_markerpopup_thumb', 1);
		setOptionDefault('osmap_showlayerscontrol', 0);
		setOptionDefault('osmap_layerscontrolpos', 'topright');
		$layerslist = openStreetMap::getLayersList();
		foreach ($layerslist as $layer_dbname) {
			setOptionDefault($layer_dbname, 0);
		}
		setOptionDefault('osmap_showscale', 1);
		setOptionDefault('osmap_showalbummarkers', 0);
		setOptionDefault('osmap_showminimap', 0);
		setOptionDefault('osmap_minimap_width', 100);
		setOptionDefault('osmap_minimap_height', 100);
		setOptionDefault('osmap_minimap_zoom', -5);
		setOptionDefault('osmap_cluster_showcoverage_on_hover', 0);
		if (class_exists('cacheManager')) {
			cacheManager::deleteCacheSizes('openstreetmap');
			cacheManager::addCacheSize('openstreetmap', 150, NULL, NULL, NULL, NULL, NULL, NULL, true, NULL, NULL, NULL);
		}
	}

	function getOptionsSupported() {
		$providers = array_combine(openStreetMap::getTileProviders(), openStreetMap::getTileProviders());
		$layerslist = openStreetMap::getLayersList();

		$options = array(
				gettext('Map dimensions—width') => array(
						'key' => 'osmap_width',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 1,
						'desc' => gettext("Width of the map including the unit name e.g 100% (default for responsive map), 100px or 100em.")),
				gettext('Map dimensions—height') => array(
						'key' => 'osmap_height',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 2,
						'desc' => gettext("Height of the map including the unit name e.g 100% (default for responsive map), 100px or 100em.")),
				gettext('Map zoom') => array(
						'key' => 'osmap_zoom',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 3,
						'desc' => gettext("Default zoom level.")),
				gettext('Map minimum zoom') => array(
						'key' => 'osmap_minzoom',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 5,
						'desc' => gettext("Default minimum zoom level possible.")),
				gettext('Map maximum zoom') => array(
						'key' => 'osmap_maxzoom',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 6,
						'desc' => gettext("Default maximum zoom level possible. If no value is defined, use the maximum zoom level of the map used (may be different for each map).")),
				gettext('Default layer') => array(
						'key' => 'osmap_defaultlayer',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 7,
						'selections' => $providers,
						'desc' => gettext('The default map tile provider to use. Only free providers are included.'
										. ' Some providers (Here, Mapbox, Thunderforest, Geoportail) require access credentials and registration.'
										. ' More info on <a href="https://github.com/leaflet-extras/leaflet-providers">leaflet-providers</a>')),
				gettext('Zoom controls position') => array(
						'key' => 'osmap_zoomcontrolpos',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 8,
						'selections' => array(
								gettext('Top left') => 'topleft',
								gettext('Top right') => 'topright',
								gettext('Bottom left') => 'bottomleft',
								gettext('Bottom right') => 'bottomright'
						),
						'desc' => gettext('Position of the zoom controls')),
				gettext('Cluster radius') => array(
						'key' => 'osmap_clusterradius',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 9,
						'desc' => gettext("The radius when marker clusters should be used.")),
				gettext('Show cluster coverage on hover') => array(
						'key' => 'osmap_cluster_showcoverage_on_hover',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 10,
						'desc' => gettext("Enable if you want to the bounds of a marker cluster on hover.")),
				gettext('Marker popups') => array(
						'key' => 'osmap_markerpopup',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 11,
						'desc' => gettext("Enable this if you wish info popups on the map markers. Only for album context or custom geodata.")),
				gettext('Marker popups with thumbs') => array(
						'key' => 'osmap_markerpopup_thumb',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 12,
						'desc' => gettext("Enable if you want to show thumb of images in the marker popups. Only for album context.")),
				gettext('Marker popups with title') => array(
						'key' => 'osmap_markerpopup_title',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 13,
						'desc' => gettext("Enable if you want to show title of images in the marker popups. Only for album context.")),
				gettext('Marker popups with description') => array(
						'key' => 'osmap_markerpopup_desc',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 14,
						'desc' => gettext("Enable if you want to show desc of images in the marker popups. Only for album context.")),
				gettext('Show layers controls') => array(
						'key' => 'osmap_showlayerscontrol',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 14.2,
						'desc' => gettext("Enable if you want to show layers controls with selected layers list below.")),
				gettext('Layers list') => array(
						'key' => 'osmap_layerslist',
						'type' => OPTION_TYPE_CHECKBOX_UL,
						'order' => 14.4,
						'checkboxes' => $layerslist,
						'desc' => gettext("Choose layers list to show in layers controls. No need to select the default layer again, otherwise it will be de-duplicated.")),
				gettext('Layers controls position') => array(
						'key' => 'osmap_layerscontrolpos',
						'type' => OPTION_TYPE_SELECTOR,
						'order' => 14.6,
						'selections' => array(
								gettext('Top left') => 'topleft',
								gettext('Top right') => 'topright',
								gettext('Bottom left') => 'bottomleft',
								gettext('Bottom right') => 'bottomright'
						),
						'desc' => gettext('Position of the layers controls')),
				gettext('Show scale') => array(
						'key' => 'osmap_showscale',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 15,
						'desc' => gettext("Enable if you want to show scale overlay (kilometers and miles).")),
				gettext('Show cursor position') => array(
						'key' => 'osmap_showcursorpos',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 16,
						'desc' => gettext("Enable if you want to show the coordinates if moving the cursor over the map.")),
				gettext('Show album markers') => array(
						'key' => 'osmap_showalbummarkers',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 17,
						'desc' => gettext("Enable if you want to show the map on the single image page not only the marker of the current image but all markers from the album. The current position will be highlighted.")),
				gettext('Mini map') => array(
						'key' => 'osmap_showminimap',
						'type' => OPTION_TYPE_CHECKBOX,
						'order' => 18,
						'desc' => gettext("Enable if you want to show an overview mini map in the lower right corner.")),
				gettext('Mini map: width') => array(
						'key' => 'osmap_minimap_width',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 19,
						'desc' => gettext("Pixel width")),
				gettext('Mini map: height') => array(
						'key' => 'osmap_minimap_height',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 20,
						'desc' => gettext("Pixel height")),
				gettext('Mini map: Zoom level') => array(
						'key' => 'osmap_minimap_zoom',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 21,
						'desc' => gettext("The offset applied to the zoom in the minimap compared to the zoom of the main map. Can be positive or negative, defaults to -5.")),
				gettext('HERE - App id') => array(
						'key' => 'osmap_here_appid',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 22,
						'desc' => ''),
				gettext('HERE - App code') => array(
						'key' => 'osmap_here_appcode',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 23,
						'desc' => ''),
				gettext('Mapbox - Access token') => array(
						'key' => 'osmap_mapbox_accesstoken',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 24,
						'desc' => ''),
				gettext('Thunderforest - ApiKey') => array(
						'key' => 'osmap_thunderforest_apikey',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 25,
						'desc' => ''),
				gettext('GeoportailFrance - ApiKey') => array(
						'key' => 'osmap_geoportailfrance_apikey',
						'type' => OPTION_TYPE_TEXTBOX,
						'order' => 26,
						'desc' => ''),
		);
		return $options;
	}

}

/**
 * The class for all OSM map related functionality
 */
class openStreetMap {

	/**
	 * Contains the array of the image or images from albums geodata
	 * @var array
	 */
	public $geodata = NULL;

	/**
	 * Contains a string presenting a Javascript array of geodata for leafletjs
	 * @var array
	 */
	public $geodatajs = NULL;

	/**
	 * geodata array('min' => array(lat,lng), 'max => array(lat,lng))
	 * Default created from an image or the images of an album. 
	 * @var array
	 */
	public $fitbounds = NULL;

	/**
	 * geodata array(lat,lng)
	 * Default created from an image or the images of an album. 
	 * @var array
	 */
	public $center = NULL;

	/**
	 * Optional class name to attach to the map html
	 * @var string
	 */
	public $class = '';

	/**
	 * "single" (one marker)
	 * "cluster" (several markers always clustered)
	 * "single-cluster" (markers of the images of the current album)
	 * Default created by the $geodata property: "single "if array with one entry, "cluster" if more entries
	 * @var string
	 */
	public $mode = NULL;

	/**
	 * 
	 * Default false if set to true on single image maps the markers of all other images are shown as well.
	 * The current image's position will be highlighted.
	 * @var bool
	 */
	public $showalbummarkers = false;

	/**
	 * geodata array(lat,lng)
	 * Default created from the image marker or from the markers of the images of an album if in context
	 * @var array
	 */
	public $mapcenter = NULL;

	/**
	 * Unique number if using more than one map on a page
	 * @var int
	 */
	public $mapnumber = '';

	/**
	 * Default 100% for responsive map. Values like "100%", "100px" or "100em"
	 * Default taken from plugin options
	 * @var string
	 */
	public $width = NULL;

	/**
	 * Values like "100px" or "100em"
	 * Default taken from plugin options
	 * @var string 
	 */
	public $height = NULL;

	/**
	 * Default zoom state
	 * Default taken from plugin options
	 * @var int
	 */
	public $zoom = NULL;
	public $minzoom = NULL;
	public $maxzoom = NULL;

	/**
	 * The tile providers to use. Select from the $tileproviders property like $this->maptiles = $this->tileproviders['<desired provider>']
	 * Must be like array('<map provider url>','<attribution as requested>')
	 * Default taken from plugin options
	 * @var array
	 */
	public $defaultlayer = NULL;
	public $layerslist = NULL;
	public $layer = NULL;

	/**
	 * Radius when clusters should be created on more than one marker
	 * Default taken from plugin options
	 * @var int
	 */
	public $clusterradius = NULL;

	/**
	 * If used on albums or several custom markers if you wish popups on the markers
	 * If using custom markers you need to provide the content for the popups withn the $geodata property
	 * Default taken from plugin options
	 * @var bool
	 */
	public $markerpopup = false;

	/**
	 * Only if on an album page and if $imagepopups are enabled.
	 * If the imagepopus should contain thumbs of the images
	 * Default taken from plugin options
	 * @var bool
	 */
	public $markerpopup_title = false;
	public $markerpopup_desc = false;
	public $markerpopup_thumb = false;
	public $showmarkers = true;

	/**
	 * Mini map parameters
	 * @var string
	 */
	public $showminimap = false;
	public $minimap_width = NULL;
	public $minimap_height = NULL;
	public $minimap_zoom = NULL;

	/**
	 * Position of the map controls: "topleft", "topright", "bottomleft", "bottomright"
	 * Default taken from plugin options
	 * @var string
	 */
	public $zoomcontrolpos = NULL;
	public $showscale = NULL;
	public $showcursorpos = NULL;

	/**
	 * The current image or album object if not passing custom geodata
	 * @var object
	 */
	public $obj = NULL;

	/**
	 * The predefined array of all free map tile providers for Open Street Map
	 * @var array
	 */
	public $tileproviders = NULL;

	/**
	 * Show the bounds of a marker cluster on hover
	 * Default taken from plugin options
	 * @var bool
	 */
	public $cluster_showcoverage_on_hover = NULL;
	
	/**
	 * Show layers controls with selected layers
	 * Default taken from plugin options
	 * @var bool
	 */
	public $showlayerscontrol = NULL;
	
	/**
	 * Position of the layers controls: "topleft", "topright", "bottomleft", "bottomright"
	 * Default taken from plugin options
	 * @var string
	 */
	public $layerscontrolpos = NULL;
	
	/**
	 * If no $geodata array is passed the function gets geodata from the current image or the images of the current album
	 * if in appropiate context.
	 * 
	 * Alternatively you can pass an image or album object directly. This ignores the $geodata parameter then.
	 * 
	 * The $geodata array requires this structure:
	 * Single marker:
	 * 
	 * array(
	 *   array(
	 *      'lat' => <latitude>,
	 *      'long' => <longitude>,
	 *      'title' => 'some title',
	 *      'desc' => 'some description',
	 *      'thumb' => 'some html' // an <img src=""> call or else. 
	 *   )
	 * );
	 * 
	 * If you use html for title, desc or thumb be sure to use double quotes for attributes to avoid JS conflicts.
	 * For several markers add more arrays to the array. 
	 *
	 * If you neither pass $geodata, an object or there is no current image/album you can still display a map.
	 * But in this case you need to set the $center and $fitbounds properties manually before printing a map.
	 *
	 * @global string $_zp_gallery_page
	 * @param array $geodata Array as noted above if no current image or album should be used 
	 * @param obj Image or album object If set this object is used and $geodatat is ignored if set as well
	 */
	function __construct($geodata = NULL, $obj = NULL) {
		global $_zp_gallery_page, $_zp_current_album, $_zp_current_image;

		$this->showalbummarkers = getOption('osmap_showalbummarkers');
		$this->tileproviders = self::getTileProviders();
		if (is_object($obj)) {
			if (Image::isImageClass($obj)) {
				$this->obj = $obj;
				$this->mode = 'single';
			} else if (AlbumBase::isAlbumClass($obj)) {
				$this->obj = $obj;
				$this->mode = 'cluster';
			}
		} else {
			if (is_array($geodata)) {
				if (count($geodata) < 1) {
					$this->mode = 'single';
				} else {
					$this->mode = 'cluster';
				}
				$this->geodata = $geodata;
			} else {
				switch ($_zp_gallery_page) {
					case 'image.php':
						if ($this->showalbummarkers) {
							$this->obj = $_zp_current_album;
							$this->mode = 'single-cluster';
						} else {
							$this->obj = $_zp_current_image;
							$this->mode = 'single';
						}
						break;
					case 'album.php':
					case 'favorites.php':
						$this->obj = $_zp_current_album;
						$this->mode = 'cluster';
						$this->markerpopup_title = getOption('osmap_markerpopup_title');
						$this->markerpopup_desc = getOption('osmap_markerpopup_desc');
						$this->markerpopup_thumb = getOption('osmap_markerpopup_thumb');
					case 'search.php':
						$this->mode = 'cluster';
						$this->markerpopup_title = getOption('osmap_markerpopup_title');
						$this->markerpopup_desc = getOption('osmap_markerpopup_desc');
						$this->markerpopup_thumb = getOption('osmap_markerpopup_thumb');
						break;
				}
			}
		}
		$this->center = $this->getCenter();
		$this->fitbounds = $this->getFitBounds();
		$this->geodata = $this->getGeoData();
		$this->width = getOption('osmap_width');
		$this->height = getOption('osmap_height');
		$this->zoom = getOption('osmap_zoom');
		$this->minzoom = getOption('osmap_minzoom');
		$this->maxzoom = getOption('osmap_maxzoom');
		$this->zoomcontrolpos = getOption('osmap_zoomcontrolpos');
		$this->defaultlayer = $this->setMapTiles(getOption('osmap_defaultlayer'));
		$this->clusterradius = getOption('osmap_clusterradius');
		$this->cluster_showcoverage_on_hover = getOption('osmap_cluster_showcoverage_on_hover');
		$this->markerpopup = getOption('osmap_markerpopup');
		$this->markerpopup_title = getOption('osmap_markerpopup_title');
		$this->markerpopup_desc = getOption('osmap_markerpopup_desc');
		$this->markerpopup_thumb = getOption('osmap_markerpopup_thumb');
		$this->showlayerscontrol = getOption('osmap_showlayerscontrol');
		// generate an array of selected layers
		$selectedlayerslist = array();
		$layerslist = self::getLayersList();
		if ($layerslist) {
			foreach ($layerslist as $layer => $layer_dbname) {
				if (getOption($layer_dbname)) {
					$selectedlayerslist[$layer] = $layer;
				}
			}
		}
		// deduplicate default Layer from layers list
		unset($selectedlayerslist[array_search($this->defaultlayer, $selectedlayerslist)]);
		$this->layerslist = $selectedlayerslist;
		$this->layerscontrolpos = getOption('osmap_layerscontrolpos');
		$this->showscale = getOption('osmap_showscale');
		$this->showcursorpos = getOption('osmap_showcursorpos');
		$this->showminimap = getOption('osmap_showminimap');
		$this->minimap_width = getOption('osmap_minimap_width');
		$this->minimap_height = getOption('osmap_minimap_height');
		$this->minimap_zoom = getOption('osmap_minimap_zoom');
	}

	/**
	 * Assigns the needed JS and CSS
	 */
	static function scripts() {
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/openstreetmap/leaflet.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/openstreetmap/MarkerCluster.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/openstreetmap/MarkerCluster.Default.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/openstreetmap/openstreetmap.css" />
		<?php
		if (getOption('osmap_showcursorpos')) {
			?>
			<link rel="stylesheet" type="text/css" href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/openstreetmap/L.Control.MousePosition.css" />
			<?php
		}
		if (getOption('osmap_showminimap')) {
			?>
			<link rel="stylesheet" type="text/css" href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/openstreetmap/Control.MiniMap.min.css" />
			<?php
		}
		?>
		<script src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/openstreetmap/leaflet.js"></script>
		<script src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/openstreetmap/leaflet.markercluster.js"></script>
		<script src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/openstreetmap/leaflet-providers.js"></script>
		<?php
		if (getOption('osmap_showcursorpos')) {
			?>
			<script src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/openstreetmap/L.Control.MousePosition.js"></script>
			<?php
		}
		if (getOption('osmap_showminimap')) {
			?>
			<script src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/openstreetmap/Control.MiniMap.min.js"></script>
			<?php
		}
	}

	/**
	 * $returns coordinate informations for an image
	 * Adapted from the offical Zenphoto GoogleMap plugin by Stephen Billard (sbillard) & Vincent Bourganel (vincent3569)
	 * @param $image	image object
	 */
	function getImageGeodata($image) {
		global $_zp_current_image;
		$result = array();
		$gps = $image->getGeodata();
		if ($gps) {
			$thumb = "<a href='" . $image->getLink() . "'><img src='" . $image->getCustomImage(150, NULL, NULL, NULL, NULL, NULL, NULL, true) . "' alt='' /></a>";
			$current = 0;
			if ($this->mode == 'single-cluster' && isset($_zp_current_image) && ($image->filename == $_zp_current_image->filename && $image->getAlbumname() == $_zp_current_image->getAlbumname())) {
				$current = 1;
			}
			$result = array(
					'lat' => $gps['lat'],
					'long' => $gps['long'],
					'title' => "<a href='" . $image->getLink() . "'>". js_encode(shortenContent($image->getTitle(), 50, '...')) . "</a><br />",
					'desc' => js_encode(shortenContent($image->getDesc(), 100, '...')),
					'thumb' => $thumb,
					'current' => $current
			);
		}
		return $result;
	}

	/**
	 * Gathers the map data for an album
	 * Adapted from the offical Zenphoto GoogleMap plugin by Stephen Billard (sbillard) & Vincent Bourganel (vincent3569)
	 * @param $album		album object
	 */
	function getAlbumGeodata($album) {
		$result = array();
		$images = $album->getImages(0, 0, null, null, false);
		foreach ($images as $an_image) {
			$image = Image::newImage($album, $an_image);
			$imggeodata = $this->getImageGeodata($image);
			if (!empty($imggeodata)) {
				$result[] = $imggeodata;
			}
		}
		return $result;
	}

	/**
	 * Extracts the geodata from an image or the images of an album 
	 * and creates the JS arrays for leaflet including title, description and thumb if set.
	 * @return array
	 */
	function getGeoData() {
		global $_zp_current_image, $_zp_current_album;

		$geodata = array();
		if (!is_null($this->geodata)) {
			return $this->geodata;
		}
		switch ($this->mode) {
			case 'single':
				$imggeodata = $this->getImageGeodata($this->obj);
				if (!empty($imggeodata)) {
					$geodata = array($imggeodata);
				}
				break;
			case 'single-cluster':
			case 'cluster':
				$albgeodata = $this->getAlbumGeodata($this->obj);
				if (!empty($albgeodata)) {
					$geodata = $albgeodata;
				}
				break;
		}
		if (empty($geodata)) {
			return NULL;
		} else {
			return $this->geodata = $geodata;
		}
	}

	/**
	 * Processes the geodata returned by getGeoData() and formats it to a string 
	 * presenting a multidimensional Javascript array for use with leafletjs
	 * @return string
	 */
	function getGeoDataJS() {
		if (!is_null($this->geodatajs)) {
			return $this->geodatajs;
		}
		$geodata = $this->getGeoData();
		if (!empty($geodata)) {
			$count = -1;
			$js_geodata = '';
			foreach ($geodata as $geo) {
				$count++;
				$js_geodata .= ' geodata[' . $count . '] = {
					lat : "' . $geo['lat'] . '",
					long : "' . $geo['long'] . '",
					title : "' . $geo['title'] . '",
					desc : "' . $geo['desc'] . '",
					thumb : "' . $geo['thumb'] . '",
					current : "' . $geo['current'] . '"

				};';
			}
			return $this->geodatajs = $js_geodata;
		}
	}

	/**
	 * Returns the bounds the map should fit based on the geodata of an image or images of an album
	 * @return array
	 */
	function getFitBounds() {
		if (!is_null($this->fitbounds)) {
			return $this->fitbounds;
		}
		$geodata = $this->getGeoData();
		if (!empty($geodata)) {
			$geocount = count($geodata);
			$bounds = '';
			$count = '';
			foreach ($geodata as $g) {
				$count++;
				$bounds .= '[' . $g['lat'] . ',' . $g['long'] . ']';
				if ($count < $geocount) {
					$bounds .= ',';
				}
			}
			$this->fitbounds = $bounds;
		}
		return $this->fitbounds;
	}

	/**
	 * Returns the center point of the map. On an single image it is the marker of the image itself.
	 * On images from an album it is calculated from their geodata
	 * @return array
	 */
	function getCenter() {
		//$this->center = array(53.18, 10.38); //demotest
		if (!is_null($this->center)) {
			return $this->center;
		}
		$geodata = $this->getGeoData();
		if (!empty($geodata)) {
			switch ($this->mode) {
				case 'single':
					$this->center = array($geodata[0]['lat'], $geodata[0]['long']);
					break;
				case 'single-cluster':
					foreach ($geodata as $geo) {
						if ($geo['current'] == 1) {
							$this->center = array($geo['lat'], $geo['long']);
							break;
						}
					}
					break;
				case 'cluster':
					//for demo tests only needs to be calculated properly later on!
					$this->center = array($geodata[0]['lat'], $geodata[0]['long']);
					break;
			}
		} else {
			//fallback if no geodata at all
			$this->center = ''; // not null as we don't need to re-do if there is nothing
		}
		// fallback if geodata was somehow wrong
		if (empty($this->center[0]) || empty($this->center[1])) {
			$this->center = '';
		}
		return $this->center;
	}

	/**
	 * Return the map tile js definition for leaflet and its leaflet-providers plugin. 
	 * For certain map providers it include the access credentials.
	 * 
	 * @return string
	 */
	function getTileLayerJS() {
		$maptile = explode('.', $this->layer);
		switch ($maptile[0]) {
			case 'MapBox':
				// should be Mapbox but follow leaflet-providers behavior
				return "L.tileLayer.provider('" . $maptile[0] . "', {"
								. "id: '" . strtolower($this->layer) . "', "
								. "accessToken: '" . getOption('osmap_mapbox_accesstoken') . "'"
								. "})";
			case 'HERE':
				return "L.tileLayer.provider('" . $this->layer . "', {"
								. "app_id: '" . getOption('osmap_here_appid') . "', "
								. "app_code: '" . getOption('osmap_here_appcode') . "'"
								. "})";
			case 'Thunderforest':
				return "L.tileLayer.provider('" . $this->layer . "', {"
								. "apikey: '" . getOption('osmap_thunderforest_apikey') . "'"
								. "})";
			case 'GeoportailFrance':
				return "L.tileLayer.provider('" . $this->layer . "', {"
								. "apikey: '" . getOption('osmap_geoportailfrance_apikey') . "'"
								. "})";
			default:
				return "L.tileLayer.provider('" . $this->layer . "')";
		}
	}

	/**
	 * Prints the required HTML and JS for the map
	 */
	function printMap() {
		$class = '';
		if (!empty($this->class)) {
			$class = ' class="' . $this->class . '"';
		}
		$geodataJS = $this->getGeoDataJS();
		if (!empty($geodataJS) && !empty($this->center)) {
			?>
			<div id="osm_map<?php echo $this->mapnumber; ?>"<?php echo $class; ?> style="width:<?php echo $this->width; ?>; height:<?php echo $this->height; ?>;"></div>
			<script>
				var geodata = new Array();
			<?php echo $geodataJS; ?>
				var map = L.map('osm_map<?php echo $this->mapnumber; ?>', {
					center: [<?php echo $this->center[0]; ?>, <?php echo $this->center[1]; ?>],
					zoom: <?php echo $this->zoom; ?>, //option
					zoomControl: false, // disable so we can position it below
					minZoom: <?php echo $this->minzoom; ?>,
			<?php if (!empty($this->maxzoom)) { ?>
						maxZoom: <?php echo $this->maxzoom; ?>
			<?php } ?>
				});

			<?php
			if (!$this->showlayerscontrol) {
				$this->layer = $this->defaultlayer;
				echo $this->getTileLayerJS() . '.addTo(map);';
			} else {
				$defaultlayer = $this->defaultlayer;
				$layerslist = $this->layerslist;
				$layerslist[$defaultlayer] = $defaultlayer;
				ksort($layerslist); // order layers list including default layer
				$baselayers = "";
				foreach ($layerslist as $layer) {
					if ($layer == $defaultlayer) {
						$baselayers = $baselayers . "'" . $defaultlayer . "': defaultLayer,\n";
					} else {
						$this->layer = $layer;
						$baselayers = $baselayers . "'" . $layer . "': " . $this->getTileLayerJS() . ",\n";
					}
				}
				?>
					var defaultLayer = <?php $this->layer = $this->defaultlayer;
				echo $this->getTileLayerJS(); ?>.addTo(map);
					var baseLayers = {
				<?php echo $baselayers; ?>
					};

					L.control.layers(baseLayers, null, {position: '<?php echo $this->layerscontrolpos; ?>'}).addTo(map);
				<?php
			}
			if ($this->mode == 'cluster' && $this->fitbounds) {
				?>
					map.fitBounds([<?php echo $this->fitbounds; ?>]);
				<?php
			}
			if ($this->showminimap) {
				?>
					var osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
					var osm2 = new L.TileLayer(osmUrl);
					var miniMap = new L.Control.MiniMap(osm2, {
						toggleDisplay: true,
						zoomLevelOffset: <?php echo $this->minimap_zoom; ?>,
						width: <?php echo $this->minimap_width; ?>,
						height: <?php echo $this->minimap_height; ?>
					}).addTo(map);
				<?php
			}
			if ($this->showscale) {
				?>
					L.control.scale().addTo(map);
			<?php } ?>

				L.control.zoom({position: '<?php echo $this->zoomcontrolpos; ?>'}).addTo(map);
			<?php if ($this->showcursorpos) { ?>
					L.control.mousePosition().addTo(map);
				<?php
			}
			if ($this->showmarkers) {
				switch ($this->mode) {
					case 'single':
						?>
							var marker = L.marker([geodata[0]['lat'], geodata[0]['long']]).addTo(map); // from image
						<?php
						break;
					case 'single-cluster':
					case 'cluster':
						?>
							var markers_cluster = new L.MarkerClusterGroup({
								maxClusterRadius: <?php echo $this->clusterradius; ?>,
								showCoverageOnHover: <?php echo $this->cluster_showcoverage_on_hover; ?>
							}); //radius > Option
							$.each(geodata, function (index, value) {
								var text = '';
						<?php if ($this->markerpopup) { ?>
							<?php if ($this->markerpopup_title) { ?>
										text = value.title;
							<?php } ?>
							<?php if ($this->markerpopup_thumb) { ?>
										text += value.thumb;
							<?php } ?>
							<?php if ($this->markerpopup_desc) { ?>
										text += value.desc;
							<?php } ?>
						<?php } ?>
								if (text === '') {
									markers_cluster.addLayer(L.marker([value.lat, value.long]));
								} else {
									markers_cluster.addLayer(L.marker([value.lat, value.long]).bindPopup(text));
								}
							});
							map.addLayer(markers_cluster);
						<?php
						break;
				}
			}
			?>
			</script>
			<?php
		}
	}

	/**
	 * It returns an array of layer option db name
	 * 
	 * @param array $providers provider list
	 * @return array
	 */
	static function getLayersList() {
		$providers = openStreetMap::getTileProviders();
		foreach ($providers as $provider) {
			// requested because option names may not contain '.'
			$provider_dbname = 'osmap_layer_' . str_replace(".", "_", $provider);
			$layers_list[$provider] = $provider_dbname;
		}
		return $layers_list;
	}

	/**
	 * It returns the provider chosen if it is valid or the default 'OpenStreetMap.Mapnik' tile
	 * 
	 * @param string $tileprovider The tile provider to validate
	 * @return string
	 */
	function setMapTiles($tileprovider = null) {
		if (in_array($tileprovider, $this->tileproviders)) {
			return $tileprovider;
		} else {
			return $this->tileproviders[0];
		}
	}

	/**
	 * Returns an array of all defined tile provider names from and for use with leaflet-providers.js and the plugin options
	 * 
	 * @return array
	 */
	static function getTileProviders() {
		return array(
				'OpenStreetMap.Mapnik',
				'OpenStreetMap.DE',
				'OpenStreetMap.France',
				'OpenStreetMap.HOT',
				'OpenTopoMap',
				'Thunderforest.OpenCycleMap',
				'Thunderforest.TransportDark',
				'Thunderforest.SpinalMap',
				'Thunderforest.Landscape',
				'Hydda.Full',
				// should be mapbox.streets,... but follow leaflet-providers behavior
				'MapBox.streets',
				'MapBox.light',
				'MapBox.dark',
				'MapBox.satellite',
				'MapBox.streets-satellite',
				'MapBox.wheatpaste',
				'MapBox.streets-basic',
				'MapBox.comic',
				'MapBox.outdoors',
				'MapBox.run-bike-hike',
				'MapBox.pencil',
				'MapBox.pirates',
				'MapBox.emerald',
				'MapBox.high-contrast',
				'Stamen.Watercolor',
				'Stamen.Terrain',
				'Stamen.TerrainBackground',
				'Stamen.TopOSMRelief',
				'Stamen.TopOSMFeatures',
				'Esri.WorldStreetMap',
				'Esri.DeLorme',
				'Esri.WorldTopoMap',
				'Esri.WorldImagery',
				'Esri.WorldTerrain',
				'Esri.WorldShadedRelief',
				'Esri.WorldPhysical',
				'Esri.OceanBasemap',
				'Esri.NatGeoWorldMap',
				'Esri.WorldGrayCanvas',
				'HERE.normalDay',
				'HERE.normalDayCustom',
				'HERE.normalDayGrey',
				'HERE.normalDayMobile',
				'HERE.normalDayGreyMobile',
				'HERE.normalDayTransit',
				'HERE.normalDayTransitMobile',
				'HERE.normalNight',
				'HERE.normalNightMobile',
				'HERE.normalNightGrey',
				'HERE.normalNightGreyMobile',
				'HERE.basicMap',
				'HERE.mapLabels',
				'HERE.trafficFlow',
				'HERE.carnavDayGrey',
				'HERE.hybridDay',
				'HERE.hybridDayMobile',
				'HERE.pedestrianDay',
				'HERE.pedestrianNight',
				'HERE.satelliteDay',
				'HERE.terrainDay',
				'HERE.terrainDayMobile',
				'FreeMapSK',
				'MtbMap',
				'CartoDB.Positron',
				'CartoDB.PositronNoLabels',
				'CartoDB.PositronOnlyLabels',
				'CartoDB.DarkMatter',
				'CartoDB.DarkMatterNoLabels',
				'CartoDB.DarkMatterOnlyLabels',
				'HikeBike.HikeBike',
				'HikeBike.HillShading',
				'BasemapAT.basemap',
				'BasemapAT.grau',
				'BasemapAT.highdpi',
				'BasemapAT.orthofoto',
				'NLS',
				'GeoportailFrance.ignMaps',
				'GeoportailFrance.orthos',
		);
	}

	/**
	 * Template function wrapper for the openStreetMap class to show a map with geodata markers 
	 * for the current image or collected the images of an album.
	 * 
	 * For more flexibility use the class directly.
	 * 
	 * The map is not shown if there is no geodata available.
	 * 
	 * @global obj $_zp_current_album
	 * @global obj $_zp_current_image
	 * @global string $_zp_gallery_page
	 * @param array $geodata Array of the geodata to create and display markers. See the constructor of the openStreetMap Class for the require structure
	 * @param string $width Width with unit, e.g. 100%, 100px, 100em
	 * @param string $height Height with unit, e.g. 100px, 100em
	 * @param array $mapcenter geodata array(lat,lng);
	 * @param int $zoom Number of the zoom 0 - 
	 * @param array $fitbounds geodata array('min' => array(lat,lng), 'max => array(lat,lng))
	 * @param string $class Class name to attach to the map element
	 * @param int $mapnumber If calling more than one map per page an unique number is required
	 * @param obj $obj Image or album object to skip current image or album and also $geodata
	 * @param bool $minimap True to show the minimap in the lower right corner
	 */
	static function printOpenStreetMap($geodata = NULL, $width = NULL, $height = NULL, $mapcenter = NULL, $zoom = NULL, $fitbounds = NULL, $class = '', $mapnumber = NULL, $obj = NULL, $minimap = false) {
		if (!empty($class)) {
			$class = ' class="' . $class . '"';
		}
		$map = new openStreetMap($geodata, $obj);
		if (!is_null($width)) {
			$map->width = $width;
		}
		if (!is_null($height)) {
			$map->height = $height;
		}
		if (!is_null($mapcenter)) {
			$map->center = $mapcenter;
		}
		if (!is_null($zoom)) {
			$map->zoom = $zoom;
		}
		if (!is_null($fitbounds)) {
			$map->fitbounds = $fitbounds;
		}
		if (!is_null($class)) {
			$map->class = $class;
		}
		if (!is_null($mapnumber)) {
			$map->mapnumber = $mapnumber;
		}
		if ($minimap) {
			$map->showminimap = true;
		}
		$map->printMap();
	}

}

// osm class end

/**
 * Template function wrapper for the openStreetMap class to show a map with geodata markers 
 * for the current image or collected the images of an album.
 * 
 * For more flexibility use the class directly.
 * 
 * The map is not shown if there is no geodata available.
 * 
 * @deprecated 2.0 – Use openStreetMap::printOpenStreetMap() instead
 * 
 * @global obj $_zp_current_album
 * @global obj $_zp_current_image
 * @global string $_zp_gallery_page
 * @param array $geodata Array of the geodata to create and display markers. See the constructor of the openStreetMap Class for the require structure
 * @param string $width Width with unit, e.g. 100%, 100px, 100em
 * @param string $height Height with unit, e.g. 100px, 100em
 * @param array $mapcenter geodata array(lat,lng);
 * @param int $zoom Number of the zoom 0 - 
 * @param array $fitbounds geodata array('min' => array(lat,lng), 'max => array(lat,lng))
 * @param string $class Class name to attach to the map element
 * @param int $mapnumber If calling more than one map per page an unique number is required
 * @param obj $obj Image or album object to skip current image or album and also $geodata
 * @param bool $minimap True to show the minimap in the lower right corner
 */
function printOpenStreetMap($geodata = NULL, $width = NULL, $height = NULL, $mapcenter = NULL, $zoom = NULL, $fitbounds = NULL, $class = '', $mapnumber = NULL, $obj = NULL, $minimap = false) {
	deprecationNotice(gettext('Use openStreetMap::printOpenStreetMap() instead'));
	openStreetMap::printOpenStreetMap($geodata, $width, $height, $mapcenter, $zoom , $fitbounds, $class, $mapnumber, $obj, $minimap);
}
