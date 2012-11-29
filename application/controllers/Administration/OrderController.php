<?php

/**
 * Administration order
 * @author mlaug
 */
class Administration_OrderController extends Default_Controller_AdministrationBase {

    /**
     * List all orders
     * @author vpriem
     * @since 30.11.2010
     */
    public function indexAction() {

        $request = $this->getRequest();
        $type = $request->getParam('type', 'view_grid_orders_this_day');
        $filters = array(
            'payerId' => $request->getParam('payerId'),
            'customertel' => $request->getParam('tel'),
            'restauranttel' => $request->getParam('resttel'),
            'service' => $request->getParam('Dienstleistergrid'),
            'customername' => $request->getParam('Namegrid'),
            'rabattId' => $request->getParam('rabattId'),
        );

        $grid = Default_Helpers_Grid::generateOrderGrid($type, $filters);

        // deploy grid to view
        $grid->setExport(array('csv'));
        $this->view->grid = $grid->deploy();
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @todo this function should automatically recreate all billings!!!!!!
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id', null);

        $order = null;
        try {
            $order = new Yourdelivery_Model_Order($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }

        //no support for company orders
        if ($order->getKind() == "comp") {
            $this->error(__b("Aktuelle werden nur private Bestellungen unterstützt"));
            $this->_redirect('/administration_order');
        }

        $this->view->assign('order', $order);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $taxes = $request->getParam('tax', null);
            $counts = $request->getParam('count', null);
            $specials = $request->getParam('special', null);
            //get all bucketIds involved
            $ids = $request->getParam('ids', null);

            if (is_array($ids)) {
                // go through every meal
                foreach ($ids as $key) {
                    $meal = null;
                    $meal = new Yourdelivery_Model_Order_BucketMeals($key);
                    $order->changeTaxOfMeal($meal, $taxes[$key]);
                    $order->changeCountOfMeal($meal, $counts[$key]);
                    $order->changeSpecial($meal, $specials[$key]);
                }
            }

            // must go through options and extras seperate because of different counts
            $options = $request->getParam('option', null);
            $oldOptions = $request->getParam('oldoption', null);
            if (!is_null($options)) {
                foreach ($options as $key => $option) {
                    $meal = null;
                    $meal = new Yourdelivery_Model_Order_BucketMeals($key);
                    $order->changeOption($meal, $oldOptions[$key], $options[$key]);
                }
            }

            $extras = $request->getParam('extra', null);
            $oldExtras = $request->getParam('oldextra', null);
            if (!is_null($extras)) {
                foreach ($extras as $key => $extra) {
                    $meal = null;
                    $meal = new Yourdelivery_Model_Order_BucketMeals($key);
                    $order->changeOption($meal, $oldExtras[$key], $exras[$key]);
                }
            }

            $comment = $request->getParam('comment', null);
            $order->changeComment($comment);

            $order->renewOrder();
            $this->success(__b("Änderungen wurden gespeichert"));
        }
    }

    /**
     * search for orders by given id or number, both with prefixes
     * @author Alex Vait <vait@lieferando.de>
     * @since 13.12.2011
     * @see YD-709
     */
    public function searchAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Administration_Order_Search();

