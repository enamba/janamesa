<?php

class Yourdelivery_Form_Api_UserSettings extends Yourdelivery_Form_User_Settings {

    private $_customerId = null;

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 01.12.2011
     *
     * @param array $options
     * @param string $customerId ID to exclude from validate email-db-record-exists
     */
    public function __construct($customerId = null, $options = null) {

        $this->_customerId = $customerId;

        parent::__construct(array(
            'name' => array('required' => true, 'validate' => true, 'customMessages' => true),
            'prename' => array('required' => true, 'validate' => true, 'customMessages' => true),
            'tel' => array('required' => true, 'validate' => true, 'customMessages' => true),
            'password' => array('required' => false, 'validate' => true, 'customMessages' => true),

        ),$options);
    }

    public function init() {
        parent::initEmail($this->_customerId);
    }

}
