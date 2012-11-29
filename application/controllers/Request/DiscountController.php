<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * ajax Actions for Discounts
 * Process:
 * 1. codeAction
 * 2. emailAction
 * -> DiscountController/confirm called from email
 * 3. telAction
 * 4. telcodeAction
 *
 * additional:
 * resendmailAction -> resend Email
 * resendsmsAction -> resend Sms
 * resendcodeAction -> resend Final Mail
 *
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 19.01.2012
 */
class Request_DiscountController extends Default_Controller_RequestBase {

    /**
     * @var string
     */
    private $errorType = null;

    /**
     * Set Referer for all Actions
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     */
    public function preDispatch() {
        // no views for this controller
        $this->_disableView();

        if ($this->getRequest()->getActionName() == "error" || $this->getRequest()->getActionName() == "randomcodefromdiscount") {
            return;
        }

        parent::preDispatch();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        $request = $this->getRequest();
        $this->discountPath = $request->getParam('referer', null);

        if (is_null($this->discountPath)) {
            $this->errorType = "referer_missing";
            $this->_forward("error", null, null, array("errorType" => "referer_missing"));
            return;
        }

        $this->discount = Yourdelivery_Model_Rabatt::getByReferer($this->discountPath);
        if (is_null($this->discount) || !($this->discount instanceof Yourdelivery_Model_Rabatt)) {
            $this->errorType = "general";
            $this->_forward("error", null, null, array("errorType" => "general"));
            return;
        }

        if (!$this->discount->isActive()) {
            $this->errorType = "disabled";
         //   $this->getRequest()->setParam("errorType", "disabled");
            $this->_forward("error", null, null, array("errorType" => "disabled"));
            return;
        }
    }

    /**
     *  Check Verification Code
     *  @author Daniel Hahn <hahn@lieferando.de>
     *  @since 19.01.2012
     */
    public function codeAction() {

        //verify Code Here

        $code = $this->getRequest()->getParam("code", null);

        if (!$code) {
            echo json_encode(array('status' => 'NOK', 'response' => __('Bitte gib einen Gutscheincode ein!')));
            return;
        }

        if (is_null($this->discount)) {
            $this->logger->info(sprintf('DISCOUNT CHECK: discount action not found'));
            echo json_encode(array('status' => 'NOK', 'response' => __('Gutscheinaktion mit dieser URL existiert nicht!')));
            return;
        }

        // Only if Discount is of Type 2 or 3
        if ($this->discount->getType() > 1) {

            $discount = Yourdelivery_Model_Rabatt_Check::getValidVerificationCode($code, $this->discount->getId());
            //check Usable
            if (!$discount) {
                echo json_encode(array('status' => 'NOK', 'response' => __('Dieser Gutscheincode ist nicht gültig.')));
                $this->logger->info(sprintf('DISCOUNT CHECK: verification code %s not found', $code));
                return;
            } elseif ($discount['send'] == 1) {
                $this->logger->info(sprintf('DISCOUNT CHECK: verification code %s already used', $code));
                echo json_encode(array('status' => 'NOK', 'response' => __('Dieser Gutscheincode ist abgelaufen oder wurde schon einmal benutzt.')));
                return;
            }
        }

        $this->logger->info(sprintf('DISCOUNT CHECK: verification code %s successfully entered', $code));

        //put in session
        $this->session->discountCode = $code;

        echo json_encode(array('status' => 'OK'));
        return;
    }

