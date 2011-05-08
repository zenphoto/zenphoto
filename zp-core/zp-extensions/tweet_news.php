<?php
/**
 * Use to tweet new objects as they are published
 *
 */
$plugin_is_filter = 9|THEME_PLUGIN|ADMIN_PLUGIN;
$plugin_description = gettext('Tweet news articles when published.');
$plugin_author = "Stephen Billard (sbillard)";
$plugin_version = '1.4.1';
$plugin_disable = (version_compare(PHP_VERSION, '5.2.0') != 1) ? gettext('PHP version 5.2 or greater is required.') : false;
if ($plugin_disable) {
	setOption('zp_plugin_tweet_news',0);
} else {
	$option_interface = 'tweet_options';
	zp_register_filter('show_change', 'tweetNewsArticle');
	zp_register_filter('new_image', 'tweetNewsArticle');
	zp_register_filter('admin_head', 'tweetScan');
	zp_register_filter('theme_head', 'tweetScan');
	require_once(getPlugin('tweet_news/twitteroauth.php'));
}

$option_interface = 'tweet_options';

class tweet_options {

	function tweet_options() {
		setOptionDefault('tweet_news_consumer', NULL);
		setOptionDefault('tweet_news_consumer_secret', NULL);
		setOptionDefault('tweet_news_oauth_token', NULL);
		setOptionDefault('tweet_news_oauth_token_secret', NULL);
		setOptionDefault('tweet_news_rescan', 1);
		setOptionDefault('tweet_news_categories_none', NULL);
		setOptionDefault('tweet_news_images', NULL);
		setOptionDefault('tweet_news_albums', NULL);
		setOptionDefault('tweet_news_news', 1);
		setOptionDefault('tweet_news_protected', NULL);
	}

