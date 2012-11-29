<?php

/**
 * @package Yourdelivery
 * @subpackage PartnerAPI
 */
/**
 * <b>Description:</b>
 *
 *  <ul>
 *      <li>
 *          
 *      </li>
 *  </ul>
 *
 * <b>Available Actions:</b>
 *  <ul>
 *      <li>
 *          index - disallowed - 403
 *      </li>
 *      <li>
 *          delete - disallowed - 403
 *      </li>
 *      <li>
 *          get - disallowed - 403
 *      </li>
 *      <li>
 *          post - login a customer
 *      </li>
 *      <li>
 *          put - disallowed - 403
 *      </li>
 *  </ul>
 *
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 13.08.2012
 */
require_once('AbstractController.php');

class Get_Partner_CustomerController extends AbstractApiPartnerController {

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          login partner customer
     *      </li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Paremeters:</b>
     *
     *  <code>
     *      type POST
     *      {
     *          nr          STRING          customerNr of restaurant
     *          pass        STRING          password for partner backend
     *      }
     *  </code>
     *
      -------------------------------------------------------------------------
     *
     * <b>3. Response:</b>
     *
     *  <code>
     *      <response>
     *          <access>STRING</access>
     *          <success>BOOLEAN</success>
     *          <name>STRING</name>             Name of sub-account / driver
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>4. Examples:</b>
     *
     * <b>4.1. Example - Request:</b>
     *
     *  <code>
     *      <ul>
     *          <li>
     *              curl -d nr=40000 -d pass=testen -X POST http://www.lieferando.local/get_partner_customer
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <name>Kotobuki Sushi</name>
     *          <access>13073b7cc49a473131dc098e6b7e4994</access>
     *          <success>true</success>
     *          <message></message>
     *          <url></url>
     *          <anchortext></anchortext>
     *          <errorkey></errorkey>
     *          <memory>10</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <b>5.1. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>201 - authentication success</li>
     *      <li>403 - authentication failed</li>
     *      <li>404 - internal error</li>
     *      <li>406 - invalid data provided</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     *
     * @return integer HTTP-RESPONSE-CODE
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.08.2012
     */
    public function postAction() {
        $form = new Yourdelivery_Form_Partner_Login();
        $data = $this->getRequest()->getPost();

        if (!$form->isValid($data)) {
            $this->returnFormErrors($form->getErrors());
            return $this->getResponse()->setHttpResponseCode(406);
        }

        $customerNr = $form->getValue('nr');
        $pass = $form->getValue('pass');

        $row = Yourdelivery_Model_DbTable_Restaurant::findByCustomerNr($customerNr);
        if ((count($row) <= 0) || (strlen($row['id']) <= 0)) {
            $this->message = __p('Dieses Restaurant gibt es nicht!');
            return $this->getResponse()->setHttpResponseCode(403);
        }

        try {
            // create restaurant and partner data, if available
            $restaurant = new Yourdelivery_Model_Servicetype_Restaurant($row['id']);
            $partnerData = new Yourdelivery_Model_Servicetype_Partner(null, $restaurant->getId());
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->message = __p('Unbekannter Fehler');
            return $this->getResponse()->setHttpResponseCode(404);
        }
        
        //we do not allow temporary login until passwort has been reset by partner
        if (strlen($partnerData->getTemporarypassword()) > 0) {
            $this->message = __p('Ihr Passwort wurde kürzlich zurückgesetzt. Bitte legen Sie über die Webseite http://%s/partner zuerst ihr eigenes Passwort fest', $this->config->domain->base);
            return $this->getResponse()->setHttpResponseCode(403);
        }

        $auth = $form->getAuthAdapter();
        $auth->setIdentity($customerNr)->setCredential($pass);
        //get result ...
        $result = $auth->authenticate();
        //... and check it
        switch ($result->getCode()) {

            case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                $this->message = __p('Das Passwort ist falsch!');
                return $this->getResponse()->setHttpResponseCode(403);

            case Zend_Auth_Result::SUCCESS:
                $this->xml->appendChild(create_node($this->doc, 'name', $restaurant->getName()));
                $this->xml->appendChild(create_node($this->doc, 'access', $restaurant->getSalt()));
                return $this->getResponse()->setHttpResponseCode(201);

            default:
                $this->message = __p('Unbekannter Fehler!');
                return $this->getResponse()->setHttpResponseCode(403);
        }
    }

    /**
     * this method is not used and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function putAction() {
       return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * this method is not used and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function getAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * this method is not used and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function indexAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * this method is not used and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}
