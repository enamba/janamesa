<?php
/**
    * Database interface for Yourdelivery_Models_DbTable_Restaurant_Notepad_Ticket
    *
    * @author Daniel Hahn <hahn@lieferando.de>
    * @since 28.09.2011
    *
*/

class Yourdelivery_Model_DbTable_Restaurant_Notepad_Ticket extends Default_Model_DbTable_Base
{    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'restaurant_notepad_ticket';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    
    /**
     * delete a table row by given primary key
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 28.09.2011
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_notepad_ticket', 'id = ' . ((integer) $id));
    }


    /**
     * get all comments of Today 
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 09.12.2010
     */
    public static function getAllCommentsOfToday($restaurantId){
        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()->from(array('rnt'  => 'restaurant_notepad_ticket'),array("rnt.id", "rnt.comment","time" => "rnt.created", "aau.name"))
                                               ->joinLeft(array('aau' => 'admin_access_users'), "aau.id=rnt.adminId")
                                               ->where('rnt.restaurantId=?',$restaurantId)
                                               ->where('DATE(rnt.created) = DATE(NOW())')
                                               ->order('rnt.created DESC')
                                               ->limit(1);
                     
        
       // echo $select; die();
        
        $changes = $db->fetchAll($select);
        
        return $changes;
    }
           
}
