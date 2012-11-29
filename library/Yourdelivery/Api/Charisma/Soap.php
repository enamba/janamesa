<?php

/**
 * Soap client for Charisma API
 * for further information you may contact
 * "Marcelle Hövelmanns, art solution GmbH" <marcelle.hoevelmanns@artsolution.de>
 * 
 * the sandbox can be found under 
 * PRODUCTION: http://webservice.artsolution.de/sandbox/apikey/1679091c5a880faf6fb5e6087eb1b2dc 
 * 
 * DEVELOPMENT: http://webservice.artsolution.de/sandbox/apikey/d26f0afc5779984c9e35ae67b6e0d16b
 * 
 * @author Matthias Laug <laug@lieferando.de> 
 */
class Yourdelivery_Api_Charisma_Soap {

    /** WSDL-URL ******************************************** */
    private $_WSDL_URI = "URL";

    /**
     * @var Yourdelivery_Log
     */
    private $logger = null;

    /**
     * API key for production mode
     * @var string 
     */
    private $apiKeyProduction = 'Production API Key';
    
    /**
     * API key for production mode
     * @var string 
     */
    private $apiKeyDevelopment = 'Develop API Key';
    
    const RECEIVED = 1;
    const FETECHED_BY_CLIENT = 2;
    const PRINTED_AND_FINISHED = 3 ;
    const PRINTERERROR = 4 ;

    public function __construct() {
        ini_set("soap.wsdl_cache_enabled", 0);
        $this->logger = Zend_Registry::get('logger');
    }

    /**
     * get state of order in carisma api
     * 
     * @param Yourdelivery_Model_Order $order
     * @return integer
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.08.2012
     */
    public function getStatus(Yourdelivery_Model_Order $order) {
        $client = new Zend_Soap_Client($this->_WSDL_URI, array('encoding' => 'utf-8'));
        $response = @json_decode($client->getOrderStatus($order->getNr(), IS_PRODUCTION ? $this->apiKeyProduction : $this->apiKeyDevelopment), true);
        return $response['OrderStatus'];
        
    }

    /**
     * send an order to the soap api and get a result. 
     * HTTP CODE 201 => order has been created and we get a json response in body
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 16.07.2012
     * @param Yourdelivery_Model_Order $order
     * @return boolean 
     */
    public function placeOrder(Yourdelivery_Model_Order $order) {

        $client = new Zend_Soap_Client($this->_WSDL_URI, array('encoding' => 'utf-8'));

        $parameter = array(
            0 =>
            array(
                'apiKey' => IS_PRODUCTION ? $this->apiKeyProduction : $this->apiKeyDevelopment,
                'payment' => $order->getPayment(),
                'total_sum' => intToPrice($order->getBucketTotal() + $order->getDeliverCost()),
                'delivery_costs' => intToPrice($order->getDeliverCost()),
                'partnerOrderId' => $order->getNr(),
                'dishes' => array(),
                'customer' => array(
                    'title' => strlen($order->getCustomer()->getAnrede()) > 0 ? $order->getCustomer()->getAnrede() : ' ',
                    'first_name' => $order->getCustomer()->getPrename(),
                    'last_name' => $order->getCustomer()->getName(),
                    'street' => $order->getLocation()->getStreet(),
                    'street_number' => $order->getLocation()->getHausnr(),
                    'zip' => $order->getLocation()->getCity()->getPlz(),
                    'city' => $order->getLocation()->getCity()->getCity(),
                    'location' => '',
                    'company' => strlen($order->getLocation()->getCompanyName()) > 0 ? $order->getLocation()->getCompanyName() : ' ',
                    'email' => $order->getCustomer()->getEmail(),
                    'level' => strlen($order->getLocation()->getEtage()) > 0 ? $order->getLocation()->getEtage() : ' ',
                    'delivery_note' => strlen($order->getLocation()->getComment()) > 0 ? $order->getLocation()->getComment() : ' ',
                    'phone' => $order->getLocation()->getTel(),
                    'delivery_time' => $order->getDeliverTime(),
                    'order_time' => $order->getTime()
                )
            )
        );

        $bucket = $order->getCard();
        foreach ($bucket['bucket'] as $cBucket) {
            foreach ($cBucket as $item) {
                $meal = $item['meal'];
              
                $mealData = array(
                    'sku' => strlen($meal->getNr()) == 0 ? 'NN' : $meal->getNr(),
                    'caption' => $meal->getName(),
                    'category' => $meal->getCategory()->getName(),
                    'currency' => 'EUR',
                    'size' => $meal->getCurrentSizeName(),
                    'price' => intToPrice($meal->getCost()),
                    'quantity' => $item['count'],
                    'tax_group' => $meal->getTax() == '7' ? 'a' : 'b',
                    'sum_row' => intToPrice($item['count'] * $meal->getCost()),
                    'extras' => array(),
                    'toppings' => array()
                );

                foreach ($meal->getCurrentExtras() as $extra) {
                    $mealData['extras'][] = array(
                        'name' => $extra->getName(),
                        'additional' => true,
                        'quantity' => $extra->getCount(),
                        'price' => intToPrice($extra->getCost(false)),
                        'sum_row' => intToPrice($extra->getCost(false)* $extra->getCount())
                    );
                }

                foreach ($meal->getCurrentOptions() as $option) {
                    $mealData['toppings'][] = array(
                        'name' => $option->getName(),
                        'price' => intToPrice($option->getCost()),
                        'sum_row' => intToPrice($option->getCost()* $option->getCount())
                    );
                }

                $parameter[0]['dishes'][] = $mealData;
            }
        }
        
        $response = @json_decode($client->addNewOrder($parameter), true);
        $headers = $client->getLastResponseHeaders();

        if ((strstr($headers, 'HTTP/1.1 201 Created') || strstr($headers, 'HTTP/1.1 200 OK')) && is_array($response)) {
            if ($response['msg'] == 'Success') {
                $this->logger->info(sprintf('successfully pushed order #%s to charisma api', $order->getId()));
                $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::CHARISMA_SUCCESS));
                return true;
            } else {
                $this->logger->warn(sprintf('failed to push order #%s to charisma api: %s', $order->getId(), implode(' || ', $response['msg']['Error'])));
                $order->setStatus(Yourdelivery_Model_Order::DELIVERERROR, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::CHARISMA_FAIL, implode(' || ', $response['msg']['Error'])));
                return false;
            }
        } else {
            $this->logger->crit(sprintf('failed to push order #%s to charisma because of invalid http response in headers: %s', $order->getId(), $headers));
            $order->setStatus(Yourdelivery_Model_Order::DELIVERERROR, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::CHARISMA_FAIL, 'no valid http response'));
            return false;
        }
    }

}
