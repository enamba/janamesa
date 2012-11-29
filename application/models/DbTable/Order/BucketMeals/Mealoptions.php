<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Budget
 *
 * @author jens naie
 */
class Yourdelivery_Model_DbTable_Order_BucketMeals_Mealoptions extends Default_Model_DbTable_Base {

    protected $_name = 'orders_bucket_meals_mealoptions';

    protected $_referenceMap    = array(
        'OrderBucket' => array(
            'columns'           => 'bucketItemId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Order_BucketMeals',
            'refColumns'        => 'id'
        )
    );
    
}
?>
