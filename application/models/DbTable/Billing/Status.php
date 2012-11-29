<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_BillingStatus.
 *
 * @author Alex Vait <vait@lieferando.de>
 * @since 19.06.2012
*/

class Yourdelivery_Model_DbTable_Billing_Status extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'billing_status';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
}
