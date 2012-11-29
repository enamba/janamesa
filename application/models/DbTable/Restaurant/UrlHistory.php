<?php
/**
 * @author Daniel Hahn <hahn@lieferando.de>
 */
class Yourdelivery_Model_DbTable_Restaurant_UrlHistory extends Default_Model_DbTable_Base {
    //put your code here
    
    /**
     * name of the table
     * @param string
     */
    protected $_name = 'restaurant_url_history';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param string $url
     * @return  
     */
    public function findByUrl($url) {       
        
        $where = $this->getAdapter()->quoteInto('url LIKE ? ', $url);
        
        return $this->fetchAll($where, 'url');
        
    }
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param string $url
     * @return array|null
     */
    public static function findByDirectLink($url) {
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                     ->from('restaurant_url_history')
                     ->where('url = ? ', $url);
        
        $row = $db->fetchRow($select);
         if ($row) {
             return $row;
         }
        
         return null;
    }
    
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param string $restaurantId
     * @return array|null
     */
    public static function findByRestaurantId($restaurantId) {
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        
        $select = $db->select()
                     ->from('restaurant_url_history')
                     ->where('restaurantId = ? ', $restaurantId);
        
        $rows = $db->fetchAll($select);
        if (count($rows) > 0) {
            return $rows;
        }
        
        return null;
    }

}
