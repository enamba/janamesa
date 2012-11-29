<?php
/**
    * Database interface for Yourdelivery_Models_DbTable_Restaurant_Notepad
    *
    * @author alex
    * @since 07.10.2010
    *
*/

class Yourdelivery_Model_DbTable_Restaurant_Notepad extends Default_Model_DbTable_Base
{    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'restaurant_notepad';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    
    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_notepad', 'restaurant_notepad.id = ' . $id);
    }

    /**
     * get all comments for this restaurant with corresponding author
     * @param integer $id
     */
    public static function getComments($restaurantId){
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("select rn.id as id, masterAdmin, time, comment, aau.email as aEmail, aau.name as aName, cu.prename as cPrename, cu.name as cName, cu.email as cEmail from restaurant_notepad rn left join admin_access_users aau on aau.id=rn.adminId left join customers cu on cu.id=rn.adminId where rn.restaurantId=%d order by time desc", $restaurantId);
        return $db->query($sql)->fetchAll();
    }

    /**
     * get all comments of yesterday with restaurant data
     * @author alex
     * @since 09.12.2010
     */
    public static function getAllChangesOfYesterday(){
        $db = Zend_Registry::get('dbAdapter');
        $changes = $db->fetchAll(
            "SELECT r.id AS restaurantId,
                    r.name AS restaurantName,
                    masterAdmin,
                    time,
                    comment,
                    aau.email AS aEmail,
                    aau.name AS aName,
                    cu.prename AS cPrename,
                    cu.name AS cName,
                    cu.email AS cEmail
                FROM restaurant_notepad rn
                JOIN restaurants r ON rn.restaurantId=r.id
                LEFT JOIN admin_access_users aau ON aau.id=rn.adminId
                LEFT JOIN customers cu ON cu.id=rn.adminId
                WHERE DATE_FORMAT(time, '%Y-%m-%d')=DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d')
                ORDER BY time ASC");
        
        return $changes;
    }
    
}
