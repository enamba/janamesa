<?php

/**
 * Statistics for restaurant backend
 *
 * @author alex
 */
class Yourdelivery_Statistics_Restaurant extends Yourdelivery_Statistics_Abstract {
    /**
     * Retrieves data of 10 most selling meals
     *
     * @author Alex Vait <vait@lieferando.de>, Marek Hejduk <m.hejduk@pyszne.pl>
     *
     * @param int $restairantId
     * @param int $count
     * @return array
     */
    public static function getTopMeals($restaurantId, $count=15) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()->from(array('obm' => 'orders_bucket_meals'), array(
            'count' => new Zend_Db_Expr('SUM(obm.count)'),
        ))->join(array('o' => 'orders'), 'obm.orderId = o.id', array(
        ))->join(array('m'  => 'meals'), 'obm.mealId = m.id', array(
            'm.id', 'm.name',
        ))->join(array('mc' => 'meal_categories'), 'm.categoryId = mc.id', array(
            'categoryId' => 'mc.id', 'categoryName' => 'mc.name',
        ))->where('o.restaurantId = ?', $restaurantId)
            ->where('o.state > ?', Yourdelivery_Model_Order::NOTAFFIRMED)
            ->where('m.deleted = 0')
            ->where('m.status = 1')
            ->group('m.id')->order('count DESC')->limit($count);

