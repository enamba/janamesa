<?php

/**
 * Description of BlacklistController
 *
 * @author daniel
 */
class Administration_BlacklistController extends Default_Controller_AdministrationBase {

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.06.2012
     * @param array $types
     * @param string $type
     * @return Bvb_Grid
     */
    protected function _getGrid(array $types) {

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
                ->from(array('b' => 'blacklist'), array(
                    'bv.type',
                    'bv.value',
                    'bv.behaviour',
                    'bv.hits',
                    'bv.deleted',
                    'bv.matching',
                    'comment',
                    'orderId',
                    'aau.name',
                    'created' => new Zend_Db_Expr("DATE_FORMAT(b.created, '%d.%m.%Y')"),
                    'valueId' => 'bv.id',
                    'bv.blacklistId',
                ))
                ->join(array('bv' => "blacklist_values"), "b.id = bv.blacklistId", array())
                ->joinLeft(array('aau' => 'admin_access_users'), 'aau.id = b.adminId', array())
                ->where('bv.type IN (?)', array_keys($types));

        $grid = Default_Helper::getTableGrid();
        $grid->setExport(array());
        $grid->setPagination(20);
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('orderId', array('title' => __b("Bestellung"), 'callback' => array('function' => 'gridBlacklistOrderLink', 'params' => array('{{orderId}}'))));
        $grid->updateColumn('comment', array('title' => __b("Wieso")));
        $grid->updateColumn('created', array('title' => __b("Wann")));
        $grid->updateColumn('type', array('title' => __b("Spalte"), 'decorator' => '<span class="striked-{{deleted}}">{{type}}</span>'));
        $grid->updateColumn('value', array('title' => __b("Wert"), 'decorator' => '<span class="striked-{{deleted}}">{{value}}</span>'));
        $grid->updateColumn('behaviour', array('title' => __b("Verhalten")));
        $grid->updateColumn('hits', array('decorator' => '<a href="/administration_request_blacklist/matchings/id/{{valueId}}" class="yd-blacklist-lightbox-matchings">{{hits}}</span>'));
        $grid->updateColumn('name', array('title' => __b("Supporter")));
        $grid->updateColumn('deleted', array('hidden' => 1));
        $grid->updateColumn('valueId', array('hidden' => 1));
        $grid->updateColumn('blacklistId', array('hidden' => 1));

        $filters = new Bvb_Grid_Filters();
        $filters->addFilter('type', array('values' => $types))
                ->addFilter('value')
                ->addFilter('behaviour', array('values' => Yourdelivery_Model_Support_Blacklist::getBehaviours()))
                ->addFilter('matching', array('values' => Yourdelivery_Model_Support_Blacklist::getMatchings()))
                ->addFilter('hits')
                ->addFilter('comment')
                ->addFilter('orderId')
                ->addFilter('name')
                ->addFilter('created', array('class'=>'yd-datepicker-default'));
        $grid->addFilters($filters);

        return $grid;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.06.2012
     */
    public function keywordsAction() {

        $form = new Yourdelivery_Form_Administration_Blacklist_Keyword();
        $this->view->form = $form;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if ($form->isValid($post)) {
                $values = $form->getValues();
                $blacklist = new Yourdelivery_Model_Support_Blacklist();
                $blacklist->setAdminId($this->session->admin->getId());
                $blacklist->setComment($values['comment']);
                $blacklist->setOrderId($values['orderId']);
                $blacklist->addValue($values['type'], $values['value'], $values['matching']);
                $blacklist->save();

                if ($values['cancelOrder'] && $values['orderId']) {
                    $this->_cancelOrder($values['orderId']);
                }

                // is called as ajax request as well in the backend
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $this->_disableView();
                    return $this->getResponse()
                                    ->setHttpResponseCode(201);
                }

                return $this->_redirect('/administration_blacklist/keywords');
            }

            // is called as ajax request as well in the backend
            if ($this->getRequest()->isXmlHttpRequest()) {
                $this->_disableView();
                echo json_encode($form->getMessages());
                return $this->getResponse()
                                ->setHttpResponseCode(406);
            }

            $this->error($form->getMessages());
        }

        $grid = $this->_getGrid(
                Yourdelivery_Model_Support_Blacklist::getTypes('KEYWORDS')
        );

        $grid->updateColumn("matching", array('title' => __b('Vergleich'), 'callback' => array('function' => 'gridBlacklistMatchings', 'params' => array('{{matching}}'))));
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')
                ->name(__b('Optionen'))
                ->callback(array('function' => 'gridBlacklistOptions', 'params' => array('{{valueId}}')));
        $grid->addExtraColumns($option);
        $this->view->grid = $grid->deploy();
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.06.2012
     */
    public function emailAction() {

        $form = new Yourdelivery_Form_Administration_Blacklist_Email();
        $this->view->form = $form;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if ($form->isValid($post)) {
                $blacklist = new Yourdelivery_Model_Support_Blacklist();
                $blacklist->setAdminId($this->session->admin->getId());
                $blacklist->setComment($form->getValue('bl_comment'));
                $blacklist->setOrderId($form->getValue('bl_orderId'));
                $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL, $form->getValue('bl_email'));

                if ($form->getValue('bl_minutemailer') == 1) {
                    $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL_MINUTEMAILER, $form->getValue('bl_email'));
                }

