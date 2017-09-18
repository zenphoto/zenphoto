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

admin_securityChecks(ADMIN_RIGHTS, $return = currentRelativeURL());

$xlate = array('plugins' => gettext('User plugins'), 'zp-core/zp-extensions' => gettext('Extensions'), 'themes' => gettext('Themes'));

if (isset($_POST['purge'])) {
	XSRFdefender('purgeOptions');
	$purgedActive = array();

	if (isset($_POST['del'])) {
		foreach ($_POST['del'] as $owner) {
			$sql = 'DELETE FROM ' . prefix('options') . ' WHERE `creator` LIKE ' . db_quote($owner . '%');
			query($sql);
			$sql = 'DELETE FROM ' . prefix('plugin_storage') . ' WHERE `type`=' . db_quote(basename($owner));
			query($sql);

			if (preg_match('~^' . THEMEFOLDER . '/~', $owner)) {
				if (file_exists(SERVERPATH . '/' . THEMEFOLDER . '/' . basename($owner) . '/themeoptions.php')) {
					$purgedActive[] = true; // theme still exists, need to re-run setup
				}
			} else {
				$plugin = basename($owner);
				if (!(isset($_POST['missingplugin']) && in_array($plugin, $_POST['missingplugin']))) {
					$purgedActive[basename($owner)] = true;
					purgeOption('zp_plugin_' . stripSuffix(basename($owner)));
				}
			}
		}
	}

	if (isset($_POST['missingcreator'])) {
		foreach ($_POST['missingcreator'] as $key => $action) {
			switch ($action) {
				case 1: // take no action
					$sql = 'UPDATE ' . prefix('options') . ' SET `creator`=NULL WHERE `id`=' . $key;
					$result = query($sql);
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
	header("Location: " . FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/purgeOptions/purgeOptions_tab.php?tab=purge');
	exitZP();
}

printAdminHeader('options', '');
$orphaned = array();
?>
<link rel="stylesheet" href="<?php echo FULLWEBPATH . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/purgeOptions/purgeOptions.css" type="text/css">
</head>
<body>
	<?php printLogoAndLinks(); ?>
	<div id="main">
		<?php printTabs(); ?>
		<div id="content">
			<div id="container">
				<?php zp_apply_filter('admin_note', 'clone', ''); ?>
				<h1><?php echo gettext('purge options'); ?></h1>
				<div class="tabbox">
					<?php
					$owners = array(ZENFOLDER . '/' . PLUGIN_FOLDER => array(), USER_PLUGIN_FOLDER => array(), THEMEFOLDER => array());
					$sql = 'SELECT `name` FROM ' . prefix('options') . ' WHERE `name` LIKE "zp\_plugin\_%"';
					$result = query_full_array($sql);
					foreach ($result as $row) {
						$plugin = str_replace('zp_plugin_', '', $row['name']);
						$file = str_replace(SERVERPATH, '', getPlugin($plugin . '.php', false));
						if ($file) {
							if (strpos($file, PLUGIN_FOLDER) === false) {
								$owners[USER_PLUGIN_FOLDER][strtolower($plugin)] = $plugin;
							}
						} else {
							purgeOption($row['name']);
						}
					}
					$sql = 'SELECT DISTINCT `type` FROM ' . prefix('plugin_storage');
					$result = query_full_array($sql);
					foreach ($result as $row) {
						$plugin = $row['type'];
						$file = str_replace(SERVERPATH, '', getPlugin($plugin . '.php', false));
						if ($file && strpos($file, PLUGIN_FOLDER) !== false) {
							$owners[ZENFOLDER . '/' . PLUGIN_FOLDER][strtolower($plugin)] = $plugin;
						} else {
							$owners[USER_PLUGIN_FOLDER][strtolower($plugin)] = $plugin;
						}
					}

					$sql = 'SELECT `creator` FROM ' . prefix('options') . ' ORDER BY `creator`';
					$result = query_full_array($sql);
					foreach ($result as $owner) {
						$structure = explode('/', preg_replace('~\[.*\]$~', '', $owner['creator']));
						switch ($structure[0]) {
							case NULL:
								break;
							case THEMEFOLDER:
								$owners[THEMEFOLDER][strtolower($structure[1])] = $structure[1];
								break;
							case USER_PLUGIN_FOLDER:
								unset($structure[0]);
								$creator = stripSuffix(implode('/', $structure));
								$owners[USER_PLUGIN_FOLDER][strtolower($creator)] = $creator;
								break;
							case ZENFOLDER:
								if ($structure[1] == PLUGIN_FOLDER) {
									unset($structure[0], $structure[1]);
									$creator = stripSuffix(implode('/', $structure));
									$owners[ZENFOLDER . '/' . PLUGIN_FOLDER][strtolower($creator)] = $creator;
								}
								break;
						}
					}
					ksort($owners[ZENFOLDER . '/' . PLUGIN_FOLDER]);
					ksort($owners[USER_PLUGIN_FOLDER]);
					ksort($owners[THEMEFOLDER]);

					$empty = $hiddenOptions = false;
					$sql = 'SELECT * FROM ' . prefix('options') . ' WHERE `creator` is NULL || `creator` LIKE "%purgeOptions%" ORDER BY `name`';
					$result = query_full_array($sql);
					foreach ($result as $opt) {
						if (strpos($opt['name'], 'zp_plugin_') === false) {
							if (empty($opt['value'])) {
								$empty = true;
								if (empty($opt['creator'])) {
									$orpahaned[$opt['id']] = array('display' => $opt['name'], 'class' => array('emptyOption')); //'<span class="emptyOption">' . $opt['name'] . '</span>';
								} else {
									$hiddenOptions = true;
									$orpahaned[$opt['id']] = array('display' => $opt['name'], 'class' => array('emptyOption', 'hiddenOrphanHighlight')); //'<span class="hiddenOrphanHighlight emptyOption">' . $opt['name'] . '</span>';
								}
							} else {
								if (empty($opt['creator'])) {
									$orpahaned[$opt['id']] = array('display' => $opt['name'], 'class' => array());
								} else {
									$hiddenOptions = true;
									$orpahaned[$opt['id']] = array('display' => $opt['name'], 'class' => array('hiddenOrphanHighlight')); //'<span class="hiddenOrphanHighlight">' . $opt['name'] . '</span>';
								}
							}
						}
					}



					if (empty($owners) && empty($orpahaned)) {
						echo gettext('No option owners have been located.');
					} else {
						?>
						<form class="dirtylistening" onReset="setClean('purge_options_form');" id="purge_options_form" action="?page=options&tab=purge" method="post" >
							<?php XSRFToken('purgeOptions'); ?>
							<input type="hidden" name="purge" value="1" />
							<p class = "buttons" >
								<button type="submit" value="<?php echo gettext('Apply') ?>">
									<?php echo CHECKMARK_GREEN; ?>
									<strong><?php echo gettext("Apply"); ?></strong>
								</button >
								<button type="reset" value="<?php echo gettext('reset') ?>">
									<?php echo CROSS_MARK_RED; ?>
									<strong><?php echo gettext("Reset"); ?></strong>
								</button>
							</p>
							<br class="clearall">

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
							if (!empty($orpahaned) || !empty($orpahanedb)) {
								$size = ceil(count($orpahaned) / 25);
								?>
								<br class="clearall">
								<div class="purgeOptions_list">
									<span class="purgeOptionsClass"><?php echo gettext('Orphaned options'); ?></span>
									<label title="<?php echo gettext('all: no acation'); ?>">
										<?php echo BULLSEYE_BLUE; ?>
										<input type="radio" name="orphaned" id="orphanedIgnore" onclick="$('.orphanedDelete').removeAttr('checked');$('.orphaned').removeAttr('checked');$('#emptyOptionCheck').removeAttr('checked');">
									</label>
									<label title="<?php echo gettext('all: delete'); ?>">
										<?php echo WASTEBASKET; ?>
										<input type="radio" name="orphaned" id="orphanedDelete" onclick="$('.orphanedDelete').prop('checked', $('#orphanedDelete').prop('checked'));">
									</label>
									<label title="<?php echo gettext('all: hide'); ?>">
										<?php echo HIDE_ICON; ?>
										<input type="radio" name="orphaned" id="orphaned" onclick="$('.orphaned').prop('checked', $('#orphaned').prop('checked'));$('#emptyOptionCheck').removeAttr('checked');">
									</label>
									<?php
									if ($empty) {
										?>
										<label title="<?php echo gettext('all: delete. It is generally safe to remove an orphaned option whose value is empty since referencing a non-existent option will return an empty value.'); ?>" >
											<?php
											echo gettext('<span class="emptyOption">empty</span>');
											?>
											<input type = "checkbox" id = "emptyOptionCheck" onclick = "$('.deleteEmpty').prop('checked', $('#emptyOptionCheck').prop('checked'));"></label>
										<?php
									}
									?>
									<br />
									<ul class = "purgeOptionsBlock"<?php
									if ($size > 1)
										echo ' style="' . "column-count:$size;	-moz-column-count: $size;	-webkit-column-count: $size;" . '"';
									?>>
												<?php
												foreach ($orpahaned as $key => $option) {
													$display = $option['display'];
													$classes = $option['class'];
													$hidden = in_array('hiddenOrphanHighlight', $classes);
													?>
											<li<?php if ($hidden) echo ' class="hiddenOrphan"'; ?>>
												<label class="none">
													<?php echo BULLSEYE_BLUE; ?>
													<input type="radio" name="missingcreator[<?php echo $key; ?>]" class="orphanedIgnore" value="1" <?php if (!$hidden) echo ' checked="checked"'; ?>/>
												</label>
												<label class="none">
													<?php echo WASTEBASKET; ?>
													<input type="radio" name="missingcreator[<?php echo $key; ?>]" class="orphanedDelete<?php if (in_array('emptyOption', $classes)) echo ' deleteEmpty'; ?>" value="2" />
												</label>
												<label class="none">
													<?php echo HIDE_ICON; ?>
													<input type="radio" name="missingcreator[<?php echo $key; ?>]" class="orphaned" value="3" <?php if ($hidden) echo ' checked="checked"'; ?>/>
												</label>

												<?php
												if (empty($classes)) {
													echo $display;
												} else {
													$title = '';
													if ($hidden) {
														$title = gettext('hidden');
													}
													if (in_array('emptyOption', $classes)) {
														if ($title) {
															$title .= ', ';
														}
														$title .= gettext('empty');
													}
													echo '<label title="' . $title . '" class="' . implode(' ', $classes) . '">' . $display . '</label>';
												}
												?>
											</li>
											<?php
										}
										?>
									</ul>
									<?php echo BULLSEYE_BLUE; ?>
									<?php echo gettext('no action'); ?>
									<?php echo WASTEBASKET; ?>
									<?php echo gettext('delete'); ?>
									<?php echo HIDE_ICON; ?>
									<?php echo gettext('hide'); ?>
									<br />
									<?php
									if ($empty) {
										echo gettext('<span class="emptyOption">Denotes</span> an empty option value.');
									}
									if ($hiddenOptions) {

										echo gettext(' <span class="hiddenOrphan"><span class="hiddenOrphanHighlight">Denotes</span> a "hidden" option.</span>');
										?>
										<br />
										<input type="checkbox" name="showHidden" id="showHidden" class="ignoredirty" onclick="$('.hiddenOrphan').toggle();"/>
										<?php
										echo gettext(' Show hidden orphans.');
									}
									?>
								</div>
								<?php
							}
							?>
							<br class="clearall">
							<p class="buttons">
								<button type="submit" value="<?php echo gettext('Apply') ?>" >
									<?php echo CHECKMARK_GREEN; ?>
									<strong><?php echo gettext("Apply"); ?></strong>
								</button>
								<button type="reset" value="<?php echo gettext('reset') ?>" >
									<?php echo CROSS_MARK_RED; ?>
									<strong><?php echo gettext("Reset"); ?></strong>
								</button>
							</p>
							<br class="clearall">
						</form>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		$('.hiddenOrphan').hide();
<?php
if (!isset($highlighted)) {
	?>
			$('.highlighted').remove();
	<?php
}
?>
	</script>
	<?php printAdminFooter(); ?>
</body>
</html>
