<?php

/**
 * Regions
 * @author Jens Naie <naie@lieferando.de>
 */
class Yourdelivery_Model_DbTable_Districts extends Default_Model_DbTable_Base {

    protected $_name = "districts";

    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Regions',
    );
    
    /**
     * the reference array to map on dependent tables
     * @var array
     */
    protected $_referenceMap = array(
        'City' => array(
            'columns' => 'id',
            'refTableClass' => 'Yourdelivery_Model_DbTable_City',
            'refColumns' => 'districtId'
        ),
    );

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';


    /**
     * Get a row matching direct district link
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     * @param string $uri URI to find district by
     * @return array
     */
    public static function findByDirectLink($uri, $onlyUsed = true, $type = null, $limit = null) {

        if (empty($uri) || $uri == "/") {
            return null;
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');

        if (strcmp($type, 'cater')==0) {
            $query = $db->select()->from(array('d' => 'districts'), array('used'))
                    ->where("d.caterUrl = ?", $uri);
        }
        else if (strcmp($type, 'great')==0) {
            $query = $db->select()->from(array('d' => 'districts'), array('used'))
                    ->where("d.greatUrl = ?", $uri);
        }
        else {
            $query = $db->select()->from(array('d' => 'districts'), array('used'))
                    ->where("d.restUrl = ?", $uri);
        }
        if ($onlyUsed) {
            $query->where("d.used = 1");
        } elseif($onlyUsed === false) {
            $query->where("d.used = 0");
        }
        if ($limit) {
            $query->limit($limit);
        }
        $query->join(array('c' => 'city'), 'd.id = c.districtId', array('c.id'));
        $query->distinct();

        $rows = $db->fetchAll($query);
        
        if ($rows) {
            return $rows;
        }

        return null;
    }

    /**
     * Get restUrl, caterUrl, greatUrl for given array of ids
     * @author Jens Naie <naie@lieferando.de>
     * @since 03.08.2012
     * @param array $ids
     * @return array
     */
    public static function getUrlsForIds(array $ids) {
        if (empty($ids)) {
            return null;
        }
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $query = $db->select()->from(array('d' => 'districts'), array('restUrl', 'caterUrl', 'greatUrl'))
                              ->where('d.id IN (?)', $ids);
        $rows = $db->fetchAll($query);
        
        if ($rows) {
            return $rows;
        }

        return null;
    }
}
