<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('ACOM_VERSION', '1.0');

/**
 * Description of Callback
 *
 * @author mlaug
 */
require_once APPLICATION_PATH . '/../library/Default/Controller/RestBase.php';

class Order_Acom_CallbackController extends Default_Controller_Base {

    /**
     * @var DOMDocument
     */
    protected $doc = null;

    public function init() {
        //disable view and set content type
        $this->_disableView();
        $this->getResponse()->setHeader('Content-Type', 'text/xml');
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
    }

    /**
     * this action will be called frequently from the acom shopsystem
     * on the basis of this information the orders will be delivered to the
     * shop
     * @author mlaug
     */
    public function ordersAction() {
        $orders_xml = $this->doc->createElement('orders');
        $orders_xml->appendChild(create_node($this->doc, 'version', ACOM_VERSION));
        $orders_xml->appendChild(create_node($this->doc, 'appid', 'YOURDELIVERY'));

        $service = (integer) $this->getRequest()->getParam('service');
        if ($service <= 0) {
            $this->getResponse()->setHttpResponseCode(404);
            return;
        }

        //get all acom orders, which have not been confirmed
        $acom_orders = Yourdelivery_Api_Acom_Worker::getAcomOrders($service);
        foreach ($acom_orders as $acom_order) {
            //basis information
            $order_xml = $this->doc->createElement('order');
            $order_xml->appendChild(create_node($this->doc, 'typ', 0));
            $order_xml->appendChild(create_node($this->doc, 'orderid', $acom_order->getId()));
            $order_xml->appendChild(create_node($this->doc, 'Ordertime', date('Y-m-d H:i:s', $acom_order->getTime())));
            if ($acom_order->getDeliverTime() > $acom_order->getTime()) {
                $order_xml->appendChild(create_node($this->doc, 'Deliverytime', date('Y-m-d H:i:s', $acom_order->getDeliverTime())));
            }
            $order_xml->appendChild(create_node($this->doc, 'Price', intToPrice($acom_order->getBucketTotal(), 2, ',')));
            $order_xml->appendChild(create_node($this->doc, 'deliverysum', intToPrice($acom_order->getDeliverCost(), 2, ',')));
            $order_xml->appendChild(create_node($this->doc, 'DeliveryType', 0));
            $order_xml->appendChild(create_node($this->doc, 'Remark', $acom_order->getLocation()->getComment()));                    

            //append discount code and use payment object
            if ($acom_order->getDiscountAmount() > 0) {
                
                $payments = $this->doc->createElement('payments');
                
                //add discount as payment
                $payment_discount = $this->doc->createElement('payment');
                $payment_discount->appendChild(create_node($this->doc, 'type', 206));
                $payment_discount->appendChild(create_node($this->doc, 'code', $acom_order->getDiscount()->getCode()));
                $payment_discount->appendChild(create_node($this->doc, 'value', intToPrice($acom_order->getDiscountAmount(), 2, ',')));
                $payments->appendChild($payment_discount);
                
                //add normal payment
                $payment_normal = $this->doc->createElement('payment');
                $payment_normal->appendChild(create_node($this->doc, 'type', Yourdelivery_Api_Acom_Worker::payment($acom_order->getPayment())));
                $payment_normal->appendChild(create_node($this->doc, 'value', intToPrice($acom_order->getAbsTotal(true, true, true, true, false, false, true), 2, ',')));
                $payments->appendChild($payment_normal);
                
                //append payment object
                $order_xml->appendChild($payments);
                
            }
            //otherwise use default payment field
            else{
                $order_xml->appendChild(create_node($this->doc, 'payment', Yourdelivery_Api_Acom_Worker::payment($acom_order->getPayment())));
            }
            
            //create customer as xml
            $customer_xml = $this->doc->createElement('customer');
            $customer_xml->appendChild(create_node($this->doc, 'CustID', (integer) $acom_order->getCustomerId()));
            $customer_xml->appendChild(create_node($this->doc, 'iscompany', (boolean) $acom_order->getKind() == 'comp' || strlen($acom_order->getLocation()->getCompanyName())));

            $name = '';
            $prename = '';
            if (strlen($acom_order->getLocation()->getCompanyName()) > 0) {
                $name = $acom_order->getLocation()->getCompanyName();
                $prename = $acom_order->getCustomer()->getFullname();
            } else {
                $name = $acom_order->getCustomer()->getName();
                $prename = $acom_order->getCustomer()->getPrename();
            }

            $customer_xml->appendChild(create_node($this->doc, 'name1', $name));
            $customer_xml->appendChild(create_node($this->doc, 'name2', $prename));
            $customer_xml->appendChild(create_node($this->doc, 'name3', ''));
            $customer_xml->appendChild(create_node($this->doc, 'name4', $acom_order->getLocation()->getEtage()));
            $customer_xml->appendChild(create_node($this->doc, 'sex', '0'));
            $customer_xml->appendChild(create_node($this->doc, 'streetno', $acom_order->getLocation()->getHausnr()));
            $customer_xml->appendChild(create_node($this->doc, 'street', $acom_order->getLocation()->getStreet()));
            $customer_xml->appendChild(create_node($this->doc, 'zip', $acom_order->getLocation()->getCity()->getPlz()));
            $customer_xml->appendChild(create_node($this->doc, 'city', $acom_order->getLocation()->getCity()->getCity()));
            $customer_xml->appendChild(create_node($this->doc, 'phone', $acom_order->getLocation()->getTel()));
            $customer_xml->appendChild(create_node($this->doc, 'email', $acom_order->getCustomer()->getEmail()));
            $customer_xml->appendChild(create_node($this->doc, 'mailingsallowed', 0));
            $order_xml->appendChild($customer_xml);

            //append order card
            foreach ($acom_order->getCard() as $customerBucket) {
                foreach ($customerBucket as $bucket) {
                    foreach ($bucket as $item) {
                        $mealElement = $this->doc->createElement('item');

                        //type 0 is a meal
                        $mealElement->appendChild(create_node($this->doc, 'type', 0));

                        $meal = $item['meal'];
                        $mealElement->appendChild(create_node($this->doc, 'nr', $meal->getNr()));
                        $addition = "";
                        if ( $meal->getSpecial() ){
                            $addition = __('Kundenhinweis:') . $meal->getSpecial();
                        }
                        $mealElement->appendChild(create_node($this->doc, 'Name', $meal->getName() . ' ' . $meal->getCurrentSizeName() . ' ' . $addition));
                        $mealElement->appendChild(create_node($this->doc, 'Price', intToPrice($meal->getCost(), 2, ',')));
                        $mealElement->appendChild(create_node($this->doc, 'cnt', $item['count']));

                        foreach ($meal->getCurrentExtras() as $extra) {
                            $extraElement = $this->doc->createElement('item');
                            $extraElement->appendChild(create_node($this->doc, 'Name', $extra->getName()));
                            $extraElement->appendChild(create_node($this->doc, 'Price', intToPrice($extra->getCost(false), 2, ',')));
                            $extraElement->appendChild(create_node($this->doc, 'cnt', $extra->getCount()));
                            $mealElement->appendChild($extraElement);
                        }

                        foreach ($meal->getCurrentOptions() as $option) {
                            $optionElement = $this->doc->createElement('item');
                            $optionElement->appendChild(create_node($this->doc, 'Name', $option->getName()));
                            $optionElement->appendChild(create_node($this->doc, 'Price', intToPrice($option->getCost(), 2, ',')));
                            $optionElement->appendChild(create_node($this->doc, 'cnt', 1));
                            $mealElement->appendChild($optionElement);
                        }

                        $order_xml->appendChild($mealElement);
                    }
                }
            }
            
            //append a discount
            $orders_xml->appendChild($order_xml);
        }

        //append everything
        $this->doc->appendChild($orders_xml);
        echo $this->doc->saveXML();
        $this->logger->info('ACOM: create xml for acom shop');
    }

