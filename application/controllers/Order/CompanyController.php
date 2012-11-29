<?php

require_once( APPLICATION_PATH . "/controllers/Order/BasisController.php");

/**
 * @author mlaug
 */
class Order_CompanyController extends Order_BasisController {

    /**
     * @var string
     */
    protected $_orderClass = "Yourdelivery_Model_Order_Company";

    public function preDispatch() {
        parent::preDispatch();
        if (!$this->getCustomer()->isEmployee() || $this->config->domain->base == 'eat-star.de') {
            $this->logger->warn(sprintf('Customer #%s is no employee OR domain is eatstar, but tried to access a company page, redirecting back to private page',$this->getCustomer()->getId()));
            return $this->_redirect('/order_private/start');
        }

        //make sure we have the kind in the view
        $this->view->kind = 'comp';
        $state = Yourdelivery_Cookie::factory('yd-state');
        $state->set('kind', 'comp');
        $state->save();
    }

    protected function _preFinish(Yourdelivery_Model_Order_Abstract $order, array &$post) {

        // set current payment method
        if (!in_array($post['payment'], array('bar', 'credit', 'paypal', 'ebanking', 'bill'))) {
            $this->logger->info('switching back to bar, no valid payment selected');
            $post['payment'] = 'bar';
        }

        try {
            $order->setPayment('bill');
            if ( $order->getAbsTotal() > 0 ){
                $order->setCurrentPayment($post['payment']);
            }
        } catch (Yourdelivery_Exception $e) {
            $this->logger->info('selected bar payment with discount, do not allow that!');
            $this->error(__('Mit einem Gutschein kann leider keine Barzahlung ausgewählt werden'));
            return false;
        }

        return true;
    }

