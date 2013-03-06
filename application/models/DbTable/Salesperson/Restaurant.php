<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of salesperson-restaurant relationship
 *
 * @author alex
 */
class Yourdelivery_Model_DbTable_Salesperson_Restaurant extends Default_Model_DbTable_Base {

    protected $_name = "salesperson_restaurant";

   /**
     * check if a salesperson is responsible for the restaurant
     * @author alex
     * @return bool
     */
    public function isSalespersonFor($salespersonId, $restaurantId){
        $sql = sprintf('select * from salesperson_restaurant where salespersonId=%d and restaurantId=%d', $salespersonId, $restaurantId);
        $query = $this->getAdapter()->query($sql);

        if (count($query->fetchAll()) > 0) {
            return true;
        }
       
        return false;
    }

   /**
     * get all restaurants this saler is responsible for
     * @author alex
     * @return array
     */
    public static function getRestaurantsForSalesperson($salespersonId){
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf('select * from salesperson_restaurant where salespersonId=%d', $salespersonId);
        $query = $db->query($sql);
        return $query->fetchAll();
    }

    /**
     * delete a table row by given salespersonId
     * @author alex
     * @param integer $id
     * @return void
     */
    public static function removeBySalesperson($salespersonId)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('salesperson_restaurant', 'salesperson_restaurant.salespersonId = ' . $salespersonId);
    }
    
    /**
     * delete a table row by given restaurantId
     * @author alex
     * @param integer $id
     * @return void
     */
    public static function removeByRestaurant($restaurantId)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('salesperson_restaurant', 'salesperson_restaurant.restaurantId = ' . $restaurantId);
    }

    /**
     * get salespersons relationships in the certain time slot
     * @param int $from
     * @param int $until
     * @author alex
     * @since 29.11.2010
     */
    public static function getContracts($from, $until) {
        $db = Zend_Registry::get('dbAdapter');
        
        $select = $db
            ->select()
            ->from(array("sr" => "salesperson_restaurant"), 
                        array(
                            "contractCreated" => "DATE_FORMAT(sr.signed, '%d.%m.%Y %H:%i')",
                            "contractId" => "sr.id",
                            "sr.salespersonId",
                            "salespersonName" => "s.name",
                            "salespersonPrename" => "s.prename",
                            "salespersonCallcenter" => "s.callcenter",
                            "salespersonSalary" => "s.salary",
                            "sr.restaurantId",
                            "sr.paid",
                            "restaurantName" => "r.name",
                            "categoria" => "rc.name",
                            "restaurantOnlyCash" => "r.onlycash",
                            "restaurantIsOnline" => "r.isOnline",
                            "restaurantStatus" => "r.status",
                            "restaurantDeleted" => "r.deleted",
                            "restaurantKomm" => "r.komm",
                            "restaurantFee" => "r.fee",
                            "hasContract" => new Zend_Db_Expr("r.franchiseTypeId<>2"),
                            "restaurantOrt" => "c.city",
                            "franchisetype" => "ft.name"
                    ))
            ->join(array("s" => "salespersons"), "s.id=sr.salespersonId", array())
            ->join(array("r" => "restaurants"), "r.id=sr.restaurantId", array())
            ->join(array("rc" => "restaurant_categories"), "r.categoryId=rc.id", array())
            ->join(array("ft" => "restaurant_franchisetype"), "ft.id=r.franchiseTypeId", array())
            ->join(array("c" => "city"), "c.id=r.cityId", array())
            ->where("sr.signed between '" . $from . "' and '" . $until . "'")
            ->where('r.deleted = 0');
        
        return $select
                ->query()
                ->fetchAll();        
    }


}
?>
