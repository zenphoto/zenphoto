<?php

/**
 * USER credentials handlers
 *
 * An alternate authorization script may be provided to override this script. To do so, make a script that
 * implements the classes declared below. Place the new script inthe <ZENFOLDER>/plugins/alt/ folder. ZenPhoto20
 * will then will be automatically loaded the alternate script in place of this one.
 *
 * Replacement libraries must implement two classes:
 * 		"Zenphoto_Authority" class: Provides the methods used for user authorization and management
 * 			store an instantiation of this class in $_zp_authority.
 *
 * 		"Zenphoto_Administrator" class: supports the basic needs for object manipulation of administrators.
 *
 * (You can include the <code>lib-auth.php</code> script and extend/overwrite class methods if that suits your needs.)
 *
 * The global $_zp_current_admin_obj represents the current admin with.
 * The library must instantiate its authority class and store the object in the global $_zp_authority
 * (Note, this library does instantiate the object as described. This is so its classes can
 * be used as parent classes for lib-auth implementations. If auth_zp.php decides to use this
 * library it will instantiate the class and store it into $_zp_authority.
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
