<?php

/**
 * Zenpage single news article template functions
 * 
 * @since 1.7 separated from admin-functions.php file
 * 
 * @author Malte Müller (acrylian), Stephen Billard (sbillard)
 * @package zpcore\plugins\zenpage\admin
 */

/**
 * Gets the id of a news article/item
 *
 * @return int
 */
function getNewsID() {
	global $_zp_current_zenpage_news;
	if (!is_null($_zp_current_zenpage_news)) {
		return $_zp_current_zenpage_news->getID();
	}
}

/**
 * Gets the news article title
 *
 * @return string
 */
function getNewsTitle() {
	global $_zp_current_zenpage_news;
	if (!is_null($_zp_current_zenpage_news)) {
		return $_zp_current_zenpage_news->getTitle();
	}
}

/**
 * prints the news article title
 *
 * @param string $before insert if you want to use for the breadcrumb navigation or in the html title tag
 */
function printNewsTitle($before = '') {
	if ($title = getNewsTitle()) {
		if ($before) {
			echo '<span class="beforetext">' . html_encode($before) . '</span>';
		}
		echo html_encode($title);
	}
}

/**
 * Returns the raw title of a news article.
 *
 *
 * @return string
 */
function getBareNewsTitle() {
	return getBare(getNewsTitle());
}

function printBareNewsTitle() {
	echo html_encode(getBareNewsTitle());
}

/**
 * Returns the link (url) of the current news article.
 * or of the titlelink passed if not empty
 *
 * @param string $titlelink
 * @return string
 */
function getNewsURL($titlelink = NULL) {
	global $_zp_current_zenpage_news;
	if (empty($titlelink)) {
		$obj = $_zp_current_zenpage_news;
	} else {
		$obj = new ZenpageNews($titlelink);
	}
	if (!is_null($obj))
		return $obj->getLink();
}

/**
 * Prints the title of a news article as a full html link
 *
 * @param string $before insert what you want to be show before the titlelink.
 */
function printNewsURL($before = '') {
	if (getNewsTitle()) {
		if ($before) {
			$before = '<span class="beforetext">' . html_encode($before) . '</span>';
		}
		echo "<a href=\"" . html_encode(getNewsURL()) . "\" title=\"" . getBareNewsTitle() . "\">" . $before . html_encodeTagged(getNewsTitle()) . "</a>";
	}
}

/**
 * Gets the content of a news article
 *
 * If using the CombiNews feature this returns the description for gallery items (see printNewsContent for more)
 *
 * @param int $shorten The optional length of the content for the news list for example, will override the plugin option setting if set, "" (empty) for full content (not used for image descriptions!)
 * @param string $shortenindicator The placeholder to mark the shortening (e.g."(...)"). If empty the Zenpage option for this is used.
 * @param string $readmore The text for the "read more" link. If empty the term set in Zenpage option is used.
 *
 * @return string
 */
function getNewsContent($shorten = false, $shortenindicator = NULL, $readmore = NULL) {
	global $_zp_current_image, $_zp_gallery, $_zp_current_zenpage_news, $_zp_page;
	if (!$_zp_current_zenpage_news->checkAccess()) {
		return '<p>' . gettext('<em>This entry belongs to a protected category.</em>') . '</p>';
	}
	$excerptbreak = false;
	if (!$shorten && !is_NewsArticle()) {
		$shorten = ZP_SHORTEN_LENGTH;
	}

	$articlecontent = $_zp_current_zenpage_news->getContent();
	if (!is_NewsArticle()) {
		if ($_zp_current_zenpage_news->getTruncation()) {
			$shorten = true;
		}
		$articlecontent = getContentShorten($articlecontent, $shorten, $shortenindicator, $readmore, $_zp_current_zenpage_news->getLink());
	}

	return $articlecontent;
}

