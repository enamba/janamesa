<?php

/**
 * @author alex
 * @since 01.07.2011
 */
class Yourdelivery_Model_DbTable_Meal_Types extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'meal_types';

    /**
     * Primary key
     * @var string
     */
    protected $_primary = 'id';

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.06.2012
     * @return array
     */
    public function getChildren() {
        
        return $this->fetchAll(
            $this->select()
                 ->where("`parentId` = ?", $this->getId())
        );
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 26.06.2012
     * @return array
     */
    public static function getAutocomplete() {
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchAll(
            "(
                SELECT mt1.id, mt1.name `value`
                FROM `meal_types` mt1
                WHERE mt1.parentId = 0)
            UNION (
                SELECT mt2.id, CONCAT(mt1.name, ' > ', mt2.name) `value`
                FROM `meal_types` mt1
                INNER JOIN `meal_types` mt2 ON mt2.parentId = mt1.id
                WHERE mt1.parentId = 0)
            UNION (
                SELECT mt3.id, CONCAT(mt1.name, ' > ', mt2.name, ' > ', mt3.name) `value`
                FROM `meal_types` mt1
                INNER JOIN `meal_types` mt2 ON mt2.parentId = mt1.id
                INNER JOIN `meal_types` mt3 ON mt3.parentId = mt2.id
                WHERE mt1.parentId = 0)
            UNION (
                SELECT mt4.id, CONCAT(mt1.name, ' > ', mt2.name, ' > ', mt3.name, ' > ', mt4.name) `value`
                FROM `meal_types` mt1
                INNER JOIN `meal_types` mt2 ON mt2.parentId = mt1.id
                INNER JOIN `meal_types` mt3 ON mt3.parentId = mt2.id
                INNER JOIN `meal_types` mt4 ON mt4.parentId = mt3.id
                WHERE mt1.parentId = 0)
            ORDER BY 2"
        );
    }

    /**
     * get all types of this parent
     * @author alex
     * @since 18.07.2011
     * @return array
     */
    public static function getTypesOfParent($parentId) {
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                    ->from(array("m" => "meal_types"))
                    ->where("m.parentid = ?", $parentId)
                    ->order("m.name");
        return $db->fetchAll($query);
    }

    /**
     * get all types of this meal
     * @author alex
     * @since 12.08.2011
     * @return array
     */
    public static function getTypesOfMeal($mealId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf("select mt.* from meal_types_nn mtn join meal_types mt on mt.id=mtn.typeId where mtn.mealId = %d", $mealId);
        return $db->fetchAll($sql);
    }

}
