<?php include ("header.php"); ?>

	<div class="wrapper" id="breadcrumbs">
		<div class="centered">
			<div class="breadcrumbs">
				<div>
					<span><?php printHomeLink('', ' » '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php gettext('Albums Index'); ?>"><?php echo gettext('Home'); ?></a> &raquo; <?php echo gettext('Contact'); ?></span>
				</div>
			</div>
		</div>
	</div>
	
	<div class="wrapper">
		<div class="centered">
			<div class="post">
				<?php if (function_exists('printContactForm')) { printContactForm(); } else { ?> 
				<p><?php echo gettext('The Contact Form plugin has not been activated.'); ?></p>
				<?php } ?>
			</div>
		</div>	
	</div>
		
<?php include("footer.php"); ?>