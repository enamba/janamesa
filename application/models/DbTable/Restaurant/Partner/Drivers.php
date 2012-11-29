<?php


/**
 * Description of Drivers
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Model_DbTable_Restaurant_Partner_Drivers extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'restaurant_partner_drivers';
    
    /**
     * find a driver and return id
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.08.2012
     * @param string $driver
     * @param integer $restaurantId
     * @return integer
     */
    public function findDriver($driver, $restaurantId){
        $id = (integer) $this->select(array('id'))
                             ->where('name=?', $driver)
                             ->where('restaurantId=?', $restaurantId)
                             ->query()
                             ->fetchColumn();
        if ( $id <= 0 ){
            $id = $this->createRow(array(
                'name' => $driver,
                'restaurantId' => $restaurantId
            ))->save();
        }
        
        return $id;
    }
}