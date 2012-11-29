<?php

/**
 * Description of Administration
 * @author mlaug
 */
class Request_AdministrationController extends Default_Controller_RequestAdministrationBase {

    /**
     * transactions overview for fidelity points
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 12.07.2011
     * @return string $html | null
     */
    public function fidelitytransactionsAction() {
        $request = $this->getRequest();

        $custId = $request->getParam('custId', null);
        if (is_null($custId)) {
            return null;
        }

        try {
            $cust = new Yourdelivery_Model_Customer($custId);
            $html = $this->view->fetch('request/administration/fidelitytransactions.htm');
            $this->view->assign('transactions', Yourdelivery_Model_DbTable_Customer_FidelityTransaction::findAllByEmail($cust->getEmail()));
            $this->view->assign('customer', $cust);
            return $html;
        } catch (Yourdelivery_Exception_Database_Inconsistency $exc) {
            $this->error('Could not find customer with id #' . $custId);
            return null;
        }
    }

    public function checkdirectlinkAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $link = $this->getRequest()->getParam('link', null);
        $id = $this->getRequest()->getParam('id', null);

        if (is_null($link)) {
            echo '<span>Link empty, service will not be available for orders in this type</span>';
        }

        if (preg_match('(^/)', $link)) {
            $link = substr($link, 1);
        }

