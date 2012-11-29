<?php

/**
 * Description of Rating
 * @author mlaug
 */
class Yourdelivery_Model_Servicetype_Rating extends Default_Model_Base {

    /**
     * Get associated table of model
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Restaurant_Ratings
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Restaurant_Ratings();
        }
        return $this->_table;
    }

    /**
     * Get author
     * @author vpriem
     * @since 18.10.2010
     * @return string
     */
    public function getAuthor() {

        $author = $this->_data['author'];
        if ($author === null || $author == "") {
            return __("Unbekannt");
        }
        return $author;
    }

    /**
     * get picture of author
     *
     * @return string URL of profile image of author (or default image)
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.03.2012
     */
    public function getAuthorImage() {
        
        // try to generate customer
        try {
            $customer = $this->getCustomer();
            if(is_object($customer) && $customer instanceof Yourdelivery_Model_Customer && $customer->hasProfileImage()){
                return $customer->getProfileImage();
            }
        } catch (Exception $e) {
            
        }
        return Yourdelivery_Model_Customer_Abstract::DEFAULT_IMG;
    }

    /**
     * Get total rating for google rich snippets
     * @author vpriem
     * @since 18.10.2010
     * @return float
     */
    public function getRating() {

        $r = ($this->_data['quality'] + $this->_data['delivery']) / 2;
        return str_replace(",", ".", $r);
    }

    /**
     * Get quality rating with leading 0
     * @author mlaug
     * @return string
     */
    public function getQualityZeroLeading() {

        $q = $this->_data['quality'];
        if ($q == 5) {
            return "10";
        }
        if ($q < 5) {
            return "0" . ($q * 2);
        }
    }

    /**
     * Get delivery rating with leading zero
     * @author mlaug
     * @return string
     */
    public function getDeliveryZeroLeading() {

        $d = $this->_data['delivery'];
        if ($d == 5) {
            return "10";
        }
        if ($d < 5) {
            return "0" . ($d * 2);
        }
    }

    /**
     * activate the rating and take care of fidelity points
     * @author Matthias Laug <laug@lieferando.de>
     * @since 19.01.2012
     */
    public function activate() {
        $this->setStatus(1);
        $this->save();

        if (!is_null($this->getOrder())) {
            if (is_object($this->getOrder()->getCustomer())) {
                $fidelity = $this->getOrder()->getCustomer()->getFidelity();
                $transaction = $fidelity->getTransactionByTransactionDataAction($this->getOrderId(), 'rate_');
                if ($transaction['id'] > 0) {
                    $fidelity->modifyTransaction($transaction['id'], 0);
                }
                $this->logger->info(sprintf("set fidelity transaction #%s to state 1 while setting activating rating", $transaction['id']));
            }
        }
    }

    /**
     * deactivate the rating and take care of fidelity points
     * @author Matthias Laug <laug@lieferando.de>
     * @since 19.01.2012
     */
    public function deactivate() {
        $this->setStatus(0);
        $this->save();

        if (!is_null($this->getOrder())) {
            if (is_object($this->getOrder()->getCustomer())) {
                $fidelity = $this->getOrder()->getCustomer()->getFidelity();
                $transaction = $fidelity->getTransactionByTransactionDataAction($this->getOrderId(), 'rate_');
                if ($transaction['id'] > 0) {
                    $fidelity->modifyTransaction($transaction['id'], -1);
                }
                $this->logger->info(sprintf("set fidelity transaction #%s to state -1 while setting deactivating rating", $transaction['id']));
            }
        }
    }

    /**
     * delete the rating and deactivate the corresponding fidelity points
     * @author Alex Vait <vait@lieferando.de>
     * @since 10.02.2012
     */
    public function delete() {
        $this->setStatus(-1);
        $this->save();

        if (!is_null($this->getOrder())) {
            if (is_object($this->getOrder()->getCustomer())) {
                $fidelity = $this->getOrder()->getCustomer()->getFidelity();
                $transaction = $fidelity->getTransactionByTransactionDataAction($this->getOrderId(), 'rate_');
                if ($transaction['id'] > 0) {
                    $fidelity->modifyTransaction($transaction['id'], -1);
                }
                $this->logger->info(sprintf("set fidelity transaction #%s to state -1 while deleting rating", $transaction['id']));
            }
        }
    }

    /**
     * get all ratings in certain time slot, with specified advise state
     * @author Alex Vait <vait@lieferando.de>
     * @since 09.01.2012
     */
    public static function getRatingsInTimeslot($from, $until, $advise) {
        return Yourdelivery_Model_DbTable_Restaurant_Ratings::getRatingsInTimeslot($from, $until, $advise);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.04.2012
     * @param int $adminId
     * @return mixed boolean|int
     */
    public function logCrm($adminId, $callName) {
        
        $crm = new Yourdelivery_Model_Servicetype_Rating_Crm();
        $crm->setRatingId($this->getId());
        $crm->setAdminId((integer) $adminId);
        $crm->setCallName($callName);
        return $crm->save();
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 17.04.2012
     * @return array 
     */
    public function getCrmLogs() {
        
        $rows = $this->getTable()
            ->getCurrent()
            ->findDependentRowset("Yourdelivery_Model_DbTable_Restaurant_Ratings_Crm");
        
        $arr = array();
        foreach ($rows as $row) {
            try {
                $arr[] = new Yourdelivery_Model_Servicetype_Rating_Crm($row->id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            }
        }
        
        return $arr;
    }

}
