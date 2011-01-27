<?php
/**
 * Wordpress importer
 *
 * This imports Wordpress pages, posts, categories and comments to Zenpage
 *
 * @package admin
 */

define('OFFSET_PATH', 3);
chdir(dirname(dirname(__FILE__)));

require_once(dirname(dirname(__FILE__)).'/admin-functions.php');
require_once(dirname(dirname(__FILE__)).'/admin-globals.php');

if(getOption('zp_plugin_zenpage')) {
	require_once(dirname(dirname(__FILE__)).'/'.PLUGIN_FOLDER.'/zenpage/zenpage-admin-functions.php');
}

$button_text = gettext('Wordpress Importer');
$button_hint = gettext('An importer for Wordpress posts and pages to Zenpage.');
$button_icon = 'images/wpmini-blue.png';
$button_rights = ADMIN_RIGHTS;

admin_securityChecks(NULL, currentRelativeURL(__FILE__));

if(isset($_POST['dbname']) || isset($_POST['dbuser']) || isset($_POST['dbpass']) || isset($_POST['dbhost'])) {
	XSRFdefender('wordpress');
}

$gallery = new Gallery();
$webpath = WEBPATH.'/'.ZENFOLDER.'/';

// some extra functions
function wp_query_full_array($sql) {
	$result = mysql_query($sql) or die(gettext("Query failed : ") . mysql_error());
	if ($result) {
		$allrows = array();
		while ($row = mysql_fetch_assoc($result))
			$allrows[] = $row;
		return $allrows;
	} else {
		return false;
	}
}

function wp_prefix($tablename,$wp_prefix) {
	return '`'.$wp_prefix.$tablename.'`';
}

function wpimport_TryAgainError($message) {
	?>
	<p class="errorbox">
	<?php	echo $message; ?>
	<br /><a href="wordpress_import.php"><?php echo gettext('Please try again'); ?></a>
	</p>
	<?php
}

function printWPDatabaseInfo($wp_dbname,$wp_dbbuser,$wp_dbpassword,$wp_dbhost,$wp_prefix) {
	?>
	<h2><?php echo gettext('Wordpress-Database info'); ?></h2>
		<ul>
			<li><?php echo gettext('Database name'); ?>: <strong><?php echo $wp_dbname; ?></strong></li>
			<li><?php echo gettext('Database user'); ?>: <strong><?php echo $wp_dbbuser; ?></strong></li>
			<li><?php echo gettext('Database password'); ?>: <strong><?php echo $wp_dbpassword; ?></strong></li>
			<li><?php echo gettext('Database host'); ?>: <strong><?php echo $wp_dbhost; ?></strong></li>
			<li><?php echo gettext('Database table prefix'); ?>: <strong><?php echo $wp_prefix; ?></strong></li>
		</ul>
		<?php
}

printAdminHeader(gettext('utilities'),gettext('wordpress'));
?>
</head>
<body>
<?php printLogoAndLinks(); ?>
<div id="main">
<?php printTabs(); ?>
<div id="content">
<h1><?php echo (gettext('Wordpress Importer')); ?></h1>
<p><?php echo gettext("An importer for <strong>Wordpress 2.9.x/3.x</strong> to the Zenpage CMS plugin that imports the following:"); ?></p>
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

