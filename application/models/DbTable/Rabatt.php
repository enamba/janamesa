<?php
/**
 * Database interface for Yourdelivery_Models_DbTable_Rabatt.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
*/

class Yourdelivery_Model_DbTable_Rabatt extends Default_Model_DbTable_Base
{

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'rabatt';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_dependentTables = array(
                                        'Yourdelivery_Model_DbTable_RabattCodes',
                                        'Yourdelivery_Model_DbTable_Rabatt_Restaurant',
                                        'Yourdelivery_Model_DbTable_Rabatt_City'
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
        $db->update('rabatt', $data, 'rabatt.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('rabatt', 'rabatt.id = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order=null, $limit=0, $from=0)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("%ftable%" => "rabatt") );

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
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "rabatt") )
                    ->where( "r.id = " . $id );

        return $db->fetchRow($query);
    }

    /**
     * Gets a single rabatt by hash
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @param string $hash
     * @return array
     */
    public static function findByHash($hash)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "rabatt") )
                    ->where( "r.hash = ?", $hash);

        return $db->fetchRow($query);
    }

        /**
     * get a rows matching Name by given value
     * @param varchar $name
     */
    public static function findByName($name)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "rabatt") )
                    ->where( "r.name = " . $name );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching Rrepeat by given value
     * @param tinyint $rrepeat
     */
    public static function findByRrepeat($rrepeat)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "rabatt") )
                    ->where( "r.rrepeat = " . $rrepeat );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching Number by given value
     * @param int $number
     */
    public static function findByNumber($number)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "rabatt") )
                    ->where( "r.number = " . $number );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching StartTime by given value
     * @param int $startTime
     */
    public static function findByStartTime($startTime)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "rabatt") )
                    ->where( "r.startTime = " . $startTime );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching EndTime by given value
     * @param int $endTime
     */
    public static function findByEndTime($endTime)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "rabatt") )
                    ->where( "r.endTime = " . $endTime );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching Kind by given value
     * @param tinyint $kind
     */
    public static function findByKind($kind)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "rabatt") )
                    ->where( "r.kind = " . $kind );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching Rabatt by given value
     * @param int $rabatt
     */
    public static function findByRabatt($rabatt)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "rabatt") )
                    ->where( "r.rabatt = " . $rabatt );

        return $db->fetchRow($query);
    }
        /**
     * get a rows matching Status by given value
     * @param tinyint $status
     */
    public static function findByStatus($status)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("r" => "rabatt") )
                    ->where( "r.status = " . $status );

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Referer by given value
     * @param tinyint $referer
     */
    public static function findByReferer($referer)
    {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                    ->from( array("r" => "rabatt") )
                    ->where( "r.referer = ?" ,$referer );

        return $db->fetchRow($query);
    }

    /**
     * returns the amount of used discount codes
     * @return integer
     */
    public function getUsed() {
        $rcTable = new Yourdelivery_Model_DbTable_RabattCodes();
        return count($rcTable->fetchAll('rabattId = ' . $this->getId() . ' AND used > 0'));
    }

    /**
     * get all the codes of this discount campaign
     * @return rowset
     */
    public function getCodes($regcodes) {
        if ($regcodes) {
            $rcTable = new Yourdelivery_Model_DbTable_RabattCodesVerification();
        }
        else {
            $rcTable = new Yourdelivery_Model_DbTable_RabattCodes();
        }
        return $rcTable->fetchAll('rabattId = ' . $this->getId());
    }

    /**
     * get the list of all distinct fields
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getDistinctNameId($sortby = 'id'){
        $sql = sprintf('select distinct(id) from rabatt order by ' . $sortby);
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * get the list of all distinct fields
     * @return Zend_Db_Table_Row_Abstract
     */
    public static function getDistinctReferer(){

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                     ->distinct()
                     ->from('rabatt', array('referer'))
                     ->where('type > 0')
                     ->where('type < 4')
                     ->where('referer IS NOT NULL');
        $fields = $db->fetchAll($select);
        return $fields;
    }

}
