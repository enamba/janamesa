<?php
/**
 * Description of Code
 *
 * @author vait
 */
class Yourdelivery_Model_Servicetype_OpeningsSpecial extends Default_Model_Base {

    public function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Openings_Special();
        }
        return $this->_table;
    }

    /**
     * get special openings for this restaurant
     * @author mlaug
     * @return RowSet
     */
    public static function getSpecialOpening($restaurantId, $current = false) {
        $db = Zend_Registry::get('dbAdapter');

        $result = array();

        try{
            $sql = 'select * from restaurant_openings_special ros where ros.restaurantId = '. $restaurantId . ' order by ros.specialDate, ros.from';
            $select= $db->select()->from('restaurant_openings_special')->where('restaurantId = ?', $restaurantId)->order('specialDate')->order('from');      
            if($current) {
                $select->where('DATE(specialDate)  > DATE(NOW()) ');
            }
            
            $result = $db->fetchAll($select);
        }
        catch ( Zend_Db_Statement_Exception $e ){
            return 0;
        }

        return $result;        
    }

}
?>
