<?php
/**
 * Detailed Gallery Statistics
 *
 * This plugin shows statistical graphs and info about your gallery\'s images and albums
 *
 * @package zpcore\admin\utilities
 */
define('OFFSET_PATH', 3);

require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');
require_once(dirname(dirname(__FILE__)) . '/' . PLUGIN_FOLDER . '/image_album_statistics.php');
if (extensionEnabled('zenpage')) {
	require_once(dirname(dirname(__FILE__)) . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-admin-functions.php');
}
require_once(dirname(dirname(__FILE__)) . '/classes/class-admingallerystats.php');

$buttonlist[] = array(
		'category' => gettext('Info'),
		'enable' => true,
		'button_text' => gettext('Gallery Statistics'),
		'formname' => 'gallery_statistics.php',
		'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/gallery_statistics.php',
		'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/bar_graph.png',
		'title' => gettext('Shows statistical graphs and info about your gallery’s images and albums.'),
		'alt' => '',
		'hidden' => '',
		'rights' => ADMIN_RIGHTS
);

admin_securityChecks(OVERVIEW_RIGHTS, currentRelativeURL());

$_zp_gallery->garbageCollect();
$webpath = WEBPATH . '/' . ZENFOLDER . '/';

$_zp_admin_menu['overview']['subtabs'] = array(gettext('Statistics') => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/gallery_statistics.php');
printAdminHeader('overview', 'statistics');
?>
<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/css/admin-statistics.css" type="text/css" media="screen" />
</head>

<body>
	<?php
	printLogoAndLinks();
	?>
	<div id="main">
		<span id="top"></span>
		<?php
		printTabs();

// getting the counts
		$albumcount = $_zp_gallery->getNumAlbums(true);
		$albumscount_unpub = $albumcount - $_zp_gallery->getNumAlbums(true, true);
		$imagecount = $_zp_gallery->getNumImages();
		$imagecount_unpub = $imagecount - $_zp_gallery->getNumImages(true);
		?>
		<div id="content">
		<?php printSubtabs() ?>
			<div class="tabbox">
			<?php zp_apply_filter('admin_note', 'statistics', ''); ?>
				<h1><?php echo gettext("Gallery Statistics"); ?></h1>
				<p><?php echo gettext("This page shows more detailed statistics of your gallery. For album statistics the bar graph always shows the total number of images in that album. For image statistics always the album the image is in is shown.<br />Un-published items are marked in dark red. Images are marked un-published if their (direct) album is, too."); ?></p>

				<?php
				if (!isset($_GET['stats']) AND !isset($_GET['fulllist'])) {
					adminGalleryStats::printStatisticsMenu();
					if (!isset($_GET['stats']) && !isset($_GET['fulllist'])) {
						adminGalleryStats::printDiskSpaceStats();
						adminGalleryStats::printImageTypeStats();
					}
					$supported = adminGalleryStats::getSupportedTypes();
					foreach ($supported as $type => $data) {
						?>
						<h2><?php echo $data['title']; ?></h2>
						<?php
						foreach ($data['sortorders'] as $sortorder) {
							$statsobj = new adminGalleryStats($sortorder, $type);
							$statsobj->printStatistics();
						}
					}
				}

				// If a single list is requested
				if (isset($_GET['type'])) {
					$fromtonumbers = adminGalleryStats::getProcessedFromToNumbers();
					$stats = sanitize($_GET['stats']);
					$type = sanitize($_GET['type']);
					adminGalleryStats::printSingleStatSelectionForm($fromtonumbers, $stats, $type);
					$supported = adminGalleryStats::getSupportedTypes();
					if (array_key_exists($type, $supported) && in_array($stats, $supported[$type]['sortorders'])) {
						$statsobj = new adminGalleryStats($stats, $type, $fromtonumbers['from'], $fromtonumbers['to']);
						$statsobj->printStatistics();
					}
				} // main if end
				?>
			</div>
		</div><!-- content -->
<?php printAdminFooter(); ?>
	</div><!-- main -->
</body>
<?php echo "</html>"; ?>
