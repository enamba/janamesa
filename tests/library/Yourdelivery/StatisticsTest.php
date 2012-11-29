<?php
/**
 * Test suite for Yourdelivery statistics module
 *
 * @author Marek Hejduk <m.hejduk@pyszne.pl>
 * 
 * @runTestsInSeparateProcesses
 */
class YourdeliveryStatisticsTest extends Yourdelivery_Test {
    /**
     * Test case for overall stats - unregistered company orders
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 3.07.2012
     */
    public function testGetUnregisteredCompanyOrders() {
        list($from, $until) = $this->getTimestamps();
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $rawQuery = "SELECT o.id, o.time, r.name AS rname, r.street AS rstreet, r.hausnr AS rhausnr, r.plz AS rplz, r.tel AS rtel, o.total, cnr.prename, cnr.name, cnr.email, l.tel, l.plz, l.street, l.hausnr, l.companyName, l.comment FROM orders o JOIN orders_location l ON o.id = l.orderId JOIN orders_customer cnr ON o.id=cnr.orderId JOIN restaurants r ON r.id = o.restaurantId WHERE l.companyName != '' AND l.companyName NOT IN (SELECT c.name FROM companys c) AND o.time BETWEEN from_unixtime(".$from.") AND from_unixtime(".$until.") and o.state > 0";
        $rawResult = $result = $db->fetchAll($rawQuery, array(), Zend_Db::FETCH_OBJ);
        $testedResult = Yourdelivery_Statistics_Overallstats::getUnregisteredCompanyOrders($from, $until);
        $this->compareResults($rawResult, $testedResult, 10, function ($a, $b) {
            return ($a->id != $b->id)
                ? (($a->id > $b->id)? 1: -1)
                : 0;
        });
    }

    /**
     * Test case for overall stats - used discounts (standard mode)
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 3.07.2012
     */
    public function testGetUsedDiscountsWithoutDateGroupping() {
        list($from, $until) = $this->getTimestamps();
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $rawQuery = "select rabatt_codes.code as name, count(*) as count, sum(discountAmount) as sum, rabatt.name as rabattName, rabatt.id as rabattId from orders join rabatt_codes on orders.rabattCodeId=rabatt_codes.id join rabatt on rabatt_codes.rabattId=rabatt.id where time between from_unixtime($from) and from_unixtime($until) and rabattCodeId>1 and orders.state>0 group by rabattCodeId order by sum";
        $rawResult = $db->fetchAll($rawQuery, array(), PDO::FETCH_ASSOC);
        $testedResult = Yourdelivery_Statistics_Overallstats::getUsedDiscounts($from, $until);
        $this->compareResults($rawResult, $testedResult, 10, function ($a, $b) {
            if ($a['sum'] != $b['sum'])
                return ($a['sum'] > $b['sum'])? 1: -1;
            if (($cmp = (strcmp($a['rabattName'], $b['rabattName']))) !== 0) {
                return $cmp;
            }
            if (($cmp = (strcmp($a['name'], $b['name']))) !== 0) {
                return $cmp;
            }
        });
    }

    /**
     * Test case for overall stats - used discounts with groupping by time periods
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 3.07.2012
     */
    public function testGetUsedDiscountsWithDateGroupping() {
        list($from, $until) = $this->getTimestamps();
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $rawQuery = "select count(rabatt_codes.id) as count, sum(discountAmount) as sum, rabatt.name as rabattName, rabatt.id as rabattId, YEAR(orders.time) as year, MONTH(orders.time) as month from orders join rabatt_codes on orders.rabattCodeId=rabatt_codes.id join rabatt on rabatt_codes.rabattId=rabatt.id where rabattCodeId>1 and orders.state>0 and orders.time>FROM_UNIXTIME($from) and orders.time<FROM_UNIXTIME($until) group by rabattId, year, month order by year desc, month, sum desc";
        $rawResult = $db->fetchAll($rawQuery, array(), PDO::FETCH_ASSOC);
        $testedResult = Yourdelivery_Statistics_Overallstats::getUsedDiscounts($from, $until, true);
        $this->compareResults($rawResult, $testedResult, 10, function ($a, $b) {
            if ($a['year'] != $b['year'])
                return ($a['year'] < $b['year'])? 1: -1;
            if ($a['month'] != $b['month'])
                return ($a['month'] > $b['month'])? 1: -1;
            if ($a['sum'] != $b['sum'])
                return ($a['sum'] < $b['sum'])? 1: -1;
            if (($cmp = (strcmp($a['rabattName'], $b['rabattName']))) !== 0) {
                return $cmp;
            }
            if (($cmp = (strcmp($a['name'], $b['name']))) !== 0) {
                return $cmp;
            }
        });
    }

    /**
     * Test case for restaurant stats - top sold meals
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 3.07.2012
     */
    public function testGetTopMeals() {
        $restaurantId = $this->getRestaurantId();
        $count = rand(10, 50);
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $rawQuery = sprintf('SELECT SUM(obm.count) AS count, m.id, m.name, mc.id as categoryId, mc.name as categoryName FROM orders_bucket_meals obm JOIN orders o ON obm.orderId=o.id JOIN meals m ON obm.mealId=m.id JOIN meal_categories mc ON m.categoryId=mc.id  WHERE o.restaurantId=%d and m.deleted=0 and m.status=1 and o.state>0 GROUP BY m.id ORDER BY count DESC LIMIT 0 , %d', $restaurantId, $count);
        $rawResult = $db->fetchAll($rawQuery, array(), PDO::FETCH_OBJ);
        $testedResult = Yourdelivery_Statistics_Restaurant::getTopMeals($restaurantId, $count);
        $this->compareResults($rawResult, $testedResult, 10, function ($a, $b) {
            if ($a->count != $b->count)
                return ($a->count > $b->count)? 1: -1;
            return ($a->id != $b->id)
                ? (($a->id > $b->id)? 1: -1)
                : 0;
        });
    }

