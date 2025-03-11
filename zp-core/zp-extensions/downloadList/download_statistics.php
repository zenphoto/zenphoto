<?php
/**
 * Detailed Gallery Statistics
 *
 * This plugin shows statistical graphs and info about your gallery\'s images and albums
 *
 * This plugin is dependent on the css of the gallery_statistics utility plugin!
 *
 * @package zpcore\plugins\downloadlist
 */
define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(SERVERPATH. '/' . ZENFOLDER . '/classes/class-admingallerystats.php');


admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());

if (!zp_loggedin(OVERVIEW_RIGHTS)) { // prevent nefarious access to this page.
	redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . currentRelativeURL());
}

$webpath = WEBPATH . '/' . ZENFOLDER . '/';

$_zp_admin_menu['overview']['subtabs'] = array(gettext('Download') => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/downloadList/download_statistics.php?stats=mostdownloaded&type=downloads');
printAdminHeader('overview', 'download');
?>
<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/css/admin-statistics.css" type="text/css" media="screen" />
</head>

<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<span id="top">
			<?php printTabs(); ?>
		</span>
		<div id="content">
			<?php printSubtabs(); ?>
			<div class="tabbox">
				<?php
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
				<h1><?php echo gettext("Download Statistics"); ?></h1>
				<p><?php echo gettext("Shows statistical graphs and info about your gallery’s downloads if using the downloadList plugin."); ?></p>
				<p><?php echo gettext("Entries marked red do not exist in the download folder anymore but are kept for the statistics until you remove them manually via the button."); ?></p>

				<?php
				if (!extensionEnabled('downloadList')) {
					echo '<strong>' . gettext('The downloadList plugin is not active') . '</strong>';
				} else {
					?>
					<p class="buttons"><a href="?removeoutdateddownloads&amp;XSRFToken=<?php echo getXSRFToken('removeoutdateddownloads') ?>&amp;stats=mostdownloaded&amp;type=downloads"><?php echo gettext('Clear outdated downloads from database'); ?></a></p>
					<p class="buttons"><a href="?removealldownloads&amp;XSRFToken=<?php echo getXSRFToken('removealldownloads') ?>&amp;stats=mostdownloaded&amp;type=downloads"><?php echo gettext('Clear all downloads from database'); ?></a></p><br class="clearall" />
					<br class="clearall" /><br />
				<?php
					$fromtonumbers = adminGalleryStats::getProcessedFromToNumbers();
					adminGalleryStats::printSingleStatSelectionForm($fromtonumbers);
					$statsobj = new adminGalleryStats('mostdownloaded', 'downloads', $fromtonumbers['from'], $fromtonumbers['to']);
					$statsobj->printStatistics();
				}
				?>
			</div>
		</div><!-- content -->
		<?php printAdminFooter(); ?>
	</div><!-- main -->
</body>
<?php echo "</html>"; ?>
