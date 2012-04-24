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
                	//i+2 needed as somehow nth-children needs to start that way...
                 	newcontent += '<div class="comment">'+$('#comments div.comment:nth-child('+(i+2)+')').html()+'</div>';
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
                // count entries inside the hidden content
                var num_entries = $('#comments div.comment').length;
                // Create content inside pagination element
                $(".Pagination").pagination(num_entries, {
                		prev_text: "<?php echo gettext('prev'); ?>",
                		next_text: "<?php echo gettext('next'); ?>",
                    callback: pageselectCallback,
                    load_first_page:true,
                    items_per_page:<?php echo getOption('comment_form_comments_per_page'); ?> // Show only one item per page
                });
             }

            // When document is ready, initialize pagination
            $(document).ready(function(){
                initPagination();
            });

        </script>
	<?php
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
						<input type="text" name="0-comment_form_street" id="comment_form_street" class="inputbox" size="40" value="'.$address['street'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						sprintf(gettext('City%s:'),$required).
					'</td>
					<td>
						<input type="text" name="0-comment_form_city" id="comment_form_city" class="inputbox" size="40" value="'.$address['city'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						sprintf(gettext('State%s:'),$required).
				 '</td>
					<td>
						<input type="text" name="0-comment_form_state" id="comment_form_state" class="inputbox" size="40" value="'.$address['state'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						sprintf(gettext('Country%s:'),$required).
				 '</td>
					<td>
						<input type="text" name="0-comment_form_country" id="comment_form_country" class="inputbox" size="40" value="'.$address['country'].'">
					</td>
				</tr>
				<tr>
					<td>'.
						sprintf(gettext('Postal code%s:'),$required).
					'</td>
					<td>
						<input type="text" name="0-comment_form_postal" id="comment_form_postal" class="inputbox" size="40" value="'.$address['postal'].'">
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
			'<td'.((!empty($background)) ? ' style="'.$background.'"':'').'></td>'.
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

?>