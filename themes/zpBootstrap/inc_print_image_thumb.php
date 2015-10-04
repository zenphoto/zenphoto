<div class="image-wrap">
	<ul class="thumbnails">
		<?php while (next_image()) { ?>
			<li class="span3 image-thumb">
				<a class="thumbnail" rel="tooltip" href="<?php echo html_encode(getImageURL()); ?>" title="<?php echo html_encode(getBareImageTitle()); ?>">
					<?php printImageThumb(getAnnotatedImageTitle(), 'remove-attributes'); ?>
				</a>
			</li>
		<?php } ?>
	</ul>
	<script type="text/javascript">
		$('.thumbnail').tooltip({
			placement: 'bottom'
		});
	</script>
</div>