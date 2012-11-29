<?php
/**
 * Description of Meal_Categorys_Nn
 * @package service
 * @subpackage menu
 */

/**
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Servicetypes_MealCategorysNn extends Default_Model_DbTable_Base {


    protected $_primary = 'id';

    protected $_name = 'servicetypes_meal_categorys_nn';

    protected $_referenceMap    = array(
        'Servicetype' => array(
            'columns'           => 'servicetypeId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Servicetypes',
            'refColumns'        => 'id'
        ),
        'MealCategory' => array(
            'columns'           => 'mealCategoryId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Meal_Categories',
            'refColumns'        => 'id'
        )
    );

    /**
     * @author mlaug
     * @param integer $categoryId
     * @return array 
     */
    function getSizesOfCategory($categoryId){
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAssoc("
            SELECT * FROM meal_sizes 
                WHERE 
                    categoryId=? 
                ORDER BY rank", (integer) $categoryId);
    }

    /**
     * get all meals based on restaurant and servicetype, also we get the 
     * parents and list them up
     * @author mlaug
     * @since 13.10.2011
     * @param int $r
     * @param int $s
     * @return array
     */
    public function getCategories($r = null, $s = null, $forcopy=false) {

        if ( is_null($r) || is_null($s) ){
            return array();
        }
        
        if (!$forcopy) {
            $timeRestriction = " AND CURRENT_TIME BETWEEN mc.`from` and mc.`to`AND 
                                    POW(2, (WEEKDAY(NOW()))) & mc.weekdays > 0 ";
        }
            
        //order by rank
        $sql = "SELECT 
                    mc.*,mcp.id as parentCategoryId, mcp.name as parentCategoryName FROM meal_categories mc 
                INNER JOIN 
                    servicetypes_meal_categorys_nn smc ON mc.id=smc.mealCategoryId 
                LEFT JOIN
                    meal_categories_parents mcp ON mcp.id=mc.parentMealCategoryId
                WHERE 
                    mc.restaurantId=? and smc.serviceTypeId=? " . $timeRestriction . 
                "ORDER BY 
                    mc.rank ASC ";
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return  $db->fetchAssoc($sql,array((integer) $r, (integer) $s));
        
    }

    /**
     * get all meals of a given category
     * @author mlaug
     * @param integer $catId
     * @return array
     */
    public function getMealsOfCategory($catId){       
        if ( is_null($catId) ){
            return array();
        }
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAssoc("
            SELECT * FROM meals 
                WHERE categoryId=? AND 
                    deleted=0 AND 
                    status=1 
                ORDER BY hasPicture DESC,rank ASC", (integer) $catId);
    }
    
    /**
     * get all sizes of a given meal 
     * @author mlaug
     * @param integer $mealId
     * @return array 
     */
    public function getSizesOfMeal($mealId){

        if ( is_null($mealId) ){
            return array();
        }

        /**
         * @todo: fix this wrong relation!
         */
        //  with this command we can avoid that sizes belonging to other categories will be assigned to the meals,
        //  resulting in showing meal price without size in menu
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAssoc("
            SELECT msn.* FROM meal_sizes_nn msn 
                INNER JOIN meal_sizes ms ON msn.sizeId=ms.id 
                INNER JOIN meals m ON m.id=msn.mealId 
                WHERE 
                    mealId=? AND 
                    m.categoryId=ms.categoryId 
                ORDER BY ms.rank ASC", (integer) $mealId);
    }

    /**
     * delete a table row by given meal category id
     * @param integer $id
     * @return void
     */
    public static function removeByMealCategoryId($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('servicetypes_meal_categorys_nn', 'servicetypes_meal_categorys_nn.mealCategoryId = ' . (integer) $id);
    }
}