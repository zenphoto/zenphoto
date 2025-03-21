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
$_GET['page'] = 'gallerystatistics';
adminGalleryStats::registerSubTabs();
printAdminHeader('overview', 'general');
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
				$currenttab = isset($_GET['tab']) ? sanitize($_GET['tab']) : 'general';
				if (!isset($_GET['sortorder'])) {
					adminGalleryStats::printStatisticsMenu($currenttab);
				}
				if($currenttab == 'general') {
					adminGalleryStats::printDiskSpaceStats();
					adminGalleryStats::printImageTypeStats();
				}
				if (isset($_GET['sortorder'])) {
					// If a single list is requested
					$fromtonumbers = adminGalleryStats::getProcessedFromToNumbers();
					$type = sanitize($_GET['tab']);
					$sortorder = sanitize($_GET['sortorder']);
					adminGalleryStats::printSingleStatSelectionForm($fromtonumbers, $sortorder, $type );
					$supported = adminGalleryStats::getSupportedTypes();
					if (array_key_exists($type, $supported) && in_array($sortorder, $supported[$type]['sortorders'])) {
						$statsobj = new adminGalleryStats($sortorder, $type, $fromtonumbers['from'], $fromtonumbers['to']);
						$statsobj->printStatistics();
					}
				} else {
					// If a general tab
					if ($currenttab == 'downloads' && extensionEnabled('downloadList')) {
						if (isset($_GET['removeoutdateddownloads'])) {
							XSRFdefender('removeoutdateddownloads');
							downloadList::clearOutdatedDownloads();
							echo '<p class="messagebox fade-message">' . gettext('Outdated file entries cleared from the database') . '</p>';
						}
						if (isset($_GET['removealldownloads'])) {
							XSRFdefender('removealldownloads');
							downloadList::clearDownloads();
							echo '<p class="messagebox fade-message">' . gettext('All download file entries cleared from the database') . '</p>';
						}
						?>
						<p class="buttons"><a href="?removeoutdateddownloads&amp;XSRFToken=<?php echo getXSRFToken('removeoutdateddownloads') ?>&amp;sortorder=mostdownloaded&amp;tab=downloads"><?php echo gettext('Clear outdated downloads from database'); ?></a></p>
						<p class="buttons"><a href="?removealldownloads&amp;XSRFToken=<?php echo getXSRFToken('removealldownloads') ?>&amp;sortorder=mostdownloaded&amp;tab=downloads"><?php echo gettext('Clear all downloads from database'); ?></a></p><br class="clearall" />
						<br class="clearall" /><br />
						<?php
					} else {
						echo '<strong>' . gettext('The downloadList plugin is not active') . '</strong>';
					}
					$supported = adminGalleryStats::getSupportedTypesByType($currenttab);
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
				?>
			</div>
		</div><!-- content -->
<?php printAdminFooter(); ?>
	</div><!-- main -->
</body>
<?php echo "</html>"; ?>
