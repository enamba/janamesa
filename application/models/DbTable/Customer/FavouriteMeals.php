<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_CustomerCompany.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Customer_FavouriteMeals extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'customer_favourite_meals';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * the reference array to map on dependent tables
     * @var array
     */
    protected $_referenceMap    = array(
        'Customer' => array(
            'columns'           => 'customerId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Customer',
            'refColumns'        => 'id'
        ),
        'Meals' => array(
            'columns'           => 'mealId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Meals',
            'refColumns'        => 'id'
        )
    );
    
}
