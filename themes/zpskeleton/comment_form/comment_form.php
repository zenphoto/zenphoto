					<form id="commentform" action="#" method="post">
						<input type="hidden" name="comment" value="1" />
						<input type="hidden" name="remember" value="1" />
						<?php
						printCommentErrors();
						$required = false;
						
						if ($req = getOption('comment_name_required')) {
							if ($req == 'required') {
								$star = "*";
								$required = true;
							} else {
								$star = '';
							} ?>
							<label>
								<?php printf(gettext("%sName:"),$star); ?>
								<?php if (getOption('comment_form_anon') && !$disabled['anon']) { ?>
									(<input type="checkbox" name="anon" value="1"<?php if ($stored['anon']) echo ' checked="checked"'; echo $disabled['anon']; ?> /> <?php echo gettext(" <em>anonymous</em> "); ?>)
								<?php } ?>
							</label>
							<input type="text" id="name" name="name" size="22" value="<?php echo html_encode($stored['name']);?>" class="inputbox" />
						<?php }
						
						if ($req = getOption('comment_email_required')) {
							if ($req == 'required') {
								$star = "*";
								$required = true;
							} else {
								$star = '';
							} ?>
							<label><?php printf(gettext("%sE-Mail:"),$star); ?></label>
							<input type="text" id="email" name="email" size="22" value="<?php echo html_encode($stored['email']);?>" class="inputbox" />
						<?php }

						if ($req = getOption('comment_web_required')) {
							if ($req == 'required') {
								$star = "*";
								$required = true;
							} else {
								$star = '';
							} ?>
							<label><?php printf(gettext("%sSite:"),$star); ?></label>
							<input type="text" id="website" name="website" size="22" value="<?php echo html_encode($stored['website']);?>" class="inputbox" />
						<?php }
						
						if ($req = getOption('comment_form_addresses')) {
							if ($req == 'required') {
								$star = '*';
								$required = true;
							} else {
								$star = '';
							} ?>
							<label><?php printf(gettext('%sStreet:'),$star); ?></label>
							<input type="text" name="0-comment_form_street" id="comment_form_street" class="inputbox" size="22" value="<?php echo html_encode($stored['street']); ?>" />
							<label><?php printf(gettext('%sCity:'),$star); ?></label>
							<input type="text" name="0-comment_form_city" id="comment_form_city" class="inputbox" size="22" value="<?php echo html_encode($stored['city']); ?>" />
							<label><?php printf(gettext('%sState:'),$star); ?></label>
							<input type="text" name="0-comment_form_state" id="comment_form_state-0" class="inputbox" size="22" value="<?php echo html_encode($stored['state']); ?>" />
							<label><?php printf(gettext('%sCountry:'),$star); ?></label>
							<input type="text" name="comment_form_country" id="comment_form_country-0" class="inputbox" size="22" value="<?php echo html_encode($stored['country']); ?>" />
							<label><?php printf(gettext('%sPostal code:'),$star); ?></label>
							<input type="text" id="comment_form_postal-0" name="0-comment_form_postal" class="inputbox" size="22" value="<?php echo html_encode($stored['postal']); ?>" />
						<?php }
						
						if($required) { ?>
							<div><strong><?php echo gettext('*Required fields'); ?></strong></div>
						<?php }
						
						if (getOption('Use_Captcha')) { 
							$captcha = $_zp_captcha->getCaptcha(gettext("Enter CAPTCHA:")); ?>
	 						<?php if (isset($captcha['html']) && isset($captcha['input'])) echo $captcha['html']; ?>
							<?php if (isset($captcha['input'])) {
								echo $captcha['input'];
							} else {
								if (isset($captcha['html'])) echo $captcha['html'];
							}
							if (isset($captcha['hidden'])) echo $captcha['hidden'];
						}
						
						if (getOption('comment_form_private') && !$disabled['private']) { ?>
							<input type="checkbox" name="private" value="1"<?php if ($stored['private']) echo ' checked="checked"'; ?> />
							<?php echo gettext("Private comment (don't publish)"); ?>
						<?php } ?>

						<textarea name="comment" rows="6" cols="42" class="textarea_inputbox"><?php echo $stored['comment']; echo $disabled['comment']; ?></textarea>
						<input type="submit" class="pushbutton" value="<?php echo gettext('Add Comment'); ?>" />
					</form>
