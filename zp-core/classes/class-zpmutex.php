<?php
/**
 * Zenphoto Mutex class
 * @author Stephen Billard (sbillard)
 * 
 * @package zpcore\classes\helpers
 */
class zpMutex {

	private $locked = NULL;
	private $ignoreUserAbort = NULL;
	private $mutex = NULL;
	private $lock = NULL;

	function __construct($lock = 'zP', $concurrent = NULL) {
		// if any of the construction fails, run in free mode (lock = NULL)
		if (function_exists('flock') && defined('SERVERPATH')) {
			if ($concurrent) {
				If ($subLock = self::which_lock($lock, $concurrent)) {
					$this->lock = $lock . '_' . $subLock;
				}
			} else {
				$this->lock = $lock;
			}
		}
		return $this->lock;
	}

	// returns the integer id of the lock to be obtained
	// rotates locks sequentially mod $concurrent
	private static function which_lock($lock, $concurrent) {
		global $_zp_mutex;
		$counter_file = SERVERPATH . '/' . DATA_FOLDER . '/' . MUTEX_FOLDER . '/' . $lock . '_counter';
		$_zp_mutex->lock();
		// increment the lock id:
		if (@file_put_contents($counter_file, $count = (((int) @file_get_contents($counter_file)) + 1) % $concurrent)) {
			$count++;
		} else {
			$count = false;
		}
		$_zp_mutex->unlock();
		return $count;
	}

	function __destruct() {
		if ($this->locked) {
			$this->unlock();
		}
	}

	public function lock() {
		//if "flock" is not supported run un-serialized
		//Only lock an unlocked mutex, we don't support recursive mutex'es
		if (!$this->locked && $this->lock) {
			if ($this->mutex = @fopen(SERVERPATH . '/' . DATA_FOLDER . '/' . MUTEX_FOLDER . '/' . $this->lock, 'wb')) {
				if (flock($this->mutex, LOCK_EX)) {
					$this->locked = true;
					//We are entering a critical section so we need to change the ignore_user_abort setting so that the
					//script doesn't stop in the critical section.
					$this->ignoreUserAbort = ignore_user_abort(true);
				}
			}
		}
		return $this->locked;
	}

	/**
	 * 	Unlock the mutex.
	 */
	public function unlock() {
		if ($this->locked) {
			//Only unlock a locked mutex.
			$this->locked = false;
			ignore_user_abort($this->ignoreUserAbort); //Restore the ignore_user_abort setting.
			flock($this->mutex, LOCK_UN);
			fclose($this->mutex);
			return true;
		}
		return false;
	}

}