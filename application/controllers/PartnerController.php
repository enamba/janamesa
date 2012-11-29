<?php

/**
 * Description of partnerController
 *
 * @author daniel
 */
class PartnerController extends Default_Controller_PartnerBase {

    protected $_config = null;
    
    /**
     * Initializes the controller and prepares domainBase for every action
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.07.2012
     */
    public function init() {
        parent::init();
        $this->initView();
        $this->_config = Zend_Registry::get('configuration');
        $domainBase = Zend_Registry::get('configuration')->domain->base;

        // domains which redirect to lieferando.de/partner
        if (in_array($domainBase, array('eat-star.de'))) {
            $this->_redirect('http://www.lieferando.de/partner/login');
        }

        $domainBase = $domainBase == 'lieferando.at' || $domainBase == 'lieferando.ch' ? 'lieferando.de' : $domainBase;
        $this->view->domainBase = $domainBase;
    }

    /**
     * show main site with a list of all orders
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 25.03.2012
     */
    public function indexAction() {
        $restaurant = $this->initRestaurant();

        if (is_null($restaurant->getId())) {
            return $this->_helper->redirector->gotoRoute(array('action' => 'login'), 'partnerRoute', true);
        }

        $openings = array();
        foreach ($restaurant->getRegularOpenings() as $opening) {
            $openings[$opening->day][] = array('from' => $opening->from, 'until' => $opening->until);
        }

        $this->view->openings = $openings;
    }