	function getOptionsSupported() {
		global $_zp_zenpage;
		$options = array(	gettext('Consumer key') => array('key'=>'tweet_news_consumer', 'type'=>OPTION_TYPE_TEXTBOX,
																												'order'=>2,
																												'desc'=>gettext('This <code>tweet_news</code> app for this site needs a <em>consumer key</em>, a <em>consumer key secret</em>, an <em>access token</em>, and an <em>access token secret</em>.').'<p class="notebox">'. gettext('Get these from <a href="http://dev.twitter.com/">Twitter developers</a>').'</p>'),
											gettext('Secret') => array('key'=>'tweet_news_consumer_secret', 'type'=>OPTION_TYPE_TEXTBOX,
																									'order'=>3,
																									'desc'=>gettext('The <em>secret</em> associated with your <em>consumer key</em>.')),
											gettext('Access token') => array('key'=>'tweet_news_oauth_token', 'type'=>OPTION_TYPE_TEXTBOX,
																												'order'=>4,
																												'desc'=>gettext('The application <em>oauth_token</em> token.')),
											gettext('Access token secret') => array('key'=>'tweet_news_oauth_token_secret', 'type'=>OPTION_TYPE_TEXTBOX,
																															'order'=>5,
																															'desc'=>gettext('The application <em>oauth_token</em> secret.')),
											gettext('Protected albums') => array('key'=>'tweet_news_protected', 'type'=>OPTION_TYPE_CHECKBOX,
																														'order'=>7,
																														'desc'=>gettext('If checked, protected items will be tweeted. <strong>Note:</strong> followers will need the password to visit the tweeted link.'))
										);
		$note = '';
		$list = array('<em>'.gettext('Albums').'</em>'=>'tweet_news_albums', '<em>'.gettext('Images').'</em>'=>'tweet_news_images');
		if (getOption('zp_plugin_zenpage')) {
			$list['<em>'.gettext('News').'</em>'] = 'tweet_news_news';
			$options[gettext('Scan pending')] = array('key'=>'tweet_news_rescan', 'type'=>OPTION_TYPE_CHECKBOX,
																								'order'=>8,
																								'desc'=>gettext('<code>tweet_news</code> notices when an article is published. '.
																																'If the article date is in the future, it is put in the <em>to-be-tweeted</em> and tweeted when that date arrives. '.
																																'This option allows you to re-populate that list to the current state of scheduled articles.'));


		} else {
			setOption('tweet_news_news', 0);
		}
		$options[gettext('Tweet')] = array('key'=>'tweet_news_items', 'type'=>OPTION_TYPE_CHECKBOX_ARRAY,
																			'order'=>6,
																			'checkboxes' => $list,
																			'desc'=>gettext('If an <em>type</em> is checked, a Tweet will be made when an object of that <em>type</em> is published.'));

		if (getOption('tweet_news_news')) {
			$catlist = unserialize(getOption('tweet_news_categories'));
			$news_categories = $_zp_zenpage->getAllCategories();
			$catlist = array(gettext('*not categorized*')=>'tweet_news_categories_none');
			foreach ($news_categories as $category) {
				$option = 'tweet_news_categories_'.$category['titlelink'];
				$catlist[$category['title']] = $option;
				setOptionDefault($option, NULL);
			}
			$options[gettext('News categories')] = array('key'=>'tweet_news_categories', 'type'=>OPTION_TYPE_CHECKBOX_UL,
																													'order'=>6,
																													'checkboxes' => $catlist,
																													'desc'=>gettext('Only those <em>news categories</em> checked will be Tweeted. <strong>Note:</strong> <em>*not categorized*</em> means those news articles which have no category assigned.'));
		}
		if (getOption('tweet_news_rescan')) {
			setOption('tweet_news_rescan', 0);
			$note = tweetRepopulate();
		}
		$result = query_full_array('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="tweet_news" AND `aux`="error"');
		if (!empty($result)) {
			$errors = '';
			foreach ($result as $error) {
				$errors .= $error['data'].'<br />';
				query('DELETE FROM'.prefix('plugin_storage').' WHERE `id`='.$error['id']);
			}
			$note .= '<p class="errorbox">'.$errors.'</p>';
		}
		if ($note) {
			$options['note'] = array('key'=>'tweet_news_rescan', 'type'=>OPTION_TYPE_NOTE,
															'order'=>0,
															'desc'=>$note);
		}

		return $options;
	}
	function handleOption($option, $currentValue) {
	}
}

function sendTweet($status) {
	global $tweet;
	if (!is_object($tweet)) {
		$consumerKey = getOption('tweet_news_consumer');
		$consumerSecret = getOption('tweet_news_consumer_secret');
		$OAuthToken = getOption('tweet_news_oauth_token');
		$OAuthSecret = getOption('tweet_news_oauth_token_secret');
		$tweet = new TwitterOAuth($consumerKey, $consumerSecret, $OAuthToken, $OAuthSecret);
	}
	$response = $tweet->post('statuses/update', array('status' => $status));
	if (isset($response->error)) {
		return $response->error;
	}
	return false;
}

