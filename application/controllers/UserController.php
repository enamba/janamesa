<?php

/**
 * @author Matthias Laug <laug@lieferando.de>
 */
class UserController extends Default_Controller_Base {

    public function preDispatch() {
        $this->view->extra_css = 'user';
    }

    /**
     * login action
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function loginAction() {
        $this->_preLogin();

        //get our request
        $request = $this->getRequest();
        $user = $request->getParam('user');
        $pass = $request->getParam('pass');

        if ($user === null || $pass === null || empty($user) || empty($pass)) {
            return $this->_redirect('/user/loginfailed');
        }

        //insert login values into auth adapter
        $this->auth
                ->setIdentity($user)
                ->setCredential($pass);
        $this->_postLogin();
    }

    /**
     * facebook login/register action
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    public function fbLoginAction() {
        $this->_preLogin();

        $facebook = new Yourdelivery_Connect_Facebook();
        if (($customer = $facebook->getYourdeliveryUser())) {
            $this->auth_fb
                    ->setIdentity($customer->getEmail())
                    ->setCredential($customer->getFacebookId());
            $this->_postLogin();
        } else {
            $this->error($facebook->getError());
            return $this->_redirect('/user/loginfailed');
        }
    }

    /**
     * facebook connect action for a logged in user
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    public function fbConnectAction() {
        if (!$this->getCustomer()->isLoggedIn()) {
            return $this->_redirect('/');
        }
        $redirectUrl = $this->getRequest()->getParam('redirect_url', null);

        $facebook = new Yourdelivery_Connect_Facebook();
        if ($facebook->getYourdeliveryUser($this->getCustomer())) {
            // don't redirect from loginfailed to loginfailed
            if (is_null($redirectUrl) || strstr($redirectUrl, 'loginfailed') || 'http://' . HOSTNAME . '/' == $redirectUrl) {
                $redirectUrl = $this->getCustomer()->getStartUrl();
            }
            return $this->_redirect($redirectUrl);
        } else {
            $this->error($facebook->getError());
            return $this->_redirect($redirectUrl);
        }
    }

    /**
     * helper function for login and facebook login action
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    private function _preLogin() {
        //if the current customer is already logged in, we will logout
        //http://ticket/browse/YD-2113
        if ($this->getCustomer()->isLoggedIn()) {
            $this->setupLogout();
        }
    }

    /**
     * helper function for login and facebook login action
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    private function _postLogin() {
        $request = $this->getRequest();
        $user = $request->getParam('user');

        //get result ...
        $result = $this->auth->authenticate();

        //... and check it
        switch ($result->getCode()) {

            //is not meant to be :(
            case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                $this->logger->warn(sprintf('User %s failed while trying to log in', $user));
                return $this->_redirect('/user/loginfailed');
                break;

            case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                $this->logger->warn(sprintf('User %s failed while trying to log in', $user));
                return $this->_redirect('/user/loginfailed');
                break;

            //YEAHA!!!
            case Zend_Auth_Result::SUCCESS:
                //direct redirect to intern page
                $result = $this->setupLogin($result->getIdentity());
                if (!$result) {
                    $this->logger->warn(sprintf('User %s failed while trying to log in, because we could not setup his login', $user));
                    return $this->_redirect('/user/loginfailed');
                } else {
                    $this->logger->info(sprintf('User %s successfully logged in', $user));
                    //piwik tracking
                    Yourdelivery_Model_Piwik_Tracker::trackCustomVariable(
                            1, 'customerId', $this->getCustomer()->getId()
                    );

                    //if the customer wants login from finish page
                    //we forward to keep the post which enherits the
                    //order data
                    if ($request->getParam('login_finish')) {
                        switch ($request->getParam('kind')) {
                            default:
                            case 'priv':
                                return $this->_forward('finish', 'order_private');
                                break;
                            case 'comp':
                                return $this->_forward('finish', 'order_company');
                                break;
                        }
                    }

                    $redirectUrl = $request->getParam('redirect_url', null);
                    // no locations: probably facebook login
                    $locations = $this->getCustomer()->getLocations();
                    if (!$locations || count($locations) == 0) {
                        $redirectUrl = '/user/settings';
                        // don't redirect from loginfailed to loginfailed
                    } elseif (is_null($redirectUrl) || strstr($redirectUrl, 'loginfailed') || 'http://' . HOSTNAME . '/' == $redirectUrl) {
                        $redirectUrl = $this->getCustomer()->getStartUrl();
                    }
                    return $this->_redirect($redirectUrl);
                }
                break;

            default:
                return $this->_redirect('/user/loginfailed');
        }
    }

    /**
     * delete the session and remove piwik tracking for this customer
     * @author Matthias Laug <laug@lieferando.de>
     * @since ever
     */
    public function logoutAction() {
        $this->setupLogout();

        //piwik tracking and siable this customer variable
        Yourdelivery_Model_Piwik_Tracker::trackCustomVariable(
                1, 'customerId', null
        );
        return $this->_redirect($this->getRequest()->getParam('redirect_url', '/'));
    }

