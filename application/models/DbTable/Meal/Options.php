<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_MealOptions.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Meal_Options extends Default_Model_DbTable_Base
{
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'meal_options';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    
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
        $db->update('meal_options', $data, 'meal_options.id = ' . $id);
    }
    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('meal_options', 'meal_options.id = ' . $id);
        $db->delete('meal_options_nn', 'meal_options_nn.optionId = ' . $id);
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
                    ->from( array("%ftable%" => "meal_options") );
                    
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
                    ->from( array("m" => "meal_options") )                           
                    ->where( "m.id = " . $id );

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
                    ->from( array("m" => "meal_options") )                           
                    ->where( "m.restaurantId = " . $restaurantId );

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
                    ->from( array("m" => "meal_options") )                           
                    ->where( "m.name = " . $name );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Kind by given value
     * @param int $kind
     */
    public static function findByKind($kind)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_options") )                           
                    ->where( "m.kind = " . $kind );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Cost by given value
     * @param int $cost
     */
    public static function findByCost($cost)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_options") )                           
                    ->where( "m.cost = " . $cost );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Nr by given value
     * @param varchar $nr
     */
    public static function findByNr($nr)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_options") )                           
                    ->where( "m.nr = " . $nr );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching Status by given value
     * @param tinyint $status
     */
    public static function findByStatus($status)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_options") )                           
                    ->where( "m.status = " . $status );

        return $db->fetchRow($query); 
    }
        /**
     * get a rows matching CategoryId by given value
     * @param int $categoryId
     */
    public static function findByCategoryId($categoryId)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("m" => "meal_options") )                           
                    ->where( "m.categoryId = " . $categoryId );

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
                    ->from( array("m" => "meal_options") )                           
                    ->where( "m.mwst = " . $mwst );

        return $db->fetchRow($query); 
    }    

    /**
     * get id of the option group this option belongs to
     * @return int
     */
    public function getOptRowId() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        try{
            $sql = sprintf('select optionRowId from meal_options_nn where optionId=%d', $this->getId());
            $result = $db->fetchRow($sql);
        }
        catch ( Zend_Db_Statement_Exception $e ){
            $db->setFetchMode(Zend_Db::FETCH_ASSOC);
            return 0;
        }

        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        return ($result->optionRowId);
    }

    /**
     * get all meal categories this option belongs to
     * @return array
     */
    public function getCategories(){
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $sql = sprintf("select mc.id, mc.name from meal_options mo join meal_options_nn mon on mo.id=mon.optionId join meal_options_rows mor on mor.id=mon.optionRowId join meal_categories mc on mc.id=mor.categoryId where mo.id=%d", $this->getId());
        $row = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    /**
     * Get all options groups this option belongs to
     * @author Alex Vait <vait@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 31.07.2012
     * @return array
     */
    public function getOptionsGroups(){
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->query(
            "SELECT mor.id, mor.name, mor.internalName 
            FROM meal_options mo 
            INNER JOIN meal_options_nn mon ON mo.id = mon.optionId 
            INNER JOIN meal_options_rows mor ON mor.id = mon.optionRowId 
            WHERE mo.id = ?", $this->getId()
        )->fetchAll(PDO::FETCH_ASSOC);
    }

}
