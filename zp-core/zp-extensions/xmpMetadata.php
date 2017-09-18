<?php

/**
 *
 * Enable this filter to scan images (or <i>xmp sidecar</i> files) for metadata.
 *
 * Relevant metadata found will be incorporated into the image (or album object).
 * See <i>{@link http://www.adobe.com/devnet/xmp.html  Adobe XMP Specification}</i>
 * for xmp metadata description. This plugin attempts to map the <i>xmp metadata</i> to database or IPTC fields.
 *
 * If a sidecar file exists, it will take precedence (the image file will not be
 * examined.) The sidecar file should reside in the same folder, have the same <i>prefix</i> name as the
 * image (album), and the suffix <var>.xmp</var>. Thus, the sidecar for <i>image</i>.jpg would be named
 * <i>image</i><var>.xmp</var>.
 *
 * NOTE: dynamic albums have an <var>.alb</var> suffix. Append <var>.xmp</var> to that name so
 * that the dynamic album sidecar would be named <i>album</i><var>.alb.xmp</var>.
 *
 * There are two options for this plugin
 * 	<ul>
 * 		<li>The suffix of the metadata sidecar file</li>
 * 		<li>A list of image file suffixes that may contain metadata</li>
 * 	</ul>
 * Check each image type you wish the plugin to search within for
 * an <i>xmp block</i>. These are disabled by default because scanning image files can add considerably to the
 * processing time.
 *
 * The plugin does not present any theme interface.
 *
 * @author Stephen Billard (sbillard)
 *
 * @package plugins
 * @subpackage media
 */
$plugin_is_filter = 9 | CLASS_PLUGIN;
$plugin_description = gettext('Extracts <em>XMP</em> metadata from images and <code>XMP</code> sidecar files.');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'xmpMetadata';

zp_register_filter('album_instantiate', 'xmpMetadata::album_instantiate');
zp_register_filter('new_album', 'xmpMetadata::new_album');
zp_register_filter('album_refresh', 'xmpMetadata::new_album');
zp_register_filter('image_instantiate', 'xmpMetadata::image_instantiate');
zp_register_filter('image_metadata', 'xmpMetadata::new_image');
zp_register_filter('upload_filetypes', 'xmpMetadata::sidecars');
zp_register_filter('save_album_utilities_data', 'xmpMetadata::putXMP');
zp_register_filter('edit_album_utilities', 'xmpMetadata::create');
zp_register_filter('save_image_utilities_data', 'xmpMetadata::putXMP');
zp_register_filter('edit_image_utilities', 'xmpMetadata::create');
zp_register_filter('bulk_image_actions', 'xmpMetadata::bulkActions');
zp_register_filter('bulk_album_actions', 'xmpMetadata::bulkActions');

require_once(dirname(dirname(__FILE__)) . '/exif/exif.php');

define('XMP_EXTENSION', strtolower(getOption('xmpMetadata_suffix')));

/**
 * Plugin option handling class
 *
 */
class xmpMetadata {

