<?php

/**
 * USER credentials handlers
 *
 * Class Plugins may override the stand class-auth authentication library. ZenPhoto20 supplies
 * LDAP_auth, an authentication plugin that authenticates via an LDAP server. It
 * also provides a good example of how to implement alternative Authorities.
 *
 *
 * Replacement libraries must implement two classes:
 * 		"Zenphoto_Authority" class: Provides the methods used for user authorization and management
 * 			store an instantiation of this class in $_zp_authority.
 *
 * 		"Zenphoto_Administrator" class: supports the basic needs for object manipulation of administrators.
 *
 * (You can include the <code>lib-auth.php</code> script and extend/overwrite class methods if that suits your needs.)
 *
 * The global $_zp_current_admin_obj represents the current admin.
 *
 * The following elements need to be present in any alternate implementation in the
 * array returned by getAdministrators().
 *
 * 		In particular, there should be array elements for:
 * 				'id' (unique), 'user' (unique),	'pass',	'name', 'email', 'rights', 'valid',
 * 				'group', and 'other_credentials'
 *
 * 		So long as all these indices are populated it should not matter when and where
 * 		the data is stored.
 *
 * 		Administrator class methods are required for these elements as well.
 *
 * 		The getRights() method must define at least the rights defined by the method in
 * 		this library.
 *
 * 		The checkAuthorization() method should promote the "most privileged" Admin to
 * 		ADMIN_RIGHTS to insure that there is some user capable of adding users or
 * 		modifying user rights.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage admin
 */
require_once(dirname(__FILE__) . '/lib-auth.php');

class Zenphoto_Authority extends _Authority {

}

class Zenphoto_Administrator extends _Administrator {

}
