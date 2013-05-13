<?php

// force UTF-8 Ø

?>
<!-- stopdesign comment form -->
<?php $showhide = "<a href=\"#comments\" id=\"showcomments\"><img src=\"" .
	$_zp_themeroot . "/images/btn_show.gif\" width=\"35\" height=\"11\" alt=\"".gettext("SHOW")."\" /></a> <a href=\"#content\" id=\"hidecomments\"><img src=\"" .
	$_zp_themeroot . "/images/btn_hide.gif\" width=\"35\" height=\"11\" alt=\"".gettext("HIDE")."\" /></a>";
 $num = @call_user_func('getCommentCount');
?>
<h2>
	<?php
	if ($num == 0) {
		echo gettext("No comments yet");
	} else {
		printf(ngettext('%u comment so far','%u comments so far', $num),$num).' '. $showhide;
	}
 ?>
</h2>
<?php printCommentErrors(); ?>

<!-- BEGIN #comments -->
<div id="comments">
	<?php
		$autonumber = 0;
		while (next_comment()) {
			if (!$autonumber) {
				?>
				<dl class="commentlist">
				<?php
			}
			$autonumber++;
		?>
		<dt id="comment<?php echo $autonumber; ?>">
			<a href="#comment<?php echo $autonumber; ?>" class="postno" title="<?php printf(gettext('Link to Comment %u'),$autonumber); ?>"><?php echo $autonumber; ?>.</a>
			<em>On <?php echo getCommentDateTime();?>, <?php printf(gettext('%s wrote:'),getCommentAuthorLink()); ?></em>
		</dt>
		<dd><p><?php echo html_encodeTagged(getCommentBody(),false); ?><?php printEditCommentLink(gettext('Edit'), ' | ', ''); ?></p></dd>
		<?php
		}
		if ($autonumber) {
			?>
			</dl>
			<?php
		}
	?>
	<script type="text/javascript">
		// <!-- <![CDATA[
		var initstate = <?php echo $errors; ?>;
		// ]]> -->
	</script>
	<script type="text/javascript" src="<?php echo $_zp_themeroot ?>/js/comments.js"></script>
	<!-- BEGIN #commentblock -->
		<div id="commentblock">
		<?php printCommentForm(false, ''); ?>
		</div>
	<!-- END #commentblock -->
</div>
<!-- END #comments -->