        try {
            return $db->fetchAll($select, array(), Zend_Db::FETCH_OBJ);
        } catch (Zend_Db_Statement_Exception $ex) {
            return array();
        }
    }

    /**
     * Retrieves data of 10 most selling meal categories
     *
     * @author Alex Vait <vait@lieferando.de>, Marek Hejduk <m.hejduk@pyszne.pl>
     *
     * @param int $restairantId
     * @param int $count
     * @return array
     */
    public static function getTopCategories($restaurantId, $count=15) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()->from(array('obm' => 'orders_bucket_meals'), array(
            'count' => new Zend_Db_Expr('SUM(obm.count)'),
        ))->join(array('o' => 'orders'), 'obm.orderId = o.id', array(
        ))->join(array('m'  => 'meals'), 'obm.mealId = m.id', array(
        ))->join(array('mc' => 'meal_categories'), 'm.categoryId = mc.id', array(
            'mc.id', 'mc.name',
        ))->where('o.restaurantId = ?', $restaurantId)
            ->where('o.state > ?', Yourdelivery_Model_Order::NOTAFFIRMED)
            ->where('m.deleted = 0')
            ->where('m.status = 1')
            ->group('mc.id')->order('count DESC')->limit($count);

        try {
            return $db->fetchAll($select, array(), Zend_Db::FETCH_OBJ);
        } catch (Zend_Db_Statement_Exception $ex) {
            return array();
        }
    }

    /**
     * Returns sales amount for given restaurant and month
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.04.2012
     *
     * @param int $restaurantId
     * @param int $month
     * @param string|null @payment
     * @return float
     */
    public static function getSalesVolume($restaurantId, $month, $payment = null) {

        $db = Zend_Registry::get('dbAdapterReadOnly');

        $select = $db->select()->from(array('o' => 'orders'), array('total' => new Zend_Db_Expr('sum(o.total + o.serviceDeliverCost + o.courierCost)')))
                ->where('MONTH(o.time) = ?', $month)
                ->where('YEAR(o.time) = YEAR(NOW())')
                ->where('o.state > 0')
                ->where('o.restaurantId = ?', $restaurantId);

        if ($payment == 'bar') {
            $select->where('o.payment = ?', $payment);
        } elseif ($payment == 'online') {
            $select->where("o.payment != 'bar'");
        }

        try {
            $result = $db->fetchRow($select);
            return $result['total'];
        } catch (Zend_Db_Statement_Exception $ex) {
            return 0;
        }
    }

    /**
     * Retrieves monthly billing stats for given restaurant and year
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.04.2012
     *
     * @param int $restaurantId
     * @param int $year
     * @return array
     */
    public static function getBillingsPerMonth($restaurantId, $year = null) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        if ($year === null) {
            $year = date('Y');
        }

        $select = $db->select()->from(array('b' => 'billing'), array('ids' => new Zend_Db_Expr("GROUP_CONCAT(`b`.`id` SEPARATOR ',')"), 'month' => new Zend_Db_Expr('MONTH(b.from)')))
                ->where('refId = ?', $restaurantId)
                ->where("mode = 'rest'")
                ->where("YEAR(b.from) = ?", $year)
                ->group('month');

        $result = array();
        try {
            foreach ($db->fetchAll($select) as $bill) {
                $result[$bill['month']] = explode(',', $bill['ids']);
            }
        } catch (Zend_Db_Statement_Exception $ex) {
        }

        return $result;
    }

    /**
     * Retrieves monthly order data for given restaurant and year
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.04.2012
     *
     * @param int $restaurantId
     * @param int $year
     * @return array
     */
    public static function getOrdersPerMonth($restaurantId, $year = null) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        
        $select = self::_getOrderStatsQuery($restaurantId, $year);
        $select->group('month');

        $result = array();
        $months = $db->fetchAll($select);
        foreach ($months as $month) {
            $result[$month['month']] = $month;
        }
        return $result;
    }

    /**
     * Retrieves weekly order data for given restaurant and year
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.04.2012
     *
     * @param int $restaurantId
     * @param int $year
     * @return array
     */
    public static function getOrdersPerWeek($restaurantId, $year = null) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        
        $select = self::_getOrderStatsQuery($restaurantId, $year);
        $select->group('week');

        $result = array();
        $weeks = $db->fetchAll($select);
        foreach ($weeks as $week) {
            $result[$week['week']] = $week;
        }
        return $result;
    }

    /**
     * Retrieves daily sales data for given restaurant and month
     * 
     * @author Daniel Hahn <hahn@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 12.04.2012
     *
     * @param int $restaurantId
     * @param int $month
     * @return array
     */
    public static function getSalesPerDay($restaurantId, $month = null) {
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $config = Zend_Registry::get('configuration');

        if ($month === null) {
            $month = date('n');
        }

        $inDomain = array();
        $inDomain[] = $config->domain->base;
        $inDomain[] = "www." . $config->domain->base;
        if ($config->domain->base == 'lieferando.de') {
            $inDomain[] = "www.eat-star.de";
            $inDomain[] = "eat-star.de";
        }
        $inDomain = "'" . implode("', '", $inDomain) . "'";
        
        $fields = array(
            'totalOrders' => new Zend_Db_Expr("COUNT(o.id)"),
            'totalSales' => new Zend_Db_Expr("SUM(o.total + o.serviceDeliverCost + o.courierCost)"),
            'totalSalesBar' => new Zend_Db_Expr("SUM(IF(o.payment = 'bar', o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'totalOrdersBar' => new Zend_Db_Expr("SUM(IF(o.payment = 'bar', 1, 0))"),
            'totalSalesOnline' => new Zend_Db_Expr("SUM(IF(o.payment != 'bar', o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'totalOrdersOnline' => new Zend_Db_Expr("SUM(IF(o.payment != 'bar', 1, 0))"),
            'orders' => new Zend_Db_Expr("SUM(IF(o.domain IN ($inDomain) OR o.domain IS NULL, 1, 0))"),
            'sales' => new Zend_Db_Expr("SUM(IF(o.domain IN ($inDomain) OR o.domain IS NULL, o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'salesBar' => new Zend_Db_Expr("SUM(IF(o.payment = 'bar' AND (o.domain IN ($inDomain) OR o.domain IS NULL), o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'salesOnline' => new Zend_Db_Expr("SUM(IF(o.payment != 'bar' AND (o.domain IN ($inDomain) OR o.domain IS NULL), o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'satelliteOrders' => new Zend_Db_Expr("SUM(IF(o.domain NOT IN ($inDomain), 1, 0))"),
            'satelliteSales' => new Zend_Db_Expr("SUM(IF(o.domain NOT IN ($inDomain), o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'satelliteSalesBar' => new Zend_Db_Expr("SUM(IF(o.payment = 'bar' AND o.domain NOT IN ($inDomain), o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'satelliteSalesOnline' => new Zend_Db_Expr("SUM(IF(o.payment != 'bar' AND o.domain NOT IN ($inDomain), o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'dayOfWeek' => new Zend_Db_Expr("WEEKDAY(o.time) + 1"),
            'day' => new Zend_Db_Expr("DATE_FORMAT(o.time,'%d.%m')"));

        $select = $db->select()
                ->from(array('o' => 'orders'), $fields)
                ->where('YEAR(o.time) = YEAR(NOW())')
                ->where('MONTH(o.time) = ?', $month)
                ->where('o.restaurantId = ?', $restaurantId)
                ->where('o.state > 0')
                ->group('day');

        $result = array();
        $days = $db->fetchAll($select);
        foreach ($days as $day) {
            $result[$day['day']] = $day;
        }
        return $result;
    }

    /**
     * Retrieves monthly sales data for given restaurant and month
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.04.2012
     *
     * @param int $restaurantId
     * @param int $year
     * @return array
     */
    public static function getSalesPerMonth($restaurantId, $year = null) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        
        $select = self::_getSalesStatsQuery($restaurantId, $year);
        $select->group('month');

        $result = array();
        $months = $db->fetchAll($select);
        foreach ($months as $month) {
            $result[$month['month']] = $month;
        }

        return $result;
    }

    /**
     * Retrieves weekly sales data for given restaurant and month
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.04.2012
     *
     * @param int $restaurantId
     * @param int $year
     * @return array
     */
    public static function getSalesPerWeek($restaurantId, $year = null) {
        $db = Zend_Registry::get('dbAdapterReadOnly');
        
        $select = self::_getSalesStatsQuery($restaurantId, $year);
        $select->group('week');

        $result = array();
        $weeks = $db->fetchAll($select);
        foreach ($weeks as $week) {
            $result[$week['week']] = $week;
        }

        return $result;
    }

    /**
     * Returns order count for given restaurant and time period
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 12.04.2012
     * 
     * @param int $restaurantId
     * @param int $year
     * @return array
     */
    public static function getOrderCount($restaurantId, $time) {
        $db = Zend_Registry::get('dbAdapterReadOnly');

        $select = $db->select()->from(array('o' => 'orders'), array('count' => new Zend_Db_Expr('COUNT(DISTINCT o.id)')))
                ->where('o.restaurantId = ?', $restaurantId)
                ->where('o.state > 0');

        switch ($time) {
            case 'today': $select->where('DATE(o.time) = DATE(NOW())');
                break;
            case 'lastseven': $select->where("DATE(o.time) > DATE_SUB(NOW(), INTERVAL  7 DAY)");
                break;
            case 'week': $select->where('WEEK(o.time) = WEEK(NOW()) AND YEAR(o.time) = YEAR(NOW())');
                break;
            case 'month': $select->where('MONTH(o.time) = MONTH(NOW()) AND YEAR(o.time) = YEAR(NOW())');
                break;
            case 'lastmonth': $select->where("date_format(o.time, '%Y-%m') = date_format(now() - INTERVAL 1 MONTH, '%Y-%m')");
                break;
            default: break;
        }

        try {
            $result = $db->fetchRow($select);
            return $result['count'];
        } catch (Zend_Db_Statement_Exception $ex) {
            return 0;
        }
    }

    /**
     * A shortcut generating order stats select instance
     *
     * @author Daniel Hahn <hahn@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 04.06.2012
     *
     * @param int $restaurantId
     * @param int $year
     * @return Zend_Db_Table_Select
     */
    protected static function _getOrderStatsQuery($restaurantId, $year) {
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $config = Zend_Registry::get('configuration');

        if ($year === null) {
            $year = date('Y');
        }

        $inDomain = array();
        $inDomain[] = $config->domain->base;
        $inDomain[] = "www." . $config->domain->base;
        if ($config->domain->base == 'lieferando.de') {
            $inDomain[] = "www.eat-star.de";
            $inDomain[] = "eat-star.de";
        }
        $inDomain = "'" . implode("', '", $inDomain) . "'";

        $fields = array(
            'totalCount' => new Zend_Db_Expr('COUNT(DISTINCT o.id)'),
            'totalCountBar' => new Zend_Db_Expr("SUM(IF(o.payment = 'bar', 1, 0))"),
            'totalCountOnline' => new Zend_Db_Expr("SUM(IF(o.payment != 'bar', 1, 0))"),
            'count' => new Zend_Db_Expr("SUM(IF(o.domain IN ($inDomain) OR o.domain IS NULL, 1, 0))"),
            'countBar' => new Zend_Db_Expr("SUM(IF(o.payment = 'bar' AND (o.domain IN ($inDomain) OR o.domain IS NULL), 1, 0))"),
            'countOnline' => new Zend_Db_Expr("SUM(IF(o.payment != 'bar' AND (o.domain IN ($inDomain) OR o.domain IS NULL), 1, 0))"),
            'satelliteCount' => new Zend_Db_Expr("SUM(IF(o.domain NOT IN ($inDomain), 1, 0))"),
            'satelliteCountBar' => new Zend_Db_Expr("SUM(IF(o.payment = 'bar' AND o.domain NOT IN ($inDomain), 1, 0))"),
            'satelliteCountOnline' => new Zend_Db_Expr("SUM(IF(o.payment != 'bar' AND o.domain NOT IN ($inDomain), 1, 0))"),
            'month' => new Zend_Db_Expr('MONTH(o.time)'),
            'week' => new Zend_Db_Expr('WEEK(o.time)'));

        return $db->select()
                ->from(array('o' => 'orders'), $fields)
                ->where('YEAR(o.time) = ?', $year)
                ->where('o.restaurantId = ?', $restaurantId)
                ->where('o.state > 0');
    }

    /**
     * A shortcut generating sales stats select instance
     *
     * @author Daniel Hahn <hahn@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 04.06.2012
     * 
     * @param int $restaurantId
     * @param int $year
     * @return Zend_Db_Table_Select
     */
    protected static function _getSalesStatsQuery($restaurantId, $year) {
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        $config = Zend_Registry::get('configuration');
        
        if ($year === null) {
            $year = date('Y');
        }

        $inDomain = array();
        $inDomain[] = $config->domain->base;
        $inDomain[] = "www." . $config->domain->base;
        if ($config->domain->base == 'lieferando.de') {
            $inDomain[] = "www.eat-star.de";
            $inDomain[] = "eat-star.de";
        }
        $inDomain = "'" . implode("', '", $inDomain) . "'";

        $fields = array(
            'totalSales' => new Zend_Db_Expr("SUM(o.total + o.serviceDeliverCost + o.courierCost)"),
            'totalSalesBar' => new Zend_Db_Expr("SUM(IF(o.payment = 'bar', o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'totalSalesOnline' => new Zend_Db_Expr("SUM(IF(o.payment != 'bar', o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'sales' => new Zend_Db_Expr("SUM(IF(o.domain IN ($inDomain) OR o.domain IS NULL, o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'salesBar' => new Zend_Db_Expr("SUM(IF(o.payment = 'bar' AND (o.domain IN ($inDomain) OR o.domain IS NULL), o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'salesOnline' => new Zend_Db_Expr("SUM(IF(o.payment != 'bar' AND (o.domain IN ($inDomain) OR o.domain IS NULL), o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'satelliteSales' => new Zend_Db_Expr("SUM(IF(o.domain NOT IN ($inDomain), o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'satelliteSalesBar' => new Zend_Db_Expr("SUM(IF(o.payment = 'bar' AND o.domain NOT IN ($inDomain), o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'satelliteSalesOnline' => new Zend_Db_Expr("SUM(IF(o.payment != 'bar' AND o.domain NOT IN ($inDomain), o.total + o.serviceDeliverCost + o.courierCost, 0))"),
            'month' => new Zend_Db_Expr('MONTH(o.time)'),
            'week' => new Zend_Db_Expr('WEEK(o.time)'));

        return $db->select()
                ->from(array('o' => 'orders'), $fields)
                ->where('YEAR(o.time) = ?', $year)
                ->where('o.restaurantId = ?', $restaurantId)
                ->where('o.state > 0');
    }
}
