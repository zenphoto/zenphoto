<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
if (function_exists('printContactForm')) {
	?>
<!DOCTYPE html>

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_head.php'); ?>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_header.php'); ?>

<div id="background-main" class="background">
	<div class="container<?php if (getOption('full_width')) {echo '-fluid';}?>">
	<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_breadcrumbs.php'); ?>
		<div id="center" class="row" itemscope itemtype="http://schema.org/ContactPage">
			<section class="col-sm-9" id="main" itemprop="mainContentOfPage">

			<h1><?php echo gettext('Contact us') ?></h1>
				
			<p>						
				<?php
						printContactForm();
				?>
			</p>
				
			</section>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_sidebar.php'); ?>
		</div>
	</div>
</div>		

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_footer.php'); ?>
	<?php
} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
}
	?>