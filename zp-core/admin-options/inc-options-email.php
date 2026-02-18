<div id="tab_image" class="tabbox">
	<?php filter::applyFilter('admin_note', 'options', $subtab); ?>
	<form class="dirty-check" id="form_options" action="?action=saveoptions" method="post" autocomplete="off">
		<?php XSRFToken('saveoptions'); ?>
		<input type="hidden" name="saveemailoptions" value="yes" />

		<table class="options">
			<tr>
				<td colspan="3">
					<p class="buttons">
						<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
						<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
					</p>
				</td>
			</tr>
			<?php
			$emailoptions = new emailOptions();
			customOptions($emailoptions, '');
			?>
			<tr>
				<td colspan="3">
					<p class="buttons">
						<button type="submit" value="<?php echo gettext('Apply') ?>"><img src="images/pass.png" alt="" /><strong><?php echo gettext("Apply"); ?></strong></button>
						<button type="reset" value="<?php echo gettext('reset') ?>"><img src="images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button>
					</p>
				</td>
			</tr>
		</table>
	</form>
</div>