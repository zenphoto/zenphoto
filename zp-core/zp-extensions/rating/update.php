<?php
/**
 * rating plugin updater - Updates the rating in the database
 * @author Stephen Billard (sbillard)
 * @package plugins
 */

define ('OFFSET_PATH', 4);
require_once(dirname(dirname(dirname(__FILE__))).'/template-functions.php');
require_once('functions-rating.php');


$id = sanitize_numeric($_POST['id']);
$table = sanitize($_POST['table'],3);
$dbtable = prefix($table);
$ip = getUserIP();
$unique = '_'.$table.'_'.$id;
$split_stars = getOption('rating_split_stars')+1;
$rating = max(0, min(5, round(sanitize_numeric($_POST['star_rating-value'.$unique])/$split_stars)));
$IPlist = query_single_row("SELECT * FROM $dbtable WHERE id= $id");
if (is_array($IPlist)) {
	$oldrating = getRatingByIP($ip, $IPlist['used_ips'], $IPlist['rating']);
} else {
	$oldrating = false;
}
if(!$oldrating || getOption('rating_recast')) {
	if ($rating) {
		$_rating_current_IPlist[$ip] = $rating;
	} else {
		if (isset($_rating_current_IPlist[$ip])) {
			unset($_rating_current_IPlist[$ip]); // retract vote
		}
	}
	$insertip = serialize($_rating_current_IPlist);
	if ($oldrating) {
		if ($rating) {
			$voting = '';
		} else {
			$voting = ' total_votes=total_votes-1,';	// retract vote
		}
		$valuechange = $rating-$oldrating;
		if ($valuechange>=0) {
			$valuechange = '+'.$valuechange;
		}
		$valuechange = ' total_value=total_value'.$valuechange.',';
	} else {
		$voting = ' total_votes=total_votes+1,';
		$valuechange = ' total_value=total_value+'.$rating.',';
	}
	$sql = "UPDATE ".$dbtable.' SET'.$voting.$valuechange." rating=total_value/total_votes, used_ips='".$insertip."' WHERE id='".$id."'";
	$rslt = query($sql,false);
}

?>
