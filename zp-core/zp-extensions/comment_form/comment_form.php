<form id="commentform" action="#commentform" method="post">
	<input type="hidden" name="comment" value="1" />
	<input type="hidden" name="remember" value="1" />
	<?php
	$star = '<strong>*</strong>';
	$required = false;
	printCommentErrors();
	?>
	<p style="display:none;">
		<label for="username">Username:</label>
		<input type="text" id="username" name="username" value="" />
	</p>
	<?php
	if ($req = getOption('comment_name_required')) {
		$required =$required || $req=='required';
		?>
		<p>
			<label for="name"><?php printf(gettext("Name%s"),($req == 'required' ? $star : '')); ?></label>
			<input<?php if($disabled['name']) echo ' READONLY '; ?> type="text" id="name" name="name" size="22" value="<?php echo html_encode($stored['name']);?>" class="inputbox" />
		</p>
		<?php
			if (getOption('comment_form_anon') && !$disabled['anon']) {
				?>
				<p>
				<label for="anon"> (<?php echo gettext("<em>anonymous</em>"); ?>)</label>
				<input type="checkbox" name="anon" id="anon" value="1"<?php if ($stored['anon']) echo ' checked="checked"'; echo $disabled['anon']; ?> />
				</p>
				<?php
			}
	}
	if ($req = getOption('comment_email_required')) {
		$required =$required || $req=='required';
		?>
		<p>
		<label for="email"><?php printf(gettext("E-Mail%s"),($req == 'required' ? $star : '')); ?></label>
		<input <?php if($disabled['email']) echo 'READONLY'; ?> type="text" id="email" name="email" size="22" value="<?php echo html_encode($stored['email']);?>" class="inputbox" />
		</p>
		<?php
		}
		if ($req = getOption('comment_web_required')) {
			?>
			<p>
				<label for="website"><?php printf(gettext("Site%s"),($req == 'required' ? $star : '')); ?></label>
				<input <?php if($disabled['website']) echo 'READONLY'; ?> type="text" id="website" name="website" size="22" value="<?php echo html_encode($stored['website']);?>" class="inputbox" />
			</p>
	<?php
	}
	if ($req = getOption('comment_form_addresses')) {
		$required =$required || $req=='required';
		?>
		<p>
			<label for="0-comment_form_street"><?php printf(gettext('Street%s'),($req == 'required' ? $star : '')); ?></label>
			<input <?php if($disabled['street']) echo 'READONLY'; ?> type="text" name="0-comment_form_street" id="0-comment_form_street" class="inputbox" size="22" value="<?php echo html_encode($stored['street']); ?>" />
		</p>
		<p>
			<label for="0-comment_form_city"><?php printf(gettext('City%s'),($req == 'required' ? $star : '')); ?></label>
			<input <?php if($disabled['city']) echo 'READONLY'; ?> type="text" name="0-comment_form_city" id="0-comment_form_city" class="inputbox" size="22" value="<?php echo html_encode($stored['city']); ?>" />
		</p>
		<p>
			<label for="comment_form_state"><?php printf(gettext('State%s'),($req == 'required' ? $star : '')); ?></label>
			<input <?php if($disabled['state']) echo 'READONLY'; ?> type="text" name="0-comment_form_state" id="comment_form_state" class="inputbox" size="22" value="<?php echo html_encode($stored['state']); ?>" />
		</p>
		<p>
			<label for="comment_form_country"><?php printf(gettext('Country%s'),($req == 'required' ? $star : '')); ?></label>
			<input <?php if($disabled['country']) echo 'READONLY'; ?> type="text" id="comment_form_country" name="0-comment_form_country" class="inputbox" size="22" value="<?php echo html_encode($stored['country']); ?>" />
		</p>
		<p>
			<label for="comment_form_postal"><?php printf(gettext('Postal code%s'),($req == 'required' ? $star : '')); ?></label>
			<input <?php if($disabled['postal']) echo 'READONLY'; ?> type="text" id="comment_form_postal" name="0-comment_form_postal" class="inputbox" size="22" value="<?php echo html_encode($stored['postal']); ?>" />
		</p>
	<?php
	}
	if (commentFormUseCaptcha()) {
		$captcha = $_zp_captcha->getCaptcha(gettext("Enter CAPTCHA<strong>*</strong>"));
		$required = true;
		?>
		<p>
			<?php
			if (isset($captcha['html'])) echo $captcha['html'];
			if (isset($captcha['input'])) echo $captcha['input'];
			if (isset($captcha['hidden'])) echo $captcha['hidden'];
			?>
		</p>
	<?php
	}
	if ($required) {
		?>
		<p><?php echo gettext('<strong>*</strong>Required fields'); ?></p>
		<?php
	}
	if (getOption('comment_form_private') && !$disabled['private']) {
		?>
		<p>
			<label for="private"><?php echo gettext("Private comment (don't publish)"); ?></label>
			<input type="checkbox" id="private" name="private" value="1"<?php if ($stored['private']) echo ' checked="checked"'; ?> />
		</p>
		<?php
	}
	?>
	<br />
	<textarea name="comment" rows="6" cols="42" class="textarea_inputbox"><?php echo $stored['comment']; echo $disabled['comment']; ?></textarea>
	<br />
	<input type="submit" class="button buttons"  value="<?php echo gettext('Add Comment'); ?>" />
</form>
