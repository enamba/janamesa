<?php
/**
 * @author vpriem
 * @since 23.02.2011
 */
class Default_Helpers_Random{

    /**
     * Generate a random color
     * @author vpriem
     * @since 23.02.2011
     * @return string
     */
    public static function color(){
        
        $chars = "0123456789abcdef";
        $str = "";
        for ($i = 0; $i < 6; $i++) {
            $str .= $chars{mt_rand(0, strlen($chars) - 1)};
        }
        return $str;
        
    }

}
