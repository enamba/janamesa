<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

/**
 * List of all restaurants set online yesterday
 * mail must be send every day at the morning, 6 o'clock or so
 * @author alex
 * @since 08.02.2011
 */
clog('info', 'sending csv with online restaurants');

$emailBody = "<html><title>Restaurants, die gestern online gestellt wurden</title>";
$emailBody .= "<table>";
$emailBody .= "<tr>";

$emailBody .= "<td width='10%'>ID</td>";
$emailBody .= "<td width='20%'>Restaurant</td>";
$emailBody .= "<td width='40%'>Stadt</td>";
$emailBody .= "<td width='10%'>Status</td>";
$emailBody .= "<td width='20%'>Freigeschaltet am</td>";
$emailBody .= "</tr>";

$db = Zend_Registry::get('dbAdapter');
$data = $db->fetchAll(
        "select r.id, r.name, c.city, IF(count(rn.comment) > 1, 'Reaktiviert', 'Neu') 'status', MAX(rn.`time`) as freigeschaltet
        from restaurants r
            join city c on c.id=r.cityId
            join restaurant_notepad rn on rn.restaurantId=r.id
                where   r.isOnline=1 and
                        rn.comment='Online gestellt'
                        group by r.id having DATE_FORMAT(freigeschaltet, '%Y-%m-%d')=DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d') order by freigeschaltet, c.city, r.name");

$countNew = 0;
$countReactivated = 0;

foreach ($data as $d) {
    $emailBody .= "<tr>";
    $emailBody .= "<td>" . $d['id'] . "</td>";
    $emailBody .= "<td>" . $d['name'] . "</td>";
    $emailBody .= "<td>" . $d['city'] . "</td>";
    $emailBody .= "<td>" . $d['status'] . "</td>";
    $emailBody .= "<td>" . $d['freigeschaltet'] . "</td>";
    $emailBody .= "</tr>";
    
    if (strcmp($d['status'], 'Reaktiviert') == 0) {
        $countReactivated++;
    }
    else {
        $countNew++;
    }
}

$emailBody .= "<tr><td><br/>Neu: " . $countNew . "</td></tr>";
$emailBody .= "<tr><td>Reaktiviert: " . $countReactivated . "</td></tr>";

$emailBody .= "</table>";
$emailBody .= "</html>";

$email = new Yourdelivery_Sender_Email();
$email->setSubject('Restaurants, die gestern online geschaltet wurden')
        ->setBodyHtml($emailBody)
        ->addTo('gia@lieferando.de')
        ->addTo('sharndorf@lieferando.de')
        ->addTo('ohrmann@lieferando.de')
        ->addTo('m.schmidt@lieferando.de');

clog('info', $emailBody);

if ($email->send('system')) {
    clog('info', 'Email was send');
} else {
    clog('err', 'Sending Email failed');
}
