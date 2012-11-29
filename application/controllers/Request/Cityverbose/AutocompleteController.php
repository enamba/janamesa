<?php

/**
 * Description of AutocompleteController
 *
 * @author mlaug
 */
class Request_Cityverbose_AutocompleteController extends Default_Controller_RequestBase {

    /**
     * get additional verbose information
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 14.12.2011
     */
    public function cepAction() {
        $process = $this->getRequest()->getParam('process', 'order');
        $verbose = new Yourdelivery_Model_City_Verbose();

        $this->view->cidades = $cidades = $verbose->getCities();
        $this->view->enderecos = $enderecos = $verbose->getSteetTypes();
        $this->view->process = $process;
    }

    /**
     * get the list of possible cep the 
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.01.2012
     */
    public function listAction() {
        $process = $this->getRequest()->getParam('process', 'order');
        $cidades = $this->getRequest()->getParam('cidade');
        $logradouro = $this->getRequest()->getParam('logradouro');
        $verbose = new Yourdelivery_Model_City_Verbose();
        $this->view->list = $verbose->findmatch($cidades, $logradouro);
        $this->view->process = $process;
    }

    /**
     * triggered on menu page, if a number is needed to find a cityId
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 30.05.2012 
     */
    public function finalizeAction() {
        $this->_disableView(true);
        $request = $this->getRequest();
        $form = new Yourdelivery_Form_Order_Start_Citystreet();
        //validates that street, number and city match upxx (must be strict)
        if ($form->isValid($request->getPost())) {
            $cityVerbose = new Yourdelivery_Model_City_Verbose();
            $matches = $cityVerbose->findmatch(
                    $form->getValue('city'), $form->getValue('street'), $form->getValue('hausnr', null)
            );
            if (count($matches) == 1) {
                $data = array_pop($matches);

                //store that verbose information in cookie
                $state = Yourdelivery_Cookie::factory('yd-state');
                $state->set('verbose', $verboseId);
                $state->save();
                
                echo json_encode($data);
                
            } else {
                //nothing or more than one found, we need the number
                $this->getResponse()->setHttpResponseCode(404);
            }
        }
        else{
            $this->getResponse()->setHttpResponseCode(406);
        }
    }

    /**
     * get list of biggest cities in a div for poland autocomplete
     * 
     * @autor Matthias Laug <laug@lieferando.de>
     * @since 19.04.2012 
     */
    public function cityAction() {
        
    }

}

?>
