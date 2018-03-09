<?php
// force UTF-8 Ø

/* Plug-in for theme option handling
 * The Admin Options page tests for the presence of this file in a theme folder
 * If it is present it is linked to with a require_once call.
 * If it is not present, no theme options are displayed.
 *
 */

class ThemeOptions {

  function __construct() {
    
    if (!extensionEnabled('zenpage')) enableExtension('zenpage', 8291, true);
    if (!extensionEnabled('print_album_menu')) enableExtension('print_album_menu', 1025, true);
    
    setThemeOptionDefault('Use_thickbox', 1);
    setThemeOptionDefault('Allow_search', 1);
    setThemeOptionDefault('zenfluid_theme', 'colorlightgreen');
    setThemeOptionDefault('zenfluid_border', 'borderround');
    setThemeOptionDefault('zenfluid_font', 'times/fonttimes');
    setThemeOptionDefault('zenfluid_menuupper', 0);
    setThemeOptionDefault('zenfluid_homelink', false);
    setThemeOptionDefault('zenfluid_thumbdesc', 1);
    setThemeOptionDefault('zenfluid_titlebreadcrumb', 1);
    setThemeOptionDefault('zenfluid_randomimage', 1);
    setThemeOptionDefault('zenfluid_imageroot', '');
    setThemeOptionDefault('zenfluid_transitionnewrow', 1);
    setThemeOptionDefault('zenfluid_showheader', 0);
    setThemeOptionDefault('zenfluid_showfooter', 0);
    setThemeOptionDefault('zenfluid_stagewidth', 1280);
    setThemeOptionDefault('zenfluid_commentwidth', 800);
    setThemeOptionDefault('zenfluid_descriptionwidth', 800);
    setThemeOptionDefault('zenfluid_descriptionjustification', 'left');
    setThemeOptionDefault('zenfluid_stageposition', 'center');
    setThemeOptionDefault('zenfluid_titleposition', 'center');
    setThemeOptionDefault('zenfluid_buttonposition', 'center');
    setThemeOptionDefault('zenfluid_commentposition', 'center');
    setThemeOptionDefault('zenfluid_menuposition', 'left');
    setThemeOptionDefault('zenfluid_descriptionposition', 'center');
    setThemeOptionDefault('zenfluid_buttontitle', 1);
    setThemeOptionDefault('zenfluid_stageimage', 0);
    setThemeOptionDefault('zenfluid_stagethumb', 1);
    setThemeOptionDefault('zenfluid_menutitles', 0);
    setThemeOptionDefault('zenfluid_titlemargin', 75);
    setThemeOptionDefault('zenfluid_titletop', 0);
    setThemeOptionDefault('albums_per_page', 20);
    setThemeOptionDefault('albums_per_row', 2);
    setThemeOptionDefault('images_per_page', 20);
    setThemeOptionDefault('images_per_row', 2);
    setThemeOptionDefault('image_size', 1920, NULL, 'zenpage');
    setThemeOptionDefault('image_use_side', 'width', NULL, 'zenpage');
    setThemeOptionDefault('thumb_size',100, NULL, 'zenpage');
    setThemeOptionDefault('thumb_crop_width', 100);
    setThemeOptionDefault('thumb_crop_height', 100);
    setThemeOptionDefault('thumb_crop', 1);
    setThemeOptionDefault('thumb_transition', 1);

    if (class_exists('colorbox')) {
      colorbox::registerScripts(array('album','image','search','contact','news','pages','index'));
			setOptionDefault('colorbox_theme', 'custom');
    }
    
    if (class_exists('cacheManager')) {
      $me = basename(dirname(__FILE__));
      cacheManager::deleteThemeCacheSizes($me);
      cacheManager::addThemeCacheSize($me, NULL, 1920, 1920, NULL, NULL, NULL, NULL, NULL, false, getOption('fullimage_watermark'), true);
      cacheManager::addThemeCacheSize($me, 100, NULL, NULL, getThemeOption('thumb_crop_width'), getThemeOption('thumb_crop_height'), NULL, NULL, true, getOption('Image_watermark'), NULL, NULL);
    }
  }

