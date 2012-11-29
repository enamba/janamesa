<?php

/**
 * @author Alex Vait <vait@lieferando.de>
 * @since 28.08.2012
 */
class Yourdelivery_Model_Admin_Access_UserGroupNn extends Default_Model_Base {

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 28.08.2012
     * @return Yourdelivery_Model_DbTable_Admin_Access_UserGroupNn
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Admin_Access_UserGroupNn();
        }

        return $this->_table;
    }

}