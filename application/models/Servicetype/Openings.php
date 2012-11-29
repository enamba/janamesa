<?php

/**
 * Service opening times
 *
 * @author vait
 */
class Yourdelivery_Model_Servicetype_Openings extends Default_Model_Base {

    /**
     * @var Yourdelivery_Model_Servicetype_Abstract
     */
    protected $_service = null;

    /**
     *
     * @var array 
     */
    protected $_openings = null;

    /**
     * singleton object
     * @var Yourdelivery_Model_Servicetype_Rating_Service
     */
    static private $instances = array();

    /**
     * this object is nearly never in flux, so we use a singleton here
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 15.08.2012
     * 
     * @param Yourdelivery_Model_Servicetype_Abstract $object
     * @return Yourdelivery_Model_Servicetype_Openings
     */
    public static function getInstance(Yourdelivery_Model_Servicetype_Abstract $object) {
        if (array_key_exists($object->getId(), self::$instances) && self::$instances[$object->getId()] instanceof Yourdelivery_Model_Servicetype_Openings) {
            return self::$instances[$object->getId()];
        }
        self::$instances[$object->getId()] = new self($object);
        return self::$instances[$object->getId()];
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 15.08.2012
     * 
     * @param Yourdelivery_Model_Servicetype_Abstract $service
     * @param integer $id
     */
    public function __construct(Yourdelivery_Model_Servicetype_Abstract $service = null, $id = null) {

        if ((integer) $id > 0) {
            parent::__construct($id);
        } else {
            $this->_service = $service;
        }
    }

    /**
     *
     * @return Yourdelivery_Model_DbTable_Restaurant_Openings
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Openings();
        }
        return $this->_table;
    }

    /**
     * Adds a normal opening
     *
     * @param  array $openings
     * 
     * @return integer ID of added opening | 0
     * 
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 29.05.2012
     */
    public function addNormalOpening(array $openings) {
        $id = 0;
        try {
            $opening = new Yourdelivery_Model_Servicetype_Openings($this->_service);
            $openings['restaurantId'] = $this->_service->getId();
            $opening->setData($openings);
            $id = $opening->save();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err(sprintf('addNormalOpening: %s', $e->getMessage()));
        }
        return $id;
    }

    /**
     * Adds a holiday opening
     *
     * @param  array $openings
     * 
     * @return integer ID of added opening | 0
     * 
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 29.05.2012
     */
    public function addHolidayOpening(array $openings) {
        $openings['day'] = 10;
        return $this->addNormalOpening($openings);
    }

    /**
     * Adds a special opening
     *
     * @param array $openings
     * 
     * @return integer ID of added opening | 0
     * 
     * @author Andre Ponert <ponert@lieferando.de>
     */
    public function addSpecialOpening(array $openings) {
        $id = 0;
        try {
            $specialOpenings = new Yourdelivery_Model_Servicetype_OpeningsSpecial();
            $openings['restaurantId'] = $this->_service->getId();
            $specialOpenings->setData($openings);
            $id = $specialOpenings->save();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->err(sprintf('addSpecialOpening: %s', $e->getMessage()));
        }
        return $id;
    }

    /**
     * check if the current service is open at the current time
     * or given $time
     *
     * @param integer $time
     * @return boolean
     */
    public function isOpen($time = null) {

        if ($time === null) {
            $time = time();
        }

        $openings = $this->_getOpeningsFromDatabase($time, $time);

        foreach ($openings as $timestamp => $opening) {
            foreach ($opening as $intervalOfDay) {
                $from = $intervalOfDay['timestamp_from'];
                $until = $intervalOfDay['timestamp_until'];
                if ($time >= $from && $time <= $until && !$intervalOfDay['closed']) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * @param integer $time
     * @param array(array($from, $until)) $time
     */
    public function getIntervalOfDay($time = null) {

        if ($time === null) {
            $time = time();
        }

        $until = strtotime('+1 day', $time) - 1;

        return $this->_getOpeningsFromDatabase($time, $until);
    }

    /**
     *
     * @param integer $from
     * @param integer $until
     * @return array( n...m array(array($from, $until))) for n>=0 and m<=6 or 10 (free day)
     */
    public function getIntervals($from, $until) {

        if ($from != $until) {
            $until = strtotime('+1 day', $until);
        }

        return $this->_getOpeningsFromDatabase($from, $until);
    }

    /**
     * get raw data from database and get only those openings in that give
     * $from - $until interval. If we want to be strict, the timestamp will
     * be compared to the data to remove those openings intervals which are
     * already run through at the give $from day
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @param integer $from
     * @param integer $unil
     * @param boolean $strict
     * @return array
     */
    protected function _getOpeningsFromDatabase($from, $until, $strict = false) {

        $slice = true;
        if ((($until - $from) / 60 / 60 / 24) > 6) {
            $until = strtotime('+6 day', $from);
            $slice = false;
        }

        $start = $from;

        if ($this->_openings === null) {
            $this->_openings = array(array());
        }

        if ($this->_openings[$from][$until] == null) {
            $openingsRaw = $this->_openings[$from][$until] = $this->getTable()->getOpenings($this->_service->getId(), $from, $until);
        } else {
            $openingsRaw = $this->_openings[$from][$until];
        }
        
        $openings = array();
        foreach ($openingsRaw as $opening) {
            if ($opening['closed'] == 1) {
                $openings[$start][] = array(
                    'timestamp_from' => strtotime('00:00:00'),
                    'timestamp_until' => strtotime('24:00:00'),
                    'day' => (integer) $opening['day'],
                    'closed' => 1
                );
                $start = strtotime('+1 day', $start);
                continue;
            } else {
                $split_openings = explode(',', $opening['openings']);
                foreach ($split_openings as $opening_of_day) {
                    $fromUntilArr = explode('|', $opening_of_day);
                    if (isset($fromUntilArr[0]) && isset($fromUntilArr[1]) && $fromUntilArr[0]!=$fromUntilArr[1]) {
                        $currentFrom = strtotime($fromUntilArr[0], $start);
                        $currentUntil = strtotime($fromUntilArr[1], $start);

                        if ($strict && ((strlen($opening_of_day) > 0 && $currentFrom < $from) || $opening['closed'] )) {
                            $start = strtotime('+1 day', $start);
                            continue;
                        }

                        $openings[$start][] = array(
                            'timestamp_from' => $currentFrom,
                            'timestamp_until' => $currentUntil,
                            'day' => (integer) $opening['day'],
                            'from' => substr($fromUntilArr[0], 0, 5),
                            'until' => substr($fromUntilArr[1], 0, 5),
                            'closed' => (boolean) $opening['closed'],
                        );
                    }
                }
            }

            $dayBefore = strtotime('-1 day', $start);
            if (isset($openings[$dayBefore])) {
                $openings[$dayBefore]['next'] = $openings[$start];
            }

            $start = strtotime('+1 day', $start);
        }

        return $slice && count($openings) > 1 ? array_slice($openings, 0, -1, true) : $openings;
    }

    /**
     * clear all dynamic cached instances of model
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.08.2012 
     */
    public function clearCache() {
        self::$instances = array();
    }

}
