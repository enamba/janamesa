<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * Send some statistics as email
 * @author vpriem
 * @since 21.01.2011
 */
clog('info', 'sending confidence email');

$queries = array(
    'restaurants' =>
    "SELECT COUNT(`id`)
        FROM `restaurants`
        WHERE `deleted` = 0",
    'restaurants_servicetype' =>
    "SELECT COUNT(r.id)
        FROM `restaurants` r
        INNER JOIN `restaurant_servicetype` rs ON r.id = rs.restaurantId
            AND rs.servicetypeId = ?
        WHERE r.deleted = 0",
    'online_restaurants' =>
    "SELECT COUNT(`id`)
        FROM `restaurants`
        WHERE `deleted` = 0
            AND `isOnline` = 1",
    'online_restaurants_servicetype' =>
    "SELECT COUNT(r.id)
        FROM `restaurants` r
        INNER JOIN `restaurant_servicetype` rs ON r.id = rs.restaurantId
            AND rs.servicetypeId = ?
        WHERE r.deleted = 0
            AND r.isOnline = 1",
    'company_customers' => // reviewed
    "SELECT COUNT(*)
        FROM (
            SELECT o.customerId
            FROM `orders` o
            INNER JOIN `customer_company` cc ON o.customerId = cc.customerId
            INNER JOIN `companys` c ON cc.companyId = c.id
                AND c.deleted = 0
            WHERE o.state > 0
                AND YEAR(o.time) = YEAR(CURDATE())
            GROUP BY o.customerId
            HAVING COUNT(o.customerId) > 1
        ) t",
    'private_customers_registered' =>
    "SELECT `count` from view_customers_count_registered",
    'private_customers_unregistered' =>
    "SELECT `count` from view_customers_count_notregistered",
    'registered_customers' =>
    "SELECT COUNT(c.id)
        FROM `customers` c
        WHERE `created` BETWEEN ? AND ?",
    'orders' =>
    "SELECT COUNT(o.orderId)
        FROM `view_sales` o
        WHERE o.time BETWEEN ? AND ?",
    'premium_orders' =>
    "SELECT COUNT(o.id)
        FROM `orders` o
        INNER JOIN `restaurants` r ON o.restaurantId = r.id
            AND r.franchiseTypeId in (3,4,5)
        WHERE o.state > 0
            AND o.time BETWEEN ? AND ?",
    'sales' =>
    "SELECT ROUND(SUM(o.sales) / 100, 2)
        FROM `view_sales` o
        WHERE o.time BETWEEN ? AND ?",
    'comission' =>
    "SELECT ROUND(SUM(o.commission) / 100, 2)
        FROM `view_sales` o
        WHERE o.time BETWEEN ? AND ?",
);
$db = Zend_Registry::get('dbAdapter');

$timestamp = time();
//$timestamp = mktime(23, 59, 0, date('n'), date('j') - 1); // yesterday
$today = date("Y-m-d 00:00:00", $timestamp);
$to = date("Y-m-d 23:59:59", $timestamp);
$thisweek = date("Y-m-d 00:00:00", (date('w', $timestamp) == 1 ? $timestamp : strtotime('last Monday', $timestamp)));
$thismonth = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp), 1));
$last3month = date("Y-m-d 00:00:00", strtotime("-3 months", $timestamp));

