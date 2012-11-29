<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_RestaurantOpenings.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Restaurant_Company extends Default_Model_DbTable_Base
{

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'restaurant_company';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_referenceMap    = array(
        'Company' => array(
            'columns'           => 'companyId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Company',
            'refColumns'        => 'id'
        ),
        'Restaurant' => array(
            'columns'           => 'restaurantId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Restaurant',
            'refColumns'        => 'id'
        )
    );

    /**
     * Adds a company<->restaurnat relationship
     *
     * @param int $restaurantId
     * @param int $companyId
     * @return
     */
    public static function add($restaurantId, $excl, $companyId) {
        $relationTable = new Yourdelivery_Model_DbTable_Restaurant_Company();
        return $relationTable->insert(
                array(
                    'restaurantId' => $restaurantId,
                    'companyId' => $companyId,
                    'created' => date('Y:m:d H:i:s'),
                    'exclusive' => $excl
                    )
                );
    }

    /**
     * get a rows matching this restaurant and company Ids
     * @param int $id
     */
    public static function findByAssoc($restId, $compId)
    {
        if ( is_null($restId) || is_null($compId) )
            return null;
        
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                    ->from( array("rc" => "restaurant_company") )
                    ->where( "rc.restaurantId = " . $restId . " and companyId = " . $compId);

        return $db->fetchRow($query);
    }

    /**
     * Removes a company<->restaurnat relationship
     *
     * @param int $restaurantId
     * @param int $companyId
     * @return
     */
    public static function remove($restaurantId, $companyId) {
        $relationTable = new Yourdelivery_Model_DbTable_Restaurant_Company();
        return $relationTable->delete('restaurantId = ' . $restaurantId . ' AND companyId = ' . $companyId);
    }

}
