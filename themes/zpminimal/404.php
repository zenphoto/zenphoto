<?php include ("inc-header.php"); ?>

			</div> <!-- close #header -->
			<div id="content">
				<div id="main"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
					<div id="random-image">
						<?php printRandomImages(1,null,'all','',190,225,true); ?>
					</div>
					<p><?php echo gettext("The page you are requesting cannot be found."); ?></p>
					<h3>
					<?php
					if (isset($album)) {
						echo '<br />'.sprintf(gettext('Album: %s'),sanitize($album));
					}
					if (isset($image)) {
						echo '<br />'.sprintf(gettext('Image: %s'),sanitize($image));
					}
					if (isset($obj)) {
						echo '<br />'.sprintf(gettext('Page: %s'),substr(basename($obj),0,-4));
					}
					?>
					</h3><br />
					<div id="enter">
						<a href="<?php echo getCustomPageURL('gallery'); ?>" title="<?php echo gettext('Gallery index'); ?>"><?php echo gettext('Back to Gallery Index →'); ?></a>
					</div>
				</div>
				<div id="sidebar"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
					<div class="sidebar-divide">
						<?php printGalleryDesc(true); ?>
					</div>
					<?php include ("inc-sidemenu.php"); ?>
				</div>
			</div>

<?php include ("inc-footer.php"); ?>			
