<?php
/**
 * Provides a unified comment handling facility
 *
 * Place a call on the function printCommentForm() in your script where you
 * wish the comment items to appear.
 *
 * Normally the plugin uses the form plugins/comment_form/comment_form.php.
 * However, you may override this form by placing a script of the same name in your theme folder.
 * This will allow you to customize the appearance of the comments on your site.
 *
 * There are several options to tune what the plugin will do.
 *
 * @package plugins
 */
$plugin_is_filter = 5|ADMIN_PLUGIN|THEME_PLUGIN;
$plugin_description = gettext("Provides a unified comment handling facility.");
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';
$option_interface = 'comment_form';

if (getOption('zp_plugin_comment_form')) {	// 	We might get loaded by some plugin needing the address fields
	zp_register_filter('comment_post', 'comment_form_comment_post');
	zp_register_filter('options_comments', 'comment_form_options');
	zp_register_filter('save_comment_custom_data', 'comment_form_save_comment');
	zp_register_filter('edit_comment_custom_data', 'comment_form_edit_comment');
	zp_register_filter('admin_overview', 'comment_form_print10Most',0);
}
zp_register_filter('save_admin_custom_data', 'comment_form_save_admin');
zp_register_filter('edit_admin_custom_data', 'comment_form_edit_admin');
if (getOption('register_user_address_info')) {
	zp_register_filter('register_user_form', 'comment_form_register_user');
	zp_register_filter('register_user_registered', 'comment_form_register_save');
}

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
										'desc' => gettext('If checked, an RSS link will be included at the bottom of the comment section.'))
									);
	}

	function handleOption($option, $currentValue) {
	}

}

/**
 * Returns a processed comment custom data item
 * Called when a comment edit is saved
 *
 * @param string $discard always empty
 * @return string
 */
function comment_form_save_comment($discard) {
	return serialize(getUserInfo(0));
}

/**
 * Admin overview summary
 */
function comment_form_print10Most($side) {
	if ($side=='right') {
			?>
		<div class="box" id="overview-comments">
		<h2 class="h2_bordered"><?php echo gettext("10 Most Recent Comments"); ?></h2>
		<ul>
		<?php
		$comments = fetchComments(10);
		foreach ($comments as $comment) {
			$id = $comment['id'];
			$author = $comment['name'];
			$email = $comment['email'];
			$link = gettext('<strong>database error</strong> '); // incase of such

			// ZENPAGE: switch added for zenpage comment support
			switch ($comment['type']) {
				case "albums":
					$image = '';
					$title = '';
					$albmdata = query_full_array("SELECT `title`, `folder` FROM ". prefix('albums') .
		 										" WHERE `id`=" . $comment['ownerid']);
					if ($albmdata) {
						$albumdata = $albmdata[0];
						$album = $albumdata['folder'];
						$albumtitle = get_language_string($albumdata['title']);
						$link = "<a href=\"".rewrite_path("/$album","/index.php?album=".pathurlencode($album))."\">".$albumtitle.$title."</a>";
						if (empty($albumtitle)) $albumtitle = $album;
					}
					break;
				case "news": // ZENPAGE: if plugin is installed
					if(getOption("zp_plugin_zenpage")) {
						$titlelink = '';
						$title = '';
						$newsdata = query_full_array("SELECT `title`, `titlelink` FROM ". prefix('news') .
		 										" WHERE `id`=" . $comment['ownerid']);
						if ($newsdata) {
							$newsdata = $newsdata[0];
							$titlelink = $newsdata['titlelink'];
							$title = get_language_string($newsdata['title']);
							$link = "<a href=\"".rewrite_path("/news/".$titlelink,"/index.php?p=news&amp;title=".urlencode($titlelink))."\">".$title."</a> ".gettext("[news]");
						}
					}
					break;
				case "pages": // ZENPAGE: if plugin is installed
					if(getOption("zp_plugin_zenpage")) {
						$image = '';
						$title = '';
						$pagesdata = query_full_array("SELECT `title`, `titlelink` FROM ". prefix('pages') .
		 										" WHERE `id`=" . $comment['ownerid']);
						if ($pagesdata) {
							$pagesdata = $pagesdata[0];
							$titlelink = $pagesdata['titlelink'];
							$title = get_language_string($pagesdata['title']);
							$link = "<a href=\"".rewrite_path("/pages/".$titlelink,"/index.php?p=pages&amp;title=".urlencode($titlelink))."\">".$title."</a> ".gettext("[page]");
						}
					}
					break;
				default: // all of the image types
					$imagedata = query_full_array("SELECT `title`, `filename`, `albumid` FROM ". prefix('images') .
		 										" WHERE `id`=" . $comment['ownerid']);
					if ($imagedata) {
						$imgdata = $imagedata[0];
						$image = $imgdata['filename'];
						if ($imgdata['title'] == "") $title = $image; else $title = get_language_string($imgdata['title']);
						$title = '/ ' . $title;
						$albmdata = query_full_array("SELECT `folder`, `title` FROM ". prefix('albums') .
		 											" WHERE `id`=" . $imgdata['albumid']);
						if ($albmdata) {
							$albumdata = $albmdata[0];
							$album = $albumdata['folder'];
							$albumtitle = get_language_string($albumdata['title']);
							$link = "<a href=\"".rewrite_path("/$album/$image","/index.php?album=".pathurlencode($album).	"&amp;image=".urlencode($image))."\">".$albumtitle.$title."</a>";
							if (empty($albumtitle)) $albumtitle = $album;
						}
					}
					break;
			}
			$comment = truncate_string($comment['comment'], 123);
			echo "<li><div class=\"commentmeta\">".sprintf(gettext('<em>%1$s</em> commented on %2$s:'),$author,$link)."</div><div class=\"commentbody\">$comment</div></li>";
		}
		?>
		</ul>
		</div>
		<?php
	}
	return $side;
}

