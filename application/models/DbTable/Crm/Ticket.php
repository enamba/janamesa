<?php
/**
 * DB model for crm ticket
 * @author alex
 * @since 12.07.2011
 */
class Yourdelivery_Model_DbTable_Crm_Ticket extends Default_Model_DbTable_Base{
    
    protected $_name = "crm_ticket";

    protected $_primary = 'id';

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     * @author alex
     * @since 29.06.2011
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('crm_ticket', 'crm_ticket.id = ' . $id);
    }
    
    /**
     * get tickets for certain admin
     * @author alex
     * @since 13.07.2011
     * @return array
     */
    static function getTickets($adminId, $isClosed = null) {
        $db = Zend_Registry::get('dbAdapter');

        if (!is_null($isClosed)) {            
            $whereAtt = " and closed = " . intval($isClosed);
        }
        $query = $db->select()
                    ->from( array("crm" => "crm_ticket") )                           
                    ->where( "crm.assignedToId = " . $adminId . $whereAtt);

        return $db->fetchAll($query); 
    }
        
}
