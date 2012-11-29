<?php

/**
 * @package Yourdelivery
 * @subpackage API
 */

/**
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 28.02.2012
 */
class Get_TestController extends Default_Controller_RestBase {


    public function indexAction() {
        $this->fidelity_message = 'you got some points for calling this URL';
        $this->fidelity_points = 98;
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
     * the post method is not in use, and will be forbidden
     *
     * @return HTTP-RESONSE-CODE 403
     */
    public function postAction() {
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
