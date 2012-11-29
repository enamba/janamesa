<?php

/**
 * model for table city, replacement for old table 'orte'
 * @author alex
 * @since 01.03.2011
 */

class Yourdelivery_Model_City extends Default_Model_Base{
    /**
     * get all entries by plz
     * @author alex
     * @since 01.03.2011
     * @param string $plz
     * @return array
     */
    static function getByPlz($plz) {
        if ($plz === null) {
            return array();
        }

        $config = Zend_Registry::get('configuration');
        //add for janamesa, which plz are split with a - at the end
        //but some customers do not enter that
        if ( $config->domain->base == 'janamesa.com.br' && !strstr($plz, '-') ){
            $cep = substr($plz,0,-3) . '-' . substr($plz,-3);
        }
        else{
            $cep = $plz;
        }

        $table = new Yourdelivery_Model_DbTable_City();
        return $table->fetchAll(
            $table->select()
                  ->where("plz = ?", $plz)
                  ->orWhere("plz = ?", $cep));
    }

    /**
     * get all entries by city
     * @author alex
     * @since 01.03.2011
     * @param string $city
     * @return array
     */
    static function getByCity($city) {
        if ($city === null){
            return array();
        }

        if (!strlen($city)){
            return array();
        }

        $table = new Yourdelivery_Model_DbTable_City();
        return $table->fetchAll(
            $table->select()
                  ->where("city = ?", $city));
    }

    /**
     * get names of all cities
     * @author alex
     * @since 01.03.2011
     * @return array
     */
    public static function getAllCities() {
        return Yourdelivery_Model_DbTable_City::getAllCities();
    }

    /**
     * get names of all states
     * @author alex
     * @since 07.04.2011
     * @return array
     */
    public static function getAllStates() {
        return Yourdelivery_Model_DbTable_City::allStates();
    }

    /**
     * get all entries from city table
     * @author alex
     * @since 08.03.2011
     * @return array
     */
    public static function all() {
        return Yourdelivery_Model_DbTable_City::all();
    }

    /**
     * get all entries from city table where plz is starting with the defined number
     * @author alex
     * @since 13.04.2011
     * @return array
     */
    public static function allStartingAt($startingWith, $orderBy = 'plz') {
        return Yourdelivery_Model_DbTable_City::allStartingAt($startingWith, $orderBy);
    }

    /**
     * get all entries from city table where entries has the same plz as the given one, except this one
     * @author alex
     * @since 06.06.2011
     * @return array
     */
    public static function possibleParentsForCityId($cityId, $orderBy = 'plz') {
        if (intval($cityId) == 0) {
            return null;
        }
        return Yourdelivery_Model_DbTable_City::possibleParentsForCityId($cityId, $orderBy);
    }

    /**
     * Get related table
     * @author vpriem
     * @since 10.03.2011
     * @return Yourdelivery_Model_DbTable_City
     */
    public function getTable(){

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_City();
        }
        return $this->_table;

    }

    /**
     * Return a name of a city
     * @author vpriem
     * @since 26.04.2011
     * @return string
     */
    public function __toString(){

        $city = $this->getCity();
        return $city === null ? "" : $city;

    }

    /**
     * return a name of a city
     * @return string
     */
    public function getOrt(){
        return $this->getFullName();
    }

    /**
     * Full name - the name of this city or <name of parent> (<city name>)
     * @author alex
     * @since 05.08.2011
     * @return string
     */
    public function getFullName(){
        return $this->getTable()->getFullName();
    }

    /**
     * Get url from mode
     * @author vpriem
     * @since 05.09.2011
     * @return string
     */
    public function getUrl($mode = null) {

        switch ($mode) {
            case 'cater':
                return $this->getCaterUrl();
            case 'great':
                return $this->getGreatUrl();
        }

        return $this->getRestUrl();
    }
    /**
     * get additional information about that 
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 03.05.2012
     * @return array
     * @see http://ticket.yourdelivery.local/browse/YD-844
     */
    public function getVerboseInformation(){
        $verbose = new Yourdelivery_Model_DbTable_City_Verbose();
        return $verbose->getInformation($this->getId());
    }


    /**
     * Delete the cache for plz
     * @author alex
     * @since 28.07.2011
     */
    public function uncache() {
        $restUrl = APPLICATION_PATH . "/../public/cache/html/" . HOSTNAME . "/" . $this->getRestUrl() . ".html";
        if(file_exists($restUrl)){
            unlink($restUrl);
        }

        $caterUrl = APPLICATION_PATH . "/../public/cache/html/" . HOSTNAME . "/" . $this->getCaterUrl() . ".html";
        if(file_exists($caterUrl)){
            unlink($caterUrl);
        }

        $greatUrl = APPLICATION_PATH . "/../public/cache/html/" . HOSTNAME . "/" . $this->getGreatUrl() . ".html";
        if(file_exists($greatUrl)){
            unlink($greatUrl);
        }

        $this->logger->info(sprintf("Yourdelivery_Model_Servicetype_Restaurant: Cache was flushed for cityId #%s", $this->getId()));
    }

    /**
     * get all entries, which plz starts wiht the specified string
     * @author Alex Vait <vait@lieferando.de>
     * @since 05.01.2012
     * @param string $plzprefix
     * @return array
     */
    static function getPlzByPrefix($plzprefix = null) {
        if ( (is_null($plzprefix)) || (strlen($plzprefix) == 0) ){
            return array();
        }

        $table = new Yourdelivery_Model_DbTable_City();
        return $table->fetchAll(
            $table->select()
                  ->where("plz LIKE '" . $plzprefix . "%'"));
    }

    /**
     * Returns city entry by its id
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 5.07.2012
     *
     * @param int $id
     * @return std_obj
     */
    public function getById($id) {
        $table = new Yourdelivery_Model_DbTable_City();
        return $table->fetchRow($table->select()->where("id = ?", $id));
    }

    /**
     * Get a all rows matching direct city link
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     * @return void
     */
    public function setGeoReferences() {
        if (($plz = $this->getPlz()) && !$this->getDistrictId()) {
            $geoTable = new Yourdelivery_Model_DbTable_GeoPC();
            $select = $geoTable->select()
                               ->where('ZIP = ?', $plz)
                               ->where('City = ?', $this->getCity());
            $geoRow = $geoTable->fetchRow($select);
            if(!$geoRow) {
                $select = $geoTable->select()
                                ->where('ZIP = ?', $plz);
                $geoRow = $geoTable->fetchRow($select);
            }
            if ($geoRow) {
                $this->setLat($geoRow->Lat);
                $this->setLng($geoRow->Lng);
                $this->setDistrictId($geoRow->districtId);
                $this->setRegionId($geoRow->regionId);
                $this->save();

                $districtTable = new Yourdelivery_Model_DbTable_Districts();
                $districtTable->update(array('used' => true), 'id = ' . $geoRow->districtId);
                $regionTable = new Yourdelivery_Model_DbTable_Districts();
                $regionTable->update(array('used' => true), 'id = ' . $geoRow->regionId);
            }
        }
    }
}
