<?php
/**
 *
 * Admin tab for user mailing list
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
 */
if (!defined('OFFSET_PATH'))
	define('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL());

$admins = $_zp_authority->getAdministrators();

printAdminHeader('admin', 'Mailing');
?>
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'user_mailing', ''); ?>
			<h1><?php echo gettext('User mailing list'); ?></h1>
			<div class="tabbox">
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


					<div class="floatleft">
						<labelfor="subject"><?php echo gettext('Subject:'); ?></label><br />
							<input type="text" id="subject" name="subject" value="" size="70"<?php echo $disabled; ?> /><br /><br />
							<label for="message"><?php echo gettext('Message:'); ?></label><br />
							<textarea id="message" name="message" value="" cols="68" rows="10"<?php echo $disabled; ?> ></textarea>
					</div>

					<div class="floatleft">
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

					</div>
					<br class="clearall">
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
						<button class="submitbutton" type="submit" title="<?php echo gettext("Send mail"); ?>"<?php echo $disabled; ?> >
							<?php echo CHECKMARK_GREEN; ?>
							<strong><?php echo gettext("Send mail"); ?></strong>
						</button>
					</p>
					<p class="buttons">
						<button class="submitbutton" type="reset" title="<?php echo gettext("Reset"); ?>">
							<?php echo CROSS_MARK_RED; ?>
							<strong><?php echo gettext("Reset"); ?></strong>
						</button>
					</p>
					<br style="clear: both" />
				</form>

			</div>
		</div><!-- content -->
	</div><!-- main -->
	<?php printAdminFooter(); ?>
</body>
</html>