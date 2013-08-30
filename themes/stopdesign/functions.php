<?php

zp_register_filter('themeSwitcher_head', 'modifyThemeSelector');
zp_register_filter('themeSwitcher_css', 'stopdesign_switch');
enableExtension('zenpage', 0, false); //	we do not support it

function modifyThemeSelector($themelist) {
	//remove the zenpage plugin controllink from the DOM
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		window.onload = function() {
			$('#themeSwitcher_zenpage').html('');
		}
		// ]]> -->
	</script>
	<?php

	return $themelist;
}

function stopdesign_switch($css) {
	return ".themeSwitcherControlLink {\n" .
					" position: fixed;\n" .
					" z-index: 10000;\n" .
					" left: 200px;\n" .
					" top: 0px;\n" .
					" border-bottom: 1px solid #444;\n" .
					" border-left: 1px solid #444;\n" .
					" color: black;\n" .
					" padding: 2px;\n" .
					" background-color: #f5f5f5;\n" .
					"}\n";
}
?>