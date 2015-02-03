<form id="passwordform" name="password" action="<?php echo $_password_redirect; ?>" method="post">
	<input type="hidden" name="password" value="1" />
	<input type="hidden" name="redirect" value="<?php echo $_password_redirect; ?>" />

	<table class="password">
	<?php 
		if ($_password_showuser) {
			?>
			<tr>
			<td class="userlabel"><?php echo gettext("Login"); ?></td>
			<td class="userinput"><input type="text" name="user" /></td>
			</tr>
			<?php
		}
		?>
		<tr>
		<td class="passwordlabel"><?php echo gettext("Password"); ?></td>
		<td class="passwordinput"><input type="password" name="pass" /></td>
		</tr>
		<tr>
		<td></td>
		<td class="submit" ><input class="button" type="submit" value="<?php echo gettext("Submit"); ?>" /></td>
		</tr>
		<?php 
		if (!empty($_password_hint)) {
			?>
			<tr>
			<td class="hint" colspan="2"><?php printf(gettext("Hint: %s"), $_password_hint); ?></td>
			</tr>
			<?php
		}
		?>
	</table>
</form>
