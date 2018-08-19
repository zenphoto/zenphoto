<?php include('inc_header.php'); ?>

	<!-- .container main -->
		<!-- .page-header -->
			<!-- .header -->
				<h3><?php printGalleryTitle(); ?> &raquo; <?php echo gettext("Object not found"); ?></h3>
			</div><!-- .header -->
		</div><!-- /.page-header -->

		<h4>
			<?php print404status(isset($album) ? $album : NULL, isset($image) ? $image : NULL, $obj); ?>
		</h4>

	</div><!-- /.container main -->

<?php include('inc_footer.php'); ?>