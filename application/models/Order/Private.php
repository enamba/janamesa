<?php

/**
 * Description of Private
 * @package order
 * @author mlaug
 */
class Yourdelivery_Model_Order_Private extends Yourdelivery_Model_Order_Abstract {

    /**
     * @author mlaug
     * @since 12.11.2010
     * @return Yourdelivery_Model_Order_Pdf_Private_Single_Fax 
     */
    public function getFaxClass() {
        return new Yourdelivery_Model_Order_Pdf_Private_Single_Fax();
    }

    /**
     * @author mlaug
     * @since 12.11.2010
     * @return Yourdelivery_Model_Order_Pdf_Private_Single_FaxCourier 
     */
    public function getCourierFaxClass() {
        return new Yourdelivery_Model_Order_Pdf_Private_Single_FaxCourier();
    }
    
    /**
     *
     * @author mlaug
     * @return Yourdelivery_Model_OrderAbstract
     */
    public function setup(Yourdelivery_Model_Customer_Abstract $customer, $mode = 'rest') {

        //set current customer as default ordering customer, mh who else :)
        $this->setCustomer($customer);
        
        //generate secret key
        $this->_secret = Default_Helper::generateRandomString(20);

        //generate a custom number / must be unique
        $this->generateOrderNumber();

        //set standard modes, ( this will be used for identification )
        $this->setTime(time());
        $this->setDeliverTime(time());
        $this->setMode($mode);
        $this->setKind('priv');
        $this->setIdent($this->_secret);      

        //create a new order
        return $this;
    }

    /**
     * get already payed amount
     * @author mlaug
     * @return int
     */
    public function getPayedAmount() {
        return $this->getAbsTotal(true, false, true, false) - $this->getAbsTotal();
    }

}
