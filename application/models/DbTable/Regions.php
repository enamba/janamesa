<?php

/**
 * Regions
 * @author Jens Naie <naie@lieferando.de>
 */
class Yourdelivery_Model_DbTable_Regions extends Default_Model_DbTable_Base {

    protected $_name = "regions";

    /**
     * the reference array to map on dependent tables
     * @var array
     */
    protected $_referenceMap = array(
        'District' => array(
            'columns' => 'id',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Districts',
            'refColumns' => 'regionId'
        ),
        'City' => array(
            'columns' => 'id',
            'refTableClass' => 'Yourdelivery_Model_DbTable_City',
            'refColumns' => 'regionId'
        ),
    );

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * Get a row matching direct region link
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     * @param string $uri URI to find region by
     * @return array
     */
    public static function findByDirectLink($uri, $onlyUsed = true, $type = null, $limit = null) {

        if (empty($uri) || $uri == "/") {
            return null;
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');

        if (strcmp($type, 'cater')==0) {
            $query = $db->select()->from(array('r' => 'regions'), array('used'))
                    ->where("r.caterUrl = ?", $uri);
        }
        else if (strcmp($type, 'great')==0) {
            $query = $db->select()->from(array('r' => 'regions'), array('used'))
                    ->where("r.greatUrl = ?", $uri);
        }
        else {
            $query = $db->select()->from(array('r' => 'regions'), array('used'))
                    ->where("r.restUrl = ?", $uri);
        }
        if ($onlyUsed === true) {
            $query->where("r.used = 1");
        } elseif($onlyUsed === false) {
            $query->where("r.used = 0");
        }
        if ($limit) {
            $query->limit($limit);
        }
        $query->join(array('c' => 'city'), 'r.id = c.regionId', array('c.id'));
        $query->distinct();

        $row = $db->fetchAll($query);
        
        if ($row) {
            return $row;
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
        $query = $db->select()->from(array('r' => 'regions'), array('restUrl', 'caterUrl', 'greatUrl'))
                              ->where('r.id IN (?)', $ids);
        $rows = $db->fetchAll($query);
        
        if ($rows) {
            return $rows;
        }

        return null;
    }
}
