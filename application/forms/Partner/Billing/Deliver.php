<?php

/**
 * Description of NewPassForm
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 */
class Yourdelivery_Form_Partner_Billing_Deliver extends Default_Forms_Base {

    public function init() {

        $this->setAction("/partner_request_billing/deliver")
             ->setMethod('post');

        $this->addElement('checkbox', 'email', array(
            'label' => __p('E-Mail und Partner-Konto (gratis)'),
            'checkedValue' => 1,
            'uncheckedValue' => 0,
        ));

        $this->addElement('checkbox', 'fax', array(
            'label' => __p('Fax (2,50 € pro Rechung)'),
            'checkedValue' => 1,
            'uncheckedValue' => 0,
        ));

        $this->addElement('checkbox', 'post', array(
            'label' => __p('Post (6 € pro Rechnung)'),
            'checkedValue' => 1,
            'uncheckedValue' => 0,
        ));
    }

}