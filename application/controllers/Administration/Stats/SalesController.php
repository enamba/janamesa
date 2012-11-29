<?php

/**
 * @author vpriem
 * @sinces 04.11.2010
 */
class Administration_Stats_SalesController extends Default_Controller_AdministrationBase {

    /**
     * @author alex
     * @sinces 09.12.2010
     */
    public function indexAction() {
        // get db
        $db = Zend_Registry::get('dbAdapter');

        // statistics for today
        $today = $db->fetchRow(
                        "SELECT COUNT(o.orderId) `count`, SUM(o.sales) `sales`, SUM(o.commission) `commission`
            FROM `data_view_sales` o
            WHERE o.time BETWEEN '" . date("Y-m-d 00:00:00", time()) . "' AND NOW()");
        $this->view->today = $today;

        // statistics for this week
        $thisweek = $db->fetchRow(
                        "SELECT COUNT(o.orderId) `count`, SUM(o.sales) `sales`, SUM(o.commission) `commission`
            FROM `data_view_sales` o
            WHERE o.time BETWEEN '" . date("Y-m-d 00:00:00", (date('w') == 1 ? time() : strtotime('last Monday'))) . "' AND NOW()");
        $this->view->thisweek = $thisweek;

        // statistics for this month
        $thismonth = $db->fetchRow(
                        "SELECT COUNT(o.orderId) `count`, SUM(o.sales) `sales`, SUM(o.commission) `commission`
            FROM `data_view_sales` o
            WHERE o.time BETWEEN '" . date("Y-m-01 00:00:00", time()) . "' AND NOW()");
        $this->view->thismonth = $thismonth;
    }

    /**
     * @author vpriem
     * @sinces 04.11.2010
     */
    public function summaryAction() {

        $kind = '';
        if($this->getRequest()->getParam('kind')=='priv'){
            $kind = "AND o.kind='priv'";
        }else if($this->getRequest()->getParam('kind')=='comp'){
            $kind = "AND o.kind!='priv'";
        }
        
        // get db
        $db = Zend_Registry::get('dbAdapter');

        // this week
        $thisweek = $db->fetchAll(
                        "SELECT DATE(o.time) `day`, COUNT(o.orderId) `count`, SUM(o.sales) `sales`, SUM(o.commission) `commission`
            FROM `data_view_sales` o
            WHERE o.time BETWEEN ? AND NOW()
            $kind
            GROUP BY `day`
            ORDER BY `day`", date("Y-m-d 00:00:00", (date('w') == 1 ? time() : strtotime('last Monday')))
        );

        foreach ($thisweek as $key => $day) {
            $thisweek[$key]['day'] = substr($day['day'], 8, 2) . '.' . substr($day['day'], 5, 2) . '.' . substr($day['day'], 0, 4);
        }

        $this->view->thisweek = $thisweek;

        // this month
        $thismonth = $db->fetchAll(
                        "SELECT o.time ordertime, WEEKOFYEAR(o.time) `week`, COUNT(o.orderId) `count`, SUM(o.sales) `sales`, SUM(o.commission) `commission`
            FROM `data_view_sales` o
            WHERE o.time BETWEEN ? AND NOW()
            $kind
            GROUP BY `week`
            ORDER BY ordertime", date("Y-m-d 00:00:00", Default_Helpers_Date::getLastMontayBeforeStartOfMonth())
        );
        $this->view->thismonth = $thismonth;

        // this year
        $thisyear = $db->fetchAll(
                        "SELECT MONTH(o.time) `month`, COUNT(o.orderId) `count`, SUM(o.sales) `sales`, SUM(o.commission) `commission`
            FROM `data_view_sales` o
            WHERE o.time BETWEEN ? AND NOW()
            $kind
            GROUP BY `month`
            ORDER BY `month`", date("Y-m-d 00:00:00", mktime(0, 0, 0, 1, 1, date("Y")))
        );
        $yearstats = array();
        foreach ($thisyear as $key => $monthdata) {
            $yearstats[$monthdata['month']] = $monthdata;
        }
        $this->view->thisyear = $yearstats;

        if($this->getRequest()->getParam('kind')=='priv'){
            $kind = "WHERE o.kind='priv'";
        }else if($this->getRequest()->getParam('kind')=='comp'){
            $kind = "WHERE o.kind!='priv'";
        }
        // per year
        $peryear = $db->fetchAll(
                        "SELECT YEAR(o.time) `year`, COUNT(o.orderId) `count`, SUM(o.sales) `sales`, SUM(o.commission) `commission`
            FROM `data_view_sales` o
            $kind
            GROUP BY `year`
            ORDER BY `year`"
        );
        $this->view->peryear = $peryear;
    }

    /**
     * @author vpriem
     * @sinces 04.11.2010
     */
    public function toptenAction() {
        // show default time slot of one week
        $from = date('d.m.Y', time() - 7 * 24 * 60 * 60);
        $until = date('d.m.Y');
        $plzFilter = "";
        
        if ($this->_request->isPost()) {
            $from = $this->_request->fromD;
            $until = $this->_request->untilD;
            $plz = $this->_request->plz;
            
            if (!is_null($plz) && (strcmp($plz, 'all')!=0)) {
                $plzFilter = " AND `o`.`plz` LIKE '" . $plz . "%' ";
                $plzFilter_unreg = " AND `ol`.`plz` LIKE '" . $plz . "%' ";
                $this->view->plz = $plz;
            }
        }

        // if time slot was defined, convert it in sql time format
        $fromFormatted = date(DATE_DB, strtotime($from));
        $untilFormatted = date(DATETIME_DB, strtotime($until . " 23:59:59"));

        // get db
        $db = Zend_Registry::get('dbAdapter');

        // restaurants
        $restaurants = $db->fetchAll(
                        "SELECT r.id, r.name, SUM(o.sales) `sales`
            FROM `data_view_sales` o
            INNER JOIN `restaurants` r ON o.restaurantId = r.id
                WHERE r.deleted = 0 AND time BETWEEN '" . $fromFormatted . "' AND '" . $untilFormatted . "'" . $plzFilter .
            "GROUP BY r.id
            ORDER BY `sales` DESC
            LIMIT 10;"
        );
        $this->view->restaurants = $restaurants;

        // registered customers
        $regcustomers = $db->fetchAll(
                        "SELECT c.id, c.name, c.prename, SUM(o.sales) `sales`
            FROM `data_view_sales` o
            INNER JOIN `customers` c ON o.customerId = c.id
                AND c.deleted = 0
            LEFT JOIN `customer_company` cc ON c.id = cc.customerId
            WHERE cc.id IS NULL AND time BETWEEN '" . $fromFormatted . "' AND '" . $untilFormatted . "'" . $plzFilter .
            "GROUP BY c.id
            ORDER BY `sales` DESC
            LIMIT 10;"
        );
        $this->view->regcustomers = $regcustomers;

        // unregistered customers
        $unregcustomers = $db->fetchAll(
                        "SELECT oc.email, oc.prename, oc.name, o.total + COALESCE(o.serviceDeliverCost, 0) + COALESCE(o.courierCost, 0) `sales`
                FROM `orders` o
                INNER JOIN `orders_customer` oc on o.id = oc.orderId
                INNER JOIN `orders_location` ol on o.id = ol.orderId
                LEFT JOIN `customers` c on c.email = oc.email
                WHERE c.id IS NULL
                AND time BETWEEN '" . $fromFormatted . "' AND '" . $untilFormatted . "'" . $plzFilter_unreg . " AND o.state>0
                GROUP BY oc.email
                ORDER BY `sales` DESC
                LIMIT 10;"
        );
        $this->view->unregcustomers = $unregcustomers;

        // companies
        $companies = $db->fetchAll(
                        "SELECT c.id, c.name, SUM(o.sales) `sales`
            FROM `data_view_sales` o
            INNER JOIN `companys` c ON o.companyId = c.id
                AND c.deleted = 0 AND time BETWEEN '" . $fromFormatted . "' AND '" . $untilFormatted . "'" . $plzFilter . "
            GROUP BY c.id
            ORDER BY `sales` DESC
            LIMIT 10;"
        );
        $this->view->companies = $companies;

        $this->view->from = $from;
        $this->view->until = $until;
    }

    /**
     * @author vpriem
     * @sinces 04.11.2010
     */
    public function detailAction() {

        // get params
        $request = $this->getRequest();
        $year = $request->getParam('year', date('Y'));
        $plz = $request->getParam('plz', -1);

        // get db
        $db = Zend_Registry::get('dbAdapter');

        // plz
        $this->view->plz = $plz;
        $this->view->plzs = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

        // years
        $years = array();
        for ($y = 2009, $n = date('Y'); $y <= $n; $y++) {
            $years[] = $y;
        }
        $this->view->year = $year;
        $this->view->years = $years;

        // sales
        if ($plz > -1) {
            $results = $db->fetchAll(
                            "SELECT o.mode, MONTH(o.time) `month`, COUNT(o.orderId) `count`, SUM(o.sales) `sales`, SUM(o.commission) `commission`
                FROM `data_view_sales` o
                INNER JOIN `restaurants` r ON o.restaurantId = r.id
                    AND SUBSTRING(CONCAT('0', r.plz), -5) LIKE ?
                WHERE YEAR(o.time) = ?
                GROUP BY o.mode, `month`", array($plz . "%", $year)
            );
        } else {
            $results = $db->fetchAll(
                            "SELECT o.mode, MONTH(o.time) `month`, COUNT(o.orderId) `count`, SUM(o.sales) `sales`, SUM(o.commission) `commission`
                FROM `data_view_sales` o
                WHERE YEAR(o.time) = ?
                GROUP BY o.mode, `month`", $year
            );
        }
        $orders = $sales = $commissions = array('sum' => array());

        foreach ($results as $res) {
            if (!is_array($orders[$res['mode']])) {
                $orders[$res['mode']] = array('count' => 0);
            }
            $orders[$res['mode']][$res['month']] = $res['count'];
            $orders[$res['mode']]['count'] += $res['count'];
            $orders['sum'][$res['month']] += $res['count'];
            $orders['sum']['count'] += $res['count'];

            if (!is_array($sales[$res['mode']])) {
                $sales[$res['mode']] = array('count' => 0);
            }
            $sales[$res['mode']][$res['month']] = $res['sales'];
            $sales[$res['mode']]['count'] += $res['sales'];
            $sales['sum'][$res['month']] += $res['sales'];
            $sales['sum']['count'] += $res['sales'];

            if (!is_array($commissions[$res['mode']])) {
                $commissions[$res['mode']] = array('count' => 0);
            }
            $commissions[$res['mode']][$res['month']] = $res['commission'];
            $commissions[$res['mode']]['count'] += $res['commission'];
            $commissions['sum'][$res['month']] += $res['commission'];
            $commissions['sum']['count'] += $res['commission'];
        }

        $this->view->orders = $orders;
        $this->view->sales = $sales;
        $this->view->commissions = $commissions;

        // modes
        $this->view->modes = array('billasset' => "Rechnungsposten", 'rest' => "Restaurant", 'cater' => "Caterer", 'fruit' => "Obsthändler", 'great' => "Großhändler", 'canteen' => "Kantine", 'sum' => Gesamt);
    }

    /**
     * Sales statistiks per payment kind
     * @author alex
     * @sinces 01.02.2011
     */
    public function paymentkindAction() {
        // get db
        $db = Zend_Registry::get('dbAdapter');

        $request = $this->getRequest();

        $modeFilter = "";
        $mode = 'all';
        
        if ($request->isPost()) {
            $post = $request->getPost();
            $mode = $post['mode'];
            
            if (!is_null($mode) && strcmp($mode, 'all')!=0) {
                $modeFilter = sprintf(" WHERE `mode` = '%s' ", $mode);
            }            
        }
                
        $result = array();
        
        // all orders by payment kind
        $data = $db->fetchAll(
                        "SELECT payment, SUM(sales) as betrag, SUM(discountAmount), SUM(sales - discountAmount) AS total, MONTH(time) as month, YEAR(time) as year
                    FROM `data_view_sales` " . $modeFilter . " GROUP BY MONTH(time), YEAR(time), payment ORDER BY year desc, payment");

        foreach ($data as $d) {
            $result[$d['year']][$d['payment']][$d['month']] = $d;
            $result[$d['year']][$d['payment']]['sumbetrag'] += $d['betrag'];
            $result[$d['year']][$d['payment']]['sum'] += $d['total'];
        }

        // sum of all data
        $sum = array();
        foreach ($result as $year => $d1) {
            foreach ($d1 as $art => $d2) {
                for ($month = 1; $month < 13; $month++) {
                    $sum[$year][$month] += $d2[$month]['total'];
                    $sumbetrag[$year][$month] += $d2[$month]['betrag'];
                    $sum[$year]['sum'] += $d2[$month]['total'];
                    $sumbetrag[$year]['sum'] += $d2[$month]['betrag'];
                }
            }
        }

        $this->view->paymentkinds = array('bill' => 'Rechnung', 'bar' => 'Barzahlung', 'credit' => 'Kredit', 'paypal' => 'Paypal', 'debit' => 'Debit', 'ebanking' => 'E-Banking');
        $this->view->months = array(1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
        $this->view->paymentstats = $result;
        $this->view->sum = $sum;
        $this->view->sumbetrag = $sumbetrag;
        $this->view->mode = $mode;
    }

    public function matrixAction() {

        // get db
        $db = Zend_Registry::get('dbAdapter');

        $year = '2011';

        $week = array();
        for ($j = 8; $j < 24; $j++) {
            for ($i = 1; $i <= 7; $i++) {
                $week[$j][$i] = $db->fetchAll(sprintf("
                    SELECT  count(id)/((DATEDIFF(DATE(NOW()), '2011-01-01')+1)/7) AS count,
                    sum(o.total + o.serviceDeliverCost + o.courierCost)/((DATEDIFF(DATE(NOW()), '2011-01-01')+1)/7) AS amount
                        FROM orders o
                            WHERE
                                o.kind='priv'
                                AND o.state>0
                                AND YEAR(o.time)=%d
                                AND HOUR(o.time) >= %d
                                AND HOUR(o.time) < %d
                                AND WEEKDAY(o.time) + 1 = %d;",
                                        $year, $j, $j + 1, $i));
            }
        }

        $this->view->week = $week;
    }

}