$html =
        '<h2>Übersicht für den %s bis um %s</h2>
     <hr />
     <table cellpadding="3">
        <tr>
            <td>Dienstleister</td>
            <td align="center">Online</td>
            <td align="center">Gesamt</td>
        </tr>
        <tr>
            <td>Restaurant:</td>
            <td align="center">%s</td>
            <td align="center">%s</td>
        </tr>
        <tr>
            <td>Caterer:</td>
            <td align="center">%s</td>
            <td align="center">%s</td>
        </tr>
        <tr>
            <td>Großhändler:</td>
            <td align="center">%s</td>
            <td align="center">%s</td>
        </tr>
        <tr>
            <td>Gesamt:</td>
            <td align="center">%s</td>
            <td align="center">%s</td>
        </tr>
     </table>
     <hr />
     <table cellpadding="3">
        <tr>
            <td>Firmenkunden:</td>
            <td align="center">%s</td>
        </tr>
        <tr>
            <td>Privatkunden registriert:</td>
            <td align="center">%s</td>
        </tr>
        <tr>
            <td>Privatkunden unregistriert:</td>
            <td align="center">%s</td>
        </tr>
     </table>
     <hr />
     <table cellpadding="3">
        <tr>
            <td colspan="2">Kunden registriert</td>
        </tr>
        <tr>
            <td>Heute:</td>
            <td align="center">%s</td>
        </tr>
        <tr>
            <td>7 Tage:</td>
            <td align="center">%s</td>
        </tr>
        <tr>
            <td>1 Monat:</td>
            <td align="center">%s</td>
        </tr>
        <tr>
            <td>3 Monate:</td>
            <td align="center">%s</td>
        </tr>
     </table>
     <hr />
     <table cellpadding="3">
        <tr>
            <td>Bestellungen</td>
            <td align="center">Anzahl</td>
            <td align="center">Umsatz</td>
            <td align="center">Provision</td>
        </tr>
        <tr>
            <td>Heute:</td>
            <td align="center">%s</td>
            <td align="center">%s &euro;</td>
            <td align="center">%s &euro;</td>
        </tr>
        <tr>
            <td>7 Tage:</td>
            <td align="center">%s</td>
            <td align="center">%s &euro;</td>
            <td align="center">%s &euro;</td>
        </tr>
        <tr>
            <td>1 Monat:</td>
            <td align="center">%s</td>
            <td align="center">%s &euro;</td>
            <td align="center">%s &euro;</td>
        </tr>
        <tr>
            <td>3 Monate:</td>
            <td align="center">%s</td>
            <td align="center">%s &euro;</td>
            <td align="center">%s &euro;</td>
        </tr>
     </table>
     <hr />
     <table cellpadding="3">
        <tr>
            <td colspan="2">Premium Bestellungen</td>
        </tr>
        <tr>
            <td>Heute:</td>
            <td align="center">%s</td>
        </tr>
        <tr>
            <td>7 Tage:</td>
            <td align="center">%s</td>
        </tr>
        <tr>
            <td>1 Monat:</td>
            <td align="center">%s</td>
        </tr>
        <tr>
            <td>3 Monate:</td>
            <td align="center">%s</td>
        </tr>
     </table>';

$html = sprintf($html, date('d.m.Y', $timestamp), date('H:i', $timestamp), $db->fetchOne($queries['online_restaurants_servicetype'], 1), $db->fetchOne($queries['restaurants_servicetype'], 1), $db->fetchOne($queries['online_restaurants_servicetype'], 2), $db->fetchOne($queries['restaurants_servicetype'], 2), $db->fetchOne($queries['online_restaurants_servicetype'], 3), $db->fetchOne($queries['restaurants_servicetype'], 3), $db->fetchOne($queries['online_restaurants']), $db->fetchOne($queries['restaurants']), $db->fetchOne($queries['company_customers']), $db->fetchOne($queries['private_customers_registered']), $db->fetchOne($queries['private_customers_unregistered']), $db->fetchOne($queries['registered_customers'], array($today, $to)), $db->fetchOne($queries['registered_customers'], array($thisweek, $to)), $db->fetchOne($queries['registered_customers'], array($thismonth, $to)), $db->fetchOne($queries['registered_customers'], array($last3month, $to)), $db->fetchOne($queries['orders'], array($today, $to)), $db->fetchOne($queries['sales'], array($today, $to)), $db->fetchOne($queries['comission'], array($today, $to)), $db->fetchOne($queries['orders'], array($thisweek, $to)), $db->fetchOne($queries['sales'], array($thisweek, $to)), $db->fetchOne($queries['comission'], array($thisweek, $to)), $db->fetchOne($queries['orders'], array($thismonth, $to)), $db->fetchOne($queries['sales'], array($thismonth, $to)), $db->fetchOne($queries['comission'], array($thismonth, $to)), $db->fetchOne($queries['orders'], array($last3month, $to)), $db->fetchOne($queries['sales'], array($last3month, $to)), $db->fetchOne($queries['comission'], array($last3month, $to)), $db->fetchOne($queries['premium_orders'], array($today, $to)), $db->fetchOne($queries['premium_orders'], array($thisweek, $to)), $db->fetchOne($queries['premium_orders'], array($thismonth, $to)), $db->fetchOne($queries['premium_orders'], array($last3month, $to))
);

// Replace Money Sign
$html = str_replace("&euro;", __('€'), $html);


Yourdelivery_Sender_Email::confidence($html);