function tweetNewsArticle($obj) {
	global $_zp_UTF8;
	if ($obj->getShow()) {
		switch ($type = $obj->table) {
			case 'news':
				if (!getOption('tweet_news_protected') && $obj->inProtectedCategory(true)) {
					break;
				}
				$tweet = false;
				$mycategories = $obj->getCategories();
				if (empty($mycategories)) {
					$tweet = getOption('tweet_news_categories_none');
				} else {
					foreach($mycategories as $cat) {
						if ($tweet = getOption('tweet_news_categories_'.$cat['titlelink'])) {
							break;
						}
					}
				}

				$dt = $obj->getDateTime();
				if($tweet && $dt > date('Y-m-d H:i:s')) {
					$result = query_single_row('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="tweet_news" AND `aux`="pending" AND `data`='.db_quote($obj->getTitlelink()));
					if (!$result) {
						query('INSERT INTO '.prefix('plugin_storage').' (`type`,`aux`,`data`) VALUES ("tweet_news","pending",'.db_quote($obj->getTitlelink()).')');
					}
				} else {	//	tweet it
					$text = trim(strip_tags($obj->getContent()));
					if (strlen($text) > 140) {
						require_once(SERVERPATH.'/'.ZENFOLDER.'/template-functions.php');
						$title = trim(strip_tags($obj->getTitle()));
						$link = getTinyURL($obj);
						$c = 140 - strlen($link) - 4;	//	allow for ellipsis
						if ($_zp_UTF8->strlen($title) >= ($c - 25)) {	//	not much point in the body if shorter than 25
							$text = trim(strip_tags(shortenContent($title, $c, '... '))).$link;
						} else {
							$c = $c - $_zp_UTF8->strlen($title) - 1;
							$text = $title.' '.trim(strip_tags(shortenContent($text, $c, '... '))).$link;
						}
					}
					$error = sendTweet($text);
					if ($error) {
						query('INSERT INTO '.prefix('plugin_storage').' (`type`,`aux`,`data`) VALUES ("tweet_news","error",'.db_quote(sprintf(gettext('Error tweeting <code>%1$s</code>: %2$s'),$obj->getTitlelink(),$error)).')');
					}
				}
				break;
			case 'albums':
				if (!getOption('tweet_news_protected') && isProtectedAlbum($obj)) {
					break;
				}
			case 'images':
				if (getOption('tweet_news_'.$type)) {
					if ($type=='images') {
						if (!getOption('tweet_news_protected') && isProtectedAlbum($obj->album)) {
							break;
						}
						$text = sprintf(gettext('New image %1$s in %2$s '),trim(strip_tags($obj->getTitle())),trim(strip_tags($obj->album->getTitle())));
					} else {
						$text = sprintf(gettext('New album: %s '),trim(strip_tags($obj->getTitle())));
					}
					$link = getTinyURL($obj);
					if ($_zp_UTF8->strlen($text.$link) > 140) {
						require_once(SERVERPATH.'/'.ZENFOLDER.'/template-functions.php');
						$c = 140 - strlen($link) - 4;	//	allow for ellipsis
						$text = trim(strip_tags(shortenContent($text, $c, '... '))).$link;
					} else {
						$text = $text.$link;
					}
					$error = sendTweet($text);
					if ($error) {
						query('INSERT INTO '.prefix('plugin_storage').' (`type`,`aux`,`data`) VALUES ("tweet_news","error",'.db_quote(sprintf(gettext('Error tweeting <code>%1$s</code>: %2$s'),$obj->getTitlelink(),$error)).')');
					}
				}
				break;
		}
	}
	return $obj;
}


function tweetScan() {
	$result = query_full_array('SELECT * FROM '.prefix('news').' AS news,'.prefix('plugin_storage').' AS store WHERE store.type="tweet_news" AND store.aux="pending" AND store.data = news.titlelink AND news.date <= '.db_quote(date('Y-m-d H:i:s')));
	if ($result) {
		foreach ($result as $article) {
			query('DELETE FROM '.prefix('plugin_storage').' WHERE `type`="tweet_news" AND `aux`="pending" AND `data`='.db_quote($article['titlelink']));
			$news = new ZenpageNews($article['titlelink']);
			tweetNewsArticle($news);
		}
	}
}

function tweetRepopulate() {
	query('DELETE FROM '.prefix('plugin_storage').' WHERE `type`="tweet_news" AND `aux`="pending"');
	$result = query_full_array('SELECT * FROM '.prefix('news').' WHERE `show`=1 AND `date`>'.db_quote(date('Y-m-d H:i:s')));
	if ($result) {
		foreach ($result as $pending) {
			query('INSERT INTO '.prefix('plugin_storage').' (`type`,`aux`,`data`) VALUES ("tweet_news","pending",'.db_quote($pending['titlelink']).')');
		}
		return '<p class="messagebox">'.gettext('Scheduled news articles have been noted for tweeting.</p>').'</p>';
	}
	return '<p class="messagebox">'.gettext('No scheduled news articles found.</p>').'</p>';
}
?>