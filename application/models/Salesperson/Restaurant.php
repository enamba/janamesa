<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of salesperson-restaurant relationship
 *
 * @author vait
 */
class Yourdelivery_Model_Salesperson_Restaurant extends Default_Model_Base {

    /**
     * Adds a salesperson<->restaurant relationship
     *
     * @param array $salespersonId
     * @param int $restaurantId
     * @return
     */
    public static function add($salespersonId, $restaurantId, $signed = null) {
        if ( is_null($salespersonId) || is_null($restaurantId) ){
            return false;
        }

        if (is_null(signed)) {
            $signed = date("Y-m-d H:i:s", time());
        }

        $table = new Yourdelivery_Model_DbTable_Salesperson_Restaurant();
        $row = $table->createRow();
        $row->salespersonId = $salespersonId;
        $row->restaurantId = $restaurantId;
        $row->signed = $signed;
        $row->save();

        return true;
    }

    /**
     * Removes salesperson<->restaurant relationship
     *
     * @param int $salespersonId
     * @param int $restaurantId
     * @return 
     */
    public static function delete($salespersonId, $restaurantId) {
        if ( is_null($salespersonId) || is_null($restaurantId) ){
            return false;
        }

        $db = Zend_Registry::get('dbAdapter');
        $db->delete('salesperson_restaurant', 'salesperson_restaurant.salespersonId = ' . $salespersonId . ' and ' . 'salesperson_restaurant.restaurantId = ' . $restaurantId);

        return true;
    }

    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Salesperson_Restaurant();
        }
        return $this->_table;
    }
}
?>
