<?php
error_reporting(E_ALL | E_STRICT);

// Set the private include path
$path_delimiter = PHP_OS == 'WINNT' ? ';' : ':';
ini_set('include_path','../../..' . $path_delimiter . ini_get('include_path'));


if (count($argv) != 5) {
  die("You must specify the database arguments: host database user password\n");
}
$dbhost = $argv[1];
$dbname = $argv[2];
$dbusr  = $argv[3];
$dbpwd  = $argv[4];

$dbh = mysql_connect($dbhost, $dbusr, $dbpwd);
if (!(isset($dbh) && mysql_select_db($dbname,$dbh))) {
  throw new Exception(mysql_errno() . ": " . mysql_error(), E_USER_ERROR);
}

require_once('IPC/SharedMem/MySQL.php');

$key = 'Yabadabadoo';
$value = '';
$shm = new IPC_SharedMem_MySQL($dbh, $key, array('create' => true, 'remove' => true));

$value = $shm->fetch();
print "Fetched: $value\n";

$value = 'Once upon a time';
$shm->store($value);
print "Stored: $value\n";

$value = $shm->fetch();
print "Fetched: $value\n";

$value .= " there was a wolf.";
$shm->store($value);
print "Stored: $value\n";

$value = $shm->fetch();
print "Fetched: $value\n";

$shm->transaction_finish();


?>