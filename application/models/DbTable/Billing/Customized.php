<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_Billing.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Billing_Customized extends Default_Model_DbTable_Base
{

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'billing_customized';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

}
