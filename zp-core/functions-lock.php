<?php
/**
 * Mutex functions based on MySQL locks, with flock lock option too.
 * Original draft copyright by d4gurasu, 2012
 * Contributed for zenphoto, and gpl license granted accordingly.
 *
 * lock core functions
 * @package core
 */

/* Several things about MySQL locks:
 * MySQL locks clear when the same session asks for another lock or when the session closes.
 * So every concurrent MySQL lock must have its own db connection associated with the lock.
 * Here, every lock id (concurrent or not) gets its own connection stored in the global
 * associative array $_lock_resource_array[].
 *
 * You cannot release a lock without using the connection it was made on.
 * Thus without more IPC, only the process that made the lock (and knows it's connection link)
 * can release it.
 * Here we stick to that rule. Are connection links valid across processes?
 * Maybe they could be stored in the db if there was a need to get around this.
 *
 * Partially for this reason there is no stale lock cleanup implementation here.
 * If a lock is old, the thread is still running. Fix it by enforcing php timeouts.
 *
 * From Mysql 5.1 manual: Note If a client attempts to acquire a lock that is already held by another client,
 * it blocks according to the timeout argument. If the blocked client terminates,
 * its thread does not die until the lock request times out. This is a known bug (fixed in MySQL 5.5).
 *
 * Bummer, so long blocking locks are no good.
 * MySQL version is detected and a workaround for this bug is included here.
 *
 * About flock locks:
 * They are auto releasing on process exit, or even if all references to the handler are reassigned 
 * or deleted (including automatic function variable deletion).
 * They don't work on all old filesystems and maybe not well on NFS (at least not between multiple hosts)
 * The non-blocking option probably doesn't work on at least some versions of php on some versions of windows.
 * However, the get_multi_queu_lock function below works without use of non-blocking locks.
 * If you do request a 0 or limited timeout lock directly, the use_flock option will be ignored.
 */


/* Reimpliment MySQL connect and query functions here
 * We have different needs here including need of an interface that doesn't reuse connections.
 */

// need basic zp functions to get MySQL login credentials:
require_once(dirname(__FILE__).'/functions-basic.php');

/**
 * Output the provided string plus a newline to a log file.
 * Really for development use only... could just put if (false).
 * @param string output message.
 */
function lock_debug($msg){
	// This prints a bunch.. so use a separate debug option.
	$debug = isset($_GET['debug_locks']);
	if($debug){
		$output=fopen(SERVERPATH."/".DATA_FOLDER.'/lock_debug.log',"a");
//		echo $msg."<br />";
		fwrite($output,$msg."\n");
		fclose($output);
	}
}

/**
 * Get a new MySQL connection just to use for a lock.
 * @global array $_zp_conf_vars used to get connections creditials.
 * @return mysqli style connection link.
 */
function db_new_connect() {
	lock_debug("db_new_connect:");
	global $_zp_conf_vars;
	$DB_connection = @mysqli_connect($_zp_conf_vars['mysql_host'], $_zp_conf_vars['mysql_user'], $_zp_conf_vars['mysql_pass']);
	if (!$DB_connection) {
		lock_debug("Error getting new_db connection: ".mysqli_error()."<br />");
		return false;
	}
	lock_debug("db_new_connect: Got new db connection");
	// For TCP/IP or Unix socket connections.. we can try to make sure the session doesn't expire too soon:
	query_result('SET session wait_timeout=86400',$DB_connection);
	return $DB_connection;
}


/**
 *Processes a Mysql query and return first value in result array.
 *@param string $query_string  The Mysql query string.
 *@param mysqli_connection_link
 *@return First value of result in array resource returned by mysqli_query, true or false for lock queries.
 */
function query_result($query_string, $lock_resource = 0) {
	static $default_connection = NULL;
	lock_debug("<br /> query_result: string: {$query_string}");
	if (!$lock_resource) {
		// Use a default connection.  That's fine for queries that just read the lock status.
		// Keep the default connection open for later use.
		if (!$default_connection){
			if(!$default_connection=db_new_connect()){
				echo "query_result: Could not get a db connection for query <br />";
				exit;
			}
		}
		lock_debug("query_result: No connection provided, using default connection");
		$lock_resource=$default_connection;
	}
	$resource=mysqli_query($lock_resource, $query_string);
	if ($resource === true ){
		// not a documented result, but a valid one and it fills the logs with errrors if it goes to mysqli_fetch.
		return true;
	} elseif ($resource) {
		$result_array=mysqli_fetch_row($resource);
		$result_val=$result_array[0];
		lock_debug("query_result: result: $result_val ");
		return $result_val;
	} elseif ($resource === NULL) {// also not a documented result
		echo "Null query result. This shouldn't happen.<br />";
		exit;
	} else {
		echo "Error in mysql query in function query_result <br />";
		exit;
	}
}

/**
 * Indirection wrapper to call MySQL or flock lock as requested.
 * Use timeout of -1 for indefinitely blocking lock
 */
