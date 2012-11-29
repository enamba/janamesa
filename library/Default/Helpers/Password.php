<?php

/**
 * password generator
 * @author Alex Vait <vait@lieferando.de>
 * @since 01.08.2012
 */
class Default_Helpers_Password {
    
// digits are three time for equal possibility of chosing the letter from lowercase, uppercase and digits
    const LEGALCHARS = "acefghjklmnpqrtuvwxyzACEFGHJKLMNPQRTUVWXYZ234679234679234679";
    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 01.08.2012
     * @param $length int the length of generated password
     * @param $charset array the characters, which will  be used to create the password
     * @param $minLowercaseLetters int how many lowercase letters at least must be contained in the password
     * @param $minCapitalLetter int how many capital letters at least must be contained in the password
     * @param $minNumbers int how many numbers at least must be contained in the password
     * 
     * @return string random password
    */
    public static function generatePassword($length = 10, $charset = null, $minLowercaseLetters=0, $minCapitalLetters=0, $minNumbers=0){
        
        if (is_null($charset)) {
            $charset = self::LEGALCHARS;
        }
        
        // hwo many times we shall try to generate the password with all requirements
        $deadLockPreventer = 100;
        
        do {
            $password = Default_Helper::generateRandomString($length, $charset);
            
            /*
             * difference between length of the password and the length of the password stripped of capital letters
             * this difference is the count of capital letters
             */
            $countCapitalLetters = (strlen($password) - strlen(preg_replace("[A-Z]+", "", $password)));
            $conditionCapitalLetters = ($countCapitalLetters < $minCapitalLetters);

            // the same as above - the count of lowercase letters
            $countLowercaseLetters = (strlen($password) - strlen(preg_replace("[a-z]+", "", $password)));
            $conditionLowercaseLetters = ($countLowercaseLetters < $minLowercaseLetters);
            
            // the same as above - the count of numbers in the password
            $countNumbers = (strlen($password) - strlen(preg_replace("[0-9]+", "", $password)));            
            $conditionNumbers = ($countNumbers < $minNumbers);
            
            $deadLockPreventer--;
        }
        while (($conditionCapitalLetters || $conditionNumbers || $conditionLowercaseLetters) && ($deadLockPreventer>0));
                
        return $password;
    }

}
