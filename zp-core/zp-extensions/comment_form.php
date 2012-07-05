<?php
/**
 * This is Zenphoto's unified comment handling facility
 *
 * Place a call on the function <var>printCommentForm()</var> in your script where you
 * wish the comment items to appear.
 *
 * The plugin uses <var>%ZENFOLDER%/%PLUGIN_FOLDER%/comment_form/comment_form.php</var>.
 * However, you may override this form by placing a script of the same name in a similar folder in your theme.
 * This will allow you to customize the appearance of the comments on your site.
 *
 * There are several options to tune what the plugin will do.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 */
$plugin_is_filter = 5|ADMIN_PLUGIN|THEME_PLUGIN;
$plugin_description = gettext("Provides a unified comment handling facility.");
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'comment_form';

require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/comment_form/functions.php');

zp_register_filter('comment_post', 'comment_form_comment_post');
zp_register_filter('options_comments', 'comment_form_options');
zp_register_filter('save_comment_custom_data', 'comment_form_save_comment');
zp_register_filter('edit_comment_custom_data', 'comment_form_edit_comment');
zp_register_filter('admin_overview', 'comment_form_print10Most');

// I choose to keep unneeded js loading low to add an option to specifially disable pagination
if(getOption('comment_form_pagination')) {
	zp_register_filter('theme_head','comment_form_PaginationJS');
}
zp_register_filter('save_admin_custom_data', 'comment_form_save_admin');
zp_register_filter('edit_admin_custom_data', 'comment_form_edit_admin');

class comment_form {

	/**
	 * class instantiation function
	 *
	 * @return admin_login
	 */
	function comment_form() {
		setOptionDefault('comment_form_addresses', 0);
		setOptionDefault('comment_form_require_addresses', 0);
		setOptionDefault('comment_form_members_only', 0);
		setOptionDefault('comment_form_albums', 1);
		setOptionDefault('comment_form_images', 1);
		setOptionDefault('comment_form_articles', 1);
		setOptionDefault('comment_form_pages', 1);
		setOptionDefault('comment_form_rss', 1);
		setOptionDefault('comment_form_private', 1);
		setOptionDefault('comment_form_anon', 1);
		setOptionDefault('comment_form_showURL', 1);
		setOptionDefault('comment_form_comments_per_page', 10);
		setOptionDefault('comment_form_pagination', true);
	}


