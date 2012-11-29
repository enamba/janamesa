<?php

/**
 * Description of DiscountController
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 19.01.2012
 */
class DiscountController extends Default_Controller_Base {

    private $discountPath = 'discount';

    /**
     * set referer for all actions
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     */
    public function preDispatch() {

        $this->view->extra_css = 'discount';

        parent::preDispatch();
        
        $request = $this->getRequest();

        /** can be called from different domains, but we have to stick with that one
         * route has been defined in Bootstrap
         * @see Bootstrap
         */
        $match = explode('/', $this->getRequest()->getPathInfo());
        $path = $match[1];
        if (empty($path)) {
            $path = $request->getParam('referer', 'discount');
        }
        $this->view->discountPath = $this->discountPath = $path;


        $this->discount = Yourdelivery_Model_Rabatt::getByReferer($request->getParam('referer', null));
        if (is_null($this->discount)) {
            return $this->_redirect('/');
        }

        $this->view->referer = $this->discount->getReferer();
        $this->view->discount = $this->discount;
        $this->view->discountType = $this->discount->getType();
        $this->view->discountId = $this->discount->getId();
        $this->view->discountEnd = $this->discount->getEnd();
        $this->view->discountAmount = $this->discount->getRabatt();
        $this->view->discountKind = $this->discount->getKind();
        $this->view->minimumOrderValue = $this->discount->getMinAmount();
        if (!($this->discount instanceof Yourdelivery_Model_Rabatt) || !$this->discount->isActive()) {
            return $this->_redirect('/');
        }

        if ($this->discount->getType() == Yourdelivery_Model_Rabatt::TYPE_VERIFICATION_MANY ||
                $this->discount->getType() == Yourdelivery_Model_Rabatt::TYPE_VERIFICATION_SINGLE) {
            $this->view->verify = true;
        } else {
            $this->view->verfiy = false;
        }

        $meta = array();
        if ( $this->discountPath == 'pizza' ){
            $meta[] = '<meta name="robots" content="index,follow" />';
        }
        else{
            $meta[] = '<meta name="robots" content="noindex,follow" />';
        }
        $meta[] = '<link rel="stylesheet" type="text/css" href="/media/css/www.lieferando.de/yd-frontend-internallinks.css" />';
        $this->view->assign('additionalMetatags', $meta);
    }

    /**
     * Default Landing Page
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     */
    public function indexAction() {


        $this->view->enableCache();
        $this->_checkIfLoggedIn();
        if ($this->discount->getType() == Yourdelivery_Model_Rabatt::TYPE_LANDING_PAGE) {
            $this->view->step = 2;
        } else {
            $this->view->step = 1;
        }
    }

    /**
     * will be called from the email with a link, fallback for finished Discounts
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     */
    public function confirmAction() {

        $this->_checkIfLoggedIn();
        $this->view->step = 4;


        $request = $this->getRequest();
        $code = $request->getParam('code');

        if (strlen($code) == 0) {
            $this->error(__('eMail Adresse konnte nicht bestätigt werden'));
            return $this->_redirect(sprintf('/%s/', $this->discountPath));
        }

        $table = new Yourdelivery_Model_DbTable_RabattCheck();
        $row = $table->findByCodeEmail($code);
        //  echo $this->discount->getId(); die();
        //check if code is for current discount
        if (md5($row['codeTel'] . SALT . $this->discount->getId()) !== $code) {
            //   echo md5($row['codeTel'].SALT.$this->discount->getId()); die();
            $this->logger->warn('DISCOUNT CHECK: user tried to use code in wrong discount');
            return $this->_redirect($this->discountPath);
        }

        // init view and session
        if ($row && count($row) > 0 && empty($row['rabattCodeId'])) {
            // if it's the first time user klicks on email, save the time
            if ($row['emailConfirmed'] == 0) {
                try {
                    $check = new Yourdelivery_Model_Rabatt_Check($row['id']);
                    $check->setEmailConfirmed(date(DATETIME_DB));
                    $check->save();
                }
                catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                }
            }

            $table1 = new Yourdelivery_Model_DbTable_RabattCodesVerification();
            $verification = $table1->findById($row['rabattVerificationId']);

            $this->session->rabattCheckId = $row['id'];
            $this->view->emailConfirmed = $this->session->emailConfirmed = true;
            $this->view->discountCode = $verification['registrationCode'];
            $this->view->prename = $row['prename'];
            $this->view->name = $row['name'];
            $this->view->email = $row['email'];

            if($row['tel'] != NULL && $row['codeTel'] != NULL) {
                $this->view->tel = preg_replace("/^".$this->config->locale->telcode."/","", $row['tel']);
                $this->view->step = 5;
            }

            $this->render("index");
        } elseif ($row && count($row) > 0 && !empty($row['rabattCodeId'])) {
            $rabatt = new Yourdelivery_Model_Rabatt_Code(null, $row['rabattCodeId']);
            $this->session->rabattCheckId = $row['id'];
            $this->session->emailConfirmed = true;
            $this->view->code = $rabatt->getCode();
        } else {
            return $this->_redirect($this->discountPath);
        }
    }

    /**
     * @author mlaug
     * @since 28.04.2011
     */
    private function _checkIfLoggedIn() {
        if ($this->getCustomer()->isLoggedIn()) {
            $this->logger->warn('DISCOUNT CHECK: user already logged in');
            return $this->_redirect('/');
        }
    }

}