    /**
     * Check Email and send it out
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     *
     * @return json
     */
    public function emailAction() {

        $request = $this->getRequest();
        $form = new Yourdelivery_Form_RegisterDiscount();
        $post = $request->getPost();

        if (!$this->session->discountCode && $this->discount->getType() != 1) {
            echo json_encode(array('status' => 'NOK', 'response' => 'NOCODE'));
            return;
        }

        if ($request->isPost() && $form->isValid($post)) {
            $values = $form->getValues();

            $email = $values['email'];

            // Only if Discount is of Type 2 or 3
            if ($this->discount->getType() > 1) {

                $discount = Yourdelivery_Model_Rabatt_Check::getValidVerificationCode($this->session->discountCode, $this->discount->getId());
                //check Usable
                if (!$discount) {
                    echo json_encode(array('status' => 'NOK', 'response' => __('Dieser Gutscheincode ist nicht gültig.')));
                    return;
                } elseif ($discount['send'] == 1) {
                    echo json_encode(array('status' => 'NOK', 'response' => __('Dieser Gutscheincode ist abgelaufen oder wurde schon einmal benutzt.')));
                    return;
                }
            }


            try {
                $check = new Yourdelivery_Model_Rabatt_Check();
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                echo json_encode(array('status' => 'NOK', 'response' => __('Ein Fehler ist aufgetreten.')));
                return;
            }

            if ($check->getTable()->findByEmailOrTel($email)) {
                echo json_encode(array('status' => 'NOK', 'response' => 'FIELDS', 'email' => __('Diese Email-Adresse wurde  schon einmal benutzt. Bitte benutze eine andere.')));
                return;
            }

            $check->setEmailSendCount(1);
            $check->save();

            $check->saveStep2($this->discount, $values['email'], $values['name'], $values['prename'], $discount['id']);

            $this->logger->info(sprintf('DISCOUNT CHECK: user finished step2, try to send out email with verification code %s to %s via broadmail', $check->getCodeEmail(), $check->getEmail()));

            if (!$check->sendConfirmMail($this->discount, $this->config)) {

                echo json_encode(array('status' => 'NOK', 'response' => __('Ein Fehler ist aufgetreten.')));
                return;
            }

            $this->session->rabattCheckId = $check->getId();

            echo json_encode(array('status' => 'OK'));
            return;
        }

        $messages = $form->getMessages();

        foreach ($messages as $key => &$message) {
            $message = array_pop(array_values($message));
        }

        echo json_encode(array_merge(array('status' => 'NOK', 'response' => 'FIELDS'), $messages));
        return;
    }

    /**
     * Check Telephonenumber and send sms
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     */
    public function telAction() {
        //send code by tel
        $request = $this->getRequest();
        if ($this->session->emailConfirmed && $request->isPost() && isset($this->session->rabattCheckId)) {
            $tel = $request->getParam("tel"); //will be normalized

            if ($tel === null || empty($tel) || strlen((int) $tel) < 5) {
                $this->logger->info(sprintf('DISCOUNT CHECK: user tried to validate tel "%s", but not valid', $tel));
                echo json_encode(array('status' => 'NOK', 'response' => __("Bitte gib eine gültige Telefonnummer ein!")));
                return;
            }

            $check = new Yourdelivery_Model_Rabatt_Check($this->session->rabattCheckId);
            if ($check->checkTel($tel) === false) {
                $this->logger->info(sprintf('DISCOUNT CHECK: user tried tel %s, but already in system', $tel));
                echo json_encode(array('status' => 'NOK', 'response' => __('Diese Telefonnummer wurde schon einmal benutzt. Bitte benutze eine andere.')));
                return;
            }

            /**
             * once this is validated, we normalize
             */
            $tel = Default_Helpers_Normalize::telephone($tel);

            if (!$check->allowResend('sms')) {
                echo json_encode(array('status' => 'NOK', 'response' => __('Die SMS wurde bereits drei mal verschickt!')));
                return;
            }


            if (!$check->sendSms($tel)) {
                echo json_encode(array('status' => 'NOK', 'response' => __('SMS konnte nicht verschickt werden.')));
                return;
            }
            echo json_encode(array('status' => 'OK'));
            return;
        }

        echo json_encode(array('status' => 'NOK', 'response' => __('Ein Fehler ist aufgetreten.')));
        return;
    }

    /**
     * Check Code from Sms
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     */
    public function telcodeAction() {
        $request = $this->getRequest();
        if ($this->session->emailConfirmed && $request->isPost() && isset($this->session->rabattCheckId)) {
            $code = $request->getParam("codetel");

            $check = new Yourdelivery_Model_Rabatt_Check($this->session->rabattCheckId);

            if (!$check->codeIsValid($code)) {
                echo json_encode(array('status' => 'NOK', 'response' => __("Dieser Code ist ungültig.")));
                ;
                return;
            }

            try {
                //save, send emails, check if correct
                $result = $check->finalize($this->discount);
            } catch (Exception $e) {
                $this->logger->err('DISCOUNT CHECK: telcode error: ' . $e->getMessage());
                echo json_encode(array('status' => 'NOK', 'response' => __('Ein Fehler ist aufgetreten.')));
                return;
            }

            $this->view->code = $result['rabatt']->getCode();

            $this->session->customerId = $result['customer']->getId();
            $this->getCustomer()->isLoggedIn();

            $output = $this->view->fetch('request/discount/index.htm');

            echo json_encode(array('status' => 'OK', 'response' => $output));
            return;
        }
        return;
    }