    /**
     * Test case for restaurant stats - top sold meal categories
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 3.07.2012
     */
    public function testGetTopCategories() {
        $restaurantId = $this->getRestaurantId();
        $count = rand(10, 50);
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $rawQuery = sprintf('SELECT SUM(obm.count) AS count, mc.id, mc.name FROM orders_bucket_meals obm JOIN orders o ON obm.orderId=o.id JOIN meals m ON obm.mealId=m.id JOIN meal_categories mc ON m.categoryId=mc.id WHERE o.restaurantId=%d and m.deleted=0 and m.status=1 and o.state>0 GROUP BY mc.id ORDER BY count DESC LIMIT 0 , %d', $restaurantId, $count);
        $rawResult = $db->fetchAll($rawQuery, array(), PDO::FETCH_OBJ);
        $testedResult = Yourdelivery_Statistics_Restaurant::getTopCategories($restaurantId, $count);
        $this->compareResults($rawResult, $testedResult, 10, function ($a, $b) {
            if ($a->count != $b->count)
                return ($a->count > $b->count)? 1: -1;
            return ($a->id != $b->id)
                ? (($a->id > $b->id)? 1: -1)
                : 0;
        });
    }

    /**
     * Test case for restaurant stats - order per month
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testGetOrdersPerMonth() {
        
        $restaurantId = $this->getRestaurantId();
        $ordersPerMonth = Yourdelivery_Statistics_Restaurant::getOrdersPerMonth($restaurantId);

        $total = 0;
        foreach ($ordersPerMonth as $month) {
            $total += $month['totalCount'];
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
            ->from('orders', array('total' => new Zend_Db_Expr('COUNT(DISTINCT id)')))
            ->where('state > 0')
            ->where('restaurantId = ?', $restaurantId)
            ->where('YEAR(time) = ?', date('Y'));
        $testTotal = $db->fetchRow($select);

        $this->assertEquals($total, $testTotal['total']);
    }

    /**
     * Test case for restaurant stats - sales per month
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     */
    public function testGetSalesPerMonth() {
        
        $restaurantId = $this->getRestaurantId();
        $ordersPerMonth = Yourdelivery_Statistics_Restaurant::getSalesPerMonth($restaurantId);

        $total = 0;
        foreach ($ordersPerMonth as $month) {
            $total += $month['totalSales'];
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()
            ->from('orders', array('total' => new Zend_Db_Expr('sum(total + serviceDeliverCost + courierCost)')))
            ->where('state > 0')
            ->where('restaurantId = ?', $restaurantId)
            ->where('YEAR(time) = ?', date('Y'));
        $testTotal = $db->fetchRow($select);

        $this->assertEquals($total, $testTotal['total']);
    }

    /**
     * Returns period timestamps generated by random
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 3.07.2012
     *
     * @return array
     */
    private function getTimestamps() {
        $now = time();
        return array(
            $now - rand(2500000, 5000000),      // about 30-60 days ago
            $now - rand(0, 2500000)             // about 0-30 days ago
        );
    }

    /**
     * Returns id of randomly found restaurant
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 3.07.2012
     *
     * @return int
     */
    private function getRestaurantId() {
        return $this->getRandomService(array(
            'type' => Yourdelivery_Model_Servicetype_Abstract::RESTAURANT_IND,
            'online' => true,
            'hasOrders' => true,
            // In *.pl fax (default) is generally not used
            'notify' => (substr($this->config->domain->base, -3) == '.pl')? 'sms': 'fax'
        ))->getId();
    }

    /**
     * Comapres result sets by checking their sizes and some of record data (chosen by random)
     *
     * @author Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 3.07.2012
     *
     * @param array $rawResult - result pattern, generated by raw SQL query
     * @param array $testedResult - result generated by tested method
     * @param int $count - how many records should be compared
     * @return void
     */
    private function compareResults($rawResult, $testedResult, $count, $sortCmp = null) {
        $this->assertEquals(count($rawResult), count($testedResult), 'Result lengths are different');
        // Selecting by random few indexes of data to be compared
        $indexes = array_keys($rawResult);
        shuffle($indexes);
        if (isset($sortCmp)) {
            // Additional sorting, useful when result sort orders are not strict
            usort($rawResult, $sortCmp);
            usort($testedResult, $sortCmp);
        }
        foreach (array_slice($indexes, 0, $count) as $index) {
            // Important note: in both cases tested results may contain more data,
            // than raw query results, which should not be treated as a fault
            if (is_object($rawResult[$index])) {
                // comparing objects
                $this->assertTrue(is_object($testedResult[$index]));
                foreach (array_keys((array)$rawResult[$index]) as $key) {
                    $this->assertEquals(
                        $rawResult[$index]->$key, $testedResult[$index]->$key,
                        sprintf('Result mismatch - row: %d, key: %s', $index, $key)
                    );
                }
            } else {
                // comparing arrays
                $this->assertTrue(is_array($testedResult[$index]));
                foreach (array_keys($rawResult[$index]) as $key) {
                    $this->assertEquals(
                        $rawResult[$index][$key], $testedResult[$index][$key],
                        sprintf('Result mismatch - row: %d, key: %s', $index, $key)
                    );
                }
            }
        }
    }
}
