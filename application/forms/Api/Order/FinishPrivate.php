<?php

/**
 * Description of API FinishNotRegistered
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 29.12.2011
 */
class Yourdelivery_Form_Api_Order_FinishPrivate extends Yourdelivery_Form_Order_Finish_Abstract {

    public function init() {

        parent::initPrename();
        parent::initName();
        parent::initTel();
        parent::initStreetHausNr();
        // email without blacklist check
        parent::initEmail(true, true, true, false);

        $this->additional();
        $this->payment();

        // check for serviceId exits
        $this->addElement('text', 'serviceId', array(
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                'NotEmpty',
                array('validator' => 'Db_RecordExists', 'options' => array('restaurants', 'id'))
            )
        ));

        $this->getElement('serviceId')->
                getValidator('NotEmpty')
                ->setMessage(__('Kein Lieferdienst übergeben.'), Zend_Validate_NotEmpty::IS_EMPTY);
        $this->getElement('serviceId')->
                getValidator('Db_RecordExists')
                ->setMessage(__('Der Lieferdienst existiert nicht.'), Zend_Validate_Db_RecordExists::ERROR_NO_RECORD_FOUND);

        // check for empty meals tag
        $this->addElement('text', 'meals', array(
            'required' => true,
            'validators' => array(
                'NotEmpty'
            )
        ));

        $this->getElement('meals')->
                getValidator('NotEmpty')
                ->setMessage(__('Keine Speisen übergeben.'), Zend_Validate_NotEmpty::IS_EMPTY);
    }

}

