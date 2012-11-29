<?php

/**
 * Database interface for Yourdelivery_Models_DbTable_Billing.
 *
 * @copyright   Yourdelivery
 * @author	vait
 */
class Yourdelivery_Model_DbTable_BillingAsset extends Default_Model_DbTable_Base {

    protected $_referenceMap = array(
        'Company' => array(
            'columns' => 'companyId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Company',
            'refColumns' => 'id'
        ),
        'Service' => array(
            'columns' => 'restaurantId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Restaurant',
            'refColumns' => 'id'
        )
    );

    /**
     * name of the table
     * @var string
     */
    protected $_name = 'billing_assets';

    /**
     * primary key
     * @var string
     */
    protected $_primary = 'id';

    /**
     * delete a table row by given primary key
     * 
     * @param integer $id id of billing asset
     * 
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('billing_assets', 'billing_assets.id = ' . $id);
    }

    /**
     * mark this order as billed
     * 
     * @param int    $billId billingId
     * @param string $mode   billing mode (comp / rest)
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * 
     * @return boolean
     */
    public function billMe($billId, $mode) {

        if (is_null($this->getId())) {
            return false;
        }

        $current = $this->getCurrent();

        if (!is_object($current)) {
            return false;
        }

        if ($mode == "rest") {
            $current->billRest = $billId;
        }

        if ($mode == "company") {
            $current->billCompany = $billId;
        }

        if ($mode == "courier") {
            $current->billCourier = $billId;
        }

        try {
            $current->save();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

}
