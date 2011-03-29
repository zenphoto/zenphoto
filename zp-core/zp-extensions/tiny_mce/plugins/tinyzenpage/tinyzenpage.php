<?php
define('OFFSET_PATH', 5);
$const_webpath = dirname(dirname(dirname(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))))));
$basepath = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
require_once($basepath."/admin-functions.php");
require_once($basepath .'/'. PLUGIN_FOLDER ."/zenpage/zenpage-template-functions.php");
require_once($basepath .'/'. PLUGIN_FOLDER ."/flowplayer3.php");
?>
<!-- tinyZenpage - A TinyMCE plugin for Zenphoto with Zenpage
		 Version: 1.0.6.1
		 Author: Malte Müller (acrylian), Stephen Billard (sbillard)
		 inspired by Alessandro "Simbul" Morandi's  ZenphotoPress (http://simbul.bzaar.net/zenphotopress)
		 License: GPL v2 http://www.gnu.org/licenses/gpl.html -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>tinyZenpage</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<script type="text/javascript" src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/htmlencoder.js"></script>
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="../../../../js/jquery.js"></script>
	<link rel="stylesheet" type="text/css" href="css/tinyzenpage.css" media="screen" />
	<link rel="stylesheet" href="../../../../zp-extensions/colorbox/colorbox.css" type="text/css" />
	<script src="../../../../zp-extensions/colorbox/jquery.colorbox-min.js" type="text/javascript"></script>
	<script language="javascript" type="text/javascript">
	$(document).ready(function(){
		$("a[rel='colorbox']").colorbox({iframe:true, innerWidth:450, innerHeight:450});
		$("a.colorbox").colorbox({iframe:true, innerWidth:450, innerHeight:450});
		
		$('#imagetitle,#albumtitle,#customtext').click(function() {
			$('#imagesize').hide();
			$('#titledesc').hide();
		});
		$('#image').click(function() {
			$('#imagesize').show();
			$('#titledesc').show();
		});
	});
	</script>
	<?php
require_once("js/dialog.php");
require_once("tinyzenpage-functions.php");
?>
	<?php zp_apply_filter('admin_head'); ?>
</head>

<body>
<div id="main" style="margin-top: -10px;">
<div class="optionsdiv">
<?php if(getOption('zp_plugin_zenpage')) { ?>
 <form name="zenpagelist" action="tinyzenpage.php?" method="get" style="margin: 8px 0px 8px 0px">
		<div class="panel current">
			<fieldset>
				<legend>Zenpage</legend>
    		<select name="zenpage" size="1" onchange="javascript:this.form.submit();">
    			<option><?php echo gettext("*Select*"); ?></option>
      		<?php printZenpageItems(); ?>
    		</select>
    		<br /><small><strong><?php echo gettext("(only direct links!)"); ?></strong></small>
  		</fieldset>
		</div>
	</form>
<?php } ?>
	<form name="albumlist" action="tinyzenpage.php?" method="get" style="margin: 8px 0px 8px 0px">
		<div class="panel current">
			<fieldset>
				<legend>Zenphoto</legend>
    		<select name="album" size="1" onchange="javascript:this.form.submit();">
    			<option><?php echo gettext("*Select an album*"); ?></option>
      		<?php printFullAlbumsList(); ?>
    		</select>
    		<br /><small><strong><?php echo gettext("(select variant below)"); ?></strong></small>
  		</fieldset>
		</div>
	</form>

