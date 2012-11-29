<?php
// +----------------------------------------------------------------------+
// | PHP extensions shmop en sysvsem based shared memory class for PHP5.  |
// | Copyright (C) 2005 Craig Manley                                      |
// +----------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU Lesser General Public License as       |
// | published by the Free Software Foundation; either version 2.1 of the |
// | License, or (at your option) any later version.                      |
// |                                                                      |
// | This library is distributed in the hope that it will be useful, but  |
// | WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU     |
// | Lesser General Public License for more details.                      |
// |                                                                      |
// | You should have received a copy of the GNU Lesser General Public     |
// | License along with this library; if not, write to the Free Software  |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307  |
// | USA                                                                  |
// |                                                                      |
// | LGPL license URL: http://opensource.org/licenses/lgpl-license.php    |
// +----------------------------------------------------------------------+
// | Author: Craig Manley                                                 |
// +----------------------------------------------------------------------+
//
// $Id: ShmOp.php,v 1.4 2006/06/19 15:57:41 cmanley Exp $
//



/**
 * @author    Craig Manley
 * @copyright Copyright � 2005, Craig Manley. All rights reserved.
 * @package   IPC_SharedMem
 * @version   $Revision: 1.4 $
 */


/**
 * @ignore Require interface class.
 */
require_once(dirname(__FILE__) . '/SharedMem.php');


/**
 * PHP extensions shmop en sysvsem based shared memory class.
 * Makes use of the shmop and semaphore functions in PHP. These are not
 * compiled it by default so you've got to make sure they're available
 * if you want to use this class. These extensions aren't available on
 * all platforms.
 *
 * @package  IPC_SharedMem
 * @see      http://www.php.net/manual/en/ref.shmop.php
 * @see      http://www.php.net/manual/en/ref.sem.php
 */
class IPC_SharedMem_ShmOp implements IPC_ISharedMem {

  // Private members
  private $key           = null;
  private $option_perms  = null;
  private $option_remove = false;
  private $sem_id        = null; // only set if in transaction mode.

  /**
   * Class method that throws an exception if the required PHP extensions shmop and sysvsem are not loaded.
   */
  public static function check_required_ext() {
    $shmop   = extension_loaded('shmop');
    $sysvsem = extension_loaded('sysvsem');
    if ($shmop && $sysvsem) {
      return true;
    }
    $missing = array();
    if (!$shmop) {
      array_push($missing, 'shmop');
    }
    if (!$sysvsem) {
      array_push($missing, 'sysvsem');
    }
    if (count($missing) == 1) {
      throw new Exception('The PHP extension ' . $missing[0] . ' required by class ' . __CLASS__ . ' is not loaded or built into PHP.');
    }
    throw new Exception('The PHP extensions ' . implode(' and ', $missing) . ' required by class ' . __CLASS__ . ' are not loaded or built into PHP.');
  }


  /**
   * Constructor.
   *
   * If the shared memory segment with the key passed in the 1st parameter does not exist,
   * then it will be created automatically when needed. The key must be a 32 bit integer
   * or a 4 character string. If it's the latter, then it will be used to generate a
   * 32 bit integer key.
   *
   * The following options can be set in the 2nd parameter:
   * <ul>
   *  <li>perms  - the octal shared memory permissions.</li>
   *  <li>remove - boolean, delete the shared memory when this object is destroyed, default false.</li>
   * </ul>
   *
   * @param mixed $key the access key.
   * @param array $options associative array of options.
   * @return object
   */
  public function __construct($key, $options = null) {
    // Define IPC_CREAT if it is not defined already.
    defined('IPC_CREAT') || define('IPC_CREAT', 0x00001000);

    // Check the key.
    $croak = false;
    if (is_string($key)) {
      if (strlen($key) == 4) {
        //intel endian
        $key = ord(substr($key,0,1)) | (ord(substr($key,1,1))<<8) | (ord(substr($key,2,1))<<16) | (ord(substr($key,3,1)) << 24);
      }
      elseif (preg_match('/^[+-]?\d{1,11}$/', $key)) {
        $key += 0;
      }
      else {
        $croak = true;
      }
    }
    elseif (!is_int($key)) {
      $croak = true;
    }
    if ($croak) {
      throw new Exception("Key is not an integer nor a 4 character string!");
    }
    $this->key = $key;
    // Set options
    if (isset($options)) {
      if (isset($options['perms'])) {
        $this->option_perms = $options['perms'];
      }
      if (isset($options['remove'])) {
        $this->option_remove = $options['remove'];
      }
    }
    if (is_null($this->option_perms)) {
      $this->option_perms = 0666 & ~umask();
    }
  }


