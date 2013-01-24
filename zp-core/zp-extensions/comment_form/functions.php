<?php
if (getOption('register_user_address_info')) {
	zp_register_filter('register_user_form', 'comment_form_register_user');
	zp_register_filter('register_user_registered', 'comment_form_register_save');
}

function comment_form_PaginationJS() {
	?>
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER ; ?>/js/jquery.pagination.js"></script>
	<script type="text/javascript">

						// This is a very simple demo that shows how a range of elements can
						// be paginated.
						// The elements that will be displayed are in a hidden DIV and are
						// cloned for display. The elements are static, there are no Ajax
						// calls involved.

						/**
						 * Callback function that displays the content.
						 *
						 * Gets called every time the user clicks on a pagination link.
						 *
						 * @param {int} page_index New Page index
						 * @param {jQuery} jq the container with the pagination links as a jQuery object
						 */
						function pageselectCallback(page_index, jq){
								var items_per_page = <?php echo getOption('comment_form_comments_per_page'); ?>;
								var max_elem = Math.min((page_index+1) * items_per_page, $('#comments div.comment').length);
								var newcontent = '';
							 // alert(members);
								// Iterate through a selection of the content and build an HTML string
								for(var i=page_index*items_per_page;i<max_elem;i++) {
									newcontent += '<div class="comment">'+$('#comments div.comment:nth-child('+(i+1)+')').html()+'</div>';
								}

								// Replace old content with new content
								$('#Commentresult').html(newcontent);

								// Prevent click eventpropagation
								return false;
						}

						/**
						 * Initialisation function for pagination
						 */
						function initPagination() {
								var startPage;
								if (Comm_ID_found){
									startPage=Math.ceil(current_comment_N/<?php echo getOption('comment_form_comments_per_page'); ?>)-1;
								} else {
									startPage=0;
								}
								// count entries inside the hidden content
								var num_entries = $('#comments div.comment').length;
								// Create content inside pagination element
								$(".Pagination").pagination(num_entries, {
										prev_text: "<?php echo gettext('prev'); ?>",
										next_text: "<?php echo gettext('next'); ?>",
										callback: pageselectCallback,
										load_first_page:true,
										items_per_page:<?php echo getOption('comment_form_comments_per_page'); ?>, // Show only one item per page
										current_page:startPage
								});
						 }

						// When document is ready, initialize pagination
						$(document).ready(function(){
								current_comment_N = $('.comment h4').index($(addrBar_hash))+1;
								initPagination();
								if (Comm_ID_found){
									$(addrBar_hash).scrollToMe();
								}
						});
						//Initialize variables used to detect if a comment has been posted and its position
						var current_comment_N, addrBar_hash = window.location.hash,Comm_ID_found = !addrBar_hash.search(/#zp_comment_id_/);
						jQuery.fn.extend({
							scrollToMe: function () {
							var x = jQuery(this).offset().top -10;
							jQuery('html,body').animate({scrollTop: x}, 400);
						}});

				</script>
	<?php
}

function comment_form_visualEditor() {
	zp_apply_filter('texteditor_config', '','comments');
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
function comment_form_print10Most() {
	?>
	<div class="box overview-utility">
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
		$comment = shortenContent($comment['comment'], 123, '...');
		echo "<li><div class=\"commentmeta\">".sprintf(gettext('<em>%1$s</em> commented on %2$s:'),$author,$link)."</div><div class=\"commentbody\">$comment</div></li>";
	}
	?>
	</ul>
	</div>
	<?php
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
	$required = getOption('register_user_address_info');
	if ($required == 'required') {
		$required = '*';
	} else {
		$required = false;
	}
	$html =
			 '<tr>
					<td>'.
						sprintf(gettext('Street%s:'),$required).
				 '</td>
					<td>
						<input type="text" name="0-comment_form_street" id="comment_form_street" class="inputbox" size="40" value="'.@$address['street'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						sprintf(gettext('City%s:'),$required).
					'</td>
					<td>
						<input type="text" name="0-comment_form_city" id="comment_form_city" class="inputbox" size="40" value="'.@$address['city'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						sprintf(gettext('State%s:'),$required).
				 '</td>
					<td>
						<input type="text" name="0-comment_form_state" id="comment_form_state" class="inputbox" size="40" value="'.@$address['state'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						sprintf(gettext('Country%s:'),$required).
				 '</td>
					<td>
						<input type="text" name="0-comment_form_country" id="comment_form_country" class="inputbox" size="40" value="'.@$address['country'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						sprintf(gettext('Postal code%s:'),$required).
					'</td>
					<td>
						<input type="text" name="0-comment_form_postal" id="comment_form_postal" class="inputbox" size="40" value="'.@$address['postal'].'">
					</td>
				</tr>'."\n";
	if ($required) {
		$html .=
				'<tr>
					<td>
					</td>
					<td>'.
						gettext('*Required').
					'</td>
				</tr>'."\n";
	}
	return $html;
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
		'<tr'.((!$current)? ' style="display:none;"':'').' class="userextrainfo">'.
			'<td width="20%"'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top">'.
				'<fieldset>
					<legend>'.gettext("Street").'</legend>
					<input type="text" name="'.$i.'-comment_form_street" value="'.$address['street'].'" size="'.TEXT_INPUT_SIZE.'" />
				</fieldset>'.
				'<fieldset>
					<legend>'.gettext("City").'</legend>
					<input type="text" name="'.$i.'-comment_form_city" value="'.$address['city'].'" size="'.TEXT_INPUT_SIZE.'" />
				</fieldset>'.
				'<fieldset>
					<legend>'.gettext("State").'</legend>
					<input type="text" name="'.$i.'-comment_form_state" value="'.$address['state'].'" size="'.TEXT_INPUT_SIZE.'" />
				</fieldset>'.
			'</td>'.
			'<td'.((!empty($background)) ? ' style="'.$background.'"':'').' valign="top">'.
				'<fieldset>
					<legend>'.gettext("Website").'</legend>
					<input type="text" name="'.$i.'-comment_form_website" value="'.$address['website'].'" size="'.TEXT_INPUT_SIZE.'" />
				</fieldset>'.
				'<fieldset>
					<legend>'.gettext("Country").'</legend>
					<input type="text" name="'.$i.'-comment_form_country" value="'.$address['country'].'" size="'.TEXT_INPUT_SIZE.'" />
				</fieldset>'.
				'<fieldset>
					<legend>'.gettext("Postal code").'</legend>
					<input type="text" name="'.$i.'-comment_form_postal" value="'.$address['postal'].'" size="'.TEXT_INPUT_SIZE.'" />
				</fieldset>'.
			'</td>'.
		'</tr>';
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

define ('COMMENT_EMAIL_REQUIRED', 1);
define ('COMMENT_NAME_REQUIRED', 2);
define ('COMMENT_WEB_REQUIRED', 4);
define ('USE_CAPTCHA', 8);
define ('COMMENT_BODY_REQUIRED', 16);
define ('COMMENT_SEND_EMAIL', 32);
/**
 * Generic comment adding routine. Called by album objects or image objects
 * to add comments.
 *
 * Returns a comment object
 *
 * @param string $name Comment author name
 * @param string $email Comment author email
 * @param string $website Comment author website
 * @param string $comment body of the comment
 * @param string $code CAPTCHA code entered
 * @param string $code_ok CAPTCHA hash expected
 * @param string $type 'albums' if it is an album or 'images' if it is an image comment
 * @param object $receiver the object (image or album) to which to post the comment
 * @param string $ip the IP address of the comment poster
 * @param bool $private set to true if the comment is for the admin only
 * @param bool $anon set to true if the poster wishes to remain anonymous
 * @param bit $check bitmask of which fields must be checked. If set overrides the options
 * @return object
 */
function comment_form_addCcomment($name, $email, $website, $comment, $code, $code_ok, $receiver, $ip, $private, $anon, $check=false) {
	global $_zp_captcha, $_zp_gallery, $_zp_authority, $_zp_comment_on_hold, $_zp_spamFilter;
	if ($check === false) {
		$whattocheck = 0;
		if (getOption('comment_email_required')=='required') $whattocheck = $whattocheck | COMMENT_EMAIL_REQUIRED;
		if (getOption('comment_name_required')) $whattocheck = $whattocheck | COMMENT_NAME_REQUIRED;
		if (getOption('comment_web_required')=='required') $whattocheck = $whattocheck | COMMENT_WEB_REQUIRED;
		if (getOption('Use_Captcha')) $whattocheck = $whattocheck | USE_CAPTCHA;
		if (getOption('comment_body_requiired')) $whattocheck = $whattocheck | COMMENT_BODY_REQUIRED;
		IF (getOption('email_new_comments')) $whattocheck = $whattocheck | COMMENT_SEND_EMAIL;
	} else {
		$whattocheck = $check;
	}
	$type = $receiver->table;
	$class = get_class($receiver);
	$receiver->getComments();
	$name = trim($name);
	$email = trim($email);
	$website = trim($website);
	if (!empty($website) && substr($website, 0, 7) != "http://") {
		$website = "http://" . $website;
	}
	// Let the comment have trailing line breaks and space? Nah...
	// Also (in)validate HTML here, and in $name.
	$comment = trim($comment);
	$receiverid = $receiver->getID();
	$goodMessage = 2;
	if ($private) $private = 1; else $private = 0;
	if ($anon) $anon = 1; else $anon = 0;
	$commentobj = new Comment();
	$commentobj->transient = false; // otherwise we won't be able to save it....
	$commentobj->setOwnerID($receiverid);
	$commentobj->setName($name);
	$commentobj->setEmail($email);
	$commentobj->setWebsite($website);
	$commentobj->setComment($comment);
	$commentobj->setType($type);
	$commentobj->setIP($ip);
	$commentobj->setPrivate($private);
	$commentobj->setAnon($anon);
	$commentobj->setInModeration(0);
	if (($whattocheck & COMMENT_EMAIL_REQUIRED) && (empty($email) || !is_valid_email_zp($email))) {
		$commentobj->setInModeration(-2);
		$commentobj->comment_error_text .= ' '.gettext("You must supply an e-mail address.");
		$goodMessage = false;
	}
	if (($whattocheck & COMMENT_NAME_REQUIRED) && empty($name)) {
		$commentobj->setInModeration(-3);
		$commentobj->comment_error_text .= ' '.gettext("You must enter your name.");
		$goodMessage = false;
	}
	if (($whattocheck & COMMENT_WEB_REQUIRED) && (empty($website) || !isValidURL($website))) {
		$commentobj->setInModeration(-4);
		$commentobj->comment_error_text .= ' '.gettext("You must supply a WEB page URL.");
		$goodMessage = false;
	}
	if (($whattocheck & USE_CAPTCHA)) {
		if (!$_zp_captcha->checkCaptcha($code, $code_ok)) {
			$commentobj->setInModeration(-5);
			$commentobj->comment_error_text .= ' '.gettext("CAPTCHA verification failed.");
			$goodMessage = false;
		}
	}
	if (($whattocheck & COMMENT_BODY_REQUIRED) && empty($comment)) {
		$commentobj->setInModeration(-6);
		$commentobj->comment_error_text .= ' '.gettext("You must enter something in the comment text.");
		$goodMessage = false;
	}
	$moderate = 0;
	if ($goodMessage && isset($_zp_spamFilter)) {
		$goodMessage = $_zp_spamFilter->filterMessage($name, $email, $website, $comment, $receiver, $ip);
		switch ($goodMessage) {
			case	 0:
				$commentobj->setInModeration(2);
				$commentobj->comment_error_text .= sprintf(gettext('Your comment was rejected by the <em>%s</em> SPAM filter.'),$_zp_spamFilter->name);
				$goodMessage = false;
				break;
			case   1:
				$_zp_comment_on_hold = sprintf(gettext('Your comment has been marked for moderation by the <em>%s</em> SPAM filter.'),$_zp_spamFilter->name);
				$commentobj->comment_error_text .= $_zp_comment_on_hold;
				$commentobj->setInModeration(1);
				$moderate = 1;
				break;
			case 2:
				$commentobj->setInModeration(0);
				break;
		}
	}
	$localerrors = $commentobj->getInModeration();
	zp_apply_filter('comment_post', $commentobj, $receiver);
	if ($check === false)	{
		// ignore filter provided errors if caller is supplying the fields to check
		$localerrors = $commentobj->getInModeration();
	}
	if ($goodMessage && $localerrors >= 0)	{
		// Update the database entry with the new comment
		$commentobj->save();
		//  add to comments array and notify the admin user
		if (!$moderate) {
			$receiver->comments[] = array('name' => $commentobj->getname(),
																		'email' => $commentobj->getEmail(),
																		'website' => $commentobj->getWebsite(),
																		'comment' => $commentobj->getComment(),
																		'date' => $commentobj->getDateTime(),
																		'custom_data' => $commentobj->getCustomData());
		}
		$class = strtolower(get_class($receiver));
		switch ($class) {
			case "album":
				$url = "album=" . pathurlencode($receiver->name);
				$ur_album = getUrAlbum($receiver);
				if ($moderate) {
					$action = sprintf(gettext('A comment has been placed in moderation on your album "%1$s".'), $receiver->name);
				} else {
					$action = sprintf(gettext('A comment has been posted on your album "%1$s".'), $receiver->name);
				}
				break;
			case "zenpagenews":
				$url = "p=news&title=" . urlencode($receiver->getTitlelink());
				if ($moderate) {
					$action = sprintf(gettext('A comment has been placed in moderation on your article "%1$s".'), $receiver->getTitlelink());
				} else {
					$action = sprintf(gettext('A comment has been posted on your article "%1$s".'), $receiver->getTitlelink());
				}
				break;
			case "zenpagepage":
				$url = "p=pages&title=" . urlencode($receiver->getTitlelink());
				if ($moderate) {
					$action = sprintf(gettext('A comment has been placed in moderation on your page "%1$s".'), $receiver->getTitlelink());
				} else {
					$action = sprintf(gettext('A comment has been posted on your page "%1$s".'), $receiver->getTitlelink());
				}
				break;
			default: // all image types
				$url = "album=" . pathurlencode($receiver->album->name) . "&image=" . urlencode($receiver->filename);
			$album = $receiver->getAlbum();
			$ur_album = getUrAlbum($album);
			if ($moderate) {
				$action = sprintf(gettext('A comment has been placed in moderation on your image "%1$s" in the album "%2$s".'), $receiver->getTitle(), $receiver->getAlbumName());
			} else {
				$action = sprintf(gettext('A comment has been posted on your image "%1$s" in the album "%2$s".'), $receiver->getTitle(), $receiver->getAlbumName());
			}
			break;
		}
		if (($whattocheck & COMMENT_SEND_EMAIL)) {
			$message = $action . "\n\n" .
			sprintf(gettext('Author: %1$s'."\n".'Email: %2$s'."\n".'Website: %3$s'."\n".'Comment:'."\n\n".'%4$s'),$commentobj->getname(), $commentobj->getEmail(), $commentobj->getWebsite(), $commentobj->getComment()) . "\n\n" .
			sprintf(gettext('You can view all comments about this item here:'."\n".'%1$s'), 'http://' . $_SERVER['SERVER_NAME'] . WEBPATH . '/index.php?'.$url) . "\n\n" .
			sprintf(gettext('You can edit the comment here:'."\n".'%1$s'), 'http://' . $_SERVER['SERVER_NAME'] . WEBPATH . '/' . ZENFOLDER . '/admin-comments.php?page=editcomment&id='.$commentobj->getID());
			$emails = array();
			$admin_users = $_zp_authority->getAdministrators();
			foreach ($admin_users as $admin) {
				// mail anyone with full rights
				if (!empty($admin['email']) && (($admin['rights'] & ADMIN_RIGHTS) ||
				(($admin['rights'] & (MANAGE_ALL_ALBUM_RIGHTS | COMMENT_RIGHTS)) == (MANAGE_ALL_ALBUM_RIGHTS | COMMENT_RIGHTS)))) {
					$emails[] = $admin['email'];
					unset($admin_users[$admin['id']]);
				}
			}
			if($type === "images" OR $type === "albums") {
				// mail to album admins
				$id = $ur_album->getID();
				$sql = 'SELECT `adminid` FROM '.prefix('admin_to_object').' WHERE `objectid`='.$id.' AND `type` LIKE "album%"';
				$result = query($sql);
				if ($result) {
					while ($anadmin = db_fetch_assoc($result)) {
						$id = $anadmin['adminid'];
						if (array_key_exists($id,$admin_users)) {
							$admin = $admin_users[$id];
							if (($admin['rights'] & COMMENT_RIGHTS) && !empty($admin['email'])) {
								$emails[] = $admin['email'];
							}
						}
					}
					db_free_result($result);
				}
			}
			$on = gettext('Comment posted');
			$result = zp_mail("[" . $_zp_gallery->getTitle() . "] $on", $message, $emails);
			if ($result) {
				$commentobj->setInModeration(-12);
				$commentobj->comment_error_text = $result;
			}
		}
	}
	return $commentobj;
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
					<div id="comment_toggle"><!-- place holder for toggle button --></div>
					<script type="text/javascript">
						// <!-- <![CDATA[
						function toggleComments(hide) {
							if (hide) {
								$('div.comment').hide();
								$('.Pagination').hide();
								$('#comment_toggle').html('<button type="button" onclick="javascript:toggleComments(false);"><?php echo gettext('show comments');?></button>');
							} else {
								$('div.comment').show();
								$('.Pagination').show();
								$('#comment_toggle').html('<button type="button" onclick="javascript:toggleComments(true);"><?php echo gettext('hide comments');?></button>');
							}
						}
						$(document).ready(function() {
							toggleComments(window.location.hash.search(/#zp_comment_id_/));
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
			<?php
			while (next_comment($desc_order)) {
				if (!getOption('comment_form_showURL')) {
					$_zp_current_comment['website'] = '';
				}
				?>
				<div class="comment" <?php echo $display; ?>>
					<div class="commentinfo">
						<h4 id="zp_comment_id_<?php echo $_zp_current_comment['id']; ?>"><?php	printCommentAuthorLink(); ?>: on <?php echo getCommentDateTime(); printEditCommentLink(gettext('Edit'), ', ', ''); ?></h4>
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

/**
 *
 * checks if comments are allowed and then processes them if so
 * @param string $error
 */
function comment_form_postcomment($error) {
	global $_zp_current_album, $_zp_current_image, $_zp_current_zenpage_news, $_zp_current_zenpage_page;
	if (( (commentsAllowed('comment_form_albums') && in_context(ZP_ALBUM) && !in_context(ZP_IMAGE) && $_zp_current_album->getCommentsAllowed()) ||
				(commentsAllowed('comment_form_images') && in_context(ZP_IMAGE) && $_zp_current_image->getCommentsAllowed()) ||
				(commentsAllowed('comment_form_articles') && in_context(ZP_ZENPAGE_NEWS_ARTICLE) && $_zp_current_zenpage_news->getCommentsAllowed()) ||
				(commentsAllowed('comment_form_pages') && in_context(ZP_ZENPAGE_PAGE) && $_zp_current_zenpage_page->getCommentsAllowed()) )
				){
		$error = comment_form_handle_comment();
	}
	return $error;
}

/**
 *
 * Handles the POSTing of a comment
 * @return NULL|boolean
 */
function comment_form_handle_comment() {
	global $_zp_current_image, $_zp_current_album, $_zp_comment_stored, $_zp_current_zenpage_news, $_zp_current_zenpage_page;
	$activeImage = false;
	$comment_error = 0;
	$cookie = zp_getCookie('zenphoto_comment');
	if (isset($_POST['comment'])) {
		if(isset($_POST['username']) && !empty($_POST['username'])) {
			return false;
		}
		if ((in_context(ZP_ALBUM) || in_context(ZP_ZENPAGE_NEWS_ARTICLE) || in_context(ZP_ZENPAGE_PAGE))) {
			if (isset($_POST['name'])) {
				$p_name = sanitize($_POST['name'],3);
			} else {
				$p_name = NULL;
			}
			if (isset($_POST['email'])) {
				$p_email = sanitize($_POST['email'], 3);
				if (!is_valid_email_zp($p_email)) {
					$p_email = NULL;
				}
			} else {
				$p_email = NULL;
			}
			if (isset($_POST['website'])) {
				$p_website = sanitize($_POST['website'], 3);
				if (!isValidURL($p_website)) {
					$p_website = NULL;
				}
			} else {
				$p_website = NULL;
			}
			if (isset($_POST['comment'])) {
				$p_comment = sanitize($_POST['comment'], 1);
			} else {
				$p_comment = '';
			}
			$p_server = getUserIP();
			if (isset($_POST['code'])) {
				$code1 = sanitize($_POST['code'], 3);
				$code2 = sanitize($_POST['code_h'], 3);
			} else {
				$code1 = '';
				$code2 = '';
			}
			$p_private = isset($_POST['private']);
			$p_anon = isset($_POST['anon']);

			if (in_context(ZP_IMAGE) AND in_context(ZP_ALBUM)) {
				$commentobject = $_zp_current_image;
				$redirectTo = $_zp_current_image->getImageLink();
			} else if (!in_context(ZP_IMAGE) AND in_context(ZP_ALBUM)){
				$commentobject = $_zp_current_album;
				$redirectTo = $_zp_current_album->getAlbumLink();
			} else 	if (in_context(ZP_ZENPAGE_NEWS_ARTICLE)) {
				$commentobject = $_zp_current_zenpage_news;
				$redirectTo = FULLWEBPATH . '/index.php?p=news&title='.$_zp_current_zenpage_news->getTitlelink();
			} else if (in_context(ZP_ZENPAGE_PAGE)) {
				$commentobject = $_zp_current_zenpage_page;
				$redirectTo = FULLWEBPATH . '/index.php?p=pages&title='.$_zp_current_zenpage_page->getTitlelink();
			}
			$commentadded = $commentobject->addComment($p_name, $p_email, $p_website, $p_comment,
			$code1, $code2,	$p_server, $p_private, $p_anon);

			$comment_error = $commentadded->getInModeration();
			$_zp_comment_stored = array($commentadded->getName(), $commentadded->getEmail(), $commentadded->getWebsite(), $commentadded->getComment(), false,
			$commentadded->getPrivate(), $commentadded->getAnon(), $commentadded->getCustomData());
			if (isset($_POST['remember'])) $_zp_comment_stored[4] = true;
			if (!$comment_error) {
				if (isset($_POST['remember'])) {
					// Should always re-cookie to update info in case it's changed...
					$_zp_comment_stored[3] = ''; // clear the comment itself
					zp_setCookie('zenphoto_comment', implode('|~*~|', $_zp_comment_stored), NULL, '/');
				} else {
					zp_clearCookie('zenphoto_comment', '/');
				}
				//use $redirectTo to send users back to where they came from instead of booting them back to the gallery index. (default behaviour)
				if (!isset($_SERVER['SERVER_SOFTWARE']) || strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'microsoft-iis') === false) {
					// but not for Microsoft IIS because that server fails if we redirect!
					header('Location: ' . $redirectTo . '#zp_comment_id_' . $commentadded->getId());
					exitZP();
				}
			} else {
				$comment_error++;
				if ($activeImage !== false AND !in_context(ZP_ZENPAGE_NEWS_ARTICLE) AND !in_context(ZP_ZENPAGE_PAGE)) {
					// tricasa hack? Set the context to the image on which the comment was posted
					$_zp_current_image = $activeImage;
					$_zp_current_album = $activeImage->getAlbum();
					add_context(ZP_ALBUM | ZP_INDEX);
				}
			}
		}
		return $commentadded->comment_error_text;
	} else if (!empty($cookie)) {
		// Comment form was not submitted; get the saved info from the cookie.
		$_zp_comment_stored = explode('|~*~|', stripslashes($cookie));
		$_zp_comment_stored[4] = true;
		if (!isset($_zp_comment_stored[5])) $_zp_comment_stored[5] = false;
		if (!isset($_zp_comment_stored[6])) $_zp_comment_stored[6] = false;
		if (!isset($_zp_comment_stored[7])) $_zp_comment_stored[7] = false;
	} else {
		$_zp_comment_stored = array('','','', '', false, false, false, false);
	}
	return false;
}

?>
