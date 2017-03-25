<?php
/**
 *
 * A tool to send e-mails to all registered users who have provided an e-mail address.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage users
 */
if (defined('OFFSET_PATH')) {
	$plugin_is_filter = 5 | ADMIN_PLUGIN;
	$plugin_description = gettext("Provides a utility function to send e-mails to all users who have provided an e-mail address.");
	$plugin_author = "Malte Müller (acrylian)";

	zp_register_filter('admin_utilities_buttons', 'user_mailing_list_button');

	function user_mailing_list_button($buttons) {
		global $_zp_authority, $_zp_current_admin_obj;
		$button = array(
				'category' => gettext('Admin'),
				'enable' => false,
				'button_text' => gettext('User mailing list'),
				'formname' => 'user_mailing_list.php',
				'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/user_mailing_list.php',
				'icon' => 'images/icon_mail.png',
				'title' => gettext('There are no other registered users who have provided an e-mail address.'),
				'alt' => '',
				'hidden' => '',
				'rights' => ADMIN_RIGHTS
		);
		$currentadminuser = $_zp_current_admin_obj->getUser();
		$admins = $_zp_authority->getAdministrators();
		foreach ($admins as $admin) {
			if (!empty($admin['email']) && $currentadminuser != $admin['user']) {
				$button['enable'] = true;
				$button['title'] = gettext('A tool to send e-mails to all registered users who have provided an e-mail address.');
				break;
			}
		}
		$buttons[] = $button;
		return $buttons;
	}

} else {

	define('OFFSET_PATH', 3);
	chdir(dirname(dirname(__FILE__)));

	require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');

	admin_securityChecks(NULL, currentRelativeURL());

	$admins = $_zp_authority->getAdministrators();
	$zenphoto_tabs['overview']['subtabs'] = array(gettext('Mailing') => '');

	printAdminHeader('overview', 'Mailing');
	?>
	</head>
	<body>
		<?php printLogoAndLinks(); ?>
		<div id="main">
			<?php printTabs(); ?>
			<div id="content">
				<?php printSubtabs('Mailing'); ?>
				<div class="tabbox">
					<?php zp_apply_filter('admin_note', 'user_mailing', ''); ?>
					<h1><?php echo gettext('User mailing list'); ?></h1>
					<p><?php echo gettext("A tool to send e-mails to all registered users who have provided an e-mail address. There is always a copy sent to the current admin and all e-mails are sent as <em>blind copies</em>."); ?></p>
					<?php
					if (!zp_has_filter('sendmail')) {
						$disabled = ' disabled="disabled"';
						?>
						<p class="notebox">
							<?php
							echo gettext("<strong>Note: </strong>No <em>sendmail</em> filter is registered. You must activate and configure a mailer plugin.");
							?>
						</p>
						<?php
					} else {
						$disabled = '';
					}
					?>
					<p id="sent" class="messagebox" style="display:none;">
						<?php echo gettext('Mail sent'); ?>
					</p>

					<h2><?php echo gettext('Please enter the message you want to send.'); ?></h2>
					<form class="dirtylistening" onReset="setClean('massmail');" id="massmail" action="<?php echo WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER ?>/user_mailing_list/mail_handler.php?sendmail" method="post" accept-charset="UTF-8" autocomplete="off">
						<?php XSRFToken('mailing_list'); ?>
						<table>
							<tr>
								<td valign="top">
							<labelfor="subject"><?php echo gettext('Subject:'); ?></label><br />
								<input type="text" id="subject" name="subject" value="" size="70"<?php echo $disabled; ?> /><br /><br />
								<label for="message"><?php echo gettext('Message:'); ?></label><br />
								<textarea id="message" name="message" value="" cols="68" rows="10"<?php echo $disabled; ?> ></textarea>
								</td>
								<td valign="top" align="left">
									<?php echo gettext('Select users:'); ?>
									<ul class="unindentedchecklist" style="height: 205px; width: 30em;">
										<?php
										$currentadminuser = $_zp_current_admin_obj->getUser();
										foreach ($admins as $admin) {
											if (!empty($admin['email']) && $currentadminuser != $admin['user']) {
												?>
												<li>
													<label for="admin_<?php echo $admin['id']; ?>">
														<input name="admin_<?php echo $admin['id']; ?>" id="admin_<?php echo $admin['id']; ?>" type="checkbox" value="<?php echo html_encode($admin['email']); ?>" checked="checked"  <?php echo $disabled; ?>/>
														<?php
														echo $admin['user'] . " (";
														if (!empty($admin['name'])) {
															echo $admin['name'] . " - ";
														}
														echo $admin['email'] . ")";
														?>
													</label>
												</li>
												<?php
											}
										}
										?>
									</ul>
									<br />
								</td>
								</tr>
						</table>
						<script type="text/javascript">
							$('form#massmail').submit(function () {
								$.post($(this).attr('action'), $(this).serialize(), function (res) {
									// Do something with the response `res`
									console.log(res);
								});
								$('form#massmail').trigger('reset');
								$('#sent').show();
								$("#sent").fadeTo(5000, 1).fadeOut(1000);
								return false; // prevent default action
							});
						</script>
						<p class="buttons">
							<button class="submitbutton" type="submit"
											title="<?php echo gettext("Send mail"); ?>"<?php echo $disabled; ?> ><img
									src="../images/pass.png" alt="" /><strong><?php echo gettext("Send mail"); ?></strong></button>
						</p>
						<p class="buttons">
							<button class="submitbutton" type="reset"
											title="<?php echo gettext("Reset"); ?>"><img src="../images/reset.png"
																													 alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
						</p>
						<br style="clear: both" />
					</form>

				</div>
			</div><!-- content -->
		</div><!-- main -->
		<?php printAdminFooter(); ?>
	</body>
	</html>

	<?php
}
?>