    public function loginfailedAction() {
        $meta[] = '<meta name="robots" content="noindex,follow" />';
        $this->view->assign('additionalMetatags', $meta);
    }

    /**
     * overview of the customer
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.11.2011
     */
    public function indexAction() {
        if (!$this->getCustomer()->isLoggedIn()) {
            return $this->_redirect('/');
        }
        $this->view->mode = "rest";
        $this->view->news = Default_Helpers_Web::getBlogNews($this->config->domain->base);
        $this->view->ordered = (integer) count($this->getCustomer()->getTable()->getOrders());
        $this->view->rated = (integer) count(Yourdelivery_Model_DbTable_Customer::getRatedOrders($this->getCustomer()));
    }

    /**
     * action to change all settings mainly after a facebook register
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    public function allSettingsAction() {
        $customer = $this->getCustomer();
        if (!$customer->isLoggedIn()) {
            return $this->_redirect('/');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            // A wrapper for *.pl
            if (substr($this->config->domain->base, -3) == '.pl') {
                // Reseting plz and cityId, which may be incorrect or unset - must be verified on server side
                $post['cityId'] = '0';
                $post['plz'] = '';
                if (isset($post['city'], $post['street'], $post['hausnr'])) {
                    // now we can try to override plz/cityId, as all needed data have been passed
                    $cityVerbose = new Yourdelivery_Model_City_Verbose();
                    // first try - trying without hausnr (as we don't know here, where current street has defined ranges)
                    $matches = $cityVerbose->findmatch($post['city'], $post['street']);
                    if (count($matches) > 1) {
                        // second try: we must extract first numeric fragment from hausnr
                        $matches = $cityVerbose->findmatch(
                                $post['city'], $post['street'], $post['hausnr']
                        );
                    }
                    if (count($matches) == 1) {
                        $data = array_pop($matches);
                        $post['cityId'] = $data['cityId'];
                        $post['plz'] = $data['cep'];
                    }
                }
            }

            $form = new Yourdelivery_Form_User_AllSettings();
            $form->initEmail($customer->getId());
            
            if (!$form->isValid($post)) {
                $values = $form->getValues();
                $customer->setData($values);
                $this->error($form->getMessages());
                $this->logger->debug('form errors while trying to update settings after facebook registration');
                return;
            } else {

                $values = $form->getValues();
                //check if those are matching
                $cityId = $form->getValue('cityId');
                if (empty($cityId)) {

                    $plz = $form->getValue('plz');
                    $cityIds = array_map(function($a) {
                                return $a['id'];
                            }, Yourdelivery_Model_City::getByPlz($plz)->toArray());

                    if (count($cityIds) == 0) {
                        $this->error(__('Bitte überprüfen sie ihre PLZ'));
                        $this->view->post = $post;
                        $this->logger->debug(__('Bitte überprüfen sie ihre PLZ'));
                        return;
                    }
                    $values['cityId'] = (integer) current($cityIds);
                }
                $customer->setData($values);
                $customer->addAddress($values);
                $customer->save();

                return $this->_redirect('/user/registered');
            }
        } else {
            Yourdelivery_Model_Piwik_Tracker::trackGoal('registerpage');
        }
        $meta[] = '<meta name="robots" content="noindex,follow" />';
        $this->view->assign('additionalMetatags', $meta);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function profileAction() {
        if (!$this->getCustomer()->isLoggedIn()) {
            return $this->_redirect('/');
        }
        $this->getCustomer()->getFidelity()->clearCache();
        if ($this->getCustomer() instanceof Yourdelivery_Model_Customer && strlen($this->getRequest()->getParam('delete')) > 0) {
            $this->getCustomer()->deleteProfileImage();
            $this->success(__('Bild erfolgreich entfernt'));
            $this->_redirect('/user/' . $this->getRequest()->getParam('redirect', 'index'));
        } else {
            $this->uploadProfilePicture(true, $this->getRequest()->getParam('redirect', 'index'));
        }
    }

    /**
     * upload a profile picture. Used on different occasions
     * so we capsulate it into this method
     * @author Matthias Laug <laug@lieferando.de>
     * @since 18.11.2011
     * @param boolean $redirect
     */
    private function uploadProfilePicture($redirect = false, $redirectTo = 'index') {
        if (!$this->getCustomer()->isLoggedIn()) {
            return $this->_redirect('/');
        }
        $form = new Yourdelivery_Form_User_Profile();
        $post = $this->getRequest()->getPost();
        if ($form->isValid($post) && $form->img && $form->img->receive() && $form->img->isUploaded()) {
            $file = $form->img->getFileName();
            try {
                $customer = $this->getCustomer();
                $customer->addImage($file, true);
                $this->success(__('Profilbild erfolgreich aktualisiert'));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                
            }
        } else {
            $this->error(__('Konnte Profilbild nicht aktualisieren. Unterstützt werden JPG, GIF, PNG Dateien bis zu einer Größe von 2MB'));
            foreach ($form->getMessages() as $msg) {
                $this->logger->warn(sprintf('could not validate form for image upload: %s', array_pop($msg)));
            }
        }
        $redirect ? $this->_redirect('/user/' . $redirectTo) : null;
    }

