<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 20.07.2012
 */
class Default_Model_DbTable_Select extends Zend_Db_Table_Select {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 20.07.2012
     * @param Default_Model_DbTable_Base $table
     */
    public function __construct(Default_Model_DbTable_Base $table) {

        parent::__construct($table);
        
        $this->_adapter = $table->getAdapterReadOnly();
    }

}
