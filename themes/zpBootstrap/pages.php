<?php
if (!$_zenpage_enabled) die();
include('inc_header.php');
?>

	<!-- .container main -->
		<!-- .page-header -->
			<!-- .header -->
				<h3><?php printPageTitle(); ?></h3>
			</div><!-- .header -->
		</div><!-- /.page-header -->

		<?php if (getNumPages()) { ?>
		<div class="row margin-bottom ">
			<div class="col-sm-offset-1 col-sm-10">
				<?php printPageMenu('list-sub', '', '', 'pages-list nav nav-pills', 'active'); ?>
			</div>
		</div>
		<?php } ?>

		<?php if (getPageExtraContent()) { ?>
		<div class="row">
			<div class="col-sm-9">
				<div class="post clearfix">
					<?php printPageContent(); ?>
					<?php printCodeblock(1); ?>
				</div><!--/.post -->
			</div>
			<div class="col-sm-3">
				<div class="extra-content clearfix">
					<?php printPageExtraContent(); ?>
				</div>
			</div>
		</div>
		<?php } else { ?>
		<div class="row">
			<div class="col-sm-12">
				<div class="post clearfix">
					<?php printPageContent(); ?>
					<?php printCodeblock(1); ?>
				</div><!--/.post -->
			</div>
		</div>
		<?php } ?>
		

		<?php if (extensionEnabled('comment_form')) { ?>
			<?php include('inc_print_comment.php'); ?>
		<?php } ?>

	</div><!-- /.container main -->

<?php include('inc_footer.php'); ?>