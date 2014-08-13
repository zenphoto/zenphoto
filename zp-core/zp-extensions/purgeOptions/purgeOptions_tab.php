<?php
/**
 * purge options tab
 *
 * @author Stephen Billard (sbillard)
 *
 * copyright © 2014 Stephen L Billard
 *
 * @package plugins
 * @subpackage admin
 */
// force UTF-8 Ø

define('OFFSET_PATH', 1);
require_once(dirname(dirname(dirname(__FILE__))) . '/admin-globals.php');

admin_securityChecks(OPTIONS_RIGHTS, $return = currentRelativeURL());

$xlate = array('plugins' => gettext('User plugins'), 'zp-core/zp-extensions' => gettext('Extensions'), 'themes' => gettext('Themes'));

if (isset($_POST['purge'])) {
	XSRFdefender('purgeOptions');
	if (isset($_POST['del'])) {
		foreach ($_POST['del'] as $owner) {
			$sql = 'DELETE FROM ' . prefix('options') . ' WHERE `creator` LIKE ' . db_quote('%' . basename($owner));
			$result = query($sql);
			if (preg_match('~^' . THEMEFOLDER . '/~', $owner)) {
				$sql = 'DELETE FROM ' . prefix('options') . ' WHERE `creator` LIKE ' . db_quote('%' . basename($owner) . '/themeoptions.php');
				$result = query($sql);
			} else {
				purgeOption('zp_plugin_' . stripSuffix(basename($owner)));
			}
		}
	}
	if (isset($_POST['missingplugin'])) {
		foreach ($_POST['missingplugin'] as $plugin) {
			purgeOption('zp_plugin_' . stripSuffix($plugin));
		}
	}
}

printAdminHeader('options', '');
?>
<link rel="stylesheet" href="purgeOptions.css" type="text/css">
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<?php printSubtabs(); ?>
				<div class="tabbox">
					<?php
					$owners = array();
					$sql = 'SELECT `name` FROM ' . prefix('options') . ' WHERE `name` LIKE "zp_plugin_%"';
					$result = query_full_array($sql);
					foreach ($result as $plugin) {
						$plugin = str_replace('zp_plugin_', '', $plugin['name']) . '.php';
						$file = str_replace(SERVERPATH, '', getPlugin($plugin, false));
						if (strpos($file, PLUGIN_FOLDER) === false) {
							$owners[USER_PLUGIN_FOLDER][$plugin] = $plugin;
						} else {
							$owners[ZENFOLDER . '/' . PLUGIN_FOLDER][$plugin] = $plugin;
						}
					}
					$sql = 'SELECT DISTINCT `creator` FROM ' . prefix('options');
					$result = query_full_array($sql);
					foreach ($result as $owner) {
						$structure = explode('/', $owner['creator']);
						switch (count($structure)) {
							case 1:
								break;
							case 2:
								$owners[$structure[0]][] = $structure[1];
								break;
							case 3:
								$owners[$structure[0]][$structure[1]][] = $structure[2];
								break;
							case 4:
								$owners[$structure[0]][$structure[1]][$structure[2]][] = $structure[3];
								break;
						}
					}

					if (isset($owners[USER_PLUGIN_FOLDER])) {
						$owners[USER_PLUGIN_FOLDER] = array_unique($owners[USER_PLUGIN_FOLDER]);
						natcasesort($owners[USER_PLUGIN_FOLDER]);
					}
					if (isset($owners[ZENFOLDER][PLUGIN_FOLDER])) {
						$owners[ZENFOLDER . '/' . PLUGIN_FOLDER] = array_unique($owners['zp-core']['zp-extensions']);
						natcasesort($owners[ZENFOLDER . '/' . PLUGIN_FOLDER]);
					}
					unset($owners[ZENFOLDER]);

					if (isset($owners[THEMEFOLDER])) {
						foreach ($owners[THEMEFOLDER] as $theme => $v) {
							if (is_array($v)) {
								$owners[THEMEFOLDER][] = $theme;
								unset($owners[THEMEFOLDER][$theme]);
							}
						}
						$owners[THEMEFOLDER] = array_unique($owners[THEMEFOLDER]);
						natcasesort($owners[THEMEFOLDER]);
					}
					if (empty($owners)) {
						echo gettext('No option owners have been located.');
					} else {
						?>
						<form class="dirtylistening" onReset="setClean('purge_options_form');" id="purge_options_form" action="?page=options&tab=purge" method="post" >
							<?php XSRFToken('purgeOptions'); ?>
							<input type="hidden" name="purge" value="1" />.
							<p class = "buttons" >
								<button type="submit" value="<?php echo gettext('Apply') ?>"> <img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt="" /> <strong><?php echo gettext("Apply"); ?> </strong></button >
								<button type="" "reset" value="<?php echo gettext('reset') ?>"> <img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" alt="" /> <strong><?php echo gettext("Reset"); ?> </strong></button>
							</p>
							<br class="clearall" />

							<p>
								<?php
								echo gettext('Check an item to purge options associated with it.');
								?>
								<span class="highlighted">
									<?php echo gettext('Items that are <span class="missing_owner">higlighed</span> appear no longer to exist.') ?>
								</span>
							</p>
							<ul class="highlighted">
								<li>
									<?php printf(gettext('<span class="missing_owner">higlighed</span>%s '), '<input type="checkbox" id="missing" checked="checked" onclick="$(\'.missing\').prop(\'checked\', $(\'#missing\').prop(\'checked\'));">');
									?>
								</li>
							</ul>
							<ul>
								<?php listOwners($owners); ?>
							</ul>
							<p class="buttons">
								<button type="submit" value="<?php echo gettext('Apply') ?>" > <img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/pass.png" alt = "" /> <strong><?php echo gettext("Apply"); ?> </strong></button>
								<button type="reset" value="<?php echo gettext('reset') ?>" > <img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" alt="" /> <strong><?php echo gettext("Reset"); ?> </strong></button>
							</p>
							<br class="clearall" />
						</form>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
	if (!isset($highlighted)) {
		?>
		<script type="text/javascript">
			$('.highlighted').remove();
		</script>
		<?php
	}
	?>
	<br class="clearall" />
	<?php printAdminFooter(); ?>
</body>
</html>
