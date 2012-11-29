<?php

/**
 * Description of Normalize
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
class Default_Helpers_Normalize {
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param string $number
     * @return boolean 
     */
    public static function telephone($number){    
        //get country code (de => 0049)
        $config = Zend_Registry::get('configuration');
        $countrycode = $config->locale->telcode;
        
        //do initial normalization for that number
        $number = preg_replace('/^\+/','00', $number); //replace + with 00
        $number = preg_replace('/\D/', '', $number); //remove non numeric values
        $number = str_replace(' ','', $number); //remove whitespaces
        
        //no country code? prepend it
        if ( !preg_match(sprintf('/^%s/',$countrycode), $number) ){ //no leading country code
            
            //starting zero has not been found
            preg_match('/^00|^0/', $number, $matches);
            
            //prepend country code
            if ( count($matches) != 1){
                
                //remove 00 from country code and check against current number
                $ccWithoutZero = preg_replace('/^00/', '', $countrycode); 
                
                //case one is 49 (de), missing 00 before country code
                if ( preg_match(sprintf('/^%s/',$ccWithoutZero),$number) ){
                    $number = '00' . $number;
                }
                else{
                    //case two is 151 (de), missing 0 before mobile number or local call
                    $number = $countrycode . $number;
                }
            }
            
            //customer forgot to add country code
            elseif ( $matches[0] === "0" ){ //must be strict, otherwise "0" == "00" => true
                $number = $countrycode . substr($number, 1);
            }
        }
        
        //double check ... if the number does not have a leading country code now, this should fail as well
        if ( !preg_match(sprintf('/^%s/',$countrycode), $number) ){ //no leading country code
            return false;
        }
        
        //finalize with our magic regex
        if ( preg_match('/^00(?:[0-9]){6,14}[0-9]$/', $number) ){
            return $number;
        }
        
        return false;
    }
    
    /**
     * strip non-acsii strings from email
     * @author Alex Vait <vait@lieferando.de>
     * @param string $email
     * @return sring
     */
    public static function email($email){    
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $email);
    }
     
}

?>
