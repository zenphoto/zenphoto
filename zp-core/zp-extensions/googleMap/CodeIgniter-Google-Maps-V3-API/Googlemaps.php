<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter Google Maps API V3 Class
 *
 * Displays a Google Map
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		BIOSTALL (Steve Marks)
 * @link		http://biostall.com/codeigniter-google-maps-v3-api-library
 * @docs		http://biostall.com/wp-content/uploads/2010/07/Google_Maps_V3_API_Documentation.pdf
 */

class Googlemaps {

	var $adsense					= FALSE; 					// Whether Google Adsense For Content should be enabled
	var $adsenseChannelNumber		= ''; 						// The Adsense channel number for tracking the performance of this AdUnit
	var $adsenseFormat				= 'HALF_BANNER';			// The format of the AdUnit
	var $adsensePosition			= 'TOP_CENTER';				// The position of the AdUnit
	var $adsensePublisherID			= '';						// Your Google AdSense publisher ID
	var $apiKey						= ''; 						// If you've got an API key you can use it by passing this parameter. Setup an API key here: https://code.google.com/apis/console
	var $backgroundColor			= '';						// A hex color value shown as the map background when tiles have not yet loaded as the user pans
	var $bicyclingOverlay			= FALSE;					// If set to TRUE will overlay bicycling information (ie. bike paths and suggested routes) onto the map by default
	var $center						= "37.4419, -122.1419";		// Sets the default center location (lat/long co-ordinate or address) of the map. If defaulting to the users location set to "auto"
	var $cluster					= FALSE;					// Whether to cluster markers
	var $clusterGridSize			= 60;						// The grid size of a cluster in pixels
	var $clusterMaxZoom				= '';						// The maximum zoom level that a marker can be part of a cluster
	var $clusterZoomOnClick			= TRUE;						// Whether the default behaviour of clicking on a cluster is to zoom into it
	var $clusterAverageCenter		= FALSE;					// Whether the center of each cluster should be the average of all markers in the cluster
	var $clusterMinimumClusterSize	= 2;						// The minimum number of markers to be in a cluster before the markers are hidden and a count is shown
	var $disableDefaultUI			= FALSE;					// If set to TRUE will hide the default controls (ie. zoom, scale etc)
	var $disableDoubleClickZoom		= FALSE;					// If set to TRUE will disable zooming when a double click occurs
	var $disableMapTypeControl		= FALSE;					// If set to TRUE will hide the MapType control (ie. Map, Satellite, Hybrid, Terrain)
	var $disableNavigationControl	= FALSE;					// If set to TRUE will hide the Navigation control (ie. zoom in/out, pan)
	var $disableScaleControl		= FALSE;					// If set to TRUE will hide the Scale control
	var $disableStreetViewControl	= FALSE;					// If set to TRUE will hide the Street View control
	var $draggable					= TRUE;						// If set to FALSE will prevent the map from being dragged around
	var $draggableCursor			= '';						// The name or url of the cursor to display on a draggable object
	var $draggingCursor				= '';						// The name or url of the cursor to display when an object is being dragged
	var $geocodeCaching				= FALSE;					// If set to TRUE will cache any geocode requests made when an address is used instead of a lat/long. Requires DB table to be created (see documentation)
	var $https						= FALSE;					// If set to TRUE will load the Google Maps JavaScript API over HTTPS, allowing you to utilize the API within your HTTPS secure application
	var $navigationControlPosition	= '';						// The position of the Navigation control, eg. 'BOTTOM_RIGHT'
	var $keyboardShortcuts			= TRUE;						// If set to FALSE will disable to map being controlled via the keyboard
	var $jsfile						= '';						// Set this to the path of an external JS file if you wish the JavaScript to be placed in a file rather than output directly into the <head></head> section. The library will try to create the file if it does not exist already. Please ensure the destination file is writeable
	var $kmlLayerURL				= '';						// A URL to publicly available KML or GeoRSS data for displaying geographic information
	var $kmlLayerPreserveViewport	= FALSE;					// Specifies whether the map should be adjusted to the bounds of the KmlLayer's contents. By default the map is zoomed and positioned to show the entirety of the layer's contents
	var $language					= '';						// The map will by default load in the language of the browser. This can be overriden however here. For a full list of codes see https://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1
	var $loadAsynchronously			= FALSE;					// Load the map and API asynchronously once the page has loaded
	var $map_div_id					= "map_canvas";				// The ID of the <div></div> that is output which contains the map
	var $map_height					= "450px";					// The height of the map container. Any units (ie 'px') can be used. If no units are provided 'px' will be presumed
	var $map_name					= "map";					// The JS reference to the map. Currently not used but to be used in the future when multiple maps are supported
	var $map_type					= "ROADMAP";				// The default MapType. Values accepted are 'HYBRID', 'ROADMAP', 'SATELLITE' or 'TERRAIN'
	var $map_types_available		= array();					// The other MapTypes available for selection on the map
	var $map_width					= "100%";					// The width of the map container. Any units (ie 'px') can be used. If no units are provided 'px' will be presumed
	var $mapTypeControlPosition		= '';						// The position of the MapType control, eg. 'BOTTOM_RIGHT'
	var $mapTypeControlStyle		= '';						// The style of the MapType control. blank, 'DROPDOWN_MENU' or 'HORIZONTAL_BAR' values accepted.
	var $minzoom					= '';						// The minimum zoom level which will be displayed on the map
	var $maxzoom					= '';						// The maximum zoom level which will be displayed on the map
	var $minifyJS					= FALSE;					// If TRUE will run the JavaScript through Jsmin.php (this file and PHP5+ required) to minify the code
	var $noClear					= FALSE;					// If TRUE do not clear the contents of the map div
	var $onboundschanged			= '';						// The JavaScript action to perform when the viewport bounds have changed
	var $oncenterchanged			= '';						// The JavaScript action to perform when themap center property changes
	var $onclick					= '';						// The JavaScript action to perform when the map is clicked
	var $ondblclick					= '';						// The JavaScript action to perform when the map is double-clicked
	var $ondrag						= '';						// The JavaScript action to perform while the map is dragged
	var $ondragend					= '';						// The JavaScript action to perform when the user stops dragging the map
	var $ondragstart				= '';						// The JavaScript action to perform when the user starts dragging the map
	var $onidle						= '';						// The JavaScript action to perform when the map becomes idle after panning or zooming
	var $onload						= '';						// The JavaScript action to perform when the map first loads. This library hi-jacks the window.load event so add any bespoke code using this option
	var $onmousemove				= '';						// The JavaScript action to perform when the user's mouse moves over the map container
	var $onmouseout					= '';						// The JavaScript action to perform when the user's mouse exits the map container
	var $onmouseover				= '';						// The JavaScript action to perform when the user's mouse enters the map container
	var $onresize					= '';						// The JavaScript action to perform when the maps div changes size
	var $onrightclick				= '';						// The JavaScript action to perform when the map is right-clicked
	var $ontilesloaded				= '';						// The JavaScript action to perform when the visible tiles have finished loading
	var $onzoomchanged				= '';						// The JavaScript action to perform when the maps zoom property changes
	var	$panoramio					= FALSE;					// If TRUE will add photos from Panoramio as a layer to your maps as a series of large and small photo icons
	var	$panoramioTag				= '';						// Restrict the set of Panoramio photos shown to those matching a certain textual tag
	var	$panoramioUser				= '';						// Restrict the set of Panoramio photos shown to those matching a particular user
	var $region						= '';						// Country code top-level domain (eg "uk") within which to search. Useful if supplying addresses rather than lat/longs
	var $scaleControlPosition		= '';						// The position of the Scale control, eg. 'BOTTOM_RIGHT'
	var $scrollwheel				= TRUE;						// If set to FALSE will disable zooming by scrolling of the mouse wheel
	var $sensor						= FALSE;					// Set to TRUE if being used on a device that can detect a users location
	var $streetViewAddressControl	= TRUE;						// If set to FALSE will hide the Address control
	var $streetViewAddressPosition	= '';						// The position of the Address control, eg. 'BOTTOM'
	var $streetViewControlPosition	= '';						// The position of the Street View control when viewing normal aerial map, eg. 'BOTTOM_RIGHT'
	var $streetViewCloseButton		= FALSE;					// If set to TRUE will show the close button in the top right. The close button allows users to return to the aerial map
	var $streetViewLinksControl		= TRUE;						// If set to FALSE will hide the Links control
	var $streetViewPanControl		= TRUE;						// If set to FALSE will hide the Pan control
	var $streetViewPanPosition		= '';						// The position of the Scale control, eg. 'TOP_RIGHT'
	var $streetViewPovHeading		= 0;						// The Street View camera heading in degrees relative to true north. True north is 0, east is 90, south is 180, west is 270
	var $streetViewPovPitch			= 0;						// The Street View camera pitch in degrees, relative to the street view vehicle. Directly upwards is 90, Directly downwards is -90.
	var $streetViewPovZoom			= 0;						// The Street View zoom level. Fully zoomed-out is level 0, zooming in increases the zoom level.
	var $streetViewZoomControl		= TRUE;						// If set to FALSE will hide the Zoom control
	var $streetViewZoomPosition		= '';						// The position of the Scale control, eg. 'TOP_RIGHT'
	var $streetViewZoomStyle		= '';						// The size of the Street View zoom control. blank, 'SMALL' or 'LARGE' values accepted.
	var $styles						= array();					// An array of styles used to colour aspects of the map and turn points of interest on and off
	var $stylesAsMapTypes			= false;					// If applying styles, whether to apply them to the default map or add them as additional map types
	var $stylesAsMapTypesDefault	= '';						// If $stylesAsMapTypes is true the default style. Should contain the 'Name' of the style
	var	$tilt						= 0;						// The angle of tilt. Currently only supports the values 0 and 45 in SATELLITE and HYBRID map types and at certain zoom levels
	var	$trafficOverlay				= FALSE;					// If set to TRUE will overlay traffic information onto the map by default
	var	$version					= "3";						// Version of the API being used. Not currently used in the library
	var $zoom						= 13;						// The default zoom level of the map. If set to "auto" will autozoom/center to fit in all visible markers. If "auto", also overrides the $center parameter
	var $zoomControlPosition		= '';						// The position of the Zoom control, eg. 'BOTTOM_RIGHT'
	var $zoomControlStyle			= '';						// The size of the zoom control. blank, 'SMALL' or 'LARGE' values accepted.

	var	$markers					= array();					// An array used by the library to store the markers as they are produced
	var $markersInfo				= array();					// An array containing marker information (id, latitude, longitude etc) for use elsewhere
	var	$polylines					= array();					// An array used by the library to store the polylines as they are produced
	var	$polygons					= array();					// An array used by the library to store the polygons as they are produced
	var	$circles					= array();					// An array used by the library to store the circles as they are produced
	var	$rectangles					= array();					// An array used by the library to store the rectangles as they are produced
	var	$overlays					= array();					// An array used by the library to store the overlays as they are produced

