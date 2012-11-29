<?php

/**
 * Description of PaypalBacklistForm
 *
 * @author mlaug
 */
class Yourdelivery_Form_Administration_Order_Edit_Paypal extends Yourdelivery_Form_Administration_Order_Edit_Abstract {

    /**
     * initialize paypal form
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.07.2012 
     */
    public function initialize() {
        $payerId = $this->createElement('hidden', 'payerId');
        $payerId->removeDecorator('DtDWrapper');
        $payerId->removeDecorator('HtmlTag');
        $payerId->removeDecorator('Label');
        $payerId->setValue($this->_orderObj->getPayerId());

        if ($this->_orderObj->isBlacklisted()) {
            $this->setAction('/request_administration_orderedit/whitelist/id/' . $this->_orderObj->getId());
            $this->addElement('submit', 'send', array('label' => __b('User entsperren')));
        } else {
            $this->setAction('/request_administration_orderedit/block/id/' . $this->_orderObj->getId());
            $this->addElement('submit', 'send', array('label' => __b('User sperren')));
        }
    }

}

?>
