<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.06.2012
 */
class Yourdelivery_Model_DbTable_Blacklist_Matching extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string 
     */
    protected $_name = 'blacklist_matching';

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
            'columns'       => 'orderId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Order',
            'refColumns'    => 'id',
        ),
        'Value' => array(
            'columns'       => 'valueId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Blacklist_Values',
            'refColumns'    => 'id',
        ),
    );
    
}