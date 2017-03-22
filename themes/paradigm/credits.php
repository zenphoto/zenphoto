<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_head.php'); ?>
<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_header.php'); ?>

<div id="background-main" class="background">
	<div class="container<?php
	if (getOption('full_width')) {
		echo '-fluid';
	}
	?>">
				 <?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_breadcrumbs.php'); ?>
		<div id="center" class="row" itemscope itemtype="http://schema.org/ContactPage">

			<section class="col-sm-9" id="main" itemprop="mainContentOfPage">

				<h1 itemprop="name"><?php echo gettext('Credits'); ?></h1>
				<h2>Copyright</h2>
				Copyright
				<?php
				$admin = $_zp_authority->getMasterUser();
				$author = $admin->getName();
				echo $author . ' ';
				?>
				<?php echo date('Y'); ?>.

				<h2>ZenPhoto20</h2>
				<p><?php echo gettext('This website is based on ZenPhoto20 the <a href="http://zenphoto20.us/" target="_blank">simple media website CMS</a>'); ?>.</p>
				<p><?php echo gettext('Theme used:'); ?> Paradigm <?php echo gettext('by'); ?> Olivier Ffrench (<a href="http://www.france-in-photos.com">France in Photos</a>).</p>
			</section>
			<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_sidebar.php'); ?>
		</div>
	</div>
</div>

<?php include(SERVERPATH . '/' . THEMEFOLDER . '/paradigm/includes/_footer.php'); ?>
