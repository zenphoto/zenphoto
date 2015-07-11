<?php
if (!$_zenpage_enabled) die();
include('inc_header.php');
?>

	<!-- wrap -->
		<!-- container -->
			<!-- header -->
				<h3><?php printPageTitle(); ?></h3>
			</div> <!-- /header -->

			<div class="row">
				<div class="span9">
					<div class="post clearfix">
						<?php printPageContent(); ?>
						<?php printCodeblock(1); ?>
					</div>
				</div>

				<div class="span3">
					<?php printPageMenu('omit-top', 'news-cat-list', 'active', 'nav sub-nav nav-pills nav-stacked', 'active'); ?>
					<?php if (getPageExtraContent()) { ?>
					<div class="extra-content clearfix">
						<?php printPageExtraContent(); ?>
					</div>
					<?php } ?>
				</div>
			</div>

			<?php if (extensionEnabled('comment_form')) { ?>
				<?php include('inc_print_comment.php'); ?>
			<?php } ?>

<?php include('inc_footer.php'); ?>