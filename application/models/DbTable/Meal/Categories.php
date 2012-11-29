<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealCategories.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Meal_Categories extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_categories';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_dependentTables = array(
                                        'Yourdelivery_Model_DbTable_Meals',
                                        'Yourdelivery_Model_DbTable_Meal_Sizes',
                                        'Yourdelivery_Model_DbTable_Servicetypes_Meal_Categorys_Nn'
                                       );

    protected $_referenceMap    = array(
        'Restaurant' => array(
            'columns'           => 'restaurantId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Restaurant',
            'refColumns'        => 'id'
        ),
        'OptionsRow' => array(
            'columns'           => 'id',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Meal_OptionsRows',
            'refColumns'        => 'categoryId'
        ),
    );
    /**
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public static function edit($id, $data)
    {        
        $db = Zend_Registry::get('dbAdapter');
        $db->update('meal_categories', $data, 'meal_categories.id = ' . $id);
    }
    
    /**
     * delete meal category and update all dependant rows in other tables
     * @author Alex Vait
     * @since 10.11.2011
     */    
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        // delete the category
        $db->delete('meal_categories', 'meal_categories.id = ' . $id);
        // set null as category id for meal sizes
        $db->update('meal_sizes', array ('categoryId' => '0'), 'categoryId = ' . $id);
        // set null as category id and mark meals rows as deleted
        $db->update('meals', array ('categoryId' => '0', 'deleted' => '1'), 'categoryId = ' . $id);
        // set null as category id for the options groups
        $db->update('meal_options_rows', array ('categoryId' => '0'), 'categoryId = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order=null, $limit=0, $from=0)
    {
        $db = Zend_Registry::get('dbAdapter');
        
        $query = $db->select()
                    ->from( array("%ftable%" => "meal_categories") );
                    
        if($order != null)
        {
            $query->order($order);
        }

        if($limit != 0)
        {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
    }
    
    /**
     * get a rows matching Id by given value
     * @param int $id
     */
    public static function findById($id)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_categories") )                           
                    ->where( "m.id = " . $id );

        return $db->fetchRow($query); 
    }
    
    /**
     * get a rows matching Name by given value
     * @param varchar $name
     */
    public static function findByName($name)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_categories") )                           
                    ->where( "m.name = " . $name );

        return $db->fetchRow($query); 
    }
    
    /**
     * get a rows matching Description by given value
     * @param text $description
     */
    public static function findByDescription($description)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_categories") )                           
                    ->where( "m.description = " . $description );

        return $db->fetchRow($query); 
    }

    /**
     * get a rows matching RestaurantId by given value
     * @param int $restaurantId
     */
    public static function findByRestaurantId($restaurantId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_categories") )                           
                    ->where( "m.restaurantId = " . $restaurantId );

        return $db->fetchRow($query); 
    }

    /**
     * get a rows matching Def by given value
     * @param tinyint $def
     */
    public static function findByDef($def)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_categories") )                           
                    ->where( "m.def = " . $def );

        return $db->fetchRow($query); 
    }

    /**
     * get a rows matching Top by given value
     * @param int $top
     */
    public static function findByTop($top)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_categories") )                           
                    ->where( "m.top = " . $top );

        return $db->fetchRow($query); 
    }

    /**
     * get a rows matching Mwst by given value
     * @param int $mwst
     */
    public static function findByMwst($mwst)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_categories") )                           
                    ->where( "m.mwst = " . $mwst );

        return $db->fetchRow($query); 
    }

    /**
     * get maximal rank of categories for this restaurant
     * @param int Restaurant Id
     * @return int
     */
    public static function getMaxRank($restId){
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->fetchRow(sprintf('SELECT max(rank) as max FROM meal_categories where restaurantId=%d', $restId));       
        return $result['max'];
    }

    /**
     * get minimal rank of categories for this restaurant
     * @param int Restaurant Id
     * @return int
     */
    public static function getMinRank($restId){
        $db = Zend_Registry::get('dbAdapter');
        $result = $db->fetchRow(sprintf('SELECT min(rank) as min FROM meal_categories where restaurantId=%d and rank>0', $restId));
        return $result['min'];
    }

    /**
     * Search replace
     * @author vpriem
     * @since 31.08.2010
     * @param string $search
     * @return array
     */
    public static function searchReplace ($search) {

        $db = Zend_Registry::get('dbAdapter');

        $search = "%" . $search . "%";
        return $db->fetchAll(
            "SELECT `name`, `description`
            FROM `meal_categories`
            WHERE `name` LIKE ?
                OR `description` LIKE ?
            LIMIT 100", array($search, $search)
        );

    }

    /**
     * get all sizes associated with this category
     */
    public function getSizes(){
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Meal_Sizes');
    }

    /**
     * get all sizes associated with this category ordfered by rank
     */
    public function getSizesByRank(){
        $sql = sprintf("select * from meal_sizes where categoryId=%d order by rank", $this->getId());
        return $this->getAdapter()->fetchAssoc($sql);
    }

    /**
     * get all extras
     * @return Zend_Db_Table_Rowset
     */
    public function getExtras(){
        $sql = sprintf('select me.id, me.name from meal_extras_relations mr join meal_extras me on me.id=mr.extraId where mr.categoryId=%d', $this->getId());
        return $this->getAdapter()->query($sql)->fetchAll();
    }

    /**
     * get all extras with extras
     * @return Zend_Db_Table_Rowset
     */
    public function getExtrasWithGroups(){
        $sql = sprintf('select me.id, me.name, mr.cost, mr.id as relId, mg.name as grname from meal_extras_relations mr join meal_extras me on me.id=mr.extraId join meal_extras_groups mg on me.groupId=mg.id where mr.categoryId=%d', $this->getId());
        return $this->getAdapter()->query($sql)->fetchAll();
    }

    /**
     * get all options
     * @return Zend_Db_Table_Rowset
     */
    public function getOptions(){
        //dummy
        return null;
    }

    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 18.06.2012
     * @return Zend_Db_Table_Rowset
     */
    public function getOptionRows() {
        $db = $this->getDefaultAdapter();

        $query = $db->select()
                ->from(array("mor" => "meal_options_rows"))
                ->where("mor.categoryId = ?", $this->getId());

        return $db->fetchAll($query);    
    }    
    
    /**
     * get all meals associated with this category
     * @param int Zend_Db_Table_Rowset_Abstract
     */
    public function getMeals(){
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Meals');
    }

    /**
     * get all meals associated with this category sorted by rank
     * @return array
     */
    public function getMealsIdSorted(){
        $sql = sprintf("select id from meals where categoryId=%d and deleted=0 order by rank", $this->getId());
        return $this->getAdapter()->query($sql)->fetchAll();
    }

    /**
     * category-size-extra association exists?
     * @return boolean
     */
    public function hasExtra($extraId, $sizeId) {
        $sql = sprintf('select id from meal_extras_relations where categoryId=%d and extraId=%d and sizeId=%d', $this->getId(), $extraId, $sizeId);
        return $this->getAdapter()->fetchRow($sql);
    }

    /**
     * category-options row association exists?
     * @return boolean
     */
    public function hasOptionsRow($optRowId) {
        $sql = sprintf('select id from meal_options_rows where categoryId=%d and id=%d', $this->getId(), $optRowId);
        return ($this->getAdapter()->fetchRow($sql) != 0);
    }


    /**
     * cost for certain extra in this size
     * @return int
     */
    public function getExtraCost($extraId, $sizeId) {
        $sql = sprintf('select cost from meal_extras_relations where categoryId=%d and extraId=%d and sizeId=%d', $this->getId(), $extraId, $sizeId);
        $row = $this->getAdapter()->fetchRow($sql);
        return $row['cost'];
    }

    /**
     * remove all extras relationship with this category for the size
     * used to clean the data before saving new extras relationships
     * @return
     */
    public function removeExtrasForSize($sizeId)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_extras_relations', 'categoryId = ' . $this->getId() . ' and sizeId = ' . $sizeId);
    }

    /**
     * shows if the category has certain service type assigned
     * @param int $type Yourdelivery_Model_Servicetype_Abstract::[RESTAURANT_IND | CATER_IND | GREAT_IND | FRUIT_IND]
     * @return boolean
     */
    public function hasServiceType($type) {
        $sql = sprintf("select count(id) from servicetypes_meal_categorys_nn where servicetypeId=%d and mealCategoryId=%d", $type, $this->getId());
        return $this->getAdapter()->query($sql)->fetchColumn();
    }

    /**
     * get all service types of this category
     * @return array
     */
    public function getServiceTypes(){
        $sql = sprintf("select st.id, st.name from servicetypes st join servicetypes_meal_categorys_nn stc on st.id=stc.servicetypeId where stc.mealCategoryId=%d", $this->getId());
        $row = $this->getAdapter()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }


    /*
    * get all categories for this restaurant
    * @return array
    */
    public static function getCategories($restaurantId){
        if ( is_null($restaurantId) ){
            return null;
        }

        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("select * from meal_categories where restaurantId=" . $restaurantId . " order by rank");
        $row = $db->query($sql)->fetchAll();
        return $row;
    }

    /*
    * get all category names for this restaurant which has meal extras assigned
    * @return array
    */
    public static function getCategoriesWithExtras($restaurantId){
        if ( is_null($restaurantId) ){
            return null;
        }

        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("select distinct meal_categories.name from meal_categories inner join meal_extras on meal_extras.categoryId=meal_categories.id where meal_categories.restaurantId=" . $restaurantId);
        $row = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    /**
     * get count of all meals associated with this category
     * is for the restaurant backend, so menu can be shown quicker
     * @param int categoryId
     */
    public function getMealsCount(){
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("select count(id) from meals where categoryId=%d and deleted=0", $this->getId());
        $result = $db->query($sql)->fetchColumn();
        return $result['count'];
    }
    
    
    /**
     * check if this category is available on this weekday (1 - monday ... 7 - sunday)
     * @return bool
     * @author alex
     * @since 10.11.2011
     */
    public function isAvailableOnWeekday($weekday){
        $wdbin = pow(2, $weekday-1);
        $sql = sprintf("select weekdays & %d as res from meal_categories where id=%d", $wdbin, $this->getId());
        $row = $this->getAdapter()->query($sql)->fetchAll(PDO::FETCH_COLUMN);
        return $row[0];
    }        
}
