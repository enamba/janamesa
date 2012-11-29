<?php
/**
* @author Alex Vait <vait@lieferando.de>
* @since 27.06.2012
*/

class Yourdelivery_Model_DbTable_Meal_Ingredients_Nn extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_ingredients_nn';

    /**
     * primary key
     * @param string
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
        'Ingredient' => array(
            'columns'       => 'ingredientsId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Meal_Ingredients',
            'refColumns'    => 'id',
        ),
    );
    
}
