<?php

/**
 *
 * Macros are "delcared" by filters registered to the <var>content_macro</var> filter. The filter should add its macros to the
 * array passed and return the result. A macro is defined by an array element. The index of the array element is the macro identifier.
 *
 * Note: the plugin should be active both on THEMES to provide the function and on the ADMIN pages to provide
 * the macro documentation.
 *
 * The content of the array is as follows:
 * <ol>
 * 	<li>"class	 => <var>macro class</var>, see below.</li>
 * 	<li>"params" => An array of parameter types. Append an <b>*</b> if the parameter may be omitted. The types allowed are:
 * 		<ul>
 * 			<li>"string": may be enclosed in quotation marks when the macro is invoked. The quotes are stripped before the macro is processed. </li>
 * 			<li>"int": a number</li>
 * 			<li>"bool": <var>true</var> or <var>false</var></li>
 * 			<li>"array": will process assignment type parameter (<var>x = y</var>) lists. If the assignment is left out, the value will be inserted with its position in the list as the array index. Since an array parameter will consume all remaining elements it must be the last item in the parameter list.</li>
 * 		</ul>
 * 	</li>
 * 	<li>"value"	 => This is a function, procedure, expression or content as defined by the macro class.</li>
 * 	<li>"owner"	 => This should be your plugin name.</li>
 * 	<li>"desc"	 => Text that describes the macro usage.</li>
 * </ol>
 *
 * macro classes:
 * 	<ol>
 * 		<li>
 * 			<var>procedure</var> calls a script function that produces output. The output is captured and inserted in place of the macro instance.
 * 		</li>
 * 		<li>
 * 			<var>function</var> calls a script function that returns a result. The result is inserted in place of the macro instance.
 * 		</li>
 * 		<li>
 * 			<var>constant</var> replaces the macro instances with the constant provided.
 * 		</li>
 * 		<li>
 * 			<var>expression</var> evaluates the expression provided and replaces the instance with the result of the evaluation. If a regex is supplied for an expression.
 * 														The values provided will replace placeholders in the expression. The first parameter replaces $1, the second $2, etc.
 * 		</li>
 * 	</ol>
 *
 * useage examples:
 * 	<ol>
 * 		<li>
 * 			[CODEBLOCK 3]
 * 		</li>
 * 		<li>
 * 			[PAGE]
 * 		</li>
 * 		<li>
 * 			[ZENPHOTO_VERSION]
 * 		</li>
 * 		<li>
 * 			[pageLink register]
 * 		</li>
 * 	</ol>
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 * @subpackage tools
 */
$plugin_is_filter = 5 | ADMIN_PLUGIN;
$plugin_description = gettext('View available <code>content macros</code>.');
$plugin_author = "Stephen Billard (sbillard)";

$macros = getMacros();
if (!empty($macros)) {
	zp_register_filter('admin_tabs', 'macro_admin_tabs');
}

function macro_admin_tabs($tabs) {
	$tabs['macros'] = array('text'		 => gettext("macros"),
					'link'		 => WEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/macroList/macroList_tab.php?page=macros&amp;tab=' . gettext('macros'),
					'subtabs'	 => NULL);
	return $tabs;
}

function MacroList_show($macro, $detail) {
	$warned = array();
	echo '<dl>';
	$warn = array();
	if (preg_match('/[^\w]/', $macro)) {
		$warn['identifier'] = gettext('Macro identifiers may not contain special characters.');
		echo "<dt><code>[<span class=\"error\">$macro</span>";
	} else {
		echo "<dt><code>[$macro";
	}
	$required = $array = false;
	if ($detail['class'] == 'expression') {
		preg_match_all('/\$\d+/', $detail['value'], $replacements);
		foreach ($replacements as $rkey => $v) {
			if (empty($v))
				unset($replacements[$rkey]);
		}
		if (count($detail['params']) != count($replacements)) {
			$warn['paremeters'] = gettext('The number of macro parameters must match the number of replacement tokens in the expression.');
		}
	} else if ($detail['class'] == 'function' || $detail['class'] == 'procedure') {
		if (!is_callable($detail['value'])) {
			$warn['method'] = sprintf(gettext('<code>%s</code> is not callable'), $detail['value']);
		}
	}
	if (!empty($detail['params'])) {
		$params = '';
		$brace = '{';
		for ($i = 1; $i <= count($detail['params']); $i++) {
			$type = rtrim($rawtype = $detail['params'][$i - 1], '*');
			if ($array) {
				$params .= ' <em><span class="error">' . $type . ' %' . $i . '</span></em>';
				$warn['array'] = gettext('An array parameter must be the last parameter.');
			} else if ($type == $rawtype) {
				if ($required) {
					$params .= ' <em><span class="error">' . $type . ' %' . $i . '</span></em>';
					$warn['required'] = gettext('Required parameters should not follow optional ones.');
				} else {
					$params = $params . ' <em>' . $type . " %$i</em>";
				}
			} else {
				if ($detail['class'] == 'expression') {
					$params = $params . " <em>$brace" . '<span class="error">' . $type . " %$i</span></em>";
					$warn['expression'] = gettext('Expressions may not have optional parameters.');
				} else {
					$params = $params . " <em>$brace" . $type . " %$i</em>";
				}
				$required = true;
				$brace = '';
			}
			$array = $array || $type == 'array';
		}
		if ($required)
			$params .= "<em>}</em>";
		echo $params;
	}
	echo ']</code> <em>(' . @$detail['owner'] . ')</em></dt><dd>' . $detail['desc'] . '</dd>';
	if (count($warn)) {
		echo '<div class="notebox"><strong>Warning:</strong>';
		foreach ($warn as $warning) {
			echo '<p>' . $warning . '</p>';
		}
		echo'</div>';
	}
	echo '</dl>';
}

?>