	private static $XML_trans = array(
			'&#128;' => '€',
			'&#130;' => '‚',
			'&#131;' => 'ƒ',
			'&#132;' => '„',
			'&#133;' => '…',
			'&#134;' => '†',
			'&#135;' => '‡',
			'&#136;' => 'ˆ',
			'&#137;' => '‰',
			'&#138;' => 'Š',
			'&#139;' => '‹',
			'&#140;' => 'Œ',
			'&#142;' => 'Ž',
			'&#145;' => '‘',
			'&#146;' => '’',
			'&#147;' => '“',
			'&#148;' => '”',
			'&#149;' => '•',
			'&#150;' => '–',
			'&#151;' => '—',
			'&#152;' => '˜',
			'&#153;' => '™',
			'&#154;' => 'š',
			'&#155;' => '›',
			'&#156;' => 'œ',
			'&#158;' => 'ž',
			'&#159;' => 'Ÿ',
			'&#161;' => '¡',
			'&#162;' => '¢',
			'&#163;' => '£',
			'&#164;' => '¤',
			'&#165;' => '¥',
			'&#166;' => '¦',
			'&#167;' => '§',
			'&#168;' => '¨',
			'&#169;' => '©',
			'&#170;' => 'ª',
			'&#171;' => '«',
			'&#172;' => '¬',
			'&#173;' => '­',
			'&#174;' => '®',
			'&#175;' => '¯',
			'&#176;' => '°',
			'&#177;' => '±',
			'&#178;' => '²',
			'&#179;' => '³',
			'&#180;' => '´',
			'&#181;' => 'µ',
			'&#182;' => '¶',
			'&#183;' => '·',
			'&#184;' => '¸',
			'&#185;' => '¹',
			'&#186;' => 'º',
			'&#187;' => '»',
			'&#188;' => '¼',
			'&#189;' => '½',
			'&#190;' => '¾',
			'&#191;' => '¿',
			'&#192;' => 'À',
			'&#193;' => 'Á',
			'&#194;' => 'Â',
			'&#195;' => 'Ã',
			'&#196;' => 'Ä',
			'&#197;' => 'Å',
			'&#198;' => 'Æ',
			'&#199;' => 'Ç',
			'&#200;' => 'È',
			'&#201;' => 'É',
			'&#202;' => 'Ê',
			'&#203;' => 'Ë',
			'&#204;' => 'Ì',
			'&#205;' => 'Í',
			'&#206;' => 'Î',
			'&#207;' => 'Ï',
			'&#208;' => 'Ð',
			'&#209;' => 'Ñ',
			'&#210;' => 'Ò',
			'&#211;' => 'Ó',
			'&#212;' => 'Ô',
			'&#213;' => 'Õ',
			'&#214;' => 'Ö',
			'&#215;' => '×',
			'&#216;' => 'Ø',
			'&#217;' => 'Ù',
			'&#218;' => 'Ú',
			'&#219;' => 'Û',
			'&#220;' => 'Ü',
			'&#221;' => 'Ý',
			'&#222;' => 'Þ',
			'&#223;' => 'ß',
			'&#224;' => 'à',
			'&#225;' => 'á',
			'&#226;' => 'â',
			'&#227;' => 'ã',
			'&#228;' => 'ä',
			'&#229;' => 'å',
			'&#230;' => 'æ',
			'&#231;' => 'ç',
			'&#232;' => 'è',
			'&#233;' => 'é',
			'&#234;' => 'ê',
			'&#235;' => 'ë',
			'&#236;' => 'ì',
			'&#237;' => 'í',
			'&#238;' => 'î',
			'&#239;' => 'ï',
			'&#240;' => 'ð',
			'&#241;' => 'ñ',
			'&#242;' => 'ò',
			'&#243;' => 'ó',
			'&#244;' => 'ô',
			'&#245;' => 'õ',
			'&#246;' => 'ö',
			'&#247;' => '÷',
			'&#248;' => 'ø',
			'&#249;' => 'ù',
			'&#250;' => 'ú',
			'&#251;' => 'û',
			'&#252;' => 'ü',
			'&#253;' => 'ý',
			'&#254;' => 'þ',
			'&#255;' => 'ÿ',
			'&#256;' => 'Ā',
			'&#257;' => 'ā',
			'&#258;' => 'Ă',
			'&#259;' => 'ă',
			'&#260;' => 'Ą',
			'&#261;' => 'ą',
			'&#262;' => 'Ć',
			'&#263;' => 'ć',
			'&#264;' => 'Ĉ',
			'&#265;' => 'ĉ',
			'&#266;' => 'Ċ',
			'&#267;' => 'ċ',
			'&#268;' => 'Č',
			'&#269;' => 'č',
			'&#270;' => 'Ď',
			'&#271;' => 'ď',
			'&#272;' => 'Đ',
			'&#273;' => 'đ',
			'&#274;' => 'Ē',
			'&#275;' => 'ē',
			'&#276;' => 'Ĕ',
			'&#277;' => 'ĕ',
			'&#278;' => 'Ė',
			'&#279;' => 'ė',
			'&#280;' => 'Ę',
			'&#281;' => 'ę',
			'&#282;' => 'Ě',
			'&#283;' => 'ě',
			'&#284;' => 'Ĝ',
			'&#285;' => 'ĝ',
			'&#286;' => 'Ğ',
			'&#287;' => 'ğ',
			'&#288;' => 'Ġ',
			'&#289;' => 'ġ',
			'&#290;' => 'Ģ',
			'&#291;' => 'ģ',
			'&#292;' => 'Ĥ',
			'&#293;' => 'ĥ',
			'&#294;' => 'Ħ',
			'&#295;' => 'ħ',
			'&#296;' => 'Ĩ',
			'&#297;' => 'ĩ',
			'&#298;' => 'Ī',
			'&#299;' => 'ī',
			'&#300;' => 'Ĭ',
			'&#301;' => 'ĭ',
			'&#302;' => 'Į',
			'&#303;' => 'į',
			'&#304;' => 'İ',
			'&#305;' => 'ı',
			'&#306;' => 'Ĳ',
			'&#307;' => 'ĳ',
			'&#308;' => 'Ĵ',
			'&#309;' => 'ĵ',
			'&#310;' => 'Ķ',
			'&#311;' => 'ķ',
			'&#312;' => 'ĸ',
			'&#313;' => 'Ĺ',
			'&#314;' => 'ĺ',
			'&#315;' => 'Ļ',
			'&#316;' => 'ļ',
			'&#317;' => 'Ľ',
			'&#318;' => 'ľ',
			'&#319;' => 'Ŀ',
			'&#320;' => 'ŀ',
			'&#321;' => 'Ł',
			'&#322;' => 'ł',
			'&#323;' => 'Ń',
			'&#324;' => 'ń',
			'&#325;' => 'Ņ',
			'&#326;' => 'ņ',
			'&#327;' => 'Ň',
			'&#328;' => 'ň',
			'&#329;' => 'ŉ',
			'&#330;' => 'Ŋ',
			'&#331;' => 'ŋ',
			'&#332;' => 'Ō',
			'&#333;' => 'ō',
			'&#334;' => 'Ŏ',
			'&#335;' => 'ŏ',
			'&#336;' => 'Ő',
			'&#337;' => 'ő',
			'&#338;' => 'Œ',
			'&#339;' => 'œ',
			'&#340;' => 'Ŕ',
			'&#341;' => 'ŕ',
			'&#342;' => 'Ŗ',
			'&#343;' => 'ŗ',
			'&#344;' => 'Ř',
			'&#345;' => 'ř',
			'&#346;' => 'Ś',
			'&#347;' => 'ś',
			'&#348;' => 'Ŝ',
			'&#349;' => 'ŝ',
			'&#34;' => '"',
			'&#350;' => 'Ş',
			'&#351;' => 'ş',
			'&#352;' => 'Š',
			'&#353;' => 'š',
			'&#354;' => 'Ţ',
			'&#355;' => 'ţ',
			'&#356;' => 'Ť',
			'&#357;' => 'ť',
			'&#358;' => 'Ŧ',
			'&#359;' => 'ŧ',
			'&#360;' => 'Ũ',
			'&#361;' => 'ũ',
			'&#362;' => 'Ū',
			'&#363;' => 'ū',
			'&#364;' => 'Ŭ',
			'&#365;' => 'ŭ',
			'&#366;' => 'Ů',
			'&#367;' => 'ů',
			'&#368;' => 'Ű',
			'&#369;' => 'ű',
			'&#370;' => 'Ų',
			'&#371;' => 'ų',
			'&#372;' => 'Ŵ',
			'&#373;' => 'ŵ',
			'&#374;' => 'Ŷ',
			'&#375;' => 'ŷ',
			'&#377;' => 'Ź',
			'&#378;' => 'ź',
			'&#379;' => 'Ż',
			'&#380;' => 'ż',
			'&#381;' => 'Ž',
			'&#382;' => 'ž',
			'&#383;' => 'ſ',
			'&#38;' => '&',
			'&#39;' => '\'',
			'&#402;' => 'ƒ',
			'&#439;' => 'Ʒ',
			'&#452;' => 'Ǆ',
			'&#453;' => 'ǅ',
			'&#454;' => 'ǆ',
			'&#455;' => 'Ǉ',
			'&#456;' => 'ǈ',
			'&#457;' => 'ǉ',
			'&#458;' => 'Ǌ',
			'&#459;' => 'ǋ',
			'&#460;' => 'ǌ',
			'&#478;' => 'Ǟ',
			'&#479;' => 'ǟ',
			'&#484;' => 'Ǥ',
			'&#485;' => 'ǥ',
			'&#486;' => 'Ǧ',
			'&#487;' => 'ǧ',
			'&#488;' => 'Ǩ',
			'&#489;' => 'ǩ',
			'&#494;' => 'Ǯ',
			'&#495;' => 'ǯ',
			'&#497;' => 'Ǳ',
			'&#499;' => 'ǳ',
			'&#500;' => 'Ǵ',
			'&#501;' => 'ǵ',
			'&#506;' => 'Ǻ',
			'&#507;' => 'ǻ',
			'&#508;' => 'Ǽ',
			'&#509;' => 'ǽ',
			'&#510;' => 'Ǿ',
			'&#511;' => 'ǿ',
			'&#60;' => '<',
			'&#62;' => '>',
			'&#636;' => 'ɼ',
			'&#64257;' => 'ﬁ',
			'&#64258;' => 'ﬂ',
			'&#658;' => 'ʒ',
			'&#728;' => '˘',
			'&#729;' => '˙',
			'&#730;' => '˚',
			'&#731;' => '˛',
			'&#732;' => '˜',
			'&#733;' => '˝',
			'&#7682;' => 'Ḃ',
			'&#7683;' => 'ḃ',
			'&#7690;' => 'Ḋ',
			'&#7691;' => 'ḋ',
			'&#7696;' => 'Ḑ',
			'&#7697;' => 'ḑ',
			'&#7710;' => 'Ḟ',
			'&#7711;' => 'ḟ',
			'&#7728;' => 'Ḱ',
			'&#7729;' => 'ḱ',
			'&#7744;' => 'Ṁ',
			'&#7745;' => 'ṁ',
			'&#7766;' => 'Ṗ',
			'&#7767;' => 'ṗ',
			'&#7776;' => 'Ṡ',
			'&#7777;' => 'ṡ',
			'&#7786;' => 'Ṫ',
			'&#7787;' => 'ṫ',
			'&#7808;' => 'Ẁ',
			'&#7809;' => 'ẁ',
			'&#7810;' => 'Ẃ',
			'&#7811;' => 'ẃ',
			'&#7812;' => 'Ẅ',
			'&#7813;' => 'ẅ',
			'&#7922;' => 'Ỳ',
			'&#7923;' => 'ỳ',
			'&#8213;' => '―',
			'&#8227;' => '‣',
			'&#8252;' => '‼',
			'&#8254;' => '‾',
			'&#8260;' => '⁄',
			'&#8319;' => 'ⁿ',
			'&#8355;' => '₣',
			'&#8356;' => '₤',
			'&#8359;' => '₧',
			'&#8453;' => '℅',
			'&#8470;' => '№',
			'&#8539;' => '⅛',
			'&#8540;' => '⅜',
			'&#8541;' => '⅝',
			'&#8542;' => '⅞',
			'&#8592;' => '←',
			'&#8593;' => '↑',
			'&#8594;' => '→',
			'&#8595;' => '↓',
			'&#8706;' => '∂',
			'&#8710;' => '∆',
			'&#8719;' => '∏',
			'&#8721;' => '∑',
			'&#8729;' => '∙',
			'&#8730;' => '√',
			'&#8734;' => '∞',
			'&#8735;' => '∟',
			'&#8745;' => '∩',
			'&#8747;' => '∫',
			'&#8776;' => '≈',
			'&#8800;' => '≠',
			'&#8801;' => '≡',
			'&#8804;' => '≤',
			'&#8805;' => '≥',
			'&#94;' => '^',
			'&#9792;' => '♀',
			'&#9794;' => '♂',
			'&#9824;' => '♠',
			'&#9827;' => '♣',
			'&#9829;' => '♥',
			'&#9830;' => '♦',
			'&#9833;' => '♩',
			'&#9834;' => '♪',
			'&#9836;' => '♬',
			'&#9837;' => '♭',
			'&#9839;' => '♯',
			'&498;' => 'ǲ',
			'&AElig;' => 'Æ',
			'&Aacute;' => 'Á',
			'&Acirc;' => 'Â',
			'&Agrave;' => 'À',
			'&Aring;' => 'Å',
			'&Atilde;' => 'Ã',
			'&Auml;' => 'Ä',
			'&Ccedil;' => 'Ç',
			'&Dagger;' => '‡',
			'&ETH;' => 'Ð',
			'&Eacute;' => 'É',
			'&Ecirc;' => 'Ê',
			'&Egrave;' => 'È',
			'&Euml;' => 'Ë',
			'&Iacute;' => 'Í',
			'&Icirc;' => 'Î',
			'&Igrave;' => 'Ì',
			'&Iuml;' => 'Ï',
			'&Ntilde;' => 'Ñ',
			'&OElig;' => 'Œ',
			'&Oacute;' => 'Ó',
			'&Ocirc;' => 'Ô',
			'&Ograve;' => 'Ò',
			'&Oslash;' => 'Ø',
			'&Otilde;' => 'Õ',
			'&Ouml;' => 'Ö',
			'&THORN;' => 'Þ',
			'&Uacute;' => 'Ú',
			'&Ucirc;' => 'Û',
			'&Ugrave;' => 'Ù',
			'&Uuml;' => 'Ü',
			'&Yacute;' => 'Ý',
			'&Yuml;' => 'Ÿ',
			'&aacute;' => 'á',
			'&acirc;' => 'â',
			'&acute;' => '´',
			'&aelig;' => 'æ',
			'&agrave;' => 'à',
			'&amp;' => '&',
			'&aring;' => 'å',
			'&atilde;' => 'ã',
			'&auml;' => 'ä',
			'&brvbar;' => '¦',
			'&ccedil;' => 'ç',
			'&cedil;' => '¸',
			'&cent;' => '¢',
			'&clubs;' => '♣',
			'&copy;' => '©',
			'&curren;' => '¤',
			'&dagger;' => '†',
			'&darr;' => '↓',
			'&dbquo;' => '„',
			'&deg;' => '°',
			'&diams;' => '♦',
			'&divide;' => '÷',
			'&eacute;' => 'é',
			'&ecirc;' => 'ê',
			'&egrave;' => 'è',
			'&eth;' => 'ð',
			'&euml;' => 'ë',
			'&euro;' => '€',
			'&frac12;' => '½',
			'&frac14;' => '¼',
			'&frac34;' => '¾',
			'&gt;' => '>',
			'&hearts;' => '♥',
			'&iacute;' => 'í',
			'&icirc;' => 'î',
			'&iexcl;' => '¡',
			'&igrave;' => 'ì',
			'&iquest;' => '¿',
			'&iuml;' => 'ï',
			'&laquo;' => '«',
			'&larr;' => '←',
			'&ldquo;' => '“',
			'&lsaquo;' => '‹',
			'&lsquo;' => '‘',
			'&lt;' => '<',
			'&macr;' => '¯',
			'&mdash;' => '—',
			'&micro;' => 'µ',
			'&middot;' => '·',
			'&ndash;' => '–',
			'&not;' => '¬',
			'&ntilde;' => 'ñ',
			'&oacute;' => 'ó',
			'&ocirc;' => 'ô',
			'&oelig;' => 'œ',
			'&ograve;' => 'ò',
			'&oline;' => '‾',
			'&ordf;' => 'ª',
			'&ordm;' => 'º',
			'&oslash;' => 'ø',
			'&otilde;' => 'õ',
			'&ouml;' => 'ö',
			'&para;' => '¶',
			'&permil;' => '‰',
			'&plusmn;' => '±',
			'&pound;' => '£',
			'&quot;' => '"',
			'&raquo;' => '»',
			'&rarr;' => '→',
			'&rdquo;' => '”',
			'&reg;' => '®',
			'&rsaquo;' => '›',
			'&rsquo;' => '’',
			'&sbquo;' => '‚',
			'&sect;' => '§',
			'&shy;' => '­',
			'&spades;' => '♠',
			'&sup1;' => '¹',
			'&sup2;' => '²',
			'&sup3;' => '³',
			'&szlig;' => 'ß',
			'&thorn;' => 'þ',
			'&tilde' => '˜',
			'&tilde;' => '˜',
			'&times;' => '×',
			'&trade;' => '™',
			'&uacute;' => 'ú',
			'&uarr;' => '↑',
			'&ucirc;' => 'û',
			'&ugrave;' => 'ù',
			'&uml;' => '¨',
			'&uuml;' => 'ü',
			'&yacute;' => 'ý',
			'&yen;' => '¥',
			'&yuml;' => 'ÿ'
	);

