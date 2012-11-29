<?php
/**
 * Description of Cater
 *
 * @author mlaug
 */
class Yourdelivery_Model_Servicetype_Fruit extends Yourdelivery_Model_Servicetype_Abstract {

    protected $_type = self::FRUIT;

    protected $_typeId = 4;

    /**
     * get servicetype name
     * @author mlaug
     * @return string
     */
    public function getServiceName(){
        return __('Obsthändler');
    }

    /**
     * get product name
     * @author mlaug
     * @return string
     */
    public function getSellingName(){
        return __('Obst');
    }
    
}
