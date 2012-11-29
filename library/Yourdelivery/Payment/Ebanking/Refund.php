<?php
/**
 * eBanking refund
 * Make a refund transaction through the sofortueberweisung API
 * @package payment
 * @author Vincent Priem <priem@lieferando.de>
 * @since 18.01.2012
 */
class Yourdelivery_Payment_Ebanking_Refund {
    
    /**
     * The url
     * @var string
     */
    private $_url = "https://www.sofortueberweisung.de/payment/refunds";
    
    /**
     * The user id
     * @var string
     */
    private $_user_id = "USER ID";
    
    /**
     * The api key
     * @var string
     */
    private $_api_key = "API KEY";

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     */
    public function __construct() {
        
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.01.2012
     * @param Yourdelivery_Model_Order $order
     * @throws Yourdelivery_Payment_Ebanking_Exception 
     * @return Yourdelivery_Payment_Ebanking_Refund_Response
     */
    public function refund(Yourdelivery_Model_Order $order, $comment = null) {
        
        $request = null;
        
        $transactions = $order->getTable()
                              ->getEbankingTransactions();
        foreach ($transactions as $transaction) {
            $data = $transaction->getData();
            if (isset($data['transaction']) && (IS_PRODUCTION || (!IS_PRODUCTION && $data['project_id'] == "96814"))) {
                $request = new Yourdelivery_Payment_Ebanking_Refund_Request($data['transaction'], $data['amount'], $comment);
                break;
            }
        }
        
        if ($request === null) {
            throw new Yourdelivery_Payment_Ebanking_Exception("No transactions found");
        }
        
        // send request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->_url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Authorization: Basic " . base64_encode($this->_user_id . ":" . $this->_api_key), 
            "Content-Type: application/xml; charset=UTF-8",
            "Accept: application/xml; charset=UTF-8",
        ));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode($request));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $curlExec = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);
        
        $dbTable = new Yourdelivery_Model_DbTable_Ebanking_Refund_Transactions();
        $dbTable->createRow(array(
            'orderId' => $order->getId(),
            'request' => $request,
            'response' => $curlExec === false ? $curlError : $curlExec,
        ))->save();
        
        if ($curlExec === false) {
            throw new Yourdelivery_Payment_Ebanking_Exception($curlError);
        }
        
        return new Yourdelivery_Payment_Ebanking_Refund_Response($curlExec);
    }
}