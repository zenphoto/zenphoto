<?php include ("inc-header.php"); ?>
	<div class="wrapper contrast top">
		<div class="container">	
			<div class="sixteen columns">
				<?php include ("inc-search.php"); ?>
				<h1><?php echo gettext('Contact us') ?></h1>
			</div>
		</div>
	</div>
	<div class="wrapper">
		<div class="container">
			<div class="ten columns">
				<?php printContactForm(); ?>
			</div>
			<?php if (!$zpskel_ismobile) { ?>
			<div class="five columns offset-by-one noshow-mobile">
				<?php printRandomImages(1,'scale-with-grid','all','',420,420,true); ?>
			</div>
			<?php } ?>
		</div>
	</div>
<?php include ("inc-bottom.php"); ?>
<?php include ("inc-footer.php"); ?>