<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
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

			<strong><?php echo gettext("A password is required for the page you requested"); ?></strong>
				
			<p><?php printPasswordForm($hint, $show); ?></p>
							
			<?php
				if (!zp_loggedin() && function_exists('printRegisterURL') && $_zp_gallery->isUnprotectedPage('register')) {
					printRegisterURL(gettext('Register for this site'), '<br />');
				echo '<br />';
				}
			?>
				
			</section>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_sidebar.php'); ?>
		</div>
	</div>
</div>

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_footer.php'); ?>