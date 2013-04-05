<?php

/**
 * @author alex
 * @sinces 20.12.2010
 */
class Administration_Stats_CustomersController extends Default_Controller_AdministrationBase {

    /**
     * Initialize
     */
    public function init() {
        
        parent::init();
        $this->view->years = array(2012, 2013, 2014);
    }

    /**
     * @author alex
     * @sinces 20.12.2010
     */
    public function indexAction() {
        $dbTable = new Yourdelivery_Model_DbTable_Customer();
        
        $this->view->dataRegistered = $dbTable->getCustomerStats();
        $this->view->ordersRegistered = $dbTable->getCustomerOrderStats();
        
        $this->view->dataNotregistered = $dbTable->getCustomerStats("notreg");    
        $this->view->ordersNotregistered = $dbTable->getCustomerOrderStats("notreg");
        
        $this->view->dataCompany = $dbTable->getCustomerStats("comp");    
        $this->view->ordersCompany = $dbTable->getCustomerOrderStats("comp");
    }

    /**
     * show the count of first orders in the defined timeslot
     * @author alex
     * @sinces 20.12.2010
     */
    public function firstordersAction() {
        // get db
        $db = Zend_Registry::get('dbAdapter');

        $from = date('d.m.Y', time() - 10 * 24 * 60 * 60);
        $until = date('d.m.Y');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $from = $post['fromD'];
            $until = $post['untilD'];

            // convert time in sql time format
            $fromFormatted  = date(DATE_DB, strtotime($from));
            $untilFormatted = date(DATETIME_DB, strtotime($until . " 23:59:59"));

            $days = array();
            for($d = strtotime($from); $d <= strtotime($until . " 23:59:59"); $d+=(60*60*24)) {
                $days[] = date('d.m.Y', $d);
            }
            $this->view->days = $days;


            // first orders made by registered customers in selected time slot
            $firstorders_registered_timeslot_sql = $db->fetchAll(
                "SELECT count(email) as count, DATE_FORMAT(v.time, '%d.%m.%Y') as datum FROM data_view_customer_registered_first_order v where v.time BETWEEN '" . $fromFormatted . "' AND '" . $untilFormatted . "' GROUP BY YEAR(v.time), MONTH(v.time), DAY(v.time)"
            );

            $firstorders_registered_timeslot = array();
            foreach ($firstorders_registered_timeslot_sql as $val) {
                $firstorders_registered_timeslot[$val['datum']] = $val['count'];
            }
            $this->view->firstorders_registered_timeslot = $firstorders_registered_timeslot;


            // first orders made by customers, who have never been registered in selected time slot
            $firstorders_nonregistered_timeslot_sql = $db->fetchAll(
                "SELECT count(email) as count, DATE_FORMAT(v.time, '%d.%m.%Y') as datum FROM data_view_customer_unregistered_first_order v where v.time BETWEEN '" . $fromFormatted . "' AND '" . $untilFormatted . "' GROUP BY YEAR(v.time), MONTH(v.time), DAY(v.time)"
            );

            $firstorders_nonregistered_timeslot = array();
            foreach ($firstorders_nonregistered_timeslot_sql as $val) {
                $firstorders_nonregistered_timeslot[$val['datum']] = $val['count'];
            }
            $this->view->firstorders_nonregistered_timeslot = $firstorders_nonregistered_timeslot;

            
            // first orders made by company customers in selected time slot
            $firstorders_company_timeslot_sql = $db->fetchAll(
                "SELECT count(email) as count, DATE_FORMAT(v.time, '%d.%m.%Y') as datum FROM data_view_customer_company_first_order v where v.time BETWEEN '" . $fromFormatted . "' AND '" . $untilFormatted . "' GROUP BY YEAR(v.time), MONTH(v.time), DAY(v.time)"
            );

            $firstorders_company_timeslot = array();
            foreach ($firstorders_company_timeslot_sql as $val) {
                $firstorders_company_timeslot[$val['datum']] = $val['count'];
            }
            $this->view->firstorders_company_timeslot = $firstorders_company_timeslot;

        }


        // first orders made by registered customers
        $firstorders_registered_sql = $db->fetchAll(
                        "SELECT count(email) as count, YEAR(v.time) as year, MONTH(v.time) as month FROM data_view_customer_registered_first_order v GROUP BY year, month"
        );

        // fill array with results in form [year][month] => count
        $firstorders_registered = array();
        foreach ($firstorders_registered_sql as $val) {
            if (!array_key_exists($val['year'], $firstorders_registered)) {
                $firstorders_registered[$val['year']] = array();
            }
            $firstorders_registered[$val['year']][$val['month']] = $val['count'];
        }
        $this->view->firstorders_registered = $firstorders_registered;

        
        // first orders made by customers, who have never been registered
        $firstorders_nonregistered_sql = $db->fetchAll(
                        "SELECT count(email) as count, YEAR(v.time) as year, MONTH(v.time) as month FROM data_view_customer_unregistered_first_order v GROUP BY year, month"
        );

        // fill array with results in form [year][month] => count
        $firstorders_nonregistered = array();
        foreach ($firstorders_nonregistered_sql as $val) {
            if (!array_key_exists($val['year'], $firstorders_nonregistered)) {
                $firstorders_nonregistered[$val['year']] = array();
            }
            $firstorders_nonregistered[$val['year']][$val['month']] = $val['count'];
        }
        $this->view->firstorders_nonregistered = $firstorders_nonregistered;

        
        // first orders made by company customers
        $firstorders_company_sql = $db->fetchAll(
                        "SELECT count(email) as count, YEAR(v.time) as year, MONTH(v.time) as month FROM data_view_customer_company_first_order v GROUP BY year, month"
        );

        // fill array with results in form [year][month] => count
        $firstorders_company = array();
        foreach ($firstorders_company_sql as $val) {
            if (!array_key_exists($val['year'], $firstorders_company)) {
                $firstorders_company[$val['year']] = array();
            }
            $firstorders_company[$val['year']][$val['month']] = $val['count'];
        }
        $this->view->firstorders_company = $firstorders_company;
    }

    /**
     * show the count of users, who made certain count of orders
     * @author alex
     * @sinces 20.12.2010
     */
    public function repeatedordersAction() {
        // get db
        $db = Zend_Registry::get('dbAdapter');

        // count of orders made by registered customers
        $registered = $db->fetchAll(
                        "SELECT count(v.email) as users, v.count FROM data_view_customer_registered_first_order v group by v.count order by v.count"
        );
        $sumreg = 0;
        foreach ($registered as $reg) {
            $sumreg += $reg['users'];
        }

        $this->view->sumreg = $sumreg;
        $this->view->registered = $registered;


        // count of orders made by unregistered customers
        $unregistered = $db->fetchAll(
                        "SELECT count(v.email) as users, v.count FROM data_view_customer_unregistered_first_order v group by v.count order by v.count"
        );

        $sumunreg = 0;
        foreach ($unregistered as $unreg) {
            $sumunreg += $unreg['users'];
        }

        $this->view->sumunreg = $sumunreg;
        $this->view->unregistered = $unregistered;


        // count of orders made by company customers
        $companies = $db->fetchAll(
                        "SELECT count(v.email) as users, v.count FROM data_view_customer_company_first_order v group by v.count order by v.count"
        );

        $sumcomp = 0;
        foreach ($companies as $comp) {
            $sumcomp += $comp['users'];
        }

        $this->view->sumcomp = $sumcomp;
        $this->view->companies = $companies;
    }

}