<p><?php echo gettext("In case anything does not work as expected the query results from the Wordpress database are logged in <code>zp-data/debug_log.txt</code>"); ?></p>
<?php if(!getOption('zp_plugin_zenpage')) { ?>
<p class="errorbox"><?php echo gettext('<strong>ERROR: </strong>The Zenpage CMS plugin is not enabled.'); ?></p>
<?php
die();
} ?>
<?php if(!isset($_POST['dbname']) && !isset($_POST['dbuser']) && !isset($_POST['dbpass']) && !isset($_POST['dbhost'])) { ?>
<h2><?php echo gettext('Please enter your Wordpress database details:'); ?></h2>
<form action="" method="post" name="wordpress">
	<?php XSRFToken('wordpress');?>
	<input type="text" value="wordpress" id="dbname" name="dbname" /> <label for="dbname"><?php echo gettext("Database name"); ?></label><br />
	<input type="text" value="root" id="dbuser" name="dbuser" /> <label for="dbuser"><?php echo gettext("Database user"); ?></label><br />
	<input type="text" value="root" id="dbpass" name="dbpass" /> <label for="dbpass"><?php echo gettext("Database password"); ?></label><br />
	<input type="text" value="localhost" id="dbhost" name="dbhost" /> <label for="dbhost"><?php echo gettext("Database host"); ?></label><br />
	<input type="text" value="wp_" name="tableprefix" id="tableprefix" /> <label for="tableprefix"><?php echo gettext("Database table prefix"); ?></label><br />
	<p class="buttons"><button class="submitbutton" type="submit" title="<?php echo gettext("Import"); ?>"><img src="../images/pass.png" alt="" /><strong><?php echo gettext("Import"); ?></strong></button></p>
	<p class="buttons"><button class="submitbutton" type="reset" title="<?php echo gettext("Reset"); ?>"><img src="../images/reset.png" alt="" /><strong><?php echo gettext("Reset"); ?></strong></button></p>
	<br style="clear:both" />
</form>
<?php }

