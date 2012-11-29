<?php
/**
 * Description of Cater
 *
 * @author mlaug
 */
class Yourdelivery_Model_Servicetype_Great extends Yourdelivery_Model_Servicetype_Abstract {

    protected $_type = self::GREAT;

    protected $_typeId = 3;

    /**
     * get name of servicetype
     * @author mlaug
     * @return string
     */
    public function getServiceName(){
        return __('Großhändler');
    }

    /**
     * get name of product
     * @author mlaug
     * @return string
     */
    public function getSellingName(){
        return __('Getränke');
    }

}
