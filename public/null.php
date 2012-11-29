<?php
require_once('../library/FirePHPCore/FirePHP.class.php');
$firephp = FirePHP::getInstance(true);
$firephp->log('null.php retrieves informations');
$firephp->log($_GET);

header("content-type:image/png");
$im = imagecreate(1, 1);
$white = imagecolorallocate($im,0,0,0);
imagepng($im);
imagedestroy($im);