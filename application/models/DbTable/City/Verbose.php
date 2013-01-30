<?php

/**
 * Verbose Information for a city
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Model_DbTable_City_Verbose extends Default_Model_DbTable_Base {

    protected $_name = 'city_verbose';

    /**
     * get additional information based on a city id
     * this is a feature for janamesa, since they only know their "cep"
     * and not their adress... such wankers!
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.12.2011
     * @param integer $cityId
     * @return array
     * @see http://ticket.yourdelivery.local/browse/YD-844
     */
    public function getInformation($cityId) {
        return $this->select()
                        ->where('cityId=?', (integer) $cityId)
                        ->query()
                        ->fetchAll();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.01.2012
     * @return Zend_Db_Table_Rowset
     */
    public function getCities() {
        return $this->getAdapter()
                        ->select()
                        ->from(array('c' => 'city'), array('c.city'))
                        ->group('c.city')
                        ->query()
                        ->fetchAll();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.01.2012
     * @return Zend_Db_Table_Rowset
     */
    public function getStreetTypes() {
        return $this->select()
                        ->from(array('c' => 'city_verbose'), array('c.tp_street'))
                        ->group('tp_street')
                        ->query()
                        ->fetchAll();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.01.2012
     * @return Zend_Db_Table_Rowset
     */
    public function findmatch($city, $street, $number = null) {
        $select = $this->getAdapter()
                ->select()
                ->from(array('cv' => 'city_verbose'), array('vId' => 'cv.id', 'street' => 'cv.street', 'number' => 'cv.number', 'neighbour' => 'cv.neighbour'))
                ->join(array('c' => 'city'), 'c.id=cv.cityId', array('cep' => 'c.plz', 'url' => 'c.restUrl', 'cityId' => 'c.id'))
                ->where('c.city=?', $city)
                ->where('cv.street LIKE "%'.$street.'%"')
                ->limit(50);

        //if a number has been provided
        if ($number !== null && !empty($number)) {
            $select->where('? REGEXP cv.number_regex', $number); //create regex for number search
        }
        
        return $select->query()
                ->fetchAll();
    }

}

?>