	/**
	 * Class instantiation function
	 *
	 * @return xmpMetadata_options
	 */
	function __construct() {
		if (OFFSET_PATH == 2) {
			setOptionDefault('xmpMetadata_suffix', 'xmp');
		}
	}

	/**
	 * Option interface
	 *
	 * @return array
	 */
	function getOptionsSupported() {
		global $_zp_supported_images, $_zp_images_classes;
		$list = array_diff($_zp_supported_images, array('gif', 'wbmp', 'wbm', 'bmp'));
		foreach ($_zp_images_classes as $suffix => $type) {
			if ($type == 'Video')
				$list[] = $suffix;
		}
		$listi = array();
		natcasesort($list);
		foreach ($list as $suffix) {
			$listi[$suffix] = 'xmpMetadata_examine_images_' . $suffix;
		}
		return array(gettext('Sidecar file extension') => array('key' => 'xmpMetadata_suffix', 'type' => OPTION_TYPE_TEXTBOX,
						'desc' => gettext('The plugin will look for files with <em>image_name.extension</em> and extract XMP metadata from them into the <em>image_name</em> record.')),
				gettext('Process extensions') => array('key' => 'xmpMetadata_examine_imagefile', 'type' => OPTION_TYPE_CHECKBOX_UL,
						'checkboxes' => $listi,
						'desc' => gettext('If no sidecar file exists and the extension is enabled, the plugin will search within that type <em>image</em> file for an <code>XMP</code> block. <strong>Warning</strong> do not set this option unless you require it. Searching image files can be computationally intensive.'))
		);
	}