	var $directions					= FALSE;					// Whether or not the map will be used to show directions
	var $directionsStart			= "";						// The starting location (lat/long co-ordinate or address) of the directions
	var $directionsEnd				= "";						// The destination point (lat/long co-ordinate or address) of the directions
	var $directionsDivID			= "";						// An element's ID on the page where textual directions will be output to. Leave blank if not required
	var $directionsMode				= "DRIVING"; 				// DRIVING, WALKING or BICYCLING (US Only) - The vehicle/mode of transport to show directions for
	var $directionsAvoidTolls		= FALSE;					// Whether or not directions should avoid tolls
	var $directionsAvoidHighways	= FALSE;					// Whether or not directions should avoid highways
	var $directionsDraggable		= FALSE;					// Whether or not directions on the map are draggable
	var $directionsChanged			= "";						// JavaScript to perform when directions are dragged
	var $directionsUnits			= "";						// 'metric' for kilometers and meters or 'imperial for miles and feet. Leave blank and it will default to the region or country of where directions are being obtained

	var $drawing					= FALSE;					// Whether or not the drawing library tools will be loaded
	var $drawingControl				= TRUE;						// If set to FALSE will hide the Drawing Manager control
	var $drawingControlPosition		= 'TOP_CENTER';				// The position of the Drawing Manager control, eg. 'TOP_RIGHT'
	var $drawingDefaultMode			= 'marker';					// The default mode for the Drawing Manager. Accepted values are marker, polygon, polyline, rectangle, circle, or null. null means that the user can interact with the map as normal when the map loads, and clicks do not draw anything.
	var $drawingModes				= array();					// An array of modes available for use. Accepted values are marker, polygon, polyline, rectangle, circle
	var $drawingOnComplete			= array();					// An array of JS to execute when shapes are completed, one array element per shape. For example: array('circle'=>'JS here', 'polygon'=>'JS here');
	var $drawingOnEdit				= array();					// An array of JS to execute when shapes are changed/resized, one array element per shape. For example: array('circle'=>'JS here', 'polygon'=>'JS here');

	var $places						= FALSE;					// Whether or not the map will be used to show places
	var $placesLocation				= '';						// A point (lat/long co-ordinate or address) on the map if the search for places is based around a central point
	var $placesRadius				= 0;						// The radius (in meters) if search is based around a central position
	var $placesLocationSW			= '';						// If preferring to search within bounds the South-West position (latitude/longitude coordinate OR address)
	var $placesLocationNE			= '';						// If preferring to search within bounds the North-East position (latitude/longitude coordinate OR address)
	var $placesTypes				= array();					// The types of places to search for. For a list of supported types see http://code.google.com/apis/maps/documentation/places/supported_types.html
	var $placesName					= '';						// A term to be matched against when searching for places to display on the map
	var $placesAutocompleteInputID	= '';						// The ID attribute of the textfield that the autocomplete should effect
	var $placesAutocompleteTypes	= array();					// The types of places for the autocomplete to return. Options can be seen here https://developers.google.com/maps/documentation/javascript/places#places_autocomplete but include 'establishment' to only return business results, '(cities)', or '(regions)'
	var $placesAutocompleteBoundSW	= '';						// By specifying an area in which to search for Places, the results are biased towards, but not restricted to, Places contained within these bounds.
	var $placesAutocompleteBoundNE	= '';						// Both South-West (lat/long co-ordinate or address) and North-East (lat/long co-ordinate or address) values are required if wishing to set bounds
	var $placesAutocompleteBoundsMap= FALSE;					// An alternative to setting the SW and NE bounds is to use the bounds of the current viewport. If set to TRUE, the bounds will be set to the viewport of the visible map, even if dragged or zoomed
	var $placesAutocompleteOnChange	= '';						// The JavaScript action to perform when a place is selected

	function Googlemaps($config = array())
	{
		if (count($config) > 0)
		{
			$this->initialize($config);
		}

		log_message('debug', "Google Maps Class Initialized");
	}

	function initialize($config = array())
	{
		foreach ($config as $key => $val)
		{
			if (isset($this->$key))
			{
				$this->$key = $val;
			}
		}

		if ($this->sensor) { $this->sensor = "true"; }else{ $this->sensor = "false"; }

	}

	function add_marker($params = array())
	{

		$marker = array();
		$this->markersInfo['marker_'.count($this->markers)] = array();

		$marker['position'] = '';								// The position (lat/long co-ordinate or address) at which the marker will appear
		$marker['infowindow_content'] = '';						// If not blank, creates an infowindow (aka bubble) with the content provided. Can be plain text or HTML
		$marker['id'] = '';										// The unique identifier of the marker suffix (ie. marker_yourID). If blank, this will default to marker_X where X is an incremental number
		$marker['clickable'] = TRUE;							// Defines if the marker is clickable
		$marker['cursor'] = '';									// The name or url of the cursor to display on hover
		$marker['draggable'] = FALSE;							// Defines if the marker is draggable
		$marker['flat'] = FALSE;								// If set to TRUE will not display a shadow beneath the icon
		$marker['icon'] = '';									// The name or url of the icon to use for the marker
		$marker['animation'] = ''; 								// blank, 'DROP' or 'BOUNCE'
		$marker['onclick'] = '';								// JavaScript performed when a marker is clicked
		$marker['ondblclick'] = '';								// JavaScript performed when a marker is double-clicked
		$marker['ondrag'] = '';									// JavaScript repeatedly performed while the marker is being dragged
		$marker['ondragstart'] = '';							// JavaScript performed when a marker is started to be dragged
		$marker['ondragend'] = '';								// JavaScript performed when a draggable marker is dropped
		$marker['onmousedown'] = '';							// JavaScript performed when a mousedown event occurs on a marker
		$marker['onmouseout'] = '';								// JavaScript performed when the mouse leaves the area of the marker icon
		$marker['onmouseover'] = '';							// JavaScript performed when the mouse enters the area of the marker icon
		$marker['onmouseup'] = '';								// JavaScript performed when a mouseup event occurs on a marker
		$marker['onpositionchanged'] = '';						// JavaScript performed when the markers position changes
		$marker['onrightclick'] = '';							// JavaScript performed when a right-click occurs on a marker
		$marker['raiseondrag'] = TRUE;							// If FALSE, disables the raising and lowering of the icon when a marker is being dragged
		$marker['shadow'] = '';									// The name or url of the icon's shadow
		$marker['title'] = '';									// The tooltip text to show on hover
		$marker['visible'] = TRUE;								// Defines if the marker is visible by default
		$marker['zIndex'] = '';									// The zIndex of the marker. If two markers overlap, the marker with the higher zIndex will appear on top

		$marker_output = '';

		foreach ($params as $key => $value) {

			if (isset($marker[$key])) {

				$marker[$key] = $value;

			}

		}

		$marker_id = count($this->markers);
		if (trim($marker['id']) != "")
		{
			$marker_id = $marker['id'];
		}

		if ($marker['position']!="") {
			if ($this->is_lat_long($marker['position'])) {
				$marker_output .= '
			var myLatlng = new google.maps.LatLng('.$marker['position'].');
			';
				$explodePosition = explode(",", $marker['position']);
				$this->markersInfo['marker_'.$marker_id]['latitude'] = trim($explodePosition[0]);
				$this->markersInfo['marker_'.$marker_id]['longitude'] = trim($explodePosition[1]);
			}else{
				$lat_long = $this->get_lat_long_from_address($marker['position']);
				$marker_output .= '
			var myLatlng = new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].');';
				$this->markersInfo['marker_'.$marker_id]['latitude'] = $lat_long[0];
				$this->markersInfo['marker_'.$marker_id]['longitude'] = $lat_long[1];
			}
		}

		$marker_output .= '
			var markerOptions = {
				map: '.$this->map_name;
		if ($marker['position']!="") {
			$marker_output .= ',
				position: myLatlng';
		}
		if (!$marker['clickable']) {
			$marker_output .= ',
				clickable: false';
		}
		if ($marker['cursor']!="") {
			$marker_output .= ',
				cursor: "'.$marker['cursor'].'"';
		}
		if ($marker['draggable']) {
			$marker_output .= ',
				draggable: true';
		}
		if ($marker['flat']) {
			$marker_output .= ',
				flat: true';
		}
		if ($marker['icon']!="") {
			$marker_output .= ',
				icon: "'.$marker['icon'].'"';
		}
		if (!$marker['raiseondrag']) {
			$marker_output .= ',
				raiseOnDrag: false';
		}
		if ($marker['shadow']!="") {
			$marker_output .= ',
				shadow: "'.$marker['shadow'].'"';
		}
		if ($marker['title']!="") {
			$marker_output .= ',
				title: "'.$marker['title'].'"';
			$this->markersInfo['marker_'.$marker_id]['title'] = $marker['title'];
		}
		if (!$marker['visible']) {
			$marker_output .= ',
				visible: false';
		}
		if ($marker['zIndex']!="" && is_numeric($marker['zIndex'])) {
			$marker_output .= ',
				zIndex: '.$marker['zIndex'];
		}
		if ($marker['animation']!="" && (strtoupper($marker['animation'])=="DROP" || strtoupper($marker['animation']=="BOUNCE"))) {
			$marker_output .= ',
				animation:  google.maps.Animation.'.strtoupper($marker['animation']);
		}
		$marker_output .= '
			};
			marker_'.$marker_id.' = createMarker(markerOptions);
			';

