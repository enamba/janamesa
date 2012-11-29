<?php

/**
 *
 * @author mlaug
 */
class Yourdelivery_Form_Administration_Order_Edit_Resend extends Yourdelivery_Form_Administration_Order_Edit_Abstract {

    /**
     * initialize resend form
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.07.2012 
     */
    public function initialize() {

        $this->setAction('/request_administration_orderedit/resend/id/' . $this->_orderObj->getId());

        $this->addElement('checkbox', 'torestaurant', array(
            'required' => true,
            'label' => __b('Lieferservice'),
            'filters' => array('StringTrim')
        ));
        $this->getElement('torestaurant')->setValue(1);

        //only for courier services
        if ($this->_orderObj->getService()->hasCourier()) {
            $this->addElement('checkbox', 'tocourier', array(
                'required' => true,
                'label' => __b('Kurier'),
                'filters' => array('StringTrim')
            ));
        }

        $this->addElement('submit', 'send', array('label' => __b('Bestellung erneut versenden')));
    }

}

?>
