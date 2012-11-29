<?php
/**
 * Backlink Db Table
 * @author vpriem
 * @since 27.07.2011
 */
class Yourdelivery_Model_DbTable_Printer_Topup extends Default_Model_DbTable_Base{

    /**
     * Table name
     */
    protected $_name = 'printer_topup';

    /**
     * Primary key name
     */
    protected $_primary = 'id';

    /**
     * get all possible states of printer
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 30.08.2012
     * @return array
     */
    public static function getStates() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->from(array('ps' => 'printer_states'))
                ->order('ps.id');

        return $db->fetchAll($select);
    }    
}
