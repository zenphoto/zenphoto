<div id="tab_security" class="tabbox">
						<?php zp_apply_filter('admin_note', 'options', $subtab); ?>
						<form class="dirty-check" id="form_options" action="?action=saveoptions" method="post" autocomplete="off">
							<?php XSRFToken('saveoptions'); ?>
							<input type="hidden" name="savesecurityoptions" value="yes" />
							<table class="options">
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('save') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext("Server protocol:"); ?></td>
									<td width="350">
										<select id="server_protocol" name="server_protocol">
											<option value="http" <?php if (SERVER_PROTOCOL == 'http') echo 'selected="selected"'; ?>>http</option>
											<option value="https" <?php if (SERVER_PROTOCOL == 'https') echo 'selected="selected"'; ?>>https</option>
										</select>
									</td>
									<td>
										<p><?php echo gettext("Normally this option should be set to <em>http</em>. If you are running a secure server, change this to <em>https</em>."); ?></p>
										<p class="warningbox"><?php
											echo gettext('<strong>Warning:</strong> If you select <em>https</em> your server <strong>MUST</strong> support <em>https</em>. ' .
															'If you set <em>https</em> on a server which does not support <em>https</em> you will not be able to access the <code>admin</code> pages to reset the option! ' .
															'Your only possibility then is to set or add <code>$conf["server_protocol"] = "http";</code> to your <code>zenphoto.cfg.php</code> file .');
											?>
										</p>
									</td>
								</tr>
								<tr>
									<td><?php echo gettext('Cookie security') ?></td>
									<td>
										<label><input type="checkbox" name="IP_tied_cookies" value="1" <?php checked(1, getOption('IP_tied_cookies')); ?> /></label>
									</td>
									<td>
										<?php echo gettext('Tie cookies to the IP address of the browser.'); ?>
										<?php if (!getOption('IP_tied_cookies')) { ?>
										<div class="warningbox">
											<p>
												<?php echo gettext('<strong>Warning</strong>: If your browser does not present a consistant IP address during a session you may not be able to log into your site when this option is enabled.');?>
											</p>
											<p>
												<?php echo gettext('You <strong>WILL</strong> have to login after changing this option.'); ?>
											</p>
											<p>
												<?php gettext('If you set the option and cannot login, you will have to restore your database to a point when the option was not set, so you might want to backup your database first.'); ?>
											</p>
											<p><?php echo gettext('This will not work properly if Zenphoto is set to anonymize the IP address which is strongly advised for privacy concerns in many jurisdictions.'); ?>
											</p>
										</div>
										<?php } ?>
									</td>
								</tr>
								<tr>
									<td width="175"><?php echo gettext('Obscure cache filenames'); ?></td>
									<td width="350">
										<label><input type="checkbox" name="obfuscate_cache" id="obfuscate_cache" value="1" <?php checked(1, getOption('obfuscate_cache')); ?> /></label>
									</td>
									<td><?php echo gettext('Cause the filename of cached items to be obscured. This makes it difficult for someone to "guess" the name in a URL.'); ?></td>
								</tr>
								<tr>
									<td><?php echo gettext('Image Processor security') ?></td>
									<td>
										<label><input type="checkbox" name="image_processor_flooding_protection" value="1" <?php checked(1, getOption('image_processor_flooding_protection')); ?> /></label>
									</td>
									<td>
										<?php echo gettext('Add a security parameter to image processor URIs to prevent denial of service attacks requesting arbitrary sized images.'); ?>
									</td>
								</tr>
								<?php
								if (GALLERY_SECURITY == 'public') {
									$disable = $_zp_gallery->getUser() || getOption('search_user') || getOption('protected_image_user') || getOption('downloadList_user');
									?>
									<div class="public_gallery">
										<tr>
											<td><?php echo gettext('User name'); ?></td>
											<td>
												<label>
													<?php
													if ($disable) {
														?>
														<input type="hidden" name="login_user_field" value="1" />
														<input type="checkbox" name="login_user_field_disabled" id="login_user_field"
																	 value="1" checked="checked" disabled="disabled" />
																	 <?php
																 } else {
																	 ?>
														<input type="checkbox" name="login_user_field" id="login_user_field"
																	 value="1" <?php checked('1', $_zp_gallery->getUserLogonField()); ?> />
																	 <?php
																 }
																 ?>
												</label>
											</td>
											<td>
												<?php
												echo gettext('If enabled guest logon forms will include the <em>User Name</em> field. This allows <em>Zenphoto</em> users to logon from the form.');
												if ($disable) {
													echo '<p class="notebox">' . gettext('<strong>Note</strong>: This field is required because one or more of the <em>Guest</em> passwords has a user name associated.') . '</p>';
												}
												?>
											</td>
										</tr>
									</div>
									<?php
								} else {
									?>
									<input type="hidden" name="login_user_field" id="login_user_field"	value="<?php echo $_zp_gallery->getUserLogonField(); ?>" />
									<?php
								}
								?>
								<tr>
									<td width="175">
										<p><?php echo gettext('Anonymize IP'); ?></p>
									</td>
									<td width="350">
										<label>
											<?php
												$anonymize_ip = getOption('anonymize_ip');
												$anonymize_ip_levels = array(
													gettext('0 - No anonymizing') => 0,
													gettext('1 - Last fourth anonymized') => 1,
													gettext('2 - Last half anonymized') => 2,
													gettext('3 - Last three fourths anonymized') => 3,
													gettext('4 - Full anonymization, no IP stored') => 4
												);
											?>
											<select id="anonymize_ip" name="anonymize_ip">
												<?php	generateListFromArray(array($anonymize_ip), $anonymize_ip_levels, false, true); ?>
											</select>
											<?php echo gettext('Anonymize level'); ?>
										</label>
									</td>
									<td width="175">
										<p><?php echo gettext('Zenphoto stores the IP address of visitors on several occasions (e.g. rating, spam filtering, comment posting). '
														. 'In some jurisdictions like the EU and its GDPR the IP address is considered private information and therefore it is required to not store the full IP address or no IP at all.'
														. 'Choose your level of anonymization so parts are replaced by 0. This covers both IPv4 (1.1.1.0) and IPv6 (1:1:1:1:1:1:0:0) addresses.'); ?>
										</p>
									</td>
								</tr>
								<?php
								$data_policy_sharedtext = gettext('This is used by the official plugins <em>comment_form</em>, <em>contact_form</em> and <em>register_user</em> plugins if the data usage confirmation is enabled. Other plugins or usages must implement <code>getDataUsageNotice()/printDataUsageNotice()</code> specifially.');
								?>
								<tr>
									<td width="175">
										<p><?php echo gettext('Data privacy usage notice'); ?></p>
									</td>
									<td width="350">
										 <?php print_language_string_list(getOption('dataprivacy_policy_notice'), 'dataprivacy_policy_notice', true); ?>
									</td>
									<td width="175">
										<p><?php echo gettext('Here you can define the data usage confirmation notice that is recommended if your site is using forms submitting data in some jurisdictions like the EU and its GDPR. Leave empty to use the default text:'); ?></p>
										<blockquote><?php echo gettext('By using this form you agree with the storage and handling of your data by this website.'); ?></blockquote>
										<p class="notebox">
											<?php echo $data_policy_sharedtext; ?>
										</p>
									</td>
								</tr>
								<tr>
									<td width="175">
										<p><?php echo gettext('Data privacy policy page'); ?></p>
									</td>
									<td width="350">
										<?php printZenpagePageSelector('dataprivacy_policy_zenpage', 'dataprivacy_policy_custompage', false); ?>
										<p>
											<label>
											<?php print_language_string_list(getOption('dataprivacy_policy_customlinktext'), 'dataprivacy_policy_customlinktext'); ?>
											<br><?php echo gettext('Custom link text'); ?>
										</label>
										</p>
									</td>
									<td width="175">
										<p><?php echo gettext('Here you can define your data policy statement page that is recommended to have in jurisdictions like the EU and its GDPR.'); ?></p>
										<p><?php echo gettext('If the Zenpage CMS plugin is enabled and also its pages feature you can select one of its pages, otherwise enter a full custom page url manually which would also override the Zenpage page selection.'); ?></p>
										<p><?php echo gettext('Additionally you can define a custom text for the page link. If not set the default text <em>More info on our data privacy policy.</em> is used.'); ?></p>
										<p class="notebox">
											<?php echo $data_policy_sharedtext; ?>
										</p>
									</td>
								<tr>
									<?php
									$supportedOptions = $_zp_authority->getOptionsSupported();
									if (count($supportedOptions) > 0) {
										customOptions($_zp_authority, '');
									}
									?>
								</tr>
								<tr>
									<td colspan="3">
										<p class="buttons">
											<button type="submit" value="<?php echo gettext('save') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
											<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
										</p>
									</td>
								</tr>
							</table> <!-- security page table -->
						</form>
					</div>