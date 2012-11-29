<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 20.07.2012
 */
class Yourdelivery_Model_DbTable_Order_Deliverdelay extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'order_deliverdelay';

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
            'columns' => 'orderId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Order',
            'refColumns' => 'id',
        ),
    );

}
