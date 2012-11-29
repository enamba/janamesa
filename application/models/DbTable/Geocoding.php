<?php
/**
 * Geocoding Db Table
 * @author vpriem
 * @since 11.08.2010
 */
class Yourdelivery_Model_DbTable_Geocoding extends Zend_Db_Table_Abstract{

    protected $_defaultSource = self::DEFAULT_DB;
    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Geocoding';

    /**
     * Table name
     */
    protected $_name = 'geocoding';

    /**
     * Primary key name
     */
    protected $_primary = 'id';

     /**
     * Find row
     * @author vpriem
     * @since 11.08.2010
     * @param string $hash
     * @return Zend_Db_Table_Row_Abstract | boolean
     */
    public function findByHash ($hash) {

        $row = $this->fetchRow(
            $this->select()
                 ->where("`hash` = ?", $hash)
        );
        if ($row === null) {
            return false;
        }
        return $row;
        
    }

}

/**
 * Geocoding Db Table Row
 * @author vpriem
 * @since 11.08.2010
 */
class Yourdelivery_Model_DbTableRow_Geocoding extends Zend_Db_Table_Row_Abstract{

    /**
     * Lifetime: 1 Month
     * @var int
     */
    private $_lifetime = 2592000;

    /**
     * Has expired
     * @author vpriem
     * @since 11.08.2010
     * @return boolean
     */
    public function hasExpired(){

        if ($this->id === null) {
            return true;
        }
        if ((strtotime($this->updated) + $this->_lifetime) < time()) {
            return true;
        }
        return false;

    }

}