		if ($marker['infowindow_content']!="") {

			// Escape any quotes in the event that HTML is being added to the infowindow
			$marker['infowindow_content'] = str_replace('\"', '"', $marker['infowindow_content']);
			$marker['infowindow_content'] = str_replace('"', '\"', $marker['infowindow_content']);
			$marker_output .= '
			marker_'.$marker_id.'.set("content", "'.$marker['infowindow_content'].'");

			google.maps.event.addListener(marker_'.$marker_id.', "click", function(event) {
				iw.setContent(this.get("content"));
				iw.open('.$this->map_name.', this);
			';
			if ($marker['onclick']!="") { $marker_output .= $marker['onclick'].'
			'; }
			$marker_output .= '
			});
			';
			// hack zenphoto
			$marker_output .= '
			oms.addMarker(marker_'.$marker_id.');
			';
			// end hack zenphoto
		}else{
			if ($marker['onclick']!="") {
				$marker_output .= '
				google.maps.event.addListener(marker_'.$marker_id.', "click", function(event) {
					'.$marker['onclick'].'
				});
				';
			}
		}

		if ($marker['ondblclick']!="") {
			$marker_output .= '
			google.maps.event.addListener(marker_'.$marker_id.', "dblclick", function(event) {
				'.$marker['ondblclick'].'
			});
			';
		}
		if ($marker['onmousedown']!="") {
			$marker_output .= '
			google.maps.event.addListener(marker_'.$marker_id.', "mousedown", function(event) {
				'.$marker['onmousedown'].'
			});
			';
		}
		if ($marker['onmouseout']!="") {
			$marker_output .= '
			google.maps.event.addListener(marker_'.$marker_id.', "mouseout", function(event) {
				'.$marker['onmouseout'].'
			});
			';
		}
		if ($marker['onmouseover']!="") {
			$marker_output .= '
			google.maps.event.addListener(marker_'.$marker_id.', "mouseover", function(event) {
				'.$marker['onmouseover'].'
			});
			';
		}
		if ($marker['onmouseup']!="") {
			$marker_output .= '
			google.maps.event.addListener(marker_'.$marker_id.', "mouseup", function(event) {
				'.$marker['onmouseup'].'
			});
			';
		}
		if ($marker['onpositionchanged']!="") {
			$marker_output .= '
			google.maps.event.addListener(marker_'.$marker_id.', "position_changed", function(event) {
				'.$marker['onpositionchanged'].'
			});
			';
		}
		if ($marker['onrightclick']!="") {
			$marker_output .= '
			google.maps.event.addListener(marker_'.$marker_id.', "rightclick", function(event) {
				'.$marker['onrightclick'].'
			});
			';
		}

		if ($marker['draggable']) {
			if ($marker['ondrag']!="") {
				$marker_output .= '
				google.maps.event.addListener(marker_'.$marker_id.', "drag", function(event) {
					'.$marker['ondrag'].'
				});
				';
			}
			if ($marker['ondragend']!="") {
				$marker_output .= '
				google.maps.event.addListener(marker_'.$marker_id.', "dragend", function(event) {
					'.$marker['ondragend'].'
				});
				';
			}
			if ($marker['ondragstart']!="") {
				$marker_output .= '
				google.maps.event.addListener(marker_'.$marker_id.', "dragstart", function(event) {
					'.$marker['ondragstart'].'
				});
				';
			}
		}

		array_push($this->markers, $marker_output);

	}

	function add_polyline($params = array())
	{

		$polyline = array();

		$polyline['points'] = array();							// An array of latitude/longitude coordinates OR addresses, or a mixture of both. If an address is supplied the Google geocoding service will be used to return a lat/long.
		$polyline['clickable'] = TRUE;							// Defines if the polyline is clickable
		$polyline['strokeColor'] = '#FF0000';					// The hex value of the polylines color
		$polyline['strokeOpacity'] = '1.0';						// The opacity of the polyline. 0 to 1.0
		$polyline['strokeWeight'] = '2';						// The thickness of the polyline
		$polyline['onclick'] = '';								// JavaScript performed when a polyline is clicked
		$polyline['ondblclick'] = '';							// JavaScript performed when a polyline is double-clicked
		$polyline['onmousedown'] = '';							// JavaScript performed when a mousedown event occurs on a polyline
		$polyline['onmousemove'] = '';							// JavaScript performed when the mouse moves in the area of the polyline
		$polyline['onmouseout'] = '';							// JavaScript performed when the mouse leaves the area of the polyline
		$polyline['onmouseover'] = '';							// JavaScript performed when the mouse enters the area of the polyline
		$polyline['onmouseup'] = '';							// JavaScript performed when a mouseup event occurs on a polyline
		$polyline['onrightclick'] = '';							// JavaScript performed when a right-click occurs on a polyline
		$polyline['zIndex'] = '';								// The zIndex of the polyline. If two polylines overlap, the polyline with the higher zIndex will appear on top

		$polyline_output = '';

		foreach ($params as $key => $value) {

			if (isset($polyline[$key])) {

				$polyline[$key] = $value;

			}

		}

		if (count($polyline['points'])) {

			$polyline_output .= '
				var polyline_plan_'.count($this->polylines).' = [';
			$i=0;
			$lat_long_output = '';
			foreach ($polyline['points'] as $point) {
				if ($i>0) { $polyline_output .= ','; }
				$lat_long_to_push = '';
				if ($this->is_lat_long($point)) {
					$lat_long_to_push = $point;
					$polyline_output .= '
					new google.maps.LatLng('.$point.')
					';
				}else{
					$lat_long = $this->get_lat_long_from_address($point);
					$polyline_output .= '
					new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].')';
					$lat_long_to_push = $lat_long[0].', '.$lat_long[1];
				}
				$lat_long_output .= '
					lat_longs.push(new google.maps.LatLng('.$lat_long_to_push.'));
				';
				$i++;
			}
			$polyline_output .= '];';

			$polyline_output .= $lat_long_output;

			$polyline_output .= '
				var polyline_'.count($this->polylines).' = new google.maps.Polyline({
						path: polyline_plan_'.count($this->polylines).',
						strokeColor: "'.$polyline['strokeColor'].'",
						strokeOpacity: '.$polyline['strokeOpacity'].',
						strokeWeight: '.$polyline['strokeWeight'];
			if (!$polyline['clickable']) {
				$polyline_output .= ',
					clickable: false';
			}
			if ($polyline['zIndex']!="" && is_numeric($polyline['zIndex'])) {
				$polyline_output .= ',
					zIndex: '.$polyline['zIndex'];
			}
			$polyline_output .= '
				});

				polyline_'.count($this->polylines).'.setMap('.$this->map_name.');

			';

			if ($polyline['onclick']!="") {
				$polyline_output .= '
				google.maps.event.addListener(polyline_'.count($this->polylines).', "click", function() {
					'.$polyline['onclick'].'
				});
				';
			}
			if ($polyline['ondblclick']!="") {
				$polyline_output .= '
				google.maps.event.addListener(polyline_'.count($this->polylines).', "dblclick", function() {
					'.$polyline['ondblclick'].'
				});
				';
			}
			if ($polyline['onmousedown']!="") {
				$polyline_output .= '
				google.maps.event.addListener(polyline_'.count($this->polylines).', "mousedown", function() {
					'.$polyline['onmousedown'].'
				});
				';
			}
			if ($polyline['onmousemove']!="") {
				$polyline_output .= '
				google.maps.event.addListener(polyline_'.count($this->polylines).', "mousemove", function() {
					'.$polyline['onmousemove'].'
				});
				';
			}
			if ($polyline['onmouseout']!="") {
				$polyline_output .= '
				google.maps.event.addListener(polyline_'.count($this->polylines).', "mouseout", function() {
					'.$polyline['onmouseout'].'
				});
				';
			}
			if ($polyline['onmouseover']!="") {
				$polyline_output .= '
				google.maps.event.addListener(polyline_'.count($this->polylines).', "mouseover", function() {
					'.$polyline['onmouseover'].'
				});
				';
			}
			if ($polyline['onmouseup']!="") {
				$polyline_output .= '
				google.maps.event.addListener(polyline_'.count($this->polylines).', "mouseup", function() {
					'.$polyline['onmouseup'].'
				});
				';
			}
			if ($polyline['onrightclick']!="") {
				$polyline_output .= '
				google.maps.event.addListener(polyline_'.count($this->polylines).', "rightclick", function() {
					'.$polyline['onrightclick'].'
				});
				';
			}

			array_push($this->polylines, $polyline_output);

		}

	}

	function add_polygon($params = array())
	{

		$polygon = array();

		$polygon['points'] = array();							// The positions (latitude/longitude coordinates OR addresses) at which the polygon points will appear. NOTE: The first and last elements of the array must be the same
		$polygon['clickable'] = TRUE;							// Defines if the polygon is clickable
		$polygon['strokeColor'] = '#FF0000';					// The hex value of the polygons border color
		$polygon['strokeOpacity'] = '0.8';						// The opacity of the polygon border. 0 to 1.0
		$polygon['strokeWeight'] = '2';							// The thickness of the polygon border
		$polygon['fillColor'] = '#FF0000';						// The hex value of the polygons fill color
		$polygon['fillOpacity'] = '0.3';						// The opacity of the polygons fill
		$polygon['onclick'] = '';								// JavaScript performed when a polygon is clicked
		$polygon['ondblclick'] = '';							// JavaScript performed when a polygon is double-clicked
		$polygon['onmousedown'] = '';							// JavaScript performed when a mousedown event occurs on a polygon
		$polygon['onmousemove'] = '';							// JavaScript performed when the mouse moves in the area of the polygon
		$polygon['onmouseout'] = '';							// JavaScript performed when the mouse leaves the area of the polygon
		$polygon['onmouseover'] = '';							// JavaScript performed when the mouse enters the area of the polygon
		$polygon['onmouseup'] = '';								// JavaScript performed when a mouseup event occurs on a polygon
		$polygon['onrightclick'] = '';							// JavaScript performed when a right-click occurs on a polygon
		$polygon['zIndex'] = '';								// The zIndex of the polygon. If two polygons overlap, the polygon with the higher zIndex will appear on top

		$polygon_output = '';

		foreach ($params as $key => $value) {

			if (isset($polygon[$key])) {

				$polygon[$key] = $value;

			}

		}

		if (count($polygon['points'])) {

			$polygon_output .= '
				var polygon_plan_'.count($this->polygons).' = [';
			$i=0;
			$lat_long_output = '';
			foreach ($polygon['points'] as $point) {
				if ($i>0) { $polygon_output .= ','; }
				$lat_long_to_push = '';
				if ($this->is_lat_long($point)) {
					$lat_long_to_push = $point;
					$polygon_output .= '
					new google.maps.LatLng('.$point.')
					';
				}else{
					$lat_long = $this->get_lat_long_from_address($point);
					$polygon_output .= '
					new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].')';
					$lat_long_to_push = $lat_long[0].', '.$lat_long[1];
				}
				$lat_long_output .= '
					lat_longs.push(new google.maps.LatLng('.$lat_long_to_push.'));
				';
				$i++;
			}
			$polygon_output .= '];';

			$polygon_output .= $lat_long_output;

		}

		$polygon_output .= '
			var polygon_'.count($this->polygons).' = new google.maps.Polygon({
					';
		if (count($polygon['points'])) {
			$polygon_output .= 'path: polygon_plan_'.count($this->polygons).',
					';
		}
		$polygon_output .= '
					strokeColor: "'.$polygon['strokeColor'].'",
					strokeOpacity: '.$polygon['strokeOpacity'].',
					strokeWeight: '.$polygon['strokeWeight'].',
				fillColor: "'.$polygon['fillColor'].'",
				fillOpacity: '.$polygon['fillOpacity'];
		if (!$polygon['clickable']) {
			$polygon_output .= ',
				clickable: false';
		}
		if ($polygon['zIndex']!="" && is_numeric($polygon['zIndex'])) {
			$polygon_output .= ',
				zIndex: '.$polygon['zIndex'];
		}
		$polygon_output .= '
			});

			polygon_'.count($this->polygons).'.setMap('.$this->map_name.');

		';

		if ($polygon['onclick']!="") {
			$polygon_output .= '
			google.maps.event.addListener(polygon_'.count($this->polygons).', "click", function() {
				'.$polygon['onclick'].'
			});
			';
		}
		if ($polygon['ondblclick']!="") {
			$polygon_output .= '
			google.maps.event.addListener(polygon_'.count($this->polygons).', "dblclick", function() {
				'.$polygon['ondblclick'].'
			});
			';
		}
		if ($polygon['onmousedown']!="") {
			$polygon_output .= '
			google.maps.event.addListener(polygon_'.count($this->polygons).', "mousedown", function() {
				'.$polygon['onmousedown'].'
			});
			';
		}
		if ($polygon['onmousemove']!="") {
			$polygon_output .= '
			google.maps.event.addListener(polygon_'.count($this->polygons).', "mousemove", function() {
				'.$polygon['onmousemove'].'
			});
			';
		}
		if ($polygon['onmouseout']!="") {
			$polygon_output .= '
			google.maps.event.addListener(polygon_'.count($this->polygons).', "mouseout", function() {
				'.$polygon['onmouseout'].'
			});
			';
		}
		if ($polygon['onmouseover']!="") {
			$polygon_output .= '
			google.maps.event.addListener(polygon_'.count($this->polygons).', "mouseover", function() {
				'.$polygon['onmouseover'].'
			});
			';
		}
		if ($polygon['onmouseup']!="") {
			$polygon_output .= '
			google.maps.event.addListener(polygon_'.count($this->polygons).', "mouseup", function() {
				'.$polygon['onmouseup'].'
			});
			';
		}
		if ($polygon['onrightclick']!="") {
			$polygon_output .= '
			google.maps.event.addListener(polygon_'.count($this->polygons).', "rightclick", function() {
				'.$polygon['onrightclick'].'
			});
			';
		}

		array_push($this->polygons, $polygon_output);

	}

