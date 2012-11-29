<?php

/**
 * Description of MultipleController
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
require_once APPLICATION_PATH . "/controllers/Order/PrivateController.php";

class Satellite_MultipleController extends Order_PrivateController {

    protected $_satellite = null;

    public function init() {
        parent::init();
        $this->getSatellite();

        //use the default menu template for satellites

        $dir = 'satellite';
        if (is_dir(APPLICATION_PATH . '/views/smarty/template/default/satellite/' . Default_Helpers_Web::getDomain())) {
            $dir = 'satellite/' . Default_Helpers_Web::getDomain();
        } elseif (is_dir(APPLICATION_PATH . '/views/smarty/template/default/satellite/' . Default_Helpers_Web::getSubdomain())) {
            $dir = 'satellite/' . Default_Helpers_Web::getSubdomain();
        }

        if (file_exists(APPLICATION_PATH . '/views/smarty/template/default/' . $dir . '/' . $this->getRequest()->getActionName() . '.htm')) {
            $this->view->setDir($dir);
        } else {
            $this->view->setDir('satellite');
        }
        
        $this->view->package_css = Default_Helpers_Web::getSubdomain() == 'www' ? 'satellite' : Default_Helpers_Web::getSubdomain();
        
    }

    /**
     * @return Yourdelivery_Model_Satellite 
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.11.2011
     * @return Yourdelivery_Model_Satellite
     */
    private function getSatellite() {

        $id = (integer) $this->getRequest()->getParam('satelliteId');
        if ($id <= 0) {
            return null;
        }
        try {
            $this->view->satellite = $this->_satellite = new Yourdelivery_Model_Satellite($id);
            $this->view->service = $this->_satellite->getService();
            $this->view->metatags = $this->_satellite->getService();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return null;
        }

        return $this->_satellite;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     */
    public function indexAction() {
        if ( Default_Helpers_Web::getSubdomain() == 'gelbeseiten' ){
            return $this->_redirect('http://www.gelbeseiten.de');
        }
        return $this->_forward('list');
        //this page should display the init page of all satellites
    }

    /**
     * show base menu
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.11.2011
     */
    public function menuAction() {

        $service = $this->_satellite->getService();
        $this->view->extra_css = 'step3';
        list($menu, $parents) = $service->getMenu();

        $this->view->menu = $menu;
        $this->view->parents = $parents;

        $this->view->mode = $mode = $service->getType();
        if ($service->useTopseller() && $mode == 'rest') {
            $maxSizes = 1;
            $bestSeller = $service->getBestSeller(10);
            foreach ($bestSeller as $best) {
                $maxSizes = count($best->getSizes()) > $maxSizes ? count($best->getSizes()) : $maxSizes;
            }
            $this->view->topSellerCountSizes = $maxSizes;
        }

        $this->view->enableCache();
        $this->setCache(28800); //8 hours
        $this->view->tags = implode(", ", $service->getTagsWithMaxStringlength($count = 30));
    }

    /**
     * show finish page
     * just make sure to use the standard satellite folder
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.11.2011
     */
    public function finishAction() {
        parent::finishAction();
    }

    /**
     * use this success action to get the satellite
     * 
     * @param Yourdelivery_Model_Order_Abstract $order
     * 
     * @return type 
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.11.2011
     */
    public function _success(Yourdelivery_Model_Order_Abstract $order) {
        parent::_success($order);
        $this->_gatherSatellite($order);
    }

    /**
     * show success page
     * just make sure to use the standard satellite folder
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.11.2011
     */
    public function successAction() {
        parent::successAction();
    }
    
    /**
     * get payment page for satellite
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 23.07.2012 
     */
    public function paymentAction(){
        $this->_gatherSatellite($this->_getCurrentOrder());
        parent::paymentAction();
    }

    /**
     * the action is only for listing purpouse if one domains
     * is assigned to multiple services
     * 
     * @AVANTI
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 21.11.2011
     */
    public function listAction() {
        $this->view->satelliteList = $this->getSatelliteList();
        $this->view->enableCache();
        $this->setCache(28800); //8 hours
    }

    /**
     * show impressum page
     * just make sure to use the standard satellite folder
     * 
     * @AVANTI
     * 
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 14.12.2011
     */
    public function impressumAction() {
        $this->view->satelliteList = $this->getSatelliteList();
        $this->view->enableCache();
        $this->setCache(28800); //8 hours
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 15.02.2012
     */
    public function notfoundAction() {
        $this->view->extra_css = 'start';
        if (!$this->view->preview) {
            $this->view->enableCache();
            $this->setCache(86400); //24 hours
        }
        
        $this->view->service = $this->getSatellite()->getService();
        $this->notSeoRelevant(); 
   }

    /**
     * show rating page
     * just make sure to use the standard satellite folder
     * 
     * @AVANTI
     * 
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 14.12.2011
     */
    public function bewertenAction() {
        $this->view->satelliteList = $this->getSatelliteList();
        $this->view->enableCache();
        $this->setCache(28800); //8 hours
    }

    /**
     * send email to avanti include rating
     *
     * @AVANTI
     * 
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 14.12.2011
     */
    public function bewertensendAction() {

        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();
            try {

                $email = new Yourdelivery_Sender_Email();
                $email->addTo('info@avanti.de')
                        ->setSubject('Avanti Bewertungsmail');
                $email->setBodyHtml(
                        "<html>
                         <head><title>Bewertung Avanti</title></head>
                         <body>   " .
                        "<br/>Bewertung von: " . $post["email"] .
                        "<br/>Store: " . $post['store'] .
                        "<br/>Geschmack: " . $post['geschmack'] .
                        "<br/>Verwendete Zutaten: " . $post['verwendete_zutaten'] .
                        "<br/>Temperatur: " . $post['temperatur'] .
                        "<br/>Kompetenz des Telefonisten: " . $post['kompetenz_des_telefonisten'] .
                        "<br/>Kompetenz des Fahrers: " . $post['kompetenz_des_fahrers'] .
                        "<br/>Preis Leistung: " . $post['preis_leistung'] .
                        "<br/>Umfang des Angebots: " . $post['umfang_des_angebots'] .
                        "<br/>Qualitaet der Sonderaktionen: " . $post['qualitaet_der_sonderaktionen'] .
                        "<br/>Erstesmal: " . $post["erstesmal"] .
                        "<br/>Kommentar: " . $post['comment'] .
                        "</body></html>");
                $email->send();
            } catch (Exception $e) {
                Yourdelivery_Sender_Email::error("Is not possible to send Mail to Premium Satellite", true);
                return $this->_redirect('/bewerten');
            }

            // redirect
            return $this->_redirect('/bewerten_success');
        } else {
            return $this->_redirect('/bewerten');
        }
    }

    /**
     * send email to PREMIUM-SATELLITE include rating
     * 
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 15.05.2012
     */
    public function sendmailtopremiumAction() {

        // get request
        $request = $this->getRequest();

        // post
        if ($request->isPost()) {
            $post = $request->getPost();
            try {
                $email = new Yourdelivery_Sender_Email();
                $email->addTo($post["tomail"])
                        ->setSubject("Bewertung");
                $body = "<html><head><title>Bewertung</title></head> <body> ";
                foreach ($post as $key => $value) {
                    if ($key != 'tomail') {
                        $body .= "<br/> " . $key . " : " . $value;
                    }
                }
                $body .= "</body></html>";
                $email->setBodyHtml($body);
                $email->send();
            } catch (Exception $e) {
                $msg = sprintf("Is not possible to send Mail to Premium Satellite - Exception was %s", $e->getMessage());
                $this->logger->err($msg);
                Yourdelivery_Sender_Email::error($msg, true);
                return $this->_redirect('/bewerten');
            }

            // redirect
            $this->logger->info(sprintf('successfully send out rating mail to premium satellite to email %s', $post['tomail']));
            return $this->_redirect('/bewerten_success');
        } else {
            return $this->_redirect('/bewerten');
        }
    }

    /**
     * get list of satellites by domain
     *
     * @param string $domain
     *
     * @return array Yourdelivery_Model_Satellite
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 02.06.2012
     */
    protected function getSatelliteList() {

        $domain = Default_Helpers_Web::getHostname();

        $satelliteTable = new Yourdelivery_Model_DbTable_Satellite();
        $satellites = $satelliteTable->findAllByDomain($domain, 50);
        $satelliteList = array();
        foreach ($satellites as $satellite) {
            try {
                $sat = new Yourdelivery_Model_Satellite((integer) $satellite['id']);

                // check geting service
                if (!($sat->getService() instanceof Yourdelivery_Model_Servicetype_Abstract)) {
                    throw new Yourdelivery_Exception_Database_Inconsistency(sprintf('did not get service for satellite #%s', (integer) $satellite['id']));
                }
                if (!($sat->getService()->getCity()->getId())) {
                    throw new Yourdelivery_Exception_Database_Inconsistency(sprintf('cityId not provided for service. #%s', (integer) $satellite['id']));
                }
                $satelliteList[] = $sat;
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                $this->logger->warn(sprintf('Satellite_MultipleController - getSatelliteList: Exception: %s', $e->getMessage()));
            }
        }

        return $satelliteList;
    }

    /**
     * get on generic url the satellite based on the current domain and the current Order in session
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 23.07.2012
     * @return type 
     */
    protected function _gatherSatellite(Yourdelivery_Model_Order $order){     
        $satelliteTable = new Yourdelivery_Model_DbTable_Satellite();
        $row = $satelliteTable->select()->where('restaurantId=?', $order->getService()->getId())
                        ->where('domain=?', Default_Helpers_Web::getHostname())
                        ->query()->fetch();
        $satelliteId = (integer) $row['id'];

        if ($satelliteId <= 0) {
            return $this->_redirect('/');
        }
        try {
            $satellite = new Yourdelivery_Model_Satellite($satelliteId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            return $this->_redirect('/');
        }

        $this->view->satellite = $satellite;
        $this->view->service = $satellite->getService();
        $this->view->metatags = $satellite->getService();
    }
    
}

?>
