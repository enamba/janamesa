<?php


require_once( APPLICATION_PATH . "/controllers/Request/OrderController.php");
class Request_IframeController extends Request_OrderController {
    
    public function initcardAction(){
        $this->view->order = $this->session->currentOrder;
    }

    
    public function expressservicesAction(){

        $plz = $this->getRequest()->getParam('plz', null);

        if( is_null($plz) ){
            return null;
        }

        $order = $this->session->currentOrder;
        
        //create location
        $location = new Yourdelivery_Model_Location();
        $location->setPlz($plz);

        //append current location
        $order->setLocation($location);

        $services = $order->getServicesByCityId($plz);
        $this->view->assign('services', $services);
    }

    public function expresscategoriesAction(){
        $plz = $this->getRequest()->getParam('plz', null);

        if( is_null($plz) ){
            return null;
        }
        $this->view->ydcategories = Yourdelivery_Model_Servicetype_Categories::getCategoriesByCityId($plz, 1);
    }
    
}