function lock_command($lock_id,$lock_timeout,$lock_resource,$lock_type){
	if($lock_type == 'flock' and $lock_timeout == PHP_INT_MAX ){
		lock_debug("lock_command: Using a flock lock");
		return flock($lock_resource,LOCK_EX);
	}
	if ($lock_timeout == -1){
		$lock_timeout=PHP_INT_MAX;
	}
	return query_result("SELECT GET_LOCK('{$lock_id}',{$lock_timeout})",$lock_resource);
}


/**
 *Get a lock/mutex
 * Usage
 * get a blocking lock:  get_lock('my_lock_id')
 * get a non-blocking lock (0 timeout):  $did_I_get_the_lock=get_lock('my_lock_id',0)
 * blocking until 1 minute timeout:  $did_I_get_the_lock=get_lock('my_lock_id',60)
 * If option use_flock is true AND timeout =-1, then we'll use a flock lock.
 *@param string $lock_id
 *@param int timeout in seconds, optional.  Default=-1: no timeout.  0 => non-blocking lock.
 *@global $_lock_resource_array array of mysqli connection links associated with each lock id.
 *@return true if success, false if timed-out.
 */
function get_lock($lock_id,$timeout=-1) {
	//  If the connection closes, the lock will too.
	global $_lock_resource_array; // store the handles for each lock.
	static $mysql_version=NULL;
	static $try_to_use_flock=NULL;
	$lockpath=SERVERPATH.'/'.DATA_FOLDER.'/locks';
	if ($try_to_use_flock === NULL ){// $initialize try_to_use_flock
		$try_to_use_flock=getOption('use_flock_locks');
		if (!file_exists($lockpath)) {
			mkdir($lockpath);
		}
	}

	lock_debug("get_lock");
	if ($try_to_use_flock and $timeout==-1) {// use a flock lock if appropriate.
		$lock_file="{$lockpath}"."/lock_{$lock_id}";
		$lock_resource=fopen($lock_file,"w+");
		fclose($lock_resource);
		if($lock_resource=fopen($lock_file,"r+")){
		} else {
			echo "Failed to open the lock file: $lock_file  \n";
			exit;
		}
		$lock_type='flock';
	} else {
		// try to reuse an old connection if we've already requested this lock before:
		// This can be a big speedup:
		$lock_type='db';
		$reconnect=true;
		if (isset($_lock_resource_array[$lock_id]) and mysqli_ping($_lock_resource_array[$lock_id])){
			lock_debug("Reusing connection for $lock_id");
			$reconnect=false;
			$lock_resource=$_lock_resource_array[$lock_id];
		}
		if ($reconnect) {
			if (!$lock_resource=db_new_connect()){
				lock_debug("Could not get a mysql connection to create lock: $lock_id <br />");
				return false;
			}
		}
	}
	//  MySQL 5 versions <5.5 have big problems with blocking, so in that case do our own blocking to support these versions
	//  This polling uses noticeably more cpu than the built in blocking, but, oh well.

	// Get the MySQL version in a static variable once:
	if(!$mysql_version){
		$mysql_version=query_result("select version() as ve");
		lock_debug("get_lock: MySQL version: $mysql_version ");
	}
	// 	$mysql_version="5.1.3"; // uncomment this and and turn off flock to test workaround for MySQL 5.1 bug
	// Define version specific behavior:
	if( "$mysql_version" >= '5.5' or $lock_type=='flock' ){ // then we don't care about the bug
		$loop_timeout=false; // use mysql built-in get_lock timeout
		if ($timeout == -1) {
			$get_lock_timeout=PHP_INT_MAX;
		} else {
			$get_lock_timeout=$timeout;
		}
	} else { // we do the timeout in the polling loop, outside of get_lock
		lock_debug("get_lock: MySQL version potentially has buggy locks.  Working around it.");
		$loop_timeout=true;
		$get_lock_timeout=0;
	}
	// Do the locking:
	$start=time();
	while (!$result_val=lock_command($lock_id,$get_lock_timeout,$lock_resource,$lock_type)){
		if ($result_val === NULL ){
			echo "Error getting blocking lock: $lock_id ; This is probably a bug.";
			exit;
		} elseif ($loop_timeout) { // see if the loop timed out
			$delay=time()-$start;
			lock_debug("loop_timeout: $timeout, time is: $delay");
			if ($timeout != -1 and $delay >= $timeout ){
				//  This is not an error, this is just a timeout lock that timed out:
				lock_debug("get_lock: Timed out before acquiring lock $lock_id");
				return false;
			}  // else continue the loop
		} else { // relying on MySQL blocking, not the loop.
			if ( $timeout == -1 ){ // get_lock should have blocked forever:
				echo "Error getting a blocking lock $lock_id ; This is probably a bug.";
				exit;
			} else {
				//  This is not an error, this is just a timeout lock that timed out:
				lock_debug("get_lock: Timed out before acquiring lock, $lock_id");
				return false;
			}
		}
		usleep(50000); //wait 50ms and try again.
	}
	// store the lock handles so we can delete them, and so they don't self-destruct.
	$_lock_resource_array[$lock_id]=$lock_resource;
	//	sleep(10);
	$delay=time()-$start;
	lock_debug("get_lock: blocked for $delay  seconds");
	return true;
}


