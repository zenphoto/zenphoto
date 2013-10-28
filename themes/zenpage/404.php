<?php

// force UTF-8 Ø

if (!defined('WEBPATH')) die();

?>
<!DOCTYPE html>
<html>
<head>
	<?php zp_apply_filter('theme_head'); ?>
	<?php printHeadTitle(); ?>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<link rel="stylesheet" href="<?php echo $_zp_themeroot; ?>/style.css" type="text/css" />
</head>

<body>
<?php zp_apply_filter('theme_body_open'); ?>

<div id="main">

	<div id="header">
		<h1><?php printGalleryTitle();?></h1>
		</div>

		<div id="content">
		<div id="breadcrumb">
	<h2><a href="<?php echo getGalleryIndexURL(); ?>">Index</a> » <strong><?php echo gettext("Object not found"); ?></strong></h2>
	</div>

	<div id="content-error">

		<div class="errorbox">
 		<?php
		echo gettext("The Zenphoto object you are requesting cannot be found.");
		if (isset($album)) {
			echo '<br />'.gettext("Album").': '.html_encode($album);
		}
		if (isset($image)) {
			echo '<br />'.gettext("Image").': '.html_encode($image);
		}
		if (isset($obj)) {
			echo '<br />'.gettext("Theme page").': '.html_encode(substr(basename($obj),0,-4));
		}
 		?>
		</div>

</div>



<div id="footer">
<?php include("footer.php"); ?>
</div>



</div><!-- content -->

</div><!-- main -->
<?php
zp_apply_filter('theme_body_close');
?>
</body>
</html>