	/**
	 * Custom option handler
	 *
	 * @param string $option
	 * @param mixed $currentValue
	 */
	function handleOption($option, $currentValue) {

	}

	/**
	 * returns the database fields used by the object
	 * @return array
	 *
	 * @author Stephen Billard
	 * @Copyright 2015 by Stephen L Billard for use in {@link https://github.com/ZenPhoto20/ZenPhoto20 ZenPhoto20}
	 */
	static function getMetadataFields() {
		return array(
				// Database Field      => array(0:'source', 1:'Metadata Key', 2;'ZP Display Text', 3:Display?	4:size,	5:enabled, 6:type, 7:linked)
				'XMPAperatureValue' => array('XMP', '<exif:ApertureValue>', gettext('Aperature Value'), false, 52, true, 'string', false),
				'XMPArtist' => array('XMP', '<dc:creator>', gettext('Artist'), false, 52, true, 'string', false),
				'XMPContrast' => array('XMP', '<exif:Contrast>', gettext('Contrast Setting'), false, 52, true, 'string', false),
				'XMPDateTimeOriginal' => array('XMP', '<exif:DateTimeOriginal>', gettext('Original Time Taken'), true, 52, true, 'time', false),
				'XMPExposureBiasValue' => array('XMP', '<exif:ExposureBiasValue>', gettext('Exposure Compensation'), true, 52, true, 'string', false),
				'XMPExposureProgram' => array('XMP', '<exif:ExposureProgram>', gettext('Exposure program'), true, 52, true, 'string', false),
				'XMPExposureTime' => array('XMP', '<exif:ExposureTime>', gettext('Exposure time'), true, 52, true, 'string', false),
				'XMPFNumber' => array('XMP', '<exif:FNumber>', gettext('Aperture'), true, 52, true, 'string', false),
				'XMPFocalLength' => array('XMP', '<exif:FocalLength>', gettext('Focal Length'), true, 52, true, 'string', false),
				'XMPGPSAltitude' => array('XMP', '<exif:GPSAltitude>', gettext('Altitude'), false, 52, true, 'number', false),
				'XMPGPSAltitudeRef' => array('XMP', '<exif:GPSAltitudeRef>', gettext('Altitude Reference'), false, 52, true, 'string', 'XMPGPSAltitude'),
				'XMPGPSLatitude' => array('XMP', '<exif:GPSLatitude>', gettext('Latitude'), false, 52, true, 'number', false),
				'XMPGPSLongitude' => array('XMP', '<exif:GPSLongitude>', gettext('Longitude'), false, 52, true, 'number', false),
				'XMPISOSpeedRatings' => array('XMP', '<exif:ISOSpeedRatings>', gettext('ISO Sensitivity'), true, 52, true, 'string', false),
				'XMPLensInfo' => array('XMP', '<aux:LensInfo>', gettext('Lens Info'), false, 52, true, 'string', false),
				'XMPLensType' => array('XMP', '<aux:Lens>', gettext('Lens Type'), false, 52, true, 'string', false),
				'XMPMake' => array('XMP', '<tiff:Make>', gettext('Camera Maker'), true, 52, true, 'string', false),
				'XMPMeteringMode' => array('XMP', '<exif:MeteringMode>', gettext('Metering Mode'), true, 52, true, 'string', false),
				'XMPModel' => array('XMP', '<tiff:Model>', gettext('Camera Model'), true, 52, true, 'string', false),
				'XMPOrientation' => array('XMP', '<tiff:Orientation>', gettext('Orientation'), false, 52, true, 'string', false),
				'XMPSaturation' => array('XMP', '<exif:Saturation>', gettext('Saturation Setting'), false, 52, true, 'string', false),
				'XMPSharpness' => array('XMP', '<exif:Sharpness>', gettext('Sharpness Setting'), false, 52, true, 'string', false),
				'XMPShutterSpeedValue' => array('XMP', '<exif:ShutterSpeedValue>', gettext('Shutter Speed'), true, 52, true, 'string', false),
				'XMPWhiteBalance' => array('XMP', '<exif:WhiteBalance>', gettext('White Balance'), false, 52, true, 'string', false),
				'XMPCity' => array('XMP', '<photoshop:City>', gettext('City'), false, 32, true, 'string', false),
				'XMPCopyright' => array('XMP', '<dc:rights>', gettext('Copyright Notice'), false, 128, true, 'string', false),
				'XMPImageCaption' => array('XMP', '<dc:description>', gettext('Image Caption'), false, 2000, true, 'string', false),
				'XMPImageCredit' => array('XMP', '<photoshop:Credit>', gettext('Image Credit'), false, 32, true, 'string', false),
				'XMPImageHeadline' => array('XMP', '<photoshop:Headline>', gettext('Image Headline'), false, 256, true, 'string', false),
				'XMPKeywords' => array('XMP', '<dc:subject>', gettext('Keywords'), false, 0, true, 'string', false),
				'XMPLocationCode' => array('XMP', '<Iptc4xmpCore:CountryCode>', gettext('Country/Primary Location Code'), false, 3, true, 'string', false),
				'XMPLocationName' => array('XMP', '<photoshop:Country>', gettext('Country/Primary Location Name'), false, 64, true, 'string', false),
				'XMPObjectName' => array('XMP', '<dc:title>', gettext('Object Name'), false, 256, true, 'string', false),
				'XMPSource' => array('XMP', '<photoshop:Source>', gettext('Image Source'), false, 32, true, 'string', false),
				'XMPState' => array('XMP', '<photoshop:State>', gettext('Province/State'), false, 32, true, 'string', false),
				'XMPSubLocation' => array('XMP', '<Iptc4xmpCore:Location>', gettext('Sub-location'), false, 32, true, 'string', false),
				'XMPrating' => array('XMP', '<MicrosoftPhoto:Rating>', gettext('Rating'), false, 32, true, 'string', false)
		);
	}

