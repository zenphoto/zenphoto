<?php

/**
 * Prints out the menu of the theme
 */
function printMenu() {
	?>
	<ul id="m_menu" class="prefix_10">
		<?php if (function_exists('printCalendar')) { ?>
			<li class="grid_2"><a href="<?php echo getCustomPageURL('calendar'); ?>"><?php echo gettext('Calendar') ?></a></li>
		<?php } else { ?>
			<li class="grid_2"><a href="<?php echo getCustomPageURL('archive'); ?>"><?php echo gettext('Archive View') ?></a></li>
		<?php } ?>
			<?php if (getOption('zp_plugin_contact_form')) { ?><li class="grid_2"><a href="<?php echo getCustomPageURL('contact'); ?>"><?php echo gettext('Contact') ?></a></li><?php } ?>
	</ul>
	<?php
}

/**
 * Prints out the links for login/out, register formular if asked
 */
function printLoginZone() {
	if (!zp_loggedin() && (function_exists('printUserLogin_out') || function_exists('printUserLogin_out') || function_exists('printRegistrationForm'))) {
		$multi = 0;
		echo '<div id="loginout" class=" push_5 grid_10">';
		if (zp_loggedin() && function_exists('printUserLogin_out')) {
			printUserLogin_out('', '', false);
			$multi++;
		}
		if (!zp_loggedin() && function_exists('printUserLogin_out')) {
			if ($multi) {
				echo ' - ';
			}
			printCustomPageURL(gettext('Login'), 'login', '', '');
			$multi++;
		}
		if (!zp_loggedin() && function_exists('printRegistrationForm')) {
			if ($multi) {
				echo ' - ';
			}
			printCustomPageURL(gettext('Register for this site'), 'register', '', '');
		}
		echo '</div>';
	}
}

/**
 * Prints out the footer of the page
 */
function printFooter() {
	?>
	<div class="copyright">
		<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/">
			<img alt="Licence Creative Commons" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/88x31.png" />
		</a>
		<span class="bold">Gray Highlights</span> by The Whole Life To Learn
	</div>
	<div class="zen-logo">
		<?php printZenphotoLink(); ?>
	</div>
	<?php
}
?>