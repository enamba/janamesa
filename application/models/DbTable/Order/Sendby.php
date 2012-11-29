<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 24.11.2011
 */
class Yourdelivery_Model_DbTable_Order_Sendby extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @param string
     */
    protected $_name = 'order_sendby';

    /**
     * Primary key name
     * @param string
     */
    protected $_primary = 'id';

    /**
     * @var array
     */
    protected $_referenceMap = array(
        'Order' => array(
            'columns' => 'orderId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Order',
            'refColumns' => 'id'
        )
    );

}
