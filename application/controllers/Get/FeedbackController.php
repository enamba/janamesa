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
 *          controller to post feedback for a mobile app (iPhone/Pad, Android etc)
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
 *          post - post a feedback
 *      </li>
 *      <li>
 *          put - disallowed - 403
 *      </li>
 *  </ul>
 *
 *
 * @author Andre Ponert <ponert@lieferando.de>
 * @since 09.07.2012
 */
class Get_FeedbackController extends Default_Controller_RestBase {

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>post feedback</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * <b>2. Request:</b>
     *
     * <b>2.1. Parameters:</b>
     *
     *  <code>
     *      type JSON
     *      parameters =
     *      {
     *          name       : STRING *      (name of customer)
     *          prename    : STRING        (prename of customer)
     *          email      : STRING        (email of customer)
     *          tel        : STRING *      (telephone of customer)
     *          comment    : STRING        (comment)
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
     *              curl -X POST -d parameters='{"name":"Schmidt","prename":"Helmut","email":"helmut.schmidt@email.de","tel":"12345678","comment":"Super tolle megageile App !!!!"}' www.yourdelivery.local/get_feedback
     *          </li>
     *          <li>
     *              curl -X POST -d parameters='{"prename":"Helmut","email":"helmut.schmidt@email.de","comment":"Super tolle megageile App !!!!"}' www.yourdelivery.local/get_feedback
     *          </li>
     *      </ul>
     *  </code>
     *
     * <b>4.2. Example - Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <success>true</success>
     *          <message>Vielen Dank für Dein Feedback.</message>
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
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.07.2012
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

        $form = new Yourdelivery_Form_Api_Feedback();
        $params = (array) json_decode($post['parameters'], true);
        if (!$form->isValid($params)) {
            $this->returnFormErrors($form->getMessages());
            return $this->getResponse()->setHttpResponseCode(406);
        }

        $body = 'neue Nachricht (API - App Feedback) ' . date('d.m.Y H:i') . ' Uhr von ' . $params['prename'] . ' ' . $params['name'] . ' // E-Mail: ' . $params['email'] . ' // Telefon: ' . $params['tel'] . ' ' . ' // Kommentar: ' . $params['comment'];
        try {
            $email = new Yourdelivery_Sender_Email();
            $email->addTo('app@lieferando.de');

            $email->setSubject(__('Neue Nachricht (App-Feedback) von %s %s', $params['prename'], $params['name']))
                    ->setBodyText($body)
                    ->send('system');
            $this->logger->info(sprintf('API - SUGGESTION - POST: successfully send out suggestion: %s', $body));
            $this->message = __('Vielen Dank für Dein Feedback.');
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