<?php if(showZenphotoOptions()) { ?>
<form name="includetype" id="includetypye" action="" method="post" style="margin: 8px 0px 8px 0px">
		<div class="panel current">
			<fieldset>
				<legend><?php echo gettext("What to include"); ?></legend>
    		<input type="radio" name="type" id="image" value="1" checked='checked' /><label for="image"> <?php echo gettext("Image"); ?></label><br />
    		<?php if(checkAlbumForImages()) { ?><input type="radio" name="type" id="imagetitle" value="1" /><label for="imagetitle"> <?php echo gettext("Image title"); ?></label><br /><?php } ?>
    		<input type="radio" name="type" id="albumtitle" value="1" /><label for="albumtitle"> <?php echo gettext("Album title"); ?></label><br />
    		<input type="radio" name="type" id="customtext" value="1" />
    		<input type="text" name="text" id="text" value="" /><label for="customtext"><br /><span class="customtext"> <?php echo gettext("Custom text"); ?></span></label>
  		</fieldset>
		</div>
	</form>

	<form name="imagesize" id="imagesize" action="" method="post" style="margin: 8px 0px 8px 0px">
		<div class="panel current">
			<fieldset>
				<legend><?php echo gettext("Image size"); ?></legend>
    		<input type="radio" name="type" id="thumbnail" value="1" checked='checked'><label for="thumbnail" /> <?php echo gettext("Thumbnail"); ?></label><br />
    		<input type="radio" name="type" id="customthumb" value="1" />
    		s <input type="text" name="cropsize" id="cropsize" value="<?php echo getOption('tinymce_tinyzenpage_customthumb_size'); ?>" style="width:25px" /> / cw <input type="text" name="cropwidth" id="cropwidth" value="<?php echo getOption('tinymce_tinyzenpage_customthumb_cropwidth'); ?>" style="width:25px" /> x ch <input type="text" name="cropheight" id="cropheight" value="<?php echo getOption('tinymce_tinyzenpage_customthumb_cropwidth'); ?>" style="width:25px" /><label for="customthumb"><br /><span class="customtext"><?php echo gettext("Custom thumbnail"); ?></span></label><br />
    		<input type="radio" name="type" id="sizedimage" "value="1" /><label for="title"> <?php echo gettext("Sized image/multimedia item"); ?></label><br />
    		<input type="radio" name="type" id="customsize" value="1" />
    		<input type="text" name="size" id="size" value="<?php echo getOption('tinymce_tinyzenpage_customimagesize'); ?>" /><label for="customsize"><br /><span class="customtext"><?php echo gettext("Custom size (un-cropped)"); ?></span></label><br />
  		</fieldset>
		</div>
	</form>
	
	<form name="titledesc" id="titledesc" action="" method="post" style="margin: 8px 0px 8px 0px">
	<div class="panel current">
		<fieldset>
			<legend><?php echo gettext("Title and description"); ?></legend>
  		<input type="checkbox" name="type" id="showtitle" value="1" /><label for="showtitle"> <?php echo gettext("Show title"); ?></label><br />
  		<input type="radio" name="type" id="nodesc" value="1" /><label for="nodesc"> <?php echo gettext("No description"); ?></label><br />
  		<input type="radio" name="type" id="imagedesc" value="1" /><label for="imagedesc"> <?php echo gettext("Show image description"); ?></label><br />
  		<input type="radio" name="type" id="albumdesc" value="1" /><label for="albumdesc"> <?php echo gettext("Show album description"); ?></label>
  	</fieldset>
  	</div>
	</form>

	<form name="linktype" action="" method="post" style="margin: 8px 0px 8px 0px">
		<div class="panel current">
			<fieldset>
				<legend><?php echo gettext("Link type"); ?></legend>
    		<?php if(checkAlbumForImages()) { ?>
    		<input type="radio" name="link" id="imagelink" value="1" checked='checked' /><label for="imagelink"> <?php echo gettext("Link to image"); ?></label><br />
    		<input type="radio" name="link" id="fullimagelink" value="1"><label for="fullimagelink" /> <?php echo gettext("Link to full image"); ?></label><br /><?php } ?>
    		<input type="radio" name="link" id="albumlink" value="1" /><label for="albumlink"> <?php echo gettext("Link to album"); ?></label><br />
    		<input type="radio" name="link" id="nolink" value="1" /><label for="nolink"> <?php echo gettext("No link"); ?></label><br />
    		<input type="radio" name="link" id="customlink" value="1" />
    		<input type="text" name="linkurl" id="linkurl" value="" /><label for="customlink"><br /><span class="customtext"><?php echo gettext("Custom link"); ?></span></label>
  		</fieldset>
		</div>
	</form>

	<form name="textwrap" action="" method="post" style="margin: 8px 0px 8px 0px">
		<div class="panel current">
			<fieldset>
				<legend><?php echo gettext("Text wrap"); ?></legend>
  			<input type="radio" name="wrap" id="nowrap" value="1" checked='checked' /><label for="nowrap"><img src="img/wrapNone.gif" class="wrapicon" alt="" /> <?php echo gettext("No wrap"); ?></label><br />
    		<input type="radio" name="wrap" id="rightwrap" value="1" /><label for="rightwrap"><img src="img/wrapRight.gif" class="wrapicon" alt="" /> <?php echo gettext("Right"); ?></label><br />
    		<input type="radio" name="wrap" id="leftwrap" value="1" /><label for="leftwrap"><img src="img/wrapLeft.gif" class="wrapicon" alt="" /> <?php echo gettext("Left"); ?></label>
    	</fieldset>
		</div>
	</form>
<?php } ?>
</div><!-- panel wrapper end -->

