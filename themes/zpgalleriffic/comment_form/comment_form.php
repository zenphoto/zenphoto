					<form id="commentform" action="#" method="post">
						<input type="hidden" name="comment" value="1" />
						<input type="hidden" name="remember" value="1" />
						<?php
						printCommentErrors();
						?>
						<table border="0">
							<tr>
								<td>
									<?php
									echo gettext("Name:");
									if (getOption('comment_form_anon') && !$disabled['anon']) {
										?>
										<label>(<input type="checkbox" name="anon" value="1"<?php if ($stored['anon']) echo ' checked="checked"'; echo $disabled['anon']; ?> /> <?php echo gettext("<em>anonymous</em>"); ?>)</label>
										<?php
									}
									?>
								</td>
								<td>
									<?php
									if ($disabled['name']) {
										?>
										<div class="disabled_input" style="background-color:LightGray;color:black;">
											<?php
											echo html_encode($stored['name']);
											?>
											<input type="hidden" id="name" name="name" value="<?php echo html_encode($stored['name']);?>" />
										</div>
										<?php
									} else {
										?>
										<input type="text" id="name" name="name" size="22" value="<?php echo html_encode($stored['name']);?>" class="inputbox" />
										<?php
									}
									?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo gettext("E-Mail:"); ?>
								</td>
								<td>
									<?php
									if ($disabled['email']) {
										?>
										<div class="disabled_input" style="background-color:LightGray;color:black;">
											<?php
											echo html_encode($stored['email']);
											?>
											<input type="hidden" id="email" name="email" value="<?php echo html_encode($stored['email']);?>" />
										</div>
										<?php
									} else {
										?>
										<input type="text" id="email" name="email" size="22" value="<?php echo html_encode($stored['email']);?>" class="inputbox" />
										<?php
									}
									?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo gettext("Site:"); ?>
								</td>
								<td>
									<?php
									if ($disabled['website']) {
										?>
										<div class="disabled_input" style="background-color:LightGray;color:black;">
											<?php
											echo html_encode($stored['website']);
											?>
											<input type="hidden" id="website" name="website" value="<?php echo html_encode($stored['website']);?>" />
										</div>
										<?php
									} else {
										?>
										<input type="text" id="website" name="website" size="22" value="<?php echo html_encode($stored['website']);?>" class="inputbox" />
										<?php
									}
									?>
								</td>
							</tr>
							<?php
							if (getOption('comment_form_addresses')) {
								?>
								<tr>
									<td>
										<?php echo gettext('street:'); ?>
									</td>
									<td>
										<?php
											if ($disabled['street']) {
												?>
												<div class="disabled_input" style="background-color:LightGray;color:black;">
													<?php
													echo html_encode($stored['street']);
													?>
														<input type="hidden" id="comment_form_street-0" name="0-comment_form_street" value="<?php echo html_encode($stored['street']);?>" />
												</div>
												<?php
											} else {
												?>
												<input type="text" name="0-comment_form_street" id="comment_form_street" class="inputbox" size="22" value="<?php echo html_encode($stored['street']); ?>" />
												<?php
											}
										?>
									</td>
								</tr>
								<tr>
									<td>
										<?php echo gettext('city:'); ?>
									</td>
									<td>
										<?php
										if ($disabled['city']) {
											?>
											<div class="disabled_input"  style="background-color:LightGray;color:black;">
												<?php
												echo html_encode($stored['city']);
												?>
												<input type="hidden" id="comment_form_city-0" name="0-comment_form_city" value="<?php echo html_encode($stored['city']);?>" />
											</div>
											<?php
										} else {
											?>
											<input type="text" name="0-comment_form_city" id="comment_form_city" class="inputbox" size="22" value="<?php echo html_encode($stored['city']); ?>" />
											<?php
										}
										?>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext('state:'); ?></td>
									<td>
										<?php
										if ($disabled['state']) {
											?>
											<div class="disabled_input" style="background-color:LightGray;color:black;">
												<?php
												echo html_encode($stored['state']);
												?>
												<input type="hidden" name="0-comment_form_state" id="comment_form_state-0" value="<?php echo html_encode($stored['state']);?>" />
											</div>
											<?php
										} else {
											?>
											<input type="text" name="0-comment_form_state" id="comment_form_state-0" class="inputbox" size="22" value="<?php echo html_encode($stored['state']); ?>" />
											<?php
										}
										?>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext('country:'); ?></td>
									<td>
										<?php
										if ($disabled['country']) {
											?>
											<div class="disabled_input"  style="background-color:LightGray;color:black;">
												<?php
												echo html_encode($stored['country']);
												?>
												<input type="hidden" id="comment_form_country" name="0-comment_form_country" value="<?php echo html_encode($stored['country']);?>" />
											</div>
											<?php
										} else {
											?>
											<input type="text" name="comment_form_country" id="comment_form_country-0" class="inputbox" size="22" value="<?php echo html_encode($stored['country']); ?>" />
											<?php
										}
										?>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext('postal code:'); ?></td>
									<td>
										<?php
										if ($disabled['postal']) {
											?>
											<div class="disabled_input"  style="background-color:LightGray;color:black;">
												<?php
												echo html_encode($stored['postal']);
												?>
												<input type="hidden" name="0-comment_form_postal" value="<?php echo html_encode($stored['postal']);?>" />
											</div>
											<?php
										} else {
											?>
											<input type="text" id="comment_form_postal-0" name="0-comment_form_postal" class="inputbox" size="22" value="<?php echo html_encode($stored['postal']); ?>" />
											<?php
										}
										?>
									</td>
								</tr>
								<?php
								}
							if (getOption('Use_Captcha')) {
 								$captcha = $_zp_captcha->getCaptcha(); ?>
 								<tr>
	 								<td>
	 									<?php echo gettext("Enter CAPTCHA:"); ?>
	 								</td>
	 								<td>
	 									<?php if (isset($captcha['html']) && isset($captcha['input'])) echo $captcha['html']; ?>
										<?php if (isset($captcha['input'])) {
											echo $captcha['input'];
										} else {
											if (isset($captcha['html'])) echo $captcha['html'];
										}
										if (isset($captcha['hidden'])) echo $captcha['hidden'];
										?>
	 								</td>
 								</tr>
								<?php
							}
						
							if (getOption('comment_form_private') && !$disabled['private']) {
								?>
								<tr>
									<td colspan="2">
										<label>
											<input type="checkbox" name="private" value="1"<?php if ($stored['private']) echo ' checked="checked"'; ?> />
											<?php echo gettext("Private comment (don't publish)"); ?>
										</label>
									</td>
								</tr>
								<?php
							}
							?>
						</table>
						<textarea name="comment" rows="7" cols="42" class="textarea_inputbox"><?php echo $stored['comment']; echo $disabled['comment']; ?></textarea>
						<br />
						<input type="submit" class="pushbutton"  value="<?php echo gettext('Add Comment'); ?>" />
					</form>