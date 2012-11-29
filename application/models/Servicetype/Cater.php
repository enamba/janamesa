<?php
/**
 * Description of Cater
 *
 * @author mlaug
 */
class Yourdelivery_Model_Servicetype_Cater extends Yourdelivery_Model_Servicetype_Abstract {

    protected $_type = self::CATER;

    protected $_typeId = 2;

    /**
     * get servicetype name
     * @author mlaug
     * @return string
     */
    public function getServiceName(){
        return __('Caterer');
    }

    /**
     * get product name
     * @author mlaug
     * @return string
     */
    public function getSellingName(){
        return __('Gerichte');
    }

}