/**
 * Prints the news article content. Note: TinyMCE used by Zenpage for news articles may already add a surrounding <p></p> to the content.
 *
 * If using the CombiNews feature this prints the thumbnail or sized image for a gallery item.
 * If using the 'CombiNews sized image' mode it shows movies directly and the description below.
 *
 * @param int $shorten $shorten The lengths of the content for the news main page for example (only for video/audio descriptions, not for normal image descriptions)
 * @param string $shortenindicator The placeholder to mark the shortening (e.g."(...)"). If empty the Zenpage option for this is used.
 * @param string $readmore The text for the "read more" link. If empty the term set in Zenpage option is used.
 */
function printNewsContent($shorten = false, $shortenindicator = NULL, $readmore = NULL) {
	global $_zp_current_zenpage_news;
	$content = filter::applyFilter('articlecontent_html', getNewsContent($shorten, $shortenindicator, $readmore), $_zp_current_zenpage_news);
	echo html_encodeTagged($content);
}

/**
 * Shorten the content of any type of item and add the shorten indicator and readmore link
 * set on the Zenpage plugin options. Helper function for getNewsContent() but usage of course not limited to that.
 * If there is nothing to shorten the content passed.
 *
 * The read more link is wrapped within <p class="readmorelink"></p>.
 *
 * @param string $text The text content to be shortenend.
 * @param mixed $shorten The lenght the content should be shortened. Set to true for shorten to pagebreak zero or false for no shortening
 * @param string $shortenindicator The placeholder to mark the shortening (e.g."(...)"). If empty the Zenpage option for this is used.
 * @param string $readmore The text for the "read more" link. If empty the term set in Zenpage option is used.
 * @param string $readmoreurl The url the read more link should point to
 */
function getContentShorten($text, $shorten, $shortenindicator = NULL, $readmore = NULL, $readmoreurl = NULL) {
	$readmorelink = '';
	if (is_null($shortenindicator)) {
		$shortenindicator = ZP_SHORTENINDICATOR;
	}
	if (is_null($readmore)) {
		$readmore = i18n::getLanguageString(ZP_READ_MORE);
	}
	if (!is_null($readmoreurl)) {
		$readmorelink = '<p class="readmorelink"><a href="' . html_encode($readmoreurl) . '" title="' . html_encode($readmore) . '">' . html_encode($readmore) . '</a></p>';
	}

	if (!$shorten && !is_NewsArticle()) {
		$shorten = ZP_SHORTEN_LENGTH;
	}
	$contentlenght = mb_strlen($text);
	if (!empty($shorten) && ($contentlenght > (int) $shorten)) {
		if (stristr($text, '<!-- pagebreak -->')) {
			$array = explode('<!-- pagebreak -->', $text);
			$newtext = array_shift($array);
			while (!empty($array) && (mb_strlen($newtext) + mb_strlen($array[0])) < $shorten) { //	find the last break within shorten
				$newtext .= array_shift($array);
			}
			if ($shortenindicator && empty($array) || ($array[0] == '</p>' || trim($array[0]) == '')) { //	page break was at end of article
				$text = shortenContent($newtext, $shorten, '') . $readmorelink;
			} else {
				$text = shortenContent($newtext, $shorten, $shortenindicator, true) . $readmorelink;
			}
		} else {
			if (!is_bool($shorten)) {
				$newtext = shortenContent($text, $shorten, $shortenindicator);
				if ($newtext != $text) {
					$text = $newtext . $readmorelink;
				}
			}
		}
	}
	return $text;
}

/**
 * Gets the extracontent of a news article if in single news articles view or returns FALSE
 *
 * @return string
 */
function getNewsExtraContent() {
	global $_zp_current_zenpage_news;
	if (is_News()) {
		$extracontent = $_zp_current_zenpage_news->getExtraContent();
		return $extracontent;
	} else {
		return FALSE;
	}
}

/**
 * Prints the extracontent of a news article if in single news articles view
 *
 * @return string
 */
function printNewsExtraContent() {
	echo getNewsExtraContent();
}