/**
 * Returns table row(s) for edit of a comment's custom data
 *
 * @param string $discard always empty
 * @return string
 */
function comment_form_edit_comment($discard, $raw) {
	if (!preg_match('/^a:[0-9]+:{/', $raw)) {
		$address = array('street'=>'', 'city'=>'', 'state'=>'', 'country'=>'', 'postal'=>'', 'website'=>'');
	} else {
		$address = unserialize($raw);
	}
	return
			 '<tr>
					<td>'.
						gettext('street:').
				 '</td>
					<td>
						<input type="text" name="0-comment_form_street" id="comment_form_street" class="inputbox" size="40" value="'.$address['street'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						gettext('city:').
					'</td>
					<td>
						<input type="text" name="0-comment_form_city" id="comment_form_city" class="inputbox" size="40" value="'.$address['city'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						gettext('state:').
				 '</td>
					<td>
						<input type="text" name="0-comment_form_state" id="comment_form_state" class="inputbox" size="40" value="'.$address['state'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						gettext('country:').
				 '</td>
					<td>
						<input type="text" name="0-comment_form_country" id="comment_form_country" class="inputbox" size="40" value="'.$address['country'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						gettext('postal code:').
					'</td>
					<td>
						<input type="text" name="0-comment_form_postal" id="comment_form_postal" class="inputbox" size="40" value="'.$address['postal'].'">
					</td>
				</tr>'."\n";
}

function comment_form_register_user($html) {
	global $_comment_form_save_post;
	return comment_form_edit_comment(false, $_comment_form_save_post);
}

function comment_form_register_save($user) {
	global $_comment_form_save_post;
	$addresses = getOption('register_user_address_info');
	$userinfo = getUserInfo(0);
	$_comment_form_save_post = serialize($userinfo);
	if ($addresses == 'required') {
		if (!isset($userinfo['street']) || empty($userinfo['street'])) {
			$user->transient = true;
			$user->msg .= ' '.gettext('You must supply the street field.');
		}
		if (!isset($userinfo['city']) || empty($userinfo['city'])) {
			$user->transient = true;
			$user->msg .= ' '.gettext('You must supply the city field.');
		}
		if (!isset($userinfo['state']) || empty($userinfo['state'])) {
			$user->transient = true;
			$user->msg .= ' '.gettext('You must supply the state field.');
		}
		if (!isset($userinfo['country']) || empty($userinfo['country'])) {
			$user->transient = true;
			$user->msg .= ' '.gettext('You must supply the country field.');
		}
		if (!isset($userinfo['postal']) || empty($userinfo['postal'])) {
			$user->transient = true;
			$user->msg .= ' '.gettext('You must supply the postal code field.');
		}
	}
	$user->setCustomData($_comment_form_save_post);
}

