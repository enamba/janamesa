<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 10.08.2012
 */
class Default_Controller_RequestPartnerBase extends Default_Controller_RequestBase {

    /**
     * @var Yourdelivery_Model_Servicetype_Restaurant
     */
    protected $_restaurant = null;
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 10.08.2012
     */
    public function preDispatch() {

        // check if user logged in
        $restaurantId = $this->session->partnerRestaurantId;
        if ($restaurantId === null) {
            die();
        }
        try {
            $this->_restaurant = new Yourdelivery_Model_Servicetype_Restaurant($restaurantId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            die();
        }
        
        parent::preDispatch();
    }
}
