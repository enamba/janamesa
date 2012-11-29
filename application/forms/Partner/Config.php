<?php

/**
 * Description of Config
 *
 * @author mlaug
 */
class Yourdelivery_Form_Partner_Config extends Default_Forms_Base {

    public function init() {
        parent::init();

        $this->addElement('checkbox', 'orderticker', array(
            'label' => __p('Benachrichtigung bei neuer Bestellung'),
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('checkbox', 'sound', array(
            'label' => __p('Sound an'),
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ));

        $this->addElement('hidden', 'type', array(
            'value' => 'config'
        ));

        $this->addElement('submit','save' , array(
            'required' => false,
            'label' => __p('Ändern'),
            'filters' => array('StringTrim')
        ));
    }

}
