<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 * Image API
 * @since 03.11.2010
 * @author mlaug
 */
class Get_ImageController extends Default_Controller_RestBase {

    /**
     * <b>1. Description:</b>
     *
     *  <ul>
     *      <li>
     *          upload an image to a customer
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
     *          <IMAGE>   FILE
     *      }
     * 
     *      type JSON
     *      {
     *          "access":"STRING"       (access secret for user)
     *      }
     * 
     *      type GET
     *      {
     *          profile=INTEGER         (1 = use as profile picture)
     *      } 
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>3.1. Response:</b>
     *
     *  <code>
     *      <response>
     *          <version>1.0</version>
     *          <success>BOOLEAN</success>
     *          <message>STRING</message>
     *          <url>STRING</url>
     *          <anchortext>STRING</anchortext>
     *          <fidelity>
     *              <points>INTEGER</points>
     *              <message>STRING</message>
     *              </fidelity>
     *          <memory>INTEGER</memory>
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
     *              curl -X POST --form img=@/home/felix/test.jpg --form parameters='{"access":"dc9aeffb7ef67068d1d19fb3d246060a"}' "http://www.lieferando.local/get_image?profile=1"
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
     *          <message></message>
     *          <url></url>
     *          <anchortext></anchortext>
     *          <fidelity>
     *              <points>8</points>
     *              <message>Upload Deines Profilbild: 8 Treuepunkte.</message>
     *              </fidelity>
     *          <memory>8</memory>
     *      </response>
     *  </code>
     *
     * -------------------------------------------------------------------------
     *
     * <b>5. HTTP Response Codes:</b>
     *
     *  <ul>
     *      <li>201 - image has been uploaded</li>
     *      <li>403 - invalid access token</li>
     *      <li>406 - image not valid</li>
     *      <li>405 - no image received</li>
     *  </ul>
     *
     * -------------------------------------------------------------------------
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 30.11.2011
     * 
     * @return HTTP-RESPONSE-CODE
     */
    public function postAction() {
        $form = new Yourdelivery_Form_Api_Image();
        $post = $this->getRequest()->getPost();

        if ($form->img && $form->img->receive() && $form->img->isUploaded()) {
            if (!$form->isValid($post)) {
                $this->returnFormErrors($form->getMessages());
                return $this->getResponse()->setHttpResponseCode(406);
            }

            $file = $form->img->getFileName();

            try {
                $json = json_decode($post['parameters']);
                $customer = $this->_getCustomer($json);
                $customer->addImage($file, (boolean) $this->getRequest()->getParam('profile', true));
                $this->logger->info(sprintf("API - IMAGE - POST: customer #%s %s successfully uploaded profile image", $customer->getId(), $customer->getFullname()));
                return $this->getResponse()->setHttpResponseCode(201);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return $this->getResponse()->setHttpResponseCode(403);
            }
        } else {
            $this->logger->warn(sprintf('API - IMAGE - POST: could not receive image'));
            $this->message = 'could not receive image';
            return $this->getResponse()->setHttpResponseCode(405);
        }
    }

    /**
     * the put method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function indexAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the delete method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function getAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the put method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function putAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

    /**
     * the delete method is not in use, and will be forbidden
     *
     * @return integer HTTP-RESPONSE-CODE 403
     */
    public function deleteAction() {
        return $this->getResponse()->setHttpResponseCode(403);
    }

}