	/**
	 * Reports the supported options
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		$checkboxes = array(gettext('Albums') => 'comment_form_albums', gettext('Images') => 'comment_form_images');
		if (getOption('zp_plugin_zenpage')) {
			$checkboxes = array_merge($checkboxes, array(gettext('Pages') => 'comment_form_pages', gettext('News') => 'comment_form_articles'));
		}

		return array(	gettext('Address fields') => array('key' => 'comment_form_addresses', 'type' => OPTION_TYPE_RADIO,
										'order' => 0,
										'buttons' => array(gettext('Omit')=>0, gettext('Show')=>1, gettext('Require')=>'required'),
										'desc' => gettext('If <em>Address fields</em> are shown or required, the form will include positions for address information. If required, the poster must supply data in each address field.')),
									gettext('Allow comments on') => array('key' => 'comment_form_allowed', 'type' => OPTION_TYPE_CHECKBOX_ARRAY,
										'order' => 5,
										'checkboxes' => $checkboxes,
										'desc' => gettext('Comment forms will be presented on the checked pages.')),
									gettext('Toggled comment block') => array('key' => 'comment_form_toggle', 'type' => OPTION_TYPE_CHECKBOX,
										'order' => 6,
										'desc' => gettext('If checked, existing comments will be initially hidden. Clicking on the provided button will show them.')),
									gettext('Show author URL') => array('key' => 'comment_form_showURL', 'type' => OPTION_TYPE_CHECKBOX,
										'order' => 1,
										'desc' => gettext('To discourage SPAM, uncheck this box and the author URL will not be revealed.')),
									gettext('Only members can comment') => array('key' => 'comment_form_members_only', 'type' => OPTION_TYPE_CHECKBOX,
										'order' => 2,
										'desc' => gettext('If checked, only logged in users will be allowed to post comments.')),
									gettext('Allow private postings') => array('key' => 'comment_form_private', 'type' => OPTION_TYPE_CHECKBOX,
										'order' => 3,
										'desc' => gettext('If checked, posters may mark their comments as private (not for publishing).')),
									gettext('Allow anonymous posting') => array('key' => 'comment_form_anon', 'type' => OPTION_TYPE_CHECKBOX,
										'order' => 4,
										'desc' => gettext('If checked, posters may exclude their personal information from the published post.')),
									gettext('Include RSS link') => array('key' => 'comment_form_rss', 'type' => OPTION_TYPE_CHECKBOX,
										'order' => 8,
										'desc' => gettext('If checked, an RSS link will be included at the bottom of the comment section.')),
									gettext('Comments per page') => array('key' => 'comment_form_comments_per_page', 'type' => OPTION_TYPE_TEXTBOX,
										'order' => 8,
										'desc' => gettext('The comments that should show per page using the jQuery pagination')),
									gettext('Pagination') => array('key' => 'comment_form_pagination', 'type' => OPTION_TYPE_CHECKBOX,
										'order' => 8,
										'desc' => gettext('Uncheck to disable the jQuery pagination of comments. Enabled by default.')),
									);
	}

	function handleOption($option, $currentValue) {
	}

}

/**
 * Prints a form for posting comments
 *
 * @param bool $showcomments defaults to true for showing list of comments
 * @param string $addcommenttext alternate text for "Add a comment:"
 * @param bool $addheader set true to display comment count header
 * @param string $comment_commententry_mod use to add styles, classes to the comment form div
 * @param bool $desc_order default false, set to true to change the comment order to descending ( = newest to oldest)
 */
function printCommentForm($showcomments=true, $addcommenttext=NULL, $addheader=true, $comment_commententry_mod='',$desc_order=false) {
	global $_zp_gallery_page, $_zp_current_admin_obj, $_zp_current_comment, $_zp_captcha, $_zp_authority;
	if (getOption('email_new_comments')) {
		$email_list = $_zp_authority->getAdminEmail();
		if (empty($email_list)) {
			setOption('email_new_comments', 0);
		}
	}
	if (is_null($addcommenttext)) $addcommenttext = '<h3>'.gettext('Add a comment:').'</h3>';
	switch ($_zp_gallery_page) {
		case 'album.php':
			if (!getOption('comment_form_albums')) return;
			$comments_open = OpenedForComments(ALBUM);
			$formname = '/comment_form.php';
			break;
		case 'image.php':
			if (!getOption('comment_form_images')) return;
			$comments_open = OpenedForComments(IMAGE);
			$formname = '/comment_form.php';
			break;
		case 'pages.php':
			if (!getOption('comment_form_pages')) return;
			$comments_open = zenpageOpenedForComments();
			$formname = '/comment_form.php';
			break;
		case 'news.php':
			if (!getOption('comment_form_articles')) return;
			$comments_open = zenpageOpenedForComments();
			$formname = '/comment_form.php';
			break;
		default:
			return;
			break;
	}
	$arraytest = '/^a:[0-9]+:{/'; // this screws up Eclipse's brace count!!!
	?>
<!-- printCommentForm -->
	<div id="commentcontent">
		<?php
		$num = getCommentCount();
		if ($showcomments) {
			if ($num==0) {
				if ($addheader) echo '<h3 class="empty">'.gettext('No Comments').'</h3>';
				$display = '';
			} else {
				if ($addheader) echo '<h3>'.sprintf(ngettext('%u Comment','%u Comments',$num), $num).'</h3>';
				if (getOption('comment_form_toggle')) {
					?>
					<script type="text/javascript">
						// <!-- <![CDATA[
						function toggleComments(hide) {
							if (hide) {
								$('div.comment').hide();
								$('#comment_toggle').html('<button type="button" onclick="javascript:toggleComments(false);"><?php echo gettext('show comments');?></button>');
							} else {
								$('div.comment').show();
								$('#comment_toggle').html('<button type="button" onclick="javascript:toggleComments(true);"><?php echo gettext('hide comments');?></button>');
							}
						}
						$(document).ready(function() {
							toggleComments(true);
						});
						// ]]> -->
					</script>
					<?php
					$display = ' style="display:none"';
				} else {
					$display = '';
				}
			}
			$hideoriginalcomments = '';
			if(getOption('comment_form_pagination') && getOption('comment_form_comments_per_page') < $num) {
				$hideoriginalcomments = ' style="display:none"'; // hide original comment display to be replaced by jQuery pagination
			}
		 if(getOption('comment_form_pagination') && getOption('comment_form_comments_per_page') < $num) { ?>
					<div class="Pagination"></div><!-- this is the jquery pagination nav placeholder -->
					<div id="Commentresult">
            This content will be replaced when pagination inits.
        	</div>
      <?php
      }
		 ?>
		<div id="comments"<?php echo $hideoriginalcomments; ?>>
			<div id="comment_toggle"><!-- place holder for toggle button --></div>
			<?php
			while (next_comment($desc_order)) {
				if (!getOption('comment_form_showURL')) {
					$_zp_current_comment['website'] = '';
				}
				?>
				<div class="comment" <?php echo $display; ?>>
					<span id="c_<?php echo $_zp_current_comment['id']; ?>"></span>
					<div class="commentinfo">
						<h4><?php	printCommentAuthorLink(); ?>: on <?php echo getCommentDateTime(); printEditCommentLink(gettext('Edit'), ', ', ''); ?></h4>
					</div><!-- class "commentinfo" -->
					<div class="commenttext"><?php echo html_encodeTagged(getCommentBody(),false); ?></div><!-- class "commenttext" -->
				</div><!-- class "comment" -->
				<?php
			}
			?>
		</div><!-- id "comments" -->
		<?php
		}
		if(getOption('comment_form_pagination') && getOption('comment_form_comments_per_page') < $num) { ?>
			<div class="Pagination"></div><!-- this is the jquery pagination nav placeholder -->
      <?php
    }
		?>
		<!-- Comment Box -->
		<?php
		if ($comments_open) {
			$stored = array_merge(getCommentStored(),array('street'=>'', 'city'=>'', 'state'=>'', 'country'=>'', 'postal'=>''));
			$raw = $stored['custom'];
			if (preg_match($arraytest, $raw)) {
				$custom = unserialize($raw);
				foreach ($custom as $key=>$value) {
					if (!empty($value)) $stored[$key] = $value;
				}
			}
			$disabled = array('name'=>'',	'website'=>'', 'anon'=>'', 'private'=>'', 'comment'=>'',
 												'street'=>'', 'city'=>'', 'state'=>'', 'country'=>'', 'postal'=>'');
			foreach ($stored as $key=>$value) {
				$disabled[$key] = false;
			}

			if (zp_loggedin()) {
				$raw = $_zp_current_admin_obj->getCustomData();
				if (preg_match($arraytest, $raw)) {
					$address = unserialize($raw);
					foreach ($address as $key=>$value) {
						if (!empty($value)) {
							$disabled[$key] = true;
							$stored[$key] = $value;
						}
					}
				}
				$name = $_zp_current_admin_obj->getName();
				if (!empty($name)) {
					$stored['name'] = $name;
					$disabled['name'] = ' disabled="disabled"';
				} else {
					$user = $_zp_current_admin_obj->getUser();
					if (!empty($user)) {
						$stored['name'] = $user;
						$disabled['name'] = ' disabled="disabled"';
					}
				}
				$email = $_zp_current_admin_obj->getEmail();
				if (!empty($email)) {
					$stored['email'] = $email;
					$disabled['email'] = ' disabled="disabled"';
				}
				if (!empty($address['website'])) {
					$stored['website'] = $address['website'];
					$disabled['website'] = ' disabled="disabled"';
				}
			}
			$data = zp_apply_filter('comment_form_data',array('data'=>$stored, 'disabled'=>$disabled));
			$disabled = $data['disabled'];
			$stored = $data['data'];

			if (MEMBERS_ONLY_COMMENTS && !zp_loggedin(POST_COMMENT_RIGHTS)) {
				echo gettext('Only registered users may post comments.');
			} else {
				if (!empty($addcommenttext)) {
					echo $addcommenttext;
				}
				?>
				<div id="commententry" <?php echo $comment_commententry_mod; ?>>
				<?php
				$theme = getCurrentTheme();
				$form = getPlugin('comment_form'.$formname, $theme);
				require($form);
				?>
				</div><!-- id="commententry" -->
				<?php
			}
		} else {
			?>
			<div id="commententry">
				<h3><?php echo gettext('Closed for comments.');?></h3>
			</div><!-- id="commententry" -->
			<?php
		}
		?>
		</div><!-- id="commentcontent" -->
	<?php
if (getOption('comment_form_rss') && getOption('RSS_comments')) {
	?>
	<br clear="all" />
	<?php
	switch($_zp_gallery_page) {
		case "image.php":
			printRSSLink("Comments-image","",gettext("Subscribe to comments"),"");
			break;
		case "album.php":
			printRSSLink("Comments-album","",gettext("Subscribe to comments"),"");
			break;
		case "news.php":
			printZenpageRSSLink("Comments-news", "", "", gettext("Subscribe to comments"), "");
			break;
		case "pages.php":
			printZenpageRSSLink("Comments-page", "", "", gettext("Subscribe to comments"), "");
			break;
	}
}
?>
<!-- end printCommentForm -->
<?php
}
?>