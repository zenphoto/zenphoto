<?php
/**
 * purge options tab
 *
 * @author Stephen Billard (sbillard)
 *
 * Copyright 2014 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
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
	$purgedActive = array();

	if (isset($_POST['del'])) {
		foreach ($_POST['del'] as $owner) {
			$sql = 'DELETE FROM ' . prefix('options') . ' WHERE `creator` LIKE ' . db_quote('%' . basename($owner));
			$result = query($sql);
			if (preg_match('~^' . THEMEFOLDER . '/~', $owner)) {
				if ($owner == THEMEFOLDER . '/') {
					$purgedActive[] = true;
					$where = ' WHERE `creator` = "' . THEMEFOLDER . '/"';
				} else {
					$where = ' WHERE `creator` LIKE ' . db_quote('%' . basename($owner) . '/themeoptions.php');
					if (file_exists(SERVERPATH . '/' . THEMEFOLDER . '/' . basename($owner) . '/themeoptions.php')) {
						$purgedActive[] = true;
					}
				}
				$sql = 'DELETE FROM ' . prefix('options') . $where;
				$result = query($sql);
			} else {
				purgeOption('zp_plugin_' . stripSuffix(basename($owner)));
				$purgedActive[basename($owner)] = true;
			}
		}
	}
	if (isset($_POST['missingplugin'])) {
		foreach ($_POST['missingplugin'] as $plugin) {
			purgeOption('zp_plugin_' . stripSuffix($plugin));
			unset($purgedActive[$plugin]);
		}
	}
	if (isset($_POST['missingcreator'])) {
		foreach ($_POST['missingcreator'] as $key => $action) {
			switch ($action) {
				case 1: // take no action
					break;
				case 2: //	purge
					$sql = 'DELETE FROM ' . prefix('options') . ' WHERE `id`=' . $key;
					$result = query($sql);
					break;
				case 3: //	mark as ingored
					$sql = 'UPDATE ' . prefix('options') . ' SET `creator`=' . db_quote(replaceScriptPath(__FILE__) . '[' . __LINE__ . ']') . ' WHERE `id`=' . $key;
					$result = query($sql);
					break;
			}
		}
	}

	if (!empty($purgedActive)) {
		requestSetup('purgeOptions');
	}
	header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/purgeOptions/purgeOptions_tab.php');
	exitZP();
}

printAdminHeader('options', '');
?>
<link rel="stylesheet" href="<?php echo FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/purgeOptions/purgeOptions.css" type="text/css">
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<?php zp_apply_filter('admin_note', 'clone', ''); ?>
			<div id="container">
				<?php printSubtabs(); ?>
				<div class="tabbox">
					<?php
					$owners = array(ZENFOLDER . '/' . PLUGIN_FOLDER => array(), USER_PLUGIN_FOLDER => array(), THEMEFOLDER => array());
					$sql = 'SELECT `name` FROM ' . prefix('options') . ' WHERE `name` LIKE "zp_plugin_%"';
					$result = query_full_array($sql);
					foreach ($result as $row) {
						$plugin = str_replace('zp_plugin_', '', $row['name']) . '.php';
						$file = str_replace(SERVERPATH, '', getPlugin($plugin, false));
						if ($file) {
							if (strpos($file, PLUGIN_FOLDER) === false) {
								$owners[USER_PLUGIN_FOLDER][$plugin] = $plugin;
							}
						} else {
							purgeOption($row['name']);
						}
					}

					$nullCreator = false;
					$sql = 'SELECT `creator` FROM ' . prefix('options') . ' ORDER BY `creator`';
					$result = query_full_array($sql);
					foreach ($result as $owner) {
						$structure = explode('/', preg_replace('~\[.*\]$~', '', $owner['creator']));
						switch ($structure[0]) {
							case NULL:
								$nullCreator = true;
								break;
							case THEMEFOLDER:
								$owners[THEMEFOLDER][] = $structure[1];
								break;
							case USER_PLUGIN_FOLDER:
								unset($structure[0]);
								$owners[USER_PLUGIN_FOLDER][] = implode('/', $structure);
								break;
							case ZENFOLDER:
								if ($structure[1] == PLUGIN_FOLDER) {
									unset($structure[0], $structure[1]);
									$owners[ZENFOLDER . '/' . PLUGIN_FOLDER][] = implode('/', $structure);
								}
								break;
						}
					}

					if ($nullCreator) {
						$empty = false;
						$sql = 'SELECT * FROM ' . prefix('options') . ' WHERE `creator` is NULL';
						$result = query_full_array($sql);
						foreach ($result as $opt) {
							if (strpos($opt['name'], 'zp_plugin_') === false) {
								if (empty($opt['value'])) {
									$empty = true;
									$orpahaned[$opt['id']] = '<span class="emptyOption">' . $opt['name'] . '</span>';
								} else {
									$orpahaned[$opt['id']] = $opt['name'];
								}
							}
						}
						if (!empty($orpahaned)) {
							natcasesort($orpahaned);
						}
					}
					if (isset($owners[ZENFOLDER . '/' . PLUGIN_FOLDER])) {
						$owners[ZENFOLDER . '/' . PLUGIN_FOLDER] = array_unique($owners[ZENFOLDER . '/' . PLUGIN_FOLDER]);
					}
					if (isset($owners[USER_PLUGIN_FOLDER])) {
						$owners[USER_PLUGIN_FOLDER] = array_unique($owners[USER_PLUGIN_FOLDER]);
					}
					if (isset($owners[THEMEFOLDER])) {
						$owners[THEMEFOLDER] = array_unique($owners[THEMEFOLDER]);
					}

					if (empty($owners) && !$nullCreator) {
						echo gettext('No option owners have been located.');
					} else {
						?>
						<form class="dirtylistening" onReset="setClean('purge_options_form');
									" id="purge_options_form" action="?page = options&tab = purge" method="post" >
										<?php XSRFToken('purgeOptions'); ?>
							<input type="hidden" name="purge" value="1" />.
							<p class = "buttons" >
								<button type="submit" value="<?php echo gettext('Apply')
										?>"> <img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/add.png" alt="" /> <strong><?php echo gettext("Apply"); ?> </strong></button >
								<button type="" "reset" value="<?php echo gettext('reset') ?>"> <img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/reset.png" alt="" /> <strong><?php echo gettext("Reset"); ?> </strong></button>
							</p>
							<br class="clearall" />

							<p>
								<?php
								echo gettext('Check an item to purge options associated with it.');
								?>
								<span class="highlighted">
									<?php echo gettext('Items that are <span class = "missing_owner">highlighted</span> appear to no longer to exist.') ?>
								</span>
							</p>
							<div class="highlighted purgeOptions_list">

								<span class = "missing_owner purgeOptionsClass">
									<?php echo gettext('highlighted'); ?>
									<input type = "checkbox" id = "missing" checked = "checked" onclick = "$('.missing').prop('checked', $('#missing').prop('checked'));">
								</span>

							</div>
							<br class="clearall">
							<?php
							if (!empty($owners)) {
								listOwners($owners);
							}
							if (!empty($orpahaned)) {
								$size = ceil(count($orpahaned) / 25);
								?>
								<br class="clearall">
								<div class="purgeOptions_list">
									<span class="purgeOptionsClass"><?php echo gettext('Orphaned options'); ?></span>
									<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/view.png' ?>">
									<input type="radio" name="orphaned" id="orphanedIgnore" onclick="$('.orphanedDelete').removeAttr('checked');$('.orphaned').removeAttr('checked');">
									<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/fail.png' ?>">
									<input type="radio" name="orphaned" id="orphanedDelete" onclick="$('.orphanedDelete').prop('checked', $('#orphanedDelete').prop('checked'));">
									<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/add.png' ?>">
									<input type="radio" name="orphaned" id="orphaned" onclick="$('.orphaned').prop('checked', $('#orphaned').prop('checked'));">
									<br />
									<ul class="purgeOptionsBlock"<?php if ($size > 1) echo ' style="' . "column-count:$size;	-moz-column-count: $size;	-webkit-column-count: $size;" . '"'; ?>>
										<?php
										foreach ($orpahaned as $key => $display) {
											?>
											<li>
												<label class="none">
													<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/view.png' ?>">
													<input type="radio" name="missingcreator[<?php echo $key; ?>]" class="orphanedIgnore" value="1" onclick="$(this).removeAttr('checked');"/>
													<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/fail.png' ?>">
													<input type="radio" name="missingcreator[<?php echo $key; ?>]" class="orphanedDelete" value="2" />
													<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/add.png' ?>">
													<input type="radio" name="missingcreator[<?php echo $key; ?>]" class="orphaned" value="3" />
													<?php echo $display; ?>
												</label>
											</li>
											<?php
										}
										?>
									</ul>
									<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/view.png' ?>">
									<?php echo gettext('no action'); ?>
									<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/fail.png' ?>">
									<?php echo gettext('delete'); ?>
									<img src="<?php echo WEBPATH . '/' . ZENFOLDER . '/images/add.png' ?>">
									<?php echo gettext('hide'); ?>
									<br />
									<?php
									if ($empty) {
										echo gettext('<span class="emptyOption">Denotes</span> an empty option value.');
									}
									?>
								</div>
								<?php
							}
							?>
							<br class="clearall" />
							<p class="buttons">
								<button type="submit" value="<?php echo gettext('Apply') ?>" > <img src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/images/add.png" alt = "" /> <strong><?php echo gettext("Apply"); ?> </strong></button>
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