    /**
     * @author vpriem
     * @since 14.07.2011
     * @param Yourdelivery_Model_Order_Company $order
     */
    protected function _finish(Yourdelivery_Model_Order_Company $order) {

        $request = $this->getRequest();
        $post = $request->getPost();
        $form = new Yourdelivery_Form_Order_Finish_Company();

        if (!$form->isValid($post)) {
            $this->error(Default_View_Notification::array2html($form->getMessages()));
            return false;
        }

        $order->getCustomer()->setTel($form->getValue('telefon'));
        $order->getCustomer()->setEmail($form->getValue('email'));
        $order->getLocation()->setTel($form->getValue('telefon'));
        $order->getLocation()->setComment($form->getValue('comment'));
        $order->getLocation()->setEtage($form->getValue('etage'));
        $order->getLocation()->setCompanyName($form->getValue('companyName'));

        // set some stuff
        $deliverTime = $this->getRequest()->getUserParam('deliver-time') != null ? $this->getRequest()->getUserParam('deliver-time') :$form->getValue('deliver-time');
        $deliverTimeDay = $form->getValue('deliver-time-day');
        $order->setDeliverTime($deliverTime, $deliverTimeDay);
        $this->logger->debug(sprintf('setting deliver time to %s based on day: %s time: %s', $order->getDeliverTimeFormated(), $deliverTimeDay, $deliverTime));

        $service = $order->getService();
        if ( !$service->getOpening()->isOpen($order->getDeliverTime()) ){
            //case where discount is used, but restaurant is not open
            if ($this->getRequest()->getUserParam('deliver-time') != null) {
                $this->error(__('Der Gutschein kann nicht für Vorbestellungen genutzt werden'));
                $this->logger->warn(sprintf('customer tried to use discount %s  where the service is not open: %s %s', $order->getDiscount()->getCode(), $service->getId(), $service->getName()));
            } else {
                $this->error(__('Der Dienstleister hat zu diesem Zeitpunkt leider geschlossen'));
                $this->logger->warn(sprintf('customer selected a time for #%s %s, where the service is not open: %s', $service->getId(), $service->getName(), $order->getDeliverTimeFormated()));
            }
            return false;
        }

        //check if this service allows online payment
        if ( $service->isOnlycash() ){
            $this->logger->warn('User hit a service, which does not allow online payment');
            $this->error(__('Der Dienstleister %s erlaubt leider keine Online Zahlung. Bitte wählen sie einen alternativen Dienstleister'));
            return false;
        }

        //add all budgets if any
        $budgets = (array) $request->getParam('budget', array());
        foreach ($budgets as $budget) {
            list($customer, $amount, $reason) = $order->addBudget(
                    filter_var($budget['email'], FILTER_SANITIZE_EMAIL), (integer) $budget['amount'], filter_var($budget['code'], FILTER_SANITIZE_STRING), filter_var($budget['addition'], FILTER_SANITIZE_STRING)
            );
            if ($customer === null) {
                $this->logger->warn('cannot add budget to order: ' . $reason);
                $this->warn($reason);
            }
        }


        // project code handling
        $pnumber = $form->getValue('pnumber');
        $projectAddition = $form->getValue('projectAddition');
        $projectAddition2 = $form->getValue('projectAddition2');

        /**
         * CodeVariants:
         * 0 = use no projectnumbers
         * 1 = allow only given numbers
         * 2 = allow given and new numbers
         * 3 = allow new numbers
         */
        $codeVariant = $order->getCustomer()->getCompany()->getCodeVariant();

        if (!is_null($pnumber) && !empty($pnumber)) {

            switch ($codeVariant) {
                case '0': break;
                case '1': {
                        try {
                            $project = Yourdelivery_Model_Projectnumbers::findByNumber($pnumber, $order->getCustomer()->getCompany());
                            if ($project === false) {
                                throw new Yourdelivery_Exception_Database_Inconsistency();
                            }
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            $this->logger->debug('failed to find project number');
                            $this->error(__('Projektnummer konnte nicht gefunden werden'));
                            return false;
                        }

                        // special for BBDO and 3x Scholz HH
                        if ($order->getCustomer()->isEmployee() &&
                                ($order->getCustomer()->getCompany()->getId() == 1235 ||
                                $order->getCustomer()->getCompany()->getId() == 1673 ||
                                $order->getCustomer()->getCompany()->getId() == 1674 ||
                                $order->getCustomer()->getCompany()->getId() == 1675)) {
                            $projectAddition = '/' . $projectAddition . '/' . $projectAddition2;
                        }

                        $order->setProject($project);
                        $order->setProjectAddition($projectAddition);
                        break;
                    }
                case '2': {
                        $project = Yourdelivery_Model_Projectnumbers::findByNumber($pnumber, $order->getCustomer()->getCompany());

                        if ($project === false) {
                            $project = new Yourdelivery_Model_Projectnumbers();
                            $project->setNumber($pnumber);
                            $project->setCompany($order->getCustomer()->getCompany());
                            $project->save();
                        }

                        // special for BBDO and 3x Scholz HH
                        if ($order->getCustomer()->isEmployee() &&
                                ($order->getCustomer()->getCompany()->getId() == 1235 ||
                                $order->getCustomer()->getCompany()->getId() == 1673 ||
                                $order->getCustomer()->getCompany()->getId() == 1674 ||
                                $order->getCustomer()->getCompany()->getId() == 1675)) {
                            $projectAddition = '/' . $projectAddition . '/' . $projectAddition2;
                        }

                        $order->setProject($project);
                        $order->setProjectAddition($projectAddition);
                        break;
                    }
                case '3': {
                        $project = Yourdelivery_Model_Projectnumbers::findByNumber($pnumber, $order->getCustomer()->getCompany());

                        if ($project === false) {
                            $project = new Yourdelivery_Model_Projectnumbers();
                            $project->setNumber($pnumber);
                            $project->setCompany($order->getCustomer()->getCompany());
                            $project->save();
                        }

                        $projectAddition = $form->getValue('projectAddition');
                        // special for BBDO and 3x Scholz HH
                        if ($order->getCustomer()->isEmployee() &&
                                ($order->getCustomer()->getCompany()->getId() == 1235 ||
                                $order->getCustomer()->getCompany()->getId() == 1673 ||
                                $order->getCustomer()->getCompany()->getId() == 1674 ||
                                $order->getCustomer()->getCompany()->getId() == 1675)) {
                            $projectAddition = '/' . $projectAddition . '/' . $projectAddition2;
                        }

                        $order->setProject($project);
                        $order->setProjectAddition($projectAddition);
                        break;
                    }
                default: break;
            }
        } else {
            if ($order->getCustomer()->getCompany()->isCode()) {
                $this->warn(__('Sie sind verpflichtet einen Projektcode auszuwählen'));
                return false;
            }
        }

        return true;
        //if open amount, check payment
    }

