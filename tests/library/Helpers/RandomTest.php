<?php
/**
 * @author vpriem
 * @since 23.02.2011
 */
class HelpersRandomTest extends Yourdelivery_Test{

    /**
     * @author vpriem
     * @since 23.02.2011
     */
    public function testColor(){

        $this->assertRegExp('/[a-f0-9]{6}/', Default_Helpers_Random::color());

    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.08.2011
     */
    public function testGenerateRandomStringWithDefaultValues(){
        $chars ="abcedfghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRTSUVWXYZ0123456789";
        
        $randomString = Default_Helper::generateRandomString();
        $this->assertEquals(8, strlen($randomString));
        // check, that every char of generated string is in default chars
        for($i, $l = strlen($randomString); $i<$l; $i++){
            $this->assertTrue(stristr($chars,$randomString[$i]) !== false, 'not in chars: '.$randomString[$i]);
        }
    }
    
    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.08.2011
     */
    public function testGenerateRandomStringWithGivenValues(){
        $chars = 'fubar123456';
        
        $randomString = Default_Helper::generateRandomString(4);
        $this->assertEquals(4,strlen($randomString));
        
        $randomString = Default_Helper::generateRandomString(15, $chars);
        $this->assertEquals(15,strlen($randomString));
        
        // check, that every char of generated string is in given chars
        for($i, $l = strlen($randomString); $i<$l; $i++){
            $this->assertTrue(stristr($chars,$randomString[$i]) !== false, 'not in chars: '.$randomString[$i]);
        }
    }

}