<?php 
/**
 * Tags related admin functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @package zpcore\admin\functions
 */




	/**
	 * Creates an unordered checklist of the tags
	 *
	 * @param object $that Object for which to get the tags
	 * @param string $postit prefix to prepend for posting
	 * @param bool $showCounts set to true to get tag count displayed
	 */
	function tagSelector($that, $postit, $showCounts = false, $mostused = false, $addnew = true, $resizeable = false, $class = 'checkTagsAuto') {
		global $_zp_admin_ordered_taglist, $_zp_admin_lc_taglist;
		if (is_null($_zp_admin_ordered_taglist)) {
			if ($mostused || $showCounts) {
				$counts = getAllTagsCount();
				if ($mostused)
					arsort($counts, SORT_NUMERIC);
				$them = array();
				foreach ($counts as $tag => $count) {
					$them[] = $tag;
				}
			} else {
				$them = getAllTagsUnique();
			}
			$_zp_admin_ordered_taglist = $them;
			$_zp_admin_lc_taglist = array();
			foreach ($them as $tag) {
				$_zp_admin_lc_taglist[] = mb_strtolower($tag);
			}
		} else {
			$them = $_zp_admin_ordered_taglist;
		}
		if (is_null($that)) {
			$tags = array();
		} else {
			$tags = $that->getTags();
		}

		if (count($tags) > 0) {
			foreach ($tags as $tag) {
				$tagLC = mb_strtolower($tag);
				$key = array_search($tagLC, $_zp_admin_lc_taglist);
				if ($key !== false) {
					unset($them[$key]);
				}
			}
		}
		if ($resizeable) {
			$tagclass = 'resizeable_tagchecklist';
		} else {
			$tagclass = 'tagchecklist';
		}
		if ($addnew) {
			?>
			<span class="new_tag displayinline" >
				<a href="javascript:addNewTag('<?php echo $postit; ?>');" title="<?php echo gettext('add tag'); ?>">
					<img src="images/add.png" title="<?php echo gettext('add tag'); ?>"/>
				</a>
				<span class="tagSuggestContainer">
					<input class="tagsuggest <?php echo $class; ?> " type="text" value="" name="newtag_<?php echo $postit; ?>" id="newtag_<?php echo $postit; ?>" />
				</span>
			</span>

			<?php
		}
		?>
		<div id="resizable_<?php echo $postit; ?>" class="tag_div">
			<ul id="list_<?php echo $postit; ?>" class="<?php echo $tagclass; ?>">
				<?php
				if ($showCounts) {
					$displaylist = array();
					foreach ($them as $tag) {
						$displaylist[$tag . ' [' . $counts[$tag] . ']'] = $tag;
					}
				} else {
					$displaylist = $them;
				}
				if (count($tags) > 0) {
					generateUnorderedListFromArray($tags, $tags, $postit, false, !$mostused, $showCounts, $class);
					?>
					<li><hr /></li>
					<?php
				}
				generateUnorderedListFromArray(array(), $displaylist, $postit, false, !$mostused, $showCounts, $class);
				?>
			</ul>
		</div>
		<?php
	}
	
	/**
	 * Returns the desired tagsort order (0 for alphabetic, 1 for most used)
	 *
	 * @return int
	 */
	function getTagOrder() {
		if (isset($_REQUEST['tagsort'])) {
			$tagsort = sanitize($_REQUEST['tagsort']);
			setOption('tagsort', (int) ($tagsort && true));
		} else {
			$tagsort = getOption('tagsort');
		}
		return $tagsort;
	}

	/**
 * Process the bulk tags
 *
 * @return array
 */
function bulkTags() {
	$tags = array();
	foreach ($_POST as $key => $value) {
		$key = postIndexDecode($key);
		if ($value && substr($key, 0, 10) == 'mass_tags_') {
			$tags[] = sanitize(substr($key, 10));
		}
	}
	return $tags;
}
