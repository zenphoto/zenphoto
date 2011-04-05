<?php
/**
 * Sitemap Tools
 * 
 * Tool to generate sitemaps
 * 
 * @package admin
 */

define('OFFSET_PATH', 4);
chdir(dirname(dirname(__FILE__)));

// user plugin variant
require_once('../../zp-core/admin-functions.php');
require_once('../../zp-core/admin-globals.php');

$button_text = gettext('Sitemap tools');
$button_hint = gettext("Tools to generate sitemaps.");
$button_icon = WEBPATH.'/'.ZENFOLDER.'/images/bar_graph.png';
$button_rights = ADMIN_RIGHTS;

admin_securityChecks(ADMIN_RIGHTS, currentRelativeURL(__FILE__));

if (getOption('zenphoto_release') != ZENPHOTO_RELEASE) {
	header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . "/setup.php");
	exit();
}

if (!zp_loggedin(OVERVIEW_RIGHTS)) { // prevent nefarious access to this page.
	header('Location: ' . FULLWEBPATH . '/' . ZENFOLDER . '/admin.php?from=' . currentRelativeURL(__FILE__));
	exit();
}

$gallery = new Gallery();
$webpath = WEBPATH.'/'.ZENFOLDER.'/';

printAdminHeader(gettext('utilities'),gettext('Sitemap tools'));
?>
<link rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin-statistics.css" type="text/css" media="screen" />
<?php

echo '</head>';
?>

<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<a name="top"></a>
<?php printTabs('home'); 
?>
<div id="content">
	<h1>Sitemap tools</h1>
	<p class="notebox"><strong>NOTE:</strong> This is a work in progress version and still not fully optimized for huge galleries.</p>
	<p>The first button generates separate static xml sitemap files for several Zenphoto and Zenpage items and also a sitemap index file that points to the separate ones. You can reference this file in your robots.txt file or submit its url to services like Google via <code>www.yourdomain.com/zenphoto/index.php?sitemap</code></p>
	<p>Existing files are overwritten with updates. All files are stored in the <code>/cache_html/sitemap/</code> folder.</p>
	<p class="buttons"><a href="?generatesitemaps"><?php echo "Generate sitemaps"; ?></a></p>	
	<p class="buttons"><a href="?clearsitemapcache"><?php echo "Clear sitemap cache"; ?></a></p>	
	<br style="clear: both" /><br />
	<?php
	if(!isset($_GET['generatesitemaps']) && !isset($_GET['clearsitemapcache'])) {
		$cachefolder = SERVERPATH.'/cache_html/sitemap/';
		$dirs = array_diff(scandir($cachefolder),array( '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn'));
		echo '<h2>'.gettext('Available sitemap files:').'</h2>';
		if(!$dirs) {
			echo '<p>'.gettext('No sitemap files available.').'</p>';
		} else {
			echo '<ol>';
			foreach($dirs as $dir) {
				echo '<li>'.$dir.'</li>';
			}
			echo '</ol>';
		}
	}
	if(isset($_GET['generatesitemaps'])) {
		echo '<h2>'.gettext('Sitemap files generated:').'</h2>';
		echo '<ol>';
		generateSitemapCacheFile('sitemap-zenphoto-index',getSitemapIndexLinks());
		generateSitemapCacheFile('sitemap-zenphoto-albums-images',getSitemapAlbumsAndImages());
		if(getOption('zp_plugin_zenpage')) {
			generateSitemapCacheFile('sitemap-zenpage-newsindex',getSitemapZenpageNewsIndex());
			generateSitemapCacheFile('sitemap-zenpage-pages',getSitemapZenpagePages());
			generateSitemapCacheFile('sitemap-zenpage-news',getSitemapZenpageNewsArticles());
			generateSitemapCacheFile('sitemap-zenpage-categories',getSitemapZenpageNewsCategories());
			generateSitemapIndexCacheFile();
		}
		echo '</ol>';
	}
	if(isset($_GET['clearsitemapcache'])) {
		clearSitemapCache();
		echo gettext('Sitemap cache cleared');
	}
		
	?>

</div><!-- content -->
<?php printAdminFooter(); ?>
</div><!-- main -->
</body>
<?php echo "</html>"; ?>