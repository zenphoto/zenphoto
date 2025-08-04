<?php
/**
 * Search related template functions
 * 
 * @since 1.7 moved to separate file from template-functions.php
 * 
 * @package zpcore\functions\template
 */

/**
 * tests if a search page is an "archive" page
 *
 * @return bool
 */
function isArchive() {
	return isset($_REQUEST['date']);
}

/**
 * Returns a search URL
 * 
 * @since 1.1.3
 * @deprecated 2.0 - Use SearchEngine::getSearchURL() instead
 *
 * @param mixed $words the search words target
 * @param mixed $dates the dates that limit the search
 * @param mixed $fields the fields on which to search
 * @param int $page the page number for the URL
 * @param array $object_list the list of objects to search
 * @return string
 */
function getSearchURL($words, $dates, $fields, $page, $object_list = NULL) {
	deprecationNotice(gettext('Use SearchEngine::getSearchURL() instead'));
	return SearchEngine::getSearchURL($words, $dates, $fields, $page, $object_list);
}

/**
 * Prints the search form
 *
 * Search works on a list of tokens entered into the search form.
 *
 * Tokens may be part of boolean expressions using &, |, !, and parens. (Comma is retained as a synonom of | for
 * backwords compatibility.)
 *
 * Tokens may be enclosed in quotation marks to create exact pattern matches or to include the boolean operators and
 * parens as part of the tag..
 *
 * @param string $prevtext text to go before the search form
 * @param string $id css id for the search form, default is 'search'
 * @param string $buttonSource optional path to the image for the button or if not a path to an image,
 * 											this will be the button hint
 * @param string $buttontext optional text for the button ("Search" will be the default text)
 * @param string $iconsource optional theme based icon for the search fields toggle
 * @param array $query_fields override selection for enabled fields with this list
 * @param array $objects_list optional array of things to search eg. [albums]=>[list], etc.
 * 														if the list is simply 0, the objects will be omitted from the search
 * @param string $within set to true to search within current results, false to search fresh
 * @param bool $enable_fieldselector True|false to enable/disable the search fields selector. Default null to use the option as set
 * @since 1.1.3
 */