        $result = Yourdelivery_Model_DbTable_Restaurant::findByDirectLink($link);
        if ($result) {
            if ($result[1]['id'] == $id) {
                echo '<span>OK</span>';
            } else {
                echo '<span style="color:red;font-weight:bold;">Link already assigned to another service</span>';
            }
        } else {
            echo '<span>OK</span>';
        }
    }

    public function blacklistAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $blacklist = $this->getRequest()->getParam('blacklist', null);
        /* @deprecated BLACKLIST */
        $filename = BLACKLIST;
        $fp = fopen($filename, 'w+');
        fputs($fp, $blacklist);
        fclose($fp);
    }

    public function supportAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $id = $this->getRequest()->getParam('support', null);
        if (!is_null($id)) {
            try {
                $support = new Yourdelivery_Model_Support($id);
                $support->setActive(!$support->getActive());
                $support->save();
            } catch (Yourdelivery_Exception_AlreadyFinished $e) {
                return;
            }
        }
    }

    public function removesupportAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();
        $id = $request->getParam('support', null);
        if ($request->isPost()) {
            $number = $request->getParam('support', null);
            if (!empty($number) && !is_null($number)) {
                try {
                    $support = new Yourdelivery_Model_Support($id);
                    $support->getTable()->getCurrent()->delete();
                    echo 1;
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    echo 0;
                }
            }
        }
    }

    public function addsupportAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $number = $request->getParam('support', null);
            $name = $this->getRequest()->getParam('name', null);
            if (!empty($number) && !is_null($number)) {
                $support = new Yourdelivery_Model_Support();
                $support->setNumber($number);
                $support->setName($name);
                $support->save();
                $this->view->number = $number;
                $this->view->name = $name;
                $this->view->id = $support->getId();
            } else {
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            }
        } else {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        }
    }

    public function reservebillingnumberAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
            $mode = $post['type'];
            $refId = $post['refId'];

            if (strlen($refId) == 0) {
                return;
            }

            $from = $post['from'];
            $until = $post['until'];

            $dateFrom = substr($from, 6, 4) . "-" . substr($from, 3, 2) . "-" . substr($from, 0, 2) . " 00:00:00";
            $dateUntil = substr($until, 6, 4) . "-" . substr($until, 3, 2) . "-" . substr($until, 0, 2) . " 23:59:59";

            $billing = new Yourdelivery_Model_DbTable_Billing();
            $number = $billing->getNextBillingNumber();

            $row = $billing->createRow();
            $row->mode = $mode;
            $row->refId = $refId;
            $row->from = $dateFrom;
            $row->until = $dateUntil;

            $fullNumber = null;
            if (strcmp($mode, 'company') == 0) {
                try {
                    $company = new Yourdelivery_Model_Company($refId);
                    $fullNumber = "R-" . substr($from, 8, 2) . substr($from, 3, 2) . "-" . $company->getCustomerNr() . "-" . $number;
                    $row->number = $fullNumber;
                } catch (Yourdelivery_Exception_DatabaseInconsistency $e) {
                    
                }
            } else if (strcmp($mode, 'rest') == 0) {
                try {
                    $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($refId);
                    $fullNumber = "R-" . substr($from, 8, 2) . substr($from, 3, 2) . "-" . $restaurant->getCustomerNr() . "-" . $number;
                    $row->number = $fullNumber;
                } catch (Yourdelivery_Exception_DatabaseInconsistency $e) {
                    
                }
            } else if (strcmp($mode, 'courier') == 0) {
                try {
                    $courier = new Yourdelivery_Model_Courier($refId);
                    $fullNumber = "R-" . substr($from, 8, 2) . substr($from, 3, 2) . "-" . $courier->getCustomerNr() . "-" . $number;
                    $row->number = $fullNumber;
                } catch (Yourdelivery_Exception_DatabaseInconsistency $e) {
                    
                }
            }
            $id = $row->save();

            //send as response the reserved number and next number, formatted as string
            echo $fullNumber . ':' . $number . '-' . str_pad(strval($number + 1), strlen($number), "0", STR_PAD_LEFT);
        }
    }

    /**
     * add official holiday
     * @author alex
     * @since 02.12.2010
     */
    public function addholidayAction() {
        // disable view
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $day = $post['day'];
            $name = $post['name'];
            $states = $post['yd-state'];

            // split day from european format to array
            $dayParts = explode(".", $day);
            if (count($dayParts) != 3) {
                return;
            }

            // for each marked federal land, save the day and the land id
            if (isset($states)) {
                foreach ($states as $stateId => $val) {
                    // convert array of day parts to sql date format
                    $dateSql = implode("-", array_reverse($dayParts));

                    if (!Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday::isHoliday($dateSql, $stateId)) {
                        $holiday = new Yourdelivery_Model_Servicetype_OpeningsHolidays();
                        $holiday->setName($name);
                        $holiday->setDate($dateSql);
                        $holiday->setStateId($stateId);
                        $holiday->save();
                    }
                }
            }
        }
    }

    /**
     * get all holidays
     * @author alex
     * @since 02.12.2010
     */
    public function getholidaysAction() {
        $result = Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday::getHolidays();

        // save results as array of {data=>{array of lands}}
        $holidays = array();
        foreach ($result as $r) {
            if (!array_key_exists($r['date'], $holidays)) {
                $holidays[$r['date']] = array();
            }

            if (!array_key_exists($r['date'], $holiday_names)) {
                $holiday_names[$r['date']] = $r['name'];
            }
            $holidays[$r['date']][$r['stateId']] = $r['id'];
        }

        $this->view->holidays = $holidays;
        $this->view->holiday_names = $holiday_names;
        $this->view->states = Yourdelivery_Model_DbTable_City::getAllStates();
    }

    /**
     * remove official holiday
     * @author alex
     * @since 02.12.2010
     */
    public function removeholidayAction() {
        // disable view
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $id = $post['id'];
            if (!is_null($id)) {
                Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday::remove($id);
            }
        }
    }

    /**
     * remove official holiday by date
     * @author alex
     * @since 02.12.2010
     */
    public function removeholidaydateAction() {
        // disable view
        $this->_helper->viewRenderer->setNoRender(true);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
            $date = $post['date'];
            if (!is_null($date)) {
                Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday::removeByDate($date);
            }
        }
    }

    /**
     * Show lightbox with discount codes for the selected discount action
     * @author Alex Vait <vait@lieferando.de>
     * @since 18.01.2012
     */
    public function discountAction() {
        $id = $this->getRequest()->getParam('id', null);

        if (!is_null($id)) {
            try {
                $discount = new Yourdelivery_Model_Rabatt($id);
                $this->view->discount = $discount;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->view->discount = null;
            }
        }
    }

    /**
     * Show lightbox with registration codes for the selected discount action
     * @author Alex Vait <vait@lieferando.de>
     * @since 18.01.2012
     */
    public function registrationcodesAction() {
        $id = $this->getRequest()->getParam('id', null);

        if (!is_null($id)) {
            try {
                $discount = new Yourdelivery_Model_Rabatt($id);
                $this->view->discount = $discount;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->view->discount = null;
            }
        }
    }

    /**
     * Return the count of codes in this discount action
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 23.11.2011
     * @return string JSON encoded object
     */
    public function getcodescountAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $id = $this->getRequest()->getParam('id', null);
        if (!is_null($id)) {
            $count = Yourdelivery_Model_DbTable_RabattCodes::getCodesCount($id);

            echo Zend_Json::encode(array(
                'count' => intval($count)
            ));
            return;
        }
    }

    /**
     * Return the count of registration codes in this discount action
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 17.01.2012
     * @return string JSON encoded object
     */
    public function getregistrationcodescountAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $id = $this->getRequest()->getParam('id', null);
        if (!is_null($id)) {
            $count = Yourdelivery_Model_DbTable_RabattCodesVerification::getCodesCount($id);

            echo Zend_Json::encode(array(
                'count' => intval($count)
            ));
            return;
        }
    }

    /**
     * Lightbox for resending the discount code
     */
    public function discountcodeAction() {
        $request = $this->getRequest();

        $id = $request->getParam('id', null);
        if (!is_null($id)) {
            try {
                $code = new Yourdelivery_Model_Rabatt_Code(null, $id);
                $check = new Yourdelivery_Model_DbTable_RabattCheck();
                $this->view->code = $code;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->view->code = null;
                $this->view->lightboxstate = 1;
                return;
            }
        }

        $this->view->lightboxstate = 0;
        $action = $request->getParam('per');
        if (!is_null($action)) {
            $receiver = $request->getParam('receiver');

            switch ($action) {
                // resend code per telephone
                case 'tel':
                    // TODO Send per SMS
                    // Number : $receiver
                    $gateway = $request->getParam('gateway');

                    $sms = new Yourdelivery_Sender_Sms();
                    $sms->send($receiver, __("Ihr Gutscheincode von %s: %s", $config->domain->base, $code->getCode()), $gateway);

                    $oldTel = $code->getTel();

                    try {
                        $row = $check->fetchRow('tel=' . $oldTel);
                        $check->update(array('tel' => $receiver), 'id=' . $row['id']);
                    } catch (Exception $e) {
                        // if this phone number is not found in the rabatt_check table
                    }

                    $code->setTel($receiver);
                    $code->save();

                    $this->logger->adminInfo(sprintf('Discount code %d was resend per SMS to number %s', $id, $receiver));
                    if (strcmp($oldTel, $receiver) != 0) {
                        $this->logger->adminInfo(sprintf('Phone number was changed from %s to %s while sending discount code %d', $oldTel, $receiver, $id));
                    }

                    $this->view->message = "Rabattcode wurde erfolgreich per SMS verschickt";
                    $this->view->lightboxstate = 1;
                    break;

                case 'email':
                    // TODO Send per E-Mail
                    // Email: $receiver

                    $oldEmail = $code->getEmail();
                    $code->setEmail($receiver);
                    $code->save();

                    $this->logger->adminInfo(sprintf('Discount code %d was resend per E-Mail to %s', $id, $receiver));
                    if (strcmp($oldEmail, $receiver) != 0) {
                        $this->logger->adminInfo(sprintf('E-Mail was changed from %s to %s while sending discount code %d', $oldEmail, $receiver, $id));
                    }

                    $this->view->message = "Rabattcode wurde erfolgreich per E-Mail verschickt";
                    $this->view->lightboxstate = 1;
                    break;

                default:
                    $this->view->message = "Unknown action :(";
                    $this->view->lightboxstate = 1;
                    break;
            }
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.09.2011
     *
     * show all comments, not used right now
     * @return void
     */
    public function ordercommentsAction() {
        $orderId = $this->getRequest()->getParam('id', null);

        if ($orderId) {
            try {
                $order = new Yourdelivery_Model_Order($orderId);

                $this->view->comments = $order->getTable()->getStateHistory();
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                $this->getResponse()->setHttpResponseCode(404);
                return;
            }
        }
    }

    /**
     * Edit city seo text
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     *
     * @return void
     */
    public function citySeoTextAction() {
        $request = $this->getRequest();

        $id = $request->getParam('id');
        try {
            $city = new Yourdelivery_Model_City($id);
            $this->view->city = $city;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }
        if ($request->isPost()) {
            // print only json
            $this->_helper->viewRenderer->setNoRender(true);

            $post = $request->getPost();
            $form = new Yourdelivery_Form_Administration_City_SeoText();
            if ($form->isValid($post)) {
                $values = $form->getValues();
                $city->setData($values);
                $city->save();
                
                $values['success'] = true;
            } else {
                $values = array('success' => false, 'message' => implode(",", $form->getMessages()));
            }
            echo Zend_Json::encode($values); 
        }
    }

    /**
     * Edit region seo text
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     *
     * @return void
     */
    public function regionSeoTextAction() {
        $request = $this->getRequest();

        $id = $request->getParam('id');
        
        try {
            $tableModel = new Yourdelivery_Model_DbTable_Regions();
            $rowSet = $tableModel->find($id);
            $row = $rowSet->current();
            $this->view->region = $row;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }
        if ($request->isPost()) {
            // print only json
            $this->_helper->viewRenderer->setNoRender(true);

            $post = $request->getPost();
            $form = new Yourdelivery_Form_Administration_Region_SeoText();
            if ($form->isValid($post)) {
                $values = $form->getValues();
                foreach($values as $column => $value) {
                    if ($column != 'id') {
                        $row->$column = $value;
                    }
                }
                $row->save();
                
                $values['success'] = true;
            } else {
                $values = array('success' => false, 'message' => implode(",", $form->getMessages()));
            }
            echo Zend_Json::encode($values); 
        }
    }

    /**
     * Edit district seo text
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     *
     * @return void
     */
    public function districtSeoTextAction() {
        $request = $this->getRequest();

        $id = $request->getParam('id');
        
        try {
            $tableModel = new Yourdelivery_Model_DbTable_Districts();
            $rowSet = $tableModel->find($id);
            $row = $rowSet->current();
            $this->view->district = $row;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }
        if ($request->isPost()) {
            // print only json
            $this->_helper->viewRenderer->setNoRender(true);

            $post = $request->getPost();
            $form = new Yourdelivery_Form_Administration_District_SeoText();
            if ($form->isValid($post)) {
                $values = $form->getValues();
                foreach($values as $column => $value) {
                    if ($column != 'id') {
                        $row->$column = $value;
                    }
                }
                $row->save();
                
                $values['success'] = true;
            } else {
                $values = array('success' => false, 'message' => implode(",", $form->getMessages()));
            }
            echo Zend_Json::encode($values); 
        }
    }

    /*
     * Lightbox with help information to this order
     * @author alex
     * @since 13.10.2010
     * @return void
     */

    public function orderhelpAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id');

        if (!is_null($id)) {
            try {
                $order = new Yourdelivery_Model_Order($id);
                $this->view->order = $order;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
    }

    /*
     * Lightbox with information to this order
     * @author alex
     * @since 28.02.2011
     * @return void
     */

    public function orderinfoAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id');

        if (!is_null($id)) {
            try {
                $order = new Yourdelivery_Model_Order($id);
                $this->view->order = $order;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        }
    }

    /**
     * Lightbox for editing prompt data
     * @author vpriem
     * @since 29.09.2010
     */
    public function promptAction() {
        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $id = $request->getParam('orderId');
            try {
                $order = new Yourdelivery_Model_Order($id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return;
            }
            if (!$order->getService()->hasPromptCourier()) {
                return;
            }

            $post = $request->getPost();
            $form = new Yourdelivery_Form_Administration_Order_Courier_Prompt();
            if ($form->isValid($post)) {
                $values = $form->getValues();
                $location = $order->getLocation();
                $location->setStreet($values['street']);
                $location->setHausnr($values['hausnr']);
                $location->setPlz($values['plz']);

                if (hook_after_fax_is_ok($order)) {
                    $this->view->msg = "Die Bestellung wurde erfolgreich an Prompt übermitteln";
                    $order->changeLocation($location);
                } else {
                    $this->view->msg = "Die Bestellung konnte nicht an Prompt übermitteln werden, prüfen Sie bitte der Anschrift";
                }
            } else {
                $this->view->msg = implode(",", $form->getMessages());
            }
        } else { // get
            $id = $request->getParam('id');
        }

        if ($id === null) {
            return;
        }

        // get id
        try {
            $order = new Yourdelivery_Model_Order($id);
            $this->view->order = $order;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return;
        }

        // courier
        if (!$order->getService()->hasPromptCourier()) {
            return;
        }

        $table = new Yourdelivery_Model_DbTable_Prompt_Transactions();
        $this->view->transactions = $table->getByOrder($order->getId());

        $table = new Yourdelivery_Model_DbTable_Prompt_Tracking();
        $this->view->trackings = $table->getByOrder($order->getId());
    }

    /*
     * Lightbox for editing rabatt code
     * @author alex
     * @since 28.09.2010
     * @return void
     */

    public function rabattcodeeditAction() {
        $request = $this->getRequest();
        $codeId = $request->getParam('id', null);
        $command = $request->getParam('command', null);
        $orderId = $request->getParam('order', null);
        
        if (!is_null($codeId)) {
            try {
                $code = new Yourdelivery_Model_Rabatt_Code(null, $codeId);
                $this->view->code = $code;
                $this->view->orderId = $orderId;
                $this->view->rabatt = $code->getParent();
                $this->view->lightboxstate = 0;
                $this->view->discountTypes = $code->getParent()->getDiscountTypes();              
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->view->order = null;
                $this->view->lightboxstate = -1;
                return;
            }
        }

        // reset rabatt code
        if (strcmp($command, "reset") == 0) {
            try {
                
                $code->setCodeUnused();
                $this->view->message = "Der Gutschein wurde zurückgesetzt";
                $this->logger->adminInfo(sprintf('Successfully reset %d', $code->getId()));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->adminErr(sprintf('Could not resett rabatt code %d', $code->getId()));
                $this->view->message = "Konnte den Gutschein " . $code->getId() . " nicht zurücksetzen";
            }
            $this->view->lightboxstate = 1;
        }
    }

    /**
     * Detailed statistics per PLZ
     * @author alex
     */
    protected function calculateRestaurantsByTypePlz($type) {
        $restaurants = Yourdelivery_Model_DbTable_Restaurant::getRestaurantsByType($type);

        $restCountByPlz = array(
            '0' => 0,
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0,
            '6' => 0,
            '7' => 0,
            '8' => 0,
            '9' => 0,
        );

        foreach ($restaurants as $r) {
            $restCountByPlz[$r->plz]++;
        }
        return $restCountByPlz;
    }

    /**
     * Delete a picture from picture category
     * @author Alex Vait <vait@lieferando.de>
     * @modified 07.12.2011
     */
    public function deletecatpicAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $pic = $this->getRequest()->getParam('pic', null);
        $id = $this->getRequest()->getParam('id', null);

        if (is_null($pic) || is_null($id)) {
            echo 0;
        }

        $stat = false;
        $stat = unlink(APPLICATION_PATH . $pic);

        // set new picture for all associated meal categories
        try {
            $cat = new Yourdelivery_Model_Category_Picture($id);
            $cat->updateAssociatedCategories();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }

        if ($stat == true) {
            echo 1;
        } else {
            echo 0;
        }
    }

    public function trackingcodedetailsAction() {
        $id = $this->getRequest()->getParam('id', null);
        try {
            $code = new Yourdelivery_Model_Tracking_Code($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }
        $this->view->assign('code', $code);
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function deletemealfromorderAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $orderId = $this->getRequest()->getParam('orderId', null);
        $bucketId = $this->getRequest()->getParam('bucketId', null);

        if (is_null($orderId) || is_null($bucketId)) {
            return null;
        }

        $order = null;
        try {
            $order = new Yourdelivery_Model_Order($orderId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }

        // delete options
        // delete extras
        // delete from bucket
        $order->deleteMeal(new Yourdelivery_Model_Order_BucketMeals($bucketId));
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function deleteoptionfrommealAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $bucketId = $this->getRequest()->getParam('bucketId', null);
        $optionId = $this->getRequest()->getParam('optionId', null);

        if (is_null($bucketId) || is_null($optionId)) {
            return null;
        }

        $meal = null;
        try {
            $meal = new Yourdelivery_Model_Order_BucketMeals($bucketId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }

        if (!$meal->deleteOption($optionId)) {
            return null;
        }
        echo 0;
    }

    /**
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     */
    public function deleteextrafrommealAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $bucketId = $this->getRequest()->getParam('bucketId', null);
        $extraId = $this->getRequest()->getParam('extraId', null);

        if (is_null($bucketId) || is_null($extraId)) {
            return null;
        }

        $meal = null;
        try {
            $meal = new Yourdelivery_Model_Order_BucketMeals($bucketId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }

        if (!$meal->deleteExtra($extraId)) {
            return null;
        }
        echo 0;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 2010-08-03
     */
    public function removefidelitypointAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $custId = $this->getRequest()->getParam('custId', null);
        $transaction = $this->getRequest()->getParam('transaction', 1);

        if (!is_null($custId)) {
            $cust = null;
            try {
                $cust = new Yourdelivery_Model_Customer($custId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return null;
            }
            echo $cust->removeFidelityPoint($transaction);
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 2010-08-03
     */
    public function addfidelitypointAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $custId = $this->getRequest()->getParam('custId', null);
        $count = $this->getRequest()->getParam('count', 1);
        $comment = $this->getRequest()->getParam('comment', 'manually added at AdminBackend');

        if (!is_null($custId)) {
            $cust = null;
            try {
                $cust = new Yourdelivery_Model_Customer($custId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return null;
            }
            echo $cust->addFidelityPoint('manual', $comment, $count);
        }
    }

    public function downloadsentbillAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $request = $this->getRequest();
        $id = $request->getParam('id', null);
        if (is_null($id)) {
            return;
        }

        $table = new Yourdelivery_Model_DbTable_Billing_Sent();
        $row = $table->find($id)->current();
        ;
        if ($row) {
            try {
                $bill = new Yourdelivery_Model_Billing($row['billingId']);
                $pdf = APPLICATION_PATH . '/../storage/billing/sendOut/' .
                        $row['billingId'] . '/' .
                        date('d-m-Y-', strtotime($row['on'])) . $row['id'] . '/' .
                        $bill->getNumber() . '.pdf';

                echo Default_Helper::makeRelative($pdf);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return;
            }
        }
    }

    /**
     * @todo YD-1904
     * @since 07.08.2010
     * @author mlaug
     */
    public function mahnungAction() {
        if ($this->getRequest()->isPost()) {
            $config = Zend_Registry::get('configuration');

            $htmlHead = '<h1>Mahnung erstellen</h1><br /><br /><form action="" style="padding:5px;" method="post"><p>';
            $htmlFoot = '</p></form><br />';

            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            $bills = $this->getRequest()->getParam('bills', array());
            $text = $this->getRequest()->getParam('text', null);
            $heading = $this->getRequest()->getParam('heading', 'Zahlungserinnerung');
            $steps = $this->getRequest()->getParam('steps', array());
            $reminder = $this->getRequest()->getParam('reminder', 14);

            if (!is_array($bills)) {
                echo $htmlHead . 'Keine Rechnungen angegeben.' . $htmlFoot;
                return;
            }

            $bills = array_filter(
                    $bills, function($b) {
                        return $b >= 0 ? true : false;
                    }
            );

            if (count($bills) == 0) {
                echo $htmlHead . 'Keine Rechnungen angegeben.' . $htmlFoot;
                return;
            }

            //create object
            $admonation = new Yourdelivery_Model_Billing_Admonation();
            $admonation->setBills($bills, $steps);
            $admonation->setConfig($config);
            $admonation->setHeading($heading);
            $admonation->setText($text);
            $admonation->setReminder($reminder);
            //create pdf
            if ($admonation->create()) {
                echo $htmlHead . 'Mahnung erfolgreich erstellt.' . $htmlFoot;
            } else {
                echo $htmlHead . 'Mahnung konnte leider nicht erfolgreich erstellt werden.' . $htmlFoot;
            }
            return;
        } else {

            $billId = $this->getRequest()->getParam('id', null);
            try {
                $bill = new Yourdelivery_Model_Billing($billId);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
                echo $htmlHead . 'Mahnung konnte nicht erstellt werden: Rechnung wurde nicht gefunden.' . $htmlFoot;
                return;
            }
            $billings = $bill->getObject()->getBillings();
            $this->view->billings = $billings;
        }
    }

    /**
     * search for meal and category to replace
     * @author mlaug
     * @since 31.08.2010
     */
    public function searchreplaceAction() {

        // print only json
        $this->_helper->viewRenderer->setNoRender(true);
        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();

            if (empty($post['search'])) {
                echo Zend_Json::encode(array(
                    'error' => "BITTE EINE EINGABE MACHEN"
                ));
                return;
            }

            // replace
            if (isset($post['_replace'])) {
                if (empty($post['replace'])) {
                    echo Zend_Json::encode(array(
                        'error' => "BITTE EINE EINGABE MACHEN"
                    ));
                }

                $table = new Yourdelivery_Model_DbTable_Filters();
                $table->createRow(array(
                    'name' => "mealNameDescription",
                    'type' => "replace",
                    'search' => $post['search'],
                    'replace' => $post['replace'],
                ))->save();
                $table->createRow(array(
                    'name' => "mealCategoryNameDescription",
                    'type' => "replace",
                    'search' => $post['search'],
                    'replace' => $post['replace'],
                ))->save();

                echo Zend_Json::encode(array(
                    'success' => "Das Filter wurde erfolgreich gespeichert"
                ));
                return;
            }

            // search
            $meals = Yourdelivery_Model_DbTable_Meals::searchReplace($post['search']);
            foreach ($meals as &$meal) {
                $meal['name'] = str_replace($post['search'], '<b><em style="color:red">' . $post['search'] . '</em></b>', $meal['name']);
                $meal['description'] = str_replace($post['search'], '<b><em style="color:red">' . $post['search'] . '</em></b>', $meal['description']);
            }
            unset($meal);

            $categories = Yourdelivery_Model_DbTable_Meal_Categories::searchReplace($post['search']);
            foreach ($categories as &$category) {
                $category['name'] = str_replace($post['search'], '<b><em style="color:red">' . $post['search'] . '</em></b>', $category['name']);
                $category['description'] = str_replace($post['search'], '<b><em style="color:red">' . $post['search'] . '</em></b>', $category['description']);
            }
            unset($category);

            echo Zend_Json::encode(array(
                'meals' => $meals,
                'categories' => $categories,
            ));
        }
    }

    /**
     * change the name of tjhe discount code
     * @author alex
     * @since 08.10.2010
     */
    public function savediscountcodeAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

            //create code object
            try {
                $code = new Yourdelivery_Model_Rabatt_Code(null, $post['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                echo('Dieser Rabattcode existiert nicht!');
                return;
            }

            $code->setCode($post['name']);
            $code->save();
            echo 'ok';
        }
    }

    /**
     * test if this discoutn code already exists
     * @author alex
     * @since 08.10.2010
     */
    public function testdiscountcodeAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

            //create admin resource object
            $row = Yourdelivery_Model_DbTable_RabattCodes::findByCode($post['name']);
            if ($row === false) {
                echo 'ok';
            }
        }
    }

    /**
     * Toggle rating status
     * @author alex
     * @since 25.11.2010
     */
    public function toggleratingstatusAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            if (isset($post['ratingId'])) {
                try {
                    $rating = new Yourdelivery_Model_Servicetype_Rating($post['ratingId']);
                    $newState = !$rating->getStatus();
                    if ($newState) {
                        $rating->activate();
                    } else {
                        $rating->deactivate();
                    }

                    // clear cached ratings so the changes are seen on the yd site
                    $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($rating->getRestaurantId());
                    $restaurant->uncacheRating();

                    echo Zend_Json::encode(array('state' => $newState));
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->logger->debug($e->getMessage());
                }
            }
        }
    }

    /**
     * Delete rating, i.e. set status to -1
     * @author Alex Vait <vait@lieferando.de>
     * @since 10.02.2012
     */
    public function deleteratingAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            if (isset($post['ratingId'])) {
                try {
                    $rating = new Yourdelivery_Model_Servicetype_Rating($post['ratingId']);
                    $rating->delete();

                    $this->logger->adminInfo(sprintf('Successfully deleted rating %d', $rating->getId()));

                    // clear cached ratings so the changes are seen on the yd site
                    $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($rating->getRestaurantId());
                    $restaurant->uncacheRating();

                    echo Zend_Json::encode(array('state' => true));
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    echo Zend_Json::encode(array('error' => $e->getMessage()));
                    $this->logger->debug($e->getMessage());
                }
            }
        }
    }

    /**
     * Undele rating - set it's status to 0
     * @author Alex Vait <vait@lieferando.de>
     * @since 10.02.2012
     */
    public function undeleteratingAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            if (isset($post['ratingId'])) {
                try {
                    $rating = new Yourdelivery_Model_Servicetype_Rating($post['ratingId']);
                    $rating->setStatus(0);
                    $rating->save();

                    $this->logger->adminInfo(sprintf('Successfully undeleted rating %d', $rating->getId()));

                    // clear cached ratings so the changes are seen on the yd site
                    $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($rating->getRestaurantId());
                    $restaurant->uncacheRating();

                    echo Zend_Json::encode(array('state' => true));
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    echo Zend_Json::encode(array('error' => $e->getMessage()));
                    $this->logger->debug($e->getMessage());
                }
            }
        }
    }

    /**
     * Toggle rating status
     * @author alex
     * @since 25.11.2010
     */
    public function toggleratingtopAction() {

        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            if (isset($post['ratingId'])) {
                try {
                    $rating = new Yourdelivery_Model_Servicetype_Rating($post['ratingId']);
                    $newState = !($rating->getTopRating());
                    $rating->setTopRating($newState);
                    $rating->save();

                    // clear cached ratings so the changes are seen on the yd site
                    $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($rating->getRestaurantId());
                    $restaurant->uncacheRating();

                    echo Zend_Json::encode(array('state' => $newState));
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    
                }
            }
        }
    }

    /*
     * list of all budgets of this company if available to build a drop-down menu in view
     * @author alex
     * @since 10.01.2011
     */

    public function companybudgetsAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            if (strlen($post['companyId']) > 0) {
                try {
                    $company = new Yourdelivery_Model_Company($post['companyId']);
                    $this->view->assign('budgets', $company->getBudgets());
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    
                }
            }
        }
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 17.10.2011
     *
     * @return json
     */
    public function companyrestaurantassocAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $companyId = $this->getRequest()->getParam('companyId', null);
        $serviceListMode = $this->getRequest()->getParam('serviceListMode', null);
        if (is_null($companyId) || is_null($serviceListMode)) {
            echo Zend_Json::encode(array(
                'result' => false,
                'msg' => 'Nicht alle Parameter gegeben'
            ));
            return;
        }
        try {
            $company = new Yourdelivery_Model_Company($companyId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            echo Zend_Json::encode(array(
                'result' => false,
                'msg' => 'Konnte Firma nicht finden'
            ));
            return;
        }
        $company->setServiceListMode($serviceListMode);
        $company->save();

        echo Zend_Json::encode(array(
            'result' => true,
            'msg' => 'Firmenzuordnung erfolgreich gespeichert'
        ));
        $this->logger->adminInfo(sprintf('Successfully edited company/restaurant-association for company #%s %s', $company->getId(), $company->getName()));
        return;
    }

    /**
     * generate fax for the order
     * @author alex
     * @since 13.01.2011
     */
    public function generatefaxfororderAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $id = $this->getRequest()->getParam('id', null);
        try {
            $order = new Yourdelivery_Model_Order($id);
        } catch (Yourdelivery_Exception_DatabaseInconsistency $e) {
            $this->logger->adminErr(sprintf('Could not find order %d to generate fax', $id));
            echo Zend_Json::encode(array('error' => "Die Bestellung konnte nicht erstellt werden"));
            return;
        }
        if (!$order->generatePdf()) {
            echo Zend_Json::encode(array('error' => "Fax konnte nicht generiert werden"));
            return;
        }
        echo Zend_Json::encode(array('success' => "Fax wurde generiert"));
    }

    /**
     * test if this zip code is registered in the database
     */
    public function testzipAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $zip = $request->getParam('zip', null);
            $city = Yourdelivery_Model_City::getCityByZip($zip);

            // the zip  is found in the database
            if (!is_null($city)) {
                echo 1;
            }
            // the zip is not found in the database
            else {
                echo 0;
            }
        }
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
    }

    /**
     * get all districts for this zip code
     * @author alex
     * @since 01.03.2011
     * @deprecated (action + view, if exists)
     */
    public function getcitiesAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $plzind = $request->getParam('plzind', null);

            if (!is_null($plzind)) {
                $cities = Yourdelivery_Model_City::allStartingAt($plzind);
                $this->view->assign('cities', $cities);
            }
        }
    }

    /**
     * Sends autocomplete hints for passed term (if long enough), encoded as JSON
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 17.08.2012
     *
     * @return void
     */
    public function cityautocompleteAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $minLength = 2;
        $term = $this->getRequest()->getParam('term');
        $cities = array();

        if (strlen($term) >= $minLength) {
            try {
                $cities = Yourdelivery_Model_Autocomplete::plz(null, $term);
            } catch (Yourdelivery_Exception_Database_Inconsistency $ex) {
                // ignoring DB error
            }
        }

        echo Zend_Json::encode(array('cities' => $cities));
    }

    /**
     * Toggle paid/unpaid state of salespersons contracts
     * @author alex
     * @since 19.04.2011
     */
    public function togglesalespersonpaidAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            if (isset($post['contractId'])) {
                try {
                    $contract = new Yourdelivery_Model_Salesperson_Restaurant($post['contractId']);
                    $newState = !($contract->getPaid());
                    $contract->setPaid($newState);
                    $contract->save();

                    $this->logger->adminInfo(sprintf('Contract %d set %s', $contract->getId(), $newState ? 'online' : 'offline'));

                    echo Zend_Json::encode(array('state' => $newState));
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    
                }
            }
        }
    }

    /**
     * Change billing status
     * @author alex
     * @since 04.05.2011
     */
    public function setbillingstatusAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            if (isset($post['billingId']) && isset($post['status'])) {
                if (($post['status'] > 3) || ($post['status'] < 0)) {
                    echo Zend_Json::encode(array('error' => 'Unbekannter status ' . $post['status'] . ' bei der Rechnung ' . $post['billingId']));
                    return;
                }

                try {
                    $bill = new Yourdelivery_Model_Billing($post['billingId']);
                    $bill->setStatus($post['status']);
                    $bill->save();

                    $this->logger->adminInfo(sprintf('Status of billing #%d set to ds', $bill->getId(), $post['status']));
                    echo Zend_Json::encode(array('success' => 'ok'));
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    echo Zend_Json::encode(array('error' => 'Cannot create billing object with id ' . $post['billingId']));
                }
            } else {
                echo Zend_Json::encode(array('error' => 'Missing parameter'));
            }
        }
    }

    /**
     * Check if a css template with this name already exists
     * @author alex
     * @since 24.05.2011
     */
    public function checkcsstemplatenameAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();

            if (isset($post['templateName'])) {
                $templateName = $post['templateName'];

                if (file_exists(APPLICATION_PATH . "/../storage/satellites/css/color-" . $templateName . ".ini")) {
                    echo Zend_Json::encode(array('state' => 1));
                }
            }
        }
    }

    /**
     * test if this mail already exists in the contacts table
     * @author alex
     */
    public function testmailAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
            $email = $request->getParam('email', null);

            if (strlen(trim($email)) == 0) {
                echo 0;
                return;
            }

            $contactId = Yourdelivery_Model_Contact::getByEmail($email);
            $contact = new Yourdelivery_Model_Contact($contactId);

            // this email is already registered in contacts table
            if (($contact->getId() != 0) && ($contact->getDeleted() == 0)) {
                echo Zend_Json::encode(array("status" => "1", "code" => "<a href=\"/administration_contact/edit/id/" . $contact->getId() . "\">" . $contact->getPrename() . " " . $contact->getName() . "</a>"));
            }
            // this email is not registered in contacts table
            else {
                echo Zend_Json::encode(array("status" => "2"));
            }
        }
    }

    /**
     * get all crm reason for this department
     * @author alex
     * @since 14.07.2011
     */
    public function getcrmreasonsAction() {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $department = $request->getParam('department', null);

            if (!is_null($department)) {
                $reasons = Yourdelivery_Model_Crm_Ticket::getReasons($department);
                $this->view->assign('reasons', $reasons);
            }
        }
    }

}
