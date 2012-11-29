<?php
/**
 * @author vpriem
 * @since 02.11.2010
 */
class HelpersNormalizeTest extends Yourdelivery_Test{

    public static function numbers2normalize(){
        return array(
            array('004915114534408a','004915114534408'),
            array('015114534408','004915114534408'),
            array('+4915114534408', '004915114534408'),
            array('01738422614','00491738422614'),
            array('0521/5462312','00495215462312'),
            array('0341 9261166','0493419261166'),
            array('0711 - 50 88 55 24','004971150885524'),
            array('15114534408','004915114534408'),
            array('4915114534408','004915114534408')
        );
    }
    
    public static function numbers2fail(){
        return array(
            array('132112321132432864328')
        );
    }
    
    /**
     * @dataProvider numbers2normalize
     * @author Matthias Laug <laug@lieferano.de>
     * @since 02.11.2010
     */
    public function testTelephon($wrong, $correct){
        $this->assertEquals($correct, Default_Helpers_Normalize::telephone($wrong));
    }
    
    /**
     * @dataProvider numbers2fail
     * @author Matthias Laug <laug@lieferano.de>
     * @since 02.11.2010
     */
    public function testTelephonFail($fail){
        $this->assertFalse(Default_Helpers_Normalize::telephone($fail));
    }

}