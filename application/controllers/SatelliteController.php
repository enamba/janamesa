<?php

require_once( APPLICATION_PATH . "/controllers/Order/PrivateController.php");

class SatelliteController extends Order_PrivateController {

    /**
     * Satellite
     * @var Yourdelivery_Model_Satellite
     */
    private $_satellite;

    /**
     * @author Vincent Priem <priem@lieferando.de>
     */
    public function init() {
        parent::init();

        // get parameters
        $request = $this->getRequest();

        $id = $request->getParam('id');

        // assign nav
        $this->view->action = $request->getActionName();

        // search for satellite
        $satellite = new Yourdelivery_Model_Satellite();

        // enable preview mode for admin
        $this->view->preview = false;
        if ($id !== null && $this->session_admin->admin !== null) {
            $satellite->load($id);
            $this->view->preview = true;
        } elseif (!$satellite->loadByDomain()) {
            return $this->_redirect('http://www.' . $this->config->domain->base);
        }
        $this->view->satellite = $this->_satellite = $satellite;
        // search for service
        $service = $satellite->getService();
        if (!($service instanceof Yourdelivery_Model_Servicetype_Abstract)) {
            return $this->_redirect('http://www.' . $this->config->domain->base);
        }
        $this->view->service = $service;
    }

    /**
     * @author mlaug
     * @since 19.05.2011
     */
    public function indexAction() {
        $this->view->extra_css = 'start';
        if (!$this->view->preview) {
            $this->view->enableCache();
            $this->setCache(86400); //24 hours
        }

        $this->view->tags = implode(", ", $this->_satellite->getService()->getTagsWithMaxStringlength(30));
        // assign plz
        $ranges = array();
        foreach ($this->_satellite->getService()->getRanges() as $r) {
            $ranges[$r['mincost']][] = $r['cityname'];
        }
        ksort($ranges);
        $this->view->ranges = $ranges;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 28.04.2011
     */
    public function aboutAction() {
        $this->view->extra_css = 'start';
        if (!$this->view->preview) {
            $this->view->enableCache();
            $this->setCache(86400); //24 hours
        }

        $this->notSeoRelevant();
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

        $this->notSeoRelevant(); 
   }

    /**
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 12.05.2011
     */
    public function jobsAction() {
        $this->view->extra_css = 'start';
        if (!$this->_satellite->getShowJobs()) {
            return $this->_redirect("/");
        }

        if (!$this->view->preview) {
            $this->setCache(86400); //24 hours
            $this->view->enableCache();
        }
    }

    /**
     * @author Tmeuschke
     * @since 13.05.2011
     */
    public function opinionAction() {
        $this->view->extra_css = 'start';
        if (!$this->_satellite->getShowOpinions()) {
            return $this->_redirect("/");
        }

        if (!$this->view->preview) {
            $this->view->enableCache();
        }

        // assign ratings
        $this->view->ratings = $this->_satellite->getService()->getRatings(5);
    }

    /**
     * @author mlaug
     * @since 28.04.2011
     */
    public function menuAction() {
        
        $service = $this->_satellite->getService();

        
        $cityId = $this->_getParam('cityId', 0);
        
        if ($cityId == 0) {
            return $this->_redirect('/');
        }
        
        $plzs = Yourdelivery_Model_Autocomplete::getPlzFromServiceAndcity($cityId, $service->getId());
        
        if (count($plzs) == 0) {
            return $this->_redirect('/');
        }
        
        $plz = $plzs[0]['plz'];

        $this->view->plz = $plz;
        $this->view->city = $cityId;
        $this->view->extra_css = 'step3';
        list($menu, $parents) = $service->getMenu();
        $this->view->menu = $menu;
        $this->view->parents = $parents;
        
        /**
         * @author Toni Meuschke <meuschke@lieferando.de
         * @copy from mlaug 
         * @since 15.06.2012
         */
        $this->view->mode = $mode = $service->getType();
        
        if ($service->useTopseller() && $mode == 'rest') {
            $maxSizes = 1;
            $bestSeller = $service->getBestSeller(10);
            foreach ($bestSeller as $best) {
                $maxSizes = count($best->getSizes()) > $maxSizes ? count($best->getSizes()) : $maxSizes;
            }
            $this->view->topSellerCountSizes = $maxSizes;
        }
        
        if (!$this->view->preview) {
            $this->setCache(28800); //8 hours
            $this->view->enableCache();
        }
    }

    /**
     * Finish page 
     * 
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 20.12.2011
     */
    public function finishAction() {
        $this->notSeoRelevant();
        parent::finishAction();
    }

}
