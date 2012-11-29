<?php

/**
 * Database interface for Yourdelivery_Models_DbTable_Restaurant_BillingMerge
 *
 * @copyright Yourdelivery
 * @author alex
 * @since 28.09.2010
 *
 */
class Yourdelivery_Model_DbTable_Restaurant_BillingMerge extends Default_Model_DbTable_Base {

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'billing_merge';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('billing_merge', 'billing_merge.id = ' . $id);
    }

    /**
     * find 
     * @param integer $id
     * @return void
     */
    public static function removeByParentAndChild($parentId, $childId) {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('billing_merge', 'billing_merge.parent = ' . (integer) $parentId . ' and child = ' . (integer) $childId);
    }

}
