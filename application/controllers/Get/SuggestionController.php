<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * <b>Description:</b>
 *
 *  <ul>
 *      <li>
 *          controller to post suggestion for a service that you would like to have included in our system
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
 *          post - post a suggestion
 *      </li>
 *      <li>
 *          put - disallowed - 403
 *      </li>
 *  </ul>
 *
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 27.02.2012
 */
class Get_SuggestionController extends Default_Controller_RestBase {

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>post suggestion</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Paremeters:</b>
     *
     *  <code>
     *      type JSON
     *      parameters =
     *      {
     *          name    : STRING        (name of customer)
     *          service : STRING        (name of service / restaurant)
     *          ort     : STRING        (location of service)
     *          street  : STRING *      (street of service)
     *          hausnr  : STRING *      (house number)
     *          comment : STRING *      (comment)
     *      }
     *  </code>
     *  * = optional params
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      <response>
     *          <success>BOOLEAN</success>
     *          <message>STRING</message>
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
     *              curl -X POST -d parameters='{"name":"Felix","service":"Pizza King","ort":"Dreschdn", "street":"MainStreet", "hausnr":"13", "comment":"schmeckt jut dort"}' www.lieferando.local/get_suggestion
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <respons>
     *          <version>1.0</version>
     *          <success>true</success>
     *          <message>Vielen Dank für Deinen Vorschlag.</message>
     *          <fidelity>
     *              <points>0</points>
     *              <message></message>
     *          </fidelity>
     *          <memory>20</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>200 - success</li>
     *      <li>403 - invalid json provided</li>
     *      <li>404 - data could not be submitted to system</li>
     *      <li>406 - invalid data</li>
     *  </ul>
     *
     *
     * -------------------------------------------------------------------------
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 27.02.2012
     *
     * @return HTTP-RESONSE-CODE
     */
    public function postAction() {
        $post = $this->getRequest()->getPost();

        $json = json_decode($post['parameters']);
        if (!is_object($json)) {
            $this->logger->err('API - SUGGESTION - POST: could not encode json');
            $this->message = __('Anfrage konnte nicht verarbeitet werden');
            return $this->getResponse()->setHttpResponseCode(403);
        }

        $form = new Yourdelivery_Form_Api_Suggestion();
        $params = (array) json_decode($post['parameters'], true);
        if (!$form->isValid($params)) {
            $this->returnFormErrors($form->getMessages());
            return $this->getResponse()->setHttpResponseCode(406);
        }

        $body = 'neue Nachricht (API - Restaurant Vorschlagen) ' . date('d.m.Y H:i') . ' von ' . $params['name'] . ' // Restaurant: ' . $params['service'] . ' // Strasse & Nr: ' . $params['street'] . ' ' . $params['hausnr'] . ' // Ort: ' . $params['ort'] . ' // Nachricht: ' . $params['comment'];
        try {
            $email = new Yourdelivery_Sender_Email();
            $email->addTo('rueggen@lieferando.de');

            $email->setSubject(__('Neue Nachricht (iPhoneApp - Restaurant Vorschlagen) von %s', $params['name']))
                    ->setBodyText($body)
                    ->send('system');
            $this->logger->info(sprintf('API - SUGGESTION - POST: successfully send out suggestion: %s', $body));
            $this->message = __('Vielen Dank für Deinen Vorschlag.');
        } catch (Exception $e) {
            $this->logger->err(sprintf('API - SUGGESTION - POST: could not send email because of exception: %s', $e->getMessage()));
            $this->message = __('Anfrage konnte nicht verarbeitet werden');
            return $this->getResponse()->setHttpResponseCode(404);
        }
    }

    /**
     * the get method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function indexAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the get method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function getAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the put method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function putAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the delete method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}
