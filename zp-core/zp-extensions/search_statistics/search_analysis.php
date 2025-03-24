<?php
/**
 *
 * Collects and analyzes searches
 *
 * @author Stephen Billard (sbillard)
 * @package zpcore\plugins\searchstatistics
 */
define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once dirname(dirname(dirname(__FILE__))) . '/classes/class-admingallerystats.php';
require_once 'class-admingallerystatssearch.php';
admin_securityChecks(OVERVIEW_RIGHTS, currentRelativeURL());

if (isset($_GET['reset'])) {
	admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());
	XSRFdefender('search_statistics');
	$sql = 'DELETE FROM ' . $_zp_db->prefix('plugin_storage') . ' WHERE `type`="search_statistics"';
	$_zp_db->query($sql);
	redirectURL(FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/search_statistics/search_analysis.php');
}
$_zp_admin_menu['overview']['subtabs'] = array(gettext('Search analysis') => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/search_statistics/search_analysis.php?page=searchstatistics&amp;tab=search');
printAdminHeader('overview', 'searchstatistics');
echo '</head>';

?>
<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/css/admin-statistics.css" type="text/css" media="screen" />
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php printSubtabs(); ?>
			<div class="tabbox">
				<?php zp_apply_filter('admin_note', 'albums', ''); ?>
				<h1><?php echo (gettext('Search analysis')); ?></h1>
				<?php
				if (!extensionEnabled('search_statistics')) {
					echo '<strong>' . gettext('The search_statistics plugin is not active') . '</strong>';
				}
				if (!isset($_GET['sortorder'])) {
					if (zp_loggedin(ADMIN_RIGHTS)) {
						?>
						<p class="buttons">
							<a href="?reset&amp;XSRFToken=<?php echo getXSRFToken('search_statistics'); ?>"><?php echo gettext('Reset all search statistics'); ?></a>
						</p>
						<br class="clearall" />
						<?php
					} 
					adminGalleryStatsSearch::printStatisticsMenu();
					$supported = adminGalleryStatsSearch::getSupportedTypes();
					foreach ($supported as $type => $data) {
						?>
						<h2><?php echo $data['title']; ?></h2>
						<?php
						foreach ($data['sortorders'] as $sortorder) {
							$statsobj = new adminGalleryStatsSearch($sortorder, 'search');
							$statsobj->printStatistics();
						}
					}
				}
					
				if (isset($_GET['tab'])) {
					$fromtonumbers = adminGalleryStatsSearch::getProcessedFromToNumbers();
					$sortorder = sanitize($_GET['sortorder']);
					$type = sanitize($_GET['tab']);
					adminGalleryStatsSearch::printSingleStatSelectionForm($fromtonumbers, $sortorder, $type);
					$supported = adminGalleryStatsSearch::getSupportedTypes();
					if (array_key_exists($type, $supported) && in_array($sortorder, $supported[$type]['sortorders'])) {
						$statsobj = new adminGalleryStatsSearch($sortorder, 'search', $fromtonumbers['from'], $fromtonumbers['to']);
						$statsobj->printStatistics();
					}
				}
				?>
			</div>
		</div>
	</div>
	<?php printAdminFooter(); ?>
</body>
<?php
echo "</html>";
?>
