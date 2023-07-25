<?php
/**
 * Database quick reference
 *
 * @package zpcore\admin\utilities
 */

define('OFFSET_PATH', 3);

require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');

$buttonlist[] = $mybutton = array(
		'category' => gettext('Info'),
		'enable' => true,
		'button_text' => gettext('Database info'),
		'formname' => 'database_reference.php',
		'action' => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/database_reference.php',
		'icon' => FULLWEBPATH . '/' . ZENFOLDER . '/images/info.png',
		'title' => gettext('Shows all database table and field info for quick reference.'),
		'alt' => '',
		'hidden' => '',
		'rights' => ADMIN_RIGHTS
);

admin_securityChecks(NULL, currentRelativeURL());

if (isset($_POST['dbname']) || isset($_POST['dbuser']) || isset($_POST['dbpass']) || isset($_POST['dbhost'])) {
	XSRFdefender('databaseinfo');
}

$webpath = WEBPATH . '/' . ZENFOLDER . '/';

$_zp_admin_menu['overview']['subtabs'] = array(gettext('Database') => FULLWEBPATH . '/' . ZENFOLDER . '/' . UTILITIES_FOLDER . '/database_reference.php');
printAdminHeader('overview','Database');

?>
<link rel="stylesheet" href="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/css/admin-statistics.css" type="text/css" media="screen" />
<style>

.bordered td {
	border: 1px solid  #E5E5E5;
	width:16%;
}

.bordered tr.grayback td {
	background-color: #FAFAFA !important;
}

.field {
	font-weight: bold;
}

h2, h3 {
	font-weight: bold;
	margin-top: 30px;
	font-size: 15px;
}

h2 {
	margin: 0;
}
</style>
</head>
<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
<?php printSubtabs() ?>
<div class="tabbox">
<?php zp_apply_filter('admin_note','database', ''); ?>
<h1><span id="top"><?php echo $mybutton['button_text']; ?></span></h1>
<p>
	<?php echo $mybutton['title']; ?>
	<?php echo gettext("The internal Zenphoto table relations can be viewed on the PDF database reference that is included in the release package within the /docs_files folder of your Zenphoto installation. For more detailed info about the database use tools like phpMyAdmin."); ?>
</p>
<?php
$database_name = $_zp_db->getDBName();
$prefix = $_zp_db->getPrefix();
$tables = $_zp_db->getTables();
?>
<hr />
<ul>
<li>
<?php
$dbsoftware = $_zp_db->getSoftware();
printf(gettext('%1$s version: <strong>%2$s</strong>'),$dbsoftware['application'],$dbsoftware['version']);
?>
</li>
<li><?php printf(gettext('Database name: <strong>%1$s</strong>'),$database_name); ?></li>
<li>
<?php
if(empty($prefix)) {
	echo gettext('Table prefix: no prefix');
} else {
	echo sprintf(gettext('Table prefix: <strong>%1$s</strong>'),$prefix);
}
?>
</li>
</ul>
<?php 
if ($_zp_db->isUtf8System('database', 'any')) {
	echo '<p class="messagebox">' . gettext('The database is UTF-8') . '</p>';
} else {
	echo '<p class="warningbox">' . gettext('The database is not fully set to UTF-8 (utf8mb4). If you encounter data encoding issues try changing the configuration.') . '</p>';
}
if ($_zp_db->isUtf8System('server', 'any')) {
	echo '<p class="messagebox">' . gettext('The database server is UTF-8') . '</p>';
} else {
	echo '<p class="warningbox">' . gettext('The database is not fully set to UTF-8 (utf8mb4). If you encounter data encoding issues try changing the configuration.') . '</p>';
}
?>
<ul>
<?php
$result = $_zp_db->getDBInfo('charsets');
if ($result) {
	foreach ($result as $row) {
	?>
	<li><?php echo $row['Variable_name']; ?>: <strong><?php echo $row['Value']; ?></strong></li>
	<?php
	}
}

?>
</ul>
<ul>
<?php
$result = $_zp_db->getDBInfo('collations');
if ($result) {
	foreach ($result as $row) {
	?>
	<li><?php echo $row['Variable_name']; ?>: <strong><?php echo $row['Value']; ?></strong></li>
	<?php
	}
}
?>
</ul>
<?php
if ($tables) {
	$non_utf8_tables = array();
	foreach ($tables as $table) {
		if (!$_zp_db->isUTF8Table($table, 'any')) {
			$non_utf8_tables[] = $table;
		}
	}
	if ($non_utf8_tables) {
		echo '<div class="warningbox">';
		echo '<p>' . gettext('The following tables are not UTF-8.') . '</p>';
		echo '<ul>';
		foreach ($non_utf8_tables as $non_utf8_table) {
			echo '<li>' . $non_utf8_table . '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
}
?>
<hr />
<script>
function toggleRow(id) {
	if ($('#'+id).is(":visible")) {
		$('#'+id+'_k').hide();
		$('#'+id).hide();
	} else {
		$('#'+id+'_k').show();
		$('#'+id).show();
	}
}
</script>
<?php
$i = 0;
foreach($tables as $table) {
	$table = substr($table,strlen($prefix));
	$i++;
	?>
	<h3><a href="javascript:toggleRow('t_<?php echo $i; ?>')"><?php echo $table; ?></a></h3>
	<table id = "t_<?php echo $i; ?>" class="bordered" <?php if ($i>1) { ?>style="display: none;" <?php } ?>>
		<tr>
			<?php
			$cols = $tablecols = $_zp_db->getFields($table);
			$cols = array_shift($cols);
			foreach ($cols as $col=>$value) {
				 ?>
				 <th><?php echo $col; ?></th>
				 <?php
			}
			?>
		</tr>
		<?php
		$rowcount = 0;
		foreach($tablecols as $col) {
			$rowcount++;
			if($rowcount % 2 == 0) {
				$rowclass = ' class="grayback"';
			} else {
				$rowclass ='';
			}
			?>
			<tr<?php echo $rowclass; ?>>
			<?php
			$fieldcount = '';
			foreach($col as $field) {
				$fieldcount++;
				$class = '';
				if($fieldcount == 1) {
					$class = ' class="field"';
				}
				?>
				<td<?php echo $class; ?>><?php echo $field; ?></td>
				<?php
			}
			?>
			</tr>
		 <?php
		}
	 ?>
 </table>
	<?php
	$sql = 'SHOW KEYS FROM '.$_zp_db->prefix($table);
	$result = $_zp_db->queryFullArray($sql);
	$nest = '';
	?>
	<div style="width:40%">
	<table id = "t_<?php echo $i; ?>_k" class="bordered" <?php if ($i>1) { ?>style="display: none;" <?php } ?>>
		<tr>
			<th<?php echo $class; ?>>
				<?php echo gettext('Key'); ?>
			</th>
			<th<?php echo $class; ?>>
				<?php echo gettext('Column'); ?>
			</th>
		</tr>
	<?php
	foreach ($result as $key) {
		?>
		<tr>
			<td<?php echo $class; ?>>
			<?php
			if ($nest != $key['Key_name']) {
				echo $nest = $key['Key_name'];
				if (!$key['Non_unique']) {
					echo '*';
				}
			}
			?>
			</td>
			<td<?php echo $class; ?>>
				<?php echo $key['Column_name']; ?>
			</td>
		</tr>
		<?php
	}
	?>
	</table>
	</div>
	<?php

}
?>
</div>
</div><!-- content -->
</div><!-- main -->
<?php printAdminFooter(); ?>
</body>
</html>
