<?php
/**
 * DbTable Order Location
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Order_Location extends Default_Model_DbTable_Base {

    protected $_name = 'orders_location';

    protected $_referenceMap = array(
        'Order' => array(
            'columns'       => 'orderId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Order',
            'refColumns'    => 'id',
        )
    );
    
}
