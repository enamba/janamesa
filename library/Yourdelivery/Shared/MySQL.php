<?php
// +----------------------------------------------------------------------+
// | PHP mysql extension based shared memory class for PHP5.              |
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
// $Id: MySQL.php,v 1.2 2005/01/09 22:39:54 cmanley Exp $
//



/**
 * @author    Craig Manley
 * @copyright Copyright � 2005, Craig Manley. All rights reserved.
 * @package   IPC_SharedMem
 * @version   $Revision: 1.2 $
 */


/**
 * @ignore Require interface class.
 */
require_once(dirname(__FILE__) . '/../SharedMem.php');


/**
 * PHP mysql extension based shared memory class.
 * Makes use of the mysql_* functions and a mysql database as a form of shared memory.
 * WARNING: Old versions of Mysql (I think <= v3.53) occasionally hang when using named locks.
 *
 * @package  IPC_SharedMem
 * @see      http://www.php.net/manual/en/ref.mysql.php
 */
class IPC_SharedMem_MySQL implements IPC_ISharedMem {

  // Private members
  private $key = null;
  private $dbh = null;
  private $lock_name = null;
  private $option_table   = 'shared_memory';
  private $option_timeout = 10;
  private $option_remove  = false;
  private $locked = false; // only set if in transaction mode.


  /**
   * Constructor.
   *
   * The table being shared must contain 2 fields:
   * <ul>
   *  <li>id   - the primary key.</li>
   *  <li>data - the blob field which contains the shared data.</li>
   * </ul>
   *
   * The following options can be set in the 2nd parameter:
   * <ul>
   *  <li>create - create the table if it does not exist, default true.</li>
   *  <li>remove - boolean, delete the shared record when this object is destroyed, default false.</li>
   *  <li>table  - the table name to use as shared memory. Default is 'SharedMemory'.</li>
   *  <li>timeout - the maximum time in seconds to wait for a record lock, default 10.</li>
   * </ul>
   *
   * @param resource $dbh a mysql resource link connected to a database.
   * @param string $key the primary key of the record to share (case sensitive, max 32 chars).
   * @param array $options associative array of options.
   * @return object
   */
  public function __construct($dbh, $key, $options = null) {
    $this->dbh = $dbh;
    $this->key = $key;
    // Set options
    if (isset($options)) {
      if (isset($options['remove'])) {
        $this->option_remove = $options['remove'];
      }
      if (isset($options['table'])) {
        $this->option_table = $options['table'];
      }
      if (isset($options['timeout']) && preg_match('^\d{1,10}$/', $options['timeout']) && ($options['timeout'] > 0)) {
        $this->option_table = $options['timeout'];
      }
      if (isset($options['create']) && $options['create']) {
        // Create the table if it does not exist
        $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->option_table . ' (id CHAR(32) BINARY NOT NULL PRIMARY KEY, data MEDIUMBLOB)';
        $sth = mysql_query($sql,$dbh);
        if (!isset($sth) or ($sth === false)) {
          throw new Exception('Failed to create shared memory table. Error: ' . mysql_errno($dbh) . ": " . mysql_error($dbh));
        }
      }
    }
    $this->lock_name = $this->option_table . "_$key";
  }


  /**
   * Destructor. Removes the shared memory record if the 'remove' option is true.
   */
  public function __destruct() {
    if ($this->option_remove) {
      $dbh = $this->dbh;
      $sql = 'DELETE FROM ' . $this->option_table . ' WHERE id="' . mysql_real_escape_string($this->key, $dbh) . '"';
      $sth = mysql_query($sql,$dbh);
      if (!isset($sth) or ($sth === false)) {
        throw new Exception(mysql_errno($dbh) . ": " . mysql_error($dbh));
      }
    }
    $this->transaction_finish();
  }


  /**
   * Determines if this object is in transaction mode (i.e. it has a record lock).
   *
   * @return boolean
   */
  public function in_transaction() {
    return isset($this->locked) && $this->locked;
  }

  /**
   * Locks the record.
   *
   * @return boolean
   */
  public function transaction_start() {
    if (!$this->in_transaction()) {
      $dbh = $this->dbh;
      $sql = 'SELECT GET_LOCK("' . mysql_real_escape_string($this->lock_name, $dbh) . '", ' . $this->option_timeout . ')';
      $sth = mysql_query($sql,$dbh);
      if (!isset($sth) or ($sth === false)) {
        throw new Exception(mysql_errno($dbh) . ": " . mysql_error($dbh));
      }
      list($result) = mysql_fetch_row($sth);
      mysql_free_result($sth);
      if (!$result) {
        throw new Exception('Timeout waiting for the MySQL named lock "' . $this->lock_name . '".');
      }
      $this->locked = $result;
      return $result;
    }
    return false;
  }


  /**
   * Releases the record lock.
   *
   * @return boolean
   */
  public function transaction_finish() {
    if ($this->in_transaction()) {
      $dbh = $this->dbh;
      $sql = 'SELECT RELEASE_LOCK("' . mysql_real_escape_string($this->lock_name, $dbh) . '")';
      $sth = mysql_query($sql,$dbh);
      if (!isset($sth) or ($sth === false)) {
        throw new Exception(mysql_errno($dbh) . ": " . mysql_error($dbh));
      }
      $this->locked = false;
      return true;
    }
    return false;
  }


  /**
   * Returns all data from the shared memory record.
   *
   * @return string
   */
  public function fetch() {
    $dbh = $this->dbh;
    $sql = 'SELECT data FROM ' . $this->option_table . ' WHERE id="' . mysql_real_escape_string($this->key, $dbh) . '" LIMIT 1';
    $sth = mysql_query($sql,$this->dbh);
    if (!isset($sth) or ($sth === false)) {
      throw new Exception(mysql_errno($dbh) . ": " . mysql_error($dbh));
    }
    list($result) = mysql_fetch_row($sth);
    mysql_free_result($sth);
    if (!isset($result)) {
      $result = '';
    }
    return $result;
  }


  /**
   * Writes the given string to the shared memory record.
   *
   * @param string $value
   * @return integer
   */
  public function store($value) {
    $dbh = $this->dbh;
    $sql = 'REPLACE INTO ' . $this->option_table . ' (id,data) VALUES ("' . mysql_real_escape_string($this->key, $dbh) . '", "' . mysql_real_escape_string($value, $dbh) . '")';
    $sth = mysql_query($sql,$dbh);
    if (!isset($sth) or ($sth === false)) {
      throw new Exception(mysql_errno($dbh) . ": " . mysql_error($dbh));
    }
    return strlen($value);
  }

}


?>