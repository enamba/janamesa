<?php

/**
 * @package location
 */

class Yourdelivery_Model_Orte extends Default_Model_Base{

    /**
     * Get name of city by given postal code
     * @author vpriem
     * @since 10.02.2011
     * @param string $plz
     * @return string
     */
    static function getNameByPlz ($plz = null) {

        if ($plz === null) {
            return null;
        }
        $row = Yourdelivery_Model_DbTable_Orte::findByPlz($plz);
        if ($row !== null) {
            return $row['ort'];
        }
        return null;

    }

    /**
     * provide a city and get all plz
     * @author mlaug
     * @param string $city
     * @return int
     */
    static function getPlzByName($city = null){
        if ( is_null($city) ){
            return null;
        }
        $table = new Yourdelivery_Model_DbTable_Orte();
        return $table->fetchAll("ort = '" . $city . "'");
    }

    /**
     * get names of all cities
     * @author mlaug
     * @return array
     */
    public static function getAllCities() {
        return Yourdelivery_Model_DbTable_Orte::getAllCities();
    }

    /**
     * get related table
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Cms
     */
    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Orte();
        }
        return $this->_table;
    }

    /**
     * ->getOrt()->getOrt() is a bit of convinience
     * @return string
     */
    public function __toString(){
        return $this->getOrt();
    }
    
}
?>