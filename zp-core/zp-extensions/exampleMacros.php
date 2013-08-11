<?php
/*
 * Declares example macros
 *
 * @package plugins
 * @subpackage development

 */
$plugin_is_filter = 5 | THEME_PLUGIN | ADMIN_PLUGIN;
$plugin_description = gettext("Adds example macros.");
$plugin_author = "Stephen Billard (sbillard)";

setOptionDefault('zp_plugin_exampleMacros', $plugin_is_filter);
zp_register_filter('content_macro', 'exampleMacros::macro');

class exampleMacros {

	static function macro($macros) {
		$my_macros = array(
						'CODEBLOCK'				 => array('class'	 => 'procedure',
										'params' => array('int'),
										'value'	 => 'printCodeblock',
										'owner'	 => 'exampleMacros',
										'desc'	 => gettext('Places codeblock number <code>%1</code> in the content where the macro exists.')),
						'PAGE'						 => array('class'	 => 'function',
										'params' => array(),
										'value'	 => 'getCurrentPage',
										'owner'	 => 'exampleMacros',
										'desc'	 => gettext('Prints the current page number.')),
						'ZENPHOTO_VERSION' => array('class'	 => 'constant',
										'params' => array(),
										'value'	 => ZENPHOTO_VERSION,
										'owner'	 => 'exampleMacros',
										'desc'	 => gettext('Prints the version of the Zenphoto installation.')),
						'CURRENT_SCRIPT'	 => array('class'	 => 'expression',
										'params' => array(),
										'value'	 => '"current script: ".stripSuffix($GLOBALS["_zp_gallery_page"]);',
										'owner'	 => 'exampleMacros',
										'desc'	 => gettext('An example of how to reference global variables. In this case to dump the current gallery page variable.')),
						'PARAM_DUMP'			 => array('class'	 => 'procedure',
										'params' => array('array'),
										'value'	 => 'exampleMacros::arrayTest',
										'owner'	 => 'exampleMacros',
										'desc'	 => gettext('Dump the contents of the array parameter list. The array is in the form <em>variable_1</em>=<code>value</code> <em>variable_2</em>=<code>value</code> <em>etc.</em>.')),
						'PAGELINK'				 => array('class'	 => 'expression',
										'params' => array('string'),
										'value'	 => 'getCustomPageURL($1);',
										'owner'	 => 'exampleMacros',
										'desc'	 => gettext('Provides text for a link to a "custom" script page indicated by <code>%1</code>.'))
		);
		return array_merge($macros, $my_macros);
	}

	static function arrayTest($params) {
		?>
		<div>
			<?php
			echo gettext('The PARAM_DUMP macro was passed the following:');
			?>
			<ul>
				<?php
				foreach ($params as $key => $value) {
					echo '<li>' . $key . ' => ' . $value . '</li>';
				}
				?>
			</ul>
		</div>
		<?php
	}

}
?>
