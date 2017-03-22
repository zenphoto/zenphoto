<form id="commentform" class="form-horizontal" action="#commentform" method="post">
	<input type="hidden" name="comment" value="1" />
	<input type="hidden" name="remember" value="1" />
	<?php
	$star = '<strong>*</strong>';
	$required = false;
	printCommentErrors();
	?>
	<div class="form-group" style="display:none;">
		<label for="username" class="col-sm-3 control-label">Username:</label>
		<div class="col-sm-9">
			<input type="text" id="username" name="username" value="" class="form-control" />
		</div>	
	</div>
	<?php
	if ($req = getOption('comment_name_required')) {
		$required = $required || $req == 'required';
		?>
		<div class="form-group">
			<label for="name" class="col-sm-3 control-label"><?php printf(gettext("Name%s"), ($req == 'required' ? $star : '')); ?></label>
		<div class="col-sm-9">			
			<input<?php if ($disabled['name']) echo ' READONLY '; ?> type="text" id="name" name="name" size="22" value="<?php echo html_encode($stored['name']); ?>" class="form-control"  />
		</div>
		</div>
		<?php
		if (getOption('comment_form_anon') && !$disabled['anon']) {
			?>
			<div class="form-group">
				<label for="anon" class="col-sm-3 control-label"> (<?php echo gettext("<em>anonymous</em>"); ?>)</label>
		<div class="col-sm-9">
				<input type="checkbox" name="anon" id="anon" value="1"<?php if ($stored['anon']) echo ' checked="checked"';	echo $disabled['anon']; ?> />
		</div>
		</div>
			<?php
		}
	}
	if ($req = getOption('comment_email_required')) {
		$required = $required || $req == 'required';
		?>
		<div class="form-group">
			<label for="email" class="col-sm-3 control-label"><?php printf(gettext("E-Mail%s"), ($req == 'required' ? $star : '')); ?></label>
		<div class="col-sm-9">
			<input <?php if ($disabled['email']) echo 'READONLY'; ?> type="text" id="email" name="email" size="22" value="<?php echo html_encode($stored['email']); ?>" class="form-control"  />
		</div>	
		</div>
		<?php
	}
	if ($req = getOption('comment_web_required')) {
		?>
		<div class="form-group">
			<label for="website" class="col-sm-3 control-label"><?php printf(gettext("Site%s"), ($req == 'required' ? $star : '')); ?></label>
		<div class="col-sm-9">
			<input <?php if ($disabled['website']) echo 'READONLY'; ?> type="text" id="website" name="website" size="22" value="<?php echo html_encode($stored['website']); ?>" class="form-control"  />
		</div>			
		</div>
		<?php
	}
	if ($req = getOption('comment_form_addresses')) {
		$required = $required || $req == 'required';
		?>
		<div class="form-group">
			<label for="0-comment_form_street" class="col-sm-3 control-label"><?php printf(gettext('Street%s'), ($req == 'required' ? $star : '')); ?></label>
		<div class="col-sm-9">
			<input <?php if ($disabled['street']) echo 'READONLY'; ?> type="text" name="0-comment_form_street" id="0-comment_form_street" class="form-control"  size="22" value="<?php echo html_encode($stored['street']); ?>" />
		</div>	
		</div>
		<div class="form-group">
			<label for="0-comment_form_city" class="col-sm-3 control-label"><?php printf(gettext('City%s'), ($req == 'required' ? $star : '')); ?></label>
		<div class="col-sm-9">
			<input <?php if ($disabled['city']) echo 'READONLY'; ?> type="text" name="0-comment_form_city" id="0-comment_form_city" class="form-control"  size="22" value="<?php echo html_encode($stored['city']); ?>" />
		</div>	
		</div>
		<div class="form-group">
			<label for="comment_form_state" class="col-sm-3 control-label"><?php printf(gettext('State%s'), ($req == 'required' ? $star : '')); ?></label>
		<div class="col-sm-9">
			<input <?php if ($disabled['state']) echo 'READONLY'; ?> type="text" name="0-comment_form_state" id="comment_form_state" class="form-control"  size="22" value="<?php echo html_encode($stored['state']); ?>" />
		</div>	
		</div>
		<div class="form-group">
			<label for="comment_form_country" class="col-sm-3 control-label"><?php printf(gettext('Country%s'), ($req == 'required' ? $star : '')); ?></label>
		<div class="col-sm-9">
			<input <?php if ($disabled['country']) echo 'READONLY'; ?> type="text" id="comment_form_country" name="0-comment_form_country" class="form-control"  size="22" value="<?php echo html_encode($stored['country']); ?>" />
		</div>	
		</div>
		<div class="form-group">
			<label for="comment_form_postal" class="col-sm-3 control-label"><?php printf(gettext('Postal code%s'), ($req == 'required' ? $star : '')); ?></label>
		<div class="col-sm-9">
			<input <?php if ($disabled['postal']) echo 'READONLY'; ?> type="text" id="comment_form_postal" name="0-comment_form_postal" class="form-control"  size="22" value="<?php echo html_encode($stored['postal']); ?>" />
		</div>	
		</div>
		<?php
	}
	if (commentFormUseCaptcha()) {
		$captcha = $_zp_captcha->getCaptcha(gettext("Enter CAPTCHA<strong>*</strong>"));
		$required = true;
		?>
		<div class="form-group">
			<?php
			if (isset($captcha['html']))
				echo $captcha['html'];
			if (isset($captcha['input']))
				echo $captcha['input'];
			if (isset($captcha['hidden']))
				echo $captcha['hidden'];
			?>
		</div>
		<?php
	}
	if ($required) {
		?>
		<p><?php echo gettext('<strong>*</strong>Required fields'); ?></p>
		<?php
	}
	if (getOption('comment_form_private') && !$disabled['private']) {
		?>
		<div class="form-group">
			<label for="private" class="col-sm-3 control-label"><?php echo gettext("Private comment (do not publish)"); ?></label>
		<div class="col-sm-9">
			<input type="checkbox" id="private" name="private" value="1"<?php if ($stored['private']) echo ' checked="checked"'; ?> />
		</div>	
		</div>
		<?php
	}
	?>

	<div class="form-group">
		<label for="comment" class="col-sm-3 control-label"><?php echo gettext("Comment"); ?></label>
		<div class="col-sm-9">
			<textarea name="comment" rows="6" cols="42" class="form-control"><?php echo $stored['comment'];
			echo $disabled['comment'];
			?></textarea>
		</div>
		<br/>
	</div>

	<div class="col-sm-9 col-sm-offset-3">	
		<input type="submit" class="button buttons"  value="<?php echo gettext('Add Comment'); ?>" />
	</div>	

</form>
