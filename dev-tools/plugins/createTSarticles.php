<?php
/* Generates "news" articles for plugins
 *
 * @package plugins
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('Generates Troubleshooting news articles.');
$plugin_author = "Stephen Billard (sbillard)";

zp_register_filter('admin_utilities_buttons', 'Troubleshooting_button');

function Troubleshooting_button($buttons) {
	if (isset($_REQUEST['Troubleshooting'])) {
		XSRFdefender('Troubleshooting');
		processTroubleshooting();
	}
	$buttons[] = array(
								'enable'=>true,
								'button_text'=>gettext('Troubleshooting Articles'),
								'formname'=>'Troubleshooting_button',
								'action'=>'?Troubleshooting=gen',
								'icon'=>'images/add.png',
								'title'=>gettext('Generate Troubleshooting articles'),
								'alt'=>'',
								'hidden'=> '<input type="hidden" name="Troubleshooting" value="gen" />',
								'rights'=> ADMIN_RIGHTS,
								'XSRFTag' => 'Troubleshooting'
								);
	return $buttons;
}

function makeArticle($class,$text) {
	global $unique;
	$unique ++;
	$i = strpos($text, '</a>');
	$j = strpos($text, '</h4>');
	$h4 = substr($text, $i+4, $j-$i-4);
	$text = substr($text, $j+5);
	$text = str_replace('<hr />', '', $text);
	$text = str_replace('<hr/>', '', $text);
	$ts_news = new ZenpageNews(seoFriendly($class.'_'.trim(truncate_string(strip_tags($h4),30,'')).'_'.$unique), true);
	$ts_news->setShow(0);
	$ts_news->setDateTime(date('Y-m-d H:i:s'));
	$ts_news->setAuthor('TSGenerator');
	$ts_news->setTitle($h4);
	$ts_news->setContent($text);
	$ts_news->setCategories(array());
	$ts_news->setCategories(array('troubleshooting','troubleshooting-'.$class));
	$ts_news->save();
}

function processTroubleshooting() {
	global $_zp_current_admin_obj;
	$curdir = getcwd();
	$basepath = SERVERPATH."/".UPLOAD_FOLDER.'/';
	chdir($basepath);
	$filelist = array('zenphoto'=>'ts.html','zenpage'=>'zp_TS.html');
	foreach ($filelist as $class=>$file) {
		$data = file_get_contents($basepath.$file);
		$i = strpos($data, '<h4><a name=');
		if ($i !== false) {
			$data = substr($data, $i);
		}
		while (!empty($data)) {
			$i = strpos($data,'<h4><a name=', 4);
			if ($i === false) {
				if (!empty($data)) {
					makeArticle($class,$data);
				}
				break;
			} else {
				makeArticle($class,substr($data, 0, $i));
				$data = substr($data, $i);
			}
		}
	}
	chdir($curdir);
}

?>