    /**
     * List all favourites
     * create or delete one
     * @author vpriem
     * @since 06.05.2011
     */
    public function favouritesAction() {

        $customer = $this->getCustomer();
        if (!$customer->isLoggedIn()) {
            return $this->_redirect('/');
        }

        // get request
        // and delete favourite
        $request = $this->getRequest();
        $this->view->mode = "rest";
        $this->view->services = Yourdelivery_Model_DbTable_Favourites::getAllRestaurantIds($customer->getId(), false);
    }

    /**
     * List all orders of the current user
     * @author vpriem
     * @modified daniel
     * @since 06.05.2011, 17.11.2011
     */
    public function ordersAction() {
        $customer = $this->getCustomer();
        if (!$customer->isLoggedIn()) {
            return $this->_redirect('/');
        }

        $pagination = $this->getRequest()->getParam('perPageuser_orders');

        if (!$pagination) {
            $pagination = 25;
        }

        $this->view->assign('pagination', $pagination);

        // make the account tab active
        $this->view->assign('account', 'active');

        $select = $customer->getTable()->getOrdersSelect();

        // get grid
        $grid = Default_Helper::getTableGrid();
        //$grid->setPaginationInterval( array(25=> 25, 50 => 50, 100 => 100));
        if ($pagination > 1000) {
            $grid->setRecordsPerPage(1000);
        } else {
            $grid->setRecordsPerPage($pagination);
        }

        if (Zend_Registry::isRegistered('cache')) {
            $grid->setCache(array('use' => 1, 'instance' => $this->cache, 'tag' => 'grid'));
        }
        $grid->setGridId('user_orders'); //??
        $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        $grid->updateColumn('Id', array('hidden' => 1));
        $grid->updateColumn('ID', array('hidden' => 1));
        $grid->updateColumn('RID', array('hidden' => 1));
        $grid->updateColumn('RATED', array('hidden' => 1));
        $grid->updateColumn('RATEABLE', array('hidden' => 1));
        $grid->updateColumn('STATE', array('hidden' => 1));
        $grid->updateColumn('HausNr', array('title' => __('HausNr'), 'hidden' => 1));
        $grid->updateColumn('Bestellzeit', array('title' => __('Bestellzeit')));
        $grid->updateColumn('Preis', array('title' => __('Preis') ,'callback' => array('function' => 'intToPrice', 'params' => array('{{Preis}}')), 'decorator' => '{{Preis}}&nbsp;' . __('€')));
        $grid->updateColumn('Bestellnummer', array('title' => __('Bestellnummer'), 'class' => 'state', 'callback' => array('function' => 'checkForStorno', 'params' => array('{{STATE}}', '{{Bestellnummer}}'))));
        $grid->updateColumn('mode', array('hidden' => 1));
        $grid->updateColumn('kind', array('hidden' => 1));
        $grid->updateColumn('NR', array('hidden' => 1));
        $grid->updateColumn('Lieferservice', array('title' => __('Lieferservice'), 'class' => 'searchable'));
        $grid->updateColumn('Lieferadresse', array('title' => __('Lieferadresse'), 'class' => 'searchable'));
        $grid->updateColumn('Speisen', array('title' => __('Speisen'), 'class' => 'searchable'));
        $grid->setExport(array());
        // add filters
        $filters = new Bvb_Grid_Filters();

        $grid->addFilters($filters);

        $favourites = array();
        $favouritesRow = $customer->getTable()->getFavourites();
        foreach ($favouritesRow as $fav) {
            $favourites[] = $fav->orderId;
        }
        // add options
        $option = new Bvb_Grid_Extra_Column();
        $option->position('right')
                ->name(__('Optionen'))
                ->callback(array('function' => 'checkFavourite', 'params' => array($favourites, '{{kind}}', '{{mode}}', '{{ID}}', '{{STATE}}', '{{RID}}', $customer, '{{RATED}}', '{{RATEABLE}}')));

        //             ->decorator('{{favourites}}'); // have to add a fewstuff... order repeat wont work
        $grid->addExtraColumns($option);

        // deploy grid to view
        $this->view->grid = $grid->deploy();

        $this->view->disableElement('budget');
        $this->view->disableElement('budget_box');
        $this->view->disableElement('ordering-breadcrumbs');
    }