/**
 * Saves admin custom data
 * Called when an admin is saved
 *
 * @param string $updated true if data has changed
 * @param object $userobj admin user object
 * @param string $i prefix for the admin
 * @param bool $alter will be true if critical admin data may be altered
 * @return bool
 */
function comment_form_save_admin($updated, $userobj, $i, $alter) {
	$olddata = $userobj->getCustomData();
	$userobj->setCustomData(serialize(getUserInfo($i)));
	if ($olddata != $userobj->getCustomData()) {
		return true;
	}
	return $updated;
}

/**
 * Processes the post of an address
 *
 * @param int $i sequence number of the comment
 * @return array
 */
function getUserInfo($i) {
	$result = array();
	if (isset($_POST[$i.'-comment_form_website'])) $result['website'] = sanitize($_POST[$i.'-comment_form_website'], 1);
	if (isset($_POST[$i.'-comment_form_street'])) $result['street'] = sanitize($_POST[$i.'-comment_form_street'], 1);
	if (isset($_POST[$i.'-comment_form_city'])) $result['city'] = sanitize($_POST[$i.'-comment_form_city'], 1);
	if (isset($_POST[$i.'-comment_form_state'])) $result['state'] = sanitize($_POST[$i.'-comment_form_state'], 1);
	if (isset($_POST[$i.'-comment_form_country'])) $result['country'] = sanitize($_POST[$i.'-comment_form_country'], 1);
	if (isset($_POST[$i.'-comment_form_postal'])) $result['postal'] = sanitize($_POST[$i.'-comment_form_postal'], 1);
	return $result;
}

/**
 * Processes the address parts of a comment post
 *
 * @param object $commentobj the comment object
 * @param object $receiver the object receiving the comment
 * @return object
 */
function comment_form_comment_post($commentobj, $receiver) {
	if ($addresses = getOption('comment_form_addresses')) {
		$userinfo = getUserInfo(0);
		if ($addresses == 'required') {
			// Note: this error will be incremented by functions-controller
			if (!isset($userinfo['street']) || empty($userinfo['street'])) {
				$commentobj->setInModeration(-11);
				$commentobj->comment_error_text .= ' '.gettext('You must supply the street field.');
			}
			if (!isset($userinfo['city']) || empty($userinfo['city'])) {
				$commentobj->setInModeration(-12);
				$commentobj->comment_error_text .= ' '.gettext('You must supply the city field.');
			}
			if (!isset($userinfo['state']) || empty($userinfo['state'])) {
				$commentobj->setInModeration(-13);
				$commentobj->comment_error_text .= ' '.gettext('You must supply the state field.');
			}
			if (!isset($userinfo['country']) || empty($userinfo['country'])) {
				$commentobj->setInModeration(-14);
				$commentobj->comment_error_text .= ' '.gettext('You must supply the country field.');
			}
			if (!isset($userinfo['postal']) || empty($userinfo['postal'])) {
				$commentobj->comment_error_text .= ' '.gettext('You must supply the postal code field.');
				$commentobj->setInModeration(-15);
			}
		}
		$commentobj->setCustomData(serialize($userinfo));
	}
	return $commentobj;
}

/**
 * Supplies comment form options on the options/comments tab
 */
function comment_form_options() {
	$optionHandler = new comment_form();
	customOptions($optionHandler, "");
}

/**
 * Returns table row(s) for edit of an admin user's custom data
 *
 * @param string $html always empty
 * @param $userobj Admin user object
 * @param string $i prefix for the admin
 * @param string $background background color for the admin row
 * @param bool $current true if this admin row is the logged in admin
 * @return string
 */
