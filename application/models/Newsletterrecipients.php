<?php

class Yourdelivery_Model_Newsletterrecipients extends Default_Model_Base {

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.10.2010
     * @param string $email
     * @return void
     */
    public function __construct($id = null) {

        if ($id === null) {
            return;
        }

        parent::__construct($id);
    }

    /**
     * get related table
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.10.2010
     * @return Yourdelivery_Model_DbTable_Newsletterrecipients
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Newsletterrecipients();
        }
        return $this->_table;
    }

}

