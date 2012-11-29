<?php
/**
 * @author alex
 * @since 02.12.2010
 */
class Administration_Service_HolidaysController extends Default_Controller_AdministrationBase {

    /**
     * Table with all holidays per bundesland
     * @author alex
     * @since 02.12.2011
     */
    public function indexAction(){
        $result = Yourdelivery_Model_DbTable_Restaurant_Openings_Holiday::getHolidays();

        // save results as array of {data=>{array of lands}}
        $holidays = array();
        $holiday_names = array();
        
        foreach ($result as $r) {
            if (!array_key_exists($r['date'], $holidays)) {
                $holidays[$r['date']] = array();
            }
            
            if (!array_key_exists($r['date'], $holiday_names)) {
                $holiday_names[$r['date']] = $r['name'];
            }
            
            $holidays[$r['date']][$r['stateId']] = $r['id'];
        }

        $this->view->holidays = $holidays;
        $this->view->holiday_names = $holiday_names;
        $this->view->states = Yourdelivery_Model_DbTable_City::getAllStates();
    }

}