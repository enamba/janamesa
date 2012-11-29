<?php

/**
 *
 * @author mlaug
 */
class Yourdelivery_Form_Administration_Order_Edit_Payment extends Yourdelivery_Form_Administration_Order_Edit_Abstract {

    /**
     * initialize storno form
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.07.2012 
     */
    public function initialize() {
        $this->setAction('/request_administration_orderedit/changepayment/id/' . $this->_orderObj->getId());

        $this->addElement('select', 'payment', array(
            'required' => true,
            'multiOptions' => array('bar' => __b('Barzahlung')),
            'label' => __b('Zahlungsart')
        ));

        $this->addElement('submit', 'send', array('label' => __b('Zahlungsart ändern')));
    }

}

?>
