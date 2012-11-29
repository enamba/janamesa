<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_Billing.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Tracking_Code extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'tracking_code';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_referenceMap    = array(
        'Campaigne' => array(
            'columns'           => 'campaignId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Tracking_Campaign',
            'refColumns'        => 'id'
        )
    );
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('tracking_code', 'tracking_code.id = ' . $id);
    }

    /**
     * get the list of all tracking codes
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctNameId($sortby = 'name'){
        $sql = sprintf('select distinct(id), name, street, plz from restaurants order by ' . $sortby);
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }
}
