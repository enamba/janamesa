<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_Billing.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Tracking_Campaign extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'tracking_campaign';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_dependentTables = array(
                                        'Yourdelivery_Model_DbTable_Tracking_Code',
                                       );



    /**
     * get all tracking codes, belonging to this tracking campaign
     * @param $where condition for tracking codes
     * @return Zend_Db_Table_Rowset_Abstract
     */
     public function getCodes($where = null){
        if($where) {
            $codesTable = new Yourdelivery_Model_DbTable_Tracking_Code();
            $codes = $codesTable->select()->where($where);
            return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Tracking_Code', null, $codes);
        }
        else {
            return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Tracking_Code');
        }
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('tracking_campaign', 'tracking_campaign.id = ' . $id);
        $db->delete('tracking_code', 'tracking_code.campaignId = ' . $id);
    }
    
}
