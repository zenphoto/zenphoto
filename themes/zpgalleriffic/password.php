<?php setOption('zp_plugin_colorbox',false,false); ?>
<?php include ("header.php"); ?>

	<div class="wrapper" id="breadcrumbs">
		<div class="centered">
			<div class="breadcrumbs">
				<div>
					<span><?php printHomeLink('', ' » '); ?><a href="<?php echo htmlspecialchars(getGalleryIndexURL());?>" title="<?php gettext('Albums Index'); ?>"><?php echo gettext('Home'); ?></a> &raquo; <?php echo gettext("Password Required..."); ?></span>
				</div>
			</div>
		</div>
	</div>
	
	<div class="wrapper">
		<div class="centered">
			<div class="post">
				<?php if (!zp_loggedin()) { ?>
				<div class="error"><?php echo gettext("Please Login"); ?></div>	
				<?php printPasswordForm($hint); ?>
				<?php } else { ?>
				<div class="errorbox">
					<p><?php echo gettext('You are logged in...'); ?></p>
				</div>
				<?php } ?>

				<?php if (!zp_loggedin() && function_exists('printRegistrationForm') && $_zp_gallery->isUnprotectedPage('register')) {
					printCustomPageURL(gettext('Register for this site'), 'register', '', '<br />');
					echo '<br />';
				}?>
			</div>
		</div>	
	</div>
		
<?php include("footer.php"); ?>

