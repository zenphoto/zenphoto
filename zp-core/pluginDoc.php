<?php
/**
 *
 * Displays a "plugin usage" document based on the plugin's doc comment block.
 *
 * Supports the following PHPDoc markup tags:
 * 	<i> for emphasis
 *  <b> for strong
 *  <code> for mono-spaced text
 *  <hr> for horizontal rule
 *  <ul><li>, <ol><li> for lists
 *  <pre>
 *  <br> for line breaks
 *  <var> for variables (treated the same as <code>)
 *  <a href= ...></a>
 *  <img src= ... />
 *
 * @package admin
 */
define('OFFSET_PATH', 2);
require_once(dirname(__FILE__).'/admin-globals.php');

admin_securityChecks(NULL, currentRelativeURL());

$markup = array(
						'&lt;i&gt;'=>'<em>',
						'&lt;/i&gt;'=>'</em>',
						'&lt;b&gt;'=>'<strong>',
						'&lt;/b&gt;'=>'</strong>',
						'&lt;code&gt;'=>'<span class="inlinecode">',
						'&lt;/code&gt;'=>'</span>',
						'&lt;hr&gt;'=>'<hr />',
						'&lt;ul&gt;'=>'<ul>',
						'&lt;/ul&gt;'=>'</ul>',
						'&lt;ol&gt;'=>'<ol>',
						'&lt;/ol&gt;'=>'</ol>',
						'&lt;li&gt;'=>'<li>',
						'&lt;/li&gt;'=>'</li>',
						'&lt;pre&gt;'=>'<pre>',
						'&lt;/pre&gt;'=>'</pre>',
						'&lt;br&gt;'=>'<br />',
						'&lt;var&gt;'=>'<span class="inlinecode">',
						'&lt;/var&gt;'=>'</span>'
);
$const_tr = array('%ZENFOLDER%'=>ZENFOLDER,
									'%PLUGIN_FOLDER%'=>PLUGIN_FOLDER,
									'%USER_PLUGIN_FOLDER%'=>USER_PLUGIN_FOLDER,
									'%ALBUMFOLDER%'=>ALBUMFOLDER,
									'%THEMEFOLDER%'=>THEMEFOLDER,
									'%BACKUPFOLDER%'=>BACKUPFOLDER,
									'%UTILITIES_FOLDER%'=>UTILITIES_FOLDER,
									'%DATA_FOLDER%'=>DATA_FOLDER,
									'%CACHEFOLDER%'=>CACHEFOLDER,
									'%UPLOAD_FOLDER%'=>UPLOAD_FOLDER,
									'%STATIC_CACHE_FOLDER%'=>STATIC_CACHE_FOLDER,
									'%FULLWEBPATH%'=>FULLWEBPATH,
									'%WEBPATH%'=>WEBPATH
);

$extension = sanitize($_GET['extension']);
$thirdparty = isset($_GET['thirdparty']);
if ($thirdparty) {
	$path = SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/'.$extension.'.php';
} else {
	$path = SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/'.$extension.'.php';
}

$plugin_description = '';
$plugin_notice = '';
$plugin_disable = '';
$plugin_author = '';
$plugin_version = '';
$plugin_is_filter = '';
$plugin_URL = '';
$option_interface = '';

@require_once($path);
$buttonlist = zp_apply_filter('admin_utilities_buttons', array());

$pluginStream = @file_get_contents($path);

if ($thirdparty) {
	$whose = gettext('third party plugin');
	$path = stripSuffix($path).'/logo.png';
	if (file_exists($path)) {
		$ico = str_replace(SERVERPATH, WEBPATH, $path);
	} else {
		$ico = 'images/place_holder_icon.png';
	}
} else {
	$subpackage = false;
	$whose = 'Zenphoto official plugin';
	$ico = 'images/zp_gold.png';
}
$regex_Url = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
$regex_img = '|&lt;img(\s*)src=(\s*)&quot;(.*)&quot;(\s*)/&gt;|';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link rel="stylesheet" href="<?php echo WEBPATH.'/'.ZENFOLDER; ?>/admin.css" type="text/css" />
	<meta http-equiv="content-type" content="text/html; charset=<?php echo LOCAL_CHARSET; ?>" />
	<title><?php echo sprintf(gettext('%1$s %2$s: %3$s'),html_encode($_zp_gallery->getTitle()),gettext('admin'),html_encode($extension)); ?></title>
	<style>
	.doc_box_field {
		padding-left: 0px;
		padding-right: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		margin: 15px;
		border: 1px solid #cccccc;
		width: 460px;
		-moz-border-radius: 5px;
		-khtml-border-radius: 5px;
		-webkit-border-radius: 5px;
		border-radius: 5px;
	}
	.moc_button {
		display: block;
		float: left;
		width: 200px;
		margin: 0 7px 0 0;
		background-color: #f5f5f5;
		background-image: url(images/admin-buttonback.jpg);
		background-repeat: repeat-x;
		border: 1px solid #dedede;
		border-top: 1px solid #eee;
		border-left: 1px solid #eee;
		font-family: "Lucida Grande", Tahoma, Arial, Verdana, sans-serif;
		font-size: 100%;
		line-height: 130%;
		text-decoration: none;
		font-weight: bold;
		color: #565656;
		cursor: pointer;
		padding: 5px 10px 6px 7px; /* Links */
		-moz-border-radius: 5px;
		-khtml-border-radius: 5px;
		-webkit-border-radius: 5px;
		border-radius: 5px;
	}
	.buttons .tip {
		text-align: left;
	}
	ul.options  {
		list-style: none;
		margin-left: 0;
		padding: 0;
	}
	ul.options li {
		list-style: none;
		margin-left: 1.5em;
		padding-bottom: 2px;
	}
	</style>
