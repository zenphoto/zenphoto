<?php

// force UTF-8 Ø

define('OFFSET_PATH', 3);
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/admin-globals.php");
admin_securityChecks(ZENPAGE_PAGES_RIGHTS | ZENPAGE_NEWS_RIGHTS, '');
require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER."/zenpage/zenpage-template-functions.php");
header('Last-Modified: ' . ZP_LAST_MODIFIED);
header('Content-Type: text/html; charset=' . LOCAL_CHARSET);
?>
<!-- tinyZenpage - A TinyMCE plugin for Zenphoto with Zenpage
		 Version: 1.0.6.1
		 Author: Malte Müller (acrylian), Stephen Billard (sbillard)
		 inspired by Alessandro "Simbul" Morandi's  ZenphotoPress (http://simbul.bzaar.net/zenphotopress)
		 License: GPL v2 http://www.gnu.org/licenses/gpl.html -->
<!DOCTYPE html>
<html>
<head>
	<title>tinyZenpage</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<script type="text/javascript" src="<?php echo WEBPATH.'/'. ZENFOLDER; ?>/js/htmlencoder.js"></script>
	<script type="text/javascript" src="<?php echo WEBPATH.'/'. ZENFOLDER; ?>/js/jquery.js"></script>
	<script type="text/javascript" src="<?php echo WEBPATH.'/'. ZENFOLDER.'/'.PLUGIN_FOLDER; ?>/tiny_mce/tiny_mce_popup.js"></script>
	<link rel="stylesheet" type="text/css" href="css/tinyzenpage.css" media="screen" />
	<script language="javascript" type="text/javascript">
	$(document).ready(function(){
		$("a[rel='colorbox']").colorbox({
			iframe:true,
			innerWidth:'90%',
			innerHeight:'85%',
			close: '<?php echo gettext("close"); ?>'
			});
		$("a.colorbox").colorbox({
			iframe:true,
			innerWidth:'90%',
			innerHeight:'85%',
			close: '<?php echo gettext("close"); ?>'
			});

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
<div id="help"><a href="tinyzenpage.php" target="_self"><small><?php echo gettext("Help"); ?><small></a></div>
<div id="main" style="margin-top: -10px;">
<div class="optionsdiv">
<?php
if(extensionEnabled('zenpage')) {
	?>
 <form name="zenpagelist" action="tinyzenpage.php?" method="get" class="optionform">
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
	<?php
	}
	?>
	<form name="albumlist" action="tinyzenpage.php?" method="get" class="optionform">
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

<?php
if(showZenphotoOptions()) {
	?>
	<form name="includetype" id="includetypye" action="" method="post" class="optionform">
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

	<form name="imagesize" id="imagesize" action="" method="post" class="optionform">
		<div class="panel current">
			<fieldset>
				<legend><?php echo gettext("Image size"); ?></legend>
				<input type="radio" name="type" id="thumbnail" value="1" checked='checked'><label for="thumbnail" /> <?php echo gettext("Thumbnail"); ?></label><br />
				<input type="radio" name="type" id="customthumb" value="1" />
				s <input type="text" name="cropsize" id="cropsize" value="<?php echo getOption('tinymce_tinyzenpage_customthumb_size'); ?>" style="width:25px" /> / cw <input type="text" name="cropwidth" id="cropwidth" value="<?php echo getOption('tinymce_tinyzenpage_customthumb_cropwidth'); ?>" style="width:25px" /> x ch <input type="text" name="cropheight" id="cropheight" value="<?php echo getOption('tinymce_tinyzenpage_customthumb_cropwidth'); ?>" style="width:25px" /><label for="customthumb"><br /><span class="customtext"><?php echo gettext("Custom thumbnail"); ?></span></label><br />
				<input type="radio" name="type" id="sizedimage" "value="1" /><label for="title"> <?php echo gettext("Sized image/multimedia item"); ?></label><br />
				<input type="radio" name="type" id="customsize" value="1" />
				<input type="text" name="size" id="size" value="<?php echo getOption('tinymce_tinyzenpage_customimagesize'); ?>" /><label for="customsize"><br /><span class="customtext"><?php echo gettext("Custom size (un-cropped)"); ?></span></label><br />
				<input type="radio" name="type" id="fullimage" value="1"><label for="fullimage" /> <?php echo gettext("Full image"); ?></label><br />
			</fieldset>
		</div>
	</form>

	<form name="titledesc" id="titledesc" action="" method="post" class="optionform">
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

	<form name="linktype" action="" method="post" class="optionform">
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

	<form name="textwrap" action="" method="post" class="optionform">
		<div class="panel current">
			<fieldset>
				<legend><?php echo gettext("Text wrap"); ?></legend>
				<input type="radio" name="wrap" id="nowrap" value="1" checked='checked' /><label for="nowrap"><img src="img/wrapNone.gif" class="wrapicon" alt="" /> <?php echo gettext("No wrap"); ?></label><br />
				<input type="radio" name="wrap" id="rightwrap" value="1" /><label for="rightwrap"><img src="img/wrapRight.gif" class="wrapicon" alt="" /> <?php echo gettext("Right"); ?></label><br />
				<input type="radio" name="wrap" id="leftwrap" value="1" /><label for="leftwrap"><img src="img/wrapLeft.gif" class="wrapicon" alt="" /> <?php echo gettext("Left"); ?></label>
			</fieldset>
		</div>
	</form>
	<?php
}
?>
</div><!-- panel wrapper end -->

<div class="albumdiv">
	 <div>
	 <?php
			if(empty($_GET['zenpage']) AND empty($_GET['album'])) {
				?>
				<h2>
				<em>tiny</em>Zenpage</h2>
				<p>
				<?php echo gettext("This provides access to your images and albums (select from the <em>Zenphoto</em> dropdown) as well as pages, news articles and news categories (select from the <em>Zenpage</em> dropdown) to easily include them in your pages and articles."); ?>
				</p>
				<p>

				<h3>Zenpage</h3>
				<p>
				<?php echo gettext("Select <em>pages</em>, <em>articles</em> or <em>categories</em> to show a list of these items. Click on a title to include a link. Hover of the link to see an excerpt of the page or article. Un-published pages or articles are marked with an '*' and protected with an '+'. There are no further options."); ?>
				</p>

				<h3>Zenphoto</h3>
				<p>
					<?php echo gettext("Select an album to include images from into your page or article. Click on the image to include it.".
															" Un-published albums or images are marked with an '*'.".
															" You can also click on the magnify glass icon to see a preview of the item. The preview opens in a colorbox showing the frontend/theme view.".
															" The first thumbnail is always the thumbnail of the selected album.".
															" It is titled <em>Albumthumb</em> and appears on every thumbnail page."); ?>
				</p>
				<h4><?php echo gettext("Options on what to include"); ?></h4>

				<p>
					<?php echo gettext("Include the image itself, only its title, the title of its album, or some custom text."); ?>
				</p>
				<p>
					<?php echo gettext("NOTE: These selections are not sticky. If you change albums or change pages in an album you will have to select them again."); ?>
					<?php echo gettext("The window does not close automatically so you can include several images one after another."); ?>
				</p>

				<h4><?php echo gettext("Image size"); ?></h4>
				<ul>
					<li><?php echo gettext("Thumbnail: Size as set in Zenphoto's options"); ?></li>
					<li><?php echo gettext("Custom thumbnail: size for the longest side / (cropwidth x cropheight). You can set default sizes for this on the TinyMCE plugin options."); ?></li>
					<li>
						<?php echo gettext("Sized image/multimedia item: The sized image as set in gallery default theme's options."); ?>
						<br />
						<?php echo gettext("<strong>Multimedia item</strong>: This embeds the content macro MEDIAPLAYER into the text. This generates a video or audio player on the front end if any suitable multimedia player plugin for the file type is enabled and also has registered to the macro."); ?>
					</li>
					<li><?php echo gettext("Custom size (un-cropped)."); ?></li>
					<li><?php echo gettext("Full image: The original image directly. NOTE: Full image protection options do not apply!."); ?></li>
				</ul>
				<p>
				<?php echo gettext("If you additionally check <em>Show title</em> or <em>Show description</em> the title/description of the image or album (if you checked <em>link to album</em>) will be printed below the image. Only if <em>Image</em> is chosen as type."); ?>
				</p>
				<h4><?php echo gettext("Link type"); ?></h4>
				<p>
				<?php echo gettext("Select to link to the image page of the image, to the album the image is in, no link at all or a custom URL."); ?>
				</p>

				<h4><?php echo gettext("Text wrap"); ?></h4>
				<p>
				<?php echo gettext("Wrap the text right or left of the image or not at all. Note that tinyZenpage adds a bit of CSS to the included image if you use text wrap. So that you see immediate results to the text wrap it attaches some inline CSS:"); ?>
				<ul>
					<li><?php echo gettext("left wrap: <em>style=\"float:right\"</em>"); ?></li>
					<li><?php echo gettext("right wrap: <em>style=\"float: left\"</em>"); ?></li>
				</ul>

				<p>
				<?php echo gettext("Also tinyZenpage attaches a default CSS class to the image (if image is chosen) to be styled with your theme's css. If you choose left or right align '_left' or '_right' is appended:"); ?>
				</p>
				<ul>
					<li><?php echo gettext("Default thumbnail: <em>zenpage_thumb</em> /<em>zenpage_thumb_left</em> /<em>zenpage_thumb_right</em>"); ?></li>
					<li><?php echo gettext("Custom thumbnail: <em>zenpage_customthumb</em> or <em>zenpage_customthumb_left</em>/<em>zenpage_customthumb_right</em>"); ?></li>
					<li><?php echo gettext("Sized image: <em>zenpage_sizedimage</em>/<em>zenpage_sizedimage_left</em>/<em>zenpage_sizedimage_right</em>"); ?></li>
					<li><?php echo gettext("Custom image: <em>zenpage_customimage</em>/<em>zenpage_customimage_left</em>/<em>zenpage_customimage_right</em>"); ?></li>
					<li><?php echo gettext("If you additionally have checked <em>Show title</em> or <em>Show description</em> for an image or album the div with the class <em>zenpage_wrapper</em> is wrapped around the image and link. Within that after the image and the link a div with the class <em>zenpage_title</em> wrapping the title respectively <em>zenpage_desc</em> wrapping the description."); ?></li>
				</ul>
				<p>
				<?php echo gettext("Additionally a default CSS class is attached to the link itself depending on the link option set:"); ?>
				</p>
				<ul>
					<li><?php echo gettext("Image link: <em>zenpage_imagelink</em>"); ?></li>
					<li><?php echo gettext("Full image link: <em>zenpage_fullimagelink</em>. Additionally <em>rel='colorbox'</em> is attached so you can move through all images on a page using Colorbox or similar *box scripts."); ?></li>
					<li><?php echo gettext("Album link: <em>zenpage_albumlink</em>"); ?></li>
					<li><?php echo gettext("Custom link: <em>zenpage_customlink</em>"); ?></li>
				</ul>
				<p>
				<?php echo gettext("If you would like to do some direct styling you can also use TinyMCE's image button or source code editor."); ?>
				</p>
				<?php
			}
			printAllNestedList();
			printNewsArticlesList(12);
			printImageslist(19);

		 ?>
	</div>
</div>
<br style="clear: both" />
</div><!-- main div -->

</body>
</html>