    /**
     * alter settings of user
     * @author olli
     * @modified Daniel Hahn <hahn@lieferando.de>
     */
    public function settingsAction() {
        $customer = $this->getCustomer();
        if (!$customer->isLoggedIn()) {
            return $this->_redirect('/');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form = new Yourdelivery_Form_User_Settings();
            $form->initEmail($customer->getId());

            if ($form->isValid($request->getPost())) {
                $oldEmail = $customer->getEmail();

                $values = $form->getValues();
                $email = $values['email'];
                $newpw = $values['newpw'];
                $prename = $values['prename'];
                $name = $values['name'];

                // if ne email differs from existing, we have to migrate existing fidelity transactions
                if (strtolower($email) != strtolower($oldEmail)) {
                    // migrate fidelity points / transactions
                    $customer->getFidelity()->migrateToEmail($email);
                    $customer->setNewsletter(false);
                }

                //change birthday
                $day = (integer) $values['birthday_day'];
                $month = (integer) $values['birthday_month'];
                $year = (integer) $values['birthday_year'];
                if ($day > 0 && $month > 0 && $year > 1950) {
                    $birthday = date('Y-m-d', strtotime($day . '.' . $month . '.' . $year . '.'));
                    $customer->setBirthday($birthday);
                }

                $tel = $values['tel'];
                $sex = $values['sex'];
                $newsletter = $values['newsletter'];

                /**
                 * change data only if there was valid input
                 * and the new email is not in the fidelity-table
                 */
                if (!$customer->isEmployee() && strlen($email) != 0 && $email != $customer->getEmail()) {
                    $customer->setEmail($email);
                }

                /**
                 * change data only if there was valid input
                 */
                if (!is_null($newpw) && !empty($newpw)) {
                    $customer->setPassword(md5($newpw));
                }
                if (!is_null($values['birthday']) && !empty($values['birthday'])) {
                    $customer->setBirthday($birthday);
                }

                $customer->setTel($tel);
                $customer->setSex($sex);
                $customer->setName($name);
                $customer->setPrename($prename);
                $customer->setNewsletter((boolean) $newsletter);
                $customer->save();

                // update cookies
                $customer->login();
                $customer->getFidelity()->clearCache();
                $customer->clearCache();
                $this->success(__('Daten erfolgreich geändert.'));
                return $this->_redirect('/user/settings');
            } else {
                $this->view->postData = $request->getPost();
                $this->error($form->getMessages());
            }
        }

        // make the account tab active
        $this->view->assign('account', 'active');
    }

    /**
     * Show customer locations
     * @author vpriem
     * @since 16.03.2011
     */
    public function locationsAction() {
        $customer = $this->getCustomer();

        if (!$customer->isLoggedIn()) {
            return $this->_redirect('/');
        }

        // delete a location
        $request = $this->getRequest();
        $del = $request->getParam('del');
        if ($del !== null) {
            $del = (integer) $del;
            $location = new Yourdelivery_Model_Location($del);
            if ($location->getCustomerId() == $customer->getId()) {
                if ($location->remove()) {
                    $this->success(__('Adresse erfolgreich gelöscht'));
                } else {
                    $this->error(__('Adresse konnte nicht gelöscht werden'));
                }
            }
            return $this->_redirect('/user/locations/');
        }
    }

    /**
     * list all transactions
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.11.2011
     */
    public function fidelityAction() {
        if (!$this->getCustomer()->isLoggedIn()) {
            return $this->_redirect('/');
        }
    }

    /**
     * facebook settings
     * @author Jens Naie <naie@lieferando.de>
     * @since 11.07.2012
     */
    public function socialnetworksAction() {
        $customer = $this->getCustomer();

        if (!$this->getCustomer()->isLoggedIn()) {
            return $this->_redirect('/');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form = new Yourdelivery_Form_User_Socialnetworks();

            if ($form->isValid($request->getPost())) {
                $values = $form->getValues();

                if (empty($values['facebookId']) && $customer->getFacebookId()) {
                    // set facebook post back to default value
                    // if the user reconnects it will be set already
                    $customer->setFacebookPost(1);
                    $customer->setFacebookId(null);
                    // TODO: cancel points doesn't work!
                    $customer->getFidelity()->cancelTransactionByAction('facebookconnect');
                } else {
                    $customer->setFacebookPost((boolean) $values['facebookPost']);
                }
                $customer->save();

                $customer->clearCache();
                $this->success(__('Daten erfolgreich geändert.'));
                if (!$customer->getFacebookId() && !$customer->getPassword()) {
                    return $this->_redirect('/user/settings');
                } else {
                    return $this->_redirect('/user/socialnetworks');
                }
            } else {
                $this->view->postData = $request->getPost();
                $this->error($form->getMessages());
            }
        }

        // make the account tab active
        $this->view->assign('account', 'active');
    }

