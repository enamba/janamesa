<?php
error_reporting(E_ALL | E_STRICT);

// Set the private include path
$path_delimiter = PHP_OS == 'WINNT' ? ';' : ':';
ini_set('include_path','../../..' . $path_delimiter . ini_get('include_path'));


require_once('IPC/SharedMem/ShmOp.php');

$value = '';
$shm = new IPC_SharedMem_ShmOp('TEST', array('size' => 40, 'remove' => false));


$shm->transaction_start();
try {
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
}
catch (Exception $e) {
  $shm->transaction_finish();
  throw $e;
}


?>