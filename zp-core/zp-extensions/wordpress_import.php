<?php
/**
 *
 * This imports Wordpress pages, posts, categories and comments to Zenpage
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage utilities
 */
if (defined('OFFSET_PATH')) {
	$plugin_is_filter = 5 | ADMIN_PLUGIN;
	$plugin_description = gettext("Import Wordpress pages, posts, categories, and comments to Zenpage.");
	$plugin_author = "Malte Müller (acrylian)";


	zp_register_filter('admin_utilities_buttons', 'wordpress_import_button');

	function wordpress_import_button($buttons) {
		$buttons[] = array(
						'category'		 => gettext('Admin'),
						'enable'			 => true,
						'button_text'	 => gettext('Wordpress Importer'),
						'formname'		 => 'wordpress_import.php',
						'action'			 => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/wordpress_import.php',
						'icon'				 => WEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/wordpress_import/wpmini-blue.png',
						'title'				 => gettext('An importer for Wordpress posts and pages to Zenpage.'),
						'alt'					 => '',
						'hidden'			 => '',
						'rights'			 => ADMIN_RIGHTS
		);
		return $buttons;
	}

} else {

	define('OFFSET_PATH', 3);

	require_once(dirname(dirname(__FILE__)) . '/admin-globals.php');

	if (extensionEnabled('zenpage')) {
		require_once(dirname(dirname(__FILE__)) . '/' . PLUGIN_FOLDER . '/zenpage/zenpage-admin-functions.php');
	}

	admin_securityChecks(NULL, currentRelativeURL());

	if (isset($_REQUEST['dbname']) || isset($_REQUEST['dbuser']) || isset($_REQUEST['dbpass']) || isset($_REQUEST['dbhost'])) {
		XSRFdefender('wordpress');
	}

	$webpath = WEBPATH . '/' . ZENFOLDER . '/';

	// some extra functions
	function wp_query_full_array($sql, $wpconnection) {
		$result = mysql_query($sql, $wpconnection) or die(gettext("Query failed : ") . mysql_error());
		if ($result) {
			$allrows = array();
			while ($row = mysql_fetch_assoc($result))
				$allrows[] = $row;
			return $allrows;
		} else {
			return false;
		}
	}

	function wp_prefix($tablename, $wp_prefix) {
		return '`' . $wp_prefix . $tablename . '`';
	}

	function wpimport_TryAgainError($message) {
		return '<p class="import-error">' . $message . '<br /><a href="wordpress_import.php">' . gettext('Please try again') . '</a>
		</p>';
	}

	$metaURL = '';
	if (isset($_REQUEST['dbname']) || isset($_REQUEST['dbuser']) || isset($_REQUEST['dbpass']) || isset($_REQUEST['dbhost'])) {
		// Wordpres DB connection
		$wp_dbname = sanitize($_REQUEST['dbname']);
		$wp_dbbuser = sanitize($_REQUEST['dbuser']);
		$wp_dbpassword = sanitize($_REQUEST['dbpass']);
		$wp_dbhost = sanitize($_REQUEST['dbhost']);
		$wp_prefix = addslashes(sanitize($_REQUEST['tableprefix']));
		$dbinfo_incomplete = '';
		$db_noconnection = '';
		$db_notselected = '';
		$missingmessage = '<span style="color: red">' . gettext('Missing') . '</span>';
		if (empty($wp_dbname) || empty($wp_dbbuser) || empty($wp_dbpassword) || empty($wp_dbhost)) {
			if (empty($wp_dbname))
				$wp_dbname = $missingmessage;
			if (empty($wp_dbbuser))
				$wp_dbbuser = $missingmessage;
			if (empty($wp_dbpassword))
				$wp_dbpassword = $missingmessage;
			if (empty($wp_dbhost))
				$wp_dbhost = $missingmessage;
		}
		$dbinfo = '
		<h2>' . gettext('Wordpress-Database info') . '</h2>
			<ul>
				<li>' . gettext('Database name') . ': <strong>' . $wp_dbname . '</strong></li>
				<li>' . gettext('Database user') . ': <strong>' . $wp_dbbuser . '</strong></li>
				<li>' . gettext('Database password') . ': <strong>' . $wp_dbpassword . '</strong></li>
				<li>' . gettext('Database host') . ': <strong>' . $wp_dbhost . '</strong></li>
				<li>' . gettext('Database table prefix') . '>: <strong>' . $wp_prefix . '</strong></li>
			</ul>
			';
		if (empty($wp_dbname) || empty($wp_dbbuser) || empty($wp_dbpassword) || empty($wp_dbhost)) {
			$dbinfo_incomplete = wpimport_TryAgainError($message);
		}
		$wpdbconnection = @mysql_connect($wp_dbhost, $wp_dbbuser, $wp_dbpassword, true); // open 2nd connection to Wordpress additionally to the existing Zenphoto connection
		mysql_query("SET NAMES 'utf8'");
		@mysql_query('SET SESSION sql_mode="";');
		if (!$wpdbconnection) {
			$db_noconnection = wpimport_TryAgainError(gettext('<strong>ERROR:</strong> Could not connect to the Wordpress database - Query failed : ') . mysql_error());
		}
		if (!@mysql_select_db($wp_dbname, $wpdbconnection)) {
			$db_notselected = wpimport_TryAgainError(gettext('<strong>ERROR:</strong> Wordpress database could not be selected - Query failed : ') . mysql_error());
		}

		/*		 * *********************************
		 * getting all Wordpress categories
		 * ********************************** */
		$catinfo = '';
		if (!isset($_GET['refresh'])) {
			$cats = wp_query_full_array("SELECT * FROM " . wp_prefix('terms', $wp_prefix) . " as terms, " . wp_prefix('term_taxonomy', $wp_prefix) . " as tax WHERE tax.taxonomy = 'category' AND terms.term_id = tax.term_id", $wpdbconnection);
			//echo "<li><strong>Categories</strong>: <pre>"; print_r($cats); echo "</pre></li>"; // for debugging
			debugLogVar('Wordpress import - All Categories', $cats);

			//Add categories to Zenphoto database
			if ($cats) {
				foreach ($cats as $cat) {
					$cattitlelink = $cat['slug'];
					$cattitle = $_zp_UTF8->convert($cat['name']);
					//$catdesc = $_zp_UTF8->convert($cat['description']);
					if (getcheckboxState('convertlinefeeds')) {
						$catdesc = nl2br($catdesc);
					}
					if (query("INSERT INTO " . prefix('news_categories') . " (titlelink, title, permalink) VALUES (" . db_quote($cattitlelink) . ", " . db_quote($cattitle) . ", '1')", false)) {
						$catinfo .= '<li class="import-success">' . sprintf(gettext("Category <em>%s</em> added"), $cat['name']) . '</li>';
					} else {
						$catinfo .= '<li class="import-exists">' . sprintf(gettext("A category with the title/titlelink <em>%s</em> already exists!"), $cat['name']) . '</li>';
					}
				}
			} else {
				$catinfo .= '<li class="import-nothing">' . gettext('No categories to import.') . '</li>';
			}

			/*			 * *********************************
			 * getting all Wordpress tags
			 * ********************************** */
			$taginfo = '';
			$tags = wp_query_full_array("SELECT * FROM " . wp_prefix('terms', $wp_prefix) . " as terms, " . wp_prefix('term_taxonomy', $wp_prefix) . " as tax WHERE tax.taxonomy = 'post_tag' AND terms.term_id = tax.term_id", $wpdbconnection);
			//echo "<li><strong>Tags</strong>: <pre>"; print_r($tags); echo "</pre></li>"; // for debugging
			debugLogVar('Wordpress import - Tags import', $tags);

			//Add tags to Zenphoto database
			if ($tags) {
				foreach ($tags as $tag) {
					if (query("INSERT INTO " . prefix('tags') . " (name) VALUES (" . db_quote($tag['slug']) . ")", false)) {
						$taginfo .= '<li class="import-success">' . sprintf(gettext("Tag <em>%s</em> added"), $tag['name']) . '</li>';
					} else {
						$taginfo .= '<li class="import-exists">' . sprintf(gettext("A tag with the title/titlelink <em>%s</em> already exists!"), $tag['name']) . '</li>';
					}
				}
			} else {
				$taginfo .= '<li class="import-nothing">' . gettext('No tags to import.') . '</li>';
			}
		}
		$postinfo = '';
		$postcount = '';
		if (!isset($_GET['refresh'])) {
			$limit = ' LIMIT 0,10';
		} else {
			$refresh = sanitize_numeric($_GET['refresh']);
			$limit = ' LIMIT ' . ($refresh) . ',10';
			$postcount = $refresh;
		}
		/*		 * *********************************
		 * get wp posts and pages
		 * ********************************** */
		$posttotal = mysql_query("
			SELECT COUNT(*)
			FROM " . wp_prefix('posts', $wp_prefix) . "
			WHERE (post_type = 'post' OR post_type = 'page') AND (post_status = 'publish' OR post_status = 'draft')
		", $wpdbconnection);
		$row = db_fetch_row($posttotal);
		$posttotalcount = $row[0];

		$posts = wp_query_full_array("
		SELECT
		id,
		post_title as title,
		post_name as titlelink,
		post_content as content,
		post_date as date,
		post_modified as lastchange,
		post_status as `show`,
		post_type as type
		FROM " . wp_prefix('posts', $wp_prefix) . "
		WHERE (post_type = 'post' OR post_type = 'page') AND (post_status = 'publish' OR post_status = 'draft')
		ORDER BY post_date DESC" . $limit, $wpdbconnection);
		if ($posts) {
			//echo "Posts<br /><pre>"; print_r($posts); echo "</pre>"; // for debugging
			foreach ($posts as $post) {
				//echo "<li><strong>".$post['title']."</strong> (id: ".$post['id']." / type: ".$post['type']." / date: ".$post['date'].")<br />";
				debugLogVar('Wordpress import - Import post: "' . $post['title'] . '" (' . $post['type'] . ')', $posts);
				if ($post['show'] == "publish") {
					$show = 1;
				} else {
					$show = 0;
				}
				$post['title'] = $_zp_UTF8->convert($post['title']);
				$titlelink = $post['titlelink'];
				$post['content'] = $_zp_UTF8->convert($post['content']);
				if (getcheckboxState('convertlinefeeds')) {
					$post['content'] = nl2br($post['content']);
				}
				$post['date'] = $post['date'];
				$post['lastchange'] = $post['lastchange'];
				$post['type'] = $post['type'];
				switch ($post['type']) {
					case 'post':
						//Add the post to Zenphoto database as Zenpage article
						if (query("INSERT INTO " . prefix('news') . " (title,titlelink,content,date,lastchange,`show`,permalink) VALUES (" . db_quote($post['title']) . "," . db_quote($titlelink) . "," . db_quote($post['content']) . "," . db_quote($post['date']) . "," . db_quote($post['lastchange']) . "," . $show . ",1)", false)) {
							$postinfo .= '<li class="import-success">' . sprintf(gettext('%1$s <em>%2$s</em> added'), $post['type'], $post['title']);
						} else {
							$postinfo .= '<li class="import-exists">' . sprintf(gettext('%1$s with the title/titlelink <em>%2$s</em> already exists!'), $post['type'], $post['title']);
						}
						// Get new id of the article
						$newarticle = new ZenpageNews($titlelink, true);
						$newarticleid = $newarticle->getID();
						// getting the categories and tags assigned to this post (Wordpress pages do not have tags or categories
						$termrelations = wp_query_full_array("
							SELECT rel.object_id, rel.term_taxonomy_id, tax.term_id, tax.taxonomy, terms.term_id, terms.name, terms.slug
							FROM " . wp_prefix('term_relationships', $wp_prefix) . " as rel,
							" . wp_prefix('term_taxonomy', $wp_prefix) . " as tax,
							" . wp_prefix('terms', $wp_prefix) . " as terms
							WHERE tax.term_taxonomy_id = rel.term_taxonomy_id
							AND tax.term_id = terms.term_id
							AND rel.object_id = '" . $post['id'] . "'", $wpdbconnection);
						//echo "<br /><strong>Categories:</strong><pre>"; print_r($termrelations); echo "</pre>"; // for debugging
						$postinfo .= "<ul>";
						if ($termrelations) {
							foreach ($termrelations as $term) {
								$term['name'] = $_zp_UTF8->convert($term['name']);
								$term['slug'] = $term['slug'];
								$term['taxonomy'] = $term['taxonomy'];
								switch ($term['taxonomy']) {
									case 'category':
										//Get new id of category
										$getcat = query_single_row("SELECT titlelink, title,id from " . prefix('news_categories') . " WHERE titlelink = " . db_quote($term['slug']) . " AND title = " . db_quote($term['name']));
										//Prevent double assignments
										if (query_single_row("SELECT id from " . prefix('news2cat') . " WHERE news_id = " . $newarticleid . " AND cat_id=" . $getcat['id'], false)) {
											$postinfo .= '<li class="import-exists">' . sprintf(gettext('%1$s <em>%2$s</em> already assigned'), $term['taxonomy'], $term['name']);
										} else {
											if (query("INSERT INTO " . prefix('news2cat') . " (cat_id,news_id) VALUES (" . $getcat['id'] . "," . $newarticleid . ")", false)) {
												$postinfo .= '<li class="import-success">' . sprintf(gettext('%1$s <em>%2$s</em> assigned'), $term['taxonomy'], $term['name']);
											} else {
												$postinfo .= '<li class="import-error">' . sprintf(gettext('%1$s <em>%2$s</em> could not be assigned!'), $term['taxonomy'], $term['name']);
											}
										}
										break;
									case 'post_tag':
										//Get new id of tag
										// only use "slug" for tags as ZP different to WP has no name (title) and slug (urlname) separately but just an urlname
										$gettag = query_single_row("SELECT name,id from " . prefix('tags') . " WHERE name = " . db_quote($term['slug']));
										//Prevent double assignments
										if (query_single_row("SELECT id from " . prefix('obj_to_tag') . " WHERE objectid = " . $newarticleid . " AND tagid =" . $gettag['id'], false)) {
											$postinfo .= '<li class="import-exists">' . sprintf(gettext('%1$s <em>%2$s</em> already assigned'), $term['taxonomy'], $term['slug']);
										} else {
											if (query("INSERT INTO " . prefix('obj_to_tag') . " (tagid,type,objectid) VALUES (" . $gettag['id'] . ",'news'," . $newarticleid . ")", false)) {
												$postinfo .= '<li class="import-success">' . sprintf(gettext('%1$s <em>%2$s</em> assigned'), $term['taxonomy'], $term['slug']);
											} else {
												$postinfo .= '<li class="import-error">' . sprintf(gettext('%1$s <em>%2$s</em> could not be assigned!'), $term['taxonomy'], $term['slug']);
											}
										}
										break;
								}
								//echo "<li>".sprintf(gettext('%1$s <em>%2$s</em>'),$term['taxonomy'],$term['slug'])."</li>";
							}
							$postinfo .= "</ul>";
							debugLogVar('Wordpress import - Term relations for "' . $post['title'] . '" (' . $post['type'] . ')', $termrelations);
						}
						break;
					case 'page':
						//Add the page to Zenphoto database as Zenpage page
						if (query("INSERT INTO " . prefix('pages') . " (title,titlelink,content,date,lastchange,`show`,permalink) VALUES (" . db_quote($post['title']) . "," . db_quote($titlelink) . "," . db_quote($post['content']) . "," . db_quote($post['date']) . "," . db_quote($post['lastchange']) . "," . $show . ",1)", false)) {
							$postinfo .= '<li class="import-success">' . sprintf(gettext('%1$s <em>%2$s</em> added'), $post['type'], $post['title']);
						} else {
							$postinfo .= '<li class="import-exists">' . sprintf(gettext('%1$s with the title/titlelink <em>%2$s</em> already exists!'), $post['type'], $post['title']);
						}
						break;
				} // switch end

				/*				 * ***********************************************************************
				 * getting comments (for each post/page indiviually so we can assign them)
				 * ************************************************************************ */
				switch ($post['type']) {
					case 'post':
						$ctype = 'news';
						break;
					case 'page':
						$ctype = 'pages';
						break;
				}
				$comments = wp_query_full_array("
							SELECT comment_post_ID, comment_author, comment_author_email, comment_author_url,comment_date, comment_content, comment_approved
							FROM " . wp_prefix('comments', $wp_prefix) . "
							WHERE comment_approved = 1 AND comment_post_ID = " . $post['id'], $wpdbconnection);
				$commentcount = '';
				$commentexists_count = '';
				if ($comments) {
					$postinfo .= '<ul>';
					foreach ($comments as $comment) {
						$comment['comment_author'] = $_zp_UTF8->convert($comment['comment_author']);
						$comment['comment_author_email'] = $comment['comment_author_email'];
						$comment['comment_author_url'] = $comment['comment_author_url'];
						$comment['comment_date'] = $comment['comment_date'];
						$comment['comment_content'] = nl2br($_zp_UTF8->convert($comment['comment_content']));
						if (getcheckboxState('convertlinefeeds')) {
							$comment['comment_content'] = nl2br($comment['comment_content']);
						}
						$comment_approved = sanitize_numeric($comment['comment_approved']);
						if ($comment_approved == 1) { // in WP 1 means approved, with ZP the opposite!
							$comment_approved = 0;
						} else {
							$comment_approved = 1;
						}
						//echo "commentstatus:".$comment['comment_approved'];
						if (query_single_row("SELECT * from " . prefix('comments') . " WHERE ownerid =" . $newarticleid . " AND name=" . db_quote($comment['comment_author']) . " AND email =" . db_quote($comment['comment_author_email']) . " AND website =" . db_quote($comment['comment_author_url']) . " AND date =" . db_quote($comment['comment_date']) . " AND comment =" . db_quote($comment['comment_content']) . " AND inmoderation =" . $comment_approved . " AND type='" . $ctype . "'", false)) {
							$commentexists_count++;
						} else {
							if (query("INSERT INTO " . prefix('comments') . " (ownerid,name,email,website,date,comment,inmoderation,type) VALUES (" . $newarticleid . "," . db_quote($comment['comment_author']) . "," . db_quote($comment['comment_author_email']) . "," . db_quote($comment['comment_author_url']) . "," . db_quote($comment['comment_date']) . "," . db_quote($comment['comment_content']) . "," . $comment_approved . ",'" . $ctype . "')", true)) {
								$commentcount++;
							} else {
								$postinfo .= '<li class="import-error">' . gettext('Comment could not be assigned!') . '</li>';
							}
						}
					}
					if ($commentexists_count != 0) {
						$postinfo .= '<li class="import-exists">' . sprintf(ngettext('%1$u comment already exists.', '%1$u comments already exist.', $commentexists_count), $commentexists_count) . '</li>';
					}
					if ($commentcount != 0) {
						$postinfo .= '<li class="import-success">' . sprintf(ngettext('%1$u comment imported.', '%1$u comments imported.', $commentcount), $commentcount) . '</li>';
					}
				} else {
					$postinfo .= '<ul><li class="import-nothing">' . gettext('No comments to import') . '</li>';
				}
				debugLogVar('Wordpress import - Comments for "' . $post['title'] . '" (' . $post['type'] . ')', $comments);
				$postinfo .= '</ul></li>';
				$postcount++;
			} // posts foreach
			$metaURL = 'wordpress_import.php?refresh=' . $postcount . '&amp;dbname=' . $wp_dbname . '&amp;dbuser=' . $wp_dbbuser . '&amp;dbpass=' . $wp_dbpassword . '&amp;dbhost=' . $wp_dbhost . '&amp;tableprefix=' . $wp_prefix . '&amp;convertlinefeeds=' . getcheckboxState('convertlinefeeds') . '&amp;XSRFToken=' . getXSRFToken('wordpress');
		} else { // if posts are available at all
			$metaURL = ''; // to be sure...
			$postinfo .= "<li class='import-nothing'>" . gettext("No posts or pages to import.") . "</li>";
		}
	} // if db data set
	$zenphoto_tabs['overview']['subtabs'] = array(gettext('Wordpress') => '');
	printAdminHeader('overview', 'wordpress');
	if (!empty($metaURL) && $postcount < $posttotalcount) {
		?>
		<meta http-equiv="refresh" content="1; url=<?php echo $metaURL; ?>" />
		<?php
	}
	?>
	<style type="text/css">
		.import-success {
			color: darkgreen;
		}
		.import-nothing {
			color: #663300;
		}
		.import-exists {
			color: darkblue;
		}
		.import-error {
			color: red;
		}
	</style>
	</head>
	<body>
		<?php printLogoAndLinks(); ?>
		<div id="main">
			<?php printTabs(); ?>
			<div id="content">
				<?php printSubtabs('Wordpress'); ?>
				<div class="tabbox">
					<?php zp_apply_filter('admin_note', 'wordpress', ''); ?>
					<h1><?php echo (gettext('Wordpress Importer')); ?></h1>
					<?php if (!isset($_REQUEST['dbname']) && !isset($_REQUEST['dbuser']) && !isset($_REQUEST['dbpass']) && !isset($_REQUEST['dbhost']) && !isset($_GET['refresh'])) { ?>
						<p><?php echo gettext("An importer for <strong>Wordpress 3.x</strong> to the Zenpage CMS plugin that imports the following:"); ?></p>
						<ul>
							<li><?php echo gettext("<strong>Posts (post_status published and draft only) => Zenpage articles</strong>"); ?></li>
							<li><?php echo gettext("<strong>Pages (post_status published and draft only) => Zenpage pages</strong>"); ?></li>
							<li><?php echo gettext("<strong>Post categories => Zenpage categories including assignment to their article</strong>"); ?></li>
							<li><?php echo gettext("<strong>Post tags => Zenphoto tags including assignment to their article</strong>"); ?></li>
							<li><?php echo gettext("<strong>Post and page comments => Zenphoto comments including assignment to their article</strong>"); ?></li>
						</ul>
						<p class="notebox">
							<?php echo gettext("<strong>IMPORTANT: </strong>If you are not using an fresh Zenphoto install it is <strong>strongly recommended to backup your database</strong> before running this importer. Make also sure that both databases use the same encoding so you do not get messed up character display."); ?>
						</p>
						<p class="notebox">
							<?php echo gettext("<strong>Note:</strong> <em>Wordpress page and category nesting</em> is currently not preserved but can easily be recreated by drag and drop sorting."); ?>
						</p>

						<p><?php echo gettext("In case anything does not work as expected the query results from the Wordpress database are logged in <code>zp-data/debug.log</code>"); ?></p>
						<?php if (!extensionEnabled('zenpage')) { ?>
							<p class="errorbox"><?php echo gettext('<strong>ERROR: </strong>The Zenpage CMS plugin is not enabled.'); ?></p>
							<?php
							die();
						}
						?>

						<h2><?php echo gettext('Please enter your Wordpress database details:'); ?></h2>
						<form action="" method="post" name="wordpress">
							<?php XSRFToken('wordpress'); ?>
							<input type="text" value="wordpress" id="dbname" name="dbname" /> <label for="dbname"><?php echo gettext("Database name"); ?></label><br />
							<input type="text" value="root" id="dbuser" name="dbuser" /> <label for="dbuser"><?php echo gettext("Database user"); ?></label><br />
							<input type="text" value="root" id="dbpass" name="dbpass" /> <label for="dbpass"><?php echo gettext("Database password"); ?></label><br />
							<input type="text" value="localhost" id="dbhost" name="dbhost" /> <label for="dbhost"><?php echo gettext("Database host"); ?></label><br />
							<input type="text" value="wp_" name="tableprefix" id="tableprefix" /> <label for="tableprefix"><?php echo gettext("Database table prefix"); ?></label><br />
							<input type="checkbox" value="0" name="convertlinefeeds" id="convertlinefeeds" /> <label for="convertlinefeeds"><?php echo gettext('Convert linefeeds to new lines (br)'); ?></label><br />
							<p class="buttons"><button class="submitbutton" type="submit" title="<?php echo gettext("Import"); ?>"><img src="../images/pass.png" alt="" /><strong><?php echo gettext("Import"); ?></strong></button></p>
							<p class="buttons"><button class="submitbutton" type="reset"><img src="../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button></p>
							<br style="clear:both" />
						</form>
						<?php
					}

					if (isset($_REQUEST['dbname']) || isset($_REQUEST['dbuser']) || isset($_REQUEST['dbpass']) || isset($_REQUEST['dbhost'])) {
						// Wordpres DB connection check output
						if ($dbinfo_incomplete) {
							echo $dbinfo . $dbinfo_incomplete;
							die();
						}
						if ($db_noconnection) {
							echo $dbinfo . $db_noconnection;
							die();
						}
						if ($db_notselected) {
							echo $dbinfo . $db_notselected;
							die();
						}
						if ($posttotalcount == $postcount) {
							?>
							<p class="messagebox"><?php echo gettext('Import finished.'); ?></p>
						<?php } else {
							?>
							<p><?php echo gettext('Importing...patience please.'); ?></p>
						<?php } ?>
						<ul>
							<?php
							if (!isset($_GET['refresh'])) {
								?>
								<li><strong><?php echo gettext('Categories'); ?></strong>
									<ol>
										<?php echo $catinfo; ?>
									</ol>
								</li>
								<?php
							}
							if (!isset($_GET['refresh'])) {
								?>
								<li><strong><?php echo gettext('Tags'); ?></strong>
									<ol>
										<?php echo $taginfo; ?>
									</ol>
								</li>
								<?php
							}
							?>
							<li><strong><?php echo gettext('Pages and Articles'); ?></strong>
								<?php
								if (isset($_GET['refresh'])) {
									$startlist = ' start="' . $refresh . '"';
								} else {
									$startlist = '';
								}
								?>
								<ol<?php echo $startlist; ?>>
									<?php echo $postinfo; ?>
								</ol>
							</li>
						</ul>
						<?php if ($posttotalcount == $postcount) { ?>
							<p class="buttons"><a href="wordpress_import.php"><?php echo gettext('New import'); ?></a></p>
							<br style="clear:both" />
							<?php
						}
					} // if form submit if
					?>
				</div>
			</div><!-- content -->
		</div><!-- main -->
		<?php printAdminFooter(); ?>
	</body>
	</html>

	<?php
}
?>