<?php
/* Generates "news" articles for plugins
 *
 * @package plugins
 */
$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = gettext('Generates news articles for supported plugins.');
$plugin_author = "Stephen Billard (sbillard)";

function pluginNews_button($buttons) {
	if (isset($_REQUEST['pluginNews'])) {
		XSRFdefender('pluginNews');
		processPlugins();
	}
	$buttons[] = array(
								'enable'=>true,
								'button_text'=>gettext('Plugin Articles'),
								'formname'=>'pluginNews_button',
								'action'=>'?pluginNews=gen',
								'icon'=>'images/add.png',
								'title'=>gettext('Generate plugin articles'),
								'alt'=>'',
								'hidden'=> '<input type="hidden" name="pluginNews" value="gen" />',
								'rights'=> ADMIN_RIGHTS,
								'XSRFTag' => 'pluginNews'
								);
	return $buttons;
}

function processPlugins() {
	global $_zp_current_admin_obj;
	$curdir = getcwd();
	$basepath = SERVERPATH."/".ZENFOLDER.'/'.PLUGIN_FOLDER.'/';
	chdir($basepath);
	$filelist = safe_glob('*.php');
	foreach ($filelist as $file) {
		$titlelink = stripSuffix(filesystemToInternal($file));
		$author = stripSuffix(basename(__FILE__));
		$sql = 'SELECT `id` FROM '.prefix('news').' WHERE `titlelink`='.db_quote($titlelink);
		$result = query_single_row($sql);
		if (empty($result)) {
			$plugin_news = new ZenpageNews($titlelink);

			$fp = fopen($basepath.$file,'r');
			$empty = true;
			$desc = '<p>';
			$tags = array($titlelink);
			$incomment = false;
			while($line = fgets($fp)) {
				if (strpos($line, '/*') !== false) {
					$incomment = true;
				}
				if ($incomment) {
					if (strpos($line, '*/') !== false) {
						break;
					}
					$i = strpos($line, '*');
					$line = trim(trim(substr($line, $i+1), '*'));
					if (empty($line)) {
						if (!$empty) {
							$desc .= '<p>';
						}
						$empty = true;
					} else {
						if (strpos($line,'@') === 0) {
							$line = trim($line, '@');
							$i = strpos($line, ' ');
							$mod = substr($line, 0, $i);
							$line = trim(substr($line, $i+1));
							switch ($mod) {
								case 'author':
									$desc .= 'Author: '.html_encode($line).' ';
									$empty = false;
									preg_match_all('|\((.+?)\)|', $line, $matches);
									$tags = array_merge($tags, $matches[1]);
									$author = array_shift($matches[1]);
									break;
								case 'package':
								case 'subpackage':
									$tags[] = $line;
									break;
								case 'tags':
									$pluginTags = explode(',',$line);
									foreach ($pluginTags as $tag) {
										$tags[] = trim(unQuote($tag));
									}
									break;
							}
						} else {
							$desc .= html_encode($line).' ';
							$empty = false;
						}
					}
				}
			}

			$desc .= '</p>';
			fclose($fp);
			$plugin_news->setShow(0);
			$plugin_news->setDateTime(date('Y-m-d H:i:s'),filemtime($file));
			$plugin_news->setAuthor($author);
			$plugin_news->setTitle($titlelink);
			$plugin_news->setContent($desc);
			$plugin_news->setTags($tags);
			$plugin_news->setCategories(array('officially-supported','extensions'));
			$plugin_news->setCustomData("http://www.zenphoto.org/documentation/plugins/_".PLUGIN_FOLDER."---".$titlelink.".html");
			$plugin_news->save();
		}
	}
	chdir($curdir);

}

zp_register_filter('admin_utilities_buttons', 'pluginNews_button');

?>