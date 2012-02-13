<?php
/**
 * Code needed only for error handling
 * @package core
 */

/**
 *
 * base error handler
 * @param string $message
 * @param bool $fatal
 */
function display_error($message, $fatal) {
	global $_zp_error;
	if (!$_zp_error) {
		?>
		<div style="padding: 15px; border: 1px solid #F99; background-color: #FFF0F0; margin: 20px; font-family: Arial, Helvetica, sans-serif; font-size: 12pt;">
			<h2 style="margin: 0px 0px 5px; color: #C30;">Zenphoto encountered an error</h2>
			<div style=" color:#000;">
				<?php echo $message; ?>
			</div>
		<?php
		if (DEBUG_ERROR) {
			// Get a backtrace.
			$bt = debug_backtrace();
			// Get rid of indirect callers in the backtrace.
			array_shift($bt);
			array_shift($bt);
			$prefix = '  ';
			?>
			<p>
				<?php echo gettext('<strong>Backtrace:</strong>'); ?>
				<br />
				<pre>
					<?php
					echo "\n";
					foreach($bt as $b) {
						echo $prefix . ' -> '
						. (isset($b['class']) ? $b['class'] : '')
						. (isset($b['type']) ? $b['type'] : '')
						. $b['function']
						. (isset($b['file']) ? ' (' . basename($b['file']) : '')
						. (isset($b['line']) ? ' [' . $b['line'] . "])" : '')
						. "\n";
						$prefix .= '  ';
					}
					?>
				</pre>
			</p>
			<?php
		}
		?>
		</div>
		<?php
		if ($fatal) {
			$_zp_error = true;
			debugLogBacktrace("fatal zp_error:$message");
			exitZP();
		} else {
			debugLogBacktrace("zp_error:$message");
		}
	}
}

/**
 *
 * Database error message display
 * @param string $sql
 * @param string $error
 */
function display_db_error($sql, $error) {
	global $_zp_conf_vars;
	$sql = str_replace($_zp_conf_vars['mysql_prefix'], '['.gettext('prefix').']',$sql);
	$sql = str_replace($_zp_conf_vars['mysql_database'], '['.gettext('DB').']',$sql);
	$sql = html_encode($sql);
	display_error(sprintf(gettext('%1$s Error: ( <em>%2$s</em> ) failed. %1$s returned the error <em>%3$s</em>'),DATABASE_SOFTWARE,$sql,$error), true);
}

?>