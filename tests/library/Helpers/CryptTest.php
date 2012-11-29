<?php
/**
 * @author vpriem
 * @since 02.11.2010
 */
class HelpersCryptTest extends Yourdelivery_Test{

    /**
     * @author vpriem
     * @since 02.11.2010
     */
    public function testHash(){

        $this->assertEquals(
            Default_Helpers_Crypt::hash(9149),
            "18c2f2438fc778dae75c9703319b6b07"
        );

    }

}