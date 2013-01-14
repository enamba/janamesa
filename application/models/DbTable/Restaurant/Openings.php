<?php

/**
 * Yourdelivery_Models_DbTable_RestaurantOpenings.
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Restaurant_Openings extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string
     */
    protected $_name = 'restaurant_openings';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Update a table row by given primary key
     * @param int $id
     * @param array $data
     * @return void
     */
    public static function edit($id, $data) {

        $db = Zend_Registry::get('dbAdapter');
        $db->update('restaurant_openings', $data, 'id = ' . ((integer) $id));
    }

    /**
     * Delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id) {

        $db = Zend_Registry::get('dbAdapter');
        $db->delete('restaurant_openings', 'id = ' . ((integer) $id));
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order = null, $limit = 0, $from = 0) {

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->from(array("%ftable%" => "restaurant_openings"));

        if ($order != null) {
            $select->order($order);
        }

        if ($limit != 0) {
            $select->limit($limit, $from);
        }

        return $db->fetchAll($select);
    }

    /**
     * get a rows matching Id by given value
     * @param int $id
     */
    public static function findById($id) {

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()
                ->from(array("r" => "restaurant_openings"))
                ->where("r.id = ?", $id);

        return $db->fetchRow($select);
    }

    /**
     * get a rows matching RestaurantId by given value
     * @param int $restaurantId
     */
    public static function findByRestaurantId($restaurantId) {

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()
                ->from(array("r" => "restaurant_openings"))
                ->where("r.restaurantId = ?", $restaurantId);

        return $db->fetchRow($select);
    }

    /**
     * get a rows matching Day by given value
     * @param int $day
     */
    public static function findByDay($day) {

        $db = Zend_Registry::get('dbAdapter');
        $select = $db->select()
                ->from(array("r" => "restaurant_openings"))
                ->where("r.day = ?", $day);

        return $db->fetchRow($select);
    }

    /**
     * get all restaurant openings
     * @author Matthias Laug <laug@lieferando.de> Allen Frank <frank@lieferando.de>
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getOpenings($serviceId, $from, $until) {

        $serviceId = (integer) $serviceId;
        if ($serviceId <= 0) {
            return null;
        }

        //create date formats
        $fromDay = date('w', $from);
        $untilDay = date('w', $until);
        $fromDate = date('Y-m-d', $from);
        $untilDate = date('Y-m-d', $until);

        $selectRegularOpenings = null;
        $selectHolidayOpenings = null;
        $selectSpecialOpenings = null;

        $generatedDays = $this->generateDays($fromDay, $untilDay);

        //fill up array
        $selectFillup = $this->getAdapter()->select()
                ->from(array('rod' => 'restaurant_openings_default'))
                ->where('rod.day IN(?)', $generatedDays);
        if ($serviceId == 31){
            $a = 'a';
        }

        //find if today is holiday
        $selectHoliday = $this->getAdapter()->select()
                ->from(array('ro' => 'restaurant_openings_holidays'))
                ->where('ro.date = ?', $fromDate);
        
        $resultHoliday = $this->getAdapter()->fetchAll($selectHoliday);
        
        if (count($resultHoliday) > 0) {
            //if today is holiday then change the day of week to holiday.
            // 10 == holiday
            $generatedDays = array (10);
        }
        
        //get all regular openings
        $selectRegularOpenings = $this->getAdapter()->select()
                ->from(array('ro' => 'restaurant_openings'), array(
                    'day' => 'ro.day',
                    'openings' => new Zend_Db_Expr('GROUP_CONCAT(CONCAT(ro.from,"|", ro.until) order by ro.from ASC)'),
                    'closed' => new Zend_Db_Expr('0')
                ))
                ->where('ro.restaurantId = ?', $serviceId)
                ->where('ro.day IN(?)', $generatedDays)
                ->group('day');

        //get all holiday openings
        $selectHolidayOpenings = $this->getAdapter()->select()
                ->from(array('r' => 'restaurants'), array(
                    'day' => new Zend_Db_Expr('DAYOFWEEK(`date`) - 1'), //mysql counts from 1 - 7, we do from 0 - 6
                    'openings' => new Zend_Db_Expr('GROUP_CONCAT(CONCAT(ro.from,"|", ro.until) order by ro.from ASC)'),
                    'closed' => new Zend_Db_Expr('0')
                ))
                ->join(array('c' => 'city'), 'c.id = r.cityId', array())
                ->join(array('roh' => 'restaurant_openings_holidays'), 'roh.stateId = c.stateId', array())
                ->join(array('ro' => 'restaurant_openings'), 'ro.restaurantId = r.id', array())
                ->where('r.id = ?', $serviceId)
                ->where('roh.date >= ?', $fromDate)
                ->where('roh.date <= ?', $untilDate)
                ->where('ro.day = 10');

        //get all special openings
        $selectSpecialOpenings = $this->getAdapter()->select()
                ->from(array('ros' => 'restaurant_openings_special'), array(
                    'day' => new Zend_Db_Expr('DAYOFWEEK(`specialDate`) - 1'),
                    'openings' => new Zend_Db_Expr('GROUP_CONCAT(CONCAT(ros.from,"|", ros.until) order by ros.from ASC)'),
                    'closed' => 'ros.closed'
                ))
                ->where('ros.restaurantId = ?', $serviceId)
                ->where('specialDate >= ?', $fromDate)
                ->where('specialDate <= ?', $untilDate)
                ->group('day');

        $union = $this->getAdapter()->select()->union(array($selectSpecialOpenings, $selectHolidayOpenings, $selectRegularOpenings, $selectFillup));

             
        $selectAll = $this->getAdapter()->select()->from($union)->where('day is not null')->group('day'); 
                  
        $rawData = $this->getAdapter()->fetchAll($selectAll);
                        
        $output = array();
        foreach ($generatedDays as $value) {
            foreach ($rawData as $data) {
                if ($value == $data['day']) {
                    $output[] = $data;
                }                                                
            }
        }        
        return $output;
    }

    /**
     * @author Daniel, Vincent, Allen
     * @since 08.06.2012
     * @param type $start
     * @param type $end
     * @return array
     */
    public function generateDays($start, $end) {

        $max = 6;
        $begin = 0;
        
        $output = array();
        if ($end < $start) {
            $output = array_merge($output, range($start, $max));
            $output = array_merge($output, range($begin, $end));
        } else {
            $output = range($start, $end);
        }

        return $output;
    }

    /**
     * Get restaurant openings
     * @author Alex Vait
     * @since 25.06.2012
     * @param int $id restaurantId
     * @param int $day week day (optional)
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRegularOpenings ($id = null, $day = null) {
        if ($id === null) {
            return array();
        }
        $select = $this->select()
            ->where("`restaurantId` = ?", $id)
            ->where("`day` IS NOT NULL");
            
        if ($day !== null) {
            $select->where("`day` = ?", $day);
        }
        
        $select->order("round((day)/day+1) desc")
                ->order("day")
                ->order("from");

        return $this->fetchAll($select);        
    }    
    
}
