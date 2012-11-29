<?php
/**
 * Statistics
 * @author alex
 * @since 03.01.2011
 */
class Administration_Stats_GraphicsController extends Default_Controller_AdministrationBase{

    /**
     * Show weekly orders statistics
     * @author vpriem
     * @since 30.11.2010
     */
    public function indexAction(){
        // get params
        $request = $this->getRequest();
        $year = $request->getParam('year', date('Y'));
        $week = $request->getParam('week', date('W'));
        $city = $request->getParam('city');

        // get years
        $years = array();
        for ($y = 2009, $n = date('Y'); $y <= $n; $y++) {
            $years[] = $y;
        }
        $this->view->years = $years;
        $this->view->year = $year;

        // get weeks
        $weeks = array();
        for ($w = 1; $w <= 52; $w++) {
            $weeks[] = $w;
        }
        $this->view->weeks = $weeks;
        $this->view->week = $week;

        // get cities
        $cities = Yourdelivery_Model_DbTable_City::getAllCities();
        $this->view->cities = $cities;
        $this->view->city = $city;
    }
}
