<?php

$fail = '';
$search = new SearchEngine();
$searchfields = array();
foreach ($_POST as $key => $value) {
	if (strpos($key, 'SEARCH_') !== false) {
		$searchfields[] = substr(sanitize(postIndexDecode($key)), 7);
	}
}
setOption('search_fields', implode(',', $searchfields));
setOption('search_fieldsselector_enabled', (int) isset($_POST['search_fieldsselector_enabled']));
setOption('search_cache_duration', sanitize_numeric($_POST['search_cache_duration']));
$notify = processCredentials('search');
setOption('exact_tag_match', sanitize($_POST['tag_match']));
setOption('exact_string_match', sanitize($_POST['string_match']));
setOption('search_space_is', sanitize($_POST['search_space_is']));
setOption('search_no_albums', (int) isset($_POST['search_no_albums']));
setOption('search_no_images', (int) isset($_POST['search_no_images']));
setOption('search_no_pages', (int) isset($_POST['search_no_pages']));
setOption('search_no_news', (int) isset($_POST['search_no_news']));
setOption('search_within', (int) ($_POST['search_within'] && true));

// image default sort order + direction
$sorttype = strtolower(sanitize($_POST['search_image_sort_type'], 3));
if ($sorttype == 'custom') {
	$sorttype = unquote(strtolower(sanitize($_POST['custom_image_sort'], 3)));
}
setOption('search_image_sort_type', $sorttype);
if ($sorttype == 'random') {
	setOption('search_image_sort_direction', 0);
} else {
	if (empty($sorttype)) {
		$direction = 0;
	} else {
		$direction = isset($_POST['search_image_sort_direction']);
	}
	setOption('search_image_sort_direction', $direction);
}

// album default sort order + direction
$sorttype = strtolower(sanitize($_POST['search_album_sort_type'], 3));
if ($sorttype == 'custom') {
	$sorttype = strtolower(sanitize($_POST['custom_album_sort'], 3));
}
setOption('search_album_sort_type', $sorttype);
if ($sorttype == 'random') {
	setOption('search_album_sort_direction', 0);
} else {
	setOption('search_album_sort_direction', isset($_POST['search_album_sort_direction']));
}

if (ZP_NEWS_ENABLED) {
	// Zenpage news articles default sort order + direction
	$sorttype = strtolower(sanitize($_POST['search_newsarticle_sort_type'], 3));
	if ($sorttype == 'custom') {
		$sorttype = strtolower(sanitize($_POST['custom_newsarticle_sort'], 3));
	}
	setOption('search_newsarticle_sort_type', $sorttype);
	if ($sorttype == 'random') {
		setOption('search_newsarticle_sort_direction', 0);
	} else {
		setOption('search_newsarticle_sort_direction', isset($_POST['search_newsarticle_sort_direction']));
	}
}

if (ZP_PAGES_ENABLED) {
	// Zenpage pages default sort order + direction
	$sorttype = strtolower(sanitize($_POST['search_page_sort_type'], 3));
	if ($sorttype == 'custom')
		$sorttype = strtolower(sanitize($_POST['custom_page_sort'], 3));
	setOption('search_page_sort_type', $sorttype);
	if ($sorttype == 'random') {
		setOption('search_page_sort_direction', 0);
	} else {
		setOption('search_page_sort_direction', isset($_POST['search_page_sort_direction']));
	}
}
$returntab = "&tab=search";
