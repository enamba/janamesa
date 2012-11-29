<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 16.11.2010
 */
class Yourdelivery_Model_DbTable_Heyho_Messages extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'heyho_messages';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * Get all available messages from table
     * 
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.11.2011
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getMessages(){
        
        return $this->fetchAll(
            $this->select()
                 ->where('`adminId` IS NULL')
                 ->where('`state` = 0'));             
    }

}