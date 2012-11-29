<?php

/**
 * Description of Time
 * @package helper
 * @author mlaug
 */
class Default_Helpers_Time {

    public function isHappyDay($time = null){
        if ( is_null($time) ){
            $time = time();
        }
    }

}
?>
