<?php
/**
 * GPRS printer to restaurants association
 * @author alex
 * @since 24.05.2011
*/
class Yourdelivery_Model_DbTable_Restaurant_Printer extends Default_Model_DbTable_Base{
    
    /**
     * Table name
     * @var string
     */
    protected $_name = 'restaurant_printer_topup';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * @var array
     */
    protected $_referenceMap = array(
        'Restaurant' => array(
            'columns'       => 'restaurantId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Restaurant',
            'refColumns'    => 'id'
        ),
        'Printer' => array(
            'columns'       => 'printerId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Printer_Topup',
            'refColumns'    => 'id'
        )
    );
    
    /**
     * @author vpriem
     * @since 16.08.2011
     * @param string $restaurantId
     * @return array
     */
    public function findByRestaurantId($restaurantId) {
        
        $rows = $this->getAdapter()->fetchAll(
            $this->getAdapter()
                 ->select()
                 ->from(array('rpt' => 'restaurant_printer_topup'))
                 ->joinLeft(array('pt' => 'printer_topup'), 'pt.id = rpt.printerId', array('type'))
                 ->where("rpt.restaurantId = ?", $restaurantId)
        );
        
        if (count($rows)) {
            return $rows[0];
        }
        
        return null;
    }
}