<div class="albumdiv">
	 <div style="margin-top: 10px">
	 <?php
	 		if(empty($_GET['zenpage']) AND empty($_GET['album'])) {
	 			echo "<h2 style='margin-left: 8px'>";
	 			echo "<em>tiny</em>Zenpage (v1.4.1)</h2>";
	 			echo "<p style='margin-left: 8px'>";
	 			echo gettext("This provides access to your images and albums (dropdown 'Zenphoto') as well as pages, news articles and news categories (dropdown 'Zenpage') to easily include them in your pages and articles. You need at least 'Manage all albums' or 'Edit' rights to specific albums to be able to included image from them.")."</p>";
	 			echo "<h3 style='margin-left: 1px'>General usage</h3>";
	 			echo '<ol style="margin-left: 8px">';
	 			echo '<li>'.gettext("Select first the items type using the 'Zenphoto' or 'Zenpage' drop down").'</li>';
	 			echo '<li>'.gettext("Set the options as wished and explained below.").'</li>';
	 			echo '<li>'.gettext("Click on the image/page/article/category to include.").'</li>';
	 			echo '</ol>';
	 			echo "<p style='margin-left: 8px'>";
	 			echo gettext("NOTE: These options are be used on the fly and are not sticky. If you reload the page by selecting another album or moving between pages in an album you have to set them again.");
	 			echo "<p style='margin-left: 8px'>";
	 			echo gettext("The windows does not close automatically so you can include several images one after another.")."</p>";
	 			echo "<p style='margin-left: 8px'>";

	 			echo "<h3 style='margin-left: 1px'>Zenpage</h3>";
	 			echo "<p style='margin-left: 8px'>";
	 			echo gettext("Select to show a list of Zenpage pages, articles or categories. Click on a title to include a link. Hover of the link to see an excerpt of the page or article. Un-published pages or articles are marked with an '*' and protected with an '+'. There are no further options.")."</p>";

	 			echo "<h3 style='margin-left: 1px'>Zenphoto</h3>";
	 			echo "<p style='margin-left: 8px'>";
	 			echo gettext("Select an album to include images from into your page or article. Click on the image to included it. Un-published albums or images are marked with an '*'. You can also click on the magnify glass icon to see a preview of the item (Multimedia files are previewed in Flowplayer, no matter if that plugin is activated or not.). Note the first thumbnail is always the album thumbnail of the currently selected album itself. It is always titled <em>Albumthumb</em> and appears on every thumbnail page.<br />You have several options what to include and how:")."</p>";
				echo "<h4 style='margin-left: 8px'>".gettext("What to include")."</h4>";

				echo "<p style='margin-left: 8px'>";
				echo gettext("Include the image itself, only its title, the title of its album, or a custom text.")."</p>";

				echo "<h4 style='margin-left: 8px'>".gettext("Image size")."</h4>";
				echo '<ul style="margin-left: 8px">';
				echo '<li>'.gettext("Thumbnail: Size as set in Zenphoto's options").'</li>';
				echo '<li>'.gettext("Custom thumbnail: size for the longest side / cropwidth x cropheight). You can set default sizes for this on the TinyMCE plugin options. Not available for video/audio items.").'</li>';
				echo '<li>'.gettext("Sized image as set in Zenphoto's options.").'<br />';
				echo gettext("<strong>Video/audio: </strong>If the FLowplayer3 plugin is enabled you can also embed video/audio files (.flv, .mp4, .mp3). These items are highlighted with an orange border to be easily spotted. <br />Default values for the player width and height can be set on the TinyMCE plugin options (except for mp3s only the controlbar is shown). All other settings are inherited from the Flowplayer3 plugin options (cover/splash images are not supported).<br />NOTE: After embedding no frame of the embedded item might be visible in the editor until saving the page/article for unknown reasons.");
				echo '</li>';
				echo '<li>'.gettext("Custom size image: Size is for the longest side of the image. Not available for video/audio items.").'</li>';
				echo '</ul>';
				echo "<p style='margin-left: 8px'>";
				echo gettext("If you additionally check <em>Show title</em> or <em>Show description</em> the title/description of the image or album (if you checked <em>link to album</em>) will be printed below the image. Only if <em>Image</em> is chosen as type.")."</p>";
				echo "<h4 style='margin-left: 8px'>".gettext("Link type")."</h4>";
				echo "<p style='margin-left: 8px'>";
				echo gettext("Select to link to the image page of the image, to the album the image is in, no link at all or a custom URL.")."</p>";

				echo "<h4 style='margin-left: 8px'>".gettext("Text wrap")."</h4>";
				echo "<p style='margin-left: 8px'>";
				echo gettext("Wrap the text right or left of the image or not at all. Note that tinyZenpage adds a bit of CSS to the included image if you use text wrap. So that you see immediate results to the text wrap it attaches some inline CSS:");
				echo "<ul style='margin-left: 8px'>";
				echo "<li>".gettext("left wrap: <em>style=\"float:right\"</em>")."</li>";
				echo "<li>".gettext("right wrap: <em>style=\"float: left\"</em>")."</li>";
				echo "</ul>";

				echo "<p style='margin-left: 8px'>";
				echo gettext("Also tinyZenpage attaches a default CSS class to the image (if image is chosen) to be styled with your theme's css. If you choose left or right align '_left' or '_right' is appended:");
				echo "</p>";
				echo "<ul style='margin-left: 8px'>";
				echo "<li>".gettext("Default thumbnail: <em>zenpage_thumb</em> /<em>zenpage_thumb_left</em> /<em>zenpage_thumb_right</em>")."</li>";
				echo "<li>".gettext("Custom thumbnail: <em>zenpage_customthumb</em> or <em>zenpage_customthumb_left</em>/<em>zenpage_customthumb_right</em>")."</li>";
				echo "<li>".gettext("Sized image: <em>zenpage_sizedimage</em>/<em>zenpage_sizedimage_left</em>/<em>zenpage_sizedimage_right</em>")."</li>";
				echo "<li>".gettext("Custom image: <em>zenpage_customimage</em>/<em>zenpage_customimage_left</em>/<em>zenpage_customimage_right</em>")."</li>";
				echo "<li>".gettext("If you additionally have checked <em>Show title</em> or <em>Show description</em> for an image or album the div with the class <em>zenpage_wrapper</em> is wrapped around the image and link. Within that after the image and the link a div with the class <em>zenpage_title</em> wrapping the title respectively <em>zenpage_desc</em> wrapping the description.")."</li>";
				echo "</ul>";
				echo "<p style='margin-left: 8px'>";
				echo gettext("Additionally a default CSS class is attached to the link itself depending on the link option set:");
				echo "</p>";
				echo "<ul style='margin-left: 8px'>";
				echo "<li>".gettext("Image link: <em>zenpage_imagelink</em>")."</li>";
				echo "<li>".gettext("Full image link: <em>zenpage_fullimagelink</em>")."</li>";
				echo "<li>".gettext("Album link: <em>zenpage_albumlink</em>")."</li>";
				echo "<li>".gettext("Custom link: <em>zenpage_customlink</em>")."</li>";
				echo "</ul>";
				echo "<p style='margin-left: 8px'>";
				echo gettext("If you like to do some direct styling you can also use TinyMCE's image button or source code editor. <br />Also you can customize the CSS output yourself by directly changing the textwrap variables in line 47 and 50 in <em>tinyzenpage/js/dialog.php</em>.");
				echo "</p>";
	 		}
	  	printAllNestedList();
	 		printNewsArticlesList(12);
			printImageslist(19);
			
		 ?>
	</div>
</div>
<br style="clear: both" />
</div><!-- main div -->
<div id="help"><a href="tinyzenpage.php" target="_self"><small><?php echo gettext("Help"); ?><small></a></div>
</body>
</html>