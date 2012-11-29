<?php

$msg = $_GET['description'];
$url = $_GET['url'];
$line = $_GET['line'];
$parent = $_GET['parent_url'];
$user_agent = $_GET['user_agent'];

if ( $msg == 'Error loading script' || $msg == 'Script error' || $msg == 'Script error.'){
    //thank you, but no thanks
}
else{
    $fp = fopen(sprintf('../application/logs/javascript-errors-%s.log', date('d-m-Y')), 'a+');
    fwrite($fp, sprintf("TIME: %s \n MESSAGE: %s \n URL: %s \n LINE: %s \n PARENTURL: %s \n USERAGENT: %s \n\n", date('d-m-Y H:i:s'), $msg, $url, $line, $parent, $user_agent));
    fclose($fp);
}