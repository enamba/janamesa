<?php

function smarty_modifier_inttoprice($price, $precision = 2, $seperator = ","){
    return intToPrice($price, $precision, $seperator);
}

?>