    /**
     * login
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 25.03.2012
     * @modified 02.08.2012 Alex Vait 
     */
    public function loginAction() {

        //redirect if already logged in
        if (is_object($this->session->admin) || is_object($this->session->masterAdmin)) {
            return $this->_helper->redirector->gotoRoute(array(), 'partnerRoute', true);
        }

        $form = new Yourdelivery_Form_Partner_Login();
        $form->populate(array());
        $this->view->form = $form;
        //get our request
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $customerNr = $form->getValue('nr');
                $pass = $form->getValue('pass');

                $restaurant = null;
                $partnerData = null;
                $temporaryLogin = false;

                // first test if this is the temporary password, send to the partner because he has forgotten the old password                
                $row = Yourdelivery_Model_DbTable_Restaurant::findByCustomerNr($customerNr);

                if ((count($row) > 0) && (strlen($row['id']) > 0)) {
                    try {
                        // create restaurant and partner data, if available
                        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($row['id']);
                        $partnerData = $restaurant->getPartnerData();

                        if (!is_null($partnerData) && (strlen($partnerData->getTemporarypassword()) > 0)) {
                            $this->temporaryAdminAuth
                                    ->setIdentity($restaurant->getId())
                                    ->setCredential($pass);
                            // try to authenticate with temporary password
                            $result = $this->temporaryAdminAuth->authenticate();

                            // if we can login, that means that the partner requested a temporary password,
                            // so set the session variable temporaryAuthentication that forces him to define a new password                            
                            if ($result->getCode() == Zend_Auth_Result::SUCCESS) {
                                $temporaryLogin = true;
                                $this->session->partnerRestaurantId = $restaurant->getId();
                                $this->session->temporaryAuthentication = true;
                                return $this->_helper->redirector->gotoRoute(array('action' => 'resetpassword'), 'partnerRoute', true);
                            }
                        }
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->warn(__p('Benutzerdaten sind fehlerhaft!'));
                    }
                }

                // the temporary login was not requested or was wrong, try to authenticate with usual login data
                if (!$temporaryLogin) {
                    //insert login values into auth adapter
                    $this->adminAuth
                            ->setIdentity($customerNr)
                            ->setCredential($pass);
                    //get result ...
                    $result = $this->adminAuth->authenticate();

                    //... and check it
                    switch ($result->getCode()) {

                        case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                            $this->warn(__p('Diesen Account gibt es nicht!'));
                            break;

                        case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                            $this->warn(__p('Das Passwort ist falsch!'));
                            break;

                        case Zend_Auth_Result::SUCCESS:
                            // if partner data available and temporary password was set, delete it
                            // this case means that tempporary password was asked by someone faked or that the partner recalled his old  password
                            if (!is_null($partnerData) && (strlen($partnerData->getTemporarypassword()) > 0)) {
                                $partnerData->setTemporarypassword("");
                                $partnerData->save();
                            }

                            $this->session->partnerRestaurantId = $restaurant->getId();

                            // no partner data was set yet, force the partner to the data page to enter email or optional mobile phone number
                            if (is_null($partnerData) || ($partnerData->getId() == 0)) {
                                return $this->_helper->redirector->gotoRoute(array('action' => 'data'), 'partnerRoute', true);
                            }

                            return $this->_helper->redirector->gotoRoute(array(), 'partnerRoute', true);
                            break;

                        default:
                            $this->warn(__p('Unbekannter Fehler!'));
                    }
                }
            } else {
                
            }
            $this->view->p = $request->getPost();
        }
    }

    /**
     * logout
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 25.03.2012
     */
    public function logoutAction() {

        if (!is_null($this->session->partnerRestaurantId)) {
            $this->session->unsetAll();
        }
        return $this->_helper->redirector->gotoRoute(array('action' => 'login'), 'partnerRoute', true);
    }

    /**
     * show all orders
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 25.03.2012
     */
    public function ordersAction() {

        $filter = $this->getRequest()->getParam('filter');

        $this->view->assign('filter', $filter);

        // build query
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $restaurantId = $this->session->partnerRestaurantId;

        $select = $db
                ->select()
                ->from(array('o' => 'orders'), array(
                    'Datum' => 'time',
                    'BestellNr' => 'nr',
                    'Summe' => 'total',
                    'id' => 'id',
                    'hash' => 'hashtag',
                    'Status' => new Zend_Db_Expr('IF (o.state = 2, 1, o.state)'),
                    'Typ' => 'mode'))
                ->join(array('oc' => 'orders_customer'), 'o.id = oc.orderId', array(
                    'Kundenname' => new Zend_Db_Expr("CONCAT(oc.prename, ' ', oc.name)")))
                ->join(array('ol' => 'orders_location'), 'o.id = ol.orderId', array(
                    'Telefonnummer' => 'ol.tel', 
                    'Adresse' => new Zend_Db_Expr("CONCAT(ol.street, ' ', ol.hausnr)"), 
                    'PLZ' => 'plz'))
                ->join(array('c' => 'city'), 'ol.cityId = c.id', array(
                    'Ort' => 'city'
                ))
                ->where('o.restaurantId = ?', $restaurantId)
                ->where('o.state IN (-2, 1, 2)')
                ->order('o.time DESC');

        switch ($filter) {
            default:
            case 'today' :
                $select->where('DATE(o.time) = DATE(NOW())');
                $this->view->filter = 'today';
                break;

            case 'lastseven' :
                $select->where("DATE(o.time) > DATE_SUB(NOW(), INTERVAL  7 DAY)");
                break;
            
            case 'lastmonth' :
                $select->where("DATE_FORMAT(o.time, '%Y-%m') = DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m')");
                break;
            
            case 'week' :
                $select->where('YEAR(o.time) = YEAR(NOW())');
                $select->where('WEEK(o.time) = WEEK(NOW())');
                break;

            case 'month' :
                $select->where('YEAR(o.time) = YEAR(NOW())');
                $select->where('MONTH(o.time) = MONTH(NOW())');
                break;
            
            case 'all':
                break;
        }

        // build grid
        $config = Zend_Registry::get('configuration');
        $grid = Bvb_Grid::factory('Tabletranslate', $config, 'grid');

        // pagination must use the partnerRoute Route
        $grid->setRouteUrl($this->view->url(array('action' => 'orders'), 'partnerRoute', true));

        $grid->setOptions(array(
            'template' => array(
                'tabletranslate' => array(
                    'cssClass' => array(
                        'table' => 'user-tab yd-grid-input'
                    )
                )
            ),
            'deploy' => array(
                'tabletranslate' => array(
                    'imagesUrl' => '/media/images/yd-backend/grid/'
                )
            )
        ));
        $grid->setView(new Zend_View());

        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->setExport(array());
        $grid->setPagination(20);
        $grid->updateColumn('id', array('hidden' => 1));
        $grid->updateColumn('BestellNr', array('title' => __p('BestellNr')));
        $grid->updateColumn('Kundenname', array('title' => __p('Kundenname')));
        $grid->updateColumn('Ort', array('title' => __p('Ort')));
        $grid->updateColumn('prename', array('hidden' => 1));
        $grid->updateColumn('Adresse', array('decorator' => '{{Adresse}}'));
        $grid->updateColumn('hash', array('hidden' => 1));
        $grid->updateColumn('Datum', array('callback' => array('function' => 'dateFull', 'params' => array('{{Datum}}')), 'searchType' => 'equal'));
        $grid->updateColumn('Summe', array('callback' => array('function' => 'intToPrice', 'params' => array('{{Summe}}')), 'decorator' => '{{Summe}} ' . __p('€')));
        $grid->updateColumn('Status', array('title' => __p('Status'), 'searchType' => 'equal', 'class' => 'status', 'callback' => array('function' => 'intToStatusOrders', 'params' => array('{{Status}}', 'partner'))));
        $grid->updateColumn('Typ', array('hidden' => 1));

        // translate stati
        $statis = array(
            '-2' => __p('Storno'),
            '1' => __p('Bestätigt'),
            '' => __p('Alle')
        );

        // add filters
        $gridFilters = new Bvb_Grid_Filters();
        $gridFilters->addFilter('Datum')
                ->addFilter('BestellNr')
                ->addFilter('Summe')
                ->addFilter('Status', array('values' => $statis))
                ->addFilter('Kundenname')
                ->addFilter('PLZ')
                ->addFilter('Ort')
                ->addFilter('Telefonnummer')
                ->addFilter('Adresse');
        $grid->addFilters($gridFilters);

        $links = new Bvb_Grid_Extra_Column();
        $links->position('right')
                ->name(__p('Bestellungen'))
                ->decorator('<a class="yd-icon-html" onclick="return popup(\'/order/bestellzettel/order/{{id}}\', \'Bestellzettel\', 800, 600);" title="' . __p('Als Webseite ansehen') . '"
                    href="#">HTML</a>&nbsp;|&nbsp;<a class="yd-icon-pdf" title="' . __p('Als pdf speichern') . '" href="/download/order/{{hash}}">' . __p('PDF') . '</a>');
        $grid->addExtraColumns($links);

        // deploy grid to view
        $this->view->grid = $grid->deploy();
    }

    /**
     * billings
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 25.03.2012
     */
    public function billingAction() {

        $restaurantId = $this->restaurant->getId();
        $currentMonth = date('n');
        $lastYear = date('Y') - 1;

        $billsPerMonth = array();
        for($i = $this->_firstSaleYear; $i <= date('Y'); $i++){
            $billsPerMonth[$i] = $this->restaurant->getBillings(array('year' => $i));
        }
        $this->view->billsPerMonth = array_reverse($billsPerMonth, true);
        
        $form = new Yourdelivery_Form_Partner_Billing_Deliver();
        $form->getElement('email')->setAttribs(array(
            'checked' => "checked",
            'disabled' => "disabled",
        ));
        $billDeliver = explode(",", $this->restaurant->getBillDeliver());
        if (in_array("fax", $billDeliver)) {
            $form->getElement('fax')->setAttrib('checked', "checked");
        }
        if (in_array("post", $billDeliver)) {
            $form->getElement('post')->setAttrib('checked', "checked");
        }
        
        $this->view->salesVolume = Yourdelivery_Statistics_Restaurant::getSalesVolume($restaurantId, $currentMonth);
        $this->view->salesVolumeBar = Yourdelivery_Statistics_Restaurant::getSalesVolume($restaurantId, $currentMonth, 'bar');
        $this->view->salesVolumeOnline = Yourdelivery_Statistics_Restaurant::getSalesVolume($restaurantId, $currentMonth, 'online');
        
        $this->view->form = $form;
    }

    /**
     * Orders statistics
     * @author Daniel Hahn <hahn@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 25.03.2012
     */
    public function statsAction() {

        $daysArray = array();
        $month = date('m');

        for ($d = 1, $day = date('d'); $d <= $day; $d++) {
            $daysArray[] = sprintf("%02d.%02d", $d, $month);
        }

        $restaurantId = $this->restaurant->getId();
        $lastYear = date('Y') - 1;

        // daily
        $this->view->dailyStats = Yourdelivery_Statistics_Restaurant::getSalesPerDay($restaurantId);

        //get all stats from all years
        $stats = array();
        for($i = $this->_firstSaleYear; $i <= date('Y'); $i++){
            $dataOrdersWeek = Yourdelivery_Statistics_Restaurant::getOrdersPerWeek($restaurantId, $i);
            $dataSalesWeek = Yourdelivery_Statistics_Restaurant::getSalesPerWeek($restaurantId, $i);
            
            $dataOrdesMonth = Yourdelivery_Statistics_Restaurant::getOrdersPerMonth($restaurantId, $i);
            $dataSalesMonth = Yourdelivery_Statistics_Restaurant::getSalesPerMonth($restaurantId, $i);
            $stats[$i] = array(
                'firstWeek' => array_shift(array_keys($dataOrdersWeek)),
                'lastWeek' => array_pop(array_keys($dataOrdersWeek)),
                'orders' => array(
                    'week' => $dataOrdersWeek,
                    'month' => $dataOrdesMonth
                ),
                'sales' => array(
                    'week' =>$dataSalesWeek,
                    'month' => $dataSalesMonth
                )
            );
        }
        
        $this->view->stats = array_reverse($stats, true);
        $this->view->domain = $this->config->domain->base;
        $this->view->daysArray = $daysArray;
    }

    /**
     * contact form
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 25.03.2012
     */
    public function contactAction() {
        $form = new Yourdelivery_Form_Partner_Contact();
        $this->view->form = $form;
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($this->_processContact($request, $form)) {
                return $this->_helper->redirector->gotoRoute(array('action' => 'contact'), 'partnerRoute', true);
            }
        }
    }

    /**
     * contact form in a lightbox
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 15.08.2012
     */
    public function contactlightAction() {
        $form = new Yourdelivery_Form_Partner_Contact();
        $this->view->form = $form;
        $request = $this->getRequest();
        $this->view->close = false;
        if ($request->isPost()) {
            if ($this->_processContact($request, $form)) {
                $this->view->close = true;
            }
        }
    }

    /**
     * process contact form
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 15.08.2012
     * 
     * @param Zend_Controller_Request_Abstract $request
     * @param Yourdelivery_Form_Partner_Contact $form
     * @return boolean
     */
    private function _processContact(Zend_Controller_Request_Abstract $request, Yourdelivery_Form_Partner_Contact $form) {
        if (!$form->isValid($request->getPost())) {
            return false;
        }

        $values = $form->getValues();

        $email = new Yourdelivery_Sender_Email();
        $email->setSubject($values['subject'] . ' [' . __p('Dienstleister Nr.') . ' ' . $this->restaurant->getCustomerNr() . ', id #' . $this->restaurant->getId() . ']');
        $email->setBodyText($values['message']);
        if (strlen($this->restaurant->getEmail()) > 0) {
            $email->setFrom($this->restaurant->getEmail());
        }
        $email->addTo('backoffice@lieferando.de');

        $file = $form->attachment->getFileName();

        if (strlen($file) > 0) {
            $email->attachFile($form->attachment->getFileName());
        }

        if ($email->send()) {
            $this->success(__p('Ihre Anfrage wurde erfolgreich versandt'));
            return true;
        } else {
            $this->error(__p('Ein Fehler ist aufgetreten!'));
            return false;
        }
    }

    /**
     * contact form
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 25.03.2012
     */
    public function accountAction() {
        $billingContact = $this->restaurant->getBillingContact();

        //should be stored in backen data as well YD-3133
        if ($billingContact === null) {
            $billingContact = new Yourdelivery_Model_Contact();
            $billingContact->setName($this->restaurant->getName());
            $billingContact->setPrename('');
            $billingContact->save();
            $this->restaurant->setBillingContactId($billingContact->getId());
            $this->restaurant->save();
        }
        
        $partnerData = new Yourdelivery_Model_Servicetype_Partner(null, $this->restaurant->getId());
        $this->view->emailForm = $emailForm = new Yourdelivery_Form_Partner_Email();

        $this->view->mobileForm = $mobileForm = new Yourdelivery_Form_Partner_Mobile();
        
        $this->view->passForm = $passForm = new Yourdelivery_Form_Partner_Password();

        $this->view->configForm = $configForm = new Yourdelivery_Form_Partner_Config();

        $request = $this->getRequest();

        if ($request->isPost()) {

            $type = $request->getParam('type');
            switch ($type) {
                default:
                    break;

                case 'email':
                    if ($emailForm->isValid($request->getParams())) {
                        $oldEmail = $partnerData->getEmail();

                        // actually this shall not happen, because email is required at first login
                        if (is_null($partnerData) || ($partnerData->getId() == 0)) {
                            $partnerData = new Yourdelivery_Model_Servicetype_Partner();
                            $partnerData->setRestaurantId($this->restaurant->getId());
                        }

                        $partnerData->setEmail($emailForm->getValue('email'));
                        $billingContact->setEmail($emailForm->getValue('email'));
                        $billingContact->save();

                        try {
                            $partnerData->save();
                            if (strlen($oldEmail) == 0) {
                                $this->logger->info(sprintf('partner: Restaurant %s set email to %s', $this->restaurant->getId(), $emailForm->getValue('email')));
                                $this->info(__p("Email wurde erfolgreich gesetzt"));
                            } else {
                                $this->logger->info(sprintf('partner: Restaurant %s changed email from %s to %s', $this->restaurant->getId(), $oldEmail, $emailForm->getValue('email')));
                                $this->info(__p("Email wurde erfolgreich geändert"));
                            }
                            return $this->_helper->redirector->gotoRoute(array('action' => 'account'), 'partnerRoute', true);
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->logger->info(sprintf('partner: Restaurant %s could not set email to %s. Exception: %s', $this->restaurant->getId(), $emailForm->getValue('email'), $e->getMessage()));
                            $this->error(__p('Ihre Email konnte nicht gesetzt werden. Bitte kontaktieren Sie unseren Support!'));
                        }
                    }
                    break;

                case 'mobile':
                    if ($mobileForm->isValid($request->getParams())) {
                        $oldMobile = $partnerData->getMobile();

                        // actually this shall not happen, because at least email is required at first login
                        if (is_null($partnerData) || ($partnerData->getId() == 0)) {
                            $partnerData = new Yourdelivery_Model_Servicetype_Partner();
                            $partnerData->setRestaurantId($this->restaurant->getId());
                        }

                        $partnerData->setMobile($mobileForm->getValue('mobile'));
                        $billingContact->setMobile($mobileForm->getValue('mobile'));
                        $billingContact->save();

                        try {
                            $partnerData->save();
                            if (strlen($oldMobile) == 0) {
                                $this->logger->info(sprintf('partner: Restaurant %s set mobile phone number to %s', $this->restaurant->getId(), $mobileForm->getValue('mobile')));
                                $this->info(__p("Mobilnummer wurde erfolgreich gesetzt"));
                            } else {
                                $this->logger->info(sprintf('partner: Restaurant %s changed mobile phone number from %s to %s', $this->restaurant->getId(), $oldMobile, $mobileForm->getValue('mobile')));
                                $this->info(__p("Mobilnummer wurde erfolgreich geändert"));
                            }
                            return $this->_helper->redirector->gotoRoute(array('action' => 'account'), 'partnerRoute', true);
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->logger->info(sprintf('partner: Restaurant %s could not set mobile phone number to %s. Exception: %s', $this->restaurant->getId(), $mobileForm->getValue('mobile'), $e->getMessage()));
                            $this->error(__p('Ihre Mobilnummer konnte nicht gesetzt werden. Bitte kontaktieren Sie unseren Support!'));
                        }
                    }
                    break;
                    
                case 'password':
                    if ($passForm->isValid($request->getParams())) {

                        if (strcmp(md5($passForm->getValue('passwordOld')), $this->restaurant->getPassword()) != 0) {
                            $this->error(__p("Das alte Passwort ist nicht korrekt"));
                            return $this->_helper->redirector->gotoRoute(array('action' => 'account'), 'partnerRoute', true);
                        }

                        $this->restaurant->setPassword(md5($passForm->getValue('passwordOne')));
                        $this->restaurant->save();

                        $email = new Yourdelivery_Sender_Email();
                        $email->setSubject(__b("Ihr neues Passwort für das Lieferando-Konto"))
                                ->setBodyHtml(__b("Ihr neues Passwort für das Lieferando-Konto lautet: %s<br /><br /><br />Ihr Lieferando-Team.", $passForm->getValue('passwordOne')))
                                ->addTo($this->restaurant->getEmail());

                        if ($email->send('system')) {
                            $this->success(__b("Email mit Passwort wurde an den Dienstleister verschickt"));
                            $this->logger->info(sprintf("Password was send to restaurant #%d per email", $this->restaurant->getId()));
                        } else {
                            $this->error(__b("Email mit Passwort konnte nicht an den Dienstleister verschickt werden"));
                            $this->logger->warn(sprintf("Password could not be send to restaurant #%d per email", $this->restaurant->getId()));
                        }

                        $this->logger->info(sprintf('partner: Restaurant %s changed password', $this->restaurant->getId()));
                        $this->success(__p("Passwort wurde erfolgreich geändert"));
                        return $this->_helper->redirector->gotoRoute(array('action' => 'account'), 'partnerRoute', true);
                    }
                    break;

                case 'config':
                    if ($configForm->isValid($request->getPost())) {

                        $this->restaurant->setSound((boolean) $configForm->getValue('sound'));
                        $this->restaurant->setOrderticker((boolean) $configForm->getValue('orderticker'));
                        $this->restaurant->save();

                        $this->success(__p('Ihre Einstellungen wurden gespeichert'));
                        return $this->_helper->redirector->gotoRoute(array('action' => 'account'), 'partnerRoute', true);
                    }
                    break;
            }
            
        }
        
        $configForm->populate(array(
            'sound' => $this->restaurant->getSound(),
            'orderticker' => $this->restaurant->getOrderticker(),
        ));
        
        $emailForm->populate(array(
            'email' => $billingContact->getEmail(),
        ));

        $mobileForm->populate(array(
            'mobile' => $billingContact->getMobile(),
        ));
    }

    /**
     * @author Allen Frank <frank@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 18.07.12
     */
    public function documentsAction() {

        $pdfs = array(
            'extraIngredients' => __p('Extrazutaten'),
            'openings' => __p('Öffnungszeiten'),
            'contract' => __p('Datenänderung'),
            'ownership' => __p('Inhaberwechsel'),
            'bankDetails' => __p('Kontodatenänderung'),
            'menu' => __p('Kartenänderung'),
            'move' => __p('Umbau - Umzug'),
            'deliveryArea' => __p('Liefergebiete'),
        );

        $localNames = array($this->_config->locale->name);
        // for AT and CH we use pdf from DE if they do not exists
        if (in_array($localNames[0], array("de_AT", "de_CH"))) {
            $localNames[] = "de_DE";
        }

        foreach ($pdfs as $pdfName => $pdfLabel) {
            foreach ($localNames as $localName) {
                $pdfFile = '/media/pdf/partner/' . $localName . '/' . $pdfName . '.pdf';
                if (file_exists(APPLICATION_PATH . '/../public' . $pdfFile)) {
                    $this->view->$pdfName = sprintf('/partner/getdoc?name=%s&file=%s', $pdfName, $pdfFile);
                    break;
                }
            }
        }
    }

    /**
     * download pdf files and add service details
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.08.2012
     */
    public function getdocAction() {
        $this->_disableView(true);

        $name = $this->getRequest()->getParam('name', null);
        $pdfName = $this->getRequest()->getParam('file', null);
        if ($name === null) {
            return $this->_helper->redirector->gotoRoute(array('action' => 'documents'), 'partnerRoute', true);
        }

        $pdfFile = APPLICATION_PATH . '/../public' . $pdfName;

        //add 
        $pdf = Zend_Pdf::load($pdfFile);
        $page = $pdf->pages[0];
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $page->setFont($font, 15);
        $page->drawText($this->restaurant->getCustomerNr(), 55, 780);
        $page->drawText($this->restaurant->getName(), 175, 780);

        $tmpFile = tempnam('/tmp', 'partner');
        $pdf->save($tmpFile);

        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $name . '.pdf"')
                //->setHeader('Content-Length',filesize($file))
                ->setHeader('Content-Transfer-Encoding', 'binary')
                ->setHeader('Expires', '0')
                ->setHeader('Pragma', 'no-cache');

        readfile($tmpFile);
    }

    /**
     * Forms for setting partner data
     * @author Alex Vait <vait@lieferando.de>
     * @since 31.07.12
     */
    public function dataAction() {

        // test if the entry is already in the table, it can happen onyl if the user entered the url directly,
        // so we have no reason to stay on the page        
        $partnerData = new Yourdelivery_Model_Servicetype_Partner(null, $this->restaurant->getId());
        if (!is_null($partnerData) && ($partnerData->getId() > 0)) {
            // the entry is there, so redirect from here!
            return $this->_helper->redirector->gotoRoute(array(), 'partnerRoute', true);
        }

        $form = new Yourdelivery_Form_Partner_Data();
        $form->getElement('email')->setValue($this->restaurant->getEmail());
        $form->getElement('mobile')->setValue($this->restaurant->getMobile());

        $this->view->form = $form;

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $values = $form->getValues();
                $partnerData = new Yourdelivery_Model_Servicetype_Partner();

                // set partner data
                $partnerData->setRestaurantId($this->restaurant->getId());
                $partnerData->setData($values);

                try {

                    //should be stored in backen data as well YD-3133
                    $this->restaurant->setEmail($form->getValue('email'));
                    $this->restaurant->save();

                    $partnerData->save();
                    $this->success(__p('Vielen Dank! Du kannst deine Email, Telefonnummer und das Passwort jederzeit unter "Konto" ändern.'));
                    return $this->_helper->redirector->gotoRoute(array(), 'partnerRoute', true);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__p('Ihre Daten konnten nicht gesetzt werden. Bitte kontaktieren Sie unseren Support!'));
                }
            }
        }
    }

    /**
     * Forms for setting new password after temporary password was send to the partner restaurant
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.08.12
     */
    public function resetpasswordAction() {

        // if the partner was not asked to reset his password, he don't need this page
        if (!$this->session->temporaryAuthentication) {
            return $this->_helper->redirector->gotoRoute(array(), 'partnerRoute', true);
        }

        $form = new Yourdelivery_Form_Partner_Resetpassword();
        $this->view->form = $form;

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $values = $form->getValues();

                try {
                    $this->restaurant->setPassword(md5($values['passwordOne']));
                    $this->restaurant->save();

                    $partnerData = new Yourdelivery_Model_Servicetype_Partner(null, $this->restaurant->getId());
                    /*
                     * if partner data available and temporary password was set, delete it
                     * this case means that temporary password was asked and now succesfully reset
                     */
                    if (!is_null($partnerData) && (strlen($partnerData->getTemporarypassword()) > 0)) {
                        $partnerData->setTemporarypassword("");
                        $partnerData->save();
                    }

                    // also delete the session variable so we don't get redirected to the password reset page
                    $this->session->temporaryAuthentication = false;

                    $this->success(__p('Vielen Dank! Ihr neues Passwort wurde gesetzt!'));
                    return $this->_helper->redirector->gotoRoute(array(), 'partnerRoute', true);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->error(__p('Ihre Daten konnten nicht gesetzt werden. Bitte kontaktieren Sie unseren Support!'));
                }
            }
        }
    }

    /**
     * Forms for resending password 
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.08.12
     */
    public function requestpasswordAction() {
        $form = new Yourdelivery_Form_Partner_Requestpassword();
        $post = $this->getRequest()->getPost();

        if (!is_null($post)) {
            $form->getElement('customerNr')->setValue($post['customerNr']);
            $form->getElement('email')->setValue($post['email']);
            $form->getElement('mobile')->setValue($post['mobile']);
        }

        $this->view->form = $form;
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $values = $form->getValues();

                $email = $values['email'];
                $mobile = Default_Helpers_Normalize::telephone($values['mobile']);

                // if neither emial no mobile number were given, show error message
                if ((strlen($email) == 0) && (strlen($mobile) == 0)) {
                    $this->error(__p('Bitte geben Sie Ihre E-Mail oder Ihre Mobilnummer ein!'));
                    return $this->_helper->redirector->gotoRoute(array('action' => 'requestpassword'), 'partnerRoute', true);
                }

                $row = Yourdelivery_Model_DbTable_Restaurant::findByCustomerNr($values['customerNr']);

                if (strlen($row['id']) > 0) {
                    // a restaurant was found
                    try {
                        // create restaurant and partner data, if available
                        $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($row['id']);
                        $partnerData = new Yourdelivery_Model_Servicetype_Partner(null, $restaurant->getId());

                        $lastPasswortRequest = strtotime($partnerData->getTemporarypasswordsend());
                        $secondsFromLastPasswortRequest = time() - $lastPasswortRequest;

                        // five minutes
                        $timebetweenRequests = 300;

                        // if last request was less than five minutes, reject it
                        if ($secondsFromLastPasswortRequest <= $timebetweenRequests) {
                            $this->error(__p('Sie haben bereits ein Passwort angefordert! Bitte warten Sie bis zum nächsten Versuch!'));
                            return $this->_helper->redirector->gotoRoute(array('action' => 'requestpassword'), 'partnerRoute', true);
                        }

                        if (strlen($email) != 0) {
                            if (strcmp($restaurant->getPartnerEmail(), $email) != 0) {
                                // wrong email for the partner was defined
                                $this->error(__b('Die E-Mail ist nicht korrekt für diesen Dienstleister'));
                                $this->logger->adminInfo(__b("Someone tried to request new password for the restaurant #%s to the email %s", $restaurant->getId(), $email));
                            }
                            // email is corrrect, so send the temporary password
                            else {
                                $state = $restaurant->sendPartnerTemporaryPassword('email', $email);
                                if ($state) {
                                    $this->success(__b('Ein temporäres Passwort wurde an die Email ' . $email . ' verschickt.'));
                                    $this->logger->adminInfo(__b("Temporary password was send to the restaurant #%s to the email %s", $restaurant->getId(), $email));
                                    return $this->_helper->redirector->gotoRoute(array(), 'partnerRoute', true);
                                }
                                // something went wrong
                                else {
                                    $this->error(__b('Email konnte nicht verschickt werden. Bitte kontaktieren Sie unseren Support!'));
                                    $this->logger->adminInfo(__b("Email sending error. Temporary password was not send to the restaurant #%s to the email %s", $restaurant->getId(), $email));
                                }
                            }
                        } else {
                            if (strcmp($restaurant->getPartnerMobile(), $mobile) != 0) {
                                // wrong mobile number for the partner was defined
                                $this->error(__b('Die Mobilnummer ist nicht korrekt für diesen Dienstleister'));
                                $this->logger->adminInfo(__b("Someone tried to request new password for the restaurant #%s to the number %s", $restaurant->getId(), $mobile));
                            }
                            // number is corrrect, so send the temporary password
                            else {
                                $state = $restaurant->sendPartnerTemporaryPassword('mobile', $mobile);

                                if ($state) {
                                    $this->success(__b('Ein temporäres Passwort wurde per SMS an die Nummer ' . $mobile . ' verschickt.'));
                                    $this->logger->adminInfo(__b("Temporary password was send to the restaurant #%s to the mobile number %s", $restaurant->getId(), $mobile));
                                    return $this->_helper->redirector->gotoRoute(array(), 'partnerRoute', true);
                                }
                                // something went wrong
                                else {
                                    $this->error(__b('SMS konnte nicht verschickt werden. Bitte kontaktieren Sie unseren Support!'));
                                    $this->logger->adminInfo(__b("SMS sending error. Temporary password was not send to the restaurant #%s to the mobile number %s", $restaurant->getId(), $mobile));
                                }
                            }
                        }
                    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                        $this->warn(__p('Benutzerdaten sind fehlerhaft!'));
                    }
                } else {
                    $this->error(__p('Diese Kundennummer wurde nicht gefunden!'));
                    return $this->_helper->redirector->gotoRoute(array('action' => 'requestpassword'), 'partnerRoute', true);
                }
            }
        }
    }
    
    public function faqAction() {}

}