    /**
     * setup enviroment for user
     * @param string $customer
     * @return boolean
     */
    private function setupLogin($customer) {
        try {
            if (!$this->getCustomer()->isLoggedIn(true)) {
                $customer = new Yourdelivery_Model_Customer(null, $customer);

                //store id in session
                $this->resetCustomer();
                $this->session->customerId = $customer->getId();

                //increase count of logins
                $count = intval($customer->getCountLogin());
                $customer->setLastlogin(date('Y-m-d H:i:s'));
                $customer->setCountLogin(++$count);
                $customer->save();

                $customer->login();
                Yourdelivery_Model_Piwik_Tracker::trackCustomVariable(1, 'customerId', $customer->getId());
                return true;
            }

            $this->logger->info('User tried to login, but was already in session. just return, redirect and see what happend');
            return true;
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            
        }
        return false;
    }

    /**
     * User registration
     * @author vpriem
     * @since 09.03.2011
     */
    public function registerAction() {
        // if user is logged in, redirect to the settings page
        if ($this->getCustomer()->isLoggedIn()) {
            $this->logger->debug('customer is already logged in');
            return $this->_redirect('/user/settings');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
            // A wrapper for *.pl
            if (substr($this->config->domain->base, -3) == '.pl') {
                // Reseting plz and cityId, which may be incorrect or unset - must be verified on server side
                $post['cityId'] = '0';
                $post['plz'] = '';
                if (isset($post['city'], $post['street'], $post['hausnr'])) {
                    // now we can try to override plz/cityId, as all needed data have been passed
                    $cityVerbose = new Yourdelivery_Model_City_Verbose();
                    // first try - trying without hausnr (as we don't know here, where current street has defined ranges)
                    $matches = $cityVerbose->findmatch($post['city'], $post['street']);
                    if (count($matches) > 1) {
                        // second try: we must extract first numeric fragment from hausnr
                        $matches = $cityVerbose->findmatch(
                                $post['city'], $post['street'], $post['hausnr']
                        );
                    }
                    if (count($matches) == 1) {
                        $data = array_pop($matches);
                        $post['cityId'] = $data['cityId'];
                        $post['plz'] = $data['cep'];
                    }
                }
            }

            $form = new Yourdelivery_Form_Register();
            if (!$form->isValid($post)) {
                $this->error($form->getMessages());
                $this->view->post = $post;
                $this->logger->debug('form errors while trying to register');
                return;
            } else {
                $values = $form->getValues();

                //check if those are matching
                $cityId = $form->getValue('cityId');
                if (empty($cityId)) {

                    $plz = $form->getValue('plz');
                    $cityIds = array_map(function($a) {
                                return $a['id'];
                            }, Yourdelivery_Model_City::getByPlz($plz)->toArray());

                    if (count($cityIds) == 0) {
                        $this->error(__('Bitte überprüfen sie ihre PLZ'));
                        $this->view->post = $post;
                        $this->logger->debug(__('Bitte überprüfen sie ihre PLZ'));
                        return;
                    }
                    $values['cityId'] = (integer) current($cityIds);
                }

                // check if password is same as name or prename
                if (stripos($values['name'], $values['password']) !== false || stripos($values['prename'], $values['password']) !== false) {
                    $this->error(__('Ihr Passwort darf keine Teile Ihres Vor- oder Nachnamens enthalten.'));
                    $this->view->post = $post;
                    $this->logger->debug(__('Ihr Passwort darf keine Teile Ihres Vor- oder Nachnamens enthalten.'));
                    return;
                }

                $id = Yourdelivery_Model_Customer::add($values);

                if (is_null($id)) {
                    $this->error(__('Der Benutzeraccount konnte nicht angelegt werden.'));
                    $this->view->post = $post;
                    $this->logger->err('Customer::add: Customer could not be created');
                    return;
                }

                $this->session->customerId = $id;
                $customer = new Yourdelivery_Model_Customer($id);
                $customer->setNewsletter(true);
                $customer->addAddress($values);
                $customer->login();

                //send out registration email
                $email = new Yourdelivery_Sender_Email_Template('register');
                $email->setSubject(__('Registrierung auf %s', $this->config->domain->base));
                $email->addTo($customer->getEmail(), $customer->getFullname());
                $email->assign('cust', $customer);
                $email->send();

                return $this->_redirect('/user/registered');
            }
        } else {
            $this->logger->debug('no post');
            // if email was provided as get parameter
            // assign it to the form
            $email = $request->getParam('email');
            if ($email !== null) {
                $this->view->post = compact("email");
            }
            Yourdelivery_Model_Piwik_Tracker::trackGoal('registerpage');
        }
        $meta[] = '<meta name="robots" content="noindex,follow" />';
        $this->view->assign('additionalMetatags', $meta);
    }

