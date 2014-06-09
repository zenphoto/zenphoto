<?php
/**
 *
 * Removes the specific image and album HTML from the DOM if the user does not have <var>ADMIN_RIGHTS</var>
 *
 * This instance of the plugin will disable the image/album "publish" action. But it is intended mostly as an example.
 * To disable other actions you will need to examine the page HTML and change/insert code as needed.
 *
 * @author Stephen Billard (sbillard)
 * 
 * @package plugins
 * @subpackage example
 * @category package
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext("Disable publish/unpublish if user does not have <em>ADMIN_RIGHTS</em>.");
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_note', 'disableRight::disable'); // a convenient point since it is established what page and tab are selected
zp_register_filter('admin_managed_albums_access', 'disableRight::save'); // this point allows us to alter the $_GET and $_POST arrays before they are used
zp_register_filter('plugin_tabs', 'disableRight::tab');

class disableRight {

	/**
	 * removes the HTML for the action we wish to disable.
	 * @param string $tab
	 * @param string $subtab
	 */
	static function disable($tab, $subtab) {
		global $_zp_admin_tab;
		if (!zp_loggedin(ADMIN_RIGHTS)) {
			switch ($_zp_admin_tab) {
				case 'upload':
					//	the upload tab.
					?>
					<script type="text/javascript">
						// <!-- <![CDATA[
						$(window).load(function() {
							//	disable the checkbox for publishing the album so it stays at its initial state
							$('#publishalbum').attr('disabled', 'disabled');
						});
						// ]]> -->
					</script>
					<?php
					break;

				case 'edit':
					// the album and image tabs. What we do depends on the subtab
					switch ($subtab) {
						case 'imageinfo':
							//	the image subtab:
							?>
							<script type="text/javascript">
								// <!-- <![CDATA[
								$(window).load(function() {
									//	remove the bulk action publish options
									$('option[value=showall]').remove();
									$('option[value=hideall]').remove();
									//	disable the publish checkboxes
									$('input[name$=Visible]').attr('disabled', 'disabled');
								});
								// ]]> -->
							</script>
							<?php
							break;
						case 'albuminfo':
							// the album subtab:
							?>
							<script type="text/javascript">
								// <!-- <![CDATA[
								$(window).load(function() {
									//	disable the publish checkbox
									$('input[name=Published]').attr('disabled', 'disabled');
								});
								// ]]> -->
							</script>
							<?php
							break;
						case 'sort':
							//	the image sort subtab. Nothing to do here
							break;
						default:
							//	the "main" tab:
							if (isset($_GET['massedit'])) {
								?>
								<script type="text/javascript">
									// <!-- <![CDATA[
									$(window).load(function() {
										//	disable the "mass-edit" publish checkboxes
										$('input[name$=Published]').attr('disabled', 'disabled');
									});
									// ]]> -->
								</script>
								<?php
							} else {
								?>
								<script type="text/javascript">
									// <!-- <![CDATA[
									$(window).load(function() {
										//	remove the bulk action publish options
										$('option[value=showall]').remove();
										$('option[value=hideall]').remove();
										//	remove the publish/unpublish links
										$('a[href*=publish]').remove();
										//	remove the ledgend for publish/unpublish
										$("li:contains('<?php echo gettext('Published/Un-published'); ?>')").remove();
									});
									// ]]> -->
								</script>
								<?php
							}
							break;
					}
					break;
			}
		}
	}

	static function tab($xlate) {
		$xlate['demo'] = gettext('demo');
		return $xlate;
	}

	/**
	 * intercepts the album/image "apply" actions and alters their input
	 *
	 * NOTE:
	 *
	 * The nature of checkboxes is that if they are checked there will be an entry in the $_POST array
	 * containing the value of the input item. If they are not checked they there will not be anything in the
	 * $_POST array. Admin pages presume that if the element is not set target attribute should be set to "false".
	 *
	 * Since publish items are checkboxes it is necessary to re-create the $_POST array to the way it would have
	 * initally--that is, if the item was originally published the $_POST array element should have a value of one.
	 * If the item was originally unpublished there should be no $_POST array element.
	 *
	 *
	 * @param unknown_type $allow
	 * @return unknown
	 */
	static function save($allow) {
		if (!zp_loggedin(ADMIN_RIGHTS)) {
			if (isset($_GET['action'])) {
				switch ($_GET['action']) {
					case 'publish':
						//	the publish/unpublish icons--simply remove the action
						unset($_GET['action']);
						break;
					case 'save':
						//	an "apply"
						if (isset($_POST['album'])) {
							// on the image or ablum tab
							$folder = sanitize_path($_POST['album']);
							$album = newAlbum($folder);
							if (isset($_POST['totalimages'])) {
								//	for images, set the "Visible" item to the state of the image.
								for ($i = 0; $i < $_POST['totalimages']; $i++) {
									$filename = sanitize($_POST["$i-filename"]);
									$image = newImage($album, $filename);
									if ($image->getShow()) {
										$_POST[$i . '-Visible'] = 1;
									} else {
										unset($_POST[$i . '-Visible']);
									}
								}
							} else {
								//	set to the publish state of the album
								if ($album->getShow()) {
									$_POST['Published'] = 1;
								} else {
									unset($_POST['Published']);
								}
							}
						} else {
							if (isset($_POST['totalalbums'])) {
								//	mass-edit of albums.
								$n = sanitize_numeric($_POST['totalalbums']);
								for ($i = 1; $i <= $n; $i++) {
									//	set the "Visible" item to the state of the image.
									if ($i > 0) {
										$prefix = $i . "-";
									} else {
										$prefix = '';
									}
									$f = sanitize_path(trim(sanitize($_POST[$prefix . 'folder'])));
									$album = newAlbum($f);
									if ($album->getShow()) {
										$_POST[$prefix . 'Published'] = 1;
									} else {
										unset($_POST[$prefix . 'Published']);
									}
								}
							}
						}
						break;
				}
			}
		}
		return $allow;
	}

}
?>