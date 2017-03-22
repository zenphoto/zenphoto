<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_head.php'); ?>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_header.php'); ?>

<div id="background-main" class="background">
	<div class="container<?php if (getOption('full_width')) {echo '-fluid';}?>">
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_breadcrumbs.php'); ?>

		<div id="center" class="row" itemscope itemtype="http://schema.org/WebPage">

			<section class="col-sm-9" id="main" itemprop="mainContentOfPage">

			<h1><?php echo gettext("Object not found"); ?></h1>
				
			<p><?php print404status(isset($album) ? $album : NULL, isset($image) ? $image : NULL, $obj); ?></p>

			</section>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_sidebar.php'); ?>
		</div>
	</div>
</div>		

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_footer.php'); ?>