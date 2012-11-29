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
class Yourdelivery_Model_DbTable_Order_BucketMeals_Extras extends Default_Model_DbTable_Base {

    protected $_name = 'orders_bucket_meals_extras';

    protected $_referenceMap    = array(
        'OrderBucket' => array(
            'columns'           => 'bucketItemId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Order_BucketMeals',
            'refColumns'        => 'id'
        )
    );
    
}
?>
