<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * discount management
 *
 * @author alex
 */
class Administration_DiscountController extends Default_Controller_AdministrationBase {

    public function preDispatch() {
        parent::preDispatch();
        $this->view->assign('navdiscounts', 'active');
    }

    /**
     * create discount
     * @author alex
     */
    public function createAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/discounts');
        }

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            //create new discount
            $form = new Yourdelivery_Form_Administration_Discount_Create();
            if ($form->isValid($post)) {
                $discount = new Yourdelivery_Model_Rabatt();
                $values = $form->getValues();

                $values['start'] = substr($values['startTimeD'], 6, 4) . "-" . substr($values['startTimeD'], 3, 2) . "-" . substr($values['startTimeD'], 0, 2) . " " . substr($values['startTimeT'], 0, 2) . ":" . substr($values['startTimeT'], 3, 2) . ":00";
                $values['end'] = substr($values['endTimeD'], 6, 4) . "-" . substr($values['endTimeD'], 3, 2) . "-" . substr($values['endTimeD'], 0, 2) . " " . substr($values['endTimeT'], 0, 2) . ":" . substr($values['endTimeT'], 3, 2) . ":00";

                if (($values['kind'] == 0) && ($values['rabatt'] > 100)) {
                    $this->error(sprintf(__b("Eine Rabattaktion kann nicht größer als 100%% sein. Rabatt wurde für 100%% erstellt"))); // use sprintf to escape %
                    $values['rabatt'] = 100;
                }

                if ($values['kind'] == 1) {
                    $values['rabatt'] = priceToInt2($values['rabatt']);
                }

                // ugly hack to make referer unique, solve it somehow
                if ($values['type'] == Yourdelivery_Model_Rabatt::TYPE_REGULAR ||
                        $values['type'] == Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_ONCE_PER_THIS_TYPE ||
                        $values['type'] == Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_ACTION ||
                        $values['type'] == Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_SINGLE_ACTION ||
                        $values['type'] == Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_SINGLE_ALL
                ) {
                    $values['referer'] = time() . '-' . $values['name'];
                }

                if ($values['rrepeat'] != 2) {
                    $values['countUsage'] = 0;
                }

                if (strlen(trim($values['referer'])) == 0) {
                    if (($values['type'] == Yourdelivery_Model_Rabatt::TYPE_LANDING_PAGE) ||
                            ($values['type'] == Yourdelivery_Model_Rabatt::TYPE_VERIFICATION_MANY) ||
                            ($values['type'] == Yourdelivery_Model_Rabatt::TYPE_LANDING_PAGE)
                            ) {
                        $this->error(sprintf(__b("Bitte definieren sie einen Referer für die Seite")));
                        return $this->_redirect('/administration_discount/create');
                    } else {
                        $values['referer'] = false;
                    }
                }

                if ($values['type'] == Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_SINGLE_ACTION) {
                    $values['rrepeat'] = 1;
                }


                if (($values['type'] == Yourdelivery_Model_Rabatt::TYPE_VERIFICATION_SINGLE) && (strlen(trim($values['fakeCode'])) == 0)) {
                    $this->error(sprintf(__b("Bitte definieren sie einen universellen Rabattcode für die Aktion")));
                    return $this->_redirect('/administration_discount/create');
                }

                $values['referer'] = str_replace("/", "", $values['referer']);

                $values['minAmount'] = priceToInt2($values['minAmount']);
                $values['noCash'] = 1;

                $restaurantIds = $values['restaurantIds'];
                unset($values['restaurantIds']);

                $cityIds = $values['cityIds'];
                unset($values['cityIds']);

                $discount->setData($values);
                $discount->save();
                $discount->setData(array('hash' => $discount->makeHash()));
                $discount->save();

                if(!is_null($restaurantIds)) {
                    $discount->setRestaurants($restaurantIds);
                }

                 if(!is_null($cityIds)) {
                    $discount->setCitys($cityIds);
                 }

                if (intval($values['number']) > 500 && $discount->getType() != Yourdelivery_Model_Rabatt::TYPE_VERIFICATION_SINGLE) {
                    if ($values['email'] == '') {
                        $values['email'] = 'it@lieferando.de';
                    }
                    $this->warn(__b('Du hast mehr als 500 Gutscheine angefordert. Das System wird diese im Hintergrund generieren und bei Vollendung eine email an %s schicken', $values['email']));
                    Yourdelivery_Model_DbTable_Rabatt_Jobs::createJob($discount, $values['email'], $values['number']);
                    $values['number'] = 1;
                }

                // upload image for he landing page
                if ($form->img->isUploaded()) {
                    $discount->setImg($form->img->getFileName());
                }

                // create the codes if new rabatt
                if ( intval($values['number']) <= 500) {
                    $number = (integer) $values['number'];
                    if (!$discount->generateCodes($number, trim($values['fakeCode']))) {
                        $this->error(__b('Code wird schon in anderer Aktion verwendet'));
                        return $this->_redirect('/administration/discounts');
                    }
                }

                $this->logger->adminInfo(sprintf("New discount %s (#%d) was created", $discount->getName(), $discount->getId()));

                $this->success(__b("Rabattaktion wurde erfolgreich erstellt"));
                $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::RABATT_CREATE, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_RABATT, $discount->getId());
                $this->_redirect('/administration/discounts');
            } else {
                $this->error($form->getMessages());
                $this->view->assign('p', $post);
            }
        } else {
            // show default time slot of one year
            $until = date('d.m.Y', time() + 365 * 24 * 60 * 60);
            $this->view->assign('until', $until);
        }
    }

    /**
     * edit discount
     * @author alex
     */
    public function editAction() {
        $request = $this->getRequest();

        if ($request->getParam('cancel') !== null) {
            return $this->_redirect('/administration/discounts');
        }

        if (is_null($request->getParam('id'))) {
            $this->error(__b("Diese Rabattaktion gibt es nicht!"));
            $this->_redirect('/administration/discounts');
        }

        //create discount object
        try {
            $discount = new Yourdelivery_Model_Rabatt($request->getParam('id'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diese Rabattaktion gibt es nicht!"));
            $this->_redirect('/administration/discounts');
        }

        if ($request->isPost()) {
            $post = $request->getPost();

            $form = new Yourdelivery_Form_Administration_Discount_Edit();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                // get the start/end timestamps
                $startTimeStr = $values['startTimeD'] . ' ' . $values['startTimeT'];
                $endTimeStr = $values['endTimeD'] . ' ' . $values['endTimeT'];

                $values['start'] = substr($startTimeStr, 6, 4) . "-" . substr($startTimeStr, 3, 2) . "-" . substr($startTimeStr, 0, 2) . " " . substr($startTimeStr, 11, 2) . ":" . substr($startTimeStr, 14, 2) . ":00";
                $values['end'] = substr($endTimeStr, 6, 4) . "-" . substr($endTimeStr, 3, 2) . "-" . substr($endTimeStr, 0, 2) . " " . substr($endTimeStr, 11, 2) . ":" . substr($endTimeStr, 14, 2) . ":00";

                if ($values['kind'] == 1) {
                    $values['rabatt'] = priceToInt2($values['rabatt']);
                } else {
                    if ($values['rabatt'] > 100) {
                        $this->error(sprintf(__b("Der Rabatt kann nicht größer als 100%% sein!"))); // use sprintf to escape %
                        $this->_redirect('/administration_discount/edit/id/' . $discount->getId());
                    }
                }

                $values['minAmount'] = priceToInt2($values['minAmount']);
                $values['referer'] = str_replace("/", "", $values['referer']);

                // ugly hack to make referer unique, solve it somehow
                if ($values['type'] == Yourdelivery_Model_Rabatt::TYPE_REGULAR) {
                    $values['referer'] = time() . '-' . $values['name'];
                }

                if ($values['type'] == Yourdelivery_Model_Rabatt::TYPE_VERIFCATION_SINGLE_ACTION) {
                    $values['rrepeat'] = 1;
                }

                //save new data
                $values['noCash'] = true;
                $discount->setData($values);
                $discount->save();

                $discount->setRestaurants($values['restaurantIds']);
                $discount->setCitys($values['cityIds']);
                // upload image for he landing page
                if ($form->img->isUploaded()) {
                    $discount->setImg($form->img->getFileName());
                }

                $this->logger->adminInfo(sprintf("Discount %s (%s) was edited", $discount->getName(), $discount->getId()));

                $this->success(__b("Änderungen erfolgreich gespeichert."));
                $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::RABATT_EDIT, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_RABATT, $discount->getId());
                $this->_redirect('/administration/discounts');
            } else {
                $this->error($form->getMessages());
                $this->_redirect('/administration_discount/edit/id/' . $discount->getId());
            }
        }

        // get the fake code for action of type 3
        if ($discount->getType() == 3) {
            $codes = Yourdelivery_Model_DbTable_RabattCodesVerification::findByRabattId($discount->getId());
            $this->view->assign('code', $codes[0]['registrationCode']);
        }
        if($discount->getType() == 6 || $discount->getType() == 7) {
            $code = Yourdelivery_Model_DbTable_RabattCodes::findByRabattId($discount->getId());
            $this->view->assign('code', $code['code']);
        }


        $this->view->assign('discount', $discount);
        $this->view->assign('types', Yourdelivery_Model_Rabatt::getDiscountTypes());
    }

    /**
     * delete discount
     * @author alex
     */
    public function deleteAction() {
        $request = $this->getRequest();

        if (is_null($request->getParam('id'))) {
            $this->error(__b("Diese Rabattaktion gibt es nicht!"));
            $this->_redirect('/administration/discounts');
        }

        $rabatt = new Yourdelivery_Model_Rabatt($request->getParam('id'));
        if (is_null($rabatt->getId())) {
            $this->error(__b("Diese Rabattaktion gibt es nicht!"));
        } else {
            $this->logger->adminInfo(sprintf("Deleting discount: %s (%s)", $rabatt->getName(), $rabatt->getId()));
            $rabatt->getTable()->remove($rabatt->getId());
            $this->logger->adminInfo(sprintf("Discount was deleted"));
        }

        $this->_redirect('/administration/discounts');
    }

    /**
     * show discounts table with only the group selected, to which this code belongs to
     * @author alex
     */
    public function discountbycodeAction() {
        $request = $this->getRequest();

        //create the discount code to get the discount it belongs to
        try {
            $code = new Yourdelivery_Model_Rabatt_Code($request->getParam('name', null), $request->getParam('id', null));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diesen Rabatt Code gibt es nicht!"));
            $this->_redirect('/administration/discounts');
        }

        // redirect to the discounts table where only the discount action containing this code is selected
        $discountId = $code->getRabattId();
        $this->_redirect('/administration/discounts/IDgrid/' . $discountId);
    }

    /**
     * check if this discount is usable and if not, show the reason
     * @since 27.01.2011
     * @author alex
     */
    public function checkdiscountcodeAction() {

        $request = $this->getRequest();

        $codeId = (integer) $request->getParam('codeId', null);
        $code = $request->getParam('code', '');

        if ($codeId > 0 || strlen($code) > 0) {
            //create discount code object
            try {
                $this->view->code = $codeObj = new Yourdelivery_Model_Rabatt_Code($code, $codeId);
                $this->view->types = $codeObj->getParent()->getDiscountTypes();
                $this->view->grid = Default_Helpers_Grid::generateOrderGrid('view_grid_orders', array('rabattCodeId' => $codeObj->getId()))->deploy();
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("Diesen Gutscheincode gibt es nicht!"));
                $this->logger->warn(sprintf('ADMIN: could not find discount %s/%s to check', $code, $codeId));
                $this->_redirect('/administration_discount/checkdiscountcode/');
            }
        }
    }

    /**
     * check the orders made with this registration code
     * @since 19.06.2012
     * @author Alex Vait <vait@lieferando.de>
     */
    public function checkregistrationcodeAction() {
        $request = $this->getRequest();

        $registrationcode = $request->getParam('registrationcode', '');

        if (strlen($registrationcode) > 0) {
            //create registration code object

            $regCodeId = Yourdelivery_Model_DbTable_RabattCodesVerification::findByCode($registrationcode);

            if (!is_array($regCodeId) || count($regCodeId) == 0) {
                $this->error(__b("Diesen Registrierungscode gibt es nicht!"));
                $this->_redirect('/administration_discount/checkregistrationcode/');
            }

            try {
                $registrationCodeObj = new Yourdelivery_Model_Rabatt_CodesVerification($regCodeId['id']);
                $this->view->registrationcode = $registrationCodeObj;
                $this->view->types = $registrationCodeObj->getParent()->getDiscountTypes();

                // build grid
                $db = Zend_Registry::get('dbAdapter');
                $select = $db
                        ->select()
                        ->from(array('o' => 'orders'), array(
                            __b('ID') => 'o.id',
                            __b('Eingang') => new Zend_Db_Expr("DATE_FORMAT(o.time, '%d.%m.%Y %H:%i')"),
                            __b('Preis') => new Zend_Db_Expr('o.total + o.serviceDeliverCost + o.courierCost - o.courierDiscount'),
                            __b('Status') => 'o.state',
                            __b('Typ') => 'o.mode',
                        ))
                        ->joinLeft(array('rc' => 'rabatt_check'), 'rc.rabattCodeId = o.rabattCodeId', array())
                        ->where('rc.rabattVerificationId = ' . $registrationCodeObj->getId());

                // build grid
                $grid = Default_Helper::getTableGrid();
                $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

                $grid->updateColumn('Status', array('title' => __b('Status'), 'searchType' => 'equal', 'class' => 'status', 'callback' => array('function' => 'intToStatusOrders', 'params' => array('{{Status}}', '{{Typ}}'))));
                $grid->updateColumn('Typ', array('title' => __b('Typ'), 'callback' => array('function' => 'modeToReadable', 'params' => array('{{Typ}}'))));
                $grid->updateColumn('Preis', array('title' => __b('Preis'), 'callback' => array('function' => 'intToPrice', 'params' => array('{{Preis}}'))));

                // translate stati
                $statis = array(
                    '-8' => __b('Pending payment'),
                    '-7' => __b('Storno Discount'),
                    '-6' => __b('Blacklist'),
                    '-5' => __b('Prepayment'),
                    '-4' => __b('Not affirmed on billing'),
                    '-3' => __b('Fake'),
                    '-2' => __b('Storno'),
                    '-1' => __b('Error'),
                    '-15' => __b('Fax error'),
                    '0' => __b('Not affirmed'),
                    '1' => __b('Affirmed'),
                    '2' => __b('Delivered'),
                    '' => __b('Alle')
                );

                // translate mode
                $modes = array(
                    'rest' => __b('Restaurant'),
                    'cater' => __b('Catering'),
                    'fruit' => __b('Obst'),
                    'great' => __b('Großhandel'),
                    'canteen' => __b('Kantine'),
                    '' => __b('Alle')
                );

                // add filters
                $filters = new Bvb_Grid_Filters();
                $filters->addFilter('ID')
                        ->addFilter('Nummer')
                        ->addFilter('Status', array('values' => $statis))
                        ->addFilter('Typ', array('values' => $modes));
                $grid->addFilters($filters);

                // add option row
                $option = new Bvb_Grid_Extra_Column();
                $option->position('right')->name(__b('Options'))->decorator("<a href='/administration_order/index/type/view_grid_orders/IDgrid/{{" . __b('ID') . "}}'>" . __b('Details') . "</a>");
                $grid->addExtraColumns($option);

                //deploy grid to view
                $this->view->grid = $grid->deploy();
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("Diesen Registrierungscode gibt es nicht!"));
                $this->_redirect('/administration_discount/checkregistrationcode/');
            }
        }
    }

    /**
     * deactivate discount code
     * @since 24.05.2011
     * @author alex
     */
    public function deactivatediscountcodeAction() {
        $request = $this->getRequest();

        $codeId = null;
        $code = null;

        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();
            if (!is_null($post['code'])) {
                $code = $post['code'];

                //create discount code object
                try {
                    $discountCode = new Yourdelivery_Model_Rabatt_Code($code);

                    if ($discountCode->getParent()->getRrepeat() != 0) {
                        $this->error(__b("Die Gutscheinaktion von dem Gutscheincode ist nicht einmalig. Gutscheincode kann nicht deaktiviert werden!"));
                        $this->_redirect('/administration_discount/deactivatediscountcode');
                    }

                    $discountCode->setDailydeal('deaktiviert');
                    $discountCode->setUsed(1);
                    $discountCode->save();

                    $this->logger->adminInfo(sprintf("Successfully deactivated discount code %s", $code));
                    $this->success(__b("Gutscheincode wurde deaktiviert"));

                    $this->_trackUserMove(Yourdelivery_Model_Admin_Access_Tracking::RABATT_DISABLE, Yourdelivery_Model_Admin_Access_Tracking::MODEL_TYPE_RABATT, $discountCode->getId());
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__b("Diesen Gutscheincode gibt es nicht!"));
                    $this->_redirect('/administration_discount/deactivatediscountcode');
                }
            }
        }else {
            $this->view->code = $request->getParam('code');
        }
    }

    /**
     * show a sortable, filterable table of discount codes of this discount action
     * @author alex
     * @since 09.08.2011
     */
    public function codesAction() {
        $this->error(__b("Diese Funktion wurde deaktiviert."));
        return $this->_redirect('/administration/discounts');

        $request = $this->getRequest();

        $rabattId = $request->getParam('rabattId', null);
        if (is_null($rabattId)) {
            $this->error(__b("Diese Rabattaktion gibt es nicht!"));
            $this->_redirect('/administration/discounts');
        }

        //create discount object
        try {
            $discount = new Yourdelivery_Model_Rabatt($rabattId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diese Rabattaktion gibt es nicht!"));
            $this->_redirect('/administration/discounts');
        }

        $this->view->assign('navcodes', 'active');
        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        $grid->setPagination(20);
        //select orders
        $select = $db->select()
                ->from(array('rc' => 'rabatt_codes'), array(
                    __b('ID') => 'id',
                    __b('DailyDeal-Code') => 'dailydeal',
                    __b('SMS-Code') => 'code',
                    __b('used'),
                    __b('Telefon') => 'tel',
                    __b('Email') => 'email',
                    __b('Erstellt') => 'created',
                ))
                ->where('rc.rabattId = ' . $rabattId)
                ->order('rc.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $grid->updateColumn('Status', array('callback' => array('function' => 'intToStatusDiscount', 'params' => array('{{' . __b('Status') . '}}'))));

        //add filters
        $filters = new Bvb_Grid_Filters();
        $yesno = array(
            '0' => __b('Nein'),
            '1' => __b('Ja'),
            '' => __b('Alle')
        );

        //add filters
        $filters->addFilter(__b('ID'))
                ->addFilter(__b('SMS-Code'))
                ->addFilter(__b('used'), array('values' => $yesno))
                ->addFilter(__b('DailyDeal-Code'))
                ->addFilter(__b('Telefon'))
                ->addFilter(__b('Email'))
                ->addFilter(__b('Erstellt'));

        $grid->addFilters($filters);

        $editLinks = new Bvb_Grid_Extra_Column();
        $editLinks->position('right')->name('')->callback(array('function' => 'discountcodeedit', 'params' => array('{{' . __b('ID') . '}}', '{{' . __b('used') . '}}', '{{' . __b('dailydeal') . '}}')));

        //add extra rows
        $grid->addExtraColumns($editLinks);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
        $this->view->discount = $discount;
    }

    /**
     * show a sortable, filterable table of verification codes of this discount action
     * @author Alex Vait <vait@lieferando.de>
     * @since 17.01.2012
     */
    public function verificationcodesAction() {
        $this->error(__b("Diese Funktion wurde deaktiviert."));
        return $this->_redirect('/administration/discounts');

        $request = $this->getRequest();

        $rabattId = $request->getParam('rabattId', null);
        if (is_null($rabattId)) {
            $this->error(__b("Diese Rabattaktion gibt es nicht!"));
            $this->_redirect('/administration/discounts');
        }

        //create discount object
        try {
            $discount = new Yourdelivery_Model_Rabatt($rabattId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diese Rabattaktion gibt es nicht!"));
            $this->_redirect('/administration/discounts');
        }

        $grid = Default_Helper::getTableGrid();
        $db = Zend_Registry::get('dbAdapter');
        $grid->setExport(array());
        $grid->setPagination(20);
        //select orders
        $select = $db->select()
                ->from(array('rcv' => 'rabatt_codes_verification'), array(
                    __b('ID') => 'rcv.id',
                    __b('Registrierungscode') => 'rcv.registrationCode',
                    __b('Versendet') => 'rcv.send',
                    __b('Rabattcode Id') => 'rch.rabattCodeId',
                ))
                ->joinLeft(array('rch' => 'rabatt_check'), 'rch.rabattVerificationId=rcv.id', array())
                ->joinLeft(array('rc' => 'rabatt_codes'), 'rc.id=rch.rabattCodeId', array(__b('Rabattcode') => 'rc.code'))
                ->where('rcv.rabattId = ' . $rabattId)
                ->order('rcv.id DESC');

        //update some columns
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        $grid->updateColumn(__b('Versendet'), array('callback' => array('function' => 'intToYesNoIcon', 'params' => array('{{' . __b('Versendet') . '}}'))));

        //add filters
        $filters = new Bvb_Grid_Filters();
        $yesno = array(
            '0' => __b('Nein'),
            '1' => __b('Ja'),
            '' => __b('Alle')
        );

        //add filters
        $filters->addFilter(__b('ID'))
                ->addFilter(__b('Versendet'), array('values' => $yesno))
                ->addFilter(__b('Rabattcode Id'))
                ->addFilter(__b('Registrierungscode'))
                ->addFilter(__b('Rabattcode'))
                ->addFilter(__b('Erstellt'));

        $grid->addFilters($filters);

        //deploy grid to view
        $this->view->grid = $grid->deploy();
        $this->view->discount = $discount;
    }

    /**
     * check state of discount for neu customers
     * @since 18.01.2012
     * @author alex
     */
    public function checkregistrationdiscountcodeAction() {

        $request = $this->getRequest();


        if ($request->isPost()) {
            $post = $this->getRequest()->getPost();

            // test if this registration code is unique, if not, refuse the action
            if (strlen($post['registrationCode']) > 0) {
                $verificationCodeArr = Yourdelivery_Model_DbTable_RabattCodesVerification::findByCode($post['registrationCode']);

                try {
                    $verificationCode = new Yourdelivery_Model_Rabatt_CodesVerification($verificationCodeArr['id']);

                    if (!is_null($verificationCode) && ($verificationCode->getId() != 0)) {
                        try {
                            $rabatt = new Yourdelivery_Model_Rabatt($verificationCode->getRabattId());
                            if ($rabatt->getType() == 3) {
                                $this->error(__b("Dieser Registrierungscode gehört zu der Rabattaktion mit einem universellem Code, damit können sich mehrere Benutzer registrieren!"));
                                $this->_redirect('/administration_discount/checkregistrationdiscountcode/');
                            }
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

                        }
                        $this->view->verificationCode = $verificationCode;
                    }
                    // do nothing - we have no corresponding discount
                    else {

                    }
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

                }
            }

            $post['tel'] = Default_Helpers_Normalize::telephone($post['tel']);

            // find entry based on email or tel. number or customer id or registration code
            $discountcheck = Yourdelivery_Model_Rabatt_Check::findByEmailOrTelOrCustomerOrVerificationcode($post['email'], $post['tel'], $post['customerId'], $post['registrationCode']);

            if (!is_null($discountcheck) && ($discountcheck->getId() != 0)) {
                $this->view->discountcheck = $discountcheck;

                $this->view->customer = $discountcheck->getCustomer();

                $rabattcode = $discountcheck->getRabattcode();
                $this->view->rabattcode = $rabattcode;

                if (!is_null($rabattcode)) {
                    $this->view->orderscount = $rabattcode->getOrdersCount();
                }
            }
            // try to find out, why the user received no discount code
            else if (strlen(trim($post['email'])) > 0) {
                try {
                    $customer = new Yourdelivery_Model_Customer(null, $post['email']);
                    $this->view->emailcustomer = $customer;
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

                }
            } else if (strlen(trim($post['tel'])) > 0) {
                try {
                    $customer = Yourdelivery_Model_Customer::findByTel($post['tel']);
                    $this->view->telcustomer = $customer;
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {

                }
            }

            $searchValue = array();
            if (strlen($post['email']) > 0) {
                $searchValue['email'] = $post['email'];
            } else if (strlen($post['tel']) > 0) {
                $searchValue['tel'] = $post['tel'];
            } else if (strlen($post['customerId']) > 0) {
                $searchValue['customerId'] = $post['customerId'];
            } else if (strlen($post['registrationCode']) > 0) {
                $searchValue['registrationCode'] = $post['registrationCode'];
            }

            $this->view->searchval = $searchValue;
        }
        // get request
        else {
            //set placeholder to show it was not post for code checking
            $this->view->getrequest = 1;
        }
    }

    /**
     * enables the user to download the codes
     * @author Allen Frank <frank@lieferando.de>
     * @since 14-02-2012
     */
    public function downloadcodesAction() {

        $this->_disableView();
        /**
         * @todo
         * currently only discounts which have less than 10000 codes are downloadable
         * the rest is not displayed.
         */
        $request = $this->getRequest();

        try {
            $discount = new Yourdelivery_Model_Rabatt($request->getParam('id'));
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->error(__b("Diese Rabattaktion gibt es nicht!"));
            $this->_redirect('/administration/discounts');
        }

        try {
            $codeFile = $discount->getZipFile();
        } catch (Yourdelivery_Exception_FileWrite $e) {
            $this->error($e->getMessage());
        }

        ini_set('zlib.output_compression', 'Off');

        if (!is_file($codeFile)) {
            return $this->_redirect('/administration/discounts');
        }

        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Content-Type', "application/zip")
                ->setHeader('Content-Disposition', 'attachment; filename="' . basename($codeFile) . '"')
                ->setHeader('Content-Transfer-Encoding', 'binary')
                ->setHeader('Expires', '0')
                ->setHeader('Pragma', 'no-cache');

        readfile($codeFile);
    }


    /**
     * show the information about the discount action and all orders made with codes from this discount
     * @since 30.07.2012
     * @author Alex Vait
     */
    public function checkdiscountAction() {

        $request = $this->getRequest();

        $rabattId = (integer) $request->getParam('rabattId', null);

        if ($rabattId > 0) {
            //create discount object
            try {
                $this->view->rabatt = $rabattObj = new Yourdelivery_Model_Rabatt($rabattId);
                $this->view->types = $rabattObj->getDiscountTypes();
                $this->view->grid = Default_Helpers_Grid::generateOrderGrid('view_grid_orders', array('rabattId' => $rabattId))->deploy();
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->error(__b("Diese Rabattaktion existriert nicht!"));
                $this->logger->warn(sprintf('ADMIN: could not find discount %s to check', $rabattId));
                $this->_redirect('/administration_discount/checkdiscount/');
            }
        }
    }

}
