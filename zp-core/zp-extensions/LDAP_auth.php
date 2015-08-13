<?php
/*
 * LDAP authorization plugin
 *
 * This plugin will link ZenPhoto20 to an LDAP server for user verification.
 * It assumes that your LDAP server contains posix-style users and groups.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage users
 */

$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext('Enable LDAP user authentication.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_disable = function_exists('ldap_connect') ? '' : gettext('php_ldap extension is not enabled');
$option_interface = 'LDAP_auth_options';

if (!($plugin_disable || class_exists('Zenphoto_Authority'))) {
	require_once(SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/LDAP_auth/LDAP auth.php');
}

class LDAP_auth_options {

	function __construct() {
		global $_zp_authority;
		if (extensionEnabled('user_groups')) {
			$ldap = getOption('ldap_group_map');
			if (is_null($ldap)) {
				$groups = $_zp_authority->getAdministrators('groups');
				if (!empty($groups)) {
					foreach ($groups as $group) {
						if ($group['name'] != 'template') {
							$ldap[$group['user']] = $group['user'];
						}
					}
				}
				if (!empty($ldap)) {
					setOption('ldap_group_map', serialize($ldap));
				}
			}
		}
	}

	static function getOptionsSupported() {
		setOptionDefault('ldap_id_offset', 100000);
		$ldapOptions = array(
						gettext('LDAP domain')								 => array('key'		 => 'ldap_domain', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1,
										'desc'	 => gettext('Domain name of the LDAP server')),
						gettext('LDAP base dn')								 => array('key'		 => 'ldap_basedn', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1.1,
										'desc'	 => gettext('Base DN strings for the LDAP searches.')),
						gettext('ID offset for LDAP usersids') => array('key'		 => 'ldap_id_offset', 'type'	 => OPTION_TYPE_NUMBER,
										'order'	 => 1.4,
										'desc'	 => gettext('This number is added to the LDAP <em>userid</em> to insure that there is no overlap to ZenPhoto20 <em>userids</em>.')),
						gettext('LDAP reader user')						 => array('key'		 => 'ldap_reader_user', 'type'	 => OPTION_TYPE_TEXTBOX,
										'order'	 => 1.2,
										'desc'	 => gettext('User name for LDAP searches. If empty the searches will be anonymous.')),
						gettext('LDAP reader password')				 => array('key'		 => 'ldap_reader_pass', 'type'	 => OPTION_TYPE_PASSWORD,
										'order'	 => 1.3,
										'desc'	 => gettext('User password for LDAP searches.'))
		);
		if (extensionEnabled('user_groups')) {
			$ldapOptions[gettext('LDAP Group map')] = array('key'		 => 'ldap_group_map_custom', 'type'	 => OPTION_TYPE_CUSTOM,
							'order'	 => 1.5,
							'desc'	 => gettext('Mapping of LDAP groups to ZenPhoto20 groups') . '<p class="notebox">' . gettext('<strong>Note:</strong> if the LDAP group is empty no mapping will take place.') . '</p>');
			if (!extensionEnabled('LDAP_auth')) {
				$ldapOptions['note'] = array(
								'key'		 => 'LDAP_auth_note', 'type'	 => OPTION_TYPE_NOTE,
								'order'	 => 0,
								'desc'	 => '<p class="notebox">' . gettext('The LDAP Group map cannot be managed with the plugin disabled') . '</p>');
			}
		}
		return $ldapOptions;
	}

	static function handleOption($option, $currentValue) {
		global $_zp_authority;
		if ($option == 'ldap_group_map_custom') {
			$groups = $_zp_authority->getAdministrators('groups');
			$ldap = getSerializedArray(getOption('ldap_group_map'));
			if (empty($groups)) {
				echo gettext('No groups or templates are defined');
			} else {
				?>
				<dl>
					<dt><em><?php echo gettext('ZenPhoto20 group'); ?></em></dt>
					<dd><em><?php echo gettext('LDAP group'); ?></em></dd>
					<?php
					foreach ($groups as $group) {
						if ($group['name'] != 'template') {
							if (array_key_exists($group['user'], $ldap)) {
								$ldapgroup = $ldap[$group['user']];
							} else {
								$ldapgroup = $group['user'];
							}
							?>
							<dt>
							<?php echo html_encode($group['user']); ?>
							</dt>
							<dd>
								<?php echo '<input type="textbox" name="LDAP_group_for_' . $group['id'] . '" value="' . html_encode($ldapgroup) . '">'; ?>
							</dd>
							<?php
						}
					}
					?>
				</dl>
				<?php
			}
		}
	}

	static function handleOptionSave($themename, $themealbum) {
		global $_zp_authority;
		$groups = $_zp_authority->getAdministrators('groups');
		if (!empty($groups)) {
			$ldap = NULL;
			foreach ($_POST as $key => $v) {
				if (strpos($key, 'LDAP_group_for_') !== false) {
					$ldap[$groups[substr($key, 15)]['user']] = $v;
				}
			}
			if ($ldap) {
				setOption('ldap_group_map', serialize($ldap));
			}
		}
	}

}