    /**
     * a landing page for customers, who have successfully registered
     * @author Matthias Laug <laug@lieferando.de>
     * @since 06.10.2011
     */
    public function registeredAction() {
        Yourdelivery_Model_Piwik_Tracker::trackGoal('registered');
        //redirect after 3 seconds
        $meta = array();
        $meta[] = '<meta http-equiv="refresh" content="3; URL=/user/settings">';
        $meta[] = '<meta name="robots" content="noindex,follow" />';
        $this->view->assign('additionalMetatags', $meta);
        $this->view->customer = $this->getCustomer();
    }

    /**
     * destroy user's enviroment
     * @return boolean
     */
    private function setupLogout() {
        if ($this->getCustomer()->isLoggedIn()) {
            $customer = $this->getCustomer();
            $customer->getFidelity()->clearCache();
            if ($customer instanceof Yourdelivery_Model_Customer || is_subclass_of($customer, "Yourdelivery_Model_Customer")) {
                $customer->clearCache();
            }
            $this->session->unsetAll();
            $this->resetCustomer();
            Default_Helpers_Web::deleteCookie('YD_UID');
            Default_Helpers_Web::deleteCookie('yd-customer');
            //$this->info(__('Sie haben sich erfolgreich abgemeldet'));
            Yourdelivery_Model_Piwik_Tracker::trackCustomVariable(1, 'customerId', 0);

            return true;
        }
        $this->warn(__('Fehler beim abmelden'));
        return false;
    }

    /**
     * unsubscribe from newsletter via form
     * @author Matthias Laug <laug@lieferando.de>
     * @since 24.05.2011
     */
    public function abmeldenAction() {
        $this->notSeoRelevant();

        $reasons = Yourdelivery_Model_Newsletter_OptOutReasons::getAll();
        $this->view->reasons = $reasons;
        $this->view->signoff = false;

        $request = $this->getRequest();
        $email = $request->getParam('email');
        $reason = $request->getParam('reason');

        if ($email !== null) {

            if (!array_key_exists($reason, $reasons)) {
                $reason = false;
            }

            if (!Default_Helper::email_validate($email)) {
                $this->warn(__('Bitte geben Sie eine gültige E-Mail-Adresse ein'));
            } else {
                $table = new Yourdelivery_Model_DbTable_Newsletterrecipients();
                $where = $table->getAdapter()->quoteInto('email = ?', $email);
                if ($table->fetchAll($where)->count() == 0) {
                    $newsletterEntry = new Yourdelivery_Model_Newsletterrecipients();
                    $newsletterEntry->setEmail($email);
                    $newsletterEntry->setStatus(0);
                    if ($reason) {
                        $newsletterEntry->setOptOutReason($reason);
                    }
                    $newsletterEntry->save();
                } else {
                    $anonym = new Yourdelivery_Model_Anonym();
                    $anonym->setEmail($email);
                    $anonym->setNewsletter(false, false, $reason);
                }
                $this->logger->info(sprintf('user %s unsubscribed from newsletter', $email));
                $this->view->signoff = true;
            }
        }
        $this->view->email = $email;
    }

    /**
     * confirm the email adress
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.04.2011
     */
    public function confirmemailAction() {
        $request = $this->getRequest();
        $hash = $request->getParam('hash');
        if (is_string($hash)) {
            $row = Yourdelivery_Model_DbTable_Newsletterrecipients::findByEmailHash($hash);
            if (is_array($row)) {
                $cust = new Yourdelivery_Model_Anonym();
                $cust->setEmail($row['email']);
                $cust->setNewsletter(true, true);
                return $this->_redirect('/double-opt-in');
            }
        }
        return $this->_redirect('/double-opt-in-fail');
    }

