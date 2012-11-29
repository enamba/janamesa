<?php
/**
 * Request_Service_Rating_Controller
 * @author alex
 */
class Administration_Request_Service_RatingController extends Default_Controller_RequestAdministrationBase {

    /**
     * Delete rating
     * @author alex
     * @since 17.11.2010
     */
    public function deleteAction(){
        
        $this->_disableView();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $ratingId = $request->getParam('ratingId');
            Yourdelivery_Model_DbTable_Restaurant_Ratings::remove($ratingId);
        }
        
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de> 
     * @since 11.04.2012
     */
    public function sorryAction(){
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->_disableView();
            
            $post = $request->getPost();
            try {
                $rating = new Yourdelivery_Model_Servicetype_Rating($post['id']);
            }
            catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return $this->_json(array('error' => __b("Bewertung nicht gefunden")));
            }
            
            if ($rating->getCrmEmail()) {
                return $this->_json(array('error' => __b("Email wurde bereits abgeschickt")));
            }

            if (empty($post['prename'])) {
                return $this->_json(array('error' => __b("Vorname darf nicht leer sein")));
            }
            
            $customer = $rating->getOrder()->getCustomer();
            $service = $rating->getService();
            
            try {
                // Using config-based locale during composing and sending e-mail
                $this->_restoreLocale();
                $email = new Yourdelivery_Sender_Email_Optivo();
                $email->setbmRecipientId($customer->getEmail())
                      ->setUserPrename($post['prename'])
                      ->setLastOrderServiceName($service->getName());
                $isEmailSent = $email->send($post['call']);
                $this->_overrideLocale();

                if ($isEmailSent) {
                    $rating->setCrmEmail(1);
                    $rating->save();
                    $rating->logCrm($this->session_admin->admin->getId(), $post['call']);
                    
                    $this->logger->adminInfo(sprintf("Successfully send crm email to %s for order #%s", $customer->getEmail(), $rating->getOrderId()));
                    return $this->_json(array('success' => __b("Email wurde erfolgreich an %s verschikt", $customer->getEmail())));
                }
            }
            catch (Yourdelivery_Sender_Email_Optivo_Exception $e) {
                $this->_overrideLocale();
                return $this->_json(array('error' => __b("Email konnte nicht verschikt werden weil: %s", $e->getMessage())));
            }
            
            return $this->_json(array('error' => __b("Email konnte nicht verschikt werden")));
        }
        
        $id = $request->getParam('id');
        try {
            $rating = new Yourdelivery_Model_Servicetype_Rating($id);
        }
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        $this->view->rating = $rating;
        $this->view->calls = array(
            'RATING_NO_COMMENT' => __b('keine Bewertung geschrieben'),
            'RATING_LONG_DELIVERTIME' => __b('lange Lieferzeit'),
            'RATING_NO_DELIVERY' => __b('keine Lieferung'),
            'RATING_BAD_FOOD' => __b('Schlechtes Essen'),
            'RATING_SORRY' => __b('Generelle Sorry Mail'),
        );
    }

}
