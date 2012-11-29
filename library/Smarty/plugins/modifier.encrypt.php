<?php

function smarty_modifier_encrypt($string)
{
    return base64_encode($string);
}

?>
