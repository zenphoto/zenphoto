<?php

class Combi extends Zenpage {

	function getOldCombiNews() {
		return $this->getCombiNews(NULL, NULL, 'published');
	}

}

global $plugin_is_filter;
enableExtension('galleryArticles', $plugin_is_filter);

$obj = new Combi();
$combi = $obj->getOldCombiNews();
$cat = new ZenpageCategory('combiNews', true);
$cat->setTitle(gettext('combiNews'));
$cat->setDesc(gettext('Auto category for ported combi-news articles.'));
$cat->save();
foreach ($combi as $article) {
	switch ($article['type']) {
		case 'images':
			$obj = newImage(NULL, array('folder' => $article['albumname'], 'filename' => $article['titlelink']), false);
			if ($obj->exists) {
				$obj->setPublishDate($article['date']);
				self::publishArticle($obj, 'combiNews');
			}
			break;
		case 'albums':
			$obj = newAlbum($article['albumname'], false);
			if ($obj->exists) {
				$obj->setPublishDate($article['date']);
				self::publishArticle($obj, 'combiNews');
			}
			break;
	}
}
purgeOption('zenpage_combinews');
purgeOption('combinews-customtitle');
purgeOption('combinews-customtitle-imagetitles');
purgeOption("zenpage_combinews_sortorder");
purgeOption('zenpage_combinews_imagesize');
purgeOption('combinews-thumbnail-width');
purgeOption('combinews-thumbnail-height');
purgeOption('combinews-thumbnail-cropwidth');
purgeOption('combinews-thumbnail-cropheight');
purgeOption('combinews-thumbnail-cropx');
purgeOption('combinews-thumbnail-cropy');
purgeOption('zenpage_combinews_mode');
?>