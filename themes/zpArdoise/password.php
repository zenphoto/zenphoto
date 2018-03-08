<?php include ('inc_header.php'); ?>

<div id="post">

	<div id="headline" class="clearfix">
		<h3><?php echo gettext('Password required'); ?></h3>
	</div>

	<div class="post">
		<div style='display: none;'>
			<?php printPasswordForm(isset($hint) ? $hint : NULL, isset($show) ? $show : TRUE, false, isset($hint) ? WEBPATH : NULL); ?>
		</div>
	</div>
	<script type="text/javascript">
		//<![CDATA[
		$(document).ready(function () {
			$.colorbox({
				inline: true,
				href: "#passwordform",
				innerWidth: "400px",
				close: '<?php echo gettext("close"); ?>',
				open: true
			});
		});
		//]]>
	</script>

</div>

<?php include('inc_footer.php'); ?>