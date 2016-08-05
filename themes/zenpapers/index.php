<?php
// force UTF-8 Ø

if (!defined('WEBPATH'))
	die();
?>
<!DOCTYPE html>
<html>
	<head>
		<?php zp_apply_filter('theme_head'); ?>
		<title><?php
			printBareGalleryTitle();
			if ($_zp_page > 1)
				echo "[$_zp_page]";
			?></title>
		<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
		<link rel="stylesheet" href="<?php echo pathurlencode($zenCSS); ?>" type="text/css" />
		<link rel="stylesheet" href="<?php echo WEBPATH . '/' . THEMEFOLDER; ?>/zenpapers/common.css" type="text/css" />
<?php if (class_exists('RSS')) printRSSHeaderLink('Gallery', gettext('Gallery RSS')); ?>
	</head>
	<body>
<?php zp_apply_filter('theme_body_open'); ?>

	<p id="path">
		<?php printHomeLink('', ' > '); ?>
		<?php printGalleryTitle(); ?>
		</p>    
        
		<div id="main">
			<div id="gallerytitle">
				<?php
				if (getOption('Allow_search')) {
					printSearchForm('');
				}
				?>

			</div>
			<div id="padbox">
				<div id="albums">
<?php while (next_album()): ?>
						<div class="album">
							<div class="thumb">
                            <a href="<?php echo html_encode(getAlbumLinkURL());?>" title="<?php printf(gettext("View album: %s"), html_encode(getAnnotatedAlbumTitle()));?>" class="img"><?php printCustomAlbumThumbImage(getAnnotatedAlbumTitle(), null, 144, 44 ,144,44); ?></a></div>
							<div class="albumdesc">
								<h3><a href="<?php echo html_encode(getAlbumLinkURL()); ?>" title="<?php echo gettext('View album:'); ?> <?php printAnnotatedAlbumTitle(); ?>"><?php printAlbumTitle(); ?></a></h3>
								<small>					<?php
						$anumber = getNumAlbums();
						$inumber = getNumImages();
						if ($anumber > 0 || $inumber > 0) {
							echo '<p><em>(';
							if ($anumber == 0) {
								if ($inumber != 0) {
									printf(ngettext('%u image','%u images', $inumber), $inumber);
								}
							} else if ($anumber == 1) {
								if ($inumber > 0) {
									printf(ngettext('1 album,&nbsp;%u image','1 album,&nbsp;%u images', $inumber), $inumber);
								} else {
									printf(gettext('1 album'));
								}
							} else {
								if ($inumber == 1) {
									printf(ngettext('%u album,&nbsp;1 image','%u albums,&nbsp;1 image', $anumber), $anumber);
								} else if ($inumber > 0) {
									printf(ngettext('%1$u album,&nbsp;%2$s','%1$u albums,&nbsp;%2$s', $anumber), $anumber, sprintf(ngettext('%u image','%u images',$inumber),$inumber));
								} else {
									printf(ngettext('%u album','%u albums', $anumber), $anumber);
								}
							}
							echo ')</em><br />';
						}
						echo shortenContent(strip_tags(getAlbumDesc()), 50, '...');
					?>
					</p></small>
								<div><?php printAlbumDesc(); ?></div>
							</div>
							<p style="clear: both; "></p>
						</div>
<?php endwhile; ?>
				</div>
				<br class="clearall" />
<?php printPageListWithNav("« " . gettext("prev"), gettext("next") . " »"); ?>

<div id="credit">
			<?php @call_user_func('printUserLogin_out', '', ' | '); ?>
			<?php if (class_exists('RSS')) printRSSLink('Gallery', '', 'RSS', ' | '); ?>
			
			<?php
			if (extensionEnabled('contact_form')) {
				printCustomPageURL(gettext('Contact us'), 'contact', '', '', ' | ');
			}
			?>
			<?php
			if (!zp_loggedin() && function_exists('printRegistrationForm')) {
				printCustomPageURL(gettext('Register for this site'), 'register', '', '', ' | ');
			}
			?>
			<?php
			if (function_exists('printFavoritesLink')) {
				printFavoritesLink();
				?> | <?php
			}
			?>
            <?php printCustomPageURL(gettext("Archive View"), "archive"); ?> |
            ZenPapers Template by <a href="http://animepapers.org">Anime Papers |</a>
		<?php printZenphotoLink(); ?>
		</div>
			</div>
		</div>
        
        
        		<div id="secondary">
			<div class="module">
				<h2><?php echo gettext('Description'); ?></h2>
				<?php printGalleryDesc(); ?>
			</div>
			<div class="module">
				<?php $selector = getOption('Mini_slide_selector'); ?>
				<ul id="randomlist">
					<?php
					
							if (function_exists('getImageStatistic')) {
								echo '<h2>'.gettext('Recent images').'</h2>';
								$images = getImageStatistic(12, "latest");
								$c = 0;
								foreach ($images as $image) {
									if (is_valid_image($image->filename)) {
										if ($c++ < 6) {
											echo "<li><table><tr><td>\n";
											$imageURL = html_encode(getURL($image));
											if ($image->getWidth() >= $image->getHeight()) {
												$iw = 44;
												$ih = NULL;
												$cw = 44;
												$ch = 33;
											} else {
												$iw = NULL;
												$ih = 44;
												$ch = 44;
												$cw = 33;
											}
											echo '<a href="'.$imageURL.'" title="'.gettext("View image:").' '.
											html_encode($image->getTitle()) . '"><img src="' .
											pathurlencode($image->getCustomImage(NULL, $iw, $ih, $cw, $ch, NULL, NULL, true)) .
																		'" alt="' . html_encode($image->getTitle()) . "\"/></a>\n";
											echo "</td></tr></table></li>\n";
										}
									}
								}
							}												
					?>
				</ul>
			</div>
			<div class="module">
				<h2><?php echo gettext("Gallery data"); ?></h2>
				<table cellspacing="0" class="moduledata">
						<tr>
							<th><?php echo gettext('Series'); ?></th>
							<td>			<?php
			$t = $_zp_gallery->getNumAlbums(true);
			$c = $t-$_zp_gallery->getNumAlbums(true,true);
				printf(ngettext('%u', '%u',$t),$t);
			
			?></td>
							<td></td>
						</tr>
						<tr>
							<th><?php echo gettext('Images'); ?></th>
							<td><?php $photosNumber = db_count('images'); echo $photosNumber ?></td>
							<td><?php if (class_exists('RSS')) printRSSLink('Gallery','','','',true,'i'); ?></td>
						</tr>
					<?php if (function_exists('printCommentForm')) { ?>
						<tr>
							<th><?php echo gettext('Comments'); ?></th>
							<td><?php $commentsNumber = db_count('comments'," WHERE inmoderation = 0"); echo $commentsNumber ?></td>
							<td><?php if (class_exists('RSS')) printRSSLink('Comments','','','',true,'i'); ?></td>
							</tr>
						<?php } ?>
				</table>
			</div>
		</div>
	</div>
    
    
    
		
		<?php @call_user_func('mobileTheme::controlLink'); ?>
		<?php @call_user_func('printLanguageSelector'); ?>
		<?php
		zp_apply_filter('theme_body_close');
		?>
	</body>
</html>