<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
if (function_exists('printRegistrationForm')) {	
?>

<!DOCTYPE html>

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_head.php'); ?>
<meta name="robots" content="noindex, nofollow">
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_header.php'); ?>

<div id="background-main" class="background">
	<div class="container<?php if (getOption('full_width')) {echo '-fluid';}?>">
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_breadcrumbs.php'); ?>

		<div id="center" class="row">

			<section class="col-sm-9" id="main">
				
			<h1><?php echo gettext('User Registration') ?></h1>
			
			<?php printRegistrationForm(); ?>
			
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