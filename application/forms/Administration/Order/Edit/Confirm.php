<?php

/**
 *
 * @author mlaug
 */
class Yourdelivery_Form_Administration_Order_Edit_Confirm extends Yourdelivery_Form_Administration_Order_Edit_Abstract {

    /**
     * initialize confirm form
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.07.2012 
     */
    public function initialize() {
        $this->setAction('/request_administration_orderedit/confirm/id/' . $this->_orderObj->getId());
        $this->addElement('submit', 'send', array('label' => __b('bestätigen')));
    }

}