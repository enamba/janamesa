<?php

/**
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Order_Favourites extends Default_Model_DbTable_Base {

    protected $_name = 'favourites';
    protected $_referenceMap = array(
        'Customer' => array(
            'columns' => 'customerId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Customer',
            'refColumns' => 'id'
        ),
        'Order' => array(
            'columns' => 'orderId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Order',
            'refColumns' => 'id'
        )
    );

}