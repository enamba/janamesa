<?php

/**
 * Description of Tracked
 *
 * @package customer
 * @author mlaug
 */
class Yourdelivery_Model_Customer_Tracked extends Default_Model_Base{

    public function setCustomer($customer){
        if ( is_object($customer) && !is_object($this->_data['customer']) ){
            $this->_data['email'] = $customer->getEmail();
            $this->_data['customer'] = $customer;
            $this->save();
        }
    }

    public function setEmail($email){
        if ( !is_null($email) && !is_null($this->_data['email']) ){
            $this->setEmail($email);
            $this->save();
        }
    }

    public function setOrder($order){
        if ( is_object($order) && !is_object($this->_data['order'])){
            $this->_data['order'] = $order;
            $this->save();
        }
    }

    public function setTrackingCode($code){
        if ( is_object($code) && !is_object($this->_data['trackingCode']) ){
            $this->_data['trackingCode'] = $code;
            $this->save();
        }
    }

    //put your code here
    public function getTable() {
        if ( is_null($this->_table) ){
            $this->_table = new Yourdelivery_Model_DbTable_Customer_Tracked();
        }
        return $this->_table;
    }
}
?>