	/**
	 * Parses xmp metadata for interesting tags
	 *
	 * @param string $xmpdata
	 * @return array
	 */
	private static function extract($xmpdata) {
		$desiredtags = array(
				'owner' => '<zp:Owner>',
				'thumb' => '<zp:Thumbnail>',
				'watermark' => '<zp:Watermark>',
				'watermark_use' => '<zp:Watermark_use>',
				'watermark_thumb' => '<zp:Watermark_thumb>',
				'custom_data' => '<zp:CustomData',
				'codeblock' => '<zp:Codeblock>'
		);
		foreach (self::getMetadataFields()as $field => $item) {
			$desiredtags[$field] = $item[1];
		}
		$xmp_parsed = array();
		while (!empty($xmpdata)) {
			$s = strpos($xmpdata, '<');
			$e = strpos($xmpdata, '>', $s);
			$tag = substr($xmpdata, $s, $e - $s + 1);
			$xmpdata = substr($xmpdata, $e + 1);
			$key = array_search($tag, $desiredtags);
			if ($key !== false) {
				$close = str_replace('<', '</', $tag);
				$e = strpos($xmpdata, $close);
				$meta = trim(substr($xmpdata, 0, $e));
				$xmpdata = substr($xmpdata, $e + strlen($close));
				if (strpos($meta, '<') === false) {
					$xmp_parsed[$key] = self::decode($meta);
				} else {
					$elements = array();
					while (!empty($meta)) {
						$s = strpos($meta, '<');
						$e = strpos($meta, '>', $s);
						$tag = substr($meta, $s, $e - $s + 1);
						$meta = substr($meta, $e + 1);
						if (strpos($tag, 'rdf:li') !== false) {
							$e = strpos($meta, '</rdf:li>');
							$elements[] = self::decode(trim(substr($meta, 0, $e)));
							$meta = substr($meta, $e + 9);
						}
					}
					$xmp_parsed[$key] = $elements;
				}
			} else { // look for shorthand elements
				if (strpos($tag, '<rdf:Description') !== false) {
					$meta = substr($tag, 17); // strip off the description tag leaving the elements
					while (preg_match('/^[a-zA-z0-9_]+\:[a-zA-z0-9_]+\=".*"/', $meta, $element)) {
						$item = $element[0];
						$meta = trim(substr($meta, strlen($item)));
						$i = strpos($item, '=');
						$tag = '<' . substr($item, 0, $i) . '>';
						$v = self::decode(trim(substr($item, $i + 2, -1)));
						$key = array_search($tag, $desiredtags);
						if ($key !== false) {
							$xmp_parsed[$key] = trim($v);
						}
					}
				}
			}
		}
		return ($xmp_parsed);
	}

