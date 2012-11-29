<?php

/**
 * Description of Administration_Stats_Flash
 * @author vpriem
 * @since 10.11.2010
 */
class Administration_Stats_FlashController extends Default_Controller_AdministrationBase {

    /**
     * Init
     * @author vpriem
     * @since 10.11.2010
     */
    public function init() {

        parent::init();
        
        // print only xml
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Display the weekly sales
     * @author vpriem
     * @since 10.11.2010
     */
    public function salesAction() {

        // get params
        $request = $this->getRequest();
        $year = $request->getParam('year', date('Y'));
        $week = $request->getParam('week', date('W'));
        $type = $request->getParam('type', 'sales');
        $city = $request->getParam('city');

        // get week
        $start = mktime(0, 0, 0, 1, 1, $year);
        while (intval(date('W', $start)) != intval($week)) {
            $start += 24 * 60 * 60;
        }
        $end = $start + 7 * 24 * 60 * 59;

        // get type
        $types = array(
            'count' => "Bestellungen",
            'sales' => "Umsatz",
            'commission' => "Provision",
        );
        if (!array_key_exists($type, $types)) {
            $type = "sales";
        }

        // get db
        $db = Zend_Registry::get('dbAdapter');

        $binds = array(
            date("Y-m-d 00:00:00", $start),
            date("Y-m-d 23:59:59", $end)
        );
        if ($city !== null) {
            array_unshift($binds, $city);
        }
        $results = $db->fetchAll(
            "SELECT DAYOFWEEK(o.time) `day`, COUNT(o.orderId) `count`, SUM(o.sales) / 100 `sales`, SUM(o.commission) / 100 `commission`
            FROM `data_view_sales` o " .
                ($city !== null ?
                    "INNER JOIN `orders_location` ol ON o.orderId = ol.orderId
            INNER JOIN `city` t ON ol.cityId = t.id AND t.city = ? " : "") . "
            WHERE o.time BETWEEN ? AND ?
            GROUP BY `day`
            ORDER BY o.time", $binds
        );
        $sales = array();
        foreach ($results as $res) {
            if (!is_array($sales['Gesamt'])) {
                $sales['Gesamt'] = array();
            }
            $sales['Gesamt'][($res['day'] == 1 ? 8 : $res['day'])] = $res[$type];
        }

        // print data
        echo
        '<graph
            caption="' . $types[$type] . '"
            subCaption="'.__b("von") . date("d.m.Y", $start) . __b("bis") . date("d.m.Y", $end) . '"
            showvalues="1"
            decimalPrecision="' . ($type == "count" ? 0 : 2) . '"
            decimalSeparator=","
            thousandSeparator="."
            formatNumberScale="0">
            <categories>
                <category name="'.__b("Montag").'"/>
                <category name="'.__b("Dienstag").'"/>
                <category name="'.__b("Mittwoch").'"/>
                <category name="'.__b("Donerstag").'"/>
                <category name="'.__b("Freitag").'"/>
                <category name="'.__b("Samstag").'"/>
                <category name="'.__b("Sonntag").'"/>
            </categories>';
        foreach ($sales as $days) {
            for ($i = 2; $i < 9; $i++) {
                if (!array_key_exists($i, $days)) {
                    $days[$i] = 0;
                }
            }
            ksort($days);
            echo
            '<dataset seriesName="Gesamt" color="f5d18c"><set value="' . implode('"/><set value="', $days) . '"/></dataset>';
        }
        echo
        '</graph>';
    }

    /**
     * Display the weekly sales by mode
     * @author vpriem
     * @since 10.11.2010
     */
    public function salesmodeAction() {

        // get params
        $request = $this->getRequest();
        $year = $request->getParam('year', date('Y'));
        $week = $request->getParam('week', date('W'));
        $type = $request->getParam('type', 'sales');
        $city = $request->getParam('city');

        // get week
        $start = mktime(0, 0, 0, 1, 1, $year);
        while (intval(date('W', $start)) != intval($week)) {
            $start += 24 * 60 * 60;
        }
        $end = $start + 7 * 24 * 60 * 59;

        // get type
        $types = array(
            'count' => "Bestellungen",
            'sales' => "Umsatz",
            'commission' => "Provision",
        );
        if (!array_key_exists($type, $types)) {
            $type = "sales";
        }

        // get db
        $db = Zend_Registry::get('dbAdapter');

        $binds = array(
            date("Y-m-d 00:00:00", $start),
            date("Y-m-d 23:59:59", $end)
        );
        if ($city !== null) {
            array_unshift($binds, $city);
        }
        $results = $db->fetchAll(
            "SELECT o.mode, DAYOFWEEK(o.time) `day`, COUNT(o.orderId) `count`, SUM(o.sales) / 100 `sales`, SUM(o.commission) / 100 `commission`
            FROM `data_view_sales` o " .
                ($city !== null ?
                    "INNER JOIN `orders_location` ol ON o.orderId = ol.orderId
            INNER JOIN `city` t ON ol.cityId = t.id AND t.city = ? " : "") . "
            WHERE o.time BETWEEN ? AND ?
            GROUP BY o.mode, `day`
            ORDER BY `day`, o.mode", $binds
        );
        $sales = array();
        foreach ($results as $res) {
            if (!is_array($sales[$res['mode']])) {
                $sales[$res['mode']] = array();
            }
            $sales[$res['mode']][($res['day'] == 1 ? 8 : $res['day'])] = $res[$type];
        }

        // get modes
        $modes = array(
            'rest' => "Restaurant",
            'cater' => "Caterer",
            'great' => "Grosshaendler",
            'fruit' => "Obsthaendler",
            'canteen' => "Kantine",
            'billasset' => "Rechnungsposten",
        );

        // get colors
        $colors = array(
            'rest' => "f5d18c",
            'cater' => "ee704b",
            'great' => "277aa7",
            'fruit' => "92c23e",
            'canteen' => "999999"
        );

        // print data
        echo
        '<graph 
            caption="' . $types[$type] . '"
            subCaption="'.__b("von"). date("d.m.Y", $start) .__b(" bis ") . date("d.m.Y", $end) . '"
            showvalues="1"
            decimalPrecision="' . ($type == "count" ? 0 : 2) . '"
            decimalSeparator=","
            thousandSeparator="."
            formatNumberScale="0">
            <categories>
                <category name="'.__b("Montag").'"/>
                <category name="'.__b("Dienstag").'"/>
                <category name="'.__b("Mittwoch").'"/>
                <category name="'.__b("Donerstag").'"/>
                <category name="'.__b("Freitag").'"/>
                <category name="'.__b("Samstag").'"/>
                <category name="'.__b("Sonntag").'"/>
            </categories>';
        foreach ($sales as $mode => $days) {
            for ($i = 2; $i < 9; $i++) {
                if (!array_key_exists($i, $days)) {
                    $days[$i] = 0;
                }
            }
            ksort($days);
            echo
            '<dataset seriesName="' . $modes[$mode] . '" color="' . $colors[$mode] . '"><set value="' . implode('"/><set value="', $days) . '"/></dataset>';
        }
        echo
        '</graph>';
    }

    /**
     * Display the weekly sales by kind
     * @author vpriem
     * @since 10.11.2010
     */
    public function saleskindAction() {

        // get params
        $request = $this->getRequest();
        $year = $request->getParam('year', date('Y'));
        $week = $request->getParam('week', date('W'));
        $type = $request->getParam('type', 'sales');
        $city = $request->getParam('city');

        // get week
        $start = mktime(0, 0, 0, 1, 1, $year);
        while (intval(date('W', $start)) != intval($week)) {
            $start += 24 * 60 * 60;
        }
        $end = $start + 7 * 24 * 60 * 59;

        // get type
        $types = array(
            'count' => "Bestellungen",
            'sales' => "Umsatz",
            'commission' => "Provision",
        );
        if (!array_key_exists($type, $types)) {
            $type = "sales";
        }

        // get db
        $db = Zend_Registry::get('dbAdapter');

        $binds = array(
            date("Y-m-d 00:00:00", $start),
            date("Y-m-d 23:59:59", $end)
        );
        if ($city !== null) {
            array_unshift($binds, $city);
        }
        $results = $db->fetchAll(
            "SELECT o.kind, DAYOFWEEK(o.time) `day`, COUNT(o.orderId) `count`, SUM(o.sales) / 100 `sales`, SUM(o.commission) / 100 `commission`
            FROM `data_view_sales` o " .
                ($city !== null ?
                    "INNER JOIN `orders_location` ol ON o.orderId = ol.orderId
            INNER JOIN `city` t ON ol.cityId = t.id AND t.ort = ? " : "") . "
            WHERE o.time BETWEEN ? AND ?
            GROUP BY o.kind, `day`
            ORDER BY `day`, o.kind", $binds
        );
        $sales = array();
        foreach ($results as $res) {
            if (!is_array($sales[$res['kind']])) {
                $sales[$res['kind']] = array();
            }
            $sales[$res['kind']][($res['day'] == 1 ? 8 : $res['day'])] = $res[$type];
        }

        // get modes
        $kinds = array(
            'priv' => "Privat",
            'comp' => "Firmen",
        );

        // get colors
        $colors = array(
            'priv' => "f5d18c",
            'comp' => "ee704b",
        );

        // print data
        echo
        '<graph
            caption="' . $types[$type] . '"
            subCaption="'.__b("von"). date("d.m.Y", $start) .__b(" bis ") . date("d.m.Y", $end) . '"
            showvalues="1"
            decimalPrecision="' . ($type == "count" ? 0 : 2) . '"
            decimalSeparator=","
            thousandSeparator="."
            formatNumberScale="0">
            <categories>
                <category name="'.__b("Montag").'"/>
                <category name="'.__b("Dienstag").'"/>
                <category name="'.__b("Mittwoch").'"/>
                <category name="'.__b("Donerstag").'"/>
                <category name="'.__b("Freitag").'"/>
                <category name="'.__b("Samstag").'"/>
                <category name="'.__b("Sonntag").'"/>
            </categories>';
        foreach ($sales as $kind => $days) {
            for ($i = 2; $i < 9; $i++) {
                if (!array_key_exists($i, $days)) {
                    $days[$i] = 0;
                }
            }
            ksort($days);
            echo
            '<dataset seriesName="' . $kinds[$kind] . '" color="' . $colors[$kind] . '"><set value="' . implode('"/><set value="', $days) . '"/></dataset>';
        }
        echo
        '</graph>';
    }

    /**
     * @author mlaug
     * @since 31.03.2011
     */
    public function marketingAction() {

        $request = $this->getRequest();
        $type = $request->getParam('type');
        $from = $request->getParam('from');   
        
        
        if ($from !== null) {
            $from = strtotime($from);
        }
        $until = $request->getParam('until');
        if ($until !== null) {
            $until = strtotime($until);
        }        
        if ($until < $from || (isset($from) && !isset($until))) {
            $until = strtotime("now");
        }
        
        $this->logger->info($from);
        $this->logger->info($until);
        switch ($type) {
            case 'conversion':
                break;
            case 'orders':
                break;
            default:
                $type = 'orders';
        }

        $categories = $channels = $colors = array();
        
        $orders = Yourdelivery_Model_Order_Salechannels::allByDay($from, $until);
        foreach ($orders as $o) {
            $categories[] = $o['day'];
            
            if (!is_array($channels[$o['saleChannel']])) {
                $channels[$o['saleChannel']] = array();
            }
            $channels[$o['saleChannel']][$o['day']] = $o[$type];
            
            $colors[$o['saleChannel']] = Yourdelivery_Model_Order_Salechannels::getSaleChannelColor($o['saleChannel']);
        }
        $categories = array_unique($categories);
        sort($categories);
        

        // print data
        echo
        '<graph
            caption="SaleChannel"
            subCaption="' . $type . '"
            showvalues="1"
            decimalPrecision="' . ($type == "orders" ? 0 : 2) . '"
            decimalSeparator=","
            thousandSeparator="."
            formatNumberScale="0">
            <categories>';
        foreach ($categories as $category) {
            echo '<category name="' . $category . '"/>' . LF;
        }
        echo '</categories>' . LF;
        foreach ($channels as $channel => $values) {
            echo '<dataset seriesName="' . $channel . '" color="' . $colors[$channel] . '">' . LF;
            foreach ($categories as $category) {
                echo '<set value="' . ($values[$category] ? $values[$category] : 0) . '"/>' . LF;
            }
            echo '</dataset>' . LF;
        }
        echo '</graph>';
        
    }
    
}
