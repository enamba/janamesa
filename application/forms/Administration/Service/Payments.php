<?php

/**
 * @author Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Form_Administration_Service_Payments extends Default_Forms_Base {

    public function init() {

        $subForm = new Zend_Form_SubForm();
        $payments = array_merge(
            Yourdelivery_Payment_Abstract::getPayments(), 
            Yourdelivery_Payment_Abstract::getAdditions()
        );
        foreach ($payments as $payment => $paymentName) {
            $subForm->addElement('select', $payment, array(
                'belongsTo' => "payments",
                'label' => $paymentName,
                'required' => false,
                'multiOptions' => array(
                    99 => __b("Standardeinstellung"),
                    1 => __b("Aktiviert"),
                    0 => __b("Deaktiviert")),
            ));
        }
        $this->addSubForm($subForm, 'payments');
        
        $this->addElement('select', 'default', array(
            'label' => __b('Standard Zahlart'),
            'required' => true,
            'multiOptions' => array_merge(
                array('none' => "Kein"), 
                Yourdelivery_Payment_Abstract::getPayments(), 
                Yourdelivery_Payment_Abstract::getAdditions()),
        ));

        $this->addElement('submit', __b('Speichern'));
    }

}
