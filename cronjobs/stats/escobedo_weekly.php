<?php
    // Cronjob for SP-6246

    require_once(realpath(dirname(__FILE__) . '/../base.php'));

    $db = Zend_Registry::get('dbAdapter');

    $currentWeek = date("W",time());
    $previousWeek = $currentWeek -1;

    $queries = array();
    foreach (array('de', 'at') as $country) {
        foreach (array($previousWeek, $currentWeek) as $week) {
            $queries['orders_' . $country . '_' . $week] = sprintf('SELECT COUNT(id) FROM `lieferando.%s`.`orders` WHERE WEEK(time)=%s', $country, $week);
            $queries['ratings_' . $country . '_' . $week] = sprintf('SELECT COUNT(id) FROM `lieferando.%s`.`restaurant_ratings` WHERE WEEK(created)=%s', $country, $week);
        }
    }

    $results = array();
    foreach ($queries as $key => $query) {
        $results[$key] = $db->fetchOne($query);
    }

    $mailcontent = "";
    $mailcontent .= "Bestellungen DE KW $previousWeek:{$results['orders_de_'.$previousWeek]}\n\n";
    $mailcontent .= "Bestellungen DE KW $currentWeek:{$results['orders_de_'.$currentWeek]}\n\n";
    $mailcontent .= "Bewertungen DE KW $previousWeek:{$results['ratings_de_'.$previousWeek]}\n\n";
    $mailcontent .= "Bewertungen DE KW $currentWeek:{$results['ratings_de_'.$currentWeek]}\n\n";

    $mailcontent .= "Bestellungen AT KW $previousWeek:{$results['orders_at_'.$previousWeek]}\n\n";
    $mailcontent .= "Bestellungen AT KW $currentWeek:{$results['orders_at_'.$currentWeek]}\n\n";
    $mailcontent .= "Bewertungen AT KW $previousWeek:{$results['ratings_at_'.$previousWeek]}\n\n";
    $mailcontent .= "Bewertungen AT KW $currentWeek:{$results['ratings_at_'.$currentWeek]}\n\n";

    $mail = new Yourdelivery_Sender_Email();
    $mail->addTo('escobedo@lieferando.de')
         ->setSubject(sprintf('Bestellungen und Bewertungen für DE/AT - KW %s und %s', $previousWeek, $currentWeek))
         ->setBodyText($mailcontent)
         ->send();