            if ($form->isValid($post)) {
                $config = Zend_Registry::get('configuration');
                $domain_ending = end(explode('.', $config->domain->base));

                $values = $form->getValues();
                $input = explode("\n", $values['searchfor']);

                $orderIds = array();
                $orderNrs = array();

                // save order ids and order numbers in different arrays
                foreach ($input as $idnr) {
                    $inputArr = explode(";", $idnr);

                    $matchId = array();
                    $matchPfxId = array();
                    $matchNr = array();
                    $idnr = trim($idnr);

                    if ((preg_match('/[0-9]+/', $idnr, $matchId) > 0) && (is_numeric($idnr))) {
                        $orderIds[] = intval($matchId[0]);
                    }

                    if (preg_match('/(' . $domain_ending . '\_)([0-9a-zA-Z]+)/', $idnr, $matchPfxId) > 0) {
                        $orderIds[] = intval($matchPfxId[2]);
                    }

                    if (preg_match('/(NB\_S\_)([0-9a-zA-Z]+)/', $idnr, $matchNr) > 0) {
                        $orderNrs[] = $matchNr[2];
                    }
                }

                $orderIds = array_unique($orderIds);
                $orderNrs = array_unique($orderNrs);

                $whereId = '0';
                $whereNr = '0';

                if (sizeof($orderIds) > 0) {
                    $whereId = 'o.id IN (' . implode(',', $orderIds) . ')';
                }
                if (sizeof($orderNrs) > 0) {
                    $whereNr = 'o.nr IN (\'' . implode('\',\'', $orderNrs) . '\')';
                }

                $whereString = $whereId . ' OR ' . $whereNr;

                $orders = Yourdelivery_Model_DbTable_Order::searchOrdersPerIdNr($whereString);

                $result = "";
                foreach ($orders as $o) {
                    $result .= $domain_ending . '_' . $o['id'] . ";NB_S_" . $o['nr'] . ";" . (intval($o['state']) > 0 ? 'bestätigt' : 'storno') . ";";
                    $result .= ((empty($o['rabattId'])) ? "[kein];[kein];[kein];" : $o['rabattId'] . ";" . $o['rabattName'] . ";" . $o['code'] . ";");
                    $result .= "regAfSale:" . (($o['registeredAfterSale']) ? "ja" : "nein") . ";";
                    $result .= (($o['orderTime'] == $o['time']) || is_null($o['orderTime'])) ? "Neukunde;" : 'Bestandskunde;';
                    $result .= number_format(($o['total'] + $o['serviceDeliverCost'] + $o['courierCost']) / 100, 2, ',', ".");
                    $result .= '\n';
                }

                $this->view->input = $values['searchfor'];
                $this->view->result = $result;
            } else {
                $this->error($form->getMessages());
            }
        }
    }

    /**
     * search for orders by given id and cancel them
     * @author Allen Frank <frank@lieferando.de>
     * @since 22-02-2012
     */
    public function massstornoAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Administration_Order_Search();

            if ($form->isValid($post)) {

                $values = $form->getValues();

                $input = explode("\n", $values['searchfor']);
                $cancelOrder = (Boolean) $values['cancel'];
                $orderIds = array();
                $orderNrs = array();
                $orderNotFound = array();
                $messages = array();
                $ordersWithDiscount = array();
                $whereId = '0';
                $whereNr = '0';

                // save order ids and order numbers in different arrays
                foreach ($input as $idnr) {
                    $idnr = trim($idnr);
                    $matchId = array();
                    $matchNr = array();

                    if (!ctype_alnum($idnr)) {
                        $orderNotFound[] = $idnr;
                    } else {
                        if ((preg_match('/[0-9]+/', $idnr, $matchId) > 0) && (is_numeric($idnr))) {
                            $orderIds[] = intval($matchId[0]);
                        }
                        if ((preg_match('/([0-9a-zA-Z]+)/', $idnr, $matchNr) > 0)) {
                            $orderNrs[] = $matchNr[0];
                        }
                    }
                }

                $orderIds = array_unique($orderIds);
                $orderNrs = array_unique($orderNrs);

                if (sizeof($orderIds) > 0) {
                    $whereId = 'o.id IN (' . implode(',', $orderIds) . ')';
                }
                if (sizeof($orderNrs) > 0) {
                    $whereNr = 'o.nr IN (\'' . implode('\',\'', $orderNrs) . '\')';
                }

                $whereString = $whereId . ' OR ' . $whereNr;

                $orders = Yourdelivery_Model_DbTable_Order::searchOrdersPerIdNr($whereString);

                $allOrders = array_unique(array_merge($orderIds, $orderNrs));

                foreach ($orders as $o) {

                    $key = in_array($o['id'], $allOrders) ? array_search($o['id'], $allOrders) : array_search($o['nr'], $allOrders);
                    if ($key !== false) {
                        unset($allOrders[$key]);
                    }

                    $messages[] = __b('%sID:%s/Nr:%s ', "\n", $o['id'], $o['nr']);

                    if ($o['state'] == -2) {
                        $messages[] = __b('Bestellung wurde schon storniert');
                        continue;
                    } elseif (isset($o['rabattCodeId']) && !$cancelOrder) {
                        $cancel = 1;
                        $messages[] = __b('ist mit einem Gutschein bezahlt worden', "\n", $o['id'], $o['nr']);
                        $ordersWithDiscount[] = $o['id'];
                        continue;
                    }

                    try {
                        $order = new Yourdelivery_Model_Order($o['id']);
                        $oldStatus = $order->getState();
                        $order->setStatus(Yourdelivery_Model_Order_Abstract::STORNO, new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_STORNO_MASSSTORNO, $this->session_admin->admin->getName())
                                , true);

                        // Using config-based locale during composing and sending e-mail
                        $this->_restoreLocale();
                        // send storno to restaurant
                        if ($oldStatus >= 0 && (Boolean) $values['notify_restaurant']) {
                            $order->sendStornoNotificationToRestaurant();
                        }
                        if ((Boolean) $values['notify_user']) {
                            $order->sendStornoEmailToUser();
                        }
                        $this->_overrideLocale();

                        if ($order->getState() == -2) {
                            $messages[] = __b('Bestellung erfolgreich storniert');
                        }

                        $messages_tmp = array();
                        switch ($order->getPayment()) {
                            case 'paypal':
                                Yourdelivery_Helpers_Payment::refundPaypal($order, $this->logger, $messages);
                                break;
                            case 'ebanking':
                                Yourdelivery_Helpers_Payment::refundEbanking($order, $this->logger, $messages, __b("storniert von %s", $this->session_admin->admin->getName()));
                                break;
                            case 'credit':
                                Yourdelivery_Helpers_Payment::refundCredit($order, $this->logger, $messages);
                                break;
                            default:
                            //nothing special to do
                        }
                        $this->logger->adminInfo(sprintf("Successfully canceled order: %d by ", $order->getId(), $this->session_admin->admin->getName()));
                        $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::ORDER_STORNO, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $order->getId());

                        // Using config-based locale during composing and sending e-mail
                        $this->_restoreLocale();
                        $order->sendStornoEmailToUser();
                        $this->_overrideLocale();
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->logger->adminErr(sprintf("Could not cancel %d", $o['id']));
                        $messages[] = __b("Konnte die Bestellung ") . $this->id . __b(" nicht finden");
                    }
                }
                if (sizeof($orderNotFound) > 0 || sizeof($allOrders) > 0) {
                    $messages[] = __b('%sFolgende Bestell-Ids/Nr wurden nicht gefunden bzw sind redundant: %s', '\n', implode(', ', array_merge($orderNotFound, $allOrders)));
                }

                $this->view->input = trim(sprintf('%s %s %s', implode("\n", $ordersWithDiscount), '\n', implode("\n", $orderNotFound)));
                $this->view->checkbox = array('cancel' => $cancel, 'notify_user' => $values['notify_user'], 'notify_restaurant' => $values['notify_restaurant']);
                $this->view->messageForOrdersWithDiscount = sizeof($ordersWithDiscount);
                $this->view->message = implode(", ", $messages);
            } else {
                $this->error($form->getMessages());
            }
        }
    }

    /**
     * show input fields for order search. Will be redirected to the order grid
     * @author Alex Vait <vait@lieferando.de>
     * @since 04.01.2012
     */
    public function gridsearchAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $url = "/administration_order/index/type/view_grid_orders";

            if (strlen($post['orderId']) > 0) {
                $url = $url . "/IDgrid/" . $post['orderId'];
            }

            if (strlen($post['orderNr']) > 0) {
                $url = $url . "/Nummergrid/" . $post['orderNr'];
            }

            if (strlen($post['customerName']) > 0) {
                $url = $url . "/Namegrid/" . $post['customerName'];
            }

            if (strlen($post['customerId']) > 0) {
                $url = $url . "/customerIdgrid/" . $post['customerId'];
            }

            if (strlen($post['customerEmail']) > 0) {
                $url = $url . "/Emailgrid/" . str_replace(' ', '', $post['customerEmail']);
            }

            if (strlen($post['ipAddr']) > 0) {
                $url = $url . "/ipAddrgrid/" . str_replace(' ', '', $post['ipAddr']);
            }

            $this->_redirect($url);
        }
    }

}