/**
 * Returns the text for the read more link for news articles or gallery items if in CombiNews mode
 *
 * @return string
 */
function getNewsReadMore() {
	global $_zp_current_zenpage_news;
	$readmore = i18n::getLanguageString(ZP_READ_MORE);
	return $readmore;
}

/**
 * Gets the custom data field of the curent news article
 *
 * @return string
 */
function getNewsCustomData() {
	global $_zp_current_zenpage_news;
	if (!is_null($_zp_current_zenpage_news)) {
		return $_zp_current_zenpage_news->getCustomData();
	}
}

/**
 * Prints the custom data field of the curent news article
 *
 */
function printNewsCustomData() {
	echo getNewsCustomData();
}



/**
 * Gets the date of the current news article
 *
 * @return string
 */
function getNewsDate() {
	global $_zp_current_zenpage_news;
	if (!is_null($_zp_current_zenpage_news)) {
		$d = $_zp_current_zenpage_news->getDateTime();
		return zpFormattedDate(DATETIME_DISPLAYFORMAT, strtotime($d));
	}
	return false;
}

/**
 * Prints the date of the current news article
 *
 * @return string
 */
function printNewsDate() {
	echo html_encode(getNewsDate());
}

/**
 * Returns the title and the titlelink of the next or previous article in single news article pagination as an array
 * Returns false if there is none (or option is empty)
 *
 * NOTE: This is not available if using the CombiNews feature
 *
 * @param string $option "prev" or "next"
 *
 * @return mixed
 */
function getNextPrevNews($option = '') {
	global $_zp_zenpage, $_zp_current_zenpage_news;
	if (!empty($option)) {
		switch ($option) {
			case "prev":
				if ($article = $_zp_current_zenpage_news->getPrevArticle()) {
					return array("link" => $article->getLink(), "title" => $article->getTitle());
				}
				break;
			case "next":
				if ($article = $_zp_current_zenpage_news->getNextArticle()) {
					return array("link" => $article->getLink(), "title" => $article->getTitle());
				}
				break;
		}
	}
	return false;
}

/**
 * Returns the title and the titlelink of the previous article in single news article pagination as an array
 * Returns false if there is none (or option is empty)
 *
 * NOTE: This is not available if using the CombiNews feature
 *
 * @return mixed
 */
function getPrevNewsURL() {
	return getNextPrevNews("prev");
}

/**
 * Returns the title and the titlelink of the next article in single news article pagination as an array
 * Returns false if there is none (or option is empty)
 *
 * NOTE: This is not available if using the CombiNews feature
 *
 * @return mixed
 */
function getNextNewsURL() {
	return getNextPrevNews("next");
}

/**
 * Prints the link of the next article in single news article pagination if available
 *
 * NOTE: This is not available if using the CombiNews feature
 *
 * @param string $next If you want to show something with the title of the article like a symbol
 * @return string
 */
function printNextNewsLink($next = " »") {
	$article_url = getNextPrevNews("next");
	if ($article_url && array_key_exists('link', $article_url) && $article_url['link'] != "") {
		echo "<a href=\"" . html_encode($article_url['link']) . "\" title=\"" . html_encode(getBare($article_url['title'])) . "\">" . $article_url['title'] . "</a> " . html_encode($next);
	}
}

/**
 * Prints the link of the previous article in single news article pagination if available
 *
 * NOTE: This is not available if using the CombiNews feature
 *
 * @param string $next If you want to show something with the title of the article like a symbol
 * @return string
 */
function printPrevNewsLink($prev = "« ") {
	$article_url = getNextPrevNews("prev");
	if ($article_url && array_key_exists('link', $article_url) && $article_url['link'] != "") {
		echo html_encode($prev) . " <a href=\"" . html_encode($article_url['link']) . "\" title=\"" . html_encode(getBare($article_url['title'])) . "\">" . $article_url['title'] . "</a>";
	}
}