	/**
	 * insures that the metadata is a string
	 *
	 * @param mixed $meta
	 * @return string
	 */
	private static function to_string($meta) {
		if (is_array($meta)) {
			$meta = implode(',', $meta);
		}
		return trim($meta);
	}

	/**
	 * Filter called when an album object is instantiated
	 * sets the sidecars to include xmp files
	 *
	 * @param $album album object
	 * @return $object
	 */
	static function album_instantiate($album) {
		$album->addSidecar(XMP_EXTENSION);
		return $album;
	}

	/**
	 * Filter for handling album objects
	 *
	 * @param object $album
	 * @return object
	 */
	static function new_album($album) {
		$metadata_path = dirname($album->localpath) . '/' . basename($album->localpath) . '*';
		$files = safe_glob($metadata_path);
		if (count($files) > 0) {
			foreach ($files as $file) {
				if (strtolower(getSuffix($file)) == XMP_EXTENSION) {
					$source = file_get_contents($file);
					$metadata = self::extract($source);
					if (array_key_exists('XMPImageCaption', $metadata)) {
						$album->setDesc(self::to_string($metadata['XMPImageCaption']));
					}
					if (array_key_exists('XMPImageHeadline', $metadata)) {
						$data = self::to_string($metadata['XMPImageHeadline']);
						if (getoption('transform_newlines')) {
							$desc = nl2br($desc);
						}
						$album->setTitle($data);
					}
					if (array_key_exists('XMPLocationName', $metadata)) {
						$album->setLocation(self::to_string($metadata['XMPLocationName']));
					}
					if (array_key_exists('XMPKeywords', $metadata)) {
						$tags = $metadata['XMPKeywords'];
						if (!is_array($tags)) {
							$tags = explode(',', $tags);
						}
						$album->setTags($tags);
					}
					if (array_key_exists('XMPDateTimeOriginal', $metadata)) {
						$album->setDateTime($metadata['XMPDateTimeOriginal']);
					}
					if (array_key_exists('thumb', $metadata)) {
						$album->setThumb($metadata['thumb']);
					}
					if (array_key_exists('owner', $metadata)) {
						$album->setOwner($metadata['owner']);
					}
					if (array_key_exists('codeblock', $metadata)) {
						$album->setCodeblock($metadata['codeblock']);
					}
					if (array_key_exists('watermark', $metadata)) {
						$album->setWatermark($metadata['watermark']);
					}
					if (array_key_exists('watermark_thumb', $metadata)) {
						$album->setWatermarkThumb($metadata['watermark_thumb']);
					}
					if (array_key_exists('rating', $metadata)) {
						$v = min(getoption('rating_stars_count'), $metadata['rating']) * min(1, getOption('rating_split_stars'));
						$album->set('total_value', $v);
						$album->set('rating', $v);
						$album->set('total_votes', 1);
					}
					$album->save();
					break;
				}
			}
			return $album;
		}
	}

	/**
	 * Finds and returns xmp metadata
	 *
	 * @param int $j
	 * @return string
	 */
	private static function extractXMP($f) {
		if (preg_match('~<.*?xmpmeta~', $f, $m)) {
			$open = $m[0];
			$close = str_replace('<', '</', $open);
			$j = strpos($f, $open);
			if ($j !== false) {
				$k = strpos($f, $close, $j + 4);
				$meta = substr($f, $j, $k + 14 - $j);
				$l = 0;
				return $meta;
			}
		}
		return false;
	}

