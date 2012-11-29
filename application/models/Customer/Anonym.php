<?php

/**
 * This is an anonymous user
 * @author mlaug
 * @package customer
 */
class Yourdelivery_Model_Customer_Anonym extends Yourdelivery_Model_Customer_Abstract {

    /**
     * Get usr shorted name
     * @author vpriem
     * @since 18.10.2010
     * @return string
     */
    public function getShortedName() {

        $prename = $this->getPrename();
        $name = $this->getName();
        if (strlen($name) > 3) {
            $name = substr($name, 0, 3) . ".";
        }
        return trim($prename . " " . $name);
    }

    /**
     * no table is associated with this object
     * @author mlaug
     * @return null
     */
    public function getTable() {
        return null;
    }

    /**
     * generates a id based on current time
     * this getter has to be overwritten, so that getId can be used
     * to store informations e.g. card without being logged in
     * @author mlaug
     * @return int
     */
    public function getId() {
        if (is_null($this->_id)) {
            $this->_id = time();
        }
        return $this->_id;
    }

    /**
     * always returns false to ensure no one
     * thinks this anonymous user actually is a ordinary user
     * we also remove the YD_UID cookie, so no loop in redirection occures
     * this may happend if the session is not closed correctly. we do this here!
     * @author mlaug
     * @return boolean
     */
    public function isLoggedIn() {
        Default_Helpers_Web::deleteCookie('yd-customer');
        return false;
    }

    /**
     * no persistent messages are possible for anonymous user
     * @return void
     */
    public function setPersistentNotfication() {
        return null;
    }

    /**
     * create a message
     * @author mlaug
     */
    public function onlyLoggedInUsers() {
        $this->error(__('Diese Funktion steht nur angemeldeten Benutzern zur Verfügung.'));
    }

    /**
     * in model Anonym always return false
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.08.2010
     * @return boolean
     */
    public function isRegistered() {
        return false;
    }

    /**
     * @author vpriem
     * @since 03.11.2011
     * @return array
     */
    public function getCreditcards() {

        return array();
    }
    
    /**
     * 
     * Function to get Orders from anonymous customer by email
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 07.05.2012
     * @return array
     */
    public function getOrders() {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()->from(array('o' => 'orders'),array('id'))
                                             ->joinLeft(array('oc' => 'orders_customer'), "o.id = oc.orderId",array())
                                            ->where('oc.email=?',$this->getEmail());
        
        return $db->fetchAll($select);
        
    }
    
    
    /**
     * get unrated orders 
     * 
     * @param string  $email email of not registered user
     * @param integer $limit specify limit of result
     * @param integer $start specify offset
     * 
     * @return array
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>    
     * @since 27.01.2012
     */
    public static function getUnratedOrders($email, $limit = false, $start = 0) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()->from(array('o' => 'orders'), array('order_id' => 'o.id', 'o.total', 'o.serviceDeliverCost', 'o.time', 'r.name', 'rr.quality', 'rr.delivery', 'ol.street', 'ol.hausnr', 'o.delivertime', 'restaurantId' => 'r.id', 'created' => 'rr.id'))
                ->joinLeft(array('rr' => 'restaurant_ratings'), 'rr.orderId = o.id', array())
                ->joinLeft(array('r' => 'restaurants'), "r.id = o.restaurantId", array())
                ->joinLeft(array('ol' => 'orders_location'), "ol.orderId = o.id", array())
                ->joinLeft(array('oc' => 'orders_customer'), "oc.orderId = o.id", array())
                ->where('o.state > 0');
        $select->where('rr.delivery IS NULL');
        $select->where('rr.quality IS  NULL');
        $select->where('o.deliverTime < SUBTIME(NOW(), "1:00")');
        $select->where("oc.email = ?", $email);

        if ($limit) {
            $select->limit($limit, $start);
        }
        $select->order('created DESC');
        return $db->fetchAll($select);
    }
    
    
     /**
     * get rated orders 
     * 
     * @param string  $email email of not registered user
     * @param integer $limit specify limit of result
     * @param integer $start specify offset
     * 
     * @return array
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>    
     * @since 30.01.2012
     */
    public static function getRatedOrders($email, $limit = false, $start = 0){
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $select = $db->select()->from(array('o' => 'orders'), array('order_id' => 'o.id', 'o.total', 'o.serviceDeliverCost', 'o.time', 'r.name', 'rr.quality', 'rr.delivery', 'ol.street', 'ol.hausnr', 'o.delivertime', 'restaurantId' => 'r.id', 'created' => 'rr.id'))
                ->joinLeft(array('rr' => 'restaurant_ratings'), 'rr.orderId = o.id', array())
                ->joinLeft(array('r' => 'restaurants'), "r.id = o.restaurantId", array())
                ->joinLeft(array('ol' => 'orders_location'), "ol.orderId = o.id", array())
                ->joinLeft(array('oc' => 'orders_customer'), "oc.orderId = o.id", array())
                ->where('o.state > 0')
                ->where('rr.delivery IS NOT NULL')
                ->where('rr.quality IS NOT NULL')
                ->where("oc.email = ?", $email);
        if ($limit) {
            $select->limit($limit, $start);
        }
        $select->order('created DESC');
        return $db->fetchAll($select);
    }

}
