<?php
/*
 * Guts of the security options tab
 */
$optionRights = ADMIN_RIGHTS;

function saveOptions() {
	global $_zp_gallery, $_zp_authority, $zp_cfg, $_configMutex;

	$notify = $returntab = NULL;
	$protocol = sanitize($_POST['server_protocol'], 3);
	if (getOption('server_protocol') != $protocol) {
		switch ($protocol) {
			case'https_admin':
			case'https':
				if (!secureServer()) {
					//don't do it if we are not running secure!
					break;
				}
			case'http':
				setOption('server_protocol', $protocol);
				$_configMutex->lock();
				$zp_cfg = @file_get_contents(SERVERPATH . '/' . DATA_FOLDER . '/' . CONFIGFILE);
				$zp_cfg = updateConfigItem('server_protocol', $protocol, $zp_cfg);
				storeConfig($zp_cfg);
				$_configMutex->unlock();
				break;
		}
	}
	if (method_exists($_zp_authority, 'handleOptionSave')) {
		$_zp_authority->handleOptionSave(NULL, NULL);
	}

	$_zp_gallery->setUserLogonField(isset($_POST['login_user_field']));
	if ($protocol == 'http') {
		zp_clearCookie("zenphoto_ssl");
	}
	setOption('IP_tied_cookies', (int) isset($_POST['IP_tied_cookies']));
	setOption('obfuscate_cache', (int) isset($_POST['obfuscate_cache']));
	setOption('image_processor_flooding_protection', (int) isset($_POST['image_processor_flooding_protection']));
	$_zp_gallery->save();
	$returntab = "&tab=security";

	return array($returntab, $notify, NULL, NULL, NULL);
}

