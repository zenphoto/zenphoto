<?php
$catobj = new ZenpageCategory('troubleshooting');
$cats = $catobj->getSubCategories();
foreach ($cats as $cat) {
	$catobj = new ZenpageCategory($cat);
	$h2 = $catobj->getTitle();
	?>
	<h2><a name="<?php echo $catobj->getTitlelink(); ?>"></a><?php echo $h2; ?></h2>
	<?php
	listArticles($catobj);
}

function listArticles($cat) {
	$articles = $cat->getArticles();
	if (!empty($articles)) {
		?>
		<ol>
		<?php
		foreach ($articles as $titlelink) {
			$titlelink = $titlelink['titlelink'];
			$article = new ZenpageNews($titlelink);
			?>
			<li>
			<h4><a href="javascript:toggle('<?php echo $titlelink; ?>');"><?php echo $article->getTitle(); ?></a></h4>
			<div id="<?php echo html_encode($titlelink)?>" style="display:none;">
				<?php echo $article->getContent(); ?>
			</div>
			<?php
			?>
			</li>
			<?php
		}
		?>
		</ol>
		<?php
	}
}
?>