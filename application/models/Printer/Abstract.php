<?php

/**
 * @author Daniel Hahn <hahn@lieferando.de>
 */
abstract class Yourdelivery_Model_Printer_Abstract extends Default_Model_Base {

    const TYPE_TOPUP = "topup";
    const TYPE_WIERCIK = "wiercik";
    
    /**
     * @var Yourdelivery_Model_DbTable_Printer_Topup
     */
    protected $_table = null;

    /**
     * @var string
     */
    protected $_type = null;


    /**
     * 
     * Do not call directly, use Factory to avoid Exceptions
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 02.05.2012
     * @param type $id
     * @param type $current
     * @throws Yourdelivery_Exception_Database_Inconsistency 
     */
    public function __construct($id = null, $current = null) {
        
        parent::__construct($id, $current);
        
        if ($id === null) {
            $this->setType($this->_type);
        }
        
        if ($this->getType() != $this->_type) {
            throw new Yourdelivery_Exception_Database_Inconsistency("Type of printer is not " . $this->_type);
        }
    }
    
    /**
     * @author vpriem
     * @since 04.05.2012
     * @return string 
     */
    public function getClassType() {
        
        return $this->_type;
    }
    
    /**
     * Get related table
     * @author vpriem
     * @since 27.07.2011
     * @return Yourdelivery_Model_DbTable_Printer_Topup
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Printer_Topup();
        }
        return $this->_table;
    }

    abstract function pushOrder($orderId);

    abstract function isOnline();

    /**
     * Return all assigned restaurants
     * @author vpriem
     * @since 16.11.2011
     * @return SplObjectStorage
     */
    public function getRestaurants() {

        $spl = new SplObjectStorage();

        $rows = $this->getTable()
                ->getCurrent()
                ->findDependentRowset("Yourdelivery_Model_DbTable_Restaurant_Printer");
        foreach ($rows as $row) {
            try {
                $spl->attach(new Yourdelivery_Model_Servicetype_Restaurant($row->restaurantId));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
        return $spl;
    }

    /**
     * Return all restaurants associations
     * @author vpriem
     * @since 17.11.2011
     * @return SplObjectStorage
     */
    public function getRestaurantAssociations() {

        $spl = new SplObjectStorage();

        $rows = $this->getTable()
                ->getCurrent()
                ->findDependentRowset("Yourdelivery_Model_DbTable_Restaurant_Printer");
        foreach ($rows as $row) {
            try {
                $spl->attach(new Yourdelivery_Model_Servicetype_Printer($row->id));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
        return $spl;
    }

    /**
     * Add restaurant
     * @author vpriem
     * @since 16.11.2011
     * @return boolean
     */
    public function addRestaurant($restaurant) {

        $restaurantId = $restaurant;
        if ($restaurant instanceof Yourdelivery_Model_Servicetype_Abstract) {
            $restaurantId = $restaurant->getId();
        }

        foreach ($this->getRestaurants() as $r) {
            if ($r->getId() == $restaurantId) {
                return true;
            }
        }

        $assoc = new Yourdelivery_Model_Servicetype_Printer();
        $assoc->setRestaurantId($restaurantId);
        $assoc->setPrinterId($this->getId());
        $assoc->save();

        return true;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param int $id
     * @param string $type
     * @return \Yourdelivery_Model_Printer_Topup|\Yourdelivery_Model_Printer_Wiercik
     * @throws Yourdelivery_Exception_Database_Inconsistency 
     */
    public static function factory($id, $type = null) {
        
        $db = Zend_Registry::get('dbAdapterReadOnly');

        if (is_null($type)) {
            $select = $db->select()
                         ->from('printer_topup')
                         ->where('id = ?', $id);
            $row = $db->fetchRow($select);                     
        } else {
            $row = array(
                'id' => $id, 
                'type' => $type);
        }


        if ($row && $row['id']) {
            switch ($row['type']) {
                default;
                case self::TYPE_TOPUP: 
                    return new Yourdelivery_Model_Printer_Topup($row['id']);

                case self::TYPE_WIERCIK: 
                    return new Yourdelivery_Model_Printer_Wiercik($row['id']);
            }
        }

        throw new Yourdelivery_Exception_Database_Inconsistency('No Printer found for this Id');
    }

}

