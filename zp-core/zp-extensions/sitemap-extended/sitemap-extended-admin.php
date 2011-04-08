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
if(isset($_GET['generatesitemaps'])) {
	$sitemap_number = sanitize_numeric($_GET['number']);
	$sitemap_index = getSitemapIndexLinks();
	$sitemap_albums = getSitemapAlbums();
	$sitemap_images = getSitemapImages();
	if(getOption('zp_plugin_zenpage')) {
		$sitemap_newsindex = getSitemapZenpageNewsIndex();
		$sitemap_articles = getSitemapZenpageNewsArticles();
		$sitemap_categories = getSitemapZenpageNewsCategories();
		$sitemap_pages = getSitemapZenpagePages();
	}
	$numberAppend = '';
	if(isset($_GET['generatesitemaps']) && 
	(!empty($sitemap_index) 
	|| !empty($sitemap_albums)
	|| !empty($sitemap_images)
	|| !empty($sitemap_newsindex)
	|| !empty($sitemap_articles)
	|| !empty($sitemap_categories)
	|| !empty($sitemap_pages))) {
		$numberAppend = '-'.$sitemap_number;
		$metaURL = 'sitemap-extended-admin.php?generatesitemaps&amp;number='.($sitemap_number+1);
	} else {
		$metaURL = '';
	}
	if (!empty($metaURL)) {
		?>
		<meta http-equiv="refresh" content="10; url=<?php echo $metaURL; ?>" />
		<?php
	}
} // if(isset($_GET['generatesitemaps']) end 
?>
<link rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin-statistics.css" type="text/css" media="screen" />
<script type="text/javascript">
		// <!-- <![CDATA[
		$(document).ready(function(){
			$(".colorbox").colorbox({
				iframe: false
			});
		});
		// ]]> -->
	</script>
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
	<?php if(!isset($_GET['generatesitemaps']) && !isset($_GET['clearsitemapcache'])) { ?>
	<p class="notebox"><strong>NOTE:</strong> This is a work in progress version and may still not be fully optimized for huge galleries.</p>
	<p class="notebox"><strong>NOTE:</strong> If your theme uses different settings as the backend options the sitemaps may not match your site.</p>
	<p>This creates individual static xml sitemap files of the following items:</p>
	<ul>
		<li><strong>Zenphoto items</strong>
			<ul>
				<li><em>Index pages</em></li>
				<li><em>Albums</em>: These are splitted into individual sitemaps per album (incl. all albums pages). If you enable the Google sitemap extension its special image links are added to this sitemap. So handle with care if your gallery is huge!</li>
				<li><em>Images</em>: These are splitted into individual sitemaps per albums.</li>
			</ul>
		</li>
		<li><strong>Zenpage CMS items (if the plugin is enabled)</strong>
			<ul>
				<li><em>News index</em></li>
				<li><em>News Articles</em></li>
				<li><em>News categories</em></li>
				<li><em>Pages</em></li>
			</ul>
		</li>
	</ul>
	<p>Additionally a sitemapindex file is created that points to the separate ones above. You can reference this sitemapindex file in your robots.txt file or submit its url to services like Google via <code>www.yourdomain.com/zenphoto/index.php?sitemap</code></p>
	<p>Already existing files are overwritten with updated versions. All files are stored in the <code>/cache_html/sitemap/</code> folder.</p>
	<p class="buttons"><a href="sitemap-extended-admin.php?generatesitemaps&amp;number=1"><?php echo "Generate sitemaps"; ?></a></p>	
	<p class="buttons"><a href="sitemap-extended-admin.php?clearsitemapcache"><?php echo "Clear sitemap cache"; ?></a></p>	
	<br style="clear: both" /><br />
	<?php
		$cachefolder = SERVERPATH.'/cache_html/sitemap/';
		$dirs = array_diff(scandir($cachefolder),array( '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn'));
		echo '<h2>'.gettext('Available sitemap files:').'</h2>';
		if(!$dirs) {
			echo '<p>'.gettext('No sitemap files available.').'</p>';
		} else {
			echo '<ol>';
			foreach($dirs as $dir) {
				$filemtime = filemtime($cachefolder.$dir);
				$lastchange = zpFormattedDate(DATE_FORMAT,$filemtime);
				echo '<li>'.$dir.' ('.$lastchange.')'; //<a class="colorbox" href="'.FULLWEBPATH.'/cache_html/sitemap/'.$dir.'">Preview</a></li>';
			}
			echo '</ol>';
		}
	}
	if(isset($_GET['generatesitemaps'])) {
		echo '<ul>';
		generateSitemapCacheFile('sitemap-zenphoto-index',$sitemap_index);
	  generateSitemapCacheFile('sitemap-zenphoto-albums'.$numberAppend,$sitemap_albums);
		generateSitemapCacheFile('sitemap-zenphoto-images'.$numberAppend,$sitemap_images);
		if(getOption('zp_plugin_zenpage')) {
			generateSitemapCacheFile('sitemap-zenpage-newsindex',$sitemap_newsindex);
			generateSitemapCacheFile('sitemap-zenpage-news',$sitemap_articles);
			generateSitemapCacheFile('sitemap-zenpage-categories',$sitemap_categories);
			generateSitemapCacheFile('sitemap-zenpage-pages',$sitemap_pages);
		}
		echo '</ul>';
		echo '<p>'.gettext('Sitemap files are being generated...Patience please.').'</p>';
		if(empty($metaURL)) {
			generateSitemapIndexCacheFile(); 
		 ?>
		<p class="buttons"><a href="sitemap-extended-admin.php"><?php echo 'Back to Sitemap tools'; ?></a></p>	
		<?php
		}
	}
	if(isset($_GET['clearsitemapcache'])) {
		clearSitemapCache();
		echo gettext('Sitemap cache cleared');
		?>
		<p class="buttons"><a href="sitemap-extended-admin.php"><?php echo 'Back to Sitemap tools'; ?></a></p>	
		<?php
	}
		
	?>

</div><!-- content -->
<?php printAdminFooter(); ?>
</div><!-- main -->
</body>
<?php echo "</html>"; ?>