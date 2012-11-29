<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_RabattCodes.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_RabattCodes extends Default_Model_DbTable_Base
{

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'rabatt_codes';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_referenceMap    = array(
        'Rabatt' => array(
            'columns'           => 'rabattId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Rabatt',
            'refColumns'        => 'id'
        )
    );

    /**
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public static function edit($id, $data)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('rabatt_codes', $data, 'rabatt_codes.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('rabatt_codes', 'rabatt_codes.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function removeCodes($rabattId){
        if(is_null($rabattId)){
            return;
        }

        $db = Zend_Registry::get('dbAdapter');
        $db->delete('rabatt_codes', sprintf('rabatt_codes.rabattId = %d', $rabattId));
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order=null, $limit=0, $from=0)
    {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                    ->from( array("%ftable%" => "rabatt_codes") );

        if($order != null)
        {
            $query->order($order);
        }

        if($limit != 0)
        {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
    }

        /**
     * get a rows matching Id by given value
     * @param int $id
     */
    public static function findById($id)
    {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                    ->from( array("r" => "rabatt_codes") )
                    ->where( "r.id = " . $id );

        return $db->fetchRow($query);
    }

    /**
     * Get a rows matching code by given value
     * @author vpriem
     * @since 23.02.2011
     * @param varchar $code
     * @return array
     */
    public static function findByCode ($code) {
        // remove special chars
        $code = str_replace(array("'", '"'), "", $code);

        $db = Zend_Registry::get('dbAdapter'); //keep this to the master!!!
        return $db->fetchRow(
            "SELECT *
            FROM `rabatt_codes` r
            WHERE r.code = ?
            LIMIT 1", $code
        );
    }
        /**
     * get a rows matching Used by given value
     * @param int $used
     */
    public static function findByUsed($used)
    {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                    ->from( array("r" => "rabatt_codes") )
                    ->where( "r.used = " . $used );

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching RabattId by given value
     * @param int $rabattId
     */
    public static function findByRabattId($rabattId)
    {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                    ->from( array("r" => "rabatt_codes") )
                    ->where( "r.rabattId = ?" , $rabattId );

        return $db->fetchRow($query);
    }

    /**
     * get the list of all discount codes from discount actions with onlyCustomer flag set to 1
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getOnlyCustomersIds(){
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $selectRabattIds = $db->select()
                ->from(array('r' => 'rabatt'), array('ids' => 'r.id'))
                ->where('r.onlyCustomer = 1');
        $rabattIdsArray = $db->fetchAll($selectRabattIds);
        
        if(empty($rabattIdsArray)){
            return array();
        }

        $selectRabattCodes = $db->select()->from(array('rc' => 'rabatt_codes'),
                array('id' => 'rc.id', 'code' => 'rc.code', 'name' => 'r.name', 'rabatt' => 'r.rabatt', 'kind' => 'r.kind'))
                ->join(array('r' => 'rabatt'), 'r.id = rc.rabattId', array())
                ->where(sprintf('rc.rabattId IN (%s)',implode(',', array_map(function($arr){return $arr['ids'];}, $rabattIdsArray))));

        $fields = $db->fetchAll($selectRabattCodes);
        return $fields;
    }

    /**
     * get the list of all discount codes from discount actions with onlyCompany flag set to 1
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getOnlyCompanyIds(){
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $selectRabattIds = $db->select()
                ->from(array('r' => 'rabatt'), array('ids' => 'r.id' ))
                ->where('r.onlyCompany = 1');
        $rabattIdsArray = $db->fetchAll($selectRabattIds);

        if(is_null($rabattIdsArray)){
            return array();
        }

        $selectRabattCodes = $db->select()->from(array('rc' => 'rabatt_codes'),
                array('id' => 'rc.id', 'code' => 'rc.code', 'name' => 'r.name', 'rabatt' => 'r.rabatt', 'kind' => 'r.kind'))
                ->join(array('r' => 'rabatt'), 'r.id = rc.rabattId', array())
                ->where(sprintf('rc.rabattId IN (%s)',implode(',', array_map(function($arr){return $arr['ids'];}, $rabattIdsArray))));

        $fields = $db->fetchAll($selectRabattCodes);
        return $fields;
    }

    /**
     *
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getParent(){
        if ( is_null($this->getId()) ){
            return null;
        }
        return $this->getCurrent()->findParentRow('Yourdelivery_Model_DbTable_Rabatt');
    }

    /**
     * get all codes
     * @return array
     */
    static public function getAllCodes(){
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select('code')
                    ->from( array("r" => "rabatt_codes") );

        return $db->fetchAll($query);
    }

    /**
     * how often this code was used
     * @author alex
     * @since 28.09.2010
     * @return int
     */
    public function getUsedCountByOrders(){
        $sql = sprintf('select count(o.id) as count from orders o join rabatt_codes rc on o.rabattCodeId=rc.id where o.state>0 and rc.id=%d', $this->getId());
        $result = $this->getAdapter()->fetchRow($sql);

        return $result['count'];
    }


    /**
     * get all orders where this code was used
     * @author alex
     * @since 27.01.2011
     */
    public function getOrdersAsArray() {
        $query = $this->getAdapter()->select()
                    ->from( array("o" => "orders") )
                    ->where( "o.rabattCodeId = " . $this->getId() );

        return $this->getAdapter()->fetchAll($query);
    }

    /**
    * get count of orders where this code was used
    * @author alex
    * @since 15.07.2011
    */
    public function getOrdersCount(){
        $sql = sprintf('select count(id) as count from orders where rabattCodeId=%d', $this->getId());
        $result = $this->getAdapter()->fetchRow($sql);
        return $result['count'];
    }

    /**
     * Return the count of codes in this discount action
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.11.2011
     * @return int
     */
    public static function getCodesCount($rabattId) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $result = $db->fetchRow(sprintf('select count(id) count from rabatt_codes where rabattId=%d', $rabattId));
        return $result['count'];
    }
}
