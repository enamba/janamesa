<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 27.06.2012
 */
class Yourdelivery_Model_DbTable_Meal_Types_Nn extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'meal_types_nn';

    /**
     * Primary key
     * @var string
     */
    protected $_primary = 'id';

    /**
     * @var array
     */
    protected $_referenceMap = array(
        'Meal' => array(
            'columns'       => 'mealId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Meals',
            'refColumns'    => 'id',
        ),
        'Type' => array(
            'columns'       => 'typeId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Meal_Types',
            'refColumns'    => 'id',
        ),
    );

}
