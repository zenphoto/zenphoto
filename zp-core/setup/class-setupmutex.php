<?php

/**
 * Zenphoto Mutex class
 * @author Stephen
 * @package zpcore\setup
 *
 */
class setupMutex {

	private $locked = NULL;
	private $ignoreUserAbort = NULL;
	private $mutex = NULL;
	private $lock = NULL;
	private $lockfolder;

	function __construct() {
		// if any of the construction fails, run in free mode (lock = NULL)
		if (function_exists('flock')) {
			$this->lockfolder = dirname(dirname(dirname(__FILE__))) . '/' . DATA_FOLDER . '/' . MUTEX_FOLDER;
			if (!file_exists($this->lockfolder))
				setup::mkdir_r($this->lockfolder, 0777);
			if (file_exists($this->lockfolder))
				$this->lock = 'sP';
		}
		return $this->lock;
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
			if ($this->mutex = @fopen($this->lockfolder . '/' . $this->lock, 'wb')) {
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