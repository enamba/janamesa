<?php

/**
 *
 * @author MAtthias Laug <laug@lieferando.de>
 */
abstract class Yourdelivery_Form_Administration_Order_Edit_Abstract extends Default_Forms_Base {

    protected $_orderObj = null;

    /**
     * set order and initialize the form
     * 
     * @author MAtthias Laug <laug@lieferando.de>
     * @since 24.07.2012
     * @param Yourdelivery_Model_Order $order
     * @return \Yourdelivery_Form_Administration_Order_Edit_Storno 
     */
    public function setOrder(Yourdelivery_Model_Order $order) {
        $this->_orderObj = $order;
        $this->initialize();
        return $this;
    }
    
    abstract function initialize();
    
}