function printSearchForm($prevtext = NULL, $id = 'search', $buttonSource = '', $buttontext = '', $iconsource = NULL, $query_fields = NULL, $object_list = NULL, $within = NULL, $enable_fieldselector = null) {
	global $_zp_adminjs_loaded, $_zp_current_search;
	if (is_null($enable_fieldselector)) {
		$enable_fieldselector = getOption('search_fieldsselector_enabled');
	}
	$engine = new SearchEngine();
	if (!is_null($_zp_current_search) && !$_zp_current_search->getSearchWords()) {
		$engine->clearSearchWords();
	}
	if (!is_null($object_list)) {
		if (array_key_exists(0, $object_list)) { // handle old form albums list
			trigger_error(gettext('printSearchForm $album_list parameter is deprecated. Pass array("albums"=>array(album, album, ...)) instead.'), E_USER_NOTICE);
			$object_list = array('albums' => $object_list);
		}
	}
	if (empty($buttontext)) {
		$buttontext = gettext("Search");
	}
	$searchwords = $engine->codifySearchString();
	if (substr($searchwords, -1, 1) == ',') {
		$searchwords = substr($searchwords, 0, -1);
	}
	$hint = $hintJS = '%s';
	if (empty($searchwords)) {
		$within = false;
	} else {
		$hintJS = gettext('%s within previous results');
	}
	if (is_null($within)) {
		$within = getOption('search_within');
	}
	if ($within) {
		$hint = gettext('%s within previous results');
	}
	if (preg_match('!\/(.*)[\.png|\.jpg|\.jpeg|\.gif]$!', strval($buttonSource))) {
		$buttonSource = 'src="' . $buttonSource . '" alt="' . $buttontext . '"';
		$button = 'title="' . sprintf($hint, $buttontext) . '"';
		$type = 'image';
	} else {
		$type = 'submit';
		if ($buttonSource) {
			$button = 'value="' . $buttontext . '" title="' . sprintf($hint, $buttonSource) . '"';
			$buttonSource = '';
		} else {
			$button = 'value="' . $buttontext . '" title="' . sprintf($hint, $buttontext) . '"';
		}
	}
	if (empty($iconsource)) {
		$iconsource = WEBPATH . '/' . ZENFOLDER . '/images/searchfields_icon.png';
	}
	$searchurl = SearchEngine::getSearchURL();
	if (!$within) {
		$engine->clearSearchWords();
	}

	$fields = $engine->allowedSearchFields();
	if (!$_zp_adminjs_loaded) {
		$_zp_adminjs_loaded = true;
		?>
		<script src="<?php echo WEBPATH . '/' . ZENFOLDER; ?>/js/zp_admin.js"></script>
		<?php
	}
	?>
	<div id="<?php echo $id; ?>">
		<!-- search form -->
		<form method="get" action="<?php echo $searchurl; ?>" id="search_form">
			<?php if(!MOD_REWRITE) { ?>
				<input type="hidden" name="p" value="search" />
			<?php } ?>
			<script>
			var within = <?php echo (int) $within; ?>;
			function search_(way) {
				within = way;
				if (way) {
					$('#search_submit').attr('title', '<?php echo sprintf($hintJS, $buttontext); ?>');
				} else {
					lastsearch = '';
					$('#search_submit').attr('title', '<?php echo $buttontext; ?>');
				}
				$('#search_input').val('');
			}
			$('#search_form').submit(function() {
				if (within) {
					var newsearch = $.trim($('#search_input').val());
					if (newsearch.substring(newsearch.length - 1) == ',') {
						newsearch = newsearch.substr(0, newsearch.length - 1);
					}
					if (newsearch.length > 0) {
						$('#search_input').val('(<?php echo js_encode($searchwords); ?>) AND (' + newsearch + ')');
					} else {
						$('#search_input').val('<?php echo js_encode($searchwords); ?>');
					}
				}
				return true;
			});
    $(document).ready(function() {
      $( $("#checkall_searchfields") ).on( "click", function() {
        $("#searchextrashow :checkbox").prop("checked", $("#checkall_searchfields").prop("checked") );
      });
    });
			</script>
			<?php echo $prevtext; ?>
			<div>
				<span class="tagSuggestContainer">
					<input type="text" name="s" value="" id="search_input" size="10" />
				</span>
				<?php if ($enable_fieldselector && (count($fields) > 1 || $searchwords)) { ?>
					<a class="toggle_searchextrashow" href="#"><img src="<?php echo $iconsource; ?>" title="<?php echo gettext('search options'); ?>" alt="<?php echo gettext('fields'); ?>" id="searchfields_icon" /></a>
					<script>
						$(".toggle_searchextrashow").click(function(event) {
							event.preventDefault();
							$("#searchextrashow").toggle();
						});
					</script>
				<?php } ?>
				<input type="<?php echo $type; ?>" <?php echo $button; ?> class="button buttons" id="search_submit" <?php echo $buttonSource; ?> data-role="none" />
				<?php
				if (is_array($object_list)) {
					foreach ($object_list as $key => $list) {
						?>
						<input type="hidden" name="in<?php echo $key ?>" value="<?php
						if (is_array($list))
							echo html_encode(implode(',', $list));
						else
							echo html_encode($list);
						?>" />
									 <?php
								 }
							 }
							 ?>
				<br />
				<?php
				if (count($fields) > 1 || $searchwords) {
					$fields = array_flip($fields);
					sortArray($fields);
					$fields = array_flip($fields);
					if (is_null($query_fields)) {
						$query_fields = $engine->parseQueryFields();
					} else {
						if (!is_array($query_fields)) {
							$query_fields = $engine->numericFields($query_fields);
						}
					}
					if (count($query_fields) == 0) {
						$query_fields = $engine->allowedSearchFields();
					}
					?>
					<div style="display:none;" id="searchextrashow">
						<?php
						if ($searchwords) {
							?>
							<label>
								<input type="radio" name="search_within" id="search_within-1" value="1"<?php if ($within) echo ' checked="checked"'; ?> onclick="search_(1);" />
								<?php echo gettext('Within'); ?>
							</label>
							<label>
								<input type="radio" name="search_within" id="search_within-0" value="1"<?php if (!$within) echo ' checked="checked"'; ?> onclick="search_(0);" />
								<?php echo gettext('New'); ?>
							</label>
							<?php
						}
						if ($enable_fieldselector && count($fields) > 1) {
							?>
							<ul>
        <li><label><input type="checkbox" name="checkall_searchfields" id="checkall_searchfields" checked="checked">* <?php echo gettext('Check/uncheck all'); ?> *</label></li>
								<?php
								foreach ($fields as $display => $key) {
									echo '<li><label><input id="SEARCH_' . html_encode($key) . '" name="searchfields[]" type="checkbox"';
									if (in_array($key, $query_fields)) {
										echo ' checked="checked" ';
									}
									echo ' value="' . html_encode($key) . '"  /> ' . html_encode($display) . "</label></li>" . "\n";
								}
								?>
							</ul>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
		</form>
	</div><!-- end of search form -->
	<?php
}

/**
 * Returns the a sanitized version of the search string
 *
 * @return string
 * @since 1.1
 */
function getSearchWords() {
	global $_zp_current_search;
	if (!in_context(ZP_SEARCH))
		return '';
	return $_zp_current_search->getSearchWordsSanitized();
}

/**
 * Returns the date of the search
 *
 * @param string $format A datetime format, if using localized dates an ICU dateformat
 * @return string
 * @since 1.1
 */
function getSearchDate($format = 'F Y') {
	if (in_context(ZP_SEARCH)) {
		global $_zp_current_search;
		return $_zp_current_search->getSearchDateFormatted($format);
	}
	return false;
}

