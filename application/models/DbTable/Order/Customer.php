<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Budget
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Order_Customer extends Default_Model_DbTable_Base {

    protected $_name = 'orders_customer';

    protected $_referenceMap    = array(
        'Order' => array(
            'columns'           => 'orderId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Order',
            'refColumns'        => 'id'
        )
    );
    
}
?>
