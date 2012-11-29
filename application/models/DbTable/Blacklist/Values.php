<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.06.2012
 */
class Yourdelivery_Model_DbTable_Blacklist_Values extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string 
     */
    protected $_name = 'blacklist_values';

    /**
     * Primary key name
     * @var string 
     */
    protected $_primary = 'id';

    /**
     * @var string 
     */
    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Blacklist_Values';
    
    /**
     * @var array
     */
    protected $_referenceMap = array(
        'Blacklist' => array(
            'columns'       => 'blacklistId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Blacklist',
            'refColumns'    => 'id',
        )
    );
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     * @param array $types 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getList(array $types) {
        
        return $this->fetchAll(
                $this->select()
                     ->where("`type` IN (?)", $types)
                     ->where("`deleted` = 0")
                
        );
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     * @param string $type
     * @param string $value
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findByTypeValue($type, $value) {
        
        return $this->fetchAll(
                $this->select()
                     ->where("`type` = ?", $type)
                     ->where("`value` = ?", $value)
        );
    }
}

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.06.2012
 */
class Yourdelivery_Model_DbTableRow_Blacklist_Values extends Default_Model_DbTable_Row {
    
    /**
    * @author Vincent Priem <priem@lieferando.de>
    * @since 18.06.2012
     * @return boolean
    */
    public function isDeprecated() {
        
        if ($this->type == Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_IP) {
            $timestamp = strtotime($this->created);
            if ($timestamp + (24 * 60 * 60) < time()) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
    * @author Vincent Priem <priem@lieferando.de>
    * @since 18.06.2012
    */
    public function delete() {
        
        $this->deleted = 1;
        $this->save();
    }
    
    /**
    * @author Vincent Priem <priem@lieferando.de>
    * @since 18.06.2012
    */
    public function addMatching($orderId) {
        
        $dbTable = new Yourdelivery_Model_DbTable_Blacklist_Matching();
        $dbTable->createRow(array(
            'valueId' => $this->id,
            'orderId' => $orderId,
        ))->save();
        
        $this->hits++;
        $this->save();
    }
}