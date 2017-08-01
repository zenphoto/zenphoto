<?php
if (!defined('WEBPATH'))
	die();
if (function_exists('printAddThis')) {
	$zpmin_social = true;
} else {
	$zpmin_social = false;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<link rel="stylesheet" type="text/css" href="<?php echo $_zp_themeroot; ?>/css/main.css" />
		<?php if (function_exists('printGslideshow')) setOption('gslideshow_style', 'light', false); ?>
		<?php
		$showsearch = true;
		$galleryactive = false;
		$zpmin_metatitle = '';
		$zpmin_albumorimage = '';
		$zpmin_functionoption = '';
		$cbscript = false;
		?>
		<?php
		$zpmin_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
		switch ($_zp_gallery_page) {
			case 'index.php':
				require_once (SERVERPATH . '/' . ZENFOLDER . "/zp-extensions/image_album_statistics.php");
				$showsearch = false;
				$zpmin_social = false;
				break;
			case 'album.php':
			case 'favorites.php';
				$zpmin_metatitle = getBareAlbumTitle() . ' | ';
				$zpmin_metadesc = truncate_string(getBareAlbumDesc(), 150, '...');
				if (extensionEnabled('rss')) printRSSHeaderLink('Album', getAlbumTitle());
				$galleryactive = true;
				$cbscript = true;
				break;
			case 'image.php':
				$zpmin_metatitle = getBareImageTitle() . ' | ';
				$zpmin_metadesc = truncate_string(getBareImageDesc(), 150, '...');
				$galleryactive = true;
				$cbscript = true;
				break;
			case 'archive.php':
				$zpmin_metatitle = gettext("Archive View") . ' | ';
				break;
			case 'search.php':
				$zpmin_metatitle = gettext('Search') . " | " . html_encode(getSearchWords()) . ' | ';
				$galleryactive = true;
				$cbscript = true;
				$zpmin_social = false;
				break;
			case 'pages.php':
				$zpmin_metatitle = getBarePageTitle() . ' | ';
				$zpmin_metadesc = truncate_string(getBare(getPageContent(), 150, '...'));
				$cbscript = true;
				break;
			case 'news.php':
				if (is_NewsArticle()) {
					$zpmin_metatitle = gettext('News') . ' | ' . getBareNewsTitle() . ' | ';
					$zpmin_metadesc = truncate_string(getBare(getNewsContent(), 150, '...'));
				} else if ($_zp_current_category) {
					$zpmin_metatitle = gettext('News') . ' | ' . $_zp_current_category->getTitle() . ' | ';
					$zpmin_metadesc = truncate_string(getBare(getNewsCategoryDesc(), 150, '...'));
				} else if (getCurrentNewsArchive()) {
					$zpmin_metatitle = gettext('News') . ' | ' . getCurrentNewsArchive() . ' | ';
				} else {
					$zpmin_metatitle = gettext('News') . ' | ';
				}
				$cbscript = true;
				break;
			case 'slideshow.php':
				$zpmin_metatitle = getBareAlbumTitle() . ' | ' . gettext('Slideshow') . ' | ';
				if (!function_exists('printGslideshow')) {
					echo '<link rel="stylesheet" href="' . $_zp_themeroot . '/css/slideshow.css" type="text/css" />';
				}
				$showsearch = false;
				$zpmin_social = false;
				break;
			case 'contact.php':
				$zpmin_metatitle = gettext('Contact') . ' | ';
				$zpmin_social = false;
				break;
			case 'login.php':
				$zpmin_metatitle = gettext('Login') . ' | ';
				$zpmin_social = false;
				break;
			case 'register.php':
				$zpmin_metatitle = gettext('Register') . ' | ';
				$zpmin_social = false;
				break;
			case 'gallery.php':
				$zpmin_metatitle = gettext('Gallery Index') . ' | ';
				$galleryactive = true;
				break;
			case 'password.php':
				$zpmin_metatitle = gettext('Password Required') . ' | ';
				$zpmin_social = false;
				break;
			case '404.php':
				$zpmin_metatitle = gettext('404 Not Found...') . ' | ';
				$zpmin_social = false;
				break;
			default:
				$zpmin_metatitle = '';
				$zpmin_metadesc = truncate_string(getBareGalleryDesc(), 150, '...');
				break;
		}
		?>
		<meta name="description" content="<?php echo $zpmin_metadesc; ?>" />

		<!--[if lt IE 8]>
		<style type="text/css">.album-maxspace,.thumb-maxspace{zoom:1;display:inline;}#search{padding:2px 6px 6px 6px;}</style>
		<![endif]-->
		<?php if (extensionEnabled('rss')) {
			printRSSHeaderLink('Gallery', gettext('Gallery RSS'));
			printRSSHeaderLink("News", "", gettext('News RSS'), "");
		}
		?>

		<?php
		$zenpage = getOption('zp_plugin_zenpage');
		//$cb = getOption('zp_plugin_colorbox');
		if (!is_null(getOption('zpmin_finallink'))) {
			$zpmin_finallink = getOption('zpmin_finallink');
		} else {
			$zpmin_finallink = 'nolink';
		}
		if (!is_null(getOption('zpmin_zpsearchcount'))) {
			$zpmin_zpsearchcount = getOption('zpmin_zpsearchcount');
		} else {
			$zpmin_zpsearchcount = 2;
		}
		if (!is_null(getOption('zpmin_disablemeta'))) {
			$zpmin_disablemeta = getOption('zpmin_disablemeta');
		} else {
			$zpmin_disablemeta = false;
		}
		if (!is_null(getOption('zpmin_colorbox'))) {
			$zpmin_colorbox = getOption('zpmin_colorbox');
		} else {
			$zpmin_colorbox = true;
		}
		if (!is_null(getOption('zpmin_cbstyle'))) {
			$zpmin_cbstyle = getOption('zpmin_cbstyle');
		} else {
			$zpmin_cbstyle = 'style3';
		}
		if (!is_null(getOption('zpmin_logo'))) {
			$zpmin_logo = getOption('zpmin_logo');
		} else {
			$zpmin_logo = '';
		}
		if (!is_null(getOption('zpmin_menu'))) {
			$zpmin_menu = getOption('zpmin_menu');
		} else {
			$zpmin_menu = '';
		}
		if (!is_null(getOption('zpmin_switch'))) {
			$zpmin_switch = getOption('zpmin_switch');
		} else {
			$zpmin_switch = false;
		}
		$zpmin_img_thumb_size = getOption('thumb_size');
		if (is_numeric(getOption('zpmin_album_thumb_size'))) {
			$zpmin_album_thumb_size = getOption('zpmin_album_thumb_size');
		} else {
			$zpmin_album_thumb_size = 158;
		}
		$zpmin_thumb_crop = getOption('thumb_crop');
		$zpmin_img_thumb_maxspace_w = $zpmin_img_thumb_size + 2;
		$zpmin_img_thumb_maxspace_h = $zpmin_img_thumb_size + 2;
		$zpmin_album_thumb_maxspace_w = $zpmin_album_thumb_size + 2;
		$zpmin_album_thumb_maxspace_h = $zpmin_album_thumb_size + 17;
		$cblinks_top = ($zpmin_img_thumb_size / 2) - 8;
		?>
		<style type="text/css">
			.album-maxspace,.album-maxspace .thumb-link{
				width:<?php echo $zpmin_album_thumb_maxspace_w; ?>px;
				height:<?php echo $zpmin_album_thumb_maxspace_h; ?>px;
			}
			.thumb-maxspace,.thumb-maxspace .thumb-link{
				width:<?php echo $zpmin_img_thumb_maxspace_w; ?>px;
				height:<?php echo $zpmin_img_thumb_maxspace_h; ?>px;
			}
			.cblinks{top:<?php echo $cblinks_top; ?>px;}
		</style>
		<?php if (getOption('zp_plugin_reCaptcha')) { ?>
			<script type="text/javascript" charset="utf-8">
				var RecaptchaOptions = {
					theme: 'clean'
				};
			</script>
		<?php } ?>
		<?php if ((($zpmin_colorbox) || (($zpmin_finallink) == 'colorbox')) && ($cbscript)) { ?>
			<script src="<?php echo FULLWEBPATH . "/" . ZENFOLDER ?>/zp-extensions/colorbox_js/jquery.colorbox-min.js" type="text/javascript"></script>
			<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/css/cbStyles/<?php echo $zpmin_cbstyle; ?>/colorbox.css" type="text/css" media="screen"/>
			<script type="text/javascript">
				// <!-- <![CDATA[
				$(document).ready(function() {
					$("a.thickbox").colorbox({maxWidth: "90%", maxHeight: "90%", photo: true});
				});
				// ]]> -->
			</script>

		<?php } ?>
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<div id="wrapper">
			<div id="header"<?php
			if (!$showsearch) {
				echo ' style="text-align:center;"';
			}
			?>>
						 <?php if ($zpmin_logo) { ?>
					<div id="image-logo"><a href="<?php echo htmlspecialchars(getGalleryIndexURL()); ?>"><img src="<?php echo $_zp_themeroot; ?>/images/<?php echo $zpmin_logo; ?>" /></a></div>
				<?php } else { ?>
					<h1 id="logo"><a href="<?php echo htmlspecialchars(getGalleryIndexURL()); ?>"><?php echo getGalleryTitle(); ?></a></h1>
				<?php } ?>
				<?php if ($zpmin_social) { ?>
					<div id="social">
						<?php printAddThis(); ?>
					</div>
				<?php } ?>
				<?php
				if ($showsearch) {
					printSearchForm('', 'searchform', '', gettext('Search'), "$_zp_themeroot/images/drop.gif", null, null, "$_zp_themeroot/images/reset.gif");
				}
				?>
