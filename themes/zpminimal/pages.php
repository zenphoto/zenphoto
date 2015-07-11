<?php include ("inc-header.php"); ?>
			
				<div id="breadcrumbs">
					<h2><a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Home'); ?>"><?php echo gettext('Home'); ?></a> &raquo; <a href="<?php echo getCustomPageURL('gallery'); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo gettext('Gallery Index'); ?></a> &raquo; <?php printZenpageItemsBreadcrumb(""," » "); ?><?php printPageTitle(); ?></h2>
				</div>
			</div> <!-- close #header -->
			<div id="content">
				<div id="main"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
					<div id="post">
						<h1><?php printPageTitle(); ?></h1>
						<?php printPageContent(); printCodeblock(1); ?>
					</div>
					<?php if (function_exists('printCommentForm')) { ?><div class="section"><?php printCommentForm(); ?></div><?php } ?>
				</div>
				<div id="sidebar"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
					<?php if (getPageExtraContent()) { ?>
					<div class="sidebar-divide">
						<div class="extra-content"><?php printPageExtraContent(); ?></div>
					</div>
					<?php } ?>
					<?php include ("inc-sidemenu.php"); ?>
					<?php if (function_exists('printCommentForm')) { ?>
					<div class="latest">
						<?php if ($zenpage) printLatestComments(2); ?>
						<?php printLatestComments(2); ?>
					</div>
					<?php } ?>
				</div>
			</div>

<?php include ("inc-footer.php"); ?>	