    /**
     * this callback is triggered, once an action has been done in the acom
     * shop. the information will be delivered here!
     * @author mlaug
     * @since 03.11.2011
     */
    public function statusAction() {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->logger->warn('ACOM: called status url without posting');
            return;
        }

        $post = $request->getPost();
        $status = (integer) $post['status'];
        $orderId = (integer) $post['orderid'];
        $text = (integer) $post['text'];

        try {
            $order = new Yourdelivery_Model_Order($orderId);
            if ($order->getService()->getNotify() != 'acom') {
                $this->logger->crit(sprintf('ACOM: tried to alter the status of order %s which service is not set to acom', $order->getId()));
                $this->getResponse()->setHttpResponseCode(500);
                return;
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->getResponse()->setHttpResponseCode(404);
            return;
        }

        switch ($status) {
            default:
                //no valid case given
                $this->logger->warn(sprintf('ACOM: invalid response ' . $status . ' on order %d', $order->getId()));
                $order->setStatus(Yourdelivery_Model_Order::DELIVERERROR,
                                                new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_ACOM_INVALID, $text)
                        );
                break;
            case 1:
                //in progress
                $this->logger->info(sprintf('ACOM: order %d is in progress', $order->getId()));
                $order->setStatus(Yourdelivery_Model_Order::AFFIRMED,
                                                new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_ACOM_PROGRESS, $text)
                 );
                break;
            case 2:
                //OK
                $this->logger->info(sprintf('ACOM: order %d has been confirmed', $order->getId()));
                $order->setStatus(Yourdelivery_Model_Order::AFFIRMED,
                                                 new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_ACOM_CONFIRM, $text)
                        );
                break;
            case 3:
                //ERROR
                $this->logger->warn(sprintf('ACOM: error occured on order %d', $order->getId()));
                $order->setStatus(Yourdelivery_Model_Order::DELIVERERROR,
                          new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_ACOM_ERROR, $text)
                );
                break;
            case 4:
                //delivery in progress
                $this->logger->info(sprintf('ACOM: order %d is currently beeing delivered', $order->getId()));
                $order->setStatus(Yourdelivery_Model_Order::AFFIRMED, 
                        new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_ACOM_DELIVERING, $text)
                   );
                break;
            case 5:
                //delivery completed
                $this->logger->info(sprintf('ACOM: order %d has been delivered', $order->getId()));
                $order->setStatus(Yourdelivery_Model_Order::DELIVERED,
                        new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_ACOM_DELIVERED, $text)
                   );
                break;
        }
        
        echo "OK";
    }

}

?>