	/**
	 * convert a fractional representation to something more user friendly
	 *
	 * @param $element string
	 * @return string
	 */
	private static function rationalNum($element) {
		// deal with the fractional representation
		$n = explode('/', $element);
		$v = sprintf('%f', $n[0] / $n[1]);
		for ($i = strlen($v) - 1; $i > 1; $i--) {
			if ($v{$i} != '0')
				break;
		}
		if ($v{$i} == '.')
			$i--;
		return substr($v, 0, $i + 1);
	}

	private static function encode($str) {
		return strtr($str, array_flip(self::$XML_trans));
	}

	private static function decode($str) {
		return strtr($str, self::$XML_trans);
	}

	static function image_instantiate($image) {
		$image->addSidecar(XMP_EXTENSION);
		return $image;
	}

	/**
	 * Filter for handling image objects
	 *
	 * @param object $image
	 * @return object
	 */
	static function new_image($image) {
		$source = '';
		$metadata_path = '';
		$files = safe_glob(substr($image->localpath, 0, strrpos($image->localpath, '.')) . '.*');
		if (count($files) > 0) {
			foreach ($files as $file) {
				if (strtolower(getSuffix($file)) == XMP_EXTENSION) {
					$metadata_path = $file;
					break;
				}
			}
		}
		if (!empty($metadata_path)) {
			$source = self::extractXMP(file_get_contents($metadata_path));
		} else if (getOption('xmpMetadata_examine_images_' . strtolower(substr(strrchr($image->localpath, "."), 1)))) {
			$f = file_get_contents($image->localpath);
			$l = filesize($image->localpath);
			$abort = 0;
			$i = 0;
			while ($i < $l && $abort < 200 && !$source) {
				$tag = bin2hex(substr($f, $i, 2));
				$size = hexdec(bin2hex(substr($f, $i + 2, 2)));
				switch ($tag) {
					case 'ffe1': // EXIF
					case 'ffe2': // EXIF extension
					case 'fffe': // COM
					case 'ffe0': // IPTC marker
						$source = self::extractXMP($f);
						$i = $i + $size + 2;
						$abort = 0;
						break;
					default:
						if ($f{$i} == '<') {
							$source = self::extractXMP($f);
						}
						$i = $i + 1;
						$abort++;
						break;
				}
			}
		}
		// direct import to ZenPhoto20 fields with no processing required
		$import = array(
				'location' => 'XMPSubLocation',
				'city' => 'XMPCity',
				'city' => 'XMPCity',
				'state' => 'XMPState',
				'country' => 'XMPLocationName',
				'copyright' => 'XMPCopyright',
				'rotation' => 'XMPOrientation'
		);
		if (!empty($source)) {
			$metadata = self::extract($source);
			if (count($metadata > 0)) {
				$exifVars = self::getMetadataFields();
				foreach ($metadata as $field => $element) {
					if (!array_key_exists($field, $exifVars) || $exifVars[$field][EXIF_FIELD_ENABLED]) {
						$image->set('hasMetadata', 1);
						$v = self::to_string($element);
						if (($key = array_search($field, $import)) !== false) {
							$image->set($key, $v);
						} else {
							switch ($field) {
								case 'XMPSource':
									if (!isset($metadata['XMPImageCredit'])) {
										$this->set('credit', $v);
									}
									break;
								case 'XMPImageCredit':
									$this->set('credit', $v);
									break;
								case 'XMPImageHeadline':
									if (getoption('transform_newlines')) {
										$v = nl2br($v);
									}
									$image->setTitle($v);
									break;
								case 'XMPImageCaption':
									if (getoption('transform_newlines')) {
										$v = nl2br(v);
									}
									$image->setDesc($v);
									break;
								case 'XMPDateTimeOriginal':
									$image->setDateTime($element);
									break;
								case 'XMPExposureTime':
									$v = formatExposure(self::rationalNum($element));
									break;
								case 'XMPFocalLength':
									$v = self::rationalNum($element) . ' mm';
									break;
								case 'XMPAperatureValue':
								case 'XMPFNumber':
									$v = 'f/' . self::rationalNum($element);
									break;
								case 'XMPExposureBiasValue':
								case 'XMPGPSAltitude':
									$v = self::rationalNum($element);
									break;
								case 'XMPGPSLatitude':
								case 'XMPGPSLongitude':
									$n = explode(',', substr($element, 0, -1));
									$r = $n[0] + $n[1] / 60;
									$ref = strtoupper(substr($element, -1, 1));
									$v = Image::toDMS($r, $ref);
									if (in_array($ref, array('S', 'W'))) {
										$r = -$r;
									}
									$r = (float) $r;
									$image->set(substr($field, 3), $r);
									break;
								case 'XMPLensInfo':
									preg_match_all('~(\d+/\d+)~', $v, $matches);
									if (isset($matches[1]) && !empty($matches[1])) {
										$lens = array();
										foreach ($matches[1] as $i => $f) {
											$term = explode('/', $f);
											if ($term[0] != 0 && $term[1] != 0) {
												$lens[$i] = convertToFraction($term[0] / $term[1]);
											} else {
												$lens[$i] = 0;
											}
										}
										if ($lens[0] == $lens[1]) {
											$v = sprintf('%0.0fmm', $lens[0]);
										} else {
											$v = sprintf('%0.0f-%0.0fmm', $lens[0], $lens[1]);
										}
										if ($lens[2] == $lens[3]) {
											if ($lens[2] != 0) {
												$v .= sprintf(' f/%0.1f', $lens[2]);
											}
										} else {
											$v .= sprintf(' f/%0.1f-%0.1f', $lens[3], $lens[2]);
										}
									}
									break;
								case 'rating':
									$v = min(getoption('rating_stars_count'), $v) * min(1, getOption('rating_split_stars'));
									$image->set('total_value', $v);
									$image->set('total_votes', 1);
									break;
								case 'XMPKeywords':
									if (!is_array($element)) {
										$element = explode(',', $element);
									}
									$element = array_unique(array_merge($image->getTags(), $element));
									$image->setTags($element);
									break;
							}
						}
						$image->set($field, $v);
					}
				}
				$image->save();
			}
		}
		return $image;
	}

