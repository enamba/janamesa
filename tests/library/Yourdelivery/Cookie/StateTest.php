<?php

/**
 * Description of Warehouse
 *
 * @author mlaug
 */
class YourdeliveryStateTest extends Yourdelivery_Test {
    
    public function testCustomer(){
        $cookie = Yourdelivery_Cookie::factory('yd-customer');
        $this->assertTrue($cookie instanceof Yourdelivery_Cookie);
        $this->assertEquals('yd-customer',$cookie->getName());
        $this->assertFalse($cookie->set('samson','tiffy'));
        $this->assertTrue($cookie->set('name','Matthias'));
        $this->assertEquals('Matthias',$cookie->get('name'));
        $cookie->save();
        $new_cookie = Yourdelivery_Cookie::factory('yd-customer');
        $this->assertEquals('Matthias',$cookie->get('name'));
    }
    
    public function testState(){
        $cookie = Yourdelivery_Cookie::factory('yd-state');
        $this->assertTrue($cookie instanceof Yourdelivery_Cookie);
        $this->assertEquals('yd-state',$cookie->getName());  
        $this->assertFalse($cookie->set('samson','tiffy'));
        $this->assertTrue($cookie->set('mode','comp'));
        $this->assertEquals('comp',$cookie->get('mode'));
        $cookie->save();
        $new_cookie = Yourdelivery_Cookie::factory('yd-state');
        $this->assertEquals('comp',$cookie->get('mode'));
    }
    
    
}

?>
