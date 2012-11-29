<?php

/**
 * @author Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Model_DbTable_Restaurant_Payments extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'restaurant_payments';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * @var array
     */
    protected $_referenceMap = array(
        'Order' => array(
            'columns' => 'restaurantId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Restaurant',
            'refColumns' => 'id',
        ),
    );

}