    /**
     * show form (with thumbs) for rating an order
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 23.05.2011
     */
    public function bewertenAction() {
        $request = $this->getRequest();
        $hash = $request->getParam('hash');
        $adviselink = $request->getParam('adviselink', null);

        $order = Yourdelivery_Model_Order::createFromHash($hash);

        if (!is_object($order)) {
            $this->logger->info(sprintf('cannot rate order for hash %s', $hash));
            /**
             * this message will never appear because in production index-page is cached
             * @TODO we have to find a solution here
             */
            $this->warn(__('Konnte Bestellung nicht finden'));
            return $this->_redirect('/');
        }

        if (!$order->isRateable()) {
            $this->logger->warn(sprintf('user tried to rate order #%s, which is not rateable any more', $order->getId()));
            if ($this->getCustomer()->isLoggedIn()) {
                $this->error(__('Die Bestellung kann nicht mehr bewertet werden.'));
                return $this->_redirect('/user/ratings');
            } else {
                return $this->_redirect('/#order-not-rateable');
            }
        }

        if ($order->getState() <= 0) {
            $this->logger->warn(sprintf('user tried to rate order #%s, which was not affirmed', $order->getId()));
            $this->warn(__('Konnte Bestellung nicht finden'));
            return $this->_redirect('/');
        }

        $customer = $this->getCustomer();

        if ($order->getCustomerId() == $customer->getId()) {
            $this->view->assign('isLoggedIn', 1);
        }

        $this->view->assign('order', $order);

        if (is_null($adviselink)) {
            $this->logger->debug('adviselink is null');
            // check, if order is already rated
            if ($order->isRated()) {
                /**
                 * customer came from system and order is rated already,
                 * so we show the saved values
                 */
                $rating = $order->getRating();
                if ($rating[0]['advise'] == 1) {
                    $this->view->assign('activeyes', 'active');
                    $this->view->assign('checked', "1");
                } else {
                    $this->view->assign('activeno', 'active');
                    $this->view->assign('checked', '0');
                }

                $this->view->assign('step1', '0');
                $this->view->assign('step2', '1');
                $this->view->assign('step3', '2');
            } else {
                /**
                 * customer came from system and order is NOT rated
                 * we preselect advise YES
                 */
                $this->view->assign('activeyes', 'active');
                $this->view->assign('checked', '1');
                $this->view->assign('step1', '1');
                $this->view->assign('step2', '2');
                $this->view->assign('step3', '3');
            }
        } else {
            /**
             * customer comes from email and has link to advise YES or NO in url
             */
            if (md5(SALT . 'no') == $adviselink) {
                $this->logger->debug('adviselink NO');
                $this->view->assign('activeno', 'active');
                $this->view->assign('checked', '0');
            } else {
                $this->logger->debug('adviselink YES');
                $this->view->assign('activeyes', 'active');
                $this->view->assign('checked', '1');
            }

            /**
             * if customer comes from email,
             * there must be only 2 more optional steps,
             * because advise is preselected via link
             */
            $this->view->assign('step1', '0');
            $this->view->assign('step2', '1');
            $this->view->assign('step3', '2');
        }

        if ($order->isRated()) {
            $rating = $order->getRating();
            $this->view->assign('rating', $rating[0]);
        }


        $mealIds = $order->getMealIds();
        $meals = array();
        foreach ($mealIds as $id) {
            try {
                $meals[$id] = new Yourdelivery_Model_Meals($id);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
        }
        $this->view->assign('meals', $meals);

        //store rating
        $form = new Yourdelivery_Form_User_RateOrder();
        if ($request->isPost()) {
            $post = $request->getPost();
            $meals_post_ids = $post['meal'];

            //validate
            $ids = array_diff(array_keys($meals_post_ids), $mealIds);
            $meal_values = array_diff(array_values($meals_post_ids), array(0, 1, 2, 3, 4, 5));

            if ($form->isValid($post) && count($ids) == 0 && count($meal_values) == 0) {
                $order->rate($order->getCustomer()->getId(), $form->getValue('rate-1'), $form->getValue('rate-2'), $form->getValue('comment'), $form->getValue('title'), $form->getValue('advise'), $form->getValue('author'));

                foreach ($meals as $meal) {
                    $meal->getRatings()->addRating($order, $meals_post_ids[$meal->getId()], "");
                }

                if ($customer instanceof Yourdelivery_Model_Customer || is_subclass_of($customer, "Yourdelivery_Model_Customer")) {
                    $customer->getFidelity()->clearCache();
                    $customer->clearCache();
                }

                if ($this->getCustomer()->isLoggedIn()) {
                    $this->success(__('Bestellung wurde erfolgreich bewertet'));
                    return $this->_redirect('/user/ratings');
                } else {
                    //logged out customers, will get the "thank you" page
                    return $this->_redirect('/thankyou');
                }
            } else {
                foreach ($form->getErrors() as $err) {
                    Default_View_Notification::warn($err);
                }
                die();
            }
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function ratingsAction() {
        $customer = $this->getCustomer();
        if (!$customer->isLoggedIn()) {
            return $this->_redirect('/');
        }

        $request = $this->getRequest();
        $start = $request->getParam('start');
        $limit_unrated = $request->getParam('limitunrated');
        $limit_rated = $request->getParam('limitrated');

        if (!is_numeric($limit_unrated) || $limit_unrated < 0) {
            $limit_unrated = 25;
        }
        if (!is_numeric($limit_rated) || $limit_rated < 0) {
            $limit_rated = 10;
        }
        if (!is_numeric($start) || $start < 0) {
            $start = 0;
        }

        $this->view->unrated = $customer->getUnratedOrders($limit_unrated, $start);
        $this->view->rated = $customer->getRatedOrders($limit_rated, $start);

        $this->view->unratedCount = $customer->getUnratedOrdersCount();
        $this->view->ratedCount = $customer->getRatedOrdersCount();
    }

    /**
     * show thank-you-page to customer after rating an order
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06/2010
     */
    public function dankeAction() {
        if ($this->getCustomer()->isLoggedIn()) {
            return $this->_redirect('/user/ratings');
        }

        Yourdelivery_Model_Piwik_Tracker::trackGoal('ratedorder');
        $meta[] = '<meta name="robots" content="noindex,follow" />';
        $customer = $this->getCustomer();
        if ($customer->isLoggedIn()) {
            $unrated = Yourdelivery_Model_DbTable_Customer::getUnRatedOrders($customer->getId());

            // print_r($unrated); die();
            if ($unrated[0]) {
                $this->view->assign('unrated', $unrated[0]);
            }
        }
        $this->view->assign('additionalMetatags', $meta);
        $this->view->customer = $customer;
    }
    
    /**
     * show thank-you-page to customer after rating an order
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06/2010
     */
    public function optinAction() {

        $meta[] = '<meta name="robots" content="noindex,follow" />';
        $customer = $this->getCustomer();
        $this->view->assign('additionalMetatags', $meta);
        $this->view->customer = $customer;
    }

    /**
     * show html emails for logged-in customer here
     */
    public function emailAction() {
        // find content by md5
        $hash = $this->getRequest()->getParam('my', null);

        $id = Yourdelivery_Model_DbTable_Emails::findByEmailTimeHash($hash);

        if (!$id) {
            $this->warn(__('Die angeforderte Seite ist nicht verfügbar'));
            return $this->_redirect('user/settings');
        }

        try {
            $mail = new Yourdelivery_Model_Emails($id);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->warn(__('Die angeforderte Seite ist nicht verfügbar'));
            return $this->_redirect('user/settings');
        }
        $this->view->assign('email', $mail->getContent());
    }

    /**
     * get the order by hash or redirect to start page
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.03.2012
     * @param string $hash
     * @return Yourdelivery_Model_Order|redirect
     */
    private function getOrderByHash($hash) {
        $order = Yourdelivery_Model_Order::createFromHash($hash);

        if (!$order instanceof Yourdelivery_Model_Order_Abstract) {

            $this->_redirect('/');
        }
        return $order;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.03.2012
     */
    public function billrequestAction() {
        $this->view->hash = $hash = $this->getRequest()->getParam('hash');
        $order = $this->getOrderByHash($hash);
        $form = new Yourdelivery_Form_User_BillRequest();
        $form->setAction('/user/billcreate/hash/' . $hash);
        $form->populate(array(
            'street' => $order->getLocation()->getStreet(),
            'hausnr' => $order->getLocation()->getHausnr(),
            'plz' => $order->getLocation()->getPlz(),
            'cityId' => $order->getLocation()->getCity()->getId(),
            'prename' => $order->getCustomer()->getPrename(),
            'name' => $order->getCustomer()->getName(),
            'companyName' => $order->getLocation()->getCompanyName()
        ));
        $this->view->form = $form;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 29.03.2012
     */
    public function billcreateAction() {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $this->logger->warn('try to hit billcreate page without post, redirecting to frontpage');
            return $this->_redirect('/');
        }

        $hash = $request->getParam('hash', 0);

        $order = $this->getOrderByHash($hash);

        $form = new Yourdelivery_Form_User_BillRequest();
        if (!$form->isValid($request->getPost())) {
            $this->error(Default_View_Notification::array2html($form->getMessages()));
            return $this->_redirect('/user/billrequest/hash/' . $hash);
        }

        //overwrite with form data
        $order->getLocation()->setStreet($form->getValue('street'));
        $order->getLocation()->setCityId($form->getValue('cityId'));
        $order->getLocation()->setHausnr($form->getValue('hausnr'));
        $order->getLocation()->setCompanyName($form->getValue('companyName'));
        $order->getCustomer()->setPrename($form->getValue('prename'));
        $order->getCustomer()->setName($form->getValue('name'));

        $this->logger->info(sprintf('create certified bill for order #%s', $order->getId()));

        $bill = new Yourdelivery_Model_Billing_Order($order->getCustomer());
        $bill->addOrder($order);
        $bill->until = $order->getTime();
        $bill->create();
        $pdf = $bill->file;

        if (file_exists($pdf)) {
            $this->logger->info(sprintf('sending out bill for order #%s to %s', $order->getId(), $order->getCustomer()->getEmail()));
            Yourdelivery_Sender_Email::verifyPdf($order->getCustomer()->getEmail(), $pdf);
            return $this->_redirect('/user/billconfirm');
        } else {
            $this->logger->crit(sprintf('could not create certified bill for order #%s', $order->getId()));
            $this->info(__('Ihre Rechnung konnte leider nicht generiert werden. Bitte wenden sie sich an den Support'));
            return $this->_redirect('/');
        }
    }

    /**
     * create a bill for a given list of orders
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.12.2010
     */
    public function billconfirmAction() {
        $meta[] = '<meta name="robots" content="noindex,follow" />';
        $this->view->assign('additionalMetatags', $meta);
    }

}
