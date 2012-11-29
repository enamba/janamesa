<?php

/**
 * @author Daniel Hahn <hahn@lieferando.de>
 */
class Yourdelivery_Model_Newsletter_OptOutReasons extends Default_Model_Base {

    /**
     * Get related table
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 21.03.2011
     * @return Yourdelivery_Model_DbTable_Newsletter_OptOutReasons
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Newsletter_OptOutReasons();
        }
        return $this->_table;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 21.03.2011
     * @return array
     */
    public static function getAll() {

        $reasonModel = new Yourdelivery_Model_DbTable_Newsletter_OptOutReasons();

        $reasons = $reasonModel->fetchAll()
                               ->toArray();

        $return = array();
        foreach ($reasons as $n) {
            if ($n['online'] == 1) {
                $return[$n['id']] = $n['reason'];
            }
        }

        return $return;
    }

}
