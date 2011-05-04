<?php
/**
 * Use to tweet news articles as they are published
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
	zp_register_filter('admin_head', 'tweetScan');
	zp_register_filter('theme_head', 'tweetScan');
	require_once(getPlugin('tweet_news/twitteroauth.php'));
}

$option_interface = 'tweet_options';

class tweet_options {
	function cacheHeader_options() {
		setOptionDefault('tweet_news_consumer', NULL);
		setOptionDefault('tweet_news_consumer_secret', NULL);
		setOptionDefault('tweet_news_oauth_token', NULL);
		setOptionDefault('tweet_news_oauth_token_secret', NULL);
		setOptionDefault('tweet_news_rescan', 1);
		setOptionDefault('tweet_news_categories_none', NULL);
	}

	function getOptionsSupported() {
		global $_zp_zenpage;
		$catlist = unserialize(getOption('tweet_news_categories'));
		$news_categories = $_zp_zenpage->getAllCategories();
		$catlist = array(gettext('*not categorized*')=>'tweet_news_categories_none');
		foreach ($news_categories as $category) {
			$option = 'tweet_news_categories_'.$category['titlelink'];
			$catlist[$category['title']] = $option;
			setOptionDefault($option, NULL);
		}
		return array(	gettext('Scan pending') => array('key'=>'tweet_news_rescan', 'type'=>OPTION_TYPE_CHECKBOX,
										'order'=>5,
										'desc'=>gettext('<code>tweet_news</code> notices when an article is published. '.
																		'If the article date is in the future, it is put in the <em>to-be-tweeted</em> and tweeted when that date arrives. '.
																		'This option allows you to re-populate that list to the current state of scheduled articles.')),
									gettext('Consumer key') => array('key'=>'tweet_news_consumer', 'type'=>OPTION_TYPE_TEXTBOX,
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
									gettext('Tweet categories') => array('key'=>'tweet_news_categories', 'type'=>OPTION_TYPE_CHECKBOX_UL,
										'order'=>6,
										'checkboxes' => $catlist,
										'desc'=>gettext('Only those categories checked will be Tweeted. <strong>Note:</strong> <em>*not categorized*</em> means those news articles which have no category assigned.')),
									chr(0).'1' => array('key'=>'tweet_news_rescan', 'type'=>OPTION_TYPE_CUSTOM,
										'order'=>0,
										'desc'=>NULL)
									);
	}
	function handleOption($option, $currentValue) {
		switch($option) {
			case 'tweet_news_rescan':
				if (getOption('tweet_news_rescan')) {
					setOption('tweet_news_rescan', 0);
					tweetRepopulate();
				}
				$result = query_full_array('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="tweet_news" AND `aux`="error"');
				if (!empty($result)) {
					$errors = '';
					foreach ($result as $error) {
						$errors .= $error['data'].'<br />';
						query('DELETE FROM'.prefix('plugin_storage').' WHERE `id`='.$error['id']);
					}
					echo '<p class="errorbox">'.$errors.'</p>';
				}
				break;
		}
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
	if ($obj->table == 'news' && $obj->getShow()) {
		$dt = $obj->getDateTime();
		$mycategories = $obj->getCategories();
		$tweet = false;
		if (empty($mycategories)) {
			$tweet = getOption('tweet_news_categories_none');
		} else {
			foreach($mycategories as $cat) {
				if ($tweet = getOption('tweet_news_categories_'.$cat['titlelink'])) {
					break;
				}
			}
		}
		if($tweet && $dt > date('Y-m-d H:i:s')) {
			$result = query_single_row('SELECT * FROM '.prefix('plugin_storage').' WHERE `type`="tweet_news" AND `aux`="pending" AND `data`='.db_quote($obj->getTitlelink()));
			if (!$result) {
				query('INSERT INTO '.prefix('plugin_storage').' (`type`,`aux`,`data`) VALUES ("tweet_news","pending",'.db_quote($obj->getTitlelink()).')');
			}
		} else {	//	tweet it
			require_once(SERVERPATH.'/'.ZENFOLDER.'/template-functions.php');
			require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/zenpage/zenpage-template-functions.php');
			$text = strip_tags($obj->getContent());
			if (strlen($text) > 140) {
				$link = PROTOCOL.'://'.$_SERVER['HTTP_HOST'].getNewsURL($obj->getTitlelink());
				$c = 140 - strlen($link) - 4;	//	allow for ellipsis
				$text = strip_tags(shortenContent($text, $c, '... ')).$link;
			}
			$error = sendTweet($text);
			if ($error) {
				query('INSERT INTO '.prefix('plugin_storage').' (`type`,`aux`,`data`) VALUES ("tweet_news","error",'.db_quote(sprintf(gettext('Error tweeting <code>%1$s</code>: %2$s'),$obj->getTitlelink(),$error)).')');
			}
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
		echo gettext('<p class="messagebox">Scheduled news articles have been noted for tweeting.</p>');
	}
}
?>