<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 15.06.2012
 */
class Yourdelivery_Model_Support_Blacklist_Values extends Default_Model_Base {
   
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 15.06.2012
     * @return Yourdelivery_Model_DbTable_Blacklist_Values
     */
    public function getTable() {
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Blacklist_Values();
        }
        
        return $this->_table;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.08.2012
     * @param int $id
     * @param string $type
     * @param string $value
     */
    public function __construct($id = null, $type = null, $value = null) {

        if ($type !== null && $value !== null) {
            $id = null;
            $rows = $this->getTable()
                         ->findByTypeValue($type, $value);
            foreach ($rows as $row) {
                $id = $row->id;
                break;
            }

            if ($id === null) {
                throw new Yourdelivery_Exception_Database_Inconsistency(sprintf('Element with type %s and value %s cannot not be found.', $type, $value));
            }
        }

        parent::__construct($id);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.06.2012
     * @return int
     */
    public function getBehaviourToOrderState() {
        
        return Yourdelivery_Model_Support_Blacklist::getBehaviourToOrderState($this->getBehaviour());
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.06.2012
     * @return Yourdelivery_Model_Support_Blacklist_Matching[]
     */
    public function getMatchings() {
        
        $values = array();
        
        $rows = $this->getTable()
                     ->getCurrent()
                     ->findDependentRowset("Yourdelivery_Model_DbTable_Blacklist_Matching");
        foreach ($rows as $row) {
            $values[] = new Yourdelivery_Model_Support_Blacklist_Matching($row->id);
        }
        return $values;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.08.2012
     * @return boolean
     */
    public function restore() {

        if (!$this->getId()) {
            return false;
        }

        if ($this->getDeleted()) {
            $this->setDeleted(0);
            $this->save();
            return true;
        }

        return false;
    }
}
