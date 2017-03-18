<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/css/reset.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/css/text.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/css/1200_15_col.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="<?php echo $_zp_themeroot ?>/css/theme.css" type="text/css" media="screen" />
	</head>
	<body>
		<?php zp_apply_filter('theme_body_open'); ?>
		<div class="container_15">
			<div id="header" class="grid_15">
				<?php
				if (function_exists('printLanguageSelector')) {
					echo '<div class="languages grid_5">';
					printLanguageSelector(true);
					echo '</div>';
				}
				?>
				<?php printLoginZone(); ?>
				<h1><?php echo html_encode(getBareGalleryTitle()); ?></h1>
			</div>
			<div class="clear"></div>
			<div id="menu">
				<div id="m_bread" class="grid_8">
					<a href="<?php echo html_encode(getGalleryIndexURL()); ?>" title="<?php echo getGalleryTitle(); ?>"><?php echo getGalleryTitle(); ?></a>
					<?php printParentBreadcrumb('', '', ''); ?>
					<a href="<?php echo html_encode(getAlbumURL()); ?>"><?php echo getAlbumTitle(); ?></a>
					<span class="current"><?php echo getImageTitle(); ?></span>
				</div>
				<?php printMenu(); ?>
			</div>
			<div class="clear"></div>
			<div id="content">
				<div class="desc grid_5">
					<h2 class="suffix_1"><?php echo getImageTitle(); ?></h2>
					<div class="date"><?php echo getImageDate('%d/%d/%Y'); ?></div>
					<?php
					if (function_exists('printRating')) {
						echo '<div id="star_rating_images">';
						printRating();
						echo '</div>';
					}
					?>
					<div class="comment"><?php echo getImageDesc(); ?></div>
					<div class="data">
						<?php if (getImageLocation()) { ?><div><label><?php echo gettext('Location:'); ?> </label><?php echo getImageLocation(); ?></div><?php } ?>
						<?php if (getImageCity()) { ?><div><label><?php echo gettext('City:'); ?> </label><?php echo getImageCity(); ?></div><?php } ?>
						<?php if (getImageState()) { ?><div><label><?php echo gettext('State:'); ?> </label><?php echo getImageState(); ?></div><?php } ?>
						<?php if (getImageCountry()) { ?><div><label><?php echo gettext('Country:'); ?> </label><?php echo getImageCountry(); ?></div><?php } ?>
						<?php if (getImageData('credit')) { ?><div><label><?php echo gettext('Credit:'); ?> </label><?php echo getImageData('credit'); ?></div><?php } ?>
						<?php if (getImageData('copyright')) { ?><div><label><?php echo gettext('Copyright:'); ?> </label><?php echo getImageData('copyright'); ?></div><?php } ?>
						<?php printImageMetaData(); ?>
					</div>
				</div>
				<div class="image suffix_5">
					<?php $linkImage = getFullImageURL(); ?>
					<?php
					if (!empty($linkImage)) {
						echo '<a href="' . getFullImageURL() . '" alt="' . getImageTitle() . '">';
					}

					$iL = isLandscape($_zp_current_image);
					if ($iL) {
						setOption('image_size', 776, false);
					} else {
						setOption('image_size', 456, false);
					}
					setOption('image_use_side', 'longest', false);
					printDefaultSizedImage(getImageTitle());

					if (!empty($linkImage)) {
						echo '</a>';
					}
					?>
				</div>
				<div class="clear"></div>
				<div class="imgnav">
					<?php
					if (hasPrevImage()) {
						?>
						<div class="imgprevious"><a href="<?php echo html_encode(getPrevImageURL()); ?>" title="<?php echo gettext("Previous Image"); ?>">« <?php echo gettext("prev"); ?></a></div>
						<?php
					} if (hasNextImage()) {
						?>
						<div class="imgnext"><a href="<?php echo html_encode(getNextImageURL()); ?>" title="<?php echo gettext("Next Image"); ?>"><?php echo gettext("next"); ?> »</a></div>
						<?php
					}
					?>
				</div>
				<div class="clear"></div>
			</div>
			<div id="footer" class="grid_15">
				<?php printFooter(); ?>
			</div>
		</div>
		<?php zp_apply_filter('theme_body_close'); ?>
	</body>
</html>
