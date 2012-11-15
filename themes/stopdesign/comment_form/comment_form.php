<?php

// force UTF-8 Ø

global $_zp_themeroot;
?>
<p class="mainbutton" id="addcommentbutton"><a href="#addcomment" class="btn"><img src="<?php echo $_zp_themeroot ?>/images/btn_add_a_comment.gif" alt="" width="116" height="21" /></a></p>
	<!-- BEGIN #addcomment -->
	<div id="addcomment">
		<script type="text/javascript">
			// <!-- <![CDATA[
			$(function() {
  			window.onload = initCommentState;
			});
			// ]]> -->
		</script>
		<h2><?php echo gettext("Add a comment") ?></h2>
		<form method="post" action="#" id="comments-form">
			<input type="hidden" name="comment" value="1" />
			<input type="hidden" name="remember" value="1" />
			<table>
				<?php
				if ($req = getOption('comment_name_required')) {
					if ($req == 'required') {
						$star = '*';
						$required = true;
					} else {
						$star = '';
					}
					?>
					<tr valign="top" align="left" id="row-name">
						<th><?php printf(gettext('%sName:'),$star); ?></th>
						<td>
							<?php
							if ($disabled['name']) {
								?>
								<input tabindex="1" id="name" name="name" class="text" type="hidden" value="<?php echo html_encode($stored['name']);?>" />
								<?php echo html_encode($stored['name']);?>
								<?php
							} else {
								?>
								<input tabindex="1" id="name" name="name" class="text" value="<?php echo html_encode($stored['name']);?>" />
								<?php
							}
							?>
							<label>
							(<input type="checkbox" name="anon" value="1"<?php if ($stored['anon']) echo ' checked="checked"'; ; ?> /> <?php echo gettext('<em>anonymous</em>'); ?>)
							</label>
						</td>
					</tr>
					<?php
				}
				if ($req = getOption('comment_email_required')) {
					if ($req == 'required') {
						$star = '*';
						$required = true;
					} else {
						$star = '';
					}
					?>
					<tr valign="top" align="left" id="row-email">
						<th><?php printf(gettext('Email%s:'),$star); ?></th>
						<td>
							<?php
							if ($disabled['email']) {
								?>
								<input tabindex="2" id="email" name="email" class="text" type="hidden" value="<?php echo html_encode($stored['email']);?>" />
								<?php echo html_encode($stored['email']);?>
								<?php
							} else {
								?>
								<input tabindex="2" id="email" name="email" class="text" value="<?php echo html_encode($stored['email']);?>" />
								<?php
							}
							?>
							<em><?php echo gettext("(not displayed)"); ?></em>
						</td>
					</tr>
					<?php
				}
				if ($req = getOption('comment_web_required')) {
					if ($req == 'required') {
						$star = '*';
						$required = true;
					} else {
						$star = '';
					}
					?>
					<tr valign="top" align="left">
						<th><?php printf(gettext('URL%s:'),$star); ?></th>
						<td>
							<?php
							if ($disabled['website']) {
								?>
								<input tabindex="3" name="website" id="website" class="text" type="hidden" value="<?php echo html_encode($stored['website']);?>" />
								<?php echo html_encode($stored['website']);?>
								<?php
							} else {
								?>
								<input tabindex="3" name="website" id="website" class="text" value="<?php echo html_encode($stored['website']);?>" />
								<?php
							}
							?>
						</td>
					</tr>
					<?php
				}
				if ($req = getOption('comment_form_addresses')) {
					if ($req == 'required') {
						$star = '*';
						$required = true;
					} else {
						$star = '';
					}
				?>
				<tr>
					<th><?php printf(gettext('Street%s:'),$star); ?></th>
					<td>
						<?php
						if ($disabled['street']) {
							?>
							<input name="0-comment_form_street" id="comment_form_street" class="text" type="hidden" size="22" value="<?php echo html_encode($address['street']); ?>" />
							<?php echo html_encode($address['street']); ?>
							<?php
						} else {
							?>
							<input name="0-comment_form_street" id="comment_form_street" class="text" size="22" value="<?php echo html_encode($address['street']); ?>" />
							<?php
						}
						?>
					</td>
				</tr>
				<tr>
					<th><?php printf(gettext('City:%s'),$star); ?></th>
					<td>
						<?php
						if ($disabled['city']) {
							?>
							<input name="0-comment_form_city" id="comment_form_city" class="text" type="hidden" size="22" value="<?php echo html_encode($address['city']); ?>" />
							<?php echo html_encode($address['city']); ?>
							<?php
						} else {
							?>
							<input name="0-comment_form_city" id="comment_form_city" class="text" size="22" value="<?php echo html_encode($address['city']); ?>" />
							<?php
						}
						?>
					</td>
				</tr>
				<tr>
					<th><?php printf(gettext('State%s:'),$star); ?></th>
					<td>
						<?php
						if ($disabled['state']) {
							?>
							<input name="0-comment_form_state" id="comment_form_state" class="text" type="hidden" size="22" value="<?php echo html_encode($address['state']); ?>" />
							<?php echo html_encode($address['state']); ?>
							<?php
						} else {
							?>
							<input name="0-comment_form_state" id="comment_form_state" class="text" size="22" value="<?php echo html_encode($address['state']); ?>" />
							<?php
						}
						?>
					</td>
				</tr>
				<tr>
					<th><?php printf(gettext('Country:%s'),$star); ?></th>
					<td>
						<?php
						if ($disabled['country']) {
							?>
							<input name="comment_form_country" id="comment_form_country" class="text" type="hidden" size="22" value="<?php echo html_encode($address['country']); ?>" />
							<?php echo html_encode($address['country']); ?>
							<?php
						} else {
							?>
							<input name="comment_form_country" id="comment_form_country" class="text" size="22" value="<?php echo html_encode($address['country']); ?>" />
							<?php
						}
						?>
					</td>
				</tr>
				<tr>
					<th><?php printf(gettext('Postal code%s:'),$star); ?></th>
					<td>
						<?php
						if ($disabled['postal']) {
							?>
							<input name="0-comment_form_postal" id="comment_form_postal" class="text" size="22" type="hidden" value="<?php echo html_encode($address['postal']); ?>" />
							<?php echo html_encode($address['postal']); ?>
							<?php
						} else {
							?>
							<input name="0-comment_form_postal" id="comment_form_postal" class="text" size="22" value="<?php echo html_encode($address['postal']); ?>" />
							<?php
						}
						?>
					</td>
				</tr>
				<?php
				}
				if($required) {
					?>
					<tr><td colspan="2"><?php echo gettext('*Required fields'); ?></td></tr>
					<?php
				}
				if ($_zp_captcha && getOption('Use_Captcha')) {
					printCaptcha("<tr valign=\"top\" align=\"left\"><th>" .gettext('Enter CAPTCHA').' ', "</th><td>", "</td></tr>\n", 16);
				}
				?>
				<tr valign="top" align="left">
					<th><?php echo gettext('Private comment:'); ?></th>
					<td>
						<label>
						<input type="checkbox" name="private" value="1"<?php if ($stored['private']) echo ' checked="checked"'; ; ?> /> <?php echo gettext("(don't publish)"); ?>
						</label>
					</td>
				</tr>
				<?php
				?>
				<tr valign="top" align="left">
					<th><?php echo gettext('Comment:'); ?></th>
					<td></td>
				</tr>
				<tr>
					<td colspan="2"><textarea tabindex="4" id="comment" name="comment" class="textarea_inputbox" rows="10" cols="40"><?php echo $stored['comment']; ?></textarea></td>
				</tr>
				<tr valign="top" align="left">
					<td class="buttons" colspan="2">
						<!--<input type="submit" name="preview" tabindex="5" value="Preview" id="btn-preview" />-->
						<input type="submit" name="post" tabindex="6" value="<?php echo gettext('Post'); ?>" id="btn-post" />
						<p><?php echo gettext('Avoid clicking &ldquo;Post&rdquo; more than once.'); ?></p>
					</td>
				</tr>
			</table>
		</form>

	</div>
	<!-- END #addcomment -->