function comment_form_edit_admin($html, $userobj, $i, $background, $current) {
	$raw = $userobj->getCustomData();
	$needs = array('street'=>'', 'city'=>'', 'state'=>'', 'country'=>'', 'postal'=>'', 'website'=>'');
	if (!preg_match('/^a:[0-9]+:{/', $raw)) {
		$address = $needs;
	} else {
		$address = unserialize($raw);
		foreach ($needs as $needed=>$value) {
			if (!isset($address[$needed])) {
				$address[$needed] = '';
			}
		}
	}

	return $html.
	 '<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
			<td width="20%"'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.gettext("Website:").'</td>
			<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top"><input type="text" name="'.$i.'-comment_form_website" value="'.$address['website'].'" /></td>
			<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top" ></td>
		</tr>'.
	 '<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
			<td width="20%"'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.gettext("Street:").'</td>
			<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top"><input type="text" name="'.$i.'-comment_form_street" value="'.$address['street'].'" /></td>
			<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top" rowspan="5">'.gettext('Address information').'</td>
		</tr>'.
		'<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
			<td width="20%"'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.gettext("City:").'</td>
			<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top"><input type="text" name="'.$i.'-comment_form_city" value="'.$address['city'].'" /></td>
		</tr>'.
		'<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
			<td width="20%"'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.gettext("State:").'</td>
			<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top"><input type="text" name="'.$i.'-comment_form_state" value="'.$address['state'].'" /></td>
		</tr>'.
		'<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
			<td width="20%"'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.gettext("Country:").'</td>
			<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top"><input type="text" name="'.$i.'-comment_form_country" value="'.$address['country'].'" /></td>
		</tr>'.
		'<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">
			<td width="20%"'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.gettext("Postal code:").'</td>
			<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top"><input type="text" name="'.$i.'-comment_form_postal" value="'.$address['postal'].'" /></td>
		</tr>';
}

/**
 * Returns an error message if a comment posting was not accepted
 *
 * @return string
 */
function getCommentErrors() {
	global $_zp_comment_error;
	return $_zp_comment_error;
}

/**
 * Tool to output an error message if a comment posting was not accepted
 */
function printCommentErrors() {
	global $_zp_comment_error, $_zp_comment_on_hold;
	if ($_zp_comment_on_hold) {
		$s = trim(str_replace($_zp_comment_on_hold, '', trim($_zp_comment_error)));
		?>
		<p class="notebox"><?php echo $_zp_comment_on_hold; ?></p>
		<?php
	} else {
		$s = trim($_zp_comment_error);
	}
	if ($s) {
		$lines = explode('.', $s);
		foreach ($lines as $key=>$line) {
			if (empty($line) || $line == gettext('Mail send failed')) {
				unset($lines[$key]);
			}
		}
		?>
		<div class="errorbox">
			<h2><?php echo ngettext('Error posting comment:','Errors posting comment:',count($lines)); ?></h2>
			<ul class="errorlist">
			<?php
			foreach ($lines as $line) {
				echo '<li>'.trim($line).'</li>';
			}
			?>
			</ul>
		</div>
		<?php
	}
}

/**
 * Prints a form for posting comments
 *
 * @param bool $showcomments defaults to true for showing list of comments
 * @param string $addcommenttext alternate text for "Add a comment:"
 * @param bool $addheader set true to display comment count header
 * @param string $comment_commententry_mod use to add styles, classes to the comment form div
 */
function printCommentForm($showcomments=true, $addcommenttext=NULL, $addheader=true, $comment_commententry_mod='') {
	global $_zp_gallery_page, $_zp_themeroot,	$_zp_current_admin_obj, $_zp_current_comment;
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
		if ($showcomments) {
			$num = getCommentCount();
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
			?>
		<div id="comments">
			<div id="comment_toggle"><!-- place holder for toggle button --></div>
			<?php
			while (next_comment()) {
				if (!getOption('comment_form_showURL')) {
					$_zp_current_comment['website'] = '';
				}
				?>
				<div class="comment" <?php echo $display; ?>>
					<a name="c_<?php echo $_zp_current_comment['id']; ?>"></a>
					<div class="commentinfo">
						<h4><?php	printCommentAuthorLink(); ?>: on <?php echo getCommentDateTime(); printEditCommentLink('Edit', ', ', ''); ?></h4>
					</div><!-- class "commentinfo" -->
					<div class="commenttext"><?php echo getCommentBody();?></div><!-- class "commenttext" -->
				</div><!-- class "comment" -->
				<?php
			}
			?>
		</div><!-- id "comments" -->
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
if (getOption('comment_form_rss')) {
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