  function getOptionsSupported() {
  
    $themeList = array( gettext('White') => 'colorwhite', gettext('Light Green') => 'colorlightgreen', gettext('Dark gray') => 'colordarkgray');
    $borderList = array(gettext('Round corners') => 'borderround',  gettext('Square corners') => 'bordersquare', gettext('No Border') => 'borderno');
    $fontList = array(gettext('Times') => 'times/fonttimes', gettext('Com4T') => 'com4t/fontcom4t');
    $positions = array( gettext('Left') => 'left', gettext('Center') => 'center', gettext('Right') => 'right');
  
    $list = array();
    genAlbumList($list);
    foreach ($list as $fullfolder => $albumtitle) {
      $list[$fullfolder] = $fullfolder;
    }
    $list['*All Albums*'] = '';

  
    $options = array(
      gettext('ZenFluid color') => array(
        'key' => 'zenfluid_theme',
        'order' => 1, 
        'type' => OPTION_TYPE_SELECTOR, 
        'selections' => $themeList, 
        'desc' => gettext("Select the colour scheme.")
      ),
      gettext('Font type') => array(
        'key' => 'zenfluid_font', 
        'order' => 2, 
        'type' => OPTION_TYPE_SELECTOR, 
        'selections' => $fontList, 
        'desc' => gettext("Select the font style.")
      ),
      gettext('Border type') => array(
        'key' => 'zenfluid_border', 
        'order' => 3, 
        'type' => OPTION_TYPE_SELECTOR, 
        'selections' => $borderList, 
        'desc' => gettext("Select the border scheme.")
      ),
      gettext('Allow search') => array(
        'key' => 'Allow_search',
        'order' => 4, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable search form.')
      ),
      gettext('Use Colorbox') => array(
        'key' => 'Use_thickbox',
        'order' => 5, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable to display full size image with Colorbox.')
      ),
      gettext('Use UPPERCASE menu') => array(
        'key' => 'zenfluid_menuupper',
        'order' => 6, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable if you want all menu entries to be uppercase')
      ),
      gettext('Include HOME link') => array(
        'key' => 'zenfluid_homelink',
        'order' => 7, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable if you want to include a HOME link at the top of the menus')
      ),
      gettext('Include title and description with thumbnail') => array(
        'key' => 'zenfluid_thumbdesc',
        'order' => 8, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable if you want to include the title and description beside each thumbnail')
      ),
      gettext('Use random image') => array(
        'key' => 'zenfluid_randomimage',
        'order' => 9, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable if you want a random image displayed on the home page, otherwise the latest image will be displayed')
      ),
      gettext('Random/Latest image root folder') => array(
        'key' => 'zenfluid_imageroot',
        'order' => 10, 
        'type' => OPTION_TYPE_SELECTOR, 
        'selections' => $list, 
        'desc' => gettext('Optional: Select the name of the album folder from which the random or latest image will be taken.')
      ),
      gettext('Print title breadcrumb') => array(
        'key' => 'zenfluid_titlebreadcrumb',
        'order' => 11, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable this if you want the album title to be included before the image title')
      ),
      gettext('Title Margin') => array(
        'key' => 'zenfluid_titlemargin',
        'order' => 12, 
        'type' => OPTION_TYPE_TEXTBOX, 
        'desc' => gettext('Set size (in pixels) of the title, buttons, and comments that always shows below the image or video')
      ),
      gettext('Title on top') => array(
        'key' => 'zenfluid_titletop',
        'order' => 13, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable this if you want the image title to be displayed above the image')
      ),
      gettext('Transition on new row') => array(
        'key' => 'zenfluid_transitionnewrow',
        'order' => 14, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('If combined transition is selected above, enable this if you wish the transition to start on a new row, otherwise the transition will continue on the same row')
      ),
      gettext('Show header') => array(
        'key' => 'zenfluid_showheader',
        'order' => 15, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable to show a header with gallery title and description across the top of the screen instead of at the top of the sidebar')
      ),
      gettext('Show footer') => array(
        'key' => 'zenfluid_showfooter',
        'order' => 16, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable to show a footer across the bottom of the screen instead of at the bottom of the sidebar')
      ),
      gettext('Stage maximum width') => array(
        'key' => 'zenfluid_stagewidth',
        'order' => 17, 
        'type' => OPTION_TYPE_TEXTBOX, 
        'desc' => gettext('Enter the maximum width (in pixels) that the stage (everything to the right of the sidebar) should take.')
      ),
      gettext('Comment maximum width') => array(
        'key' => 'zenfluid_commentwidth',
        'order' => 18, 
        'type' => OPTION_TYPE_TEXTBOX, 
        'desc' => gettext('Enter the maximum width (in pixels) that the comments section should take. (Enter 0 (zero) to use the maximum width)')
      ),
      gettext('Description maximum width') => array(
        'key' => 'zenfluid_descriptionwidth',
        'order' => 19, 
        'type' => OPTION_TYPE_TEXTBOX, 
        'desc' => gettext('Enter the maximum width (in pixels) that the description section should take. (Enter 0 (zero) to use the maximum width)')
      ),
      gettext('Description justification') => array(
        'key' => 'zenfluid_descriptionjustification',
        'order' => 20, 
        'type' => OPTION_TYPE_RADIO, 
        'buttons' => $positions, 
        'desc' => gettext('Enter the default justification for the description text (Overide individual descriptions in admin panel text editor).')
      ),
      gettext('Stage position') => array(
        'key' => 'zenfluid_stageposition',
        'order' => 21, 
        'type' => OPTION_TYPE_RADIO, 
        'buttons' => $positions, 
        'desc' => gettext('Select the position of the stage when its width is less than the width of the window')
      ),
      gettext('Title position') => array(
        'key' => 
        'zenfluid_titleposition',
        'order' => 22, 
        'type' => OPTION_TYPE_RADIO, 
        'buttons' => $positions, 
        'desc' => gettext('Select the position of the title')
      ),
      gettext('Description position') => array(
        'key' => 'zenfluid_descriptionposition',
        'order' => 23, 
        'type' => OPTION_TYPE_RADIO, 
        'buttons' => $positions, 
        'desc' => gettext('Select the position of the image/album description.')
      ),
      gettext('Button position') => array(
        'key' => 'zenfluid_buttonposition',
        'order' => 24, 
        'type' => OPTION_TYPE_RADIO, 
        'buttons' => $positions, 
        'desc' => gettext('Select the position of the buttons under the image')
      ),
      gettext('Comment position') => array(
        'key' => 'zenfluid_commentposition',
        'order' => 25, 
        'type' => OPTION_TYPE_RADIO, 
        'buttons' => $positions, 
        'desc' => gettext('Select the position of the comments section when its width is less than the stage')
      ),
      gettext('Menu justification') => array(
        'key' => 'zenfluid_menuposition',
        'order' => 26, 
        'type' => OPTION_TYPE_RADIO, 
        'buttons' => $positions, 
        'desc' => gettext('Select the position of the sidebar menu items')
      ),
      gettext('Image width same as Stage') => array(
        'key' => 'zenfluid_stageimage',
        'order' => 27, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable this to force the image width to match the stage width if the image width is larger than the stage width')
      ),
      gettext('Thumb list width same as Stage') => array(
        'key' => 'zenfluid_stagethumb',
        'order' => 28, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable this to force the width of the list of thumbs to match the stage width')
      ),
      gettext('Buttons before Title') => array(
        'key' => 'zenfluid_buttontitle',
        'order' => 29, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable if you want the image buttons to appear before the title and description')
      ),
      gettext('Menu section titles') => array(
        'key' => 'zenfluid_menutitles',
        'order' => 30, 
        'type' => OPTION_TYPE_CHECKBOX, 
        'desc' => gettext('Enable the display of a title for each sidebar menu section.')
      )
    );
  return $options;
  }

  function getOptionsDisabled() {
    return array('custom_index_page','albums_per_row','images_per_row');
  }

  function handleOption($option, $currentValue) {
  }
}
?>