if(isset($_POST['dbname']) || isset($_POST['dbuser']) || isset($_POST['dbpass']) || isset($_POST['dbhost'])) {
	// Wordpres DB connection
	$missingmessage = '<span style="color: red">'.gettext('Missing').'</span>';
	$wp_dbname = sanitize($_POST['dbname']);
	$wp_dbbuser = sanitize($_POST['dbuser']);
	$wp_dbpassword = sanitize($_POST['dbpass']);
	$wp_dbhost = sanitize($_POST['dbhost']);
	$wp_prefix = sanitize($_POST['tableprefix']);
	if(empty($wp_dbname) || empty($wp_dbbuser) || empty($wp_dbpassword) || empty($wp_dbhost)) {
		if(empty($wp_dbname)) $wp_dbname = $missingmessage;
		if(empty($wp_dbbuser)) $wp_dbbuser = $missingmessage;
		if(empty($wp_dbpassword)) $wp_dbpassword = $missingmessage;
		if(empty($wp_dbhost)) $wp_dbhost = $missingmessage;
		printWPDatabaseInfo($wp_dbname,$wp_dbbuser,$wp_dbpassword,$wp_dbhost,$wp_prefix);
		wpimport_TryAgainError(gettext('<strong>ERROR:</strong> Wordpress database info incomplete'));
		die();
	}
	printWPDatabaseInfo($wp_dbname,$wp_dbbuser,$wp_dbpassword,$wp_dbhost,$wp_prefix);
		$wpdbconnection = @mysql_connect($wp_dbhost,$wp_dbbuser,$wp_dbpassword,true); // open 2nd connection to Wordpress additionally to the existing Zenphoto connection
		mysql_query("SET NAMES 'utf8'");
		@mysql_query('SET SESSION sql_mode="";');
		if(!$wpdbconnection) {
			wpimport_TryAgainError(gettext('<strong>ERROR:</strong> Could not connect to the Wordpress database - Query failed : ') . mysql_error());
			die();
		}
		if(!@mysql_select_db($wp_dbname,$wpdbconnection)) {
			wpimport_TryAgainError(gettext('<strong>ERROR:</strong> Wordpress database could not be selected - Query failed : ') . mysql_error());
			die();
		}
		?>
		<ul>
		<li><strong><?php echo gettext('Categories'); ?></strong>
		<ol>
	<?php
	//getting all Wordpress categories
	$cats = wp_query_full_array("SELECT * FROM ".wp_prefix('terms',$wp_prefix)." as terms, ".wp_prefix('term_taxonomy',$wp_prefix)." as tax WHERE tax.taxonomy = 'category' AND terms.term_id = tax.term_id");
	//echo "<li><strong>Categories</strong>: <pre>"; print_r($cats); echo "</pre></li>"; // for debugging
	debugLogArray('Wordpress import - All Categories', $cats);

	//Add categories to Zenphoto database
 	if($cats) {
		foreach($cats as $cat) {
			$cattitlelink = $_zp_UTF8->convert($cat['slug']);
			$cattitle = seoFriendly($_zp_UTF8->convert($cat['name']));
			if (query("INSERT INTO ".prefix('news_categories')." (titlelink, title, permalink) VALUES (".db_quote($cattitlelink).", ".db_quote($cattitle).",'1')", false)) {
				echo '<li class="messagebox">'.sprintf(gettext("Category <em>%s</em> added"),$cat['name']).'</li>';
			} else {
				echo '<li class="errorbox">'.sprintf(gettext("A category with the title/titlelink <em>%s</em> already exists!"),$cat['name']).'</li>';
			}
		}
	} else {
		echo '<li class="notebox">'.gettext('No categories to import.').'</li>';
	} 
	?> 
	</ol>
	</li>
	<li><strong><?php echo gettext('Tags'); ?></strong>
	<ol>
	<?php
	// getting all Wordpress tags
	$tags = wp_query_full_array("SELECT * FROM ".wp_prefix('terms',$wp_prefix)." as terms, ".wp_prefix('term_taxonomy',$wp_prefix)." as tax WHERE tax.taxonomy = 'post_tag' AND terms.term_id = tax.term_id");
	//echo "<li><strong>Tags</strong>: <pre>"; print_r($tags); echo "</pre></li>"; // for debugging
	debugLogArray('Wordpress import - Tags import', $tags);

	//Add tags to Zenphoto database
	if($tags) {
		foreach($tags as $tag) {
			if (query("INSERT INTO ".prefix('tags')." (name) VALUES (".db_quote(seoFriendly($tag['slug'])).")", false)) {
				echo '<li class="messagebox">'.sprintf(gettext("Tag <em>%s</em> added"),$tag['name']).'</li>';
			} else {
				echo '<li class="errorbox">'.sprintf(gettext("A tag with the title/titlelink <em>%s</em> already exists!"),$tag['name']).'</li>';
			}
		}
	} else {
		echo '<li class="notebox">'.gettext('No tags to import.').'</li>';
	} 
	?>
	</ol>
	</li>
	<li><strong><?php echo gettext('Pages and Articles'); ?></strong>
	<ol>
	<?php
	// get wp posts and pages
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
	FROM ".wp_prefix('posts',$wp_prefix)."
	WHERE (post_type = 'post' OR post_type = 'page') AND (post_status = 'publish' OR post_status = 'draft')
	ORDER BY post_date DESC
");
	if($posts) {
		//echo "Posts<br /><pre>"; print_r($posts); echo "</pre>"; // for debugging
		foreach ($posts as $post) {
			//echo "<li><strong>".$post['title']."</strong> (id: ".$post['id']." / type: ".$post['type']." / date: ".$post['date'].")<br />";
			debugLogArray('Wordpress import - Import post: "'.$post['title'].'" ('.$post['type'].')', $posts);
			if($post['show'] == "publish") {
				$show = 1;
			} else {
				$show = 0;
			}
			$post['title']= $_zp_UTF8->convert($post['title']);
			$titlelink = sanitize(seoFriendly($post['title']));
		 	//$post['content'] = nl2br($_zp_UTF8->convert($post['content']));
		 	$post['content'] = $_zp_UTF8->convert($post['content']);
			$post['date']  = $post['date'];
			$post['lastchange'] = $post['lastchange'];
			$post['type'] = $post['type'];
			switch($post['type']) {
				case 'post':
					//Add the post to Zenphoto database as Zenpage article
					if (query("INSERT INTO ".prefix('news')." (title,titlelink,content,date,lastchange,`show`) VALUES (".db_quote($post['title']).",".db_quote($titlelink).",".db_quote($post['content']).",".db_quote($post['date']).",".db_quote($post['lastchange']).",".$show.")", false)) {
						echo '<li class="messagebox">'.sprintf(gettext('%1$s <em>%2$s</em> added'),$post['type'], $post['title']);
					} else {
						echo '<li class="errorbox">'.sprintf(gettext('%1$s with the title/titlelink <em>%2$s</em> already exists!'),$post['type'], $post['title']);
					}
					//Get new id of the article
					$newarticle = new ZenpageNews($titlelink);
					$newarticleid = $newarticle->getID();
					// getting the categories and tags assigned to this post (Wordpress pages do not have tags or categories
					$termrelations = wp_query_full_array("
						SELECT rel.object_id, rel.term_taxonomy_id, tax.term_id, tax.taxonomy, terms.term_id, terms.name, terms.slug
						FROM ".wp_prefix('term_relationships',$wp_prefix)." as rel,
						".wp_prefix('term_taxonomy',$wp_prefix)." as tax,
						".wp_prefix('terms',$wp_prefix)." as terms
						WHERE tax.term_taxonomy_id = rel.term_taxonomy_id
						AND tax.term_id = terms.term_id
						AND rel.object_id = '".$post['id']."'");
					//echo "<br /><strong>Categories:</strong><pre>"; print_r($termrelations); echo "</pre>"; // for debugging
					echo "<ul>";
					if($termrelations) {
						foreach($termrelations as $term) {
							$term['name']  = $_zp_UTF8->convert($term['name']);
							$term['slug'] = $term['slug'];
							$term['taxonomy'] = $term['taxonomy'];
							switch($term['taxonomy']) {
								case 'category':
									//Get new id of category
									$getcat = query_single_row("SELECT titlelink, title,id from ".prefix('news_categories')." WHERE titlelink = ".db_quote($term['slug'])." AND title = ".db_quote($term['name']));
									//Prevent double assignments
									if (query_single_row("SELECT id from ".prefix('news2cat')." WHERE news_id = ".db_quote($newarticleid)." AND cat_id=".db_quote($getcat['id']),false)) {
										echo '<li class="errorbox">'.sprintf(gettext('%1$s <em>%2$s</em> already assigned'),$term['taxonomy'], $term['name']);
									} else {
										if (query("INSERT INTO ".prefix('news2cat')." (cat_id,news_id) VALUES (".$getcat['id'].",".$newarticleid.")", false)) {
											echo '<li class="messagebox">'.sprintf(gettext('%1$s <em>%2$s</em> assigned'),$term['taxonomy'], $term['name']);
										} else {
											echo '<li class="errorbox">'.sprintf(gettext('%1$s <em>%2$s</em> could not be assigned!'),$term['taxonomy'], $term['name']);
										}
									}
									break;
								case 'post_tag':
									//Get new id of tag
									$gettag = query_single_row("SELECT name,id from ".prefix('tags')." WHERE name = ".db_quote($term['name']));
									//Prevent double assignments
									if (query_single_row("SELECT id from ".prefix('obj_to_tag')." WHERE objectid = ".$newarticleid." AND tagid =".$gettag['id'],false)) {
										echo '<li class="errorbox">'.sprintf(gettext('%1$s <em>%2$s</em> already assigned'),$term['taxonomy'], $term['name']);
									} else {
										if (query("INSERT INTO ".prefix('obj_to_tag')." (tagid,type,objectid) VALUES ('".$gettag['id']."','news','".$newarticleid."')",false)) {
											echo '<li class="messagebox">'.sprintf(gettext('%1$s <em>%2$s</em> assigned'),$term['taxonomy'], $term['name']);
										} else {
											echo '<li class="errorbox">'.sprintf(gettext('%1$s <em>%2$s</em> could not be assigned!'),$term['taxonomy'], $term['name']);
										}
									}
									break;
							}
							//echo "<li>".sprintf(gettext('%1$s <em>%2$s</em>'),$term['taxonomy'],$term['slug'])."</li>";
						}
						echo "</ul>";
						debugLogArray('Wordpress import - Term relations for "'.$post['title'].'" ('.$post['type'].')', $termrelations);
					}
					break;
				case 'page':
				//Add the page to Zenphoto database as Zenpage page
					if (query("INSERT INTO ".prefix('pages')." (title,titlelink,content,date,lastchange,`show`) VALUES (".db_quote($post['title']).",".db_quote($titlelink).",".db_quote($post['content']).",".db_quote($post['date']).",".db_quote($post['lastchange']).",".$show.")", false)) {
						echo '<li class="messagebox">'.sprintf(gettext('%1$s <em>%2$s</em> added'),$post['type'], $post['title']);
					} else {
						echo '<li class="errorbox">'.sprintf(gettext('%1$s with the title/titlelink <em>%2$s</em> already exists!'),$post['type'], $post['title']);
					}
					break;
			} // switch end
			// getting comments
			switch($post['type']) {
				case 'post':
					$ctype = 'news';
					break;
				case 'page':
					$ctype = 'pages';
					break;
			}
			$comments = wp_query_full_array("
						SELECT comment_post_ID, comment_author, comment_author_email, comment_author_url,comment_date, comment_content, comment_approved
						FROM ".wp_prefix('comments',$wp_prefix)."
						WHERE comment_post_ID = '".$post['id']."'");
			$commentcount = "";

			if($comments) {
				echo '<ul>';
				foreach($comments as $comment) {
					$comment['comment_author']  = $_zp_UTF8->convert($comment['comment_author']);
					$comment['comment_author_email']  = $comment['comment_author_email'];
					$comment['comment_author_url']  = $comment['comment_author_url'];
					$comment['comment_date']  = $comment['comment_date'];
					$comment['comment_content']  = nl2br($_zp_UTF8->convert($comment['comment_content']));
					$comment['comment_approved']  = $comment['comment_approved'];
					if (query_single_row("SELECT * from ".prefix('comments')." WHERE ownerid =".$newarticleid." AND name=".db_quote($comment['comment_author'])." AND email =".db_quote($comment['comment_author_email'])." AND website =".db_quote($comment['comment_author_url'])." AND date =".db_quote($comment['comment_date'])." AND comment =".db_quote($comment['comment_content'])." AND inmoderation =".$comment['comment_approved']." AND type='".$ctype."'",false)) {
						echo '<li class="errorbox">'.gettext('Comment already exists!').'</li>';
					} else {
						if(query("INSERT INTO ".prefix('comments')." (ownerid,name,email,website,date,comment,inmoderation,type) VALUES (".$newarticleid.",".db_quote($comment['comment_author']).",'".db_quote($comment['comment_author_email']).",".db_quote($comment['comment_author_url']).",".db_quote($comment['comment_date']).",".db_quote($comment['comment_content']).",".$comment['comment_approved'].",".$ctype.")",false)) {
							$commentcount++;
						} else {
							echo '<li class="errorbox">'.gettext('Comment could not be assigned!').'</li>';
						}
					}
				}
				//echo "<li class='messagebox'>".sprintf(gettext('%u comments imported'),$commentcount)."</li>";
			} else {
				echo '<ul><li class="notebox">'.gettext('No comments to import').'</li>';
			}
			debugLogArray('Wordpress import - Comments for "'.$post['title'].'" ('.$post['type'].')', $comments);
			echo '</ul></li>'; 
		} // posts foreach
	} else { // if posts are available at all
		echo "<li class='notebox'>".gettext("No posts or pages to import.")."</li>";
	} 
	?>
	<p class="buttons"><a href="wordpress_import.php"><?php echo gettext('New import'); ?></a></p>
	<br style="clear:both" />
	</ol>
</li>
</ul>
	<?php
} // if form submit if
?>
</div><!-- content -->
</div><!-- main -->
<?php printAdminFooter(); ?>
</body>
</html>