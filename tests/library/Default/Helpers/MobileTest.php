<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 */
class Default_Helpers_MobileTest extends Yourdelivery_Test {
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 24.02.2012
     */
    public function testIsMobile() {

        // DE
        $this->assertFalse(Default_Helpers_Phone::isMobile("004914123456789"));
        $this->assertTrue(Default_Helpers_Phone::isMobile( "004915123456789"));
        $this->assertTrue(Default_Helpers_Phone::isMobile( "004916123456789"));
        $this->assertTrue(Default_Helpers_Phone::isMobile( "004917123456789"));
        $this->assertFalse(Default_Helpers_Phone::isMobile("004918123456789"));

        // FR
        $this->assertFalse(Default_Helpers_Phone::isMobile("00335123456789"));
        $this->assertTrue(Default_Helpers_Phone::isMobile( "00336123456789"));
        $this->assertTrue(Default_Helpers_Phone::isMobile( "00337123456789"));
        $this->assertFalse(Default_Helpers_Phone::isMobile("00338123456789"));

        // AT
        $this->assertFalse(Default_Helpers_Phone::isMobile("0043643123456789"));
        $this->assertTrue(Default_Helpers_Phone::isMobile( "0043644123456789"));
        $this->assertFalse(Default_Helpers_Phone::isMobile("0043645123456789"));

        // CH
        $this->assertFalse(Default_Helpers_Phone::isMobile("004175123456789"));
        $this->assertTrue(Default_Helpers_Phone::isMobile( "004176123456789"));
        $this->assertTrue(Default_Helpers_Phone::isMobile( "004177123456789"));
        $this->assertTrue(Default_Helpers_Phone::isMobile( "004178123456789"));
        $this->assertTrue(Default_Helpers_Phone::isMobile( "004179123456789"));
        $this->assertFalse(Default_Helpers_Phone::isMobile("004180123456789"));
        
        // PL
        $this->assertFalse(Default_Helpers_Phone::isMobile("004849123456789"));
        $this->assertTrue(Default_Helpers_Phone::isMobile( "004850123456789"));
        $this->assertFalse(Default_Helpers_Phone::isMobile("004852123456789"));
    }
}
