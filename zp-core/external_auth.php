<?php
/**
 *
 * Root class for external authorizaton plugins
 *
 * @author Stephen Billard (sbillard)
 * @package core
 */

class external_auth {

	var $auth='external';

	/**
	 * returns an array with the user details from the external authorization
	 */
	protected function user() {
		return NULL;
	}

	/**
	 * This is the cookie processor filter handler
	 * Check if there is a valid MyBB cookie that can be used to authorize the visitor
	 * @param BIT $authorized
	 */
	function check($authorized) {
		global $_zp_current_admin_obj;
		if (!$authorized) {	// not logged in via normal Zenphoto handling
			if ($result = $this->user()) {
				$user = $result['user'];
				unset($result['user']);
				$searchfor = array('`user`=' => $user,  '`valid`=' => 1);
				$userobj = Zenphoto_Authority::getAnAdmin($searchfor);
				if (!$userobj) {
					//	create a transient user
					$userobj = new Zenphoto_Administrator('', 1);
					$userobj->setUser($user);
					//	Flag as external credentials for completeness
					$userobj->setCredentials(array($this->auth,'user','email'));
					//	populate the user properties
					foreach ($result as $key=>$value) {
						switch ($key) {
							case 'groups':
								//	find the Zenphoto group corresponding to the MyBB one (if it exists)
								$member = false;
								$rights = 0;
								$objects = array();
								$groups = $value;
								foreach ($groups as $key=>$group) {
									$groupobj = Zenphoto_Authority::getAnAdmin(array('`user`=' => $group,'`valid`=' => 0));
									if ($groupobj) {
										$member = true;
										$rights = $groupobj->getRights() | $rights;
										$objects = array_merge($groupobj->getObjects(), $objects);
										if ($groupobj->getName() == 'template') {
											unset($groups[$key]);
										}
									} else {
										unset($groups[$key]);
									}
								}
								if (!$member) {
									//	No such Zenphoto group, use the default Zenphoto group for MyBB users
									$group = getOption('MyBB_auth_myBB_default_group');
									$groupobj = Zenphoto_Authority::getAnAdmin(array('`user`=' => $group,'`valid`=' => 0));
									$rights = $groupobj->getRights();
									$objects = $groupobj->getObjects();
									if ($groupobj->getName() != 'template') {
										$groups = NULL;
									}
								}
								//	setup standard user items
								$userobj->setGroup(implode(',',$groups));
								$userobj->setRights($rights);
								$userobj->setObjects($objects);
								break;
							case 'objects':
								$userobj->setObjects($objects);
								break;
							case 'album':
								$userobj->createPrimealbum(false, $value);
								break;
							default:
								$userobj->set($key,$value);
								break;
						}
					}
				}
				if (isset($result['logout_link'])) {
					$userobj->logout_link = $result['logout_link'];
				}
				$_zp_current_admin_obj = $userobj;
				$authorized = $_zp_current_admin_obj->getRights();
			}
		}
		return $authorized;
	}


}
?>