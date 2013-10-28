<?php
if (!defined('WEBPATH')) die();

?>
<!DOCTYPE html>
<html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php printHeadTitle(); ?>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo $_zp_themeroot ?>/css/master.css" />
</head>

<body class="gallery">
	<?php zp_apply_filter('theme_body_open'); ?>
	<?php printGalleryTitle(); ?>

	<div id="content">

		<div class="galleryinfo">
		<?php
		  echo "<h1><em>". gettext('Object not found'). "</em></h1>";
		?>
		</div>

	<div class="galleryinfo">
		<?php
		echo gettext("The Zenphoto object you are requesting cannot be found.").'<br />';
		if (isset($album)) {
			echo '<br />'.sprintf(gettext('Album: %s'),html_encode($album));
		}
		if (isset($image)) {
			echo '<br />'.sprintf(gettext('Image: %s'),html_encode($image));
		}
		if (isset($obj)) {
			echo '<br />'.sprintf(gettext('Page: %s'),html_encode(substr(basename($obj),0,-4)));
		}
		?>

	</div>
	</div>

	<p id="path">
		<?php printHomeLink('', ' > '); ?>
		<a href="<?php echo html_encode(getGalleryIndexURL(false));?>" title="<?php echo gettext('Main Index'); ?>"><?php echo gettext('Home');?></a> &gt;
		<a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Albums Index'); ?>">
		<?php printGalleryTitle();?></a> &gt;
		<?php
		echo "<em>".gettext('Object not found')."</em>";
		?>
	</p>

	<div id="footer">
		<hr />
		<p>
		<?php printZenphotoLink(); ?>
		</p>
	</div>
	<?php
	zp_apply_filter('theme_body_close');
	?>
</body>
</html>
