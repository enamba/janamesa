<?php
/**
 * Crm ticket history model
 * @package crm
 * @author alex
 * @since 30.06.2011
 */
class Yourdelivery_Model_Crm_Tickethistory extends Default_Model_Base{

    /**
     * Get table
     * @author alex
     * @since 14.07.2011
     * @return Yourdelivery_Model_DbTable_Crm_Tickethistory
     */
    public function getTable(){
        
        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Crm_Tickethistory();
        }
        return $this->_table;
    }
}