                $blacklist->save();

                if ($form->getValue('bl_cancelorder') && $form->getValue('bl_orderId')) {
                    $this->_cancelOrder($form->getValue('bl_orderId'));
                }

                // is called as ajax request as well in the backend
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $this->_disableView();
                    return $this->getResponse()
                                    ->setHttpResponseCode(201);
                }


                return $this->_redirect('/administration_blacklist/email');
            }

            // is called as ajax request as well in the backend
            if ($this->getRequest()->isXmlHttpRequest()) {
                $this->_disableView();
                echo json_encode($form->getMessages());
                return $this->getResponse()
                                ->setHttpResponseCode(406);
            }

            $this->error($form->getMessages());
        }

        $grid = $this->_getGrid(
                Yourdelivery_Model_Support_Blacklist::getTypes('EMAIL')
        );
        $grid->updateColumn("matching", array('hidden' => 1));

        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')
                ->name(__b('Optionen'))
                ->callback(array('function' => 'gridBlacklistOptions', 'params' => array('{{valueId}}')));
        $grid->addExtraColumns($option);
        $this->view->grid = $grid->deploy();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since14.06.2012
     * @return type
     */
    public function paypalAction() {

        $form = new Yourdelivery_Form_Administration_Blacklist_Paypal();
        $this->view->form = $form;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            if ($form->isValid($post)) {
                $blacklist = new Yourdelivery_Model_Support_Blacklist();
                $blacklist->setAdminId($this->session->admin->getId());
                $blacklist->setComment($form->getValue('bl_comment'));
                $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_EMAIL, $form->getValue('bl_paypal_email'), Yourdelivery_Model_Support_Blacklist::MATCHING_EXACT, $form->getValue('bl_behaviour'));
                $blacklist->addValue(Yourdelivery_Model_Support_Blacklist::TYPE_PAYPAL_PAYERID, $form->getValue('bl_payerId'), Yourdelivery_Model_Support_Blacklist::MATCHING_EXACT, $form->getValue('bl_behaviour'));
                $blacklist->setOrderId($form->getValue('bl_orderId'));

                $blacklist->save();

                if ($form->getValue('bl_cancelorder') && $form->getValue('bl_orderId')) {
                    $this->_cancelOrder($form->getValue('bl_orderId'));
                }

                // is called as ajax request as well in the backend
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $this->_disableView();
                    return $this->getResponse()
                                    ->setHttpResponseCode(201);
                }

                return $this->_redirect('/administration_blacklist/paypal');
            }

            // is called as ajax request as well in the backend
            if ($this->getRequest()->isXmlHttpRequest()) {
                $this->_disableView();
                echo json_encode($form->getMessages());
                return $this->getResponse()
                                ->setHttpResponseCode(406);
            }


            $this->error($form->getMessages());
        }

        $grid = $this->_getGrid(
                Yourdelivery_Model_Support_Blacklist::getTypes('PAYPAL')
        );
        $grid->updateColumn("matching", array('hidden' => 1));
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')
                ->name(__b('Optionen'))
                ->callback(array('function' => 'gridBlacklistPaypalOptions', 'params' => array('{{valueId}}', '{{blacklistId}}')));
        $grid->addExtraColumns($option);
        $this->view->grid = $grid->deploy();
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 14.06.2012
     * TODO: add user notification
     */
    public function whitelistAction() {

        $request = $this->getRequest();
        $id = $request->getParam('id');

        try {
            $blacklist = new Yourdelivery_Model_Support_Blacklist($id);
            $blacklist->setBehaviour(Yourdelivery_Model_Support_Blacklist::BEHAVIOUR_WHITELIST);
            $blacklist->save();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

        }

        return $this->_redirect('/administration_blacklist/paypal');
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since19.06.2012
     */
    protected function _cancelOrder($orderId, $comment) {

        try {
            $order = new Yourdelivery_Model_Order($orderId);
            $this->logger->adminInfo(sprintf("Order %d blacklisted by Supporter %s", $this->id, $this->session_admin->admin->getName()));
            $order->setStatus(Yourdelivery_Model_Order::FAKE_STORNO,
                    new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::ORDER_BLACKLIST, $comment, $this->session_admin->admin->getName()));                    
            $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::ORDER_FAKE, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_ORDER, $orderId);

            $payment = $order->getPayment();

            $messages = array();
            $messages[] = __b("Bestellung wurde geblacklistet");

            // refund paypal transaction
            if ($payment == 'paypal') {
                Yourdelivery_Helpers_Payment::refundPaypal($order, $this->logger, $messages);
                // refund ebanking transaction
            } elseif ($payment == 'ebanking') {
                Yourdelivery_Helpers_Payment::refundEbanking($order, $this->logger, $messages, __b("storniert von %s", $this->session_admin->admin->getName()));
                // refund credit transaction
            } elseif ($payment == 'credit') {
                Yourdelivery_Helpers_Payment::refundCredit($order, $this->logger, $messages);
            }
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

        }
    }

}

