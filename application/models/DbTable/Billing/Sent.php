<?php
/**
 * Yourdelivery_Model_DbTable_Billing_Sent
 * @author vpriem
 */
class Yourdelivery_Model_DbTable_Billing_Sent extends Default_Model_DbTable_Base{
    
    /**
     * Table name
     * @var string
     */
    protected $_name = 'billing_sent';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    protected $_referenceMap    = array(
        'Billing' => array(
            'columns'           => 'billingId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Billing',
            'refColumns'        => 'id'
        )
    );
    
}