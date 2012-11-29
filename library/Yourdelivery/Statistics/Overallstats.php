<?php

/**
 * General statistics
 *
 * @author alex
 */
class Yourdelivery_Statistics_Overallstats extends Yourdelivery_Statistics_Abstract {
    /**
     * Retrieves data of orders made by unregistered companies for given time period
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de>, Marek Hejduk <m.hejduk@pyszne.pl>
     *
     * @param timestamp $from
     * @param timestamp $until
     * @return array
     */
    public static function getUnregisteredCompanyOrders($from, $until = null) {
        if (!is_numeric($from)) {
            // prevent from executing very heavy query
            return array();
        }
        if (!is_numeric($until)) {
            $until = time();
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()->from(array('o' => 'orders'), array(
            'o.id', 'o.time', 'o.total',
        ))->join(array('l' => 'orders_location'), 'o.id = l.orderId', array(
            'l.tel', 'l.plz', 'l.street', 'l.hausnr', 'l.companyName', 'l.comment',
        ))->join(array('cnr' => 'orders_customer'), 'o.id = cnr.orderId', array(
            'cnr.prename', 'cnr.name', 'cnr.email',
        ))->join(array('r' => 'restaurants'), 'o.restaurantId = r.id', array(
            'rname' => 'r.name', 'rstreet' => 'r.street', 'rhausnr' => 'r.hausnr',
            'rplz' => 'r.plz', 'rtel' => 'r.tel',
        ))->where("o.time >= FROM_UNIXTIME(?)", $from)
            ->where("o.time <= FROM_UNIXTIME(?)", $until)
            ->where("o.state > ?", Yourdelivery_Model_Order::NOTAFFIRMED)
            ->where("l.companyName != ''")
            ->where("l.companyName NOT IN (SELECT companys.name FROM companys)");

        try {
            return $db->fetchAll($select, array(), Zend_Db::FETCH_OBJ);
        } catch (Zend_Db_Statement_Exception $ex) {
            return array();
        }
    }

    /**
     * Retrieves stats data of used discounts for given time period (with groupping by date option)
     *
     * @author Alex Vait <vait@lieferando.de>, Marek Hejduk <m.hejduk@pyszne.pl>
     *
     * @param timestamp $from
     * @param timestamp $until
     * @param bool $withDateGroupping
     * @return array
     */
    public static function getUsedDiscounts($from, $until = null, $withDateGroupping = false) {
        if (!is_numeric($from)) {
            // prevent from executing very heavy query
            return array();
        }
        if (!is_numeric($until)) {
            $until = time();
        }

        $db = Zend_Registry::get('dbAdapterReadOnly');
        $select = $db->select()->from(array('o' => 'orders'), array(
            'sum' => new Zend_Db_Expr('SUM(o.discountAmount)'),
            'year' => new Zend_Db_Expr(($withDateGroupping)? 'YEAR(o.time)': 'NULL'),
            'month' => new Zend_Db_Expr(($withDateGroupping)? 'MONTH(o.time)': 'NULL'),
        ))->join(array('rc' => 'rabatt_codes'), 'o.rabattCodeId = rc.id', array(
            'count' => new Zend_Db_Expr(($withDateGroupping)? 'COUNT(rc.id)': 'COUNT(*)'),
            'name' => ($withDateGroupping)? new Zend_Db_Expr('NULL'): 'rc.code',
        ))->join(array('rb' => 'rabatt'), 'rc.rabattId = rb.id', array(
            'rabattName' => 'rb.name', 'rabattId' => 'rb.id',
        ))->where("o.time >= FROM_UNIXTIME(?)", $from)
            ->where("o.time <= FROM_UNIXTIME(?)", $until)
            ->where("o.state > ?", Yourdelivery_Model_Order::NOTAFFIRMED)
            ->where("o.rabattCodeId > 1");
        if ($withDateGroupping) {
            // grouping also by year and month
            $select->group(array('rabattId', 'year', 'month'))
                ->order(array('year DESC', 'month', 'sum DESC'));
        } else {
            // standard case - only by rebate code
            $select->group('rabattCodeId')->order('sum');
        }

        try {
            return $db->fetchAll($select, array(), PDO::FETCH_ASSOC);
        } catch (Zend_Db_Statement_Exception $ex) {
            return array();
        }
    }
}
