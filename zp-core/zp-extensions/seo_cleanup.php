<?php
/**
 * SEO file/folder name clenser
 *
 * This plugin will scan your images and albums for file/folder names that are not <i>SEO friendly</i>.
 * It will rename those that found needing improvement replacing offending characters with friendly equivalents.
 *
 * Note: Clicking the button causes this process to execute. There is no <i>undo</i>.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
if (defined('OFFSET_PATH')) {
	$plugin_is_filter = 5|ADMIN_PLUGIN;
	$plugin_description = gettext("Provides a utility SEO file/folder name clenser.");
	$plugin_author = "Stephen Billard (sbillard)";

	zp_register_filter('admin_utilities_buttons', 'seo_cleanup_button');

	function seo_cleanup_button($buttons) {
		$buttons[] = array(
											'XSRFTag'=>'seo_cleanup',
											'category'=>gettext('Seo'),
											'enable'=>true,
											'button_text'=>gettext('SEO Cleaner'),
											'formname'=>'seo_cleanup.php',
											'action'=>WEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/seo_cleanup.php',
											'icon'=>'images/redo.png',
											'title'=>gettext('Make file and folder names in the Gallery SEO friendly.'),
											'alt'=>'',
											'hidden'=>'',
											'rights'=> ADMIN_RIGHTS
											);
		return $buttons;
	}

} else {

	define('OFFSET_PATH', 3);
	require_once(dirname(dirname(__FILE__)).'/admin-globals.php');

	admin_securityChecks(ALBUM_RIGHTS, currentRelativeURL());

	//XSRFdefender('seo_cleanup');

	function checkFolder($folder) {
		global $albums, $count, $albumcount;
		$files = scandir(ALBUM_FOLDER_SERVERPATH.'/'.$folder);
		$display = true;
		if (!empty($folder)) {
			$album = new Album(NULL, filesystemToInternal($folder));
		}
		foreach ($files as $file) {
			$file = str_replace('\\','/',$file);
			$key = str_replace(SERVERPATH.'/', '', $folder.'/'.$file);
			if (is_dir(ALBUM_FOLDER_SERVERPATH.$folder.'/'.$file) && $file!='..' && $file!='.') {
				if (empty($folder)) {
					$albumname = $file;
				} else {
					$albumname = $folder.'/'.$file;
				}
				checkFolder($albumname);
			} else {
				if (is_valid_image($file) || is_valid_other_type($file)) {
					$filename = internalToFilesystem($file);
					$seoname = seoFriendly($filename);
					if ($seoname != $filename) {
						$old = filesystemToInternal($file);
						$image = newImage($album, $old);
						if (!$e = $image->rename($seoname)) {
							if ($display) {
								echo '<p>'.filesystemToInternal($folder)."</p>\n";
								$display = false;
							}
							echo '&nbsp;&nbsp;';
							printf(gettext('<em>%1$s</em> renamed to <em>%2$s</em>'),$old,$seoname);
							echo "<br />\n";
							$count++;
							?>
							<script type="text/javascript">
							<!--
								imagecount = <?php echo $count; ?>;
							//-->
							</script>
							<?php
						}
					}
				}
			}
		}
		if (!empty($folder)) {
			$albumname = internalToFilesystem($folder);
			$file = basename($albumname);
			$seoname = seoFriendly($file);
			if ($seoname != $file) {
				$newname = dirname($albumname);
				if (empty($newname) || $newname == '.') {
					$newname = $seoname;
				} else {
					$newname .= '/'.$seoname;
				}
				if (!$album->rename($newname)) {
					printf(gettext('<em>%1$s</em> renamed to <em>%2$s</em>'),$albumname,$newname);
					echo "<br />\n";
					$albumcount++;
					?>
					<script type="text/javascript">
					<!--
						albumcount = <?php echo $albumcount; ?>;
					//-->
					</script>
					<?php
				}
			}
		}
	}

	$zenphoto_tabs['overview']['subtabs']=array(gettext('SEO cleaner')=>'');
	printAdminHeader('overview','SEO cleaner');

	if (isset($_GET['todo'])) {
		$count = sanitize_numeric($_GET['imagecount']);
		$albumcount = sanitize_numeric($_GET['albumcount']);
		$albums = array();
		foreach (explode(',', sanitize(sanitize($_GET['todo']))) as $album) {
			$albums[] = sanitize($album);
		}
	} else {
		$count = 0;
		$albumcount = 0;
		$albums = $_zp_gallery->getAlbums();
	}

	?>
	<script type="text/javascript">
	<!--
		var albumcount = 0;
		var imagecount = 0;
		var albumspending = [<?php
													$c = 0;
													foreach ($albums as $key=>$album) {
														if (hasDynamicAlbumSuffix($album)) {
															unset($albums[$key]);
														} else {
															if ($c) echo ',';
															echo "'".$album."'";
															$c++;
														}
													}
												?>];
		function reStart() {
			var datum = '?imagecount='+imagecount+'&albumcount='+albumcount+'&todo='+albumspending.join(',')+'&XSRFToken=<?php echo getXSRFToken('seo_cleanup')?>';
			window.location = 'seo_cleanup.php'+datum;
		}
	//-->
	</script>
	<?php echo '</head>'; ?>
	<body>
		<?php printLogoAndLinks(); ?>
		<div id="main">
			<?php printTabs(); ?>
			<div id="content">
			<?php printSubtabs('thumbs'); ?>
			<div class="tabbox">
			<?php zp_apply_filter('admin_note','seo_cleanup', ''); ?>
			<h1><?php echo gettext('Cleanup album and image names to be SEO friendly'); ?></h1>
			<div id="to_clean">
				<?php echo gettext('Albums to clean:'); ?>
				<ul>
				<?php
				foreach ($albums as $key=>$album) {
					?>
					<li id="li_<?php echo $album; ?>"><?php echo $album; ?></li>
					<?php
				}
				?>
				</ul>
				<?php echo gettext('If this script does not complete, <a href="javascript:reStart();" title="restart">click here</a>'); ?>
			</div>
			<?php
			foreach ($albums as $album) {
				checkFolder(internalToFilesystem($album));
				?>
				<script type="text/javascript">
				<!--
					albumspending = jQuery.grep(albumspending, function(value) {
						return value != '<?php echo $album; ?>';
					});
					$('#li_<?php echo $album; ?>').remove();
				//-->
				</script>
				<?php
			}
			?>
			<script type="text/javascript">
				<?php
				if ($count) {
					$imagecleaned = sprintf(ngettext('%u image name cleaned.','%u images names cleaned.',$count),$count);
				} else {
					$imagecleaned = gettext('No image names needed cleaning.');
				}
				if ($albumcount) {
					$albumcleaned = sprintf(ngettext('%u album folder name cleaned.','%u albums folder names cleaned.',$albumcount),$albumcount);
				} else {
					$albumcleaned = gettext('No album folder names needed cleaning.');
				}
				?>
				$('#to_clean').html('<?php echo $imagecleaned; ?><br /><?php echo $albumcleaned; ?>');
			</script>
			</div>
			</div><!-- content -->
	</div><!-- main -->
	<?php printAdminFooter(); ?>
	</body>
	<?php
	echo "</html>";
}
?>