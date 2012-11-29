<?php
/**
 * @author Alex Vait <vait@lieferano.de>
 * @since 02.08.2012
 */
class HelpersPasswordTest extends Yourdelivery_Test{

    /**
     * Generate password with different requirements
     * @author Alex Vait <vait@lieferano.de>
     * @since 02.08.2012
     */
    public function testGenerate(){
        // generate password from default set of legal chars with at least one capital letter, two small letters, two digits
        $password = Default_Helpers_Password::generatePassword(15, null, 1, 1, 1);
        
        $this->assertEquals(15, strlen($password));
        
        $countLowercase = 0;
        $countCapital = 0;
        $countDigits = 0;
        
        // check that every char in the generated password is in the legal charset
        // also count lowercase letters, capital letters and digits
        for($i, $l = strlen($password); $i<$l; $i++){   
            $letter = substr($password, $i, 1);
            $this->assertTrue(strpos(Default_Helpers_Password::LEGALCHARS, $letter) !== false);

            if (ctype_lower($letter)) {
                $countLowercase++;
            }
            
            if (ctype_upper($letter)) {
                $countCapital++;
            }
            
            if (ctype_digit($letter)) {
                $countDigits++;
            }            
        }

        // count of all knid of chars must be at least 2
        $this->assertGreaterThan(0, $countLowercase);
        $this->assertGreaterThan(0, $countCapital);
        $this->assertGreaterThan(0, $countDigits);
        
        
    }

}