	static function sidecars($types) {
		$types[] = XMP_EXTENSION;
		return $types;
	}

	static function putXMP($object, $prefix) {
		if (isset($_POST['xmpMedadataPut_' . $prefix])) {
			self::publish($object);
		}
		return $object;
	}

	static function publish($object) {
		$desiredtags = array('copyright' => '<dc:rights>',
				'desc' => '<dc:description>',
				'title' => '<dc:title>',
				'tags' => '<dc:subject>',
				'location' => '<Iptc4xmpCore:Location>',
				'city' => '<photoshop:City>',
				'state' => '<photoshop:State>',
				'country' => '<photoshop:Country>',
				'title' => '<photoshop:Headline>',
				'credit' => '<photoshop:Credit>',
				'thumb' => '<zp:Thumbnail>',
				'owner' => '<zp:Owner>',
				'watermark' => '<zp:Watermark>',
				'watermark_use' => '<zp:Watermark_use>',
				'watermark_thumb' => '<zp:Watermark_thumb>',
				'custom_data' => '<zp:CustomData>',
				'codeblock' => '<zp:Codeblock>',
				'date' => '<exif:DateTimeOriginal>',
				'rating' => '<MicrosoftPhoto:Rating>'
		);
		$process = array('dc', 'Iptc4xmpCore', 'photoshop', 'xap');
		if (isAlbumClass($object)) {
			$file = rtrim($object->localpath, '/');
			$file .= '.xmp';
		} else {
			$file = stripSuffix($object->localpath) . '.xmp';
		}
		@chmod($file, 0777);
		$f = fopen($file, 'w');
		fwrite($f, '<x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 4.2-c020 1.124078, Tue Sep 11 2007 23:21:40 ">' . "\n");
		fwrite($f, ' <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' . "\n");
		$last_element = $special = $output = false;
		foreach ($desiredtags as $field => $elementXML) {
			$elementXML = substr($elementXML, 1, -1);
			if ($last_element != $elementXML) {
				if ($output) {
					fwrite($f, '  </rdf:Description>' . "\n");
					fwrite($f, '  <rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n");
				}
				$last_element = $elementXML;
				$output = false;
			}
			$v = self::encode($object->get($field));
			$tag = $elementXML;
			switch ($elementXML) {
				case 'dc:creator':
					$special = 'rdf:Seq';
					$tag = 'rdf:li';
					if ($v) {
						fwrite($f, "   <$elementXML>\n");
						fwrite($f, "    <$special>\n");
						fwrite($f, "     <$tag>$v</$tag>\n");
						fwrite($f, "    </$special>\n");
						fwrite($f, "   </$elementXML>\n");
						$output = true;
					}
					break;
				case 'dc:rights':
				case 'xapRights:UsageTerms':
					$special = 'rdf:Alt';
					$tag = 'rdf:li';
					if ($v) {
						fwrite($f, "   <$elementXML>\n");
						fwrite($f, "    <$special>\n");
						fwrite($f, "     <$tag>$v</$tag>\n");
						fwrite($f, "    </$special>\n");
						fwrite($f, "   </$elementXML>\n");
						$output = true;
					}
					break;
				case 'dc:subject':
					$tags = $object->getTags(false);
					if (!empty($tags)) {
						fwrite($f, "   <$elementXML>\n");
						fwrite($f, "    <rdf:Bag>\n");
						foreach ($tags as $tag) {
							fwrite($f, "     <rdf:li>" . self::encode($tag) . "</rdf:li>\n");
						}
						fwrite($f, "    </rdf:Bag>\n");
						fwrite($f, "   </$elementXML>\n");
						$output = true;
					}
					break;
				default:
					if ($v) {
						fwrite($f, "   <$tag>$v</$tag>\n");
						$output = true;
					}
					break;
			}
		}
		fwrite($f, '  </rdf:Description>' . "\n");
		fwrite($f, ' </rdf:RDF>' . "\n");
		fwrite($f, '</x:xmpmeta>' . "\n");
		fclose($f);
		clearstatcache();
		@chmod($file, FILE_MOD);
		return gettext('Metadata exported');
	}

	static function create($html, $object, $prefix) {
		if ($html)
			$html .= '<hr />';
		$html .= '<label><input type="checkbox" name="xmpMedadataPut_' . $prefix . '" value="1" /> ' . gettext('Export metadata info to XMP sidecar.') . '</label>';
		return $html;
	}

	static function bulkActions($actions) {
		return array_merge($actions, array(gettext('Export Metadata') => 'xmpMetadataPublish'));
	}

}

function xmpMetadata_enable($enabled) {
	if ($enabled) {
		//establish defaults for display and disable
		$display = $disable = array();
		$exifvars = xmpMetadata::getMetadataFields();
		foreach ($exifvars as $key => $item) {
			if ($exifvars[$key][EXIF_DISPLAY]) {
				$display[$key] = $key;
			}
			if (!$exifvars[$key][EXIF_FIELD_ENABLED]) {
				$disable[$key] = $key;
			}
		}
		setOption('metadata_disabled', serialize($disable));
		setOption('metadata_displayed', serialize($display));
	}
	requestSetup('XMP Metadata');
}

?>