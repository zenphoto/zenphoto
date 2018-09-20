<?php
/**
 * Sitemap Tools
 *
 * Tool to generate sitemaps
 *
 * @package admin/sitemap-extended
 */
define('OFFSET_PATH', 3);

require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');
require_once(SERVERPATH . '/' . ZENFOLDER . '/template-functions.php');

admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL());

if (!zp_loggedin(OVERVIEW_RIGHTS)) { // prevent nefarious access to this page.
	header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . currentRelativeURL());
	exitZP();
}
if (isset($_GET['clearsitemapcache'])) {
	sitemap::clearCache();
	header('location:' . WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/sitemap-extended/sitemap-extended-admin.php');
	exitZP();
}

printAdminHeader('overview', 'sitemap');
if (isset($_GET['generatesitemaps'])) {
	$_zp_loggedin = NULL;
	$_sitemap_number = sanitize_numeric($_GET['number']);
	$sitemap_index = sitemap::getIndexLinks();
	$sitemap_albums = sitemap::getAlbums();
	$sitemap_images = sitemap::getImages();
	if (extensionEnabled('zenpage')) {
		$sitemap_newsindex = sitemap::getNewsIndex();
		$sitemap_articles = sitemap::getNewsArticles();
		$sitemap_categories = sitemap::getNewsCategories();
		$sitemap_pages = sitemap::getPages();
	}
	$numberAppend = '';
	if (isset($_GET['generatesitemaps']) && (!empty($sitemap_index) || !empty($sitemap_albums) || !empty($sitemap_images) || !empty($sitemap_newsindex) || !empty($sitemap_articles) || !empty($sitemap_categories) || !empty($sitemap_pages))) {
		$numberAppend = '-' . $_sitemap_number;
		$metaURL = 'sitemap-extended-admin.php?generatesitemaps&amp;number=' . ($_sitemap_number + SITEMAP_CHUNK);
	} else {
		$metaURL = '';
	}
	if (!empty($metaURL)) {
		?>
		<meta http-equiv="refresh" content="1; url=<?php echo $metaURL; ?>" />
		<?php
	}
} // if(isset($_GET['generatesitemaps']) end
?>
<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/admin-statistics.css" type="text/css" media="screen" />
<script type="text/javascript">
	// <!-- <![CDATA[
	window.addEventListener('load', function () {
		/*	$(".colorbox").colorbox({
		 iframe: false,
		 inline:true,
		 href: '#sitemap',
		 width: 90%,
		 photo: false,
		 close: '<?php echo gettext("close"); ?>'
		 }); */
	}, false);
	// ]]> -->
</script>
<?php
echo '</head>';
?>

<body>
	<?php
	printLogoAndLinks();
	?>
	<div id="main">
		<span id="top"></span>
		<?php printTabs();
		?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'sitemap', ''); ?>
			<h1><?php echo gettext('Sitemap tools'); ?></h1>
			<div class="tabbox">
				<?php if (!isset($_GET['generatesitemaps']) && !isset($_GET['clearsitemapcache'])) { ?>
					<p class="notebox"><?php echo gettext('<strong>NOTE:</strong> If your theme uses different custom settings instead of the backend options the sitemaps may not match your site.'); ?></p>
					<p><?php echo gettext('This creates individual static xml sitemap files of the following items:'); ?></p>
					<ul>
						<li><strong><?php echo gettext('netPhotoGraphics items'); ?></strong>
							<ul>
								<li><em><?php echo gettext('Index pages'); ?></em></li>
								<li><?php echo gettext('<em>Albums</em>: These are split into multiple sitemaps.'); ?></li>
								<li><?php echo gettext('<em>Images</em>: These are split into multiple sitemaps.'); ?></li>
							</ul>
						</li>
						<li><strong><?php echo gettext('Zenpage CMS items (if the plugin is enabled)'); ?></strong>
							<ul>
								<li><em><?php echo gettext('News index'); ?></em></li>
								<li><em><?php echo gettext('News Articles'); ?></em></li>
								<li><em><?php echo gettext('News categories'); ?></em></li>
								<li><em><?php echo gettext('Pages'); ?></em></li>
							</ul>
						</li>
					</ul>
					<p><?php echo sprintf(gettext('Additionally a sitemapindex file is created that points to the separate ones above. You can reference this sitemapindex file in your robots.txt file or submit its url to services like Google via <code>%1$s/index.php?sitemap</code>'), FULLWEBPATH); ?></p>
					<p><?php printf(gettext('The sitemap cache is cleared if you create new ones. All files are stored in the <code>/%s/sitemap/</code> folder.'), STATIC_CACHE_FOLDER); ?></p>
					<p class="buttons">
						<a href="sitemap-extended-admin.php?generatesitemaps&amp;number=1">
							<?php echo CHECKMARK_GREEN; ?>
							<?php echo gettext("Generate sitemaps"); ?>
						</a>
					</p>
					<p class="buttons">
						<a href="sitemap-extended-admin.php?clearsitemapcache">
							<?php echo RECYCLE_ICON; ?>
							<?php echo gettext("Clear sitemap cache"); ?>
						</a>
					</p>
					<br style="clear: both" />
					<br />
					<?php
					sitemap::printAvailableSitemaps();
				} // isset generate sitemaps / clearsitemap cache
				if (isset($_GET['generatesitemaps'])) {

					// clear cache before creating new ones
					if ($_sitemap_number == 1) {
						sitemap::clearCache();
					}
					echo '<ul>';
					sitemap::generateCacheFile('sitemap-zenphoto-index', $sitemap_index);
					sitemap::generateCacheFile('sitemap-zenphoto-albums' . $numberAppend, $sitemap_albums);
					sitemap::generateCacheFile('sitemap-zenphoto-images' . $numberAppend, $sitemap_images);
					if (extensionEnabled('zenpage')) {
						sitemap::generateCacheFile('sitemap-zenpage-newsindex', $sitemap_newsindex);
						sitemap::generateCacheFile('sitemap-zenpage-news', $sitemap_articles);
						sitemap::generateCacheFile('sitemap-zenpage-categories', $sitemap_categories);
						sitemap::generateCacheFile('sitemap-zenpage-pages', $sitemap_pages);
					}
					echo '</ul>';
					if (!empty($metaURL)) {
						echo '<p><img src="' . WEBPATH . '/' . ZENFOLDER . '/images/ajax-loader.gif" alt="" /><br /><br />' . gettext('Sitemap files are being generated...Patience please.') . '</p>';
					} else {
						sitemap::generateIndexCacheFile();
						?>
						<script type="text/javascript">
							// <!-- <![CDATA[
							window.addEventListener('load', function () {
								window.location = "<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/sitemap-extended/sitemap-extended-admin.php";
							}, false);
							// ]]> -->
						</script>
						<?php
					}
				}
				?>
			</div><!-- tabbox -->
		</div><!-- content -->
		<?php printAdminFooter(); ?>
	</div><!-- main -->
</body>
<?php echo "</html>"; ?>
