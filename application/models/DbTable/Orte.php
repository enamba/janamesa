<?php
/**
 * Description of Orte
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Orte extends Default_Model_DbTable_Base{

    /**
     * Table name
     * @var string
     */
    protected $_name = "orte";
    
    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'plz';

    protected $_referenceMap = array(
        'Customer_Locations' => array(
            'columns'       => 'plz',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Locations',
            'refColumns'    => 'plz'
        ),
        'Company_Addresses' => array(
            'columns'       => 'plz',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Company_Addresses',
            'refColumns'    => 'plz'
        ),
    );

    /**
     * get all elements from this table as array
     * @return array
     */
    public static function allPlz(){

        $db = Zend_Registry::get('dbAdapter');
        return $db->fetchAll(
            "SELECT `plz`
            FROM `orte` 
            WHERE LENGTH(`plz`) > 4");

    }
    
    public static function lookup($plz){

        $db = Zend_Registry::get('dbAdapter');
        $sql = "select plz,ort from orte where plz like '$plz%'";
        return $db->fetchAll($sql);
        
    }

    /**
     * @param integer $id
     * @param array $data
     * @return void
     */
    public static function edit ($id, $data) {

        $db = Zend_Registry::get('dbAdapter');
        $db->update('orte', $data, 'orte.plz = ' . $id);

    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove ($id) {

        $db = Zend_Registry::get('dbAdapter');
        $db->delete('orte', 'orte.plz = ' . $id);

    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get ($order = null, $limit = 0, $from = 0) {

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                    ->from(array("%ftable%" => "orte"));

        if ($order !== null) {
            $query->order($order);
        }

        if ($limit != 0) {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
        
    }

    /**
     * Get a rows matching Plz by given value
     * @author vpriem
     * @since 10.02.2011
     * @param int $plz
     * @return array
     */
    public static function findByPlz ($plz) {

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                    ->from(array("o" => "orte"))
                    ->where("o.plz = ?", $plz);
        return $db->fetchRow($query);

    }

    /**
     * Get a rows matching Ort by given value
     * @author vpriem
     * @since 10.02.2011
     * @param string $ort
     * @return array
     */
    public static function findByOrt ($ort) {

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                    ->from(array("o" => "orte"))
                    ->where("o.ort = ?", $ort);
        return $db->fetchRow($query);

    }

    /**
     * get a rows matching Kreisid by given value
     * @param int $kreisid
     */
    public static function findByKreisid ($kreisid) {

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                    ->from(array("o" => "orte"))
                    ->where("o.kreisid = " . $kreisid);
        return $db->fetchRow($query);

    }

    /**
     * get a rows matching Kreis by given value
     * @param varchar $kreis
     */
    public static function findByKreis ($kreis) {

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                    ->from(array("o" => "orte"))
                    ->where("o.kreis = " . $kreis);
        return $db->fetchRow($query);

    }

   /**
    * get a rows matching Landid by given value
    * @param int $landid
    */
    public static function findByLandid ($landid) {

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                    ->from(array("o" => "orte"))
                    ->where("o.landid = " . $landid);
        return $db->fetchRow($query);

    }

    /**
     * get a rows matching Land by given value
     * @param varchar $land
     */
    public static function findByLand ($land) {

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                    ->from(array("o" => "orte"))
                    ->where("o.land = " . $land);
        return $db->fetchRow($query);

    }

    /**
     * @return array
     */
    public function getAllPlz(){

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                    ->from(array("o" => "orte"));
        return $db->fetchAll($query);

    }

    /**
     * Get names of all cities
     * @return array
     */
    public static function getAllCities(){
        
        $db = Zend_Registry::get('dbAdapter');
        return $db->fetchCol(
            "SELECT DISTINCT `ort` 
            FROM `orte` 
            ORDER BY `ort`"        
        );

    }

    public static function getPlzByOrt ($ort) {

        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()
                    ->from(array("o" => "orte"))
                    ->where( "o.ort = '" . $ort ."'");
        return $db->fetchAll($query);

    }

    /**
     * Get all names of federal states
     * @return array
     */
    public static function getAllLands(){
        $db = Zend_Registry::get('dbAdapter');
        $query = $db->select()->distinct()
                    ->from(array('o' => 'orte'), array(
                            'o.landid',
                            'o.land'
                        ))
                    ->where( "LENGTH(o.land)>0")
                    ->order('o.land');
        return $db->fetchAll($query);
    }


}
