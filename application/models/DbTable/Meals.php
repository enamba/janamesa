<?php

/**
 * Database interface for Yourdelivery_Models_DbTable_Meals.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
 */
class Yourdelivery_Model_DbTable_Meals extends Default_Model_DbTable_Base {

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meals';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    protected $_sizes = null;
    protected $_opt = null;
    protected $_ext = null;
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Meal_SizesNn',
        'Yourdelivery_Model_DbTable_Customer_FavouriteMeals',
        'Yourdelivery_Model_DbTable_Meal_Ratings'
    );
    protected $_referenceMap = array(
        'Category' => array(
            'columns' => 'categoryId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Meal_Categories',
            'refColumns' => 'id'
        ),
        'Restaurant' => array(
            'columns' => 'restaurantId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Restaurant',
            'refColumns' => 'id'
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
        $db->update('meals', $data, 'meals.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');

        try {
            $meal = new Yourdelivery_Model_Meals($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        $rank = $meal->getRank();

        //update the rank of lower sizes
        $sql = sprintf('update meals set rank=rank-1 where rank>%d and categoryId = %d', $rank, $meal->getCategoryId());
        $db->query($sql);
        $db->update('meals', array('deleted' => 1, 'rank' => '-1'), 'meals.id = ' . $id);

        //$db->delete('meals', 'meals.id = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order = null, $limit = 0, $from = 0) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("%ftable%" => "meals"));

        if ($order != null) {
            $query->order($order);
        }

        if ($limit != 0) {
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
                ->from(array("m" => "meals"))
                ->where("m.id = " . $id);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching CategoryId by given value
     * @param int $categoryId
     */
    public static function findByCategoryId($categoryId) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("m" => "meals"))
                ->where("m.categoryId = " . $categoryId);

        return $db->fetchAll($query);
    }

    /**
     * get a rows matching Name by given value
     * @param varchar $name
     */
    public static function findByName($name) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("m" => "meals"))
                ->where("m.name = " . $name);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Description by given value
     * @param text $description
     */
    public static function findByDescription($description) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("m" => "meals"))
                ->where("m.description = " . $description);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching RestaurantId by given value
     * @param int $restaurantId
     */
    public static function findByRestaurantId($restaurantId) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("m" => "meals"))
                ->where("m.restaurantId = " . $restaurantId);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Nr by given value
     * @param varchar $nr
     */
    public static function findByNr($nr) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("m" => "meals"))
                ->where("m.nr = " . $nr);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Status by given value
     * @param tinyint $status
     */
    public static function findByStatus($status) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("m" => "meals"))
                ->where("m.status = " . $status);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Temp by given value
     * @param tinyint $temp
     */
    public static function findByTemp($temp) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("m" => "meals"))
                ->where("m.temp = " . $temp);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Caterdesc by given value
     * @param text $caterdesc
     */
    public static function findByCaterdesc($caterdesc) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("m" => "meals"))
                ->where("m.caterdesc = " . $caterdesc);

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Mwst by given value
     * @param int $mwst
     */
    public static function findByMwst($mwst) {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                ->from(array("m" => "meals"))
                ->where("m.mwst = " . $mwst);

        return $db->fetchRow($query);
    }

    /**
     * Search replace
     * @author vpriem
     * @since 31.08.2010
     * @param string $search
     * @return array
     */
    public static function searchReplace($search) {

        $db = Zend_Registry::get('dbAdapter');

        $search = "%" . $search . "%";
        return $db->fetchAll(
                        "SELECT `name`, `description`
            FROM `meals`
            WHERE `name` LIKE ?
                OR `description` LIKE ?
            LIMIT 100", array($search, $search)
        );
    }

    /**
     * get name of meal category
     * @return string
     */
    public function getCategoryName() {
        return $this->getCurrent()->findParentRow('Yourdelivery_Model_DbTable_Meal_Categories')->name;
    }

    /**
     * get mwst of meal category
     * @return string
     */
    public function getCategoryMwst() {
        #return $this->getCurrent()->findParentRow('Yourdelivery_Model_DbTable_Meal_Categories')->mwst;
    }

    /**
     * get description of meal category
     * @return string
     */
    public function getCategoryDescription() {
        return $this->getCurrent()->findParentRow('Yourdelivery_Model_DbTable_Meal_Categories')->description;
    }

    public function getCategoryId() {
        return $this->getCurrent()->findParentRow('Yourdelivery_Model_DbTable_Meal_Categories')->id;
    }

    /**
     * get all sizes from current meal
     * @return array
     */
    public function getSizes() {
        if (is_null($this->_sizes) && $this->getCurrent()) {
            $rows = $this->getCurrent()
                    ->findDependentRowset('Yourdelivery_Model_DbTable_Meal_SizesNn');
            $result = array();
            foreach ($rows as $size) {
                $mealParent = $size->findParentRow('Yourdelivery_Model_DbTable_Meal_Sizes');
                if ($mealParent) {
                    $result[$mealParent->id] = array_merge(
                            $mealParent->toArray(),
                            //append some more informations about product
                            array(
                        'cost' => $rows->current()->cost,
                        'pfand' => $rows->current()->pfand,
                        'mealId' => $rows->current()->mealId
                            )
                    );
                }
            }
            $this->_sizes = $result;
        }
        return $this->_sizes;
    }

    /**
     * get all options
     * @since 27.10.2010
     * @author alex
     * @return array
     */
    public function getOptions() {

        if (is_null($this->_opt)) {
            $adapter = Zend_Registry::get('dbAdapterReadOnly');
            // get all options rows belonging to this meal or to the category of this meal
            $sql1 = $adapter->select()
                    ->distinct()
                    ->from(array('row' => 'meal_options_rows'), array('row.id', 'row.choices', 'row.minChoices', 'row.name', 'row.internalName', 'row.description', 'row.rank'))
                    ->join(array('nn' => 'meal_options_rows_nn'), 'nn.optionRowId = row.id', array())
                    ->where($adapter->quoteInto('nn.mealId = ?', $this->getId()));
            $sql2 = $adapter->select()
                    ->distinct()
                    ->from(array("row" => "meal_options_rows"), array('row.id', 'row.choices', 'row.minChoices', 'row.name', 'row.internalName', 'row.description', 'row.rank'))
                    ->where($adapter->quoteInto('row.categoryId = ?', $this->getCurrent()->categoryId));

            $query = $adapter->select()
                    ->union(array($sql1, $sql2))
                    ->order('rank');
            $optRows = $adapter->fetchAll($query);
            
            $sizeNameArr = $adapter->query(sprintf("SELECT GROUP_CONCAT(s2.name) AS snames FROM meal_sizes_nn snn2
                                            JOIN meal_sizes s2 ON snn2.sizeId=s2.id
                                            WHERE snn2.mealId='%d'", $this->getId()))->fetchAll();
            
            $ret = array();
            // get all options of this options row
            foreach ($optRows AS $row) {
                $opts = $adapter
                                ->query(sprintf("SELECT o.id AS oid, o.name, o.cost, o.mwst
                                                FROM meal_options_nn nn
                                                LEFT JOIN meal_options o ON nn.optionId=o.id
                                                WHERE nn.optionRowId='%d' AND o.status=1", (integer) $row['id']))->fetchAll();

                if (count($opts) > 0) {
                    $row['items'] = $opts;
                }
                
                if($sizeNameArr) {
                    $mealOpts = $adapter
                                    ->query(sprintf("SELECT m.id AS mid, m.name, snn.cost, snn.sizeId as sid, s.name as sname, m.mwst
                                                    FROM meal_mealoptions_nn nn
                                                    JOIN meals m ON nn.mealId=m.id
                                                    JOIN meal_sizes_nn snn ON snn.mealId=m.id
                                                    JOIN meal_sizes s ON snn.sizeId=s.id
                                                    WHERE nn.optionRowId='%d'
                                                    AND s.name IN ('%s')", (integer) $row['id'], str_replace(',', "','", $sizeNameArr[0]['snames'])))->fetchAll();
                }
                if (count($mealOpts) > 0) {
                    $row['mealOptionItems'] = $mealOpts;
                }
                
                if ($row['items'] || $row['mealOptionItems']) {
                    $ret[] = $row;
                }
            }
            $this->_opt = $ret;
        }
        return $this->_opt;
    }

    /**
     * get certain size of the meal
     * @return int
     */
    public function getSize($sizeId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("m" => "meal_sizes_nn"))
                ->where("m.mealId = " . $this->getId() . " and m.sizeId = " . $sizeId);

        $row = $db->fetchRow($query);
        return $row;
    }

    /**
     * get cost for certain size of the meal
     * @return int
     */
    public function getCostForSize($sizeId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("m" => "meal_sizes_nn"))
                ->where("m.mealId = " . $this->getId() . " and m.sizeId = " . $sizeId);

        $row = $db->fetchRow($query);
        return $row['cost'];
    }

    /**
     * get cost for pfand size of the meal
     * @return int
     */
    public function getPfandForSize($sizeId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("m" => "meal_sizes_nn"))
                ->where("m.mealId = " . $this->getId() . " and m.sizeId = " . $sizeId);

        $row = $db->fetchRow($query);
        return $row['pfand'];
    }

    /**
     * get nr of size-meal association
     * @author Alex Vait <vait@lieferando.de>
     * @since 20.12.2011
     * @param int $sizeId
     * @return string
     */
    public function getNrForSize($sizeId) {
        if (intval($sizeId) == 0) {
            return '';
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("m" => "meal_sizes_nn"))
                ->where("m.mealId = " . $this->getId() . " and m.sizeId = " . $sizeId);

        $row = $db->fetchRow($query);
        return $row['nr'];
    }

    /**
     * get size-meal realation
     * @author Alex Vait <vait@lieferando.de>
     * @since 21.12.2011
     * @param int $sizeId
     * @return string
     */
    public function getSizeRelation($sizeId) {
        if (intval($sizeId) == 0) {
            return '';
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("m" => "meal_sizes_nn"))
                ->where("m.mealId = " . $this->getId() . " and m.sizeId = " . $sizeId);

        $row = $db->fetchRow($query);
        return $row;
    }

    /**
     * get current size name
     * @param int $sizeId
     * @return string
     */
    public function getSizeName($sizeId = null) {
        $sizes = $this->getSizes();
        if (is_null($sizes)) {
            return "";
        }

        //check for sizes
        if (!isset($sizes[$sizeId])) {
            return "";
        }

        $size = $sizes[$sizeId];
        return $size['name'];
    }

    /**
     * meal-size-extra association exists?
     * @return boolean
     */
    public function hasExtra($extraId, $sizeId) {
        $adapter = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf('select id from meal_extras_relations where mealId=%d and extraId=%d and sizeId=%d', $this->getId(), $extraId, $sizeId);
        return $adapter->fetchRow($sql);
    }

    /**
     * cost for certain extra in this size
     * @return int
     */
    public function getExtraCost($extraId, $sizeId) {
        $adapter = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf('select cost from meal_extras_relations where mealId=%d and extraId=%d and sizeId=%d', $this->getId(), $extraId, $sizeId);
        $row = $adapter->fetchRow($sql);
        return $row['cost'];
    }

    /**
     * @author ---
     * @modified afrank 26-06-12
     * @modified Alex Vait 16-07-12
     * @param int $sizeId
     * @return array
     * @see YD-814
     * mealId is needed so we can distinguish if this extra is associated wiht the meal or the whole category 
     */
    public function getExtras($sizeId = 0) {

            $adapter = Zend_Registry::get('dbAdapterReadOnly');
            // get all extras rows belonging to this meal or to the category of this meal
            $sql = $adapter->select()
                    ->from(array('me' => 'meal_extras'), array(
                        'name' => new Zend_Db_Expr('TRIM(me.name)'),
                        'id' => 'me.id',
                        'cost' => 'mer.cost',
                        'groupName' => 'meg.name',
                    ))
                    ->join(array('mer' => 'meal_extras_relations'), 'mer.extraId=me.id', array())
                    ->join(array('meg' => 'meal_extras_groups'), 'me.groupId = meg.id', array())
                    ->where(sprintf('mer.mealId = %d OR  mer.categoryId = %d', $this->getId(), $this->getCategoryId()))
                    ->where('mer.sizeId = ?', $sizeId)
                    ->where('me.status = 1')
                    ->order('meg.name')
                    ->order('me.name')
                    ->group('me.id');

            $query = $adapter->select()
                    ->from(array('s' => $sql))
                    ->order('name');

        return $adapter->query($query)->fetchAll();
    }

    /**
     * get extras of meal for menu copy. Not using cache.
     * @author alex
     * @since 22.09.2010
     * @param int $categoryId
     * @param int $sizeId
     * @return array
     */
    public function getExtrasForCopying($sizeId = 0) {
        $sql = sprintf("SELECT distinct(E.id), E.name, E.groupId, N.id as relId, N.cost, N.mealId, N.categoryId FROM meal_extras_relations N
                        INNER JOIN meal_extras E ON N.extraId=E.id
                        WHERE (N.mealId=%d OR N.categoryId=%d) AND N.sizeId=%d and N.cost>0", $this->getId(), $this->getCategoryId(), $sizeId);
        return $this->getAdapter()
                        ->query($sql)
                        ->fetchAll();
    }

    /**
     * remove all extras relationship with this meal for the size
     * used to clean the data before saving new extras relationships
     * @return
     */
    public function removeExtrasForSize($sizeId) {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_extras_relations', 'mealId = ' . $this->getId() . ' and sizeId = ' . $sizeId);
    }

    /**
     * remove all options relationship with this meal
     * used to clean the data before saving new options relationships
     * @return
     */
    public function removeAllOptions() {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_options_rows_nn', 'mealId = ' . $this->getId());
    }

    /**
     * checks if the option group is available for this meal
     * @return boolean
     */
    public function hasOptionsRow($rowId) {
        $sql = sprintf('select id from meal_options_rows_nn where mealId=%d and optionRowId=%d', $this->getId(), $rowId);
        $row = $this->getAdapter()->fetchRow($sql);
        return $row['id'];
    }

    /**
     * get maximal rank of meals for this category
     * @param int 
     * @return int
     */
    public static function getMaxRank($categoryId) {
        $db = Zend_Registry::get('dbAdapter');

        $result = $db->fetchRow(sprintf('SELECT max(rank) as max FROM meals where categoryId=%d  and deleted=0', $categoryId));

        if ($result['max'] < 0) {
            return 0;
        }

        return $result['max'];
    }

    /**
     * get minimal rank of meals for this category
     * @param int
     * @return int
     */
    public static function getMinRank($categoryId) {
        $db = Zend_Registry::get('dbAdapter');

        $result = $db->fetchRow(sprintf('SELECT min(rank) as min FROM meals where categoryId=%d and rank>0 and deleted=0', $categoryId));
        return $result['min'];
    }

    /**
     * get all options rows for the meal category
     * @since 22.09.2010
     * @author alex
     */
    public function getOptionsRowsByCategory($categoryId) {
        $sql = sprintf("SELECT * FROM meal_options_rows WHERE categoryId=%d", $categoryId);
        $optRows = $this->getAdapter()->fetchAll($sql);

        return $optRows;
    }

    /**
     * get all options rows having association with this meal
     * @since 22.09.2010
     * @author alex
     */
    public function getOptionsRowNNByMeal() {
        $sql = sprintf("SELECT * FROM meal_options_rows_nn WHERE mealId=%d AND restaurantId=%d", $this->getId(), $this->getCurrent()->restaurantId);
        $optRowsNn = $this->getAdapter()->fetchAll($sql);

        return $optRowsNn;
    }

    /**
     * get all ratings of a meal
     * @author mlaug
     * @since 28.06.2011
     */
    public function getRatings() {
        return $this->getCurrent()->findDependentRowset('Yourdelivery_Model_DbTable_Meal_Ratings');
    }

    /**
     * Coutn meal having the search text in it's name
     * @author alex
     * @since 06.01.2011
     * @param string $searchText
     * @return int 
     */
    public static function getMealsWithSearchedString($searchText, $exactphrase = 0, $excludetext = 0, $excluderestaurants, $showOnlyIfNoImage = 0, $ifnotype = 0, $ifnoingredients = 0) {
        if (strlen(trim($searchText)) == 0) {
            return 0;
        }
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select('m.id')
                ->from(array("m" => "meals"))
                ->join(array('mc' => 'meal_categories'), "mc.id=m.categoryId", array())
                ->join(array('r' => 'restaurants'), "r.id=m.restaurantId", array())
                ->joinLeft(array('mtn' => 'meal_types_nn'), "mtn.mealId=m.id", array())
                ->joinLeft(array('min' => 'meal_ingredients_nn'), "min.mealId=m.id", array())
                ->where("m.deleted = 0")
                ->where("m.restaurantId > 0");

        if (strlen($excludetext) > 0) {
            $query->where("m.name NOT LIKE '%" . $excludetext . "%'");
        }

        if ($exactphrase == 1) {
            $query->where("m.name ='" . $searchText . "'");
        } else {
            $query->where("m.name LIKE '%" . $searchText . "%'");
        }

        if ($showOnlyIfNoImage) {
            $query->where("m.hasPicture = 0");
        }

        if ($ifnotype) {
            $query->where("COALESCE(mtn.id, 0) = 0");
        }

        if ($ifnoingredients) {
            $query->where("COALESCE(min.id, 0) = 0");
        }

        if ($excluderestaurants) {
            $query->where("r.id NOT IN (" . $excluderestaurants . ")");
        }
        
        return $db->fetchAll($query);        
    }

    /**
     * get all types of this meal
     * @author alex
     * @since 06.07.2011
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getTypes() {
        $sql = sprintf("SELECT * FROM meal_types_nn WHERE mealId=%d", $this->getId());
        $typesRows = $this->getAdapter()->fetchAll($sql);

        return $typesRows;
    }

    /**
     * get all ingredients of this meal
     * @author alex
     * @since 21.07.2011
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getIngredients() {
        $adapter = $this->getAdapter();
        $query = $adapter->select()
                ->from(array("meal_ingredients_nn"))
                ->where("mealId = ?", $this->getId());
        
        return $adapter->query($query)->fetchAll();
    }

    /**
     * TODO refactor for backend
     * set options/extras flag for all meal sizes relations of this meal
     * @author alex
     * @since 28.07.2011
     * @param $hasSpecials
     */
    public function setHasSpecials($sizeId, $hasSpecials) {
        $this->getAdapter()->update('meal_sizes_nn', array('hasSpecials' => $hasSpecials), 'meal_sizes_nn.mealId = ' . $this->getId() . ' and meal_sizes_nn.sizeId = ' . $sizeId);
    }

    /**
     * clear the meal data so that all extras and options will be corrected when another size is set
     * @author alex
     * @since 29.07.2011
     */
    public function clearData() {
        $this->_opt = null;
        $this->_ext = null;
    }

    
    /**
     * get count of meals with specified associations set
     * $condition values: 
     *  null - get count of all undeleted online meals
     *  'ingredients' are set - if some ingredients for meals are defined (association with a table meal_ingredients_nn)
     *  'types' are set - if some ingredients for meals are defined (association with a table meal_types_nn)
     * @author Alex Vait
     * @since 28.06.2012
     * @param array $conditions
     * @return int
     */
    public static function getConditionalCount(array $conditions = null) {
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                     ->from(array('m' => "meals"), array(
                        new Zend_Db_Expr("COUNT(DISTINCT(m.id))")
                     ));
        
        if ($conditions['ingredients']) {
            $select->join(array('nn' => "meal_ingredients_nn"), "nn.mealId = m.id", array());
        }                
        if ($conditions['types']) {
            $select->join(array('nn' => "meal_types_nn"), "nn.mealId = m.id", array());
        }

        $select->where("m.deleted = 0")
               ->where("m.status = 1");
        
        return $db->fetchOne($select);
    }    
}
