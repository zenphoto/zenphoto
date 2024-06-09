<form id="commentform" action="#commentform" method="post"<?php echo getCommentformFormAutocompleteAttr(); ?>>
	<input type="hidden" name="comment" value="1" />
	<?php printCommentErrors(); ?>
	<p style="display:none;">
		<label for="username">Username:</label>
		<input type="text" id="username" name="username" autocomplete="username" value="" />
	</p>
	<?php
	if (getOption('comment_name_required')) {
		?>
		<p>
			<label for="name"><?php printf(gettext("Name%s"), getCommentFormRequiredFieldMark('comment_name_required')); ?></label>
			<input<?php printCommentFormFieldAttributes('comment_name_required', $disabled['name'], 'name'); ?> type="text" id="name" name="name" size="22" value="<?php echo html_encode($stored['name']); ?>" />
		</p>
		<?php
		if (getOption('comment_form_anon') && !$disabled['anon']) {
			?>
			<p>
				<label for="anon"> (<?php echo gettext("<em>anonymous</em>"); ?>)</label>
				<input type="checkbox" id="anon" name="anon" value="1"<?php if ($stored['anon'])
			echo ' checked="checked"';
		echo $disabled['anon'];
		?> />
			</p>
			<?php
		}
	}
	if (getOption('comment_email_required')) {
		?>
		<p>
			<label for="email"><?php printf(gettext("E-Mail%s"), getCommentFormRequiredFieldMark('comment_email_required')); ?></label>
			<input<?php printCommentFormFieldAttributes('comment_email_required', $disabled['email'], 'email'); ?> type="email" id="email" name="email" size="22" value="<?php echo html_encode($stored['email']); ?>" />
		</p>
		<?php
	}
	if (getOption('comment_web_required')) {
		?>
		<p>
			<label for="website"><?php printf(gettext("Website%s"), getCommentFormRequiredFieldMark('comment_web_required')); ?></label>
			<input<?php printCommentFormFieldAttributes('comment_web_required', $disabled['website'], 'url'); ?> type="url" id="website" name="website" size="22" value="<?php echo html_encode($stored['website']); ?>" />
		</p>
		<?php
	}
	if (getOption('comment_form_addresses')) {
		?>
		<p>
			<label for="comment_form_street"><?php printf(gettext('Street%s'), getCommentFormRequiredFieldMark('comment_form_addresses')); ?></label>
			<input<?php printCommentFormFieldAttributes('comment_form_addresses', $disabled['street']); ?> type="text" id="comment_form_street" name="0-comment_form_street"<?php printCommentformAutocompleteAttr('street-address', true); ?> size="22" value="<?php echo html_encode($stored['street']); ?>" />
		</p>
		<p>
			<label for="comment_form_city"><?php printf(gettext('City%s'), getCommentFormRequiredFieldMark('comment_form_addresses')); ?></label>
			<input<?php printCommentFormFieldAttributes('comment_form_addresses', $disabled['city']); ?> type="text" id="comment_form_city" name="0-comment_form_city"<?php printCommentformAutocompleteAttr('address-level2', true); ?> size="22" value="<?php echo html_encode($stored['city']); ?>" />
		</p>
		<p>
			<label for="comment_form_state"><?php printf(gettext('State%s'), getCommentFormRequiredFieldMark('comment_form_addresses')); ?></label>
			<input<?php printCommentFormFieldAttributes('comment_form_addresses', $disabled['state']); ?> type="text" id="comment_form_state" name="0-comment_form_state"<?php printCommentformAutocompleteAttr('address-level1', true); ?> size="22" value="<?php echo html_encode($stored['state']); ?>" />
		</p>
		<p>
			<label for="comment_form_country"><?php printf(gettext('Country%s'), getCommentFormRequiredFieldMark('comment_form_addresses')); ?></label>
			<input<?php printCommentFormFieldAttributes('comment_form_addresses', $disabled['country']); ?> type="text" id="comment_form_country" name="0-comment_form_country"<?php printCommentformAutocompleteAttr('country', true); ?> size="22" value="<?php echo html_encode($stored['country']); ?>" />
		</p>
		<p>
			<label for="comment_form_postal"><?php printf(gettext('Postal code%s'), getCommentFormRequiredFieldMark('comment_form_addresses')); ?></label>
			<input<?php printCommentFormFieldAttributes('comment_form_addresses', $disabled['postal']); ?> type="text" id="comment_form_postal" name="0-comment_form_postal"<?php printCommentformAutocompleteAttr('postal-code', true); ?> size="22" value="<?php echo html_encode($stored['postal']); ?>" />
		</p>
		<?php
	}
	if ($_zp_captcha->name && commentFormUseCaptcha()) {
		$captcha = $_zp_captcha->getCaptcha(gettext("Enter CAPTCHA<strong>*</strong>"));
		?>
		<p>
			<?php
			if (isset($captcha['html']))
				echo $captcha['html'];
			if (isset($captcha['input']))
				echo $captcha['input'];
			if (isset($captcha['hidden']))
				echo $captcha['hidden'];
			?>
		</p>
		<?php
	}
	if (getOption('comment_form_private') && !$disabled['private']) {
		?>
		<p>
			<label for="private"><?php echo gettext("Private comment (do not publish)"); ?></label>
			<input type="checkbox" id="private" name="private" value="1"<?php if ($stored['private']) echo ' checked="checked"'; ?> />
		</p>
		<?php
	}
	if(getOption('comment_form_remember')) {
		?>
		<p>
			<label for="remember"><?php echo gettext("Remember me"); ?></label>
			<input type="checkbox" id="remember" name="remember" value="1" />
		</p>	
		<?php
	}
	if (getOption('comment_form_dataconfirmation')) {
		?>
		<p>
			<label for="comment_dataconfirmation">
				<?php printDataUsageNotice();
				echo '<strong>*</strong>'; ?>
				<input type="checkbox" id="comment_dataconfirmation" name="comment_dataconfirmation" value="1"<?php if ($stored['comment_dataconfirmation']) echo ' checked="checked"'; ?> required/>
			</label>
		</p>
		<?php
	}
	?>
	<p><?php echo gettext('<strong>*</strong>Required fields'); ?></p>
	<?php
	?>
	<br />
	<textarea name="comment" rows="6" cols="42" class="textarea_inputbox" <?php if(!getOption('tinymce4_comments')) echo 'required'; ?>><?php
		echo $stored['comment'];
		echo $disabled['comment'];
?></textarea>
	<br />
	<input type="submit" class="button buttons"  value="<?php echo gettext('Add Comment'); ?>" />
</form>
