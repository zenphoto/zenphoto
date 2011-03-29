<?php
/**
 * Plugin handler for "text" files
 * Text type files can be displayed in place of an image in themes
 *
 * supports files of the following types:
 * 	.txt
 * 	.htm
 * 	.html
 * 		The contents of these files are "dumpped" into a SPAN sized to a 24x36 ratioed box based on your
 * 		theme	"image size" option. This has a class of "textobject" so it can be styled.
 *
 * What this plugin really is for is to serve as a model of how a plugin can be made to handle file types
 * that zenphoto does not handle natively.
 *
 * Some key points to note:
 * 1. The naming convention for these plugins is class-«handler class».php.
 * 2. The statement setting the plugin_is_filter variable must be near the front of the file. This is important
 * as it is the indicator to the Zenphoto plugin loader to load the script at the same point that other
 * object modules are loaded.
 * 3. These objects are extension to the zenphoto "Image" class. This means they have all the properties of
 * an image plus whatever you add. Of course you will need to override some of the image class functions to
 * implement the functionality of your new class.
 * 4. There is one VERY IMPORTANT method that you must provide which is not part of the "Image" base class. That
 * getBody() method. This method is called by template-functions.php in place of where it would normally put a URL
 * to the image to show. This method must do everything needed to cause your image object to be viewable by the
 * browser.
 *
 * So, briefly, the first four lines of code below are the standard plugin interface to Admin. There is one small
 * wrinkle you might notice--the code for 'plugin_description' includes a test which sets the variable $disable.
 * As you might expect, there were some changes needed to zenphoto in order to get this concept to work.  $disable
 * is set to true if the revision of zenphoto that is attempting to load this plugin is lower than the one where the
 * implementation first appeared. The interface variable 'plugin_disable' is set to this value telling Admin not to
 * allow enabling of the plugin if the release level is too low.
 *
 * The line that follows insures that the plugin will not load when it should be disabled--just in case.
 *
 * Then there is a call on addPlginType(«file extension», «Object Name»); This function registers the plugin as the
 * handler for files with the specified extension. If the plugin can handle more than one file extension, make a call
 * to the registration function for each extension that it handles.
 *
 * The rest is the object class for handling these files.
 *
 * The code of the object instantiation function is mostly required. Plugin "images" follow the lead of videos in that
 * if there is a real image file with the same name save the suffix, it will be considered the thumb image of the object.
 * This image is fetched by the call on checkObjectsThumb(). There is also code in the getThumb() method to deal with
 * this property.
 *
 * Since text files have no natural height and width, we set them based on the image size option. This happens after the call
 * PersistentObject(). The rest of the code there sets up the default title.
 *
 * getThumb() is responsible for generating the thumbnail image for the object. As above, if there is a similar named real
 * image, it will be used. Otherwise [for this object implementation] we will use a thumbnail image provided with the plugin.
 * The particular form of the file name used when there is no thumb stand-in image allows zenphoto to choose an image in the
 * plugin folder.
 *
 * @author Stephen Billard (sbillard)
 * @package plugins
 *
 */

$plugin_is_filter = 9|CLASS_PLUGIN;
$plugin_description = gettext('Provides a means for showing text type documents (.txt, .html, .htm) where zenphoto would normally display images. For documentation, see the script file.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';

addPluginType('htm', 'TextObject');
addPluginType('html', 'TextObject');
addPluginType('txt', 'TextObject');
$option_interface = 'textObject_Options';

/**
 * Option class for textobjects objects
 *
 */
class TextObject_Options {

	/**
	 * Standard option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		return array(gettext('Watermark default images') => array ('key' => 'textobject_watermark_default_images', 'type' => OPTION_TYPE_CHECKBOX,
																	'desc' => gettext('Check to place watermark image on default thumbnail images.')));
	}

}

require_once(dirname(__FILE__).'/class-textobject/class-textobject_core.php');
?>