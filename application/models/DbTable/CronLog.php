<?php

/**
 * Database interface for Yourdelivery_Models_DbTable_CronLog.
 *
 * @copyright   Yourdelivery
 * @author	Matthias Laug
 * 
 * @deprecated
 */
class Yourdelivery_Model_DbTable_CronLog extends Default_Model_DbTable_Base {

    /**
     * name of the table
     * @param string
     */
    protected $_name = 'cron_log';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * edit one row in table
     * 
     * @param integer $id   id of row to edit
     * @param array   $data data to update
     *
     * @return void
     */
    public static function edit($id, $data) {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('cron_log', $data, 'cron_log.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * 
     * @param integer $id id of row to delete
     * 
     * @return void
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since ?????
     */
    public static function remove($id) {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('cron_log', 'cron_log.id = ' . $id);
    }

    /**
     * get rows
     * 
     * @param string  $order order by addition
     * @param integer $limit limit for query
     * @param string  $from  offest for query
     * 
     * @return Zend_DbTable_Rowset
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since ?????
     */
    public static function get($order = null, $limit = 0, $from = 0) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("%ftable%" => "cron_log"));

        if ($order != null) {
            $query->order($order);
        }

        if ($limit != 0) {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
    }

    /**
     * get a row matching Id by given value
     * 
     * @param integer $id id to find cronlog by
     * 
     * @return array
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since ?????
     */
    public static function findById($id) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "cron_log"))
                ->where("c.id = " . $id);

        return $db->fetchRow($query);
    }

    /**
     * get a row matching Time by given value
     * 
     * @param integer $time time to find cronlog by
     * 
     * @return array
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since ?????
     */
    public static function findByTime($time) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "cron_log"))
                ->where("c.time = " . $time);

        return $db->fetchRow($query);
    }

    /**
     * get a row matching Mode by given value
     * 
     * @param string $mode mode to find cronlog by
     * 
     * @return array
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since ?????
     */
    public static function findByMode($mode) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $query = $db->select()
                ->from(array("c" => "cron_log"))
                ->where("c.mode = " . $mode);

        return $db->fetchRow($query);
    }

}
