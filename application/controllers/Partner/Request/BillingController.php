<?php

/**
 * @author Vincent Priem <priem@lieferando.de>
 * @since 10.08.2012
 */
class Partner_Request_BillingController extends Default_Controller_RequestPartnerBase {
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 10.08.2012
     */
    public function deliverAction() {
        
        $this->_disableView();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            
            $form = new Yourdelivery_Form_Partner_Billing_Deliver();
            if ($form->isValid($post)) {
                $values = $form->getValues();

                $billDeliver = array();
                $billDeliver[] = 'email'; // always add email
                if ($values['fax'] == 1) {
                    $billDeliver[] = 'fax';
                }
                if ($values['post'] == 1) {
                    $billDeliver[] = 'post';
                }

                $this->_restaurant->setBillDeliver(implode(",", $billDeliver));
                $this->_restaurant->save();
                
                return $this->_json(array(
                    'success' => __p("Der Rechungsversand wurde erfolgreich umgestellt."),
                ));
            }
        }
        
        return $this->_json(array(
            'error' => __p("Ein Fehler ist aufgetreten"),
        ));
    }
}
