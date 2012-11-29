<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * The API for Woopla handles all calls in france, where the services are used to
 * be called after each placed order
 *
 * @author Matthias Laug <laug@lieferando.de>
 * @since 07.09.2010
 */
class Get_CallController extends Default_Controller_RestBase {

    /**
     * the index method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function indexAction() {
        $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the get method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function getAction() {
        $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * Post action to affirm fax receival via woopla service
     * we get an orderid and either a false or a true
     *
     * <ul>
     *  <li>406 if parameters are invalid</li>
     *  <li>404 if order by given id is not found</li>
     *  <li>200 for a valid call, no matter if positive or negative</li>
     * </ul>
     *
     * @author mlaug
     * @since 14.09.2010
     * @return integer HTTP_RESPONSE_CODE
     */
    public function postAction() {
        $request = $this->getRequest();
        $post = $request->getPost();

        if (!isset($post['id']) || !isset($post['answer'])) {
            $this->logger->warn('API - CALL - POST: missing parameters');
            $this->message = 'Missing parameters';
            $this->getResponse()->setHttpResponseCode(406);
            return;
        }

        $orderId = (integer) $post['id'];
        $answer = (integer) $post['answer'];

        try {
            $order = new Yourdelivery_Model_Order($orderId);
            $this->logger->debug(sprintf('API - CALL - POST: found order #%s', $orderId));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->logger->warn(sprintf('API - CALL - POST: could not find order #%s', $orderId));
            $this->message = 'Could not find order by given id';
            $this->getResponse()->setHttpResponseCode(404);
            return;
        }

        if ($answer == 1) {
            $this->logger->info(sprintf('API - CALL - POST: service validated receival of order #%s with response %s', $orderId, $post['answer']));
            $order->setStatus(
                    Yourdelivery_Model_Order::DELIVERED, 
                    new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::WOOPLA_OK)                    
            );
        } else {
            $this->logger->info(sprintf('API - CALL - POST: service declined receival of order #%s with response %s', $orderId, $post['answer']));
            $order->setStatus(
                    Yourdelivery_Model_Order::DELIVERERROR,
                     new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::WOOPLA_ERROR)                                       
            );
        }
    }

    /**
     * the put method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function putAction() {
        $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the put method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function deleteAction() {
        $this->getResponse()->setHttpResponseCode(403);
    }

}

?>
