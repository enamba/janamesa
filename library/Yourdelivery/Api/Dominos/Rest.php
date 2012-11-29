<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rest
 *
 * @author mlaug
 */
require_once APPLICATION_PATH . '/../library/Default/Controller/RestBase.php';

class Yourdelivery_Api_Dominos_Rest extends Zend_Soap_Client{

    protected $key = null;
    protected $doc = null;
    protected $logger = null;

    public function __construct($uri = null) {
        if ($uri == null) {
            //use default staging url
            $uri = 'URL';
        }

        //set hardcoded key
        $this->key = 'KEY';

        //disable view and set content type
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
        
        $this->logger = Zend_Registry::get('logger');

        parent::__construct($uri);
    }

    /**
     * check wether the service is online or not
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function checkIfOnline(){
        $result = $this->Online();
        if ( !is_array($result) || !isset($result['OnlineResult']) || !$result['OnlineResult'] ){
            return false;
        } 
        return true;
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @param type $plz 
     */
    public function getStoreByPlz($plz){
        return $this->getStoreByPostcode($plz, 0, 1);
    }
    
    /**
     * place an order to the soap api
     * 
     * @author mlaug
     * @since 28.02.2012
     * @param Yourdelivery_Model_Order $orderXml 
     * @return boolean
     */
    public function doOrder(Yourdelivery_Model_Order $orderObj) {
        
        /*if ( !$this->checkIfOnline() ){
            return false;
        }*/
        
        $orderXml = $this->doc->createElement('Order');
        $basket = $this->doc->createElement('Basket');
        $items = $this->doc->createElement('items');
        
        //append order card
        foreach ($orderObj->getCard() as $customerBucket) {
            foreach ($customerBucket as $bucket) {
                foreach ($bucket as $item) {
                    $mealElement = $this->doc->createElement('BasketItem');

                    $meal = $item['meal'];
                    $mealElement->appendChild(create_node($this->doc, 'productSkuId', $meal->getNr()));
                    $mealElement->appendChild(create_node($this->doc, 'secondarySkuId', -1));
                    
                    $addition = "";
                    if ($meal->getSpecial()) {
                        $addition = __('Kundenhinweis:') . $meal->getSpecial();
                    }
                    
                    $mealElement->appendChild(create_node($this->doc, 'quantity', $item['count']));

                    /*foreach ($meal->getCurrentExtras() as $extra) {
                        $extraElement = $this->doc->createElement('item');
                        $extraElement->appendChild(create_node($this->doc, 'Name', $extra->getName()));
                        $extraElement->appendChild(create_node($this->doc, 'Price', intToPrice($extra->getCost(), 2, ',')));
                        $extraElement->appendChild(create_node($this->doc, 'cnt', 1));
                        $mealElement->appendChild($extraElement);
                    }

                    foreach ($meal->getCurrentOptions() as $option) {
                        $optionElement = $this->doc->createElement('item');
                        $optionElement->appendChild(create_node($this->doc, 'Name', $option->getName()));
                        $optionElement->appendChild(create_node($this->doc, 'Price', intToPrice($option->getCost(), 2, ',')));
                        $optionElement->appendChild(create_node($this->doc, 'cnt', 1));
                        $mealElement->appendChild($optionElement);
                    }*/

                    $items->appendChild($mealElement);
                }
            }
        }
        
        //delivery info
        $location = $orderObj->getLocation();
        $customer = $orderObj->getCustomer();
        $deliveryDetails = $this->doc->createElement('deliveryDetails');
        $deliveryDetails->appendChild(create_node($this->doc, 'type', 'DELIVERY'));
        $deliveryDetails->appendChild(create_node($this->doc, 'paymentType', 'CARD'));
        $deliveryDetails->appendChild(create_node($this->doc, 'contactName', $customer->getFullname()));
        $deliveryDetails->appendChild(create_node($this->doc, 'contactPhone', Default_Helpers_Normalize::telephone($location->getTel())));
        $deliveryDetails->appendChild(create_node($this->doc, 'contactEmail', $customer->getEmail()));
        $deliveryDetails->appendChild(create_node($this->doc, 'address', $location->getAddress()));
        $deliveryDetails->appendChild(create_node($this->doc, 'addressLookupId', '0'));
        $deliveryDetails->appendChild(create_node($this->doc, 'optInEmail', 'false'));
        $deliveryDetails->appendChild(create_node($this->doc, 'optInSMS', 'false'));
        $deliveryDetails->appendChild(create_node($this->doc, 'deliveryTime', '10:00'));
        
        //payment info
        $paymentDetails = $this->doc->createElement('paymentDetails');
        $paymentDetails->appendChild(create_node($this->doc, 'merchantReference', 'COLLECTION'));
        $paymentDetails->appendChild(create_node($this->doc, 'amount', intToPrice($orderObj->getAbsTotal())));
        $paymentDetails->appendChild(create_node($this->doc, 'origin', '0'));
        $paymentDetails->appendChild(create_node($this->doc, 'paymentProviderReference', 'DUMMYREFERENCE'));
        
        
        $basket->appendChild($items);
        $orderXml->appendChild($basket);
        $orderXml->appendChild($deliveryDetails);
        $orderXml->appendChild($paymentDetails);
        $orderXml->appendChild(create_node($this->doc, 'platformId', '20'));
        $orderXml->appendChild(create_node($this->doc, 'storeId', '28335'));
        $this->doc->appendChild($orderXml);
        
        //place order to api
        $this->logger->debug('sending xml to dominos: ' . $this->doc->saveXml());
        $result = $this->placeOrder();
        $this->logger->info(sprintf('response from dominos %d with result code %d', $result->isSuccess(), $result->getStatus()));
        return true;
    }

}

?>
