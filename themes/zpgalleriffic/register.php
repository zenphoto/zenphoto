<?php include ("header.php"); ?>
	
	<div class="wrapper" id="breadcrumbs">
		<div class="centered">
			<div class="breadcrumbs">
				<div>
					<span><?php printHomeLink('', ' » '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php gettext('Albums Index'); ?>"><?php echo gettext('Home'); ?></a> &raquo; <?php echo gettext('User Registration'); ?></span>
				</div>
			</div>
		</div>
	</div>
	<div class="wrapper">
		<div class="centered">
			<div id="post">
				<h2><?php echo gettext('User Registration') ?></h2>
				<?php printRegistrationForm(); ?>
			</div>
		</div>
	</div>
			
<?php include("footer.php"); ?>
