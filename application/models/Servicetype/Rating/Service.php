<?php

require_once(APPLICATION_PATH . '/../library/FirePHPCore/FirePHP.class.php');

/**
 * Description of Customer
 *
 * @author mlaug
 */
class Yourdelivery_Model_Servicetype_Rating_Service extends Default_Model_Base {

    /**
     *
     * @var array
     */
    protected $_data = array();

    /**
     *
     * @var Yourdelivery_Model_Servicetype_Abstract
     */
    protected $_object = null;

    /**
     *
     * @var array
     */
    protected $_list = null;

    /**
     *
     * @var array
     */
    protected $_topList = null;

    /**
     * @var array
     */
    protected $_starCount = null;

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
     * @return Yourdelivery_Model_Servicetype_Rating_Service
     */
    public static function getInstance(Yourdelivery_Model_Servicetype_Abstract $object) {
        if (array_key_exists($object->getId(), self::$instances) && self::$instances[$object->getId()] instanceof Yourdelivery_Model_Servicetype_Rating_Service) {
            return self::$instances[$object->getId()];
        }
        self::$instances[$object->getId()] = new self($object);
        return self::$instances[$object->getId()];
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.08.2012
     * 
     * @param Yourdelivery_Model_Servicetype_Abstract $object
     */
    public function __construct(Yourdelivery_Model_Servicetype_Abstract $object) {
        $this->_object = $object;
    }

    /**
     * get list of rating services
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 09.05.2012
     * 
     * @param integer $slice
     * @param boolean $onlyConfirmed
     * @param boolean $withComments
     * @param boolean $lastThirtyDays
     * @param integer $limit
     * @return array
     */
    public function getList($slice = null, $onlyConfirmed = false, $withComments = false, $lastThirtyDays = false, $limit = null) {

        //if object is not set, return empty array
        if (!$this->_object instanceof Yourdelivery_Model_Servicetype_Abstract) {
            return array();
        }

        //init dynamic cache
        if ($this->_list === null) {
            $this->_list = array(array(array(array())));
        }

        //use reference for better code reading experience
        $dynamicArray = &$this->_list[(integer) $onlyConfirmed][(integer) $withComments][(integer) $lastThirtyDays][$limit];

        //check if this reference already inherits an array
        if (is_array($dynamicArray)) {
            return $dynamicArray;
        }

        //generaete the cache tag and try to find result in memcache
        $cacheTag = sprintf('serviceRatingList%d%d%d%d%d', $this->_object->getId(), (integer) $onlyConfirmed, (integer) $withComments, (integer) $lastThirtyDays, $limit);
        $dynamicArray = Default_Helpers_Cache::load($cacheTag);

        //if memcache result itself is empty as well, we need to go to database
        if ($dynamicArray === null) {
            $dynamicArray = $this->getTable()->getListFromService($this->_object->getId(), $onlyConfirmed, $withComments, $lastThirtyDays, $limit);
            Default_Helpers_Cache::store($cacheTag, $dynamicArray);
        }

        if ($slice > 0) {
            return array_slice($dynamicArray, 0, $slice);
        }

        return $dynamicArray;
    }

    /**
     * hide the real methods with an underscore to provide dynamic 
     * caching for all of them
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.08.2012
     * 
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args) {
        $method = '_' . $method;
        if (method_exists($this, $method)) { //if this method exsits 
            $methodHash = $method . md5(serialize($args)); //we create a hash of method name and parameters
            if (array_key_exists($methodHash, $this->_data)) { //check for existance
                return $this->_data[$methodHash]; //avoid duplicate calls
            }
            $this->_data[$methodHash] = call_user_func_array(array($this, $method), $args);
            return $this->_data[$methodHash];
        }
        return null;
    }

    /**
     * 
     * @param integer $slice
     * @param boolean $onlyConfirmed
     * @param boolean $withComments
     * @param boolean $lastThirtyDays
     * @param integer $limit
     * @return integer
     */
    private function _count($slice = null, $onlyConfirmed = false, $withComments = false, $lastThirtyDays = false, $limit = null) {
        return count($this->getList($slice, $onlyConfirmed, $withComments, $lastThirtyDays, $limit));
    }

    /**
     * are there any ratings
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 09.05.2012
     * @return boolean
     */
    private function _hasRating() {
        return (boolean) $this->count(null, true, false, true) > 0;
    }

    /** Get percentage of $stars rating
     * 
     * @param int $stars
     * @param string $type
     * 
     * @return float
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    private function _getPercentRating($stars, $type = "delivery") {

        $count = count($this->getList(null, true, false, true));
        
        if (!$count) {
            return 0;
        }
            
        if (!is_null($this->_starCount[$type][$stars])) {
            return round(($this->_starCount[$type][$stars] / $count) * 100);
        }

        $hash = sprintf('percentRating%d', $this->_object->getId());
        $this->_starCount = Default_Helpers_Cache::load($hash);

        if ($this->_starCount === null) {

            $this->_starCount = array(
                'delivery' => array(
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 0
                ),
                'quality' => array(
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 0
                )
            );

            $ratings = $this->getList(null, true, false, true);
            foreach ($ratings as $rating) {
                $this->_starCount['delivery'][$rating['delivery']]++;
                $this->_starCount['quality'][$rating['quality']]++;
            }
            Default_Helpers_Cache::store($hash, $this->_starCount);
        }

        return round(($this->_starCount[$type][$stars] / $count) * 100);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 09.05.2012
     * @return decimal
     */
    private function _getAverage($precision = null, $seperator = null) {
        $average = ($this->getAverageDelivery() + $this->getAverageQuality()) / 2;

        if (!is_null($precision)) {
            $average = round($average, $precision);
        }

        if (!is_null($seperator)) {
            $average = str_replace(',', $seperator, $average);
        }
        return $average;
    }

    /**
     * get a percantage of advises
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.05.2012
     * @return decimal
     */
    private function _getAverageAdvise() {
        $count = $this->count(null, true, false, true);
        $count = $count > 0 ? $count : 1;
        return array_reduce($this->getList(null, true, false, true), function($current, $rating) {
                            return $current + (integer) $rating['advise'];
                        }, 0) / $count * 100;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.05.2012
     * @return decimal
     */
    private function _getAverageDelivery() {
        $count = $this->count(null, true, false, true);
        $count = $count > 0 ? $count : 1;
        return array_reduce($this->getList(null, true, false, true), function($current, $rating) {
                            return $current + (integer) $rating['delivery'];
                        }, 0) / $count;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.05.2012
     * @return decimal
     */
    private function _getAverageQuality() {
        $count = $this->count(null, true, false, true);
        $count = $count > 0 ? $count : 1;
        return array_reduce($this->getList(null, true, false, true), function($current, $rating) {
                            return $current + (integer) $rating['quality'];
                        }, 0) / $count;
    }

    /**
     * clear cache for rating abstract model
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 10.07.2012
     */
    public function clearCache() {
        $this->logger->debug('clearing cache for rating service model');

        $this->_list = null;
        $this->_starCount = null;
        $this->_data = array();

        $types = array('delivery', 'quality');
        $stars = array(1, 2, 3, 4, 5);

        foreach ($types as $type) {
            foreach ($stars as $star) {
                $hash = sprintf('percentRating%d%d%s', $this->_object->getId(), $star, $type);
                Default_Helpers_Cache::remove($hash);
            }
        }
        $confirmedStates = array(0, 1);
        $withCommentStates = array(0, 1);
        $lastThirtyDaysStates = array(0, 1);
        $limits = array(0, 5, 10, 15, 20, 50, 100);
        foreach ($confirmedStates as $confirmedState) {
            foreach ($withCommentStates as $withCommentState) {
                foreach ($lastThirtyDaysStates as $lastThirtyDaysState) {
                    foreach ($limits as $limit) {
                        $hash = sprintf('serviceRatingList%d%d%d%d%d', $this->_object->getId(), $confirmedState, $withCommentState, $lastThirtyDaysState, $limit);
                        Default_Helpers_Cache::remove($hash);
                    }
                }
            }
        }
    }

    /**
     * Get associated table of model
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.05.2012
     * @return Yourdelivery_Model_DbTable_Restaurant_Ratings
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Ratings();
        }
        return $this->_table;
    }

}
