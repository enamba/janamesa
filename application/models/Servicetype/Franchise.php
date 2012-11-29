<?php

/**
 * Model Servicetype Franchise
 * @author Allen Frank <frank@lieferando.de>
 */
class Yourdelivery_Model_Servicetype_Franchise extends Default_Model_Base {

    /**
     * singleton storage
     * @var array
     */
    static private $instances = array();
    
    /**
     * this object is nearly never in flux, so we use a singleton here
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 15.08.2012
     * 
     * @param integer $franchiseId
     * @return Yourdelivery_Model_Servicetype_Franchise
     */
    public static function getInstance($franchiseId){
        if (array_key_exists($franchiseId, self::$instances) && self::$instances[$franchiseId] instanceof Yourdelivery_Model_Servicetype_Franchise ){
            return self::$instances[$franchiseId];
        }
        self::$instances[$franchiseId] = new self($franchiseId);
        return self::$instances[$franchiseId];
    }
    
    /**
     * Inserts a new franchise with given name
     * @author Allen Frank <frank@lieferando.de>
     * @since 21-02-2012
     * @param type $franchiseName
     * @return id of the new row 
     */
    public function setFranchise($franchiseName) {
        $franchiseName = str_replace(' ','',iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $franchiseName));
        
        $dbTable = new Yourdelivery_Model_DbTable_Restaurant_Franchise();
        $franchises = $dbTable->getAllNames();
        foreach ($franchises as $franchise) {
            if (strcasecmp($franchise['name'],$franchiseName) == 0 ) {
                return (integer) $franchise['id'];
            }
        }
        return $dbTable->insert(array('name' => $franchiseName));
    }

    /**
     * Get all
     * @author Allen Frank <frank@lieferando.de>
     * @since 15-02-2012
     * @return array
     */
    public static function all() {

        $dbTable = new Yourdelivery_Model_DbTable_Restaurant_Franchise();
        return $dbTable->fetchAll(null, "name ASC");
    }

    /**
     * Get table
     * @author Allen Frank <frank@lieferando.de>
     * @since 15-02-2012
     * @return Yourdelivery_Model_DbTable_Restaurant_Franchise
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Franchise();
        }
        return $this->_table;
    }

    protected $_names = null;
    protected $_noContractIds = null;
    /**
     * @author Allen Frank <frank@lieferando.de>
     * @return array 
     */
    public function getNames($toAscii = false) {
        
        $hash = sprintf('franchiseNames%d',(integer) $toAscii);
        if(!is_null($this->_names[$hash])){
            return $this->_names[$hash];
        }
        
        $this->_names[$hash] = Default_Helpers_Cache::load($hash);
        if(!is_null($this->_names[$hash])){
            return $this->_names[$hash];
        }
        
        
        $table = new Yourdelivery_Model_DbTable_Restaurant_Franchise();
        $tmp = $table->getAllNames();
        $names = array();
        foreach ($tmp as $name) {
            $value = lcfirst($name['name']);
            if ($toAscii) {
                $value = str_replace(' ','',iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $value));
            }
            $names[$name['id']] = $value;
        }
        $this->_names[$hash] = $names;
        Default_Helpers_Cache::store($hash, $names);
        return $this->_names[$hash];
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @return string 
     */
    public static function getById($id, $toAscii = false, $translate = false, $replaceNoContractIds = true) {

        $hash = sprintf('franchiseNames%d%d%d%d', $id,(integer) $toAscii, (integer) $translate, (integer) $replaceNoContractIds);
        
        $name = Default_Helpers_Cache::load($hash);
        if (is_null($name)) {

            $config = Zend_Registry::get('configuration');
            $noContractIds = array();
            if($replaceNoContractIds 
                    && $config->franchise 
                    && $config->franchise->noContractIds) {
                $noContractIds = $config->franchise->noContractIds->toArray();
            }

            if($replaceNoContractIds && in_array($id, $noContractIds)) {
                $id = (int)$noContractIds[0];
            }
            $table = new Yourdelivery_Model_DbTable_Restaurant_Franchise();
            $nameRow = $table->findById($id);
            
            if ($toAscii) {
                $name =  str_replace(' ','',lcfirst(iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $nameRow['name'])));
            }
            if ($translate) {
                $name = __b($nameRow['name']);
            } else {
                $name = lcfirst($nameRow['name']);
            }
            
            Default_Helpers_Cache::store($hash, $name);
        }
        
        return $name;
    }
    
    /**
     * get an ascii name for a given ID
     * 
     * @author Jens Naie <naie@lieferando.de>
     * @since 08.08.2012 
     */
    public function getAsciiNameById($id, $replaceNoContractIds = true) {

        if ($replaceNoContractIds) {
            if (is_null($this->_noContractIds)) {
                $config = Zend_Registry::get('configuration');
                $this->_noContractIds = array();
                if($config->franchise->noContractIds) {
                    $this->_noContractIds = $config->franchise->noContractIds->toArray();
                }
            }
            if(in_array($id, $this->_noContractIds)) {
                $id = (int)$this->_noContractIds[0];
            }
        }

        $names = $this->getNames(true);
        if ($names[$id]) {
            return $names[$id];
        }
        
        return '';
    }

        
    /**
     * clear cache for franchiseType model
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.07.2012 
     */
    public function clearCache(){
        $this->logger->debug('clearing cache for franchisetype');
        Default_Helpers_Cache::remove('franchiseNames0');
        Default_Helpers_Cache::remove('franchiseNames1');
    }

}
