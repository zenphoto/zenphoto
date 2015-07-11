<?php include ("inc-header.php"); ?>

				<div id="breadcrumbs">
					<h2><a href="<?php echo html_encode(getGalleryIndexURL());?>" title="<?php echo gettext('Home'); ?>"><?php echo gettext('Home'); ?></a> &raquo; <a href="<?php echo getCustomPageURL('gallery'); ?>" title="<?php echo gettext('Gallery Index'); ?>"><?php echo gettext('Gallery Index'); ?></a> &raquo;  <?php echo gettext('Login'); ?></h2>
				</div>
			</div> <!-- close #header -->
			<div id="content">
				<div id="main"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
					<div id="random-image">
						<?php printRandomImages(1,null,'all','',190,225,true); ?>
					</div>
					<?php printUserLogin_out('','',true); ?>
				</div>
				<div id="sidebar"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
					<div class="sidebar-divide">
						<?php printGalleryDesc(true); ?>
					</div>
					<?php include ("inc-sidemenu.php"); ?>
				</div>
			</div>

<?php include ("inc-footer.php"); ?>			

