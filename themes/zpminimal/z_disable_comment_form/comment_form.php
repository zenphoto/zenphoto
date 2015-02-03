
<form id="commentform" action="#" method="post">
	<input type="hidden" name="comment" value="1" />
	<input type="hidden" name="remember" value="1" />
	<?php printCommentErrors(); ?>

	<table>
		<tbody>
			<tr>
				<td colspan="3">
					<div class="commform-textarea">
						<textarea tabindex="1" rows="6" cols="42" class="textarea_inputbox" id="comment" name="comment"></textarea>
					</div>
				</td>
			</tr>
			<tr>
				<td class="commform-author">
					<p><?php echo gettext("Name"); ?> <span><?php echo '*'; ?></span></p>
					<div>
						<input tabindex="2" <?php if ($disabled['name']) { ?>readonly="readonly" <?php } ?>type="text" id="author" name="name" value="<?php echo html_encode($stored['name']); ?>" />
					</div>
				</td>
				<td class="commform-email">
					<p><?php echo gettext("E-Mail"); ?> <span><?php echo '*'; ?></span></p>
					<div>
						<input tabindex="3" <?php if ($disabled['name']) { ?>readonly="readonly" <?php } ?>type="text" id="email" name="email" value="<?php echo html_encode($stored['email']); ?>" />
					</div>
				</td>
				<td class="commform-url">
					<p><?php echo gettext("Website"); ?></p>
					<div>
						<input type="text" tabindex="4" <?php if ($disabled['website']) { ?>readonly="readonly" <?php } ?>id="url" name="website" value="<?php echo html_encode($stored['website']); ?>" />
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="3" style="padding-top:5px;">
					<?php if (getOption('comment_form_anon') && !$disabled['anon']) { ?>
						<span><input type="checkbox" name="anon" value="1"<?php if ($stored['anon'])
						echo ' checked="checked"';
					echo $disabled['anon'];
						?> /> <?php echo gettext("Anonymous"); ?></span>
						<?php } ?>&nbsp;&nbsp;
						<?php if (getOption('comment_form_private') && !$disabled['private']) { ?>
						<span><input type="checkbox" name="private" value="1"<?php if ($stored['private']) echo ' checked="checked"'; ?> />
	<?php echo gettext("Private comment (don't publish)"); ?></span>
<?php } ?>
				</td>
			</tr>
			<tr>
				<td colspan="3" class="commform-code" style="padding-top:10px;">
					<?php
					if (getOption('Use_Captcha') && is_object($_zp_captcha)) {
						$captcha = $_zp_captcha->getCaptcha();
						?>

						<p><?php echo gettext("Enter CAPTCHA"); ?><span><?php echo '*'; ?></span></p>
						<div id="captcha">
							<?php if (isset($captcha['html']) && isset($captcha['input'])) echo $captcha['html']; ?>
							<?php
							if (isset($captcha['input'])) {
								echo $captcha['input'];
							} else {
								if (isset($captcha['html']))
									echo $captcha['html'];
							}
							if (isset($captcha['hidden']))
								echo $captcha['hidden'];
							?>
						</div>
<?php } ?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="submit clear">
		<input type="submit" value="<?php echo gettext('Add Comment'); ?>" tabindex="5" id="submit" name="submit">
	</div>
</form>
