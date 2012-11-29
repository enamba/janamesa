<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_Billing.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Billing_Admonation extends Default_Model_DbTable_Base
{

    protected $_referenceMap    = array(
        'Billing' => array(
            'columns'           => 'billingId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Billing',
            'refColumns'        => 'id'
        )
    );
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'billing_admonation';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    
}