    /**
     * @author vpriem
     * @since 14.07.2011
     * @param Yourdelivery_Model_Order_Abstract $order
     */
    protected function _success(Yourdelivery_Model_Order_Abstract $order) {

    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 23.08.2011
     */
    public function finishAction() {

        //we need an location id on the finish page for company
        $state = Yourdelivery_Cookie::factory('yd-state');
        $locationId = (integer) $state->get('location');
        if ($locationId <= 0) {
            $this->logger->warn('accessed finish page with mode "comp", but without a locationId in cookie');
            $this->error(__('Wir konnten leider ihre Adresse nicht ermitteln, bitte versuchen sie es noch einmal'));
            return $this->_redirect('/order_company/start');
        }

        //check if location is matching any company location
        $isValid = false;
        $validLocations = $this->getCustomer()->getCompany()->getLocations();
        foreach ($validLocations as $v) {
            if ($v->getId() == $locationId) {
                $isValid = true;
                break;
            }
        }

        //no adress found? redirect!
        if (!$isValid) {
            $this->logger->warn(sprintf('found no valid location with %d for customer %s', $locationId, $this->getCustomer()->getId()));
            $this->error(__('Wir konnten leider ihre Adresse nicht ermitteln, bitte versuchen sie es noch einmal'));
            return $this->_redirect('/order_company/start');
        }

        //check if we have budget, if this is a delivery service order
        $post = $this->getRequest()->getPost();
        if ( $this->getCustomer()->getCurrentBudget() <= 0 && $post['mode'] == 'rest'){
            $this->logger->warn('No budget, but tried to access finish page, redirecting back to private page');
            return $this->_redirect('/order_private/start');
        }

        parent::finishAction();

        /**
         * handle different views für company specials
         */
        $companyId = $this->getCustomer()->getCompany()->getId();
        switch ($companyId) {
            case 1235:
                // BBDO
                $this->logger->debug('ORDER FINISH: rendering custom view for comnpany bbdo #1235');
                $this->view->budgethtml = $this->view->fetch('order/company/finish/budgetsharing/bbdo.htm');
                $this->view->projecthtml = $this->view->fetch('order/company/finish/project/bbdo.htm');
                break;
            case 1218:
                // Houlihan Lokey
                $companyId = $this->getCustomer()->getCompany()->getId();
                $projectcodes = Yourdelivery_Model_Projectnumbers::allByCompany($companyId);
                $this->view->projectcodes = $projectcodes;
                $employeeemails = Yourdelivery_Model_Company::allEmployeesEmail($companyId);
                $this->view->employeeemails = $employeeemails;

                $this->logger->debug('ORDER FINISH: rendering custom view for comnpany houlihan lokey #1218');
                $this->view->budgethtml = $this->view->fetch('order/company/finish/budgetsharing/houlihan.htm');
                $this->view->projecthtml = $this->view->fetch('order/company/finish/project/houlihan.htm');
                break;
            case 1260:
                /**
                 * Scholz & Friends Berlin
                 * - they get a select dropdown with NWB / WB that will be saved as addition
                 */
                $this->logger->debug('ORDER FINISH: rendering custom view for comnpany scholz & friends berlin #1260');
                $this->view->budgethtml = $this->view->fetch('order/company/finish/budgetsharing/scholz_bln.htm');
                $this->view->projecthtml = $this->view->fetch('order/company/finish/project/scholz_bln.htm');
                break;
            case 1673:
                /**
                 * Scholz & Friends Hamburg
                 * - they get a drop down with the emails of all employees and have to type a 5-digit number in projectcode
                 * - projectdescription is a optional text
                 */
                $companyId = $this->getCustomer()->getCompany()->getId();
                $projectcodes = Yourdelivery_Model_Projectnumbers::allByCompany($companyId);
                $this->view->projectcodes = $projectcodes;
                $employeeemails = Yourdelivery_Model_Company::allEmployeesEmail($companyId);
                $this->view->employeeemails = $employeeemails;

                $this->logger->debug('ORDER FINISH: rendering custom view for comnpany scholz & friends hamburg #1260');
                $this->view->budgethtml = $this->view->fetch('order/company/finish/budgetsharing/scholz_hh.htm');
                $this->view->projecthtml = $this->view->fetch('order/company/finish/project/scholz_hh.htm');
                break;
            case 1674:
                /**
                 * Scholz & Friends Group
                 * - they get a drop down with the emails of all employees and have to type a 5-digit number in projectcode
                 * - projectdescription is a optional text
                 * - projectnumber text is required
                 */
                $companyId = $this->getCustomer()->getCompany()->getId();
                $projectcodes = Yourdelivery_Model_Projectnumbers::allByCompany($companyId);
                $this->view->projectcodes = $projectcodes;
                $employeeemails = Yourdelivery_Model_Company::allEmployeesEmail($companyId);
                $this->view->employeeemails = $employeeemails;

                $this->logger->debug('ORDER FINISH: rendering custom view for comnpany scholz & friends group #1674');
                $this->view->budgethtml = $this->view->fetch('order/company/finish/budgetsharing/scholz_group.htm');
                $this->view->projecthtml = $this->view->fetch('order/company/finish/project/scholz_group.htm');
                break;
            case 1675:
                // Scholz & Friends Strategy
                $companyId = $this->getCustomer()->getCompany()->getId();
                $projectcodes = Yourdelivery_Model_Projectnumbers::allByCompany($companyId);
                $this->view->projectcodes = $projectcodes;
                $employeeemails = Yourdelivery_Model_Company::allEmployeesEmail($companyId);
                $this->view->employeeemails = $employeeemails;

                $this->logger->debug('ORDER FINISH: rendering custom view for comnpany scholz & friends strategy #1675');
                $this->view->budgethtml = $this->view->fetch('order/company/finish/budgetsharing/scholz_strategy.htm');
                $this->view->projecthtml = $this->view->fetch('order/company/finish/project/scholz_strategy.htm');
                break;
            default:
                $this->view->budgethtml = $this->view->fetch('order/company/finish/budgetsharing/_default.htm');
                $this->view->projecthtml = $this->view->fetch('order/company/finish/project/_default.htm');
                break;
        }
    }

}
