<?php
/**
 * Database quick reference
 *
 * @package admin
 */

define('OFFSET_PATH', 3);
chdir(dirname(dirname(__FILE__)));

require_once(dirname(dirname(__FILE__)).'/admin-functions.php');
require_once(dirname(dirname(__FILE__)).'/admin-globals.php');
/*
if(getOption('zp_plugin_zenpage')) {
	require_once(dirname(dirname(__FILE__)).'/'.PLUGIN_FOLDER.'/zenpage/zenpage-admin-functions.php');
}
*/
$button_text = gettext('Database quick reference');
$button_hint = gettext('Shows all database table and field info for quick reference.');
$button_icon = 'images/info.png';
$button_rights = ADMIN_RIGHTS;

admin_securityChecks(NULL, currentRelativeURL(__FILE__));

if(isset($_POST['dbname']) || isset($_POST['dbuser']) || isset($_POST['dbpass']) || isset($_POST['dbhost'])) {
	XSRFdefender('databaseinfo');
}


$webpath = WEBPATH.'/'.ZENFOLDER.'/';
printAdminHeader(gettext('utilities'),gettext('reference'));
?>
<link rel="stylesheet" href="../admin-statistics.css" type="text/css" media="screen" />
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
<?php zp_apply_filter('admin_note','database', ''); ?>
<h1><a name="top"></a><?php echo $button_text; ?></h1>
<p>
	<?php echo $button_hint; ?>
	<?php echo gettext("The internal Zenphoto table relations can be viewed on the PDF database reference that is included in the release package within the /docs_files folder of your Zenphoto installation. For more detailed info about the database use tools like myPhpAdmin."); ?>
</p>
<?php
$database_name =db_name();
$prefix = prefix();
$resource = db_show('tables');
if ($resource) {
	$result = array();
	while ($row = db_fetch_assoc($resource)) {
		$result[] = $row;
	}
} else {
	$result = false;
}
$tables = array();
if (is_array($result)) {
	foreach ($result as $row) {
		$tables[] = array_shift($row);
	}
}
//echo "<pre>"; print_r($tables); echo "</pre>";
?>
<hr />
<ul>
<li>
<?php
$dbsoftware = db_software();
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
<ul>
<?php
$result = db_show('variables','character_set%');
if (is_array($result)) {
	foreach ($result as $row) {
	?>
	<li><?php echo $row['Variable_name']; ?>: <strong><?php echo $row['Value']; ?></strong></li>
	<?php
	}
}
//echo "<pre>"; print_r($result); echo "</pre>";
?>
</ul>
<ul>
<?php
$result = db_show('variables','collation%');
if (is_array($result)) {
	foreach ($result as $row) {
	?>
	<li><?php echo $row['Variable_name']; ?>: <strong><?php echo $row['Value']; ?></strong></li>
	<?php
	}
}
//echo "<pre>"; print_r($result); echo "</pre>";
?>
</ul>
<hr />
<script type="text/javascript">
function toggleRow(id) {
	if ($('#'+id).is(":visible")) {
		$('#'+id).hide();
	} else {
		$('#'+id).show();
	}
}
</script>
<?php
$i = 0;
foreach($tables as $table) {
	$i++;
	?>
	<h3><a href="javascript:toggleRow('t_<?php echo $i; ?>')"><?php echo str_replace($prefix,'',$table); ?></a></h3>
	<table id = "t_<?php echo $i; ?>" class="bordered" <?php if ($i>1) { ?>style="display: none;" <?php } ?>>
		<tr>
			<?php
			$cols = $tablecols = db_list_fields(str_replace($prefix, '', $table), true);
			$cols = array_shift($cols);
			foreach ($cols as $col=>$value) {
				 ?>
				 <th><?php echo $col; ?></th>
				 <?php
			}
			?>
		</tr>
		<?php
		//echo "<pre>"; print_r($tablecols); echo "</pre>";
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
}
?>

</div><!-- content -->
</div><!-- main -->
<?php printAdminFooter(); ?>
</body>
</html>