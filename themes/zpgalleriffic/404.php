<?php include ("header.php"); ?>
	
	<div class="wrapper" id="breadcrumbs">
		<div class="centered">
			<div class="breadcrumbs">
				<?php printHomeLink('', ' » '); ?><?php echo gettext("Page not found..."); ?>
				<br /><h4>
					<?php echo gettext("The page you are requesting cannot be found.");
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
				</h4><br />
			</div>
		</div>
	</div>
		
<?php include("footer.php"); ?>
