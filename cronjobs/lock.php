<?php

/*
  CLASS ExclusiveLock
  Description
  ==================================================================
  This is a pseudo implementation of mutex since php does not have
  any thread synchronization objects
  This class uses flock() as a base to provide locking functionality.
  Lock will be released in following cases
  1 - user calls unlock
  2 - when this lock object gets deleted
  3 - when request or script ends
  ==================================================================
  Usage:

  //get the lock
  $lock = new ExclusiveLock( "mylock", FALSE);

  //lock
  if( $lock->lock( ) == FALSE )
  error("Locking failed");
  //--
  //Do your work here
  //--

  //unlock
  $lock->unlock();
  ===================================================================
 */

class ExclusiveLock {

    protected $key = null;  //user given value
    protected $file = null;  //resource to lock
    protected $own = FALSE; //have we locked resource

    function __construct($key) {
        $this->key = $key;
        //create a new resource or get exisitng with same key
        $this->file = fopen(APPLICATION_PATH . '/../cronjobs/locks/' . "$key.lockfile", 'w+');
    }

    function __destruct() {
        if ($this->own == TRUE)
            $this->unlock();
    }

    function lock() {
        if (!flock($this->file, LOCK_EX | LOCK_NB)) { //failed
            $key = $this->key;
            clog("warn","ExclusiveLock::acquire_lock FAILED to acquire lock [$key]");
            return FALSE;
        }
        ftruncate($this->file, 0); // truncate file
        //write something to just help debugging
        fwrite($this->file, "Locked\n");
        fflush($this->file);

        $this->own = TRUE;
        return $this->own;
    }

    function unlock() {
        $key = $this->key;
        if ($this->own == TRUE) {
            if (!flock($this->file, LOCK_UN)) { //failed
                clog("warn","ExclusiveLock::lock FAILED to release lock [$key]");
                return FALSE;
            }
            ftruncate($this->file, 0); // truncate file
            //write something to just help debugging
            fwrite($this->file, "Unlocked\n");
            fflush($this->file);
        } else {
            clog("warn","ExclusiveLock::unlock called on [$key] but its not acquired by caller");
        }
        $this->own = FALSE;
        return $this->own;
    }

}

;