	function add_circle($params = array())
	{

		$circle = array();

		$circle['center'] = '';									// The center position (latitude/longitude coordinate OR addresse) at which the circle will appear
		$circle['clickable'] = TRUE;							// Defines if the circle is clickable
		$circle['radius'] = 0;									// The circle radius (in metres)
		$circle['strokeColor'] = '0.8';							// The hex value of the circles border color
		$circle['strokeOpacity'] = '0.8';						// The opacity of the circle border
		$circle['strokeWeight'] = '2';							// The thickness of the circle border
		$circle['fillColor'] = '#FF0000';						// The hex value of the circles fill color
		$circle['fillOpacity'] = '0.3';							// The opacity of the circles fill
		$circle['onclick'] = '';								// JavaScript performed when a circle is clicked
		$circle['ondblclick'] = '';								// JavaScript performed when a circle is double-clicked
		$circle['onmousedown'] = '';							// JavaScript performed when a mousedown event occurs on a circle
		$circle['onmousemove'] = '';							// JavaScript performed when the mouse moves in the area of the circle
		$circle['onmouseout'] = '';								// JavaScript performed when the mouse leaves the area of the circle
		$circle['onmouseover'] = '';							// JavaScript performed when the mouse enters the area of the circle
		$circle['onmouseup'] = '';								// JavaScript performed when a mouseup event occurs on a circle
		$circle['onrightclick'] = '';							// JavaScript performed when a right-click occurs on a circle
		$circle['zIndex'] = '';									// The zIndex of the circle. If two circles overlap, the circle with the higher zIndex will appear on top

		$circle_output = '';

		foreach ($params as $key => $value) {

			if (isset($circle[$key])) {

				$circle[$key] = $value;

			}

		}

		if ($circle['radius']>0 && $circle['center']!="") {

			$lat_long_to_push = '';
			if ($this->is_lat_long($circle['center'])) {
				$lat_long_to_push = $circle['center'];
				$circle_output = '
				var circleCenter = new google.maps.LatLng('.$circle['center'].')
				';
			}else{
				$lat_long = $this->get_lat_long_from_address($circle['center']);
				$circle_output = '
				var circleCenter = new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].')';
				$lat_long_to_push = $lat_long[0].', '.$lat_long[1];
			}
			$circle_output .= '
				lat_longs.push(new google.maps.LatLng('.$lat_long_to_push.'));
			';

			$circle_output .= '
				var circleOptions = {
					strokeColor: "'.$circle['strokeColor'].'",
					strokeOpacity: '.$circle['strokeOpacity'].',
					strokeWeight: '.$circle['strokeWeight'].',
					fillColor: "'.$circle['fillColor'].'",
					fillOpacity: '.$circle['fillOpacity'].',
					map: '.$this->map_name.',
					center: circleCenter,
					radius: '.$circle['radius'];
			if (!$circle['clickable']) {
				$circle_output .= ',
					clickable: false';
			}
			if ($circle['zIndex']!="" && is_numeric($circle['zIndex'])) {
				$circle_output .= ',
					zIndex: '.$circle['zIndex'];
			}
			$circle_output .= '
				};
				var circle_'.count($this->circles).' = new google.maps.Circle(circleOptions);
			';

			if ($circle['onclick']!="") {
				$circle_output .= '
				google.maps.event.addListener(circle_'.count($this->circles).', "click", function() {
					'.$circle['onclick'].'
				});
				';
			}
			if ($circle['ondblclick']!="") {
				$circle_output .= '
				google.maps.event.addListener(circle_'.count($this->circles).', "dblclick", function() {
					'.$circle['ondblclick'].'
				});
				';
			}
			if ($circle['onmousedown']!="") {
				$circle_output .= '
				google.maps.event.addListener(circle_'.count($this->circles).', "mousedown", function() {
					'.$circle['onmousedown'].'
				});
				';
			}
			if ($circle['onmousemove']!="") {
				$circle_output .= '
				google.maps.event.addListener(circle_'.count($this->circles).', "mousemove", function() {
					'.$circle['onmousemove'].'
				});
				';
			}
			if ($circle['onmouseout']!="") {
				$circle_output .= '
				google.maps.event.addListener(circle_'.count($this->circles).', "mouseout", function() {
					'.$circle['onmouseout'].'
				});
				';
			}
			if ($circle['onmouseover']!="") {
				$circle_output .= '
				google.maps.event.addListener(circle_'.count($this->circles).', "mouseover", function() {
					'.$circle['onmouseover'].'
				});
				';
			}
			if ($circle['onmouseup']!="") {
				$circle_output .= '
				google.maps.event.addListener(circle_'.count($this->circles).', "mouseup", function() {
					'.$circle['onmouseup'].'
				});
				';
			}
			if ($circle['onrightclick']!="") {
				$circle_output .= '
				google.maps.event.addListener(circle_'.count($this->circles).', "rightclick", function() {
					'.$circle['onrightclick'].'
				});
				';
			}

			array_push($this->circles, $circle_output);

		}

	}

	function add_rectangle($params = array())
	{

		$rectangle = array();

		$rectangle['positionSW'] = '';							// The South-West position (latitude/longitude coordinate OR address) at which the rectangle will appear
		$rectangle['positionNE'] = '';							// The North-East position(latitude/longitude coordinate OR address) at which the rectangle will appear
		$rectangle['clickable'] = TRUE;							// Defines if the rectangle is clickable
		$rectangle['strokeColor'] = '0.8';						// The hex value of the rectangles border color
		$rectangle['strokeOpacity'] = '0.8';					// The opacity of the rectangle border
		$rectangle['strokeWeight'] = '2';						// The thickness of the rectangle border
		$rectangle['fillColor'] = '#FF0000';					// The hex value of the rectangles fill color
		$rectangle['fillOpacity'] = '0.3';						// The opacity of the rectangles fill
		$rectangle['onclick'] = '';								// JavaScript performed when a rectangle is clicked
		$rectangle['ondblclick'] = '';							// JavaScript performed when a rectangle is double-clicked
		$rectangle['onmousedown'] = '';							// JavaScript performed when a mousedown event occurs on a rectangle
		$rectangle['onmousemove'] = '';							// JavaScript performed when the mouse moves in the area of the rectangle
		$rectangle['onmouseout'] = '';							// JavaScript performed when the mouse leaves the area of the rectangle
		$rectangle['onmouseover'] = '';							// JavaScript performed when the mouse enters the area of the rectangle
		$rectangle['onmouseup'] = '';							// JavaScript performed when a mouseup event occurs on a rectangle
		$rectangle['onrightclick'] = '';						// JavaScript performed when a right-click occurs on a rectangle
		$rectangle['zIndex'] = '';								// The zIndex of the rectangle. If two rectangles overlap, the rectangle with the higher zIndex will appear on top

		$rectangle_output = '';

		foreach ($params as $key => $value) {

			if (isset($rectangle[$key])) {

				$rectangle[$key] = $value;

			}

		}

		if ($rectangle['positionSW']!="" && $rectangle['positionNE']!="") {

			$lat_long_to_push = '';
			if ($this->is_lat_long($rectangle['positionSW'])) {
				$lat_long_to_push = $rectangle['positionSW'];
				$rectangle_output .= '
				var positionSW = new google.maps.LatLng('.$rectangle['positionSW'].')
				';
			}else{
				$lat_long = $this->get_lat_long_from_address($rectangle['positionSW']);
				$rectangle_output .= '
				var positionSW = new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].')';
				$lat_long_to_push = $lat_long[0].', '.$lat_long[1];
			}
			$rectangle_output .= '
				lat_longs.push(new google.maps.LatLng('.$lat_long_to_push.'));
			';

			$lat_long_to_push = '';
			if ($this->is_lat_long($rectangle['positionNE'])) {
				$lat_long_to_push = $rectangle['positionNE'];
				$rectangle_output .= '
				var positionNE = new google.maps.LatLng('.$rectangle['positionNE'].')
				';
			}else{
				$lat_long = $this->get_lat_long_from_address($rectangle['positionNE']);
				$rectangle_output .= '
				var positionNE = new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].')';
				$lat_long_to_push = $lat_long[0].', '.$lat_long[1];
			}
			$rectangle_output .= '
				lat_longs.push(new google.maps.LatLng('.$lat_long_to_push.'));
			';

			$rectangle_output .= '
				var rectangleOptions = {
					strokeColor: "'.$rectangle['strokeColor'].'",
					strokeOpacity: '.$rectangle['strokeOpacity'].',
					strokeWeight: '.$rectangle['strokeWeight'].',
					fillColor: "'.$rectangle['fillColor'].'",
					fillOpacity: '.$rectangle['fillOpacity'].',
					map: '.$this->map_name.',
					bounds: new google.maps.LatLngBounds(positionSW, positionNE)';
			if (!$rectangle['clickable']) {
				$rectangle_output .= ',
					clickable: false';
			}
			if ($rectangle['zIndex']!="" && is_numeric($rectangle['zIndex'])) {
				$rectangle_output .= ',
					zIndex: '.$rectangle['zIndex'];
			}
			$rectangle_output .= '
				};';

			$rectangle_output .= '
				var rectangle_'.count($this->rectangles).' = new google.maps.Rectangle(rectangleOptions);
			';

			if ($rectangle['onclick']!="") {
				$rectangle_output .= '
				google.maps.event.addListener(rectangle_'.count($this->rectangles).', "click", function() {
					'.$rectangle['onclick'].'
				});
				';
			}
			if ($rectangle['ondblclick']!="") {
				$rectangle_output .= '
				google.maps.event.addListener(rectangle_'.count($this->rectangles).', "dblclick", function() {
					'.$rectangle['ondblclick'].'
				});
				';
			}
			if ($rectangle['onmousedown']!="") {
				$rectangle_output .= '
				google.maps.event.addListener(rectangle_'.count($this->rectangles).', "mousedown", function() {
					'.$rectangle['onmousedown'].'
				});
				';
			}
			if ($rectangle['onmousemove']!="") {
				$rectangle_output .= '
				google.maps.event.addListener(rectangle_'.count($this->rectangles).', "mousemove", function() {
					'.$rectangle['onmousemove'].'
				});
				';
			}
			if ($rectangle['onmouseout']!="") {
				$rectangle_output .= '
				google.maps.event.addListener(rectangle_'.count($this->rectangles).', "mouseout", function() {
					'.$rectangle['onmouseout'].'
				});
				';
			}
			if ($rectangle['onmouseover']!="") {
				$rectangle_output .= '
				google.maps.event.addListener(rectangle_'.count($this->rectangles).', "mouseover", function() {
					'.$rectangle['onmouseover'].'
				});
				';
			}
			if ($rectangle['onmouseup']!="") {
				$rectangle_output .= '
				google.maps.event.addListener(rectangle_'.count($this->rectangles).', "mouseup", function() {
					'.$rectangle['onmouseup'].'
				});
				';
			}
			if ($rectangle['onrightclick']!="") {
				$rectangle_output .= '
				google.maps.event.addListener(rectangle_'.count($this->rectangles).', "rightclick", function() {
					'.$rectangle['onrightclick'].'
				});
				';
			}

			array_push($this->rectangles, $rectangle_output);

		}

	}

	function add_ground_overlay($params = array())
	{

		$overlay = array();

		$overlay['image'] = '';									// JavaScript performed when a ground overlay is clicked
		$overlay['positionSW'] = '';							// The South-West position (latitude/longitude coordinate OR addresse) at which the ground overlay will appear
		$overlay['positionNE'] = '';							// The North-East position (latitude/longitude coordinate OR addresse) at which the ground overlay will appear
		$overlay['clickable'] = TRUE;							// Defines if the ground overlay is clickable
		$overlay['onclick'] = '';								// JavaScript performed when a ground overlay is clicked

		$overlay_output = '';

		foreach ($params as $key => $value) {

			if (isset($overlay[$key])) {

				$overlay[$key] = $value;

			}

		}

		if ($overlay['image']!="" && $overlay['positionSW']!="" && $overlay['positionNE']!="") {

			$lat_long_to_push = '';
			if ($this->is_lat_long($overlay['positionSW'])) {
				$lat_long_to_push = $overlay['positionSW'];
				$overlay_output .= '
				var positionSW = new google.maps.LatLng('.$overlay['positionSW'].')
				';
			}else{
				$lat_long = $this->get_lat_long_from_address($overlay['positionSW']);
				$overlay_output .= '
				var positionSW = new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].')';
				$lat_long_to_push = $lat_long[0].', '.$lat_long[1];
			}
			$overlay_output .= '
				lat_longs.push(new google.maps.LatLng('.$lat_long_to_push.'));
			';

			$lat_long_to_push = '';
			if ($this->is_lat_long($overlay['positionNE'])) {
				$lat_long_to_push = $overlay['positionNE'];
				$overlay_output .= '
				var positionNE = new google.maps.LatLng('.$overlay['positionNE'].')
				';
			}else{
				$lat_long = $this->get_lat_long_from_address($overlay['positionNE']);
				$overlay_output .= '
				var positionNE = new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].')';
				$lat_long_to_push = $lat_long[0].', '.$lat_long[1];
			}
			$overlay_output .= '
				lat_longs.push(new google.maps.LatLng('.$lat_long_to_push.'));
			';

			$overlay_output .= '
				var overlay_'.count($this->overlays).' = new google.maps.GroundOverlay("'.$overlay['image'].'", new google.maps.LatLngBounds(positionSW, positionNE), { map: '.$this->map_name;
			if (!$overlay['clickable']) { $overlay_output .= ', clickable: false'; }
			$overlay_output .= '});
			';

			if ($overlay['onclick']!="") {
				$overlay_output .= '
				google.maps.event.addListener(overlay_'.count($this->overlays).', "click", function() {
					'.$overlay['onclick'].'
				});
				';
			}

			array_push($this->overlays, $overlay_output);

		}

	}

	function create_map()
	{

		$this->output_js = '';
		$this->output_js_contents = '';
		$this->output_html = '';

		if ($this->apiKey!="")
		{
			if ($this->https) { $apiLocation = 'https'; }else{ $apiLocation = 'http'; }
			$apiLocation .= '://maps.googleapis.com/maps/api/js?key='.$this->apiKey.'&';
		}
		else
		{
		if ($this->https) { $apiLocation = 'https://maps-api-ssl'; }else{ $apiLocation = 'http://maps'; }
			$apiLocation .= '.google.com/maps/api/js?';
		}
		$apiLocation .= 'sensor='.$this->sensor;
		if ($this->region!="" && strlen($this->region)==2) { $apiLocation .= '&region='.strtoupper($this->region); }
		if ($this->language!="") { $apiLocation .= '&language='.$this->language; }
		$libraries = array();
		if ($this->adsense!="") { array_push($libraries, 'adsense'); }
		if ($this->places!="") { array_push($libraries, 'places'); }
		if ($this->panoramio) { array_push($libraries, 'panoramio'); }
		if ($this->drawing) { array_push($libraries, 'drawing'); }
		if (count($libraries)) { $apiLocation .= '&libraries='.implode(",", $libraries); }
		$this->output_js .= '
		<script type="text/javascript" src="'.$apiLocation.'"></script>';
		if ($this->center=="auto" || $this->directionsStart=="auto") { $this->output_js .= '
		<script type="text/javascript" src="http://code.google.com/apis/gears/gears_init.js"></script>
		'; }
		if ($this->cluster) { $this->output_js .= '
		<script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer_compiled.js"></script>
		'; }
		if ($this->jsfile=="") {
			$this->output_js .= '
			<script type="text/javascript">
			//<![CDATA[
			';
		}

		$this->output_js_contents .= '
			var '.$this->map_name.'; // Global declaration of the map

			// hack zenphoto
			//var iw = new google.maps.InfoWindow(); // Global declaration of the infowindow
			var iw = new google.maps.InfoWindow({maxWidth:250}); // Global declaration of the infowindow
			var lat_longs = new Array();
			var markers = new Array();
			';
		if ($this->cluster) {
			$this->output_js_contents .= 'var markerCluster;
			';
		}
		if ($this->directions) {
			$rendererOptions = '';
			if ($this->directionsDraggable) {
				$this->output_js_contents .= '
			var rendererOptions = { draggable: true };
			';
				$rendererOptions = 'rendererOptions';
			}
			$this->output_js_contents .= 'var directionsDisplay = new google.maps.DirectionsRenderer('.$rendererOptions.');
			var directionsService = new google.maps.DirectionsService();
			';
		}
		if ($this->places) {
			$this->output_js_contents .= 'var placesService;
			';
			if ($this->placesAutocompleteInputID != "")
			{
				$this->output_js_contents .= 'var placesAutocomplete;
			';
			}
		}
		if ($this->adsense) {
			$this->output_js_contents .= 'var adUnit;
			';
		}
		if ($this->drawing) {
			$this->output_js_contents .= 'var drawingManager;
			';
		}

		$this->output_js_contents .= 'function initialize() {

				 ';

		$styleOutput = '';
		if (count($this->styles)) {
			$styles = 0;
			foreach ($this->styles as $style) {
				$this->output_js_contents .= 'var styles_'.$styles.' = '.json_encode($style['definition']).';
				';

				if ($this->stylesAsMapTypes) {
					$this->output_js_contents .= 'var styles_'.$styles.' = new google.maps.StyledMapType(styles_'.$styles.', {name:"'.$style['name'].'"});
				';
				}else{
					$styleOutput .= $this->map_name.'.setOptions({styles: styles_'.$styles.'});
				';
					break;
				}

				$styles++;
			}
		}

		if ($this->center!="auto") {
			if ($this->is_lat_long($this->center)) { // if centering the map on a lat/long
				$this->output_js_contents .= 'var myLatlng = new google.maps.LatLng('.$this->center.');';
			}else{  // if centering the map on an address
				$lat_long = $this->get_lat_long_from_address($this->center);
				$this->output_js_contents .= 'var myLatlng = new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].');';
			}
		}

		$this->output_js_contents .= '
				var myOptions = {
						';
		if ($this->zoom=="auto") { $this->output_js_contents .= 'zoom: 13,'; }else{ $this->output_js_contents .= 'zoom: '.$this->zoom.','; }
		if ($this->center!="auto") { $this->output_js_contents .= '
					center: myLatlng,'; }
		if (strtolower($this->map_type)=="street") { $map_type = "ROADMAP"; }else{ $map_type = $this->map_type; }
		$this->output_js_contents .= '
						mapTypeId: google.maps.MapTypeId.'.$map_type;
		if ($this->backgroundColor) {
			$this->output_js_contents .= ',
					backgroundColor: \''.$this->backgroundColor.'\'';
		}
		if ($this->disableDefaultUI) {
			$this->output_js_contents .= ',
					disableDefaultUI: true';
		}
		if ($this->disableMapTypeControl) {
			$this->output_js_contents .= ',
					mapTypeControl: false';
		}
		if ($this->disableNavigationControl) {
			$this->output_js_contents .= ',
					navigationControl: false';
		}
		if ($this->disableScaleControl) {
			$this->output_js_contents .= ',
					scaleControl: false';
		}
		if ($this->disableStreetViewControl) {
			$this->output_js_contents .= ',
					streetViewControl: false';
		}
		if ($this->disableDoubleClickZoom) {
			$this->output_js_contents .= ',
					disableDoubleClickZoom: true';
		}
		if (!$this->draggable) {
			$this->output_js_contents .= ',
					draggable: false';
		}
		if ($this->draggableCursor!="") {
			$this->output_js_contents .= ',
					draggableCursor: "'.$this->draggableCursor.'"';
		}
		if ($this->draggingCursor!="") {
			$this->output_js_contents .= ',
					draggingCursor: "'.$this->draggingCursor.'"';
		}
		if (!$this->keyboardShortcuts) {
			$this->output_js_contents .= ',
					keyboardShortcuts: false';
		}
		$mapTypeControlOptions = array();
		$map_types = array();
		if ($this->mapTypeControlPosition!="") {
			array_push($mapTypeControlOptions, 'position: google.maps.ControlPosition.'.strtoupper($this->mapTypeControlPosition));
		}
		if ($this->mapTypeControlStyle!="" && (strtoupper($this->mapTypeControlStyle)=="DROPDOWN_MENU" || strtoupper($this->mapTypeControlStyle)=="HORIZONTAL_BAR")) {
			array_push($mapTypeControlOptions, 'style: google.maps.MapTypeControlStyle.'.strtoupper($this->mapTypeControlStyle));
		}
		if (count($this->map_types_available)) {
			foreach ($this->map_types_available as $map_type) { array_push($map_types, 'google.maps.MapTypeId.'.strtoupper($map_type)); }
		}
		if (count($this->styles) && $this->stylesAsMapTypes) {
			$styles = 0;
			foreach ($this->styles as $style) {
				array_push($map_types, '"style'.$styles.'"');
				$styleOutput .= '
						'.$this->map_name.'.mapTypes.set("style'.$styles.'", styles_'.$styles.');
				';
				if ($this->stylesAsMapTypesDefault==$style['name']) {
				$styleOutput .= '
						'.$this->map_name.'.setMapTypeId("style'.$styles.'");
				';
				}
				$styles++;
			}
		}
		if (count($map_types)) {
			array_push($mapTypeControlOptions, 'mapTypeIds: ['.implode(", ", $map_types).']');
		}
		if (count($mapTypeControlOptions)) {
			$this->output_js_contents .= ',
					mapTypeControlOptions: {'.implode(",", $mapTypeControlOptions).'}';
		}
		if ($this->minzoom!="") {
			$this->output_js_contents .= ',
					minZoom: '.$this->minzoom;
		}
		if ($this->maxzoom!="") {
			$this->output_js_contents .= ',
					maxZoom: '.$this->maxzoom;
		}
		if ($this->noClear) {
			$this->output_js_contents .= ',
					noClear: true';
		}
		if ($this->navigationControlPosition!="") {
			$this->output_js_contents .= ',
					navigationControlOptions: {position: google.maps.ControlPosition.'.strtoupper($this->navigationControlPosition).'}';
		}
		if ($this->scaleControlPosition!="") {
			$this->output_js_contents .= ',
					scaleControlOptions: {position: google.maps.ControlPosition.'.strtoupper($this->scaleControlPosition).'}';
		}
		if (!$this->scrollwheel) {
			$this->output_js_contents .= ',
					scrollwheel: false';
		}
		if ($this->streetViewControlPosition!="") {
			$this->output_js_contents .= ',
					streetViewControlOptions: {position: google.maps.ControlPosition.'.strtoupper($this->streetViewControlPosition).'}';
		}
		if ($this->tilt==45) {
			$this->output_js_contents .= ',
					tilt: '.$this->tilt;
		}
		$zoomControlOptions = array();
		if ($this->zoomControlPosition!="") { array_push($zoomControlOptions, 'position: google.maps.ControlPosition.'.strtoupper($this->zoomControlPosition)); }
		if ($this->zoomControlStyle!="" && (strtoupper($this->zoomControlStyle)=="SMALL" || strtoupper($this->zoomControlStyle)=="LARGE")) { array_push($zoomControlOptions, 'style: google.maps.ZoomControlStyle.'.strtoupper($this->zoomControlStyle)); }
		if (count($zoomControlOptions)) {
			$this->output_js_contents .= ',
					zoomControlOptions: {'.implode(",", $zoomControlOptions).'}';
		}
		$this->output_js_contents .= '}
				'.$this->map_name.' = new google.maps.Map(document.getElementById("'.$this->map_div_id.'"), myOptions);
				';

		if ($styleOutput!="") {
			$this->output_js_contents .= $styleOutput.'
				';
		}

		// hack zenphoto
		$this->output_js_contents .= '
				var oms = new OverlappingMarkerSpiderfier(map, {keepSpiderfied: true});

				oms.addListener("click", function(marker){
					iw.setContent(marker.get("content"));
					iw.open(map, marker);
				});
				oms.addListener("spiderfy", function(markers){
					iw.close();
				});

				';
		// end hack zenphoto

		if ($this->trafficOverlay) {
			$this->output_js_contents .= 'var trafficLayer = new google.maps.TrafficLayer();
				trafficLayer.setMap('.$this->map_name.');
				';
		}
		if ($this->bicyclingOverlay) {
			$this->output_js_contents .= 'var bikeLayer = new google.maps.BicyclingLayer();
				bikeLayer.setMap('.$this->map_name.');
				';
		}

		if ($this->kmlLayerURL!="") {
			$this->output_js_contents .= '
				var kmlLayerOptions = {
					map: '.$this->map_name;
			if ($this->kmlLayerPreserveViewport) {
				$this->output_js_contents .= ',
					preserveViewport: true';
			}
			$this->output_js_contents .= '
				}
				var kmlLayer = new google.maps.KmlLayer("'.$this->kmlLayerURL.'", kmlLayerOptions);
				';
		}

		if ($this->panoramio) {
			$this->output_js_contents .= 'var panoramioLayer = new google.maps.panoramio.PanoramioLayer();
				';
			if ($this->panoramioTag!="") { $this->output_js_contents .= 'panoramioLayer.setTag("'.$this->panoramioTag.'");
				'; }
			if ($this->panoramioUser!="") { $this->output_js_contents .= 'panoramioLayer.setUserId("'.$this->panoramioUser.'");
				'; }
			$this->output_js_contents .= '
				panoramioLayer.setMap('.$this->map_name.');
				';
		}

		if (strtolower($this->map_type)=="street") { // if defaulting the map to Street View
			$this->output_js_contents .= '
					var streetViewOptions = {
						position: myLatlng';
			if (!$this->streetViewAddressControl) {
				$this->output_js_contents .= ',
					addressControl: false';
			}
			if ($this->streetViewAddressPosition!="") {
				$this->output_js_contents .= ',
					addressControlOptions: { position: google.maps.ControlPosition.'.$this->streetViewAddressPosition.' }';
			}
			if ($this->streetViewCloseButton) {
				$this->output_js_contents .= ',
					enableCloseButton: true';
			}
			if (!$this->streetViewLinksControl) {
				$this->output_js_contents .= ',
					linksControl: false';
			}
			if (!$this->streetViewPanControl) {
				$this->output_js_contents .= ',
					panControl: false';
			}
			if ($this->streetViewPanPosition!="") {
				$this->output_js_contents .= ',
					panControlOptions: { position: google.maps.ControlPosition.'.$this->streetViewPanPosition.' }';
			}
			if ($this->streetViewPovHeading!=0 || $this->streetViewPovPitch!=0 || $this->streetViewPovZoom!=0) {
				$this->output_js_contents .= ',
					pov: {
						heading: '.$this->streetViewPovHeading.',
						pitch: '.$this->streetViewPovPitch.',
						zoom: '.$this->streetViewPovZoom.'
					}';
			}
			if (!$this->streetViewZoomControl) {
				$this->output_js_contents .= ',
					zoomControl: false';
			}
			if ($this->streetViewZoomPosition!="" || $this->streetViewZoomStyle!="") {
				$this->output_js_contents .= ',
					zoomControlOptions: {';
				if ($this->streetViewZoomPosition!="") {
					$this->output_js_contents .= '
						position: google.maps.ControlPosition.'.$this->streetViewZoomPosition.',';
				}
				if ($this->streetViewZoomStyle!="") {
					$this->output_js_contents .= '
						style: google.maps.ZoomControlStyle.'.$this->streetViewZoomStyle.',';
				}
				$this->output_js_contents = trim($this->output_js_contents, ",");
				$this->output_js_contents .= '}';
			}
			$this->output_js_contents .= '
				};
				var streetView = new google.maps.StreetViewPanorama(document.getElementById("'.$this->map_div_id.'"), streetViewOptions);
				streetView.setVisible(true);
						';
		}

		if ($this->center=="auto") { // if wanting to center on the users location
			$this->output_js_contents .= '
				// Try W3C Geolocation (Preferred)
				if(navigator.geolocation) {
					navigator.geolocation.getCurrentPosition(function(position) {
						'.$this->map_name.'.setCenter(new google.maps.LatLng(position.coords.latitude,position.coords.longitude));
					}, function() { alert("Unable to get your current position. Please try again. Geolocation service failed."); });
				// Try Google Gears Geolocation
				} else if (google.gears) {
					var geo = google.gears.factory.create(\'beta.geolocation\');
					geo.getCurrentPosition(function(position) {
						'.$this->map_name.'.setCenter(new google.maps.LatLng(position.latitude,position.longitude));
					}, function() { alert("Unable to get your current position. Please try again. Geolocation service failed."); });
				// Browser doesn\'t support Geolocation
				}else{
					alert(\'Your browser does not support geolocation.\');
				}
			';
		}

		if ($this->directions) {
			$this->output_js_contents .= 'directionsDisplay.setMap('.$this->map_name.');
			';
			if ($this->directionsDivID!="") {
				$this->output_js_contents .= 'directionsDisplay.setPanel(document.getElementById("'.$this->directionsDivID.'"));
			';
			}
			if ($this->directionsDraggable && $this->directionsChanged!="") {
				$this->output_js_contents .= 'google.maps.event.addListener(directionsDisplay, "directions_changed", function() {
					'.$this->directionsChanged.'
				});
			';
			}
		}

		if ($this->drawing) {

			if ($this->drawingControlPosition=='') { $this->drawingControlPosition = 'TOP_CENTER'; }

			$this->output_js_contents .= 'drawingManager = new google.maps.drawing.DrawingManager({
				drawingMode: google.maps.drawing.OverlayType.'.strtoupper($this->drawingDefaultMode).',
					drawingControl: '.(!$this->drawingControl ? 'false' : 'true').',
					drawingControlOptions: {
						position: google.maps.ControlPosition.'.strtoupper($this->drawingControlPosition);
				$shapeOptions = '';
			if (count($this->drawingModes)) {
				$this->output_js_contents .= ',
					drawingModes: [';
				$i=0;
				foreach ($this->drawingModes as $drawingMode) {
					if ($i>0) { $this->output_js_contents .= ','; }
					$this->output_js_contents .= 'google.maps.drawing.OverlayType.'.strtoupper($drawingMode);
					if (strtoupper($drawingMode)!="MARKER") {
						$shapeOptions .= ',
						'.strtolower($drawingMode).'Options: {
							editable: true
						}';
					}
					$i++;
				}
				$this->output_js_contents .= ']';
				}
			$this->output_js_contents .= '
				}'.$shapeOptions.'
			});
			drawingManager.setMap('.$this->map_name.');
			';

			$this->output_js_contents .= '
			google.maps.event.addListener(drawingManager, "overlaycomplete", function(event) {
				var newShape = event.overlay;
				newShape.type = event.type;
				';
			if (count($this->drawingOnComplete)) {
				foreach ($this->drawingOnComplete as $shape=>$js) {
					$this->output_js_contents .= 'if (event.type==google.maps.drawing.OverlayType.'.strtoupper($shape).') {
						'.$js.'
					}
					';
				}
			}

			if (count($this->drawingOnEdit)) {

				if (isset($this->drawingOnEdit['polygon'])) {
					$this->output_js_contents .= '
				if (newShape.type==google.maps.drawing.OverlayType.POLYGON) {
					var newShapePaths = newShape.getPaths();
					for (var i=0; i<newShapePaths.length; i++) {
						google.maps.event.addListener(newShapePaths.getAt(i), "set_at", function(event) {
							'.$this->drawingOnEdit['polygon'].'
						});
						google.maps.event.addListener(newShapePaths.getAt(i), "insert_at", function(event) {
							'.$this->drawingOnEdit['polygon'].'
						});
						google.maps.event.addListener(newShapePaths.getAt(i), "remove_at", function(event) {
							'.$this->drawingOnEdit['polygon'].'
						});
					}
				}';
				}
				if (isset($this->drawingOnEdit['polyline'])) {
					$this->output_js_contents .= '
				if (newShape.type==google.maps.drawing.OverlayType.POLYLINE) {
					var newShapePaths = newShape.getPaths();
					for (var i=0; i<newShapePaths.length; i++) {
						google.maps.event.addListener(newShapePaths.getAt(i), "set_at", function(event) {
							'.$this->drawingOnEdit['polyline'].'
						});
						google.maps.event.addListener(newShapePaths.getAt(i), "insert_at", function(event) {
							'.$this->drawingOnEdit['polyline'].'
						});
						google.maps.event.addListener(newShapePaths.getAt(i), "remove_at", function(event) {
							'.$this->drawingOnEdit['polyline'].'
						});
					}
				}';
				}
				if (isset($this->drawingOnEdit['rectangle'])) {
					$this->output_js_contents .= '
				if (newShape.type==google.maps.drawing.OverlayType.RECTANGLE) {
					google.maps.event.addListener(newShape, "bounds_changed", function(event) {
						'.$this->drawingOnEdit['rectangle'].'
					});
				}';
				}
				if (isset($this->drawingOnEdit['circle'])) {
					$this->output_js_contents .= '
				if (newShape.type==google.maps.drawing.OverlayType.CIRCLE) {
					google.maps.event.addListener(newShape, "radius_changed", function(event) {
						'.$this->drawingOnEdit['circle'].'
					});
					google.maps.event.addListener(newShape, "center_changed", function(event) {
						'.$this->drawingOnEdit['circle'].'
					});
				}';
				}
			}

			$this->output_js_contents .= '
			});';

		}

		if ($this->places) {

			$placesLocationSet = false;

			if ($this->placesLocationSW!="" && $this->placesLocationNE!="") { // if search based on bounds

				$placesLocationSet = true;

				if ($this->is_lat_long($this->placesLocationSW)) {
					$this->output_js_contents .= 'var placesLocationSW = new google.maps.LatLng('.$this->placesLocationSW.');
			';
				}else{  // if centering the map on an address
					$lat_long = $this->get_lat_long_from_address($this->placesLocationSW);
					$this->output_js_contents .= 'var placesLocationSW = new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].');
			';
				}

				if ($this->is_lat_long($this->placesLocationNE)) {
					$this->output_js_contents .= 'var placesLocationNE = new google.maps.LatLng('.$this->placesLocationNE.');
			';
				}else{  // if centering the map on an address
					$lat_long = $this->get_lat_long_from_address($this->placesLocationNE);
					$this->output_js_contents .= 'var placesLocationNE = new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].');
			';
				}

			}

			if (($placesLocationSet || $this->placesLocation!="") || count($this->placesTypes) || $this->placesName!="")
			{
				$this->output_js_contents .= 'var placesRequest = {
					';
				if ($placesLocationSet) {
					$this->output_js_contents .= 'bounds: new google.maps.LatLngBounds(placesLocationSW, placesLocationNE)
						';
				}else{
					if ($this->placesLocation!="") { // if search based on a center point
						if ($this->is_lat_long($this->placesLocation)) { // if centering the map on a lat/long
							$this->output_js_contents .= 'location: new google.maps.LatLng('.$this->placesLocation.')
						';
						}else{  // if centering the map on an address
							$lat_long = $this->get_lat_long_from_address($this->placesLocation);
							$this->output_js_contents .= 'location: new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].')
						';
						}
						$this->output_js_contents .= ',radius: '.$this->placesRadius.'
						';
					}
				}

				if (count($this->placesTypes)) {
					$this->output_js_contents .= ',types: [\''.implode("','", $this->placesTypes).'\']
						';
				}
				if ($this->placesName!="") {
					$this->output_js_contents .= ',name : \''.$this->placesName.'\'
						';
				}
				$this->output_js_contents .= '};

				placesService = new google.maps.places.PlacesService('.$this->map_name.');
				placesService.search(placesRequest, placesCallback);
				';
			}

			if ($this->placesAutocompleteInputID != "")
			{
				$this->output_js_contents .= 'var autocompleteOptions = {
					';
				$autocompleteOptions = '';
				if ($this->placesAutocompleteBoundSW != "" && $this->placesAutocompleteBoundNE != "")
				{
					if ($this->is_lat_long($this->placesAutocompleteBoundSW)) {
						$autocompleteOptionsSW = 'new google.maps.LatLng('.$this->placesAutocompleteBoundSW.')
					';
					}else{  // if centering the map on an address
						$lat_long = $this->get_lat_long_from_address($this->placesAutocompleteBoundSW);
						$autocompleteOptionsSW = 'new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].')
					';
					}

					if ($this->is_lat_long($this->placesAutocompleteBoundNE)) {
						$autocompleteOptionsNE = 'new google.maps.LatLng('.$this->placesAutocompleteBoundNE.')
					';
					}else{  // if centering the map on an address
						$lat_long = $this->get_lat_long_from_address($this->placesAutocompleteBoundNE);
						$autocompleteOptionsNE = 'new google.maps.LatLng('.$lat_long[0].', '.$lat_long[1].')
					';
					}
					$autocompleteOptions .= 'bounds: new google.maps.LatLngBounds('.$autocompleteOptionsSW.', '.$autocompleteOptionsNE.')';
				}
				if (count($this->placesAutocompleteTypes))
				{
					if ($autocompleteOptions != "")
					{
						 $autocompleteOptions .= ',
						 ';
					}
					$autocompleteOptions .= 'types: [\''.implode("','", $this->placesAutocompleteTypes).'\']';
				}
				$this->output_js_contents .= $autocompleteOptions;
				$this->output_js_contents .= '}';

				$this->output_js_contents .= '
				var autocompleteInput = document.getElementById(\''.$this->placesAutocompleteInputID.'\');

				placesAutocomplete = new google.maps.places.Autocomplete(autocompleteInput, autocompleteOptions);
				';

				if ($this->placesAutocompleteBoundsMap)
				{
					$this->output_js_contents .= 'placesAutocomplete.bindTo(\'bounds\', map);
					';
				}

				if ($this->placesAutocompleteOnChange != "")
				{
					$this->output_js_contents .= 'google.maps.event.addListener(placesAutocomplete, \'place_changed\', function() {
						'.$this->placesAutocompleteOnChange.'
					});
					';
				}
			}

		}

		if ($this->onboundschanged!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "bounds_changed", function(event) {
					'.$this->onboundschanged.'
				});
			';
		}
		if ($this->oncenterchanged!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "center_changed", function(event) {
					'.$this->oncenterchanged.'
				});
			';
		}
		if ($this->onclick!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "click", function(event) {
					'.$this->onclick.'
				});
			';
		}
		if ($this->ondblclick!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "dblclick", function(event) {
					'.$this->ondblclick.'
				});
			';
		}
		if ($this->ondrag!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "drag", function(event) {
					'.$this->ondrag.'
				});
			';
		}
		if ($this->ondragend!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "dragend", function(event) {
					'.$this->ondragend.'
				});
			';
		}
		if ($this->ondragstart!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "dragstart", function(event) {
					'.$this->ondragstart.'
				});
			';
		}
		if ($this->onidle!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "idle", function(event) {
					'.$this->onidle.'
				});
			';
		}
		if ($this->onmousemove!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "mousemove", function(event) {
					'.$this->onmousemove.'
				});
			';
		}
		if ($this->onmouseout!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "mouseout", function(event) {
					'.$this->onmouseout.'
				});
			';
		}
		if ($this->onmouseover!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "mouseover", function(event) {
					'.$this->onmouseover.'
				});
			';
		}
		if ($this->onresize!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "resize", function(event) {
					'.$this->onresize.'
				});
			';
		}
		if ($this->onrightclick!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "rightclick", function(event) {
					'.$this->onrightclick.'
				});
			';
		}
		if ($this->ontilesloaded!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "tilesloaded", function(event) {
					'.$this->ontilesloaded.'
				});
			';
		}
		if ($this->onzoomchanged!="") {
			$this->output_js_contents .= 'google.maps.event.addListener(map, "zoom_changed", function(event) {
					'.$this->onzoomchanged.'
				});
			';
		}

		// add markers
		if (count($this->markers)) {
			foreach ($this->markers as $marker) {
				$this->output_js_contents .= $marker;
			}
		}
		//

		if ($this->cluster) {
			$this->output_js_contents .= '
			var clusterOptions = {
				gridSize: '.$this->clusterGridSize;
			if ($this->clusterMaxZoom!="") { $this->output_js_contents .= ',
				maxZoom: '.$this->clusterMaxZoom; }
			if (!$this->clusterZoomOnClick) { $this->output_js_contents .= ',
				zoomOnClick: false'; }
			if ($this->clusterAverageCenter) { $this->output_js_contents .= ',
				averageCenter: true'; }
			$this->output_js_contents .= ',
				minimumClusterSize: '.$this->clusterMinimumClusterSize.'
			};
			markerCluster = new MarkerClusterer('.$this->map_name.', markers, clusterOptions);
			';
		}

		// add polylines
		if (count($this->polylines)) {
			foreach ($this->polylines as $polyline) {
				$this->output_js_contents .= $polyline;
			}
		}
		//

		// add polygons
		if (count($this->polygons)) {
			foreach ($this->polygons as $polygon) {
				$this->output_js_contents .= $polygon;
			}
		}
		//

		// add circles
		if (count($this->circles)) {
			foreach ($this->circles as $circle) {
				$this->output_js_contents .= $circle;
			}
		}
		//

		// add rectangles
		if (count($this->rectangles)) {
			foreach ($this->rectangles as $rectangle) {
				$this->output_js_contents .= $rectangle;
			}
		}
		//

		// add ground overlays
		if (count($this->overlays)) {
			foreach ($this->overlays as $overlay) {
				$this->output_js_contents .= $overlay;
			}
		}
		//

		if ($this->zoom=="auto") {

			$this->output_js_contents .= '
			fitMapToBounds();
			';

		}

		if ($this->adsense) {
			$this->output_js_contents .= '
			var adUnitDiv = document.createElement("div");

				// Note: replace the publisher ID noted here with your own
				// publisher ID.
				var adUnitOptions = {
					format: google.maps.adsense.AdFormat.'.$this->adsenseFormat.',
					position: google.maps.ControlPosition.'.$this->adsensePosition.',
					publisherId: "'.$this->adsensePublisherID.'",
					';
				if ($this->adsenseChannelNumber!="") { $this->output_js_contents .= 'channelNumber: "'.$this->adsenseChannelNumber.'",
					'; }
				$this->output_js_contents .= 'map: map,
					visible: true
				};
				adUnit = new google.maps.adsense.AdUnit(adUnitDiv, adUnitOptions);
				';
		}

		if ($this->directions && $this->directionsStart!="" && $this->directionsEnd!="") {
			if ($this->directionsStart=="auto") {
				$this->output_js_contents .= '
				// Try W3C Geolocation (Preferred)
				if(navigator.geolocation) {
					navigator.geolocation.getCurrentPosition(function(position) {
						start = position.coords.latitude+","+position.coords.longitude;
						calcRoute(start, \''.$this->directionsEnd.'\');
					}, function() { alert("Unable to get your current position. Please try again. Geolocation service failed."); });
				// Try Google Gears Geolocation
				} else if (google.gears) {
					var geo = google.gears.factory.create(\'beta.geolocation\');
					geo.getCurrentPosition(function(position) {
						start = position.latitude+","+position.longitude;
						calcRoute(start, \''.$this->directionsEnd.'\');
					}, function() { alert("Unable to get your current position. Please try again. Geolocation service failed."); });
				// Browser doesn\'t support Geolocation
				}else{
					alert(\'Your browser does not support geolocation.\');
				}
				';
			}else{
				$this->output_js_contents .= '
				calcRoute(\''.$this->directionsStart.'\', \''.$this->directionsEnd.'\');
				';
			}
		}

		if ($this->onload!="") {
			$this->output_js_contents .= '
				'.$this->onload;
		}

		$this->output_js_contents .= '

			}

		';

		// add markers
		$this->output_js_contents .= '
		function createMarker(markerOptions) {
			var marker = new google.maps.Marker(markerOptions);
			markers.push(marker);
			lat_longs.push(marker.getPosition());
			return marker;
		}
		';
		//

		if ($this->directions) {

			$this->output_js_contents .= 'function calcRoute(start, end) {

			var request = {
						origin:start,
						destination:end,
						travelMode: google.maps.TravelMode.'.$this->directionsMode.'
						';
			if ($this->region!="" && strlen($this->region)==2) {
				$this->output_js_contents .= ',region: '.strtoupper($this->region).'
					';
			}
			if (trim($this->directionsUnits)!="" && (strtolower(trim($this->directionsUnits)) == "metric" || strtolower(trim($this->directionsUnits)) == "imperial")) {
				$this->output_js_contents .= ',unitSystem: google.maps.UnitSystem.'.strtoupper(trim($this->directionsUnits)).'
					';
			}
			if ($this->directionsAvoidTolls) {
				$this->output_js_contents .= ',avoidTolls: true
					';
			}
			if ($this->directionsAvoidHighways) {
				$this->output_js_contents .= ',avoidHighways: true
					';
			}

			$this->output_js_contents .= '
			};
					directionsService.route(request, function(response, status) {
						if (status == google.maps.DirectionsStatus.OK) {
								directionsDisplay.setDirections(response);
						}else{
							switch (status) {
								case "NOT_FOUND": { alert("Either the start location or destination were not recognised"); break }
								case "ZERO_RESULTS": { alert("No route could be found between the start location and destination"); break }
								case "MAX_WAYPOINTS_EXCEEDED": { alert("Maximum waypoints exceeded. Maximum of 8 allowed"); break }
								case "INVALID_REQUEST": { alert("Invalid request made for obtaining directions"); break }
								case "OVER_QUERY_LIMIT": { alert("This webpage has sent too many requests recently. Please try again later"); break }
								case "REQUEST_DENIED": { alert("This webpage is not allowed to request directions"); break }
								case "UNKNOWN_ERROR": { alert("Unknown error with the server. Please try again later"); break }
							}
						}
					});
			}
			';

		}

		if ($this->places) {
			$this->output_js_contents .= 'function placesCallback(results, status) {
				if (status == google.maps.places.PlacesServiceStatus.OK) {
					for (var i = 0; i < results.length; i++) {

						var place = results[i];

						var placeLoc = place.geometry.location;
						var placePosition = new google.maps.LatLng(placeLoc.lat(), placeLoc.lng());
						var markerOptions = {
							map: '.$this->map_name.',
									position: placePosition
								};
								var marker = createMarker(markerOptions);
								marker.set("content", place.name);
								google.maps.event.addListener(marker, "click", function() {
									iw.setContent(this.get("content"));
									iw.open('.$this->map_name.', this);
								});

								lat_longs.push(placePosition);

					}
					';
			if ($this->zoom=="auto") { $this->output_js_contents .= 'fitMapToBounds();'; }
			$this->output_js_contents .= '
				}
			}
			';
		}

		if ($this->zoom=="auto") {
			$this->output_js_contents .= '
			function fitMapToBounds() {
				var bounds = new google.maps.LatLngBounds();
				if (lat_longs.length>0) {
					for (var i=0; i<lat_longs.length; i++) {
						bounds.extend(lat_longs[i]);
					}
					'.$this->map_name.'.fitBounds(bounds);
				}
			}
			';
		}

		if ($this->loadAsynchronously) {
			$this->output_js_contents .= '
			function loadScript() {
				var script = document.createElement("script");
					script.type = "text/javascript";
					script.src = "'.$apiLocation.'&callback=initialize";
					document.body.appendChild(script);
			}
			window.onload = loadScript;
			';
		}else{
			$this->output_js_contents .= '
				window.onload = initialize;
			';
		}

		// Minify the Javascript if the $minifyJS config value is true. Requires Jsmin.php and PHP 5+
		if ($this->minifyJS) {
			$CI = get_instance();
			$CI->load->library('jsmin');
			$this->output_js_contents = $CI->jsmin->min($this->output_js_contents);
		}
		if ($this->jsfile=="") {
			$this->output_js .= $this->output_js_contents;
		}else{ // if needs writing to external js file
			if (!$handle = fopen($this->jsfile, "w")) {
				$this->output_js .= $this->output_js_contents;
			}else{
				if (!fwrite($handle, $this->output_js_contents)) {
					$this->output_js .= $this->output_js_contents;
				}else{
					$this->output_js .= '
					<script src="'.$this->jsfile.'" type="text/javascript"></script>';
				}
			}
		}

		if ($this->jsfile=="") {
			$this->output_js .= '
			//]]>
			</script>';
		}



		// set height and width
		if (is_numeric($this->map_width)) { // if no width type set
			$this->map_width = $this->map_width.'px';
		}
		if (is_numeric($this->map_height)) { // if no height type set
			$this->map_height = $this->map_height.'px';
		}
		//

		$this->output_html .= '<div id="'.$this->map_div_id.'" style="width:'.$this->map_width.'; height:'.$this->map_height.';"></div>';

		return array('js'=>$this->output_js, 'html'=>$this->output_html, 'markers'=>$this->markersInfo);

	}

	function is_lat_long($input)
	{

		$input = str_replace(", ", ",", trim($input));
		$input = explode(",", $input);
		if (count($input)==2) {

			if (is_numeric($input[0]) && is_numeric($input[1])) { // is a lat long
				return true;
			}else{ // not a lat long - incorrect values
				return false;
			}

		}else{ // not a lat long - too many parts
			return false;
		}

	}

	function get_lat_long_from_address($address, $attempts = 0)
	{

		$lat = 0;
		$lng = 0;

		$error = '';

		if ($this->geocodeCaching) { // if caching of geocode requests is activated

			$CI = get_instance();
			$CI->load->database();
			$CI->db->select("latitude,longitude");
			$CI->db->from("geocoding");
			$CI->db->where("address", trim(strtolower($address)));
			$query = $CI->db->get();

			if ($query->num_rows()>0) {
				$row = $query->row();
				return array($row->latitude, $row->longitude);
			}

		}
		if ($this->https) { $data_location = 'https://'; }else{ $data_location = 'http://'; }
		$data_location .= "maps.google.com/maps/api/geocode/json?address=".urlencode(utf8_encode($address))."&sensor=".$this->sensor;
		if ($this->region!="" && strlen($this->region)==2) { $data_location .= "&region=".$this->region; }
		$data = file_get_contents($data_location);

		$data = json_decode($data);

		if ($data->status=="OK")
		{
			$lat = $data->results[0]->geometry->location->lat;
			$lng = $data->results[0]->geometry->location->lng;

			if ($this->geocodeCaching) { // if we to need to cache this result
				$data = array(
					"address"=>trim(strtolower($address)),
					"latitude"=>$lat,
					"longitude"=>$lng
				);
				$CI->db->insert("geocoding", $data);
			}
		}
		else
		{
			if ($data->status == "OVER_QUERY_LIMIT")
			{
				$error = $data->status;
				if ($attempts < 2)
				{
					sleep(1);
					++$attempts;
					list($lat, $lng, $error) = $this->get_lat_long_from_address($address, $attempts);
				}
			}
		}

		return array($lat, $lng, $error);

	}

}

?>