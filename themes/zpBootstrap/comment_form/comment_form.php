<form id="commentform" class="form-horizontal" action="#commentform" method="post">
	<input type="hidden" name="comment" value="1" />
	<input type="hidden" name="remember" value="1" />
	<?php
	printCommentErrors();
	$star = '<strong>*</strong>';
	$required = false;
	?>
	<div class="control-group hide" style="display:none;">
		<label class="control-label" for="username">Username</label>
		<div class="controls">
			<input type="text" id="username" class="span3" name="username" value="" />
		</div>
	</div>

	<?php
	if ($req = getOption('comment_name_required')) {
		$required = $required || $req == 'required';
		?>
		<div class="control-group">
			<label class="control-label" for="name">
				<?php printf(gettext("Name%s"), ($req == 'required' ? $star : '')); ?>
				<?php if ((getOption('comment_form_anon')) && (!$disabled['anon'])) { ?>
					(<input type="checkbox" name="anon" value="1"<?php if ($stored['anon']) echo ' checked="checked"';
					echo $disabled['anon']; ?> /> <?php echo gettext("<em>anonymous</em>"); ?>)
	<?php } ?>
			</label>
			<div class="controls">
				<input type="text" id="name" class="span3" name="name" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($stored['name']); ?>"<?php if ($disabled['name']) echo ' READONLY'; ?> />
			</div>
		</div>
	<?php
	}

	if ($req = getOption('comment_email_required')) {
		$required = $required || $req == 'required';
		?>
		<div class="control-group">
			<label class="control-label" for="email"><?php printf(gettext("E-Mail%s"), $star); ?></label>
			<div class="controls">
				<input type="text" id="email" class="span3" name="email" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($stored['email']); ?>"<?php if ($disabled['email']) echo ' READONLY'; ?> />
			</div>
		</div>
<?php
}

if ($req = getOption('comment_web_required')) {
	$required = $required || $req == 'required';
	?>
		<div class="control-group">
			<label class="control-label" for="website"><?php printf(gettext("Site%s"), ($req == 'required' ? $star : '')); ?></label>
			<div class="controls">
				<input type="text" id="website" class="span3" name="website" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($stored['website']); ?>" />
			</div>
		</div>
<?php
}

if ($req = getOption('comment_form_addresses')) {
	$required = $required || $req == 'required';
	?>
		<div class="control-group">
			<label class="control-label" for="0-comment_form_street"><?php printf(gettext("Street%s"), ($req == 'required' ? $star : '')); ?></label>
			<div class="controls">
				<input type="text" id="0-comment_form_street" class="span3" name="0-comment_form_street" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($stored['street']); ?>"<?php if ($disabled['street']) echo ' READONLY'; ?> />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="0-comment_form_city"><?php printf(gettext("City%s"), ($req == 'required' ? $star : '')); ?></label>
			<div class="controls">
				<input type="text" id="0-comment_form_city" class="span3" name="0-comment_form_city" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($stored['city']); ?>"<?php if ($disabled['city']) echo ' READONLY'; ?> />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="0-comment_form_state"><?php printf(gettext("State%s"), ($req == 'required' ? $star : '')); ?></label>
			<div class="controls">
				<input type="text" id="comment_form_state-0" class="span3" name="0-comment_form_state" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($stored['state']); ?>"<?php if ($disabled['state']) echo ' READONLY'; ?> />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="0-comment_form_country"><?php printf(gettext("Country%s"), ($req == 'required' ? $star : '')); ?></label>
			<div class="controls">
				<input type="text" id="0-comment_form_country" class="span3" name="0-comment_form_country" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($stored['country']); ?>"<?php if ($disabled['country']) echo ' READONLY'; ?> />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="0-comment_form_postal"><?php printf(gettext("Postal code%s"), ($req == 'required' ? $star : '')); ?></label>
			<div class="controls">
				<input type="text" id="0-comment_form_postal" class="span3" name="0-comment_form_postal" size="<?php echo TEXT_INPUT_SIZE; ?>" value="<?php echo html_encode($stored['postal']); ?>"<?php if ($disabled['postal']) echo ' READONLY'; ?> />
			</div>
		</div>
			<?php }

			if (commentFormUseCaptcha()) {
				?>
		<div class="control-group">
			<label class="control-label" for="captcha"><?php echo gettext("Enter CAPTCHA<strong>*</strong>"); ?></label>
			<div class="controls">
	<?php
	$captcha = $_zp_captcha->getCaptcha('');
	if (isset($captcha['html']))
		echo $captcha['html'];
	if (isset($captcha['input']))
		echo $captcha['input'];
	if (isset($captcha['hidden']))
		echo $captcha['hidden'];
	?>
			</div>
		</div>
<?php }

if ($required) {
	?>
		<div class="control-group controls">
			<strong><?php echo gettext("<strong>*</strong>Required fields"); ?></strong>
		</div>
<?php }

if (getOption('comment_form_private') && !$disabled['private']) {
	?>
		<div class="control-group controls">
			<label class="checkbox" for="private">
				<input type="checkbox" id="private" name="private" value="1"<?php if ($stored['private']) echo ' checked="checked"'; ?> />
	<?php echo gettext("Private comment (don't publish)"); ?>
			</label>
		</div>
<?php } ?>

	<div class="control-group">
		<textarea name="comment" rows="6" cols="42" class="span6"><?php echo $stored['comment'];
echo $disabled['comment']; ?></textarea>
	</div>

	<div class="control-group">
		<input type="submit" class="btn btn-inverse" value="<?php echo gettext("Add Comment"); ?>" />
	</div>
</form>