  /**
   * Destructor. Removes the shared memory segment if the 'remove' option is true.
   */
  public function __destruct() {
    if ($this->option_remove) {
      $this->transaction_start();
      $shm_id = $this->_open_existing();
      if (isset($shm_id)) {
        shmop_delete($shm_id);
        shmop_close($shm_id);
      }
    }
    $this->transaction_finish();
  }


  /**
   * Opens an existing shared memory segment and returns the handle.
   *
   * @return integer
   */
  protected function _open_existing() {
    $key   = $this->key;
    $perms = $this->option_perms;
    $mode  = 'w';
    $size  = 0;
    //printf("Call _open::shmop_open(%x, '%s', 0%o, %u)\n", $key, $mode, $perms, $size);
    return @shmop_open($key, $mode, $perms, $size);
  }


  /**
   * Opens an existing or creates a new memory segment with the given size.
   * Returns the handle on success, else it throws an exception.
   *
   * @param  integer $size
   * @return integer
   */
  protected function _open($size) {
    // Try to open an existing shm.
    $result = $this->_open_existing();
    if ($result) {
      if (shmop_size($result) == $size) {
        return $result;
      }
      shmop_delete($result);
      shmop_close($result);
    }
    // Create a new shm.
    $key   = $this->key;
    $perms = $this->option_perms;
    $mode = 'c';
    //printf("Call _open::shmop_open(%x, '%s', 0%o, %u)\n", $key, $mode, $perms, $size);
    $result = shmop_open($key, $mode, $perms, $size);
    if (!$result) {
      $this->transaction_finish(); // just in case the caller doesn't do it.
      throw new Exception(sprintf('shmop_open(%x, "%s", 0%o, %u) failed.', $key, $mode, $perms, $size));
    }
    //print "shmop_open() result: \"$result\"\n";
    return $result;
  }


  /**
   * Determines if this object is in transaction mode (i.e. it has a semaphore lock).
   *
   * @return boolean
   */
  public function in_transaction() {
    return isset($this->sem_id) && $this->sem_id;
  }

  /**
   * Opens and acquires a semaphore.
   *
   * @return boolean
   */
  public function transaction_start() {
    if (!$this->in_transaction()) {
      $key   = $this->key;
      $perms = $this->option_perms;
      if (!($this->sem_id = sem_get($key, 1, $perms | IPC_CREAT))) {
        throw new Exception(sprintf('sem_get(%x, 1, 0%o | IPC_CREAT) failed.', $key, $perms));
      }
      if (!sem_acquire($this->sem_id)) {
        throw new Exception(sprintf('sem_acquire(%x) failed.', $this->sem_id));
      }
      return true;
    }
    return false;
  }


  /**
   * Releases the semphore.
   *
   * @return boolean
   */
  public function transaction_finish() {
    if ($this->in_transaction()) {
      $result = sem_release($this->sem_id);
      if ($this->option_remove) {
        sem_remove($this->sem_id);
      }
      $this->sem_id = null;
      return $result;
    }
    return false;
  }


  /**
   * Returns all data from the shared memory segment.
   * If this is called while not in transaction mode, then a
   * transaction is automatically used within this call.
   *
   * @return string
   */
  public function fetch() {
    $atomic = !$this->in_transaction();
    if ($atomic) {
      $this->transaction_start();
    }
    $shm_id = $this->_open_existing();
    $result = '';
    if ($shm_id) {
      $result = shmop_read($shm_id, 0, shmop_size($shm_id));
      shmop_close($shm_id);
    }
    if ($atomic) {
      $this->transaction_finish();
    }
    return $result;
  }


  /**
   * Writes the given string to the shared memory segment.
   * If this is called while not in transaction mode, then an
   * exclusive transaction is automatically used within this call.
   * Returns the number of bytes written.
   *
   * @param string $value
   * @return integer
   */
  public function store($value) {
    $atomic = !$this->in_transaction();
    if ($atomic) {
      $this->transaction_start();
    }
    $shm_id = $this->_open(strlen($value));
    $result = shmop_write($shm_id, $value, 0);
    shmop_close($shm_id);
    if ($atomic) {
      $this->transaction_finish();
    }
    return $result;
  }

}



/**
 * @ignore Check that the shmop and sysvsem extensions are loaded.
 */
IPC_SharedMem_ShmOp::check_required_ext();

?>