    /**
     * Resend Mail
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     */
    public function resendmailAction() {

        if (isset($this->session->rabattCheckId)) {
            $check = new Yourdelivery_Model_Rabatt_Check($this->session->rabattCheckId);

            if (!$check->allowResend('email')) {
                echo json_encode(array('status' => 'NOK', 'response' => __('Die Email wurde bereits drei mal verschickt')));
                return;
            }

            if (!$check->sendConfirmMail($this->discount, $this->config)) {
                echo json_encode(array('status' => 'NOK', 'response' => __('Fehler beim Versenden der Email')));
                return;
            }

            $check->setEmailSendCount($check->getEmailSendCount() + 1);
            $check->save();

            echo json_encode(array('status' => 'OK', 'response' => __('All fine')));
            return;
        } else {
            $this->logger->warn(sprintf("DISCOUNT CHECK: no rabattCheckId set in session"));
            return;
        }
        return;
    }

    /**
     * Resend Sms
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     */
    public function resendsmsAction() {

        $request = $this->getRequest();
        if ($this->session->emailConfirmed && $request->isPost() && isset($this->session->rabattCheckId)) {

            $check = new Yourdelivery_Model_Rabatt_Check($this->session->rabattCheckId);

            if (!$check->allowResend('sms')) {
                echo json_encode(array('status' => 'NOK', 'response' => __("Die SMS wurde bereits drei mal verschickt!")));
                return;
            }

            $sms = new Yourdelivery_Sender_Sms();
            $state = $sms->send($check->getTel(), __('Dein Bestätigungscode lautet: %s', $check->getCodeTel()));

            if ($state) {
                $this->logger->info(sprintf("DISCOUNT CHECK: successfully re-sent out sms with code %s to %s", $check->getCodeTel(), $check->getTel()));

                $check->increaseSmsSendCount();
                $check->save();

                echo json_encode(array('status' => 'OK', 'response' => __('All fine')));
                return;
            }

            echo json_encode(array('status' => 'NOK', 'response' => __("SMS konnte nicht verschickt werden.")));
            return;
        }

        echo json_encode(array('status' => 'NOK', 'response' => __("Request Fehler!")));
        return;
    }

    /**
     * Resend final Email
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     */
    public function resendcodeAction() {
        $request = $this->getRequest();
        if ($this->session->emailConfirmed && $request->isPost() && isset($this->session->rabattCheckId)) {
            $check = new Yourdelivery_Model_Rabatt_Check($this->session->rabattCheckId);

            if ($check->resend()) {
                echo "OK";
                return;
            }
        }
        echo "NOK:REQUESTFAILURE";
        return;
    }

    public function errorAction() {

        if($this->errorType == null) {
            $this->errorType = $this->getRequest()->getParam("errorType");
        }

        switch ($this->errorType) {
            case "disabled":
                echo json_encode(array('status' => 'NOK', 'response' => __("Diese Gutscheinaktion ist beendet.")));
                break;
            case "referer_missing":
                echo json_encode(array('status' => 'NOK', 'response' => __("Es wurde kein referer angegeben")));
                break;

            case "general":
                echo json_encode(array('status' => 'NOK', 'response' => __("Es konnte keine Gutscheinaktion gefunden werden.")));
                break;

            default:
                echo json_encode(array('status' => 'NOK', 'response' => __("Ein Fehler ist aufgetreten")));
                break;
        }
    }

    /**
     * Picks a random discount code from an existing discount
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.08.2012
     */
    public function randomcodefromdiscountAction() {
        $rabattHash = $this->_request->rabattHash;

        $rabatt = Yourdelivery_Model_Rabatt::getByHash($rabattHash);
        if ($rabatt === null) {
            $this->_forward('error', null, null, array('errorType' => 'general'));
            return;
        }

        if ($rabatt->isActive()) {
            if ($rabatt->getRandomCode() === null) {
                $this->_forward('error', null, null, array('errorType' => 'general'));
                return;
            }

            $rabattCode = new Yourdelivery_Model_Rabatt_Code($rabatt->getRandomCode());

            if ($rabattCode->getCode() == '') {
                $this->_forward('error', null, null, array('errorType' => 'disabled'));
                return;
            }
            $rabattCode->setReserved(1);
            $rabattCode->save();

            echo json_encode(array('status' => 'OK', 'rabattCode' => $rabattCode->getCode()));
        } else {
            $this->_forward('error', null, null, array('errorType' => 'disabled'));
            return;
        }

    }
}

