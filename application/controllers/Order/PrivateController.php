<?php

require_once( APPLICATION_PATH . "/controllers/Order/BasisController.php");

/**
 * @author mlaug
 */
class Order_PrivateController extends Order_BasisController {

    /**
     * @var string
     */
    protected $_orderClass = "Yourdelivery_Model_Order_Private";

    /**
     * @author vpriem
     * @since 26.07.2011
     */
    public function init() {
        parent::init();

        //make sure we have the kind in the view
        $this->view->kind = 'priv';
        $state = Yourdelivery_Cookie::factory('yd-state');
        $state->set('kind', 'priv');
        $state->save();
    }

    protected function _preFinish(Yourdelivery_Model_Order_Abstract $order, array &$post) {
        // set current payment method
        if (!in_array($post['payment'], array('bar', 'credit', 'paypal', 'ebanking', 'bill'))) {
            $this->logger->info('switching back to bar, no valid payment selected');
            $post['payment'] = 'bar';
        }

        try {
            /**
             * when amount of order is completely covered
             * we don't have to check, if payment is allowed
             * because there is no payment needed
             * 
             * @author Felix Haferkorn
             * @since 17.04.2012 
             */
            $checkPayment = true;
            if ($order->getAbsTotal() <= 0) {
                $checkPayment = false;
            }
            $order->setPayment($post['payment'], $checkPayment);
        } catch (Yourdelivery_Exception $e) {
            $this->logger->info('selected bar payment with discount, do not allow that!');
//            $this->error(__('Mit einem Gutschein kann leider keine Barzahlung ausgewählt werden'));
            $this->error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @author vpriem
     * @since 14.07.2011
     */
    protected function _finish(Yourdelivery_Model_Order_Abstract $order) {

        $form = new Yourdelivery_Form_Order_Finish_Private();

        // it's a post for sure
        $request = $this->getRequest();
        $post = $request->getPost();
        if (!$form->isValid($post)) {
            $this->error(Default_View_Notification::array2html($form->getMessages()));
            return false;
        }

        // create user after order or not
        $this->session->createUserAfterOrder = false;
        if ((boolean) $post['create_user']) {
            $this->session->createUserAfterOrder = true;
        }

        // setup location
        $location = $order->getLocation();
        $location->setStreet($form->getValue('street'));
        $appartment = $form->getValue('appartment', false) ? '/' . $form->getValue('appartment') : null;
        $location->setHausnr($form->getValue('hausnr') . $appartment);
        $location->setTel($form->getValue('telefon'));
        if ($this->config->domain->base == 'taxiresto.fr') {
            $location->setComment($order->getLocation()->getComment());
        } else {
            $location->setComment($form->getValue('comment'));
        }
        $location->setEtage($form->getValue('etage'));
        $location->setCompanyName($form->getValue('companyName'));

        // update customer
        $customer = $order->getCustomer();
        $customer->setName($form->getValue('name'));
        $customer->setPrename($form->getValue('prename'));
        $customer->setEmail($form->getValue('email'));

        // if no double opt in is configured or the customer did set the newsletter checkbox
        if ($this->config->newsletter->method == 'doubleoptin') {
            if ((boolean) $post['newsletter']) {
                $this->logger->info(sprintf('customer %s asked for newsletter while double opt in is active', $customer->getEmail()));
                $customer->setNewsletter(true);
            }
        } else {
            // if the singleoptin is used, we at least check, if the customer is not yet in the database
            if (!$customer->isInNewsletterRecipients()) {
                $this->logger->info(sprintf('customer %s asked for newsletter and checked agb. not yet in database', $customer->getEmail()));
                $customer->setNewsletter(true);
            }
        }

        // set some stuff  
        $deliverTime = $this->getRequest()->getUserParam('deliver-time') != null ? $this->getRequest()->getUserParam('deliver-time') : $form->getValue('deliver-time');
        $deliverTimeDay = $form->getValue('deliver-time-day');
        $order->setDeliverTime($deliverTime, $deliverTimeDay);
        $this->logger->debug(sprintf('setting deliver time to %s based on day: %s time: %s', $order->getDeliverTimeFormated(), $deliverTimeDay, $deliverTime));


        // check for fidelity points
        if ((integer) $post['fidelity'] == 1 && !$order->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code && $order->getCustomer()->getFidelityPoints()->getPoints() >= $order->getCustomer()->getFidelity()->getCashInNeed()) {
            $cost = $order->getMostExpensiveCost($order->getCustomer()->getFidelity()->getCashInLimit());
            if ($cost > 0) {
                $discount = Yourdelivery_Model_Rabatt::factory(Yourdelivery_Model_Rabatt::FIDELITY, array(
                            'cost' => $cost,
                            'fullname' => $this->getCustomer()->getFullname(),
                        ));
                if ($discount instanceof Yourdelivery_Model_Rabatt_Code) {
                    $order->setDiscount($discount);
                    $this->logger->debug('create discount for fidelity points and added to order');
                } else {
                    $this->logger->crit('failed to create discount for fidelity points');
                }
            } else {
                $this->logger->warn('could not find any meal for fidelity points');
            }
        }

        // this check has to be after fidelity, so customer can not cash fidelity points and do a preOrder
        $service = $order->getService();
        if ($order->getDiscount() && !$order->getCustomer()->getDiscount()) {
            $order->setDeliverTime(__('sofort'));
        }

        if (!$service->getOpening()->isOpen($order->getDeliverTime())) {
            //case where discount is used, but restaurant is not open
            if ($order->getDiscount() && !$order->getCustomer()->getDiscount() || (is_object($order->getDiscount()) && is_object($order->getCustomer()->getDiscount()) && ($order->getDiscount()->getId() != $order->getCustomer()->getDiscount()->getId()))) {
                $this->error(__('Der Gutschein kann nicht für Vorbestellungen genutzt werden'));
                $this->logger->warn(sprintf('customer tried to use discount %s where the service #%s %s is not open - unsetting discount', $order->getDiscount()->getCode(), $service->getId(), $service->getName()));
                $order->setDiscount(null);
            } else {
                $this->error(__('Der Dienstleister hat zu diesem Zeitpunkt leider geschlossen'));
                $this->logger->warn(sprintf('customer selected deliver time %s where the service #%s %s is not open', date('H:i:s d.m.Y', $order->getDeliverTime()), $service->getId(), $service->getName()));
            }
            return false;
        }

        return true;
    }

    /**
     * @author vpriem
     * @since 21.07.2011
     * TODO: grouppon
     */
    protected function _success(Yourdelivery_Model_Order_Abstract $order) {

        $customer = $this->getCustomer();
        $this->view->assign('loggedInCustomer', $customer->isLoggedIn());
        $orderCustomer = $order->getCustomer();

        // if this is an unregisterd user, and he want to register after finalizing his order
        // we may grant him this wish
        if (!$customer->isLoggedIn() && $this->session->createUserAfterOrder) {
            $this->session->createUserAfterOrder = false;

            $email = $orderCustomer->getEmail();
            try {
                $customer = new Yourdelivery_Model_Customer(null, $email);
                /**
                 * @todo: this should be checked, before calling success page
                 */
                $this->error(__('Ein Account mit dieser eMail Adresse existiert bereits. Es wurde kein neuer Account eingerichtet.'));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                //create new customer object
                $customerId = (integer) Yourdelivery_Model_Customer::add($orderCustomer->getData());
                if ($customerId > 0) {
                    $customer = new Yourdelivery_Model_Customer($customerId);

                    //track piwik goal
                    Yourdelivery_Model_Piwik_Tracker::trackGoal('created-user-after-sale');

                    // add fidelity points
                    $this->view->assign('gotfidelitypoints', true);

                    //append this user to current order
                    $order->updateCustomer($customer);

                    //login
                    $this->session->customerId = $customer->getId();
                    $customer->login();
                    $this->view->cust = $customer;

                    //add extra points, if registration is here right after sale
                    $customer->addFidelityPoint('registeraftersale', $customer->getId());

                    //save location as first location for htis user
                    $orderLocation = $order->getLocation();
                    $customer->addAddress($orderLocation->getData());
                    $pass = $customer->resetPassword();

                    //send out email
                    $email = new Yourdelivery_Sender_Email_Template('register');
                    $email->setSubject(__('Registrierung auf %s', $this->config->domain->base))
                            ->addTo($customer->getEmail(), $customer->getFullname())
                            ->assign('cust', $customer)
                            ->assign('password', $pass)
                            ->send();

                    //very nice!
                    Yourdelivery_Model_Piwik_Tracker::trackGoal('registerAfterSale');
                    $this->success(__('Account erstellt. Du erhältst Dein Passwort per E-Mail'));
                } else {
                    $this->logger->crit('account could not be created after order');
                    $this->error(__('Account konnte leider nicht erstellt werden'));
                }
            }
        }

        /**
         * For registered user we add the order location to his locations if:
         * - he doesn't have any (facebook)
         * - he doesn't have this one
         * @author Vincent Priem <priem@lieferando.de>
         * @since 15.08.2012
         */
        if ($customer->isLoggedIn()) {
            $orderLocation = $order->getLocation();
            $customerLocations = $customer->getLocations();
            
            $createLocationFromOrderLocation = true;
            foreach ($customerLocations as $customerLocation) {
                if (strcasecmp($customerLocation->getAddress(), $orderLocation->getAddress()) == 0) {
                    $createLocationFromOrderLocation = false;
                    break;
                }
            }
            
            if ($createLocationFromOrderLocation) {
                $customer->addAddress($orderLocation->getData());
            }
        }
        
        $this->view->assign('fidelity', false);
        if (!$customer->isLoggedIn()) {
            // link to register
            $this->view->assign('fidelity', true);
        }

        //flag to track ecommerce with google
        $this->view->trackGoogleEcomerce = true;

        $data = Default_Helpers_Web::getCookie('yd-channels');
        if (is_array($data)) {

            //special url set
            if (array_key_exists('com_id', $data)) {
                $com_id = (integer) $data['com_id'];
                try {

                    if ($com_id <= 0) {
                        throw new Yourdelivery_Exception_Database_Inconsistency('no valid com id');
                    }

                    $url = new Yourdelivery_Model_Marketing_Url($com_id);

                    // BESTELLWERT: order amount without discount amount
                    $pixel = str_replace('BESTELLWERT', intToPrice($order->getBucketTotal() + $order->getDeliverCost() - $order->getDiscountAmount() - $order->getTax(), 2, ','), $url->getPixel());
                    $pixel = str_replace('TRACKING_NUMMER', $order->getId(), $pixel);
                    if ($order->getDiscount() instanceof Yourdelivery_Model_Rabatt_Code && $order->getDiscount()->getParent()->getFidelity() == 0) {
                        $pixel = str_replace("RABATTCODE", $order->getDiscount()->getCode(), $pixel);
                        $pixel = str_replace("RABATTAKTIONNAME", urlencode($order->getDiscount()->getParent()->getName()), $pixel);
                    } else {
                        $pixel = str_replace("RABATTCODE", "", $pixel);
                        $pixel = str_replace("RABATTAKTIONNAME", "", $pixel);
                    }
                    $this->view->pixel = $pixel;
                    Default_Helpers_Web::deleteCookie('yd-channels');
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    //do nothing
                }
            }
        } else {
            $this->view->pixel = null;
        }

        // clear cooperation partner
        unset($this->session->partner);
    }

}
