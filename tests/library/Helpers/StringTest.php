<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of String
 *
 * @author matthiaslaug
 */
class StringTest extends Yourdelivery_Test{
    
    public function testUmlaute(){
        $msg = 'öäüß';
        $this->assertEquals(Default_Helpers_String::replaceUmlaute($msg),'oeaeuess');
    }
    
}

?>
