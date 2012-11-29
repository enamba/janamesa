<?php

/**
 * Restaurant Franchise Table
 * @author Allen Frank <frank@lieferando.de>
 * @since 15-02-2012
 */
class Yourdelivery_Model_DbTable_Restaurant_Franchise extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @param string
     */
    protected $_name = 'restaurant_franchisetype';

    /**
     * Primary key name
     * @param string
     */
    protected $_primary = 'id';

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @modified Matthias Laug add some caching
     * @since xxx
     * @return array 
     */
    public function getAllNames() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array('rf' => $this->_name), array('rf.id', 'rf.name'))
                ->where('rf.id != 1');
        $data = $db->fetchAll($query);
        return $data;
    }

    /**
     * @author Allen Frank <frank@lieferando.de>
     * @modified Matthias Laug add some caching
     * @since xxx
     * @return array 
     */
    public function findById($id) {
        $cacheId = sprintf('franchiseId%s', $id);
        $data = Default_Helpers_Cache::load($cacheId);
        if ($data) {
            return $data;
        }
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()
                ->from(array('rf' => $this->_name), array('rf.id', 'rf.name'))
                ->where($db->quoteInto('rf.id = ?', $id));
        $data = $db->fetchRow($query);
        Default_Helpers_Cache::store($cacheId, $data);
        return $data;
    }

}
