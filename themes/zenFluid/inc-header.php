<?php
// force UTF-8 Ø
zp_apply_filter('theme_body_open');
$stageWidth = getOption('zenfluid_stagewidth');
$stagePosition = getOption('zenfluid_stageposition');
switch ($stagePosition) {
	case 'left' :
		$stageStyle = ($stageWidth > 0) ? 'style="max-width:' . $stageWidth . 'px;"' : '';
		$imageStyle = 'style="text-align: left;"';
		break;
	case 'center' :
		$stageStyle = ($stageWidth > 0) ? 'style="max-width:' . $stageWidth . 'px; margin-left: auto; margin-right: auto;"' : 'style="margin-left: auto; margin-right: auto;"';
		$imageStyle = 'style="text-align: center;"';
		break;
	case 'right' :
		$stageStyle = ($stageWidth > 0) ? 'style="max-width:' . $stageWidth . 'px; margin-left: auto;"' : 'style="margin-left: auto;"';
		$imageStyle = 'style="text-align: right;"';
		break;
}
$commentWidth = getOption('zenfluid_commentwidth');
$commentPosition = getOption('zenfluid_commentposition');
switch ($commentPosition) {
	case 'left' :
		$commentStyle = ($commentWidth > 0) ? 'style="max-width:' . $commentWidth . 'px;"' : '';
		break;
	case 'center' :
		$commentStyle = ($commentWidth > 0) ? 'style="max-width:' . $commentWidth . 'px; margin-left: auto; margin-right: auto;"' : 'style="margin-left: auto; margin-right: auto;"';
		break;
	case 'right' :
		$commentStyle = ($commentWidth > 0) ? 'style="max-width:' . $commentWidth . 'px; margin-left: auto;"' : 'style="margin-left: auto;"';
		break;
}
$descriptionWidth = getOption('zenfluid_descriptionwidth');
$descriptionPosition = getOption('zenfluid_descriptionposition');
switch ($descriptionPosition) {
	case 'left' :
		$descriptionStyle = ($descriptionWidth > 0) ? 'style="max-width:' . $descriptionWidth . 'px;"' : '';
		break;
	case 'center' :
		$descriptionStyle = ($descriptionWidth > 0) ? 'style="max-width:' . $descriptionWidth . 'px; margin-left: auto; margin-right: auto;"' : 'style="margin-left: auto; margin-right: auto;"';
		break;
	case 'right' :
		$descriptionStyle = ($descriptionWidth > 0) ? 'style="max-width:' . $descriptionWidth . 'px; margin-left: auto;"' : 'style="margin-left: auto;"';
		break;
}
$titleStyle = 'style="text-align: '.getOption('zenfluid_titleposition').';"';
$buttonStyle = 'style="text-align: '.getOption('zenfluid_buttonposition').';"';
$menuStyle = 'style="text-align: '.getOption('zenfluid_menuposition').';"';
$justifyStyle = 'style="text-align: '.getOption('zenfluid_descriptionjustification').';"';

$homeLink = getOption('zenfluid_homelink') ? 'Home<br>&nbsp' : '';

?>
	<div id="container">
		<div id="contents">
			<?php 
			if (getOption('zenfluid_showheader')) {
				?>
				<div class="header border colour">
					<div class="headertitle" <?php echo $titleStyle;?>>
						<a href="<?php echo getGalleryIndexURL(); ?>"><?php printGalleryTitle();?></a>
						<div class="headersubtitle">
							<?php printGalleryDesc();echo "\n";?>
						</div>
					</div>
				</div>
				<?php
			}
?>