</head>
<body>
	<div id="main">
		<?php echo gettext('Plugin usage information'); ?>
		<div id="content">
			<h1><img class="zp_logoicon" src="<?php echo $ico; ?>" alt="<?php echo gettext('logo'); ?>" title="<?php echo $whose; ?>" /><?php echo html_encode($extension); ?></h1>
			<div class="border">
				<?php echo $plugin_description; ?>
			</div>
				<?php
				if ($thirdparty) {
					?>
					<h3><?php printf( gettext('Version: %s'), $plugin_version); ?></h3>
					<?php
					}
				?>
				<h3><?php printf(gettext('Author: %s'), html_encode($plugin_author)); ?></h3>
			<div>
			<?php
			if ($plugin_disable) {
				?>
				<p class="warningbox">
					<?php echo $plugin_disable; ?>
				</p>
				<?php
			}
			if ($plugin_notice) {
				?>
				<div class="notebox">
					<?php echo $plugin_notice; ?>
				</div>
				<?php
			}
			$i = strpos($pluginStream, '/*');
			$j = strpos($pluginStream, '*/');
			if ($i !== false && $j !== false) {
				$commentBlock = strtr(substr($pluginStream, $i+2, $j-$i-2), $const_tr);
				$lines = explode('*', $commentBlock);
				$doc = '';
				$par = false;
				$empty = false;

				foreach ($lines as $line) {
					$line = trim($line);
					if (empty($line)) {
						if (!$empty) {
							if ($par) {
								$doc .=  '</p>';
							}
							$doc .= '<p>';
							$empty = $par = true;
						}
					} else {
						if (strpos($line, '@') === 0) {
							preg_match('/@(.*?)\s/',$line,$matches);
							if (!empty($matches)) {
								switch ($matches[1]) {
									case 'author':
										$plugin_author = trim(substr($line, 8));
										break;
									case 'subpackage':
										$subpackage = trim(substr($line, 11)).'/';
										break;
								}
							}
						} else {
							$line = strtr(html_encode($line),$markup);
							if(preg_match($regex_Url, $line, $url)) {
								$line = preg_replace($regex_Url, '<a href="'.$url[0].'">'.$url[0].'</a> ', $line);
							} else {
								if (preg_match($regex_img, $line, $img)) {
									$line = preg_replace($regex_img, '<img src="'.$img[3].'" />', $line);
								}
							}
							$doc .= $line.' ';
							$empty = false;
						}
					}
				}
				if ($par) {
					$doc .=  '</p>';
					echo $doc;
					$doc = '';
				}
			echo $doc;
			}

			if ($option_interface) {
				if (is_string($option_interface)) {
					$option_interface = new $option_interface;
				}
				$options = $supportedOptions = $option_interface->getOptionsSupported();
				$option = array_shift($options);
				if (array_key_exists('order', $option)) {
					$options = sortMultiArray($supportedOptions, 'order');
					$options = array_keys($options);
				} else {
					$options = array_keys($supportedOptions);
					natcasesort($options);
				}
				?>
				<p>
				<?php echo ngettext('Option:','Options:',count($options)); ?>
				<ul class="options">
					<?php
					foreach ($options as $option) {
						$row = $supportedOptions[$option];
						$option = trim($option,'*'.chr(0));
						if ($option && $row['type'] != OPTION_TYPE_NOTE) {
							?>
							<li><code><?php echo $option; ?></code></li>
							<?php
						}
					}
					?>
				</ul>
				</p>
				<?php
			}
			if (!empty($buttonlist)) {
				$buttonlist = sortMultiArray($buttonlist, array('category','button_text'), false);
				?>
				<div id="overview-utility">
				<p>
				<?php echo ngettext('Overview utility button','Overview utility buttons',count($buttonlist)); ?>
				</p>
					<?php
					$category = '';
					foreach ($buttonlist as $button) {
						$button_category = $button['category'];
						$button_icon = $button['icon'];
						if ($category != $button_category) {
							if ($category) {
								?>
								</fieldset>
								<?php
							}
							$category = $button_category;
							?>
							<fieldset class="doc_box_field"><legend><?php echo $category; ?></legend>
							<?php
						}
						?>
						<form class="overview_utility_buttons">
							<div class="moc_button tip" title="<?php echo $button['title']; ?>" >
								<?php
								if(!empty($button_icon)) {
									?>
									<img src="<?php echo $button_icon; ?>" alt="<?php echo $button['alt']; ?>" />
									<?php
								}
								echo html_encode($button['button_text']);
								?>
							</div>
						</form>
						<?php
					}
					if ($category) {
						?>
						</fieldset>
						<?php
					}
					?>
				</div><!-- overview-utility -->
				<br clear="all">
				<?php
			}
			?>
			</div>
		</div>
		<?php
		if ($thirdparty) {
			if ($plugin_URL) {
				printf(gettext('See also the <a href="%1$s">%2$s</a>'),$plugin_URL, $extension);
			}
		} else {
			$plugin_URL = 'http://www.zenphoto.org/documentation/plugins/'.$subpackage.'_'.PLUGIN_FOLDER.'---'.$extension.'.php.html';
			printf(gettext('See also the Zenphoto online documentation: <a href="%1$s">%2$s</a>'),$plugin_URL, $extension);
		}
		?>
	</div>
</body>
