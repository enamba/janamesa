<?php

/**
 * 
 *
 * @copyright   Yourdelivery
 * @author	Daniel
 * @since 26.10.2011
 */
class Yourdelivery_Model_DbTable_Restaurant_StatusHistory extends Default_Model_DbTable_Base {

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'restaurant_status_history';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    
    /**
     *  Save Status Change in Table - Changes will be added to status entry in corresponding Fields
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.10.2011
     * @param int $newStatus
     * @param int $oldStatus 
     * @return boolean
     */    
    public static function logStatusChange($newStatus, $oldStatus = false) {
        
        if($newStatus == $oldStatus) {
            return false;
        }
        
        
        if ($oldStatus !== false) {            
              self::updateRow($oldStatus, "delCount");
        }        
        self::updateRow($newStatus, "addCount");
        
        
        return true;
    }
    
    /**
     * Update or Insert Status Entry per day
     * 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 26.10.2011
     * @param int $status
     * @param string $type 
     */
    protected static function updateRow($status, $type) {
        $db = Zend_Registry::get('dbAdapter');
        
        $status = strval($status);
        
        $select = $db->select()
                              ->from('restaurant_status_history')
                              ->where('status=?', $status)->where('DATE(created) = DATE(NOW())');

        $result = $db->fetchAll($select);

        if (count($result) == 0) {
            $db->insert('restaurant_status_history', array('status' => $status, $type => 1));
        } else {


            $data = $result[0];
            $data[$type] +=1;
            $id = $data['id'];
            $db->update('restaurant_status_history', $data, "id=".$data['id']);
        }
    }

}
