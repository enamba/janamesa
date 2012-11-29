<?php
/**
 * @author Alex Vait <vait@lieferando.de>
 * @since 30.08.2012
 */
class Yourdelivery_Model_DbTable_Printer_StatesHistory extends Default_Model_DbTable_Base{

    /**
     * Table name
     * @var string
     */
    protected $_name = 'printer_states_history';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * get states history
     * 
     * @author Alex Vait <vait@lieferando.de>
     * @since 30.08.2012
     * @return array
     */
    public static function getStatesHistory($printerId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->from(array('psh' => 'printer_states_history'), array('ps.state', 'psh.created'))
                ->join(array('ps' => 'printer_states'), 'ps.id = psh.stateId' )
                ->where('psh.printerId = ?', $printerId)
                ->order('psh.created desc');

        return $db->fetchAll($select);
    }     
}