/**
 * Release a lock
 *@param string $lock_id
 *@return true if success, 2 if already released (which is also true), false if failure.
 */
function release_lock($lock_id) {
	// To release the lock, we need to use the same db connection it was created with:
	global $_lock_resource_array;
	if (get_resource_type($_lock_resource_array[$lock_id])=="stream"){// then this is a flock file lock
		//		$retval=flock($_lock_resource_array[$lock_id],LOCK_UN);
//		$retval=unlink($_lock_resource_array[$lock_id]);
		$retval=flock($_lock_resource_array[$lock_id],LOCK_UN);
// don't unlink it, it could race another fopen();fclose(); flock() and prevent another lock (tested fact).
		if(!$retval){
			lock_debug("release_lock: flock could not release the lock file: {$_lock_resource_array[$lock_id]} \n.  Was it already unlocked");
		}
		return $retval;
	}
	if (!$_lock_resource_array[$lock_id]){
		lock_debug("release_lock: Lock has already been released, $lock_id");
		return 2; // this will evaluate to true, but if caller cares, they can tell something is fishy.
	}
	// We can only release a MySQL lock if we have the connection anyway
	// So no point releasing by name.. just close the connection
	// Another process cannot release the lock (without IPC).
	if (mysqli_close($_lock_resource_array[$lock_id])){
		// check if it's free now:
		$result=query_result("SELECT IS_FREE_LOCK('{$lock_id}')");
		if ($result){
			lock_debug("release_lock: Released lock, id: $lock_id");
			$_lock_resource_array[$lock_id]=NULL; // just in case
			return true;
		} else {
			lock_debug("release_lock: Could not release the lock, id: $lock_id <br />");
			lock_debug('&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp It looks like we don\'t own it. <br />');
			return false;
		}
	} else {
		lock_debug("release_lock: mysqli_close error, could not release the lock, id: $lock_id <br />");
		return false;
	}
}

/**
 * PROBABLY OBSOLETE.. not in use... see get_multi_queue_lock() 
 * Get one of up to n locks from a group of locks with group id $group_lockid
 * This is to control the number of resources in use.
 * This version keeps trying all locks and gets the first one it finds free.
 * This keeps all locks in parallel use as long as possible,
 * but requires significant load on the mysql server.
 *@param string $group_lockid
 *@param integer $n
 *@return $lockid or false if failure
 */
function get_first_multi_lock($group_lock_id,$n) {
	$havelock=false;
	// the single blocking lock is far easier on the database if
	// we are only getting one lock anyway
	if ($n==1){
		if (get_lock($group_lock_id,-1)){
			return $group_lock_id;
		} else {
			lock_debug("Oops");
			exit;
		}
	}
	// for everything else we need to loop and wait.
	while (true) {
		for ($i=0; $i<$n; $i++){
			// make sure nobody constructs the individual id's by accident
			$lockid=$group_lock_id . '_somerandomnes90874534_' . $i;
			lock_debug("get_multi_lock: trying $group_lock_id");
			if (get_lock($lockid,0)){ // non-blocking lock
				lock_debug("get_multi_lock: got $group_lock_id #$i of $n" );
				return $lockid;
			}
			// placing the wait in the for loop has some benefits:
			// sleep 50ms.  This loop is expensive and parallel, faster seems unwise.
			usleep(50000);
		}
	}
}

/**
 * Get one of up to n locks from a group of locks with group id $group_lock_id
 * This is to control the number of resources in use
 * This version assigns (by rotation) each request to one of the n locks.
 * Once assigned a slot we wait for a blocking lock on our slot.
 * In principle this could result in unbalanced slot usage if we're unlucky,
 * but the mysql load is much lighter than continuously polling for free slots.
 * Lock delivery order is not predictable. 
 *@param string $group_lockid
 *@param integer $n
 *@return $lockid or false if failure
 */
function get_multi_queue_lock($group_lock_id,$n) {
	lock_debug("get_multi_queue_lock: $group_lock_id $n");
	if ($n>1) { //select which lock to wait on
		// get the lock counter and increment it mod n.
		if (!get_lock("lock_counter_".$group_lock_id."_90485724")){
			echo "Failed to get counter incrementlock in get_multi_lock";
			exit;
		}
		$counter_id="lock_counter_{$group_lock_id}_207234987";
		$counter=(getOption("{$counter_id}")+1) % $n;
		setOption("{$counter_id}",$counter);
		if (!release_lock("lock_counter_".$group_lock_id."_90485724")){
			echo "Failed to release counter incrementlock in get_multi_lock \n";
			exit;
		}
	} else {
		$counter=0;
	}
	if (get_lock("{$group_lock_id}_290713429_{$counter}")){
		lock_debug("get_multi_lock: got $group_lock_id #".($counter+1)." of {$n}" );
		return "{$group_lock_id}_290713429_{$counter}";
	} else {
		echo "ERROR: Blocking lock exited without success in get_multi_lock";
		exit;
	}
}
?>