<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 17.04.2012
 */
class Yourdelivery_Model_DbTable_Restaurant_Ratings_Crm extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'restaurant_ratings_crm';

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
            'columns' => 'ratingId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Restaurant_Ratings',
            'refColumns' => 'id'
        )
    );

}
