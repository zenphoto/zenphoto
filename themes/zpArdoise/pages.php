<?php 
if ($_zenpage_enabled) {
	include ('inc_header.php');
?>

		<div id="post" class="clearfix">
			<h3><?php printPageTitle(); ?></h3>
			<?php if (getPageExtraContent()) { ?>
			<div class="extra-content">
				<?php printPageExtraContent(); ?>
			</div>
			<?php } ?>

			<?php printPageContent(); ?>
			<?php printCodeblock(1); ?>
		</div>

		<?php if (extensionEnabled('comment_form')) { ?>
			<?php include('inc_print_comment.php'); ?>
		<?php } ?>

<?php
	include('inc_footer.php');

} else {
	include(SERVERPATH . '/' . ZENFOLDER . '/404.php');
} ?>