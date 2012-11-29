<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Yourdelivery_Sender_Wiercik
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 04.05.2012
 */
class Yourdelivery_Sender_Wiercik {

    /**
     * Object constructor
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @param array $orderData
     */
    public function __construct(Yourdelivery_Model_Order $order, $printerId) {
        $this->order = $order;        // array with complete order data, its structure should be easy to replace (if needed)
        $this->printerId = $printerId;
        $this->logger  = Zend_Registry::get('logger');
        
    }

    /**
     * Sends order data to printer queue (to be sent as notification onto GPRS printer)
     * Works for standard Pyszne.pl GPRS printers
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @return boolean
     */
    public function sendToPrinterQueue() {
        try {
            $object = new stdClass();
            $object->type = 'order';
            $object->target_id = $this->printerId;  // printer numeric identifier, assigned to restaurant
            $object->order_id = $this->order->getId();              // unique alphanum order key (up to 12 characters)
            $object->placed_at = $this->order->getLastStateChange();                         // timestamp of order request
            $object->content = $this->getPrinterXmlOrderContent();                               // see below
            $object->time_requested = $this->order->getDeliverTimestamp();                 // timestamp of requested order delivery time (if given, null otherwise)
            $serializedObject = serialize($object);
            $this->logger->debug('Serialized order object for printer: ' . $serializedObject);
            $config = Zend_Registry::get('configuration');
            Pheanstalk_Queue::init($config->pheanstalk);
            $queue = new Pheanstalk_Queue();
            $queue->insert($serializedObject);
            return true;
        } catch (ErrorException $ex) {
        }
        
        return false;
    }
    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 04.05.2012
     * @return type 
     */
    public function getPrinterXmlOrderContent() {

        $customer = $this->order->getCustomer();
        $location = $this->order->getLocation();
    
        
        $timestampDiff = (date('O')/100 * 3600);
        
        $orderXML = new DOMDocument();
        $orderXML->loadXml("<order></order>");
        $orderElem = $orderXML->documentElement;
        $orderElem->appendChild($orderXML->createElement("orderId", $this->order->getNr()));
        $orderInformation = $orderXML->createElement('orderInformation');
        $orderInformation->appendChild($orderXML->createElement('orderType', 'delivery'));
        $orderInformation->appendChild($orderXML->createElement('orderTime', date("U", $this->order->getLastStateChange() + $timestampDiff))); //maybe strtotime
        $orderInformation->appendChild($orderXML->createElement('requestedFor', ($this->order->getDeliverTimestamp() == date("U", $this->order->getTime()))? '': 'Dostawa na: ' . date("G:i",$this->order->getDeliverTimestamp())));
        $orderInformation->appendChild($orderXML->createElement('distance', 0));
        
        $payment = ($this->order->getPayment() == 'bar')? "cash": 'epayment';
        //Fix for payment if discount amount larger than order amount
        if($this->order->getAbsTotal(false,false) - $this->order->getDiscountAmount() <= 0) {
            $payment = "epayment";
        }
        
        $orderInformation->appendChild($orderXML->createElement('paymentMethod', $payment));
        $orderInformation->appendChild($orderXML->createElement('paymentStatus', ($this->order->getPayment() == 'bar')? "pending": 'paid'));
        $orderInformation->appendChild($orderXML->createElement('specialOffer', ''));
        $orderInformation->appendChild($orderXML->createElement('deliveryCharge', sprintf('%.2f',$this->order->getServiceDeliverCost()/100)));
        $orderInformation->appendChild($orderXML->createElement('packageCharge', 0));
        $orderInformation->appendChild($orderXML->createElement('orderTotal',sprintf('%.2f',$this->order->getAbsTotal(false,false)/100)));
        $orderInformation->appendChild($orderXML->createElement('invoiceRequested', 'no'));

        $orderElem->appendChild($orderInformation);
        
        //beware, this can be an array or an splObjectStorage depending on Model
        $orderCount = count($customer->getOrders());                
        $customerElem = $orderXML->createElement('customer');
        $customerElem->appendChild($orderXML->createElement("prevOrders", ($orderCount <= 3)? $orderCount: "3+"));
        $customerElem->appendChild($orderXML->createElement("name", htmlspecialchars($customer->getFullname())));
        $customerElem->appendChild($orderXML->createElement("phone", htmlspecialchars($location->getTel())));
        $customerElem->appendChild($orderXML->createElement("addressStreet", htmlspecialchars($location->getStreet() . " " . $location->getHausnr())));
        $customerElem->appendChild($orderXML->createElement("addressCity", htmlspecialchars($location->getCity()->getFullname())));
        
        
        $comments = ($location->getCompanyName()) ? __('Firma: ').$location->getCompanyName(). " ": "";
        $comments .= ($location->getEtage()) ? __('Etage: '). $location->getEtage(). " " : "";
        $comments .= $location->getComment();
                
        $customerElem->appendChild($orderXML->createElement("comments", ($comments)? htmlspecialchars($comments): '\\n'));

        $orderElem->appendChild($customerElem);

        $cartElems = $orderXML->createElement('products');

        $card = $this->order->getCard();

        foreach ($card['bucket'] as $items) {
            foreach ($items as $item) {
                  $meal = $item['meal'];
                  $product = $orderXML->createElement('product');
                  $product->appendChild($orderXML->createElement("name", htmlspecialchars($meal->getName() . " " . $meal->getCurrentSizeName())));
                  $product->appendChild($orderXML->createElement("quantity",  $item['count'])); 
                  $product->appendChild($orderXML->createElement("price",  sprintf('%.2f',($meal->getCost()/ 100)))); 
                  
                  $additions = $orderXML->createElement('additions');
                  
                  foreach ($meal->getCurrentOptions() as $option) {
                      $addition = $orderXML->createElement('addition');
                      $addition->appendChild($orderXML->createElement("name", htmlspecialchars($option->getName())));
                      $addition->appendChild($orderXML->createElement("quantity", 1));
                      $addition->appendChild($orderXML->createElement("price", sprintf('%.2f',($option->getCost()/100))));
                      $additions->appendChild($addition);
                  }
                  
                 foreach ($meal->getCurrentExtras() as $extra) {
                      $addition = $orderXML->createElement('addition');
                      $addition->appendChild($orderXML->createElement("name", htmlspecialchars($extra->getName())));
                      $addition->appendChild($orderXML->createElement("quantity", $extra->getCount()));
                      $addition->appendChild($orderXML->createElement("price", sprintf('%.2f',($extra->getCost()/100))));
                      $additions->appendChild($addition);
                  }
                  
                  if($meal->getSpecial() != "") {
                      $addition = $orderXML->createElement('addition');
                      $addition->appendChild($orderXML->createElement("name", htmlspecialchars($meal->getSpecial())));
                      $addition->appendChild($orderXML->createElement("quantity", 1));
                      $addition->appendChild($orderXML->createElement("price", 0));
                      $additions->appendChild($addition);
                  }
                  
                  $product->appendChild($additions);
                  
                  $cartElems->appendChild($product);
            }
        }
        
        $orderElem->appendChild($cartElems);
        
        return $orderXML->saveXml();
    }

}

