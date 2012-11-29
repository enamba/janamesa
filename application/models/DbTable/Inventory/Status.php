<?php
/**
 * Inventory DB Table
 * @author mlaug
 * @since 18.04.2011
 */
class Yourdelivery_Model_DbTable_Inventory_Status extends Default_Model_DbTable_Base{

    /**
     * Table name
     */
    protected $_name = 'inventory_status';

    /**
     * Primary key name
     */
    protected $_primary = 'id';
    
    public function getCurrentState($id,$type){
        return $this->getAdapter()->query(
            "SELECT * 
            FROM `inventory_status` i
            WHERE i.type = ? AND i.inventoryId = ? 
            ORDER BY i.date DESC 
            LIMIT 1", array($type, $id)
        )->fetch();
    }
    
    public function getAllStates($id, $type){
        return $this->getAdapter()->fetchAll(
            "SELECT * 
            FROM `inventory_status` i 
            INNER JOIN `admin_access_users` acu ON acu.id = i.adminId 
            WHERE i.type = ? AND i.inventoryId = ? 
            ORDER BY i.date DESC", array($type, $id)
        );
    }

}