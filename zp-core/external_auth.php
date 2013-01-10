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
	 * it invokes the child class check() method to see if there is a valid visitor to the site
	 * The check() method should return "false" if there is no valid visitor or an array of
	 * User information if there is one.
	 *
	 * If there is a valid user, the user name is checked against Zenphoto users. If such user exists
	 * he will be automatically logged in. If no user by that userid exists a transient user will be
	 * created and logged in. User details are filled in from the user information in the passed array.
	 *
	 * Most enteries in the result array are simply stored into the user property of the same name. However,
	 * there are some special handling items that may be present:
	 * 	<ul>
	 * 		<li>groups: an array of the user's group membership</li>
	 * 		<li>objects: a Zenphoto "managed object list" array</li>
	 * 		<li>album: the name of the user's primary album</li>
	 * 		<li>logout_link: information that the plugin can use when a user loggs out</li>
	 *	</ul>
	 *
	 * All the above may be missing. However, if there is no groups entry, there needs to be an
	 * entry for the user's rights otherwise he will have none. There should not be both a rights entry
	 * and a groups entry as they are mutually exclusive.
	 *
	 * album and objects entries should come last in the list so all other properties are processed first as
	 * these methods may modify other properties.
	 *
	 * @param BIT $authorized
	 */
	function check($authorized) {
		global $_zp_current_admin_obj;
		if (!$authorized) {	// not logged in via normal Zenphoto handling
			if ($result = $this->user()) {
				$user = $result['user'];
				$searchfor = array('`user`=' => $user,  '`valid`=' => 1);
				$userobj = Zenphoto_Authority::getAnAdmin($searchfor);
				if (!$userobj) {
					unset($result['id']);
					unset($result['user']);
					$authority = '';
					//	create a transient user
					$userobj = new Zenphoto_Administrator('', 1);
					$userobj->setUser($user);
					$userobj->setRights(NO_RIGHTS);	//	just incase none get set
					//	Flag as external credentials for completeness
					$properties = array_keys($result);	//	the list of things we got from the external authority
					array_unshift($properties, $this->auth);
					$userobj->setCredentials($properties);
					//	populate the user properties
					$member = false;	//	no group membership (yet)
					foreach ($result as $key=>$value) {
						switch ($key) {
							case 'authority':
								$authority = '::'.$value;
								unset($result['authority']);
								break;
							case 'groups':
								//	find the corresponding Zenphoto group (if it exists)
								$rights = NO_RIGHTS;
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
								if ($member) {
									$userobj->setGroup(implode(',',$groups));
									$userobj->setRights($rights);
									$userobj->setObjects($objects);
								}
								break;
							case 'defaultgroup':
								if (!$member && isset($result['defaultgroup'])) {
									//	No Zenphoto group, use the default group
									$group = $result['defaultgroup'];
									$groupobj = Zenphoto_Authority::getAnAdmin(array('`user`=' => $group,'`valid`=' => 0));
									if ($groupobj) {
										$rights = $groupobj->getRights();
										$objects = $groupobj->getObjects();
										if ($groupobj->getName() != 'template') {
											$group = NULL;
										}
										$userobj->setGroup($group);
										$userobj->setRights($rights);
										$userobj->setObjects($objects);
									}
								}
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
					$properties = array_keys($result);	//	the list of things we got from the external authority
					array_unshift($properties, $this->auth.$authority);
					$userobj->setCredentials($properties);
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