function getOptionContent() {
	global $_zp_gallery, $zp_cfg, $_configMutex, $_zp_authority;
	?>
	<div id="tab_security" class="tabbox">
		<form class="dirtylistening" onReset="setClean('form_options');" id="form_options" action="?action=saveoptions" method="post" autocomplete="off" >
			<?php XSRFToken('saveoptions'); ?>
			<input type="hidden" name="saveoptions" value="security" />
			<table>
				<tr>
					<td colspan="100%">
						<p class="buttons">
							<button type="submit" value="<?php echo gettext('save') ?>">
								<?php echo CHECKMARK_GREEN; ?>
								<strong><?php echo gettext("Apply"); ?></strong>
							</button>
							<button type="reset" value="<?php echo gettext('reset') ?>">
								<?php echo CROSS_MARK_RED; ?>
								<strong><?php echo gettext("Reset"); ?></strong>
							</button>
						</p>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext("Server protocol"); ?></td>
					<td class="option_value">
						<?php
						if (secureServer()) {
							?>

							<script type="text/javascript">
								function warn_http(sel) {
									if (sel.value == 'http') {
										alert('<?php echo gettext('Chanaging to http may require clearing secured authentication cookies!'); ?>');
									}

								}
							</script>
							<?php
						}
						?>
						<select id="server_protocol" name="server_protocol"<?php if (secureServer()) echo ' onchange="warn_http(this);"' ?>>
							<option value="http"<?php
							if (SERVER_PROTOCOL == 'http' && !secureServer())
								echo 'selected = "selected"';
							?>>http</option>
							<option value="https"<?php
							if (secureServer()) {
								if (SERVER_PROTOCOL == 'https')
									echo ' selected="selected"';
							} else {
								echo ' disabled="disabled"';
							}
							?>>https</option>
							<option value="https_admin"<?php
							if (secureServer()) {
								if (SERVER_PROTOCOL != 'https')
									echo ' selected="selected"';
							} else {
								echo ' disabled="disabled"';
							}
							?>><?php echo gettext('secure admin'); ?></option>
						</select>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<p><?php printf(gettext("Normally this option should be set to <em>http</em>. If you are running a secure server, change this to <em>https</em>. Select <em>%s</em> if you need only to insure secure access to <code>admin</code> pages."), gettext('secure admin')); ?></p>
								<p class="notebox"><?php
									printf(gettext("<strong>Note:</strong>" .
																	"<br /><br />Login from the front-end user login form is secure only if <em>https</em> is selected." .
																	"<br /><br /><em>https</em> and <em>%s</em> are disabled unless you have used a secure link to the administrative pages. " .
																	'This is to insure that the site supports secure protocols. Otherwise if one of these options were set you could be locked out of your site.'), gettext('secure admin'));
									?>
								</p>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext('Cookie security') ?></td>
					<td class="option_value">
						<label><input type="checkbox" name="IP_tied_cookies" value="1" <?php checked(1, getOption('IP_tied_cookies')); ?> /></label>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext('Tie cookies to the IP address of the browser.'); ?>
								<p class="notebox">
									<?php
									if (!getOption('IP_tied_cookies')) {
										echo ' ' . gettext('<strong>Note</strong>: If your browser does not present a consistant IP address during a session you may not be able to log into your site when this option is enabled.') . ' ';
									}
									echo gettext(' You <strong>WILL</strong> have to login after changing this option.');
									if (!getOption('IP_tied_cookies')) {
										echo ' ' . gettext('If you set the option and cannot login, you will have to restore your database to a point when the option was not set, so you might want to backup your database first.');
									}
									?>
								</p>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext('Obscure cache filenames'); ?></td>
					<td class="option_value">
						<label><input type="checkbox" name="obfuscate_cache" id="obfuscate_cache" value="1" <?php checked(1, getOption('obfuscate_cache')); ?> /></label>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext('Cause the filename of cached items to be obscured. This makes it difficult for someone to "guess" the name in a URL.'); ?>
							</div>
						</span>
					</td>
				</tr>
				<tr>
					<td class="option_name"><?php echo gettext('Image Processor security') ?></td>
					<td class="option_value">
						<label><input type="checkbox" name="image_processor_flooding_protection" value="1" <?php checked(1, getOption('image_processor_flooding_protection')); ?> /></label>
					</td>
					<td class="option_desc">
						<span class="option_info">
							<?php echo INFORMATION_BLUE; ?>
							<div class="option_desc_hidden">
								<?php echo gettext('Add a security parameter to image processor URIs to prevent denial of service attacks requesting arbitrary sized images.'); ?>
							</div>
						</span>
					</td>
				</tr>
				<?php
				if (GALLERY_SECURITY == 'public') {
					$disable = $_zp_gallery->getUser() || getOption('search_user') || getOption('protected_image_user') || getOption('downloadList_user');
					?>
					<tr class="public_gallery">
						<td class="option_name"><?php echo gettext('User name'); ?></td>
						<td class="option_value">
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
						<td class="option_desc">
							<span class="option_info">
								<?php echo INFORMATION_BLUE; ?>
								<div class="option_desc_hidden">
									<?php
									echo gettext('If enabled guest logon forms will include the <em>User Name</em> field. This allows users to logon from the form.');
									if ($disable) {
										echo '<p class = "notebox">' . gettext('<strong>Note</strong>: This field is required because one or more of the <em>Guest</em> passwords has a user name associated.') . '</p>';
									}
									?>
								</div>
							</span>
						</td>
					</tr>
					<?php
				} else {
					?>
					<input type="hidden" name="login_user_field" id="login_user_field"	value="<?php echo $_zp_gallery->getUserLogonField(); ?>" />
					<?php
				}
				$authority = new ReflectionClass('Zenphoto_Authority');
				$file = basename($authority->getFileName());
				if ($file != 'class-auth.php') {
					?>
					<tr>
						<td colspan="100%"><?php printf(gettext('Authentication authority: <strong>%s</strong>'), stripSuffix($file)); ?></td>
					</tr>
					<?php
				}
				$supportedOptions = $_zp_authority->getOptionsSupported();
				if (count($supportedOptions) > 0) {
					?>
					<tr>
						<?php customOptions($_zp_authority, ''); ?>
					</tr>
					<?php
				}
				?>
				<tr>
					<td colspan="100%">
						<p class="buttons">
							<button type="submit" value="<?php echo gettext('save') ?>">
								<?php echo CHECKMARK_GREEN; ?>
								<strong><?php echo gettext("Apply"); ?></strong>
							</button>
							<button type="reset" value="<?php echo gettext('reset') ?>">
								<?php echo CROSS_MARK_RED; ?>
								<strong><?php echo gettext("Reset"); ?></strong>
							</button>
						</p>
					</td>
				</tr>
			</table> <!-- security page table -->
		</form>
	</div>
	<!-- end of tab_security div -->
	<?php
}
