<?php
$plugin_is_filter = 9|ADMIN_PLUGIN;
$plugin_description = gettext('creates the installSignature function');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.2';

zp_register_filter('admin_utilities_buttons', 'installSignature_button');

function installSignature_button($buttons) {
	if (isset($_REQUEST['installSignature'])) {
		installSignature_gen();
	}
	$buttons[] = array(
								'category'=>gettext('development'),
								'enable'=>true,
								'button_text'=>gettext('installSignature Gen'),
								'formname'=>'installSignature_button',
								'action'=>'?installSignature=gen',
								'icon'=>'images/add.png',
								'title'=>gettext('Generate installSignature function'),
								'alt'=>'',
								'hidden'=> '<input type="hidden" name="installSignature" value="gen" />',
								'rights'=> ADMIN_RIGHTS
	);
	return $buttons;
}

function installSignature_gen() {
	$text = file_get_contents(__FILE__);
	$s = strpos($text,'/*'.'start installSignature*/')+strlen('/*'.'start installSignature*/')+1;
	$e = strpos($text,'/*'.'end installSignature*/')-1;

	$function_encoded = '';
	for ($i=$s;$i<$e;$i++) {
		$c = substr($text,$i,1);
		if ($c != "\t" && $c != "\n") {
			$function_encoded .= sprintf('\%03o',ord($c));
		}
	}

	$f = fopen(SERVERPATH.'/'.ZENFOLDER.'/Signature','w');
	fwrite($f, $function_encoded);
	fclose($f);
	header('Location: '.FULLWEBPATH.'/'.ZENFOLDER.'/admin.php?action=external&msg=installSignature file created.');
	exit();

	if (false) {
		/*start installSignature*/
		function installSignature() {
			$t1 = ZENPHOTO_VERSION.ZENPHOTO_RELEASE.':';
			if (isset($_SERVER['SERVER_SOFTWARE'])) {
				$t1 .= $_SERVER['SERVER_SOFTWARE'].':';
			} else {
				$t1 .= 'software unknown:';
			}
			if (defined('RELEASE')) {
				$t1 .= filesize(SERVERPATH.'/'.ZENFOLDER.'/functions.php').':';
			} else {
				$t1 .= 'SVN:';
			}
			$t1 .= dirname(__FILE__);
			$t1 = sha1($t1);
			$id = "{"	.substr($t1, 0, 8).'-'
								.substr($t1, 8, 4).'-'
								.substr($t1,12, 4).'-'
								.substr($t1,16, 4).'-'
								.substr($t1,20,12)."}";
			return $id;
		}
		/*end installSignature*/
	}
}
?>