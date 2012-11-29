<?php

/**
 * Description of Warehouse
 *
 * @author mlaug
 */
class YourdeliveryDataWarehouseTest extends Yourdelivery_Test {
    
    /**
     * @modified Felix Haferkorn <haferkorn@lieferando.de>, 02.01.2012
     */
    public function testRegenerate(){
        $db = Zend_Registry::get('dbAdapter');
        
        $view = "view_service_top_10";
        $db->query(sprintf('DROP TABLE IF EXISTS `data_%s`', $view));
        
        $warehouse = new Yourdelivery_Data_Warehouse();
        $warehouse->setView($view);
        $this->assertTrue($warehouse->regenerate());
        
        // throws Exception if actrion failed
        $db->query(sprintf('SELECT * FROM `view_service_top_10`'));
    }
    
    
    public function testNotRegenerate(){
        $warehouse = new Yourdelivery_Data_Warehouse();
        $warehouse->setView('view_fubar');
        $this->assertFalse($warehouse->regenerate());
    }
    
}