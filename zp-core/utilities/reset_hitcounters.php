<?php
$buttonlist[] = array(
								'XSRFTag'=>'hitcounter',
								'category'=>gettext('database'),
								'enable'=>'1',
								'button_text'=>gettext('Reset all hitcounters'),
								'formname'=>'reset_hitcounters.php',
								'action'=>WEBPATH.'/'.ZENFOLDER.'/admin.php?action=reset_hitcounters=true',
								'icon'=>'images/reset1.png',
								'title'=>'',
								'alt'=>gettext('Reset hitcounters'),
								'hidden'=>'<input type="hidden" name="action" value="reset_hitcounters" />',
								'rights'=> ADMIN_RIGHTS
								);
?>