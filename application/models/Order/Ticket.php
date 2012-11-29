<?php

/**
 * Description of Ticket
 *
 * @author mlaug
 */
class Yourdelivery_Model_Order_Ticket extends Yourdelivery_Model_Order {

    protected $_prio = null;
    
    
    protected $_timediff = 0;
    
   

    protected $_notifyPaid = 0;

    /**
     * @author mlaug
     * @since 21.01.2011
     * @return integer
     */
    public function getPrio() {
        if ($this->_prio === null) {
            $this->_prio = 1;
        }
        return (integer) $this->_prio;
    }
        

        /**
     * @author mlaug
     * @since 24.01.2011
     * @param integer $prio 
     */
    public function setPrio($prio) {

        $prio = round($prio / 10);

        //upper threshold is 10 (for those who are really old
        if ($prio > 10) {
            $prio = 10;
        }

        $this->_prio = (integer) $prio;
    }
    
    /**
     *
     * @return int
     */
    public function getTimediff() {
        return $this->_timediff;
    }
    
    /**
     *
     * @param int $_timediff 
     */
    public function setTimediff($_timediff) {
        $this->_timediff = $_timediff;
    }
    
    
    /**
     *
     * @return int
     */
    public function getNotifyPaid() {
        return $this->_notifyPaid;
    }

    /**
     *
     * @param int $notifyPaid 
     */
    public function setNotifyPaid($notifyPaid) {
        $this->_notifyPaid = $notifyPaid;
    }
    
    /**
     * lock this order by any supporter
     * @author mlaug
     * @since 21.01.2011
     * @param integer $support 
     * @return boolean
     */
    public function lock($support) {
        $row = $this->getRow();

        if ($row->supporter > 0 && $row->supporter != $support) {
            return false;
        }

        $row->supporter = (integer) $support;
        $row->pulledOn = date('Y-m-d H:i:s', time());
        $row->save();
        return true;
    }

    /**
     * release the lock from this order from any support
     * @author mlaug
     * @since 21.01.2011
     */
    public function release() {
        $row = $this->getRow();
        $row->supporter = null;
        $row->save();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 05.10.2011
     * @return boolean
     */
    public function isPaid() {

        $payment = $this->getPayment();
        switch ($payment) {

            case "paypal";
                $paypal = new Yourdelivery_Model_DbTable_Paypal_Transactions();

                $transactions = $paypal->getByOrder($this->getId());
                if (count($transactions) < 2) {
                    return false;
                }

                foreach ($transactions as $transaction) {
                    $response = $transaction->getResponse();
                    $params = $transaction->getParams();
                    if ($params['METHOD'] === "DoExpressCheckoutPayment" && $response['ACK'] === "Success" && !isset ($response['REDIRECTREQUIRED'])) {
                        return true;
                    }
                }
                
                $paypalIPN = new Yourdelivery_Model_DbTable_Paypal_Notifications();
                
                $transactions = $paypalIPN->getByOrder($this->getId());
                foreach ($transactions as $transaction) {
                    if ($transaction->response === "VERIFIED") {
                        return true;
                    }
                }
                
                break;
                
            case "ebanking":
                $ebanking = new Yourdelivery_Model_DbTable_Ebanking_Transactions();

                $transactions = $ebanking->getByOrder($this->getId());
                if (count($transactions) < 2) {
                    return false;
                }

                foreach ($transactions as $transaction) {
                    $data = $transaction->getData();
                    if ((integer) $data['security_criteria'] === 1) {
                        return true;
                    }
                }

                break;
                
            case "credit" :
                $heidel = new Yourdelivery_Model_DbTable_Heidelpay_Wpf_Transactions();
                
                $transactions = $heidel->getByOrder($this->getId());
                if (count($transactions) < 2) {
                    return false;
                }

                foreach ($transactions as $transaction) {
                    if ($transaction->isResponseSuccessful()) {
                        return true;
                    }
                }
                
                $heidelXml = new Yourdelivery_Model_DbTable_Heidelpay_Xml_Transactions();
                
                $transactions = $heidelXml->getByOrder($this->getId());
                if (!count($transactions)) {
                    return false;
                }
                
                foreach ($transactions as $transaction) {
                    if ($transaction->isResponseSuccessful()) {
                        return true;
                    }
                }

                break;

            case "bar":
                return true;

            default:
                return false;
        }

        return false;
    }

}

?>
