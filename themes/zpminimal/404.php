<?php include ("inc-header.php"); ?>

</div> <!-- close #header -->
<div id="content">
	<div id="main"<?php if ($zpmin_switch) echo ' class="switch"'; ?>>
		<div id="random-image">
			<?php printRandomImages(1, null, 'all', '', 190, 225, true); ?>
		</div>
		<div class="errorbox">
			<?php print404status(); ?>
		</div>
		<br />
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
