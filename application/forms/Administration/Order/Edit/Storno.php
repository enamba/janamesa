<?php

/**
 *
 * @author mlaug
 */
class Yourdelivery_Form_Administration_Order_Edit_Storno extends Yourdelivery_Form_Administration_Order_Edit_Abstract {

    /**
     * initialize storno form
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.07.2012 
     */
    public function initialize() {

        $this->setAction('/request_administration_orderedit/storno/id/' . $this->_orderObj->getId());

        $this->addElement('select', 'reasonId', array(
            'required' => true,
            'multiOptions' => array_merge(array(''),Yourdelivery_Model_Order_Abstract::getStornoReasons()),
            'label' => __b('Begründung')
        ));

        $this->addElement('checkbox', 'informrestaurant', array(
            'required' => false,
            'label' => __b('Restaurant benachrichtigen'),
            'filters' => array('StringTrim')
        ));        
        $this->getElement('informrestaurant')->setValue(1);

        $this->addElement('checkbox', 'informcustomer', array(
            'required' => false,
            'label' => __b('Kunde benachrichtigen'),
            'filters' => array('StringTrim')
        ));        
        $this->getElement('informcustomer')->setValue(1);

        if ($this->_orderObj->getPayment() == 'paypal') {
            $this->addElement('checkbox', 'paypal', array(
                'required' => false,
                'label' => __b('PayPal stornieren'),
                'filters' => array('StringTrim')
            ));
        }

        if ($this->_orderObj->getPayment() == 'credit') {
            $this->addElement('checkbox', 'credit', array(
                'required' => false,
                'label' => __b('Credit stornieren'),
                'filters' => array('StringTrim')
            ));
        }

        if ($this->_orderObj->getPayment() == 'ebanking') {
            $this->addElement('checkbox', 'ebanking', array(
                'required' => false,
                'label' => __b('Ebanking stornieren'),
                'filters' => array('StringTrim')
            ));
        }

        $this->addElement('submit', 'send', array('label' => __b('stornieren')));
    }

}

?>
