<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealSizes.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
 */

class Yourdelivery_Model_DbTable_Meal_Sizes extends Default_Model_DbTable_Base {

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_sizes';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Meal_SizesNn',
    );

    protected $_referenceMap    = array(
        'MealCategory' => array(
        'columns'           => 'categoryId',
        'refTableClass'     => 'Yourdelivery_Model_DbTable_Meal_Categories',
        'refColumns'        => 'id'
        )
    );

    /**
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public static function edit($id, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('meal_sizes', $data, 'meal_sizes.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        
        try {
            $size = new Yourdelivery_Model_Meal_Sizes($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        $rank = $size->getRank();

        //update the rank of lower sizes
        $sql = sprintf('update meal_sizes set rank=rank-1 where rank>%d and categoryId = %d', $rank, $size->getCategoryId());
        $db->query($sql);
        $db->delete('meal_sizes', 'meal_sizes.id = ' . $id);
        $db->delete('meal_sizes_nn', 'meal_sizes_nn.sizeId = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order=null, $limit=0, $from=0) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
            ->from( array("%ftable%" => "meal_sizes") );

        if($order != null) {
            $query->order($order);
        }

        if($limit != 0) {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
    }

    /**
     * get a rows matching Id by given value
     * @param int $id
     */
    public static function findById($id) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
            ->from( array("m" => "meal_sizes") )
            ->where( "m.id = " . $id );

        return $db->fetchRow($query);
    }
    
    /**
     * get a rows matching Name by given value
     * @param varchar $name
     */
    public static function findByName($name) {
        $db = Zend_Registry::get('dbAdapter');
        
        $query = $db->select()
            ->from( array("m" => "meal_sizes") )
            ->where( "m.name = " . $name );

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Description by given value
     * @param text $description
     */
    public static function findByDescription($description) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
            ->from( array("m" => "meal_sizes") )
            ->where( "m.description = " . $description );

        return $db->fetchRow($query);
    }
    
    /**
     * get a rows matching CategoryId by given value
     * @param int $categoryId
     */
    public static function findByCategoryId($categoryId) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
            ->from( array("m" => "meal_sizes") )
            ->where( "m.categoryId = " . $categoryId );

        return $db->fetchRow($query);
    }
    /**
     * get a rows matching Status by given value
     * @param tinyint $status
     */
    public static function findByStatus($status) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
            ->from( array("m" => "meal_sizes") )
            ->where( "m.status = " . $status );

        return $db->fetchRow($query);
    }

    /**
     * size-extra association exists?
     * @return boolean
     */
    public function hasExtra($extraId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf('select id from meal_extras_relations where sizeId=%d and extraId=%d', $this->getId(), $extraId);
        return $db->fetchRow($sql);
    }

    /**
     * get maximal rank of meal sizes for this category
     * @param int
     * @return int
     */
    public static function getMaxRank($categoryId){
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $result = $db->fetchRow(sprintf('SELECT max(rank) as max FROM meal_sizes where categoryId=%d', $categoryId));
        return $result['max'];
    }

    /**
     * get minimal rank of sizes for this category
     * @param int
     * @return int
     */
    public static function getMinRank($categoryId){
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $result = $db->fetchRow(sprintf('SELECT min(rank) as min FROM meal_sizes where categoryId=%d and rank>0', $categoryId));
        return $result['min'];
    }

}
